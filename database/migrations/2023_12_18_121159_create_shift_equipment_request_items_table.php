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
        Schema::create('shift_equipment_request_items', function (Blueprint $table) {
            $table->id();
            $table->integer('shift_id');
            $table->integer('shift_equipment_request_id');
            $table->integer('type_equipment_id');
            $table->integer('equipment_id');
            $table->integer('operator_user_id')->nullable();
            $table->double('petrol',10,2)->nullable();
            $table->double('moves',10,2)->nullable();
            $table->double('ton',10,2)->nullable();
            $table->double('distance',10,2)->nullable();
            $table->integer('accident')->nullable();
            $table->text('warning')->nullable();
            $table->text('obs')->nullable();
           
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_equipment_request_items');
    }
};
