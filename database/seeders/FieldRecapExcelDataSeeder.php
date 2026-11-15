<?php

namespace Database\Seeders;

use App\Models\MaterialLedger;
use App\Models\Project;
use App\Models\ProjectMaterialTransfer;
use App\Models\ProjectToolRental;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FieldRecapExcelDataSeeder extends Seeder
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

        // =====================================================================
        // 1. REKAP PEMBELIAN MATERIAL PASIR & BATU (SUPRI)
        // =====================================================================
        $supplierSupri = Supplier::firstOrCreate(
            ['name' => 'Supri (Pasir & Batu)'],
            ['phone' => '0812-3456-7890']
        );

        $ledgerSupri = MaterialLedger::updateOrCreate(
            [
                'project_id' => $project->id,
                'title'      => 'CATATAN PEMBELIAN PASIR & BATU (SUPRI)',
            ],
            [
                'project_name'          => $project->name,
                'supplier_id'           => $supplierSupri->id,
                'supplier_name'         => 'Supri',
                'account_behalf'        => 'Proyek UGM',
                'default_unit'          => 'Rit',
                'default_unit_price'    => 650000,
                'start_date'            => '2026-10-11',
                'end_date'              => '2026-11-06',
                'week_number'           => 41,
                'year'                  => 2026,
                'status'                => 'active',
                'notes'                 => 'Material urugan pasir dan batu pondasi',
                'created_by'            => $creator->id,
            ]
        );

        $ledgerSupri->entries()->delete();

        $pasirItems = [
            ['tgl' => '2026-10-11', 'sj' => 'SJ-SPR-01', 'mat' => 'Pasir', 'qty' => 1.0],
            ['tgl' => '2026-10-11', 'sj' => 'SJ-SPR-02', 'mat' => 'Pasir', 'qty' => 1.0],
            ['tgl' => '2026-10-20', 'sj' => 'SJ-SPR-03', 'mat' => 'Pasir', 'qty' => 1.0],
            ['tgl' => '2026-10-20', 'sj' => 'SJ-SPR-04', 'mat' => 'Batu & 20 Liter', 'qty' => 1.0],
            ['tgl' => '2026-10-22', 'sj' => 'SJ-SPR-05', 'mat' => 'Pasir', 'qty' => 1.0],
            ['tgl' => '2026-10-22', 'sj' => 'SJ-SPR-06', 'mat' => 'Batu', 'qty' => 1.0],
            ['tgl' => '2026-10-24', 'sj' => 'SJ-SPR-07', 'mat' => 'Pasir', 'qty' => 1.0],
            ['tgl' => '2026-10-24', 'sj' => 'SJ-SPR-08', 'mat' => 'Batu', 'qty' => 1.0],
            ['tgl' => '2026-10-24', 'sj' => 'SJ-SPR-09', 'mat' => 'Batu', 'qty' => 1.0],
            ['tgl' => '2026-11-03', 'sj' => 'SJ-SPR-10', 'mat' => 'Pasir', 'qty' => 1.0],
            ['tgl' => '2026-11-03', 'sj' => 'SJ-SPR-11', 'mat' => 'Batu', 'qty' => 1.0],
            ['tgl' => '2026-11-03', 'sj' => 'SJ-SPR-12', 'mat' => 'Batu', 'qty' => 1.0],
            ['tgl' => '2026-11-03', 'sj' => 'SJ-SPR-13', 'mat' => 'Pasir', 'qty' => 1.0],
            ['tgl' => '2026-11-04', 'sj' => 'SJ-SPR-14', 'mat' => 'Pasir', 'qty' => 1.0],
            ['tgl' => '2026-11-04', 'sj' => 'SJ-SPR-15', 'mat' => 'Batu', 'qty' => 1.0],
            ['tgl' => '2026-11-04', 'sj' => 'SJ-SPR-16', 'mat' => 'Batu', 'qty' => 1.0],
            ['tgl' => '2026-11-04', 'sj' => 'SJ-SPR-17', 'mat' => 'Batu', 'qty' => 1.0],
            ['tgl' => '2026-11-05', 'sj' => 'SJ-SPR-18', 'mat' => 'Batu', 'qty' => 1.0],
            ['tgl' => '2026-11-05', 'sj' => 'SJ-SPR-19', 'mat' => 'Batu', 'qty' => 1.0],
            ['tgl' => '2026-11-05', 'sj' => 'SJ-SPR-20', 'mat' => 'Batu', 'qty' => 1.0],
            ['tgl' => '2026-11-05', 'sj' => 'SJ-SPR-21', 'mat' => 'Pasir', 'qty' => 1.0],
            ['tgl' => '2026-11-06', 'sj' => 'SJ-SPR-22', 'mat' => 'Pasir', 'qty' => 1.0],
            ['tgl' => '2026-11-06', 'sj' => 'SJ-SPR-23', 'mat' => 'Batu', 'qty' => 1.0],
            ['tgl' => '2026-11-06', 'sj' => 'SJ-SPR-24', 'mat' => 'Batu', 'qty' => 1.0],
            ['tgl' => '2026-11-06', 'sj' => 'SJ-SPR-25', 'mat' => 'Batu', 'qty' => 1.0],
            ['tgl' => '2026-11-06', 'sj' => 'SJ-SPR-26', 'mat' => 'Pasir', 'qty' => 1.0],
        ];

        $sortOrder = 1;
        $totalDeliv = 0;
        foreach ($pasirItems as $item) {
            $ledgerSupri->entries()->create([
                'delivery_date'          => $item['tgl'],
                'supplier_name'          => 'Supri',
                'account_behalf'         => 'Proyek UGM',
                'delivery_note_number'   => $item['sj'],
                'material_specification' => $item['mat'],
                'deposit_volume'         => null,
                'delivery_volume'        => $item['qty'],
                'unit'                   => 'Rit',
                'unit_price'             => 650000,
                'amount_used'            => 650000,
                'batch_color'            => 'green',
                'notes'                  => '',
                'sort_order'             => $sortOrder++,
                'created_by'             => $creator->id,
            ]);
            $totalDeliv += $item['qty'];
        }

        $ledgerSupri->update([
            'total_deposit_vol'     => 0,
            'total_delivered_vol'   => $totalDeliv,
            'remaining_deposit_vol' => -$totalDeliv,
            'total_used_amount'     => $totalDeliv * 650000,
        ]);

        // =====================================================================
        // 2. REKAP TIBO URIP (SEWA ALAT KERJA DENGAN TIMESHEET HARIAN)
        // =====================================================================
        
        // 2a. EXCA 78 08 (60 Jam)
        $rentalExca = ProjectToolRental::updateOrCreate(
            ['project_id' => $project->id, 'tool_name' => 'Excavator EXCA 78 08'],
            [
                'project_name'      => $project->name,
                'vendor_name'       => 'Tibo Urip Mulyo',
                'docket_number'     => 'DOCK-TU-01',
                'start_date'        => '2026-11-07',
                'end_date'          => '2026-11-12',
                'rental_duration'   => 60.0,
                'duration_unit'     => 'Jam',
                'rate_per_unit'     => 175000,
                'deposit_amount'    => 0,
                'total_rental_cost' => 60.0 * 175000,
                'status'            => 'active',
                'notes'             => 'Pengecoran & melayani boredpile',
                'created_by'        => $creator->id,
            ]
        );
        $rentalExca->logs()->delete();
        $excaLogs = [
            ['date' => '2026-11-07', 'dur' => 7.0, 'act' => 'Pengecoran', 'spec' => ''],
            ['date' => '2026-11-08', 'dur' => 7.0, 'act' => 'Pengecoran', 'spec' => ''],
            ['date' => '2026-11-09', 'dur' => 9.0, 'act' => 'Layani boredpile', 'spec' => ''],
            ['date' => '2026-11-10', 'dur' => 10.0, 'act' => 'Layani boredpile', 'spec' => ''],
            ['date' => '2026-11-11', 'dur' => 13.0, 'act' => 'Layani boredpile', 'spec' => ''],
            ['date' => '2026-11-12', 'dur' => 11.0, 'act' => 'Layani boredpile', 'spec' => ''],
            ['date' => '2026-11-12', 'dur' => 3.0, 'act' => 'Bongkar besi', 'spec' => ''],
        ];
        foreach ($excaLogs as $log) {
            $rentalExca->logs()->create([
                'usage_date'     => $log['date'],
                'usage_duration' => $log['dur'],
                'duration_unit'  => 'Jam',
                'activity_type'  => $log['act'],
                'equipment_spec' => $log['spec'],
                'created_by'     => $creator->id,
            ]);
        }

        // 2b. MC - Mini / Mobile Crane (15 Jam)
        $rentalMc = ProjectToolRental::updateOrCreate(
            ['project_id' => $project->id, 'tool_name' => 'Mini Crane MC'],
            [
                'project_name'      => $project->name,
                'vendor_name'       => 'Tibo Urip Mulyo',
                'docket_number'     => 'DOCK-TU-02',
                'start_date'        => '2026-10-15',
                'end_date'          => '2026-10-23',
                'rental_duration'   => 15.0,
                'duration_unit'     => 'Jam',
                'rate_per_unit'     => 225000,
                'deposit_amount'    => 0,
                'total_rental_cost' => 15.0 * 225000,
                'status'            => 'active',
                'notes'             => 'Bongkar box beton & setting tes beban',
                'created_by'        => $creator->id,
            ]
        );
        $rentalMc->logs()->delete();
        $mcLogs = [
            ['date' => '2026-10-15', 'dur' => 8.0, 'act' => 'Bongkar bok beton', 'spec' => ''],
            ['date' => '2026-10-23', 'dur' => 7.0, 'act' => 'Seting tes beban', 'spec' => ''],
        ];
        foreach ($mcLogs as $log) {
            $rentalMc->logs()->create([
                'usage_date'     => $log['date'],
                'usage_duration' => $log['dur'],
                'duration_unit'  => 'Jam',
                'activity_type'  => $log['act'],
                'equipment_spec' => $log['spec'],
                'created_by'     => $creator->id,
            ]);
        }

        // 2c. YCM6 90 - Excavator Galian (44 Jam)
        $rentalYcm = ProjectToolRental::updateOrCreate(
            ['project_id' => $project->id, 'tool_name' => 'Excavator YCM6 90'],
            [
                'project_name'      => $project->name,
                'vendor_name'       => 'Tibo Urip Mulyo',
                'docket_number'     => 'DOCK-TU-03',
                'start_date'        => '2026-10-18',
                'end_date'          => '2026-10-22',
                'rental_duration'   => 44.0,
                'duration_unit'     => 'Jam',
                'rate_per_unit'     => 175000,
                'deposit_amount'    => 0,
                'total_rental_cost' => 44.0 * 175000,
                'status'            => 'active',
                'notes'             => 'Pekerjaan galian pondasi',
                'created_by'        => $creator->id,
            ]
        );
        $rentalYcm->logs()->delete();
        $ycmLogs = [
            ['date' => '2026-10-18', 'dur' => 8.0, 'act' => 'Galian', 'spec' => ''],
            ['date' => '2026-10-19', 'dur' => 7.0, 'act' => 'Galian', 'spec' => ''],
            ['date' => '2026-10-20', 'dur' => 7.0, 'act' => 'Galian', 'spec' => ''],
            ['date' => '2026-10-21', 'dur' => 8.0, 'act' => 'Galian', 'spec' => ''],
            ['date' => '2026-10-21', 'dur' => 7.0, 'act' => 'Galian', 'spec' => ''],
            ['date' => '2026-10-22', 'dur' => 7.0, 'act' => 'Galian', 'spec' => ''],
        ];
        foreach ($ycmLogs as $log) {
            $rentalYcm->logs()->create([
                'usage_date'     => $log['date'],
                'usage_duration' => $log['dur'],
                'duration_unit'  => 'Jam',
                'activity_type'  => $log['act'],
                'equipment_spec' => $log['spec'],
                'created_by'     => $creator->id,
            ]);
        }

        // 2d. TRUCK - Truk Tronton Langsir (6 Rit)
        $rentalTruck = ProjectToolRental::updateOrCreate(
            ['project_id' => $project->id, 'tool_name' => 'Truck Tronton Langsir'],
            [
                'project_name'      => $project->name,
                'vendor_name'       => 'Tibo Urip Mulyo',
                'docket_number'     => 'DOCK-TU-04',
                'start_date'        => '2026-10-15',
                'end_date'          => '2026-10-15',
                'rental_duration'   => 6.0,
                'duration_unit'     => 'Rit',
                'rate_per_unit'     => 350000,
                'deposit_amount'    => 0,
                'total_rental_cost' => 6.0 * 350000,
                'status'            => 'active',
                'notes'             => 'Langsir alat beban geoteknik',
                'created_by'        => $creator->id,
            ]
        );
        $rentalTruck->logs()->delete();
        $truckLogs = [
            ['date' => '2026-10-15', 'dur' => 2.0, 'act' => 'Langsir alat beban geo', 'spec' => 'Truck bak tronton'],
            ['date' => '2026-10-15', 'dur' => 2.0, 'act' => 'Langsir', 'spec' => 'Truck bak tronton'],
            ['date' => '2026-10-15', 'dur' => 2.0, 'act' => 'Langsir', 'spec' => 'Truck bak tronton'],
        ];
        foreach ($truckLogs as $log) {
            $rentalTruck->logs()->create([
                'usage_date'     => $log['date'],
                'usage_duration' => $log['dur'],
                'duration_unit'  => 'Rit',
                'activity_type'  => $log['act'],
                'equipment_spec' => $log['spec'],
                'created_by'     => $creator->id,
            ]);
        }

        // =====================================================================
        // 3. REKAP PEMINDAHAN BARANG (SURAT JALAN FAPERTA)
        // =====================================================================
        $transferFaperta = ProjectMaterialTransfer::updateOrCreate(
            [
                'project_id' => $project->id,
                'title'      => 'SURAT JALAN FAPERTA',
            ],
            [
                'project_name'          => $project->name,
                'origin_location'       => 'Proyek FAPERTA UGM',
                'destination_location'  => 'Proyek Gedung A (Teknik UGM)',
                'transfer_date'         => '2026-10-15',
                'docket_number'         => 'SJ-FAP-2026-10',
                'driver_or_transporter' => 'Tronton & Pick-up Proyek',
                'status'                => 'received',
                'notes'                 => 'Mobilisasi blok beton beban uji dan baja profil WF',
                'created_by'            => $creator->id,
            ]
        );
        $transferFaperta->items()->delete();

        $fapertaItems = [
            ['tgl' => '2026-10-15', 'nama' => 'Blok beton 1,2x1x0,6', 'unit' => 'Buah', 'qty' => 15],
            ['tgl' => '2026-10-15', 'nama' => 'Blok beton 1,2x1x0,6', 'unit' => 'Buah', 'qty' => 15],
            ['tgl' => '2026-10-15', 'nama' => 'Blok beton 1,2x1x0,6', 'unit' => 'Buah', 'qty' => 15],
            ['tgl' => '2026-10-15', 'nama' => 'Blok beton 1,2x1x0,6', 'unit' => 'Buah', 'qty' => 15],
            ['tgl' => '2026-10-15', 'nama' => 'Blok beton 1,2x1x0,6', 'unit' => 'Buah', 'qty' => 15],
            ['tgl' => '2026-10-15', 'nama' => 'Blok beton 1,2x1x0,6', 'unit' => 'Buah', 'qty' => 15],
            ['tgl' => '2026-10-17', 'nama' => 'Blok beton 1,2x1x0,6', 'unit' => 'Buah', 'qty' => 8],
            ['tgl' => '2026-10-17', 'nama' => 'WF 600 7m', 'unit' => 'Batang', 'qty' => 1],
            ['tgl' => '2026-10-17', 'nama' => 'WF 600 8m', 'unit' => 'Batang', 'qty' => 2],
            ['tgl' => '2026-10-17', 'nama' => 'Box peralatan', 'unit' => 'Buah', 'qty' => 1],
            ['tgl' => '2026-10-17', 'nama' => 'Besi patok', 'unit' => 'Batang', 'qty' => 6],
            ['tgl' => '2026-10-17', 'nama' => 'Tangga', 'unit' => 'Buah', 'qty' => 1],
            ['tgl' => '2026-10-17', 'nama' => 'Referensi', 'unit' => 'Batang', 'qty' => 2],
            ['tgl' => '2026-10-17', 'nama' => 'Blok beton 1,2x1x0,6', 'unit' => 'Buah', 'qty' => 11],
            ['tgl' => '2026-10-17', 'nama' => 'WF 600 12m', 'unit' => 'Batang', 'qty' => 5],
            ['tgl' => '2026-10-17', 'nama' => 'Jack HYD kapasitas 300T', 'unit' => 'Unit', 'qty' => 1],
            ['tgl' => '2026-10-17', 'nama' => 'Blok beton 1,2x1x0,6', 'unit' => 'Buah', 'qty' => 11],
            ['tgl' => '2026-10-17', 'nama' => 'WF 600 12m', 'unit' => 'Batang', 'qty' => 5],
            ['tgl' => '2026-10-22', 'nama' => 'Plat besi 2cm', 'unit' => 'Lembar', 'qty' => 3],
            ['tgl' => '2026-10-22', 'nama' => 'Plat besi 2cm', 'unit' => 'Lembar', 'qty' => 4],
        ];

        $iOrder = 1;
        foreach ($fapertaItems as $item) {
            $transferFaperta->items()->create([
                'transfer_date' => $item['tgl'],
                'item_name'     => $item['nama'],
                'unit'          => $item['unit'],
                'quantity'      => $item['qty'],
                'sort_order'    => $iOrder++,
                'created_by'    => $creator->id,
            ]);
        }
    }
}
