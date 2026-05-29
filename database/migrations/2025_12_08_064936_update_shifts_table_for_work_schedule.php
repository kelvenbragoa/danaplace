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
        Schema::table('shifts', function (Blueprint $table) {
            // Adicionar novas colunas para o módulo de escala de trabalho
            $table->foreignId('work_schedule_id')->nullable()->constrained('work_schedules')->onDelete('cascade');
            $table->enum('shift_type', ['morning', 'afternoon', 'evening', 'night'])->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['scheduled', 'active', 'completed', 'cancelled'])->default('scheduled');
            
            // Índices para melhor performance
            $table->index('work_schedule_id');
            $table->index(['date', 'shift_type']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropForeign(['work_schedule_id']);
            $table->dropColumn([
                'work_schedule_id',
                'shift_type',
                'start_time',
                'end_time',
                'description',
                'status'
            ]);
        });
    }
};
