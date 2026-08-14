<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order, public string $title, public string $message)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title)
            ->greeting('Hello ' . ($notifiable->name ?? 'there') . ',')
            ->line($this->message)
            ->line('Order: ' . $this->order->order_number)
            ->action('View order', url(route('orders.show', $this->order, false)))
            ->line('Thank you for using ' . config('app.name') . '.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
        ];
    }
}
