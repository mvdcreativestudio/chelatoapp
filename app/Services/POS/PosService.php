<?php

namespace App\Services\POS;

use App\Models\Store;
use App\Services\POS\ScanntechIntegrationService;
use App\Services\POS\FiservIntegrationService;
use Illuminate\Support\Facades\Log;
use App\Models\PosIntegrationStoreInfo;
use App\Models\PosProvider;
use App\Models\PosDevice;


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
        Log::info('Datos recibidos en processTransaction:', $transactionData);

        $this->setPosIntegration($transactionData['store_id']);

        // Verifica que 'cash_register_id' exista antes de continuar
        if (!isset($transactionData['cash_register_id'])) {
            throw new \Exception('La clave "cash_register_id" no está definida en los datos de la transacción.');
        }

        $deviceInfo = $this->getDeviceInfo($transactionData['cash_register_id']);

        $transactionData = array_merge($transactionData, $deviceInfo);

        $formattedData = $this->posIntegration->formatTransactionData($transactionData);

        return $this->posIntegration->processTransaction($formattedData);
    }


    protected function getDeviceInfo($cashRegisterId): array
    {
        Log::info('Iniciando getDeviceInfo con cash_register_id: ' . $cashRegisterId);

        // Buscar la relación en la tabla intermedia cash_register_pos_device
        $relation = \DB::table('cash_register_pos_device')
            ->where('cash_register_id', $cashRegisterId)
            ->first();

        if (!$relation) {
            Log::error('No se encontró relación entre la caja registradora y el dispositivo POS', [
                'cash_register_id' => $cashRegisterId
            ]);
            throw new \Exception('No se encontró relación entre la caja registradora y el dispositivo POS para cash_register_id: ' . $cashRegisterId);
        }
        Log::info('Relación encontrada en cash_register_pos_device:', (array)$relation);

        // Obtener el dispositivo POS usando el pos_device_id de la relación
        $device = PosDevice::find($relation->pos_device_id);

        if (!$device) {
            Log::error('Dispositivo POS no encontrado', [
                'pos_device_id' => $relation->pos_device_id
            ]);
            throw new \Exception('Dispositivo POS no encontrado para el pos_device_id: ' . $relation->pos_device_id);
        }
        Log::info('Dispositivo POS encontrado:', $device->toArray());

        // Obtener el proveedor POS y la integración asociada al store_id
        $integrationInfo = PosIntegrationStoreInfo::where('pos_provider_id', $device->pos_provider_id)
            ->first();

        if (!$integrationInfo) {
            Log::warning('No se encontró información de integración para el proveedor POS', [
                'pos_provider_id' => $device->pos_provider_id
            ]);
            throw new \Exception('No se encontró información de integración para el proveedor POS asociado.');
        }
        Log::info('Información de integración encontrada:', $integrationInfo->toArray());

        // Retornar la información final
        $result = [
            'identifier' => $device->identifier,
            'user' => $device->user,
            'cash_register' => $cashRegisterId, // Cash register interno
            'branch' => $integrationInfo->branch ?? 'Sucursal1',
            'company' => $integrationInfo->company ?? null,
            'system_id' => $integrationInfo->system_id ?? null,
            'client_app_id' => $integrationInfo->client_app_id ?? 'Caja1',
        ];

        Log::info('Resultado de getDeviceInfo:', $result);
        return $result;
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
