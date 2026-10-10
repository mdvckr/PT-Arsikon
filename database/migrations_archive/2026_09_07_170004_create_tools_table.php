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
        Schema::create('tools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('restrict');
            $table->foreignId('current_warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            $table->string('code')->unique();
            $table->string('serial_number')->nullable()->unique();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->enum('status', ['available', 'assigned', 'maintenance', 'damaged', 'lost'])->default('available');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tools');
    }
};
