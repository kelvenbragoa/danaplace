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
        Schema::create('request_stocks', function (Blueprint $table) {
            $table->id();
            $table->integer('task_mcscr_id')->nullable();
            $table->integer('mcscr_id')->nullable();
            $table->text('first_observation')->nullable();
            $table->text('final_observation')->nullable();
            $table->integer('created_by_user_id')->nullable();
            $table->integer('approved_by_user_id')->nullable();
            $table->integer('delivered_by_user_id')->nullable();
            $table->integer('request_stock_status_id')->nullable();
            $table->timestamp('approved_date')->nullable();
            $table->timestamp('delivered_date')->nullable();
            $table->timestamp('schedule_for')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_stocks');
    }
};
