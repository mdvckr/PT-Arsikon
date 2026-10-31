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

        // 5. Document Numbering Counters (Sequential number generator)
        Schema::create('document_counters', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50);
            $table->integer('year');
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();

            $table->unique(['type', 'year']);
        });

        // 6. Security Logs (Threat monitoring, auth events, access hardening)
        Schema::create('security_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event', 100);
            $table->enum('severity', ['info', 'warning', 'critical'])->default('info');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('url')->nullable();
            $table->string('method', 10)->nullable();
            $table->json('details')->nullable();
            $table->timestamps();

            $table->index(['event', 'created_at'], 'idx_security_logs_event_created');
            $table->index(['user_id', 'created_at'], 'idx_security_logs_user_created');
            $table->index(['ip_address', 'created_at'], 'idx_security_logs_ip_created');
        });

        // 7. Internal Audit Findings (Discrepancy, PO finance/delay, and manual findings)
        Schema::create('audit_findings', function (Blueprint $table) {
            $table->id();
            $table->string('finding_code', 30)->unique();
            $table->string('source_type', 50);
            $table->nullableMorphs('auditable');
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->unsignedBigInteger('stock_opname_id')->nullable();
            $table->string('title');
            $table->text('description');
            $table->decimal('financial_impact', 15, 2)->default(0);
            $table->enum('risk_level', ['Low', 'Medium', 'High', 'Critical'])->default('Medium');
            $table->foreignId('pic_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date');
            $table->string('status', 50)->default('Open');
            $table->text('corrective_action')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('last_reminded_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'due_date']);
            $table->index('source_type');
            $table->index('purchase_order_id');
            $table->index('stock_opname_id');
        });

        // 8. Audit Action Plans (Corrective actions & verification evidence)
        Schema::create('audit_action_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_finding_id')->constrained('audit_findings')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('corrective_action');
            $table->string('evidence_file')->nullable();
            $table->enum('verification_status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('verifier_notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        // 9. Monthly Audit Reports (Executive audit summaries)
        Schema::create('monthly_audit_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_period', 7)->unique(); // YYYY-MM
            $table->integer('total_findings')->default(0);
            $table->integer('resolved_findings')->default(0);
            $table->integer('pending_findings')->default(0);
            $table->integer('overdue_findings')->default(0);
            $table->decimal('total_financial_impact', 15, 2)->default(0);
            $table->json('summary_payload')->nullable();
            $table->timestamp('generated_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_audit_reports');
        Schema::dropIfExists('audit_action_plans');
        Schema::dropIfExists('audit_findings');
        Schema::dropIfExists('security_logs');
        Schema::dropIfExists('document_counters');
        Schema::dropIfExists('print_templates');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('stock_opname_items');
        Schema::dropIfExists('stock_opnames');
    }
};
