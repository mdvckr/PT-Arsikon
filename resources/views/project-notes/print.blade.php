<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Catatan Material & Sewa Alat - PT. Arsikon Cipta Karya</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 12mm 10mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #0f172a;
            margin: 0;
            padding: 10px;
            background: #fff;
        }
        .header-title {
            text-align: center;
            margin-bottom: 12px;
        }
        .header-title h1 {
            margin: 0;
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-title h2 {
            margin: 2px 0 0 0;
            font-size: 12px;
            font-weight: 700;
        }
        .header-title p {
            margin: 2px 0 0 0;
            font-size: 10px;
            color: #475569;
            font-style: italic;
        }
        .section-banner {
            background-color: #D9E8F5;
            border: 1px solid #94a3b8;
            padding: 6px 12px;
            font-weight: 800;
            font-size: 11px;
            margin-top: 15px;
            margin-bottom: 4px;
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        th, td {
            border: 1px solid #94a3b8;
            padding: 4px 6px;
            font-size: 9.5px;
        }
        th {
            background-color: #9BC2E6;
            color: #0f172a;
            font-weight: 800;
            text-align: center;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .font-mono { font-family: monospace; }
        
        /* Batch Colors Persis Excel */
        .batch-green { background-color: #C6E0B4 !important; }
        .batch-yellow { background-color: #FFE699 !important; }
        .batch-peach { background-color: #F8CBAD !important; }
        .batch-blue { background-color: #B4C6E7 !important; }
        
        /* Grand Total Kuning */
        .row-total {
            background-color: #FFFF00 !important;
            font-weight: 800;
            border-top: 2px solid #000;
        }

        /* Signatures */
        .signature-table {
            width: 100%;
            border: none;
            margin-top: 25px;
            page-break-inside: avoid;
        }
        .signature-table td {
            border: none;
            text-align: center;
            vertical-align: top;
            width: 33.33%;
            padding: 4px;
        }
        .sign-space {
            height: 55px;
        }
        .sign-name {
            font-weight: bold;
            text-decoration: underline;
        }
        .sign-role {
            font-size: 9px;
            color: #475569;
        }

        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print()" style="padding: 6px 14px; background: #0f172a; color: #fff; font-weight: bold; border-radius: 4px; cursor: pointer; border: none;">
            🖨 Cetak Halaman (A4 Landscape)
        </button>
        <button onclick="window.close()" style="padding: 6px 14px; background: #e2e8f0; color: #334155; font-weight: bold; border-radius: 4px; cursor: pointer; border: 1px solid #cbd5e1;">
            Tutup
        </button>
    </div>

    <div class="header-title">
        <h1>PT. ARSIKON CIPTA KARYA</h1>
        <h2>REKAPITULASI CATATAN MATERIAL & SEWA ALAT PROYEK</h2>
        <p>Proyek: {{ $project ? $project->name : 'Semua Proyek' }} &bull; Periode: Minggu ke-{{ $currentWeekNumber }} ({{ $startDate->translatedFormat('d F Y') }} — {{ $endDate->translatedFormat('d F Y') }})</p>
    </div>

    {{-- 1. Tabel Catatan Material --}}
    @foreach($ledgers as $ledger)
        <div class="section-banner">
            {{ $ledger->title }} &mdash; {{ $ledger->supplier_name }}
        </div>
        <table>
            <thead>
                <tr>
                    <th style="width: 70px;">Tanggal</th>
                    <th>Suplier</th>
                    <th style="width: 45px;">AN</th>
                    <th style="width: 70px;">No. Surat</th>
                    <th>Nama Material / Truk</th>
                    <th style="width: 60px;">Vol. Deposit</th>
                    <th style="width: 60px;">Vol Pengiriman</th>
                    <th style="width: 35px;">Sat</th>
                    <th style="width: 75px;">Harga</th>
                    <th style="width: 85px;">Jumlah Dipakai</th>
                    <th style="width: 95px;">Sisa Deposit (Kumulatif)</th>
                    <th style="width: 65px;">Total Volume</th>
                    <th>Keterangan</th>
                    <th style="width: 65px;">Sisa Vol</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ledger->calculated_entries as $entry)
                    @php
                        $colorClass = match($entry->batch_color) {
                            'green'  => 'batch-green',
                            'yellow' => 'batch-yellow',
                            'peach', 'orange' => 'batch-peach',
                            'blue'   => 'batch-blue',
                            default  => '',
                        };
                    @endphp
                    <tr class="{{ $colorClass }}">
                        <td class="text-center">{{ $entry->delivery_date ? $entry->delivery_date->format('d/m/Y') : '-' }}</td>
                        <td>{{ $entry->supplier_name ?: $ledger->supplier_name }}</td>
                        <td class="text-center font-bold">{{ $entry->account_behalf ?: ($ledger->account_behalf ?: '-') }}</td>
                        <td class="text-center font-mono font-bold">{{ $entry->delivery_note_number }}</td>
                        <td>{{ $entry->material_specification }}</td>
                        <td class="text-center font-bold">{{ $entry->deposit_volume ? number_format($entry->deposit_volume, 0, ',', '.') : '' }}</td>
                        <td class="text-center font-bold">{{ number_format($entry->delivery_volume, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $entry->unit }}</td>
                        <td class="text-right">Rp {{ number_format($entry->unit_price, 0, ',', '.') }}</td>
                        <td class="text-right font-bold">Rp {{ number_format($entry->calculated_amount_used, 0, ',', '.') }}</td>
                        <td class="text-right font-bold">Rp {{ number_format($entry->running_total_amount, 0, ',', '.') }}</td>
                        <td class="text-right font-bold">{{ number_format($entry->running_total_volume, 2, ',', '.') }}</td>
                        <td>{{ $entry->notes }}</td>
                        <td class="text-center font-bold text-slate-400">-</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="row-total">
                    <td colspan="5" class="text-right font-bold">TOTAL REKAPITULASI PENGIRIMAN:</td>
                    <td class="text-center">{{ number_format($ledger->total_deposit_vol, 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($ledger->total_delivered_vol, 0, ',', '.') }}</td>
                    <td colspan="2"></td>
                    <td class="text-right">Rp {{ number_format($ledger->total_used_amount, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($ledger->total_used_amount, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($ledger->total_delivered_vol, 2, ',', '.') }}</td>
                    <td class="text-center font-bold">TOTAL SISA VOL</td>
                    <td class="text-center font-bold">-</td>
                </tr>
            </tfoot>
        </table>
    @endforeach

    {{-- 2. Tabel Sewa Alat --}}
    @if($rentals->isNotEmpty())
        <div class="section-banner">
            REKAPITULASI SEWA ALAT KERJA PROYEK
        </div>
        <table>
            <thead>
                <tr>
                    <th style="width: 30px;">No</th>
                    <th>Nama Alat Kerja / Armada</th>
                    <th>Vendor Penyedia</th>
                    <th>No. Bukti / Surat Sewa</th>
                    <th style="width: 75px;">Tgl Mulai</th>
                    <th style="width: 75px;">Tgl Selesai</th>
                    <th style="width: 50px;">Durasi</th>
                    <th style="width: 45px;">Satuan</th>
                    <th style="width: 80px;">Tarif / Satuan</th>
                    <th style="width: 75px;">Deposit</th>
                    <th style="width: 90px;">Total Biaya Sewa</th>
                    <th style="width: 60px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rentals as $idx => $r)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="font-bold">{{ $r->tool_name }}</td>
                        <td>{{ $r->vendor_name }}</td>
                        <td class="text-center font-mono">{{ $r->docket_number ?: '-' }}</td>
                        <td class="text-center">{{ $r->start_date ? $r->start_date->format('d/m/Y') : '-' }}</td>
                        <td class="text-center">{{ $r->end_date ? $r->end_date->format('d/m/Y') : '-' }}</td>
                        <td class="text-right">{{ (float)$r->rental_duration }}</td>
                        <td class="text-center">{{ $r->duration_unit }}</td>
                        <td class="text-right">Rp {{ number_format($r->rate_per_unit, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($r->deposit_amount, 0, ',', '.') }}</td>
                        <td class="text-right font-bold">Rp {{ number_format($r->total_rental_cost, 0, ',', '.') }}</td>
                        <td class="text-center">{{ strtoupper($r->status) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: #FEF3C7; font-weight: bold; border-top: 2px solid #000;">
                    <td colspan="10" class="text-right">TOTAL BIAYA SEWA ALAT:</td>
                    <td class="text-right">Rp {{ number_format($rentals->sum('total_rental_cost'), 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    @endif

    {{-- 3. Tanda Tangan Proyek --}}
    <table class="signature-table">
        <tr>
            <td colspan="3" style="text-align: right; padding-bottom: 8px; font-style: italic;">
                Yogyakarta, {{ $endDate->translatedFormat('d F Y') }}
            </td>
        </tr>
        <tr>
            <td>
                <strong>Pembuat,</strong>
                <div class="sign-space"></div>
                <div class="sign-name">(  ________________________  )</div>
                <div class="sign-role">Staf Logistik / Lapangan</div>
            </td>
            <td>
                <strong>Meng-crosscheck,</strong>
                <div class="sign-space"></div>
                <div class="sign-name">(  ________________________  )</div>
                <div class="sign-role">Site Manager / Pengawas Proyek</div>
            </td>
            <td>
                <strong>Mengetahui,</strong>
                <div class="sign-space"></div>
                <div class="sign-name">(  ________________________  )</div>
                <div class="sign-role">Project Manager / Direksi</div>
            </td>
        </tr>
    </table>

</body>
</html>
