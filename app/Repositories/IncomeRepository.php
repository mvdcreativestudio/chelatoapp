<?php

namespace App\Repositories;

use App\Exceptions\CFEException;
use App\Helpers\Helpers;
use App\Models\Client;
use App\Models\Currency;
use App\Models\Income;
use App\Models\IncomeCategory;
use App\Models\PaymentMethod;
use App\Models\Supplier;
use App\Models\CashRegisterLog;
use App\Models\TaxRate;
use App\Models\CurrencyRate;
use App\Models\CurrencyRateHistory;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class IncomeRepository
{

    /**
     * El repositorio de contabilidad.
     *
     * @var AccountingRepository
     */
    protected $accountingRepository;


    public function __construct(AccountingRepository $accountingRepository)
    {
        $this->accountingRepository = $accountingRepository;
    }

    /**
     * Obtiene todos los ingresos.
     *
     * @param int $cashRegisterLogId
     * @return mixed
     */
    public function getAllIncomes(int $cashRegisterLogId): array
    {
        $incomes = Income::where('cash_register_log_id', $cashRegisterLogId)
            ->orderBy('id', 'desc')
            ->get();

        $totalIncomes = Income::where('cash_register_log_id', $cashRegisterLogId)
            ->count();

        $totalIncomeAmount = Income::where('cash_register_log_id', $cashRegisterLogId)
            ->sum('income_amount');

        // Obtener todos los datos necesarios para los forms
        $paymentMethods = PaymentMethod::all();
        $incomeCategories = IncomeCategory::all();
        $clients = Client::all();
        $suppliers = Supplier::all();
        $taxes = TaxRate::all();

        return [
            'incomes' => $incomes,
            'totalIncomes' => $totalIncomes,
            'totalIncomeAmount' => $totalIncomeAmount,
            'paymentMethods' => $paymentMethods,
            'incomeCategories' => $incomeCategories,
            'clients' => $clients,
            'suppliers' => $suppliers,
            'taxes' => $taxes
        ];
    }

    /**
     * Almacena un nuevo ingreso en la base de datos.
     *
     * @param  array  $data
     * @return Income
     */
    public function store(array $data): Income
    {
        // Verificar si hay un log abierto
        $openCashRegisterLog = CashRegisterLog::whereHas('cashRegister', function($query) {
            $query->where('user_id', auth()->id());
        })->whereNull('close_time')->first();

        if (!$openCashRegisterLog) {
            throw new Exception('No hay una caja registradora abierta.');
        }

        
        DB::beginTransaction();
        try {
            // Calcular monto total de items
            $incomeAmount = 0;
            foreach ($data['items'] as $item) {
                $incomeAmount += $item['price'] * $item['quantity'];
            }

            // Verificar tax_rate del cliente si existe
            if (!empty($data['client_id'])) {
                $client = Client::find($data['client_id']);
                if ($client && $client->tax_rate_id) {
                    $clientTax = TaxRate::find($client->tax_rate_id);
                    if ($clientTax && $clientTax->rate == 0) {
                        // Si el cliente tiene tasa 0%, no se aplican impuestos
                        $data['tax_rate_id'] = $client->tax_rate_id;
                    }
                    // Si el cliente tiene otra tasa, se mantiene la del income
                }
            }

            // Calcular impuestos si aplica
            if (!empty($data['tax_rate_id'])) {
                $tax = TaxRate::find($data['tax_rate_id']);
                if ($tax && $tax->rate > 0) {
                    $taxAmount = $incomeAmount * ($tax->rate / 100);
                    $incomeAmount += $taxAmount;
                }
            }

            $income = Income::create([
                'income_name' => $data['income_name'],
                'income_description' => $data['income_description'] ?? null,
                'income_date' => $data['income_date'],
                'income_amount' => $incomeAmount,
                'payment_method_id' => $data['payment_method_id'],
                'income_category_id' => $data['income_category_id'],
                'currency' => $data['currency'],
                'currency_rate' => $data['currency_rate'] ?? null,
                'tax_rate_id' => $data['tax_rate_id'] ?? null,
                'client_id' => $data['client_id'] ?? null,
                'supplier_id' => $data['supplier_id'] ?? null,
                'items' => $data['items'],
                'cash_register_log_id' => $openCashRegisterLog->id
            ]);

            DB::commit();
            return $income;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtiene un ingreso específico por su ID.
     *
     * @param int $incomeId
     * @return Income
     */
    public function getIncomeById(int $incomeId): Income
    {
        return Income::findOrFail($incomeId)->load('client', 'supplier', 'paymentMethod', 'incomeCategory');
    }

    /**
     * Actualiza un ingreso específico en la base de datos.
     *
     * @param int $incomeId
     * @param array $data
     * @return Income
     */
    public function update(int $incomeId, array $data): Income
    {
        DB::beginTransaction();
        try {
            // Calcular monto total de items
            $incomeAmount = 0;
            foreach ($data['items'] as $item) {
                $incomeAmount += $item['price'] * $item['quantity'];
            }

            // Verificar tax_rate del cliente si existe
            if (!empty($data['client_id'])) {
                $client = Client::find($data['client_id']);
                if ($client && $client->tax_rate_id) {
                    $clientTax = TaxRate::find($client->tax_rate_id);
                    if ($clientTax && $clientTax->rate == 0) {
                        // Si el cliente tiene tasa 0%, no se aplican impuestos
                        $data['tax_rate_id'] = $client->tax_rate_id;
                    }
                    // Si el cliente tiene otra tasa, se mantiene la del income
                }
            }

            // Calcular impuestos si aplica
            if (!empty($data['tax_rate_id'])) {
                $tax = TaxRate::find($data['tax_rate_id']);
                if ($tax && $tax->rate > 0) {
                    $taxAmount = $incomeAmount * ($tax->rate / 100);
                    $incomeAmount += $taxAmount;
                }
            }

            // Buscar y actualizar el ingreso
            $income = Income::findOrFail($incomeId);
            $income->update([
                'income_name' => $data['income_name'],
                'income_description' => $data['income_description'] ?? null,
                'income_date' => $data['income_date'],
                'income_amount' => $incomeAmount,
                'payment_method_id' => $data['payment_method_id'],
                'income_category_id' => $data['income_category_id'],
                'currency' => $data['currency'],
                'exchange_rate' => $data['exchange_rate'],
                'tax_rate_id' => $data['tax_rate_id'] ?? null,
                'client_id' => $data['client_id'] ?? null,
                'supplier_id' => $data['supplier_id'] ?? null,
                'items' => $data['items'],
                'cash_register_log_id' => $income->cash_register_log_id
            ]);

            DB::commit();
            return $income;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Elimina un ingreso específico.
     *
     * @param int $incomeId
     * @return void
     */
    public function destroyIncome(int $incomeId): void
    {
        $income = Income::findOrFail($incomeId);
        $income->delete();
    }

    /**
     * Elimina varios ingresos.
     *
     * @param array $incomeIds
     * @return void
     */
    public function deleteMultipleIncomes(array $incomeIds): void
    {
        DB::beginTransaction();

        try {
            // Eliminar los ingresos
            Income::whereIn('id', $incomeIds)->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtiene los ingresos para la DataTable.
     *
     * @param Request $request
     * @return mixed
     */
    public function getIncomesForDataTable(Request $request): mixed
    {
        // Verificar cash_register_log abierto
        $openCashRegisterLog = CashRegisterLog::whereHas('cashRegister', function($query) {
                $query->where('user_id', auth()->id());
            })
            ->whereNull('close_time')
            ->first();

        if (!$openCashRegisterLog) {
            throw new \Exception('No hay una caja registradora abierta.');
        }

        $query = Income::select([
            'incomes.*',
            'clients.name as client_name',
            'clients.lastname as client_lastname',
            'suppliers.name as supplier_name',
            'income_categories.income_name as income_category_name',
            'payment_methods.description as payment_method_name',
        ])
            ->leftJoin('clients', 'incomes.client_id', '=', 'clients.id')
            ->leftJoin('suppliers', 'incomes.supplier_id', '=', 'suppliers.id')
            ->leftJoin('income_categories', 'incomes.income_category_id', '=', 'income_categories.id')
            ->leftJoin('payment_methods', 'incomes.payment_method_id', '=', 'payment_methods.id')
            ->leftJoin('cash_register_logs', 'incomes.cash_register_log_id', '=', 'cash_register_logs.id')
            ->leftJoin('cash_registers', 'cash_register_logs.cash_register_id', '=', 'cash_registers.id')
            ->where('cash_registers.store_id', auth()->user()->store_id)
            ->where('incomes.cash_register_log_id', $openCashRegisterLog->id)
            ->orderBy('incomes.id', 'desc');
        
        if ($request->input('income_category_id')) {
            $query->where('incomes.income_category_id', $request->input('income_category_id'));
        }
        // Filtrar por rango de fechas
        if (Helpers::validateDate($request->input('start_date')) && Helpers::validateDate($request->input('end_date'))) {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $query->whereBetween('incomes.income_date', [$startDate, $endDate]);
        }

        return DataTables::of($query)
        // Columnas deshabilitadas porque aca no funcionaban. En Sumeria si. 
        /*->addColumn('last_invoice', function ($income) {
            $lastInvoice = $income->cfes()
            ->orderBy('created_at', 'desc')
            ->first();

            return $lastInvoice ? $lastInvoice->toArray() : null;
        })
        ->addColumn('return_note_credit', function ($income) {
            return $income->return_note_credit;
        })
        ->addColumn('can_emit_invoice', function ($income) {
            return $income->canEmitInvoice();
        })
        ->addColumn('can_emit_credit_note', function ($income) {
            return $income->canEmitCreditNote();
        })
        ->addColumn('can_emit_receipt', function ($income) {
            return $income->canEmitReceipt();
        })*/
        ->make(true);
    }

    /**
     * Maneja la emisión de la factura (CFE).
     *
     * @param int $incomeId
     * @param Request $request
     * @return void
     */

    public function emitCFE(int $incomeId, Request $request): RedirectResponse
    {
        $income = Income::findOrFail($incomeId);

        if ($income->is_billed) {
            return redirect()->back()->withErrors(['error' => 'El ingreso ya ha sido facturado.']);
        }

        $amountToBill = $request->amountToBill ?? $income->income_amount;

        if ($amountToBill > $income->income_amount) {
            throw new CFEException('El monto a facturar no puede ser mayor que el total de la orden.');
        }

        $payType = $request->payType ?? 1;

        $this->accountingRepository->emitCFEFree($income, $amountToBill, $payType);
        $income->update(['is_billed' => true]);
        return redirect()->back()->with('success', 'Factura emitida correctamente.');
    }

    /**
     * Obtiene las ventas libres para exportar.
     *
     * @param string $entityType
     * @param int|null $categoryId
     * @param string|null $startDate
     * @param string|null $endDate
     * @return mixed
     */
    public function getIncomesForExport($entityType, $categoryId, $startDate, $endDate)
    {
        // Verificar cash_register_log abierto
        $openCashRegisterLog = CashRegisterLog::whereHas('cashRegister', function($query) {
                $query->where('user_id', auth()->id());
            })
            ->whereNull('close_time')
            ->first();

        if (!$openCashRegisterLog) {
            throw new \Exception('No hay una caja registradora abierta.');
        }

        $query = Income::with(['client', 'supplier', 'paymentMethod', 'incomeCategory', 'taxRate'])
            ->when($entityType === 'Cliente', function ($q) {
                return $q->whereNotNull('client_id');
            })
            ->when($entityType === 'Proveedor', function ($q) {
                return $q->whereNotNull('supplier_id');
            })
            ->when($entityType === 'Ninguno', function ($q) {
                return $q->whereNull('client_id')->whereNull('supplier_id');
            })
            ->when($categoryId, function ($q) use ($categoryId) {
                return $q->where('income_category_id', $categoryId);
            })
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                return $q->whereBetween('income_date', [$startDate, $endDate]);
            })
            ->leftJoin('cash_register_logs', 'incomes.cash_register_log_id', '=', 'cash_register_logs.id')
            ->leftJoin('cash_registers', 'cash_register_logs.cash_register_id', '=', 'cash_registers.id')
            ->where('cash_registers.store_id', auth()->user()->store_id)
            ->where('incomes.cash_register_log_id', $openCashRegisterLog->id)
            ->select('incomes.*')
            ->get();

        return $query;
    }

    public function exportCfePdf($incomeId)
    {
        $income = Income::findOrFail($incomeId);
        return $this->accountingRepository->getCfePdfFree($income);
    }


    /**
     * Obtiene la última cotización del dolar para realizar ventas.
     * @return float
     */
    public function getDollarRate(): float
    {
        $dollar = CurrencyRate::where('name', 'Dólar')->first();
        
        if (!$dollar) {
            return 0;
        }
        
        $rate = CurrencyRateHistory::where('currency_rate_id', $dollar->id)
            ->orderBy('created_at', 'desc')
            ->first();
            
        return $rate ? $rate->sell : 0;
    }
}