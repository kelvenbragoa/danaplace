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
        Schema::table('logistic_trips', function (Blueprint $table) {
            //
            $table->bigInteger('customer_id')->nullable();
            $table->bigInteger('quotation_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logistic_trips', function (Blueprint $table) {
            //
            $table->dropColumn('customer_id');
            $table->dropColumn('quotation_id');
        });
    }
};
