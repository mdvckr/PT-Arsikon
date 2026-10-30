<x-app-layout>
    <x-slot name="title">{{ $isRoleAdminPOProyek ? 'Kas & Saldo Operasional Proyek' : 'Manajemen Pembayaran PO & Kas Proyek' }}</x-slot>

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

        .balance-hero-banner {
            background: linear-gradient(135deg, #064e3b 0%, #065f46 50%, #047857 100%);
            color: #ffffff;
            border-radius: 14px;
            padding: 20px 24px;
            margin-bottom: 20px;
            box-shadow: 0 4px 14px -2px rgba(6,78,59,0.25);
            position: relative;
            overflow: hidden;
        }

        .balance-hero-banner::after {
            content: '';
            position: absolute;
            right: -20px;
            top: -30px;
            width: 180px;
            height: 180px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            pointer-events: none;
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
            padding: 7px 13px;
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
                    <x-lucide-file-spreadsheet />
                </div>
                <div>
                    @if($isRoleAdminPOProyek)
                        <h2 style="font-size:20px;font-weight:800;color:#0f172a;line-height:1.2;margin:0;">
                            Saldo Proyek
                        </h2>
                        <p style="font-size:12.5px;color:#64748b;margin:2px 0 0 0;">
                            Pengelolaan saldo kas harian proyek, riwayat mutasi nota harian, dan pengajuan penambahan saldo ke Pusat/Owner.
                        </p>
                    @else
                        <h2 style="font-size:20px;font-weight:800;color:#0f172a;line-height:1.2;margin:0;">
                            Pembayaran PO & Saldo Kas Proyek
                        </h2>
                        <p style="font-size:12.5px;color:#64748b;margin:2px 0 0 0;">
                            Manajemen kas harian Admin PO, realisasi pelunasan supplier, dan pembayaran invoice PO.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== Hero Banner: Saldo Kas Proyek Admin PO ==================== --}}
    <div class="balance-hero-banner">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;position:relative;z-index:2;">
            <div>
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                    <span class="badge" style="background:rgba(255,255,255,0.22);color:#ffffff;font-size:11px;font-weight:700;padding:4px 10px;border-radius:20px;letter-spacing:0.3px;">
                        <x-lucide-coins class="me-1" /> SALDO KAS PROYEK (ADMIN PO)
                    </span>
                    <span style="font-size:11.5px;color:#a7f3d0;font-weight:500;">
                        Petty Cash Operasional Nota Pembelian Harian Lapangan
                    </span>
                </div>
                <div style="font-size:28px;font-weight:800;letter-spacing:-0.5px;line-height:1.2;">
                    Rp {{ number_format($metrics['total_operational_balance'] ?? 0, 0, ',', '.') }}
                </div>
                <div style="font-size:12px;color:#d1fae5;margin-top:4px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    @if($isRoleAdminPOProyek && $assignedProject)
                        <span><x-lucide-building-2 class="text-emerald-300 me-1" /> Proyek: <strong>{{ $assignedProject->name }}</strong> ({{ $assignedProject->code ?? 'PRJ' }})</span>
                    @else
                        <span><x-lucide-circle-check class="text-emerald-300 me-1" /> Total saldo aktif siap pakai di seluruh proyek</span>
                    @endif
                    <span>&bull;</span>
                    <span><strong>{{ $metrics['instant_count'] ?? 0 }}x</strong> Pembayaran Instan Berhasil (Rp {{ number_format($metrics['instant_verified_amount'] ?? 0, 0, ',', '.') }})</span>
                </div>
            </div>

            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;position:relative;z-index:2;">
                @if($isRoleAdminPOProyek)
                    <button type="button" onclick="openRequestBalanceModal()" class="btn btn-sm" style="background:#ffffff;color:#065f46;font-size:12.5px;font-weight:700;padding:9px 20px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.15);display:inline-flex;align-items:center;gap:6px;">
                        <x-lucide-hand-coins style="width:16px;height:16px;" /> Ajukan Kas Lapangan ke Pusat/Owner
                    </button>
                @else
                    <button type="button" onclick="openBalanceRequestsModal()" class="btn btn-sm" style="background:{{ $pendingBalanceRequestsCount > 0 ? '#f59e0b' : 'rgba(255,255,255,0.18)' }};color:#ffffff;border:1px solid {{ $pendingBalanceRequestsCount > 0 ? '#d97706' : 'rgba(255,255,255,0.3)' }};font-size:12px;font-weight:700;padding:8px 14px;border-radius:8px;box-shadow:{{ $pendingBalanceRequestsCount > 0 ? '0 2px 8px rgba(245,158,11,0.3)' : 'none' }};">
                        <x-lucide-inbox class="me-1" /> Permintaan Saldo
                        @if($pendingBalanceRequestsCount > 0)
                            <span class="badge" style="background:#dc2626;color:#ffffff;font-size:10.5px;margin-left:4px;padding:2px 6px;border-radius:10px;">{{ $pendingBalanceRequestsCount }}</span>
                        @endif
                    </button>
                    <button type="button" onclick="openTopupModal()" class="btn btn-sm" style="background:#ffffff;color:#065f46;font-size:12px;font-weight:700;padding:8px 18px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.15);">
                        <x-lucide-circle-plus class="me-1" /> Top-Up Saldo Langsung
                    </button>
                    <button type="button" onclick="openBalanceHistoryModal()" class="btn btn-sm" style="background:rgba(255,255,255,0.18);color:#ffffff;border:1px solid rgba(255,255,255,0.3);font-size:12px;font-weight:600;padding:8px 14px;border-radius:8px;">
                        <x-lucide-list class="me-1" /> Riwayat Mutasi
                    </button>
                @endif
            </div>
        </div>

        {{-- Chips Saldo per Proyek (Khusus Admin Pusat / Manajemen) --}}
        @if(!$isRoleAdminPOProyek && $projects && $projects->count() > 0)
        <div style="margin-top:16px;padding-top:14px;border-top:1px solid rgba(255,255,255,0.18);display:flex;gap:10px;overflow-x:auto;padding-bottom:4px;position:relative;z-index:2;">
            @foreach($projects as $prj)
            <div style="background:rgba(255,255,255,0.12);backdrop-filter:blur(6px);border:1px solid rgba(255,255,255,0.22);border-radius:10px;padding:8px 14px;font-size:12px;min-width:210px;display:flex;justify-content:space-between;align-items:center;gap:10px;">
                <div style="overflow:hidden;">
                    <div style="font-weight:700;color:#ffffff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:130px;" title="{{ $prj->name }}">
                        {{ $prj->name }}
                    </div>
                    <div style="font-size:10.5px;color:#a7f3d0;font-family:monospace;">{{ $prj->code ?? 'PRJ' }}</div>
                </div>
                <div style="text-align:right;flex-shrink:0;">
                    <div style="font-weight:800;color:#ffffff;font-size:12.5px;">
                        Rp {{ number_format($prj->operational_balance, 0, ',', '.') }}
                    </div>
                    <button type="button" onclick="openTopupModal({{ $prj->id }}, '{{ addslashes($prj->name) }}')" style="background:none;border:none;color:#6ee7b7;font-size:10.5px;font-weight:700;cursor:pointer;padding:0;text-decoration:underline;">
                        + Top-up
                    </button>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    @if($isRoleAdminPOProyek)
        {{-- ==================== KHUSUS ADMIN PO PROYEK: KAS & PENGELOLAAN SALDO NOTA HARIAN ==================== --}}
        
        {{-- Card 1: Status Pengajuan Permintaan Saldo Kas ke Pusat / Owner --}}
        <div class="card mb-4" style="border:1.5px solid #cbd5e1;border-radius:12px;overflow:hidden;">
            <div style="padding:14px 20px;background:#f8fafc;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                <div class="flex items-center gap-2">
                    <x-lucide-hand-coins class="text-emerald-600" style="font-size:16px;" />
                    <h3 style="font-size:15px;font-weight:700;color:#0f172a;margin:0;">Status Pengajuan Permintaan Saldo Kas ke Pusat / Owner</h3>
                    <span class="badge badge-secondary" style="font-size:11px;">{{ $balanceRequests->count() }} Pengajuan</span>
                </div>
            </div>
            <div class="table-wrap">
                <table class="data-table" style="font-size:12.5px;margin:0;">
                    <thead>
                        <tr style="background:#ffffff;border-bottom:2px solid #e2e8f0;">
                            <th style="width:40px;text-align:center;">No</th>
                            <th>No. Pengajuan</th>
                            <th>Tgl Diajukan</th>
                            <th>Proyek</th>
                            <th class="text-end">Nominal Saldo</th>
                            <th class="text-center">Status Persetujuan</th>
                            <th class="text-center" style="width:90px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($balanceRequests as $i => $req)
                        <tr>
                            <td class="text-muted text-center">{{ $i + 1 }}</td>
                            <td>
                                <strong style="font-family:monospace;font-size:12px;color:#0f172a;">
                                    {{ $req->request_number ?? ('PKL-' . $req->id) }}
                                </strong>
                            </td>
                            <td>{{ $req->request_date ? $req->request_date->format('d/m/Y') : $req->created_at->format('d/m/Y H:i') }}</td>
                            <td><strong class="text-primary">{{ $req->project?->name ?? '-' }}</strong></td>
                            <td class="text-end"><strong class="text-success" style="font-size:13.5px;">Rp {{ number_format($req->amount, 0, ',', '.') }}</strong></td>
                            <td class="text-center">
                                <span class="badge badge-{{ $req->status_badge_color }}" style="font-weight:700;font-size:11px;">
                                    {{ $req->status_label }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('payments.balance-requests.print', $req) }}" target="_blank" class="btn btn-xs btn-outline-secondary" style="font-weight:700;display:inline-flex;align-items:center;gap:4px;" title="Cetak Dokumen Pengajuan Kas Lapangan">
                                    <x-lucide-printer style="width:13px;height:13px;" /> Cetak
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-6">
                                Belum ada pengajuan permintaan saldo kas.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Card 2: Riwayat Mutasi Saldo Kas Proyek (Dikelompokkan per 1 Minggu & di dalamnya Hari) --}}
        <div class="card mb-4" style="border:1.5px solid #cbd5e1;border-radius:12px;overflow:hidden;box-shadow:0 2px 6px rgba(0,0,0,0.03);">
            <div style="padding:14px 20px;background:#f8fafc;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                <div class="flex items-center gap-2">
                    <x-lucide-history class="text-primary" style="font-size:16px;" />
                    <h3 style="font-size:15px;font-weight:700;color:#0f172a;margin:0;">Riwayat Mutasi Saldo Kas Proyek</h3>
                    <span class="badge badge-secondary" style="font-size:11px;">{{ $balanceLogs->count() }} Transaksi</span>
                    @if(isset($groupedBalanceWeeks) && $groupedBalanceWeeks->count() > 0)
                        <div class="flex items-center gap-1 ms-2">
                            <button type="button" onclick="toggleAllBalanceWeeks(true)" class="btn btn-light btn-xs border" style="font-size:11px;padding:3px 8px;font-weight:600;">
                                <x-lucide-chevrons-down class="me-1" /> Buka Semua
                            </button>
                            <button type="button" onclick="toggleAllBalanceWeeks(false)" class="btn btn-light btn-xs border" style="font-size:11px;padding:3px 8px;font-weight:600;">
                                <x-lucide-chevrons-up class="me-1" /> Tutup Semua
                            </button>
                        </div>
                    @endif
                </div>
                <span class="text-muted text-xs">Dikelompokkan per 1 minggu &amp; di dalamnya per hari</span>
            </div>

            <div style="padding:14px 16px;background:#f8fafc;">
                @forelse($groupedBalanceWeeks as $wIdx => $week)
                    <div class="card mb-3 balance-week-card" style="border:1.5px solid #cbd5e1;overflow:hidden;box-shadow:0 2px 6px rgba(0,0,0,0.04);background:#ffffff;">
                        
                        {{-- Header Minggu (Clickable Accordion) --}}
                        <div onclick="toggleBalanceWeek('{{ $wIdx }}')" 
                             style="background:linear-gradient(135deg, #0f172a 0%, #1e293b 100%);color:#ffffff;padding:12px 18px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;cursor:pointer;user-select:none;">
                            
                            <div class="flex items-center gap-3">
                                <div style="width:34px;height:34px;border-radius:8px;background:rgba(255,255,255,0.12);display:flex;align-items:center;justify-content:center;font-size:15px;">
                                    <x-lucide-calendar-range class="text-emerald-400" />
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 style="font-size:14.5px;font-weight:700;margin:0;letter-spacing:-0.2px;color:#ffffff;">
                                            {{ $week->week_label }}
                                        </h4>
                                        <span class="badge" style="background:#047857;color:#a7f3d0;border:1px solid #059669;font-size:10.5px;padding:2px 8px;">
                                            Periode 1 Minggu
                                        </span>
                                    </div>
                                    <span style="font-size:11.5px;color:#94a3b8;">
                                        Total {{ count($week->days) }} hari transaksi &bull; Klik untuk buka/tutup
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <span style="background:rgba(255,255,255,0.12);color:#e2e8f0;padding:4px 10px;border-radius:6px;font-size:11.5px;font-weight:600;">
                                    <x-lucide-receipt class="me-1" /> {{ $week->count }} Mutasi
                                </span>
                                @if($week->total_topup > 0)
                                <span style="background:#10b981;color:#ffffff;padding:4px 10px;border-radius:6px;font-size:12px;font-weight:700;">
                                    + Rp {{ number_format($week->total_topup, 0, ',', '.') }}
                                </span>
                                @endif
                                @if($week->total_deduction > 0)
                                <span style="background:#ef4444;color:#ffffff;padding:4px 10px;border-radius:6px;font-size:12px;font-weight:700;">
                                    - Rp {{ number_format($week->total_deduction, 0, ',', '.') }}
                                </span>
                                @endif
                                <x-lucide-chevron-down class="balance-week-icon" id="balance-week-icon-{{ $wIdx }}" style="font-size:13px;color:#94a3b8;transition:transform 0.2s;" />
                            </div>
                        </div>

                        {{-- Isi Minggu: Hari-Hari di Dalam Minggu Tersebut --}}
                        <div id="balance-week-content-{{ $wIdx }}" class="balance-week-content" style="display:block;">
                            @foreach($week->days as $dIdx => $day)
                                <div style="border-bottom:1px solid #e2e8f0;">
                                    
                                    {{-- Subheader Hari --}}
                                    <div style="background:#f1f5f9;padding:10px 18px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;border-top:1px solid #e2e8f0;">
                                        <div class="flex items-center gap-2">
                                            <x-lucide-calendar class="text-emerald-600" style="font-size:15px;" />
                                            <strong style="font-size:13px;color:#0f172a;">📅 Hari: {{ $day->date_label }}</strong>
                                            <span class="badge badge-secondary" style="font-size:10.5px;padding:2px 7px;">{{ $day->count }} transaksi</span>
                                        </div>
                                        <div style="font-size:12px;font-weight:700;color:#334155;display:flex;gap:12px;align-items:center;">
                                            @if($day->total_topup > 0)
                                                <span>Top-Up: <strong class="text-success">+Rp {{ number_format($day->total_topup, 0, ',', '.') }}</strong></span>
                                            @endif
                                            @if($day->total_deduction > 0)
                                                <span>Potong Nota: <strong class="text-danger">-Rp {{ number_format($day->total_deduction, 0, ',', '.') }}</strong></span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Tabel Mutasi Hari Ini --}}
                                    <div class="table-wrap">
                                        <table class="data-table" style="font-size:12.5px;margin:0;">
                                            <thead>
                                                <tr style="background:#ffffff;border-bottom:2px solid #e2e8f0;">
                                                    <th style="width:40px;text-align:center;">No</th>
                                                    <th>Waktu Transaksi</th>
                                                    <th>Proyek</th>
                                                    <th>Jenis Mutasi</th>
                                                    <th class="text-end">Nominal</th>
                                                    <th class="text-end">Saldo Akhir</th>
                                                    <th>Keterangan Transaksi</th>
                                                    <th>Dicatat Oleh</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($day->logs as $idx => $log)
                                                <tr>
                                                    <td class="text-muted text-center">{{ $idx + 1 }}</td>
                                                    <td>
                                                        <span style="font-weight:600;color:#334155;">{{ $log->created_at ? $log->created_at->format('H:i') . ' WIB' : '-' }}</span>
                                                        <span class="text-muted text-xs block">{{ $log->created_at ? $log->created_at->format('d/m/Y') : '' }}</span>
                                                    </td>
                                                    <td><strong class="text-primary">{{ $log->project?->name ?? '-' }}</strong></td>
                                                    <td>
                                                        @if($log->type === 'topup')
                                                            <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:10.5px;font-weight:700;">
                                                                <x-lucide-arrow-down class="me-1" /> Top-Up Masuk
                                                            </span>
                                                        @elseif($log->type === 'payment_deduction')
                                                            <span class="badge" style="background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;font-size:10.5px;font-weight:700;">
                                                                <x-lucide-zap class="text-warning me-1" /> Potong Nota Harian
                                                            </span>
                                                        @else
                                                            <span class="badge badge-secondary">{{ $log->type_label }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end" style="font-weight:700;color:{{ $log->type === 'topup' ? '#059669' : '#dc2626' }};">
                                                        {{ $log->type === 'topup' ? '+' : '-' }} Rp {{ number_format($log->amount, 0, ',', '.') }}
                                                    </td>
                                                    <td class="text-end">
                                                        <strong style="color:#0f172a;">Rp {{ number_format($log->balance_after, 0, ',', '.') }}</strong>
                                                    </td>
                                                    <td style="max-width:280px;">
                                                        <div>{{ $log->notes ?: '-' }}</div>
                                                        @if($log->reference_number)
                                                            <div class="text-muted text-xs font-mono">Ref: {{ $log->reference_number }}</div>
                                                        @endif
                                                    </td>
                                                    <td class="text-muted text-xs">{{ $log->user?->name ?? 'Sistem' }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div style="text-align:center;padding:36px 16px;color:#64748b;background:#ffffff;border-radius:8px;border:1px dashed #cbd5e1;">
                        <x-lucide-receipt class="text-gray-300 mb-2" style="font-size:36px;display:inline-block;" />
                        <div style="font-weight:700;font-size:13.5px;color:#1e293b;">Belum Ada Riwayat Mutasi Saldo Kas Proyek</div>
                        <p style="font-size:12px;margin:4px 0 0 0;color:#64748b;">Transaksi top-up dan pemotongan nota belanja harian akan otomatis dikelompokkan per minggu &amp; hari di sini.</p>
                    </div>
                @endforelse
            </div>
        </div>

    @else
        {{-- ==================== KHUSUS ADMIN PUSAT, OWNER, ADMIN PO: MANAJEMEN PEMBAYARAN PO ==================== --}}

        {{-- ==================== KPI Metric Cards (4 Cards) ==================== --}}
        <div class="payment-kpi-grid">
        {{-- 1. Total Terverifikasi (Nominal) --}}
        <div class="payment-kpi-card">
            <div class="payment-kpi-icon" style="background:#ecfdf5;color:#059669;">
                <x-lucide-circle-check />
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
                <x-lucide-history />
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
                <x-lucide-receipt />
            </div>
            <div>
                <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.4px;">
                    Total Catatan Bayar
                </div>
                <div style="font-size:19px;font-weight:800;color:#0f172a;margin-top:2px;line-height:1.2;">
                    {{ $metrics['total_count'] ?? 0 }} <span style="font-size:13px;font-weight:600;color:#64748b;">Transaksi</span>
                </div>
                <div class="text-muted" style="font-size:11px;margin-top:2px;">
                    {{ $metrics['instant_count'] ?? 0 }} Instan &bull; {{ $metrics['waiting_count'] ?? 0 }} Reguler
                </div>
            </div>
        </div>

        {{-- 4. Ditolak / Dibatalkan --}}
        <div class="payment-kpi-card">
            <div class="payment-kpi-icon" style="background:#fef2f2;color:#dc2626;">
                <x-lucide-ban />
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
        {{-- Quick Filter Tabs by Status & Payment Type --}}
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:14px;padding-bottom:12px;border-bottom:1px solid #f1f5f9;">
            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                <span style="font-size:11.5px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.3px;margin-right:4px;">
                    <x-lucide-filter class="text-primary me-1" /> Filter:
                </span>
                
                {{-- Semua --}}
                <a href="{{ route('payments.index', array_merge(request()->except('status', 'payment_type', 'page'), [])) }}" 
                   class="status-tab-btn {{ (!request('status') && !request('payment_type')) ? 'active' : '' }}">
                    Semua ({{ $metrics['total_count'] ?? 0 }})
                </a>

                {{-- Instan Kas Proyek --}}
                <a href="{{ route('payments.index', array_merge(request()->except('payment_type', 'page'), ['payment_type' => 'instant'])) }}" 
                   class="status-tab-btn {{ request('payment_type') === 'instant' ? 'active' : '' }}" style="{{ request('payment_type') === 'instant' ? 'background:#059669;border-color:#059669;' : '' }}">
                    <x-lucide-zap class="text-warning" /> ⚡ Instan Kas Proyek ({{ $metrics['instant_count'] ?? 0 }})
                </a>

                {{-- PO Proyek Reguler --}}
                <a href="{{ route('payments.index', array_merge(request()->except('payment_type', 'page'), ['payment_type' => 'waiting'])) }}" 
                   class="status-tab-btn {{ request('payment_type') === 'waiting' ? 'active' : '' }}">
                    <x-lucide-clock class="text-blue-500" /> ⏳ PO Proyek Reguler ({{ $metrics['waiting_count'] ?? 0 }})
                </a>

                {{-- Menunggu Verifikasi --}}
                <a href="{{ route('payments.index', array_merge(request()->except('status', 'page'), ['status' => 'pending'])) }}" 
                   class="status-tab-btn {{ request('status') === 'pending' ? 'active' : '' }}">
                    <x-lucide-hourglass class="text-warning" /> Menunggu Verifikasi ({{ $metrics['pending_count'] ?? 0 }})
                </a>

                {{-- Terverifikasi --}}
                <a href="{{ route('payments.index', array_merge(request()->except('status', 'page'), ['status' => 'verified'])) }}" 
                   class="status-tab-btn {{ request('status') === 'verified' ? 'active' : '' }}">
                    <x-lucide-circle-check class="text-success" /> Terverifikasi ({{ $metrics['verified_count'] ?? 0 }})
                </a>

                {{-- Ditolak --}}
                <a href="{{ route('payments.index', array_merge(request()->except('status', 'page'), ['status' => 'rejected'])) }}" 
                   class="status-tab-btn {{ request('status') === 'rejected' ? 'active' : '' }}">
                    <x-lucide-ban class="text-danger" /> Ditolak ({{ $metrics['rejected_count'] ?? 0 }})
                </a>
            </div>

            @if(request()->hasAny(['search', 'status', 'payment_type', 'project_id', 'payment_method', 'start_date', 'end_date']))
            <a href="{{ route('payments.index') }}" class="btn btn-sm btn-light border" style="font-size:11.5px;padding:5px 10px;color:#dc2626;background:#fef2f2;border-color:#fecaca;">
                <x-lucide-x class="me-1" /> Reset Semua Filter
            </a>
            @endif
        </div>

        {{-- Search & Detailed Form Filter --}}
        <form method="GET" action="{{ route('payments.index') }}" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            @if(request('payment_type'))
                <input type="hidden" name="payment_type" value="{{ request('payment_type') }}">
            @endif

            {{-- Input Search --}}
            <div style="flex:2;min-width:240px;position:relative;">
                <div style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:13px;pointer-events:none;">
                    <x-lucide-search />
                </div>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari No. Pembayaran, No. PO, Supplier, Proyek, atau No. Ref..." 
                       class="form-control" 
                       style="height:38px;padding-left:34px;font-size:12.5px;border-radius:8px;border-color:#cbd5e1;">
            </div>

            {{-- Filter Proyek --}}
            <div style="flex:1;min-width:160px;">
                <select name="project_id" class="form-control" style="height:38px;font-size:12.5px;border-radius:8px;border-color:#cbd5e1;" onchange="this.form.submit()">
                    <option value="">-- Semua Proyek --</option>
                    @foreach($projects as $prj)
                        <option value="{{ $prj->id }}" {{ request('project_id') == $prj->id ? 'selected' : '' }}>
                            {{ $prj->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Metode Pembayaran --}}
            <div style="flex:1;min-width:150px;">
                <select name="payment_method" class="form-control" style="height:38px;font-size:12.5px;border-radius:8px;border-color:#cbd5e1;" onchange="this.form.submit()">
                    <option value="">-- Semua Metode --</option>
                    <option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Tunai / Cash</option>
                    <option value="cheque" {{ request('payment_method') === 'cheque' ? 'selected' : '' }}>Cek / Giro</option>
                </select>
            </div>

            {{-- Filter Tanggal Mulai --}}
            <div style="width:140px;">
                <input type="date" 
                       name="start_date" 
                       value="{{ request('start_date') }}" 
                       title="Tanggal Mulai"
                       class="form-control" 
                       style="height:38px;font-size:12px;border-radius:8px;border-color:#cbd5e1;">
            </div>

            {{-- Filter Tanggal Selesai --}}
            <div style="width:140px;">
                <input type="date" 
                       name="end_date" 
                       value="{{ request('end_date') }}" 
                       title="Tanggal Sampai"
                       class="form-control" 
                       style="height:38px;font-size:12px;border-radius:8px;border-color:#cbd5e1;">
            </div>

            <button type="submit" class="btn btn-secondary" style="height:38px;padding:0 16px;border-radius:8px;font-size:12.5px;font-weight:700;display:inline-flex;align-items:center;gap:6px;">
                <x-lucide-filter /> Terapkan
            </button>
        </form>
    </div>

    {{-- ==================== Data Table Card ==================== --}}
    <div class="payment-table-card">
        <div style="padding:14px 18px;border-bottom:1px solid #e2e8f0;background:#f8fafc;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <x-lucide-list-checks class="text-primary" style="font-size:14px;" />
                <span style="font-size:13.5px;font-weight:700;color:#1e293b;">Daftar Transaksi Pembayaran</span>
                <span class="badge" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;font-size:11px;font-weight:700;padding:2px 8px;border-radius:12px;">
                    {{ $payments->total() }} Data
                </span>

                {{-- Global Expand / Collapse All Buttons --}}
                <div style="display:inline-flex;align-items:center;gap:5px;margin-left:8px;">
                    <button type="button" onclick="toggleAllPoGroups(true)" class="btn btn-light border" style="font-size:11px;font-weight:700;padding:3px 9px;border-radius:6px;background:#ffffff;color:#2563eb;line-height:1.4;" title="Buka semua dropdown pembayaran">
                        <x-lucide-chevrons-down class="me-1" /> Buka Semua
                    </button>
                    <button type="button" onclick="toggleAllPoGroups(false)" class="btn btn-light border" style="font-size:11px;font-weight:700;padding:3px 9px;border-radius:6px;background:#ffffff;color:#64748b;line-height:1.4;" title="Tutup semua dropdown pembayaran">
                        <x-lucide-chevrons-up class="me-1" /> Tutup Semua
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
                        <th style="min-width:180px;">No. Pembayaran & Tipe</th>
                        <th style="min-width:140px;">Tahapan (Termin)</th>
                        <th style="min-width:150px;">Proyek / Gudang</th>
                        <th style="min-width:140px;">Metode & Rekening</th>
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
                                            <x-lucide-chevron-down class="group-toggle-icon" id="icon-group-{{ $poId }}" />
                                        </span>

                                        @if($po)
                                            <a href="{{ route('purchase-orders.show', $po) }}" onclick="event.stopPropagation()" style="text-decoration:none;" title="Lihat Detail PO">
                                                <span class="badge" style="background:#2563eb;color:#ffffff;font-size:12px;font-weight:700;padding:5px 9px;border-radius:6px;display:inline-flex;align-items:center;gap:5px;">
                                                    <x-lucide-receipt /> {{ $po->po_number }}
                                                </span>
                                            </a>
                                            <span style="font-size:13px;font-weight:700;color:#0f172a;">
                                                <x-lucide-truck class="text-muted me-1" /> {{ $po->supplier?->name ?? '-' }}
                                            </span>
                                            <span class="text-muted" style="font-size:12px;">
                                                (Total Nilai PO: <strong>Rp {{ number_format($poTotal, 0, ',', '.') }}</strong>)
                                            </span>
                                        @else
                                            <span class="badge" style="background:#64748b;color:#ffffff;font-size:12px;font-weight:700;padding:5px 9px;border-radius:6px;">
                                                <x-lucide-receipt /> Pembayaran Mandiri / Tanpa PO
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
                                            <x-lucide-layers class="me-1" /> {{ $poItems->count() }}x Pembayaran
                                        </span>

                                        @if($isPoLunas)
                                            <span class="badge" style="background:#ecfdf5;color:#047857;font-size:11px;font-weight:700;border:1px solid #a7f3d0;padding:4px 8px;">
                                                <x-lucide-check-check class="me-1" /> Tagihan Lunas
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

                            {{-- 2. No Pembayaran & Tipe --}}
                            <td>
                                <div style="display:flex;flex-direction:column;gap:3px;">
                                    <div style="display:flex;align-items:center;gap:6px;">
                                        <a href="{{ route('payments.show', $p) }}" style="font-size:13px;font-weight:700;color:#2563eb;text-decoration:none;display:inline-flex;align-items:center;gap:5px;">
                                            <x-lucide-receipt class="text-primary" style="font-size:11px;" />
                                            {{ $p->payment_number }}
                                        </a>

                                        @if($p->isInstant())
                                            <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:10px;font-weight:700;padding:2px 6px;border-radius:4px;">
                                                <x-lucide-zap class="text-warning me-1" /> Instan (Kas Proyek)
                                            </span>
                                        @else
                                            <span class="badge" style="background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;font-size:10px;font-weight:700;padding:2px 6px;border-radius:4px;">
                                                <x-lucide-clock class="text-blue-500 me-1" /> PO Proyek (Reguler)
                                            </span>
                                        @endif
                                    </div>

                                    <span class="text-muted" style="font-size:10.5px;">
                                        Oleh: <strong>{{ $p->creator?->name ?? 'Sistem' }}</strong>
                                        @if($p->notes)
                                            &bull; <span style="font-style:italic;">"{{ Str::limit($p->notes, 30) }}"</span>
                                        @endif
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

                            {{-- 4. Proyek / Gudang --}}
                            <td>
                                @php
                                    $proj = $p->project ?? $po?->project;
                                @endphp
                                @if($proj)
                                    <div style="font-size:12px;font-weight:600;color:#0f172a;display:inline-flex;align-items:center;gap:5px;">
                                        <x-lucide-building-2 class="text-primary" style="font-size:11px;" />
                                        <span>{{ $proj->name }}</span>
                                    </div>
                                    <div style="font-size:10.5px;color:#64748b;">
                                        {{ $proj->code ?? 'Proyek' }}
                                    </div>
                                @elseif($po?->deliveryWarehouse)
                                    <div style="font-size:12px;color:#334155;">
                                        <x-lucide-warehouse class="text-muted me-1" /> {{ $po->deliveryWarehouse->name }}
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
                                        'cash' => 'banknote',
                                        'cheque' => 'file-text',
                                        default => 'landmark'
                                    };
                                @endphp
                                <div style="display:flex;flex-direction:column;gap:3px;">
                                    <span class="badge" style="background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;font-size:10.5px;font-weight:700;width:fit-content;display:inline-flex;align-items:center;gap:4px;">
                                        <x-dynamic-component :component="'lucide-'.$methodIcon" style="width:11px;height:11px;" />
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
                                @if($p->isInstant() && $p->balance_after !== null)
                                    <div style="font-size:10px;color:#64748b;margin-top:1px;">
                                        Sisa Saldo: Rp {{ number_format($p->balance_after, 0, ',', '.') }}
                                    </div>
                                @endif
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
                                        <x-lucide-circle-check /> Terverifikasi
                                    </span>
                                    @if($p->verified_at)
                                    <div class="text-muted" style="font-size:9.5px;margin-top:2px;">
                                        {{ $p->verified_at->format('d/m/y H:i') }}
                                    </div>
                                    @endif
                                @elseif($p->status === 'pending')
                                    <span class="payment-badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;">
                                        <x-lucide-clock /> Menunggu Verifikasi
                                    </span>
                                @elseif($p->status === 'rejected')
                                    <span class="payment-badge" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca;">
                                        <x-lucide-circle-x /> Ditolak
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
                                    <x-lucide-eye style="font-size:11px;" /> Detail
                                </a>
                            </td>
                        </tr>
                        @endforeach

                    @empty
                    <tr>
                        <td colspan="9" style="text-align:center;padding:48px 16px;background:#ffffff;">
                            <div style="width:56px;height:56px;border-radius:50%;background:#f1f5f9;color:#94a3b8;display:inline-flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:12px;">
                                <x-lucide-receipt />
                            </div>
                            <div style="font-size:14px;font-weight:700;color:#1e293b;margin-bottom:4px;">
                                Belum Ada Data Pembayaran
                            </div>
                            <p class="text-muted" style="font-size:12px;max-width:380px;margin:0 auto 16px auto;">
                                @if(request()->hasAny(['search', 'status', 'payment_type', 'project_id', 'payment_method', 'start_date', 'end_date']))
                                    Tidak ada pembayaran yang cocok dengan kriteria filter atau pencarian Anda. Silakan coba atur ulang filter.
                                @else
                                    Belum ada transaksi pembayaran yang dicatat. Klik tombol di bawah untuk mencatat pembayaran PO pertama.
                                @endif
                            </p>
                            @if(request()->hasAny(['search', 'status', 'payment_type', 'project_id', 'payment_method', 'start_date', 'end_date']))
                                <a href="{{ route('payments.index') }}" class="btn btn-sm btn-light border" style="font-size:12px;font-weight:600;">
                                    <x-lucide-rotate-ccw class="me-1" /> Reset Filter
                                </a>
                            @else
                                <a href="{{ route('payments.create') }}" class="btn btn-sm btn-primary" style="font-size:12px;font-weight:700;">
                                    <x-lucide-circle-plus class="me-1" /> Catat Pembayaran Baru
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
    @endif

    {{-- ==================== MODAL 1: Input / Top-Up Saldo Admin PO ==================== --}}
    <div id="modalTopupBalance" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#ffffff;border-radius:14px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1),0 10px 10px -5px rgba(0,0,0,0.04);width:100%;max-width:520px;overflow:hidden;animation:fadeIn 0.2s ease;">
            
            {{-- Modal Header --}}
            <div style="background:#064e3b;color:#ffffff;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:34px;height:34px;border-radius:8px;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:16px;">
                        <x-lucide-wallet class="text-emerald-200" />
                    </div>
                    <div>
                        <h3 style="font-size:15px;font-weight:800;margin:0;line-height:1.2;">Top-Up Saldo Kas Proyek</h3>
                        <p style="font-size:11px;color:#a7f3d0;margin:2px 0 0 0;">Petty cash operasional Admin PO untuk pembayaran nota harian</p>
                    </div>
                </div>
                <button type="button" onclick="closeTopupModal()" style="background:none;border:none;color:#d1fae5;cursor:pointer;font-size:18px;line-height:1;padding:4px;">
                    <x-lucide-x />
                </button>
            </div>

            {{-- Modal Form --}}
            <form method="POST" action="{{ route('payments.topupBalance') }}">
                @csrf
                <div style="padding:20px;display:flex;flex-direction:column;gap:14px;">
                    
                    {{-- 1. Pilih Proyek --}}
                    <div>
                        <label class="form-label required" style="font-size:11.5px;font-weight:700;color:#334155;text-transform:uppercase;letter-spacing:0.3px;display:block;margin-bottom:5px;">
                            Pilih Proyek Tujuan <span class="text-danger">*</span>
                        </label>
                        <select name="project_id" id="topupProjectId" required class="form-control" style="height:40px;font-size:13px;border-radius:8px;border-color:#cbd5e1;">
                            <option value="">-- Pilih Proyek yang Dituju --</option>
                            @foreach($projects as $prj)
                                <option value="{{ $prj->id }}" data-balance="{{ (float)$prj->operational_balance }}">
                                    {{ $prj->name }} (Saldo Saat Ini: Rp {{ number_format($prj->operational_balance, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 2. Nominal Top-Up --}}
                    <div>
                        <label class="form-label required" style="font-size:11.5px;font-weight:700;color:#334155;text-transform:uppercase;letter-spacing:0.3px;display:block;margin-bottom:5px;">
                            Nominal Pengisian Saldo (Rp) <span class="text-danger">*</span>
                        </label>
                        <div style="position:relative;">
                            <div style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-weight:700;color:#64748b;font-size:13px;">
                                Rp
                            </div>
                            <input type="number" 
                                   name="amount" 
                                   id="topupAmountInput" 
                                   min="1000" 
                                   step="any" 
                                   required 
                                   placeholder="0" 
                                   oninput="formatRupiahPreview(this.value)"
                                   class="form-control" 
                                   style="height:42px;padding-left:40px;font-size:15px;font-weight:800;color:#047857;border-radius:8px;border-color:#cbd5e1;">
                        </div>
                        <div id="topupRupiahPreview" style="font-size:11.5px;color:#047857;font-weight:600;margin-top:4px;display:none;">
                            Preview: Rp 0
                        </div>
                    </div>

                    {{-- 3. No Referensi Bukti Kas / Transfer --}}
                    <div>
                        <label class="form-label" style="font-size:11.5px;font-weight:700;color:#334155;text-transform:uppercase;letter-spacing:0.3px;display:block;margin-bottom:5px;">
                            No. Referensi Transfer / Bukti Kas Kantor Pusat
                        </label>
                        <input type="text" 
                               name="reference_number" 
                               placeholder="Contoh: TRF-PUSAT-00918 atau BKK-1029" 
                               class="form-control" 
                               style="height:38px;font-size:12.5px;border-radius:8px;border-color:#cbd5e1;">
                    </div>

                    {{-- 4. Catatan Tambahan --}}
                    <div>
                        <label class="form-label" style="font-size:11.5px;font-weight:700;color:#334155;text-transform:uppercase;letter-spacing:0.3px;display:block;margin-bottom:5px;">
                            Catatan / Keterangan
                        </label>
                        <textarea name="notes" 
                                  rows="2" 
                                  placeholder="Contoh: Saldo petty cash minggu ke-3 untuk Admin PO proyek gedung..." 
                                  class="form-control" 
                                  style="font-size:12.5px;padding:8px 12px;border-radius:8px;border-color:#cbd5e1;"></textarea>
                    </div>

                </div>

                {{-- Modal Footer --}}
                <div style="background:#f8fafc;padding:12px 20px;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:10px;">
                    <button type="button" onclick="closeTopupModal()" class="btn btn-light border" style="font-size:12.5px;font-weight:600;padding:7px 16px;border-radius:8px;">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-success" style="font-size:12.5px;font-weight:700;padding:8px 20px;border-radius:8px;background:#059669;border-color:#059669;box-shadow:0 2px 6px rgba(5,150,105,0.25);display:inline-flex;align-items:center;gap:6px;">
                        <x-lucide-circle-check /> Simpan Top-Up Saldo
                    </button>
                </div>
            </form>

        </div>
    </div>

    {{-- ==================== MODAL 2: Riwayat Mutasi Saldo Kas Proyek ==================== --}}
    <div id="modalBalanceHistory" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#ffffff;border-radius:14px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1),0 10px 10px -5px rgba(0,0,0,0.04);width:100%;max-width:860px;max-height:90vh;display:flex;flex-direction:column;overflow:hidden;">
            
            {{-- Modal Header --}}
            <div style="background:#0f172a;color:#ffffff;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:34px;height:34px;border-radius:8px;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;font-size:16px;color:#38bdf8;">
                        <x-lucide-history />
                    </div>
                    <div>
                        <h3 style="font-size:15px;font-weight:800;margin:0;line-height:1.2;">Riwayat Mutasi Saldo Kas Proyek</h3>
                        <p style="font-size:11px;color:#94a3b8;margin:2px 0 0 0;">Audit pencatatan penambahan top-up & pemotongan pembayaran instan</p>
                    </div>
                </div>
                <button type="button" onclick="closeBalanceHistoryModal()" style="background:none;border:none;color:#94a3b8;cursor:pointer;font-size:18px;line-height:1;padding:4px;">
                    <x-lucide-x />
                </button>
            </div>

            {{-- Modal Body Table (Grouped per 1 Minggu & Hari) --}}
            <div style="overflow-y:auto;padding:16px 20px;flex:1;">
                @if(isset($groupedBalanceWeeks) && $groupedBalanceWeeks->count() > 0)
                    @foreach($groupedBalanceWeeks as $wIdx => $week)
                        <div class="card mb-3" style="border:1.5px solid #cbd5e1;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,0.03);background:#ffffff;">
                            
                            {{-- Header Minggu Modal --}}
                            <div onclick="toggleModalBalanceWeek('{{ $wIdx }}')"
                                 style="background:linear-gradient(135deg, #0f172a 0%, #1e293b 100%);color:#ffffff;padding:10px 16px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;cursor:pointer;user-select:none;">
                                <div class="flex items-center gap-2">
                                    <x-lucide-calendar-range class="text-emerald-400" />
                                    <strong style="font-size:13px;color:#ffffff;">{{ $week->week_label }}</strong>
                                    <span class="badge" style="background:#047857;color:#a7f3d0;font-size:10px;padding:2px 6px;">1 Minggu</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span style="background:rgba(255,255,255,0.12);color:#e2e8f0;padding:2px 8px;border-radius:4px;font-size:11px;">
                                        {{ $week->count }} Mutasi
                                    </span>
                                    @if($week->total_topup > 0)
                                        <span style="background:#10b981;color:#ffffff;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;">
                                            +Rp {{ number_format($week->total_topup, 0, ',', '.') }}
                                        </span>
                                    @endif
                                    @if($week->total_deduction > 0)
                                        <span style="background:#ef4444;color:#ffffff;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;">
                                            -Rp {{ number_format($week->total_deduction, 0, ',', '.') }}
                                        </span>
                                    @endif
                                    <x-lucide-chevron-down class="modal-balance-icon" id="modal-balance-icon-{{ $wIdx }}" style="font-size:11px;color:#94a3b8;transition:transform 0.2s;" />
                                </div>
                            </div>

                            {{-- Isi Hari di Modal --}}
                            <div id="modal-balance-content-{{ $wIdx }}" class="modal-balance-content" style="display:block;">
                                @foreach($week->days as $dIdx => $day)
                                    <div style="border-bottom:1px solid #e2e8f0;">
                                        <div style="background:#f1f5f9;padding:7px 14px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:6px;border-top:1px solid #e2e8f0;font-size:11.5px;">
                                            <div class="flex items-center gap-2">
                                                <x-lucide-calendar class="text-emerald-600" />
                                                <strong style="color:#0f172a;">📅 Hari: {{ $day->date_label }}</strong>
                                                <span class="badge badge-secondary" style="font-size:10px;padding:1px 5px;">{{ $day->count }} mutasi</span>
                                            </div>
                                            <div style="font-weight:700;color:#334155;display:flex;gap:8px;">
                                                @if($day->total_topup > 0)
                                                    <span class="text-success">+Rp {{ number_format($day->total_topup, 0, ',', '.') }}</span>
                                                @endif
                                                @if($day->total_deduction > 0)
                                                    <span class="text-danger">-Rp {{ number_format($day->total_deduction, 0, ',', '.') }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <table style="width:100%;border-collapse:collapse;font-size:11.5px;">
                                            <thead>
                                                <tr style="background:#ffffff;border-bottom:1.5px solid #e2e8f0;text-align:left;">
                                                    <th style="padding:6px 10px;font-size:10.5px;color:#475569;">WAKTU</th>
                                                    <th style="padding:6px 10px;font-size:10.5px;color:#475569;">PROYEK</th>
                                                    <th style="padding:6px 10px;font-size:10.5px;color:#475569;">TIPE</th>
                                                    <th style="padding:6px 10px;font-size:10.5px;color:#475569;text-align:right;">NOMINAL</th>
                                                    <th style="padding:6px 10px;font-size:10.5px;color:#475569;text-align:right;">SALDO AKHIR</th>
                                                    <th style="padding:6px 10px;font-size:10.5px;color:#475569;">CATATAN</th>
                                                    <th style="padding:6px 10px;font-size:10.5px;color:#475569;">OLEH</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($day->logs as $log)
                                                <tr style="border-bottom:1px solid #f1f5f9;">
                                                    <td style="padding:6px 10px;color:#64748b;white-space:nowrap;">
                                                        {{ $log->created_at ? $log->created_at->format('H:i') : '-' }}
                                                    </td>
                                                    <td style="padding:6px 10px;font-weight:600;color:#0f172a;">
                                                        {{ $log->project?->name ?? '-' }}
                                                    </td>
                                                    <td style="padding:6px 10px;">
                                                        @if($log->type === 'topup')
                                                            <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:10px;font-weight:700;">
                                                                Top-Up
                                                            </span>
                                                        @elseif($log->type === 'payment_deduction')
                                                            <span class="badge" style="background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;font-size:10px;font-weight:700;">
                                                                Potong Nota
                                                            </span>
                                                        @else
                                                            <span class="badge badge-secondary" style="font-size:10px;">{{ $log->type_label }}</span>
                                                        @endif
                                                    </td>
                                                    <td style="padding:6px 10px;text-align:right;font-weight:700;white-space:nowrap;color:{{ $log->type === 'topup' ? '#059669' : '#dc2626' }};">
                                                        {{ $log->type === 'topup' ? '+' : '-' }} Rp {{ number_format($log->amount, 0, ',', '.') }}
                                                    </td>
                                                    <td style="padding:6px 10px;text-align:right;font-weight:700;color:#0f172a;white-space:nowrap;">
                                                        Rp {{ number_format($log->balance_after, 0, ',', '.') }}
                                                    </td>
                                                    <td style="padding:6px 10px;color:#475569;font-size:11px;max-width:180px;">
                                                        <div>{{ $log->notes ?: '-' }}</div>
                                                        @if($log->reference_number)
                                                            <div style="font-family:monospace;font-size:9.5px;color:#94a3b8;">Ref: {{ $log->reference_number }}</div>
                                                        @endif
                                                    </td>
                                                    <td style="padding:6px 10px;color:#64748b;font-size:10.5px;white-space:nowrap;">
                                                        {{ $log->user?->name ?? 'Sistem' }}
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @else
                    <div style="text-align:center;padding:36px 16px;color:#64748b;">
                        <x-lucide-history style="font-size:32px;color:#cbd5e1;margin-bottom:8px;display:inline-block;" />
                        <div style="font-weight:700;font-size:13px;color:#1e293b;">Belum Ada Riwayat Mutasi Saldo</div>
                        <p style="font-size:12px;margin:4px 0 0 0;">Transaksi top-up dan pemotongan pembayaran instan akan tercatat otomatis di sini.</p>
                    </div>
                @endif
            </div>

            {{-- Modal Footer --}}
            <div style="background:#f8fafc;padding:12px 20px;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;">
                <button type="button" onclick="closeBalanceHistoryModal()" class="btn btn-secondary" style="font-size:12px;font-weight:600;padding:6px 16px;border-radius:6px;">
                    Tutup
                </button>
            </div>

        </div>
    </div>

    {{-- ==================== MODAL 3: Formulir PENGAJUAN KAS LAPANGAN (Admin PO Proyek ke Pusat/Owner) ==================== --}}
    <div id="modalRequestBalance" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.65);backdrop-filter:blur(4px);z-index:9999;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#ffffff;border-radius:14px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);width:100%;max-width:920px;max-height:92vh;display:flex;flex-direction:column;overflow:hidden;">
            
            {{-- Modal Header --}}
            <div style="background:linear-gradient(135deg, #ea580c 0%, #c2410c 100%);color:#ffffff;padding:16px 22px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:38px;height:38px;border-radius:8px;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:18px;">
                        <x-lucide-file-text class="text-white" />
                    </div>
                    <div>
                        <h3 style="font-size:16px;font-weight:900;margin:0;line-height:1.2;letter-spacing:0.3px;">PENGAJUAN KAS LAPANGAN</h3>
                        <p style="font-size:11.5px;color:#ffedd5;margin:2px 0 0 0;">Formulir resmi permohonan dana kas operasional (petty cash) proyek ke Kantor Pusat / Owner</p>
                    </div>
                </div>
                <button type="button" onclick="closeRequestBalanceModal()" style="background:none;border:none;color:#ffedd5;cursor:pointer;font-size:20px;line-height:1;padding:4px;">
                    <x-lucide-x />
                </button>
            </div>

            {{-- Modal Form --}}
            <form method="POST" action="{{ route('payments.requestBalance') }}" style="display:flex;flex-direction:column;flex:1;overflow:hidden;" onsubmit="calcRequestGrandTotal()">
                @csrf
                <div style="padding:20px 22px;overflow-y:auto;display:flex;flex-direction:column;gap:18px;flex:1;">
                    
                    {{-- 1. Metadata Pengajuan --}}
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px 16px;">
                        <div style="font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:10px;display:flex;align-items:center;gap:6px;">
                            <x-lucide-info style="width:14px;height:14px;" /> INFORMASI UTAMA PENGAJUAN
                        </div>
                        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:12px;">
                            <div>
                                <label class="form-label" style="font-size:11px;font-weight:700;color:#334155;text-transform:uppercase;margin-bottom:4px;display:block;">
                                    Proyek Tujuan (Sesuai Akun)
                                </label>
                                <input type="hidden" name="project_id" id="reqProjectId" value="{{ $assignedProject?->id ?? ($projects->first()?->id ?? '') }}">
                                <div style="background:#f1f5f9;border:1px solid #cbd5e1;border-radius:6px;padding:6px 12px;height:36px;display:flex;align-items:center;justify-content:space-between;">
                                    <span id="reqProjectNameDisplay" style="font-weight:700;font-size:12.5px;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-right:8px;" title="{{ $assignedProject ? $assignedProject->name : ($projects->first()?->name ?? 'Proyek Lapangan') }}">
                                        {{ $assignedProject ? $assignedProject->name : ($projects->first()?->name ?? 'Proyek Lapangan') }}
                                    </span>
                                    <span id="reqProjectBalanceDisplay" style="font-size:11px;font-weight:700;color:#059669;background:#d1fae5;padding:2px 8px;border-radius:4px;white-space:nowrap;flex-shrink:0;">
                                        Saldo: Rp {{ number_format($assignedProject?->operational_balance ?? ($projects->first()?->operational_balance ?? 0), 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>

                            <div>
                                <label class="form-label required" style="font-size:11px;font-weight:700;color:#334155;text-transform:uppercase;margin-bottom:4px;display:block;">
                                    Tanggal Pengajuan <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="request_date" value="{{ date('Y-m-d') }}" required class="form-control form-control-sm" style="height:36px;font-size:12.5px;border-radius:6px;">
                            </div>

                            <div>
                                <label class="form-label required" style="font-size:11px;font-weight:700;color:#334155;text-transform:uppercase;margin-bottom:4px;display:block;">
                                    Pemohon (Yang Mengajukan) <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="signer_requester" value="{{ auth()->user()->name }}" required class="form-control form-control-sm" style="height:36px;font-size:12.5px;border-radius:6px;">
                            </div>

                            <div>
                                <label class="form-label" style="font-size:11px;font-weight:700;color:#334155;text-transform:uppercase;margin-bottom:4px;display:block;">
                                    Mengetahui / Site Manager
                                </label>
                                <input type="text" name="signer_site_manager" placeholder="Nama Site Manager / Penanggung Jawab" class="form-control form-control-sm" style="height:36px;font-size:12.5px;border-radius:6px;">
                            </div>
                        </div>
                    </div>

                    {{-- 2. Tabel Rincian Barang / Kebutuhan Biaya --}}
                    <div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                            <div style="font-size:12px;font-weight:800;color:#0f172a;display:flex;align-items:center;gap:6px;">
                                <x-lucide-list-plus class="text-emerald-600" style="width:16px;height:16px;" /> RINCIAN KEBUTUHAN KAS LAPANGAN
                            </div>
                            <span class="text-muted" style="font-size:11px;">Isi daftar barang yang dibutuhkan</span>
                        </div>

                        <div style="border:1.5px solid #e2e8f0;border-radius:8px;overflow:hidden;">
                            <table style="width:100%;border-collapse:collapse;font-size:12px;" id="requestItemsTable">
                                <thead>
                                    <tr style="background:#f1f5f9;border-bottom:1.5px solid #cbd5e1;color:#334155;font-size:10.5px;text-transform:uppercase;font-weight:700;letter-spacing:0.3px;">
                                        <th style="padding:8px 6px;width:36px;text-align:center;">No</th>
                                        <th style="padding:8px 10px;text-align:left;">Nama Barang <span class="text-danger">*</span></th>
                                        <th style="padding:8px 6px;width:90px;text-align:center;">Qty <span class="text-danger">*</span></th>
                                        <th style="padding:8px 6px;width:90px;text-align:center;">Satuan</th>
                                        <th style="padding:8px 8px;width:150px;text-align:right;">Harga Satuan (Rp) <span class="text-danger">*</span></th>
                                        <th style="padding:8px 10px;width:150px;text-align:right;">Total (Rp)</th>
                                        <th style="padding:8px 6px;width:40px;text-align:center;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="requestItemsBody">
                                    <tr id="req-item-row-1" style="border-bottom:1px solid #e2e8f0;">
                                        <td class="text-center font-bold text-muted req-row-num" style="padding:6px;vertical-align:middle;">1</td>
                                        <td style="padding:6px;">
                                            <input type="text" name="items[1][item_name]" required placeholder="Contoh: Paku 5cm, Semen darurat, dll." class="form-control form-control-sm" style="font-size:12px;">
                                        </td>
                                        <td style="padding:6px;">
                                            <input type="number" name="items[1][quantity]" value="1" min="0.01" step="any" required oninput="calcRequestRowTotal(1)" class="form-control form-control-sm text-center req-item-qty" style="font-size:12px;">
                                        </td>
                                        <td style="padding:6px;">
                                            <input type="text" name="items[1][unit]" placeholder="sak / pcs" class="form-control form-control-sm text-center" style="font-size:12px;">
                                        </td>
                                        <td style="padding:6px;">
                                            <input type="number" name="items[1][unit_price]" value="0" min="0" step="any" required oninput="calcRequestRowTotal(1)" class="form-control form-control-sm text-end req-item-price" style="font-size:12px;font-weight:700;">
                                        </td>
                                        <td style="padding:6px;vertical-align:middle;text-align:right;">
                                            <span id="req-subtotal-1" class="font-bold text-success" style="font-size:12.5px;">Rp 0</span>
                                        </td>
                                        <td style="padding:6px;text-align:center;vertical-align:middle;">
                                            <button type="button" onclick="removeRequestItemRow(1)" class="btn btn-xs btn-outline-danger" title="Hapus Baris" style="padding:2px 6px;">
                                                <x-lucide-trash-2 style="width:13px;height:13px;" />
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- Tombol Tambah Baris --}}
                        <div style="margin-top:8px;">
                            <button type="button" onclick="addRequestItemRow()" class="btn btn-sm btn-outline-primary" style="font-size:11.5px;font-weight:700;display:inline-flex;align-items:center;gap:5px;border-radius:6px;">
                                <x-lucide-circle-plus style="width:14px;height:14px;" /> Tambah Baris Kebutuhan
                            </button>
                        </div>
                    </div>

                    {{-- 3. Total Banner --}}
                    <div style="background:#ecfdf5;border:1.5px solid #a7f3d0;border-radius:10px;padding:12px 18px;display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <div style="font-size:11px;font-weight:700;color:#047857;text-transform:uppercase;">TOTAL NOMINAL PENGAJUAN KAS LAPANGAN</div>
                            <div style="font-size:11px;color:#065f46;">Akumulasi otomatis dari kuantitas x harga seluruh rincian kebutuhan</div>
                        </div>
                        <div style="text-align:right;">
                            <div id="grandTotalDisplay" style="font-size:20px;font-weight:900;color:#047857;letter-spacing:0.5px;">
                                Rp 0
                            </div>
                            <input type="hidden" name="amount" id="hiddenGrandTotal" value="0">
                        </div>
                    </div>

                </div>

                {{-- Modal Footer --}}
                <div style="background:#f8fafc;padding:14px 22px;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:10px;flex-shrink:0;">
                    <button type="button" onclick="closeRequestBalanceModal()" class="btn btn-light border" style="font-size:12.5px;font-weight:600;padding:8px 18px;border-radius:8px;">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-success" style="font-size:13px;font-weight:800;padding:8px 22px;border-radius:8px;background:#059669;border-color:#059669;box-shadow:0 2px 6px rgba(5,150,105,0.3);display:inline-flex;align-items:center;gap:6px;">
                        <x-lucide-send style="width:15px;height:15px;" /> Kirim Pengajuan Kas Lapangan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ==================== MODAL 4: Daftar Permintaan Saldo Kas Proyek ==================== --}}
    <div id="modalBalanceRequests" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#ffffff;border-radius:14px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1),0 10px 10px -5px rgba(0,0,0,0.04);width:100%;max-width:960px;max-height:90vh;display:flex;flex-direction:column;overflow:hidden;">
            
            <div style="background:#0f172a;color:#ffffff;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:34px;height:34px;border-radius:8px;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;font-size:16px;color:#f59e0b;">
                        <x-lucide-inbox />
                    </div>
                    <div>
                        <h3 style="font-size:15px;font-weight:800;margin:0;line-height:1.2;">Daftar Permintaan Saldo Kas Operasional Proyek</h3>
                        <p style="font-size:11px;color:#94a3b8;margin:2px 0 0 0;">Pengajuan penambahan kas dari Admin PO Proyek untuk diverifikasi Pusat / Owner</p>
                    </div>
                </div>
                <button type="button" onclick="closeBalanceRequestsModal()" style="background:none;border:none;color:#94a3b8;cursor:pointer;font-size:18px;line-height:1;padding:4px;">
                    <x-lucide-x />
                </button>
            </div>

            <div style="overflow-y:auto;padding:16px 20px;flex:1;">
                @if(isset($balanceRequests) && $balanceRequests->count() > 0)
                <table style="width:100%;border-collapse:collapse;font-size:12.5px;">
                    <thead>
                        <tr style="border-bottom:2px solid #e2e8f0;background:#f8fafc;color:#475569;text-transform:uppercase;font-size:10.5px;letter-spacing:0.5px;font-weight:700;">
                            <th style="padding:10px 12px;text-align:left;">Tgl / Pemohon</th>
                            <th style="padding:10px 12px;text-align:left;">Proyek</th>
                            <th style="padding:10px 12px;text-align:right;">Nominal Diminta</th>
                            <th style="padding:10px 12px;text-align:center;">Status</th>
                            <th style="padding:10px 12px;text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($balanceRequests as $reqItem)
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:10px 12px;font-size:11.5px;color:#334155;">
                                <strong>{{ $reqItem->created_at->format('d/m/Y H:i') }}</strong>
                                <div class="text-muted">{{ $reqItem->requester?->name ?? 'Admin PO Proyek' }}</div>
                            </td>
                            <td style="padding:10px 12px;">
                                <strong class="text-primary">{{ $reqItem->project?->name ?? '-' }}</strong>
                                <div class="text-muted text-xs">Saldo Saat Ini: Rp {{ number_format($reqItem->project?->operational_balance ?? 0, 0, ',', '.') }}</div>
                            </td>
                            <td style="padding:10px 12px;text-align:right;font-weight:800;color:#047857;font-size:13.5px;white-space:nowrap;">
                                Rp {{ number_format($reqItem->amount, 0, ',', '.') }}
                            </td>
                            <td style="padding:10px 12px;text-align:center;white-space:nowrap;">
                                <span class="badge badge-{{ $reqItem->status_badge_color }}" style="font-size:11px;font-weight:700;">
                                    {{ $reqItem->status_label }}
                                </span>
                                @if($reqItem->status === 'rejected' && $reqItem->rejection_reason)
                                    <div class="text-danger text-xs mt-1" title="{{ $reqItem->rejection_reason }}">Ditolak: {{ \Illuminate\Support\Str::limit($reqItem->rejection_reason, 20) }}</div>
                                @endif
                            </td>
                            <td style="padding:10px 12px;text-align:center;white-space:nowrap;">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('payments.balance-requests.print', $reqItem) }}" target="_blank" class="btn btn-xs btn-outline-secondary" style="font-weight:700;" title="Cetak Dokumen Pengajuan Kas Lapangan">
                                        <x-lucide-printer class="me-1" /> Cetak
                                    </a>
                                    @if($reqItem->status === 'pending')
                                        @if(!$isRoleAdminPOProyek)
                                            <form action="{{ route('payments.approveBalanceRequest', $reqItem) }}" method="POST" class="inline" onsubmit="return confirm('Setujui permintaan saldo Rp {{ number_format($reqItem->amount, 0, ',', '.') }} untuk {{ $reqItem->project?->name }}?')">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-success" title="Setujui & Top-Up Saldo">
                                                    <x-lucide-check class="me-1" /> Setujui
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-xs btn-outline-danger" onclick="promptRejectRequest({{ $reqItem->id }}, '{{ addslashes($reqItem->project?->name) }}')">
                                                <x-lucide-x class="me-1" /> Tolak
                                            </button>
                                        @else
                                            <span class="text-muted text-xs italic">Menunggu Pusat</span>
                                        @endif
                                    @else
                                        <span class="text-muted text-xs">
                                            {{ $reqItem->approver?->name ?? 'Pusat' }} ({{ $reqItem->updated_at->format('d/m/Y') }})
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div style="text-align:center;padding:36px 16px;color:#64748b;">
                    <x-lucide-inbox style="font-size:32px;color:#cbd5e1;margin-bottom:8px;display:inline-block;" />
                    <div style="font-weight:700;font-size:13px;color:#1e293b;">Belum Ada Permintaan Saldo Kas</div>
                    <p style="font-size:12px;margin:4px 0 0 0;">Permintaan saldo kas dari Admin PO Proyek ke Pusat/Owner akan tampil di sini.</p>
                </div>
                @endif
            </div>

            <div style="background:#f8fafc;padding:12px 20px;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;">
                <button type="button" onclick="closeBalanceRequestsModal()" class="btn btn-secondary" style="font-size:12px;font-weight:600;padding:6px 16px;border-radius:6px;">
                    Tutup
                </button>
            </div>

        </div>
    </div>

    {{-- Form Hidden untuk Penolakan Request --}}
    <form id="rejectRequestForm" method="POST" style="display:none;">
        @csrf
        <input type="hidden" name="rejection_reason" id="rejectReasonInput">
    </form>

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

        function openTopupModal(projectId = null, projectName = null) {
            const modal = document.getElementById('modalTopupBalance');
            if (projectId) {
                const select = document.getElementById('topupProjectId');
                if (select) select.value = projectId;
            }
            if (modal) modal.style.display = 'flex';
        }

        function closeTopupModal() {
            const modal = document.getElementById('modalTopupBalance');
            if (modal) modal.style.display = 'none';
        }

        let reqItemCounter = 1;

        function addRequestItemRow() {
            reqItemCounter++;
            const tbody = document.getElementById('requestItemsBody');
            if (!tbody) return;

            const tr = document.createElement('tr');
            tr.id = 'req-item-row-' + reqItemCounter;
            tr.style.borderBottom = '1px solid #e2e8f0';
            tr.innerHTML = `
                <td class="text-center font-bold text-muted req-row-num" style="padding:6px;vertical-align:middle;"></td>
                <td style="padding:6px;">
                    <input type="text" name="items[${reqItemCounter}][item_name]" required placeholder="Contoh: Paku 5cm, Semen darurat, dll." class="form-control form-control-sm" style="font-size:12px;">
                </td>
                <td style="padding:6px;">
                    <input type="number" name="items[${reqItemCounter}][quantity]" value="1" min="0.01" step="any" required oninput="calcRequestRowTotal(${reqItemCounter})" class="form-control form-control-sm text-center req-item-qty" style="font-size:12px;">
                </td>
                <td style="padding:6px;">
                    <input type="text" name="items[${reqItemCounter}][unit]" placeholder="sak / pcs" class="form-control form-control-sm text-center" style="font-size:12px;">
                </td>
                <td style="padding:6px;">
                    <input type="number" name="items[${reqItemCounter}][unit_price]" value="0" min="0" step="any" required oninput="calcRequestRowTotal(${reqItemCounter})" class="form-control form-control-sm text-end req-item-price" style="font-size:12px;font-weight:700;">
                </td>
                <td style="padding:6px;vertical-align:middle;text-align:right;">
                    <span id="req-subtotal-${reqItemCounter}" class="font-bold text-success" style="font-size:12.5px;">Rp 0</span>
                </td>
                <td style="padding:6px;text-align:center;vertical-align:middle;">
                    <button type="button" onclick="removeRequestItemRow(${reqItemCounter})" class="btn btn-xs btn-outline-danger" title="Hapus Baris" style="padding:2px 6px;">
                        <x-lucide-trash-2 style="width:13px;height:13px;" />
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
            renumberRequestRows();
        }

        function removeRequestItemRow(idx) {
            const row = document.getElementById('req-item-row-' + idx);
            const tbody = document.getElementById('requestItemsBody');
            if (tbody && tbody.children.length > 1 && row) {
                row.remove();
                renumberRequestRows();
                calcRequestGrandTotal();
            } else {
                alert('Minimal harus ada 1 baris rincian kebutuhan barang.');
            }
        }

        function renumberRequestRows() {
            const rows = document.querySelectorAll('.req-row-num');
            rows.forEach((td, i) => {
                td.textContent = i + 1;
            });
        }

        function calcRequestRowTotal(idx) {
            const row = document.getElementById('req-item-row-' + idx);
            if (!row) return;
            const qtyInput = row.querySelector('.req-item-qty');
            const priceInput = row.querySelector('.req-item-price');
            const subtotalSpan = document.getElementById('req-subtotal-' + idx);

            const qty = parseFloat(qtyInput ? qtyInput.value : 0) || 0;
            const price = parseFloat(priceInput ? priceInput.value : 0) || 0;
            const subtotal = qty * price;

            if (subtotalSpan) {
                subtotalSpan.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(subtotal);
            }
            calcRequestGrandTotal();
        }

        function calcRequestGrandTotal() {
            let grandTotal = 0;
            const rows = document.querySelectorAll('#requestItemsBody tr');
            rows.forEach(r => {
                const qty = parseFloat(r.querySelector('.req-item-qty')?.value || 0) || 0;
                const price = parseFloat(r.querySelector('.req-item-price')?.value || 0) || 0;
                grandTotal += (qty * price);
            });

            const displayEl = document.getElementById('grandTotalDisplay');
            const hiddenEl = document.getElementById('hiddenGrandTotal');
            if (displayEl) {
                displayEl.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(grandTotal);
            }
            if (hiddenEl) {
                hiddenEl.value = grandTotal;
            }
        }

        function openRequestBalanceModal(projectId = null, projectName = null, projectBalance = null) {
            const modal = document.getElementById('modalRequestBalance');
            if (projectId) {
                const hiddenInput = document.getElementById('reqProjectId');
                if (hiddenInput) hiddenInput.value = projectId;
                const nameDisplay = document.getElementById('reqProjectNameDisplay');
                if (nameDisplay && projectName) {
                    nameDisplay.textContent = projectName;
                    nameDisplay.setAttribute('title', projectName);
                }
                const balanceDisplay = document.getElementById('reqProjectBalanceDisplay');
                if (balanceDisplay && projectBalance !== null) {
                    balanceDisplay.textContent = 'Saldo: Rp ' + new Intl.NumberFormat('id-ID').format(projectBalance);
                }
            }
            if (modal) modal.style.display = 'flex';
        }

        function closeRequestBalanceModal() {
            const modal = document.getElementById('modalRequestBalance');
            if (modal) modal.style.display = 'none';
        }

        function openBalanceRequestsModal() {
            const modal = document.getElementById('modalBalanceRequests');
            if (modal) modal.style.display = 'flex';
        }

        function closeBalanceRequestsModal() {
            const modal = document.getElementById('modalBalanceRequests');
            if (modal) modal.style.display = 'none';
        }

        function promptRejectRequest(requestId, projectName) {
            const reason = prompt('Masukkan alasan penolakan permintaan saldo kas untuk proyek ' + projectName + ':');
            if (reason !== null && reason.trim() !== '') {
                const form = document.getElementById('rejectRequestForm');
                form.action = '/payments/balance-requests/' + requestId + '/reject';
                document.getElementById('rejectReasonInput').value = reason.trim();
                form.submit();
            }
        }

        function toggleBalanceWeek(idx) {
            const content = document.getElementById('balance-week-content-' + idx);
            const icon = document.getElementById('balance-week-icon-' + idx);
            if (!content) return;
            const isHidden = (content.style.display === 'none');
            content.style.display = isHidden ? 'block' : 'none';
            if (icon) {
                icon.style.transform = isHidden ? 'rotate(0deg)' : 'rotate(-90deg)';
            }
        }

        function toggleAllBalanceWeeks(expand) {
            document.querySelectorAll('.balance-week-content').forEach(c => {
                c.style.display = expand ? 'block' : 'none';
            });
            document.querySelectorAll('.balance-week-icon').forEach(icon => {
                icon.style.transform = expand ? 'rotate(0deg)' : 'rotate(-90deg)';
            });
        }

        function toggleModalBalanceWeek(idx) {
            const content = document.getElementById('modal-balance-content-' + idx);
            const icon = document.getElementById('modal-balance-icon-' + idx);
            if (!content) return;
            const isHidden = (content.style.display === 'none');
            content.style.display = isHidden ? 'block' : 'none';
            if (icon) {
                icon.style.transform = isHidden ? 'rotate(0deg)' : 'rotate(-90deg)';
            }
        }

        function openBalanceHistoryModal() {
            const modal = document.getElementById('modalBalanceHistory');
            if (modal) modal.style.display = 'flex';
        }

        function closeBalanceHistoryModal() {
            const modal = document.getElementById('modalBalanceHistory');
            if (modal) modal.style.display = 'none';
        }

        function formatRupiahPreview(val) {
            const previewEl = document.getElementById('topupRupiahPreview');
            if (!val || isNaN(val) || Number(val) <= 0) {
                previewEl.style.display = 'none';
                return;
            }
            previewEl.style.display = 'block';
            previewEl.textContent = 'Preview: Rp ' + new Intl.NumberFormat('id-ID').format(val);
        }

        function formatRequestRupiahPreview(val) {
            const previewEl = document.getElementById('reqRupiahPreview');
            if (!val || isNaN(val) || Number(val) <= 0) {
                previewEl.style.display = 'none';
                return;
            }
            previewEl.style.display = 'block';
            previewEl.textContent = 'Preview: Rp ' + new Intl.NumberFormat('id-ID').format(val);
        }

        // Close modal on Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeTopupModal();
                closeBalanceHistoryModal();
                closeRequestBalanceModal();
                closeBalanceRequestsModal();
            }
        });
    </script>
    @endpush
</x-app-layout>
