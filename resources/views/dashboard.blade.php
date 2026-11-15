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
        } elseif ($authUser->hasRole('Super Admin')) {
            $userRoleText = 'Super Admin';
        } elseif ($authUser->hasRole('Admin Pusat')) {
            $userRoleText = 'Admin Pusat';
        } elseif ($authUser->hasRole('Admin Gudang Pusat')) {
            $userRoleText = 'Admin Gudang Pusat';
        } elseif ($authUser->hasRole('Admin Gudang Proyek')) {
            $userRoleText = 'Admin Gudang Proyek';
        } elseif ($authUser->hasAnyRole(['Admin PO', 'Admin Pengadaan'])) {
            $userRoleText = 'Admin Pengadaan Pusat';
        } elseif ($authUser->hasAnyRole(['Admin Pengadaan Proyek', 'Admin PO Proyek', 'admin-po-nota', 'po_nota_harian'])) {
            $userRoleText = 'Admin PO Proyek';
        } else {
            $userRoleText = $authUser->roles->first()?->name ?? 'Petugas Logistik';
        }
    @endphp

    {{-- Breadcrumb Navigation --}}
    <div class="breadcrumb mb-2" style="font-size:12px;">
        <span class="text-primary fw-600"><x-lucide-home class="me-1 inline-block" style="width:13px;height:13px;" /> Beranda</span>
        <span class="breadcrumb-sep"><x-lucide-chevron-right class="inline-block" style="width:10px;height:10px;" /></span>
        <span>Dashboard Operasional</span>
    </div>

    {{-- Role Header Panel --}}
    <div class="erp-header-panel">
        <div class="erp-greeting-block">
            <h1>
                <span>Welcome! {{ auth()->user()->name }}</span>
                <span class="badge-erp-pill badge-pill-navy" style="font-size:11px;font-weight:700;padding:2px 8px;vertical-align:middle;">
                    {{ $userRoleText }}
                </span>
            </h1>
            <p>
                @if(($persona ?? '') === 'procurement_center')
                    Pusat Komando Pengadaan Vendor, PO Proyek, & Kontrak Suplier
                @elseif(($persona ?? '') === 'site_purchasing')
                    Pencatatan Pembelian Harian, Catatan Material & Sewa Alat Lapangan
                @elseif(($persona ?? '') === 'executive')
                    Ringkasan Eksekutif, Komitmen Pengadaan, & Portofolio Proyek
                @else
                    Pusat Komando Logistik, Material Proyek, dan Inventori Fisik Lapangan
                @endif
            </p>
        </div>
        <div class="erp-header-meta" style="display:flex;align-items:center;gap:12px;">
            <div style="font-size:12px;color:var(--arsikon-slate-muted);text-align:right;">
                <div style="font-weight:700;color:var(--arsikon-slate-dark);">{{ now()->translatedFormat('l, d F Y') }}</div>
                <div style="font-size:11px;">PT Arsikon Cipta Karya</div>
            </div>
        </div>
    </div>

    {{-- ROLE-ADAPTIVE DASHBOARD BODY --}}
    @if(($persona ?? '') === 'procurement_center')
        @include('dashboard.partials.procurement-center')
    @elseif(($persona ?? '') === 'site_purchasing')
        @include('dashboard.partials.site-purchasing')
    @elseif(($persona ?? '') === 'executive')
        @include('dashboard.partials.management-executive')
    @else
        @include('dashboard.partials.warehouse-logistics')
    @endif
</x-app-layout>
