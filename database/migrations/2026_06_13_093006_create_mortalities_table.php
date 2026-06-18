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
        Schema::create('mortalities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flock_id')->constrained('flocks')->onDelete('cascade');
            $table->date('date');
            $table->integer('quantity');
            $table->string('probable_cause', 255)->nullable();
            $table->boolean('necropsy_performed')->default(false);
            $table->text('necropsy_report')->nullable();
            $table->foreignId('responsible_id')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mortalities');
    }
};
