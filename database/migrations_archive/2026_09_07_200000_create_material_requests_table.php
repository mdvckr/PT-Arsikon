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
        Schema::create('material_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('from_warehouse_id')->constrained('warehouses')->onDelete('restrict');
            $table->foreignId('to_warehouse_id')->constrained('warehouses')->onDelete('restrict');
            $table->foreignId('requested_by_user_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', [
                'draft',
                'submitted',
                'approved',
                'partially_fulfilled',
                'fulfilled',
                'rejected',
                'cancelled'
            ])->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_requests');
    }
};
