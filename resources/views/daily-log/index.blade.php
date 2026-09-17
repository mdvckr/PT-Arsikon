<x-app-layout>
    <x-slot name="title">Log Harian Logistik dan Material Proyek</x-slot>

    <!-- Header & Date/Warehouse Filter -->
    <div class="flex items-center justify-between mb-4" style="flex-wrap:wrap;gap:12px;">
        <div>
            <h2 class="fw-700" style="font-size:20px;color:#0f172a;">Log Harian Logistik dan Aktivitas Proyek</h2>
            <p class="text-muted" style="font-size:13px;margin-top:2px;">
                Rekap barang masuk, material terpakai, alat aktif, dan posisi sisa stok harian
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('daily-log.print', ['warehouse_id' => $selectedWarehouse->id, 'date' => $date]) }}" target="_blank" class="btn btn-primary">
                <i class="fas fa-print"></i> Cetak Laporan Harian
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-body" style="padding:16px 20px;">
            <form method="GET" action="{{ route('daily-log.index') }}" class="flex gap-3" style="flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:220px;">
                    <label class="form-label">Gudang / Site Proyek</label>
                    <select name="warehouse_id" class="form-control" onchange="this.form.submit()">
                        @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ $selectedWarehouse->id == $wh->id ? 'selected' : '' }}>
                            {{ $wh->name }} {{ $wh->is_central ? '(Pusat)' : '(Proyek)' }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div style="min-width:180px;">
                    <label class="form-label">Pilih Tanggal Laporan</label>
                    <input type="date" name="date" value="{{ $date }}" class="form-control" onchange="this.form.submit()">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-secondary" style="height:38px;padding:0 16px;">
                        <i class="fas fa-arrows-rotate"></i> Muat Data
                    </button>
                    @if($date !== date('Y-m-d'))
                    <a href="{{ route('daily-log.index', ['warehouse_id' => $selectedWarehouse->id, 'date' => date('Y-m-d')]) }}" class="btn btn-light border" style="height:38px;padding:0 12px;display:flex;align-items:center;" title="Kembali ke Hari Ini">
                        Hari Ini
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="grid mb-4" style="display:grid;grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px;">
        <div class="card p-3" style="border-left: 4px solid #3b82f6;">
            <div class="text-muted" style="font-size:11.5px;font-weight:600;text-transform:uppercase;">Material Masuk</div>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="fw-800" style="font-size:22px;color:#1e40af;">{{ $totalItemsIn }}</span>
                <span class="text-muted" style="font-size:12px;">Item Masuk</span>
            </div>
        </div>

        <div class="card p-3" style="border-left: 4px solid #ef4444;">
            <div class="text-muted" style="font-size:11.5px;font-weight:600;text-transform:uppercase;">Material Dipakai</div>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="fw-800" style="font-size:22px;color:#b91c1c;">{{ $totalItemsOut }}</span>
                <span class="text-muted" style="font-size:12px;">Item Dikeluarkan</span>
            </div>
        </div>

        <div class="card p-3" style="border-left: 4px solid #10b981;">
            <div class="text-muted" style="font-size:11.5px;font-weight:600;text-transform:uppercase;">Alat Ready (Gudang)</div>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="fw-800" style="font-size:22px;color:#047857;">{{ $totalToolsReady }}</span>
                <span class="text-muted" style="font-size:12px;">Unit Siap Pakai</span>
            </div>
        </div>

        <div class="card p-3" style="border-left: 4px solid #8b5cf6;">
            <div class="text-muted" style="font-size:11.5px;font-weight:600;text-transform:uppercase;">Alat di Lapangan</div>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="fw-800" style="font-size:22px;color:#6d28d9;">{{ $totalToolsInUse }}</span>
                <span class="text-muted" style="font-size:12px;">Unit Sedang Dipinjam</span>
            </div>
        </div>

        <div class="card p-3" style="border-left: 4px solid #f59e0b;">
            <div class="text-muted" style="font-size:11.5px;font-weight:600;text-transform:uppercase;">Alat Rusak / Servis</div>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="fw-800" style="font-size:22px;color:#b45309;">{{ $totalToolsDamaged }}</span>
                <span class="text-muted" style="font-size:12px;">Unit Butuh Servis</span>
            </div>
        </div>
    </div>

    <!-- Section 1: Pemakaian Material Lapangan Hari Ini -->
    <div class="card mb-4">
        <div class="card-header flex justify-between items-center" style="background:#fff1f2;">
            <div class="flex items-center gap-2">
                <i class="fas fa-dolly text-danger"></i>
                <span class="card-title" style="color:#9f1239;">1. Pemakaian Material Lapangan Hari Ini (Outgoing)</span>
            </div>
            <span class="badge badge-danger">{{ $usages->count() }} Transaksi Bon</span>
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
                            <th style="width:130px;">Petugas Gudang</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usages as $usage)
                        <tr>
                            <td>
                                <a href="{{ route('material-usages.show', $usage) }}" class="fw-700 text-primary">
                                    {{ $usage->usage_number }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-700" style="color:#0f172a;">{{ $usage->recipient_name }}</div>
                            </td>
                            <td>
                                <span class="badge badge-gray" style="font-size:12px;">{{ $usage->job_section ?? 'Pekerjaan Umum' }}</span>
                            </td>
                            <td>
                                <div style="display:flex;flex-direction:column;gap:3px;">
                                    @foreach($usage->items as $uItem)
                                    <div style="font-size:12.5px;">
                                        <i class="fas fa-circle-dot text-danger" style="font-size:8px;margin-right:4px;"></i>
                                        <strong>{{ format_quantity($uItem->quantity) }} {{ $uItem->material?->unit?->abbreviation }}</strong>
                                        — {{ $uItem->material?->name }}
                                        @if($uItem->notes) <span class="text-muted" style="font-size:11px;">({{ $uItem->notes }})</span> @endif
                                    </div>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <span class="text-muted" style="font-size:12px;">{{ $usage->issuedBy?->name ?? '-' }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted p-3" style="font-size:13px;">
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
        <div class="card-header flex justify-between items-center" style="background:#eff6ff;">
            <div class="flex items-center gap-2">
                <i class="fas fa-truck-ramp-box text-primary"></i>
                <span class="card-title" style="color:#1e40af;">2. Penerimaan Material Hari Ini (Incoming)</span>
            </div>
            <span class="badge badge-info">{{ $incomingDistributions->count() + $incomingGoodsReceipts->count() }} Penerimaan</span>
        </div>
        <div class="card-body p-0">
            <div class="table-wrap">
                <table class="data-table mb-0">
                    <thead>
                        <tr>
                            <th style="width:140px;">No. Surat Jalan / GR</th>
                            <th style="width:180px;">Sumber Pengirim</th>
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
                                <a href="{{ route('distributions.show', $dist) }}" class="fw-700 text-primary">
                                    {{ $dist->distribution_number }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-600">{{ $dist->fromWarehouse?->name ?? 'Gudang Pusat' }}</div>
                                <div class="text-muted" style="font-size:11px;">Supir: {{ $dist->driver_name ?? '-' }}</div>
                            </td>
                            <td>
                                <div style="display:flex;flex-direction:column;gap:3px;">
                                    @foreach($dist->items as $dItem)
                                    <div style="font-size:12.5px;">
                                        <i class="fas fa-circle-dot text-primary" style="font-size:8px;margin-right:4px;"></i>
                                        <strong>{{ format_quantity($dItem->quantity) }} {{ $dItem->material?->unit?->abbreviation }}</strong>
                                        — {{ $dItem->material?->name ?? $dItem->tool?->name }}
                                    </div>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-success"><i class="fas fa-check"></i> Diterima di Site</span>
                            </td>
                        </tr>
                        @endforeach

                        @foreach($incomingGoodsReceipts as $gr)
                        @php $hasIncoming = true; @endphp
                        <tr>
                            <td>
                                <span class="fw-700 text-primary">{{ $gr->receipt_number }}</span>
                            </td>
                            <td>
                                <div class="fw-600">{{ $gr->supplier?->name ?? 'Supplier Langsung' }}</div>
                                <div class="text-muted" style="font-size:11px;">Surat Jalan: {{ $gr->supplier_do_number ?? '-' }}</div>
                            </td>
                            <td>
                                <div style="display:flex;flex-direction:column;gap:3px;">
                                    @foreach($gr->items as $gItem)
                                    <div style="font-size:12.5px;">
                                        <i class="fas fa-circle-dot text-primary" style="font-size:8px;margin-right:4px;"></i>
                                        <strong>{{ format_quantity($gItem->quantity_received) }} {{ $gItem->material?->unit?->abbreviation }}</strong>
                                        — {{ $gItem->material?->name }}
                                    </div>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-success"><i class="fas fa-check"></i> Masuk Gudang</span>
                            </td>
                        </tr>
                        @endforeach

                        @if(!$hasIncoming)
                        <tr>
                            <td colspan="4" class="text-center text-muted p-3" style="font-size:13px;">
                                Tidak ada penerimaan barang masuk pada tanggal {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}.
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Section 3: Status Alat Kerja Hari Ini -->
    <div class="card mb-4">
        <div class="card-header flex justify-between items-center" style="background:#f5f3ff;">
            <div class="flex items-center gap-2">
                <i class="fas fa-hand-holding text-purple"></i>
                <span class="card-title" style="color:#6d28d9;">3. Pantauan Alat Kerja di Lapangan (Tools Monitoring)</span>
            </div>
            <span class="badge badge-purple">{{ $toolAssignments->count() }} Peminjaman Tercatat</span>
        </div>
        <div class="card-body p-0">
            <div class="table-wrap">
                <table class="data-table mb-0">
                    <thead>
                        <tr>
                            <th>Nama Alat & Kode</th>
                            <th style="width:100px;text-align:center;">Jumlah</th>
                            <th>Peminjam (Mandor/Tukang)</th>
                            <th>Lokasi Lapangan</th>
                            <th style="width:110px;">Tgl Pinjam</th>
                            <th style="width:110px;">Estimasi Kembali</th>
                            <th style="width:140px;text-align:center;">Status Saat Ini</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($toolAssignments as $ta)
                        <tr>
                            <td>
                                <div class="fw-700" style="color:#0f172a;">{{ $ta->tool?->name }}</div>
                                <div class="text-muted" style="font-size:11px;">Kode: {{ $ta->tool?->code ?? '-' }}</div>
                            </td>
                            <td style="text-align:center;">
                                <strong style="font-size:13px;">{{ $ta->quantity ?? 1 }} Unit</strong>
                            </td>
                            <td>
                                <div class="fw-600">{{ $ta->borrower_display }}</div>
                                @if($ta->borrower_phone)
                                <div class="text-muted" style="font-size:11px;"><i class="fas fa-phone" style="font-size:9px;"></i> {{ $ta->borrower_phone }}</div>
                                @endif
                            </td>
                            <td>
                                <span>{{ $ta->location_display }}</span>
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
                                        @if($isOver) <i class="fas fa-exclamation-circle"></i> @endif
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                @if($ta->status === 'returned')
                                    <span class="badge badge-success"><i class="fas fa-check"></i> Sudah Dikembalikan</span>
                                @elseif($ta->status === 'overdue' || ($ta->status === 'active' && $ta->expected_return_at && \Carbon\Carbon::parse($ta->expected_return_at)->isPast()))
                                    <span class="badge badge-danger"><i class="fas fa-clock"></i> Melewati Batas</span>
                                @else
                                    <span class="badge badge-purple"><i class="fas fa-screwdriver-wrench"></i> Sedang Dipakai</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted p-3" style="font-size:13px;">
                                Tidak ada alat kerja yang sedang dipinjam atau beroperasi pada tanggal ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Section 4: Neraca Sisa Stok Material Hari Ini -->
    <div class="card">
        <div class="card-header flex justify-between items-center" style="background:#f8fafc;">
            <div class="flex items-center gap-2">
                <i class="fas fa-scale-balanced text-primary"></i>
                <span class="card-title">4. Neraca Sisa Stok Material Hari Ini (Stock Balance)</span>
            </div>
            <span class="text-muted" style="font-size:12px;">Stok Awal + Masuk - Keluar = Sisa Stok</span>
        </div>
        <div class="card-body p-0">
            <div class="table-wrap">
                <table class="data-table mb-0">
                    <thead>
                        <tr>
                            <th style="width:40px;text-align:center;">#</th>
                            <th>Nama Material</th>
                            <th style="width:120px;text-align:right;">Stok Awal</th>
                            <th style="width:120px;text-align:right;color:#1e40af;">Masuk (+)</th>
                            <th style="width:120px;text-align:right;color:#b91c1c;">Dipakai (-)</th>
                            <th style="width:140px;text-align:right;background:#f1f5f9;">Sisa Stok Sore Ini</th>
                            <th style="width:90px;text-align:center;">Satuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stockBalance as $idx => $stk)
                        <tr class="{{ $stk->has_activity ? 'bg-amber-50/40' : '' }}">
                            <td style="text-align:center;">{{ $idx + 1 }}</td>
                            <td>
                                <div class="fw-700" style="color:#0f172a;">{{ $stk->material_name }}</div>
                                <div class="text-muted" style="font-size:11px;">Kode: {{ $stk->material_code }}</div>
                            </td>
                            <td style="text-align:right;font-weight:600;color:#475569;">
                                {{ format_quantity($stk->opening_stock) }}
                            </td>
                            <td style="text-align:right;font-weight:700;color:#1e40af;">
                                {{ $stk->qty_in > 0 ? '+' . format_quantity($stk->qty_in) : '-' }}
                            </td>
                            <td style="text-align:right;font-weight:700;color:#b91c1c;">
                                {{ $stk->qty_out > 0 ? '-' . format_quantity($stk->qty_out) : '-' }}
                            </td>
                            <td style="text-align:right;font-weight:800;font-size:14px;background:#f8fafc;color:#0f172a;">
                                {{ format_quantity($stk->closing_stock) }}
                            </td>
                            <td style="text-align:center;color:#64748b;font-weight:600;">
                                {{ $stk->unit }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted p-4">
                                Belum ada data inventori material di gudang ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Section 5: Neraca Posisi & Kesiapan Alat Kerja -->
    <div class="card mt-4 mb-4">
        <div class="card-header flex justify-between items-center" style="background:#f0fdf4;">
            <div class="flex items-center gap-2">
                <i class="fas fa-toolbox text-success"></i>
                <span class="card-title" style="color:#166534;">5. Neraca Posisi & Kesiapan Alat Kerja (Tool Availability & Condition Balance)</span>
            </div>
            <div class="flex items-center gap-2" style="flex-wrap:wrap;">
                <span class="badge badge-success"><i class="fas fa-check"></i> {{ $totalToolsReady }} Ready di Gudang</span>
                <span class="badge badge-purple"><i class="fas fa-person-digging"></i> {{ $totalToolsInUse }} di Lapangan</span>
                @if($totalToolsDamaged > 0)
                <span class="badge badge-danger"><i class="fas fa-wrench"></i> {{ $totalToolsDamaged }} Servis/Rusak</span>
                @endif
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-wrap">
                <table class="data-table mb-0">
                    <thead>
                        <tr>
                            <th style="width:40px;text-align:center;">#</th>
                            <th>Nama Alat Kerja</th>
                            <th style="width:105px;text-align:center;">Total di Proyek</th>
                            <th style="width:115px;text-align:center;color:#166534;">Ready (Gudang)</th>
                            <th style="width:115px;text-align:center;color:#6d28d9;">Di Lapangan</th>
                            <th style="width:110px;text-align:center;color:#b91c1c;">Rusak / Servis</th>
                            <th>Mandor Pemegang / Posisi Lapangan</th>
                            <th style="width:165px;text-align:center;">Kesiapan Besok</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($toolBalance as $tIdx => $tb)
                        <tr class="{{ $tb->has_activity ? 'bg-amber-50/40' : '' }}">
                            <td style="text-align:center;">{{ $tIdx + 1 }}</td>
                            <td>
                                <div class="fw-700" style="color:#0f172a;">{{ $tb->tool_name }}</div>
                                <div class="text-muted" style="font-size:11px;">
                                    Kode: {{ $tb->tool_code }} | Merk: {{ $tb->brand }} | {{ $tb->category_name }}
                                </div>
                            </td>
                            <td style="text-align:center;">
                                <strong style="font-size:13.5px;color:#0f172a;">{{ $tb->stock_total }} Unit</strong>
                            </td>
                            <td style="text-align:center;">
                                @if($tb->stock_available > 0)
                                    <span class="badge badge-success" style="font-size:12px;padding:3px 8px;">
                                        <i class="fas fa-check-circle"></i> {{ $tb->stock_available }} Unit
                                    </span>
                                @else
                                    <span class="text-muted" style="font-weight:600;">0 Unit</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                @if($tb->stock_borrowed > 0)
                                    <span class="badge badge-purple" style="font-size:12px;padding:3px 8px;">
                                        <i class="fas fa-person-digging"></i> {{ $tb->stock_borrowed }} Unit
                                    </span>
                                @else
                                    <span class="text-muted">0 Unit</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                @if($tb->stock_maintenance > 0)
                                    <span class="badge badge-danger" style="font-size:12px;padding:3px 8px;">
                                        <i class="fas fa-wrench"></i> {{ $tb->stock_maintenance }} Unit
                                    </span>
                                @else
                                    <span class="text-muted">0 Unit</span>
                                @endif
                            </td>
                            <td>
                                @if(!empty($tb->borrowers))
                                    <div style="display:flex;flex-direction:column;gap:3px;">
                                        @foreach($tb->borrowers as $bName)
                                        <div style="font-size:12px;color:#1e293b;">
                                            <i class="fas fa-user text-purple" style="font-size:10px;margin-right:4px;"></i>
                                            <strong>{{ $bName }}</strong>
                                        </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted" style="font-size:12px;"><i class="fas fa-warehouse" style="font-size:10px;color:#10b981;"></i> Seluruh unit standby di gudang</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                @if($tb->stock_available > 0)
                                    <span class="badge badge-success" style="font-size:11px;">
                                        <i class="fas fa-circle-check"></i> Siap Pakai ({{ $tb->stock_available }})
                                    </span>
                                @elseif($tb->stock_borrowed > 0)
                                    <span class="badge badge-warning" style="font-size:11px;">
                                        <i class="fas fa-clock"></i> Dipakai Lapangan
                                    </span>
                                @elseif($tb->stock_maintenance > 0)
                                    <span class="badge badge-danger" style="font-size:11px;">
                                        <i class="fas fa-triangle-exclamation"></i> Butuh Servis
                                    </span>
                                @else
                                    <span class="badge badge-secondary" style="font-size:11px;">Kosong</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted p-4">
                                Belum ada data alat kerja yang terdaftar di proyek ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
