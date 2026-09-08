<x-app-layout>
    <x-slot name="title">Laporan Diskrepansi (Selisih) Stok</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div class="breadcrumb">
            <a href="{{ route('reports.index') }}">Laporan</a>
            <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
            <span>Diskrepansi Stok</span>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-triangle-exclamation text-warning"></i>
            <span class="card-title">Laporan Selisih Stock Opname</span>
            <a href="{{ route('reports.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tgl Audit</th>
                        <th>Gudang</th>
                        <th>Material</th>
                        <th>Sistem</th>
                        <th>Fisik</th>
                        <th>Selisih</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $row)
                    @php $diff = $row->difference ?? $row['difference']; @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($row->opname_date ?? $row['opname_date'])->format('d/m/Y') }}</td>
                        <td>{{ $row->warehouse_name ?? $row['warehouse_name'] }}</td>
                        <td class="fw-600">{{ $row->material_name ?? $row['material_name'] }}</td>
                        <td>{{ number_format($row->system_quantity ?? $row['system_quantity'], 2) }}</td>
                        <td>{{ number_format($row->physical_quantity ?? $row['physical_quantity'], 2) }}</td>
                        <td class="{{ $diff > 0 ? 'text-success' : ($diff < 0 ? 'text-danger' : 'text-muted') }} fw-700">
                            {{ number_format($diff, 2) }}
                        </td>
                        <td>
                            @if($diff > 0)
                                <span class="badge badge-success">Surplus</span>
                            @elseif($diff < 0)
                                <span class="badge badge-danger">Defisit</span>
                            @else
                                <span class="badge badge-gray">Cocok</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted p-4">Tidak ada diskrepansi</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
