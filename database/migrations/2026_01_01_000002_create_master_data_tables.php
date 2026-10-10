<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Master Data Tables:
     * Projects, Warehouses, User Warehouse Assignments,
     * Categories, Units, Suppliers, Materials, and Tools.
     */
    public function up(): void
    {
        // 1. Projects
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('location')->nullable();
            $table->enum('status', ['planning', 'active', 'completed', 'on_hold'])->default('active');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });

        // 2. Warehouses
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('cascade');
            $table->string('code')->unique();
            $table->string('name');
            $table->enum('type', ['central', 'project'])->default('project');
            $table->boolean('is_central')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('address')->nullable();
            $table->timestamps();
        });

        // 3. User Warehouse Assignments (Pivot / Access Control)
        Schema::create('user_warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->string('role_in_warehouse')->default('member');
            $table->timestamps();
            $table->unique(['user_id', 'warehouse_id']);
        });

        // 4. Categories
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->enum('type', ['material', 'tool'])->default('material');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 5. Units
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->boolean('is_decimal')->default(false);
            $table->timestamps();
        });

        // 6. Suppliers
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 7. Materials (with full specifications, brand, size, stages, and optimized indexes)
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('supplier_name')->nullable();
            $table->foreignId('unit_id')->constrained('units');
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('size')->nullable();
            $table->string('type')->nullable();
            $table->decimal('min_stock_central', 12, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->json('incoming_stages')->nullable();
            $table->timestamps();

            // Composite Indexes for high performance querying
            $table->index(['category_id', 'is_active'], 'idx_materials_category_active');
            $table->index(['is_active', 'name'], 'idx_materials_active_name');
        });

        // 8. Tools (Bulk/Quantified Tool Master with operational status tracking)
        Schema::create('tools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('current_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('size')->nullable();
            $table->string('type')->nullable();
            $table->string('brand')->nullable();
            $table->integer('stock_total')->default(0);
            $table->integer('stock_available')->default(0);
            $table->integer('stock_borrowed')->default(0);
            $table->integer('stock_maintenance')->default(0);
            $table->integer('stock_damaged')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->json('incoming_stages')->nullable();
            $table->timestamps();

            // Composite Indexes for fast filtering
            $table->index(['category_id', 'is_active'], 'idx_tools_category_active');
            $table->index(['is_active', 'name'], 'idx_tools_active_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tools');
        Schema::dropIfExists('materials');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('units');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('user_warehouses');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('projects');
    }
};
