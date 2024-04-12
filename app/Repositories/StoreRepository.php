<?php

namespace App\Repositories;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class StoreRepository
{
    /**
     * Devuelve todas las tiendas.
     *
     * @return Collection|Store[]
     */
    public function getAll(): Collection
    {
      return Store::withCount('users')->with('phoneNumber')->get();
    }

    /**
     * Crea una nueva tienda con los datos proporcionados.
     *
     * @param array $data
     * @return Store
     */
    public function create(array $data): Store
    {
        return Store::create($data);
    }

    /**
     * Actualiza una tienda existente con los datos proporcionados.
     *
     * @param Store $store
     * @param array $data
     * @return Store
     */
    public function update(Store $store, array $data): Store
    {
        $store->update($data);
        return $store;
    }

    /**
     * Elimina una tienda de la base de datos.
     *
     * @param Store $store
     * @return bool|null
     */
    public function delete(Store $store): ?bool
    {
        return $store->delete();
    }

    /**
     * Devuelve usuarios que no están asociados a ninguna tienda.
     *
     * @return Collection|User[]
     */
    public function getUnassociatedUsers(): Collection
    {
        return User::whereNull('store_id')->get();
    }


    /**
     * Asocia un usuario a una tienda.
     *
     * @param Store $store
     * @param int $userId
     * @return Store
     */
    public function associateUser(Store $store, int $userId): Store
    {
        $user = User::findOrFail($userId);
        $user->store_id = $store->id;
        $user->save();

        return $store;
    }

    /**
     * Desasocia un usuario de una tienda.
     *
     * @param Store $store
     * @param int $userId
     * @return Store
     */
    public function disassociateUser(Store $store, int $userId): Store
    {
        $user = User::findOrFail($userId);
        $user->store_id = null;
        $user->save();

        return $store;
    }
}
