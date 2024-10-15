<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpensePaymentMethodRequest;
use App\Http\Requests\UpdateExpensePaymentMethodRequest;
use App\Models\Expense;
use App\Models\ExpensePaymentMethod;
use App\Repositories\ExpensePaymentMethodRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpensePaymentMethodController extends Controller
{
    /**
     * El repositorio para las operaciones de métodos de pago de gastos.
     *
     * @var ExpensePaymentMethodRepository
     */
    protected $expensePaymentMethodRepository;

    /**
     * Inyecta el repositorio en el controlador y los middleware.
     *
     * @param ExpensePaymentMethodRepository $expensePaymentMethodRepository
     */
    public function __construct(ExpensePaymentMethodRepository $expensePaymentMethodRepository)
    {
        $this->middleware(['check_permission:access_expenses', 'user_has_store'])->only(
            [
                'index',
                'create',
                'show',
                'destroy',
                'detail',
                'deleteMultiple',
                'datatable'
            ]
        );

        $this->expensePaymentMethodRepository = $expensePaymentMethodRepository;
    }

    /**
     * Almacena un nuevo método de pago de gasto en la base de datos.
     *
     * @param StoreExpensePaymentMethodRequest $request
     * @return JsonResponse
     */
    public function store(StoreExpensePaymentMethodRequest $request): JsonResponse
    {
        try {
            $expensePaymentMethod = $this->expensePaymentMethodRepository->store($request->validated());
            return response()->json($expensePaymentMethod);
        } catch (\Exception $e) {
            dd($e);
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al guardar el método de pago de gasto.'], 400);
        }
    }

    /**
     * Muestra un método de pago de gasto específico.
     *
     * @param ExpensePaymentMethod $expensePaymentMethod
     * @return View
     */
    public function detail(Expense $expense): View
    {
        $expensePaymentMethod = $this->expensePaymentMethodRepository->getExpensePaymentMethodByExpenseId($expense->id);
        $paymentsMethods = $this->expensePaymentMethodRepository->getPaymentsMethods();
        return view('content.accounting.expenses.expenses-payments-methods.index', compact('expense','expensePaymentMethod', 'paymentsMethods'));
    }

    // edit method
    /**
     * Muestra el formulario para editar un método de pago de gasto específico.
     *
     * @param ExpensePaymentMethod $expensePaymentMethod
     * @return View
     */
    public function edit(int $id): JsonResponse
    {
        try {
            $expensePaymentMethod = $this->expensePaymentMethodRepository->getExpensePaymentMethodById($id);
            return response()->json($expensePaymentMethod);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al obtener el método de pago de gasto.'], 400);
        }
    }

    /**
     * Actualiza un método de pago de gasto específico.
     *
     * @param UpdateExpensePaymentMethodRequest $request
     * @param ExpensePaymentMethod $expensePaymentMethod
     * @return JsonResponse
     */
    public function update(UpdateExpensePaymentMethodRequest $request, ExpensePaymentMethod $expensePaymentMethod): JsonResponse
    {
        try {
            $expensePaymentMethod = $this->expensePaymentMethodRepository->update($expensePaymentMethod, $request->validated());
            return response()->json($expensePaymentMethod);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al actualizar el método de pago de gasto.'], 400);
        }
    }

    /**
     * Eliminar un método de pago de gasto específico.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->expensePaymentMethodRepository->destroyExpensePaymentMethod($id);
            return response()->json(['success' => true, 'message' => 'Método de pago de gasto eliminado correctamente.']);
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar el método de pago de gasto.'], 400);
        }
    }

    /**
     * Elimina varios métodos de pago de gastos.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteMultiple(Request $request): JsonResponse
    {
        try {
            $this->expensePaymentMethodRepository->deleteMultipleExpensePaymentMethods($request->input('ids'));
            return response()->json(['success' => true, 'message' => 'Métodos de pago de gastos eliminados correctamente.']);
        } catch (\Exception $e) {
            dd($e);
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar los métodos de pago de gastos.'], 400);
        }
    }

    /**
     * Obtiene los métodos de pago de gastos para la DataTable.
     *
     * @return mixed
     */
    public function datatable(Request $request, $id): mixed
    {
        return $this->expensePaymentMethodRepository->getExpensePaymentMethodsForDataTable($request, $id);
    }
}