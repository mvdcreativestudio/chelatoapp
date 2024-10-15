<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Flavor;
use App\Models\Store;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\SelectStoreRequest;

class CartRepository
{

  /**
   * Seleccionar una tienda y guardar la información en la sesión.
   *
   * @param SelectStoreRequest $request
   * @return RedirectResponse
  */
  // public function selectStore(SelectStoreRequest $request): RedirectResponse
  // {
  //   $request->session()->forget(['store', 'cart', 'subtotal']);


  
    /**
     * Seleccionar una tienda y guardar la información en la sesión.
     *
     * @param SelectStoreRequest $request
     * @return RedirectResponse
     */
    public function selectStore(SelectStoreRequest $request): RedirectResponse
    {
        // Limpiar la sesión antes de almacenar nuevos datos de la tienda
        $request->session()->flush();

        // Obtener el slug de la solicitud
        $slug = $request->slug;

        // Buscar la tienda por el slug
        $store = Store::where('slug', $slug)->first();

        if (!$store) {
            return redirect()->back()->with('error', 'La tienda no existe.');
        }

        // Almacenar todos los detalles de la tienda en la sesión
        $request->session()->put('store', [
            'id' => $store->id,
            'name' => $store->name,
            'address' => $store->address,
            'slug' => $store->slug,
            // Puedes agregar cualquier otro dato necesario de la tienda aquí
        ]);

        // Redirigir a la ruta de la tienda seleccionada
        return redirect()->route('store', ['slug' => $store->slug]);
    }


  /**
   * Añadir producto al carrito.
   *
   * @param Request $request
   * @param int $productId
   * @return array
  */
  public function addProduct(Request $request, int $productId): array
  {
    $product = Product::find($productId);
    if (!$product) {
        return ['success' => false, 'message' => 'El producto no existe.'];
    }

    $cart = session('cart', []);
    $flavorIds = $request->input('flavors', []);
    $flavors = $this->getFlavors($flavorIds);

    $cartItemKey = $this->generateCartItemKey($productId, $flavorIds);
    $quantity = $request->input('quantity', 1); // Obtén la cantidad del request, por defecto 1

    $this->updateCartItem($cart, $cartItemKey, $product, $flavors, $quantity);
    session(['cart' => $cart]);

    $this->updateSubtotal();

    return ['success' => true, 'message' => 'Producto añadido al carrito con éxito!'];
  }

  /**
   * Obtener los variaciones del producto.
   *
   * @param array $flavorIds
   * @return array
  */
  private function getFlavors(array $flavorIds): array
  {
    $flavors = [];
    foreach ($flavorIds as $flavorId) {
        $flavor = Flavor::find($flavorId);
        if ($flavor) {
            if (!isset($flavors[$flavorId])) {
                $flavors[$flavorId] = ['name' => $flavor->name, 'quantity' => 1];
            } else {
                $flavors[$flavorId]['quantity'] += 1;
            }
        }
    }
    return $flavors;
  }

  /**
   * Generar una clave única para identificar el producto en el carrito.
   *
   * @param int $productId
   * @param array $flavorIds
   * @return string
  */
  private function generateCartItemKey(int $productId, array $flavorIds): string
  {
    if (empty($flavorIds)) {
        return (string) $productId;
    } else {
        return $productId . '-' . implode('-', $flavorIds);
    }
  }

  /**
  * Actualizar el artículo en el carrito.
  *
  * @param array &$cart
  * @param string $cartItemKey
  * @param Product $product
  * @param array $flavors
  * @param int $quantity
  */
  private function updateCartItem(array &$cart, string $cartItemKey, Product $product, array $flavors, int $quantity): void
  {
    if (isset($cart[$cartItemKey])) {
        $cart[$cartItemKey]['quantity'] += $quantity;
        foreach ($flavors as $id => $details) {
            if (isset($cart[$cartItemKey]['flavors'][$id])) {
                $cart[$cartItemKey]['flavors'][$id]['quantity'] += $details['quantity'];
            } else {
                $cart[$cartItemKey]['flavors'][$id] = $details;
            }
        }
    } else {
        $cart[$cartItemKey] = [
            "id" => $product->id,
            "name" => $product->name,
            "sku" => $product->sku,
            "description" => $product->description,
            "type" => $product->type,
            "quantity" => $quantity,
            "old_price" => $product->old_price ?? 0,
            "price" => $product->price ?? $product->old_price ?? 0,
            "image" => $product->image,
            "flavors" => $flavors
        ];
    }
  }

  /**
   * Actualizar la cantidad de un producto en el carrito.
   *
   * @param int $productId
   * @param int $quantity
   * @return array
  */
  public function updateProductQuantity(int $productId, int $quantity): array
  {
    $cart = session('cart', []);
    if (isset($cart[$productId])) {
        $cart[$productId]['quantity'] = $quantity;
        session(['cart' => $cart]);
        $this->updateSubtotal();

        return ['success' => true, 'message' => 'Carrito actualizado con éxito!'];
    }

    return ['success' => false, 'message' => 'Producto no encontrado en el carrito.'];
  }

  /**
   * Eliminar un artículo específico del carrito.
   *
   * @param string $cartItemKey
   * @return array
  */
  public function removeItem(string $cartItemKey): array
  {
    $cart = session('cart', []);

    if (isset($cart[$cartItemKey])) {
        unset($cart[$cartItemKey]);
        session(['cart' => $cart]);
        $this->updateSubtotal();

        return ['status' => 'success', 'message' => 'Artículo eliminado del carrito con éxito.'];
    }

    return ['status' => 'error', 'message' => 'Artículo no encontrado en el carrito.'];
  }

  /**
   * Vaciar el carrito.
   *
   * @return void
  */
  public function clearCart(): void
  {
    session()->forget('cart');
    session()->forget('subtotal');
  }

  /**
   * Limpiar toda la sesión.
   *
   * @return void
  */
  public function clearSession(): void
  {
    session()->flush();
  }

  /**
   * Actualizar el subtotal del carrito.
   *
   * @return void
  */
  private function updateSubtotal(): void
  {
    $cart = session('cart', []);
    $subtotal = 0;

    foreach ($cart as $item) {
        $itemSubtotal = $item['price'] * $item['quantity'];
        $subtotal += $itemSubtotal;

        // Depuración de los subtotales
        Log::debug('Updating cart subtotal: Item', [
            'price' => $item['price'],
            'quantity' => $item['quantity'],
            'itemSubtotal' => $itemSubtotal
        ]);
    }

    session(['subtotal' => $subtotal]);
    Log::debug('Total Subtotal Updated', ['subtotal' => $subtotal]);
  }
}
