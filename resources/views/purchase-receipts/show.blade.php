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
        <div class="flex gap-2 items-center flex-wrap">
            @can('create purchase receipts')
                @if($purchaseReceipt->payment_status === 'unpaid')
                    <form action="{{ route('purchase-receipts.update-status', $purchaseReceipt) }}" method="POST" onsubmit="return confirm('Tandai nota ini sebagai SUDAH DIBAYAR?')">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="payment_status" value="paid">
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="fas fa-check-circle me-1"></i> Tandai Sudah Dibayar
                        </button>
                    </form>
                @else
                    <form action="{{ route('purchase-receipts.update-status', $purchaseReceipt) }}" method="POST" onsubmit="return confirm('Kembalikan status nota menjadi BELUM DIBAYAR?')">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="payment_status" value="unpaid">
                        <button type="submit" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-undo me-1"></i> Kembalikan ke Belum Dibayar
                        </button>
                    </form>
                @endif
            @endcan

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
                    <div class="flex items-center gap-2 mt-1">
                        <span class="badge badge-{{ $purchaseReceipt->status_color }}">{{ $purchaseReceipt->status_label }}</span>
                        @can('create purchase receipts')
                            @if($purchaseReceipt->payment_status === 'unpaid')
                                <form action="{{ route('purchase-receipts.update-status', $purchaseReceipt) }}" method="POST" class="inline" onsubmit="return confirm('Tandai nota ini sebagai SUDAH DIBAYAR?')">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="payment_status" value="paid">
                                    <button type="submit" class="btn btn-success btn-xs" style="font-size:11px;padding:2px 8px;">
                                        <i class="fas fa-check me-1"></i> Set Sudah Bayar
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('purchase-receipts.update-status', $purchaseReceipt) }}" method="POST" class="inline" onsubmit="return confirm('Kembalikan status nota menjadi BELUM DIBAYAR?')">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="payment_status" value="unpaid">
                                    <button type="submit" class="btn btn-outline-warning btn-xs" style="font-size:11px;padding:2px 8px;">
                                        <i class="fas fa-undo me-1"></i> Ubah Belum Bayar
                                    </button>
                                </form>
                            @endif
                        @endcan
                    </div>
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

    <!-- SECTION FINANSIAL & RIWAYAT PEMBAYARAN NOTA -->
    @php
        $receiptTotal = (float) $purchaseReceipt->total_amount;
        $po = $purchaseReceipt->purchaseOrder;
        $payments = $po ? $po->payments : collect();
        $verifiedPayments = $payments->where('status', 'verified');
        $pendingPayments = $payments->where('status', 'pending');
        
        $verifiedAmount = (float) $verifiedPayments->sum('amount');
        $pendingAmount = (float) $pendingPayments->sum('amount');
        
        $isPaid = ($purchaseReceipt->payment_status === 'paid');
        $paidAmount = $isPaid ? ($verifiedAmount > 0 ? $verifiedAmount : $receiptTotal) : $verifiedAmount;
        $remainingAmount = max(0.0, round($receiptTotal - $paidAmount, 2));
    @endphp
    <div class="card mb-4" style="padding:20px; border:1.5px solid #cbd5e1; background:#ffffff;">
        <div class="flex justify-between items-center mb-4 flex-wrap gap-3">
            <div>
                <h3 style="font-size:16px; font-weight:700; color:#0f172a;" class="flex items-center gap-2">
                    <i class="fas fa-coins text-primary"></i> Ringkasan Finansial & Pembayaran Nota
                </h3>
                <p class="text-muted" style="font-size:12.5px; margin-top:2px;">
                    Rincian status pelunasan dan bukti transaksi pembayaran untuk nota pembelian ini.
                </p>
            </div>
            <div>
                @if($isPaid || $remainingAmount <= 0)
                    <span class="badge" style="background:#dcfce7; color:#15803d; font-size:11.5px; padding:6px 12px; border:1px solid #bbf7d0;">
                        <i class="fas fa-check-double me-1"></i> Tagihan Nota Lunas
                    </span>
                @elseif($po)
                    <a href="{{ route('payments.create', ['po_id' => $po->id]) }}" class="btn btn-primary btn-sm" style="font-size:12px; font-weight:700; padding:6px 12px;">
                        <i class="fas fa-credit-card me-1"></i> Bayar Tagihan Nota Ini
                    </a>
                @endif
            </div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:12px; margin-bottom:16px;">
            <div style="background:#f8fafc; padding:12px; border-radius:8px; border:1px solid #e2e8f0; text-align:center;">
                <div style="font-size:10.5px; font-weight:700; color:#64748b; text-transform:uppercase;">TOTAL NILAI NOTA</div>
                <div style="font-size:15px; font-weight:800; color:#0f172a; margin-top:4px;">
                    Rp {{ number_format($receiptTotal, 0, ',', '.') }}
                </div>
            </div>
            <div style="background:#f0fdf4; padding:12px; border-radius:8px; border:1px solid #bbf7d0; text-align:center;">
                <div style="font-size:10.5px; font-weight:700; color:#166534; text-transform:uppercase;">SUDAH DIBAYAR</div>
                <div style="font-size:15px; font-weight:800; color:#15803d; margin-top:4px;">
                    Rp {{ number_format($paidAmount, 0, ',', '.') }}
                </div>
            </div>
            <div style="background:#fffbeb; padding:12px; border-radius:8px; border:1px solid #fde68a; text-align:center;">
                <div style="font-size:10.5px; font-weight:700; color:#b45309; text-transform:uppercase;">PENDING VERIFIKASI</div>
                <div style="font-size:15px; font-weight:800; color:#d97706; margin-top:4px;">
                    Rp {{ number_format($pendingAmount, 0, ',', '.') }}
                </div>
            </div>
            <div style="background:#ecfdf5; padding:12px; border-radius:8px; border:1px solid #a7f3d0; text-align:center;">
                <div style="font-size:10.5px; font-weight:700; color:#065f46; text-transform:uppercase;">SISA TAGIHAN</div>
                <div style="font-size:15px; font-weight:800; color:#059669; margin-top:4px;">
                    Rp {{ number_format($remainingAmount, 0, ',', '.') }}
                </div>
            </div>
        </div>

        {{-- Riwayat Pembayaran --}}
        @if($payments && $payments->count() > 0)
            <div class="mt-4 pt-3" style="border-top:1px solid #e2e8f0;">
                <h4 style="font-size:13.5px; font-weight:700; color:#334155; margin-bottom:10px;">
                    <i class="fas fa-history me-1"></i> Riwayat Pembayaran Nota:
                </h4>
                <div class="table-wrap">
                    <table class="data-table" style="background:#fff; font-size:12px;">
                        <thead>
                            <tr>
                                <th>No. Pembayaran</th>
                                <th>Tanggal</th>
                                <th>Nominal</th>
                                <th>Metode</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $pmt)
                                <tr>
                                    <td><code>{{ $pmt->payment_number }}</code></td>
                                    <td>{{ $pmt->payment_date ? $pmt->payment_date->format('d/m/Y') : '-' }}</td>
                                    <td><strong>Rp {{ number_format($pmt->amount, 0, ',', '.') }}</strong></td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $pmt->payment_method)) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $pmt->status_color }}">
                                            {{ $pmt->status_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('payments.show', $pmt) }}" class="btn btn-xs btn-secondary">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
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
