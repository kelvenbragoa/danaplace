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
        Schema::create('fee_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fee_invoice_id');
            $table->unsignedBigInteger('equipment_id');
            $table->unsignedBigInteger('fee_id');
            $table->decimal('amount', 10, 2); // Valor da taxa para este equipamento
            $table->boolean('is_paid')->default(false); // Se foi pago
            $table->timestamp('paid_at')->nullable(); // Quando foi marcado como pago
            $table->unsignedBigInteger('marked_by')->nullable(); // Usuário que marcou como pago
            $table->text('notes')->nullable(); // Observações específicas do item
            $table->json('payment_details')->nullable(); // Detalhes do pagamento (método, referência, etc.)
            $table->timestamps();
            
            // Relacionamentos
            $table->foreign('fee_invoice_id')->references('id')->on('fee_invoices')->onDelete('cascade');
            $table->foreign('equipment_id')->references('id')->on('equipment');
            $table->foreign('fee_id')->references('id')->on('fees');
            $table->foreign('marked_by')->references('id')->on('users');
            
            // Indices para performance e evitar duplicatas
            $table->unique(['fee_invoice_id', 'equipment_id', 'fee_id']);
            $table->index(['is_paid']);
            $table->index(['paid_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_invoice_items');
    }
};
