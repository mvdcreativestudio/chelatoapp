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
    try {
        Log::info('Iniciando proceso de reverso en Scanntech', $transactionData);

        $token = $this->authService->getAccessToken(); // Autenticación obligatoria

        $payload = [
            'PosID' => $transactionData['PosID'],
            'Empresa' => $transactionData['Empresa'] ?? '2024',
            'Local' => $transactionData['Local'] ?? '1',
            'Caja' => $transactionData['Caja'] ?? '7',
            'UserId' => $transactionData['UserId'] ?? 'Usuario1',
            'TransactionDateTimeyyyyMMddHHmmssSSS' => $transactionData['TransactionDateTimeyyyyMMddHHmmssSSS'],
            'TransactionId' => (int) $transactionData['TransactionId'],
            'STransactionId' => (string) $transactionData['STransactionId'],
        ];

        Log::info('Payload enviado a Scanntech (postReverse):', $payload);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ])->post($this->apiUrl . 'postReverse', $payload);

        Log::info('Respuesta de Scanntech (postReverse):', [
            'status_code' => $response->status(),
            'body' => $response->body(),
        ]);

        if (!$response->successful()) {
            return [
                'success' => false,
                'message' => 'Error en la comunicación con Scanntech al intentar reversar.',
                'details' => $response->body(),
            ];
        }

        $jsonResponse = $response->json();
        $responseCode = (int) ($jsonResponse['ResponseCode'] ?? 999);

        if ($responseCode === 0) {
            Log::info('Reverso exitoso en Scanntech', $jsonResponse);

            // Actualiza estado en la base de datos
            Transaction::where('TransactionId', $payload['TransactionId'])
                ->update(['status' => 'reversed']);

            return [
                'success' => true,
                'message' => 'Transacción reversada exitosamente.',
                'details' => $jsonResponse,
            ];
        }

        return [
            'success' => false,
            'message' => 'Error en el reverso: código ' . $responseCode,
            'details' => $jsonResponse,
        ];
    } catch (\Exception $e) {
        Log::error('Excepción durante reverseTransaction en Scanntech: ' . $e->getMessage());

        return [
            'success' => false,
            'message' => 'Excepción durante reverso.',
            'details' => $e->getMessage(),
        ];
    }
}


    public function voidTransaction(array $transactionData): array
    {
        try {
            $token = $this->authService->getAccessToken();

            // Buscar la transacción original
            $originalTransaction = Transaction::where('order_id', $transactionData['order_id'])
                ->where('type', 'sale')
                ->latest()
                ->first();

            if (!$originalTransaction) {
                Log::error('No se encontró la transacción original para anular', $transactionData);
                return [
                    'success' => false,
                    'message' => 'No se encontró la transacción original.',
                    'icon' => 'error',
                    'showCloseButton' => true,
                ];
            }

            // Decodificar formatted_data
            $formattedData = is_array($originalTransaction->formatted_data)
                ? $originalTransaction->formatted_data
                : json_decode($originalTransaction->formatted_data, true);

            if (!isset($formattedData['TransactionDateTimeyyyyMMddHHmmssSSS'])) {
                Log::error('No se encontró la fecha de la transacción original.', $formattedData);
                return [
                    'success' => false,
                    'message' => 'Falta la fecha de la transacción original.',
                    'icon' => 'error',
                    'showCloseButton' => true,
                ];
            }

            // Armar payload para Scanntech con la fecha original
            $payload = [
                'PosID' => $formattedData['PosID'],
                'Empresa' => $formattedData['Empresa'] ?? '2024',
                'Local' => $formattedData['Local'] ?? '1',
                'Caja' => $formattedData['Caja'] ?? '7',
                'UserId' => $formattedData['UserId'] ?? 'Usuario1',
                'TransactionDateTimeyyyyMMddHHmmssSSS' => $formattedData['TransactionDateTimeyyyyMMddHHmmssSSS'],
                'TicketNumber' => $transactionData['TicketNumber'] ?? null,
            ];

            Log::info('Payload final enviado a Scanntech (voidTransaction):', $payload);

            // Enviar anulación a Scanntech
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . 'postVoidPurchase', $payload);

            $jsonResponse = $response->json();
            Log::info('Respuesta de Scanntech (voidTransaction):', $jsonResponse);

            $responseCode = (int)($jsonResponse['ResponseCode'] ?? 999);
            $responseConfig = Config::get('ScanntechResponses.postPurchaseResponses')[$responseCode] ?? [
                'message' => 'Código de respuesta desconocido.',
                'icon' => 'warning',
                'showCloseButton' => true,
                'keepPolling' => false,
                'transactionSuccess' => false,
            ];

            // Si fue exitosa, guardar nueva transacción tipo "void"
            if ($responseCode === 0) {
                Transaction::create([
                    'order_id' => $transactionData['order_id'] ?? null,
                    'TransactionId' => $jsonResponse['TransactionId'] ?? null,
                    'STransactionId' => $jsonResponse['STransactionId'] ?? null,
                    'type' => 'void',
                    'status' => 'pending',
                    'formatted_data' => $payload,
                ]);
            }

            return array_merge([
                'success' => $responseCode === 0,
                'details' => $jsonResponse,
                'TransactionId' => $jsonResponse['TransactionId'] ?? null,
                'STransactionId' => $jsonResponse['STransactionId'] ?? null,
            ], $responseConfig);

        } catch (\Exception $e) {
            Log::error('Excepción al anular transacción en Scanntech: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Excepción al anular la transacción.',
                'icon' => 'error',
                'showCloseButton' => true,
                'keepPolling' => false,
                'transactionSuccess' => false,
                'details' => $e->getMessage(),
            ];
        }
    }


