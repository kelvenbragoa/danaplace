<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_productions', function (Blueprint $table) {
            $table->integer('normal_eggs')->default(0)->after('clean_eggs');
            $table->integer('grande_eggs')->default(0)->after('normal_eggs');
            $table->integer('jumbo_eggs')->default(0)->after('grande_eggs');
        });
    }

    public function down(): void
    {
        Schema::table('daily_productions', function (Blueprint $table) {
            $table->dropColumn(['normal_eggs', 'grande_eggs', 'jumbo_eggs']);
        });
    }
};
