<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Store;

class EnsureUserCanAccessStore
{
    /**
     * Maneja una solicitud entrante.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        $store = $request->route('store');

        // Si $store es un ID, buscar el objeto Store
        if (is_numeric($store)) {
            $store = Store::find($store);
        }

        // Verificar si se omitirá el chequeo para rutas relacionadas con POS Devices
        if (str_contains($request->path(), 'pos/devices')) {
            return $next($request);
        }

        // Registrar en el log para verificar valores
        Log::info('EnsureUserCanAccessStore middleware', [
            'user_id' => $user->id,
            'store_id' => $store->id ?? 'ID no disponible',
            'can_view_all_stores' => $user->can('view_all_stores'),
            'user_store_id' => $user->store_id,
        ]);

        // Verificar si el usuario puede acceder a la tienda
        if (!$user->can('view_all_stores') && $user->store_id != $store->id) {
            abort(403, 'No tienes permiso para acceder a esta Empresa.');
        }

        return $next($request);
    }
}
