<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmailConfigRequest;
use App\Repositories\StoresEmailConfigRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StoresEmailConfigController extends Controller
{
    protected StoresEmailConfigRepository $emailConfigRepository;

    public function __construct(StoresEmailConfigRepository $emailConfigRepository)
    {
        $this->emailConfigRepository = $emailConfigRepository;
    }

    /**
     * Muestra la configuración de correo para una tienda específica.
     *
     * @param int $storeId
     * @return JsonResponse
     */
    public function show(int $storeId): JsonResponse
    {
        $config = $this->emailConfigRepository->getConfigByStoreId($storeId);
        return response()->json($config);
    }

    /**
     * Guarda o actualiza la configuración de correo para una tienda.
     *
     * @param StoreEmailConfigRequest $request
     * @param int $storeId
     * @return JsonResponse
     */
    public function storeOrUpdate(Request $request, int $storeId): JsonResponse
    {
        try {
            $config = $this->emailConfigRepository->saveConfig($storeId, $request->validated());
            return response()->json(['success' => true, 'data' => $config], 200);
        } catch (\Exception $e) {
            Log::error("Error al guardar la configuración de correo para la tienda ID {$storeId}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al guardar la configuración de correo.'], 400);
        }
    }

    /**
     * Elimina la configuración de correo para una tienda específica.
     *
     * @param int $storeId
     * @return JsonResponse
     */
    public function destroy(int $storeId): JsonResponse
    {
        try {
            $this->emailConfigRepository->deleteConfig($storeId);
            return response()->json(['success' => true, 'message' => 'Configuración eliminada correctamente.']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            Log::error("Error al eliminar la configuración de correo para la tienda ID {$storeId}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar la configuración de correo.'], 400);
        }
    }
}
