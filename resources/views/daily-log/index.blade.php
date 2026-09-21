<x-app-layout>
    <x-slot name="title">Log Harian Proyek</x-slot>

    <!-- Header & Action -->
    <div class="flex items-center justify-between mb-4" style="flex-wrap:wrap;gap:12px;">
        <div>
            <h2 class="fw-700" style="font-size:20px;color:#0f172a;margin-bottom:3px;">Log Harian Logistik dan Aktivitas Proyek</h2>
            <p class="text-muted" style="font-size:13px;margin:0;">
                Rekap mutasi barang masuk, pemakaian lapangan, pantauan alat, dan posisi sisa stok harian
            </p>
        </div>
        <div>
            <a href="{{ route('daily-log.print', ['warehouse_id' => $selectedWarehouse->id, 'date' => $date]) }}" target="_blank" class="btn btn-primary">
                <i class="fas fa-print"></i> Cetak Laporan
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-body" style="padding:16px 20px;">
            <form method="GET" action="{{ route('daily-log.index') }}" class="flex gap-3" style="flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:240px;">
                    <label class="form-label" style="font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;">Gudang / Site Proyek</label>
                    <select name="warehouse_id" class="form-control" onchange="this.form.submit()">
                        @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ $selectedWarehouse->id == $wh->id ? 'selected' : '' }}>
                            {{ $wh->name }} {{ $wh->is_central ? '(Pusat)' : '(Proyek)' }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div style="min-width:180px;">
                    <label class="form-label" style="font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;">Tanggal Laporan</label>
                    <input type="date" name="date" value="{{ $date }}" class="form-control" onchange="this.form.submit()">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-secondary" style="height:40px;padding:0 16px;">
                        Muat Data
                    </button>
                    @if($date !== date('Y-m-d'))
                    <a href="{{ route('daily-log.index', ['warehouse_id' => $selectedWarehouse->id, 'date' => date('Y-m-d')]) }}" class="btn btn-secondary" style="height:40px;padding:0 14px;display:flex;align-items:center;" title="Kembali ke Hari Ini">
                        Hari Ini
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="grid mb-4" style="display:grid;grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px;">
        <div class="card" style="padding:16px 18px;">
            <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.04em;">Material Masuk</div>
            <div class="flex items-baseline gap-2 mt-2">
                <span class="fw-700" style="font-size:24px;color:#0f172a;line-height:1;">{{ $totalItemsIn }}</span>
                <span class="text-muted" style="font-size:12px;">Item</span>
            </div>
            <div class="text-muted mt-1" style="font-size:11.5px;">Surat jalan & GR diterima</div>
        </div>

        <div class="card" style="padding:16px 18px;">
            <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.04em;">Material Dipakai</div>
            <div class="flex items-baseline gap-2 mt-2">
                <span class="fw-700" style="font-size:24px;color:#0f172a;line-height:1;">{{ $totalItemsOut }}</span>
                <span class="text-muted" style="font-size:12px;">Item</span>
            </div>
            <div class="text-muted mt-1" style="font-size:11.5px;">Dikeluarkan ke lapangan</div>
        </div>

        <div class="card" style="padding:16px 18px;">
            <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.04em;">Alat Ready</div>
            <div class="flex items-baseline gap-2 mt-2">
                <span class="fw-700" style="font-size:24px;color:#0f172a;line-height:1;">{{ $totalToolsReady }}</span>
                <span class="text-muted" style="font-size:12px;">Unit</span>
            </div>
            <div class="text-muted mt-1" style="font-size:11.5px;">Standby di gudang</div>
        </div>

        <div class="card" style="padding:16px 18px;">
            <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.04em;">Alat di Lapangan</div>
            <div class="flex items-baseline gap-2 mt-2">
                <span class="fw-700" style="font-size:24px;color:#0f172a;line-height:1;">{{ $totalToolsInUse }}</span>
                <span class="text-muted" style="font-size:12px;">Unit</span>
            </div>
            <div class="text-muted mt-1" style="font-size:11.5px;">Sedang aktif dipinjam</div>
        </div>

        <div class="card" style="padding:16px 18px;">
            <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.04em;">Alat Servis / Rusak</div>
            <div class="flex items-baseline gap-2 mt-2">
                <span class="fw-700" style="font-size:24px;color:{{ $totalToolsDamaged > 0 ? '#dc2626' : '#0f172a' }};line-height:1;">{{ $totalToolsDamaged }}</span>
                <span class="text-muted" style="font-size:12px;">Unit</span>
            </div>
            <div class="text-muted mt-1" style="font-size:11.5px;">Perlu perbaikan</div>
        </div>
    </div>

    <!-- Section 1: Pemakaian Material Lapangan Hari Ini -->
    <div class="card mb-4">
        <div class="card-header flex justify-between items-center" style="background:#ffffff;padding:14px 20px;border-bottom:1px solid #f1f5f9;">
            <span class="card-title" style="font-size:14px;font-weight:700;color:#0f172a;margin:0;">
                1. Pemakaian Material Lapangan Hari Ini (Outgoing)
            </span>
            <span class="badge badge-gray">{{ $usages->count() }} Transaksi Bon</span>
        </div>
        <div class="card-body p-0">
            <div class="table-wrap">
                <table class="data-table mb-0">
                    <thead>
                        <tr>
                            <th style="width:140px;">No. Bon</th>
                            <th style="width:180px;">Penerima (Mandor/Tukang)</th>
                            <th style="min-width:180px;">Bagian Pekerjaan / Zona</th>
                            <th>Material & Kuantitas yang Dikeluarkan</th>
                            <th style="width:140px;">Petugas Gudang</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usages as $usage)
                        <tr>
                            <td>
                                <a href="{{ route('material-usages.show', $usage) }}" class="fw-600 text-primary">
                                    {{ $usage->usage_number }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-600" style="color:#0f172a;">{{ $usage->recipient_name }}</div>
                            </td>
                            <td>
                                <span class="badge badge-gray">{{ $usage->job_section ?? 'Pekerjaan Umum' }}</span>
                            </td>
                            <td>
                                <div style="display:flex;flex-direction:column;gap:4px;">
                                    @foreach($usage->items as $uItem)
                                    <div style="font-size:13px;line-height:1.4;">
                                        <strong>{{ format_quantity($uItem->quantity) }} {{ $uItem->material?->unit?->abbreviation }}</strong>
                                        <span style="color:#475569;">— {{ $uItem->material?->name }}</span>
                                        @if($uItem->notes)
                                        <span class="text-muted" style="font-size:11.5px;">({{ $uItem->notes }})</span>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <span class="text-muted" style="font-size:12.5px;">{{ $usage->issuedBy?->name ?? '-' }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted p-4" style="font-size:13px;">
                                Tidak ada pengeluaran / pemakaian material pada tanggal {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Section 2: Material Masuk Hari Ini -->
    <div class="card mb-4">
        <div class="card-header flex justify-between items-center" style="background:#ffffff;padding:14px 20px;border-bottom:1px solid #f1f5f9;">
            <span class="card-title" style="font-size:14px;font-weight:700;color:#0f172a;margin:0;">
                2. Penerimaan Material Masuk (Incoming)
            </span>
            <span class="badge badge-gray">{{ $incomingDistributions->count() + $incomingGoodsReceipts->count() }} Penerimaan</span>
        </div>
        <div class="card-body p-0">
            <div class="table-wrap">
                <table class="data-table mb-0">
                    <thead>
                        <tr>
                            <th style="width:140px;">No. Surat Jalan / GR</th>
                            <th style="width:200px;">Sumber Pengirim</th>
                            <th>Material & Kuantitas Diterima</th>
                            <th style="width:140px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $hasIncoming = false; @endphp
                        @foreach($incomingDistributions as $dist)
                        @php $hasIncoming = true; @endphp
                        <tr>
                            <td>
                                <a href="{{ route('distributions.show', $dist) }}" class="fw-600 text-primary">
                                    {{ $dist->distribution_number }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-600" style="color:#0f172a;">{{ $dist->fromWarehouse?->name ?? 'Gudang Pusat' }}</div>
                                @if($dist->driver_name)
                                <div class="text-muted" style="font-size:11.5px;">Supir: {{ $dist->driver_name }}</div>
                                @endif
                            </td>
                            <td>
                                <div style="display:flex;flex-direction:column;gap:4px;">
                                    @foreach($dist->items as $dItem)
                                    <div style="font-size:13px;line-height:1.4;">
                                        <strong>{{ format_quantity($dItem->qty_received) }} {{ $dItem->material?->unit?->abbreviation }}</strong>
                                        <span style="color:#475569;">— {{ $dItem->material?->name ?? $dItem->tool?->name }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-success">Diterima di Site</span>
                            </td>
                        </tr>
                        @endforeach

                        @foreach($incomingGoodsReceipts as $gr)
                        @php $hasIncoming = true; @endphp
                        <tr>
                            <td>
                                <span class="fw-600 text-primary">{{ $gr->receipt_number }}</span>
                            </td>
                            <td>
                                <div class="fw-600" style="color:#0f172a;">{{ $gr->supplier?->name ?? 'Supplier Langsung' }}</div>
                                @if($gr->supplier_do_number)
                                <div class="text-muted" style="font-size:11.5px;">SJ: {{ $gr->supplier_do_number }}</div>
                                @endif
                            </td>
                            <td>
                                <div style="display:flex;flex-direction:column;gap:4px;">
                                    @foreach($gr->items as $gItem)
                                    <div style="font-size:13px;line-height:1.4;">
                                        <strong>{{ format_quantity($gItem->quantity_received) }} {{ $gItem->material?->unit?->abbreviation }}</strong>
                                        <span style="color:#475569;">— {{ $gItem->material?->name }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-success">Masuk Gudang</span>
                            </td>
                        </tr>
                        @endforeach

                        @if(!$hasIncoming)
                        <tr>
                            <td colspan="4" class="text-center text-muted p-4" style="font-size:13px;">
                                Tidak ada penerimaan barang masuk pada tanggal {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}.
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Section 3: Distribusi Keluar ke Gudang Lain -->
    <div class="card mb-4">
        <div class="card-header flex justify-between items-center" style="background:#ffffff;padding:14px 20px;border-bottom:1px solid #f1f5f9;">
            <span class="card-title" style="font-size:14px;font-weight:700;color:#0f172a;margin:0;">
                3. Distribusi Keluar ke Proyek / Gudang Lain
            </span>
            <span class="badge badge-gray">{{ $outgoingDistributions->count() }} Distribusi</span>
        </div>
        <div class="card-body p-0">
            <div class="table-wrap">
                <table class="data-table mb-0">
                    <thead>
                        <tr>
                            <th style="width:140px;">No. Surat Jalan</th>
                            <th style="width:200px;">Tujuan Pengiriman</th>
                            <th style="width:110px;text-align:center;">Tanggal Kirim</th>
                            <th>Material & Kuantitas Dikirim</th>
                            <th style="width:130px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($outgoingDistributions as $dist)
                        <tr>
                            <td>
                                <a href="{{ route('distributions.show', $dist) }}" class="fw-600 text-primary">
                                    {{ $dist->distribution_number }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-600" style="color:#0f172a;">{{ $dist->toWarehouse?->name ?? 'Gudang Lain' }}</div>
                                @if($dist->driver_name)
                                <div class="text-muted" style="font-size:11.5px;">Supir: {{ $dist->driver_name }}</div>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                <span>{{ \Carbon\Carbon::parse($dist->shipped_at)->format('d/m/Y') }}</span>
                            </td>
                            <td>
                                <div style="display:flex;flex-direction:column;gap:4px;">
                                    @foreach($dist->items as $dItem)
                                    <div style="font-size:13px;line-height:1.4;">
                                        <strong>{{ format_quantity($dItem->qty_shipped) }} {{ $dItem->material?->unit?->abbreviation }}</strong>
                                        <span style="color:#475569;">— {{ $dItem->material?->name ?? $dItem->tool?->name }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-{{ $dist->status === 'received' ? 'success' : 'primary' }}">
                                    {{ $dist->status === 'received' ? 'Diterima' : 'Dikirim' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted p-4" style="font-size:13px;">
                                Tidak ada distribusi keluar pada tanggal {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Section 4: Retur Material dari Lapangan -->
    <div class="card mb-4">
        <div class="card-header flex justify-between items-center" style="background:#ffffff;padding:14px 20px;border-bottom:1px solid #f1f5f9;">
            <span class="card-title" style="font-size:14px;font-weight:700;color:#0f172a;margin:0;">
                4. Retur Material dari Lapangan
            </span>
            <span class="badge badge-gray">{{ $materialReturns->count() }} Retur</span>
        </div>
        <div class="card-body p-0">
            <div class="table-wrap">
                <table class="data-table mb-0">
                    <thead>
                        <tr>
                            <th style="width:140px;">No. Retur</th>
                            <th style="width:200px;">Sumber Retur</th>
                            <th style="width:110px;text-align:center;">Tanggal Terima</th>
                            <th>Material & Kuantitas Diterima</th>
                            <th style="width:140px;">Alasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($materialReturns as $ret)
                        <tr>
                            <td>
                                <a href="{{ route('returns.show', $ret) }}" class="fw-600 text-primary">
                                    {{ $ret->return_number }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-600" style="color:#0f172a;">{{ $ret->fromWarehouse?->name ?? 'Lapangan' }}</div>
                                <div class="text-muted" style="font-size:11.5px;">Penerima: {{ $ret->receiver?->name ?? '-' }}</div>
                            </td>
                            <td style="text-align:center;">
                                <span>{{ \Carbon\Carbon::parse($ret->received_at)->format('d/m/Y') }}</span>
                            </td>
                            <td>
                                <div style="display:flex;flex-direction:column;gap:4px;">
                                    @foreach($ret->items as $rItem)
                                    <div style="font-size:13px;line-height:1.4;">
                                        <strong>{{ format_quantity($rItem->received_qty) }} {{ $rItem->material?->unit?->abbreviation }}</strong>
                                        <span style="color:#475569;">— {{ $rItem->material?->name }}</span>
                                        <span class="text-muted" style="font-size:11.5px;">({{ ucfirst($rItem->condition ?? 'good') }})</span>
                                    </div>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-{{ $ret->status === 'received' ? 'success' : 'warning' }}">
                                    {{ $ret->reason_label ?? $ret->getReasonLabel() }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted p-4" style="font-size:13px;">
                                Tidak ada retur material diterima pada tanggal {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Section 5: Status Alat Kerja Hari Ini -->
    <div class="card mb-4">
        <div class="card-header flex justify-between items-center" style="background:#ffffff;padding:14px 20px;border-bottom:1px solid #f1f5f9;">
            <span class="card-title" style="font-size:14px;font-weight:700;color:#0f172a;margin:0;">
                5. Pantauan Alat Kerja di Lapangan
            </span>
            <span class="badge badge-gray">{{ $toolAssignments->count() }} Peminjaman Aktif</span>
        </div>
        <div class="card-body p-0">
            <div class="table-wrap">
                <table class="data-table mb-0">
                    <thead>
                        <tr>
                            <th>Nama Alat & Kode</th>
                            <th style="width:90px;text-align:center;">Jumlah</th>
                            <th>Peminjam (Mandor/Tukang)</th>
                            <th>Lokasi Lapangan</th>
                            <th style="width:110px;">Tgl Pinjam</th>
                            <th style="width:120px;">Estimasi Kembali</th>
                            <th style="width:140px;text-align:center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($toolAssignments as $ta)
                        <tr>
                            <td>
                                <div class="fw-600" style="color:#0f172a;">{{ $ta->tool?->name }}</div>
                                <div class="text-muted" style="font-size:11.5px;">Kode: {{ $ta->tool?->code ?? '-' }}</div>
                            </td>
                            <td style="text-align:center;">
                                <span class="fw-600" style="font-size:13px;">{{ $ta->quantity ?? 1 }} Unit</span>
                            </td>
                            <td>
                                <div class="fw-600" style="color:#0f172a;">{{ $ta->borrower_display }}</div>
                                @if($ta->borrower_phone)
                                <div class="text-muted" style="font-size:11.5px;">Telp: {{ $ta->borrower_phone }}</div>
                                @endif
                            </td>
                            <td>
                                <span style="color:#334155;">{{ $ta->location_display }}</span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($ta->assigned_at)->format('d/m/Y') }}</td>
                            <td>
                                @if($ta->expected_return_at)
                                    @php
                                        $exp = \Carbon\Carbon::parse($ta->expected_return_at);
                                        $isOver = $ta->status === 'active' && $exp->isPast();
                                    @endphp
                                    <span class="{{ $isOver ? 'text-danger fw-700' : '' }}">
                                        {{ $exp->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                @if($ta->status === 'returned')
                                    <span class="badge badge-success">Dikembalikan</span>
                                @elseif($ta->status === 'overdue' || ($ta->status === 'active' && $ta->expected_return_at && \Carbon\Carbon::parse($ta->expected_return_at)->isPast()))
                                    <span class="badge badge-danger">Melewati Batas</span>
                                @else
                                    <span class="badge badge-primary">Sedang Dipakai</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted p-4" style="font-size:13px;">
                                Tidak ada alat kerja yang sedang dipinjam pada tanggal ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Section 6: Neraca Sisa Stok Material Hari Ini — hanya material yang dipakai hari ini, dikelompokkan kategori+nama -->
    <div class="card mb-4">
        <div class="card-header flex justify-between items-center" style="background:#ffffff;padding:14px 20px;border-bottom:1px solid #f1f5f9;">
            <div>
                <span class="card-title" style="font-size:14px;font-weight:700;color:#0f172a;margin:0;">
                    6. Neraca Sisa Stok Material Hari Ini (Stock Balance)
                </span>
                <span class="text-muted" style="font-size:11.5px;display:block;margin-top:2px;">Dikelompokkan kategori & nama — hanya tampil material yang dipakai/masuk hari ini</span>
            </div>
            <span class="text-muted" style="font-size:12px;">Stok Awal + Masuk - Keluar = Sisa Stok</span>
        </div>
        <div class="card-body p-0">
            @if(isset($stockBalanceGrouped) && $stockBalanceGrouped->isNotEmpty())
                @foreach($stockBalanceGrouped as $catName => $rows)
                <div style="background:#f8fafc;padding:8px 16px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-tag" style="font-size:11px;color:#64748b;"></i>
                    <span class="fw-700" style="font-size:12px;text-transform:uppercase;letter-spacing:.04em;color:#0f172a;">{{ $catName }}</span>
                    <span class="badge badge-gray" style="font-size:11px;">{{ $rows->count() }} material dipakai</span>
                </div>
                <div class="table-wrap">
                    <table class="data-table mb-0">
                        <thead>
                            <tr>
                                <th style="width:45px;text-align:center;">#</th>
                                <th>Nama Material</th>
                                <th style="width:120px;text-align:right;">Stok Awal</th>
                                <th style="width:120px;text-align:right;">Masuk (+)</th>
                                <th style="width:120px;text-align:right;">Dipakai (-)</th>
                                <th style="width:130px;text-align:right;">Sisa Akhir</th>
                                <th style="width:80px;text-align:center;">Satuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $idx => $stk)
                            <tr>
                                <td style="text-align:center;color:#64748b;">{{ $idx + 1 }}</td>
                                <td>
                                    <div class="fw-600" style="color:#0f172a;">{{ $stk->material_name }}</div>
                                    <div class="text-muted" style="font-size:11.5px;">Kode: {{ $stk->material_code }}</div>
                                </td>
                                <td style="text-align:right;color:#475569;">{{ format_quantity($stk->opening_stock) }}</td>
                                <td style="text-align:right;font-weight:600;color:{{ $stk->qty_in > 0 ? '#0f172a' : '#94a3b8' }};">{{ $stk->qty_in > 0 ? '+' . format_quantity($stk->qty_in) : '-' }}</td>
                                <td style="text-align:right;font-weight:600;color:{{ $stk->qty_out > 0 ? '#0f172a' : '#94a3b8' }};">{{ $stk->qty_out > 0 ? '-' . format_quantity($stk->qty_out) : '-' }}</td>
                                <td style="text-align:right;font-weight:700;font-size:13.5px;color:#0f172a;">{{ format_quantity($stk->closing_stock) }}</td>
                                <td style="text-align:center;color:#64748b;">{{ $stk->unit }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endforeach
            @else
                <div class="text-center text-muted p-4" style="font-size:13px;">Tidak ada material yang dipakai/masuk pada tanggal {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}.</div>
            @endif
        </div>
    </div>

    <!-- Section 7: Neraca Posisi & Kesiapan Alat Kerja — hanya alat yang dipinjam, grouped kategori+nama -->
    <div class="card mb-4">
        <div class="card-header flex justify-between items-center" style="background:#ffffff;padding:14px 20px;border-bottom:1px solid #f1f5f9;">
            <div>
                <span class="card-title" style="font-size:14px;font-weight:700;color:#0f172a;margin:0;">
                    7. Neraca Posisi & Kesiapan Alat Kerja (Tool Availability)
                </span>
                <span class="text-muted" style="font-size:11.5px;display:block;margin-top:2px;">Dikelompokkan kategori & nama — hanya tampil alat yang dipinjam hari ini</span>
            </div>
            <div class="flex items-center gap-2" style="flex-wrap:wrap;">
                <span class="badge badge-gray">{{ $totalToolsReady }} Ready</span>
                <span class="badge badge-gray">{{ $totalToolsInUse }} di Lapangan</span>
                @if($totalToolsDamaged > 0)
                <span class="badge badge-danger">{{ $totalToolsDamaged }} Rusak/Servis</span>
                @endif
            </div>
        </div>
        <div class="card-body p-0">
            @if(isset($toolBalanceGrouped) && $toolBalanceGrouped->isNotEmpty())
                @foreach($toolBalanceGrouped as $catName => $rows)
                <div style="background:#f8fafc;padding:8px 16px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-tools" style="font-size:11px;color:#64748b;"></i>
                    <span class="fw-700" style="font-size:12px;text-transform:uppercase;letter-spacing:.04em;color:#0f172a;">{{ $catName }}</span>
                    <span class="badge badge-gray" style="font-size:11px;">{{ $rows->count() }} alat dipinjam</span>
                </div>
                <div class="table-wrap">
                    <table class="data-table mb-0">
                        <thead>
                            <tr>
                                <th style="width:45px;text-align:center;">#</th>
                                <th>Nama Alat Kerja</th>
                                <th style="width:105px;text-align:center;">Total Unit</th>
                                <th style="width:105px;text-align:center;">Ready (Gudang)</th>
                                <th style="width:105px;text-align:center;">Di Lapangan</th>
                                <th style="width:105px;text-align:center;">Rusak / Servis</th>
                                <th>Mandor Pemegang / Posisi</th>
                                <th style="width:140px;text-align:center;">Status Kesiapan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $tIdx => $tb)
                            <tr>
                                <td style="text-align:center;color:#64748b;">{{ $tIdx + 1 }}</td>
                                <td>
                                    <div class="fw-600" style="color:#0f172a;">{{ $tb->tool_name }}</div>
                                    <div class="text-muted" style="font-size:11.5px;">Kode: {{ $tb->tool_code }} | Merk: {{ $tb->brand }}</div>
                                </td>
                                <td style="text-align:center;"><strong style="font-size:13px;color:#0f172a;">{{ $tb->stock_total }} Unit</strong></td>
                                <td style="text-align:center;"><span style="font-weight:600;color:{{ $tb->stock_available > 0 ? '#0f172a' : '#94a3b8' }};">{{ $tb->stock_available }} Unit</span></td>
                                <td style="text-align:center;"><span style="font-weight:600;color:{{ $tb->stock_borrowed > 0 ? '#0f172a' : '#94a3b8' }};">{{ $tb->stock_borrowed }} Unit</span></td>
                                <td style="text-align:center;"><span style="font-weight:600;color:{{ $tb->stock_maintenance > 0 ? '#dc2626' : '#94a3b8' }};">{{ $tb->stock_maintenance }} Unit</span></td>
                                <td>
                                    @if(!empty($tb->borrowers))
                                        <div style="display:flex;flex-direction:column;gap:3px;">
                                            @foreach($tb->borrowers as $bName)
                                            <div style="font-size:12.5px;color:#1e293b;">{{ $bName }}</div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted" style="font-size:12px;">Standby di gudang</span>
                                    @endif
                                </td>
                                <td style="text-align:center;">
                                    @if($tb->stock_available > 0)
                                        <span class="badge badge-success">Siap Pakai</span>
                                    @elseif($tb->stock_borrowed > 0)
                                        <span class="badge badge-primary">Dipakai Lapangan</span>
                                    @elseif($tb->stock_maintenance > 0)
                                        <span class="badge badge-danger">Butuh Servis</span>
                                    @else
                                        <span class="badge badge-gray">Kosong</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endforeach
            @else
                <div class="text-center text-muted p-4" style="font-size:13px;">Tidak ada alat yang dipinjam pada tanggal {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}.</div>
            @endif
        </div>
    </div>
</x-app-layout>
