<x-app-layout>
    <x-slot name="title">Detail GR: {{ $goodsReceipt->receipt_number }}</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('goods-receipts.index') }}">Penerimaan Barang</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>{{ $goodsReceipt->receipt_number }}</span>
    </div>

    <div class="grid" style="grid-template-columns:1fr 320px;gap:20px;align-items:start;">

        {{-- ── Items Table ── --}}
        <div class="card">
            <div class="card-header">
                <i class="fas fa-list text-primary"></i>
                <span class="card-title">Item Barang Diterima</span>

                @if(!$goodsReceipt->isConfirmed())
                @can('confirm goods receipts')
                <form method="POST" action="{{ route('goods-receipts.confirm', $goodsReceipt) }}">
                    @csrf
                    <button type="submit" class="btn btn-success"
                        onclick="return confirm('Konfirmasi penerimaan ini? Stok gudang akan diperbarui.')">
                        <i class="fas fa-check-circle"></i> Konfirmasi & Update Stok
                    </button>
                </form>
                @endcan
                @endif
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Material</th>
                            <th>Kategori</th>
                            <th class="text-right">Qty</th>
                            <th>Satuan</th>
                            <th class="text-right">Harga Satuan</th>
                            <th class="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $total = 0; @endphp
                        @foreach($goodsReceipt->items as $item)
                        @php
                            $qty      = (float) $item->qty_received;
                            $price    = (float) $item->unit_price;
                            $subtotal = $qty * $price;
                            $total   += $subtotal;
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-600">{{ $item->material?->name }}</div>
                                <div class="text-muted" style="font-size:11.5px;">{{ $item->material?->code }}</div>
                            </td>
                            <td class="text-muted">{{ $item->material?->category?->name ?? '—' }}</td>
                            <td class="fw-600 text-right">{{ number_format($qty, 2, ',', '.') }}</td>
                            <td>{{ $item->material?->unit?->abbreviation ?? '—' }}</td>
                            <td class="text-right">Rp {{ number_format($price, 0, ',', '.') }}</td>
                            <td class="fw-600 text-right">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background:#f8fafc;">
                            <td colspan="5" style="padding:12px 14px;text-align:right;font-weight:700;color:#0f172a;">
                                Total Nilai
                            </td>
                            <td style="padding:12px 14px;font-weight:800;color:#2563eb;font-size:15px;text-align:right;">
                                Rp {{ number_format($total, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- ── Info Sidebar ── --}}
        <div>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-file-alt text-primary"></i>
                    <span class="card-title">Informasi</span>
                </div>
                <div class="card-body">
                    @php
                        $rows = [
                            ['No. Tanda Terima', $goodsReceipt->receipt_number],
                            ['No. Invoice', $goodsReceipt->invoice_number ?? '—'],
                            ['Supplier', $goodsReceipt->supplier?->name ?? '—'],
                            ['Gudang Tujuan', $goodsReceipt->warehouse?->name ?? '—'],
                            ['Tgl. Terima',
                                $goodsReceipt->receipt_date
                                    ? \Carbon\Carbon::parse($goodsReceipt->receipt_date)->format('d/m/Y')
                                    : '—'
                            ],
                            ['Dibuat Oleh', $goodsReceipt->creator?->name ?? '—'],
                        ];
                        if ($goodsReceipt->purchaseOrder) {
                            $rows[] = ['Purchase Order', $goodsReceipt->purchaseOrder->po_number];
                        }
                    @endphp

                    @foreach($rows as [$label, $value])
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:13.5px;">
                        <span class="text-muted">{{ $label }}</span>
                        <span class="fw-600 text-right" style="max-width:60%;">{{ $value }}</span>
                    </div>
                    @endforeach

                    {{-- Status Badge --}}
                    <div style="margin-top:14px;">
                        @if($goodsReceipt->isConfirmed())
                            <span class="badge badge-success" style="font-size:13px;padding:6px 14px;">
                                <i class="fas fa-check-circle"></i> Dikonfirmasi
                            </span>
                            <div class="text-muted mt-1" style="font-size:12px;">
                                oleh {{ $goodsReceipt->confirmedBy?->name ?? '—' }}
                                @if($goodsReceipt->confirmed_at)
                                    — {{ \Carbon\Carbon::parse($goodsReceipt->confirmed_at)->format('d/m/Y H:i') }}
                                @endif
                            </div>
                        @else
                            <span class="badge badge-warning" style="font-size:13px;padding:6px 14px;">
                                <i class="fas fa-clock"></i> Draft — Stok belum diupdate
                            </span>
                        @endif
                    </div>

                    @if($goodsReceipt->notes)
                    <div style="margin-top:14px;padding:12px;background:#f8fafc;border-radius:8px;font-size:13px;color:#475569;">
                        <strong>Catatan:</strong><br>{{ $goodsReceipt->notes }}
                    </div>
                    @endif
                </div>
            </div>

            {{-- Link ke Daily Log hari itu --}}
            <a href="{{ route('daily-log.index', [
                    'warehouse_id' => $goodsReceipt->warehouse_id,
                    'date' => $goodsReceipt->receipt_date?->format('Y-m-d'),
                ]) }}"
                class="btn btn-secondary w-full" style="justify-content:center;">
                <i class="fas fa-calendar-check"></i> Lihat Daily Log Hari Ini
            </a>
        </div>

    </div>
</x-app-layout>
