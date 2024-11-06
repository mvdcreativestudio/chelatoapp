<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFlavorRequest;
use App\Repositories\ProductRepository;
use App\Models\ProductCategory;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\SwitchProductStatusRequest;
use App\Http\Requests\StoreMultipleFlavorsRequest;
use App\Http\Requests\UpdateFlavorRequest;
use App\Models\Store;
use Illuminate\Http\Request;
use App\Services\ExportService;
use App\Models\Product;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GenericExport;
use App\Imports\ProductsImport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Exports\ProductTemplateExport;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Validators\ValidationException;
use App\Models\CompanySettings;


class ProductController extends Controller
{
  /**
   * El repositorio para las operaciones de productos.
   *
   * @var ProductRepository
  */
  protected $productRepo;

  /**
   * Inyecta el repositorio en el controlador.
   *
   * @param  ProductRepository  $productRepo
  */
  public function __construct(ProductRepository $productRepo)
  {
    $this->middleware(['check_permission:access_products', 'user_has_store'])->only(
        [
            'index',
            'show',
            'create',
            'store',
            'edit',
            'update',
            'destroy',
            'switchStatus',
            'flavors',
            'storeFlavor',
            'storeMultipleFlavors',
            'editFlavor',
            'updateFlavor',
            'destroyFlavor',
            'switchFlavorStatus'
        ]
    );

    $this->productRepo = $productRepo;
  }

  /**
   * Muestra una lista de todos los productos.
   *
   * @return View
  */
  public function index(): View
  {
    if (Auth::user()->can('access_global_products')) {
      $stores = Store::select('id', 'name')->get();
      $categories = ProductCategory::select('id', 'name')->get();
    } else {
      $stores = Store::select('id', 'name')->where('id', Auth::user()->store_id)->get();
      $categories = ProductCategory::select('id', 'name')->where('store_id', Auth::user()->store_id)->get();
    }
    return view('content.e-commerce.backoffice.products.products', compact('stores', 'categories'));
  }


  /**
   * Muestra un producto específico.
   *
   * @param int $id
   * @return View
   */
  public function show(int $id): View
  {
    $product = $this->productRepo->show($id);
    return view('content.e-commerce.backoffice.products.show-product', $product);
  }

    /**
   * Muestra una lista de todos los productos para Stock.
   *
   * @return View
   */
  public function stock(): View
  {
      if (Auth::user()->can('access_global_products')) {
          $stores = Store::select('id', 'name')->get();
      } else {
          $stores = Store::select('id', 'name')->where('id', Auth::user()->store_id)->get();
      }

      return view('content.e-commerce.backoffice.products.stock', compact('stores'));
  }


  /**
   * Muestra el formulario para crear un nuevo producto.
   *
   * @return View
  */
  public function create(): View
  {
    $product = $this->productRepo->create();
    return view('content.e-commerce.backoffice.products.add-product', $product);
  }

  /**
   * Almacena un nuevo producto en la base de datos.
   *
   * @param StoreProductRequest $request
   * @return RedirectResponse
  */
  public function store(StoreProductRequest $request): RedirectResponse
  {
    $validated = $request->validated();

    $this->productRepo->createProduct($request);

    return redirect()->route('products.index')->with('success', 'Producto creado correctamente.');
  }

  /**
   * Obtiene los datos de los productos para DataTables.
   *
   * @return mixed
  */
  public function datatable(Request $request): mixed
  {
      // Pasa el request al repositorio
      return $this->productRepo->getProductsForDataTable($request);
  }

  /**
   * Muestra el formulario para editar un producto.
   *
   * @param  int  $id
   * @return View
  */
  public function edit(int $id): View
  {
    $product = $this->productRepo->edit($id);
    return view('content.e-commerce.backoffice.products.edit-product', $product) ;
  }

