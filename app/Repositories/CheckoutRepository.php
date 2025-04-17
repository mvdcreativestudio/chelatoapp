<?php

namespace App\Repositories;

use App\Enums\Events\EventEnum;
use App\Models\Client;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\MercadoPagoAccount;
use App\Models\EcommerceSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use App\Services\MercadoPagoService;
use App\Repositories\EmailNotificationsRepository;
use Illuminate\Http\Request;
use App\Http\Requests\CheckoutStoreOrderRequest;
use Illuminate\Http\RedirectResponse;
use App\Events\OrderCreatedEvent;
use App\Repositories\PedidosYaRepository;

use Exception;

class CheckoutRepository
{
    /**
     * Repositorio de notificaciones de correo electrónico.
     *
     * @var EmailNotificationsRepository
    */
    protected $emailNotificationsRepository;

    /**
     * Repositorio de contabilidad.
     *
     * @var AccountingRepository
    */
    protected $accountingRepository;

    /**
     * El repositorio de PedidosYa para la gestión de envíos.
     *
     * @var PedidosYaRepository
    */
    protected $pedidosYaRepository;

    /**
     * Inicializa el repositorio de notificaciones de correo electrónico.
     *
     * @param EmailNotificationsRepository $emailNotificationsRepository
     * @param AccountingRepository $accountingRepository
    */
    public function __construct(PedidosYaRepository $pedidosYaRepository, EmailNotificationsRepository $emailNotificationsRepository, AccountingRepository $accountingRepository)
    {
        $this->pedidosYaRepository = $pedidosYaRepository;
        $this->emailNotificationsRepository = $emailNotificationsRepository;
        $this->accountingRepository = $accountingRepository;
    }

    /**
     * Obtiene los datos para mostrar en la página de checkout.
     *
     * @return array
    */
    public function index(): array
    {
        $order = null;
        $cart = session('cart');
        $subtotal = 0;

        if ($cart && is_array($cart)) {
          foreach ($cart as $item) {
              $price = !empty($item['price']) ? $item['price'] : $item['old_price'];
              $subtotal += $price * $item['quantity'];
          }
        }

        $discount = session('coupon.discount', 0);
        $totalPedido = ($subtotal - $discount);
        $preferenceId = null;
        $settings = EcommerceSetting::first();
        $googleMapsApiKey = config('services.google.maps_api_key');

        return compact('order', 'cart', 'subtotal', 'totalPedido', 'preferenceId', 'discount', 'settings', 'googleMapsApiKey');
    }

    /**
     * Obtiene los datos para mostrar en la página de éxito.
     *
     * @param string $uuid
     * @return array
    */
    public function success(string $uuid): array
    {
        $order = Order::with('client')->where('uuid', $uuid)->firstOrFail();
        return compact('order');
    }

    /**
     * Obtiene los datos para mostrar en la página de fallo.
     *
     * @param string $uuid
     * @return array
    */
    public function failure(string $uuid): array
    {
        $order = Order::with('client')->where('uuid', $uuid)->firstOrFail();
        return compact('order');
    }

    /**
     * Procesa y almacena una nueva orden.
     *
     * @param CheckoutStoreOrderRequest $request
     * @param MercadoPagoService $mercadoPagoService
     * @return RedirectResponse
    */
    public function processOrder(CheckoutStoreOrderRequest $request, MercadoPagoService $mercadoPagoService): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Obtener el ID de la tienda desde la sesión
            $storeId = session('store.id');
            Log::info('ID de tienda obtenido:', ['store_id' => $storeId]);

            // Obtener los datos del cliente
            $clientData = $this->getClientData($request);
            Log::info('Datos del cliente recibidos:', $clientData);

            // Validar si el cupón es de un solo uso por cliente
            $couponId = session('coupon.id');
            Log::info('Cupón ID desde sesión:', ['coupon_id' => $couponId]);

            if ($couponId && $clientData['document']) {
                Log::info('Buscando cliente por documento:', ['document' => $clientData['document']]);
                $client = Client::where('document', $clientData['document'])->first();

                if ($client) {
                    Log::info('Cliente encontrado:', ['client_id' => $client->id]);

                    $coupon = Coupon::find($couponId);
                    if ($coupon) {
                        Log::info('Cupón encontrado:', ['single_use' => $coupon->single_use]);

                        if ($coupon->single_use) {
                            $alreadyUsed = Order::where('client_id', $client->id)
                                ->where('coupon_id', $coupon->id)
                                ->exists();

                            Log::info('¿El cliente ya usó el cupón?', ['used' => $alreadyUsed]);

                            if ($alreadyUsed) {
                                Log::warning('El cliente ya utilizó el cupón, no se debería continuar.');
                                throw new \Exception('Este cupón solo se puede usar una vez por cliente.');
                            }
                        }
                    } else {
                        Log::warning('No se encontró el cupón con ID:', ['coupon_id' => $couponId]);
                    }
                } else {
                    Log::warning('No se encontró cliente con documento:', ['document' => $clientData['document']]);
                }
            } else {
                Log::info('No se pudo validar el cupón por falta de datos.', ['couponId' => $couponId, 'document' => $clientData['document']]);
            }

