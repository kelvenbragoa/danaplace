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
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('equipment_id');
            $table->unsignedBigInteger('type_equipment_id');
            $table->unsignedBigInteger('destination_id');
            $table->unsignedBigInteger('area_id');
            $table->unsignedBigInteger('is_operational')->nullable();
            $table->float('total_hours',15,2)->nullable();

            $table->unsignedBigInteger('engine_condition')->nullable();
            $table->text('engine_description')->nullable();

            $table->unsignedBigInteger('eletrical_system_condition')->nullable();
            $table->text('eletrical_system_description')->nullable();

            $table->unsignedBigInteger('transmission_condition')->nullable();
            $table->text('transmission_description')->nullable();

            $table->unsignedBigInteger('control_system_condition')->nullable();
            $table->text('control_system_description')->nullable();

            $table->unsignedBigInteger('structure_condition')->nullable();
            $table->text('structure_description')->nullable();

            $table->unsignedBigInteger('hydraulic_system_condition')->nullable();
            $table->text('hydraulic_system_description')->nullable();

            $table->unsignedBigInteger('pneumatic_system_condition')->nullable();
            $table->text('pneumatic_system_description')->nullable();

            $table->unsignedBigInteger('suspension_condition')->nullable();
            $table->text('suspension_description')->nullable();

            $table->unsignedBigInteger('tyres_condition')->nullable();
            $table->text('tyres_description')->nullable();

            $table->unsignedBigInteger('blades_condition')->nullable();
            $table->text('blades_description')->nullable();

            $table->unsignedBigInteger('cabin_condition')->nullable();
            $table->text('cabin_description')->nullable();

            $table->unsignedBigInteger('others_condition')->nullable();
            $table->text('others_description')->nullable();

            $table->unsignedBigInteger('rating_unit_condition')->nullable();

            $table->unsignedBigInteger('rating_in_operation')->nullable();

            $table->text('comments')->nullable();

            $table->text('recommendation_1')->nullable();
            $table->text('recommendation_2')->nullable();
            $table->text('recommendation_3')->nullable();
            $table->text('recommendation_4')->nullable();


            $table->unsignedBigInteger('inspection_status_id');

            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->unsignedBigInteger('closed_by_user_id')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
