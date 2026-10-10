<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Surat Jalan — {{ $distribution->distribution_number }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f1f5f9;
            color: #0f172a;
            font-size: 12px;
            line-height: 1.45;
        }

        /* ===== TOOLBAR CETAK (HANYA DITAMPILKAN DI LAYAR) ===== */
        .print-toolbar {
            max-width: 860px;
            margin: 16px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #ffffff;
            padding: 12px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
        }

        .toolbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
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
            font-size: 13px;
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
            background: #2563eb;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.3);
        }
        .btn-toolbar.print:hover {
            background: #1d4ed8;
        }

        .toggle-kop-label {
            font-size: 12.5px;
            color: #334155;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            cursor: pointer;
            user-select: none;
            font-weight: 500;
        }

        /* ===== A4 PRINT SHEET ===== */
        .print-sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 30px auto;
            background-color: #ffffff;
            background-repeat: no-repeat;
            background-position: top center;
            background-size: 210mm 297mm;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
            position: relative;
            padding: 48mm 20mm 42mm 20mm; /* Menghindari area Kop di atas dan Ribbon di bawah */
        }

        /* Kelas dengan Background Template Kop */
        .with-kop-bg {
            background-image: url('{{ asset('assets/kop-surat-arsikon.png') }}');
        }

        /* ===== JUDUL SURAT JALAN ===== */
        .doc-title-block {
            text-align: center;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid #0f172a;
        }

        .doc-title-block h2 {
            font-size: 17px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 3px;
        }

        .doc-title-block .doc-number {
            font-size: 13px;
            font-weight: 700;
            color: #2563eb;
            letter-spacing: 0.03em;
        }

        .doc-title-block .doc-ref {
            font-size: 11px;
            color: #64748b;
            margin-top: 2px;
        }

        /* ===== INFORMASI PENGIRIMAN (METADATA) ===== */
        .meta-card {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.95);
            padding: 8px 12px;
            margin-bottom: 14px;
        }

        .meta-grid {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5px;
        }

        .meta-grid td {
            padding: 2.5px 4px;
            vertical-align: top;
        }

        .meta-grid .lbl {
            width: 120px;
            color: #475569;
            font-weight: 600;
        }

        .meta-grid .val {
            color: #0f172a;
            font-weight: 500;
        }

        .meta-grid .colon {
            width: 10px;
            color: #64748b;
            text-align: center;
        }

        /* ===== TABEL DAFTAR BARANG & ALAT ===== */
        .section-label {
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            color: #1e293b;
            margin: 12px 0 5px 0;
            display: flex;
            align-items: center;
            gap: 6px;
            letter-spacing: 0.02em;
        }

        .table-items {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 12px;
            background: rgba(255, 255, 255, 0.98);
        }

        .table-items thead th {
            background: #f1f5f9;
            color: #1e293b;
            border: 1px solid #94a3b8;
            padding: 6px 6px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.03em;
            text-align: center;
        }

        .table-items tbody td {
            border: 1px solid #94a3b8;
            padding: 5px 7px;
            color: #1e293b;
            vertical-align: middle;
        }

        .table-items tbody tr:nth-child(even) {
            background: #fafbfc;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }

        .sku-code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 10px;
            color: #475569;
            background: #f1f5f9;
            padding: 1px 4px;
            border-radius: 3px;
            border: 1px solid #e2e8f0;
            display: inline-block;
        }

        /* ===== CATATAN ===== */
        .notes-card {
            border: 1px dashed #94a3b8;
            background: rgba(255, 255, 255, 0.95);
            padding: 6px 10px;
            border-radius: 5px;
            font-size: 11px;
            color: #334155;
            margin-top: 10px;
            margin-bottom: 16px;
        }

        /* ===== TANDA TANGAN FORMAL ===== */
        .signature-grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: transparent;
        }

        .signature-cell {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 0 8px;
        }

        .sig-role {
            font-size: 11px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 2px;
        }

        .sig-sub {
            font-size: 10px;
            color: #64748b;
        }

        .sig-space {
            height: 52px;
        }

        .sig-name {
            font-size: 11.5px;
            font-weight: 700;
            color: #0f172a;
            border-bottom: 1px solid #0f172a;
            display: inline-block;
            min-width: 130px;
            padding-bottom: 2px;
        }

        .sig-date {
            font-size: 9.5px;
            color: #64748b;
            margin-top: 3px;
        }

        /* ===== MEDIA CETAK (PRINT RULES) ===== */
        @media print {
            body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .print-toolbar {
                display: none !important;
            }

            .print-sheet {
                width: 210mm !important;
                min-height: 297mm !important;
                margin: 0 !important;
                box-shadow: none !important;
                border: none !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            @page {
                size: A4 portrait;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    {{-- TOOLBAR ATAS (HANYA LAYAR) --}}
    <div class="print-toolbar">
        <div class="toolbar-left">
            <a href="{{ route('distributions.show', $distribution) }}" class="btn-toolbar back">
                <i class="fas fa-arrow-left"></i> Kembali ke Detail
            </a>
            <label class="toggle-kop-label" title="Centang untuk menyertakan background Kop Resmi PT Arsikon saat mencetak">
                <input type="checkbox" id="toggleKopCheckbox" checked onchange="toggleKopBackground(this)">
                <span>Sertakan Kop Surat Resmi PT Arsikon</span>
            </label>
        </div>
        <div class="toolbar-right">
            <button type="button" class="btn-toolbar print" onclick="window.print()">
                <i class="fas fa-print"></i> Cetak Dokumen (A4)
            </button>
        </div>
    </div>

    {{-- LEMBAR A4 DOKUMEN --}}
    <div class="print-sheet with-kop-bg" id="printSheet">

        {{-- JUDUL SURAT JALAN --}}
        <div class="doc-title-block">
            <h2>SURAT JALAN PENGIRIMAN ALAT & MATERIAL</h2>
            <div class="doc-number">No: {{ $distribution->distribution_number }}</div>
            @if($distribution->surat_jalan)
                <div class="doc-ref">Ref. Fisik Surat Jalan: <strong>{{ $distribution->surat_jalan }}</strong></div>
            @endif
        </div>

        {{-- METADATA PENGIRIMAN --}}
        <div class="meta-card">
            <table class="meta-grid">
                <tr>
                    <td class="lbl">Gudang Asal</td>
                    <td class="colon">:</td>
                    <td class="val"><strong>{{ $distribution->fromWarehouse?->name ?? '-' }}</strong></td>
                    <td class="lbl" style="padding-left:14px;">Tanggal Kirim</td>
                    <td class="colon">:</td>
                    <td class="val">{{ $distribution->delivery_date ? \Carbon\Carbon::parse($distribution->delivery_date)->format('d/m/Y') : '-' }}</td>
                </tr>
                <tr>
                    <td class="lbl">Gudang Tujuan</td>
                    <td class="colon">:</td>
                    <td class="val"><strong>{{ $distribution->toWarehouse?->name ?? '-' }}</strong></td>
                    <td class="lbl" style="padding-left:14px;">Nama Pengemudi</td>
                    <td class="colon">:</td>
                    <td class="val">{{ $distribution->driver_name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="lbl">No. Permintaan (Ref)</td>
                    <td class="colon">:</td>
                    <td class="val">{{ $distribution->materialRequest?->request_number ?? '-' }}</td>
                    <td class="lbl" style="padding-left:14px;">No. Polisi Kendaraan</td>
                    <td class="colon">:</td>
                    <td class="val">{{ $distribution->vehicle_number ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="lbl">Status Pengiriman</td>
                    <td class="colon">:</td>
                    <td class="val" style="text-transform:uppercase;font-weight:700;">
                        {{ $distribution->status === 'completed' ? 'Selesai / Diterima' : ($distribution->status === 'in_transit' ? 'Dalam Pengiriman' : $distribution->status) }}
                    </td>
                    <td class="lbl" style="padding-left:14px;">Tanggal Diterima</td>
                    <td class="colon">:</td>
                    <td class="val">{{ $distribution->received_at ? \Carbon\Carbon::parse($distribution->received_at)->format('d/m/Y H:i') : '-' }}</td>
                </tr>
            </table>
        </div>

        {{-- FILTER MASTER ITEMS & CUSTOM ITEMS --}}
        @php
            $masterItems = $distribution->items->filter(fn($i) => $i->material_id !== null || $i->tool_id !== null);
            $customItems = $distribution->items->filter(fn($i) => $i->material_id === null && $i->tool_id === null);
        @endphp

        {{-- TABEL ITEM MASTER --}}
        @if($masterItems->isNotEmpty())
        <div class="section-label">
            <i class="fas fa-boxes-stacked" style="color:#2563eb;font-size:11px;"></i>
            Daftar Material & Alat Kerja (Master Inventori)
        </div>
        <table class="table-items">
            <thead>
                <tr>
                    <th style="width:28px;">No</th>
                    <th style="text-align:left;">Nama Barang / Alat</th>
                    <th style="width:110px;">Kode / SKU</th>
                    <th style="width:65px;">Satuan</th>
                    <th style="width:75px;">Dikirim</th>
                    <th style="width:75px;">Diterima</th>
                    <th style="width:65px;">Rusak</th>
                    <th style="width:75px;">Hilang/Kurang</th>
                </tr>
            </thead>
            <tbody>
                @foreach($masterItems as $idx => $item)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $item->name() }}</strong>
                        @if($item->isTool() && $item->toolAssignment)
                            <div style="font-size:9.5px;color:#2563eb;">Pinjaman: {{ $item->toolAssignment->assignment_number }}</div>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="sku-code">{{ $item->detail() ?: '-' }}</span>
                    </td>
                    <td class="text-center">{{ $item->unitAbbr() }}</td>
                    <td class="text-center" style="font-weight:700;">{{ number_format((float)$item->qty_shipped, 0, ',', '.') }}</td>
                    <td class="text-center" style="color:#059669;font-weight:700;">{{ number_format((float)$item->qty_received, 0, ',', '.') }}</td>
                    <td class="text-center" style="{{ (float)$item->qty_damaged_or_lost > 0 ? 'color:#dc2626;font-weight:700;' : 'color:#64748b;' }}">
                        {{ number_format((float)$item->qty_damaged_or_lost, 0, ',', '.') }}
                    </td>
                    <td class="text-center" style="{{ (float)$item->qty_lost > 0 ? 'color:#d97706;font-weight:700;' : 'color:#64748b;' }}">
                        {{ number_format((float)$item->qty_lost, 0, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- TABEL ITEM CUSTOM / TAMBAHAN --}}
        @if($customItems->isNotEmpty())
        <div class="section-label" style="color:#c2410c;">
            <i class="fas fa-pen-nib" style="font-size:11px;"></i>
            Daftar Barang / Alat Tambahan (Custom Non-Master)
        </div>
        <table class="table-items">
            <thead>
                <tr>
                    <th style="width:28px;">No</th>
                    <th style="text-align:left;">Nama Barang / Alat Baru</th>
                    <th style="width:110px;">Klasifikasi</th>
                    <th style="width:65px;">Satuan</th>
                    <th style="width:75px;">Dikirim</th>
                    <th style="width:75px;">Diterima</th>
                    <th style="width:65px;">Rusak</th>
                    <th style="width:75px;">Hilang/Kurang</th>
                </tr>
            </thead>
            <tbody>
                @foreach($customItems as $idx => $item)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $item->custom_item_name ?? '(Item Custom)' }}</strong>
                    </td>
                    <td class="text-center">
                        <span style="font-size:10px;color:#c2410c;font-weight:600;">Item Tambahan</span>
                    </td>
                    <td class="text-center">{{ $item->custom_item_unit ?? 'unit' }}</td>
                    <td class="text-center" style="font-weight:700;">{{ number_format((float)$item->qty_shipped, 0, ',', '.') }}</td>
                    <td class="text-center" style="color:#059669;font-weight:700;">{{ number_format((float)$item->qty_received, 0, ',', '.') }}</td>
                    <td class="text-center" style="{{ (float)$item->qty_damaged_or_lost > 0 ? 'color:#dc2626;font-weight:700;' : 'color:#64748b;' }}">
                        {{ number_format((float)$item->qty_damaged_or_lost, 0, ',', '.') }}
                    </td>
                    <td class="text-center" style="{{ (float)$item->qty_lost > 0 ? 'color:#d97706;font-weight:700;' : 'color:#64748b;' }}">
                        {{ number_format((float)$item->qty_lost, 0, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- CATATAN PENGIRIMAN --}}
        @if($distribution->notes)
        <div class="notes-card">
            <strong>Catatan Pengiriman:</strong> {{ $distribution->notes }}
        </div>
        @endif

        {{-- AREA TANDA TANGAN (3 PIHAK: PENGIRIM, SUPIR, PENERIMA) --}}
        <table class="signature-grid">
            <tr>
                <td class="signature-cell">
                    <div class="sig-role">Diserahkan Oleh,</div>
                    <div class="sig-sub">(Petugas Gudang Asal)</div>
                    <div class="sig-space"></div>
                    <div class="sig-name">
                        {{ $distribution->shippedBy?->name ?? ($distribution->creator?->name ?? '..................................') }}
                    </div>
                    <div class="sig-date">Tgl: {{ $distribution->delivery_date ? \Carbon\Carbon::parse($distribution->delivery_date)->format('d/m/Y') : '___/___/2026' }}</div>
                </td>
                <td class="signature-cell">
                    <div class="sig-role">Membawa / Supir,</div>
                    <div class="sig-sub">(Jasa Ekspedisi / Driver)</div>
                    <div class="sig-space"></div>
                    <div class="sig-name">
                        {{ $distribution->driver_name ?: '..................................' }}
                    </div>
                    <div class="sig-date">No. Pol: {{ $distribution->vehicle_number ?: '..................' }}</div>
                </td>
                <td class="signature-cell">
                    <div class="sig-role">Diterima Oleh,</div>
                    <div class="sig-sub">(Petugas Gudang Tujuan)</div>
                    <div class="sig-space"></div>
                    <div class="sig-name">
                        {{ $distribution->receivedBy?->name ?? '..................................' }}
                    </div>
                    <div class="sig-date">Tgl: {{ $distribution->received_at ? \Carbon\Carbon::parse($distribution->received_at)->format('d/m/Y') : '___/___/2026' }}</div>
                </td>
            </tr>
        </table>

        @if($distribution->status === 'in_transit' || $distribution->status === 'draft')
        <div style="margin-top:14px;text-align:center;font-size:9.5px;color:#64748b;font-style:italic;">
            * Lembar Surat Jalan ini wajib ditandatangani saat serah terima barang dan dikonfirmasi di sistem CWMS PT Arsikon Cipta Karya.
        </div>
        @endif

    </div>

    <script>
        function toggleKopBackground(checkbox) {
            const sheet = document.getElementById('printSheet');
            if (!sheet) return;
            if (checkbox.checked) {
                sheet.classList.add('with-kop-bg');
            } else {
                sheet.classList.remove('with-kop-bg');
            }
        }
    </script>
</body>
</html>
