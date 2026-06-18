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
        Schema::create('egg_alerts', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['laying', 'mortality', 'inventory', 'expiry', 'vaccine']);
            $table->string('title', 100);
            $table->text('message');
            $table->dateTime('alert_datetime');
            $table->dateTime('read_datetime')->nullable();
            $table->dateTime('resolved_datetime')->nullable();
            $table->enum('status', ['sent', 'read', 'resolved'])->default('sent');
            $table->boolean('email_sent')->default(false);
            $table->boolean('sms_sent')->default(false);
            $table->foreignId('flock_id')->nullable()->constrained('flocks');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('egg_alerts');
    }
};
