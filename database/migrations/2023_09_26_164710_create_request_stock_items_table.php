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
        Schema::create('request_stock_items', function (Blueprint $table) {
            $table->id();
            $table->integer('request_stock_id');
            $table->integer('product_id');
            $table->integer('stock_center_id');
            $table->float('quantity',10,2);
            $table->float('delivered_quantity',10,2)->nullable();
            $table->text('obs')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_stock_items');
    }
};
