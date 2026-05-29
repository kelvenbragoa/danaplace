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
        Schema::create('technician_absences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('technician_id');
            $table->date('date');
            $table->enum('type', ['absence', 'late_arrival', 'early_departure'])->default('absence');
            $table->decimal('hours_lost', 5, 2)->default(0); // Horas perdidas
            $table->text('reason')->nullable(); // Motivo da falta
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('observations')->nullable(); // Observações do aprovador
            $table->unsignedBigInteger('created_by_user_id');
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('technician_id')->references('id')->on('technicians')->onDelete('cascade');
            $table->foreign('created_by_user_id')->references('id')->on('users');
            $table->foreign('approved_by_user_id')->references('id')->on('users');
            
            // Indices
            $table->index(['technician_id', 'date']);
            $table->index(['status']);
            $table->index(['date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('technician_absences');
    }
};
