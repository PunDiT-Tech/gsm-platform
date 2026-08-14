<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderConfirmationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): \Illuminate\Notifications\Messages\MailMessage
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Order received: ' . $this->order->order_number)
            ->greeting('Hello ' . ($this->order->customer_name ?? 'there') . ',')
            ->line('Thank you. Your order has been received and is now ' . str_replace('_', ' ', $this->order->status) . '.')
            ->line('Service: ' . $this->order->service_name_snapshot)
            ->line('Amount: ' . $this->order->currency_snapshot . ' ' . number_format((float) $this->order->price_snapshot, 2))
            ->line('Keep your order number and tracking code to follow progress.')
            ->action('Track order', url('/check-order'))
            ->line('Thank you for using ' . config('app.name') . '.');
    }
}
