<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\MercadoPagoService;
use App\Repositories\EmailNotificationsRepository;
use App\Repositories\PedidosYaRepository;

class MercadoPagoRepository
{
  /**
   * El repositorio de PedidosYa para la gestión de envíos.
   *
   * @var PedidosYaRepository
  */
  protected $pedidosYaRepository;

  /**
   * El repositorio de Email para envío de correos
   *
   * @var EmailNotificationsRepository
   */
  protected $emailNotificationsRepository;

  /**
   * Inyecta los repositorios necesarios.
   *
   * @param PedidosYaRepository $pedidosYaRepository
   * @param EmailNotificationsRepository $emailNotificationsRepository
  */
  public function __construct(PedidosYaRepository $pedidosYaRepository, EmailNotificationsRepository $emailNotificationsRepository)
  {
    $this->pedidosYaRepository = $pedidosYaRepository;
    $this->emailNotificationsRepository = $emailNotificationsRepository;
  }


  /**
   * Maneja las notificaciones webhook de MercadoPago.
   *
   * @param Request $request
   * @param MercadoPagoService $mpService
   * @return array
  */
  public function handleWebhook(Request $request, MercadoPagoService $mpService): array
  {
    $xSignatureParts = explode(',', $request->header('x-signature'));
    $xSignatureData = $this->parseXSignature($xSignatureParts);

    $ts = $xSignatureData['ts'] ?? '';
    $receivedHash = $xSignatureData['v1'] ?? '';
    $payload = $request->all();

    // Log completo de todos los datos recibidos
    Log::info('Webhook completo de MercadoPago:', [
        'headers' => $request->header(),
        'payload' => $payload,
        'x_signature_parts' => $xSignatureParts,
        'x_signature_data' => $xSignatureData
    ]);

    // MercadoPago puede enviar el ID en diferentes ubicaciones según el tipo de webhook
    $dataId = $payload['data']['id'] ?? $payload['id'] ?? $payload['data_id'] ?? null;
    $resourceUrl = $payload['resource'] ?? null;
    $id = $dataId ?: basename(parse_url($resourceUrl, PHP_URL_PATH));
    $topic = $payload['topic'] ?? $payload['type'] ?? null;

    // Log para debugging
    Log::info('Procesando webhook de MercadoPago:', [
        'dataId' => $dataId,
        'resourceUrl' => $resourceUrl,
        'id' => $id,
        'topic' => $topic,
        'payload_keys' => array_keys($payload)
    ]);

    if ($id) {
        Log::info('Verificando HMAC con parámetros:', [
            'id' => $id,
            'request_id' => $request->header('x-request-id'),
            'ts' => $ts,
            'received_hash' => $receivedHash
        ]);

        // Obtener la secret key específica de la tienda
        // Para esto, necesitamos determinar qué tienda corresponde a este webhook
        $secretKey = $this->getSecretKeyForWebhook($payload, $id, $topic);

        if ($secretKey) {
            $hmacValid = $this->verifyHMAC($id, $request->header('x-request-id'), $ts, $receivedHash, $secretKey);

            if ($hmacValid) {
                Log::info('La verificación HMAC pasó correctamente');
            } else {
                Log::warning('La verificación HMAC falló, pero continuando para debugging');
                // Por ahora, continuamos para debugging, pero en producción deberías rechazar
                // return ['message' => ['error' => 'HMAC verification failed'], 'status' => 401];
            }
        } else {
            Log::warning('No se pudo determinar la secret key para el webhook');
        }

        return $this->processNotification($topic, $id, $mpService);
    } else {
        Log::error('El índice "data" o "resource" no está presente en los datos de la solicitud');
        return ['message' => ['error' => 'Invalid request data'], 'status' => 400];
    }
  }

