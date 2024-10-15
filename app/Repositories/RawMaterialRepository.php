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
     * @return array
     */
    public function getAll(): array
    {
        if (auth()->user()->can('view_all_raw-materials')) {
            // Obtiene todas las materias primas con sus relaciones de tiendas
            $rawMaterials = RawMaterial::with('stores')->get();
        } else {
            // Obtiene las materias primas asociadas a la tienda del usuario autenticado
            $storeId = auth()->user()->store_id;
            $rawMaterials = RawMaterial::whereHas('stores', function ($query) use ($storeId) {
                $query->where('store_id', $storeId);
            })->with(['stores' => function ($query) use ($storeId) {
                $query->where('store_id', $storeId);
            }])->get();
        }

        // Calcular el stock total para cada materia prima
        $rawMaterials->each(function ($material) {
            $totalStock = $material->stores->sum('pivot.stock');
            $material->total_stock = $totalStock; // Añadimos el total_stock al objeto de materia prima

            // Opcional: Agregar stock por tienda
            $material->stores->each(function ($store) use ($material) {
                $store->store_stock = $store->pivot->stock;
            });
        });

        return $rawMaterials->toArray();
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
        if (isset($data['image'])) {
            $data['image_url'] = $this->uploadImage($data['image']);
            unset($data['image']);
        }

        // Crear la materia prima
        $rawMaterial = RawMaterial::create($data);

        // Asociar la materia prima con la tienda y establecer el stock inicial
        $storeId = auth()->user()->store_id ?? throw new ModelNotFoundException('No se puede crear una materia prima sin una tienda asignada.');
        $initialStock = $data['initial_stock'] ?? 0;
        $rawMaterial->stores()->attach($storeId, ['stock' => $initialStock]);

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
        if (isset($data['image'])) {
            if ($rawMaterial->image_url) {
                Storage::delete('public/' . $rawMaterial->image_url);
            }
            $data['image_url'] = $this->uploadImage($data['image']);
            unset($data['image']);
        }

        // Actualizar la materia prima
        $rawMaterial->update($data);

        // Si se proporciona un stock nuevo, actualiza el stock en la tienda
        if (isset($data['stock'])) {
            $storeId = auth()->user()->store_id;
            $rawMaterial->stores()->updateExistingPivot($storeId, ['stock' => $data['stock']]);
        }

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

    /**
     * Sube una imagen al servidor y devuelve el nombre del archivo.
     *
     * @param  \Illuminate\Http\UploadedFile $image
     * @return string
     */
    protected function uploadImage(HttpUploadedFile $image): string
    {
        $path = $image->store('public/assets/img/raw_materials');
        return basename($path);
    }
}
