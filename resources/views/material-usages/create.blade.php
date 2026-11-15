<x-app-layout>
    <x-slot name="title">Catat Pengeluaran Material</x-slot>

    @push('styles')
    <style>
        .material-usage-layout {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 16px;
            align-items: start;
        }

        .mode-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .mode-select-box {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 12px;
            background: #ffffff;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .mode-select-box:hover {
            border-color: #93c5fd;
            background: #fbfdff;
        }

        .mode-select-box.active {
            border-color: #2563eb;
            background: #eff6ff;
            box-shadow: 0 0 0 1px #2563eb;
        }

        .mode-radio {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            border: 2px solid #cbd5e1;
            display: inline-block;
            transition: all 0.15s;
            flex-shrink: 0;
        }

        .mode-select-box.active .mode-radio {
            border-color: #2563eb;
            background: #2563eb;
            box-shadow: inset 0 0 0 2.5px #ffffff;
        }

        .panel-mr-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }

        .is-invalid {
            border-color: #ef4444 !important;
            background-color: #fef2f2 !important;
        }

        .row-fulfilled {
            background: #f8fafc !important;
            opacity: 0.65;
        }

        .row-out-of-stock {
            background: #fffbeb !important;
        }

        /* Category Tabs & Group Divider Rows */
        .category-tabs-bar {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .btn-category-tab {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 11px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 600;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #475569;
            cursor: pointer;
            transition: all 0.15s ease;
            white-space: nowrap;
            user-select: none;
        }

        .btn-category-tab:hover {
            border-color: #94a3b8;
            background: #f1f5f9;
            color: #0f172a;
        }

        .btn-category-tab.active {
            background: #1e3a8a;
            border-color: #1e3a8a;
            color: #ffffff;
            box-shadow: 0 1px 3px rgba(30, 58, 138, 0.25);
        }

        .btn-category-tab .tab-badge {
            background: rgba(0, 0, 0, 0.06);
            border-radius: 10px;
            padding: 1px 6px;
            font-size: 10px;
            font-weight: 700;
        }

        .btn-category-tab.active .tab-badge {
            background: rgba(255, 255, 255, 0.25);
            color: #ffffff;
        }

        .category-divider-row td {
            background: #f1f5f9 !important;
            padding: 7px 14px !important;
            border-top: 1px solid #e2e8f0 !important;
            border-bottom: 1px solid #e2e8f0 !important;
        }

        .subgroup-divider-row td {
            background: #f8fafc !important;
            padding: 5px 14px 5px 36px !important;
            border-bottom: 1px solid #edf2f7 !important;
        }

        .btn-clear-row {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            border: 1px solid transparent;
            background: transparent;
            color: #94a3b8;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn-clear-row:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            color: #475569;
        }

        .material-data-row {
            transition: background-color 0.12s ease;
        }

        .material-data-row:hover {
            background-color: #fbfcfe !important;
        }

        .material-data-row.row-active-qty {
            background-color: #f0fdf4 !important;
        }

        /* Modal Material Picker */
        #addMaterialModalBackdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(3px);
            z-index: 99999;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .modal-material-item {
            padding: 10px 14px;
            border-bottom: 1px solid #f1f5f9;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            transition: background 0.12s;
        }

        .modal-material-item:hover {
            background: #f0f9ff;
        }

        .sidebar-sticky-bon .form-control {
            height: 36px;
            padding: 7px 10px;
            font-size: 12px;
            line-height: 1.4;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            box-sizing: border-box;
            background-color: #ffffff;
            color: #0f172a;
        }

        .sidebar-sticky-bon .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
            outline: none;
        }

        .sidebar-sticky-bon textarea.form-control {
            height: auto !important;
            min-height: 64px;
            line-height: 1.4;
            padding: 8px 10px;
        }

        #mrSelect {
            height: 38px !important;
            padding: 7px 12px !important;
            font-size: 12.5px !important;
            line-height: 1.4 !important;
            border-radius: 8px !important;
            border: 1.5px solid #cbd5e1 !important;
            box-sizing: border-box !important;
            background-color: #ffffff !important;
            color: #0f172a !important;
            font-weight: 500 !important;
            transition: all 0.2s ease !important;
        }

        #mrSelect:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.15) !important;
            outline: none !important;
        }

        .mr-detail-card {
            margin-top: 12px;
            background: #ffffff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 6px -1px rgba(37,99,235,0.08);
        }

        /* Items Table Form Controls Styling */
        #itemsTable thead th {
            padding: 9px 12px !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.3px !important;
            color: #64748b !important;
            background: #fafafa !important;
            border-bottom: 1px solid #e2e8f0 !important;
            white-space: nowrap !important;
        }

        #itemsTable tbody td {
            padding: 8px 10px !important;
            vertical-align: middle !important;
        }

        #itemsTable .material-select,
        #itemsTable select.form-control {
            height: 38px !important;
            min-height: 38px !important;
            padding: 6px 12px !important;
            font-size: 13px !important;
            line-height: 1.4 !important;
            border-radius: 6px !important;
            border: 1.5px solid #cbd5e1 !important;
            background-color: #ffffff !important;
            color: #0f172a !important;
            box-sizing: border-box !important;
            width: 100% !important;
            cursor: pointer;
        }

        #itemsTable .material-select:focus,
        #itemsTable select.form-control:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15) !important;
            outline: none !important;
        }

        #itemsTable input.qty-input {
            height: 38px !important;
            min-height: 38px !important;
            padding: 6px 8px !important;
            font-size: 13px !important;
            font-weight: 700 !important;
            border-radius: 6px !important;
            border: 1.5px solid #cbd5e1 !important;
            box-sizing: border-box !important;
            color: #0f172a !important;
            text-align: center !important;
        }

        #itemsTable input.qty-input:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15) !important;
            outline: none !important;
        }

        #itemsTable input[name*="[notes]"] {
            height: 38px !important;
            min-height: 38px !important;
            padding: 7px 12px !important;
            font-size: 12.5px !important;
            line-height: 1.4 !important;
            border-radius: 6px !important;
            border: 1.5px solid #cbd5e1 !important;
            box-sizing: border-box !important;
            color: #0f172a !important;
        }

        #itemsTable input[name*="[notes]"]:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15) !important;
            outline: none !important;
        }

        #itemsTable .btn-delete-row {
            width: 36px !important;
            height: 36px !important;
            padding: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 6px !important;
            color: #dc2626 !important;
            background: #ffffff !important;
            border: 1px solid #fecaca !important;
            transition: all 0.15s ease !important;
            cursor: pointer !important;
            font-size: 12px !important;
        }

        #itemsTable .btn-delete-row:hover {
            background: #fef2f2 !important;
            border-color: #ef4444 !important;
            color: #b91c1c !important;
            transform: scale(1.04) !important;
        }

        /* Responsive Breakpoints */
        @media (max-width: 992px) {
            .material-usage-layout {
                grid-template-columns: 1fr !important;
            }
            .sidebar-sticky-bon {
                position: static !important;
            }
        }

        @media (max-width: 640px) {
            .mode-grid {
                grid-template-columns: 1fr !important;
                gap: 8px;
            }
            .panel-mr-grid {
                grid-template-columns: 1fr 1fr !important;
                gap: 8px !important;
            }
        }
    </style>
    @endpush

    <div class="breadcrumb mb-2" style="font-size:12px;">
        <a href="{{ route('material-usages.index') }}">Pemakaian Material</a>
        <span class="breadcrumb-sep"><x-lucide-chevron-right style="font-size:9px;" /></span>
        <span>Catat Pengeluaran Baru</span>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger mb-3" style="border-radius: 6px; font-size:12px; padding:8px 12px;">
        <x-lucide-triangle-alert class="text-danger" style="font-size: 14px;" />
        <div>
            <strong>Terdapat kesalahan pada input pengeluaran:</strong>
            <ul style="margin: 3px 0 0 16px; font-size: 11.5px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('material-usages.store') }}" id="usageForm" onsubmit="return validateBeforeSubmit(this)">
        @csrf
        <input type="hidden" name="input_mode" id="inputMode" value="manual">

        <div class="material-usage-layout mb-3">

            {{-- ── Kiri: Metode MR + Tabel Material ── --}}
            <div style="display:flex; flex-direction:column; gap:16px;">

                {{-- ==================== SECTION 1: METODE PENGELUARAN ==================== --}}

                {{-- ==================== SECTION 2: RINCIAN MATERIAL YANG DIKELUARKAN ==================== --}}
                <div class="card" style="border:1px solid #e2e8f0; border-radius:10px; background:#ffffff; box-shadow:0 2px 6px -1px rgba(0,0,0,0.03); overflow:hidden;">
                    <div class="card-header" style="background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:10px 14px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                        <div style="display:flex; align-items:center; gap:6px;">
                            <x-lucide-boxes class="text-primary" style="font-size:13px;" />
                            <span class="card-title" style="font-size:13px; font-weight:700; color:#1e293b;">Rincian Material yang Dikeluarkan</span>
                        </div>
                        <span id="itemsLoadingSpinner" style="display: none; font-size: 11.5px; color: #2563eb; font-weight: 600;">
                            <x-lucide-loader-2 class="me-1 animate-spin" /> Memuat data MR...
                        </span>

                        <div id="mrQuickActions" style="display: none;" class="flex gap-1">
                            <button type="button" class="btn btn-sm btn-light border text-primary fw-600" onclick="fillAllRemainingQuota()" title="Otomatis mengisi kuantitas sesuai sisa kuota yang disetujui" style="height: 32px; font-size: 12px; border-radius:6px; padding:0 12px;">
                                <x-lucide-check-check class="text-primary me-1" /> Penuhi Kuota
                            </button>
                            <button type="button" class="btn btn-sm btn-light border text-muted fw-600" onclick="resetAllQuantities()" title="Kosongkan nilai input kuantitas" style="height: 32px; font-size: 12px; border-radius:6px; padding:0 10px;">
                                <x-lucide-rotate-ccw class="me-1" /> Reset
                            </button>
                        </div>

                        <div id="manualButtonsGroup" class="flex items-center gap-2">
                            <button type="button" class="btn btn-sm" onclick="openAddMaterialModal()" style="height: 32px; font-size: 12px; border-radius: 6px; padding: 0 12px; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;" title="Cari dan tambahkan material lain dari master data">
                                <x-lucide-plus style="width:13px; height:13px;" /> Tambah Material Lain
                            </button>
                            <button type="button" class="btn btn-sm btn-light border" onclick="addCustomItemRow()" style="height: 32px; font-size: 12px; border-radius: 6px; padding: 0 10px; color: #475569; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;" title="Tambah item material tidak terdaftar / custom">
                                <x-lucide-package-plus style="width:13px; height:13px;" /> Item Custom
                            </button>
                        </div>
                        
                    </div>

                    {{-- Category Tabs Bar --}}
                    @if(isset($categoriesWithStock) && count($categoriesWithStock) > 0)
                    <div class="category-tabs-bar" id="categoryTabsBar">
                        <button type="button" class="btn-category-tab active" id="tab-category-all" onclick="filterByCategory('all')">
                            <span>Semua</span>
                            <span class="tab-badge" id="badge-count-all">{{ count($materialsData) }}</span>
                        </button>
                        @foreach($categoriesWithStock as $cat)
                        @php
                            $catSlug = \Illuminate\Support\Str::slug($cat['name']);
                            $catUpper = strtoupper($cat['name']);
                        @endphp
                        <button type="button" class="btn-category-tab" id="tab-category-{{ $catSlug }}" onclick="filterByCategory('{{ addslashes($cat['name']) }}')">
                            @if(str_contains($catUpper, 'STRUKTUR'))
                                <x-lucide-building-2 style="width:12px;height:12px;" />
                            @elseif(str_contains($catUpper, 'ARSITEK'))
                                <x-lucide-palette style="width:12px;height:12px;" />
                            @elseif(str_contains($catUpper, 'MEP'))
                                <x-lucide-zap style="width:12px;height:12px;" />
                            @else
                                <x-lucide-layers style="width:12px;height:12px;" />
                            @endif
                            <span>{{ $cat['name'] }}</span>
                            <span class="tab-badge">{{ $cat['count'] }}</span>
                        </button>
                        @endforeach
                    </div>
                    @endif

                    {{-- Search Toolbar --}}
                    <div style="padding: 10px 14px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;">
                        <div style="position: relative; max-width: 360px; width: 100%;">
                            <x-lucide-search style="position: absolute; left: 11px; top: 50%; transform:translateY(-50%); font-size: 11.5px; color: #94a3b8;" />
                            <input type="text" id="tableSearchInput" class="form-control" placeholder="Cari nama material atau kelompok (cth: Semen, Pasir)..." oninput="filterTableRows(this.value)" style="height: 34px; padding-left: 32px; padding-right: 28px; font-size: 12px; border-radius: 6px; border-color:#cbd5e1;">
                            <button type="button" id="clearSearchBtn" onclick="clearTableSearch()" style="display: none; position: absolute; right: 8px; top: 50%; transform: translateY(-50%); border: none; background: transparent; color: #94a3b8; cursor: pointer; padding: 2px 4px; font-size: 12px;" title="Hapus Pencarian">
                                <x-lucide-x style="width:13px;height:13px;" />
                            </button>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div class="flex items-center gap-1">
                                <button type="button" class="btn btn-sm btn-light border" onclick="expandAllAccordion()" style="height:32px; font-size:11.5px; font-weight:600; padding:0 10px; border-radius:6px; color:#475569; display:inline-flex; align-items:center; gap:4px;" title="Buka seluruh kategori dan kelompok material">
                                    <x-lucide-chevrons-down-up style="width:12px;height:12px;" /> Buka Semua
                                </button>
                                <button type="button" class="btn btn-sm btn-light border" onclick="collapseAllAccordion()" style="height:32px; font-size:11.5px; font-weight:600; padding:0 10px; border-radius:6px; color:#475569; display:inline-flex; align-items:center; gap:4px;" title="Tutup seluruh kategori dan kelompok material">
                                    <x-lucide-chevrons-up-down style="width:12px;height:12px;" /> Tutup Semua
                                </button>
                            </div>
                            <span class="text-muted" id="rowCountIndicator" style="font-size: 11px; font-weight: 600;">
                                {{ count($materialsData) }} item terdaftar
                            </span>
                        </div>
                    </div>

                    {{-- Table Rincian Material --}}
                    <div class="table-wrap" style="overflow-x:auto; -webkit-overflow-scrolling:touch;">
                        <table class="data-table mb-0" id="itemsTable">
                            <thead>
                                <tr style="background:#fafafa; border-bottom:1px solid #e2e8f0; font-size:10.5px; text-transform:uppercase; letter-spacing:0.3px; color:#64748b;">
                                    <th style="text-align: left; padding:7px 14px;">Nama Material & Spesifikasi</th>
                                    <th style="display:none; text-align: center; width: 110px; padding:7px 8px;" class="col-mr-only">Tiba / Pakai</th>
                                    <th style="display:none; text-align: center; width: 100px; padding:7px 8px;" class="col-mr-only">Sisa Kuota</th>
                                    <th style="text-align: center; width: 110px; padding:7px 8px;">Stok Gudang</th>
                                    <th style="text-align: center; width: 145px; padding:7px 8px;">Jumlah Keluar <span class="text-danger">*</span></th>
                                    <th style="text-align: left; padding:7px 12px;">Keterangan Keperluan</th>
                                    <th style="width: 44px; text-align: center; padding:7px 6px;"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                @php
                                    $currentCat = null;
                                    $currentType = null;
                                    $catCountMap = collect($materialsData)->groupBy('category_name')->map->count();
                                    $typeCountMap = collect($materialsData)->groupBy(function($item) {
                                        return ($item['category_name'] ?? 'UMUM') . '|||' . (!empty($item['type']) ? $item['type'] : 'Material Umum');
                                    })->map->count();
                                @endphp
                                @forelse($materialsData as $idx => $m)
                                @php
                                    $rowCat = $m['category_name'] ?? 'UMUM';
                                    $rowType = !empty($m['type']) ? $m['type'] : 'Material Umum';
                                    $catSlug = \Illuminate\Support\Str::slug($rowCat);
                                    $subKey = 'sub-' . $catSlug . '-' . \Illuminate\Support\Str::slug($rowType);
                                @endphp

                                {{-- Level 1: Disiplin / Kategori Header (Collapsible) --}}
                                @if($currentCat !== $rowCat)
                                    @php 
                                        $currentCat = $rowCat; 
                                        $currentType = null;
                                    @endphp
                                    <tr class="group-toggle category-divider-row collapsed" data-group="group-cat-{{ $catSlug }}" data-cat-header="{{ $currentCat }}" style="background:#f1f5f9 !important; cursor:pointer; user-select:none; border-top:1px solid #e2e8f0; border-bottom:1px solid #e2e8f0;">
                                        <td colspan="7" style="padding:9px 14px !important;">
                                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <x-lucide-chevron-down class="group-chev" style="font-size:10px; color:#64748b; transition:transform .2s; transform:rotate(-90deg);" aria-hidden="true" />
                                                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 5px; {{ str_contains(strtoupper($currentCat), 'STRUKTUR') ? 'background:#dbeafe; color:#1d4ed8;' : (str_contains(strtoupper($currentCat), 'ARSITEK') ? 'background:#fef3c7; color:#b45309;' : (str_contains(strtoupper($currentCat), 'MEP') ? 'background:#e0e7ff; color:#4338ca;' : 'background:#f1f5f9; color:#475569;')) }}">
                                                        @if(str_contains(strtoupper($currentCat), 'STRUKTUR'))
                                                            <x-lucide-building-2 style="width:12px;height:12px;" />
                                                        @elseif(str_contains(strtoupper($currentCat), 'ARSITEK'))
                                                            <x-lucide-palette style="width:12px;height:12px;" />
                                                        @elseif(str_contains(strtoupper($currentCat), 'MEP'))
                                                            <x-lucide-zap style="width:12px;height:12px;" />
                                                        @else
                                                            <x-lucide-layers style="width:12px;height:12px;" />
                                                        @endif
                                                    </span>
                                                    <span style="font-size: 12px; font-weight: 700; letter-spacing: 0.4px; text-transform: uppercase; color: #1e293b;">{{ $currentCat }}</span>
                                                </div>
                                                <span class="badge" style="background:#ffffff; color:#475569; font-size:10px; font-weight:600; padding:2px 8px; border-radius:10px; border:1px solid #cbd5e1;">
                                                    {{ $catCountMap[$currentCat] ?? 0 }} Material
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                @endif

                                {{-- Level 2: Sub-group Kelompok Barang Header (Collapsible) --}}
                                @if($currentType !== $rowType)
                                    @php $currentType = $rowType; @endphp
                                    <tr class="group-rows group-cat-{{ $catSlug }} subgroup-toggle subgroup-divider-row collapsed" data-group="{{ $subKey }}" data-cat-header="{{ $currentCat }}" data-subgroup-header="{{ $currentType }}" style="background:#f8fafc !important; cursor:pointer; user-select:none; border-bottom:1px solid #edf2f7; display:none;">
                                        <td colspan="7" style="padding:7px 14px 7px 32px !important;">
                                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                                <div style="display: flex; align-items: center; gap: 7px;">
                                                    <x-lucide-chevron-down class="subgroup-chev" style="font-size:9px; color:#94a3b8; transition:transform .2s; transform:rotate(-90deg);" aria-hidden="true" />
                                                    <x-lucide-layers style="width:11px; height:11px; color:#64748b;" />
                                                    <span style="font-size: 11.5px; font-weight: 600; color: #334155;">{{ $currentType }}</span>
                                                </div>
                                                <span class="text-muted" style="font-size:11px; font-weight:500;">
                                                    ({{ $typeCountMap[$currentCat . '|||' . $currentType] ?? 1 }})
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                @endif

                                {{-- Material Data Row (Clean, no search input inside row!) --}}
                                <tr id="row-{{ $idx }}" class="material-data-row group-rows group-cat-{{ $catSlug }} subgroup-rows {{ $subKey }}" 
                                    data-category="{{ $rowCat }}" 
                                    data-type="{{ $rowType }}"
                                    data-name="{{ strtolower($m['name']) }}" 
                                    data-code="{{ strtolower($m['code']) }}"
                                    data-sku="{{ strtolower($m['sku'] ?? '') }}"
                                    style="border-bottom: 1px solid #f1f5f9; display:none;">
                                    <td style="padding: 10px 14px; vertical-align: middle;">
                                        <div style="display: flex; flex-direction: column; gap: 2px;">
                                            <span style="font-size: 13px; font-weight: 700; color: #0f172a; line-height: 1.3;">
                                                {{ $m['name'] }}
                                            </span>
                                            <div style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: #64748b; margin-top: 2px;">
                                                <span class="badge" style="font-size: 9.5px; font-weight: 700; padding: 1.5px 6px; border-radius: 4px; {{ str_contains(strtoupper($rowCat), 'STRUKTUR') ? 'background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe;' : (str_contains(strtoupper($rowCat), 'ARSITEK') ? 'background:#fefce8; color:#a16207; border:1px solid #fef08a;' : (str_contains(strtoupper($rowCat), 'MEP') ? 'background:#eef2ff; color:#4338ca; border:1px solid #c7d2fe;' : 'background:#f8fafc; color:#64748b; border:1px solid #e2e8f0;')) }}">
                                                    {{ $rowCat }}
                                                </span>
                                                @if(!empty($m['type']))
                                                    <span class="fw-500" style="color: #475569;">• {{ $m['type'] }}</span>
                                                @endif
                                                @if(!empty($m['brand']))
                                                    <span>• Merek: {{ $m['brand'] }}</span>
                                                @endif
                                                @if(!empty($m['size']))
                                                    <span>• {{ $m['size'] }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <input type="hidden" name="items[{{ $idx }}][material_id]" id="row-{{ $idx }}-material-id" value="{{ $m['id'] }}">
                                        <input type="hidden" name="items[{{ $idx }}][custom_item_name]" id="row-{{ $idx }}-custom-name" value="">
                                        <input type="hidden" name="items[{{ $idx }}][custom_item_unit]" id="row-{{ $idx }}-custom-unit" value="">
                                    </td>
                                    <td style="display:none; padding: 7px 8px; text-align: center;" class="col-mr-only">
                                        -
                                    </td>
                                    <td style="display:none; padding: 7px 8px; text-align: center;" class="col-mr-only">
                                        -
                                    </td>
                                    <td style="padding: 8px 8px; text-align: center;">
                                        <span class="badge" id="row-{{ $idx }}-stock" style="font-size: 11.5px; font-weight: 700; padding: 4px 8px; border-radius: 5px; {{ (float)$m['stock'] > 0 ? 'background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe;' : 'background:#fef2f2; color:#b91c1c; border:1px solid #fecaca;' }}">
                                            {{ $m['stock'] }} {{ $m['unit'] }}
                                        </span>
                                    </td>
                                    <td style="padding: 8px 8px;">
                                        <div style="display: flex; align-items: center; justify-content:center; gap: 6px;">
                                            <input type="number" name="items[{{ $idx }}][quantity]" id="row-{{ $idx }}-qty" class="form-control qty-input text-center"
                                                   step="0.01" min="0" placeholder="0" value="" oninput="validateManualQty('row-{{ $idx }}')"
                                                   style="width: 85px;" max="{{ $m['stock'] }}">
                                            <span class="text-muted fw-600" id="row-{{ $idx }}-unit" style="font-size: 11.5px; min-width: 24px;">{{ $m['unit'] }}</span>
                                        </div>
                                        <div id="row-{{ $idx }}-warning" class="text-danger fw-600" style="font-size: 10px; margin-top: 3px; text-align:center; display: none;"></div>
                                    </td>
                                    <td style="padding: 8px 10px;">
                                        <input type="text" name="items[{{ $idx }}][notes]" class="form-control" placeholder="Keterangan keperluan...">
                                    </td>
                                    <td style="padding: 8px 6px; text-align: center;">
                                        <button type="button" class="btn-clear-row" onclick="clearRowQty('row-{{ $idx }}')" title="Kosongkan Nilai Kuantitas">
                                            <x-lucide-rotate-ccw style="width:13px; height:13px;" />
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr id="empty-stock-row">
                                    <td colspan="7" class="text-center p-4 text-muted" style="font-size: 12.5px;">
                                        Belum ada stok material yang tercatat di gudang ini. Klik <strong>+ Tambah Material Lain</strong> atau <strong>+ Item Custom</strong> di atas untuk mencatat pengeluaran.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>

                        {{-- Empty State jika belum pilih MR --}}
                        <div id="mrEmptyState" style="display:none; padding: 28px 16px; text-align: center; color: #94a3b8;">
                            <x-lucide-inbox style="font-size: 24px; color: #cbd5e1; margin-bottom: 6px; display:inline-block;" />
                            <div style="font-size: 12.5px; font-weight: 600; color: #475569;">Belum Ada PO Lapangan (MR) yang Dipilih</div>
                            <div style="font-size: 11px; margin-top: 2px;">Silakan pilih nomor MR pada bagian <strong>Metode & Surat PO Lapangan</strong> di atas.</div>
                        </div>
                    </div>

                    {{-- Summary Bar --}}
                    <div style="padding: 8px 14px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                        <div style="display: flex; align-items: center; gap: 14px; font-size: 11.5px;">
                            <div>Material: <strong id="sumItemCount" style="color: #0f172a;">0</strong> jenis</div>
                            <div>Total Kuantitas: <strong id="sumQuantityCount" style="color: #2563eb;">0</strong></div>
                        </div>
                        <div id="statusValidationBadge">
                            <span class="badge" style="background:#f1f5f9; color:#64748b; font-size: 10.5px; padding:2px 8px; border-radius:12px; border:1px solid #cbd5e1;">
                                Belum Ada Kuantitas
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Kanan: Informasi Pengeluaran ── --}}
            <div class="card sidebar-sticky-bon" id="bonSidebarCard" style="position:sticky; top:20px; border:1px solid #e2e8f0; border-radius:10px; background:#ffffff; box-shadow:0 2px 6px -1px rgba(0,0,0,0.03); overflow:hidden;">
                <div class="card-header" style="background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:10px 14px; display:flex; align-items:center; gap:6px;">
                    <x-lucide-receipt class="text-primary" style="font-size:13px;" />
                    <span class="card-title" style="font-size:13px; font-weight:700; color:#1e293b;">Informasi Pengeluaran</span>
                </div>
                <div class="card-body" style="padding:14px;">

                    {{-- Gudang Sumber (Otomatis seperti di Surat Jalan) --}}
                    <div class="mb-2">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:3px;">
                            <label class="form-label mb-0" style="font-size:11px; font-weight:600; color:#334155;">Gudang Sumber <span class="text-danger">*</span></label>
                            @if($warehouses->count() > 1 && auth()->user()->hasAnyRole(['Owner', 'Admin Pusat', 'Admin Gudang Pusat', 'Admin PO']))
                            <button type="button" onclick="toggleWarehouseSwitch()" style="background:none; border:none; padding:0; font-size:10.5px; color:#2563eb; font-weight:600; cursor:pointer;" title="Ganti Gudang">
                                <x-lucide-arrow-left-right style="font-size:9.5px;" /> Ganti
                            </button>
                            @endif
                        </div>

                        {{-- Hidden actual value submitted with form --}}
                        <input type="hidden" name="warehouse_id" id="warehouseSelect" value="{{ $selectedWarehouse?->id }}">

                        {{-- Readonly Box: Otomatis --}}
                        <div id="warehouseLockedDisplay" style="display:flex; align-items:center; justify-content:space-between; padding:7px 10px; background:#f8fafc; border:1px solid #cbd5e1; border-radius:6px; min-height:36px; box-sizing:border-box;">
                            <div style="display:flex; align-items:center; gap:7px; overflow:hidden;">
                                <x-lucide-warehouse class="text-primary" style="font-size:12px; flex-shrink:0;" />
                                <span style="font-size:12px; font-weight:600; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" id="displayWarehouseName">
                                    {{ $selectedWarehouse?->name ?? 'Gudang Pusat' }} {{ $selectedWarehouse?->is_central ? '(Pusat)' : '' }}
                                </span>
                            </div>
                            <span class="badge" style="background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe; font-size:10px; padding:2px 7px; border-radius:4px; font-weight:600; flex-shrink:0;">
                                <x-lucide-lock style="font-size:8.5px;" /> Otomatis
                            </span>
                        </div>

                        @if($warehouses->count() > 1 && auth()->user()->hasAnyRole(['Owner', 'Admin Pusat', 'Admin Gudang Pusat', 'Admin PO']))
                        <div id="warehouseSwitchWrapper" style="display:none; margin-top:5px;">
                            <select id="warehousePickerDropdown" class="form-control" onchange="onWarehouseChanged(this.value)" style="height:34px; padding:5px 9px; font-size:12px; border-radius:6px; border-color:#93c5fd; box-sizing:border-box;">
                                @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ $selectedWarehouse?->id == $wh->id ? 'selected' : '' }}>
                                    {{ $wh->name }} {{ $wh->is_central ? '(Pusat)' : '' }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                    </div>

                    {{-- Nama Pemakai --}}
                    <div class="mb-2">
                        <label class="form-label" style="font-size:11px; font-weight:600; color:#334155; margin-bottom:3px;">Pemakai (Mandor / Tukang) <span class="text-danger">*</span></label>
                        <input type="text" name="recipient_name" id="recipientNameInput" class="form-control"
                               placeholder="Contoh: Pak Supri (Mandor / Pekerja)"
                               value="{{ old('recipient_name') }}" required>
                    </div>

                    {{-- Bagian Pekerjaan / Zona --}}
                    <div class="mb-2">
                        <label class="form-label" style="font-size:11px; font-weight:600; color:#334155; margin-bottom:3px;">Pekerjaan / Zona <span class="text-muted fw-400">(Opsional)</span></label>
                        <input type="text" name="job_section" id="jobSectionInput" class="form-control"
                               placeholder="Contoh: Kolom Lt. 2"
                               value="{{ old('job_section') }}">
                    </div>

                    {{-- Tanggal Pengeluaran --}}
                    <div class="mb-2">
                        <label class="form-label" style="font-size:11px; font-weight:600; color:#334155; margin-bottom:3px;">Tanggal Pengeluaran <span class="text-danger">*</span></label>
                        <input type="date" name="usage_date" class="form-control"
                               value="{{ old('usage_date', date('Y-m-d')) }}" required>
                    </div>

                    {{-- Catatan Tambahan --}}
                    <div class="mb-3">
                        <label class="form-label" style="font-size:11px; font-weight:600; color:#334155; margin-bottom:3px;">Catatan Tambahan</label>
                        <textarea name="notes" id="notesInput" class="form-control" rows="2"
                            placeholder="Catatan serah terima material...">{{ old('notes') }}</textarea>
                    </div>

                    <div style="display:flex; flex-direction:column; gap:6px;">
                        <button type="submit" class="btn btn-primary w-full" id="submitBtn" style="justify-content:center; padding:8px 12px; height:38px; font-size:12.5px; font-weight:600; border-radius:6px; box-shadow:0 2px 4px rgba(37,99,235,0.2);">
                            <x-lucide-circle-check class="me-1" /> Simpan &amp; Potong Stok
                        </button>
                        <a href="{{ route('material-usages.index') }}" class="btn btn-light border w-full text-center" style="justify-content:center; padding:6px 12px; height:32px; font-size:12px; font-weight:500; border-radius:6px; color:#64748b;">
                            Batal
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </form>

    {{-- Modal Tambah Material Lain (Clean Material Picker Modal) --}}
    <div id="addMaterialModalBackdrop" onclick="handleModalBackdropClick(event)">
        <div id="addMaterialModal" style="background:#ffffff; border-radius:12px; width:100%; max-width:620px; box-shadow:0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1); border:1px solid #e2e8f0; overflow:hidden; display:flex; flex-direction:column; max-height:85vh; margin:auto;">
            {{-- Modal Header --}}
            <div style="padding:14px 18px; border-bottom:1px solid #e2e8f0; display:flex; align-items:center; justify-content:space-between; background:#f8fafc;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:6px; background:#eff6ff; color:#2563eb;">
                        <x-lucide-boxes style="width:16px; height:16px;" />
                    </span>
                    <div>
                        <h4 style="font-size:13.5px; font-weight:700; color:#0f172a; margin:0;">Pilih Material dari Master Data</h4>
                        <p style="font-size:11px; color:#64748b; margin:0;">Cari dan tambahkan material ke tabel pengeluaran tanpa input autocomplete per-baris</p>
                    </div>
                </div>
                <button type="button" onclick="closeAddMaterialModal()" style="border:none; background:transparent; color:#94a3b8; cursor:pointer; padding:4px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center;" title="Tutup">
                    <x-lucide-x style="width:18px; height:18px;" />
                </button>
            </div>

            {{-- Modal Search Input --}}
            <div style="padding:12px 18px; border-bottom:1px solid #f1f5f9; background:#ffffff;">
                <div style="position:relative;">
                    <x-lucide-search style="position:absolute; left:12px; top:50%; transform:translateY(-50%); font-size:13px; color:#94a3b8;" />
                    <input type="text" id="modalSearchInput" class="form-control" placeholder="Cari nama material atau kelompok (cth: Semen, Pasir)..." oninput="filterModalMaterials(this.value)" style="height:38px; padding-left:36px; padding-right:12px; font-size:12.5px; border-radius:6px; border:1px solid #cbd5e1; width:100%;">
                </div>
            </div>

            {{-- Modal Materials List --}}
            <div id="modalMaterialsList" style="flex:1; overflow-y:auto; max-height:420px; padding:4px 0;">
                {{-- Rendered dynamically by JS --}}
            </div>

            {{-- Modal Footer --}}
            <div style="padding:10px 18px; border-top:1px solid #e2e8f0; background:#f8fafc; display:flex; align-items:center; justify-content:space-between;">
                <span id="modalResultCount" style="font-size:11px; color:#64748b; font-weight:600;">0 material tersedia</span>
                <button type="button" class="btn btn-light border" onclick="closeAddMaterialModal()" style="font-size:12px; padding:5px 14px; font-weight:600; border-radius:6px;">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- Global Floating Material Autocomplete Suggestions Dropdown --}}
    <div id="material-suggestions-box" 
         style="display:none; position:absolute; z-index:99999; background:#ffffff; border:1px solid #cbd5e1; border-radius:8px; box-shadow:0 14px 30px -4px rgba(0,0,0,0.22); max-height:280px; overflow-y:auto; font-family:'Inter', sans-serif;">
    </div>

    {{-- Floating Action Bar for Mobile View --}}
    <div id="mobileUsageSummaryBar" style="display:none;position:fixed;bottom:14px;left:14px;right:14px;z-index:99;background:#0f172a;color:#ffffff;border-radius:10px;padding:10px 14px;box-shadow:0 8px 20px -3px rgba(0,0,0,0.3);align-items:center;justify-content:space-between;gap:10px;">
        <div style="display:flex;align-items:center;gap:6px;">
            <x-lucide-boxes class="text-primary" style="font-size:13px;" />
            <span style="font-size:12px;font-weight:600;"><span id="mobileUsageItemCount">0</span> Item Siap</span>
        </div>
        <button type="button" onclick="scrollToBonForm()" class="btn btn-sm btn-primary" style="padding:5px 12px;font-size:11.5px;font-weight:600;border-radius:5px;box-shadow:none;">
            Simpan Bon &darr;
        </button>
    </div>

    {{-- ==================== JAVASCRIPT ==================== --}}
    @push('scripts')
    <script>
        /* ts-nocheck: Blade directives inside script are server-rendered */
        const availableMaterials = @json($materialsData);
        const materialsGrouped = @json($materialsGrouped ?? []);
        const selectedWarehouseId = {{ $selectedWarehouse?->id ?? 'null' }};
        let currentMode = 'manual';
        let isLoadingMR = false;
        let mrItemsCache = [];
        let rowIndex = {{ count($materialsData) }};

        function scrollToBonForm() {
            const sidebar = document.getElementById('bonSidebarCard');
            if (sidebar) {
                sidebar.scrollIntoView({ behavior: 'smooth' });
                const recipientInput = document.getElementById('recipientNameInput');
                if (recipientInput) setTimeout(() => recipientInput.focus(), 300);
            }
        }



        function toggleWarehouseSwitch() {
            const wrapper = document.getElementById('warehouseSwitchWrapper');
            if (wrapper) {
                wrapper.style.display = (wrapper.style.display === 'none' || wrapper.style.display === '') ? 'block' : 'none';
            }
        }

        function onWarehouseChanged(warehouseId) {
            window.location.href = '{{ route('material-usages.create') }}?warehouse_id=' + warehouseId;
        }

        async function loadMRDetails(mrId) {
            if (isLoadingMR) return;

            const tbody = document.getElementById('itemsBody');
            const panel = document.getElementById('mrDetailPanel');
            const spinner = document.getElementById('itemsLoadingSpinner');
            const emptyState = document.getElementById('mrEmptyState');
            const quickActions = document.getElementById('mrQuickActions');

            if (!mrId) {
                panel.style.display = 'none';
                quickActions.style.display = 'none';
                tbody.innerHTML = '';
                rowIndex = 0;
                emptyState.style.display = 'block';
                updateTableSummary();
                return;
            }

            isLoadingMR = true;
            emptyState.style.display = 'none';
            spinner.style.display = 'inline-block';

            try {
                const response = await fetch(`/material-requests/${mrId}/details?warehouse_id=${selectedWarehouseId}`);
                if (!response.ok) {
                    throw new Error('Gagal memuat rincian MR dari server');
                }
                const data = await response.json();
                spinner.style.display = 'none';
                quickActions.style.display = 'flex';

                // Fill MR executive panel
                document.getElementById('panelMrNumber').innerText = '#' + data.request_number;
                document.getElementById('panelMrRequester').innerText = data.requested_by;
                document.getElementById('panelMrApprover').innerText = data.approved_by;
                document.getElementById('panelMrDate').innerText = data.created_at;
                document.getElementById('panelMrItemCount').innerText = data.items.length + ' Item';

                const statusBadge = document.getElementById('panelMrStatus');
                if (data.status === 'partially_fulfilled') {
                    statusBadge.style.background = '#eff6ff';
                    statusBadge.style.color = '#2563eb';
                    statusBadge.style.borderColor = '#bfdbfe';
                    statusBadge.innerText = 'Terkirim Sebagian';
                } else {
                    statusBadge.style.background = '#ecfdf5';
                    statusBadge.style.color = '#047857';
                    statusBadge.style.borderColor = '#a7f3d0';
                    statusBadge.innerText = 'Disetujui Site Manager';
                }

                if (data.notes) {
                    document.getElementById('panelMrNotes').innerText = data.notes;
                    document.getElementById('panelMrNotesWrapper').style.display = 'block';
                } else {
                    document.getElementById('panelMrNotesWrapper').style.display = 'none';
                }
                panel.style.display = 'block';

                const notesInput = document.getElementById('notesInput');
                if (!notesInput.value && data.notes) {
                    notesInput.value = 'Berdasarkan MR #' + data.request_number + ': ' + data.notes;
                }

                tbody.innerHTML = '';
                rowIndex = 0;
                mrItemsCache = data.items;

                data.items.forEach(item => {
                    const rowId = 'row-' + rowIndex;
                    const remaining = parseFloat(item.qty_remaining || 0);
                    const stock = parseFloat(item.stock || 0);
                    const defaultQty = remaining > 0 && stock > 0 ? Math.min(remaining, stock) : (remaining > 0 ? remaining : 0);
                    const isFullyFulfilled = remaining <= 0;
                    const isOutOfStock = stock <= 0;
                    const fulfilledBadgeHtml = isFullyFulfilled 
                        ? `<span class="badge" style="font-size: 10px; background:#f1f5f9; color:#64748b; margin-top:2px; display:inline-block;"><x-lucide-check /> Terpenuhi</span>` 
                        : '';

                    const tr = document.createElement('tr');
                    tr.id = rowId;
                    tr.className = (isFullyFulfilled ? 'row-fulfilled' : (isOutOfStock ? 'row-out-of-stock' : ''));
                    tr.style.borderBottom = '1px solid #f1f5f9';

                    tr.innerHTML = `
                        <td style="padding: 7px 12px;">
                            <input type="hidden" name="items[${rowIndex}][material_id]" value="${item.material_id}">
                            <div class="fw-600 text-dark" style="font-size: 12.5px; color:#0f172a;">${item.name}</div>
                            <div class="flex items-center gap-2 mt-1">
                                <code style="font-size: 10.5px; background: #f1f5f9; padding: 1px 4px; border-radius: 3px; color: #475569;">${item.code}</code>
                                <span class="badge" style="font-size: 10px; background:#f1f5f9; color:#64748b; border-radius:3px; padding:1px 5px;">${item.category}</span>
                            </div>
                            ${fulfilledBadgeHtml}
                        </td>
                        <td style="padding: 7px 8px; text-align: center;">
                            <div class="fw-700" style="font-size: 12px; color:#1e293b;">Tiba: ${item.qty_received} ${item.unit}</div>
                            <div class="text-muted" style="font-size: 10.5px;">Pakai: ${item.qty_issued}</div>
                        </td>
                        <td style="padding: 7px 8px; text-align: center;">
                            <span class="badge" style="font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius:4px; ${remaining > 0 ? 'background:#ecfdf5; color:#047857; border:1px solid #a7f3d0;' : 'background:#f1f5f9; color:#64748b;'}">
                                ${remaining} ${item.unit}
                            </span>
                        </td>
                        <td style="padding: 7px 8px; text-align: center;">
                            <span class="badge" style="font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius:4px; ${stock > 0 ? 'background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe;' : 'background:#fef2f2; color:#b91c1c; border:1px solid #fecaca;'}">
                                ${stock} ${item.unit}
                            </span>
                            ${isOutOfStock ? '<div class="text-danger" style="font-size: 10px; font-weight: 600; margin-top: 1px;">Stok 0</div>' : ''}
                        </td>
                        <td style="padding: 8px 8px;">
                            <div style="display: flex; align-items: center; justify-content:center; gap: 6px;">
                                <input type="number" 
                                       name="items[${rowIndex}][quantity]" 
                                       id="${rowId}-qty" 
                                       class="form-control qty-input text-center"
                                       step="0.01" 
                                       min="0"
                                       data-remaining="${remaining}"
                                       data-stock="${stock}"
                                       placeholder="0" 
                                       value="${isFullyFulfilled ? 0 : defaultQty}" 
                                       ${isFullyFulfilled ? 'readonly' : 'required'}
                                       oninput="validateMRQty('${rowId}')"
                                       style="width: 85px;">
                                <span class="text-muted fw-600" style="font-size: 11.5px; min-width: 24px;">${item.unit}</span>
                            </div>
                            <div id="${rowId}-warning" class="text-danger fw-600" style="font-size: 10px; margin-top: 3px; text-align:center; display: none;"></div>
                        </td>
                        <td style="padding: 8px 10px;">
                            <input type="text" name="items[${rowIndex}][notes]" class="form-control" placeholder="Catatan keperluan..." value="${item.notes || ''}">
                        </td>
                        <td style="padding: 8px 6px; text-align: center;">
                            <button type="button" class="btn-delete-row" onclick="removeRow('${rowId}')" title="Hapus Baris">
                                <x-lucide-trash-2 />
                            </button>
                        </td>
                    `;

                    tbody.appendChild(tr);
                    rowIndex++;
                    validateMRQty(rowId);
                });

                updateTableSummary();

            } catch (err) {
                spinner.style.display = 'none';
                alert('Gagal memuat rincian MR: ' + err.message);
            } finally {
                isLoadingMR = false;
            }
        }

        function validateMRQty(rowId) {
            const input = document.getElementById(rowId + '-qty');
            if (!input) return;

            const remaining = parseFloat(input.getAttribute('data-remaining') || 0);
            const stock = parseFloat(input.getAttribute('data-stock') || 0);
            const val = parseFloat(input.value || 0);
            const warningEl = document.getElementById(rowId + '-warning');

            if (val > remaining) {
                input.classList.add('is-invalid');
                warningEl.style.display = 'block';
                warningEl.innerHTML = `<x-lucide-circle-alert class="me-1" /> Max: ${remaining}`;
            } else if (val > stock) {
                input.classList.add('is-invalid');
                warningEl.style.display = 'block';
                warningEl.innerHTML = `<x-lucide-triangle-alert class="me-1" /> Stok: ${stock}`;
            } else {
                input.classList.remove('is-invalid');
                warningEl.style.display = 'none';
                warningEl.innerText = '';
            }
            updateTableSummary();
        }

        function fillAllRemainingQuota() {
            const rows = document.querySelectorAll('#itemsBody tr');
            rows.forEach(tr => {
                const input = tr.querySelector('.qty-input');
                if (input && !input.readOnly) {
                    const remaining = parseFloat(input.getAttribute('data-remaining') || 0);
                    const stock = parseFloat(input.getAttribute('data-stock') || 0);
                    const autoQty = Math.min(remaining, stock);
                    input.value = autoQty > 0 ? autoQty : 0;
                    validateMRQty(tr.id);
                }
            });
            updateTableSummary();
        }

        function resetAllQuantities() {
            const rows = document.querySelectorAll('#itemsBody tr');
            rows.forEach(tr => {
                const input = tr.querySelector('.qty-input');
                if (input && !input.readOnly) {
                    input.value = 0;
                    validateMRQty(tr.id);
                }
            });
            updateTableSummary();
        }

        let activeAutocompleteRowId = null;
        let currentSuggestions = [];
        let activeSuggestionIndex = -1;

        /* --- Modal Picker & Dynamic Row Management --- */
        function openAddMaterialModal() {
            const modal = document.getElementById('addMaterialModalBackdrop');
            if (modal) {
                modal.style.display = 'flex';
                const searchInp = document.getElementById('modalSearchInput');
                if (searchInp) {
                    searchInp.value = '';
                    filterModalMaterials('');
                    setTimeout(() => searchInp.focus(), 100);
                }
            }
        }

        function closeAddMaterialModal() {
            const modal = document.getElementById('addMaterialModalBackdrop');
            if (modal) modal.style.display = 'none';
        }

        function handleModalBackdropClick(event) {
            if (event.target && event.target.id === 'addMaterialModalBackdrop') {
                closeAddMaterialModal();
            }
        }

        function filterModalMaterials(query) {
            const container = document.getElementById('modalMaterialsList');
            const countLabel = document.getElementById('modalResultCount');
            if (!container) return;

            const q = (query || '').toLowerCase().trim();
            let matched = availableMaterials.filter(m => {
                if (!q) return true;
                const name = (m.name || '').toLowerCase();
                const code = (m.code || '').toLowerCase();
                const sku = (m.sku || '').toLowerCase();
                const cat = (m.category_name || m.category || '').toLowerCase();
                const type = (m.type || '').toLowerCase();
                const words = name.split(/[\s,.\/-]+/).filter(Boolean);
                const startsWithWord = words.some(w => w.startsWith(q));
                return startsWithWord || name.includes(q) || code.includes(q) || sku.includes(q) || cat.includes(q) || type.includes(q);
            });

            matched.sort((a, b) => {
                if (q) {
                    const aStarts = (a.name || '').toLowerCase().startsWith(q);
                    const bStarts = (b.name || '').toLowerCase().startsWith(q);
                    if (aStarts && !bStarts) return -1;
                    if (!aStarts && bStarts) return 1;
                }
                return (a.name || '').localeCompare(b.name || '', 'id', { sensitivity: 'base' });
            });

            if (countLabel) {
                countLabel.innerText = matched.length + ' material ditemukan';
            }

            if (matched.length === 0) {
                container.innerHTML = `
                    <div style="text-align:center; padding:32px 16px; color:#94a3b8;">
                        <div style="font-size:13px; font-weight:600; color:#475569;">Tidak ada material yang cocok</div>
                        <div style="font-size:11.5px; margin-top:3px;">Coba kata kunci lain atau gunakan tombol "+ Item Custom" untuk item non-master.</div>
                    </div>
                `;
                return;
            }

            const existingMatIds = new Set();
            document.querySelectorAll('#itemsBody input[name*="[material_id]"]').forEach(inp => {
                if (inp.value) existingMatIds.add(String(inp.value));
            });

            let html = '';
            matched.forEach(m => {
                const isAlreadyInTable = existingMatIds.has(String(m.id));
                const stockVal = parseFloat(m.stock || 0);
                const isInStock = stockVal > 0;
                const catName = m.category_name || m.category || 'UMUM';
                const catUpper = catName.toUpperCase();
                const catColorStyle = catUpper.includes('STRUKTUR') 
                    ? 'background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe;' 
                    : (catUpper.includes('ARSITEK') 
                        ? 'background:#fefce8; color:#a16207; border:1px solid #fef08a;' 
                        : (catUpper.includes('MEP') 
                            ? 'background:#eef2ff; color:#4338ca; border:1px solid #c7d2fe;' 
                            : 'background:#f8fafc; color:#64748b; border:1px solid #e2e8f0;'));

                html += `
                    <div class="modal-material-item" onclick="selectMaterialFromModal(${m.id})">
                        <div style="flex:1; min-width:0;">
                            <span style="font-size:12.5px; font-weight:700; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; display:block;">
                                ${highlightMatch(m.name, q)}
                            </span>
                            <div style="display:flex; align-items:center; gap:6px; font-size:11px; color:#64748b; margin-top:3px;">
                                <span class="badge" style="font-size:9.5px; font-weight:700; padding:1.5px 6px; border-radius:4px; ${catColorStyle}">
                                    ${escapeHtml(catName)}
                                </span>
                                ${m.type ? `<span class="fw-500" style="color:#475569;">&bull; ${escapeHtml(m.type)}</span>` : ''}
                                ${m.brand ? `<span>&bull; Merek: ${escapeHtml(m.brand)}</span>` : ''}
                                ${m.size ? `<span>&bull; ${escapeHtml(m.size)}</span>` : ''}
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; gap:10px; flex-shrink:0;">
                            <span class="badge" style="font-size:11px; font-weight:700; padding:3px 7px; border-radius:4px; ${isInStock ? 'background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe;' : 'background:#fef2f2; color:#b91c1c; border:1px solid #fecaca;'}">
                                ${stockVal} ${escapeHtml(m.unit || '')}
                            </span>
                            ${isAlreadyInTable 
                                ? `<span class="badge" style="background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; font-size:10.5px; padding:3px 8px; border-radius:5px;">Sudah di Tabel</span>` 
                                : `<button type="button" class="btn btn-sm btn-primary" style="font-size:11px; padding:3px 9px; font-weight:600; border-radius:5px;">+ Pilih</button>`}
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        function selectMaterialFromModal(materialId) {
            closeAddMaterialModal();

            let existingRow = null;
            document.querySelectorAll('#itemsBody tr.material-data-row').forEach(tr => {
                const hid = tr.querySelector('input[name*="[material_id]"]');
                if (hid && hid.value == materialId) {
                    existingRow = tr;
                }
            });

            if (existingRow) {
                if (currentActiveCategory !== 'all') {
                    filterByCategory('all');
                }
                const searchInp = document.getElementById('tableSearchInput');
                if (searchInp && searchInp.value) {
                    clearTableSearch();
                }

                existingRow.classList.forEach(c => {
                    if (c.startsWith('sub-')) {
                        const sub = document.querySelector(`#itemsBody tr.subgroup-toggle[data-group="${c}"]`);
                        if (sub) {
                            sub.classList.remove('collapsed');
                            sub.style.display = '';
                            const sc = sub.querySelector('.subgroup-chev');
                            if (sc) sc.style.transform = 'rotate(0deg)';
                        }
                    }
                    if (c.startsWith('group-cat-')) {
                        const cat = document.querySelector(`#itemsBody tr.group-toggle[data-group="${c}"]`);
                        if (cat) {
                            cat.classList.remove('collapsed');
                            cat.style.display = '';
                            const cc = cat.querySelector('.group-chev');
                            if (cc) cc.style.transform = 'rotate(0deg)';
                        }
                    }
                });

                existingRow.style.display = '';
                existingRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
                existingRow.classList.add('row-active-qty');
                const qtyInput = existingRow.querySelector('.qty-input');
                if (qtyInput) {
                    setTimeout(() => {
                        qtyInput.focus();
                        qtyInput.select();
                    }, 200);
                }
                return;
            }

            addMaterialRowFromMaster(materialId);
        }

        function addMaterialRowFromMaster(materialId, initialQty = '', initialNotes = '') {
            const m = availableMaterials.find(x => x.id == materialId);
            if (!m) return;

            const tbody = document.getElementById('itemsBody');
            const emptyStockRow = document.getElementById('empty-stock-row');
            if (emptyStockRow) emptyStockRow.remove();

            const rowId = 'row-' + rowIndex;
            const rowCat = m.category_name || m.category || 'UMUM';
            const rowType = m.type || 'Material Umum';
            const catUpper = rowCat.toUpperCase();
            const catColorStyle = catUpper.includes('STRUKTUR') 
                ? 'background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe;' 
                : (catUpper.includes('ARSITEK') 
                    ? 'background:#fefce8; color:#a16207; border:1px solid #fef08a;' 
                    : (catUpper.includes('MEP') 
                        ? 'background:#eef2ff; color:#4338ca; border:1px solid #c7d2fe;' 
                        : 'background:#f8fafc; color:#64748b; border:1px solid #e2e8f0;'));

            const tr = document.createElement('tr');
            tr.id = rowId;
            tr.className = 'material-data-row';
            tr.setAttribute('data-category', rowCat);
            tr.setAttribute('data-type', rowType);
            tr.setAttribute('data-name', (m.name || '').toLowerCase());
            tr.setAttribute('data-code', (m.code || '').toLowerCase());
            tr.setAttribute('data-sku', (m.sku || '').toLowerCase());
            tr.style.borderBottom = '1px solid #f1f5f9';

            tr.innerHTML = `
                <td style="padding: 10px 14px; vertical-align: middle;">
                    <div style="display: flex; flex-direction: column; gap: 2px;">
                        <span style="font-size: 13px; font-weight: 700; color: #0f172a; line-height: 1.3;">
                            ${escapeHtml(m.name)}
                        </span>
                        <div style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: #64748b; margin-top: 2px;">
                            <span class="badge" style="font-size: 9.5px; font-weight: 700; padding: 1.5px 6px; border-radius: 4px; ${catColorStyle}">
                                ${escapeHtml(rowCat)}
                            </span>
                            ${m.type ? `<span class="fw-500" style="color: #475569;">• ${escapeHtml(m.type)}</span>` : ''}
                            ${m.brand ? `<span>• Merek: ${escapeHtml(m.brand)}</span>` : ''}
                            ${m.size ? `<span>• ${escapeHtml(m.size)}</span>` : ''}
                        </div>
                    </div>

                    <input type="hidden" name="items[${rowIndex}][material_id]" id="${rowId}-material-id" value="${m.id}">
                    <input type="hidden" name="items[${rowIndex}][custom_item_name]" id="${rowId}-custom-name" value="">
                    <input type="hidden" name="items[${rowIndex}][custom_item_unit]" id="${rowId}-custom-unit" value="">
                </td>
                <td style="display:none; padding: 7px 8px; text-align: center;" class="col-mr-only">-</td>
                <td style="display:none; padding: 7px 8px; text-align: center;" class="col-mr-only">-</td>
                <td style="padding: 8px 8px; text-align: center;">
                    <span class="badge" id="${rowId}-stock" style="font-size: 11.5px; font-weight: 700; padding: 4px 8px; border-radius: 5px; ${parseFloat(m.stock || 0) > 0 ? 'background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe;' : 'background:#fef2f2; color:#b91c1c; border:1px solid #fecaca;'}">
                        ${m.stock} ${escapeHtml(m.unit || '')}
                    </span>
                </td>
                <td style="padding: 8px 8px;">
                    <div style="display: flex; align-items: center; justify-content:center; gap: 6px;">
                        <input type="number" name="items[${rowIndex}][quantity]" id="${rowId}-qty" class="form-control qty-input text-center"
                               step="0.01" min="0" placeholder="0" value="${initialQty}" oninput="validateManualQty('${rowId}')"
                               style="width: 85px;" max="${m.stock}">
                        <span class="text-muted fw-600" id="${rowId}-unit" style="font-size: 11.5px; min-width: 24px;">${escapeHtml(m.unit || '')}</span>
                    </div>
                    <div id="${rowId}-warning" class="text-danger fw-600" style="font-size: 10px; margin-top: 3px; text-align:center; display: none;"></div>
                </td>
                <td style="padding: 8px 10px;">
                    <input type="text" name="items[${rowIndex}][notes]" class="form-control" placeholder="Keterangan keperluan..." value="${escapeHtml(initialNotes)}">
                </td>
                <td style="padding: 8px 6px; text-align: center;">
                    <button type="button" class="btn-clear-row" onclick="clearRowQty('${rowId}')" title="Kosongkan Nilai Kuantitas">
                        <x-lucide-rotate-ccw style="width:13px; height:13px;" />
                    </button>
                </td>
            `;

            tbody.appendChild(tr);
            rowIndex++;
            updateTableSummary();

            setTimeout(() => {
                const qInp = document.getElementById(rowId + '-qty');
                if (qInp) {
                    qInp.focus();
                    qInp.select();
                }
            }, 100);
        }

        function addCustomItemRow(initialName = '', initialQty = '', initialUnit = 'unit', initialNotes = '') {
            const tbody = document.getElementById('itemsBody');
            const emptyStockRow = document.getElementById('empty-stock-row');
            if (emptyStockRow) emptyStockRow.remove();

            const rowId = 'row-' + rowIndex;

            const tr = document.createElement('tr');
            tr.id = rowId;
            tr.className = 'material-data-row';
            tr.setAttribute('data-category', 'CUSTOM');
            tr.setAttribute('data-type', 'Item Custom');
            tr.setAttribute('data-name', initialName.toLowerCase());
            tr.setAttribute('data-code', '');
            tr.setAttribute('data-sku', '');
            tr.style.borderBottom = '1px solid #f1f5f9';

            tr.innerHTML = `
                <td style="padding: 10px 14px; vertical-align: middle;">
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span class="badge" style="background:#fef3c7; color:#92400e; border:1px solid #fde68a; font-size:10px; padding:2px 6px;">Non-Master</span>
                            <input type="text" class="form-control" placeholder="Ketik nama item custom / non-master..." value="${escapeHtml(initialName)}"
                                   style="height:32px; font-size:12.5px; font-weight:600; border-radius:5px;"
                                   oninput="onCustomItemNameInput(this, '${rowId}')">
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span style="font-size:11px; color:#64748b;">Satuan:</span>
                            <input type="text" id="${rowId}-custom-unit-input" class="form-control" placeholder="Contoh: sak, btg, roll" value="${escapeHtml(initialUnit)}"
                                   style="height: 26px; font-size: 11px; padding: 2px 6px; width: 110px; border-radius: 4px;"
                                   oninput="onCustomUnitChange(this, '${rowId}')">
                        </div>
                    </div>

                    <input type="hidden" name="items[${rowIndex}][material_id]" id="${rowId}-material-id" value="">
                    <input type="hidden" name="items[${rowIndex}][custom_item_name]" id="${rowId}-custom-name" value="${escapeHtml(initialName)}">
                    <input type="hidden" name="items[${rowIndex}][custom_item_unit]" id="${rowId}-custom-unit" value="${escapeHtml(initialUnit)}">
                </td>
                <td style="display:none; padding: 7px 8px; text-align: center;" class="col-mr-only">-</td>
                <td style="display:none; padding: 7px 8px; text-align: center;" class="col-mr-only">-</td>
                <td style="padding: 8px 8px; text-align: center;">
                    <span class="badge" id="${rowId}-stock" style="font-size: 11px; font-weight: 600; padding: 3px 7px; border-radius: 5px; background:#fef3c7; color:#92400e; border:1px solid #fde68a;">
                        Custom
                    </span>
                </td>
                <td style="padding: 8px 8px;">
                    <div style="display: flex; align-items: center; justify-content:center; gap: 6px;">
                        <input type="number" name="items[${rowIndex}][quantity]" id="${rowId}-qty" class="form-control qty-input text-center"
                               step="0.01" min="0" placeholder="0" value="${initialQty}" oninput="validateManualQty('${rowId}')"
                               style="width: 85px;">
                        <span class="text-muted fw-600" id="${rowId}-unit" style="font-size: 11.5px; min-width: 24px;">${escapeHtml(initialUnit)}</span>
                    </div>
                    <div id="${rowId}-warning" class="text-danger fw-600" style="font-size: 10px; margin-top: 3px; text-align:center; display: none;"></div>
                </td>
                <td style="padding: 8px 10px;">
                    <input type="text" name="items[${rowIndex}][notes]" class="form-control" placeholder="Keterangan keperluan..." value="${escapeHtml(initialNotes)}">
                </td>
                <td style="padding: 8px 6px; text-align: center;">
                    <button type="button" class="btn-clear-row" onclick="removeRow('${rowId}')" title="Hapus Baris Ini">
                        <x-lucide-trash-2 style="width:13px; height:13px; color:#ef4444;" />
                    </button>
                </td>
            `;

            tbody.appendChild(tr);
            rowIndex++;
            updateTableSummary();

            setTimeout(() => {
                const nameInp = tr.querySelector('input[type="text"]');
                if (nameInp) nameInp.focus();
            }, 100);
        }

        function onCustomItemNameInput(input, rowId) {
            const val = input.value.trim();
            const customHidden = document.getElementById(rowId + '-custom-name');
            if (customHidden) customHidden.value = val;
            const tr = document.getElementById(rowId);
            if (tr) tr.setAttribute('data-name', val.toLowerCase());
        }

        function clearRowQty(rowId) {
            const qtyInput = document.getElementById(rowId + '-qty');
            if (qtyInput) {
                qtyInput.value = '';
                qtyInput.classList.remove('is-invalid');
            }
            const warningEl = document.getElementById(rowId + '-warning');
            if (warningEl) {
                warningEl.style.display = 'none';
                warningEl.innerText = '';
            }
            const tr = document.getElementById(rowId);
            if (tr) {
                tr.classList.remove('row-active-qty');
            }
            updateTableSummary();
        }

        function addManualRow(preselectedId = null, qtyVal = '', isCustom = false, initialCustomName = '') {
            if (isCustom) {
                return addCustomItemRow(initialCustomName, qtyVal);
            }
            if (preselectedId) {
                return addMaterialRowFromMaster(preselectedId, qtyVal);
            }
            openAddMaterialModal();
        }

            

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function highlightMatch(text, query) {
            if (!query) return escapeHtml(text);
            const safeText = escapeHtml(text);
            const safeQuery = escapeHtml(query);
            const regex = new RegExp(`(${safeQuery.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
            return safeText.replace(regex, '<mark style="background:#fef08a;color:#713f12;padding:0 2px;border-radius:2px;font-weight:700;">$1</mark>');
        }

        function onMaterialSearchFocus(input, rowId) {
            activeAutocompleteRowId = rowId;
            renderMaterialSuggestions(input, rowId, input.value);
        }

        function onMaterialSearchInput(input, rowId) {
            activeAutocompleteRowId = rowId;
            const clearBtn = document.getElementById(rowId + '-clear-btn');
            if (clearBtn) clearBtn.style.display = input.value.trim() ? 'block' : 'none';

            // Check if user modified the name of an already linked master
            const matIdInput = document.getElementById(rowId + '-material-id');
            if (matIdInput && matIdInput.value) {
                const currentMaster = availableMaterials.find(m => m.id == matIdInput.value);
                if (currentMaster && currentMaster.name.trim().toLowerCase() !== input.value.trim().toLowerCase()) {
                    // Unlink master
                    matIdInput.value = '';
                    const customNameInput = document.getElementById(rowId + '-custom-name');
                    if (customNameInput) customNameInput.value = input.value.trim();
                }
            }

            renderMaterialSuggestions(input, rowId, input.value);
        }

        function positionSuggestionsBox(input) {
            const box = document.getElementById('material-suggestions-box');
            if (!box || !input) return;
            const rect = input.getBoundingClientRect();
            box.style.top = (rect.bottom + window.scrollY + 4) + 'px';
            box.style.left = (rect.left + window.scrollX) + 'px';
            box.style.width = Math.max(rect.width, 360) + 'px';
        }

        function renderMaterialSuggestions(input, rowId, query) {
            const box = document.getElementById('material-suggestions-box');
            if (!box) return;

            const q = (query || '').trim().toLowerCase();
            currentSuggestions = [];
            activeSuggestionIndex = -1;

            // Filter materials by name, code, or category
            let matched = availableMaterials.filter(m => {
                if (!q) return true;
                const name = (m.name || '').toLowerCase();
                const code = (m.code || '').toLowerCase();
                const cat = (m.category || '').toLowerCase();
                return name.includes(q) || code.includes(q) || cat.includes(q);
            });

            // Sort: 1. startsWith query name, 2. Alphabetical A-Z
            matched.sort((a, b) => {
                if (q) {
                    const aStarts = (a.name || '').toLowerCase().startsWith(q);
                    const bStarts = (b.name || '').toLowerCase().startsWith(q);
                    if (aStarts && !bStarts) return -1;
                    if (!aStarts && bStarts) return 1;
                }
                return (a.name || '').localeCompare(b.name || '', 'id', { sensitivity: 'base' });
            });

            matched = matched.slice(0, q ? 20 : 12);

            let html = '';
            let itemIdx = 0;

            if (matched.length > 0) {
                html += `<div style="padding:6px 12px;background:#f8fafc;border-bottom:1px solid #e2e8f0;font-size:11px;font-weight:700;color:#64748b;display:flex;justify-content:space-between;align-items:center;">
                    <span><x-lucide-boxes class="me-1 text-primary" /> Data Master Inventori (${matched.length} Ditemukan)</span>
                    <span style="font-size:10px;color:#94a3b8;font-weight:normal;">Urut A-Z</span>
                </div>`;

                matched.forEach(m => {
                    currentSuggestions.push({
                        type: 'master',
                        id: m.id,
                        name: m.name,
                        stock: m.stock,
                        unit: m.unit
                    });

                    const safeNameAttr = escapeHtml(m.name);
                    const isInStock = m.stock > 0;

                    html += `<div class="material-sugg-item" data-index="${itemIdx}" 
                                  onclick="selectMaterialSuggestion('${rowId}', ${m.id})" 
                                  style="padding:8px 12px;cursor:pointer;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;transition:background .15s;">
                        <div style="min-width:0;padding-right:10px;">
                            <div style="font-weight:600;font-size:12.5px;color:#0f172a;">
                                ${highlightMatch(m.name, q)}
                            </div>
                            <div class="text-muted" style="font-size:11px;display:flex;align-items:center;gap:6px;margin-top:2px;">
                                ${m.code ? `<code style="font-size:10px;background:#f1f5f9;padding:1px 4px;border-radius:3px;color:#475569;">${escapeHtml(m.code)}</code> &bull; ` : ''}
                                <span>Kategori: ${escapeHtml(m.category || 'Umum')}</span>
                            </div>
                        </div>
                        <div style="text-align:right;flex-shrink:0;">
                            <span class="badge" style="background:${isInStock ? '#eff6ff' : '#fef2f2'};color:${isInStock ? '#0284c7' : '#b91c1c'};border:1px solid ${isInStock ? '#bae6fd' : '#fecaca'};font-size:11px;font-weight:600;padding:2px 7px;">
                                Stok: ${m.stock} ${escapeHtml(m.unit || '')}
                            </span>
                        </div>
                    </div>`;
                    itemIdx++;
                });
            } else {
                html += `<div style="padding:16px;text-align:center;color:#94a3b8;font-size:12px;">
                    Tidak ada material master yang cocok dengan "<strong>${escapeHtml(q)}</strong>".
                </div>`;
            }

            box.innerHTML = html;
            positionSuggestionsBox(input);
            box.style.display = 'block';
        }

        function hideMaterialSuggestions() {
            const box = document.getElementById('material-suggestions-box');
            if (box) box.style.display = 'none';
            activeAutocompleteRowId = null;
            activeSuggestionIndex = -1;
            currentSuggestions = [];
        }

        function onMaterialSearchKeydown(e, rowId) {
            const box = document.getElementById('material-suggestions-box');
            if (!box || box.style.display === 'none') {
                if (e.key === 'ArrowDown') {
                    onMaterialSearchFocus(e.target, rowId);
                }
                return;
            }

            const items = box.querySelectorAll('.material-sugg-item');
            if (!items || items.length === 0) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeSuggestionIndex = (activeSuggestionIndex + 1) % items.length;
                highlightActiveSuggestion(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeSuggestionIndex = (activeSuggestionIndex - 1 + items.length) % items.length;
                highlightActiveSuggestion(items);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (activeSuggestionIndex >= 0 && activeSuggestionIndex < currentSuggestions.length) {
                    const selected = currentSuggestions[activeSuggestionIndex];
                    if (selected.type === 'master') {
                        selectMaterialSuggestion(rowId, selected.id);
                    } else {
                        selectAsCustomMaterial(rowId, selected.name);
                    }
                } else if (currentSuggestions.length > 0) {
                    const first = currentSuggestions[0];
                    if (first.type === 'master') {
                        selectMaterialSuggestion(rowId, first.id);
                    } else {
                        selectAsCustomMaterial(rowId, first.name);
                    }
                }
            } else if (e.key === 'Escape') {
                hideMaterialSuggestions();
            }
        }

        function highlightActiveSuggestion(items) {
            items.forEach((it, idx) => {
                if (idx === activeSuggestionIndex) {
                    it.style.backgroundColor = '#e0f2fe';
                    it.scrollIntoView({ block: 'nearest' });
                } else {
                    it.style.backgroundColor = '';
                }
            });
        }

        function selectMaterialSuggestion(rowId, materialId) {
            const m = availableMaterials.find(x => x.id == materialId);
            if (!m) return;

            const nameInput = document.getElementById(rowId + '-name-input');
            const matIdInput = document.getElementById(rowId + '-material-id');
            const customNameInput = document.getElementById(rowId + '-custom-name');
            const customUnitInput = document.getElementById(rowId + '-custom-unit');
            const customBadgeWrap = document.getElementById(rowId + '-custom-badge-wrap');
            const clearBtn = document.getElementById(rowId + '-clear-btn');
            const stockBadge = document.getElementById(rowId + '-stock');
            const unitLabel = document.getElementById(rowId + '-unit');
            const qtyInput = document.getElementById(rowId + '-qty');
            const tr = document.getElementById(rowId);

            if (tr) {
                tr.setAttribute('data-category', m.category_name || m.category || 'UMUM');
                tr.setAttribute('data-name', (m.name || '').toLowerCase());
                tr.setAttribute('data-code', (m.code || '').toLowerCase());
            }

            if (nameInput) nameInput.value = m.name;
            if (matIdInput) matIdInput.value = m.id;
            if (customNameInput) customNameInput.value = '';
            if (customUnitInput) customUnitInput.value = '';
            if (customBadgeWrap) customBadgeWrap.style.display = 'none';
            if (clearBtn) clearBtn.style.display = 'block';

            if (stockBadge) {
                stockBadge.style.background = m.stock > 0 ? '#eff6ff' : '#fef2f2';
                stockBadge.style.color = m.stock > 0 ? '#2563eb' : '#b91c1c';
                stockBadge.style.border = m.stock > 0 ? '1px solid #bfdbfe' : '1px solid #fecaca';
                stockBadge.innerText = m.stock + ' ' + (m.unit || '');
            }

            if (unitLabel) unitLabel.innerText = m.unit || 'unit';
            if (qtyInput) {
                qtyInput.max = m.stock;
                validateManualQty(rowId);
                setTimeout(() => {
                    qtyInput.focus();
                    qtyInput.select();
                }, 80);
            }

            hideMaterialSuggestions();
            updateTableSummary();
        }

        function selectAsCustomMaterial(rowId, customName) {
            const nameInput = document.getElementById(rowId + '-name-input');
            const matIdInput = document.getElementById(rowId + '-material-id');
            const customNameInput = document.getElementById(rowId + '-custom-name');
            const customUnitInput = document.getElementById(rowId + '-custom-unit');
            const customUnitField = document.getElementById(rowId + '-custom-unit-input');
            const customBadgeWrap = document.getElementById(rowId + '-custom-badge-wrap');
            const clearBtn = document.getElementById(rowId + '-clear-btn');
            const stockBadge = document.getElementById(rowId + '-stock');
            const unitLabel = document.getElementById(rowId + '-unit');
            const qtyInput = document.getElementById(rowId + '-qty');

            const trimmedName = customName.trim();
            if (nameInput) nameInput.value = trimmedName;
            if (matIdInput) matIdInput.value = '';
            if (customNameInput) customNameInput.value = trimmedName;
            const chosenUnit = (customUnitField ? customUnitField.value.trim() : 'unit') || 'unit';
            if (customUnitInput) customUnitInput.value = chosenUnit;
            if (customBadgeWrap) customBadgeWrap.style.display = 'flex';
            if (clearBtn) clearBtn.style.display = 'block';

            if (stockBadge) {
                stockBadge.style.background = '#fef3c7';
                stockBadge.style.color = '#92400e';
                stockBadge.style.border = '1px solid #fde68a';
                stockBadge.innerText = 'Custom';
            }

            if (unitLabel) unitLabel.innerText = chosenUnit;
            if (qtyInput) {
                qtyInput.removeAttribute('max');
                validateManualQty(rowId);
                setTimeout(() => {
                    if (customUnitField && (!customUnitField.value || customUnitField.value === 'unit')) {
                        customUnitField.focus();
                        customUnitField.select();
                    } else {
                        qtyInput.focus();
                        qtyInput.select();
                    }
                }, 80);
            }

            hideMaterialSuggestions();
            updateTableSummary();
        }

        function clearMaterialRowSelection(rowId) {
            const nameInput = document.getElementById(rowId + '-name-input');
            const matIdInput = document.getElementById(rowId + '-material-id');
            const customNameInput = document.getElementById(rowId + '-custom-name');
            const customUnitInput = document.getElementById(rowId + '-custom-unit');
            const customBadgeWrap = document.getElementById(rowId + '-custom-badge-wrap');
            const clearBtn = document.getElementById(rowId + '-clear-btn');
            const stockBadge = document.getElementById(rowId + '-stock');
            const unitLabel = document.getElementById(rowId + '-unit');
            const qtyInput = document.getElementById(rowId + '-qty');

            if (nameInput) {
                nameInput.value = '';
                nameInput.focus();
            }
            if (matIdInput) matIdInput.value = '';
            if (customNameInput) customNameInput.value = '';
            if (customUnitInput) customUnitInput.value = '';
            if (customBadgeWrap) customBadgeWrap.style.display = 'none';
            if (clearBtn) clearBtn.style.display = 'none';

            if (stockBadge) {
                stockBadge.style.background = '#f1f5f9';
                stockBadge.style.color = '#64748b';
                stockBadge.style.border = '1px solid #cbd5e1';
                stockBadge.innerText = '-';
            }

            if (unitLabel) unitLabel.innerText = '-';
            if (qtyInput) {
                qtyInput.removeAttribute('max');
                validateManualQty(rowId);
            }

            renderMaterialSuggestions(nameInput, rowId, '');
            updateTableSummary();
        }

        function onCustomUnitChange(input, rowId) {
            const unitLabel = document.getElementById(rowId + '-unit');
            const customUnitHidden = document.getElementById(rowId + '-custom-unit');
            const val = input.value.trim() || 'unit';
            if (unitLabel) unitLabel.innerText = val;
            if (customUnitHidden) customUnitHidden.value = val;
        }

        // Global listeners for positioning and closing suggestions dropdown
        document.addEventListener('click', function(e) {
            const box = document.getElementById('material-suggestions-box');
            if (!box || box.style.display === 'none') return;
            if (!box.contains(e.target) && !e.target.classList.contains('material-autocomplete-input')) {
                hideMaterialSuggestions();
            }
        });

        window.addEventListener('scroll', function() {
            const box = document.getElementById('material-suggestions-box');
            if (box && box.style.display !== 'none' && activeAutocompleteRowId) {
                const inp = document.getElementById(activeAutocompleteRowId + '-name-input');
                if (inp) positionSuggestionsBox(inp);
            }
        }, true);

        window.addEventListener('resize', function() {
            const box = document.getElementById('material-suggestions-box');
            if (box && box.style.display !== 'none' && activeAutocompleteRowId) {
                const inp = document.getElementById(activeAutocompleteRowId + '-name-input');
                if (inp) positionSuggestionsBox(inp);
            }
        });

        function validateManualQty(rowId) {
            const qtyInput = document.getElementById(rowId + '-qty');
            if (!qtyInput) return;
            const max = parseFloat(qtyInput.max || 0);
            const val = parseFloat(qtyInput.value || 0);
            const warningEl = document.getElementById(rowId + '-warning');

            if (max > 0 && val > max) {
                qtyInput.classList.add('is-invalid');
                warningEl.style.display = 'block';
                warningEl.innerHTML = `<x-lucide-triangle-alert class="me-1" /> Max: ${max}`;
            } else {
                qtyInput.classList.remove('is-invalid');
                warningEl.style.display = 'none';
            }
            updateTableSummary();
        }

        function removeRow(rowId) {
            const row = document.getElementById(rowId);
            if (row) {
                row.remove();
            }
            const tbody = document.getElementById('itemsBody');
            if (tbody.children.length === 0) {
                if (currentMode === 'manual') {
                    addManualRow();
                } else {
                    document.getElementById('mrEmptyState').style.display = 'block';
                }
            }
            updateTableSummary();
        }

        let currentActiveCategory = 'all';

        function initAccordionToggles() {
            // Level 1: Category Toggle
            document.querySelectorAll('#itemsBody tr.group-toggle').forEach(row => {
                row.onclick = function(e) {
                    if (e.target.closest('button') || e.target.closest('a') || e.target.closest('input')) return;
                    const groupClass = this.getAttribute('data-group');
                    const isCollapsed = this.classList.contains('collapsed');
                    const chev = this.querySelector('.group-chev');

                    if (isCollapsed) {
                        this.classList.remove('collapsed');
                        if (chev) chev.style.transform = 'rotate(0deg)';
                        document.querySelectorAll('#itemsBody tr.' + groupClass + '.subgroup-toggle').forEach(sub => {
                            sub.style.display = '';
                            const subKey = sub.getAttribute('data-group');
                            if (!sub.classList.contains('collapsed')) {
                                document.querySelectorAll('#itemsBody tr.subgroup-rows.' + subKey).forEach(itemRow => {
                                    itemRow.style.display = '';
                                });
                            }
                        });
                    } else {
                        this.classList.add('collapsed');
                        if (chev) chev.style.transform = 'rotate(-90deg)';
                        document.querySelectorAll('#itemsBody tr.' + groupClass).forEach(r => {
                            r.style.display = 'none';
                        });
                    }
                };
            });

            // Level 2: Subgroup Toggle
            document.querySelectorAll('#itemsBody tr.subgroup-toggle').forEach(row => {
                row.onclick = function(e) {
                    if (e.target.closest('button') || e.target.closest('a') || e.target.closest('input')) return;
                    const subKey = this.getAttribute('data-group');
                    const isCollapsed = this.classList.contains('collapsed');
                    const chev = this.querySelector('.subgroup-chev');

                    if (isCollapsed) {
                        this.classList.remove('collapsed');
                        if (chev) chev.style.transform = 'rotate(0deg)';
                        document.querySelectorAll('#itemsBody tr.subgroup-rows.' + subKey).forEach(itemRow => {
                            itemRow.style.display = '';
                        });
                    } else {
                        this.classList.add('collapsed');
                        if (chev) chev.style.transform = 'rotate(-90deg)';
                        document.querySelectorAll('#itemsBody tr.subgroup-rows.' + subKey).forEach(itemRow => {
                            itemRow.style.display = 'none';
                        });
                    }
                };
            });
        }

        function expandAllAccordion() {
            document.querySelectorAll('#itemsBody tr.group-toggle').forEach(cat => {
                cat.classList.remove('collapsed');
                cat.style.display = '';
                const chev = cat.querySelector('.group-chev');
                if (chev) chev.style.transform = 'rotate(0deg)';
            });
            document.querySelectorAll('#itemsBody tr.subgroup-toggle').forEach(sub => {
                sub.classList.remove('collapsed');
                sub.style.display = '';
                const chev = sub.querySelector('.subgroup-chev');
                if (chev) chev.style.transform = 'rotate(0deg)';
            });
            document.querySelectorAll('#itemsBody tr.material-data-row').forEach(row => {
                row.style.display = '';
            });
            const indicator = document.getElementById('rowCountIndicator');
            if (indicator) {
                const total = document.querySelectorAll('#itemsBody tr.material-data-row').length;
                indicator.innerText = total + ' item terdaftar (Semua Terbuka)';
            }
        }

        function collapseAllAccordion() {
            document.querySelectorAll('#itemsBody tr.group-toggle').forEach(cat => {
                cat.classList.add('collapsed');
                const chev = cat.querySelector('.group-chev');
                if (chev) chev.style.transform = 'rotate(-90deg)';
            });
            document.querySelectorAll('#itemsBody tr.subgroup-toggle').forEach(sub => {
                sub.classList.add('collapsed');
                sub.style.display = 'none';
                const chev = sub.querySelector('.subgroup-chev');
                if (chev) chev.style.transform = 'rotate(-90deg)';
            });
            document.querySelectorAll('#itemsBody tr.material-data-row').forEach(row => {
                const qtyInput = row.querySelector('.qty-input');
                const qty = parseFloat(qtyInput?.value || 0);
                if (qty > 0) {
                    row.style.display = '';
                    row.classList.forEach(c => {
                        if (c.startsWith('sub-')) {
                            const sub = document.querySelector(`#itemsBody tr.subgroup-toggle[data-group="${c}"]`);
                            if (sub) {
                                sub.classList.remove('collapsed');
                                sub.style.display = '';
                                const sc = sub.querySelector('.subgroup-chev');
                                if (sc) sc.style.transform = 'rotate(0deg)';
                            }
                        }
                        if (c.startsWith('group-cat-')) {
                            const cat = document.querySelector(`#itemsBody tr.group-toggle[data-group="${c}"]`);
                            if (cat) {
                                cat.classList.remove('collapsed');
                                const cc = cat.querySelector('.group-chev');
                                if (cc) cc.style.transform = 'rotate(0deg)';
                            }
                        }
                    });
                } else {
                    row.style.display = 'none';
                }
            });
            const indicator = document.getElementById('rowCountIndicator');
            if (indicator) {
                const total = document.querySelectorAll('#itemsBody tr.material-data-row').length;
                indicator.innerText = total + ' item terdaftar (Tertutup)';
            }
        }

        function filterByCategory(categoryName) {
            currentActiveCategory = categoryName;
            document.querySelectorAll('.btn-category-tab').forEach(btn => btn.classList.remove('active'));

            const targetTab = categoryName === 'all'
                ? document.getElementById('tab-category-all')
                : (document.querySelector(`[onclick*="filterByCategory('${categoryName}')"]`) || document.getElementById('tab-category-' + categoryName.toLowerCase().replace(/[^a-z0-9]/g, '-')));

            if (targetTab) {
                targetTab.classList.add('active');
            }

            const searchVal = document.getElementById('tableSearchInput')?.value || '';
            filterTableRows(searchVal);
        }

        function clearTableSearch() {
            const input = document.getElementById('tableSearchInput');
            if (input) {
                input.value = '';
                input.focus();
            }
            const clearBtn = document.getElementById('clearSearchBtn');
            if (clearBtn) clearBtn.style.display = 'none';
            filterTableRows('');
        }

        function filterTableRows(query) {
            const q = (query || '').toLowerCase().trim();
            const clearBtn = document.getElementById('clearSearchBtn');
            if (clearBtn) {
                clearBtn.style.display = q ? 'block' : 'none';
            }

            const rows = document.querySelectorAll('#itemsBody tr.material-data-row');
            const categoryDividers = document.querySelectorAll('#itemsBody tr.category-divider-row');
            const subgroupDividers = document.querySelectorAll('#itemsBody tr.subgroup-divider-row');

            let visibleCount = 0;
            const matchedSubgroups = new Set();
            const matchedCategories = new Set();

            if (q.length > 0) {
                rows.forEach(tr => {
                    const itemCategory = (tr.getAttribute('data-category') || 'UMUM').trim();
                    const itemType = (tr.getAttribute('data-type') || '').trim();
                    const itemName = (tr.getAttribute('data-name') || '').trim();
                    const itemCode = (tr.getAttribute('data-code') || '').trim();
                    const itemSku = (tr.getAttribute('data-sku') || '').trim();

                    const categoryMatch = (currentActiveCategory === 'all' || itemCategory.toLowerCase() === currentActiveCategory.toLowerCase());

                    const words = itemName.split(/[\s,.\/-]+/).filter(Boolean);
                    const startsWithWord = words.some(w => w.startsWith(q));
                    const containsMatch = itemName.includes(q) || itemCode.includes(q) || itemSku.includes(q) || itemCategory.toLowerCase().includes(q) || itemType.toLowerCase().includes(q);
                    const searchMatch = startsWithWord || containsMatch;

                    const isVisible = categoryMatch && searchMatch;
                    tr.style.display = isVisible ? '' : 'none';

                    if (isVisible) {
                        visibleCount++;
                        matchedCategories.add(itemCategory.toUpperCase());
                        tr.classList.forEach(cls => {
                            if (cls.startsWith('sub-')) matchedSubgroups.add(cls);
                        });
                    }
                });

                categoryDividers.forEach(cat => {
                    const catHeader = (cat.getAttribute('data-cat-header') || '').trim().toUpperCase();
                    const shouldShow = matchedCategories.has(catHeader);
                    cat.style.display = shouldShow ? '' : 'none';
                    if (shouldShow) {
                        cat.classList.remove('collapsed');
                        const chev = cat.querySelector('.group-chev');
                        if (chev) chev.style.transform = 'rotate(0deg)';
                    }
                });

                subgroupDividers.forEach(sub => {
                    const subGroup = sub.getAttribute('data-group');
                    const shouldShow = matchedSubgroups.has(subGroup);
                    sub.style.display = shouldShow ? '' : 'none';
                    if (shouldShow) {
                        sub.classList.remove('collapsed');
                        const chev = sub.querySelector('.subgroup-chev');
                        if (chev) chev.style.transform = 'rotate(0deg)';
                    }
                });
            } else {
                categoryDividers.forEach(cat => {
                    const catHeader = (cat.getAttribute('data-cat-header') || '').trim().toUpperCase();
                    const isCatMatch = (currentActiveCategory === 'all' || catHeader === currentActiveCategory.toUpperCase());
                    cat.style.display = isCatMatch ? '' : 'none';

                    const groupClass = cat.getAttribute('data-group');
                    const isCatCollapsed = cat.classList.contains('collapsed');

                    document.querySelectorAll('#itemsBody tr.' + groupClass + '.subgroup-toggle').forEach(sub => {
                        const isSubCollapsed = sub.classList.contains('collapsed');
                        sub.style.display = (isCatMatch && !isCatCollapsed) ? '' : 'none';

                        const subKey = sub.getAttribute('data-group');
                        document.querySelectorAll('#itemsBody tr.subgroup-rows.' + subKey).forEach(itemRow => {
                            const isVisible = isCatMatch && !isCatCollapsed && !isSubCollapsed;
                            itemRow.style.display = isVisible ? '' : 'none';
                            if (isCatMatch) visibleCount++;
                        });
                    });
                });
            }

            const indicator = document.getElementById('rowCountIndicator');
            if (indicator) {
                if (q.length > 0 || currentActiveCategory !== 'all') {
                    indicator.innerText = visibleCount + ' item ditampilkan';
                } else {
                    indicator.innerText = visibleCount + ' item terdaftar';
                }
            }
        }

        function updateTableSummary() {
            const rows = document.querySelectorAll('#itemsBody tr');
            let totalItems = 0;
            let totalQuantity = 0;
            let hasInvalid = false;

            rows.forEach(tr => {
                const qtyInput = tr.querySelector('.qty-input');
                if (qtyInput) {
                    const q = parseFloat(qtyInput.value || 0);
                    if (q > 0) {
                        totalItems++;
                        totalQuantity += q;
                    }
                    if (qtyInput.classList.contains('is-invalid')) {
                        hasInvalid = true;
                    }
                }
            });

            document.getElementById('sumItemCount').innerText = totalItems;
            document.getElementById('sumQuantityCount').innerText = totalQuantity.toLocaleString('id-ID', { maximumFractionDigits: 2 });
            document.getElementById('rowCountIndicator').innerText = rows.length + ' item terdaftar';

            const badge = document.getElementById('statusValidationBadge');
            if (hasInvalid) {
                badge.innerHTML = `<span class="badge" style="background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; font-size: 10.5px; padding:2px 7px; border-radius:12px;"><x-lucide-circle-x class="me-1" /> Melebihi Batas</span>`;
            } else if (totalItems === 0) {
                badge.innerHTML = `<span class="badge" style="background:#f1f5f9; color:#64748b; border:1px solid #cbd5e1; font-size: 10.5px; padding:2px 7px; border-radius:12px;">Belum Ada Kuantitas</span>`;
            } else {
                badge.innerHTML = `<span class="badge" style="background:#ecfdf5; color:#047857; border:1px solid #a7f3d0; font-size: 10.5px; padding:2px 7px; border-radius:12px;"><x-lucide-circle-check class="me-1" /> Siap (${totalItems} item)</span>`;
            }

            // Mobile Floating Action Bar
            const mobileBar = document.getElementById('mobileUsageSummaryBar');
            const mobileCount = document.getElementById('mobileUsageItemCount');
            if (mobileBar && mobileCount) {
                mobileCount.innerText = totalItems;
                if (window.innerWidth <= 992 && totalItems > 0 && !hasInvalid) {
                    mobileBar.style.display = 'flex';
                } else {
                    mobileBar.style.display = 'none';
                }
            }
        }

        function validateBeforeSubmit(form) {
            // Auto-link any typed name if material_id was not explicitly clicked
            const manualRows = document.querySelectorAll('#itemsBody tr');
            manualRows.forEach(tr => {
                const nameInput = tr.querySelector('.material-autocomplete-input');
                const matIdInput = tr.querySelector('input[name*="[material_id]"]');
                const customNameInput = tr.querySelector('input[name*="[custom_item_name]"]');
                const customUnitInput = tr.querySelector('input[name*="[custom_item_unit]"]');

                if (nameInput && nameInput.value.trim()) {
                    const typed = nameInput.value.trim();
                    if (!matIdInput.value && !customNameInput.value) {
                        const exactMatch = availableMaterials.find(m => m.name.trim().toLowerCase() === typed.toLowerCase());
                        if (exactMatch) {
                            matIdInput.value = exactMatch.id;
                        } else {
                            customNameInput.value = typed;
                            if (!customUnitInput.value) customUnitInput.value = 'unit';
                        }
                    }
                }
            });

            if (currentMode === 'mr') {
                const mrSelect = document.getElementById('mrSelect');
                if (!mrSelect.value) {
                    alert('Silakan pilih Nomor Permintaan (MR) yang telah disetujui terlebih dahulu.');
                    mrSelect.focus();
                    return false;
                }
            }

            const rows = document.querySelectorAll('#itemsBody tr');
            if (rows.length === 0) {
                alert('Daftar material yang dikeluarkan tidak boleh kosong.');
                return false;
            }

            let hasValidQty = false;
            let hasError = false;

            rows.forEach(tr => {
                const qtyInput = tr.querySelector('.qty-input');
                if (qtyInput) {
                    const qty = parseFloat(qtyInput.value || 0);
                    if (qty > 0) hasValidQty = true;

                    if (qtyInput.classList.contains('is-invalid')) {
                        hasError = true;
                    }
                }
            });

            if (!hasValidQty) {
                rows.forEach(tr => tr.querySelectorAll('input, select').forEach(inp => inp.disabled = false));
                alert('Silakan masukkan jumlah pengeluaran minimal 1 material dengan kuantitas lebih dari 0.');
                return false;
            }

            if (hasError) {
                rows.forEach(tr => tr.querySelectorAll('input, select').forEach(inp => inp.disabled = false));
                alert('Terdapat input kuantitas yang melebihi kuota persetujuan MR atau stok fisik gudang. Mohon perbaiki baris bertanda merah.');
                return false;
            }

            // Saring dan nonaktifkan baris yang kuantitasnya kosong / 0 agar tidak dikirim ke server
            rows.forEach(tr => {
                const qtyInput = tr.querySelector('.qty-input');
                const qty = parseFloat(qtyInput ? qtyInput.value : 0) || 0;
                if (qty <= 0) {
                    tr.querySelectorAll('input, select').forEach(inp => inp.disabled = true);
                }
            });

            const btn = form.querySelector('button[type=submit]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = `<x-lucide-loader-2 class="me-1 animate-spin" /> Menyimpan...`;
            }

            return true;
        }

        window.addEventListener('resize', function() {
            updateTableSummary();
        });

        const oldItems = @json(old('items') ?? []);

        function initMaterialRows() {
            // 1. Pulihkan data submit sebelumnya jika ada (misal redirect error validasi)
            if (oldItems && oldItems.length > 0) {
                const tbody = document.getElementById('itemsBody');
                tbody.innerHTML = '';
                rowIndex = 0;
                oldItems.forEach(it => {
                    if (it.is_custom || it.custom_item_name) {
                        addManualRow(null, it.quantity || '', true, it.custom_item_name || '');
                    } else if (it.material_id) {
                        addManualRow(it.material_id, it.quantity || '');
                    }
                });
            }

            updateTableSummary();
        }

        // Initialize on DOM ready or immediate if already loaded
        function setupPage() {
            const manualButtonsGroup = document.getElementById('manualButtonsGroup');
            const mrQuickActions = document.getElementById('mrQuickActions');
            const mrCols = document.querySelectorAll('.col-mr-only');
            const emptyState = document.getElementById('mrEmptyState');
            if (manualButtonsGroup) manualButtonsGroup.style.display = 'inline-flex';
            if (mrQuickActions) mrQuickActions.style.display = 'none';
            if (emptyState) emptyState.style.display = 'none';
            mrCols.forEach(el => el.style.display = 'none');
            initMaterialRows();
            initAccordionToggles();

            // Auto-expand any active/filled rows
            document.querySelectorAll('#itemsBody tr.material-data-row').forEach(row => {
                const qtyInput = row.querySelector('.qty-input');
                const qty = parseFloat(qtyInput?.value || 0);
                if (qty > 0) {
                    row.style.display = '';
                    row.classList.forEach(c => {
                        if (c.startsWith('sub-')) {
                            const sub = document.querySelector(`#itemsBody tr.subgroup-toggle[data-group="${c}"]`);
                            if (sub) {
                                sub.classList.remove('collapsed');
                                sub.style.display = '';
                                const sc = sub.querySelector('.subgroup-chev');
                                if (sc) sc.style.transform = 'rotate(0deg)';
                            }
                        }
                        if (c.startsWith('group-cat-')) {
                            const cat = document.querySelector(`#itemsBody tr.group-toggle[data-group="${c}"]`);
                            if (cat) {
                                cat.classList.remove('collapsed');
                                const cc = cat.querySelector('.group-chev');
                                if (cc) cc.style.transform = 'rotate(0deg)';
                            }
                        }
                    });
                }
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupPage);
        } else {
            setupPage();
        }
    </script>
    @endpush
</x-app-layout>
