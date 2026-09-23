<x-app-layout>
    <x-slot name="title">Penerimaan Barang</x-slot>

    @push('styles')
    <style>
        .receipt-filter-grid {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .receipt-filter-grid .filter-col {
            flex: 1;
            min-width: 180px;
        }

        .receipt-filter-grid .form-control {
            height: 36px !important;
            padding: 6px 10px !important;
            font-size: 12px !important;
            line-height: 1.4 !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            box-sizing: border-box !important;
            background-color: #ffffff !important;
            color: #0f172a !important;
        }

        .receipt-filter-grid .form-control:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 2px rgba(37,99,235,0.15) !important;
            outline: none !important;
        }

        .receipt-filter-grid .btn {
            height: 36px !important;
            padding: 0 14px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 6px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            box-sizing: border-box !important;
        }

        .receipt-table th {
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

        .receipt-table td {
            padding: 8px 12px !important;
            vertical-align: middle !important;
        }

        .nowrap {
            white-space: nowrap !important;
        }

        @media (max-width: 640px) {
            .page-header-flex {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 10px;
            }
            .page-header-flex a {
                width: 100%;
                justify-content: center;
            }
            .receipt-filter-grid .filter-col {
                min-width: 100%;
            }
        }
    </style>
    @endpush

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-3 page-header-flex">
        <div>
            <h2 class="fw-700" style="font-size:16px;color:#0f172a;margin:0;display:flex;align-items:center;gap:6px;">
                <i class="fas fa-truck-ramp-box text-primary" style="font-size:15px;"></i>
                Penerimaan Barang Supplier
            </h2>
            <p class="text-muted" style="font-size:12px;margin-top:2px;margin-bottom:0;">
                Catat dan pantau penerimaan material / barang masuk dari supplier ke gudang
            </p>
        </div>
        @can('create goods receipts')
        <a href="{{ route('goods-receipts.create') }}" class="btn btn-primary btn-sm" style="border-radius:6px;padding:6px 14px;font-size:12px;font-weight:600;box-shadow:0 2px 4px rgba(37,99,235,0.2);">
            <i class="fas fa-plus me-1"></i> Catat Penerimaan
        </a>
        @endcan
    </div>

    {{-- Alerts --}}
    @if (session('success'))
    <div class="alert alert-success mb-3" style="border-radius:6px;padding:8px 12px;font-size:12px;">
        <i class="fas fa-circle-check"></i> {{ session('success') }}
    </div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger mb-3" style="border-radius:6px;padding:8px 12px;font-size:12px;">
        <i class="fas fa-circle-exclamation"></i> {{ session('error') }}
    </div>
    @endif

    {{-- Filter Card --}}
    <div class="card mb-3" style="border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
        <div class="card-body" style="padding:12px 16px;">
            <form method="GET" class="receipt-filter-grid">
                <div class="filter-col" style="flex: 2; min-width: 220px;">
                    <label class="form-label" style="font-size:11px;font-weight:600;color:#475569;margin-bottom:3px;">Pencarian</label>
                    <div style="position:relative;">
                        <i class="fas fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);font-size:11px;color:#94a3b8;pointer-events:none;"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="form-control" placeholder="No. tanda terima, invoice, atau supplier..." style="padding-left:28px !important;">
                    </div>
                </div>

                @if(isset($warehouses) && $warehouses->count() > 1)
                <div class="filter-col" style="max-width:180px;">
                    <label class="form-label" style="font-size:11px;font-weight:600;color:#475569;margin-bottom:3px;">Gudang</label>
                    <select name="warehouse_id" class="form-control">
                        <option value="">Semua Gudang</option>
                        @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ (string)($warehouseId) === (string)$wh->id ? 'selected' : '' }}>
                            {{ $wh->name }} {{ $wh->is_central ? '(Pusat)' : '' }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="filter-col" style="max-width:160px;">
                    <label class="form-label" style="font-size:11px;font-weight:600;color:#475569;margin-bottom:3px;">Status</label>
                    <select name="status" class="form-control">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Dikonfirmasi</option>
                    </select>
                </div>

                <div style="display:flex;gap:6px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('goods-receipts.index') }}" class="btn btn-secondary" title="Reset Filter" style="padding:0 12px !important;">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="card" style="border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;box-shadow:0 2px 6px -1px rgba(0,0,0,0.03);overflow:hidden;">
        <div class="table-wrap">
            <table class="data-table receipt-table">
                <thead>
                    <tr>
                        <th style="width:160px;">No. Tanda Terima</th>
                        <th>Supplier</th>
                        <th>Gudang Penerima</th>
                        <th style="width:110px;">Tgl. Terima</th>
                        <th>Invoice / Surat Jalan</th>
                        <th>Purchase Order</th>
                        <th style="width:130px;text-align:center;">Status</th>
                        <th style="width:50px;text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receipts as $r)
                    <tr>
                        <td class="nowrap">
                            <a href="{{ route('goods-receipts.show', $r) }}" style="color:#2563eb;font-weight:700;font-size:12.5px;text-decoration:none;display:inline-flex;align-items:center;gap:5px;">
                                <i class="fas fa-receipt text-primary" style="font-size:11px;"></i>
                                {{ $r->receipt_number }}
                            </a>
                        </td>
                        <td>
                            <div class="fw-600" style="font-size:12px;color:#0f172a;display:flex;align-items:center;gap:5px;">
                                <i class="fas fa-building text-muted" style="font-size:10px;"></i>
                                {{ $r->supplier?->name ?? '—' }}
                            </div>
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:6px;">
                                <span style="font-size:12px;font-weight:500;color:#334155;">{{ $r->warehouse?->name ?? '—' }}</span>
                                @if($r->warehouse?->is_central)
                                    <span class="badge" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;font-size:9.5px;padding:1px 5px;border-radius:4px;font-weight:700;">Pusat</span>
                                @else
                                    <span class="badge" style="background:#fff7ed;color:#c2410c;border:1px solid #fed7aa;font-size:9.5px;padding:1px 5px;border-radius:4px;font-weight:700;">Proyek</span>
                                @endif
                            </div>
                        </td>
                        <td class="nowrap text-muted" style="font-size:12px;">
                            <i class="fas fa-calendar text-muted me-1" style="font-size:10.5px;"></i>
                            {{ $r->receipt_date ? \Carbon\Carbon::parse($r->receipt_date)->format('d/m/Y') : '—' }}
                        </td>
                        <td>
                            @if($r->invoice_number)
                                <code style="font-size:11px;background:#f1f5f9;color:#334155;padding:2px 6px;border-radius:4px;border:1px solid #e2e8f0;">
                                    {{ $r->invoice_number }}
                                </code>
                            @else
                                <span class="text-muted" style="font-size:11.5px;">—</span>
                            @endif
                        </td>
                        <td>
                            @if($r->purchaseOrder)
                                <a href="{{ route('purchase-orders.show', $r->purchaseOrder) }}" class="badge" style="background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;font-size:10.5px;padding:3px 7px;border-radius:4px;text-decoration:none;font-weight:600;">
                                    <i class="fas fa-file-invoice me-1"></i> #{{ $r->purchaseOrder->po_number }}
                                </a>
                            @else
                                <span class="text-muted" style="font-size:11.5px;">— Non-PO —</span>
                            @endif
                        </td>
                        <td style="text-align:center;" class="nowrap">
                            @if($r->status === 'confirmed')
                                <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:10.5px;padding:2px 8px;border-radius:12px;font-weight:600;">
                                    <i class="fas fa-circle-check me-1"></i> Dikonfirmasi
                                </span>
                            @else
                                <span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-size:10.5px;padding:2px 8px;border-radius:12px;font-weight:600;">
                                    <i class="fas fa-clock me-1"></i> Draft
                                </span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            <a href="{{ route('goods-receipts.show', $r) }}"
                                class="btn btn-sm btn-light border" title="Lihat Rincian Penerimaan"
                                style="width:28px;height:28px;padding:0;display:inline-flex;align-items:center;justify-content:center;border-radius:5px;font-size:11px;color:#475569;">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state" style="padding:36px 16px;text-align:center;">
                                <i class="fas fa-truck-ramp-box" style="font-size:32px;color:#cbd5e1;margin-bottom:8px;"></i>
                                <h3 style="font-size:14px;color:#475569;margin-bottom:3px;">Belum Ada Data Penerimaan Barang</h3>
                                <p style="font-size:12px;color:#94a3b8;margin:0;">Catat penerimaan barang baru dari supplier dengan klik tombol di atas.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($receipts->hasPages())
        <div style="padding:10px 16px;border-top:1px solid #f1f5f9;background:#fafafa;">
            {{ $receipts->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
