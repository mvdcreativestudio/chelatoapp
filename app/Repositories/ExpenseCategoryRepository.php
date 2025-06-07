<?php

namespace App\Repositories;

use App\Models\ExpenseCategory;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class ExpenseCategoryRepository
{
    /**
     * Obtiene todas las categorías de gastos.
     *
     * @return mixed
     */
    public function getAllExpenseCategories(): mixed
    {

        $expensesCategories = ExpenseCategory::all();
        $totalExpensesCategories = $expensesCategories->count();
        return compact('expensesCategories', 'totalExpensesCategories');
    }

    /**
     * Almacena una nueva categoría de gastos en la base de datos.
     *
     * @param  array  $data
     * @return ExpenseCategory
     */
    public function store(array $data): ExpenseCategory
    {
        DB::beginTransaction();

        try {
            $expenseCategory = ExpenseCategory::create($data);
            DB::commit();
            return $expenseCategory;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtiene una categoría de gastos específica por su ID.
     *
     * @param int $categoryId
     * @return ExpenseCategory
     */
    public function getCategoryById(int $categoryId): ExpenseCategory
    {
        return ExpenseCategory::findOrFail($categoryId);
    }

    /**
     * Actualiza una categoría de gastos específica en la base de datos.
     *
     * @param ExpenseCategory $expenseCategory
     * @param array $data
     * @return ExpenseCategory
     */
    public function update(ExpenseCategory $expenseCategory, array $data): ExpenseCategory
    {
        DB::beginTransaction();
        try {
            $expenseCategory->update($data);
            DB::commit();
            return $expenseCategory;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Elimina una categoría de gastos específica.
     *
     * @param int $categoryId
     * @return void
     */
    public function destroyCategory(int $categoryId): void
    {
        $expenseCategory = ExpenseCategory::findOrFail($categoryId);
        $expenseCategory->delete();
    }

    /**
     * Elimina múltiples categorías de gastos.
     *
     * @param array $categoryIds
     * @return void
     */
    public function deleteMultipleCategories(array $categoryIds): void
    {
        ExpenseCategory::whereIn('id', $categoryIds)->delete();
    }

    /**
     * Obtiene las categorías de gastos para la DataTable.
     *
     * @return mixed
     */
    public function getCategoriesForDataTable(): mixed
    {
        $query = ExpenseCategory::select([
            'id',
            'name',
            'description',
            'created_at',
            'updated_at'
        ])->orderBy('created_at', 'desc');

        return DataTables::of($query)->make(true);
    }
}
