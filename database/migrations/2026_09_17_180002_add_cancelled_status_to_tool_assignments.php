<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Extend enum to include 'cancelled'
        DB::statement("ALTER TABLE tool_assignments MODIFY status ENUM('pending','active','returned','overdue','lost','rejected','cancelled') NOT NULL DEFAULT 'pending'");

        Schema::table('tool_assignments', function (Blueprint $table) {
            $table->timestamp('cancelled_at')->nullable()->after('rejection_reason');
            $table->foreignId('cancelled_by_user_id')->nullable()->after('cancelled_at')->constrained('users')->nullOnDelete();
            $table->string('cancellation_reason')->nullable()->after('cancelled_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('tool_assignments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cancelled_by_user_id');
            $table->dropColumn(['cancelled_at', 'cancellation_reason']);
        });

        DB::statement("ALTER TABLE tool_assignments MODIFY status ENUM('pending','active','returned','overdue','lost','rejected') NOT NULL DEFAULT 'pending'");
    }
};
