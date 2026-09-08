<x-app-layout>
    <x-slot name="title">Detail Supplier: {{ $supplier->name }}</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('suppliers.index') }}">Supplier</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>{{ $supplier->name }}</span>
    </div>

    <div class="grid" style="grid-template-columns:1fr 1fr;gap:24px;align-items:start;">

        <div class="card">
            <div class="card-header">
                <i class="fas fa-truck text-primary"></i> <span class="card-title">Informasi Profil</span>
                @can('edit suppliers')
                <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-sm btn-warning"><i class="fas fa-pen"></i> Edit</a>
                @endcan
            </div>
            <div class="card-body">
                @php
                    $rows = [
                        ['Nama Supplier', $supplier->name],
                        ['Telepon', $supplier->phone ?? '-'],
                        ['Email', $supplier->email ?? '-'],
                        ['Contact Person', $supplier->contact_person ?? '-'],
                        ['Alamat Lengkap', $supplier->address ?? '-'],
                    ];
                @endphp
                @foreach($rows as [$label, $value])
                <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid #f1f5f9;">
                    <span class="text-muted fw-500" style="width:140px;flex-shrink:0;">{{ $label }}</span>
                    <span class="fw-600 text-end">{{ $value }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="fas fa-clock-rotate-left text-primary"></i> <span class="card-title">Riwayat Penerimaan (10 Terakhir)</span>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tgl Terima</th>
                            <th>No. Tanda Terima</th>
                            <th>Gudang</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($supplier->goodsReceipts as $receipt)
                        <tr>
                            <td class="text-muted">{{ $receipt->received_at ? \Carbon\Carbon::parse($receipt->received_at)->format('d/m/Y') : '-' }}</td>
                            <td>
                                <a href="{{ route('goods-receipts.show', $receipt) }}" class="text-primary fw-600">
                                    {{ $receipt->receipt_number }}
                                </a>
                            </td>
                            <td>{{ $receipt->warehouse?->name ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted p-4">Belum ada riwayat transaksi</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
