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
        Schema::create('task_plan_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('task_plan_id');
            $table->integer('type_task_id');
            $table->integer('critical_id');
            $table->integer('estimated_time_days');
            $table->integer('estimated_time_hours');
            $table->integer('estimated_time_minutes');
            $table->integer('unavailable_equipment_time_days');
            $table->integer('unavailable_equipment_time_hours');
            $table->integer('unavailable_equipment_time_minutes');
            $table->integer('do_every');
            $table->integer('frequency_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_plan_tasks');
    }
};
