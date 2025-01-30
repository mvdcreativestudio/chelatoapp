<?php

namespace App\Http\Controllers;

use App\Enums\MercadoPago\MercadoPagoApplicationTypeEnum;
use App\Exceptions\MercadoPagoException;
use App\Helpers\Helpers;
use App\Http\Middleware\EnsureUserCanAccessStore;
use App\Http\Requests\StoreStoreRequest;
use App\Http\Requests\UpdateStoreRequest;
use App\Models\Store;
use App\Models\TaxRate;
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
        $taxRates = TaxRate::all(); // Obtener todas las tasas de impuestos
        return view('stores.create', [
            'googleMapsApiKey' => config('services.google.maps_api_key'),
            'taxRates' => $taxRates
        ]);
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

        // Obtener todas las tasas de IVA disponibles
        $taxRates = TaxRate::all();

        return view('stores.edit', compact('store', 'googleMapsApiKey', 'companyInfo', 'logoUrl', 'taxRates'));
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
    Log::info('Datos enviados al actualizar la tienda:', $request->all());

    try {
        // Validar los datos enviados en la request
        $storeData = $request->validated();
        Log::info('Datos validados:', $storeData);

        // Validar exclusividad entre Scanntech y Fiserv
        if ($request->boolean('scanntech') && $request->boolean('fiserv')) {
            return redirect()->back()->withErrors([
                'error' => 'Solo puede estar activo un proveedor de POS a la vez. Desactive una opción antes de activar la otra.'
            ]);
        }

        // Determinar el valor de pos_provider_id
        if ($request->boolean('scanntech')) {
            $storeData['pos_provider_id'] = 1; // Scanntech
            Log::info('Scanntech activado');
        } elseif ($request->boolean('fiserv')) {
            $storeData['pos_provider_id'] = 2; // Fiserv
            Log::info('Fiserv activado');
        } else {
            $storeData['pos_provider_id'] = null; // Ninguno
            Log::info('Ningún proveedor POS activado');
        }

            // Actualización de la tienda excluyendo datos de integraciones específicas
            $this->storeRepository->update($store, $storeData);

            // Actualizar la tasa de IVA seleccionada
            $store->tax_rate_id = $request->tax_rate_id;
            $store->save();

        Log::info('Estado de la tienda después de la actualización:', $store->toArray());

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

        // Manejo de la integración de Fiserv
        $this->handleFiservIntegration($request, $store);

            return redirect()->route('stores.edit', $store->id)->with('success', 'Empresa actualizada con éxito.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar la empresa: ' . $e->getMessage());
            return redirect()
                ->route('stores.edit', $store->id)
                ->with('error', 'Ocurrió un error durante la actualización: ' . $e->getMessage());
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
