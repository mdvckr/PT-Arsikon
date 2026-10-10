<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Inventory & Warehouse Operations:
     * Material Inventories, Stock Mutations, Tool Multi-Warehouse Inventories,
     * Material Requests (and items), and Material Usages (and items).
     */
    public function up(): void
    {
        // 1. Material Warehouse Inventories (Current stock per warehouse)
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('material_id')->constrained('materials')->onDelete('cascade');
            $table->decimal('quantity', 12, 2)->default(0.00);
            $table->decimal('min_stock', 12, 2)->default(0.00);
            $table->decimal('qty_allocated', 12, 2)->default(0.00);
            $table->decimal('qty_in_transit', 12, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['warehouse_id', 'material_id']);
        });

        // 2. Stock Mutations (Ledger of all stock movements)
        Schema::create('stock_mutations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('material_id')->constrained('materials')->onDelete('cascade');
            $table->string('reference_type');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('qty_change', 12, 2);
            $table->decimal('qty_balance_after', 12, 2);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Strategic index for rapid historical queries per warehouse & material
            $table->index(['warehouse_id', 'material_id', 'created_at'], 'idx_mutations_wh_mat_created');
        });

        // 3. Tool Multi-Warehouse Inventories (Bulk tracking per warehouse)
        Schema::create('tool_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('tool_id')->constrained('tools')->onDelete('cascade');
            $table->integer('stock_total')->default(0);
            $table->integer('stock_available')->default(0);
            $table->integer('stock_borrowed')->default(0);
            $table->integer('stock_maintenance')->default(0);
            $table->integer('stock_damaged')->default(0);
            $table->timestamps();

            $table->unique(['warehouse_id', 'tool_id']);
        });

        // 4. Material Requests (Inter-warehouse transfer & job site requests)
        Schema::create('material_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('from_warehouse_id')->constrained('warehouses');
            $table->foreignId('to_warehouse_id')->constrained('warehouses');
            $table->foreignId('requested_by_user_id')->constrained('users');
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->enum('status', [
                'draft',
                'submitted',
                'approved',
                'partially_fulfilled',
                'fulfilled',
                'rejected',
                'cancelled'
            ])->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 5. Material Request Items
        Schema::create('material_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_request_id')->constrained('material_requests')->onDelete('cascade');
            $table->foreignId('material_id')->nullable()->constrained('materials');
            $table->string('custom_item_name')->nullable();
            $table->string('custom_item_unit', 50)->nullable();
            $table->decimal('qty_requested', 12, 2);
            $table->decimal('qty_approved', 12, 2)->default(0.00);
            $table->decimal('qty_fulfilled', 12, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 6. Material Usages (Field consumption on project sites)
        Schema::create('material_usages', function (Blueprint $table) {
            $table->id();
            $table->string('usage_number')->unique();
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('material_request_id')->nullable()->constrained('material_requests')->nullOnDelete();
            $table->foreignId('issued_by_user_id')->constrained('users');
            $table->string('recipient_name');
            $table->string('job_section')->nullable();
            $table->date('usage_date');
            $table->enum('status', ['completed', 'cancelled'])->default('completed');
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('cancellation_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 7. Material Usage Items
        Schema::create('material_usage_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_usage_id')->constrained('material_usages')->onDelete('cascade');
            $table->foreignId('material_id')->nullable()->constrained('materials');
            $table->string('custom_item_name')->nullable();
            $table->string('custom_item_unit', 50)->nullable();
            $table->decimal('quantity', 12, 2);
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_usage_items');
        Schema::dropIfExists('material_usages');
        Schema::dropIfExists('material_request_items');
        Schema::dropIfExists('material_requests');
        Schema::dropIfExists('tool_inventories');
        Schema::dropIfExists('stock_mutations');
        Schema::dropIfExists('inventories');
    }
};
