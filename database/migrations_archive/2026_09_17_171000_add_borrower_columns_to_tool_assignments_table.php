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
        Schema::table('tool_assignments', function (Blueprint $table) {
            $table->string('borrower_name')->nullable()->after('assigned_to_user_id');
            $table->string('borrower_phone')->nullable()->after('borrower_name');
            $table->string('location_name')->nullable()->after('borrower_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tool_assignments', function (Blueprint $table) {
            $table->dropColumn(['borrower_name', 'borrower_phone', 'location_name']);
        });
    }
};
