<?php

namespace App\Services;

use App\Enums\MercadoPago\MercadoPagoApplicationTypeEnum;
use App\Exceptions\MercadoPagoException;
use App\Models\MercadoPagoAccount;
use App\Models\Order;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use MercadoPago\Item;
use MercadoPago\Preference;
use MercadoPago\SDK;
use Illuminate\Support\Str;
use stdClass;

class MercadoPagoService
{

    /**
     * La clave pública de la tienda en MercadoPago.
     *
     * @var string
     */

    private string $publicKey;

    /**
     * El token de acceso de la tienda en MercadoPago.
     *
     * @var string
     */

    private string $accessToken;

    /**
     * La clave secreta de la tienda en MercadoPago.
     *
     * @var string
     */
    private ?string $secretKey;

    /**
     * El ID de usuario de la tienda en MercadoPago.
     *
     * @var string
     */
    private ?string $userIdMP;

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
        // Cargar la secret key desde la configuración
        // $this->secretKey = 'b28e634161009b18f3c44a4eee3639867a1dca05464234b2da2ceb6aec5bb6f3';

        // Configurar el acceso a la API de MercadoPago
        // SDK::setAccessToken(config('services.mercadopago.access_token'));
    }

    /**
     * Configura las credenciales de la tienda para acceder a la API de MercadoPago.
     *
     * @param string $publicKey
     * @param string $accessToken
     * @return void
     */
    public function setCredentials(string $storeId, string $type = MercadoPagoApplicationTypeEnum::PAID_ONLINE->value): void
    {
        try {
            $mercadoPagoAccount = MercadoPagoAccount::where('store_id', $storeId)->where('type', $type)->first();
            if (!$mercadoPagoAccount) {
                throw new \Exception('No se encontraron las credenciales de MercadoPago para la tienda asociada al pedido.');
            }
            $this->publicKey = $mercadoPagoAccount->public_key;
            $this->accessToken = $mercadoPagoAccount->access_token;
            $this->secretKey = $mercadoPagoAccount->secret_key;
            $this->userIdMP = $mercadoPagoAccount->user_id_mp;
            SDK::initialize();
            SDK::setPublicKey($this->publicKey);
            SDK::setAccessToken($this->accessToken);
            Log::info('Credenciales de MercadoPago configuradas:', [
                'public_key' => $this->publicKey,
                'access_token' => $this->accessToken,
                'secret_key' => $this->secretKey,
            ]);
        } catch (\Exception $e) {
            Log::error("Error al configurar las credenciales de la tienda: {$e->getMessage()}");

            throw $e;
        }
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
            'order_id' => $order->id,
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
            "pending" => config('services.checkout.return_url') . "/pending/{$order->uuid}",
        ];
        $preference->auto_return = "all";

        // URL para las notificaciones webhooks
        $preference->notification_url = 'https://cd65-2800-a4-1756-6f00-61bd-b9eb-ccc6-7b8e.ngrok-free.app/api/mpagohook?source_news=webhooks';

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
    public function verifyHMAC(string $id, string $requestId, string $timestamp, string $receivedHash, string $secretKey): bool
    {
        $message = "id:$id;request-id:$requestId;ts:$timestamp;";
        $generatedHash = hash_hmac('sha256', $message, $secretKey);

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

    // QR

    private function makeRequest(string $method, string $url, array $headers = [], array $body = [], array $queryParams = []): array
    {
        try {
            // Agregar parámetros de consulta a la URL si están presentes
            if (!empty($queryParams)) {
                $url .= '?' . http_build_query($queryParams);
            }

            $options = [
                'headers' => $headers,
            ];

            // Solo agregar el cuerpo para métodos POST y PUT
            if (in_array($method, ['POST', 'PUT'])) {
                $options['json'] = $body;
            }

            $response = $this->client->request($method, $url, $options);

            $responseBody = $response->getBody()->getContents();

            // Decodificar la respuesta JSON
            $decodedResponse = json_decode($responseBody, true);
            if ($decodedResponse === null) {
                return [];
            }
            return $decodedResponse ?? []; // Devuelve un array vacío si no hay datos
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // Extraer el cuerpo completo de la respuesta
            $responseBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null;

            Log::error("Error en la solicitud a MercadoPago: {$e->getMessage()}");
            Log::error("URL: {$url}");
            Log::error("Headers: " . json_encode($headers));
            Log::error("Body: " . json_encode($body));
            Log::error("Query Params: " . json_encode($queryParams));
            Log::error("Response Body: {$responseBody}");

            throw new MercadoPagoException($e->getMessage(), json_decode($responseBody, true));
        } catch (\Exception $e) {
            Log::error("Error en la solicitud a MercadoPago: {$e->getMessage()}");
            Log::error("URL: {$url}");
            Log::error("Headers: " . json_encode($headers));
            Log::error("Body: " . json_encode($body));
            Log::error("Query Params: " . json_encode($queryParams));

            throw $e;
        }
    }
    // Obtener las cabeceras estándar para las solicitudes
    private function getHeaders(): array
    {
        return [
            'Authorization' => "Bearer {$this->accessToken}",
            'Content-Type' => 'application/json',
        ];
    }

    // Métodos relacionados con Sucursales
    public function searchStores(array $queryParams = []): array
    {
        $url = "https://api.mercadopago.com/users/{$this->userIdMP}/stores/search";
        $headers = $this->getHeaders();
        return $this->makeRequest('GET', $url, $headers, [], $queryParams);
    }

    public function getStore(string $storeId): array
    {
        $url = "https://api.mercadopago.com/stores/{$storeId}";
        $headers = $this->getHeaders();
        return $this->makeRequest('GET', $url, $headers);
    }

    public function createStore(array $data): array
    {
        $url = "https://api.mercadopago.com/users/{$this->userIdMP}/stores";
        $headers = $this->getHeaders();
        return $this->makeRequest('POST', $url, $headers, $data);
    }

    public function updateStore(string $storeId, array $data): array
    {
        $url = "https://api.mercadopago.com/users/{$this->userIdMP}/stores/{$storeId}";
        $headers = $this->getHeaders();
        return $this->makeRequest('PUT', $url, $headers, $data);
    }

    public function deleteStore(string $storeId): array
    {
        $url = "https://api.mercadopago.com/users/{$this->userIdMP}/stores/{$storeId}";
        $headers = $this->getHeaders();
        return $this->makeRequest('DELETE', $url, $headers);
    }

    // Métodos relacionados con Cajas
    public function searchPOS(string $posId): array
    {
        $url = "https://api.mercadopago.com/pos/{$posId}";
        $headers = $this->getHeaders();
        return $this->makeRequest('GET', $url, $headers);
    }

    public function getPOS(): array
    {
        $url = "https://api.mercadopago.com/pos";
        $headers = $this->getHeaders();
        return $this->makeRequest('GET', $url, $headers);
    }

    public function createPOS(array $data): array
    {
        $url = "https://api.mercadopago.com/pos";
        $headers = $this->getHeaders();
        return $this->makeRequest('POST', $url, $headers, $data);
    }

    public function updatePOS(string $posId, array $data): array
    {
        $url = "https://api.mercadopago.com/pos/{$posId}";
        $headers = $this->getHeaders();
        return $this->makeRequest('PUT', $url, $headers, $data);
    }

    public function deletePOS(string $posId): array
    {
        $url = "https://api.mercadopago.com/pos/{$posId}";
        $headers = $this->getHeaders();
        return $this->makeRequest('DELETE', $url, $headers);
    }

    // Métodos relacionados con Órdenes
    public function getOrder(string $collectorId, string $posId): array
    {
        $url = "https://api.mercadopago.com/instore/qr/seller/collectors/{$collectorId}/pos/{$posId}/orders";
        $headers = $this->getHeaders();
        return $this->makeRequest('GET', $url, $headers);
    }

    public function createOrder(string $collectorId, string $storeId, string $posId, array $data): array
    {
        $url = "https://api.mercadopago.com/instore/qr/seller/collectors/{$collectorId}/stores/{$storeId}/pos/{$posId}/orders";
        $headers = $this->getHeaders();
        return $this->makeRequest('PUT', $url, $headers, $data);
    }

    public function deleteOrder(string $collectorId, string $posId): array
    {
        $url = "https://api.mercadopago.com/instore/qr/seller/collectors/{$collectorId}/pos/{$posId}/orders";
        $headers = $this->getHeaders();
        return $this->makeRequest('DELETE', $url, $headers);
    }

    // Merchant Orders
    public function getMerchantOrder(string $orderId): array
    {
        $url = "https://api.mercadopago.com/merchant_orders/{$orderId}";
        $headers = $this->getHeaders();
        return $this->makeRequest('GET', $url, $headers);
    }

    public function createOrderTramma(string $collectorId, string $posId, array $data): array
    {
        $url = "https://api.mercadopago.com/instore/orders/qr/seller/collectors/{$collectorId}/pos/{$posId}/qrs";
        $headers = $this->getHeaders();
        return $this->makeRequest('POST', $url, $headers, $data);
    }

    public function refundOrder(string $paymentId, array $data = []): array
    {
        $url = "https://api.mercadopago.com/v1/payments/{$paymentId}/refunds";
        $headers = $this->getHeaders();
        // Generar un UUID para el encabezado X-Idempotency-Key
        $idempotencyKey = (string) Str::uuid();

        $headers = array_merge($this->getHeaders(), [
            'X-Idempotency-Key' => $idempotencyKey
        ]);
        return $this->makeRequest('POST', $url, $headers, $data);
    }

}
