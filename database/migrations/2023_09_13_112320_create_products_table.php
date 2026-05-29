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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->integer('product_brand_id');
            $table->integer('product_category_id');
            $table->integer('unit_id');
            $table->integer('tax_iva_id');
            $table->float('quantity',8,2);
            $table->float('stock_min',8,2);
            $table->float('unity_price',8,2);
            $table->float('unity_buy_price',8,2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
