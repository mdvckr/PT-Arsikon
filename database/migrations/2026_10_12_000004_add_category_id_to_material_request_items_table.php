<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('material_request_items', function (Blueprint $table) {
            if (! Schema::hasColumn('material_request_items', 'category_id')) {
                $table->foreignId('category_id')
                    ->nullable()
                    ->after('material_id')
                    ->constrained('categories')
                    ->nullOnDelete();
                $table->index('category_id', 'idx_mr_items_category_id');
            }
        });

        // Backfill data lama: item master material diisi dari category_id milik material
        try {
            DB::statement("
                UPDATE material_request_items mri
                INNER JOIN materials m ON mri.material_id = m.id
                SET mri.category_id = m.category_id
                WHERE mri.material_id IS NOT NULL
                  AND m.category_id IS NOT NULL
                  AND mri.category_id IS NULL
            ");

            if (Schema::hasColumn('material_request_items', 'tool_id')) {
                DB::statement("
                    UPDATE material_request_items mri
                    INNER JOIN tools t ON mri.tool_id = t.id
                    SET mri.category_id = t.category_id
                    WHERE mri.tool_id IS NOT NULL
                      AND t.category_id IS NOT NULL
                      AND mri.category_id IS NULL
                ");
            }
        } catch (\Throwable $e) {
            // Abaikan jika tabel kosong saat seeding
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_request_items', function (Blueprint $table) {
            if (Schema::hasColumn('material_request_items', 'category_id')) {
                $table->dropForeign(['category_id']);
                $table->dropIndex('idx_mr_items_category_id');
                $table->dropColumn('category_id');
            }
        });
    }
};
