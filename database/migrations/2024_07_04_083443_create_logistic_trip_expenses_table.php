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
        Schema::create('logistic_trip_expenses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('trip_id');
            $table->bigInteger('destination_expense_id')->nullable();
            $table->string('expense_description');
            $table->double('expense_amount',8,2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logistic_trip_expenses');
    }
};
