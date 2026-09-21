<x-app-layout>
    <x-slot name="title">Detail Distribusi: {{ $distribution->distribution_number }}</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('distributions.index') }}">Distribusi</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>{{ $distribution->distribution_number }}</span>
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
                    <form method="POST" action="{{ route('distributions.ship', $distribution) }}" id="shipForm">
                        @csrf
                        <!-- Hidden submit button triggered by JS modal -->
                        <button type="submit" id="shipFormSubmit" style="display:none;"></button>
                        <button type="button" class="btn btn-primary" onclick="openShipModal()">
                            <i class="fas fa-truck"></i> Proses Pengiriman (Ship)
                        </button>
                    </form>
                </div>
                @endcan
            @endif

            @if(in_array($distribution->status, ['draft', 'in_transit', 'completed']))
                @php
                    $statusText = match($distribution->status) {
                        'draft' => 'Draft (Menunggu Pengiriman)',
                        'in_transit' => 'Dalam Pengiriman (In Transit)',
                        'completed' => 'Selesai (Sudah Diterima)',
                        default => strtoupper($distribution->status)
                    };
                    $waText = "*LAPORAN SURAT JALAN PENGIRIMAN*\n"
                            . "----------------------------------------\n"
                            . "📋 *No. Surat Jalan*: {$distribution->distribution_number}\n"
                            . "🏢 *Gudang Asal*: " . ($distribution->fromWarehouse?->name ?? '-') . "\n"
                            . "🏗️ *Gudang Tujuan*: " . ($distribution->toWarehouse?->name ?? '-') . "\n"
                            . "📅 *Tanggal Kirim*: " . ($distribution->delivery_date ? \Carbon\Carbon::parse($distribution->delivery_date)->format('d/m/Y') : '-') . "\n"
                            . "🚚 *Kurir/Supir*: " . ($distribution->driver_name ?? '-') . "\n"
                            . "🚗 *No. Polisi*: " . ($distribution->vehicle_number ?? '-') . "\n"
                            . "📌 *Status*: {$statusText}\n"
                            . "----------------------------------------\n"
                            . "*RINCIAN ITEM (MATERIAL & ALAT)*:\n";
                    foreach($distribution->items as $idx => $it) {
                        $name = $it->name();
                        $qty = number_format((float)$it->qty_shipped, 0, ',', '.') . ' ' . $it->unitAbbr();
                        $waText .= ($idx+1) . ". *{$name}* — {$qty}\n";
                    }
                    if($distribution->notes) {
                        $waText .= "----------------------------------------\n"
                                . "💬 *Catatan*: " . $distribution->notes . "\n";
                    }
                    $waText .= "----------------------------------------\n"
                            . "🌐 *Link Surat Jalan*: " . route('distributions.show', $distribution) . "\n"
                            . "_PT ARSIKON CIPTA KARYA - WMS_";
                    $waUrl = "https://wa.me/?text=" . urlencode($waText);
                @endphp
                <div class="card-body flex gap-2" style="border-bottom:1px solid #f1f5f9;background:#f8fafc;">
                    <a href="{{ route('distributions.print', $distribution) }}" target="_blank" class="btn btn-primary">
                        <i class="fas fa-print"></i> Cetak / PDF Surat Jalan
                    </a>
                    <a href="{{ $waUrl }}" target="_blank" class="btn btn-success" style="background:#25d366;border-color:#25d366;">
                        <i class="fab fa-whatsapp"></i> Kirim ke WhatsApp
                    </a>
                </div>
            @endif

            @if($distribution->status === 'in_transit')
                @can('receive distributions')
                <form method="POST" action="{{ route('distributions.receive', $distribution) }}">
                @csrf
                @endcan
            @endif

            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Barang / Alat</th>
                            <th>Dikirim</th>
                            <th>Diterima Sblmnya</th>
                            <th>Terima Sekarang</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($distribution->items as $i => $item)
                        <tr>
                            <td>
                                @if($item->isTool())
                                <div class="fw-600">{{ $item->tool?->name ?? 'Alat' }}</div>
                                <div class="text-muted" style="font-size:11px;">{{ $item->tool?->code }} · {{ $item->toolAssignment?->assignment_number }}</div>
                                @elseif($item->isCustom())
                                <div class="fw-600">{{ $item->name() }} <span class="badge badge-info" style="font-size:10px;margin-left:4px;">Custom</span></div>
                                <div class="text-muted" style="font-size:11px;">Item Bebas · Non-Master</div>
                                @else
                                <div class="fw-600">{{ $item->material?->name ?? 'Material' }}</div>
                                <div class="text-muted" style="font-size:11px;">{{ $item->material?->code }}</div>
                                @endif
                            </td>
                            <td class="fw-600">{{ number_format($item->qty_shipped, 0, ',', '.') }} {{ $item->unitAbbr() }}</td>
                            <td class="text-success fw-600">{{ number_format($item->qty_received, 0, ',', '.') }} {{ $item->unitAbbr() }}</td>
                            <td>
                                @if($distribution->status === 'in_transit' && auth()->user()->can('receive distributions'))
                                    @php $remaining = (float) $item->qty_shipped - (float) $item->qty_received - (float) $item->qty_damaged_or_lost; @endphp
                                    @if($remaining > 0)
                                    <input type="hidden" name="items[{{ $i }}][distribution_item_id]" value="{{ $item->id }}">
                                    <div class="flex items-center gap-1">
                                        <input type="number" name="items[{{ $i }}][received_quantity]" class="form-control form-control-sm"
                                            value="{{ $remaining }}" min="0" max="{{ $remaining }}" step="0.01" style="width:80px;" required>
                                        <input type="number" name="items[{{ $i }}][qty_damaged_or_lost]" class="form-control form-control-sm"
                                            value="0" min="0" step="0.01" style="width:60px;" title="Rusak / Hilang">
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

            @if($distribution->status === 'in_transit')
                @can('receive distributions')
                <div class="card-body" style="border-top:1px solid #f1f5f9;text-align:right;">
                    <button type="submit" class="btn btn-success" onclick="return confirm('Konfirmasi penerimaan barang/alat di gudang tujuan?')">
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
                        ['No. Surat Jalan', $distribution->distribution_number],
                        ['Gudang Asal', $distribution->fromWarehouse?->name ?? '-'],
                        ['Gudang Tujuan', $distribution->toWarehouse?->name ?? '-'],
                        ['Tgl Kirim', $distribution->delivery_date ? \Carbon\Carbon::parse($distribution->delivery_date)->format('d/m/Y') : '-'],
                        ['Supir', $distribution->driver_name ?? '-'],
                        ['No. Polisi', $distribution->vehicle_number ?? '-'],
                        ['Dibuat Oleh', $distribution->creator?->name ?? '-'],
                        ['Referensi MR', $distribution->materialRequest?->request_number ?? '-'],
                    ];
                    $statusMap = [
                        'draft'     => ['badge-warning', 'clock', 'Draft'],
                        'in_transit'=> ['badge-primary', 'truck', 'Dalam Pengiriman'],
                        'completed' => ['badge-success', 'check-double', 'Selesai'],
                        'cancelled' => ['badge-gray', 'ban', 'Dibatalkan'],
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

{{-- Ship Confirmation Modal --}}
<div id="shipModal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="shipModalTitle">
    <div class="modal-box">
        <div class="modal-header">
            <div style="width:44px;height:44px;border-radius:12px;background:rgba(37,99,235,0.12);color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">
                <i class="fas fa-truck"></i>
            </div>
            <h3 id="shipModalTitle" class="modal-title">Konfirmasi Pengiriman Surat Jalan</h3>
            <button type="button" class="btn-close-modal" onclick="closeShipModal()" aria-label="Tutup">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="alert alert-info" style="margin-bottom:16px;">
                <i class="fas fa-info-circle"></i>
                <div>
                    <strong>Nomor Surat Jalan:</strong> {{ $distribution->distribution_number }}<br>
                    <strong>Dari:</strong> {{ $distribution->fromWarehouse?->name }}<br>
                    <strong>Ke:</strong> {{ $distribution->toWarehouse?->name }}<br>
                    <strong>Tanggal Kirim:</strong> {{ $distribution->delivery_date ? \Carbon\Carbon::parse($distribution->delivery_date)->format('d/m/Y') : '-' }}<br>
                    <strong>Supir:</strong> {{ $distribution->driver_name ?? '-' }}<br>
                    <strong>No. Polisi:</strong> {{ $distribution->vehicle_number ?? '-' }}
                </div>
            </div>

            <div style="background:#fffbeb;border:1px solid #fcd34d;border-radius:10px;padding:14px;margin-bottom:16px;">
                <div style="display:flex;align-items:flex-start;gap:10px;">
                    <i class="fas fa-exclamation-triangle text-warning" style="font-size:18px;margin-top:2px;"></i>
                    <div style="flex:1;color:#78350f;font-size:13.5px;line-height:1.5;">
                        <strong>Tindakan ini akan:</strong>
                        <ul style="margin:8px 0 0 20px;padding:0;">
                            <li>Mengurangi stok material di gudang asal (<strong>{{ $distribution->fromWarehouse?->name }}</strong>)</li>
                            <li>Menambahkan stok <em>in-transit</em> di gudang tujuan (<strong>{{ $distribution->toWarehouse?->name }}</strong>)</li>
                            <li>Menyetujui otomatis peminjaman alat yang berstatus <strong>Pending</strong></li>
                            <li>Mengubah status Surat Jalan menjadi <strong>Dalam Pengiriman (In Transit)</strong></li>
                        </ul>
                        <p style="margin:8px 0 0 0;font-size:12.5px;color:#92400e;"><strong>Catatan:</strong> Proses ini tidak dapat dibatalkan setelah dieksekusi.</p>
                    </div>
                </div>
            </div>

            <div style="font-size:13.5px;color:#475569;line-height:1.6;">
                <strong>Item yang akan dikirim:</strong>
                <ul style="margin:8px 0 0 20px;padding:0;">
                    @foreach($distribution->items as $idx => $item)
                    <li style="margin-bottom:4px;">
                        <strong>{{ $item->name() }}</strong> — {{ number_format((float)$item->qty_shipped, 0, ',', '.') }} {{ $item->unitAbbr() }}
                        @if($item->isTool() && $item->toolAssignment)
                            <span class="badge badge-info" style="margin-left:8px;font-size:10px;">{{ $item->toolAssignment->assignment_number }}</span>
                        @endif
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeShipModal()">
                <i class="fas fa-times"></i> Batal
            </button>
            <button type="button" class="btn btn-primary" onclick="submitShipForm()">
                <i class="fas fa-truck"></i> Ya, Kirim Sekarang
            </button>
        </div>
    </div>
</div>

<script>
let isSubmitting = false;

function openShipModal() {
    const modal = document.getElementById('shipModal');
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeShipModal() {
    const modal = document.getElementById('shipModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

function submitShipForm() {
    if (isSubmitting) return;
    isSubmitting = true;

    const form = document.getElementById('shipForm');
    if (!form) {
        console.error('Form shipForm not found!');
        isSubmitting = false;
        return;
    }

    closeShipModal();
    
    setTimeout(() => {
        const submitBtn = document.getElementById('shipFormSubmit');
        if (submitBtn) {
            submitBtn.click();
        } else {
            form.submit();
        }
    }, 100);
}

// Close on overlay click
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('shipModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeShipModal();
        });
    }
    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeShipModal();
    });
});
</script>
</x-app-layout>