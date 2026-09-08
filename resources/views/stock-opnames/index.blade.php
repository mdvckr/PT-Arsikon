<x-app-layout>
    <x-slot name="title">Stock Opname</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="fw-700" style="font-size:20px;color:#0f172a;">Stock Opname (Audit Fisik)</h2>
            <p class="text-muted" style="font-size:13px;margin-top:2px;">Audit stok fisik dan penyesuaian selisih inventori</p>
        </div>
        @can('create stock opname')
        <a href="{{ route('stock-opnames.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Buat Stock Opname
        </a>
        @endcan
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No. Dokumen</th>
                        <th>Gudang</th>
                        <th>Tanggal Audit</th>
                        <th>Status</th>
                        <th>Dibuat Oleh</th>
                        <th>Disetujui Oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($opnames as $op)
                    <tr>
                        <td>
                            <a href="{{ route('stock-opnames.show', $op) }}" class="text-primary fw-600">
                                {{ $op->opname_number }}
                            </a>
                        </td>
                        <td>{{ $op->warehouse?->name ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($op->opname_date)->format('d/m/Y') }}</td>
                        <td>
                            @if($op->status === 'open')
                                <span class="badge badge-warning"><i class="fas fa-clock"></i> Open</span>
                            @elseif($op->status === 'approved')
                                <span class="badge badge-success"><i class="fas fa-check-double"></i> Disetujui</span>
                            @else
                                <span class="badge badge-danger"><i class="fas fa-xmark"></i> Ditolak</span>
                            @endif
                        </td>
                        <td>{{ $op->creator?->name ?? '-' }}</td>
                        <td>{{ $op->approver?->name ?? '-' }}</td>
                        <td>
                            <a href="{{ route('stock-opnames.show', $op) }}" class="btn btn-sm btn-secondary btn-icon" title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted p-4">Belum ada data stock opname</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($opnames->hasPages())
        <div style="padding:12px;border-top:1px solid #f1f5f9;">{{ $opnames->links() }}</div>
        @endif
    </div>
</x-app-layout>
