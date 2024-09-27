<?php

namespace App\Jobs;

use App\Models\Order;
use App\Repositories\EmailNotificationsRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendOrderEmailsJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    /**
     * Crea una instancia del job.
     *
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Ejecuta el job.
     *
     * @param EmailNotificationsRepository $emailNotificationsRepository
     * @return void
     */
    public function handle(EmailNotificationsRepository $emailNotificationsRepository)
    {
        $order = $this->order;

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
        $emailNotificationsRepository->sendNewOrderEmail($variables);

        // Enviar correo al cliente
        $emailNotificationsRepository->sendNewOrderClientEmail($variables);
    }
}
