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
        Schema::create('shift_equipment_requests', function (Blueprint $table) {
            $table->id();
            $table->integer('shift_id');
            $table->integer('type_equipment_id');
            $table->integer('request_quantity');
            $table->integer('delivered_quantity')->nullable();
            $table->integer('status');
            $table->integer('created_by_user_id');
            $table->integer('answered_by_user_id')->nullable(); 
            $table->timestamp('answered_date')->nullable(); 
            $table->text('obs')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_equipment_requests');
    }
};
