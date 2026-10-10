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
        Schema::create('tool_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_assignment_id')->nullable()->constrained('tool_assignments')->onDelete('cascade');
            $table->foreignId('tool_id')->constrained('tools')->onDelete('cascade');
            $table->foreignId('inspected_by_user_id')->constrained('users')->onDelete('restrict');
            $table->enum('condition', ['good', 'damaged', 'lost'])->default('good');
            $table->enum('action_taken', ['returned_to_stock', 'sent_to_maintenance', 'scrapped'])->default('returned_to_stock');
            $table->text('notes')->nullable();
            $table->timestamp('inspected_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tool_inspections');
    }
};
