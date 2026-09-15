<x-app-layout>
    <x-slot name="title">Inventori Gudang</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="fw-700" style="font-size:20px;color:#0f172a;">Inventori Gudang</h2>
            <p class="text-muted" style="font-size:13px;margin-top:2px;">Monitoring stok material & alat kerja secara real-time di seluruh gudang</p>
        </div>
    </div>

    {{-- Filter 4 Tingkat --}}
    <div class="card mb-4" style="box-shadow:0 1px 3px rgba(0,0,0,0.05);">
        <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:12px 20px;">
            <div class="flex items-center gap-2">
                <i class="fas fa-filter text-primary" style="font-size:13px;"></i>
                <span class="fw-700" style="font-size:13.5px;color:#0f172a;">Filter Inventori Bertingkat (4 Dropdown)</span>
            </div>
        </div>
        <div class="card-body" style="padding:18px 20px;">
            <form method="GET" action="{{ route('inventory.index') }}" id="inventoryFilterForm">
                {{-- Row 1: 4 Dropdown Bertingkat --}}
                <div class="grid grid-4 mb-3" style="gap:14px;">
                    {{-- Dropdown 1: Jenis Barang (Awal / Utama) --}}
                    <div>
                        <label class="form-label" style="font-weight:600;color:#334155;font-size:12px;">
                            <span style="display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:50%;background:#2563eb;color:#fff;font-size:9.5px;font-weight:800;margin-right:4px;">1</span>
                            Jenis Item
                        </label>
                        <select name="item_type" id="filter_item_type" class="form-control" onchange="onItemTypeChange()">
                            <option value="">-- Semua (Material & Alat) --</option>
                            <option value="material" {{ request('item_type') === 'material' ? 'selected' : '' }}>📦 Material Konstruksi</option>
                            <option value="tool" {{ request('item_type') === 'tool' ? 'selected' : '' }}>🛠️ Alat Kerja</option>
                        </select>
                    </div>

                    {{-- Dropdown 2: Kategori --}}
                    <div>
                        <label class="form-label" style="font-weight:600;color:#334155;font-size:12px;">
                            <span style="display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:50%;background:#0284c7;color:#fff;font-size:9.5px;font-weight:800;margin-right:4px;">2</span>
                            Kategori
                        </label>
                        <select name="category_id" id="filter_category" class="form-control" onchange="onCategoryChange()">
                            <option value="">-- Semua Kategori --</option>
                        </select>
                    </div>

                    {{-- Dropdown 3: Kelompok Barang / Alat --}}
                    <div>
                        <label class="form-label" style="font-weight:600;color:#334155;font-size:12px;">
                            <span style="display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:50%;background:#16a34a;color:#fff;font-size:9.5px;font-weight:800;margin-right:4px;">3</span>
                            Kelompok Barang / Alat
                        </label>
                        <select name="type" id="filter_type" class="form-control" onchange="onTypeChange()">
                            <option value="">-- Semua Kelompok --</option>
                        </select>
                    </div>

                    {{-- Dropdown 4: Ukuran / Spesifikasi --}}
                    <div>
                        <label class="form-label" style="font-weight:600;color:#334155;font-size:12px;">
                            <span style="display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:50%;background:#ea580c;color:#fff;font-size:9.5px;font-weight:800;margin-right:4px;">4</span>
                            Ukuran / Spesifikasi
                        </label>
                        <select name="size" id="filter_size" class="form-control">
                            <option value="">-- Semua Ukuran / Spesifikasi --</option>
                        </select>
                    </div>
                </div>

                {{-- Row 2: Search, Level Stok, & Actions --}}
                <div class="flex gap-3" style="flex-wrap:wrap;align-items:flex-end;border-top:1px dashed #e2e8f0;padding-top:14px;">
                    <div style="flex:1;min-width:220px;">
                        <label class="form-label" style="font-size:12px;color:#64748b;">Pencarian Nama / Kode SKU / Merk</label>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Ketik kata kunci pencarian...">
                    </div>
                    <div style="min-width:160px;">
                        <label class="form-label" style="font-size:12px;color:#64748b;">Level Stok</label>
                        <select name="stock_level" class="form-control">
                            <option value="">Semua Level</option>
                            <option value="low" {{ request('stock_level') === 'low' ? 'selected' : '' }}>⚠️ Stok Rendah / Kritis</option>
                            <option value="out" {{ request('stock_level') === 'out' ? 'selected' : '' }}>⛔ Stok Habis</option>
                        </select>
                    </div>
                    <div class="flex gap-2" style="align-items:center;">
                        <button type="submit" class="btn btn-primary" style="padding:7px 16px;">
                            <i class="fas fa-search me-1"></i> Terapkan Filter
                        </button>
                        <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary" style="padding:7px 14px;" title="Reset Filter">
                            <i class="fas fa-rotate-left me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABEL INVENTORI: MATERIAL --}}
    @if(!$itemType || $itemType === 'material')
    <div class="card mb-4">
        <div class="card-header flex items-center justify-between" style="background:#f8fafc;padding:12px 20px;">
            <div class="flex items-center gap-2">
                <span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-size:11px;font-weight:700;">
                    <i class="fas fa-boxes-stacked me-1"></i> MATERIAL
                </span>
                <span class="fw-700" style="font-size:14px;color:#0f172a;">Inventori Material Gudang</span>
            </div>
            <span class="text-muted" style="font-size:12px;">{{ $categoriesData->sum(fn($c) => $c->materials->count()) }} varian terdaftar</span>
        </div>
        <div class="table-wrap">
            <table class="data-table mb-0">
                <thead>
                    <tr>
                        <th style="width:40px;text-align:center;">#</th>
                        <th>Material</th>
                        <th>Kelompok & Spesifikasi</th>
                        <th>Kategori</th>
                        <th>Gudang</th>
                        <th style="text-align:center;">Stok Saat Ini</th>
                        <th style="text-align:center;">Min. Stok</th>
                        <th>Status</th>
                        <th>Update Terakhir</th>
                        <th style="text-align:center;width:80px;">Aksi</th>
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
                            return $m->category?->name ?? 'Lainnya';
                        });
                        $totalMaterials = $materialsWithInv->count();
                        $totalGroups = $typeGroups->count();
                    @endphp
                    @if($materialsWithInv->isNotEmpty())
                    {{-- Level 1: Category Header --}}
                    <tr class="group-toggle" data-group="group-mat-cat-{{ $category->id }}" style="background:#f1f5f9 !important;cursor:pointer;">
                        <td colspan="10" style="padding:10px 20px !important;">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center" style="gap:8px;">
                                    <i class="fas fa-chevron-down group-chev" style="font-size:11px;color:#64748b;transition:transform .2s;"></i>
                                    <span class="fw-700" style="font-size:13px;text-transform:uppercase;color:#0f172a;">{{ $category->name }}</span>
                                    <span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-size:10.5px;">{{ $totalMaterials }} Material</span>
                                    <span class="badge" style="background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;font-size:10px;">{{ $totalGroups }} Kelompok</span>
                                </div>
                                <span class="text-muted" style="font-size:11px;opacity:0.8;">
                                    <i class="fas fa-chevron-down me-1" style="font-size:9px;"></i> Klik untuk buka / tutup kategori
                                </span>
                            </div>
                        </td>
                    </tr>

                    {{-- Level 2: Sub-Group Header (Kelompok Barang / Type) --}}
                    @foreach($typeGroups as $typeName => $materialsInType)
                    @php $subKey = 'sub-mat-' . $category->id . '-' . Str::slug($typeName); @endphp
                    <tr class="group-rows group-mat-cat-{{ $category->id }} subgroup-toggle" data-group="{{ $subKey }}" style="background:#f8fafc !important;cursor:pointer;border-left:3px solid #3b82f6;">
                        <td colspan="10" style="padding:8px 20px 8px 40px !important;">
                            <div class="flex items-center justify-between" style="gap:8px;">
                                <div class="flex items-center" style="gap:7px;">
                                    <i class="fas fa-chevron-down subgroup-chev" style="font-size:10px;color:#94a3b8;transition:transform .2s;"></i>
                                    <i class="fas fa-cubes" style="font-size:11px;color:#3b82f6;"></i>
                                    <span class="fw-600" style="font-size:12.5px;color:#334155;">
                                        {{ $typeName }}
                                    </span>
                                    <span class="badge" style="background:#e0e7ff;color:#4338ca;border:1px solid #c7d2fe;font-size:10px;">
                                        {{ $materialsInType->count() }} varian
                                    </span>
                                </div>
                                <span class="text-muted" style="font-size:10.5px;opacity:0.7;">
                                    <i class="fas fa-chevron-down me-1" style="font-size:9px;"></i> Klik untuk buka / tutup
                                </span>
                            </div>
                        </td>
                    </tr>

                    {{-- Level 3: Individual Inventory Material Rows --}}
                    @foreach($materialsInType as $material)
                        @foreach($material->inventories as $inv)
                        <tr class="group-rows group-mat-cat-{{ $category->id }} subgroup-rows {{ $subKey }}">
                            <td class="text-muted" style="text-align:center;">{{ $loop->iteration }}</td>
                            <td>
                                <div class="fw-600" style="color:#0f172a;">{{ $material->name }}</div>
                                <code style="font-size:11px;color:#64748b;background:#f1f5f9;padding:1px 5px;border-radius:3px;">{{ $material->sku ?? '-' }}</code>
                            </td>
                            <td>
                                @if($material->type)
                                <div class="fw-500" style="font-size:12.5px;color:#334155;"><i class="fas fa-tag me-1 text-muted"></i>{{ $material->type }}</div>
                                @endif
                                @if($material->size)
                                <span class="badge bg-light text-dark border" style="font-size:10.5px;"><i class="fas fa-ruler-combined text-muted me-1"></i>{{ $material->size }}</span>
                                @elseif(!$material->type)
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td><span class="text-muted" style="font-size:12.5px;">{{ $category->name }}</span></td>
                            <td><span class="fw-500" style="color:#1e293b;"><i class="fas fa-warehouse text-muted me-1"></i>{{ $inv->warehouse?->name ?? '-' }}</span></td>
                            <td style="text-align:center;">
                                <span class="fw-700" style="font-size:15px;color:#0f172a;">{{ number_format($inv->quantity, 0, ',', '.') }}</span>
                                <span class="text-muted" style="font-size:11px;"> {{ $material->unit?->abbreviation ?? $material->unit?->name }}</span>
                            </td>
                            <td style="text-align:center;">{{ number_format($inv->min_stock, 0, ',', '.') }}</td>
                            <td>
                                @if($inv->quantity <= 0)
                                    <span class="badge bg-danger-subtle text-danger" style="font-size:11px;"><i class="fas fa-exclamation-circle me-1"></i> Habis</span>
                                @elseif($inv->quantity <= $inv->min_stock)
                                    <span class="badge bg-warning-subtle text-warning" style="font-size:11px;"><i class="fas fa-triangle-exclamation me-1"></i> Rendah</span>
                                @else
                                    <span class="badge bg-success-subtle text-success" style="font-size:11px;"><i class="fas fa-check-circle me-1"></i> Normal</span>
                                @endif
                            </td>
                            <td class="text-muted" style="font-size:12px;">{{ $inv->updated_at ? $inv->updated_at->diffForHumans() : '-' }}</td>
                            <td style="text-align:center;">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('inventory.show', $inv) }}" class="btn btn-sm btn-info btn-icon" title="Detail Riwayat Stok">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @endforeach
                    @endforeach
                    @endif
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted p-4">
                            <i class="fas fa-box-open mb-2" style="font-size:20px;color:#94a3b8;"></i>
                            <div>Tidak ada data inventori material yang cocok dengan kriteria filter.</div>
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
    <div class="card mb-4">
        <div class="card-header flex items-center justify-between" style="background:#f8fafc;padding:12px 20px;">
            <div class="flex items-center gap-2">
                <span class="badge" style="background:#fff7ed;color:#c2410c;border:1px solid #fed7aa;font-size:11px;font-weight:700;">
                    <i class="fas fa-tools me-1"></i> ALAT KERJA
                </span>
                <span class="fw-700" style="font-size:14px;color:#0f172a;">Inventori Alat Kerja Gudang</span>
            </div>
            <span class="text-muted" style="font-size:12px;">{{ $toolsCategoriesData->sum(fn($c) => $c->tools->count()) }} alat terdaftar</span>
        </div>
        <div class="table-wrap">
            <table class="data-table mb-0">
                <thead>
                    <tr>
                        <th style="width:40px;text-align:center;">#</th>
                        <th>Nama Alat & Kode</th>
                        <th>Merk & Spesifikasi</th>
                        <th>Kategori</th>
                        <th>Gudang / Lokasi</th>
                        <th style="text-align:center;">Total Stok</th>
                        <th style="text-align:center;">Tersedia</th>
                        <th style="text-align:center;">Dipinjam</th>
                        <th>Status</th>
                        <th style="text-align:center;width:80px;">Aksi</th>
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
                            return $t->category?->name ?? 'Lainnya';
                        });
                        $totalTools = $toolCategory->tools->count();
                        $totalToolGroups = $toolTypeGroups->count();
                    @endphp
                    {{-- Level 1: Tool Category Header --}}
                    <tr class="group-toggle" data-group="group-tool-cat-{{ $toolCategory->id }}" style="background:#f1f5f9 !important;cursor:pointer;">
                        <td colspan="10" style="padding:10px 20px !important;">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center" style="gap:8px;">
                                    <i class="fas fa-chevron-down group-chev" style="font-size:11px;color:#64748b;transition:transform .2s;"></i>
                                    <span class="fw-700" style="font-size:13px;text-transform:uppercase;color:#0f172a;">{{ $toolCategory->name }}</span>
                                    <span class="badge" style="background:#fff7ed;color:#c2410c;border:1px solid #fed7aa;font-size:10.5px;">{{ $totalTools }} Alat</span>
                                    <span class="badge" style="background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;font-size:10px;">{{ $totalToolGroups }} Kelompok</span>
                                </div>
                                <span class="text-muted" style="font-size:11px;opacity:0.8;">
                                    <i class="fas fa-chevron-down me-1" style="font-size:9px;"></i> Klik untuk buka / tutup kategori
                                </span>
                            </div>
                        </td>
                    </tr>

                    {{-- Level 2: Sub-Group Header (Kelompok Alat / Type) --}}
                    @foreach($toolTypeGroups as $toolTypeName => $toolsInType)
                    @php $subToolKey = 'sub-tool-' . $toolCategory->id . '-' . Str::slug($toolTypeName); @endphp
                    <tr class="group-rows group-tool-cat-{{ $toolCategory->id }} subgroup-toggle" data-group="{{ $subToolKey }}" style="background:#f8fafc !important;cursor:pointer;border-left:3px solid #f97316;">
                        <td colspan="10" style="padding:8px 20px 8px 40px !important;">
                            <div class="flex items-center justify-between" style="gap:8px;">
                                <div class="flex items-center" style="gap:7px;">
                                    <i class="fas fa-chevron-down subgroup-chev" style="font-size:10px;color:#94a3b8;transition:transform .2s;"></i>
                                    <i class="fas fa-screwdriver-wrench" style="font-size:11px;color:#ea580c;"></i>
                                    <span class="fw-600" style="font-size:12.5px;color:#334155;">
                                        {{ $toolTypeName }}
                                    </span>
                                    <span class="badge" style="background:#ffedd5;color:#c2410c;border:1px solid #fed7aa;font-size:10px;">
                                        {{ $toolsInType->count() }} varian
                                    </span>
                                </div>
                                <span class="text-muted" style="font-size:10.5px;opacity:0.7;">
                                    <i class="fas fa-chevron-down me-1" style="font-size:9px;"></i> Klik untuk buka / tutup
                                </span>
                            </div>
                        </td>
                    </tr>

                    {{-- Level 3: Individual Tool Rows --}}
                    @foreach($toolsInType as $tool)
                    <tr class="group-rows group-tool-cat-{{ $toolCategory->id }} subgroup-rows {{ $subToolKey }}">
                        <td class="text-muted" style="text-align:center;">{{ $loop->iteration }}</td>
                        <td>
                            <div class="fw-600" style="color:#0f172a;">{{ $tool->name }}</div>
                            <code style="font-size:11px;color:#64748b;background:#f1f5f9;padding:1px 5px;border-radius:3px;">{{ $tool->code ?? '-' }}</code>
                        </td>
                        <td>
                            @if($tool->brand)
                            <div class="fw-600" style="font-size:12.5px;color:#334155;">{{ $tool->brand }}</div>
                            @endif
                            @if($tool->size)
                            <span class="badge bg-light text-dark border" style="font-size:10.5px;"><i class="fas fa-ruler-combined text-muted me-1"></i>{{ $tool->size }}</span>
                            @elseif(!$tool->brand)
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td><span class="text-muted" style="font-size:12.5px;">{{ $toolCategory->name }}</span></td>
                        <td><span class="fw-500" style="color:#1e293b;"><i class="fas fa-warehouse text-muted me-1"></i>{{ $tool->currentWarehouse?->name ?? 'Gudang Pusat' }}</span></td>
                        <td style="text-align:center;">
                            <span class="fw-700" style="font-size:14px;color:#0f172a;">{{ number_format($tool->stock_total, 0, ',', '.') }}</span>
                            <span class="text-muted" style="font-size:11px;"> unit</span>
                        </td>
                        <td style="text-align:center;">
                            <span class="fw-700" style="font-size:14px;color:#16a34a;">{{ number_format($tool->stock_available, 0, ',', '.') }}</span>
                            <span class="text-muted" style="font-size:11px;"> unit</span>
                        </td>
                        <td style="text-align:center;">
                            @if($tool->stock_borrowed > 0)
                                <span class="fw-700" style="font-size:14px;color:#e11d48;">{{ number_format($tool->stock_borrowed, 0, ',', '.') }}</span>
                            @else
                                <span class="text-muted" style="font-size:12px;">0</span>
                            @endif
                        </td>
                        <td>
                            @if($tool->stock_available > 0)
                                <span class="badge bg-success-subtle text-success" style="font-size:11px;"><i class="fas fa-check-circle me-1"></i> Tersedia</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger" style="font-size:11px;"><i class="fas fa-circle-xmark me-1"></i> Habis / Dipinjam</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('tools.show', $tool) }}" class="btn btn-sm btn-info btn-icon" title="Detail Alat">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    @endforeach
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted p-4">
                            <i class="fas fa-wrench mb-2" style="font-size:20px;color:#94a3b8;"></i>
                            <div>Tidak ada data inventori alat kerja yang cocok dengan kriteria filter.</div>
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
        // Data struktur cascading 4 tingkat dari server
        var cascadingData = @json($cascadingData);

        // Nilai terpilih saat ini dari query string
        var currentItemType = @json(request('item_type') ?? '');
        var currentCategoryId = @json(request('category_id') ?? '');
        var currentType = @json(request('type') ?? '');
        var currentSize = @json(request('size') ?? '');

        function onItemTypeChange(preserveValues) {
            var itemType = document.getElementById('filter_item_type').value;
            var catSelect = document.getElementById('filter_category');
            
            catSelect.innerHTML = '<option value="">-- Semua Kategori --</option>';

            var cats = [];
            if (itemType === 'material') {
                cats = cascadingData.material || [];
            } else if (itemType === 'tool') {
                cats = cascadingData.tool || [];
            } else {
                // Semua: gabungkan kategori material dan tool
                var matCats = (cascadingData.material || []).map(function(c) {
                    return { id: c.id, name: '[Material] ' + c.name, types: c.types };
                });
                var toolCats = (cascadingData.tool || []).map(function(c) {
                    return { id: c.id, name: '[Alat] ' + c.name, types: c.types };
                });
                cats = matCats.concat(toolCats);
            }

            cats.forEach(function(cat) {
                var opt = document.createElement('option');
                opt.value = cat.id;
                opt.textContent = cat.name;
                if (preserveValues && String(currentCategoryId) === String(cat.id)) {
                    opt.selected = true;
                }
                catSelect.appendChild(opt);
            });

            onCategoryChange(preserveValues);
        }

        function onCategoryChange(preserveValues) {
            var itemType = document.getElementById('filter_item_type').value;
            var catId = document.getElementById('filter_category').value;
            var typeSelect = document.getElementById('filter_type');

            typeSelect.innerHTML = '<option value="">-- Semua Kelompok --</option>';

            var foundCat = null;
            var allCats = [];
            if (itemType === 'material') {
                allCats = cascadingData.material || [];
            } else if (itemType === 'tool') {
                allCats = cascadingData.tool || [];
            } else {
                allCats = (cascadingData.material || []).concat(cascadingData.tool || []);
            }

            if (catId) {
                foundCat = allCats.find(function(c) { return String(c.id) === String(catId); });
            }

            if (foundCat && foundCat.types) {
                Object.keys(foundCat.types).forEach(function(typeName) {
                    var opt = document.createElement('option');
                    opt.value = typeName;
                    opt.textContent = typeName;
                    if (preserveValues && currentType === typeName) {
                        opt.selected = true;
                    }
                    typeSelect.appendChild(opt);
                });
            } else if (!catId) {
                // Kumpulkan seluruh type dari kategori yang tersedia
                var uniqueTypes = {};
                allCats.forEach(function(c) {
                    if (c.types) {
                        Object.keys(c.types).forEach(function(t) { uniqueTypes[t] = true; });
                    }
                });
                Object.keys(uniqueTypes).forEach(function(typeName) {
                    var opt = document.createElement('option');
                    opt.value = typeName;
                    opt.textContent = typeName;
                    if (preserveValues && currentType === typeName) {
                        opt.selected = true;
                    }
                    typeSelect.appendChild(opt);
                });
            }

            onTypeChange(preserveValues);
        }

        function onTypeChange(preserveValues) {
            var itemType = document.getElementById('filter_item_type').value;
            var catId = document.getElementById('filter_category').value;
            var typeVal = document.getElementById('filter_type').value;
            var sizeSelect = document.getElementById('filter_size');

            sizeSelect.innerHTML = '<option value="">-- Semua Ukuran / Spesifikasi --</option>';

            var allCats = [];
            if (itemType === 'material') {
                allCats = cascadingData.material || [];
            } else if (itemType === 'tool') {
                allCats = cascadingData.tool || [];
            } else {
                allCats = (cascadingData.material || []).concat(cascadingData.tool || []);
            }

            var uniqueSizes = {};
            allCats.forEach(function(c) {
                if (catId && String(c.id) !== String(catId)) return;
                if (c.types) {
                    if (typeVal && c.types[typeVal]) {
                        c.types[typeVal].forEach(function(s) { uniqueSizes[s] = true; });
                    } else if (!typeVal) {
                        Object.values(c.types).forEach(function(sizesList) {
                            sizesList.forEach(function(s) { uniqueSizes[s] = true; });
                        });
                    }
                }
            });

            Object.keys(uniqueSizes).forEach(function(sz) {
                var opt = document.createElement('option');
                opt.value = sz;
                opt.textContent = sz;
                if (preserveValues && currentSize === sz) {
                    opt.selected = true;
                }
                sizeSelect.appendChild(opt);
            });
        }

        // Initialize cascading on page load with current request values
        document.addEventListener('DOMContentLoaded', function () {
            onItemTypeChange(true);

            // Accordion toggle functionality - Level 1: Kategori
            var toggleRows = document.querySelectorAll('.group-toggle');
            toggleRows.forEach(function (row) {
                row.addEventListener('click', function (e) {
                    if (e.target.closest('.btn') || e.target.closest('a')) return;
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
                    var chev = row.querySelector('.group-chev');
                    if (chev) {
                        chev.style.transform = isCatCollapsed ? 'rotate(-90deg)' : '';
                    }
                });
            });

            // Accordion toggle functionality - Level 2: Subgroup (Kelompok Barang / Alat)
            var subToggleRows = document.querySelectorAll('.subgroup-toggle');
            subToggleRows.forEach(function (subRow) {
                subRow.addEventListener('click', function (e) {
                    if (e.target.closest('.btn') || e.target.closest('a')) return;
                    var subKey = subRow.getAttribute('data-group');
                    var isSubCollapsed = subRow.classList.toggle('collapsed');
                    var childRows = document.querySelectorAll('.' + subKey);
                    childRows.forEach(function (r) {
                        r.style.display = isSubCollapsed ? 'none' : '';
                    });
                    var chev = subRow.querySelector('.subgroup-chev');
                    if (chev) {
                        chev.style.transform = isSubCollapsed ? 'rotate(-90deg)' : '';
                    }
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
