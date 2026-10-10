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
        // Perluas enum status: tambah 'pending' (menunggu persetujuan) & 'rejected' (ditolak).
        DB::statement("ALTER TABLE tool_assignments MODIFY status ENUM('pending','active','returned','overdue','lost','rejected') NOT NULL DEFAULT 'pending'");

        Schema::table('tool_assignments', function (Blueprint $table) {
            $table->foreignId('approved_by_user_id')->nullable()->after('assigned_by_user_id')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by_user_id');
            $table->string('rejection_reason')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tool_assignments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by_user_id');
            $table->dropColumn(['approved_at', 'rejection_reason']);
        });

        DB::statement("ALTER TABLE tool_assignments MODIFY status ENUM('active','returned','overdue','lost') NOT NULL DEFAULT 'active'");
    }
};