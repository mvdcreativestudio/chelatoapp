<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\PedidosYaRepository;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PedidosYaController extends Controller
{
  /**
   * El repositorio para las operaciones de PedidosYa.
   *
   * @var PedidosYaRepository
  */
  protected $pedidosYaRepository;

  /**
   * Inyecta el repositorio en el controlador.
   *
   * @param  PedidosYaRepository  $pedidosYaRepository
  */
  public function __construct(PedidosYaRepository $pedidosYaRepository)
  {
      $this->pedidosYaRepository = $pedidosYaRepository;
  }

  /**
   * Obtiene la clave de la API de PedidosYa para una empresa.
   *
   * @param  int  $store_id
   * @return JsonResponse
   */
  public function getApiKey($store_id)
  {
      $store = Store::find($store_id);
      if ($store) {
          return response()->json(['api_key' => $store->peya_envios_key], 200);
      }

      return response()->json(['error' => 'Store not found'], 404);
  }



  /**
   * Calcula el costo de envío de un pedido y si es posible realizarlo.
   *
   * @param  Request  $request
   * @return JsonResponse
  */
  public function estimateOrder(Request $request): JsonResponse
  {
      $validatedData = $request->validate([
          'store_id' => 'required|exists:stores,id',
      ]);

      Log::info('Estimating order for store ' . $request->store_id);

      $estimatedOrderResponse = $this->pedidosYaRepository->estimateOrderRequest($request);

      return response()->json($estimatedOrderResponse)
          ->header('Content-Type', 'application/json')
          ->header('Access-Control-Allow-Origin', '*');
  }


  /**
    * Confirma una estimación de pedido y lo envía a la API de PedidosYa.
    *
    * @param  Request  $request
    * @return JsonResponse
  */
  public function confirmOrder(Request $request): JsonResponse
  {
    $confirmedOrderResponse = $this->pedidosYaRepository->confirmOrderRequest($request);

    return response()->json($confirmedOrderResponse)
      ->header('Content-Type', 'application/json')
      ->header('Access-Control-Allow-Origin', '*');
  }
}
