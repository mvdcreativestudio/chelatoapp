<?php

namespace App\Http\Controllers;

use App\Models\PosProvider;
use Illuminate\Http\Request;

class PosProviderController extends Controller
{
    public function store(Request $request)
    {
        // Validación básica de los datos
        $request->validate([
            'name' => 'required|string|max:255',
            'api_url' => 'required|url',
        ]);

        // Crear el proveedor con la URL incluida
        $provider = PosProvider::create($request->all());

        return redirect()->back()->with('success', 'Proveedor POS creado exitosamente');
    }
}
