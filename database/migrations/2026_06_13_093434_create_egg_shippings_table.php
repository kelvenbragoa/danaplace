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
        Schema::create('egg_shippings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('egg_orders');
            $table->foreignId('inventory_id')->constrained('egg_inventories');
            $table->date('shipping_date');
            $table->string('invoice_number', 50)->unique();
            $table->string('carrier', 100);
            $table->string('vehicle_plate', 10);
            $table->string('driver_name', 100);
            $table->decimal('vehicle_temperature', 5, 2)->nullable();
            $table->string('seal_number', 50)->nullable();
            $table->string('health_certificate', 100)->nullable();
            $table->foreignId('responsible_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('egg_shippings');
    }
};
