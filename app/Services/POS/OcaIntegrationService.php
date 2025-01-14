<?php

namespace App\Services\POS;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use App\Models\PosProvider;
use App\Models\Transaction;


class OcaIntegrationService implements PosIntegrationInterface
{
    protected $apiUrl;  // Definir la propiedad para la URL de la API


    public function __construct()
    {
        $this->apiUrl = $this->getOcaApiUrl();  // Asignar la URL de la API cuando se crea la instancia
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
            'order_id' => $transactionData['order_id'] ?? null,
            'TransactionTimeOut' => '30', // 30 segundos o timeout
        ];
    }


    // Obtener la URL de la API desde la tabla pos_providers
    protected function getOcaApiUrl()
    {
        // Obtener el pos_provider con el id 2 (que corresponde a Oca)
        $posProvider = PosProvider::find(3); // Oca tiene el ID 3

        if ($posProvider && $posProvider->api_url) {
            Log::info('URL de la API de Oca encontrada: ' . $posProvider->api_url);
            return $posProvider->api_url;
        } else {
            // Registrar un error si no se encuentra el proveedor o no tiene URL definida
            Log::error('No se encontró la URL de la API para el proveedor Oca.');
            throw new \Exception('No se pudo encontrar la URL de la API para Oca');
        }
    }

    // No se necesita autenticación en Oca
    public function getToken()
    {
        return null; // No se necesita autenticación en Oca
    }

    public function processTransaction(array $transactionData): array
    {
        // Agregar un log para inspeccionar los datos que se están enviando para crear la transacción
        Log::info('Datos recibidos para crear la transacción inicial:', $transactionData);

        // Crear el registro inicial de la transacción
        $initialTransaction = Transaction::create([
            'order_id' => $transactionData['order_id'] ?? null, // Asignar el order_id desde los datos recibidos
            'TransactionId' => null, // Inicialmente nulo, se actualizará con la respuesta de Oca
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

        // Realiza la solicitud a Oca
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($this->apiUrl . 'processFinancialPurchase', $transactionData);

        Log::info('Estableciendo conexión Oca en ' . $this->apiUrl . 'processFinancialPurchase');
        Log::info('Datos de la transacción enviados a Oca:', $transactionData);

        if ($response->successful()) {
            $jsonResponse = $response->json();
            Log::info('Respuesta de Oca al procesar transacción:', $jsonResponse);

            // Actualizar el registro de la transacción con los datos de la respuesta de Oca
            $initialTransaction->update([
                'TransactionId' => $jsonResponse['TransactionId'] ?? null,
                'STransactionId' => $jsonResponse['STransactionId'] ?? null,
                'formatted_data' => array_merge($transactionData, [
                    'TransactionId' => $jsonResponse['TransactionId'] ?? null,
                    'STransactionId' => $jsonResponse['STransactionId'] ?? null,
                ]),
            ]);

            Log::info('Datos de la transacción actualizados en la base de datos:', $initialTransaction->toArray());

            // Verificar el estado de la transacción
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

        // Manejar errores en la solicitud a Oca
        Log::error('Error al procesar la transacción con Oca: ' . $response->body());

        // En caso de error, puedes actualizar el estado de la transacción para indicar un fallo
        $initialTransaction->update([
            'status' => 'failed', // Agrega un campo de estado si es necesario
            'error_message' => $response->body(),
        ]);

        return [
            'success' => false,
            'message' => 'Error al procesar la transacción con Oca',
        ];
    }

    public function checkTransactionStatus(array $transactionData): array
    {
        try {
            Log::info('Iniciando consulta de estado para transacción (checkTransactionStatus)', $transactionData);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . 'processFinancialPurchaseQuery', $transactionData);

            Log::info('Respuesta de Oca en checkTransactionStatus:', [
                'status_code' => $response->status(),
                'response_body' => $response->body(),
            ]);

            if ($response->successful()) {
                $jsonResponse = $response->json();

                // Verificar si ResponseCode es 0 (transacción exitosa)
                if (isset($jsonResponse['ResponseCode']) && $jsonResponse['ResponseCode'] === 0) {
                    Log::info('Transacción exitosa según Oca (ResponseCode 0):', $jsonResponse);

                    // Obtener el Acquirer y asegurarnos de que esté presente
                    $acquirer = $jsonResponse['Acquirer'] ?? null;
                    if ($acquirer) {
                        Log::info('Acquirer obtenido de la respuesta de Oca:', ['Acquirer' => $acquirer]);

                        // Actualizar el registro de la transacción en la base de datos
                        Transaction::where('TransactionId', $transactionData['TransactionId'])
                            ->update([
                                'status' => 'completed', // Cambiar el estado a completado
                                'formatted_data->Acquirer' => $acquirer, // Actualizar el campo Acquirer en los datos formateados
                            ]);

                        Log::info('Transacción actualizada con el Acquirer en la base de datos.');
                    } else {
                        Log::warning('Acquirer no presente en la respuesta de Oca.');
                    }

                    return [
                        'responseCode' => 0,
                        'message' => 'La transacción fue exitosa.',
                        'icon' => 'success',
                        'keepPolling' => false,
                        'transactionSuccess' => true,
                        'details' => $jsonResponse,
                    ];
                }

                // Manejar el caso donde PosResponseCode esté presente
                $posResponseCode = $jsonResponse['PosResponseCode'] ?? '';
                if (!empty($posResponseCode)) {
                    if ($posResponseCode === 'CT') {
                        return [
                            'responseCode' => 999,
                            'message' => 'La transacción fue cancelada.',
                            'icon' => 'error',
                            'keepPolling' => false,
                            'transactionSuccess' => false,
                            'details' => $jsonResponse,
                        ];
                    }
                }

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
            Log::error('Error al consultar estado de transacción con Oca: ' . $response->body());
            return $this->getResponses(999);

        } catch (\Exception $e) {
            Log::error('Excepción al consultar estado de transacción en Oca: ' . $e->getMessage());
            return $this->getResponses(999);
        }
    }



    public function reverseTransaction(array $transactionData): array
    {
        try {
            Log::info('Iniciando proceso de reverso de transacción', $transactionData);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . 'processFinancialReverse', $transactionData);

            Log::info('Respuesta de reverso de Oca:', [
                'status_code' => $response->status(),
                'response_body' => $response->body(),
            ]);

            if ($response->successful()) {
                $jsonResponse = $response->json();

                if ($jsonResponse['ResponseCode'] === 0) {
                    Log::info('Reverso completado exitosamente', $jsonResponse);

                    // Actualiza la transacción como reversada en la base de datos
                    Transaction::where('TransactionId', $transactionData['TransactionId'])
                        ->update(['status' => 'reversed']);

                    return [
                        'success' => true,
                        'message' => 'Transacción reversada exitosamente.',
                        'details' => $jsonResponse,
                    ];
                }

                return [
                    'success' => false,
                    'message' => 'Error al realizar el reverso: ' . ($jsonResponse['Message'] ?? 'Código ' . $jsonResponse['ResponseCode']),
                    'details' => $jsonResponse,
                ];
            }

            return [
                'success' => false,
                'message' => 'Error al conectarse a Oca.',
                'details' => $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error('Error durante el proceso de reverso de transacción: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error interno al realizar el reverso.',
                'details' => $e->getMessage(),
            ];
        }
    }


    public function getResponses($responseCode)
    {
        // Cargar respuestas desde la configuración
        $responses = Config::get('OcaResponses.processFinancialPurchaseResponses');

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

    public function voidTransaction(array $transactionData): array
{
    try {
        Log::info('Iniciando voidTransaction en OcaIntegrationService con los datos:', $transactionData);

        // Buscar la transacción original utilizando order_id y status "completed"
        $originalTransaction = Transaction::where('order_id', $transactionData['order_id'])
            ->where('status', 'completed')
            ->first();

        if (!$originalTransaction) {
            Log::error('No se encontró una transacción original con status "completed" para el order_id proporcionado.', [
                'order_id' => $transactionData['order_id']
            ]);
            throw new \Exception('No se encontró una transacción original con status "completed" para el order_id proporcionado.');
        }

        Log::info('Transacción original encontrada:', $originalTransaction->toArray());

        // Validar que formatted_data sea un array
        $formattedData = $originalTransaction->formatted_data;
        if (is_string($formattedData)) {
            $formattedData = json_decode($formattedData, true);
        }

        if (!is_array($formattedData)) {
            Log::error('formatted_data no es un array válido.', [
                'formatted_data' => $originalTransaction->formatted_data
            ]);
            throw new \Exception('formatted_data no es un array válido.');
        }

        // Verificar que el Acquirer exista en formatted_data
        if (!isset($formattedData['Acquirer'])) {
            Log::error('Acquirer no encontrado en formatted_data.', [
                'formatted_data' => $formattedData
            ]);
            throw new \Exception('Acquirer no encontrado en formatted_data de la transacción original.');
        }

        // Preparar los datos para la solicitud de anulación a Oca
        $voidRequestData = [
            'PosID' => $transactionData['PosID'] ?? $formattedData['PosID'],
            'SystemId' => $transactionData['SystemId'] ?? $formattedData['SystemId'],
            'Branch' => $transactionData['Branch'] ?? $formattedData['Branch'],
            'ClientAppId' => $transactionData['ClientAppId'] ?? $formattedData['ClientAppId'],
            'UserId' => $transactionData['UserId'] ?? $formattedData['UserId'],
            'TransactionDateTimeyyyyMMddHHmmssSSS' => '20241121201131000',
            'TicketNumber' => $transactionData['TicketNumber'] ?? $formattedData['TicketNumber'],
            'Acquirer' => (string) ($transactionData['Acquirer'] ?? $formattedData['Acquirer']), // Convertir a string
            'CiNoCheckDigict' => '4795307',
            'Merchant' => 'Resonet',
          ];

        Log::info('Enviando solicitud de anulación a Oca con los datos:', $voidRequestData);

        // Realizar la solicitud de anulación a Oca
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($this->apiUrl . 'processFinancialPurchaseVoidByTicket', $voidRequestData);

        if ($response->successful()) {
            $jsonResponse = $response->json();
            Log::info('Respuesta de Oca al realizar voidTransaction:', $jsonResponse);

            if ($jsonResponse['ResponseCode'] === 0) {
                // Crear una nueva transacción de anulación
                $voidTransaction = Transaction::create([
                    'TransactionId' => (string) $jsonResponse['TransactionId'], // Guardar como string
                    'STransactionId' => (string) $jsonResponse['STransactionId'], // Guardar como string
                    'order_id' => $originalTransaction->order_id, // Asociar con el mismo order_id
                    'type' => 'void',
                    'status' => 'pending',
                    'formatted_data' => array_merge($voidRequestData, [
                        'TransactionId' => (string) $jsonResponse['TransactionId'],
                        'STransactionId' => (string) $jsonResponse['STransactionId'],
                    ]),
                ]);

                Log::info('Transacción de anulación registrada en la base de datos:', $voidTransaction->toArray());

                return [
                    'success' => true,
                    'message' => 'Transacción de anulación completada exitosamente.',
                    'details' => $jsonResponse,
                ];
            }

            return [
                'success' => false,
                'message' => $jsonResponse['Message'] ?? 'Error al anular la transacción.',
                'details' => $jsonResponse,
            ];
        }

        Log::error('Error al realizar la solicitud de anulación a Oca:', $response->body());

        return [
            'success' => false,
            'message' => 'Error al conectarse a Oca.',
            'details' => $response->body(),
        ];
    } catch (\Exception $e) {
        Log::error('Error durante voidTransaction:', [
            'message' => $e->getMessage(),
            'transactionData' => $transactionData,
        ]);

        return [
            'success' => false,
            'message' => 'Error interno al realizar la anulación.',
            'details' => $e->getMessage(),
        ];
    }
}





    public function pollVoidStatus(array $transactionData): array
    {
        try {
            Log::info('Iniciando consulta de estado para transacción anulada (pollVoidStatus)', $transactionData);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . 'processFinancialPurchaseQuery', $transactionData);

            Log::info('Respuesta de Oca en pollVoidStatus:', [
                'status_code' => $response->status(),
                'response_body' => $response->body(),
            ]);

            if ($response->successful()) {
                $jsonResponse = $response->json();

                if ($jsonResponse['ResponseCode'] === 0 && $jsonResponse['PosResponseCode'] === '00') {
                    Log::info('La transacción fue anulada correctamente (ResponseCode 111)', $jsonResponse);

                    // Actualizar el estado de la transacción en la base de datos
                    Transaction::where('TransactionId', (string) $transactionData['TransactionId'])
                        ->update(['status' => 'completed']);

                    return [
                        'success' => true,
                        'message' => 'La transacción fue anulada correctamente.',
                        'details' => $jsonResponse,
                    ];
                }

                return [
                    'success' => false,
                    'keepPolling' => true,
                    'message' => 'La transacción aún no ha sido anulada.',
                    'details' => $jsonResponse,
                ];
            }

            return [
                'success' => false,
                'message' => 'Error al consultar el estado de la transacción.',
                'details' => $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error('Error en pollVoidStatus: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al realizar la consulta.',
                'details' => $e->getMessage(),
            ];
        }
    }


    public function processQuery(array $queryData): array
    {
        try {
            Log::info('Iniciando processQuery en Oca con datos:', $queryData);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . 'processQuery', $queryData);

            if ($response->successful()) {
                $jsonResponse = $response->json();
                Log::info('Respuesta de Oca para processQuery:', $jsonResponse);

                return [
                    'success' => true,
                    'data' => $jsonResponse,
                ];
            }

            Log::error('Error en processQuery de Oca: ' . $response->body());
            return [
                'success' => false,
                'message' => 'Error en processQuery: ' . $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error('Excepción en processQuery de Oca: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Excepción en processQuery: ' . $e->getMessage(),
            ];
        }
    }

    public function fetchTransactionHistory(array $queryData): array
    {
        try {
            // Obtener la URL base desde el método getOcaApiUrl
            $baseUrl = $this->getOcaApiUrl();

            // Endpoint específico para el historial de transacciones
            $endpoint = $baseUrl . 'processQuery?QueryRequest';

            // Validar datos antes de enviar la solicitud
            Log::info('Preparando solicitud para obtener historial de transacciones:', [
                'endpoint' => $endpoint,
                'queryData' => $queryData,
            ]);

            // Enviar la solicitud utilizando sendRequest
            $response = $this->sendRequest($endpoint, $queryData, 'POST');

            // Validar la respuesta
            if (!isset($response['ResponseCode']) || $response['ResponseCode'] !== 0) {
                throw new \Exception('Error en la API de Oca: ' . ($response['ResponseMessage'] ?? 'Error desconocido'));
            }

            return $response;
        } catch (\Exception $e) {
            Log::error('Error al obtener el historial de transacciones en OcaIntegrationService: ' . $e->getMessage());
            throw $e;
        }
    }



    public function sendRequest(string $url, array $data, string $method = 'POST'): array
    {
        try {
            // Configurar el cliente HTTP
            $client = new \GuzzleHttp\Client([
                'timeout' => 30.0, // Tiempo de espera en segundos
            ]);

            $options = [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ];

            if ($method === 'GET') {
                // Si el método es GET, pasar los datos como query params
                $options['query'] = $data;
            } else {
                // Para POST, enviar los datos como JSON
                $options['json'] = $data;
            }

            // Registrar el URL y los datos que se están enviando
            Log::info('Realizando solicitud HTTP:', [
                'url' => $url,
                'method' => $method,
                'data' => $data,
                'options' => $options,
            ]);

            // Realizar la solicitud HTTP
            $response = $client->request($method, $url, $options);

            // Decodificar la respuesta JSON
            $responseData = json_decode($response->getBody()->getContents(), true);

            // Verificar errores de decodificación
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Error al decodificar la respuesta JSON: ' . json_last_error_msg());
            }

            return $responseData;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Log::error('Error 4xx en la solicitud HTTP a Oca:', [
                'message' => $e->getMessage(),
                'response' => $e->getResponse() ? $e->getResponse()->getBody()->getContents() : null,
                'url' => $url,
                'data' => $data,
            ]);
            throw new \Exception('Error en la solicitud HTTP: ' . $e->getMessage());
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            Log::error('Error 5xx en la solicitud HTTP a Oca:', [
                'message' => $e->getMessage(),
                'response' => $e->getResponse() ? $e->getResponse()->getBody()->getContents() : null,
                'url' => $url,
                'data' => $data,
            ]);
            throw new \Exception('Error del servidor en la solicitud HTTP: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Error general en sendRequest:', [
                'message' => $e->getMessage(),
                'url' => $url,
                'data' => $data,
            ]);
            throw $e;
        }
    }

    public function processRefundTransaction(array $refundData): array
    {
        try {
            Log::info('Iniciando proceso de devolución con los datos:', $refundData);

            // Crear una nueva transacción en la base de datos con estado 'pending'
            $refundTransaction = Transaction::create([
                'order_id' => $refundData['order_id'] ?? null,
                'TransactionId' => null, // Será actualizado con la respuesta de Oca
                'STransactionId' => null, // Será actualizado con la respuesta de Oca
                'status' => 'pending',
                'type' => 'refund', // Identificar que esta es una transacción de devolución
                'formatted_data' => $refundData, // Guardar los datos iniciales de la devolución
            ]);

            Log::info('Registro inicial de devolución creado en la base de datos:', $refundTransaction->toArray());

            // Transformar el monto a centavos
            $amount = $refundData['Amount'] * 100;

            // Formatear los datos para la solicitud a Oca
            $requestPayload = [
                'PosID' => $refundData['PosID'],
                'SystemId' => $refundData['SystemId'],
                'Branch' => $refundData['Branch'],
                'ClientAppId' => $refundData['ClientAppId'],
                'UserId' => $refundData['UserId'],
                'TransactionDateTimeyyyyMMddHHmmssSSS' => now()->format('YmdHis') . '000',
                'TicketNumber' => $refundData['TicketNumber'],
                'OriginalTransactionDateyyMMdd' => $refundData['OriginalTransactionDateyyMMdd'],
                'Amount' => (string)$amount,
                'Currency' => $refundData['Currency'] ?? '858', // Default a pesos
                'Quotas' => $refundData['Quotas'] ?? 1,
                'Plan' => $refundData['Plan'] ?? 0,
                'TaxableAmount' => $refundData['TaxableAmount'] ?? $refundData['Amount'],
                'TaxRefund' => $refundData['TaxRefund'] ?? 0,
                'InvoiceAmount' => $refundData['InvoiceAmount'] ?? $refundData['Amount'],
            ];

            Log::info('Datos formateados para la solicitud de devolución a Oca:', $requestPayload);

            // Realizar la solicitud a Oca
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . 'processFinancialPurchaseRefund', $requestPayload);

            Log::info('Respuesta de Oca para la solicitud de devolución:', [
                'status_code' => $response->status(),
                'response_body' => $response->body(),
            ]);

            // Manejar la respuesta de Oca
            if ($response->successful()) {
                $jsonResponse = $response->json();

                // Actualizar la transacción con los datos de Oca
                $refundTransaction->update([
                    'TransactionId' => $jsonResponse['TransactionId'] ?? null,
                    'STransactionId' => $jsonResponse['STransactionId'] ?? null,
                    'status' => 'completed', // Marcar como completada
                    'formatted_data' => array_merge($refundData, [
                        'TransactionId' => $jsonResponse['TransactionId'] ?? null,
                        'STransactionId' => $jsonResponse['STransactionId'] ?? null,
                    ]),
                ]);

                Log::info('Transacción de devolución actualizada en la base de datos:', $refundTransaction->toArray());

                return [
                    'success' => true,
                    'message' => 'Devolución procesada exitosamente.',
                    'details' => $jsonResponse,
                ];
            }

            // Manejar errores en la solicitud
            Log::error('Error en la solicitud de devolución a Oca:', $response->body());

            // Actualizar la transacción con el estado de fallo
            $refundTransaction->update([
                'status' => 'failed',
                'error_message' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Error al procesar la devolución.',
                'details' => $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error('Excepción durante el proceso de devolución:', [
                'message' => $e->getMessage(),
                'refundData' => $refundData,
            ]);

            return [
                'success' => false,
                'message' => 'Error interno al procesar la devolución.',
                'details' => $e->getMessage(),
            ];
        }
    }

    public function fetchBatchCloses(array $queryData): array
    {
        try {
            Log::info('Preparando solicitud para obtener historial de cierres:', $queryData);

            $url = $this->apiUrl . 'processQueryLastNClose?QueryLastNCloseRequest';
            $response = $this->sendRequest($url, $queryData, 'POST');

            Log::info('Respuesta completa de Oca para fetchBatchCloses:', $response);

            // Validar que la respuesta contiene los datos esperados
            if (!isset($response['ResponseCode']) || $response['ResponseCode'] !== 0) {
                throw new \Exception('Error en la API de Oca: ' . ($response['ResponseMessage'] ?? 'Error desconocido'));
            }

            return $response;
        } catch (\Exception $e) {
            Log::error('Error al obtener el historial de cierres en OcaIntegrationService: ' . $e->getMessage());
            throw $e;
        }
    }

    public function fetchOpenBatches(array $queryData): array
{
    try {
        Log::info('Preparando solicitud para obtener lotes abiertos:', $queryData);

        $url = $this->apiUrl . 'ProcessCurrentTransactionsBatchQuery';
        $response = $this->sendRequest($url, $queryData, 'POST');

        Log::info('Respuesta completa de Oca para fetchOpenBatches:', $response);

        // Validar que la respuesta contiene los datos esperados
        if (!isset($response['ResponseCode']) || $response['ResponseCode'] !== 0) {
            throw new \Exception('Error en la API de Oca: ' . ($response['ResponseMessage'] ?? 'Error desconocido'));
        }

        return $response;
    } catch (\Exception $e) {
        Log::error('Error al obtener los lotes abiertos en OcaIntegrationService: ' . $e->getMessage());
        throw $e;
    }
}



}
