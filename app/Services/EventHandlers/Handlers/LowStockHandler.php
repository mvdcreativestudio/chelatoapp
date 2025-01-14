<?php
namespace App\Services\EventHandlers\Handlers;

use App\Jobs\SendLowStockAlertJob;
use App\Models\EventLogProduct;
use App\Models\Product;
use App\Repositories\StoreRepository;
use App\Services\EventHandlers\Interface\EventHandlerInterface;
use Illuminate\Support\Facades\Log;
use Exception;

class LowStockHandler implements EventHandlerInterface
{
    protected $storeRepository;

    public function __construct(StoreRepository $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }

    public function handle(int $storeId, array $data = [])
    {
        try {
            $order = $data['order'];
            $eventId = $data['event_id'];

            // Verificar que los datos necesarios estén presentes
            if (!isset($order['products']) || !isset($eventId)) {
                Log::error("Datos insuficientes para manejar el evento de bajo stock para la tienda {$storeId}");
                throw new Exception("Datos insuficientes para manejar el evento de bajo stock", 1);
            }

            // Recorrer cada producto en la orden
            foreach ($order['products'] as $productData) {
                $productId = $productData['id'];

                // Verificar si ya se envió la alerta para este producto y evento
                $existingLog = EventLogProduct::where('store_id', $storeId)
                    ->where('event_id', $eventId)
                    ->where('product_id', $productId)
                    ->whereNotNull('alert_sent_at')
                    ->first();

                if ($existingLog) {
                    Log::info("Alerta de bajo stock ya enviada para el producto {$productId} en la tienda {$storeId}");
                    continue;
                }

                // Verificar si el stock actual está por debajo del margen de seguridad
                $product = Product::findOrFail($productId);

                if ($product->stock > $product->safety_margin) {
                    Log::info("El stock actual para el producto {$productId} aún no está por debajo del margen de seguridad.");
                    continue;
                }

                // Obtener el email de la tienda
                $email = $this->storeRepository->getStoreByUserId(auth()->id())->email;

                // Despachar el trabajo para enviar el correo
                SendLowStockAlertJob::dispatch($storeId, $product, $email, $eventId);
                Log::info("Trabajo de alerta de bajo stock despachado para el producto {$productId} en la tienda {$storeId}");
            }

        } catch (Exception $e) {
            Log::error("Error al manejar el evento de bajo stock para la tienda {$storeId}", [
                'exception' => $e->getMessage(),
            ]);
            throw new Exception("Error al manejar el evento de bajo stock: " . $e->getMessage(), 0, $e);
        }
    }
}