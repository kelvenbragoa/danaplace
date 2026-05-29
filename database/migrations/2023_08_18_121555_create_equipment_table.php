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
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->integer('criticaly_id');
            $table->integer('type_equipment_id');
            $table->integer('equipment_status_id');
            $table->integer('destination_id');
            $table->integer('area_id');
            $table->integer('supplier_id');
            $table->integer('load_unity_id');
            $table->integer('distance_control_id');
            $table->integer('center_cost_account_id')->nullable();
            $table->integer('center_cost_id')->nullable();
            $table->integer('acquisition_id');
            $table->string('name');
            $table->float('load_max',15,2);
            $table->float('fuel',15,2);
            $table->float('amount',15,2);
            $table->integer('is_commissioned');
            $table->integer('coin_id');
            $table->string('ref')->nullable();
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->string('serial')->nullable();
            $table->string('chassis')->nullable();
            $table->year('year')->nullable();
            $table->year('buy_year')->nullable();
            $table->string('gps_tracking_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
