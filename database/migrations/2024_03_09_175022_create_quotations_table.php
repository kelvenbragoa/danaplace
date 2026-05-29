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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->date('expires_date');
            $table->float('total_amount',10,2)->nullable();
            $table->text('obs')->nullable();
            $table->unsignedBigInteger('destination_id');
            $table->unsignedBigInteger('created_by_user_id');
            $table->unsignedBigInteger('status_quotation_id');
            $table->timestamps();

            // $table->foreign('destination_id')->references('id')->on('destinations');
            // $table->foreign('created_by_user_id')->references('id')->on('users');
            // $table->foreign('status_quotation_id')->references('id')->on('status_quotations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
