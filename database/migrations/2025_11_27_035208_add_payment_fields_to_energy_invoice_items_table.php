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
        Schema::table('energy_invoice_items', function (Blueprint $table) {
            $table->boolean('is_paid')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->unsignedBigInteger('marked_by')->nullable();
            $table->json('payment_details')->nullable();
            
            $table->foreign('marked_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('energy_invoice_items', function (Blueprint $table) {
            $table->dropForeign(['marked_by']);
            $table->dropColumn(['is_paid', 'paid_at', 'marked_by', 'payment_details']);
        });
    }
};
