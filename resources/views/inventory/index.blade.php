<x-app-layout>
    <x-slot name="title">Inventaris Material & Gudang</x-slot>

    @php
        $totalMatVariants = $categoriesData->sum(fn($c) => $c->materials->count());
        $totalMatStock = $categoriesData->sum(fn($c) => $c->materials->sum(fn($m) => $m->inventories->sum('quantity')));

        $safeCount = 0; $lowCount = 0; $critCount = 0;
        foreach ($categoriesData as $c) {
            foreach ($c->materials as $m) {
                foreach ($m->inventories as $inv) {
                    $qty = (float) $inv->quantity;
                    $min = (float) $inv->min_stock;
                    if ($qty <= 0) {
                        $critCount++;
                    } elseif ($min > 0 && $qty <= $min) {
                        $lowCount++;
                    } else {
                        $safeCount++;
                    }
                }
            }
        }

        $invRowTotal = $safeCount + $lowCount + $critCount;
        $amanPct = $invRowTotal > 0 ? round($safeCount / $invRowTotal * 100) : 100;

        $totalToolsCount = $toolsCategoriesData->sum(fn($c) => $c->tools->count());
        $totalToolStockTotal = $toolsCategoriesData->sum(fn($c) => $c->tools->sum('stock_total'));
        $totalToolAvailable = $toolsCategoriesData->sum(fn($c) => $c->tools->sum('stock_available'));
        $totalToolBorrowed = $toolsCategoriesData->sum(fn($c) => $c->tools->sum('stock_borrowed'));
        $totalToolMaintenance = $toolsCategoriesData->sum(fn($c) => $c->tools->sum('stock_maintenance'));

        $groupKey = fn($name) => trim((string) $name) ?: 'Lainnya';
        $sortGroup = fn($key) => $key === 'Lainnya' ? 'zzz' : mb_strtolower($key);

        $allCategoryKeys = [];
        $firstSubKeys = [];
        foreach ($categoriesData as $cat) {
            $key = 'cat-mat-' . $cat->id;
            $allCategoryKeys[] = $key;
            $items = $cat->materials->filter(fn($m) => $m->inventories->isNotEmpty());
            $groups = $items->groupBy(fn($m) => $groupKey($m->type))->sortBy(fn($i, $k) => $sortGroup($k));
            $first = $groups->keys()->first();
            if ($first !== null) {
                $firstSubKeys[] = $key . '-' . md5($first);
            }
        }
        foreach ($toolsCategoriesData as $tCat) {
            $key = 'cat-tool-' . $tCat->id;
            $allCategoryKeys[] = $key;
            $groups = $tCat->tools->groupBy(fn($t) => $groupKey($t->type))->sortBy(fn($i, $k) => $sortGroup($k));
            $first = $groups->keys()->first();
            if ($first !== null) {
                $firstSubKeys[] = $key . '-' . md5($first);
            }
        }

        $currentWhObj = is_numeric($selectedWarehouseId ?? null) ? $accessibleWarehouses->firstWhere('id', (int) $selectedWarehouseId) : null;
        $whSub = $currentWhObj ? $currentWhObj->name : 'semua lokasi gudang';
        $today = \Carbon\Carbon::now()->locale('id')->translatedFormat('l, j F Y');
    @endphp

    <div x-data="{
            activeTab: @js(request('item_type') === 'tool' ? 'alat' : 'material'),
            searchQuery: @js((string) request('search', '')),
            openGroups: @js($allCategoryKeys),
            openSubs: @js($firstSubKeys),
            toggleGroup(group) {
                const state = window.ArsikonAnimate ? window.ArsikonAnimate.flipGetState(this.$el) : null;
                if (this.openGroups.includes(group)) {
                    this.openGroups = this.openGroups.filter(g => g !== group);
                } else {
                    this.openGroups.push(group);
                }
                if (state) this.$nextTick(() => window.ArsikonAnimate.flipFrom(state));
            },
            toggleSub(divId, subKey) {
                const state = window.ArsikonAnimate ? window.ArsikonAnimate.flipGetState(this.$el) : null;
                if (this.openSubs.includes(subKey)) {
                    this.openSubs = this.openSubs.filter(k => k !== subKey);
                } else {
                    this.openSubs = [...this.openSubs.filter(k => !k.startsWith(divId)), subKey];
                }
                if (state) this.$nextTick(() => window.ArsikonAnimate.flipFrom(state));
            },
            switchTab(tab) {
                if (this.activeTab === tab) return;
                const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                this.activeTab = tab;
                if (!reduce && window.gsap) {
                    this.$nextTick(() => {
                        const els = Array.from(this.$el.querySelectorAll('[data-animate]'))
                            .filter(el => (el.getAttribute('data-animate') || '').split(/\s+/).includes('tab-strip'));
                        if (els.length) {
                            window.gsap.fromTo(els, { opacity: 0.35 }, { opacity: 1, duration: 0.25, ease: 'power1.out', clearProps: 'opacity' });
                        }
                    });
                }
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
        class="w-full"
        @keydown.window="if ($event.key === '/' && !['INPUT', 'SELECT', 'TEXTAREA'].includes($event.target.tagName)) { $event.preventDefault(); $refs.invSearch.focus(); }">

        {{-- ==================== HEADER ==================== --}}

        <header class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mt-1" data-animate="header">
            <div>
                <h1 class="text-[24px] leading-tight font-extrabold tracking-tight text-[#111111]">Inventaris</h1>
                <p class="text-[13px] text-gray-500 mt-1" x-text="activeTab === 'alat' ? @js('Stok alat & armada di ' . $whSub) : @js('Stok material di ' . $whSub)"></p>
            </div>
            <template x-if="activeTab === 'material'">
                <div>
                    @can('create materials')
                    <a href="{{ route('materials.create') }}" class="h-11 px-6 inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-[#ea580c] hover:bg-[#d9480c] text-white text-[13px] font-semibold transition shadow-sm">
                        Tambah material
                    </a>
                    @endcan
                </div>
            </template>
            <template x-if="activeTab === 'alat'">
                <div>
                    <a href="{{ route('tools.create') }}" class="h-11 px-6 inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-[#ea580c] hover:bg-[#d9480c] text-white text-[13px] font-semibold transition shadow-sm">
                        Tambah alat
                    </a>
                </div>
            </template>
        </header>

        {{-- ==================== KPI STRIP — MATERIAL ==================== --}}
        <section x-show="activeTab === 'material'" data-animate="tab-strip panel" class="mt-5 bg-white border border-[#e9e9e7] rounded-2xl grid grid-cols-2 md:grid-cols-4 divide-x divide-y md:divide-y-0 divide-[#efefef] overflow-hidden">
            <div class="p-5">
                <div class="flex items-start justify-between gap-2">
                    <span class="text-[12px] text-gray-500">Total stok fisik</span>
                    <span class="w-8 h-8 rounded-[9px] bg-[#e9efff] text-[#2f6df6] flex items-center justify-center flex-shrink-0"><x-lucide-package class="w-4 h-4" /></span>
                </div>
                <div class="text-[26px] leading-none font-extrabold text-[#111111] tabular-nums mt-2">{{ number_format($totalMatStock, 0, ',', '.') }}</div>
                <div class="text-[11px] text-gray-500 mt-2">{{ $totalMatVariants }} SKU di {{ $categoriesData->count() }} divisi</div>
            </div>
            <div class="p-5">
                <div class="flex items-start justify-between gap-2">
                    <span class="text-[12px] text-gray-500">Aman</span>
                    <span class="w-8 h-8 rounded-[9px] bg-[#e6f7ec] text-[#16a34a] flex items-center justify-center flex-shrink-0"><x-lucide-circle-check class="w-4 h-4" /></span>
                </div>
                <div class="text-[26px] leading-none font-extrabold text-[#111111] tabular-nums mt-2">{{ $safeCount }}</div>
                <div class="text-[11px] text-gray-500 mt-2">{{ $amanPct }}% dari SKU</div>
            </div>
            <div class="p-5">
                <div class="flex items-start justify-between gap-2">
                    <span class="text-[12px] text-gray-500">Menipis</span>
                    <span class="w-8 h-8 rounded-[9px] bg-[#fdf3dd] text-[#d97706] flex items-center justify-center flex-shrink-0"><x-lucide-triangle-alert class="w-4 h-4" /></span>
                </div>
                <div class="text-[26px] leading-none font-extrabold text-[#111111] tabular-nums mt-2">{{ $lowCount }}</div>
                <div class="text-[11px] text-gray-500 mt-2">{{ $lowCount > 0 ? $lowCount . ' SKU di bawah batas min' : 'Semua di atas batas min' }}</div>
            </div>
            <div class="p-5">
                <div class="flex items-start justify-between gap-2">
                    <span class="text-[12px] text-gray-500">Kritis atau habis</span>
                    <span class="w-8 h-8 rounded-[9px] bg-[#fdeaea] text-[#dc2626] flex items-center justify-center flex-shrink-0"><x-lucide-octagon-x class="w-4 h-4" /></span>
                </div>
                <div class="text-[26px] leading-none font-extrabold text-[#111111] tabular-nums mt-2">{{ $critCount }}</div>
                <div class="text-[11px] text-gray-500 mt-2">{{ $critCount > 0 ? $critCount . ' SKU perlu dipesan' : 'Tidak ada yang perlu dipesan' }}</div>
            </div>
        </section>

        {{-- ==================== KPI STRIP — ALAT ==================== --}}
        <section x-show="activeTab === 'alat'" x-cloak data-animate="tab-strip panel" class="mt-5 bg-white border border-[#e9e9e7] rounded-2xl grid grid-cols-2 md:grid-cols-4 divide-x divide-y md:divide-y-0 divide-[#efefef] overflow-hidden">
            <div class="p-5">
                <div class="flex items-start justify-between gap-2">
                    <span class="text-[12px] text-gray-500">Total alat</span>
                    <span class="w-8 h-8 rounded-[9px] bg-[#e9efff] text-[#2f6df6] flex items-center justify-center flex-shrink-0"><x-lucide-wrench class="w-4 h-4" /></span>
                </div>
                <div class="text-[26px] leading-none font-extrabold text-[#111111] tabular-nums mt-2">{{ number_format($totalToolStockTotal, 0, ',', '.') }}</div>
                <div class="text-[11px] text-gray-500 mt-2">{{ $totalToolsCount }} jenis alat terdata</div>
            </div>
            <div class="p-5">
                <div class="flex items-start justify-between gap-2">
                    <span class="text-[12px] text-gray-500">Siap pakai</span>
                    <span class="w-8 h-8 rounded-[9px] bg-[#e6f7ec] text-[#16a34a] flex items-center justify-center flex-shrink-0"><x-lucide-circle-check class="w-4 h-4" /></span>
                </div>
                <div class="text-[26px] leading-none font-extrabold text-[#111111] tabular-nums mt-2">{{ number_format($totalToolAvailable, 0, ',', '.') }}</div>
                <div class="text-[11px] text-gray-500 mt-2">unit standby di gudang</div>
            </div>
            <div class="p-5">
                <div class="flex items-start justify-between gap-2">
                    <span class="text-[12px] text-gray-500">Di proyek</span>
                    <span class="w-8 h-8 rounded-[9px] bg-[#fdf3dd] text-[#d97706] flex items-center justify-center flex-shrink-0"><x-lucide-hard-hat class="w-4 h-4" /></span>
                </div>
                <div class="text-[26px] leading-none font-extrabold text-[#111111] tabular-nums mt-2">{{ number_format($totalToolBorrowed, 0, ',', '.') }}</div>
                <div class="text-[11px] text-gray-500 mt-2">unit sedang dipinjam</div>
            </div>
            <div class="p-5">
                <div class="flex items-start justify-between gap-2">
                    <span class="text-[12px] text-gray-500">Perawatan</span>
                    <span class="w-8 h-8 rounded-[9px] bg-[#fdeaea] text-[#dc2626] flex items-center justify-center flex-shrink-0"><x-lucide-settings class="w-4 h-4" /></span>
                </div>
                <div class="text-[26px] leading-none font-extrabold text-[#111111] tabular-nums mt-2">{{ number_format($totalToolMaintenance, 0, ',', '.') }}</div>
                <div class="text-[11px] text-gray-500 mt-2">unit dalam servis</div>
            </div>
        </section>

        {{-- ==================== TABS ==================== --}}
        <div class="mt-6 border-b border-[#e7e7e5]">
            <div class="flex items-center gap-6" role="tablist" aria-label="Jenis inventori">
                <button type="button" role="tab" :aria-selected="activeTab === 'material'" @click="switchTab('material')"
                    :class="activeTab === 'material' ? 'border-[#ea580c] text-[#111111] font-semibold' : 'border-transparent text-gray-500 hover:text-[#111111]'"
                    class="pb-2.5 -mb-px border-b-2 text-[13px] transition inline-flex items-center gap-1.5 cursor-pointer">
                    Material <span class="text-[12px] text-gray-400 font-normal tabular-nums">{{ $totalMatVariants }}</span>
                </button>
                <button type="button" role="tab" :aria-selected="activeTab === 'alat'" @click="switchTab('alat')"
                    :class="activeTab === 'alat' ? 'border-[#ea580c] text-[#111111] font-semibold' : 'border-transparent text-gray-500 hover:text-[#111111]'"
                    class="pb-2.5 -mb-px border-b-2 text-[13px] transition inline-flex items-center gap-1.5 cursor-pointer">
                    Alat <span class="text-[12px] text-gray-400 font-normal tabular-nums">{{ $totalToolsCount }}</span>
                </button>
            </div>
        </div>

        {{-- ==================== FILTER ==================== --}}
        <form method="GET" action="{{ route('inventory.index') }}" class="mt-4 flex flex-col sm:flex-row gap-3">
            <input type="hidden" name="item_type" :value="activeTab === 'material' ? 'material' : 'tool'">

            <div class="relative flex-1">
                <x-lucide-search class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400" />
                <label class="sr-only" for="inv-search">Cari</label>
                <input type="text" id="inv-search" x-ref="invSearch" name="search" value="{{ request('search') }}" x-model="searchQuery"
                    placeholder="Cari nama, SKU, atau kategori" autocomplete="off"
                    class="w-full h-12 pl-11 pr-4 rounded-xl bg-white border border-[#e2e2e0] text-[13px] text-[#111111] placeholder-gray-400 focus:outline-none focus:border-[#ea580c] focus:ring-2 focus:ring-[#ea580c]/20 transition">
            </div>

            <div class="relative sm:w-[280px]">
                <label class="sr-only" for="inv-warehouse">Gudang</label>
                <select id="inv-warehouse" name="warehouse_id" onchange="this.form.submit()"
                    class="w-full h-12 appearance-none pl-4 pr-10 rounded-xl bg-white border border-[#e2e2e0] text-[13px] text-[#111111] focus:outline-none focus:border-[#ea580c] cursor-pointer">
                    @if(!empty($canViewAllWarehouses))
                        <option value="all" {{ ($selectedWarehouseId === 'all') ? 'selected' : '' }}>Semua lokasi gudang</option>
                    @endif
                    @foreach($accessibleWarehouses as $wh)
                        <option value="{{ $wh->id }}" {{ (string)$selectedWarehouseId === (string)$wh->id ? 'selected' : '' }}>{{ $wh->name }}{{ $wh->is_central ? ' (Pusat)' : '' }}</option>
                    @endforeach
                </select>
                <x-lucide-chevron-down class="w-4 h-4 absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400" />
            </div>

            <div class="relative sm:w-[180px]">
                <label class="sr-only" for="inv-stock-level">Status stok</label>
                <select id="inv-stock-level" name="stock_level" onchange="this.form.submit()"
                    class="w-full h-12 appearance-none pl-4 pr-10 rounded-xl bg-white border border-[#e2e2e0] text-[13px] text-[#111111] focus:outline-none focus:border-[#ea580c] cursor-pointer">
                    <option value="">Semua status</option>
                    <option value="low" {{ request('stock_level') === 'low' ? 'selected' : '' }}>Stok menipis</option>
                    <option value="out" {{ request('stock_level') === 'out' ? 'selected' : '' }}>Kritis / habis</option>
                </select>
                <x-lucide-chevron-down class="w-4 h-4 absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400" />
            </div>
        </form>

        {{-- ==================== TREE MATERIAL ==================== --}}
        <div x-show="activeTab === 'material'" class="hidden md:block mt-4 bg-white border border-[#e9e9e7] rounded-2xl overflow-hidden overflow-x-auto">
            <div class="min-w-[880px]">
                <div class="grid grid-cols-[44px_150px_minmax(0,1fr)_200px_96px] gap-4 px-5 py-2.5 bg-[#fcfcfb] border-b border-[#ececec] text-[10.5px] font-semibold uppercase tracking-wider text-gray-400">
                    <span>No</span><span>SKU</span><span>Material</span><span>Supplier</span><span class="text-right">Stok</span>
                </div>
                @php $globalRowIdx = 1; @endphp
                @forelse($categoriesData as $category)
                    @php
                        $catKey = 'cat-mat-' . $category->id;
                        $materialsWithInv = $category->materials->filter(fn($m) => $m->inventories->isNotEmpty());
                        $catMaterialCount = $materialsWithInv->count();
                        $groupedByType = $materialsWithInv->groupBy(fn($m) => $groupKey($m->type))->sortBy(fn($i, $k) => $sortGroup($k));
                    @endphp

                    @if($materialsWithInv->isNotEmpty())
                        <div class="border-b border-[#e5e9f0] last:border-b-0">
                            <button type="button" @click="toggleGroup('{{ $catKey }}')" :aria-expanded="openGroups.includes('{{ $catKey }}')"
                                class="w-full flex items-center gap-2.5 px-5 py-3 bg-[#e9edf5] hover:bg-[#e2e8f1] transition text-left cursor-pointer">
                                <svg :class="openGroups.includes('{{ $catKey }}') ? 'rotate-90 text-[#334155]' : 'text-[#94a3b8]'" class="w-4 h-4 transition-transform duration-200 flex-shrink-0" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                </svg>
                                <span class="text-[13px] font-extrabold tracking-wide text-[#111827] uppercase">{{ strtoupper(trim(preg_replace('/^DIVISI\s+/i', '', $category->name))) }}</span>
                                <span class="text-[11.5px] font-medium text-[#64748b]">({{ $catMaterialCount }} material)</span>
                            </button>

                            <div x-show="openGroups.includes('{{ $catKey }}')" x-cloak>
                                @foreach($groupedByType as $subLabel => $subItems)
                                    @php $subKey = $catKey . '-' . md5($subLabel); @endphp
                                    <button type="button" data-flip @click="toggleSub('{{ $catKey }}', '{{ $subKey }}')" :aria-expanded="openSubs.includes('{{ $subKey }}')"
                                        class="w-full flex items-center gap-2 pl-11 pr-5 py-2.5 bg-[#eef1f7] hover:bg-[#e6ebf3] border-t border-[#e3e8ef] transition text-left cursor-pointer">
                                        <svg :class="openSubs.includes('{{ $subKey }}') ? 'rotate-90 text-[#334155]' : 'text-[#94a3b8]'" class="w-3.5 h-3.5 transition-transform duration-200 flex-shrink-0" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                        </svg>
                                        <span class="text-[12px] font-bold text-[#1f2937]">{{ $subLabel }}</span>
                                        <span class="text-[11px] text-[#64748b]">({{ $subItems->count() }})</span>
                                    </button>

                                    <div x-show="openSubs.includes('{{ $subKey }}')" x-cloak>
                                        @foreach($subItems as $material)
                                            @foreach($material->inventories as $inv)
                                                <div data-flip data-animate="rows" x-show="matchesSearch(@js($material->name), @js($material->sku ?? ''), @js($material->type ?? ''), @js($category->name))"
                                                    class="grid grid-cols-[44px_150px_minmax(0,1fr)_200px_96px] items-center gap-4 px-5 py-3.5 bg-white border-t border-[#edf0f5]">
                                                    <span class="text-[12px] text-[#94a3b8] tabular-nums">{{ $globalRowIdx++ }}</span>
                                                    <span class="font-mono text-[11.5px] text-[#475569] truncate" title="{{ $material->sku }}">{{ $material->sku ?? '—' }}</span>
                                                    <span class="min-w-0">
                                                        <span class="block text-[13px] font-semibold text-[#111827] leading-snug truncate">{{ $material->name }}</span>
                                                        @if($material->brand)
                                                            <span class="block text-[11px] text-[#64748b] mt-0.5 truncate">Merek: {{ $material->brand }}</span>
                                                        @endif
                                                    </span>
                                                    <span class="text-[12px] text-[#1f2937] truncate" title="{{ $material->display_supplier_name }}">{{ $material->display_supplier_name }}</span>
                                                    <span class="text-right">
                                                        <span class="block text-[15px] font-extrabold text-[#111827] tabular-nums leading-none">{{ number_format($inv->quantity, 0, ',', '.') }}</span>
                                                        <span class="block text-[10px] text-[#94a3b8] mt-1">{{ $material->unit?->abbreviation ?? $material->unit?->name ?? '' }}</span>
                                                    </span>
                                                </div>
                                            @endforeach
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="py-14 px-6 text-center">
                        <x-lucide-package-search class="w-9 h-9 mx-auto text-gray-300" />
                        <h4 class="text-[13px] font-bold text-[#111111] mt-3">Tidak ada data material</h4>
                        <p class="text-[12px] text-gray-500 mt-1.5 max-w-sm mx-auto">Tidak ditemukan material yang sesuai dengan filter atau pencarian. Coba longgarkan kata kunci.</p>
                        <a href="{{ route('inventory.index') }}" class="inline-flex items-center h-10 px-5 mt-5 rounded-full border border-[#e2e2e0] text-[12px] font-semibold text-gray-600 hover:text-[#ea580c] hover:border-[#ea580c] transition">Reset filter</a>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ==================== TREE MATERIAL (mobile) ==================== --}}
        <div x-show="activeTab === 'material'" class="md:hidden mt-4 bg-white border border-[#e9e9e7] rounded-2xl overflow-hidden">
            @forelse($categoriesData as $category)
                @php
                    $catKey = 'cat-mat-' . $category->id;
                    $materialsWithInv = $category->materials->filter(fn($m) => $m->inventories->isNotEmpty());
                    $catMaterialCount = $materialsWithInv->count();
                    $groupedByType = $materialsWithInv->groupBy(fn($m) => $groupKey($m->type))->sortBy(fn($i, $k) => $sortGroup($k));
                @endphp

                @if($materialsWithInv->isNotEmpty())
                    <div class="border-b border-[#e5e9f0] last:border-b-0">
                        <button type="button" @click="toggleGroup('{{ $catKey }}')" :aria-expanded="openGroups.includes('{{ $catKey }}')"
                            class="w-full flex items-center gap-2 px-4 py-3 bg-[#e9edf5] text-left cursor-pointer">
                            <svg :class="openGroups.includes('{{ $catKey }}') ? 'rotate-90 text-[#334155]' : 'text-[#94a3b8]'" class="w-4 h-4 transition-transform duration-200 flex-shrink-0" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                            <span class="text-[12px] font-extrabold tracking-wide text-[#111827] uppercase">{{ strtoupper(trim(preg_replace('/^DIVISI\s+/i', '', $category->name))) }}</span>
                            <span class="text-[11px] font-medium text-[#64748b]">({{ $catMaterialCount }})</span>
                        </button>

                        <div x-show="openGroups.includes('{{ $catKey }}')" x-cloak>
                            @foreach($groupedByType as $subLabel => $subItems)
                                @php $subKey = $catKey . '-' . md5($subLabel); @endphp
                                <button type="button" data-flip @click="toggleSub('{{ $catKey }}', '{{ $subKey }}')" :aria-expanded="openSubs.includes('{{ $subKey }}')"
                                    class="w-full flex items-center gap-2 pl-10 pr-4 py-2.5 bg-[#eef1f7] border-t border-[#e3e8ef] text-left cursor-pointer">
                                    <svg :class="openSubs.includes('{{ $subKey }}') ? 'rotate-90 text-[#334155]' : 'text-[#94a3b8]'" class="w-3.5 h-3.5 transition-transform duration-200 flex-shrink-0" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                    </svg>
                                    <span class="text-[11.5px] font-bold text-[#1f2937]">{{ $subLabel }}</span>
                                    <span class="text-[10.5px] text-[#64748b]">({{ $subItems->count() }})</span>
                                </button>

                                <div x-show="openSubs.includes('{{ $subKey }}')" x-cloak>
                                    @foreach($subItems as $material)
                                        @foreach($material->inventories as $inv)
                                            <div data-flip data-animate="rows" x-show="matchesSearch(@js($material->name), @js($material->sku ?? ''), @js($material->type ?? ''), @js($category->name))"
                                                class="bg-white border-t border-[#edf0f5] px-4 py-3">
                                                <div class="flex items-center justify-between gap-3">
                                                    <span class="font-mono text-[10.5px] text-[#64748b] truncate">{{ $material->sku }}</span>
                                                    <span class="text-[13px] font-extrabold text-[#111827] tabular-nums flex-shrink-0">{{ number_format($inv->quantity, 0, ',', '.') }} <span class="text-[10px] font-medium text-gray-400">{{ $material->unit?->abbreviation ?? '' }}</span></span>
                                                </div>
                                                <div class="text-[12.5px] font-semibold text-[#111827] leading-snug mt-1.5">{{ $material->name }}</div>
                                                <div class="flex items-center justify-between gap-3 mt-1">
                                                    <span class="text-[10px] text-[#64748b] truncate">{{ $material->brand ? 'Merek: ' . $material->brand : '' }}</span>
                                                    <span class="text-[10px] text-[#475569] truncate max-w-[55%] text-right">{{ $material->display_supplier_name }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @empty
                <div class="py-14 px-6 text-center">
                    <x-lucide-package-search class="w-9 h-9 mx-auto text-gray-300" />
                    <h4 class="text-[13px] font-bold text-[#111111] mt-3">Tidak ada data material</h4>
                    <p class="text-[12px] text-gray-500 mt-1.5">Coba longgarkan kata kunci atau atur ulang filter.</p>
                    <a href="{{ route('inventory.index') }}" class="inline-flex items-center h-10 px-5 mt-5 rounded-full border border-[#e2e2e0] text-[12px] font-semibold text-gray-600">Reset filter</a>
                </div>
            @endforelse
        </div>

        {{-- ==================== TREE ALAT (desktop) ==================== --}}
        <div x-show="activeTab === 'alat'" x-cloak class="hidden md:block mt-4 bg-white border border-[#e9e9e7] rounded-2xl overflow-hidden overflow-x-auto">
            <div class="min-w-[940px]">
                <div class="grid grid-cols-[44px_150px_minmax(0,1fr)_200px_72px_72px_82px] gap-4 px-5 py-2.5 bg-[#fcfcfb] border-b border-[#ececec] text-[10.5px] font-semibold uppercase tracking-wider text-gray-400">
                    <span>No</span><span>Kode</span><span>Alat</span><span>Supplier</span><span class="text-right">Total</span><span class="text-right">Siap</span><span class="text-right">Dipinjam</span>
                </div>
                @php $toolRowIdx = 1; @endphp
                @forelse($toolsCategoriesData as $toolCategory)
                    @php
                        $toolCatKey = 'cat-tool-' . $toolCategory->id;
                        $catToolCount = $toolCategory->tools->count();
                        $toolsByType = $toolCategory->tools->groupBy(fn($t) => $groupKey($t->type))->sortBy(fn($i, $k) => $sortGroup($k));
                    @endphp

                    <div class="border-b border-[#e5e9f0] last:border-b-0">
                        <button type="button" @click="toggleGroup('{{ $toolCatKey }}')" :aria-expanded="openGroups.includes('{{ $toolCatKey }}')"
                            class="w-full flex items-center gap-2.5 px-5 py-3 bg-[#e9edf5] hover:bg-[#e2e8f1] transition text-left cursor-pointer">
                            <svg :class="openGroups.includes('{{ $toolCatKey }}') ? 'rotate-90 text-[#334155]' : 'text-[#94a3b8]'" class="w-4 h-4 transition-transform duration-200 flex-shrink-0" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                            <span class="text-[13px] font-extrabold tracking-wide text-[#111827] uppercase">{{ strtoupper(trim(preg_replace('/^DIVISI\s+/i', '', $toolCategory->name))) }}</span>
                            <span class="text-[11.5px] font-medium text-[#64748b]">({{ $catToolCount }} alat)</span>
                        </button>

                        <div x-show="openGroups.includes('{{ $toolCatKey }}')" x-cloak>
                            @foreach($toolsByType as $subLabel => $subItems)
                                @php $subKey = $toolCatKey . '-' . md5($subLabel); @endphp
                                <button type="button" data-flip @click="toggleSub('{{ $toolCatKey }}', '{{ $subKey }}')" :aria-expanded="openSubs.includes('{{ $subKey }}')"
                                    class="w-full flex items-center gap-2 pl-11 pr-5 py-2.5 bg-[#eef1f7] hover:bg-[#e6ebf3] border-t border-[#e3e8ef] transition text-left cursor-pointer">
                                    <svg :class="openSubs.includes('{{ $subKey }}') ? 'rotate-90 text-[#334155]' : 'text-[#94a3b8]'" class="w-3.5 h-3.5 transition-transform duration-200 flex-shrink-0" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                    </svg>
                                    <span class="text-[12px] font-bold text-[#1f2937]">{{ $subLabel }}</span>
                                    <span class="text-[11px] text-[#64748b]">({{ $subItems->count() }})</span>
                                </button>

                                <div x-show="openSubs.includes('{{ $subKey }}')" x-cloak>
                                    @foreach($subItems as $tool)
                                        <div data-flip data-animate="rows" x-show="matchesSearch(@js($tool->name), @js($tool->code ?? ''), @js($tool->brand ?? ''), @js($toolCategory->name))"
                                            class="grid grid-cols-[44px_150px_minmax(0,1fr)_200px_72px_72px_82px] items-center gap-4 px-5 py-3.5 bg-white border-t border-[#edf0f5]">
                                            <span class="text-[12px] text-[#94a3b8] tabular-nums">{{ $toolRowIdx++ }}</span>
                                            <span class="font-mono text-[11.5px] text-[#475569] truncate" title="{{ $tool->code }}">{{ $tool->code ?? '—' }}</span>
                                            <span class="min-w-0">
                                                <span class="block text-[13px] font-semibold text-[#111827] leading-snug truncate">{{ $tool->name }}</span>
                                                @if($tool->brand)
                                                    <span class="block text-[11px] text-[#64748b] mt-0.5 truncate">Merek: {{ $tool->brand }}</span>
                                                @endif
                                            </span>
                                            <span class="text-[12px] text-[#1f2937] truncate" title="{{ $tool->display_supplier_name }}">{{ $tool->display_supplier_name }}</span>
                                            <span class="text-right text-[13px] font-extrabold text-[#111827] tabular-nums">{{ number_format($tool->stock_total, 0, ',', '.') }}</span>
                                            <span class="text-right text-[13px] font-extrabold text-[#16a34a] tabular-nums">{{ number_format($tool->stock_available, 0, ',', '.') }}</span>
                                            <span class="text-right text-[13px] font-extrabold text-[#111827] tabular-nums">{{ number_format($tool->stock_borrowed, 0, ',', '.') }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="py-14 px-6 text-center">
                        <x-lucide-wrench class="w-9 h-9 mx-auto text-gray-300" />
                        <h4 class="text-[13px] font-bold text-[#111111] mt-3">Belum ada unit alat</h4>
                        <p class="text-[12px] text-gray-500 mt-1.5 max-w-sm mx-auto">Data alat kerja dan armada operasional akan tampil di sini setelah ditambahkan.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ==================== TREE ALAT (mobile) ==================== --}}
        <div x-show="activeTab === 'alat'" x-cloak class="md:hidden mt-4 bg-white border border-[#e9e9e7] rounded-2xl overflow-hidden">
            @forelse($toolsCategoriesData as $toolCategory)
                @php
                    $toolCatKey = 'cat-tool-' . $toolCategory->id;
                    $catToolCount = $toolCategory->tools->count();
                    $toolsByType = $toolCategory->tools->groupBy(fn($t) => $groupKey($t->type))->sortBy(fn($i, $k) => $sortGroup($k));
                @endphp

                <div class="border-b border-[#e5e9f0] last:border-b-0">
                    <button type="button" @click="toggleGroup('{{ $toolCatKey }}')" :aria-expanded="openGroups.includes('{{ $toolCatKey }}')"
                        class="w-full flex items-center gap-2 px-4 py-3 bg-[#e9edf5] text-left cursor-pointer">
                        <svg :class="openGroups.includes('{{ $toolCatKey }}') ? 'rotate-90 text-[#334155]' : 'text-[#94a3b8]'" class="w-4 h-4 transition-transform duration-200 flex-shrink-0" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span class="text-[12px] font-extrabold tracking-wide text-[#111827] uppercase">{{ strtoupper(trim(preg_replace('/^DIVISI\s+/i', '', $toolCategory->name))) }}</span>
                        <span class="text-[11px] font-medium text-[#64748b]">({{ $catToolCount }})</span>
                    </button>

                    <div x-show="openGroups.includes('{{ $toolCatKey }}')" x-cloak>
                        @foreach($toolsByType as $subLabel => $subItems)
                            @php $subKey = $toolCatKey . '-' . md5($subLabel); @endphp
                            <button type="button" data-flip @click="toggleSub('{{ $toolCatKey }}', '{{ $subKey }}')" :aria-expanded="openSubs.includes('{{ $subKey }}')"
                                class="w-full flex items-center gap-2 pl-10 pr-4 py-2.5 bg-[#eef1f7] border-t border-[#e3e8ef] text-left cursor-pointer">
                                <svg :class="openSubs.includes('{{ $subKey }}') ? 'rotate-90 text-[#334155]' : 'text-[#94a3b8]'" class="w-3.5 h-3.5 transition-transform duration-200 flex-shrink-0" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                </svg>
                                <span class="text-[11.5px] font-bold text-[#1f2937]">{{ $subLabel }}</span>
                                <span class="text-[10.5px] text-[#64748b]">({{ $subItems->count() }})</span>
                            </button>

                            <div x-show="openSubs.includes('{{ $subKey }}')" x-cloak>
                                @foreach($subItems as $tool)
                                    <div data-flip data-animate="rows" x-show="matchesSearch(@js($tool->name), @js($tool->code ?? ''), @js($tool->brand ?? ''), @js($toolCategory->name))"
                                        class="bg-white border-t border-[#edf0f5] px-4 py-3">
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="font-mono text-[10.5px] text-[#64748b] truncate">{{ $tool->code }}</span>
                                            <span class="text-[13px] font-extrabold text-[#111827] tabular-nums flex-shrink-0">{{ number_format($tool->stock_total, 0, ',', '.') }} <span class="text-[10px] font-medium text-gray-400">unit</span></span>
                                        </div>
                                        <div class="text-[12.5px] font-semibold text-[#111827] leading-snug mt-1.5">{{ $tool->name }}</div>
                                        <div class="flex items-center justify-between gap-3 mt-1">
                                            <span class="text-[10px] text-[#64748b] truncate">{{ $tool->brand ? 'Merek: ' . $tool->brand : '' }}</span>
                                            <span class="text-[10px] text-[#475569] truncate max-w-[55%] text-right">{{ $tool->display_supplier_name }}</span>
                                        </div>
                                        <div class="text-[10px] text-gray-500 mt-0.5 tabular-nums">{{ number_format($tool->stock_available, 0, ',', '.') }} siap &middot; {{ number_format($tool->stock_borrowed, 0, ',', '.') }} diproyek</div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="py-14 px-6 text-center">
                    <x-lucide-wrench class="w-9 h-9 mx-auto text-gray-300" />
                    <h4 class="text-[13px] font-bold text-[#111111] mt-3">Belum ada unit alat</h4>
                    <p class="text-[12px] text-gray-500 mt-1.5">Data alat kerja akan tampil di sini setelah ditambahkan.</p>
                </div>
            @endforelse
        </div>

    </div>
</x-app-layout>
