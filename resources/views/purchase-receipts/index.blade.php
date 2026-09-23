<x-app-layout>
    <x-slot name="title">Nota Pembelian Harian</x-slot>

    <div class="flex justify-between items-center mb-4">
        <div>
            <h2 style="font-size:20px;font-weight:700;color:#0f172a;">Nota Pembelian Harian</h2>
            <p class="text-muted" style="font-size:13px;">Pencatatan nota-nota fisik pembelian barang/material harian (Hari 1, Hari 2, dst.)</p>
        </div>
        <a href="{{ route('purchase-receipts.create') }}" class="btn btn-primary">
            <i class="fas fa-receipt me-1"></i> Catat Nota Baru
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4 flex items-center justify-between" style="background:#ecfdf5;border:1px solid #10b981;color:#065f46;padding:12px 16px;border-radius:8px;">
            <span><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</span>
        </div>
    @endif

    <div class="card">
        <div class="card-header flex flex-wrap gap-3 items-center justify-between">
            <span class="card-title">Daftar Nota Pembelian</span>
            <form method="GET" class="flex gap-2 ms-auto flex-wrap">
                <select name="po_id" class="form-control" style="width:200px;padding:6px 10px;font-size:13px;" onchange="this.form.submit()">
                    <option value="">-- Semua PO --</option>
                    @foreach($pos as $po)
                        <option value="{{ $po->id }}" {{ request('po_id') == $po->id ? 'selected' : '' }}>
                            {{ $po->po_number }} - {{ $po->supplier?->name ?? 'Supplier' }}
                        </option>
                    @endforeach
                </select>
                <select name="payment_status" class="form-control" style="width:160px;padding:6px 10px;font-size:13px;" onchange="this.form.submit()">
                    <option value="">-- Status Bayar --</option>
                    <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>⏳ Belum Dibayar</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>✅ Sudah Dibayar</option>
                </select>
                <input type="date" name="date" value="{{ request('date') }}" class="form-control" style="width:160px;padding:6px 10px;font-size:13px;" onchange="this.form.submit()">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor nota / supplier..." class="form-control" style="width:200px;padding:6px 10px;font-size:13px;">
                <button class="btn btn-secondary btn-sm"><i class="fas fa-search"></i></button>
                @if(request('search') || request('po_id') || request('date') || request('payment_status'))
                    <a href="{{ route('purchase-receipts.index') }}" class="btn btn-light btn-sm" title="Reset Filter"><i class="fas fa-undo"></i></a>
                @endif
            </form>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tgl Nota</th>
                        <th>No. Nota</th>
                        <th>PO / Proyek</th>
                        <th>Supplier / Toko</th>
                        <th>Status Pembayaran</th>
                        <th>Jumlah Item</th>
                        <th>Total Pembelian</th>
                        <th>Bukti Nota</th>
                        <th>Dicatat Oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receipts as $i => $receipt)
                    <tr>
                        <td class="text-muted">{{ $receipts->firstItem() + $i }}</td>
                        <td><strong>{{ $receipt->receipt_date ? $receipt->receipt_date->format('d M Y') : '-' }}</strong></td>
                        <td><code>{{ $receipt->receipt_number ?? '-' }}</code></td>
                        <td>
                            @if($receipt->purchaseOrder)
                                <a href="{{ route('purchase-orders.show', $receipt->purchaseOrder) }}" class="text-primary font-semibold" style="text-decoration:underline;">
                                    {{ $receipt->purchaseOrder->po_number }}
                                </a>
                            @elseif($receipt->project_name || $receipt->project)
                                <span>{{ $receipt->project_name ?? $receipt->project?->name }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td><strong>{{ $receipt->supplier_name ?? $receipt->supplier?->name ?? $receipt->purchaseOrder?->supplier?->name ?? '-' }}</strong></td>
                        <td>
                            <span class="badge badge-{{ $receipt->status_color }}">
                                {{ $receipt->status_label }}
                            </span>
                        </td>
                        <td><span class="badge badge-secondary">{{ $receipt->items->count() }} item</span></td>
                        <td><strong class="text-success">Rp {{ number_format($receipt->total_amount, 0, ',', '.') }}</strong></td>
                        <td>
                            @if($receipt->image_path)
                                <a href="{{ asset('storage/' . $receipt->image_path) }}" target="_blank" class="btn btn-xs btn-outline-info" style="font-size:11px;padding:2px 6px;">
                                    <i class="fas fa-file-image me-1"></i> Lihat Nota
                                </a>
                            @else
                                <span class="text-muted" style="font-size:12px;">Tanpa Foto</span>
                            @endif
                        </td>
                        <td class="text-muted" style="font-size:12px;">{{ $receipt->creator?->name ?? '-' }}</td>
                        <td>
                            <a href="{{ route('purchase-receipts.show', $receipt) }}" class="btn btn-sm btn-secondary btn-icon" title="Detail Nota">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">Belum ada pencatatan nota pembelian harian.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($receipts->hasPages())
        <div class="card-footer">
            {{ $receipts->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
