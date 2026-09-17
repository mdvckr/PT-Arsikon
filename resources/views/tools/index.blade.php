<x-app-layout>
    <x-slot name="title">Data Alat</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="fw-700" style="font-size:20px;color:#0f172a;">Data Inventaris Alat</h2>
            <p class="text-muted" style="font-size:13px;margin-top:2px;">Kelola inventaris alat kerja dan stok pemakaian per kategori</p>
        </div>
        @can('create tools')
        <a href="{{ route('tools.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Alat
        </a>
        @endcan
    </div>

    {{-- Filter --}}
    <div class="card mb-4">
        <div class="card-body" style="padding:16px 20px;">
            <form method="GET" class="flex gap-3" style="flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:200px;">
                    <label class="form-label">Cari Alat</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Nama, kode alat, merk, kelompok, atau ukuran...">
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
                        <th>Kode Alat</th>
                        <th>Nama Alat & Model</th>
                        <th>Merk & Spesifikasi</th>
                        <th>Keterangan</th>
                        <th>Tgl Input</th>
                        <th style="text-align:center;">Total Stock</th>
                        <th style="text-align:center;">Dipinjam</th>
                        <th style="text-align:center;">Stock Sisa</th>
                        <th style="text-align:center;width:95px;">Aksi</th>
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
                    <tr class="group-toggle" data-group="group-cat-{{ $category->id }}" style="background:#f1f5f9 !important;cursor:pointer;">
                        <td colspan="10" style="padding:10px 20px !important;">
                            <div class="flex items-center justify-between" style="gap:12px;flex-wrap:nowrap;">
                                <div class="flex items-center" style="gap:8px;min-width:0;">
                                    <i class="fas fa-chevron-down group-chev" style="font-size:11px;color:#64748b;transition:transform .2s;" aria-hidden="true"></i>
                                    <span class="fw-700" style="font-size:13px;text-transform:uppercase;letter-spacing:.03em;color:#0f172a;white-space:nowrap;">
                                        {{ $category->name }}
                                    </span>
                                    <span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;white-space:nowrap;">
                                        {{ $totalTools }} alat
                                    </span>
                                    <span class="badge" style="background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;white-space:nowrap;font-size:10px;">
                                        {{ $totalGroups }} kelompok
                                    </span>
                                </div>
                                <div class="flex items-center" style="gap:16px;flex-shrink:0;">
                                    @can('create tools')
                                    <a href="{{ route('tools.create', ['category_id' => $category->id]) }}" style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:5px;font-size:11.5px;font-weight:600;background:#ffffff;border:1px solid #cbd5e1;color:#2563eb;text-decoration:none;box-shadow:0 1px 2px rgba(0,0,0,0.04);" title="Tambah Alat pada {{ $category->name }}" onclick="event.stopPropagation();">
                                        <i class="fas fa-plus" style="font-size:9.5px;"></i> Tambah
                                    </a>
                                    @endcan
                                </div>
                            </div>
                        </td>
                    </tr>

                    @foreach($typeGroups as $typeName => $toolsInType)
                    @php $subKey = 'sub-' . $category->id . '-' . Str::slug($typeName); @endphp
                    {{-- Level 2: Sub-Group Header (Kelompok Alat / Type) --}}
                    <tr class="group-rows group-cat-{{ $category->id }} subgroup-toggle" data-group="{{ $subKey }}" style="background:#f8fafc !important;cursor:pointer;border-left:3px solid #3b82f6;">
                        <td colspan="10" style="padding:8px 20px 8px 40px !important;">
                            <div class="flex items-center justify-between" style="gap:8px;">
                                <div class="flex items-center" style="gap:7px;">
                                    <i class="fas fa-chevron-down subgroup-chev" style="font-size:10px;color:#94a3b8;transition:transform .2s;" aria-hidden="true"></i>
                                    <i class="fas fa-tools" style="font-size:11px;color:#3b82f6;"></i>
                                    <span class="fw-600" style="font-size:12.5px;color:#334155;">
                                        {{ $typeName }}
                                    </span>
                                    <span class="badge" style="background:#e0e7ff;color:#4338ca;border:1px solid #c7d2fe;font-size:10px;">
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
                    <tr class="group-rows group-cat-{{ $category->id }} subgroup-rows {{ $subKey }}" style="border-bottom:1px solid #f1f5f9;">
                        <td class="text-muted" style="text-align:center;font-size:12px;padding:10px;vertical-align:middle;">{{ $loop->iteration }}</td>
                        <td style="padding:10px 14px;vertical-align:middle;"><span style="font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;background:#f8fafc;padding:2px 6px;border-radius:4px;font-size:11.5px;font-weight:500;border:1px solid #e2e8f0;color:#475569;">{{ $tool->code }}</span></td>
                        <td style="padding:10px 14px;vertical-align:middle;">
                            <div class="fw-600" style="color:#0f172a;font-size:13px;">{{ $tool->name }}</div>
                            @if($tool->type)
                            <div class="text-muted" style="font-size:11px;margin-top:2px;">{{ $tool->type }}</div>
                            @endif
                        </td>
                        <td style="padding:10px 14px;vertical-align:middle;">
                            <div>
                                @if($tool->brand)
                                <span class="fw-600" style="font-size:12.5px;color:#334155;">{{ $tool->brand }}</span>
                                @endif
                                @if($tool->size)
                                <span class="badge" style="font-size:10.5px;font-weight:600;background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;margin-left:4px;">
                                    {{ $tool->size }}
                                </span>
                                @elseif(!$tool->brand)
                                <span class="text-muted" style="font-size:12px;">-</span>
                                @endif
                            </div>
                        </td>
                        <td style="padding:10px 14px;max-width:280px;min-width:180px;vertical-align:middle;">
                            @if($tool->notes)
                            <div style="font-size:12px;color:#334155;line-height:1.4;">{{ $tool->notes }}</div>
                            @else
                            <span class="text-muted" style="font-size:12px;">-</span>
                            @endif
                        </td>
                        <td style="padding:10px 14px;font-size:12px;color:#64748b;white-space:nowrap;vertical-align:middle;">
                            <div>{{ $tool->created_at ? $tool->created_at->format('d/m/Y') : '-' }}</div>
                        </td>


                        <td style="text-align:center;padding:10px 14px;vertical-align:middle;">
                            <span style="font-weight:600;font-size:13.5px;color:#0f172a;">{{ number_format($tool->stock_total, 0, ',', '.') }}</span>
                            <span class="text-muted" style="font-size:11px;"> unit</span>
                        </td>
                        <td style="text-align:center;padding:10px 14px;vertical-align:middle;">
                            @if($tool->stock_borrowed > 0)
                                <span style="font-weight:600;font-size:13.5px;color:#e11d48;">{{ number_format($tool->stock_borrowed, 0, ',', '.') }}</span>
                            @else
                                <span class="text-muted" style="font-size:13px;">0</span>
                            @endif
                            <span class="text-muted" style="font-size:11px;"> unit</span>
                        </td>
                        <td style="text-align:center;padding:10px 14px;vertical-align:middle;">
                            <div style="font-weight:700;font-size:13.5px;color:#0f172a;">
                                {{ number_format($tool->stock_available, 0, ',', '.') }}
                                <span class="text-muted" style="font-size:11px;font-weight:normal;"> unit</span>
                            </div>
                            @if($tool->stock_available > 0)
                                <span style="display:inline-block;margin-top:3px;padding:1px 8px;border-radius:12px;font-size:10.5px;font-weight:600;background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;">
                                    Tersedia
                                </span>
                            @else
                                <span style="display:inline-block;margin-top:3px;padding:1px 8px;border-radius:12px;font-size:10.5px;font-weight:600;background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;">
                                    Habis
                                </span>
                            @endif
                        </td>

                        <td style="text-align:center;padding:10px 14px;vertical-align:middle;">
                            <div class="flex items-center justify-center" style="gap:4px;">
                                <a href="{{ route('tools.show', $tool) }}" class="btn btn-sm btn-light border" style="width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center;color:#475569;border-radius:6px;background:#ffffff;" title="Detail Alat">
                                    <i class="fas fa-eye" style="font-size:11px;"></i>
                                </a>
                                @can('edit tools')
                                <a href="{{ route('tools.edit', $tool) }}" class="btn btn-sm btn-light border" style="width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center;color:#475569;border-radius:6px;background:#ffffff;" title="Edit Alat">
                                    <i class="fas fa-pen" style="font-size:11px;"></i>
                                </a>
                                @endcan
                                @can('delete tools')
                                <form method="POST" action="{{ route('tools.destroy', $tool) }}"
                                    onsubmit="return confirm('Hapus alat {{ addslashes($tool->name) }}?')" style="display:inline-block;margin:0;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light border" style="width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center;color:#dc2626;border-radius:6px;background:#ffffff;" title="Hapus Alat">
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
                        <td colspan="10">
                            <div class="empty-state">
                                <i class="fas fa-tools"></i>
                                <h3>Belum Ada Alat</h3>
                                <p>Tambahkan alat kerja pertama untuk memulai inventaris.</p>
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
        function initToolAccordion() {
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

            // Level 2: Subgroup (Kelompok Alat) Toggle - Buka dan Tutup
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
            document.addEventListener('DOMContentLoaded', initToolAccordion);
        } else {
            initToolAccordion();
        }
    </script>
    @endpush
</x-app-layout>