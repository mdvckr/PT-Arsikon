<x-app-layout>
    <x-slot name="title">Detail Peminjaman Alat #{{ $toolLoan->loan_number }}</x-slot>

    @push('styles')
    <style>
        .tool-detail-layout {
            display: grid;
            grid-template-columns: 1fr 340px;
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
            width: 140px;
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

        .return-item-card {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 10px;
            transition: border-color 0.15s ease;
        }

        .return-item-card:hover {
            border-color: #94a3b8;
        }

        @media (max-width: 992px) {
            .tool-detail-layout {
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
        <a href="{{ route('tool-assignments.index') }}">Peminjaman Alat</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:9px;"></i></span>
        <span>Detail #{{ $toolLoan->loan_number }}</span>
    </div>

    {{-- Top Action & Header Bar --}}
    <div class="flex items-center justify-between mb-3 page-actions-header">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <a href="{{ route('tool-assignments.index') }}" class="btn btn-light border btn-sm" style="border-radius:6px;padding:5px 10px;font-size:11.5px;color:#475569;">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
            <div>
                <h2 class="fw-700" style="font-size:15px;color:#0f172a;margin:0;display:inline-flex;align-items:center;gap:6px;">
                    #{{ $toolLoan->loan_number }}
                </h2>
            </div>
            @php
                if ($toolLoan->status === 'returned') {
                    if ($toolLoan->returned_lost > 0) {
                        $statusBadge = '<span class="badge" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca;font-size:11px;padding:3px 9px;border-radius:12px;font-weight:700;"><i class="fas fa-circle-exclamation me-1"></i> Dikembalikan (' . $toolLoan->returned_lost . ' Hilang)</span>';
                    } elseif ($toolLoan->returned_damaged > 0) {
                        $statusBadge = '<span class="badge" style="background:#fff7ed;color:#ea580c;border:1px solid #fed7aa;font-size:11px;padding:3px 9px;border-radius:12px;font-weight:700;"><i class="fas fa-triangle-exclamation me-1"></i> Dikembalikan (' . $toolLoan->returned_damaged . ' Rusak)</span>';
                    } else {
                        $statusBadge = '<span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:11px;padding:3px 9px;border-radius:12px;font-weight:700;"><i class="fas fa-check me-1"></i> Dikembalikan Lengkap</span>';
                    }
                } elseif ($toolLoan->status === 'lost') {
                    $statusBadge = '<span class="badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-size:11px;padding:3px 9px;border-radius:12px;font-weight:700;"><i class="fas fa-circle-xmark me-1"></i> Hilang Total</span>';
                } else {
                    $statusBadge = match ($toolLoan->status) {
                        'pending'   => '<span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-size:10.5px;padding:2px 8px;border-radius:12px;font-weight:600;"><i class="fas fa-hourglass-half me-1"></i> Menunggu Persetujuan</span>',
                        'overdue'   => '<span class="badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-size:10.5px;padding:2px 8px;border-radius:12px;font-weight:600;"><i class="fas fa-clock me-1"></i> Terlambat</span>',
                        'rejected'  => '<span class="badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-size:10.5px;padding:2px 8px;border-radius:12px;font-weight:600;"><i class="fas fa-xmark me-1"></i> Ditolak</span>',
                        'cancelled' => '<span class="badge" style="background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;font-size:10.5px;padding:2px 8px;border-radius:12px;font-weight:600;"><i class="fas fa-ban me-1"></i> Dibatalkan</span>',
                        default     => '<span class="badge" style="background:#f5f3ff;color:#6d28d9;border:1px solid #ddd6fe;font-size:10.5px;padding:2px 8px;border-radius:12px;font-weight:600;"><i class="fas fa-hand-holding me-1"></i> Dipinjam</span>',
                    };
                }
            @endphp
            {!! $statusBadge !!}
        </div>

        <div style="display:flex;align-items:center;gap:6px;">
            @if(in_array($toolLoan->status, ['active', 'pending']))
                @can('create distributions')
                <a href="{{ route('distributions.create', ['tool_loan_id' => $toolLoan->id]) }}" class="btn btn-primary btn-sm" style="font-size:11.5px;padding:5px 12px;border-radius:6px;display:inline-flex;align-items:center;gap:5px;box-shadow:0 2px 4px rgba(37,99,235,0.2);">
                    <i class="fas fa-truck-fast"></i> Buat Surat Jalan
                </a>
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

    @if($toolLoan->status === 'cancelled')
    <div class="alert alert-danger mb-3" style="display:flex;align-items:center;gap:10px;border:1px solid #fecaca;background:#fef2f2;border-radius:8px;padding:10px 14px;">
        <i class="fas fa-ban" style="font-size:16px;color:#dc2626;"></i>
        <div>
            <strong style="color:#991b1b;font-size:12.5px;">Peminjaman Ini Telah Dibatalkan</strong>
            <div class="text-muted" style="font-size:11px;margin-top:1px;">
                Oleh {{ $toolLoan->cancelledBy?->name ?? '-' }} pada {{ $toolLoan->cancelled_at?->format('d/m/Y H:i') ?? '-' }}.
            </div>
            @if($toolLoan->cancellation_reason)
            <div style="font-size:11.5px;margin-top:2px;color:#991b1b;">
                <strong>Alasan:</strong> {{ $toolLoan->cancellation_reason }}
            </div>
            @endif
        </div>
    </div>
    @endif

    <div class="tool-detail-layout mb-3">
        {{-- Left: Transaction Info & Tool Items List --}}
        <div style="display:flex;flex-direction:column;gap:16px;">
            
            {{-- Detail Peminjaman Card --}}
            <div class="card" style="border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
                <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:10px 14px;display:flex;justify-content:space-between;align-items:center;">
                    <div style="display:flex;align-items:center;gap:6px;">
                        <i class="fas fa-clipboard-user text-primary" style="font-size:13px;"></i>
                        <span class="card-title" style="font-size:13px;font-weight:700;color:#1e293b;">Informasi Peminjaman</span>
                    </div>
                    <span class="text-muted" style="font-size:11px;">Oleh: <strong>{{ $toolLoan->assignedBy?->name ?? '-' }}</strong></span>
                </div>
                <div class="card-body" style="padding:12px 14px;">
                    <div class="info-row">
                        <span class="info-label">Nama Peminjam</span>
                        <span class="info-val" style="color:#0f172a;">{{ $toolLoan->borrower_name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">No. Telepon / HP</span>
                        <span class="info-val">{{ $toolLoan->borrower_phone ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Lokasi / Site Proyek</span>
                        <span class="info-val">{{ $toolLoan->location_name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Gudang Asal Alat</span>
                        <span class="info-val">{{ $toolLoan->fromWarehouse?->name ?? '-' }} {{ $toolLoan->fromWarehouse?->is_central ? '(Pusat)' : '' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tanggal Pinjam</span>
                        <span class="info-val">{{ \Carbon\Carbon::parse($toolLoan->assigned_at)->format('d/m/Y') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Estimasi Kembali</span>
                        <span class="info-val">
                            @if($toolLoan->expected_return_at)
                                @php
                                    $expDate = \Carbon\Carbon::parse($toolLoan->expected_return_at);
                                    $isOverdue = in_array($toolLoan->status, ['active', 'overdue']) && $expDate->isPast();
                                @endphp
                                <span class="{{ $isOverdue ? 'text-danger fw-700' : '' }}">
                                    {{ $expDate->format('d/m/Y') }}
                                    @if($isOverdue) <i class="fas fa-triangle-exclamation ms-1"></i> @endif
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Keperluan / Catatan</span>
                        <span class="info-val" style="font-weight:500;">{{ $toolLoan->notes ?? '-' }}</span>
                    </div>

                    {{-- Rekapitulasi Pengembalian (Jika Sudah Dikembalikan) --}}
                    @if(in_array($toolLoan->status, ['returned', 'lost']))
                    <div style="margin-top:12px;padding-top:10px;border-top:1px dashed #cbd5e1;">
                        <div class="fw-700 mb-2" style="font-size:12px;color:#047857;display:flex;align-items:center;gap:6px;">
                            <i class="fas fa-check-circle"></i> Hasil Rekapitulasi Pengembalian
                        </div>
                        <div class="info-row">
                            <span class="info-label">Waktu Kembali</span>
                            <span class="info-val">{{ \Carbon\Carbon::parse($toolLoan->returned_at)->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Kondisi Fisik Alat</span>
                            <div class="info-val" style="display:flex;align-items:center;gap:5px;flex-wrap:wrap;justify-content:flex-end;">
                                <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:11px;padding:3px 8px;border-radius:6px;font-weight:700;">
                                    <i class="fas fa-circle-check me-1"></i> {{ $toolLoan->returned_good }} Baik
                                </span>
                                @if($toolLoan->returned_damaged > 0)
                                <span class="badge" style="background:#fff7ed;color:#ea580c;border:1px solid #fed7aa;font-size:11px;padding:3px 8px;border-radius:6px;font-weight:700;">
                                    <i class="fas fa-triangle-exclamation me-1"></i> {{ $toolLoan->returned_damaged }} Rusak
                                </span>
                                @endif
                                @if($toolLoan->returned_lost > 0)
                                <span class="badge" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca;font-size:11px;padding:3px 8px;border-radius:6px;font-weight:700;">
                                    <i class="fas fa-circle-xmark me-1"></i> {{ $toolLoan->returned_lost }} Hilang
                                </span>
                                @endif
                            </div>
                        </div>
                        @if($toolLoan->return_notes)
                        <div class="info-row">
                            <span class="info-label">Catatan Pengembalian</span>
                            <span class="info-val" style="font-weight:500;">{{ $toolLoan->return_notes }}</span>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            {{-- Tool Items Card (Tabel Daftar Alat yang Dipinjam) --}}
            <div class="card" style="border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
                <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:10px 14px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:6px;">
                    <div style="display:flex;align-items:center;gap:6px;">
                        <i class="fas fa-screwdriver-wrench text-primary" style="font-size:13px;"></i>
                        <span class="card-title" style="font-size:13px;font-weight:700;color:#1e293b;">Daftar Alat yang Dipinjam</span>
                    </div>
                    <span class="badge" style="background:#eff6ff;color:#2563eb;font-weight:700;font-size:10.5px;padding:2px 8px;border-radius:12px;border:1px solid #bfdbfe;">
                        {{ $toolLoan->items->count() }} Jenis ({{ $toolLoan->items->sum('quantity') }} Unit)
                    </span>
                </div>
                <div class="table-wrap" style="overflow-x:auto;">
                    <table class="data-table mb-0">
                        <thead>
                            <tr style="background:#fafafa;border-bottom:1px solid #e2e8f0;font-size:10.5px;text-transform:uppercase;letter-spacing:0.3px;color:#64748b;">
                                <th style="width:36px;padding:8px 10px;text-align:center;">#</th>
                                <th style="padding:8px 12px;">Nama Alat</th>
                                <th style="padding:8px 10px;">Kategori</th>
                                <th style="text-align:center;width:100px;padding:8px 10px;">Jumlah Pinjam</th>
                                @if(in_array($toolLoan->status, ['returned', 'lost']))
                                <th style="padding:8px 10px;min-width:180px;">Kondisi Saat Kembali</th>
                                <th style="padding:8px 10px;min-width:160px;">Catatan Kerusakan/Hilang</th>
                                @endif
                                <th style="padding:8px 10px;width:110px;">Kode Ref</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($toolLoan->items as $idx => $item)
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td style="padding:8px 10px;text-align:center;color:#94a3b8;font-size:11.5px;">{{ $idx + 1 }}</td>
                                <td style="padding:8px 12px;">
                                    <div class="fw-700" style="color:#0f172a;font-size:12.5px;">{{ $item->tool?->name ?? 'Alat tidak ditemukan' }}</div>
                                    @if($item->tool?->code)
                                    <code style="font-size:10px;background:#f1f5f9;padding:1px 5px;border-radius:4px;color:#475569;margin-top:2px;display:inline-block;">{{ $item->tool->code }}</code>
                                    @endif
                                </td>
                                <td style="padding:8px 10px;font-size:11.5px;color:#475569;">
                                    {{ $item->tool?->category?->name ?? '-' }}
                                </td>
                                <td style="padding:8px 10px;text-align:center;">
                                    <span class="badge" style="background:#f5f3ff;color:#6d28d9;border:1px solid #ddd6fe;font-size:11px;font-weight:700;padding:3px 8px;border-radius:4px;">
                                        {{ $item->quantity }} Unit
                                    </span>
                                </td>

                                {{-- Rincian Hasil Pengembalian per Baris Alat --}}
                                @if(in_array($toolLoan->status, ['returned', 'lost']))
                                <td style="padding:8px 10px;">
                                    <div style="display:flex;align-items:center;gap:4px;flex-wrap:wrap;">
                                        @if($item->returned_good > 0)
                                        <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:10.5px;padding:2px 6px;border-radius:4px;font-weight:700;">
                                            <i class="fas fa-check me-1"></i> {{ $item->returned_good }} Baik
                                        </span>
                                        @endif
                                        @if($item->returned_damaged > 0)
                                        <span class="badge" style="background:#fff7ed;color:#ea580c;border:1px solid #fed7aa;font-size:10.5px;padding:2px 6px;border-radius:4px;font-weight:700;">
                                            <i class="fas fa-triangle-exclamation me-1"></i> {{ $item->returned_damaged }} Rusak
                                        </span>
                                        @endif
                                        @if($item->returned_lost > 0)
                                        <span class="badge" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca;font-size:10.5px;padding:2px 6px;border-radius:4px;font-weight:700;">
                                            <i class="fas fa-circle-xmark me-1"></i> {{ $item->returned_lost }} Hilang
                                        </span>
                                        @endif
                                    </div>
                                </td>
                                <td style="padding:8px 10px;">
                                    @if($item->return_notes)
                                        <span style="font-size:11.5px;color:#1e293b;font-weight:500;">{{ $item->return_notes }}</span>
                                    @elseif($item->returned_damaged > 0 || $item->returned_lost > 0)
                                        <span class="text-muted" style="font-size:11px;font-style:italic;">Tanpa catatan</span>
                                    @else
                                        <span class="text-muted" style="font-size:11px;">Kondisi prima siap pakai</span>
                                    @endif
                                </td>
                                @endif

                                <td style="padding:8px 10px;font-size:11px;color:#64748b;">
                                    {{ $item->assignment_number }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ in_array($toolLoan->status, ['returned', 'lost']) ? 7 : 5 }}" class="text-center text-muted p-3" style="font-size:12px;">
                                    Tidak ada daftar alat dalam transaksi ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- Right Panel: Action / Approval / Return / Cancellation Cards --}}
        <div style="display:flex;flex-direction:column;gap:14px;">

            {{-- 1. Pending Approval by Central Admin --}}
            @if($toolLoan->status === 'pending')
                @can('approve tool assignments')
                <div class="card" style="border:1px solid #fde68a;border-radius:10px;background:#fffbeb;box-shadow:0 2px 6px -1px rgba(245,158,11,0.06);overflow:hidden;">
                    <div class="card-header" style="background:#fef3c7;border-bottom:1px solid #fde68a;padding:10px 14px;display:flex;align-items:center;gap:6px;">
                        <i class="fas fa-shield-halved text-warning" style="font-size:13px;"></i>
                        <span class="card-title" style="font-size:12.5px;font-weight:700;color:#92400e;">Persetujuan Admin Pusat</span>
                    </div>
                    <div class="card-body" style="padding:12px 14px;">
                        <p class="text-muted mb-2" style="font-size:11.5px;line-height:1.4;color:#78350f;">
                            Pengajuan peminjaman alat memerlukan persetujuan Admin Pusat sebelum stok alat resmi dikurangi.
                        </p>
                        <form method="POST" action="{{ route('tool-assignments.approve', $toolLoan->id) }}" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-success w-full" style="justify-content:center;padding:8px;font-size:12px;font-weight:600;border-radius:6px;" onclick="return confirm('Setujui pengajuan peminjaman #{{ $toolLoan->loan_number }} ini? Stok seluruh alat akan otomatis dikurangi.')">
                                <i class="fas fa-check-circle me-1"></i> Setujui Peminjaman
                            </button>
                        </form>
                        <form method="POST" action="{{ route('tool-assignments.reject', $toolLoan->id) }}">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label" style="font-size:11px;font-weight:600;color:#78350f;">Alasan Penolakan <span class="text-danger">*</span></label>
                                <input type="text" name="rejection_reason" class="form-control" placeholder="Alasan penolakan..." required style="font-size:11.5px;height:36px;padding:6px 10px;border-radius:5px;border-color:#fcd34d;">
                            </div>
                            <button type="submit" class="btn btn-outline-danger w-full" style="justify-content:center;padding:6px;font-size:11.5px;font-weight:600;border-radius:6px;" onclick="return confirm('Tolak pengajuan peminjaman ini?')">
                                <i class="fas fa-xmark me-1"></i> Tolak Pengajuan
                            </button>
                        </form>
                    </div>
                </div>
                @endcan
            @elseif($toolLoan->status === 'rejected')
                <div class="card" style="border:1px solid #fecaca;border-radius:10px;background:#fef2f2;overflow:hidden;">
                    <div class="card-header" style="background:#fee2e2;border-bottom:1px solid #fecaca;padding:10px 14px;">
                        <span class="card-title" style="font-size:12px;font-weight:700;color:#991b1b;"><i class="fas fa-circle-xmark me-1"></i> Pengajuan Ditolak</span>
                    </div>
                    <div class="card-body" style="padding:10px 14px;">
                        <div style="font-size:11.5px;color:#7f1d1d;">
                            <strong>Alasan:</strong> {{ $toolLoan->rejection_reason ?? '-' }}
                        </div>
                    </div>
                </div>
            @elseif($toolLoan->approvedBy)
                <div class="card" style="border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
                    <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:10px 14px;display:flex;align-items:center;gap:6px;">
                        <i class="fas fa-check-circle text-success" style="font-size:12px;"></i>
                        <span class="card-title" style="font-size:12px;font-weight:700;color:#1e293b;">Disetujui Oleh</span>
                    </div>
                    <div class="card-body" style="padding:10px 14px;">
                        <div class="info-row" style="padding:4px 0;">
                            <span class="info-label" style="width:90px;">Nama</span>
                            <span class="info-val">{{ $toolLoan->approvedBy?->name ?? '-' }}</span>
                        </div>
                        <div class="info-row" style="padding:4px 0;">
                            <span class="info-label" style="width:90px;">Tanggal</span>
                            <span class="info-val">{{ $toolLoan->approved_at ? \Carbon\Carbon::parse($toolLoan->approved_at)->format('d/m/Y H:i') : '-' }}</span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- 2. Return Process Card (Active or Overdue) --}}
            @if(in_array($toolLoan->status, ['active', 'overdue']))
                @can('return tool assignments')
                <div class="card" style="border:1px solid #cbd5e1;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
                    <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:10px 14px;display:flex;align-items:center;gap:6px;">
                        <i class="fas fa-rotate-left text-primary" style="font-size:12px;"></i>
                        <span class="card-title" style="font-size:12.5px;font-weight:700;color:#0f172a;">Proses Pengembalian</span>
                    </div>
                    <div class="card-body" style="padding:12px 14px;">
                        <form method="POST" action="{{ route('tool-assignments.return', $toolLoan->id) }}" id="toolReturnForm">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.02em;">
                                    Waktu Kembali <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" name="returned_at" class="form-control" value="{{ date('Y-m-d\TH:i') }}" style="height:36px;padding:6px 10px;border-radius:6px;font-size:11.5px;font-weight:600;" required>
                            </div>

                            {{-- Input Rincian Kondisi Alat (Baik, Rusak, Hilang) per Item --}}
                            <div class="mb-3">
                                <label class="form-label" style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.02em;display:flex;justify-content:space-between;align-items:center;">
                                    <span>Kondisi Pengembalian Alat <span class="text-danger">*</span></span>
                                </label>

                                @foreach($toolLoan->items as $item)
                                <div class="return-item-card" data-item-id="{{ $item->id }}" data-max-qty="{{ $item->quantity }}">
                                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                                        <div style="font-size:11.5px;font-weight:700;color:#0f172a;">
                                            {{ $item->tool?->name ?? 'Alat' }}
                                        </div>
                                        <span class="badge" style="background:#f5f3ff;color:#6d28d9;border:1px solid #ddd6fe;font-size:10px;padding:2px 6px;border-radius:4px;font-weight:700;">
                                            Pinjam: {{ $item->quantity }} Unit
                                        </span>
                                    </div>

                                    {{-- 3 Kolom Input Kuantitas --}}
                                    <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:6px;margin-bottom:6px;">
                                        <div>
                                            <label style="font-size:10px;font-weight:700;color:#047857;display:block;margin-bottom:2px;">
                                                <i class="fas fa-check-circle"></i> Baik
                                            </label>
                                            <input type="number" 
                                                   name="returns[{{ $item->id }}][returned_good]" 
                                                   id="good_{{ $item->id }}"
                                                   min="0" 
                                                   max="{{ $item->quantity }}" 
                                                   value="{{ $item->quantity }}" 
                                                   class="form-control text-center" 
                                                   style="height:32px;padding:4px;font-size:12px;font-weight:700;border-color:#a7f3d0;" 
                                                   oninput="checkItemReturnTotals({{ $item->id }}, {{ $item->quantity }})"
                                                   required>
                                        </div>
                                        <div>
                                            <label style="font-size:10px;font-weight:700;color:#ea580c;display:block;margin-bottom:2px;">
                                                <i class="fas fa-triangle-exclamation"></i> Rusak
                                            </label>
                                            <input type="number" 
                                                   name="returns[{{ $item->id }}][returned_damaged]" 
                                                   id="damaged_{{ $item->id }}"
                                                   min="0" 
                                                   max="{{ $item->quantity }}" 
                                                   value="0" 
                                                   class="form-control text-center" 
                                                   style="height:32px;padding:4px;font-size:12px;font-weight:700;border-color:#fed7aa;" 
                                                   oninput="checkItemReturnTotals({{ $item->id }}, {{ $item->quantity }})"
                                                   required>
                                        </div>
                                        <div>
                                            <label style="font-size:10px;font-weight:700;color:#dc2626;display:block;margin-bottom:2px;">
                                                <i class="fas fa-circle-xmark"></i> Hilang
                                            </label>
                                            <input type="number" 
                                                   name="returns[{{ $item->id }}][returned_lost]" 
                                                   id="lost_{{ $item->id }}"
                                                   min="0" 
                                                   max="{{ $item->quantity }}" 
                                                   value="0" 
                                                   class="form-control text-center" 
                                                   style="height:32px;padding:4px;font-size:12px;font-weight:700;border-color:#fecaca;" 
                                                   oninput="checkItemReturnTotals({{ $item->id }}, {{ $item->quantity }})"
                                                   required>
                                        </div>
                                    </div>

                                    {{-- Status Penghitungan Otomatis Realtime --}}
                                    <div id="check_status_{{ $item->id }}" style="font-size:10.5px;font-weight:700;display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;color:#047857;">
                                        <span>Total: <span id="sum_count_{{ $item->id }}">{{ $item->quantity }}</span> / {{ $item->quantity }} Unit</span>
                                        <span id="sum_badge_{{ $item->id }}" class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:9.5px;padding:1px 6px;">Pas ✓</span>
                                    </div>

                                    {{-- Catatan Khusus Kerusakan / Hilang pada Item ini --}}
                                    <input type="text" 
                                           name="returns[{{ $item->id }}][notes]" 
                                           class="form-control" 
                                           placeholder="Keterangan kerusakan / hilang jika ada..." 
                                           style="height:28px;padding:4px 8px;font-size:11px;border-radius:4px;border-color:#cbd5e1;">
                                </div>
                                @endforeach
                            </div>

                            <div class="mb-3">
                                <label class="form-label" style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.02em;">Catatan Umum</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Catatan umum kondisi pengembalian..." style="padding:8px 10px;border-radius:6px;font-size:11.5px;"></textarea>
                            </div>

                            <button type="submit" id="btnSubmitReturn" class="btn btn-success w-full" style="justify-content:center;padding:9px;font-size:12.5px;font-weight:700;border-radius:6px;" onclick="return confirm('Konfirmasi pengembalian seluruh alat? Stok fisik gudang akan otomatis diperbarui.')">
                                <i class="fas fa-check-circle me-1"></i> Konfirmasi Pengembalian
                            </button>
                        </form>
                    </div>
                </div>
                @endcan
            @endif

            {{-- 3. Cancellation Card --}}
            @if(in_array($toolLoan->status, ['pending', 'active']))
                @can('cancel tool assignments')
                <div class="card" style="border:1px solid #fecaca;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(220,38,38,0.04);overflow:hidden;">
                    <div class="card-header" style="background:#fef2f2;border-bottom:1px solid #fecaca;padding:10px 14px;display:flex;align-items:center;gap:6px;">
                        <i class="fas fa-ban" style="color:#dc2626;font-size:12px;"></i>
                        <span class="card-title" style="font-size:12px;font-weight:700;color:#991b1b;">Batalkan Peminjaman</span>
                    </div>
                    <div class="card-body" style="padding:12px 14px;">
                        <p class="text-muted mb-2" style="font-size:11px;line-height:1.4;">
                            @if($toolLoan->status === 'active')
                                Membatalkan peminjaman akan mengembalikan stok alat ke gudang asal.
                            @else
                                Membatalkan pengajuan ini tidak akan memotong stok.
                            @endif
                        </p>
                        <form method="POST" action="{{ route('tool-assignments.cancel', $toolLoan->id) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" style="font-size:11px;font-weight:700;color:#475569;">Alasan Pembatalan <span class="text-danger">*</span></label>
                                <textarea name="cancellation_reason" class="form-control" rows="2" required placeholder="Alasan..." style="padding:8px 10px;border-radius:5px;font-size:11.5px;border-color:#fca5a5;"></textarea>
                            </div>
                            <button type="submit" class="btn btn-outline-danger w-full" style="justify-content:center;padding:6px;font-size:12px;font-weight:600;border-radius:6px;" onclick="return confirm('Yakin ingin MEMBATALKAN peminjaman ini?')">
                                <i class="fas fa-ban me-1"></i> Batalkan Transaksi
                            </button>
                        </form>
                    </div>
                </div>
                @endcan
            @endif

        </div>
    </div>

    @push('scripts')
    <script>
        /**
         * Validasi kuantitas pengembalian secara realtime per item
         */
        function checkItemReturnTotals(itemId, maxQty) {
            const goodInput = document.getElementById('good_' + itemId);
            const damagedInput = document.getElementById('damaged_' + itemId);
            const lostInput = document.getElementById('lost_' + itemId);
            const statusWrap = document.getElementById('check_status_' + itemId);
            const sumCount = document.getElementById('sum_count_' + itemId);
            const sumBadge = document.getElementById('sum_badge_' + itemId);
            const submitBtn = document.getElementById('btnSubmitReturn');

            const good = parseInt(goodInput.value) || 0;
            const damaged = parseInt(damagedInput.value) || 0;
            const lost = parseInt(lostInput.value) || 0;
            const total = good + damaged + lost;

            if (sumCount) sumCount.innerText = total;

            if (total === maxQty) {
                if (statusWrap) statusWrap.style.color = '#047857';
                if (sumBadge) {
                    sumBadge.style.background = '#ecfdf5';
                    sumBadge.style.color = '#047857';
                    sumBadge.style.borderColor = '#a7f3d0';
                    sumBadge.innerText = 'Pas ✓';
                }
            } else {
                if (statusWrap) statusWrap.style.color = '#dc2626';
                if (sumBadge) {
                    sumBadge.style.background = '#fef2f2';
                    sumBadge.style.color = '#dc2626';
                    sumBadge.style.borderColor = '#fecaca';
                    sumBadge.innerText = (total < maxQty) ? `Kurang ${maxQty - total} Unit` : `Lebih ${total - maxQty} Unit`;
                }
            }
        }

        document.getElementById('toolReturnForm')?.addEventListener('submit', function(e) {
            const items = document.querySelectorAll('.return-item-card');
            for (const item of items) {
                const itemId = item.dataset.itemId;
                const maxQty = parseInt(item.dataset.maxQty) || 0;
                const good = parseInt(document.getElementById('good_' + itemId)?.value) || 0;
                const damaged = parseInt(document.getElementById('damaged_' + itemId)?.value) || 0;
                const lost = parseInt(document.getElementById('lost_' + itemId)?.value) || 0;
                const total = good + damaged + lost;
                
                if (total !== maxQty) {
                    e.preventDefault();
                    alert(`Total pengembalian alat (Baik + Rusak + Hilang) berjumlah ${total} unit, seharusnya pas ${maxQty} unit. Mohon periksa kembali.`);
                    return false;
                }
            }
        });
    </script>
    @endpush
</x-app-layout>
