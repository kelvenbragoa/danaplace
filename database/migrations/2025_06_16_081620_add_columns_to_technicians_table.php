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
        Schema::table('technicians', function (Blueprint $table) {
            //
            $table->date('date_of_birth')->nullable();
            $table->string('contact')->nullable();
            $table->string('gender')->nullable();
            $table->date('address')->nullable();
            $table->date('province')->nullable();
            $table->date('city')->nullable();
            $table->date('civil_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('technicians', function (Blueprint $table) {
            //
            $table->dropColumn('date_of_birth');
            $table->dropColumn('contact');
            $table->dropColumn('gender');
            $table->dropColumn('address');
            $table->dropColumn('province');
            $table->dropColumn('city');
            $table->dropColumn('civil_status');
        });
    }
};
