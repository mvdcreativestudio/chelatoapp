<?php

namespace App\Services\POS;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use App\Models\Transaction;
use App\Models\PosProvider;

class ScanntechIntegrationService implements PosIntegrationInterface
{
    protected $authService;
    protected $apiUrl;

    public function __construct(ScanntechAuthService $authService)
    {
        $this->authService = $authService;
        $this->apiUrl = $this->getScanntechApiUrl(); // Asignar la URL de la API al instanciar
    }

    // Obtener la URL de la API desde la tabla pos_providers
    protected function getScanntechApiUrl()
    {
        $posProvider = PosProvider::find(1); // Scanntech tiene el ID 1
        if ($posProvider && $posProvider->api_url) {
            Log::info('URL de la API de Scanntech encontrada: ' . $posProvider->api_url);
            return $posProvider->api_url;
        } else {
            Log::error('No se encontró la URL de la API para el proveedor Scanntech.');
            throw new \Exception('No se pudo encontrar la URL de la API para Scanntech');
        }
    }

    public function getToken()
    {
        return $this->authService->getAccessToken();
    }

    public function formatTransactionData(array $transactionData): array
    {
        Log::info('Datos recibidos en formatTransactionData (Scanntech):', $transactionData);

        return [
            'PosID' => $transactionData['PosID'] ?? null,
            'Empresa' => $transactionData['Empresa'] ?? '2024',
            'Local' => $transactionData['Local'] ?? '1',
            'Caja' => $transactionData['Caja'] ?? '7',
            'UserId' => $transactionData['UserId'] ?? 'Usuario1',
            'TransactionDateTimeyyyyMMddHHmmssSSS' => now()->format('YmdHis'),
            'Amount' => number_format($transactionData['Amount'] ?? 0, 0, '', ''),
            'Quotas' => isset($transactionData['Quotas']) && is_numeric($transactionData['Quotas']) ? (int) $transactionData['Quotas'] : 1, // Validar y asignar valor predeterminado
            'Plan' => $transactionData['Plan'] ?? 1,
            'Currency' => '858',
            'TaxableAmount' => number_format($transactionData['Amount'] ?? 0, 0, '', ''),
            'InvoiceAmount' => number_format($transactionData['Amount'] ?? 0, 0, '', ''),
            'NeedToReadCard' => $transactionData['NeedToReadCard'] ?? 0,
            'order_id' => $transactionData['order_id'] ?? null,
            'TransactionTimeOut' => '60', // 30 segundos o timeout
        ];
    }

