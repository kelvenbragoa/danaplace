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
        Schema::create('eggs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flock_id')->constrained('flocks')->onDelete('cascade');
            $table->foreignId('classification_id')->nullable()->constrained('egg_classifications');
            $table->foreignId('category_id')->nullable()->constrained('egg_categories');
            $table->date('lay_date');
            $table->date('classification_date')->nullable();
            $table->enum('quality', ['clean', 'dirty', 'cracked', 'deformed'])->default('clean');
            $table->string('reject_reason', 100)->nullable();
            $table->enum('destination', ['packaged', 'reject', 'broken'])->default('packaged');
            $table->string('traceability_code', 50)->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eggs');
    }
};
