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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('firstName');
            $table->string('lastName');
            $table->string('address')->nullable();
            $table->string('code');
            $table->string('bi')->nullable();
            $table->string('mobile')->nullable();
            $table->string('cellphone')->nullable();
            $table->string('signature')->nullable();
            $table->integer('area_id')->nullable();
            $table->integer('destination_id')->nullable();
            $table->integer('country_id');
            $table->integer('province_id');
            $table->integer('city_id');
            $table->integer('user_status_id');
            $table->integer('role_id');
            $table->integer('account_status_id');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
