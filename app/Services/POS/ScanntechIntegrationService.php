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
            'Quotas' => $transactionData['Quotas'] ?? 1.5,
            'Plan' => $transactionData['Plan'] ?? 1,
            'Currency' => '858',
            'TaxableAmount' => number_format($transactionData['TaxableAmount'] ?? 0, 0, '', ''),
            'InvoiceAmount' => number_format($transactionData['InvoiceAmount'] ?? 0, 0, '', ''),
            'TaxAmount' => number_format($transactionData['TaxAmount'] ?? 0, 0, '', ''),
            'IVAAmount' => number_format($transactionData['IVAAmount'] ?? 0, 0, '', ''),
            'NeedToReadCard' => $transactionData['NeedToReadCard'] ?? 0,
        ];
    }

    public function processTransaction(array $transactionData): array
    {
        $token = $this->authService->getAccessToken();
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ])->post($this->apiUrl . 'postPurchase', $transactionData);

        Log::info('Enviando transacción a Scanntech', $transactionData);

        if ($response->successful()) {
            $jsonResponse = $response->json();
            Log::info('Respuesta de Scanntech al procesar transacción:', $jsonResponse);

            // Guardar la transacción
            Transaction::create([
                'TransactionId' => $jsonResponse['TransactionId'] ?? null,
                'STransactionId' => $jsonResponse['STransactionId'] ?? null,
                'store_id' => $transactionData['store_id'] ?? null,
                'formatted_data' => json_encode($transactionData),
                'response_data' => json_encode($jsonResponse),
            ]);

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

            // Preparar los datos para la consulta
            $queryData = array_merge(
              json_decode($lastTransaction->formatted_data, true) ?? [], // Decodificar a un array
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
}
