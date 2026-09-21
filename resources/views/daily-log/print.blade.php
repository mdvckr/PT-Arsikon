<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Harian Logistik — {{ $selectedWarehouse->name }} ({{ \Carbon\Carbon::parse($date)->format('d/m/Y') }})</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #fff;
            color: #0f172a;
            font-size: 11px;
            line-height: 1.4;
        }
        .print-sheet {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px 30px;
            background: #fff;
        }
        /* Kop Surat */
        .kop {
            display: flex;
            align-items: center;
            gap: 16px;
            padding-bottom: 10px;
            border-bottom: 3px double #1e293b;
        }
        .kop-logo {
            width: 65px;
            height: 65px;
            object-fit: contain;
        }
        .kop-text {
            flex: 1;
            text-align: center;
        }
        .kop-text .company {
            font-size: 19px;
            font-weight: 800;
            color: #c2410c;
            letter-spacing: 0.03em;
        }
        .kop-text .tagline {
            font-size: 10.5px;
            font-weight: 600;
            color: #334155;
            margin-top: 1px;
        }
        .kop-text .address {
            font-size: 9.5px;
            color: #64748b;
            margin-top: 2px;
        }
        /* Title */
        .doc-title {
            text-align: center;
            margin: 14px 0 10px;
        }
        .doc-title h2 {
            font-size: 15px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #0f172a;
        }
        .doc-title .doc-sub {
            font-size: 11.5px;
            font-weight: 700;
            color: #2563eb;
            margin-top: 2px;
        }
        /* Meta Grid */
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 14px;
            background: #f8fafc;
            padding: 10px 14px;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
            font-size: 11px;
        }
        .meta-row { display: flex; margin-bottom: 3px; }
        .meta-label { width: 140px; color: #64748b; font-weight: 500; flex-shrink: 0; }
        .meta-val { font-weight: 700; color: #0f172a; }

        /* Section Headings */
        .section-header {
            font-size: 11.5px;
            font-weight: 800;
            text-transform: uppercase;
            color: #0f172a;
            margin: 14px 0 6px;
            padding-bottom: 3px;
            border-bottom: 1.5px solid #cbd5e1;
            display: flex;
            justify-content: space-between;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        th {
            background: #f1f5f9;
            color: #334155;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 5px 8px;
            border: 1px solid #cbd5e1;
        }
        td {
            padding: 5px 8px;
            border: 1px solid #cbd5e1;
            font-size: 10.5px;
        }
        tbody tr:nth-child(even) { background: #fafafa; }

        /* Signatures */
        .sig-section {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-top: 24px;
            text-align: center;
        }
        .sig-box {
            border: 1px solid #cbd5e1;
            padding: 10px 6px;
            border-radius: 4px;
            background: #fff;
        }
        .sig-title {
            font-size: 10.5px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 50px;
        }
        .sig-name {
            font-size: 11px;
            font-weight: 700;
            color: #0f172a;
            border-top: 1px solid #94a3b8;
            padding-top: 3px;
            display: inline-block;
            min-width: 130px;
        }
        .sig-role { font-size: 9.5px; color: #64748b; }

        /* Action Buttons */
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
            font-size: 12.5px;
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
            🖨️ Cetak Laporan
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
                <div class="address">Gudang & Workshop Logistik Konstruksi — Log Book & Laporan Harian Site</div>
            </div>
        </div>

        <!-- Judul -->
        <div class="doc-title">
            <h2>LAPORAN HARIAN LOGISTIK DAN MATERIAL PROYEK</h2>
            <div class="doc-sub">{{ $selectedWarehouse->name }} — Tanggal: {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</div>
        </div>

        <!-- Metadata -->
        <div class="meta-grid">
            <div>
                <div class="meta-row">
                    <span class="meta-label">Gudang / Site</span>
                    <span class="meta-val">: {{ $selectedWarehouse->name }} ({{ $selectedWarehouse->is_central ? 'Pusat' : 'Proyek' }})</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Proyek Terkait</span>
                    <span class="meta-val">: {{ $selectedWarehouse->project?->name ?? '-' }}</span>
                </div>
            </div>
            <div>
                <div class="meta-row">
                    <span class="meta-label">Dicetak Pada</span>
                    <span class="meta-val">: {{ now()->format('d/m/Y H:i') }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Petugas Logistik</span>
                    <span class="meta-val">: {{ auth()->user()->name }}</span>
                </div>
            </div>
        </div>

        <!-- 1. Pemakaian Material Hari Ini -->
        <div class="section-header">
            <span>1. Pemakaian Material Lapangan Hari Ini (Outgoing)</span>
            <span style="font-weight:600;font-size:10px;">{{ $usages->count() }} Transaksi</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th style="width:30px;text-align:center;">No</th>
                    <th style="width:110px;">No. Bon</th>
                    <th style="width:150px;">Mandor / Penerima</th>
                    <th style="width:160px;">Pekerjaan / Zona</th>
                    <th>Material & Kuantitas yang Dikeluarkan</th>
                    <th style="width:110px;">Petugas Gudang</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usages as $idx => $usage)
                <tr>
                    <td style="text-align:center;">{{ $idx + 1 }}</td>
                    <td><strong>{{ $usage->usage_number }}</strong></td>
                    <td><strong>{{ $usage->recipient_name }}</strong></td>
                    <td>{{ $usage->job_section ?? '-' }}</td>
                    <td>
                        @foreach($usage->items as $uItem)
                        <div>• <strong>{{ format_quantity($uItem->quantity) }} {{ $uItem->material?->unit?->abbreviation }}</strong> — {{ $uItem->material?->name }}</div>
                        @endforeach
                    </td>
                    <td>{{ $usage->issuedBy?->name ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;color:#64748b;">Tidak ada pemakaian material pada tanggal ini.</td></tr>
                @endforelse
            </tbody>
        </table>

        <!-- 2. Penerimaan Material Hari Ini -->
        <div class="section-header">
            <span>2. Penerimaan Material Masuk Hari Ini (Incoming)</span>
            <span style="font-weight:600;font-size:10px;">{{ $incomingDistributions->count() + $incomingGoodsReceipts->count() }} Dokumen</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th style="width:30px;text-align:center;">No</th>
                    <th style="width:110px;">No. Surat Jalan/GR</th>
                    <th style="width:160px;">Sumber / Pengirim</th>
                    <th>Material & Kuantitas Diterima</th>
                    <th style="width:90px;text-align:center;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @php $inCount = 0; @endphp
                @foreach($incomingDistributions as $dist)
                @php $inCount++; @endphp
                <tr>
                    <td style="text-align:center;">{{ $inCount }}</td>
                    <td><strong>{{ $dist->distribution_number }}</strong></td>
                    <td>{{ $dist->fromWarehouse?->name ?? 'Gudang Pusat' }} (Supir: {{ $dist->driver_name ?? '-' }})</td>
                    <td>
                        @foreach($dist->items as $dItem)
                        <div>• <strong>{{ format_quantity($dItem->quantity) }} {{ $dItem->material?->unit?->abbreviation }}</strong> — {{ $dItem->material?->name ?? $dItem->tool?->name }}</div>
                        @endforeach
                    </td>
                    <td style="text-align:center;">Distribusi Pusat</td>
                </tr>
                @endforeach

                @foreach($incomingGoodsReceipts as $gr)
                @php $inCount++; @endphp
                <tr>
                    <td style="text-align:center;">{{ $inCount }}</td>
                    <td><strong>{{ $gr->receipt_number }}</strong></td>
                    <td>{{ $gr->supplier?->name ?? 'Supplier' }} (SJ: {{ $gr->supplier_do_number ?? '-' }})</td>
                    <td>
                        @foreach($gr->items as $gItem)
                        <div>• <strong>{{ format_quantity($gItem->quantity_received) }} {{ $gItem->material?->unit?->abbreviation }}</strong> — {{ $gItem->material?->name }}</div>
                        @endforeach
                    </td>
                    <td style="text-align:center;">Supplier PO</td>
                </tr>
                @endforeach

                @if($inCount === 0)
                <tr><td colspan="5" style="text-align:center;color:#64748b;">Tidak ada penerimaan barang masuk pada tanggal ini.</td></tr>
                @endif
            </tbody>
        </table>

        <!-- 2b. Distribusi Keluar ke Gudang Lain -->
        <div class="section-header">
            <span>2b. Distribusi Keluar ke Gudang Lain (Outgoing)</span>
            <span style="font-weight:600;font-size:10px;">{{ $outgoingDistributions->count() }} Dokumen</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th style="width:30px;text-align:center;">No</th>
                    <th style="width:110px;">No. Surat Jalan</th>
                    <th style="width:150px;">Tujuan</th>
                    <th style="width:90px;text-align:center;">Tgl Kirim</th>
                    <th>Material & Kuantitas Dikirim</th>
                </tr>
            </thead>
            <tbody>
                @php $outCount = 0; @endphp
                @forelse($outgoingDistributions as $dist)
                @php $outCount++; @endphp
                <tr>
                    <td style="text-align:center;">{{ $outCount }}</td>
                    <td><strong>{{ $dist->distribution_number }}</strong></td>
                    <td>{{ $dist->toWarehouse?->name ?? 'Gudang Tujuan' }} (Supir: {{ $dist->driver_name ?? '-' }})</td>
                    <td style="text-align:center;">{{ $dist->shipped_at ? \Carbon\Carbon::parse($dist->shipped_at)->format('d/m/Y') : '-' }}</td>
                    <td>
                        @foreach($dist->items as $dItem)
                        <div>• <strong>{{ format_quantity($dItem->quantity) }} {{ $dItem->material?->unit?->abbreviation }}</strong> — {{ $dItem->material?->name ?? $dItem->tool?->name }}</div>
                        @endforeach
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center;color:#64748b;"> Tidak ada distribusi keluar pada tanggal ini.</td></tr>
                @endforelse
            </tbody>
        </table>

        <!-- 2c. Retur Material dari Lapangan -->
        <div class="section-header">
            <span>2c. Retur Material dari Lapangan (Returns)</span>
            <span style="font-weight:600;font-size:10px;">{{ $materialReturns->count() }} Retur</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th style="width:30px;text-align:center;">No</th>
                    <th style="width:110px;">No. Retur</th>
                    <th style="width:140px;">Sumber Retur</th>
                    <th style="width:90px;text-align:center;">Tgl Terima</th>
                    <th>Material & Kuantitas Diterima</th>
                </tr>
            </thead>
            <tbody>
                @php $retCount = 0; @endphp
                @forelse($materialReturns as $ret)
                @php $retCount++; @endphp
                <tr>
                    <td style="text-align:center;">{{ $retCount }}</td>
                    <td><strong>{{ $ret->return_number }}</strong></td>
                    <td>{{ $ret->fromWarehouse?->name ?? 'Lapangan' }} (Penerima: {{ $ret->receiver?->name ?? '-' }})</td>
                    <td style="text-align:center;">{{ $ret->received_at ? \Carbon\Carbon::parse($ret->received_at)->format('d/m/Y') : '-' }}</td>
                    <td>
                        @foreach($ret->items as $rItem)
                        <div>• <strong>{{ format_quantity($rItem->received_qty) }} {{ $rItem->material?->unit?->abbreviation }}</strong> — {{ $rItem->material?->name }} ({{ ucfirst($rItem->condition ?? 'good') }})</div>
                        @endforeach
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center;color:#64748b;">Tidak ada retur material pada tanggal ini.</td></tr>
                @endforelse
            </tbody>
        </table>

        <!-- 3. Status Alat Kerja -->
        <div class="section-header">
            <span>3. Pantauan Alat Kerja di Lapangan (Tools Monitoring)</span>
            <span style="font-weight:600;font-size:10px;">{{ $toolAssignments->count() }} Peminjaman</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th style="width:30px;text-align:center;">No</th>
                    <th style="width:140px;">Nama Alat</th>
                    <th style="width:70px;text-align:center;">Jumlah</th>
                    <th style="width:140px;">Peminjam (Mandor/Tukang)</th>
                    <th>Lokasi Lapangan</th>
                    <th style="width:90px;">Estimasi Kembali</th>
                    <th style="width:100px;text-align:center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($toolAssignments as $idx => $ta)
                <tr>
                    <td style="text-align:center;">{{ $idx + 1 }}</td>
                    <td><strong>{{ $ta->tool?->name }}</strong> ({{ $ta->tool?->code ?? '-' }})</td>
                    <td style="text-align:center;">{{ $ta->quantity ?? 1 }} Unit</td>
                    <td>{{ $ta->borrower_display }} @if($ta->borrower_phone) <span style="color:#64748b;">({{ $ta->borrower_phone }})</span> @endif</td>
                    <td>{{ $ta->location_display }}</td>
                    <td>{{ $ta->expected_return_at ? \Carbon\Carbon::parse($ta->expected_return_at)->format('d/m/Y') : '-' }}</td>
                    <td style="text-align:center;font-weight:700;">
                        {{ $ta->status === 'returned' ? 'SUDAH KEMBALI' : 'DI LAPANGAN' }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;color:#64748b;">Tidak ada aktivitas alat kerja pada tanggal ini.</td></tr>
                @endforelse
            </tbody>
        </table>

        <!-- 4. Neraca Sisa Stok — hanya dipakai, grouped kategori -->
        <div class="section-header">
            <span>4. Neraca Sisa Stok Material Hari Ini (Closing Balance)</span>
            <span style="font-weight:600;font-size:10px;">Dikelompokkan kategori — hanya material dipakai</span>
        </div>
        @if(isset($stockBalanceGrouped) && $stockBalanceGrouped->isNotEmpty())
            @foreach($stockBalanceGrouped as $catName => $rows)
            <div style="background:#f1f5f9;padding:4px 8px;margin:8px 0 4px;font-weight:800;font-size:10px;text-transform:uppercase;color:#0f172a;border-left:3px solid #2563eb;">{{ $catName }} — {{ $rows->count() }} material</div>
            <table>
                <thead>
                    <tr>
                        <th style="width:30px;text-align:center;">No</th>
                        <th>Nama Material</th>
                        <th style="width:90px;text-align:right;">Stok Awal</th>
                        <th style="width:90px;text-align:right;">Masuk (+)</th>
                        <th style="width:90px;text-align:right;">Dipakai (-)</th>
                        <th style="width:110px;text-align:right;background:#e2e8f0;">Sisa Stok Sore</th>
                        <th style="width:60px;text-align:center;">Satuan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $idx => $stk)
                    <tr>
                        <td style="text-align:center;">{{ $idx + 1 }}</td>
                        <td><strong>{{ $stk->material_name }}</strong> <span style="color:#64748b;font-size:9.5px;">({{ $stk->material_code }})</span></td>
                        <td style="text-align:right;">{{ format_quantity($stk->opening_stock) }}</td>
                        <td style="text-align:right;">{{ $stk->qty_in > 0 ? '+' . format_quantity($stk->qty_in) : '-' }}</td>
                        <td style="text-align:right;">{{ $stk->qty_out > 0 ? '-' . format_quantity($stk->qty_out) : '-' }}</td>
                        <td style="text-align:right;font-weight:800;background:#f1f5f9;">{{ format_quantity($stk->closing_stock) }}</td>
                        <td style="text-align:center;">{{ $stk->unit }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endforeach
        @else
            <div style="text-align:center;color:#64748b;padding:12px;border:1px dashed #cbd5e1;border-radius:4px;">Tidak ada material yang dipakai pada tanggal ini.</div>
        @endif

        <!-- 5. Neraca Posisi & Kesiapan Alat Kerja — hanya dipinjam, grouped kategori -->
        <div class="section-header">
            <span>5. Neraca Posisi & Kesiapan Alat Kerja (Tool Availability)</span>
            <span style="font-weight:600;font-size:10px;">Dikelompokkan kategori — hanya alat dipinjam</span>
        </div>
        @if(isset($toolBalanceGrouped) && $toolBalanceGrouped->isNotEmpty())
            @foreach($toolBalanceGrouped as $catName => $rows)
            <div style="background:#f1f5f9;padding:4px 8px;margin:8px 0 4px;font-weight:800;font-size:10px;text-transform:uppercase;color:#0f172a;border-left:3px solid #7c3aed;">{{ $catName }} — {{ $rows->count() }} alat</div>
            <table>
                <thead>
                    <tr>
                        <th style="width:30px;text-align:center;">No</th>
                        <th>Nama Alat Kerja</th>
                        <th style="width:75px;text-align:center;">Total Fisik</th>
                        <th style="width:80px;text-align:center;background:#dcfce7;color:#166534;">Ready Gudang</th>
                        <th style="width:80px;text-align:center;background:#f3e8ff;color:#6b21a8;">Di Lapangan</th>
                        <th style="width:75px;text-align:center;background:#fee2e2;color:#991b1b;">Rusak/Servis</th>
                        <th>Mandor Penanggung Jawab</th>
                        <th style="width:110px;text-align:center;">Status Besok</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $idx => $tb)
                    <tr>
                        <td style="text-align:center;">{{ $idx + 1 }}</td>
                        <td><strong>{{ $tb->tool_name }}</strong><div style="font-size:9.5px;color:#64748b;">{{ $tb->tool_code }} | {{ $tb->brand }}</div></td>
                        <td style="text-align:center;font-weight:800;">{{ $tb->stock_total }} Unit</td>
                        <td style="text-align:center;font-weight:800;background:#f0fdf4;color:#166534;">{{ $tb->stock_available }} Unit</td>
                        <td style="text-align:center;font-weight:700;background:#faf5ff;color:#6b21a8;">{{ $tb->stock_borrowed }} Unit</td>
                        <td style="text-align:center;font-weight:700;background:#fff1f2;color:#9f1239;">{{ $tb->stock_maintenance }} Unit</td>
                        <td>@if(!empty($tb->borrowers)) @foreach($tb->borrowers as $b)<div style="font-size:9.5px;">• {{ $b }}</div>@endforeach @else <span style="color:#64748b;font-style:italic;">Standby</span> @endif</td>
                        <td style="text-align:center;font-weight:700;font-size:9.5px;">
                            @if($tb->stock_available > 0)<span style="color:#166534;">SIAP PAKAI ({{ $tb->stock_available }})</span>
                            @elseif($tb->stock_borrowed > 0)<span style="color:#6b21a8;">DI LAPANGAN</span>
                            @elseif($tb->stock_maintenance > 0)<span style="color:#991b1b;">PERLU SERVIS</span>
                            @else<span style="color:#64748b;">KOSONG</span>@endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endforeach
        @else
            <div style="text-align:center;color:#64748b;padding:12px;border:1px dashed #cbd5e1;border-radius:4px;">Tidak ada alat yang dipinjam pada tanggal ini.</div>
        @endif

        <!-- Tanda Tangan 3 Pihak -->
        <div class="sig-section">
            <div class="sig-box">
                <div class="sig-title">Dibuat Oleh<br>(Admin Logistik / Gudang)</div>
                <div class="sig-name">{{ auth()->user()->name }}</div>
                <div class="sig-role">Logistik Proyek</div>
            </div>
            <div class="sig-box">
                <div class="sig-title">Diperiksa Oleh<br>(Pelaksana Lapangan / Mandor Utama)</div>
                <div class="sig-name">....................................</div>
                <div class="sig-role">Pelaksana Lapangan</div>
            </div>
            <div class="sig-box">
                <div class="sig-title">Disetujui Oleh<br>(Site Manager / PM)</div>
                <div class="sig-name">....................................</div>
                <div class="sig-role">Site Manager / Project Manager</div>
            </div>
        </div>

        <div style="margin-top:20px;font-size:9px;color:#94a3b8;text-align:center;">
            Dokumen Laporan Harian Logistik dicetak otomatis melalui CWMS PT ARSIKON CIPTA KARYA pada {{ now()->format('d/m/Y H:i:s') }}.
        </div>
    </div>
</body>
</html>
