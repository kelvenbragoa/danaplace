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
        Schema::create('tire_layout_level_positions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tire_layout_id');
            $table->unsignedBigInteger('tire_layout_level_id');
            $table->unsignedBigInteger('position_id');
            $table->string('position');
            $table->string('name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tire_layout_level_positions');
    }
};