            // Obtener los datos de la orden
            $orderData = $this->getOrderData($request);

            $order = $this->createOrder($clientData, $orderData);
            $store = $order->store;

            if ($store->automatic_billing) {
                $this->accountingRepository->emitCFE($order);
                $order->update(['is_billed' => true]);
            } else {
                $order->update(['is_billed' => false]);
            }

            if ($request->payment_method === 'card') {
                $mercadoPagoAccount = MercadoPagoAccount::where('store_id', $storeId)->first();

                if (!$mercadoPagoAccount) {
                    throw new \Exception('No se encontraron las credenciales de MercadoPago para la tienda asociada al pedido.');
                }

                $mercadoPagoService->setCredentials($mercadoPagoAccount->public_key, $mercadoPagoAccount->access_token);

                $redirectUrl = $this->processCardPayment($request, $order, $mercadoPagoService, $storeId);
                DB::commit();
                session()->forget('cart');
                session()->forget('coupon'); // limpiar después de usar
                return Redirect::away($redirectUrl);
            } else {
                DB::commit();
                session()->forget('cart');
                session()->forget('coupon'); // limpiar después de usar

                Log::info('Pedido procesado correctamente.');

                if ($order->shipping_method === 'peya') {
                    Log::info('Método de envío es peya. Creando envío...', ['order' => $order]);
                    $this->createPeYaShipping($order);
                }

                try {
                    Log::channel('emails')->info('Método de pago es efectivo. Intentando enviar correos...');
                    $variables = [
                      'order_id' => $order->id,
                      'client_name' => $order->client->name,
                      'client_lastname' => $order->client->lastname,
                      'client_email' => $order->client->email,
                      'client_phone' => $order->client->phone,
                      'client_address' => $order->client->address,
                      'client_city' => $order->client->city,
                      'client_state' => $order->client->state,
                      'client_country' => $order->client->country,
                      'order_total' => $order->total,
                      'order_date' => $order->date,
                      'order_items' => $order->products,
                    ];

                    $this->emailService->sendNewOrderEmail($variables);
                    $this->emailService->sendNewOrderClientEmail($variables);
                } catch (\Exception $e) {
                    Log::channel('emails')->error("Error al enviar correos: {$e->getMessage()} en {$e->getFile()}:{$e->getLine()}");
                }

                return redirect()->route('checkout.success', $order->uuid);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al procesar el pedido: {$e->getMessage()} en {$e->getFile()}:{$e->getLine()}");
            return back()->with('error', $e->getMessage())->withInput();
        }
    }



    /**
     * Crea el envío en PedidosYa si el método de envío es 'peya'.
     *
     * @param Order $order
     * @return void
    */
    private function createPeYaShipping(Order $order): void
    {
        $request = new Request([
            'estimate_id' => $order->estimate_id,
            'store_id' => $order->store_id, // Asegúrate de incluir el store_id
            'delivery_offer_id' => $order->delivery_offer_id,
        ]);

        $response = $this->pedidosYaRepository->confirmOrderRequest($request);

        if (isset($response['shippingId'])) {
            $order->shipping_id = $response['shippingId'];
            $order->shipping_status = $response['status'];
            $order->save();

            Log::info("Envío creado con éxito en PedidosYa para la orden con ID: {$order->id}");
        } else {
            Log::error("Error al crear el envío en PedidosYa para la orden con ID: {$order->id}", ['response' => $response]);
        }
    }


    /**
     * Analiza los datos de la firma X-Signature.
     *
     * @param array $xSignatureParts
     * @return array
    */
    private function parseXSignature(array $xSignatureParts): array
    {
        $xSignatureData = [];
        foreach ($xSignatureParts as $part) {
            list($key, $value) = explode('=', $part);
            $xSignatureData[trim($key)] = trim($value);
        }
        return $xSignatureData;
    }


    /**
     * Crea una orden y un cliente en la base de datos.
     *
     * @param array $clientData
     * @param array $orderData
     * @return Order
     */
    private function createOrder(array $clientData, array $orderData): Order
    {
        // Obtener la configuración de companySettings
        $companySettings = app('companySettings');

        // Asignar store_id o null dependiendo del valor de clients_has_store
        if ($companySettings && $companySettings->clients_has_store == 1) {
            $clientData['store_id'] = session('store.id');
        } else {
            $clientData['store_id'] = null;
        }

        // Crear y guardar el cliente
        $client = Client::updateOrCreate(
            ['email' => $clientData['email']],
            $clientData
        );

        Log::info('Cliente creado:', $client->toArray());

        // Crear la orden
        $order = Order::create(array_merge($orderData, ['client_id' => $client->id]));

        Log::info('Orden creada:', $order->toArray());

        // Despachar evento
        event(new OrderCreatedEvent($order));

        return $order;
    }

    /**
     * Procesa el pago con tarjeta utilizando MercadoPago.
     *
     * @param Request $request
     * @param Order $order
     * @param MercadoPagoService $mercadoPagoService
     * @param int $storeId
     * @return string
    */
    private function processCardPayment(Request $request, Order $order, MercadoPagoService $mercadoPagoService, int $storeId): string
    {
        $cartItems = session('cart', []);
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

        // Configurar el SDK de MercadoPago con las credenciales de la tienda
        $mercadoPagoService->setCredentials($mercadoPagoAccount->public_key, $mercadoPagoAccount->access_token);

        $preferenceData = [
          'items' => $items,
          'payer' => ['email' => $request->email],
          'external_reference' => (string) $order->id,
          'discount' => [
              'amount' => session('coupon.discount', 0),
              'description' => 'Cupón de descuento aplicado'
          ]
        ];


        $preference = $mercadoPagoService->createPreference($preferenceData, $order);
        $order->preference_id = $preference->id;
        $order->save();

        return "https://www.mercadopago.com.uy/checkout/v1/payment/redirect/?preference-id={$preference->id}";
    }

    /**
     * Aplica un cupón de descuento y lo almacena en la sesión.
     *
     * @param string $couponCode
     * @return array
     * @throws \Exception
    */
    public function applyCoupon(string $couponCode): array
    {
        $subtotal = session('subtotal', 0);

        if ($subtotal <= 0) {
            throw new \Exception('Parece que hay un error con el carrito. Por favor, intentalo nuevamente.');
        }

        return $this->applyCouponToSession($couponCode, $subtotal);
    }

    /**
     * Obtiene los datos del cliente desde la solicitud.
     *
     * @param Request $request
     * @return array
    */
    private function getClientData(Request $request): array
    {
        return [
            'name' => $request->name,
            'lastname' => $request->lastname,
            'type' => 'individual',
            'state' => $request->department ?? 'Montevideo',
            'city' => $request->city ?? 'Montevideo',
            'country' => 'Uruguay',
            'address' => $request->address ?? 'N/A',
            'phone' => $request->phone,
            'email' => $request->email,
            'doc_type' => $request->doc_type,
            'document' => $request->doc_recep,
        ];
    }

    /**
     * Obtiene los datos de la orden desde la solicitud.
     *
     * @param Request $request
     * @return array
    */
    private function getOrderData(Request $request): array
    {
        $subtotal = 0;
        $cartItems = session('cart', []);
        $products = [];

        foreach ($cartItems as $item) {
            $price = $item['price'] ?? $item['old_price'];
            $subtotal += $price * $item['quantity'];

            $flavors = [];
            if (!empty($item['flavors'])) {
                foreach ($item['flavors'] as $flavorId => $flavorInfo) {
                    $flavors[] = $flavorInfo['name'] . ' (x' . $flavorInfo['quantity'] . ')';
                }
            }

            $category = DB::table('category_product')
                ->where('product_id', $item['id'])
                ->first();

            Log::info('Category data for product:', [
                'product_id' => $item['id'],
                'category' => $category
            ]);

            $products[] = [
                'id' => $item['id'],
                'name' => $item['name'],
                'price' => $price,
                'quantity' => $item['quantity'],
                'flavors' => implode(', ', $flavors),
                'image' => $item['image'],
                'category_id' => $category ? $category->category_id : null,
            ];
        }

        $costoEnvio = $request->shipping_cost;
        $total = $subtotal + $costoEnvio - session('coupon.discount', 0);

        $orderData = [
            'date' => now(),
            'time' => now()->format('H:i:s'),
            'origin' => 'ecommerce',
            'store_id' => session('store.id', 1),
            'subtotal' => $subtotal,
            'tax' => 0,
            'shipping' => $costoEnvio,
            'total' => $total,
            'payment_status' => 'pending',
            'shipping_status' => 'pending',
            'coupon_id' => session('coupon.id', null),
            'coupon_amount' => session('coupon.amount', 0),
            'discount' => session('coupon.discount', 0),
            'payment_method' => $request->payment_method,
            'shipping_method' => $request->shipping_method,
            'products' => json_encode($products),
            'doc_type' => $request->doc_type,
            'document' => $request->doc_recep,
        ];

        if ($request->filled('estimate_id')) {
            $orderData['estimate_id'] = $request->estimate_id;
        }
        if($request->filled('delivery_offer_id')) {
            $orderData['delivery_offer_id'] = $request->delivery_offer_id;
        }

        return $orderData;
    }

    /**
     * Aplica un cupón a la sesión, limitando el descuento solo a productos elegibles.
     *
     * @param string $couponCode
     * @param float $subtotal
     * @return array
     * @throws \Exception
     */
    public function applyCouponToSession(string $couponCode, float $subtotal, string $document = null): array
    {
        Log::info('Documento recibido para validación:', ['doc' => $document]);

        $coupon = Coupon::with(['excludedProducts', 'excludedCategories'])->where('code', $couponCode)->first();

        if (!$coupon) {
            throw new \Exception('El código del cupón no existe.');
        }

        $now = now();

        // ✅ Verificar si el cupón es de un solo uso
        if ($coupon->single_use) {
          // Buscar el cliente por su documento
          $clientDocument = $document;
          $client = \App\Models\Client::where('document', $clientDocument)->first();

          if ($client) {
              $used = \App\Models\Order::where('client_id', $client->id)
                  ->where('coupon_id', $coupon->id)
                  ->exists();

              if ($used) {
                  throw new \Exception('Este cupón solo se puede usar una vez por cliente.');
              }
          }
        }


        // ✅ Verificar fechas del cupón
        if ($coupon->init_date && $now < $coupon->init_date) {
            throw new \Exception('El cupón aún no está disponible.');
        }

        if ($coupon->due_date && $now > $coupon->due_date) {
            throw new \Exception('El cupón ha expirado.');
        }

        // ✅ Obtener productos y categorías excluidos
        $excludedProductIds = $coupon->excludedProducts->pluck('id')->toArray();
        $excludedCategoryIds = $coupon->excludedCategories->pluck('id')->toArray();

        Log::info('Productos excluidos por el cupón:', $excludedProductIds);
        Log::info('Categorías excluidas por el cupón:', $excludedCategoryIds);

        // ✅ Obtener los productos del carrito y sus categorías
        $cartItems = session('cart', []);
        $cartProducts = collect($cartItems)->map(function ($item) {
            $category = DB::table('category_product')->where('product_id', $item['id'])->first();

            return [
                'id' => $item['id'],
                'price' => $item['price'] ?? $item['old_price'],
                'quantity' => $item['quantity'] ?? 1,
                'total' => ($item['price'] ?? $item['old_price']) * ($item['quantity'] ?? 1),
                'category_id' => $category ? $category->category_id : null,
            ];
        });

        Log::info('Productos en el carrito:', $cartProducts->toArray());

        // ✅ Filtrar solo los productos que SÍ PUEDEN recibir el cupón
        $eligibleProducts = $cartProducts->reject(function ($item) use ($excludedProductIds, $excludedCategoryIds) {
            return in_array($item['id'], $excludedProductIds) || in_array($item['category_id'], $excludedCategoryIds);
        });

        Log::info('Productos elegibles para el cupón:', $eligibleProducts->toArray());

        // ✅ Si no hay productos elegibles, el cupón no se puede aplicar
        if ($eligibleProducts->isEmpty()) {
            throw new \Exception('El cupón no se puede aplicar porque todos los productos en el carrito están excluidos.');
        }

        // ✅ Calcular el subtotal de los productos elegibles
        $eligibleSubtotal = $eligibleProducts->sum('total');

        Log::info('Subtotal de productos elegibles:', ['subtotal' => $eligibleSubtotal]);

        // ✅ Calcular el descuento solo sobre los productos elegibles
        $discount = $coupon->type === 'fixed'
            ? min($coupon->amount, $eligibleSubtotal)  // Si el cupón es fijo, no debe exceder el subtotal de productos elegibles
            : round($eligibleSubtotal * ($coupon->amount / 100), 2); // Si es porcentaje, se aplica solo al subtotal elegible

        Log::info('Descuento calculado:', ['discount' => $discount]);

        // ✅ Si el descuento es 0 o negativo, no se puede aplicar
        if ($discount <= 0) {
            throw new \Exception('No se pudo calcular un descuento válido.');
        }

        // ✅ Guardar el cupón en la sesión
        session([
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'amount' => $coupon->amount,
                'discount' => $discount
            ]
        ]);

        Log::info('Cupón aplicado correctamente:', ['coupon' => session('coupon')]);

        return ['code' => $coupon->code, 'discount' => $discount];
    }


}
