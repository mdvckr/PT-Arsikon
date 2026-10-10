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
        if (Schema::hasTable('maintenances') && !Schema::hasColumn('maintenances', 'quantity')) {
            Schema::table('maintenances', function (Blueprint $table) {
                $table->integer('quantity')->default(1)->after('tool_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('maintenances') && Schema::hasColumn('maintenances', 'quantity')) {
            Schema::table('maintenances', function (Blueprint $table) {
                $table->dropColumn('quantity');
            });
        }
    }
};
