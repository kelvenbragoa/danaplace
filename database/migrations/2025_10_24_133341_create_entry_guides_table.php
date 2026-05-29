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
        Schema::create('entry_guides', function (Blueprint $table) {
            $table->id();
            $table->string('guide_number')->unique();
            $table->unsignedBigInteger('destination_id');
            $table->string('guest_name');
            $table->string('guest_document'); // CPF, RG, etc
            $table->string('guest_phone')->nullable();
            $table->string('guest_email')->nullable();
            $table->string('host_name'); // Nome do morador que autorizou
            $table->string('host_unit'); // Apartamento/casa
            $table->text('purpose')->nullable(); // Motivo da visita
            $table->datetime('valid_from');
            $table->datetime('valid_until');
            $table->enum('status', ['active', 'used', 'expired', 'cancelled'])->default('active');
            $table->string('qr_code_path')->nullable();
            $table->datetime('entry_time')->nullable();
            $table->datetime('exit_time')->nullable();
            $table->text('observations')->nullable();
            $table->unsignedBigInteger('created_by'); // Usuário que criou
            $table->timestamps();

            $table->foreign('destination_id')->references('id')->on('destinations')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            
            $table->index(['destination_id', 'status']);
            $table->index(['valid_from', 'valid_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entry_guides');
    }
};
