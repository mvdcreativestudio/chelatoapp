<?php

namespace App\Http\Controllers;

use App\Repositories\PriceListRepository;
use App\Http\Requests\StorePriceListRequest;
use App\Http\Requests\UpdatePriceListRequest;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use App\Models\Store;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use Illuminate\Support\Facades\Log;



class PriceListController extends Controller
{
    protected $priceListRepo;

      /**
   * Inyecta el repositorio en el controlador.
   *
   * @param  PriceListRepository  $priceListRepo
    */
    public function __construct(PriceListRepository $priceListRepo)
    {
        $this->middleware(['check_permission:access_price-lists'])->only(['index', 'show', 'create', 'store', 'edit', 'update', 'destroy']);
        $this->middleware('check_permission:access_show-price-lists')->only('show', 'getProducts');
        $this->middleware('check_permission:access_create-price-lists')->only('create', 'store');
        $this->middleware('check_permission:access_edit-price-lists')->only('edit', 'update');
        $this->middleware('check_permission:access_delete-price-lists')->only('destroy');
    
        $this->priceListRepo = $priceListRepo;
    }

    /**
     * Muestra un listado de todas las listas de precios, junto con las variables necesarias para los filtros.
     *
     * @return View
     */
    public function index(): View
    {
        // Verificar si el usuario tiene permiso para acceder a todas las tiendas o solo a las suyas
        if (Auth::user()->can('view_all_stores') && Auth::user()->can('view_all_price-lists')) {
            $stores = Store::select('id', 'name')->get();
        } else {
            $stores = Store::select('id', 'name')->where('id', Auth::user()->store_id)->get();
        }

        // Devolver la vista con las listas de precios y las tiendas para los filtros
        return view('content.e-commerce.backoffice.price-lists.index', compact('stores'));
    }



    /**
     * Muestra el formulario para crear una nueva lista de precios.
     *
     * @return View
     */
    public function create(): View
    {
        $stores = Store::all();
        return view('content.e-commerce.backoffice.price-lists.create', compact('stores'));
    }

    /**
     * Almacena una nueva lista de precios.
     *
     * @param StorePriceListRequest $request
     * @return RedirectResponse
     */
    public function store(StorePriceListRequest $request): RedirectResponse
    {
        // Guardar la nueva lista de precios
        $priceList = $this->priceListRepo->createPriceList($request->validated());
        
        // Verificar el origen de la solicitud
        $source = $request->input('source');

        // Redirigir dependiendo de la vista de origen
        if ($source === 'clients') {
            // Redirigir a la vista de clientes si el origen es la vista de clientes
            return redirect()->route('clients.index')->with('success', 'Lista de precios creada correctamente.');
        } else {
            // Si el origen es otra vista, redirigir a la edición de la lista de precios
            return redirect()->route('price-lists.edit', $priceList->id)->with('success', 'Lista de precios creada correctamente.');
        }
    }

    

    /**
     * Muestra una lista de precios individual.
     *
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        $priceList = $this->priceListRepo->getPriceListById($id);

        // Verificar si el usuario tiene permiso para ver todas las listas o solo las de su tienda
        if (!Auth::user()->can('view_all_price-lists') && $priceList->store_id !== Auth::user()->store_id) {
            abort(403, 'No tienes permiso para ver esta lista de precios.');
        }

        return view('content.e-commerce.backoffice.price-lists.show', compact('priceList'));
    }


    /**
     * Muestra el formulario para editar una lista de precios.
     *
     * @param int $id
     * @return View
     */
    public function edit(int $id): View
    {
        $priceList = $this->priceListRepo->getPriceListById($id);
        $stores = Store::all();
        return view('content.e-commerce.backoffice.price-lists.edit', compact('priceList', 'stores'));
    }

    /**
     * Actualiza una lista de precios.
     *
     * @param UpdatePriceListRequest $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(UpdatePriceListRequest $request, $id): RedirectResponse
    {
        Log::info('Solicitud recibida para actualizar lista de precios');
        Log::info($request->all());


        $priceList = $this->priceListRepo->updatePriceList($id, $request->only(['name', 'description']));

        $prices = $request->input('prices', []);
        foreach ($prices as $productId => $price) {
            if (!empty($price)) {
                // Guardar solo si hay un precio
                $this->priceListRepo->addProductToPriceList($priceList->id, $productId, $price);
            }
        }

        return redirect()->route('price-lists.edit', $priceList->id)->with('success', 'Lista de precios actualizada correctamente.');
    }
    
    /**
     * Elimina una lista de precios.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->priceListRepo->deletePriceList($id);
        return redirect()->route('price-lists.index')->with('success', 'Lista de precios eliminada correctamente.');
    }

    /**
     * Obtiene todas las listas de precios para mostrarlas en la vista.
     *
     * @return JsonResponse
     */
    public function datatable(): JsonResponse
    {
        if(Auth::user()->can('view_all_price-lists')) {
            $priceLists = $this->priceListRepo->getAll();
        } else {
            $priceLists = $this->priceListRepo->getByStoreId(Auth::user()->store_id);
        }
        
        $data = [];

        // Formatear la respuesta para enviarla al frontend
        foreach ($priceLists as $priceList) {
            $data[] = [
                'id' => $priceList->id,
                'name' => $priceList->name,
                'description' => $priceList->description ?? 'Sin descripción',
                'store_name' => $priceList->store->name ?? 'Sin tienda asociada',
                'products_count' => $priceList->products_count ?? 0,
            ];
        }

        return response()->json(['data' => $data]);
    }


    /**
     * Obtiene los productos de una tienda específica.
     * 
     * @param int $storeId
     * @return JsonResponse
     */
    public function getProducts($storeId, $priceListId): JsonResponse
    {
        // Obtener productos de la tienda seleccionada junto con el precio asignado en la lista de precios
        $products = Product::where('store_id', $storeId)
            ->leftJoin('price_list_products', function($join) use ($priceListId) {
                $join->on('products.id', '=', 'price_list_products.product_id')
                     ->where('price_list_products.price_list_id', '=', $priceListId);
            })
            ->select('products.id', 'products.name', 'price_list_products.price') // Seleccionar el nombre y el precio del producto
            ->get();
    
        return response()->json(['products' => $products]);
    }
    
    

}
