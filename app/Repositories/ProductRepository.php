<?php

namespace App\Repositories;

use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use Yajra\DataTables\DataTables;
use App\Models\ProductCategory;
use App\Models\Store;
use App\Models\RawMaterial;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Flavor;
use App\Models\Recipe;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Requests\StoreFlavorRequest;
use App\Http\Requests\StoreMultipleFlavorsRequest;
use App\Http\Requests\UpdateFlavorRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;



class ProductRepository
{
  /**
   * Muestra el formulario para crear un nuevo producto.
   *
   * @return array
  */
  public function create(): array
  {
    // Verificar si el usuario tiene permiso para ver todas las categorías
    if (Auth::user()->can('access_global_products')) {
        $categories = ProductCategory::all();
        $stores = Store::all();
    } else {
        // Si no tiene el permiso, mostrar solo las categorías y tiendas asociadas a su tienda
        $categories = ProductCategory::where('store_id', Auth::user()->store_id)->get();
        $stores = Store::where('id', Auth::user()->store_id)->get();
    }

    $flavors = Flavor::all();
    $rawMaterials = RawMaterial::all();

    return compact('stores', 'categories', 'flavors', 'rawMaterials');
  }

  /**
   * Muestra un producto específico.
   *
   * @param int $id
   * @return array
   */
  public function show(int $id): array
  {
      $product = Product::with('categories', 'store', 'flavors', 'recipes.rawMaterial', 'recipes.usedFlavor')
                        ->findOrFail($id);

      return compact('product');
  }


  /**
   * Almacena un nuevo producto en base de datos.
   *
   * @param  StoreProductRequest  $request
   * @return Product
   */
  public function createProduct(StoreProductRequest $request): Product
  {
      // Se crea una nueva instancia del modelo Product.
      $product = new Product();
      // Se rellenan los campos del producto con los datos del formulario.
      $product->fill($request->only([
          'name', 'sku', 'description', 'type', 'max_flavors', 'old_price',
          'price', 'discount', 'store_id', 'status', 'stock', 'safety_margin', 'bar_code', 'build_price'
      ]));

      // Manejo de la imagen
      if ($request->hasFile('image')) {
          $file = $request->file('image');
          $filename = time() . '.' . $file->getClientOriginalExtension();
          $path = $file->move(public_path('assets/img/ecommerce-images'), $filename);
          $product->image = 'assets/img/ecommerce-images/' . $filename;
      } else {
          // Si no se carga una imagen, asignar la imagen por defecto
          $product->image = 'assets/img/ecommerce-images/placeholder.png';
      }

      // Se establece el estado de borrador del producto.
      $product->draft = $request->action === 'save_draft' ? 1 : 0;
      // Se guarda el producto en la base de datos.
      $product->save();

      // Se sincronizan las categorías del producto.
      $product->categories()->sync($request->input('categories', []));

      // Se sincronizan los variaciones del producto si se han seleccionado variaciones.
      if ($request->filled('flavors')) {
          $product->flavors()->sync($request->flavors);
      }

      // Manejar recetas para productos simples
      if ($request->input('type') === 'simple') {
          $recipes = $request->input('recipes', []);
          foreach ($recipes as $recipe) {
              if (isset($recipe['raw_material_id']) && isset($recipe['quantity'])) {
                  Recipe::create([
                      'product_id' => $product->id,
                      'raw_material_id' => $recipe['raw_material_id'],
                      'quantity' => $recipe['quantity'],
                  ]);
              }
              if (isset($recipe['used_flavor_id']) && isset($recipe['units_per_bucket'])) {
                  Recipe::create([
                      'product_id' => $product->id,
                      'used_flavor_id' => $recipe['used_flavor_id'],
                      'quantity' => (1 / $recipe['units_per_bucket']),
                  ]);
              }
          }
      }

      return $product;
  }

