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
            if (!Schema::hasColumn('goods_receipts', 'attachment_path')) {
                $table->string('attachment_path', 255)->nullable()->after('invoice_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            if (Schema::hasColumn('goods_receipts', 'attachment_path')) {
                $table->dropColumn('attachment_path');
            }
        });
    }
};

