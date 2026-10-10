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
        if (Schema::hasTable('tool_inspections') && !Schema::hasColumn('tool_inspections', 'quantity')) {
            Schema::table('tool_inspections', function (Blueprint $table) {
                $table->integer('quantity')->default(1)->after('tool_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tool_inspections') && Schema::hasColumn('tool_inspections', 'quantity')) {
            Schema::table('tool_inspections', function (Blueprint $table) {
                $table->dropColumn('quantity');
            });
        }
    }
};
