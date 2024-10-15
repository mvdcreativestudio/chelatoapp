<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCartNotEmpty
{
  /**
   * Maneja una solicitud entrante.
   *
   * @param \Illuminate\Http\Request $request
   * @param \Closure $next
   * @return mixed
   */
  public function handle(Request $request, Closure $next)
  {
      $cart = session('cart', []);

      $selectedStore = session('store', null);

      if (empty($selectedStore)) {
          return redirect()->route('shop')->with('error', 'Por favor, selecciona una tienda antes de proceder al checkout.');
      }

      // if (empty($cart)) {
      //     return redirect('/store/' . $selectedStore['slug'])->with('error', 'El carrito está vacío. Por favor, agrega productos antes de proceder al checkout.');
      // }

      return $next($request);
  }
}
