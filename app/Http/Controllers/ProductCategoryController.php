<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Repositories\ProductCategoryRepository;
use App\Http\Requests\StoreProductCategoryRequest;
use App\Http\Requests\UpdateProductCategoryRequest;
use Illuminate\Http\JsonResponse;
use App\Models\Store;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    /**
     * El repositorio para las operaciones de categorías de productos.
     *
     * @var ProductCategoryRepository
    */
    protected $productCategoryRepo;

    /**
     * Inyecta el repositorio en el controlador.
     *
     * @param  ProductCategoryRepository  $productCategoryRepo
    */
    public function __construct(ProductCategoryRepository $productCategoryRepo)
    {
        $this->middleware(['check_permission:access_product-categories'])->only([
            'index', 'create', 'store', 'edit', 'update', 'destroy', 'datatable'
        ]);
        $this->productCategoryRepo = $productCategoryRepo;
    }

    public function index(): View
    {
        $stores = Store::all();

        // Obtener las categorías junto con el conteo de productos y el total de stock
        $data = $this->productCategoryRepo->getCategories();

        // Pasar las variables adicionales a la vista
        return view('content.e-commerce.backoffice.product-categories.product-categories', [
            'categories' => $data['categories'],  // Las categorías
            'totalCategories' => $data['total_categories'],  // Total de categorías
            'categoryWithMostProducts' => $data['category_with_most_products'],  // Categoría con más productos
            'categoryWithMostStock' => $data['category_with_most_stock'],  // Categoría con más stock
            'stores' => $stores,  // Las tiendas
        ]);
    }

    /**
     * Muestra el formulario para crear una nueva categoría de producto.
     *
     * @return View
    */
    public function create(): View
    {
      $stores = Store::all();
      return view('content.e-commerce.backoffice.product-categories.add-category', compact('stores'));
    }

    /**
     * Almacena una nueva categoría de producto en la base de datos.
     *
     * @param StoreProductCategoryRequest $request
     * @return RedirectResponse
    */
    public function store(StoreProductCategoryRequest $request): RedirectResponse
    {
      $this->productCategoryRepo->store($request);
      return redirect()->route('product-categories.index')->with('success', 'Categoría creada correctamente.');
    }

    /**
     * Muestra el formulario para editar una categoría de producto.
     *
     * @param ProductCategory $category
     * @return View
    */
    public function edit(ProductCategory $category): View
    {
      return view('content.e-commerce.backoffice.product-categories.edit-category', compact('category'));
    }

    /**
     * Actualiza una categoría de producto en la base de datos.
     *
     * @param UpdateProductCategoryRequest $request
     * @param ProductCategory $category
     * @return RedirectResponse
    */
    public function update(UpdateProductCategoryRequest $request, ProductCategory $category): RedirectResponse
    {
      $this->productCategoryRepo->update($request, $category);
      return redirect()->route('product-categories.index')->with('success', 'Categoría actualizada correctamente.');
    }


    /**
     * Actualiza una categoría de producto en la base de datos dado un ID.
     *
     * @param UpdateProductCategoryRequest $request
     * @param int $id
     * @return JsonResponse
    */
    public function updateSelected(UpdateProductCategoryRequest $request, int $id): JsonResponse
    {
      $this->productCategoryRepo->updateSelected($request, $id);
      return response()->json(['success' => 'Categoría actualizada correctamente.']);
    }


    /**
     * Encuentra una categoría dada un ID.
     *
     * @param int $id
    */
    public function getSelected($id)
    {
      $category = $this->productCategoryRepo->getSelected($id);

      if($category)
      {
        return response()->json($category);
      } else {
        return response()->json(['error' => 'Categoría no encontrada'], 404);
      }
    }

    /**
     * Elimina una categoría de producto de la base de datos.
     *
     * @param ProductCategory $category
     * @return RedirectResponse
    */
    public function destroy(ProductCategory $category): RedirectResponse
    {
      $this->productCategoryRepo->destroy($category);
      return redirect()->route('product-categories.index')->with('success', 'Categoría eliminada correctamente.');
    }

    /**
     * Elimina una categoría dado un ID.
     *
     * @param ProductCategory $category
     * @return RedirectResponse
    */
    public function deleteSelected($id): RedirectResponse
    {
      if($this->productCategoryRepo->deleteSelected($id)){
        return redirect()->route('product-categories.index')->with('success', 'Categoría eliminada correctamente.');
      }
      return redirect()->route('product-categories.index')->with('error', 'No se ha podido eliminar la categoría.');
    }

    /**
     * Obtiene los datos de las categorías de productos para DataTables.
     *
     * @param Request $request
     * @return mixed
     */
    public function datatable(Request $request): mixed
    {
        return $this->productCategoryRepo->datatable($request);
    }
}
