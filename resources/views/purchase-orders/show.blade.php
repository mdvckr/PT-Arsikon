<x-app-layout>
    <x-slot name="title">Detail PO: {{ $purchaseOrder->po_number }}</x-slot>

    @php
        $canViewPrice = $userCanViewPrice ?? (auth()->user() ? auth()->user()->canViewPoPrice() : false);
    @endphp

    <div class="flex justify-between items-center mb-4">
        <div>
            <a href="{{ route('purchase-orders.index') }}" class="text-muted" style="font-size:13px;">
                <x-lucide-arrow-left class="me-1" /> Kembali ke Daftar PO
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

        <div class="flex gap-2 items-center flex-wrap">
            @if($canViewPrice)
                {{-- Opsi Cetak Ganda untuk Admin PO / Admin Pusat / Owner --}}
                <a href="{{ route('purchase-orders.print', ['purchaseOrder' => $purchaseOrder->id, 'mode' => 'official']) }}" target="_blank"
                   class="btn" style="background:#0f766e;color:#fff;font-weight:600;" title="Cetak PO Resmi dengan harga untuk Supplier / Keuangan">
                    <x-lucide-file-spreadsheet class="me-1" /> Cetak PO Resmi (Harga)
                </a>
                <a href="{{ route('purchase-orders.print', ['purchaseOrder' => $purchaseOrder->id, 'mode' => 'blind']) }}" target="_blank"
                   class="btn" style="background:#475569;color:#fff;font-weight:600;" title="Cetak PO Lapangan / Gudang tanpa harga (Rp 0) untuk bagian penerimaan barang di proyek">
                    <x-lucide-shield-check class="me-1" /> Cetak PO Lapangan (Tanpa Harga)
                </a>
            @else
                <a href="{{ route('purchase-orders.print', ['purchaseOrder' => $purchaseOrder->id, 'mode' => 'blind']) }}" target="_blank"
                   class="btn" style="background:#475569;color:#fff;font-weight:600;" title="Cetak PO Lapangan (Tanpa Harga)">
                    <x-lucide-printer class="me-1" /> Cetak PO Lapangan
                </a>
            @endif

            @can('update', $purchaseOrder)
                @if(!in_array($purchaseOrder->status, ['cancelled', 'closed', 'received']) && !$purchaseOrder->hasConfirmedGoodsReceipt())
                    <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}" class="btn" style="background:#fef3c7;border:1px solid #fde68a;color:#92400e;font-weight:600;" title="Edit Purchase Order">
                        <x-lucide-pencil class="me-1" /> Edit PO
                    </a>
                @endif
            @endcan

            @if($purchaseOrder->status === 'draft')
                @if((auth()->user()->can('approve purchase orders') || auth()->user()->hasRole(['Admin Pusat', 'Admin'])) && $purchaseOrder->created_by !== auth()->id())
                    <form action="{{ route('purchase-orders.approve', $purchaseOrder) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success" onclick="return confirm('Setujui PO ini?')">
                            <x-lucide-check class="me-1" /> Setujui PO
                        </button>
                    </form>
                @endif
            @endif

            @if($purchaseOrder->status === 'approved')
                <form action="{{ route('purchase-orders.send', $purchaseOrder) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Kirim PO ini ke Supplier?')">
                        <x-lucide-send class="me-1" /> Kirim ke Supplier
                    </button>
                </form>
            @endif

            @if(in_array($purchaseOrder->status, ['draft', 'approved', 'sent']))
                <form action="{{ route('purchase-orders.cancel', $purchaseOrder) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Batalkan PO ini?')">
                        Batalkan
                    </button>
                </form>
            @endif

            @if(in_array($purchaseOrder->status, ['sent', 'partial_received']) && $purchaseOrder->outstanding > 0)
                @if((auth()->user()->can('close purchase orders') || auth()->user()->hasRole(['Admin Pusat', 'Admin'])) && $purchaseOrder->created_by !== auth()->id())
                    <button type="button" class="btn btn-outline-warning" onclick="promptShortClose()">
                        <x-lucide-ban class="me-1" /> Short Close
                    </button>
                    <form id="shortCloseForm" action="{{ route('purchase-orders.close', $purchaseOrder) }}" method="POST" style="display:none;">
                        @csrf
                        <input type="hidden" name="reason" id="shortCloseReason">
                    </form>
                    <script>
                        function promptShortClose() {
                            let reason = prompt('Masukkan alasan penutupan (Short Close) PO:');
                            if (reason && reason.trim().length > 0) {
                                document.getElementById('shortCloseReason').value = reason.trim();
                                document.getElementById('shortCloseForm').submit();
                            }
                        }
                    </script>
                @endif
            @endif
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger mb-4" style="background:#fef2f2;border:1px solid #ef4444;color:#991b1b;padding:12px 16px;border-radius:8px;">
            <x-lucide-circle-alert class="me-2" />{{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success mb-4" style="background:#ecfdf5;border:1px solid #10b981;color:#065f46;padding:12px 16px;border-radius:8px;">
            <x-lucide-circle-check class="me-2" />{{ session('success') }}
        </div>
    @endif

    @if(session('show_print_prompt'))
        <div style="background:#eff6ff;border:1.5px solid #3b82f6;color:#1e40af;padding:14px 18px;border-radius:8px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
            <div>
                <x-lucide-printer class="me-2" />
                <strong>PO berhasil dibuat!</strong> Apakah Anda ingin mencetak dokumen Purchase Order ini?
            </div>
            <div style="display:flex;gap:8px;flex-shrink:0;flex-wrap:wrap;">
                @if($canViewPrice)
                    <a href="{{ route('purchase-orders.print', ['purchaseOrder' => $purchaseOrder->id, 'mode' => 'official']) }}" target="_blank"
                       style="background:#0f766e;color:#fff;padding:7px 14px;border-radius:6px;font-size:12.5px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:5px;">
                        <x-lucide-file-spreadsheet /> Cetak Resmi (Harga)
                    </a>
                    <a href="{{ route('purchase-orders.print', ['purchaseOrder' => $purchaseOrder->id, 'mode' => 'blind']) }}" target="_blank"
                       style="background:#475569;color:#fff;padding:7px 14px;border-radius:6px;font-size:12.5px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:5px;">
                        <x-lucide-shield-check /> Cetak Lapangan (Tanpa Harga)
                    </a>
                @else
                    <a href="{{ route('purchase-orders.print', ['purchaseOrder' => $purchaseOrder->id, 'mode' => 'blind']) }}" target="_blank"
                       style="background:#475569;color:#fff;padding:7px 14px;border-radius:6px;font-size:12.5px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:5px;">
                        <x-lucide-printer /> Cetak PO Lapangan
                    </a>
                @endif
                <button onclick="this.parentElement.parentElement.remove()"
                        style="background:transparent;border:1px solid #93c5fd;color:#1d4ed8;padding:7px 12px;border-radius:6px;font-size:12.5px;cursor:pointer;">
                    Lewati
                </button>
            </div>
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
                @if(in_array($purchaseOrder->status, ['approved', 'sent', 'partial_received', 'received']) && $purchaseOrder->approver)
                    <span class="block text-xs text-muted mt-1">Disetujui: <strong>{{ $purchaseOrder->approver->name }}</strong> ({{ $purchaseOrder->approved_at?->format('d/m/Y H:i') }})</span>
                @endif
            </div>
            <div>
                <span class="text-muted block text-xs">Total Nilai PO:</span>
                @if($canViewPrice)
                    <strong class="text-primary" style="font-size:16px;">Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</strong>
                @else
                    <strong class="text-muted" style="font-size:16px;">Rp 0 <span style="font-size:11px;font-weight:normal;">(Dirahasiakan)</span></strong>
                @endif
            </div>
            <div>
                <span class="text-muted block text-xs">Gudang Tujuan Pengiriman:</span>
                <strong>{{ $purchaseOrder->deliveryWarehouse ? $purchaseOrder->deliveryWarehouse->name . ($purchaseOrder->deliveryWarehouse->project ? ' - ' . $purchaseOrder->deliveryWarehouse->project->name : '') : 'Gudang Pusat' }}</strong>
            </div>
            <div>
                <span class="text-muted block text-xs">Penanggung Risiko Pengiriman:</span>
                @if($purchaseOrder->risk_bearer === 'buyer')
                    <span class="badge" style="background:#fee2e2;color:#991b1b;font-weight:600;font-size:11.5px;">
                        <x-lucide-shield class="me-1" /> Pembeli (PT Arsikon)
                    </span>
                @else
                    <span class="badge" style="background:#ecfdf5;color:#065f46;font-weight:600;font-size:11.5px;">
                        <x-lucide-truck class="me-1" /> Vendor / Supplier
                    </span>
                @endif
            </div>
            <div>
                <span class="text-muted block text-xs">Estimasi Tiba:</span>
                <span>{{ $purchaseOrder->expected_delivery ? $purchaseOrder->expected_delivery->format('d M Y') : '-' }}</span>
            </div>
            <div>
                <span class="text-muted block text-xs">Dibuat Oleh:</span>
                <span>{{ $purchaseOrder->creator?->name ?? '-' }}</span>
            </div>
            <div>
                <span class="text-muted block text-xs">Contact Person (CP):</span>
                <span>{{ $purchaseOrder->contact_person ?: ($purchaseOrder->creator?->name ?? '-') }}</span>
            </div>
            @php
                $linkedMr = $purchaseOrder->materialRequest ?? $purchaseOrder->items->pluck('materialRequestItem.materialRequest')->filter()->first();
            @endphp
            @if($linkedMr)
            <div>
                <span class="text-muted block text-xs">Dari PO Lapangan:</span>
                <a href="{{ route('material-requests.show', $linkedMr) }}" style="color:#2563eb;text-decoration:none;font-weight:700;">
                    <x-lucide-clipboard-check class="me-1" />#{{ $linkedMr->request_number }}
                </a>
                <span class="block text-xs text-muted">{{ $linkedMr->toWarehouse?->project?->name ?? $linkedMr->toWarehouse?->name }}</span>
            </div>
            @endif
        </div>
    </div>

    @if($purchaseOrder->receipts && $purchaseOrder->receipts->count() > 0)
        {{-- Jika PO terhubung Nota Pembelian, ringkasan finansial & riwayat pembayaran dipindahkan ke halaman Nota Pembelian --}}
        <div class="card mb-6" style="padding:16px 20px; border:1px solid #cbd5e1; background:#f8fafc; border-radius:8px;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                <div style="font-size:13px; color:#1e293b;">
                    <x-lucide-receipt class="text-primary me-2" />
                    PO ini terhubung dengan <strong>{{ $purchaseOrder->receipts->count() }} Nota Pembelian Fisik</strong>.
                    Ringkasan finansial dan riwayat pembayaran ditampilkan langsung pada halaman Nota Pembelian.
                </div>
                <div class="flex gap-2">
                    @foreach($purchaseOrder->receipts as $rc)
                        <a href="{{ route('purchase-receipts.show', $rc) }}" class="btn btn-sm btn-primary" style="font-size:12px; font-weight:600;">
                            <x-lucide-receipt class="me-1" /> Buka Nota #{{ $rc->receipt_number ?? $rc->id }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @else
    @if($canViewPrice)
    <!-- SECTION FINANSIAL & PEMBAYARAN PO (Nilai Sah, Nilai Tertahan, Status Bayar) -->
    @php
        $poNilaiSah = (float) $purchaseOrder->nilai_sah;
        $poVerifiedPaid = (float) $purchaseOrder->verified_payment_amount;
        $poPendingPaid = (float) $purchaseOrder->pending_payment_amount;
        $poPayableRemaining = (float) $purchaseOrder->payable_remaining_amount;
        $withheldData = $purchaseOrder->withheld_summary;
        $poTotalWithheld = (float) $withheldData['total_withheld'];
        $hasConfirmedGr = $purchaseOrder->hasConfirmedGoodsReceipt();
        $hasPayableProof = $purchaseOrder->hasPayableProof();
    @endphp
    <div class="card mb-6" style="padding:20px; border:1.5px solid #cbd5e1; background:#ffffff;">
        <div class="flex justify-between items-center mb-4 flex-wrap gap-3">
            <div>
                <h3 style="font-size:16px; font-weight:700; color:#0f172a;" class="flex items-center gap-2">
                    <x-lucide-coins class="text-primary" /> Ringkasan Finansial & Nilai Sah Tagihan PO
                </h3>
                <p class="text-muted" style="font-size:12.5px; margin-top:2px;">
                    Pembayaran tagihan dihitung berdasarkan nilai sah barang yang telah diterima di gudang (GR) atau bukti nota fisik lapangan.
                </p>
            </div>
            <div>
                @if($hasPayableProof && $poPayableRemaining > 0)
                    <a href="{{ route('payments.create', ['po_id' => $purchaseOrder->id]) }}" class="btn btn-primary" style="font-size:12.5px; font-weight:700; padding:7px 14px; border-radius:6px;">
                        <x-lucide-credit-card class="me-1" /> Ajukan Pembayaran
                    </a>
                @elseif(! $hasPayableProof)
                    <span class="badge" style="background:#f1f5f9; color:#64748b; font-size:11.5px; padding:6px 10px; border:1px solid #cbd5e1;">
                        <x-lucide-clock class="me-1" /> Belum ada Bukti GR / Nota Pembelian
                    </span>
                @else
                    <span class="badge" style="background:#dcfce7; color:#15803d; font-size:11.5px; padding:6px 10px; border:1px solid #bbf7d0;">
                        <x-lucide-check-check class="me-1" /> Nilai Tagihan Lunas / Penuh Diajukan
                    </span>
                @endif
            </div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:12px; margin-bottom:16px;">
            <div style="background:#f8fafc; padding:12px; border-radius:8px; border:1px solid #e2e8f0; text-align:center;">
                <div style="font-size:10.5px; font-weight:700; color:#64748b; text-transform:uppercase;">TOTAL NILAI PO</div>
                <div style="font-size:15px; font-weight:800; color:#0f172a; margin-top:4px;">
                    Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}
                </div>
            </div>
            <div style="background:#eff6ff; padding:12px; border-radius:8px; border:1px solid #bfdbfe; text-align:center;">
                <div style="font-size:10.5px; font-weight:700; color:#1e40af; text-transform:uppercase;">NILAI SAH SAAT INI</div>
                <div style="font-size:15px; font-weight:800; color:#1d4ed8; margin-top:4px;">
                    Rp {{ number_format($poNilaiSah, 0, ',', '.') }}
                </div>
            </div>
            <div style="background:#f0fdf4; padding:12px; border-radius:8px; border:1px solid #bbf7d0; text-align:center;">
                <div style="font-size:10.5px; font-weight:700; color:#166534; text-transform:uppercase;">SUDAH DIBAYAR</div>
                <div style="font-size:15px; font-weight:800; color:#15803d; margin-top:4px;">
                    Rp {{ number_format($poVerifiedPaid, 0, ',', '.') }}
                </div>
            </div>
            <div style="background:#fffbeb; padding:12px; border-radius:8px; border:1px solid #fde68a; text-align:center;">
                <div style="font-size:10.5px; font-weight:700; color:#b45309; text-transform:uppercase;">PENDING VERIFIKASI</div>
                <div style="font-size:15px; font-weight:800; color:#d97706; margin-top:4px;">
                    Rp {{ number_format($poPendingPaid, 0, ',', '.') }}
                </div>
            </div>
            <div style="background:#ecfdf5; padding:12px; border-radius:8px; border:1px solid #a7f3d0; text-align:center;">
                <div style="font-size:10.5px; font-weight:700; color:#065f46; text-transform:uppercase;">SISA BISA DIBAYAR</div>
                <div style="font-size:15px; font-weight:800; color:#059669; margin-top:4px;">
                    Rp {{ number_format($poPayableRemaining, 0, ',', '.') }}
                </div>
            </div>
            <div style="background:#fef2f2; padding:12px; border-radius:8px; border:1px solid #fecaca; text-align:center;">
                <div style="font-size:10.5px; font-weight:700; color:#991b1b; text-transform:uppercase;">NILAI TERTAHAN</div>
                <div style="font-size:15px; font-weight:800; color:#b91c1c; margin-top:4px;">
                    Rp {{ number_format($poTotalWithheld, 0, ',', '.') }}
                </div>
            </div>
        </div>

        @if(!empty($withheldData['items']))
            <div class="mt-3" style="background:#fff5f5; border:1px solid #fed7d7; border-radius:8px; padding:12px 14px;">
                <div style="font-size:12px; font-weight:700; color:#991b1b; margin-bottom:6px;" class="flex items-center gap-1">
                    <x-lucide-circle-alert /> Rincian Nilai Tertahan (Unresolved / Rusak / Tanggungan Vendor / Belum Tiba):
                </div>
                <div style="display:flex; flex-direction:column; gap:6px;">
                    @foreach($withheldData['items'] as $wh)
                        <div style="display:flex; justify-content:space-between; align-items:center; font-size:12px; padding:4px 0; border-bottom:1px dashed #fecaca;">
                            <div>
                                <strong>{{ $wh['item_name'] }}</strong>
                                <span class="text-muted">({{ format_quantity($wh['qty']) }} {{ $wh['unit'] }})</span>
                                &mdash; <span style="color:#7f1d1d;">{{ $wh['reason'] }}</span>
                            </div>
                            <div style="font-weight:700; color:#b91c1c;">
                                Rp {{ number_format($wh['value'], 0, ',', '.') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Tabel Riwayat Pembayaran --}}
        @if($purchaseOrder->payments && $purchaseOrder->payments->count() > 0)
            <div class="mt-4 pt-3" style="border-top:1px solid #e2e8f0;">
                <h4 style="font-size:13.5px; font-weight:700; color:#334155; margin-bottom:10px;">
                    <x-lucide-history class="me-1" /> Riwayat Pembayaran PO:
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
                            @foreach($purchaseOrder->payments as $pmt)
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
    @endif
    @endif

    <!-- Section Barang yang Dipesan -->
    <div class="card mb-6" style="padding:20px;">
        <h3 style="font-size:16px;font-weight:600;margin-bottom:16px;color:#1e293b;">Barang yang Dipesan (Item PO)</h3>
        @if($purchaseOrder->notes)
            <div class="mb-4" style="background:#f8fafc;border-left:4px solid #64748b;padding:10px 14px;border-radius:4px;font-size:13px;white-space:pre-line;">
                <span class="text-muted block font-semibold text-xs mb-1">Catatan Dokumen / Riwayat Short Close:</span>
                {{ $purchaseOrder->notes }}
            </div>
        @endif
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Material</th>
                        <th class="text-center">Jumlah Dipesan</th>
                        @if($purchaseOrder->status !== 'draft')
                            <th class="text-center">Diterima / Ditutup</th>
                        @endif
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
                                        <x-lucide-link class="me-1" /> Dari Permintaan: #{{ $item->materialRequestItem->materialRequest->request_number }} ({{ $item->materialRequestItem->materialRequest->fromWarehouse?->name }})
                                    </span>
                                </div>
                            @elseif($item->isCustom())
                                <div class="mt-1">
                                    <span class="badge" style="background:#fef3c7;color:#92400e;font-size:11px;font-weight:500;">
                                        <x-lucide-tag class="me-1" /> Barang Baru / Pengadaan Khusus
                                    </span>
                                </div>
                            @endif

                            @if($item->notes)
                                <div class="mt-1" style="font-size:11.5px;color:#475569;">
                                    <x-lucide-sticky-note class="text-amber-600 me-1" /> Catatan: <em>{{ $item->notes }}</em>
                                </div>
                            @endif

                            @if($item->deliverySchedules && $item->deliverySchedules->count() > 0)
                                <div class="mt-2" style="font-size:11px; background:#f8fafc; padding:6px 10px; border-radius:6px; border:1px solid #e2e8f0;">
                                    <span style="color:#475569; font-weight:600;"><x-lucide-calendar-days class="me-1" /> Jadwal Kedatangan:</span>
                                    <div class="mt-1" style="display:flex; flex-direction:column; gap:3px;">
                                        @foreach($item->deliverySchedules as $sch)
                                            <div style="display:flex; align-items:center; justify-content:space-between; gap:6px;">
                                                <span>
                                                    <strong>{{ $sch->stage_label }}:</strong>
                                                    {{ format_quantity($sch->planned_qty) }} {{ $item->displayUnit() }}
                                                    (Rencana: {{ $sch->planned_date ? $sch->planned_date->format('d/m/Y') : '-' }})
                                                </span>
                                                <span>
                                                    <span class="text-success fw-semibold">{{ format_quantity($sch->received_qty) }} tiba</span>
                                                    @if($sch->status === 'received')
                                                        <span class="badge" style="background:#dcfce7;color:#166534;font-size:9.5px;">Tuntas</span>
                                                    @elseif($sch->is_late)
                                                        <span class="badge" style="background:#fee2e2;color:#991b1b;font-size:9.5px;">Terlambat</span>
                                                    @else
                                                        <span class="badge" style="background:#e0f2fe;color:#0369a1;font-size:9.5px;">Menunggu</span>
                                                    @endif
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </td>
                        <td class="text-center">
                            {{ format_quantity($item->quantity) }} {{ $item->displayUnit() }}
                        </td>
                        @if($purchaseOrder->status !== 'draft')
                            <td class="text-center">
                                <span class="text-success font-semibold">{{ format_quantity($item->received_qty) }} diterima</span>
                                @if((float)$item->short_closed_qty > 0)
                                    <span class="badge ms-1" style="background:#fef3c7;color:#92400e;font-size:10.5px;">
                                        Ditutup: {{ format_quantity($item->short_closed_qty) }}
                                    </span>
                                @endif
                            </td>
                        @endif
                        <td class="text-end">
                            @if($canViewPrice)
                                Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                            @else
                                <span class="text-muted">Rp 0</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($canViewPrice)
                                <strong class="text-primary">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong>
                            @else
                                <span class="text-muted">Rp 0</span>
                            @endif
                        </td>
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
                    <x-lucide-receipt class="text-sky-600" /> Riwayat Nota Pembelian Harian (Hari 1, Hari 2, dst.)
                </h3>
                <p class="text-muted" style="font-size:13px;">Daftar pengeluaran dan nota fisik harian yang dicatat untuk PO ini.</p>
            </div>
            <a href="{{ route('purchase-receipts.create', ['po_id' => $purchaseOrder->id]) }}" class="btn btn-sm btn-primary">
                <x-lucide-plus class="me-1" /> Tambah Nota Hari Ini
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
                                    <x-lucide-file-image class="me-1" /> Lihat Foto
                                </a>
                            @else
                                <span class="text-muted" style="font-size:12px;">Tanpa Foto</span>
                            @endif
                        </td>
                        <td class="text-muted" style="font-size:12px;">{{ $receipt->creator?->name ?? '-' }}</td>
                        <td>
                            <a href="{{ route('purchase-receipts.show', $receipt) }}" class="btn btn-sm btn-secondary btn-icon" title="Detail Nota">
                                <x-lucide-eye />
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
