<x-app-layout>
    <x-slot name="title">Data Material</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="fw-700" style="font-size:20px;color:#0f172a;">Data Material</h2>
            <p class="text-muted" style="font-size:13px;margin-top:2px;">Kelola semua material konstruksi terdaftar</p>
        </div>
        @can('create materials')
        <a href="{{ route('materials.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Material
        </a>
        @endcan
    </div>

    {{-- Filter --}}
    <div class="card mb-4">
        <div class="card-body" style="padding:16px 20px;">
            <form method="GET" class="flex gap-3" style="flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:200px;">
                    <label class="form-label">Cari Material</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Nama, kode SKU, atau ukuran...">
                </div>
                <div style="min-width:180px;">
                    <label class="form-label">Kategori</label>
                    <select name="category_id" class="form-control" onchange="this.form.submit()">
                        <option value="">Semua Kategori</option>
                        @foreach($filterCategories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    {{-- Grouped Accordion Table --}}
    <div class="card">
        <div class="table-wrap">
            <table class="data-table mb-0">
                <thead>
                    <tr>
                        <th style="width:40px;text-align:center;">#</th>
                        <th>Kode SKU</th>
                        <th>Nama Material & Model</th>
                        <th>Ukuran & Dimensi</th>
                        <th>Keterangan (masih ada tambahan)</th>
                        <th>Tgl Input</th>
                        <th style="text-align:center;">Total Stock</th>
                        <th style="text-align:center;">Pemakaian</th>
                        <th style="text-align:center;">Stock Sisa</th>
                        <th style="text-align:center;width:95px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categoriesData as $category)
                    @php
                        // Group materials by type (kelompok barang)
                        $typeGroups = $category->materials->groupBy(function($m) {
                            if (!empty($m->type)) {
                                return $m->type;
                            }
                            if (!empty($m->size) && str_ends_with($m->name, $m->size)) {
                                $inferred = trim(substr($m->name, 0, -strlen($m->size)));
                                if (!empty($inferred)) return $inferred;
                            }
                            return $m->category?->name ?? 'Lainnya';
                        });
                        $totalMaterials = $category->materials->count();
                        $totalGroups = $typeGroups->count();
                    @endphp
                    {{-- Level 1: Category Header --}}
                    <tr class="group-toggle" data-group="group-cat-{{ $category->id }}" style="background:#f1f5f9 !important;cursor:pointer;">
                        <td colspan="10" style="padding:10px 20px !important;">
                            <div class="flex items-center justify-between" style="gap:12px;flex-wrap:nowrap;">
                                <div class="flex items-center" style="gap:8px;min-width:0;">
                                    <i class="fas fa-chevron-down group-chev" style="font-size:11px;color:#64748b;transition:transform .2s;" aria-hidden="true"></i>
                                    <span class="fw-700" style="font-size:13px;text-transform:uppercase;letter-spacing:.03em;color:#0f172a;white-space:nowrap;">
                                        {{ $category->name }}
                                    </span>
                                    <span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;white-space:nowrap;">
                                        {{ $totalMaterials }} material
                                    </span>
                                    <span class="badge" style="background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;white-space:nowrap;font-size:10px;">
                                        {{ $totalGroups }} kelompok
                                    </span>
                                </div>
                                <div class="flex items-center" style="gap:16px;flex-shrink:0;">
                                    @can('create materials')
                                    <a href="{{ route('materials.create', ['category_id' => $category->id]) }}" class="btn btn-sm btn-primary" title="Tambah Material pada {{ $category->name }}" onclick="event.stopPropagation();">
                                        <i class="fas fa-plus"></i> Tambah
                                    </a>
                                    @endcan
                                </div>
                            </div>
                        </td>
                    </tr>

                    @foreach($typeGroups as $typeName => $materialsInType)
                    @php $subKey = 'sub-' . $category->id . '-' . Str::slug($typeName); @endphp
                    {{-- Level 2: Sub-Group Header (Kelompok Barang / Type) --}}
                    <tr class="group-rows group-cat-{{ $category->id }} subgroup-toggle" data-group="{{ $subKey }}" style="background:#f8fafc !important;cursor:pointer;border-left:3px solid #3b82f6;">
                        <td colspan="10" style="padding:8px 20px 8px 40px !important;">
                            <div class="flex items-center justify-between" style="gap:8px;">
                                <div class="flex items-center" style="gap:7px;">
                                    <i class="fas fa-chevron-down subgroup-chev" style="font-size:10px;color:#94a3b8;transition:transform .2s;" aria-hidden="true"></i>
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

                    {{-- Level 3: Individual Material Rows --}}
                    @foreach($materialsInType as $m)
                    <tr class="group-rows group-cat-{{ $category->id }} subgroup-rows {{ $subKey }}">
                        <td class="text-muted" style="text-align:center;">{{ $loop->iteration }}</td>
                        <td><code style="background:#f1f5f9;padding:2px 7px;border-radius:5px;font-size:12px;font-weight:600;">{{ $m->sku }}</code></td>
                        <td>
                            <div class="fw-600" style="color:#0f172a;">{{ $m->name }}</div>
                            @if($m->type)
                            <div class="text-muted" style="font-size:11px;"><i class="fas fa-tag me-1 text-muted"></i>{{ $m->type }}</div>
                            @endif
                        </td>
                        <td>
                            @if($m->size)
                            <span class="badge bg-light text-dark border" style="font-size:12px;font-weight:600;">
                                <i class="fas fa-ruler-combined text-muted me-1"></i> {{ $m->size }}
                            </span>
                            @else
                            <span class="text-muted" style="font-size:12px;">-</span>
                            @endif
                        </td>
                        <td style="max-width:280px;min-width:180px;">
                            @if($m->description)
                            <div style="font-size:12px;color:#334155;line-height:1.4;">{{ $m->description }}</div>
                            @endif

                            @if(!empty($m->incoming_stages) && count($m->incoming_stages) > 0)
                            <div style="margin-top:6px;display:flex;flex-wrap:wrap;gap:4px;align-items:center;">
                                @foreach($m->incoming_stages as $stg)
                                    @php
                                        $isReceived = ($stg['status'] ?? 'received') === 'received';
                                    @endphp
                                    <span class="inline-flex items-center" 
                                          style="display:inline-flex;align-items:center;padding:2px 6px;border-radius:4px;font-size:10.5px;line-height:1.2;
                                                 background:{{ $isReceived ? '#f0fdf4' : '#fffbeb' }};
                                                 border:1px solid {{ $isReceived ? '#bbf7d0' : '#fde68a' }};
                                                 color:{{ $isReceived ? '#166534' : '#92400e' }};"
                                          title="{{ $stg['stage'] ?? 'T' }}: {{ number_format((float)($stg['qty'] ?? 0), 0, ',', '.') }} {{ $m->unit?->abbreviation ?? '' }} ({{ $isReceived ? 'Sudah Masuk' : 'Rencana Kedatangan' }}) {{ !empty($stg['notes']) ? '- ' . $stg['notes'] : '' }}">
                                        <strong style="margin-right:3px;">{{ $stg['stage'] ?? ('T'.($loop->iteration)) }}</strong>:
                                        <span>{{ number_format((float)($stg['qty'] ?? 0), 0, ',', '.') }}</span>
                                        @if(!empty($stg['date']))
                                        <span style="opacity:0.75;font-size:9.5px;margin-left:3px;">{{ \Carbon\Carbon::parse($stg['date'])->format('d/m') }}</span>
                                        @endif
                                        <i class="fas {{ $isReceived ? 'fa-check text-success' : 'fa-clock text-warning' }}" style="font-size:9px;margin-left:4px;" aria-hidden="true"></i>
                                    </span>
                                @endforeach
                            </div>
                            @elseif(!$m->description)
                            <span class="text-muted" style="font-size:12px;">-</span>
                            @endif
                        </td>
                        <td style="font-size:12px;color:#64748b;white-space:nowrap;">
                            <div>{{ $m->created_at ? $m->created_at->format('d M Y') : '-' }}</div>
                            <div style="font-size:10.5px;" class="text-muted">{{ $m->created_at ? $m->created_at->format('H:i') : '' }}</div>
                        </td>
                        @php 
                            $stockSisa = (float) $m->inventories->sum('quantity'); 
                            $activeInventories = $m->inventories->where('quantity', '>', 0);
                            $pemakaian = (float) abs($m->stockMutations->where('qty_change', '<', 0)->sum('qty_change'));
                            $totalIn = (float) $m->stockMutations->where('qty_change', '>', 0)->sum('qty_change');
                            $totalStock = $totalIn > 0 ? max($totalIn, $stockSisa + $pemakaian) : ($stockSisa + $pemakaian);

                            $plannedStages = !empty($m->incoming_stages) ? collect($m->incoming_stages)->where('status', 'planned') : collect();
                            $totalPlanned = (float) $plannedStages->sum('qty');
                        @endphp
                        <td style="text-align:center;">
                            <span style="font-weight:700;font-size:14px;color:#0f172a;">{{ number_format($totalStock, 0, ',', '.') }}</span>
                            @if($m->unit?->abbreviation || $m->unit?->name)
                            <span class="text-muted" style="font-size:11px;"> {{ $m->unit->abbreviation ?? $m->unit->name }}</span>
                            @endif

                            @if($totalPlanned > 0)
                            <div style="font-size:10px;color:#b45309;margin-top:2px;" title="Jadwal Rencana Kedatangan Mendatang">
                                <i class="fas fa-clock me-1"></i>+{{ number_format($totalPlanned, 0, ',', '.') }} rencana
                            </div>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            @if($pemakaian > 0)
                                <span style="font-weight:700;font-size:14px;color:#e11d48;">{{ number_format($pemakaian, 0, ',', '.') }}</span>
                            @else
                                <span class="text-muted" style="font-size:13px;font-weight:500;">0</span>
                            @endif
                            @if($m->unit?->abbreviation || $m->unit?->name)
                            <span class="text-muted" style="font-size:11px;"> {{ $m->unit->abbreviation ?? $m->unit->name }}</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            <div style="font-weight:700;font-size:14px;color:#16a34a;">
                                {{ number_format($stockSisa, 0, ',', '.') }}
                                @if($m->unit?->abbreviation || $m->unit?->name)
                                <span class="text-muted" style="font-size:11px;font-weight:normal;"> {{ $m->unit->abbreviation ?? $m->unit->name }}</span>
                                @endif
                            </div>
                            @if($stockSisa > 0)
                                <span class="badge bg-success-subtle text-success" style="font-size:10px;" title="{{ $activeInventories->count() }} Gudang/Lokasi">
                                    <i class="fas fa-warehouse me-1"></i> {{ $activeInventories->count() }} Lokasi
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger" style="font-size:10px;">
                                    <i class="fas fa-box-open me-1"></i> Habis
                                </span>
                            @endif
                        </td>

                        <td style="text-align:center;">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('materials.show', $m) }}" class="btn btn-sm btn-info btn-icon" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('edit materials')
                                <a href="{{ route('materials.edit', $m) }}" class="btn btn-sm btn-warning btn-icon" title="Edit">
                                    <i class="fas fa-pen"></i>
                                </a>
                                @endcan
                                @can('delete materials')
                                <form method="POST" action="{{ route('materials.destroy', $m) }}"
                                    onsubmit="return confirm('Hapus material {{ addslashes($m->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger btn-icon" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    @endforeach
                    @empty
                    <tr>
                        <td colspan="10">
                            <div class="empty-state">
                                <i class="fas fa-boxes-stacked"></i>
                                <h3>Belum Ada Material</h3>
                                <p>Tambahkan material pertama untuk memulai.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
        function initMaterialAccordion() {
            // Level 1: Category Toggle
            var toggleRows = document.querySelectorAll('.group-toggle');
            toggleRows.forEach(function (row) {
                var newRow = row.cloneNode(true);
                row.parentNode.replaceChild(newRow, row);

                var group = newRow.getAttribute('data-group');
                newRow.addEventListener('click', function (e) {
                    if (e.target.closest('.btn') || e.target.closest('a')) return;

                    var isCatCollapsed = newRow.classList.toggle('collapsed');
                    var targetRows = document.querySelectorAll('.' + group);
                    targetRows.forEach(function (r) {
                        if (isCatCollapsed) {
                            r.style.display = 'none';
                        } else {
                            if (r.classList.contains('subgroup-toggle')) {
                                r.style.display = '';
                            } else if (r.classList.contains('subgroup-rows')) {
                                // Hanya tampilkan baris item jika subgroup-nya tidak sedang tertutup
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
                    var chev = newRow.querySelector('.group-chev');
                    if (chev) {
                        chev.style.transform = isCatCollapsed ? 'rotate(-90deg)' : '';
                    }
                });
            });

            // Level 2: Subgroup (Kelompok Barang) Toggle - Buka dan Tutup
            var subToggleRows = document.querySelectorAll('.subgroup-toggle');
            subToggleRows.forEach(function (row) {
                var newSubRow = row.cloneNode(true);
                row.parentNode.replaceChild(newSubRow, row);

                var subKey = newSubRow.getAttribute('data-group');
                newSubRow.addEventListener('click', function (e) {
                    if (e.target.closest('.btn') || e.target.closest('a')) return;

                    var isSubCollapsed = newSubRow.classList.toggle('collapsed');
                    var childRows = document.querySelectorAll('.' + subKey);
                    childRows.forEach(function (r) {
                        r.style.display = isSubCollapsed ? 'none' : '';
                    });

                    var chev = newSubRow.querySelector('.subgroup-chev');
                    if (chev) {
                        chev.style.transform = isSubCollapsed ? 'rotate(-90deg)' : '';
                    }
                });
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initMaterialAccordion);
        } else {
            initMaterialAccordion();
        }
    </script>
    @endpush
</x-app-layout>
