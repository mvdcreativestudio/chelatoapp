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

    protected function setIntegrationByProvider(PosProvider $posProvider)
    {
        switch ($posProvider->id) {
            case 1: // Scanntech
                Log::info('Configurando integración para Scanntech');
                $this->posIntegration = new \App\Services\POS\ScanntechIntegrationService();
                break;

            case 2: // Fiserv
                Log::info('Configurando integración para Fiserv');
                $this->posIntegration = new \App\Services\POS\FiservIntegrationService();
                break;

            default:
                throw new \Exception('Proveedor POS no soportado: ' . $posProvider->id);
        }
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
        $integrationInfo = PosIntegrationStoreInfo::where('store_id', $storeId)->first();

        if (!$integrationInfo) {
            throw new \Exception('No se encontró la integración POS para esta tienda.');
        }

        Log::info('Estableciendo integración POS para store ID: ' . $storeId . ', Proveedor POS ID: ' . $integrationInfo->pos_provider_id);

        switch ($integrationInfo->pos_provider_id) {
            case 1: // Scanntech
                Log::info('Integración seleccionada: Scanntech');
                $this->posIntegration = new ScanntechIntegrationService(new ScanntechAuthService());
                break;

            case 2: // Fiserv
                Log::info('Integración seleccionada: Fiserv');
                $this->posIntegration = new FiservIntegrationService();
                break;

            default:
                throw new \Exception('Proveedor POS no soportado para esta tienda.');
        }
    }


    /**
     * Realizar un reverso de transacción seleccionando el proveedor POS dinámicamente.
     *
     * @param array $transactionData
     * @return array
     * @throws \Exception
     */
    public function reverseTransaction(array $transactionData): array
    {
        Log::info('Iniciando reverseTransaction con los datos:', $transactionData);

        // Verificar que TransactionId esté presente
        if (!isset($transactionData['TransactionId'])) {
            throw new \Exception('La clave "TransactionId" no está definida en los datos de la transacción.');
        }

        // Obtener la transacción desde la base de datos utilizando TransactionId
        $transaction = \App\Models\Transaction::where('TransactionId', $transactionData['TransactionId'])->first();

        if (!$transaction) {
            throw new \Exception('No se encontró una transacción con el TransactionId proporcionado.');
        }

        // Obtener el store_id a través del order_id relacionado en la tabla orders
        $order = \App\Models\Order::find($transaction->order_id);

        if (!$order || !$order->store_id) {
            throw new \Exception('No se pudo obtener el store_id a través del order_id relacionado.');
        }

        $storeId = $order->store_id;

        // Establecer el proveedor POS basado en el store_id
        $this->setPosIntegration($storeId);

        // Obtener los datos formateados desde la transacción
        $formattedData = $transaction->formatted_data;

        Log::info('Datos completos para el reverso desde formatted_data:', $formattedData);

        // Delegar la lógica específica al proveedor POS
        return $this->posIntegration->reverseTransaction($formattedData);
    }


    public function voidTransaction(array $transactionData): array
    {
        Log::info('Iniciando voidTransaction con los datos:', $transactionData);

        // Configurar integración POS
        $this->setPosIntegration($transactionData['store_id']);

        // Obtener los datos del dispositivo POS seleccionado
        $device = PosDevice::findOrFail($transactionData['pos_device_id']);

        $formattedData = [
            'PosID' => $transactionData['PosID'] ?? $device->identifier,
            'SystemId' => $transactionData['SystemId'] ?? $device->provider->api_url,
            'Branch' => $transactionData['Branch'] ?? $device->store->branch ?? 'Sucursal1',
            'ClientAppId' => $transactionData['ClientAppId'] ?? 'Caja1',
            'UserId' => $transactionData['UserId'] ?? $device->user,
            'TransactionDateTimeyyyyMMddHHmmssSSS' => $transactionData['TransactionDateTimeyyyyMMddHHmmssSSS'] ?? now()->format('YmdHis') . '000',
            'TicketNumber' => $transactionData['TicketNumber'],
            'order_id' => $transactionData['order_id'] ?? null,

        ];

        Log::info('Datos formateados para enviar a voidTransaction:', $formattedData);

        // Validar que posIntegration esté configurado
        if (is_null($this->posIntegration)) {
            throw new \Exception('No se configuró posIntegration antes de llamar a voidTransaction.');
        }

        return $this->posIntegration->voidTransaction($formattedData);
    }


    public function pollVoidStatus(array $transactionData): array
    {
        $this->setPosIntegration($transactionData['store_id']); // Configurar la integración según el store_id
        return $this->posIntegration->pollVoidStatus($transactionData);
    }


    public function fetchTransactionHistory(array $validated): array
{
    try {
        // Validar que el pos_device_id sea válido
        $posDevice = PosDevice::findOrFail($validated['pos_device_id']);
        $posProvider = PosProvider::findOrFail($posDevice->pos_provider_id);

        // Usar el store_id directamente del $validated
        $storeId = $validated['store_id'];

        // Obtener el system_id desde pos_integrations_store_info usando store_id y pos_provider_id
        $integrationInfo = PosIntegrationStoreInfo::where([
            ['store_id', '=', $storeId],
            ['pos_provider_id', '=', $posProvider->id]
        ])->first();

        if (!$integrationInfo || !$integrationInfo->system_id) {
            Log::error('No se encontró el system_id para la tienda seleccionada.', [
                'store_id' => $storeId,
                'pos_provider_id' => $posProvider->id,
            ]);
            throw new \Exception('No se encontró el system_id para la tienda seleccionada.');
        }

        // Configurar la integración POS
        $this->setIntegrationByProvider($posProvider);

        // Preparar los datos para la consulta
        $queryData = [
            'FromDateyyyyMMddHHmmss' => $validated['from_date'],
            'ToDateyyyyMMddHHmmss' => $validated['to_date'],
            'OnlyConfirmedTransactions' => $validated['only_confirmed'],
            'PosID' => $posDevice->identifier,
            'SystemId' => $integrationInfo->system_id, // Agregar el system_id al queryData
        ];

        // Obtener las transacciones desde la integración configurada
        $response = $this->posIntegration->fetchTransactionHistory($queryData);

        if (!isset($response['ResponseCode']) || $response['ResponseCode'] !== 0) {
            throw new \Exception('Error en la consulta del histórico: ' . ($response['ResponseMessage'] ?? 'Error desconocido'));
        }

        return $response['Transactions'] ?? [];
    } catch (\Exception $e) {
        Log::error('Error al obtener el historial de transacciones: ' . $e->getMessage());
        throw $e;
    }
}



}
