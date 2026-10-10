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
        if (Schema::hasTable('suppliers') && !Schema::hasColumn('suppliers', 'contact_person')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->string('contact_person')->nullable()->after('name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('suppliers') && Schema::hasColumn('suppliers', 'contact_person')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->dropColumn('contact_person');
            });
        }
    }
};
