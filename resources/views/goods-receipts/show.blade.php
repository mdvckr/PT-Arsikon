<x-app-layout>
    <x-slot name="title">Penerimaan Barang: {{ $goodsReceipt->receipt_number }}</x-slot>

    @push('styles')
    <style>
        .gr-meta-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }

        .gr-meta-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.2s ease;
        }

        .gr-meta-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .gr-meta-label {
            font-size: 11.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .gr-meta-value {
            font-size: 13.5px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.4;
            word-break: break-word;
        }

        .gr-meta-sub {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .gr-items-table th {
            padding: 11px 14px !important;
            font-size: 11.5px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.4px !important;
            color: #475569 !important;
            background: #f8fafc !important;
            border-bottom: 1px solid #e2e8f0 !important;
            white-space: nowrap !important;
        }

        .gr-items-table td {
            padding: 13px 14px !important;
            vertical-align: middle !important;
            font-size: 12.5px !important;
            border-bottom: 1px solid #f1f5f9 !important;
        }

        .gr-items-table tbody tr:hover td {
            background-color: #f8fafc !important;
        }

        @media (max-width: 1024px) {
            .gr-meta-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .gr-meta-grid {
                grid-template-columns: 1fr;
            }
            .gr-header-actions {
                flex-direction: column;
                align-items: stretch !important;
                gap: 10px;
            }
            .gr-header-actions > * {
                width: 100%;
            }
        }
    </style>
    @endpush

    {{-- Breadcrumb Navigasi --}}
    <div class="breadcrumb mb-3" style="font-size:12px;color:#64748b;">
        <a href="{{ route('goods-receipts.index') }}" class="text-slate-500 hover:text-slate-800" style="text-decoration:none;">Logistik & Penerimaan</a>
        <span class="breadcrumb-sep mx-1.5"><x-lucide-chevron-right style="width:12px;height:12px;display:inline;" /></span>
        <span class="font-semibold text-slate-800">{{ $goodsReceipt->receipt_number }}</span>
    </div>

    {{-- ── 1. Clean Header Hero Bar (Single Action Location) ── --}}
    <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-5 shadow-sm mb-4 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3.5 flex-wrap">
            <a href="{{ route('goods-receipts.index') }}" class="btn btn-light border btn-sm text-slate-600 hover:text-slate-900 shadow-sm" style="border-radius:8px;padding:6px 12px;font-size:12px;font-weight:600;" title="Kembali ke Daftar Penerimaan">
                <x-lucide-arrow-left class="me-1 w-4 h-4" /> Kembali
            </a>
            
            <div class="flex items-center gap-2.5 flex-wrap">
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight m-0">
                    {{ $goodsReceipt->receipt_number }}
                </h1>

                @if($goodsReceipt->isConfirmed())
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-sm">
                        <x-lucide-circle-check class="w-3.5 h-3.5 text-emerald-600" /> Dikonfirmasi
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 shadow-sm">
                        <x-lucide-clock class="w-3.5 h-3.5 text-amber-600" /> Draft (Menunggu Konfirmasi)
                    </span>
                @endif
            </div>
        </div>

        {{-- Single Action Buttons Area --}}
        <div class="flex items-center gap-2.5 gr-header-actions">
            @if(!$goodsReceipt->isConfirmed())
                @can('create goods receipts')
                    <a href="{{ route('goods-receipts.edit', $goodsReceipt) }}" 
                       class="btn btn-light border btn-sm text-slate-700 shadow-sm"
                       style="border-radius:8px;padding:7px 14px;font-size:12.5px;font-weight:600;"
                       title="Ubah rincian sebelum dikonfirmasi">
                        <x-lucide-pencil class="me-1 w-4 h-4 text-slate-500" /> Edit Draft
                    </a>
                @endcan

                @if($goodsReceipt->canUserConfirm(auth()->user()))
                    {{-- SATU-SATUNYA TOMBOL KONFIRMASI UTAMA (Tanpa Duplikasi) --}}
                    <form method="POST" action="{{ route('goods-receipts.confirm', $goodsReceipt) }}" style="margin:0;"
                          data-confirm="Konfirmasi penerimaan barang/alat ini? Stok fisik di gudang {{ $goodsReceipt->warehouse?->name }} akan otomatis diperbarui dan bertambah."
                          data-confirm-title="Konfirmasi Penerimaan Barang"
                          data-confirm-btn="Ya, Konfirmasi & Update Stok">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm shadow-md"
                            style="border-radius:8px;padding:7px 18px;font-size:12.5px;font-weight:700;background:#059669;border-color:#059669;box-shadow:0 3px 6px -1px rgba(5,150,105,0.3);">
                            <x-lucide-circle-check class="me-1.5 w-4 h-4" /> Konfirmasi &amp; Update Stok
                        </button>
                    </form>
                @endif
            @endif
        </div>
    </div>

    {{-- Notifikasi Flash Pesan --}}
    @if (session('success'))
    <div class="alert alert-success mb-4 rounded-xl shadow-sm border border-emerald-300 bg-emerald-50 text-emerald-800 p-3.5 text-xs flex items-center gap-2">
        <x-lucide-circle-check class="w-4 h-4 text-emerald-600 flex-shrink-0" />
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger mb-4 rounded-xl shadow-sm border border-red-300 bg-red-50 text-red-800 p-3.5 text-xs flex items-center gap-2">
        <x-lucide-circle-alert class="w-4 h-4 text-red-600 flex-shrink-0" />
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- ── 2. Draft Status Banner (Tanpa Tombol Ganda, Murni Informasi) ── --}}
    @if(!$goodsReceipt->isConfirmed())
    <div class="mb-4 bg-amber-50/80 border border-amber-200/90 rounded-xl p-3.5 sm:p-4 text-amber-900 shadow-sm flex items-start sm:items-center justify-between gap-3">
        <div class="flex items-start sm:items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-amber-200/70 text-amber-800 flex items-center justify-center flex-shrink-0 mt-0.5 sm:mt-0">
                <x-lucide-info class="w-4 h-4" />
            </div>
            <div class="text-xs sm:text-sm">
                <strong class="font-bold text-amber-950">Status Dokumen: Draft (Stok Belum Masuk)</strong>
                <p class="text-amber-800 text-xs m-0 mt-0.5">
                    Kuantitas fisik di <strong class="text-amber-950">{{ $goodsReceipt->warehouse?->name }}</strong> belum bertambah ke buku inventori hingga dokumen ini dikonfirmasi.
                </p>
            </div>
        </div>

        @if(!$goodsReceipt->canUserConfirm(auth()->user()))
            <div class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-white/70 border border-amber-300 text-[11px] font-semibold text-amber-800 flex-shrink-0">
                <x-lucide-lock class="w-3.5 h-3.5" /> Konfirmasi oleh Petugas Gudang
            </div>
        @endif
    </div>
    @endif

    {{-- ── 3. Ringkasan Dokumen (Grid Metadata 4-Kolom yang Rapi & Tidak Duplikasi) ── --}}
    <div class="gr-meta-grid mb-4">
        
        {{-- Card 1: Supplier & Bukti Surat Jalan --}}
        <div class="gr-meta-card">
            <div>
                <div class="gr-meta-label">
                    <x-lucide-truck class="w-3.5 h-3.5 text-primary" /> Supplier &amp; Pengiriman
                </div>
                <div class="gr-meta-value text-slate-900">
                    {{ $goodsReceipt->supplier?->name ?? '— Pembelian Bebas / Non-Vendor —' }}
                </div>
                <div class="gr-meta-sub">
                    <span class="text-slate-500">No. Surat Jalan / Nota:</span>
                    <strong class="text-slate-700">{{ $goodsReceipt->invoice_number ?: '—' }}</strong>
                </div>
            </div>
            <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-500 font-medium">Bukti Fisik SJ:</span>
                @if($goodsReceipt->attachment_path)
                    <a href="{{ $goodsReceipt->attachment_url }}" target="_blank" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 text-xs font-semibold transition" title="Buka lampiran foto/scan surat jalan">
                        <x-lucide-file-image class="w-3.5 h-3.5 text-blue-600" /> Lihat Foto SJ
                    </a>
                @else
                    <span class="text-xs text-slate-400 font-normal">Tanpa Lampiran</span>
                @endif
            </div>
        </div>

        {{-- Card 2: Gudang & Lokasi Penerima --}}
        <div class="gr-meta-card">
            <div>
                <div class="gr-meta-label">
                    <x-lucide-warehouse class="w-3.5 h-3.5 text-primary" /> Gudang Penerima
                </div>
                <div class="gr-meta-value text-slate-900 flex items-center gap-2">
                    <span>{{ $goodsReceipt->warehouse?->name ?? '—' }}</span>
                    @if($goodsReceipt->warehouse?->is_central)
                        <span class="px-2 py-0.5 rounded text-[10.5px] font-bold bg-blue-100 text-blue-700 border border-blue-200">Gudang Pusat</span>
                    @else
                        <span class="px-2 py-0.5 rounded text-[10.5px] font-bold bg-orange-100 text-orange-700 border border-orange-200">Proyek</span>
                    @endif
                </div>
                <div class="gr-meta-sub">
                    <span class="text-slate-500">Proyek:</span>
                    <strong class="text-slate-700">{{ $goodsReceipt->warehouse?->project?->name ?? 'Operasional Pusat' }}</strong>
                </div>
            </div>
            <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-500 font-medium">Tanggal Masuk:</span>
                <strong class="text-xs text-slate-800">
                    {{ $goodsReceipt->receipt_date ? \Carbon\Carbon::parse($goodsReceipt->receipt_date)->translatedFormat('d F Y') : '—' }}
                </strong>
            </div>
        </div>

        {{-- Card 3: Referensi Dokumen PO / MR --}}
        @php
            $linkedMr = $goodsReceipt->materialRequest ?? $goodsReceipt->purchaseOrder?->items?->map(fn($it) => $it->materialRequestItem?->materialRequest)->filter()->first();
        @endphp
        <div class="gr-meta-card">
            <div>
                <div class="gr-meta-label">
                    <x-lucide-file-text class="w-3.5 h-3.5 text-primary" /> Dokumen Referensi
                </div>
                <div class="gr-meta-value">
                    @if($goodsReceipt->purchaseOrder)
                        <a href="{{ route('purchase-orders.show', $goodsReceipt->purchaseOrder) }}" class="inline-flex items-center gap-1 text-primary hover:text-blue-800" title="Buka Detail PO">
                            <x-lucide-shopping-cart class="w-3.5 h-3.5 text-blue-600" />
                            #{{ $goodsReceipt->purchaseOrder->po_number }}
                        </a>
                    @else
                        <span class="text-slate-400 font-medium text-xs">— Non-PO (Langsung) —</span>
                    @endif
                </div>
                <div class="gr-meta-sub">
                    <span class="text-slate-500">PO Lapangan:</span>
                    @if($linkedMr)
                        <a href="{{ route('material-requests.show', $linkedMr) }}" class="font-semibold text-primary hover:text-blue-800 inline-flex items-center gap-1" title="Buka PO Lapangan">
                            <x-lucide-clipboard-check class="w-3.5 h-3.5 text-blue-500" />
                            #{{ $linkedMr->request_number }}
                        </a>
                    @else
                        <span class="text-slate-400">—</span>
                    @endif
                </div>
            </div>
            <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-500 font-medium">Total Item:</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                    {{ $goodsReceipt->items->count() }} Jenis
                </span>
            </div>
        </div>

        {{-- Card 4: Petugas & Verifikasi (Diringkas, Tanpa Duplikasi) --}}
        @php
            $penerima = $goodsReceipt->received_by_name ?? $goodsReceipt->receivedBy?->name ?? '—';
            $pencatat = $goodsReceipt->creator?->name ?? '—';
            $isSamePerson = ($penerima !== '—' && $penerima === $pencatat);
        @endphp
        <div class="gr-meta-card">
            <div>
                <div class="gr-meta-label">
                    <x-lucide-user-check class="w-3.5 h-3.5 text-primary" /> Petugas &amp; Konfirmasi
                </div>
                <div class="gr-meta-value text-slate-900">
                    @if($isSamePerson)
                        {{ $penerima }}
                        <div class="text-[11px] font-normal text-slate-500 mt-0.5">(Penerima &amp; Pencatat Dokumen)</div>
                    @else
                        <div class="text-xs text-slate-600 font-normal">Penerima: <strong class="text-slate-900">{{ $penerima }}</strong></div>
                        <div class="text-xs text-slate-600 font-normal mt-0.5">Dicatat: <strong class="text-slate-900">{{ $pencatat }}</strong></div>
                    @endif
                </div>
            </div>
            <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                @if($goodsReceipt->isConfirmed())
                    <span class="text-slate-500 font-medium">Dikonfirmasi:</span>
                    <span class="text-slate-800 font-semibold" title="Waktu: {{ $goodsReceipt->confirmed_at ? \Carbon\Carbon::parse($goodsReceipt->confirmed_at)->format('d/m/Y H:i') : '' }}">
                        {{ $goodsReceipt->confirmedBy?->name ?? 'Sistem' }}
                    </span>
                @else
                    <span class="text-slate-500 font-medium">Status Verifikasi:</span>
                    <span class="text-amber-700 font-semibold">Menunggu Approval</span>
                @endif
            </div>
        </div>

    </div>

    {{-- ── 4. Main Items Table (Full Width, Lega, Visual Hierarchy Jelas) ── --}}
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mb-4">
        <div class="bg-slate-50/70 border-b border-slate-200 px-5 py-3.5 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center font-bold">
                    <x-lucide-boxes class="w-4 h-4" />
                </div>
                <div>
                    <h3 class="font-bold text-sm sm:text-base text-slate-800 m-0">Daftar Item Barang Masuk</h3>
                    <p class="text-xs text-slate-500 m-0">Rincian fisik dan status pengecekan kondisi material/alat saat diterima</p>
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                    {{ $goodsReceipt->items->count() }} Jenis Item
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full data-table gr-items-table mb-0 text-left border-collapse">
                <thead>
                    <tr>
                        <th style="width:50px;text-align:center;">No</th>
                        <th style="width:100px;">Tipe</th>
                        <th>Nama Barang &amp; Spesifikasi</th>
                        <th style="width:140px;">Kategori</th>
                        <th style="text-align:center;width:110px;">Qty Kirim</th>
                        <th style="text-align:center;width:130px;">Diterima (Baik)</th>
                        <th style="text-align:center;width:100px;">Rusak</th>
                        <th style="text-align:center;width:140px;">Selisih / Hilang</th>
                        <th style="width:90px;text-align:center;">Satuan</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalReceivedSum = 0;
                        $totalDamagedSum  = 0;
                        $totalMissingSum  = 0;
                    @endphp
                    @forelse($goodsReceipt->items as $idx => $item)
                    @php
                        $isTool   = $item->isTool();
                        $qty      = (float) $item->qty_received;
                        $itemName = $item->item_name;
                        $itemCode = $item->item_code;
                        $categoryName = $item->category?->name ?? ($isTool ? ($item->tool?->category?->name ?? 'Alat / Mesin') : ($item->material?->category?->name ?? 'Umum'));
                        $unitName = $item->item_unit;
                        
                        $totalReceivedSum += $qty;
                        $totalDamagedSum  += (float) $item->qty_damaged;
                        $totalMissingSum  += (float) $item->qty_missing;
                    @endphp
                    <tr>
                        <td style="text-align:center;color:#64748b;font-weight:600;">
                            {{ $idx + 1 }}
                        </td>
                        <td>
                            @if($isTool)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                    <x-lucide-hard-hat class="w-3.5 h-3.5 text-amber-600" /> Alat
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold bg-blue-50 text-blue-800 border border-blue-200">
                                    <x-lucide-package class="w-3.5 h-3.5 text-blue-600" /> Material
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="font-bold text-slate-900 text-sm tracking-tight">{{ $itemName }}</div>
                            <div class="text-slate-500 text-xs mt-1 flex items-center gap-2 flex-wrap">
                                @if($itemCode && $itemCode !== '-')
                                    <code class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 text-[11px] font-mono border border-slate-200 flex items-center gap-1">
                                        <x-lucide-barcode class="w-3 h-3 text-slate-500" /> {{ $itemCode }}
                                    </code>
                                @endif
                                @php
                                    $brand = $isTool ? $item->tool?->brand : $item->material?->brand;
                                    $size  = $isTool ? $item->tool?->size : $item->material?->size;
                                @endphp
                                @if($brand)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] bg-slate-100 text-slate-700 border border-slate-200">
                                        Merek: <strong class="text-slate-900">{{ $brand }}</strong>
                                    </span>
                                @endif
                                @if($size)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] bg-sky-50 text-sky-800 border border-sky-200">
                                        Ukuran: <strong class="text-sky-950">{{ $size }}</strong>
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="inline-block px-2.5 py-1 rounded text-xs bg-slate-100 text-slate-700 border border-slate-200 font-medium">
                                {{ $categoryName }}
                            </span>
                        </td>
                        <td style="text-align:center;font-size:13px;color:#475569;font-weight:600;">
                            {{ $item->qty_shipped > 0 ? format_quantity($item->qty_shipped) : '-' }}
                        </td>
                        <td style="text-align:center;">
                            <span class="inline-block px-3 py-1 rounded-lg text-sm font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
                                {{ format_quantity($qty) }}
                            </span>
                        </td>
                        <td style="text-align:center;">
                            @if($item->qty_damaged > 0)
                                <span class="inline-block px-2.5 py-0.5 rounded text-xs font-bold bg-red-100 text-red-800 border border-red-200">
                                    {{ format_quantity($item->qty_damaged) }}
                                </span>
                            @else
                                <span class="text-slate-300">-</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            @if($item->qty_missing > 0)
                                @php
                                    $isLost = ($item->missing_action === 'lost' || $item->missing_status === 'confirmed_lost');
                                @endphp
                                <span class="inline-block px-2.5 py-0.5 rounded text-xs font-bold {{ $isLost ? 'bg-red-100 text-red-800 border border-red-300' : 'bg-amber-100 text-amber-800 border border-amber-300' }}">
                                    {{ format_quantity($item->qty_missing) }}
                                </span>
                                <div class="text-[10.5px] mt-1 font-semibold {{ $isLost ? 'text-red-700' : 'text-amber-800' }}">
                                    {{ $item->missing_status === 'resolved' ? 'Terselesaikan' : ($isLost ? 'Dinyatakan Hilang' : 'Belum Lengkap') }}
                                </div>
                                @if($item->missing_status === 'unresolved' && auth()->user()->can('create transit losses'))
                                    <div class="mt-1.5">
                                        <button type="button" class="btn btn-outline-danger btn-xs" onclick="openDeclareLossModal({{ $item->id }}, '{{ addslashes($itemName) }}', {{ $item->qty_missing }}, '{{ $unitName }}')" style="font-size:10.5px;padding:2px 8px;border-radius:4px;font-weight:600;">
                                            <x-lucide-triangle-alert class="me-1 w-3 h-3" /> Nyatakan Hilang
                                        </button>
                                    </div>
                                @endif
                            @else
                                <span class="text-slate-300">-</span>
                            @endif
                        </td>
                        <td style="text-align:center;font-weight:700;color:#64748b;font-size:12px;">
                            {{ $unitName }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="text-align:center;color:#94a3b8;padding:32px 16px;">
                            <x-lucide-inbox class="w-8 h-8 text-slate-300 mx-auto mb-2" />
                            <p class="m-0 font-medium">Tidak ada rincian item barang pada penerimaan ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 font-bold border-t-2 border-slate-200 text-slate-700">
                        <td colspan="4" class="px-5 py-3 text-slate-700 font-bold text-xs uppercase tracking-wider">
                            Akumulasi Total: <span class="text-slate-900 font-black">{{ $goodsReceipt->items->count() }}</span> Jenis Barang
                        </td>
                        <td style="text-align:center;" class="text-slate-500 text-xs">
                            —
                        </td>
                        <td style="text-align:center;" class="text-emerald-700 font-black text-sm">
                            {{ format_quantity($totalReceivedSum) }}
                        </td>
                        <td style="text-align:center;" class="{{ $totalDamagedSum > 0 ? 'text-red-600 font-bold' : 'text-slate-400' }}">
                            {{ $totalDamagedSum > 0 ? format_quantity($totalDamagedSum) : '-' }}
                        </td>
                        <td style="text-align:center;" class="{{ $totalMissingSum > 0 ? 'text-amber-700 font-bold' : 'text-slate-400' }}">
                            {{ $totalMissingSum > 0 ? format_quantity($totalMissingSum) : '-' }}
                        </td>
                        <td style="text-align:center;" class="text-slate-400 text-xs">
                            —
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ── 5. Catatan Penerimaan (Bila Ada) ── --}}
    @if($goodsReceipt->notes)
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm mb-4">
        <div class="font-bold text-xs uppercase tracking-wider text-slate-500 mb-2 flex items-center gap-1.5">
            <x-lucide-quote class="w-3.5 h-3.5 text-slate-400" />
            Catatan Dokumen / Keterangan Khusus
        </div>
        <div class="text-xs sm:text-sm text-slate-800 bg-slate-50 border border-slate-200 rounded-lg p-3 leading-relaxed">
            {{ $goodsReceipt->notes }}
        </div>
    </div>
    @endif

    {{-- Modal: Nyatakan Hilang (Transit Loss) --}}
    <div id="declareLossModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.65);backdrop-filter:blur(4px);z-index:2500;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#ffffff;border-radius:14px;width:100%;max-width:520px;overflow:hidden;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);border:1px solid #e2e8f0;">
            <div style="padding:16px 20px;background:#f8fafc;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <x-lucide-triangle-alert class="text-danger w-5 h-5 text-red-600" />
                    <strong style="font-size:14px;color:#0f172a;">Nyatakan Barang Hilang (Transit Loss)</strong>
                </div>
                <button type="button" onclick="closeDeclareLossModal()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;line-height:1;">&times;</button>
            </div>
            <form method="POST" action="{{ route('transit-losses.store') }}" enctype="multipart/form-data" style="margin:0;">
                @csrf
                <input type="hidden" name="goods_receipt_item_id" id="modalGrItemId">
                <div style="padding:18px 20px;font-size:12.5px;">
                    <div style="background:#fef2f2;border:1px solid #fecaca;padding:12px 14px;border-radius:10px;margin-bottom:14px;">
                        <div style="font-weight:700;color:#991b1b;margin-bottom:2px;" id="modalItemTitle">Nama Barang</div>
                        <div style="color:#b91c1c;font-size:12px;">
                            Kuantitas Hilang/Selisih: <strong id="modalItemQty" class="font-black">0</strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" style="font-weight:600;font-size:12px;margin-bottom:4px;display:block;color:#334155;">
                            Bukti Foto / Dokumen Surat Jalan <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="evidence" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png,.webp" required style="font-size:12px;border-radius:6px;">
                        <span class="text-muted" style="font-size:11px;">Unggah foto fisik, berita acara, atau foto lembar surat jalan vendor (Max 10MB).</span>
                    </div>

                    <div class="mb-0">
                        <label class="form-label" style="font-weight:600;font-size:12px;margin-bottom:4px;display:block;color:#334155;">
                            Catatan & Keterangan Pengiriman <span class="text-danger">*</span>
                        </label>
                        <textarea name="notes" class="form-control" rows="3" required placeholder="Sebutkan keterangan sopir, nama armada/ekspedisi, nomor plat, dan kronologi..." style="font-size:12px;border-radius:6px;"></textarea>
                    </div>
                </div>
                <div style="padding:14px 20px;background:#f8fafc;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:8px;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="closeDeclareLossModal()" style="font-size:12px;border-radius:6px;">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm" style="font-size:12px;font-weight:700;border-radius:6px;">
                        <x-lucide-send class="me-1 w-3.5 h-3.5" /> Ajukan Laporan Kehilangan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openDeclareLossModal(itemId, itemName, qty, unit) {
        document.getElementById('modalGrItemId').value = itemId;
        document.getElementById('modalItemTitle').textContent = itemName;
        document.getElementById('modalItemQty').textContent = qty + ' ' + unit;
        var modal = document.getElementById('declareLossModal');
        modal.style.display = 'flex';
    }
    function closeDeclareLossModal() {
        var modal = document.getElementById('declareLossModal');
        modal.style.display = 'none';
    }
    </script>
    @endpush
</x-app-layout>
