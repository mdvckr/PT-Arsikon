<x-app-layout>
    <x-slot name="title">Inventori Gudang</x-slot>

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

        $totalActiveItems = $totalMatVariants + $totalToolsCount;
        $totalCritical = $totalLowStockMat + $totalToolLow;
        $healthPercentage = $totalActiveItems > 0 
            ? max(0, min(100, round((($totalActiveItems - $totalCritical) / $totalActiveItems) * 100))) 
            : 100;

        $currentWhObj = is_numeric($selectedWarehouseId ?? null) ? $accessibleWarehouses->firstWhere('id', (int)$selectedWarehouseId) : null;

        // Micro-Chart: Komposisi Kategori Material
        $categoryComposition = $categoriesData->map(function($cat) {
            $stock = $cat->materials->sum(fn($m) => $m->inventories->sum('quantity'));
            $variants = $cat->materials->count();
            return [
                'name' => $cat->name,
                'stock' => $stock,
                'variants' => $variants,
            ];
        })->sortByDesc('stock')->values();

        $compTotalStock = $categoryComposition->sum('stock');
        $colorStyles = [
            ['bar' => 'bg-blue-600', 'dot' => 'bg-blue-600', 'text' => 'text-blue-700'],
            ['bar' => 'bg-orange-500', 'dot' => 'bg-orange-500', 'text' => 'text-orange-700'],
            ['bar' => 'bg-emerald-500', 'dot' => 'bg-emerald-500', 'text' => 'text-emerald-700'],
            ['bar' => 'bg-purple-600', 'dot' => 'bg-purple-600', 'text' => 'text-purple-700'],
        ];

        // Collect all category keys for Alpine openGroups
        $allCategoryKeys = [];
        foreach ($categoriesData as $cat) {
            $allCategoryKeys[] = 'cat-mat-' . $cat->id;
        }
        foreach ($toolsCategoriesData as $tCat) {
            $allCategoryKeys[] = 'cat-tool-' . $tCat->id;
        }

        $gradientPalette = [
            'from-blue-600 to-indigo-600',
            'from-amber-500 to-orange-600',
            'from-emerald-600 to-teal-600',
            'from-purple-600 to-indigo-600',
            'from-cyan-600 to-blue-600',
            'from-rose-500 to-pink-600',
        ];
    @endphp

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
            matchesSearch(name, sku, brand, spec) {
                if (!this.searchQuery) return true;
                const q = this.searchQuery.toLowerCase();
                return (name && name.toLowerCase().includes(q)) ||
                       (sku && sku.toLowerCase().includes(q)) ||
                       (brand && brand.toLowerCase().includes(q)) ||
                       (spec && spec.toLowerCase().includes(q));
            }
        }" 
        class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        <!-- ================= 1. EXECUTIVE HEADER BANNER (LIGHT ENTERPRISE THEME) ================= -->
        <div class="relative overflow-hidden rounded-2xl bg-white p-6 sm:p-7 border border-slate-200/90 shadow-xs">
            <!-- Subtle Ambient Background Accents -->
            <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-orange-500/10 blur-3xl"></div>
            <div class="pointer-events-none absolute right-48 -bottom-20 h-48 w-48 rounded-full bg-blue-500/10 blur-3xl"></div>

            <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200/80 text-xs font-semibold text-emerald-800 shadow-2xs">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span>
                            @if($currentWhObj)
                                {{ $currentWhObj->name }} {{ $currentWhObj->is_central ? '(Gudang Pusat Sentral)' : '(Site Gudang Proyek)' }}
                            @elseif(($selectedWarehouseId ?? null) === 'all')
                                Semua Gudang (Konsolidasi Multi-Site)
                            @else
                                Gudang Pusat PT Arsikon (Sentral Distribusi)
                            @endif
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                        Pusat Kendali Inventori & Aset
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 max-w-2xl">
                        Pantau pergerakan stok material konstruksi, ambang batas kritis, dan distribusi alat kerja proyek secara presisi.
                    </p>
                </div>

                <!-- Tombol Aksi Cepat Bergaya Enterprise -->
                <div class="flex flex-wrap items-center gap-3 self-start lg:self-center">
                    <a href="{{ route('reports.stock') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 shadow-2xs transition">
                        <x-lucide-file-spreadsheet class="w-4 h-4 text-slate-500" />
                        <span>Unduh Laporan Stok</span>
                    </a>
                    <a href="{{ route('stock-opnames.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold bg-orange-600 hover:bg-orange-700 text-white shadow-sm shadow-orange-500/25 transition">
                        <x-lucide-plus class="w-4 h-4" />
                        <span>Stock Opname Baru</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- ================= 2. BENTO METRIC CARDS (LIGHT THEME) ================= -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

            <!-- BENTO 1: Material + Visual Komposisi Bar -->
            <div class="group relative bg-white rounded-2xl p-6 border border-slate-200/90 shadow-xs hover:shadow-md transition-all duration-200">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Material Konstruksi</span>
                        <div class="mt-2 flex items-baseline gap-2.5">
                            <span class="text-3xl font-extrabold tracking-tight text-slate-900 tabular-nums">
                                {{ number_format($totalMatStock, 0, ',', '.') }}
                            </span>
                            <span class="text-xs font-semibold text-slate-500">
                                Unit Fisik ({{ number_format($totalMatVariants, 0, ',', '.') }} Varian)
                            </span>
                        </div>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 shadow-2xs">
                        <x-lucide-boxes class="w-5 h-5" />
                    </div>
                </div>

                <!-- Micro-Chart: Komposisi Kategori Material -->
                <div class="mt-5 space-y-2">
                    <div class="flex h-2 w-full overflow-hidden rounded-full bg-slate-100 p-0.5 gap-0.5">
                        @if($compTotalStock > 0 && $categoryComposition->isNotEmpty())
                            @foreach($categoryComposition->take(3) as $idx => $comp)
                                @php
                                    $pct = max(4, round(($comp['stock'] / $compTotalStock) * 100));
                                    $style = $colorStyles[$idx % count($colorStyles)];
                                    $isFirst = $loop->first;
                                    $isLast = $loop->last;
                                    $roundClass = ($isFirst && $isLast) ? 'rounded-full' : ($isFirst ? 'rounded-l-full' : ($isLast ? 'rounded-r-full' : ''));
                                @endphp
                                <div class="{{ $style['bar'] }} {{ $roundClass }}" style="width: {{ $pct }}%" title="{{ $comp['name'] }} ({{ $pct }}%)"></div>
                            @endforeach
                        @else
                            <div class="bg-blue-600 rounded-full w-full" title="Belum ada data stok"></div>
                        @endif
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-600 flex-wrap gap-1">
                        @if($categoryComposition->isNotEmpty())
                            @foreach($categoryComposition->take(3) as $idx => $comp)
                                @php $style = $colorStyles[$idx % count($colorStyles)]; @endphp
                                <span class="inline-flex items-center gap-1">
                                    <i class="w-2 h-2 rounded-full {{ $style['dot'] }} inline-block"></i> 
                                    <strong class="font-medium text-slate-700">{{ Str::limit($comp['name'], 12) }}</strong> ({{ $comp['variants'] }})
                                </span>
                            @endforeach
                        @else
                            <span class="inline-flex items-center gap-1"><i class="w-2 h-2 rounded-full bg-blue-600 inline-block"></i> Umum ({{ $totalMatVariants }})</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- BENTO 2: Kesiapan Armada & Alat Kerja -->
            <div class="group relative bg-white rounded-2xl p-6 border border-slate-200/90 shadow-xs hover:shadow-md transition-all duration-200">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Kesiapan Alat Kerja</span>
                        <div class="mt-2 flex items-baseline gap-2.5">
                            <span class="text-3xl font-extrabold tracking-tight text-slate-900 tabular-nums">
                                {{ number_format($totalToolAvailable, 0, ',', '.') }}
                            </span>
                            <span class="text-xs font-semibold text-emerald-600">Unit Siap Pakai</span>
                        </div>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 shadow-2xs">
                        <x-lucide-wrench class="w-5 h-5" />
                    </div>
                </div>

                <!-- Split Pill Info -->
                <div class="mt-5 grid grid-cols-2 gap-2.5 pt-1">
                    <div class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                        <span class="text-[11px] text-slate-500 font-medium">Di Proyek</span>
                        <span class="text-xs font-bold text-slate-800 tabular-nums">
                            {{ number_format($totalToolBorrowed, 0, ',', '.') }} Unit
                        </span>
                    </div>
                    <div class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                        <span class="text-[11px] text-slate-500 font-medium">Total Armada</span>
                        <span class="text-xs font-bold text-slate-800 tabular-nums">
                            {{ number_format($totalToolStockTotal, 0, ',', '.') }} Unit
                        </span>
                    </div>
                </div>
            </div>

            <!-- BENTO 3: Indikator Kesehatan Stok (Stock Health Radar) -->
            <div class="group relative bg-white rounded-2xl p-6 border border-slate-200/90 shadow-xs hover:shadow-md transition-all duration-200">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Indeks Kesehatan Stok</span>
                        <div class="mt-2 flex items-center gap-2.5">
                            <span class="text-3xl font-extrabold tracking-tight tabular-nums {{ $healthPercentage >= 90 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $healthPercentage }}%
                            </span>
                            @if($totalCritical === 0)
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                    Terkendali
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200/80">
                                    Perlu Restok
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="w-11 h-11 rounded-xl {{ $totalCritical === 0 ? 'bg-emerald-50 border-emerald-100 text-emerald-600' : 'bg-rose-50 border-rose-100 text-rose-600' }} border flex items-center justify-center shadow-2xs">
                        @if($totalCritical === 0)
                            <x-lucide-shield-check class="w-5 h-5" />
                        @else
                            <x-lucide-triangle-alert class="w-5 h-5" />
                        @endif
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-between px-3.5 py-2.5 rounded-xl {{ $totalCritical === 0 ? 'bg-emerald-50/70 border-emerald-100' : 'bg-rose-50/70 border-rose-100' }} border">
                    <span class="text-xs {{ $totalCritical === 0 ? 'text-emerald-800' : 'text-rose-800' }} font-medium">
                        Stok di bawah batas minimum
                    </span>
                    <span class="text-xs font-extrabold {{ $totalCritical === 0 ? 'text-emerald-700' : 'text-rose-700' }} tabular-nums">
                        {{ $totalCritical }} SKU Kritis
                    </span>
                </div>
            </div>

        </div>

        <!-- ================= 3. MAIN DATA WORKSPACE (LIGHT ENTERPRISE THEME) ================= -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">

            <!-- COMMAND BAR: Segmented Switcher + Search & Filter Form -->
            <form method="GET" action="{{ route('inventory.index') }}" id="inventoryFilterForm" class="p-4 sm:px-6 border-b border-slate-200/80 flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                <input type="hidden" name="item_type" :value="activeTab === 'material' ? 'material' : 'tool'">

                <!-- Segmented Switcher -->
                <div class="inline-flex p-1 rounded-xl bg-slate-100 border border-slate-200/80 self-start">
                    <button type="button" @click="activeTab = 'material'"
                        :class="activeTab === 'material' ? 'bg-white text-slate-900 shadow-xs ring-1 ring-slate-900/5' : 'text-slate-600 hover:text-slate-900'"
                        class="flex items-center gap-2.5 px-4 py-2 rounded-lg text-xs font-bold transition-all cursor-pointer">
                        <span class="w-2 h-2 rounded-full transition-colors" :class="activeTab === 'material' ? 'bg-orange-500' : 'bg-slate-300'"></span>
                        <span>Material Konstruksi</span>
                        <span class="px-2 py-0.5 rounded-md text-[11px] bg-slate-100 text-slate-700 tabular-nums">
                            {{ $totalMatVariants }}
                        </span>
                    </button>

                    <button type="button" @click="activeTab = 'alat'"
                        :class="activeTab === 'alat' ? 'bg-white text-slate-900 shadow-xs ring-1 ring-slate-900/5' : 'text-slate-600 hover:text-slate-900'"
                        class="flex items-center gap-2.5 px-4 py-2 rounded-lg text-xs font-bold transition-all cursor-pointer">
                        <span class="w-2 h-2 rounded-full transition-colors" :class="activeTab === 'alat' ? 'bg-orange-500' : 'bg-slate-300'"></span>
                        <span>Alat Kerja & Armada</span>
                        <span class="px-2 py-0.5 rounded-md text-[11px] bg-slate-100 text-slate-700 tabular-nums">
                            {{ $totalToolsCount }}
                        </span>
                    </button>
                </div>

                <!-- Filter & Search Controls -->
                <div class="flex flex-wrap sm:flex-nowrap items-center gap-2.5">
                    <!-- Quick Search Input -->
                    <div class="relative w-full sm:w-72">
                        <x-lucide-search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                        <input type="text" name="search" value="{{ request('search') }}" x-model="searchQuery"
                            @keydown.window.prevent.slash="$el.focus()"
                            placeholder="Filter cepat nama, SKU, merk..." 
                            class="w-full pl-10 pr-10 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition">
                        <kbd class="hidden sm:inline-block absolute right-2.5 top-1/2 -translate-y-1/2 px-1.5 py-0.5 text-[10px] font-mono text-slate-400 bg-white border border-slate-200 rounded shadow-2xs">/</kbd>
                    </div>

                    <!-- Warehouse Selector -->
                    <select name="warehouse_id" id="filter_warehouse_id" onchange="this.form.submit()"
                        class="text-xs bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-slate-700 font-medium cursor-pointer focus:bg-white focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition">
                        @if(!empty($canViewAllWarehouses))
                            <option value="all" {{ ($selectedWarehouseId === 'all') ? 'selected' : '' }}>
                                Semua Gudang (Konsolidasi)
                            </option>
                        @endif
                        @foreach($accessibleWarehouses as $wh)
                            <option value="{{ $wh->id }}" {{ (string)$selectedWarehouseId === (string)$wh->id ? 'selected' : '' }}>
                                {{ $wh->name }} {{ $wh->is_central ? '(Pusat)' : '(Proyek)' }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Stock Condition Selector -->
                    <select name="stock_level" id="filter_stock_level" onchange="this.form.submit()"
                        class="text-xs bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-slate-700 font-medium cursor-pointer focus:bg-white focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition">
                        <option value="">Semua Kondisi Stok</option>
                        <option value="low" {{ request('stock_level') === 'low' ? 'selected' : '' }}>Menipis (Min. Stok)</option>
                        <option value="out" {{ request('stock_level') === 'out' ? 'selected' : '' }}>Stok Habis (0)</option>
                    </select>

                    <!-- Reset Filter Button -->
                    @if(request('search') || request('stock_level') || request('item_type') || (request()->has('warehouse_id') && request('warehouse_id') != (auth()->user()->activeWarehouse()?->id ?? 'all')))
                        <a href="{{ route('inventory.index') }}" title="Reset Semua Filter" 
                            class="p-2 rounded-xl text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition">
                            <x-lucide-rotate-ccw class="w-4 h-4" />
                        </a>
                    @endif
                </div>
            </form>

            <!-- ================= TAB 1: TABEL MATERIAL KONSTRUKSI ================= -->
            <div x-show="activeTab === 'material'" class="overflow-x-auto">
                <div class="px-6 py-2.5 bg-slate-50/70 border-b border-slate-200/70 flex items-center justify-between text-xs text-slate-500">
                    <span class="font-medium">Total <strong class="text-slate-800">{{ $totalMatVariants }} varian</strong> material terdaftar</span>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="expandAll()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white border border-slate-200 hover:text-orange-600 font-medium transition cursor-pointer shadow-2xs">
                            <x-lucide-chevrons-down class="w-3.5 h-3.5" />
                            <span>Buka Semua</span>
                        </button>
                        <button type="button" @click="collapseAll()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white border border-slate-200 hover:text-orange-600 font-medium transition cursor-pointer shadow-2xs">
                            <x-lucide-chevrons-up class="w-3.5 h-3.5" />
                            <span>Tutup Semua</span>
                        </button>
                    </div>
                </div>

                <table class="w-full text-left border-collapse min-w-[960px]">
                    <thead>
                        <tr class="bg-slate-50/90 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="py-3.5 px-6">Identitas Material & SKU</th>
                            <th class="py-3.5 px-4">Spesifikasi & Kelompok</th>
                            <th class="py-3.5 px-4">Titik Gudang</th>
                            <th class="py-3.5 px-4 w-52">Kapasitas Stok Fisik</th>
                            <th class="py-3.5 px-4 text-center">Status Level</th>
                            <th class="py-3.5 px-4">Pembaruan Terakhir</th>
                            <th class="py-3.5 px-6 text-right">Tindakan</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($categoriesData as $category)
                            @php
                                $catKey = 'cat-mat-' . $category->id;
                                $materialsWithInv = $category->materials->filter(fn($m) => $m->inventories->isNotEmpty());
                                $catMaterialCount = $materialsWithInv->count();
                                $typeGroups = $materialsWithInv->groupBy(function($m) {
                                    if (!empty($m->type)) return $m->type;
                                    if (!empty($m->size) && str_ends_with($m->name, $m->size)) {
                                        $inferred = trim(substr($m->name, 0, -strlen($m->size)));
                                        if (!empty($inferred)) return $inferred;
                                    }
                                    return $m->category?->name ?? 'Umum';
                                });
                                $avatarGradient = $gradientPalette[$category->id % count($gradientPalette)];
                            @endphp

                            @if($materialsWithInv->isNotEmpty())
                                <!-- GRUP KATEGORI ACCORDION -->
                                <tr @click="toggleGroup('{{ $catKey }}')" class="bg-slate-50/80 hover:bg-slate-100/90 cursor-pointer transition select-none border-b border-slate-200/60">
                                    <td colspan="7" class="py-3 px-6">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <span class="w-6 h-6 rounded-lg bg-white border border-slate-200 flex items-center justify-center shadow-2xs">
                                                    <svg :class="openGroups.includes('{{ $catKey }}') ? 'rotate-90 text-orange-600' : 'text-slate-400'" 
                                                        class="w-3.5 h-3.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                </span>
                                                <span class="font-bold tracking-wide text-slate-800">
                                                    {{ strtoupper($category->name) }}
                                                </span>
                                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/70">
                                                    {{ $catMaterialCount }} Varian Material
                                                </span>
                                            </div>
                                            <span class="text-[11px] font-medium text-slate-500 hover:text-slate-800" x-text="openGroups.includes('{{ $catKey }}') ? 'Lipat Grup' : 'Buka Grup'"></span>
                                        </div>
                                    </td>
                                </tr>

                                <!-- BARIS MATERIAL DI DALAM KATEGORI -->
                                @foreach($typeGroups as $typeName => $materialsInType)
                                    @foreach($materialsInType as $material)
                                        @foreach($material->inventories as $inv)
                                            @php
                                                // Generate 2-Letter Monogram Initials
                                                $words = preg_split('/\s+/', trim($material->name));
                                                if (count($words) >= 2) {
                                                    $rawInit = mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1);
                                                } else {
                                                    $rawInit = mb_substr($material->name, 0, 2);
                                                }
                                                $initials = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $rawInit) ?: 'MT');

                                                // Compute Visual Stock Capacity
                                                $qty = (float) $inv->quantity;
                                                $minStock = (float) $inv->min_stock;
                                                if ($minStock > 0) {
                                                    $percent = min(100, max(6, round(($qty / max(1, $minStock * 2)) * 100)));
                                                } else {
                                                    $percent = $qty > 0 ? 100 : 0;
                                                }

                                                if ($qty <= 0) {
                                                    $barClass = 'bg-rose-500';
                                                    $barWidth = '0%';
                                                    $statusText = 'Stok Habis';
                                                    $statusBadge = 'bg-rose-50 text-rose-700 border-rose-200';
                                                    $dotColor = 'bg-rose-500';
                                                } elseif ($minStock > 0 && $qty <= $minStock) {
                                                    $barClass = 'bg-amber-500';
                                                    $barWidth = $percent . '%';
                                                    $statusText = 'Menipis';
                                                    $statusBadge = 'bg-amber-50 text-amber-700 border-amber-200';
                                                    $dotColor = 'bg-amber-500';
                                                } else {
                                                    $barClass = 'bg-emerald-500';
                                                    $barWidth = $percent . '%';
                                                    $statusText = 'Stok Aman';
                                                    $statusBadge = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                                    $dotColor = 'bg-emerald-500';
                                                }
                                            @endphp

                                            <tr x-show="openGroups.includes('{{ $catKey }}') && matchesSearch('{{ addslashes($material->name) }}', '{{ addslashes($material->sku ?? '') }}', '{{ addslashes($material->brand ?? '') }}', '{{ addslashes($material->type ?? '') }}')"
                                                class="group hover:bg-orange-50/40 transition">
                                                <!-- Kolom 1: Monogram Avatar + Nama + Pill SKU -->
                                                <td class="py-4 px-6">
                                                    <div class="flex items-center gap-3.5">
                                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $avatarGradient }} text-white font-bold text-xs flex items-center justify-center shadow-xs shrink-0 select-none">
                                                            {{ $initials }}
                                                        </div>
                                                        <div>
                                                            <div class="font-bold text-slate-900 text-[13px] group-hover:text-orange-600 transition">
                                                                {{ $material->name }}
                                                            </div>
                                                            <div class="flex items-center gap-2 mt-1 flex-wrap">
                                                                <span class="px-1.5 py-0.5 rounded bg-slate-100 font-mono text-[10px] font-semibold text-slate-700 border border-slate-200">
                                                                    {{ $material->sku ?? '-' }}
                                                                </span>
                                                                @if($material->brand)
                                                                    <span class="text-[11px] text-slate-500">{{ $material->brand }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>

                                                <!-- Kolom 2: Spesifikasi & Kelompok -->
                                                <td class="py-4 px-4">
                                                    <div class="font-semibold text-slate-800">{{ $material->type ?: '-' }}</div>
                                                    @if($material->size)
                                                        <div class="text-[11px] text-slate-500 mt-0.5">{{ $material->size }}</div>
                                                    @endif
                                                </td>

                                                <!-- Kolom 3: Titik Gudang -->
                                                <td class="py-4 px-4">
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100/90 text-slate-700 font-medium text-[11px] border border-slate-200/60">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                                        {{ $inv->warehouse?->name ?? '-' }}
                                                    </span>
                                                </td>

                                                <!-- Kolom 4: Visual Stock Meter -->
                                                <td class="py-4 px-4">
                                                    <div class="flex items-baseline justify-between mb-1.5">
                                                        <span class="text-sm font-extrabold text-slate-900 tabular-nums">
                                                            {{ number_format($inv->quantity, 0, ',', '.') }} 
                                                            <span class="text-[11px] font-normal text-slate-500">
                                                                {{ $material->unit?->abbreviation ?? $material->unit?->name }}
                                                            </span>
                                                        </span>
                                                        <span class="text-[10px] font-medium text-slate-400">
                                                            Min: {{ number_format($inv->min_stock, 0, ',', '.') }}
                                                        </span>
                                                    </div>
                                                    <!-- Progress Bar Stok -->
                                                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                                        <div class="{{ $barClass }} h-full rounded-full transition-all duration-300" style="width: {{ $barWidth }}"></div>
                                                    </div>
                                                </td>

                                                <!-- Kolom 5: Badge Status -->
                                                <td class="py-4 px-4 text-center">
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $statusBadge }}">
                                                        <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }}"></span> 
                                                        {{ $statusText }}
                                                    </span>
                                                </td>

                                                <!-- Kolom 6: Waktu Update -->
                                                <td class="py-4 px-4">
                                                    <div class="text-slate-800 font-medium">
                                                        {{ $inv->updated_at ? $inv->updated_at->format('d M Y') : '-' }}
                                                    </div>
                                                    <div class="text-[11px] text-slate-400">
                                                        {{ $inv->updated_at ? 'Pukul ' . $inv->updated_at->format('H:i') . ' WIB' : '-' }}
                                                    </div>
                                                </td>

                                                <!-- Kolom 7: Tombol Aksi Interaktif -->
                                                <td class="py-4 px-6 text-right">
                                                    <a href="{{ route('inventory.show', $inv) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:border-orange-500 hover:text-orange-600 shadow-2xs transition">
                                                        <x-lucide-eye class="w-3.5 h-3.5" />
                                                        <span>Kartu Stok</span>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                @endforeach
                            @endif
                        @empty
                            <tr>
                                <td colspan="7" class="p-12 text-center">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3.5 border border-slate-200">
                                        <x-lucide-boxes class="w-6 h-6" />
                                    </div>
                                    <h3 class="text-sm font-bold text-slate-800">Tidak Ada Data Material</h3>
                                    <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">
                                        Tidak ditemukan catatan inventori material yang sesuai dengan kriteria filter saat ini.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- ================= TAB 2: TABEL ALAT KERJA & ARMADA ================= -->
            <div x-show="activeTab === 'alat'" style="display: none;" class="overflow-x-auto">
                @if($toolsCategoriesData->isEmpty())
                    <div class="p-14 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3.5 border border-slate-200">
                            <x-lucide-wrench class="w-6 h-6" />
                        </div>
                        <h3 class="text-sm font-bold text-slate-800">Belum Ada Unit Alat Kerja Terdaftar</h3>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">
                            Data inventaris alat kerja dan posisi peminjaman proyek akan tampil di sini setelah ditambahkan melalui modul Alat.
                        </p>
                    </div>
                @else
                    <div class="px-6 py-2.5 bg-slate-50/70 border-b border-slate-200/70 flex items-center justify-between text-xs text-slate-500">
                        <span class="font-medium">Total <strong class="text-slate-800">{{ $totalToolsCount }} jenis alat</strong> terdaftar</span>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="expandAll()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white border border-slate-200 hover:text-orange-600 font-medium transition cursor-pointer shadow-2xs">
                                <x-lucide-chevrons-down class="w-3.5 h-3.5" />
                                <span>Buka Semua</span>
                            </button>
                            <button type="button" @click="collapseAll()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white border border-slate-200 hover:text-orange-600 font-medium transition cursor-pointer shadow-2xs">
                                <x-lucide-chevrons-up class="w-3.5 h-3.5" />
                                <span>Tutup Semua</span>
                            </button>
                        </div>
                    </div>

                    <table class="w-full text-left border-collapse min-w-[960px]">
                        <thead>
                            <tr class="bg-slate-50/90 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="py-3.5 px-6">Nama Alat & Kode</th>
                                <th class="py-3.5 px-4">Merk & Spesifikasi</th>
                                <th class="py-3.5 px-4">Titik Lokasi / Site</th>
                                <th class="py-3.5 px-4 text-center">Total Unit</th>
                                <th class="py-3.5 px-4 text-center">Siap Pakai</th>
                                <th class="py-3.5 px-4 text-center">Di Proyek</th>
                                <th class="py-3.5 px-4 text-center">Status Level</th>
                                <th class="py-3.5 px-6 text-right">Tindakan</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 text-xs">
                            @foreach($toolsCategoriesData as $toolCategory)
                                @php
                                    $toolCatKey = 'cat-tool-' . $toolCategory->id;
                                    $catToolCount = $toolCategory->tools->count();
                                @endphp

                                <!-- GRUP KATEGORI ALAT -->
                                <tr @click="toggleGroup('{{ $toolCatKey }}')" class="bg-slate-50/80 hover:bg-slate-100/90 cursor-pointer transition select-none border-b border-slate-200/60">
                                    <td colspan="8" class="py-3 px-6">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <span class="w-6 h-6 rounded-lg bg-white border border-slate-200 flex items-center justify-center shadow-2xs">
                                                    <svg :class="openGroups.includes('{{ $toolCatKey }}') ? 'rotate-90 text-amber-600' : 'text-slate-400'" 
                                                        class="w-3.5 h-3.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                </span>
                                                <span class="font-bold tracking-wide text-slate-800">
                                                    DIVISI {{ strtoupper($toolCategory->name) }}
                                                </span>
                                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200/70">
                                                    {{ $catToolCount }} Jenis Alat
                                                </span>
                                            </div>
                                            <span class="text-[11px] font-medium text-slate-500 hover:text-slate-800" x-text="openGroups.includes('{{ $toolCatKey }}') ? 'Lipat Grup' : 'Buka Grup'"></span>
                                        </div>
                                    </td>
                                </tr>

                                <!-- BARIS ALAT -->
                                @foreach($toolCategory->tools as $tool)
                                    @php
                                        $whName = ($tool->relationLoaded('inventories') && $tool->inventories->isNotEmpty())
                                            ? ($tool->inventories->first()->warehouse?->name ?? '-')
                                            : ($tool->currentWarehouse?->name ?? 'Gudang Pusat');

                                        // Monogram for tool
                                        $words = preg_split('/\s+/', trim($tool->name));
                                        if (count($words) >= 2) {
                                            $rawInit = mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1);
                                        } else {
                                            $rawInit = mb_substr($tool->name, 0, 2);
                                        }
                                        $initialsTool = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $rawInit) ?: 'EQ');
                                    @endphp

                                    <tr x-show="openGroups.includes('{{ $toolCatKey }}') && matchesSearch('{{ addslashes($tool->name) }}', '{{ addslashes($tool->code ?? '') }}', '{{ addslashes($tool->brand ?? '') }}', '{{ addslashes($tool->type ?? '') }}')"
                                        class="group hover:bg-amber-50/25 transition">
                                        <!-- Kolom 1: Avatar + Nama Alat + Kode -->
                                        <td class="py-4 px-6">
                                            <div class="flex items-center gap-3.5">
                                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white font-bold text-xs flex items-center justify-center shadow-xs shrink-0 select-none">
                                                    {{ $initialsTool }}
                                                </div>
                                                <div>
                                                    <div class="font-bold text-slate-900 text-[13px] group-hover:text-orange-600 transition">
                                                        {{ $tool->name }}
                                                    </div>
                                                    <div class="flex items-center gap-2 mt-1 flex-wrap">
                                                        <span class="px-1.5 py-0.5 rounded bg-slate-100 font-mono text-[10px] font-semibold text-slate-700 border border-slate-200">
                                                            {{ $tool->code ?? '-' }}
                                                        </span>
                                                        @if($tool->brand)
                                                            <span class="text-[11px] text-slate-500">{{ $tool->brand }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Kolom 2: Merk & Spesifikasi -->
                                        <td class="py-4 px-4">
                                            <div class="font-semibold text-slate-800">{{ $tool->type ?: '-' }}</div>
                                            @if($tool->size)
                                                <div class="text-[11px] text-slate-500 mt-0.5">{{ $tool->size }}</div>
                                            @endif
                                        </td>

                                        <!-- Kolom 3: Lokasi Site -->
                                        <td class="py-4 px-4">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100/90 text-slate-700 font-medium text-[11px] border border-slate-200/60">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                {{ $whName }}
                                            </span>
                                        </td>

                                        <!-- Kolom 4: Total Unit -->
                                        <td class="py-4 px-4 text-center">
                                            <span class="font-extrabold text-slate-900 tabular-nums text-sm">
                                                {{ number_format($tool->stock_total, 0, ',', '.') }}
                                            </span>
                                            <span class="text-[11px] text-slate-500">unit</span>
                                        </td>

                                        <!-- Kolom 5: Siap Pakai -->
                                        <td class="py-4 px-4 text-center">
                                            <span class="font-extrabold text-emerald-600 tabular-nums text-sm">
                                                {{ number_format($tool->stock_available, 0, ',', '.') }}
                                            </span>
                                            <span class="text-[11px] text-slate-500">unit</span>
                                        </td>

                                        <!-- Kolom 6: Di Proyek -->
                                        <td class="py-4 px-4 text-center">
                                            <span class="font-bold text-slate-700 tabular-nums text-xs">
                                                {{ number_format($tool->stock_borrowed, 0, ',', '.') }}
                                            </span>
                                        </td>

                                        <!-- Kolom 7: Status Level -->
                                        <td class="py-4 px-4 text-center">
                                            @if($tool->stock_available > 0)
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Siap Pakai
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Dipinjam / Habis
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Kolom 8: Tindakan -->
                                        <td class="py-4 px-6 text-right">
                                            <a href="{{ route('tools.show', $tool) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:border-orange-500 hover:text-orange-600 shadow-2xs transition">
                                                <x-lucide-eye class="w-3.5 h-3.5" />
                                                <span>Detail</span>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

        </div>

    </div>
</x-app-layout>
