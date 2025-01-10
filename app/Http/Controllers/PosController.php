<?php

namespace App\Http\Controllers;

use App\Services\POS\PosService;
use App\Models\PosDevice;
use App\Models\PosProvider;
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

    /**
     * Reversar una transacción.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reverseTransaction(Request $request)
    {
        try {
            // Validar la entrada del cliente
            $validated = $request->validate([
                'TransactionId' => 'required|string', // ID de transacción
                'STransactionId' => 'required|string', // ID secundario de transacción
                'store_id' => 'required|integer', // ID de la tienda
            ]);

            // Log para depuración
            Log::info('Iniciando reverseTransaction con los datos:', $validated);

            // Llamar al servicio POS para realizar el reverso
            $response = $this->posService->reverseTransaction($validated);

            if ($response['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $response['message'],
                    'details' => $response['details'],
                ], 200);
            }

            // En caso de error en el reverso
            return response()->json([
                'success' => false,
                'message' => $response['message'],
                'details' => $response['details'],
            ], 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Manejar errores de validación
            Log::error('Error de validación en reverseTransaction: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos: ' . $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Manejar errores generales
            Log::error('Error al reversar la transacción: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al realizar el reverso de la transacción.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function voidTransaction(Request $request)
{
    try {
        $validated = $request->validate([
            'store_id' => 'required|integer|exists:stores,id',
            'pos_device_id' => 'required|integer|exists:pos_devices,id',
            'PosID' => 'required|string',
            'SystemId' => 'required|string',
            'Branch' => 'required|string',
            'ClientAppId' => 'required|string',
            'UserId' => 'required|string',
            'TransactionDateTimeyyyyMMddHHmmssSSS' => 'required|string|size:20',
            'TicketNumber' => 'required|string',
            'order_id' => 'nullable|integer|exists:orders,id', // Permitir que sea opcional
        ]);

        // Si no viene el order_id en el request, buscarlo en la transacción original
        if (!isset($validated['order_id']) || is_null($validated['order_id'])) {
          // Buscar la transacción pendiente por TicketNumber
          $originalTransaction = \App\Models\Transaction::where('STransactionId', $validated['STransactionId'])
              ->where('status', 'pending')
              ->first();

          if (!$originalTransaction || !$originalTransaction->order_id) {
              Log::error('No se encontró una transacción pendiente para el TicketNumber proporcionado.');
              throw new \Exception('No se encontró una transacción válida para el número de ticket proporcionado.');
          }

          // Asignar el order_id encontrado a los datos validados
          $validated['order_id'] = $originalTransaction->order_id;
        }


        Log::info('Datos validados para voidTransaction:', $validated);

        // Llamar al servicio POS
        $response = $this->posService->voidTransaction($validated);

        return response()->json($response);

    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error('Error de validación en voidTransaction: ' . json_encode($e->errors()));
        return response()->json([
            'success' => false,
            'message' => 'Datos inválidos: ' . $e->getMessage(),
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        Log::error('Error durante voidTransaction: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error al procesar la anulación.',
            'details' => $e->getMessage(),
        ], 500);
    }
}



    public function pollVoidStatus(Request $request)
{
    try {
        // Validar los datos iniciales
        $validated = $request->validate([
            'TransactionId' => 'required|integer', // Validar como entero porque viene de la base de datos
            'STransactionId' => 'required|string',
        ]);

        // Buscar la transacción correspondiente
        $transaction = \App\Models\Transaction::where('TransactionId', $validated['TransactionId'])
            ->where('STransactionId', $validated['STransactionId'])
            ->first();

        if (!$transaction || !$transaction->formatted_data) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron datos formateados para la transacción.',
            ], 404);
        }

        // Obtener el order_id de la transacción
        $orderId = $transaction->order_id;
        if (!$orderId) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró el order_id relacionado con esta transacción.',
            ], 404);
        }

        // Obtener el store_id desde la tabla orders
        $order = \App\Models\Order::find($orderId);
        if (!$order || !$order->store_id) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró el store_id relacionado con esta transacción.',
            ], 404);
        }

        $storeId = $order->store_id;

        // Decodificar los datos formateados para el cuerpo de la solicitud
        $pollData = $transaction->formatted_data;

        // Verificar si es una cadena JSON y decodificarla si es necesario
        if (is_string($pollData)) {
            $pollData = json_decode($pollData, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Error al decodificar formatted_data: ' . json_last_error_msg());
                return response()->json([
                    'success' => false,
                    'message' => 'Los datos formateados no están en un formato válido (JSON).',
                ], 500);
            }
        }

        // Si no es un array después de la decodificación, retornar un error
        if (!is_array($pollData)) {
            Log::error('formatted_data no es un array después de la decodificación.');
            return response()->json([
                'success' => false,
                'message' => 'Los datos formateados no están en el formato esperado.',
            ], 500);
        }

        if (!$pollData) {
            return response()->json([
                'success' => false,
                'message' => 'Los datos formateados no están en un formato válido.',
            ], 500);
        }

        // Asegurarnos de que TransactionId siga siendo un entero y STransactionId un string
        $pollData['TransactionId'] = (int) $pollData['TransactionId']; // Asegurar entero
        $pollData['STransactionId'] = (string) $pollData['STransactionId']; // Asegurar string
        $pollData['store_id'] = $storeId; // Agregar el store_id al cuerpo de la solicitud

        Log::info('Iniciando pollVoidStatus desde PosController con datos formateados:', $pollData);

        // Enviar la solicitud al servicio POS
        $response = $this->posService->pollVoidStatus($pollData);

        return response()->json($response);
    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error('Error de validación en pollVoidStatus: ' . json_encode($e->errors()));
        return response()->json([
            'success' => false,
            'message' => 'Datos inválidos: ' . $e->getMessage(),
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        Log::error('Error en pollVoidStatus: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error al realizar la consulta.',
            'details' => $e->getMessage(),
        ], 500);
    }
}



    public function getPosDevices(Request $request)
    {
        try {
            $storeId = $request->input('store_id');

            // Obtener dispositivos POS asociados al store_id a través de cash_registers
            $devices = PosDevice::whereHas('cashRegisters', function ($query) use ($storeId) {
                $query->where('store_id', $storeId);
            })
            ->with('provider') // Traer información del proveedor
            ->get();

            return response()->json([
                'success' => true,
                'devices' => $devices,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al obtener dispositivos POS: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener dispositivos POS.'], 500);
        }
    }

    public function fetchTransactionHistory(Request $request)
    {
        try {
            // Validar los datos recibidos del request
            $validated = $request->validate([
                'from_date' => 'required|date_format:YmdHis',
                'to_date' => 'required|date_format:YmdHis|after:from_date',
                'pos_device_id' => 'required|integer|exists:pos_devices,id',
                'only_confirmed' => 'required|boolean',
                'store_id' => 'required|integer|exists:stores,id', // Validar el store_id
            ]);

            // Llamar al servicio POS para obtener las transacciones
            $transactions = $this->posService->fetchTransactionHistory($validated);

            return response()->json([
                'success' => true,
                'data' => $transactions,
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación en fetchTransactionHistory: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos: ' . $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error al obtener el histórico de transacciones: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al consultar las transacciones.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }



    public function getDevicesByStore($storeId)
    {
        // Obtener la información de integración basada en el store_id
        $integrationInfo = \DB::table('pos_integrations_store_info')
            ->where('store_id', $storeId)
            ->first();

        // Validar que exista información de integración
        if (!$integrationInfo) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró información de integración para la tienda seleccionada.'
            ], 404);
        }

        // Obtener los dispositivos POS asociados al pos_provider_id de la integración
        $devices = \DB::table('pos_devices')
            ->where('pos_provider_id', $integrationInfo->pos_provider_id)
            ->get();

        // Validar que existan dispositivos asociados
        if ($devices->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron dispositivos POS para el proveedor asociado a esta tienda.'
            ], 404);
        }

        // Formatear la respuesta incluyendo información relevante de pos_providers
        $response = $devices->map(function ($device) use ($integrationInfo) {
            return [
                'id' => $device->id,
                'name' => $device->name,
                'identifier' => $device->identifier,
                'user' => $device->user,
                'cash_register' => $device->cash_register,
                'provider' => \DB::table('pos_providers')
                    ->where('id', $device->pos_provider_id)
                    ->select('name', 'requires_token', 'api_url')
                    ->first(),
                'integration' => [
                    'company' => $integrationInfo->company,
                    'branch' => $integrationInfo->branch,
                    'system_id' => $integrationInfo->system_id,
                ],
            ];
        });

        return response()->json(['success' => true, 'devices' => $response]);
    }



}
