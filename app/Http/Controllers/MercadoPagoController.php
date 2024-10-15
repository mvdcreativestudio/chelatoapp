<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\MercadoPagoService;
use App\Repositories\MercadoPagoRepository;
use Illuminate\Http\JsonResponse;

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
      Log::info('Datos recibidos de MercadoPago:', [
          'headers' => $request->header(),
          'body' => $request->all(),
      ]);

      $result = $this->mercadopagorepo->handleWebhook($request, $this->mpService);
      return response()->json($result['message'], $result['status']);
    }
}
