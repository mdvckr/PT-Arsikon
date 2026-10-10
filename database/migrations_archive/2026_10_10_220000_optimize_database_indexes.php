<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tambahkan composite indexes strategis untuk mempercepat filtering,
     * pencarian skala besar, dan mencegah full table scan (Database DoS prevention).
     */
    public function up(): void
    {
        // 1. Indeks pada tabel materials
        if (Schema::hasTable('materials')) {
            Schema::table('materials', function (Blueprint $table) {
                $table->index(['category_id', 'is_active'], 'idx_materials_category_active');
                $table->index(['is_active', 'name'], 'idx_materials_active_name');
            });
        }

        // 2. Indeks pada tabel tools
        if (Schema::hasTable('tools')) {
            Schema::table('tools', function (Blueprint $table) {
                $table->index(['category_id', 'is_active'], 'idx_tools_category_active');
                $table->index(['is_active', 'name'], 'idx_tools_active_name');
            });
        }

        // 3. Indeks pada riwayat mutasi stok (query riwayat per gudang & material)
        if (Schema::hasTable('stock_mutations')) {
            Schema::table('stock_mutations', function (Blueprint $table) {
                $table->index(['warehouse_id', 'material_id', 'created_at'], 'idx_mutations_wh_mat_created');
            });
        }

        // 4. Indeks pada distribusi barang (query status pengiriman per gudang)
        if (Schema::hasTable('distributions')) {
            Schema::table('distributions', function (Blueprint $table) {
                $table->index(['from_warehouse_id', 'status', 'created_at'], 'idx_dist_from_status_created');
                $table->index(['to_warehouse_id', 'status', 'created_at'], 'idx_dist_to_status_created');
            });
        }

        // 5. Indeks pada audit logs (query riwayat aktivitas per user & waktu)
        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->index(['user_id', 'created_at'], 'idx_audit_logs_user_created');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('materials')) {
            Schema::table('materials', function (Blueprint $table) {
                $table->dropIndex('idx_materials_category_active');
                $table->dropIndex('idx_materials_active_name');
            });
        }

        if (Schema::hasTable('tools')) {
            Schema::table('tools', function (Blueprint $table) {
                $table->dropIndex('idx_tools_category_active');
                $table->dropIndex('idx_tools_active_name');
            });
        }

        if (Schema::hasTable('stock_mutations')) {
            Schema::table('stock_mutations', function (Blueprint $table) {
                $table->dropIndex('idx_mutations_wh_mat_created');
            });
        }

        if (Schema::hasTable('distributions')) {
            Schema::table('distributions', function (Blueprint $table) {
                $table->dropIndex('idx_dist_from_status_created');
                $table->dropIndex('idx_dist_to_status_created');
            });
        }

        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->dropIndex('idx_audit_logs_user_created');
            });
        }
    }
};
