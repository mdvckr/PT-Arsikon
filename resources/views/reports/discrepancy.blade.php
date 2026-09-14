<x-app-layout>
    <x-slot name="title">Laporan Diskrepansi Opname</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div class="breadcrumb">
            <a href="{{ route('reports.index') }}">Laporan</a>
            <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
            <span>Diskrepansi Opname</span>
        </div>
        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-secondary">
            Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-header" style="display:flex;align-items:center;gap:10px;padding:16px 20px;">
            <div style="width:32px;height:32px;border-radius:6px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#475569;flex-shrink:0;">
                <i class="fas fa-clipboard-check" style="font-size:13px;"></i>
            </div>
            <span class="card-title" style="margin:0;font-size:15px;">Laporan Diskrepansi Stock Opname</span>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:130px;">Tgl Audit</th>
                        <th>Gudang</th>
                        <th>Material</th>
                        <th style="text-align:right;">Stok Sistem</th>
                        <th style="text-align:right;">Stok Fisik</th>
                        <th style="text-align:right;">Selisih</th>
                        <th style="text-align:center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $row)
                    @php 
                        $diff = (float) ($row->difference ?? $row['difference'] ?? 0); 
                        $sys = (float) ($row->system_quantity ?? $row['system_quantity'] ?? 0);
                        $phys = (float) ($row->physical_quantity ?? $row['physical_quantity'] ?? 0);
                        $date = $row->opname_date ?? $row['opname_date'] ?? null;
                    @endphp
                    <tr>
                        <td style="color:#64748b;font-size:13px;">
                            {{ $date ? \Carbon\Carbon::parse($date)->format('d/m/Y') : '-' }}
                        </td>
                        <td>{{ $row->warehouse_name ?? $row['warehouse_name'] ?? '-' }}</td>
                        <td>
                            <div class="fw-600" style="color:#0f172a;">{{ $row->material_name ?? $row['material_name'] ?? '-' }}</div>
                        </td>
                        <td style="text-align:right;color:#475569;">{{ number_format($sys, 0, ',', '.') }}</td>
                        <td style="text-align:right;color:#475569;">{{ number_format($phys, 0, ',', '.') }}</td>
                        <td class="fw-700" style="text-align:right;color:#0f172a;">
                            {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 0, ',', '.') }}
                        </td>
                        <td style="text-align:center;">
                            @if($diff > 0)
                                <span class="badge" style="background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;">Surplus</span>
                            @elseif($diff < 0)
                                <span class="badge" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;">Defisit</span>
                            @else
                                <span class="badge" style="background:#f8fafc;color:#475569;border:1px solid #e2e8f0;">Sesuai</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted p-4" style="font-size:13px;">
                            Tidak ada data diskrepansi opname.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
