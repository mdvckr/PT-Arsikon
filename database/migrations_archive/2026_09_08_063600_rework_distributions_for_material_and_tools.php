<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. distributions: material_request_id boleh kosong (SJ bisa tanpa MR, mis. hanya alat)
        DB::statement('ALTER TABLE distributions MODIFY material_request_id BIGINT UNSIGNED NULL');

        // shipped_by_user_id boleh kosong (draft dibuat dulu, dikirim belakangan)
        DB::statement('ALTER TABLE distributions MODIFY shipped_by_user_id BIGINT UNSIGNED NULL');

        Schema::table('distributions', function (Blueprint $table) {
            $table->date('delivery_date')->nullable()->after('to_warehouse_id');
            $table->string('driver_name')->nullable()->after('delivery_date');
            $table->string('vehicle_number')->nullable()->after('driver_name');
            $table->foreignId('created_by_user_id')->nullable()->after('vehicle_number')
                ->constrained('users')->nullOnDelete();
        });

        // 2. distribution_items: item bisa material ATAU alat
        DB::statement('ALTER TABLE distribution_items MODIFY material_id BIGINT UNSIGNED NULL');

        Schema::table('distribution_items', function (Blueprint $table) {
            $table->foreignId('tool_id')->nullable()->after('material_id')
                ->constrained('tools')->nullOnDelete();
            $table->foreignId('tool_assignment_id')->nullable()->after('tool_id')
                ->constrained('tool_assignments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('distribution_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tool_assignment_id');
            $table->dropConstrainedForeignId('tool_id');
        });

        DB::statement('ALTER TABLE distribution_items MODIFY material_id BIGINT UNSIGNED NOT NULL');

        Schema::table('distributions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by_user_id');
            $table->dropColumn(['vehicle_number', 'driver_name', 'delivery_date']);
        });

        DB::statement('ALTER TABLE distributions MODIFY shipped_by_user_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE distributions MODIFY material_request_id BIGINT UNSIGNED NOT NULL');
    }
};