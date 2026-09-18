<x-app-layout>
    <x-slot name="title">Penerimaan Barang</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="fw-700" style="font-size:20px;color:#0f172a;">Penerimaan Barang Supplier</h2>
            <p class="text-muted" style="font-size:13px;margin-top:2px;">
                Catat penerimaan barang dari supplier ke gudang pusat maupun proyek
            </p>
        </div>
        @can('create goods receipts')
        <a href="{{ route('goods-receipts.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Catat Penerimaan
        </a>
        @endcan
    </div>

    {{-- Filter --}}
    <div class="card mb-4">
        <div class="card-body" style="padding:16px 20px;">
            <form method="GET" class="flex gap-3" style="flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:200px;">
                    <label class="form-label">Cari No., Invoice, atau Supplier</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="form-control" placeholder="Cari...">
                </div>
                @if(isset($warehouses) && $warehouses->count() > 1)
                <div style="min-width:200px;">
                    <label class="form-label">Gudang</label>
                    <select name="warehouse_id" class="form-control">
                        <option value="">Semua Gudang</option>
                        @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ (string)($warehouseId) === (string)$wh->id ? 'selected' : '' }}>
                            {{ $wh->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div style="min-width:160px;">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Dikonfirmasi</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cari</button>
                    <a href="{{ route('goods-receipts.index') }}" class="btn btn-secondary">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No. Tanda Terima</th>
                        <th>Supplier</th>
                        <th>Gudang</th>
                        <th>Tgl. Terima</th>
                        <th>Invoice</th>
                        <th>PO</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receipts as $r)
                    <tr>
                        <td>
                            <a href="{{ route('goods-receipts.show', $r) }}" class="text-primary fw-600">
                                {{ $r->receipt_number }}
                            </a>
                        </td>
                        <td>{{ $r->supplier?->name ?? '—' }}</td>
                        <td>
                            <span style="font-size:12.5px;">{{ $r->warehouse?->name ?? '—' }}</span>
                            @if($r->warehouse?->is_central)
                            <span class="badge badge-primary" style="font-size:10px;padding:2px 6px;margin-left:4px;">Pusat</span>
                            @else
                            <span class="badge badge-warning" style="font-size:10px;padding:2px 6px;margin-left:4px;">Proyek</span>
                            @endif
                        </td>
                        <td class="text-muted">
                            {{ $r->receipt_date ? \Carbon\Carbon::parse($r->receipt_date)->format('d/m/Y') : '—' }}
                        </td>
                        <td class="text-muted">{{ $r->invoice_number ?? '—' }}</td>
                        <td class="text-muted" style="font-size:12px;">
                            {{ $r->purchaseOrder?->po_number ?? '—' }}
                        </td>
                        <td>
                            @if($r->status === 'confirmed')
                                <span class="badge badge-success"><i class="fas fa-check"></i> Dikonfirmasi</span>
                            @else
                                <span class="badge badge-warning"><i class="fas fa-clock"></i> Draft</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('goods-receipts.show', $r) }}"
                                class="btn btn-sm btn-secondary btn-icon" title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="fas fa-truck-ramp-box"></i>
                                <h3>Belum Ada Penerimaan</h3>
                                <p>Catat penerimaan barang pertama dari supplier.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($receipts->hasPages())
        <div style="padding:16px 20px;border-top:1px solid #f1f5f9;">
            {{ $receipts->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
