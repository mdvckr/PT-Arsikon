<x-app-layout>
    <x-slot name="title">Purchase Order (PO)</x-slot>

    @php
        $canViewPrice = auth()->user() ? auth()->user()->canViewPoPrice() : false;
    @endphp

    <div class="flex justify-between items-center mb-4">
        <div>
            <h2 style="font-size:20px;font-weight:700;color:#0f172a;">Purchase Order (PO)</h2>
            <p class="text-muted" style="font-size:13px;">Manajemen Pesanan Pembelian ke Supplier</p>
        </div>
        <div class="flex gap-2 items-center">
            <a href="{{ route('purchase-orders.weekly-report') }}" 
               target="_blank" 
               class="btn btn-secondary border shadow-sm flex items-center gap-1.5 font-semibold text-slate-800"
               style="background:#f8fafc;border-color:#cbd5e1;"
               title="Cetak Rekapitulasi Surat PO Minggu Berjalan">
                <x-lucide-printer class="text-primary w-4 h-4" />
                <span>Cetak PO Minggu Ini</span>
                <span class="badge badge-primary text-xs ml-1" style="font-size:10.5px;padding:2px 6px;">W{{ \Carbon\Carbon::now()->weekOfMonth }}</span>
            </a>

            <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary">
                <x-lucide-plus class="me-1" /> Buat PO Baru
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4" style="background:#ecfdf5;border:1px solid #10b981;color:#065f46;padding:12px 16px;border-radius:8px;">
            <x-lucide-circle-check class="me-2" />{{ session('success') }}
        </div>
    @endif

    <div class="card mb-4" style="padding:16px 20px;">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <div>
                <span class="font-bold text-slate-800" style="font-size:16px;">
                    <x-lucide-layers class="text-primary me-2" />Daftar Purchase Order per Tanggal
                </span>
                <p class="text-muted text-xs mb-0">Pesanan dikelompokkan berdasarkan tanggal pembuatan/order agar rapi dan tidak menumpuk.</p>
            </div>

            <form method="GET" class="flex flex-wrap gap-2 items-center ms-auto">
                <input type="date" name="date" value="{{ request('date') }}" class="form-control" style="width:160px;padding:6px 10px;font-size:13px;" title="Filter Tanggal Tertentu" onchange="this.form.submit()">

                <select name="status" class="form-control" style="width:160px;padding:6px 10px;font-size:13px;" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Dikirim ke Supplier</option>
                    <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Diterima</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>

                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. PO / Supplier / Barang..." class="form-control" style="width:230px;padding:6px 10px;font-size:13px;">

                <button type="submit" class="btn btn-secondary btn-sm" title="Cari">
                    <x-lucide-search />
                </button>

                @if(request('date') || request('status') || request('search'))
                    <a href="{{ route('purchase-orders.index') }}" class="btn btn-light btn-sm" title="Reset Filter">
                        <x-lucide-x class="me-1" /> Reset
                    </a>
                @endif
            </form>
        </div>
    </div>

    <!-- Grouped by Date View -->
    @if(isset($groupedPOs) && $groupedPOs->isNotEmpty())
        <div class="flex justify-end mb-3 gap-2">
            <button type="button" class="btn btn-sm btn-light" onclick="toggleAllGroups(true)">
                <x-lucide-maximize-2 class="me-1" /> Buka Semua
            </button>
            <button type="button" class="btn btn-sm btn-light" onclick="toggleAllGroups(false)">
                <x-lucide-minimize-2 class="me-1" /> Lipat Semua
            </button>
        </div>

        <div class="space-y-4">
            @foreach($groupedPOs as $dateKey => $poGroup)
                @php
                    $isNoDate = $dateKey === 'Tanpa Tanggal';
                    $carbonDate = !$isNoDate ? \Carbon\Carbon::parse($dateKey) : null;
                    $isToday = $carbonDate ? $carbonDate->isToday() : false;
                    $dateTitle = $carbonDate ? $carbonDate->translatedFormat('l, d F Y') : 'Tanggal Tidak Ditentukan';
                    $groupTotalAmount = $poGroup->sum('total_amount');
                    $groupTotalReceipts = $poGroup->sum(fn($p) => $p->receipts ? $p->receipts->sum('total_amount') : 0);
                    $groupId = 'group-' . md5($dateKey);
                @endphp

                <div class="card overflow-hidden" style="border:1px solid {{ $isToday ? '#bfdbfe' : '#e2e8f0' }};box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                    <!-- Header Tanggal Grup -->
                    <div class="card-header flex flex-wrap justify-between items-center cursor-pointer select-none"
                         style="background: {{ $isToday ? '#eff6ff' : '#f8fafc' }}; padding: 12px 18px; border-bottom: 1px solid #e2e8f0;"
                         onclick="toggleDateGroup('{{ $groupId }}')">
                        
                        <div class="flex items-center gap-3">
                            <span class="text-primary font-bold" style="font-size:15px;">
                                <x-lucide-calendar-days class="me-2 text-primary" />{{ $dateTitle }}
                            </span>
                            @if($isToday)
                                <span class="badge badge-primary" style="font-size:11px;">Hari Ini</span>
                            @endif
                            <span class="badge badge-secondary" style="font-size:11px;">
                                {{ $poGroup->count() }} PO
                            </span>
                        </div>

                        <div class="flex items-center gap-4 text-xs">
                            <div>
                                <span class="text-muted">Total Nilai:</span>
                                <strong class="text-primary font-semibold ms-1" style="font-size:13px;">
                                    @if($canViewPrice)
                                        Rp {{ number_format($groupTotalAmount, 0, ',', '.') }}
                                    @else
                                        Rp 0
                                    @endif
                                </strong>
                            </div>

                            @if($groupTotalReceipts > 0 && $canViewPrice)
                            <div class="hidden md:block">
                                <span class="text-muted">Nota:</span>
                                <strong class="text-success font-semibold ms-1">
                                    Rp {{ number_format($groupTotalReceipts, 0, ',', '.') }}
                                </strong>
                            </div>
                            @endif

                            <button type="button" class="btn btn-sm btn-icon text-muted" id="btn-{{ $groupId }}" style="width:28px;height:28px;">
                                <x-lucide-chevron-up class="transition-transform duration-200" id="icon-{{ $groupId }}" />
                            </button>
                        </div>
                    </div>

                    <!-- Tabel Item PO pada tanggal ini -->
                    <div id="{{ $groupId }}" class="table-wrap date-group-content">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width:5%;">#</th>
                                    <th style="width:16%;">No. PO</th>
                                    <th style="width:18%;">Supplier</th>
                                    <th style="width:24%;">Rincian Barang yang Dipesan</th>
                                    <th style="width:13%;">Target Kirim</th>
                                    <th style="width:14%; text-align:right;">Total Nilai PO</th>
                                    <th style="width:10%; text-align:center;">Status</th>
                                    <th style="width:10%; text-align:center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($poGroup as $j => $po)
                                <tr>
                                    <td class="text-muted">{{ $j + 1 }}</td>
                                    <td>
                                        <a href="{{ route('purchase-orders.show', $po) }}" class="font-semibold text-primary" style="text-decoration:none;">
                                            <code>{{ $po->po_number }}</code>
                                        </a>
                                        @if($po->procurementRequest)
                                            <div class="text-muted" style="font-size:10px;">
                                                PR: {{ $po->procurementRequest->pr_number }}
                                            </div>
                                        @endif
                                        @php
                                            $linkedMr = $po->materialRequest ?? $po->items->firstWhere('material_request_item_id', '!=', null)?->materialRequestItem?->materialRequest;
                                        @endphp
                                        @if($linkedMr)
                                            <div style="font-size:10px;margin-top:2px;">
                                                <a href="{{ route('material-requests.show', $linkedMr) }}" style="color:#1d4ed8;text-decoration:none;font-weight:600;" title="PO Lapangan">
                                                    <x-lucide-clipboard-check class="me-1" />#{{ $linkedMr->request_number }}
                                                </a>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $po->display_supplier_name }}</strong>
                                        @if($po->isManualSupplier())
                                            <div class="mt-1">
                                                <span class="badge" style="background:#fef3c7;color:#92400e;font-size:9.5px;padding:1px 5px;font-weight:500;">
                                                    Vendor Bebas
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="space-y-1">
                                            @foreach($po->items->take(3) as $poItem)
                                                <div class="text-xs flex items-center gap-1">
                                                    <x-lucide-circle-check class="text-primary" style="font-size:9px;" />
                                                    <span>{{ $poItem->displayName() }}</span>
                                                    <span class="text-muted">({{ format_quantity($poItem->quantity) }} {{ $poItem->displayUnit() }})</span>
                                                    @if($poItem->material_request_item_id)
                                                        <span class="badge" style="background:#e0f2fe;color:#0369a1;font-size:9px;padding:1px 4px;">Dari MR</span>
                                                    @elseif($poItem->isCustom())
                                                        <span class="badge" style="background:#fef3c7;color:#92400e;font-size:9px;padding:1px 4px;">Barang Baru</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                            @if($po->items->count() > 3)
                                                <div class="text-muted text-xs italic">
                                                    + {{ $po->items->count() - 3 }} barang lainnya...
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-xs">
                                        {{ $po->expected_delivery ? $po->expected_delivery->format('d M Y') : '-' }}
                                    </td>
                                    <td style="text-align:right;">
                                        @if($canViewPrice)
                                            <strong class="text-primary" style="font-size:13px;">
                                                Rp {{ number_format($po->total_amount, 0, ',', '.') }}
                                            </strong>
                                            @php
                                                $totalNota = $po->receipts ? $po->receipts->sum('total_amount') : 0;
                                            @endphp
                                            @if($totalNota > 0)
                                                <div class="text-success text-xs font-medium">
                                                    Nota: Rp {{ number_format($totalNota, 0, ',', '.') }}
                                                </div>
                                            @endif
                                        @else
                                            <strong class="text-muted" style="font-size:13px;" title="Dirahasiakan untuk role lapangan">
                                                Rp 0
                                            </strong>
                                        @endif
                                    </td>
                                    <td style="text-align:center;">
                                        <span class="badge badge-{{ $po->status_color }}">{{ $po->status_label }}</span>
                                    </td>
                                    <td style="text-align:center;">
                                        <div class="flex justify-center gap-1">
                                            <a href="{{ route('purchase-orders.show', $po) }}" class="btn btn-sm btn-secondary btn-icon" title="Detail PO">
                                                <x-lucide-eye />
                                            </a>
                                            @can('update', $po)
                                                @if(!in_array($po->status, ['cancelled', 'closed', 'received']) && !$po->hasConfirmedGoodsReceipt())
                                                    <a href="{{ route('purchase-orders.edit', $po) }}" class="btn btn-sm btn-outline-warning btn-icon" title="Edit PO">
                                                        <x-lucide-pencil />
                                                    </a>
                                                @endif
                                            @endcan
                                            @if($canViewPrice)
                                                <a href="{{ route('purchase-orders.print', ['purchaseOrder' => $po->id, 'mode' => 'official']) }}" target="_blank" class="btn btn-sm btn-light border btn-icon" style="color:#0f766e;" title="Cetak PO Resmi (Harga)">
                                                    <x-lucide-printer />
                                                </a>
                                                <a href="{{ route('purchase-orders.print', ['purchaseOrder' => $po->id, 'mode' => 'blind']) }}" target="_blank" class="btn btn-sm btn-light border btn-icon" style="color:#475569;" title="Cetak PO Lapangan (Tanpa Harga / Rp 0)">
                                                    <x-lucide-shield-check />
                                                </a>
                                            @else
                                                <a href="{{ route('purchase-orders.print', ['purchaseOrder' => $po->id, 'mode' => 'blind']) }}" target="_blank" class="btn btn-sm btn-light border btn-icon" style="color:#475569;" title="Cetak PO Lapangan">
                                                    <x-lucide-printer />
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>

        @if($pos->hasPages())
        <div class="card-footer mt-4" style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:12px 16px;">
            {{ $pos->links() }}
        </div>
        @endif
    @else
        <div class="card text-center py-8" style="padding:40px 20px;">
            <x-lucide-receipt class="text-muted mb-3" style="font-size:36px;color:#94a3b8;" />
            <h4 class="font-semibold text-slate-700 mb-1">Belum Ada Purchase Order</h4>
            <p class="text-muted text-sm mb-4">Tidak ada PO yang sesuai dengan filter atau belum ada PO yang dibuat.</p>
            <div>
                <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary">
                    <x-lucide-plus class="me-1" /> Buat PO Pertama
                </a>
            </div>
        </div>
    @endif

    <script>
        function toggleDateGroup(groupId) {
            const el = document.getElementById(groupId);
            const icon = document.getElementById('icon-' + groupId);
            if (!el) return;

            if (el.style.display === 'none') {
                el.style.display = 'block';
                if (icon) icon.style.transform = 'rotate(0deg)';
            } else {
                el.style.display = 'none';
                if (icon) icon.style.transform = 'rotate(180deg)';
            }
        }

        function toggleAllGroups(show) {
            document.querySelectorAll('.date-group-content').forEach(function(content) {
                content.style.display = show ? 'block' : 'none';
            });
            document.querySelectorAll('[id^="icon-group-"]').forEach(function(icon) {
                icon.style.transform = show ? 'rotate(0deg)' : 'rotate(180deg)';
            });
        }
    </script>
</x-app-layout>
