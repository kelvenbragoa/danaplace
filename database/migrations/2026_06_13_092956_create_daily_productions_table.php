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
        Schema::create('daily_productions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flock_id')->constrained('flocks')->onDelete('cascade');
            $table->date('date');
            $table->integer('total_eggs')->default(0);
            $table->integer('cracked_eggs')->default(0);
            $table->integer('dirty_eggs')->default(0);
            $table->integer('deformed_eggs')->default(0);
            $table->integer('clean_eggs')->default(0);
            $table->decimal('feed_consumption_kg', 10, 2)->default(0);
            $table->decimal('water_consumption_liters', 10, 2)->default(0);
            $table->decimal('light_hours', 5, 2)->default(0);
            $table->foreignId('responsible_id')->nullable()->constrained('users');
            $table->string('observations')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_productions');
    }
};
