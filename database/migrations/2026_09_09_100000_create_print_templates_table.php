<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('print_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->unsignedInteger('width_px')->default(0);
            $table->unsignedInteger('height_px')->default(0);
            $table->boolean('used_for_pr')->default(false);
            $table->boolean('used_for_po')->default(false);
            $table->decimal('padding_top_mm', 5, 1)->default(45.0);
            $table->decimal('padding_left_mm', 5, 1)->default(14.0);
            $table->decimal('padding_right_mm', 5, 1)->default(14.0);
            $table->decimal('padding_bottom_mm', 5, 1)->default(22.0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_templates');
    }
};
