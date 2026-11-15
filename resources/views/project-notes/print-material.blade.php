<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Catatan Material Proyek - PT. Arsikon Cipta Karya</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 8mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #0f172a;
            margin: 0;
            padding: 8px;
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
            margin-top: 10px;
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
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .signature-table td {
            border: none;
            text-align: center;
            vertical-align: top;
            width: 25%;
            padding: 4px;
        }
        .sign-space {
            height: 50px;
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
        <h2>REKAPITULASI CATATAN MATERIAL PROYEK</h2>
        <p>
            Proyek: {{ $project ? $project->name : 'Semua Proyek' }}
            @if(isset($startDate) && isset($endDate))
                &bull; Periode: {{ $startDate->translatedFormat('d F Y') }} — {{ $endDate->translatedFormat('d F Y') }}
            @endif
        </p>
    </div>

    {{-- Tabel Catatan Material --}}
    @forelse($ledgers as $ledger)
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
                @forelse($ledger->calculated_entries ?? [] as $entry)
                    @php
                        $colorClass = match($entry->batch_color ?? '') {
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
                        <td class="text-right">
                            @if((float)($entry->unit_price ?? 0) > 0)
                                Rp {{ number_format($entry->unit_price, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-right font-bold">
                            @if((float)($entry->calculated_amount_used ?? 0) > 0)
                                Rp {{ number_format($entry->calculated_amount_used, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-right font-bold">
                            @if((float)($entry->running_total_amount ?? 0) > 0)
                                Rp {{ number_format($entry->running_total_amount, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-right font-bold">{{ number_format($entry->running_total_volume, 2, ',', '.') }}</td>
                        <td>{{ $entry->notes }}</td>
                        <td class="text-center font-bold text-slate-400">-</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="14" class="text-center" style="padding: 10px; color: #64748b;">Belum ada catatan pengiriman surat jalan.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="row-total">
                    <td colspan="5" class="text-right font-bold">TOTAL REKAPITULASI PENGIRIMAN:</td>
                    <td class="text-center">{{ number_format($ledger->total_deposit_vol, 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($ledger->total_delivered_vol, 0, ',', '.') }}</td>
                    <td colspan="2"></td>
                    <td class="text-right">
                        @if((float)$ledger->total_used_amount > 0)
                            Rp {{ number_format($ledger->total_used_amount, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right">
                        @if((float)$ledger->total_used_amount > 0)
                            Rp {{ number_format($ledger->total_used_amount, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($ledger->total_delivered_vol, 2, ',', '.') }}</td>
                    <td class="text-center font-bold">TOTAL SISA VOL</td>
                    <td class="text-center font-bold">-</td>
                </tr>
            </tfoot>
        </table>

        {{-- Tanda Tangan Proyek Khusus Catatan Material --}}
        <table class="signature-table">
            <tr>
                <td colspan="4" style="text-align: right; padding-bottom: 6px; font-style: italic;">
                    Yogyakarta, {{ ($ledger->end_date ?? now())->translatedFormat('d F Y') }}
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Pembuat (Logistik Proyek),</strong>
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
                    <strong>Pelaksana Lapangan,</strong>
                    <div class="sign-space"></div>
                    <div class="sign-name">(  ________________________  )</div>
                    <div class="sign-role">Pelaksana Lapangan / Mandor</div>
                </td>
                <td>
                    <strong>Mengetahui Suplier,</strong>
                    <div class="sign-space"></div>
                    <div class="sign-name">(  ________________________  )</div>
                    <div class="sign-role">{{ $ledger->supplier_name }}</div>
                </td>
            </tr>
        </table>

        @if(!$loop->last)
            <div style="page-break-after: always;"></div>
        @endif
    @empty
        <div style="text-align:center; padding: 40px; color: #64748b;">
            Tidak ada Buku Catatan Material yang ditemukan.
        </div>
    @endforelse

</body>
</html>

