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
        Schema::create('type_equipment_sub_components', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('model');
            $table->string('make');
            $table->integer('criticaly_id');
            $table->integer('type_equipment_component_id');
            $table->integer('percentage_weigth');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('type_equipment_sub_components');
    }
};
