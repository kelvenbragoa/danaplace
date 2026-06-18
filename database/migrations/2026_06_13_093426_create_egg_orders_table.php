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
        Schema::create('egg_orders', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name', 100);
            $table->string('customer_tax_id', 18)->nullable();
            $table->string('customer_email', 100)->nullable();
            $table->string('customer_phone', 20)->nullable();
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->foreignId('category_id')->constrained('egg_categories');
            $table->integer('quantity_dozens');
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->enum('status', ['pending', 'approved', 'picked', 'shipped', 'canceled'])->default('pending');
            $table->text('observations')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('egg_orders');
    }
};
