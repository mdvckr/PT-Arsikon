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
                @if($materialRequest->status === 'pending')
                    @can('approve material requests')
                    <div class="flex gap-2">
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
                    </div>
                    @endcan
                @endif
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Material</th>
                            <th>Qty Diminta</th>
                            <th>Satuan</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($materialRequest->items as $item)
                        <tr>
                            <td>
                                <div class="fw-600">{{ $item->material?->name }}</div>
                                <div class="text-muted" style="font-size:11.5px;">{{ $item->material?->code }}</div>
                            </td>
                            <td class="fw-600">{{ number_format($item->quantity, 2) }}</td>
                            <td>{{ $item->material?->unit?->abbreviation }}</td>
                            <td class="text-muted">{{ $item->notes ?? '-' }}</td>
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
                        ['Pemohon', $materialRequest->requester?->name ?? '-'],
                        ['Gudang', $materialRequest->warehouse?->name ?? '-'],
                        ['Dibutuhkan', $materialRequest->needed_at ? \Carbon\Carbon::parse($materialRequest->needed_at)->format('d/m/Y') : '-'],
                        ['Diproses Oleh', $materialRequest->approver?->name ?? '-'],
                    ];
                    $statusMap = [
                        'pending'  => ['badge-warning', 'clock', 'Pending'],
                        'approved' => ['badge-success', 'check', 'Disetujui'],
                        'rejected' => ['badge-danger', 'xmark', 'Ditolak'],
                        'fulfilled'=> ['badge-info',   'truck', 'Terpenuhi'],
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
