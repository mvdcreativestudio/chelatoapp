<?php

namespace App\Services;

use MercadoPago\SDK;
use MercadoPago\Preference;
use MercadoPago\Item;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use stdClass;
use App\Models\Order;

class MercadoPagoService
{
    /**
     * Instancia del cliente.
     *
     * @var Client
    */
    private Client $client;

    /**
     * Constructor para configurar el acceso a la API de MercadoPago.
    */
    public function __construct()
    {
      $this->client = new Client();

      // Configurar el acceso a la API de MercadoPago
      SDK::setAccessToken(config('services.mercadopago.access_token'));
    }

    /**
     * Crea una preferencia de pago en MercadoPago.
     *
     * @param array $preferenceData
     * @param Order $order
     * @return Preference
    */
    public function createPreference(array $preferenceData, Order $order): Preference
    {
      // Crear la preferencia de MercadoPago
      $preference = new Preference();

      // Configurar el pagador
      $payer = new stdClass();
      $payer->email = $preferenceData['payer']['email'];
      $preference->payer = $payer;
      $preference->currency_id = 'UYU';

      // Configurar el campo metadata
      $preference->metadata = [
          'order_id' => $order->id
      ];

      // Configurar los ítems
      $items = [];
      foreach ($preferenceData['items'] as $itemData) {
          $item = new Item();
          $item->title = $itemData['title'];
          $item->description = 'descripcion';
          $item->quantity = $itemData['quantity'];
          $item->unit_price = $itemData['unit_price'];
          $items[] = $item;
      }

      // Aplicar descuento
      $discount = $preferenceData['discount']['amount'] ?? 0;
      if ($discount > 0) {
        $discountItem = new Item();
        $discountItem->title = $preferenceData['discount']['description'];
        $discountItem->quantity = 1;
        $discountItem->unit_price = -$discount;
        $items[] = $discountItem;
      }

      $preference->items = $items;

      // Configurar las URLs de retorno
      $preference->back_urls = [
        "success" => config('services.checkout.return_url') . "/success/{$order->uuid}",
        "failure" => config('services.checkout.return_url') . "/failure/{$order->uuid}",
        "pending" => config('services.checkout.return_url') . "/pending/{$order->uuid}"
      ];
      $preference->auto_return = "all";

      // URL para las notificaciones webhooks
      $preference->notification_url = 'https://chelato.com.uy/api/mpagohook';

      $preference->external_reference = $order->id;

      // Configurar los envíos
      $preference->shipments = (object) [
          'mode' => 'not_specified',
          'cost' => (float) $order->shipping,
      ];

      // Guardar la preferencia y generar el log
      $preference->save();
      Log::info('Preference created:', $preference->toArray());

      return $preference;
    }

    /**
     * Verifica la firma HMAC de una solicitud.
     *
     * @param string $id
     * @param string $requestId
     * @param string $timestamp
     * @param string $receivedHash
     * @param string $secretKey Secret key de la base de datos (requerido)
     * @return bool
     */
    public function verifyHMAC(string $id, string $requestId, string $timestamp, string $receivedHash, string $secretKey): bool
    {
      $message = "id:$id;request-id:$requestId;ts:$timestamp;";
      $generatedHash = hash_hmac('sha256', $message, $secretKey);

      // ⚠️ LOGGING DETALLADO para debugging (sin exponer el hash completo por seguridad)
      Log::info('🔐 Calculando HMAC:', [
          'message' => $message,
          'generated_hash' => substr($generatedHash, 0, 20) . '...' . substr($generatedHash, -10),
          'received_hash' => substr($receivedHash, 0, 20) . '...' . substr($receivedHash, -10),
          'hash_length_generated' => strlen($generatedHash),
          'hash_length_received' => strlen($receivedHash),
          'match' => hash_equals($generatedHash, $receivedHash),
          'secret_key_source' => 'database'
      ]);

      return hash_equals($generatedHash, $receivedHash);
    }

