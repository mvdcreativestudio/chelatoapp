<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasStore
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->user()->store_id) {
            session()->flash('error', 'No se puede acceder a este módulo sin una tienda asignada.');
            return redirect()->back();
        }
        return $next($request);
    }
}
