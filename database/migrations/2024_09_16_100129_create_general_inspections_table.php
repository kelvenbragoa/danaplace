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
        Schema::create('general_inspections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('equipment_id');
            $table->unsignedBigInteger('type_equipment_id');
            $table->unsignedBigInteger('destination_id');
            $table->unsignedBigInteger('area_id');
            $table->unsignedBigInteger('is_operational')->nullable();
            $table->float('total_hours',15,2)->nullable();

            $table->unsignedBigInteger('interior')->nullable();
            $table->text('interior_description')->nullable();

            $table->unsignedBigInteger('seats')->nullable();
            $table->text('seats_description')->nullable();

            $table->unsignedBigInteger('interior_trim_roof_lining_carpet')->nullable();
            $table->text('interior_trim_roof_lining_carpet_description')->nullable();

            $table->unsignedBigInteger('dashboard_cluster')->nullable();
            $table->text('dashboard_cluster_description')->nullable();

            $table->unsignedBigInteger('heating_ventilation')->nullable();
            $table->text('heating_ventilation_description')->nullable();

            $table->unsignedBigInteger('interior_control_unitis')->nullable();
            $table->text('interior_control_unitis_description')->nullable();

            $table->unsignedBigInteger('air_condition')->nullable();
            $table->text('air_condition_description')->nullable();

            $table->unsignedBigInteger('eletric_windows')->nullable();
            $table->text('eletric_windows_description')->nullable();

            $table->unsignedBigInteger('eletric_sunroof')->nullable();
            $table->text('eletric_sunroof_description')->nullable();

            $table->unsignedBigInteger('seat_heaters')->nullable();
            $table->text('seat_heaters_description')->nullable();

            $table->unsignedBigInteger('rims')->nullable();
            $table->text('rims_description')->nullable();

            $table->unsignedBigInteger('mechanical_doors')->nullable();
            $table->text('mechanical_doors_description')->nullable();

            $table->unsignedBigInteger('vehicle_body')->nullable();
            $table->text('vehicle_body_description')->nullable();

            $table->unsignedBigInteger('windows')->nullable();
            $table->text('windows_description')->nullable();

            $table->unsignedBigInteger('hang_on_parts')->nullable();
            $table->text('hang_on_parts_description')->nullable();

            $table->unsignedBigInteger('spare_wheel')->nullable();
            $table->text('spare_wheel_description')->nullable();

            $table->unsignedBigInteger('tires')->nullable();
            $table->text('tires_description')->nullable();

            $table->unsignedBigInteger('engine_oil')->nullable();
            $table->text('engine_oil_description')->nullable();

            $table->unsignedBigInteger('engine_cooling_system')->nullable();
            $table->text('engine_cooling_system_description')->nullable();

            $table->unsignedBigInteger('oil_loss_engine')->nullable();
            $table->text('oil_loss_engine_description')->nullable();

            $table->unsignedBigInteger('oil_loss_gear_box')->nullable();
            $table->text('oil_loss_gear_box_description')->nullable();

            $table->unsignedBigInteger('exhaust_system')->nullable();
            $table->text('exhaust_system_description')->nullable();

            $table->unsignedBigInteger('gearshift')->nullable();
            $table->text('gearshift_description')->nullable();

            $table->unsignedBigInteger('noise_levels_engine')->nullable();
            $table->text('noise_levels_engine_description')->nullable();

            $table->unsignedBigInteger('noise_levels_transmissions')->nullable();
            $table->text('noise_levels_transmissions_description')->nullable();

            $table->unsignedBigInteger('noise_levels_axles')->nullable();
            $table->text('noise_levels_axles_description')->nullable();

            $table->unsignedBigInteger('engine')->nullable();
            $table->text('engine_description')->nullable();

            $table->unsignedBigInteger('gearbox')->nullable();
            $table->text('gearbox_description')->nullable();

            $table->unsignedBigInteger('drivetrain')->nullable();
            $table->text('drivetrain_description')->nullable();

            $table->unsignedBigInteger('brake_fluid')->nullable();
            $table->text('brake_fluid_description')->nullable();

            $table->unsignedBigInteger('brakes')->nullable();
            $table->text('brakes_description')->nullable();

            $table->unsignedBigInteger('brake_system')->nullable();
            $table->text('brake_system_description')->nullable();

            $table->unsignedBigInteger('vehicle_undercarriage')->nullable();
            $table->text('vehicle_undercarriage_description')->nullable();

            $table->unsignedBigInteger('axles_suspension')->nullable();
            $table->text('axles_suspension_description')->nullable();

            $table->float('front_left',10,2)->nullable();
            $table->float('front_right',10,2)->nullable();
            $table->float('front_axle_weight',10,2)->nullable();
            $table->float('front_deceleration',10,2)->nullable();

            $table->float('rear_left',10,2)->nullable();
            $table->float('rear_right',10,2)->nullable();
            $table->float('rear_axle_weight',10,2)->nullable();
            $table->float('rear_deceleration',10,2)->nullable();

            $table->float('emergency_left',10,2)->nullable();
            $table->float('emergency_right',10,2)->nullable();
            $table->float('emergency_axle_weight',10,2)->nullable();
            $table->float('emergency_deceleration',10,2)->nullable();

            $table->string('front_left_size')->nullable();
            $table->string('front_left_load')->nullable();
            $table->string('front_left_manufacture')->nullable();
            $table->string('front_left_model')->nullable();
            $table->string('front_left_type')->nullable();
            $table->date('front_left_date')->nullable();
            $table->string('front_left_thread_depth')->nullable();

            $table->string('front_right_size')->nullable();
            $table->string('front_right_load')->nullable();
            $table->string('front_right_manufacture')->nullable();
            $table->string('front_right_model')->nullable();
            $table->string('front_right_type')->nullable();
            $table->date('front_right_date')->nullable();
            $table->string('front_right_thread_depth')->nullable();

            $table->string('rear_left_size')->nullable();
            $table->string('rear_left_load')->nullable();
            $table->string('rear_left_manufacture')->nullable();
            $table->string('rear_left_model')->nullable();
            $table->string('rear_left_type')->nullable();
            $table->date('rear_left_date')->nullable();
            $table->string('rear_left_thread_depth')->nullable();

            $table->string('rear_right_size')->nullable();
            $table->string('rear_right_load')->nullable();
            $table->string('rear_right_manufacture')->nullable();
            $table->string('rear_right_model')->nullable();
            $table->string('rear_right_type')->nullable();
            $table->date('rear_right_date')->nullable();
            $table->string('rear_right_thread_depth')->nullable();

            $table->string('spare_size')->nullable();
            $table->string('spare_load')->nullable();
            $table->string('spare_manufacture')->nullable();
            $table->string('spare_model')->nullable();
            $table->string('spare_type')->nullable();
            $table->date('spare_date')->nullable();
            $table->string('spare_thread_depth')->nullable();

            $table->text('diagnostic')->nullable();

            $table->text('inspection_condition')->nullable();

            $table->text('comments')->nullable();

            $table->text('concluding_remarks')->nullable();

            $table->unsignedBigInteger('inspection_status_id');

            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->unsignedBigInteger('opened_by_user_id');
            $table->unsignedBigInteger('closed_by_user_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_inspections');
    }
};