  /**
   * Actualiza un producto específico en la base de datos.
   *
   * @param UpdateProductRequest $request
   * @param int $id
   * @return RedirectResponse
  */
  public function update(UpdateProductRequest $request, $id)
  {
    $this->productRepo->update($id, $request);
    return redirect()->route('products.index')->with('success', 'Producto actualizado correctamente.');
  }

  /**
    * Cambia el estado de un producto.
    *
    * @param SwitchProductStatusRequest $request
    * @return JsonResponse
  */
  public function switchStatus(SwitchProductStatusRequest $request): JsonResponse
  {
    $this->productRepo->switchProductStatus($request->id);
    return response()->json(['success' => true, 'message' => 'Estado del producto actualizado correctamente.']);
  }

  /**
   * Elimina un producto de la base de datos.
   *
   * @param int $id
   * @return RedirectResponse
  */
  public function destroy(int $id): RedirectResponse
  {
    $this->productRepo->delete($id);
    return redirect()->route('products.index')->with('success', 'Producto eliminado correctamente.');
  }

  /**
   * Muestra una lista de todos los variaciones.
   *
   * @return View
  */
  public function flavors(): View
  {
    $flavors = $this->productRepo->flavors();
    return view('content.e-commerce.backoffice.products.flavors', $flavors);
  }

  /**
   * Obtiene los datos de los variaciones para DataTables.
   *
   * @return mixed
  */
  public function flavorsDatatable(): mixed
  {
    return $this->productRepo->flavorsDatatable();
  }

  /**
   * Almacena un sabor
   *
   * @param StoreFlavorRequest $request
   * @return RedirectResponse
  */
  public function storeFlavor(StoreFlavorRequest $request): RedirectResponse
  {
    $this->productRepo->storeFlavor($request);
    return redirect()->route('product-flavors')->with('success', 'Sabor creado correctamente.');
  }

  /**
   * Almacena múltiples variaciones
   *
   * @param StoreMultipleFlavorsRequest $request
   * @return JsonResponse
  */
  public function storeMultipleFlavors(StoreMultipleFlavorsRequest $request): JsonResponse
  {
    $this->productRepo->storeMultipleFlavors($request);
    return response()->json(['success' => true, 'message' => 'Variaciones múltiples creados correctamente.']);
  }

  /**
   * Muestra el formulario para editar un sabor.
   *
   * @param  int  $id
   * @return View
  */
  public function editFlavor(int $id): JsonResponse
  {
    $flavor = $this->productRepo->editFlavor($id);
    return response()->json($flavor);
  }

  /**
   * Actualiza un sabor específico en la base de datos.
   *
   * @param  UpdateFlavorRequest  $request A CAMBIAR JEJEJE
   * @param  int  $id
   * @return View
  */
  public function updateFlavor(UpdateFlavorRequest $request, int $id): JsonResponse
  {
      $flavor = $this->productRepo->updateFlavor($request, $id);
      return response()->json(['success' => true, 'message' => 'Sabor actualizado con éxito']);
  }

  /**
   * Elimina un sabor de la base de datos.
   *
   * @param  int  $id
   * @return JsonResponse
  */
  public function destroyFlavor(int $id): JsonResponse
  {
    $this->productRepo->destroyFlavor($id);
    return response()->json(['success' => true, 'message' => 'Sabor eliminado correctamente.']);
  }

  /**
   * Cambia el estado de un sabor.
   *
   * @param  int  $id
   * @return JsonResponse
  */
  public function switchFlavorStatus(int $id): JsonResponse
  {
    $this->productRepo->switchFlavorStatus($id);
    return response()->json(['success' => true, 'message' => 'Estado del sabor actualizado correctamente.']);
  }

  /**
   * Exporta los productos a un archivo de Excel.
   *
   * @param Request $request
   * @return mixed
   */
  public function exportToExcel(Request $request)
  {
      $filters = $request->all();  // Capturar todos los filtros
      $products = Product::filterData($filters)->get()->toArray();  // Asegúrate de convertir los datos a array

      return Excel::download(new GenericExport($products), 'productos_filtrados.xlsx');
  }

