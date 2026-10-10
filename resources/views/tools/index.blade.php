<x-app-layout>
    <x-slot name="title">Data Alat</x-slot>

    @push('styles')
    <style>
        .action-btn-group {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        .action-btn {
            width: 30px;
            height: 30px;
            padding: 0;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            transition: all 0.15s ease;
            text-decoration: none;
            cursor: pointer;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        }
        .action-btn-view {
            color: #2563eb;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
        }
        .action-btn-view:hover {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
            box-shadow: 0 2px 4px rgba(37,99,235,0.25);
        }
        .action-btn-edit {
            color: #d97706;
            background: #fffbeb;
            border: 1px solid #fde68a;
        }
        .action-btn-edit:hover {
            background: #d97706;
            color: #ffffff;
            border-color: #d97706;
            box-shadow: 0 2px 4px rgba(217,119,6,0.25);
        }
        .action-btn-delete {
            color: #dc2626;
            background: #fef2f2;
            border: 1px solid #fecaca;
        }
        .action-btn-delete:hover {
            background: #dc2626;
            color: #ffffff;
            border-color: #dc2626;
            box-shadow: 0 2px 4px rgba(220,38,38,0.25);
        }
        /* Fix empty-state conflict from global styles */
        .empty-custom-box i {
            display: inline-block !important;
            margin: 0 !important;
            opacity: 1 !important;
        }
    </style>
    @endpush

    <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
        <div>
            <div class="flex items-center gap-2.5">
                <h2 class="fw-700" style="font-size:20px;color:#0f172a;margin:0;">Data Alat</h2>
                @php
                    $currentWhObj = is_numeric($selectedWarehouseId ?? null) ? $accessibleWarehouses->firstWhere('id', (int)$selectedWarehouseId) : null;
                @endphp
                @if($currentWhObj)
                    <span class="badge" style="background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;padding:3.5px 9px;border-radius:6px;font-size:11.5px;font-weight:600;display:inline-flex;align-items:center;gap:5px;" title="Gudang: {{ $currentWhObj->name }}">
                        <i class="fas fa-warehouse text-primary" style="font-size:10px;"></i> {{ $currentWhObj->name }}{{ $currentWhObj->is_central ? ' (Pusat)' : ' (Proyek)' }}
                    </span>
                @elseif(($selectedWarehouseId ?? null) === 'all')
                    <span class="badge" style="background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;padding:3.5px 9px;border-radius:6px;font-size:11.5px;font-weight:600;display:inline-flex;align-items:center;gap:5px;">
                        <i class="fas fa-boxes-stacked text-primary" style="font-size:10px;"></i> Semua Gudang (Konsolidasi)
                    </span>
                @endif
            </div>
            <p class="text-muted" style="font-size:13px;margin:3px 0 0 0;">Kelola inventaris alat kerja dan stok pemakaian per kategori</p>
        </div>
        @can('create tools')
        <div>
            <a href="{{ route('tools.create') }}" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:7px;border-radius:8px;padding:8px 16px;font-size:13px;font-weight:600;box-shadow:0 1px 3px rgba(37,99,235,0.2);">
                <i class="fas fa-plus"></i> Tambah Alat
            </a>
        </div>
        @endcan
    </div>

    {{-- Filter Card --}}
    <div class="card mb-4" style="border:1px solid #e2e8f0;border-radius:8px;box-shadow:none;background:#ffffff;">
        <div class="card-body" style="padding:14px 18px;">
            <form method="GET" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
                <div style="flex:1;min-width:240px;">
                    <label class="form-label" style="font-size:12px;font-weight:600;color:#475569;margin-bottom:4px;">Cari Alat</label>
                    <div style="position:relative;">
                        <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:12px;color:#94a3b8;pointer-events:none;"></i>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                            placeholder="Nama, kode alat, merk, kelompok, atau ukuran..."
                            style="padding-left:34px;height:38px;border-radius:6px;font-size:13px;border:1px solid #cbd5e1;">
                    </div>
                </div>
                <div style="min-width:180px;">
                    <label class="form-label" style="font-size:12px;font-weight:600;color:#475569;margin-bottom:4px;">Kategori</label>
                    <select name="category_id" class="form-control" onchange="this.form.submit()"
                        style="height:38px;border-radius:6px;font-size:13px;border:1px solid #cbd5e1;">
                        <option value="">Semua Kategori</option>
                        @foreach($filterCategories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @if($isGlobalAccess || $accessibleWarehouses->count() > 1)
                <div style="min-width:200px;">
                    <label class="form-label" style="font-size:12px;font-weight:600;color:#475569;margin-bottom:4px;">Filter Gudang</label>
                    <select name="warehouse_id" id="tool-warehouse-filter" class="form-control" onchange="this.form.submit()"
                        style="height:38px;border-radius:6px;font-size:13px;border:1px solid #cbd5e1;">
                        @if($isGlobalAccess)
                        <option value="all" {{ ($selectedWarehouseId === 'all') ? 'selected' : '' }}>&#127981; Semua Gudang (Konsolidasi)</option>
                        @endif
                        @foreach($accessibleWarehouses as $wh)
                        <option value="{{ $wh->id }}" {{ (string)$selectedWarehouseId === (string)$wh->id ? 'selected' : '' }}>
                            {{ $wh->name }}{{ $wh->is_central ? ' (Pusat)' : ' (Proyek)' }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif
                @php
                    $isFiltered = request('search') || request('category_id') || (request()->has('warehouse_id') && request('warehouse_id') != (auth()->user()->activeWarehouse()?->id ?? ''));
                @endphp
                @if($isFiltered)
                <div>
                    <a href="{{ route('tools.index') }}" class="btn btn-light border" style="height:38px;padding:0 14px;border-radius:6px;font-size:13px;font-weight:600;display:inline-flex;align-items:center;gap:6px;color:#64748b;background:#ffffff;" title="Reset Filter">
                        <i class="fas fa-times"></i> Reset
                    </a>
                </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Grouped Accordion Table --}}
    <div class="card">
        <div class="table-wrap">
            <table class="data-table mb-0">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                        <th style="width:130px;white-space:nowrap;padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;">Kode Alat</th>
                        <th style="padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;">Nama Alat & Model</th>
                        <th style="padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;">Merk</th>
                        <th style="padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;">Tahapan Masuk</th>
                        <th style="white-space:nowrap;padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;">Tgl Input</th>
                        <th style="text-align:center;white-space:nowrap;padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;">Total Stock</th>
                        <th style="text-align:center;white-space:nowrap;padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;">Dipinjam</th>
                        <th style="text-align:center;white-space:nowrap;padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#dc2626;">Kondisi Rusak</th>
                        <th style="text-align:center;white-space:nowrap;padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;">Stock Sisa</th>
                        <th style="text-align:center;width:95px;white-space:nowrap;padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php $dataToIterate = $categoriesData ?? $categories; @endphp
                    @forelse($dataToIterate as $category)
                    @php
                        // Group tools by type (kelompok barang)
                        $typeGroups = $category->tools->groupBy(function($t) {
                            if (!empty($t->type)) {
                                return $t->type;
                            }
                            if (!empty($t->size) && str_ends_with($t->name, $t->size)) {
                                $inferred = trim(substr($t->name, 0, -strlen($t->size)));
                                if (!empty($inferred)) return $inferred;
                            }
                            return $t->category?->name ?? 'Lainnya';
                        });
                        $totalTools = $category->tools->count();
                        $totalGroups = $typeGroups->count();
                    @endphp
                    {{-- Level 1: Category Header --}}
                    <tr class="group-toggle {{ !request('search') ? 'collapsed' : '' }}" data-group="group-cat-{{ $category->id }}" style="background:#f8fafc !important;cursor:pointer;user-select:none;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;">
                        <td colspan="10" style="padding:8px 14px !important;">
                            <div class="flex items-center justify-between" style="gap:12px;flex-wrap:nowrap;">
                                <div class="flex items-center" style="gap:8px;min-width:0;">
                                    <i class="fas fa-chevron-down group-chev" style="font-size:9.5px;color:#64748b;transition:transform .2s;{{ !request('search') ? 'transform:rotate(-90deg);' : '' }}" aria-hidden="true"></i>
                                    <span class="fw-700" style="font-size:12.5px;text-transform:uppercase;letter-spacing:.03em;color:#1e293b;white-space:nowrap;">
                                        {{ $category->name }}
                                    </span>
                                    <span style="font-size:11.5px;color:#64748b;font-weight:500;">
                                        ({{ $totalTools }} alat)
                                    </span>
                                </div>
                                <div class="flex items-center" style="gap:6px;flex-shrink:0;">
                                    @can('create tools')
                                    <a href="{{ route('tools.create', ['category_id' => $category->id]) }}" style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:5px;font-size:11.5px;font-weight:600;background:#ffffff;border:1px solid #cbd5e1;color:#2563eb;text-decoration:none;box-shadow:0 1px 2px rgba(0,0,0,0.04);" title="Tambah Alat pada {{ $category->name }}" onclick="event.stopPropagation();">
                                        <i class="fas fa-plus" style="font-size:9.5px;"></i> Tambah
                                    </a>
                                    @endcan
                                    @if(auth()->user()->can('delete categories') || auth()->user()->hasAnyRole(['Owner', 'Admin Pusat', 'Admin', 'Admin Gudang Pusat']))
                                    <button type="button" 
                                        onclick="event.stopPropagation(); openDeleteCategoryModal({{ $category->id }}, '{{ addslashes($category->name) }}', {{ $totalTools }})" 
                                        style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:5px;font-size:11.5px;font-weight:600;background:#ffffff;border:1px solid #fecaca;color:#dc2626;box-shadow:0 1px 2px rgba(0,0,0,0.04);cursor:pointer;" 
                                        title="Hapus Kategori {{ $category->name }} jika salah memasukkan">
                                        <i class="fas fa-trash-can" style="font-size:10px;"></i> Hapus
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>

                    @foreach($typeGroups as $toolTypeName => $toolsInType)
                    @php $subKey = 'sub-' . $category->id . '-' . Str::slug($toolTypeName); @endphp
                    {{-- Level 2: Sub-Group Header (Kelompok Alat / Type) --}}
                    <tr class="group-rows group-cat-{{ $category->id }} subgroup-toggle {{ !request('search') ? 'collapsed' : '' }}" data-group="{{ $subKey }}" style="background:#fafbfc !important;cursor:pointer;user-select:none;border-bottom:1px solid #f1f5f9;border-left:3px solid #cbd5e1;{{ !request('search') ? 'display:none;' : '' }}">
                        <td colspan="10" style="padding:6px 14px 6px 28px !important;">
                            <div class="flex items-center justify-between" style="gap:8px;">
                                <div class="flex items-center" style="gap:7px;">
                                    <i class="fas fa-chevron-down subgroup-chev" style="font-size:8.5px;color:#94a3b8;transition:transform .2s;{{ !request('search') ? 'transform:rotate(-90deg);' : '' }}" aria-hidden="true"></i>
                                    <span class="fw-600" style="font-size:12px;color:#334155;">
                                        {{ $toolTypeName }}
                                    </span>
                                    <span style="font-size:11px;color:#94a3b8;">
                                        ({{ $toolsInType->count() }})
                                    </span>
                                </div>
                                <div class="flex items-center" style="gap:10px;">
                                    @if(auth()->user()->can('delete tools') || auth()->user()->hasAnyRole(['Owner', 'Admin Pusat', 'Admin', 'Admin Gudang Pusat']))
                                    <button type="button" 
                                        onclick="event.stopPropagation(); openDeleteGroupModal({{ $category->id }}, '{{ addslashes($category->name) }}', '{{ addslashes($toolTypeName) }}', {{ $toolsInType->count() }})" 
                                        style="display:inline-flex;align-items:center;gap:3px;padding:2px 7px;border-radius:4px;font-size:11px;font-weight:600;background:#ffffff;border:1px solid #fecaca;color:#dc2626;cursor:pointer;" 
                                        title="Hapus kelompok {{ $toolTypeName }} jika salah memasukkan">
                                        <i class="fas fa-trash-can" style="font-size:9.5px;"></i> Hapus Kelompok
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>

                    {{-- Level 3: Individual Tool Rows --}}
                    @foreach($toolsInType as $tool)
                    <tr class="group-rows group-cat-{{ $category->id }} subgroup-rows {{ $subKey }}" style="border-bottom:1px solid #f1f5f9;{{ !request('search') ? 'display:none;' : '' }}">
                        <td style="width:130px;white-space:nowrap;padding:9px 14px;vertical-align:middle;">
                            <span style="font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;font-size:12px;font-weight:600;color:#334155;letter-spacing:0.02em;">
                                {{ $tool->code ?? '-' }}
                            </span>
                        </td>
                        <td style="padding:9px 14px;vertical-align:middle;">
                            <a href="{{ route('tools.show', $tool) }}" class="fw-600" style="color:#0f172a;font-size:13px;text-decoration:none;display:inline-block;line-height:1.35;">
                                {{ $tool->name }}
                            </a>
                            @if($tool->type)
                            <div style="font-size:11.5px;color:#64748b;margin-top:2px;">{{ $tool->type }}</div>
                            @endif
                        </td>
                        <td style="padding:9px 14px;font-size:12.5px;color:#334155;vertical-align:middle;">
                            @if($tool->brand && $tool->size)
                                <span class="fw-600">{{ $tool->brand }}</span> <span class="text-muted" style="font-size:11.5px;">· {{ $tool->size }}</span>
                            @elseif($tool->brand)
                                <span class="fw-600">{{ $tool->brand }}</span>
                            @elseif($tool->size)
                                <span>{{ $tool->size }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td style="padding:9px 14px;white-space:nowrap;vertical-align:middle;">
                            @if(!empty($tool->incoming_stages) && count($tool->incoming_stages) > 0)
                                @php
                                    $stages = collect($tool->incoming_stages);
                                    $stageCount = $stages->count();
                                    $receivedStages = $stages->where('status', 'received');
                                    $plannedStages = $stages->where('status', 'planned');
                                    $recCount = $receivedStages->count();
                                    $planCount = $plannedStages->count();
                                    $recQty = (int) $receivedStages->sum('qty');
                                    $planQty = (int) $plannedStages->sum('qty');
                                    $unitAbbr = 'unit';
                                @endphp

                                @if($stageCount === 1)
                                    @php
                                        $stg = $stages->first();
                                        $isReceived = ($stg['status'] ?? 'received') === 'received';
                                        $bg = $isReceived ? '#f0fdf4' : '#fffbeb';
                                        $border = $isReceived ? '#bbf7d0' : '#fde68a';
                                        $color = $isReceived ? '#166534' : '#92400e';
                                        $icon = $isReceived ? 'fa-check-circle' : 'fa-clock';
                                        $iconColor = $isReceived ? '#16a34a' : '#d97706';
                                        $statusLabel = $isReceived ? 'Masuk' : 'Rencana';
                                        $stageDate = !empty($stg['date']) ? \Carbon\Carbon::parse($stg['date'])->format('d/m/Y') : null;
                                    @endphp
                                    <span style="display:inline-flex;align-items:center;gap:3.5px;padding:2.5px 7px;border-radius:5px;font-size:10.5px;font-weight:500;background:{{ $bg }};border:1px solid {{ $border }};color:{{ $color }};"
                                          title="{{ $stg['stage'] ?? 'T1' }}: {{ number_format((float)($stg['qty'] ?? 0), 0, ',', '.') }} unit ({{ $statusLabel }}){{ $stageDate ? ' · '.$stageDate : '' }}{{ !empty($stg['notes']) ? ' · '.$stg['notes'] : '' }}">
                                        <i class="fas {{ $icon }}" style="font-size:9.5px;color:{{ $iconColor }};"></i>
                                        <strong style="font-weight:700;">{{ $stg['stage'] ?? 'T1' }}</strong>:
                                        <span>{{ number_format((float)($stg['qty'] ?? 0), 0, ',', '.') }}</span>
                                        <span style="font-size:9.5px;opacity:0.9;">({{ $statusLabel }})</span>
                                    </span>
                                @else
                                    @php
                                        $allReceived = ($recCount === $stageCount);
                                        $btnBg = $allReceived ? '#f0fdf4' : '#f8fafc';
                                        $btnBorder = $allReceived ? '#bbf7d0' : '#cbd5e1';
                                        $btnColor = $allReceived ? '#166534' : '#334155';
                                    @endphp
                                    <button type="button"
                                            class="btn-stage-detail"
                                            onclick="openStagesFromBtn(this)"
                                            data-name="{{ $tool->name }}"
                                            data-sku="{{ $tool->code ?? '-' }}"
                                            data-unit="unit"
                                            data-stages='@json($stages)'
                                            style="display:inline-flex;align-items:center;gap:5px;padding:2.5px 8px;border-radius:5px;font-size:10.5px;font-weight:500;background:{{ $btnBg }};border:1px solid {{ $btnBorder }};color:{{ $btnColor }};cursor:pointer;line-height:1.3;transition:all .15s ease;"
                                            onmouseover="this.style.opacity='0.85';"
                                            onmouseout="this.style.opacity='1';">
                                        @if($allReceived)
                                            <i class="fas fa-check-circle" style="font-size:9.5px;color:#16a34a;"></i>
                                            <span><strong style="font-weight:700;">{{ $stageCount }}/{{ $stageCount }}</strong> Masuk</span>
                                            <span style="color:#86efac;">•</span>
                                            <span style="font-weight:700;color:#15803d;">{{ number_format($recQty, 0, ',', '.') }} unit</span>
                                        @elseif($recCount === 0)
                                            <i class="fas fa-clock" style="font-size:9.5px;color:#d97706;"></i>
                                            <span><strong style="font-weight:700;">{{ $stageCount }}</strong> Tahap Rencana</span>
                                            <span style="color:#cbd5e1;">•</span>
                                            <span style="font-weight:600;color:#92400e;">{{ number_format($planQty, 0, ',', '.') }} unit</span>
                                        @else
                                            <i class="fas fa-layer-group" style="font-size:9.5px;color:#64748b;"></i>
                                            <span style="color:#166534;font-weight:600;"><i class="fas fa-check-circle" style="font-size:9px;color:#16a34a;margin-right:2px;"></i>{{ $recCount }}/{{ $stageCount }} Masuk</span>
                                            <span style="color:#cbd5e1;">•</span>
                                            <span style="font-weight:700;color:#15803d;">{{ number_format($recQty, 0, ',', '.') }}</span>
                                            <span style="font-size:9.5px;color:#92400e;font-weight:500;">(+{{ number_format($planQty, 0, ',', '.') }})</span>
                                        @endif
                                        <i class="fas fa-search-plus" style="font-size:8.5px;color:#94a3b8;margin-left:2px;" title="Lihat rincian tahapan"></i>
                                    </button>
                                @endif
                            @else
                                <span class="text-muted" style="font-size:12px;">-</span>
                            @endif
                        </td>
                        <td style="padding:9px 14px;font-size:12px;color:#64748b;white-space:nowrap;vertical-align:middle;">
                            {{ $tool->created_at ? $tool->created_at->format('d/m/Y') : '-' }}
                        </td>
                        <td style="text-align:center;padding:9px 14px;vertical-align:middle;white-space:nowrap;">
                            <span class="fw-700" style="font-size:13px;color:#0f172a;font-variant-numeric:tabular-nums;">{{ number_format($tool->stock_total, 0, ',', '.') }}</span>
                            <span class="text-muted" style="font-size:11px;"> unit</span>
                        </td>
                        <td style="text-align:center;padding:9px 14px;vertical-align:middle;white-space:nowrap;">
                            <span style="font-size:12.5px;color:#64748b;font-variant-numeric:tabular-nums;">{{ number_format($tool->stock_borrowed, 0, ',', '.') }}</span>
                            <span class="text-muted" style="font-size:11px;"> unit</span>
                        </td>
                        <td style="text-align:center;padding:9px 14px;vertical-align:middle;white-space:nowrap;">
                            @php
                                $damagedCount = (int) $tool->stock_damaged;
                                $maintCount   = (int) $tool->stock_maintenance;
                            @endphp
                            @if($damagedCount > 0 || $maintCount > 0)
                                <div class="fw-700" style="font-size:13px;color:#dc2626;font-variant-numeric:tabular-nums;">
                                    {{ number_format($damagedCount + $maintCount, 0, ',', '.') }} <span class="text-muted" style="font-size:11px;font-weight:normal;">unit</span>
                                </div>
                                @if($damagedCount > 0 && $maintCount > 0)
                                    <span class="badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-size:10px;padding:2px 6px;border-radius:4px;font-weight:600;margin-top:2px;display:inline-block;" title="{{ $damagedCount }} Rusak, {{ $maintCount }} Maintenance">
                                        <i class="fas fa-triangle-exclamation" style="font-size:9px;margin-right:2px;"></i> {{ $damagedCount }} Rusak • {{ $maintCount }} Maint
                                    </span>
                                @elseif($damagedCount > 0)
                                    <span class="badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-size:10px;padding:2px 7px;border-radius:4px;font-weight:600;margin-top:2px;display:inline-block;">
                                        <i class="fas fa-triangle-exclamation" style="font-size:9px;margin-right:2px;"></i> Rusak
                                    </span>
                                @else
                                    <span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-size:10px;padding:2px 7px;border-radius:4px;font-weight:600;margin-top:2px;display:inline-block;">
                                        <i class="fas fa-wrench" style="font-size:9px;margin-right:2px;"></i> Maintenance
                                    </span>
                                @endif
                            @else
                                <span style="font-size:12.5px;color:#94a3b8;font-variant-numeric:tabular-nums;">0 unit</span>
                            @endif
                        </td>
                        <td style="text-align:center;padding:9px 14px;vertical-align:middle;white-space:nowrap;">
                            <div class="fw-700" style="font-size:13px;color:#0f172a;font-variant-numeric:tabular-nums;">
                                {{ number_format($tool->stock_available, 0, ',', '.') }} <span class="text-muted" style="font-size:11px;font-weight:normal;">unit</span>
                            </div>
                            @if($tool->stock_available > 0)
                                <span class="badge" style="background:#f8fafc;color:#475569;border:1px solid #e2e8f0;font-size:10px;padding:2px 7px;border-radius:4px;font-weight:600;margin-top:2px;display:inline-block;">Tersedia</span>
                            @else
                                <span class="badge" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;font-size:10px;padding:2px 7px;border-radius:4px;font-weight:600;margin-top:2px;display:inline-block;">Habis</span>
                            @endif
                        </td>
                        <td style="text-align:center;padding:8px 12px;vertical-align:middle;white-space:nowrap;width:110px;">
                            <div class="action-btn-group">
                                <a href="{{ route('tools.show', $tool) }}" class="action-btn action-btn-view" title="Detail Alat">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('edit tools')
                                <a href="{{ route('tools.edit', $tool) }}" class="action-btn action-btn-edit" title="Edit Alat">
                                    <i class="fas fa-pen"></i>
                                </a>
                                @endcan
                                @can('delete tools')
                                <form method="POST" action="{{ route('tools.destroy', $tool) }}"
                                    onsubmit="return confirm('Hapus alat {{ addslashes($tool->name) }}?')" style="display:inline-block;margin:0;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn action-btn-delete" title="Hapus Alat">
                                        <i class="fas fa-trash-can"></i>
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
                        <td colspan="10" style="padding:48px 20px;text-align:center;">
                            <div class="empty-custom-box" style="display:inline-flex;flex-direction:column;align-items:center;justify-content:center;max-width:360px;margin:0 auto;">
                                <div style="width:56px;height:56px;border-radius:50%;background:#f1f5f9;display:inline-flex;align-items:center;justify-content:center;margin-bottom:14px;color:#94a3b8;">
                                    <i class="fas fa-tools" style="font-size:22px !important;color:#94a3b8 !important;"></i>
                                </div>
                                <h3 style="font-size:15px;font-weight:700;color:#1e293b;margin:0 0 6px 0;">Belum Ada Alat</h3>
                                <p class="text-muted" style="font-size:13px;line-height:1.5;margin:0 0 16px 0;">
                                    @if(request('search') || request('category_id') || request('warehouse_id'))
                                        Tidak ada alat yang sesuai dengan kriteria filter saat ini.
                                    @else
                                        Tambahkan alat kerja pertama untuk memulai inventaris gudang.
                                    @endif
                                </p>
                                @can('create tools')
                                <a href="{{ route('tools.create') }}" class="btn btn-primary" style="height:36px;padding:0 18px;border-radius:6px;font-size:13px;font-weight:600;display:inline-flex;align-items:center;gap:7px;box-shadow:0 1px 3px rgba(37,99,235,0.2);">
                                    <i class="fas fa-plus" style="font-size:11px !important;line-height:1 !important;"></i>
                                    <span>Tambah Alat</span>
                                </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    {{-- Modal Rincian Tahapan Kedatangan Alat --}}
    <div id="modalStages" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.55);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#ffffff;border-radius:10px;border:1px solid #e2e8f0;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);width:100%;max-width:440px;overflow:hidden;">
            {{-- Modal Header --}}
            <div style="background:#f8fafc;padding:14px 18px;border-bottom:1px solid #e2e8f0;display:flex;align-items:flex-start;justify-content:space-between;gap:12px;">
                <div>
                    <div style="display:flex;align-items:center;gap:6px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.04em;">
                        <i class="fas fa-calendar-alt" style="color:#475569;"></i>
                        <span>Jadwal & Tahapan Kedatangan Alat</span>
                    </div>
                    <div id="modalToolName" style="font-size:14px;font-weight:700;color:#0f172a;margin-top:2px;"></div>
                    <div id="modalToolCode" style="font-size:11px;color:#64748b;font-family:ui-monospace,SFMono-Regular,Consolas,monospace;margin-top:1px;"></div>
                </div>
                <button type="button" onclick="closeStagesModal()" style="background:none;border:none;color:#94a3b8;cursor:pointer;padding:4px;font-size:14px;line-height:1;border-radius:4px;" onmouseover="this.style.color='#0f172a'" onmouseout="this.style.color='#94a3b8'">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            {{-- Summary Stats --}}
            <div style="padding:12px 18px;background:#ffffff;border-bottom:1px solid #f1f5f9;display:flex;gap:12px;">
                <div style="flex:1;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;padding:8px 12px;">
                    <div style="font-size:10.5px;color:#166534;font-weight:600;">Sudah Masuk</div>
                    <div id="modalTotalRec" style="font-size:14px;font-weight:700;color:#15803d;margin-top:1px;">0</div>
                </div>
                <div style="flex:1;background:#fffbeb;border:1px solid #fde68a;border-radius:6px;padding:8px 12px;">
                    <div style="font-size:10.5px;color:#92400e;font-weight:600;">Rencana Kedatangan</div>
                    <div id="modalTotalPlan" style="font-size:14px;font-weight:700;color:#b45309;margin-top:1px;">0</div>
                </div>
            </div>

            {{-- Timeline Stages List --}}
            <div id="modalStagesList" style="max-height:280px;overflow-y:auto;padding:6px 18px;">
                {{-- Injected dynamically --}}
            </div>

            {{-- Modal Footer --}}
            <div style="background:#f8fafc;padding:10px 18px;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;">
                <button type="button" onclick="closeStagesModal()" class="btn btn-secondary" style="height:32px;padding:0 14px;border-radius:6px;font-size:12px;font-weight:600;">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Hapus Kategori Alat --}}
    <div id="modalDeleteCategory" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.55);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:12px;width:100%;max-width:480px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1),0 8px 10px -6px rgba(0,0,0,0.1);overflow:hidden;">
            <form id="formDeleteCategory" method="POST" action="">
                @csrf
                @method('DELETE')
                
                {{-- Modal Header --}}
                <div style="padding:16px 20px;border-bottom:1px solid #fee2e2;background:#fef2f2;display:flex;align-items:center;justify-content:space-between;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:36px;height:36px;border-radius:50%;background:#fee2e2;color:#dc2626;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;">
                            <i class="fas fa-trash-can"></i>
                        </div>
                        <div>
                            <div class="fw-700" style="font-size:15px;color:#991b1b;">Hapus Kategori Alat</div>
                            <div class="text-muted" style="font-size:11.5px;">Hapus atau bersihkan kategori yang salah dimasukkan</div>
                        </div>
                    </div>
                    <button type="button" onclick="closeDeleteCategoryModal()" style="background:none;border:none;color:#94a3b8;font-size:18px;cursor:pointer;line-height:1;padding:4px;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div style="padding:18px 20px;">
                    <div style="margin-bottom:14px;padding:12px 14px;border-radius:8px;background:#f8fafc;border:1px solid #e2e8f0;">
                        <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:700;letter-spacing:0.5px;">Kategori yang Dipilih</div>
                        <div id="delCatName" style="font-size:15px;font-weight:700;color:#0f172a;margin-top:2px;">-</div>
                        <div id="delCatCountBadge" style="margin-top:6px;font-size:12px;color:#475569;">
                            <span class="badge badge-gray" id="delCatCountText">0 Alat</span>
                        </div>
                    </div>

                    {{-- Case 1: Kategori Kosong --}}
                    <div id="delEmptySection" style="display:none;">
                        <p style="font-size:13px;color:#475569;margin:0;line-height:1.5;">
                            Kategori ini belum memiliki alat kerja di dalamnya. Anda dapat langsung menghapusnya tanpa mempengaruhi data inventaris lainnya.
                        </p>
                    </div>

                    {{-- Case 2: Kategori ada isinya --}}
                    <div id="delHasItemsSection" style="display:none;">
                        <div style="padding:10px 12px;border-radius:6px;background:#fffbeb;border:1px solid #fef3c7;color:#92400e;font-size:12px;line-height:1.45;margin-bottom:14px;">
                            <i class="fas fa-triangle-exclamation me-1" style="color:#d97706;"></i>
                            Kategori ini masih memiliki <strong id="delCountHighlight">0</strong> alat kerja. Agar data inventaris tetap utuh, pilih kategori pengganti untuk memindahkan seluruh alat:
                        </div>

                        <div style="margin-bottom:12px;">
                            <label class="form-label" style="font-size:12px;font-weight:700;color:#334155;">Pindahkan Seluruh Alat ke Kategori:</label>
                            <select name="transfer_to_category_id" id="delTargetCatSelect" class="form-control" style="height:38px;border-radius:6px;font-size:13px;">
                                <option value="">— Pilih Kategori Tujuan —</option>
                                @foreach($filterCategories as $fCat)
                                <option value="{{ $fCat->id }}">{{ $fCat->name }}</option>
                                @endforeach
                            </select>
                            <div class="text-muted" style="font-size:11px;margin-top:4px;">Seluruh alat akan dialihkan ke kategori ini, lalu kategori yang salah akan dihapus.</div>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div style="background:#f8fafc;padding:12px 20px;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:8px;">
                    <button type="button" onclick="closeDeleteCategoryModal()" class="btn btn-secondary" style="height:36px;padding:0 14px;border-radius:6px;font-size:12.5px;font-weight:600;">
                        Batal
                    </button>
                    <button type="submit" id="delSubmitBtn" class="btn btn-danger" style="height:36px;padding:0 16px;border-radius:6px;font-size:12.5px;font-weight:600;display:inline-flex;align-items:center;gap:6px;">
                        <i class="fas fa-trash-can"></i> Hapus Kategori
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Hapus Kelompok Alat (Type) --}}
    <div id="modalDeleteGroup" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.55);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:12px;width:100%;max-width:480px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1),0 8px 10px -6px rgba(0,0,0,0.1);overflow:hidden;">
            <form id="formDeleteGroup" method="POST" action="{{ route('tools.delete-group') }}">
                @csrf
                <input type="hidden" name="category_id" id="groupDelCatId">
                <input type="hidden" name="type_name" id="groupDelTypeName">
                
                {{-- Modal Header --}}
                <div style="padding:16px 20px;border-bottom:1px solid #fee2e2;background:#fef2f2;display:flex;align-items:center;justify-content:space-between;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:36px;height:36px;border-radius:50%;background:#fee2e2;color:#dc2626;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;">
                            <i class="fas fa-trash-can"></i>
                        </div>
                        <div>
                            <div class="fw-700" style="font-size:15px;color:#991b1b;">Hapus Kelompok Alat</div>
                            <div class="text-muted" style="font-size:11.5px;">Hapus atau bersihkan kelompok yang salah dimasukkan</div>
                        </div>
                    </div>
                    <button type="button" onclick="closeDeleteGroupModal()" style="background:none;border:none;color:#94a3b8;font-size:18px;cursor:pointer;line-height:1;padding:4px;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div style="padding:18px 20px;">
                    <div style="margin-bottom:14px;padding:12px 14px;border-radius:8px;background:#f8fafc;border:1px solid #e2e8f0;">
                        <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:700;letter-spacing:0.5px;">Kategori: <span id="groupDelCatDisplay" style="color:#0f172a;">-</span></div>
                        <div style="font-size:16px;font-weight:700;color:#0f172a;margin-top:4px;" id="groupDelTypeDisplay">-</div>
                        <div style="margin-top:6px;font-size:12px;color:#475569;">
                            <span class="badge badge-gray" id="groupDelCountText">0 Alat</span>
                        </div>
                    </div>

                    <div style="margin-bottom:14px;">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#334155;margin-bottom:8px;">PILIH METODE PENGHAPUSAN:</label>
                        
                        <div style="display:flex;flex-direction:column;gap:10px;">
                            {{-- Option 1: Pindahkan ke kelompok lain --}}
                            <label style="display:flex;align-items:flex-start;gap:10px;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;cursor:pointer;background:#fff;" id="labelOptTransfer">
                                <input type="radio" name="action_type" value="transfer" checked onchange="toggleGroupAction(this.value)" style="margin-top:3px;">
                                <div style="flex:1;">
                                    <div style="font-size:13px;font-weight:700;color:#0f172a;">Pindahkan ke Kelompok Lain (Direkomendasikan)</div>
                                    <div style="font-size:11.5px;color:#64748b;margin-top:2px;">Ubah nama kelompok untuk seluruh alat ini ke kelompok lain (misal jika typo atau salah ketik).</div>
                                    
                                    <div id="targetTypeInputWrap" style="margin-top:10px;">
                                        <input type="text" name="target_type" id="groupTargetTypeInput" class="form-control" placeholder="Contoh: Genset, Molen Beton, Lainnya..." style="height:36px;border-radius:6px;font-size:13px;">
                                    </div>
                                </div>
                            </label>

                            {{-- Option 2: Hapus alat di dalamnya --}}
                            <label style="display:flex;align-items:flex-start;gap:10px;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;cursor:pointer;background:#fff;" id="labelOptDelete">
                                <input type="radio" name="action_type" value="delete_items" onchange="toggleGroupAction(this.value)" style="margin-top:3px;">
                                <div style="flex:1;">
                                    <div style="font-size:13px;font-weight:700;color:#dc2626;">Hapus Seluruh Alat di Kelompok Ini</div>
                                    <div style="font-size:11.5px;color:#64748b;margin-top:2px;">Hanya dapat dilakukan jika alat di kelompok ini tidak sedang dalam peminjaman aktif.</div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div style="background:#f8fafc;padding:12px 20px;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:8px;">
                    <button type="button" onclick="closeDeleteGroupModal()" class="btn btn-secondary" style="height:36px;padding:0 14px;border-radius:6px;font-size:12.5px;font-weight:600;">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-danger" style="height:36px;padding:0 16px;border-radius:6px;font-size:12.5px;font-weight:600;display:inline-flex;align-items:center;gap:6px;">
                        <i class="fas fa-trash-can"></i> Proses Hapus Kelompok
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function openDeleteCategoryModal(catId, catName, itemCount) {
            var modal = document.getElementById('modalDeleteCategory');
            var form = document.getElementById('formDeleteCategory');
            var nameEl = document.getElementById('delCatName');
            var countText = document.getElementById('delCatCountText');
            var emptySec = document.getElementById('delEmptySection');
            var hasItemsSec = document.getElementById('delHasItemsSection');
            var countHigh = document.getElementById('delCountHighlight');
            var targetSelect = document.getElementById('delTargetCatSelect');
            var submitBtn = document.getElementById('delSubmitBtn');

            form.action = "{{ url('/categories') }}/" + catId;
            nameEl.textContent = catName;
            countText.textContent = itemCount + ' Alat';

            if (targetSelect) {
                targetSelect.value = '';
                Array.from(targetSelect.options).forEach(function(opt) {
                    if (opt.value == catId) {
                        opt.style.display = 'none';
                        opt.disabled = true;
                    } else {
                        opt.style.display = '';
                        opt.disabled = false;
                    }
                });
            }

            if (itemCount > 0) {
                emptySec.style.display = 'none';
                hasItemsSec.style.display = 'block';
                countHigh.textContent = itemCount;
                submitBtn.innerHTML = '<i class="fas fa-right-left"></i> Pindahkan & Hapus';
                if (targetSelect) targetSelect.required = true;
            } else {
                emptySec.style.display = 'block';
                hasItemsSec.style.display = 'none';
                submitBtn.innerHTML = '<i class="fas fa-trash-can"></i> Ya, Hapus Kategori';
                if (targetSelect) targetSelect.required = false;
            }

            modal.style.display = 'flex';
        }

        function closeDeleteCategoryModal() {
            var modal = document.getElementById('modalDeleteCategory');
            if (modal) modal.style.display = 'none';
        }

        function openDeleteGroupModal(catId, catName, typeName, itemCount) {
            var modal = document.getElementById('modalDeleteGroup');
            document.getElementById('groupDelCatId').value = catId;
            document.getElementById('groupDelTypeName').value = typeName;
            document.getElementById('groupDelCatDisplay').textContent = catName;
            document.getElementById('groupDelTypeDisplay').textContent = typeName;
            document.getElementById('groupDelCountText').textContent = itemCount + ' Alat';
            document.getElementById('groupTargetTypeInput').value = '';

            modal.style.display = 'flex';
        }

        function closeDeleteGroupModal() {
            var modal = document.getElementById('modalDeleteGroup');
            if (modal) modal.style.display = 'none';
        }

        function toggleGroupAction(val) {
            var wrap = document.getElementById('targetTypeInputWrap');
            if (wrap) {
                wrap.style.display = val === 'transfer' ? 'block' : 'none';
            }
        }

        function openStagesFromBtn(btn) {
            try {
                var stages = JSON.parse(btn.getAttribute('data-stages') || '[]');
                var name = btn.getAttribute('data-name') || '-';
                var code = btn.getAttribute('data-sku') || '-';
                var unit = btn.getAttribute('data-unit') || 'unit';
                openStagesModal({ name: name, code: code, unit: unit, stages: stages });
            } catch(e) {
                console.error('Error parsing stages data:', e);
            }
        }

        function openStagesModal(data) {
            document.getElementById('modalToolName').textContent = data.name || '-';
            document.getElementById('modalToolCode').textContent = 'Kode: ' + (data.code || '-');

            var unit = data.unit || 'unit';
            var list = document.getElementById('modalStagesList');
            list.innerHTML = '';

            var stages = data.stages || [];
            var recQty = 0;
            var planQty = 0;

            stages.forEach(function(stg, idx) {
                var isReceived = (stg.status || 'received') === 'received';
                var qty = parseFloat(stg.qty || 0);
                if (isReceived) {
                    recQty += qty;
                } else {
                    planQty += qty;
                }

                var stageLabel = stg.stage || ('T' + (idx + 1));
                var statusLabel = isReceived ? 'Sudah Masuk' : 'Rencana';
                var statusBg = isReceived ? '#f0fdf4' : '#fffbeb';
                var statusBorder = isReceived ? '#bbf7d0' : '#fde68a';
                var statusColor = isReceived ? '#166534' : '#92400e';
                var icon = isReceived ? 'fa-check' : 'fa-clock';
                var iconColor = isReceived ? '#16a34a' : '#d97706';
                var dateFormatted = stg.date ? formatDate(stg.date) : null;

                var row = document.createElement('div');
                row.style.cssText = 'padding:10px 0;display:flex;align-items:flex-start;justify-content:space-between;gap:12px;border-bottom:1px solid #f1f5f9;';
                row.innerHTML = `
                    <div style="display:flex;align-items:flex-start;gap:9px;">
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:50%;background:${statusBg};color:${iconColor};border:1px solid ${statusBorder};font-size:9.5px;margin-top:1px;flex-shrink:0;">
                            <i class="fas ${icon}"></i>
                        </span>
                        <div>
                            <div style="display:flex;align-items:center;gap:6px;">
                                <span style="font-weight:700;font-size:12.5px;color:#0f172a;">${stageLabel}</span>
                                <span style="font-size:10px;font-weight:600;padding:1px 6px;border-radius:4px;background:${statusBg};color:${statusColor};border:1px solid ${statusBorder};">
                                    ${statusLabel}
                                </span>
                            </div>
                            ${dateFormatted ? `<div style="font-size:11px;color:#64748b;margin-top:2px;"><i class="far fa-calendar-alt" style="font-size:10px;margin-right:4px;"></i>${dateFormatted}</div>` : ''}
                            ${stg.notes ? `<div style="font-size:11px;color:#64748b;font-style:italic;margin-top:2px;"><i class="far fa-sticky-note" style="font-size:10px;margin-right:4px;"></i>${stg.notes}</div>` : ''}
                        </div>
                    </div>
                    <div style="text-align:right;white-space:nowrap;padding-top:1px;">
                        <div style="font-weight:700;font-size:13px;color:#0f172a;">${numberFormat(qty)} <span style="font-size:11px;font-weight:normal;color:#64748b;">${unit}</span></div>
                    </div>
                `;
                list.appendChild(row);
            });

            document.getElementById('modalTotalRec').textContent = numberFormat(recQty) + ' ' + unit;
            document.getElementById('modalTotalPlan').textContent = numberFormat(planQty) + ' ' + unit;

            document.getElementById('modalStages').style.display = 'flex';
        }

        function closeStagesModal() {
            var modal = document.getElementById('modalStages');
            if (modal) modal.style.display = 'none';
        }

        function formatDate(dStr) {
            try {
                var p = dStr.split('-');
                if (p.length === 3) return p[2] + '/' + p[1] + '/' + p[0];
                return dStr;
            } catch(e) {
                return dStr;
            }
        }

        function numberFormat(n) {
            return new Intl.NumberFormat('id-ID').format(n);
        }

        function initToolAccordion() {
            var hasSearch = {{ request('search') ? 'true' : 'false' }};
            var catToggleRows = document.querySelectorAll('.group-toggle');
            var subToggleRows = document.querySelectorAll('.subgroup-toggle');

            // 1. Kondisi awal saat pertama kali dibuka: tertutup sendiri secara default jika tidak sedang mencari
            if (!hasSearch) {
                catToggleRows.forEach(function (catRow) {
                    catRow.classList.add('collapsed');
                    var chev = catRow.querySelector('.group-chev');
                    if (chev) chev.style.transform = 'rotate(-90deg)';
                });

                subToggleRows.forEach(function (subRow) {
                    subRow.classList.add('collapsed');
                    var chev = subRow.querySelector('.subgroup-chev');
                    if (chev) chev.style.transform = 'rotate(-90deg)';
                });

                document.querySelectorAll('.group-rows').forEach(function (r) {
                    r.style.display = 'none';
                });
            } else {
                subToggleRows.forEach(function (subRow) {
                    var subKey = subRow.getAttribute('data-group');
                    var childRows = document.querySelectorAll('.' + subKey);
                    childRows.forEach(function (r) {
                        r.style.display = '';
                    });
                });
            }

            // 2. Level 1: Category Toggle (Kategori lain otomatis tertutup sendiri saat pindah kategori)
            catToggleRows.forEach(function (row) {
                var newRow = row.cloneNode(true);
                row.parentNode.replaceChild(newRow, row);

                var group = newRow.getAttribute('data-group');
                newRow.addEventListener('click', function (e) {
                    if (e.target.closest('.btn') || e.target.closest('a') || e.target.closest('button')) return;

                    var willOpen = newRow.classList.contains('collapsed');

                    if (willOpen) {
                        // Tutup kategori lainnya
                        document.querySelectorAll('.group-toggle').forEach(function (otherCat) {
                            if (otherCat !== newRow && !otherCat.classList.contains('collapsed')) {
                                otherCat.classList.add('collapsed');
                                var otherGroup = otherCat.getAttribute('data-group');
                                document.querySelectorAll('.' + otherGroup).forEach(function (r) {
                                    r.style.display = 'none';
                                });
                                var otherChev = otherCat.querySelector('.group-chev');
                                if (otherChev) otherChev.style.transform = 'rotate(-90deg)';
                            }
                        });

                        // Buka kategori yang diklik
                        newRow.classList.remove('collapsed');
                        var chev = newRow.querySelector('.group-chev');
                        if (chev) chev.style.transform = '';

                        // Tampilkan subgroup-toggle miliknya (item di dalam sub-kelompok tetap tertutup sampai sub-kelompok diklik)
                        document.querySelectorAll('.' + group).forEach(function (r) {
                            if (r.classList.contains('subgroup-toggle')) {
                                r.style.display = '';
                            } else if (r.classList.contains('subgroup-rows')) {
                                var subKey = null;
                                r.classList.forEach(function (cls) {
                                    if (cls.startsWith('sub-')) subKey = cls;
                                });
                                var subToggle = subKey ? document.querySelector('.subgroup-toggle[data-group="' + subKey + '"]') : null;
                                if (subToggle && !subToggle.classList.contains('collapsed')) {
                                    r.style.display = '';
                                } else {
                                    r.style.display = 'none';
                                }
                            }
                        });

                        // Jika hanya ada 1 sub-kelompok di kategori ini, otomatis buka agar data langsung terlihat
                        var subTogglesInCat = document.querySelectorAll('.' + group + '.subgroup-toggle');
                        if (subTogglesInCat.length === 1) {
                            var singleSub = subTogglesInCat[0];
                            singleSub.classList.remove('collapsed');
                            var singleSubChev = singleSub.querySelector('.subgroup-chev');
                            if (singleSubChev) singleSubChev.style.transform = '';
                            var singleSubKey = singleSub.getAttribute('data-group');
                            document.querySelectorAll('.' + singleSubKey).forEach(function (r) {
                                r.style.display = '';
                            });
                        }
                    } else {
                        // Tutup kategori ini
                        newRow.classList.add('collapsed');
                        var chev = newRow.querySelector('.group-chev');
                        if (chev) chev.style.transform = 'rotate(-90deg)';
                        document.querySelectorAll('.' + group).forEach(function (r) {
                            r.style.display = 'none';
                        });
                    }
                });
            });

            // 3. Level 2: Subgroup Toggle (Kelompok lain dalam kategori yang sama otomatis tertutup sendiri saat pindah kelompok)
            subToggleRows = document.querySelectorAll('.subgroup-toggle');
            subToggleRows.forEach(function (row) {
                var newSubRow = row.cloneNode(true);
                row.parentNode.replaceChild(newSubRow, row);

                var subKey = newSubRow.getAttribute('data-group');
                newSubRow.addEventListener('click', function (e) {
                    if (e.target.closest('.btn') || e.target.closest('a') || e.target.closest('button')) return;

                    var willOpen = newSubRow.classList.contains('collapsed');

                    if (willOpen) {
                        // Cari kategori induk
                        var parentCatClass = null;
                        newSubRow.classList.forEach(function (cls) {
                            if (cls.startsWith('group-cat-')) parentCatClass = cls;
                        });

                        // Tutup sub-kelompok lain di kategori yang sama
                        if (parentCatClass) {
                            document.querySelectorAll('.' + parentCatClass + '.subgroup-toggle').forEach(function (otherSub) {
                                if (otherSub !== newSubRow && !otherSub.classList.contains('collapsed')) {
                                    otherSub.classList.add('collapsed');
                                    var otherSubKey = otherSub.getAttribute('data-group');
                                    document.querySelectorAll('.' + otherSubKey).forEach(function (r) {
                                        r.style.display = 'none';
                                    });
                                    var otherChev = otherSub.querySelector('.subgroup-chev');
                                    if (otherChev) otherChev.style.transform = 'rotate(-90deg)';
                                }
                            });
                        }

                        // Buka sub-kelompok yang diklik
                        newSubRow.classList.remove('collapsed');
                        var chev = newSubRow.querySelector('.subgroup-chev');
                        if (chev) chev.style.transform = '';
                        document.querySelectorAll('.' + subKey).forEach(function (r) {
                            r.style.display = '';
                        });
                    } else {
                        // Tutup sub-kelompok ini
                        newSubRow.classList.add('collapsed');
                        var chev = newSubRow.querySelector('.subgroup-chev');
                        if (chev) chev.style.transform = 'rotate(-90deg)';
                        document.querySelectorAll('.' + subKey).forEach(function (r) {
                            r.style.display = 'none';
                        });
                    }
                });
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initToolAccordion);
        } else {
            initToolAccordion();
        }
    </script>
    @endpush
</x-app-layout>