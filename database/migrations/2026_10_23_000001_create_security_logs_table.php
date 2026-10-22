<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Layer 7.1 Security Hardening: Dedicated Security Audit & Threat Monitoring Logs
     */
    public function up(): void
    {
        Schema::create('security_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event', 100); // e.g. auth.login_success, auth.login_failed, auth.lockout, auth.logout, access.forbidden, admin.role_change
            $table->enum('severity', ['info', 'warning', 'critical'])->default('info');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('url')->nullable();
            $table->string('method', 10)->nullable();
            $table->json('details')->nullable();
            $table->timestamps();

            // Indeks query untuk monitoring, filter timeline, dan deteksi brute-force
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
    }
};
