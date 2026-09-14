<x-app-layout>
    <x-slot name="title">Laporan Mutasi Stok</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div class="breadcrumb">
            <a href="{{ route('reports.index') }}">Laporan</a>
            <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
            <span>Mutasi Stok</span>
        </div>
        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-secondary">
            Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-header" style="display:flex;align-items:center;gap:10px;padding:16px 20px;">
            <div style="width:32px;height:32px;border-radius:6px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#475569;flex-shrink:0;">
                <i class="fas fa-arrow-right-arrow-left" style="font-size:13px;"></i>
            </div>
            <span class="card-title" style="margin:0;font-size:15px;">Riwayat Mutasi Stok</span>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:140px;">Tanggal</th>
                        <th>Material</th>
                        <th>Gudang</th>
                        <th>Arah Mutasi</th>
                        <th style="text-align:right;">Jumlah</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $row)
                    @php
                        $type = $row->type ?? $row['type'] ?? 'in';
                        $isIn = in_array($type, ['in', 'receipt', 'opname_adjust_up']) || (isset($row->quantity) && $row->quantity > 0);
                        $qty = abs((float) ($row->quantity ?? $row['quantity'] ?? 0));
                        $refType = $row->reference_type ?? $row['reference_type'] ?? '';
                    @endphp
                    <tr>
                        <td style="color:#64748b;font-size:13px;">
                            {{ \Carbon\Carbon::parse($row->created_at ?? $row['created_at'])->format('d/m/Y H:i') }}
                        </td>
                        <td>
                            <div class="fw-600" style="color:#0f172a;">{{ $row->material_name ?? $row['material_name'] }}</div>
                        </td>
                        <td>{{ $row->warehouse_name ?? $row['warehouse_name'] }}</td>
                        <td>
                            @if($isIn)
                                <span class="badge" style="background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;">Masuk</span>
                            @else
                                <span class="badge" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;">Keluar</span>
                            @endif
                            @if($refType)
                                <span class="text-muted ms-1" style="font-size:11.5px;">({{ ucwords(str_replace('_', ' ', $refType)) }})</span>
                            @endif
                        </td>
                        <td class="fw-700" style="text-align:right;color:#0f172a;">
                            {{ $isIn ? '+' : '-' }}{{ number_format($qty, 0, ',', '.') }}
                        </td>
                        <td class="text-muted" style="font-size:12.5px;">{{ $row->notes ?? $row['notes'] ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted p-4" style="font-size:13px;">
                            Tidak ada data mutasi pada periode ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
