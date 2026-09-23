<x-app-layout>
    <x-slot name="title">Log Harian Logistik & Aktivitas Proyek</x-slot>

    @push('styles')
    <style>
        .daily-filter-grid {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .daily-filter-grid .filter-col {
            flex: 1;
            min-width: 200px;
        }

        .daily-filter-grid .form-control {
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

        .daily-filter-grid .form-control:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 2px rgba(37,99,235,0.15) !important;
            outline: none !important;
        }

        .daily-filter-grid .btn {
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

        .daily-kpi-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 12px;
        }

        .daily-kpi-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 14px;
            box-shadow: 0 2px 6px -1px rgba(0,0,0,0.03);
            display: flex;
            align-items: center;
            gap: 12px;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .daily-kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px -2px rgba(0,0,0,0.06);
        }

        .daily-kpi-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .daily-section-card {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #ffffff;
            box-shadow: 0 2px 6px -1px rgba(0,0,0,0.03);
            overflow: hidden;
            margin-bottom: 16px;
        }

        .daily-section-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 10px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .daily-table th {
            padding: 8px 12px !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.3px !important;
            color: #64748b !important;
            background: #fafafa !important;
            border-bottom: 1px solid #e2e8f0 !important;
            white-space: nowrap !important;
        }

        .daily-table td {
            padding: 8px 12px !important;
            vertical-align: middle !important;
            font-size: 12px !important;
            border-bottom: 1px solid #f1f5f9 !important;
        }

        .category-sub-bar {
            background: #f8fafc;
            padding: 7px 14px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        @media (max-width: 1200px) {
            .daily-kpi-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .daily-kpi-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .daily-page-header {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 10px;
            }
            .daily-page-header .header-actions {
                width: 100%;
                justify-content: flex-start;
                flex-wrap: wrap;
            }
        }

        @media (max-width: 480px) {
            .daily-kpi-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    @endpush

    @php
        $carbonDate = \Carbon\Carbon::parse($date);
        $prevDate = $carbonDate->copy()->subDay()->toDateString();
        $nextDate = $carbonDate->copy()->addDay()->toDateString();
        $isToday = $date === date('Y-m-d');
    @endphp

  

    {{-- Header & Action Bar --}}
    <div class="flex items-center justify-between mb-3 daily-page-header" style="flex-wrap:wrap;gap:12px;">
        <div>
            <h2 class="fw-700" style="font-size:16px;color:#0f172a;margin:0;display:flex;align-items:center;gap:6px;">
                <i class="fas fa-calendar-check text-primary" style="font-size:15px;"></i>
                Log Harian Logistik & Aktivitas Proyek
            </h2>
            <p class="text-muted" style="font-size:12px;margin-top:2px;margin-bottom:0;">
                Rekap mutasi barang masuk, pemakaian lapangan, pergerakan alat, dan posisi sisa stok harian
            </p>
        </div>
        <div class="header-actions" style="display:flex;align-items:center;gap:6px;">

            <a href="{{ route('daily-log.print', ['warehouse_id' => $selectedWarehouse->id, 'date' => $date]) }}" target="_blank" 
               class="btn btn-sm btn-primary" style="height:32px;padding:0 12px;border-radius:6px;font-size:12px;font-weight:600;box-shadow:0 2px 4px rgba(37,99,235,0.2);">
                <i class="fas fa-print me-1"></i> Cetak Laporan
            </a>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="card mb-3" style="border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
        <div class="card-body" style="padding:12px 16px;">
            <form method="GET" action="{{ route('daily-log.index') }}" class="daily-filter-grid">
                <div class="filter-col" style="flex:2;min-width:240px;">
                    <label class="form-label" style="font-size:11px;font-weight:600;color:#475569;margin-bottom:3px;">
                        Gudang / Site Proyek
                    </label>
                    <div style="position:relative;">
                        <i class="fas fa-warehouse" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);font-size:11px;color:#94a3b8;pointer-events:none;"></i>
                        <select name="warehouse_id" class="form-control" onchange="this.form.submit()" style="padding-left:28px !important;">
                            @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ $selectedWarehouse->id == $wh->id ? 'selected' : '' }}>
                                {{ $wh->name }} {{ $wh->is_central ? '(Pusat)' : '(Proyek)' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="filter-col" style="max-width:200px;">
                    <label class="form-label" style="font-size:11px;font-weight:600;color:#475569;margin-bottom:3px;">
                        Pilih Tanggal
                    </label>
                    <input type="date" name="date" value="{{ $date }}" class="form-control" onchange="this.form.submit()">
                </div>

                <div style="display:flex;gap:6px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-arrows-rotate me-1"></i> Muat Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- KPI Metric Cards (5 Cards) --}}
    <div class="daily-kpi-grid mb-3">
        {{-- 1. Material Masuk --}}
        <div class="daily-kpi-card">
            <div class="daily-kpi-icon" style="background:#eff6ff;color:#2563eb;">
                <i class="fas fa-truck-ramp-box"></i>
            </div>
            <div>
                <div style="font-size:10.5px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.3px;">Material Masuk</div>
                <div style="display:flex;align-items:baseline;gap:4px;margin-top:1px;">
                    <span style="font-size:20px;font-weight:800;color:#0f172a;line-height:1;">{{ $totalItemsIn }}</span>
                    <span style="font-size:11px;color:#64748b;font-weight:600;">Item</span>
                </div>
                <div class="text-muted" style="font-size:10.5px;margin-top:2px;">Surat jalan & GR diterima</div>
            </div>
        </div>

        {{-- 2. Material Dipakai --}}
        <div class="daily-kpi-card">
            <div class="daily-kpi-icon" style="background:#fff7ed;color:#ea580c;">
                <i class="fas fa-dolly"></i>
            </div>
            <div>
                <div style="font-size:10.5px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.3px;">Material Dipakai</div>
                <div style="display:flex;align-items:baseline;gap:4px;margin-top:1px;">
                    <span style="font-size:20px;font-weight:800;color:#0f172a;line-height:1;">{{ $totalItemsOut }}</span>
                    <span style="font-size:11px;color:#64748b;font-weight:600;">Item</span>
                </div>
                <div class="text-muted" style="font-size:10.5px;margin-top:2px;">Dikeluarkan ke lapangan</div>
            </div>
        </div>

        {{-- 3. Alat Ready --}}
        <div class="daily-kpi-card">
            <div class="daily-kpi-icon" style="background:#ecfdf5;color:#059669;">
                <i class="fas fa-screwdriver-wrench"></i>
            </div>
            <div>
                <div style="font-size:10.5px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.3px;">Alat Ready</div>
                <div style="display:flex;align-items:baseline;gap:4px;margin-top:1px;">
                    <span style="font-size:20px;font-weight:800;color:#0f172a;line-height:1;">{{ $totalToolsReady }}</span>
                    <span style="font-size:11px;color:#64748b;font-weight:600;">Unit</span>
                </div>
                <div class="text-muted" style="font-size:10.5px;margin-top:2px;">Standby di gudang</div>
            </div>
        </div>

        {{-- 4. Alat di Lapangan --}}
        <div class="daily-kpi-card">
            <div class="daily-kpi-icon" style="background:#f5f3ff;color:#7c3aed;">
                <i class="fas fa-hand-holding"></i>
            </div>
            <div>
                <div style="font-size:10.5px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.3px;">Alat di Lapangan</div>
                <div style="display:flex;align-items:baseline;gap:4px;margin-top:1px;">
                    <span style="font-size:20px;font-weight:800;color:#0f172a;line-height:1;">{{ $totalToolsInUse }}</span>
                    <span style="font-size:11px;color:#64748b;font-weight:600;">Unit</span>
                </div>
                <div class="text-muted" style="font-size:10.5px;margin-top:2px;">Sedang aktif dipinjam</div>
            </div>
        </div>

        {{-- 5. Alat Rusak / Servis --}}
        <div class="daily-kpi-card">
            <div class="daily-kpi-icon" style="background:#fef2f2;color:{{ $totalToolsDamaged > 0 ? '#dc2626' : '#94a3b8' }};">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <div>
                <div style="font-size:10.5px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.3px;">Alat Rusak / Servis</div>
                <div style="display:flex;align-items:baseline;gap:4px;margin-top:1px;">
                    <span style="font-size:20px;font-weight:800;color:{{ $totalToolsDamaged > 0 ? '#dc2626' : '#0f172a' }};line-height:1;">{{ $totalToolsDamaged }}</span>
                    <span style="font-size:11px;color:#64748b;font-weight:600;">Unit</span>
                </div>
                <div class="text-muted" style="font-size:10.5px;margin-top:2px;">Perlu perbaikan</div>
            </div>
        </div>
    </div>

    {{-- ==================== SECTION 1: PEMAKAIAN MATERIAL LAPANGAN ==================== --}}
    <div class="daily-section-card">
        <div class="daily-section-header">
            <div style="display:flex;align-items:center;gap:8px;">
                <span style="width:22px;height:22px;border-radius:6px;background:#eff6ff;color:#2563eb;font-weight:700;font-size:11px;display:inline-flex;align-items:center;justify-content:center;">1</span>
                <span style="font-size:13px;font-weight:700;color:#1e293b;">Pemakaian Material Lapangan Hari Ini (Outgoing)</span>
            </div>
            <span class="badge" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;font-size:10.5px;padding:2px 8px;border-radius:12px;font-weight:600;">
                {{ $usages->count() }} Bon Pengeluaran
            </span>
        </div>
        <div class="table-wrap">
            <table class="data-table daily-table mb-0">
                <thead>
                    <tr>
                        <th style="width:140px;">No. Bon</th>
                        <th style="width:180px;">Penerima (Mandor/Tukang)</th>
                        <th style="min-width:160px;">Bagian Pekerjaan / Zona</th>
                        <th>Material & Kuantitas yang Dikeluarkan</th>
                        <th style="width:130px;">Petugas Gudang</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usages as $usage)
                    <tr>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('material-usages.show', $usage) }}" style="color:#2563eb;font-weight:700;font-size:12px;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                                <i class="fas fa-file-invoice text-primary" style="font-size:10.5px;"></i>
                                {{ $usage->usage_number }}
                            </a>
                        </td>
                        <td>
                            <div class="fw-600" style="color:#0f172a;font-size:12px;">{{ $usage->recipient_name }}</div>
                        </td>
                        <td>
                            <span class="badge" style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;font-size:10.5px;padding:2px 6px;border-radius:4px;">
                                {{ $usage->job_section ?? 'Pekerjaan Umum' }}
                            </span>
                        </td>
                        <td>
                            <div style="display:flex;flex-direction:column;gap:3px;">
                                @foreach($usage->items as $uItem)
                                <div style="font-size:12px;line-height:1.4;">
                                    <strong style="color:#0f172a;">{{ format_quantity($uItem->quantity) }} {{ $uItem->material?->unit?->abbreviation ?? $uItem->custom_item_unit ?? 'unit' }}</strong>
                                    <span style="color:#475569;">— {{ $uItem->material?->name ?? $uItem->custom_item_name }}</span>
                                    @if($uItem->notes)
                                    <span class="text-muted" style="font-size:11px;">({{ $uItem->notes }})</span>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </td>
                        <td>
                            <span class="text-muted" style="font-size:11.5px;">{{ $usage->issuedBy?->name ?? '-' }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center;color:#94a3b8;padding:20px;font-size:12px;">
                            <i class="fas fa-box-open" style="font-size:20px;color:#cbd5e1;display:block;margin-bottom:4px;"></i>
                            Tidak ada pengeluaran / pemakaian material pada tanggal {{ $carbonDate->translatedFormat('d F Y') }}.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ==================== SECTION 2: MATERIAL MASUK ==================== --}}
    <div class="daily-section-card">
        <div class="daily-section-header">
            <div style="display:flex;align-items:center;gap:8px;">
                <span style="width:22px;height:22px;border-radius:6px;background:#ecfdf5;color:#047857;font-weight:700;font-size:11px;display:inline-flex;align-items:center;justify-content:center;">2</span>
                <span style="font-size:13px;font-weight:700;color:#1e293b;">Penerimaan Material Masuk (Incoming)</span>
            </div>
            <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:10.5px;padding:2px 8px;border-radius:12px;font-weight:600;">
                {{ $incomingDistributions->count() + $incomingGoodsReceipts->count() }} Dokumen Penerimaan
            </span>
        </div>
        <div class="table-wrap">
            <table class="data-table daily-table mb-0">
                <thead>
                    <tr>
                        <th style="width:150px;">No. Surat Jalan / GR</th>
                        <th style="width:180px;">Sumber Pengirim</th>
                        <th>Material & Kuantitas Diterima</th>
                        <th style="width:120px;text-align:center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php $hasIncoming = false; @endphp
                    @foreach($incomingDistributions as $dist)
                    @php $hasIncoming = true; @endphp
                    <tr>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('distributions.show', $dist) }}" style="color:#2563eb;font-weight:700;font-size:12px;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                                <i class="fas fa-truck-fast text-primary" style="font-size:10.5px;"></i>
                                {{ $dist->distribution_number }}
                            </a>
                        </td>
                        <td>
                            <div class="fw-600" style="color:#0f172a;font-size:12px;">{{ $dist->fromWarehouse?->name ?? 'Gudang Pusat' }}</div>
                            @if($dist->driver_name)
                            <div class="text-muted" style="font-size:11px;">Supir: {{ $dist->driver_name }}</div>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;flex-direction:column;gap:3px;">
                                @foreach($dist->items as $dItem)
                                <div style="font-size:12px;line-height:1.4;">
                                    <strong style="color:#0f172a;">{{ format_quantity($dItem->qty_received) }} {{ $dItem->material?->unit?->abbreviation ?? $dItem->custom_item_unit ?? 'unit' }}</strong>
                                    <span style="color:#475569;">— {{ $dItem->material?->name ?? $dItem->tool?->name ?? $dItem->custom_item_name }}</span>
                                </div>
                                @endforeach
                            </div>
                        </td>
                        <td style="text-align:center;">
                            <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:10.5px;padding:2px 7px;border-radius:10px;">
                                <i class="fas fa-check me-1"></i> Diterima di Site
                            </span>
                        </td>
                    </tr>
                    @endforeach

                    @foreach($incomingGoodsReceipts as $gr)
                    @php $hasIncoming = true; @endphp
                    <tr>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('goods-receipts.show', $gr) }}" style="color:#2563eb;font-weight:700;font-size:12px;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                                <i class="fas fa-receipt text-primary" style="font-size:10.5px;"></i>
                                {{ $gr->receipt_number }}
                            </a>
                        </td>
                        <td>
                            <div class="fw-600" style="color:#0f172a;font-size:12px;">{{ $gr->supplier?->name ?? 'Supplier Langsung' }}</div>
                            @if($gr->supplier_do_number ?? $gr->invoice_number)
                            <div class="text-muted" style="font-size:11px;">Ref: {{ $gr->supplier_do_number ?? $gr->invoice_number }}</div>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;flex-direction:column;gap:3px;">
                                @foreach($gr->items as $gItem)
                                <div style="font-size:12px;line-height:1.4;">
                                    <strong style="color:#0f172a;">{{ format_quantity($gItem->quantity_received ?? $gItem->qty_received) }} {{ $gItem->material?->unit?->abbreviation ?? 'unit' }}</strong>
                                    <span style="color:#475569;">— {{ $gItem->material?->name }}</span>
                                </div>
                                @endforeach
                            </div>
                        </td>
                        <td style="text-align:center;">
                            <span class="badge" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;font-size:10.5px;padding:2px 7px;border-radius:10px;">
                                <i class="fas fa-check-circle me-1"></i> Masuk Gudang
                            </span>
                        </td>
                    </tr>
                    @endforeach

                    @if(!$hasIncoming)
                    <tr>
                        <td colspan="4" style="text-align:center;color:#94a3b8;padding:20px;font-size:12px;">
                            <i class="fas fa-truck" style="font-size:20px;color:#cbd5e1;display:block;margin-bottom:4px;"></i>
                            Tidak ada penerimaan barang masuk pada tanggal {{ $carbonDate->translatedFormat('d F Y') }}.
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- ==================== SECTION 3: DISTRIBUSI KELUAR ==================== --}}
    <div class="daily-section-card">
        <div class="daily-section-header">
            <div style="display:flex;align-items:center;gap:8px;">
                <span style="width:22px;height:22px;border-radius:6px;background:#f5f3ff;color:#7c3aed;font-weight:700;font-size:11px;display:inline-flex;align-items:center;justify-content:center;">3</span>
                <span style="font-size:13px;font-weight:700;color:#1e293b;">Distribusi Keluar ke Proyek / Gudang Lain</span>
            </div>
            <span class="badge" style="background:#f5f3ff;color:#7c3aed;border:1px solid #ddd6fe;font-size:10.5px;padding:2px 8px;border-radius:12px;font-weight:600;">
                {{ $outgoingDistributions->count() }} Pengiriman
            </span>
        </div>
        <div class="table-wrap">
            <table class="data-table daily-table mb-0">
                <thead>
                    <tr>
                        <th style="width:150px;">No. Surat Jalan</th>
                        <th style="width:180px;">Tujuan Pengiriman</th>
                        <th style="width:110px;text-align:center;">Tgl Kirim</th>
                        <th>Material & Kuantitas Dikirim</th>
                        <th style="width:120px;text-align:center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($outgoingDistributions as $dist)
                    <tr>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('distributions.show', $dist) }}" style="color:#2563eb;font-weight:700;font-size:12px;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                                <i class="fas fa-paper-plane text-primary" style="font-size:10.5px;"></i>
                                {{ $dist->distribution_number }}
                            </a>
                        </td>
                        <td>
                            <div class="fw-600" style="color:#0f172a;font-size:12px;">{{ $dist->toWarehouse?->name ?? 'Gudang Lain' }}</div>
                            @if($dist->driver_name)
                            <div class="text-muted" style="font-size:11px;">Supir: {{ $dist->driver_name }}</div>
                            @endif
                        </td>
                        <td style="text-align:center;font-size:11.5px;color:#475569;">
                            {{ $dist->shipped_at ? \Carbon\Carbon::parse($dist->shipped_at)->format('d/m/Y') : '—' }}
                        </td>
                        <td>
                            <div style="display:flex;flex-direction:column;gap:3px;">
                                @foreach($dist->items as $dItem)
                                <div style="font-size:12px;line-height:1.4;">
                                    <strong style="color:#0f172a;">{{ format_quantity($dItem->qty_shipped) }} {{ $dItem->material?->unit?->abbreviation ?? $dItem->custom_item_unit ?? 'unit' }}</strong>
                                    <span style="color:#475569;">— {{ $dItem->material?->name ?? $dItem->tool?->name ?? $dItem->custom_item_name }}</span>
                                </div>
                                @endforeach
                            </div>
                        </td>
                        <td style="text-align:center;">
                            @if($dist->status === 'received')
                                <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:10.5px;padding:2px 7px;border-radius:10px;">Diterima</span>
                            @else
                                <span class="badge" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;font-size:10.5px;padding:2px 7px;border-radius:10px;">Dalam Perjalanan</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center;color:#94a3b8;padding:20px;font-size:12px;">
                            <i class="fas fa-route" style="font-size:20px;color:#cbd5e1;display:block;margin-bottom:4px;"></i>
                            Tidak ada distribusi pengiriman keluar pada tanggal {{ $carbonDate->translatedFormat('d F Y') }}.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ==================== SECTION 4: RETUR MATERIAL ==================== --}}
    <div class="daily-section-card">
        <div class="daily-section-header">
            <div style="display:flex;align-items:center;gap:8px;">
                <span style="width:22px;height:22px;border-radius:6px;background:#fffbeb;color:#b45309;font-weight:700;font-size:11px;display:inline-flex;align-items:center;justify-content:center;">4</span>
                <span style="font-size:13px;font-weight:700;color:#1e293b;">Retur Material dari Lapangan</span>
            </div>
            <span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-size:10.5px;padding:2px 8px;border-radius:12px;font-weight:600;">
                {{ $materialReturns->count() }} Dokumen Retur
            </span>
        </div>
        <div class="table-wrap">
            <table class="data-table daily-table mb-0">
                <thead>
                    <tr>
                        <th style="width:140px;">No. Retur</th>
                        <th style="width:180px;">Sumber Retur</th>
                        <th style="width:110px;text-align:center;">Tgl Terima</th>
                        <th>Material & Kuantitas Diterima</th>
                        <th style="width:130px;text-align:center;">Alasan Retur</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($materialReturns as $ret)
                    <tr>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('returns.show', $ret) }}" style="color:#2563eb;font-weight:700;font-size:12px;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                                <i class="fas fa-rotate-left text-warning" style="font-size:10.5px;"></i>
                                {{ $ret->return_number }}
                            </a>
                        </td>
                        <td>
                            <div class="fw-600" style="color:#0f172a;font-size:12px;">{{ $ret->fromWarehouse?->name ?? 'Lapangan Proyek' }}</div>
                            <div class="text-muted" style="font-size:11px;">Penerima: {{ $ret->receiver?->name ?? '-' }}</div>
                        </td>
                        <td style="text-align:center;font-size:11.5px;color:#475569;">
                            {{ $ret->received_at ? \Carbon\Carbon::parse($ret->received_at)->format('d/m/Y') : '—' }}
                        </td>
                        <td>
                            <div style="display:flex;flex-direction:column;gap:3px;">
                                @foreach($ret->items as $rItem)
                                <div style="font-size:12px;line-height:1.4;">
                                    <strong style="color:#0f172a;">{{ format_quantity($rItem->received_qty) }} {{ $rItem->material?->unit?->abbreviation ?? 'unit' }}</strong>
                                    <span style="color:#475569;">— {{ $rItem->material?->name }}</span>
                                    <span class="text-muted" style="font-size:11px;">({{ ucfirst($rItem->condition ?? 'Kondisi Baik') }})</span>
                                </div>
                                @endforeach
                            </div>
                        </td>
                        <td style="text-align:center;">
                            <span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-size:10.5px;padding:2px 7px;border-radius:10px;">
                                {{ $ret->reason_label ?? $ret->getReasonLabel() ?? 'Retur Sisa' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center;color:#94a3b8;padding:20px;font-size:12px;">
                            <i class="fas fa-arrow-rotate-left" style="font-size:20px;color:#cbd5e1;display:block;margin-bottom:4px;"></i>
                            Tidak ada retur material diterima pada tanggal {{ $carbonDate->translatedFormat('d F Y') }}.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ==================== SECTION 5: PANTAUAN ALAT KERJA ==================== --}}
    <div class="daily-section-card">
        <div class="daily-section-header">
            <div style="display:flex;align-items:center;gap:8px;">
                <span style="width:22px;height:22px;border-radius:6px;background:#f5f3ff;color:#6d28d9;font-weight:700;font-size:11px;display:inline-flex;align-items:center;justify-content:center;">5</span>
                <span style="font-size:13px;font-weight:700;color:#1e293b;">Pantauan Alat Kerja di Lapangan (Active Tools)</span>
            </div>
            <span class="badge" style="background:#f5f3ff;color:#6d28d9;border:1px solid #ddd6fe;font-size:10.5px;padding:2px 8px;border-radius:12px;font-weight:600;">
                {{ $toolAssignments->count() }} Peminjaman Aktif
            </span>
        </div>
        <div class="table-wrap">
            <table class="data-table daily-table mb-0">
                <thead>
                    <tr>
                        <th>Nama Alat & Kode</th>
                        <th style="width:80px;text-align:center;">Qty</th>
                        <th>Peminjam (Mandor/Tukang)</th>
                        <th>Lokasi / Zona Kerja</th>
                        <th style="width:100px;text-align:center;">Tgl Pinjam</th>
                        <th style="width:110px;text-align:center;">Target Kembali</th>
                        <th style="width:130px;text-align:center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($toolAssignments as $ta)
                    <tr>
                        <td>
                            <div class="fw-600" style="color:#0f172a;font-size:12px;">{{ $ta->tool?->name }}</div>
                            <div class="text-muted" style="font-size:11px;margin-top:1px;">
                                @if($ta->tool?->code)
                                    <code style="background:#f1f5f9;color:#475569;padding:1px 4px;border-radius:3px;font-size:10px;">{{ $ta->tool->code }}</code>
                                @endif
                            </div>
                        </td>
                        <td style="text-align:center;">
                            <strong style="font-size:12px;color:#0f172a;">{{ $ta->quantity ?? 1 }} Unit</strong>
                        </td>
                        <td>
                            <div class="fw-600" style="color:#0f172a;font-size:12px;">{{ $ta->borrower_display }}</div>
                            @if($ta->borrower_phone)
                            <div class="text-muted" style="font-size:10.5px;">Telp: {{ $ta->borrower_phone }}</div>
                            @endif
                        </td>
                        <td>
                            <span style="color:#334155;font-size:11.5px;">{{ $ta->location_display }}</span>
                        </td>
                        <td style="text-align:center;font-size:11.5px;color:#475569;">
                            {{ $ta->assigned_at ? \Carbon\Carbon::parse($ta->assigned_at)->format('d/m/Y') : '-' }}
                        </td>
                        <td style="text-align:center;font-size:11.5px;">
                            @if($ta->expected_return_at)
                                @php
                                    $exp = \Carbon\Carbon::parse($ta->expected_return_at);
                                    $isOver = $ta->status === 'active' && $exp->isPast();
                                @endphp
                                <span class="{{ $isOver ? 'text-danger fw-700' : 'text-muted' }}">
                                    {{ $exp->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            @if($ta->status === 'returned')
                                <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:10.5px;padding:2px 7px;border-radius:10px;">Dikembalikan</span>
                            @elseif($ta->status === 'overdue' || ($ta->status === 'active' && $ta->expected_return_at && \Carbon\Carbon::parse($ta->expected_return_at)->isPast()))
                                <span class="badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-size:10.5px;padding:2px 7px;border-radius:10px;">Terlambat</span>
                            @else
                                <span class="badge" style="background:#f5f3ff;color:#6d28d9;border:1px solid #ddd6fe;font-size:10.5px;padding:2px 7px;border-radius:10px;">Dipinjam</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center;color:#94a3b8;padding:20px;font-size:12px;">
                            <i class="fas fa-screwdriver-wrench" style="font-size:20px;color:#cbd5e1;display:block;margin-bottom:4px;"></i>
                            Tidak ada alat kerja yang sedang dipinjam di lapangan pada tanggal ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ==================== SECTION 6: NERACA SISA STOK MATERIAL ==================== --}}
    <div class="daily-section-card">
        <div class="daily-section-header">
            <div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span style="width:22px;height:22px;border-radius:6px;background:#eff6ff;color:#2563eb;font-weight:700;font-size:11px;display:inline-flex;align-items:center;justify-content:center;">6</span>
                    <span style="font-size:13px;font-weight:700;color:#1e293b;">Neraca Sisa Stok Material Hari Ini (Stock Balance)</span>
                </div>
                <div class="text-muted" style="font-size:11px;margin-top:2px;">
                    Rumus kalkulasi: Stok Awal + Mutasi Masuk - Pemakaian Keluar = Sisa Stok Akhir
                </div>
            </div>
            <span class="badge" style="background:#f8fafc;color:#475569;border:1px solid #cbd5e1;font-size:10.5px;padding:2px 8px;border-radius:12px;">
                Material Terpengaruh
            </span>
        </div>
        <div>
            @if(isset($stockBalanceGrouped) && $stockBalanceGrouped->isNotEmpty())
                @foreach($stockBalanceGrouped as $catName => $rows)
                <div class="category-sub-bar">
                    <div style="display:flex;align-items:center;gap:6px;">
                        <i class="fas fa-tag text-primary" style="font-size:11px;"></i>
                        <span style="font-weight:700;font-size:11.5px;text-transform:uppercase;color:#1e293b;letter-spacing:0.3px;">{{ $catName }}</span>
                    </div>
                    <span class="badge" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;font-size:10px;padding:1px 6px;border-radius:4px;">
                        {{ $rows->count() }} Jenis Material
                    </span>
                </div>
                <div class="table-wrap">
                    <table class="data-table daily-table mb-0">
                        <thead>
                            <tr>
                                <th style="width:40px;text-align:center;">#</th>
                                <th>Nama Material</th>
                                <th style="width:110px;text-align:right;">Stok Awal</th>
                                <th style="width:110px;text-align:right;">Masuk (+)</th>
                                <th style="width:110px;text-align:right;">Dipakai (-)</th>
                                <th style="width:120px;text-align:right;">Sisa Akhir</th>
                                <th style="width:75px;text-align:center;">Satuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $idx => $stk)
                            <tr>
                                <td style="text-align:center;color:#94a3b8;font-size:11px;">{{ $idx + 1 }}</td>
                                <td>
                                    <div class="fw-600" style="color:#0f172a;font-size:12px;">{{ $stk->material_name }}</div>
                                    @if($stk->material_code)
                                    <div class="text-muted" style="font-size:10.5px;"><code>{{ $stk->material_code }}</code></div>
                                    @endif
                                </td>
                                <td style="text-align:right;color:#475569;font-size:12px;">{{ format_quantity($stk->opening_stock) }}</td>
                                <td style="text-align:right;font-weight:600;font-size:12px;color:{{ $stk->qty_in > 0 ? '#059669' : '#94a3b8' }};">
                                    {{ $stk->qty_in > 0 ? '+' . format_quantity($stk->qty_in) : '-' }}
                                </td>
                                <td style="text-align:right;font-weight:600;font-size:12px;color:{{ $stk->qty_out > 0 ? '#dc2626' : '#94a3b8' }};">
                                    {{ $stk->qty_out > 0 ? '-' . format_quantity($stk->qty_out) : '-' }}
                                </td>
                                <td style="text-align:right;font-weight:800;font-size:12.5px;color:#0f172a;">
                                    {{ format_quantity($stk->closing_stock) }}
                                </td>
                                <td style="text-align:center;color:#64748b;font-size:11.5px;font-weight:600;">{{ $stk->unit }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endforeach
            @else
                <div style="text-align:center;color:#94a3b8;padding:24px;font-size:12px;">
                    <i class="fas fa-layer-group" style="font-size:22px;color:#cbd5e1;display:block;margin-bottom:6px;"></i>
                    Tidak ada transaksi mutasi material pada tanggal {{ $carbonDate->translatedFormat('d F Y') }}.
                </div>
            @endif
        </div>
    </div>

    {{-- ==================== SECTION 7: KESIAPAN ALAT KERJA ==================== --}}
    <div class="daily-section-card">
        <div class="daily-section-header">
            <div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span style="width:22px;height:22px;border-radius:6px;background:#ecfdf5;color:#047857;font-weight:700;font-size:11px;display:inline-flex;align-items:center;justify-content:center;">7</span>
                    <span style="font-size:13px;font-weight:700;color:#1e293b;">Neraca Posisi & Kesiapan Alat Kerja (Tool Availability)</span>
                </div>
                <div class="text-muted" style="font-size:11px;margin-top:2px;">
                    Pantauan ketersediaan unit di gudang vs peminjaman di lapangan
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:10.5px;padding:2px 7px;border-radius:10px;">{{ $totalToolsReady }} Ready</span>
                <span class="badge" style="background:#f5f3ff;color:#7c3aed;border:1px solid #ddd6fe;font-size:10.5px;padding:2px 7px;border-radius:10px;">{{ $totalToolsInUse }} di Lapangan</span>
                @if($totalToolsDamaged > 0)
                <span class="badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-size:10.5px;padding:2px 7px;border-radius:10px;">{{ $totalToolsDamaged }} Rusak/Servis</span>
                @endif
            </div>
        </div>
        <div>
            @if(isset($toolBalanceGrouped) && $toolBalanceGrouped->isNotEmpty())
                @foreach($toolBalanceGrouped as $catName => $rows)
                <div class="category-sub-bar">
                    <div style="display:flex;align-items:center;gap:6px;">
                        <i class="fas fa-toolbox text-primary" style="font-size:11px;"></i>
                        <span style="font-weight:700;font-size:11.5px;text-transform:uppercase;color:#1e293b;letter-spacing:0.3px;">{{ $catName }}</span>
                    </div>
                    <span class="badge" style="background:#f5f3ff;color:#7c3aed;border:1px solid #ddd6fe;font-size:10px;padding:1px 6px;border-radius:4px;">
                        {{ $rows->count() }} Jenis Alat
                    </span>
                </div>
                <div class="table-wrap">
                    <table class="data-table daily-table mb-0">
                        <thead>
                            <tr>
                                <th style="width:40px;text-align:center;">#</th>
                                <th>Nama Alat Kerja</th>
                                <th style="width:95px;text-align:center;">Total Unit</th>
                                <th style="width:95px;text-align:center;">Ready (Gudang)</th>
                                <th style="width:95px;text-align:center;">Di Lapangan</th>
                                <th style="width:95px;text-align:center;">Servis / Rusak</th>
                                <th>Mandor Pemegang / Posisi</th>
                                <th style="width:130px;text-align:center;">Kesiapan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $tIdx => $tb)
                            <tr>
                                <td style="text-align:center;color:#94a3b8;font-size:11px;">{{ $tIdx + 1 }}</td>
                                <td>
                                    <div class="fw-600" style="color:#0f172a;font-size:12px;">{{ $tb->tool_name }}</div>
                                    <div class="text-muted" style="font-size:10.5px;margin-top:1px;">
                                        @if($tb->tool_code)
                                            <code>{{ $tb->tool_code }}</code>
                                        @endif
                                        @if($tb->brand)
                                            <span>• Merk: {{ $tb->brand }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td style="text-align:center;">
                                    <strong style="font-size:12px;color:#0f172a;">{{ $tb->stock_total }} Unit</strong>
                                </td>
                                <td style="text-align:center;">
                                    <span style="font-weight:600;font-size:11.5px;color:{{ $tb->stock_available > 0 ? '#059669' : '#94a3b8' }};">
                                        {{ $tb->stock_available }} Unit
                                    </span>
                                </td>
                                <td style="text-align:center;">
                                    <span style="font-weight:600;font-size:11.5px;color:{{ $tb->stock_borrowed > 0 ? '#7c3aed' : '#94a3b8' }};">
                                        {{ $tb->stock_borrowed }} Unit
                                    </span>
                                </td>
                                <td style="text-align:center;">
                                    <span style="font-weight:600;font-size:11.5px;color:{{ $tb->stock_maintenance > 0 ? '#dc2626' : '#94a3b8' }};">
                                        {{ $tb->stock_maintenance }} Unit
                                    </span>
                                </td>
                                <td>
                                    @if(!empty($tb->borrowers))
                                        <div style="display:flex;flex-direction:column;gap:2px;">
                                            @foreach($tb->borrowers as $bName)
                                            <div style="font-size:11.5px;color:#1e293b;display:flex;align-items:center;gap:4px;">
                                                <i class="fas fa-user text-muted" style="font-size:9.5px;"></i>
                                                {{ $bName }}
                                            </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted" style="font-size:11px;">Standby di gudang</span>
                                    @endif
                                </td>
                                <td style="text-align:center;">
                                    @if($tb->stock_available > 0)
                                        <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:10.5px;padding:2px 7px;border-radius:10px;">Siap Pakai</span>
                                    @elseif($tb->stock_borrowed > 0)
                                        <span class="badge" style="background:#f5f3ff;color:#7c3aed;border:1px solid #ddd6fe;font-size:10.5px;padding:2px 7px;border-radius:10px;">Di Lapangan</span>
                                    @elseif($tb->stock_maintenance > 0)
                                        <span class="badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-size:10.5px;padding:2px 7px;border-radius:10px;">Perlu Servis</span>
                                    @else
                                        <span class="badge" style="background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;font-size:10.5px;padding:2px 7px;border-radius:10px;">Kosong</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endforeach
            @else
                <div style="text-align:center;color:#94a3b8;padding:24px;font-size:12px;">
                    <i class="fas fa-wrench" style="font-size:22px;color:#cbd5e1;display:block;margin-bottom:6px;"></i>
                    Tidak ada alat yang dipinjam pada tanggal {{ $carbonDate->translatedFormat('d F Y') }}.
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
