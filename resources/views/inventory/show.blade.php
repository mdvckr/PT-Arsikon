<x-app-layout>
    <x-slot name="title">Detail Inventori: {{ $inventory->material?->name }}</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('inventory.index') }}">Inventori</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>{{ $inventory->material?->name }}</span>
    </div>

    <div class="grid" style="grid-template-columns:300px 1fr;gap:20px;align-items:start;">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-layer-group text-primary"></i>
                <span class="card-title">Info Inventori</span>
            </div>
            <div class="card-body">
                @php
                    $rows = [
                        ['Material', $inventory->material?->name],
                        ['Kode', $inventory->material?->code],
                        ['Kategori', $inventory->material?->category?->name ?? '-'],
                        ['Satuan', $inventory->material?->unit?->name ?? '-'],
                        ['Gudang', $inventory->warehouse?->name ?? '-'],
                        ['Stok Saat Ini', number_format($inventory->quantity, 2).' '.$inventory->material?->unit?->abbreviation],
                        ['Stok Minimum', number_format($inventory->min_stock, 2)],
                    ];
                @endphp
                @foreach($rows as [$lbl, $val])
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:13.5px;">
                    <span class="text-muted">{{ $lbl }}</span>
                    <span class="fw-600">{{ $val }}</span>
                </div>
                @endforeach

                <div style="margin-top:16px;text-align:center;">
                    <div style="font-size:40px;font-weight:800;color:{{ $inventory->quantity <= 0 ? '#dc2626' : ($inventory->quantity <= $inventory->min_stock ? '#d97706' : '#059669') }};">
                        {{ number_format($inventory->quantity, 2) }}
                    </div>
                    <div class="text-muted" style="font-size:13px;">{{ $inventory->material?->unit?->abbreviation }}</div>
                    @if($inventory->quantity <= 0)
                        <span class="badge badge-danger mt-2" style="font-size:13px;padding:6px 14px;">Stok Habis</span>
                    @elseif($inventory->quantity <= $inventory->min_stock)
                        <span class="badge badge-warning mt-2" style="font-size:13px;padding:6px 14px;">Stok Rendah</span>
                    @else
                        <span class="badge badge-success mt-2" style="font-size:13px;padding:6px 14px;">Normal</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="fas fa-arrows-up-down text-primary"></i>
                <span class="card-title">Riwayat Mutasi (30 Terakhir)</span>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Tipe</th>
                            <th>Jumlah</th>
                            <th>Referensi</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventory->stockMutations as $mut)
                        <tr>
                            <td class="text-muted">{{ $mut->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @if(in_array($mut->type, ['in', 'receipt', 'opname_adjust_up']))
                                    <span class="badge badge-success"><i class="fas fa-arrow-up"></i> Masuk</span>
                                @else
                                    <span class="badge badge-danger"><i class="fas fa-arrow-down"></i> Keluar</span>
                                @endif
                            </td>
                            <td class="fw-600">{{ number_format(abs($mut->quantity), 2) }}</td>
                            <td class="text-muted">{{ $mut->reference_type ?? '-' }} {{ $mut->reference_id ?? '' }}</td>
                            <td class="text-muted">{{ $mut->notes ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-muted" style="text-align:center;padding:24px;">Belum ada riwayat mutasi</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
