<x-app-layout>
    <x-slot name="title">Inventaris Material & Gudang</x-slot>

    @php
        // Summary KPI Computations
        $totalMatVariants = $categoriesData->sum(fn($c) => $c->materials->count());
        $totalMatStock = $categoriesData->sum(fn($c) => $c->materials->sum(fn($m) => $m->inventories->sum('quantity')));

        // Stock Level Stratification
        $safeQty = 0; $safeCount = 0;
        $lowQty = 0; $lowCount = 0;
        $critQty = 0; $critCount = 0;

        foreach ($categoriesData as $c) {
            foreach ($c->materials as $m) {
                foreach ($m->inventories as $inv) {
                    $qty = (float) $inv->quantity;
                    $min = (float) $inv->min_stock;
                    if ($qty <= 0) {
                        $critQty += $qty;
                        $critCount++;
                    } elseif ($min > 0 && $qty <= $min) {
                        $lowQty += $qty;
                        $lowCount++;
                    } else {
                        $safeQty += $qty;
                        $safeCount++;
                    }
                }
            }
        }

        // Tool KPI Computations
        $totalToolsCount = $toolsCategoriesData->sum(fn($c) => $c->tools->count());
        $totalToolStockTotal = $toolsCategoriesData->sum(fn($c) => $c->tools->sum('stock_total'));
        $totalToolAvailable = $toolsCategoriesData->sum(fn($c) => $c->tools->sum('stock_available'));
        $totalToolBorrowed = $toolsCategoriesData->sum(fn($c) => $c->tools->sum('stock_borrowed'));
        $totalToolMaintenance = $toolsCategoriesData->sum(fn($c) => $c->tools->sum('stock_maintenance'));

        // Collect all category keys for Alpine openGroups
        $allCategoryKeys = [];
        foreach ($categoriesData as $cat) {
            $allCategoryKeys[] = 'cat-mat-' . $cat->id;
        }
        foreach ($toolsCategoriesData as $tCat) {
            $allCategoryKeys[] = 'cat-tool-' . $tCat->id;
        }

        $currentWhObj = is_numeric($selectedWarehouseId ?? null) ? $accessibleWarehouses->firstWhere('id', (int)$selectedWarehouseId) : null;
    @endphp

    @push('styles')
    <style>
        /* ===== MODERN INVENTORY WORKSPACE STYLING ===== */
        .inv-header-container {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 18px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.04);
            margin-bottom: 20px;
        }

        .inv-header-left-col {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }

        .inv-header-icon-box {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: #fff7ed;
            border: 1px solid #fed7aa;
            box-shadow: 0 1px 3px rgba(234, 88, 12, 0.08);
        }

        .inv-header-icon-box img {
            width: 26px;
            height: 26px;
            object-fit: contain;
            display: block;
        }

        .inv-header-title {
            font-size: 21px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.25;
            margin: 0;
            letter-spacing: -0.02em;
        }

        .inv-header-sub {
            font-size: 12px;
            color: #64748b;
            margin: 3px 0 0 0;
            font-weight: 500;
        }

        .inv-header-right-col {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .btn-tambah-material-cta {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 18px;
            border-radius: 10px;
            background: #ea580c;
            color: #ffffff !important;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 3px 10px rgba(234, 88, 12, 0.25);
            border: 1px solid #c2410c;
            transition: all 0.15s ease;
            white-space: nowrap;
        }

        .btn-tambah-material-cta:hover {
            background: #c2410c;
            box-shadow: 0 5px 14px rgba(234, 88, 12, 0.35);
            transform: translateY(-1px);
            color: #ffffff !important;
        }

        .btn-tambah-material-cta svg {
            width: 14px !important;
            height: 14px !important;
            color: #ffffff !important;
            stroke: #ffffff !important;
        }

        .btn-katalog-material-cta {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 18px;
            border-radius: 10px;
            background: #f1f5f9;
            color: #334155 !important;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            border: 1px solid #cbd5e1;
            transition: all 0.15s ease;
            white-space: nowrap;
        }

        .btn-katalog-material-cta:hover {
            background: #e2e8f0;
            color: #0f172a !important;
        }

        .btn-katalog-material-cta svg {
            width: 14px !important;
            height: 14px !important;
            color: #64748b !important;
        }

        @media (max-width: 640px) {
            .inv-header-container {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
                padding: 14px;
            }
            .inv-header-right-col {
                width: 100%;
            }
            .btn-tambah-material-cta,
            .btn-katalog-material-cta {
                width: 100%;
                justify-content: center;
            }
        }

        .inv-metric-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 16px 18px 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 10px -2px rgba(15, 23, 42, 0.04);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .inv-metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px -4px rgba(15, 23, 42, 0.08);
            border-color: #cbd5e1;
        }

        .inv-metric-card.card-blue {
            background: linear-gradient(180deg, #f0f7ff 0%, #ffffff 100%);
            border-color: #dbeafe;
        }

        .inv-metric-card.card-green {
            background: linear-gradient(180deg, #f0fdf4 0%, #ffffff 100%);
            border-color: #dcfce7;
        }

        .inv-metric-card.card-amber {
            background: linear-gradient(180deg, #fffbeb 0%, #ffffff 100%);
            border-color: #fef3c7;
        }

        .inv-metric-card.card-rose {
            background: linear-gradient(180deg, #fef2f2 0%, #ffffff 100%);
            border-color: #fee2e2;
        }

        .metric-lens-box {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        /* Sparkline Waves */
        .sparkline-svg {
            width: 100%;
            height: 32px;
            margin-top: 6px;
            overflow: visible;
        }

        /* Status Pills (Matching Mockup) */
        .status-dot-pill {
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            line-height: 1.2;
            white-space: nowrap;
        }

        .status-dot-pill .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .pill-safe {
            background: #ecfdf5;
            color: #059669;
            border: 1px solid #a7f3d0;
        }
        .pill-safe .dot { background: #10b981; }

        .pill-low {
            background: #fffbeb;
            color: #d97706;
            border: 1px solid #fde68a;
        }
        .pill-low .dot { background: #f59e0b; }

        .pill-critical {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        .pill-critical .dot { background: #ef4444; }

        /* Modern Table */
        .inv-data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .inv-data-table th {
            background: #f8fafc;
            color: #64748b;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.35px;
            padding: 10px 14px;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        .inv-data-table td {
            font-size: 12px;
            padding: 11px 14px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        .inv-data-table tr:hover td {
            background: #f8fafc;
        }

        /* Category Accordion Header Row */
        .category-accordion-row td {
            background: #f1f5f9 !important;
            padding: 8px 14px !important;
            border-bottom: 1px solid #e2e8f0 !important;
            border-top: 1px solid #e2e8f0 !important;
            cursor: pointer;
            user-select: none;
        }

        .category-accordion-row:hover td {
            background: #e2e8f0 !important;
        }

        /* Action Buttons */
        .btn-stock-card {
            padding: 4px 10px;
            border-radius: 7px;
            font-size: 11px;
            font-weight: 600;
            background: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .btn-stock-card:hover {
            border-color: #ea580c;
            color: #ea580c;
            background: #fff7ed;
        }

        /* Tidy Modern Dropdown Selects */
        .inv-filter-select {
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            background-color: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 9999px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            color: #1e293b !important;
            line-height: 1.5 !important;
            padding: 7px 36px 7px 16px !important;
            cursor: pointer !important;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
            transition: all 0.15s ease !important;
            white-space: nowrap !important;
        }

        .inv-select-warehouse {
            min-width: 220px !important;
        }

        .inv-select-status {
            min-width: 145px !important;
        }

        .inv-filter-select:hover {
            border-color: #94a3b8 !important;
            background-color: #f8fafc !important;
        }

        .inv-filter-select:focus {
            border-color: #ea580c !important;
            outline: none !important;
            box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.12) !important;
            background-color: #ffffff !important;
        }
    </style>
    @endpush

    <div x-data="{ 
            activeTab: '{{ request('item_type') === 'tool' ? 'alat' : 'material' }}', 
            searchQuery: '{{ addslashes(request('search', '')) }}',
            openGroups: {{ json_encode($allCategoryKeys) }},
            toggleGroup(group) {
                if (this.openGroups.includes(group)) {
                    this.openGroups = this.openGroups.filter(g => g !== group);
                } else {
                    this.openGroups.push(group);
                }
            },
            expandAll() { 
                this.openGroups = {{ json_encode($allCategoryKeys) }}; 
            },
            collapseAll() { 
                this.openGroups = []; 
            },
            matchesSearch(name, sku, spec, cat) {
                if (!this.searchQuery) return true;
                const q = this.searchQuery.toLowerCase();
                return (name && name.toLowerCase().includes(q)) ||
                       (sku && sku.toLowerCase().includes(q)) ||
                       (spec && spec.toLowerCase().includes(q)) ||
                       (cat && cat.toLowerCase().includes(q));
            }
        }" 
        class="w-full max-w-[1600px] mx-auto px-3 sm:px-6 py-5 space-y-5">

        {{-- ================= 1. HEADER (PERSIS MOCKUP) ================= --}}
        <div class="inv-header-container">
            {{-- Left Title & Icon --}}
            <div class="inv-header-left-col">
                <div class="inv-header-icon-box">
                    <img src="{{ asset('images/inventory-icon.png') }}" alt="Inventaris Material" class="w-6 h-6 sm:w-7 sm:h-7 object-contain" />
                </div>
                <div class="inv-header-title-wrap">
                    <h1 class="inv-header-title">Inventaris Material</h1>
                    <p class="inv-header-sub">Kelola persediaan material di seluruh gudang dengan mudah dan akurat.</p>
                </div>
            </div>

            @can('create materials')
            <div class="inv-header-right-col">
                <a href="{{ route('materials.create') }}" class="btn-tambah-material-cta">
                    <x-lucide-plus />
                    <span>Tambah Material</span>
                </a>
            </div>
            @endcan
        </div>

        {{-- ================= 2. 4 TOP METRIC CARDS DENGAN MICRO-SPARKLINE SVG ================= --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            {{-- CARD 1: TOTAL MATERIAL --}}
            <div class="inv-metric-card card-blue">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Material</span>
                        <div class="mt-1 flex items-baseline gap-2">
                            <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 tabular-nums">
                                {{ number_format($totalMatStock, 0, ',', '.') }}
                            </span>
                            <span class="text-xs font-semibold text-slate-500">Item</span>
                        </div>
                    </div>
                    <div class="metric-lens-box bg-blue-100/80 text-blue-600 border border-blue-200/60">
                        <x-lucide-boxes class="w-5 h-5" />
                    </div>
                </div>

                {{-- Trend Pill & Sparkline --}}
                <div class="mt-3">
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="inline-flex items-center gap-1 font-bold text-blue-600">
                            <x-lucide-arrow-up-right class="w-3.5 h-3.5" />
                            <span>{{ $totalMatVariants }} Varian</span>
                        </span>
                        <span class="text-slate-400 font-medium">Terdaftar</span>
                    </div>

                    {{-- Pure SVG Sparkline Wave (Blue) --}}
                    <svg viewBox="0 0 120 30" class="sparkline-svg" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="blueSparkGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.3" />
                                <stop offset="100%" stop-color="#3b82f6" stop-opacity="0.0" />
                            </linearGradient>
                        </defs>
                        <path d="M 0,20 Q 30,26 60,12 T 120,6 L 120,30 L 0,30 Z" fill="url(#blueSparkGrad)" />
                        <path d="M 0,20 Q 30,26 60,12 T 120,6" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" />
                    </svg>
                </div>
            </div>

            {{-- CARD 2: STOK AMAN --}}
            <div class="inv-metric-card card-green">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Stok Aman</span>
                        <div class="mt-1 flex items-baseline gap-2">
                            <span class="text-2xl sm:text-3xl font-extrabold text-emerald-700 tabular-nums">
                                {{ number_format($safeQty, 0, ',', '.') }}
                            </span>
                            <span class="text-xs font-semibold text-slate-500">Item</span>
                        </div>
                    </div>
                    <div class="metric-lens-box bg-emerald-100/80 text-emerald-600 border border-emerald-200/60">
                        <x-lucide-check-circle class="w-5 h-5" />
                    </div>
                </div>

                {{-- Trend Pill & Sparkline --}}
                <div class="mt-3">
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="inline-flex items-center gap-1 font-bold text-emerald-600">
                            <x-lucide-arrow-up-right class="w-3.5 h-3.5" />
                            <span>{{ $safeCount }} Item</span>
                        </span>
                        <span class="text-slate-400 font-medium">Di Atas Minimum</span>
                    </div>

                    {{-- Pure SVG Sparkline Wave (Emerald) --}}
                    <svg viewBox="0 0 120 30" class="sparkline-svg" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="emeraldSparkGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#10b981" stop-opacity="0.3" />
                                <stop offset="100%" stop-color="#10b981" stop-opacity="0.0" />
                            </linearGradient>
                        </defs>
                        <path d="M 0,24 Q 25,10 60,18 T 120,5 L 120,30 L 0,30 Z" fill="url(#emeraldSparkGrad)" />
                        <path d="M 0,24 Q 25,10 60,18 T 120,5" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" />
                    </svg>
                </div>
            </div>

            {{-- CARD 3: STOK MENIPIS --}}
            <div class="inv-metric-card card-amber">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Stok Menipis</span>
                        <div class="mt-1 flex items-baseline gap-2">
                            <span class="text-2xl sm:text-3xl font-extrabold text-amber-700 tabular-nums">
                                {{ number_format($lowQty, 0, ',', '.') }}
                            </span>
                            <span class="text-xs font-semibold text-slate-500">Item</span>
                        </div>
                    </div>
                    <div class="metric-lens-box bg-amber-100/80 text-amber-600 border border-amber-200/60">
                        <x-lucide-triangle-alert class="w-5 h-5" />
                    </div>
                </div>

                {{-- Trend Pill & Sparkline --}}
                <div class="mt-3">
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="inline-flex items-center gap-1 font-bold text-amber-600">
                            <x-lucide-alert-triangle class="w-3.5 h-3.5" />
                            <span>{{ $lowCount }} SKU</span>
                        </span>
                        <span class="text-slate-400 font-medium">Batas Minimum</span>
                    </div>

                    {{-- Pure SVG Sparkline Wave (Amber) --}}
                    <svg viewBox="0 0 120 30" class="sparkline-svg" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="amberSparkGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#f59e0b" stop-opacity="0.3" />
                                <stop offset="100%" stop-color="#f59e0b" stop-opacity="0.0" />
                            </linearGradient>
                        </defs>
                        <path d="M 0,16 Q 35,26 70,12 T 120,18 L 120,30 L 0,30 Z" fill="url(#amberSparkGrad)" />
                        <path d="M 0,16 Q 35,26 70,12 T 120,18" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" />
                    </svg>
                </div>
            </div>

            {{-- CARD 4: STOK KRITIS --}}
            <div class="inv-metric-card card-rose">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Stok Kritis / Habis</span>
                        <div class="mt-1 flex items-baseline gap-2">
                            <span class="text-2xl sm:text-3xl font-extrabold text-rose-700 tabular-nums">
                                {{ number_format($critQty, 0, ',', '.') }}
                            </span>
                            <span class="text-xs font-semibold text-slate-500">Item</span>
                        </div>
                    </div>
                    <div class="metric-lens-box bg-rose-100/80 text-rose-600 border border-rose-200/60">
                        <x-lucide-alert-circle class="w-5 h-5" />
                    </div>
                </div>

                {{-- Trend Pill & Sparkline --}}
                <div class="mt-3">
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="inline-flex items-center gap-1 font-bold text-rose-600">
                            <x-lucide-arrow-down-right class="w-3.5 h-3.5" />
                            <span>{{ $critCount }} SKU Kritis</span>
                        </span>
                        <span class="text-slate-400 font-medium">Perlu Pengadaan</span>
                    </div>

                    {{-- Pure SVG Sparkline Wave (Rose) --}}
                    <svg viewBox="0 0 120 30" class="sparkline-svg" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="roseSparkGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#ef4444" stop-opacity="0.3" />
                                <stop offset="100%" stop-color="#ef4444" stop-opacity="0.0" />
                            </linearGradient>
                        </defs>
                        <path d="M 0,12 Q 40,8 80,22 T 120,24 L 120,30 L 0,30 Z" fill="url(#roseSparkGrad)" />
                        <path d="M 0,12 Q 40,8 80,22 T 120,24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" />
                    </svg>
                </div>
            </div>

        </div>

        {{-- ================= 3. SEGMENTED TAB SWITCHER & FILTER TOOLBAR ================= --}}
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">

            {{-- Segmented Tabs --}}
            <div class="px-5 pt-4 pb-0 border-b border-slate-200/80 flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center gap-1">
                    <button type="button" @click="activeTab = 'material'"
                        :class="activeTab === 'material' ? 'border-orange-600 text-orange-600 font-bold bg-orange-50/50' : 'border-transparent text-slate-500 hover:text-slate-800 font-semibold'"
                        class="inline-flex items-center gap-2 px-4 py-2.5 border-b-2 text-xs transition cursor-pointer rounded-t-lg">
                        <x-lucide-boxes class="w-4 h-4" />
                        <span>Material</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-100 text-slate-600 tabular-nums">
                            {{ $totalMatVariants }}
                        </span>
                    </button>

                    <button type="button" @click="activeTab = 'alat'"
                        :class="activeTab === 'alat' ? 'border-orange-600 text-orange-600 font-bold bg-orange-50/50' : 'border-transparent text-slate-500 hover:text-slate-800 font-semibold'"
                        class="inline-flex items-center gap-2 px-4 py-2.5 border-b-2 text-xs transition cursor-pointer rounded-t-lg">
                        <x-lucide-wrench class="w-4 h-4" />
                        <span>Alat</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-100 text-slate-600 tabular-nums">
                            {{ $totalToolsCount }}
                        </span>
                    </button>
                </div>

                {{-- Accordion Quick Controls --}}
                <div class="flex items-center gap-2 pb-2">
                    <button type="button" @click="expandAll()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200 hover:text-orange-600 font-semibold text-[11px] text-slate-600 transition shadow-2xs">
                        <x-lucide-chevrons-down class="w-3.5 h-3.5" />
                        <span>Buka Semua</span>
                    </button>
                    <button type="button" @click="collapseAll()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200 hover:text-orange-600 font-semibold text-[11px] text-slate-600 transition shadow-2xs">
                        <x-lucide-chevrons-up class="w-3.5 h-3.5" />
                        <span>Tutup Semua</span>
                    </button>
                </div>
            </div>

            {{-- Filter Bar Form --}}
            <form method="GET" action="{{ route('inventory.index') }}" id="invFilterForm" class="p-4 sm:p-5 bg-slate-50/50 flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-slate-200/80">
                <input type="hidden" name="item_type" :value="activeTab === 'material' ? 'material' : 'tool'">

                {{-- Search Box --}}
                <div class="relative flex-1 min-w-[240px]">
                    <x-lucide-search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                    <input type="text" name="search" value="{{ request('search') }}" x-model="searchQuery"
                        placeholder="Cari nama material, SKU, atau kategori..." 
                        class="w-full pl-10 pr-4 py-2 text-xs bg-white border border-slate-200 rounded-xl text-slate-800 placeholder:text-slate-400 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition shadow-2xs">
                </div>

                {{-- Filter Dropdowns --}}
                <div class="flex items-center gap-2.5 flex-wrap">

                    {{-- Lokasi / Gudang --}}
                    <div class="relative inline-flex items-center">
                        <select name="warehouse_id" onchange="this.form.submit()"
                            class="inv-filter-select inv-select-warehouse">
                            @if(!empty($canViewAllWarehouses))
                                <option value="all" {{ ($selectedWarehouseId === 'all') ? 'selected' : '' }}>
                                    Semua Lokasi
                                </option>
                            @endif
                            @foreach($accessibleWarehouses as $wh)
                                <option value="{{ $wh->id }}" {{ (string)$selectedWarehouseId === (string)$wh->id ? 'selected' : '' }}>
                                    {{ $wh->name }} {{ $wh->is_central ? '(Pusat)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <x-lucide-chevron-down class="w-3.5 h-3.5 text-slate-400 absolute right-3 pointer-events-none" />
                    </div>

                    {{-- Status Level --}}
                    <div class="relative inline-flex items-center">
                        <select name="stock_level" onchange="this.form.submit()"
                            class="inv-filter-select inv-select-status">
                            <option value="">Semua Status</option>
                            <option value="low" {{ request('stock_level') === 'low' ? 'selected' : '' }}>Menipis</option>
                            <option value="out" {{ request('stock_level') === 'out' ? 'selected' : '' }}>Kritis / Habis</option>
                        </select>
                        <x-lucide-chevron-down class="w-3.5 h-3.5 text-slate-400 absolute right-3 pointer-events-none" />
                    </div>

                    {{-- Reset Button --}}
                    @if(request('search') || request('stock_level') || request('category_id') || (request()->has('warehouse_id') && request('warehouse_id') != (auth()->user()->activeWarehouse()?->id ?? 'all')))
                        <a href="{{ route('inventory.index') }}" title="Reset Filter" 
                            class="p-2 rounded-xl text-slate-600 hover:text-slate-900 bg-white border border-slate-200 hover:bg-slate-100 transition shadow-2xs">
                            <x-lucide-rotate-ccw class="w-4 h-4" />
                        </a>
                    @endif

                    {{-- Cari Button --}}
                    <button type="submit" 
                        class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white transition shadow-2xs inline-flex items-center gap-1.5 cursor-pointer">
                        <x-lucide-search class="w-3.5 h-3.5" />
                        <span>Cari</span>
                    </button>
                </div>
            </form>

            {{-- ================= 4. TAB 1: DATA TABEL MATERIAL KONSTRUKSI ================= --}}
            <div x-show="activeTab === 'material'" class="overflow-x-auto">
                <table class="inv-data-table">
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">No.</th>
                            <th>Nama Material</th>
                            <th>Kode SKU</th>
                            <th>Spesifikasi</th>
                            <th>Satuan</th>
                            <th>Lokasi</th>
                            <th style="text-align: right;">Stok Fisik</th>
                            <th style="text-align: center;">Status</th>
                            <th style="text-align: center; width: 90px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $globalRowIdx = 1; @endphp
                        @forelse($categoriesData as $category)
                            @php
                                $catKey = 'cat-mat-' . $category->id;
                                $materialsWithInv = $category->materials->filter(fn($m) => $m->inventories->isNotEmpty());
                                $catMaterialCount = $materialsWithInv->count();
                            @endphp

                            @if($materialsWithInv->isNotEmpty())
                                {{-- Kategori Accordion Header Row --}}
                                <tr @click="toggleGroup('{{ $catKey }}')" class="category-accordion-row">
                                    <td colspan="9">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2.5">
                                                <svg :class="openGroups.includes('{{ $catKey }}') ? 'rotate-90 text-orange-600' : 'text-slate-400'" 
                                                    class="w-3.5 h-3.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                                </svg>
                                                <span class="font-extrabold text-slate-800 text-xs tracking-wide">
                                                    {{ strtoupper(trim(preg_replace('/^DIVISI\s+/i', '', $category->name))) }}
                                                </span>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-white text-slate-600 border border-slate-200">
                                                    {{ $catMaterialCount }} Varian
                                                </span>
                                            </div>
                                            <span class="text-[11px] font-medium text-slate-500 hover:text-slate-800" 
                                                x-text="openGroups.includes('{{ $catKey }}') ? 'Tutup Grup' : 'Buka Grup'"></span>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Baris Material --}}
                                @foreach($materialsWithInv as $material)
                                    @foreach($material->inventories as $inv)
                                        @php
                                            $qty = (float) $inv->quantity;
                                            $minStock = (float) $inv->min_stock;

                                            if ($qty <= 0) {
                                                $pillClass = 'pill-critical';
                                                $statusText = 'Kritis';
                                            } elseif ($minStock > 0 && $qty <= $minStock) {
                                                $pillClass = 'pill-low';
                                                $statusText = 'Menipis';
                                            } else {
                                                $pillClass = 'pill-safe';
                                                $statusText = 'Aman';
                                            }
                                        @endphp

                                        <tr x-show="openGroups.includes('{{ $catKey }}') && matchesSearch('{{ addslashes($material->name) }}', '{{ addslashes($material->sku ?? '') }}', '{{ addslashes($material->type ?? '') }}', '{{ addslashes($category->name) }}')">
                                            {{-- No. --}}
                                            <td style="text-align: center; color: #94a3b8; font-variant-numeric: tabular-nums;">
                                                {{ $globalRowIdx++ }}
                                            </td>

                                            {{-- Nama Material --}}
                                            <td>
                                                <div class="font-bold text-slate-900 text-[13px]">
                                                    {{ $material->name }}
                                                </div>
                                                @if($material->brand)
                                                    <div class="text-[11px] text-slate-400 font-medium">{{ $material->brand }}</div>
                                                @endif
                                            </td>

                                            {{-- Kode SKU --}}
                                            <td>
                                                <span class="px-2 py-0.5 rounded-md bg-slate-100 font-mono text-[11px] font-bold text-slate-700 border border-slate-200/80">
                                                    {{ $material->sku ?? '-' }}
                                                </span>
                                            </td>

                                            {{-- Spesifikasi --}}
                                            <td>
                                                <div class="font-medium text-slate-700">{{ $material->type ?: '-' }}</div>
                                                @if($material->size)
                                                    <div class="text-[10.5px] text-slate-400">{{ $material->size }}</div>
                                                @endif
                                            </td>

                                            {{-- Satuan --}}
                                            <td>
                                                <span class="text-slate-600 font-semibold">
                                                    {{ $material->unit?->abbreviation ?? $material->unit?->name ?? '-' }}
                                                </span>
                                            </td>

                                            {{-- Lokasi --}}
                                            <td>
                                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-slate-50 text-slate-700 text-[11px] font-medium border border-slate-200/70">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                                    {{ $inv->warehouse?->name ?? '-' }}
                                                </span>
                                            </td>

                                            {{-- Stok Fisik --}}
                                            <td style="text-align: right;">
                                                <span class="font-extrabold text-slate-900 text-[13px] tabular-nums">
                                                    {{ number_format($inv->quantity, 0, ',', '.') }}
                                                </span>
                                            </td>

                                            {{-- Status --}}
                                            <td style="text-align: center;">
                                                <span class="status-dot-pill {{ $pillClass }}">
                                                    <span class="dot"></span>
                                                    <span>{{ $statusText }}</span>
                                                </span>
                                            </td>

                                            {{-- Aksi --}}
                                            <td style="text-align: center;">
                                                <a href="{{ route('inventory.show', $inv) }}" class="btn-stock-card" title="Buka Kartu Stok">
                                                    <x-lucide-file-text class="w-3.5 h-3.5" />
                                                    <span>Kartu</span>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @endif
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-slate-400">
                                    <x-lucide-boxes class="w-8 h-8 mx-auto mb-2 text-slate-300" />
                                    <div class="font-bold text-slate-700 text-sm">Tidak Ada Data Material</div>
                                    <div class="text-xs text-slate-400 mt-1">Tidak ditemukan material sesuai kriteria filter saat ini.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ================= 5. TAB 2: DATA TABEL ALAT & ARMADA ================= --}}
            <div x-show="activeTab === 'alat'" style="display: none;" class="overflow-x-auto">
                <table class="inv-data-table">
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">No.</th>
                            <th>Nama Alat</th>
                            <th>Kode Alat</th>
                       
                            <th>Merk & Spesifikasi</th>
                            <th>Lokasi / Site</th>
                            <th style="text-align: center;">Total Unit</th>
                            <th style="text-align: center;">Siap Pakai</th>
                            <th style="text-align: center;">Di Proyek</th>
                            <th style="text-align: center;">Status</th>
                            <th style="text-align: center; width: 90px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $toolRowIdx = 1; @endphp
                        @forelse($toolsCategoriesData as $toolCategory)
                            @php
                                $toolCatKey = 'cat-tool-' . $toolCategory->id;
                                $catToolCount = $toolCategory->tools->count();
                            @endphp

                            {{-- Accordion Header Alat --}}
                            <tr @click="toggleGroup('{{ $toolCatKey }}')" class="category-accordion-row">
                                <td colspan="10">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <svg :class="openGroups.includes('{{ $toolCatKey }}') ? 'rotate-90 text-orange-600' : 'text-slate-400'" 
                                                class="w-3.5 h-3.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                            </svg>
                                            <span class="font-extrabold text-slate-800 text-xs tracking-wide">
                                                {{ strtoupper(trim(preg_replace('/^DIVISI\s+/i', '', $toolCategory->name))) }}
                                            </span>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-white text-slate-600 border border-slate-200">
                                                {{ $catToolCount }} Jenis Alat
                                            </span>
                                        </div>
                                        <span class="text-[11px] font-medium text-slate-500 hover:text-slate-800" 
                                            x-text="openGroups.includes('{{ $toolCatKey }}') ? 'Tutup Grup' : 'Buka Grup'"></span>
                                    </div>
                                </td>
                            </tr>

                            {{-- Baris Alat --}}
                            @foreach($toolCategory->tools as $tool)
                                @php
                                    $whName = ($tool->relationLoaded('inventories') && $tool->inventories->isNotEmpty())
                                        ? ($tool->inventories->first()->warehouse?->name ?? '-')
                                        : ($tool->currentWarehouse?->name ?? 'Gudang Pusat');
                                @endphp

                                <tr x-show="openGroups.includes('{{ $toolCatKey }}') && matchesSearch('{{ addslashes($tool->name) }}', '{{ addslashes($tool->code ?? '') }}', '{{ addslashes($tool->brand ?? '') }}', '{{ addslashes($toolCategory->name) }}')">
                                    {{-- No. --}}
                                    <td style="text-align: center; color: #94a3b8; font-variant-numeric: tabular-nums;">
                                        {{ $toolRowIdx++ }}
                                    </td>

                                    {{-- Nama Alat --}}
                                    <td>
                                        <div class="font-bold text-slate-900 text-[13px]">{{ $tool->name }}</div>
                                        @if($tool->brand)
                                            <div class="text-[11px] text-slate-400 font-medium">{{ $tool->brand }}</div>
                                        @endif
                                    </td>

                                    {{-- Kode Alat --}}
                                    <td>
                                        <span class="px-2 py-0.5 rounded-md bg-slate-100 font-mono text-[11px] font-bold text-slate-700 border border-slate-200/80">
                                            {{ $tool->code ?? '-' }}
                                        </span>
                                    </td>

                                    {{-- Merk & Spesifikasi --}}
                                    <td>
                                        <div class="font-medium text-slate-700">{{ $tool->type ?: '-' }}</div>
                                        @if($tool->size)
                                            <div class="text-[10.5px] text-slate-400">{{ $tool->size }}</div>
                                        @endif
                                    </td>

                                    {{-- Lokasi / Site --}}
                                    <td>
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-slate-50 text-slate-700 text-[11px] font-medium border border-slate-200/70">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            {{ $whName }}
                                        </span>
                                    </td>

                                    {{-- Total Unit --}}
                                    <td style="text-align: center;">
                                        <span class="font-extrabold text-slate-900 text-[13px] tabular-nums">
                                            {{ number_format($tool->stock_total, 0, ',', '.') }}
                                        </span>
                                    </td>

                                    {{-- Siap Pakai --}}
                                    <td style="text-align: center;">
                                        <span class="font-extrabold text-emerald-600 text-[13px] tabular-nums">
                                            {{ number_format($tool->stock_available, 0, ',', '.') }}
                                        </span>
                                    </td>

                                    {{-- Di Proyek --}}
                                    <td style="text-align: center;">
                                        <span class="font-bold text-slate-700 text-[12px] tabular-nums">
                                            {{ number_format($tool->stock_borrowed, 0, ',', '.') }}
                                        </span>
                                    </td>

                                    {{-- Status --}}
                                    <td style="text-align: center;">
                                        @if($tool->stock_available > 0)
                                            <span class="status-dot-pill pill-safe">
                                                <span class="dot"></span>
                                                <span>Siap Pakai</span>
                                            </span>
                                        @else
                                            <span class="status-dot-pill pill-critical">
                                                <span class="dot"></span>
                                                <span>Dipinjam</span>
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Aksi --}}
                                    <td style="text-align: center;">
                                        <a href="{{ route('tools.show', $tool) }}" class="btn-stock-card" title="Detail Alat">
                                            <x-lucide-eye class="w-3.5 h-3.5" />
                                            <span>Detail</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="10" class="py-12 text-center text-slate-400">
                                    <x-lucide-wrench class="w-8 h-8 mx-auto mb-2 text-slate-300" />
                                    <div class="font-bold text-slate-700 text-sm">Belum Ada Unit Alat Terdaftar</div>
                                    <div class="text-xs text-slate-400 mt-1">Data alat kerja akan tampil di sini setelah ditambahkan melalui modul Alat.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Table Footer --}}
            <div class="px-5 py-3.5 bg-slate-50/80 border-t border-slate-200/80 flex items-center justify-between text-xs text-slate-500 flex-wrap gap-2">
                <div>
                    Menampilkan total <strong class="text-slate-800">{{ $totalMatVariants }} data material</strong> dalam inventori.
                </div>
                <div class="text-[11px] text-slate-400">
                    Sistem Inventori Real-Time &bull; PT Arsikon Cipta Karya
                </div>
            </div>

        </div>

    </div>
</x-app-layout>
