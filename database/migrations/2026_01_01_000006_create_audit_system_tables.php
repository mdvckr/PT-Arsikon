<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Audit, Inventory Accountability & System Settings:
     * Stock Opnames (Periodic physical vs system counts),
     * Audit Logs (User activity & historical tracking),
     * and Print Templates (Document header & margin templates).
     */
    public function up(): void
    {
        // 1. Stock Opnames
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->string('opname_number')->unique();
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->foreignId('conducted_by_user_id')->constrained('users');
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->date('conducted_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Stock Opname Items
        Schema::create('stock_opname_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained('stock_opnames')->onDelete('cascade');
            $table->foreignId('material_id')->constrained('materials');
            $table->decimal('qty_system', 12, 2);
            $table->decimal('qty_physical', 12, 2)->default(0.00);
            $table->decimal('qty_difference', 12, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. System Audit Logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event');
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            // Strategic index for rapid activity filtering per user and timeline
            $table->index(['user_id', 'created_at'], 'idx_audit_logs_user_created');
        });

        // 4. Print Templates (Letterheads, PR/PO printing configuration)
        Schema::create('print_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->unsignedInteger('width_px')->default(0);
            $table->unsignedInteger('height_px')->default(0);
            $table->boolean('used_for_pr')->default(false);
            $table->boolean('used_for_po')->default(false);
            $table->decimal('padding_top_mm', 5, 1)->default(45.0);
            $table->decimal('padding_left_mm', 5, 1)->default(14.0);
            $table->decimal('padding_right_mm', 5, 1)->default(14.0);
            $table->decimal('padding_bottom_mm', 5, 1)->default(22.0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_templates');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('stock_opname_items');
        Schema::dropIfExists('stock_opnames');
    }
};
