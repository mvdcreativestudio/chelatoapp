<?php

namespace App\Repositories;

use App\Models\MercadoPagoAccountStore;
use Illuminate\Support\Facades\DB;
use Exception;

class MercadoPagoAccountStoreRepository
{
    /**
     * Obtiene todas las sucursales de MercadoPago.
     *
     * @return mixed
     */
    public function getAllStores(): mixed
    {
        return MercadoPagoAccountStore::all();
    }

    /**
     * Obtiene una sucursal específica por su ID.
     *
     * @param int $storeId
     * @return MercadoPagoAccountStore
     */
    public function getStoreById(int $storeId): MercadoPagoAccountStore
    {
        return MercadoPagoAccountStore::findOrFail($storeId);
    }

    /**
     * Crea una nueva sucursal en la base de datos.
     *
     * @param array $data
     * @return MercadoPagoAccountStore
     */
    public function store(array $data): MercadoPagoAccountStore
    {
        DB::beginTransaction();

        try {
            $store = MercadoPagoAccountStore::create($data);
            DB::commit();
            return $store;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Actualiza una sucursal existente.
     *
     * @param MercadoPagoAccountStore $store
     * @param array $data
     * @return MercadoPagoAccountStore
     */
    public function update(MercadoPagoAccountStore $store, array $data): MercadoPagoAccountStore
    {
        DB::beginTransaction();

        try {
            $store->update($data);
            DB::commit();
            return $store;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Elimina una sucursal específica.
     *
     * @param int $storeId
     * @return void
     */
    public function destroy(int $storeId): void
    {
        $store = MercadoPagoAccountStore::findOrFail($storeId);
        $store->delete();
    }

    /**
     * Obtiene una sucursal por external_id.
     *
     * @param string $externalId
     * @return MercadoPagoAccountStore|null
     */
    public function getStoreByExternalId(int $externalId): ?MercadoPagoAccountStore
    {
        return MercadoPagoAccountStore::where('external_id', 'SUC' . $externalId)->first();
    }

    /**
     * Obtiene las sucursales asociadas a una cuenta de MercadoPago.
     *
     * @param int $mercadopagoAccountId
     * @return mixed
     */
    public function getStoresByAccountId(int $mercadopagoAccountId): mixed
    {
        return MercadoPagoAccountStore::where('mercado_pago_account_id', $mercadopagoAccountId)->first();
    }
}
