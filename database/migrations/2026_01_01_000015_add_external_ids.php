<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('external_id')->nullable()->unique()->after('slug');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('external_id')->nullable()->unique()->after('order_number');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('external_id');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('external_id');
        });
    }
};