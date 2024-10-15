<?php

namespace App\Repositories;

use App\Mail\AdminNewOrderMail;
use App\Mail\ClientNewOrderMail;
use App\Models\EmailTemplate;
use App\Models\EcommerceSetting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailNotificationsRepository
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = 'https://chelato.mvdstudio.com.uy';
    }

    /**
     * Envía un correo electrónico basado en una plantilla.
     *
     * @param string $templateName
     * @param array $variables
     * @param string $recipient
     */
    private function sendTemplateEmail(string $templateName, array $variables, string $recipient)
{
    try {
        Log::info('Iniciando envío de correo', ['templateName' => $templateName, 'variables' => $variables, 'recipient' => $recipient]);

        $template = EmailTemplate::where('name', $templateName)->firstOrFail();
        Log::info('Plantilla encontrada', ['template' => $template]);

        if (isset($variables['order_items'])) {
            $variables['order_items'] = $this->generateOrderItemsHtml(json_decode($variables['order_items'], true));
        } else {
            Log::error('La clave order_items no está presente en las variables');
        }

        $variables['year'] = date('Y');

        $subject = $this->replaceVariables($template->subject, $variables);
        Log::info('Asunto del correo generado', ['subject' => $subject]);

        $replyTo = $template->reply_to ?? 'default_reply_to@example.com';
        $viewName = $this->getViewNameByTemplateName($templateName);
        Log::info('Nombre de la vista obtenido', ['viewName' => $viewName]);

        $body = view($viewName, $variables)->render();
        Log::info('Cuerpo del correo renderizado');

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



    /**
     * Reemplaza las variables en el contenido de la plantilla.
     *
     * @param string $content
     * @param array $variables
     * @return string
     */
    private function replaceVariables(string $content, array $variables): string
    {
        Log::info('Contenido original:', ['content' => $content]);
        Log::info('Variables para reemplazo:', ['variables' => $variables]);

        foreach ($variables as $key => $value) {
            $content = str_replace('{{ ' . $key . ' }}', $value, $content);
        }

        Log::info('Contenido después del reemplazo:', ['content' => $content]);
        return $content;
    }


    /**
     * Genera el HTML para los productos en la orden.
     *
     * @param array $items
     * @return string
     */
    private function generateOrderItemsHtml(array $items): string
    {
        Log::info('Generando HTML para los ítems del pedido', ['items' => $items]);
        $html = '';
        foreach ($items as $item) {
            if (is_array($item)) {
                $html .= '
                    <tr>
                        <td>
                            <img src="' . $this->baseUrl . $item['image'] . '" alt="' . $item['name'] . '" style="max-width: 70px; max-height: 70px;">
                        </td>
                        <td style="font-size: 1em;">' . $item['name'] . '</td>
                        <td>' . $item['quantity'] . '</td>
                        <td>$' . $item['price'] . '</td>
                    </tr>';
            } else {
                Log::error('Ítem no es un arreglo', ['item' => $item]);
            }
        }
        return $html;
    }


    /**
     * Envía un correo de nueva orden al administrador de la tienda.
     *
     * @param array $variables
     */
    public function sendNewOrderEmail(array $variables)
    {
        try {
            $ecommerceSettings = EcommerceSetting::firstOrFail();
            $recipient = $ecommerceSettings->notifications_email;

            $template = EmailTemplate::where('name', 'new-order')->firstOrFail();
            $variables['subject'] = $this->replaceVariables($template->subject, $variables);

            $variables['order_items'] = $this->generateOrderItemsHtml(json_decode($variables['order_items'], true));

            Mail::to($recipient)->send(new AdminNewOrderMail(null, $variables));

            Log::info('Correo enviado a ' . $recipient . ' con el asunto ' . $variables['subject']);
        } catch (\Exception $e) {
            Log::error('Error enviando correo: ' . $e->getMessage());
        }
    }

    /**
     * Envía un correo de nueva orden al cliente.
     *
     * @param array $variables
     */
    public function sendNewOrderClientEmail(array $variables)
    {
        if (isset($variables['client_email'])) {
            $recipient = $variables['client_email'];

            $template = EmailTemplate::where('name', 'new-order-client')->firstOrFail();
            $variables['subject'] = $this->replaceVariables($template->subject, $variables);
            $variables['order_items'] = $this->generateOrderItemsHtml(json_decode($variables['order_items'], true));


            Mail::to($recipient)->send(new ClientNewOrderMail(null, $variables));

            Log::info('Correo enviado a ' . $recipient . ' con el asunto ' . $variables['subject']);
        } else {
            Log::error('El email del cliente no está definido en las variables.');
        }
    }

    /**
     * Envía un correo de actualización de producto al administrador de la tienda.
     *
     * @param array $variables
     */
    public function sendProductUpdateEmail(array $variables)
    {
        try {
            $ecommerceSettings = EcommerceSetting::firstOrFail();
            $recipient = $ecommerceSettings->notifications_email;

            $template = EmailTemplate::where('name', 'product-update')->firstOrFail();
            $variables['subject'] = $this->replaceVariables($template->subject, $variables);

            Mail::to($recipient)->send(new ProductUpdateMail(null, $variables));

            Log::info('Correo enviado a ' . $recipient . ' con el asunto ' . $variables['subject']);
        } catch (\Exception $e) {
            Log::error('Error enviando correo: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene el nombre de la vista correspondiente a una plantilla.
     *
     * @param string $templateName
     * @return string
     */
    private function getViewNameByTemplateName(string $templateName): string
    {
        $viewMap = [
            'new-order' => 'emails.ecommerce.admin.new-order',
            'new-order-client' => 'emails.ecommerce.customer.new-order-client',
            'product-update' => 'emails.ecommerce.admin.product-update',
        ];

        return $viewMap[$templateName] ?? 'emails.default';
    }
}
