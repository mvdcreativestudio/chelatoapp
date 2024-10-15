<?php

namespace App\Repositories;

use App\Enums\Expense\ExpenseStatusEnum;
use App\Enums\Expense\ExpenseTemporalStatusEnum;
use App\Helpers\Helpers;
use App\Models\Currency;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpensePaymentMethod;
use App\Models\Store;
use App\Models\Supplier;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ExpenseRepository
{
    /**
     * Obtiene todos los gastos y las estadísticas necesarias para las cards.
     *
     * @return array
     */
    public function getAllExpenses(): array
    {
        // Verificar si el usuario tiene permiso para ver todos los gastos de la tienda
        if (Auth::user()->can('view_all_expenses')) {
            // Si tiene el permiso, obtenemos todos los gastos
            $expenses = Expense::all();
        } else {
            // Si no tiene el permiso, solo obtenemos los gastos de su store_id
            $expenses = Expense::where('store_id', Auth::user()->store_id)->get();
        }

        // Calcular las estadísticas basadas en los gastos filtrados
        $totalExpenses = $expenses->count();
        $totalAmount = $expenses->sum('amount');
        $paidExpenses = $expenses->where('status', ExpenseStatusEnum::PAID)->count();
        $partialExpenses = $expenses->where('status', ExpenseStatusEnum::PARTIAL)->count();
        $unpaidExpenses = $expenses->where('status', ExpenseStatusEnum::UNPAID)->count();

        return compact('expenses', 'totalExpenses', 'totalAmount', 'partialExpenses', 'paidExpenses', 'unpaidExpenses');
    }

    /**
     * Almacena un nuevo gasto en la base de datos.
     *
     * @param  array  $data
     * @return Expense
     */
    public function store(array $data): Expense
    {
        DB::beginTransaction();

        try {
            $expense = Expense::create($data);
            $expense->status = ExpenseStatusEnum::UNPAID;
            $expense->temporal_status = $expense->calculateTemporalStatus();
            $expense->save();

            // verificar si existe un monto pagado
            if ($data['is_paid']) {
                $paymentData = [
                    'expense_id' => $expense->id,
                    'amount_paid' => $data['amount_paid'],
                    'payment_date' => $data['due_date'],
                    'payment_method_id' => $data['payment_method_id'],
                ];

                $expensePaymentMethod = ExpensePaymentMethod::create($paymentData);
                $expense->status = $data['amount_paid'] == $data['amount'] ? ExpenseStatusEnum::PAID : ExpenseStatusEnum::PARTIAL;
                $expense->temporal_status = $expense->calculateTemporalStatus();
                $expense->save();
            }
            DB::commit();
            return $expense;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    /**
     * Obtiene un gasto específico por su ID.
     *
     * @param int $expenseId
     * @return Expense
     */
    public function getExpenseById(int $expenseId): Expense
    {
        return Expense::findOrFail($expenseId);
    }


    /**
     * Actualiza un gasto específico en la base de datos.
     *
     * @param Expense $expense
     * @param array $data
     * @return Expense
     */
    public function update(Expense $expense, array $data): Expense
    {
        DB::beginTransaction();

        try {
            $expense->update($data);
            DB::commit();
            return $expense;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Carga las relaciones de un gasto.
     *
     * @param Expense $expense
     * @return Expense
     */
    public function loadExpenseRelations(Expense $expense): Expense
    {
        return $expense->load(['supplier', 'expenseCategory', 'store', 'payments']);
    }

    /**
     * Elimina un gasto específico.
     *
     * @param int $expenseId
     * @return void
     */
    public function destroyExpense(int $expenseId): void
    {
        $expense = Expense::findOrFail($expenseId);
        $expense->delete();
    }

    /**
     * Eliminar varios gastos.
     *
     * @param array $expenseIds
     * @return void
     */
    public function deleteMultipleExpenses(array $expenseIds): void
    {
        Expense::whereIn('id', $expenseIds)->delete();
    }

    /**
     * Obtiene los gastos para la DataTable.
     *
     * @return mixed
     */
    public function getExpensesForDataTable(Request $request): mixed
    {
        $query = Expense::select([
            'expenses.id',
            'expenses.amount',
            'expenses.status',
            'expenses.due_date',
            'expenses.temporal_status',
            'expenses.supplier_id',
            'expenses.expense_category_id',
            'expenses.store_id',
            'expenses.created_at',
            'suppliers.name as supplier_name',
            'expense_categories.name as category_name',
            'currencies.name as currency_name',
            'currencies.symbol as currency_symbol',
            'stores.name as store_name',
        ])
            ->join('suppliers', 'expenses.supplier_id', '=', 'suppliers.id')
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->join('currencies', 'expenses.currency_id', '=', 'currencies.id')
            ->leftJoin('stores', 'expenses.store_id', '=', 'stores.id') // Cambiar a leftJoin para incluir registros con store_id null
            ->orderBy('expenses.created_at', 'desc');

        // Verificar permisos del usuario
        if (!Auth::user()->can('view_all_expenses')) {
            $query->where(function ($query) {
                $query->where('expenses.store_id', Auth::user()->store_id)
                      ->orWhereNull('expenses.store_id'); // Incluir registros con store_id null
            });
        }

        // Filtrar por rango de fechas
        if (Helpers::validateDate($request->input('start_date')) && Helpers::validateDate($request->input('end_date'))) {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $query->whereBetween('expenses.due_date', [$startDate, $endDate]);
        }

        $dataTable = DataTables::of($query)->make(true);

        return $dataTable;
    }

    /**
     * Obtiene los pagos de un gasto para la DataTable.
     *
     * @param Expense $expense
     * @return mixed
     */
    public function getExpensePaymentsForDataTable(Expense $expense)
    {
        $query = ExpensePaymentMethod::where('expense_id', $expense->id)
            ->select(['id', 'amount', 'payment_date', 'payment_method']);

        return DataTables::of($query)
            ->addColumn('total_payment', function ($payment) {
                return number_format($payment->amount, 2);
            })
            ->make(true);
    }

    /**
     * Obtiene el conteo de gastos del proveedor.
     *
     * @param int $supplierId
     * @return int
     */
    public function getSupplierExpensesCount(int $supplierId): int
    {
        return Expense::where('supplier_id', $supplierId)->count();
    }

    /**
     * Actualiza el estado del pago de un gasto.
     *
     * @param int $expenseId
     * @param string $paymentStatus
     * @return Expense
     */
    public function updatePaymentStatus(int $expenseId, string $paymentStatus): Expense
    {
        $expense = Expense::findOrFail($expenseId);
        $oldStatus = $expense->status;

        // Verificar si hay un cambio en el estado de pago
        if ($oldStatus !== $paymentStatus) {
            $expense->status = $paymentStatus;
            $expense->save();
        }

        return $expense;
    }

    // function getAllSuppliers
    /**
     * Obtiene todos los proveedores.
     *
     * @return mixed
     */

    public function getAllSuppliers(): mixed
    {
        return Supplier::all();
    }

    // function getAllStores
    /**
     * Obtiene todas las tiendas.
     *
     * @return mixed
     */
    public function getAllStores(): mixed
    {
        return Store::all();
    }

    // function getAllExpenseCategories
    /**
     * Obtiene todas las categorías de gastos.
     *
     * @return mixed
     */
    public function getAllExpenseCategories(): mixed
    {
        return ExpenseCategory::all();
    }

    // function get enum values from EnumExpense
    /**
     * Obtiene los valores de enumeración de la clase EnumExpense.
     *
     * @return array
     */
    public function getExpenseStatus(): array
    {
        return ExpenseStatusEnum::getTranslateds();
    }

    public function getAllCurrencies(): mixed
    {
        return Currency::all();
    }
}
