<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Coupon;
use App\Models\Store;
use App\Models\Flavor;
use Log;

class CartController extends Controller
{
  public function index()
  {
      return view('content.e-commerce.front.cart');
  }


  public function selectStore(Request $request)
  {
      $storeId = $request->input('storeId'); // Obtiene el ID de la tienda desde el cuerpo del formulario
      $store = Store::find($storeId);
      if (!$store) {
          return redirect()->back()->with('error', 'La tienda no existe.');
      }
      session()->put('store', ['id' => $store->id, 'name' => $store->name, 'address' => $store->address]);
      return redirect()->route('store', ['storeId' => $store->id]);
    }


  public function addToCart(Request $request, $productId)
  {
    $product = Product::find($productId);

    // Verificar si el producto existe
    if (!$product) {
        return redirect()->back()->with('error', 'El producto no existe.');
    }

    $cart = session()->get('cart', []);

    // Recuperar los IDs de los sabores seleccionados desde el request
    $flavorIds = $request->input('flavors', []);

    // Buscar los nombres de los sabores basados en los IDs
    $flavors = Flavor::findMany($flavorIds)->pluck('name', 'id');

    // Preparar la estructura de sabores para incluir tanto IDs como nombres
    $flavorsForSession = $flavors->map(function($name, $id) {
        return ['id' => $id, 'name' => $name];
    })->values()->toArray();

    if(isset($cart[$productId])) {
        $cart[$productId]['quantity']++;
        // Actualizar los sabores solo si es necesario
        $cart[$productId]['flavors'] = $flavorsForSession;
    } else {
        $cart[$productId] = [
            "id" => $product->id,
            "name" => $product->name,
            "sku" => $product->sku,
            "description" => $product->description,
            "type" => $product->type,
            "quantity" => 1,
            "old_price" => $product->old_price,
            "price" => $product->price,
            "image" => $product->image,
            // Guardar la estructura de sabores con IDs y nombres
            "flavors" => $flavorsForSession
        ];
    }

    session()->put('cart', $cart);
    return redirect()->back()->with('success', 'Producto añadido al carrito con éxito!');
  }



  public function updateCart()
  {
      return view('content.e-commerce.front.cart');
  }


  public function removeFromCart()
  {
      return view('content.e-commerce.front.cart');
  }


  public function clearCart()
  {
      return view('content.e-commerce.front.cart');
  }


  public function clearSession()
  {
    session()->flush();

    return redirect('/')->with('success', 'Sesión limpiada correctamente.');
  }


}
