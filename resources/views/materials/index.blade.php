<x-app-layout>
    <x-slot name="title">Data Material</x-slot>

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-4" style="flex-wrap:wrap;gap:12px;">
        <div>
           
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
                    <tr>
                        <th style="width:130px;white-space:nowrap;padding:7px 10px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px;color:#64748b;background:#fafafa;border-bottom:1px solid #e2e8f0;">Kode SKU</th>
                        <th style="padding:7px 10px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px;color:#64748b;background:#fafafa;border-bottom:1px solid #e2e8f0;">Nama Material</th>
                        <th style="padding:7px 10px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px;color:#64748b;background:#fafafa;border-bottom:1px solid #e2e8f0;">Supplier</th>
                        <th style="padding:7px 10px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px;color:#64748b;background:#fafafa;border-bottom:1px solid #e2e8f0;">Ukuran</th>
                        <th style="padding:7px 10px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px;color:#64748b;background:#fafafa;border-bottom:1px solid #e2e8f0;">Tahapan Masuk</th>
                        <th style="white-space:nowrap;padding:7px 10px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px;color:#64748b;background:#fafafa;border-bottom:1px solid #e2e8f0;">Tgl Input</th>
                        <th style="text-align:center;white-space:nowrap;padding:7px 10px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px;color:#64748b;background:#fafafa;border-bottom:1px solid #e2e8f0;">Total Stok</th>
                        <th style="text-align:center;white-space:nowrap;padding:7px 10px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px;color:#64748b;background:#fafafa;border-bottom:1px solid #e2e8f0;">Pemakaian</th>
                        <th style="text-align:center;white-space:nowrap;padding:7px 10px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px;color:#64748b;background:#fafafa;border-bottom:1px solid #e2e8f0;">Stok Sisa</th>
                        <th style="text-align:center;width:95px;white-space:nowrap;padding:7px 10px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px;color:#64748b;background:#fafafa;border-bottom:1px solid #e2e8f0;">Aksi</th>
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
                        <td colspan="10" style="padding:9px 16px !important;">
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
                                    <a href="{{ route('materials.create', ['category_id' => $category->id]) }}" style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:5px;font-size:11.5px;font-weight:600;background:#ffffff;border:1px solid #cbd5e1;color:#2563eb;text-decoration:none;box-shadow:0 1px 2px rgba(0,0,0,0.04);" title="Tambah Material pada {{ $category->name }}" onclick="event.stopPropagation();">
                                        <i class="fas fa-plus" style="font-size:9.5px;"></i> Tambah
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
                        <td colspan="10" style="padding:7px 16px 7px 34px !important;">
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
                        <td style="width:130px;white-space:nowrap;padding:10px 14px;vertical-align:middle;">
                            <span style="font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;font-size:12px;color:#334155;font-weight:600;letter-spacing:0.02em;">
                                {{ $m->sku ?? '-' }}
                            </span>
                        </td>
                        <td style="padding:10px 14px;vertical-align:middle;">
                            <a href="{{ route('materials.show', $m) }}" style="color:#0f172a;font-weight:600;text-decoration:none;font-size:13px;display:inline-block;">
                                {{ $m->name }}
                            </a>
                            @if($m->brand)
                            <div class="text-muted" style="font-size:11px;margin-top:2px;">
                                Merek: {{ $m->brand }}
                            </div>
                            @endif
                        </td>
                        <td style="padding:10px 14px;font-size:12.5px;color:#334155;vertical-align:middle;">
                            {{ $m->supplier?->name ?? $m->supplier_name ?? '-' }}
                        </td>
                        <td style="padding:10px 14px;font-size:12.5px;color:#334155;vertical-align:middle;">
                            {{ $m->size ?: '-' }}
                        </td>
                        <td style="padding:10px 14px;white-space:nowrap;vertical-align:middle;">
                            @if(!empty($m->incoming_stages) && count($m->incoming_stages) > 0)
                                @php
                                    $stages = collect($m->incoming_stages);
                                    $stageCount = $stages->count();
                                    $receivedStages = $stages->where('status', 'received');
                                    $plannedStages = $stages->where('status', 'planned');
                                    $recCount = $receivedStages->count();
                                    $planCount = $plannedStages->count();
                                    $recQty = (float) $receivedStages->sum('qty');
                                    $planQty = (float) $plannedStages->sum('qty');
                                    $unitAbbr = $m->unit?->abbreviation ?? $m->unit?->name ?? '';
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
                                          title="{{ $stg['stage'] ?? 'T1' }}: {{ number_format((float)($stg['qty'] ?? 0), 0, ',', '.') }} {{ $unitAbbr }} ({{ $statusLabel }}){{ $stageDate ? ' · '.$stageDate : '' }}{{ !empty($stg['notes']) ? ' · '.$stg['notes'] : '' }}">
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
                                            data-name="{{ $m->name }}"
                                            data-sku="{{ $m->sku ?? '-' }}"
                                            data-unit="{{ $unitAbbr }}"
                                            data-stages='@json($stages)'
                                            style="display:inline-flex;align-items:center;gap:5px;padding:2.5px 8px;border-radius:5px;font-size:10.5px;font-weight:500;background:{{ $btnBg }};border:1px solid {{ $btnBorder }};color:{{ $btnColor }};cursor:pointer;line-height:1.3;transition:all .15s ease;"
                                            onmouseover="this.style.opacity='0.85';"
                                            onmouseout="this.style.opacity='1';">
                                        @if($allReceived)
                                            <i class="fas fa-check-circle" style="font-size:9.5px;color:#16a34a;"></i>
                                            <span><strong style="font-weight:700;">{{ $stageCount }}/{{ $stageCount }}</strong> Masuk</span>
                                            <span style="color:#86efac;">•</span>
                                            <span style="font-weight:700;color:#15803d;">{{ number_format($recQty, 0, ',', '.') }} {{ $unitAbbr }}</span>
                                        @elseif($recCount === 0)
                                            <i class="fas fa-clock" style="font-size:9.5px;color:#d97706;"></i>
                                            <span><strong style="font-weight:700;">{{ $stageCount }}</strong> Tahap Rencana</span>
                                            <span style="color:#cbd5e1;">•</span>
                                            <span style="font-weight:600;color:#92400e;">{{ number_format($planQty, 0, ',', '.') }} {{ $unitAbbr }}</span>
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
                        <td style="padding:10px 14px;font-size:12px;color:#64748b;white-space:nowrap;vertical-align:middle;">
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
                        <td style="text-align:center;padding:10px 14px;vertical-align:middle;">
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
                        <td style="text-align:center;padding:10px 14px;vertical-align:middle;">
                            <span style="font-size:13px;color:#334155;">{{ number_format($pemakaian, 0, ',', '.') }}</span>
                            @if($m->unit?->abbreviation || $m->unit?->name)
                            <span class="text-muted" style="font-size:11px;">{{ $m->unit->abbreviation ?? $m->unit->name }}</span>
                            @endif
                        </td>
                        <td style="text-align:center;padding:10px 14px;vertical-align:middle;">
                            <div style="font-weight:700;font-size:13.5px;color:#0f172a;">
                                {{ number_format($stockSisa, 0, ',', '.') }}
                                @if($m->unit?->abbreviation || $m->unit?->name)
                                <span class="text-muted" style="font-size:11px;font-weight:normal;">{{ $m->unit->abbreviation ?? $m->unit->name }}</span>
                                @endif
                            </div>
                            @if($stockSisa > 0)
                                <span class="badge" style="background:#f8fafc;color:#475569;border:1px solid #e2e8f0;font-size:10px;padding:2px 7px;border-radius:4px;font-weight:600;margin-top:2px;display:inline-block;">
                                    Tersedia
                                </span>
                            @else
                                <span class="badge" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;font-size:10px;padding:2px 7px;border-radius:4px;font-weight:600;margin-top:2px;display:inline-block;">
                                    Habis
                                </span>
                            @endif
                        </td>

                        <td style="text-align:center;padding:10px 14px;vertical-align:middle;">
                            <div class="flex items-center justify-center" style="gap:4px;">
                                <a href="{{ route('materials.show', $m) }}" class="btn btn-sm btn-light border" style="width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center;color:#475569;border-radius:6px;background:#ffffff;" title="Detail Material">
                                    <i class="fas fa-eye" style="font-size:11px;"></i>
                                </a>
                                @can('edit materials')
                                <a href="{{ route('materials.edit', $m) }}" class="btn btn-sm btn-light border" style="width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center;color:#475569;border-radius:6px;background:#ffffff;" title="Edit Material">
                                    <i class="fas fa-pen" style="font-size:11px;"></i>
                                </a>
                                @endcan
                                @can('delete materials')
                                <form method="POST" action="{{ route('materials.destroy', $m) }}"
                                    onsubmit="return confirm('Hapus material {{ addslashes($m->name) }}?')" style="display:inline-block;margin:0;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light border" style="width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center;color:#dc2626;border-radius:6px;background:#ffffff;" title="Hapus Material">
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
                        <td colspan="10" style="padding:40px 20px;text-align:center;">
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

    {{-- Modal Detail Tahapan Kedatangan --}}
    <div id="stagesModalBackdrop" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.45);backdrop-filter:blur(3px);z-index:99999;align-items:center;justify-content:center;padding:16px;" onclick="if(event.target === this) closeStagesModal();">
        <div style="background:#ffffff;border-radius:10px;border:1px solid #e2e8f0;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);width:100%;max-width:440px;overflow:hidden;">
            {{-- Modal Header --}}
            <div style="background:#f8fafc;padding:14px 18px;border-bottom:1px solid #e2e8f0;display:flex;align-items:flex-start;justify-content:space-between;gap:12px;">
                <div>
                    <div style="display:flex;align-items:center;gap:6px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.04em;">
                        <i class="fas fa-calendar-alt" style="color:#475569;"></i>
                        <span>Jadwal & Tahapan Kedatangan</span>
                    </div>
                    <div id="modalMaterialName" style="font-size:14px;font-weight:700;color:#0f172a;margin-top:2px;"></div>
                    <div id="modalMaterialSku" style="font-size:11px;color:#64748b;font-family:ui-monospace,SFMono-Regular,Consolas,monospace;margin-top:1px;"></div>
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

    @push('scripts')
    <script>
        function openStagesFromBtn(btn) {
            try {
                var stages = JSON.parse(btn.getAttribute('data-stages') || '[]');
                var name = btn.getAttribute('data-name') || '-';
                var sku = btn.getAttribute('data-sku') || '-';
                var unit = btn.getAttribute('data-unit') || '';
                openStagesModal({ name: name, sku: sku, unit: unit, stages: stages });
            } catch(e) {
                console.error('Error parsing stages data:', e);
            }
        }

        function openStagesModal(data) {
            document.getElementById('modalMaterialName').textContent = data.name || '-';
            document.getElementById('modalMaterialSku').textContent = 'SKU: ' + (data.sku || '-');

            var unit = data.unit || '';
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

            var backdrop = document.getElementById('stagesModalBackdrop');
            backdrop.style.display = 'flex';
        }

        function closeStagesModal() {
            var backdrop = document.getElementById('stagesModalBackdrop');
            if (backdrop) backdrop.style.display = 'none';
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeStagesModal();
        });

        function numberFormat(val) {
            return new Intl.NumberFormat('id-ID').format(val);
        }

        function formatDate(dateStr) {
            if (!dateStr) return '';
            var parts = dateStr.split('-');
            if (parts.length === 3) {
                return parts[2] + '/' + parts[1] + '/' + parts[0];
            }
            return dateStr;
        }

        function initMaterialAccordion() {
            // Level 1: Category Toggle
            var toggleRows = document.querySelectorAll('.group-toggle');
            toggleRows.forEach(function (row) {
                var newRow = row.cloneNode(true);
                row.parentNode.replaceChild(newRow, row);

                var group = newRow.getAttribute('data-group');
                newRow.addEventListener('click', function (e) {
                    if (e.target.closest('.btn') || e.target.closest('a') || e.target.closest('button')) return;

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
                    if (e.target.closest('.btn') || e.target.closest('a') || e.target.closest('button')) return;

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
