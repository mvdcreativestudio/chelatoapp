<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use App\Repositories\OrderRepository;
use App\Services\MercadoPagoService;
use MercadoPago\SDK;
use App\Models\MercadoPagoAccount;
use App\Models\Store;
use Illuminate\Support\Facades\Redirect;



use Log;

class CheckoutController extends Controller
{
    protected $orderRepository;
    protected $mercadoPagoService; // Declara una propiedad para el servicio de MercadoPago

    public function __construct(OrderRepository $orderRepository, MercadoPagoService $mercadoPagoService)
    {
        $this->orderRepository = $orderRepository;
        $this->mercadoPagoService = $mercadoPagoService; // Inyecta el servicio de MercadoPago

    }


    public function index()
    {
        $order = null; // Inicializamos $order como nulo, ya que aún no se ha creado una orden
        $cart = session('cart', []);
        $subtotal = 0;
        $costoEnvio = 60;

        foreach ($cart as $item) {
            $price = !empty($item['price']) ? $item['price'] : $item['old_price'];
            $subtotal += $price * $item['quantity'];
        }

        $envioGratis = $subtotal > 100; // Envío gratis para pedidos mayores a $100
        if ($envioGratis) {
            $costoEnvio = 0;
        }

        $totalPedido = $subtotal + $costoEnvio;

        $preferenceId = null;

        // Pasar el ID de la preferencia y otros datos a la vista
        return view('content.e-commerce.front.checkout', compact('order', 'cart', 'subtotal', 'costoEnvio', 'totalPedido', 'envioGratis', 'preferenceId'));
    }




  public function success($orderId)
  {
      // Cargar la orden con productos y sabores relacionados
      $order = Order::with(['products.flavors'])->findOrFail($orderId);
      return view('content.e-commerce.front.checkout-success', compact('order'));
  }



  public function store(Request $request)
{
    try {
        // Obtener el ID de la tienda de la sesión
        $storeId = session('store.id');

        // Validación de los datos recibidos
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'lastname' => 'required|max:255',
            'address' => 'required',
            'phone' => 'required',
            'email' => 'required|email',
            'payment_method' => 'required',
        ]);

        // Preparar datos del cliente
        $clientData = [
            'name' => $validatedData['name'],
            'lastname' => $validatedData['lastname'],
            'type' => 'individual',
            'state' => 'Montevideo',
            'city' => 'Montevideo',
            'country' => 'Uruguay',
            'address' => $validatedData['address'],
            'phone' => $validatedData['phone'],
            'email' => $validatedData['email'],
        ];

        // Preparar datos de la orden
        $subtotal = 0;
        $cartItems = session('cart', []);
        if (!is_array($cartItems)) {
            $cartItems = [];
        }

        foreach ($cartItems as $item) {
            $price = $item['price'] ?? $item['old_price'];
            $subtotal += $price * ($item['quantity'] ?? 1);
        }

        $costoEnvio = session('costoEnvio', 60); // Costo de envío predeterminado si no se ha establecido en la sesión
        $total = $subtotal + $costoEnvio;

        $orderData = [
            'date' => now(),
            'time' => now()->format('H:i:s'),
            'origin' => 'ecommerce',
            'store_id' => $storeId,
            'subtotal' => $subtotal,
            'tax' => 0,
            'shipping' => $costoEnvio,
            'total' => $total,
            'payment_status' => 'pending',
            'shipping_status' => 'pending',
            'payment_method' => $validatedData['payment_method'],
            'shipping_method' => 'peya',
        ];

        Log::info('Datos validados y preparados para la orden y el cliente:', [
            'client_data' => $clientData,
            'order_data' => $orderData
        ]);

        DB::beginTransaction();
        $order = $this->orderRepository->createOrder($clientData, $orderData, $cartItems);

        Log::info('Orden creada:', $order->toArray());

        if ($validatedData['payment_method'] === 'card') {
            // Lógica para MercadoPago
            $items = array_map(function ($item) {
                return [
                    'title' => $item['name'],
                    'quantity' => $item['quantity'] ?? 1,
                    'unit_price' => $item['price'] ?? $item['old_price'],
                ];
            }, $cartItems);

            // Obtener las credenciales de MercadoPago de la tienda
            $mercadoPagoAccount = MercadoPagoAccount::where('store_id', $storeId)->first();

            if (!$mercadoPagoAccount) {
                throw new \Exception('No se encontraron las credenciales de MercadoPago para la tienda asociada al pedido.');
            }

            Log::info('Credenciales de MercadoPago obtenidas:', $mercadoPagoAccount->toArray());

            // Configurar el SDK de MercadoPago con las credenciales de la tienda
            $this->mercadoPagoService->setCredentials($mercadoPagoAccount->public_key, $mercadoPagoAccount->access_token);

            $preferenceData = [
                'items' => $items,
                'payer' => ['email' => $clientData['email']],
            ];

            Log::info('Creando preferencia de pago con los siguientes datos:', $preferenceData);

            $preference = $this->mercadoPagoService->createPreference($preferenceData, $order);
            $preferenceId = $preference->id;
            $order->preference_id = $preferenceId;
            $order->save();

            Log::info('Preferencia de pago creada:', $preference->toArray());

            DB::commit();
            session()->forget('cart'); // Limpiar el carrito de compras

            // Redirigir al usuario a la página de pago de MercadoPago
            $redirectUrl = "https://www.mercadopago.com.uy/checkout/v1/payment/redirect/?preference-id=$preferenceId";
            return Redirect::away($redirectUrl);
        } else {
            // Lógica para pago en efectivo
            DB::commit();
            session()->forget('cart'); // Limpiar el carrito de compras

            Log::info('Pedido procesado correctamente.');

            // Redirigir al usuario a la página de éxito
            return redirect()->route('checkout.success', $order->id);
        }
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("Error al procesar el pedido: {$e->getMessage()} en {$e->getFile()}:{$e->getLine()}");
        return back()->withErrors('Error al procesar el pedido. Por favor, intente nuevamente.')->withInput();
    }
}





}
