<?php

namespace App\Services\EventHandlers\Handlers;

use App\Jobs\SendNewOrderCustomerConfirmationJob;
use App\Models\EventLogProduct;
use App\Repositories\StoreRepository;
use App\Services\EventHandlers\Interface\EventHandlerInterface;
use Illuminate\Support\Facades\Log;
use Exception;

class NewOrderCustomerConfirmationEccommerceHandler implements EventHandlerInterface
{
    protected $storeRepository;

    public function __construct(StoreRepository $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }
    
    // public function handle(int $storeId, array $data = [])
    // {
    //     try {
    //         $order = $data['order'];
    //         $eventId = $data['event_id'];

    //         if (!isset($order['customer_email']) || !isset($eventId)) {
    //             Log::error("Datos insuficientes para manejar la confirmación de nuevo pedido para cliente.");
    //             throw new Exception("Datos insuficientes para manejar la confirmación.");
    //         }

    //         $email = $order['customer_email'];

    //         // Despachar el trabajo para enviar la confirmación
    //         SendNewOrderCustomerConfirmationJob::dispatch($storeId, $order, $email, $eventId);
    //         Log::info("Trabajo de confirmación de nuevo pedido despachado para el cliente.");

    //     } catch (Exception $e) {
    //         Log::error("Error al manejar la confirmación de nuevo pedido para cliente.", [
    //             'exception' => $e->getMessage(),
    //         ]);
    //         throw $e;
    //     }
    // }

    public function handle(int $storeId, array $data = [])
    {
        try {
            $eventId = $data['event_id'];

            if (!isset($eventId)) {
                Log::channel('emails')->error("Datos insuficientes para manejar la notificación de nuevo pedido en tienda {$storeId}");
                throw new Exception("Datos insuficientes para manejar la notificación");
            }

            // Obtener email de la tienda
            $email = $this->storeRepository->getStoreById($storeId)->email;

            // Despachar el trabajo para enviar la notificación
            SendNewOrderCustomerConfirmationJob::dispatch($storeId, $data, $email, $eventId);
            Log::channel('emails')->info("Trabajo de notificación de nuevo pedido despachado para la tienda {$storeId}");

        } catch (Exception $e) {
            dd($e);
            Log::channel('emails')->error("Error al manejar la notificación de nuevo pedido para la tienda {$storeId}", [
                'exception' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
