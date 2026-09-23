<x-app-layout>
    <x-slot name="title">Detail Material: {{ $material->name }}</x-slot>

    {{-- Breadcrumb --}}
    <div class="breadcrumb no-print" style="margin-bottom:8px;">
        <a href="{{ route('materials.index') }}">Material</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>{{ $material->name }}</span>
    </div>

    {{-- Action Bar --}}
    <div class="flex items-center justify-between mb-4 no-print" style="flex-wrap:wrap;gap:12px;">
        <div>
            <div class="flex items-center gap-2" style="flex-wrap:wrap;">
                <h2 class="fw-700" style="font-size:20px;color:#0f172a;margin:0;">{{ $material->name }}</h2>
                <span style="font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;font-size:13px;color:#334155;font-weight:600;">
                    {{ $material->sku }}
                </span>
                @if($material->category)
                <span class="text-muted" style="font-size:12.5px;">
                    · {{ $material->category->name }}
                </span>
                @endif
            </div>
            <p class="text-muted" style="font-size:12.5px;margin:3px 0 0;">
                Terdaftar sejak {{ $material->created_at ? $material->created_at->format('d M Y, H:i') : '-' }}
            </p>
        </div>
        <div class="flex gap-2 items-center">
            @can('edit materials')
            <a href="{{ route('materials.edit', $material) }}" class="btn btn-secondary" style="height:36px;padding:0 14px;font-size:13px;font-weight:600;border-radius:6px;">
                Edit
            </a>
            @endcan
            <button type="button" class="btn btn-primary" onclick="printMaterial()" id="btn-print-material" style="height:36px;padding:0 14px;font-size:13px;font-weight:600;border-radius:6px;">
                Cetak / Print
            </button>
            <a href="{{ route('materials.index') }}" class="btn btn-light border" style="height:36px;padding:0 14px;font-size:13px;font-weight:600;border-radius:6px;color:#475569;">
                Kembali
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

    <div class="grid" style="grid-template-columns:1fr 340px;gap:20px;align-items:start;" id="material-detail-grid">
        <div style="display:flex;flex-direction:column;gap:16px;">
            {{-- Inventory per Warehouse --}}
            <div class="card" style="border:1px solid #e2e8f0;box-shadow:none;border-radius:8px;overflow:hidden;">
                <div style="padding:14px 18px;border-bottom:1px solid #e2e8f0;">
                    <div class="fw-700" style="font-size:14px;color:#0f172a;"><i class="fas fa-warehouse me-1 text-primary"></i> Stok per Lokasi / Gudang</div>
                </div>
                <div class="table-wrap">
                    <table class="data-table mb-0" style="width:100%;border-collapse:collapse;">
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
                                        <span style="color:#dc2626;font-weight:500;">Habis</span>
                                    @elseif($inv->quantity <= $inv->min_stock)
                                        <span style="color:#b45309;font-weight:500;">Rendah</span>
                                    @else
                                        <span style="color:#16a34a;font-weight:500;">Normal</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-muted" style="text-align:center;padding:20px;font-size:13px;">Belum ada stok fisik terdaftar di gudang manapun.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Recent Mutations --}}
            <div class="card" style="border:1px solid #e2e8f0;box-shadow:none;border-radius:8px;overflow:hidden;">
                <div style="padding:14px 18px;border-bottom:1px solid #e2e8f0;">
                    <div class="fw-700" style="font-size:14px;color:#0f172a;"><i class="fas fa-list me-1 text-primary"></i> Riwayat Mutasi Stok (20 Terakhir)</div>
                </div>
                <div class="table-wrap">
                    <table class="data-table mb-0" style="width:100%;border-collapse:collapse;">
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
    
        {{-- Info Card --}}
        <div class="card" style="border:1px solid #e2e8f0;box-shadow:none;border-radius:8px;position:sticky;top:20px;">
            <div style="padding:14px 18px;border-bottom:1px solid #e2e8f0;">
                <div class="fw-700" style="font-size:14px;color:#0f172a;"><i class="fas fa-cube me-1 text-primary"></i> Informasi Material</div>
            </div>
            <div class="card-body" style="padding:18px;">
                <table style="width:100%;font-size:13px;border-collapse:collapse;">
                    @php
                        $displaySupplier = $material->supplier?->name ?? $material->supplier_name;
                        $rows = [
                            ['Kode SKU', '<span style="font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;font-size:12.5px;color:#334155;font-weight:600;">'.$material->sku.'</span>'],
                            ['Nama Material', '<span style="font-weight:600;color:#0f172a;">'.$material->name.'</span>'],
                            ['Kelompok Barang', $material->type ?: '-'],
                            ['Merek / Brand', $material->brand ?: '-'],
                            ['Ukuran / Dimensi', $material->size ?: '-'],
                            ['Supplier / Pemasok', $displaySupplier ?: '-'],
                            ['Kategori', $material->category?->name ?? '-'],
                            ['Satuan', ($material->unit?->name ?? '-').' ('.($material->unit?->abbreviation ?? '').')'],
                            ['Waktu Input Data', $material->created_at ? $material->created_at->format('d M Y, H:i') : '-'],
                        ];
                    @endphp
                    @foreach($rows as [$label, $value])
                    <tr style="border-bottom:1px solid #f8fafc;">
                        <td style="padding:8px 0;color:#64748b;font-weight:500;width:40%;vertical-align:top;font-size:12px;">{{ $label }}</td>
                        <td style="padding:8px 0;color:#1e293b;vertical-align:top;">{!! $value !!}</td>
                    </tr>
                    @endforeach
                    {{-- Incoming Stages (shown in print too) --}}
                    @if(!empty($material->incoming_stages) && count($material->incoming_stages) > 0)
                    <tr>
                        <td style="padding:10px 0 4px;color:#64748b;font-weight:500;vertical-align:top;font-size:12px;">Tahap Kedatangan</td>
                        <td style="padding:10px 0 4px;">
                            <div style="display:flex;flex-direction:column;gap:5px;">
                            @foreach($material->incoming_stages as $stg)
                                @php $isReceived = ($stg['status'] ?? 'received') === 'received'; @endphp
                                <div style="display:flex;align-items:center;justify-content:space-between;padding:4px 8px;border-radius:4px;font-size:11.5px;background:#f8fafc;border:1px solid #e2e8f0;color:#334155;">
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
                                        {{ number_format((float)($stg['qty'] ?? 0), 0, ',', '.') }} {{ $material->unit?->abbreviation ?? '' }}
                                    </div>
                                </div>
                            @endforeach
                            </div>
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        </div>

    @push('styles')
    <style>
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

            .card {
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
