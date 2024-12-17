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
   * @param  int  $store_id
   * @return string|null
   */
  private function getApiKeyForStore(int $store_id): ?string
  {
      if (!$store_id) {
          Log::error('El store_id proporcionado es inválido o está vacío.');
          return null;
      }

      $store = Store::find($store_id);

      if (!$store) {
          Log::error("No se encontró una tienda asociada al store_id {$store_id}.");
          return null;
      }

      return $store->peya_envios_key;
  }



  /**
   * Calcula el costo de envío de un pedido y si es posible realizarlo.
   *
   * @param  Request  $request
   * @return array
  */
  public function estimateOrderRequest(Request $request): array {
    $url = 'https://courier-api.pedidosya.com/v3/shippings/estimates';
    Log::info('Request recibida en estimateOrderRequest', $request->all());
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
    Log::info('Contenido de Request en confirmOrderRequest:', $request->all());

    $url = 'https://courier-api.pedidosya.com/v3/shippings/estimates/' . $request->estimate_id . '/confirm';

    // Convertir store_id a entero
    $storeId = (int) $request->store_id;
    Log::info('Confirming order for store ' . $storeId);

    $apiKey = $this->getApiKeyForStore($storeId);
    Log::info('API key: ' . $apiKey);

    if (!$apiKey) {
        return ['error' => 'API key not found for the store'];
    }

    // Construir el cuerpo del request
    $body = [
        'deliveryOfferId' => $request->delivery_offer_id,
    ];

    // Encabezados de la solicitud
    $headers = [
        'Authorization' => $apiKey,
        'Content-Type' => 'application/json',
        'User-Agent' => 'PedidosYa MVD Studio Client'
    ];

    // Log para capturar los headers
    Log::info('Headers de la solicitud a PedidosYa:', $headers);

    try {
        $response = Http::withHeaders($headers)
            ->post($url, $body); // Enviar el cuerpo con delivery_offer_id

        if ($response->successful()) {
            Log::info('Respuesta exitosa de PedidosYa:', $response->json());
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
