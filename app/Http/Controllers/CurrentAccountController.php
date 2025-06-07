<?php

namespace App\Http\Controllers;

use App\Exports\CurrentAccountExport;
use App\Http\Requests\StoreCurrentAccountRequest;
use App\Http\Requests\UpdateCurrentAccountRequest;
use App\Repositories\CurrentAccountRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class CurrentAccountController extends Controller
{
    /**
     * El repositorio para las operaciones de cuentas corrientes.
     *
     * @var CurrentAccountRepository
     */
    protected $currentAccountRepository;

    /**
     * Inyecta los repositorios en el controlador y los middleware.
     *
     * @param CurrentAccountRepository $currentAccountRepository
     */
    public function __construct(CurrentAccountRepository $currentAccountRepository)
    {
        $this->middleware(['check_permission:access_current-accounts'])->only(
            [
                'index',
                'create',
                'show',
                'datatable',
                'accountPaymentsDatatable',
            ]
        );

        $this->middleware(['check_permission:access_delete_current-accounts'])->only(
            [
                'destroy',
                'deleteMultiple',
            ]
        );

        $this->currentAccountRepository = $currentAccountRepository;
    }

    /**
     * Muestra una lista de todas las cuentas corrientes filtradas por tipo de transacción.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {

        $currentAccounts = $this->currentAccountRepository->getAllCurrentAccounts();

        return view('current-accounts.index', $currentAccounts);
    }

    /**
     * Muestra el formulario para crear una nueva cuenta corriente.
     *
     * @return View
     */
    public function create(): View
    {
        $paymentMethods = $this->currentAccountRepository->getPaymentMethods();
        $clients = $this->currentAccountRepository->getClients();
        $suppliers = $this->currentAccountRepository->getSuppliers();
        $currentAccountSettings = $this->currentAccountRepository->getCurrentAccountSettings();
        $currencies = $this->currentAccountRepository->getCurrency();
        return view('current-accounts.add-current-account', compact('paymentMethods', 'clients', 'suppliers', 'currentAccountSettings', 'currencies'));
    }

    /**
     * Almacena una nueva cuenta corriente en la base de datos.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(StoreCurrentAccountRequest $request): JsonResponse
    {
        try {
            $currentAccount = $this->currentAccountRepository->store($request->validated());
            return response()->json($currentAccount);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al guardar la cuenta corriente.'], 400);
        }
    }

    /**
     * Devuelve datos para una cuenta corriente específica.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function edit(int $id): View
    {
        try {
            $currentAccount = $this->currentAccountRepository->getCurrentAccountById($id);
            $currencies = $this->currentAccountRepository->getCurrency();
            $clients = $this->currentAccountRepository->getClients();
            $suppliers = $this->currentAccountRepository->getSuppliers();
            $currentAccountSettings = $this->currentAccountRepository->getCurrentAccountSettings();
            $paymentMethods = $this->currentAccountRepository->getPaymentMethods();
            $currentAccountStatus = $this->currentAccountRepository->getCurrentAccountStatus();

            // filter for initial credit
            $initialCredit = $currentAccount->initialCredits->filter(function ($initialCredit) {
                return $initialCredit->description === 'Crédito Inicial';
            })->first();
            // filter current settings
            $currentAccountSettings = $currentAccountSettings->filter(function ($setting) use ($currentAccount) {
                return $setting->transaction_type === $currentAccount->transaction_type;
            });
            return view('current-accounts.edit-current-account', compact('currentAccount', 'currencies', 'clients', 'suppliers', 'currentAccountSettings', 'paymentMethods', 'currentAccountStatus', 'initialCredit'));
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al obtener los datos de la cuenta corriente.'], 400);
        }
    }

    /**
     * Actualiza una cuenta corriente específica.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateCurrentAccountRequest $request, int $id): JsonResponse
    {
        try {
            $currentAccount = $this->currentAccountRepository->update($id, $request->validated());
            return response()->json($currentAccount);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al actualizar la cuenta corriente.'], 400);
        }
    }

    /**
     * Eliminar una cuenta corriente específica.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->currentAccountRepository->destroy($id);
            return response()->json(['success' => true, 'message' => 'Cuenta corriente eliminada correctamente.']);
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar la cuenta corriente.'], 400);
        }
    }

    /**
     * Elimina varias cuentas corrientes.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteMultiple(Request $request): JsonResponse
    {
        try {
            $this->currentAccountRepository->deleteMultiple($request->input('ids'));
            return response()->json(['success' => true, 'message' => 'Cuentas corrientes eliminadas correctamente.']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar las cuentas corrientes.'], 400);
        }
    }

    /**
     * Obtiene las cuentas corrientes para la DataTable.
     *
     * @return mixed
     */
    public function datatable(Request $request): mixed
    {
        return $this->currentAccountRepository->getCurrentAccountsForDataTable($request);
    }

    public function exportExcel(Request $request)
    {
        try {
            // Obtener los filtros de la solicitud
            $entityType = $request->input('entity_type'); // Tipo de entidad (cliente o proveedor)
            $clientId = $request->input('client_id'); // ID del cliente seleccionado
            $supplierId = $request->input('supplier_id'); // ID del proveedor seleccionado
            $status = $request->input('status'); // Estado del pago
            $startDate = $request->input('start_date'); // Fecha de inicio
            $endDate = $request->input('end_date'); // Fecha de fin

            // Llamar al método del repositorio que obtiene los datos filtrados
            $currentAccounts = $this->currentAccountRepository->getCurrentAccountsForExport($entityType, $clientId, $supplierId, $status, $startDate, $endDate);

            // Exportar a Excel utilizando Maatwebsite\Excel
            return Excel::download(new CurrentAccountExport($currentAccounts), 'cuentas-corrientes-' . date('Y-m-d_H-i-s') . '.xlsx');
        } catch (\Exception $e) {
            dd($e->getMessage());
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Error al exportar las cuentas corrientes. Por favor, intente nuevamente.');
        }
    }

    public function exportPdf(Request $request)
    {
        try {
            // Obtener los filtros de la solicitud
            $entityType = $request->input('entity_type'); // Tipo de entidad (cliente o proveedor)
            $clientId = $request->input('client_id'); // ID del cliente seleccionado
            $supplierId = $request->input('supplier_id'); // ID del proveedor seleccionado
            $status = $request->input('status'); // Estado del pago
            $startDate = $request->input('start_date'); // Fecha de inicio
            $endDate = $request->input('end_date'); // Fecha de fin

            // Llamar al método del repositorio que obtiene los datos filtrados
            $currentAccounts = $this->currentAccountRepository->getCurrentAccountsForExport($entityType, $clientId, $supplierId, $status, $startDate, $endDate);

            // Exportar a PDF utilizando Dompdf
            $pdf = \PDF::loadView('current-accounts.export-pdf', compact('currentAccounts'));
            return $pdf->download('cuentas-corrientes-' . date('Y-m-d_H-i-s') . '.pdf');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Error al exportar las cuentas corrientes. Por favor, intente nuevamente.');
        }
    }
}
