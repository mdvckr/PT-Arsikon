
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
        if (Schema::hasTable('purchase_order_items') && !Schema::hasColumn('purchase_order_items', 'total_price')) {
            Schema::table('purchase_order_items', function (Blueprint $table) {
                $table->decimal('total_price', 15, 2)->default(0)->after('subtotal');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('purchase_order_items') && Schema::hasColumn('purchase_order_items', 'total_price')) {
            Schema::table('purchase_order_items', function (Blueprint $table) {
                $table->dropColumn('total_price');
            });
        }
    }
};
