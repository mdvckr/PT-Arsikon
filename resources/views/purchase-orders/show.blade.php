<x-app-layout>
    <x-slot name="title">Detail PO: {{ $purchaseOrder->po_number }}</x-slot>

    <div class="flex justify-between items-center mb-4">
        <div>
            <a href="{{ route('purchase-orders.index') }}" class="text-muted" style="font-size:13px;">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar PO
            </a>
            <h2 style="font-size:20px;font-weight:700;color:#0f172a;margin-top:4px;">
                Purchase Order {{ $purchaseOrder->po_number }}
            </h2>
            <p class="text-muted" style="font-size:13px;">Supplier: <strong>{{ $purchaseOrder->display_supplier_name }}</strong>
                @if($purchaseOrder->isManualSupplier())
                    <span class="badge" style="background:#fef3c7;color:#92400e;font-size:10.5px;font-weight:500;margin-left:4px;">Vendor Bebas / Manual</span>
                @endif
            </p>
        </div>

        <div class="flex gap-2 items-center">
            <a href="{{ route('purchase-receipts.create', ['po_id' => $purchaseOrder->id]) }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Catat Nota Harian Baru
            </a>

            @if($purchaseOrder->status === 'draft')
                <form action="{{ route('purchase-orders.send', $purchaseOrder) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success" onclick="return confirm('Kirim PO ini ke Supplier?')">
                        <i class="fas fa-paper-plane me-1"></i> Kirim ke Supplier
                    </button>
                </form>
            @endif

            @if($purchaseOrder->status !== 'cancelled')
                <form action="{{ route('purchase-orders.cancel', $purchaseOrder) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Batalkan PO ini?')">
                        Batalkan
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4" style="background:#ecfdf5;border:1px solid #10b981;color:#065f46;padding:12px 16px;border-radius:8px;">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif

    <!-- Section Header PO -->
    <div class="card mb-4" style="padding:20px;">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4" style="font-size:14px;">
            <div>
                <span class="text-muted block text-xs">No. PO:</span>
                <code>{{ $purchaseOrder->po_number }}</code>
            </div>
            <div>
                <span class="text-muted block text-xs">Tanggal Order:</span>
                <strong>{{ $purchaseOrder->order_date ? $purchaseOrder->order_date->format('d M Y') : '-' }}</strong>
            </div>
            <div>
                <span class="text-muted block text-xs">Status PO:</span>
                <span class="badge badge-{{ $purchaseOrder->status_color }}">{{ $purchaseOrder->status_label }}</span>
            </div>
            <div>
                <span class="text-muted block text-xs">Total Nilai PO:</span>
                <strong class="text-primary" style="font-size:16px;">Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</strong>
            </div>
        </div>
    </div>

    <!-- Section Barang yang Dipesan -->
    <div class="card mb-6" style="padding:20px;">
        <h3 style="font-size:16px;font-weight:600;margin-bottom:16px;color:#1e293b;">Barang yang Dipesan (Item PO)</h3>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Material</th>
                        <th class="text-center">Jumlah Dipesan</th>
                        <th class="text-end">Harga Satuan</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseOrder->items as $idx => $item)
                    <tr>
                        <td class="text-muted">{{ $idx + 1 }}</td>
                        <td>
                            <div>
                                <strong>{{ $item->displayName() }}</strong>
                                <span class="text-muted text-xs ms-1">({{ $item->displayUnit() }})</span>
                            </div>
                            @if($item->materialRequestItem && $item->materialRequestItem->materialRequest)
                                <div class="mt-1">
                                    <span class="badge" style="background:#e0f2fe;color:#0369a1;font-size:11px;font-weight:500;">
                                        <i class="fas fa-link me-1"></i> Dari Permintaan: #{{ $item->materialRequestItem->materialRequest->request_number }} ({{ $item->materialRequestItem->materialRequest->fromWarehouse?->name }})
                                    </span>
                                </div>
                            @elseif($item->isCustom())
                                <div class="mt-1">
                                    <span class="badge" style="background:#fef3c7;color:#92400e;font-size:11px;font-weight:500;">
                                        <i class="fas fa-tag me-1"></i> Barang Baru / Pengadaan Khusus
                                    </span>
                                </div>
                            @endif
                        </td>
                        <td class="text-center">
                            {{ number_format($item->quantity, 2, ',', '.') }} {{ $item->displayUnit() }}
                        </td>
                        <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="text-end"><strong class="text-primary">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- SECTION UTAMA: Riwayat Nota Pembelian Harian (Hari 1, Hari 2, dst) -->
    <div class="card mb-6" style="padding:20px;border:2px solid #e0f2fe;background:#f8fafc;">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h3 style="font-size:17px;font-weight:700;color:#0369a1;" class="flex items-center gap-2">
                    <i class="fas fa-receipt text-sky-600"></i> Riwayat Nota Pembelian Harian (Hari 1, Hari 2, dst.)
                </h3>
                <p class="text-muted" style="font-size:13px;">Daftar pengeluaran dan nota fisik harian yang dicatat untuk PO ini.</p>
            </div>
            <a href="{{ route('purchase-receipts.create', ['po_id' => $purchaseOrder->id]) }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Tambah Nota Hari Ini
            </a>
        </div>

        <div class="table-wrap">
            <table class="data-table" style="background:#fff;">
                <thead>
                    <tr>
                        <th>Tgl Nota</th>
                        <th>No. Nota Fisik</th>
                        <th>Supplier / Toko</th>
                        <th>Status Bayar</th>
                        <th>Jml Item</th>
                        <th>Total Pembelian Nota</th>
                        <th>Foto/Scan Nota</th>
                        <th>Dicatat Oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseOrder->receipts as $receipt)
                    <tr>
                        <td><strong>{{ $receipt->receipt_date ? $receipt->receipt_date->format('d M Y') : '-' }}</strong></td>
                        <td><code>{{ $receipt->receipt_number ?? '-' }}</code></td>
                        <td><strong>{{ $receipt->supplier_name ?? $receipt->supplier?->name ?? '-' }}</strong></td>
                        <td>
                            <span class="badge badge-{{ $receipt->status_color }}">
                                {{ $receipt->status_label }}
                            </span>
                        </td>
                        <td><span class="badge badge-secondary">{{ $receipt->items->count() }} item</span></td>
                        <td><strong class="text-success">Rp {{ number_format($receipt->total_amount, 0, ',', '.') }}</strong></td>
                        <td>
                            @if($receipt->image_path)
                                <a href="{{ asset('storage/' . $receipt->image_path) }}" target="_blank" class="btn btn-xs btn-outline-info" style="font-size:11px;padding:2px 6px;">
                                    <i class="fas fa-file-image me-1"></i> Lihat Foto
                                </a>
                            @else
                                <span class="text-muted" style="font-size:12px;">Tanpa Foto</span>
                            @endif
                        </td>
                        <td class="text-muted" style="font-size:12px;">{{ $receipt->creator?->name ?? '-' }}</td>
                        <td>
                            <a href="{{ route('purchase-receipts.show', $receipt) }}" class="btn btn-sm btn-secondary btn-icon" title="Detail Nota">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Belum ada nota pembelian harian yang dicatat untuk PO ini.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="background:#f1f5f9;font-weight:bold;">
                        <td colspan="4" class="text-end" style="font-size:14px;">TOTAL AKUMULASI NOTA HARIAN:</td>
                        <td colspan="4" style="font-size:16px;color:#16a34a;">
                            Rp {{ number_format($purchaseOrder->receipts ? $purchaseOrder->receipts->sum('total_amount') : 0, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-app-layout>
