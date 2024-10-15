<?php

namespace App\Repositories;

use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Yajra\DataTables\DataTables;
use App\Http\Requests\UpdateProductCategoryRequest;


class ProductCategoryRepository
{
  /**
   * Obtiene una lista de todas las categorías de productos.
   *
   * @return array
  */
  public function index(): Collection
  {
    if(auth()->user()->can('access_global_products')){
      return ProductCategory::all(); // Devuelve una colección
    }else{
      return ProductCategory::where('store_id', auth()->user()->store_id)->get(); // Devuelve una colección
    }
  }

  /**
   * Almacena una nueva categoría de producto en la base de datos.
   *
   * @param  Request  $request
   * @return ProductCategory
  */
  public function store(Request $request): ProductCategory
  {
    $category = new ProductCategory();
    $category->name = $request->name;

    // Generar y validar slug único
    $baseSlug = empty($request->slug) ? \Str::slug($request->name) : $request->slug;
    $slug = $baseSlug;
    $counter = 1;

    while (ProductCategory::where('slug', $slug)->exists()) {
        $slug = $baseSlug . '-' . $counter;
        $counter++;
    }

    $category->slug = $slug;
    $category->store_id = $request->store_id;
    $category->description = $request->description;
    $category->parent_id = $request->parent_id;
    $category->status = $request->status;

    if ($request->hasFile('image')) {
        $file = $request->file('image');
        $filename = time() . '.' . $file->getClientOriginalExtension();
        $path = $file->move(public_path('assets/img/ecommerce-images'), $filename);
        $category->image_url = 'assets/img/ecommerce-images/' . $filename;
    }

    $category->save();

    return $category;
  }

  /**
   * Actualiza una categoría de producto en la base de datos.
   *
   * @param  Request  $request
   * @param  ProductCategory  $category
   * @return ProductCategory
  */
  public function update(Request $request, ProductCategory $category): ProductCategory
  {
    $category->name = $request->name;
    $category->slug = $request->slug;
    $category->description = $request->description;
    $category->parent_id = $request->parent_id;
    $category->status = $request->status;

    if ($request->hasFile('image')) {
        $file = $request->file('image');
        $filename = time() . '.' . $file->getClientOriginalExtension();
        $path = $file->move(public_path('assets/img/ecommerce-images'), $filename);
        $category->image_url = 'assets/img/ecommerce-images/' . $filename;
    }

    $category->save();

    return $category;
  }


  /**
   * Actualiza una categoría de producto en la base de datos dado un id.
   *
   * @param  UpdateProductCategoryRequest  $request
   * @param  int $id
   * @return ProductCategory
  */
  public function updateSelected(UpdateProductCategoryRequest $request,int $id): ProductCategory
  {
    $category = ProductCategory::find($id);

    $category->name = $request->name;
    $category->slug = $request->slug;
    $category->description = $request->description;
    $category->parent_id = $request->parent_id;
    $category->status = $request->status;

    if ($request->hasFile('image')) {
        $file = $request->file('image');
        $filename = time() . '.' . $file->getClientOriginalExtension();
        $path = $file->move(public_path('assets/img/ecommerce-images'), $filename);
        $category->image_url = 'assets/img/ecommerce-images/' . $filename;
    }

    $category->save();
    return $category;
  }

  /**
   * Elimina una categoría de producto de la base de datos.
   *
   * @param  ProductCategory  $category
   * @return void
  */
  public function destroy(ProductCategory $category): void
  {
    $category->delete();
  }

  /**
   * Elimina una categoría dado un ID.
   *
   * @param  int  $id
   * @return void
  */
  public function deleteSelected(int $id): bool
  {
    $category = ProductCategory::find($id);

    if ($category) {
        $category->delete();
        return true;
    } else {
        throw new \Exception("La categoría con el ID $id no existe.");
        return false;
    }
  }


  /**
     * Encuentra una categoría dada un ID.
     *
     * @param int $id
     * @return ProductCategory
    */
    public function getSelected($id): ProductCategory
    {
        $category = ProductCategory::find($id);

        if ($category) {
          return $category;
        } else {
          throw new \Exception("La categoría con el ID $id no existe.");
        }
    }



  /**
   * Obtiene los datos de las categorías de productos para DataTables.
   *
   * @return mixed
  */
  public function datatable(Request $request): mixed
  {
      // Agrega el conteo de productos y la suma del stock de productos
      $query = ProductCategory::withCount('products') // products_count será agregado automáticamente
          ->withSum('products', 'stock'); // Esto agrega la suma del stock de los productos relacionados

      // Aplica permisos y búsqueda como antes...
      if (!auth()->user()->can('access_global_products')) {
          $query->where('store_id', auth()->user()->store_id);
      }

      if ($request->has('search') && !empty($request->input('search'))) {
          $query->where('name', 'like', '%' . $request->input('search') . '%');
      }

      return DataTables::of($query)
          ->addColumn('product_count', function($category) {
              return $category->products_count; // Ya está bien, muestra el conteo de productos
          })
          ->addColumn('products_sum_stock', function($category) {
              return $category->products_sum_stock ?? 0; // Aquí mostramos la suma del stock, o 0 si es null
          })
          ->make(true);
  }


  /**
   * Obtiene todas las categorías junto con el conteo de productos y el conteo total de stock.
   * También devuelve estadísticas adicionales:
   * - Total de categorías
   * - Categoría con más productos
   * - Categoría con más stock
   *
   * @return array
   */
  public function getCategories(): array
  {
      // Verificar si el usuario tiene el permiso de acceso global a los productos
      if (auth()->user()->can('access_global_products')) {
          // Si el usuario tiene permiso, obtener todas las categorías
          $categories = ProductCategory::withCount('products') // Obtener conteo de productos
              ->withSum('products', 'stock') // Obtener la suma del stock
              ->get();
      } else {
          // Si el usuario no tiene permiso, obtener solo las categorías de su tienda
          $categories = ProductCategory::where('store_id', auth()->user()->store_id)
              ->withCount('products') // Obtener conteo de productos
              ->withSum('products', 'stock') // Obtener la suma del stock
              ->get();
      }

      // Mapear las categorías a un formato más manejable
      $mappedCategories = $categories->map(function ($category) {
          return [
              'id' => $category->id,
              'name' => $category->name,
              'slug' => $category->slug,
              'product_count' => $category->products_count, // Conteo de productos
              'stock_count' => $category->products_sum_stock ?? 0, // Total del stock
              'status' => $category->status,
          ];
      });

      // Calcular el total de categorías
      $totalCategories = $categories->count();

      // Encontrar la categoría con más productos
      $categoryWithMostProducts = $categories->sortByDesc('products_count')->first();

      // Encontrar la categoría con más stock
      $categoryWithMostStock = $categories->sortByDesc('products_sum_stock')->first();

      // Devolver la respuesta con las categorías y estadísticas adicionales, incluyendo el total de productos y stock
      return [
          'categories' => $mappedCategories,
          'total_categories' => $totalCategories,
          'category_with_most_products' => [
              'id' => $categoryWithMostProducts->id ?? null,
              'name' => $categoryWithMostProducts->name ?? 'No disponible',
              'product_count' => $categoryWithMostProducts->products_count ?? 0, // Total de productos
          ],
          'category_with_most_stock' => [
              'id' => $categoryWithMostStock->id ?? null,
              'name' => $categoryWithMostStock->name ?? 'No disponible',
              'stock_count' => $categoryWithMostStock->products_sum_stock ?? 0, // Total de stock
          ]
      ];
  }



}
