{{-- ========================================================================= --}}
{{-- DASHBOARD: ADMIN GUDANG & LOGISTIK (WAREHOUSE & STOCK LOGISTICS)          --}}
{{-- ========================================================================= --}}

@php
    // Tool Fleet Calculations
    $toolsReady = $toolsReady ?? 0;
    $toolsInUse = $toolsInUse ?? 0;
    $toolsMaintenance = $toolsMaintenance ?? 0;
    $totalFleet = $toolsReady + $toolsInUse + $toolsMaintenance;
    $readyPct = $totalFleet > 0 ? round(($toolsReady / $totalFleet) * 100, 1) : 0;
    $inUsePct = $totalFleet > 0 ? round(($toolsInUse / $totalFleet) * 100, 1) : 0;
    $maintPct = $totalFleet > 0 ? round(($toolsMaintenance / $totalFleet) * 100, 1) : 0;

    // Inventory Category Donut Setup
    $inventoryByCategory = $inventoryByCategory ?? collect();
    $totalCategoryQty = $inventoryByCategory->sum();
    $catPalette = [
        '#ea580c', // Arsikon Amber
        '#1e3a8a', // Structural Navy
        '#059669', // Safety Emerald
        '#d97706', // Warning Gold
        '#7c3aed', // Purple
        '#0284c7', // Sky
        '#64748b', // Slate
    ];
    $circumference = 238.76; // 2 * pi * 38
    $donutSegments = [];
    $runningOffset = 0;
    $catIdx = 0;

    foreach ($inventoryByCategory as $catName => $catQty) {
        $pct = $totalCategoryQty > 0 ? round(($catQty / $totalCategoryQty) * 100, 1) : 0;
        $dashLength = round(($pct / 100) * $circumference, 2);
        $color = $catPalette[$catIdx % count($catPalette)];
        $donutSegments[] = [
            'name' => $catName,
            'qty' => $catQty,
            'pct' => $pct,
            'color' => $color,
            'dasharray' => "{$dashLength} {$circumference}",
            'dashoffset' => -$runningOffset,
        ];
        $runningOffset += $dashLength;
        $catIdx++;
    }

    // 7-Day Stock Mutation Timeline Processing
    $past7Days = [];
    for ($i = 6; $i >= 0; $i--) {
        $d = now()->subDays($i)->format('Y-m-d');
        $past7Days[$d] = [
            'date' => $d,
            'label' => now()->subDays($i)->format('d M'),
            'total' => 0,
        ];
    }

    if (isset($mutations) && $mutations->count() > 0) {
        foreach ($mutations as $m) {
            $mDate = substr($m->date, 0, 10);
            if (isset($past7Days[$mDate])) {
                $past7Days[$mDate]['total'] += abs((float) $m->total);
            }
        }
    }

    $mutationPoints = [];
    $chartW = 620;
    $chartH = 150;
    $padX = 35;
    $padY = 22;
    $usableW = $chartW - ($padX * 2);
    $usableH = $chartH - ($padY * 2);

    $mutationValues = array_column(array_values($past7Days), 'total');
    $maxMutationVal = max(max($mutationValues), 10);
    $stepX = $usableW / 6;

    foreach ($mutationValues as $idx => $v) {
        $ptX = round($padX + ($idx * $stepX), 1);
        $ptY = round(($chartH - $padY) - (($v / $maxMutationVal) * $usableH), 1);
        $mutationPoints[] = ['x' => $ptX, 'y' => $ptY, 'val' => $v];
    }

    // Spline calculation
    $splinePath = "M {$mutationPoints[0]['x']} {$mutationPoints[0]['y']}";
    for ($i = 0; $i < count($mutationPoints) - 1; $i++) {
        $p0 = $mutationPoints[max($i - 1, 0)];
        $p1 = $mutationPoints[$i];
        $p2 = $mutationPoints[$i + 1];
        $p3 = $mutationPoints[min($i + 2, count($mutationPoints) - 1)];

        $cp1x = round($p1['x'] + ($p2['x'] - $p0['x']) / 6, 1);
        $cp1y = round($p1['y'] + ($p2['y'] - $p0['y']) / 6, 1);
        $cp2x = round($p2['x'] - ($p3['x'] - $p1['x']) / 6, 1);
        $cp2y = round($p2['y'] - ($p3['y'] - $p1['y']) / 6, 1);

        $splinePath .= " C {$cp1x} {$cp1y}, {$cp2x} {$cp2y}, {$p2['x']} {$p2['y']}";
    }
    $areaSplinePath = $splinePath . " L {$mutationPoints[6]['x']} " . ($chartH - $padY) . " L {$mutationPoints[0]['x']} " . ($chartH - $padY) . " Z";
    $totalMutationCount = array_sum($mutationValues);
