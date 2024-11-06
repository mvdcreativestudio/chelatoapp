<?php

namespace App\Repositories;

use App\Models\IncomeCategory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class IncomeCategoryRepository
{
    /**
     * Obtiene todas las categorías de ingresos.
     *
     * @return mixed
     */
    public function getAllIncomeCategories(): mixed
    {
        $incomeCategories = IncomeCategory::all();
        $totalIncomeCategories = $incomeCategories->count();
        return compact('incomeCategories', 'totalIncomeCategories');
    }

    /**
     * Almacena una nueva categoría de ingreso en la base de datos.
     *
     * @param  array  $data
     * @return IncomeCategory
     */
    public function store(array $data): IncomeCategory
    {
        DB::beginTransaction();

        try {
            // Crear la nueva categoría de ingreso
            $incomeCategory = IncomeCategory::create([
                'income_name' => $data['income_name'],
                'income_description' => $data['income_description'] ?? null,
            ]);

            DB::commit();
            return $incomeCategory;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtiene una categoría de ingreso específica por su ID.
     *
     * @param int $categoryId
     * @return IncomeCategory
     */
    public function getIncomeCategoryById(int $categoryId): IncomeCategory
    {
        return IncomeCategory::findOrFail($categoryId);
    }

    /**
     * Actualiza una categoría de ingreso específica en la base de datos.
     *
     * @param int $categoryId
     * @param array $data
     * @return IncomeCategory
     */
    public function update(int $categoryId, array $data): IncomeCategory
    {
        DB::beginTransaction();

        try {
            // Buscar y actualizar la categoría de ingreso
            $incomeCategory = IncomeCategory::findOrFail($categoryId);
            $incomeCategory->update([
                'income_name' => $data['income_name'],
                'income_description' => $data['income_description'] ?? null,
            ]);

            DB::commit();
            return $incomeCategory;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Elimina una categoría de ingreso específica.
     *
     * @param int $categoryId
     * @return void
     */
    public function destroyIncomeCategory(int $categoryId): void
    {
        $incomeCategory = IncomeCategory::findOrFail($categoryId);
        $incomeCategory->delete();
    }

    /**
     * Elimina varias categorías de ingresos.
     *
     * @param array $categoryIds
     * @return void
     */
    public function deleteMultipleIncomeCategories(array $categoryIds): void
    {
        DB::beginTransaction();

        try {
            // Eliminar las categorías de ingresos
            IncomeCategory::whereIn('id', $categoryIds)->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtiene las categorías de ingresos para la DataTable.
     *
     * @param Request $request
     * @return mixed
     */
    public function getIncomeCategoriesForDataTable(Request $request): mixed
    {
        $query = IncomeCategory::select([
            'id',
            'income_name',
            'income_description',
            'created_at',
        ])->orderBy('id', 'desc');

        $dataTable = DataTables::of($query)->make(true);

        return $dataTable;
    }
}
