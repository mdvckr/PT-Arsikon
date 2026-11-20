<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PENGAJUAN KAS LAPANGAN - {{ $balanceRequest->request_number ?? ('PKL-' . $balanceRequest->id) }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 10mm;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #0f172a;
            padding: 16px 20px;
            background: #fff;
        }
        .print-controls {
            position: fixed;
            top: 12px;
            right: 18px;
            display: flex;
            gap: 8px;
            background: #fff;
            padding: 6px 12px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border: 1px solid #cbd5e1;
            z-index: 9999;
        }
        .btn-print {
            background: #0f172a;
            color: #fff;
            border: none;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: bold;
            cursor: pointer;
        }
        .btn-close {
            background: #64748b;
            color: #fff;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
        }
        .doc-wrapper {
            border: 1.5px solid #0f172a;
            max-width: 820px;
            margin: 0 auto;
            background: #fff;
        }
        .doc-header {
            display: flex;
            align-items: stretch;
            border-bottom: 1.5px solid #0f172a;
        }
        .header-logo-col {
            width: 130px;
            border-right: 1.5px solid #0f172a;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 10px 8px;
        }
        .header-logo-col img {
            max-width: 70px;
            max-height: 70px;
            object-fit: contain;
        }
        .company-name-under-logo {
            font-size: 8px;
            font-weight: bold;
            text-align: center;
            margin-top: 4px;
            text-transform: uppercase;
        }
        .header-info-col {
            flex: 1;
            border-right: 1.5px solid #0f172a;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 10px 14px;
        }
        .company-main {
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 0.2px;
        }
        .company-sub {
            font-size: 9.5px;
            color: #475569;
            margin-top: 2px;
        }
        .header-title-col {
            width: 250px;
            background: #f8fafc;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 10px;
            text-align: center;
        }
        .doc-title {
            font-size: 14px;
            font-weight: 900;
            text-transform: uppercase;
            line-height: 1.2;
            color: #0f172a;
        }
        .doc-no {
            font-size: 10.5px;
            font-weight: 700;
            font-family: monospace;
            margin-top: 4px;
            color: #1e3a8a;
        }
        .meta-section {
            border-bottom: 1.5px solid #0f172a;
            padding: 10px 14px;
            display: flex;
            justify-content: space-between;
            gap: 20px;
            background: #fafafa;
        }
        .meta-col {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .meta-row {
            display: flex;
            align-items: baseline;
            font-size: 10.5px;
        }
        .meta-label {
            font-weight: bold;
            width: 110px;
            color: #475569;
            text-transform: uppercase;
            font-size: 9.5px;
        }
        .meta-colon { margin: 0 4px; }
        .meta-val {
            flex: 1;
            font-weight: 600;
            color: #0f172a;
        }
        table.item-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.item-table th, table.item-table td {
            border: 1px solid #0f172a;
            padding: 5px 8px;
            font-size: 10px;
        }
        table.item-table th {
            background-color: #f1f5f9;
            font-weight: 800;
            text-align: center;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .font-extrabold { font-weight: 800; }
        .font-mono { font-family: monospace; }

        .purpose-box {
            border-top: 1.5px solid #0f172a;
            padding: 8px 14px;
            background: #fff;
            font-size: 10.5px;
        }
        .purpose-label {
            font-weight: 800;
            text-transform: uppercase;
            font-size: 9.5px;
            color: #475569;
            margin-bottom: 2px;
        }

        .signatures {
            border-top: 1.5px solid #0f172a;
            display: flex;
            page-break-inside: avoid;
        }
        .sign-box {
            flex: 1;
            border-right: 1px solid #0f172a;
            padding: 10px 12px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 125px;
        }
        .sign-box:last-child {
            border-right: none;
        }
        .sign-header {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            color: #0f172a;
        }
        .sign-role {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            margin-top: 1px;
        }
        .sign-space {
            height: 55px;
        }
        .sign-name {
            font-weight: bold;
            font-size: 10px;
            border-top: 1px solid #0f172a;
            padding-top: 3px;
            width: 80%;
            margin: 0 auto;
        }

        @media print {
            body { padding: 0; }
            .print-controls { display: none !important; }
            .doc-wrapper { max-width: 100%; width: 100%; border-color: #000; }
            .doc-header, .header-logo-col, .header-info-col, .meta-section, table.item-table th, table.item-table td, .purpose-box, .signatures, .sign-box, .sign-name {
                border-color: #000 !important;
            }
            .header-title-col { background: #f8fafc !important; }
        }
    </style>
</head>
<body>

    <div class="print-controls">
        <button class="btn-print" onclick="window.print()">
            Cetak Dokumen
        </button>
        <a href="{{ route('project-balance-requests.index') }}" class="btn-close">
            Kembali
        </a>
    </div>

    <div class="doc-wrapper">
        {{-- Header Dokumen --}}
        <div class="doc-header">
            <div class="header-logo-col">
                <img src="{{ asset('assets/Logo-Dashboard.png') }}" alt="Logo PT Arsikon" onerror="this.style.display='none'">
                <div class="company-name-under-logo">PT ARSIKON CIPTA KARYA</div>
            </div>
            <div class="header-info-col">
                <div class="company-main">PT ARSIKON CIPTA KARYA</div>
                <div class="company-sub">General Contractor, Civil Works & Structural Engineering</div>
                <div class="company-sub">Formulir Resmi Permohonan Kas Operasional Lapangan (Petty Cash)</div>
            </div>
            <div class="header-title-col">
                <div class="doc-title">PENGAJUAN KAS LAPANGAN</div>
                <div class="doc-no">{{ $balanceRequest->request_number ?? ('PKL-' . $balanceRequest->id) }}</div>
            </div>
        </div>

        {{-- Metadata Pengajuan --}}
        <div class="meta-section">
            <div class="meta-col">
                <div class="meta-row">
                    <span class="meta-label">Nama Proyek</span>
                    <span class="meta-colon">:</span>
                    <span class="meta-val">{{ $balanceRequest->project?->name ?? '-' }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Pemohon (Admin Pengadaan)</span>
                    <span class="meta-colon">:</span>
                    <span class="meta-val">{{ $balanceRequest->signer_requester ?? $balanceRequest->requester?->name ?? 'Admin Pembelian Proyek' }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Tanggal Pengajuan</span>
                    <span class="meta-colon">:</span>
                    <span class="meta-val">{{ $balanceRequest->request_date ? $balanceRequest->request_date->translatedFormat('d F Y') : '-' }}</span>
                </div>
            </div>
            <div class="meta-col">
                <div class="meta-row">
                    <span class="meta-label">Tingkat Urgensi</span>
                    <span class="meta-colon">:</span>
                    <span class="meta-val">{{ strtoupper($balanceRequest->urgency ?? 'NORMAL') }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Ditujukan Kepada</span>
                    <span class="meta-colon">:</span>
                    <span class="meta-val">{{ $balanceRequest->approver_display_name }} (Pengadaan)</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Status Persetujuan</span>
                    <span class="meta-colon">:</span>
                    <span class="meta-val font-bold">
                        @if($balanceRequest->status === 'approved')
                            DISETUJUI & DICAIRKAN ADMIN PENGADAAN
                        @elseif($balanceRequest->status === 'rejected')
                            DITOLAK
                        @else
                            MENUNGGU PERSETUJUAN ADMIN PENGADAAN
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- Tabel Rincian Kebutuhan Item Belanja --}}
        <table class="item-table">
            <thead>
                <tr>
                    <th style="width: 35px;">No</th>
                    <th>Nama Barang / Kebutuhan Belanja</th>
                    <th style="width: 65px;">Qty</th>
                    <th style="width: 65px;">Satuan</th>
                    <th style="width: 120px;">Estimasi Harga (Rp)</th>
                    <th style="width: 130px;">Jumlah Total (Rp)</th>
                    <th style="width: 140px;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($balanceRequest->items as $idx => $item)
                <tr>
                    <td class="text-center font-mono">{{ $idx + 1 }}</td>
                    <td class="font-bold">{{ $item->item_name }}</td>
                    <td class="text-center">{{ (float)$item->quantity }}</td>
                    <td class="text-center">{{ $item->unit }}</td>
                    <td class="text-right">{{ number_format($item->estimated_unit_price, 0, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($item->estimated_subtotal, 0, ',', '.') }}</td>
                    <td>{{ $item->notes ?: '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td class="text-center font-mono">1</td>
                    <td class="font-bold">{{ $balanceRequest->purpose }}</td>
                    <td class="text-center">1</td>
                    <td class="text-center">Paket</td>
                    <td class="text-right">{{ number_format($balanceRequest->amount, 0, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($balanceRequest->amount, 0, ',', '.') }}</td>
                    <td>-</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="background-color: #fafafa; font-weight: bold;">
                    <td colspan="5" class="text-right font-extrabold" style="text-transform: uppercase;">
                        Total Pengajuan Kas Proyek:
                    </td>
                    <td class="text-right font-extrabold" style="font-size: 11px;">
                        Rp {{ number_format($balanceRequest->amount, 0, ',', '.') }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        {{-- Uraian & Keperluan Belanja --}}
        <div class="purpose-box">
            <div class="purpose-label">Keperluan & Alasan Pengajuan Kas:</div>
            <div>{{ $balanceRequest->purpose }}</div>
        </div>

        {{-- Tanda Tangan Resmi (PO Pusat sebagai Approval Final / Manajer Kas) --}}
        <div class="signatures">
            <div class="sign-box">
                <div>
                    <div class="sign-header">Diajukan Oleh:</div>
                    <div class="sign-role">Admin Pembelian Proyek</div>
                </div>
                <div class="sign-space"></div>
                <div class="sign-name">
                    ( {{ $balanceRequest->signer_requester ?? $balanceRequest->requester?->name ?? 'Admin Pengadaan Proyek' }} )
                </div>
            </div>

            <div class="sign-box">
                <div>
                    <div class="sign-header">Mengetahui / Diperiksa:</div>
                    <div class="sign-role">Site Engineer / Site Manager</div>
                </div>
                <div class="sign-space"></div>
                <div class="sign-name">
                    ( {{ $balanceRequest->signer_site_manager ?? 'Site Manager Lapangan' }} )
                </div>
            </div>

            <div class="sign-box">
                <div>
                    <div class="sign-header">Disetujui & Dicairkan Oleh:</div>
                    <div class="sign-role">Admin Pengadaan</div>
                </div>
                <div class="sign-space"></div>
                <div class="sign-name">
                    ( {{ $balanceRequest->approver_display_name }} )
                </div>
            </div>
        </div>
    </div>

</body>
</html>
