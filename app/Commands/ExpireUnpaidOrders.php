<?php

namespace App\Commands;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Console\Command;

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

            $count++;
        }

        $this->info("Expired {$count} unpaid order(s).");

        return self::SUCCESS;
    }
}
