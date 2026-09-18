<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            // Status: draft (tersimpan, stok belum diubah) | confirmed (stok sudah diupdate)
            $table->string('status')->default('draft')->after('purchase_order_id');
            // No. Invoice dari supplier
            $table->string('invoice_number', 100)->nullable()->after('status');
            // User yang membuat dokumen GR
            $table->foreignId('created_by')->nullable()->after('invoice_number')
                  ->constrained('users')->nullOnDelete();
            // Siapa dan kapan konfirmasi dilakukan
            $table->foreignId('confirmed_by')->nullable()->after('created_by')
                  ->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable()->after('confirmed_by');
        });

        Schema::table('goods_receipt_items', function (Blueprint $table) {
            // Link opsional ke item PO untuk tracing
            $table->foreignId('purchase_order_item_id')->nullable()->after('goods_receipt_id')
                  ->constrained('purchase_order_items')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('goods_receipt_items', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_item_id']);
            $table->dropColumn('purchase_order_item_id');
        });

        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropForeign(['confirmed_by']);
            $table->dropForeign(['created_by']);
            $table->dropColumn(['status', 'invoice_number', 'created_by', 'confirmed_by', 'confirmed_at']);
        });
    }
};
