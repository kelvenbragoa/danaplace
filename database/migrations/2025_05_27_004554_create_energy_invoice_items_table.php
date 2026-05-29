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
        Schema::create('energy_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('energy_invoice_id');
            $table->unsignedBigInteger('equipment_id');
            $table->unsignedBigInteger('destination_id');
            $table->decimal('apr_consumption', 20, 10)->default(0.00);
            $table->decimal('meter', 20, 10)->default(0.00);
            $table->decimal('cost', 20, 10)->default(0.00);
            $table->decimal('percentage_value', 20, 10)->default(0.00);
            $table->decimal('tax_iva', 20, 10)->default(0.00);
            $table->decimal('total', 20, 10)->default(0.00);
            $table->decimal('total_to_invoice', 20, 10)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('energy_invoice_items');
    }
};
