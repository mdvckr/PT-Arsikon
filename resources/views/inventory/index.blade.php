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

        $safePercent = $totalMatVariants > 0 ? min(100, round(($safeCount / $totalMatVariants) * 100)) : 100;

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
        class="w-full max-w-[1600px] mx-auto space-y-5">

        {{-- ==================== 1. Header & Actions (Anti AI-Slop) ==================== --}}
        <div class="flex items-center justify-between mb-2 flex-wrap gap-4">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-orange-50 dark:bg-orange-950/40 text-orange-600 flex items-center justify-center border border-orange-200/60 dark:border-orange-900 shadow-2xs">
                    <x-lucide-boxes class="w-5 h-5" />
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                            Inventaris Material & Gudang
                        </h1>
                        @if($currentWhObj)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-200/70 dark:border-blue-800">
                                <x-lucide-building-2 class="w-3 h-3 text-blue-600" /> {{ $currentWhObj->name }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                🏬 Semua Lokasi Gudang
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Kelola stok persediaan material konstruksi dan pemantauan ketersediaan unit alat kerja lapangan.
                    </p>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-2 flex-wrap">
                <template x-if="activeTab === 'material'">
                    <div>
                        @can('create materials')
                        <a href="{{ route('materials.create') }}" class="h-9 px-4 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs inline-flex items-center gap-1.5 transition shadow-xs">
                            <x-lucide-circle-plus class="w-4 h-4" /> Tambah Material
                        </a>
                        @endcan
                    </div>
                </template>

                <template x-if="activeTab === 'alat'">
                    <div>
                        <a href="{{ route('tools.create') }}" class="h-9 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs inline-flex items-center gap-1.5 transition shadow-xs">
                            <x-lucide-circle-plus class="w-4 h-4 text-orange-400" /> Tambah Alat
                        </a>
                    </div>
                </template>
            </div>
        </div>

        {{-- ==================== 2. Purposeful Inventory Health Strip (Anti AI-Slop) ==================== --}}
        {{-- A. Material Health Strip --}}
        <div x-show="activeTab === 'material'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
            {{-- 1. Total Material --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 p-4 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Total Material Fisik</span>
                    <span class="w-7 h-7 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-600 flex items-center justify-center border border-blue-200/60 dark:border-blue-900">
                        <x-lucide-boxes class="w-3.5 h-3.5" />
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-white tracking-tight font-mono tabular-nums">
                    {{ number_format($totalMatStock, 0, ',', '.') }} <span class="text-xs font-semibold text-slate-400">Item</span>
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 flex items-center justify-between">
                    <span class="font-bold text-blue-600 dark:text-blue-400">{{ $totalMatVariants }} SKU Varian</span>
                    <span class="text-slate-400">{{ $categoriesData->count() }} Kategori</span>
                </div>
            </div>

            {{-- 2. Stok Aman (Health Ratio) --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 p-4 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Stok Aman</span>
                    <span class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 flex items-center justify-center border border-emerald-200/60 dark:border-emerald-900">
                        <x-lucide-circle-check class="w-3.5 h-3.5" />
                    </span>
                </div>
                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight font-mono tabular-nums">
                    {{ number_format($safeQty, 0, ',', '.') }} <span class="text-xs font-semibold text-slate-400">Item</span>
                </div>
                <div class="mt-2 space-y-1">
                    <div class="w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden">
                        <div class="bg-emerald-500 h-full rounded-full transition-all duration-500" style="width: {{ $safePercent }}%;"></div>
                    </div>
                    <div class="text-[10.5px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
                        <span>{{ $safeCount }} SKU aman</span>
                        <span class="font-bold text-emerald-600">{{ $safePercent }}% Sehat</span>
                    </div>
                </div>
            </div>

            {{-- 3. Stok Menipis (Warning State) --}}
            <div class="rounded-2xl border p-4 shadow-2xs transition {{ $lowCount > 0 ? 'bg-amber-50/70 border-amber-300 dark:bg-amber-950/30 dark:border-amber-800 shadow-amber-500/5' : 'bg-white dark:bg-slate-900 border-slate-200/90 dark:border-slate-800' }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider {{ $lowCount > 0 ? 'text-amber-800 dark:text-amber-300' : 'text-slate-400 dark:text-slate-500' }}">
                        Stok Menipis
                    </span>
                    <span class="w-7 h-7 rounded-lg flex items-center justify-center border {{ $lowCount > 0 ? 'bg-amber-100 text-amber-700 border-amber-300' : 'bg-slate-100 text-slate-500 border-slate-200' }}">
                        <x-lucide-triangle-alert class="w-3.5 h-3.5" />
                    </span>
                </div>
                <div class="text-2xl font-black tracking-tight font-mono tabular-nums {{ $lowCount > 0 ? 'text-amber-900 dark:text-amber-200' : 'text-slate-900 dark:text-white' }}">
                    {{ number_format($lowQty, 0, ',', '.') }} <span class="text-xs font-semibold text-slate-400">Item</span>
                </div>
                <div class="text-[11px] mt-2 flex items-center justify-between {{ $lowCount > 0 ? 'text-amber-800 dark:text-amber-300' : 'text-slate-400' }}">
                    <span>{{ $lowCount }} SKU mendekati batas min</span>
                    @if($lowCount > 0)
                        <a href="{{ route('inventory.index', ['stock_level' => 'low']) }}" class="font-bold underline hover:text-amber-900">Filter &rarr;</a>
                    @endif
                </div>
            </div>

            {{-- 4. Stok Kritis / Habis (Emergency State) --}}
            <div class="rounded-2xl border p-4 shadow-2xs transition {{ $critCount > 0 ? 'bg-rose-50/80 border-rose-300 dark:bg-rose-950/30 dark:border-rose-800 shadow-rose-500/5' : 'bg-white dark:bg-slate-900 border-slate-200/90 dark:border-slate-800' }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider {{ $critCount > 0 ? 'text-rose-800 dark:text-rose-300' : 'text-slate-400 dark:text-slate-500' }}">
                        Stok Kritis / Habis
                    </span>
                    <span class="w-7 h-7 rounded-lg flex items-center justify-center border {{ $critCount > 0 ? 'bg-rose-100 text-rose-700 border-rose-300 animate-pulse' : 'bg-slate-100 text-slate-500 border-slate-200' }}">
                        <x-lucide-alert-circle class="w-3.5 h-3.5" />
                    </span>
                </div>
                <div class="text-2xl font-black tracking-tight font-mono tabular-nums {{ $critCount > 0 ? 'text-rose-600' : 'text-slate-900 dark:text-white' }}">
                    {{ number_format($critQty, 0, ',', '.') }} <span class="text-xs font-semibold text-slate-400">Item</span>
                </div>
                <div class="text-[11px] mt-2 flex items-center justify-between {{ $critCount > 0 ? 'text-rose-700 font-bold' : 'text-slate-400' }}">
                    <span>{{ $critCount > 0 ? $critCount . ' SKU Perlu Pengadaan Segera' : 'Semua kuota terpenuhi' }}</span>
                    @if($critCount > 0)
                        <a href="{{ route('inventory.index', ['stock_level' => 'out']) }}" class="underline hover:text-rose-900">Periksa &rarr;</a>
                    @endif
                </div>
            </div>
        </div>

        {{-- B. Tools Health Strip --}}
        <div x-show="activeTab === 'alat'" style="display: none;" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 p-4 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Total Alat & Armada</span>
                    <span class="w-7 h-7 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-600 flex items-center justify-center border border-blue-200/60 dark:border-blue-900">
                        <x-lucide-wrench class="w-3.5 h-3.5" />
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-white tracking-tight font-mono tabular-nums">
                    {{ number_format($totalToolStockTotal, 0, ',', '.') }} <span class="text-xs font-semibold text-slate-400">Unit</span>
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-2">
                    {{ $totalToolsCount }} Jenis alat terdata
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 p-4 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Siap Pakai (Standby)</span>
                    <span class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 flex items-center justify-center border border-emerald-200/60 dark:border-emerald-900">
                        <x-lucide-circle-check class="w-3.5 h-3.5" />
                    </span>
                </div>
                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight font-mono tabular-nums">
                    {{ number_format($totalToolAvailable, 0, ',', '.') }} <span class="text-xs font-semibold text-slate-400">Unit</span>
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-2">
                    Tersedia di gudang & siap kirim
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 p-4 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Di Proyek (Operasional)</span>
                    <span class="w-7 h-7 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-600 flex items-center justify-center border border-amber-200/60 dark:border-amber-900">
                        <x-lucide-hard-hat class="w-3.5 h-3.5" />
                    </span>
                </div>
                <div class="text-2xl font-black text-amber-700 dark:text-amber-400 tracking-tight font-mono tabular-nums">
                    {{ number_format($totalToolBorrowed, 0, ',', '.') }} <span class="text-xs font-semibold text-slate-400">Unit</span>
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-2">
                    Sedang dipinjam di proyek
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 p-4 shadow-2xs hover:shadow-xs transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Perawatan / Maintenance</span>
                    <span class="w-7 h-7 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 flex items-center justify-center border border-rose-200/60 dark:border-rose-900">
                        <x-lucide-wrench class="w-3.5 h-3.5" />
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-white tracking-tight font-mono tabular-nums">
                    {{ number_format($totalToolMaintenance, 0, ',', '.') }} <span class="text-xs font-semibold text-slate-400">Unit</span>
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-2">
                    Dalam perbaikan / servis
                </div>
            </div>
        </div>

        {{-- ==================== 3. Workspace Card with Integrated Toolbar ==================== --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
            
            {{-- Toolbar Top Header: Segmented Tabs & Accordion Quick Toggles --}}
            <div class="p-4 sm:px-6 border-b border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/60 flex items-center justify-between flex-wrap gap-3">
                <div class="inline-flex items-center gap-1 p-1 rounded-2xl bg-slate-200/60 dark:bg-slate-800">
                    <button type="button" @click="activeTab = 'material'"
                        :class="activeTab === 'material' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-2xs font-bold' : 'text-slate-500 hover:text-slate-900 font-semibold'"
                        class="px-4 py-2 rounded-xl text-xs transition inline-flex items-center gap-2 cursor-pointer">
                        <x-lucide-boxes class="w-4 h-4 text-orange-500" />
                        <span>Material</span>
                        <span class="px-2 py-0.5 rounded-full text-[10.5px] bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-mono font-bold">
                            {{ $totalMatVariants }}
                        </span>
                    </button>

                    <button type="button" @click="activeTab = 'alat'"
                        :class="activeTab === 'alat' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-2xs font-bold' : 'text-slate-500 hover:text-slate-900 font-semibold'"
                        class="px-4 py-2 rounded-xl text-xs transition inline-flex items-center gap-2 cursor-pointer">
                        <x-lucide-wrench class="w-4 h-4 text-amber-500" />
                        <span>Alat & Armada</span>
                        <span class="px-2 py-0.5 rounded-full text-[10.5px] bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-mono font-bold">
                            {{ $totalToolsCount }}
                        </span>
                    </button>
                </div>

                {{-- Accordion Controls --}}
                <div class="inline-flex items-center gap-1.5">
                    <button type="button" @click="expandAll()" class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-[11px] font-bold text-slate-600 dark:text-slate-300 hover:text-orange-600 hover:bg-slate-50 transition inline-flex items-center gap-1 shadow-2xs cursor-pointer" title="Buka Semua Kategori">
                        <x-lucide-chevrons-down class="w-3.5 h-3.5" /> Buka Semua
                    </button>
                    <button type="button" @click="collapseAll()" class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-[11px] font-bold text-slate-500 dark:text-slate-400 hover:bg-slate-50 transition inline-flex items-center gap-1 shadow-2xs cursor-pointer" title="Tutup Semua Kategori">
                        <x-lucide-chevrons-up class="w-3.5 h-3.5" /> Tutup Semua
                    </button>
                </div>
            </div>

            {{-- Filter Form Toolbar --}}
            <form method="GET" action="{{ route('inventory.index') }}" class="p-4 sm:p-5 flex flex-wrap items-center gap-2.5 border-b border-slate-100 dark:border-slate-800">
                <input type="hidden" name="item_type" :value="activeTab === 'material' ? 'material' : 'tool'">

                {{-- Search Box --}}
                <div class="relative flex-1 min-w-[240px]">
                    <x-lucide-search class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                    <input type="text" name="search" value="{{ request('search') }}" x-model="searchQuery"
                        placeholder="Cari nama material/alat, SKU, atau kategori..." 
                        class="w-full text-xs h-9 pl-9 pr-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 shadow-2xs focus:ring-2 focus:ring-orange-500/20">
                </div>

                {{-- Warehouse Dropdown --}}
                <div class="relative">
                    <select name="warehouse_id" onchange="this.form.submit()"
                        class="text-xs h-9 pl-3 pr-8 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 shadow-2xs focus:ring-2 focus:ring-orange-500/20 font-medium">
                        @if(!empty($canViewAllWarehouses))
                            <option value="all" {{ ($selectedWarehouseId === 'all') ? 'selected' : '' }}>
                                🏬 Semua Lokasi Gudang
                            </option>
                        @endif
                        @foreach($accessibleWarehouses as $wh)
                            <option value="{{ $wh->id }}" {{ (string)$selectedWarehouseId === (string)$wh->id ? 'selected' : '' }}>
                                📦 {{ $wh->name }} {{ $wh->is_central ? '(Pusat)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status Filter --}}
                <div class="relative">
                    <select name="stock_level" onchange="this.form.submit()"
                        class="text-xs h-9 pl-3 pr-8 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 shadow-2xs focus:ring-2 focus:ring-orange-500/20 font-medium">
                        <option value="">Semua Status Stok</option>
                        <option value="low" {{ request('stock_level') === 'low' ? 'selected' : '' }}>⚠️ Stok Menipis</option>
                        <option value="out" {{ request('stock_level') === 'out' ? 'selected' : '' }}>🚨 Kritis / Habis</option>
                    </select>
                </div>

                @if(request('search') || request('stock_level') || request('category_id') || (request()->has('warehouse_id') && request('warehouse_id') != (auth()->user()->activeWarehouse()?->id ?? 'all')))
                    <a href="{{ route('inventory.index') }}" title="Reset Filter"
                        class="h-9 px-3 rounded-xl text-xs font-bold text-rose-600 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 inline-flex items-center gap-1.5 transition">
                        <x-lucide-rotate-ccw class="w-3.5 h-3.5" /> Reset
                    </a>
                @endif

                <button type="submit" 
                    class="h-9 px-4 rounded-xl text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white transition shadow-2xs inline-flex items-center gap-1.5 cursor-pointer">
                    <x-lucide-filter class="w-3.5 h-3.5" /> Terapkan
                </button>
            </form>

            {{-- ==================== 4. DATA TABLE TAB 1: MATERIAL ==================== --}}
            <div x-show="activeTab === 'material'" class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/80 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <th class="py-3 px-4 text-center w-12">No.</th>
                            <th class="py-3 px-4 min-w-[200px]">Nama Material</th>
                            <th class="py-3 px-4 min-w-[130px]">Kode SKU</th>
                            <th class="py-3 px-4 min-w-[140px]">Spesifikasi</th>
                            <th class="py-3 px-4 min-w-[90px]">Satuan</th>
                            <th class="py-3 px-4 min-w-[150px]">Lokasi Gudang</th>
                            <th class="py-3 px-4 min-w-[130px] text-right">Stok Fisik</th>
                            <th class="py-3 px-4 min-w-[110px] text-center">Status</th>
                            <th class="py-3 px-4 text-center w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @php $globalRowIdx = 1; @endphp
                        @forelse($categoriesData as $category)
                            @php
                                $catKey = 'cat-mat-' . $category->id;
                                $materialsWithInv = $category->materials->filter(fn($m) => $m->inventories->isNotEmpty());
                                $catMaterialCount = $materialsWithInv->count();
                            @endphp

                            @if($materialsWithInv->isNotEmpty())
                                {{-- Kategori Accordion Header Banner --}}
                                <tr @click="toggleGroup('{{ $catKey }}')" class="cursor-pointer select-none bg-slate-100/90 dark:bg-slate-800/60 hover:bg-slate-200/80 dark:hover:bg-slate-700/60 transition border-y-2 border-slate-200 dark:border-slate-700">
                                    <td colspan="9" class="py-2.5 px-4">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-6 h-6 rounded-md bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 flex items-center justify-center text-slate-700 dark:text-slate-200 shadow-2xs">
                                                    <svg :class="openGroups.includes('{{ $catKey }}') ? 'rotate-90 text-orange-600' : 'text-slate-400'" 
                                                         class="w-3.5 h-3.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                </div>
                                                <span class="font-extrabold text-slate-900 dark:text-white text-xs tracking-wider uppercase">
                                                    {{ strtoupper(trim(preg_replace('/^DIVISI\s+/i', '', $category->name))) }}
                                                </span>
                                                <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 shadow-2xs">
                                                    {{ $catMaterialCount }} Varian
                                                </span>
                                            </div>
                                            <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400" 
                                                  x-text="openGroups.includes('{{ $catKey }}') ? 'Tutup Grup ▲' : 'Buka Grup ▼'"></span>
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

                                        <tr x-show="openGroups.includes('{{ $catKey }}') && matchesSearch('{{ addslashes($material->name) }}', '{{ addslashes($material->sku ?? '') }}', '{{ addslashes($material->type ?? '') }}', '{{ addslashes($category->name) }}')"
                                            class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                                            {{-- No. --}}
                                            <td class="text-center text-slate-400 font-mono text-[11.5px] py-3 px-4">
                                                {{ $globalRowIdx++ }}
                                            </td>

                                            {{-- Nama Material --}}
                                            <td class="py-3 px-4">
                                                <div class="font-bold text-slate-900 dark:text-white text-xs">
                                                    {{ $material->name }}
                                                </div>
                                                @if($material->brand)
                                                    <div class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">{{ $material->brand }}</div>
                                                @endif
                                            </td>

                                            {{-- Kode SKU --}}
                                            <td class="py-3 px-4">
                                                <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 font-mono text-[11px] font-bold text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700">
                                                    {{ $material->sku ?? '-' }}
                                                </span>
                                            </td>

                                            {{-- Spesifikasi --}}
                                            <td class="py-3 px-4 text-xs">
                                                <div class="font-medium text-slate-700 dark:text-slate-300">{{ $material->type ?: '-' }}</div>
                                                @if($material->size)
                                                    <div class="text-[10.5px] text-slate-400">{{ $material->size }}</div>
                                                @endif
                                            </td>

                                            {{-- Satuan --}}
                                            <td class="py-3 px-4 text-xs">
                                                <span class="text-slate-600 dark:text-slate-400 font-semibold">
                                                    {{ $material->unit?->abbreviation ?? $material->unit?->name ?? '-' }}
                                                </span>
                                            </td>

                                            {{-- Lokasi Gudang --}}
                                            <td class="py-3 px-4 text-xs">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[11px] font-medium border border-slate-200/70 dark:border-slate-700">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                                    {{ $inv->warehouse?->name ?? '-' }}
                                                </span>
                                            </td>

                                            {{-- Stok Fisik & Min --}}
                                            <td class="py-3 px-4 text-right">
                                                <div class="font-black text-slate-900 dark:text-white text-sm font-mono tabular-nums">
                                                    {{ number_format($inv->quantity, 0, ',', '.') }}
                                                </div>
                                                <div class="text-[10.5px] text-slate-400 font-medium">
                                                    @if($inv->min_stock > 0)
                                                        Min: {{ number_format($inv->min_stock, 0, ',', '.') }}
                                                    @else
                                                        Tanpa Min
                                                    @endif
                                                </div>
                                            </td>

                                            {{-- Status --}}
                                            <td class="py-3 px-4 text-center">
                                                <span class="status-dot-pill {{ $pillClass }}">
                                                    <span class="dot"></span>
                                                    <span>{{ $statusText }}</span>
                                                </span>
                                            </td>

                                            {{-- Aksi --}}
                                            <td class="py-3 px-4 text-center">
                                                <a href="{{ route('inventory.show', $inv) }}" 
                                                   class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-bold hover:border-orange-500 hover:text-orange-600 transition shadow-2xs"
                                                   title="Buka Kartu Stok">
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
                                <td colspan="9" class="py-16 px-4 text-center bg-white dark:bg-slate-900">
                                    <div class="max-w-md mx-auto space-y-3">
                                        <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 mx-auto flex items-center justify-center border border-slate-200/60 dark:border-slate-700 shadow-2xs">
                                            <x-lucide-boxes class="w-7 h-7" />
                                        </div>
                                        <div class="space-y-1">
                                            <h4 class="text-sm font-bold text-slate-900 dark:text-white">Tidak Ada Data Material</h4>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                                Tidak ditemukan material yang sesuai dengan kriteria filter atau pencarian Anda.
                                            </p>
                                        </div>
                                        <div class="pt-2">
                                            <a href="{{ route('inventory.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 transition shadow-2xs">
                                                <x-lucide-rotate-ccw class="w-3.5 h-3.5" /> Reset Filter
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ==================== 5. DATA TABLE TAB 2: ALAT & ARMADA ==================== --}}
            <div x-show="activeTab === 'alat'" style="display: none;" class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/80 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <th class="py-3 px-4 text-center w-12">No.</th>
                            <th class="py-3 px-4 min-w-[200px]">Nama Alat</th>
                            <th class="py-3 px-4 min-w-[130px]">Kode Alat</th>
                            <th class="py-3 px-4 min-w-[150px]">Merk & Spesifikasi</th>
                            <th class="py-3 px-4 min-w-[150px]">Lokasi / Site</th>
                            <th class="py-3 px-4 text-center min-w-[90px]">Total Unit</th>
                            <th class="py-3 px-4 text-center min-w-[90px]">Siap Pakai</th>
                            <th class="py-3 px-4 text-center min-w-[90px]">Di Proyek</th>
                            <th class="py-3 px-4 text-center min-w-[110px]">Status</th>
                            <th class="py-3 px-4 text-center w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @php $toolRowIdx = 1; @endphp
                        @forelse($toolsCategoriesData as $toolCategory)
                            @php
                                $toolCatKey = 'cat-tool-' . $toolCategory->id;
                                $catToolCount = $toolCategory->tools->count();
                            @endphp

                            {{-- Accordion Header Alat --}}
                            <tr @click="toggleGroup('{{ $toolCatKey }}')" class="cursor-pointer select-none bg-slate-100/90 dark:bg-slate-800/60 hover:bg-slate-200/80 dark:hover:bg-slate-700/60 transition border-y-2 border-slate-200 dark:border-slate-700">
                                <td colspan="10" class="py-2.5 px-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-6 h-6 rounded-md bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 flex items-center justify-center text-slate-700 dark:text-slate-200 shadow-2xs">
                                                <svg :class="openGroups.includes('{{ $toolCatKey }}') ? 'rotate-90 text-orange-600' : 'text-slate-400'" 
                                                     class="w-3.5 h-3.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </div>
                                            <span class="font-extrabold text-slate-900 dark:text-white text-xs tracking-wider uppercase">
                                                {{ strtoupper(trim(preg_replace('/^DIVISI\s+/i', '', $toolCategory->name))) }}
                                            </span>
                                            <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 shadow-2xs">
                                                {{ $catToolCount }} Jenis Alat
                                            </span>
                                        </div>
                                        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400" 
                                              x-text="openGroups.includes('{{ $toolCatKey }}') ? 'Tutup Grup ▲' : 'Buka Grup ▼'"></span>
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

                                <tr x-show="openGroups.includes('{{ $toolCatKey }}') && matchesSearch('{{ addslashes($tool->name) }}', '{{ addslashes($tool->code ?? '') }}', '{{ addslashes($tool->brand ?? '') }}', '{{ addslashes($toolCategory->name) }}')"
                                    class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                                    {{-- No. --}}
                                    <td class="text-center text-slate-400 font-mono text-[11.5px] py-3 px-4">
                                        {{ $toolRowIdx++ }}
                                    </td>

                                    {{-- Nama Alat --}}
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-slate-900 dark:text-white text-xs">{{ $tool->name }}</div>
                                        @if($tool->brand)
                                            <div class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">{{ $tool->brand }}</div>
                                        @endif
                                    </td>

                                    {{-- Kode Alat --}}
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 font-mono text-[11px] font-bold text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700">
                                            {{ $tool->code ?? '-' }}
                                        </span>
                                    </td>

                                    {{-- Merk & Spesifikasi --}}
                                    <td class="py-3 px-4 text-xs">
                                        <div class="font-medium text-slate-700 dark:text-slate-300">{{ $tool->type ?: '-' }}</div>
                                        @if($tool->size)
                                            <div class="text-[10.5px] text-slate-400">{{ $tool->size }}</div>
                                        @endif
                                    </td>

                                    {{-- Lokasi / Site --}}
                                    <td class="py-3 px-4 text-xs">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[11px] font-medium border border-slate-200/70 dark:border-slate-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            {{ $whName }}
                                        </span>
                                    </td>

                                    {{-- Total Unit --}}
                                    <td class="py-3 px-4 text-center">
                                        <span class="font-black text-slate-900 dark:text-white text-xs font-mono tabular-nums">
                                            {{ number_format($tool->stock_total, 0, ',', '.') }}
                                        </span>
                                    </td>

                                    {{-- Siap Pakai --}}
                                    <td class="py-3 px-4 text-center">
                                        <span class="font-black text-emerald-600 dark:text-emerald-400 text-xs font-mono tabular-nums">
                                            {{ number_format($tool->stock_available, 0, ',', '.') }}
                                        </span>
                                    </td>

                                    {{-- Di Proyek --}}
                                    <td class="py-3 px-4 text-center">
                                        <span class="font-bold text-slate-700 dark:text-slate-300 text-xs font-mono tabular-nums">
                                            {{ number_format($tool->stock_borrowed, 0, ',', '.') }}
                                        </span>
                                    </td>

                                    {{-- Status --}}
                                    <td class="py-3 px-4 text-center">
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
                                    <td class="py-3 px-4 text-center">
                                        <a href="{{ route('tools.show', $tool) }}" 
                                           class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-bold hover:border-orange-500 hover:text-orange-600 transition shadow-2xs"
                                           title="Lihat Detail Alat">
                                            <x-lucide-eye class="w-3.5 h-3.5" />
                                            <span>Detail</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="10" class="py-16 px-4 text-center bg-white dark:bg-slate-900">
                                    <div class="max-w-md mx-auto space-y-3">
                                        <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 mx-auto flex items-center justify-center border border-slate-200/60 dark:border-slate-700 shadow-2xs">
                                            <x-lucide-wrench class="w-7 h-7" />
                                        </div>
                                        <div class="space-y-1">
                                            <h4 class="text-sm font-bold text-slate-900 dark:text-white">Belum Ada Unit Alat Terdaftar</h4>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                                Data alat kerja dan armada operasional akan tampil di sini setelah ditambahkan ke inventori.
                                            </p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ==================== 6. Table Footer ==================== --}}
            <div class="px-5 py-3.5 bg-slate-50/80 dark:bg-slate-800/80 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 flex-wrap gap-2">
                <div>
                    <template x-if="activeTab === 'material'">
                        <span>Menampilkan total <strong class="text-slate-800 dark:text-slate-200">{{ $totalMatVariants }} varian material</strong> dalam inventori.</span>
                    </template>
                    <template x-if="activeTab === 'alat'">
                        <span>Menampilkan total <strong class="text-slate-800 dark:text-slate-200">{{ $totalToolsCount }} jenis alat & armada</strong> dalam inventori.</span>
                    </template>
                </div>
                <div class="text-[11px] text-slate-400">
                    Sistem Inventori Real-Time &bull; PT Arsikon Cipta Karya
                </div>
            </div>

        </div>

    </div>
</x-app-layout>
