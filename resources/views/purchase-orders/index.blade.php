<x-app-layout>
    <x-slot name="title">Purchase Order (PO)</x-slot>

    <div class="flex justify-between items-center mb-4">
        <div>
            <h2 style="font-size:20px;font-weight:700;color:#0f172a;">Purchase Order (PO)</h2>
            <p class="text-muted" style="font-size:13px;">Manajemen Pesanan Pembelian ke Supplier</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('purchase-receipts.index') }}" class="btn btn-secondary">
                <i class="fas fa-receipt me-1"></i> Nota Pembelian Harian
            </a>
            <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Buat PO Baru
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4" style="background:#ecfdf5;border:1px solid #10b981;color:#065f46;padding:12px 16px;border-radius:8px;">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header flex justify-between items-center">
            <span class="card-title">Daftar Purchase Order</span>
            <form method="GET" class="flex gap-2 ms-auto">
                <select name="status" class="form-control" style="width:160px;padding:6px 10px;font-size:13px;" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Dikirim ke Supplier</option>
                    <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Diterima</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor PO..." class="form-control" style="width:200px;padding:6px 10px;font-size:13px;">
                <button class="btn btn-secondary btn-sm"><i class="fas fa-search"></i></button>
            </form>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>No. PO</th>
                        <th>Supplier</th>
                        <th>Tgl Order</th>
                        <th>Target Pengiriman</th>
                        <th>Total Nilai PO</th>
                        <th>Nota Terbayar/Tercatat</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pos as $i => $po)
                    <tr>
                        <td class="text-muted">{{ $pos->firstItem() + $i }}</td>
                        <td><code>{{ $po->po_number }}</code></td>
                        <td><strong>{{ $po->supplier?->name ?? '-' }}</strong></td>
                        <td>{{ $po->order_date ? $po->order_date->format('d M Y') : '-' }}</td>
                        <td>{{ $po->expected_delivery ? $po->expected_delivery->format('d M Y') : '-' }}</td>
                        <td><strong class="text-primary">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</strong></td>
                        <td>
                            @php
                                $totalNota = $po->receipts ? $po->receipts->sum('total_amount') : 0;
                            @endphp
                            <span class="text-success font-semibold">Rp {{ number_format($totalNota, 0, ',', '.') }}</span>
                            <span class="text-muted text-xs">({{ $po->receipts ? $po->receipts->count() : 0 }} nota)</span>
                        </td>
                        <td><span class="badge badge-{{ $po->status_color }}">{{ $po->status_label }}</span></td>
                        <td class="flex gap-1">
                            <a href="{{ route('purchase-orders.show', $po) }}" class="btn btn-sm btn-secondary btn-icon" title="Detail PO">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('purchase-receipts.create', ['po_id' => $po->id]) }}" class="btn btn-sm btn-outline-primary btn-icon" title="Tambah Nota Harian">
                                <i class="fas fa-receipt"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">Belum ada Purchase Order.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pos->hasPages())
        <div class="card-footer">
            {{ $pos->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
