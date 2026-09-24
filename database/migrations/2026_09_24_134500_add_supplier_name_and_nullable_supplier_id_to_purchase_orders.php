<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Make supplier_id nullable on purchase_orders
        try {
            DB::statement('ALTER TABLE purchase_orders MODIFY supplier_id BIGINT UNSIGNED NULL');
        } catch (\Throwable $e) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->unsignedBigInteger('supplier_id')->nullable()->change();
            });
        }

        // 2. Add supplier_name column
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_orders', 'supplier_name')) {
                $table->string('supplier_name')->nullable()->after('supplier_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_orders', 'supplier_name')) {
                $table->dropColumn('supplier_name');
            }
        });

        try {
            DB::statement('ALTER TABLE purchase_orders MODIFY supplier_id BIGINT UNSIGNED NOT NULL');
        } catch (\Throwable $e) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->unsignedBigInteger('supplier_id')->nullable(false)->change();
            });
        }
    }
};
