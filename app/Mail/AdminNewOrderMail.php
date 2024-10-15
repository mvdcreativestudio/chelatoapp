<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminNewOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $variables;

    /**
     * Crea una nueva instancia de mensaje.
     *
     * @param $order La orden que se incluirá en el correo.
     * @param array $variables Las variables que se reemplazarán en la plantilla de correo.
     */
    public function __construct($order, $variables)
    {
        $this->order = $order;
        $this->variables = $variables;
    }

    /**
     * Construye el mensaje.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.ecommerce.admin.new-order')
                    ->subject($this->variables['subject'])
                    ->with('variables', $this->variables);
    }
}
