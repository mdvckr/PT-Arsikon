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

        // 2. Seed Categories (Material & Tool)
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
        $catAlatBerat = Category::firstOrCreate(
            ['code' => 'CAT-TOOL-01'],
            ['name' => 'Alat Berat & Elektrik', 'type' => 'tool', 'description' => 'Genset, molen, bor listrik']
        );
        $catAlatTangan = Category::firstOrCreate(
            ['code' => 'CAT-TOOL-02'],
            ['name' => 'Alat Tangan', 'type' => 'tool', 'description' => 'Cangkul, sekop, gerobak dorong']
        );

        // 3. Seed Suppliers
        Supplier::firstOrCreate(
            ['code' => 'SUP-TCL'],
            [
                'name' => 'Toko Cat Lancar',
                'email' => 'sales@catlancar.com',
                'phone' => '081234567001',
                'address' => 'Jl. Magelang No. 12, Yogyakarta',
                'is_active' => true,
            ]
        );
        Supplier::firstOrCreate(
            ['code' => 'SUP-JR'],
            [
                'name' => 'Jaya Raya',
                'email' => 'contact@jayaraya.com',
                'phone' => '081234567002',
                'address' => 'Jl. Industri No. 45, Jakarta',
                'is_active' => true,
            ]
        );
        Supplier::firstOrCreate(
            ['code' => 'SUP-IJ'],
            [
                'name' => 'Indo Jaya',
                'email' => 'info@indojaya.com',
                'phone' => '081234567003',
                'address' => 'Jl. Listrik Mandiri No. 8, Tangerang',
                'is_active' => true,
            ]
        );

        // 4. Seed Master Materials & Initial Central Warehouse Stock (Local / Dev only)
        if (!app()->environment('testing')) {
            $centralWh = Warehouse::where('is_central', true)->first();
            $supplierCat = Supplier::where('code', 'SUP-TCL')->first();
            $supplierBesi = Supplier::where('code', 'SUP-JR')->first();
            $supplierUmum = Supplier::where('code', 'SUP-IJ')->first();

            $materialsData = [
            [
                'sku' => 'MAT-SEM-001',
                'name' => 'Semen Portland 50kg',
                'category_id' => $catStruktur->id,
                'unit_id' => $unitSak->id,
                'supplier_id' => $supplierUmum?->id,
                'brand' => 'Tiga Roda',
                'size' => '50 kg',
                'type' => 'PCC',
                'min_stock_central' => 100,
                'is_active' => true,
                'stock' => 500,
            ],
            [
                'sku' => 'MAT-BSI-010',
                'name' => 'Besi Beton Ulir D10',
                'category_id' => $catStruktur->id,
                'unit_id' => $unitBtg->id,
                'supplier_id' => $supplierBesi?->id,
                'brand' => 'Krakatau Steel',
                'size' => '12 meter',
                'type' => 'Ulir D10',
                'min_stock_central' => 50,
                'is_active' => true,
                'stock' => 300,
            ],
            [
                'sku' => 'MAT-BSI-013',
                'name' => 'Besi Beton Ulir D13',
                'category_id' => $catStruktur->id,
                'unit_id' => $unitBtg->id,
                'supplier_id' => $supplierBesi?->id,
                'brand' => 'Krakatau Steel',
                'size' => '12 meter',
                'type' => 'Ulir D13',
                'min_stock_central' => 50,
                'is_active' => true,
                'stock' => 250,
            ],
            [
                'sku' => 'MAT-PSR-001',
                'name' => 'Pasir Pasang Cor',
                'category_id' => $catStruktur->id,
                'unit_id' => $unitM3->id,
                'supplier_id' => $supplierUmum?->id,
                'brand' => 'Merapi',
                'size' => 'M3',
                'type' => 'Pasir Cor',
                'min_stock_central' => 10,
                'is_active' => true,
                'stock' => 45,
            ],
            [
                'sku' => 'MAT-SPL-001',
                'name' => 'Batu Split 2/3',
                'category_id' => $catStruktur->id,
                'unit_id' => $unitM3->id,
                'supplier_id' => $supplierUmum?->id,
                'brand' => 'Lokal',
                'size' => '2/3 cm',
                'type' => 'Agregat Kasar',
                'min_stock_central' => 10,
                'is_active' => true,
                'stock' => 40,
            ],
            [
                'sku' => 'MAT-CAT-001',
                'name' => 'Cat Dinding Eksterior White 25kg',
                'category_id' => $catArsitek->id,
                'unit_id' => $unitPcs->id,
                'supplier_id' => $supplierCat?->id,
                'brand' => 'Dulux Weathershield',
                'size' => '25 kg (Pail)',
                'type' => 'Eksterior',
                'min_stock_central' => 10,
                'is_active' => true,
                'stock' => 35,
            ],
            [
                'sku' => 'MAT-PIP-001',
                'name' => 'Pipa PVC AW 3 inch',
                'category_id' => $catMep->id,
                'unit_id' => $unitBtg->id,
                'supplier_id' => $supplierUmum?->id,
                'brand' => 'Rucika',
                'size' => '4 meter',
                'type' => 'AW',
                'min_stock_central' => 20,
                'is_active' => true,
                'stock' => 80,
            ],
        ];

        foreach ($materialsData as $mData) {
            $initialStock = $mData['stock'];
            unset($mData['stock']);
            $material = Material::firstOrCreate(['sku' => $mData['sku']], $mData);

            if ($centralWh) {
                Inventory::firstOrCreate(
                    [
                        'warehouse_id' => $centralWh->id,
                        'material_id'  => $material->id,
                    ],
                    [
                        'quantity'      => $initialStock,
                        'qty_allocated' => 0,
                    ]
                );
            }
        }
    }
}
}
