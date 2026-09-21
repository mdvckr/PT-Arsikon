<x-app-layout>
    <x-slot name="title">Detail Permintaan: {{ $materialRequest->request_number }}</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('material-requests.index') }}">Permintaan Material</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>{{ $materialRequest->request_number }}</span>
    </div>

    <div class="grid" style="grid-template-columns:1fr 300px;gap:20px;align-items:start;">

        {{-- Items --}}
        <div class="card">
            <div class="card-header">
                <i class="fas fa-list text-primary"></i>
                <span class="card-title">Item Permintaan</span>
                <div class="flex gap-2 items-center">
                    @if(in_array($materialRequest->status, ['approved', 'partially_fulfilled']))
                        @can('create material usages')
                        <a href="{{ route('material-usages.create', ['warehouse_id' => $materialRequest->from_warehouse_id]) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-boxes-packing me-1"></i> Catat Pengeluaran Material
                        </a>
                        @endcan
                    @endif

                    @if($materialRequest->status === 'submitted')
                        @can('approve material requests')
                        <form method="POST" action="{{ route('material-requests.approve', $materialRequest) }}">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm"
                                onclick="return confirm('Setujui permintaan ini?')">
                                <i class="fas fa-check"></i> Setujui
                            </button>
                        </form>
                        <button type="button" class="btn btn-danger btn-sm" onclick="document.getElementById('rejectModal').classList.add('show')">
                            <i class="fas fa-xmark"></i> Tolak
                        </button>
                        @endcan
                    @endif
                </div>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Material</th>
                            <th style="text-align:right;">Qty Diminta</th>
                            @if(!in_array($materialRequest->status, ['draft', 'submitted']))
                            <th style="text-align:right;">Qty Disetujui</th>
                            <th style="text-align:right;">Sudah Keluar</th>
                            <th style="text-align:right;">Sisa Kuota</th>
                            @endif
                            <th style="text-align:center;">Satuan</th>
                            <th style="text-align:center;">Status Item</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $hasCustom = $materialRequest->items->contains(fn($i) => $i->isCustom()); @endphp
                        @foreach($materialRequest->items as $item)
                        @php
                            $approved = (float) $item->qty_approved;
                            $fulfilled = (float) $item->qty_fulfilled;
                            $remaining = max(0, $approved - $fulfilled);
                            $unit = $item->displayUnit();
                            $isCustom = $item->isCustom();
                        @endphp
                        <tr @if($isCustom) style="background:#fffbeb;" @endif>
                            <td>
                                <div class="fw-600">{{ $item->displayName() }} @if($isCustom)<span class="badge badge-warning" style="font-size:10px;margin-left:4px;">Custom</span>@endif</div>
                                <div class="text-muted" style="font-size:11.5px;">@if($isCustom) Manual — tidak ada di Pusat @else Kode: {{ $item->material?->code ?? '-' }} @endif</div>
                            </td>
                            <td class="fw-600" style="text-align:right;">{{ format_quantity($item->qty_requested) }}</td>
                            @if(!in_array($materialRequest->status, ['draft', 'submitted']))
                            <td class="fw-700 text-primary" style="text-align:right;">{{ format_quantity($approved) }}</td>
                            <td class="fw-600 text-muted" style="text-align:right;">{{ format_quantity($fulfilled) }}</td>
                            <td class="fw-700 {{ $remaining > 0 ? 'text-success' : 'text-muted' }}" style="text-align:right;">
                                {{ format_quantity($remaining) }}
                            </td>
                            @endif
                            <td style="text-align:center;">{{ $unit }}</td>
                            <td style="text-align:center;">
                                @if(in_array($materialRequest->status, ['draft', 'submitted']))
                                    <span class="badge badge-warning" style="font-size:11px;">Menunggu Persetujuan</span>
                                @elseif($fulfilled >= $approved && $approved > 0)
                                    <span class="badge badge-success" style="font-size:11px;"><i class="fas fa-check-circle"></i> Terpenuhi</span>
                                @elseif($fulfilled > 0)
                                    <span class="badge badge-info" style="font-size:11px;"><i class="fas fa-clock"></i> Sebagian</span>
                                @else
                                    <span class="badge badge-secondary" style="font-size:11px;">Belum Keluar</span>
                                @endif
                            </td>
                            <td class="text-muted" style="font-size:12.5px;">{{ $item->notes ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($materialRequest->rejection_reason)
            <div class="card-body" style="border-top:1px solid #f1f5f9;">
                <div class="alert alert-danger">
                    <i class="fas fa-circle-xmark"></i>
                    <div><strong>Alasan Penolakan:</strong> {{ $materialRequest->rejection_reason }}</div>
                </div>
            </div>
            @endif
        </div>

        {{-- Riwayat Pengeluaran Material (Material Usages) --}}
        @if($materialRequest->materialUsages->isNotEmpty())
        <div class="card mb-4" style="grid-column: 1 / -1;">
            <div class="card-header flex justify-between items-center" style="background:#f8fafc;">
                <div class="flex items-center gap-2">
                    <i class="fas fa-clipboard-check text-success"></i>
                    <span class="card-title fw-700">Riwayat Bukti Pengeluaran Lapangan (Material Usages)</span>
                </div>
                <span class="badge badge-purple">{{ $materialRequest->materialUsages->count() }} Transaksi</span>
            </div>
            <div class="table-wrap">
                <table class="data-table mb-0">
                    <thead>
                        <tr>
                            <th>No. Bukti Pengeluaran</th>
                            <th>Tanggal</th>
                            <th>Gudang Sumber</th>
                            <th>Penerima (Mandor/Tukang)</th>
                            <th>Bagian Pekerjaan / Zona</th>
                            <th>Petugas Gudang</th>
                            <th style="text-align:center;">Status</th>
                            <th style="text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($materialRequest->materialUsages as $usage)
                        <tr>
                            <td>
                                <a href="{{ route('material-usages.show', $usage) }}" class="fw-700 text-primary">
                                    {{ $usage->usage_number }}
                                </a>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($usage->usage_date)->format('d/m/Y') }}</td>
                            <td>{{ $usage->warehouse?->name ?? '-' }}</td>
                            <td><strong>{{ $usage->recipient_name }}</strong></td>
                            <td>{{ $usage->job_section ?? '-' }}</td>
                            <td>{{ $usage->issuedBy?->name ?? '-' }}</td>
                            <td style="text-align:center;">
                                @if($usage->status === 'completed')
                                    <span class="badge badge-success"><i class="fas fa-check"></i> Selesai</span>
                                @else
                                    <span class="badge badge-danger"><i class="fas fa-ban"></i> Dibatalkan</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                <a href="{{ route('material-usages.show', $usage) }}" class="btn btn-sm btn-light border">
                                    <i class="fas fa-eye"></i> Rincian
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Info Card --}}
        <div class="card">
            <div class="card-header">
                <i class="fas fa-file-alt text-primary"></i>
                <span class="card-title">Informasi</span>
            </div>
            <div class="card-body">
                @php
                    $rows = [
                        ['No. Permintaan', $materialRequest->request_number],
                        ['Pemohon', $materialRequest->requestedBy?->name ?? '-'],
                        ['Gudang Asal', $materialRequest->fromWarehouse?->name ?? '-'],
                        ['Gudang Tujuan', $materialRequest->toWarehouse?->name ?? '-'],
                        ['Diajukan', $materialRequest->created_at ? \Carbon\Carbon::parse($materialRequest->created_at)->format('d/m/Y') : '-'],
                        ['Diproses Oleh', $materialRequest->approvedBy?->name ?? '-'],
                    ];
                    $statusMap = [
                        'draft'               => ['badge-gray',   'file',          'Draft'],
                        'submitted'           => ['badge-warning','clock',         'Menunggu Persetujuan'],
                        'approved'            => ['badge-success','check',         'Disetujui'],
                        'partially_fulfilled' => ['badge-info',   'truck',         'Terkirim Sebagian'],
                        'fulfilled'           => ['badge-info',   'truck',         'Terpenuhi'],
                        'rejected'            => ['badge-danger', 'xmark',         'Ditolak'],
                        'cancelled'           => ['badge-gray',   'ban',           'Dibatalkan'],
                    ];
                    [$cls, $icon, $label] = $statusMap[$materialRequest->status] ?? ['badge-gray', 'question', $materialRequest->status];
                @endphp
                @foreach($rows as [$lbl, $val])
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:13.5px;">
                    <span class="text-muted">{{ $lbl }}</span>
                    <span class="fw-600">{{ $val }}</span>
                </div>
                @endforeach
                <div style="margin-top:12px;">
                    <span class="badge {{ $cls }}" style="font-size:13px;padding:6px 14px;">
                        <i class="fas fa-{{ $icon }}"></i> {{ $label }}
                    </span>
                </div>
                @if($materialRequest->notes)
                <div style="margin-top:12px;padding:12px;background:#f8fafc;border-radius:8px;font-size:13px;color:#475569;">
                    <strong>Catatan:</strong><br>{{ $materialRequest->notes }}
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div class="modal-overlay" id="rejectModal">
        <div class="modal-box">
            <div class="modal-header">
                <i class="fas fa-xmark-circle text-danger"></i>
                <span class="modal-title">Tolak Permintaan</span>
                <button class="btn-close-modal" onclick="document.getElementById('rejectModal').classList.remove('show')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('material-requests.reject', $materialRequest) }}">
                @csrf
                <div class="modal-body">
                    <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                    <textarea name="rejection_reason" class="form-control" rows="4"
                        placeholder="Jelaskan alasan penolakan..." required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('rejectModal').classList.remove('show')">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-xmark"></i> Tolak Permintaan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