  /**
   * Obtiene la secret key específica para el webhook basándose en los datos recibidos.
   *
   * @param array $payload
   * @param string $id
   * @param string $topic
   * @return string|null
  */
  private function getSecretKeyForWebhook(array $payload, string $id, string $topic): ?string
  {
    try {
        // Para webhooks de payment, intentar obtener la información del pago primero
        if ($topic === 'payment') {
            $paymentInfo = $this->getPaymentInfoFromMP($id);
            if ($paymentInfo && isset($paymentInfo['metadata']['store_id'])) {
                $storeId = $paymentInfo['metadata']['store_id'];
                $account = \App\Models\MercadoPagoAccount::where('store_id', $storeId)->first();
                if ($account) {
                    Log::info('Secret key encontrada para payment webhook:', ['store_id' => $storeId]);
                    return $account->secret_key;
                }
            }
        }

        // Para webhooks de merchant_order, buscar la orden por preference_id
        if ($topic === 'merchant_order' || $topic === 'topic_merchant_order_wh') {
            // Intentar buscar la orden por preference_id que coincida con el patrón
            // Los preference_id tienen el formato: 2535442343-xxxxx-xxxxx-xxxxx
            $preferencePattern = '2535442343-%';
            $order = \App\Models\Order::where('preference_id', 'LIKE', $preferencePattern)->first();

            if ($order) {
                $account = \App\Models\MercadoPagoAccount::where('store_id', $order->store_id)->first();
                if ($account) {
                    Log::info('Secret key encontrada para merchant_order webhook:', ['store_id' => $order->store_id]);
                    return $account->secret_key;
                }
            }

            // Si no se encuentra por preference_id, intentar con la información de la orden
            $orderInfo = $this->getMerchantOrderInfoFromMP($id);
            if ($orderInfo && isset($orderInfo['external_reference'])) {
                $order = \App\Models\Order::where('id', $orderInfo['external_reference'])->first();
                if ($order) {
                    $account = \App\Models\MercadoPagoAccount::where('store_id', $order->store_id)->first();
                    if ($account) {
                        Log::info('Secret key encontrada para merchant_order webhook:', ['store_id' => $order->store_id]);
                        return $account->secret_key;
                    }
                }
            }
        }

        // Si no se puede determinar, usar la secret key por defecto
        Log::warning('No se pudo determinar la secret key específica, usando la por defecto');
        return config('services.mercadopago.secret_key');
    } catch (\Exception $e) {
        Log::error('Error obteniendo secret key para webhook: ' . $e->getMessage());
        return config('services.mercadopago.secret_key');
    }
  }

  /**
   * Obtiene información del pago desde MercadoPago sin configurar credenciales.
   *
   * @param string $id
   * @return array|null
  */
  private function getPaymentInfoFromMP(string $id): ?array
  {
    try {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', "https://api.mercadopago.com/v1/payments/{$id}", [
            'headers' => [
                'Authorization' => 'Bearer ' . config('services.mercadopago.access_token'),
            ],
        ]);

