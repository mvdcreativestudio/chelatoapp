<?php

namespace App\Jobs;

use App\Helpers\Helpers;
use App\Models\EventLogProduct;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;

class SendLowStockAlertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $storeId;
    protected $product;
    protected $email;
    protected $eventId;

    public function __construct(int $storeId, Product $product, string $email, int $eventId)
    {
        $this->storeId = $storeId;
        $this->product = $product;
        $this->email = $email;
        $this->eventId = $eventId;
    }

    public function handle()
    {
        try {
            // Enviar correo
            $subject = "Alerta de bajo stock para el producto {$this->product->name}";

            Helpers::emailService()->sendMail(
                $this->email,
                $subject,
                'events.low_stock',
                null,
                '',
                [
                    'product' => $this->product,
                    'currentStock' => $this->product->stock,
                ],
                $this->storeId
            );

            Log::info("Correo de alerta de bajo stock enviado para el producto {$this->product->id} en la tienda {$this->storeId}");

            // Registrar el envío en la tabla EventLogProduct
            EventLogProduct::create([
                'store_id' => $this->storeId,
                'event_id' => $this->eventId,
                'product_id' => $this->product->id,
                'alert_sent_at' => Carbon::now(),
            ]);

        } catch (Exception $e) {
            Log::error("Error al enviar correo de alerta de bajo stock para el producto {$this->product->id} en la tienda {$this->storeId}", [
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
