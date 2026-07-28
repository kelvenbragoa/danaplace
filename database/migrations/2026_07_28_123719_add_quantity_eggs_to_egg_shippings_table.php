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
        Schema::table('egg_shippings', function (Blueprint $table) {
            $table->unsignedInteger('quantity_eggs')->nullable()->after('inventory_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('egg_shippings', function (Blueprint $table) {
            $table->dropColumn('quantity_eggs');
        });
    }
};
