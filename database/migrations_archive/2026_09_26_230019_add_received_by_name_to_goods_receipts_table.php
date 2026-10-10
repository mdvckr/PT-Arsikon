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
        Schema::table('goods_receipts', function (Blueprint $table) {
            // Nama penerima barang (bebas ketik, tidak harus user sistem)
            $table->string('received_by_name', 150)->nullable()->after('received_by_user_id');
            // Nama supplier bebas (untuk kasus supplier belum terdaftar di master)
            $table->string('supplier_name', 255)->nullable()->after('supplier_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropColumn(['received_by_name', 'supplier_name']);
        });
    }
};
