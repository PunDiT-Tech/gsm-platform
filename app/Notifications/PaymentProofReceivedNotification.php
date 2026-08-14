<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PaymentProofReceivedNotification extends Notification implements ShouldQueue
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
            ->subject('Payment proof received: ' . $this->order->order_number)
            ->greeting('Hello ' . ($this->order->customer_name ?? 'there') . ',')
            ->line('We have received your payment proof for order ' . $this->order->order_number . '.')
            ->line('Service: ' . $this->order->service_name_snapshot)
            ->line('Amount: ' . $this->order->currency_snapshot . ' ' . number_format((float) $this->order->price_snapshot, 2))
            ->line('Our team will review and confirm your payment shortly.')
            ->action('Track order', url('/check-order'))
            ->line('Thank you for using ' . config('app.name') . '.');
    }
}