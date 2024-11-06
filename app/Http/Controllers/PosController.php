<?php

namespace App\Http\Controllers;

use App\Services\POS\PosService;
use App\Models\PosDevice;
use App\Models\CashRegister;
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

    // Obtener el token de acceso para el proveedor POS
    public function getPosToken(Request $request)
    {
        $storeId = $request->input('store_id');
    
        if (!$storeId) {
            return response()->json(['error' => 'Store ID no proporcionado'], 400);
        }
    
        try {
            Log::info("Obteniendo token para el store ID: " . $storeId);
    
            // Obtener el proveedor POS asociado a la tienda
            $posProvider = $this->posService->getProviderByStoreId($storeId);
    
            if (!$posProvider) {
                Log::error("No se pudo encontrar el proveedor POS para el store ID: " . $storeId);
                return response()->json(['error' => 'No se pudo encontrar el proveedor POS para la tienda'], 500);
            }
    
            // Obtener el token si el proveedor lo requiere
            if ($posProvider->requires_token) {
                $accessToken = $this->posService->getPosToken($storeId);
    
                if (!$accessToken) {
                    Log::error("No se pudo obtener el token para el store ID: " . $storeId);
                    return response()->json(['error' => 'No se pudo obtener el token de acceso para el proveedor POS'], 500);
                }
    
                return response()->json(['access_token' => $accessToken]);
            }
    
            return response()->json(['message' => 'El proveedor POS no requiere token']);
        } catch (\Exception $e) {
            Log::error('Error al obtener el token del POS para el store ID: ' . $storeId . ' - Error: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener el token del POS'], 500);
        }
    }
    



    // Obtener la información del dispositivo POS asociado a la caja registradora
    public function getDeviceInfo($cashRegisterId)
    {
        // Buscar la relación entre la caja registradora y el dispositivo POS
        $posDeviceRelation = \DB::table('cash_register_pos_device')
            ->where('cash_register_id', $cashRegisterId)
            ->first();

        // Verificar si se encontró la relación
        if (!$posDeviceRelation) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró un dispositivo POS vinculado a la caja registradora proporcionada.'
            ], 404);
        }

        // Buscar el dispositivo POS correspondiente en la tabla pos_devices
        $posDevice = PosDevice::find($posDeviceRelation->pos_device_id);

        // Verificar si se encontró el dispositivo POS
        if (!$posDevice) {
            return response()->json([
                'success' => false,
                'message' => 'Dispositivo POS no encontrado.'
            ], 404);
        }

        // Buscar el store_id en la tabla cash_registers
        $cashRegister = \DB::table('cash_registers')
            ->where('id', $cashRegisterId)
            ->first();

        if (!$cashRegister || !isset($cashRegister->store_id)) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró el store_id asociado a la caja registradora.'
            ], 404);
        }

        // Buscar los datos de la integración (company y branch) desde la tabla pos_integrations_store_info usando el store_id
        $posIntegrationInfo = \DB::table('pos_integrations_store_info')
            ->where('store_id', $cashRegister->store_id)
            ->first();

        if (!$posIntegrationInfo) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la información de integración del POS para la tienda.'
            ], 404);
        }

        // Retornar la información del dispositivo POS junto con company y branch desde la tabla pos_integrations_store_info
        return response()->json([
            'success' => true,
            'data' => [
                'identifier' => $posDevice->identifier,
                'company' => $posIntegrationInfo->company,  // Información de la nueva tabla
                'branch' => $posIntegrationInfo->branch,    // Información de la nueva tabla
                'cash_register' => $posDevice->cash_register,
                'user' => $posDevice->user,
            ]
        ], 200);
    }


}
