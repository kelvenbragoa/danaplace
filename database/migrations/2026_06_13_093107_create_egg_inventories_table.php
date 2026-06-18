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
        Schema::create('egg_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('egg_id')->constrained('eggs');
            $table->foreignId('house_id')->constrained('houses');
            $table->integer('quantity');
            $table->date('entry_date');
            $table->date('exit_date')->nullable();
            $table->string('location', 100)->nullable();
            $table->enum('status', ['available', 'reserved', 'shipped'])->default('available');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('egg_inventories');
    }
};
