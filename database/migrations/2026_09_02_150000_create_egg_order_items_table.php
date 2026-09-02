<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('egg_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('egg_orders')->cascadeOnDelete();
            $table->foreignId('inventory_id')->constrained('egg_inventories');
            $table->unsignedInteger('quantity');
            $table->timestamps();

            $table->index(['order_id', 'inventory_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('egg_order_items');
    }
};
