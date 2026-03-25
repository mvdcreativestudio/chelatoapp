<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\StoreCashRegisterRequest;
use App\Http\Requests\UpdateCashRegisterRequest;
use Illuminate\View\View;
use App\Repositories\CashRegisterRepository;
use App\Repositories\CashRegisterLogRepository;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\CashRegister;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Supplier;
use App\Models\ExpensePaymentMethod;
use App\Enums\Expense\ExpenseStatusEnum;
use App\Enums\Expense\ExpenseTemporalStatusEnum;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;


class CashRegisterController extends Controller
{
    protected $cashRegisterRepository;
    protected $cashRegisterLogRepository;

    public function __construct(CashRegisterRepository $cashRegisterRepository, CashRegisterLogRepository $cashRegisterLogRepository)
    {
        $this->cashRegisterRepository = $cashRegisterRepository;
        $this->cashRegisterLogRepository = $cashRegisterLogRepository;
    }

    /**
     * Muestra una lista de todas las cajas registradoras.
     */
    public function index()
    {
        $userId = auth()->user()->id;

        // Buscar las cajas abiertas para el usuario
        $openRegisters = $this->cashRegisterLogRepository->getOpenCashRegistersForUser($userId);

        if ($openRegisters->count() === 1 && !Session::has('open_cash_register_id')) {
            $openCashRegisterId = $openRegisters->first()->cash_register_id;
            $storeId = $this->cashRegisterRepository->findStoreByCashRegisterId($openCashRegisterId);

            Session::put('open_cash_register_id', $openCashRegisterId);
            Session::put('store_id', $storeId);
        }

        // Obtener las cajas registradoras
        $cajas = $this->cashRegisterRepository->getCashRegistersForDatatable($userId);

        return view('points-of-sales.index', compact('cajas', 'userId'));
    }


    /**
     * Agrega una caja registradora a la base de datos.
     */
    public function store(StoreCashRegisterRequest $request)
    {
        $validatedData = $request->validated();
        $cashRegister = $this->cashRegisterRepository->createCashRegister($validatedData);
        return response()->json($cashRegister, 201);
    }

    /**
     * Devuelve una caja registradora dado un id.
     */
    public function show(string $id)
    {
        $cashRegister = $this->cashRegisterRepository->getCashRegisterById($id);

        if ($cashRegister) {
            return response()->json($cashRegister);
        } else {
            return response()->json(['message' => 'Cash register not found.'], 404);
        }
    }

    /**
     * Actualiza una caja registradora ya creada.
     */
    public function update(UpdateCashRegisterRequest $request, string $id)
    {
        $validatedData = $request->validated();
        $updated = $this->cashRegisterRepository->updateCashRegister($id, $validatedData);

        if ($updated) {
            return response()->json(['message' => 'Caja registradora actualizada correctamente.']);
        } else {
            return response()->json(['message' => 'Ha ocurrido un error al intentar actualizar la caja registradora.'], 404);
        }
    }

    /**
     * Borra una caja registradora dado un id.
     */
    public function destroy(string $id)
    {
        $deleted = $this->cashRegisterRepository->deleteCashRegister($id);

        if ($deleted) {
            return response()->json(['message' => 'Caja registradora borrada exitosamente.']);
        } else {
            return response()->json(['message' => 'No se pudo encontrar la caja registradora que se deseó borrar.'], 404);
        }
    }

    /**
     * Devuelve la(s) empresa(s) a las cuales le puede abrir una caja registradora.
     */
    public function storesForCashRegister()
    {
        $stores = $this->cashRegisterRepository->storesForCashRegister();
        return response()->json($stores, 201);
    }

