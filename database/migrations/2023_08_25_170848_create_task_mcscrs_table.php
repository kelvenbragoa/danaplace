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
        Schema::create('task_mcscrs', function (Blueprint $table) {
            $table->id();
            $table->integer('equipment_id');
            $table->integer('type_equipment_id');
            $table->integer('destination_id');
            $table->integer('area_id');
            $table->float('distance',15,2)->nullable();
            $table->float('total_hours',15,2)->nullable();
            $table->float('material_cost',15,2)->nullable();
            $table->float('material_labor',15,2)->nullable();
            $table->integer('task_plan_id');
            $table->integer('task_plan_task_id');
            $table->integer('task_mcscr_status_id');
            $table->integer('closed_by_user_id')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('schedule_for')->nullable();
            $table->integer('opened_by_user_id')->nullable();
            $table->integer('schedule_by_user_id')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->text('observation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_mcscrs');
    }
};
