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
        Schema::create('task_mcscr_departments', function (Blueprint $table) {
            $table->id();
            $table->integer('task_mcscr_id');
            $table->integer('task_plan_task_department_id');
            $table->integer('task_plan_task_id');
            $table->integer('task_plan_id');
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_mcscr_departments');
    }
};
