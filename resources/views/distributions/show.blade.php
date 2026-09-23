<x-app-layout>
    <x-slot name="title">Detail Distribusi: {{ $distribution->distribution_number }}</x-slot>

    @push('styles')
    <style>
        .dist-detail-layout {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 16px;
            align-items: start;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 12px;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #64748b;
            font-weight: 500;
            width: 130px;
            flex-shrink: 0;
            font-size: 11.5px;
        }

        .info-val {
            color: #0f172a;
            font-weight: 600;
            text-align: right;
            word-break: break-word;
            font-size: 12px;
        }

        .dist-item-table th {
            padding: 7px 10px !important;
            font-size: 10.5px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.3px !important;
            color: #64748b !important;
            background: #fafafa !important;
            border-bottom: 1px solid #e2e8f0 !important;
            white-space: nowrap !important;
        }

        .dist-item-table td {
            padding: 8px 10px !important;
            vertical-align: middle !important;
            font-size: 12px !important;
        }

        @media (max-width: 992px) {
            .dist-detail-layout {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .info-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 2px;
            }
            .info-label {
                width: 100%;
                font-size: 11px;
            }
            .info-val {
                text-align: left;
                width: 100%;
            }
            .page-actions-header {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 10px;
            }
        }
    </style>
    @endpush

    <div class="breadcrumb mb-2" style="font-size:12px;">
        <a href="{{ route('distributions.index') }}">Distribusi</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:9px;"></i></span>
        <span>{{ $distribution->distribution_number }}</span>
    </div>

    @if(session('error'))
    <div class="alert alert-danger mb-3" style="border-radius:6px;padding:8px 12px;font-size:12px;">
        <i class="fas fa-circle-exclamation"></i> {{ session('error') }}
    </div>
    @endif
    @if(session('success'))
    <div class="alert alert-success mb-3" style="border-radius:6px;padding:8px 12px;font-size:12px;">
        <i class="fas fa-circle-check"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('info'))
    <div class="alert alert-info mb-3" style="border-radius:6px;padding:8px 12px;font-size:12px;">
        <i class="fas fa-info-circle"></i> {{ session('info') }}
    </div>
    @endif

    {{-- Top Action & Header Bar --}}
    @php
        $statusBadge = match($distribution->status) {
            'draft'      => '<span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-size:10.5px;padding:2px 8px;border-radius:12px;font-weight:600;"><i class="fas fa-clock me-1"></i>Draft</span>',
            'in_transit' => '<span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-size:10.5px;padding:2px 8px;border-radius:12px;font-weight:600;"><i class="fas fa-truck me-1"></i>Dalam Pengiriman</span>',
            'completed'  => '<span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:10.5px;padding:2px 8px;border-radius:12px;font-weight:600;"><i class="fas fa-check-double me-1"></i>Selesai</span>',
            'cancelled'  => '<span class="badge" style="background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;font-size:10.5px;padding:2px 8px;border-radius:12px;font-weight:600;"><i class="fas fa-ban me-1"></i>Dibatalkan</span>',
            default      => '<span class="badge" style="background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;font-size:10.5px;padding:2px 8px;border-radius:12px;font-weight:600;">'.$distribution->status.'</span>',
        };
    @endphp

    <div class="flex items-center justify-between mb-3 page-actions-header">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <a href="{{ route('distributions.index') }}" class="btn btn-light border btn-sm" style="border-radius:6px;padding:5px 10px;font-size:11.5px;color:#475569;">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
            <h2 class="fw-700" style="font-size:15px;color:#0f172a;margin:0;">{{ $distribution->distribution_number }}</h2>
            {!! $statusBadge !!}
        </div>
        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
            @if(in_array($distribution->status, ['draft', 'in_transit', 'completed']))
                <a href="{{ route('distributions.print', $distribution) }}" target="_blank" class="btn btn-sm btn-light border" style="font-size:11.5px;padding:5px 12px;border-radius:6px;display:inline-flex;align-items:center;gap:5px;color:#475569;">
                    <i class="fas fa-print"></i> Cetak / PDF
                </a>
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
                <a href="{{ $waUrl }}" target="_blank" class="btn btn-sm" style="background:#25d366;border-color:#25d366;color:#fff;font-size:11.5px;padding:5px 12px;border-radius:6px;display:inline-flex;align-items:center;gap:5px;font-weight:600;">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </a>
            @endif
        </div>
    </div>

    <div class="dist-detail-layout mb-3">
        {{-- Left: Items Table --}}
        <div style="display:flex;flex-direction:column;gap:16px;">

            {{-- Ship Action --}}
            @if($distribution->status === 'draft')
                @can('ship distributions')
                <div class="card" style="border:1px solid #bfdbfe;border-radius:10px;background:#eff6ff;box-shadow:0 2px 6px -1px rgba(37,99,235,0.06);overflow:hidden;">
                    <div class="card-body" style="padding:12px 14px;display:flex;align-items:center;justify-content:space-between;gap:10px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <i class="fas fa-truck text-primary" style="font-size:14px;"></i>
                            <span style="font-size:12.5px;font-weight:600;color:#1e40af;">Surat Jalan ini siap dikirimkan</span>
                        </div>
                        <form method="POST" action="{{ route('distributions.ship', $distribution) }}" id="shipForm">
                            @csrf
                            <button type="submit" id="shipFormSubmit" style="display:none;"></button>
                            <button type="button" class="btn btn-primary btn-sm" onclick="openShipModal()" style="font-size:12px;padding:6px 14px;border-radius:6px;font-weight:600;box-shadow:0 2px 4px rgba(37,99,235,0.2);">
                                <i class="fas fa-truck me-1"></i> Proses Pengiriman
                            </button>
                        </form>
                    </div>
                </div>
                @endcan
            @endif

            {{-- Items Card --}}
            <div class="card" style="border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
                <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:10px 14px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:6px;">
                    <div style="display:flex;align-items:center;gap:6px;">
                        <i class="fas fa-boxes-stacked text-primary" style="font-size:13px;"></i>
                        <span class="card-title" style="font-size:13px;font-weight:700;color:#1e293b;">Item Surat Jalan</span>
                    </div>
                    <span class="badge" style="background:#eff6ff;color:#2563eb;font-weight:700;font-size:10.5px;padding:2px 8px;border-radius:12px;border:1px solid #bfdbfe;">
                        {{ $distribution->items->count() }} Item
                    </span>
                </div>

                @php $canReceive = $distribution->status === 'in_transit' && auth()->user()->can('receive distributions'); @endphp
                @if($canReceive)
                <form method="POST" action="{{ route('distributions.receive', $distribution) }}">
                @csrf
                @endif

                <div class="table-wrap" style="overflow-x:auto;">
                    <table class="data-table dist-item-table mb-0">
                        <thead>
                            <tr>
                                <th style="width:36px;text-align:center;">#</th>
                                <th style="padding:7px 12px;">Barang / Alat</th>
                                <th style="text-align:center;">Dikirim</th>
                                <th style="text-align:center;">Diterima</th>
                                <th style="text-align:center;">Terima Sekarang</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($distribution->items as $i => $item)
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td style="text-align:center;color:#94a3b8;font-size:11px;">{{ $i + 1 }}</td>
                                <td style="padding:8px 12px;">
                                    @if($item->isTool())
                                    <div class="fw-600" style="color:#0f172a;font-size:12.5px;">{{ $item->tool?->name ?? 'Alat' }}</div>
                                    <div class="text-muted" style="font-size:10.5px;">
                                        <span style="font-family:ui-monospace,SFMono-Regular,Consolas,monospace;">{{ $item->tool?->code }}</span>
                                        @if($item->toolAssignment) · {{ $item->toolAssignment->assignment_number }} @endif
                                    </div>
                                    @elseif($item->isCustom())
                                    <div class="fw-600" style="color:#0f172a;font-size:12.5px;">
                                        {{ $item->name() }}
                                        <span class="badge" style="background:#fef3c7;color:#92400e;font-size:9px;padding:1px 5px;border-radius:3px;margin-left:4px;font-weight:600;">Custom</span>
                                    </div>
                                    <div class="text-muted" style="font-size:10.5px;">Item Bebas · Non-Master</div>
                                    @else
                                    <div class="fw-600" style="color:#0f172a;font-size:12.5px;">{{ $item->material?->name ?? 'Material' }}</div>
                                    <div class="text-muted" style="font-size:10.5px;">
                                        <span style="font-family:ui-monospace,SFMono-Regular,Consolas,monospace;">{{ $item->material?->code }}</span>
                                    </div>
                                    @endif
                                </td>
                                <td class="fw-600" style="text-align:center;">{{ number_format($item->qty_shipped, 0, ',', '.') }} {{ $item->unitAbbr() }}</td>
                                <td style="text-align:center;">
                                    <span class="fw-600" style="color:#047857;">{{ number_format($item->qty_received, 0, ',', '.') }}</span>
                                    <span class="text-muted" style="font-size:10.5px;">{{ $item->unitAbbr() }}</span>
                                </td>
                                <td style="text-align:center;">
                                    @if($canReceive)
                                        @php $remaining = (float) $item->qty_shipped - (float) $item->qty_received - (float) $item->qty_damaged_or_lost; @endphp
                                        @if($remaining > 0)
                                        <input type="hidden" name="items[{{ $i }}][distribution_item_id]" value="{{ $item->id }}">
                                        <div style="display:inline-flex;align-items:center;gap:4px;">
                                            <input type="number" name="items[{{ $i }}][received_quantity]"
                                                value="{{ $remaining }}" min="0" max="{{ $remaining }}" step="0.01"
                                                style="width:60px;height:28px;text-align:center;font-weight:700;font-size:11.5px;border-radius:4px;border:1px solid #cbd5e1;padding:0 2px;" required>
                                            <input type="number" name="items[{{ $i }}][qty_damaged_or_lost]"
                                                value="0" min="0" step="0.01" title="Rusak / Hilang"
                                                style="width:50px;height:28px;text-align:center;font-size:11.5px;border-radius:4px;border:1px solid #fca5a5;padding:0 2px;color:#b91c1c;">
                                        </div>
                                        @else
                                        <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:10px;padding:2px 6px;border-radius:10px;font-weight:600;"><i class="fas fa-check" style="font-size:8px;"></i> Selesai</span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($canReceive)
                <div class="card-body" style="border-top:1px solid #e2e8f0;padding:12px 14px;">
                    <div class="mb-2">
                        <label class="form-label" style="font-size:11px;font-weight:600;color:#334155;">No. Surat Jalan</label>
                        <input type="text" name="surat_jalan" class="form-control" value="{{ old('surat_jalan', $distribution->surat_jalan ?? '') }}" placeholder="Masukkan No. Surat Jalan" style="height:32px;border-radius:5px;font-size:12px;">
                    </div>
                    <button type="submit" class="btn btn-success w-full" style="justify-content:center;padding:8px;font-size:12.5px;font-weight:600;border-radius:6px;" onclick="return confirm('Konfirmasi penerimaan barang/alat di gudang tujuan?')">
                        <i class="fas fa-clipboard-check me-1"></i> Konfirmasi Penerimaan
                    </button>
                </div>
                </form>
                @endif
            </div>
        </div>

        {{-- Right: Info Card --}}
        <div style="display:flex;flex-direction:column;gap:14px;">
            <div class="card" style="border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
                <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:10px 14px;display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-file-lines text-primary" style="font-size:13px;"></i>
                    <span class="card-title" style="font-size:13px;font-weight:700;color:#1e293b;">Informasi Surat Jalan</span>
                </div>
                <div class="card-body" style="padding:12px 14px;">
                    <div class="info-row">
                        <span class="info-label">No. Surat Jalan</span>
                        <span class="info-val">{{ $distribution->distribution_number }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Gudang Asal</span>
                        <span class="info-val">{{ $distribution->fromWarehouse?->name ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Gudang Tujuan</span>
                        <span class="info-val">{{ $distribution->toWarehouse?->name ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tgl Kirim</span>
                        <span class="info-val">{{ $distribution->delivery_date ? \Carbon\Carbon::parse($distribution->delivery_date)->format('d/m/Y') : '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Supir / Kurir</span>
                        <span class="info-val">{{ $distribution->driver_name ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">No. Polisi</span>
                        <span class="info-val">{{ $distribution->vehicle_number ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Dibuat Oleh</span>
                        <span class="info-val">{{ $distribution->creator?->name ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Referensi MR</span>
                        <span class="info-val">
                            @if($distribution->materialRequest)
                            <a href="{{ route('material-requests.show', $distribution->materialRequest) }}" class="text-primary" style="font-size:12px;text-decoration:none;">
                                <i class="fas fa-link me-1" style="font-size:9px;"></i>{{ $distribution->materialRequest->request_number }}
                            </a>
                            @else
                            -
                            @endif
                        </span>
                    </div>

                    @if($distribution->notes)
                    <div style="margin-top:10px;padding:10px;background:#f8fafc;border-radius:6px;font-size:12px;color:#475569;">
                        <strong style="color:#334155;">Catatan:</strong><br>{{ $distribution->notes }}
                    </div>
                    @endif
                </div>
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
                            <span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-size:10px;padding:1px 5px;border-radius:4px;margin-left:6px;">{{ $item->toolAssignment->assignment_number }}</span>
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
        try {
            const submitBtn = document.getElementById('shipFormSubmit');
            if (submitBtn) {
                submitBtn.click();
            } else {
                form.submit();
            }
        } catch (e) {
            console.error(e);
            isSubmitting = false;
        }
        // safety reset if redirect fails (e.g. validation error)
        setTimeout(() => { isSubmitting = false; }, 3000);
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