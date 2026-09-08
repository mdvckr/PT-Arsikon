<x-app-layout>
    <x-slot name="title">Laporan Mutasi Stok</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div class="breadcrumb">
            <a href="{{ route('reports.index') }}">Laporan</a>
            <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
            <span>Mutasi Stok</span>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-arrows-up-down text-primary"></i>
            <span class="card-title">Riwayat Pergerakan Stok</span>
            <a href="{{ route('reports.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Material</th>
                        <th>Gudang</th>
                        <th>Tipe</th>
                        <th>Qty</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $row)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($row->created_at ?? $row['created_at'])->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="fw-600">{{ $row->material_name ?? $row['material_name'] }}</div>
                        </td>
                        <td>{{ $row->warehouse_name ?? $row['warehouse_name'] }}</td>
                        <td>
                            @php $type = $row->type ?? $row['type']; @endphp
                            @if(in_array($type, ['in', 'receipt', 'opname_adjust_up']))
                                <span class="badge badge-success"><i class="fas fa-arrow-up"></i> Masuk ({{ $type }})</span>
                            @else
                                <span class="badge badge-danger"><i class="fas fa-arrow-down"></i> Keluar ({{ $type }})</span>
                            @endif
                        </td>
                        <td class="fw-700">{{ number_format(abs($row->quantity ?? $row['quantity']), 2) }}</td>
                        <td class="text-muted" style="font-size:12.5px;">{{ $row->notes ?? $row['notes'] ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted p-4">Tidak ada data mutasi pada periode ini</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