public function pollVoidStatus(array $transactionData): array
{
    Log::info('Iniciando pollVoidStatus en Scanntech con los datos:', $transactionData);

    try {
        $apiUrl = $this->getScanntechApiUrl();
        $endpoint = $apiUrl . 'getTransactionState';

        $payload = [
            'PosID' => $transactionData['PosID'],
            'Empresa' => $transactionData['Empresa'] ?? '2024',
            'Local' => $transactionData['Local'],
            'Caja' => $transactionData['Caja'],
            'UserId' => $transactionData['UserId'],
            'TransactionDateTimeyyyyMMddHHmmssSSS' => $transactionData['TransactionDateTimeyyyyMMddHHmmssSSS'],
            'TransactionId' => (int) $transactionData['TransactionId'],
            'STransactionId' => (string) $transactionData['STransactionId'],
        ];

        Log::info('Payload enviado a Scanntech (getTransactionState):', $payload);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->authService->getAccessToken(),
            'Content-Type' => 'application/json',
        ])->post($endpoint, $payload);

        Log::info('Respuesta cruda de Scanntech (pollVoidStatus):', [
            'status_code' => $response->status(),
            'body' => $response->body(),
        ]);

        // 🔒 Si hay error de autorización, cortamos
        if ($response->status() === 401) {
            Log::error('Token inválido o expirado en pollVoidStatus de Scanntech');
            return [
                'success' => false,
                'keepPolling' => false,
                'transactionSuccess' => false,
                'message' => 'No autorizado. Token inválido o expirado.',
                'details' => $response->body(),
            ];
        }

        // 🔧 Otros errores HTTP
        if (!$response->successful()) {
            return [
                'success' => false,
                'keepPolling' => false,
                'transactionSuccess' => false,
                'message' => 'Error en la comunicación con Scanntech.',
                'details' => $response->body(),
            ];
        }

        $data = $response->json();

        Log::info('Respuesta JSON procesada de Scanntech:', $data);

        $state = trim((string) ($data['State'] ?? ''));
        $responseCode = (int) ($data['ResponseCode'] ?? 999);

        // ✅ Éxito
        if (in_array($state, ['2', '3']) || in_array($responseCode, [0, 111])) {
            Transaction::where('TransactionId', (string) $transactionData['TransactionId'])
                ->update(['status' => 'completed']);

            return [
                'success' => true,
                'transactionSuccess' => true,
                'keepPolling' => false,
                'message' => 'Transacción anulada correctamente.',
                'details' => $data,
            ];
        }

        // ❌ Cancelada
        if (in_array($state, ['6']) || $responseCode === 14) {
            Transaction::where('TransactionId', (string) $transactionData['TransactionId'])
                ->update(['status' => 'canceled']);

            return [
                'success' => false,
                'transactionSuccess' => false,
                'keepPolling' => false,
                'message' => 'La transacción fue cancelada.',
                'details' => $data,
            ];
        }

        // ⌛ Expirada
        if (in_array($state, ['5']) || $responseCode === 11) {
            Transaction::where('TransactionId', (string) $transactionData['TransactionId'])
                ->update(['status' => 'expired']);

            return [
                'success' => false,
                'transactionSuccess' => false,
                'keepPolling' => false,
                'message' => 'La transacción expiró.',
                'details' => $data,
            ];
        }

        // 🔄 Aún esperando
        if ($responseCode === 10 || $state === '1') {
            return [
                'success' => false,
                'transactionSuccess' => false,
                'keepPolling' => true,
                'message' => 'Esperando operación en el POS...',
                'details' => $data,
            ];
        }

        // 📱 PinPad procesó los datos
        if ($responseCode === 12) {
            return [
                'success' => false,
                'transactionSuccess' => false,
                'keepPolling' => true,
                'message' => 'Procesando anulación.',
                'details' => $data,
            ];
        }

        // ❓ Estado desconocido
        return [
            'success' => false,
            'transactionSuccess' => false,
            'keepPolling' => false,
            'message' => 'Estado desconocido. Revisar logs.',
            'details' => $data,
        ];

    } catch (\Exception $e) {
        Log::error('Excepción en pollVoidStatus Scanntech: ' . $e->getMessage());
        return [
            'success' => false,
            'keepPolling' => false,
            'transactionSuccess' => false,
            'message' => 'Excepción al consultar estado de la transacción.',
            'details' => $e->getMessage(),
        ];
    }
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

    public function cancelTransaction(array $transactionData): array
    {
        // Implementación acorde a la interfaz
        return [
            'success' => false,
            'message' => 'Cancel transaction not implemented.'
        ];
    }


}
