<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Field Operations & Site Logistics Module:
     * 1. Material Ledgers & Delivery Notes (Buku Catatan Cor Readymix & Material Proyek)
     * 2. Project Tool Rentals & Daily Logs (Sewa Alat Proyek Mingguan & Log Durasi Jam/Hari)
     * 3. Inter-Project Transfers & Mobilization Items (Surat Jalan Pemindahan Barang/Alat Antar Proyek)
     */
    public function up(): void
    {
        // 1. Header Buku Catatan Material (Contoh: "CATATAN MATERIAL COR READYMIX FAK.TEKNIK UGM")
        if (!Schema::hasTable('material_ledgers')) {
            Schema::create('material_ledgers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
                $table->string('project_name')->nullable();
                $table->string('title');
                $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
                $table->string('supplier_name');
                $table->string('account_behalf', 50)->nullable(); // Kolom "AN", contoh "PBAG"
                $table->string('default_unit', 20)->default('m3');
                $table->decimal('default_unit_price', 15, 2)->default(0);

                // Siklus Mingguan
                $table->unsignedSmallInteger('week_number')->nullable();
                $table->unsignedSmallInteger('year')->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();

                // Ringkasan Total
                $table->decimal('total_deposit_vol', 12, 2)->default(0);
                $table->decimal('total_delivered_vol', 12, 2)->default(0);
                $table->decimal('remaining_deposit_vol', 12, 2)->default(0);
                $table->decimal('total_used_amount', 15, 2)->default(0);

                $table->enum('status', ['active', 'completed', 'archived'])->default('active');
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->constrained('users');
                $table->timestamps();
            });
        }

        // 2. Detail Baris Surat Jalan & Pengiriman (T1, T2, dst.)
        if (!Schema::hasTable('material_ledger_entries')) {
            Schema::create('material_ledger_entries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('material_ledger_id')->constrained('material_ledgers')->cascadeOnDelete();
                
                $table->date('delivery_date');
                $table->string('supplier_name')->nullable();
                $table->string('account_behalf', 50)->nullable();
                $table->string('delivery_note_number', 100); // Kolom "NO. SURAT"
                $table->string('material_specification', 255); // Kolom "NAMA MATERIAL" misal K-300, T1
                
                // Kuota Deposit & Pengiriman
                $table->decimal('deposit_volume', 12, 2)->nullable(); // Kolom "VOL. DEPOSIT"
                $table->decimal('delivery_volume', 12, 2)->default(0); // Kolom "VOL PENGIRIMAN"
                $table->string('unit', 20)->default('m3'); // Kolom "SAT"
                $table->decimal('unit_price', 15, 2)->default(0); // Kolom "HARGA"
                $table->decimal('amount_used', 15, 2)->default(0); // Kolom "JUMLAH DIPAKAI"
                
                // Pengelompokan Warna Batch: green, yellow, peach, blue
                $table->string('batch_color', 30)->default('green');
                $table->text('notes')->nullable();
                $table->unsignedInteger('sort_order')->default(1);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 3. Sewa Alat Proyek Mingguan ("SEWA ALAT")
        if (!Schema::hasTable('project_tool_rentals')) {
            Schema::create('project_tool_rentals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
                $table->string('project_name')->nullable();
                $table->string('tool_name');
                $table->string('vendor_name');
                $table->string('docket_number', 100)->nullable();

                // Siklus Mingguan
                $table->unsignedSmallInteger('week_number')->nullable();
                $table->unsignedSmallInteger('year')->nullable();
                $table->date('start_date');
                $table->date('end_date')->nullable();
                $table->decimal('rental_duration', 10, 2)->default(1);
                $table->string('duration_unit', 30)->default('Hari');

                $table->decimal('rate_per_unit', 15, 2)->default(0);
                $table->decimal('deposit_amount', 15, 2)->default(0);
                $table->decimal('total_rental_cost', 15, 2)->default(0);

                $table->enum('status', ['active', 'returned', 'unpaid', 'paid'])->default('active');
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->constrained('users');
                $table->timestamps();
            });
        }

        // 4. Log Durasi & Pemakaian Sewa Alat Harian
        if (!Schema::hasTable('project_tool_rental_logs')) {
            Schema::create('project_tool_rental_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_tool_rental_id')->constrained('project_tool_rentals')->cascadeOnDelete();
                $table->date('usage_date');
                $table->decimal('usage_duration', 10, 2)->default(1); // Jam, Rit, atau Shift
                $table->string('duration_unit', 30)->default('Jam'); // Jam, Rit, Hari, Shift
                $table->string('activity_type', 255); // Contoh: Pengecoran, Layani boredpile, Galian, Langsir
                $table->string('equipment_spec', 255)->nullable(); // Contoh: Truck bak tronton, No Polisi, dll
                $table->text('notes')->nullable();
                $table->unsignedInteger('sort_order')->default(1);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 5. Header Rekap Surat Jalan Pemindahan Barang & Mobilisasi Alat Antar Proyek
        if (!Schema::hasTable('project_material_transfers')) {
            Schema::create('project_material_transfers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
                $table->string('project_name')->nullable();
                $table->string('title'); // Contoh: "SURAT JALAN FAPERTA"
                $table->string('origin_location')->nullable(); // Lokasi asal misal "Proyek Faperta UGM"
                $table->string('destination_location')->nullable(); // Lokasi tujuan misal "Gudang Proyek Gedung A"
                $table->date('transfer_date')->nullable();
                $table->string('docket_number', 100)->nullable(); // Nomor Surat Jalan
                $table->string('driver_or_transporter', 150)->nullable(); // Nama sopir / armada angkut
                $table->enum('status', ['pending', 'in_transit', 'received', 'completed'])->default('received');
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 6. Rincian Item Barang / Alat yang Dimobilisasi
        if (!Schema::hasTable('project_material_transfer_items')) {
            Schema::create('project_material_transfer_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('project_material_transfer_id');
                $table->foreign('project_material_transfer_id', 'fk_pmt_items_transfer_id')
                    ->references('id')
                    ->on('project_material_transfers')
                    ->cascadeOnDelete();
                $table->date('transfer_date')->nullable();
                $table->string('item_name', 255); // Contoh: Blok beton 1,2x1x0,6, WF 600 7m, Jack HYD 300T
                $table->string('unit', 30)->default('Buah'); // Buah, Batang, Unit, Lembar, dll
                $table->decimal('quantity', 10, 2)->default(1);
                $table->text('notes')->nullable();
                $table->unsignedInteger('sort_order')->default(1);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 7. Header Catatan Material Curah Lapangan (Pasir, Batu, Ritase Lokal)
        if (!Schema::hasTable('project_bulk_materials')) {
            Schema::create('project_bulk_materials', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
                $table->string('project_name')->nullable();
                $table->string('title'); // Contoh: "CATATAN PEMBELIAN PASIR & BATU (SUPRI)"
                $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
                $table->string('supplier_name');
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->enum('status', ['active', 'completed', 'archived'])->default('active');
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 8. Rincian Ritase & Pengiriman Harian Material Curah
        if (!Schema::hasTable('project_bulk_material_items')) {
            Schema::create('project_bulk_material_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_bulk_material_id')->constrained('project_bulk_materials')->cascadeOnDelete();
                $table->date('delivery_date');
                $table->string('ticket_number', 100)->nullable(); // No Nota / Bukti Surat
                $table->decimal('quantity', 10, 2)->default(1);
                $table->string('unit', 30)->default('Rit'); // Rit, m3, Truk, Pick Up
                $table->string('material_type', 100); // Pasir, Batu, Batu & 20 Liter, dll
                $table->decimal('unit_price', 15, 2)->default(0);
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->text('notes')->nullable();
                $table->unsignedInteger('sort_order')->default(1);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_bulk_material_items');
        Schema::dropIfExists('project_bulk_materials');
        Schema::dropIfExists('project_material_transfer_items');
        Schema::dropIfExists('project_material_transfers');
        Schema::dropIfExists('project_tool_rental_logs');
        Schema::dropIfExists('project_tool_rentals');
        Schema::dropIfExists('material_ledger_entries');
        Schema::dropIfExists('material_ledgers');
    }
};
