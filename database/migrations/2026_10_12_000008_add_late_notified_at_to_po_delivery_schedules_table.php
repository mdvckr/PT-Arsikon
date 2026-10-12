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
        if (Schema::hasTable('po_delivery_schedules')) {
            Schema::table('po_delivery_schedules', function (Blueprint $table) {
                if (! Schema::hasColumn('po_delivery_schedules', 'late_notified_at')) {
                    $table->timestamp('late_notified_at')->nullable()->after('notes');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('po_delivery_schedules')) {
            Schema::table('po_delivery_schedules', function (Blueprint $table) {
                if (Schema::hasColumn('po_delivery_schedules', 'late_notified_at')) {
                    $table->dropColumn('late_notified_at');
                }
            });
        }
    }
};
