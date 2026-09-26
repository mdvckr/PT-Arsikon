<x-app-layout>
    <x-slot name="title">Manajemen Gudang & Lokasi</x-slot>

    <!-- Page Header -->
    <div class="warehouse-header-wrap mb-4">
        <div class="warehouse-header-title">
            <div class="flex items-center gap-2">
                <div class="icon-circle icon-circle-orange">
                    <i class="fas fa-warehouse"></i>
                </div>
                <div>
                    <h2 class="fw-800" style="font-size:22px;color:#0f172a;line-height:1.2;">Data Gudang & Lokasi</h2>
                    <p class="text-muted" style="font-size:13px;margin:2px 0 0 0;">Kelola master lokasi pergudangan logistik pusat dan site proyek konstruksi.</p>
                </div>
            </div>
        </div>
        @if(auth()->user()->can('warehouses.manage') || auth()->user()->hasAnyRole(['Owner', 'Admin Pusat', 'Admin']))
        <div class="warehouse-header-action">
            <a href="{{ route('warehouses.create') }}" class="btn btn-primary btn-add-wh">
                <i class="fas fa-plus-circle"></i> Tambah Gudang
            </a>
        </div>
        @endif
    </div>



    <!-- Filter & Search Bar -->
    <div class="card mb-4 wh-filter-card">
        <div class="card-body" style="padding:16px 20px;">
            <form method="GET" action="{{ route('warehouses.index') }}" class="wh-filter-form">
                <div class="wh-search-input-wrap">
                    <label class="form-label" style="font-size:12px;margin-bottom:4px;">Cari Gudang</label>
                    <div class="input-icon-wrap">
                        <i class="fas fa-magnifying-glass input-icon"></i>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control with-icon" placeholder="Nama, kode, atau alamat gudang...">
                    </div>
                </div>

                <div class="wh-select-wrap">
                    <label class="form-label" style="font-size:12px;margin-bottom:4px;">Tipe Gudang</label>
                    <div class="input-icon-wrap">
                        <i class="fas fa-layer-group input-icon"></i>
                        <select name="type" class="form-control with-icon" onchange="this.form.submit()">
                            <option value="">Semua Tipe</option>
                            <option value="main" {{ request('type') === 'main' || request('type') === 'central' ? 'selected' : '' }}>Gudang Pusat</option>
                            <option value="project" {{ request('type') === 'project' ? 'selected' : '' }}>Gudang Proyek</option>
                        </select>
                    </div>
                </div>

                <div class="wh-select-wrap">
                    <label class="form-label" style="font-size:12px;margin-bottom:4px;">Status Operasional</label>
                    <div class="input-icon-wrap">
                        <i class="fas fa-toggle-on input-icon"></i>
                        <select name="status" class="form-control with-icon" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="wh-btn-filter-wrap">
                    <button type="submit" class="btn btn-primary" title="Terapkan Filter" style="height:38px;">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    @if(request()->hasAny(['search', 'type', 'status']))
                    <a href="{{ route('warehouses.index') }}" class="btn btn-secondary" title="Reset Pencarian" style="height:38px;">
                        <i class="fas fa-rotate-left"></i> Reset
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="card wh-table-card shadow-sm">
        <div class="table-wrap">
            <table class="data-table wh-data-table">
                <thead>
                    <tr>
                        <th style="min-width:180px;">Nama & Kode Gudang</th>
                        <th style="min-width:130px;">Tipe</th>
                        <th style="min-width:160px;">Proyek Terkait</th>
                        <th style="min-width:180px;">Lokasi / Alamat</th>
                        <th style="min-width:110px;">Item / Inventori</th>
                        <th style="min-width:100px;">Status</th>
                        <th style="min-width:90px;text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($warehouses as $wh)
                    @php
                        $isCentral = $wh->is_central || in_array($wh->type, ['central', 'main', 'pusat']);
                    @endphp
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="wh-item-icon {{ $isCentral ? 'central' : 'project' }}">
                                    <i class="fas {{ $isCentral ? 'fa-building' : 'fa-helmet-safety' }}"></i>
                                </div>
                                <div>
                                    <div class="fw-700" style="color:#0f172a;font-size:13.5px;">{{ $wh->name }}</div>
                                    <div class="text-muted" style="font-size:11.5px;font-family:monospace;margin-top:1px;">
                                        <i class="fas fa-barcode" style="font-size:10px;"></i> {{ $wh->code ?: '-' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($isCentral)
                                <span class="badge badge-primary wh-badge-pill">
                                    <i class="fas fa-building"></i> Gudang Pusat
                                </span>
                            @else
                                <span class="badge badge-info wh-badge-pill">
                                    <i class="fas fa-person-digging"></i> Gudang Proyek
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($wh->project)
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-folder-open text-warning" style="font-size:13px;"></i>
                                    <span class="fw-600" style="color:#1e293b;font-size:13px;">{{ $wh->project->name }}</span>
                                </div>
                            @elseif($isCentral)
                                <span class="text-muted" style="font-size:12px;">
                                    <i class="fas fa-star text-warning" style="font-size:10px;"></i> Sentral Kantor Pusat
                                </span>
                            @else
                                <span class="text-muted" style="font-size:12px;">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-start gap-1 text-muted" style="font-size:12px;max-width:240px;line-height:1.4;">
                                <i class="fas fa-location-dot text-danger" style="margin-top:2px;font-size:11px;flex-shrink:0;"></i>
                                <span>{{ $wh->address ?: ($wh->location ?: 'Lokasi Sentral Jakarta') }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-secondary" style="font-weight:600;font-size:11.5px;">
                                <i class="fas fa-box" style="font-size:10px;"></i> {{ $wh->inventories_count ?? 0 }} Item
                            </span>
                        </td>
                        <td>
                            @if($wh->is_active)
                                <span class="badge badge-success">
                                    <i class="fas fa-circle-check" style="font-size:10px;"></i> Aktif
                                </span>
                            @else
                                <span class="badge badge-danger">
                                    <i class="fas fa-circle-xmark" style="font-size:10px;"></i> Nonaktif
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center justify-center gap-1">
                                @if(auth()->user()->can('warehouses.manage') || auth()->user()->hasAnyRole(['Owner', 'Admin Pusat', 'Admin']))
                                <a href="{{ route('warehouses.edit', $wh) }}" class="btn btn-sm btn-warning btn-icon" title="Edit Data Gudang">
                                    <i class="fas fa-pen-to-square"></i>
                                </a>
                                <form method="POST" action="{{ route('warehouses.destroy', $wh) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus gudang {{ addslashes($wh->name) }}?')">
                                    @csrf 
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger btn-icon" title="Hapus Gudang">
                                        <i class="fas fa-trash-can"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center p-5">
                            <div class="empty-state" style="padding:24px 10px;">
                                <div style="width:60px;height:60px;border-radius:50%;background:#f1f5f9;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px;color:#94a3b8;font-size:24px;">
                                    <i class="fas fa-warehouse"></i>
                                </div>
                                <h4 class="fw-700" style="color:#1e293b;font-size:15px;margin-bottom:4px;">Tidak Ada Data Gudang</h4>
                                <p class="text-muted" style="font-size:13px;max-width:360px;margin:0 auto 16px auto;">
                                    @if(request()->hasAny(['search', 'type', 'status']))
                                        Tidak ditemukan gudang dengan filter pencarian tersebut. Silakan reset filter.
                                    @else
                                        Belum ada lokasi gudang yang ditambahkan ke sistem.
                                    @endif
                                </p>
                                @if(request()->hasAny(['search', 'type', 'status']))
                                    <a href="{{ route('warehouses.index') }}" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-rotate-left"></i> Bersihkan Filter
                                    </a>
                                @elseif(auth()->user()->can('warehouses.manage') || auth()->user()->hasAnyRole(['Owner', 'Admin Pusat', 'Admin']))
                                    <a href="{{ route('warehouses.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> Tambah Gudang Pertama
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($warehouses->hasPages())
        <div class="card-footer wh-pagination-footer" style="padding:14px 20px;border-top:1px solid #f1f5f9;background:#ffffff;">
            {{ $warehouses->links() }}
        </div>
        @endif
    </div>

    @push('styles')
    <style>
        .warehouse-header-wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .icon-circle-orange {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: rgba(234, 88, 12, 0.12);
            color: #ea580c;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .btn-add-wh {
            padding: 9px 18px;
            font-size: 13.5px;
            font-weight: 700;
        }

        /* Filter Form */
        .wh-filter-form {
            display: flex;
            align-items: flex-end;
            gap: 12px;
            flex-wrap: wrap;
        }

        .wh-search-input-wrap {
            flex: 2;
            min-width: 240px;
        }

        .wh-select-wrap {
            flex: 1;
            min-width: 170px;
        }

        .wh-btn-filter-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        /* Input With Icon */
        .input-icon-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon-wrap .input-icon {
            position: absolute;
            left: 12px;
            color: #94a3b8;
            font-size: 13px;
            pointer-events: none;
        }

        .input-icon-wrap .form-control.with-icon {
            padding-left: 36px;
        }

        /* Warehouse Item Icon */
        .wh-item-icon {
            width: 36px;
            height: 36px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }
        .wh-item-icon.central {
            background: rgba(37, 99, 235, 0.1);
            color: #2563eb;
        }
        .wh-item-icon.project {
            background: rgba(8, 145, 178, 0.1);
            color: #0891b2;
        }

        .wh-badge-pill {
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 700;
        }

        .wh-data-table td {
            vertical-align: middle;
            padding: 14px 16px;
        }

        .wh-data-table th {
            padding: 12px 16px;
        }

        /* RESPONSIVE MOBILE STYLES */
        @media (max-width: 768px) {
            .warehouse-header-wrap {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .warehouse-header-action {
                width: 100%;
            }

            .btn-add-wh {
                width: 100%;
                justify-content: center;
            }


            .wh-filter-form {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }

            .wh-search-input-wrap,
            .wh-select-wrap {
                width: 100%;
                min-width: 0;
                flex: none;
            }

            .wh-btn-filter-wrap {
                width: 100%;
            }

            .wh-btn-filter-wrap .btn {
                flex: 1;
                justify-content: center;
            }
        }
    </style>
    @endpush
</x-app-layout>
