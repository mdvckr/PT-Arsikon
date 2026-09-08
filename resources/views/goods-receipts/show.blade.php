<x-app-layout>
    <x-slot name="title">Detail Penerimaan: {{ $goodsReceipt->receipt_number }}</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('goods-receipts.index') }}">Penerimaan Barang</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>{{ $goodsReceipt->receipt_number }}</span>
    </div>

    <div class="grid" style="grid-template-columns:1fr 300px;gap:20px;align-items:start;">

        {{-- Items Table --}}
        <div class="card">
            <div class="card-header">
                <i class="fas fa-list text-primary"></i>
                <span class="card-title">Item Barang Diterima</span>
                @if($goodsReceipt->status === 'draft')
                @can('confirm goods receipts')
                <form method="POST" action="{{ route('goods-receipts.confirm', $goodsReceipt) }}">
                    @csrf
                    <button type="submit" class="btn btn-success"
                        onclick="return confirm('Konfirmasi penerimaan ini? Stok akan diperbarui.')">
                        <i class="fas fa-check-circle"></i> Konfirmasi
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
                            <th>Qty</th>
                            <th>Satuan</th>
                            <th>Harga Satuan</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $total = 0; @endphp
                        @foreach($goodsReceipt->items as $item)
                        @php $subtotal = $item->quantity * $item->unit_price; $total += $subtotal; @endphp
                        <tr>
                            <td>
                                <div class="fw-600">{{ $item->material?->name }}</div>
                                <div class="text-muted" style="font-size:11.5px;">{{ $item->material?->code }}</div>
                            </td>
                            <td>{{ $item->material?->category?->name ?? '-' }}</td>
                            <td class="fw-600">{{ number_format($item->quantity, 2) }}</td>
                            <td>{{ $item->material?->unit?->abbreviation }}</td>
                            <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="fw-600">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background:#f8fafc;">
                            <td colspan="5" style="padding:12px 14px;text-align:right;font-weight:700;color:#0f172a;">Total Nilai</td>
                            <td style="padding:12px 14px;font-weight:800;color:#2563eb;font-size:15px;">
                                Rp {{ number_format($total, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Header Info --}}
        <div class="card">
            <div class="card-header">
                <i class="fas fa-file-alt text-primary"></i>
                <span class="card-title">Informasi</span>
            </div>
            <div class="card-body">
                @php
                    $rows = [
                        ['No. Tanda Terima', $goodsReceipt->receipt_number],
                        ['Supplier', $goodsReceipt->supplier?->name ?? '-'],
                        ['Gudang', $goodsReceipt->warehouse?->name ?? '-'],
                        ['Tgl. Terima', $goodsReceipt->received_at ? \Carbon\Carbon::parse($goodsReceipt->received_at)->format('d/m/Y') : '-'],
                        ['No. Invoice', $goodsReceipt->invoice_number ?? '-'],
                        ['Dibuat Oleh', $goodsReceipt->creator?->name ?? '-'],
                    ];
                @endphp
                @foreach($rows as [$label, $value])
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:13.5px;">
                    <span class="text-muted">{{ $label }}</span>
                    <span class="fw-600">{{ $value }}</span>
                </div>
                @endforeach

                <div style="margin-top:12px;">
                    @if($goodsReceipt->status === 'confirmed')
                        <span class="badge badge-success" style="font-size:13px;padding:6px 14px;">
                            <i class="fas fa-check-circle"></i> Dikonfirmasi
                        </span>
                    @else
                        <span class="badge badge-warning" style="font-size:13px;padding:6px 14px;">
                            <i class="fas fa-clock"></i> Draft
                        </span>
                    @endif
                </div>

                @if($goodsReceipt->notes)
                <div style="margin-top:12px;padding:12px;background:#f8fafc;border-radius:8px;font-size:13px;color:#475569;">
                    <strong>Catatan:</strong><br>{{ $goodsReceipt->notes }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