@endphp

{{-- ROW 1: 4 TOP MODULAR METRIC CARDS --}}
<div class="dash-kpi-grid">
    {{-- Card 1: Open Purchase Orders / Kedatangan Logistik --}}
    <a href="{{ route('goods-receipts.create') }}" class="glass-erp-card kpi-erp-card" title="Buka Form Penerimaan Barang">
        <div>
            <div class="kpi-header-row">
                <span class="kpi-title-label">Jadwal Pengiriman (PO)</span>
                <div class="kpi-icon-lens">
                    <x-lucide-package-check style="width:15px;height:15px;" />
                </div>
            </div>
            <div class="kpi-primary-val">
                <span>{{ number_format($incomingTodayCount ?? 0) }}</span>
                <span class="val-unit">Jadwal Tiba Hari Ini</span>
            </div>
        </div>
        <div class="kpi-badge-pills">
            @if(($incomingLateCount ?? 0) > 0)
                <span class="badge-erp-pill badge-pill-danger">
                    <x-lucide-triangle-alert style="width:11px;height:11px;" />
                    {{ $incomingLateCount }} Terlambat
                </span>
            @else
                <span class="badge-erp-pill badge-pill-success">
                    <x-lucide-circle-check style="width:11px;height:11px;" />
                    Tepat Waktu
                </span>
            @endif
            <span class="badge-erp-pill badge-pill-gray">
                {{ $totalMaterials }} Item Terdaftar
            </span>
        </div>
    </a>

    {{-- Card 2: Field Requests & Material Operations --}}
    <a href="{{ route('material-requests.index') }}" class="glass-erp-card kpi-erp-card" title="Buka PO Lapangan">
        <div>
            <div class="kpi-header-row">
                <span class="kpi-title-label">PO Lapangan & Pemakaian</span>
                <div class="kpi-icon-lens">
                    <x-lucide-file-text style="width:15px;height:15px;" />
                </div>
            </div>
            <div class="kpi-primary-val" style="{{ $pendingRequests > 0 ? 'color:var(--arsikon-amber);' : '' }}">
                <span>{{ number_format($pendingRequests) }}</span>
                <span class="val-unit">Permintaan Pending</span>
            </div>
        </div>
        <div class="kpi-badge-pills">
            @if($pendingRequests > 0)
                <span class="badge-erp-pill badge-pill-amber">
                    <x-lucide-clock style="width:11px;height:11px;" />
                    Menunggu Approval
                </span>
            @else
                <span class="badge-erp-pill badge-pill-success">
                    <x-lucide-circle-check style="width:11px;height:11px;" />
                    Antrean Bersih
                </span>
            @endif

            @if($lowStockItems > 0)
                <span class="badge-erp-pill badge-pill-danger">
                    <x-lucide-triangle-alert style="width:11px;height:11px;" />
                    {{ $lowStockItems }} Kritis
                </span>
            @else
                <span class="badge-erp-pill badge-pill-gray">
                    Stok Aman
                </span>
            @endif
        </div>
    </a>

    {{-- Card 3: Armada Alat Kerja --}}
    <a href="{{ route('tool-assignments.index') }}" class="glass-erp-card kpi-erp-card" title="Buka Peminjaman Alat">
        <div>
            <div class="kpi-header-row">
                <span class="kpi-title-label">Alat Kerja</span>
                <div class="kpi-icon-lens">
                    <x-lucide-wrench style="width:15px;height:15px;" />
                </div>
            </div>
            <div class="kpi-primary-val">
                <span>{{ number_format($totalFleet) }}</span>
                <span class="val-unit">Unit Fisik</span>
            </div>
        </div>
        <div class="kpi-badge-pills">
            <span class="badge-erp-pill badge-pill-success">
                {{ $toolsReady }} Siap
            </span>
            <span class="badge-erp-pill badge-pill-navy">
                {{ $toolsInUse }} Di Lapangan
            </span>
            @if($toolsMaintenance > 0)
                <span class="badge-erp-pill badge-pill-danger">
                    {{ $toolsMaintenance }} Servis
                </span>
            @endif
        </div>
    </a>

    {{-- Card 4: Komposisi Stok per Kategori (SVG Donut Ring) --}}
    <a href="{{ route('inventory.index') }}" class="glass-erp-card kpi-erp-card" title="Buka Detail Inventori" style="justify-content: flex-start;">
        <div class="kpi-header-row" style="margin-bottom:4px;">
            <span class="kpi-title-label">Komposisi Stok Material</span>
            <div class="kpi-icon-lens">
                <x-lucide-pie-chart style="width:15px;height:15px;" />
            </div>
        </div>

        <div class="donut-card-layout">
            <div class="donut-svg-wrap">
                <svg viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="38" fill="transparent" stroke="#f1f5f9" stroke-width="14" />
                    @if($totalCategoryQty > 0)
                        @foreach($donutSegments as $seg)
                            @if($seg['pct'] > 0)
                                <circle
                                    cx="50"
                                    cy="50"
                                    r="38"
                                    fill="transparent"
                                    stroke="{{ $seg['color'] }}"
                                    stroke-width="14"
                                    stroke-dasharray="{{ $seg['dasharray'] }}"
                                    stroke-dashoffset="{{ $seg['dashoffset'] }}"
                                    stroke-linecap="round"
                                    style="transition: stroke-dasharray 0.5s ease;"
                                />
                            @endif
                        @endforeach
                    @else
                        <circle cx="50" cy="50" r="38" fill="transparent" stroke="#e2e8f0" stroke-width="14" />
                    @endif
                </svg>

                <div class="donut-center-stat">
                    <div class="num">{{ number_format($totalCategoryQty) }}</div>
                    <div class="lbl">Unit</div>
                </div>
            </div>

            <div class="donut-legend-list">
                @forelse(array_slice($donutSegments, 0, 3) as $seg)
                    <div class="donut-legend-item">
                        <span class="cat-name">
                            <span class="cat-dot" style="background: {{ $seg['color'] }};"></span>
                            {{ Str::limit($seg['name'], 9) }}
                        </span>
                        <span class="cat-pct">{{ $seg['pct'] }}%</span>
                    </div>
                @empty
                    <div style="font-size:11px;color:var(--arsikon-slate-muted);">Belum ada data stok.</div>
                @endforelse
            </div>
        </div>
    </a>
