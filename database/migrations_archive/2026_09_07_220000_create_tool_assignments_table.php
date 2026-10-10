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
        Schema::create('tool_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('assignment_number')->unique();
            $table->foreignId('tool_id')->constrained('tools')->onDelete('cascade');
            $table->foreignId('from_warehouse_id')->constrained('warehouses')->onDelete('restrict');
            $table->foreignId('to_warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('assigned_by_user_id')->constrained('users')->onDelete('restrict');
            $table->timestamp('assigned_at')->useCurrent();
            $table->date('expected_return_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->enum('status', ['active', 'returned', 'overdue', 'lost'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tool_assignments');
    }
};
