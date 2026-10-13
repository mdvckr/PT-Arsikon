<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order Lapangan — {{ $materialRequest->request_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            background: #fff;
            color: #000;
            padding: 18px 24px;
        }

        /* ── HEADER ─────────────────────────────── */
        .doc-header {
            display: flex;
            align-items: stretch;
            border: 2px solid #1a56a4;
            margin-bottom: 0;
        }

        .header-logo-box {
            width: 110px;
            min-width: 110px;
            border-right: 2px solid #1a56a4;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 8px;
            background: #fff;
        }

        .header-logo-box img {
            max-width: 80px;
            max-height: 70px;
            object-fit: contain;
        }

        .header-logo-box .company-name {
            font-size: 7.5px;
            text-align: center;
            color: #1a3a6b;
            font-weight: bold;
            margin-top: 4px;
            line-height: 1.3;
        }

        .header-main {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .doc-title-bar {
            background: #1a56a4;
            color: #fff;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 1.5px;
            padding: 7px 10px;
            text-transform: uppercase;
        }

        .header-fields {
            display: flex;
            padding: 8px 12px;
            gap: 10px;
            flex: 1;
            align-items: center;
        }

        .hf-left { flex: 1; }
        .hf-right { min-width: 230px; }

        .hf-row {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
            font-size: 11.5px;
        }
        .hf-row:last-child { margin-bottom: 0; }

        .hf-label {
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            min-width: 68px;
            font-size: 11px;
        }

        .hf-colon { margin: 0 6px; font-weight: bold; }

        .hf-val {
            border-bottom: 1px solid #555;
            min-width: 180px;
            padding-bottom: 1px;
        }

        .hf-row-r {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
            justify-content: flex-end;
        }
        .hf-row-r:last-child { margin-bottom: 0; }

        .hf-val-r {
            border-bottom: 1px solid #555;
            min-width: 120px;
            padding-left: 6px;
            padding-bottom: 1px;
        }

        .doc-number {
            font-size: 15px;
            font-weight: bold;
            color: #1a3a6b;
        }

        /* ── TABLE ──────────────────────────────── */
        .item-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #1a56a4;
            border-top: none;
        }

        .item-table th {
            background: #1a56a4;
            color: #fff;
            text-align: center;
            font-size: 10.5px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 5px 6px;
            border: 1px solid #1a56a4;
            vertical-align: middle;
        }

        .item-table .th-sub {
            background: #3a78d4;
            font-size: 9.5px;
        }

        .item-table td {
            border: 1px solid #a0b4d4;
            padding: 5px 6px;
            vertical-align: middle;
            font-size: 11px;
        }

        .item-table tr:nth-child(even) td { background: #f0f5fb; }
        .no-col  { text-align: center; width: 28px; }
        .qty-col { text-align: center; width: 60px; font-weight: bold; }
        .sat-col { text-align: center; width: 42px; }
        .tgl-col { text-align: center; width: 72px; }
        .empty-row td { height: 22px; }

        /* ── SIGNATURE ──────────────────────────── */
        .sign-section {
            display: flex;
            border: 2px solid #1a56a4;
            border-top: none;
        }

        .sign-box {
            flex: 1;
            padding: 10px 12px 6px;
            text-align: center;
            border-right: 1px solid #1a56a4;
        }
        .sign-box:last-child { border-right: none; }

        .sign-label-top { font-size: 11px; font-weight: bold; margin-bottom: 2px; }
        .sign-space { height: 58px; }
        .sign-label-bottom {
            border-top: 1px solid #777;
            padding-top: 3px;
            font-size: 10.5px;
            color: #333;
        }

        /* ── NOTES ──────────────────────────────── */
        .notes-section { margin-top: 10px; font-size: 10.5px; line-height: 1.55; }

        /* ── PRINT CONTROLS ─────────────────────── */
        .print-controls {
            position: fixed;
            top: 14px;
            right: 18px;
            display: flex;
            gap: 8px;
            z-index: 9999;
        }
        .btn-print {
            background: #1a56a4;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 7px 16px;
            font-size: 12px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-close {
            background: #64748b;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 7px 14px;
            font-size: 12px;
            cursor: pointer;
        }
        @media print {
            .print-controls { display: none; }
            body { padding: 10px 14px; }
            @page { size: A4 landscape; margin: 10mm; }
        }
    </style>
</head>
<body>

    <div class="print-controls">
        <button class="btn-print" onclick="window.print()">&#128438; Cetak / Simpan PDF</button>
        <button class="btn-close" onclick="window.close()">&#10005; Tutup</button>
    </div>

    @php
        $mr       = $materialRequest;
        $items    = $mr->items;
        $docNo    = substr($mr->request_number, strrpos($mr->request_number, '-') + 1);
        $proyek   = $mr->toWarehouse?->project?->name ?? $mr->toWarehouse?->name ?? '-';
        $kepada        = $mr->fromWarehouse ? $mr->fromWarehouse->name : 'Pengadaan / Beli ke Vendor';
        $tglOrder      = $mr->created_at ? $mr->created_at->format('d/m/y') : '-';
        $tglDibutuhkan = $mr->needed_at ? \Carbon\Carbon::parse($mr->needed_at)->format('d/m/y') : '-';
        $minRows  = 12;
        $emptyRows = max(0, $minRows - $items->count());
    @endphp

    {{-- HEADER --}}
    <div class="doc-header">
        <div class="header-logo-box">
            <img src="{{ asset('assets/Logo-Dashboard.png') }}" alt="Logo">
            <div class="company-name">PT. ARSIKON CIPTA KARYA</div>
        </div>
        <div class="header-main">
            <div class="doc-title-bar">Purchase Order Lapangan</div>
            <div class="header-fields">
                <div class="hf-left">
                    <div class="hf-row">
                        <span class="hf-label">Kepada</span>
                        <span class="hf-colon">:</span>
                        <span class="hf-val">{{ $kepada }}</span>
                    </div>
                    <div class="hf-row">
                        <span class="hf-label">Proyek</span>
                        <span class="hf-colon">:</span>
                        <span class="hf-val">{{ $proyek }}</span>
                    </div>
                    <div class="hf-row">
                        <span class="hf-label">Pemohon</span>
                        <span class="hf-colon">:</span>
                        <span class="hf-val">{{ $mr->requestedBy?->name ?? '-' }} ({{ $mr->requested_as ?: 'Admin Proyek' }})</span>
                    </div>
                </div>
                <div class="hf-right">
                    <div class="hf-row-r">
                        <span class="hf-label" style="min-width:110px;text-align:right;">No PO</span>
                        <span class="hf-colon">:</span>
                        <span class="hf-val-r doc-number">{{ $docNo }}</span>
                    </div>
                    <div class="hf-row-r">
                        <span class="hf-label" style="min-width:110px;text-align:right;">Tanggal Order</span>
                        <span class="hf-colon">:</span>
                        <span class="hf-val-r">{{ $tglOrder }}</span>
                    </div>
                    <div class="hf-row-r">
                        <span class="hf-label" style="min-width:110px;text-align:right;">Dibutuhkan Tgl</span>
                        <span class="hf-colon">:</span>
                        <span class="hf-val-r">{{ $tglDibutuhkan }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ITEM TABLE --}}
    <table class="item-table">
        <thead>
            <tr>
                <th class="no-col" rowspan="2">NO</th>
                <th colspan="2" style="border-bottom:1px solid #4a80c4;">DESKRIPSI BAHAN</th>
                <th class="qty-col" rowspan="2">QUANTITY</th>
                <th class="sat-col" rowspan="2">SAT</th>
                <th colspan="2" style="border-bottom:1px solid #4a80c4;">PENGGUNAAN</th>
                <th rowspan="2">KETERANGAN</th>
            </tr>
            <tr>
                <th class="th-sub" style="min-width:160px;">NAMA BAHAN</th>
                <th class="th-sub" style="min-width:110px;">SPESIFIKASI</th>
                <th class="th-sub tgl-col">TANGGAL</th>
                <th class="th-sub" style="min-width:100px;">ALOKASI</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $idx => $item)
            <tr>
                <td class="no-col">{{ $idx + 1 }}</td>
                <td>
                    {{ $item->displayName() }}
                    @if($item->isCustom())
                        <br><small style="color:#b45309;">(Manual)</small>
                    @endif
                </td>
                <td style="font-size:10.5px;color:#334155;">{{ $item->specification ?? '' }}</td>
                <td class="qty-col">{{ format_quantity($item->qty_requested) }}</td>
                <td class="sat-col">{{ $item->displayUnit() }}</td>
                <td class="tgl-col" style="font-size:10.5px;">
                    @if($item->usage_date)
                        {{ \Carbon\Carbon::parse($item->usage_date)->format('d/m/y') }}
                    @endif
                </td>
                <td style="font-size:10.5px;">{{ $item->usage_location ?? '' }}</td>
                <td style="font-size:10.5px;">
                    @if($item->notes && $item->notes !== 'Manual - tidak ada di gudang sumber')
                        {{ $item->notes }}
                    @endif
                </td>
            </tr>
            @endforeach
            @for($e = 0; $e < $emptyRows; $e++)
            <tr class="empty-row">
                <td class="no-col"></td><td></td><td></td>
                <td class="qty-col"></td><td class="sat-col"></td>
                <td class="tgl-col"></td><td></td><td></td>
            </tr>
            @endfor
        </tbody>
    </table>

    {{-- SIGNATURE --}}
    <div class="sign-section">
        <div class="sign-box">
            <div class="sign-label-top">Disetujui Oleh,</div>
            <div class="sign-space">
                @if($mr->approvedBy)
                    <div style="font-size:10px;margin-top:4px;color:#555;">{{ $mr->approvedBy->name }}</div>
                @endif
            </div>
            <div class="sign-label-bottom">(Direktur)</div>
        </div>
        <div class="sign-box">
            <div class="sign-label-top">Disiapkan Oleh,</div>
            <div class="sign-space"></div>
            <div class="sign-label-bottom">(Purchasing)</div>
        </div>
        <div class="sign-box">
            <div class="sign-label-top">Dipesan Oleh,</div>
            <div class="sign-space">
                @if($mr->requestedBy)
                    <div style="font-size:10px;margin-top:4px;color:#555;">{{ $mr->requestedBy->name }}</div>
                @endif
            </div>
            <div class="sign-label-bottom">({{ $mr->requested_as ?: 'Site Manager' }})</div>
        </div>
    </div>

    {{-- NOTES --}}
    <div class="notes-section">
        <strong>Catatan :</strong><br>
        - Jika order dilakukan oleh Site Engineer tetap harus diketahui oleh Site Manager (paraf)<br>
        - Orderan minimal 2 hari sebelum bahan digunakan kecuali orderan khusus yang membutuhkan waktu lama<br>
        - Lapangan Tidak dibenarkan order langsung kepada pihak ketiga
        @if($mr->notes)
            <br>- <em>{{ $mr->notes }}</em>
        @endif
    </div>

</body>
</html>
