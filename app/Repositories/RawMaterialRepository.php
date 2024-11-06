<?php

namespace App\Repositories;

use App\Models\RawMaterial;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile as HttpUploadedFile;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RawMaterialRepository
{
    /**
     * Devuelve todas las Materias Primas.
     *
     */
    public function getAll()
    {
        if (auth()->user() && auth()->user()->can('view_all_raw-materials')) {
            return $rawMaterials = RawMaterial::all();
        } 
        return collect([]);  // Retorna una colección vacía si no hay permiso
        
    }

    /**
     * Busca Materia Primas por el store_id
     *
     * @param int $store_id
     * @return Collection
    */
    public function findByStoreId($store_id): Collection
    {
        return RawMaterial::whereHas('stores', function ($query) use ($store_id) {
            $query->where('store_id', $store_id);
        })->with(['stores' => function ($query) use ($store_id) {
            $query->where('store_id', $store_id);
        }])->get();
    }


    /**
     * Busca una Materia Prima por el ID.
     *
     * @param  int $id
     * @return RawMaterial|null
     */
    public function findById($id): ?RawMaterial
    {
        return RawMaterial::find($id);
    }

    /**
     * Guarda una nueva Materia Prima.
     *
     * @param  array $data
     * @return RawMaterial
     */
    public function create(array $data): RawMaterial
    {
        // Crear la materia prima
        $rawMaterial = RawMaterial::create($data);

        // Asociar la materia prima con la tienda y establecer el stock inicial
        $storeId = auth()->user()->store_id ?? throw new ModelNotFoundException('No se puede crear una materia prima sin una tienda asignada.');
        $initialStock = $data['initial_stock'] ?? 0;

        return $rawMaterial;
    }


    /**
     * Actualiza una Materia Prima existente.
     *
     * @param  RawMaterial $rawMaterial
     * @param  array $data
     * @return RawMaterial
     */
    public function update(RawMaterial $rawMaterial, array $data): RawMaterial
    {
        // Actualizar la materia prima
        $rawMaterial->update($data);

        return $rawMaterial;
    }


    /**
     * Elimina una Materia Prima.
     *
     * @param  RawMaterial $rawMaterial
     * @return bool|null
     */
    public function delete(RawMaterial $rawMaterial): ?bool
    {
        return $rawMaterial->delete();
    }
}