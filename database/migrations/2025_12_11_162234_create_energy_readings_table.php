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
        Schema::create('energy_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('energy_invoice_id')->constrained('energy_invoices')->onDelete('cascade');
            $table->foreignId('equipment_id')->constrained('equipment')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Técnico responsável
            $table->date('reading_date');
            $table->decimal('reading_value', 10, 2); // Valor atual da leitura
            $table->decimal('previous_reading', 10, 2)->nullable(); // Leitura anterior
            $table->decimal('consumption', 10, 2)->nullable(); // Consumo calculado
            $table->text('notes')->nullable(); // Observações
            $table->timestamps();

            // Índices para melhor performance
            $table->index(['energy_invoice_id', 'reading_date']);
            $table->index(['equipment_id', 'reading_date']);
            $table->unique(['equipment_id', 'reading_date']); // Uma leitura por equipamento por dia
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('energy_readings');
    }
};
