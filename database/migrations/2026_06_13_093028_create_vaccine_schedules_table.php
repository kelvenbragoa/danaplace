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
        Schema::create('vaccine_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flock_id')->constrained('flocks')->onDelete('cascade');
            $table->foreignId('vaccine_id')->constrained('vaccines');
            $table->date('scheduled_date');
            $table->date('application_date')->nullable();
            $table->enum('administration_route', ['injectable', 'water', 'feed']);
            $table->string('dosage', 50)->nullable();
            $table->foreignId('responsible_id')->nullable()->constrained('users');
            $table->enum('status', ['pending', 'applied', 'canceled'])->default('pending');
            $table->text('observations')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vaccine_schedules');
    }
};
