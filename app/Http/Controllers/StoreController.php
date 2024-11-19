<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStoreRequest;
use App\Http\Requests\UpdateStoreRequest;
use App\Models\Store;
use App\Repositories\AccountingRepository;
use App\Repositories\StoreRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use App\Http\Middleware\EnsureUserCanAccessStore;
class StoreController extends Controller
{
    /**
     * El repositorio de empresa.
     *
     * @var StoreRepository
     */
    protected StoreRepository $storeRepository;

    /**
     * El repositorio de contabilidad.
     *
     * @var AccountingRepository
     */
    protected AccountingRepository $accountingRepository;

    /**
     * Constructor para inyectar el repositorio.
     *
     * @param StoreRepository $storeRepository
     * @param AccountingRepository $accountingRepository
     */
    public function __construct(StoreRepository $storeRepository, AccountingRepository $accountingRepository, EnsureUserCanAccessStore $ensureUserCanAccessStore)
    {
        $this->storeRepository = $storeRepository;
        $this->accountingRepository = $accountingRepository;
        $this->middleware('ensure_user_can_access_store')->only(['edit', 'update', 'destroy']);
      }

    /**
     * Muestra una lista de todas las empresa.
     *
     * @return View
     */
    public function index(): View
    {
        $stores = $this->storeRepository->getAll();
        return view('stores.index', compact('stores'));
    }

    /**
     * Muestra el formulario para crear una nueva empresa.
     *
     * @return View
     */
    public function create(): View
    {
        return view('stores.create', ['googleMapsApiKey' => config('services.google.maps_api_key')]);
    }

    /**
     * Almacena una nueva empresa en la base de datos.
     *
     * @param StoreStoreRequest $request
     * @return RedirectResponse
     */
    public function store(StoreStoreRequest $request): RedirectResponse
    {
        $storeData = $request->validated();

        $store = $this->storeRepository->create($storeData);

        return redirect()->route('stores.index')->with('success', 'Empresa creada con éxito.');
    }

    /**
     * Muestra una empresa específica.
     *
     * @param Store $store
     * @return View
     */
    public function show(Store $store): View
    {
        return view('stores.show', compact('store'));
    }

    /**
     * Muestra el formulario para editar una empresa existente.
     *
     * @param Store $store
     * @return View
     */
    public function edit(Store $store): View
    {
        $googleMapsApiKey = config('services.google.maps_api_key');
        $companyInfo = null;
        $logoUrl = null;
        $branchOffices = [];
    
        // Carga la información de la empresa si la facturación está habilitada
        if ($store->invoices_enabled && $store->pymo_user && $store->pymo_password) {
            $companyInfo = $this->accountingRepository->getCompanyInfo($store);
            $logoUrl = $this->accountingRepository->getCompanyLogo($store);
            $branchOffices = $companyInfo['branchOffices'] ?? [];
        }
    
        // Cargar dispositivos vinculados a Scanntech para esta tienda
        $devices = $store->posDevices()->get();
    
        return view('stores.edit', compact('store', 'googleMapsApiKey', 'companyInfo', 'logoUrl', 'branchOffices', 'devices'));
    }
    
    /**
     * Actualiza una Empresa específica en la base de datos.
     *
     * @param UpdateStoreRequest $request
     * @param Store $store
     * @return RedirectResponse
     */
    public function update(UpdateStoreRequest $request, Store $store): RedirectResponse
    {
        // Validar los datos enviados en la request
        $storeData = $request->validated();

        // Actualización de la tienda excluyendo los datos de integraciones específicas
        $this->storeRepository->update($store, Arr::except($storeData, [
            'mercadoPagoPublicKey',
            'mercadoPagoAccessToken',
            'mercadoPagoSecretKey',
            'accepts_mercadopago',
            'pymo_user',
            'pymo_password',
            'pymo_branch_office',
            'accepts_peya_envios',
            'peya_envios_key',
            'callbackNotificationUrl',
            'scanntechCompany',
            'scanntechBranch',
            'scanntechUser',
            'mail_host',
            'mail_port',
            'mail_username',
            'mail_password',
            'mail_encryption',
            'mail_from_address',
            'mail_from_name',
        ]));

        // Manejo de la integración de MercadoPago
        $this->handleMercadoPagoIntegration($request, $store);

        // Manejo de la integración de Pedidos Ya Envíos
        $this->handlePedidosYaEnviosIntegration($request, $store);

        // Manejo de la integración de Scanntech
        $this->handleScanntechIntegration($request, $store);

        // Manejo de la integración de Pymo (Facturación Electrónica)
        $this->handlePymoIntegration($request, $store);

        // Manejo de la integración de configuración de correo
        $this->handleEmailConfigIntegration($request, $store);

        return redirect()->route('stores.edit', $store->id)->with('success', 'Empresa actualizada con éxito.');
    }

