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
        if (Schema::hasTable('material_requests') && !Schema::hasColumn('material_requests', 'approved_at')) {
            Schema::table('material_requests', function (Blueprint $table) {
                $table->timestamp('approved_at')->nullable()->after('approved_by_user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('material_requests') && Schema::hasColumn('material_requests', 'approved_at')) {
            Schema::table('material_requests', function (Blueprint $table) {
                $table->dropColumn('approved_at');
            });
        }
    }
};
