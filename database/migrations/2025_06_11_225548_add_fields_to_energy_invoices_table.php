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
        Schema::table('energy_invoices', function (Blueprint $table) {
            //
            $table->decimal('difference', 20, 10)->default(0.00);
            $table->unsignedBigInteger('quantity_houses')->default(0);
            $table->decimal('ponta_plus_fix_rate', 20, 10)->default(0.00);
            $table->decimal('fix_rate_plus_fix_rate_per_house', 20, 10)->default(0.00);
            $table->decimal('rate_per_active_consumption', 20, 10)->default(0.00);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('energy_invoices', function (Blueprint $table) {
            //
        });
    }
};
