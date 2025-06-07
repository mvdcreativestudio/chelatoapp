<?php

namespace App\Repositories;

use App\Models\Driver;

class DriverRepository
{
    public function getAll()
    {
        return Driver::all();
    }

    public function find($id)
    {
        return Driver::findOrFail($id);
    }

    public function create(array $data)
    {
        return Driver::create($data);
    }

    public function update($id, array $data)
    {
        $driver = $this->find($id);
        $driver->update($data);
        return $driver;
    }

    public function delete($id)
    {
        $driver = $this->find($id);
        return $driver->delete();
    }
}