    /**
     * Maneja la lógica de la integración con MercadoPago.
     *
     * @param UpdateStoreRequest $request
     * @param Store $store
     */
    private function handleMercadoPagoIntegration(UpdateStoreRequest $request, Store $store): void
    {
        if ($request->boolean('accepts_mercadopago')) {
            $store->mercadoPagoAccount()->updateOrCreate(
                ['store_id' => $store->id],
                [
                    'public_key' => $request->input('mercadoPagoPublicKey'),
                    'access_token' => $request->input('mercadoPagoAccessToken'),
                    'secret_key' => $request->input('mercadoPagoSecretKey'),
                ]
            );
        } else {
            $store->mercadoPagoAccount()->delete();
        }
    }

    /**
     * Maneja la lógica de la integración con Pedidos Ya Envíos.
     *
     * @param UpdateStoreRequest $request
     * @param Store $store
     */
    private function handlePedidosYaEnviosIntegration(UpdateStoreRequest $request, Store $store): void
    {
        if ($request->boolean('accepts_peya_envios')) {
            $store->update([
                'accepts_peya_envios' => true,
                'peya_envios_key' => $request->input('peya_envios_key'),
            ]);
        } else {
            $store->update([
                'accepts_peya_envios' => false,
                'peya_envios_key' => null,
            ]);
        }
    }

    /**
     * Maneja la lógica de la integración con Pymo (Facturación Electrónica).
     *
     * @param UpdateStoreRequest $request
     * @param Store $store
     */
    private function handlePymoIntegration(UpdateStoreRequest $request, Store $store): void
    {
        if ($request->boolean('invoices_enabled')) {
            $updateData = [
                'invoices_enabled' => true,
                'pymo_user' => $request->input('pymo_user'),
                'pymo_branch_office' => $request->input('pymo_branch_office'),
            ];

            // Solo encriptar la nueva contraseña si es enviada
            if ($request->filled('pymo_password')) {
                $updateData['pymo_password'] = Crypt::encryptString($request->input('pymo_password'));
            }

            if ($request->boolean('automatic_billing')) {
                $updateData['automatic_billing'] = true;
            } else {
                $updateData['automatic_billing'] = false;
            }

            $store->update($updateData);
        } else {
            $store->update([
                'invoices_enabled' => false,
                'pymo_user' => null,
                'pymo_password' => null,
                'pymo_branch_office' => null,
                'automatic_billing' => false,
            ]);
        }
    }

    /**
     * Manejo de la integración de Scanntech
     * 
     * @param UpdateStoreRequest $request
     * @param Store $store
     * @return void
     */
    private function handleScanntechIntegration(UpdateStoreRequest $request, Store $store): void
    {
        if ($request->boolean('scanntech')) {
            $store->posIntegrationInfo()->updateOrCreate(
                ['store_id' => $store->id, 'pos_provider_id' => 1], // Scanntech
                [
                    'company' => $request->input('scanntechCompany'),
                    'branch' => $request->input('scanntechBranch'),
                ]
            );
        } else {
            // Elimina la integración si se desactiva
            $store->posIntegrationInfo()->where('pos_provider_id', 1)->delete();
        }
    }
    
    /**
     * Maneja la lógica de la integración de configuración de correo.
     *
     * @param UpdateStoreRequest $request
     * @param Store $store
     */
    private function handleEmailConfigIntegration(UpdateStoreRequest $request, Store $store): void
    {
        if ($request->boolean('stores_email_config')) {
            $store->emailConfig()->updateOrCreate(
                ['store_id' => $store->id],
                [
                    'mail_host' => $request->input('mail_host'),
                    'mail_port' => $request->input('mail_port'),
                    'mail_username' => $request->input('mail_username'),
                    'mail_password' => $request->input('mail_password'),
                    'mail_encryption' => $request->input('mail_encryption'),
                    'mail_from_address' => $request->input('mail_from_address'),
                    'mail_from_name' => $request->input('mail_from_name'),
                    'mail_reply_to_address' => $request->input('mail_reply_to_address'),
                    'mail_reply_to_name' => $request->input('mail_reply_to_name'),
                ]
            );
        } else {
            $store->emailConfig()->delete();
        }
    }


