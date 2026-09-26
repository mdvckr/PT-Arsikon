<x-app-layout>
    <x-slot name="title">Inventori Gudang</x-slot>

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-4" style="flex-wrap:wrap;gap:12px;">
        <div>
            <h2 class="fw-700" style="font-size:20px;color:#0f172a;margin:0;">Inventori Gudang</h2>
            <p class="text-muted" style="font-size:12.5px;margin:2px 0 0;">Monitoring stok material dan alat kerja secara real-time di seluruh gudang</p>
        </div>
    </div>

    {{-- Filter Bar: Jenis Item dipindahkan di samping Pencarian --}}
    <div class="card mb-4" style="border:1px solid #e2e8f0;box-shadow:none;border-radius:8px;">
        <div class="card-body" style="padding:14px 18px;">
            <form method="GET" action="{{ route('inventory.index') }}" id="inventoryFilterForm" class="flex gap-3" style="flex-wrap:wrap;align-items:flex-end;">
                {{-- Kolom Pencarian --}}
                <div style="flex:1;min-width:240px;">
                    <label class="form-label" style="font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;">
                        Pencarian
                    </label>
                    <div style="position:relative;">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                            placeholder="Cari nama item, kode SKU, atau spesifikasi..."
                            style="height:38px;border-radius:6px;font-size:13px;padding-left:34px;border:1px solid #cbd5e1;">
                        <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:12px;color:#94a3b8;"></i>
                    </div>
                </div>

                {{-- Kolom Lokasi Gudang (Role Admin / Owner dapat melihat semua atau memfilter per gudang) --}}
                <div style="width:230px;min-width:180px;">
                    <label class="form-label" style="font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;">
                        Lokasi Gudang
                    </label>
                    <select name="warehouse_id" id="filter_warehouse_id" class="form-control" onchange="this.form.submit()" style="height:38px;border-radius:6px;font-size:13px;border:1px solid #cbd5e1;">
                        @if(!empty($canViewAllWarehouses))
                            <option value="all" {{ ($selectedWarehouseId === 'all' || empty($selectedWarehouseId)) ? 'selected' : '' }}>
                                Semua Gudang (Konsolidasi)
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
                <div style="width:200px;min-width:160px;">
                    <label class="form-label" style="font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;">
                        Jenis Item
                    </label>
                    <select name="item_type" id="filter_item_type" class="form-control" onchange="this.form.submit()" style="height:38px;border-radius:6px;font-size:13px;border:1px solid #cbd5e1;">
                        <option value="">Semua (Material & Alat)</option>
                        <option value="material" {{ request('item_type') === 'material' ? 'selected' : '' }}>Material Konstruksi</option>
                        <option value="tool" {{ request('item_type') === 'tool' ? 'selected' : '' }}>Alat Kerja</option>
                    </select>
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-secondary" style="height:38px;padding:0 16px;border-radius:6px;font-size:13px;font-weight:600;">
                        Cari
                    </button>
                    @if(request('search') || request('item_type') || (request('warehouse_id') && request('warehouse_id') !== 'all'))
                    <a href="{{ route('inventory.index') }}" class="btn btn-light border" style="height:38px;padding:0 12px;border-radius:6px;font-size:13px;color:#64748b;" title="Reset Filter">
                        Reset
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- TABEL INVENTORI: MATERIAL --}}
    @if(!$itemType || $itemType === 'material')
    <div class="card mb-4" style="border:1px solid #e2e8f0;box-shadow:none;border-radius:8px;overflow:hidden;">
        <div class="card-header flex items-center justify-between" style="background:#f8fafc;padding:12px 18px;border-bottom:1px solid #e2e8f0;">
            <div>
                <span class="fw-700" style="font-size:14px;color:#0f172a;">Inventori Material</span>
            </div>
            <span class="text-muted" style="font-size:12px;">
                {{ $categoriesData->sum(fn($c) => $c->materials->count()) }} varian terdaftar
            </span>
        </div>
        <div class="table-wrap">
            <table class="data-table mb-0" style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                        <th style="padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;">Material</th>
                        <th style="padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;">Kelompok & Spesifikasi</th>
                        <th style="padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;">Kategori</th>
                        <th style="padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;">Gudang</th>
                        <th style="text-align:center;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;">Stok Saat Ini</th>
                        <th style="text-align:center;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;">Min. Stok</th>
                        <th style="padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;text-align:center;">Status</th>
                        <th style="padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;">Update</th>
                        <th style="text-align:center;width:65px;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;">Aksi</th>
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
                    <tr class="group-toggle" data-group="group-mat-cat-{{ $category->id }}" style="background:#f8fafc !important;cursor:pointer;user-select:none;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;">
                        <td colspan="9" style="padding:8px 14px !important;">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center" style="gap:8px;">
                                    <i class="fas fa-chevron-down group-chev" style="font-size:9.5px;color:#64748b;transition:transform .2s;"></i>
                                    <span class="fw-700" style="font-size:12.5px;letter-spacing:.04em;text-transform:uppercase;color:#1e293b;">{{ $category->name }}</span>
                                    <span style="font-size:11.5px;color:#64748b;font-weight:500;">
                                        ({{ $totalMaterials }} material)
                                    </span>
                                </div>
                            </div>
                        </td>
                    </tr>

                    {{-- Level 2: Sub-Group Header (Kelompok Barang / Type) --}}
                    @foreach($typeGroups as $typeName => $materialsInType)
                    @php $subKey = 'sub-mat-' . $category->id . '-' . Str::slug($typeName); @endphp
                    <tr class="group-rows group-mat-cat-{{ $category->id }} subgroup-toggle" data-group="{{ $subKey }}" style="background:#fafbfc !important;cursor:pointer;user-select:none;border-bottom:1px solid #f1f5f9;border-left:3px solid #cbd5e1;">
                        <td colspan="9" style="padding:6px 14px 6px 28px !important;">
                            <div class="flex items-center justify-between" style="gap:8px;">
                                <div class="flex items-center" style="gap:7px;">
                                    <i class="fas fa-chevron-down subgroup-chev" style="font-size:8.5px;color:#94a3b8;transition:transform .2s;"></i>
                                    <span class="fw-600" style="font-size:12px;color:#334155;">
                                        {{ $typeName }}
                                    </span>
                                    <span style="font-size:11px;color:#94a3b8;">
                                        ({{ $materialsInType->count() }})
                                    </span>
                                </div>
                            </div>
                        </td>
                    </tr>

                    {{-- Level 3: Individual Inventory Material Rows --}}
                    @foreach($materialsInType as $material)
                        @foreach($material->inventories as $inv)
                        <tr class="group-rows group-mat-cat-{{ $category->id }} subgroup-rows {{ $subKey }}" style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:9px 14px;vertical-align:middle;">
                                <div class="fw-600" style="color:#0f172a;font-size:12.5px;line-height:1.35;">{{ $material->name }}</div>
                                <div style="margin-top:2px;display:flex;align-items:center;gap:6px;">
                                    <span style="font-family:ui-monospace,SFMono-Regular,Consolas,monospace;font-size:11.5px;color:#64748b;font-weight:600;letter-spacing:0.02em;">
                                        {{ $material->sku ?? '-' }}
                                    </span>
                                    @if($material->brand)
                                    <span class="text-muted" style="font-size:11px;">
                                        Merek: {{ $material->brand }}
                                    </span>
                                    @endif
                                </div>
                            </td>
                            <td style="padding:8px 12px;font-size:12px;color:#475569;vertical-align:middle;">
                                {{ $material->type ?: '-' }}
                                @if($material->size)
                                <span class="text-muted" style="font-size:11px;">· {{ $material->size }}</span>
                                @endif
                            </td>
                            <td style="padding:8px 12px;font-size:12px;color:#64748b;vertical-align:middle;">
                                {{ $category->name }}
                            </td>
                            <td style="padding:8px 12px;font-size:12px;color:#334155;font-weight:500;vertical-align:middle;">
                                <span style="display:inline-flex;align-items:center;gap:5.5px;padding:2px 8px;border-radius:5px;background:#f8fafc;border:1px solid #e2e8f0;font-size:11.5px;color:#1e293b;font-weight:600;">
                                    <i class="fas fa-warehouse" style="font-size:10px;color:#64748b;"></i>
                                    {{ $inv->warehouse?->name ?? '-' }}
                                </span>
                            </td>
                            <td style="text-align:center;padding:8px 12px;vertical-align:middle;">
                                <span class="fw-700" style="font-size:13px;color:#0f172a;font-variant-numeric:tabular-nums;">{{ number_format($inv->quantity, 0, ',', '.') }}</span>
                                <span class="text-muted" style="font-size:11px;"> {{ $material->unit?->abbreviation ?? $material->unit?->name }}</span>
                            </td>
                            <td style="text-align:center;padding:8px 12px;font-size:12px;color:#64748b;vertical-align:middle;font-variant-numeric:tabular-nums;">
                                {{ number_format($inv->min_stock, 0, ',', '.') }}
                            </td>
                            <td style="padding:8px 12px;vertical-align:middle;text-align:center;">
                                @if($inv->quantity <= 0)
                                    <span class="badge" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;font-size:10.5px;padding:2px 7px;border-radius:4px;font-weight:600;">Habis</span>
                                @elseif($inv->quantity <= $inv->min_stock)
                                    <span class="badge" style="background:#fffbeb;color:#92400e;border:1px solid #fde68a;font-size:10.5px;padding:2px 7px;border-radius:4px;font-weight:600;">Rendah</span>
                                @else
                                    <span class="badge" style="background:#f8fafc;color:#475569;border:1px solid #e2e8f0;font-size:10.5px;padding:2px 7px;border-radius:4px;font-weight:600;">Normal</span>
                                @endif
                            </td>
                            <td class="text-muted" style="padding:8px 12px;font-size:11.5px;white-space:nowrap;vertical-align:middle;">
                                {{ $inv->updated_at ? $inv->updated_at->format('d/m/Y') : '-' }}
                            </td>
                            <td style="text-align:center;padding:8px 12px;vertical-align:middle;">
                                <a href="{{ route('inventory.show', $inv) }}" class="btn btn-sm btn-light border" style="width:28px;height:28px;padding:0;display:inline-flex;align-items:center;justify-content:center;color:#475569;border-radius:5px;background:#f8fafc;" title="Detail Riwayat Stok">
                                    <i class="fas fa-eye" style="font-size:11px;"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    @endforeach
                    @endforeach
                    @endif
                    @empty
                    <tr>
                        <td colspan="9" style="padding:32px;text-align:center;" class="text-muted">
                            Tidak ada data inventori material yang cocok dengan pencarian.
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
    <div class="card mb-4" style="border:1px solid #e2e8f0;box-shadow:none;border-radius:8px;overflow:hidden;">
        <div class="card-header flex items-center justify-between" style="background:#f8fafc;padding:12px 18px;border-bottom:1px solid #e2e8f0;">
            <div>
                <span class="fw-700" style="font-size:14px;color:#0f172a;">Inventori Alat Kerja</span>
            </div>
            <span class="text-muted" style="font-size:12px;">
                {{ $toolsCategoriesData->sum(fn($c) => $c->tools->count()) }} alat terdaftar
            </span>
        </div>
        <div class="table-wrap">
            <table class="data-table mb-0" style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                        <th style="padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;">Nama Alat & Kode</th>
                        <th style="padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;">Merk & Spesifikasi</th>
                        <th style="padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;">Kategori</th>
                        <th style="padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;">Gudang / Lokasi</th>
                        <th style="text-align:center;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;">Total Stok</th>
                        <th style="text-align:center;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;">Tersedia</th>
                        <th style="text-align:center;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;">Dipinjam</th>
                        <th style="padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;text-align:center;">Status</th>
                        <th style="text-align:center;width:65px;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;">Aksi</th>
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
                    <tr class="group-toggle" data-group="group-tool-cat-{{ $toolCategory->id }}" style="background:#f8fafc !important;cursor:pointer;user-select:none;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;">
                        <td colspan="9" style="padding:8px 14px !important;">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center" style="gap:8px;">
                                    <i class="fas fa-chevron-down group-chev" style="font-size:9.5px;color:#64748b;transition:transform .2s;"></i>
                                    <span class="fw-700" style="font-size:12.5px;letter-spacing:.04em;text-transform:uppercase;color:#1e293b;">{{ $toolCategory->name }}</span>
                                    <span style="font-size:11.5px;color:#64748b;font-weight:500;">
                                        ({{ $totalTools }} alat)
                                    </span>
                                </div>
                                <span style="font-size:11px;color:#94a3b8;font-weight:500;">
                                    Buka / Tutup
                                </span>
                            </div>
                        </td>
                    </tr>

                    {{-- Level 2: Sub-Group Header (Kelompok Alat / Type) --}}
                    @foreach($toolTypeGroups as $toolTypeName => $toolsInType)
                    @php $subToolKey = 'sub-tool-' . $toolCategory->id . '-' . Str::slug($toolTypeName); @endphp
                    <tr class="group-rows group-tool-cat-{{ $toolCategory->id }} subgroup-toggle" data-group="{{ $subToolKey }}" style="background:#fafbfc !important;cursor:pointer;user-select:none;border-bottom:1px solid #f1f5f9;border-left:3px solid #cbd5e1;">
                        <td colspan="9" style="padding:6px 14px 6px 28px !important;">
                            <div class="flex items-center justify-between" style="gap:8px;">
                                <div class="flex items-center" style="gap:7px;">
                                    <i class="fas fa-chevron-down subgroup-chev" style="font-size:8.5px;color:#94a3b8;transition:transform .2s;"></i>
                                    <span class="fw-600" style="font-size:12px;color:#334155;">
                                        {{ $toolTypeName }}
                                    </span>
                                    <span style="font-size:11px;color:#94a3b8;">
                                        ({{ $toolsInType->count() }})
                                    </span>
                                </div>
                                <span style="font-size:11px;color:#94a3b8;">
                                    Buka / Tutup
                                </span>
                            </div>
                        </td>
                    </tr>

                    {{-- Level 3: Individual Tool Rows --}}
                    @foreach($toolsInType as $tool)
                    <tr class="group-rows group-tool-cat-{{ $toolCategory->id }} subgroup-rows {{ $subToolKey }}" style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:9px 14px;vertical-align:middle;">
                            <div class="fw-600" style="color:#0f172a;font-size:12.5px;line-height:1.35;">{{ $tool->name }}</div>
                            <div style="margin-top:2px;">
                                <span style="font-family:ui-monospace,SFMono-Regular,Consolas,monospace;font-size:11.5px;color:#64748b;font-weight:600;letter-spacing:0.02em;">
                                    {{ $tool->code ?? '-' }}
                                </span>
                            </div>
                        </td>
                        <td style="padding:8px 12px;font-size:12px;color:#475569;vertical-align:middle;">
                            @if($tool->brand)
                            <span>{{ $tool->brand }}</span>
                            @endif
                            @if($tool->size)
                            <span class="text-muted" style="font-size:11px;">· {{ $tool->size }}</span>
                            @elseif(!$tool->brand)
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td style="padding:8px 12px;font-size:12px;color:#64748b;vertical-align:middle;">
                            {{ $toolCategory->name }}
                        </td>
                        <td style="padding:8px 12px;font-size:12px;color:#334155;font-weight:500;vertical-align:middle;">
                            @php
                                $whName = ($tool->relationLoaded('inventories') && $tool->inventories->isNotEmpty())
                                    ? ($tool->inventories->first()->warehouse?->name ?? '-')
                                    : ($tool->currentWarehouse?->name ?? 'Gudang Pusat');
                            @endphp
                            <span style="display:inline-flex;align-items:center;gap:5.5px;padding:2px 8px;border-radius:5px;background:#f8fafc;border:1px solid #e2e8f0;font-size:11.5px;color:#1e293b;font-weight:600;">
                                <i class="fas fa-warehouse" style="font-size:10px;color:#64748b;"></i>
                                {{ $whName }}
                            </span>
                        </td>
                        <td style="text-align:center;padding:8px 12px;vertical-align:middle;">
                            <span class="fw-700" style="font-size:13px;color:#0f172a;font-variant-numeric:tabular-nums;">{{ number_format($tool->stock_total, 0, ',', '.') }}</span>
                            <span class="text-muted" style="font-size:11px;"> unit</span>
                        </td>
                        <td style="text-align:center;padding:8px 12px;vertical-align:middle;">
                            <span class="fw-700" style="font-size:13px;color:#0f172a;font-variant-numeric:tabular-nums;">{{ number_format($tool->stock_available, 0, ',', '.') }}</span>
                            <span class="text-muted" style="font-size:11px;"> unit</span>
                        </td>
                        <td style="text-align:center;padding:8px 12px;font-size:12px;color:#64748b;vertical-align:middle;font-variant-numeric:tabular-nums;">
                            {{ number_format($tool->stock_borrowed, 0, ',', '.') }}
                        </td>
                        <td style="padding:8px 12px;vertical-align:middle;text-align:center;">
                            @if($tool->stock_available > 0)
                                <span class="badge" style="background:#f8fafc;color:#475569;border:1px solid #e2e8f0;font-size:10.5px;padding:2px 7px;border-radius:4px;font-weight:600;">Tersedia</span>
                            @else
                                <span class="badge" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;font-size:10.5px;padding:2px 7px;border-radius:4px;font-weight:600;">Habis / Dipinjam</span>
                            @endif
                        </td>
                        <td style="text-align:center;padding:8px 12px;vertical-align:middle;">
                            <a href="{{ route('tools.show', $tool) }}" class="btn btn-sm btn-light border" style="width:28px;height:28px;padding:0;display:inline-flex;align-items:center;justify-content:center;color:#475569;border-radius:5px;background:#f8fafc;" title="Detail Alat">
                                <i class="fas fa-eye" style="font-size:11px;"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                    @endforeach
                    @empty
                    <tr>
                        <td colspan="9" style="padding:32px;text-align:center;" class="text-muted">
                            Tidak ada data inventori alat kerja yang cocok dengan pencarian.
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
