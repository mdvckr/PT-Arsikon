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
        Supplier::firstOrCreate(
            ['code' => 'SUP-001'],
            [
                'name' => 'PT Semen Indonesia Distributor',
                'email' => 'sales@semenindonesia.co.id',
                'phone' => '081234567890',
                'address' => 'Kawasan Industri Pulogadung, Jakarta',
                'is_active' => true,
            ]
        );
        Supplier::firstOrCreate(
            ['code' => 'SUP-002'],
            [
                'name' => 'CV Baja Mandiri Perkasa',
                'email' => 'marketing@bajamandiri.com',
                'phone' => '081398765432',
                'address' => 'Jl. Daan Mogot KM 12, Jakarta Barat',
                'is_active' => true,
            ]
        );

        // 4. Seed Materials & Initial Inventories
        $materialsData = [
            // Semen & Pengikat -> Semen Portland (PCC)
            [
                'sku' => 'MAT-SEM-001',
                'category_id' => $catSemen->id,
                'unit_id' => $unitSak->id,
                'type' => 'Semen Portland (PCC)',
                'size' => '50kg',
                'name' => 'Semen Tiga Roda 50kg',
                'min_stock_central' => 100,
                'is_active' => true,
                'description' => 'Semen Portland Komposit 50kg standar SNI',
                'incoming_stages' => [
                    ['stage' => 'T1', 'date' => '2026-09-05', 'qty' => 300, 'status' => 'received', 'notes' => 'DO-0891/Tiga Roda'],
                    ['stage' => 'T2', 'date' => '2026-09-12', 'qty' => 250, 'status' => 'received', 'notes' => 'DO-0914/Tiga Roda'],
                    ['stage' => 'T3', 'date' => '2026-09-22', 'qty' => 500, 'status' => 'planned', 'notes' => 'Jadwal Cor Plat Lt.2'],
                ],
                'central_qty' => 500,
                'project_qty' => 50,
            ],
            [
                'sku' => 'MAT-SEM-002',
                'category_id' => $catSemen->id,
                'unit_id' => $unitSak->id,
                'type' => 'Semen Portland (PCC)',
                'size' => '50kg',
                'name' => 'Semen Gresik 50kg',
                'min_stock_central' => 80,
                'is_active' => true,
                'description' => 'Semen Portland Type I 50kg',
                'incoming_stages' => [
                    ['stage' => 'T1', 'date' => '2026-09-06', 'qty' => 200, 'status' => 'received', 'notes' => 'DO-4412/Gresik'],
                ],
                'central_qty' => 200,
                'project_qty' => 30,
            ],
            // Semen & Pengikat -> Mortar / Perekat Bata Ringan
            [
                'sku' => 'MAT-SEM-010',
                'category_id' => $catSemen->id,
                'unit_id' => $unitSak->id,
                'type' => 'Mortar / Perekat Bata Ringan',
                'size' => '40kg',
                'name' => 'Mortar MU-380 ThinBed 40kg',
                'min_stock_central' => 50,
                'is_active' => true,
                'description' => 'Semen instan perekat bata ringan (thin bed)',
                'incoming_stages' => [
                    ['stage' => 'T1', 'date' => '2026-09-08', 'qty' => 150, 'status' => 'received', 'notes' => 'DO-8831/Mapei'],
                ],
                'central_qty' => 150,
                'project_qty' => 20,
            ],

            // Besi & Baja -> Besi Beton Ulir
            [
                'sku' => 'MAT-BES-001',
                'category_id' => $catBesi->id,
                'unit_id' => $unitBatang->id,
                'type' => 'Besi Beton Ulir',
                'size' => 'D10mm x 12m',
                'name' => 'Besi Beton Ulir D10mm x 12m',
                'min_stock_central' => 150,
                'is_active' => true,
                'description' => 'Besi beton ulir standar SNI BJTS 420B',
                'incoming_stages' => [
                    ['stage' => 'T1', 'date' => '2026-09-01', 'qty' => 400, 'status' => 'received', 'notes' => 'Batch Krakatau Steel D10'],
                ],
                'central_qty' => 400,
                'project_qty' => 50,
            ],
            [
                'sku' => 'MAT-BES-002',
                'category_id' => $catBesi->id,
                'unit_id' => $unitBatang->id,
                'type' => 'Besi Beton Ulir',
                'size' => 'D13mm x 12m',
                'name' => 'Besi Beton Ulir D13mm x 12m',
                'min_stock_central' => 200,
                'is_active' => true,
                'description' => 'Besi beton ulir standar SNI',
                'incoming_stages' => [
                    ['stage' => 'T1', 'date' => '2026-09-02', 'qty' => 500, 'status' => 'received', 'notes' => 'Batch Krakatau Steel #1'],
                    ['stage' => 'T2', 'date' => '2026-09-09', 'qty' => 600, 'status' => 'received', 'notes' => 'Batch Krakatau Steel #2'],
                    ['stage' => 'T3', 'date' => '2026-09-25', 'qty' => 400, 'status' => 'planned', 'notes' => 'Rencana Kolom Utama'],
                ],
                'central_qty' => 1100,
                'project_qty' => 100,
            ],
            [
                'sku' => 'MAT-BES-003',
                'category_id' => $catBesi->id,
                'unit_id' => $unitBatang->id,
                'type' => 'Besi Beton Ulir',
                'size' => 'D16mm x 12m',
                'name' => 'Besi Beton Ulir D16mm x 12m',
                'min_stock_central' => 100,
                'is_active' => true,
                'description' => 'Besi beton ulir standar SNI BJTS 420B',
                'incoming_stages' => [
                    ['stage' => 'T1', 'date' => '2026-09-03', 'qty' => 350, 'status' => 'received', 'notes' => 'Batch Krakatau Steel D16'],
                ],
                'central_qty' => 350,
                'project_qty' => 40,
            ],

            // Besi & Baja -> Besi Hollow Galvanis
            [
                'sku' => 'MAT-BES-010',
                'category_id' => $catBesi->id,
                'unit_id' => $unitBatang->id,
                'type' => 'Besi Hollow Galvanis',
                'size' => '40x40x2.0mm x 6m',
                'name' => 'Besi Hollow Galvanis 40x40x2.0mm x 6m',
                'min_stock_central' => 50,
                'is_active' => true,
                'description' => 'Hollow galvanis anti karat untuk rangka plafon & kanopi',
                'incoming_stages' => [
                    ['stage' => 'T1', 'date' => '2026-09-07', 'qty' => 150, 'status' => 'received', 'notes' => 'Pengiriman Toko Besi Utama'],
                ],
                'central_qty' => 150,
                'project_qty' => 20,
            ],
            [
                'sku' => 'MAT-BES-011',
                'category_id' => $catBesi->id,
                'unit_id' => $unitBatang->id,
                'type' => 'Besi Hollow Galvanis',
                'size' => '40x60x2.0mm x 6m',
                'name' => 'Besi Hollow Galvanis 40x60x2.0mm x 6m',
                'min_stock_central' => 40,
                'is_active' => true,
                'description' => 'Hollow pipa kotak galvanis tebal 2.0mm',
                'incoming_stages' => [
                    ['stage' => 'T1', 'date' => '2026-09-07', 'qty' => 100, 'status' => 'received', 'notes' => 'Pengiriman Toko Besi Utama'],
                ],
                'central_qty' => 100,
                'project_qty' => 15,
            ],

            // Besi & Baja -> Wiremesh
            [
                'sku' => 'MAT-BES-020',
                'category_id' => $catBesi->id,
                'unit_id' => $unitPcs->id,
                'type' => 'Wiremesh',
                'size' => 'M8 (2.1m x 5.4m)',
                'name' => 'Wiremesh M8 (2.1m x 5.4m)',
                'min_stock_central' => 30,
                'is_active' => true,
                'description' => 'Wiremesh lembaran diameter 8mm ulir standar SNI',
                'incoming_stages' => [
                    ['stage' => 'T1', 'date' => '2026-09-05', 'qty' => 80, 'status' => 'received', 'notes' => 'DO Krakatau Wiremesh'],
                ],
                'central_qty' => 80,
                'project_qty' => 10,
            ],

            // Pasir & Agregat -> Pasir
            [
                'sku' => 'MAT-PAS-001',
                'category_id' => $catAgregat->id,
                'unit_id' => $unitM3->id,
                'type' => 'Pasir',
                'size' => 'Standar Cor',
                'name' => 'Pasir Beton Cuci',
                'min_stock_central' => 50,
                'is_active' => true,
                'description' => 'Pasir beton kualitas super bebas lumpur',
                'incoming_stages' => [
                    ['stage' => 'T1', 'date' => '2026-09-04', 'qty' => 150, 'status' => 'received', 'notes' => 'Tronton Pasir 1 & 2'],
                    ['stage' => 'T2', 'date' => '2026-09-11', 'qty' => 125, 'status' => 'received', 'notes' => 'Tronton Pasir 3'],
                ],
                'central_qty' => 275,
                'project_qty' => 25,
            ],
            [
                'sku' => 'MAT-PAS-002',
                'category_id' => $catAgregat->id,
                'unit_id' => $unitM3->id,
                'type' => 'Pasir',
                'size' => 'Standar Pasang',
                'name' => 'Pasir Pasang Halus',
                'min_stock_central' => 40,
                'is_active' => true,
                'description' => 'Pasir halus untuk pasangan bata dan plesteran',
                'incoming_stages' => [
                    ['stage' => 'T1', 'date' => '2026-09-08', 'qty' => 80, 'status' => 'received', 'notes' => 'Dump Truck Pasir Halus'],
                ],
                'central_qty' => 80,
                'project_qty' => 15,
            ],

            // Pasir & Agregat -> Batu Split
            [
                'sku' => 'MAT-PAS-010',
                'category_id' => $catAgregat->id,
                'unit_id' => $unitM3->id,
                'type' => 'Batu Split',
                'size' => '1x2 cm',
                'name' => 'Batu Split 1x2 cm Cor',
                'min_stock_central' => 50,
                'is_active' => true,
                'description' => 'Agregat kasar batu pecah ukuran 1x2 untuk struktur cor',
                'incoming_stages' => [
                    ['stage' => 'T1', 'date' => '2026-09-04', 'qty' => 120, 'status' => 'received', 'notes' => 'Dump Truck Split 1x2'],
                ],
                'central_qty' => 120,
                'project_qty' => 20,
            ],
        ];

        $centralWarehouse = Warehouse::where('is_central', true)->first();
        $projectWarehouse = Warehouse::where('is_central', false)->first();

        foreach ($materialsData as $data) {
            $centralQty = $data['central_qty'] ?? 0;
            $projectQty = $data['project_qty'] ?? 0;
            unset($data['central_qty'], $data['project_qty']);

            $mat = Material::updateOrCreate(['sku' => $data['sku']], $data);

            if ($centralWarehouse && $centralQty > 0) {
                \App\Models\Inventory::updateOrCreate(
                    ['warehouse_id' => $centralWarehouse->id, 'material_id' => $mat->id],
                    ['quantity' => $centralQty, 'min_stock' => $mat->min_stock_central ?? 0]
                );
            }

            if ($projectWarehouse && $projectQty > 0) {
                \App\Models\Inventory::updateOrCreate(
                    ['warehouse_id' => $projectWarehouse->id, 'material_id' => $mat->id],
                    ['quantity' => $projectQty, 'min_stock' => 10]
                );
            }
        }

        // 5. Seed Tools
        $centralWarehouse = Warehouse::where('is_central', true)->first();

        $toolGen = Tool::firstOrCreate(
            ['code' => 'TOOL-GEN-01'],
            [
                'category_id' => $catAlatBerat->id,
                'current_warehouse_id' => $centralWarehouse?->id,
                'name' => 'Genset Silent 5000W',
                'brand' => 'Honda Silent',
                'type' => $catAlatBerat->name,
                'stock_total' => 5,
                'stock_available' => 5,
                'is_active' => true,
            ]
        );

        $toolMol = Tool::firstOrCreate(
            ['code' => 'TOOL-MOL-01'],
            [
                'category_id' => $catAlatBerat->id,
                'current_warehouse_id' => $centralWarehouse?->id,
                'name' => 'Mesin Molen Beton 500L',
                'brand' => 'Tiger Engine',
                'type' => $catAlatBerat->name,
                'stock_total' => 3,
                'stock_available' => 3,
                'is_active' => true,
            ]
        );

        if ($centralWarehouse) {
            \App\Models\ToolInventory::firstOrCreate(
                ['warehouse_id' => $centralWarehouse->id, 'tool_id' => $toolGen->id],
                [
                    'stock_total' => 5,
                    'stock_available' => 5,
                    'stock_borrowed' => 0,
                    'stock_maintenance' => 0,
                    'stock_damaged' => 0,
                ]
            );
            \App\Models\ToolInventory::firstOrCreate(
                ['warehouse_id' => $centralWarehouse->id, 'tool_id' => $toolMol->id],
                [
                    'stock_total' => 3,
                    'stock_available' => 3,
                    'stock_borrowed' => 0,
                    'stock_maintenance' => 0,
                    'stock_damaged' => 0,
                ]
            );
        }
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
