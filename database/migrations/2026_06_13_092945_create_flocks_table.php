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
        Schema::create('flocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('house_id')->constrained('houses')->onDelete('cascade');
            $table->foreignId('lineage_id')->constrained('lineages');
            $table->string('code', 50)->unique();
            $table->date('birth_date');
            $table->date('housing_date');
            $table->integer('initial_bird_count');
            $table->integer('current_bird_count');
            $table->date('expected_disposal_date')->nullable();
            $table->date('actual_disposal_date')->nullable();
            $table->enum('status', ['growing', 'laying', 'disposed'])->default('growing');
            $table->text('observations')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flocks');
    }
};
