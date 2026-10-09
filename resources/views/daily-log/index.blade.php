<x-app-layout>
    <x-slot name="title">Log Harian Logistik dan Aktivitas Proyek</x-slot>

    @push('styles')
    <style>
        .daily-page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 16px;
        }

        .daily-filter-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #ffffff;
            box-shadow: 0 2px 6px -1px rgba(0,0,0,0.03);
            padding: 14px 18px;
            margin-bottom: 16px;
        }

        .daily-filter-grid {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .daily-filter-grid .filter-col {
            flex: 1;
            min-width: 220px;
        }

        .daily-filter-grid .form-control {
            height: 38px !important;
            padding: 6px 12px !important;
            font-size: 12.5px !important;
            line-height: 1.4 !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 8px !important;
            box-sizing: border-box !important;
            background-color: #ffffff !important;
            color: #0f172a !important;
            font-weight: 500 !important;
        }

        .daily-filter-grid .form-control:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.15) !important;
            outline: none !important;
        }

        .daily-kpi-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 12px;
            transition: all 0.3s ease;
        }

        .daily-kpi-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 16px;
            box-shadow: 0 2px 6px -1px rgba(0,0,0,0.03);
            display: flex;
            align-items: center;
            gap: 14px;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease, opacity 0.25s ease;
            cursor: pointer;
            user-select: none;
            position: relative;
        }

        .daily-kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px -2px rgba(37,99,235,0.12);
            border-color: #93c5fd;
        }

        .daily-kpi-card:active {
            transform: translateY(0);
        }

        .daily-kpi-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .daily-filter-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            background: #ffffff;
            padding: 10px 16px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 6px -1px rgba(0,0,0,0.03);
            margin-bottom: 16px;
        }

        .btn-tab-filter {
            padding: 7px 15px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            border: 1.5px solid #cbd5e1;
            background: #f8fafc;
            color: #475569;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
        }

        .btn-tab-filter:hover {
            background: #f1f5f9;
            color: #0f172a;
            border-color: #94a3b8;
        }

        .btn-tab-filter.active {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
            box-shadow: 0 2px 6px rgba(37,99,235,0.25);
        }

        .btn-toggle-kpi {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 11.5px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #475569;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
        }

        .btn-toggle-kpi:hover {
            background: #f1f5f9;
            color: #0f172a;
            border-color: #94a3b8;
        }

        @keyframes targetSectionHighlight {
            0% {
                box-shadow: 0 0 0 0 rgba(37,99,235,0);
                border-color: #e2e8f0;
            }
            25% {
                box-shadow: 0 0 0 4px rgba(37,99,235,0.35);
                border-color: #2563eb;
            }
            100% {
                box-shadow: 0 2px 6px -1px rgba(0,0,0,0.03);
                border-color: #e2e8f0;
            }
        }

        .section-highlight {
            animation: targetSectionHighlight 2s ease forwards;
        }

        .daily-section-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #ffffff;
            box-shadow: 0 2px 6px -1px rgba(0,0,0,0.03);
            overflow: hidden;
            margin-bottom: 18px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .daily-section-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .daily-table th {
            padding: 10px 14px !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.4px !important;
            color: #475569 !important;
            background: #fafafa !important;
            border-bottom: 1.5px solid #e2e8f0 !important;
            white-space: nowrap !important;
        }

        .daily-table td {
            padding: 10px 14px !important;
            vertical-align: middle !important;
            font-size: 12.5px !important;
            border-bottom: 1px solid #f1f5f9 !important;
            color: #1e293b;
        }

        .category-sub-bar {
            background: #f8fafc;
            padding: 8px 16px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .qty-badge-in {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
            padding: 2px 8px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 11.5px;
        }

        .qty-badge-out {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            padding: 2px 8px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 11.5px;
        }

        .qty-badge-balance {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            padding: 3px 9px;
            border-radius: 6px;
            font-weight: 800;
            font-size: 12px;
        }

        .daily-empty-box {
            text-align: center;
            padding: 32px 20px;
            color: #94a3b8;
        }

        .daily-empty-box i {
            font-size: 24px;
            color: #cbd5e1;
            display: block;
            margin-bottom: 6px;
        }

        @media (max-width: 1200px) {
            .daily-kpi-grid {
                grid-template-columns: repeat(3, 1fr) !important;
            }
        }

        @media (max-width: 768px) {
            .daily-kpi-grid {
                grid-template-columns: repeat(2, 1fr) !important;
            }
            .daily-page-header {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 10px;
            }
        }

        @media (max-width: 480px) {
            .daily-kpi-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
    @endpush

    @php
        $carbonDate = \Carbon\Carbon::parse($date);
        $prevDate = $carbonDate->copy()->subDay()->toDateString();
        $nextDate = $carbonDate->copy()->addDay()->toDateString();
        $isToday = $date === date('Y-m-d');
        $opnameCount = isset($opnameAdjustments) ? $opnameAdjustments->count() : 0;
    @endphp

    {{-- ==================== Header & Action Bar ==================== --}}
    <div class="daily-page-header">
        <div>
            <h2 class="fw-700" style="font-size:18px;color:#0f172a;margin:0;display:flex;align-items:center;gap:8px;">
                <i class="fas fa-calendar-check text-primary" style="font-size:18px;"></i>
                Log Harian Logistik dan Aktivitas Proyek
            </h2>
            <p class="text-muted" style="font-size:12.5px;margin-top:3px;margin-bottom:0;">
                Rekap mutasi barang masuk, pemakaian material lapangan hari ini, pergerakan alat kerja, dan neraca sisa stok material hari ini.
            </p>
        </div>
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <a href="{{ route('daily-log.print', ['warehouse_id' => $selectedWarehouse->id, 'date' => $date]) }}" target="_blank" 
               class="btn btn-sm btn-primary" style="height:36px;padding:0 14px;border-radius:8px;font-size:12.5px;font-weight:700;display:inline-flex;align-items:center;gap:6px;box-shadow:0 2px 6px rgba(37,99,235,0.25);">
                <i class="fas fa-print"></i> Cetak Laporan
            </a>
        </div>
    </div>

    {{-- ==================== Filter Card (Gudang & Navigasi Tanggal Cepat) ==================== --}}
    <div class="daily-filter-card">
        <form method="GET" action="{{ route('daily-log.index') }}" class="daily-filter-grid">
            {{-- Gudang Selector --}}
            <div class="filter-col" style="flex:2;min-width:260px;">
                <label class="form-label" style="font-size:11px;font-weight:700;color:#475569;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.3px;">
                    Gudang / Site Proyek
                </label>
                <div style="position:relative;">
                    <i class="fas fa-warehouse" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:12px;color:#94a3b8;pointer-events:none;"></i>
                    <select name="warehouse_id" class="form-control" onchange="this.form.submit()" style="padding-left:32px !important;">
                        @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ $selectedWarehouse->id == $wh->id ? 'selected' : '' }}>
                            {{ $wh->name }} {{ $wh->is_central ? '(Pusat)' : '(Proyek)' }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Tanggal Picker dengan Quick Navigasi (Kemarin, Hari Ini, Besok) --}}
            <div class="filter-col" style="flex:1.5;min-width:280px;">
                <label class="form-label" style="font-size:11px;font-weight:700;color:#475569;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.3px;">
                    Tanggal Log
                </label>
                <div style="display:flex;align-items:center;gap:6px;">
                    <div style="display:inline-flex;align-items:center;background:#f8fafc;border:1.5px solid #cbd5e1;border-radius:8px;padding:2px;gap:2px;">
                        <a href="{{ route('daily-log.index', ['warehouse_id' => $selectedWarehouse->id, 'date' => $prevDate]) }}" 
                           class="btn-tab-filter" style="border:none;background:transparent;padding:4px 9px;font-size:11px;color:#475569;" title="Kemarin">
                            <i class="fas fa-chevron-left"></i> H-1
                        </a>
                        <a href="{{ route('daily-log.index', ['warehouse_id' => $selectedWarehouse->id, 'date' => date('Y-m-d')]) }}" 
                           class="btn-tab-filter {{ $isToday ? 'active' : '' }}" style="border:none;padding:4px 10px;font-size:11px;" title="Hari Ini">
                            Hari Ini
                        </a>
                        <a href="{{ route('daily-log.index', ['warehouse_id' => $selectedWarehouse->id, 'date' => $nextDate]) }}" 
                           class="btn-tab-filter" style="border:none;background:transparent;padding:4px 9px;font-size:11px;color:#475569;" title="Besok">
                            H+1 <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                    <input type="date" name="date" value="{{ $date }}" class="form-control" onchange="this.form.submit()" style="flex:1;min-width:130px;">
                </div>
            </div>

            <div style="display:flex;gap:6px;">
                <button type="submit" class="btn btn-primary" style="height:38px;padding:0 16px;border-radius:8px;font-size:12.5px;font-weight:700;display:inline-flex;align-items:center;gap:6px;">
                    <i class="fas fa-arrows-rotate"></i> Muat Data
                </button>
            </div>
        </form>
    </div>

    {{-- ==================== KPI Metric Cards (5 Cards) ==================== --}}
    {{-- Saat user klik filter Khusus Material / Alat, kartu di sini otomatis disaring/disembunyikan sesuai kategori --}}
    <div class="daily-kpi-grid mb-3" id="dailyKpiGridContainer">
        {{-- 1. Material Masuk --}}
        <div class="daily-kpi-card" data-kpi-category="material" onclick="goToSection('section-material-masuk', 'material')" title="Klik untuk menuju ke Penerimaan Material Masuk">
            <div class="daily-kpi-icon" style="background:#eff6ff;color:#2563eb;">
                <i class="fas fa-truck-ramp-box"></i>
            </div>
            <div>
                <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.4px;">Material Masuk</div>
                <div style="display:flex;align-items:baseline;gap:5px;margin-top:2px;">
                    <span style="font-size:22px;font-weight:800;color:#0f172a;line-height:1;">{{ $totalItemsIn }}</span>
                    <span style="font-size:11.5px;color:#64748b;font-weight:700;">Item</span>
                </div>
                <div class="text-muted" style="font-size:11px;margin-top:2px;">Surat jalan & GR diterima</div>
            </div>
        </div>

        {{-- 2. Material Dipakai --}}
        <div class="daily-kpi-card" data-kpi-category="material" onclick="goToSection('section-material-dipakai', 'material')" title="Klik untuk menuju ke Pemakaian Material Lapangan">
            <div class="daily-kpi-icon" style="background:#fff7ed;color:#ea580c;">
                <i class="fas fa-dolly"></i>
            </div>
            <div>
                <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.4px;">Material Dipakai</div>
                <div style="display:flex;align-items:baseline;gap:5px;margin-top:2px;">
                    <span style="font-size:22px;font-weight:800;color:#0f172a;line-height:1;">{{ $totalItemsOut }}</span>
                    <span style="font-size:11.5px;color:#64748b;font-weight:700;">Item</span>
                </div>
                <div class="text-muted" style="font-size:11px;margin-top:2px;">Dikeluarkan ke lapangan</div>
            </div>
        </div>

        {{-- 3. Alat Ready --}}
        <div class="daily-kpi-card" data-kpi-category="tool" onclick="goToSection('section-kesiapan-alat', 'tool')" title="Klik untuk menuju ke Kesiapan Alat Kerja di Gudang">
            <div class="daily-kpi-icon" style="background:#ecfdf5;color:#059669;">
                <i class="fas fa-screwdriver-wrench"></i>
            </div>
            <div>
                <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.4px;">Alat Ready</div>
                <div style="display:flex;align-items:baseline;gap:5px;margin-top:2px;">
                    <span style="font-size:22px;font-weight:800;color:#0f172a;line-height:1;">{{ $totalToolsReady }}</span>
                    <span style="font-size:11.5px;color:#64748b;font-weight:700;">Unit</span>
                </div>
                <div class="text-muted" style="font-size:11px;margin-top:2px;">Standby di gudang</div>
            </div>
        </div>

        {{-- 4. Alat di Lapangan --}}
        <div class="daily-kpi-card" data-kpi-category="tool" onclick="goToSection('section-alat-lapangan', 'tool')" title="Klik untuk menuju ke Pantauan Alat di Lapangan">
            <div class="daily-kpi-icon" style="background:#f5f3ff;color:#7c3aed;">
                <i class="fas fa-hand-holding"></i>
            </div>
            <div>
                <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.4px;">Alat di Lapangan</div>
                <div style="display:flex;align-items:baseline;gap:5px;margin-top:2px;">
                    <span style="font-size:22px;font-weight:800;color:#0f172a;line-height:1;">{{ $totalToolsInUse }}</span>
                    <span style="font-size:11.5px;color:#64748b;font-weight:700;">Unit</span>
                </div>
                <div class="text-muted" style="font-size:11px;margin-top:2px;">Sedang aktif dipinjam</div>
            </div>
        </div>

        {{-- 5. Alat Rusak / Servis --}}
        <div class="daily-kpi-card" data-kpi-category="tool" onclick="goToSection('section-kesiapan-alat', 'tool')" title="Klik untuk menuju ke Alat Perlu Servis / Rusak">
            <div class="daily-kpi-icon" style="background:#fef2f2;color:{{ $totalToolsDamaged > 0 ? '#dc2626' : '#94a3b8' }};">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <div>
                <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.4px;">Alat Rusak / Servis</div>
                <div style="display:flex;align-items:baseline;gap:5px;margin-top:2px;">
                    <span style="font-size:22px;font-weight:800;color:{{ $totalToolsDamaged > 0 ? '#dc2626' : '#0f172a' }};line-height:1;">{{ $totalToolsDamaged }}</span>
                    <span style="font-size:11.5px;color:#64748b;font-weight:700;">Unit</span>
                </div>
                <div class="text-muted" style="font-size:11px;margin-top:2px;">Perlu perbaikan</div>
            </div>
        </div>
    </div>

    {{-- ==================== Filter Tab Bar (Material vs Alat Kerja) ==================== --}}
    <div class="daily-filter-bar">
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <span style="font-size:12px;font-weight:700;color:#475569;margin-right:2px;display:inline-flex;align-items:center;gap:6px;">
                <i class="fas fa-filter text-primary" style="font-size:12px;"></i> Filter Bagian:
            </span>
            <button type="button" class="btn-tab-filter active" data-filter="all" onclick="filterSections('all')">
                <i class="fas fa-layer-group"></i> Semua Bagian
            </button>
            <button type="button" class="btn-tab-filter" data-filter="material" onclick="filterSections('material')">
                <i class="fas fa-boxes-stacked"></i> Khusus Material
            </button>
            <button type="button" class="btn-tab-filter" data-filter="tool" onclick="filterSections('tool')">
                <i class="fas fa-screwdriver-wrench"></i> Khusus Alat Kerja
            </button>
        </div>

        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            {{-- Tombol Toggle Sembunyikan / Tampilkan Seluruh Kartu di Atas --}}
            <button type="button" id="btnToggleKpiCards" onclick="toggleAllKpiCards()" class="btn-toggle-kpi" title="Sembunyikan / Tampilkan kartu metrik di atas">
                <i class="fas fa-eye-slash" id="toggleKpiIcon"></i>
                <span id="toggleKpiText">Sembunyikan Kartu</span>
            </button>

            <div class="text-muted" id="filterHelpText" style="font-size:11px;">
                <i class="fas fa-mouse-pointer me-1 text-primary"></i> Klik kartu metrik di atas untuk otomatis menuju ke tabel rincian terkait.
            </div>
        </div>
    </div>

    {{-- ==================== SECTION 1: PEMAKAIAN MATERIAL LAPANGAN ==================== --}}
    <div class="daily-section-card" id="section-material-dipakai" data-category="material">
        <div class="daily-section-header">
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="width:26px;height:26px;border-radius:8px;background:#eff6ff;color:#2563eb;font-weight:800;font-size:12px;display:inline-flex;align-items:center;justify-content:center;">1</span>
                <div>
                    <span style="font-size:13.5px;font-weight:700;color:#1e293b;">Pemakaian Material Lapangan Hari Ini (Outgoing)</span>
                    <div class="text-muted" style="font-size:11px;">Bon pengeluaran barang yang telah diserahkan ke mandor/pekerja</div>
                </div>
            </div>
            <span class="badge" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;font-size:11px;padding:3px 9px;border-radius:12px;font-weight:700;">
                {{ $usages->count() }} Bon Pengeluaran
            </span>
        </div>
        <div class="table-wrap">
            <table class="data-table daily-table mb-0">
                <thead>
                    <tr>
                        <th style="width:150px;">No. Bon</th>
                        <th style="width:190px;">Penerima (Mandor/Tukang)</th>
                        <th style="min-width:170px;">Bagian Pekerjaan / Zona</th>
                        <th>Material & Kuantitas yang Dikeluarkan</th>
                        <th style="width:140px;">Petugas Gudang</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usages as $usage)
                    <tr>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('material-usages.show', $usage) }}" style="color:#2563eb;font-weight:700;font-size:12.5px;text-decoration:none;display:inline-flex;align-items:center;gap:5px;">
                                <i class="fas fa-file-invoice text-primary" style="font-size:11px;"></i>
                                {{ $usage->usage_number }}
                            </a>
                        </td>
                        <td>
                            <div class="fw-700" style="color:#0f172a;font-size:12.5px;">{{ $usage->recipient_name }}</div>
                        </td>
                        <td>
                            <span class="badge" style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;font-size:11px;padding:3px 8px;border-radius:6px;font-weight:600;">
                                {{ $usage->job_section ?? 'Pekerjaan Umum' }}
                            </span>
                        </td>
                        <td>
                            <div style="display:flex;flex-direction:column;gap:4px;">
                                @foreach($usage->items as $uItem)
                                <div style="font-size:12px;line-height:1.4;display:flex;align-items:center;gap:6px;">
                                    <span class="qty-badge-out">-{{ format_quantity($uItem->quantity) }} {{ $uItem->material?->unit?->abbreviation ?? $uItem->custom_item_unit ?? 'unit' }}</span>
                                    <span style="color:#1e293b;font-weight:600;">{{ $uItem->material?->name ?? $uItem->custom_item_name }}</span>
                                    @if($uItem->notes)
                                    <span class="text-muted" style="font-size:11px;">({{ $uItem->notes }})</span>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </td>
                        <td>
                            <span class="text-muted" style="font-size:12px;">{{ $usage->issuedBy?->name ?? '-' }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="daily-empty-box">
                            <i class="fas fa-box-open"></i>
                            Tidak ada pengeluaran / pemakaian material pada tanggal {{ $carbonDate->translatedFormat('d F Y') }}.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ==================== SECTION 2: MATERIAL MASUK ==================== --}}
    <div class="daily-section-card" id="section-material-masuk" data-category="material">
        <div class="daily-section-header">
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="width:26px;height:26px;border-radius:8px;background:#ecfdf5;color:#047857;font-weight:800;font-size:12px;display:inline-flex;align-items:center;justify-content:center;">2</span>
                <div>
                    <span style="font-size:13.5px;font-weight:700;color:#1e293b;">Penerimaan Material Masuk (Incoming)</span>
                    <div class="text-muted" style="font-size:11px;">Kiriman dari Gudang Pusat (Surat Jalan) maupun Supplier (Goods Receipt)</div>
                </div>
            </div>
            <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:11px;padding:3px 9px;border-radius:12px;font-weight:700;">
                {{ $incomingDistributions->count() + $incomingGoodsReceipts->count() }} Dokumen Penerimaan
            </span>
        </div>
        <div class="table-wrap">
            <table class="data-table daily-table mb-0">
                <thead>
                    <tr>
                        <th style="width:160px;">No. Surat Jalan / GR</th>
                        <th style="width:190px;">Sumber Pengirim</th>
                        <th>Material & Kuantitas Diterima</th>
                        <th style="width:130px;text-align:center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php $hasIncoming = false; @endphp
                    @foreach($incomingDistributions as $dist)
                    @php $hasIncoming = true; @endphp
                    <tr>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('distributions.show', $dist) }}" style="color:#2563eb;font-weight:700;font-size:12.5px;text-decoration:none;display:inline-flex;align-items:center;gap:5px;">
                                <i class="fas fa-truck-fast text-primary" style="font-size:11px;"></i>
                                {{ $dist->distribution_number }}
                            </a>
                        </td>
                        <td>
                            <div class="fw-700" style="color:#0f172a;font-size:12.5px;">{{ $dist->fromWarehouse?->name ?? 'Gudang Pusat' }}</div>
                            @if($dist->driver_name)
                            <div class="text-muted" style="font-size:11px;">Supir: {{ $dist->driver_name }}</div>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;flex-direction:column;gap:4px;">
                                @foreach($dist->items as $dItem)
                                <div style="font-size:12px;line-height:1.4;display:flex;align-items:center;gap:6px;">
                                    <span class="qty-badge-in">+{{ format_quantity($dItem->qty_received) }} {{ $dItem->material?->unit?->abbreviation ?? $dItem->custom_item_unit ?? 'unit' }}</span>
                                    <span style="color:#1e293b;font-weight:600;">{{ $dItem->material?->name ?? $dItem->tool?->name ?? $dItem->custom_item_name }}</span>
                                </div>
                                @endforeach
                            </div>
                        </td>
                        <td style="text-align:center;">
                            <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:11px;padding:3px 8px;border-radius:12px;font-weight:700;">
                                <i class="fas fa-check me-1"></i> Diterima di Site
                            </span>
                        </td>
                    </tr>
                    @endforeach

                    @foreach($incomingGoodsReceipts as $gr)
                    @php $hasIncoming = true; @endphp
                    <tr>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('goods-receipts.show', $gr) }}" style="color:#2563eb;font-weight:700;font-size:12.5px;text-decoration:none;display:inline-flex;align-items:center;gap:5px;">
                                <i class="fas fa-receipt text-primary" style="font-size:11px;"></i>
                                {{ $gr->receipt_number }}
                            </a>
                        </td>
                        <td>
                            <div class="fw-700" style="color:#0f172a;font-size:12.5px;">{{ $gr->supplier?->name ?? 'Supplier Langsung' }}</div>
                            @if($gr->supplier_do_number ?? $gr->invoice_number)
                            <div class="text-muted" style="font-size:11px;">Ref: {{ $gr->supplier_do_number ?? $gr->invoice_number }}</div>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;flex-direction:column;gap:4px;">
                                @foreach($gr->items as $gItem)
                                <div style="font-size:12px;line-height:1.4;display:flex;align-items:center;gap:6px;">
                                    <span class="qty-badge-in">+{{ format_quantity($gItem->quantity_received ?? $gItem->qty_received) }} {{ $gItem->material?->unit?->abbreviation ?? 'unit' }}</span>
                                    <span style="color:#1e293b;font-weight:600;">{{ $gItem->material?->name }}</span>
                                </div>
                                @endforeach
                            </div>
                        </td>
                        <td style="text-align:center;">
                            <span class="badge" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;font-size:11px;padding:3px 8px;border-radius:12px;font-weight:700;">
                                <i class="fas fa-check-circle me-1"></i> Masuk Gudang
                            </span>
                        </td>
                    </tr>
                    @endforeach

                    @if(!$hasIncoming)
                    <tr>
                        <td colspan="4" class="daily-empty-box">
                            <i class="fas fa-truck"></i>
                            Tidak ada penerimaan barang masuk pada tanggal {{ $carbonDate->translatedFormat('d F Y') }}.
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- ==================== SECTION 3: DISTRIBUSI KELUAR ==================== --}}
    <div class="daily-section-card" id="section-distribusi-keluar" data-category="material">
        <div class="daily-section-header">
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="width:26px;height:26px;border-radius:8px;background:#f5f3ff;color:#7c3aed;font-weight:800;font-size:12px;display:inline-flex;align-items:center;justify-content:center;">3</span>
                <div>
                    <span style="font-size:13.5px;font-weight:700;color:#1e293b;">Distribusi Keluar ke Proyek / Gudang Lain</span>
                    <div class="text-muted" style="font-size:11px;">Pengiriman surat jalan keluar dari {{ $selectedWarehouse->name }}</div>
                </div>
            </div>
            <span class="badge" style="background:#f5f3ff;color:#7c3aed;border:1px solid #ddd6fe;font-size:11px;padding:3px 9px;border-radius:12px;font-weight:700;">
                {{ $outgoingDistributions->count() }} Pengiriman
            </span>
        </div>
        <div class="table-wrap">
            <table class="data-table daily-table mb-0">
                <thead>
                    <tr>
                        <th style="width:160px;">No. Surat Jalan</th>
                        <th style="width:190px;">Tujuan Pengiriman</th>
                        <th style="width:115px;text-align:center;">Tgl Kirim</th>
                        <th>Material & Kuantitas Dikirim</th>
                        <th style="width:130px;text-align:center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($outgoingDistributions as $dist)
                    <tr>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('distributions.show', $dist) }}" style="color:#2563eb;font-weight:700;font-size:12.5px;text-decoration:none;display:inline-flex;align-items:center;gap:5px;">
                                <i class="fas fa-paper-plane text-primary" style="font-size:11px;"></i>
                                {{ $dist->distribution_number }}
                            </a>
                        </td>
                        <td>
                            <div class="fw-700" style="color:#0f172a;font-size:12.5px;">{{ $dist->toWarehouse?->name ?? 'Gudang Lain' }}</div>
                            @if($dist->driver_name)
                            <div class="text-muted" style="font-size:11px;">Supir: {{ $dist->driver_name }}</div>
                            @endif
                        </td>
                        <td style="text-align:center;font-size:12px;color:#475569;">
                            {{ $dist->shipped_at ? \Carbon\Carbon::parse($dist->shipped_at)->format('d/m/Y') : '—' }}
                        </td>
                        <td>
                            <div style="display:flex;flex-direction:column;gap:4px;">
                                @foreach($dist->items as $dItem)
                                <div style="font-size:12px;line-height:1.4;display:flex;align-items:center;gap:6px;">
                                    <span class="qty-badge-out">-{{ format_quantity($dItem->qty_shipped) }} {{ $dItem->material?->unit?->abbreviation ?? $dItem->custom_item_unit ?? 'unit' }}</span>
                                    <span style="color:#1e293b;font-weight:600;">{{ $dItem->material?->name ?? $dItem->tool?->name ?? $dItem->custom_item_name }}</span>
                                </div>
                                @endforeach
                            </div>
                        </td>
                        <td style="text-align:center;">
                            @if($dist->status === 'received')
                                <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:11px;padding:3px 8px;border-radius:12px;font-weight:700;">Diterima</span>
                            @else
                                <span class="badge" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;font-size:11px;padding:3px 8px;border-radius:12px;font-weight:700;">Dalam Perjalanan</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="daily-empty-box">
                            <i class="fas fa-route"></i>
                            Tidak ada distribusi pengiriman keluar pada tanggal {{ $carbonDate->translatedFormat('d F Y') }}.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ==================== SECTION 4: RETUR MATERIAL ==================== --}}
    <div class="daily-section-card" id="section-retur-material" data-category="material">
        <div class="daily-section-header">
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="width:26px;height:26px;border-radius:8px;background:#fffbeb;color:#b45309;font-weight:800;font-size:12px;display:inline-flex;align-items:center;justify-content:center;">4</span>
                <div>
                    <span style="font-size:13.5px;font-weight:700;color:#1e293b;">Retur Material dari Lapangan</span>
                    <div class="text-muted" style="font-size:11px;">Pengembalian sisa material proyek kembali ke gudang</div>
                </div>
            </div>
            <span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-size:11px;padding:3px 9px;border-radius:12px;font-weight:700;">
                {{ $materialReturns->count() }} Dokumen Retur
            </span>
        </div>
        <div class="table-wrap">
            <table class="data-table daily-table mb-0">
                <thead>
                    <tr>
                        <th style="width:150px;">No. Retur</th>
                        <th style="width:190px;">Sumber Retur</th>
                        <th style="width:115px;text-align:center;">Tgl Terima</th>
                        <th>Material & Kuantitas Diterima</th>
                        <th style="width:140px;text-align:center;">Alasan Retur</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($materialReturns as $ret)
                    <tr>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('returns.show', $ret) }}" style="color:#2563eb;font-weight:700;font-size:12.5px;text-decoration:none;display:inline-flex;align-items:center;gap:5px;">
                                <i class="fas fa-rotate-left text-warning" style="font-size:11px;"></i>
                                {{ $ret->return_number }}
                            </a>
                        </td>
                        <td>
                            <div class="fw-700" style="color:#0f172a;font-size:12.5px;">{{ $ret->fromWarehouse?->name ?? 'Lapangan Proyek' }}</div>
                            <div class="text-muted" style="font-size:11px;">Penerima: {{ $ret->receiver?->name ?? '-' }}</div>
                        </td>
                        <td style="text-align:center;font-size:12px;color:#475569;">
                            {{ $ret->received_at ? \Carbon\Carbon::parse($ret->received_at)->format('d/m/Y') : '—' }}
                        </td>
                        <td>
                            <div style="display:flex;flex-direction:column;gap:4px;">
                                @foreach($ret->items as $rItem)
                                <div style="font-size:12px;line-height:1.4;display:flex;align-items:center;gap:6px;">
                                    <span class="qty-badge-in">+{{ format_quantity($rItem->received_qty) }} {{ $rItem->material?->unit?->abbreviation ?? 'unit' }}</span>
                                    <span style="color:#1e293b;font-weight:600;">{{ $rItem->material?->name }}</span>
                                    <span class="text-muted" style="font-size:11px;">({{ ucfirst($rItem->condition ?? 'Baik') }})</span>
                                </div>
                                @endforeach
                            </div>
                        </td>
                        <td style="text-align:center;">
                            <span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-size:11px;padding:3px 8px;border-radius:12px;">
                                {{ $ret->reason_label ?? $ret->getReasonLabel() ?? 'Retur Sisa' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="daily-empty-box">
                            <i class="fas fa-arrow-rotate-left"></i>
                            Tidak ada retur material diterima pada tanggal {{ $carbonDate->translatedFormat('d F Y') }}.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ==================== SECTION 5: PANTAUAN ALAT KERJA ==================== --}}
    <div class="daily-section-card" id="section-alat-lapangan" data-category="tool">
        <div class="daily-section-header">
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="width:26px;height:26px;border-radius:8px;background:#f5f3ff;color:#6d28d9;font-weight:800;font-size:12px;display:inline-flex;align-items:center;justify-content:center;">5</span>
                <div>
                    <span style="font-size:13.5px;font-weight:700;color:#1e293b;">Pantauan Alat Kerja di Lapangan (Active Tools)</span>
                    <div class="text-muted" style="font-size:11px;">Daftar alat operasional yang saat ini sedang dipinjam pekerja di lapangan</div>
                </div>
            </div>
            <span class="badge" style="background:#f5f3ff;color:#6d28d9;border:1px solid #ddd6fe;font-size:11px;padding:3px 9px;border-radius:12px;font-weight:700;">
                {{ $toolAssignments->count() }} Peminjaman Aktif
            </span>
        </div>
        <div class="table-wrap">
            <table class="data-table daily-table mb-0">
                <thead>
                    <tr>
                        <th>Nama Alat & Kode</th>
                        <th style="width:85px;text-align:center;">Qty</th>
                        <th>Peminjam (Mandor/Tukang)</th>
                        <th>Lokasi / Zona Kerja</th>
                        <th style="width:110px;text-align:center;">Tgl Pinjam</th>
                        <th style="width:115px;text-align:center;">Target Kembali</th>
                        <th style="width:130px;text-align:center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($toolAssignments as $ta)
                    <tr>
                        <td>
                            <div class="fw-700" style="color:#0f172a;font-size:12.5px;">{{ $ta->tool?->name }}</div>
                            <div class="text-muted" style="font-size:11px;margin-top:2px;">
                                @if($ta->tool?->code)
                                    <code style="background:#f1f5f9;color:#475569;padding:1px 5px;border-radius:4px;font-size:10px;">{{ $ta->tool->code }}</code>
                                @endif
                            </div>
                        </td>
                        <td style="text-align:center;">
                            <strong style="font-size:12.5px;color:#0f172a;">{{ $ta->quantity ?? 1 }} Unit</strong>
                        </td>
                        <td>
                            <div class="fw-700" style="color:#0f172a;font-size:12.5px;">{{ $ta->borrower_display }}</div>
                            @if($ta->borrower_phone)
                            <div class="text-muted" style="font-size:11px;">Telp: {{ $ta->borrower_phone }}</div>
                            @endif
                        </td>
                        <td>
                            <span style="color:#334155;font-size:12px;">{{ $ta->location_display ?: 'Zona Lapangan' }}</span>
                        </td>
                        <td style="text-align:center;font-size:12px;color:#475569;">
                            {{ $ta->assigned_at ? \Carbon\Carbon::parse($ta->assigned_at)->format('d/m/Y') : '-' }}
                        </td>
                        <td style="text-align:center;font-size:12px;">
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
                                <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:11px;padding:3px 8px;border-radius:12px;font-weight:700;">Dikembalikan</span>
                            @elseif($ta->status === 'overdue' || ($ta->status === 'active' && $ta->expected_return_at && \Carbon\Carbon::parse($ta->expected_return_at)->isPast()))
                                <span class="badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-size:11px;padding:3px 8px;border-radius:12px;font-weight:700;">Terlambat</span>
                            @else
                                <span class="badge" style="background:#f5f3ff;color:#6d28d9;border:1px solid #ddd6fe;font-size:11px;padding:3px 8px;border-radius:12px;font-weight:700;">Dipinjam</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="daily-empty-box">
                            <i class="fas fa-screwdriver-wrench"></i>
                            Tidak ada alat kerja yang sedang dipinjam di lapangan pada tanggal ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ==================== SECTION 6: NERACA SISA STOK MATERIAL ==================== --}}
    <div class="daily-section-card" id="section-neraca-material" data-category="material">
        <div class="daily-section-header">
            <div>
                <div style="display:flex;align-items:center;gap:10px;">
                    <span style="width:26px;height:26px;border-radius:8px;background:#eff6ff;color:#2563eb;font-weight:800;font-size:12px;display:inline-flex;align-items:center;justify-content:center;">6</span>
                    <span style="font-size:13.5px;font-weight:700;color:#1e293b;">Neraca Sisa Stok Material Hari Ini (Stock Balance)</span>
                </div>
                <div class="text-muted" style="font-size:11.5px;margin-top:2px;">
                    Rumus kalkulasi: Stok Awal + Mutasi Masuk (+) - Pemakaian Keluar (-) = Sisa Stok Akhir
                </div>
            </div>
            <span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-size:11px;padding:3px 9px;border-radius:12px;font-weight:700;">
                {{ $stockBalance->count() }} Material Aktif
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
                    <span class="badge" style="background:#ffffff;color:#475569;border:1px solid #cbd5e1;font-size:10.5px;padding:2px 7px;border-radius:6px;font-weight:600;">
                        {{ $rows->count() }} Jenis Material
                    </span>
                </div>
                <div class="table-wrap">
                    <table class="data-table daily-table mb-0">
                        <thead>
                            <tr>
                                <th style="width:40px;text-align:center;">#</th>
                                <th>Nama Material</th>
                                <th style="width:115px;text-align:right;">Stok Awal</th>
                                <th style="width:115px;text-align:right;">Masuk (+)</th>
                                <th style="width:115px;text-align:right;">Dipakai (-)</th>
                                <th style="width:130px;text-align:right;">Sisa Akhir</th>
                                <th style="width:80px;text-align:center;">Satuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $idx => $stk)
                            <tr>
                                <td style="text-align:center;color:#94a3b8;font-size:11px;">{{ $idx + 1 }}</td>
                                <td>
                                    <div class="fw-700" style="color:#0f172a;font-size:12.5px;">{{ $stk->material_name }}</div>
                                    @if($stk->material_code)
                                    <div class="text-muted" style="font-size:10.5px;margin-top:2px;"><code>{{ $stk->material_code }}</code></div>
                                    @endif
                                </td>
                                <td style="text-align:right;color:#475569;font-size:12px;font-weight:600;">{{ format_quantity($stk->opening_stock) }}</td>
                                <td style="text-align:right;">
                                    @if($stk->qty_in > 0)
                                        <span class="qty-badge-in">+{{ format_quantity($stk->qty_in) }}</span>
                                    @else
                                        <span style="color:#cbd5e1;">-</span>
                                    @endif
                                </td>
                                <td style="text-align:right;">
                                    @if($stk->qty_out > 0)
                                        <span class="qty-badge-out">-{{ format_quantity($stk->qty_out) }}</span>
                                    @else
                                        <span style="color:#cbd5e1;">-</span>
                                    @endif
                                </td>
                                <td style="text-align:right;">
                                    <span class="qty-badge-balance">
                                        {{ format_quantity($stk->closing_stock) }}
                                    </span>
                                </td>
                                <td style="text-align:center;color:#64748b;font-size:11.5px;font-weight:600;">{{ $stk->unit }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endforeach
            @else
                <div class="daily-empty-box">
                    <i class="fas fa-layer-group"></i>
                    Tidak ada transaksi mutasi material pada tanggal {{ $carbonDate->translatedFormat('d F Y') }}.
                </div>
            @endif
        </div>
    </div>

    {{-- ==================== SECTION 7: KESIAPAN ALAT KERJA ==================== --}}
    <div class="daily-section-card" id="section-kesiapan-alat" data-category="tool">
        <div class="daily-section-header">
            <div>
                <div style="display:flex;align-items:center;gap:10px;">
                    <span style="width:26px;height:26px;border-radius:8px;background:#ecfdf5;color:#047857;font-weight:800;font-size:12px;display:inline-flex;align-items:center;justify-content:center;">7</span>
                    <span style="font-size:13.5px;font-weight:700;color:#1e293b;">Neraca Posisi & Kesiapan Alat Kerja (Tool Availability)</span>
                </div>
                <div class="text-muted" style="font-size:11.5px;margin-top:2px;">
                    Pantauan ketersediaan unit di gudang vs peminjaman aktif di lapangan
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:11px;padding:3px 8px;border-radius:12px;font-weight:700;">{{ $totalToolsReady }} Ready</span>
                <span class="badge" style="background:#f5f3ff;color:#7c3aed;border:1px solid #ddd6fe;font-size:11px;padding:3px 8px;border-radius:12px;font-weight:700;">{{ $totalToolsInUse }} di Lapangan</span>
                @if($totalToolsDamaged > 0)
                <span class="badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-size:11px;padding:3px 8px;border-radius:12px;font-weight:700;">{{ $totalToolsDamaged }} Rusak/Servis</span>
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
                    <span class="badge" style="background:#ffffff;color:#475569;border:1px solid #cbd5e1;font-size:10.5px;padding:2px 7px;border-radius:6px;font-weight:600;">
                        {{ $rows->count() }} Jenis Alat
                    </span>
                </div>
                <div class="table-wrap">
                    <table class="data-table daily-table mb-0">
                        <thead>
                            <tr>
                                <th style="width:40px;text-align:center;">#</th>
                                <th>Nama Alat Kerja</th>
                                <th style="width:100px;text-align:center;">Total Unit</th>
                                <th style="width:105px;text-align:center;">Ready (Gudang)</th>
                                <th style="width:105px;text-align:center;">Di Lapangan</th>
                                <th style="width:105px;text-align:center;">Servis / Rusak</th>
                                <th>Mandor Pemegang / Posisi</th>
                                <th style="width:130px;text-align:center;">Kesiapan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $tIdx => $tb)
                            <tr>
                                <td style="text-align:center;color:#94a3b8;font-size:11px;">{{ $tIdx + 1 }}</td>
                                <td>
                                    <div class="fw-700" style="color:#0f172a;font-size:12.5px;">{{ $tb->tool_name }}</div>
                                    <div class="text-muted" style="font-size:10.5px;margin-top:2px;">
                                        @if($tb->tool_code)
                                            <code>{{ $tb->tool_code }}</code>
                                        @endif
                                        @if($tb->brand)
                                            <span>• Merk: {{ $tb->brand }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td style="text-align:center;">
                                    <strong style="font-size:12.5px;color:#0f172a;">{{ $tb->stock_total }} Unit</strong>
                                </td>
                                <td style="text-align:center;">
                                    <span style="font-weight:700;font-size:12px;color:{{ $tb->stock_available > 0 ? '#059669' : '#94a3b8' }};">
                                        {{ $tb->stock_available }} Unit
                                    </span>
                                </td>
                                <td style="text-align:center;">
                                    <span style="font-weight:700;font-size:12px;color:{{ $tb->stock_borrowed > 0 ? '#7c3aed' : '#94a3b8' }};">
                                        {{ $tb->stock_borrowed }} Unit
                                    </span>
                                </td>
                                <td style="text-align:center;">
                                    <span style="font-weight:700;font-size:12px;color:{{ $tb->stock_maintenance > 0 ? '#dc2626' : '#94a3b8' }};">
                                        {{ $tb->stock_maintenance }} Unit
                                    </span>
                                </td>
                                <td>
                                    @if(!empty($tb->borrowers))
                                        <div style="display:flex;flex-direction:column;gap:3px;">
                                            @foreach($tb->borrowers as $bName)
                                            <div style="font-size:11.5px;color:#1e293b;display:flex;align-items:center;gap:5px;">
                                                <i class="fas fa-user text-muted" style="font-size:10px;"></i>
                                                {{ $bName }}
                                            </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted" style="font-size:11.5px;font-style:italic;">Standby di gudang</span>
                                    @endif
                                </td>
                                <td style="text-align:center;">
                                    @if($tb->stock_available > 0)
                                        <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:11px;padding:3px 8px;border-radius:12px;font-weight:700;">Siap Pakai</span>
                                    @elseif($tb->stock_borrowed > 0)
                                        <span class="badge" style="background:#f5f3ff;color:#7c3aed;border:1px solid #ddd6fe;font-size:11px;padding:3px 8px;border-radius:12px;font-weight:700;">Di Lapangan</span>
                                    @elseif($tb->stock_maintenance > 0)
                                        <span class="badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-size:11px;padding:3px 8px;border-radius:12px;font-weight:700;">Perlu Servis</span>
                                    @else
                                        <span class="badge" style="background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;font-size:11px;padding:3px 8px;border-radius:12px;">Kosong</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endforeach
            @else
                <div class="daily-empty-box">
                    <i class="fas fa-wrench"></i>
                    Tidak ada alat yang dipinjam pada tanggal {{ $carbonDate->translatedFormat('d F Y') }}.
                </div>
            @endif
        </div>
    </div>

    {{-- ==================== SECTION 8: PENYESUAIAN STOCK OPNAME ==================== --}}
    @if(isset($opnameAdjustments) && $opnameAdjustments->isNotEmpty())
    <div class="daily-section-card" id="section-opname-penyesuaian" data-category="material">
        <div class="daily-section-header">
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="width:26px;height:26px;border-radius:8px;background:#fef3c7;color:#d97706;font-weight:800;font-size:12px;display:inline-flex;align-items:center;justify-content:center;">8</span>
                <div>
                    <span style="font-size:13.5px;font-weight:700;color:#1e293b;">Penyesuaian Fisik Stock Opname Hari Ini</span>
                    <div class="text-muted" style="font-size:11px;">Hasil opname disetujui yang memengaruhi sisa stok akhir gudang</div>
                </div>
            </div>
            <span class="badge" style="background:#fef3c7;color:#b45309;border:1px solid #fde68a;font-size:11px;padding:3px 9px;border-radius:12px;font-weight:700;">
                {{ $opnameAdjustments->count() }} Dokumen Opname Disetujui
            </span>
        </div>
        <div class="table-wrap">
            <table class="data-table daily-table mb-0">
                <thead>
                    <tr>
                        <th style="width:160px;">No. Opname</th>
                        <th style="width:190px;">Pemeriksa & Approver</th>
                        <th>Material & Selisih Penyesuaian</th>
                        <th style="width:130px;text-align:center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($opnameAdjustments as $opname)
                    <tr>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('stock-opnames.show', $opname) }}" style="color:#2563eb;font-weight:700;font-size:12.5px;text-decoration:none;display:inline-flex;align-items:center;gap:5px;">
                                <i class="fas fa-clipboard-check text-warning" style="font-size:11px;"></i>
                                {{ $opname->opname_number }}
                            </a>
                        </td>
                        <td>
                            <div class="fw-700" style="color:#0f172a;font-size:12px;">Pemeriksa: {{ $opname->conductedBy?->name ?? '-' }}</div>
                            <div class="text-muted" style="font-size:11px;">Approver: {{ $opname->approvedBy?->name ?? '-' }}</div>
                        </td>
                        <td>
                            <div style="display:flex;flex-direction:column;gap:4px;">
                                @foreach($opname->items as $oItem)
                                <div style="font-size:12px;line-height:1.4;display:flex;align-items:center;gap:6px;">
                                    <strong style="color:#0f172a;">{{ $oItem->material?->name }}</strong>
                                    <span class="text-muted" style="font-size:11px;">(Sistem {{ format_quantity($oItem->qty_system) }} → Fisik {{ format_quantity($oItem->qty_physical) }})</span>
                                    @if($oItem->qty_difference > 0)
                                        <span class="qty-badge-in">+{{ format_quantity($oItem->qty_difference) }} {{ $oItem->material?->unit?->abbreviation ?? 'unit' }}</span>
                                    @elseif($oItem->qty_difference < 0)
                                        <span class="qty-badge-out">{{ format_quantity($oItem->qty_difference) }} {{ $oItem->material?->unit?->abbreviation ?? 'unit' }}</span>
                                    @else
                                        <span class="badge" style="background:#f1f5f9;color:#64748b;font-size:10.5px;">Sesuai</span>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </td>
                        <td style="text-align:center;">
                            <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:11px;padding:3px 8px;border-radius:12px;font-weight:700;">
                                <i class="fas fa-check-circle me-1"></i> Disetujui
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
        /**
         * Filter Section dan Otomatis Sembunyikan Kartu / Chart Metrik di Atasnya Saat Di-Filter
         */
        function filterSections(category) {
            // 1. Update status tombol tab filter aktif
            document.querySelectorAll('.btn-tab-filter').forEach(btn => {
                if (btn.getAttribute('data-filter') === category) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });

            // 2. OTOMATIS HILANGKAN KARTU / CHART METRIK DI ATAS SAAT DI-FILTER
            const kpiGrid = document.getElementById('dailyKpiGridContainer');
            const toggleIcon = document.getElementById('toggleKpiIcon');
            const toggleText = document.getElementById('toggleKpiText');
            const filterHelp = document.getElementById('filterHelpText');

            if (kpiGrid) {
                if (category !== 'all') {
                    // Saat memilih filter (Khusus Material / Khusus Alat Kerja): Otomatis HILANGKAN kartu di atas
                    kpiGrid.style.display = 'none';
                    if (toggleIcon) toggleIcon.className = 'fas fa-eye';
                    if (toggleText) toggleText.innerText = 'Tampilkan Kartu';
                    if (filterHelp) {
                        filterHelp.innerHTML = '<i class="fas fa-eye-slash me-1 text-primary"></i> Kartu ringkasan otomatis disembunyikan saat memfilter bagian.';
                    }
                } else {
                    // Saat memilih "Semua Bagian": Tampilkan kembali kartu ringkasan secara penuh
                    kpiGrid.style.display = 'grid';
                    kpiGrid.style.gridTemplateColumns = 'repeat(5, 1fr)';
                    document.querySelectorAll('.daily-kpi-card[data-kpi-category]').forEach(card => {
                        card.style.display = 'flex';
                    });
                    if (toggleIcon) toggleIcon.className = 'fas fa-eye-slash';
                    if (toggleText) toggleText.innerText = 'Sembunyikan Kartu';
                    if (filterHelp) {
                        filterHelp.innerHTML = '<i class="fas fa-mouse-pointer me-1 text-primary"></i> Klik kartu metrik di atas untuk otomatis menuju ke tabel rincian terkait.';
                    }
                }
            }

            // 3. Tampilkan atau sembunyikan section tabel data berdasarkan kategori filter
            const sections = document.querySelectorAll('.daily-section-card[data-category]');
            sections.forEach(sec => {
                const secCat = sec.getAttribute('data-category');
                if (category === 'all' || secCat === category) {
                    sec.style.display = '';
                } else {
                    sec.style.display = 'none';
                }
            });
        }

        /**
         * Toggle tombol untuk menyembunyikan / menampilkan kartu metrik di atas secara manual
         */
        function toggleAllKpiCards() {
            const kpiGrid = document.getElementById('dailyKpiGridContainer');
            const toggleIcon = document.getElementById('toggleKpiIcon');
            const toggleText = document.getElementById('toggleKpiText');
            const filterHelp = document.getElementById('filterHelpText');

            if (!kpiGrid) return;

            const activeBtn = document.querySelector('.btn-tab-filter.active');
            const currentFilter = activeBtn ? activeBtn.getAttribute('data-filter') : 'all';

            if (kpiGrid.style.display === 'none') {
                kpiGrid.style.display = 'grid';
                if (currentFilter === 'material') {
                    document.querySelectorAll('.daily-kpi-card[data-kpi-category]').forEach(card => {
                        card.style.display = card.getAttribute('data-kpi-category') === 'material' ? 'flex' : 'none';
                    });
                    kpiGrid.style.gridTemplateColumns = 'repeat(2, 1fr)';
                } else if (currentFilter === 'tool') {
                    document.querySelectorAll('.daily-kpi-card[data-kpi-category]').forEach(card => {
                        card.style.display = card.getAttribute('data-kpi-category') === 'tool' ? 'flex' : 'none';
                    });
                    kpiGrid.style.gridTemplateColumns = 'repeat(3, 1fr)';
                } else {
                    document.querySelectorAll('.daily-kpi-card[data-kpi-category]').forEach(card => {
                        card.style.display = 'flex';
                    });
                    kpiGrid.style.gridTemplateColumns = 'repeat(5, 1fr)';
                }
                if (toggleIcon) toggleIcon.className = 'fas fa-eye-slash';
                if (toggleText) toggleText.innerText = 'Sembunyikan Kartu';
                if (filterHelp) {
                    filterHelp.innerHTML = '<i class="fas fa-mouse-pointer me-1 text-primary"></i> Klik kartu metrik di atas untuk otomatis menuju ke tabel rincian terkait.';
                }
            } else {
                kpiGrid.style.display = 'none';
                if (toggleIcon) toggleIcon.className = 'fas fa-eye';
                if (toggleText) toggleText.innerText = 'Tampilkan Kartu';
                if (filterHelp) {
                    filterHelp.innerHTML = '<i class="fas fa-eye-slash me-1 text-primary"></i> Kartu ringkasan disembunyikan.';
                }
            }
        }

        /**
         * Klik kartu metrik untuk otomatis menuju section terkait dengan smooth scroll dan glow effect
         */
        function goToSection(sectionId, category) {
            // Cek apakah filter saat ini menyembunyikan target section
            const activeBtn = document.querySelector('.btn-tab-filter.active');
            const currentFilter = activeBtn ? activeBtn.getAttribute('data-filter') : 'all';

            if (currentFilter !== 'all' && currentFilter !== category) {
                filterSections(category);
            }

            const target = document.getElementById(sectionId);
            if (target) {
                target.style.display = '';
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });

                target.classList.remove('section-highlight');
                void target.offsetWidth; // trigger reflow
                target.classList.add('section-highlight');
            }
        }

        // Support URL hash navigation pada load awal
        document.addEventListener('DOMContentLoaded', function () {
            if (window.location.hash) {
                const hashId = window.location.hash.substring(1);
                const el = document.getElementById(hashId);
                if (el) {
                    const cat = el.getAttribute('data-category') || 'all';
                    goToSection(hashId, cat);
                }
            }
        });
    </script>
    @endpush
</x-app-layout>
