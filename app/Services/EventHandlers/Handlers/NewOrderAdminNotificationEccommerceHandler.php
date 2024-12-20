<?php

namespace App\Services\EventHandlers\Handlers;

use App\Jobs\SendNewOrderAdminNotificationJob;
use App\Models\EventLogProduct;
use App\Repositories\StoreRepository;
use App\Services\EventHandlers\Interface\EventHandlerInterface;
use Illuminate\Support\Facades\Log;
use Exception;

class NewOrderAdminNotificationEccommerceHandler implements EventHandlerInterface
{
    protected $storeRepository;

    public function __construct(StoreRepository $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }


    
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
            SendNewOrderAdminNotificationJob::dispatch($storeId, $data, $email, $eventId);
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
