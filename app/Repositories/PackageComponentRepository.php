<?php

namespace App\Repositories;

use App\Models\PackageComponent;


class PackageComponentRepository
{
    public function getAll()
    {
        return PackageComponent::all();
    }

    public function find($id)
    {
        return PackageComponent::findOrFail($id);
    }

    public function create(array $data)
    {
        return PackageComponent::create($data);
    }

    public function update($id, array $data)
    {
        $packageComponent = $this->find($id);
        $packageComponent->update($data);
        return $packageComponent;
    }

    public function delete($id)
    {
        $packageComponent = PackageComponent::find($id);
        return $packageComponent->delete();
    }

    public function updatePackageComponentStock($id, $stockToAdd)
    {
        $component = PackageComponent::findOrFail($id);
        $component->stock += $stockToAdd;
        $component->save();

        return $component;
    }
}
