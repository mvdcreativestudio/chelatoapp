<?php

namespace App\Repositories;

use App\Enums\CurrentAccounts\StatusPaymentEnum;
use App\Enums\CurrentAccounts\TransactionTypeEnum;
use App\Helpers\Helpers;
use App\Models\Client;
use App\Models\CurrentAccount;
use App\Models\CurrentAccountInitialCredit;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderStatusChange;
use App\Models\Product;
use App\Repositories\AccountingRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class OrderRepository
{
    /**
     * El repositorio de contabilidad.
     *
     * @var AccountingRepository
     */
    protected $accountingRepository;

    /**
     * Inyecta el repositorio de contabilidad.
     *
     * @param AccountingRepository $accountingRepository
     */
    public function __construct(AccountingRepository $accountingRepository)
    {
        $this->accountingRepository = $accountingRepository;
    }

    /**
     * Obtiene todos los pedidos y las estadísticas necesarias para las cards.
     *
     * @return array
     */
    public function getAllOrders(): array
    {
        // Verificar si el usuario tiene permiso para ver todos los pedidos de la tienda
        if (Auth::user()->can('view_all_ecommerce')) {
            // Si tiene el permiso, obtenemos todos los pedidos
            $orders = Order::all();
        } else {
            // Si no tiene el permiso, solo obtenemos los pedidos de su store_id
            $orders = Order::where('store_id', Auth::user()->store_id)->get();
        }

        // Calcular las estadísticas basadas en los pedidos filtrados
        $totalOrders = $orders->count();
        $totalIncome = $orders->where('payment_status', 'paid')->sum('total');
        $pendingOrders = $orders->where('shipping_status', 'pending')->count();
        $shippedOrders = $orders->where('shipping_status', 'shipped')->count();
        $completedOrders = $orders->where('shipping_status', 'completed')->count();

        return compact('orders', 'totalOrders', 'totalIncome', 'pendingOrders', 'shippedOrders', 'completedOrders');
    }

    /**
     * Almacena un nuevo pedido en la base de datos.
     *
     * @param  StoreOrderRequest  $request
     * @return Order
     */
    public function store($request)
    {
        Log::info('Iniciando el proceso de creación de orden', ['request' => $request->all()]);

        $clientData = $this->extractClientData($request->validated());
        $orderData = $this->prepareOrderData($request->payment_method, $request);

        DB::beginTransaction();

        try {
            // Depurar la información del cliente y los datos de la orden antes de guardar
            Log::info('Datos del cliente extraídos', ['clientData' => $clientData]);
            Log::info('Datos de la orden preparados', ['orderData' => $orderData]);

            $client = Client::firstOrCreate(['email' => $clientData['email']], $clientData);
            Log::info('Cliente creado o encontrado', ['client_id' => $client->id]);

            $order = new Order($orderData);
            $order->client()->associate($client);
            Log::info('Orden creada, asociada al cliente', ['order' => $order]);

            $order->save();
            Log::info('Orden guardada en la base de datos', ['order_id' => $order->id]);

            $products = json_decode($request['products'], true);
            $order->products = $products;
            Log::info('Productos asociados a la orden', ['products' => $products]);

            $order->save();
            Log::info('Orden actualizada con los productos');

            // if payment_method is internalCredit
            if ($request->payment_method === 'internalCredit') {
                $this->createInternalCredit($order);
            }

            DB::commit();
            Log::info('Transacción de base de datos confirmada');

            session()->forget('cart');

            $store = $order->store;
            Log::info('Información de la tienda recuperada', ['store' => $store]);

            // Verificar si se debe emitir una factura electrónica (CFE)
            if ($store->automatic_billing) {
                $this->accountingRepository->emitCFE($order);
                Log::info('Factura electrónica emitida', ['order_id' => $order->id]);
                $order->update(['is_billed' => true]);
            } else {
                Log::info('No se emite factura electrónica para esta orden');
                $order->update(['is_billed' => false]);
            }
            return $order;
        } catch (\Exception $e) {
            Log::error('Error durante la creación de la orden', ['exception' => $e->getMessage()]);
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Prepar los datos del cliente para ser almacenados en la base de datos.
     *
     * @param array $validatedData
     * @return array
     */
    private function extractClientData(array $validatedData): array
    {
        Log::info('Extrayendo datos del cliente', ['validatedData' => $validatedData]);

        $clientData = [
            'name' => $validatedData['name'],
            'lastname' => $validatedData['lastname'],
            'type' => 'individual',
            'state' => 'Montevideo',
            'country' => 'Uruguay',
            'address' => $validatedData['address'],
            'phone' => $validatedData['phone'],
            'email' => $validatedData['email'],
        ];

        Log::info('Datos del cliente procesados', ['clientData' => $clientData]);

        return $clientData;
    }

    /**
     * Prepara los datos del pedido para ser almacenados en la base de datos.
     *
     * @param string $paymentMethod
     * @param Request $request
     * @return array
     */
    private function prepareOrderData(string $paymentMethod, $request): array
    {
        $subtotal = array_reduce(session('cart', []), function ($carry, $item) {
            return $carry + ($item['price'] ?? $item['old_price']) * $item['quantity'];
        }, 0);

        Log::info('Request de prepareOrderData', ['request' => $request->all()]);

        return [
            'date' => now(),
            'time' => now()->format('H:i:s'),
            'origin' => 'physical',
            'store_id' => $request->store_id,
            'subtotal' => $subtotal,
            'tax' => 0,
            'shipping' => session('costoEnvio', 0),
            'discount' => $request->discount,
            'coupon_id' => $request->coupon_id,
            'coupon_amount' => $request->coupon_amount,
            'total' => $subtotal + session('costoEnvio', 0) - $request->discount,
            'payment_status' => 'paid',
            'shipping_status' => $request->shipping_status ?? 'delivered',
            'payment_method' => $paymentMethod,
            'shipping_method' => 'peya',
            'doc_type' => $request->doc_type,
            'document' => $request->document,
            'cash_register_log_id' => $request->cash_register_log_id,
        ];
    }

    /**
     * Carga las relaciones de un pedido.
     *
     * @param Order $order
     * @return Order
     */
    public function loadOrderRelations(Order $order)
    {
        // Cargar las relaciones necesarias
        return $order->load([
            'client',
            'statusChanges.user',
            'store',
            'coupon',
            'cashRegisterLog.cashRegister.user',
        ]);
    }

    /**
     * Elimina un pedido específico y reintegra el stock de los productos.
     *
     * @param int $orderId
     * @return void
     * @throws \Exception
     */
    public function destroyOrder($orderId): void
    {
        DB::beginTransaction();
        try {
            $order = Order::findOrFail($orderId);
            if ($order->payment_status === 'paid' && $order->shipping_status === 'delivered') {
                // Reintegrar el stock de los productos
                $products = json_decode($order->products, true);
                foreach ($products as $product) {
                    $productModel = Product::find($product['id']);
                    if ($productModel) {
                        $productModel->stock += $product['quantity'];
                        $productModel->save();
                    }
                }
            }
            // Eliminar la orden
            $order->delete();

            DB::commit();
            Log::info("Orden {$orderId} eliminada y stock reintegrado correctamente.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al eliminar la orden {$orderId}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene los pedidos para la DataTable.
     *
     * @return mixed
     */
    public function getOrdersForDataTable(Request $request): mixed
    {
        $query = Order::select([
            'orders.id',
            'orders.uuid',
            'orders.date',
            'orders.time',
            'orders.client_id',
            'orders.store_id',
            'orders.subtotal',
            'orders.tax',
            'orders.is_billed',
            'orders.shipping',
            'orders.coupon_id',
            'orders.coupon_amount',
            'orders.discount',
            'orders.total',
            'orders.products',
            'orders.payment_status',
            'orders.shipping_status',
            'orders.payment_method',
            'orders.shipping_method',
            'orders.shipping_tracking',
            'clients.email as client_email',
            'stores.name as store_name',
            DB::raw("CONCAT(clients.name, ' ', clients.lastname) as client_name"),
        ])
        ->join('clients', 'orders.client_id', '=', 'clients.id')
        ->join('stores', 'orders.store_id', '=', 'stores.id');

        // Verificar permisos del usuario
        if (!Auth::user()->can('view_all_ecommerce')) {
            $query->where('orders.store_id', Auth::user()->store_id);
        }

        // Filtrar por rango de fechas
        if (Helpers::validateDate($request->input('start_date')) && Helpers::validateDate($request->input('end_date'))) {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $query->whereBetween('orders.date', [$startDate, $endDate]);
        }

        // Aplicar siempre el orden descendente por fecha y hora
        $query->orderBy('orders.date', 'desc')
              ->orderBy('orders.time', 'desc'); // Orden adicional por hora si las fechas son iguales

        $dataTable = DataTables::of($query)->make(true);

        return $dataTable;
    }


    /**
     * Obtiene los productos de un pedido para la DataTable.
     *
     * @param Order $order
     * @return mixed
     */
    public function getOrderProductsForDataTable(Order $order)
    {
        $query = OrderProduct::where('order_id', $order->id)
            ->with([
                'product.categories:id,name',
                'product.store:id,name',
                'product.flavors:id,name',
            ])
            ->select(['id', 'product_id', 'quantity', 'price']);

        return DataTables::of($query)
            ->addColumn('product_name', function ($orderProduct) {
                $productName = $orderProduct->product->name;
                $flavors = $orderProduct->product->flavors->pluck('name')->implode(', ');
                return $flavors ? $productName . "<br><small>$flavors</small>" : $productName;
            })
            ->addColumn('category', function ($orderProduct) {
                return $orderProduct->product->categories->implode('name', ', ');
            })
            ->addColumn('store_name', function ($orderProduct) {
                return $orderProduct->product->store->name;
            })
            ->addColumn('total_product', function ($orderProduct) {
                return number_format($orderProduct->quantity * $orderProduct->price, 2);
            })
            ->rawColumns(['product_name']) // Indica a DataTables que no escape HTML en la columna 'product_name'
            ->make(true);
    }

    /**
     * Obtiene el conteo de ordenes del cliente.
     *
     * @param int $clientId
     * @return int
     */
    public function getClientOrdersCount(int $clientId): int
    {
        return Order::where('client_id', $clientId)->count();
    }

    /**
     * Actualiza el estado del pago de un pedido.
     *
     * @param int $orderId
     * @param string $paymentStatus
     * @return Order
     */
    public function updatePaymentStatus(int $orderId, string $paymentStatus): Order
    {
        $order = Order::findOrFail($orderId);
        $oldStatus = $order->payment_status;

        // Verificar si hay un cambio en el estado de pago
        if ($oldStatus !== $paymentStatus) {
            $order->payment_status = $paymentStatus;
            $order->save();
            // Registrar el cambio de estado
            OrderStatusChange::create([
                'order_id' => $orderId,
                'user_id' => Auth::id(),
                'change_type' => 'payment',
                'old_status' => $oldStatus,
                'new_status' => $paymentStatus,
            ]);
        }

        return $order;
    }

    /**
     * Actualiza el estado del envío de un pedido.
     *
     * @param int $orderId
     * @param string $shippingStatus
     * @return Order
     */
    public function updateShippingStatus(int $orderId, string $shippingStatus): Order
    {
        $order = Order::findOrFail($orderId);
        $oldStatus = $order->shipping_status;

        // Verificar si hay un cambio en el estado de envío
        if ($oldStatus !== $shippingStatus) {
            $order->shipping_status = $shippingStatus;
            $order->save();
            // Registrar el cambio de estado
            OrderStatusChange::create([
                'order_id' => $orderId,
                'user_id' => Auth::id(),
                'change_type' => 'shipping',
                'old_status' => $oldStatus,
                'new_status' => $shippingStatus,
            ]);
        }

        return $order;
    }

    /**
     * Emite un CFE para una orden.
     *
     * @param int $orderId
     * @param Request $request
     * @return void
     * @throws Exception
     */
    public function emitCFE(int $orderId, Request $request): void
    {
        $order = Order::findOrFail($orderId);

        $amountToBill = $request->amountToBill ?? $order->total;

        if ($amountToBill > $order->total) {
            throw new Exception('El monto a facturar no puede ser mayor que el total de la orden.');
        }

        $payType = $request->payType ?? 1;

        $this->accountingRepository->emitCFE($order, $amountToBill, $payType);

        $order->update(['is_billed' => true]);
    }

    public function getOrdersForExport($client, $company, $payment, $billed, $startDate, $endDate)
    {
        $query = Order::select([
            'orders.id',
            'orders.uuid',
            'orders.date',
            'orders.time',
            'orders.client_id',
            'orders.store_id',
            'orders.subtotal',
            'orders.tax',
            'orders.is_billed',
            'orders.shipping',
            'orders.coupon_id',
            'orders.coupon_amount',
            'orders.discount',
            'orders.total',
            'orders.products',
            'orders.payment_status',
            'orders.shipping_status',
            'orders.payment_method',
            'orders.shipping_method',
            'orders.shipping_tracking',
            'clients.email as client_email',
            'stores.name as store_name',
            DB::raw("CONCAT(clients.name, ' ', clients.lastname) as client_name"),
        ])
            ->join('clients', 'orders.client_id', '=', 'clients.id')
            ->join('stores', 'orders.store_id', '=', 'stores.id');

        // Aplicar los filtros
        if ($client) {
            $query->where(DB::raw("CONCAT(clients.name, ' ', clients.lastname)"), 'like', "%$client%");
        }
        if ($company) {
            $query->where('stores.name', 'like', "%$company%");
        }
        if ($payment) {
            $query->where('orders.payment_status', $payment);
        }
        if ($billed !== null) {
            $query->where('orders.is_billed', $billed == 'Facturado' ? 1 : 0);
        }
        if ($startDate && $endDate) {
            $query->whereBetween('orders.date', [$startDate, $endDate]);
        }

        return $query->get(); // Retornar los resultados
    }

    private function createInternalCredit(Order $order)
    {
        // verify if exist client in current account
        $currentAccount = CurrentAccount::where('client_id', $order->client_id)->first();

        if ($currentAccount) {
            CurrentAccountInitialCredit::create([
                'total_debit' => $order->total,
                'description' => 'Compra Interna',
                'current_account_id' => $currentAccount->id,
                'current_account_settings_id' => 1,
            ]);
        } else {
            $currentAccount = CurrentAccount::create([
                'client_id' => $order->client_id,
                'payment_total_debit' => $order->total,
                'status' => StatusPaymentEnum::UNPAID,
                'transaction_type' => TransactionTypeEnum::SALE,
                'currency_id' => 1,
            ]);

            CurrentAccountInitialCredit::create([
                'total_debit' => $order->total,
                'description' => 'Compra Interna',
                'current_account_id' => $currentAccount->id,
                'current_account_settings_id' => 1,
            ]);
        }
    }
}
