<x-app-layout>
    <x-slot name="title">Detail Nota Pembelian</x-slot>

    <div class="flex justify-between items-center mb-4">
        <div>
            <a href="{{ route('purchase-receipts.index') }}" class="text-muted" style="font-size:13px;">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Nota
            </a>
            <h2 style="font-size:20px;font-weight:700;color:#0f172a;margin-top:4px;">
                Detail Nota Pembelian
            </h2>
            <p class="text-muted" style="font-size:13px;">Tanggal Nota: {{ $purchaseReceipt->receipt_date ? $purchaseReceipt->receipt_date->format('d F Y') : '-' }}</p>
        </div>
        <div class="flex gap-2">
            @if($purchaseReceipt->purchaseOrder)
                <a href="{{ route('purchase-orders.show', $purchaseReceipt->purchaseOrder) }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-file-invoice me-1"></i> Lihat PO terkait ({{ $purchaseReceipt->purchaseOrder->po_number }})
                </a>
            @endif
            <form action="{{ route('purchase-receipts.destroy', $purchaseReceipt) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus nota pembelian harian ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-trash me-1"></i> Hapus Nota
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4" style="background:#ecfdf5;border:1px solid #10b981;color:#065f46;padding:12px 16px;border-radius:8px;">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <!-- Card Informasii Utama -->
        <div class="md:col-span-2 card" style="padding:20px;">
            <h3 style="font-size:16px;font-weight:600;margin-bottom:16px;color:#1e293b;border-bottom:1px solid #f1f5f9;padding-bottom:10px;">
                <i class="fas fa-info-circle text-primary me-2"></i>Informasi Transaksi Nota
            </h3>

            <div class="grid grid-cols-2 gap-4" style="font-size:14px;">
                <div>
                    <span class="text-muted block text-xs">No. Nota Fisik:</span>
                    <code>{{ $purchaseReceipt->receipt_number ?? '-' }}</code>
                </div>
                <div>
                    <span class="text-muted block text-xs">Status Pembayaran:</span>
                    <span class="badge badge-{{ $purchaseReceipt->status_color }}">{{ $purchaseReceipt->status_label }}</span>
                </div>
                <div>
                    <span class="text-muted block text-xs">Supplier / Toko Pembelian:</span>
                    <strong>{{ $purchaseReceipt->supplier_name ?? $purchaseReceipt->supplier?->name ?? $purchaseReceipt->purchaseOrder?->supplier?->name ?? '-' }}</strong>
                </div>
                <div>
                    <span class="text-muted block text-xs">Terkait PO:</span>
                    @if($purchaseReceipt->purchaseOrder)
                        <a href="{{ route('purchase-orders.show', $purchaseReceipt->purchaseOrder) }}" class="text-primary font-bold">
                            {{ $purchaseReceipt->purchaseOrder->po_number }}
                        </a>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </div>
                <div>
                    <span class="text-muted block text-xs">Proyek:</span>
                    <span>{{ $purchaseReceipt->project_name ?? $purchaseReceipt->project?->name ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-muted block text-xs">Dicatat Oleh:</span>
                    <span>{{ $purchaseReceipt->creator?->name ?? '-' }} ({{ $purchaseReceipt->created_at->format('d M Y H:i') }})</span>
                </div>
            </div>

            @if($purchaseReceipt->notes)
            <div class="mt-4 pt-3" style="border-top:1px dashed #e2e8f0;">
                <span class="text-muted block text-xs">Catatan:</span>
                <p class="text-sm italic text-gray-700" style="margin-top:2px;">"{{ $purchaseReceipt->notes }}"</p>
            </div>
            @endif
        </div>

        <!-- Card Bukti Nota Fisik -->
        <div class="md:col-span-1 card" style="padding:20px;">
            <h3 style="font-size:16px;font-weight:600;margin-bottom:16px;color:#1e293b;border-bottom:1px solid #f1f5f9;padding-bottom:10px;">
                <i class="fas fa-camera text-info me-2"></i>Bukti Foto / Scan Nota
            </h3>

            @if($purchaseReceipt->image_path)
                @php
                    $isPdf = str_ends_with(strtolower($purchaseReceipt->image_path), '.pdf');
                @endphp
                @if($isPdf)
                    <div class="text-center py-6">
                        <i class="fas fa-file-pdf text-danger" style="font-size:48px;"></i>
                        <p class="text-sm mt-2 text-muted">Dokumen Nota Format PDF</p>
                        <a href="{{ asset('storage/' . $purchaseReceipt->image_path) }}" target="_blank" class="btn btn-primary btn-sm mt-3">
                            <i class="fas fa-external-link-alt me-1"></i> Buka File PDF
                        </a>
                    </div>
                @else
                    <div class="text-center">
                        <a href="{{ asset('storage/' . $purchaseReceipt->image_path) }}" target="_blank" title="Klik untuk memperbesar">
                            <img src="{{ asset('storage/' . $purchaseReceipt->image_path) }}" alt="Foto Nota Fisik" class="rounded shadow-sm max-h-64 mx-auto hover:opacity-90 transition">
                        </a>
                        <a href="{{ asset('storage/' . $purchaseReceipt->image_path) }}" target="_blank" class="btn btn-light btn-xs mt-3 text-xs">
                            <i class="fas fa-expand me-1"></i> Lihat Ukuran Penuh
                        </a>
                    </div>
                @endif
            @else
                <div class="text-center py-8 text-muted">
                    <i class="fas fa-image text-gray-300 mb-2" style="font-size:36px;"></i>
                    <p class="text-xs">Tidak ada bukti foto/scan nota yang diunggah.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Table Rincian Item Nota -->
    <div class="card" style="padding:20px;">
        <h3 style="font-size:16px;font-weight:600;margin-bottom:16px;color:#1e293b;">Rincian Barang yang Dibeli</h3>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:5%;">#</th>
                        <th>Nama Barang / Material</th>
                        <th class="text-center">Jumlah</th>
                        <th class="text-end">Harga Satuan</th>
                        <th class="text-end">Subtotal</th>
                        <th>Catatan Item</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseReceipt->items as $idx => $item)
                    <tr>
                        <td class="text-muted">{{ $idx + 1 }}</td>
                        <td>
                            <strong>{{ $item->item_name }}</strong>
                            @if($item->material)
                                <span class="badge badge-secondary text-xs ms-1">{{ $item->material->unit?->symbol ?? 'unit' }}</span>
                            @endif
                        </td>
                        <td class="text-center">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="text-end"><strong class="text-success">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong></td>
                        <td class="text-muted text-xs">{{ $item->notes ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:#f8fafc;font-weight:bold;">
                        <td colspan="4" class="text-end" style="font-size:15px;">TOTAL PEMBELIAN NOTA:</td>
                        <td class="text-end" style="font-size:17px;color:#16a34a;">
                            Rp {{ number_format($purchaseReceipt->total_amount, 0, ',', '.') }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-app-layout>
