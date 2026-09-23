<x-app-layout>
    <x-slot name="title">Buat Surat Jalan / Pengiriman Barang & Alat</x-slot>

    @push('styles')
    <style>
        .distribution-create-layout {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 20px;
            align-items: start;
        }

        .source-selection-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .item-adder-grid {
            display: grid;
            grid-template-columns: 130px 1fr 140px auto;
            gap: 12px;
            align-items: end;
        }

        .delivery-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        @media (max-width: 992px) {
            .distribution-create-layout {
                grid-template-columns: 1fr !important;
            }
            .sidebar-sticky-route {
                position: static !important;
            }
        }

        @media (max-width: 768px) {
            .item-adder-grid {
                grid-template-columns: 1fr 1fr !important;
                gap: 10px;
            }
            .item-adder-grid > div:nth-child(2),
            .item-adder-grid > div:nth-child(3) {
                grid-column: span 2;
            }
            .item-adder-grid > div:last-child {
                grid-column: span 2;
            }
            .item-adder-grid > div:last-child button {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 640px) {
            .source-selection-grid {
                grid-template-columns: 1fr !important;
                gap: 12px;
            }
            .delivery-info-grid {
                grid-template-columns: 1fr !important;
                gap: 12px;
            }
        }

        /* Consistent table styling */
        #items-table thead th {
            padding: 7px 10px !important;
            font-size: 10.5px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.3px !important;
            color: #64748b !important;
            background: #fafafa !important;
            border-bottom: 1px solid #e2e8f0 !important;
            white-space: nowrap !important;
        }

        #items-table tbody td {
            padding: 8px 10px !important;
            vertical-align: middle !important;
            font-size: 12px !important;
        }

    </style>
    @endpush

    <div class="breadcrumb">
        <a href="{{ route('distributions.index') }}">Distribusi & Surat Jalan</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>Buat Surat Jalan Baru</span>
    </div>

    @if(session('error'))
    <div class="alert alert-danger" style="margin-bottom:16px;padding:12px 16px;border-radius:8px;font-size:14px;background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;display:flex;align-items:center;gap:10px;">
        <i class="fas fa-exclamation-circle" style="font-size:16px;"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger" style="margin-bottom:16px;padding:12px 16px;border-radius:8px;font-size:14px;background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;">
        <div style="display:flex;align-items:center;gap:8px;font-weight:600;margin-bottom:6px;">
            <i class="fas fa-exclamation-triangle"></i> Periksa kembali data formulir:
        </div>
        <ul style="margin:0 0 0 20px;padding:0;font-size:13px;">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div id="ajax-alert" style="display:none;margin-bottom:16px;padding:12px 16px;border-radius:8px;font-size:14px;"></div>

    @php
        $whs = [];
        foreach ($warehouses as $w) {
            $whs[] = ['id' => $w->id, 'name' => $w->name, 'is_central' => (bool) $w->is_central];
        }
        $matsGroupedJson = $materialsGrouped ?? collect([]);
        $matsDataJson    = $materialsData ?? collect([]);
        $toolsGroupedJson= $toolsGrouped ?? collect([]);
        $toolsDataJson   = $toolsData ?? collect([]);
        $mrsJson         = $mrsFormatted ?? collect([]);
        $tasJson         = $tasFormatted ?? collect([]);
        $initialMrId     = $selectedMrId ?? old('material_request_id');
        $initialLoanId   = $selectedLoanId ?? old('tool_loan_id');
    @endphp

    <form id="distribution-form" method="POST" action="{{ route('distributions.store') }}">
        @csrf

        {{-- Hidden fields for tracking source --}}
        <input type="hidden" name="material_request_id" id="hidden_material_request_id" value="{{ $initialMrId }}">
        <div id="hidden_tool_assignment_ids_container"></div>

        <div class="distribution-create-layout mb-4">

            <div style="display:flex;flex-direction:column;gap:20px;">

                {{-- CARD 1: Sumber Permintaan Disetujui --}}
                <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;background:#ffffff;box-shadow:0 4px 12px -2px rgba(0,0,0,0.04);overflow:hidden;">
                    <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:14px 18px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <i class="fas fa-file-invoice text-primary" style="font-size:15px;"></i>
                            <span class="card-title" style="font-size:14px;font-weight:700;color:#1e293b;">Pilih Sumber Permintaan</span>
                        </div>
                        <div id="source-active-indicator" style="display:none;align-items:center;gap:8px;flex-wrap:wrap;">
                            <span id="badge-mr-connected" class="badge" style="display:none;font-size:11.5px;padding:4px 10px;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;align-items:center;gap:6px;border-radius:6px;font-weight:600;">
                                MR: <strong id="mr-active-name">-</strong>
                                <button type="button" onclick="clearMrSource()" style="background:none;border:none;color:#64748b;cursor:pointer;padding:0 2px;margin-left:4px;" title="Batalkan MR ini">
                                    <i class="fas fa-xmark"></i>
                                </button>
                            </span>
                            <span id="badge-ta-connected" class="badge" style="display:none;font-size:11.5px;padding:4px 10px;background:#f5f3ff;color:#6d28d9;border:1px solid #ddd6fe;align-items:center;gap:6px;border-radius:6px;font-weight:600;">
                                Alat: <strong id="ta-active-name">-</strong>
                                <button type="button" onclick="clearTaSource()" style="background:none;border:none;color:#64748b;cursor:pointer;padding:0 2px;margin-left:4px;" title="Batalkan Peminjaman Alat ini">
                                    <i class="fas fa-xmark"></i>
                                </button>
                            </span>
                            <button type="button" class="btn btn-sm btn-light border text-danger" onclick="clearSelectedSource()" style="height:28px;padding:2px 10px;font-size:11.5px;font-weight:600;border-radius:6px;" title="Batalkan semua sumber">
                                <i class="fas fa-xmark"></i> Reset
                            </button>
                        </div>
                    </div>
                    <div class="card-body" style="padding:18px;">
                        <p class="text-muted" style="font-size:12px;margin-bottom:14px;line-height:1.4;color:#64748b;">
                            Pilih dokumen permintaan yang telah disetujui untuk mengisi rute dan daftar barang secara otomatis:
                        </p>

                        <div class="source-selection-grid">
                            {{-- Dropdown MR --}}
                            <div>
                                <label class="form-label" style="font-size:12px;font-weight:600;color:#334155;margin-bottom:6px;">
                                    Permintaan Material (MR)
                                </label>
                                <select id="select-source-mr" class="form-control" style="font-size:12.5px;height:38px;border-radius:8px;font-weight:500;border-color:#cbd5e1;" onchange="onSourceMrSelected(this.value)">
                                    <option value="">— Pilih Permintaan Material —</option>
                                    @foreach($mrsJson as $mr)
                                    <option value="{{ $mr['id'] }}" {{ $initialMrId == $mr['id'] ? 'selected' : '' }}>
                                        #{{ $mr['number'] }} &bull; {{ $mr['to_warehouse'] }} ({{ $mr['requester'] }}) [{{ $mr['status_label'] }}]
                                    </option>
                                    @endforeach
                                </select>
                                @if(empty($mrsJson) || count($mrsJson) === 0)
                                <div class="text-muted" style="font-size:11px;margin-top:4px;color:#94a3b8;">
                                    Tidak ada antrean Permintaan Material yang menunggu pengiriman.
                                </div>
                                @endif
                            </div>

                            {{-- Dropdown Peminjaman Alat --}}
                            <div>
                                <label class="form-label" style="font-size:12px;font-weight:600;color:#334155;margin-bottom:6px;">
                                    Peminjaman Alat Kerja
                                </label>
                                <select id="select-source-ta" class="form-control" style="font-size:12.5px;height:38px;border-radius:8px;font-weight:500;border-color:#cbd5e1;" onchange="onSourceTaSelected(this.value)">
                                    <option value="">— Pilih Peminjaman Alat —</option>
                                    @foreach($tasJson as $ta)
                                    <option value="{{ $ta['id'] }}" {{ $initialLoanId == $ta['id'] ? 'selected' : '' }}>
                                        #{{ $ta['number'] }} &bull; {{ $ta['borrower'] }} ({{ count($ta['items']) }} Alat) - {{ $ta['location'] }}
                                    </option>
                                    @endforeach
                                </select>
                                @if(empty($tasJson) || count($tasJson) === 0)
                                <div class="text-muted" style="font-size:11px;margin-top:4px;color:#94a3b8;">
                                    Tidak ada pengajuan peminjaman alat yang menunggu pengiriman.
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD 2: Daftar Barang / Alat yang Dikirim --}}
                <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;background:#ffffff;box-shadow:0 4px 12px -2px rgba(0,0,0,0.04);overflow:hidden;">
                    <div class="card-header flex justify-between items-center" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:14px 18px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <i class="fas fa-boxes-stacked text-primary" style="font-size:15px;"></i>
                            <span class="card-title" style="font-size:14px;font-weight:700;color:#1e293b;">Barang & Alat yang Dikirim</span>
                        </div>
                        <div>
                            <span id="item-count-badge" class="badge" style="background:#e2e8f0;color:#475569;font-size:11px;padding:3px 9px;border-radius:20px;font-weight:600;">0 item</span>
                        </div>
                    </div>

                    {{-- Form Input Item Manual / Tambahan --}}
                    <div id="item-adder-box" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:14px 18px;">
                        <div style="font-weight:600;font-size:12.5px;margin-bottom:10px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;color:#475569;">
                            <div style="display:flex;align-items:center;gap:6px;">
                                <i class="fas fa-plus text-primary" style="font-size:11px;"></i> Tambah Barang Manual (Opsional)
                            </div>
                            <div id="selected-stock-badge-container" style="display:none;">
                                <span id="selected-stock-badge" class="badge" style="background:#e0f2fe;color:#0369a1;font-size:11px;padding:3px 8px;border-radius:4px;font-weight:600;">
                                    Stok Gudang Pusat: <strong id="selected-stock-val">0</strong>
                                </span>
                            </div>
                        </div>

                        <div class="item-adder-grid">
                            <div>
                                <label class="form-label" style="font-size:12px;margin-bottom:4px;font-weight:600;color:#475569;">Tipe Item</label>
                                <select id="manual-type" class="form-control" onchange="updateManualSelect()" style="height:38px;border-radius:8px;font-size:12.5px;">
                                    <option value="material">Material</option>
                                    <option value="tool">Alat Kerja</option>
                                    <option value="custom">Item Custom</option>
                                </select>
                            </div>

                            <div id="manual-select-container">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                                    <label class="form-label" style="font-size:12px;margin:0;font-weight:600;color:#475569;">Pilih Barang / Alat</label>
                                    <span id="opt-count-label" class="text-muted" style="font-size:11px;"></span>
                                </div>
                                <div>
                                    <select id="manual-item-select" class="form-control" onchange="onItemSelectionChanged()" style="font-size:12.5px;height:38px;border-radius:8px;">
                                        <option value="">— Pilih Material —</option>
                                    </select>
                                </div>
                            </div>

                            <div id="manual-custom-container" style="display:none;">
                                <div style="display:grid;grid-template-columns:2fr 1fr;gap:8px;">
                                    <div>
                                        <label class="form-label" style="font-size:12px;margin-bottom:4px;font-weight:600;color:#475569;">Nama Barang <span class="text-danger">*</span></label>
                                        <input type="text" id="manual-custom-name" class="form-control" placeholder="Contoh: Terpal Plastik Biru 4x6" style="height:38px;border-radius:8px;font-size:12.5px;">
                                    </div>
                                    <div>
                                        <label class="form-label" style="font-size:12px;margin-bottom:4px;font-weight:600;color:#475569;">Satuan</label>
                                        <input type="text" id="manual-custom-unit" class="form-control" placeholder="pcs" value="pcs" style="height:38px;border-radius:8px;font-size:12.5px;" oninput="onCustomUnitChanged(this.value)">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="form-label" style="font-size:12px;margin-bottom:4px;font-weight:600;color:#475569;">Jumlah (Qty)</label>
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <input type="number" id="manual-qty" class="form-control" placeholder="Qty" min="0.01" step="0.01" value="1" style="height:38px;text-align:center;font-weight:700;border-radius:8px;font-size:13px;" oninput="validateManualQty()">
                                    <span id="manual-unit-label" class="text-muted" style="font-size:12px;font-weight:600;min-width:30px;">pcs</span>
                                </div>
                            </div>

                            <div>
                                <button type="button" class="btn btn-primary" onclick="addManualItem()" style="height:38px;white-space:nowrap;display:inline-flex;align-items:center;gap:6px;font-weight:600;border-radius:8px;padding:0 16px;">
                                    <i class="fas fa-plus" style="font-size:12px;"></i> <span>Tambah</span>
                                </button>
                            </div>
                        </div>

                        <div id="manual-warning" class="text-danger" style="display:none;font-size:11.5px;font-weight:600;margin-top:8px;"></div>
                        <div id="manual-hint" class="text-muted" style="font-size:11.5px;margin-top:6px;color:#94a3b8;"></div>
                    </div>

                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr style="background:#fafafa;border-bottom:1px solid #e2e8f0;font-size:11.5px;text-transform:uppercase;letter-spacing:0.5px;color:#64748b;">
                                    <th style="width:14%;padding:10px 14px;">Tipe</th>
                                    <th style="width:50%;padding:10px 14px;">Barang / Alat</th>
                                    <th style="width:24%;padding:10px 14px;">Jumlah (Qty)</th>
                                    <th style="width:12%;text-align:center;padding:10px 14px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <tr id="empty-row">
                                    <td colspan="4" style="text-align:center;padding:36px 20px;color:#94a3b8;">
                                        <i class="fas fa-inbox" style="font-size:26px;color:#cbd5e1;margin-bottom:8px;display:block;"></i>
                                        <div style="font-size:13px;font-weight:600;color:#64748b;">Belum ada barang atau alat yang dimasukkan</div>
                                        <div style="font-size:11.5px;color:#94a3b8;margin-top:3px;">Pilih dokumen permintaan di atas atau gunakan formulir tambah manual di atas</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- CARD 3: Tanggal & Catatan Pengiriman --}}
                <div class="card" style="border:1px solid #e2e8f0;border-radius:12px;background:#ffffff;box-shadow:0 4px 12px -2px rgba(0,0,0,0.04);overflow:hidden;">
                    <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:14px 18px;display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-truck text-primary" style="font-size:15px;"></i>
                        <span class="card-title" style="font-size:14px;font-weight:700;color:#1e293b;">Informasi Pengiriman</span>
                    </div>
                    <div class="card-body" style="padding:18px;">
                        <div class="delivery-info-grid">
                            <div>
                                <label class="form-label" style="font-size:12px;font-weight:600;color:#334155;margin-bottom:6px;">Tanggal Kirim <span class="text-danger">*</span></label>
                                <input type="date" name="delivery_date" class="form-control" value="{{ old('delivery_date', date('Y-m-d')) }}" required style="height:38px;border-radius:8px;font-size:13px;">
                            </div>
                            <div>
                                <label class="form-label" style="font-size:12px;font-weight:600;color:#334155;margin-bottom:6px;">Nama Supir / Kurir</label>
                                <input type="text" name="driver_name" value="{{ old('driver_name') }}" placeholder="Contoh: Pak Budi" class="form-control" style="height:38px;border-radius:8px;font-size:13px;">
                            </div>
                            <div>
                                <label class="form-label" style="font-size:12px;font-weight:600;color:#334155;margin-bottom:6px;">No. Kendaraan (Plat)</label>
                                <input type="text" name="vehicle_number" value="{{ old('vehicle_number') }}" placeholder="Contoh: B 1234 CD" class="form-control" style="height:38px;border-radius:8px;font-size:13px;">
                            </div>
                            <div>
                                <label class="form-label" style="font-size:12px;font-weight:600;color:#334155;margin-bottom:6px;">Catatan Pengiriman</label>
                                <input type="text" name="notes" id="notesInput" value="{{ old('notes') }}" placeholder="Contoh: Pengiriman material tahap 1" class="form-control" style="height:38px;border-radius:8px;font-size:13px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Gudang & Submit: Redesigned Sleek Route Stepper --}}
            <div class="card sidebar-sticky-route" id="routeSidebarCard" style="position:sticky;top:20px;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden;box-shadow:0 4px 12px -2px rgba(0,0,0,0.04);">
                <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:14px 18px;display:flex;align-items:center;justify-content:space-between;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-route text-primary" style="font-size:15px;"></i>
                        <span class="card-title" style="font-size:14px;font-weight:700;color:#1e293b;">Rute Pengiriman</span>
                    </div>
                    <span class="badge" style="background:#eff6ff;color:#2563eb;font-size:11px;font-weight:600;padding:4px 8px;border-radius:20px;">
                        <i class="fas fa-lock" style="font-size:9.5px;"></i> Pusat &rarr; Proyek
                    </span>
                </div>
                <div class="card-body" style="padding:18px;">
                    {{-- Visual Connected Route Stepper --}}
                    <div style="position:relative;padding-left:30px;margin-bottom:18px;">
                        {{-- Dotted vertical connector line --}}
                        <div style="position:absolute;left:10px;top:18px;bottom:30px;width:2px;background:repeating-linear-gradient(to bottom, #94a3b8 0, #94a3b8 4px, transparent 4px, transparent 8px);"></div>

                        {{-- 1. Gudang Asal Node --}}
                        <div style="position:relative;margin-bottom:20px;">
                            {{-- Node Dot --}}
                            <div style="position:absolute;left:-30px;top:2px;width:22px;height:22px;border-radius:50%;background:#eff6ff;border:2px solid #3b82f6;display:flex;align-items:center;justify-content:center;z-index:2;">
                                <div style="width:8px;height:8px;border-radius:50%;background:#2563eb;"></div>
                            </div>

                            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:#64748b;margin-bottom:5px;">
                                Gudang Asal (Pengirim)
                            </div>

                            <input type="hidden" name="from_warehouse_id" id="from-warehouse" value="{{ $originWarehouse?->id ?? ($warehouses->firstWhere('is_central', true)?->id ?? $warehouses->first()?->id) }}">

                            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:9px 12px;display:flex;align-items:center;justify-content:space-between;gap:8px;">
                                <div style="font-weight:600;font-size:13px;color:#1e293b;display:flex;align-items:center;gap:8px;min-width:0;overflow:hidden;">
                                    <i class="fas fa-warehouse text-primary" style="font-size:13px;flex-shrink:0;"></i>
                                    <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                        {{ $originWarehouse?->name ?? 'Gudang Pusat' }}
                                    </span>
                                </div>
                                <span class="badge" style="background:#e0f2fe;color:#0369a1;font-size:10.5px;padding:2px 8px;border-radius:4px;font-weight:600;flex-shrink:0;">
                                    Pusat
                                </span>
                            </div>
                        </div>

                        {{-- 2. Gudang Tujuan Node --}}
                        <div style="position:relative;">
                            {{-- Node Dot --}}
                            <div style="position:absolute;left:-30px;top:2px;width:22px;height:22px;border-radius:50%;background:#f0fdf4;border:2px solid #22c55e;display:flex;align-items:center;justify-content:center;z-index:2;">
                                <i class="fas fa-location-dot" style="font-size:10px;color:#16a34a;"></i>
                            </div>

                            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:#64748b;margin-bottom:5px;">
                                Gudang Tujuan (Proyek) <span class="text-danger">*</span>
                            </div>

                            <select name="to_warehouse_id" id="to-warehouse" class="form-control" required style="font-size:13px;height:38px;border-radius:8px;font-weight:500;border-color:#cbd5e1;">
                                <option value="">— Pilih Gudang Proyek —</option>
                                @foreach($warehouses as $wh)
                                    @if(!$wh->is_central)
                                    <option value="{{ $wh->id }}" {{ old('to_warehouse_id') == $wh->id ? 'selected' : '' }}>
                                        {{ $wh->name }} (Proyek)
                                    </option>
                                    @endif
                                @endforeach
                                @foreach($warehouses as $wh)
                                    @if($wh->is_central && $wh->id !== ($originWarehouse?->id ?? ($warehouses->firstWhere('is_central', true)?->id)))
                                    <option value="{{ $wh->id }}" {{ old('to_warehouse_id') == $wh->id ? 'selected' : '' }}>
                                        {{ $wh->name }} (Pusat Lain)
                                    </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Status note --}}
                    <div style="background:#f8fafc;border-left:3px solid #3b82f6;border-radius:0 6px 6px 0;padding:8px 12px;margin-bottom:18px;font-size:11.5px;color:#475569;line-height:1.45;">
                        <i class="fas fa-info-circle text-primary" style="margin-right:4px;"></i>
                        Surat jalan berstatus <strong>Draft</strong> sebelum diverifikasi dan dikirim.
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit" id="btn-submit" class="btn btn-primary w-full" style="justify-content:center;padding:12px;font-size:14px;font-weight:600;border-radius:8px;box-shadow:0 2px 6px rgba(37,99,235,0.25);">
                        <i class="fas fa-paper-plane" style="margin-right:6px;"></i> Buat Surat Jalan
                    </button>
                </div>
            </div>
        </div>
    </form>

    {{-- Floating Action Bar for Mobile View --}}
    <div id="mobileDistSummaryBar" style="display:none;position:fixed;bottom:16px;left:16px;right:16px;z-index:99;background:#0f172a;color:#ffffff;border-radius:12px;padding:12px 16px;box-shadow:0 10px 25px -4px rgba(0,0,0,0.3);align-items:center;justify-content:space-between;gap:12px;">
        <div style="display:flex;align-items:center;gap:8px;">
            <i class="fas fa-boxes-stacked text-primary" style="font-size:15px;"></i>
            <span style="font-size:13px;font-weight:600;"><span id="mobileDistItemCount">0</span> Item Siap Kirim</span>
        </div>
        <button type="button" onclick="scrollToRoute()" class="btn btn-sm btn-primary" style="padding:6px 14px;font-size:12px;font-weight:600;border-radius:6px;box-shadow:none;">
            Pilih Rute & Kirim &darr;
        </button>
    </div>

    @push('scripts')
    <script>
        const whs = @json($whs);
        const materialsGrouped = @json($matsGroupedJson);
        const materialsData    = @json($matsDataJson);
        const toolsGrouped    = @json($toolsGroupedJson);
        const toolsData       = @json($toolsDataJson);
        const mrs             = @json($mrsJson);
        const tas             = @json($tasJson);
        const initialMrId     = @json($initialMrId);
        const initialLoanId   = @json($initialLoanId);

        const itemsBody            = document.getElementById('itemsBody');
        const emptyRow             = document.getElementById('empty-row');
        const fromSelect           = document.getElementById('from-warehouse');
        const toSelect             = document.getElementById('to-warehouse');
        const manualType           = document.getElementById('manual-type');
        const manualItemSelect     = document.getElementById('manual-item-select');
        const manualQty            = document.getElementById('manual-qty');
        const manualUnitLabel      = document.getElementById('manual-unit-label');
        const manualHint           = document.getElementById('manual-hint');
        const manualWarning        = document.getElementById('manual-warning');
        const distForm             = document.getElementById('distribution-form');
        const btnSubmit            = document.getElementById('btn-submit');
        const stockBadgeContainer  = document.getElementById('selected-stock-badge-container');
        const stockBadgeVal        = document.getElementById('selected-stock-val');
        const optCountLabel        = document.getElementById('opt-count-label');
        const sourceMrSelect       = document.getElementById('select-source-mr');
        const sourceTaSelect       = document.getElementById('select-source-ta');
        const sourceIndicator      = document.getElementById('source-active-indicator');
        const sourceActiveName     = document.getElementById('source-active-name');
        const hiddenMrId           = document.getElementById('hidden_material_request_id');
        const hiddenTaContainer    = document.getElementById('hidden_tool_assignment_ids_container');
        const notesInput           = document.getElementById('notesInput');

        let rowIndex = 0;

        function updateBadge() {
            const rows = itemsBody.querySelectorAll('tr[data-kind]');
            const count = document.getElementById('item-count-badge');
            const empty = rows.length === 0;
            emptyRow.style.display = empty ? '' : 'none';
            count.textContent = `${rows.length} item`;
            count.className = 'badge ' + (empty ? 'badge-gray' : 'badge-primary');

            // Mobile Floating Bar
            const mobileBar = document.getElementById('mobileDistSummaryBar');
            const mobileCount = document.getElementById('mobileDistItemCount');
            if (mobileBar && mobileCount) {
                mobileCount.textContent = rows.length;
                if (window.innerWidth <= 992 && !empty) {
                    mobileBar.style.display = 'flex';
                } else {
                    mobileBar.style.display = 'none';
                }
            }
        }

        function scrollToRoute() {
            const sidebar = document.getElementById('routeSidebarCard');
            if (sidebar) {
                sidebar.scrollIntoView({ behavior: 'smooth' });
                const toWh = document.getElementById('to-warehouse');
                if (toWh) setTimeout(() => toWh.focus(), 350);
            }
        }

        // ==================== AUTO-FILL DARI SUMBER PERMINTAAN ====================

        function updateSourceIndicator() {
            const hasMr = !!(hiddenMrId && hiddenMrId.value);
            const hasTa = !!(hiddenTaContainer && hiddenTaContainer.children.length > 0);
            const badgeMr = document.getElementById('badge-mr-connected');
            const badgeTa = document.getElementById('badge-ta-connected');

            if (badgeMr) badgeMr.style.display = hasMr ? 'inline-flex' : 'none';
            if (badgeTa) badgeTa.style.display = hasTa ? 'inline-flex' : 'none';

            if (sourceIndicator) {
                sourceIndicator.style.display = (hasMr || hasTa) ? 'inline-flex' : 'none';
            }
        }

        function clearMrSource() {
            if (sourceMrSelect) sourceMrSelect.value = '';
            if (hiddenMrId) hiddenMrId.value = '';
            itemsBody.querySelectorAll('tr[data-source="mr"]').forEach(r => r.remove());
            updateSourceIndicator();
            updateBadge();
            updateManualSelect();
        }

        function clearTaSource() {
            if (sourceTaSelect) sourceTaSelect.value = '';
            if (hiddenTaContainer) hiddenTaContainer.innerHTML = '';
            itemsBody.querySelectorAll('tr[data-source="ta"]').forEach(r => r.remove());
            updateSourceIndicator();
            updateBadge();
            updateManualSelect();
        }

        function clearSelectedSource() {
            clearMrSource();
            clearTaSource();
        }

        function onSourceMrSelected(mrId) {
            if (!mrId) {
                clearMrSource();
                return;
            }

            const mr = mrs.find(m => m.id === Number(mrId));
            if (!mr) return;

            // Set hidden MR ID tanpa mereset Alat
            hiddenMrId.value = mr.id;

            const mrActiveName = document.getElementById('mr-active-name');
            if (mrActiveName) mrActiveName.textContent = `#${mr.number}`;

            // Auto-fill Gudang jika belum diatur
            if (mr.from_warehouse_id) fromSelect.value = mr.from_warehouse_id;
            if (mr.to_warehouse_id) toSelect.value = mr.to_warehouse_id;

            // Auto-fill Catatan Pengiriman jika masih kosong atau tambahkan
            if (!notesInput.value) {
                notesInput.value = `Pengiriman barang berdasarkan Permintaan Material #${mr.number}`;
            } else if (!notesInput.value.includes(mr.number)) {
                notesInput.value += ` & MR #${mr.number}`;
            }

            // Hapus hanya baris lama yang berasal dari MR
            itemsBody.querySelectorAll('tr[data-source="mr"]').forEach(r => r.remove());

            // Masukkan seluruh item yang disetujui dari MR
            mr.items.forEach(it => {
                const tr = document.createElement('tr');
                tr.dataset.kind = 'material';
                tr.dataset.source = 'mr';
                tr.dataset.sourceId = mr.id;

                tr.innerHTML = `
                    <td><span class="badge" style="background:#f1f5f9;color:#334155;font-size:11px;font-weight:600;padding:3px 8px;border-radius:4px;">Material</span></td>
                    <td>
                        <input type="hidden" name="items[${rowIndex}][type]" value="material">
                        <input type="hidden" name="items[${rowIndex}][material_id]" value="${it.material_id}">
                        <input type="hidden" name="items[${rowIndex}][notes]" value="Permintaan #${mr.number}">
                        <div class="fw-600" style="color:#1e293b;font-size:13px;">${it.name}</div>
                        <div class="text-muted" style="font-size:11.5px;display:flex;align-items:center;gap:6px;margin-top:2px;">
                            ${it.code ? `<code style="font-size:11px;background:#f1f5f9;padding:1px 5px;border-radius:4px;color:#475569;">${it.code}</code> &bull; ` : ''}
                            <span class="badge" style="background:#eff6ff;color:#2563eb;font-size:10.5px;padding:2px 6px;border-radius:4px;font-weight:600;">#${mr.number}</span>
                            <span>Sisa: <strong>${it.remaining} ${it.unit}</strong></span>
                        </div>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <input type="number" name="items[${rowIndex}][quantity]" class="form-control qty-input"
                                value="${it.remaining}" min="0.01" max="${it.remaining}" step="0.01" required style="width:100px;text-align:center;font-weight:700;height:34px;border-radius:6px;font-size:13px;">
                            <span class="text-muted" style="font-size:12.5px;font-weight:500;">${it.unit}</span>
                        </div>
                    </td>
                    <td style="text-align:center;">
                        <button type="button" class="btn btn-sm btn-light border text-danger" onclick="this.closest('tr').remove(); updateBadge();" title="Hapus item" style="width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;">
                            <i class="fas fa-trash-can" style="font-size:11px;"></i>
                        </button>
                    </td>`;

                itemsBody.appendChild(tr);
                rowIndex++;
            });

            updateSourceIndicator();
            updateBadge();
            updateManualSelect();
        }

        function onSourceTaSelected(loanId) {
            if (!loanId) {
                clearTaSource();
                return;
            }

            const loan = tas.find(t => t.id === Number(loanId));
            if (!loan) return;

            const taActiveName = document.getElementById('ta-active-name');
            if (taActiveName) taActiveName.textContent = `#${loan.number}`;

            // Auto-fill Gudang Asal jika belum dipilih
            if (!fromSelect.value && loan.from_warehouse_id) {
                fromSelect.value = loan.from_warehouse_id;
            }

            // Auto-fill Catatan Pengiriman jika masih kosong atau tambahkan
            if (!notesInput.value) {
                notesInput.value = `Pengiriman alat kerja peminjaman #${loan.number} (${loan.borrower})`;
            } else if (!notesInput.value.includes(loan.number)) {
                notesInput.value += ` & Peminjaman Alat #${loan.number}`;
            }

            // Hapus hanya baris lama yang berasal dari TA (jangan hapus data-source="mr")
            itemsBody.querySelectorAll('tr[data-source="ta"]').forEach(r => r.remove());
            hiddenTaContainer.innerHTML = '';

            // Masukkan seluruh alat dari Peminjaman ini
            loan.items.forEach(it => {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'tool_assignment_ids[]';
                hiddenInput.value = it.id;
                hiddenTaContainer.appendChild(hiddenInput);

                const tr = document.createElement('tr');
                tr.dataset.kind = 'tool';
                tr.dataset.source = 'ta';
                tr.dataset.sourceId = loan.id;

                tr.innerHTML = `
                    <td><span class="badge" style="background:#f5f3ff;color:#6d28d9;font-size:11px;font-weight:600;padding:3px 8px;border-radius:4px;">Alat</span></td>
                    <td>
                        <input type="hidden" name="items[${rowIndex}][type]" value="tool">
                        <input type="hidden" name="items[${rowIndex}][tool_id]" value="${it.tool_id}">
                        <input type="hidden" name="items[${rowIndex}][tool_assignment_id]" value="${it.id}">
                        <input type="hidden" name="items[${rowIndex}][notes]" value="Peminjaman #${loan.number}">
                        <div class="fw-600" style="color:#1e293b;font-size:13px;">${it.tool_name}</div>
                        <div class="text-muted" style="font-size:11.5px;display:flex;align-items:center;gap:6px;margin-top:2px;">
                            ${it.tool_code ? `<code style="font-size:11px;background:#f1f5f9;padding:1px 5px;border-radius:4px;color:#475569;">${it.tool_code}</code> &bull; ` : ''}
                            <span class="badge" style="background:#f5f3ff;color:#7c3aed;font-size:10.5px;padding:2px 6px;border-radius:4px;font-weight:600;">#${loan.number}</span>
                            <span>Peminjam: <strong>${loan.borrower}</strong></span>
                        </div>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <input type="number" name="items[${rowIndex}][quantity]" class="form-control qty-input"
                                value="${it.quantity}" min="1" max="${it.quantity}" step="1" required style="width:100px;text-align:center;font-weight:700;height:34px;border-radius:6px;font-size:13px;">
                            <span class="text-muted" style="font-size:12.5px;font-weight:500;">unit</span>
                        </div>
                    </td>
                    <td style="text-align:center;">
                        <button type="button" class="btn btn-sm btn-light border text-danger" onclick="this.closest('tr').remove(); updateBadge();" title="Hapus alat" style="width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;">
                            <i class="fas fa-trash-can" style="font-size:11px;"></i>
                        </button>
                    </td>`;

                itemsBody.appendChild(tr);
                rowIndex++;
            });

            updateSourceIndicator();
            updateBadge();
            updateManualSelect();
        }

        // ==================== MANUAL ITEM SELECTOR (SEPERTI DI PEMAKAIAN MATERIAL) ====================

        function updateManualSelect() {
            const type = manualType.value;
            const selectContainer = document.getElementById('manual-select-container');
            const customContainer = document.getElementById('manual-custom-container');
            const fromWhId = fromSelect.value ? Number(fromSelect.value) : null;

            stockBadgeContainer.style.display = 'none';
            manualWarning.style.display = 'none';

            if (type === 'custom') {
                if (selectContainer) selectContainer.style.display = 'none';
                if (customContainer) customContainer.style.display = 'block';
                manualQty.step = "0.01";
                manualQty.min = "0.01";
                const customUnitInput = document.getElementById('manual-custom-unit');
                manualUnitLabel.textContent = (customUnitInput && customUnitInput.value.trim()) ? customUnitInput.value.trim() : 'pcs';
                manualHint.textContent = "Item custom bebas tidak memotong master stok.";
                optCountLabel.textContent = "";
                return;
            }

            if (selectContainer) selectContainer.style.display = 'block';
            if (customContainer) customContainer.style.display = 'none';

            manualItemSelect.innerHTML = '';

            if (type === 'material') {
                manualQty.step = "0.01";
                manualQty.min = "0.01";
                manualHint.textContent = "Kuantitas stok mencerminkan stok fisik di Gudang Pusat.";

                const defaultOpt = document.createElement('option');
                defaultOpt.value = '';
                defaultOpt.textContent = '— Pilih Material —';
                manualItemSelect.appendChild(defaultOpt);

                let totalCount = 0;
                if (materialsGrouped && typeof materialsGrouped === 'object' && !Array.isArray(materialsGrouped)) {
                    Object.keys(materialsGrouped).sort().forEach(cat => {
                        const group = document.createElement('optgroup');
                        group.label = cat;
                        materialsGrouped[cat].forEach(m => {
                            const stock = fromWhId && m.stocks && m.stocks[fromWhId] !== undefined ? m.stocks[fromWhId] : (fromWhId ? 0 : m.total_stock);
                            const opt = document.createElement('option');
                            opt.value = m.id;
                            const codeStr = m.code ? ` (${m.code})` : '';
                            opt.textContent = `${m.name}${codeStr} — Stok: ${stock} ${m.unit}`;
                            opt.dataset.unit = m.unit;
                            opt.dataset.name = m.name;
                            opt.dataset.code = m.code || '';
                            opt.dataset.stock = stock;
                            group.appendChild(opt);
                            totalCount++;
                        });
                        manualItemSelect.appendChild(group);
                    });
                } else {
                    materialsData.forEach(m => {
                        const stock = fromWhId && m.stocks && m.stocks[fromWhId] !== undefined ? m.stocks[fromWhId] : (fromWhId ? 0 : m.total_stock);
                        const opt = document.createElement('option');
                        opt.value = m.id;
                        const codeStr = m.code ? ` (${m.code})` : '';
                        opt.textContent = `${m.name}${codeStr} — Stok: ${stock} ${m.unit}`;
                        opt.dataset.unit = m.unit;
                        opt.dataset.name = m.name;
                        opt.dataset.code = m.code || '';
                        opt.dataset.stock = stock;
                        manualItemSelect.appendChild(opt);
                        totalCount++;
                    });
                }
                optCountLabel.textContent = `${totalCount} material`;
            } else {
                // Tool / Alat Kerja
                manualQty.step = "1";
                manualQty.min = "1";
                manualHint.textContent = "Kuantitas alat mencerminkan stok siap pakai di Gudang Pusat.";

                const defaultOpt = document.createElement('option');
                defaultOpt.value = '';
                defaultOpt.textContent = '— Pilih Alat Kerja —';
                manualItemSelect.appendChild(defaultOpt);

                let totalCount = 0;
                if (toolsGrouped && typeof toolsGrouped === 'object' && !Array.isArray(toolsGrouped)) {
                    Object.keys(toolsGrouped).sort().forEach(cat => {
                        const group = document.createElement('optgroup');
                        group.label = cat;
                        toolsGrouped[cat].forEach(t => {
                            const stock = fromWhId && t.stocks && t.stocks[fromWhId] !== undefined ? t.stocks[fromWhId] : (fromWhId ? 0 : t.total_stock);
                            const opt = document.createElement('option');
                            opt.value = t.id;
                            const codeStr = t.code ? ` (${t.code})` : '';
                            opt.textContent = `${t.name}${codeStr} — Stok: ${stock} unit`;
                            opt.dataset.unit = 'unit';
                            opt.dataset.name = t.name;
                            opt.dataset.code = t.code || '';
                            opt.dataset.stock = stock;
                            group.appendChild(opt);
                            totalCount++;
                        });
                        manualItemSelect.appendChild(group);
                    });
                } else {
                    toolsData.forEach(t => {
                        const stock = fromWhId && t.stocks && t.stocks[fromWhId] !== undefined ? t.stocks[fromWhId] : (fromWhId ? 0 : t.total_stock);
                        const opt = document.createElement('option');
                        opt.value = t.id;
                        const codeStr = t.code ? ` (${t.code})` : '';
                        opt.textContent = `${t.name}${codeStr} — Stok: ${stock} unit`;
                        opt.dataset.unit = 'unit';
                        opt.dataset.name = t.name;
                        opt.dataset.code = t.code || '';
                        opt.dataset.stock = stock;
                        manualItemSelect.appendChild(opt);
                        totalCount++;
                    });
                }
                optCountLabel.textContent = `${totalCount} alat`;
            }

            onItemSelectionChanged();
        }

        function onItemSelectionChanged() {
            const select = manualItemSelect;
            const opt = select.selectedOptions[0];

            if (!opt || !select.value) {
                stockBadgeContainer.style.display = 'none';
                manualUnitLabel.textContent = manualType.value === 'tool' ? 'unit' : 'pcs';
                manualWarning.style.display = 'none';
                return;
            }

            const stock = parseFloat(opt.dataset.stock || 0);
            const unit = opt.dataset.unit || 'unit';

            manualUnitLabel.textContent = unit;
            stockBadgeContainer.style.display = 'block';
            stockBadgeVal.textContent = `${stock} ${unit}`;

            const badge = document.getElementById('selected-stock-badge');
            if (stock > 0) {
                badge.className = 'badge badge-primary';
            } else {
                badge.className = 'badge badge-danger';
            }

            validateManualQty();
        }

        function onCustomUnitChanged(val) {
            manualUnitLabel.textContent = val.trim() || 'pcs';
        }

        function validateManualQty() {
            const type = manualType.value;
            if (type === 'custom') {
                manualWarning.style.display = 'none';
                return;
            }

            const select = manualItemSelect;
            const opt = select.selectedOptions[0];
            if (!opt || !select.value) {
                manualWarning.style.display = 'none';
                return;
            }

            const stock = parseFloat(opt.dataset.stock || 0);
            const qty = parseFloat(manualQty.value || 0);
            const unit = opt.dataset.unit || 'unit';

            if (stock > 0 && qty > stock) {
                manualWarning.style.display = 'block';
                manualWarning.innerHTML = `<i class="fas fa-triangle-exclamation"></i> Perhatian: Kuantitas kirim (${qty} ${unit}) melebihi stok yang tersedia di gudang asal (${stock} ${unit}).`;
            } else if (stock <= 0) {
                manualWarning.style.display = 'block';
                manualWarning.innerHTML = `<i class="fas fa-triangle-exclamation"></i> Perhatian: Stok item ini di gudang asal adalah 0 ${unit}.`;
            } else {
                manualWarning.style.display = 'none';
            }
        }

        function addManualItem() {
            const type = manualType.value;
            const qty = parseFloat(manualQty.value);

            if (isNaN(qty) || qty <= 0) {
                alert("Masukkan jumlah (qty) yang valid.");
                manualQty.focus();
                return;
            }

            if (type === 'custom') {
                const customNameInput = document.getElementById('manual-custom-name');
                const customUnitInput = document.getElementById('manual-custom-unit');
                const customName = customNameInput ? customNameInput.value.trim() : '';
                const customUnit = (customUnitInput && customUnitInput.value.trim()) ? customUnitInput.value.trim() : 'pcs';

                if (!customName) {
                    alert("Nama barang/alat custom wajib diisi.");
                    if (customNameInput) customNameInput.focus();
                    return;
                }

                const tr = document.createElement('tr');
                tr.dataset.kind = 'custom';
                tr.dataset.source = 'manual';
                tr.innerHTML = `
                    <td><span class="badge" style="background:#ecfdf5;color:#047857;font-size:11px;font-weight:600;padding:3px 8px;border-radius:4px;">Custom</span></td>
                    <td>
                        <input type="hidden" name="items[${rowIndex}][type]" value="custom">
                        <input type="hidden" name="items[${rowIndex}][custom_item_name]" value="${customName}">
                        <input type="hidden" name="items[${rowIndex}][custom_item_unit]" value="${customUnit}">
                        <div class="fw-600" style="color:#1e293b;font-size:13px;">${customName}</div>
                        <div class="text-muted" style="font-size:11.5px;">Item Custom &bull; Non-Master Stok</div>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <input type="number" name="items[${rowIndex}][quantity]" class="form-control qty-input"
                                value="${qty}" min="0.01" step="0.01" required style="width:100px;text-align:center;font-weight:700;height:34px;border-radius:6px;font-size:13px;">
                            <span class="text-muted" style="font-size:12.5px;font-weight:500;">${customUnit}</span>
                        </div>
                    </td>
                    <td style="text-align:center;">
                        <button type="button" class="btn btn-sm btn-light border text-danger" onclick="this.closest('tr').remove(); updateBadge();" title="Hapus item" style="width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;">
                            <i class="fas fa-trash-can" style="font-size:11px;"></i>
                        </button>
                    </td>`;

                itemsBody.appendChild(tr);
                rowIndex++;
                updateBadge();
                if (customNameInput) customNameInput.value = '';
                return;
            }

            const select = manualItemSelect;
            const opt = select.selectedOptions[0];

            if (!select.value) {
                alert("Pilih barang atau alat terlebih dahulu.");
                select.focus();
                return;
            }

            // Cek apakah item sudah pernah ditambahkan ke tabel
            const inputField = type === 'material' ? 'material_id' : 'tool_id';
            const existingInput = itemsBody.querySelector(`input[name$="[${inputField}]"][value="${select.value}"]`);
            if (existingInput) {
                const row = existingInput.closest('tr');
                const qtyInput = row.querySelector('.qty-input');
                const newQty = (parseFloat(qtyInput.value) || 0) + qty;
                qtyInput.value = type === 'material' ? Math.round(newQty * 100) / 100 : Math.round(newQty);
                row.style.transition = 'background-color 0.3s';
                row.style.backgroundColor = '#ecfdf5';
                setTimeout(() => row.style.backgroundColor = '', 800);
                select.value = '';
                manualQty.value = '1';
                onItemSelectionChanged();
                return;
            }

            const tr = document.createElement('tr');
            tr.dataset.kind = type;
            tr.dataset.source = 'manual';

            if (type === 'material') {
                tr.innerHTML = `
                    <td><span class="badge" style="background:#f1f5f9;color:#334155;font-size:11px;font-weight:600;padding:3px 8px;border-radius:4px;">Material</span></td>
                    <td>
                        <input type="hidden" name="items[${rowIndex}][type]" value="material">
                        <input type="hidden" name="items[${rowIndex}][material_id]" value="${select.value}">
                        <div class="fw-600" style="color:#1e293b;font-size:13px;">${opt.dataset.name}</div>
                        <div class="text-muted" style="font-size:11.5px;display:flex;align-items:center;gap:6px;margin-top:2px;">
                            ${opt.dataset.code ? `<code style="font-size:11px;background:#f1f5f9;padding:1px 5px;border-radius:4px;color:#475569;">${opt.dataset.code}</code> &bull; ` : ''}
                            <span>Stok Pusat: <strong>${opt.dataset.stock} ${opt.dataset.unit}</strong></span>
                        </div>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <input type="number" name="items[${rowIndex}][quantity]" class="form-control qty-input"
                                value="${qty}" min="0.01" step="0.01" required style="width:100px;text-align:center;font-weight:700;height:34px;border-radius:6px;font-size:13px;">
                            <span class="text-muted" style="font-size:12.5px;font-weight:500;">${opt.dataset.unit}</span>
                        </div>
                    </td>
                    <td style="text-align:center;">
                        <button type="button" class="btn btn-sm btn-light border text-danger" onclick="this.closest('tr').remove(); updateBadge();" title="Hapus item" style="width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;">
                            <i class="fas fa-trash-can" style="font-size:11px;"></i>
                        </button>
                    </td>`;
            } else {
                tr.innerHTML = `
                    <td><span class="badge" style="background:#f5f3ff;color:#6d28d9;font-size:11px;font-weight:600;padding:3px 8px;border-radius:4px;">Alat</span></td>
                    <td>
                        <input type="hidden" name="items[${rowIndex}][type]" value="tool">
                        <input type="hidden" name="items[${rowIndex}][tool_id]" value="${select.value}">
                        <div class="fw-600" style="color:#1e293b;font-size:13px;">${opt.dataset.name}</div>
                        <div class="text-muted" style="font-size:11.5px;display:flex;align-items:center;gap:6px;margin-top:2px;">
                            ${opt.dataset.code ? `<code style="font-size:11px;background:#f1f5f9;padding:1px 5px;border-radius:4px;color:#475569;">${opt.dataset.code}</code> &bull; ` : ''}
                            <span>Stok Pusat: <strong>${opt.dataset.stock} unit</strong></span>
                        </div>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <input type="number" name="items[${rowIndex}][quantity]" class="form-control qty-input"
                                value="${qty}" min="1" max="${opt.dataset.stock > 0 ? opt.dataset.stock : ''}" step="1" required style="width:100px;text-align:center;font-weight:700;height:34px;border-radius:6px;font-size:13px;">
                            <span class="text-muted" style="font-size:12.5px;font-weight:500;">unit</span>
                        </div>
                    </td>
                    <td style="text-align:center;">
                        <button type="button" class="btn btn-sm btn-light border text-danger" onclick="this.closest('tr').remove(); updateBadge();" title="Hapus alat" style="width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;">
                            <i class="fas fa-trash-can" style="font-size:11px;"></i>
                        </button>
                    </td>`;
            }

            itemsBody.appendChild(tr);
            rowIndex++;
            updateBadge();

            // reset inputs
            select.value = '';
            manualQty.value = '1';
            onItemSelectionChanged();
        }

        // Listener: Ketika Gudang Asal berubah, perbarui stok di dalam dropdown list
        fromSelect.addEventListener('change', () => {
            const currentItemVal = manualItemSelect.value;
            updateManualSelect();
            if (currentItemVal) {
                manualItemSelect.value = currentItemVal;
                onItemSelectionChanged();
            }
        });

        // Form Submit handler
        distForm.addEventListener('submit', function(e) {
            const rows = itemsBody.querySelectorAll('tr[data-kind]');
            if (rows.length === 0) {
                e.preventDefault();
                alert("Harap masukkan minimal 1 barang atau alat ke dalam Surat Jalan.");
                return false;
            }

            if (!fromSelect.value) {
                e.preventDefault();
                alert("Pilih Gudang Asal (Pengirim).");
                fromSelect.focus();
                return false;
            }

            if (!toSelect.value) {
                e.preventDefault();
                alert("Pilih Gudang Tujuan (Penerima).");
                toSelect.focus();
                return false;
            }

            if (fromSelect.value === toSelect.value) {
                e.preventDefault();
                alert("Gudang Asal dan Gudang Tujuan tidak boleh sama.");
                toSelect.focus();
                return false;
            }

            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan Surat Jalan...';
        });

        // Initialize on load
        updateManualSelect();
        updateBadge();

        window.addEventListener('resize', function() {
            updateBadge();
        });

        // Auto trigger initial selection if coming from URL parameter
        if (initialMrId) {
            onSourceMrSelected(initialMrId);
        }
        if (initialLoanId) {
            onSourceTaSelected(initialLoanId);
        }
    </script>
    @endpush
</x-app-layout>