<x-app-layout>
    <x-slot name="title">Laporan Audit Pengadaan & Keuangan (PO)</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div class="breadcrumb">
            <a href="{{ route('reports.index') }}">Laporan & Audit</a>
            <span class="breadcrumb-sep"><x-lucide-chevron-right style="font-size:10px;" /></span>
            <span>Audit Pengadaan & Keuangan</span>
        </div>
        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-secondary">
            Kembali
        </a>
    </div>

    <!-- Filter Form Card -->
    <div class="card mb-4">
        <div class="card-header" style="display:flex;align-items:center;gap:10px;padding:16px 20px;">
            <div style="width:32px;height:32px;border-radius:6px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#475569;flex-shrink:0;">
                <x-lucide-filter style="font-size:13px;" />
            </div>
            <span class="card-title" style="margin:0;font-size:15px;">Filter Rekapitulasi PO & Audit Pengadaan</span>
        </div>
        <div class="card-body" style="padding:20px;">
            <form method="GET" action="{{ route('reports.procurement-audit') }}" class="grid grid-4" style="gap:16px;align-items:flex-end;">
                <div>
                    <label class="form-label">Dari Tanggal Pesan</label>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div>
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <div>
                    <label class="form-label">Supplier / Vendor</label>
                    <select name="supplier_id" class="form-control">
                        <option value="all">Semua Vendor</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Status PO</label>
                    <select name="status" class="form-control">
                        <option value="all">Semua Status</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Terkirim</option>
                        <option value="partial_received" {{ request('status') === 'partial_received' ? 'selected' : '' }}>Diterima Sebagian</option>
                        <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Selesai Diterima</option>
                        <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
                <div style="grid-column: span 4; display:flex; gap:10px; justify-content:flex-end; margin-top:8px;">
                    <a href="{{ route('reports.procurement-audit') }}" class="btn btn-secondary">Reset</a>
                    <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary KPI Badges -->
    @php
        $totalPO = count($purchaseOrders);
        $totalNominal = $purchaseOrders->sum('total_amount');
        $delayedPO = $purchaseOrders->where('delay_days', '>', 0)->count();
    @endphp
    <div class="grid grid-3 mb-4" style="gap:16px;">
        <div class="card" style="padding:16px 20px;">
            <div class="text-muted" style="font-size:12px;font-weight:600;text-transform:uppercase;">Total Purchase Order</div>
            <div class="fw-700" style="font-size:22px;color:#0f172a;margin-top:4px;">{{ $totalPO }} PO</div>
        </div>
        <div class="card" style="padding:16px 20px;">
            <div class="text-muted" style="font-size:12px;font-weight:600;text-transform:uppercase;">Total Nilai Pembelian (PO)</div>
            <div class="fw-700" style="font-size:22px;color:#2563eb;margin-top:4px;">Rp {{ number_format($totalNominal, 0, ',', '.') }}</div>
        </div>
        <div class="card" style="padding:16px 20px;">
            <div class="text-muted" style="font-size:12px;font-weight:600;text-transform:uppercase;">PO Terlambat Kirim (Deviasi)</div>
            <div class="fw-700" style="font-size:22px;color:{{ $delayedPO > 0 ? '#dc2626' : '#16a34a' }};margin-top:4px;">
                {{ $delayedPO }} PO
            </div>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="card">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:32px;height:32px;border-radius:6px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#475569;flex-shrink:0;">
                    <x-lucide-receipt style="font-size:13px;" />
                </div>
                <span class="card-title" style="margin:0;font-size:15px;">Daftar Realisasi Purchase Order & Kedatangan Barang</span>
            </div>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No. PO</th>
                        <th>Tgl Order</th>
                        <th>Target Pengiriman</th>
                        <th>Vendor / Supplier</th>
                        <th>Gudang Tujuan</th>
                        <th style="text-align:right;">Nilai Total PO</th>
                        <th style="text-align:center;">Status</th>
                        <th style="text-align:center;">Audit Keterlambatan</th>
                        <th style="text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseOrders as $po)
                    <tr>
                        <td>
                            <span class="fw-700" style="color:#0f172a;">{{ $po->po_number }}</span>
                            <div class="text-muted" style="font-size:11px;">Dibuat: {{ $po->creator_name ?? '-' }}</div>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($po->order_date)->format('d/m/Y') }}</td>
                        <td>
                            @if($po->expected_delivery)
                                {{ \Carbon\Carbon::parse($po->expected_delivery)->format('d/m/Y') }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="fw-600">{{ $po->supplier_name }}</td>
                        <td>{{ $po->warehouse_name ?? 'Gudang Pusat' }}</td>
                        <td style="text-align:right;font-weight:700;color:#0f172a;">
                            Rp {{ number_format($po->total_amount, 0, ',', '.') }}
                        </td>
                        <td style="text-align:center;">
                            @php
                                $badgeStyle = match($po->status) {
                                    'received', 'closed' => 'background:#dcfce7;color:#166534;border:1px solid #bbf7d0;',
                                    'partial_received'  => 'background:#e0f2fe;color:#0369a1;border:1px solid #bae6fd;',
                                    'sent', 'approved'  => 'background:#fef3c7;color:#92400e;border:1px solid #fde68a;',
                                    'cancelled'         => 'background:#fee2e2;color:#991b1b;border:1px solid #fecaca;',
                                    default             => 'background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;',
                                };
                            @endphp
                            <span class="badge" style="{{ $badgeStyle }}">
                                {{ ucfirst(str_replace('_', ' ', $po->status)) }}
                            </span>
                        </td>
                        <td style="text-align:center;">
                            @if($po->delay_days > 0)
                                <span class="badge" style="background:#fee2e2;color:#991b1b;border:1px solid #fecaca;font-weight:700;">
                                    Terlambat {{ $po->delay_days }} hari
                                </span>
                            @else
                                <span class="badge" style="background:#dcfce7;color:#166534;border:1px solid #bbf7d0;">
                                    Tepat Waktu
                                </span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            <a href="{{ route('purchase-orders.show', $po->id) }}" class="btn btn-xs btn-outline-secondary" target="_blank">
                                <x-lucide-external-link style="font-size:11px;" /> Rincian PO
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="text-align:center;padding:32px;color:#94a3b8;">
                            Tidak ada data Purchase Order sesuai filter yang dipilih.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
