<?php

namespace App\Services\POS;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class ScanntechIntegrationService implements PosIntegrationInterface
{
    protected $authService;

    public function __construct(ScanntechAuthService $authService)
    {
        $this->authService = $authService;
    }

    public function getToken()
    {
        return $this->authService->getAccessToken();
    }

    public function processTransaction(array $transactionData): array
    {
        $token = $this->authService->getAccessToken();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ])->post('http://200.40.123.21:35000/rest/v2/postPurchase', $transactionData);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Error al procesar la transacción con Scanntech: ' . $response->body());
        return [
            'success' => false,
            'message' => 'Error al procesar la transacción con Scanntech'
        ];
    }

    public function checkTransactionStatus(array $transactionData): array
    {
        $token = $this->authService->getAccessToken();

        try {
            Log::info('Enviando solicitud de estado de transacción a Scanntech', $transactionData);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->post('http://200.40.123.21:35000/rest/v2/getTransactionState', $transactionData);

            Log::info('Respuesta de Scanntech', [
                'status_code' => $response->status(),
                'response_body' => $response->body(),
            ]);

            if ($response->successful()) {
                $jsonResponse = $response->json();
                $responseCode = $jsonResponse['ResponseCode'] ?? null;

                Log::info('Código de respuesta recibido: ' . $responseCode);

                return [
                    'responseCode' => $responseCode,
                    'details' => $jsonResponse
                ];
            } else {
                Log::error('Error al consultar el estado de la transacción en Scanntech: ' . $response->body());
                return [
                    'responseCode' => $response->status(),
                    'message' => 'Error al consultar el estado de la transacción'
                ];
            }
        } catch (\Exception $e) {
            Log::error('Excepción al consultar el estado de la transacción: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return [
                'responseCode' => 999,
                'message' => 'Error al consultar el estado de la transacción: ' . $e->getMessage()
            ];
        }
    }

    public function getResponses($responseCode)
    {
        $responses = Config::get('ScanntechResponses.postPurchaseResponses');
        $responseCode = (int)$responseCode; // Convertir a entero
        Log::info('Buscando respuesta para el código: ' . $responseCode);
        Log::info('Configuración completa de respuestas:', $responses);

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