    public function processTransaction(array $transactionData): array
    {
        // Crear el registro inicial de la transacción
        $initialTransaction = Transaction::create([
          'order_id' => $transactionData['order_id'] ?? null, // Asignar el order_id desde los datos recibidos
          'TransactionId' => null, // Inicialmente nulo, se actualizará con la respuesta de Fiserv
          'STransactionId' => null, // Inicialmente nulo
          'status' => 'pending', // Estado inicial de la transacción
          'formatted_data' => $transactionData, // Guardar los datos iniciales de la transacción
        ]);

        // Verificar si el order_id se asignó correctamente en la transacción inicial
        if (!$initialTransaction->order_id) {
          Log::error('El order_id no se asignó correctamente en la transacción inicial:', [
              'transactionData' => $transactionData,
              'transactionRecord' => $initialTransaction->toArray(),
          ]);
        }

        Log::info('Registro inicial de transacción creado en la base de datos:', $initialTransaction->toArray());

        $token = $this->authService->getAccessToken();
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ])->post($this->apiUrl . 'postPurchase', $transactionData);


        Log::info('Enviando transacción a Scanntech', $transactionData);

        if ($response->successful()) {
            $jsonResponse = $response->json();
            Log::info('Respuesta de Scanntech al procesar transacción:', $jsonResponse);

            // Actualizar el registro de la transacción con los datos de la respuesta de Fiserv
            $initialTransaction->update([
              'TransactionId' => $jsonResponse['TransactionId'] ?? null,
              'STransactionId' => $jsonResponse['STransactionId'] ?? null,
              'formatted_data' => array_merge($transactionData, [
                  'TransactionId' => $jsonResponse['TransactionId'] ?? null,
                  'STransactionId' => $jsonResponse['STransactionId'] ?? null,
              ]),
            ]);

            Log::info('Datos de la transacción actualizados en la base de datos:', $initialTransaction->toArray());

            return [
                'success' => true,
                'response' => $jsonResponse,
            ];
        }

        Log::error('Error al procesar la transacción con Scanntech: ' . $response->body());
        return [
            'success' => false,
            'message' => 'Error al procesar la transacción con Scanntech',
            'response' => $response->body(),
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

            // Decodificar el campo formatted_data
            $formattedData = $lastTransaction->formatted_data;

            // Verificar si formatted_data ya es un arreglo
            if (is_array($formattedData)) {
                $formattedDataArray = $formattedData;
            } else {
                // Intentar decodificar si es una cadena JSON
                $formattedDataArray = json_decode($formattedData, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('El campo formatted_data no contiene un JSON válido.');
                }
            }

            // Preparar los datos para la consulta
            $queryData = array_merge(
                $formattedDataArray ?? [], // Usar el arreglo procesado
                [
                    'TransactionId' => $lastTransaction->TransactionId,
                    'STransactionId' => $lastTransaction->STransactionId,
                ]
            );

            Log::info('Cuerpo de la solicitud para Scanntech getTransactionState', $queryData);

            // Realizar la solicitud
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->authService->getAccessToken(),
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . 'getTransactionState', $queryData);

            if ($response->successful()) {
                $jsonResponse = $response->json();
                Log::info('Estado de transacción recibido de Scanntech', $jsonResponse);

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
            Log::error('Error al consultar estado de transacción con Scanntech: ' . $response->body());
            return $this->getResponses(999);
        } catch (\Exception $e) {
            Log::error('Excepción al consultar estado de transacción en Scanntech: ' . $e->getMessage());
            return $this->getResponses(999);
        }
    }


    public function getResponses($responseCode)
    {
        $responses = Config::get('ScanntechResponses.postPurchaseResponses');
        $responseCode = (int)$responseCode;
        Log::info('Buscando respuesta para el código: ' . $responseCode);

        if (isset($responses[$responseCode])) {
            Log::info('Respuesta encontrada para el código ' . $responseCode . ':', $responses[$responseCode]);
            return $responses[$responseCode];
        } else {
            Log::warning('Código de respuesta no encontrado: ' . $responseCode);
            return [
                'message' => 'Código de respuesta desconocido: ' . $responseCode,
                'icon' => 'warning',
                'showCloseButton' => true,
            ];
        }
    }

    public function reverseTransaction(array $transactionData): array
    {
        // Aquí va la lógica del método.
        // Como ejemplo, podríamos retornar un array con un mensaje de error.
        return [
            'success' => false,
            'message' => 'Reverse transaction not implemented.'
        ];
    }

    public function voidTransaction(array $transactionData): array
    {
        // Implementación acorde a la interfaz
        return [
            'success' => false,
            'message' => 'Void transaction not implemented.'
        ];
    }

    public function pollVoidStatus(array $transactionData): array
    {
        // Implementación acorde a la interfaz
        return [
            'success' => false,
            'message' => 'Poll void status not implemented.'
        ];
    }

    public function fetchTransactionHistory(array $transactionData): array
    {
        // Implementación acorde a la interfaz
        return [
            'success' => false,
            'message' => 'Poll void status not implemented.'
        ];
    }

    public function fetchBatchCloses(array $transactionData): array
    {
        // Implementación acorde a la interfaz
        return [
            'success' => false,
            'message' => 'Fetch batch closes not implemented.'
        ];
    }

    public function fetchOpenBatches(array $transactionData): array
    {
        // Implementación acorde a la interfaz
        return [
            'success' => false,
            'message' => 'Fetch open batches not implemented.'
        ];
    }


}
