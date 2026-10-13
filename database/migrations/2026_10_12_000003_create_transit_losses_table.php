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
        if (! Schema::hasTable('transit_losses')) {
            Schema::create('transit_losses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('distribution_id')->nullable()->constrained('distributions')->nullOnDelete();
                $table->foreignId('distribution_item_id')->nullable()->constrained('distribution_items')->nullOnDelete();
                $table->foreignId('goods_receipt_id')->nullable()->constrained('goods_receipts')->nullOnDelete();
                $table->foreignId('goods_receipt_item_id')->nullable()->constrained('goods_receipt_items')->nullOnDelete();
                $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
                $table->foreignId('purchase_order_item_id')->nullable()->constrained('purchase_order_items')->nullOnDelete();
                $table->foreignId('material_id')->nullable()->constrained('materials')->nullOnDelete();
                $table->foreignId('tool_id')->nullable()->constrained('tools')->nullOnDelete();
                $table->decimal('qty', 12, 2)->default(0.00);
                $table->decimal('lost_qty', 12, 2)->default(0.00);
                $table->decimal('unit_price', 12, 2)->nullable();
                $table->decimal('loss_value', 12, 2)->default(0.00);
                $table->enum('status', ['recorded', 'claim_filed', 'recovered', 'written_off'])->default('recorded');
                $table->string('evidence_path')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transit_losses');
    }
};
