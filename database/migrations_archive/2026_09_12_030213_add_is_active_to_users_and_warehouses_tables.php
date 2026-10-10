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
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'is_active')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('password');
            });
        }

        if (Schema::hasTable('warehouses') && !Schema::hasColumn('warehouses', 'is_active')) {
            Schema::table('warehouses', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('is_central');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'is_active')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }

        if (Schema::hasTable('warehouses') && Schema::hasColumn('warehouses', 'is_active')) {
            Schema::table('warehouses', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
};
