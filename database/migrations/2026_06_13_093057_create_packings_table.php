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
        Schema::create('packings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classification_id')->constrained('egg_classifications');
            $table->enum('package_type', ['tray', 'box']);
            $table->integer('quantity_used');
            $table->integer('packaged_eggs');
            $table->integer('remaining_eggs')->default(0);
            $table->date('expiry_date');
            $table->string('qr_code', 255)->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packings');
    }
};
