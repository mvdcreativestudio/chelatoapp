<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Store;

class PedidosYaRepository
{
  /**
   * Obtiene la clave de la API de PedidosYa para una tienda.
   *
   * @param  int  $storeId
   * @return string|null
   */
  private function getApiKeyForStore(int $storeId): ?string
  {
      $store = Store::find($storeId);

      return $store ? $store->peya_envios_key : null;
  }


  /**
   * Calcula el costo de envío de un pedido y si es posible realizarlo.
   *
   * @param  Request  $request
   * @return array
  */
  public function estimateOrderRequest(Request $request): array {
    $url = 'https://courier-api.pedidosya.com/v3/shippings/estimates';
    $apiKey = $this->getApiKeyForStore($request->store_id);

    Log::info('Estimating order for store ' . $request->store_id);
    Log::info('API key: ' . $apiKey); // Confirmar que la API key es correcta

    if (!$apiKey) {
        Log::error('API key not found for store ID: ' . $request->store_id); // Añadir este log
        return ['error' => 'API key not found for the store'];
    }

    // Realizar la solicitud a Pedidos Ya
    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $apiKey,
        'Content-Type' => 'application/json',
    ])->post($url, $request->all());

    Log::info('PedidosYa Response: ', $response->json()); // Añadir este log para ver la respuesta

    return $response->json();
}


  /**
   * Confirma una estimación de pedido y lo envía a la API de PedidosYa.
   *
   * @param  Request  $request
   * @return array
   */
  public function confirmOrderRequest(Request $request): array
    {
        $url = 'https://courier-api.pedidosya.com/v3/shippings/estimates/' . $request->estimate_id . '/confirm';
        $apiKey = $this->getApiKeyForStore($request->store_id);

        if (!$apiKey) {
            return ['error' => 'API key not found for the store'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
                'User-Agent' => 'PedidosYa MVD Studio Client'
            ])->post($url);

            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error('PedidosYa API error', [
                    'url' => $url,
                    'response' => $response->body(),
                    'status' => $response->status()
                ]);
                return [
                    'error' => 'API request failed',
                    'status' => $response->status(),
                    'body' => $response->body()
                ];
            }
        } catch (\Exception $e) {
            Log::error('PedidosYa API exception', [
                'url' => $url,
                'message' => $e->getMessage()
            ]);
            return [
                'error' => 'Exception occurred',
                'message' => $e->getMessage()
            ];
        }
  }


}
