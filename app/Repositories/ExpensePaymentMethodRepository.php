<?php

namespace App\Repositories;

use App\Enums\Expense\ExpenseStatusEnum;
use App\Helpers\Helpers;
use App\Http\Controllers\front_pages\Payment;
use App\Models\Expense;
use App\Models\ExpensePaymentMethod;
use App\Models\PaymentMethod;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ExpensePaymentMethodRepository
{

    public function getExpensePaymentMethodByExpenseId(int $expenseId): mixed
    {
        return ExpensePaymentMethod::where('expense_id', $expenseId)->get();
    }

    /**
     * Almacena un nuevo método de pago de gasto en la base de datos.
     *
     * @param  array  $data
     * @return ExpensePaymentMethod
     */
    public function store(array $data): ExpensePaymentMethod
    {
        DB::beginTransaction();

        try {
            $expensePaymentMethod = ExpensePaymentMethod::create($data);
            $this->updateStatusExpense($expensePaymentMethod->expense_id);
            DB::commit();
            return $expensePaymentMethod;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtiene un método de pago de gasto específico por su ID.
     *
     * @param int $expensePaymentMethodId
     * @return ExpensePaymentMethod
     */
    public function getExpensePaymentMethodById(int $expensePaymentMethodId): ExpensePaymentMethod
    {
        return ExpensePaymentMethod::findOrFail($expensePaymentMethodId);
    }

    /**
     * Actualiza un método de pago de gasto específico en la base de datos.
     *
     * @param ExpensePaymentMethod $expensePaymentMethod
     * @param array $data
     * @return ExpensePaymentMethod
     */
    public function update(ExpensePaymentMethod $expensePaymentMethod, array $data): ExpensePaymentMethod
    {
        DB::beginTransaction();

        try {
            $expensePaymentMethod->update($data);
            $this->updateStatusExpense($expensePaymentMethod->expense_id);
            DB::commit();
            return $expensePaymentMethod;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Elimina un método de pago de gasto específico.
     *
     * @param int $expensePaymentMethodId
     * @return void
     */
    public function destroyExpensePaymentMethod(int $expensePaymentMethodId): void
    {
        $expensePaymentMethod = ExpensePaymentMethod::findOrFail($expensePaymentMethodId);
        $expensePaymentMethod->delete();
        $this->updateStatusExpense($expensePaymentMethod->expense_id);
    }

    /**
     * Eliminar varios métodos de pago de gastos.
     *
     * @param array $expensePaymentMethodIds
     * @return void
     */
    public function deleteMultipleExpensePaymentMethods(array $expensePaymentMethodIds): void
    {
        $expenses = Expense::whereHas('payments', function ($query) use ($expensePaymentMethodIds) {
            $query->whereIn('id', $expensePaymentMethodIds);
        })->first();
        ExpensePaymentMethod::whereIn('id', $expensePaymentMethodIds)->delete();
        $this->updateStatusExpense($expenses->id);
    }

    /**
     * Obtiene los métodos de pago de gastos para la DataTable.
     *
     * @return mixed
     */
    public function getExpensePaymentMethodsForDataTable(Request $request, int $id): mixed
    {
        $query = ExpensePaymentMethod::select([
            'id',
            'amount_paid',
            'payment_date',
            'expense_id',
            'payment_method_id',
        ])
        ->with('paymentMethod')
        ->where('expense_id', $id)
        ->orderBy('created_at', 'desc');

        // Filtrar por rango de fechas
        if (Helpers::validateDate($request->input('start_date')) && Helpers::validateDate($request->input('end_date'))) {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $query->whereBetween('payment_date', [$startDate, $endDate]);
        }

        $dataTable = DataTables::of($query)->make(true);

        return $dataTable;
    }

    /**
     * Obtiene el total de los pagos realizados en un gasto.
     *
     * @param int $expenseId
     * @return float
     */
    public function getPaymentsMethods(): mixed{
        return PaymentMethod::all();
    }

    /**
     * Actualiza el estado de un gasto.
     *
     * @param int $expenseId
     * @return void
     */

    private function updateStatusExpense(int $expenseId): void
    {
        // $expense = Expense::findOrFail($expenseId)->load('payments');
        $expense = Expense::findOrFail($expenseId);
        $totalPaid = (float) $expense->payments->sum('amount_paid');
        $totalAmount = (float) $expense->amount;
    
        if ($totalPaid == $totalAmount) {
            $expense->status = ExpenseStatusEnum::PAID;
        } else if ($totalPaid == 0) {
            $expense->status = ExpenseStatusEnum::UNPAID;
        }else{
            $expense->status = ExpenseStatusEnum::PARTIAL;
        }
    
        // Guardar el cambio de estado
        $expense->save();
    }
}