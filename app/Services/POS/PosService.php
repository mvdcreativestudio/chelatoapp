<?php

namespace App\Services\POS;

use App\Models\Store;
use App\Services\POS\ScanntechIntegrationService;
use App\Services\POS\FiservIntegrationService;
use Illuminate\Support\Facades\Log;
use App\Models\PosIntegrationStoreInfo;
use App\Models\PosProvider;


class PosService
{
    protected $posIntegration;

    public function __construct()
    {
        // No es necesario pasar una implementación de PosIntegrationInterface por el constructor,
        // ya que se determinará dinámicamente en cada función en base al store_id.
    }

    public function getProviderByStoreId($storeId)
    {
        // Buscar la integración de la tienda en la tabla pos_integrations_store_info
        $integrationInfo = PosIntegrationStoreInfo::where('store_id', $storeId)->first();

        if ($integrationInfo && $integrationInfo->pos_provider_id) {
            // Obtener el proveedor POS correspondiente
            $posProvider = PosProvider::find($integrationInfo->pos_provider_id);
            return $posProvider;
        }

        return null;
    }

    // Procesar la transacción seleccionando el proveedor POS dinámicamente
    public function processTransaction(array $transactionData): array
    {
        $this->setPosIntegration($transactionData['store_id']);
        return $this->posIntegration->processTransaction($transactionData);
    }

    // Consultar el estado de la transacción seleccionando el proveedor POS dinámicamente
    public function checkTransactionStatus(array $transactionData)
    {
        $this->setPosIntegration($transactionData['store_id']);
        return $this->posIntegration->checkTransactionStatus($transactionData);
    }

    // Obtener respuestas del proveedor POS
    public function getResponses($responseCode)
    {
        return $this->posIntegration->getResponses($responseCode);
    }

    // Obtener token del proveedor POS (si aplica)
    public function getPosToken($storeId)
    {
        $this->setPosIntegration($storeId);
        return $this->posIntegration->getToken();
    }

    // Método para determinar el proveedor POS basado en el store_id
    protected function setPosIntegration($storeId)
    {
        // Busca la relación en la tabla pivot 'pos_integrations_store_info'
        $integrationInfo = \App\Models\PosIntegrationStoreInfo::where('store_id', $storeId)->first();
    
        if (!$integrationInfo) {
            throw new \Exception('No se encontró la integración POS para esta tienda.');
        }
    
        Log::info('Estableciendo integración POS para store ID: ' . $storeId . ', Proveedor POS ID: ' . $integrationInfo->pos_provider_id);
    
        // Determinar el proveedor de POS con base en el 'pos_provider_id' desde la tabla pivot
        switch ($integrationInfo->pos_provider_id) {
            case 1: // Scanntech
                Log::info('Integración seleccionada: Scanntech');
                $this->posIntegration = new ScanntechIntegrationService(new ScanntechAuthService());
                break;
    
            case 2: // Fiserv
                Log::info('Integración seleccionada: Fiserv');
                $this->posIntegration = new FiservIntegrationService(); // Asegúrate de crear este servicio
                break;
    
            default:
                throw new \Exception('Proveedor POS no soportado para esta tienda.');
        }
    }
   
}
