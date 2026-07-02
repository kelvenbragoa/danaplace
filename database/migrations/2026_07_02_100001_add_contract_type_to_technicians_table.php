<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('technicians', function (Blueprint $table) {
            $table->foreignId('contract_type_id')->nullable()->after('area_id')->constrained('contract_types')->nullOnDelete();
            $table->json('contract_extra_data')->nullable()->after('contract_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('technicians', function (Blueprint $table) {
            $table->dropForeign(['contract_type_id']);
            $table->dropColumn(['contract_type_id', 'contract_extra_data']);
        });
    }
};
