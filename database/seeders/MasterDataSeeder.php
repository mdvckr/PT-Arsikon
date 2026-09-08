<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Material;
use App\Models\Supplier;
use App\Models\Tool;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\ToolAssignment;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Units
        $unitPcs = Unit::firstOrCreate(['code' => 'PCS'], ['name' => 'Pieces / Buah', 'is_decimal' => false]);
        $unitKg = Unit::firstOrCreate(['code' => 'KG'], ['name' => 'Kilogram', 'is_decimal' => true]);
        $unitM3 = Unit::firstOrCreate(['code' => 'M3'], ['name' => 'Meter Kubik', 'is_decimal' => true]);
        $unitBatang = Unit::firstOrCreate(['code' => 'BTG'], ['name' => 'Batang', 'is_decimal' => false]);
        $unitSak = Unit::firstOrCreate(['code' => 'SAK'], ['name' => 'Sak / Bag', 'is_decimal' => false]);

        // 2. Seed Categories
        $catSemen = Category::firstOrCreate(['code' => 'CAT-MAT-01'], ['name' => 'Semen & Pengikat', 'type' => 'material', 'description' => 'Material semen dan bahan perekat']);
        $catBesi = Category::firstOrCreate(['code' => 'CAT-MAT-02'], ['name' => 'Besi & Baja', 'type' => 'material', 'description' => 'Besi beton, ulir, dan wiremesh']);
        $catAgregat = Category::firstOrCreate(['code' => 'CAT-MAT-03'], ['name' => 'Pasir & Agregat', 'type' => 'material', 'description' => 'Pasir pasang, pasir beton, kerikil']);
        $catAlatBerat = Category::firstOrCreate(['code' => 'CAT-TOOL-01'], ['name' => 'Alat Berat & Elektrik', 'type' => 'tool', 'description' => 'Genset, molen, bor listrik']);
        $catAlatTangan = Category::firstOrCreate(['code' => 'CAT-TOOL-02'], ['name' => 'Alat Tangan', 'type' => 'tool', 'description' => 'Cangkul, sekop, gerobak dorong']);

        // 3. Seed Suppliers
        $supSemen = Supplier::firstOrCreate(['code' => 'SUP-001'], ['name' => 'PT Semen Indonesia Tbk', 'phone' => '021-5551234', 'email' => 'sales@semenindonesia.com', 'address' => 'Jakarta Central']);
        $supBesi = Supplier::firstOrCreate(['code' => 'SUP-002'], ['name' => 'PT Krakatau Steel Tbk', 'phone' => '0254-392222', 'email' => 'sales@krakatausteel.com', 'address' => 'Cilegon, Banten']);

        // 4. Seed Materials
        Material::firstOrCreate(
            ['sku' => 'MAT-SEM-001'],
            [
                'category_id' => $catSemen->id,
                'unit_id' => $unitSak->id,
                'name' => 'Semen Tiga Roda 50kg',
                'min_stock_central' => 100,
                'is_active' => true,
                'description' => 'Semen Portland Komposit 50kg',
            ]
        );

        Material::firstOrCreate(
            ['sku' => 'MAT-BES-001'],
            [
                'category_id' => $catBesi->id,
                'unit_id' => $unitBatang->id,
                'name' => 'Besi Beton Ulir D13mm x 12m',
                'min_stock_central' => 200,
                'is_active' => true,
                'description' => 'Besi beton ulir standar SNI',
            ]
        );

        Material::firstOrCreate(
            ['sku' => 'MAT-PAS-001'],
            [
                'category_id' => $catAgregat->id,
                'unit_id' => $unitM3->id,
                'name' => 'Pasir Beton Cuci',
                'min_stock_central' => 50,
                'is_active' => true,
                'description' => 'Pasir beton kualitas super',
            ]
        );

        // 5. Seed Tools
        $centralWarehouse = Warehouse::where('is_central', true)->first();

        Tool::firstOrCreate(
            ['code' => 'TOOL-GEN-01'],
            [
                'category_id' => $catAlatBerat->id,
                'current_warehouse_id' => $centralWarehouse?->id,
                'serial_number' => 'SN-GENSET-2026-001',
                'name' => 'Genset Silent 5000W',
                'brand' => 'Honda Silent',
                'status' => 'available',
                'is_active' => true,
            ]
        );

        Tool::firstOrCreate(
            ['code' => 'TOOL-MOL-01'],
            [
                'category_id' => $catAlatBerat->id,
                'current_warehouse_id' => $centralWarehouse?->id,
                'serial_number' => 'SN-MOLEN-2026-002',
                'name' => 'Mesin Molen Beton 500L',
                'brand' => 'Tiger Engine',
                'status' => 'available',
                'is_active' => true,
            ]
        );
        // 6. Seed Sample Tool Assignment
        $projectUser = User::where('email', 'user.proyek@arsikon.co.id')->first();
        $projectWarehouse = Warehouse::where('is_central', false)->first();

        if ($projectUser && $projectWarehouse) {
            $tool = Tool::first();
            if ($tool) {
                ToolAssignment::firstOrCreate(
                    ['assignment_number' => 'TA-20260907-0001'],
                    [
                        'tool_id' => $tool->id,
                        'from_warehouse_id' => $projectWarehouse->id,
                        'assigned_to_user_id' => $projectUser->id,
                        'assigned_by_user_id' => $projectUser->id,
                        'assigned_at' => now(),
                        'expected_return_at' => now()->addDays(7),
                        'status' => 'active',
                        'notes' => 'Peminjaman alat untuk pekerjaan lantai 2',
                    ]
                );
            }
        }
    }
}
