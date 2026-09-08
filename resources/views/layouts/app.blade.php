<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'CWMS') }} — Arsikon Warehouse</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --sidebar-width: 260px;
            --topbar-height: 64px;
            --primary: #1e40af;
            --primary-light: #3b82f6;
            --accent: #f59e0b;
            --dark: #0f172a;
            --dark-2: #1e293b;
            --dark-3: #334155;
            --text-muted: #94a3b8;
            --border: #e2e8f0;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #06b6d4;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            display: flex;
            min-height: 100vh;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
            overflow-y: auto;
        }

        .sidebar-brand {
            padding: 20px 20px 16px;
            border-bottom: 1px solid rgba(255,255,255,0.07);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-brand .brand-icon {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: white;
            flex-shrink: 0;
        }

        .sidebar-brand .brand-text h1 {
            font-size: 14px;
            font-weight: 700;
            color: #f8fafc;
            line-height: 1.2;
        }

        .sidebar-brand .brand-text p {
            font-size: 10px;
            color: #64748b;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Workspace Switcher */
        .workspace-switcher {
            margin: 12px 16px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 10px;
            padding: 10px 12px;
        }

        .workspace-switcher label {
            font-size: 10px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: block;
            margin-bottom: 4px;
        }

        .workspace-switcher select {
            background: transparent;
            border: none;
            color: #e2e8f0;
            font-size: 13px;
            font-weight: 500;
            width: 100%;
            cursor: pointer;
            outline: none;
        }

        .workspace-switcher select option {
            background: #1e293b;
            color: #e2e8f0;
        }

        /* Nav Section */
        .nav-section {
            padding: 8px 12px 4px;
        }

        .nav-section-label {
            font-size: 10px;
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 0 8px;
            margin-bottom: 4px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: 8px;
            color: #94a3b8;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            transition: all 0.2s;
            margin-bottom: 1px;
            cursor: pointer;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }

        .nav-item i {
            width: 18px;
            text-align: center;
            font-size: 14px;
        }

        .nav-item:hover {
            background: rgba(59,130,246,0.12);
            color: #93c5fd;
        }

        .nav-item.active {
            background: linear-gradient(90deg, rgba(59,130,246,0.25), rgba(59,130,246,0.1));
            color: #60a5fa;
            border-left: 3px solid #3b82f6;
            padding-left: 9px;
        }

        .nav-item .badge {
            margin-left: auto;
            background: #ef4444;
            color: white;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
        }

        /* Sub-menu collapse */
        .nav-collapse .nav-item { padding-left: 38px; font-size: 13px; }

        .sidebar-footer {
            margin-top: auto;
            padding: 12px;
            border-top: 1px solid rgba(255,255,255,0.07);
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 10px;
            background: rgba(255,255,255,0.04);
        }

        .sidebar-user .avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            color: white;
            flex-shrink: 0;
        }

        .sidebar-user .user-info { flex: 1; min-width: 0; }
        .sidebar-user .user-name { font-size: 12.5px; font-weight: 600; color: #e2e8f0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sidebar-user .user-role { font-size: 10px; color: #64748b; }

        .btn-logout {
            color: #64748b;
            background: none;
            border: none;
            cursor: pointer;
            padding: 6px;
            border-radius: 6px;
            transition: all 0.2s;
            font-size: 14px;
        }

        .btn-logout:hover { color: #ef4444; background: rgba(239,68,68,0.1); }

        /* ===== MAIN CONTENT ===== */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ===== TOPBAR ===== */
        .topbar {
            height: var(--topbar-height);
            background: white;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            padding: 0 24px;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topbar-title {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            flex: 1;
        }

        .topbar-actions { display: flex; align-items: center; gap: 8px; }

        .topbar-btn {
            width: 38px; height: 38px;
            border: none;
            background: #f1f5f9;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #64748b;
            font-size: 15px;
            transition: all 0.2s;
            position: relative;
            text-decoration: none;
        }

        .topbar-btn:hover { background: #e2e8f0; color: #1e293b; }

        .topbar-btn .notif-dot {
            position: absolute;
            top: 6px; right: 6px;
            width: 8px; height: 8px;
            background: #ef4444;
            border-radius: 50%;
            border: 2px solid white;
        }

        /* ===== PAGE CONTENT ===== */
        .page-content {
            padding: 24px;
            flex: 1;
        }

        /* ===== CARDS ===== */
        .card {
            background: white;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .card-header {
            padding: 18px 22px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card-title {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            flex: 1;
        }

        .card-body { padding: 22px; }

        /* ===== STAT CARDS ===== */
        .stat-card {
            background: white;
            border-radius: 14px;
            padding: 20px;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: flex-start;
            gap: 16px;
            transition: all 0.25s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }

        .stat-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .stat-icon.blue   { background: rgba(59,130,246,0.12); color: #3b82f6; }
        .stat-icon.green  { background: rgba(16,185,129,0.12); color: #10b981; }
        .stat-icon.amber  { background: rgba(245,158,11,0.12); color: #f59e0b; }
        .stat-icon.red    { background: rgba(239,68,68,0.12);  color: #ef4444; }
        .stat-icon.purple { background: rgba(139,92,246,0.12); color: #8b5cf6; }
        .stat-icon.cyan   { background: rgba(6,182,212,0.12);  color: #06b6d4; }

        .stat-info .label { font-size: 12px; color: #94a3b8; font-weight: 500; margin-bottom: 4px; }
        .stat-info .value { font-size: 26px; font-weight: 800; color: #0f172a; line-height: 1; }
        .stat-info .sub   { font-size: 11.5px; color: #94a3b8; margin-top: 4px; }

        /* ===== BUTTONS ===== */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .btn-primary { background: #2563eb; color: white; }
        .btn-primary:hover { background: #1d4ed8; box-shadow: 0 4px 14px rgba(37,99,235,0.35); color: white; }
        .btn-success { background: #059669; color: white; }
        .btn-success:hover { background: #047857; color: white; }
        .btn-warning { background: #d97706; color: white; }
        .btn-warning:hover { background: #b45309; color: white; }
        .btn-danger { background: #dc2626; color: white; }
        .btn-danger:hover { background: #b91c1c; color: white; }
        .btn-secondary { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .btn-secondary:hover { background: #e2e8f0; color: #1e293b; }
        .btn-outline { background: transparent; border: 1.5px solid currentColor; }
        .btn-sm { padding: 5px 11px; font-size: 12px; }
        .btn-icon { padding: 8px; }

        /* ===== TABLES ===== */
        .table-wrap { overflow-x: auto; }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13.5px;
        }

        table.data-table th {
            background: #f8fafc;
            padding: 12px 14px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            border-bottom: 2px solid #e2e8f0;
            white-space: nowrap;
        }

        table.data-table td {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            vertical-align: middle;
        }

        table.data-table tr:last-child td { border-bottom: none; }
        table.data-table tr:hover td { background: #f8fafc; }

        /* ===== BADGES ===== */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 600;
        }

        .badge-success { background: rgba(16,185,129,0.12); color: #059669; }
        .badge-warning { background: rgba(245,158,11,0.12); color: #d97706; }
        .badge-danger  { background: rgba(239,68,68,0.12);  color: #dc2626; }
        .badge-info    { background: rgba(6,182,212,0.12);  color: #0891b2; }
        .badge-primary { background: rgba(37,99,235,0.12); color: #2563eb; }
        .badge-purple  { background: rgba(139,92,246,0.12); color: #7c3aed; }
        .badge-gray    { background: #f1f5f9; color: #64748b; }

        /* ===== FORMS ===== */
        .form-label {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 5px;
        }

        .form-control {
            width: 100%;
            padding: 9px 12px;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-size: 13.5px;
            color: #1e293b;
            background: white;
            transition: border-color 0.2s, box-shadow 0.2s;
            font-family: 'Inter', sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
        }

        .form-control.is-invalid { border-color: #ef4444; }
        .invalid-feedback { color: #dc2626; font-size: 12px; margin-top: 4px; }

        /* ===== GRID ===== */
        .grid { display: grid; gap: 16px; }
        .grid-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-4 { grid-template-columns: repeat(4, 1fr); }

        /* ===== ALERTS ===== */
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13.5px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 16px;
        }

        .alert-success { background: rgba(16,185,129,0.1); color: #065f46; border: 1px solid rgba(16,185,129,0.3); }
        .alert-warning { background: rgba(245,158,11,0.1); color: #78350f; border: 1px solid rgba(245,158,11,0.3); }
        .alert-danger  { background: rgba(239,68,68,0.1);  color: #7f1d1d; border: 1px solid rgba(239,68,68,0.3); }
        .alert-info    { background: rgba(6,182,212,0.1);  color: #164e63; border: 1px solid rgba(6,182,212,0.3); }

        /* ===== BREADCRUMB ===== */
        .breadcrumb { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #94a3b8; margin-bottom: 16px; }
        .breadcrumb a { color: #64748b; text-decoration: none; }
        .breadcrumb a:hover { color: #3b82f6; }
        .breadcrumb-sep { color: #cbd5e1; }

        /* ===== TABS ===== */
        .tabs { display: flex; gap: 2px; border-bottom: 2px solid #e2e8f0; margin-bottom: 20px; }
        .tab-btn {
            padding: 10px 18px;
            font-size: 13.5px;
            font-weight: 600;
            color: #64748b;
            background: none;
            border: none;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: all 0.2s;
        }
        .tab-btn.active { color: #2563eb; border-bottom-color: #2563eb; }
        .tab-btn:hover:not(.active) { color: #374151; }

        /* ===== MODAL ===== */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15,23,42,0.6);
            backdrop-filter: blur(4px);
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.show { display: flex; }

        .modal-box {
            background: white;
            border-radius: 16px;
            width: 90%;
            max-width: 520px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
            animation: modalIn 0.2s ease;
        }

        @keyframes modalIn {
            from { opacity: 0; transform: scale(0.95) translateY(-10px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }

        .modal-header {
            padding: 20px 22px 16px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .modal-title { font-size: 16px; font-weight: 700; color: #0f172a; flex: 1; }

        .modal-body { padding: 22px; }

        .modal-footer {
            padding: 16px 22px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .btn-close-modal { background: none; border: none; cursor: pointer; color: #94a3b8; font-size: 18px; }
        .btn-close-modal:hover { color: #1e293b; }

        /* ===== RESPONSIVE ===== */
        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 20px;
            color: #374151;
            padding: 4px;
        }

        @media (max-width: 1024px) {
            .mobile-toggle { display: flex; align-items: center; justify-content: center; }
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-wrapper { margin-left: 0; }
            .grid-4 { grid-template-columns: repeat(2, 1fr); }
            .grid-3 { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 640px) {
            .grid-2, .grid-3, .grid-4 { grid-template-columns: 1fr; }
            .page-content { padding: 16px; }
        }

        /* ===== SCROLLBAR ===== */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* ===== MISC UTILITIES ===== */
        .text-muted   { color: #94a3b8; }
        .text-success { color: #059669; }
        .text-danger  { color: #dc2626; }
        .text-warning { color: #d97706; }
        .text-primary { color: #2563eb; }
        .fw-600 { font-weight: 600; }
        .fw-700 { font-weight: 700; }
        .mt-1 { margin-top: 4px; }  .mt-2 { margin-top: 8px; }
        .mt-3 { margin-top: 12px; } .mt-4 { margin-top: 16px; }
        .mb-1 { margin-bottom: 4px; }  .mb-2 { margin-bottom: 8px; }
        .mb-3 { margin-bottom: 12px; } .mb-4 { margin-bottom: 16px; }
        .flex { display: flex; } .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .gap-1 { gap: 4px; } .gap-2 { gap: 8px; } .gap-3 { gap: 12px; }
        .w-full { width: 100%; }
        .empty-state { text-align: center; padding: 48px 24px; color: #94a3b8; }
        .empty-state i { font-size: 40px; margin-bottom: 12px; display: block; opacity: 0.4; }
        .empty-state h3 { font-size: 15px; font-weight: 600; color: #475569; margin-bottom: 6px; }
    </style>
</head>

<body>
    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon"><i class="fas fa-warehouse"></i></div>
            <div class="brand-text">
                <h1>Arsikon</h1>
                <p>Warehouse System</p>
            </div>
        </div>

        @php
            $warehouses = \App\Models\Warehouse::all();
            $activeWarehouseId = session('active_warehouse_id');
        @endphp

        @if($warehouses->count() > 1)
        <div class="workspace-switcher">
            <label>Workspace Aktif</label>
            <form method="POST" action="{{ route('workspace.switch') }}" id="workspaceForm">
                @csrf
                <select name="warehouse_id" onchange="document.getElementById('workspaceForm').submit()">
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ $activeWarehouseId == $wh->id ? 'selected' : '' }}>
                            {{ $wh->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
        @endif

        <nav style="flex:1; padding: 8px 0;">
            <!-- DASHBOARD -->
            <div class="nav-section">
                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-gauge-high"></i> Dashboard
                </a>
            </div>

            <!-- MASTER DATA -->
            @if(auth()->user()->hasAnyRole(['Owner', 'Admin']))
            <div class="nav-section">
                <div class="nav-section-label" style="padding: 8px 20px 4px;">Master Data</div>
                <a href="{{ route('materials.index') }}" class="nav-item {{ request()->routeIs('materials.*') ? 'active' : '' }}">
                    <i class="fas fa-boxes-stacked"></i> Material
                </a>
                <a href="{{ route('tools.index') }}" class="nav-item {{ request()->routeIs('tools.*') ? 'active' : '' }}">
                    <i class="fas fa-screwdriver-wrench"></i> Alat
                </a>
                <a href="{{ route('suppliers.index') }}" class="nav-item {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                    <i class="fas fa-truck"></i> Supplier
                </a>
                <a href="{{ route('categories.index') }}" class="nav-item {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <i class="fas fa-tags"></i> Kategori & Satuan
                </a>
            </div>
            @endif

            <!-- TRANSAKSI -->
            <div class="nav-section">
                <div class="nav-section-label" style="padding: 8px 20px 4px;">Transaksi</div>
                @if(auth()->user()->hasAnyRole(['Owner', 'Admin']))
                <a href="{{ route('goods-receipts.index') }}" class="nav-item {{ request()->routeIs('goods-receipts.*') ? 'active' : '' }}">
                    <i class="fas fa-truck-ramp-box"></i> Penerimaan Barang
                </a>
                @endif
                <a href="{{ route('material-requests.index') }}" class="nav-item {{ request()->routeIs('material-requests.*') ? 'active' : '' }}">
                    <i class="fas fa-file-circle-plus"></i> Permintaan Material
                </a>
                <a href="{{ route('distributions.index') }}" class="nav-item {{ request()->routeIs('distributions.*') ? 'active' : '' }}">
                    <i class="fas fa-right-left"></i> Distribusi
                </a>
            </div>

            <!-- ALAT & STOK -->
            <div class="nav-section">
                <div class="nav-section-label" style="padding: 8px 20px 4px;">Alat & Stok</div>
                <a href="{{ route('tool-assignments.index') }}" class="nav-item {{ request()->routeIs('tool-assignments.*') ? 'active' : '' }}">
                    <i class="fas fa-hand-holding"></i> Peminjaman Alat
                </a>
                <a href="{{ route('stock-opnames.index') }}" class="nav-item {{ request()->routeIs('stock-opnames.*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-check"></i> Stock Opname
                </a>
                <a href="{{ route('inventory.index') }}" class="nav-item {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                    <i class="fas fa-layer-group"></i> Inventori
                </a>
            </div>

            <!-- LAPORAN -->
            @if(auth()->user()->hasAnyRole(['Owner', 'Admin']))
            <div class="nav-section">
                <div class="nav-section-label" style="padding: 8px 20px 4px;">Laporan</div>
                <a href="{{ route('reports.index') }}" class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar"></i> Laporan
                </a>
                <a href="{{ route('audit-logs.index') }}" class="nav-item {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">
                    <i class="fas fa-shield-halved"></i> Audit Log
                </a>
            </div>
            @endif

            <!-- ADMIN -->
            @if(auth()->user()->hasAnyRole(['Owner', 'Admin']))
            <div class="nav-section">
                <div class="nav-section-label" style="padding: 8px 20px 4px;">Admin</div>
                <a href="{{ route('users.index') }}" class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i> Pengguna
                </a>
                <a href="{{ route('warehouses.index') }}" class="nav-item {{ request()->routeIs('warehouses.*') ? 'active' : '' }}">
                    <i class="fas fa-warehouse"></i> Gudang
                </a>
                <a href="{{ route('projects.index') }}" class="nav-item {{ request()->routeIs('projects.*') ? 'active' : '' }}">
                    <i class="fas fa-building-columns"></i> Proyek
                </a>
            </div>
            @endif
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
                <div class="user-info">
                    <div class="user-name">{{ auth()->user()->name }}</div>
                    <div class="user-role">{{ auth()->user()->roles->first()?->name ?? 'User' }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-logout" title="Logout">
                        <i class="fas fa-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- MAIN WRAPPER -->
    <div class="main-wrapper">
        <!-- TOPBAR -->
        <header class="topbar">
            <button class="mobile-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')">
                <i class="fas fa-bars"></i>
            </button>
            <div class="topbar-title">{{ $title ?? 'Dashboard' }}</div>
            <div class="topbar-actions">
                <a href="{{ route('notifications.index') }}" class="topbar-btn" title="Notifikasi">
                    <i class="fas fa-bell"></i>
                    @if(auth()->user()->unreadNotifications->count() > 0)
                    <span class="notif-dot"></span>
                    @endif
                </a>
                <a href="{{ route('profile.edit') }}" class="topbar-btn" title="Profil">
                    <i class="fas fa-user"></i>
                </a>
            </div>
        </header>

        <!-- PAGE CONTENT -->
        <main class="page-content">
            @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-circle-check"></i>
                <div>{{ session('success') }}</div>
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-circle-xmark"></i>
                <div>{{ session('error') }}</div>
            </div>
            @endif
            @if(session('warning'))
            <div class="alert alert-warning">
                <i class="fas fa-triangle-exclamation"></i>
                <div>{{ session('warning') }}</div>
            </div>
            @endif

            {{ $slot }}
        </main>
    </div>

    <script>
        // Close sidebar on outside click (mobile)
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            if (window.innerWidth <= 1024 && sidebar.classList.contains('open')) {
                if (!sidebar.contains(e.target) && !e.target.closest('.mobile-toggle')) {
                    sidebar.classList.remove('open');
                }
            }
        });

        // Auto-dismiss alerts
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(el => {
                el.style.transition = 'opacity 0.4s';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 400);
            });
        }, 4000);
    </script>

    @stack('scripts')
</body>
</html>
