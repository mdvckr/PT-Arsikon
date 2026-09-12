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
                        <th style="width:40px;">#</th>
                        <th>Kode SKU</th>
                        <th>Nama Material & Model</th>
                        <th>Ukuran / Dimensi</th>
                        <th style="text-align:center;">Total Stok</th>
                        <th>Status Inventori</th>
                        <th>Tanggal Input</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categoriesData as $category)
                    <tr class="group-toggle" data-group="group-cat-{{ $category->id }}" style="background:#f8fafc !important;cursor:pointer;">
                        <td colspan="8" style="padding:10px 20px !important;">
                            <div class="flex items-center justify-between" style="gap:12px;flex-wrap:nowrap;">
                                <div class="flex items-center" style="gap:8px;min-width:0;">
                                    <i class="fas fa-chevron-down group-chev" style="font-size:11px;color:#64748b;transition:transform .2s;" aria-hidden="true"></i>
                                    <span class="fw-700" style="font-size:13px;text-transform:uppercase;letter-spacing:.03em;color:#0f172a;white-space:nowrap;">
                                        {{ $category->name }}
                                    </span>
                                    <span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;white-space:nowrap;">
                                        {{ $category->materials->count() }} material
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

                    @foreach($category->materials as $m)
                    <tr class="group-rows group-cat-{{ $category->id }}">
                        <td class="text-muted" style="text-align:center;">{{ $loop->iteration }}</td>
                        <td><code style="background:#f1f5f9;padding:2px 7px;border-radius:5px;font-size:12px;">{{ $m->sku }}</code></td>
                        <td>
                            <div class="fw-600" style="color:#0f172a;">{{ $m->name }}</div>
                            @if($m->description)
                            <div class="text-muted" style="font-size:11.5px;">{{ Str::limit($m->description, 40) }}</div>
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
                        @php 
                            $totalStock = $m->inventories->sum('quantity'); 
                            $activeInventories = $m->inventories->where('quantity', '>', 0);
                        @endphp
                        <td style="text-align:center;">
                            <span style="font-weight:700;font-size:15px;color:#0f172a;">{{ number_format($totalStock, 0, ',', '.') }}</span>
                            @if($m->unit?->abbreviation || $m->unit?->name)
                            <span class="text-muted" style="font-size:11px;"> {{ $m->unit->abbreviation ?? $m->unit->name }}</span>
                            @endif
                        </td>
                        <td>
                            @if($totalStock > 0)
                                <span class="badge bg-success-subtle text-success" style="font-size:11px;" title="{{ $activeInventories->count() }} Gudang/Lokasi">
                                    <i class="fas fa-warehouse me-1"></i> Tersedia di {{ $activeInventories->count() }} Inventori
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning" style="font-size:11px;">
                                    <i class="fas fa-box-open me-1"></i> Belum ada stok
                                </span>
                            @endif
                        </td>
                        <td style="font-size:12px;color:#64748b;">
                            <div>{{ $m->created_at ? $m->created_at->format('d M Y') : '-' }}</div>
                            <div style="font-size:10.5px;" class="text-muted">{{ $m->created_at ? $m->created_at->format('H:i') : '' }}</div>
                        </td>

                        <td>
                            <div class="flex gap-1">
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
                    @empty
                    <tr>
                        <td colspan="8">
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
            var toggleRows = document.querySelectorAll('.group-toggle');
            toggleRows.forEach(function (row) {
                // Hapus event listener lama dengan cloneNode
                var newRow = row.cloneNode(true);
                row.parentNode.replaceChild(newRow, row);

                var group = newRow.getAttribute('data-group');
                newRow.addEventListener('click', function (e) {
                    // Mencegah trigger jika yang diklik adalah tombol Tambah
                    if (e.target.closest('.btn')) return;

                    var collapsed = newRow.classList.toggle('collapsed');
                    var targetRows = document.querySelectorAll('.' + group);
                    targetRows.forEach(function (r) {
                        r.style.display = collapsed ? 'none' : '';
                    });
                    var chev = newRow.querySelector('.group-chev');
                    if (chev) {
                        chev.style.transform = collapsed ? 'rotate(-90deg)' : '';
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
