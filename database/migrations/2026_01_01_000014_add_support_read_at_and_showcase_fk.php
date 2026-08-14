<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_messages', function (Blueprint $table) {
            $table->timestamp('read_at')->nullable()->after('message');
        });

        Schema::table('homepage_showcases', function (Blueprint $table) {
            $table->foreignId('service_id')
                ->nullable()
                ->change()
                ->after('link_type');
        });

        Schema::table('homepage_showcases', function (Blueprint $table) {
            $table->foreign('service_id')->references('id')->on('services')->nullOnDelete();
            $table->index('service_id');
        });
    }

    public function down(): void
    {
        Schema::table('homepage_showcases', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropIndex(['service_id']);
        });

        Schema::table('support_messages', function (Blueprint $table) {
            $table->dropColumn('read_at');
        });
    }
};
