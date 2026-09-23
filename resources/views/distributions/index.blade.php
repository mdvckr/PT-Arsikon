<x-app-layout>
    <x-slot name="title">Data Distribusi Surat Jalan</x-slot>

    @push('styles')
    <style>
        .dist-filter-grid {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .dist-filter-grid .filter-col {
            flex: 1;
            min-width: 180px;
        }

        .dist-filter-grid .form-control {
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

        .dist-filter-grid .form-control:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 2px rgba(37,99,235,0.15) !important;
            outline: none !important;
        }

        .dist-filter-grid .btn {
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

        .dist-table th {
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

        .dist-table td {
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
            .dist-filter-grid .filter-col {
                min-width: 100%;
            }
        }
    </style>
    @endpush

    <div class="flex items-center justify-between mb-3 page-header-flex">
        <div>
            <h2 class="fw-700" style="font-size:16px;color:#0f172a;margin:0;">Distribusi & Surat Jalan</h2>
            <p class="text-muted" style="font-size:12px;margin-top:2px;">Kelola pengiriman barang & alat antar gudang (Pusat → Proyek)</p>
        </div>
        @can('create distributions')
        <a href="{{ route('distributions.create') }}" class="btn btn-primary btn-sm" style="border-radius:6px;padding:6px 12px;font-size:12px;font-weight:600;box-shadow:0 2px 4px rgba(37,99,235,0.2);">
            <i class="fas fa-plus me-1"></i> Buat Surat Jalan
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
            <form method="GET" class="dist-filter-grid">
                <div class="filter-col" style="flex: 2; min-width: 220px;">
                    <label class="form-label" style="font-size:11px;font-weight:600;color:#475569;margin-bottom:3px;">Pencarian</label>
                    <div style="position:relative;">
                        <i class="fas fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);font-size:11px;color:#94a3b8;pointer-events:none;"></i>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="No. surat jalan, gudang..." style="padding-left:28px !important;">
                    </div>
                </div>
                <div class="filter-col" style="max-width:180px;">
                    <label class="form-label" style="font-size:11px;font-weight:600;color:#475569;margin-bottom:3px;">Status</label>
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>Dalam Pengiriman</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai / Diterima</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    @if(request()->anyFilled(['search', 'status']))
                    <a href="{{ route('distributions.index') }}" class="btn btn-light border" style="width:36px;padding:0;color:#64748b;" title="Reset Filter">
                        <i class="fas fa-rotate-left" style="font-size:11px;"></i>
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card" style="border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
        <div class="table-wrap" style="overflow-x:auto;-webkit-overflow-scrolling:touch;">
            <table class="data-table dist-table mb-0">
                <thead>
                    <tr>
                        <th class="nowrap" style="width:160px;">No. Surat Jalan</th>
                        <th style="min-width:120px;">Dari Gudang</th>
                        <th style="min-width:120px;">Tujuan</th>
                        <th class="nowrap" style="width:95px;">Tgl Kirim</th>
                        <th style="min-width:130px;">Referensi MR</th>
                        <th class="nowrap text-center" style="width:125px;">Status</th>
                        <th class="nowrap text-center" style="width:75px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($distributions as $dist)
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:8px 12px;">
                            <a href="{{ route('distributions.show', $dist) }}" class="text-primary fw-600" style="font-size:12px;">
                                {{ $dist->distribution_number }}
                            </a>
                            <div class="text-muted" style="font-size:10.5px;">Oleh: {{ $dist->creator?->name ?? '-' }}</div>
                        </td>
                        <td style="padding:8px 12px;">
                            <div style="font-size:12px;color:#0f172a;">{{ $dist->fromWarehouse?->name ?? '-' }}</div>
                        </td>
                        <td style="padding:8px 12px;">
                            <div class="fw-600" style="font-size:12px;color:#0f172a;">{{ $dist->toWarehouse?->name ?? '-' }}</div>
                        </td>
                        <td class="nowrap" style="font-size:12px;color:#334155;">
                            {{ $dist->delivery_date ? \Carbon\Carbon::parse($dist->delivery_date)->format('d/m/Y') : '-' }}
                        </td>
                        <td style="padding:8px 12px;">
                            @if($dist->materialRequest)
                            <a href="{{ route('material-requests.show', $dist->materialRequest) }}" class="text-primary" style="font-size:11.5px;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                                <i class="fas fa-link" style="font-size:9px;"></i>
                                {{ $dist->materialRequest->request_number }}
                            </a>
                            @else
                            <span class="text-muted" style="font-size:11.5px;">-</span>
                            @endif
                        </td>
                        <td class="nowrap text-center">
                            @if($dist->status === 'draft')
                                <span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-size:10.5px;padding:2px 7px;border-radius:12px;font-weight:600;white-space:nowrap;"><i class="fas fa-clock me-1" style="font-size:9px;"></i>Draft</span>
                            @elseif($dist->status === 'in_transit')
                                <span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-size:10.5px;padding:2px 7px;border-radius:12px;font-weight:600;white-space:nowrap;"><i class="fas fa-truck me-1" style="font-size:9px;"></i>Dalam Pengiriman</span>
                            @elseif($dist->status === 'completed')
                                <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:10.5px;padding:2px 7px;border-radius:12px;font-weight:600;white-space:nowrap;"><i class="fas fa-check-double me-1" style="font-size:9px;"></i>Selesai</span>
                            @elseif($dist->status === 'cancelled')
                                <span class="badge" style="background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;font-size:10.5px;padding:2px 7px;border-radius:12px;font-weight:600;white-space:nowrap;"><i class="fas fa-ban me-1" style="font-size:9px;"></i>Dibatalkan</span>
                            @else
                                <span class="badge" style="background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;font-size:10.5px;padding:2px 7px;border-radius:12px;font-weight:600;white-space:nowrap;">{{ $dist->status }}</span>
                            @endif
                        </td>
                        <td class="nowrap text-center">
                            <a href="{{ route('distributions.show', $dist) }}" class="btn btn-sm btn-light border" title="Lihat Detail" style="width:28px;height:28px;padding:0;border-radius:5px;display:inline-flex;align-items:center;justify-content:center;color:#2563eb;background:#f8fafc;">
                                <i class="fas fa-eye" style="font-size:11px;"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:32px 16px;color:#94a3b8;">
                            <i class="fas fa-truck" style="font-size:26px;color:#cbd5e1;margin-bottom:6px;display:block;"></i>
                            <div style="font-size:13px;font-weight:600;color:#64748b;">Belum ada data distribusi / surat jalan</div>
                            <div class="text-muted" style="font-size:11.5px;margin-top:2px;">Buat surat jalan pertama untuk mulai mengirim barang.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($distributions->hasPages())
        <div style="padding:8px 16px;border-top:1px solid #f1f5f9;">{{ $distributions->links() }}</div>
        @endif
    </div>
</x-app-layout>
