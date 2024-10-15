<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     */
    public function handle(Request $request, Closure $next, $permission): Response
    {
        if ( !auth()->user() || !auth()->user()->can($permission) ) {
            // Verifica si la solicitud es AJAX
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'No tienes permisos para realizar esta acción.'], 403);
            }
            return redirect()->route('dashboard')->with('error', 'No tienes permisos para realizar esta acción.');
        }

        return $next($request);
    }
}
