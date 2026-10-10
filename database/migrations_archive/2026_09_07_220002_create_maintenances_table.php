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
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->string('maintenance_number')->unique();
            $table->foreignId('tool_id')->constrained('tools')->onDelete('cascade');
            $table->foreignId('reported_by_user_id')->constrained('users')->onDelete('restrict');
            $table->string('maintenance_type')->default('repair'); // repair, service, calibration
            $table->decimal('cost', 12, 2)->default(0);
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->date('started_at')->nullable();
            $table->date('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};
