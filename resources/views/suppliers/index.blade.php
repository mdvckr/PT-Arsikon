<x-app-layout>
    <x-slot name="title">Data Supplier</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="fw-700" style="font-size:20px;color:#0f172a;">Data Supplier</h2>
            <p class="text-muted" style="font-size:13px;margin-top:2px;">Kelola informasi kontak dan detail pemasok</p>
        </div>
        @can('create suppliers')
        <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Supplier
        </a>
        @endcan
    </div>

    <div class="card mb-4">
        <div class="card-body" style="padding:16px 20px;">
            <form method="GET" class="flex gap-3" style="flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:200px;">
                    <label class="form-label">Cari Supplier</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nama, telepon, email...">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cari</button>
                    <a href="{{ route('suppliers.index') }}" class="btn btn-secondary"><i class="fas fa-rotate-left"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Supplier</th>
                        <th>Kontak</th>
                        <th>Contact Person</th>
                        <th>Riwayat Transaksi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $i => $sup)
                    <tr>
                        <td class="text-muted">{{ $suppliers->firstItem() + $i }}</td>
                        <td>
                            <div class="fw-600">{{ $sup->name }}</div>
                            @if($sup->address)
                            <div class="text-muted" style="font-size:11px;max-width:250px;">{{ Str::limit($sup->address, 50) }}</div>
                            @endif
                        </td>
                        <td style="font-size:12.5px;">
                            @if($sup->phone)<div><i class="fas fa-phone text-muted" style="width:16px;"></i> {{ $sup->phone }}</div>@endif
                            @if($sup->email)<div><i class="fas fa-envelope text-muted" style="width:16px;"></i> {{ $sup->email }}</div>@endif
                        </td>
                        <td>{{ $sup->contact_person ?? '-' }}</td>
                        <td><span class="badge badge-primary">{{ $sup->goods_receipts_count }} Penerimaan</span></td>
                        <td>
                            <div class="flex gap-1">
                                <a href="{{ route('suppliers.show', $sup) }}" class="btn btn-sm btn-secondary btn-icon" title="Detail"><i class="fas fa-eye"></i></a>
                                @can('edit suppliers')
                                <a href="{{ route('suppliers.edit', $sup) }}" class="btn btn-sm btn-warning btn-icon" title="Edit"><i class="fas fa-pen"></i></a>
                                @endcan
                                @can('delete suppliers')
                                <form method="POST" action="{{ route('suppliers.destroy', $sup) }}" onsubmit="return confirm('Hapus supplier {{ addslashes($sup->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger btn-icon" title="Hapus"><i class="fas fa-trash"></i></button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted p-4">Belum ada data supplier</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($suppliers->hasPages())
        <div style="padding:12px;border-top:1px solid #f1f5f9;">{{ $suppliers->links() }}</div>
        @endif
    </div>
</x-app-layout>
