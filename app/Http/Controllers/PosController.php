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


    /**
     * Procesar la transacción seleccionando el proveedor POS dinámicamente
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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
      Log::info('Ingresando a checkTransactionStatus en PosController' . $request);
        try {
            // Validar la entrada
            $validated = $request->validate([
                'TransactionId' => 'required',
                'STransactionId' => 'required',
                'store_id' => 'required|integer', // Asegúrate de que esté definido
            ]);

            // Llamar al servicio con los datos validados
            $response = $this->posService->checkTransactionStatus($validated);

            return response()->json($response);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Manejar errores de validación
            Log::error('Error de validación en checkTransactionStatus: ' . json_encode($e->errors()));
            return response()->json([
                'responseCode' => 400,
                'message' => 'Datos inválidos: ' . $e->getMessage(),
                'icon' => 'error',
                'showCloseButton' => true
            ], 400);
        } catch (\Exception $e) {
            // Manejar errores generales
            Log::error('Error al consultar el estado de la transacción: ' . $e->getMessage());
            return response()->json([
                'responseCode' => 999,
                'message' => 'Error al consultar el estado de la transacción: ' . $e->getMessage(),
                'icon' => 'error',
                'showCloseButton' => true
            ], 500);
        }
    }


    /**
     * Obtener las respuestas de la compra post
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
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




    /**
     * Obtener la información del dispositivo POS
     *
     * @param int $cashRegisterId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
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

    /**
     * Sincronizar las terminales POS
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function sync(Request $request)
    {
        try {
            // Validar los datos de entrada
            $request->validate([
                'terminals' => 'required|array',
                'terminals.*.name' => 'required|string|max:255',
                'terminals.*.identifier' => 'required|string|max:255',
                'terminals.*.user' => 'nullable|string|max:255',
                'terminals.*.cash_register' => 'nullable|string|max:255',
                'terminals.*.pos_provider_id' => 'required|integer|exists:pos_providers,id', // Validar que exista
            ]);

            $terminals = $request->input('terminals', []);

            foreach ($terminals as $terminal) {
                if (isset($terminal['id']) && $terminal['id']) {
                    // Actualizar terminal existente
                    PosDevice::where('id', $terminal['id'])->update([
                        'name' => $terminal['name'],
                        'identifier' => $terminal['identifier'],
                        'user' => $terminal['user'],
                        'cash_register' => $terminal['cash_register'],
                        'pos_provider_id' => $terminal['pos_provider_id'], // Asegurarte de incluir esto
                    ]);
                } else {
                    // Crear nueva terminal
                    PosDevice::create([
                        'name' => $terminal['name'],
                        'identifier' => $terminal['identifier'],
                        'user' => $terminal['user'],
                        'cash_register' => $terminal['cash_register'],
                        'pos_provider_id' => $terminal['pos_provider_id'], // Asegurarte de incluir esto
                    ]);
                }
            }

            return response()->json(['success' => true]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Error de validación al sincronizar terminales: ' . json_encode($e->errors()));
            return response()->json(['success' => false, 'message' => 'Datos inválidos.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Error al sincronizar terminales: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error inesperado: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar una terminal POS
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function delete($id)
    {
        try {
            $device = PosDevice::findOrFail($id);
            $device->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Error al eliminar el terminal POS: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar el terminal.'], 500);
        }
    }


}
