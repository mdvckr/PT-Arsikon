<x-app-layout>
    <x-slot name="title">Kehilangan Barang di Perjalanan (Transit Loss)</x-slot>

    @push('styles')
    <style>
        .loss-filter-grid {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        .loss-filter-grid .filter-col {
            flex: 1;
            min-width: 180px;
        }
        .loss-table th {
            padding: 8px 12px !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.4px !important;
            color: #64748b !important;
            background: #f8fafc !important;
            border-bottom: 1px solid #e2e8f0 !important;
            white-space: nowrap !important;
        }
        .loss-table td {
            padding: 10px 12px !important;
            vertical-align: middle !important;
            font-size: 12px !important;
        }
    </style>
    @endpush

    <div class="breadcrumb mb-2" style="font-size:12px;">
        <a href="{{ route('goods-receipts.index') }}"><x-lucide-package-check style="font-size:11px;" /> Logistik & Penerimaan</a>
        <span class="breadcrumb-sep"><x-lucide-chevron-right style="font-size:9px;" /></span>
        <span>Kehilangan Barang Transit</span>
    </div>

    <div class="flex items-center justify-between flex-wrap gap-2 mb-3">
        <div>
            <h2 class="fw-700" style="font-size:18px;color:#0f172a;margin:0;display:flex;align-items:center;gap:8px;">
                <x-lucide-triangle-alert class="text-warning" /> Kehilangan Barang di Perjalanan (Transit Loss)
            </h2>
            <p class="text-muted" style="font-size:12.5px;margin-top:2px;margin-bottom:0;">
                Daftar selisih barang hilang saat pengiriman vendor dengan bukti surat jalan dan penelusuran klaim.
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('goods-receipts.index') }}" class="btn btn-secondary btn-sm" style="border-radius:6px;font-size:12px;">
                <x-lucide-arrow-left /> Kembali ke Penerimaan
            </a>
        </div>
    </div>

    @if (session('success'))
    <div class="alert alert-success mb-3" style="border-radius:8px;padding:10px 14px;font-size:12.5px;">
        <x-lucide-circle-check /> {{ session('success') }}
    </div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger mb-3" style="border-radius:8px;padding:10px 14px;font-size:12.5px;">
        <x-lucide-circle-alert /> {{ session('error') }}
    </div>
    @endif

    {{-- Filter Card --}}
    <div class="card mb-3" style="border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;padding:14px 16px;">
        <form method="GET" action="{{ route('transit-losses.index') }}">
            <div class="loss-filter-grid">
                <div class="filter-col" style="flex:2;min-width:220px;">
                    <label class="form-label" style="font-size:11.5px;font-weight:600;color:#475569;margin-bottom:4px;">Cari Dokumen / Catatan</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="No GR, No PO, nama ekspedisi/sopir...">
                </div>
                <div class="filter-col">
                    <label class="form-label" style="font-size:11.5px;font-weight:600;color:#475569;margin-bottom:4px;">Status Klaim</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="">— Semua Status —</option>
                        <option value="recorded" {{ request('status') === 'recorded' ? 'selected' : '' }}>Recorded (Menunggu Approval)</option>
                        <option value="claim_filed" {{ request('status') === 'claim_filed' ? 'selected' : '' }}>Claim Filed (Disetujui/Klaim Diajukan)</option>
                        <option value="recovered" {{ request('status') === 'recovered' ? 'selected' : '' }}>Recovered (Diganti Vendor)</option>
                        <option value="written_off" {{ request('status') === 'written_off' ? 'selected' : '' }}>Written Off (Dihapusbukukan)</option>
                    </select>
                </div>
                <div style="display:flex;gap:6px;">
                    <button type="submit" class="btn btn-primary btn-sm" style="height:36px;padding:0 14px;">
                        <x-lucide-filter class="me-1" /> Filter
                    </button>
                    @if(request()->anyFilled(['search', 'status']))
                    <a href="{{ route('transit-losses.index') }}" class="btn btn-light border btn-sm" style="height:36px;padding:0 12px;">Reset</a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- Main Table --}}
    <div class="card" style="border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;overflow:hidden;">
        <div class="table-wrap">
            <table class="data-table loss-table mb-0">
                <thead>
                    <tr>
                        <th style="width:120px;">No. Dokumen</th>
                        <th>Item Barang & Vendor</th>
                        <th style="text-align:center;width:100px;">Qty Hilang</th>
                        <th style="text-align:right;width:120px;">Nilai Kerugian</th>
                        <th style="width:180px;">Catatan & Bukti</th>
                        <th style="width:110px;text-align:center;">Status</th>
                        <th style="width:140px;text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transitLosses as $loss)
                    @php
                        $grItem = $loss->goodsReceiptItem;
                        $itemName = $loss->material?->name ?? $loss->tool?->name ?? $grItem?->displayName() ?? '-';
                        $unitName = $loss->material?->unit?->abbreviation ?? 'Unit';
                        $gr = $loss->goodsReceipt;
                        $po = $loss->purchaseOrder;
                        $canApprove = auth()->user()->can('approve transit losses')
                            && $loss->status === 'recorded'
                            && auth()->id() !== (int)$loss->recorded_by
                            && (!$gr || !in_array(auth()->id(), array_filter([(int)$gr->created_by, (int)$gr->confirmed_by, (int)$gr->received_by_user_id])));
                    @endphp
                    <tr>
                        <td>
                            @if($gr)
                                <div><a href="{{ route('goods-receipts.show', $gr) }}" class="fw-700 text-primary">#{{ $gr->receipt_number }}</a></div>
                            @endif
                            @if($po)
                                <div style="font-size:11px;color:#64748b;margin-top:2px;">PO: <a href="{{ route('purchase-orders.show', $po) }}">#{{ $po->po_number }}</a></div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-700" style="color:#0f172a;">{{ $itemName }}</div>
                            <div class="text-muted" style="font-size:11px;margin-top:2px;">
                                Vendor: <strong>{{ $po?->supplier?->name ?? $gr?->supplier_name ?? '-' }}</strong>
                            </div>
                        </td>
                        <td style="text-align:center;font-weight:700;color:#dc2626;font-size:13px;">
                            {{ format_quantity($loss->qty) }} <span style="font-size:10.5px;color:#64748b;font-weight:400;">{{ $unitName }}</span>
                        </td>
                        <td style="text-align:right;font-weight:600;color:#0f172a;">
                            Rp {{ number_format($loss->loss_value, 0, ',', '.') }}
                        </td>
                        <td>
                            <div style="font-size:11.5px;color:#334155;line-height:1.4;">{{ Str::limit($loss->notes, 75) }}</div>
                            <div style="margin-top:4px;display:flex;align-items:center;gap:6px;">
                                @if($loss->evidence_path)
                                    <a href="{{ asset('storage/' . $loss->evidence_path) }}" target="_blank" class="badge" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;text-decoration:none;">
                                        <x-lucide-file-down class="me-1" /> Lihat Bukti
                                    </a>
                                @endif
                                <span class="text-muted" style="font-size:10px;">Oleh: {{ $loss->recordedByUser?->name ?? 'User' }}</span>
                            </div>
                        </td>
                        <td style="text-align:center;">
                            @if($loss->status === 'recorded')
                                <span class="badge" style="background:#fef3c7;color:#b45309;border:1px solid #fde68a;">
                                    <x-lucide-clock class="me-1" /> Recorded
                                </span>
                            @elseif($loss->status === 'claim_filed')
                                <span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;">
                                    <x-lucide-file-text class="me-1" /> Claim Filed
                                </span>
                            @elseif($loss->status === 'recovered')
                                <span class="badge" style="background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;">
                                    <x-lucide-circle-check class="me-1" /> Recovered
                                </span>
                            @elseif($loss->status === 'written_off')
                                <span class="badge" style="background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;">
                                    <x-lucide-ban class="me-1" /> Written Off
                                </span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            @if($canApprove)
                                <form method="POST" action="{{ route('transit-losses.approve', $loss) }}" style="display:inline-block;margin:0;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" style="font-size:11.5px;padding:3px 8px;border-radius:5px;" onclick="return confirm('Setujui laporan kehilangan ini? Kuota PO akan dipotong dan dinyatakan hilang sah.')">
                                        <x-lucide-check class="me-1" /> Setujui
                                    </button>
                                </form>
                            @elseif($loss->status === 'claim_filed' && auth()->user()->can('approve transit losses'))
                                <div style="display:inline-flex;gap:4px;">
                                    <form method="POST" action="{{ route('transit-losses.status', $loss) }}" style="margin:0;">
                                        @csrf
                                        <input type="hidden" name="status" value="recovered">
                                        <button type="submit" class="btn btn-sm btn-outline-success" style="font-size:11px;padding:2px 6px;" title="Tandai Diganti Vendor">
                                            Recovered
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('transit-losses.status', $loss) }}" style="margin:0;">
                                        @csrf
                                        <input type="hidden" name="status" value="written_off">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" style="font-size:11px;padding:2px 6px;" title="Tandai Dihapusbukukan">
                                            Write-off
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span class="text-muted" style="font-size:11px;">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center;color:#94a3b8;padding:24px;">
                            Belum ada laporan kehilangan barang di perjalanan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transitLosses->hasPages())
        <div style="padding:12px 16px;border-top:1px solid #e2e8f0;background:#f8fafc;">
            {{ $transitLosses->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
