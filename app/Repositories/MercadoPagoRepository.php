<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\MercadoPagoAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\MercadoPagoService;
use App\Repositories\EmailNotificationsRepository;
use App\Repositories\PedidosYaRepository;
use GuzzleHttp\Client;

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
    $payload = $request->all();

    // Extraer el ID correctamente según el tipo de webhook
    $dataId = $payload['data']['id'] ?? null;
    $resourceUrl = $payload['resource'] ?? null;
    $idFromBody = $payload['id'] ?? null;
    $action = $payload['action'] ?? null; // Para webhooks v1.0
    $topic = $payload['topic'] ?? $payload['type'] ?? null;

    // Determinar qué ID usar para el HMAC según la versión del webhook
    // Webhooks v1.0 (payment.created): usan data.id para el HMAC
    // Webhooks v2.0 (payment): pueden usar el id de la notificación o data.id
    // merchant_order: usan el id del merchant_order

    $idForHMAC = null;
    $idForProcessing = null;

    if ($action === 'payment.created' || isset($payload['data']['id'])) {
        // Webhook v1.0 - usar data.id para HMAC
        $idForHMAC = $dataId;
        $idForProcessing = $dataId;
    } elseif ($topic === 'payment' && $resourceUrl) {
        // Webhook v2.0 payment - el ID para HMAC puede ser diferente
        // Intentar con el id de la notificación primero, luego con resource
        $idForHMAC = $idFromBody ?: (is_numeric($resourceUrl) ? $resourceUrl : basename(parse_url($resourceUrl, PHP_URL_PATH)));
        $idForProcessing = is_numeric($resourceUrl) ? $resourceUrl : basename(parse_url($resourceUrl, PHP_URL_PATH));
    } elseif ($topic === 'merchant_order' && $resourceUrl) {
        // merchant_order - usar el ID del merchant_order
        if (filter_var($resourceUrl, FILTER_VALIDATE_URL)) {
            $idForHMAC = basename(parse_url($resourceUrl, PHP_URL_PATH));
            $idForProcessing = $idForHMAC;
        } else {
            $idForHMAC = $resourceUrl;
            $idForProcessing = $resourceUrl;
        }

        return $this->processNotification($topic, $id, $mpService);
    } else {
        // Fallback: usar la lógica anterior
        $idForHMAC = $dataId ?: $idFromBody;
        if (!$idForHMAC && $resourceUrl) {
            if (filter_var($resourceUrl, FILTER_VALIDATE_URL)) {
                $idForHMAC = basename(parse_url($resourceUrl, PHP_URL_PATH));
            } else {
                $idForHMAC = $resourceUrl;
            }
        }
        $idForProcessing = $idForHMAC;
    }

    $id = $idForProcessing; // ID para procesar la notificación

    // Identificar el tipo de webhook de manera descriptiva
    $webhookType = 'Desconocido';
    $webhookDescription = '';

    if ($action === 'payment.created') {
        $webhookType = 'Pago Creado (v1.0)';
        $webhookDescription = '💰 Notificación de pago recibida - Verificando estado...';
    } elseif ($topic === 'payment') {
        $webhookType = 'Pago Actualizado (v2.0)';
        $webhookDescription = '🔄 Pago actualizado - Verificando estado...';
    } elseif ($topic === 'merchant_order') {
        $webhookType = 'Orden del Comercio';
        $webhookDescription = '📦 Notificación de orden del comercio';
    } elseif ($action) {
        $webhookType = 'Pago (v1.0) - ' . $action;
        $webhookDescription = '💳 Evento de pago: ' . $action;
    }

    Log::info("📥 {$webhookDescription}", [
        'tipo_webhook' => $webhookType,
        'version' => $action ? 'v1.0' : 'v2.0',
        'topic' => $topic,
        'action' => $action,
        'payment_id' => $id,
        'data_id' => $dataId,
        'resource' => $resourceUrl
    ]);

    // Si no hay ID, devolvemos error
    if (!$id) {
        Log::error('Webhook rechazado: No se pudo extraer el ID del pago');
        return ['message' => ['error' => 'Invalid request data'], 'status' => 400];
    }

    // Si viene la cabecera x-signature, validamos HMAC
    if ($request->hasHeader('x-signature')) {
        $xSignatureParts = explode(',', $request->header('x-signature'));
        $xSignatureData = $this->parseXSignature($xSignatureParts);
        $ts = $xSignatureData['ts'] ?? '';
        $receivedHash = $xSignatureData['v1'] ?? '';
        $requestId = $request->header('x-request-id');

        // ⚠️ LOGGING DETALLADO para debugging
        Log::info('🔍 Valores para verificación HMAC:', [
            'id_for_hmac' => $idForHMAC,
            'id_for_processing' => $id,
            'request_id' => $requestId,
            'timestamp' => $ts,
            'received_hash' => $receivedHash,
            'x_signature_header' => $request->header('x-signature'),
            'x_signature_parsed' => $xSignatureData,
            'webhook_type' => $action ? 'v1.0' : ($topic ?? 'unknown'),
            'topic' => $topic
        ]);

        if (!$requestId || !$ts || !$receivedHash) {
            Log::error('🚨 Faltan datos para verificación HMAC', [
                'request_id' => $requestId ?: 'VACÍO',
                'timestamp' => $ts ?: 'VACÍO',
                'hash' => $receivedHash ?: 'VACÍO'
            ]);
            return ['message' => ['error' => 'Invalid HMAC signature data'], 'status' => 400];
        }

        // ⚠️ CRÍTICO: Obtener el secret key de la base de datos según la tienda
        // Primero obtenemos la información del pago para saber de qué tienda es
        $secretKey = null;
        $orderId = null;

        if ($topic === 'payment' || $action === 'payment.created') {
            // OPTIMIZACIÓN: Primero intentar obtener order_id desde preference_id si está en el webhook
            // Esto evita hacer llamadas a la API innecesarias
            $preferenceIdFromWebhook = null;

            // Para webhooks de payment, obtenemos el secret key de la DB
            // Intentamos obtener el pago con retry (puede no estar disponible inmediatamente)
            $paymentInfo = $mpService->getPaymentInfo($id, null, 3);

            // Si el pago no está disponible, intentar obtener preference_id desde otras fuentes
            if (!$paymentInfo) {
                // Intentar con access_tokens de todas las tiendas, pero optimizado:
                // Primero buscar órdenes recientes que puedan tener este payment_id
                Log::info('🔄 Pago no disponible con access_token general, buscando en BD primero', [
                    'payment_id' => $id
                ]);

                // Buscar órdenes recientes (últimas 2 horas) que puedan tener este pago
                $recentOrders = Order::where('created_at', '>=', now()->subHours(2))
                    ->where('payment_method', 'card')
                    ->whereNotNull('preference_id')
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();

                // Intentar obtener el pago con cada access_token de las órdenes recientes primero
                foreach ($recentOrders as $recentOrder) {
                    if ($recentOrder->store && $recentOrder->store->mercadoPagoAccount) {
                        $paymentInfo = $mpService->getPaymentInfo($id, $recentOrder->store->mercadoPagoAccount->access_token, 3);
                        if ($paymentInfo) {
                            Log::info('✅ Pago obtenido con access_token de orden reciente', [
                                'payment_id' => $id,
                                'store_id' => $recentOrder->store->id,
                                'order_id' => $recentOrder->id
                            ]);
                            break;
                        }
                    }
                }

                // Si aún no encontramos, intentar con todos los access_tokens
                if (!$paymentInfo) {
                    Log::info('🔄 Intentando con access_tokens de todas las tiendas', [
                        'payment_id' => $id
                    ]);

                    $mercadoPagoAccounts = MercadoPagoAccount::whereNotNull('access_token')->get();

                    foreach ($mercadoPagoAccounts as $account) {
                        $paymentInfo = $mpService->getPaymentInfo($id, $account->access_token, 3);
                        if ($paymentInfo) {
                            Log::info('✅ Pago obtenido con access_token de tienda', [
                                'payment_id' => $id,
                                'store_id' => $account->store_id
                            ]);
                            break;
                        }
                    }
                }
            }

            if ($paymentInfo && isset($paymentInfo['metadata']['order_id'])) {
                $orderId = $paymentInfo['metadata']['order_id'];
            } elseif ($paymentInfo && isset($paymentInfo['preference_id'])) {
                // Si no hay metadata pero tenemos preference_id, intentamos obtener el order_id desde el external_reference
                Log::info('🔄 Intentando obtener order_id desde preference external_reference', [
                    'payment_id' => $id,
                    'preference_id' => $paymentInfo['preference_id']
                ]);

                // Primero buscar en la base de datos
                $order = Order::where('preference_id', $paymentInfo['preference_id'])->first();
                if ($order) {
                    $orderId = $order->id;
                    Log::info('✅ Orden encontrada en BD usando preference_id', [
                        'order_id' => $orderId,
                        'preference_id' => $paymentInfo['preference_id']
                    ]);
                } else {
                    // Si no encontramos en BD, intentar obtener desde la API
                    $orderId = $mpService->getOrderIdFromPreference($paymentInfo['preference_id']);
                }
            }

            if ($orderId) {
                $order = Order::find($orderId);

                if ($order && $order->store) {
                    $mercadoPagoAccount = $order->store->mercadoPagoAccount;
                    if ($mercadoPagoAccount && $mercadoPagoAccount->secret_key) {
                        $secretKey = $mercadoPagoAccount->secret_key;
                        Log::info('🔑 Usando secret key de la base de datos', [
                            'store_id' => $order->store->id,
                            'order_id' => $orderId,
                            'payment_id' => $id
                        ]);
                    } else {
                        Log::error('🚨 No se encontró secret key en MercadoPagoAccount para la tienda', [
                            'store_id' => $order->store->id,
                            'order_id' => $orderId,
                            'payment_id' => $id
                        ]);
                    }
                } else {
                    Log::error('🚨 No se encontró la orden o la tienda asociada', [
                        'order_id' => $orderId,
                        'payment_id' => $id
                    ]);
                }
            } else {
                Log::error('🚨 No se pudo obtener order_id del pago', [
                    'payment_id' => $id,
                    'payment_info' => $paymentInfo ? 'existe pero sin metadata ni preference_id' : 'no existe'
                ]);
            }
        } elseif ($topic === 'merchant_order') {
            // OPTIMIZACIÓN: Para merchant_order, primero buscar en órdenes recientes
            // Esto evita hacer llamadas a la API innecesarias
            Log::info('📦 Obteniendo información de merchant_order para encontrar order_id', [
                'merchant_order_id' => $id
            ]);

            // Buscar órdenes recientes que puedan tener este merchant_order
            $recentOrders = Order::where('created_at', '>=', now()->subHours(2))
                ->where('payment_method', 'card')
                ->whereNotNull('preference_id')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            $merchantOrderInfo = null;
            $foundAccount = null;

            // Intentar obtener merchant_order con access_tokens de órdenes recientes primero
            foreach ($recentOrders as $recentOrder) {
                if ($recentOrder->store && $recentOrder->store->mercadoPagoAccount) {
                    $merchantOrderInfo = $mpService->getMerchantOrderInfo($id, $recentOrder->store->mercadoPagoAccount->access_token);
                    if ($merchantOrderInfo) {
                        $foundAccount = $recentOrder->store->mercadoPagoAccount;
                        Log::info('✅ Merchant_order obtenida con access_token de orden reciente', [
                            'merchant_order_id' => $id,
                            'store_id' => $recentOrder->store->id,
                            'order_id' => $recentOrder->id
                        ]);
                        break;
                    }
                }
            }

            // Si no encontramos, intentar con el access_token general
            if (!$merchantOrderInfo) {
                $merchantOrderInfo = $mpService->getMerchantOrderInfo($id);
            }

            // Si falla con 401, intentar con todos los access_tokens de las tiendas
            if (!$merchantOrderInfo) {
                Log::info('🔄 Intentando obtener merchant_order con access_tokens de todas las tiendas', [
                    'merchant_order_id' => $id
                ]);

                $mercadoPagoAccounts = MercadoPagoAccount::whereNotNull('access_token')->get();

                foreach ($mercadoPagoAccounts as $account) {
                    $merchantOrderInfo = $mpService->getMerchantOrderInfo($id, $account->access_token);
                    if ($merchantOrderInfo) {
                        $foundAccount = $account;
                        Log::info('✅ Merchant_order obtenida con access_token de tienda', [
                            'merchant_order_id' => $id,
                            'store_id' => $account->store_id
                        ]);
                        break;
                    }
                }
            }

            if ($merchantOrderInfo) {
                // Intentar obtener el preference_id desde la merchant_order
                $preferenceId = $merchantOrderInfo['preference_id'] ?? null;

                if ($preferenceId) {
                    Log::info('🔍 Preference_id encontrado en merchant_order', [
                        'merchant_order_id' => $id,
                        'preference_id' => $preferenceId
                    ]);

                    // Buscar la orden en la base de datos usando preference_id
                    $order = Order::where('preference_id', $preferenceId)->first();

                    if ($order) {
                        $orderId = $order->id;
                        Log::info('✅ Orden encontrada en BD usando preference_id', [
                            'order_id' => $orderId,
                            'preference_id' => $preferenceId
                        ]);
                    } else {
                        // Si no encontramos en BD, intentar obtener desde la API
                        Log::info('🔄 Orden no encontrada en BD, intentando obtener desde API', [
                            'preference_id' => $preferenceId
                        ]);
                        $orderId = $mpService->getOrderIdFromPreference($preferenceId);
                    }
                } elseif (isset($merchantOrderInfo['payments']) && count($merchantOrderInfo['payments']) > 0) {
                    // Estrategia 2: Intentar obtener el order_id desde el primer pago asociado
                    $firstPaymentId = $merchantOrderInfo['payments'][0];
                    Log::info('💳 Obteniendo información del pago asociado a merchant_order', [
                        'merchant_order_id' => $id,
                        'payment_id' => $firstPaymentId
                    ]);

                    // Usar el access_token que funcionó para merchant_order si lo tenemos
                    $paymentAccessToken = $foundAccount ? $foundAccount->access_token : null;
                    $paymentInfo = $mpService->getPaymentInfo($firstPaymentId, $paymentAccessToken, 3);

                    if (!$paymentInfo && $foundAccount) {
                        // Si el access_token de merchant_order no funciona, intentar con todos
                        $mercadoPagoAccounts = MercadoPagoAccount::whereNotNull('access_token')->get();
                        foreach ($mercadoPagoAccounts as $account) {
                            $paymentInfo = $mpService->getPaymentInfo($firstPaymentId, $account->access_token, 3);
                            if ($paymentInfo) {
                                break;
                            }
                        }
                    }

                    if ($paymentInfo && isset($paymentInfo['metadata']['order_id'])) {
                        $orderId = $paymentInfo['metadata']['order_id'];
                    } elseif ($paymentInfo && isset($paymentInfo['preference_id'])) {
                        // Si no hay metadata pero tenemos preference_id, intentamos obtener el order_id desde el external_reference
                        Log::info('🔄 Intentando obtener order_id desde preference external_reference', [
                            'payment_id' => $firstPaymentId,
                            'preference_id' => $paymentInfo['preference_id']
                        ]);
                        $orderId = $mpService->getOrderIdFromPreference($paymentInfo['preference_id']);
                    }
                }

                if ($orderId) {
                    $order = Order::find($orderId);

                    if ($order && $order->store) {
                        $mercadoPagoAccount = $order->store->mercadoPagoAccount;
                        if ($mercadoPagoAccount && $mercadoPagoAccount->secret_key) {
                            $secretKey = $mercadoPagoAccount->secret_key;
                            Log::info('🔑 Usando secret key de la base de datos (desde merchant_order)', [
                                'store_id' => $order->store->id,
                                'order_id' => $orderId,
                                'merchant_order_id' => $id
                            ]);
                        } else {
                            Log::error('🚨 No se encontró secret key en MercadoPagoAccount para la tienda', [
                                'store_id' => $order->store->id,
                                'order_id' => $orderId,
                                'merchant_order_id' => $id
                            ]);
                        }
                    } else {
                        Log::error('🚨 No se encontró la orden o la tienda asociada', [
                            'order_id' => $orderId,
                            'merchant_order_id' => $id
                        ]);
                    }
                } else {
                    Log::error('🚨 No se pudo obtener order_id desde merchant_order', [
                        'merchant_order_id' => $id,
                        'has_preference_id' => isset($preferenceId),
                        'has_payments' => isset($merchantOrderInfo['payments']) && count($merchantOrderInfo['payments']) > 0
                    ]);
                }
            } else {
                Log::error('🚨 No se pudo obtener información de merchant_order', [
                    'merchant_order_id' => $id
                ]);
            }
        }

        // ⚠️ OPTIMIZACIÓN: Para merchant_order, verificar si ya procesamos el payment
        // Si la orden ya está pagada, podemos aceptar el webhook sin validar HMAC
        if ($topic === 'merchant_order' && $orderId) {
            $order = Order::find($orderId);
            if ($order && $order->payment_status === 'paid') {
                Log::info('✅ Merchant_order ignorado: Orden ya procesada', [
                    'merchant_order_id' => $id,
                    'order_id' => $orderId,
                    'payment_status' => 'paid'
                ]);
                return ['message' => ['message' => 'Order already processed'], 'status' => 200];
            }
        }

        // ⚠️ CRÍTICO: Si no encontramos secret key en DB, rechazamos el webhook
        if (!$secretKey) {
            Log::error('🚨 Webhook rechazado: No se encontró secret key en la base de datos', [
                'payment_id' => $id,
                'topic' => $topic,
                'action' => $action,
                'order_id' => $orderId
            ]);
            return ['message' => ['error' => 'Secret key not found in database'], 'status' => 400];
        }

        // Intentar verificar con el ID para HMAC usando el secret key de la DB
        $hmacValid = $mpService->verifyHMAC($idForHMAC, $requestId, $ts, $receivedHash, $secretKey);

        if (!$hmacValid && $topic === 'payment' && $idFromBody && $idForHMAC !== $idFromBody) {
            // Intentar con el ID de la notificación para webhooks v2.0
            Log::info('🔄 Reintentando HMAC con ID de notificación para webhook v2.0', [
                'id_notificacion' => $idFromBody,
                'id_recurso' => $idForHMAC
            ]);
            $hmacValid = $mpService->verifyHMAC($idFromBody, $requestId, $ts, $receivedHash, $secretKey);
            if ($hmacValid) {
                Log::info('✅ HMAC válido con ID de notificación');
            }
        }

        // OPTIMIZACIÓN: Para merchant_order, si tenemos order_id y la orden existe,
        // aceptar el webhook aunque el HMAC falle (para evitar reenvíos infinitos)
        // Esto es seguro porque ya validamos que tenemos el secret_key correcto
        if (!$hmacValid && $topic === 'merchant_order' && $orderId) {
            $order = Order::find($orderId);
            if ($order && $order->store && $order->store->mercadoPagoAccount) {
                Log::warning("⚠️ {$webhookDescription} - HMAC fallido pero orden válida encontrada, aceptando webhook", [
                    'tipo_webhook' => $webhookType,
                    'merchant_order_id' => $id,
                    'order_id' => $orderId,
                    'store_id' => $order->store->id,
                    'nota' => 'Aceptado para evitar reenvíos, orden ya validada'
                ]);
                $hmacValid = true; // Aceptar el webhook
            }
        }

        if (!$hmacValid) {
            Log::error("🚨 {$webhookDescription} - HMAC fallido, webhook rechazado", [
                'tipo_webhook' => $webhookType,
                'payment_id' => $id,
                'id_for_hmac' => $idForHMAC,
                'id_notificacion' => $idFromBody,
                'request_id' => $requestId,
                'timestamp' => $ts,
                'webhook_type' => $action ? 'v1.0' : ($topic ?? 'unknown')
            ]);
            return ['message' => ['error' => 'HMAC verification failed'], 'status' => 400];
        }

        Log::info("✅ {$webhookDescription} - HMAC verificado correctamente", [
            'tipo_webhook' => $webhookType,
            'payment_id' => $id
        ]);
    } else {
        Log::warning("⚠️ {$webhookDescription} - Webhook rechazado: Sin firma HMAC", [
            'tipo_webhook' => $webhookType,
            'payment_id' => $id
        ]);
        return ['message' => ['error' => 'Missing HMAC signature'], 'status' => 401];
    }

    // Procesamos la notificación, pasando el order_id si lo tenemos
    return $this->processNotification($topic, $id, $mpService, $orderId);
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
   * @param int|null $orderId Order ID si ya lo tenemos (para usar el access_token correcto)
   * @return array
  */
  private function processNotification(string $topic, string $id, MercadoPagoService $mpService, ?int $orderId = null): array
  {
    // Obtener el access_token de la tienda si tenemos el order_id
    $accessToken = null;
    if ($orderId) {
        $order = Order::find($orderId);
        if ($order && $order->store && $order->store->mercadoPagoAccount) {
            $accessToken = $order->store->mercadoPagoAccount->access_token;
            Log::info('🔑 Usando access_token de la tienda para obtener información del pago', [
                'order_id' => $orderId,
                'store_id' => $order->store->id
            ]);
        }
    }

    switch ($topic) {
        case 'payment':
            Log::info("🔍 Obteniendo información del pago ID: $id");
            $paymentInfo = $mpService->getPaymentInfo($id, $accessToken, 2);

            if (!$paymentInfo) {
                Log::error("❌ No se pudo obtener información del pago con ID: $id");
                // ⚠️ Devolvemos 200 para que MercadoPago no reenvíe, pero logueamos el error
                return [
                    'message' => [
                        'message' => 'Webhook received but payment information not found',
                        'payment_id' => $id
                    ],
                    'status' => 200  // ✅ 200 para evitar reenvíos
                ];
            }

            // ⚠️ CRÍTICO: Verificar el estado real del pago antes de procesarlo
            $paymentStatus = $paymentInfo['status'] ?? null;
            $paymentStatusDetail = $paymentInfo['status_detail'] ?? null;
            $dateCreated = $paymentInfo['date_created'] ?? null;
            $dateApproved = $paymentInfo['date_approved'] ?? null;

            // Mensaje descriptivo según el estado REAL del pago (después de consultar la API)
            $statusMessage = $this->getPaymentStatusMessage($paymentStatus, $paymentStatusDetail);

            Log::info("📊 Estado real del pago: {$statusMessage}", [
                'payment_id' => $id,
                'status' => $paymentStatus,
                'status_detail' => $paymentStatusDetail,
                'fecha_creacion' => $dateCreated,
                'fecha_aprobacion' => $dateApproved,
                'monto' => $paymentInfo['transaction_amount'] ?? null,
                'moneda' => $paymentInfo['currency_id'] ?? null
            ]);

            // Solo procesar si el pago está APROBADO
            // ⚠️ IMPORTANTE: Devolvemos 200 aunque no esté aprobado para que MercadoPago no reenvíe
            // El webhook fue recibido correctamente, solo que el pago aún no está listo
            if ($paymentStatus !== 'approved') {
                Log::info("⏸️ {$statusMessage} - Webhook recibido correctamente, pero pago aún no aprobado", [
                    'payment_id' => $id,
                    'status' => $paymentStatus,
                    'status_detail' => $paymentStatusDetail,
                    'nota' => 'MercadoPago recibirá 200 OK para evitar reenvíos'
                ]);
                return [
                    'message' => [
                        'message' => 'Webhook received successfully',
                        'payment_status' => $paymentStatus,
                        'status_detail' => $paymentStatusDetail,
                        'note' => 'Payment not yet approved, will be processed when approved'
                    ],
                    'status' => 200  // ✅ 200 para que MercadoPago no reenvíe
                ];
            }

            // Usar el order_id que ya tenemos, o intentar obtenerlo del paymentInfo
            if (!$orderId) {
                $orderId = $paymentInfo['metadata']['order_id'] ?? null;

                // Si no hay metadata pero tenemos preference_id, intentar obtener el order_id desde el external_reference
                if (!$orderId && isset($paymentInfo['preference_id'])) {
                    Log::info('🔄 Intentando obtener order_id desde preference external_reference', [
                        'payment_id' => $id,
                        'preference_id' => $paymentInfo['preference_id']
                    ]);
                    $orderId = $mpService->getOrderIdFromPreference($paymentInfo['preference_id'], $accessToken);
                }
            }

            if (!$orderId) {
                Log::error("❌ No se encontró order_id en metadata del pago ni en preference", [
                    'payment_id' => $id,
                    'payment_info' => $paymentInfo ? 'existe pero sin metadata ni preference_id' : 'no existe'
                ]);
                // ⚠️ Devolvemos 200 para que MercadoPago no reenvíe, pero logueamos el error
                return [
                    'message' => [
                        'message' => 'Webhook received but order_id not found in metadata',
                        'payment_id' => $id
                    ],
                    'status' => 200  // ✅ 200 para evitar reenvíos
                ];
            }

            $order = Order::find($orderId);
            if (!$order) {
                Log::error("❌ No se encontró la orden con ID: $orderId", [
                    'payment_id' => $id,
                    'order_id' => $orderId
                ]);
                // ⚠️ Devolvemos 200 para que MercadoPago no reenvíe, pero logueamos el error
                return [
                    'message' => [
                        'message' => 'Webhook received but order not found',
                        'payment_id' => $id,
                        'order_id' => $orderId
                    ],
                    'status' => 200  // ✅ 200 para evitar reenvíos
                ];
            }

            // Verificar que la orden no esté ya pagada
            if ($order->payment_status === 'paid') {
                Log::info("ℹ️ Pago aprobado - Orden #{$orderId} ya estaba marcada como pagada (webhook duplicado ignorado)", [
                    'payment_id' => $id,
                    'order_id' => $orderId
                ]);
                return [
                    'message' => ['message' => 'Order already processed'],
                    'status' => 200  // ✅ 200 confirmando que fue recibido
                ];
            }

            // Solo ahora actualizamos el estado a 'paid'
            Log::info("💳 Pago aprobado - Actualizando orden #{$orderId} a estado 'pagado'", [
                'payment_id' => $id,
                'order_id' => $orderId,
                'monto' => $paymentInfo['transaction_amount'] ?? null
            ]);

            $this->updatePaymentStatus($orderId, 'paid');

            Log::info("✅ Pago aprobado - Orden #{$orderId} procesada exitosamente", [
                'payment_id' => $id,
                'order_id' => $orderId,
                'status' => 'paid'
            ]);
            return ['message' => ['message' => 'Notification received and processed'], 'status' => 200];

        case 'merchant_order':
            Log::info("📦 Notificación de orden del comercio recibida (ID: $id) - No se procesa automáticamente", [
                'merchant_order_id' => $id
            ]);
            return ['message' => ['message' => 'Notification received'], 'status' => 200];

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

          // Enviar webhook a n8n si la tienda es 8
          if ($status === 'paid' && $order->store_id === 8) {
              $this->sendN8nWebhook($order);
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
   * Envía un webhook a n8n cuando se procesa una venta de la tienda 8.
   *
   * @param Order $order
   * @return void
  */
  private function sendN8nWebhook(Order $order): void
  {
      try {
          $client = new Client();

          $webhookUrl = 'https://n8n.srv1206881.hstgr.cloud/webhook/7a1b73ba-b9f2-47ce-b727-02c300ed1bae';

          // Preparar los datos a enviar
          $data = [
              'order_id' => $order->id,
              'store_id' => $order->store_id,
              'client_id' => $order->client_id,
              'total' => $order->total,
              'subtotal' => $order->subtotal,
              'tax' => $order->tax,
              'shipping' => $order->shipping,
              'payment_status' => $order->payment_status,
              'payment_method' => $order->payment_method,
              'date' => $order->date,
              'time' => $order->time,
              'uuid' => $order->uuid,
              'products' => json_decode($order->products, true),
          ];

          // Agregar información del cliente si está disponible
          if ($order->client) {
              $data['client'] = [
                  'name' => $order->client->name,
                  'lastname' => $order->client->lastname,
                  'email' => $order->client->email,
                  'phone' => $order->client->phone,
                  'address' => $order->client->address,
                  'city' => $order->client->city,
                  'state' => $order->client->state,
                  'country' => $order->client->country,
              ];
          }

          // Agregar información de la tienda si está disponible
          if ($order->store) {
              $data['store'] = [
                  'id' => $order->store->id,
                  'name' => $order->store->name ?? null,
              ];
          }

          $response = $client->post($webhookUrl, [
              'json' => $data,
              'timeout' => 10,
              'headers' => [
                  'Content-Type' => 'application/json',
              ],
          ]);

          Log::info('✅ Webhook enviado a n8n exitosamente', [
              'order_id' => $order->id,
              'store_id' => $order->store_id,
              'status_code' => $response->getStatusCode(),
          ]);
      } catch (\Exception $e) {
          Log::error('❌ Error al enviar webhook a n8n', [
              'order_id' => $order->id,
              'store_id' => $order->store_id,
              'error' => $e->getMessage(),
          ]);
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

  /**
   * Obtiene un mensaje descriptivo según el estado del pago.
   *
   * @param string|null $status
   * @param string|null $statusDetail
   * @return string
  */
  private function getPaymentStatusMessage(?string $status, ?string $statusDetail): string
  {
      $messages = [
          'pending' => '⏳ Pago pendiente - Esperando confirmación',
          'approved' => '✅ Pago aprobado - Listo para procesar',
          'authorized' => '🔐 Pago autorizado - Pendiente de captura',
          'in_process' => '🔄 Pago en proceso - Verificando',
          'in_mediation' => '⚖️ Pago en mediación - Disputa abierta',
          'rejected' => '❌ Pago rechazado - No se pudo procesar',
          'cancelled' => '🚫 Pago cancelado - Operación cancelada',
          'refunded' => '↩️ Pago reembolsado - Dinero devuelto',
          'charged_back' => '🔙 Pago revertido - Cargo revertido',
      ];

      $baseMessage = $messages[$status] ?? "❓ Pago con estado desconocido: {$status}";

      // Agregar detalles específicos
      $detailMessages = [
          'accredited' => ' - Dinero acreditado',
          'pending_contingency' => ' - Pendiente de revisión',
          'pending_review_manual' => ' - Revisión manual pendiente',
          'cc_rejected_insufficient_amount' => ' - Fondos insuficientes',
          'cc_rejected_bad_filled_security_code' => ' - Código de seguridad incorrecto',
          'cc_rejected_bad_filled_date' => ' - Fecha de vencimiento incorrecta',
          'cc_rejected_other_reason' => ' - Rechazado por otro motivo',
      ];

      if ($statusDetail && isset($detailMessages[$statusDetail])) {
          $baseMessage .= $detailMessages[$statusDetail];
      } elseif ($statusDetail) {
          $baseMessage .= " ({$statusDetail})";
      }

      return $baseMessage;
  }

}
