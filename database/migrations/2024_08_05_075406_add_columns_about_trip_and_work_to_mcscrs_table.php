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
        Schema::table('mcscrs', function (Blueprint $table) {
            $table->date('trip_start_date')->nullable();
            $table->date('trip_return_date')->nullable();
            $table->string('trip_travel_hours')->nullable();
            $table->string('trip_travel_of')->nullable();
            $table->string('trip_travel_to')->nullable();
            $table->string('trip_distance_traveled')->nullable();

            $table->time('work_start_time')->nullable();
            $table->time('work_return_time')->nullable();
            $table->string('work_total_amount_of_hours')->nullable();
            $table->string('work_nights_at_hotel')->nullable();
            $table->string('work_extra_start_times')->nullable();
            $table->string('work_extra_ending_times')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mcscrs', function (Blueprint $table) {
            $table->dropColumn('trip_start_date');
            $table->dropColumn('trip_return_date');
            $table->dropColumn('trip_travel_hours');
            $table->dropColumn('trip_travel_of');
            $table->dropColumn('trip_travel_to');
            $table->dropColumn('trip_distance_traveled');
            
            $table->dropColumn('work_start_time');
            $table->dropColumn('work_return_time');
            $table->dropColumn('work_total_amount_of_hours');
            $table->dropColumn('work_nights_at_hotel');
            $table->dropColumn('work_extra_start_times');
            $table->dropColumn('work_extra_ending_times');
        });
    }
};
