<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePriceListRequest;
use App\Http\Requests\UpdatePriceListRequest;
use App\Models\Client;
use App\Models\PriceList;
use App\Repositories\PriceListRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PriceListController extends Controller
{
    protected PriceListRepository $priceListRepo;

    public function __construct(PriceListRepository $priceListRepo)
    {
        $this->middleware(['check_permission:access_price-lists', 'user_has_store'])->only([
            'index', 'datatable', 'getProducts', 'getClientPriceList',
        ]);
        $this->middleware('check_permission:access_show_price-lists')->only(['show']);
        $this->middleware('check_permission:access_create_price-lists')->only(['create', 'store']);
        $this->middleware('check_permission:access_edit_price-lists')->only(['edit', 'update', 'assignToClient']);
        $this->middleware('check_permission:access_delete_price-lists')->only(['destroy']);

        $this->priceListRepo = $priceListRepo;
    }

    /**
     * Listado de listas de precios.
     */
    public function index(): View
    {
        $data = $this->priceListRepo->commonFormData();
        return view('content.e-commerce.backoffice.price-lists.index', $data);
    }

    /**
     * DataTable JSON.
     */
    public function datatable(Request $request): mixed
    {
        return $this->priceListRepo->getForDataTable($request);
    }

    /**
     * Formulario de creación.
     */
    public function create(): View
    {
        $data = $this->priceListRepo->commonFormData();
        return view('content.e-commerce.backoffice.price-lists.create', $data);
    }

    /**
     * Crea una lista de precios.
     */
    public function store(StorePriceListRequest $request): RedirectResponse
    {
        $this->assertCanManageStore((int) $request->input('store_id'));

        $priceList = $this->priceListRepo->createPriceList($request);

        return redirect()->route('price-lists.edit', $priceList->id)
            ->with('success', 'Lista de precios creada correctamente.');
    }

    /**
     * Muestra una lista de precios.
     */
    public function show(int $id): View
    {
        $priceList = $this->priceListRepo->getPriceListById($id);
        $this->assertCanManageStore((int) $priceList->store_id);

        return view('content.e-commerce.backoffice.price-lists.show', compact('priceList'));
    }

    /**
     * Formulario de edición.
     */
    public function edit(int $id): View
    {
        $priceList = $this->priceListRepo->getPriceListById($id);
        $this->assertCanManageStore((int) $priceList->store_id);

        $data = $this->priceListRepo->commonFormData();
        $data['priceList'] = $priceList;

        return view('content.e-commerce.backoffice.price-lists.edit', $data);
    }

    /**
     * Actualiza una lista de precios.
     */
    public function update(UpdatePriceListRequest $request, int $id): RedirectResponse
    {
        $priceList = PriceList::findOrFail($id);
        $this->assertCanManageStore((int) $priceList->store_id);

        $this->priceListRepo->updatePriceList($id, $request);

        return redirect()->route('price-lists.edit', $id)
            ->with('success', 'Lista de precios actualizada correctamente.');
    }

    /**
     * Elimina una lista de precios.
     */
    public function destroy(int $id): RedirectResponse
    {
        $priceList = PriceList::findOrFail($id);
        $this->assertCanManageStore((int) $priceList->store_id);

        $this->priceListRepo->deletePriceList($id);

        return redirect()->route('price-lists.index')
            ->with('success', 'Lista de precios eliminada correctamente.');
    }

    /**
     * Productos de la lista (JSON) — opcionalmente filtrados por search/filter.
     */
    public function getProducts(int $storeId, int $priceListId, Request $request): JsonResponse
    {
        $this->assertCanManageStore($storeId);

        $data = $this->priceListRepo->getProductsForPriceList(
            $storeId,
            $priceListId,
            $request->input('query'),
            $request->input('filter', 'all')
        );

        return response()->json($data);
    }

    /**
     * Asigna una lista de precios a un cliente (reemplaza la actual).
     */
    public function assignToClient(Request $request, int $clientId): JsonResponse
    {
        $request->validate([
            'price_list_id' => 'nullable|integer|exists:price_lists,id',
        ]);

        $client = Client::findOrFail($clientId);

        if ($request->filled('price_list_id')) {
            $client->priceLists()->sync([(int) $request->price_list_id]);
        } else {
            $client->priceLists()->detach();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Devuelve la lista de precios asignada a un cliente (la primera).
     */
    public function getClientPriceList(int $clientId): JsonResponse
    {
        $client = Client::with('priceLists:id,name,currency')->findOrFail($clientId);
        $priceList = $client->priceLists->first();

        return response()->json([
            'priceListId'   => $priceList?->id,
            'priceListName' => $priceList?->name,
            'currency'      => $priceList?->currency,
        ]);
    }

    /**
     * Verifica que el usuario pueda gestionar el store_id indicado.
     */
    protected function assertCanManageStore(int $storeId): void
    {
        if (Auth::user()->can('view_all_price-lists')) {
            return;
        }
        if ((int) Auth::user()->store_id !== $storeId) {
            abort(403, 'No tenés permiso para gestionar listas de precios de esta tienda.');
        }
    }
}
