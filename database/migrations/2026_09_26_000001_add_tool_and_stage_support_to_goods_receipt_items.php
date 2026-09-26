<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_receipt_items', function (Blueprint $table) {
            // Ubah material_id jadi nullable agar bisa diisi tool_id saat penerimaan alat
            $table->unsignedBigInteger('material_id')->nullable()->change();

            // Tambahkan item_type: 'material' atau 'tool'
            if (!Schema::hasColumn('goods_receipt_items', 'item_type')) {
                $table->string('item_type', 20)->default('material')->after('goods_receipt_id');
            }

            // Tambahkan tool_id
            if (!Schema::hasColumn('goods_receipt_items', 'tool_id')) {
                $table->foreignId('tool_id')->nullable()->after('material_id')
                      ->constrained('tools')->nullOnDelete();
            }

            // Tambahkan stage_reference untuk menautkan dengan tahapan kedatangan
            if (!Schema::hasColumn('goods_receipt_items', 'stage_reference')) {
                $table->string('stage_reference', 100)->nullable()->after('purchase_order_item_id');
            }

            // Tambahkan condition: 'good', 'damaged', 'reject'
            if (!Schema::hasColumn('goods_receipt_items', 'condition')) {
                $table->string('condition', 20)->default('good')->after('qty_received');
            }
        });
    }

    public function down(): void
    {
        Schema::table('goods_receipt_items', function (Blueprint $table) {
            if (Schema::hasColumn('goods_receipt_items', 'tool_id')) {
                $table->dropForeign(['tool_id']);
                $table->dropColumn('tool_id');
            }
            if (Schema::hasColumn('goods_receipt_items', 'item_type')) {
                $table->dropColumn('item_type');
            }
            if (Schema::hasColumn('goods_receipt_items', 'stage_reference')) {
                $table->dropColumn('stage_reference');
            }
            if (Schema::hasColumn('goods_receipt_items', 'condition')) {
                $table->dropColumn('condition');
            }
            $table->unsignedBigInteger('material_id')->nullable(false)->change();
        });
    }
};
