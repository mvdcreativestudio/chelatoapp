<?php

namespace App\Http\Controllers;

use App\Enums\MercadoPago\MercadoPagoApplicationTypeEnum;
use App\Exceptions\MercadoPagoException;
use App\Helpers\Helpers;
use App\Http\Middleware\EnsureUserCanAccessStore;
use App\Http\Requests\StoreStoreRequest;
use App\Http\Requests\UpdateStoreRequest;
use App\Models\Store;
use App\Repositories\AccountingRepository;
use App\Repositories\MercadoPagoAccountStoreRepository;
use App\Repositories\StoreRepository;
use App\Services\MercadoPagoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

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
     * El servicio de MercadoPago.
     *
     * @var MercadoPagoService
     */
    protected $mpService;

    /**
     * El repositorio de Mercado Pago Account Store.
     *
     * @var MercadoPagoAccountStoreRepository
     */
    protected $mercadoPagoAccountStoreRepository;

    /**
     * Constructor para inyectar el repositorio.
     *
     * @param StoreRepository $storeRepository
     * @param AccountingRepository $accountingRepository
     */
    public function __construct(StoreRepository $storeRepository, AccountingRepository $accountingRepository, EnsureUserCanAccessStore $ensureUserCanAccessStore, MercadoPagoService $mpService, MercadoPagoAccountStoreRepository $mercadoPagoAccountStoreRepository)
    {
        $this->storeRepository = $storeRepository;
        $this->accountingRepository = $accountingRepository;
        $this->mpService = $mpService;
        $this->mercadoPagoAccountStoreRepository = $mercadoPagoAccountStoreRepository;
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
        $mercadoPagoStores = [];
        // Cargar la tienda con las relaciones necesarias
        $store->load('mercadoPagoAccount');

        // Carga la información de la empresa si la facturación está habilitada
        if ($store->invoices_enabled && $store->pymo_user && $store->pymo_password) {
            $companyInfo = $this->accountingRepository->getCompanyInfo($store);
            $logoUrl = $this->accountingRepository->getCompanyLogo($store);
            $branchOffices = $companyInfo['branchOffices'] ?? [];
        }

        // Cargar dispositivos vinculados a Scanntech para esta tienda
        $devices = $store->posDevices()->get();

        // Dividir cuentas de MercadoPago por tipo
        $mercadoPagoOnline = $store->mercadoPagoAccount->firstWhere('type', MercadoPagoApplicationTypeEnum::PAID_ONLINE);
        $mercadoPagoPresencial = $store->mercadoPagoAccount->firstWhere('type', MercadoPagoApplicationTypeEnum::PAID_PRESENCIAL);
        $mercadoPagoAccountStore = $this->mercadoPagoAccountStoreRepository->getStoreByExternalId($store->id);
        return view('stores.edit', compact('store', 'googleMapsApiKey', 'companyInfo', 'logoUrl', 'branchOffices', 'devices', 'mercadoPagoOnline', 'mercadoPagoPresencial', 'mercadoPagoAccountStore'));
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
        try {
            // Actualización de la tienda excluyendo los datos de integraciones específicas
            $this->storeRepository->update($store, Arr::except($storeData, [
                'mercadoPagoPublicKey',
                'mercadoPagoAccessToken',
                'mercadoPagoSecretKey',
                'accepts_mercadopago_online',
                'accepts_mercadopago_presencial',
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

            // Manejo de la integración de MercadoPago Online
            $this->handleMercadoPagoIntegrationOnline($request, $store);

            // Manejo de la integración de MercadoPago Presencial
            $this->handleMercadoPagoIntegrationPresencial($request, $store);

            // Manejo de la integración de Pedidos Ya Envíos
            $this->handlePedidosYaEnviosIntegration($request, $store);

            // Manejo de la integración de Scanntech
            $this->handleScanntechIntegration($request, $store);

            // Manejo de la integración de Pymo (Facturación Electrónica)
            $this->handlePymoIntegration($request, $store);

            // Manejo de la integración de configuración de correo
            $this->handleEmailConfigIntegration($request, $store);

            return redirect()->route('stores.edit', $store->id)->with('success', 'Empresa actualizada con éxito.');
        }catch (MercadoPagoException $e) {
            Log::error('Error al actualizar la empresa: ' . $e->getMessage());
            $errorMessage = Helpers::formatMercadoPagoErrors($e->getDetails());
            return redirect()
                ->route('stores.edit', $store->id)
                ->with('mercado_pago_errors', 'Error durante la actualización: ' . $errorMessage);
        }catch (\Exception $e) {
            Log::error('Error al actualizar la empresa: ' . $e->getMessage());
            return redirect()
                ->route('stores.edit', $store->id)
                ->with('error', 'Ocurrió un error durante la actualización: ' . $e->getMessage());
        }
    }

    /**
     * Maneja la lógica de la integración con MercadoPago.
     *
     * @param UpdateStoreRequest $request
     * @param Store $store
     */
    private function handleMercadoPagoIntegrationOnline(UpdateStoreRequest $request, Store $store): void
    {
        DB::beginTransaction();
        try {
            if ($request->boolean('accepts_mercadopago_online')) {
                $store->mercadoPagoAccount()->updateOrCreate(
                    ['store_id' => $store->id, 'type' => MercadoPagoApplicationTypeEnum::PAID_ONLINE],
                    [
                        'public_key' => $request->input('mercadoPagoPublicKeyOnline'),
                        'access_token' => $request->input('mercadoPagoAccessTokenOnline'),
                        'secret_key' => $request->input('mercadoPagoSecretKeyOnline'),
                    ]
                );
            } else {
                $store->mercadoPagoAccount()->where('type', MercadoPagoApplicationTypeEnum::PAID_ONLINE)->delete();
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en la integración con MercadoPago.', [
                'store_id' => $store->id,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Error en la integración con MercadoPago: ' . $e->getMessage());
        }
    }

    private function handleMercadoPagoIntegrationPresencial(UpdateStoreRequest $request, Store $store): bool
    {
        DB::beginTransaction();
        try {

            if (!$request->boolean('accepts_mercadopago_presencial')) {
                $this->mpService->setCredentials($store->id, MercadoPagoApplicationTypeEnum::PAID_PRESENCIAL->value);
                // Eliminar la sucursal de MercadoPago si existe
                $mercadoPagoAccountStore = $this->mercadoPagoAccountStoreRepository->getStoreByExternalId($store->id);
                $mercadoPagoAccountStore->load('mercadopagoAccountPOS');
                // Eliminar POS de MercadoPago si existe
                foreach ($mercadoPagoAccountStore->mercadopagoAccountPOS as $pos) {
                    $this->mpService->deletePOS($pos->id_pos);
                    $pos->delete();
                }
                if ($mercadoPagoAccountStore) {
                    $this->mpService->deleteStore($mercadoPagoAccountStore->store_id);
                    $mercadoPagoAccountStore->delete();
                }

                $store->mercadoPagoAccount()->where('type', MercadoPagoApplicationTypeEnum::PAID_PRESENCIAL)->delete();

                DB::commit();
                return true; // Detiene la ejecución si se eliminó correctamente
            }
            $mercadoPagoAccount = $store->mercadoPagoAccount()->updateOrCreate(
                ['store_id' => $store->id, 'type' => MercadoPagoApplicationTypeEnum::PAID_PRESENCIAL],
                [
                    'public_key' => $request->input('mercadoPagoPublicKeyPresencial'),
                    'access_token' => $request->input('mercadoPagoAccessTokenPresencial'),
                    'secret_key' => $request->input('mercadoPagoSecretKeyPresencial'),
                    'user_id_mp' => $request->input('mercadoPagoUserIdPresencial'),
                ]
            );
            $this->mpService->setCredentials($store->id, MercadoPagoApplicationTypeEnum::PAID_PRESENCIAL->value);

            $name = $store->name;
            $externalId = 'SUC' . $store->id;
            $streetNumber = $request->input('street_number');
            $streetName = $request->input('street_name');
            $cityName = $request->input('city_name');
            $stateName = $request->input('state_name');
            $latitude = (float) $request->input('latitude');
            $longitude = (float) $request->input('longitude');
            $reference = $request->input('reference');

            // Verificar si la sucursal ya existe
            $mercadoPagoAccountStoreExist = $this->mercadoPagoAccountStoreRepository->getStoreByExternalId($store->id);

            // Preparar datos de la sucursal
            $storeData = [
                'name' => $name,
                'external_id' => $externalId,
                'location' => [
                    'street_number' => $streetNumber,
                    'street_name' => $streetName,
                    'city_name' => $cityName,
                    'state_name' => $stateName,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'reference' => $reference,
                ],
            ];
            if (!$mercadoPagoAccountStoreExist) {
                $resultMercadoPagoStore = $this->mpService->createStore($storeData);

                $this->mercadoPagoAccountStoreRepository->store([
                    'name' => $name,
                    'external_id' => $externalId,
                    'street_number' => $streetNumber,
                    'street_name' => $streetName,
                    'city_name' => $cityName,
                    'state_name' => $stateName,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'reference' => $reference,
                    'store_id' => $resultMercadoPagoStore['id'],
                    'mercado_pago_account_id' => $mercadoPagoAccount->id,
                ]);
            } else {
                unset($storeData['external_id']);
                $resultMercadoPagoStore = $this->mpService->updateStore($mercadoPagoAccountStoreExist->store_id, $storeData);
                $this->mercadoPagoAccountStoreRepository->update($mercadoPagoAccountStoreExist, [
                    'name' => $name,
                    'street_number' => $streetNumber,
                    'street_name' => $streetName,
                    'city_name' => $cityName,
                    'state_name' => $stateName,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'reference' => $reference,
                    'store_id' => $resultMercadoPagoStore['id'],
                    'mercado_pago_account_id' => $mercadoPagoAccount->id,
                ]);
            }

            DB::commit();
            return true; // Indica éxito
        } catch (MercadoPagoException $e) {
            DB::rollBack();
            // Si es un error de MercadoPago, lanzar una excepción específica
            throw new MercadoPagoException($e->getMessage(), $e->getDetails());
        }catch (\Exception $e) {
            DB::rollBack();
            // Si no es un error de MercadoPago, lanzar una excepción genérica
            throw new \Exception('Error en la integración con MercadoPago: ' . $e->getMessage());
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
    public function handleEmailConfigIntegration(UpdateStoreRequest $request, Store $store): void
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
