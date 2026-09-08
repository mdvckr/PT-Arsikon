<x-app-layout>
    <x-slot name="title">Detail Stock Opname</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('stock-opnames.index') }}">Stock Opname</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>{{ $stockOpname->opname_number }}</span>
    </div>

    <div class="grid" style="grid-template-columns:1fr 340px;gap:24px;align-items:start;">

        <div class="card">
            <div class="card-header">
                <i class="fas fa-list-check text-primary"></i> <span class="card-title">Hasil Audit (Diskrepansi)</span>
                @if($stockOpname->status === 'open')
                    @can('approve stock opname')
                    <form method="POST" action="{{ route('stock-opnames.approve', $stockOpname) }}" class="ms-auto">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Konfirmasi audit ini? Stok sistem akan Disesuaikan secara otomatis mengikuti stok fisik.')">
                            <i class="fas fa-check-circle"></i> Setujui & Sesuaikan Stok
                        </button>
                    </form>
                    @endcan
                @endif
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Material</th>
                            <th>Stok Sistem</th>
                            <th>Stok Fisik</th>
                            <th>Selisih (Diff)</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stockOpname->items as $item)
                        @php
                            $diff = $item->difference;
                            $diffColor = $diff > 0 ? 'text-success' : ($diff < 0 ? 'text-danger' : 'text-muted');
                            $diffIcon = $diff > 0 ? 'fa-arrow-up' : ($diff < 0 ? 'fa-arrow-down' : 'fa-minus');
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-600">{{ $item->inventory?->material?->name ?? 'Material Dihapus' }}</div>
                                <div class="text-muted" style="font-size:11.5px;">{{ $item->inventory?->material?->code }}</div>
                            </td>
                            <td>{{ number_format($item->system_quantity, 2) }} {{ $item->inventory?->material?->unit?->abbreviation }}</td>
                            <td class="fw-700">{{ number_format($item->physical_quantity, 2) }} {{ $item->inventory?->material?->unit?->abbreviation }}</td>
                            <td class="{{ $diffColor }} fw-600">
                                @if($diff != 0)
                                <i class="fas {{ $diffIcon }}"></i> {{ number_format(abs($diff), 2) }}
                                @else
                                -
                                @endif
                            </td>
                            <td>
                                @if($diff > 0) Surplus @elseif($diff < 0) Defisit @else Cocok @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="fas fa-file-alt text-primary"></i> <span class="card-title">Informasi Dokumen</span>
            </div>
            <div class="card-body">
                @php
                    $rows = [
                        ['No. Dokumen', $stockOpname->opname_number],
                        ['Gudang', $stockOpname->warehouse?->name ?? '-'],
                        ['Tanggal Audit', \Carbon\Carbon::parse($stockOpname->opname_date)->format('d/m/Y')],
                        ['Status', $stockOpname->status === 'approved' ? '<span class="badge badge-success">Disetujui</span>' : ($stockOpname->status === 'rejected' ? '<span class="badge badge-danger">Ditolak</span>' : '<span class="badge badge-warning">Open</span>')],
                        ['Dibuat Oleh', $stockOpname->creator?->name ?? '-'],
                        ['Disetujui Oleh', $stockOpname->approver?->name ?? '-'],
                    ];
                @endphp
                @foreach($rows as [$label, $value])
                <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f1f5f9;font-size:13.5px;">
                    <span class="text-muted fw-500" style="width:130px;flex-shrink:0;">{{ $label }}</span>
                    <span class="fw-600 text-end">{!! $value !!}</span>
                </div>
                @endforeach
                @if($stockOpname->notes)
                <div class="mt-3 p-3 bg-gray-50 rounded" style="background:#f8fafc;border-radius:8px;font-size:13px;">
                    <strong>Catatan Auditor:</strong><br>{{ $stockOpname->notes }}
                </div>
                @endif
            </div>
        </div>

    </div>
</x-app-layout>
