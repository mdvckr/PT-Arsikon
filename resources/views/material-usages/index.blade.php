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
            <x-lucide-plus class="me-1" /> Catat Pengeluaran
        </a>
        @endcan
    </div>

    @if (session('success'))
    <div class="alert alert-success mb-3" style="border-radius:6px;padding:8px 12px;font-size:12px;">
        <x-lucide-circle-check /> {{ session('success') }}
    </div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger mb-3" style="border-radius:6px;padding:8px 12px;font-size:12px;">
        <x-lucide-circle-alert /> {{ session('error') }}
    </div>
    @endif

    <!-- Filter Card -->
    <div class="card mb-3" style="border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
        <div class="card-body" style="padding:12px 16px;">
            <form method="GET" action="{{ route('material-usages.index') }}" class="filter-form-grid">
                <div class="filter-col" style="flex: 1.5; min-width: 200px;">
                    <label class="form-label" style="font-size:11px;font-weight:600;color:#475569;margin-bottom:3px;">Pencarian</label>
                    <div style="position:relative;">
                        <x-lucide-search style="position:absolute;left:10px;top:50%;transform:translateY(-50%);font-size:11px;color:#94a3b8;pointer-events:none;" />
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari no. pemakaian, pemakai, material..." style="padding-left:28px !important;">
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
            </form>
        </div>
    </div>

    <!-- Data Presentation: Grouped per 1 Minggu -> Hari -> Daftar Pemakaian -->
    @forelse($groupedUsages as $weekLabel => $dailyGroups)
        @php
            $weekUsagesCount = $dailyGroups->flatten(1)->count();
            $weekItemsCount = $dailyGroups->flatten(1)->sum(fn($u) => $u->items->count());
            $weekId = 'week-' . \Illuminate\Support\Str::slug($weekLabel);
            $isFirstWeek = $loop->first;
        @endphp
        <div class="card mb-3" style="border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
            {{-- Week Header Bar with Accordion toggle --}}
            <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;cursor:pointer;user-select:none;transition:background 0.15s ease;" onclick="toggleWeekAccordion('{{ $weekId }}')" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:32px;height:32px;border-radius:8px;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;border:1px solid #bfdbfe;">
                        <x-lucide-calendar-range style="width:16px;height:16px;" />
                    </div>
                    <div>
                        <div style="font-weight:700;font-size:13.5px;color:#0f172a;line-height:1.2;">
                            {{ $weekLabel }}
                        </div>
                        <div style="font-size:11px;color:#64748b;margin-top:2px;">
                            Rekap pengeluaran material mingguan terstruktur
                        </div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:10px;">
                    <span class="badge" style="background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;font-size:11px;padding:3px 8px;border-radius:6px;font-weight:600;">
                        {{ $weekUsagesCount }} Transaksi
                    </span>
                    <span class="badge" style="background:#f5f3ff;color:#6d28d9;border:1px solid #ddd6fe;font-size:11px;padding:3px 8px;border-radius:6px;font-weight:600;">
                        {{ $weekItemsCount }} Item Material
                    </span>
                    <div id="icon-{{ $weekId }}" style="color:#64748b;transition:transform 0.2s ease;{{ $isFirstWeek ? '' : 'transform:rotate(-90deg);' }}">
                        <x-lucide-chevron-down style="width:18px;height:18px;" />
                    </div>
                </div>
            </div>

            {{-- Week Body containing Days --}}
            <div id="body-{{ $weekId }}" style="{{ $isFirstWeek ? '' : 'display:none;' }}">
                @foreach($dailyGroups as $dayLabel => $dayUsages)
                    <div style="padding:14px 16px;border-bottom:1px solid #f1f5f9;background:#ffffff;">
                        {{-- Day Sub-header --}}
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;padding-bottom:6px;border-bottom:1px dashed #cbd5e1;">
                            <div style="display:flex;align-items:center;gap:6px;font-weight:700;font-size:12px;color:#1e293b;">
                                <x-lucide-calendar style="width:14px;height:14px;color:#ea580c;" />
                                <span>{{ $dayLabel }}</span>
                            </div>
                            <span style="font-size:11px;color:#64748b;font-weight:600;">
                                {{ $dayUsages->count() }} Transaksi
                            </span>
                        </div>

                        {{-- Day Table --}}
                        <div class="table-wrap" style="overflow-x:auto;-webkit-overflow-scrolling:touch;border:1px solid #e2e8f0;border-radius:8px;">
                            <table class="data-table usage-table mb-0" style="width:100%;border-collapse:collapse;">
                                <thead>
                                    <tr style="background:#f8fafc;border-bottom:1.5px solid #e2e8f0;">
                                        <th style="width:180px;text-align:left;white-space:nowrap;padding:9px 12px;font-size:10.5px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.04em;">No. Pemakaian</th>
                                        <th style="width:180px;text-align:left;white-space:nowrap;padding:9px 12px;font-size:10.5px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.04em;">Gudang</th>
                                        <th style="min-width:140px;text-align:left;padding:9px 12px;font-size:10.5px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.04em;">Pemakai</th>
                                        <th style="width:140px;text-align:center;white-space:nowrap;padding:9px 12px;font-size:10.5px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.04em;">Material</th>
                                        <th style="width:110px;text-align:center;white-space:nowrap;padding:9px 12px;font-size:10.5px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.04em;">Status</th>
                                        <th style="width:140px;text-align:left;white-space:nowrap;padding:9px 12px;font-size:10.5px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.04em;">Petugas</th>
                                        <th style="width:90px;text-align:center;white-space:nowrap;padding:9px 12px;font-size:10.5px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.04em;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dayUsages as $usage)
                                    @php $rowId = 'row-items-' . $usage->id; @endphp
                                    <tr style="border-bottom:1px solid #f1f5f9;{{ $usage->status === 'cancelled' ? 'opacity:0.6;' : '' }}">
                                        {{-- No. Pemakaian --}}
                                        <td class="nowrap" style="padding:10px 12px;vertical-align:middle;">
                                            <a href="{{ route('material-usages.show', $usage) }}" class="text-primary fw-700" style="font-size:12.5px;text-decoration:none;">
                                                {{ $usage->usage_number }}
                                            </a>
                                            @if($usage->materialRequest)
                                            <div class="text-muted" style="font-size:10.5px;margin-top:2px;">
                                                MR: <a href="{{ route('material-requests.show', $usage->materialRequest) }}" style="color:#64748b;text-decoration:underline;">#{{ $usage->materialRequest->request_number }}</a>
                                            </div>
                                            @endif
                                        </td>

                                        {{-- Gudang --}}
                                        <td class="nowrap" style="padding:10px 12px;vertical-align:middle;">
                                            <span class="badge" style="font-size:11px;font-weight:600;padding:3px 8px;border-radius:4px;white-space:nowrap;{{ $usage->warehouse?->is_central ? 'background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;' : 'background:#f8fafc;color:#475569;border:1px solid #e2e8f0;' }}">
                                                <x-lucide-warehouse class="me-1" style="width:11px;height:11px;display:inline-block;vertical-align:-1px;" />{{ $usage->warehouse?->name ?? '-' }}
                                            </span>
                                        </td>

                                        {{-- Pemakai (previously Penerima) --}}
                                        <td style="padding:10px 12px;vertical-align:middle;">
                                            <div class="fw-700" style="color:#0f172a;font-size:12px;">{{ $usage->recipient_name }}</div>
                                        </td>

                                        {{-- Material with Quick View Button --}}
                                        <td class="nowrap text-center" style="padding:10px 12px;vertical-align:middle;">
                                            <button type="button" onclick="toggleUsageItems('{{ $rowId }}')" 
                                                    class="btn btn-sm" 
                                                    style="background:#f5f3ff;color:#6d28d9;border:1px solid #ddd6fe;font-size:11px;font-weight:700;padding:2px 8px;border-radius:12px;display:inline-flex;align-items:center;gap:4px;cursor:pointer;transition:all 0.15s ease;"
                                                    title="Klik untuk melihat rincian material yang dikeluarkan">
                                                <x-lucide-boxes style="width:12px;height:12px;" />
                                                <span>{{ $usage->items->count() }} Jenis</span>
                                                <x-lucide-chevron-down id="icon-item-{{ $rowId }}" style="width:10px;height:10px;transition:transform 0.15s ease;" />
                                            </button>
                                        </td>

                                        {{-- Status --}}
                                        <td class="nowrap text-center" style="padding:10px 12px;vertical-align:middle;">
                                            @if($usage->status === 'cancelled')
                                                <span class="badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-size:11px;padding:3px 8px;border-radius:12px;font-weight:600;white-space:nowrap;">
                                                    <x-lucide-circle-x class="me-1" style="width:11px;height:11px;display:inline-block;vertical-align:-1px;" /> Dibatalkan
                                                </span>
                                            @else
                                                <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:11px;padding:3px 8px;border-radius:12px;font-weight:600;white-space:nowrap;">
                                                    <x-lucide-circle-check class="me-1" style="width:11px;height:11px;display:inline-block;vertical-align:-1px;" /> Selesai
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Petugas --}}
                                        <td class="nowrap" style="padding:10px 12px;vertical-align:middle;">
                                            <span style="font-size:11.5px;color:#64748b;">{{ $usage->issuedBy?->name ?? '-' }}</span>
                                        </td>

                                        {{-- Aksi --}}
                                        <td class="nowrap text-center" style="padding:10px 12px;vertical-align:middle;">
                                            <div class="flex justify-center gap-1" style="align-items:center;">
                                                <a href="{{ route('material-usages.show', $usage) }}" class="btn btn-sm btn-light border" title="Lihat Detail &amp; Riwayat" style="width:28px;height:28px;padding:0;border-radius:5px;display:inline-flex;align-items:center;justify-content:center;color:#2563eb;background:#f8fafc;">
                                                    <x-lucide-eye style="width:13px;height:13px;" />
                                                </a>
                                                @if($usage->status !== 'cancelled')
                                                <a href="{{ route('material-usages.print', $usage) }}" target="_blank" class="btn btn-sm btn-light border" title="Cetak Bon" style="width:28px;height:28px;padding:0;border-radius:5px;display:inline-flex;align-items:center;justify-content:center;color:#64748b;background:#f8fafc;">
                                                    <x-lucide-printer style="width:13px;height:13px;" />
                                                </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>

                                    {{-- Quick View Drawer / Row for Items --}}
                                    <tr id="{{ $rowId }}" style="display:none;background:#f8fafc;">
                                        <td colspan="7" style="padding:10px 14px;border-bottom:1.5px solid #e2e8f0;">
                                            <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;box-shadow:inset 0 1px 2px rgba(0,0,0,0.02);">
                                                <div style="font-size:11.5px;font-weight:700;color:#1e293b;margin-bottom:6px;display:flex;align-items:center;justify-content:space-between;">
                                                    <div style="display:flex;align-items:center;gap:6px;">
                                                        <x-lucide-boxes style="width:13px;height:13px;color:#2563eb;" />
                                                        <span>Rincian Item Material (#{{ $usage->usage_number }} &bull; Pemakai: {{ $usage->recipient_name }})</span>
                                                    </div>
                                                    @if($usage->job_section)
                                                    <span style="font-size:10.5px;color:#64748b;">
                                                        Pekerjaan: <strong>{{ $usage->job_section }}</strong>
                                                    </span>
                                                    @endif
                                                </div>
                                                <table style="width:100%;font-size:11px;border-collapse:collapse;">
                                                    <thead>
                                                        <tr style="border-bottom:1px solid #e2e8f0;color:#64748b;text-align:left;">
                                                            <th style="padding:4px 6px;width:30px;">#</th>
                                                            <th style="padding:4px 6px;">Nama Material</th>
                                                            <th style="padding:4px 6px;text-align:right;width:120px;">Jumlah Keluar</th>
                                                            <th style="padding:4px 6px;">Keterangan</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($usage->items as $idx => $it)
                                                        <tr style="border-bottom:1px dashed #f1f5f9;">
                                                            <td style="padding:5px 6px;color:#94a3b8;">{{ $idx + 1 }}</td>
                                                            <td style="padding:5px 6px;font-weight:600;color:#0f172a;">
                                                                {{ $it->displayName() }}
                                                                @if($it->isCustom())
                                                                    <span style="font-size:9px;color:#7c3aed;background:#f5f3ff;border:1px solid #ddd6fe;padding:1px 3px;border-radius:3px;">Custom</span>
                                                                @endif
                                                            </td>
                                                            <td style="padding:5px 6px;text-align:right;font-weight:700;color:#1e3a8a;">
                                                                {{ format_quantity($it->quantity) }} {{ $it->displayUnit() }}
                                                            </td>
                                                            <td style="padding:5px 6px;color:#64748b;">
                                                                {{ $it->notes ?: '-' }}
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>
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
        <div class="card" style="border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
            <div style="text-align:center;padding:42px 16px;color:#94a3b8;">
                <div style="display:inline-flex;align-items:center;justify-content:center;width:48px;height:48px;border-radius:12px;background:#f8fafc;border:1px solid #e2e8f0;margin-bottom:10px;color:#94a3b8;">
                    <x-lucide-inbox style="width:24px;height:24px;" />
                </div>
                <div style="font-size:13.5px;font-weight:700;color:#475569;">Belum Ada Riwayat Pemakaian Material</div>
                <div style="font-size:11.5px;color:#94a3b8;margin-top:2px;">Gunakan tombol '+ Catat Pengeluaran' untuk memulai pencatatan baru.</div>
            </div>
        </div>
    @endforelse

    @if($usages->hasPages())
    <div style="margin-top:14px;padding:8px 12px;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;">
        {{ $usages->links() }}
    </div>
    @endif

    @push('scripts')
    <script>
        function toggleWeekAccordion(weekId) {
            const body = document.getElementById('body-' + weekId);
            const icon = document.getElementById('icon-' + weekId);
            if (!body || !icon) return;
            if (body.style.display === 'none') {
                body.style.display = 'block';
                icon.style.transform = 'rotate(0deg)';
            } else {
                body.style.display = 'none';
                icon.style.transform = 'rotate(-90deg)';
            }
        }

        function toggleUsageItems(rowId) {
            const row = document.getElementById(rowId);
            const icon = document.getElementById('icon-item-' + rowId);
            if (!row) return;
            if (row.style.display === 'none') {
                row.style.display = 'table-row';
                if (icon) icon.style.transform = 'rotate(180deg)';
            } else {
                row.style.display = 'none';
                if (icon) icon.style.transform = 'rotate(0deg)';
            }
        }
    </script>
    @endpush
</x-app-layout>
