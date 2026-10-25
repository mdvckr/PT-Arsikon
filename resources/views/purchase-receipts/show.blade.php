<x-app-layout>
    <x-slot name="title">Detail Nota Pembelian</x-slot>

    <div class="flex justify-between items-center mb-4">
        <div>
            <a href="{{ route('purchase-receipts.index') }}" class="text-muted" style="font-size:13px;">
                <x-lucide-arrow-left class="me-1" /> Kembali ke Daftar Nota
            </a>
            <h2 style="font-size:20px;font-weight:700;color:#0f172a;margin-top:4px;">
                Detail Nota Pembelian
            </h2>
            <p class="text-muted" style="font-size:13px;">Tanggal Nota: {{ $purchaseReceipt->receipt_date ? $purchaseReceipt->receipt_date->format('d F Y') : '-' }}</p>
        </div>
        <div class="flex gap-2 items-center flex-wrap">
            {{-- Tombol Cetak PDF --}}
            <a href="{{ route('purchase-receipts.print', $purchaseReceipt) }}" target="_blank"
               class="btn btn-primary btn-sm"
               style="font-weight:600; background:#dc2626; border-color:#dc2626; display:inline-flex; align-items:center; gap:6px;">
                <x-lucide-printer /> Cetak Nota Ini
            </a>
            {{-- Laporan Mingguan --}}
            <a href="{{ route('purchase-receipts.print-weekly', $purchaseReceipt) }}" target="_blank"
               class="btn btn-sm"
               style="font-weight:600; background:#0ea5e9; border-color:#0284c7; color:#fff; display:inline-flex; align-items:center; gap:6px;">
                <x-lucide-calendar-range /> Lap. Mingguan
            </a>
            {{-- Laporan Bulanan --}}
            <a href="{{ route('purchase-receipts.print-monthly', $purchaseReceipt) }}" target="_blank"
               class="btn btn-sm"
               style="font-weight:600; background:#8b5cf6; border-color:#7c3aed; color:#fff; display:inline-flex; align-items:center; gap:6px;">
                <x-lucide-calendar /> Lap. Bulanan
            </a>
            @if($purchaseReceipt->purchaseOrder)
                <a href="{{ route('purchase-orders.show', $purchaseReceipt->purchaseOrder) }}" class="btn btn-secondary btn-sm">
                    <x-lucide-receipt class="me-1" /> Lihat PO terkait ({{ $purchaseReceipt->purchaseOrder->po_number }})
                </a>
            @endif
            <form action="{{ route('purchase-receipts.destroy', $purchaseReceipt) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus nota pembelian harian ini? Saldo kas operasional proyek akan otomatis dikembalikan (refund).')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <x-lucide-trash-2 class="me-1" /> Hapus Nota (Refund Saldo)
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4" style="background:#ecfdf5;border:1px solid #10b981;color:#065f46;padding:12px 16px;border-radius:8px;">
            <x-lucide-circle-check class="me-2" />{{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <!-- Card Informasii Utama -->
        <div class="md:col-span-2 card" style="padding:20px;">
            <h3 style="font-size:16px;font-weight:600;margin-bottom:16px;color:#1e293b;border-bottom:1px solid #f1f5f9;padding-bottom:10px;">
                <x-lucide-info class="text-primary me-2" />Informasi Transaksi Nota
            </h3>

            <div class="grid grid-cols-2 gap-4" style="font-size:14px;">
                <div>
                    <span class="text-muted block text-xs">No. Nota Fisik:</span>
                    <code>{{ $purchaseReceipt->receipt_number ?? '-' }}</code>
                </div>
                <div>
                    <span class="text-muted block text-xs">Status Pembayaran:</span>
                    <div class="flex items-center gap-2 mt-1">
                        @if($purchaseReceipt->payment_status === 'paid')
                            <span class="badge" style="background:#dcfce7;color:#15803d;border:1px solid #86efac;font-weight:700;">
                                <x-lucide-zap class="me-1" /> Lunas (Instan - Kas Proyek)
                            </span>
                        @else
                            <span class="badge badge-{{ $purchaseReceipt->status_color }}">{{ $purchaseReceipt->status_label }}</span>
                        @endif
                    </div>
                </div>
                <div>
                    <span class="text-muted block text-xs">Supplier / Toko Pembelian:</span>
                    <strong>{{ $purchaseReceipt->supplier_name ?? $purchaseReceipt->supplier?->name ?? '-' }}</strong>
                </div>
                <div>
                    <span class="text-muted block text-xs">Proyek / Sumber Kas:</span>
                    <strong class="text-primary">{{ $purchaseReceipt->project?->name ?? $purchaseReceipt->project_name ?? '-' }}</strong>
                </div>
                @if($purchaseReceipt->purchaseOrder)
                <div>
                    <span class="text-muted block text-xs">Terkait PO:</span>
                    <a href="{{ route('purchase-orders.show', $purchaseReceipt->purchaseOrder) }}" class="text-primary font-bold">
                        {{ $purchaseReceipt->purchaseOrder->po_number }}
                    </a>
                </div>
                @endif
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
                <x-lucide-camera class="text-info me-2" />Bukti Foto / Scan Nota
            </h3>

            @if($purchaseReceipt->image_path)
                @php
                    $isPdf = str_ends_with(strtolower($purchaseReceipt->image_path), '.pdf');
                @endphp
                @if($isPdf)
                    <div class="text-center py-6">
                        <x-lucide-file-text class="text-danger" style="font-size:48px;" />
                        <p class="text-sm mt-2 text-muted">Dokumen Nota Format PDF</p>
                        <a href="{{ route('purchase-receipts.file', $purchaseReceipt) }}" target="_blank" class="btn btn-primary btn-sm mt-3">
                            <x-lucide-external-link class="me-1" /> Buka File PDF
                        </a>
                    </div>
                @else
                    <div class="text-center">
                        <a href="{{ route('purchase-receipts.file', $purchaseReceipt) }}" target="_blank" title="Klik untuk memperbesar">
                            <img src="{{ route('purchase-receipts.file', $purchaseReceipt) }}" alt="Foto Nota Fisik" class="rounded shadow-sm max-h-64 mx-auto hover:opacity-90 transition">
                        </a>
                        <a href="{{ route('purchase-receipts.file', $purchaseReceipt) }}" target="_blank" class="btn btn-light btn-xs mt-3 text-xs">
                            <x-lucide-maximize-2 class="me-1" /> Lihat Ukuran Penuh
                        </a>
                    </div>
                @endif
            @else
                <div class="text-center py-8 text-muted">
                    <x-lucide-image class="text-gray-300 mb-2" style="font-size:36px;" />
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
                    <x-lucide-coins class="text-primary" /> Ringkasan Finansial & Pembayaran Nota
                </h3>
                <p class="text-muted" style="font-size:12.5px; margin-top:2px;">
                    Rincian status pelunasan dan bukti transaksi pembayaran untuk nota pembelian ini.
                </p>
            </div>
            <div>
                @if($isPaid || $remainingAmount <= 0)
                    <span class="badge" style="background:#dcfce7; color:#15803d; font-size:11.5px; padding:6px 12px; border:1px solid #bbf7d0;">
                        <x-lucide-check-check class="me-1" /> Tagihan Nota Lunas
                    </span>
                @elseif($po)
                    <a href="{{ route('payments.create', ['po_id' => $po->id]) }}" class="btn btn-primary btn-sm" style="font-size:12px; font-weight:700; padding:6px 12px;">
                        <x-lucide-credit-card class="me-1" /> Bayar Tagihan Nota Ini
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

        @if(!$po && $purchaseReceipt->project)
            <div style="background:#f0fdf4;border:1px solid #86efac;color:#166534;font-size:13px;border-radius:8px;padding:12px 16px;">
                <x-lucide-zap class="me-1 text-warning" /> Pembayaran nota harian ini dilakukan secara <strong>instan</strong> menggunakan <strong>Saldo Kas Operasional Proyek ({{ $purchaseReceipt->project->name }})</strong>. Total belanja sebesar <strong>Rp {{ number_format($receiptTotal, 0, ',', '.') }}</strong> telah otomatis dipotong dari saldo kas proyek saat nota dicatat.
            </div>
        @endif

        {{-- Riwayat Pembayaran --}}
        @if($payments && $payments->count() > 0)
            <div class="mt-4 pt-3" style="border-top:1px solid #e2e8f0;">
                <h4 style="font-size:13.5px; font-weight:700; color:#334155; margin-bottom:10px;">
                    <x-lucide-history class="me-1" /> Riwayat Pembayaran Nota:
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
                                            <x-lucide-eye /> Detail
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
                        <th style="width:4%;">#</th>
                        <th style="width:22%;">Nama Material</th>
                        <th style="width:24%;">Item (Jenis yang Dibeli)</th>
                        <th class="text-center" style="width:10%;">Jumlah (Vol)</th>
                        <th class="text-center" style="width:8%;">Satuan</th>
                        <th class="text-end" style="width:14%;">Harga Satuan</th>
                        <th class="text-end" style="width:14%;">Subtotal</th>
                        <th>Catatan Item</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseReceipt->items as $idx => $item)
                    @php
                        $matName = $item->material_name ?: ($item->material?->name ?: $item->item_name);
                        $itemName = $item->item_name ?: '-';
                        $satuan = $item->unit_name ?: ($item->material?->unit?->symbol ?: 'Bh');
                    @endphp
                    <tr>
                        <td class="text-muted text-center">{{ $idx + 1 }}</td>
                        <td>
                            <strong class="text-primary">{{ $matName }}</strong>
                        </td>
                        <td>
                            <span>{{ $itemName }}</span>
                        </td>
                        <td class="text-center font-semibold">{{ number_format($item->quantity, $item->quantity == floor($item->quantity) ? 0 : 2, ',', '.') }}</td>
                        <td class="text-center">
                            <span class="badge badge-secondary text-xs">{{ $satuan }}</span>
                        </td>
                        <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="text-end"><strong class="text-success">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong></td>
                        <td class="text-muted text-xs">{{ $item->notes ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:#f8fafc;font-weight:bold;">
                        <td colspan="6" class="text-end" style="font-size:15px;">TOTAL PEMBELIAN NOTA:</td>
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
