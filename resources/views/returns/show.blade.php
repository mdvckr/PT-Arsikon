<x-app-layout>
    <x-slot name="title">Detail Pengembalian Material: {{ $return->return_number }}</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('returns.index') }}">Pengembalian Material</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>{{ $return->return_number }}</span>
    </div>

    <div class="grid" style="grid-template-columns:1fr 340px;gap:20px;align-items:start;">

        {{-- Main Detail Card & Items Table --}}
        <div class="card">
            <div class="card-header flex justify-between items-center">
                <div>
                    <i class="fas fa-boxes-stacked text-primary"></i> <span class="card-title">Item Material Dikembalikan</span>
                </div>
                <span class="badge badge-primary">{{ count($return->items) }} item</span>
            </div>

            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Material</th>
                            <th>Qty Dikembalikan</th>
                            <th>Kondisi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($return->items as $item)
                        <tr>
                            <td>
                                <div class="fw-600">{{ $item->material->name ?? '-' }}</div>
                                @if($item->material?->code || $item->material?->category)
                                    <div class="text-muted" style="font-size:11px;">
                                        {{ $item->material?->code }} · {{ $item->material?->category?->name ?? 'Material' }}
                                    </div>
                                @endif
                            </td>
                            <td class="fw-600">
                                {{ number_format((float)$item->quantity, 0, ',', '.') }} {{ $item->material->unit->abbreviation ?? '' }}
                            </td>
                            <td>
                                @php
                                    $condBadge = match($item->condition) {
                                        'good' => 'badge-success',
                                        'damaged' => 'badge-warning',
                                        default => 'badge-danger'
                                    };
                                    $condLabel = match($item->condition) {
                                        'good' => 'Baik',
                                        'damaged' => 'Rusak',
                                        'unusable' => 'Afkir / Hancur',
                                        default => ucfirst($item->condition)
                                    };
                                @endphp
                                <span class="badge {{ $condBadge }}">{{ $condLabel }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($return->notes)
            <div class="card-body border-top" style="background:#f8fafc;padding:12px 16px;">
                <div style="font-size:13px;color:#475569;">
                    <strong><i class="fas fa-sticky-note me-1 text-secondary"></i> Catatan Tambahan:</strong>
                    <div class="mt-1">{{ $return->notes }}</div>
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar Info Card & Actions --}}
        <div style="display:flex;flex-direction:column;gap:20px;">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-file-alt text-primary"></i> <span class="card-title">Informasi Pengembalian</span>
                </div>
                <div class="card-body">
                    @php
                        $reasonLabel = match($return->reason) {
                            'excess' => 'Kelebihan Material / Sisa',
                            'damaged' => 'Material Rusak / Cacat',
                            'wrong_item' => 'Salah Kirim',
                            'project_complete' => 'Proyek Selesai',
                            default => ucfirst($return->reason ?? '-')
                        };
                        $rows = [
                            ['No. Pengembalian', $return->return_number],
                            ['Dari Gudang Proyek', $return->fromWarehouse?->name ?? '-'],
                            ['Tujuan (Pusat)', $return->toWarehouse?->name ?? '-'],
                            ['Alasan', $reasonLabel],
                            ['Tgl Pengembalian', $return->return_date ? $return->return_date->format('d/m/Y') : '-'],
                            ['Diajukan Oleh', $return->requester?->name ?? 'Sistem'],
                            ['Tgl Pengajuan', $return->created_at ? $return->created_at->format('d/m/Y H:i') : '-'],
                        ];

                        $statusMap = [
                            'pending'  => ['badge-warning', 'clock', 'Menunggu Persetujuan'],
                            'approved' => ['badge-primary', 'truck', 'Disetujui (Dalam Pengiriman)'],
                            'received' => ['badge-success', 'check-double', 'Diterima di Pusat'],
                            'rejected' => ['badge-danger', 'times-circle', 'Ditolak'],
                        ];
                        [$cls, $icon, $label] = $statusMap[$return->status] ?? ['badge-gray', 'question', ucfirst($return->status)];
                    @endphp

                    @foreach($rows as [$lbl, $val])
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:13.5px;">
                        <span class="text-muted" style="width:130px;">{{ $lbl }}</span>
                        <span class="fw-600 text-end">{{ $val }}</span>
                    </div>
                    @endforeach

                    <div style="margin-top:16px;" class="text-center">
                        <span class="badge {{ $cls }}" style="font-size:13px;padding:8px 16px;width:100%;justify-content:center;">
                            <i class="fas fa-{{ $icon }} me-1"></i> {{ $label }}
                        </span>
                    </div>

                    @if($return->rejection_reason)
                    <div style="margin-top:12px;padding:12px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;font-size:12.5px;color:#991b1b;">
                        <strong><i class="fas fa-exclamation-circle me-1"></i> Alasan Penolakan:</strong><br>{{ $return->rejection_reason }}
                    </div>
                    @endif
                </div>
            </div>

            {{-- Action Box --}}
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-cog text-primary"></i> <span class="card-title">Aksi Status</span>
                </div>
                <div class="card-body">
                    @if($return->status === 'pending')
                        <div style="display:flex;flex-direction:column;gap:10px;">
                            <form method="POST" action="{{ route('returns.approve', $return) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary w-full" style="justify-content:center;" onclick="return confirm('Setujui pengembalian material ini?')">
                                    <i class="fas fa-check me-1"></i> Setujui Pengembalian
                                </button>
                            </form>

                            <button type="button" onclick="document.getElementById('reject-form-box').style.display='block'" class="btn btn-danger w-full" style="justify-content:center;">
                                <i class="fas fa-times me-1"></i> Tolak Pengembalian
                            </button>

                            <div id="reject-form-box" style="display:none;margin-top:8px;padding:12px;border:1px solid #fee2e2;border-radius:8px;background:#fff5f5;">
                                <form method="POST" action="{{ route('returns.reject', $return) }}">
                                    @csrf
                                    <label class="form-label text-danger" style="font-size:12px;font-weight:600;">Alasan Penolakan <span class="text-danger">*</span></label>
                                    <textarea name="rejection_reason" class="form-control mb-2" rows="2" placeholder="Tuliskan alasan penolakan..." required></textarea>
                                    <div style="display:flex;gap:8px;justify-content:flex-end;">
                                        <button type="button" onclick="document.getElementById('reject-form-box').style.display='none'" class="btn btn-sm btn-secondary">Batal</button>
                                        <button type="submit" class="btn btn-sm btn-danger">Tolak</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @elseif($return->status === 'approved')
                        <form method="POST" action="{{ route('returns.receive', $return) }}">
                            @csrf
                            <p class="text-muted mb-3" style="font-size:12.5px;">
                                Material sudah tiba di Gudang Pusat? Klik tombol di bawah untuk memasukkan kembali stok ke Gudang Pusat.
                            </p>
                            <button type="submit" class="btn btn-success w-full" style="justify-content:center;" onclick="return confirm('Konfirmasi penerimaan barang di Gudang Pusat & update stok?')">
                                <i class="fas fa-box-open me-1"></i> Terima Barang & Update Stok
                            </button>
                        </form>
                    @else
                        <div class="text-muted text-center" style="font-size:13px;">
                            Tidak ada aksi lanjutan untuk pengembalian berstatus <strong>{{ ucfirst($return->status) }}</strong>.
                        </div>
                    @endif

                    <div class="mt-3 border-top pt-3">
                        <a href="{{ route('returns.index') }}" class="btn btn-secondary w-full" style="justify-content:center;">
                            <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>

