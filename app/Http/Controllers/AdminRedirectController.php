<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

/**
 * Redirige /admin: al login si no está autenticado, al dashboard si lo está.
 * Es un controlador (no closure) para permitir route:cache.
 */
class AdminRedirectController extends Controller
{
    public function __invoke()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return redirect()->route('dashboard');
    }
}
