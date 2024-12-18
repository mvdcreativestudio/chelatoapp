<?php

namespace App\Repositories;

use App\Models\Vehicle;

class VehicleRepository
{
    public function getAll()
    {
        return Vehicle::all();
    }

    public function find($id)
    {
        return Vehicle::findOrFail($id);
    }

    public function create(array $data)
    {
        return Vehicle::create($data);
    }

    public function update($id, array $data)
    {
        $vehicle = $this->find($id);
        $vehicle->update($data);
        return $vehicle;
    }

    public function delete($id)
    {
        $vehicle = $this->find($id);
        return $vehicle->delete();
    }
}
