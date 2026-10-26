<x-app-layout>
    <x-slot name="title">PO Lapangan #{{ $materialRequest->request_number }} — PT Arsikon</x-slot>

    @push('styles')
    <style>
        /* ========================================================
           ENTERPRISE PROCUREMENT DOSSIER (PT ARSIKON)
           Clean, Human-Crafted, Professional ERP Architecture
           ======================================================== */
        
        .dossier-container {
            width: 100%;
        }

        /* Top Action Bar */
        .dossier-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
            margin-bottom: 20px;
        }

        .dossier-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #64748b;
        }

        .dossier-breadcrumb a {
            color: #64748b;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.15s ease;
        }

        .dossier-breadcrumb a:hover {
            color: #0f172a;
        }

        /* Hero Document Banner */
        .dossier-banner {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.02);
            margin-bottom: 20px;
            position: relative;
        }

        .dossier-header-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f1f5f9;
        }

        .dossier-badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 5px 14px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.2px;
        }

        .dossier-badge-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
        }

        /* Lifecycle Progress Stepper */
        .dossier-stepper {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            padding-top: 20px;
        }

        .stepper-step {
            position: relative;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 10px;
            transition: background 0.15s ease;
        }

        .stepper-step.active {
            background: #f8fafc;
        }

        .stepper-icon-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }

        .stepper-step.completed .stepper-icon-circle {
            background: #10b981;
            color: #ffffff;
            box-shadow: 0 0 0 3px #d1fae5;
        }

        .stepper-step.active .stepper-icon-circle {
            background: #2563eb;
            color: #ffffff;
            box-shadow: 0 0 0 3px #dbeafe;
        }

        .stepper-step.pending .stepper-icon-circle {
            background: #f1f5f9;
            color: #94a3b8;
            border: 1.5px solid #cbd5e1;
        }

        .stepper-step.rejected .stepper-icon-circle {
            background: #ef4444;
            color: #ffffff;
            box-shadow: 0 0 0 3px #fee2e2;
        }

        .stepper-title {
            font-size: 12.5px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.25;
            margin-bottom: 2px;
        }

        .stepper-sub {
            font-size: 11px;
            color: #64748b;
            line-height: 1.35;
        }

        /* Two-Column Overview Dossier */
        .dossier-summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .dossier-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .dossier-card-head {
            padding: 16px 20px;
            background: #fafbfc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .dossier-card-head h3 {
            font-size: 13.5px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
            letter-spacing: -0.2px;
        }

        .dossier-card-body {
            padding: 20px;
        }

        .dossier-meta-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .dossier-meta-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            font-size: 13px;
        }

        .dossier-meta-label {
            color: #64748b;
            font-weight: 500;
            flex-shrink: 0;
            width: 140px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .dossier-meta-value {
            color: #0f172a;
            font-weight: 700;
            text-align: right;
            flex: 1;
            word-break: break-word;
        }

        /* Items Table Section */
        .dossier-table-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            margin-bottom: 24px;
        }

        .dossier-table-toolbar {
            padding: 16px 22px;
            background: #fafbfc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .dossier-data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .dossier-data-table th {
            padding: 12px 16px;
            background: #f8fafc;
            color: #475569;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        .dossier-data-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            font-size: 13px;
            color: #1e293b;
        }

        .dossier-data-table tbody tr:hover {
            background: #fafbfc;
        }

        .dossier-data-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Action Pill Buttons */
        .btn-dossier-primary {
            background: #2563eb;
            color: #ffffff;
            border: 1px solid #1d4ed8;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            box-shadow: 0 1px 2px rgba(37, 99, 235, 0.2);
            transition: all 0.15s ease;
            text-decoration: none;
        }
        .btn-dossier-primary:hover {
            background: #1d4ed8;
            color: #ffffff;
        }

        .btn-dossier-success {
            background: #10b981;
            color: #ffffff;
            border: 1px solid #059669;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            box-shadow: 0 1px 2px rgba(16, 185, 129, 0.2);
            transition: all 0.15s ease;
            text-decoration: none;
        }
        .btn-dossier-success:hover {
            background: #059669;
            color: #ffffff;
        }

        .btn-dossier-secondary {
            background: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
            text-decoration: none;
        }
        .btn-dossier-secondary:hover {
            background: #f8fafc;
            color: #0f172a;
            border-color: #94a3b8;
        }

        @media (max-width: 1024px) {
            .dossier-stepper {
                grid-template-columns: repeat(2, 1fr);
            }
            .dossier-summary-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .dossier-stepper {
                grid-template-columns: 1fr;
            }
            .dossier-meta-row {
                flex-direction: column;
                gap: 4px;
            }
            .dossier-meta-label {
                width: 100%;
            }
            .dossier-meta-value {
                text-align: left;
            }
        }
    </style>
    @endpush

    @php
        $hasPurchaseItems = $materialRequest->items->contains(fn($it) => $it->fulfillment === 'purchase' && ((float)$it->qty_approved - (float)$it->qty_ordered) > 0);
        $hasCustomItems = $materialRequest->items->contains(fn($it) => empty($it->material_id) || !empty($it->custom_item_name));
        $canCreatePo = auth()->user()->hasAnyRole(['Owner', 'Admin Pusat', 'Admin Gudang Pusat', 'Admin PO']) || auth()->user()->can('create purchase orders');
        $relatedPos = $materialRequest->items->flatMap->purchaseOrderItems->map->purchaseOrder->filter()->unique('id');
        $sentPo = $relatedPos->first(fn($p) => in_array($p->status, ['sent', 'partial_received', 'approved']))
            ?? $relatedPos->first(fn($p) => $p->status !== 'received' && $p->status !== 'cancelled')
            ?? $relatedPos->first();
        $inTransitDist = $materialRequest->distributions->firstWhere('status', 'in_transit');
        $canReceiveGr = auth()->user()->can('create goods receipts');
        $canReceiveDist = $inTransitDist && $inTransitDist->canUserReceive(auth()->user());
        $canEdit = in_array($materialRequest->status, ['submitted', 'draft']) &&
            ($materialRequest->requested_by_user_id === auth()->id() || auth()->user()->hasAnyRole(['Owner', 'Admin Pusat']));

        // Direct & Related Goods Receipts
        $directGrs = $materialRequest->goodsReceipts ?? collect();
        $poGrs = $relatedPos->flatMap->goodsReceipts ?? collect();
        $allGrs = $directGrs->merge($poGrs)->unique('id')->sortByDesc('created_at');

        // Status configuration
        $statusConfig = match ($materialRequest->status) {
            'draft' => [
                'label' => 'Draft Permohonan',
                'bg' => '#f1f5f9',
                'text' => '#334155',
                'border' => '#cbd5e1',
                'dot' => '#64748b',
                'icon' => 'file-text',
                'stepIndex' => 1,
            ],
            'submitted' => [
                'label' => 'Menunggu Persetujuan',
                'bg' => '#fffbeb',
                'text' => '#b45309',
                'border' => '#fde68a',
                'dot' => '#f59e0b',
                'icon' => 'hourglass',
                'stepIndex' => 2,
            ],
            'approved' => [
                'label' => 'Disetujui',
                'bg' => '#ecfdf5',
                'text' => '#065f46',
                'border' => '#a7f3d0',
                'dot' => '#10b981',
                'icon' => 'circle-check',
                'stepIndex' => 3,
            ],
            'partially_fulfilled' => [
                'label' => 'Sebagian Terkirim',
                'bg' => '#eff6ff',
                'text' => '#1e40af',
                'border' => '#bfdbfe',
                'dot' => '#3b82f6',
                'icon' => 'truck',
                'stepIndex' => 3,
            ],
            'fulfilled' => [
                'label' => 'Selesai Terpenuhi',
                'bg' => '#f5f3ff',
                'text' => '#5b21b6',
                'border' => '#ddd6fe',
                'dot' => '#8b5cf6',
                'icon' => 'check-check',
                'stepIndex' => 4,
            ],
            'rejected' => [
                'label' => 'Ditolak',
                'bg' => '#fef2f2',
                'text' => '#991b1b',
                'border' => '#fecaca',
                'dot' => '#ef4444',
                'icon' => 'circle-x',
                'stepIndex' => 2,
            ],
            'cancelled' => [
                'label' => 'Dibatalkan',
                'bg' => '#f8fafc',
                'text' => '#475569',
                'border' => '#e2e8f0',
                'dot' => '#94a3b8',
                'icon' => 'ban',
                'stepIndex' => 1,
            ],
            default => [
                'label' => ucfirst($materialRequest->status),
                'bg' => '#f1f5f9',
                'text' => '#334155',
                'border' => '#cbd5e1',
                'dot' => '#64748b',
                'icon' => 'info',
                'stepIndex' => 1,
            ],
        };
    @endphp

    <div class="dossier-container">
        
        {{-- TOP ACTION & BREADCRUMB BAR --}}
        <div class="dossier-topbar">
            <div class="dossier-breadcrumb">
                <a href="{{ route('material-requests.index') }}" class="btn-dossier-secondary" style="padding:6px 12px;font-size:12px;">
                    <x-lucide-arrow-left />
                    <span>Daftar PO Lapangan</span>
                </a>
                <span style="color:#cbd5e1;">/</span>
                <span style="color:#0f172a;font-weight:700;">#{{ $materialRequest->request_number }}</span>
            </div>

            {{-- Operational Action Toolbar --}}
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                {{-- Terima Surat Jalan (Distribusi In-Transit) --}}
                @if($inTransitDist && $canReceiveDist)
                    <a href="{{ route('distributions.show', $inTransitDist) }}" class="btn-dossier-success">
                        <x-lucide-boxes />
                        <span>Terima Surat Jalan (#{{ $inTransitDist->distribution_number }})</span>
                    </a>
                @endif

                {{-- Terima Barang Masuk (GR) --}}
                @if(in_array($materialRequest->status, ['approved', 'partially_fulfilled']) && $canReceiveGr)
                    <a href="{{ route('goods-receipts.create', array_filter(['from_mr_id' => $materialRequest->id, 'purchase_order_id' => $sentPo?->id])) }}" class="btn-dossier-primary">
                        <x-lucide-package-check />
                        <span>Penerimaan Barang (GR)</span>
                    </a>
                @endif

                {{-- Buat PO ke Vendor --}}
                @if(in_array($materialRequest->status, ['approved', 'partially_fulfilled']) && $hasPurchaseItems && $canCreatePo)
                    <a href="{{ route('purchase-orders.create', ['from_mr_id' => $materialRequest->id]) }}" class="btn-dossier-primary" style="background:#ea580c;border-color:#c2410c;">
                        <x-lucide-shopping-cart />
                        <span>Terbitkan PO Vendor</span>
                    </a>
                @elseif($hasCustomItems && $canCreatePo)
                    <a href="{{ route('purchase-orders.create', ['from_mr_id' => $materialRequest->id]) }}" class="btn-dossier-primary" style="background:#ea580c;border-color:#c2410c;">
                        <x-lucide-shopping-cart />
                        <span>Buat PO Item Manual</span>
                    </a>
                @endif

                {{-- Edit PO Lapangan --}}
                @if($canEdit)
                    <a href="{{ route('material-requests.edit', $materialRequest) }}" class="btn-dossier-secondary">
                        <x-lucide-square-pen />
                        <span>Edit</span>
                    </a>
                @endif

                {{-- Print PDF --}}
                <a href="{{ route('material-requests.show', $materialRequest) }}?print=1" target="_blank" class="btn-dossier-secondary">
                    <x-lucide-printer />
                    <span>Cetak PDF</span>
                </a>

                {{-- Persetujuan (Approve / Reject) --}}
                @if($materialRequest->status === 'submitted')
                    @can('approve material requests')
                    <form method="POST" action="{{ route('material-requests.approve', $materialRequest) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn-dossier-success" onclick="return confirm('Apakah Anda yakin ingin menyetujui PO Lapangan ini?')">
                            <x-lucide-check />
                            <span>Setujui Permohonan</span>
                        </button>
                    </form>
                    <button type="button" class="btn-dossier-secondary" style="color:#b91c1c;border-color:#fca5a5;" onclick="document.getElementById('rejectModal').classList.add('show')">
                        <x-lucide-x />
                        <span>Tolak</span>
                    </button>
                    @endcan
                @endif
            </div>
        </div>

        {{-- FLASH MESSAGES --}}
        @if (session('success'))
        <div class="mb-4 p-4 rounded-xl flex items-center gap-3 border" style="background:#ecfdf5;border-color:#a7f3d0;color:#065f46;">
            <x-lucide-circle-check class="text-emerald-600 text-lg" />
            <span class="text-sm font-semibold">{{ session('success') }}</span>
        </div>
        @endif

        @if (session('error'))
        <div class="mb-4 p-4 rounded-xl flex items-center gap-3 border" style="background:#fef2f2;border-color:#fecaca;color:#991b1b;">
            <x-lucide-circle-alert class="text-rose-600 text-lg" />
            <span class="text-sm font-semibold">{{ session('error') }}</span>
        </div>
        @endif

        {{-- HERO DOCUMENT BANNER & PIPELINE STEPPER --}}
        <div class="dossier-banner">
            <div class="dossier-header-title">
                <div>
                    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#64748b;margin-bottom:4px;">
                        Berkas Permohonan Lapangan
                    </div>
                    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                        <h1 style="font-size:22px;font-weight:900;color:#0f172a;letter-spacing:-0.5px;margin:0;">
                            PO Lapangan #{{ $materialRequest->request_number }}
                        </h1>
                        <span class="dossier-badge-pill" style="background:{{ $statusConfig['bg'] }};color:{{ $statusConfig['text'] }};border:1px solid {{ $statusConfig['border'] }};">
                            <span class="dossier-badge-dot" style="background:{{ $statusConfig['dot'] }};"></span>
                            <span>{{ $statusConfig['label'] }}</span>
                        </span>
                    </div>
                    <div style="font-size:12.5px;color:#64748b;margin-top:6px;">
                        Dibuat oleh <strong style="color:#0f172a;">{{ $materialRequest->requestedBy?->name ?? 'Staff' }}</strong> ({{ $materialRequest->requested_as ?? 'Staff Proyek' }}) pada {{ $materialRequest->created_at ? $materialRequest->created_at->format('d M Y, H:i') : '-' }} WIB
                    </div>
                </div>

                {{-- Quick Logistics Indicators --}}
                <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                    <div style="text-align:right;">
                        <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;">Gudang Proyek Tujuan</div>
                        <div style="font-size:13.5px;font-weight:800;color:#0f172a;">{{ $materialRequest->toWarehouse?->name ?? '-' }}</div>
                    </div>
                </div>
            </div>

            {{-- 4-Stage Operational Lifecycle Stepper --}}
            <div class="dossier-stepper">
                {{-- Tahap 1: Pengajuan --}}
                <div class="stepper-step completed">
                    <div class="stepper-icon-circle">
                        <x-lucide-check />
                    </div>
                    <div>
                        <div class="stepper-title">1. Pengajuan Proyek</div>
                        <div class="stepper-sub">
                            Diajukan: {{ $materialRequest->created_at ? $materialRequest->created_at->format('d/m/Y') : '-' }}
                        </div>
                    </div>
                </div>

                {{-- Tahap 2: Persetujuan --}}
                @php
                    $isRejected = $materialRequest->status === 'rejected';
                    $isApproved = in_array($materialRequest->status, ['approved', 'partially_fulfilled', 'fulfilled']);
                    $isStep2Active = $materialRequest->status === 'submitted';
                @endphp
                <div class="stepper-step {{ $isApproved ? 'completed' : ($isRejected ? 'rejected' : ($isStep2Active ? 'active' : 'pending')) }}">
                    <div class="stepper-icon-circle">
                        @if($isApproved)
                            <x-lucide-check />
                        @elseif($isRejected)
                            <x-lucide-x />
                        @else
                            <span>2</span>
                        @endif
                    </div>
                    <div>
                        <div class="stepper-title">2. Persetujuan</div>
                        <div class="stepper-sub">
                            @if($isApproved)
                                Disetujui: {{ $materialRequest->approved_at ? \Carbon\Carbon::parse($materialRequest->approved_at)->format('d/m/Y') : 'Pusat' }}
                            @elseif($isRejected)
                                <span style="color:#dc2626;font-weight:700;">Ditolak</span>
                            @else
                                Menunggu Review
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Tahap 3: Pengadaan / Surat Jalan --}}
                @php
                    $hasDistOrPo = ($materialRequest->distributions && $materialRequest->distributions->isNotEmpty()) || $relatedPos->isNotEmpty();
                    $isStep3Completed = in_array($materialRequest->status, ['partially_fulfilled', 'fulfilled']);
                    $isStep3Active = $materialRequest->status === 'approved';
                @endphp
                <div class="stepper-step {{ $isStep3Completed ? 'completed' : ($isStep3Active ? 'active' : 'pending') }}">
                    <div class="stepper-icon-circle">
                        @if($isStep3Completed)
                            <x-lucide-check />
                        @else
                            <span>3</span>
                        @endif
                    </div>
                    <div>
                        <div class="stepper-title">3. Pengadaan / Kirim</div>
                        <div class="stepper-sub">
                            @if($materialRequest->distributions && $materialRequest->distributions->isNotEmpty())
                                {{ $materialRequest->distributions->count() }} Surat Jalan
                            @elseif($relatedPos->isNotEmpty())
                                {{ $relatedPos->count() }} PO Vendor
                            @elseif($isStep3Active)
                                Siap Diterbitkan
                            @else
                                Menunggu Approval
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Tahap 4: Penerimaan Barang --}}
                @php
                    $isFulfilled = $materialRequest->status === 'fulfilled';
                    $isPartiallyFulfilled = $materialRequest->status === 'partially_fulfilled';
                @endphp
                <div class="stepper-step {{ $isFulfilled ? 'completed' : ($isPartiallyFulfilled ? 'active' : 'pending') }}">
                    <div class="stepper-icon-circle">
                        @if($isFulfilled)
                            <x-lucide-check-check />
                        @elseif($isPartiallyFulfilled)
                            <x-lucide-package-check />
                        @else
                            <span>4</span>
                        @endif
                    </div>
                    <div>
                        <div class="stepper-title">4. Tiba di Lapangan</div>
                        <div class="stepper-sub">
                            @if($isFulfilled)
                                <span style="color:#059669;font-weight:700;">Diterima Lengkap</span>
                            @elseif($isPartiallyFulfilled)
                                <span style="color:#2563eb;font-weight:700;">Diterima Sebagian</span>
                            @else
                                Belum Diterima
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ACTIONABLE ALERTS --}}
        {{-- 1. Surat Jalan Sedang Dikirim --}}
        @if($inTransitDist)
        <div class="mb-4 p-4 rounded-xl border flex items-center justify-between gap-4 flex-wrap" style="background:#ecfdf5;border-color:#6ee7b7;">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-lg shrink-0 shadow-sm">
                    <x-lucide-truck />
                </div>
                <div>
                    <h4 class="text-sm font-bold text-emerald-950 m-0">
                        Material Sedang Dalam Pengiriman dari Gudang Pusat (Surat Jalan #{{ $inTransitDist->distribution_number }})
                    </h4>
                    <p class="text-xs text-emerald-800 m-0 mt-0.5">
                        Supir: <strong>{{ $inTransitDist->driver_name ?? '-' }}</strong> &bull; Kendaraan: <strong>{{ $inTransitDist->vehicle_number ?? '-' }}</strong> &bull; Tgl Kirim: {{ $inTransitDist->delivery_date ? \Carbon\Carbon::parse($inTransitDist->delivery_date)->format('d/m/Y') : '-' }}
                    </p>
                </div>
            </div>
            @if($canReceiveDist)
            <a href="{{ route('distributions.show', $inTransitDist) }}" class="btn-dossier-success">
                <x-lucide-boxes />
                <span>Konfirmasi Penerimaan Barang</span>
            </a>
            @endif
        </div>
        @endif

        {{-- 2. Butuh PO ke Vendor --}}
        @if(in_array($materialRequest->status, ['approved', 'partially_fulfilled']) && $hasPurchaseItems)
        <div class="mb-4 p-4 rounded-xl border flex items-center justify-between gap-4 flex-wrap" style="background:#fffbeb;border-color:#fcd34d;">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center text-lg shrink-0 shadow-sm">
                    <x-lucide-shopping-cart />
                </div>
                <div>
                    <h4 class="text-sm font-bold text-amber-950 m-0">
                        PO Lapangan Memerlukan Penerbitan Purchase Order ke Vendor
                    </h4>
                    <p class="text-xs text-amber-800 m-0 mt-0.5">
                        Terdapat item barang dengan jalur pembelian vendor yang kuotanya belum selesai dipesan.
                    </p>
                </div>
            </div>
            @if($canCreatePo)
            <a href="{{ route('purchase-orders.create', ['from_mr_id' => $materialRequest->id]) }}" class="btn-dossier-primary" style="background:#ea580c;border-color:#c2410c;">
                <x-lucide-shopping-cart />
                <span>Terbitkan PO Vendor Sekarang</span>
            </a>
            @endif
        </div>
        @endif

        {{-- 3. Alasan Penolakan --}}
        @if($materialRequest->rejection_reason)
        <div class="mb-4 p-4 rounded-xl border flex items-center gap-3.5" style="background:#fef2f2;border-color:#fca5a5;">
            <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center text-lg shrink-0 shadow-sm">
                <x-lucide-circle-x />
            </div>
            <div>
                <h4 class="text-sm font-bold text-rose-950 m-0">Permohonan PO Lapangan Ditolak</h4>
                <p class="text-xs text-rose-800 m-0 mt-0.5"><strong>Alasan:</strong> {{ $materialRequest->rejection_reason }}</p>
            </div>
        </div>
        @endif

        {{-- TWO-COLUMN STRUCTURED DOSSIER --}}
        <div class="dossier-summary-grid">
            
            {{-- Kartu Kiri: Rincian Logistik & Lokasi --}}
            <div class="dossier-card">
                <div class="dossier-card-head">
                    <h3>
                        <x-lucide-map-pin class="text-blue-600" />
                        <span>Informasi Proyek &amp; Jalur Logistik</span>
                    </h3>
                </div>
                <div class="dossier-card-body">
                    <div class="dossier-meta-list">
                        <div class="dossier-meta-row">
                            <span class="dossier-meta-label">
                                <x-lucide-building-2 class="text-slate-400" /> Proyek Pemohon
                            </span>
                            <span class="dossier-meta-value">
                                {{ $materialRequest->toWarehouse?->project ? $materialRequest->toWarehouse->project->name : ($materialRequest->toWarehouse?->name ?? '-') }}
                            </span>
                        </div>
                        <div class="dossier-meta-row">
                            <span class="dossier-meta-label">
                                <x-lucide-warehouse class="text-slate-400" /> Gudang Proyek
                            </span>
                            <span class="dossier-meta-value">
                                {{ $materialRequest->toWarehouse?->name ?? '-' }}
                            </span>
                        </div>
                        <div class="dossier-meta-row">
                            <span class="dossier-meta-label">
                                <x-lucide-package-check class="text-slate-400" /> Sumber Material
                            </span>
                            <span class="dossier-meta-value">
                                @if($materialRequest->fromWarehouse)
                                    <span class="badge" style="background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;padding:3px 8px;border-radius:6px;">
                                        {{ $materialRequest->fromWarehouse->name }} ({{ $materialRequest->fromWarehouse->is_central ? 'Gudang Pusat' : 'Gudang Proyek' }})
                                    </span>
                                @else
                                    <span class="badge" style="background:#fffbeb;color:#92400e;border:1px solid #fde68a;padding:3px 8px;border-radius:6px;">
                                        Pengadaan / Beli ke Vendor (Supplier)
                                    </span>
                                @endif
                            </span>
                        </div>
                        <div class="dossier-meta-row">
                            <span class="dossier-meta-label">
                                <x-lucide-calendar-days class="text-slate-400" /> Tgl Dibutuhkan
                            </span>
                            <span class="dossier-meta-value">
                                {{ $materialRequest->needed_at ? \Carbon\Carbon::parse($materialRequest->needed_at)->format('d F Y') : '-' }}
                                @if($materialRequest->is_overdue)
                                    <span class="badge" style="background:#fef2f2;color:#dc2626;border:1px solid #fca5a5;padding:2px 7px;border-radius:6px;font-size:10.5px;margin-left:4px;">
                                        <x-lucide-clock class="me-1" />Terlambat
                                    </span>
                                @endif
                            </span>
                        </div>
                        @if($materialRequest->notes)
                        <div style="margin-top:6px;padding:12px;background:#f8fafc;border-radius:10px;border:1px solid #e2e8f0;">
                            <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:3px;">
                                <x-lucide-quote class="text-slate-400 me-1" /> Catatan Tambahan Pemohon
                            </div>
                            <div style="font-size:12.5px;color:#0f172a;font-weight:500;line-height:1.4;">
                                {{ $materialRequest->notes }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Kartu Kanan: Otoritas & Verifikasi --}}
            <div class="dossier-card">
                <div class="dossier-card-head">
                    <h3>
                        <x-lucide-shield-check class="text-indigo-600" />
                        <span>Otoritas &amp; Dokumen Terkait</span>
                    </h3>
                </div>
                <div class="dossier-card-body">
                    <div class="dossier-meta-list">
                        <div class="dossier-meta-row">
                            <span class="dossier-meta-label">
                                <x-lucide-user class="text-slate-400" /> Pemohon
                            </span>
                            <span class="dossier-meta-value">
                                {{ $materialRequest->requestedBy?->name ?? '-' }}
                                <span class="badge" style="background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;padding:1px 6px;border-radius:4px;font-size:10.5px;margin-left:4px;">
                                    {{ $materialRequest->requested_as ?? 'Staff Proyek' }}
                                </span>
                            </span>
                        </div>
                        <div class="dossier-meta-row">
                            <span class="dossier-meta-label">
                                <x-lucide-clock class="text-slate-400" /> Waktu Pengajuan
                            </span>
                            <span class="dossier-meta-value">
                                {{ $materialRequest->created_at ? $materialRequest->created_at->format('d/m/Y H:i') : '-' }} WIB
                            </span>
                        </div>
                        <div class="dossier-meta-row">
                            <span class="dossier-meta-label">
                                <x-lucide-signature class="text-slate-400" /> Disetujui Oleh
                            </span>
                            <span class="dossier-meta-value">
                                @if($materialRequest->approvedBy)
                                    <strong style="color:#0f172a;">{{ $materialRequest->approvedBy->name }}</strong>
                                    <div style="font-size:11px;color:#64748b;font-weight:500;">
                                        Pada {{ \Carbon\Carbon::parse($materialRequest->approved_at)->format('d/m/Y H:i') }} WIB
                                    </div>
                                @else
                                    <span style="color:#b45309;font-weight:600;">Menunggu Persetujuan</span>
                                @endif
                            </span>
                        </div>

                        {{-- Dokumen Pengadaan Terhubung --}}
                        <div class="dossier-meta-row">
                            <span class="dossier-meta-label">
                                <x-lucide-receipt class="text-slate-400" /> Dokumen Terhubung
                            </span>
                            <span class="dossier-meta-value">
                                @if($relatedPos->isNotEmpty())
                                    <div style="display:flex;gap:4px;justify-content:flex-end;flex-wrap:wrap;">
                                        @foreach($relatedPos as $po)
                                            <a href="{{ route('purchase-orders.show', $po) }}" class="badge" style="background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;text-decoration:none;" title="Buka Purchase Order">
                                                <x-lucide-file-text class="me-1" />{{ $po->po_number }}
                                            </a>
                                        @endforeach
                                    </div>
                                @elseif($materialRequest->distributions && $materialRequest->distributions->isNotEmpty())
                                    <div style="display:flex;gap:4px;justify-content:flex-end;flex-wrap:wrap;">
                                        @foreach($materialRequest->distributions as $dst)
                                            <a href="{{ route('distributions.show', $dst) }}" class="badge" style="background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;text-decoration:none;" title="Buka Surat Jalan">
                                                <x-lucide-truck class="me-1" />{{ $dst->distribution_number }}
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <span style="color:#94a3b8;font-weight:500;">Belum ada dokumen turunan</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- MAIN TABLE CARD: Rincian Item PO Lapangan --}}
        <div class="dossier-table-card">
            <div class="dossier-table-toolbar">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:32px;height:32px;border-radius:8px;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:14px;">
                        <x-lucide-boxes />
                    </div>
                    <div>
                        <h3 style="font-size:15px;font-weight:800;color:#0f172a;margin:0;letter-spacing:-0.2px;">
                            Rincian Item PO Lapangan
                            <span style="font-size:12px;color:#64748b;font-weight:500;margin-left:6px;">(Daftar Kebutuhan Material &amp; Alokasi)</span>
                        </h3>
                        <p style="font-size:12px;color:#64748b;margin:0;">
                            Rincian kuantitas, spesifikasi teknis, stok sumber, dan status kedatangan material
                        </p>
                    </div>
                </div>

                <div style="display:flex;align-items:center;gap:8px;">
                    <span class="badge" style="background:#f1f5f9;color:#0f172a;font-weight:800;font-size:12px;padding:5px 12px;border-radius:20px;border:1px solid #cbd5e1;">
                        Total {{ $materialRequest->items->count() }} Jenis Item
                    </span>
                </div>
            </div>

            <div style="overflow-x:auto;">
                <table class="dossier-data-table">
                    <thead>
                        <tr>
                            <th style="width:44px;text-align:center;">#</th>
                            <th style="min-width:240px;">Material &amp; Spesifikasi</th>
                            <th style="min-width:130px;">Kategori</th>
                            <th style="text-align:right;width:100px;">Diminta</th>
                            <th style="text-align:right;width:100px;">Disetujui</th>
                            <th style="text-align:center;width:80px;">Satuan</th>
                            <th style="text-align:center;width:110px;">Tgl Pakai</th>
                            <th style="min-width:140px;">Alokasi / Zona</th>
                            <th style="min-width:140px;text-align:center;">Ketersediaan Stok</th>
                            <th style="text-align:center;width:120px;">Status Tiba</th>
                            <th style="min-width:120px;">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($materialRequest->items as $idx => $item)
                        @php
                            $approved = (float) $item->qty_approved;
                            $received = (float) $item->qty_received;
                            $unit = $item->displayUnit();
                            $isCustom = $item->isCustom();
                        @endphp
                        <tr style="{{ $isCustom ? 'background:#fffdfa;' : '' }}">
                            <td style="text-align:center;font-weight:700;color:#94a3b8;font-size:11.5px;">
                                {{ $idx + 1 }}
                            </td>
                            <td>
                                <div style="font-size:13.5px;font-weight:800;color:#0f172a;line-height:1.35;display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                    {{ $item->displayName() }}
                                    @if($isCustom)
                                        <span class="badge" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;font-size:10px;padding:2px 6px;border-radius:4px;font-weight:700;">
                                            <x-lucide-tag class="me-1" />Input Manual
                                        </span>
                                    @endif
                                </div>
                                <div style="font-size:11.5px;color:#64748b;margin-top:2px;">
                                    @if($isCustom)
                                        <span style="color:#b45309;">Material khusus non-katalog pusat</span>
                                    @else
                                        <span style="font-family:ui-monospace,SFMono-Regular,Consolas,monospace;">SKU: {{ $item->material?->sku ?? '-' }}</span>
                                        @if($item->material?->code)
                                            &bull; Kode: {{ $item->material->code }}
                                        @endif
                                    @endif
                                    @if($item->specification)
                                        <div style="color:#334155;margin-top:2px;font-weight:500;">
                                            <x-lucide-info class="me-1 text-slate-400" />{{ $item->specification }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge" style="background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;font-size:11px;padding:3px 9px;border-radius:6px;font-weight:700;">
                                    {{ $item->displayCategoryName() }}
                                </span>
                                @if($item->custom_item_type || $item->material?->type)
                                    <div style="font-size:11px;color:#64748b;margin-top:3px;">
                                        {{ $item->custom_item_type ?: $item->material?->type }}
                                    </div>
                                @endif
                            </td>
                            <td style="text-align:right;font-weight:800;color:#0f172a;font-size:13.5px;">
                                {{ format_quantity($item->qty_requested) }}
                            </td>
                            <td style="text-align:right;font-weight:900;color:#2563eb;font-size:13.5px;">
                                {{ in_array($materialRequest->status, ['draft', 'submitted']) ? '—' : format_quantity($approved) }}
                            </td>
                            <td style="text-align:center;font-weight:700;color:#475569;font-size:12px;">
                                {{ $unit }}
                            </td>
                            <td style="text-align:center;font-size:12px;">
                                @if($item->usage_date)
                                    <span class="badge" style="background:#f8fafc;color:#334155;border:1px solid #e2e8f0;padding:4px 8px;border-radius:6px;font-weight:600;">
                                        <x-lucide-calendar-days class="me-1 text-slate-400" />{{ \Carbon\Carbon::parse($item->usage_date)->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span style="color:#94a3b8;">—</span>
                                @endif
                            </td>
                            <td>
                                @if($item->usage_location)
                                    <span style="font-weight:600;color:#0f172a;">
                                        <x-lucide-map-pin class="me-1 text-blue-500" />{{ $item->usage_location }}
                                    </span>
                                @else
                                    <span style="color:#94a3b8;">—</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                @if($item->fulfillment === 'purchase')
                                    @php
                                        $approvedQty = (float) $item->qty_approved;
                                        $orderedQty  = (float) $item->qty_ordered;
                                        $remainingOrder = max(0, $approvedQty - $orderedQty);
                                        $itemPos = $item->purchaseOrderItems ? $item->purchaseOrderItems->map->purchaseOrder->filter()->unique('id') : collect();
                                    @endphp
                                    <div style="font-weight:700;color:#d97706;font-size:11.5px;">
                                        Sisa Dipesan: {{ format_quantity($remainingOrder) }} {{ $unit }}
                                    </div>
                                    @if($itemPos->isNotEmpty())
                                        <div style="font-size:10.5px;margin-top:3px;display:flex;gap:4px;justify-content:center;flex-wrap:wrap;">
                                            @foreach($itemPos as $po)
                                                <a href="{{ route('purchase-orders.show', $po) }}" class="badge" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;text-decoration:none;">
                                                    {{ $po->po_number }}
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
                                    <div style="display:flex;flex-direction:column;align-items:center;gap:3px;">
                                        <span style="font-weight:800;color:#0f172a;font-size:12px;">
                                            {{ format_quantity($availableStock) }} {{ $unit }}
                                        </span>
                                        @if($isSufficient)
                                            <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:10px;padding:2px 7px;border-radius:10px;font-weight:700;">
                                                <x-lucide-check class="me-1" />Stok Cukup
                                            </span>
                                        @else
                                            <span class="badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-size:10px;padding:2px 7px;border-radius:10px;font-weight:700;">
                                                <x-lucide-triangle-alert class="me-1" />Kurang
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span style="color:#94a3b8;">—</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                @if(in_array($materialRequest->status, ['draft', 'submitted']))
                                    <span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-size:10.5px;padding:3px 8px;border-radius:10px;font-weight:700;">
                                        Menunggu
                                    </span>
                                @elseif($received >= $approved && $approved > 0)
                                    <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:10.5px;padding:3px 8px;border-radius:10px;font-weight:800;">
                                        <x-lucide-check class="me-1" />Lengkap
                                    </span>
                                @elseif($received > 0)
                                    <span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-size:10.5px;padding:3px 8px;border-radius:10px;font-weight:700;">
                                        {{ format_quantity($received) }}/{{ format_quantity($approved) }}
                                    </span>
                                @else
                                    <span class="badge" style="background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;font-size:10.5px;padding:3px 8px;border-radius:10px;font-weight:600;">
                                        Belum Tiba
                                    </span>
                                @endif
                            </td>
                            <td style="font-size:12px;color:#475569;">
                                {{ $item->notes ?: '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- RIWAYAT OPERASIONAL LOGISTIK TERHUBUNG --}}
        {{-- 1. Riwayat Surat Jalan (Distribusi) --}}
        @if($materialRequest->distributions && $materialRequest->distributions->isNotEmpty())
        <div class="dossier-card mb-4">
            <div class="dossier-card-head">
                <h3>
                    <x-lucide-truck class="text-emerald-600" />
                    <span>Riwayat Pengiriman Surat Jalan (Distribusi Antar Gudang)</span>
                </h3>
                <span class="badge" style="background:#ecfdf5;color:#047857;font-weight:700;font-size:11px;padding:3px 9px;border-radius:12px;border:1px solid #a7f3d0;">
                    {{ $materialRequest->distributions->count() }} Surat Jalan
                </span>
            </div>
            <div style="overflow-x:auto;">
                <table class="dossier-data-table">
                    <thead>
                        <tr>
                            <th>No. Surat Jalan</th>
                            <th>Tgl Kirim</th>
                            <th>Gudang Asal</th>
                            <th>Gudang Tujuan</th>
                            <th>Supir / Plat</th>
                            <th style="text-align:center;">Status</th>
                            <th style="text-align:center;width:90px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($materialRequest->distributions as $dist)
                        @php
                            $distBadge = match($dist->status) {
                                'draft'      => '<span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-size:10.5px;padding:3px 8px;border-radius:8px;font-weight:600;">Draft</span>',
                                'in_transit' => '<span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-size:10.5px;padding:3px 8px;border-radius:8px;font-weight:700;"><x-lucide-truck class="me-1" /> Dalam Pengiriman</span>',
                                'completed'  => '<span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:10.5px;padding:3px 8px;border-radius:8px;font-weight:700;"><x-lucide-check class="me-1" /> Selesai Diterima</span>',
                                'cancelled'  => '<span class="badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-size:10.5px;padding:3px 8px;border-radius:8px;font-weight:600;">Dibatalkan</span>',
                                default      => '<span class="badge" style="background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;font-size:10.5px;padding:3px 8px;border-radius:8px;font-weight:600;">'.$dist->status.'</span>',
                            };
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('distributions.show', $dist) }}" style="font-weight:700;color:#2563eb;text-decoration:none;">
                                    {{ $dist->distribution_number }}
                                </a>
                            </td>
                            <td>{{ $dist->delivery_date ? \Carbon\Carbon::parse($dist->delivery_date)->format('d/m/Y') : '-' }}</td>
                            <td>{{ $dist->fromWarehouse?->name ?? '-' }}</td>
                            <td>{{ $dist->toWarehouse?->name ?? '-' }}</td>
                            <td>
                                <strong style="color:#0f172a;">{{ $dist->driver_name ?? '-' }}</strong>
                                @if($dist->vehicle_number)
                                    <span style="color:#64748b;font-size:11px;">({{ $dist->vehicle_number }})</span>
                                @endif
                            </td>
                            <td style="text-align:center;">{!! $distBadge !!}</td>
                            <td style="text-align:center;">
                                <a href="{{ route('distributions.show', $dist) }}" class="btn-dossier-secondary" style="padding:4px 10px;font-size:11.5px;">
                                    <span>Buka</span>
                                    <x-lucide-arrow-right class="text-[10px]" />
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
        <div class="dossier-card mb-4">
            <div class="dossier-card-head">
                <h3>
                    <x-lucide-package-check class="text-blue-600" />
                    <span>Riwayat Penerimaan Barang Masuk (Goods Receipts / GR)</span>
                </h3>
                <span class="badge" style="background:#eff6ff;color:#1e40af;font-weight:700;font-size:11px;padding:3px 9px;border-radius:12px;border:1px solid #bfdbfe;">
                    {{ $allGrs->count() }} Dokumen GR
                </span>
            </div>
            <div style="overflow-x:auto;">
                <table class="dossier-data-table">
                    <thead>
                        <tr>
                            <th>No. Dokumen GR</th>
                            <th>Tgl Terima</th>
                            <th>Gudang Penerima</th>
                            <th>Pemasok / Referensi PO</th>
                            <th>Penerima</th>
                            <th style="text-align:center;">Status</th>
                            <th style="text-align:center;width:90px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allGrs as $gr)
                        <tr>
                            <td>
                                <a href="{{ route('goods-receipts.show', $gr) }}" style="font-weight:700;color:#2563eb;text-decoration:none;">
                                    {{ $gr->receipt_number }}
                                </a>
                            </td>
                            <td>{{ $gr->receipt_date ? \Carbon\Carbon::parse($gr->receipt_date)->format('d/m/Y') : '-' }}</td>
                            <td>{{ $gr->warehouse?->name ?? '-' }}</td>
                            <td>
                                <span style="font-weight:600;color:#0f172a;">{{ $gr->supplier?->name ?? ($gr->supplier_name ?? '-') }}</span>
                                @if($gr->purchaseOrder)
                                    <div style="font-size:11px;color:#2563eb;">(PO: {{ $gr->purchaseOrder->po_number }})</div>
                                @endif
                            </td>
                            <td><strong style="color:#0f172a;">{{ $gr->received_by_name ?? ($gr->receivedBy?->name ?? '-') }}</strong></td>
                            <td style="text-align:center;">
                                @if($gr->status === 'confirmed')
                                    <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:10.5px;padding:3px 8px;border-radius:8px;font-weight:700;"><x-lucide-check class="me-1" /> Dikonfirmasi</span>
                                @else
                                    <span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-size:10.5px;padding:3px 8px;border-radius:8px;font-weight:600;">Draft</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                <a href="{{ route('goods-receipts.show', $gr) }}" class="btn-dossier-secondary" style="padding:4px 10px;font-size:11.5px;">
                                    <span>Buka</span>
                                    <x-lucide-arrow-right class="text-[10px]" />
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
        <div class="dossier-card mb-4">
            <div class="dossier-card-head">
                <h3>
                    <x-lucide-clipboard-check class="text-purple-600" />
                    <span>Riwayat Pemakaian Material di Lapangan (Material Usage)</span>
                </h3>
                <span class="badge" style="background:#f5f3ff;color:#5b21b6;font-weight:700;font-size:11px;padding:3px 9px;border-radius:12px;border:1px solid #ddd6fe;">
                    {{ $materialRequest->materialUsages->count() }} Pengeluaran
                </span>
            </div>
            <div style="overflow-x:auto;">
                <table class="dossier-data-table">
                    <thead>
                        <tr>
                            <th>No. Bukti Pemakaian</th>
                            <th>Tanggal</th>
                            <th>Gudang</th>
                            <th>Penerima / Mandor</th>
                            <th>Bagian / Zona</th>
                            <th style="text-align:center;">Status</th>
                            <th style="text-align:center;width:90px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($materialRequest->materialUsages as $usage)
                        <tr>
                            <td>
                                <a href="{{ route('material-usages.show', $usage) }}" style="font-weight:700;color:#2563eb;text-decoration:none;">
                                    {{ $usage->usage_number }}
                                </a>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($usage->usage_date)->format('d/m/Y') }}</td>
                            <td>{{ $usage->warehouse?->name ?? '-' }}</td>
                            <td><strong style="color:#0f172a;">{{ $usage->recipient_name }}</strong></td>
                            <td style="color:#475569;">{{ $usage->job_section ?? '-' }}</td>
                            <td style="text-align:center;">
                                @if($usage->status === 'completed')
                                    <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:10.5px;padding:3px 8px;border-radius:8px;font-weight:700;"><x-lucide-check class="me-1" /> Selesai</span>
                                @else
                                    <span class="badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-size:10.5px;padding:3px 8px;border-radius:8px;font-weight:600;">Dibatalkan</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                <a href="{{ route('material-usages.show', $usage) }}" class="btn-dossier-secondary" style="padding:4px 10px;font-size:11.5px;">
                                    <span>Buka</span>
                                    <x-lucide-arrow-right class="text-[10px]" />
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

    {{-- REJECT MODAL --}}
    <div class="modal-overlay" id="rejectModal">
        <div class="modal-box" style="border-radius:16px;overflow:hidden;max-width:500px;box-shadow:0 20px 40px rgba(0,0,0,0.15);">
            <div class="modal-header" style="background:#fef2f2;border-bottom:1px solid #fecaca;padding:16px 20px;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:36px;height:36px;border-radius:50%;background:#fee2e2;color:#dc2626;display:flex;align-items:center;justify-content:center;font-size:16px;">
                        <x-lucide-circle-x />
                    </div>
                    <div>
                        <h4 style="font-size:15px;font-weight:800;color:#991b1b;margin:0;">Tolak PO Lapangan</h4>
                        <p style="font-size:11.5px;color:#b91c1c;margin:0;">Permohonan akan dikembalikan dengan status ditolak</p>
                    </div>
                </div>
                <button class="btn-close-modal" onclick="document.getElementById('rejectModal').classList.remove('show')">
                    <x-lucide-x />
                </button>
            </div>
            <form method="POST" action="{{ route('material-requests.reject', $materialRequest) }}">
                @csrf
                <div class="modal-body" style="padding:20px;">
                    <label class="form-label" style="font-weight:700;font-size:12.5px;color:#0f172a;margin-bottom:6px;">
                        Alasan Penolakan <span class="text-danger">*</span>
                    </label>
                    <textarea name="rejection_reason" class="form-control" rows="4"
                        placeholder="Tuliskan alasan penolakan secara jelas dan rinci agar dapat ditindaklanjuti pemohon..." required style="font-size:13px;border-radius:10px;border-color:#cbd5e1;padding:10px 12px;"></textarea>
                </div>
                <div class="modal-footer" style="background:#f8fafc;padding:14px 20px;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:10px;">
                    <button type="button" class="btn-dossier-secondary"
                        onclick="document.getElementById('rejectModal').classList.remove('show')">Batal</button>
                    <button type="submit" class="btn-dossier-secondary" style="background:#dc2626;color:#ffffff;border-color:#b91c1c;font-weight:700;">
                        <x-lucide-x class="me-1" /> Tolak Permohonan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
