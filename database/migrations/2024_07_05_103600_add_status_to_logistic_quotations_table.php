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
        Schema::table('logistic_quotations', function (Blueprint $table) {
            //
            $table->bigInteger('status_logistic_quotation_id')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logistic_quotations', function (Blueprint $table) {
            //
            $table->dropColumn('status_logistic_quotation_id');
        });
    }
};