    /**
     * Obtiene la información de un pago desde la API de MercadoPago.
     *
     * @param string $id
     * @param string|null $accessToken Access token específico de la tienda (opcional)
     * @param int $retries Número de reintentos si el pago no está disponible
     * @return array|null
    */
    public function getPaymentInfo(string $id, ?string $accessToken = null, int $retries = 3): ?array
    {
      $token = $accessToken ?? config('services.mercadopago.access_token');
      
      for ($attempt = 0; $attempt <= $retries; $attempt++) {
          try {
              $response = $this->client->request('GET', "https://api.mercadopago.com/v1/payments/{$id}", [
                  'headers' => [
                      'Authorization' => 'Bearer ' . $token,
                  ],
              ]);

              return json_decode($response->getBody(), true);
          } catch (\GuzzleHttp\Exception\ClientException $e) {
              $statusCode = $e->getResponse()->getStatusCode();
              
              // Si es 404 y aún tenemos reintentos, esperar un poco más en cada intento
              if ($statusCode === 404 && $attempt < $retries) {
                  $waitTime = min(3 + ($attempt * 2), 10); // 3s, 5s, 7s, máximo 10s
                  Log::warning("Pago no disponible (404), reintentando en {$waitTime} segundos...", [
                      'payment_id' => $id,
                      'attempt' => $attempt + 1,
                      'max_retries' => $retries,
                      'wait_time' => $waitTime
                  ]);
                  sleep($waitTime);
                  continue;
              }
              
              // Si es 401, no reintentar (es un problema de autenticación)
              if ($statusCode === 401) {
                  Log::error("Error de autenticación (401) al obtener el pago", [
                      'payment_id' => $id,
                      'status_code' => $statusCode
                  ]);
                  return null;
              }
              
              Log::error("Error al obtener la información del pago: " . $e->getMessage(), [
                  'payment_id' => $id,
                  'status_code' => $statusCode,
                  'attempt' => $attempt + 1
              ]);
              return null;
          } catch (\Exception $e) {
              Log::error("Error al obtener la información del pago: " . $e->getMessage(), [
                  'payment_id' => $id,
                  'attempt' => $attempt + 1
              ]);
              return null;
          }
      }
      
      return null;
    }

    /**
     * Obtiene la información de una merchant_order desde la API de MercadoPago.
     *
     * @param string $id ID de la merchant_order
     * @param string|null $accessToken Access token específico de la tienda (opcional)
     * @return array|null
    */
    public function getMerchantOrderInfo(string $id, ?string $accessToken = null): ?array
    {
      $token = $accessToken ?? config('services.mercadopago.access_token');
      
      try {
          $response = $this->client->request('GET', "https://api.mercadolibre.com/merchant_orders/{$id}", [
              'headers' => [
                  'Authorization' => 'Bearer ' . $token,
              ],
          ]);

          return json_decode($response->getBody(), true);
      } catch (\Exception $e) {
          Log::error("Error al obtener la información de merchant_order: " . $e->getMessage(), [
              'merchant_order_id' => $id
          ]);
          return null;
      }
    }

    /**
     * Obtiene el order_id desde el external_reference de una preferencia.
     *
     * @param string $preferenceId ID de la preferencia
     * @param string|null $accessToken Access token específico de la tienda (opcional)
     * @return int|null
    */
    public function getOrderIdFromPreference(string $preferenceId, ?string $accessToken = null): ?int
    {
      $token = $accessToken ?? config('services.mercadopago.access_token');
      
      try {
          $response = $this->client->request('GET', "https://api.mercadopago.com/checkout/preferences/{$preferenceId}", [
              'headers' => [
                  'Authorization' => 'Bearer ' . $token,
              ],
          ]);

          $preference = json_decode($response->getBody(), true);
          
          if (isset($preference['external_reference'])) {
              return (int) $preference['external_reference'];
          }
          
          return null;
      } catch (\Exception $e) {
          Log::error("Error al obtener la preferencia: " . $e->getMessage(), [
              'preference_id' => $preferenceId
          ]);
          return null;
      }
    }

    /**
     * Configura las credenciales de la tienda para acceder a la API de MercadoPago.
     *
     * @param string $publicKey
     * @param string $accessToken
     * @return void
    */
    public function setCredentials(string $publicKey, string $accessToken): void
    {
        SDK::setPublicKey($publicKey);
        SDK::setAccessToken($accessToken);

        Log::info('Credenciales de MercadoPago configuradas:', [
            'public_key' => $publicKey,
            'access_token' => $accessToken
        ]);
    }

}
