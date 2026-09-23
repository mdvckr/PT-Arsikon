<x-app-layout>
    <x-slot name="title">Pemakaian Material Lapangan</x-slot>

    @push('styles')
    <style>
        .filter-form-grid {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .filter-form-grid .filter-col {
            flex: 1;
            min-width: 160px;
        }

        .filter-form-grid .form-control {
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

        .filter-form-grid .form-control:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 2px rgba(37,99,235,0.15) !important;
            outline: none !important;
        }

        .filter-form-grid .btn {
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

        .usage-table th {
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

        .usage-table td {
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
            .filter-form-grid .filter-col {
                min-width: 100%;
            }
            .filter-actions-flex {
                width: 100%;
            }
            .filter-actions-flex button,
            .filter-actions-flex a {
                flex: 1;
                justify-content: center;
            }
        }
    </style>
    @endpush

    <div class="flex items-center justify-between mb-3 page-header-flex">
        <div>
            <h2 class="fw-700" style="font-size:16px;color:#0f172a;margin:0;">Pemakaian Material Lapangan</h2>
            <p class="text-muted" style="font-size:12px;margin-top:2px;">Catatan pengeluaran barang & material ke mandor / pekerja proyek</p>
        </div>
        @can('create material usages')
        <a href="{{ route('material-usages.create') }}" class="btn btn-primary btn-sm" style="border-radius:6px;padding:6px 12px;font-size:12px;font-weight:600;box-shadow:0 2px 4px rgba(37,99,235,0.2);">
            <i class="fas fa-plus me-1"></i> Catat Pengeluaran
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

    <!-- Filter Card -->
    <div class="card mb-3" style="border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
        <div class="card-body" style="padding:12px 16px;">
            <form method="GET" action="{{ route('material-usages.index') }}" class="filter-form-grid">
                <div class="filter-col" style="flex: 1.5; min-width: 200px;">
                    <label class="form-label" style="font-size:11px;font-weight:600;color:#475569;margin-bottom:3px;">Pencarian</label>
                    <div style="position:relative;">
                        <i class="fas fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);font-size:11px;color:#94a3b8;pointer-events:none;"></i>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="No. bukti, penerima, pekerjaan..." style="padding-left:28px !important;">
                    </div>
                </div>
                <div class="filter-col">
                    <label class="form-label" style="font-size:11px;font-weight:600;color:#475569;margin-bottom:3px;">Gudang / Site</label>
                    <select name="warehouse_id" class="form-control">
                        <option value="">Semua Gudang</option>
                        @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>
                            {{ $wh->name }} {{ $wh->is_central ? '(Pusat)' : '' }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-col" style="max-width:140px;">
                    <label class="form-label" style="font-size:11px;font-weight:600;color:#475569;margin-bottom:3px;">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control">
                </div>
                <div class="filter-col" style="max-width:140px;">
                    <label class="form-label" style="font-size:11px;font-weight:600;color:#475569;margin-bottom:3px;">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control">
                </div>
                <div class="flex gap-1 filter-actions-flex" style="align-items:center;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    @if(request()->anyFilled(['search', 'warehouse_id', 'start_date', 'end_date']))
                    <a href="{{ route('material-usages.index') }}" class="btn btn-light border" style="width:36px;padding:0;color:#64748b;" title="Reset Filter">
                        <i class="fas fa-rotate-left" style="font-size:11px;"></i>
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="card" style="border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
        <div class="table-wrap" style="overflow-x:auto;-webkit-overflow-scrolling:touch;">
            <table class="data-table usage-table mb-0">
                <thead>
                    <tr>
                        <th class="nowrap" style="width:165px;">No. Bon</th>
                        <th class="nowrap" style="width:95px;">Tanggal</th>
                        <th class="nowrap" style="width:160px;">Gudang</th>
                        <th style="min-width:140px;">Penerima</th>
                        <th style="min-width:130px;">Pekerjaan / Zona</th>
                        <th class="nowrap text-center" style="width:90px;">Material</th>
                        <th class="nowrap text-center" style="width:95px;">Status</th>
                        <th class="nowrap" style="width:140px;">Petugas</th>
                        <th class="nowrap text-center" style="width:75px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usages as $usage)
                    <tr style="border-bottom:1px solid #f1f5f9;{{ $usage->status === 'cancelled' ? 'opacity:0.6;' : '' }}">
                        <td class="nowrap">
                            <a href="{{ route('material-usages.show', $usage) }}" class="text-primary fw-700" style="font-size:12px;text-decoration:none;">
                                {{ $usage->usage_number }}
                            </a>
                            @if($usage->materialRequest)
                            <div class="text-muted" style="font-size:10.5px;margin-top:2px;">
                                MR: <a href="{{ route('material-requests.show', $usage->materialRequest) }}" style="color:#64748b;text-decoration:underline;">#{{ $usage->materialRequest->request_number }}</a>
                            </div>
                            @endif
                        </td>
                        <td class="nowrap" style="font-size:12px;color:#334155;">
                            {{ \Carbon\Carbon::parse($usage->usage_date)->format('d/m/Y') }}
                        </td>
                        <td class="nowrap">
                            <span class="badge" style="font-size:11px;font-weight:600;padding:3px 8px;border-radius:4px;white-space:nowrap;{{ $usage->warehouse?->is_central ? 'background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;' : 'background:#f8fafc;color:#475569;border:1px solid #e2e8f0;' }}">
                                <i class="fas fa-warehouse me-1" style="font-size:9.5px;opacity:0.75;"></i>{{ $usage->warehouse?->name ?? '-' }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-600" style="color:#0f172a;font-size:12px;">{{ $usage->recipient_name }}</div>
                        </td>
                        <td>
                            <span style="font-size:11.5px;color:#475569;">{{ $usage->job_section ?: '-' }}</span>
                        </td>
                        <td class="nowrap text-center">
                            <span class="badge" style="background:#f5f3ff;color:#6d28d9;border:1px solid #ddd6fe;font-size:11px;font-weight:600;padding:3px 8px;border-radius:12px;white-space:nowrap;">
                                {{ $usage->items->count() }} Jenis
                            </span>
                        </td>
                        <td class="nowrap text-center">
                            @if($usage->status === 'cancelled')
                                <span class="badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-size:11px;padding:3px 8px;border-radius:12px;font-weight:600;white-space:nowrap;">
                                    <i class="fas fa-circle-xmark me-1" style="font-size:9.5px;"></i> Dibatalkan
                                </span>
                            @else
                                <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:11px;padding:3px 8px;border-radius:12px;font-weight:600;white-space:nowrap;">
                                    <i class="fas fa-circle-check me-1" style="font-size:9.5px;"></i> Selesai
                                </span>
                            @endif
                        </td>
                        <td class="nowrap">
                            <span style="font-size:11.5px;color:#64748b;">{{ $usage->issuedBy?->name ?? '-' }}</span>
                        </td>
                        <td class="nowrap text-center">
                            <div class="flex justify-center gap-1" style="align-items:center;">
                                <a href="{{ route('material-usages.show', $usage) }}" class="btn btn-sm btn-light border" title="Lihat Detail" style="width:28px;height:28px;padding:0;border-radius:5px;display:inline-flex;align-items:center;justify-content:center;color:#2563eb;background:#f8fafc;">
                                    <i class="fas fa-eye" style="font-size:11px;"></i>
                                </a>
                                @if($usage->status !== 'cancelled')
                                <a href="{{ route('material-usages.print', $usage) }}" target="_blank" class="btn btn-sm btn-light border" title="Cetak Bon" style="width:28px;height:28px;padding:0;border-radius:5px;display:inline-flex;align-items:center;justify-content:center;color:#64748b;background:#f8fafc;">
                                    <i class="fas fa-print" style="font-size:11px;"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="text-align:center;padding:32px 16px;color:#94a3b8;">
                            <i class="fas fa-inbox" style="font-size:26px;color:#cbd5e1;margin-bottom:6px;display:block;"></i>
                            <div style="font-size:13px;font-weight:600;color:#64748b;">Belum ada riwayat pengeluaran material</div>
                            <div style="font-size:11.5px;color:#94a3b8;margin-top:2px;">Gunakan tombol 'Catat Pengeluaran' untuk memulai pencatatan baru.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($usages->hasPages())
        <div style="padding:8px 16px;border-top:1px solid #f1f5f9;">
            {{ $usages->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
