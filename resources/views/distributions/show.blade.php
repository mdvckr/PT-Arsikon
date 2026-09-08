<x-app-layout>
    <x-slot name="title">Detail Distribusi: {{ $distribution->delivery_number }}</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('distributions.index') }}">Distribusi</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>{{ $distribution->delivery_number }}</span>
    </div>

    <div class="grid" style="grid-template-columns:1fr 340px;gap:20px;align-items:start;">

        {{-- Items List & Receive Form --}}
        <div class="card">
            <div class="card-header">
                <i class="fas fa-boxes text-primary"></i> <span class="card-title">Item Surat Jalan</span>
            </div>
            
            @if($distribution->status === 'draft')
                @can('ship distributions')
                <div class="card-body" style="border-bottom:1px solid #f1f5f9;background:#f8fafc;">
                    <form method="POST" action="{{ route('distributions.ship', $distribution) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary" onclick="return confirm('Kirim surat jalan ini? Stok di gudang asal akan dikurangi.')">
                            <i class="fas fa-truck"></i> Proses Pengiriman (Ship)
                        </button>
                    </form>
                </div>
                @endcan
            @endif

            @if(in_array($distribution->status, ['shipped', 'partially_received']))
                @can('receive distributions')
                <form method="POST" action="{{ route('distributions.receive', $distribution) }}">
                @csrf
                @endcan
            @endif

            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Material</th>
                            <th>Dikirim</th>
                            <th>Diterima Sblmnya</th>
                            <th>Terima Sekarang</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($distribution->items as $i => $item)
                        <tr>
                            <td>
                                <div class="fw-600">{{ $item->material?->name }}</div>
                                <div class="text-muted" style="font-size:11px;">{{ $item->material?->code }}</div>
                            </td>
                            <td class="fw-600">{{ $item->quantity }} {{ $item->material?->unit?->abbreviation }}</td>
                            <td class="text-success fw-600">{{ $item->received_quantity }} {{ $item->material?->unit?->abbreviation }}</td>
                            <td>
                                @if(in_array($distribution->status, ['shipped', 'partially_received']) && auth()->user()->can('receive distributions'))
                                    @php $remaining = $item->quantity - $item->received_quantity; @endphp
                                    @if($remaining > 0)
                                    <input type="hidden" name="items[{{ $i }}][distribution_item_id]" value="{{ $item->id }}">
                                    <div class="flex items-center gap-1">
                                        <input type="number" name="items[{{ $i }}][received_quantity]" class="form-control form-control-sm"
                                            value="{{ $remaining }}" min="0" max="{{ $remaining }}" step="0.01" style="width:80px;" required>
                                    </div>
                                    @else
                                    <span class="badge badge-success"><i class="fas fa-check"></i> Selesai</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(in_array($distribution->status, ['shipped', 'partially_received']))
                @can('receive distributions')
                <div class="card-body" style="border-top:1px solid #f1f5f9;text-align:right;">
                    <button type="submit" class="btn btn-success" onclick="return confirm('Konfirmasi penerimaan barang di gudang tujuan?')">
                        <i class="fas fa-clipboard-check"></i> Konfirmasi Penerimaan
                    </button>
                </div>
                </form>
                @endcan
            @endif
        </div>

        {{-- Info Card --}}
        <div class="card">
            <div class="card-header">
                <i class="fas fa-file-alt text-primary"></i> <span class="card-title">Informasi</span>
            </div>
            <div class="card-body">
                @php
                    $rows = [
                        ['No. Surat Jalan', $distribution->delivery_number],
                        ['Gudang Asal', $distribution->fromWarehouse?->name ?? '-'],
                        ['Gudang Tujuan', $distribution->toWarehouse?->name ?? '-'],
                        ['Tgl Kirim', $distribution->delivery_date ? \Carbon\Carbon::parse($distribution->delivery_date)->format('d/m/Y') : '-'],
                        ['Supir', $distribution->driver_name ?? '-'],
                        ['No. Polisi', $distribution->vehicle_number ?? '-'],
                        ['Referensi MR', $distribution->materialRequest?->request_number ?? '-'],
                    ];
                    $statusMap = [
                        'draft'              => ['badge-warning', 'clock', 'Draft'],
                        'shipped'            => ['badge-primary', 'truck', 'Dikirim'],
                        'partially_received' => ['badge-info', 'box-open', 'Diterima Parsial'],
                        'received'           => ['badge-success', 'check-double', 'Diterima Penuh'],
                    ];
                    [$cls, $icon, $label] = $statusMap[$distribution->status] ?? ['badge-gray', 'question', $distribution->status];
                @endphp
                @foreach($rows as [$lbl, $val])
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:13.5px;">
                    <span class="text-muted" style="width:120px;">{{ $lbl }}</span>
                    <span class="fw-600 text-end">{{ $val }}</span>
                </div>
                @endforeach
                <div style="margin-top:12px;">
                    <span class="badge {{ $cls }}" style="font-size:13px;padding:6px 14px;">
                        <i class="fas fa-{{ $icon }}"></i> {{ $label }}
                    </span>
                </div>
                @if($distribution->notes)
                <div style="margin-top:12px;padding:12px;background:#f8fafc;border-radius:8px;font-size:13px;color:#475569;">
                    <strong>Catatan:</strong><br>{{ $distribution->notes }}
                </div>
                @endif
            </div>
        </div>

    </div>
</x-app-layout>
