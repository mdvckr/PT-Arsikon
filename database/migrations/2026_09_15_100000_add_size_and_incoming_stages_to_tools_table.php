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
        Schema::table('tools', function (Blueprint $table) {
            if (!Schema::hasColumn('tools', 'size')) {
                $table->string('size')->nullable()->after('name');
            }
            if (!Schema::hasColumn('tools', 'incoming_stages')) {
                $table->json('incoming_stages')->nullable()->after('notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tools', function (Blueprint $table) {
            if (Schema::hasColumn('tools', 'size')) {
                $table->dropColumn('size');
            }
            if (Schema::hasColumn('tools', 'incoming_stages')) {
                $table->dropColumn('incoming_stages');
            }
        });
    }
};
