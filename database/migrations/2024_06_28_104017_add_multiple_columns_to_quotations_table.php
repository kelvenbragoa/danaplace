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
        Schema::table('quotations', function (Blueprint $table) {
            //
            $table->bigInteger('equipment_id')->nullable();
            $table->string('type_of_transport')->nullable();
            $table->bigInteger('coin_id')->nullable();
            $table->string('payment_method')->nullable();
            $table->date('warranty')->nullable();
            $table->date('delivery_date')->nullable();
            $table->float('total_discount',10,2)->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            //
        });
    }
};
