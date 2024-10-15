<?php

namespace App\Http\Controllers;

use App\Services\POS\PosService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;



class PosController extends Controller
{
    protected $posService;

    public function __construct(PosService $posService)
    {
        $this->posService = $posService;
    }

    // Procesar la transacción para el POS correspondiente
    public function processTransaction(Request $request)
    {
        $transactionData = $request->all();
        $response = $this->posService->processTransaction($transactionData);

        if (isset($response['TransactionId']) && isset($response['STransactionId'])) {
            // Almacenar TransactionId y STransactionId en la sesión o base de datos para futuras consultas
            session()->put('TransactionId', $response['TransactionId']);
            session()->put('STransactionId', $response['STransactionId']);
        }

        return response()->json($response);
    }

    public function checkTransactionStatus(Request $request)
    {
        try {
            $response = $this->posService->checkTransactionStatus($request->all());
            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Error al consultar el estado de la transacción: ' . $e->getMessage());
            return response()->json([
                'responseCode' => 999,
                'message' => 'Error al consultar el estado de la transacción: ' . $e->getMessage(),
                'icon' => 'error',
                'showCloseButton' => true
            ], 500);
        }
    }

    // Obtener las respuestas del POS
    public function getPosResponses()
    {
        // Fetch the responses from your config file or database
        $responses = config('ScanntechResponses.postPurchaseResponses');

        // Ensure you are returning a well-structured JSON response
        return response()->json($responses);
    }


    // Obtener el token del POS dependiendo del proveedor
    public function getPosToken(Request $request)
    {
        $accessToken = $this->posService->getScanntechToken();

        if (!$accessToken) {
            return response()->json(['error' => 'No se pudo obtener el token de acceso para Scanntech'], 500);
        }

        return response()->json(['access_token' => $accessToken]);
    }
}
