<?php

namespace App\Repositories;

use App\Helpers\Helpers;
use App\Models\Supplier;
use App\Models\Income;
use App\Models\IncomeCategory;
use App\Models\PaymentMethod;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class IncomeSupplierRepository
{
    /**
     * Obtiene todos los ingresos relacionados a proveedores.
     *
     * @return mixed
     */
    public function getAllIncomes(): mixed
    {
        $incomes = Income::where('supplier_id', '!=', null)->get();
        $totalIncomes = Income::where('supplier_id', '!=', null)->count();
        $totalIncomeAmount = Income::where('supplier_id', '!=', null)->sum('income_amount');
        $paymentMethods = PaymentMethod::all();
        $incomeCategories = IncomeCategory::all();
        $suppliers = Supplier::all();
        return compact('incomes', 'totalIncomes', 'totalIncomeAmount', 'paymentMethods', 'incomeCategories', 'suppliers');
    }

    /**
     * Almacena un nuevo ingreso relacionado a un proveedor.
     *
     * @param  array  $data
     * @return Income
     */
    public function store(array $data): Income
    {
        DB::beginTransaction();

        try {
            // Crear el nuevo ingreso
            $income = Income::create([
                'income_name' => $data['income_name'],
                'income_description' => $data['income_description'] ?? null,
                'income_date' => $data['income_date'],
                'income_amount' => $data['income_amount'],
                'payment_method_id' => $data['payment_method_id'],
                'income_category_id' => $data['income_category_id'],
                'supplier_id' => $data['supplier_id'] ?? null,
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
        return Income::findOrFail($incomeId);
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
            // Buscar y actualizar el ingreso
            $income = Income::findOrFail($incomeId);
            $income->update([
                'income_name' => $data['income_name'],
                'income_description' => $data['income_description'] ?? null,
                'income_date' => $data['income_date'],
                'income_amount' => $data['income_amount'],
                'payment_method_id' => $data['payment_method_id'],
                'income_category_id' => $data['income_category_id'],
                'supplier_id' => $data['supplier_id'] ?? null,
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
     * Elimina varios ingresos relacionados a proveedores.
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
        $query = Income::select([
            'incomes.id',
            'incomes.income_name',
            'incomes.income_description',
            'incomes.income_date',
            'incomes.income_amount',
            'incomes.payment_method_id',
            'incomes.income_category_id',
            'incomes.supplier_id',
            'incomes.created_at',
            'suppliers.name as supplier_name',
            'income_categories.income_name as income_category_name',
            'payment_methods.description as payment_method_name',
        ])
            ->join('suppliers', 'incomes.supplier_id', '=', 'suppliers.id')
            ->join('income_categories', 'incomes.income_category_id', '=', 'income_categories.id')
            ->leftJoin('payment_methods', 'incomes.payment_method_id', '=', 'payment_methods.id')
            ->orderBy('incomes.created_at', 'desc');

        // Verificar permisos del usuario
        if (!Auth::user()->can('view_all_incomes')) {
            $query->where(function ($query) {
                $query->where('incomes.supplier_id', Auth::user()->supplier_id)
                    ->orWhereNull('incomes.supplier_id'); // Incluir registros con supplier_id null
            });
        }

        // Filtrar por rango de fechas
        if (Helpers::validateDate($request->input('start_date')) && Helpers::validateDate($request->input('end_date'))) {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $query->whereBetween('incomes.income_date', [$startDate, $endDate]);
        }

        $dataTable = DataTables::of($query)->make(true);

        return $dataTable;
    }
}