    /**
     * Elimina la Empresa.
     *
     * @param Store $store
     * @return RedirectResponse
     */
    public function destroy(Store $store): RedirectResponse
    {
        $this->storeRepository->delete($store);
        return redirect()->route('stores.index')->with('success', 'Empresa eliminada con éxito.');
    }

    /**
     * Cambia el estado de la Empresa.
     *
     * @param Store $store
     * @return RedirectResponse
     */
    public function toggleStoreStatus(Store $store): RedirectResponse
    {
        $this->storeRepository->toggleStoreStatus($store);
        return redirect()->route('stores.index')->with('success', 'Estado de la tienda cambiado con éxito.');
    }

    /**
     * Cambia el abierto/cerrado de la tienda.
     *
     * @param $id
     * @return RedirectResponse
     */
    public function toggleStoreStatusClosed($storeId)
    {
        $success = $this->storeRepository->toggleStoreStatusClosed($storeId);

        if ($success) {
            $store = Store::findOrFail($storeId);
            return response()->json(['status' => 'success', 'closed' => $store->closed]);
        } else {
            return response()->json(['status' => 'error', 'message' => 'No se pudo cambiar el estado de la tienda.'], 500);
        }
    }

    /**
     * Muestra la página para administrar usuarios asociados a una tienda.
     *
     * @param Store $store
     * @return View
     */
    public function manageUsers(Store $store): View
    {
        $unassociatedUsers = $this->storeRepository->getUnassociatedUsers();
        $associatedUsers = $store->users;
        return view('stores.manage-users', compact('store', 'unassociatedUsers', 'associatedUsers'));
    }

    /**
     * Asocia un usuario a una tienda.
     *
     * @param Request $request
     * @param Store $store
     * @return RedirectResponse
     */
    public function associateUser(Request $request, Store $store): RedirectResponse
    {
        $this->storeRepository->associateUser($store, $request->get('user_id'));
        return redirect()->back()->with('success', 'Usuario asociado con éxito.');
    }

    /**
     * Desasocia un usuario de una tienda.
     *
     * @param Request $request
     * @param Store $store
     * @return RedirectResponse
     */
    public function disassociateUser(Request $request, Store $store): RedirectResponse
    {
        $this->storeRepository->disassociateUser($store, $request->get('user_id'));
        return redirect()->back()->with('success', 'Usuario desasociado con éxito.');
    }

    /**
     * Muestra la página para administrar los horarios de una tienda.
     *
     * @param Store $store
     * @return View
     */
    public function manageHours(Store $store): View
    {
        $storeHours = $store->storeHours->keyBy('day');
        return view('stores.manage-hours', compact('store', 'storeHours'));
    }

    /**
     * Guarda los horarios de una tienda.
     *
     * @param Store $store
     * @param Request $request
     * @return RedirectResponse
     */
    public function saveHours(Store $store, Request $request): RedirectResponse
    {
        $this->storeRepository->saveStoreHours($store, $request->get('hours', []));
        return redirect()->route('stores.index', ['store' => $store->id])->with('success', 'Horarios actualizados con éxito.');
    }

    /**
     * Cambia el estado de cierre de una tienda.
     *
     * @param Request $request
     * @param int $storeId
     * @return JsonResponse
     */
    public function closeStoreStatus(Request $request, int $storeId)
    {
        $store = Store::findOrFail($storeId);
        $store->closed = $request->input('closed');
        $store->save();

        return response()->json(['message' => 'Estado actualizado correctamente', 'newState' => $store->closed]);
    }

    /**
     * Obtiene el estado de todas las tiendas.
     *
     * @return JsonResponse
     */
    public function getAllStoreStatuses()
    {
        $storeStatuses = $this->storeRepository->getStoresWithStatus()->map(function ($store) {
            return [
                'id' => $store->id,
                'status' => $store->closed ? 'closed' : 'open',
            ];
        });

        return response()->json($storeStatuses);
    }

    /**
     * Cambia el estado de la facturación automática de la tienda.
     *
     * @param Store $store
     * @return RedirectResponse
     */
    public function toggleAutomaticBilling(Store $store): RedirectResponse
    {
        $this->storeRepository->toggleAutomaticBilling($store);
        return redirect()->route('stores.index')->with('success', 'Estado de facturación automática cambiado con éxito.');
    }
}
