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
        Schema::create('energy_invoices', function (Blueprint $table) {
            $table->id();
            $table->date('start_date_period');
            $table->date('end_date_period');

            $table->decimal('active_energy_consumption', 20, 10)->default(0.00);
            $table->decimal('active_energy_consumption_cost', 20, 10)->default(0.00);

            $table->decimal('reactive_energy_consumption', 20, 10)->default(0.00);
            $table->decimal('reactive_energy_consumption_cost', 20, 10)->default(0.00);

            $table->decimal('loss', 20, 10)->default(0.00);
            $table->decimal('loss_cost', 20, 10)->default(0.00);

            $table->decimal('ponta', 20, 10)->default(0.00);
            $table->decimal('ponta_cost', 20, 10)->default(0.00);

            $table->decimal('fix_rate', 20, 10)->default(0.00);
            $table->decimal('fix_rate_cost', 20, 10)->default(0.00);

            $table->decimal('tax_iva', 20, 10)->default(0.00);

            $table->decimal('invoice_total_cost', 20, 10)->default(0.00);

            $table->decimal('total_to_invoice_items', 20, 10)->default(0.00);
            $table->decimal('total_value_items', 20, 10)->default(0.00);
            $table->decimal('total_cost_items', 20, 10)->default(0.00);
            $table->decimal('total_apr_consumption', 20, 10)->default(0.00);



            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('energy_invoices');
    }
};
