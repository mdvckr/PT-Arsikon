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

    <div class="card mb-4">
        <div class="card-body" style="padding:16px 20px;">
            <form method="GET" class="flex gap-3" style="flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:200px;">
                    <label class="form-label">Cari Alat</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nama atau kode alat...">
                </div>
                <div style="min-width:200px;">
                    <label class="form-label">Kategori</label>
                    <select name="category_id" class="form-control" onchange="this.form.submit()">
                        <option value="">Semua Kategori</option>
                        @foreach($filterCategories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="data-table mb-0">
                <thead>
                    <tr>
                        <th style="width:40px;">No</th>
                        <th style="width:220px;">Alat</th>
                        <th>Merk</th>
                        <th style="text-align:center;">Total</th>
                        <th style="text-align:center;">Tersedia</th>
                        <th style="text-align:center;">Dipinjam</th>
                        <th style="text-align:center;">Maintenance</th>
                        <th style="text-align:center;">Rusak</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr class="group-toggle" data-group="group-cat-{{ $category->id }}" style="background:#f8fafc !important;cursor:pointer;">
                        <td colspan="9" style="padding:10px 20px !important;">
                            <div class="flex items-center justify-between" style="gap:12px;flex-wrap:nowrap;">
                                <div class="flex items-center" style="gap:8px;min-width:0;">
                                    <i class="fas fa-chevron-down group-chev" style="font-size:11px;color:#64748b;transition:transform .2s;" aria-hidden="true"></i>
                                    <span class="fw-700" style="font-size:13px;text-transform:uppercase;letter-spacing:.03em;color:#0f172a;white-space:nowrap;">{{ $category->name }}</span>
                                    <span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;white-space:nowrap;">{{ $category->tools->count() }} alat</span>
                                </div>
                                <div class="flex items-center" style="gap:16px;flex-shrink:0;">
                                    <div class="flex items-center gap-3" style="font-size:12px;color:#64748b;white-space:nowrap;">
                                        <span title="Total Stok">{{ $category->tools->sum('stock_total') }} unit</span>
                                        <span style="color:#cbd5e1;">|</span>
                                        <span title="Dipinjam">{{ $category->tools->sum('stock_borrowed') }} dipinjam</span>
                                    </div>
                                    @can('create tools')
                                    <a href="{{ route('tools.create', ['category_id' => $category->id]) }}" class="btn btn-sm btn-primary" title="Tambah Alat pada kategori {{ $category->name }}" onclick="event.stopPropagation();">
                                        <i class="fas fa-plus"></i> Tambah
                                    </a>
                                    @endcan
                                </div>
                            </div>
                        </td>
                    </tr>
                    @foreach($category->tools as $tool)
                    <tr class="group-rows group-cat-{{ $category->id }}">
                        <td style="text-align:center;color:#94a3b8;">{{ $loop->iteration }}</td>
                        <td>
                            <div class="fw-600" style="font-size:13px;">{{ $tool->name }}</div>
                            <code style="background:#f1f5f9;padding:1px 6px;border-radius:4px;font-size:11px;">{{ $tool->code }}</code>
                        </td>
                        <td style="font-size:13px;">{{ $tool->brand ?? '-' }}</td>
                        <td style="text-align:center;">
                            <span style="font-weight:700;font-size:15px;color:#0f172a;">{{ $tool->stock_total }}</span>
                        </td>
                        <td style="text-align:center;">
                            @if($tool->stock_available > 0)
                            <span class="badge badge-success">{{ $tool->stock_available }}</span>
                            @else
                            <span class="badge" style="background:#f1f5f9;color:#94a3b8;">0</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            @if($tool->stock_borrowed > 0)
                            <span class="badge badge-primary">{{ $tool->stock_borrowed }}</span>
                            @else
                            <span style="color:#cbd5e1;font-size:12px;">0</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            @if($tool->stock_maintenance > 0)
                            <span class="badge badge-warning">{{ $tool->stock_maintenance }}</span>
                            @else
                            <span style="color:#cbd5e1;font-size:12px;">0</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            @if($tool->stock_damaged > 0)
                            <span class="badge badge-danger">{{ $tool->stock_damaged }}</span>
                            @else
                            <span style="color:#cbd5e1;font-size:12px;">0</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex gap-1">
                                @can('edit tools')
                                <a href="{{ route('tools.edit', $tool) }}" class="btn btn-sm btn-warning btn-icon" title="Edit & Kelola Stok"><i class="fas fa-pen"></i></a>
                                @endcan
                                @can('delete tools')
                                <form method="POST" action="{{ route('tools.destroy', $tool) }}" onsubmit="return confirm('Hapus alat {{ addslashes($tool->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger btn-icon" title="Hapus"><i class="fas fa-trash"></i></button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    @empty
                    <tr><td colspan="9" class="text-center text-muted p-4">Belum ada data alat. Klik <strong>Tambah Alat</strong> untuk memulai.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
        function initToolAccordion() {
            var toggleRows = document.querySelectorAll('.group-toggle');
            toggleRows.forEach(function (row) {
                var newRow = row.cloneNode(true);
                row.parentNode.replaceChild(newRow, row);

                var group = newRow.getAttribute('data-group');
                newRow.addEventListener('click', function (e) {
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
            document.addEventListener('DOMContentLoaded', initToolAccordion);
        } else {
            initToolAccordion();
        }
    </script>
    @endpush
</x-app-layout>