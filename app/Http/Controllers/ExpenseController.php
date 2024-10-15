<?php

namespace App\Http\Controllers;

use App\Enums\Expense\ExpenseStatusEnum;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Repositories\ExpenseRepository;
use App\Models\Expense;
use App\Repositories\ExpensePaymentMethodRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    /**
     * El repositorio para las operaciones de gastos.
     *
     * @var ExpenseRepository
     */
    protected $expenseRepository;
    protected $expensePaymentMethodRepository;

    /**
     * Inyecta el repositorio en el controlador y los middleware.
     *
     * @param ExpenseRepository $expenseRepository
     */
    public function __construct(ExpenseRepository $expenseRepository, ExpensePaymentMethodRepository $expensePaymentMethodRepository)
    {
        $this->middleware(['check_permission:access_expenses', 'user_has_store'])->only(
            [
                'index',
                'create',
                'show',
                // 'destroy',
                // 'deleteMultiple',
                'datatable',
                'expensePaymentsDatatable'
            ]
        );

        $this->middleware(['check_permission:access_delete_expenses'])->only(
            [
                'destroy',
                'deleteMultiple'
            ]
        );

        $this->expenseRepository = $expenseRepository;
        $this->expensePaymentMethodRepository = $expensePaymentMethodRepository;
    }

    /**
     * Muestra una lista de todos los gastos.
     *
     * @return View
     */
    public function index(): View
    {
        $expenses = $this->expenseRepository->getAllExpenses();
        $suppliers = $this->expenseRepository->getAllSuppliers();
        $stores = $this->expenseRepository->getAllStores();
        $expenseCategories = $this->expenseRepository->getAllExpenseCategories();
        $paymentMethods = $this->expensePaymentMethodRepository->getPaymentsMethods();
        $currencies = $this->expenseRepository->getAllCurrencies();
         // Combinar todos los datos en un solo array
        $mergeData = array_merge($expenses, compact('suppliers', 'stores', 'expenseCategories','paymentMethods', 'currencies'));
        return view('content.accounting.expenses.index', $mergeData);
    }

    /**
     * Muestra el formulario para crear un nuevo gasto.
     *
     * @return View
     */
    public function create(): View
    {
        return view('content.accounting.expenses.add-expense');
    }

    /**
     * Almacena un nuevo gasto en la base de datos.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(StoreExpenseRequest $request): JsonResponse
    {
        try {
            $expense = $this->expenseRepository->store($request->validated());
            return response()->json($expense);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al guardar el gasto.'], 400);
        }
    }

    /**
     * Muestra un gasto específico.
     *
     * @param Expense $expense
     * @return View
     */
    public function show(Expense $expense): View
    {
        $expense = $this->expenseRepository->loadExpenseRelations($expense);
        $payments = $expense->payments;
        $supplierExpensesCount = $this->expenseRepository->getSupplierExpensesCount($expense->supplier_id);

        return view('content.accounting.expenses.details-expense', compact('expense', 'payments', 'supplierExpensesCount'));
    }

    /**
     * Devuelve datos para un gasto específico.
     * 
     * @param int $id
     * @return JsonResponse
     */

     public function edit(int $id): JsonResponse
     {
         try {
             $expense = $this->expenseRepository->getExpenseById($id);
             return response()->json($expense);
         } catch (\Exception $e) {
             Log::error($e->getMessage());
             return response()->json(['error' => 'Error al obtener los datos del gasto.'], 400);
         }
     }

    /**
     * Actualiza un gasto específico.
     *
     * @param Request $request
     * @param Expense $expense
     */

    public function update(UpdateExpenseRequest $request, Expense $expense): JsonResponse
    {
        try {
            $expense = $this->expenseRepository->update($expense, $request->validated());
            return response()->json($expense);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al actualizar el gasto.'], 400);
        }
    }

    /**
     * Eliminar un gasto específico.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->expenseRepository->destroyExpense($id);
            return response()->json(['success' => true, 'message' => 'Gasto eliminado correctamente.']);
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar el gasto.'], 400);
        }
    }

    /**
     * Elimina varios gastos.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteMultiple(Request $request): JsonResponse
    {
        try {
            $this->expenseRepository->deleteMultipleExpenses($request->input('ids'));
            return response()->json(['success' => true, 'message' => 'Gastos eliminados correctamente.']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar los gastos.'], 400);
        }
    }

    /**
     * Obtiene los gastos para la DataTable.
     *
     * @return mixed
     */
    public function datatable(Request $request): mixed
    {
        return $this->expenseRepository->getExpensesForDataTable($request);
    }

    /**
     * Obtiene los pagos de un gasto para la DataTable.
     *
     * @param Expense $expense
     * @return mixed
     */
    public function expensePaymentsDatatable(Expense $expense)
    {
        return $this->expenseRepository->getExpensePaymentsForDataTable($expense);
    }

    /**
     * Actualiza el estado del pago de un gasto.
     *
     * @param Request $request
     * @param int $expenseId
     * @return RedirectResponse
     */
    public function updateStatus(Request $request, int $expenseId): RedirectResponse
    {
        $request->validate([
            'payment_status' => 'required|string',
        ]);

        try {
            $this->expenseRepository->updatePaymentStatus($expenseId, $request->input('payment_status'));
            return redirect()->back()->with('success', 'Estado del gasto actualizado correctamente.');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'No se pudo actualizar. Por favor, intente nuevamente');
        }
    }
}