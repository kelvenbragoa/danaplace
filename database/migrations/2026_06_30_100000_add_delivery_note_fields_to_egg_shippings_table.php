<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('egg_shippings', function (Blueprint $table) {
            $table->string('delivery_note_number', 50)->nullable()->after('health_certificate');
            $table->string('delivered_to', 100)->nullable()->after('delivery_note_number');
            $table->dateTime('delivered_at')->nullable()->after('delivered_to');
        });
    }

    public function down(): void
    {
        Schema::table('egg_shippings', function (Blueprint $table) {
            $table->dropColumn(['delivery_note_number', 'delivered_to', 'delivered_at']);
        });
    }
};
