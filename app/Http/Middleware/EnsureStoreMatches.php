<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class EnsureStoreMatches
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
      $slug = $request->route('slug');
      $sessionStoreSlug = Session::get('store.slug');

      if ($sessionStoreSlug && $sessionStoreSlug != $slug) {
          return redirect()->route('store', ['slug' => $sessionStoreSlug])
              ->with('error', 'No puedes acceder a una tienda diferente a la seleccionada.');
      }

      return $next($request);
    }
}
