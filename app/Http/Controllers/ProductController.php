<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Store;
use App\Models\ProductCategory;
use App\Models\Flavor;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Log;


class ProductController extends Controller
{

  public function index()
  {
      return view('content.e-commerce.backoffice.products.products');
  }

  public function create()
  {
    $categories = ProductCategory::all();
    $stores = Store::all();
    $flavors = Flavor::all();
    return view('content.e-commerce.backoffice.products.add-product', compact('stores', 'categories', 'flavors'));
  }

  public function store(Request $request)
  {
    $product = new Product();
    $product->name = $request->name;
    $product->sku = $request->sku;
    $product->description = $request->description;
    $product->type = $request->type;
    $product->max_flavors = $request->max_flavors;
    $product->old_price = $request->old_price;
    $product->price = $request->price;
    $product->discount = $request->discount;
    $product->store_id = $request->store_id;
    $product->status = $request->status;
    $product->stock = $request->stock;

    // Agregar logs para depuración
    Log::debug('Request data:', $request->all());

    if ($request->hasFile('image')) {
        $file = $request->file('image');
        Log::debug('File info:', ['name' => $file->getClientOriginalName(), 'size' => $file->getSize(), 'mime_type' => $file->getMimeType(), 'path' => $file->getRealPath()]);

        // Obtener el nombre original del archivo
        $filename = time() . '.' . $file->getClientOriginalExtension();

        // Mover el archivo a la nueva ubicación
        $path = $file->move(public_path('assets/img/ecommerce-images'), $filename);

        // Guardar la ruta en la base de datos
        $product->image = 'assets/img/ecommerce-images/' . $filename;
    } else {
        Log::debug('No image file found in the request');
    }

    // Verificar si se guardará como borrador
    if ($request->action === 'save_draft') {
        $product->draft = 1;
    } else {
        $product->draft = 0;
    }

    $product->save();

    // Sincroniza las categorías después de guardar el producto
    $product->categories()->sync($request->input('categories', []));

    //Manejo de sabores
    if ($request->filled('flavors')) {
    $product->flavors()->sync($request->flavors);
    }

    // Redireccionar al usuario a la lista de clientes con un mensaje de éxito
    return redirect()->route('products.index')->with('success', 'Producto creado correctamente.');
  }


  public function datatable()
  {
      $query = Product::with(['categories:id,name', 'store:id,name'])
                      ->select(['id', 'name', 'sku', 'description', 'type', 'old_price', 'price', 'discount', 'image', 'store_id', 'status', 'stock', 'draft']);

      return DataTables::of($query)
          ->addColumn('category', function ($product) {
              return $product->categories->implode('name', ', ');
          })
          ->addColumn('store_name', function ($product) {
              return $product->store->name; // Acceder al nombre de la tienda a través de la relación 'store'
          })
          ->make(true);
  }

  public function switchStatus()
  {
      $product = Product::findOrFail(request('id'));
      if ($product->status == '1') {
          $product->status = '2';
      } else {
          $product->status = '1';
      }
      $product->save();

      return response()->json(['success' => true, 'message' => 'Estado del producto actualizado correctamente.']);
  }


  public function attributes()
  {
    return view('content.e-commerce.backoffice.products.attributes');
  }

  public function duplicate($id)
  {
      // Obtener el producto original
      $originalProduct = Product::findOrFail($id);

      // Crear un nuevo producto con los mismos datos
      $newProduct = $originalProduct->replicate();
      $newProduct->name = $originalProduct->name . ' (Copia)';
      // Puedes modificar otros campos si es necesario

      // Guardar el nuevo producto
      $newProduct->save();

      // Redirigir a la vista de creación con los datos del producto duplicado
      return redirect()->route('products.create')->with('product', $newProduct);
  }






}
