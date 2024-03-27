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
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAll(): Collection
    {
      if (auth()->user() && auth()->user()->can('view_all_raw-materials')) {
          return RawMaterial::with('store')->get();
      } else {
          $storeId = auth()->user()->store_id;
          return RawMaterial::where('store_id', $storeId)->get();
      }
    }

    /**
     * Busca Materia Primas por el store_id
     *
     * @param int $store_id
     * @return Collection
    */
    public function findByStoreId($store_id): Collection
    {
        return RawMaterial::where('store_id', $store_id)->get();
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

        $data['store_id'] = auth()->user()->store_id ?? throw new ModelNotFoundException('No se puede crear una materia prima sin una tienda asignada.');

        return RawMaterial::create($data);
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
