<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\OrderRepository;
use App\Services\MercadoPagoService;
use App\Repositories\CheckoutRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use App\Http\Requests\CheckoutStoreOrderRequest;
use App\Http\Requests\ApplyCouponRequest;
use Illuminate\Http\RedirectResponse;
use App\Models\Order;

class CheckoutController extends Controller
{
    /**
     * El repositorio de checkout para la gestión de la finalización de compras.
     *
     * @var CheckoutRepository
     */
    protected $checkoutRepository;

    /**
     * El repositorio de ventas para la gestión de ventas.
     *
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * El servicio de MercadoPago para la gestión de pagos.
     *
     * @var MercadoPagoService
     */
    protected $mercadoPagoService;

    /**
     * Inyecta el repositorio y el servicio en el controlador.
     *
     * @param  CheckoutRepository  $checkoutRepository
     * @param  OrderRepository  $orderRepository
     * @param  MercadoPagoService  $mercadoPagoService
     */
    public function __construct(CheckoutRepository $checkoutRepository, OrderRepository $orderRepository, MercadoPagoService $mercadoPagoService)
    {
        $this->middleware(['ensure_store_selected', 'ensure_cart_not_empty'])->only('index');

        $this->checkoutRepository = $checkoutRepository;
        $this->orderRepository = $orderRepository;
        $this->mercadoPagoService = $mercadoPagoService;
    }

    /**
     * Muestra la página de checkout.
     *
     * @return View
     */
    public function index(): View
    {
        $checkout = $this->checkoutRepository->index();

        // Obtener el ID de la empresa desde la sesión
        $storeId = session('store.id');

        // Agregar Log para verificar el store_id
        Log::info('Valor de store_id en la sesión al llegar al checkout:', ['store_id' => $storeId]);

        // Verificar si el storeId es numérico
        if (!is_numeric($storeId)) {
            Log::error('El store_id en la sesión no es numérico: ' . $storeId);
            // Aquí puedes lanzar una excepción o manejar el error según lo necesites
            abort(404, 'Store ID inválido en la sesión');
        }

        // Añadir el ID de la empresa a los datos que se pasan a la vista
        $checkout['store_id'] = $storeId;

        return view('content.e-commerce.front.checkout', $checkout);
    }



    /**
     * Muestra la página de éxito de la compra.
     *
     * @param  Order  $order
     * @return View
     */
    public function success(Order $order): View
    {
        $orderData = $this->checkoutRepository->success($order->uuid);
        return view('content.e-commerce.front.checkout-success', $orderData);
    }

    /**
     * Muestra la página de fallo de la compra.
     *
     * @param  Order  $order
     * @return View
     */
    public function failure(Order $order): View
    {
        $orderData = $this->checkoutRepository->failure($order->uuid);
        return view('content.e-commerce.front.checkout-failure', $orderData);
    }

    /**
     * Almacena una nueva orden en la base de datos.
     *
     * @param  CheckoutStoreOrderRequest  $request
     * @return RedirectResponse
     */
    public function store(CheckoutStoreOrderRequest $request): RedirectResponse
    {
        return $this->checkoutRepository->processOrder($request, $this->mercadoPagoService);
    }

    /**
     * Aplica un cupón de descuento.
     *
     * @param  ApplyCouponRequest  $request
     * @return RedirectResponse
     */
    public function applyCoupon(ApplyCouponRequest $request): RedirectResponse
    {
        try {
            // Obtener el subtotal del carrito de compras desde la sesión
            $subtotal = session('subtotal', 0);
            $couponData = $this->checkoutRepository->applyCouponToSession($request->coupon_code, $subtotal);
            return back()->with('success', 'El cupón "' . $couponData['code'] . '" se ha aplicado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error applying coupon', ['coupon_code' => $request->coupon_code, 'error' => $e->getMessage()]);
            return back()->with('error', $e->getMessage());
        }
    }
}
