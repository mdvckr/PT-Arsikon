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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['draft', 'pending', 'verified', 'rejected'])->default('draft');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method')->nullable(); // transfer, cash, cek
            $table->date('payment_date');
            $table->string('bank_account')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('proof_file')->nullable(); // bukti transfer/kwitansi
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
