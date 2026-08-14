<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('egg_shippings', function ($table) {
            $table->dropForeign(['inventory_id']);
        });

        DB::statement('ALTER TABLE egg_shippings MODIFY inventory_id BIGINT UNSIGNED NULL');

        Schema::table('egg_shippings', function ($table) {
            $table->foreign('inventory_id')->references('id')->on('egg_inventories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('egg_shippings', function ($table) {
            $table->dropForeign(['inventory_id']);
        });

        DB::statement('ALTER TABLE egg_shippings MODIFY inventory_id BIGINT UNSIGNED NOT NULL');

        Schema::table('egg_shippings', function ($table) {
            $table->foreign('inventory_id')->references('id')->on('egg_inventories');
        });
    }
};
