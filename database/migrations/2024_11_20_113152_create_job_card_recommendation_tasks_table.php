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
        Schema::create('job_card_recommendation_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mcscr_id')->nullable();
            $table->unsignedBigInteger('task_mcscr_id')->nullable();
            $table->unsignedBigInteger('generated_mcscr_id')->nullable();
            $table->unsignedBigInteger('status_id');
            $table->unsignedBigInteger('destination_id')->nullable();
            $table->unsignedBigInteger('area_id')->nullable();
            $table->unsignedBigInteger('equipment_id')->nullable();
            $table->unsignedBigInteger('type_equipment_id')->nullable();
            $table->text('task')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_card_recommendation_tasks');
    }
};