  /**
   * Importa productos desde un archivo de Excel.
   *
   * @param Request $request
   * @return RedirectResponse
   */
  public function import(Request $request)
  {
      $request->validate([
          'file' => 'required|mimes:xlsx|max:2048',
      ]);

      $storeId = Auth::user()->store_id;

      try {
          $import = new ProductsImport($storeId);
          Excel::import($import, $request->file('file'));

          $message = "Importación completada. Se procesaron {$import->getRowCount()} filas.";
          Log::info($message);

          return response()->json([
              'success' => true,
              'message' => $message
          ]);
      } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
          $failures = $e->failures();
          $errors = collect($failures)->map(function ($failure) {
              return "Fila {$failure->row()}: " . implode(', ', $failure->errors());
          })->filter()->values()->toArray();

          Log::warning('Errores de validación en la importación:', $errors);

          return response()->json([
              'success' => false,
              'message' => 'Algunos datos del archivo son inválidos.',
              'errors' => $errors
          ], 422);
      } catch (\Exception $e) {
          Log::error('Error en importación de productos: ' . $e->getMessage());
          return response()->json([
              'success' => false,
              'message' => 'Hubo un error durante la importación: ' . $e->getMessage()
          ], 500);
      }
  }

  private function isEmptyRow(array $row): bool
  {
      return empty(array_filter($row, function ($value) {
          return $value !== null && $value !== '';
      }));
  }

  /**
   * Muestra el formulario para editar productos en masa.
   *
   * @return View
   */
  public function editBulk(): View
  {
      $data = $this->productRepo->getProductsForBulkEdit();
      return view('products.editBulk', $data);
  }

  /**
   * Actualiza los productos en masa.
   *
   * @param Request $request
   * @return RedirectResponse
   */
  public function updateBulk(Request $request): RedirectResponse
  {
      $products = $request->input('products');
      $this->productRepo->updateBulk($products);
      return redirect()->route('products.editBulk')->with('success', 'Productos actualizados correctamente.');
  }

  /**
   * Muestra el formulario para agregar productos en masa.
   *
   * @return View
   */
  public function addBulk(): View
  {
      $stores = $this->productRepo->getStoresForBulkAdd();

      if (Auth::user()->can('access_global_products')) {
        $categories = ProductCategory::all();
      } else {
          // Si no tiene el permiso, mostrar solo las categorías asociadas a su empresa
          $categories = ProductCategory::where('store_id', Auth::user()->store_id)->get();
      }

      return view('products.addBulk', compact('stores', 'categories'));
  }

  /**
   * Almacena los productos en masa.
   *
   * @param Request $request
   * @return RedirectResponse
   */
  public function storeBulk(Request $request): RedirectResponse
  {
      $products = $request->input('products');
      $this->productRepo->storeBulk($products);
      return redirect()->route('products.addBulk')->with('success', 'Productos agregados correctamente.');
  }

  /**
   * Descarga una plantilla de productos.
   *
   * @param Request $request
   * @return mixed
   */
  public function downloadTemplate(Request $request)
  {
      $storeId = Auth::user()->store_id;
      $settings = CompanySettings::first();

      // Verificar el valor de categories_has_store
      if ($settings->categories_has_store == 1) {
          // Si categories_has_store es 1, solo tomamos las categorías filtradas por store_id
          $categories = ProductCategory::where('store_id', $storeId)->get();
          Log::info('Filtrando categorías por store_id: ' . $storeId);
      } else {
          // Si categories_has_store es 0, tomamos todas las categorías (incluso las que tienen store_id null)
          $categories = ProductCategory::all();
          Log::info('Tomando todas las categorías, sin filtrar por store_id.');
      }

      // Log para ver todas las categorías obtenidas
      Log::info('Categorías recuperadas desde el controlador: ', $categories->toArray());

      return Excel::download(new ProductTemplateExport($categories, $storeId, $settings), 'plantilla_productos.xlsx');
  }


}
