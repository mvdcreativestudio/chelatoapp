<?php

namespace App\Services\POS;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
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
        // Obtener el pos_provider con el id 1 (que corresponde a Scanntech)
        $posProvider = PosProvider::find(1); // Scanntech tiene el ID 1

        if ($posProvider && $posProvider->api_url) {
            Log::info('URL de la API de Scanntech encontrada: ' . $posProvider->api_url);
            return $posProvider->api_url;
        } else {
            // Registrar un error si no se encuentra el proveedor o no tiene URL definida
            Log::error('No se encontró la URL de la API para el proveedor Scanntech.');
            throw new \Exception('No se pudo encontrar la URL de la API para Scanntech');
        }
    }

    public function getToken()
    {
        return $this->authService->getAccessToken();
    }

    public function processTransaction(array $transactionData): array
    {
        $token = $this->authService->getAccessToken();

        // Usar la URL obtenida desde la tabla pos_providers
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ])->post($this->apiUrl . 'postPurchase', $transactionData);

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

            // Usar la URL obtenida desde la tabla pos_providers
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . 'getTransactionState', $transactionData);

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
