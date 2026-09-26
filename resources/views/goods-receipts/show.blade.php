<x-app-layout>
    <x-slot name="title">Detail Penerimaan: {{ $goodsReceipt->receipt_number }}</x-slot>

    @push('styles')
    <style>
        .receipt-detail-layout {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 16px;
            align-items: start;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 7px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 12px;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #64748b;
            font-weight: 500;
            width: 130px;
            flex-shrink: 0;
            font-size: 11.5px;
        }

        .info-val {
            color: #0f172a;
            font-weight: 600;
            text-align: right;
            word-break: break-word;
            font-size: 12px;
        }

        .receipt-detail-table th {
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

        .receipt-detail-table td {
            padding: 9px 12px !important;
            vertical-align: middle !important;
            font-size: 12px !important;
        }

        @media (max-width: 992px) {
            .receipt-detail-layout {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .info-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 2px;
            }
            .info-label {
                width: 100%;
                font-size: 11px;
            }
            .info-val {
                text-align: left;
                width: 100%;
            }
            .page-actions-header {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 10px;
            }
        }
    </style>
    @endpush

    <div class="breadcrumb mb-2" style="font-size:12px;">
        <a href="{{ route('goods-receipts.index') }}">Penerimaan Barang</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:9px;"></i></span>
        <span>Detail #{{ $goodsReceipt->receipt_number }}</span>
    </div>

    {{-- Top Action & Header Bar --}}
    <div class="flex items-center justify-between mb-3 page-actions-header">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <a href="{{ route('goods-receipts.index') }}" class="btn btn-light border btn-sm" style="border-radius:6px;padding:5px 10px;font-size:11.5px;color:#475569;">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
            <div>
                <h2 class="fw-700" style="font-size:15px;color:#0f172a;margin:0;display:inline-flex;align-items:center;gap:6px;">
                    #{{ $goodsReceipt->receipt_number }}
                </h2>
            </div>
            @if($goodsReceipt->isConfirmed())
                <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:11px;padding:2px 8px;border-radius:12px;font-weight:600;">
                    <i class="fas fa-circle-check me-1"></i> Dikonfirmasi
                </span>
            @else
                <span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-size:11px;padding:2px 8px;border-radius:12px;font-weight:600;">
                    <i class="fas fa-clock me-1"></i> Draft (Stok Belum Masuk)
                </span>
            @endif
        </div>

        <div style="display:flex;align-items:center;gap:8px;">
            @if(!$goodsReceipt->isConfirmed())
                @can('confirm goods receipts')
                <form method="POST" action="{{ route('goods-receipts.confirm', $goodsReceipt) }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm"
                        onclick="return confirm('Konfirmasi penerimaan ini? Stok gudang akan otomatis diperbarui.')"
                        style="border-radius:6px;padding:6px 14px;font-size:12px;font-weight:600;box-shadow:0 2px 4px rgba(16,185,129,0.25);">
                        <i class="fas fa-circle-check me-1"></i> Konfirmasi & Update Stok
                    </button>
                </form>
                @endcan
            @endif

            <a href="{{ route('daily-log.index', [
                    'warehouse_id' => $goodsReceipt->warehouse_id,
                    'date' => $goodsReceipt->receipt_date?->format('Y-m-d') ?? date('Y-m-d'),
                ]) }}" 
                class="btn btn-light border btn-sm" 
                style="border-radius:6px;padding:6px 12px;font-size:11.5px;color:#475569;"
                title="Buka Daily Log">
                <i class="fas fa-calendar-check me-1"></i> Daily Log
            </a>
        </div>
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

    {{-- Main 2-Column Layout --}}
    <div class="receipt-detail-layout">

        {{-- ── Left: Items Table Card ── --}}
        <div>
            <div class="card mb-3" style="border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
                <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:10px 14px;display:flex;justify-content:space-between;align-items:center;">
                    <div style="display:flex;align-items:center;gap:6px;">
                        <i class="fas fa-boxes-stacked text-primary" style="font-size:13px;"></i>
                        <span class="card-title" style="font-size:13px;font-weight:700;color:#1e293b;">Daftar Item Barang Masuk</span>
                    </div>
                    <span class="badge" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;font-size:11px;padding:2px 7px;border-radius:12px;font-weight:600;">
                        {{ $goodsReceipt->items->count() }} Item
                    </span>
                </div>
                <div class="table-wrap">
                    <table class="data-table receipt-detail-table mb-0">
                        <thead>
                            <tr>
                                <th style="width:110px;">Tipe</th>
                                <th>Nama Barang / Item</th>
                                <th style="width:150px;">Kategori</th>
                                <th style="text-align:center;width:130px;">Qty Diterima</th>
                                <th style="width:90px;text-align:center;">Satuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($goodsReceipt->items as $item)
                            @php
                                $isTool   = $item->isTool();
                                $qty      = (float) $item->qty_received;
                                $itemName = $item->item_name;
                                $itemCode = $item->item_code;
                                $categoryName = $isTool ? ($item->tool?->category?->name ?? 'Alat / Mesin') : ($item->material?->category?->name ?? 'Umum');
                                $unitName = $item->item_unit;
                            @endphp
                            <tr>
                                <td>
                                    @if($isTool)
                                        <span class="badge" style="background:#fef3c7;color:#b45309;border:1px solid #fde68a;font-weight:700;">
                                            <i class="fas fa-helmet-safety"></i> Alat
                                        </span>
                                    @else
                                        <span class="badge" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;font-weight:700;">
                                            <i class="fas fa-box"></i> Material
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-700" style="color:#0f172a;font-size:13px;">{{ $itemName }}</div>
                                    <div class="text-muted" style="font-size:11px;margin-top:3px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                        @if($itemCode && $itemCode !== '-')
                                            <code style="background:#f1f5f9;color:#475569;padding:1px 5px;border-radius:3px;font-size:10.5px;"><i class="fas fa-barcode me-1"></i>{{ $itemCode }}</code>
                                        @endif
                                        @php
                                            $brand = $isTool ? $item->tool?->brand : $item->material?->brand;
                                            $size  = $isTool ? $item->tool?->size : $item->material?->size;
                                        @endphp
                                        @if($brand)
                                            <span class="badge" style="background:#f8fafc;color:#334155;border:1px solid #e2e8f0;font-size:10.5px;">Merek: <strong>{{ $brand }}</strong></span>
                                        @endif
                                        @if($size)
                                            <span class="badge" style="background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;font-size:10.5px;">Ukuran: <strong>{{ $size }}</strong></span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge" style="background:#f8fafc;color:#64748b;border:1px solid #e2e8f0;font-size:10.5px;padding:2px 6px;border-radius:4px;">
                                        {{ $categoryName }}
                                    </span>
                                </td>
                                <td style="text-align:center;font-weight:700;color:#0f172a;font-size:13.5px;">
                                    {{ $isTool ? number_format($qty, 0) : number_format($qty, 2, ',', '.') }}
                                </td>
                                <td style="text-align:center;">
                                    <span class="text-muted" style="font-size:11.5px;font-weight:600;">
                                        {{ $unitName }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" style="text-align:center;color:#94a3b8;padding:20px;">
                                    Tidak ada rincian item barang pada penerimaan ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr style="background:#f8fafc;border-top:1.5px solid #e2e8f0;">
                                <td colspan="5" style="text-align:left;font-weight:600;color:#64748b;padding:10px 14px;font-size:12px;">
                                    Total: <strong style="color:#0f172a;">{{ $goodsReceipt->items->count() }}</strong> jenis barang/alat diterima
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Notes Card if exists --}}
            @if($goodsReceipt->notes)
            <div class="card" style="border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);padding:12px 16px;">
                <div style="font-weight:700;font-size:12px;color:#475569;margin-bottom:4px;display:flex;align-items:center;gap:5px;">
                    <i class="fas fa-quote-left text-muted" style="font-size:10px;"></i>
                    Catatan Penerimaan:
                </div>
                <div style="font-size:12.5px;color:#1e293b;line-height:1.5;background:#f8fafc;padding:10px 12px;border-radius:6px;border:1px solid #f1f5f9;">
                    {{ $goodsReceipt->notes }}
                </div>
            </div>
            @endif
        </div>

        {{-- ── Right: Info Sidebar Card ── --}}
        <div>
            <div class="card mb-3" style="border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
                <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:10px 14px;display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-file-lines text-primary" style="font-size:12.5px;"></i>
                    <span class="card-title" style="font-size:13px;font-weight:700;color:#1e293b;">Informasi Penerimaan</span>
                </div>
                <div class="card-body" style="padding:10px 14px;">
                    
                    <div class="info-row">
                        <span class="info-label">No. Tanda Terima</span>
                        <span class="info-val" style="color:#2563eb;">{{ $goodsReceipt->receipt_number }}</span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">No. Invoice / Nota</span>
                        <span class="info-val">
                            @if($goodsReceipt->invoice_number)
                                <code style="font-size:11px;background:#f1f5f9;color:#334155;padding:1px 5px;border-radius:3px;">
                                    {{ $goodsReceipt->invoice_number }}
                                </code>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Purchase Order</span>
                        <span class="info-val">
                            @if($goodsReceipt->purchaseOrder)
                                <a href="{{ route('purchase-orders.show', $goodsReceipt->purchaseOrder) }}" style="color:#2563eb;text-decoration:none;font-weight:700;">
                                    #{{ $goodsReceipt->purchaseOrder->po_number }}
                                </a>
                            @else
                                <span class="text-muted" style="font-weight:normal;">— Non-PO —</span>
                            @endif
                        </span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Supplier</span>
                        <span class="info-val" style="display:inline-flex;align-items:center;gap:4px;">
                            <i class="fas fa-building text-muted" style="font-size:10px;"></i>
                            {{ $goodsReceipt->supplier?->name ?? '—' }}
                        </span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Gudang Tujuan</span>
                        <span class="info-val">
                            <span style="font-weight:600;color:#0f172a;">{{ $goodsReceipt->warehouse?->name ?? '—' }}</span>
                            @if($goodsReceipt->warehouse?->is_central)
                                <span class="badge" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;font-size:9.5px;padding:1px 5px;border-radius:4px;">Pusat</span>
                            @else
                                <span class="badge" style="background:#fff7ed;color:#c2410c;border:1px solid #fed7aa;font-size:9.5px;padding:1px 5px;border-radius:4px;">Proyek</span>
                            @endif
                        </span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Tanggal Diterima</span>
                        <span class="info-val">
                            {{ $goodsReceipt->receipt_date ? \Carbon\Carbon::parse($goodsReceipt->receipt_date)->format('d F Y') : '—' }}
                        </span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Dicatat Oleh</span>
                        <span class="info-val">{{ $goodsReceipt->creator?->name ?? '—' }}</span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Nama Penerima</span>
                        <span class="info-val" style="display:inline-flex;align-items:center;gap:4px;">
                            <i class="fas fa-user-check text-muted" style="font-size:10px;"></i>
                            {{ $goodsReceipt->received_by_name ?? $goodsReceipt->receivedBy?->name ?? '—' }}
                        </span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Status</span>
                        <span class="info-val">
                            @if($goodsReceipt->isConfirmed())
                                <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:10.5px;padding:1px 7px;border-radius:10px;">
                                    <i class="fas fa-check me-1"></i> Dikonfirmasi
                                </span>
                            @else
                                <span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-size:10.5px;padding:1px 7px;border-radius:10px;">
                                    <i class="fas fa-clock me-1"></i> Draft
                                </span>
                            @endif
                        </span>
                    </div>

                    @if($goodsReceipt->isConfirmed())
                    <div class="info-row">
                        <span class="info-label">Dikonfirmasi Oleh</span>
                        <span class="info-val">{{ $goodsReceipt->confirmedBy?->name ?? '—' }}</span>
                    </div>
                    @if($goodsReceipt->confirmed_at)
                    <div class="info-row">
                        <span class="info-label">Waktu Konfirmasi</span>
                        <span class="info-val" style="font-size:11.5px;color:#64748b;">
                            {{ \Carbon\Carbon::parse($goodsReceipt->confirmed_at)->format('d/m/Y H:i') }}
                        </span>
                    </div>
                    @endif
                    @endif

                </div>
            </div>

            {{-- Quick action card if draft --}}
            @if(!$goodsReceipt->isConfirmed())
            <div class="card" style="border:1px solid #fde68a;border-radius:10px;background:#fffbeb;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);padding:12px 14px;">
                <div style="font-size:11.5px;color:#92400e;display:flex;align-items:flex-start;gap:8px;line-height:1.4;">
                    <i class="fas fa-circle-info" style="font-size:13px;color:#d97706;margin-top:2px;flex-shrink:0;"></i>
                    <div>
                        <strong>Penerimaan masih berstatus Draft.</strong><br>
                        Stok inventori di gudang belum bertambah sampai penerimaan ini dikonfirmasi.
                    </div>
                </div>
                @if($goodsReceipt->canUserConfirm(auth()->user()))
                <form method="POST" action="{{ route('goods-receipts.confirm', $goodsReceipt) }}" style="margin-top:10px;">
                    @csrf
                    <button type="submit" class="btn btn-success w-full" 
                        onclick="return confirm('Konfirmasi penerimaan ini? Stok gudang akan otomatis diperbarui.')"
                        style="justify-content:center;font-size:12px;font-weight:600;padding:7px 12px;border-radius:6px;">
                        <i class="fas fa-check-circle me-1"></i> Konfirmasi Sekarang
                    </button>
                </form>
                @else
                <div style="margin-top:10px;padding:8px 10px;border-radius:6px;background:#fef2f2;border:1px solid #fecaca;font-size:11px;color:#991b1b;display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-lock"></i>
                    <span>Hanya petugas di <strong>{{ $goodsReceipt->warehouse?->name }}</strong> yang berhak mengonfirmasi penerimaan barang ini.</span>
                </div>
                @endif
            </div>
            @endif

        </div>

    </div>
</x-app-layout>
