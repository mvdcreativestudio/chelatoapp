<?php

namespace App\Repositories;

use App\Models\StoresEmailConfig;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class StoresEmailConfigRepository
{
    /**
     * Obtiene la configuración de correo de una tienda específica.
     *
     * @param int $storeId
     * @return StoresEmailConfig|null
     */
    public function getConfigByStoreId(int $storeId): ?StoresEmailConfig
    {
        return StoresEmailConfig::where('store_id', $storeId)->first();
    }

    /**
     * Almacena o actualiza la configuración de correo para una tienda.
     *
     * @param int $storeId
     * @param array $data
     * @return StoresEmailConfig
     */
    public function saveConfig(int $storeId, array $data): StoresEmailConfig
    {
        return StoresEmailConfig::updateOrCreate(['store_id' => $storeId], $data);
    }

    /**
     * Elimina la configuración de correo para una tienda específica.
     *
     * @param int $storeId
     * @return void
     * @throws ModelNotFoundException
     */
    public function deleteConfig(int $storeId): void
    {
        $config = $this->getConfigByStoreId($storeId);
        if ($config) {
            $config->delete();
        } else {
            throw new ModelNotFoundException("No se encontró la configuración de correo para la tienda ID {$storeId}.");
        }
    }
}
