<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    @push('styles')
    <style>
        :root {
            --arsikon-amber: #ea580c;
            --arsikon-amber-dark: #c2410c;
            --arsikon-amber-soft: #fff7ed;
            --arsikon-slate-dark: #0f172a;
            --arsikon-slate-muted: #64748b;
            --arsikon-navy: #1e3a8a;
            --arsikon-navy-soft: #eff6ff;
            --arsikon-emerald: #059669;
            --arsikon-emerald-soft: #ecfdf5;
            --arsikon-rose: #dc2626;
            --arsikon-rose-soft: #fef2f2;
            --arsikon-gold: #d97706;
            --arsikon-gold-soft: #fffbeb;
            --glass-bg: rgba(255, 255, 255, 0.82);
            --glass-border: rgba(226, 232, 240, 0.88);
            --glass-border-hover: rgba(234, 88, 12, 0.38);
            --glass-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05), 0 2px 6px -1px rgba(15, 23, 42, 0.02), inset 0 1px 0 rgba(255, 255, 255, 0.95);
            --glass-shadow-hover: 0 10px 28px -4px rgba(15, 23, 42, 0.08), 0 3px 10px -2px rgba(234, 88, 12, 0.08), inset 0 1px 0 rgba(255, 255, 255, 1);
        }

        /* ===== FROSTED LIQUID GLASS CONTAINER & CARDS ===== */
        .glass-erp-card {
            background: var(--glass-bg);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            box-shadow: var(--glass-shadow);
            transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .glass-erp-card:hover {
            border-color: var(--glass-border-hover);
            box-shadow: var(--glass-shadow-hover);
        }

        /* ===== EXECUTIVE TOP HEADER ===== */
        .erp-header-panel {
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 14px 20px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
            box-shadow: var(--glass-shadow);
        }

        .erp-greeting-block h1 {
            font-size: 19px;
            font-weight: 800;
            color: var(--arsikon-slate-dark);
            letter-spacing: -0.02em;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
            line-height: 1.25;
        }

        .erp-greeting-block p {
            font-size: 12px;
            color: var(--arsikon-slate-muted);
            margin: 3px 0 0;
            font-weight: 500;
        }


        /* ===== ROW 1: 4 TOP MODULAR METRIC CARDS ===== */
        .dash-kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 16px;
        }

        .kpi-erp-card {
            padding: 16px 18px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            text-decoration: none;
            color: inherit;
            min-height: 142px;
        }

        .kpi-erp-card:hover {
            color: inherit;
        }

        .kpi-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .kpi-title-label {
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: var(--arsikon-slate-muted);
        }

        .kpi-icon-lens {
            width: 28px;
            height: 28px;
            border-radius: 7px;
            background: rgba(15, 23, 42, 0.04);
            border: 1px solid rgba(15, 23, 42, 0.08);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--arsikon-slate-muted);
            flex-shrink: 0;
            transition: all 0.2s ease;
        }

        .kpi-erp-card:hover .kpi-icon-lens {
            background: var(--arsikon-amber);
            border-color: var(--arsikon-amber);
            color: #ffffff;
        }

        .kpi-primary-val {
            font-size: 27px;
            font-weight: 800;
            color: var(--arsikon-slate-dark);
            line-height: 1;
            letter-spacing: -0.025em;
            font-variant-numeric: tabular-nums;
            margin-bottom: 8px;
            display: flex;
            align-items: baseline;
            gap: 6px;
        }

        .kpi-primary-val .val-unit {
            font-size: 11.5px;
            font-weight: 600;
            color: var(--arsikon-slate-muted);
            letter-spacing: 0;
        }

        .kpi-badge-pills {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .badge-erp-pill {
            font-size: 10px;
            font-weight: 700;
            padding: 2.5px 8px;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            letter-spacing: 0.2px;
            line-height: 1.25;
            white-space: nowrap;
        }

        .badge-pill-danger {
            background: var(--arsikon-rose-soft);
            color: var(--arsikon-rose);
            border: 1px solid #fecaca;
        }

        .badge-pill-success {
            background: var(--arsikon-emerald-soft);
            color: var(--arsikon-emerald);
            border: 1px solid #a7f3d0;
        }

        .badge-pill-amber {
            background: var(--arsikon-amber-soft);
            color: var(--arsikon-amber-dark);
            border: 1px solid #fed7aa;
        }

        .badge-pill-navy {
            background: var(--arsikon-navy-soft);
            color: var(--arsikon-navy);
            border: 1px solid #bfdbfe;
        }

        .badge-pill-gray {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        /* SVG Donut Ring Component inside Card 4 */
        .donut-card-layout {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-top: 4px;
        }

        .donut-svg-wrap {
            position: relative;
            width: 76px;
            height: 76px;
            flex-shrink: 0;
        }

        .donut-svg-wrap svg {
            width: 100%;
            height: 100%;
            transform: rotate(-90deg);
        }

        .donut-center-stat {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            pointer-events: none;
        }

        .donut-center-stat .num {
            font-size: 13px;
            font-weight: 800;
            color: var(--arsikon-slate-dark);
            line-height: 1;
            font-variant-numeric: tabular-nums;
        }

        .donut-center-stat .lbl {
            font-size: 8.5px;
            color: var(--arsikon-slate-muted);
            font-weight: 600;
            text-transform: uppercase;
        }

        .donut-legend-list {
            display: flex;
            flex-direction: column;
            gap: 4px;
            flex: 1;
            min-width: 0;
        }

        .donut-legend-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 11px;
            gap: 6px;
        }

        .donut-legend-item .cat-name {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #334155;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .donut-legend-item .cat-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .donut-legend-item .cat-pct {
            font-weight: 700;
            color: var(--arsikon-slate-dark);
            font-variant-numeric: tabular-nums;
            flex-shrink: 0;
        }

        /* ===== ROW 2: OPERATIONAL ANALYTICS & FLEET STATUS ===== */
        .dash-grid-analytics {
            display: grid;
            grid-template-columns: 7fr 5fr;
            gap: 14px;
            margin-bottom: 16px;
        }

        .analytics-card-header {
            padding: 13px 18px;
            border-bottom: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(255, 255, 255, 0.45);
        }

        .analytics-card-title {
            font-size: 13px;
            font-weight: 800;
            color: var(--arsikon-slate-dark);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 7px;
            letter-spacing: -0.01em;
        }

        /* Pure SVG Mutation Spline Area Chart */
        .mutation-chart-container {
            padding: 16px 18px 12px;
            position: relative;
        }

        .mutation-chart-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
            flex-wrap: wrap;
            gap: 8px;
        }

        .chart-timeframe-tag {
            font-size: 10.5px;
            font-weight: 600;
            color: var(--arsikon-slate-muted);
            background: rgba(15, 23, 42, 0.04);
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 6px;
            padding: 2.5px 8px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .chart-legend-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 11px;
            font-weight: 600;
            color: var(--arsikon-slate-muted);
        }

        .chart-legend-wrap span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .chart-legend-bullet {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .svg-chart-wrapper {
            width: 100%;
            height: 160px;
        }

        .svg-chart-wrapper svg {
            width: 100%;
            height: 100%;
            overflow: visible;
        }

        .chart-empty-overlay {
            padding: 28px 14px;
            text-align: center;
            color: var(--arsikon-slate-muted);
            font-size: 12px;
            font-weight: 500;
        }

        /* Fleet Readiness Matrix Panel */
        .fleet-matrix-wrap {
            padding: 16px 18px;
        }

        .fleet-matrix-header {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .fleet-matrix-total {
            font-size: 20px;
            font-weight: 800;
            color: var(--arsikon-slate-dark);
            font-variant-numeric: tabular-nums;
            line-height: 1.2;
        }

        .fleet-matrix-total span {
            font-size: 11.5px;
            font-weight: 600;
            color: var(--arsikon-slate-muted);
        }

        .fleet-segmented-track {
            height: 8px;
            border-radius: 9999px;
            background: #f1f5f9;
            display: flex;
            overflow: hidden;
            margin-bottom: 14px;
            border: 1px solid rgba(226, 232, 240, 0.9);
        }

        .fleet-seg-item {
            height: 100%;
            transition: width 0.3s ease;
        }

        .fleet-status-table {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .fleet-status-card-row {
            padding: 8px 10px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(226, 232, 240, 0.8);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: background 0.15s ease, border-color 0.15s ease;
        }

        .fleet-status-card-row:hover {
            background: #ffffff;
            border-color: #cbd5e1;
        }

        .fleet-row-left {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .fleet-row-lens {
            width: 26px;
            height: 26px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            flex-shrink: 0;
        }

        .fleet-row-labels .title {
            font-size: 11.5px;
            font-weight: 700;
            color: var(--arsikon-slate-dark);
            line-height: 1.2;
        }

        .fleet-row-labels .desc {
            font-size: 10px;
            color: var(--arsikon-slate-muted);
        }

        .fleet-row-right {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .fleet-row-right .num {
            font-size: 13.5px;
            font-weight: 800;
            color: var(--arsikon-slate-dark);
        }

        .fleet-row-right .unit {
            font-size: 10px;
            color: var(--arsikon-slate-muted);
            font-weight: 600;
        }

        .fleet-row-right .pct {
            font-size: 10px;
            font-weight: 700;
            line-height: 1;
        }

        /* ===== ROW 3: TRIPLE OPERATIONAL STREAM ===== */
        .dash-grid-triad {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-bottom: 16px;
        }

        .stream-panel-header {
            padding: 12px 16px;
            border-bottom: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(255, 255, 255, 0.45);
        }

        .stream-panel-title {
            font-size: 12.5px;
            font-weight: 800;
            color: var(--arsikon-slate-dark);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .stream-table {
            width: 100%;
            border-collapse: collapse;
        }

        .stream-table th {
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: var(--arsikon-slate-muted);
            background: rgba(248, 250, 252, 0.7);
            padding: 8px 12px;
            border-bottom: 1px solid var(--glass-border);
            text-align: left;
        }

        .stream-table td {
            font-size: 11.5px;
            padding: 8px 12px;
            vertical-align: middle;
            border-bottom: 1px solid rgba(241, 245, 249, 0.8);
        }

        .stream-table tr:last-child td {
            border-bottom: none;
        }

        .stream-table tr:hover td {
            background: rgba(255, 255, 255, 0.6);
        }

        /* Alerts & Quick Actions List */
        .alert-feed-list {
            padding: 12px 14px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .alert-feed-item {
            padding: 8px 10px;
            border-radius: 8px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            font-size: 11px;
            line-height: 1.35;
        }

        .alert-feed-item.danger {
            background: var(--arsikon-rose-soft);
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .alert-feed-item.warning {
            background: var(--arsikon-gold-soft);
            border: 1px solid #fde68a;
            color: #92400e;
        }

        .alert-feed-item.success {
            background: var(--arsikon-emerald-soft);
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        .alert-feed-item svg {
            width: 14px;
            height: 14px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        /* Quick Actions Grid inside stream panel */
        .quick-actions-quad {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            padding: 10px 14px 14px;
            border-top: 1px solid var(--glass-border);
            background: rgba(248, 250, 252, 0.5);
        }

        .action-quad-btn {
            height: 34px;
            padding: 0 10px;
            border-radius: 7px;
            background: #ffffff;
            border: 1px solid var(--glass-border);
            font-size: 11px;
            font-weight: 700;
            color: var(--arsikon-slate-dark);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.15s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
            white-space: nowrap;
        }

        .action-quad-btn:hover {
            background: var(--arsikon-amber);
            border-color: var(--arsikon-amber);
            color: #ffffff;
            box-shadow: 0 3px 8px rgba(234, 88, 12, 0.25);
            transform: translateY(-1px);
        }

        .action-quad-btn svg {
            width: 13px;
            height: 13px;
            flex-shrink: 0;
        }

        /* ===== RESPONSIVENESS ===== */
        @media (max-width: 1200px) {
            .dash-kpi-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .dash-grid-analytics {
                grid-template-columns: 1fr;
            }
            .dash-grid-triad {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .erp-header-panel {
                flex-direction: column;
                align-items: stretch;
                padding: 12px 14px;
            }
            .erp-header-meta {
                flex-direction: column;
                align-items: stretch;
            }
            .dash-kpi-grid {
                grid-template-columns: 1fr;
            }
            .quick-actions-quad {
                grid-template-columns: 1fr;
            }
        }
    </style>
    @endpush

    @php
        $authUser = auth()->user();
        $activeWh = $authUser->activeWarehouse();
        if ($authUser->hasRole('Owner')) {
            $userRoleText = 'Owner / Direksi';
        } elseif ($authUser->hasRole('Admin Pusat')) {
            $userRoleText = 'Admin Pusat';
        } elseif ($authUser->hasRole('Admin Gudang Pusat')) {
            $userRoleText = 'Admin Gudang Pusat';
        } elseif ($authUser->hasRole('Admin Gudang Proyek')) {
            $userRoleText = 'Admin Gudang Proyek';
        } elseif ($authUser->hasRole('Admin PO')) {
            $userRoleText = 'Admin Pengadaan (PO)';
        } else {
            $userRoleText = $authUser->roles->first()?->name ?? 'Petugas Logistik';
        }

        // Tool Fleet Calculations
        $totalFleet = $toolsReady + $toolsInUse + $toolsMaintenance;
        $readyPct = $totalFleet > 0 ? round(($toolsReady / $totalFleet) * 100, 1) : 0;
        $inUsePct = $totalFleet > 0 ? round(($toolsInUse / $totalFleet) * 100, 1) : 0;
        $maintPct = $totalFleet > 0 ? round(($toolsMaintenance / $totalFleet) * 100, 1) : 0;

        // Inventory Category Donut Setup
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

    {{-- Breadcrumb Navigation --}}
    <div class="breadcrumb mb-2" style="font-size:12px;">
        <span class="text-primary fw-600"><x-lucide-home class="me-1 inline-block" style="width:13px;height:13px;" /> Beranda</span>
        <span class="breadcrumb-sep"><x-lucide-chevron-right class="inline-block" style="width:10px;height:10px;" /></span>
        <span>Dashboard Operasional</span>
    </div>

    {{-- Executive Header Panel --}}
    <div class="erp-header-panel">
        <div class="erp-greeting-block">
            <h1>
                <span>Welcome! {{ auth()->user()->name }}</span>
            </h1>
            <p>Pusat Komando Logistik, Material Proyek, dan Inventori Fisik Lapangan</p>
        </div>
    </div>

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
                    <span class="kpi-title-label">Armada Alat Kerja</span>
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
                {{-- Inline SVG Donut Chart --}}
                <div class="donut-svg-wrap">
                    <svg viewBox="0 0 100 100">
                        {{-- Background Track --}}
                        <circle cx="50" cy="50" r="38" fill="transparent" stroke="#f1f5f9" stroke-width="14" />

                        {{-- Category Slices --}}
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

                    {{-- Donut Inner Text --}}
                    <div class="donut-center-stat">
                        <div class="num">{{ number_format($totalCategoryQty) }}</div>
                        <div class="lbl">Unit</div>
                    </div>
                </div>

                {{-- Donut Legend --}}
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

                {{-- Pure SVG Area & Spline Curve --}}
                <div class="svg-chart-wrapper">
                    <svg viewBox="0 0 {{ $chartW }} {{ $chartH }}" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="arsikonSplineGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" stop-color="#ea580c" stop-opacity="0.28" />
                                <stop offset="80%" stop-color="#ea580c" stop-opacity="0.04" />
                                <stop offset="100%" stop-color="#ea580c" stop-opacity="0" />
                            </linearGradient>
                        </defs>

                        {{-- Horizontal Grid Lines --}}
                        <line x1="{{ $padX }}" y1="{{ $padY }}" x2="{{ $chartW - $padX }}" y2="{{ $padY }}" stroke="#e2e8f0" stroke-dasharray="3 3" stroke-width="1" />
                        <line x1="{{ $padX }}" y1="{{ round($padY + ($usableH / 2)) }}" x2="{{ $chartW - $padX }}" y2="{{ round($padY + ($usableH / 2)) }}" stroke="#e2e8f0" stroke-dasharray="3 3" stroke-width="1" />
                        <line x1="{{ $padX }}" y1="{{ $chartH - $padY }}" x2="{{ $chartW - $padX }}" y2="{{ $chartH - $padY }}" stroke="#cbd5e1" stroke-width="1.2" />

                        {{-- Grid Value Labels --}}
                        <text x="{{ $padX - 8 }}" y="{{ $padY + 4 }}" fill="#94a3b8" font-size="9" text-anchor="end" font-weight="600">{{ $maxMutationVal }}</text>
                        <text x="{{ $padX - 8 }}" y="{{ round($padY + ($usableH / 2)) + 4 }}" fill="#94a3b8" font-size="9" text-anchor="end" font-weight="600">{{ round($maxMutationVal / 2) }}</text>
                        <text x="{{ $padX - 8 }}" y="{{ $chartH - $padY + 3 }}" fill="#94a3b8" font-size="9" text-anchor="end" font-weight="600">0</text>

                        {{-- Area Fill --}}
                        <path d="{{ $areaSplinePath }}" fill="url(#arsikonSplineGrad)" />

                        {{-- Stroke Spline Curve --}}
                        <path d="{{ $splinePath }}" fill="none" stroke="#ea580c" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />

                        {{-- Data Points & Date Ticks --}}
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
                    Kesiapan Operasional Armada Alat
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

                {{-- Segmented Readiness Bar --}}
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

                {{-- Status Rows --}}
                <div class="fleet-status-table">
                    {{-- Row 1: Siap Pakai --}}
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

                    {{-- Row 2: Di Lapangan --}}
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

                    {{-- Row 3: Servis / Perbaikan --}}
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

            {{-- Alert Stream Feed --}}
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

            {{-- 4 Primary Quick Actions --}}
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
</x-app-layout>
