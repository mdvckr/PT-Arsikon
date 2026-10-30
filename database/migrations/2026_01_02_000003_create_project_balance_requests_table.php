<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Utama Pengajuan Kas Lapangan (Project Balance Requests)
        if (!Schema::hasTable('project_balance_requests')) {
            Schema::create('project_balance_requests', function (Blueprint $table) {
                $table->id();
                $table->string('request_number', 50)->nullable()->unique();
                $table->date('request_date')->nullable();
                $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
                $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
                $table->decimal('amount', 15, 2);
                $table->text('purpose'); // Alasan/keperluan belanja nota harian
                $table->string('urgency', 20)->default('normal'); // 'normal', 'urgent'
                $table->string('status', 20)->default('pending'); // 'pending', 'approved', 'rejected'
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->string('reference_number', 100)->nullable(); // Bukti kas / transfer kantor pusat
                $table->text('rejection_reason')->nullable();
                $table->string('signer_requester', 150)->nullable(); // Nama pemohon (Admin PO Proyek/Pelaksana)
                $table->string('signer_site_manager', 150)->nullable(); // Mengetahui (Site Manager)
                $table->string('signer_approver', 150)->nullable(); // Disetujui (Kantor Pusat/Owner)
                $table->timestamps();
            });
        } else {
            Schema::table('project_balance_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('project_balance_requests', 'request_number')) {
                    $table->string('request_number', 50)->nullable()->unique()->after('id');
                }
                if (!Schema::hasColumn('project_balance_requests', 'request_date')) {
                    $table->date('request_date')->nullable()->after('request_number');
                }
                if (!Schema::hasColumn('project_balance_requests', 'signer_requester')) {
                    $table->string('signer_requester', 150)->nullable()->after('rejection_reason');
                }
                if (!Schema::hasColumn('project_balance_requests', 'signer_site_manager')) {
                    $table->string('signer_site_manager', 150)->nullable()->after('signer_requester');
                }
                if (!Schema::hasColumn('project_balance_requests', 'signer_approver')) {
                    $table->string('signer_approver', 150)->nullable()->after('signer_site_manager');
                }
            });
        }

        // 2. Tabel Rincian Item Kebutuhan Pengajuan Kas Lapangan (Items / RAB Kas)
        if (!Schema::hasTable('project_balance_request_items')) {
            Schema::create('project_balance_request_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_balance_request_id')->constrained('project_balance_requests')->cascadeOnDelete();
                $table->string('item_name'); // Nama barang / uraian kebutuhan
                $table->decimal('quantity', 12, 2)->default(1.00);
                $table->string('unit', 50)->nullable(); // pcs, sak, kg, hari, dll
                $table->decimal('unit_price', 15, 2)->default(0.00); // Harga satuan
                $table->decimal('total_price', 15, 2)->default(0.00); // Subtotal (qty * unit_price)
                $table->text('notes')->nullable(); // Catatan per item
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('project_balance_request_items');
        Schema::dropIfExists('project_balance_requests');
    }
};
