<?php

namespace App\Http\Controllers;

use App\Repositories\MercadoPagoRepository;
use App\Services\MercadoPagoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MercadoPagoController extends Controller
{
    /**
     * El servicio de MercadoPago.
     *
     * @var MercadoPagoService
     */
    protected $mpService;

    /**
     * El repositorio de MercadoPago.
     *
     * @var MercadoPagoRepository
     */
    protected $mercadopagorepo;

    /**
     * Inyecta el repositorio y el servicio en el controlador.
     *
     * @param MercadoPagoRepository $mercadopagorepo
     * @param MercadoPagoService $mpService
     */
    public function __construct(MercadoPagoRepository $mercadopagorepo, MercadoPagoService $mpService)
    {
        $this->mercadopagorepo = $mercadopagorepo;
        $this->mpService = $mpService;
    }

    /**
     * Maneja las notificaciones webhook de MercadoPago.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function webhooks(Request $request): JsonResponse
    {
        // Log de datos recibidos
        Log::info('Datos recibidos de MercadoPago:', [
            'headers' => $request->header(),
            'body' => $request->all(),
        ]);

        try {
            // Procesar el webhook
            $result = $this->mercadopagorepo->handleWebhook($request, $this->mpService);

            // Confirmar recepción con un 200
            Log::info('Webhook de MercadoPago procesado correctamente:', [
                'result' => $result,
                'status' => $result['status'],
            ]);
            return response()->json(['message' => 'ACK'], 200);
        } catch (\Exception $e) {
            // Loguear el error y retornar una respuesta genérica
            Log::error('Error procesando webhook de MercadoPago:', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Error procesando webhook'], 500);
        }
    }

}
