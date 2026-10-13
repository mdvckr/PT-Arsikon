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
        // 1. Buat tabel po_delivery_schedules
        if (! Schema::hasTable('po_delivery_schedules')) {
            Schema::create('po_delivery_schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_order_item_id')->constrained('purchase_order_items')->cascadeOnDelete();
                $table->string('stage_label', 100)->nullable();
                $table->date('planned_date')->nullable();
                $table->decimal('planned_qty', 12, 2);
                $table->decimal('received_qty', 12, 2)->default(0.00);
                $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
                $table->enum('status', ['planned', 'partial', 'received', 'cancelled'])->default('planned');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 2. Tambahkan kolom ke goods_receipt_items
        Schema::table('goods_receipt_items', function (Blueprint $table) {
            if (! Schema::hasColumn('goods_receipt_items', 'qty_shipped')) {
                $table->decimal('qty_shipped', 12, 2)->default(0.00)->after('tool_id');
            }
            if (! Schema::hasColumn('goods_receipt_items', 'qty_damaged')) {
                $table->decimal('qty_damaged', 12, 2)->default(0.00)->after('qty_received');
            }
            if (! Schema::hasColumn('goods_receipt_items', 'qty_missing')) {
                $table->decimal('qty_missing', 12, 2)->default(0.00)->after('qty_damaged');
            }
            if (! Schema::hasColumn('goods_receipt_items', 'missing_status')) {
                $table->enum('missing_status', ['none', 'unresolved', 'resolved', 'confirmed_lost'])->default('none')->after('qty_missing');
            }
            if (! Schema::hasColumn('goods_receipt_items', 'po_delivery_schedule_id')) {
                $table->foreignId('po_delivery_schedule_id')->nullable()->after('missing_status')->constrained('po_delivery_schedules')->nullOnDelete();
            }
        });

        // 3. Backfill data lama: set qty_shipped = qty_received
        try {
            DB::table('goods_receipt_items')
                ->where('qty_shipped', 0)
                ->where('qty_received', '>', 0)
                ->update(['qty_shipped' => DB::raw('qty_received')]);
        } catch (\Throwable $e) {
            // Ignore backfill error on fresh test schema
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goods_receipt_items', function (Blueprint $table) {
            if (Schema::hasColumn('goods_receipt_items', 'po_delivery_schedule_id')) {
                $table->dropForeign(['po_delivery_schedule_id']);
                $table->dropColumn('po_delivery_schedule_id');
            }
            if (Schema::hasColumn('goods_receipt_items', 'missing_status')) {
                $table->dropColumn('missing_status');
            }
            if (Schema::hasColumn('goods_receipt_items', 'qty_missing')) {
                $table->dropColumn('qty_missing');
            }
            if (Schema::hasColumn('goods_receipt_items', 'qty_damaged')) {
                $table->dropColumn('qty_damaged');
            }
            if (Schema::hasColumn('goods_receipt_items', 'qty_shipped')) {
                $table->dropColumn('qty_shipped');
            }
        });

        Schema::dropIfExists('po_delivery_schedules');
    }
};
