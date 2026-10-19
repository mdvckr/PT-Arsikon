<x-app-layout>
    <x-slot name="title">Manajemen Pembayaran PO</x-slot>

    @push('styles')
    <style>
        .payment-page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
            margin-bottom: 20px;
        }

        .payment-kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 20px;
        }

        @media (max-width: 1024px) {
            .payment-kpi-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .payment-kpi-grid {
                grid-template-columns: 1fr;
            }
        }

        .payment-kpi-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px 18px;
            box-shadow: 0 2px 6px -1px rgba(0,0,0,0.03);
            display: flex;
            align-items: center;
            gap: 14px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .payment-kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px -2px rgba(0,0,0,0.06);
        }

        .payment-kpi-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .payment-filter-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px 20px;
            box-shadow: 0 2px 6px -1px rgba(0,0,0,0.03);
            margin-bottom: 20px;
        }

        .status-tab-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.15s ease;
            border: 1.5px solid transparent;
        }

        .status-tab-btn.active {
            background: #2563eb;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(37,99,235,0.25);
        }

        .status-tab-btn:not(.active) {
            background: #f8fafc;
            color: #475569;
            border-color: #e2e8f0;
        }

        .status-tab-btn:not(.active):hover {
            background: #f1f5f9;
            color: #0f172a;
            border-color: #cbd5e1;
        }

        .payment-table-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 2px 6px -1px rgba(0,0,0,0.03);
            overflow: hidden;
        }

        .payment-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
        }

        .payment-table thead tr {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .payment-table th {
            padding: 12px 16px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #475569;
            white-space: nowrap;
        }

        .payment-table td {
            padding: 12px 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        .payment-table tbody tr:hover {
            background: #fafbfc;
        }

        .payment-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 9px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .po-group-header {
            cursor: pointer;
            user-select: none;
            transition: background 0.15s ease;
        }

        .po-group-header:hover {
            background: #e2e8f0 !important;
        }

        .group-toggle-icon {
            transition: transform 0.2s ease;
        }

        .po-child-row {
            transition: background 0.15s ease;
        }

        .po-child-row:hover {
            background: #f8fafc !important;
        }
    </style>
    @endpush

    {{-- ==================== Header & Actions ==================== --}}
    <div class="payment-page-header">
        <div>
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:38px;height:38px;border-radius:10px;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:18px;">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <div>
                    <h2 style="font-size:20px;font-weight:800;color:#0f172a;line-height:1.2;margin:0;">
                        Pembayaran Faktur & PO
                    </h2>
                    <p style="font-size:12.5px;color:#64748b;margin:2px 0 0 0;">
                        Pencatatan realisasi pelunasan, verifikasi pembayaran supplier, dan audit tagihan pengadaan.
                    </p>
                </div>
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <a href="{{ route('purchase-orders.index') }}" class="btn btn-light border" style="font-size:12.5px;font-weight:600;padding:7px 14px;border-radius:8px;color:#334155;background:#ffffff;display:inline-flex;align-items:center;gap:6px;">
                <i class="fas fa-receipt text-muted"></i> Daftar PO
            </a>
            <a href="{{ route('payments.create') }}" class="btn btn-primary" style="font-size:12.5px;font-weight:700;padding:8px 16px;border-radius:8px;box-shadow:0 2px 6px rgba(37,99,235,0.25);display:inline-flex;align-items:center;gap:6px;">
                <i class="fas fa-plus-circle"></i> Catat Pembayaran Baru
            </a>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
    <div class="alert alert-success mb-3" style="background:#ecfdf5;border:1px solid #10b981;color:#065f46;padding:12px 16px;border-radius:8px;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-check-circle" style="font-size:16px;"></i>
        <span style="font-size:13px;font-weight:600;">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger mb-3" style="background:#fef2f2;border:1px solid #ef4444;color:#991b1b;padding:12px 16px;border-radius:8px;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-triangle-exclamation" style="font-size:16px;"></i>
        <span style="font-size:13px;font-weight:600;">{{ session('error') }}</span>
    </div>
    @endif

    {{-- ==================== KPI Metric Cards (4 Cards) ==================== --}}
    <div class="payment-kpi-grid">
        {{-- 1. Total Terverifikasi (Nominal) --}}
        <div class="payment-kpi-card">
            <div class="payment-kpi-icon" style="background:#ecfdf5;color:#059669;">
                <i class="fas fa-circle-check"></i>
            </div>
            <div>
                <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.4px;">
                    Total Terverifikasi
                </div>
                <div style="font-size:19px;font-weight:800;color:#0f172a;margin-top:2px;line-height:1.2;">
                    Rp {{ number_format($metrics['total_verified_amount'] ?? 0, 0, ',', '.') }}
                </div>
                <div class="text-muted" style="font-size:11px;margin-top:2px;">
                    <strong>{{ $metrics['verified_count'] ?? 0 }}</strong> transaksi disetujui
                </div>
            </div>
        </div>

        {{-- 2. Menunggu Verifikasi --}}
        <div class="payment-kpi-card" style="{{ ($metrics['pending_count'] ?? 0) > 0 ? 'border-color:#fde68a;background:#fffdf5;' : '' }}">
            <div class="payment-kpi-icon" style="background:#fffbeb;color:#d97706;">
                <i class="fas fa-clock-rotate-left"></i>
            </div>
            <div>
                <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.4px;">
                    Menunggu Verifikasi
                </div>
                <div style="font-size:19px;font-weight:800;color:#b45309;margin-top:2px;line-height:1.2;">
                    Rp {{ number_format($metrics['pending_amount'] ?? 0, 0, ',', '.') }}
                </div>
                <div class="text-muted" style="font-size:11px;margin-top:2px;">
                    <strong>{{ $metrics['pending_count'] ?? 0 }}</strong> pembayaran butuh approval
                </div>
            </div>
        </div>

        {{-- 3. Total Transaksi --}}
        <div class="payment-kpi-card">
            <div class="payment-kpi-icon" style="background:#eff6ff;color:#2563eb;">
                <i class="fas fa-receipt"></i>
            </div>
            <div>
                <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.4px;">
                    Total Catatan Bayar
                </div>
                <div style="font-size:19px;font-weight:800;color:#0f172a;margin-top:2px;line-height:1.2;">
                    {{ $metrics['total_count'] ?? 0 }} <span style="font-size:13px;font-weight:600;color:#64748b;">Transaksi</span>
                </div>
                <div class="text-muted" style="font-size:11px;margin-top:2px;">
                    Semua riwayat pengajuan pembayaran
                </div>
            </div>
        </div>

        {{-- 4. Ditolak / Dibatalkan --}}
        <div class="payment-kpi-card">
            <div class="payment-kpi-icon" style="background:#fef2f2;color:#dc2626;">
                <i class="fas fa-ban"></i>
            </div>
            <div>
                <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.4px;">
                    Pembayaran Ditolak
                </div>
                <div style="font-size:19px;font-weight:800;color:{{ ($metrics['rejected_count'] ?? 0) > 0 ? '#dc2626' : '#0f172a' }};margin-top:2px;line-height:1.2;">
                    {{ $metrics['rejected_count'] ?? 0 }} <span style="font-size:13px;font-weight:600;color:#64748b;">Transaksi</span>
                </div>
                <div class="text-muted" style="font-size:11px;margin-top:2px;">
                    Pengajuan ditolak verifikator
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== Filter & Search Bar ==================== --}}
    <div class="payment-filter-card">
        {{-- Quick Filter Tabs by Status --}}
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:14px;padding-bottom:12px;border-bottom:1px solid #f1f5f9;">
            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                <span style="font-size:11.5px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.3px;margin-right:4px;">
                    <i class="fas fa-filter text-primary me-1"></i> Filter Status:
                </span>
                <a href="{{ route('payments.index', array_merge(request()->except('status', 'page'), [])) }}" 
                   class="status-tab-btn {{ !request('status') ? 'active' : '' }}">
                    Semua ({{ $metrics['total_count'] ?? 0 }})
                </a>
                <a href="{{ route('payments.index', array_merge(request()->except('status', 'page'), ['status' => 'pending'])) }}" 
                   class="status-tab-btn {{ request('status') === 'pending' ? 'active' : '' }}">
                    <i class="fas fa-clock text-warning"></i> Menunggu Verifikasi ({{ $metrics['pending_count'] ?? 0 }})
                </a>
                <a href="{{ route('payments.index', array_merge(request()->except('status', 'page'), ['status' => 'verified'])) }}" 
                   class="status-tab-btn {{ request('status') === 'verified' ? 'active' : '' }}">
                    <i class="fas fa-check-circle text-success"></i> Terverifikasi ({{ $metrics['verified_count'] ?? 0 }})
                </a>
                <a href="{{ route('payments.index', array_merge(request()->except('status', 'page'), ['status' => 'rejected'])) }}" 
                   class="status-tab-btn {{ request('status') === 'rejected' ? 'active' : '' }}">
                    <i class="fas fa-ban text-danger"></i> Ditolak ({{ $metrics['rejected_count'] ?? 0 }})
                </a>
            </div>

            @if(request()->hasAny(['search', 'status', 'payment_method', 'start_date', 'end_date']))
            <a href="{{ route('payments.index') }}" class="btn btn-sm btn-light border" style="font-size:11.5px;padding:5px 10px;color:#dc2626;background:#fef2f2;border-color:#fecaca;">
                <i class="fas fa-xmark me-1"></i> Reset Semua Filter
            </a>
            @endif
        </div>

        {{-- Search & Detailed Form Filter --}}
        <form method="GET" action="{{ route('payments.index') }}" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            {{-- Input Search --}}
            <div style="flex:2;min-width:240px;position:relative;">
                <div style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:13px;pointer-events:none;">
                    <i class="fas fa-magnifying-glass"></i>
                </div>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari No. Pembayaran, No. PO, Supplier, atau No. Ref..." 
                       class="form-control" 
                       style="height:38px;padding-left:34px;font-size:12.5px;border-radius:8px;border-color:#cbd5e1;">
            </div>

            {{-- Filter Metode Pembayaran --}}
            <div style="flex:1;min-width:160px;">
                <select name="payment_method" class="form-control" style="height:38px;font-size:12.5px;border-radius:8px;border-color:#cbd5e1;" onchange="this.form.submit()">
                    <option value="">-- Semua Metode --</option>
                    <option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Tunai / Cash</option>
                    <option value="cheque" {{ request('payment_method') === 'cheque' ? 'selected' : '' }}>Cek / Giro</option>
                </select>
            </div>

            {{-- Filter Tanggal Mulai --}}
            <div style="width:145px;">
                <input type="date" 
                       name="start_date" 
                       value="{{ request('start_date') }}" 
                       title="Tanggal Mulai"
                       class="form-control" 
                       style="height:38px;font-size:12px;border-radius:8px;border-color:#cbd5e1;">
            </div>

            {{-- Filter Tanggal Selesai --}}
            <div style="width:145px;">
                <input type="date" 
                       name="end_date" 
                       value="{{ request('end_date') }}" 
                       title="Tanggal Sampai"
                       class="form-control" 
                       style="height:38px;font-size:12px;border-radius:8px;border-color:#cbd5e1;">
            </div>

            <button type="submit" class="btn btn-secondary" style="height:38px;padding:0 16px;border-radius:8px;font-size:12.5px;font-weight:700;display:inline-flex;align-items:center;gap:6px;">
                <i class="fas fa-filter"></i> Terapkan
            </button>
        </form>
    </div>

    {{-- ==================== Data Table Card ==================== --}}
    <div class="payment-table-card">
        <div style="padding:14px 18px;border-bottom:1px solid #e2e8f0;background:#f8fafc;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <i class="fas fa-list-check text-primary" style="font-size:14px;"></i>
                <span style="font-size:13.5px;font-weight:700;color:#1e293b;">Daftar Transaksi Pembayaran</span>
                <span class="badge" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;font-size:11px;font-weight:700;padding:2px 8px;border-radius:12px;">
                    {{ $payments->total() }} Data
                </span>

                {{-- Global Expand / Collapse All Buttons --}}
                <div style="display:inline-flex;align-items:center;gap:5px;margin-left:8px;">
                    <button type="button" onclick="toggleAllPoGroups(true)" class="btn btn-light border" style="font-size:11px;font-weight:700;padding:3px 9px;border-radius:6px;background:#ffffff;color:#2563eb;line-height:1.4;" title="Buka semua dropdown pembayaran">
                        <i class="fas fa-angles-down me-1"></i> Buka Semua
                    </button>
                    <button type="button" onclick="toggleAllPoGroups(false)" class="btn btn-light border" style="font-size:11px;font-weight:700;padding:3px 9px;border-radius:6px;background:#ffffff;color:#64748b;line-height:1.4;" title="Tutup semua dropdown pembayaran">
                        <i class="fas fa-angles-up me-1"></i> Tutup Semua
                    </button>
                </div>
            </div>
            <div class="text-muted" style="font-size:11.5px;">
                Halaman {{ $payments->currentPage() }} dari {{ max(1, $payments->lastPage()) }}
            </div>
        </div>

        <div class="table-wrap" style="overflow-x:auto;-webkit-overflow-scrolling:touch;">
            <table class="payment-table">
                <thead>
                    <tr>
                        <th style="width:40px;text-align:center;">#</th>
                        <th style="min-width:160px;">No. Pembayaran</th>
                        <th style="min-width:140px;">Tahapan (Termin)</th>
                        <th style="min-width:150px;">Catatan / Keterangan</th>
                        <th style="min-width:150px;">Metode & Rekening</th>
                        <th style="min-width:150px;text-align:right;">Nominal Bayar</th>
                        <th style="min-width:115px;text-align:center;">Tgl Bayar</th>
                        <th style="min-width:130px;text-align:center;">Status</th>
                        <th style="width:90px;text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $groupedPayments = $payments->getCollection()->groupBy(function($item) {
                            return $item->purchase_order_id ?? 0;
                        });
                        $rowCounter = 0;
                    @endphp

                    @forelse($groupedPayments as $poId => $poItems)
                        @php
                            $firstItem = $poItems->first();
                            $po = $firstItem->purchaseOrder;
                            $poTotal = (float) ($po?->total_amount ?? 0);
                            $poPaidVerified = (float) $poItems->where('status', 'verified')->sum('amount');
                            $poPaidPending = (float) $poItems->where('status', 'pending')->sum('amount');
                            $isPoLunas = ($poTotal > 0 && $poPaidVerified >= $poTotal);
                        @endphp

                        {{-- Group Header Row per Purchase Order (Clickable to Collapse/Expand) --}}
                        <tr class="po-group-header" 
                            data-group-id="{{ $poId }}" 
                            onclick="togglePoGroup('{{ $poId }}', this)" 
                            title="Klik untuk membuka/menutup riwayat pembayaran PO ini"
                            style="background:#f1f5f9;border-top:2px solid #cbd5e1;border-bottom:1.5px solid #cbd5e1;">
                            <td colspan="9" style="padding:10px 14px;">
                                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                                        {{-- Toggle Icon Indicator --}}
                                        <span class="group-toggle-btn" style="width:26px;height:26px;border-radius:6px;background:#ffffff;border:1px solid #cbd5e1;display:inline-flex;align-items:center;justify-content:center;color:#334155;font-size:11px;box-shadow:0 1px 2px rgba(0,0,0,0.04);">
                                            <i class="fas fa-chevron-down group-toggle-icon" id="icon-group-{{ $poId }}"></i>
                                        </span>

                                        @if($po)
                                            <a href="{{ route('purchase-orders.show', $po) }}" onclick="event.stopPropagation()" style="text-decoration:none;" title="Lihat Detail PO">
                                                <span class="badge" style="background:#2563eb;color:#ffffff;font-size:12px;font-weight:700;padding:5px 9px;border-radius:6px;display:inline-flex;align-items:center;gap:5px;">
                                                    <i class="fas fa-receipt"></i> {{ $po->po_number }}
                                                </span>
                                            </a>
                                            <span style="font-size:13px;font-weight:700;color:#0f172a;">
                                                <i class="fas fa-truck-field text-muted me-1"></i> {{ $po->supplier?->name ?? '-' }}
                                            </span>
                                            <span class="text-muted" style="font-size:12px;">
                                                (Total Nilai PO: <strong>Rp {{ number_format($poTotal, 0, ',', '.') }}</strong>)
                                            </span>
                                        @else
                                            <span class="badge" style="background:#64748b;color:#ffffff;font-size:12px;font-weight:700;padding:5px 9px;border-radius:6px;">
                                                <i class="fas fa-file-invoice"></i> Pembayaran Mandiri / Tanpa PO
                                            </span>
                                        @endif
                                    </div>

                                    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                                        <div style="font-size:12px;color:#334155;">
                                            Total Terverifikasi: <strong style="color:#059669;">Rp {{ number_format($poPaidVerified, 0, ',', '.') }}</strong>
                                            @if($poPaidPending > 0)
                                                <span class="text-muted ms-1">(+ Pending: Rp {{ number_format($poPaidPending, 0, ',', '.') }})</span>
                                            @endif
                                        </div>

                                        <span class="badge" style="background:#eff6ff;color:#1d4ed8;font-size:11px;font-weight:700;border:1px solid #bfdbfe;padding:4px 8px;">
                                            <i class="fas fa-layer-group me-1"></i> {{ $poItems->count() }}x Pembayaran
                                        </span>

                                        @if($isPoLunas)
                                            <span class="badge" style="background:#ecfdf5;color:#047857;font-size:11px;font-weight:700;border:1px solid #a7f3d0;padding:4px 8px;">
                                                <i class="fas fa-check-double me-1"></i> Tagihan Lunas
                                            </span>
                                        @endif

                                        <span style="font-size:11.5px;color:#64748b;font-weight:600;display:inline-flex;align-items:center;gap:4px;background:#ffffff;padding:2px 8px;border-radius:4px;border:1px solid #e2e8f0;">
                                            <span id="text-group-{{ $poId }}">Tutup</span>
                                        </span>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        {{-- Child Payment Rows under this PO --}}
                        @foreach($poItems->sortBy('id') as $terminIndex => $p)
                        @php
                            $rowCounter++;
                            $terminNumber = $terminIndex + 1;
                        @endphp
                        <tr class="po-child-row po-child-{{ $poId }}" style="background:#ffffff;">
                            {{-- 1. Index --}}
                            <td style="text-align:center;color:#94a3b8;font-size:11.5px;">
                                {{ $rowCounter }}
                            </td>

                            {{-- 2. No Pembayaran --}}
                            <td>
                                <div style="display:flex;flex-direction:column;gap:2px;">
                                    <a href="{{ route('payments.show', $p) }}" style="font-size:13px;font-weight:700;color:#2563eb;text-decoration:none;display:inline-flex;align-items:center;gap:5px;">
                                        <i class="fas fa-file-invoice text-primary" style="font-size:11px;"></i>
                                        {{ $p->payment_number }}
                                    </a>
                                    <span class="text-muted" style="font-size:10.5px;">
                                        Oleh: <strong>{{ $p->creator?->name ?? 'Sistem' }}</strong>
                                    </span>
                                </div>
                            </td>

                            {{-- 3. Tahapan (Termin) --}}
                            <td>
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <span class="badge" style="background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;font-size:11px;font-weight:700;">
                                        ↳ Pembayaran ke-{{ $terminNumber }}
                                    </span>
                                </div>
                            </td>

                            {{-- 4. Catatan / Keterangan --}}
                            <td>
                                @if($p->notes)
                                    <div style="font-size:12px;color:#334155;font-weight:500;">
                                        "{{ $p->notes }}"
                                    </div>
                                @else
                                    <span class="text-muted" style="font-size:11.5px;">-</span>
                                @endif
                            </td>

                            {{-- 5. Metode & Rekening --}}
                            <td>
                                @php
                                    $method = strtolower($p->payment_method ?? 'bank_transfer');
                                    $methodLabel = match($method) {
                                        'cash' => 'Tunai / Cash',
                                        'cheque' => 'Cek / Giro',
                                        default => 'Bank Transfer'
                                    };
                                    $methodIcon = match($method) {
                                        'cash' => 'fa-money-bill-wave',
                                        'cheque' => 'fa-money-check',
                                        default => 'fa-building-columns'
                                    };
                                @endphp
                                <div style="display:flex;flex-direction:column;gap:3px;">
                                    <span class="badge" style="background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;font-size:10.5px;font-weight:700;width:fit-content;display:inline-flex;align-items:center;gap:4px;">
                                        <i class="fas {{ $methodIcon }}" style="font-size:10px;"></i>
                                        {{ $methodLabel }}
                                    </span>
                                    @if($p->bank_account)
                                        <span style="font-size:11px;color:#64748b;font-weight:500;">
                                            {{ $p->bank_account }}
                                        </span>
                                    @endif
                                    @if($p->reference_number)
                                        <span style="font-size:10.5px;color:#94a3b8;font-family:monospace;">
                                            Ref: {{ $p->reference_number }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- 6. Nominal Bayar --}}
                            <td style="text-align:right;">
                                <div style="font-size:14px;font-weight:800;color:#047857;letter-spacing:-0.2px;">
                                    Rp {{ number_format($p->amount, 0, ',', '.') }}
                                </div>
                            </td>

                            {{-- 7. Tgl Bayar --}}
                            <td style="text-align:center;font-size:12px;color:#334155;white-space:nowrap;">
                                @if($p->payment_date)
                                    <div style="font-weight:600;">{{ $p->payment_date->format('d/m/Y') }}</div>
                                    <div class="text-muted" style="font-size:10px;">{{ $p->payment_date->diffForHumans() }}</div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            {{-- 8. Status --}}
                            <td style="text-align:center;">
                                @if($p->status === 'verified')
                                    <span class="payment-badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;">
                                        <i class="fas fa-circle-check"></i> Terverifikasi
                                    </span>
                                    @if($p->verified_at)
                                    <div class="text-muted" style="font-size:9.5px;margin-top:2px;">
                                        {{ $p->verified_at->format('d/m/y H:i') }}
                                    </div>
                                    @endif
                                @elseif($p->status === 'pending')
                                    <span class="payment-badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;">
                                        <i class="fas fa-clock"></i> Menunggu Verifikasi
                                    </span>
                                @elseif($p->status === 'rejected')
                                    <span class="payment-badge" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca;">
                                        <i class="fas fa-circle-xmark"></i> Ditolak
                                    </span>
                                @else
                                    <span class="payment-badge" style="background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;">
                                        {{ $p->status_label }}
                                    </span>
                                @endif
                            </td>

                            {{-- 9. Aksi --}}
                            <td style="text-align:center;">
                                <a href="{{ route('payments.show', $p) }}" 
                                   class="btn btn-sm btn-light border" 
                                   style="font-size:11.5px;padding:5px 10px;border-radius:6px;color:#2563eb;background:#ffffff;font-weight:600;display:inline-flex;align-items:center;gap:4px;"
                                   title="Lihat Detail Pembayaran">
                                    <i class="fas fa-eye" style="font-size:11px;"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @endforeach

                    @empty
                    <tr>
                        <td colspan="9" style="text-align:center;padding:48px 16px;background:#ffffff;">
                            <div style="width:56px;height:56px;border-radius:50%;background:#f1f5f9;color:#94a3b8;display:inline-flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:12px;">
                                <i class="fas fa-receipt"></i>
                            </div>
                            <div style="font-size:14px;font-weight:700;color:#1e293b;margin-bottom:4px;">
                                Belum Ada Data Pembayaran
                            </div>
                            <p class="text-muted" style="font-size:12px;max-width:380px;margin:0 auto 16px auto;">
                                @if(request()->hasAny(['search', 'status', 'payment_method', 'start_date', 'end_date']))
                                    Tidak ada pembayaran yang cocok dengan kriteria filter atau pencarian Anda. Silakan coba atur ulang filter.
                                @else
                                    Belum ada transaksi pembayaran yang dicatat. Klik tombol di bawah untuk mencatat pembayaran PO pertama.
                                @endif
                            </p>
                            @if(request()->hasAny(['search', 'status', 'payment_method', 'start_date', 'end_date']))
                                <a href="{{ route('payments.index') }}" class="btn btn-sm btn-light border" style="font-size:12px;font-weight:600;">
                                    <i class="fas fa-rotate-left me-1"></i> Reset Filter
                                </a>
                            @else
                                <a href="{{ route('payments.create') }}" class="btn btn-sm btn-primary" style="font-size:12px;font-weight:700;">
                                    <i class="fas fa-plus-circle me-1"></i> Catat Pembayaran Baru
                                </a>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($payments->hasPages())
        <div style="padding:14px 18px;border-top:1px solid #f1f5f9;display:flex;justify-content:flex-end;">
            {{ $payments->links() }}
        </div>
        @endif
    </div>

    @push('scripts')
    <script>
        function togglePoGroup(groupId, headerEl) {
            const rows = document.querySelectorAll('.po-child-' + groupId);
            const icon = document.getElementById('icon-group-' + groupId);
            const textEl = document.getElementById('text-group-' + groupId);
            if (rows.length === 0) return;

            const isCurrentlyHidden = (rows[0].style.display === 'none');

            rows.forEach(r => {
                r.style.display = isCurrentlyHidden ? '' : 'none';
            });

            if (icon) {
                icon.style.transform = isCurrentlyHidden ? 'rotate(0deg)' : 'rotate(-90deg)';
            }
            if (textEl) {
                textEl.textContent = isCurrentlyHidden ? 'Tutup' : 'Buka';
            }
        }

        function toggleAllPoGroups(expand) {
            document.querySelectorAll('.po-group-header').forEach(header => {
                const groupId = header.getAttribute('data-group-id');
                const rows = document.querySelectorAll('.po-child-' + groupId);
                const icon = document.getElementById('icon-group-' + groupId);
                const textEl = document.getElementById('text-group-' + groupId);

                rows.forEach(r => {
                    r.style.display = expand ? '' : 'none';
                });

                if (icon) {
                    icon.style.transform = expand ? 'rotate(0deg)' : 'rotate(-90deg)';
                }
                if (textEl) {
                    textEl.textContent = expand ? 'Tutup' : 'Buka';
                }
            });
        }
    </script>
    @endpush
</x-app-layout>
