<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('internal_name')->nullable();
            $table->string('type'); // TEXT TEXTAREA NUMBER EMAIL PHONE IMEI SERIAL_NUMBER SELECT MULTI_SELECT RADIO CHECKBOX DATE FILE URL
            $table->string('placeholder')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_required')->default(false);
            $table->string('validation')->nullable();
            $table->string('validation_regex')->nullable();
            $table->unsignedInteger('min_length')->nullable();
            $table->unsignedInteger('max_length')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('service_id');
        });

        Schema::create('service_field_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_field_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('value');
            $table->unsignedInteger('sort_order')->default(0);

            $table->index('service_field_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_field_options');
        Schema::dropIfExists('service_fields');
    }
};
