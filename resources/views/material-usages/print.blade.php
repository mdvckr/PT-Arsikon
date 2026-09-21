<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bon Pengeluaran Material — {{ $materialUsage->usage_number }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #fff;
            color: #0f172a;
            font-size: 12px;
            line-height: 1.5;
        }
        .print-sheet {
            max-width: 800px;
            margin: 0 auto;
            padding: 24px 32px;
            background: #fff;
        }
        /* Kop Surat */
        .kop {
            display: flex;
            align-items: center;
            gap: 16px;
            padding-bottom: 12px;
            border-bottom: 3px double #1e293b;
        }
        .kop-logo {
            width: 70px;
            height: 70px;
            object-fit: contain;
        }
        .kop-text {
            flex: 1;
            text-align: center;
        }
        .kop-text .company {
            font-size: 20px;
            font-weight: 800;
            color: #c2410c;
            letter-spacing: 0.03em;
        }
        .kop-text .tagline {
            font-size: 11px;
            font-weight: 600;
            color: #334155;
            margin-top: 2px;
        }
        .kop-text .address {
            font-size: 10px;
            color: #64748b;
            margin-top: 3px;
        }
        /* Title */
        .doc-title {
            text-align: center;
            margin: 16px 0 12px;
        }
        .doc-title h2 {
            font-size: 16px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #0f172a;
        }
        .doc-title .doc-num {
            font-size: 13px;
            font-weight: 700;
            color: #2563eb;
            margin-top: 2px;
        }
        /* Meta Grid */
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 16px;
            background: #f8fafc;
            padding: 12px 16px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }
        .meta-row {
            display: flex;
            font-size: 11.5px;
            margin-bottom: 4px;
        }
        .meta-label {
            width: 130px;
            color: #64748b;
            font-weight: 500;
            flex-shrink: 0;
        }
        .meta-val {
            font-weight: 600;
            color: #0f172a;
        }
        /* Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background: #f1f5f9;
            color: #334155;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
        }
        .items-table td {
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
            font-size: 11.5px;
        }
        .items-table tbody tr:nth-child(even) {
            background: #fafafa;
        }
        /* Signatures */
        .sig-section {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-top: 30px;
            text-align: center;
        }
        .sig-box {
            border: 1px solid #cbd5e1;
            padding: 12px 8px;
            border-radius: 4px;
            background: #fff;
        }
        .sig-title {
            font-size: 11px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 60px;
        }
        .sig-name {
            font-size: 11.5px;
            font-weight: 700;
            color: #0f172a;
            border-top: 1px solid #94a3b8;
            padding-top: 4px;
            display: inline-block;
            min-width: 140px;
        }
        .sig-role {
            font-size: 10px;
            color: #64748b;
        }
        /* Action buttons bar (hidden on print) */
        .print-actions {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
            z-index: 100;
            background: rgba(255, 255, 255, 0.95);
            padding: 8px 14px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border: 1px solid #e2e8f0;
        }
        .btn {
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }
        .btn-print { background: #2563eb; color: #fff; }
        .btn-close { background: #64748b; color: #fff; }
        @media print {
            .print-actions { display: none !important; }
            body { background: #fff; }
            .print-sheet { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="print-actions">
        <button class="btn btn-print" onclick="window.print()">
            🖨️ Cetak Dokumen
        </button>
        <button class="btn btn-close" onclick="window.close()">
            ✕ Tutup
        </button>
    </div>

    <div class="print-sheet">
        <!-- Kop Surat -->
        <div class="kop">
            <img src="{{ asset('assets/Logo-Login-web.png') }}" class="kop-logo" alt="Logo PT Arsikon" onerror="this.style.display='none'">
            <div class="kop-text">
                <div class="company">PT ARSIKON CIPTA KARYA</div>
                <div class="tagline">GENERAL CONTRACTOR & INTERIOR DESIGN</div>
                <div class="address">Gudang & Workshop Logistik Konstruksi — Sistem Inventori Material & Peralatan</div>
            </div>
        </div>

        <!-- Judul Dokumen -->
        <div class="doc-title">
            <h2>BUKTI PENGELUARAN MATERIAL (BON LAPANGAN)</h2>
            <div class="doc-num">{{ $materialUsage->usage_number }}</div>
        </div>

        <!-- Metadata Informasi -->
        <div class="meta-grid">
            <div>
                <div class="meta-row">
                    <span class="meta-label">Tanggal Pengeluaran</span>
                    <span class="meta-val">: {{ \Carbon\Carbon::parse($materialUsage->usage_date)->format('d F Y') }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Gudang Sumber</span>
                    <span class="meta-val">: {{ $materialUsage->warehouse?->name ?? '-' }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Proyek</span>
                    <span class="meta-val">: {{ $materialUsage->project?->name ?? ($materialUsage->warehouse?->is_central ? 'Gudang Pusat' : '-') }}</span>
                </div>
            </div>
            <div>
                <div class="meta-row">
                    <span class="meta-label">Penerima (Mandor/Tukang)</span>
                    <span class="meta-val">: <strong>{{ $materialUsage->recipient_name }}</strong></span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Bagian Pekerjaan / Zona</span>
                    <span class="meta-val">: {{ $materialUsage->job_section ?? 'Pekerjaan Umum Lapangan' }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Petugas Gudang</span>
                    <span class="meta-val">: {{ $materialUsage->issuedBy?->name ?? '-' }}</span>
                </div>
                @if($materialUsage->materialRequest)
                <div class="meta-row">
                    <span class="meta-label">Ref. No. Permintaan (MR)</span>
                    <span class="meta-val">: <strong>#{{ $materialUsage->materialRequest->request_number }}</strong> (Disetujui Site Manager)</span>
                </div>
                @else
                <div class="meta-row">
                    <span class="meta-label">Jenis Pengeluaran</span>
                    <span class="meta-val">: Input Bebas / Insidentil (Tanpa MR)</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Rincian Material Master -->
        @php $masterItems = $materialUsage->items->filter(fn($i) => !$i->isCustom()); @endphp
        @php $customItems = $materialUsage->items->filter(fn($i) => $i->isCustom()); @endphp
        <h4 style="color:#2563eb;margin:0 0 8px;font-size:13px;"><i class="fas fa-boxes-stacked"></i> Daftar Material (Master)</h4>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:30px;text-align:center;">No</th>
                    <th style="width:100px;">Kode Material</th>
                    <th>Nama Material / Spesifikasi</th>
                    <th style="width:110px;text-align:right;">Kuantitas</th>
                    <th style="width:70px;text-align:center;">Satuan</th>
                    <th>Keterangan / Peruntukan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($masterItems as $idx => $item)
                <tr>
                    <td style="text-align:center;">{{ $idx + 1 }}</td>
                    <td>{{ $item->material?->code ?? '-' }}</td>
                    <td><strong>{{ $item->displayName() }}</strong></td>
                    <td style="text-align:right;font-weight:700;">{{ format_quantity($item->quantity) }}</td>
                    <td style="text-align:center;">{{ $item->displayUnit() }}</td>
                    <td>{{ $item->notes ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;color:#94a3b8;padding:12px;">Tidak ada item master.</td></tr>
                @endforelse
            </tbody>
        </table>

        @if($customItems->count() > 0)
        <h4 style="color:#c2410c;margin:16px 0 8px;font-size:13px;border-bottom:2px solid #c2410c;padding-bottom:4px;"><i class="fas fa-pen-nib"></i> Daftar Barang Baru (Custom)</h4>
        <table class="items-table" style="border-top:3px double #c2410c;">
            <thead>
                <tr>
                    <th style="width:30px;text-align:center;">No</th>
                    <th>Nama Barang Baru</th>
                    <th style="width:70px;text-align:center;">Satuan</th>
                    <th style="width:110px;text-align:right;">Kuantitas</th>
                    <th>Keterangan / Peruntukan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($customItems as $idx => $item)
                <tr style="background:#fff8f0;">
                    <td style="text-align:center;">{{ $idx + 1 }}</td>
                    <td><strong>{{ $item->displayName() }}</strong><div style="font-size:10px;color:#c2410c;">Item Custom / Non-Master</div></td>
                    <td style="text-align:center;">{{ $item->displayUnit() }}</td>
                    <td style="text-align:right;font-weight:700;">{{ format_quantity($item->quantity) }}</td>
                    <td>{{ $item->notes ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @if($materialUsage->notes)
        <div style="font-size:11px;margin-bottom:16px;color:#475569;border-left:3px solid #cbd5e1;padding-left:8px;">
            <strong>Catatan Tambahan:</strong> {{ $materialUsage->notes }}
        </div>
        @endif

        <!-- Lembar Tanda Tangan 3 Pihak -->
        <div class="sig-section">
            <div class="sig-box">
                <div class="sig-title">Yang Mengeluarkan<br>(Admin Gudang)</div>
                <div class="sig-name">{{ $materialUsage->issuedBy?->name ?? '....................................' }}</div>
                <div class="sig-role">Logistik / Gudang</div>
            </div>
            <div class="sig-box">
                <div class="sig-title">Penerima Lapangan<br>(Mandor / Tukang / Subkon)</div>
                <div class="sig-name">{{ $materialUsage->recipient_name }}</div>
                <div class="sig-role">Pelaksana Lapangan</div>
            </div>
            <div class="sig-box">
                <div class="sig-title">Mengetahui & Menyetujui<br>(Site Manager / PM)</div>
                <div class="sig-name">....................................</div>
                <div class="sig-role">Site Manager / Proyek</div>
            </div>
        </div>

        <div style="margin-top:24px;font-size:9.5px;color:#94a3b8;text-align:center;">
            Dokumen ini dicetak secara otomatis melalui Sistem Logistik PT ARSIKON CIPTA KARYA pada {{ now()->format('d/m/Y H:i') }}.
        </div>
    </div>
</body>
</html>
