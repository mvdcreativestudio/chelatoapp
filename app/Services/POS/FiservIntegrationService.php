<?php

namespace App\Services\POS;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use App\Models\PosProvider;

class FiservIntegrationService implements PosIntegrationInterface
{
    protected $apiUrl;  // Definir la propiedad para la URL de la API

    public function __construct()
    {
        $this->apiUrl = $this->getFiservApiUrl();  // Asignar la URL de la API cuando se crea la instancia
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
        // Usar la URL obtenida desde la tabla pos_providers
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($this->apiUrl . 'processFinancialPurchase', $transactionData);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Error al procesar la transacción con Fiserv: ' . $response->body());
        return [
            'success' => false,
            'message' => 'Error al procesar la transacción con Fiserv'
        ];
    }

    public function checkTransactionStatus(array $transactionData): array
    {
        return null;
    }

    public function getResponses($responseCode)
    {
        return null;
    }
}
