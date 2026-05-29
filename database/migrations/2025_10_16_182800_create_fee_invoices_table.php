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
        Schema::create('fee_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->integer('month'); // Mês da fatura (1-12)
            $table->integer('year'); // Ano da fatura
            $table->date('issue_date'); // Data de emissão
            $table->date('due_date'); // Data de vencimento
            $table->text('notes')->nullable(); // Observações gerais
            $table->decimal('total_amount', 15, 2)->default(0); // Valor total da fatura
            $table->decimal('paid_amount', 15, 2)->default(0); // Valor já pago
            $table->enum('status', ['draft', 'issued', 'partially_paid', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->unsignedBigInteger('created_by'); // Usuário que criou
            $table->unsignedBigInteger('approved_by')->nullable(); // Usuário que aprovou
            $table->timestamp('approved_at')->nullable(); // Data de aprovação
            $table->json('metadata')->nullable(); // Dados extras (configurações, filtros aplicados, etc.)
            $table->timestamps();
            
            // Indices para performance
            $table->index(['month', 'year']);
            $table->index(['status']);
            $table->index(['due_date']);
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('approved_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_invoices');
    }
};
