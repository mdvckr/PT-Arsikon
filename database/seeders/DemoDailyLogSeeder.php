<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Material;
use App\Models\MaterialUsage;
use App\Models\Tool;
use App\Models\ToolAssignment;
use App\Models\ToolInventory;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\MaterialUsageService;
use Illuminate\Database\Seeder;

class DemoDailyLogSeeder extends Seeder
{
    public function run(): void
    {
        $pusat = Warehouse::where('is_central', true)->first();
        $proyek = Warehouse::where('is_central', false)->first();
        if (!$proyek) {
            return;
        }

        $adminProyek = User::where('email', 'admin.proyek1@arsikon.co.id')->first() ?? User::first();

        // 1. Ensure Tool Inventories in Project Warehouse
        $tools = Tool::all();
        foreach ($tools as $t) {
            ToolInventory::firstOrCreate(
                ['tool_id' => $t->id, 'warehouse_id' => $proyek->id],
                [
                    'stock_total' => 2,
                    'stock_available' => 2,
                    'stock_borrowed' => 0,
                    'stock_maintenance' => 0,
                    'stock_damaged' => 0,
                ]
            );
        }

        // 2. Add Tool Assignment with Mandor Supardi
        $firstTool = $tools->first();
        if ($firstTool) {
            $loan = \App\Models\ToolLoan::firstOrCreate(
                ['loan_number' => 'TL-DEMO-001'],
                [
                    'from_warehouse_id'   => $proyek->id,
                    'borrower_name'       => 'Mandor Supardi (Subkon Struktur)',
                    'borrower_phone'      => '081234567890',
                    'location_name'       => 'Zona Area Cor Plat Lt. 2 Gedung FK',
                    'assigned_by_user_id' => $adminProyek?->id,
                    'assigned_at'         => now()->startOfDay()->addHours(8),
                    'expected_return_at'  => now()->startOfDay()->addDays(2)->addHours(17),
                    'status'              => 'active',
                    'notes'               => 'Peminjaman alat untuk persiapan pengecoran kolom & balok.',
                ]
            );

            ToolAssignment::updateOrCreate(
                ['assignment_number' => 'TA-DEMO-001'],
                [
                    'tool_loan_id'      => $loan->id,
                    'tool_id'           => $firstTool->id,
                    'quantity'          => 1,
                    'from_warehouse_id' => $proyek->id,
                    'borrower_name'     => 'Mandor Supardi (Subkon Struktur)',
                    'borrower_phone'    => '081234567890',
                    'location_name'     => 'Zona Area Cor Plat Lt. 2 Gedung FK',
                    'assigned_by_user_id' => $adminProyek?->id,
                    'assigned_at'       => now()->startOfDay()->addHours(8),
                    'expected_return_at' => now()->startOfDay()->addDays(2)->addHours(17),
                    'status'            => 'active',
                    'notes'             => 'Peminjaman alat untuk persiapan pengecoran kolom & balok.',
                ]
            );

            // Sync Tool Inventory in Project Warehouse for this active loan
            $toolInv = \App\Models\ToolInventory::where('warehouse_id', $proyek->id)
                ->where('tool_id', $firstTool->id)
                ->first();
            if ($toolInv && $toolInv->stock_borrowed == 0 && $toolInv->stock_available > 0) {
                $toolInv->decrement('stock_available', 1);
                $toolInv->increment('stock_borrowed', 1);
            }
        }

        // 3. Ensure sufficient material inventory in Project Warehouse
        $semen = Material::where('sku', 'MAT-SEM-001')->first();
        $mortar = Material::where('sku', 'MAT-SEM-010')->first();
        $besi = Material::where('sku', 'MAT-BES-001')->first();

        if ($semen) {
            $inv = Inventory::firstOrCreate(
                ['warehouse_id' => $proyek->id, 'material_id' => $semen->id],
                ['quantity' => 50, 'min_stock' => 10]
            );
            if ($inv->quantity < 20) {
                $inv->update(['quantity' => 50]);
            }
        }
        if ($mortar) {
            $inv = Inventory::firstOrCreate(
                ['warehouse_id' => $proyek->id, 'material_id' => $mortar->id],
                ['quantity' => 30, 'min_stock' => 5]
            );
            if ($inv->quantity < 10) {
                $inv->update(['quantity' => 30]);
            }
        }
        if ($besi) {
            $inv = Inventory::firstOrCreate(
                ['warehouse_id' => $proyek->id, 'material_id' => $besi->id],
                ['quantity' => 60, 'min_stock' => 15]
            );
            if ($inv->quantity < 20) {
                $inv->update(['quantity' => 60]);
            }
        }

        // 4. Create Material Usages for Today if empty
        if (MaterialUsage::count() === 0 && $semen && $mortar && $besi && $adminProyek) {
            $service = app(MaterialUsageService::class);

            $service->createUsage($proyek, $adminProyek, [
                'recipient_name' => 'Mandor Joko S. (Subkon Struktur)',
                'recipient_phone' => '081399887766',
                'location_name' => 'Lantai 2 - Area Balok & Plat Barat',
                'notes' => 'Pekerjaan pengecoran balok struktural dan pembesian plat. Diserahkan langsung ke mandor dalam kondisi baik.',
                'items' => [
                    ['material_id' => $semen->id, 'quantity' => 12, 'notes' => 'Campuran cor 1:2:3'],
                    ['material_id' => $besi->id, 'quantity' => 8, 'notes' => 'Pembesian sengkang balok'],
                ],
            ]);

            $service->createUsage($proyek, $adminProyek, [
                'recipient_name' => 'Mandor Sugeng (Bata)',
                'recipient_phone' => '087711223344',
                'location_name' => 'Lantai 1 - Dinding Ruang Rapat & Toilet',
                'notes' => 'Pemasangan dinding bata ringan dan plesteran dasar.',
                'items' => [
                    ['material_id' => $mortar->id, 'quantity' => 5, 'notes' => 'Perekat bata ringan tahap pagi'],
                ],
            ]);
        }
    }
}
