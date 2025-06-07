<?php

namespace App\Http\Controllers;

use App\Models\PosDevice;
use App\Models\PosProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PosDeviceController extends Controller
{
    public function index()
    {
        $devices = PosDevice::with('provider')->get();
        return view('pos_devices.index', compact('devices'));
    }

    public function store(Request $request)
    {
        // Validar los datos enviados en el formulario
        $validatedData = $request->validate([
            'identifier' => 'required|string|max:255',
            'user' => 'required|string|max:255',
            'pos_provider_id' => 'required|integer',
            'cash_register' => 'required|string|max:255',
        ]);

        // Crear un nuevo dispositivo POS
        PosDevice::create([
            'identifier' => $validatedData['identifier'],
            'user' => $validatedData['user'],
            'pos_provider_id' => $validatedData['pos_provider_id'],
            'cash_register_id' => $validatedData['cash_register'],
        ]);

        // Redireccionar de vuelta con un mensaje de éxito
        return redirect()->back()->with('success', 'Dispositivo añadido correctamente');
    }

    public function update(Request $request, $id)
{
    // Buscar el dispositivo POS en la base de datos por su ID
    $device = PosDevice::find($id);

    if (!$device) {
        // Si el dispositivo no se encuentra, lanzar un error
        Log::error('Dispositivo POS no encontrado', ['id' => $id]);
        return redirect()->back()->with('error', 'Dispositivo no encontrado');
    }

    // Log para capturar el request recibido y el dispositivo a actualizar
    Log::info('Datos recibidos en el request para actualizar PosDevice', [
        'request_data' => $request->all(),
        'device_before_update' => $device
    ]);

    // Validar los datos enviados en el formulario
    $validatedData = $request->validate([
        'identifier' => 'required|string|max:255',
        'user' => 'required|string|max:255',
        'pos_provider_id' => 'required|integer',
        'cash_register' => 'required|string|max:255',
    ]);

    // Actualizar los datos del dispositivo POS
    $device->update($validatedData);

    // Log para capturar el dispositivo después de ser actualizado
    Log::info('Dispositivo POS actualizado correctamente', [
        'device_after_update' => $device
    ]);

    // Redireccionar de vuelta con un mensaje de éxito
    return redirect()->back()->with('success', 'Dispositivo actualizado correctamente');
}
    
}
