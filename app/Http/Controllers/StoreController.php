<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Repositories\StoreRepository;
use App\Http\Requests\StoreStoreRequest;
use App\Http\Requests\UpdateStoreRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;


class StoreController extends Controller
{
    /**
     * El repositorio para las operaciones de tiendas.
     *
     * @var StoreRepository
     */
    protected $storeRepository;

    /**
     * Inyecta el repositorio en el controlador.
     *
     * @param  StoreRepository  $storeRepository
     */
    public function __construct(StoreRepository $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }

    /**
     * Muestra una lista de todas las tiendas.
     *
     * @return View
     */
    public function index(): View
    {
        $stores = $this->storeRepository->getAll();
        return view('stores.index', compact('stores'));
    }

    /**
     * Muestra el formulario para crear una nueva tienda.
     *
     * @return View
     */
    public function create(): View
    {
        return view('stores.create');
    }

    public function store(StoreStoreRequest $request): RedirectResponse
    {
        // Primero, crea la tienda sin los datos de MercadoPago
        $storeData = $request->validated();
        unset($storeData['mercadoPagoPublicKey'], $storeData['mercadoPagoAccessToken'], $storeData['mercadoPagoSecretKey'], $storeData['accepts_mercadopago']);
        $store = $this->storeRepository->create($storeData);

        // Luego, si se ha indicado que la tienda acepta MercadoPago, crea la cuenta de MercadoPago asociada
        if ($request->boolean('accepts_mercadopago')) {
            $mercadoPagoData = [
                'store_id' => $store->id, // Asume que el método create del repositorio devuelve el modelo de tienda creado
                'public_key' => $request->input('mercadoPagoPublicKey'),
                'access_token' => $request->input('mercadoPagoAccessToken'),
                'secret_key' => $request->input('mercadoPagoSecretKey'),
            ];

            // Asegúrate de tener un método para crear la cuenta de MercadoPago en el repositorio o en el modelo de la tienda
            $store->mercadoPagoAccount()->create($mercadoPagoData);
        }

        return redirect()->route('stores.index')->with('success', 'Tienda creada con éxito.');
    }


    /**
     * Muestra una tienda específica.
     *
     * @param  Store  $store
     * @return View
     */
    public function show(Store $store): View
    {
        return view('stores.show', compact('store'));
    }

    /**
     * Muestra el formulario para editar una tienda existente.
     *
     * @param  Store  $store
     * @return View
     */
    public function edit(Store $store): View
    {
        return view('stores.edit', compact('store'));
    }

    /**
     * Actualiza una tienda específica en la base de datos.
     *
     * Aquí es donde se utiliza UpdateStoreRequest para validar la solicitud antes de proceder.
     *
     * @param  UpdateStoreRequest  $request
     * @param  Store  $store
     * @return RedirectResponse
     */
    public function update(UpdateStoreRequest $request, Store $store): RedirectResponse
    {
        // Actualizar datos generales de la tienda
        $storeData = $request->validated();
        $this->storeRepository->update($store, Arr::except($storeData, ['mercadoPagoPublicKey', 'mercadoPagoAccessToken', 'mercadoPagoSecretKey', 'accepts_mercadopago']));

        // Manejar activación/desactivación y actualización de datos de MercadoPago
        if ($request->boolean('accepts_mercadopago')) {
            $mercadoPagoData = [
                'public_key' => $request->input('mercadoPagoPublicKey'),
                'access_token' => $request->input('mercadoPagoAccessToken'),
                'secret_key' => $request->input('mercadoPagoSecretKey'),
            ];
            // Crear o actualizar la cuenta MercadoPago
            $store->mercadoPagoAccount()->updateOrCreate(['store_id' => $store->id], $mercadoPagoData);
        } else {
            // Si se desactiva MercadoPago, eliminar la vinculación (si existe)
            $store->mercadoPagoAccount()->delete();
        }

        return redirect()->route('stores.index')->with('success', 'Tienda actualizada con éxito.');
    }


    /**
     * Cambia el estado de la tienda.
     *
     * @param  Store  $store
     * @return RedirectResponse
     */
    public function destroy(Store $store): RedirectResponse
    {
        $this->storeRepository->update($store, ['status' => !$store->status]);
        return redirect()->route('stores.index')->with('success', 'Tienda actualizada con éxito.');
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
        $userId = $request->get('user_id');
        $store = $this->storeRepository->associateUser($store, $userId);

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
        $userId = $request->get('user_id');
        $store = $this->storeRepository->disassociateUser($store, $userId);

        return redirect()->back()->with('success', 'Usuario desasociado con éxito.');
    }
}
