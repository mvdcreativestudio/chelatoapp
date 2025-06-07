<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDriverRequest;
use App\Http\Requests\UpdateDriverRequest;
use App\Repositories\DriverRepository;
use Illuminate\Support\Facades\Log;

class DriverController extends Controller
{
    protected $driverRepository;

    public function __construct(DriverRepository $driverRepository)
    {
        $this->driverRepository = $driverRepository;
    }

    public function index()
    {
        $drivers = $this->driverRepository->getAll();
        return view('drivers.index', compact('drivers'));
    }

    public function getAll()
    {
        $drivers = $this->driverRepository->getAll();
        return response()->json(['drivers' => $drivers]);
    }

    public function create()
    {
        return view('drivers.create');
    }

    public function store(StoreDriverRequest $request)
    {
        $driver = $this->driverRepository->create($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Conductor creado con éxito.',
                'driver' => $driver,
            ]);
        }

        return redirect()->route('drivers.index')->with('success', 'Conductor creado con éxito.');
    }

    public function show($id)
    {
        $driver = $this->driverRepository->find($id);
        return view('drivers.show', compact('driver'));
    }

    public function edit($id)
    {
        $driver = $this->driverRepository->find($id);
        return view('drivers.edit', compact('driver'));
    }

    public function update(UpdateDriverRequest $request, $id)
    {
        $updated =    $this->driverRepository->update($id, $request->validated());
        if ($updated) {
            return response()->json(['success' => 'Conductor actualizado correctamente.'], 200);
        } else {
            return response()->json(['error' => 'Error al intentar actualizar el conductor.'], 500);
        }
    }

    public function destroy($id)
    {
        $deleted = $this->driverRepository->delete($id);
        if ($deleted) {
            return response()->json(['success' => 'Conductor eliminado correctamente.'], 200);
        } else {
            return response()->json(['error' => 'Error al intentar eliminar el conductor.'], 500);
        }
    }
}
