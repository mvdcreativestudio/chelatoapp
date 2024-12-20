<?php

namespace App\Jobs;

use App\Helpers\Helpers;
use App\Models\EventLogProduct;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;

class SendNewOrderAdminNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $storeId;
    protected $data;
    protected $email;
    protected $eventId;

    public function __construct(int $storeId, array $data, string $email, int $eventId)
    {
        $this->storeId = $storeId;
        $this->data = $data;
        $this->email = $email;
        $this->eventId = $eventId;
    }

    public function handle()
    {
        try {
            $subject = "Nuevo pedido recibido en la tienda";

            // Enviar correo
            Helpers::emailService()->sendMail(
                $this->email,
                $subject,
                'emails.ecommerce.admin.new-order',
                null,
                '',
                [
                    'variables' => $this->data['data'],
                ],
                $this->storeId
            );

            Log::channel('emails')->info("Correo de notificación de nuevo pedido enviado a {$this->email} para la tienda {$this->storeId}");

            // Registrar el envío en EventLogProduct
            // EventLogProduct::create([
            //     'store_id' => $this->storeId,
            //     'event_id' => $this->eventId,
            //     'alert_sent_at' => Carbon::now(),
            // ]);

        } catch (Exception $e) {
            Log::channel('emails')->error("Error al enviar notificación de nuevo pedido para la tienda {$this->storeId}", [
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