  /**
   * Obtiene los datos de los productos para DataTables.
   *
   * @return mixed
  */
  public function getProductsForDataTable(Request $request): mixed
  {

      // Iniciar la consulta
      $query = Product::with(['categories:id,name', 'store:id,name'])
          ->select([
              'id', 'name', 'sku', 'description', 'type', 'old_price', 'price',
              'discount', 'image', 'store_id', 'status', 'draft', 'stock', 'safety_margin', 'build_price'
          ])
          ->where('is_trash', '!=', 1);

      // Filtrar por rol del usuario
      if (!Auth::user()->hasRole('Administrador')) {
          $query->where('store_id', Auth::user()->store_id);
      }

      // Aplicar filtros si están presentes en la solicitud
      if ($request->has('search') && !empty($request->search)) {
        $query->where(function($q) use ($request) {
            $q->where('name', 'like', '%' . $request->search . '%')
              ->orWhere('bar_code', 'like', '%' . $request->search . '%');

        });
      }

      if ($request->has('store_id') && !empty($request->store_id)) {
          $query->where('store_id', $request->store_id);
      }

      if ($request->has('status') && isset($request->status)) {
          $query->where('status', $request->status);
      }

      // Filtrar por rango de stock
      if ($request->has('min_stock') && isset($request->min_stock)) {
          $query->where('stock', '>=', $request->min_stock);
      }

      if ($request->has('max_stock') && isset($request->max_stock)) {
          $query->where('stock', '<=', $request->max_stock);
      }

      if ($request->has('category_id') && !empty($request->category_id)) {
        $query->whereHas('categories', function ($q) use ($request) {
            $q->where('product_categories.id', $request->category_id); // Especificamos que el 'id' viene de la tabla 'product_categories'
        });
      }



      // Aplicar la lógica de ordenamiento por stock
      if ($request->has('sort_stock')) {
          switch ($request->sort_stock) {
              case 'high_stock':
                  $query->orderBy('stock', 'desc');  // Mayor stock
                  break;
              case 'low_stock':
                  $query->orderBy('stock', 'asc');   // Menor stock
                  break;
              case 'no_stock':
                  $query->where('stock', '=', 0);    // Sin stock
                  break;
          }
      }

      // Preparar los datos para DataTables
      $dataTable = DataTables::of($query)
          ->addColumn('category', function ($product) {
              return $product->categories->implode('name', ', ');
          })
          ->addColumn('store_name', function ($product) {
              return $product->store->name;
          })
          ->make(true);

      return $dataTable;
  }




  /**
   * Devuelve un producto específico.
   *
   * @param  int  $id
   * @return array
  */
  public function edit(int $id): array
  {
    $product = Product::with('categories', 'flavors', 'recipes.rawMaterial', 'recipes.usedFlavor')->findOrFail($id);
    $stores = Store::all();
    $flavors = Flavor::all();
    $rawMaterials = RawMaterial::all();

    // Verificar si el usuario tiene permiso para ver todas las categorías
    if (Auth::user()->can('access_global_products')) {
        $categories = ProductCategory::all();
    } else {
        // Si no tiene el permiso, mostrar solo las categorías asociadas a su tienda
        $categories = ProductCategory::where('store_id', Auth::user()->store_id)->get();
    }

    return compact('product', 'stores', 'categories', 'flavors', 'rawMaterials');
  }

  /**
   * Actualiza un producto específico en la base de datos.
   *
   * @param  int  $id
   * @param  UpdateProductRequest  $request
   * @return Product
  */
  public function update(int $id, UpdateProductRequest $request): Product
  {
    $product = Product::findOrFail($id);

    $originalType = $product->type;

    $product->update($request->only([
        'name', 'sku', 'description', 'type', 'max_flavors', 'old_price',
        'price', 'discount', 'store_id', 'status', 'stock', 'safety_margin', 'bar_code', 'build_price'
    ]));

    // Manejo de la imagen si se ha subido un archivo
    if ($request->hasFile('image')) {
      $file = $request->file('image');
      $filename = time() . '.' . $file->getClientOriginalExtension();
      $path = $file->move(public_path('assets/img/ecommerce-images'), $filename);
      if ($path) {
          $product->image = 'assets/img/ecommerce-images/' . $filename;
          $product->save();  // Asegurarse de guardar el producto después de actualizar la imagen
      }
    }

    $product->categories()->sync($request->input('categories', []));
    if ($request->filled('flavors')) {
        $product->flavors()->sync($request->flavors);
    }

    if ($originalType === 'simple' && $request->input('type') === 'configurable') {
        $product->recipes()->delete();
    }

    if ($originalType === 'configurable' && $request->input('type') === 'simple') {
        $product->flavors()->detach();
    }

    if ($request->input('type') === 'simple') {
        $newRecipes = collect($request->input('recipes', []));

        // Filtrar recetas por raw_material_id o used_flavor_id
        $newRawMaterialIds = $newRecipes->pluck('raw_material_id')->filter();
        $newUsedFlavorIds = $newRecipes->pluck('used_flavor_id')->filter();

        // Eliminar recetas no presentes en las nuevas recetas
        $product->recipes()
            ->whereNotIn('raw_material_id', $newRawMaterialIds)
            ->orWhereNotIn('used_flavor_id', $newUsedFlavorIds)
            ->delete();

        // Crear o actualizar recetas
        foreach ($newRecipes as $recipe) {
            if (isset($recipe['raw_material_id'])) {
                $existingRecipe = Recipe::where('product_id', $product->id)
                    ->where('raw_material_id', $recipe['raw_material_id'])
                    ->first();

                if ($existingRecipe) {
                    if (isset($recipe['quantity'])) {
                      $existingRecipe->quantity = $recipe['quantity'];
                      $existingRecipe->save();
                    }
                } else {
                    Recipe::create([
                        'product_id' => $product->id,
                        'raw_material_id' => $recipe['raw_material_id'],
                        'quantity' => $recipe['quantity'],
                    ]);
                }
            }

            if (isset($recipe['used_flavor_id'])) {
                $existingRecipe = Recipe::where('product_id', $product->id)
                    ->where('used_flavor_id', $recipe['used_flavor_id'])
                    ->first();

                if ($existingRecipe) {
                    if (isset($recipe['units_per_bucket'])) {
                      $existingRecipe->quantity = 1 / $recipe['units_per_bucket'];
                      $existingRecipe->save();
                    }
                } else {
                    Recipe::create([
                        'product_id' => $product->id,
                        'used_flavor_id' => $recipe['used_flavor_id'],
                        'quantity' => 1 / $recipe['units_per_bucket'],
                    ]);
                }
            }
        }
    }

    return $product;
  }

