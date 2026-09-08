<x-app-layout>
    <x-slot name="title">Detail Material: {{ $material->name }}</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('materials.index') }}">Material</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>{{ $material->name }}</span>
    </div>

    <div class="grid" style="grid-template-columns:340px 1fr;gap:20px;align-items:start;">
        {{-- Info Card --}}
        <div class="card">
            <div class="card-header">
                <i class="fas fa-box text-primary"></i>
                <span class="card-title">Informasi Material</span>
                @can('edit materials')
                <a href="{{ route('materials.edit', $material) }}" class="btn btn-sm btn-warning">
                    <i class="fas fa-pen"></i> Edit
                </a>
                @endcan
            </div>
            <div class="card-body">
                <table style="width:100%;font-size:13.5px;border-collapse:collapse;">
                    @php
                        $rows = [
                            ['Kode', '<code style="background:#f1f5f9;padding:2px 7px;border-radius:5px;">'.$material->code.'</code>'],
                            ['Nama', $material->name],
                            ['Kategori', $material->category?->name ?? '-'],
                            ['Satuan', ($material->unit?->name ?? '-').' ('.($material->unit?->abbreviation ?? '').') '],
                            ['Supplier', $material->supplier?->name ?? '-'],

                        ];
                    @endphp
                    @foreach($rows as [$label, $value])
                    <tr>
                        <td style="padding:8px 0;color:#64748b;font-weight:500;width:40%;vertical-align:top;">{{ $label }}</td>
                        <td style="padding:8px 0;color:#1e293b;font-weight:600;">{!! $value !!}</td>
                    </tr>
                    @endforeach
                    @if($material->description)
                    <tr>
                        <td style="padding:8px 0;color:#64748b;font-weight:500;vertical-align:top;">Deskripsi</td>
                        <td style="padding:8px 0;color:#475569;">{{ $material->description }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <div>
            {{-- Inventory per Warehouse --}}
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-layer-group text-primary"></i>
                    <span class="card-title">Stok per Gudang</span>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr><th>Gudang</th><th>Qty</th><th>Min. Stok</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @forelse($material->inventories as $inv)
                            <tr>
                                <td class="fw-600">{{ $inv->warehouse?->name }}</td>
                                <td>{{ number_format($inv->quantity, 2) }} {{ $material->unit?->abbreviation }}</td>
                                <td>{{ number_format($inv->min_stock, 2) }}</td>
                                <td>
                                    @if($inv->quantity <= 0)
                                        <span class="badge badge-danger">Habis</span>
                                    @elseif($inv->quantity <= $inv->min_stock)
                                        <span class="badge badge-warning">Rendah</span>
                                    @else
                                        <span class="badge badge-success">Normal</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-muted" style="text-align:center;padding:24px;">Belum ada stok</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Recent Mutations --}}
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-arrows-up-down text-primary"></i>
                    <span class="card-title">Riwayat Mutasi Stok (20 Terakhir)</span>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr><th>Tanggal</th><th>Tipe</th><th>Qty</th><th>Keterangan</th></tr>
                        </thead>
                        <tbody>
                            @forelse($material->stockMutations as $mut)
                            <tr>
                                <td class="text-muted">{{ $mut->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($mut->type === 'in')
                                        <span class="badge badge-success"><i class="fas fa-arrow-up"></i> Masuk</span>
                                    @else
                                        <span class="badge badge-danger"><i class="fas fa-arrow-down"></i> Keluar</span>
                                    @endif
                                </td>
                                <td class="fw-600">{{ number_format(abs($mut->quantity), 2) }}</td>
                                <td class="text-muted">{{ $mut->notes ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-muted" style="text-align:center;padding:24px;">Belum ada mutasi</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
