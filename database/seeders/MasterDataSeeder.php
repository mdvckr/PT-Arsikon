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

        // Tool Categories
        $catAlatBerat = Category::firstOrCreate(
            ['code' => 'CAT-TOOL-01'],
            ['name' => 'Alat Berat & Elektrik', 'type' => 'tool', 'description' => 'Genset, molen, bor listrik']
        );
        $catAlatTangan = Category::firstOrCreate(
            ['code' => 'CAT-TOOL-02'],
            ['name' => 'Alat Tangan', 'type' => 'tool', 'description' => 'Cangkul, sekop, gerobak dorong']
        );

        // 3. Seed Suppliers
        $supTokoCatLancar = Supplier::firstOrCreate(
            ['code' => 'SUP-TCL'],
            [
                'name' => 'Toko Cat Lancar',
                'email' => 'sales@catlancar.com',
                'phone' => '081234567001',
                'address' => 'Jl. Magelang No. 12, Yogyakarta',
                'is_active' => true,
            ]
        );

        $supJayaRaya = Supplier::firstOrCreate(
            ['code' => 'SUP-JR'],
            [
                'name' => 'Jaya Raya',
                'email' => 'contact@jayaraya.com',
                'phone' => '081234567002',
                'address' => 'Jl. Industri No. 45, Jakarta',
                'is_active' => true,
            ]
        );

        $supIndoJaya = Supplier::firstOrCreate(
            ['code' => 'SUP-IJ'],
            [
                'name' => 'Indo Jaya',
                'email' => 'info@indojaya.com',
                'phone' => '081234567003',
                'address' => 'Jl. Listrik Mandiri No. 8, Tangerang',
                'is_active' => true,
            ]
        );

        // 4. Materials Data & Per-Warehouse Stocks
        // Stok disesuaikan agar pada Gudang Proyek FK Teknik (W-PRJ-001) tampil sesuai foto (1 kg, 1 btg, 2 roll, 1 rit)
        // dan Gudang Pusat (W-CENTRAL) serta Gudang Proyek B (W-PRJ-002) juga memiliki stok yang siap dipakai.
        $materialsData = [
            [
                'sku' => 'ARS-CJA-20',
                'name' => 'Cat Jotaplast APL 20 KG',
                'brand' => 'Jotaplast',
                'type' => 'Cat Jotaplast APL',
                'size' => '20 KG',
                'category_id' => $catArsitek->id,
                'unit_id' => $unitKg->id,
                'supplier_id' => $supTokoCatLancar->id,
                'supplier_name' => $supTokoCatLancar->name,
                'min_stock_central' => 5,
                'is_active' => true,
                'description' => 'Cat Jotaplast APL ukuran 20 KG',
                'created_at' => $baseDate->copy()->setTime(8, 0, 0),
                'updated_at' => $baseDate->copy()->setTime(8, 0, 0),
                'stocks' => [
                    'central' => ['qty' => 50, 'min' => 10],
                    'prj_a'   => ['qty' => 1,  'min' => 0],
                    'prj_b'   => ['qty' => 5,  'min' => 1],
                ],
            ],
            [
                'sku' => 'ME-KNYM',
                'name' => 'Kabel NYM 3×2,5 - 100m',
                'brand' => null,
                'type' => 'Kabel NYM',
                'size' => '3×2,5 - 100m',
                'category_id' => $catMep->id,
                'unit_id' => $unitRoll->id,
                'supplier_id' => $supIndoJaya->id,
                'supplier_name' => $supIndoJaya->name,
                'min_stock_central' => 2,
                'is_active' => true,
                'description' => 'Kabel NYM 3x2.5mm panjang 100m',
                'created_at' => $baseDate->copy()->setTime(9, 0, 0),
                'updated_at' => $baseDate->copy()->setTime(9, 0, 0),
                'stocks' => [
                    'central' => ['qty' => 20, 'min' => 5],
                    'prj_a'   => ['qty' => 2,  'min' => 0],
                    'prj_b'   => ['qty' => 3,  'min' => 1],
                ],
            ],
            [
                'sku' => 'MEP-',
                'name' => 'Pipa Rucika D 4"',
                'brand' => 'Rucika',
                'type' => 'Pipa',
                'size' => 'D 4"',
                'category_id' => $catMep->id,
                'unit_id' => $unitBtg->id,
                'supplier_id' => $supJayaRaya->id,
                'supplier_name' => $supJayaRaya->name,
                'min_stock_central' => 5,
                'is_active' => true,
                'description' => 'Pipa PVC Rucika tipe D ukuran 4 inch',
                'created_at' => $baseDate->copy()->setTime(10, 0, 0),
                'updated_at' => $baseDate->copy()->setTime(10, 0, 0),
                'stocks' => [
                    'central' => ['qty' => 40, 'min' => 10],
                    'prj_a'   => ['qty' => 1,  'min' => 0],
                    'prj_b'   => ['qty' => 5,  'min' => 1],
                ],
            ],
            [
                'sku' => 'STR-PSL',
                'name' => 'Pasir Lokal',
                'brand' => null,
                'type' => 'Pasir',
                'size' => 'Lokal',
                'category_id' => $catStruktur->id,
                'unit_id' => $unitRit->id,
                'supplier_id' => $supJayaRaya->id,
                'supplier_name' => $supJayaRaya->name,
                'min_stock_central' => 2,
                'is_active' => true,
                'description' => 'Pasir lokal berkualitas untuk pekerjaan struktur',
                'created_at' => $baseDate->copy()->setTime(11, 0, 0),
                'updated_at' => $baseDate->copy()->setTime(11, 0, 0),
                'stocks' => [
                    'central' => ['qty' => 25, 'min' => 5],
                    'prj_a'   => ['qty' => 1,  'min' => 0],
                    'prj_b'   => ['qty' => 2,  'min' => 1],
                ],
            ],
        ];

        $centralWarehouse   = Warehouse::where('is_central', true)->first();
        $projectWarehouseA = Warehouse::where('code', 'W-PRJ-001')->first() ?? Warehouse::where('is_central', false)->first();
        $projectWarehouseB = Warehouse::where('code', 'W-PRJ-002')->first();
        $adminUser         = User::where('email', 'admin.pusat@arsikon.co.id')->first() ?? User::first();

        $warehouseMap = [
            'central' => $centralWarehouse,
            'prj_a'   => $projectWarehouseA,
            'prj_b'   => $projectWarehouseB,
        ];

        foreach ($materialsData as $data) {
            $stocks = $data['stocks'] ?? [];
            unset($data['stocks']);

            $mat = Material::updateOrCreate(
                ['sku' => $data['sku']],
                $data
            );

            foreach ($stocks as $whKey => $stockInfo) {
                $wh = $warehouseMap[$whKey] ?? null;
                if (!$wh || empty($stockInfo['qty'])) {
                    continue;
                }

                $qty = (float) $stockInfo['qty'];
                $min = (float) ($stockInfo['min'] ?? 0);

                // Buat atau update Inventory di gudang terkait
                Inventory::updateOrCreate(
                    [
                        'warehouse_id' => $wh->id,
                        'material_id'  => $mat->id,
                    ],
                    [
                        'quantity'  => $qty,
                        'min_stock' => $min,
                    ]
                );

                // Catat riwayat Mutasi Stok awal
                StockMutation::firstOrCreate(
                    [
                        'warehouse_id'   => $wh->id,
                        'material_id'    => $mat->id,
                        'reference_type' => 'Initial Stock',
                    ],
                    [
                        'qty_change'         => $qty,
                        'qty_balance_after'  => $qty,
                        'created_by_user_id' => $adminUser?->id,
                        'notes'              => 'Stok awal master data (' . $wh->name . ')',
                        'created_at'         => $baseDate,
                        'updated_at'         => $baseDate,
                    ]
                );
            }
        }

        // 5. Seed Tools & Tool Inventories
        $toolGen = Tool::firstOrCreate(
            ['code' => 'TOOL-GEN-01'],
            [
                'category_id'          => $catAlatBerat->id,
                'current_warehouse_id' => $centralWarehouse?->id,
                'name'                 => 'Genset Silent 5000W',
                'brand'                => 'Honda Silent',
                'type'                 => $catAlatBerat->name,
                'stock_total'          => 5,
                'stock_available'      => 5,
                'is_active'            => true,
            ]
        );

        $toolMol = Tool::firstOrCreate(
            ['code' => 'TOOL-MOL-01'],
            [
                'category_id'          => $catAlatBerat->id,
                'current_warehouse_id' => $centralWarehouse?->id,
                'name'                 => 'Mesin Molen Beton 500L',
                'brand'                => 'Tiger Engine',
                'type'                 => $catAlatBerat->name,
                'stock_total'          => 3,
                'stock_available'      => 3,
                'is_active'            => true,
            ]
        );

        foreach ([$centralWarehouse, $projectWarehouseA] as $wh) {
            if (!$wh) continue;
            ToolInventory::firstOrCreate(
                ['warehouse_id' => $wh->id, 'tool_id' => $toolGen->id],
                [
                    'stock_total'       => 5,
                    'stock_available'   => 5,
                    'stock_borrowed'    => 0,
                    'stock_maintenance' => 0,
                    'stock_damaged'     => 0,
                ]
            );
            ToolInventory::firstOrCreate(
                ['warehouse_id' => $wh->id, 'tool_id' => $toolMol->id],
                [
                    'stock_total'       => 3,
                    'stock_available'   => 3,
                    'stock_borrowed'    => 0,
                    'stock_maintenance' => 0,
                    'stock_damaged'     => 0,
                ]
            );
        }
    }
}