</div>

{{-- ROW 2: OPERATIONAL ANALYTICS & ARMADA READINESS MATRIX --}}
<div class="dash-grid-analytics">
    {{-- Left Panel: 7-Day Stock Mutation Spline Area Chart --}}
    <div class="glass-erp-card">
        <div class="analytics-card-header">
            <h3 class="analytics-card-title">
                <x-lucide-activity style="width:15px;height:15px;color:var(--arsikon-amber);" />
                Tren Mutasi Stok Material
            </h3>
            <div class="chart-timeframe-tag">
                <x-lucide-clock style="width:11px;height:11px;" />
                7 Hari Terakhir
            </div>
        </div>

        <div class="mutation-chart-container">
            <div class="mutation-chart-header">
                <div>
                    <span style="font-size:11px;color:var(--arsikon-slate-muted);font-weight:600;text-transform:uppercase;letter-spacing:0.3px;">Pergerakan Logistik</span>
                    <div style="font-size:17px;font-weight:800;color:var(--arsikon-slate-dark);line-height:1.2;">
                        {{ number_format($totalMutationCount) }} <span style="font-size:11.5px;font-weight:600;color:var(--arsikon-slate-muted);">Unit Termutasi</span>
                    </div>
                </div>
                <div class="chart-legend-wrap">
                    <span><span class="chart-legend-bullet" style="background:var(--arsikon-amber);"></span> Fluktuasi Mutasi</span>
                    <span><span class="chart-legend-bullet" style="background:#059669;"></span> Baseline Gudang</span>
                </div>
            </div>

            <div class="svg-chart-wrapper">
                <svg viewBox="0 0 {{ $chartW }} {{ $chartH }}" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="arsikonSplineGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="#ea580c" stop-opacity="0.28" />
                            <stop offset="80%" stop-color="#ea580c" stop-opacity="0.04" />
                            <stop offset="100%" stop-color="#ea580c" stop-opacity="0.0" />
                        </linearGradient>
                    </defs>

                    <line x1="{{ $padX }}" y1="{{ $padY }}" x2="{{ $chartW - $padX }}" y2="{{ $padY }}" stroke="#e2e8f0" stroke-dasharray="3 3" stroke-width="1" />
                    <line x1="{{ $padX }}" y1="{{ round($padY + ($usableH / 2)) }}" x2="{{ $chartW - $padX }}" y2="{{ round($padY + ($usableH / 2)) }}" stroke="#e2e8f0" stroke-dasharray="3 3" stroke-width="1" />
                    <line x1="{{ $padX }}" y1="{{ $chartH - $padY }}" x2="{{ $chartW - $padX }}" y2="{{ $chartH - $padY }}" stroke="#cbd5e1" stroke-width="1.2" />

                    <text x="{{ $padX - 8 }}" y="{{ $padY + 4 }}" fill="#94a3b8" font-size="9" text-anchor="end" font-weight="600">{{ $maxMutationVal }}</text>
                    <text x="{{ $padX - 8 }}" y="{{ round($padY + ($usableH / 2)) + 4 }}" fill="#94a3b8" font-size="9" text-anchor="end" font-weight="600">{{ round($maxMutationVal / 2) }}</text>
                    <text x="{{ $padX - 8 }}" y="{{ $chartH - $padY + 3 }}" fill="#94a3b8" font-size="9" text-anchor="end" font-weight="600">0</text>

                    <path d="{{ $areaSplinePath }}" fill="url(#arsikonSplineGrad)" />
                    <path d="{{ $splinePath }}" fill="none" stroke="#ea580c" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />

                    @foreach($mutationPoints as $idx => $pt)
                        <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}" r="4" fill="#ffffff" stroke="#ea580c" stroke-width="2" />
                        <text x="{{ $pt['x'] }}" y="{{ $chartH - 4 }}" fill="#64748b" font-size="9.5" text-anchor="middle" font-weight="600">
                            {{ array_values($past7Days)[$idx]['label'] }}
                        </text>
                    @endforeach
                </svg>
            </div>

            @if($totalMutationCount === 0)
                <div class="chart-empty-overlay">
                    <x-lucide-info class="inline-block me-1" style="width:13px;height:13px;color:var(--arsikon-amber);" />
                    Aktivitas mutasi 7 hari terakhir stabil &bull; Tidak ada lonjakan stok tak terencana.
                </div>
            @endif
        </div>
    </div>

    {{-- Right Panel: Tool Fleet Operational Readiness Matrix --}}
    <div class="glass-erp-card">
        <div class="analytics-card-header">
            <h3 class="analytics-card-title">
                <x-lucide-shield-check style="width:15px;height:15px;color:var(--arsikon-emerald);" />
                Alat Kerja
            </h3>
            <a href="{{ route('tool-assignments.index') }}" class="btn btn-sm btn-light border" style="height:25px;padding:0 8px;font-size:10.5px;font-weight:700;">
                Data Peminjaman &rarr;
            </a>
        </div>

        <div class="fleet-matrix-wrap">
            <div class="fleet-matrix-header">
                <div class="fleet-matrix-total">
                    {{ number_format($totalFleet) }} <span>Unit Fisik</span>
                </div>
                <div>
                    @if($totalFleet > 0 && $toolsMaintenance === 0)
                        <span class="badge-erp-pill badge-pill-success">
                            <x-lucide-circle-check style="width:11px;height:11px;" /> {{ $readyPct }}% Siap Operasi
                        </span>
                    @elseif($toolsMaintenance > 0)
                        <span class="badge-erp-pill badge-pill-danger">
                            <x-lucide-triangle-alert style="width:11px;height:11px;" /> {{ $toolsMaintenance }} Perlu Servis
                        </span>
                    @else
                        <span class="badge-erp-pill badge-pill-gray">Belum Ada Unit</span>
                    @endif
                </div>
            </div>

            <div class="fleet-segmented-track" title="Armada: {{ $toolsReady }} Siap, {{ $toolsInUse }} Di Lapangan, {{ $toolsMaintenance }} Servis">
                @if($totalFleet > 0)
                    @if($toolsReady > 0)
                        <div class="fleet-seg-item" style="background:#059669;width:{{ $readyPct }}%;"></div>
                    @endif
                    @if($toolsInUse > 0)
                        <div class="fleet-seg-item" style="background:#1e3a8a;width:{{ $inUsePct }}%;"></div>
                    @endif
                    @if($toolsMaintenance > 0)
                        <div class="fleet-seg-item" style="background:#dc2626;width:{{ $maintPct }}%;"></div>
                    @endif
                @else
                    <div class="fleet-seg-item" style="background:#e2e8f0;width:100%;"></div>
                @endif
            </div>

            <div class="fleet-status-table">
                <div class="fleet-status-card-row">
                    <div class="fleet-row-left">
                        <div class="fleet-row-lens" style="background:var(--arsikon-emerald-soft);color:var(--arsikon-emerald);border:1px solid #a7f3d0;">
                            <x-lucide-circle-check style="width:13px;height:13px;" />
                        </div>
                        <div class="fleet-row-labels">
                            <div class="title">Siap Pakai (Standby)</div>
                            <div class="desc">Tersedia di gudang siap dipinjam</div>
                        </div>
                    </div>
                    <div class="fleet-row-right">
                        <div class="num">{{ number_format($toolsReady) }} <span class="unit">Unit</span></div>
                        <div class="pct" style="color:#059669;">{{ $readyPct }}%</div>
                    </div>
                </div>

                <div class="fleet-status-card-row">
                    <div class="fleet-row-left">
                        <div class="fleet-row-lens" style="background:var(--arsikon-navy-soft);color:var(--arsikon-navy);border:1px solid #bfdbfe;">
                            <x-lucide-user-cog style="width:13px;height:13px;" />
                        </div>
                        <div class="fleet-row-labels">
                            <div class="title">Sedang di Lapangan</div>
                            <div class="desc">Aktif dipinjam personil proyek</div>
                        </div>
                    </div>
                    <div class="fleet-row-right">
                        <div class="num">{{ number_format($toolsInUse) }} <span class="unit">Unit</span></div>
                        <div class="pct" style="color:#1e3a8a;">{{ $inUsePct }}%</div>
                    </div>
                </div>

                <div class="fleet-status-card-row">
                    <div class="fleet-row-left">
                        <div class="fleet-row-lens" style="background:{{ $toolsMaintenance > 0 ? 'var(--arsikon-rose-soft)' : '#f8fafc' }};color:{{ $toolsMaintenance > 0 ? 'var(--arsikon-rose)' : '#94a3b8' }};border:1px solid {{ $toolsMaintenance > 0 ? '#fecaca' : '#e2e8f0' }};">
                            <x-lucide-wrench style="width:13px;height:13px;" />
                        </div>
                        <div class="fleet-row-labels">
                            <div class="title">Servis / Kalibrasi</div>
                            <div class="desc">Dalam pemeliharaan bengkel</div>
                        </div>
                    </div>
                    <div class="fleet-row-right">
                        <div class="num" style="{{ $toolsMaintenance > 0 ? 'color:var(--arsikon-rose);' : '' }}">
                            {{ number_format($toolsMaintenance) }} <span class="unit">Unit</span>
                        </div>
                        <div class="pct" style="color:{{ $toolsMaintenance > 0 ? 'var(--arsikon-rose)' : '#94a3b8' }};">{{ $maintPct }}%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ROW 3: TRIPLE OPERATIONAL STREAM (Surat Jalan, PO Lapangan, Peringatan & Aksi) --}}
