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
        Schema::create('mcscrs', function (Blueprint $table) {
            $table->id();
            $table->integer('equipment_id');
            $table->integer('type_equipment_id');
            $table->integer('equipment_component_id')->nullable();
            $table->integer('equipment_sub_component_id')->nullable();
            $table->integer('destination_id');
            $table->integer('area_id');
            $table->integer('task_id');
            $table->integer('reason_id')->nullable();
            $table->text('reason')->nullable();
            $table->integer('cause_id')->nullable();
            $table->text('cause')->nullable();
            $table->integer('solution_id')->nullable();
            $table->text('solution')->nullable();
            $table->integer('consequence_id')->nullable();
            $table->text('consequence')->nullable();
            $table->integer('recommendation_id')->nullable();
            $table->text('recommendation')->nullable();
            $table->integer('waiting_status_id')->nullable();
            $table->integer('type_malfunction_id')->nullable();
            $table->integer('mcscr_status_id');
            $table->integer('opened_by_user_id');
            $table->integer('closed_by_user_id')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('output_forecast')->nullable();

            $table->timestamp('diagnosis_start_at')->nullable();
            $table->timestamp('diagnosis_end_at')->nullable();

            $table->timestamp('execution_start_at')->nullable();
            $table->timestamp('execution_end_at')->nullable();

            $table->timestamp('awaiting_approval_start_at')->nullable();
            $table->timestamp('awaiting_approval_end_at')->nullable();
            
            $table->integer('is_rework')->nullable();
            $table->text('first_observation')->nullable();
            $table->text('last_observation')->nullable();
            $table->float('total_hours',15,2)->nullable();
            $table->float('distance',15,2);
            $table->float('material_cost',15,2)->nullable();
            $table->float('material_labor',15,2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mcscrs');
    }
};
