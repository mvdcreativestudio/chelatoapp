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
     * La clave secreta de la tienda en MercadoPago.
     *
     * @var string
    */
    private string $secretKey;

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
      // Cargar la secret key desde la configuración
      $this->secretKey = config('services.mercadopago.secret_key');
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
      $preference->items = $items;

      // Configurar las URLs de retorno
      $preference->back_urls = [
          "success" => "https://chelato.test/checkout/success/{$order->id}",
          "failure" => "https://chelato.test/checkout/failure/{$order->id}",
          "pending" => "https://chelato.test/checkout/pending/{$order->id}"
      ];
      $preference->auto_return = "all";

      // URL para las notificaciones webhooks
      $preference->notification_url = 'https://cce5-2800-a4-1650-ad00-b5d7-6e7-6d71-b1ac.ngrok-free.app/api/mpagohook?source_news=webhooks';

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
     * @return bool
    */
    public function verifyHMAC(string $id, string $requestId, string $timestamp, string $receivedHash): bool
    {
      $message = "id:$id;request-id:$requestId;ts:$timestamp;";
      $generatedHash = hash_hmac('sha256', $message, $this->secretKey);

      return hash_equals($generatedHash, $receivedHash);
    }

    /**
     * Obtiene la información de un pago desde la API de MercadoPago.
     *
     * @param string $id
     * @return array|null
    */
    public function getPaymentInfo(string $id): ?array
    {
      try {
          $response = $this->client->request('GET', "https://api.mercadopago.com/v1/payments/{$id}", [
              'headers' => [
                  'Authorization' => 'Bearer ' . config('services.mercadopago.access_token'),
              ],
          ]);

          return json_decode($response->getBody(), true);
      } catch (\Exception $e) {
          Log::error("Error al obtener la información del pago: " . $e->getMessage());
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
    }
}
