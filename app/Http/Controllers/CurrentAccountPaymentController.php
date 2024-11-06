<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCurrentAccountPaymentRequest;
use App\Http\Requests\UpdateCurrentAccountPaymentRequest;
use App\Repositories\CurrentAccountPaymentRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CurrentAccountPaymentController extends Controller
{
    protected $currentAccountPaymentRepository;

    public function __construct(CurrentAccountPaymentRepository $currentAccountPaymentRepository)
    {
        $this->middleware(['check_permission:access_current-account-payments'])->only(
            [
                'index',
                'create',
                'show',
                'datatable',
                'accountPaymentsDatatable',
            ]
        );

        $this->middleware(['check_permission:access_delete_current-account-payments'])->only(
            [
                'destroy',
                'deleteMultiple',
            ]
        );

        $this->currentAccountPaymentRepository = $currentAccountPaymentRepository;
    }

    /**
     * Muestra una lista de todos los pagos de cuentas corrientes.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('current-accounts.index');
    }

    /**
     * Muestra el formulario para crear un nuevo pago de cuenta corriente.
     *
     * @return View
     */
    public function create(Request $request, $currentAccountId): View
    {
        $currentAccount = $this->currentAccountPaymentRepository->getCurrentAccount($currentAccountId);
        $paymentMethods = $this->currentAccountPaymentRepository->getPaymentMethods();

        // Obtener cliente o proveedor
        $client = $this->currentAccountPaymentRepository->getClientByCurrentAccount($currentAccountId);
        $supplier = $this->currentAccountPaymentRepository->getSupplierByCurrentAccount($currentAccountId);

        return view('current-accounts.current-account-payments.add-current-account-payment', compact('paymentMethods', 'client', 'supplier', 'currentAccount'));
    }

    /**
     * Almacena un nuevo pago de cuenta corriente en la base de datos.
     *
     * @param StoreCurrentAccountPaymentRequest $request
     * @return JsonResponse
     */
    public function store(StoreCurrentAccountPaymentRequest $request): JsonResponse
    {
        try {
            $currentAccountPayment = $this->currentAccountPaymentRepository->storePayment($request->validated());
            return response()->json($currentAccountPayment);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al guardar el pago de la cuenta corriente.'], 400);
        }
    }

    /**
     * Muestra un pago de cuenta corriente específico.
     *
     * @param int $id
     * @return ViewContract|RedirectResponse
     */
    public function show(int $id): View | RedirectResponse
    {
        try {
            $getAllCurrentAccountsPayments = $this->currentAccountPaymentRepository->getAllCurrentAccountPayments($id);
            return view('current-accounts.current-account-payments.index', $getAllCurrentAccountsPayments);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->route('current-accounts.index')->with('error', 'Error al mostrar los pagos de la cuenta corriente.');
        }
    }

    /**
     * Muestra el formulario para editar un pago de cuenta corriente específico.
     *
     * @param int $id
     * @return View
     */
    public function edit(int $id): View
    {
        $payment = $this->currentAccountPaymentRepository->getPaymentById($id);
        $paymentMethods = $this->currentAccountPaymentRepository->getPaymentMethods();
        $currentAccount = $this->currentAccountPaymentRepository->getCurrentAccount($payment->current_account_id);
        $client = $this->currentAccountPaymentRepository->getClientByCurrentAccount($payment->current_account_id);

        return view('current-accounts.current-account-payments.edit-current-account-payment', compact('payment', 'paymentMethods', 'currentAccount', 'client'));
    }

    /**
     * Actualiza un pago de cuenta corriente específico.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateCurrentAccountPaymentRequest $request, int $id): JsonResponse
    {
        try {
            $payment = $this->currentAccountPaymentRepository->updatePayment($id, $request->validated());
            return response()->json($payment);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al actualizar el pago de la cuenta corriente.'], 400);
        }
    }

    /**
     * Elimina un pago de cuenta corriente específico.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->currentAccountPaymentRepository->destroyPayment($id);
            return response()->json(['success' => true, 'message' => 'Pago de cuenta corriente eliminado correctamente.']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar el pago de la cuenta corriente.'], 400);
        }
    }

    /**
     * Elimina múltiples pagos de cuentas corrientes.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteMultiple(Request $request): JsonResponse
    {
        try {
            $this->currentAccountPaymentRepository->deleteMultiple($request->input('ids'));
            return response()->json(['success' => true, 'message' => 'Pagos de cuentas corrientes eliminados correctamente.']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar los pagos de cuentas corrientes.'], 400);
        }
    }

    /**
     * Obtiene los pagos de cuentas corrientes para la DataTable.
     *
     * @return mixed
     */
    public function datatable(Request $request): mixed
    {
        return $this->currentAccountPaymentRepository->getPaymentsForDataTable($request->account_id);
    }
}
