<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompositeProductRequest;
use App\Http\Requests\UpdateCompositeProductRequest;
use App\Repositories\CompositeProductRepository;
use App\Models\CompositeProduct;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CompositeProductController extends Controller
{
    /**
     * El repositorio para las operaciones de productos compuestos.
     *
     * @var CompositeProductRepository
     */
    protected $compositeProductRepository;

    /**
     * Inyecta el repositorio en el controlador y los middleware.
     *
     * @param CompositeProductRepository $compositeProductRepository
     */
    public function __construct(CompositeProductRepository $compositeProductRepository)
    {
        $this->middleware(['check_permission:access_composite-products', 'user_has_store'])->only(
            ['index', 'create', 'show', 'datatable']
        );

        $this->middleware(['check_permission:access_delete_composite-products'])->only(
            ['destroy', 'deleteMultiple']
        );

        $this->compositeProductRepository = $compositeProductRepository;
    }

    /**
     * Muestra una lista de todos los productos compuestos.
     *
     * @return View
     */
    public function index(): View
    {
        $compositeProducts = $this->compositeProductRepository->getAllCompositeProducts();
        $stores = $this->compositeProductRepository->getAllStores();
        $mergeData = array_merge($compositeProducts, compact('stores'));
        return view('content.e-commerce.backoffice.composite-products.index', $mergeData);
    }

    /**
     * Muestra el formulario para crear un nuevo producto compuesto.
     *
     * @return View
     */
    public function create(): View
    {

        $data = $this->compositeProductRepository->create();
        // Retornar la vista con los datos necesarios
        return view('content.e-commerce.backoffice.composite-products.add-composite-product', $data);
    }

    /**
     * Almacena un nuevo producto compuesto en la base de datos.
     *
     * @param StoreCompositeProductRequest $request
     * @return RedirectResponse
     */
    public function store(StoreCompositeProductRequest $request): JsonResponse
    {
        try {
            $compositeProduct = $this->compositeProductRepository->store($request->validated());
            return response()->json($compositeProduct);
            // return redirect()->route('composite-products.index')->with('success', 'Producto compuesto guardado correctamente.');
            // return json
            // return response()->json(['success' => true, 'message' => 'Producto compuesto guardado correctamente.']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            // return redirect()->route('composite-products.index')->with('error', 'Error al guardar el producto compuesto.');
            return response()->json(['success' => false, 'message' => 'Error al guardar el producto compuesto.'], 400);
            // return response()->json(['error' => 'Error al guardar el producto compuesto.'], 400);
        }
    }

    /**
     * Muestra un producto compuesto específico.
     *
     * @param CompositeProduct $compositeProduct
     * @return View
     */
    public function show(int $id): View
    {
        $compositeProduct = CompositeProduct::with('details.product', 'store')->findOrFail($id);
        return view('content.e-commerce.backoffice.composite-products.detail-composite-product', compact('compositeProduct'));
    }

    /**
     * Devuelve datos para un producto compuesto específico.
     * 
     * @param int $id
     * @return Mixed
     */
    public function edit(int $id): Mixed
    {
        try {
            $compositeProduct = $this->compositeProductRepository->getCompositeProductById($id);
            // return response()->json($compositeProduct);
            return view('content.e-commerce.backoffice.composite-products.edit-composite-product', $compositeProduct);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            // return response()->json(['error' => 'Error al obtener los datos del producto compuesto.'], 400);
            return redirect()->route('composite-products.index')->with('error', 'Error al obtener los datos del producto compuesto.');
        }
    }

    /**
     * Actualiza un producto compuesto específico.
     *
     * @param UpdateCompositeProductRequest $request
     * @param CompositeProduct $compositeProduct
     * @return JsonResponse
     */
    public function update(UpdateCompositeProductRequest $request, CompositeProduct $compositeProduct): JsonResponse
    {
        try {
            $compositeProduct = $this->compositeProductRepository->update($compositeProduct, $request->validated());
            return response()->json($compositeProduct);
            // return redirect()->route('composite-products.index')->with('success', 'Producto compuesto actualizado correctamente.');
        } catch (\Exception $e) {
            dd($e->getMessage());
            Log::error($e->getMessage());
            return response()->json(['error' => 'Error al actualizar el producto compuesto.'], 400);
            // return redirect()->route('composite-products.index')->with('error', 'Error al actualizar el producto compuesto.');
        }
    }

    /**
     * Elimina un producto compuesto específico.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->compositeProductRepository->destroyCompositeProduct($id);
            return response()->json(['success' => true, 'message' => 'Producto compuesto eliminado correctamente.']);
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar el producto compuesto.'], 400);
        }
    }

    /**
     * Elimina varios productos compuestos.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteMultiple(Request $request): JsonResponse
    {
        try {
            $this->compositeProductRepository->deleteMultipleCompositeProducts($request->input('ids'));
            return response()->json(['success' => true, 'message' => 'Productos compuestos eliminados correctamente.']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar los productos compuestos.'], 400);
        }
    }

    /**
     * Obtiene los productos compuestos para la DataTable.
     *
     * @return mixed
     */
    public function datatable(Request $request): mixed
    {
        return $this->compositeProductRepository->getCompositeProductsForDataTable($request);
    }
}
