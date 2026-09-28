<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Material;
use App\Models\StockMutation;
use App\Models\Supplier;
use App\Models\Tool;
use App\Models\ToolAssignment;
use App\Models\ToolInventory;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $baseDate = Carbon::create(2026, 9, 26, 8, 0, 0);

        // 1. Seed Units
        $unitKg = Unit::firstOrCreate(['code' => 'kg'], ['name' => 'Kilogram', 'is_decimal' => true]);
        $unitBtg = Unit::firstOrCreate(['code' => 'btg'], ['name' => 'Batang', 'is_decimal' => false]);
        $unitRoll = Unit::firstOrCreate(['code' => 'roll'], ['name' => 'Roll', 'is_decimal' => false]);
        $unitRit = Unit::firstOrCreate(['code' => 'rit'], ['name' => 'Rit', 'is_decimal' => false]);
        $unitPcs = Unit::firstOrCreate(['code' => 'pcs'], ['name' => 'Pieces / Buah', 'is_decimal' => false]);
        $unitSak = Unit::firstOrCreate(['code' => 'sak'], ['name' => 'Sak / Bag', 'is_decimal' => false]);
        $unitM3 = Unit::firstOrCreate(['code' => 'm3'], ['name' => 'Meter Kubik', 'is_decimal' => true]);

        // 2. Seed Categories (Material)
        $catArsitek = Category::firstOrCreate(
            ['code' => 'CAT-ARS'],
            ['name' => 'ARSITEK', 'type' => 'material', 'description' => 'Material kategori Arsitektur']
        );
        $catMep = Category::firstOrCreate(
            ['code' => 'CAT-MEP'],
            ['name' => 'MEP', 'type' => 'material', 'description' => 'Material kategori MEP']
        );
        $catStruktur = Category::firstOrCreate(
            ['code' => 'CAT-STR'],
            ['name' => 'STRUKTUR', 'type' => 'material', 'description' => 'Material kategori Struktur']
        );

    }
}
