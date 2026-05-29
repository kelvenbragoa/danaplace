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
        Schema::table('mcscrs', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('scheduled_by_user_id')->nullable();
            $table->unsignedBigInteger('diagnosis_by_user_id')->nullable();
            $table->unsignedBigInteger('execution_by_user_id')->nullable();
            $table->unsignedBigInteger('approval_by_user_id')->nullable();

            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mcscrs', function (Blueprint $table) {
            //
            $table->dropColumn('scheduled_by_user_id');
            $table->dropColumn('diagnosis_by_user_id');
            $table->dropColumn('execution_by_user_id');
            $table->dropColumn('approval_by_user_id');
        });
    }
};
