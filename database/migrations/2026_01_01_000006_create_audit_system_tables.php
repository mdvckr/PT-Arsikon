<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * System Settings & Infrastructure:
     * Print Templates, Document Counters, and Security Logs.
     */
    public function up(): void
    {
        // 1. Print Templates (Letterheads, PR/PO printing configuration)
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

        // 2. Document Numbering Counters (Sequential number generator)
        Schema::create('document_counters', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50);
            $table->integer('year');
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();

            $table->unique(['type', 'year']);
        });

        // 3. Security Logs (Threat monitoring, auth events, access hardening)
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_logs');
        Schema::dropIfExists('document_counters');
        Schema::dropIfExists('print_templates');
    }
};
