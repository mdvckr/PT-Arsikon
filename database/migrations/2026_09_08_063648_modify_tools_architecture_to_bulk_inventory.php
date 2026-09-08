<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ubah tabel tools
        Schema::table('tools', function (Blueprint $table) {
            // Kita drop kolom yang tidak diperlukan lagi untuk inventory bulk
            $table->dropColumn(['serial_number', 'status']);
            
            // Kolom stock baru
            $table->integer('stock_total')->default(0)->after('brand');
            $table->integer('stock_available')->default(0)->after('stock_total');
            $table->integer('stock_borrowed')->default(0)->after('stock_available');
            $table->integer('stock_maintenance')->default(0)->after('stock_borrowed');
            $table->integer('stock_damaged')->default(0)->after('stock_maintenance');
        });

        // Hapus unique index pada kode (karena tools.code mungkin kita tidak butuh unik jika tipe data diinput ulang, atau biarkan jika code tetap unik per kategori)
        // Kita biarkan code unik, jadi GEN adalah kode untuk Genset.
        
        // 2. Ubah tabel tool_assignments
        Schema::table('tool_assignments', function (Blueprint $table) {
            $table->integer('quantity')->default(1)->after('tool_id');
        });

        // 3. Ubah tabel tool_inspections (opsional, jika dipakai)
        Schema::table('tool_inspections', function (Blueprint $table) {
            $table->integer('quantity')->default(1)->after('tool_id');
        });

        // 4. Ubah tabel maintenances (opsional, jika dipakai)
        Schema::table('maintenances', function (Blueprint $table) {
            $table->integer('quantity')->default(1)->after('tool_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tools', function (Blueprint $table) {
            $table->string('serial_number')->nullable()->unique();
            $table->enum('status', ['available', 'assigned', 'maintenance', 'damaged', 'lost'])->default('available');
            
            $table->dropColumn(['stock_total', 'stock_available', 'stock_borrowed', 'stock_maintenance', 'stock_damaged']);
        });

        Schema::table('tool_assignments', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });

        Schema::table('tool_inspections', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });

        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }
};

