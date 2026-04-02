<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\StoreCashRegisterLogRequest;
use App\Http\Requests\UpdateCashRegisterLogRequest;
use App\Repositories\CashRegisterLogRepository;
use App\Repositories\CashRegisterRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreClientRequest;
use App\Models\Product;
use App\Models\CashRegister;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class CashRegisterLogController extends Controller
{

    protected $cashRegisterLogRepository;
    protected $cashRegisterRepository;

    public function __construct(CashRegisterLogRepository $cashRegisterLogRepository, CashRegisterRepository $cashRegisterRepository)
    {
        $this->cashRegisterLogRepository = $cashRegisterLogRepository;
        $this->cashRegisterRepository = $cashRegisterRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pdv.index');
    }

    public function front()
    {
      $products = Product::all();
      return view('pdv.front', compact('products'));
    }


    public function front2()
    {
      return view('pdv.front2');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Agrega un log de caja registradora a la base de datos.
     * La función del método es abrir la caja registradora ese día.
     *
     * @param StoreCashRegisterLogRequest $request
     * @param JsonResponse
     */
    public function store(StoreCashRegisterLogRequest $request)
    {

        $cashRegisterId = $request->input('cash_register_id');
        $name = $request->input('name');

        $storeId = CashRegister::findOrFail($cashRegisterId)->store_id;

        // Verificar si el usuario ya tiene una caja registradora abierta en esta tienda
        if ($this->cashRegisterLogRepository->hasOpenLogForUserInStore(Auth::id(), $storeId)) {
            return response()->json(['message' => 'Ya existe una caja registradora abierta para este usuario y sucursal.'], 400);
        }

        $validatedData = $request->validated();
        $validatedData['name'] = $name;
        $validatedData['open_time'] = now();
        $validatedData['cash_sales'] = 0;
        $validatedData['pos_sales'] = 0;
        $validatedData['mercadopago_sales'] = 0;
        $validatedData['bank_transfer_sales'] = 0;
        $validatedData['internal_credit_sales'] = 0;
        $validatedData['cash_expenses'] = 0;

        $cashRegisterLog = $this->cashRegisterLogRepository->createCashRegisterLog($validatedData);
        Session::put('open_cash_register_id', $cashRegisterId);
        return response()->json($cashRegisterLog, 201);
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Actualiza un log de una caja registradora.
     *
     * @param UpdateCashRegisterLogRequest $request
     * @param string $id
     */
    public function update(UpdateCashRegisterLogRequest $request, string $id)
    {
        $validatedData = $request->validated();
        $updated = $this->cashRegisterLogRepository->updateCashRegisterLog($id, $validatedData);

        if ($updated) {
            return response()->json(['message' => 'Cash register log updated successfully.']);
        } else {
            return response()->json(['message' => 'Cash register log not found or not updated.'], 404);
        }
    }

    /**
     * Borra un log de caja registradora dado un id.
     *
     * @param string $id
     */
    public function destroy(string $id)
    {
        $deleted = $this->cashRegisterLogRepository->deleteCashRegisterLog($id);

        if ($deleted) {
            return response()->json(['message' => 'Log de caja registradora borrada exitosamente.']);
        } else {
            return response()->json(['message' => 'No se pudo encontrar el log de la caja registradora que se deseó borrar.'], 404);
        }
    }


    /**
     * Cierre de caja.
     *
     * @param Request $request
     * @param string $id
     */
    public function closeCashRegister(Request $request, string $id)
    {
        $actualCash = $request->input('actual_cash');
        $cashDifference = $request->input('cash_difference', 0);

        $closed = $this->cashRegisterLogRepository->closeCashRegister($id, $actualCash, $cashDifference);

        if ($closed) {
            // Enviar email de cierre de caja
            try {
                $this->cashRegisterLogRepository->sendCashRegisterClosedEmail($id);
            } catch (\Exception $e) {
                \Log::warning('No se pudo enviar email de cierre: ' . $e->getMessage());
            }

            Session::forget('open_cash_register_id');

            $message = 'Caja registradora cerrada correctamente.';
            if ($cashDifference != 0) {
                $differenceText = $cashDifference > 0 ? 'Sobrante' : 'Faltante';
                $message .= " (Diferencia registrada: {$differenceText} de $" . abs($cashDifference) . ")";
            }

            return response()->json(['message' => $message]);
        } else {
            return response()->json(['message' => 'Ha ocurrido un error intentando cerrar la caja registradora.'], 404);
        }
    }


    /**
     * Genera e imprime el ticket 80mm del cierre de caja.
     */
    public function printCloseSummary($id)
    {
        try {
            $log = \App\Models\CashRegisterLog::with(['cashRegister.store', 'expenses'])->findOrFail($id);
            $store = $log->cashRegister->store;

            if (!$log->close_time) {
                return response('La caja aún no ha sido cerrada.', 400);
            }

            $logo = null;
            if ($store->logo) {
                $logoPath = storage_path('app/public/' . $store->logo);
                if (file_exists($logoPath)) {
                    $logo = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
                }
            }

            $closedBy = null;
            if ($log->user_id) {
                $user = \App\Models\User::find($log->user_id);
                $closedBy = $user ? $user->name : null;
            }

            $html = view('pdv.cash-register-close-80mm', [
                'log'      => $log,
                'store'    => $store,
                'logo'     => $logo,
                'closedBy' => $closedBy,
            ])->render();

            $paperWidth = 204.094; // 72mm en puntos

            $pdf = Pdf::loadHTML($html)
                ->setPaper([0, 0, $paperWidth, 1000], 'portrait')
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isRemoteEnabled', true)
                ->setOption('defaultPaperSize', 'custom')
                ->setOption('enable_auto_height', true)
                ->setOption('margin-top', 0)
                ->setOption('margin-bottom', 0)
                ->setOption('margin-left', 0)
                ->setOption('margin-right', 0);

            return $pdf->stream("cierre_caja_{$log->id}.pdf");
        } catch (\Throwable $e) {
            Log::error('Error al imprimir PDF 80mm del cierre de caja.', [
                'log_id' => $id,
                'error'  => $e->getMessage(),
            ]);

            return response($e->getMessage(), 500)->header('Content-Type', 'text/plain; charset=UTF-8');
        }
    }

    /**
     * Toma los productos de la empresa de la caja registradora.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductsByCashRegister(int $id)
    {
        $products = $this->cashRegisterLogRepository->getAllProductsForPOS($id);
        return response()->json(['products' => $products]);
    }

    /**
     * Toma los productos de la empresa de la caja registradora.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFlavorsForCashRegister()
    {
        try {
            $flavors = $this->cashRegisterLogRepository->getFlavors();
            return response()->json(['flavors' => $flavors]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }


    /**
     * Toma las categorías padres.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFathersCategories()
    {
        try {
            $categories = $this->cashRegisterLogRepository->getFathersCategories();
            return response()->json(['categories' => $categories]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    /**
     * Toma las categorías padres.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategories()
    {
        try {
            $productCategories = $this->cashRegisterLogRepository->getCategories();
            return response()->json($productCategories);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }


    /**
   * Almacena un nuevo cliente en la base de datos.
   *
   * @param StoreClientRequest $request
   * @return JsonResponse
  */
  public function storeClient(StoreClientRequest $request): JsonResponse
  {
    try {
      $validatedData = $request->validated();

      // Establecer valores predeterminados si no están presentes en la solicitud
      $validatedData['address'] = $validatedData['address'] ?? '-';
      $validatedData['city'] = $validatedData['city'] ?? '-';
      $validatedData['state'] = $validatedData['state'] ?? '-';
      $validatedData['country'] = $validatedData['country'] ?? '-';
      $validatedData['phone'] = $validatedData['phone'] ?? '-';

      // Crear el nuevo cliente
      $newClient = $this->cashRegisterLogRepository->createClient($validatedData);

      // Agregar log
      Log::info('Nuevo cliente creado desde PDV', [
          'client_id' => $newClient->id,
          'name' => $newClient->name,
          'email' => $newClient->email,
          'type' => $newClient->type
      ]);

      return response()->json([
        'success' => true,
        'message' => 'Cliente creado correctamente.',
        'client' => $newClient  // Devuelve los datos completos del cliente
    ]);
    } catch (\Exception $e) {
        Log::error('Error al crear cliente desde PDV: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Ocurrió un error al crear el cliente.']);
    }
  }

    /**
     * Busca el id del cashregister log y el store_id dado un id de caja registradora.
     *
     * @param string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCashRegisterLog(string $id)
    {
        try {
            $result = $this->cashRegisterLogRepository->getCashRegisterLogWithStore($id);
            if ($result === null) {
                return response()->json(['error' => 'No open log found'], 404);
            }
            return response()->json($result); // Devuelve tanto el cash_register_log_id como el store_id
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    /**
     * Obtiene todos los clientes en formato JSON.
     *
     * @return JsonResponse
     */
    public function getAllClients(): JsonResponse
    {
        $clients = $this->cashRegisterLogRepository->getAllClients();
        return response()->json([
            'clients' => $clients,
            'count' => $clients->count()
        ]);
    }

    /**
     * Guarda el carrito del PDV de la session.
     *
     * @return JsonResponse
     */
    public function saveCart(Request $request)
    {
        $cart = $request->input('cart');
        session(['cart' => $cart]);
        return response()->json(['status' => 'success']);
    }

    /**
     * Devuelve el carrito del PDV de la session.
     *
     * @return JsonResponse
     */
    public function getCart()
    {
        $cart = session('cart', []);
        return response()->json(['cart' => $cart]);
    }

     /**
     * Guarda el cliente del PDV de la session.
     *
     * @return JsonResponse
     */
    public function saveClient(Request $request)
    {
        $client = $request->input('client');
        session(['client' => $client]);
        return response()->json(['status' => 'success']);
    }

    /**
     * Devuelve el cliente del PDV de la session.
     *
     * @return JsonResponse
     */
    public function getClient()
    {
        $client = session('client', []);
        return response()->json(['client' => $client]);
    }

    /**
     * Devuelve el Store ID de la session.
     *
     * @return JsonResponse
     */
    public function getStoreId()
    {
        $id = session('store_id');

        return response()->json(['id' => $id]);
    }

    /**
     * Setea la caja registradora activa en la sesión.
     */
    public function setCashRegister(Request $request)
    {
        $request->validate([
            'cash_register_id' => 'required|exists:cash_registers,id',
        ]);

        $cashRegisterId = $request->input('cash_register_id');
        $storeId = $this->cashRegisterRepository->findStoreByCashRegisterId($cashRegisterId);

        Session::put('open_cash_register_id', $cashRegisterId);
        Session::put('store_id', $storeId);

        return response()->json(['status' => 'success']);
    }

    /**
     * Devuelve los tax rates.
     */
    public function taxRates()
    {
        try {
            if (class_exists('\App\Models\TaxRate')) {
                $taxRates = \App\Models\TaxRate::all();
            } else {
                $taxRates = [];
            }
            return response()->json(['taxRates' => $taxRates]);
        } catch (\Exception $e) {
            return response()->json(['taxRates' => []]);
        }
    }

    /**
     * Obtiene una lista paginada de clientes con búsqueda opcional.
     */
    public function getPaginatedClients(Request $request): JsonResponse
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 12);
        $search = $request->get('search', '') ?? '';

        $query = $this->cashRegisterLogRepository->getClientsQuery($search);

        $clients = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'clients' => $clients->items(),
            'current_page' => $clients->currentPage(),
            'last_page' => $clients->lastPage(),
            'total' => $clients->total(),
            'has_more' => $clients->hasMorePages()
        ]);
    }

    /**
     * Obtiene los detalles de un registro de caja específico (para el modal de cierre).
     */
    public function getDetails($id)
    {
        try {
            $cashRegisterLog = \App\Models\CashRegisterLog::with(['expenses', 'cashRegister.store'])->findOrFail($id);

            // Calcular ventas dinámicamente si la caja está abierta
            if ($cashRegisterLog->close_time) {
                $cashSales = $cashRegisterLog->cash_sales ?? 0;
                $posSales = $cashRegisterLog->pos_sales ?? 0;
                $mercadopagoSales = $cashRegisterLog->mercadopago_sales ?? 0;
                $bankTransferSales = $cashRegisterLog->bank_transfer_sales ?? 0;
                $internalCreditSales = $cashRegisterLog->internal_credit_sales ?? 0;
            } else {
                $sales = DB::table('orders')
                    ->selectRaw("
                        SUM(CASE WHEN payment_method = 'cash' THEN
                            CASE WHEN payment_status = 'partial_refunded' THEN
                                total - COALESCE((SELECT SUM(c.total) FROM cfes c WHERE c.order_id = orders.id AND c.type IN (102, 112)), 0)
                            ELSE total END
                        ELSE 0 END) as total_cash_sales,
                        SUM(CASE WHEN payment_method IN ('credit', 'debit', 'card') THEN
                            CASE WHEN payment_status = 'partial_refunded' THEN
                                total - COALESCE((SELECT SUM(c.total) FROM cfes c WHERE c.order_id = orders.id AND c.type IN (102, 112)), 0)
                            ELSE total END
                        ELSE 0 END) as total_pos_sales,
                        SUM(CASE WHEN payment_method = 'mercadopago' THEN
                            CASE WHEN payment_status = 'partial_refunded' THEN
                                total - COALESCE((SELECT SUM(c.total) FROM cfes c WHERE c.order_id = orders.id AND c.type IN (102, 112)), 0)
                            ELSE total END
                        ELSE 0 END) as total_mercadopago_sales,
                        SUM(CASE WHEN payment_method = 'bankTransfer' THEN
                            CASE WHEN payment_status = 'partial_refunded' THEN
                                total - COALESCE((SELECT SUM(c.total) FROM cfes c WHERE c.order_id = orders.id AND c.type IN (102, 112)), 0)
                            ELSE total END
                        ELSE 0 END) as total_bank_transfer_sales,
                        SUM(CASE WHEN payment_method = 'internalCredit' THEN
                            CASE WHEN payment_status = 'partial_refunded' THEN
                                total - COALESCE((SELECT SUM(c.total) FROM cfes c WHERE c.order_id = orders.id AND c.type IN (102, 112)), 0)
                            ELSE total END
                        ELSE 0 END) as total_internal_credit_sales
                    ")
                    ->where('cash_register_log_id', $cashRegisterLog->id)
                    ->where('payment_status', '!=', 'refunded')
                    ->first();

                $cashSales = $sales->total_cash_sales ?? 0;
                $posSales = $sales->total_pos_sales ?? 0;
                $mercadopagoSales = $sales->total_mercadopago_sales ?? 0;
                $bankTransferSales = $sales->total_bank_transfer_sales ?? 0;
                $internalCreditSales = $sales->total_internal_credit_sales ?? 0;
            }

            // Calcular el total de gastos convertidos a pesos
            $totalExpenses = 0;
            foreach ($cashRegisterLog->expenses as $expense) {
                if ($expense->currency === 'Dólar') {
                    $rate = $expense->currency_rate ?? $this->getDollarExchangeRate();
                    $totalExpenses += $expense->amount * $rate;
                } else {
                    $totalExpenses += $expense->amount;
                }
            }

            // Calcular el efectivo final
            $cashFloat = $cashRegisterLog->cash_float ?? 0;
            $finalCashBalance = $cashFloat + $cashSales - $totalExpenses;
            $totalSalesAmount = $cashSales + $posSales + $mercadopagoSales + $bankTransferSales + $internalCreditSales;

            $details = [
                'id' => $cashRegisterLog->id,
                'name' => $cashRegisterLog->name ?? 'Sin nombre',
                'open_time' => $cashRegisterLog->open_time ? $cashRegisterLog->open_time->format('d/m/Y H:i') : 'No disponible',
                'close_time' => $cashRegisterLog->close_time ? $cashRegisterLog->close_time->format('d/m/Y H:i') : null,
                'cash_float' => $cashFloat,
                'cash_sales' => $cashSales,
                'pos_sales' => $posSales,
                'mercadopago_sales' => $mercadopagoSales,
                'bank_transfer_sales' => $bankTransferSales,
                'internal_credit_sales' => $internalCreditSales,
                'total_expenses' => $totalExpenses,
                'final_cash_balance' => $finalCashBalance,
                'total_sales' => $totalSalesAmount,
                'store_name' => $cashRegisterLog->cashRegister->store->name ?? 'Sin tienda'
            ];

            return response()->json(['success' => true, 'details' => $details]);
        } catch (\Exception $e) {
            \Log::error('Error al obtener los detalles de la caja: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener los detalles de la caja.'], 500);
        }
    }

    /**
     * Obtiene la cotización actual del dólar.
     */
    private function getDollarExchangeRate(): float
    {
        try {
            $dollarRate = \App\Models\CurrencyRate::where('name', 'Dólar')->first();

            if (!$dollarRate) {
                return 1;
            }

            $latestRate = \App\Models\CurrencyRateHistory::where('currency_rate_id', $dollarRate->id)
                ->orderBy('date', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();

            return $latestRate ? $latestRate->sell : 1;
        } catch (\Exception $e) {
            return 1;
        }
    }
}
