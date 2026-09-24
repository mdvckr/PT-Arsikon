<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Make material_id nullable to support custom/new items from MR
        try {
            DB::statement('ALTER TABLE purchase_order_items MODIFY material_id BIGINT UNSIGNED NULL');
        } catch (\Throwable $e) {
            Schema::table('purchase_order_items', function (Blueprint $table) {
                $table->unsignedBigInteger('material_id')->nullable()->change();
            });
        }

        // 2. Add custom item fields and optional link to MaterialRequestItem
        Schema::table('purchase_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_order_items', 'custom_item_name')) {
                $table->string('custom_item_name')->nullable()->after('material_id');
            }
            if (!Schema::hasColumn('purchase_order_items', 'custom_item_unit')) {
                $table->string('custom_item_unit', 50)->nullable()->after('custom_item_name');
            }
            if (!Schema::hasColumn('purchase_order_items', 'material_request_item_id')) {
                $table->foreignId('material_request_item_id')
                    ->nullable()
                    ->after('custom_item_unit')
                    ->constrained('material_request_items')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_order_items', 'material_request_item_id')) {
                $table->dropForeign(['material_request_item_id']);
                $table->dropColumn('material_request_item_id');
            }
            if (Schema::hasColumn('purchase_order_items', 'custom_item_unit')) {
                $table->dropColumn('custom_item_unit');
            }
            if (Schema::hasColumn('purchase_order_items', 'custom_item_name')) {
                $table->dropColumn('custom_item_name');
            }
        });

        try {
            DB::statement('ALTER TABLE purchase_order_items MODIFY material_id BIGINT UNSIGNED NOT NULL');
        } catch (\Throwable $e) {
            Schema::table('purchase_order_items', function (Blueprint $table) {
                $table->unsignedBigInteger('material_id')->nullable(false)->change();
            });
        }
    }
};
