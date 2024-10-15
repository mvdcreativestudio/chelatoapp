<?php

namespace App\Services;

use App\Models\EmailTemplate;
use App\Models\EcommerceSetting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class EmailService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('app.url');
    }

    private function sendTemplateEmail(string $templateName, array $variables, string $recipient)
    {
        try {
            $subject = $variables['subject'] ?? 'Correo de Notificación';
            $replyTo = $variables['reply_to'] ?? 'default_reply_to@example.com';

            $variables['order_items'] = $this->generateOrderItemsHtml(json_decode($variables['order_items'], true));
            $variables['year'] = date('Y');

            $body = View::make('emails.' . str_replace('_', '.', $templateName), $variables)->render();

            Mail::send([], [], function ($message) use ($recipient, $subject, $body, $replyTo) {
                $message->to($recipient)
                    ->subject($subject)
                    ->replyTo($replyTo)
                    ->html($body);
            });

            Log::info('Correo enviado a ' . $recipient . ' con el asunto ' . $subject);
        } catch (\Exception $e) {
            Log::error('Error enviando correo: ' . $e->getMessage());
        }
    }

    private function generateOrderItemsHtml(array $items): string
    {
        $html = '';
        foreach ($items as $item) {
            $html .= '
                <tr>
                    <td>' . $item['name'] . '</td>
                    <td>' . $item['quantity'] . '</td>
                    <td>$' . $item['price'] . '</td>
                    <td>$' . ($item['quantity'] * $item['price']) . '</td>
                </tr>';
        }
        return $html;
    }

    public function sendNewOrderEmail(array $variables)
    {
        try {
            $ecommerceSettings = EcommerceSetting::firstOrFail();
            $recipient = $ecommerceSettings->notifications_email;
            $this->sendTemplateEmail('ecommerce.admin.new-order', $variables, $recipient);
        } catch (\Exception $e) {
            Log::error('Error obteniendo configuraciones de ecommerce: ' . $e->getMessage());
        }
    }

    public function sendNewOrderClientEmail(array $variables)
    {
        if (isset($variables['client_email'])) {
            $recipient = $variables['client_email'];
            $this->sendTemplateEmail('ecommerce.customer.new-order-client', $variables, $recipient);
        } else {
            Log::error('El email del cliente no está definido en las variables.');
        }
    }

    public function sendPaymentStatusUpdateClientEmail(array $variables)
    {
        if (isset($variables['client_email'])) {
            $recipient = $variables['client_email'];
            $this->sendTemplateEmail('ecommerce.customer.payment-status-update-client', $variables, $recipient);
        } else {
            Log::error('El email del cliente no está definido en las variables.');
        }
    }


    public function sendCashRegisterOpenedEmail(array $variables)
    {
        try {
            $ecommerceSettings = EcommerceSetting::firstOrFail();
            $recipient = $ecommerceSettings->notifications_email;
            $this->sendTemplateEmail('cash-register.open', $variables, $recipient);
        } catch (\Exception $e) {
            Log::error('Error enviando correo: ' . $e->getMessage());
        }
    }

    public function sendCashRegisterClosedEmail(array $variables)
    {
        try {
            $ecommerceSettings = EcommerceSetting::firstOrFail();
            $recipient = $ecommerceSettings->notifications_email;
            $this->sendTemplateEmail('cash-register.close', $variables, $recipient);
        } catch (\Exception $e) {
            Log::error('Error enviando correo: ' . $e->getMessage());
        }
    }
}
