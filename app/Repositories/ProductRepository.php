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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;



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
    $product = Product::with('taxRate', 'categories', 'store', 'flavors', 'recipes.rawMaterial', 'recipes.usedFlavor')
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
    \Log::info('Creando un nuevo producto:', [
      'request_data' => $request->all(),
    ]);

    try {
      // Crear el producto
      $product = new Product();
      $product->fill($request->only([
        'name',
        'sku',
        'description',
        'type',
        'max_flavors',
        'old_price',
        'price',
        'tax_rate_id',
        'discount',
        'store_id',
        'status',
        'show_in_catalogue',
        'stock',
        'safety_margin',
        'bar_code',
        'build_price',
      ]));

      // Manejo de la imagen principal
      if ($request->hasFile('image')) {
        $file = $request->file('image');
        $filename = time() . '.' . $file->getClientOriginalExtension();
        $path = $file->move(public_path('assets/img/ecommerce-images'), $filename);
        $product->image = 'assets/img/ecommerce-images/' . $filename;
      } else {
        $product->image = 'assets/img/ecommerce-images/placeholder.png';
      }

      $product->save();

      // Manejo de relaciones
      $product->categories()->sync($request->input('categories', []));
      $product->flavors()->sync($request->input('flavors', []));

      // Crear características
      $this->updateProductFeatures($product, $request->input('features', []));

      // Crear tamaños
      $this->updateProductSizes($product, $request->input('sizes', []));

      // Crear colores
      $this->updateProductColors($product, $request->input('colors', []));

      // Manejar imágenes de la galería
      if ($request->hasFile('gallery_images')) {
        $this->updateGalleryImages($product, $request->file('gallery_images'));
      }

      \Log::info('Producto creado con éxito:', ['product_id' => $product->id]);
      return $product;
    } catch (\Exception $e) {
      \Log::error('Error al crear producto:', ['error' => $e->getMessage()]);
      throw $e;
    }
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
        'id',
        'name',
        'sku',
        'description',
        'type',
        'old_price',
        'price',
        'discount',
        'image',
        'store_id',
        'status',
        'draft',
        'stock',
        'safety_margin',
        'build_price'
      ])
      ->where('is_trash', '!=', 1);

    // Filtrar por rol del usuario
    if (!Auth::user()->hasRole('Administrador')) {
      $query->where('store_id', Auth::user()->store_id);
    }

    // Aplicar filtros si están presentes en la solicitud
    if ($request->has('search') && !empty($request->search)) {
      $query->where(function ($q) use ($request) {
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
    try {
      // Cargar el producto con todas las relaciones necesarias
      $product = Product::with('categories', 'flavors', 'recipes.rawMaterial', 'recipes.usedFlavor', 'features', 'sizes', 'colors', 'gallery')->findOrFail($id);

      \Log::info('Producto cargado exitosamente para edición:', ['product_id' => $id]);

      // Obtener todas las tiendas, sabores, y materias primas
      $stores = Store::all();
      $flavors = Flavor::all();
      $rawMaterials = RawMaterial::all();

      // Verificar permisos para obtener categorías específicas
      if (Auth::user()->can('access_global_products')) {
        $categories = ProductCategory::all();
      } else {
        $categories = ProductCategory::where('store_id', Auth::user()->store_id)->get();
      }

      return compact('product', 'stores', 'categories', 'flavors', 'rawMaterials');
    } catch (\Exception $e) {
      \Log::error('Error al cargar producto para edición:', ['product_id' => $id, 'error' => $e->getMessage()]);
      throw $e;
    }
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
    \Log::info('Inicio de actualización del producto:', [
      'product_id' => $id,
      'request_data' => $request->all(),
    ]);

    try {
      $product = Product::findOrFail($id);

      // Actualizar campos principales del producto
      $product->update($request->only([
        'name',
        'sku',
        'description',
        'type',
        'max_flavors',
        'old_price',
        'price',
        'tax_rate_id',
        'discount',
        'store_id',
        'status',
        'show_in_catalogue',
        'stock',
        'safety_margin',
        'bar_code',
        'build_price',
      ]));

      \Log::info('Producto actualizado con éxito:', ['product_id' => $id]);

      // Manejar la imagen principal
      if ($request->hasFile('image')) {
        $file = $request->file('image');
        $filename = time() . '.' . $file->getClientOriginalExtension();
        $path = $file->move(public_path('assets/img/ecommerce-images'), $filename);
        if ($path) {
          $product->image = 'assets/img/ecommerce-images/' . $filename;
          $product->save();
          \Log::info('Imagen del producto actualizada:', ['product_id' => $id, 'image_path' => $product->image]);
        }
      }

      // Manejar imágenes de la galería
      if ($request->hasFile('gallery_images')) {
        $this->updateGalleryImages($product, $request->file('gallery_images'));
      }

      // Actualizar categorías y sabores
      $product->categories()->sync($request->input('categories', []));
      \Log::info('Categorías sincronizadas:', ['product_id' => $id, 'categories' => $request->input('categories', [])]);

      if ($request->filled('flavors')) {
        $product->flavors()->sync($request->flavors);
        \Log::info('Sabores sincronizados:', ['product_id' => $id, 'flavors' => $request->flavors]);
      }

      // Actualizar características, tamaños y colores
      $this->updateProductFeatures($product, $request->input('features', []));
      $this->updateProductSizes($product, $request->input('sizes', []));
      $this->updateProductColors($product, $request->input('colors', []));

      \Log::info('Producto actualizado correctamente en el repositorio.', [
        'product_id' => $product->id,
      ]);


      return $product;
    } catch (\Exception $e) {
      \Log::error('Error durante la actualización del producto:', [
        'product_id' => $id,
        'error' => $e->getMessage(),
      ]);
      throw $e;
    }
  }



  /**
   * Actualiza las características de un producto específico.
   *
   * @param Product $product
   * @param array $features
   * @return void
   */
  private function updateProductFeatures(Product $product, array $features): void
  {
    \Log::info('Inicio de actualización de características:', [
      'product_id' => $product->id,
      'features_data' => $features,
    ]);

    // Eliminar las características existentes
    $product->features()->delete();
    \Log::info('Características antiguas eliminadas:', ['product_id' => $product->id]);

    foreach ($features as $feature) {
      // Validar que ambos valores existan antes de guardar
      if (!empty($feature['value'])) {
        try {
          $product->features()->create([
            'value' => $feature['value'],
          ]);
          \Log::info('Característica creada:', [
            'product_id' => $product->id,
            'value' => $feature['value'],
          ]);
        } catch (\Exception $e) {
          \Log::error('Error al crear característica:', [
            'product_id' => $product->id,
            'value' => $feature['value'],
            'error' => $e->getMessage(),
          ]);
        }
      } else {
        \Log::warning('Característica incompleta, no se guardará:', [
          'product_id' => $product->id,
          'value' => $feature['value'] ?? null,
        ]);
      }
    }
  }

  private function updateProductSizes(Product $product, array $sizes): void
  {
    \Log::info('Inicio de actualización de tamaños:', [
      'product_id' => $product->id,
      'sizes_data' => $sizes,
    ]);

    // Eliminar los tamaños existentes
    $product->sizes()->delete();
    \Log::info('Tamaños antiguos eliminados:', ['product_id' => $product->id]);

    foreach ($sizes as $size) {
      // Verificar que al menos uno de los campos sea no nulo
      if (!empty($size['size']) || !empty($size['width']) || !empty($size['height']) || !empty($size['length'])) {
        try {
          $product->sizes()->create([
            'size' => $size['size'] ?? null,
            'width' => $size['width'] ?? null,
            'height' => $size['height'] ?? null,
            'length' => $size['length'] ?? null,
          ]);
          \Log::info('Tamaño creado:', [
            'product_id' => $product->id,
            'size' => $size['size'] ?? null,
            'width' => $size['width'] ?? null,
            'height' => $size['height'] ?? null,
            'length' => $size['length'] ?? null,
          ]);
        } catch (\Exception $e) {
          \Log::error('Error al crear tamaño:', [
            'product_id' => $product->id,
            'size' => $size['size'] ?? null,
            'error' => $e->getMessage(),
          ]);
        }
      } else {
        \Log::warning('Tamaño incompleto, no se guardará:', [
          'product_id' => $product->id,
          'size' => $size['size'] ?? null,
        ]);
      }
    }
  }


  /**
   * Actualiza los colores de un producto específico.
   *
   * @param Product $product
   * @param array $colors
   * @return void
   */
  private function updateProductColors(Product $product, array $colors): void
  {
    \Log::info('Inicio de actualización de colores:', [
      'product_id' => $product->id,
      'colors_data' => $colors,
    ]);

    // Eliminar los colores existentes
    $product->colors()->delete();
    \Log::info('Colores antiguos eliminados:', ['product_id' => $product->id]);

    foreach ($colors as $color) {
      // Validar que al menos el nombre esté presente
      if (!empty($color['name'])) {
        try {
          $product->colors()->create([
            'color_name' => $color['name'],
            'hex_code' => $color['hex_code'],
          ]);
          \Log::info('Color creado:', [
            'product_id' => $product->id,
            'color_name' => $color['name'],
            'hex_code' => $color['hex_code'],
          ]);
        } catch (\Exception $e) {
          \Log::error('Error al crear color:', [
            'product_id' => $product->id,
            'color_name' => $color['name'],
            'hex_code' => $color['hex_code'],
            'error' => $e->getMessage(),
          ]);
        }
      } else {
        \Log::warning('Color incompleto, no se guardará:', [
          'product_id' => $product->id,
          'color_name' => $color['name'] ?? null,
          'hex_code' => $color['hex_code'] ?? null,
        ]);
      }
    }
  }

  /**
   * Agrega imágenes a la galería de un producto.
   *
   * @param int $productId
   * @param array $images
   * @return void
   */
  private function updateGalleryImages(Product $product, array $images): void
  {
    Log::info('Inicio de actualización de imágenes de la galería:', [
      'product_id' => $product->id,
      'images_count' => count($images),
    ]);
    foreach ($images as $image) {
      if ($image->isValid()) {
        $filename = time() . '_' . $image->getClientOriginalName();
        $path = $image->move(public_path('assets/img/ecommerce-images/product-gallery'), $filename);

        $product->gallery()->create(['image' => 'assets/img/ecommerce-images/product-gallery/' . $filename]);

        \Log::info('Imagen añadida a la galería:', ['product_id' => $product->id, 'image_path' => $path]);
      } else {
        \Log::warning('Archivo inválido detectado durante la carga de imágenes de la galería:', [
          'product_id' => $product->id,
          'filename' => $image->getClientOriginalName(),
        ]);
      }
    }
  }

  /**
   * Elimina una imagen de la galería de un producto.
   *
   * @param int $imageId
   * @return bool
   */
  public function deleteGalleryImage(int $imageId): bool
  {
    $image = \App\Models\ProductGallery::find($imageId);

    if (!$image) {
      return false; // Imagen no encontrada
    }

    try {
      // Eliminar el archivo físico si existe
      $filePath = public_path($image->image);
      if (file_exists($filePath)) {
        unlink($filePath);
      }

      // Eliminar el registro de la base de datos
      $image->delete();

      return true;
    } catch (\Exception $e) {
      \Log::error('Error al eliminar la imagen de la galería:', ['error' => $e->getMessage()]);
      return false;
    }
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
      ->addColumn('action', function ($flavor) {
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

    $recipes = $flavor->recipes->map(function ($recipe) {
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
        'id',
        'name',
        'sku',
        'description',
        'type',
        'build_price',
        'old_price',
        'price',
        'discount',
        'image',
        'store_id',
        'status',
        'draft',
        'stock',
        'safety_margin'
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

  /**
   * Devuelve todos los productos de la base de datos.
   *
   */
  public function getAll()
  {
    return Product::all();
  }


}
