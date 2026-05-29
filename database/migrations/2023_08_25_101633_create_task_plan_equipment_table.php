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
        Schema::create('task_plan_equipment', function (Blueprint $table) {
            $table->id();
            $table->integer('task_plan_id');
            $table->integer('equipment_id');
            $table->integer('type_equipment_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_plan_equipment');
    }
};
