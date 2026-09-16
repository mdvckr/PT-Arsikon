<x-app-layout>
    <x-slot name="title">Data Material</x-slot>

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-4" style="flex-wrap:wrap;gap:12px;">
        <div>
            <div class="breadcrumb" style="margin-bottom:4px;">
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
                <span>Data Material</span>
            </div>
            <h2 class="fw-700" style="font-size:20px;color:#0f172a;margin:0;">Data Material</h2>
            <p class="text-muted" style="font-size:12.5px;margin:2px 0 0;">Daftar seluruh master material konstruksi, spesifikasi, dan stok</p>
        </div>
        @can('create materials')
        <a href="{{ route('materials.create') }}" class="btn btn-primary" style="height:38px;padding:0 16px;font-size:13px;display:inline-flex;align-items:center;gap:6px;border-radius:6px;font-weight:600;">
            <i class="fas fa-plus"></i> Tambah Material
        </a>
        @endcan
    </div>

    {{-- Simple Search Bar (Filter Kategori Dihapus) --}}
    <div class="card mb-3" style="border:1px solid #e2e8f0;box-shadow:none;border-radius:8px;">
        <div class="card-body" style="padding:12px 16px;">
            <form method="GET" style="display:flex;gap:10px;align-items:center;">
                <div style="flex:1;position:relative;">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Cari material berdasarkan nama, SKU, atau ukuran..."
                        style="height:38px;border-radius:6px;font-size:13px;padding-left:34px;border:1px solid #cbd5e1;">
                    <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:12px;color:#94a3b8;"></i>
                </div>
                <button type="submit" class="btn btn-secondary" style="height:38px;padding:0 16px;border-radius:6px;font-size:13px;font-weight:600;">
                    Cari
                </button>
                @if(request('search'))
                <a href="{{ route('materials.index') }}" class="btn btn-light border" style="height:38px;padding:0 12px;border-radius:6px;font-size:13px;color:#64748b;" title="Reset Pencarian">
                    Reset
                </a>
                @endif
            </form>
        </div>
    </div>

    {{-- Grouped Accordion Table --}}
    <div class="card" style="border:1px solid #e2e8f0;box-shadow:none;border-radius:8px;overflow:hidden;">
        <div class="table-wrap">
            <table class="data-table mb-0" style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                        <th style="width:40px;text-align:center;padding:11px 10px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;">#</th>
                        <th style="padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;">Kode SKU</th>
                        <th style="padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;">Nama Material</th>
                        <th style="padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;">Supplier</th>
                        <th style="padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;">Ukuran</th>
                        <th style="padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;">Keterangan & Jadwal</th>
                        <th style="padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;">Tgl Input</th>
                        <th style="text-align:center;padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;">Total Stok</th>
                        <th style="text-align:center;padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;">Pemakaian</th>
                        <th style="text-align:center;padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;">Stok Sisa</th>
                        <th style="text-align:center;width:95px;padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;">Aksi</th>
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
                    {{-- Level 1: Category Header (Clean, minimal, no excessive colors) --}}
                    <tr class="group-toggle" data-group="group-cat-{{ $category->id }}" style="background:#f1f5f9 !important;cursor:pointer;user-select:none;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;">
                        <td colspan="11" style="padding:9px 16px !important;">
                            <div class="flex items-center justify-between" style="gap:10px;">
                                <div class="flex items-center" style="gap:8px;">
                                    <i class="fas fa-chevron-down group-chev" style="font-size:10px;color:#64748b;transition:transform .2s;" aria-hidden="true"></i>
                                    <span class="fw-700" style="font-size:13px;text-transform:uppercase;letter-spacing:.03em;color:#0f172a;">
                                        {{ $category->name }}
                                    </span>
                                    <span class="text-muted" style="font-size:12px;font-weight:500;">
                                        ({{ $totalMaterials }} material)
                                    </span>
                                </div>
                                <div>
                                    @can('create materials')
                                    <a href="{{ route('materials.create', ['category_id' => $category->id]) }}" class="text-muted" style="font-size:12px;font-weight:600;text-decoration:none;padding:2px 6px;" title="Tambah Material pada {{ $category->name }}" onclick="event.stopPropagation();">
                                        + Tambah
                                    </a>
                                    @endcan
                                </div>
                            </div>
                        </td>
                    </tr>

                    @foreach($typeGroups as $typeName => $materialsInType)
                    @php $subKey = 'sub-' . $category->id . '-' . Str::slug($typeName); @endphp
                    {{-- Level 2: Sub-Group Header (Simple indentation, clean typography, no random icons) --}}
                    <tr class="group-rows group-cat-{{ $category->id }} subgroup-toggle" data-group="{{ $subKey }}" style="background:#f8fafc !important;cursor:pointer;user-select:none;border-bottom:1px solid #edf2f7;">
                        <td colspan="11" style="padding:7px 16px 7px 34px !important;">
                            <div class="flex items-center justify-between" style="gap:8px;">
                                <div class="flex items-center" style="gap:7px;">
                                    <i class="fas fa-chevron-down subgroup-chev" style="font-size:9px;color:#94a3b8;transition:transform .2s;" aria-hidden="true"></i>
                                    <span class="fw-600" style="font-size:12.5px;color:#334155;">
                                        {{ $typeName }}
                                    </span>
                                    <span class="text-muted" style="font-size:11.5px;">
                                        ({{ $materialsInType->count() }})
                                    </span>
                                </div>
                                <span class="text-muted" style="font-size:11px;">
                                    Tutup / Buka
                                </span>
                            </div>
                        </td>
                    </tr>

                    {{-- Level 3: Individual Material Rows --}}
                    @foreach($materialsInType as $m)
                    <tr class="group-rows group-cat-{{ $category->id }} subgroup-rows {{ $subKey }}" style="border-bottom:1px solid #f1f5f9;">
                        <td class="text-muted" style="text-align:center;font-size:12px;padding:9px 10px;">{{ $loop->iteration }}</td>
                        <td style="padding:9px 14px;">
                            <span style="font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;font-size:11.5px;color:#334155;background:#f1f5f9;padding:2px 6px;border-radius:4px;border:1px solid #e2e8f0;">
                                {{ $m->sku }}
                            </span>
                        </td>
                        <td style="padding:9px 14px;">
                            <a href="{{ route('materials.show', $m) }}" style="color:#0f172a;font-weight:600;text-decoration:none;font-size:13px;display:inline-block;">
                                {{ $m->name }}
                            </a>
                            @if($m->brand)
                            <div class="text-muted" style="font-size:11px;margin-top:1px;">
                                Merek: {{ $m->brand }}
                            </div>
                            @endif
                        </td>
                        <td style="padding:9px 14px;font-size:12.5px;color:#334155;">
                            {{ $m->supplier?->name ?? $m->supplier_name ?? '-' }}
                        </td>
                        <td style="padding:9px 14px;font-size:12.5px;color:#334155;">
                            {{ $m->size ?: '-' }}
                        </td>
                        <td style="padding:9px 14px;max-width:280px;min-width:180px;">
                            @if($m->description)
                            <div style="font-size:12px;color:#334155;line-height:1.4;">{{ $m->description }}</div>
                            @endif

                            @if(!empty($m->incoming_stages) && count($m->incoming_stages) > 0)
                            <div style="margin-top:4px;display:flex;flex-wrap:wrap;gap:4px;">
                                @foreach($m->incoming_stages as $stg)
                                    @php
                                        $isReceived = ($stg['status'] ?? 'received') === 'received';
                                    @endphp
                                    <span style="display:inline-block;padding:1px 6px;border-radius:4px;font-size:10.5px;background:#f1f5f9;border:1px solid #e2e8f0;color:#334155;"
                                          title="{{ $stg['stage'] ?? 'T' }}: {{ number_format((float)($stg['qty'] ?? 0), 0, ',', '.') }} {{ $m->unit?->abbreviation ?? '' }} ({{ $isReceived ? 'Sudah Masuk' : 'Rencana Kedatangan' }})">
                                        <strong>{{ $stg['stage'] ?? ('T'.$loop->iteration) }}</strong>:
                                        {{ number_format((float)($stg['qty'] ?? 0), 0, ',', '.') }}
                                        <span class="text-muted" style="font-size:9.5px;">({{ $isReceived ? 'Masuk' : 'Rencana' }})</span>
                                    </span>
                                @endforeach
                            </div>
                            @elseif(!$m->description)
                            <span class="text-muted" style="font-size:12px;">-</span>
                            @endif
                        </td>
                        <td style="padding:9px 14px;font-size:12px;color:#64748b;white-space:nowrap;">
                            {{ $m->created_at ? $m->created_at->format('d/m/Y') : '-' }}
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
                        <td style="text-align:center;padding:9px 14px;">
                            <span style="font-weight:600;font-size:13.5px;color:#0f172a;">{{ number_format($totalStock, 0, ',', '.') }}</span>
                            @if($m->unit?->abbreviation || $m->unit?->name)
                            <span class="text-muted" style="font-size:11px;">{{ $m->unit->abbreviation ?? $m->unit->name }}</span>
                            @endif
                            @if($totalPlanned > 0)
                            <div class="text-muted" style="font-size:10.5px;margin-top:1px;">
                                +{{ number_format($totalPlanned, 0, ',', '.') }} rencana
                            </div>
                            @endif
                        </td>
                        <td style="text-align:center;padding:9px 14px;">
                            <span style="font-size:13px;color:#334155;">{{ number_format($pemakaian, 0, ',', '.') }}</span>
                            @if($m->unit?->abbreviation || $m->unit?->name)
                            <span class="text-muted" style="font-size:11px;">{{ $m->unit->abbreviation ?? $m->unit->name }}</span>
                            @endif
                        </td>
                        <td style="text-align:center;padding:9px 14px;">
                            <div style="font-weight:700;font-size:13.5px;color:#0f172a;">
                                {{ number_format($stockSisa, 0, ',', '.') }}
                                @if($m->unit?->abbreviation || $m->unit?->name)
                                <span class="text-muted" style="font-size:11px;font-weight:normal;">{{ $m->unit->abbreviation ?? $m->unit->name }}</span>
                                @endif
                            </div>
                            @if($stockSisa > 0)
                                <div style="font-size:11px;color:#16a34a;margin-top:1px;font-weight:500;">
                                    Tersedia
                                </div>
                            @else
                                <div style="font-size:11px;color:#dc2626;margin-top:1px;font-weight:500;">
                                    Habis
                                </div>
                            @endif
                        </td>

                        <td style="text-align:center;padding:9px 14px;">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('materials.show', $m) }}" class="btn btn-sm btn-light border" style="width:28px;height:28px;padding:0;display:inline-flex;align-items:center;justify-content:center;color:#475569;" title="Detail Material">
                                    <i class="fas fa-eye" style="font-size:11px;"></i>
                                </a>
                                @can('edit materials')
                                <a href="{{ route('materials.edit', $m) }}" class="btn btn-sm btn-light border" style="width:28px;height:28px;padding:0;display:inline-flex;align-items:center;justify-content:center;color:#475569;" title="Edit Material">
                                    <i class="fas fa-pen" style="font-size:11px;"></i>
                                </a>
                                @endcan
                                @can('delete materials')
                                <form method="POST" action="{{ route('materials.destroy', $m) }}"
                                    onsubmit="return confirm('Hapus material {{ addslashes($m->name) }}?')" style="display:inline-block;margin:0;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light border" style="width:28px;height:28px;padding:0;display:inline-flex;align-items:center;justify-content:center;color:#dc2626;" title="Hapus Material">
                                        <i class="fas fa-trash" style="font-size:11px;"></i>
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
                        <td colspan="11" style="padding:40px 20px;text-align:center;">
                            <div class="empty-state" style="display:flex;flex-direction:column;align-items:center;justify-content:center;">
                                <h3 style="font-size:14px;font-weight:700;color:#1e293b;margin-bottom:4px;">Belum Ada Material</h3>
                                <p class="text-muted" style="font-size:13px;max-width:320px;margin-bottom:14px;">Tambahkan material pertama Anda untuk mulai mengelola stok dan inventori.</p>
                                @can('create materials')
                                <a href="{{ route('materials.create') }}" class="btn btn-primary" style="height:36px;padding:0 16px;border-radius:6px;font-size:13px;font-weight:600;">
                                    Tambah Material
                                </a>
                                @endcan
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
