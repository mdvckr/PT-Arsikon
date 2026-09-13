<x-app-layout>
    <x-slot name="title">Inventori Gudang</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="fw-700" style="font-size:20px;color:#0f172a;">Inventori Gudang</h2>
            <p class="text-muted" style="font-size:13px;margin-top:2px;">Stok material per gudang secara real-time</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card mb-4">
        <div class="card-body" style="padding:16px 20px;">
            <form method="GET" class="flex gap-3" style="flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:200px;">
                    <label class="form-label">Cari Material</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nama atau kode SKU...">
                </div>
                <div style="min-width:180px;">
                    <label class="form-label">Kategori</label>
                    <select name="category_id" class="form-control" onchange="this.form.submit()">
                        <option value="">Semua Kategori</option>
                        @foreach($filterCategories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="min-width:160px;">
                    <label class="form-label">Level Stok</label>
                    <select name="stock_level" class="form-control" onchange="this.form.submit()">
                        <option value="">Semua Level</option>
                        <option value="low" {{ request('stock_level') === 'low' ? 'selected' : '' }}>Stok Rendah</option>
                        <option value="out" {{ request('stock_level') === 'out' ? 'selected' : '' }}>Stok Habis</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                    <a href="{{ route('inventory.index') }}" class="btn btn-secondary"><i class="fas fa-rotate-left"></i></a>
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
                        <th>Material</th>
                        <th>Kategori</th>
                        <th>Gudang</th>
                        <th style="text-align:center;">Stok Saat Ini</th>
                        <th>Min. Stok</th>
                        <th>Status</th>
                        <th>Update Terakhir</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categoriesData as $category)
                    @php
                        $materialsWithInv = $category->materials->filter(fn($m) => $m->inventories->isNotEmpty());
                    @endphp
                    @if($materialsWithInv->isNotEmpty())
                    <tr class="group-toggle" data-group="group-cat-{{ $category->id }}" style="background:#f8fafc !important;cursor:pointer;">
                        <td colspan="9" style="padding:10px 20px !important;">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center" style="gap:8px;">
                                    <i class="fas fa-chevron-down group-chev" style="font-size:11px;color:#64748b;transition:transform .2s;"></i>
                                    <span class="fw-700" style="font-size:13px;text-transform:uppercase;color:#0f172a;">{{ $category->name }}</span>
                                    <span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;">{{ $materialsWithInv->count() }} Material</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @foreach($materialsWithInv as $tool)
                        @foreach($tool->inventories as $inv)
                        <tr class="group-rows group-cat-{{ $category->id }}">
                            <td class="text-muted" style="text-align:center;">{{ $loop->parent->iteration }}</td>
                            <td>
                                <div class="fw-600" style="color:#0f172a;">{{ $tool->name }}</div>
                                <code style="font-size:11px;color:#64748b;">{{ $tool->sku ?? '-' }}</code>
                            </td>
                            <td>{{ $category->name }}</td>
                            <td>{{ $inv->warehouse?->name ?? '-' }}</td>
                            <td style="text-align:center;">
                                <span class="fw-700" style="font-size:15px;color:#0f172a;">{{ number_format($inv->quantity,0,',','.') }}</span>
                                <span class="text-muted" style="font-size:11px;"> {{ $tool->unit?->abbreviation ?? $tool->unit?->name }}</span>
                            </td>
                            <td>{{ number_format($inv->min_stock,0,',','.') }}</td>
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
                            <td>
                                <div class="flex gap-1">
                                    <a href="{{ route('inventory.edit', $inv) }}" class="btn btn-sm btn-warning btn-icon" title="Edit"><i class="fas fa-pen"></i></a>
                                    <form method="POST" action="{{ route('inventory.destroy', $inv) }}" style="display:inline" onsubmit="return confirm('Hapus inventori ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger btn-icon" title="Hapus"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @endforeach
                    @endif
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted p-4">
                            <i class="fas fa-boxes-stacked"></i>
                            <h3>Belum Ada Data Inventori</h3>
                            <p>Data inventori akan muncul setelah ada penerimaan barang.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
        function initInventoryAccordion() {
            var toggleRows = document.querySelectorAll('.group-toggle');
            toggleRows.forEach(function (row) {
                var newRow = row.cloneNode(true);
                row.parentNode.replaceChild(newRow, row);
                var group = newRow.getAttribute('data-group');
                newRow.addEventListener('click', function (e) {
                    if (e.target.closest('.btn')) return;
                    var collapsed = newRow.classList.toggle('collapsed');
                    var targetRows = document.querySelectorAll('.' + group);
                    targetRows.forEach(function (r) { r.style.display = collapsed ? 'none' : ''; });
                    var chev = newRow.querySelector('.group-chev');
                    if (chev) { chev.style.transform = collapsed ? 'rotate(-90deg)' : ''; }
                });
            });
        }
        document.addEventListener('DOMContentLoaded', initInventoryAccordion);
    </script>
    @endpush
</x-app-layout>
