<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseCategoryRequest;
use App\Http\Requests\UpdateExpenseCategoryRequest;
use App\Repositories\ExpenseCategoryRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\ExpenseCategory;

class ExpenseCategoryController extends Controller
{
    /**
     * El repositorio para las operaciones de categorías de gastos.
     *
     * @var ExpenseCategoryRepository
     */
    protected $expenseCategoryRepository;

    /**
     * Inyecta el repositorio en el controlador y los middleware.
     *
     * @param ExpenseCategoryRepository $expenseCategoryRepository
     */
    public function __construct(ExpenseCategoryRepository $expenseCategoryRepository)
    {
        $this->middleware(['check_permission:access_expense-categories'])->only(
            [
                'index',
                'create',
                'show',
                'datatable'
            ]
        );

        $this->middleware(['check_permission:access_delete_expense-categories'])->only(
            [
                'destroy',
                'deleteMultiple'
            ]
        );

        $this->expenseCategoryRepository = $expenseCategoryRepository;
    }

    /**
     * Muestra una lista de todas las categorías de gastos.
     *
     * @return View
     */
    public function index(): View
    {
        $expenseCategories = $this->expenseCategoryRepository->getAllExpenseCategories();
        return view('content.accounting.expenses.expenses-categories.index', $expenseCategories);
    }

    /**
     * Muestra el formulario para crear una nueva categoría de gastos.
     *
     * @return View
     */
    public function create(): View
    {
        return view('content.accounting.expense_categories.add-expense-category');
    }

    /**
     * Almacena una nueva categoría de gastos en la base de datos.
     *
     * @param StoreExpenseCategoryRequest $request
     * @return JsonResponse
     */
    public function store(StoreExpenseCategoryRequest $request): JsonResponse
    {
        try {
            $expenseCategory = $this->expenseCategoryRepository->store($request->validated());
            return response()->json($expenseCategory);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al guardar la categoría de gasto.'], 400);
        }
    }

    /**
     * Devuelve datos para una categoría de gasto específica.
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function edit(int $id): JsonResponse
    {
        try {
            $expenseCategory = $this->expenseCategoryRepository->getCategoryById($id);
            return response()->json($expenseCategory);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al obtener los datos de la categoría de gasto.'], 400);
        }
    }

    /**
     * Actualiza una categoría de gasto específica.
     *
     * @param UpdateExpenseCategoryRequest $request
     * @param ExpenseCategory $expenseCategory
     * @return JsonResponse
     */
    public function update(UpdateExpenseCategoryRequest $request, ExpenseCategory $expenseCategory): JsonResponse
    {
        try {
            $expenseCategory = $this->expenseCategoryRepository->update($expenseCategory, $request->validated());
            return response()->json($expenseCategory);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al actualizar la categoría de gasto.'], 400);
        }
    }

    /**
     * Eliminar una categoría de gasto específica.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->expenseCategoryRepository->destroyCategory($id);
            return response()->json(['success' => true, 'message' => 'Categoría de gasto eliminada correctamente.']);
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar la categoría de gasto.'], 400);
        }
    }

    /**
     * Elimina varias categorías de gastos.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteMultiple(Request $request): JsonResponse
    {
        try {
            $this->expenseCategoryRepository->deleteMultipleCategories($request->input('ids'));
            return response()->json(['success' => true, 'message' => 'Categorías de gastos eliminadas correctamente.']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar las categorías de gastos.'], 400);
        }
    }

    /**
     * Obtiene las categorías de gastos para la DataTable.
     *
     * @return mixed
     */
    public function datatable(Request $request): mixed
    {
        return $this->expenseCategoryRepository->getCategoriesForDataTable();
    }
}
