<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Sprint 3 — Issue 4: Support custom/non-standard items in material usage
     * (items that don't exist in the materials table, e.g. incidental field materials).
     */
    public function up(): void
    {
        Schema::table('material_usage_items', function (Blueprint $table) {
            // Make material_id nullable to support custom items without a DB record
            $table->unsignedBigInteger('material_id')->nullable()->change();

            // Free-text name and unit for custom items (used when material_id is null)
            $table->string('custom_item_name')->nullable()->after('material_id');
            $table->string('custom_item_unit', 50)->nullable()->after('custom_item_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_usage_items', function (Blueprint $table) {
            $table->dropColumn(['custom_item_name', 'custom_item_unit']);
            $table->unsignedBigInteger('material_id')->nullable(false)->change();
        });
    }
};
