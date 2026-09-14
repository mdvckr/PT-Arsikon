<x-app-layout>
    <x-slot name="title">Laporan Status Alat</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div class="breadcrumb">
            <a href="{{ route('reports.index') }}">Laporan</a>
            <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
            <span>Status Alat</span>
        </div>
        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-secondary">
            Kembali
        </a>
    </div>

    <div class="grid grid-2" style="gap:16px;margin-bottom:20px;">
        <div class="card" style="padding:18px 20px;border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;">
            <div style="font-size:12px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.04em;">Total Alat Terdaftar</div>
            <div style="font-size:26px;font-weight:700;color:#0f172a;margin-top:4px;">
                {{ $data->count() }} <span style="font-size:13px;font-weight:500;color:#64748b;">Unit</span>
            </div>
        </div>
        <div class="card" style="padding:18px 20px;border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;">
            <div style="font-size:12px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.04em;">Alat Tersedia (Siap Pakai)</div>
            <div style="font-size:26px;font-weight:700;color:#0f172a;margin-top:4px;">
                {{ $data->where('status', 'available')->count() }} <span style="font-size:13px;font-weight:500;color:#64748b;">Unit</span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header" style="display:flex;align-items:center;gap:10px;padding:16px 20px;">
            <div style="width:32px;height:32px;border-radius:6px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#475569;flex-shrink:0;">
                <i class="fas fa-toolbox" style="font-size:13px;"></i>
            </div>
            <span class="card-title" style="margin:0;font-size:15px;">Daftar Status Alat</span>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:130px;">Kode</th>
                        <th>Nama Alat</th>
                        <th>Kategori</th>
                        <th>Kondisi Fisik</th>
                        <th>Status Pemakaian</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $condLabels = ['good'=>'Baik', 'damaged'=>'Rusak', 'under_maintenance'=>'Perbaikan'];
                        $statLabels = ['available'=>'Tersedia', 'in_use'=>'Dipinjam', 'under_maintenance'=>'Maintenance'];
                    @endphp
                    @forelse($data as $row)
                    @php
                        $cond = $row->condition ?? $row['condition'] ?? 'good';
                        $stat = $row->status ?? $row['status'] ?? 'available';
                    @endphp
                    <tr>
                        <td><code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:12px;">{{ $row->code ?? $row['code'] }}</code></td>
                        <td class="fw-600" style="color:#0f172a;">{{ $row->name ?? $row['name'] }}</td>
                        <td>{{ $row->category_name ?? $row['category_name'] ?? '-' }}</td>
                        <td>
                            @if($cond === 'good')
                                <span class="badge" style="background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;">Baik</span>
                            @elseif($cond === 'damaged')
                                <span class="badge" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;">Rusak</span>
                            @else
                                <span class="badge" style="background:#fffbeb;color:#92400e;border:1px solid #fde68a;">Perbaikan</span>
                            @endif
                        </td>
                        <td>
                            @if($stat === 'available')
                                <span class="badge" style="background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;">Tersedia</span>
                            @elseif($stat === 'in_use')
                                <span class="badge" style="background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;">Dipinjam</span>
                            @else
                                <span class="badge" style="background:#f8fafc;color:#475569;border:1px solid #e2e8f0;">Maintenance</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted p-4" style="font-size:13px;">
                            Tidak ada data alat yang terdaftar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
