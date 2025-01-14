<?php

namespace App\Jobs;

use App\Helpers\Helpers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;

class SendNewOrderCustomerConfirmationJob implements ShouldQueue
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
            $subject = "Confirmación de tu pedido realizado";

            // Enviar correo
            Helpers::emailService()->sendMail(
                $this->email,
                $subject,
                'emails.ecommerce.customer.new-order-client',
                null,
                '',
                [
                    'variables' => $this->data['data'],
                ],
                $this->storeId
            );

            Log::channel('emails')->info("Correo de confirmación de pedido enviado a {$this->email}.");

        } catch (Exception $e) {
            Log::channel('emails')->error("Error al enviar confirmación de pedido al cliente.", [
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
