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
        Schema::create('egg_classifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flock_id')->constrained('flocks')->onDelete('cascade');
            $table->date('processing_date');
            $table->integer('washed_eggs')->default(0);
            $table->integer('unwashed_eggs')->default(0);
            $table->integer('total_rejects')->default(0);
            $table->decimal('reject_percentage', 5, 2)->default(0);
            $table->foreignId('responsible_id')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('egg_classifications');
    }
};
