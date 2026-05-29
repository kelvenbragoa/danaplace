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
        Schema::create('vacation_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technician_id')->constrained('technicians')->onDelete('cascade');
            $table->year('year')->default(date('Y'));
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('days_requested');
            $table->integer('days_approved')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'executed'])->default('pending');
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('replacement_technician_id')->nullable()->constrained('technicians')->onDelete('set null');
            $table->timestamps();

            // Índices para melhor performance
            $table->index(['technician_id', 'year']);
            $table->index(['status', 'year']);
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacation_plans');
    }
};
