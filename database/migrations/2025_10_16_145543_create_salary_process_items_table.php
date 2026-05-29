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
        Schema::create('salary_process_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('salary_process_id');
            $table->unsignedBigInteger('technician_id');
            $table->decimal('base_salary', 10, 2);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->decimal('overtime_amount', 10, 2)->default(0);
            $table->decimal('bonus', 10, 2)->default(0);
            $table->decimal('deductions', 10, 2)->default(0);
            $table->decimal('net_salary', 10, 2);
            $table->text('observations')->nullable();
            $table->timestamps();

            $table->foreign('salary_process_id')->references('id')->on('salary_processes')->onDelete('cascade');
            $table->foreign('technician_id')->references('id')->on('technicians');
            
            $table->unique(['salary_process_id', 'technician_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_process_items');
    }
};
