<x-app-layout>
    <x-slot name="title">Log Harian Proyek</x-slot>

    @push('styles')
    <style>
        /* Pulse Glow Highlight on Scroll Target */
        @keyframes targetSectionHighlight {
            0% {
                box-shadow: 0 0 0 0 rgba(234, 88, 12, 0);
                border-color: #e2e8f0;
            }
            25% {
                box-shadow: 0 0 0 4px rgba(234, 88, 12, 0.35);
                border-color: #ea580c;
            }
            100% {
                box-shadow: 0 1px 3px rgba(0,0,0,0.03);
                border-color: #e2e8f0;
            }
        }
        .section-highlight {
            animation: targetSectionHighlight 2s ease forwards;
        }

        /* KPI Card Hover Effects */
        .daily-kpi-card {
            user-select: none;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .daily-kpi-card:hover {
            border-color: #fdba74;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px -4px rgba(234, 88, 12, 0.12);
        }

        /* 1 Baris Sejajar Penuh untuk 5 Kartu KPI */
        .daily-kpi-scroll-container {
            width: 100%;
            margin-bottom: 16px;
        }
        .daily-kpi-grid-row {
            display: grid !important;
            grid-template-columns: repeat(5, minmax(0, 1fr)) !important;
            gap: 12px;
            width: 100%;
        }
        @media (max-width: 900px) {
            .daily-kpi-scroll-container {
                overflow-x: auto;
                padding-bottom: 6px;
                -webkit-overflow-scrolling: touch;
            }
            .daily-kpi-grid-row {
                min-width: 780px;
            }
        }

        /* Standard Quantity Badges */
        .qty-badge-in {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 11.5px;
            font-weight: 700;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }
        .qty-badge-out {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 11.5px;
            font-weight: 700;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            background: #fff1f2;
            color: #be123c;
            border: 1px solid #fecdd3;
        }
        .qty-badge-balance {
            display: inline-flex;
            align-items: center;
            padding: 2.5px 9px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 800;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            background: #f1f5f9;
            color: #0f172a;
            border: 1px solid #cbd5e1;
        }

        /* Filter Tab Buttons */
        .btn-tab-filter {
            padding: 6px 14px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #475569;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
        }
        .btn-tab-filter:hover {
            background: #f8fafc;
            color: #0f172a;
            border-color: #cbd5e1;
        }
        .btn-tab-filter.active {
            background: #ea580c;
            color: #ffffff;
            border-color: #ea580c;
            box-shadow: 0 1px 3px rgba(234, 88, 12, 0.25);
        }

        /* Section Container & Subcategory bar */
        .daily-section-card {
            border: 1px solid #e9e9e7;
            border-radius: 16px;
            background: #ffffff;
            overflow: hidden;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .daily-section-header {
            background: #fcfcfb;
            border-bottom: 1px solid #edf0f5;
            padding: 14px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }
        .category-sub-bar {
            background: #f8fafc;
            padding: 8px 18px;
            border-bottom: 1px solid #edf0f5;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        /* Empty State */
        .daily-empty-box {
            text-align: center;
            padding: 36px 20px;
            color: #94a3b8;
            font-size: 12.5px;
        }
        .daily-empty-box svg {
            margin: 0 auto 8px auto;
            color: #cbd5e1;
            width: 24px;
            height: 24px;
        }
    </style>
    @endpush

    @php
        $carbonDate = \Carbon\Carbon::parse($date);
        $prevDate = $carbonDate->copy()->subDay()->toDateString();
        $nextDate = $carbonDate->copy()->addDay()->toDateString();
        $isToday = $date === date('Y-m-d');
    @endphp

    <div class="w-full">

        {{-- ==================== 1. HEADER (STANDAR PT ARSIKON) ==================== --}}
        <header class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mt-1 mb-5">
            <div>
                <h1 class="text-[24px] leading-tight font-extrabold tracking-tight text-[#111111]">Log Harian Proyek</h1>
                <p class="text-[13px] text-gray-500 mt-1">Rekapitulasi mutasi barang masuk, pemakaian material, pantauan alat kerja, dan neraca sisa stok</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('daily-log.print', ['warehouse_id' => $selectedWarehouse->id, 'date' => $date]) }}" target="_blank" 
                   class="h-11 px-6 inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-[#ea580c] hover:bg-[#d9480c] text-white text-[13px] font-semibold transition shadow-sm gap-2 cursor-pointer">
                    <x-lucide-printer class="w-4 h-4" />
                    <span>Cetak Laporan</span>
                </a>
            </div>
        </header>

        {{-- ==================== 2. FILTER GUDANG & NAVIGASI TANGGAL ==================== --}}
        <div class="bg-white border border-[#e9e9e7] rounded-2xl p-4 sm:p-5 mb-5 shadow-xs">
            <form method="GET" action="{{ route('daily-log.index') }}" class="flex flex-col lg:flex-row items-stretch lg:items-end gap-3.5">
                {{-- Gudang / Site Proyek --}}
                <div class="flex-1 min-w-[240px]">
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Gudang / Site Proyek
                    </label>
                    <div class="relative">
                        <x-lucide-warehouse class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                        <select name="warehouse_id" class="w-full h-11 pl-10 pr-8 rounded-xl bg-white border border-[#e2e2e0] text-[13px] font-medium text-slate-800 focus:outline-none focus:border-[#ea580c] focus:ring-2 focus:ring-[#ea580c]/20 transition cursor-pointer" onchange="this.form.submit()">
                            @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ $selectedWarehouse->id == $wh->id ? 'selected' : '' }}>
                                {{ $wh->name }} {{ $wh->is_central ? '(Pusat)' : '(Proyek)' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Tanggal Picker dengan Shortcut H-1, Hari Ini, H+1 --}}
                <div class="flex-1 min-w-[280px]">
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Tanggal Log
                    </label>
                    <div class="flex items-center gap-2">
                        <div class="inline-flex items-center bg-[#f1f5f9] p-1 rounded-xl gap-0.5 border border-slate-200/60">
                            <a href="{{ route('daily-log.index', ['warehouse_id' => $selectedWarehouse->id, 'date' => $prevDate]) }}" 
                               class="px-2.5 py-1.5 rounded-lg text-[12px] font-semibold text-slate-600 hover:text-slate-900 transition inline-flex items-center gap-1" title="Kemarin (H-1)">
                                <x-lucide-chevron-left class="w-3.5 h-3.5" /> H-1
                            </a>
                            <a href="{{ route('daily-log.index', ['warehouse_id' => $selectedWarehouse->id, 'date' => date('Y-m-d')]) }}" 
                               class="px-3 py-1.5 rounded-lg text-[12px] font-bold transition {{ $isToday ? 'bg-white text-[#ea580c] shadow-xs border border-orange-100' : 'text-slate-600 hover:text-slate-900' }}" title="Hari Ini">
                                Hari Ini
                            </a>
                            <a href="{{ route('daily-log.index', ['warehouse_id' => $selectedWarehouse->id, 'date' => $nextDate]) }}" 
                               class="px-2.5 py-1.5 rounded-lg text-[12px] font-semibold text-slate-600 hover:text-slate-900 transition inline-flex items-center gap-1" title="Besok (H+1)">
                                H+1 <x-lucide-chevron-right class="w-3.5 h-3.5" />
                            </a>
                        </div>
                        <input type="date" name="date" value="{{ $date }}" class="flex-1 h-11 px-3.5 rounded-xl bg-white border border-[#e2e2e0] text-[13px] font-medium text-slate-800 focus:outline-none focus:border-[#ea580c] focus:ring-2 focus:ring-[#ea580c]/20 transition" onchange="this.form.submit()">
                    </div>
                </div>

                {{-- Action Submit --}}
            </form>
        </div>

        {{-- ==================== 3. KPI METRIC CARDS (5 METRIK OPERASIONAL — 1 BARIS SEJAJAR) ==================== --}}
        <div class="daily-kpi-scroll-container">
            <div class="daily-kpi-grid-row transition-all duration-300" id="dailyKpiGridContainer" style="display: grid !important; grid-template-columns: repeat(5, minmax(0, 1fr)) !important; gap: 12px; width: 100%;">
                {{-- 1. Material Masuk --}}
                <div class="daily-kpi-card bg-white border border-[#e9e9e7] rounded-2xl p-3.5 cursor-pointer flex items-center gap-3" data-kpi-category="material" onclick="goToSection('section-material-masuk', 'material')" title="Klik untuk menuju ke Penerimaan Material Masuk">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                        <x-lucide-package-check class="w-5 h-5" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider truncate">Material Masuk</div>
                        <div class="flex items-baseline gap-1 mt-1">
                            <span class="text-[20px] font-extrabold text-[#111111] tabular-nums leading-none">{{ $totalItemsIn }}</span>
                            <span class="text-[11px] font-bold text-slate-500">Item</span>
                        </div>
                        <div class="text-[11px] text-slate-400 mt-0.5 truncate">Surat jalan & GR</div>
                    </div>
                </div>

                {{-- 2. Material Dipakai --}}
                <div class="daily-kpi-card bg-white border border-[#e9e9e7] rounded-2xl p-3.5 cursor-pointer flex items-center gap-3" data-kpi-category="material" onclick="goToSection('section-material-dipakai', 'material')" title="Klik untuk menuju ke Pemakaian Material Lapangan">
                    <div class="w-10 h-10 rounded-xl bg-orange-50 text-[#ea580c] flex items-center justify-center flex-shrink-0">
                        <x-lucide-receipt class="w-5 h-5" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider truncate">Material Dipakai</div>
                        <div class="flex items-baseline gap-1 mt-1">
                            <span class="text-[20px] font-extrabold text-[#111111] tabular-nums leading-none">{{ $totalItemsOut }}</span>
                            <span class="text-[11px] font-bold text-slate-500">Item</span>
                        </div>
                        <div class="text-[11px] text-slate-400 mt-0.5 truncate">Keluar lapangan</div>
                    </div>
                </div>

                {{-- 3. Alat Ready --}}
                <div class="daily-kpi-card bg-white border border-[#e9e9e7] rounded-2xl p-3.5 cursor-pointer flex items-center gap-3" data-kpi-category="tool" onclick="goToSection('section-kesiapan-alat', 'tool')" title="Klik untuk menuju ke Kesiapan Alat Kerja di Gudang">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                        <x-lucide-wrench class="w-5 h-5" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider truncate">Alat Ready</div>
                        <div class="flex items-baseline gap-1 mt-1">
                            <span class="text-[20px] font-extrabold text-[#111111] tabular-nums leading-none">{{ $totalToolsReady }}</span>
                            <span class="text-[11px] font-bold text-slate-500">Unit</span>
                        </div>
                        <div class="text-[11px] text-slate-400 mt-0.5 truncate">Standby gudang</div>
                    </div>
                </div>

                {{-- 4. Alat di Lapangan --}}
                <div class="daily-kpi-card bg-white border border-[#e9e9e7] rounded-2xl p-3.5 cursor-pointer flex items-center gap-3" data-kpi-category="tool" onclick="goToSection('section-alat-lapangan', 'tool')" title="Klik untuk menuju ke Pantauan Alat di Lapangan">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center flex-shrink-0">
                        <x-lucide-hand class="w-5 h-5" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider truncate">Alat Lapangan</div>
                        <div class="flex items-baseline gap-1 mt-1">
                            <span class="text-[20px] font-extrabold text-[#111111] tabular-nums leading-none">{{ $totalToolsInUse }}</span>
                            <span class="text-[11px] font-bold text-slate-500">Unit</span>
                        </div>
                        <div class="text-[11px] text-slate-400 mt-0.5 truncate">Aktif dipinjam</div>
                    </div>
                </div>

                {{-- 5. Alat Rusak / Servis --}}
                <div class="daily-kpi-card bg-white border border-[#e9e9e7] rounded-2xl p-3.5 cursor-pointer flex items-center gap-3" data-kpi-category="tool" onclick="goToSection('section-kesiapan-alat', 'tool')" title="Klik untuk menuju ke Alat Perlu Servis / Rusak">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 {{ $totalToolsDamaged > 0 ? 'text-rose-600' : 'text-slate-400' }} flex items-center justify-center flex-shrink-0">
                        <x-lucide-triangle-alert class="w-5 h-5" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider truncate">Alat Rusak</div>
                        <div class="flex items-baseline gap-1 mt-1">
                            <span class="text-[20px] font-extrabold {{ $totalToolsDamaged > 0 ? 'text-rose-600' : 'text-[#111111]' }} tabular-nums leading-none">{{ $totalToolsDamaged }}</span>
                            <span class="text-[11px] font-bold text-slate-500">Unit</span>
                        </div>
                        <div class="text-[11px] text-slate-400 mt-0.5 truncate">Perlu perbaikan</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== 4. FILTER TAB BAR (BAGIAN & TOGGLE) ==================== --}}
        <div class="bg-white border border-[#e9e9e7] rounded-2xl p-3.5 mb-5 shadow-xs flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-[12px] font-bold text-slate-600 mr-1 inline-flex items-center gap-1.5">
                    <x-lucide-filter class="w-3.5 h-3.5 text-[#ea580c]" /> Filter Bagian:
                </span>
                <button type="button" class="btn-tab-filter active" data-filter="all" onclick="filterSections('all')">
                    <x-lucide-layers class="w-3.5 h-3.5" /> Semua Bagian
                </button>
                <button type="button" class="btn-tab-filter" data-filter="material" onclick="filterSections('material')">
                    <x-lucide-boxes class="w-3.5 h-3.5" /> Khusus Material
                </button>
                <button type="button" class="btn-tab-filter" data-filter="tool" onclick="filterSections('tool')">
                    <x-lucide-wrench class="w-3.5 h-3.5" /> Khusus Alat Kerja
                </button>
            </div>
        </div>

        {{-- ==================== SECTION 1: PEMAKAIAN MATERIAL LAPANGAN ==================== --}}
        <div class="daily-section-card" id="section-material-dipakai" data-category="material">
            <div class="daily-section-header">
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-lg bg-orange-50 text-[#ea580c] font-bold text-[12px] inline-flex items-center justify-center flex-shrink-0">1</span>
                    <div>
                        <span class="text-[14px] font-bold text-[#0f172a]">Pemakaian Material Lapangan Hari Ini (Outgoing)</span>
                        <div class="text-[11.5px] text-gray-500">Bon pengeluaran barang yang telah diserahkan ke mandor/pekerja proyek</div>
                    </div>
                </div>
                <span class="badge" style="background:#fff7ed;color:#ea580c;border:1px solid #fed7aa;font-size:11px;padding:3px 10px;border-radius:12px;font-weight:700;">
                    {{ $usages->count() }} Bon Pengeluaran
                </span>
            </div>
            <div class="table-wrap">
                <table class="data-table mb-0">
                    <thead>
                        <tr>
                            <th style="width:160px;">No. Bon</th>
                            <th style="width:200px;">Penerima (Mandor/Tukang)</th>
                            <th style="min-width:180px;">Bagian Pekerjaan / Zona</th>
                            <th>Material & Kuantitas yang Dikeluarkan</th>
                            <th style="width:150px;">Petugas Gudang</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usages as $usage)
                        <tr>
                            <td style="white-space:nowrap;">
                                <a href="{{ route('material-usages.show', $usage) }}" class="font-bold text-[#0f172a] hover:text-[#ea580c] transition inline-flex items-center gap-1.5" style="text-decoration:none;">
                                    <x-lucide-receipt class="w-3.5 h-3.5 text-[#ea580c]" />
                                    <span>{{ $usage->usage_number }}</span>
                                </a>
                            </td>
                            <td>
                                <div class="font-bold text-[#0f172a] text-[13px]">{{ $usage->recipient_name }}</div>
                            </td>
                            <td>
                                <span class="badge" style="background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;font-size:11px;padding:2px 8px;border-radius:6px;font-weight:600;">
                                    {{ $usage->job_section ?? 'Pekerjaan Umum' }}
                                </span>
                            </td>
                            <td>
                                <div class="flex flex-col gap-1">
                                    @foreach($usage->items as $uItem)
                                    <div class="text-[12px] leading-relaxed flex items-center gap-2">
                                        <span class="qty-badge-out">-{{ format_quantity($uItem->quantity) }} {{ $uItem->material?->unit?->abbreviation ?? $uItem->custom_item_unit ?? 'unit' }}</span>
                                        <span class="text-[#0f172a] font-semibold">{{ $uItem->material?->name ?? $uItem->custom_item_name }}</span>
                                        @if($uItem->notes)
                                        <span class="text-gray-400 text-[11px]">({{ $uItem->notes }})</span>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <span class="text-slate-500 text-[12px]">{{ $usage->issuedBy?->name ?? '-' }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="daily-empty-box">
                                <x-lucide-package-open />
                                <div>Tidak ada pengeluaran / pemakaian material pada tanggal {{ $carbonDate->translatedFormat('d F Y') }}.</div>
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
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-700 font-bold text-[12px] inline-flex items-center justify-center flex-shrink-0">2</span>
                    <div>
                        <span class="text-[14px] font-bold text-[#0f172a]">Penerimaan Material Masuk (Incoming)</span>
                        <div class="text-[11.5px] text-gray-500">Kiriman dari Gudang Pusat (Surat Jalan) maupun Supplier (Goods Receipt)</div>
                    </div>
                </div>
                <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:11px;padding:3px 10px;border-radius:12px;font-weight:700;">
                    {{ $incomingDistributions->count() + $incomingGoodsReceipts->count() }} Dokumen Penerimaan
                </span>
            </div>
            <div class="table-wrap">
                <table class="data-table mb-0">
                    <thead>
                        <tr>
                            <th style="width:170px;">No. Dokumen (SJ/GR)</th>
                            <th style="width:210px;">Sumber Pengirim</th>
                            <th>Material & Kuantitas Diterima</th>
                            <th style="width:140px;text-align:center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $hasIncoming = false; @endphp
                        @foreach($incomingDistributions as $dist)
                        @php $hasIncoming = true; @endphp
                        <tr>
                            <td style="white-space:nowrap;">
                                <a href="{{ route('distributions.show', $dist) }}" class="font-bold text-[#0f172a] hover:text-[#ea580c] transition inline-flex items-center gap-1.5" style="text-decoration:none;">
                                    <x-lucide-truck class="w-3.5 h-3.5 text-blue-600" />
                                    <span>{{ $dist->distribution_number }}</span>
                                </a>
                            </td>
                            <td>
                                <div class="font-bold text-[#0f172a] text-[13px]">{{ $dist->fromWarehouse?->name ?? 'Gudang Pusat' }}</div>
                                @if($dist->driver_name)
                                <div class="text-gray-400 text-[11px] mt-0.5">Supir: {{ $dist->driver_name }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="flex flex-col gap-1">
                                    @foreach($dist->items as $dItem)
                                    <div class="text-[12px] leading-relaxed flex items-center gap-2">
                                        <span class="qty-badge-in">+{{ format_quantity($dItem->qty_received) }} {{ $dItem->material?->unit?->abbreviation ?? $dItem->custom_item_unit ?? 'unit' }}</span>
                                        <span class="text-[#0f172a] font-semibold">{{ $dItem->material?->name ?? $dItem->tool?->name ?? $dItem->custom_item_name }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </td>
                            <td style="text-align:center;">
                                <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:11px;padding:3px 9px;border-radius:12px;font-weight:700;">
                                    <x-lucide-check class="w-3 h-3 me-0.5" /> Diterima di Site
                                </span>
                            </td>
                        </tr>
                        @endforeach

                        @foreach($incomingGoodsReceipts as $gr)
                        @php $hasIncoming = true; @endphp
                        <tr>
                            <td style="white-space:nowrap;">
                                <a href="{{ route('goods-receipts.show', $gr) }}" class="font-bold text-[#0f172a] hover:text-[#ea580c] transition inline-flex items-center gap-1.5" style="text-decoration:none;">
                                    <x-lucide-receipt class="w-3.5 h-3.5 text-emerald-600" />
                                    <span>{{ $gr->receipt_number }}</span>
                                </a>
                            </td>
                            <td>
                                <div class="font-bold text-[#0f172a] text-[13px]">{{ $gr->supplier?->name ?? 'Supplier Langsung' }}</div>
                                @if($gr->supplier_do_number ?? $gr->invoice_number)
                                <div class="text-gray-400 text-[11px] mt-0.5">Ref: {{ $gr->supplier_do_number ?? $gr->invoice_number }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="flex flex-col gap-1">
                                    @foreach($gr->items as $gItem)
                                    <div class="text-[12px] leading-relaxed flex items-center gap-2">
                                        <span class="qty-badge-in">+{{ format_quantity($gItem->quantity_received ?? $gItem->qty_received) }} {{ $gItem->material?->unit?->abbreviation ?? 'unit' }}</span>
                                        <span class="text-[#0f172a] font-semibold">{{ $gItem->material?->name }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </td>
                            <td style="text-align:center;">
                                <span class="badge" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;font-size:11px;padding:3px 9px;border-radius:12px;font-weight:700;">
                                    <x-lucide-circle-check class="w-3 h-3 me-0.5" /> Masuk Gudang
                                </span>
                            </td>
                        </tr>
                        @endforeach

                        @if(!$hasIncoming)
                        <tr>
                            <td colspan="4" class="daily-empty-box">
                                <x-lucide-truck />
                                <div>Tidak ada penerimaan barang masuk pada tanggal {{ $carbonDate->translatedFormat('d F Y') }}.</div>
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
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-lg bg-purple-50 text-purple-700 font-bold text-[12px] inline-flex items-center justify-center flex-shrink-0">3</span>
                    <div>
                        <span class="text-[14px] font-bold text-[#0f172a]">Distribusi Keluar ke Proyek / Gudang Lain</span>
                        <div class="text-[11.5px] text-gray-500">Pengiriman surat jalan keluar dari {{ $selectedWarehouse->name }}</div>
                    </div>
                </div>
                <span class="badge" style="background:#f5f3ff;color:#7c3aed;border:1px solid #ddd6fe;font-size:11px;padding:3px 10px;border-radius:12px;font-weight:700;">
                    {{ $outgoingDistributions->count() }} Pengiriman
                </span>
            </div>
            <div class="table-wrap">
                <table class="data-table mb-0">
                    <thead>
                        <tr>
                            <th style="width:170px;">No. Surat Jalan</th>
                            <th style="width:200px;">Tujuan Pengiriman</th>
                            <th style="width:120px;text-align:center;">Tgl Kirim</th>
                            <th>Material & Kuantitas Dikirim</th>
                            <th style="width:140px;text-align:center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($outgoingDistributions as $dist)
                        <tr>
                            <td style="white-space:nowrap;">
                                <a href="{{ route('distributions.show', $dist) }}" class="font-bold text-[#0f172a] hover:text-[#ea580c] transition inline-flex items-center gap-1.5" style="text-decoration:none;">
                                    <x-lucide-send class="w-3.5 h-3.5 text-purple-600" />
                                    <span>{{ $dist->distribution_number }}</span>
                                </a>
                            </td>
                            <td>
                                <div class="font-bold text-[#0f172a] text-[13px]">{{ $dist->toWarehouse?->name ?? 'Gudang Lain' }}</div>
                                @if($dist->driver_name)
                                <div class="text-gray-400 text-[11px] mt-0.5">Supir: {{ $dist->driver_name }}</div>
                                @endif
                            </td>
                            <td style="text-align:center;font-size:12px;color:#475569;">
                                {{ $dist->shipped_at ? \Carbon\Carbon::parse($dist->shipped_at)->format('d/m/Y') : '—' }}
                            </td>
                            <td>
                                <div class="flex flex-col gap-1">
                                    @foreach($dist->items as $dItem)
                                    <div class="text-[12px] leading-relaxed flex items-center gap-2">
                                        <span class="qty-badge-out">-{{ format_quantity($dItem->qty_shipped) }} {{ $dItem->material?->unit?->abbreviation ?? $dItem->custom_item_unit ?? 'unit' }}</span>
                                        <span class="text-[#0f172a] font-semibold">{{ $dItem->material?->name ?? $dItem->tool?->name ?? $dItem->custom_item_name }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </td>
                            <td style="text-align:center;">
                                @if($dist->status === 'received')
                                    <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:11px;padding:3px 9px;border-radius:12px;font-weight:700;">Diterima</span>
                                @else
                                    <span class="badge" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;font-size:11px;padding:3px 9px;border-radius:12px;font-weight:700;">Dalam Perjalanan</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="daily-empty-box">
                                <x-lucide-route />
                                <div>Tidak ada distribusi pengiriman keluar pada tanggal {{ $carbonDate->translatedFormat('d F Y') }}.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ==================== SECTION 4: PANTAUAN ALAT KERJA ==================== --}}
        <div class="daily-section-card" id="section-alat-lapangan" data-category="tool">
            <div class="daily-section-header">
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-700 font-bold text-[12px] inline-flex items-center justify-center flex-shrink-0">4</span>
                    <div>
                        <span class="text-[14px] font-bold text-[#0f172a]">Pantauan Alat Kerja di Lapangan (Active Tools)</span>
                        <div class="text-[11.5px] text-gray-500">Daftar alat operasional yang saat ini sedang dipinjam pekerja di lapangan</div>
                    </div>
                </div>
                <span class="badge" style="background:#f5f3ff;color:#6d28d9;border:1px solid #ddd6fe;font-size:11px;padding:3px 10px;border-radius:12px;font-weight:700;">
                    {{ $toolAssignments->count() }} Peminjaman Aktif
                </span>
            </div>
            <div class="table-wrap">
                <table class="data-table mb-0">
                    <thead>
                        <tr>
                            <th>Nama Alat & Kode</th>
                            <th style="width:90px;text-align:center;">Qty</th>
                            <th>Peminjam (Mandor/Tukang)</th>
                            <th>Lokasi / Zona Kerja</th>
                            <th style="width:115px;text-align:center;">Tgl Pinjam</th>
                            <th style="width:120px;text-align:center;">Target Kembali</th>
                            <th style="width:130px;text-align:center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($toolAssignments as $ta)
                        <tr>
                            <td>
                                <div class="font-bold text-[#0f172a] text-[13px]">{{ $ta->tool?->name }}</div>
                                <div class="text-gray-400 text-[11px] mt-0.5">
                                    @if($ta->tool?->code)
                                        <code class="bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded text-[10.5px]">{{ $ta->tool->code }}</code>
                                    @endif
                                </div>
                            </td>
                            <td style="text-align:center;">
                                <strong class="text-[12.5px] text-[#0f172a]">{{ $ta->quantity ?? 1 }} Unit</strong>
                            </td>
                            <td>
                                <div class="font-bold text-[#0f172a] text-[13px]">{{ $ta->borrower_display }}</div>
                                @if($ta->borrower_phone)
                                <div class="text-gray-400 text-[11px] mt-0.5">Telp: {{ $ta->borrower_phone }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="text-slate-600 text-[12px]">{{ $ta->location_display ?: 'Zona Lapangan' }}</span>
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
                                    <span class="{{ $isOver ? 'text-rose-600 font-bold' : 'text-slate-500' }}">
                                        {{ $exp->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                @if($ta->status === 'returned')
                                    <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:11px;padding:3px 9px;border-radius:12px;font-weight:700;">Dikembalikan</span>
                                @elseif($ta->status === 'overdue' || ($ta->status === 'active' && $ta->expected_return_at && \Carbon\Carbon::parse($ta->expected_return_at)->isPast()))
                                    <span class="badge" style="background:#fff1f2;color:#be123c;border:1px solid #fecdd3;font-size:11px;padding:3px 9px;border-radius:12px;font-weight:700;">Terlambat</span>
                                @else
                                    <span class="badge" style="background:#f5f3ff;color:#6d28d9;border:1px solid #ddd6fe;font-size:11px;padding:3px 9px;border-radius:12px;font-weight:700;">Dipinjam</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="daily-empty-box">
                                <x-lucide-wrench />
                                <div>Tidak ada alat kerja yang sedang dipinjam di lapangan pada tanggal ini.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ==================== SECTION 5: NERACA SISA STOK MATERIAL ==================== --}}
        <div class="daily-section-card" id="section-neraca-material" data-category="material">
            <div class="daily-section-header">
                <div>
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-700 font-bold text-[12px] inline-flex items-center justify-center flex-shrink-0">5</span>
                        <span class="text-[14px] font-bold text-[#0f172a]">Neraca Sisa Stok Material Hari Ini (Stock Balance)</span>
                    </div>
                    <div class="text-[11.5px] text-gray-500 mt-1">
                        Rumus kalkulasi: Stok Awal + Mutasi Masuk (+) - Pemakaian Keluar (-) = Sisa Stok Akhir
                    </div>
                </div>
                <span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-size:11px;padding:3px 10px;border-radius:12px;font-weight:700;">
                    {{ $stockBalance->count() }} Material Aktif
                </span>
            </div>
            <div>
                @if(isset($stockBalanceGrouped) && $stockBalanceGrouped->isNotEmpty())
                    @foreach($stockBalanceGrouped as $catName => $rows)
                    <div class="category-sub-bar">
                        <div class="flex items-center gap-2">
                            <x-lucide-tag class="w-3.5 h-3.5 text-[#ea580c]" />
                            <span class="font-bold text-[11.5px] uppercase tracking-wider text-[#1e293b]">{{ $catName }}</span>
                        </div>
                        <span class="badge" style="background:#ffffff;color:#475569;border:1px solid #cbd5e1;font-size:10.5px;padding:2px 8px;border-radius:6px;font-weight:600;">
                            {{ $rows->count() }} Jenis Material
                        </span>
                    </div>
                    <div class="table-wrap">
                        <table class="data-table mb-0">
                            <thead>
                                <tr>
                                    <th style="width:45px;text-align:center;">#</th>
                                    <th>Nama Material</th>
                                    <th style="width:125px;text-align:right;">Stok Awal</th>
                                    <th style="width:125px;text-align:right;">Masuk (+)</th>
                                    <th style="width:125px;text-align:right;">Dipakai (-)</th>
                                    <th style="width:135px;text-align:right;">Sisa Akhir</th>
                                    <th style="width:85px;text-align:center;">Satuan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows as $idx => $stk)
                                <tr>
                                    <td style="text-align:center;color:#94a3b8;font-size:11px;">{{ $idx + 1 }}</td>
                                    <td>
                                        <div class="font-bold text-[#0f172a] text-[13px]">{{ $stk->material_name }}</div>
                                        @if($stk->material_code)
                                        <div class="text-gray-400 text-[10.5px] mt-0.5"><code class="bg-slate-100 text-slate-700 px-1 py-0.5 rounded text-[10px]">{{ $stk->material_code }}</code></div>
                                        @endif
                                    </td>
                                    <td style="text-align:right;color:#475569;font-size:12px;font-weight:600;font-family:ui-monospace, monospace;">{{ format_quantity($stk->opening_stock) }}</td>
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
                        <x-lucide-layers />
                        <div>Tidak ada transaksi mutasi material pada tanggal {{ $carbonDate->translatedFormat('d F Y') }}.</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ==================== SECTION 6: KESIAPAN ALAT KERJA ==================== --}}
        <div class="daily-section-card" id="section-kesiapan-alat" data-category="tool">
            <div class="daily-section-header">
                <div>
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-700 font-bold text-[12px] inline-flex items-center justify-center flex-shrink-0">6</span>
                        <span class="text-[14px] font-bold text-[#0f172a]">Neraca Posisi & Kesiapan Alat Kerja (Tool Availability)</span>
                    </div>
                    <div class="text-[11.5px] text-gray-500 mt-1">
                        Pantauan ketersediaan unit di gudang vs peminjaman aktif di lapangan
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:11px;padding:3px 9px;border-radius:12px;font-weight:700;">{{ $totalToolsReady }} Ready</span>
                    <span class="badge" style="background:#f5f3ff;color:#7c3aed;border:1px solid #ddd6fe;font-size:11px;padding:3px 9px;border-radius:12px;font-weight:700;">{{ $totalToolsInUse }} di Lapangan</span>
                    @if($totalToolsDamaged > 0)
                    <span class="badge" style="background:#fff1f2;color:#be123c;border:1px solid #fecdd3;font-size:11px;padding:3px 9px;border-radius:12px;font-weight:700;">{{ $totalToolsDamaged }} Rusak/Servis</span>
                    @endif
                </div>
            </div>
            <div>
                @if(isset($toolBalanceGrouped) && $toolBalanceGrouped->isNotEmpty())
                    @foreach($toolBalanceGrouped as $catName => $rows)
                    <div class="category-sub-bar">
                        <div class="flex items-center gap-2">
                            <x-lucide-wrench class="w-3.5 h-3.5 text-[#ea580c]" />
                            <span class="font-bold text-[11.5px] uppercase tracking-wider text-[#1e293b]">{{ $catName }}</span>
                        </div>
                        <span class="badge" style="background:#ffffff;color:#475569;border:1px solid #cbd5e1;font-size:10.5px;padding:2px 8px;border-radius:6px;font-weight:600;">
                            {{ $rows->count() }} Jenis Alat
                        </span>
                    </div>
                    <div class="table-wrap">
                        <table class="data-table mb-0">
                            <thead>
                                <tr>
                                    <th style="width:45px;text-align:center;">#</th>
                                    <th>Nama Alat Kerja</th>
                                    <th style="width:105px;text-align:center;">Total Unit</th>
                                    <th style="width:115px;text-align:center;">Ready (Gudang)</th>
                                    <th style="width:115px;text-align:center;">Di Lapangan</th>
                                    <th style="width:115px;text-align:center;">Servis / Rusak</th>
                                    <th>Mandor Pemegang / Posisi</th>
                                    <th style="width:135px;text-align:center;">Kesiapan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows as $tIdx => $tb)
                                <tr>
                                    <td style="text-align:center;color:#94a3b8;font-size:11px;">{{ $tIdx + 1 }}</td>
                                    <td>
                                        <div class="font-bold text-[#0f172a] text-[13px]">{{ $tb->tool_name }}</div>
                                        <div class="text-gray-400 text-[10.5px] mt-0.5 flex items-center gap-1.5 flex-wrap">
                                            @if($tb->tool_code)
                                                <code class="bg-slate-100 text-slate-700 px-1 py-0.5 rounded text-[10px]">{{ $tb->tool_code }}</code>
                                            @endif
                                            @if($tb->brand)
                                                <span>• Merk: {{ $tb->brand }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td style="text-align:center;">
                                        <strong class="text-[12.5px] text-[#0f172a]">{{ $tb->stock_total }} Unit</strong>
                                    </td>
                                    <td style="text-align:center;">
                                        <span class="font-bold text-[12px] {{ $tb->stock_available > 0 ? 'text-emerald-600' : 'text-slate-400' }}">
                                            {{ $tb->stock_available }} Unit
                                        </span>
                                    </td>
                                    <td style="text-align:center;">
                                        <span class="font-bold text-[12px] {{ $tb->stock_borrowed > 0 ? 'text-purple-600' : 'text-slate-400' }}">
                                            {{ $tb->stock_borrowed }} Unit
                                        </span>
                                    </td>
                                    <td style="text-align:center;">
                                        <span class="font-bold text-[12px] {{ $tb->stock_maintenance > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                            {{ $tb->stock_maintenance }} Unit
                                        </span>
                                    </td>
                                    <td>
                                        @if(!empty($tb->borrowers))
                                            <div class="flex flex-col gap-1">
                                                @foreach($tb->borrowers as $bName)
                                                <div class="text-[11.5px] text-[#1e293b] flex items-center gap-1.5">
                                                    <x-lucide-user class="w-3 h-3 text-slate-400" />
                                                    <span>{{ $bName }}</span>
                                                </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-gray-400 text-[11.5px] italic">Standby di gudang</span>
                                        @endif
                                    </td>
                                    <td style="text-align:center;">
                                        @if($tb->stock_available > 0)
                                            <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:11px;padding:3px 9px;border-radius:12px;font-weight:700;">Siap Pakai</span>
                                        @elseif($tb->stock_borrowed > 0)
                                            <span class="badge" style="background:#f5f3ff;color:#7c3aed;border:1px solid #ddd6fe;font-size:11px;padding:3px 9px;border-radius:12px;font-weight:700;">Di Lapangan</span>
                                        @elseif($tb->stock_maintenance > 0)
                                            <span class="badge" style="background:#fff1f2;color:#be123c;border:1px solid #fecdd3;font-size:11px;padding:3px 9px;border-radius:12px;font-weight:700;">Perlu Servis</span>
                                        @else
                                            <span class="badge" style="background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;font-size:11px;padding:3px 9px;border-radius:12px;">Kosong</span>
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
                        <x-lucide-wrench />
                        <div>Tidak ada alat yang dipinjam pada tanggal {{ $carbonDate->translatedFormat('d F Y') }}.</div>
                    </div>
                @endif
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        /**
         * Filter Section dan Otomatis Sembunyikan Kartu Metrik di Atas Saat Di-Filter
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
            const eyeOpen = document.getElementById('toggleKpiEye');
            const eyeOff = document.getElementById('toggleKpiEyeOff');
            const toggleText = document.getElementById('toggleKpiText');
            const filterHelp = document.getElementById('filterHelpText');

            if (kpiGrid) {
                if (category !== 'all') {
                    kpiGrid.style.display = 'none';
                    if (eyeOpen) eyeOpen.style.display = 'inline-flex';
                    if (eyeOff) eyeOff.style.display = 'none';
                    if (toggleText) toggleText.innerText = 'Tampilkan Kartu';
                    if (filterHelp) {
                        filterHelp.innerHTML = `<x-lucide-eye-off class="w-3.5 h-3.5 text-[#ea580c]" /> Kartu ringkasan disembunyikan saat memfilter bagian.`;
                    }
                } else {
                    kpiGrid.style.display = 'grid';
                    kpiGrid.style.gridTemplateColumns = 'repeat(5, minmax(0, 1fr))';
                    document.querySelectorAll('.daily-kpi-card[data-kpi-category]').forEach(card => {
                        card.style.display = 'flex';
                    });
                    if (eyeOpen) eyeOpen.style.display = 'none';
                    if (eyeOff) eyeOff.style.display = 'inline-flex';
                    if (toggleText) toggleText.innerText = 'Sembunyikan Kartu';
                    if (filterHelp) {
                        filterHelp.innerHTML = `<x-lucide-mouse-pointer-2 class="w-3.5 h-3.5 text-[#ea580c]" /> Klik kartu di atas untuk navigasi cepat ke tabel terkait.`;
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
            const eyeOpen = document.getElementById('toggleKpiEye');
            const eyeOff = document.getElementById('toggleKpiEyeOff');
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
                } else if (currentFilter === 'tool') {
                    document.querySelectorAll('.daily-kpi-card[data-kpi-category]').forEach(card => {
                        card.style.display = card.getAttribute('data-kpi-category') === 'tool' ? 'flex' : 'none';
                    });
                } else {
                    document.querySelectorAll('.daily-kpi-card[data-kpi-category]').forEach(card => {
                        card.style.display = 'flex';
                    });
                    kpiGrid.style.gridTemplateColumns = 'repeat(5, minmax(0, 1fr))';
                }
                if (eyeOpen) eyeOpen.style.display = 'none';
                if (eyeOff) eyeOff.style.display = 'inline-flex';
                if (toggleText) toggleText.innerText = 'Sembunyikan Kartu';
                if (filterHelp) {
                    filterHelp.innerHTML = `<x-lucide-mouse-pointer-2 class="w-3.5 h-3.5 text-[#ea580c]" /> Klik kartu di atas untuk navigasi cepat ke tabel terkait.`;
                }
            } else {
                kpiGrid.style.display = 'none';
                if (eyeOpen) eyeOpen.style.display = 'inline-flex';
                if (eyeOff) eyeOff.style.display = 'none';
                if (toggleText) toggleText.innerText = 'Tampilkan Kartu';
                if (filterHelp) {
                    filterHelp.innerHTML = `<x-lucide-eye-off class="w-3.5 h-3.5 text-[#ea580c]" /> Kartu ringkasan disembunyikan.`;
                }
            }
        }

        /**
         * Klik kartu metrik untuk otomatis menuju section terkait dengan smooth scroll dan glow effect
         */
        function goToSection(sectionId, category) {
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
