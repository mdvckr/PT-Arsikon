<x-app-layout>
    <x-slot name="title">{{ $isRoleAdminPOProyek ? 'Kas & Saldo Operasional Proyek' : 'Manajemen Pembayaran PO & Kas Proyek' }}</x-slot>

    @push('styles')
    <style>
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
    <div class="flex items-center justify-between mb-6 flex-wrap gap-4 border-b border-slate-200/80 dark:border-slate-800 pb-5">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-orange-500/10 text-orange-600 flex items-center justify-center border border-orange-500/20 shadow-2xs">
                <x-lucide-wallet-cards class="w-5 h-5" />
            </div>
            <div>
                @if($isRoleAdminPOProyek)
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                        Saldo Kas Operasional Proyek
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Pengelolaan saldo kas harian proyek, riwayat mutasi nota belanja lapangan, dan pengajuan kas ke PO Pusat.
                    </p>
                @else
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                        Keuangan &amp; Kas Operasional Proyek
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Pusat kendali likuiditas lapangan, persetujuan permohonan saldo kas proyek, dan audit pembayaran faktur vendor.
                    </p>
                @endif
            </div>
        </div>

        @if(!$isRoleAdminPOProyek)
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('payments.balance-requests.index') }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-bold border transition shadow-2xs inline-flex items-center gap-2 {{ $pendingBalanceRequestsCount > 0 ? 'bg-amber-500 hover:bg-amber-600 text-white border-amber-600 shadow-amber-500/20' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 border-slate-200 dark:border-slate-700 hover:bg-slate-50' }}">
                <x-lucide-inbox class="w-4 h-4" />
                <span>Permintaan Saldo</span>
                @if($pendingBalanceRequestsCount > 0)
                    <span class="bg-rose-600 text-white text-[10.5px] px-2 py-0.5 rounded-full font-black ml-0.5 animate-bounce">{{ $pendingBalanceRequestsCount }}</span>
                @endif
            </a>
            @if(auth()->user()->hasAnyRole(['Admin PO', 'Admin Pengadaan', 'Super Admin']))
            <button type="button" onclick="openTopupModal()" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white transition shadow-2xs inline-flex items-center gap-2">
                <x-lucide-circle-plus class="w-4 h-4 text-emerald-400" />
                <span>Top-Up Saldo</span>
            </button>
            @endif
        </div>
        @endif
    </div>

    @if($isRoleAdminPOProyek)
        {{-- ==================== KHUSUS ADMIN PO PROYEK: KAS & PENGELOLAAN SALDO NOTA HARIAN ==================== --}}
        
        {{-- Card: Kas Proyek Lapangan (Admin PO Proyek) --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 p-6 shadow-2xs mb-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            SALDO KAS PROYEK LAPANGAN
                        </span>
                        @if($assignedProject)
                            <span class="text-xs text-slate-500 font-semibold">{{ $assignedProject->name }} ({{ $assignedProject->code ?? 'PRJ' }})</span>
                        @endif
                    </div>
                    <div class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white font-mono tracking-tight tabular-nums">
                        Rp {{ number_format($metrics['total_operational_balance'] ?? 0, 0, ',', '.') }}
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Saldo aktif siap pakai untuk belanja nota harian lapangan</p>
                </div>
                <div>
                    <button type="button" onclick="openRequestBalanceModal()" class="btn btn-sm bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow-xs transition inline-flex items-center gap-2">
                        <x-lucide-hand-coins class="w-4 h-4" />
                        <span>Ajukan Kas Lapangan ke PO Pusat</span>
                    </button>
                </div>
            </div>
        </div>
        {{-- ==================== KHUSUS ADMIN PO PROYEK: KAS & PENGELOLAAN SALDO NOTA HARIAN ==================== --}}
        
        {{-- Card 1: Status Pengajuan Permintaan Saldo Kas ke Pusat / Owner --}}
        <div class="card mb-4" style="border:1.5px solid #cbd5e1;border-radius:12px;overflow:hidden;">
            <div style="padding:14px 20px;background:#f8fafc;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                <div class="flex items-center gap-2">
                    <x-lucide-hand-coins class="text-emerald-600" style="font-size:16px;" />
                    <h3 style="font-size:15px;font-weight:700;color:#0f172a;margin:0;">Status Pengajuan Permintaan Saldo Kas ke Admin PO Pusat</h3>
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
                             class="bg-slate-100/90 hover:bg-slate-200/80 dark:bg-slate-800/90 dark:hover:bg-slate-800 border-b border-slate-200/80 dark:border-slate-700/80 px-4 py-3 flex justify-between items-center flex-wrap gap-2 cursor-pointer transition-colors select-none">
                            
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                    <x-lucide-calendar-range class="w-4 h-4" />
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100 m-0">
                                            {{ $week->week_label }}
                                        </h4>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10.5px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800">
                                            Periode 1 Minggu
                                        </span>
                                    </div>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">
                                        Total {{ count($week->days) }} hari transaksi &bull; Klik untuk buka/tutup
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700 px-2.5 py-1 rounded-md shadow-xs">
                                    <x-lucide-receipt class="inline w-3.5 h-3.5 me-1 text-slate-400" /> {{ $week->count }} Mutasi
                                </span>
                                @if($week->total_topup > 0)
                                <span class="text-xs font-mono font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 px-2.5 py-1 rounded-md">
                                    + Rp {{ number_format($week->total_topup, 0, ',', '.') }}
                                </span>
                                @endif
                                @if($week->total_deduction > 0)
                                <span class="text-xs font-mono font-bold text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 px-2.5 py-1 rounded-md">
                                    - Rp {{ number_format($week->total_deduction, 0, ',', '.') }}
                                </span>
                                @endif
                                <x-lucide-chevron-down class="balance-week-icon w-4 h-4 text-slate-400 transition-transform duration-200" id="balance-week-icon-{{ $wIdx }}" />
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

        {{-- Bento Grid: Treasury Vault & Operational Dispatch Station --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 mb-6">
            {{-- Left Bento: Master Petty Cash Vault & Multi-Project Matrix (lg:col-span-7) --}}
            <div class="lg:col-span-7 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 p-5 sm:p-6 shadow-2xs flex flex-col justify-between space-y-4">
                <div>
                    <div class="flex items-center justify-between gap-3 mb-2 flex-wrap">
                        <div class="flex items-center gap-2">
                            <span class="text-[10.5px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">
                                TOTAL KAS OPERASIONAL LAPANGAN
                            </span>
                        </div>
                        <span class="text-[10px] font-mono font-bold text-slate-400 dark:text-slate-500 bg-slate-50 dark:bg-slate-800 px-2 py-0.5 rounded border border-slate-200/60 dark:border-slate-700">
                            PETTY CASH VAULT
                        </span>
                    </div>

                    <div class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white font-mono tracking-tight tabular-nums">
                        Rp {{ number_format($metrics['total_operational_balance'] ?? 0, 0, ',', '.') }}
                    </div>

                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-2 flex items-center gap-2 flex-wrap">
                        <span class="font-medium text-slate-700 dark:text-slate-300">
                            Total saldo aktif siap pakai di seluruh proyek
                        </span>
                        <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                        <span class="text-emerald-700 dark:text-emerald-400 font-semibold font-mono">
                            ⚡ {{ $metrics['instant_count'] ?? 0 }}x Instan Berhasil (Rp {{ number_format($metrics['instant_verified_amount'] ?? 0, 0, ',', '.') }})
                        </span>
                    </div>
                </div>

                {{-- Embedded Project Pockets Grid --}}
                @if($projects && $projects->count() > 0)
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80">
                    <div class="text-[10.5px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2 flex items-center justify-between">
                        <span class="flex items-center gap-1.5"><x-lucide-building-2 class="w-3.5 h-3.5" /> Saldo Khusus per Proyek</span>
                        <span class="font-mono text-[10px] text-slate-400">{{ $projects->count() }} Proyek Terdaftar</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-36 overflow-y-auto pr-1 scrollbar-thin">
                        @foreach($projects as $prj)
                        <div class="bg-slate-50/80 dark:bg-slate-800/50 rounded-xl border border-slate-200/70 dark:border-slate-700/60 p-2.5 flex items-center justify-between gap-2 hover:bg-slate-100/80 transition-colors">
                            <div class="overflow-hidden min-w-0">
                                <div class="font-bold text-slate-800 dark:text-slate-200 text-xs truncate" title="{{ $prj->name }}">
                                    {{ $prj->name }}
                                </div>
                                <div class="text-[10px] font-mono text-slate-400">{{ $prj->code ?? 'PRJ' }}</div>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="font-mono font-bold text-slate-900 dark:text-white text-xs tabular-nums">
                                    Rp {{ number_format($prj->operational_balance, 0, ',', '.') }}
                                </div>
                                @if(auth()->user()->hasAnyRole(['Admin PO', 'Admin Pengadaan', 'Super Admin']))
                                <button type="button" onclick="openTopupModal({{ $prj->id }}, '{{ addslashes($prj->name) }}')" class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 hover:underline">
                                    + Top-up
                                </button>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Action Bar under Vault --}}
                <div class="pt-2 flex items-center gap-2 flex-wrap">
                    @if(auth()->user()->hasAnyRole(['Admin PO', 'Admin Pengadaan', 'Super Admin']))
                    <button type="button" onclick="openTopupModal()" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white transition shadow-2xs inline-flex items-center gap-1.5">
                        <x-lucide-circle-plus class="w-3.5 h-3.5 text-emerald-400" />
                        <span>Top-Up Saldo Langsung</span>
                    </button>
                    @endif
                    <button type="button" onclick="openBalanceHistoryModal()" class="px-3 py-2 rounded-xl text-xs font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 transition shadow-2xs inline-flex items-center gap-1.5">
                        <x-lucide-history class="w-3.5 h-3.5 text-slate-400" />
                        <span>Riwayat Mutasi Saldo</span>
                    </button>
                </div>
            </div>

            {{-- Right Bento: Attention & Operational Dispatch Station (lg:col-span-5) --}}
            <div class="lg:col-span-5 bg-white dark:bg-slate-900 rounded-2xl border p-5 sm:p-6 shadow-2xs flex flex-col justify-between {{ ($pendingBalanceRequestsCount ?? 0) > 0 ? 'border-amber-300 dark:border-amber-800/80 bg-gradient-to-br from-amber-500/[0.05] via-white to-amber-500/[0.02] dark:from-amber-950/20 dark:via-slate-900 dark:to-transparent' : 'border-slate-200/90 dark:border-slate-800' }}">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-3">
                        @if(($pendingBalanceRequestsCount ?? 0) > 0)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 border border-rose-200 dark:border-rose-800 uppercase tracking-wider animate-pulse">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                Menunggu PO Pusat
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 uppercase tracking-wider">
                                <x-lucide-circle-check class="w-3 h-3" />
                                Saldo Terkendali
                            </span>
                        @endif

                        <a href="{{ route('payments.balance-requests.index') }}" class="text-[11px] font-semibold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 inline-flex items-center gap-1 transition">
                            <span>Buka Halaman</span>
                            <x-lucide-external-link class="w-3 h-3" />
                        </a>
                    </div>

                    @if(($pendingBalanceRequestsCount ?? 0) > 0)
                        <h4 class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-tight leading-snug">
                            Ada {{ $pendingBalanceRequestsCount }} Pengajuan Kas Saldo Proyek Menunggu Persetujuan
                        </h4>
                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-2 leading-relaxed">
                            Admin PO Proyek di lapangan telah mengajukan kebutuhan dana belanja nota harian. Sebagai Manager/Bank kas pengadaan, persetujuan Anda bersifat final dan langsung menambah saldo operasional proyek.
                        </p>
                    @else
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white leading-snug">
                            Semua Pengajuan Kas Selesai Diproses
                        </h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
                            Tidak ada permohonan saldo kas yang tertunda. Riwayat dan dokumen pengajuan terdahulu dapat ditinjau kapan saja melalui rekapitulasi mingguan.
                        </p>
                    @endif
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-800/80 mt-4">
                    <a href="{{ route('payments.balance-requests.index') }}" 
                       class="w-full py-2.5 px-4 rounded-xl text-xs font-bold transition shadow-2xs flex items-center justify-between {{ ($pendingBalanceRequestsCount ?? 0) > 0 ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200' }}">
                        <span class="inline-flex items-center gap-2">
                            <x-lucide-inbox class="w-4 h-4" />
                            <span>{{ ($pendingBalanceRequestsCount ?? 0) > 0 ? 'Tinjau & Setujui Permintaan Kas (' . $pendingBalanceRequestsCount . ')' : 'Lihat Rekapitulasi Pengajuan Kas' }}</span>
                        </span>
                        <x-lucide-arrow-right class="w-4 h-4" />
                    </a>
                </div>
            </div>
        </div>

    {{-- ==================== Data Table Card ==================== --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-2xs overflow-hidden mb-6">
        <div class="p-4 sm:px-6 border-b border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/60 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-2.5 flex-wrap">
                <div class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center justify-center border border-slate-200/80 dark:border-slate-700 shadow-2xs">
                    <x-lucide-receipt class="w-4 h-4" />
                </div>
                <span class="text-sm font-bold text-slate-900 dark:text-white">Daftar Transaksi Pembayaran</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                    {{ $payments->total() }} Data
                </span>

                {{-- Global Expand / Collapse All Buttons (Only when data exists) --}}
                @if($payments->total() > 0)
                <div class="inline-flex items-center gap-1.5 ml-2">
                    <button type="button" onclick="toggleAllPoGroups(true)" class="px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-[11px] font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition inline-flex items-center gap-1 shadow-2xs" title="Buka semua riwayat pembayaran">
                        <x-lucide-chevrons-down class="w-3.5 h-3.5 text-slate-400" /> Buka Semua
                    </button>
                    <button type="button" onclick="toggleAllPoGroups(false)" class="px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-[11px] font-semibold text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 transition inline-flex items-center gap-1 shadow-2xs" title="Tutup semua riwayat pembayaran">
                        <x-lucide-chevrons-up class="w-3.5 h-3.5 text-slate-400" /> Tutup Semua
                    </button>
                </div>
                @endif
            </div>
            <div class="flex items-center gap-3">
                <form method="GET" action="{{ route('payments.index') }}" class="m-0 relative">
                    <x-lucide-search class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor pembayaran, PO, supplier..." 
                           class="text-xs h-8 pl-8 pr-7 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 w-44 sm:w-64 focus:ring-2 focus:ring-slate-400/20 shadow-2xs">
                    @if(request('search'))
                    <a href="{{ route('payments.index') }}" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition" title="Hapus pencarian">
                        <x-lucide-x class="w-3.5 h-3.5" />
                    </a>
                    @endif
                </form>
                @if($payments->total() > 0)
                <div class="text-xs text-slate-400 font-medium whitespace-nowrap hidden sm:block">
                    Hal <strong class="text-slate-700 dark:text-slate-300">{{ $payments->currentPage() }}</strong>/{{ max(1, $payments->lastPage()) }}
                </div>
                @endif
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
                        <td colspan="9" class="py-14 px-4 text-center bg-white dark:bg-slate-900">
                            <div class="max-w-md mx-auto space-y-3">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 mx-auto flex items-center justify-center border border-slate-200/80 dark:border-slate-700 shadow-2xs">
                                    <x-lucide-receipt class="w-6 h-6 text-slate-400" />
                                </div>
                                <div class="space-y-1">
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">
                                        {{ request('search') ? 'Pembayaran Tidak Ditemukan' : 'Belum Ada Transaksi Pembayaran' }}
                                    </h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                        @if(request('search'))
                                            Tidak ditemukan data pembayaran yang cocok dengan kata kunci &ldquo;<strong>{{ request('search') }}</strong>&rdquo;.
                                        @else
                                            Belum ada transaksi pembayaran yang dicatat. Seluruh riwayat pembayaran termin vendor Purchase Order (PO) maupun pencairan kas instan proyek akan tersusun rapi di sini.
                                        @endif
                                    </p>
                                </div>
                                @if(request('search'))
                                <div class="pt-2 flex items-center justify-center">
                                    <a href="{{ route('payments.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700 transition shadow-2xs">
                                        <x-lucide-rotate-ccw class="w-3.5 h-3.5" /> Hapus Pencarian
                                    </a>
                                </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($payments->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
            {{ $payments->links() }}
        </div>
        @endif
    </div>
    @endif

    {{-- ==================== MODAL 1: Input / Top-Up Saldo Admin PO ==================== --}}
    <div id="modalTopupBalance" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#ffffff;border-radius:14px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1),0 10px 10px -5px rgba(0,0,0,0.04);width:100%;max-width:520px;overflow:hidden;animation:fadeIn 0.2s ease;">
            
            {{-- Modal Header --}}
            <div style="background:#0f172a;color:#ffffff;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:34px;height:34px;border-radius:8px;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;font-size:16px;color:#10b981;">
                        <x-lucide-wallet />
                    </div>
                    <div>
                        <h3 style="font-size:15px;font-weight:800;margin:0;line-height:1.2;">Top-Up Saldo Kas Proyek</h3>
                        <p style="font-size:11px;color:#94a3b8;margin:2px 0 0 0;">Petty cash operasional Admin PO untuk pembayaran nota harian</p>
                    </div>
                </div>
                <button type="button" onclick="closeTopupModal()" style="background:none;border:none;color:#94a3b8;cursor:pointer;font-size:18px;line-height:1;padding:4px;">
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
    <div id="modalBalanceHistory" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.6);backdrop-filter:blur(4px);z-index:9999;align-items:center;justify-content:center;padding:20px;">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200/80 dark:border-slate-800 w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150">
            
            {{-- Modal Header --}}
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0 border border-sky-500/20">
                        <x-lucide-history class="w-5 h-5" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold text-slate-900 dark:text-white leading-tight">Riwayat Mutasi Saldo Kas Proyek</h3>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                Ledger Kas
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Audit pencatatan penambahan top-up &amp; pemotongan pembayaran instan proyek</p>
                    </div>
                </div>
                <button type="button" onclick="closeBalanceHistoryModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 dark:hover:text-slate-200 transition-colors">
                    <x-lucide-x class="w-5 h-5" />
                </button>
            </div>

            {{-- Modal Body Table (Grouped per 1 Minggu & Hari) --}}
            <div class="overflow-y-auto p-6 flex-1">
                @if(isset($groupedBalanceWeeks) && $groupedBalanceWeeks->count() > 0)
                    @foreach($groupedBalanceWeeks as $wIdx => $week)
                        <div class="rounded-xl border border-slate-200/80 dark:border-slate-800 overflow-hidden shadow-sm mb-4 bg-white dark:bg-slate-900">
                            
                            {{-- Header Minggu Modal --}}
                            <div onclick="toggleModalBalanceWeek('{{ $wIdx }}')"
                                 class="bg-slate-100/80 hover:bg-slate-200/70 dark:bg-slate-800/80 dark:hover:bg-slate-800 border-b border-slate-200/80 dark:border-slate-700/80 px-4 py-3 flex justify-between items-center flex-wrap gap-2 cursor-pointer transition-colors select-none">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                        <x-lucide-calendar-range class="w-4 h-4" />
                                    </div>
                                    <strong class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $week->week_label }}</strong>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800">1 Minggu</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700 px-2 py-0.5 rounded">
                                        {{ $week->count }} Mutasi
                                    </span>
                                    @if($week->total_topup > 0)
                                        <span class="text-[11px] font-mono font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 px-2 py-0.5 rounded">
                                            +Rp {{ number_format($week->total_topup, 0, ',', '.') }}
                                        </span>
                                    @endif
                                    @if($week->total_deduction > 0)
                                        <span class="text-[11px] font-mono font-bold text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 px-2 py-0.5 rounded">
                                            -Rp {{ number_format($week->total_deduction, 0, ',', '.') }}
                                        </span>
                                    @endif
                                    <x-lucide-chevron-down class="modal-balance-icon w-4 h-4 text-slate-400 transition-transform duration-200" id="modal-balance-icon-{{ $wIdx }}" />
                                </div>
                            </div>

                            {{-- Isi Hari di Modal --}}
                            <div id="modal-balance-content-{{ $wIdx }}" class="modal-balance-content" style="display:block;">
                                @foreach($week->days as $dIdx => $day)
                                    <div class="border-b border-slate-100 dark:border-slate-800 last:border-b-0">
                                        <div class="bg-slate-50/75 dark:bg-slate-800/40 px-4 py-2 flex justify-between items-center flex-wrap gap-2 text-xs border-b border-slate-100 dark:border-slate-800/80">
                                            <div class="flex items-center gap-2">
                                                <x-lucide-calendar class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
                                                <strong class="text-slate-800 dark:text-slate-200 text-xs">Hari: {{ $day->date_label }}</strong>
                                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">{{ $day->count }} mutasi</span>
                                            </div>
                                            <div class="font-mono font-bold text-xs flex gap-3">
                                                @if($day->total_topup > 0)
                                                    <span class="text-emerald-600 dark:text-emerald-400">+Rp {{ number_format($day->total_topup, 0, ',', '.') }}</span>
                                                @endif
                                                @if($day->total_deduction > 0)
                                                    <span class="text-rose-600 dark:text-rose-400">-Rp {{ number_format($day->total_deduction, 0, ',', '.') }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <table class="w-full text-left text-xs divide-y divide-slate-100 dark:divide-slate-800">
                                            <thead>
                                                <tr class="bg-white dark:bg-slate-900 text-slate-400 text-[10px] uppercase font-semibold">
                                                    <th class="py-2 px-3">Waktu</th>
                                                    <th class="py-2 px-3">Proyek</th>
                                                    <th class="py-2 px-3">Tipe</th>
                                                    <th class="py-2 px-3 text-right">Nominal</th>
                                                    <th class="py-2 px-3 text-right">Saldo Akhir</th>
                                                    <th class="py-2 px-3">Catatan</th>
                                                    <th class="py-2 px-3">Oleh</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60 bg-white dark:bg-slate-900">
                                                @foreach($day->logs as $log)
                                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                                                    <td class="py-2 px-3 text-slate-400 font-mono text-[11px] whitespace-nowrap">
                                                        {{ $log->created_at ? $log->created_at->format('H:i') : '-' }}
                                                    </td>
                                                    <td class="py-2 px-3 font-semibold text-slate-800 dark:text-slate-200">
                                                        {{ $log->project?->name ?? '-' }}
                                                    </td>
                                                    <td class="py-2 px-3">
                                                        @if($log->type === 'topup')
                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800">
                                                                Top-Up
                                                            </span>
                                                        @elseif($log->type === 'payment_deduction')
                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-sky-50 text-sky-700 border border-sky-200 dark:bg-sky-950/40 dark:text-sky-300 dark:border-sky-800">
                                                                Potong Nota
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-700">{{ $log->type_label }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-2 px-3 text-right font-mono font-bold whitespace-nowrap text-xs {{ $log->type === 'topup' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                                        {{ $log->type === 'topup' ? '+' : '-' }} Rp {{ number_format($log->amount, 0, ',', '.') }}
                                                    </td>
                                                    <td class="py-2 px-3 text-right font-mono font-bold text-slate-800 dark:text-slate-200 whitespace-nowrap text-xs">
                                                        Rp {{ number_format($log->balance_after, 0, ',', '.') }}
                                                    </td>
                                                    <td class="py-2 px-3 text-slate-600 dark:text-slate-400 text-[11px] max-w-[200px]">
                                                        <div>{{ $log->notes ?: '-' }}</div>
                                                        @if($log->reference_number)
                                                            <div class="font-mono text-[10px] text-slate-400">Ref: {{ $log->reference_number }}</div>
                                                        @endif
                                                    </td>
                                                    <td class="py-2 px-3 text-slate-500 text-[11px] whitespace-nowrap">
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
                    <div class="text-center py-12 px-4">
                        <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-3 text-slate-400">
                            <x-lucide-history class="w-6 h-6" />
                        </div>
                        <div class="font-bold text-sm text-slate-800 dark:text-slate-200">Belum Ada Riwayat Mutasi Saldo</div>
                        <p class="text-xs text-slate-500 mt-1">Transaksi top-up dan pemotongan pembayaran instan akan tercatat otomatis di sini.</p>
                    </div>
                @endif
            </div>

            {{-- Modal Footer --}}
            <div class="px-6 py-3.5 border-t border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex justify-end shrink-0">
                <button type="button" onclick="closeBalanceHistoryModal()" class="px-4 py-2 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm transition-colors">
                    Tutup
                </button>
            </div>

        </div>
    </div>

    {{-- ==================== MODAL 3: Formulir PENGAJUAN KAS LAPANGAN (Admin PO Proyek ke PO Pusat) ==================== --}}
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
                        <p style="font-size:11.5px;color:#ffedd5;margin:2px 0 0 0;">Formulir resmi permohonan dana kas operasional (petty cash) proyek ke Admin PO Pusat (Manager Pengadaan)</p>
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
