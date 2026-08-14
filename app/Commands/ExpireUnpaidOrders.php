<?php

namespace App\Commands;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use App\Notifications\OrderStatusNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireUnpaidOrders extends Command
{
    protected $signature = 'orders:expire-unpaid';

    protected $description = 'Expire unpaid orders past their payment deadline';

    public function handle(): int
    {
        $expired = Order::where('payment_status', 'UNPAID')
            ->where('status', 'PENDING')
            ->where('expires_at', '<', now())
            ->get();

        $count = 0;

        foreach ($expired as $order) {
            DB::transaction(function () use ($order) {
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'from_status' => $order->status,
                    'to_status' => 'CANCELLED',
                    'note' => 'Auto-expired: payment deadline passed.',
                    'created_at' => now(),
                ]);

                $order->update([
                    'status' => 'CANCELLED',
                    'payment_status' => 'REJECTED',
                    'cancelled_at' => now(),
                ]);
            });

            if ($order->customer?->user_id) {
                User::find($order->customer->user_id)?->notify(
                    new OrderStatusNotification($order, 'Order cancelled', 'Your order was automatically cancelled because payment was not completed in time.')
                );
            }

            \App\Jobs\SendTelegramOrderNotification::dispatch($order, 'cancelled');

            $count++;
        }

        $this->info("Expired {$count} unpaid order(s).");

        return self::SUCCESS;
    }
}
