<?php

namespace App\Repositories;

use App\Models\Package;

class PackageRepository
{
    public function getAll()
    {
        return Package::all();
    }

    public function find($id)
    {
        return Package::findOrFail($id);
    }

    public function create(array $data)
    {
        return Package::create($data);
    }

    public function update($id, array $data)
    {
        $package = $this->find($id);
        $package->update($data);
        return $package;
    }

    public function delete($id)
    {
        $package = Package::find($id);
        return $package ? $package->delete() : false;
    }

    public function updatePackageStock($id, $stockToAdd)
    {
        $component = Package::findOrFail($id);
        $component->stock += $stockToAdd;
        $component->save();

        return $component;
    }
}
