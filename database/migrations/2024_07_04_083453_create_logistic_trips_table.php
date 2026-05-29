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
        Schema::create('logistic_trips', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('equipment_id');
            $table->bigInteger('type_equipment_id');
            $table->bigInteger('area_id');
            $table->bigInteger('driver_id');
            $table->bigInteger('destination_id');
            $table->bigInteger('trip_status_id');
            $table->bigInteger('user_id');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logistic_trips');
    }
};
