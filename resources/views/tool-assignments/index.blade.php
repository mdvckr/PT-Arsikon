<x-app-layout>
    <x-slot name="title">Peminjaman Alat</x-slot>

    @push('styles')
    <style>
        .tool-filter-grid {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .tool-filter-grid .filter-col {
            flex: 1;
            min-width: 180px;
        }

        .tool-filter-grid .form-control {
            height: 36px !important;
            padding: 6px 10px !important;
            font-size: 12px !important;
            line-height: 1.4 !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            box-sizing: border-box !important;
            background-color: #ffffff !important;
            color: #0f172a !important;
        }

        .tool-filter-grid .form-control:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 2px rgba(37,99,235,0.15) !important;
            outline: none !important;
        }

        .tool-filter-grid .btn {
            height: 36px !important;
            padding: 0 14px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 6px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            box-sizing: border-box !important;
        }

        .tool-table th {
            padding: 8px 12px !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.4px !important;
            color: #64748b !important;
            background: #f8fafc !important;
            border-bottom: 1px solid #e2e8f0 !important;
            white-space: nowrap !important;
        }

        .tool-table td {
            padding: 8px 12px !important;
            vertical-align: middle !important;
        }

        .nowrap {
            white-space: nowrap !important;
        }

        @media (max-width: 640px) {
            .page-header-flex {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 10px;
            }
            .page-header-flex a {
                width: 100%;
                justify-content: center;
            }
            .tool-filter-grid .filter-col {
                min-width: 100%;
            }
        }
    </style>
    @endpush

    <div class="flex items-center justify-between mb-3 page-header-flex">
        <div>
            <h2 class="fw-700" style="font-size:16px;color:#0f172a;margin:0;">Peminjaman Alat</h2>
            <p class="text-muted" style="font-size:12px;margin-top:2px;">Catatan distribusi peminjaman alat ke personal / mandor / proyek</p>
        </div>
        @can('create tool assignments')
        <a href="{{ route('tool-assignments.create') }}" class="btn btn-primary btn-sm" style="border-radius:6px;padding:6px 12px;font-size:12px;font-weight:600;box-shadow:0 2px 4px rgba(37,99,235,0.2);">
            <i class="fas fa-plus me-1"></i> Pinjamkan Alat
        </a>
        @endcan
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

    <div class="card mb-3" style="border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
        <div class="card-body" style="padding:12px 16px;">
            <form method="GET" class="tool-filter-grid">
                <div class="filter-col" style="flex: 2; min-width: 220px;">
                    <label class="form-label" style="font-size:11px;font-weight:600;color:#475569;margin-bottom:3px;">Pencarian</label>
                    <div style="position:relative;">
                        <i class="fas fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);font-size:11px;color:#94a3b8;pointer-events:none;"></i>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="No. pinjam, peminjam, nama alat..." style="padding-left:28px !important;">
                    </div>
                </div>
                <div class="filter-col" style="max-width:180px;">
                    <label class="form-label" style="font-size:11px;font-weight:600;color:#475569;margin-bottom:3px;">Status</label>
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                        <option value="active" {{ request('status') === 'active' || request('status') === 'assigned' ? 'selected' : '' }}>Dipinjam</option>
                        <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Dikembalikan</option>
                        <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Terlambat</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    @if(request()->anyFilled(['search', 'status']))
                    <a href="{{ route('tool-assignments.index') }}" class="btn btn-light border" style="width:36px;padding:0;color:#64748b;" title="Reset Filter">
                        <i class="fas fa-rotate-left" style="font-size:11px;"></i>
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card" style="border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
        <div class="table-wrap" style="overflow-x:auto;-webkit-overflow-scrolling:touch;">
            <table class="data-table tool-table mb-0">
                <thead>
                    <tr>
                        <th class="nowrap" style="width:160px;">No. Pinjam</th>
                        <th style="min-width:130px;">Peminjam</th>
                        <th style="min-width:140px;">Gudang / Lokasi</th>
                        <th style="min-width:160px;">Daftar Alat</th>
                        <th class="nowrap" style="width:95px;">Tgl Pinjam</th>
                        <th class="nowrap" style="width:95px;">Batas Kembali</th>
                        <th class="nowrap text-center" style="width:105px;">Status</th>
                        <th class="nowrap text-center" style="width:75px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($loans as $loan)
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:8px 12px;">
                            <a href="{{ route('tool-assignments.show', $loan->id) }}" class="text-primary fw-600" style="font-size:12px;">
                                {{ $loan->loan_number }}
                            </a>
                            <div class="text-muted" style="font-size:10.5px;">Oleh: {{ $loan->assignedBy?->name ?? '-' }}</div>
                        </td>
                        <td style="padding:8px 10px;">
                            <div class="fw-600" style="color:#0f172a;font-size:12px;">{{ $loan->borrower_name }}</div>
                            @if($loan->borrower_phone)
                            <div class="text-muted" style="font-size:10.5px;"><i class="fas fa-phone" style="font-size:8.5px;"></i> {{ $loan->borrower_phone }}</div>
                            @endif
                        </td>
                        <td style="padding:8px 10px;">
                            <div style="font-size:12px;">{{ $loan->location_name }}</div>
                            @if($loan->fromWarehouse)
                            <div class="text-muted" style="font-size:10.5px;">Asal: {{ $loan->fromWarehouse->name }}</div>
                            @endif
                        </td>
                        <td style="padding:8px 10px;">
                            @php
                                $itemCount = $loan->items->count();
                                $totalUnits = $loan->items->sum('quantity');
                            @endphp
                            <div class="fw-600" style="font-size:11.5px;color:#1e293b;">
                                <i class="fas fa-toolbox text-muted me-1"></i> {{ $itemCount }} Jenis ({{ $totalUnits }} Unit)
                            </div>
                            <div class="text-muted" style="font-size:10.5px;line-height:1.3;">
                                @foreach($loan->items->take(2) as $it)
                                    <span>• {{ $it->tool?->name ?? 'Alat' }} ({{ $it->quantity }}x)</span><br>
                                @endforeach
                                @if($itemCount > 2)
                                    <span style="color:#64748b;font-style:italic;">+ {{ $itemCount - 2 }} lainnya...</span>
                                @endif
                            </div>
                        </td>
                        <td class="nowrap" style="font-size:12px;color:#334155;">{{ \Carbon\Carbon::parse($loan->assigned_at)->format('d/m/Y') }}</td>
                        <td class="nowrap" style="font-size:12px;">
                            @if($loan->expected_return_at)
                                @php
                                    $expDate = \Carbon\Carbon::parse($loan->expected_return_at);
                                    $isOverdue = in_array($loan->status, ['active', 'overdue']) && $expDate->isPast();
                                @endphp
                                <span class="{{ $isOverdue ? 'text-danger fw-700' : '' }}">
                                    {{ $expDate->format('d/m/Y') }}
                                    @if($isOverdue) <i class="fas fa-exclamation-triangle" style="font-size:10px;"></i> @endif
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="nowrap text-center">
                            @if($loan->status === 'pending')
                                <span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-size:10.5px;padding:2px 7px;border-radius:12px;font-weight:600;white-space:nowrap;">Menunggu</span>
                            @elseif($loan->status === 'returned')
                                <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:10.5px;padding:2px 7px;border-radius:12px;font-weight:600;white-space:nowrap;">Dikembalikan</span>
                            @elseif($loan->status === 'overdue')
                                <span class="badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-size:10.5px;padding:2px 7px;border-radius:12px;font-weight:600;white-space:nowrap;">Terlambat</span>
                            @elseif($loan->status === 'rejected')
                                <span class="badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-size:10.5px;padding:2px 7px;border-radius:12px;font-weight:600;white-space:nowrap;">Ditolak</span>
                            @elseif($loan->status === 'cancelled')
                                <span class="badge" style="background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;font-size:10.5px;padding:2px 7px;border-radius:12px;font-weight:600;white-space:nowrap;">Dibatalkan</span>
                            @else
                                <span class="badge" style="background:#f5f3ff;color:#6d28d9;border:1px solid #ddd6fe;font-size:10.5px;padding:2px 7px;border-radius:12px;font-weight:600;white-space:nowrap;">Dipinjam</span>
                            @endif
                        </td>
                        <td class="nowrap text-center">
                            <a href="{{ route('tool-assignments.show', $loan->id) }}" class="btn btn-sm btn-light border" title="Lihat Detail" style="width:28px;height:28px;padding:0;border-radius:5px;display:inline-flex;align-items:center;justify-content:center;color:#2563eb;background:#f8fafc;">
                                <i class="fas fa-eye" style="font-size:11px;"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align:center;padding:32px 16px;color:#94a3b8;">
                            <i class="fas fa-inbox" style="font-size:26px;color:#cbd5e1;margin-bottom:6px;display:block;"></i>
                            <div style="font-size:13px;font-weight:600;color:#64748b;">Belum ada catatan peminjaman alat</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($loans->hasPages())
        <div style="padding:8px 16px;border-top:1px solid #f1f5f9;">{{ $loans->links() }}</div>
        @endif
    </div>
</x-app-layout>