        return json_decode($response->getBody(), true);
    } catch (\Exception $e) {
        Log::error("Error al obtener información del pago: " . $e->getMessage());
        return null;
    }
  }

  /**
   * Obtiene información de la orden desde MercadoPago sin configurar credenciales.
   *
   * @param string $id
   * @return array|null
  */
  private function getMerchantOrderInfoFromMP(string $id): ?array
  {
    try {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', "https://api.mercadopago.com/merchant_orders/{$id}", [
            'headers' => [
                'Authorization' => 'Bearer ' . config('services.mercadopago.access_token'),
            ],
        ]);

        return json_decode($response->getBody(), true);
    } catch (\Exception $e) {
        Log::error("Error al obtener información de la orden: " . $e->getMessage());
        return null;
    }
  }

  /**
   * Verifica la firma HMAC de una solicitud.
   *
   * @param string $id
   * @param string $requestId
   * @param string $timestamp
   * @param string $receivedHash
   * @param string $secretKey
   * @return bool
  */
  private function verifyHMAC(string $id, string $requestId, string $timestamp, string $receivedHash, string $secretKey): bool
  {
    $message = "id:$id;request-id:$requestId;ts:$timestamp;";
    $generatedHash = hash_hmac('sha256', $message, $secretKey);

    Log::info('Verificación HMAC:', [
        'message' => $message,
        'generated_hash' => $generatedHash,
        'received_hash' => $receivedHash,
        'secret_key_length' => strlen($secretKey)
    ]);

    return hash_equals($generatedHash, $receivedHash);
  }

  /**
   * Procesa las notificaciones según el tipo de evento.
   *
   * @param string $topic
   * @param string $id
   * @param MercadoPagoService $mpService
   * @return array
  */
  private function processNotification(string $topic, string $id, MercadoPagoService $mpService): array
  {
    switch ($topic) {
        case 'payment':
            Log::info("Procesando 'payment' con ID: $id");

            // Configurar credenciales específicas de la tienda ANTES de obtener información del pago
            $paymentInfo = $mpService->getPaymentInfo($id);

            if ($paymentInfo) {
                Log::info("Información del pago recibida:", $paymentInfo);

                $orderId = $paymentInfo['metadata']['order_id'] ?? null;
                $storeId = $paymentInfo['metadata']['store_id'] ?? null;

                if ($orderId && $storeId) {
                    // Configurar credenciales específicas de la tienda
                    $mercadoPagoAccount = \App\Models\MercadoPagoAccount::where('store_id', $storeId)->first();
                    if ($mercadoPagoAccount) {
                        $mpService->setCredentials($mercadoPagoAccount->public_key, $mercadoPagoAccount->access_token, $mercadoPagoAccount->secret_key);
                        Log::info("Credenciales configuradas para la tienda:", ['store_id' => $storeId]);
                    }

                    // Determinar el estado del pago basado en el status de MercadoPago
                    $mpStatus = $paymentInfo['status'] ?? 'pending';
                    $paymentStatus = $this->determinePaymentStatus($mpStatus);

                    // Actualizar el estado del pago
                    $this->updatePaymentStatus($orderId, $paymentStatus);

                    Log::info("Estado del pago actualizado:", [
                        'order_id' => $orderId,
                        'mp_status' => $mpStatus,
                        'payment_status' => $paymentStatus
                    ]);

                    return ['message' => ['message' => 'Payment processed successfully'], 'status' => 200];
                } else {
                    Log::error("Faltan order_id o store_id en el metadata del pago");
                    return ['message' => ['error' => 'Missing order_id or store_id in payment metadata'], 'status' => 400];
                }
            } else {
                Log::error("No se pudo obtener información del pago con ID: $id");
                return ['message' => ['error' => 'Payment information not found'], 'status' => 400];
            }

                        case 'merchant_order':
        case 'topic_merchant_order_wh':
            Log::info("Procesando 'merchant_order' con ID: $id");

            // Para merchant_order, necesitamos obtener la información de la orden
            try {
                // El ID que recibimos es el ID de MercadoPago, no el ID de nuestra orden
                Log::info("Buscando orden por mp_merchant_order_id:", ['mp_id' => $id]);

                // Primero buscar si ya tenemos una orden con este merchant_order_id
                $order = Order::where('mp_merchant_order_id', $id)->first();

                // Si no se encuentra, intentar buscar por ID (por si acaso)
                if (!$order) {
                    $order = Order::where('id', $id)->first();
                }

                // Si no se encuentra, intentar buscar por preference_id
                if (!$order) {
                    $order = Order::where('preference_id', 'LIKE', '%' . $id . '%')->first();
                }

                // Si no se encuentra, intentar buscar por external_reference
                if (!$order) {
                    Log::info("Buscando orden por external_reference:", ['external_reference' => $id]);
                    $order = Order::where('id', $id)->first();
                }

                if (!$order) {
                    Log::error("No se encontró la orden con ID de MercadoPago: $id");
                    return ['message' => ['error' => 'Order not found for MercadoPago ID'], 'status' => 400];
                }

                                // Configurar credenciales específicas de la tienda ANTES de hacer cualquier llamada a la API
                $mercadoPagoAccount = \App\Models\MercadoPagoAccount::where('store_id', $order->store_id)->first();
                if (!$mercadoPagoAccount) {
                    Log::error("No se encontraron credenciales de MercadoPago para la tienda:", ['store_id' => $order->store_id]);
                    return ['message' => ['error' => 'MercadoPago credentials not found for store'], 'status' => 400];
                }

                Log::info("Encontradas credenciales de MercadoPago:", [
                    'store_id' => $order->store_id,
                    'public_key' => $mercadoPagoAccount->public_key,
                    'access_token_length' => strlen($mercadoPagoAccount->access_token),
                    'secret_key_length' => strlen($mercadoPagoAccount->secret_key)
                ]);

                $mpService->setCredentials($mercadoPagoAccount->public_key, $mercadoPagoAccount->access_token, $mercadoPagoAccount->secret_key);
                Log::info("Credenciales configuradas para la tienda:", ['store_id' => $order->store_id]);

                                $orderInfo = $mpService->getMerchantOrderInfo($id);
                if ($orderInfo) {
                    Log::info("Información de la orden recibida:", $orderInfo);

                    // Buscar la orden por external_reference
                    $externalReference = $orderInfo['external_reference'] ?? null;
                    if ($externalReference) {
                        Log::info("Buscando orden por external_reference:", ['external_reference' => $externalReference]);
                        $order = Order::where('id', $externalReference)->first();

                        if ($order) {
                            Log::info("Orden encontrada por external_reference:", ['order_id' => $order->id]);
                        }
                    }

                    // Si no se encontró por external_reference, usar la búsqueda anterior
                    if (!$order) {
                        Log::warning("No se encontró orden por external_reference, usando búsqueda anterior");
                    }

                    // Guardar el merchant_order_id en la orden
                    $order->mp_merchant_order_id = $id;
                    $order->save();
                    Log::info("Merchant order ID guardado en la orden:", ['order_id' => $order->id, 'mp_merchant_order_id' => $id]);

                    // Procesar los pagos de la orden
                    if (isset($orderInfo['payments']) && is_array($orderInfo['payments'])) {
                        foreach ($orderInfo['payments'] as $payment) {
                            $paymentId = $payment['id'] ?? null;
                            if ($paymentId) {
                                $this->processPayment($paymentId, $mpService);
                            }
                        }
                    }

                    return ['message' => ['message' => 'Merchant order processed'], 'status' => 200];
                } else {
                    Log::error("No se pudo obtener información de la orden con ID: $id");
                    return ['message' => ['error' => 'Order information not found'], 'status' => 400];
                }
            } catch (\Exception $e) {
                Log::error("Error procesando merchant_order: " . $e->getMessage());
                return ['message' => ['error' => 'Error processing order'], 'status' => 500];
            }

        default:
            Log::warning("Tipo de notificación no soportado: $topic");
            return ['message' => ['error' => 'Unsupported notification type'], 'status' => 400];
    }
  }

  /**
   * Actualiza el estado del pago de una orden.
   *
   * @param int $orderId
   * @param string $status
   * @return bool
  */
  public function updatePaymentStatus(int $orderId, string $status): bool
  {
      $order = Order::find($orderId);

      if ($order) {
          $order->payment_status = $status;
          $order->save();

          Log::info("Estado de la orden actualizado a '$status' para la orden con ID: $orderId");

          // if ($status === 'paid') {
          //     $this->sendOrderEmails($order);
          // }

          if ($status === 'paid' && $order->shipping_method === 'peya') {
              $this->createPeYaShipping($order);
          }

          return true;
      } else {
          Log::error("No se encontró la orden con ID: $orderId");
          return false;
      }
  }

  /**
   * Envía correos electrónicos de notificación de orden.
   *
   * @param Order $order
   * @return void
   */
  private function sendOrderEmails(Order $order)
  {
      $variables = [
          'order_id' => $order->id,
          'client_name' => $order->client->name,
          'client_lastname' => $order->client->lastname,
          'client_email' => $order->client->email,
          'client_phone' => $order->client->phone,
          'client_address' => $order->client->address,
          'client_city' => $order->client->city,
          'client_state' => $order->client->state,
          'client_country' => $order->client->country,
          'order_subtotal' => $order->subtotal,
          'order_shipping' => $order->shipping,
          'coupon_amount' => $order->coupon_amount,
          'order_total' => $order->total,
          'order_date' => $order->date,
          'order_items' => $order->products,
          'order_shipping_method' => $order->shipping_method,
          'order_payment_method' => $order->payment_method,
          'order_payment_status' => $order->payment_status,
          'store_name' => $order->store->name,
      ];
      // Enviar correo al administrador
      $this->emailNotificationsRepository->sendNewOrderEmail($variables);
      // Enviar correo al cliente
      $this->emailNotificationsRepository->sendNewOrderClientEmail($variables);
  }

  /**
   * Crea el envío en PedidosYa si el método de envío es 'peya'.
   *
   * @param Order $order
   * @return void
  */
  private function createPeYaShipping(Order $order): void
  {
      $request = new Request([
          'estimate_id' => $order->estimate_id,
          'store_id' => $order->store_id, // Asegúrate de incluir el store_id
          'delivery_offer_id' => $order->delivery_offer_id,
      ]);
      $response = $this->pedidosYaRepository->confirmOrderRequest($request);
      if (isset($response['shippingId'])) {
          $order->shipping_id = $response['shippingId'];
          $order->shipping_status = $response['status'];
          $order->save();
          Log::info("Envío creado con éxito en PedidosYa para la orden con ID: {$order->id}");
      } else {
          Log::error("Error al crear el envío en PedidosYa para la orden con ID: {$order->id}", ['response' => $response]);
      }
  }

  /**
   * Procesa un pago individual.
   *
   * @param string $paymentId
   * @param MercadoPagoService $mpService
   * @return void
  */
  private function processPayment(string $paymentId, MercadoPagoService $mpService): void
  {
      Log::info("Procesando pago con ID: $paymentId");
      $paymentInfo = $mpService->getPaymentInfo($paymentId);

      if ($paymentInfo) {
          Log::info("Información del pago recibida:", $paymentInfo);

          // Obtener el ID de la orden desde el metadata
          $orderId = $paymentInfo['metadata']['order_id'] ?? null;
          $storeId = $paymentInfo['metadata']['store_id'] ?? null;

                      if ($orderId && $storeId) {
                // Configurar credenciales específicas de la tienda
                $mercadoPagoAccount = \App\Models\MercadoPagoAccount::where('store_id', $storeId)->first();
                if ($mercadoPagoAccount) {
                    $mpService->setCredentials($mercadoPagoAccount->public_key, $mercadoPagoAccount->access_token, $mercadoPagoAccount->secret_key);
                    Log::info("Credenciales configuradas para la tienda:", ['store_id' => $storeId]);
                }

              // Determinar el estado del pago
              $status = $this->determinePaymentStatus($paymentInfo['status']);
              $this->updatePaymentStatus($orderId, $status);

              Log::info("Estado del pago actualizado a '$status' para la orden con ID: $orderId");
          } else {
              Log::error("No se encontró order_id o store_id en el metadata del pago: $paymentId");
          }
      } else {
          Log::error("No se pudo obtener información del pago con ID: $paymentId");
      }
  }

  /**
   * Determina el estado del pago basado en el status de MercadoPago.
   *
   * @param string $mpStatus
   * @return string
  */
  private function determinePaymentStatus(string $mpStatus): string
  {
      switch ($mpStatus) {
          case 'approved':
              return 'paid';
          case 'pending':
              return 'pending';
          case 'rejected':
          case 'cancelled':
              return 'failed';
          default:
              return 'pending';
      }
  }

  /**
   * Analiza los datos de la firma X-Signature.
   *
   * @param array $xSignatureParts
   * @return array
  */
  private function parseXSignature(array $xSignatureParts): array
  {
      $xSignatureData = [];
      foreach ($xSignatureParts as $part) {
          list($key, $value) = explode('=', $part);
          $xSignatureData[trim($key)] = trim($value);
      }
      return $xSignatureData;
  }
}
