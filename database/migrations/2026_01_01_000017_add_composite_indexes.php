<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['status', 'payment_status'], 'orders_status_payment_status_index');
            $table->index(['status', 'created_at'], 'orders_status_created_at_index');
            $table->index('created_at', 'orders_created_at_index');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index(['order_id', 'status'], 'payments_order_id_status_index');
            $table->index('created_at', 'payments_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_created_at_index');
            $table->dropIndex('payments_order_id_status_index');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_created_at_index');
            $table->dropIndex('orders_status_created_at_index');
            $table->dropIndex('orders_status_payment_status_index');
        });
    }
};
