<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('project_balance_requests')) {
            Schema::create('project_balance_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
                $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
                $table->decimal('amount', 15, 2);
                $table->text('purpose'); // Alasan/keperluan belanja nota harian
                $table->string('urgency', 20)->default('normal'); // 'normal', 'urgent'
                $table->string('status', 20)->default('pending'); // 'pending', 'approved', 'rejected'
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->string('reference_number', 100)->nullable(); // Bukti kas / transfer kantor pusat
                $table->text('rejection_reason')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('project_balance_requests');
    }
};
