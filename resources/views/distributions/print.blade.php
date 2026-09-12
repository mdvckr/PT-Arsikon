<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Surat Jalan — {{ $distribution->distribution_number }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: #fff;
            color: #0f172a;
            font-size: 13px;
            line-height: 1.5;
        }

        .print-sheet {
            max-width: 800px;
            margin: 0 auto;
            padding: 24px 32px;
            background: #fff;
        }

        /* ===== KOP SURAT ===== */
        .kop {
            display: flex;
            align-items: center;
            gap: 16px;
            padding-bottom: 12px;
            border-bottom: 4px double #c2410c;
        }

        .kop-logo {
            width: 74px;
            height: 74px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .kop-text {
            flex: 1;
            text-align: center;
        }

        .kop-text .company {
            font-size: 24px;
            font-weight: 800;
            color: #c2410c;
            letter-spacing: 0.02em;
        }

        .kop-text .tagline {
            font-size: 12px;
            font-weight: 600;
            color: #334155;
            margin-top: 2px;
        }

        .kop-text .address {
            font-size: 11px;
            color: #64748b;
            margin-top: 4px;
        }

        /* ===== JUDUL DOKUMEN ===== */
        .doc-title {
            text-align: center;
            margin: 20px 0 6px;
        }

        .doc-title h2 {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .doc-title .doc-no {
            font-size: 12px;
            color: #475569;
            margin-top: 2px;
        }

        /* ===== INFO BARIS ===== */
        .info-section {
            margin: 16px 0;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 3px 8px;
            vertical-align: top;
            font-size: 12.5px;
            color: #1e293b;
        }

        .info-table .label {
            width: 150px;
            color: #64748b;
            font-weight: 500;
        }

        /* ===== TABEL ITEM ===== */
        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .item-table th {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            text-align: center;
            font-size: 11px;
            font-weight: 700;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .item-table td {
            border: 1px solid #cbd5e1;
            padding: 7px 10px;
            color: #334155;
            vertical-align: top;
        }

        .item-table td.no { text-align: center; width: 30px; }
        .item-table td.center { text-align: center; }

        /* ===== TANDA TANGAN ===== */
        .sign-section {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            text-align: center;
        }

        .sign-block {
            width: 45%;
        }

        .sign-block .role {
            font-weight: 700;
            color: #334155;
            font-size: 12.5px;
        }

        .sign-block .space {
            height: 60px;
        }

        .sign-block .name {
            font-weight: 600;
            color: #0f172a;
        }

        .sign-block .empty { color: #64748b; }

        /* ===== CATATAN ===== */
        .notes-block {
            margin-top: 16px;
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 12px;
            color: #475569;
        }

        .notes-block strong { color: #334155; }

        /* ===== TOMBOL PRINT (hanya di layar) ===== */
        .print-toolbar {
            text-align: right;
            padding: 12px 0;
            max-width: 800px;
            margin: 0 auto;
        }

        .print-toolbar button {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            margin-left: 8px;
        }

        .print-toolbar button:hover { background: #1d4ed8; }
        .print-toolbar button.back {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }
        .print-toolbar button.back:hover { background: #e2e8f0; }

        /* ===== PRINT RULES ===== */
        @media print {
            @page {
                size: A4;
                margin: 12mm 14mm;
            }

            body { background: #fff; }

            .print-toolbar { display: none; }

            .print-sheet {
                max-width: 100%;
                margin: 0;
                padding: 0;
            }

            .item-table th { background: #f8fafc !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="print-toolbar">
        <button class="back" onclick="window.history.back()"><i class="fas fa-arrow-left"></i> Kembali</button>
        <button onclick="window.print()">🖨 Cetak</button>
    </div>

    <div class="print-sheet">
        {{-- KOP SURAT --}}
        <div class="kop">
            <img src="{{ asset('assets/Logo-Dashboard.png') }}" alt="PT ARSIKON CIPTA KARYA" class="kop-logo">
            <div class="kop-text">
                <div class="company">PT ARSIKON CIPTA KARYA</div>
                <div class="tagline">Warehouse Management System</div>
                <div class="address">Jl. Industri Utama No. 1, Jakarta &middot; Telp: (021) 555-1234 &middot; Email: info@arsikon.co.id</div>
            </div>
        </div>

        {{-- JUDUL SURAT JALAN --}}
        <div class="doc-title">
            <h2>SURAT JALAN PENGIRIMAN ALAT & MATERIAL</h2>
            <div class="doc-no">No. {{ $distribution->distribution_number }}</div>
        </div>

        {{-- INFO --}}
        <div class="info-section">
            <table class="info-table">
                <tr>
                    <td class="label">Gudang Asal</td>
                    <td>:&nbsp;{{ $distribution->fromWarehouse?->name ?? '-' }}</td>
                    <td class="label">Tanggal Kirim</td>
                    <td>:&nbsp;{{ $distribution->delivery_date ? \Carbon\Carbon::parse($distribution->delivery_date)->format('d/m/Y') : '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Gudang Tujuan</td>
                    <td>:&nbsp;{{ $distribution->toWarehouse?->name ?? '-' }}</td>
                    <td class="label">Supir</td>
                    <td>:&nbsp;{{ $distribution->driver_name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">No. Permintaan (Ref)</td>
                    <td>:&nbsp;{{ $distribution->materialRequest?->request_number ?? '-' }}</td>
                    <td class="label">No. Polisi</td>
                    <td>:&nbsp;{{ $distribution->vehicle_number ?? '-' }}</td>
                </tr>
            </table>
        </div>

        {{-- TABEL ITEM --}}
        <table class="item-table">
            <thead>
                <tr>
                    <th class="no">No</th>
                    <th style="text-align:left;">Nama Barang / Alat</th>
                    <th>Kode</th>
                    <th>Jumlah Kirim</th>
                    <th>Jumlah Terima</th>
                    <th>Rusak / Hilang</th>
                </tr>
            </thead>
            <tbody>
                @forelse($distribution->items as $idx => $item)
                <tr>
                    <td class="no">{{ $idx + 1 }}</td>
                    <td>
                        {{ $item->name() }}
                        @if($item->isTool() && $item->toolAssignment)
                        <div style="font-size:11px;color:#64748b;">{{ $item->toolAssignment->assignment_number }}</div>
                        @endif
                    </td>
                    <td class="center">{{ $item->detail() }}</td>
                    <td class="center">{{ number_format((float)$item->qty_shipped, 0, ',', '.') }} {{ $item->unitAbbr() }}</td>
                    <td class="center">{{ number_format((float)$item->qty_received, 0, ',', '.') }} {{ $item->unitAbbr() }}</td>
                    <td class="center">{{ number_format((float)$item->qty_damaged_or_lost, 0, ',', '.') }} {{ $item->unitAbbr() }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="center" style="color:#64748b;padding:16px;">Tidak ada item.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- CATATAN --}}
        @if($distribution->notes)
        <div class="notes-block">
            <strong>Catatan:</strong> {{ $distribution->notes }}
        </div>
        @endif

        {{-- TANDA TANGAN --}}
        <div class="sign-section">
            <div class="sign-block">
                <div class="role">Pengirim,</div>
                <div class="space"></div>
                @if($distribution->shippedBy)
                <div class="name">{{ $distribution->shippedBy->name }}</div>
                @elseif($distribution->creator)
                <div class="name">{{ $distribution->creator->name }}</div>
                @else
                <div class="name empty"> ..................... </div>
                @endif
            </div>
            <div class="sign-block">
                <div class="role">Penerima,</div>
                <div class="space"></div>
                @if($distribution->receivedBy)
                <div class="name">{{ $distribution->receivedBy->name }}</div>
                @else
                <div class="name empty"> ..................... </div>
                @endif
            </div>
        </div>

        @if($distribution->status === 'in_transit' || $distribution->status === 'draft')
        <div style="margin-top:16px;text-align:center;font-size:10px;color:#94a3b8;">
            Catatan: Surat jalan ini perlu ditandatangani dan diverifikasi oleh penerima di gudang tujuan.
        </div>
        @endif
    </div>

    <script>
        window.onload = function() {
            // Do not auto-print; let user click
        };
    </script>
</body>
</html>
