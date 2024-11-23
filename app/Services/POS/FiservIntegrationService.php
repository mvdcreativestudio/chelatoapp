<?php

namespace App\Services\POS;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use App\Models\PosProvider;
use App\Models\Transaction;


class FiservIntegrationService implements PosIntegrationInterface
{
    protected $apiUrl;  // Definir la propiedad para la URL de la API

    public function __construct()
    {
        $this->apiUrl = $this->getFiservApiUrl();  // Asignar la URL de la API cuando se crea la instancia
    }

    public function formatTransactionData(array $transactionData): array
    {
        Log::info('Datos recibidos en formatTransactionData:', $transactionData);

        return [
            'PosID' => $transactionData['identifier'],
            'SystemId' => $transactionData['system_id'] ?? null,
            'ClientAppId' => $transactionData['client_app_id'] ?? 'Caja1',
            'Branch' => $transactionData['branch'],
            'UserId' => $transactionData['user'],
            'TransactionDateTimeyyyyMMddHHmmssSSS' => now()->format('YmdHis') . '000',
            'Amount' => number_format($transactionData['Amount'], 0, '', ''), // Cambiar a 'Amount'
            'Quotas' => $transactionData['Quotas'] ?? 1, // Asegúrate de usar la clave correcta
            'Plan' => $transactionData['Plan'] ?? 0,
            'Currency' => '858', // Código de moneda
            'TaxRefund' => 0, // Valor fijo
            'TaxableAmount' => number_format($transactionData['Amount'], 0, '', ''), // Cambiar a 'Amount'
            'InvoiceAmount' => number_format($transactionData['Amount'], 0, '', ''), // Cambiar a 'Amount'
        ];
    }


    // Obtener la URL de la API desde la tabla pos_providers
    protected function getFiservApiUrl()
    {
        // Obtener el pos_provider con el id 2 (que corresponde a Fiserv)
        $posProvider = PosProvider::find(2); // Fiserv tiene el ID 2

        if ($posProvider && $posProvider->api_url) {
            Log::info('URL de la API de Fiserv encontrada: ' . $posProvider->api_url);
            return $posProvider->api_url;
        } else {
            // Registrar un error si no se encuentra el proveedor o no tiene URL definida
            Log::error('No se encontró la URL de la API para el proveedor Fiserv.');
            throw new \Exception('No se pudo encontrar la URL de la API para Fiserv');
        }
    }

    // No se necesita autenticación en Fiserv
    public function getToken()
    {
        return null; // No se necesita autenticación en Fiserv
    }

    public function processTransaction(array $transactionData): array
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($this->apiUrl . 'processFinancialPurchase', $transactionData);

        Log::info('Estableciendo conexión Fiserv en ' . $this->apiUrl . 'processFinancialPurchase');
        Log::info('Data de la transacción: ' . json_encode($transactionData));

        if ($response->successful()) {
            $jsonResponse = $response->json();
            Log::info('Respuesta de Fiserv al procesar transacción:', $jsonResponse);

            // Guardar en la base de datos usando el modelo
            Transaction::create([
                'TransactionId' => $jsonResponse['TransactionId'] ?? null,
                'STransactionId' => $jsonResponse['STransactionId'] ?? null,
                'formatted_data' => $transactionData,
            ]);

            // Llamar al método para verificar el estado de la transacción
            try {
                $statusResponse = $this->checkTransactionStatus([
                    'TransactionId' => $jsonResponse['TransactionId'],
                    'STransactionId' => $jsonResponse['STransactionId'],
                ]);
                Log::info('Respuesta de verificación de estado:', $statusResponse);
            } catch (\Exception $e) {
                Log::error('Error al verificar el estado de la transacción: ' . $e->getMessage());
            }

            return $jsonResponse;
        }

        Log::error('Error al procesar la transacción con Fiserv: ' . $response->body());
        return [
            'success' => false,
            'message' => 'Error al procesar la transacción con Fiserv'
        ];
    }


    public function checkTransactionStatus(array $transactionData): array
    {
        try {
            // Obtener la última transacción registrada
            $lastTransaction = Transaction::latest()->first();

            if (!$lastTransaction || !$lastTransaction->TransactionId || !$lastTransaction->STransactionId) {
                throw new \Exception('No se encontró información de la última transacción.');
            }

            // Preparar los datos para la consulta
            $queryData = array_merge($lastTransaction->formatted_data, [
                'TransactionId' => $lastTransaction->TransactionId,
                'STransactionId' => $lastTransaction->STransactionId,
            ]);

            Log::info('Cuerpo de la solicitud para Fiserv processFinancialPurchaseQuery', $queryData);

            // Realizar la solicitud a Fiserv
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . 'processFinancialPurchaseQuery', $queryData);

            Log::info('Respuesta de Fiserv al consultar estado de transacción', [
                'status_code' => $response->status(),
                'response_body' => $response->body(),
            ]);

            if ($response->successful()) {
                $jsonResponse = $response->json();
                Log::info('Estado de transacción recibido de Fiserv', $jsonResponse);

                // Obtener la configuración del código de respuesta desde el archivo
                $responseCode = $jsonResponse['ResponseCode'] ?? 999;
                $responseConfig = $this->getResponses($responseCode);

                return [
                    'responseCode' => $responseCode,
                    'message' => $responseConfig['message'] ?? 'Error desconocido.',
                    'icon' => $responseConfig['icon'] ?? 'error',
                    'keepPolling' => $responseConfig['keepPolling'] ?? false,
                    'transactionSuccess' => $responseConfig['transactionSuccess'] ?? false,
                    'details' => $jsonResponse,
                ];
            }

            // Manejar errores HTTP
            Log::error('Error al consultar estado de transacción con Fiserv: ' . $response->body());
            return $this->getResponses(999);
        } catch (\Exception $e) {
            Log::error('Excepción al consultar estado de transacción en Fiserv: ' . $e->getMessage());
            return $this->getResponses(999);
        }
    }

    public function getResponses($responseCode)
    {
        // Cargar respuestas desde la configuración
        $responses = Config::get('FiservResponses.processFinancialPurchaseResponses');

        // Validar que las respuestas existan
        if (!$responses || !is_array($responses)) {
            Log::error('No se pudo cargar el archivo de configuración o el formato es incorrecto.');
            return [
                'message' => 'Error en la configuración de respuestas.',
                'icon' => 'error',
                'showCloseButton' => true
            ];
        }

        $responseCode = (int)$responseCode; // Asegurarse de que el código sea un entero
        Log::info('Buscando respuesta para el código: ' . $responseCode);

        // Verificar si el código existe en las respuestas configuradas
        if (isset($responses[$responseCode])) {
            Log::info('Respuesta encontrada para el código ' . $responseCode . ':', $responses[$responseCode]);
            return $responses[$responseCode];
        } else {
            Log::warning('Código de respuesta no encontrado: ' . $responseCode);
            return [
                'message' => 'Código de respuesta desconocido: ' . $responseCode,
                'icon' => 'warning',
                'showCloseButton' => true
            ];
        }
    }

    

}
