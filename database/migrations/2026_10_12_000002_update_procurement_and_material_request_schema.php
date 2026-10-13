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
        // 1. purchase_orders
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN status ENUM('draft', 'approved', 'sent', 'partial_received', 'received', 'cancelled', 'closed') NOT NULL DEFAULT 'draft'");
        }
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_orders', 'risk_bearer')) {
                $table->enum('risk_bearer', ['vendor', 'buyer'])->default('vendor')->after('status');
            }
            if (! Schema::hasColumn('purchase_orders', 'delivery_warehouse_id')) {
                $table->foreignId('delivery_warehouse_id')->nullable()->after('supplier_name')->constrained('warehouses')->nullOnDelete();
            }
        });

        // 2. purchase_order_items
        Schema::table('purchase_order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_order_items', 'tool_id')) {
                $table->foreignId('tool_id')->nullable()->after('material_id')->constrained('tools')->nullOnDelete();
            }
            if (! Schema::hasColumn('purchase_order_items', 'lost_qty')) {
                $table->decimal('lost_qty', 12, 2)->default(0.00)->after('received_qty');
            }
            if (! Schema::hasColumn('purchase_order_items', 'short_closed_qty')) {
                $table->decimal('short_closed_qty', 12, 2)->default(0.00)->after('lost_qty');
            }
            if (! Schema::hasColumn('purchase_order_items', 'procurement_request_item_id')) {
                $table->foreignId('procurement_request_item_id')->nullable()->after('material_request_item_id')->constrained('procurement_request_items')->nullOnDelete();
            }
        });

        // 3. procurement_requests
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE procurement_requests MODIFY COLUMN status ENUM('draft', 'submitted', 'approved', 'rejected', 'po_created', 'partially_ordered', 'fully_ordered') NOT NULL DEFAULT 'draft'");
        }

        // 4. procurement_request_items
        Schema::table('procurement_request_items', function (Blueprint $table) {
            if (! Schema::hasColumn('procurement_request_items', 'qty_ordered')) {
                $table->decimal('qty_ordered', 12, 2)->default(0.00)->after('quantity');
            }
        });

        // 5. material_requests
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE material_requests MODIFY COLUMN from_warehouse_id BIGINT UNSIGNED NULL");
        }
        Schema::table('material_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('material_requests', 'needed_at')) {
                $table->date('needed_at')->nullable()->after('to_warehouse_id');
            }
        });

        // 6. material_request_items
        Schema::table('material_request_items', function (Blueprint $table) {
            if (! Schema::hasColumn('material_request_items', 'item_type')) {
                $table->enum('item_type', ['material', 'tool'])->default('material')->after('material_request_id');
            }
            if (! Schema::hasColumn('material_request_items', 'tool_id')) {
                $table->foreignId('tool_id')->nullable()->after('material_id')->constrained('tools')->nullOnDelete();
            }
            if (! Schema::hasColumn('material_request_items', 'fulfillment')) {
                $table->enum('fulfillment', ['stock', 'purchase'])->nullable()->after('custom_item_unit');
            }
            if (! Schema::hasColumn('material_request_items', 'qty_ordered')) {
                $table->decimal('qty_ordered', 12, 2)->default(0.00)->after('qty_approved');
            }
            if (! Schema::hasColumn('material_request_items', 'qty_in_transit')) {
                $table->decimal('qty_in_transit', 12, 2)->default(0.00)->after('qty_ordered');
            }
            if (! Schema::hasColumn('material_request_items', 'qty_received')) {
                $table->decimal('qty_received', 12, 2)->default(0.00)->after('qty_in_transit');
            }
            if (! Schema::hasColumn('material_request_items', 'qty_issued')) {
                $table->decimal('qty_issued', 12, 2)->default(0.00)->after('qty_received');
            }
        });

        // Backfill data lama
        try {
            DB::table('material_request_items')
                ->where('qty_fulfilled', '>', 0)
                ->update(['qty_received' => DB::raw('qty_fulfilled')]);

            DB::table('procurement_requests')
                ->where('status', 'po_created')
                ->update(['status' => 'fully_ordered']);
        } catch (\Throwable $e) {
            // best-effort
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_request_items', function (Blueprint $table) {
            $table->dropForeign(['tool_id']);
            $table->dropColumn([
                'item_type',
                'tool_id',
                'fulfillment',
                'qty_ordered',
                'qty_in_transit',
                'qty_received',
                'qty_issued',
            ]);
        });

        Schema::table('material_requests', function (Blueprint $table) {
            $table->dropColumn('needed_at');
        });

        Schema::table('procurement_request_items', function (Blueprint $table) {
            $table->dropColumn('qty_ordered');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE procurement_requests MODIFY COLUMN status ENUM('draft', 'submitted', 'approved', 'rejected', 'po_created') NOT NULL DEFAULT 'draft'");
        }

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropForeign(['tool_id']);
            $table->dropForeign(['procurement_request_item_id']);
            $table->dropColumn([
                'tool_id',
                'lost_qty',
                'short_closed_qty',
                'procurement_request_item_id',
            ]);
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['delivery_warehouse_id']);
            $table->dropColumn(['risk_bearer', 'delivery_warehouse_id']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN status ENUM('draft', 'sent', 'partial_received', 'received', 'cancelled') NOT NULL DEFAULT 'draft'");
        }
    }
};
