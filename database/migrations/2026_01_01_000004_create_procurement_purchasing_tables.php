<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Procurement & Purchasing Suite:
     * Procurement Requests (PR), Purchase Orders (PO), Goods Receipts (GR),
     * Field Purchase Receipts, Payments & Termins, and Material Returns.
     */
    public function up(): void
    {
        // 1. Procurement Requests
        Schema::create('procurement_requests', function (Blueprint $table) {
            $table->id();
            $table->string('pr_number')->unique();
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('material_request_id')->nullable()->constrained('material_requests')->nullOnDelete();
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'po_created'])->default('draft');
            $table->date('needed_by')->nullable();
            $table->text('justification')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        // 2. Procurement Request Items
        Schema::create('procurement_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_request_id')->constrained('procurement_requests')->onDelete('cascade');
            $table->foreignId('material_id')->constrained('materials')->onDelete('cascade');
            $table->decimal('quantity', 12, 2);
            $table->decimal('estimated_price', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Purchase Orders
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->foreignId('procurement_request_id')->nullable()->constrained('procurement_requests')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('cascade');
            $table->string('supplier_name')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['draft', 'sent', 'partial_received', 'received', 'cancelled'])->default('draft');
            $table->date('order_date');
            $table->date('expected_delivery')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->decimal('paid_amount', 15, 2)->default(0.00);
            $table->text('terms')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        // 4. Purchase Order Items (with custom item support, unit price, subtotal, and total price)
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->onDelete('cascade');
            $table->foreignId('material_id')->nullable()->constrained('materials')->onDelete('cascade');
            $table->string('custom_item_name')->nullable();
            $table->string('custom_item_unit', 50)->nullable();
            $table->foreignId('material_request_item_id')->nullable()->constrained('material_request_items')->nullOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->decimal('received_qty', 12, 2)->default(0.00);
            $table->decimal('unit_price', 15, 2)->default(0.00);
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('total_price', 15, 2)->default(0.00);
            $table->string('unit')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 5. Goods Receipts (Incoming Warehouse Verification)
        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            $table->string('status')->default('draft');
            $table->string('invoice_number', 100)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->string('supplier_name')->nullable();
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->foreignId('received_by_user_id')->constrained('users');
            $table->string('received_by_name', 150)->nullable();
            $table->date('receipt_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 6. Goods Receipt Items (Multi-type: materials and tools with stages)
        Schema::create('goods_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_id')->constrained('goods_receipts')->onDelete('cascade');
            $table->string('item_type', 20)->default('material');
            $table->foreignId('purchase_order_item_id')->nullable()->constrained('purchase_order_items')->nullOnDelete();
            $table->string('stage_reference', 100)->nullable();
            $table->foreignId('material_id')->nullable()->constrained('materials');
            $table->foreignId('tool_id')->nullable()->constrained('tools')->nullOnDelete();
            $table->decimal('qty_received', 12, 2);
            $table->string('condition', 20)->default('good');
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 7. Field Purchase Receipts (Petty Cash / Direct Receipts with receipt photos)
        Schema::create('purchase_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('project_name')->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('supplier_name')->nullable();
            $table->string('receipt_number')->nullable();
            $table->date('receipt_date');
            $table->string('day_label')->nullable();
            $table->string('image_path')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->string('payment_status')->default('unpaid');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // 8. Purchase Receipt Items
        Schema::create('purchase_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_receipt_id')->constrained('purchase_receipts')->onDelete('cascade');
            $table->foreignId('material_id')->nullable()->constrained('materials')->nullOnDelete();
            $table->string('item_name')->nullable();
            $table->decimal('quantity', 12, 2)->default(1.00);
            $table->decimal('unit_price', 15, 2)->default(0.00);
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        // 9. Payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['draft', 'pending', 'verified', 'rejected'])->default('draft');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method')->nullable();
            $table->date('payment_date');
            $table->string('bank_account')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('proof_file')->nullable();
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        // 10. Payment Items (Termin & Milestone Breakdown)
        Schema::create('payment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->onDelete('cascade');
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->integer('termin')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 11. Material Returns
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique();
            $table->foreignId('from_warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('to_warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['draft', 'pending', 'approved', 'received', 'rejected'])->default('draft');
            $table->enum('reason', ['excess', 'damaged', 'wrong_item', 'project_complete', 'other'])->default('excess');
            $table->date('return_date');
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 12. Return Items
        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')->constrained('returns')->onDelete('cascade');
            $table->foreignId('material_id')->constrained('materials')->onDelete('cascade');
            $table->decimal('quantity', 12, 2);
            $table->decimal('received_qty', 12, 2)->default(0.00);
            $table->enum('condition', ['good', 'damaged', 'unusable'])->default('good');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_items');
        Schema::dropIfExists('returns');
        Schema::dropIfExists('payment_items');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('purchase_receipt_items');
        Schema::dropIfExists('purchase_receipts');
        Schema::dropIfExists('goods_receipt_items');
        Schema::dropIfExists('goods_receipts');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('procurement_request_items');
        Schema::dropIfExists('procurement_requests');
    }
};
