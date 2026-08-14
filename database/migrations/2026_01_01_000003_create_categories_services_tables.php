<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('service_categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('short_description')->nullable();
            $table->longText('full_description')->nullable();
            $table->string('icon')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price', 14, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->string('processing_time')->nullable();
            $table->string('service_type')->default('STANDARD'); // STANDARD | PAID | FREE | EXTERNAL
            $table->boolean('payment_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('customer_notice')->nullable();
            $table->longText('customer_instructions')->nullable();
            $table->longText('admin_internal_notes')->nullable();
            $table->boolean('consent_required')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
        Schema::dropIfExists('service_categories');
    }
};
