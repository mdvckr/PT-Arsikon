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
            $table->integer('returned_good')->default(0)->after('returned_at');
            $table->integer('returned_damaged')->default(0)->after('returned_good');
            $table->integer('returned_lost')->default(0)->after('returned_damaged');
            $table->string('condition')->nullable()->after('returned_lost');
            $table->text('return_notes')->nullable()->after('condition');
        });

        Schema::table('tool_loans', function (Blueprint $table) {
            $table->integer('returned_good')->default(0)->after('returned_at');
            $table->integer('returned_damaged')->default(0)->after('returned_good');
            $table->integer('returned_lost')->default(0)->after('returned_damaged');
            $table->text('return_notes')->nullable()->after('returned_lost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tool_assignments', function (Blueprint $table) {
            $table->dropColumn(['returned_good', 'returned_damaged', 'returned_lost', 'condition', 'return_notes']);
        });

        Schema::table('tool_loans', function (Blueprint $table) {
            $table->dropColumn(['returned_good', 'returned_damaged', 'returned_lost', 'return_notes']);
        });
    }
};
