<x-app-layout>
    <x-slot name="title">Laporan Stok</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div class="breadcrumb">
            <a href="{{ route('reports.index') }}">Laporan</a>
            <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
            <span>Laporan Stok</span>
        </div>
        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-secondary">
            Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-header" style="display:flex;align-items:center;gap:10px;padding:16px 20px;">
            <div style="width:32px;height:32px;border-radius:6px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#475569;flex-shrink:0;">
                <i class="fas fa-boxes-stacked" style="font-size:13px;"></i>
            </div>
            <span class="card-title" style="margin:0;font-size:15px;">Rekapitulasi Stok Material</span>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Material</th>
                        <th>Kategori</th>
                        <th>Gudang</th>
                        <th style="text-align:right;">Stok</th>
                        <th>Satuan</th>
                        <th style="text-align:right;">Min. Stok</th>
                        <th style="text-align:right;">Nilai Stok</th>
                        <th style="text-align:center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $row)
                    @php 
                        $qty = (float) ($row->quantity ?? $row['quantity'] ?? 0); 
                        $min = (float) ($row->min_stock ?? $row['min_stock'] ?? 0); 
                        $price = (float) ($row->unit_price ?? $row['unit_price'] ?? 0);
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-600" style="color:#0f172a;">{{ $row->material_name ?? $row['material_name'] ?? '-' }}</div>
                            @if(!empty($row->material_code ?? $row['material_code']))
                            <div class="text-muted" style="font-size:11.5px;">{{ $row->material_code ?? $row['material_code'] }}</div>
                            @endif
                        </td>
                        <td>{{ $row->category_name ?? $row['category_name'] ?? '-' }}</td>
                        <td>{{ $row->warehouse_name ?? $row['warehouse_name'] ?? '-' }}</td>
                        <td class="fw-700" style="text-align:right;color:#0f172a;">{{ number_format($qty, 0, ',', '.') }}</td>
                        <td>{{ $row->unit_abbr ?? $row['unit_abbr'] ?? '-' }}</td>
                        <td style="text-align:right;">{{ number_format($min, 0, ',', '.') }}</td>
                        <td style="text-align:right;color:#475569;">Rp {{ number_format($qty * $price, 0, ',', '.') }}</td>
                        <td style="text-align:center;">
                            @if($qty <= 0)
                                <span class="badge" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;">Habis</span>
                            @elseif($qty <= $min)
                                <span class="badge" style="background:#fffbeb;color:#92400e;border:1px solid #fde68a;">Rendah</span>
                            @else
                                <span class="badge" style="background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;">Normal</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted p-4" style="font-size:13px;">
                            Tidak ada data stok untuk filter yang dipilih.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
