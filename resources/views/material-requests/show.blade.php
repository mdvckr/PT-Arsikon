<x-app-layout>
    <x-slot name="title">Detail PO Lapangan: {{ $materialRequest->request_number }}</x-slot>

    @push('styles')
    <style>
        .po-hero-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 2px 8px -2px rgba(15, 23, 42, 0.05);
            overflow: hidden;
            margin-bottom: 18px;
        }

        .po-hero-header {
            padding: 16px 20px;
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .po-meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 1px;
            background: #e2e8f0;
        }

        .po-meta-item {
            background: #ffffff;
            padding: 14px 18px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            transition: background 0.15s ease;
        }

        .po-meta-item:hover {
            background: #f8fafc;
        }

        .po-meta-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }

        .po-meta-label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #64748b;
            margin-bottom: 2px;
        }

        .po-meta-val {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.35;
            word-break: break-word;
        }

        .po-meta-sub {
            font-size: 11px;
            color: #64748b;
            margin-top: 2px;
            font-weight: 500;
        }

        .table-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 2px 8px -2px rgba(15, 23, 42, 0.05);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .table-card-header {
            padding: 14px 18px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }

        .mr-item-table th {
            padding: 10px 12px !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.4px !important;
            color: #475569 !important;
            background: #f8fafc !important;
            border-bottom: 1px solid #cbd5e1 !important;
            white-space: nowrap !important;
        }

        .mr-item-table td {
            padding: 10px 12px !important;
            vertical-align: middle !important;
            font-size: 12.5px !important;
            border-bottom: 1px solid #f1f5f9 !important;
        }

        .mr-item-table tbody tr:hover {
            background-color: #f8fafc;
        }

        .mr-item-table tbody tr.row-custom {
            background-color: #fffdf7;
        }

        .mr-item-table tbody tr.row-custom:hover {
            background-color: #fffbeb;
        }

        .badge-status-lg {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .alert-card-action {
            border-radius: 10px;
            padding: 14px 18px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .po-hero-header {
                flex-direction: column;
                align-items: flex-start !important;
            }
            .po-meta-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    @endpush

    {{-- Breadcrumb --}}
    <div class="breadcrumb mb-3" style="font-size:12.5px;">
        <a href="{{ route('material-requests.index') }}" style="color:#64748b;text-decoration:none;">PO Lapangan</a>
        <span class="breadcrumb-sep" style="margin:0 6px;color:#94a3b8;"><i class="fas fa-chevron-right" style="font-size:9px;"></i></span>
        <span style="font-weight:600;color:#0f172a;">{{ $materialRequest->request_number }}</span>
    </div>

    @php
        $hasPurchaseItems = $materialRequest->items->contains(fn($it) => $it->fulfillment === 'purchase' && ((float)$it->qty_approved - (float)$it->qty_ordered) > 0);
        $hasCustomItems = $materialRequest->items->contains(fn($it) => empty($it->material_id) || !empty($it->custom_item_name));
        $canCreatePo = auth()->user()->hasAnyRole(['Owner', 'Admin Pusat', 'Admin Gudang Pusat', 'Admin PO']) || auth()->user()->can('create purchase orders');
        $relatedPos = $materialRequest->items->flatMap->purchaseOrderItems->map->purchaseOrder->filter()->unique('id');
        $sentPo = $relatedPos->firstWhere('status', 'sent') ?? $relatedPos->firstWhere('status', 'partial_received') ?? $relatedPos->first();
        $inTransitDist = $materialRequest->distributions->firstWhere('status', 'in_transit');
        $canReceiveGr = auth()->user()->can('create goods receipts');
        $canReceiveDist = $inTransitDist && $inTransitDist->canUserReceive(auth()->user());
        $canEdit = in_array($materialRequest->status, ['submitted', 'draft']) &&
            ($materialRequest->requested_by_user_id === auth()->id() || auth()->user()->hasAnyRole(['Owner', 'Admin Pusat']));

        $statusBadge = match ($materialRequest->status) {
            'draft'               => '<span class="badge-status-lg" style="background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;"><i class="fas fa-file-lines"></i> Draft</span>',
            'submitted'           => '<span class="badge-status-lg" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;"><i class="fas fa-hourglass-half"></i> Menunggu Persetujuan</span>',
            'approved'            => '<span class="badge-status-lg" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;"><i class="fas fa-circle-check"></i> Disetujui</span>',
            'partially_fulfilled' => '<span class="badge-status-lg" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;"><i class="fas fa-truck-ramp-box"></i> Sebagian Terkirim</span>',
            'fulfilled'           => '<span class="badge-status-lg" style="background:#f5f3ff;color:#6d28d9;border:1px solid #ddd6fe;"><i class="fas fa-check-double"></i> Terpenuhi</span>',
            'rejected'            => '<span class="badge-status-lg" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;"><i class="fas fa-circle-xmark"></i> Ditolak</span>',
            'cancelled'           => '<span class="badge-status-lg" style="background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;"><i class="fas fa-ban"></i> Dibatalkan</span>',
            default               => '<span class="badge-status-lg" style="background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;">'.$materialRequest->status.'</span>',
        };
    @endphp

    {{-- Flash Notifications --}}
    @if (session('success'))
    <div class="alert alert-success mb-3" style="border-radius:8px;padding:10px 14px;font-size:12.5px;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-circle-check text-success"></i> <span>{{ session('success') }}</span>
    </div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger mb-3" style="border-radius:8px;padding:10px 14px;font-size:12.5px;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-circle-exclamation text-danger"></i> <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- TOP HERO CARD: Header & Document Metadata Overview --}}
    <div class="po-hero-card">
        <div class="po-hero-header">
            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                <a href="{{ route('material-requests.index') }}" class="btn btn-light border btn-sm" style="border-radius:6px;padding:6px 12px;font-size:12px;color:#475569;font-weight:600;">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span style="font-size:12px;color:#64748b;font-weight:600;">PO Lapangan:</span>
                    <h1 class="fw-800" style="font-size:18px;color:#0f172a;margin:0;letter-spacing:-0.3px;">
                        {{ $materialRequest->request_number }}
                    </h1>
                </div>
                {!! $statusBadge !!}
            </div>

            {{-- Action Buttons Toolbar --}}
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                {{-- Terima Surat Jalan (Distribusi In-Transit) --}}
                @if($inTransitDist && $canReceiveDist)
                    <a href="{{ route('distributions.show', $inTransitDist) }}" class="btn btn-sm btn-success" style="font-size:12px;padding:6px 14px;border-radius:6px;display:inline-flex;align-items:center;gap:6px;font-weight:700;box-shadow:0 2px 6px rgba(16,185,129,0.25);">
                        <i class="fas fa-boxes-packing"></i> Terima Surat Jalan (#{{ $inTransitDist->distribution_number }})
                    </a>
                @endif

                {{-- Terima Barang Masuk (GR) --}}
                @if(in_array($materialRequest->status, ['approved', 'partially_fulfilled']) && $canReceiveGr)
                    <a href="{{ route('goods-receipts.create', array_filter(['from_mr_id' => $materialRequest->id, 'purchase_order_id' => $sentPo?->id])) }}" class="btn btn-sm btn-primary" style="font-size:12px;padding:6px 14px;border-radius:6px;display:inline-flex;align-items:center;gap:6px;font-weight:700;box-shadow:0 2px 6px rgba(37,99,235,0.25);">
                        <i class="fas fa-dolly"></i> Terima Barang Masuk (GR)
                    </a>
                @endif

                {{-- Buat PO ke Vendor / PO Item Manual --}}
                @if(in_array($materialRequest->status, ['approved', 'partially_fulfilled']) && $hasPurchaseItems && $canCreatePo)
                    <a href="{{ route('purchase-orders.create', ['from_mr_id' => $materialRequest->id]) }}" class="btn btn-sm" style="background:#f59e0b;border:1px solid #d97706;color:#ffffff;font-size:12px;padding:6px 14px;border-radius:6px;display:inline-flex;align-items:center;gap:6px;font-weight:700;box-shadow:0 2px 6px rgba(245,158,11,0.25);">
                        <i class="fas fa-cart-shopping"></i> Buat PO ke Vendor
                    </a>
                @elseif($hasCustomItems && $canCreatePo)
                    <a href="{{ route('purchase-orders.create', ['from_mr_id' => $materialRequest->id]) }}" class="btn btn-sm" style="background:#f59e0b;border:1px solid #d97706;color:#ffffff;font-size:12px;padding:6px 14px;border-radius:6px;display:inline-flex;align-items:center;gap:6px;font-weight:700;box-shadow:0 2px 6px rgba(245,158,11,0.25);">
                        <i class="fas fa-cart-shopping"></i> Buat PO (Item Manual)
                    </a>
                @endif

                {{-- Edit PO Lapangan --}}
                @if($canEdit)
                    <a href="{{ route('material-requests.edit', $materialRequest) }}" class="btn btn-sm btn-outline-primary" style="font-size:12px;padding:6px 12px;border-radius:6px;display:inline-flex;align-items:center;gap:5px;font-weight:600;">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                @endif

                {{-- Print PDF --}}
                <a href="{{ route('material-requests.show', $materialRequest) }}?print=1" target="_blank" class="btn btn-sm btn-dark" style="font-size:12px;padding:6px 12px;border-radius:6px;display:inline-flex;align-items:center;gap:5px;font-weight:600;">
                    <i class="fas fa-print"></i> Cetak PDF
                </a>

                {{-- Persetujuan (Approve / Reject) --}}
                @if($materialRequest->status === 'submitted')
                    @can('approve material requests')
                    <form method="POST" action="{{ route('material-requests.approve', $materialRequest) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm" style="font-size:12px;padding:6px 14px;border-radius:6px;font-weight:700;box-shadow:0 2px 6px rgba(16,185,129,0.25);" onclick="return confirm('Setujui PO Lapangan ini?')">
                            <i class="fas fa-check me-1"></i> Setujui
                        </button>
                    </form>
                    <button type="button" class="btn btn-sm btn-outline-danger" style="font-size:12px;padding:6px 12px;border-radius:6px;font-weight:600;" onclick="document.getElementById('rejectModal').classList.add('show')">
                        <i class="fas fa-xmark me-1"></i> Tolak
                    </button>
                    @endcan
                @endif
            </div>
        </div>

        {{-- Meta Grid: 6 Info Cards --}}
        <div class="po-meta-grid">
            {{-- Proyek / Pemohon --}}
            <div class="po-meta-item">
                <div class="po-meta-icon" style="background:#eff6ff;color:#2563eb;">
                    <i class="fas fa-building"></i>
                </div>
                <div>
                    <div class="po-meta-label">Nama Proyek / Pemohon</div>
                    <div class="po-meta-val">
                        {{ $materialRequest->toWarehouse?->project ? $materialRequest->toWarehouse->project->name : ($materialRequest->toWarehouse?->name ?? '-') }}
                    </div>
                    <div class="po-meta-sub">
                        Gudang: {{ $materialRequest->toWarehouse?->name ?? '-' }}
                    </div>
                </div>
            </div>

            {{-- Sumber Material --}}
            <div class="po-meta-item">
                <div class="po-meta-icon" style="background:#fffbeb;color:#d97706;">
                    <i class="fas fa-warehouse"></i>
                </div>
                <div>
                    <div class="po-meta-label">Sumber Material (Kepada)</div>
                    <div class="po-meta-val">
                        {{ $materialRequest->fromWarehouse ? $materialRequest->fromWarehouse->name : 'Pengadaan / Beli ke Vendor' }}
                    </div>
                    <div class="po-meta-sub">
                        {{ $materialRequest->fromWarehouse ? ($materialRequest->fromWarehouse->is_central ? 'Gudang Pusat' : 'Gudang Proyek') : 'Jalur Pembelian Supplier' }}
                    </div>
                </div>
            </div>

            {{-- Pemohon & Role --}}
            <div class="po-meta-item">
                <div class="po-meta-icon" style="background:#f1f5f9;color:#475569;">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div>
                    <div class="po-meta-label">Pemohon</div>
                    <div class="po-meta-val">
                        {{ $materialRequest->requestedBy?->name ?? '-' }}
                    </div>
                    <div class="po-meta-sub">
                        Diajukan: <span class="badge" style="background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;padding:1px 6px;border-radius:4px;font-size:10px;">{{ $materialRequest->requested_as ?? 'Staff Proyek' }}</span>
                    </div>
                </div>
            </div>

            {{-- Tanggal Order & Dibutuhkan --}}
            <div class="po-meta-item">
                <div class="po-meta-icon" style="background:#ecfdf5;color:#059669;">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div>
                    <div class="po-meta-label">Dibutuhkan Tgl (Jadwal Kebutuhan)</div>
                    <div class="po-meta-val" style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                        {{ $materialRequest->needed_at ? \Carbon\Carbon::parse($materialRequest->needed_at)->format('d/m/Y') : '-' }}
                        @if($materialRequest->is_overdue)
                            <span class="badge" style="background:#fef2f2;color:#dc2626;border:1px solid #fca5a5;font-size:10px;padding:2px 6px;border-radius:10px;font-weight:700;">
                                <i class="fas fa-clock me-1"></i>Lewat Tanggal / Terlambat
                            </span>
                        @endif
                    </div>
                    <div class="po-meta-sub">
                        Tanggal Order: {{ $materialRequest->created_at ? $materialRequest->created_at->format('d/m/Y') : '-' }}
                    </div>
                </div>
            </div>

            {{-- Diproses Oleh --}}
            <div class="po-meta-item">
                <div class="po-meta-icon" style="background:#f5f3ff;color:#7c3aed;">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div>
                    <div class="po-meta-label">Diproses / Disetujui Oleh</div>
                    <div class="po-meta-val">
                        {{ $materialRequest->approvedBy?->name ?? 'Menunggu Persetujuan' }}
                    </div>
                    <div class="po-meta-sub">
                        @if($materialRequest->approved_at)
                            Pada: {{ \Carbon\Carbon::parse($materialRequest->approved_at)->format('d/m/Y H:i') }}
                        @else
                            Status: {{ ucfirst($materialRequest->status) }}
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Catatan Pengajuan (Jika Ada) --}}
        @if($materialRequest->notes)
        <div style="padding:12px 20px;background:#f8fafc;border-top:1px solid #e2e8f0;display:flex;align-items:flex-start;gap:10px;">
            <i class="fas fa-comment-dots text-primary" style="margin-top:2px;font-size:13px;"></i>
            <div style="font-size:12px;color:#334155;">
                <strong style="color:#0f172a;">Catatan Tambahan Pemohon:</strong> {{ $materialRequest->notes }}
            </div>
        </div>
        @endif
    </div>

    {{-- Alert In-Transit: Sedang Dikirim dari Gudang Pusat --}}
    @if($inTransitDist)
    <div class="alert-card-action" style="background:linear-gradient(90deg, #ecfdf5 0%, #f0fdf4 100%);border:1.5px solid #a7f3d0;box-shadow:0 2px 8px rgba(16,185,129,0.08);">
        <div style="display:flex;align-items:center;gap:14px;">
            <div style="width:42px;height:42px;border-radius:10px;background:#10b981;color:#ffffff;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;box-shadow:0 2px 6px rgba(16,185,129,0.3);">
                <i class="fas fa-truck-fast"></i>
            </div>
            <div>
                <div style="font-size:14px;font-weight:700;color:#065f46;">
                    Material Sedang Dalam Pengiriman dari Gudang Pusat (Surat Jalan #{{ $inTransitDist->distribution_number }})
                </div>
                <div style="font-size:12px;color:#047857;margin-top:2px;">
                    Supir: <strong>{{ $inTransitDist->driver_name ?? '-' }}</strong> (Plat: {{ $inTransitDist->vehicle_number ?? '-' }}) &bull; Tgl Kirim: {{ $inTransitDist->delivery_date ? \Carbon\Carbon::parse($inTransitDist->delivery_date)->format('d/m/Y') : '-' }}
                </div>
            </div>
        </div>
        @if($canReceiveDist)
        <a href="{{ route('distributions.show', $inTransitDist) }}" class="btn btn-success" style="padding:8px 18px;font-size:12.5px;font-weight:700;border-radius:8px;box-shadow:0 2px 8px rgba(16,185,129,0.3);">
            <i class="fas fa-boxes-packing me-1"></i> Terima Barang Sekarang
        </a>
        @endif
    </div>
    @endif

    {{-- Alert Item Purchase / Item Manual --}}
    @if(in_array($materialRequest->status, ['approved', 'partially_fulfilled']) && $hasPurchaseItems)
    <div class="alert-card-action" style="background:linear-gradient(90deg, #fffbeb 0%, #fefce8 100%);border:1.5px solid #fde68a;box-shadow:0 2px 8px rgba(245,158,11,0.08);">
        <div style="display:flex;align-items:center;gap:14px;">
            <div style="width:40px;height:40px;border-radius:10px;background:#f59e0b;color:#ffffff;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;">
                <i class="fas fa-cart-shopping"></i>
            </div>
            <div>
                <div style="font-size:13.5px;font-weight:700;color:#92400e;">
                    PO Lapangan Memerlukan Penerbitan Purchase Order ke Vendor
                </div>
                <div style="font-size:12px;color:#b45309;margin-top:2px;">
                    Terdapat material dengan jalur pembelian vendor yang belum selesai dipesan. Admin PO dapat langsung membuat PO resmi ke supplier tujuan.
                </div>
            </div>
        </div>
        @if($canCreatePo)
        <a href="{{ route('purchase-orders.create', ['from_mr_id' => $materialRequest->id]) }}" class="btn" style="background:#f59e0b;border:1px solid #d97706;color:#ffffff;font-size:12px;padding:7px 16px;border-radius:8px;font-weight:700;">
            <i class="fas fa-cart-plus me-1"></i> Terbitkan PO Vendor
        </a>
        @endif
    </div>
    @elseif($hasCustomItems)
    <div class="alert-card-action" style="background:linear-gradient(90deg, #fffbeb 0%, #fefce8 100%);border:1.5px solid #fde68a;box-shadow:0 2px 8px rgba(245,158,11,0.08);">
        <div style="display:flex;align-items:center;gap:14px;">
            <div style="width:40px;height:40px;border-radius:10px;background:#f59e0b;color:#ffffff;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;">
                <i class="fas fa-circle-info"></i>
            </div>
            <div>
                <div style="font-size:13.5px;font-weight:700;color:#92400e;">
                    PO Lapangan Mengandung Item Khusus / Input Manual Proyek
                </div>
                <div style="font-size:12px;color:#b45309;margin-top:2px;">
                    Item ini tidak ada di master katalog Gudang Pusat. Admin PO dapat langsung menerbitkan Purchase Order untuk membelikan barang tersebut.
                </div>
            </div>
        </div>
        @if($canCreatePo)
        <a href="{{ route('purchase-orders.create', ['from_mr_id' => $materialRequest->id]) }}" class="btn" style="background:#f59e0b;border:1px solid #d97706;color:#ffffff;font-size:12px;padding:7px 16px;border-radius:8px;font-weight:700;">
            <i class="fas fa-cart-plus me-1"></i> Buat PO Item Manual
        </a>
        @endif
    </div>
    @endif

    {{-- Alert Rejection Reason (Bila Ditolak) --}}
    @if($materialRequest->rejection_reason)
    <div class="alert-card-action" style="background:#fef2f2;border:1.5px solid #fecaca;box-shadow:0 2px 8px rgba(239,68,68,0.08);">
        <div style="display:flex;align-items:center;gap:14px;">
            <div style="width:40px;height:40px;border-radius:10px;background:#ef4444;color:#ffffff;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;">
                <i class="fas fa-circle-xmark"></i>
            </div>
            <div>
                <div style="font-size:13.5px;font-weight:700;color:#991b1b;">
                    Permintaan PO Lapangan Ditolak
                </div>
                <div style="font-size:12.5px;color:#b91c1c;margin-top:2px;">
                    <strong>Alasan:</strong> {{ $materialRequest->rejection_reason }}
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- MAIN TABLE CARD: Rincian Item PO Lapangan (Full Width) --}}
    <div class="table-card">
        <div class="table-card-header">
            <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:28px;height:28px;border-radius:6px;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:13px;">
                    <i class="fas fa-boxes-stacked"></i>
                </div>
                <div>
                    <span style="font-size:14px;font-weight:700;color:#0f172a;">Rincian Item PO Lapangan</span>
                    <span class="text-muted" style="font-size:11.5px;margin-left:6px;">(Daftar barang &amp; alokasi penggunaan)</span>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <span class="badge" style="background:#eff6ff;color:#2563eb;font-weight:700;font-size:11.5px;padding:4px 10px;border-radius:12px;border:1px solid #bfdbfe;">
                    {{ $materialRequest->items->count() }} Item Diminta
                </span>
            </div>
        </div>

        <div class="table-wrap" style="overflow-x:auto;">
            <table class="data-table mr-item-table mb-0">
                <thead>
                    <tr>
                        <th style="width:40px;text-align:center;">#</th>
                        <th style="min-width:200px;">Nama Material</th>
                        <th style="min-width:120px;">Kategori</th>
                        <th style="min-width:130px;">Spesifikasi</th>
                        <th style="text-align:right;width:95px;">Qty Diminta</th>
                        <th style="text-align:right;width:95px;">Disetujui</th>
                        <th style="text-align:center;width:75px;">Satuan</th>
                        <th style="text-align:center;width:105px;">Tgl Pakai</th>
                        <th style="min-width:130px;">Alokasi / Zona</th>
                        <th style="min-width:140px;text-align:center;">Stok Tersedia</th>
                        <th style="text-align:center;width:110px;">Status Tiba</th>
                        <th style="min-width:130px;">Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($materialRequest->items as $idx => $item)
                    @php
                        $approved = (float) $item->qty_approved;
                        $received = (float) $item->qty_received;
                        $remaining = max(0, $approved - $received);
                        $unit = $item->displayUnit();
                        $isCustom = $item->isCustom();
                    @endphp
                    <tr class="{{ $isCustom ? 'row-custom' : '' }}">
                        <td style="text-align:center;color:#94a3b8;font-weight:600;font-size:11px;">{{ $idx + 1 }}</td>
                        <td>
                            <div class="fw-700" style="color:#0f172a;font-size:13px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                {{ $item->displayName() }}
                                @if($isCustom)
                                <span class="badge" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;font-size:9.5px;padding:2px 6px;border-radius:4px;font-weight:700;">
                                    <i class="fas fa-tag me-1"></i>Manual
                                </span>
                                @endif
                            </div>
                            <div class="text-muted" style="font-size:11px;margin-top:2px;">
                                @if($isCustom)
                                    <span style="color:#b45309;">Manual &mdash; tidak ada di katalog Pusat</span>
                                @else
                                    <span style="font-family:ui-monospace,SFMono-Regular,Consolas,monospace;color:#64748b;">SKU: {{ $item->material?->sku ?? '-' }}</span>
                                    @if($item->material?->code)
                                    &bull; Kode: {{ $item->material->code }}
                                    @endif
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="badge" style="background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;font-size:10.5px;padding:3px 8px;border-radius:6px;font-weight:600;">
                                {{ $item->displayCategoryName() }}
                            </span>
                            @if($item->custom_item_type || $item->material?->type)
                                <div style="font-size:10.5px;color:#64748b;margin-top:3px;display:flex;align-items:center;gap:4px;">
                                    <i class="fas fa-layer-group" style="font-size:9.5px;color:#94a3b8;"></i>
                                    <span>{{ $item->custom_item_type ?: $item->material?->type }}</span>
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($item->specification)
                                <span style="color:#0f172a;font-weight:500;">{{ $item->specification }}</span>
                            @else
                                <span class="text-muted" style="font-size:11px;">-</span>
                            @endif
                        </td>
                        <td class="fw-700" style="text-align:right;color:#0f172a;font-size:13px;">
                            {{ format_quantity($item->qty_requested) }}
                        </td>
                        <td class="fw-800" style="text-align:right;color:#2563eb;font-size:13px;">
                            {{ in_array($materialRequest->status, ['draft', 'submitted']) ? '-' : format_quantity($approved) }}
                        </td>
                        <td style="text-align:center;font-weight:600;color:#475569;">
                            {{ $unit }}
                        </td>
                        <td style="text-align:center;font-size:12px;color:#334155;">
                            @if($item->usage_date)
                                <span class="badge" style="background:#f8fafc;color:#334155;border:1px solid #e2e8f0;padding:3px 7px;border-radius:4px;">
                                    <i class="fas fa-calendar-day me-1 text-muted" style="font-size:10px;"></i>{{ \Carbon\Carbon::parse($item->usage_date)->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($item->usage_location)
                                <span style="color:#334155;font-weight:500;"><i class="fas fa-location-dot me-1 text-primary" style="font-size:10px;"></i>{{ $item->usage_location }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            @if($item->fulfillment === 'purchase')
                                @php
                                    $approvedQty = (float) $item->qty_approved;
                                    $orderedQty  = (float) $item->qty_ordered;
                                    $remainingOrder = max(0, $approvedQty - $orderedQty);
                                    $relatedPos = $item->purchaseOrderItems ? $item->purchaseOrderItems->map->purchaseOrder->filter()->unique('id') : collect();
                                @endphp
                                <div style="font-weight:700;color:#d97706;font-size:11.5px;">
                                    Sisa dipesan: {{ format_quantity($remainingOrder) }} {{ $unit }}
                                </div>
                                @if($relatedPos->isNotEmpty())
                                    <div style="font-size:10.5px;margin-top:3px;display:flex;gap:4px;justify-content:center;flex-wrap:wrap;">
                                        @foreach($relatedPos as $po)
                                            <a href="{{ route('purchase-orders.show', $po) }}" class="badge" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;text-decoration:none;" title="Lihat PO">
                                                <i class="fas fa-file-invoice me-1" style="font-size:9px;"></i>{{ $po->po_number }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            @elseif($item->material_id)
                                @php
                                    $inv = isset($sourceInventories) ? $sourceInventories->get($item->material_id) : null;
                                    $qtyOnHand = $inv ? (float) $inv->quantity : 0;
                                    $qtyAllocated = $inv ? (float) ($inv->qty_allocated ?? 0) : 0;
                                    $availableStock = max(0, $qtyOnHand - $qtyAllocated);
                                    $targetQty = (float) ($item->qty_approved > 0 ? $item->qty_approved : $item->qty_requested);
                                    $isSufficient = $availableStock >= $targetQty && $targetQty > 0;
                                @endphp
                                <div style="display:flex;flex-direction:column;align-items:center;gap:2px;">
                                    <span style="font-weight:700;color:#0f172a;font-size:12px;">
                                        {{ format_quantity($availableStock) }} {{ $unit }}
                                    </span>
                                    @if($isSufficient)
                                        <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:10px;padding:2px 7px;border-radius:10px;font-weight:600;">
                                            <i class="fas fa-check me-1" style="font-size:8.5px;"></i>Cukup
                                        </span>
                                    @else
                                        <span class="badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-size:10px;padding:2px 7px;border-radius:10px;font-weight:600;">
                                            <i class="fas fa-triangle-exclamation me-1" style="font-size:8.5px;"></i>Kurang
                                        </span>
                                    @endif
                                </div>
                            @else
                                <span class="text-muted" style="font-size:11px;">-</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            @if(in_array($materialRequest->status, ['draft', 'submitted']))
                                <span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-size:10.5px;padding:3px 8px;border-radius:10px;font-weight:600;">Menunggu</span>
                            @elseif($received >= $approved && $approved > 0)
                                <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:10.5px;padding:3px 8px;border-radius:10px;font-weight:700;"><i class="fas fa-check" style="font-size:9px;"></i> Tiba Lengkap</span>
                            @elseif($received > 0)
                                <span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-size:10.5px;padding:3px 8px;border-radius:10px;font-weight:600;">Tiba ({{ format_quantity($received) }})</span>
                            @else
                                <span class="badge" style="background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;font-size:10.5px;padding:3px 8px;border-radius:10px;font-weight:600;">Belum Tiba</span>
                            @endif
                        </td>
                        <td style="font-size:12px;color:#475569;">
                            {{ $item->notes ?: '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- SECTION RIWAYAT TRANSAKSI TERKAIT --}}
    @php
        $directGrs = $materialRequest->goodsReceipts ?? collect();
        $poGrs = $relatedPos->flatMap->goodsReceipts ?? collect();
        $allGrs = $directGrs->merge($poGrs)->unique('id')->sortByDesc('created_at');
    @endphp

    {{-- 1. Riwayat Surat Jalan (Distribusi) --}}
    @if($materialRequest->distributions && $materialRequest->distributions->isNotEmpty())
    <div class="table-card">
        <div class="table-card-header">
            <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:28px;height:28px;border-radius:6px;background:#ecfdf5;color:#059669;display:flex;align-items:center;justify-content:center;font-size:13px;">
                    <i class="fas fa-truck-fast"></i>
                </div>
                <div>
                    <span style="font-size:14px;font-weight:700;color:#0f172a;">Riwayat Pengiriman Surat Jalan</span>
                    <span class="text-muted" style="font-size:11.5px;margin-left:6px;">(Distribusi logistik antar gudang/proyek)</span>
                </div>
            </div>
            <span class="badge" style="background:#ecfdf5;color:#047857;font-weight:700;font-size:11px;padding:3px 9px;border-radius:12px;border:1px solid #a7f3d0;">
                {{ $materialRequest->distributions->count() }} Surat Jalan
            </span>
        </div>
        <div class="table-wrap" style="overflow-x:auto;">
            <table class="data-table mr-item-table mb-0">
                <thead>
                    <tr>
                        <th style="padding:8px 12px;">No. Surat Jalan</th>
                        <th>Tgl Kirim</th>
                        <th>Gudang Asal</th>
                        <th>Gudang Tujuan</th>
                        <th>Supir / Kendaraan</th>
                        <th style="text-align:center;">Status</th>
                        <th style="text-align:center;width:80px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($materialRequest->distributions as $dist)
                    @php
                        $distBadge = match($dist->status) {
                            'draft'      => '<span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-size:10px;padding:3px 7px;border-radius:10px;font-weight:600;">Draft</span>',
                            'in_transit' => '<span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-size:10px;padding:3px 7px;border-radius:10px;font-weight:700;"><i class="fas fa-truck me-1"></i> Dalam Pengiriman</span>',
                            'completed'  => '<span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:10px;padding:3px 7px;border-radius:10px;font-weight:700;"><i class="fas fa-check me-1"></i> Diterima</span>',
                            'cancelled'  => '<span class="badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-size:10px;padding:3px 7px;border-radius:10px;font-weight:600;">Dibatalkan</span>',
                            default      => '<span class="badge" style="background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;font-size:10px;padding:3px 7px;border-radius:10px;font-weight:600;">'.$dist->status.'</span>',
                        };
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ route('distributions.show', $dist) }}" class="fw-700 text-primary" style="font-size:12.5px;text-decoration:none;">
                                {{ $dist->distribution_number }}
                            </a>
                        </td>
                        <td>{{ $dist->delivery_date ? \Carbon\Carbon::parse($dist->delivery_date)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $dist->fromWarehouse?->name ?? '-' }}</td>
                        <td>{{ $dist->toWarehouse?->name ?? '-' }}</td>
                        <td>
                            <strong style="font-size:12px;color:#0f172a;">{{ $dist->driver_name ?? '-' }}</strong>
                            @if($dist->vehicle_number)
                                <span class="text-muted" style="font-size:11px;">({{ $dist->vehicle_number }})</span>
                            @endif
                        </td>
                        <td style="text-align:center;">{!! $distBadge !!}</td>
                        <td style="text-align:center;">
                            <a href="{{ route('distributions.show', $dist) }}" class="btn btn-sm btn-light border" title="Lihat Rincian Surat Jalan" style="padding:4px 10px;border-radius:6px;font-size:11.5px;color:#2563eb;font-weight:600;">
                                <i class="fas fa-eye me-1"></i> Buka
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- 2. Riwayat Penerimaan Barang Masuk (Goods Receipts) --}}
    @if($allGrs->isNotEmpty())
    <div class="table-card">
        <div class="table-card-header">
            <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:28px;height:28px;border-radius:6px;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:13px;">
                    <i class="fas fa-dolly"></i>
                </div>
                <div>
                    <span style="font-size:14px;font-weight:700;color:#0f172a;">Riwayat Penerimaan Barang Masuk (GR)</span>
                    <span class="text-muted" style="font-size:11.5px;margin-left:6px;">(Penerimaan material di gudang proyek)</span>
                </div>
            </div>
            <span class="badge" style="background:#eff6ff;color:#2563eb;font-weight:700;font-size:11px;padding:3px 9px;border-radius:12px;border:1px solid #bfdbfe;">
                {{ $allGrs->count() }} Dokumen GR
            </span>
        </div>
        <div class="table-wrap" style="overflow-x:auto;">
            <table class="data-table mr-item-table mb-0">
                <thead>
                    <tr>
                        <th style="padding:8px 12px;">No. Dokumen GR</th>
                        <th>Tgl Terima</th>
                        <th>Gudang Penerima</th>
                        <th>Pemasok / Referensi PO</th>
                        <th>Penerima</th>
                        <th style="text-align:center;">Status</th>
                        <th style="text-align:center;width:80px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allGrs as $gr)
                    <tr>
                        <td>
                            <a href="{{ route('goods-receipts.show', $gr) }}" class="fw-700 text-primary" style="font-size:12.5px;text-decoration:none;">
                                {{ $gr->receipt_number }}
                            </a>
                        </td>
                        <td>{{ $gr->receipt_date ? \Carbon\Carbon::parse($gr->receipt_date)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $gr->warehouse?->name ?? '-' }}</td>
                        <td>
                            <span style="font-weight:600;color:#334155;">{{ $gr->supplier?->name ?? ($gr->supplier_name ?? '-') }}</span>
                            @if($gr->purchaseOrder)
                                <div style="font-size:11px;color:#2563eb;">(PO: {{ $gr->purchaseOrder->po_number }})</div>
                            @endif
                        </td>
                        <td><strong style="font-size:12px;color:#0f172a;">{{ $gr->received_by_name ?? ($gr->receivedBy?->name ?? '-') }}</strong></td>
                        <td style="text-align:center;">
                            @if($gr->status === 'confirmed')
                                <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:10px;padding:3px 7px;border-radius:10px;font-weight:700;"><i class="fas fa-check me-1"></i> Dikonfirmasi</span>
                            @else
                                <span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-size:10px;padding:3px 7px;border-radius:10px;font-weight:600;">Draft</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            <a href="{{ route('goods-receipts.show', $gr) }}" class="btn btn-sm btn-light border" title="Lihat Rincian GR" style="padding:4px 10px;border-radius:6px;font-size:11.5px;color:#2563eb;font-weight:600;">
                                <i class="fas fa-eye me-1"></i> Buka
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- 3. Riwayat Pengeluaran Material di Lapangan (Material Usages) --}}
    @if($materialRequest->materialUsages && $materialRequest->materialUsages->isNotEmpty())
    <div class="table-card">
        <div class="table-card-header">
            <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:28px;height:28px;border-radius:6px;background:#f5f3ff;color:#7c3aed;display:flex;align-items:center;justify-content:center;font-size:13px;">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div>
                    <span style="font-size:14px;font-weight:700;color:#0f172a;">Riwayat Pengeluaran Material Proyek</span>
                    <span class="text-muted" style="font-size:11.5px;margin-left:6px;">(Pemakaian material terpakai di lapangan)</span>
                </div>
            </div>
            <span class="badge" style="background:#f5f3ff;color:#6d28d9;font-weight:700;font-size:11px;padding:3px 9px;border-radius:12px;border:1px solid #ddd6fe;">
                {{ $materialRequest->materialUsages->count() }} Pengeluaran
            </span>
        </div>
        <div class="table-wrap" style="overflow-x:auto;">
            <table class="data-table mr-item-table mb-0">
                <thead>
                    <tr>
                        <th style="padding:8px 12px;">No. Bukti Pengeluaran</th>
                        <th>Tanggal</th>
                        <th>Gudang</th>
                        <th>Penerima / Mandor</th>
                        <th>Bagian / Zona</th>
                        <th style="text-align:center;">Status</th>
                        <th style="text-align:center;width:80px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($materialRequest->materialUsages as $usage)
                    <tr>
                        <td>
                            <a href="{{ route('material-usages.show', $usage) }}" class="fw-700 text-primary" style="font-size:12.5px;text-decoration:none;">
                                {{ $usage->usage_number }}
                            </a>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($usage->usage_date)->format('d/m/Y') }}</td>
                        <td>{{ $usage->warehouse?->name ?? '-' }}</td>
                        <td><strong style="font-size:12px;color:#0f172a;">{{ $usage->recipient_name }}</strong></td>
                        <td style="color:#475569;">{{ $usage->job_section ?? '-' }}</td>
                        <td style="text-align:center;">
                            @if($usage->status === 'completed')
                                <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:10px;padding:3px 7px;border-radius:10px;font-weight:700;"><i class="fas fa-check me-1"></i> Selesai</span>
                            @else
                                <span class="badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-size:10px;padding:3px 7px;border-radius:10px;font-weight:600;">Dibatalkan</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            <a href="{{ route('material-usages.show', $usage) }}" class="btn btn-sm btn-light border" title="Lihat Rincian Pengeluaran" style="padding:4px 10px;border-radius:6px;font-size:11.5px;color:#2563eb;font-weight:600;">
                                <i class="fas fa-eye me-1"></i> Buka
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Reject Modal --}}
    <div class="modal-overlay" id="rejectModal">
        <div class="modal-box" style="border-radius:12px;overflow:hidden;max-width:480px;">
            <div class="modal-header" style="background:#fef2f2;border-bottom:1px solid #fecaca;padding:14px 18px;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-circle-xmark text-danger" style="font-size:16px;"></i>
                    <span class="modal-title" style="font-size:14px;font-weight:700;color:#991b1b;">Tolak PO Lapangan</span>
                </div>
                <button class="btn-close-modal" onclick="document.getElementById('rejectModal').classList.remove('show')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('material-requests.reject', $materialRequest) }}">
                @csrf
                <div class="modal-body" style="padding:18px;">
                    <label class="form-label" style="font-weight:600;font-size:12px;color:#334155;margin-bottom:6px;">
                        Alasan Penolakan <span class="text-danger">*</span>
                    </label>
                    <textarea name="rejection_reason" class="form-control" rows="4"
                        placeholder="Tuliskan alasan penolakan secara jelas agar dapat ditindaklanjuti pemohon..." required style="font-size:12.5px;border-radius:8px;"></textarea>
                </div>
                <div class="modal-footer" style="background:#f8fafc;padding:12px 18px;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:8px;">
                    <button type="button" class="btn btn-light border btn-sm"
                        onclick="document.getElementById('rejectModal').classList.remove('show')" style="border-radius:6px;padding:6px 12px;font-weight:600;">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm" style="border-radius:6px;padding:6px 14px;font-weight:700;">
                        <i class="fas fa-xmark me-1"></i> Tolak PO Lapangan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
