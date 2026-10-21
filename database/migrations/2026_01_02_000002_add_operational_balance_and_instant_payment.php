<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah operational_balance pada tabel projects
        if (Schema::hasTable('projects') && !Schema::hasColumn('projects', 'operational_balance')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->decimal('operational_balance', 15, 2)->default(0.00)->after('status');
            });
        }

        // 2. Tambah kolom pendukung pembayaran instan & saldo pada tabel payments
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                if (!Schema::hasColumn('payments', 'project_id')) {
                    $table->foreignId('project_id')->nullable()->after('purchase_order_id')->constrained('projects')->nullOnDelete();
                }
                if (!Schema::hasColumn('payments', 'payment_type')) {
                    $table->string('payment_type', 30)->default('waiting')->after('status'); // 'waiting' (PO Proyek reguler), 'instant' (Saldo Kas Nota Harian)
                }
                if (!Schema::hasColumn('payments', 'balance_before')) {
                    $table->decimal('balance_before', 15, 2)->nullable()->after('amount');
                }
                if (!Schema::hasColumn('payments', 'balance_after')) {
                    $table->decimal('balance_after', 15, 2)->nullable()->after('balance_before');
                }
            });
        }

        // 3. Tabel riwayat mutasi / top-up saldo kas proyek (Petty Cash Admin PO)
        if (!Schema::hasTable('project_balance_logs')) {
            Schema::create('project_balance_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
                $table->string('type', 30); // 'topup', 'payment_deduction', 'adjustment'
                $table->decimal('amount', 15, 2);
                $table->decimal('balance_before', 15, 2)->default(0.00);
                $table->decimal('balance_after', 15, 2)->default(0.00);
                $table->string('reference_number')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('project_balance_logs');

        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                if (Schema::hasColumn('payments', 'balance_after')) {
                    $table->dropColumn('balance_after');
                }
                if (Schema::hasColumn('payments', 'balance_before')) {
                    $table->dropColumn('balance_before');
                }
                if (Schema::hasColumn('payments', 'payment_type')) {
                    $table->dropColumn('payment_type');
                }
                if (Schema::hasColumn('payments', 'project_id')) {
                    $table->dropForeign(['project_id']);
                    $table->dropColumn('project_id');
                }
            });
        }

        if (Schema::hasTable('projects') && Schema::hasColumn('projects', 'operational_balance')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropColumn('operational_balance');
            });
        }
    }
};