<div class="dash-grid-triad">
    {{-- Panel 1: Distribusi Surat Jalan Terbaru --}}
    <div class="glass-erp-card">
        <div class="stream-panel-header">
            <h3 class="stream-panel-title">
                <x-lucide-truck style="width:14px;height:14px;color:var(--arsikon-navy);" />
                Distribusi Surat Jalan
            </h3>
            <a href="{{ route('distributions.index') }}" class="btn btn-sm btn-light border" style="height:24px;padding:0 7px;font-size:10px;font-weight:700;">
                Lihat Semua &rarr;
            </a>
        </div>

        <div style="overflow-x:auto;">
            <table class="stream-table">
                <thead>
                    <tr>
                        <th>No. Surat Jalan</th>
                        <th>Rute Pengiriman</th>
                        <th style="text-align:center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentDistributions as $dist)
                    <tr>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('distributions.show', $dist) }}" class="fw-700 text-primary" style="text-decoration:none;">
                                {{ $dist->distribution_number }}
                            </a>
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:4px;font-size:11px;color:var(--arsikon-slate-dark);">
                                <span>{{ $dist->fromWarehouse?->name ?? 'Pusat' }}</span>
                                <x-lucide-arrow-right style="width:10px;height:10px;color:#94a3b8;" />
                                <strong>{{ $dist->toWarehouse?->name ?? 'Proyek' }}</strong>
                            </div>
                            <div style="font-size:10px;color:var(--arsikon-slate-muted);">
                                {{ $dist->driver_name ? 'Supir: ' . $dist->driver_name : 'Tgl: ' . ($dist->shipped_at ? \Carbon\Carbon::parse($dist->shipped_at)->format('d/m/Y') : '-') }}
                            </div>
                        </td>
                        <td style="text-align:center;">
                            @if($dist->status === 'received')
                                <span class="badge-erp-pill badge-pill-success">Diterima</span>
                            @else
                                <span class="badge-erp-pill badge-pill-navy">Dalam Rute</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" style="text-align:center;padding:26px 12px;color:var(--arsikon-slate-muted);font-size:11.5px;">
                            Belum ada transaksi surat jalan aktif.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Panel 2: PO Lapangan Terbaru --}}
    <div class="glass-erp-card">
        <div class="stream-panel-header">
            <h3 class="stream-panel-title">
                <x-lucide-clipboard-list style="width:14px;height:14px;color:var(--arsikon-amber);" />
                PO Lapangan Terbaru
            </h3>
            <a href="{{ route('material-requests.index') }}" class="btn btn-sm btn-light border" style="height:24px;padding:0 7px;font-size:10px;font-weight:700;">
                Lihat Semua &rarr;
            </a>
        </div>

        <div style="overflow-x:auto;">
            <table class="stream-table">
                <thead>
                    <tr>
                        <th>No. PO</th>
                        <th>Pemohon / Proyek</th>
                        <th style="text-align:center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentRequests as $req)
                    <tr>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('material-requests.show', $req) }}" class="fw-700 text-primary" style="text-decoration:none;">
                                {{ $req->request_number }}
                            </a>
                        </td>
                        <td>
                            <div class="fw-700" style="color:var(--arsikon-slate-dark);font-size:11.5px;">{{ $req->requestedBy?->name ?? '-' }}</div>
                            <div style="font-size:10px;color:var(--arsikon-slate-muted);">{{ $req->toWarehouse?->display_name ?? 'Gudang Tujuan' }}</div>
                        </td>
                        <td style="text-align:center;">
                            @php
                                $statusMap = [
                                    'pending'  => ['badge-pill-amber', 'Pending'],
                                    'approved' => ['badge-pill-success', 'Disetujui'],
                                    'rejected' => ['badge-pill-danger', 'Ditolak'],
                                    'fulfilled'=> ['badge-pill-navy', 'Terpenuhi'],
                                ];
                                [$cls, $label] = $statusMap[$req->status] ?? ['badge-pill-gray', $req->status];
                            @endphp
                            <span class="badge-erp-pill {{ $cls }}">{{ $label }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" style="text-align:center;padding:26px 12px;color:var(--arsikon-slate-muted);font-size:11.5px;">
                            Belum ada pengajuan PO Lapangan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Panel 3: Peringatan Operasional & Aksi Cepat --}}
    <div class="glass-erp-card">
        <div class="stream-panel-header">
            <h3 class="stream-panel-title">
                <x-lucide-bell-ring style="width:14px;height:14px;color:var(--arsikon-rose);" />
                Peringatan & Aksi Cepat
            </h3>
            <span class="badge-erp-pill badge-pill-gray" style="font-size:9.5px;">Live Feed</span>
        </div>

        <div class="alert-feed-list">
            @php $hasAlert = false; @endphp

            @if(($incomingLateCount ?? 0) > 0)
                @php $hasAlert = true; @endphp
                <a href="{{ route('goods-receipts.create') }}" class="alert-feed-item danger" style="text-decoration:none;">
                    <x-lucide-triangle-alert />
                    <div>
                        <strong>{{ $incomingLateCount }} Pengiriman Terlambat</strong>
                        <div style="font-size:10px;opacity:0.85;">Jadwal PO vendor melewati batas waktu estimasi tiba.</div>
                    </div>
                </a>
            @endif

            @if($lowStockItems > 0)
                @php $hasAlert = true; @endphp
                <a href="{{ route('inventory.index') }}" class="alert-feed-item danger" style="text-decoration:none;">
                    <x-lucide-alert-circle />
                    <div>
                        <strong>{{ $lowStockItems }} Material Kritis</strong>
                        <div style="font-size:10px;opacity:0.85;">Stok fisik berada di bawah batas minimum pengamanan.</div>
                    </div>
                </a>
            @endif

            @if($pendingRequests > 0)
                @php $hasAlert = true; @endphp
                <a href="{{ route('material-requests.index') }}" class="alert-feed-item warning" style="text-decoration:none;">
                    <x-lucide-clock />
                    <div>
                        <strong>{{ $pendingRequests }} PO Lapangan Menunggu Approval</strong>
                        <div style="font-size:10px;opacity:0.85;">Segera verifikasi pengajuan material dari lapangan.</div>
                    </div>
                </a>
            @endif

            @if(!$hasAlert)
                <div class="alert-feed-item success">
                    <x-lucide-shield-check />
                    <div>
                        <strong>Logistik & Inventori Terkendali</strong>
                        <div style="font-size:10px;opacity:0.9;">Tidak ada insiden keterlambatan atau stok kritis aktif.</div>
                    </div>
                </div>
            @endif
        </div>

        <div class="quick-actions-quad">
            @can('create material requests')
            <a href="{{ route('material-requests.create') }}" class="action-quad-btn">
                <x-lucide-plus />
                <span>PO Lapangan</span>
            </a>
            @endcan

            @can('create distributions')
            <a href="{{ route('distributions.create') }}" class="action-quad-btn">
                <x-lucide-send />
                <span>Surat Jalan</span>
            </a>
            @endcan

            <a href="{{ route('material-usages.create') }}" class="action-quad-btn">
                <x-lucide-package-minus />
                <span>Pakai Material</span>
            </a>

            @can('create tool assignments')
            <a href="{{ route('tool-assignments.create') }}" class="action-quad-btn">
                <x-lucide-handshake />
                <span>Pinjam Alat</span>
            </a>
            @endcan
        </div>
    </div>
</div>
