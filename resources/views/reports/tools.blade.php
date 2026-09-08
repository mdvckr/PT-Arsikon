<x-app-layout>
    <x-slot name="title">Laporan Status Alat</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div class="breadcrumb">
            <a href="{{ route('reports.index') }}">Laporan</a>
            <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
            <span>Status Alat</span>
        </div>
    </div>

    <div class="grid grid-2" style="gap:24px;margin-bottom:24px;">
        <div class="card bg-primary text-white" style="padding:24px;border-radius:12px;background:linear-gradient(135deg,#3b82f6,#2563eb);">
            <div style="font-size:14px;opacity:0.9;">Total Alat Terdaftar</div>
            <div style="font-size:36px;font-weight:800;">{{ $data->count() }}</div>
        </div>
        <div class="card bg-success text-white" style="padding:24px;border-radius:12px;background:linear-gradient(135deg,#10b981,#059669);">
            <div style="font-size:14px;opacity:0.9;">Tersedia (Available)</div>
            <div style="font-size:36px;font-weight:800;">{{ $data->where('status', 'available')->count() }}</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-screwdriver-wrench text-primary"></i>
            <span class="card-title">Daftar Status Alat Saat Ini</span>
            <a href="{{ route('reports.index') }}" class="btn btn-sm btn-secondary ms-auto">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Alat</th>
                        <th>Kategori</th>
                        <th>Kondisi</th>
                        <th>Status Pemakaian</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $condColors = ['good'=>'text-success', 'damaged'=>'text-danger', 'under_maintenance'=>'text-warning'];
                        $condLabels = ['good'=>'Baik', 'damaged'=>'Rusak', 'under_maintenance'=>'Perbaikan'];
                        $statColors = ['available'=>'badge-success', 'in_use'=>'badge-primary', 'under_maintenance'=>'badge-warning'];
                        $statLabels = ['available'=>'Tersedia', 'in_use'=>'Dipinjam', 'under_maintenance'=>'Maintenance'];
                    @endphp
                    @forelse($data as $row)
                    @php
                        $cond = $row->condition ?? $row['condition'];
                        $stat = $row->status ?? $row['status'];
                    @endphp
                    <tr>
                        <td><code>{{ $row->code ?? $row['code'] }}</code></td>
                        <td class="fw-600">{{ $row->name ?? $row['name'] }}</td>
                        <td>{{ $row->category_name ?? $row['category_name'] ?? '-' }}</td>
                        <td class="fw-600 {{ $condColors[$cond] ?? '' }}">{{ $condLabels[$cond] ?? $cond }}</td>
                        <td>
                            <span class="badge {{ $statColors[$stat] ?? 'badge-gray' }}">
                                {{ $statLabels[$stat] ?? $stat }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted p-4">Tidak ada alat terdaftar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
