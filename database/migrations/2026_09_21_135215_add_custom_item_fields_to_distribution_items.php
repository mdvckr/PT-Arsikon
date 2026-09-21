<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Sprint 3 — Issue 5: Support custom/non-standard items in distribution (surat jalan),
     * such as third-party items, loaned goods, or incidental items not in the system.
     */
    public function up(): void
    {
        Schema::table('distribution_items', function (Blueprint $table) {
            // Free-text name and unit for custom items (used when material_id and tool_id are both null)
            $table->string('custom_item_name')->nullable()->after('tool_assignment_id');
            $table->string('custom_item_unit', 50)->nullable()->after('custom_item_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('distribution_items', function (Blueprint $table) {
            $table->dropColumn(['custom_item_name', 'custom_item_unit']);
        });
    }
};
