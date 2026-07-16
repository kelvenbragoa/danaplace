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
        Schema::create('egg_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->date('expense_date');
            $table->enum('category', [
                'feed',
                'vaccine',
                'medication',
                'labor',
                'energy',
                'packaging',
                'maintenance',
                'equipment',
                'transport',
                'other',
            ])->default('other');
            $table->foreignId('farm_id')->nullable()->constrained('farms')->nullOnDelete();
            $table->foreignId('house_id')->nullable()->constrained('houses')->nullOnDelete();
            $table->foreignId('flock_id')->nullable()->constrained('flocks')->nullOnDelete();
            $table->string('vendor_name')->nullable();
            $table->string('invoice_number')->nullable();
            $table->enum('payment_method', [
                'cash',
                'bank_transfer',
                'check',
                'card',
                'other',
            ])->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['expense_date', 'category']);
            $table->index(['farm_id', 'expense_date']);
            $table->index(['flock_id', 'expense_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('egg_expenses');
    }
};
