<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('material_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('material_requests', 'requested_as')) {
                $table->string('requested_as', 50)
                    ->nullable()
                    ->default('Admin Proyek')
                    ->after('requested_by_user_id');
            }
        });

        // Backfill data lama agar memiliki nilai default 'Admin Proyek'
        try {
            DB::statement("
                UPDATE material_requests
                SET requested_as = 'Admin Proyek'
                WHERE requested_as IS NULL
            ");
        } catch (\Throwable $e) {
            // Abaikan jika tabel kosong
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_requests', function (Blueprint $table) {
            if (Schema::hasColumn('material_requests', 'requested_as')) {
                $table->dropColumn('requested_as');
            }
        });
    }
};
