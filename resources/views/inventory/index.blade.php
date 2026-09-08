<x-app-layout>
    <x-slot name="title">Inventori Gudang</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="fw-700" style="font-size:20px;">Inventori Gudang</h2>
            <p class="text-muted" style="font-size:13px;margin-top:2px;">Stok material per gudang secara real-time</p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body" style="padding:16px 20px;">
            <form method="GET" class="flex gap-3" style="flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:200px;">
                    <label class="form-label">Cari Material</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nama atau kode...">
                </div>
                <div style="min-width:160px;">
                    <label class="form-label">Level Stok</label>
                    <select name="stock_level" class="form-control" onchange="this.form.submit()">
                        <option value="">Semua</option>
                        <option value="low" {{ request('stock_level') === 'low' ? 'selected' : '' }}>Stok Rendah</option>
                        <option value="out" {{ request('stock_level') === 'out' ? 'selected' : '' }}>Stok Habis</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cari</button>
                    <a href="{{ route('inventory.index') }}" class="btn btn-secondary"><i class="fas fa-rotate-left"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Material</th>
                        <th>Kategori</th>
                        <th>Gudang</th>
                        <th>Stok Saat Ini</th>
                        <th>Min. Stok</th>
                        <th>Status</th>
                        <th>Terakhir Diperbarui</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventories as $inv)
                    <tr>
                        <td>
                            <div class="fw-600">{{ $inv->material?->name }}</div>
                            <div class="text-muted" style="font-size:11.5px;">{{ $inv->material?->sku }}</div>
                        </td>
                        <td>{{ $inv->material?->category?->name ?? '-' }}</td>
                        <td>{{ $inv->warehouse?->name ?? '-' }}</td>
                        <td>
                            <span class="fw-700" style="font-size:15px;">
                                {{ number_format($inv->quantity, 2) }}
                            </span>
                            <span class="text-muted" style="font-size:12px;"> {{ $inv->material?->unit?->abbreviation }}</span>
                        </td>
                        <td>{{ number_format($inv->min_stock, 2) }}</td>
                        <td>
                            @if($inv->quantity <= 0)
                                <span class="badge badge-danger"><i class="fas fa-exclamation"></i> Habis</span>
                            @elseif($inv->quantity <= $inv->min_stock)
                                <span class="badge badge-warning"><i class="fas fa-triangle-exclamation"></i> Rendah</span>
                            @else
                                <span class="badge badge-success"><i class="fas fa-check"></i> Normal</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $inv->updated_at?->diffForHumans() }}</td>
                        <td>
                            <a href="{{ route('inventory.show', $inv) }}" class="btn btn-sm btn-secondary btn-icon">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="fas fa-layer-group"></i>
                                <h3>Inventori Kosong</h3>
                                <p>Lakukan penerimaan barang untuk mengisi inventori.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($inventories->hasPages())
        <div style="padding:16px 20px;border-top:1px solid #f1f5f9;">{{ $inventories->links() }}</div>
        @endif
    </div>
</x-app-layout>
