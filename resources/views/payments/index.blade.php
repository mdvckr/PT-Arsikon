<x-app-layout>
    <x-slot name="title">Pembayaran PO</x-slot>

    <div class="flex justify-between items-center mb-4">
        <div>
            <h2 style="font-size:20px;font-weight:700;color:#0f172a;">Pembayaran (Payment)</h2>
            <p style="font-size:13px;color:#64748b;">Kelola status pembayaran faktur/PO</p>
        </div>
        <a href="{{ route('payments.create') }}" class="btn btn-primary">+ Catat Pembayaran</a>
    </div>

    <!-- Filter Status -->
    <div class="card mb-4" style="padding: 12px 16px;">
        <form method="GET" action="{{ route('payments.index') }}" class="flex items-center gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No Pembayaran / PO..." class="form-input" style="max-width: 250px;">
            <select name="status" class="form-select" style="max-width: 200px;" onchange="this.form.submit()">
                <option value="">-- Semua Status --</option>
                <option value="draft" {{ request('status')=='draft' ? 'selected':'' }}>Draft</option>
                <option value="pending" {{ request('status')=='pending' ? 'selected':'' }}>Menunggu Verifikasi</option>
                <option value="verified" {{ request('status')=='verified' ? 'selected':'' }}>Terverifikasi</option>
                <option value="rejected" {{ request('status')=='rejected' ? 'selected':'' }}>Ditolak</option>
            </select>
            <button type="submit" class="btn btn-secondary">Filter</button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('payments.index') }}" class="btn btn-ghost" style="color: #64748b;">Reset</a>
            @endif
        </form>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>No Pembayaran</th>
                        <th>No PO</th>
                        <th>Supplier</th>
                        <th>Metode</th>
                        <th>Jumlah</th>
                        <th>Tgl Pembayaran</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $p)
                        <tr>
                            <td><strong>{{ $p->payment_number }}</strong></td>
                            <td>
                                @if($p->purchaseOrder)
                                    <a href="{{ route('purchase-orders.show', $p->purchaseOrder) }}" style="color:#0284c7;font-weight:600;">
                                        {{ $p->purchaseOrder->po_number }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $p->purchaseOrder->supplier->name ?? '-' }}</td>
                            <td><span class="badge badge-secondary">{{ strtoupper($p->payment_method ?? 'TRANSFER') }}</span></td>
                            <td style="font-weight:600;color:#0f172a;">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                            <td>{{ $p->payment_date ? $p->payment_date->format('d/m/Y') : '-' }}</td>
                            <td>
                                @php
                                    $badgeClass = match($p->status) {
                                        'verified' => 'badge-success',
                                        'pending'  => 'badge-warning',
                                        'rejected' => 'badge-danger',
                                        default    => 'badge-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">
                                    {{ $p->status_label }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('payments.show', $p) }}" class="btn btn-sm btn-secondary">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center;padding:2rem;color:#94a3b8;">
                                Belum ada data pembayaran.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
            <div style="padding: 12px 16px;">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
