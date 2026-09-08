<x-app-layout>
    <x-slot name="title">Laporan Stok</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div class="breadcrumb">
            <a href="{{ route('reports.index') }}">Laporan</a>
            <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
            <span>Laporan Stok</span>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-layer-group text-primary"></i>
            <span class="card-title">Rekap Stok Material</span>
            <a href="{{ route('reports.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Material</th>
                        <th>Kategori</th>
                        <th>Gudang</th>
                        <th>Stok</th>
                        <th>Satuan</th>
                        <th>Min. Stok</th>
                        <th>Nilai Stok (Rp)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $row)
                    <tr>
                        <td>
                            <div class="fw-600">{{ $row->material_name ?? $row['material_name'] ?? '-' }}</div>
                            <div class="text-muted" style="font-size:11.5px;">{{ $row->material_code ?? $row['material_code'] ?? '' }}</div>
                        </td>
                        <td>{{ $row->category_name ?? $row['category_name'] ?? '-' }}</td>
                        <td>{{ $row->warehouse_name ?? $row['warehouse_name'] ?? '-' }}</td>
                        <td class="fw-700">{{ number_format($row->quantity ?? $row['quantity'] ?? 0, 2) }}</td>
                        <td>{{ $row->unit_abbr ?? $row['unit_abbr'] ?? '-' }}</td>
                        <td>{{ number_format($row->min_stock ?? $row['min_stock'] ?? 0, 2) }}</td>
                        <td>Rp {{ number_format(($row->quantity ?? $row['quantity'] ?? 0) * ($row->unit_price ?? $row['unit_price'] ?? 0), 0, ',', '.') }}</td>
                        <td>
                            @php $qty = $row->quantity ?? $row['quantity'] ?? 0; $min = $row->min_stock ?? $row['min_stock'] ?? 0; @endphp
                            @if($qty <= 0)
                                <span class="badge badge-danger">Habis</span>
                            @elseif($qty <= $min)
                                <span class="badge badge-warning">Rendah</span>
                            @else
                                <span class="badge badge-success">Normal</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="fas fa-layer-group"></i>
                                <h3>Tidak Ada Data</h3>
                                <p>Tidak ada data stok untuk filter yang dipilih.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
