<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Field Operations & Logistics:
     * Tool Loans, Tool Assignments, Tool Inspections, Maintenances,
     * Distributions (Inter-warehouse transfer & Surat Jalan), and Distribution Items.
     */
    public function up(): void
    {
        // 1. Tool Loans (Grouped/External & Project Site Tool Loans)
        Schema::create('tool_loans', function (Blueprint $table) {
            $table->id();
            $table->string('loan_number')->unique();
            $table->foreignId('from_warehouse_id')->constrained('warehouses');
            $table->foreignId('assigned_by_user_id')->constrained('users');
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->string('borrower_name');
            $table->string('borrower_phone')->nullable();
            $table->string('location_name');
            $table->timestamp('assigned_at')->useCurrent();
            $table->date('expected_return_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->integer('returned_good')->default(0);
            $table->integer('returned_damaged')->default(0);
            $table->integer('returned_lost')->default(0);
            $table->text('return_notes')->nullable();
            $table->enum('status', [
                'pending',
                'active',
                'returned',
                'overdue',
                'lost',
                'rejected',
                'cancelled'
            ])->default('pending');
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
        });

        // 2. Tool Assignments (Quantified assignments per tool, linked to loan or warehouse)
        Schema::create('tool_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_loan_id')->nullable()->constrained('tool_loans')->onDelete('cascade');
            $table->string('assignment_number')->unique();
            $table->foreignId('tool_id')->constrained('tools')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->foreignId('from_warehouse_id')->constrained('warehouses');
            $table->foreignId('to_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('borrower_name')->nullable();
            $table->string('borrower_phone')->nullable();
            $table->string('location_name')->nullable();
            $table->foreignId('assigned_by_user_id')->constrained('users');
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('assigned_at')->useCurrent();
            $table->date('expected_return_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->integer('returned_good')->default(0);
            $table->integer('returned_damaged')->default(0);
            $table->integer('returned_lost')->default(0);
            $table->string('condition')->nullable();
            $table->text('return_notes')->nullable();
            $table->enum('status', [
                'pending',
                'active',
                'returned',
                'overdue',
                'lost',
                'rejected',
                'cancelled'
            ])->default('pending');
            $table->text('notes')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('cancellation_reason')->nullable();
            $table->timestamps();
        });

        // 3. Tool Inspections (Condition audit post-assignment / periodic)
        Schema::create('tool_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_assignment_id')->nullable()->constrained('tool_assignments')->onDelete('cascade');
            $table->foreignId('tool_id')->constrained('tools')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->foreignId('inspected_by_user_id')->constrained('users');
            $table->enum('condition', ['good', 'damaged', 'lost'])->default('good');
            $table->enum('action_taken', ['returned_to_stock', 'sent_to_maintenance', 'scrapped'])->default('returned_to_stock');
            $table->text('notes')->nullable();
            $table->timestamp('inspected_at')->useCurrent();
            $table->timestamps();
        });

        // 4. Maintenances (Service, repair, and overhaul tracking)
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->string('maintenance_number')->unique();
            $table->foreignId('tool_id')->constrained('tools')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->foreignId('reported_by_user_id')->constrained('users');
            $table->string('maintenance_type')->default('repair');
            $table->decimal('cost', 12, 2)->default(0.00);
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->date('started_at')->nullable();
            $table->date('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 5. Distributions (Inter-warehouse transfer with Surat Jalan integration)
        Schema::create('distributions', function (Blueprint $table) {
            $table->id();
            $table->string('distribution_number')->unique();
            $table->string('surat_jalan')->nullable();
            $table->foreignId('material_request_id')->nullable()->constrained('material_requests')->onDelete('cascade');
            $table->foreignId('from_warehouse_id')->constrained('warehouses');
            $table->foreignId('to_warehouse_id')->constrained('warehouses');
            $table->date('delivery_date')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('vehicle_number')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('shipped_by_user_id')->nullable()->constrained('users');
            $table->foreignId('received_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('shipped_at')->useCurrent();
            $table->timestamp('received_at')->nullable();
            $table->enum('status', ['draft', 'in_transit', 'completed', 'cancelled'])->default('in_transit');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Strategic composite indexes for tracking shipping status per warehouse
            $table->index(['from_warehouse_id', 'status', 'created_at'], 'idx_dist_from_status_created');
            $table->index(['to_warehouse_id', 'status', 'created_at'], 'idx_dist_to_status_created');
        });

        // 6. Distribution Items (Material and Tool cross-transfer items)
        Schema::create('distribution_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distribution_id')->constrained('distributions')->onDelete('cascade');
            $table->foreignId('material_id')->nullable()->constrained('materials');
            $table->foreignId('tool_id')->nullable()->constrained('tools')->nullOnDelete();
            $table->foreignId('tool_assignment_id')->nullable()->constrained('tool_assignments')->nullOnDelete();
            $table->string('custom_item_name')->nullable();
            $table->string('custom_item_unit', 50)->nullable();
            $table->decimal('qty_shipped', 12, 2);
            $table->decimal('qty_received', 12, 2)->default(0.00);
            $table->decimal('qty_damaged_or_lost', 12, 2)->default(0.00);
            $table->decimal('qty_lost', 12, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distribution_items');
        Schema::dropIfExists('distributions');
        Schema::dropIfExists('maintenances');
        Schema::dropIfExists('tool_inspections');
        Schema::dropIfExists('tool_assignments');
        Schema::dropIfExists('tool_loans');
    }
};
