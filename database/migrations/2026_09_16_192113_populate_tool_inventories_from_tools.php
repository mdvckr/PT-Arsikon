<?php

use App\Models\Tool;
use App\Models\ToolInventory;
use App\Models\Warehouse;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Migrate aggregate tool stock fields into per-warehouse tool_inventories.
     */
    public function up(): void
    {
        $defaultWarehouse = Warehouse::where('is_central', true)->first()
            ?? Warehouse::orderBy('id')->first();

        Tool::all()->each(function (Tool $tool) use ($defaultWarehouse) {
            $warehouse = $tool->current_warehouse_id
                ? Warehouse::find($tool->current_warehouse_id) ?? $defaultWarehouse
                : $defaultWarehouse;

            if (!$warehouse) {
                return;
            }

            ToolInventory::updateOrCreate(
                [
                    'tool_id'      => $tool->id,
                    'warehouse_id' => $warehouse->id,
                ],
                [
                    'stock_total'      => $tool->getRawOriginal('stock_total') ?? 0,
                    'stock_available'  => $tool->getRawOriginal('stock_available') ?? 0,
                    'stock_borrowed'   => $tool->getRawOriginal('stock_borrowed') ?? 0,
                    'stock_maintenance'=> $tool->getRawOriginal('stock_maintenance') ?? 0,
                    'stock_damaged'    => $tool->getRawOriginal('stock_damaged') ?? 0,
                ]
            );
        });
    }

    /**
     * Reverse the migration — simply delete the tool_inventories rows.
     */
    public function down(): void
    {
        DB::table('tool_inventories')->truncate();
    }
};
