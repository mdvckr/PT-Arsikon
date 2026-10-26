<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Modul Audit Internal Terpadu:
     * 1. audit_findings (Temuan audit selisih opname, keterlambatan PO, dan manual)
     * 2. audit_action_plans (Tindak lanjut perbaikan & verifikasi auditor)
     * 3. monthly_audit_reports (Arsip rekapitulasi audit eksekutif bulanan)
     */
    public function up(): void
    {
        // 1. Tabel Temuan Audit
        Schema::create('audit_findings', function (Blueprint $table) {
            $table->id();
            $table->string('finding_code', 30)->unique();
            $table->string('source_type', 50); // opname_discrepancy, inventory_discrepancy, po_finance, po_delay, manual, etc.
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

        // 2. Tabel Rencana & Bukti Tindak Lanjut Perbaikan (Action Plans)
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

        // 3. Tabel Arsip Laporan Audit Bulanan Otomatis
        Schema::create('monthly_audit_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_period', 7)->unique(); // Format: YYYY-MM
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
    }
};
