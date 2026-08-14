<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('egg_shipping_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('egg_shipping_id')->constrained('egg_shippings')->cascadeOnDelete();
            $table->foreignId('inventory_id')->constrained('egg_inventories');
            $table->unsignedInteger('quantity');
            $table->timestamps();

            $table->index(['egg_shipping_id', 'inventory_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('egg_shipping_items');
    }
};
