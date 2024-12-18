<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Repositories\VehicleRepository;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
class VehicleController extends Controller
{
    protected $vehicleRepository;

    public function __construct(VehicleRepository $vehicleRepository)
    {
        $this->vehicleRepository = $vehicleRepository;
    }

    public function index()
    {
        $vehicles = $this->vehicleRepository->getAll();
        return view('vehicles.index', compact('vehicles'));
    }

    public function getAll()
    {
        $vehicles = $this->vehicleRepository->getAll();
        return response()->json(['vehicles' => $vehicles]);
    }

    public function create()
    {
        return view('vehicles.create');
    }

    public function store(StoreVehicleRequest $request)
    {
        $data = $request->validated();
        $vehicle = $this->vehicleRepository->create($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Vehículo creado con éxito.',
                'vehicle' => $vehicle,
            ]);
        }

        return redirect()->route('vehicles.index')->with('success', 'Vehículo creado con éxito.');
    }

    public function show($id)
    {
        $vehicle = $this->vehicleRepository->find($id);
        return view('vehicles.show', compact('vehicle'));
    }

    public function edit($id)
    {
        $vehicle = $this->vehicleRepository->find($id);
        return view('vehicles.edit', compact('vehicle'));
    }

    public function update(UpdateVehicleRequest $request, $id)
    {
        $updated = $this->vehicleRepository->update($id, $request->validated());
        if ($updated) {
            return response()->json(['success' => 'Vehículo actualizado correctamente.'], 200);
        } else {
            return response()->json(['error' => 'Error al intentar actualizar el vehículo.'], 500);
        }
    }

    public function destroy($id)
    {
        $deleted = $this->vehicleRepository->delete($id);
        if ($deleted) {
            return response()->json(['success' => 'Vehículo eliminado correctamente.'], 200);
        } else {
            return response()->json(['error' => 'Error al intentar eliminar el vehículo.'], 500);
        }
    }
}
