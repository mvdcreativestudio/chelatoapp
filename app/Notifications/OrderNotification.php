<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use App\Models\Order;

class OrderNotification extends Notification
{
    use Queueable;

    protected $order;

    /**
     * Create a new notification instance.
     *
     * @param Order $order
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'order_uuid' => $this->order->uuid,
            'customer_name' => $this->order->client->name,
            'customer_lastname' => $this->order->client->lastname,
            'address' => $this->order->client->address,
            'payment_method' => $this->order->payment_method,
            'status' => $this->order->payment_status,
            'amount' => $this->order->total,
            'created_at' => $this->order->created_at->toDateTimeString(),
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     *
     * @param mixed $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'order_id' => $this->order->id,
            'order_uuid' => $this->order->uuid,
            'customer_name' => $this->order->client->name,
            'customer_lastname' => $this->order->client->lastname,
            'address' => $this->order->client->address,
            'payment_method' => $this->order->payment_method,
            'status' => $this->order->payment_status,
            'amount' => $this->order->total,
            'created_at' => $this->order->created_at->toDateTimeString(),
        ]);
    }
}