  /**
   * Cambia el estado de un producto.
   *
   * @param  int  $id
   * @return Product
  */
  public function switchProductStatus(int $id): Product
  {
    $product = Product::findOrFail($id);
    $product->status = $product->status == '1' ? '2' : '1';
    $product->save();

    return $product;
  }

  /**
   * Elimina un producto de la base de datos.
   *
   * @param  int  $id
   * @return Product
  */
  public function delete(int $id): Product
  {
    $product = Product::findOrFail($id);
    $product->is_trash = 1;
    $product->save();

    return $product;
  }

    /**
     * Obtiene los variaciones de los productos y las estadísticas necesarias para las cards.
     *
     * @return array
    */
    public function flavors(): array
    {
      $flavors = Flavor::all();
      $rawMaterials = RawMaterial::all();
      $totalFlavors = $flavors->count();
      $activeFlavors = $flavors->where('status', 'active')->count();
      $inactiveFlavors = $flavors->where('status', 'inactive')->count();

      return compact('rawMaterials', 'flavors', 'totalFlavors', 'activeFlavors', 'inactiveFlavors');
    }

  /**
   * Obtiene los datos de los variaciones para DataTables.
   *
   * @return mixed
  */
  public function flavorsDatatable(): mixed
  {
    $flavors = Flavor::all();

    return DataTables::of($flavors)
        ->addColumn('action', function($flavor){
            return '<a href="#" class="btn btn-primary btn-sm">Editar</a>';
        })
        ->rawColumns(['action'])
        ->make(true);
  }

  /**
   * Almacena los variaciones
   *
   * @param  StoreFlavorRequest  $request
   * @return Flavor
  */
  public function storeFlavor(StoreFlavorRequest $request)
  {
      $flavor = Flavor::create($request->only('name', 'status'));

      if ($request->has('recipes')) {
          foreach ($request->recipes as $recipeData) {
              $flavor->recipes()->create([
                  'raw_material_id' => $recipeData['raw_material_id'],
                  'quantity' => $recipeData['quantity']
              ]);
          }
      }

      return redirect()->route('product-flavors')->with('success', 'Sabor creado con éxito');
  }


  /**
   * Almacena múltiples variaciones
   *
   * @param  StoreMultipleFlavorsRequest  $request
   * @return void
  */
  public function storeMultipleFlavors(StoreMultipleFlavorsRequest $request): void
  {
    $data = json_decode($request->getContent(), true);
    $names = $data['name'];
    $status = $data['status'] ?? 'active';

    foreach ($names as $name) {
        $flavor = new Flavor();
        $flavor->name = trim($name);
        $flavor->status = $status;
        $flavor->save();
    }
  }

  /**
   * Muestra el formulario para editar un sabor.
   *
   * @param  int  $id
   * @return JsonResponse
  */
  public function editFlavor(int $id)
  {
    $flavor = Flavor::with('recipes.rawMaterial')->findOrFail($id);

    $recipes = $flavor->recipes->map(function($recipe) {
      return [
        'id' => $recipe->id,
        'raw_material_id' => $recipe->raw_material_id,
        'quantity' => $recipe->quantity,
        'unit_of_measure' => $recipe->rawMaterial->unit_of_measure
      ];
    });

    return [
      'name' => $flavor->name,
      'recipes' => $recipes
    ];
  }

