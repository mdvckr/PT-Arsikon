<x-app-layout>
    <x-slot name="title">Detail Alat: {{ $tool->name }}</x-slot>

    {{-- Breadcrumb --}}
    <div class="breadcrumb no-print" style="margin-bottom:8px;">
        <a href="{{ route('tools.index') }}">Alat</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>{{ $tool->name }}</span>
    </div>

    {{-- Action Bar --}}
    <div class="flex items-center justify-between mb-4 no-print" style="flex-wrap:wrap;gap:12px;">
        <div>
            <div class="flex items-center gap-2" style="flex-wrap:wrap;">
                <h2 class="fw-700" style="font-size:20px;color:#0f172a;margin:0;">{{ $tool->name }}</h2>
                <span style="font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;font-size:13px;color:#334155;font-weight:600;">
                    {{ $tool->code }}
                </span>
                @if($tool->category)
                <span class="text-muted" style="font-size:12.5px;">
                    · {{ $tool->category->name }}
                </span>
                @endif
                @if($tool->type)
                <span class="badge" style="background:#e0e7ff;color:#4338ca;font-size:11px;font-weight:600;">
                    {{ $tool->type }}
                </span>
                @endif
            </div>
            <p class="text-muted" style="font-size:12.5px;margin:3px 0 0;">
                Terdaftar sejak {{ $tool->created_at ? $tool->created_at->format('d M Y, H:i') : '-' }}
            </p>
        </div>
        <div class="flex gap-2 items-center">
            @can('edit tools')
            <a href="{{ route('tools.edit', $tool) }}" class="btn btn-secondary" style="height:36px;padding:0 14px;font-size:13px;font-weight:600;border-radius:6px;display:inline-flex;align-items:center;gap:6px;">
                <i class="fas fa-pen" style="font-size:12px;"></i> Edit
            </a>
            @endcan
            <button type="button" class="btn btn-primary" onclick="printTool()" id="btn-print-tool" style="height:36px;padding:0 14px;font-size:13px;font-weight:600;border-radius:6px;display:inline-flex;align-items:center;gap:6px;">
                <i class="fas fa-print" style="font-size:12px;"></i> Cetak / Print
            </button>
            <a href="{{ route('tools.index') }}" class="btn btn-light border" style="height:36px;padding:0 14px;font-size:13px;font-weight:600;border-radius:6px;color:#475569;display:inline-flex;align-items:center;gap:6px;">
                <i class="fas fa-arrow-left" style="font-size:12px;"></i> Kembali
            </a>
        </div>
    </div>

    {{-- Print Header (only visible when printing) --}}
    <div class="print-only" style="display:none;margin-bottom:20px;border-bottom:2px solid #0f172a;padding-bottom:12px;">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <div>
                <div style="font-size:18px;font-weight:800;color:#0f172a;">PT Arsikon Cipta Karya</div>
                <div style="font-size:11px;color:#64748b;margin-top:2px;">Informasi & Kartu Data Alat Kerja</div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:11px;color:#64748b;">Dicetak pada:</div>
                <div style="font-size:12px;font-weight:600;" id="print-date-time"></div>
            </div>
        </div>
    </div>

    {{-- Main Grid Layout --}}
    <div class="grid" style="grid-template-columns:1fr 340px;gap:20px;align-items:start;" id="tool-detail-grid">
        <div style="display:flex;flex-direction:column;gap:16px;">
            {{-- Stok per Lokasi / Gudang --}}
            <div class="card" style="border:1px solid #e2e8f0;box-shadow:none;border-radius:8px;overflow:hidden;">
                <div style="padding:14px 18px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
                    <div class="fw-700" style="font-size:14px;color:#0f172a;">
                        <i class="fas fa-warehouse me-1 text-primary"></i> Stok per Lokasi / Gudang
                    </div>
                </div>
                <div class="table-wrap">
                    <table class="data-table mb-0" style="width:100%;border-collapse:collapse;">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                                <th style="padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;">Gudang / Lokasi</th>
                                <th style="padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;text-align:right;">Total Stok</th>
                                <th style="padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;text-align:right;">Tersedia</th>
                                <th style="padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;text-align:right;">Dipinjam</th>
                                <th style="padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;text-align:center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tool->inventories as $inv)
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td style="padding:10px 16px;font-weight:600;color:#0f172a;">
                                    {{ $inv->warehouse?->name ?? 'Gudang Default' }}
                                    @if($inv->warehouse?->is_central)
                                    <span class="badge" style="background:#eff6ff;color:#1d4ed8;font-size:10px;margin-left:4px;">Pusat</span>
                                    @endif
                                </td>
                                <td style="padding:10px 16px;text-align:right;font-weight:600;color:#0f172a;">
                                    {{ number_format($inv->stock_total, 0, ',', '.') }} <span class="text-muted" style="font-size:11px;font-weight:normal;">unit</span>
                                </td>
                                <td style="padding:10px 16px;text-align:right;font-weight:700;color:#16a34a;">
                                    {{ number_format($inv->stock_available, 0, ',', '.') }} <span class="text-muted" style="font-size:11px;font-weight:normal;">unit</span>
                                </td>
                                <td style="padding:10px 16px;text-align:right;font-weight:600;color:#c2410c;">
                                    {{ number_format($inv->stock_borrowed, 0, ',', '.') }} <span class="text-muted" style="font-size:11px;font-weight:normal;">unit</span>
                                </td>
                                <td style="padding:10px 16px;text-align:center;font-size:12px;">
                                    @if($inv->stock_available > 0)
                                        <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-weight:600;">Tersedia</span>
                                    @elseif($inv->stock_borrowed > 0)
                                        <span class="badge" style="background:#fff7ed;color:#c2410c;border:1px solid #fed7aa;font-weight:600;">Dipinjam</span>
                                    @else
                                        <span class="badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-weight:600;">Kosong</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td style="padding:10px 16px;font-weight:600;color:#0f172a;">
                                    {{ $tool->currentWarehouse?->name ?? 'Gudang Pusat / Default' }}
                                </td>
                                <td style="padding:10px 16px;text-align:right;font-weight:600;color:#0f172a;">
                                    {{ number_format($tool->stock_total, 0, ',', '.') }} unit
                                </td>
                                <td style="padding:10px 16px;text-align:right;font-weight:700;color:#16a34a;">
                                    {{ number_format($tool->stock_available, 0, ',', '.') }} unit
                                </td>
                                <td style="padding:10px 16px;text-align:right;font-weight:600;color:#c2410c;">
                                    {{ number_format($tool->stock_borrowed, 0, ',', '.') }} unit
                                </td>
                                <td style="padding:10px 16px;text-align:center;font-size:12px;">
                                    <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-weight:600;">Tersedia</span>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Riwayat Peminjaman --}}
            <div class="card" style="border:1px solid #e2e8f0;box-shadow:none;border-radius:8px;overflow:hidden;">
                <div style="padding:14px 18px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
                    <div class="fw-700" style="font-size:14px;color:#0f172a;">
                        <i class="fas fa-hand-holding me-1 text-primary"></i> Riwayat Peminjaman (10 Terakhir)
                    </div>
                    <a href="{{ route('tool-assignments.index') }}" class="btn btn-sm btn-light border no-print" style="font-size:11.5px;padding:3px 10px;height:auto;color:#475569;">
                        Lihat Semua Peminjaman
                    </a>
                </div>
                <div class="table-wrap">
                    <table class="data-table mb-0" style="width:100%;border-collapse:collapse;">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                                <th style="padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;">No. Ref / Peminjam</th>
                                <th style="padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;">Gudang Asal</th>
                                <th style="padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;text-align:center;">Qty</th>
                                <th style="padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;">Tgl Pinjam</th>
                                <th style="padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;text-align:center;">Status</th>
                                <th style="padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;text-align:center;" class="no-print">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tool->assignments()->latest()->take(10)->get() as $assign)
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td style="padding:10px 16px;vertical-align:middle;">
                                    <div style="font-weight:600;color:#0f172a;font-size:13px;">{{ $assign->borrower_display }}</div>
                                    <div class="text-muted" style="font-size:11px;display:flex;align-items:center;gap:6px;margin-top:2px;">
                                        <span style="font-family:monospace;font-weight:600;color:#64748b;">{{ $assign->assignment_number }}</span>
                                        @if($assign->location_display && $assign->location_display !== '-')
                                        <span>· <i class="fas fa-location-dot me-1"></i>{{ $assign->location_display }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td style="padding:10px 16px;color:#334155;font-size:12.5px;vertical-align:middle;">
                                    {{ $assign->fromWarehouse?->name ?? '-' }}
                                </td>
                                <td style="padding:10px 16px;text-align:center;font-weight:700;font-size:13px;color:#0f172a;vertical-align:middle;">
                                    {{ $assign->quantity ?? 1 }} <span style="font-size:11px;font-weight:normal;color:#64748b;">unit</span>
                                </td>
                                <td style="padding:10px 16px;color:#64748b;font-size:12px;white-space:nowrap;vertical-align:middle;">
                                    <div>{{ $assign->assigned_at ? \Carbon\Carbon::parse($assign->assigned_at)->format('d/m/Y') : '-' }}</div>
                                    @if($assign->expected_return_at)
                                    <div style="font-size:11px;color:#94a3b8;">Batas: {{ \Carbon\Carbon::parse($assign->expected_return_at)->format('d/m/Y') }}</div>
                                    @endif
                                </td>
                                <td style="padding:10px 16px;text-align:center;vertical-align:middle;">
                                    @if($assign->status === 'returned')
                                        <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-weight:600;">Dikembalikan</span>
                                    @elseif($assign->status === 'active')
                                        <span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-weight:600;">Dipinjam</span>
                                    @elseif($assign->status === 'pending')
                                        <span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-weight:600;">Menunggu</span>
                                    @elseif($assign->status === 'cancelled')
                                        <span class="badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-weight:600;">Dibatalkan</span>
                                    @elseif($assign->status === 'rejected')
                                        <span class="badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-weight:600;">Ditolak</span>
                                    @else
                                        <span class="badge" style="background:#f1f5f9;color:#475569;font-weight:600;">{{ ucfirst($assign->status) }}</span>
                                    @endif
                                </td>
                                <td style="padding:10px 16px;text-align:center;vertical-align:middle;" class="no-print">
                                    @php
                                        $targetLoanId = $assign->tool_loan_id ?: $assign->id;
                                    @endphp
                                    @if($targetLoanId)
                                    <a href="{{ route('tool-assignments.show', $targetLoanId) }}" class="btn btn-sm btn-light border" style="font-size:11px;padding:3px 8px;border-radius:4px;color:#334155;" title="Lihat Detail Peminjaman">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted" style="padding:24px;font-size:13px;">Belum ada riwayat peminjaman untuk alat ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Riwayat Maintenance --}}
            <div class="card" style="border:1px solid #e2e8f0;box-shadow:none;border-radius:8px;overflow:hidden;">
                <div style="padding:14px 18px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
                    <div class="fw-700" style="font-size:14px;color:#0f172a;">
                        <i class="fas fa-wrench me-1 text-primary"></i> Riwayat Maintenance (5 Terakhir)
                    </div>
                </div>
                <div class="table-wrap">
                    <table class="data-table mb-0" style="width:100%;border-collapse:collapse;">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                                <th style="padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;">Tanggal Mulai</th>
                                <th style="padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;">Tipe</th>
                                <th style="padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;text-align:right;">Biaya</th>
                                <th style="padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;text-align:center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tool->maintenances()->latest()->take(5)->get() as $maint)
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td style="padding:10px 16px;color:#64748b;font-size:12px;vertical-align:middle;">
                                    {{ $maint->start_date ? \Carbon\Carbon::parse($maint->start_date)->format('d/m/Y') : '-' }}
                                </td>
                                <td style="padding:10px 16px;color:#1e293b;font-weight:600;font-size:12.5px;vertical-align:middle;">
                                    {{ ucfirst($maint->type) }}
                                </td>
                                <td style="padding:10px 16px;text-align:right;font-weight:600;color:#0f172a;font-size:12.5px;vertical-align:middle;">
                                    Rp {{ number_format($maint->cost ?? 0, 0, ',', '.') }}
                                </td>
                                <td style="padding:10px 16px;text-align:center;vertical-align:middle;">
                                    @if($maint->status === 'completed')
                                        <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-weight:600;">Selesai</span>
                                    @else
                                        <span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-weight:600;">Dalam Proses</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted" style="padding:24px;font-size:13px;">Belum ada riwayat maintenance untuk alat ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Right Sidebar: Informasi Alat --}}
        <div class="card" style="border:1px solid #e2e8f0;box-shadow:none;border-radius:8px;position:sticky;top:20px;">
            <div style="padding:14px 18px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
                <div class="fw-700" style="font-size:14px;color:#0f172a;">
                    <i class="fas fa-screwdriver-wrench me-1 text-primary"></i> Informasi Alat
                </div>
                @can('edit tools')
                <a href="{{ route('tools.edit', $tool) }}" class="btn btn-sm btn-light border no-print" style="font-size:11.5px;padding:3px 8px;border-radius:4px;color:#334155;">
                    <i class="fas fa-pen me-1"></i> Edit
                </a>
                @endcan
            </div>
            <div class="card-body" style="padding:18px;">
                <table style="width:100%;font-size:13px;border-collapse:collapse;">
                    @php
                        $statusBadge = match($tool->status) {
                            'available'   => '<span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-weight:600;">Tersedia</span>',
                            'assigned'    => '<span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-weight:600;">Dipinjam</span>',
                            'maintenance' => '<span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-weight:600;">Maintenance</span>',
                            'damaged'     => '<span class="badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-weight:600;">Rusak</span>',
                            'overdue'     => '<span class="badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-weight:600;">Terlambat</span>',
                            default       => '<span class="badge" style="background:#f1f5f9;color:#475569;font-weight:600;">'.ucfirst($tool->status).'</span>',
                        };

                        $rows = [
                            ['Kode Alat', '<span style="font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;font-size:12.5px;color:#334155;font-weight:600;">'.$tool->code.'</span>'],
                            ['Nama Alat', '<span style="font-weight:600;color:#0f172a;">'.$tool->name.'</span>'],
                            ['Kelompok Alat', $tool->type ? '<span class="badge" style="background:#e0e7ff;color:#4338ca;font-size:11px;font-weight:600;">'.$tool->type.'</span>' : '-'],
                            ['Spesifikasi / Ukuran', $tool->size ? '<span class="badge bg-light text-dark border"><i class="fas fa-ruler-combined me-1"></i>'.$tool->size.'</span>' : '-'],
                            ['Kategori', $tool->category?->name ?? '-'],
                            ['Merk / Brand', $tool->brand ?: '-'],
                            ['Gudang Utama', $tool->currentWarehouse?->name ?? 'Gudang Pusat / Default'],
                            ['Status', $statusBadge],
                            ['Tgl Terdaftar', $tool->created_at ? $tool->created_at->format('d M Y, H:i') : '-'],
                        ];
                    @endphp
                    @foreach($rows as [$label, $value])
                    <tr style="border-bottom:1px solid #f8fafc;">
                        <td style="padding:8px 0;color:#64748b;font-weight:500;width:42%;vertical-align:top;font-size:12px;">{{ $label }}</td>
                        <td style="padding:8px 0;color:#1e293b;vertical-align:top;text-align:right;">{!! $value !!}</td>
                    </tr>
                    @endforeach
                </table>

                {{-- Status Stok Card (Ringkasan Stok Bulk) --}}
                <div class="mt-4 p-3 rounded" style="background:#f8fafc;border:1px solid #e2e8f0;">
                    <div class="fw-700 mb-2" style="font-size:12.5px;color:#334155;display:flex;align-items:center;gap:6px;">
                        <i class="fas fa-boxes-stacked text-primary"></i> Ringkasan Stok Bulk
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:12px;">
                        <div class="p-2 rounded" style="background:#ffffff;border:1px solid #e2e8f0;">
                            <div class="text-muted" style="font-size:10.5px;">Total Stok</div>
                            <div class="fw-700" style="font-size:15px;color:#0f172a;">{{ number_format($tool->stock_total, 0, ',', '.') }} <span style="font-size:11px;font-weight:normal;color:#64748b;">unit</span></div>
                        </div>
                        <div class="p-2 rounded" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                            <div style="color:#16a34a;font-size:10.5px;font-weight:600;">Tersedia</div>
                            <div class="fw-700" style="color:#16a34a;font-size:15px;">{{ number_format($tool->stock_available, 0, ',', '.') }} <span style="font-size:11px;font-weight:normal;color:#16a34a;">unit</span></div>
                        </div>
                        <div class="p-2 rounded" style="background:#fff7ed;border:1px solid #fed7aa;">
                            <div style="color:#c2410c;font-size:10.5px;font-weight:600;">Dipinjam</div>
                            <div class="fw-700" style="color:#c2410c;font-size:15px;">{{ number_format($tool->stock_borrowed, 0, ',', '.') }} <span style="font-size:11px;font-weight:normal;color:#c2410c;">unit</span></div>
                        </div>
                        <div class="p-2 rounded" style="background:#fef2f2;border:1px solid #fecaca;">
                            <div style="color:#dc2626;font-size:10.5px;font-weight:600;">Maint./Rusak</div>
                            <div class="fw-700" style="color:#dc2626;font-size:15px;">{{ number_format($tool->stock_maintenance + $tool->stock_damaged, 0, ',', '.') }} <span style="font-size:11px;font-weight:normal;color:#dc2626;">unit</span></div>
                        </div>
                    </div>
                </div>

                {{-- Incoming Stages (if any) --}}
                @if(!empty($tool->incoming_stages) && count($tool->incoming_stages) > 0)
                <div class="mt-3">
                    <div style="font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;">
                        <i class="fas fa-truck-ramp-box me-1 text-primary"></i> Tahap Kedatangan
                    </div>
                    <div style="display:flex;flex-direction:column;gap:5px;">
                        @foreach($tool->incoming_stages as $stg)
                            @php $isReceived = ($stg['status'] ?? 'received') === 'received'; @endphp
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:6px 10px;border-radius:4px;font-size:11.5px;background:#f8fafc;border:1px solid #e2e8f0;color:#334155;">
                                <div>
                                    <strong>{{ $stg['stage'] ?? ('Tahap '.$loop->iteration) }}</strong>
                                    @if(!empty($stg['date']))
                                    <span class="text-muted" style="font-size:10.5px;">({{ \Carbon\Carbon::parse($stg['date'])->format('d/m/Y') }})</span>
                                    @endif
                                    <span style="font-size:10px;color:{{ $isReceived ? '#16a34a' : '#b45309' }};margin-left:4px;">
                                        [{{ $isReceived ? 'Sudah Masuk' : 'Rencana' }}]
                                    </span>
                                </div>
                                <div style="font-weight:600;">
                                    {{ number_format((float)($stg['qty'] ?? 0), 0, ',', '.') }} unit
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($tool->notes)
                <div class="mt-3 p-3 rounded" style="background:#f8fafc;border:1px solid #e2e8f0;font-size:12.5px;">
                    <div class="fw-600 mb-1" style="font-size:12px;color:#334155;"><i class="fas fa-note-sticky text-muted me-1"></i> Catatan / Keterangan:</div>
                    <div style="color:#475569;line-height:1.5;">{{ $tool->notes }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        @media (max-width: 992px) {
            #tool-detail-grid {
                grid-template-columns: 1fr !important;
            }
        }

        @media print {
            .no-print,
            .sidebar,
            nav,
            header,
            .breadcrumb,
            #btn-print-tool,
            .btn,
            footer {
                display: none !important;
            }

            .print-only {
                display: block !important;
            }

            body {
                background: #fff !important;
                font-size: 12px !important;
            }

            .card {
                border: 1px solid #cbd5e1 !important;
                box-shadow: none !important;
                break-inside: avoid;
                margin-bottom: 12px !important;
            }

            #tool-detail-grid {
                display: block !important;
            }

            #tool-detail-grid > div {
                width: 100% !important;
            }

            .data-table {
                font-size: 11px !important;
            }

            @page {
                margin: 15mm 12mm;
                size: A4;
            }
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        function printTool() {
            var el = document.getElementById('print-date-time');
            if (el) {
                var now = new Date();
                el.textContent = now.toLocaleDateString('id-ID', {
                    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
                }) + ', ' + now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            }
            window.print();
        }
    </script>
    @endpush
</x-app-layout>