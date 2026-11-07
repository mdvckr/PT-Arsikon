<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Surat Jalan Proyek — {{ $distribution->distribution_number }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.4;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* ===== TOOLBAR CETAK (HANYA DI LAYAR) ===== */
        .print-toolbar {
            width: 100%;
            max-width: 820px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #ffffff;
            padding: 10px 18px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border: 1px solid #cbd5e1;
            color: #0f172a;
        }

        .toolbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .toolbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-toolbar {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            border: none;
        }

        .btn-toolbar.back {
            background: #f8fafc;
            color: #475569;
            border: 1.5px solid #cbd5e1;
        }
        .btn-toolbar.back:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .btn-toolbar.print {
            background: #f5a623;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(245, 166, 35, 0.4);
            font-weight: 700;
        }
        .btn-toolbar.print:hover {
            background: #e09015;
        }

        /* ===== SLIP SURAT JALAN CONTAINER ===== */
        .slip-container {
            width: 100%;
            max-width: 820px;
            background: #ffffff;
            border: 1.5px solid #f5a623;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            position: relative;
        }

        /* HEADER SESUAI KOP PT ARSIKON */
        .slip-header {
            display: flex;
            align-items: stretch;
            border-bottom: 1.5px solid #f5a623;
        }

        .header-logo-col {
            width: 130px;
            min-width: 130px;
            border-right: 1.5px solid #f5a623;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 8px 10px;
        }

        .header-logo-col img {
            max-width: 65px;
            max-height: 65px;
            object-fit: contain;
        }

        .company-name-under-logo {
            font-size: 8px;
            text-align: center;
            font-weight: 800;
            color: #1e293b;
            margin-top: 3px;
            line-height: 1.25;
            letter-spacing: 0.3px;
        }

        .header-info-col {
            flex: 1;
            border-right: 1.5px solid #f5a623;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 10px 14px;
            font-size: 10.5px;
            line-height: 1.55;
            color: #334155;
        }

        .header-info-col .company-main {
            font-weight: 900;
            font-size: 14px;
            color: #0f172a;
            letter-spacing: 0.3px;
            margin-bottom: 2px;
        }

        .header-title-col {
            width: 210px;
            min-width: 210px;
            background: #f5a623;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 10px 12px;
            text-align: center;
        }

        .doc-title {
            font-size: 21px;
            font-weight: 900;
            color: #ffffff;
            text-align: center;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            line-height: 1.15;
        }

        .doc-subtitle {
            font-size: 10px;
            font-weight: 800;
            color: #ffffff;
            background: rgba(0,0,0,0.18);
            padding: 2px 8px;
            border-radius: 3px;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* FIELDS / METADATA SECTION */
        .slip-fields {
            border-bottom: 1.5px solid #f5a623;
            padding: 8px 12px;
            display: flex;
            align-items: flex-start;
            gap: 16px;
            background: #fffdf9;
        }

        .slip-fields-left {
            flex: 1;
        }

        .field-row {
            display: flex;
            align-items: baseline;
            margin-bottom: 6px;
            font-size: 11px;
        }
        .field-row:last-child {
            margin-bottom: 0;
        }

        .field-label {
            font-weight: 700;
            min-width: 62px;
            text-transform: uppercase;
            font-size: 10.5px;
            color: #0f172a;
        }

        .field-colon {
            margin: 0 6px;
            font-weight: 700;
            color: #0f172a;
        }

        .field-val {
            border-bottom: 1px solid #f5a623;
            flex: 1;
            padding-bottom: 1px;
            min-width: 140px;
            color: #0f172a;
            font-weight: 600;
            padding-left: 4px;
        }

        .slip-fields-right {
            width: 290px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .meta-row {
            display: flex;
            align-items: center;
            font-size: 11px;
        }

        .meta-label {
            font-weight: 700;
            font-size: 10.5px;
            text-transform: uppercase;
            min-width: 100px;
            color: #0f172a;
        }

        .meta-colon {
            margin: 0 4px;
            font-weight: 700;
            color: #0f172a;
        }

        .meta-val-box {
            border: 1px solid #64748b;
            padding: 2px 10px;
            font-size: 12.5px;
            font-weight: 900;
            color: #000;
            min-width: 110px;
            text-align: center;
            letter-spacing: 0.5px;
            background: #ffffff;
        }

        .meta-val-line {
            border-bottom: 1px dotted #f5a623;
            flex: 1;
            padding-bottom: 1px;
            color: #0f172a;
            font-weight: 600;
            padding-left: 4px;
        }

        /* TABLE SECTION */
        .slip-table {
            width: 100%;
            border-collapse: collapse;
        }

        .slip-table thead th {
            background-color: #f5a623;
            color: #ffffff;
            font-size: 10.5px;
            font-weight: 800;
            text-transform: uppercase;
            padding: 6px 6px;
            border: 1px solid #f5a623;
            text-align: center;
            letter-spacing: 0.5px;
        }

        .slip-table tbody td {
            border: 1px solid #fcd34d;
            padding: 5px 7px;
            font-size: 11px;
            color: #0f172a;
            height: 23px;
            vertical-align: middle;
        }

        .col-no {
            width: 38px;
            text-align: center;
            font-weight: 600;
        }

        .col-nama {
            width: auto;
            font-weight: 600;
        }

        .col-satuan {
            width: 80px;
            text-align: center;
            font-weight: 600;
        }

        .col-qty {
            width: 110px;
            text-align: center;
            font-weight: 700;
        }

        .col-ket {
            width: 210px;
            font-size: 10.5px;
        }

        .empty-row td {
            height: 23px;
            border-color: #fde68a;
        }

        /* FOOTER SECTION */
        .slip-footer {
            border-top: 1.5px solid #f5a623;
            padding: 8px 12px 10px 12px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            background: #fffdf9;
        }

        .notes-block {
            flex: 1;
            font-size: 10.5px;
            color: #334155;
            line-height: 1.5;
        }

        .notes-title {
            font-weight: 800;
            margin-bottom: 2px;
            font-size: 11px;
            color: #b45309;
            text-transform: uppercase;
        }

        .notes-list {
            margin-left: 0;
            list-style-type: none;
            font-weight: 600;
            color: #475569;
        }

        /* SIGNATURES */
        .signatures-wrapper {
            display: flex;
            gap: 8px;
            align-items: stretch;
        }

        .penerima-box-wrap {
            width: 100px;
            display: flex;
            flex-direction: column;
            border: 1.5px solid #f5a623;
        }

        .penerima-header {
            background-color: #f5a623;
            color: #ffffff;
            font-size: 9.5px;
            font-weight: 800;
            text-align: center;
            padding: 3px 2px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .penerima-body {
            height: 56px;
            background: #ffffff;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding: 4px;
            font-size: 9.5px;
            color: #0f172a;
            font-weight: 600;
            text-align: center;
        }

        .three-sig-wrap {
            display: flex;
            border: 1.5px solid #f5a623;
        }

        .sig-col {
            width: 92px;
            display: flex;
            flex-direction: column;
            border-right: 1.5px solid #f5a623;
        }
        .sig-col:last-child {
            border-right: none;
        }

        .sig-col-header {
            background-color: #f5a623;
            color: #ffffff;
            font-size: 9.5px;
            font-weight: 800;
            text-align: center;
            padding: 3px 2px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .sig-col-body {
            height: 56px;
            background: #ffffff;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding: 4px;
            font-size: 9.5px;
            color: #0f172a;
            font-weight: 600;
            text-align: center;
        }

        /* ===== PRINT MEDIA STYLES ===== */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .print-toolbar {
                display: none !important;
            }

            .slip-container {
                border: 1.5px solid #f5a623 !important;
                box-shadow: none !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            @page {
                size: A5 landscape;
                margin: 6mm;
            }
        }
    </style>
</head>
<body>

    {{-- TOOLBAR CETAK --}}
    <div class="print-toolbar">
        <div class="toolbar-left">
            <a href="{{ route('distributions.show', $distribution) }}" class="btn-toolbar back">
                <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Detail
            </a>
            <span style="font-size:12px;color:#64748b;">
                Surat Jalan: <strong>{{ $distribution->distribution_number }}</strong>
            </span>
        </div>
        <div class="toolbar-right">
            <button type="button" class="btn-toolbar print" onclick="window.print()">
                <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Dokumen / Simpan PDF
            </button>
        </div>
    </div>

    {{-- SLIP SURAT JALAN --}}
    <div class="slip-container">

        @php
            $slipNo = $distribution->surat_jalan ?: ($distribution->distribution_number ?: '0075');
            $poNumber = $distribution->materialRequest?->purchaseOrders?->first()?->po_number 
                ?? $distribution->materialRequest?->request_number 
                ?? '-';
            $tglKirim = $distribution->delivery_date 
                ? \Carbon\Carbon::parse($distribution->delivery_date)->format('d / m / Y') 
                : date('d / m / Y');
            
            // Nama Tujuan & Proyek
            $recipientName = $distribution->toWarehouse?->name ?? 'Proyek';
            $projectName = $distribution->toWarehouse?->project?->name 
                ?? ($distribution->materialRequest?->toWarehouse?->project?->name ?? ($distribution->toWarehouse?->name ?? '-'));
            
            // Items
            $items = $distribution->items;
            $itemCount = $items->count();
            $emptyRowsCount = max(0, 8 - $itemCount);
        @endphp

        <!-- HEADER KOP RESMI PT ARSIKON CIPTA KARYA -->
        <div class="slip-header">
            <div class="header-logo-col">
                <img src="{{ asset('assets/Logo-Dashboard.png') }}" alt="Logo PT Arsikon">
                <div class="company-name-under-logo">PT. ARSIKON<br>CIPTA KARYA</div>
            </div>
            <div class="header-info-col">
                <div class="company-main">PT. ARSIKON CIPTA KARYA</div>
                <div>Alamat : Jl. Pasir Luhur, No.1,</div>
                <div>Taraman Sinduharjo, Ngaglik, Sleman</div>
            </div>
            <div class="header-title-col">
                <div class="doc-title">SURAT JALAN</div>
                <div class="doc-subtitle">PROYEK</div>
            </div>
        </div>

        <!-- FIELDS SECTION -->
        <div class="slip-fields">
            <div class="slip-fields-left">
                <div class="field-row">
                    <span class="field-label">KEPADA</span>
                    <span class="field-colon">:</span>
                    <span class="field-val">{{ $recipientName }}</span>
                </div>
                <div class="field-row">
                    <span class="field-label">PROYEK</span>
                    <span class="field-colon">:</span>
                    <span class="field-val">{{ $projectName }}</span>
                </div>
            </div>

            <div class="slip-fields-right">
                <div class="meta-row">
                    <span class="meta-label">TANGGAL</span>
                    <span class="meta-colon">:</span>
                    <span class="meta-val-line">{{ $tglKirim }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">NO. SURAT JALAN</span>
                    <span class="meta-colon">:</span>
                    <span class="meta-val-box">{{ $distribution->distribution_number }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">NO. PO</span>
                    <span class="meta-colon">:</span>
                    <span class="meta-val-line">{{ $poNumber }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">DIKIRIM DENGAN</span>
                    <span class="meta-colon">:</span>
                    <span class="meta-val-line">DT</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">NO. POLISI</span>
                    <span class="meta-colon">:</span>
                    <span class="meta-val-line">{{ $distribution->vehicle_number ?: '-' }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">NAMA SOPIR</span>
                    <span class="meta-colon">:</span>
                    <span class="meta-val-line">{{ $distribution->driver_name ?: '-' }}</span>
                </div>
            </div>
        </div>

        <!-- ITEMS TABLE -->
        <table class="slip-table">
            <thead>
                <tr>
                    <th class="col-no">NO</th>
                    <th class="col-nama">JENIS BARANG</th>
                    <th class="col-satuan">SATUAN</th>
                    <th class="col-qty">JUMLAH BARANG</th>
                    <th class="col-ket">KETERANGAN</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $idx => $item)
                    @php
                        $qty = (float)$item->qty_shipped > 0 ? (float)$item->qty_shipped : (float)$item->qty_received;
                        $ket = $item->notes;
                        if (!$ket && $item->isTool() && $item->toolAssignment) {
                            $ket = 'Pinjaman: ' . $item->toolAssignment->assignment_number;
                        }
                    @endphp
                    <tr>
                        <td class="col-no">{{ $idx + 1 }}</td>
                        <td class="col-nama">{{ $item->name() }}</td>
                        <td class="col-satuan">{{ $item->unitAbbr() }}</td>
                        <td class="col-qty">{{ $qty > 0 ? number_format($qty, 0, ',', '.') : '-' }}</td>
                        <td class="col-ket">{{ $ket ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="col-no">1</td>
                        <td class="col-nama">-</td>
                        <td class="col-satuan">-</td>
                        <td class="col-qty">-</td>
                        <td class="col-ket">-</td>
                    </tr>
                @endforelse

                {{-- Pad empty rows to match physical receipt slip height --}}
                @for($i = 0; $i < $emptyRowsCount; $i++)
                    <tr class="empty-row">
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endfor
            </tbody>
        </table>

        <!-- FOOTER & SIGNATURES -->
        <div class="slip-footer">
            <div class="notes-block">
                <div class="notes-title">Keterangan:</div>
                <div class="notes-list">
                    1. Lembar 1 (Asli) Untuk Pengirim<br>
                    2. Lembar Ke-2 Untuk Penerima
                </div>
            </div>

            <div class="signatures-wrapper">
                <!-- PENERIMA -->
                <div class="penerima-box-wrap">
                    <div class="penerima-header">PENERIMA</div>
                    <div class="penerima-body">
                        ( {{ $distribution->receivedBy?->name ?? '....................' }} )
                    </div>
                </div>

                <!-- 3 COLUMNS: SOPIR, SITE ENGINEER, PENGIRIM -->
                <div class="three-sig-wrap">
                    <div class="sig-col">
                        <div class="sig-col-header">SOPIR</div>
                        <div class="sig-col-body">
                            ( {{ $distribution->driver_name ?: '....................' }} )
                        </div>
                    </div>
                    <div class="sig-col">
                        <div class="sig-col-header">SITE ENGINEER</div>
                        <div class="sig-col-body">
                            ( .................... )
                        </div>
                    </div>
                    <div class="sig-col">
                        <div class="sig-col-header">PENGIRIM</div>
                        <div class="sig-col-body">
                            ( {{ $distribution->shippedBy?->name ?? ($distribution->creator?->name ?? '....................') }} )
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
