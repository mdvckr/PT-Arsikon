<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('material_request_items', function (Blueprint $table) {
            $table->string('custom_item_name')->nullable()->after('material_id');
            $table->string('custom_item_unit', 50)->nullable()->after('custom_item_name');
        });

        // Make material_id nullable for manual/custom items
        // Use DB statement for compatibility
        try {
            DB::statement('ALTER TABLE material_request_items MODIFY material_id BIGINT UNSIGNED NULL');
        } catch (\Throwable $e) {
            // Fallback via Schema if DB statement fails
            Schema::table('material_request_items', function (Blueprint $table) {
                $table->unsignedBigInteger('material_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('material_request_items', function (Blueprint $table) {
            $table->dropColumn(['custom_item_name', 'custom_item_unit']);
        });
        try {
            DB::statement('ALTER TABLE material_request_items MODIFY material_id BIGINT UNSIGNED NOT NULL');
        } catch (\Throwable $e) {
            Schema::table('material_request_items', function (Blueprint $table) {
                $table->unsignedBigInteger('material_id')->nullable(false)->change();
            });
        }
    }
};
