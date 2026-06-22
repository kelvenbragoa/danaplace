<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('flocks', function (Blueprint $table) {
            //
            $table->decimal('daily_feed_consumption_kg', 10, 2)->default(0);
            $table->decimal('daily_water_consumption_liters', 10, 2)->default(0);
            $table->decimal('daily_light_hours', 5, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flocks', function (Blueprint $table) {
            //
            $table->dropColumn('daily_feed_consumption_kg');
            $table->dropColumn('daily_water_consumption_liters');
            $table->dropColumn('daily_light_hours');
        });
    }
};
