<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class EnsureStoreSelected
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
      if (!Session::has('store')) {
          return redirect('/shop')->with('error', 'Por favor, selecciona una tienda.');
      }

      return $next($request);
    }
}
