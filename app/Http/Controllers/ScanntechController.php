<?php

namespace App\Http\Controllers;

use App\Services\ScanntechAuthService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;


class ScanntechController extends Controller
{
    protected $scanntechAuthService;
    protected $scanntechResponses;

    public function __construct(ScanntechAuthService $scanntechAuthService)
    {
        $this->scanntechAuthService = $scanntechAuthService;

        // Cargar respuestas desde el archivo JSON en storage/config
        $this->loadScanntechResponses();
    }

    /**
     * Carga los códigos de respuesta desde el archivo de configuración ubicado en config/posResponses/scanntech_responses.php.
     */
    private function loadScanntechResponses()
    {
        try {
            // Intenta cargar las respuestas desde el archivo de configuración
            $this->scanntechResponses = config('posResponses.scanntech_responses');

            // Verifica si la carga falló o no es un array
            if (!is_array($this->scanntechResponses)) {
                \Log::warning('El archivo de configuración scanntech_responses.php no tiene un formato válido.');
                $this->scanntechResponses = [];
            }
        } catch (\Exception $e) {
            // Manejo de errores en caso de que la carga falle
            \Log::error('Error al cargar las respuestas de Scanntech desde la configuración: ' . $e->getMessage());
            $this->scanntechResponses = [];
        }
    }


    public function getToken()
    {
        // Obtener el token de acceso
        $accessToken = $this->scanntechAuthService->getAccessToken();

        if (!$accessToken) {
            return response()->json(['error' => 'No se pudo obtener el token de acceso'], 500);
        }

        return response()->json(['access_token' => $accessToken]);
    }

    public function postPurchase(Request $request)
    {
        // Validar los datos recibidos desde el frontend
        $validated = $request->validate([
            'PosID' => 'required|string',
            'Empresa' => 'required|string',
            'Local' => 'required|string',
            'Caja' => 'required|string',
            'UserId' => 'required|string',
            'TransactionDateTimeyyyyMMddHHmmssSSS' => 'required|string',
            'Amount' => 'required|string',
            'Quotas' => 'required|numeric',
            'Plan' => 'required|integer',
            'Currency' => 'required|string',
            'TaxableAmount' => 'required|string',
            'InvoiceAmount' => 'required|string',
            'TaxAmount' => 'required|string',
            'IVAAmount' => 'required|string',
            'NeedToReadCard' => 'required|boolean',
        ]);

        // Realiza la solicitud a Scanntech utilizando los datos recibidos
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->scanntechAuthService->getAccessToken(),
            'Content-Type' => 'application/json',
        ])->post('http://200.40.123.21:35000/rest/v2/postPurchase', $validated);

        // Manejo de la respuesta
        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json(['error' => 'Error en la conexión con Scanntech'], 500);
    }

    // Ejemplo de cómo obtener el mensaje desde la configuración y enviarlo al frontend
    public function getTransactionState(Request $request)
    {
        // Obtener los datos directamente sin validación
        $data = $request->all();

        // Registrar los datos para depuración (puedes eliminar esto en producción)
        \Log::info('Datos enviados a Scanntech:', $data);

        // Realiza la solicitud a Scanntech utilizando los datos recibidos
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->scanntechAuthService->getAccessToken(),
            'Content-Type' => 'application/json',
        ])->post('http://200.40.123.21:35000/rest/v2/getTransactionState', $data);

        // Manejo de la respuesta
        if ($response->successful()) {
            $responseCode = $response->json('ResponseCode');

            // Obtener el mensaje basado en el código de respuesta desde la configuración
            $message = $this->scanntechResponses[$responseCode] ?? 'Código de respuesta no manejado: ' . $responseCode;

            // Retornar el mensaje al frontend
            return response()->json(['message' => $message, 'responseCode' => $responseCode]);
        }

        // Capturar errores en los logs si la respuesta no es exitosa
        \Log::error('Error en la conexión con Scanntech:', ['response' => $response->body()]);

        return response()->json(['error' => 'Error en la conexión con Scanntech'], 500);
    }

    public function getScanntechResponses(): JsonResponse
    {
        // Carga la configuración desde el archivo especificado
        $responses = config('ScanntechResponses.postPurchaseResponses');

        // Devuelve la configuración en formato JSON
        return response()->json($responses);
    }
}
