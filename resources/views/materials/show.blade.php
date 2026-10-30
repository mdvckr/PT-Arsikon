<x-app-layout>
    <x-slot name="title">Detail Material: {{ $material->name }}</x-slot>

    @push('styles')
    <style>
        .detail-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            transition: box-shadow 0.2s ease;
        }

        .detail-card:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.06), 0 2px 4px -1px rgba(0, 0, 0, 0.04);
        }

        .detail-card-header {
            padding: 14px 20px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .detail-card-title {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }

        .detail-card-body {
            padding: 20px;
        }

        @media print {
            .no-print,
            .sidebar,
            nav,
            header,
            .breadcrumb,
            #btn-print-material,
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

            .detail-card {
                border: 1px solid #cbd5e1 !important;
                box-shadow: none !important;
                break-inside: avoid;
                margin-bottom: 12px !important;
            }

            #material-detail-grid {
                display: block !important;
            }

            #material-detail-grid > div {
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

    {{-- Main Container (Full Width) --}}
    <div class="w-full">

        {{-- Breadcrumb --}}
        <div class="breadcrumb no-print" style="margin-bottom:6px;">
            <a href="{{ route('materials.index') }}" class="text-slate-500 hover:text-primary">Material</a>
            <span class="breadcrumb-sep mx-1.5 text-slate-400"><x-lucide-chevron-right style="font-size:10px;" /></span>
            <span class="text-slate-700 font-medium">{{ $material->name }}</span>
        </div>

        {{-- Action Bar --}}
        <div class="flex items-center justify-between mb-4 flex-wrap gap-3 no-print">
            <div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight m-0">{{ $material->name }}</h2>
                    <span class="font-mono text-xs sm:text-sm text-slate-700 font-semibold bg-slate-100 px-2.5 py-1 rounded border border-slate-200">
                        {{ $material->sku }}
                    </span>
                    @if($material->category)
                    <span class="badge text-xs font-semibold px-2.5 py-1 rounded" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;">
                        {{ $material->category->name }}
                    </span>
                    @endif
                </div>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Terdaftar sejak {{ $material->created_at ? $material->created_at->format('d M Y, H:i') : '-' }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @can('edit materials')
                <a href="{{ route('materials.edit', $material) }}" class="btn btn-secondary" style="height:36px;padding:0 14px;font-size:13px;font-weight:600;border-radius:6px;display:inline-flex;align-items:center;gap:6px;">
                    <x-lucide-pencil /> Edit
                </a>
                @endcan
                <button type="button" class="btn btn-primary" onclick="printMaterial()" id="btn-print-material" style="height:36px;padding:0 14px;font-size:13px;font-weight:600;border-radius:6px;display:inline-flex;align-items:center;gap:6px;">
                    <x-lucide-printer /> Cetak / Print
                </button>
                <a href="{{ route('materials.index') }}" class="btn btn-light border" style="height:36px;padding:0 14px;font-size:13px;font-weight:600;border-radius:6px;color:#475569;display:inline-flex;align-items:center;gap:6px;">
                    <x-lucide-arrow-left /> Kembali
                </a>
            </div>
        </div>

        {{-- Print Header (only visible when printing) --}}
        <div class="print-only" style="display:none;margin-bottom:20px;border-bottom:2px solid #0f172a;padding-bottom:12px;">
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <div style="font-size:18px;font-weight:800;color:#0f172a;">PT Arsikon Cipta Karya</div>
                    <div style="font-size:11px;color:#64748b;margin-top:2px;">Informasi & Kartu Data Material</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:11px;color:#64748b;">Dicetak pada:</div>
                    <div style="font-size:12px;font-weight:600;" id="print-date-time"></div>
                </div>
            </div>
        </div>

        {{-- 12-Column Responsive Layout --}}
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start" id="material-detail-grid">
            
            {{-- ── KOLOM KIRI (8 KOLOM DESKTOP) ── --}}
            <div class="xl:col-span-8 space-y-6">

                {{-- Inventory per Warehouse --}}
                <div class="detail-card">
                    <div class="detail-card-header">
                        <div class="detail-card-title">
                            <x-lucide-warehouse class="text-primary" /> Stok per Lokasi / Gudang
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="data-table mb-0" style="width:100%;border-collapse:collapse;min-width:550px;">
                            <thead>
                                <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                                    <th style="padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;">Gudang / Lokasi</th>
                                    <th style="padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;text-align:right;">Qty Tersedia</th>
                                    <th style="padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;text-align:right;">Min. Stok</th>
                                    <th style="padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;text-align:center;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($material->inventories as $inv)
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    <td style="padding:10px 16px;font-weight:600;color:#0f172a;">
                                        {{ $inv->warehouse?->name ?? 'Gudang Utama' }}
                                    </td>
                                    <td style="padding:10px 16px;text-align:right;">
                                        <span style="font-weight:600;font-size:13.5px;color:#0f172a;">{{ number_format($inv->quantity, 0, ',', '.') }}</span>
                                        <span class="text-muted" style="font-size:11.5px;">{{ $material->unit?->abbreviation ?? $material->unit?->name }}</span>
                                    </td>
                                    <td style="padding:10px 16px;text-align:right;color:#64748b;font-size:12.5px;">
                                        {{ number_format($inv->min_stock, 0, ',', '.') }}
                                    </td>
                                    <td style="padding:10px 16px;text-align:center;font-size:12px;">
                                        @if($inv->quantity <= 0)
                                            <span style="color:#dc2626;font-weight:600;background:#fef2f2;padding:3px 8px;border-radius:4px;border:1px solid #fecaca;">Habis</span>
                                        @elseif($inv->quantity <= $inv->min_stock)
                                            <span style="color:#b45309;font-weight:600;background:#fffbeb;padding:3px 8px;border-radius:4px;border:1px solid #fde68a;">Rendah</span>
                                        @else
                                            <span style="color:#16a34a;font-weight:600;background:#ecfdf5;padding:3px 8px;border-radius:4px;border:1px solid #a7f3d0;">Normal</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-muted" style="text-align:center;padding:24px;font-size:13px;">Belum ada stok fisik terdaftar di gudang manapun.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Recent Mutations --}}
                <div class="detail-card">
                    <div class="detail-card-header">
                        <div class="detail-card-title">
                            <x-lucide-list class="text-primary" /> Riwayat Mutasi Stok (20 Terakhir)
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="data-table mb-0" style="width:100%;border-collapse:collapse;min-width:650px;">
                            <thead>
                                <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                                    <th style="padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;">Tanggal</th>
                                    <th style="padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;">Gudang</th>
                                    <th style="padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;">Tipe</th>
                                    <th style="padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;text-align:right;">Jumlah</th>
                                    <th style="padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;text-align:right;">Stok Akhir</th>
                                    <th style="padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#475569;">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($material->stockMutations as $mut)
                                @php
                                    $qty = (float) ($mut->qty_change ?? $mut->quantity ?? 0);
                                    $isIn = $qty >= 0;
                                @endphp
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    <td style="padding:10px 16px;color:#64748b;font-size:12px;white-space:nowrap;vertical-align:middle;">
                                        {{ $mut->created_at ? $mut->created_at->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td style="padding:10px 16px;color:#334155;font-size:12.5px;font-weight:500;vertical-align:middle;">
                                        {{ $mut->warehouse?->name ?? '-' }}
                                    </td>
                                    <td style="padding:10px 16px;font-size:12px;vertical-align:middle;">
                                        @if($isIn)
                                            <span style="display:inline-block;padding:2px 8px;border-radius:12px;font-size:10.5px;font-weight:600;background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;">Masuk</span>
                                        @else
                                            <span style="display:inline-block;padding:2px 8px;border-radius:12px;font-size:10.5px;font-weight:600;background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;">Keluar</span>
                                        @endif
                                    </td>
                                    <td style="padding:10px 16px;text-align:right;font-weight:700;font-size:13px;color:#0f172a;vertical-align:middle;">
                                        {{ $isIn ? '+' : '-' }}{{ number_format(abs($qty), 0, ',', '.') }}
                                        <span class="text-muted" style="font-size:11px;font-weight:normal;">{{ $material->unit?->abbreviation ?? $material->unit?->name ?? '' }}</span>
                                    </td>
                                    <td style="padding:10px 16px;text-align:right;font-weight:600;font-size:12.5px;color:#475569;vertical-align:middle;">
                                        {{ number_format((float)($mut->qty_balance_after ?? 0), 0, ',', '.') }}
                                        <span class="text-muted" style="font-size:11px;font-weight:normal;">{{ $material->unit?->abbreviation ?? '' }}</span>
                                    </td>
                                    <td style="padding:10px 16px;color:#64748b;font-size:12px;vertical-align:middle;">{{ $mut->notes ?? '-' }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-muted" style="text-align:center;padding:24px;font-size:13px;">Belum ada catatan mutasi stok untuk material ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            {{-- ── KOLOM KANAN: INFO MATERIAL & JADWAL KEDATANGAN (4 KOLOM DESKTOP) ── --}}
            <div class="xl:col-span-4 space-y-6 xl:sticky xl:top-6">
                
                {{-- Info Card --}}
                <div class="detail-card">
                    <div class="detail-card-header">
                        <div class="detail-card-title">
                            <x-lucide-box class="text-primary" /> Informasi Material
                        </div>
                    </div>
                    <div class="detail-card-body">
                        <table style="width:100%;font-size:13px;border-collapse:collapse;">
                            @php
                                $displaySupplier = $material->supplier?->name ?? $material->supplier_name;
                                $rows = [
                                    ['Kode SKU', $material->sku, 'font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;font-size:12.5px;color:#334155;font-weight:600;'],
                                    ['Nama Material', $material->name, 'font-weight:600;color:#0f172a;'],
                                    ['Kelompok Barang', $material->type ?: '-', null],
                                    ['Merek / Brand', $material->brand ?: '-', null],
                                    ['Ukuran / Dimensi', $material->size ?: '-', null],
                                    ['Supplier / Pemasok', $displaySupplier ?: '-', null],
                                    ['Kategori', $material->category?->name ?? '-', null],
                                    ['Satuan', ($material->unit?->name ?? '-').' ('.($material->unit?->abbreviation ?? '').')', null],
                                    ['Waktu Input Data', $material->created_at ? $material->created_at->format('d M Y, H:i') : '-', null],
                                ];
                            @endphp
                            @foreach($rows as [$label, $value, $style])
                            <tr style="border-bottom:1px solid #f8fafc;">
                                <td style="padding:8px 0;color:#64748b;font-weight:500;width:40%;vertical-align:top;font-size:12px;">{{ $label }}</td>
                                <td style="padding:8px 0;color:#1e293b;vertical-align:top;">
                                    @if($style)
                                        <span style="{{ $style }}">{{ $value }}</span>
                                    @else
                                        {{ $value }}
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </table>
                    </div>
                </div>

                {{-- Incoming Stages Card (PO & PO Lapangan Dinamis) --}}
                @php
                    $dynamicStages = $material->getDynamicIncomingStages();
                @endphp
                @if($dynamicStages->isNotEmpty())
                <div class="detail-card">
                    <div class="detail-card-header">
                        <div class="detail-card-title">
                            <x-lucide-calendar-clock class="text-primary" /> Rencana Tahap Kedatangan Barang
                        </div>
                        <span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-size:11px;font-weight:700;">
                            {{ $dynamicStages->count() }} Transaksi Aktif
                        </span>
                    </div>
                    <div class="detail-card-body">
                        <div style="display:flex;flex-direction:column;gap:10px;">
                            @foreach($dynamicStages as $stg)
                                @php
                                    $isReceived = ($stg['status'] ?? '') === 'received';
                                    $isPartial = ($stg['status'] ?? '') === 'partial';
                                    $badgeBg = $isReceived ? '#ecfdf5' : ($isPartial ? '#eff6ff' : '#fffbeb');
                                    $badgeColor = $isReceived ? '#047857' : ($isPartial ? '#1d4ed8' : '#b45309');
                                    $badgeBorder = $isReceived ? '#a7f3d0' : ($isPartial ? '#bfdbfe' : '#fde68a');
                                @endphp
                                <div style="padding:10px 12px;border-radius:8px;font-size:12px;background:#f8fafc;border:1px solid #e2e8f0;color:#334155;">
                                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;">
                                        <div>
                                            <div style="display:flex;align-items:center;gap:6px;">
                                                <span class="badge" style="font-size:10px;font-weight:700;padding:2px 6px;border-radius:4px;background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;">
                                                    {{ $stg['type'] }}
                                                </span>
                                                <strong style="font-family:ui-monospace,SFMono-Regular,monospace;color:#0f172a;">{{ $stg['reference'] }}</strong>
                                            </div>
                                            <div style="font-size:11px;color:#64748b;margin-top:3px;">
                                                <span>{{ $stg['source'] }}</span>
                                                @if(!empty($stg['warehouse_name']))
                                                    &bull; <span class="font-medium text-slate-700">{{ $stg['warehouse_name'] }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div style="text-align:right;">
                                            <span class="badge" style="background:{{ $badgeBg }};color:{{ $badgeColor }};border:1px solid {{ $badgeBorder }};font-size:10.5px;padding:3px 8px;border-radius:5px;font-weight:700;">
                                                {{ $stg['status_label'] }}
                                            </span>
                                            @if(!empty($stg['date']))
                                            <div style="font-size:10.5px;color:#94a3b8;margin-top:2px;">
                                                {{ \Carbon\Carbon::parse($stg['date'])->format('d/m/Y') }}
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div style="margin-top:8px;padding-top:8px;border-top:1px dashed #e2e8f0;display:flex;justify-content:space-between;align-items:center;font-size:11.5px;">
                                        <span class="text-slate-500">
                                            Rencana: <strong>{{ number_format($stg['planned_qty'], 0, ',', '.') }}</strong>
                                            @if($stg['received_qty'] > 0)
                                                | Tiba: <strong class="text-emerald-700">{{ number_format($stg['received_qty'], 0, ',', '.') }}</strong>
                                            @endif
                                        </span>
                                        <span style="font-weight:800;font-size:12.5px;color:#0f172a;">
                                            Sisa: {{ number_format($stg['remaining_qty'], 0, ',', '.') }} {{ $material->unit?->abbreviation ?? '' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

            </div>

        </div>

    </div>

    @push('scripts')
    <script>
        function printMaterial() {
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
