<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $bulkMaterial->title }} - PT. Arsikon Cipta Karya</title>
    <style>
        @page {
            size: A4 portrait;
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
        .header-box {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .company-name {
            font-size: 16px;
            font-weight: 900;
            color: #1e3a8a;
            letter-spacing: -0.2px;
        }
        .doc-title {
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            margin-top: 2px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .meta-table td {
            padding: 3px 6px;
            font-size: 10px;
            border: none;
        }
        .meta-label {
            font-weight: bold;
            color: #475569;
            width: 120px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #94a3b8;
            padding: 5px 6px;
            font-size: 9.5px;
        }
        table.data-table th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: 800;
            text-align: center;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .font-extrabold { font-weight: 800; }
        
        .summary-card {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 15px;
            display: inline-block;
            min-width: 250px;
        }
        .summary-card-title {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 4px;
            color: #1e293b;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 3px;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
            font-size: 9.5px;
        }

        .signatures {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }
        .signatures td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            border: none;
            font-size: 9.5px;
        }
        .sign-space {
            height: 55px;
        }

        .no-print {
            margin-bottom: 12px;
            padding: 8px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <div>
            <strong>Mode Cetak Dokumen Rekap Material Curah</strong> &bull; Ukuran A4 Portrait
        </div>
        <div>
            <button onclick="window.print()" style="background:#1e3a8a;color:#fff;border:none;padding:5px 12px;border-radius:4px;cursor:pointer;font-weight:bold;font-size:11px;">
                Cetak / Simpan PDF
            </button>
            <button onclick="window.close()" style="background:#64748b;color:#fff;border:none;padding:5px 12px;border-radius:4px;cursor:pointer;font-weight:bold;font-size:11px;margin-left:5px;">
                Tutup
            </button>
        </div>
    </div>

    {{-- Header --}}
    <div class="header-box">
        <div>
            <div class="company-name">PT. ARSIKON CIPTA KARYA</div>
            <div class="doc-title">{{ strtoupper($bulkMaterial->title) }}</div>
        </div>
        <div style="text-align: right; font-size: 9px; color: #64748b;">
            <div>Dokumen Rekap Logistik Lapangan</div>
            <div>Dicetak: {{ now()->translatedFormat('d M Y H:i') }}</div>
        </div>
    </div>

    {{-- Metadata --}}
    <table class="meta-table">
        <tr>
            <td class="meta-label">Nama Proyek</td>
            <td>: <strong>{{ $bulkMaterial->project?->name ?? ($bulkMaterial->project_name ?? '-') }}</strong></td>
            <td class="meta-label">Suplier</td>
            <td>: <strong>{{ $bulkMaterial->supplier_name }}</strong></td>
        </tr>
        <tr>
            <td class="meta-label">Periode</td>
            <td>: {{ $bulkMaterial->start_date ? $bulkMaterial->start_date->translatedFormat('d M Y') : '-' }} 
                  @if($bulkMaterial->end_date && $bulkMaterial->end_date != $bulkMaterial->start_date)
                      s/d {{ $bulkMaterial->end_date->translatedFormat('d M Y') }}
                  @endif
            </td>
            <td class="meta-label">Total Ritase</td>
            <td>: <strong>{{ number_format($bulkMaterial->total_rit, 0) }} Rit</strong> ({{ $bulkMaterial->items->count() }} Kali Pengiriman)</td>
        </tr>
        @if($bulkMaterial->notes)
        <tr>
            <td class="meta-label">Keterangan</td>
            <td colspan="3">: {{ $bulkMaterial->notes }}</td>
        </tr>
        @endif
    </table>

    {{-- Main Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 110px;">Tanggal</th>
                <th style="width: 90px;">No. Nota / Surat</th>
                <th style="width: 50px;">Qty</th>
                <th style="width: 50px;">Satuan</th>
                <th>Jenis Material</th>
                <th style="width: 85px;">Harga (Rp)</th>
                <th style="width: 95px;">Jumlah (Rp)</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bulkMaterial->items as $idx => $item)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td class="text-center font-bold">{{ $item->delivery_date ? $item->delivery_date->translatedFormat('d/m/Y') : '-' }}</td>
                <td class="text-center">{{ $item->ticket_number ?: '-' }}</td>
                <td class="text-center font-bold">{{ (float)$item->quantity }}</td>
                <td class="text-center">{{ $item->unit ?: 'Rit' }}</td>
                <td class="font-bold">{{ $item->material_type }}</td>
                <td class="text-right">{{ $item->unit_price > 0 ? number_format($item->unit_price, 0, ',', '.') : '-' }}</td>
                <td class="text-right font-bold">{{ $item->subtotal > 0 ? number_format($item->subtotal, 0, ',', '.') : '-' }}</td>
                <td>{{ $item->notes ?: '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center" style="padding: 15px; color: #64748b;">
                    Belum ada data pengiriman material curah tercatat.
                </td>
            </tr>
            @endforelse
        </tbody>
        @if($bulkMaterial->items->isNotEmpty())
        <tfoot>
            <tr style="background-color: #f8fafc; font-weight: bold;">
                <td colspan="3" class="text-right font-extrabold" style="text-transform: uppercase;">Total Keseluruhan:</td>
                <td class="text-center font-extrabold" style="font-size: 11px;">{{ number_format($bulkMaterial->total_rit, 0) }}</td>
                <td class="text-center">Rit</td>
                <td></td>
                <td></td>
                <td class="text-right font-extrabold" style="font-size: 10.5px;">
                    {{ $bulkMaterial->total_amount > 0 ? number_format($bulkMaterial->total_amount, 0, ',', '.') : '-' }}
                </td>
                <td></td>
            </tr>
        </tfoot>
        @endif
    </table>

    {{-- Rekapitulasi per Jenis Material --}}
    @php
        $summaryByType = $bulkMaterial->summary_by_type;
    @endphp
    @if($summaryByType->isNotEmpty())
    <div class="summary-card">
        <div class="summary-card-title">Rekapitulasi Per Jenis Material:</div>
        @foreach($summaryByType as $typeName => $info)
        <div class="summary-item">
            <span>&bull; {{ $typeName }}</span>
            <strong>{{ $info['total_qty'] }} {{ $info['unit'] }} ({{ $info['count'] }} rit)</strong>
        </div>
        @endforeach
        <div class="summary-item" style="border-top: 1px dashed #cbd5e1; margin-top: 3px; padding-top: 3px; font-weight: bold;">
            <span>Total Ritase:</span>
            <span>{{ number_format($bulkMaterial->total_rit, 0) }} Rit</span>
        </div>
    </div>
    @endif

    {{-- Signatures --}}
    <table class="signatures">
        <tr>
            <td>
                <div>Dibuat Oleh:</div>
                <div style="font-weight: bold; margin-top: 2px;">Logistik / Pelaksana Lapangan</div>
                <div class="sign-space"></div>
                <div>( .................................................. )</div>
            </td>
            <td>
                <div>Diperiksa & Diverifikasi:</div>
                <div style="font-weight: bold; margin-top: 2px;">Site Engineer / Pelaksana</div>
                <div class="sign-space"></div>
                <div>( .................................................. )</div>
            </td>
            <td>
                <div>Disetujui Oleh:</div>
                <div style="font-weight: bold; margin-top: 2px;">Project Manager / Site Manager</div>
                <div class="sign-space"></div>
                <div>( .................................................. )</div>
            </td>
        </tr>
    </table>

</body>
</html>
