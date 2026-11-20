<?php

namespace Database\Seeders;

use App\Models\MaterialLedger;
use App\Models\MaterialLedgerEntry;
use App\Models\Project;
use App\Models\ProjectToolRental;
use App\Models\Supplier;
use App\Models\User;
use App\Services\MaterialLedgerService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReadymixDynamixWeeklySeeder extends Seeder
{
    public function run(): void
    {
        $creator = User::first() ?? User::factory()->create([
            'name'     => 'Staf Pengadaan Proyek',
            'email'    => 'stafproyek@arsikon.com',
            'password' => bcrypt('password'),
        ]);

        $project = Project::firstOrCreate(
            ['name' => 'Fak. Teknik UGM'],
            [
                'code'        => 'PRJ-UGM-01',
                'location'    => 'Sleman, D.I. Yogyakarta',
                'status'      => 'active',
                'start_date'  => '2026-01-01',
            ]
        );

        // Pastikan Gudang Proyek FK Teknik terhubung ke Proyek UGM dan Admin Pengadaan Proyek
        $warehouse = \App\Models\Warehouse::where('name', 'like', '%FK Teknik%')->orWhere('project_id', $project->id)->first();
        if ($warehouse) {
            $warehouse->update(['project_id' => $project->id]);
        } else {
            $warehouse = \App\Models\Warehouse::create([
                'code'       => 'W-UGM-01',
                'name'       => 'Gudang Proyek FK Teknik',
                'type'       => 'project',
                'project_id' => $project->id,
                'is_central' => 0,
                'is_active'  => 1,
            ]);
        }

        $poProyekUser = User::where('email', 'po.proyek@arsikon.co.id')->first();
        if ($poProyekUser && !$poProyekUser->warehouses()->where('warehouses.id', $warehouse->id)->exists()) {
            $poProyekUser->warehouses()->attach($warehouse->id, ['role_in_warehouse' => 'member']);
        }

        $supplier = Supplier::firstOrCreate(
            ['name' => 'PT. Solusi Bangun Beton (Dynamix)'],
            [
                'phone'   => '0274-555123',
                'email'   => 'sales@dynamix.co.id',
                'address' => 'Jl. Magelang KM 7, Sleman, Yogyakarta',
            ]
        );

        // 1. Header Catatan Material
        $ledger = MaterialLedger::updateOrCreate(
            [
                'project_id' => $project->id,
                'title'      => 'CATATAN MATERIAL COR READYMIX FAK.TEKNIK UGM',
            ],
            [
                'project_name'          => $project->name,
                'supplier_id'           => $supplier->id,
                'supplier_name'         => $supplier->name,
                'account_behalf'        => 'PBAG',
                'default_unit'          => 'm3',
                'default_unit_price'    => 894438,
                'week_number'           => 38,
                'year'                  => 2026,
                'start_date'            => '2026-09-22',
                'end_date'              => '2026-09-28',
                'total_deposit_vol'     => 37,
                'total_delivered_vol'   => 37,
                'remaining_deposit_vol' => 0,
                'total_used_amount'     => 33094206,
                'status'                => 'active',
                'notes'                 => 'Pengecoran Balok & Plat Lantai 2',
                'created_by'            => $creator->id,
            ]
        );

        // Hapus entri lama jika seeder dijalankan ulang
        $ledger->entries()->delete();

        // 2. Ke-7 Baris Pengiriman Truk (T1 - T7) Sesuai Gambar material.jpeg
        $entriesData = [
            // Batch 1 (Hijau)
            [
                'delivery_date'          => '2026-09-22',
                'delivery_note_number'   => '2290102',
                'material_specification' => 'K-300/FC25, 18+/-2, G25, T1',
                'deposit_volume'         => null,
                'delivery_volume'        => 5.0,
                'unit'                   => 'm3',
                'unit_price'             => 894438,
                'amount_used'            => 4472190,
                'batch_color'            => 'green',
                'notes'                  => '',
                'sort_order'             => 1,
            ],
            [
                'delivery_date'          => '2026-09-22',
                'delivery_note_number'   => '2290103',
                'material_specification' => 'K-300/FC25, 18+/-2, G25, T2',
                'deposit_volume'         => 11.0,
                'delivery_volume'        => 6.0,
                'unit'                   => 'm3',
                'unit_price'             => 894438,
                'amount_used'            => 5366628,
                'batch_color'            => 'green',
                'notes'                  => '',
                'sort_order'             => 2,
            ],
            // Batch 2 (Kuning)
            [
                'delivery_date'          => '2026-09-23',
                'delivery_note_number'   => '2290106',
                'material_specification' => 'K-300/FC25, 18+/-2, G25, T3',
                'deposit_volume'         => null,
                'delivery_volume'        => 5.0,
                'unit'                   => 'm3',
                'unit_price'             => 894438,
                'amount_used'            => 4472190,
                'batch_color'            => 'yellow',
                'notes'                  => '',
                'sort_order'             => 3,
            ],
            [
                'delivery_date'          => '2026-09-23',
                'delivery_note_number'   => '2290109',
                'material_specification' => 'K-300/FC25, 18+/-2, G25, T4',
                'deposit_volume'         => 10.0,
                'delivery_volume'        => 5.0,
                'unit'                   => 'm3',
                'unit_price'             => 894438,
                'amount_used'            => 4472190,
                'batch_color'            => 'yellow',
                'notes'                  => '',
                'sort_order'             => 4,
            ],
            // Batch 3 (Peach / Oranye Muda)
            [
                'delivery_date'          => '2026-09-25',
                'delivery_note_number'   => '2290116',
                'material_specification' => 'K-300/FC25, 18+/-2, G25, T5',
                'deposit_volume'         => null,
                'delivery_volume'        => 7.0,
                'unit'                   => 'm3',
                'unit_price'             => 894438,
                'amount_used'            => 6261066,
                'batch_color'            => 'peach',
                'notes'                  => '',
                'sort_order'             => 5,
            ],
            [
                'delivery_date'          => '2026-09-25',
                'delivery_note_number'   => '2290118',
                'material_specification' => 'K-300/FC25, 18+/-2, G25, T6',
                'deposit_volume'         => 9.0,
                'delivery_volume'        => 2.0,
                'unit'                   => 'm3',
                'unit_price'             => 894438,
                'amount_used'            => 1788876,
                'batch_color'            => 'peach',
                'notes'                  => '',
                'sort_order'             => 6,
            ],
            // Batch 4 (Biru Muda)
            [
                'delivery_date'          => '2026-09-26',
                'delivery_note_number'   => '2290130',
                'material_specification' => 'K-300/FC25, 18+/-2, G25, T7',
                'deposit_volume'         => 7.0,
                'delivery_volume'        => 7.0,
                'unit'                   => 'm3',
                'unit_price'             => 894438,
                'amount_used'            => 6261066,
                'batch_color'            => 'blue',
                'notes'                  => '',
                'sort_order'             => 7,
            ],
        ];

        foreach ($entriesData as $data) {
            $data['created_by'] = $creator->id;
            $ledger->entries()->create($data);
        }

        // Sinkronisasi ringkasan
        (new MaterialLedgerService())->syncLedgerSummary($ledger);

        // 3. Sewa Alat Proyek (Concrete Pump & Vibrator)
        ProjectToolRental::updateOrCreate(
            [
                'project_id'    => $project->id,
                'tool_name'     => 'Concrete Pump Standar (Pompa Beton)',
                'start_date'    => '2026-09-22',
            ],
            [
                'project_name'      => $project->name,
                'vendor_name'       => 'PT. Solusi Bangun Beton / Subkon Pompa',
                'docket_number'     => 'CP-UGM-20260922',
                'week_number'       => 38,
                'year'              => 2026,
                'end_date'          => '2026-09-26',
                'rental_duration'   => 1,
                'duration_unit'     => 'Shift',
                'rate_per_unit'     => 3500000,
                'deposit_amount'    => 0,
                'total_rental_cost' => 3500000,
                'status'            => 'active',
                'notes'             => '1 Shift (Cor T1–T4)',
                'created_by'        => $creator->id,
            ]
        );

        ProjectToolRental::updateOrCreate(
            [
                'project_id'    => $project->id,
                'tool_name'     => 'Concrete Vibrator (2 Unit)',
                'start_date'    => '2026-09-22',
            ],
            [
                'project_name'      => $project->name,
                'vendor_name'       => 'Persewaan Alat Teknik Mulia',
                'docket_number'     => 'VIB-UGM-0922',
                'week_number'       => 38,
                'year'              => 2026,
                'end_date'          => '2026-09-25',
                'rental_duration'   => 4,
                'duration_unit'     => 'Hari',
                'rate_per_unit'     => 200000,
                'deposit_amount'    => 0,
                'total_rental_cost' => 800000,
                'status'            => 'returned',
                'notes'             => '4 Hari @ Rp 200.000',
                'created_by'        => $creator->id,
            ]
        );
    }
}
