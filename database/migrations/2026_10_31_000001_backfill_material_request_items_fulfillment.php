<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\MaterialRequestItem;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update all existing items with null fulfillment:
        // 1. Items from requests with no source warehouse (from_warehouse_id is null) => 'purchase'
        DB::table('material_request_items')
            ->join('material_requests', 'material_requests.id', '=', 'material_request_items.material_request_id')
            ->whereNull('material_request_items.fulfillment')
            ->whereNull('material_requests.from_warehouse_id')
            ->update(['material_request_items.fulfillment' => 'purchase']);

        // 2. Custom manual items (material_id is null) => 'purchase'
        DB::table('material_request_items')
            ->whereNull('fulfillment')
            ->whereNull('material_id')
            ->update(['fulfillment' => 'purchase']);

        // 3. Any remaining with source warehouse => 'stock'
        DB::table('material_request_items')
            ->whereNull('fulfillment')
            ->update(['fulfillment' => 'stock']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse
    }
};

