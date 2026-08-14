<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('tracking_token', 64)->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('service_name_snapshot')->nullable();
            $table->decimal('price_snapshot', 14, 2)->default(0);
            $table->string('currency_snapshot', 3)->default('USD');
            $table->string('status')->default('PENDING'); // PENDING PROCESSING WAITING_FOR_CUSTOMER COMPLETED REJECTED CANCELLED
            $table->string('payment_status')->default('UNPAID'); // UNPAID PROOF_SUBMITTED UNDER_REVIEW VERIFIED REJECTED REFUNDED
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('coupon_code')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('customer_id');
            $table->index('service_id');
            $table->index('status');
            $table->index('payment_status');
        });

        Schema::create('order_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_field_id')->nullable()->constrained()->nullOnDelete();
            $table->string('label');
            $table->text('value');
            $table->string('file_path')->nullable();
            $table->timestamps();

            $table->index('order_id');
        });

        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['order_id', 'created_at']);
        });

        Schema::create('order_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // CUSTOMER | ADMIN | INTERNAL
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('message');
            $table->string('attachment_path')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index('order_id');
        });

        Schema::create('order_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // TEXT LINK FILE CODE INSTRUCTIONS
            $table->text('content')->nullable();
            $table->string('file_path')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_results');
        Schema::dropIfExists('order_messages');
        Schema::dropIfExists('order_status_history');
        Schema::dropIfExists('order_field_values');
        Schema::dropIfExists('orders');
    }
};
