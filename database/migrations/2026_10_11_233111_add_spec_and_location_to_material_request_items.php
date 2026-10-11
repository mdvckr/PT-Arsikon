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
        Schema::table('material_request_items', function (Blueprint $table) {
            $table->string('specification', 255)->nullable()->after('notes');
            $table->string('usage_location', 255)->nullable()->after('specification');
            $table->date('usage_date')->nullable()->after('usage_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_request_items', function (Blueprint $table) {
            $table->dropColumn(['specification', 'usage_location', 'usage_date']);
        });
    }
};
