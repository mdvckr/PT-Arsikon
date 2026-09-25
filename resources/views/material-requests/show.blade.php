<x-app-layout>
    <x-slot name="title">Detail Permintaan: {{ $materialRequest->request_number }}</x-slot>

    @push('styles')
    <style>
        .mr-detail-layout {
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

        .mr-item-table th {
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

        .mr-item-table td {
            padding: 8px 10px !important;
            vertical-align: middle !important;
            font-size: 12px !important;
        }

        @media (max-width: 992px) {
            .mr-detail-layout {
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
        <a href="{{ route('material-requests.index') }}">Permintaan Material</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:9px;"></i></span>
        <span>{{ $materialRequest->request_number }}</span>
    </div>

    {{-- Top Action & Header Bar --}}
    <div class="flex items-center justify-between mb-3 page-actions-header">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <a href="{{ route('material-requests.index') }}" class="btn btn-light border btn-sm" style="border-radius:6px;padding:5px 10px;font-size:11.5px;color:#475569;">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
            <div>
                <h2 class="fw-700" style="font-size:15px;color:#0f172a;margin:0;display:inline-flex;align-items:center;gap:6px;">
                    {{ $materialRequest->request_number }}
                </h2>
            </div>
            @php
                $statusBadge = match ($materialRequest->status) {
                    'draft'               => '<span class="badge" style="background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;font-size:10.5px;padding:2px 8px;border-radius:12px;font-weight:600;"><i class="fas fa-file me-1"></i>Draft</span>',
                    'submitted'           => '<span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-size:10.5px;padding:2px 8px;border-radius:12px;font-weight:600;"><i class="fas fa-clock me-1"></i>Menunggu Persetujuan</span>',
                    'approved'            => '<span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:10.5px;padding:2px 8px;border-radius:12px;font-weight:600;"><i class="fas fa-check me-1"></i>Disetujui</span>',
                    'partially_fulfilled' => '<span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-size:10.5px;padding:2px 8px;border-radius:12px;font-weight:600;"><i class="fas fa-truck me-1"></i>Sebagian Terkirim</span>',
                    'fulfilled'           => '<span class="badge" style="background:#f5f3ff;color:#6d28d9;border:1px solid #ddd6fe;font-size:10.5px;padding:2px 8px;border-radius:12px;font-weight:600;"><i class="fas fa-check-double me-1"></i>Terpenuhi</span>',
                    'rejected'            => '<span class="badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-size:10.5px;padding:2px 8px;border-radius:12px;font-weight:600;"><i class="fas fa-xmark me-1"></i>Ditolak</span>',
                    'cancelled'           => '<span class="badge" style="background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;font-size:10.5px;padding:2px 8px;border-radius:12px;font-weight:600;"><i class="fas fa-ban me-1"></i>Dibatalkan</span>',
                    default               => '<span class="badge" style="background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;font-size:10.5px;padding:2px 8px;border-radius:12px;font-weight:600;">'.$materialRequest->status.'</span>',
                };
            @endphp
            {!! $statusBadge !!}
        </div>

        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
            @php
                $hasCustomItems = $materialRequest->items->contains(fn($it) => empty($it->material_id) || !empty($it->custom_item_name));
            @endphp

            @if(in_array($materialRequest->status, ['submitted', 'approved', 'partially_fulfilled']))
                @can('create distributions')
                <a href="{{ route('distributions.create', ['material_request_id' => $materialRequest->id]) }}" class="btn btn-primary btn-sm" style="font-size:11.5px;padding:5px 12px;border-radius:6px;display:inline-flex;align-items:center;gap:5px;box-shadow:0 2px 4px rgba(37,99,235,0.2);">
                    <i class="fas fa-truck-fast"></i> Buat Surat Jalan
                </a>
                @endcan

                @if($hasCustomItems && auth()->user()->hasAnyRole(['Owner', 'Super Admin', 'Admin Gudang Pusat', 'Admin', 'Admin PO']))
                <a href="{{ route('purchase-orders.create', ['from_mr_id' => $materialRequest->id]) }}" class="btn btn-sm" style="background:#f59e0b;border-color:#d97706;color:#ffffff;font-size:11.5px;padding:5px 12px;border-radius:6px;display:inline-flex;align-items:center;gap:5px;font-weight:600;">
                    <i class="fas fa-cart-shopping"></i> Buat PO (Item Manual)
                </a>
                @endif

                @can('create material usages')
                <a href="{{ route('material-usages.create', ['warehouse_id' => $materialRequest->from_warehouse_id]) }}" class="btn btn-sm btn-light border" style="font-size:11.5px;padding:5px 12px;border-radius:6px;display:inline-flex;align-items:center;gap:5px;color:#475569;">
                    <i class="fas fa-boxes-packing"></i> Pengeluaran
                </a>
                @endcan
            @endif

            @if($materialRequest->status === 'submitted')
                @can('approve material requests')
                <form method="POST" action="{{ route('material-requests.approve', $materialRequest) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm" style="font-size:11.5px;padding:5px 12px;border-radius:6px;font-weight:600;" onclick="return confirm('Setujui permintaan ini?')">
                        <i class="fas fa-check me-1"></i> Setujui
                    </button>
                </form>
                <button type="button" class="btn btn-sm btn-outline-danger" style="font-size:11.5px;padding:5px 12px;border-radius:6px;font-weight:600;" onclick="document.getElementById('rejectModal').classList.add('show')">
                    <i class="fas fa-xmark me-1"></i> Tolak
                </button>
                @endcan
            @endif
        </div>
    </div>

    @if (session('success'))
    <div class="alert alert-success mb-3" style="border-radius:6px;padding:8px 12px;font-size:12px;">
        <i class="fas fa-circle-check"></i> {{ session('success') }}
    </div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger mb-3" style="border-radius:6px;padding:8px 12px;font-size:12px;">
        <i class="fas fa-circle-exclamation"></i> {{ session('error') }}
    </div>
    @endif

    <div class="mr-detail-layout mb-3">
        {{-- Left: Items --}}
        <div style="display:flex;flex-direction:column;gap:16px;">

            @if($hasCustomItems)
            <div class="card" style="border:1px solid #fde68a;border-radius:10px;background:#fffbeb;overflow:hidden;">
                <div class="card-body" style="padding:10px 14px;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-circle-info" style="color:#d97706;font-size:14px;"></i>
                        <span style="font-size:12px;color:#92400e;">
                            Permintaan ini memiliki <strong>item khusus/manual</strong> yang tidak ada di inventori Pusat. Item ini dapat langsung dibelikan oleh <strong>Admin PO</strong> melalui Purchase Order.
                        </span>
                    </div>
                    @if(auth()->user()->hasAnyRole(['Owner', 'Super Admin', 'Admin Gudang Pusat', 'Admin', 'Admin PO']))
                    <a href="{{ route('purchase-orders.create', ['from_mr_id' => $materialRequest->id]) }}" class="btn btn-sm btn-warning" style="font-size:11px;padding:4px 10px;border-radius:5px;font-weight:600;">
                        <i class="fas fa-cart-plus me-1"></i> Buat PO Item Manual
                    </a>
                    @endif
                </div>
            </div>
            @endif

            {{-- Item Permintaan Card --}}
            <div class="card" style="border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
                <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:10px 14px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:6px;">
                    <div style="display:flex;align-items:center;gap:6px;">
                        <i class="fas fa-boxes-stacked text-primary" style="font-size:13px;"></i>
                        <span class="card-title" style="font-size:13px;font-weight:700;color:#1e293b;">Item Permintaan</span>
                    </div>
                    <span class="badge" style="background:#eff6ff;color:#2563eb;font-weight:700;font-size:10.5px;padding:2px 8px;border-radius:12px;border:1px solid #bfdbfe;">
                        {{ $materialRequest->items->count() }} Item
                    </span>
                </div>
                <div class="table-wrap" style="overflow-x:auto;">
                    <table class="data-table mr-item-table mb-0">
                        <thead>
                            <tr>
                                <th style="width:36px;text-align:center;padding:7px 10px;">#</th>
                                <th style="padding:7px 12px;">Material</th>
                                <th style="text-align:right;padding:7px 10px;">Qty Diminta</th>
                                @if(!in_array($materialRequest->status, ['draft', 'submitted']))
                                <th style="text-align:right;padding:7px 10px;">Disetujui</th>
                                <th style="text-align:right;padding:7px 10px;">Sudah Keluar</th>
                                <th style="text-align:right;padding:7px 10px;">Sisa</th>
                                @endif
                                <th style="text-align:center;padding:7px 10px;">Satuan</th>
                                <th style="text-align:center;padding:7px 10px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($materialRequest->items as $idx => $item)
                            @php
                                $approved = (float) $item->qty_approved;
                                $fulfilled = (float) $item->qty_fulfilled;
                                $remaining = max(0, $approved - $fulfilled);
                                $unit = $item->displayUnit();
                                $isCustom = $item->isCustom();
                            @endphp
                            <tr style="border-bottom:1px solid #f1f5f9;{{ $isCustom ? 'background:#fffbeb;' : '' }}">
                                <td style="text-align:center;color:#94a3b8;font-size:11px;">{{ $idx + 1 }}</td>
                                <td style="padding:8px 12px;">
                                    <div class="fw-600" style="color:#0f172a;font-size:12.5px;">
                                        {{ $item->displayName() }}
                                        @if($isCustom)
                                        <span class="badge" style="background:#fef3c7;color:#92400e;font-size:9px;padding:1px 5px;border-radius:3px;margin-left:4px;font-weight:600;">Custom</span>
                                        @endif
                                    </div>
                                    <div class="text-muted" style="font-size:10.5px;">
                                        @if($isCustom) Manual — tidak ada di Pusat @else Kode: {{ $item->material?->code ?? '-' }} @endif
                                    </div>
                                </td>
                                <td class="fw-600" style="text-align:right;">{{ format_quantity($item->qty_requested) }}</td>
                                @if(!in_array($materialRequest->status, ['draft', 'submitted']))
                                <td class="fw-700" style="text-align:right;color:#2563eb;">{{ format_quantity($approved) }}</td>
                                <td class="fw-600 text-muted" style="text-align:right;">{{ format_quantity($fulfilled) }}</td>
                                <td class="fw-700 {{ $remaining > 0 ? 'text-success' : 'text-muted' }}" style="text-align:right;">
                                    {{ format_quantity($remaining) }}
                                </td>
                                @endif
                                <td style="text-align:center;">{{ $unit }}</td>
                                <td style="text-align:center;">
                                    @if(in_array($materialRequest->status, ['draft', 'submitted']))
                                        <span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-size:10px;padding:2px 6px;border-radius:10px;font-weight:600;">Menunggu</span>
                                    @elseif($fulfilled >= $approved && $approved > 0)
                                        <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:10px;padding:2px 6px;border-radius:10px;font-weight:600;"><i class="fas fa-check" style="font-size:8px;"></i> Terpenuhi</span>
                                    @elseif($fulfilled > 0)
                                        <span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-size:10px;padding:2px 6px;border-radius:10px;font-weight:600;">Sebagian</span>
                                    @else
                                        <span class="badge" style="background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;font-size:10px;padding:2px 6px;border-radius:10px;font-weight:600;">Belum</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($materialRequest->rejection_reason)
                <div class="card-body" style="border-top:1px solid #f1f5f9;padding:12px 14px;">
                    <div style="display:flex;align-items:flex-start;gap:10px;padding:10px 12px;background:#fef2f2;border:1px solid #fecaca;border-radius:6px;">
                        <i class="fas fa-circle-xmark" style="color:#dc2626;font-size:14px;margin-top:1px;"></i>
                        <div style="font-size:12px;color:#991b1b;"><strong>Alasan Penolakan:</strong> {{ $materialRequest->rejection_reason }}</div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Riwayat Pengeluaran Material (Material Usages) --}}
            @if($materialRequest->materialUsages->isNotEmpty())
            <div class="card" style="border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
                <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:10px 14px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:6px;">
                    <div style="display:flex;align-items:center;gap:6px;">
                        <i class="fas fa-clipboard-check text-success" style="font-size:13px;"></i>
                        <span class="card-title" style="font-size:13px;font-weight:700;color:#1e293b;">Riwayat Pengeluaran Lapangan</span>
                    </div>
                    <span class="badge" style="background:#f5f3ff;color:#6d28d9;font-weight:700;font-size:10.5px;padding:2px 8px;border-radius:12px;border:1px solid #ddd6fe;">
                        {{ $materialRequest->materialUsages->count() }} Transaksi
                    </span>
                </div>
                <div class="table-wrap" style="overflow-x:auto;">
                    <table class="data-table mr-item-table mb-0">
                        <thead>
                            <tr>
                                <th style="padding:7px 12px;">No. Bukti</th>
                                <th>Tanggal</th>
                                <th>Gudang</th>
                                <th>Penerima</th>
                                <th>Zona</th>
                                <th style="text-align:center;">Status</th>
                                <th style="text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($materialRequest->materialUsages as $usage)
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td style="padding:8px 12px;">
                                    <a href="{{ route('material-usages.show', $usage) }}" class="fw-700 text-primary" style="font-size:12px;">{{ $usage->usage_number }}</a>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($usage->usage_date)->format('d/m/Y') }}</td>
                                <td>{{ $usage->warehouse?->name ?? '-' }}</td>
                                <td><strong style="font-size:12px;">{{ $usage->recipient_name }}</strong></td>
                                <td style="color:#475569;">{{ $usage->job_section ?? '-' }}</td>
                                <td style="text-align:center;">
                                    @if($usage->status === 'completed')
                                        <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:10px;padding:2px 6px;border-radius:10px;font-weight:600;"><i class="fas fa-check" style="font-size:8px;"></i> Selesai</span>
                                    @else
                                        <span class="badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-size:10px;padding:2px 6px;border-radius:10px;font-weight:600;">Dibatalkan</span>
                                    @endif
                                </td>
                                <td style="text-align:center;">
                                    <a href="{{ route('material-usages.show', $usage) }}" class="btn btn-sm btn-light border" title="Rincian" style="width:28px;height:28px;padding:0;border-radius:5px;display:inline-flex;align-items:center;justify-content:center;color:#2563eb;background:#f8fafc;">
                                        <i class="fas fa-eye" style="font-size:11px;"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Riwayat Pengiriman Surat Jalan (Distribusi) --}}
            @if($materialRequest->distributions && $materialRequest->distributions->isNotEmpty())
            <div class="card" style="border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
                <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:10px 14px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:6px;">
                    <div style="display:flex;align-items:center;gap:6px;">
                        <i class="fas fa-truck-fast text-primary" style="font-size:13px;"></i>
                        <span class="card-title" style="font-size:13px;font-weight:700;color:#1e293b;">Riwayat Surat Jalan</span>
                    </div>
                    <span class="badge" style="background:#eff6ff;color:#2563eb;font-weight:700;font-size:10.5px;padding:2px 8px;border-radius:12px;border:1px solid #bfdbfe;">
                        {{ $materialRequest->distributions->count() }} Surat Jalan
                    </span>
                </div>
                <div class="table-wrap" style="overflow-x:auto;">
                    <table class="data-table mr-item-table mb-0">
                        <thead>
                            <tr>
                                <th style="padding:7px 12px;">No. Surat Jalan</th>
                                <th>Tgl Kirim</th>
                                <th>Pengirim</th>
                                <th>Penerima</th>
                                <th>Supir</th>
                                <th style="text-align:center;">Status</th>
                                <th style="text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($materialRequest->distributions as $dist)
                            @php
                                $distBadge = match($dist->status) {
                                    'draft'      => '<span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-size:10px;padding:2px 6px;border-radius:10px;font-weight:600;">Draft</span>',
                                    'in_transit' => '<span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-size:10px;padding:2px 6px;border-radius:10px;font-weight:600;"><i class="fas fa-truck" style="font-size:8px;"></i> Dalam Pengiriman</span>',
                                    'completed'  => '<span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:10px;padding:2px 6px;border-radius:10px;font-weight:600;"><i class="fas fa-check" style="font-size:8px;"></i> Diterima</span>',
                                    'cancelled'  => '<span class="badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-size:10px;padding:2px 6px;border-radius:10px;font-weight:600;">Dibatalkan</span>',
                                    default      => '<span class="badge" style="background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;font-size:10px;padding:2px 6px;border-radius:10px;font-weight:600;">'.$dist->status.'</span>',
                                };
                            @endphp
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td style="padding:8px 12px;">
                                    <a href="{{ route('distributions.show', $dist) }}" class="fw-700 text-primary" style="font-size:12px;">{{ $dist->distribution_number }}</a>
                                </td>
                                <td>{{ $dist->delivery_date ? \Carbon\Carbon::parse($dist->delivery_date)->format('d/m/Y') : '-' }}</td>
                                <td>{{ $dist->fromWarehouse?->name ?? '-' }}</td>
                                <td>{{ $dist->toWarehouse?->name ?? '-' }}</td>
                                <td style="color:#475569;"><strong style="font-size:12px;">{{ $dist->driver_name ?? '-' }}</strong></td>
                                <td style="text-align:center;">{!! $distBadge !!}</td>
                                <td style="text-align:center;">
                                    <a href="{{ route('distributions.show', $dist) }}" class="btn btn-sm btn-light border" title="Rincian" style="width:28px;height:28px;padding:0;border-radius:5px;display:inline-flex;align-items:center;justify-content:center;color:#2563eb;background:#f8fafc;">
                                        <i class="fas fa-eye" style="font-size:11px;"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        {{-- Right: Info Card --}}
        <div style="display:flex;flex-direction:column;gap:14px;">
            <div class="card" style="border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
                <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:10px 14px;display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-file-lines text-primary" style="font-size:13px;"></i>
                    <span class="card-title" style="font-size:13px;font-weight:700;color:#1e293b;">Informasi Permintaan</span>
                </div>
                <div class="card-body" style="padding:12px 14px;">
                    <div class="info-row">
                        <span class="info-label">No. Permintaan</span>
                        <span class="info-val">{{ $materialRequest->request_number }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Pemohon</span>
                        <span class="info-val">{{ $materialRequest->requestedBy?->name ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Gudang Asal</span>
                        <span class="info-val">{{ $materialRequest->fromWarehouse?->name ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Gudang Tujuan</span>
                        <span class="info-val">{{ $materialRequest->toWarehouse?->name ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tgl Diajukan</span>
                        <span class="info-val">{{ $materialRequest->created_at ? $materialRequest->created_at->format('d/m/Y') : '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Dibutuhkan Pada</span>
                        <span class="info-val">{{ $materialRequest->needed_at ? \Carbon\Carbon::parse($materialRequest->needed_at)->format('d/m/Y') : '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Diproses Oleh</span>
                        <span class="info-val">{{ $materialRequest->approvedBy?->name ?? '-' }}</span>
                    </div>

                    @if($materialRequest->notes)
                    <div style="margin-top:10px;padding:10px;background:#f8fafc;border-radius:6px;font-size:12px;color:#475569;">
                        <strong style="color:#334155;">Catatan:</strong><br>{{ $materialRequest->notes }}
                    </div>
                    @endif
                </div>
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