    /**
     * Devuelve los balances y ventas de la caja registradora con filtros.
     */
    public function getDetails(string $id, Request $request)
    {
        if (!Auth::user()->hasRole('Administrador')) {
            abort(403, 'No tienes permiso para ver los logs de la caja registradora.');
        }

        // Obtener la caja registradora
        $cashRegister = $this->cashRegisterRepository->getCashRegisterById($id);

        // Obtener detalles aplicando filtros
        $query = $this->cashRegisterRepository->getDetailsQuery($id);

        // Filtrar por estado (Abierta / Cerrada)
        if ($request->has('status') && !empty($request->status)) {
            if ($request->status == 'open') {
                $query->whereNull('close_time');
            } elseif ($request->status == 'closed') {
                $query->whereNotNull('close_time');
            }
        }

        // Filtrar por rango de fechas
        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->whereDate('open_time', '>=', $request->start_date);
        }

        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->whereDate('open_time', '<=', $request->end_date);
        }

        $details = $query->with('expenses')->get();
        $openCount = $details->whereNull('close_time')->count();
        $closedCount = $details->whereNotNull('close_time')->count();

        // Calcular ventas y gastos para cada registro
        foreach ($details as $detail) {
            if ($detail->close_time) {
                $detail->cash_sales = $detail->cash_sales ?? 0;
                $detail->pos_sales = $detail->pos_sales ?? 0;
            } else {
                $sales = \DB::table('orders')
                    ->selectRaw("
                        SUM(CASE WHEN payment_method = 'cash' THEN total ELSE 0 END) as total_cash_sales,
                        SUM(CASE WHEN payment_method IN ('credit', 'debit', 'card') THEN total ELSE 0 END) as total_pos_sales,
                        SUM(CASE WHEN payment_method = 'mercadopago' THEN total ELSE 0 END) as total_mercadopago_sales,
                        SUM(CASE WHEN payment_method = 'bankTransfer' THEN total ELSE 0 END) as total_bank_transfer_sales,
                        SUM(CASE WHEN payment_method = 'internalCredit' THEN total ELSE 0 END) as total_internal_credit_sales
                    ")
                    ->where('cash_register_log_id', $detail->id)
                    ->first();

                $detail->cash_sales = $sales->total_cash_sales ?? 0;
                $detail->pos_sales = $sales->total_pos_sales ?? 0;
                $detail->mercadopago_sales = $sales->total_mercadopago_sales ?? 0;
                $detail->bank_transfer_sales = $sales->total_bank_transfer_sales ?? 0;
                $detail->internal_credit_sales = $sales->total_internal_credit_sales ?? 0;
            }

            // Calcular gastos convertidos a pesos
            $totalExpenses = 0;
            foreach ($detail->expenses as $expense) {
                if ($expense->currency === 'Dólar') {
                    $rate = $expense->currency_rate ?? $this->getDollarExchangeRate();
                    $totalExpenses += $expense->amount * $rate;
                } else {
                    $totalExpenses += $expense->amount;
                }
            }
            $detail->setAttribute('total_expenses', $totalExpenses);
        }

        // Si la petición es AJAX, devolver solo los datos filtrados
        if ($request->ajax()) {
            return response()->json([
                'details' => $details,
                'openCount' => $openCount,
                'closedCount' => $closedCount
            ]);
        }

        return view('points-of-sales.details', compact('cashRegister', 'details', 'openCount', 'closedCount'));
    }

    /**
     * Devuelve las ventas realizadas por una caja registradora.
     */
    public function getSales($id){
        if (!Auth::user()->hasRole('Administrador')) {
            abort(403, 'No tienes permiso para ver las ventas de la caja registradora.');
        }
        $cashRegisterLog = \App\Models\CashRegisterLog::findOrFail($id);
        $sales = $this->cashRegisterRepository->getSales($id);
        $totalSales = $sales->count();

        // Calcular ventas por cada método de pago dinámicamente
        $cashSales = $sales->where('payment_method', 'cash')->sum('total');
        $posSales = $sales->whereIn('payment_method', ['credit', 'debit', 'card'])->sum('total');
        $mercadopagoSales = $sales->where('payment_method', 'mercadopago')->sum('total');
        $bankTransferSales = $sales->where('payment_method', 'bankTransfer')->sum('total');
        $internalCreditSales = $sales->where('payment_method', 'internalCredit')->sum('total');
        $totalSalesAmount = $cashSales + $posSales + $mercadopagoSales + $bankTransferSales + $internalCreditSales;

        // Obtener los egresos asociados a este log
        $cashRegisterExpenses = $cashRegisterLog->expenses()->with(['supplier', 'expenseCategory'])->get();

        // Convertir gastos a pesos
        $expenses = 0;
        foreach ($cashRegisterExpenses as $expense) {
            if ($expense->currency === 'Dólar') {
                $rate = $expense->currency_rate ?? $this->getDollarExchangeRate();
                $expenses += $expense->amount * $rate;
            } else {
                $expenses += $expense->amount;
            }
        }

        // Obtener el efectivo inicial
        $cashFloat = $cashRegisterLog->cash_float ?? 0;

        return view('points-of-sales.sales', compact(
            'sales',
            'totalSales',
            'cashSales',
            'posSales',
            'mercadopagoSales',
            'bankTransferSales',
            'internalCreditSales',
            'expenses',
            'cashFloat',
            'cashRegisterExpenses',
            'cashRegisterLog',
            'id',
            'totalSalesAmount'
        ));
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
            \Log::error('Error al obtener cotización del dólar: ' . $e->getMessage());
            return 1;
        }
    }


    /**
     * Devuelve las ventas en PDF.
     */
    public function getSalesPdf($id){
        if (!Auth::user()->hasRole('Administrador')) {
            abort(403, 'No tienes permiso para ver las ventas de la caja registradora.');
        }
        $sales = $this->cashRegisterRepository->getSales($id);
        $pdf = \PDF::loadView('points-of-sales.exportSales', compact('sales','id'));

        return $pdf->stream('cash_register_sales.pdf');
    }

    /**
     * Registra un nuevo egreso de caja.
     */
    public function storeExpense(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'concept' => 'required|string|max:255',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'expense_category_id' => 'nullable|exists:expense_categories,id',
            'observations' => 'nullable|string',
            'cash_register_log_id' => 'required|exists:cash_register_logs,id',
            'currency' => 'nullable|string',
            'currency_rate' => 'required_if:currency,Dólar|numeric',
        ]);

        try {
            // Verificar que la caja esté abierta
            $cashRegisterLog = \App\Models\CashRegisterLog::findOrFail($validated['cash_register_log_id']);

            if ($cashRegisterLog->close_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede registrar un egreso en una caja cerrada.'
                ], 400);
            }

            $currentDate = now();
            $supplierID = !empty($validated['supplier_id']) ? $validated['supplier_id'] : null;
            // expense_category_id is required in DB - use first available category as default
            $categoryID = !empty($validated['expense_category_id'])
                ? $validated['expense_category_id']
                : (ExpenseCategory::first()->id ?? 1);
            $observations = isset($validated['observations']) && $validated['observations'] !== '' ?
                             $validated['observations'] : null;
            $currency = $validated['currency'] ?? 'Peso';
            $currencyRate = ($currency === 'Dólar' && isset($validated['currency_rate'])) ?
                         $validated['currency_rate'] : 0;

            // Crear el gasto
            $expense = Expense::create([
                'amount' => $validated['amount'],
                'concept' => $validated['concept'],
                'supplier_id' => $supplierID,
                'expense_category_id' => $categoryID,
                'observations' => $observations,
                'cash_register_log_id' => $validated['cash_register_log_id'],
                'due_date' => $currentDate,
                'status' => ExpenseStatusEnum::PAID,
                'temporal_status' => ExpenseTemporalStatusEnum::DUE_TODAY,
                'store_id' => $cashRegisterLog->cashRegister->store_id,
                'currency' => $currency,
                'currency_rate' => $currencyRate,
            ]);

            // Registrar el pago automáticamente
            ExpensePaymentMethod::create([
                'expense_id' => $expense->id,
                'amount_paid' => $validated['amount'],
                'payment_date' => $currentDate,
                'payment_method_id' => 1, // Efectivo
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Egreso registrado correctamente',
                'expense' => $expense
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al registrar egreso de caja: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el egreso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene los egresos asociados a un log de caja específico.
     */
    public function getExpenses($id)
    {
        try {
            $cashRegisterLog = \App\Models\CashRegisterLog::findOrFail($id);
            $expenses = $cashRegisterLog->expenses()->with(['supplier', 'expenseCategory'])->get();

            return response()->json([
                'success' => true,
                'expenses' => $expenses,
                'total' => $expenses->sum('amount')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los egresos: ' . $e->getMessage()
            ], 500);
        }
    }
}
