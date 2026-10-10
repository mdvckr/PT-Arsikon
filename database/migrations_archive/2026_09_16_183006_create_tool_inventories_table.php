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
        Schema::create('tool_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('tool_id')->constrained('tools')->onDelete('cascade');
            $table->integer('stock_total')->default(0);
            $table->integer('stock_available')->default(0);
            $table->integer('stock_borrowed')->default(0);
            $table->integer('stock_maintenance')->default(0);
            $table->integer('stock_damaged')->default(0);
            $table->timestamps();

            $table->unique(['warehouse_id', 'tool_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tool_inventories');
    }
};