  /**
   * Actualiza un sabor específico en la base de datos.
   *
   * @param  UpdateFlavorRequest  $request
   * @param  int  $id
   * @return array
  */
  public function updateFlavor(UpdateFlavorRequest $request, int $id): Flavor
  {
    $flavor = Flavor::findOrFail($id);
    $flavor->update($request->only('name', 'status'));

    $flavor->recipes()->delete();

    if ($request->has('recipes')) {
      foreach ($request->recipes as $recipeData) {
        $flavor->recipes()->create([
          'raw_material_id' => $recipeData['raw_material_id'],
          'quantity' => $recipeData['quantity']
        ]);
      }
    }

    return $flavor;
  }

  /**
   * Cambia el estado de un sabor.
   *
   * @param  int  $id
   * @return Flavor
  */
  public function switchFlavorStatus(int $id): Flavor
  {
    $flavor = Flavor::findOrFail($id);
    $flavor->status = $flavor->status === 'active' ? 'inactive' : 'active';
    $flavor->save();

    return $flavor;
  }

  /**
   * Elimina un sabor de la base de datos.
   *
   * @param  int  $id
   * @return bool
  */
  public function destroyFlavor(int $id): bool
  {
    $flavor = Flavor::findOrFail($id);
    return $flavor->delete();
  }

  public function getProductsForExport(array $filters)
  {
      // Iniciar la consulta
      $query = Product::with(['categories:id,name', 'store:id,name'])
          ->select([
              'id', 'name', 'sku', 'description', 'type', 'old_price', 'price',
              'discount', 'image', 'store_id', 'status', 'draft', 'stock', 'safety_margin'
          ])
          ->where('is_trash', '!=', 1);

      // Aplicar filtros
      if (!empty($filters['search'])) {
          $query->where('name', 'like', '%' . $filters['search'] . '%');
      }

      if (!empty($filters['store_id'])) {
          $query->where('store_id', $filters['store_id']);
      }

      if (!empty($filters['category_id'])) {
          $query->whereHas('categories', function ($q) use ($filters) {
              $q->where('product_categories.id', $filters['category_id']);
          });
      }

      if (!empty($filters['status'])) {
          $query->where('status', $filters['status']);
      }

      return $query->get();
  }

  /**
   * Muestra los productos y categorías para la edición masiva.
   *
   * @return array
   */
  public function getProductsForBulkEdit(): array
  {
      $products = Product::with('categories')->get();
      // Verificar si el usuario tiene permiso para ver todas las categorías
      if (Auth::user()->can('access_global_products')) {
        $categories = ProductCategory::all();
      } else {
          // Si no tiene el permiso, mostrar solo las categorías asociadas a su tienda
          $categories = ProductCategory::where('store_id', Auth::user()->store_id)->get();
      }

      return compact('products', 'categories');
  }

  /**
   * Actualiza los productos en masa.
   *
   * @param array $productsData
   * @return void
   */
  public function updateBulk(array $productsData): void
  {
      foreach ($productsData as $productData) {
          $product = Product::find($productData['id']);
          if ($product) {
              // Excluir 'categories' del array de actualización
              $updateData = array_diff_key($productData, array_flip(['categories']));
              $product->update($updateData);

              // Sincronizar categorías
              if (isset($productData['categories'])) {
                  $product->categories()->sync($productData['categories']);
              }
          }
      }
  }

  /**
   * Muestra las tiendas para agregar productos en masa.
   *
   * @return Collection
   */
  public function getStoresForBulkAdd(): Collection
  {
      if (Auth::user()->can('view_all_stores')) {
          return Store::all();
      } else {
          return Store::where('id', Auth::user()->store_id)->get();
      }
  }

  /**
   * Almacena los productos en masa.
   *
   * @param array $productsData
   * @return void
   */
  public function storeBulk(array $productsData): void
  {
      foreach ($productsData as $productData) {
          if (!empty($productData['name'])) {
              // Excluir 'categories' del array de creación
              $productDataWithoutCategories = array_diff_key($productData, array_flip(['categories']));
              $productDataWithoutCategories['image'] = '/assets/img/ecommerce-images/placeholder.png';

              $product = new Product($productDataWithoutCategories);
              $product->save();

              // Sincronizar categorías si están presentes
              if (isset($productData['categories'])) {
                  $product->categories()->sync($productData['categories']);
              }
          }
      }
  }

}
