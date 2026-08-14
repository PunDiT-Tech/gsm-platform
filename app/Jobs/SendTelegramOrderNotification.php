<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\TelegramService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendTelegramOrderNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(public Order $order, public string $event)
    {
    }

    public function handle(TelegramService $telegram): void
    {
        if (! $telegram->isEventEnabled($this->event)) {
            return;
        }

        $emoji = match ($this->event) {
            'new_order' => '🆕',
            'payment_proof' => '🧾',
            'payment_verified' => '✅',
            'payment_rejected' => '❌',
            'processing' => '🔄',
            'waiting_for_customer' => '📩',
            'completed' => '🎉',
            'cancelled' => '⛔',
            default => '🔔',
        };

        $message = "<b>{$emoji} {$this->event}</b>" . PHP_EOL
            . "Order: <code>{$this->order->order_number}</code>" . PHP_EOL
            . "Service: {$this->order->service_name_snapshot}" . PHP_EOL
            . "Customer: {$this->order->customer_name}" . PHP_EOL
            . "Status: {$this->order->status} / {$this->order->payment_status}" . PHP_EOL
            . "Amount: {$this->order->currency_snapshot} " . number_format((float) $this->order->price_snapshot, 2);

        $telegram->sendOrFail($message);
    }

    public function failed(\Throwable $e): void
    {
        report($e);
    }
}
