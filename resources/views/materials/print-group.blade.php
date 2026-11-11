<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Rekap Material {{ $typeName ?: 'Kelompok Barang' }} - PT. Arsikon Cipta Karya</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 6mm;
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
            position: relative;
        }
        .header-title h1 {
            margin: 0;
            font-size: 15px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
        }
        .header-title h2 {
            margin: 3px 0 0 0;
            font-size: 12.5px;
            font-weight: 700;
            color: #1e3a8a;
            text-transform: uppercase;
        }
        .header-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 6px;
            font-size: 10px;
            color: #475569;
            padding: 4px 8px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 12px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #94a3b8;
            padding: 5px 6px;
            font-size: 9.5px;
            vertical-align: top;
        }
        table.data-table th {
            background-color: #e2e8f0;
            color: #0f172a;
            font-weight: 800;
            text-align: center;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.3px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .font-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }
        
        .stage-badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 8.5px;
            margin-bottom: 3px;
            line-height: 1.25;
            border: 1px solid #cbd5e1;
        }
        .stage-received {
            background-color: #ecfdf5;
            border-color: #a7f3d0;
            color: #065f46;
        }
        .stage-planned {
            background-color: #fffbeb;
            border-color: #fde68a;
            color: #92400e;
        }
        .stage-partial {
            background-color: #eff6ff;
            border-color: #bfdbfe;
            color: #1e40af;
        }

        .row-total {
            background-color: #fef08a !important;
            font-weight: 800;
            border-top: 2px solid #000;
        }

        /* Signatures block */
        .signature-table {
            width: 100%;
            border: none;
            margin-top: 18px;
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
            height: 52px;
        }
        .sign-name {
            font-weight: bold;
            text-decoration: underline;
            font-size: 10px;
        }
        .sign-role {
            font-size: 9px;
            color: #475569;
        }

        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    {{-- Top Action Bar --}}
    <div class="no-print" style="margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; background: #f1f5f9; padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1;">
        <div style="font-size: 12px; font-weight: 600; color: #334155;">
            Pratinjau Cetak: Kelompok {{ $typeName ?: 'Umum / Lainnya' }}
        </div>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" style="padding: 6px 14px; background: #0f172a; color: #fff; font-weight: bold; font-size: 11px; border-radius: 4px; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 6px;">
                🖨 Cetak Halaman (A4 Landscape)
            </button>
            <button onclick="window.close()" style="padding: 6px 12px; background: #fff; color: #334155; font-weight: bold; font-size: 11px; border-radius: 4px; cursor: pointer; border: 1px solid #cbd5e1;">
                Tutup
            </button>
        </div>
    </div>

    {{-- Header --}}
    <div class="header-title">
        <h1>PT. ARSIKON CIPTA KARYA</h1>
        <h2>LAPORAN REKAPITULASI MATERIAL PER KELOMPOK BARANG</h2>
    </div>

    {{-- Header Metadata --}}
    <div class="header-meta">
        <div>
            <strong>Kelompok Barang:</strong> {{ $typeName ?: 'Umum / Lainnya' }}
            &bull; <strong>Kategori:</strong> {{ $category ? $category->name : 'Semua Kategori' }}
        </div>
        <div>
            <strong>Gudang / Proyek:</strong> {{ $warehouse ? $warehouse->name : 'Semua Gudang Terkonsolidasi' }}
            &bull; <strong>Tanggal Cetak:</strong> {{ now()->translatedFormat('d F Y, H:i') }} WIB
        </div>
    </div>

    @php
        $sumTotalStock = 0.0;
        $sumPemakaian  = 0.0;
        $sumSisaStock  = 0.0;
    @endphp

    {{-- Main Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th style="width: 75px;">Kode / SKU</th>
                <th>Nama Material & Spesifikasi</th>
                <th style="width: 85px;">Supplier</th>
                <th style="width: 40px;">Satuan</th>
                <th style="width: 280px;">Rincian Jadwal & Tahapan Kedatangan</th>
                <th style="width: 75px;">Total Masuk</th>
                <th style="width: 70px;">Pemakaian</th>
                <th style="width: 75px;">Sisa Stok</th>
                <th style="width: 80px;">Status Stok</th>
            </tr>
        </thead>
        <tbody>
            @forelse($materials as $index => $m)
                @php
                    $stages = $m->getDynamicIncomingStages($filterWarehouseIds ?? null);
                    if ($stages->isEmpty() && !empty($m->incoming_stages)) {
                        $stages = collect($m->incoming_stages);
                    }
                    if ($stages->isEmpty() && !empty($filterWarehouseIds)) {
                        $allStages = $m->getDynamicIncomingStages(null);
                        if ($allStages->isNotEmpty()) {
                            $stages = $allStages;
                        }
                    }

                    $stockSisa = (float) $m->inventories->sum('quantity');
                    $pemakaian = (float) abs($m->stockMutations->where('qty_change', '<', 0)->sum('qty_change'));
                    $totalIn   = (float) $m->stockMutations->where('qty_change', '>', 0)->sum('qty_change');
                    $totalStock = $totalIn > 0 ? max($totalIn, $stockSisa + $pemakaian) : ($stockSisa + $pemakaian);

                    $sumTotalStock += $totalStock;
                    $sumPemakaian  += $pemakaian;
                    $sumSisaStock  += $stockSisa;

                    $unitCode = $m->unit?->code ?? ($m->unit?->name ?? 'Unit');
                @endphp
                <tr>
                    <td class="text-center font-mono">{{ $index + 1 }}</td>
                    <td class="text-center font-mono font-bold">{{ $m->sku ?: '-' }}</td>
                    <td>
                        <div class="font-bold">{{ $m->name }}</div>
                        @if($m->brand || $m->size)
                            <div style="font-size: 8.5px; color: #475569; margin-top: 1px;">
                                @if($m->brand) Merek: {{ $m->brand }} @endif
                                @if($m->brand && $m->size) &bull; @endif
                                @if($m->size) Dimensi: {{ $m->size }} @endif
                            </div>
                        @endif
                    </td>
                    <td>{{ $m->display_supplier_name }}</td>
                    <td class="text-center font-bold">{{ $unitCode }}</td>
                    <td>
                        @if($stages->isNotEmpty())
                            @foreach($stages as $stg)
                                @php
                                    $stgStatus = $stg['status'] ?? 'received';
                                    $badgeClass = match($stgStatus) {
                                        'received' => 'stage-received',
                                        'partial'  => 'stage-partial',
                                        default    => 'stage-planned',
                                    };
                                    $stgQty = (float)($stg['received_qty'] ?? ($stg['qty'] ?? 0));
                                    if ($stgStatus === 'planned' && isset($stg['remaining_qty']) && (float)$stg['remaining_qty'] > 0) {
                                        $stgQty = (float)$stg['remaining_qty'];
                                    }
                                    $stgDate = !empty($stg['date']) ? \Carbon\Carbon::parse($stg['date'])->format('d/m/Y') : '-';
                                    $stgRef  = $stg['reference'] ?? ($stg['stage'] ?? 'PO/GR');
                                    $stgLabel = $stg['status_label'] ?? ($stgStatus === 'received' ? 'Masuk' : 'Rencana');
                                @endphp
                                <div class="stage-badge {{ $badgeClass }}" style="display: block; margin-bottom: 2px;">
                                    <strong>{{ $stgRef }}</strong> &bull; Tgl: {{ $stgDate }} &bull;
                                    <strong>{{ number_format($stgQty, 2, ',', '.') }}</strong> {{ $unitCode }}
                                    ({{ $stgLabel }})
                                </div>
                            @endforeach
                        @else
                            <span style="color: #94a3b8; font-style: italic;">Tidak ada tahapan aktif</span>
                        @endif
                    </td>
                    <td class="text-right font-bold font-mono">
                        {{ number_format($totalStock, 2, ',', '.') }}
                    </td>
                    <td class="text-right font-mono" style="color: #b91c1c;">
                        {{ number_format($pemakaian, 2, ',', '.') }}
                    </td>
                    <td class="text-right font-bold font-mono" style="color: #047857;">
                        {{ number_format($stockSisa, 2, ',', '.') }}
                    </td>
                    <td class="text-center font-bold">
                        @if($stockSisa > 0)
                            <span style="color: #065f46;">TERSEDIA</span>
                        @else
                            <span style="color: #991b1b;">HABIS</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 18px; color: #64748b;">
                        Tidak ada material dalam kelompok barang ini.
                    </td>
                </tr>
            @endforelse

            {{-- Row Akumulasi Total --}}
            @if($materials->isNotEmpty())
                <tr class="row-total">
                    <td colspan="6" class="text-center font-bold">
                        TOTAL REKAPITULASI KELOMPOK
                    </td>
                    <td class="text-right font-bold font-mono">
                        {{ number_format($sumTotalStock, 2, ',', '.') }}
                    </td>
                    <td class="text-right font-bold font-mono">
                        {{ number_format($sumPemakaian, 2, ',', '.') }}
                    </td>
                    <td class="text-right font-bold font-mono">
                        {{ number_format($sumSisaStock, 2, ',', '.') }}
                    </td>
                    <td class="text-center font-bold">-</td>
                </tr>
            @endif
        </tbody>
    </table>

    {{-- Signatures --}}
    <table class="signature-table">
        <tr>
            <td>
                <div>Dibuat Oleh,</div>
                <div class="sign-role">Logistik & Gudang Proyek</div>
                <div class="sign-space"></div>
                <div class="sign-name">( ............................................ )</div>
                <div class="sign-role">Admin Logistik / Gudang</div>
            </td>
            <td>
                <div>Diperiksa Oleh,</div>
                <div class="sign-role">Pelaksana / Pengawas Lapangan</div>
                <div class="sign-space"></div>
                <div class="sign-name">( ............................................ )</div>
                <div class="sign-role">Pelaksana Proyek</div>
            </td>
            <td>
                <div>Disetujui Oleh,</div>
                <div class="sign-role">Manajemen Operasional</div>
                <div class="sign-space"></div>
                <div class="sign-name">( ............................................ )</div>
                <div class="sign-role">Site Manager / Manajer Operasional</div>
            </td>
        </tr>
    </table>

</body>
</html>
