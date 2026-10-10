<x-app-layout>
    <x-slot name="title">Inventori Gudang</x-slot>

    @push('styles')
    <style>
        /* ===== INVENTORY STYLESHEET ===== */
        .inv-page-header {
            margin-bottom: 20px;
        }

        /* KPI Quick Stats Cards */
        .inv-kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 22px;
        }
        .inv-kpi-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px 18px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            display: flex;
            align-items: center;
            gap: 14px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .inv-kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.06);
        }
        .inv-kpi-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .inv-kpi-icon.blue   { background: #eff6ff; color: #2563eb; }
        .inv-kpi-icon.amber  { background: #fffbeb; color: #d97706; }
        .inv-kpi-icon.red    { background: #fef2f2; color: #dc2626; }
        .inv-kpi-icon.emerald{ background: #f0fdf4; color: #16a34a; }

        .inv-kpi-meta .inv-kpi-num {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
            font-variant-numeric: tabular-nums;
        }
        .inv-kpi-meta .inv-kpi-title {
            font-size: 11.5px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 2px;
        }
        .inv-kpi-meta .inv-kpi-sub {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 2px;
        }

        /* Filter Panel */
        .inv-filter-panel {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            padding: 16px 20px;
            margin-bottom: 24px;
        }
        .inv-filter-label {
            display: block;
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #475569;
            margin-bottom: 6px;
        }
        .inv-input, .inv-select {
            height: 42px;
            border-radius: 8px;
            border: 1.5px solid #cbd5e1;
            background: #ffffff;
            font-size: 13px;
            color: #1e293b;
            padding: 0 14px;
            width: 100%;
            transition: all 0.2s ease;
        }
        .inv-input:focus, .inv-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
            outline: none;
        }
        .inv-select {
            cursor: pointer;
            padding-right: 32px;
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 15px;
        }
        .inv-btn-primary {
            height: 42px;
            padding: 0 18px;
            border-radius: 8px;
            background: #2563eb;
            color: #ffffff;
            font-size: 13px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(37,99,235,0.25);
            white-space: nowrap;
        }
        .inv-btn-primary:hover {
            background: #1d4ed8;
            box-shadow: 0 3px 8px rgba(37,99,235,0.35);
        }
        .inv-btn-secondary {
            height: 42px;
            padding: 0 15px;
            border-radius: 8px;
            background: #f8fafc;
            color: #475569;
            font-size: 13px;
            font-weight: 600;
            border: 1.5px solid #e2e8f0;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .inv-btn-secondary:hover {
            background: #f1f5f9;
            color: #0f172a;
            border-color: #cbd5e1;
        }

        /* Section Container & Headers */
        .inv-section-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            overflow: hidden;
            margin-bottom: 28px;
        }
        .inv-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 20px;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            flex-wrap: wrap;
            gap: 12px;
        }
        .inv-section-title-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .inv-section-badge-icon {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }
        .inv-section-badge-icon.mat {
            background: #eff6ff;
            color: #2563eb;
        }
        .inv-section-badge-icon.tool {
            background: #fffbeb;
            color: #d97706;
        }
        .inv-section-heading {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            line-height: 1.25;
        }
        .inv-section-sub {
            font-size: 12px;
            color: #64748b;
            margin: 0;
        }
        .inv-section-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .inv-btn-toggle-all {
            height: 30px;
            padding: 0 10px;
            font-size: 11.5px;
            font-weight: 600;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #475569;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.15s ease;
        }
        .inv-btn-toggle-all:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        /* Modern Table */
        .inv-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .inv-table thead th {
            background: #f8fafc;
            color: #64748b;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 12px 14px;
            border-bottom: 2px solid #e2e8f0;
            white-space: nowrap;
        }
        .inv-table thead th.center { text-align: center; }
        .inv-table thead th.right  { text-align: right; }

        /* Hierarchy Level 1: Category Header */
        .inv-group-cat {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            cursor: pointer;
            user-select: none;
            transition: background 0.15s ease;
        }
        .inv-group-cat:hover {
            background: #f1f5f9;
        }
        .inv-cat-cell {
            padding: 10px 18px !important;
        }
        .inv-cat-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .inv-cat-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .inv-cat-chev {
            font-size: 10px;
            color: #64748b;
            width: 16px;
            height: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s ease;
        }
        .inv-group-cat.collapsed .inv-cat-chev {
            transform: rotate(-90deg);
        }
        .inv-cat-title {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .inv-cat-count {
            font-size: 11px;
            font-weight: 600;
            color: #475569;
            background: #e2e8f0;
            padding: 2px 8px;
            border-radius: 12px;
        }

        /* Hierarchy Level 2: Subgroup (Kelompok Barang / Type) */
        .inv-group-sub {
            background: #fafbfc;
            border-bottom: 1px solid #f1f5f9;
            cursor: pointer;
            user-select: none;
            transition: background 0.15s ease;
        }
        .inv-group-sub:hover {
            background: #f4f6f8;
        }
        .inv-sub-cell {
            padding: 8px 18px 8px 36px !important;
            border-left: 3px solid #cbd5e1;
        }
        .inv-sub-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .inv-sub-left {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .inv-sub-chev {
            font-size: 9px;
            color: #94a3b8;
            width: 14px;
            height: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s ease;
        }
        .inv-group-sub.collapsed .inv-sub-chev {
            transform: rotate(-90deg);
        }
        .inv-sub-title {
            font-size: 12.5px;
            font-weight: 600;
            color: #334155;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .inv-sub-count {
            font-size: 11px;
            font-weight: 500;
            color: #64748b;
        }

        /* Hierarchy Level 3: Individual Item Rows */
        .inv-row-item {
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.15s ease;
        }
        .inv-row-item:hover {
            background: #f8fafc;
        }
        .inv-row-item td {
            padding: 11px 14px;
            vertical-align: middle;
        }

        /* Badges & Pills */
        .inv-sku-badge {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 11px;
            font-weight: 600;
            color: #334155;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 2px 7px;
            border-radius: 5px;
            letter-spacing: 0.02em;
            display: inline-block;
        }
        .inv-wh-badge {
            display: inline-flex;
            align-items: center;
            gap: 5.5px;
            padding: 3.5px 9px;
            border-radius: 6px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            font-size: 11.5px;
            font-weight: 600;
            color: #334155;
            white-space: nowrap;
        }
        .inv-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11.5px;
            font-weight: 600;
            padding: 3px 9px;
            border-radius: 6px;
            white-space: nowrap;
        }
        .inv-status-pill.green {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .inv-status-pill.amber {
            background: #fffbeb;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .inv-status-pill.red {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .inv-stock-num {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            font-variant-numeric: tabular-nums;
        }
        .inv-unit-lbl {
            font-size: 11.5px;
            color: #64748b;
            font-weight: 500;
        }

        .action-btn-view {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #2563eb;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.15s ease;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        }
        .action-btn-view:hover {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
            box-shadow: 0 2px 6px rgba(37,99,235,0.28);
        }

        /* Empty State */
        .inv-empty-state {
            padding: 48px 20px;
            text-align: center;
        }
        .inv-empty-icon {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: #f1f5f9;
            color: #94a3b8;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-bottom: 12px;
        }
        .inv-empty-title {
            font-size: 14px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 4px;
        }
        .inv-empty-sub {
            font-size: 12.5px;
            color: #94a3b8;
            max-width: 340px;
            margin: 0 auto;
        }
    </style>
    @endpush

    @php
        // Summary KPI Computations
        $totalMatVariants = $categoriesData->sum(fn($c) => $c->materials->count());
        $totalMatStock = $categoriesData->sum(fn($c) => $c->materials->sum(fn($m) => $m->inventories->sum('quantity')));
        $totalLowStockMat = $categoriesData->sum(fn($c) => $c->materials->filter(fn($m) => $m->inventories->contains(fn($i) => $i->quantity <= $i->min_stock))->count());

        $totalToolsCount = $toolsCategoriesData->sum(fn($c) => $c->tools->count());
        $totalToolStockTotal = $toolsCategoriesData->sum(fn($c) => $c->tools->sum('stock_total'));
        $totalToolAvailable = $toolsCategoriesData->sum(fn($c) => $c->tools->sum('stock_available'));
        $totalToolBorrowed = $toolsCategoriesData->sum(fn($c) => $c->tools->sum('stock_borrowed'));
        $totalToolLow = $toolsCategoriesData->sum(fn($c) => $c->tools->filter(fn($t) => $t->stock_available <= 2)->count());

        $currentWhObj = is_numeric($selectedWarehouseId ?? null) ? $accessibleWarehouses->firstWhere('id', (int)$selectedWarehouseId) : null;
    @endphp

    {{-- Page Header --}}
    <div class="inv-page-header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <div class="flex items-center gap-2.5">
                    <h2 class="fw-700" style="font-size:21px;color:#0f172a;margin:0;letter-spacing:-0.01em;">Inventori Gudang</h2>
                    @if($currentWhObj)
                        <span class="inv-wh-badge" title="Gudang Terpilih: {{ $currentWhObj->name }}">
                            <i class="fas fa-warehouse text-primary" style="font-size:11px;"></i> {{ $currentWhObj->name }}{{ $currentWhObj->is_central ? ' (Pusat)' : ' (Proyek)' }}
                        </span>
                    @elseif(($selectedWarehouseId ?? null) === 'all')
                        <span class="inv-wh-badge" style="background:#eff6ff;color:#1e40af;border-color:#bfdbfe;">
                            <i class="fas fa-boxes-stacked text-primary" style="font-size:11px;"></i> Semua Gudang (Konsolidasi)
                        </span>
                    @endif
                </div>
                <p class="text-muted" style="font-size:12.5px;margin:3px 0 0 0;">Monitoring posisi stok material dan alat kerja secara terpadu di seluruh site gudang</p>
            </div>
        </div>
    </div>

    {{-- KPI Summary Stats Cards --}}
    <div class="inv-kpi-grid">
        {{-- Card 1: Material --}}
        <div class="inv-kpi-card">
            <div class="inv-kpi-icon blue">
                <i class="fas fa-cubes"></i>
            </div>
            <div class="inv-kpi-meta">
                <div class="inv-kpi-title">Material Konstruksi</div>
                <div class="inv-kpi-num">{{ number_format($totalMatVariants, 0, ',', '.') }} <span style="font-size:12px;font-weight:500;color:#64748b;">varian</span></div>
                <div class="inv-kpi-sub">{{ number_format($totalMatStock, 0, ',', '.') }} total unit tercatat</div>
            </div>
        </div>

        {{-- Card 2: Alat Kerja --}}
        <div class="inv-kpi-card">
            <div class="inv-kpi-icon amber">
                <i class="fas fa-toolbox"></i>
            </div>
            <div class="inv-kpi-meta">
                <div class="inv-kpi-title">Alat Kerja</div>
                <div class="inv-kpi-num">{{ number_format($totalToolsCount, 0, ',', '.') }} <span style="font-size:12px;font-weight:500;color:#64748b;">jenis</span></div>
                <div class="inv-kpi-sub">{{ number_format($totalToolAvailable, 0, ',', '.') }} unit siap pakai ({{ number_format($totalToolBorrowed, 0, ',', '.') }} dipinjam)</div>
            </div>
        </div>

        {{-- Card 3: Alert Stok --}}
        <div class="inv-kpi-card">
            <div class="inv-kpi-icon {{ ($totalLowStockMat + $totalToolLow) > 0 ? 'red' : 'emerald' }}">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <div class="inv-kpi-meta">
                <div class="inv-kpi-title">Peringatan Stok</div>
                <div class="inv-kpi-num" style="{{ ($totalLowStockMat + $totalToolLow) > 0 ? 'color:#dc2626;' : 'color:#16a34a;' }}">
                    {{ number_format($totalLowStockMat + $totalToolLow, 0, ',', '.') }} <span style="font-size:12px;font-weight:500;color:#64748b;">item</span>
                </div>
                <div class="inv-kpi-sub">
                    @if(($totalLowStockMat + $totalToolLow) > 0)
                        {{ $totalLowStockMat }} material &bull; {{ $totalToolLow }} alat menipis/habis
                    @else
                        Semua level stok aman terkendali
                    @endif
                </div>
            </div>
        </div>

        {{-- Card 4: Cakupan Gudang --}}
        <div class="inv-kpi-card">
            <div class="inv-kpi-icon emerald">
                <i class="fas fa-warehouse"></i>
            </div>
            <div class="inv-kpi-meta">
                <div class="inv-kpi-title">Cakupan Gudang</div>
                <div class="inv-kpi-num" style="font-size:15px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px;" title="{{ $currentWhObj ? $currentWhObj->name : 'Semua Gudang' }}">
                    {{ $currentWhObj ? $currentWhObj->name : 'Semua Gudang' }}
                </div>
                <div class="inv-kpi-sub">{{ $currentWhObj ? ($currentWhObj->is_central ? 'Gudang Sentral Arsikon' : 'Site Gudang Proyek') : 'Mode Konsolidasi Multi-Gudang' }}</div>
            </div>
        </div>
    </div>

    {{-- Filter Panel --}}
    <div class="inv-filter-panel">
        <form method="GET" action="{{ route('inventory.index') }}" id="inventoryFilterForm" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
            {{-- Kolom Pencarian --}}
            <div style="flex:2;min-width:260px;">
                <label class="inv-filter-label">Pencarian Item</label>
                <div style="position:relative;">
                    <i class="fas fa-search" style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:12.5px;color:#94a3b8;pointer-events:none;"></i>
                    <input type="text" name="search" value="{{ request('search') }}" class="inv-input"
                        placeholder="Cari nama barang, kode SKU/Alat, atau spesifikasi..."
                        style="padding-left:36px;">
                </div>
            </div>

            {{-- Kolom Lokasi Gudang --}}
            <div style="flex:1.2;min-width:220px;">
                <label class="inv-filter-label">Lokasi Gudang</label>
                <select name="warehouse_id" id="filter_warehouse_id" class="inv-select" onchange="this.form.submit()">
                    @if(!empty($canViewAllWarehouses))
                        <option value="all" {{ ($selectedWarehouseId === 'all') ? 'selected' : '' }}>
                            &#127981; Semua Gudang (Konsolidasi)
                        </option>
                    @endif
                    @foreach($accessibleWarehouses as $wh)
                        <option value="{{ $wh->id }}" {{ (string)$selectedWarehouseId === (string)$wh->id ? 'selected' : '' }}>
                            {{ $wh->name }} {{ $wh->is_central ? '(Pusat)' : '(Proyek)' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Kolom Jenis Item --}}
            <div style="flex:1;min-width:170px;">
                <label class="inv-filter-label">Jenis Item</label>
                <select name="item_type" id="filter_item_type" class="inv-select" onchange="this.form.submit()">
                    <option value="">Semua (Material & Alat)</option>
                    <option value="material" {{ request('item_type') === 'material' ? 'selected' : '' }}>Material Konstruksi</option>
                    <option value="tool" {{ request('item_type') === 'tool' ? 'selected' : '' }}>Alat Kerja</option>
                </select>
            </div>

            {{-- Kolom Filter Stok Level --}}
            <div style="flex:1;min-width:150px;">
                <label class="inv-filter-label">Status Stok</label>
                <select name="stock_level" id="filter_stock_level" class="inv-select" onchange="this.form.submit()">
                    <option value="">Semua Kondisi</option>
                    <option value="low" {{ request('stock_level') === 'low' ? 'selected' : '' }}>Menipis (Min. Stok)</option>
                    <option value="out" {{ request('stock_level') === 'out' ? 'selected' : '' }}>Stok Habis (0)</option>
                </select>
            </div>

            {{-- Tombol Aksi --}}
            <div style="display:flex;gap:8px;align-items:center;">
                <button type="submit" class="inv-btn-primary" title="Terapkan Filter">
                    <i class="fas fa-filter" style="font-size:11px;"></i> Filter
                </button>
                @if(request('search') || request('item_type') || request('stock_level') || (request()->has('warehouse_id') && request('warehouse_id') != (auth()->user()->activeWarehouse()?->id ?? 'all')))
                <a href="{{ route('inventory.index') }}" class="inv-btn-secondary" title="Reset Semua Filter">
                    <i class="fas fa-rotate-left" style="font-size:11px;"></i> Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- TABEL INVENTORI: MATERIAL --}}
    @if(!$itemType || $itemType === 'material')
    <div class="inv-section-box">
        <div class="inv-section-header">
            <div class="inv-section-title-wrap">
                <div class="inv-section-badge-icon mat">
                    <i class="fas fa-cubes"></i>
                </div>
                <div>
                    <h3 class="inv-section-heading">Inventori Material Konstruksi</h3>
                    <p class="inv-section-sub">Daftar stok material bahan bangunan & consumable di gudang</p>
                </div>
            </div>
            <div class="inv-section-actions">
                <span class="inv-cat-count" style="margin-right:6px;">
                    {{ $totalMatVariants }} varian terdaftar
                </span>
                <button type="button" class="inv-btn-toggle-all" onclick="toggleAllGroups('mat', true)" title="Buka seluruh kelompok">
                    <i class="fas fa-angles-down" style="font-size:10px;"></i> Buka Semua
                </button>
                <button type="button" class="inv-btn-toggle-all" onclick="toggleAllGroups('mat', false)" title="Tutup seluruh kelompok">
                    <i class="fas fa-angles-up" style="font-size:10px;"></i> Tutup Semua
                </button>
            </div>
        </div>

        <div class="table-wrap">
            <table class="inv-table mb-0" id="table-material-inventory">
                <thead>
                    <tr>
                        <th style="padding-left:18px;min-width:240px;">Material & SKU</th>
                        <th style="min-width:160px;">Kelompok & Spesifikasi</th>
                        <th style="min-width:120px;">Kategori</th>
                        <th style="min-width:170px;">Gudang Penyimpanan</th>
                        <th class="center" style="min-width:120px;">Stok Saat Ini</th>
                        <th class="center" style="min-width:100px;">Min. Stok</th>
                        <th class="center" style="min-width:110px;">Status</th>
                        <th class="center" style="min-width:105px;">Terakhir Update</th>
                        <th class="center" style="width:70px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categoriesData as $category)
                    @php
                        $materialsWithInv = $category->materials->filter(fn($m) => $m->inventories->isNotEmpty());
                        $typeGroups = $materialsWithInv->groupBy(function($m) {
                            if (!empty($m->type)) {
                                return $m->type;
                            }
                            if (!empty($m->size) && str_ends_with($m->name, $m->size)) {
                                $inferred = trim(substr($m->name, 0, -strlen($m->size)));
                                if (!empty($inferred)) return $inferred;
                            }
                            return $m->category?->name ?? 'Umum';
                        });
                        $catMaterialCount = $materialsWithInv->count();
                    @endphp
                    @if($materialsWithInv->isNotEmpty())
                    {{-- Level 1: Category Header --}}
                    <tr class="inv-group-cat group-toggle group-toggle-mat" data-group="group-mat-cat-{{ $category->id }}">
                        <td colspan="9" class="inv-cat-cell">
                            <div class="inv-cat-inner">
                                <div class="inv-cat-left">
                                    <span class="inv-cat-chev group-chev"><i class="fas fa-chevron-down"></i></span>
                                    <i class="fas fa-folder text-warning" style="font-size:13px;"></i>
                                    <span class="inv-cat-title">{{ $category->name }}</span>
                                    <span class="inv-cat-count">{{ $catMaterialCount }} item</span>
                                </div>
                                <span style="font-size:11.5px;color:#94a3b8;font-weight:500;">
                                    Klik untuk membuka / menutup
                                </span>
                            </div>
                        </td>
                    </tr>

                    {{-- Level 2: Sub-Group Header (Kelompok Barang / Type) --}}
                    @foreach($typeGroups as $typeName => $materialsInType)
                    @php $subKey = 'sub-mat-' . $category->id . '-' . Str::slug($typeName); @endphp
                    <tr class="group-rows group-mat-cat-{{ $category->id }} subgroup-toggle subgroup-toggle-mat inv-group-sub" data-group="{{ $subKey }}">
                        <td colspan="9" class="inv-sub-cell">
                            <div class="inv-sub-inner">
                                <div class="inv-sub-left">
                                    <span class="inv-sub-chev subgroup-chev"><i class="fas fa-chevron-down"></i></span>
                                    <span class="inv-sub-title">
                                        <i class="fas fa-layer-group text-primary" style="font-size:11px;"></i>
                                        {{ $typeName }}
                                    </span>
                                    <span class="inv-sub-count">({{ $materialsInType->count() }} varian)</span>
                                </div>
                            </div>
                        </td>
                    </tr>

                    {{-- Level 3: Individual Inventory Material Rows --}}
                    @foreach($materialsInType as $material)
                        @foreach($material->inventories as $inv)
                        <tr class="group-rows group-mat-cat-{{ $category->id }} subgroup-rows {{ $subKey }} inv-row-item">
                            <td style="padding-left:18px;">
                                <div class="fw-600" style="color:#0f172a;font-size:13px;line-height:1.35;">{{ $material->name }}</div>
                                <div style="margin-top:3.5px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                    <span class="inv-sku-badge">{{ $material->sku ?? '-' }}</span>
                                    @if($material->brand)
                                    <span class="text-muted" style="font-size:11.5px;">
                                        &bull; Merek: <strong style="color:#475569;">{{ $material->brand }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="fw-500" style="color:#334155;">{{ $material->type ?: '-' }}</span>
                                @if($material->size)
                                <span class="text-muted" style="font-size:11.5px;"> &bull; {{ $material->size }}</span>
                                @endif
                            </td>
                            <td style="color:#64748b;font-size:12.5px;">
                                {{ $category->name }}
                            </td>
                            <td>
                                <span class="inv-wh-badge">
                                    <i class="fas fa-warehouse text-primary" style="font-size:10px;"></i>
                                    {{ $inv->warehouse?->name ?? '-' }}
                                </span>
                            </td>
                            <td class="center">
                                <span class="inv-stock-num">{{ number_format($inv->quantity, 0, ',', '.') }}</span>
                                <span class="inv-unit-lbl">{{ $material->unit?->abbreviation ?? $material->unit?->name }}</span>
                            </td>
                            <td class="center" style="color:#64748b;font-variant-numeric:tabular-nums;">
                                {{ number_format($inv->min_stock, 0, ',', '.') }}
                            </td>
                            <td class="center">
                                @if($inv->quantity <= 0)
                                    <span class="inv-status-pill red"><i class="fas fa-circle-xmark"></i> Habis</span>
                                @elseif($inv->quantity <= $inv->min_stock)
                                    <span class="inv-status-pill amber"><i class="fas fa-triangle-exclamation"></i> Menipis</span>
                                @else
                                    <span class="inv-status-pill green"><i class="fas fa-circle-check"></i> Normal</span>
                                @endif
                            </td>
                            <td class="center text-muted" style="font-size:12px;white-space:nowrap;">
                                {{ $inv->updated_at ? $inv->updated_at->format('d/m/Y') : '-' }}
                            </td>
                            <td class="center">
                                <a href="{{ route('inventory.show', $inv) }}" class="action-btn-view" title="Detail Riwayat Stok">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    @endforeach
                    @endforeach
                    @endif
                    @empty
                    <tr>
                        <td colspan="9" class="inv-empty-state">
                            <div class="inv-empty-icon">
                                <i class="fas fa-boxes-stacked"></i>
                            </div>
                            <div class="inv-empty-title">Tidak Ada Data Material</div>
                            <div class="inv-empty-sub">Tidak ditemukan catatan inventori material yang sesuai dengan kriteria filter saat ini.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- TABEL INVENTORI: ALAT KERJA --}}
    @if(!$itemType || $itemType === 'tool')
    <div class="inv-section-box">
        <div class="inv-section-header">
            <div class="inv-section-title-wrap">
                <div class="inv-section-badge-icon tool">
                    <i class="fas fa-toolbox"></i>
                </div>
                <div>
                    <h3 class="inv-section-heading">Inventori Alat Kerja & Peralatan</h3>
                    <p class="inv-section-sub">Daftar kesiapan alat kerja, posisi unit, dan status peminjaman proyek</p>
                </div>
            </div>
            <div class="inv-section-actions">
                <span class="inv-cat-count" style="margin-right:6px;">
                    {{ $totalToolsCount }} alat terdaftar
                </span>
                <button type="button" class="inv-btn-toggle-all" onclick="toggleAllGroups('tool', true)" title="Buka seluruh kelompok">
                    <i class="fas fa-angles-down" style="font-size:10px;"></i> Buka Semua
                </button>
                <button type="button" class="inv-btn-toggle-all" onclick="toggleAllGroups('tool', false)" title="Tutup seluruh kelompok">
                    <i class="fas fa-angles-up" style="font-size:10px;"></i> Tutup Semua
                </button>
            </div>
        </div>

        <div class="table-wrap">
            <table class="inv-table mb-0" id="table-tool-inventory">
                <thead>
                    <tr>
                        <th style="padding-left:18px;min-width:240px;">Nama Alat & Kode</th>
                        <th style="min-width:160px;">Merk & Spesifikasi</th>
                        <th style="min-width:120px;">Kategori</th>
                        <th style="min-width:170px;">Gudang / Lokasi</th>
                        <th class="center" style="min-width:110px;">Total Unit</th>
                        <th class="center" style="min-width:110px;">Tersedia</th>
                        <th class="center" style="min-width:100px;">Dipinjam</th>
                        <th class="center" style="min-width:110px;">Status</th>
                        <th class="center" style="width:70px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($toolsCategoriesData as $toolCategory)
                    @php
                        $toolTypeGroups = $toolCategory->tools->groupBy(function($t) {
                            if (!empty($t->type)) {
                                return $t->type;
                            }
                            if (!empty($t->size) && str_ends_with($t->name, $t->size)) {
                                $inferred = trim(substr($t->name, 0, -strlen($t->size)));
                                if (!empty($inferred)) return $inferred;
                            }
                            return $t->category?->name ?? 'Umum';
                        });
                        $catToolCount = $toolCategory->tools->count();
                    @endphp
                    {{-- Level 1: Tool Category Header --}}
                    <tr class="inv-group-cat group-toggle group-toggle-tool" data-group="group-tool-cat-{{ $toolCategory->id }}">
                        <td colspan="9" class="inv-cat-cell">
                            <div class="inv-cat-inner">
                                <div class="inv-cat-left">
                                    <span class="inv-cat-chev group-chev"><i class="fas fa-chevron-down"></i></span>
                                    <i class="fas fa-folder text-warning" style="font-size:13px;"></i>
                                    <span class="inv-cat-title">{{ $toolCategory->name }}</span>
                                    <span class="inv-cat-count">{{ $catToolCount }} alat</span>
                                </div>
                                <span style="font-size:11.5px;color:#94a3b8;font-weight:500;">
                                    Klik untuk membuka / menutup
                                </span>
                            </div>
                        </td>
                    </tr>

                    {{-- Level 2: Sub-Group Header (Kelompok Alat / Type) --}}
                    @foreach($toolTypeGroups as $toolTypeName => $toolsInType)
                    @php $subToolKey = 'sub-tool-' . $toolCategory->id . '-' . Str::slug($toolTypeName); @endphp
                    <tr class="group-rows group-tool-cat-{{ $toolCategory->id }} subgroup-toggle subgroup-toggle-tool inv-group-sub" data-group="{{ $subToolKey }}">
                        <td colspan="9" class="inv-sub-cell" style="border-left-color:#f97316;">
                            <div class="inv-sub-inner">
                                <div class="inv-sub-left">
                                    <span class="inv-sub-chev subgroup-chev"><i class="fas fa-chevron-down"></i></span>
                                    <span class="inv-sub-title">
                                        <i class="fas fa-layer-group text-warning" style="font-size:11px;"></i>
                                        {{ $toolTypeName }}
                                    </span>
                                    <span class="inv-sub-count">({{ $toolsInType->count() }} item)</span>
                                </div>
                            </div>
                        </td>
                    </tr>

                    {{-- Level 3: Individual Tool Rows --}}
                    @foreach($toolsInType as $tool)
                    <tr class="group-rows group-tool-cat-{{ $toolCategory->id }} subgroup-rows {{ $subToolKey }} inv-row-item">
                        <td style="padding-left:18px;">
                            <div class="fw-600" style="color:#0f172a;font-size:13px;line-height:1.35;">{{ $tool->name }}</div>
                            <div style="margin-top:3.5px;">
                                <span class="inv-sku-badge">{{ $tool->code ?? '-' }}</span>
                            </div>
                        </td>
                        <td>
                            @if($tool->brand)
                            <span class="fw-500" style="color:#334155;">{{ $tool->brand }}</span>
                            @endif
                            @if($tool->size)
                            <span class="text-muted" style="font-size:11.5px;"> &bull; {{ $tool->size }}</span>
                            @elseif(!$tool->brand)
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td style="color:#64748b;font-size:12.5px;">
                            {{ $toolCategory->name }}
                        </td>
                        <td>
                            @php
                                $whName = ($tool->relationLoaded('inventories') && $tool->inventories->isNotEmpty())
                                    ? ($tool->inventories->first()->warehouse?->name ?? '-')
                                    : ($tool->currentWarehouse?->name ?? 'Gudang Pusat');
                            @endphp
                            <span class="inv-wh-badge">
                                <i class="fas fa-warehouse text-primary" style="font-size:10px;"></i>
                                {{ $whName }}
                            </span>
                        </td>
                        <td class="center">
                            <span class="inv-stock-num">{{ number_format($tool->stock_total, 0, ',', '.') }}</span>
                            <span class="inv-unit-lbl">unit</span>
                        </td>
                        <td class="center">
                            <span class="inv-stock-num" style="color:#16a34a;">{{ number_format($tool->stock_available, 0, ',', '.') }}</span>
                            <span class="inv-unit-lbl">unit</span>
                        </td>
                        <td class="center" style="color:#64748b;font-variant-numeric:tabular-nums;">
                            {{ number_format($tool->stock_borrowed, 0, ',', '.') }}
                        </td>
                        <td class="center">
                            @if($tool->stock_available > 0)
                                <span class="inv-status-pill green"><i class="fas fa-circle-check"></i> Siap Pakai</span>
                            @else
                                <span class="inv-status-pill red"><i class="fas fa-circle-xmark"></i> Kosong / Dipinjam</span>
                            @endif
                        </td>
                        <td class="center">
                            <a href="{{ route('tools.show', $tool) }}" class="action-btn-view" title="Detail Riwayat Alat">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                    @endforeach
                    @empty
                    <tr>
                        <td colspan="9" class="inv-empty-state">
                            <div class="inv-empty-icon">
                                <i class="fas fa-toolbox"></i>
                            </div>
                            <div class="inv-empty-title">Tidak Ada Data Alat Kerja</div>
                            <div class="inv-empty-sub">Tidak ditemukan catatan inventori alat kerja yang sesuai dengan kriteria filter saat ini.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Level 1: Toggle Category Group
            var toggleRows = document.querySelectorAll('.group-toggle');
            toggleRows.forEach(function (row) {
                row.addEventListener('click', function (e) {
                    if (e.target.closest('.btn') || e.target.closest('a') || e.target.closest('.action-btn-view')) return;
                    var group = row.getAttribute('data-group');
                    var isCatCollapsed = row.classList.toggle('collapsed');
                    var targetRows = document.querySelectorAll('.' + group);

                    targetRows.forEach(function (r) {
                        if (isCatCollapsed) {
                            r.style.display = 'none';
                        } else {
                            if (r.classList.contains('subgroup-toggle')) {
                                r.style.display = '';
                            } else if (r.classList.contains('subgroup-rows')) {
                                var subKey = null;
                                r.classList.forEach(function (cls) {
                                    if (cls.startsWith('sub-')) subKey = cls;
                                });
                                var subToggle = subKey ? document.querySelector('.subgroup-toggle[data-group="' + subKey + '"]') : null;
                                if (!subToggle || !subToggle.classList.contains('collapsed')) {
                                    r.style.display = '';
                                } else {
                                    r.style.display = 'none';
                                }
                            } else {
                                r.style.display = '';
                            }
                        }
                    });
                });
            });

            // Level 2: Toggle Subgroup (Kelompok Barang / Type)
            var subToggleRows = document.querySelectorAll('.subgroup-toggle');
            subToggleRows.forEach(function (subRow) {
                subRow.addEventListener('click', function (e) {
                    if (e.target.closest('.btn') || e.target.closest('a') || e.target.closest('.action-btn-view')) return;
                    var subKey = subRow.getAttribute('data-group');
                    var isSubCollapsed = subRow.classList.toggle('collapsed');
                    var childRows = document.querySelectorAll('.' + subKey);

                    childRows.forEach(function (r) {
                        r.style.display = isSubCollapsed ? 'none' : '';
                    });
                });
            });
        });

        // Global Expand / Collapse All Helper
        function toggleAllGroups(section, expand) {
            var catToggles = document.querySelectorAll('.group-toggle-' + section);
            var subToggles = document.querySelectorAll('.subgroup-toggle-' + section);
            var allSectionRows = document.querySelectorAll('#table-' + (section === 'mat' ? 'material' : 'tool') + '-inventory tbody tr');

            catToggles.forEach(function(cat) {
                if (expand) {
                    cat.classList.remove('collapsed');
                } else {
                    cat.classList.add('collapsed');
                }
            });

            subToggles.forEach(function(sub) {
                if (expand) {
                    sub.classList.remove('collapsed');
                } else {
                    sub.classList.add('collapsed');
                }
            });

            allSectionRows.forEach(function(row) {
                if (row.classList.contains('group-toggle')) {
                    row.style.display = '';
                } else if (row.classList.contains('subgroup-toggle')) {
                    row.style.display = expand ? '' : 'none';
                } else if (row.classList.contains('subgroup-rows')) {
                    row.style.display = expand ? '' : 'none';
                }
            });
        }
    </script>
    @endpush
</x-app-layout>
