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
            grid-template-columns: 1fr 110px 120px auto;
            gap: 12px;
            align-items: end;
        }

        .suggestion-item:hover, .suggestion-item.active {
            background-color: #f1f5f9 !important;
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

                    {{-- Form Input Item Manual / Tambahan dengan Live Autocomplete Inventori --}}
                    <div id="item-adder-box" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:14px 18px;position:relative;">
                        <div style="font-weight:600;font-size:12.5px;margin-bottom:10px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;color:#475569;">
                            <div style="display:flex;align-items:center;gap:6px;">
                                <i class="fas fa-pen-to-square text-primary" style="font-size:12px;"></i>
                                <span style="color:#1e293b;font-weight:700;">Input Barang / Alat</span>
                                <span class="text-muted" style="font-weight:normal;font-size:11.5px;">(Ketik manual atau pilih rekomendasi dari inventori)</span>
                            </div>
                            <div id="selected-stock-badge-container" style="display:none;">
                                <span id="selected-stock-badge" class="badge" style="background:#e0f2fe;color:#0369a1;font-size:11px;padding:3px 8px;border-radius:4px;font-weight:600;">
                                    Stok Gudang Asal: <strong id="selected-stock-val">0</strong>
                                </span>
                            </div>
                        </div>

                        <div class="item-adder-grid">
                            {{-- Input Nama Barang / Alat dengan Suggestions Menu --}}
                            <div style="position:relative;">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                                    <label class="form-label" style="font-size:12px;margin:0;font-weight:600;color:#334155;">
                                        Nama Barang / Alat <span class="text-danger">*</span>
                                    </label>
                                    <span id="input-source-indicator" class="badge" style="display:none;font-size:10px;padding:1px 6px;"></span>
                                </div>
                                <div style="position:relative;">
                                    <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:12.5px;pointer-events:none;"></i>
                                    <input type="text" id="manual-custom-name" class="form-control" 
                                        placeholder="Ketik nama barang / alat (cth: Semen, Genset, Terpal, Kabel)..." 
                                        autocomplete="off"
                                        style="height:38px;border-radius:8px;font-size:12.5px;padding-left:34px;padding-right:28px;"
                                        oninput="onItemSearchInput(this.value)"
                                        onfocus="onItemSearchFocus()"
                                        onkeydown="onItemSearchKeydown(event)">
                                    <button type="button" id="clear-item-btn" onclick="clearItemSearch()" 
                                        style="display:none;position:absolute;right:8px;top:50%;transform:translateY(-50%);border:none;background:transparent;color:#94a3b8;font-size:13px;cursor:pointer;padding:2px 4px;" title="Hapus teks">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>

                                {{-- Hidden Fields for tracking item metadata --}}
                                <input type="hidden" id="selected-item-type" value="custom">
                                <input type="hidden" id="selected-item-id" value="">
                                <input type="hidden" id="selected-item-code" value="">
                                <input type="hidden" id="selected-item-stock" value="0">

                                {{-- Autocomplete Dropdown List --}}
                                <div id="item-suggestions-box" 
                                    style="display:none;position:absolute;top:calc(100% + 4px);left:0;right:0;z-index:9999;background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;box-shadow:0 12px 28px -4px rgba(0,0,0,0.18);max-height:280px;overflow-y:auto;">
                                </div>
                            </div>

                            {{-- Satuan --}}
                            <div>
                                <label class="form-label" style="font-size:12px;margin-bottom:4px;font-weight:600;color:#334155;">Satuan</label>
                                <input type="text" id="manual-custom-unit" class="form-control" placeholder="pcs" value="pcs" 
                                    style="height:38px;border-radius:8px;font-size:12.5px;text-align:center;"
                                    onkeydown="if(event.key==='Enter'){event.preventDefault();addManualItem();}">
                            </div>

                            {{-- Jumlah Qty --}}
                            <div>
                                <label class="form-label" style="font-size:12px;margin-bottom:4px;font-weight:600;color:#334155;">Jumlah (Qty)</label>
                                <input type="number" id="manual-qty" class="form-control" placeholder="Qty" min="0.01" step="0.01" value="1" 
                                    style="height:38px;text-align:center;font-weight:700;border-radius:8px;font-size:13px;" 
                                    oninput="validateManualQty()"
                                    onkeydown="if(event.key==='Enter'){event.preventDefault();addManualItem();}">
                            </div>

                            {{-- Tombol Tambah --}}
                            <div>
                                <button type="button" class="btn btn-primary" onclick="addManualItem()" 
                                    style="height:38px;white-space:nowrap;display:inline-flex;align-items:center;gap:6px;font-weight:600;border-radius:8px;padding:0 18px;">
                                    <i class="fas fa-plus" style="font-size:12px;"></i> <span>Tambah</span>
                                </button>
                            </div>
                        </div>

                        {{-- Status Info Strip (muncul saat item master inventori terpilih) --}}
                        <div id="master-item-selected-strip" style="display:none;margin-top:8px;padding:6px 12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;font-size:11.5px;align-items:center;justify-content:space-between;gap:8px;">
                            <div style="display:flex;align-items:center;gap:6px;color:#166534;">
                                <i class="fas fa-circle-check text-success"></i>
                                <span>Terhubung Master: <strong id="selected-master-title"></strong></span>
                                <span id="selected-master-stock-pill" class="badge" style="background:#dcfce7;color:#15803d;font-size:10.5px;margin-left:4px;"></span>
                            </div>
                            <button type="button" class="btn btn-link btn-xs p-0 text-danger" onclick="unlinkMasterItem(false)" style="text-decoration:none;font-size:11px;font-weight:600;" title="Jadikan item bebas tanpa relasi master inventori">
                                <i class="fas fa-unlink me-1"></i> Lepas Relasi (Jadikan Manual)
                            </button>
                        </div>

                        <div id="manual-warning" class="text-danger" style="display:none;font-size:11.5px;font-weight:600;margin-top:8px;"></div>
                        <div id="manual-hint" class="text-muted" style="font-size:11.5px;margin-top:6px;color:#94a3b8;">
                            <i class="fas fa-lightbulb text-warning me-1"></i> <strong>Tips:</strong> Ketik nama barang untuk memunculkan pilihan dari inventori secara otomatis, atau ketik nama bebas untuk barang manual non-inventori.
                        </div>
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
        const manualQty            = document.getElementById('manual-qty');
        const manualHint           = document.getElementById('manual-hint');
        const manualWarning        = document.getElementById('manual-warning');
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
                const isCustom = it.is_custom || !it.material_id;
                tr.dataset.kind = isCustom ? 'custom' : 'material';
                tr.dataset.source = 'mr';
                tr.dataset.sourceId = mr.id;

                const badgeHtml = isCustom
                    ? `<span class="badge" style="background:#fef3c7;color:#92400e;font-size:11px;font-weight:600;padding:3px 8px;border-radius:4px;"><i class="fas fa-pen-nib me-1"></i>Manual</span>`
                    : `<span class="badge" style="background:#f1f5f9;color:#334155;font-size:11px;font-weight:600;padding:3px 8px;border-radius:4px;"><i class="fas fa-cube me-1"></i>Material</span>`;

                const hiddenFields = isCustom
                    ? `
                        <input type="hidden" name="items[${rowIndex}][type]" value="custom">
                        <input type="hidden" name="items[${rowIndex}][custom_item_name]" value="${escapeHtml(it.custom_item_name || it.name)}">
                        <input type="hidden" name="items[${rowIndex}][custom_item_unit]" value="${escapeHtml(it.custom_item_unit || it.unit || 'unit')}">
                        <input type="hidden" name="items[${rowIndex}][notes]" value="Permintaan #${escapeHtml(mr.number)}">
                      `
                    : `
                        <input type="hidden" name="items[${rowIndex}][type]" value="material">
                        <input type="hidden" name="items[${rowIndex}][material_id]" value="${it.material_id}">
                        <input type="hidden" name="items[${rowIndex}][notes]" value="Permintaan #${escapeHtml(mr.number)}">
                      `;

                const subtext = isCustom
                    ? `<span class="badge" style="background:#eff6ff;color:#2563eb;font-size:10.5px;padding:2px 6px;border-radius:4px;font-weight:600;">#${escapeHtml(mr.number)}</span>
                       <span class="badge" style="background:#fef3c7;color:#92400e;font-size:10px;font-weight:600;padding:1px 5px;border-radius:3px;">Input Manual</span>
                       <span>Sisa: <strong>${it.remaining} ${escapeHtml(it.unit)}</strong></span>`
                    : `${it.code ? `<code style="font-size:11px;background:#f1f5f9;padding:1px 5px;border-radius:4px;color:#475569;">${escapeHtml(it.code)}</code> &bull; ` : ''}
                       <span class="badge" style="background:#eff6ff;color:#2563eb;font-size:10.5px;padding:2px 6px;border-radius:4px;font-weight:600;">#${escapeHtml(mr.number)}</span>
                       <span>Sisa: <strong>${it.remaining} ${escapeHtml(it.unit)}</strong></span>`;

                tr.innerHTML = `
                    <td>${badgeHtml}</td>
                    <td>
                        ${hiddenFields}
                        <div class="fw-600" style="color:#1e293b;font-size:13px;">${escapeHtml(it.name)}</div>
                        <div class="text-muted" style="font-size:11.5px;display:flex;align-items:center;gap:6px;margin-top:2px;">
                            ${subtext}
                        </div>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <input type="number" name="items[${rowIndex}][quantity]" class="form-control qty-input"
                                value="${it.remaining}" min="0.01" max="${it.remaining}" step="0.01" required style="width:100px;text-align:center;font-weight:700;height:34px;border-radius:6px;font-size:13px;">
                            <span class="text-muted" style="font-size:12.5px;font-weight:500;">${escapeHtml(it.unit)}</span>
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

        // ==================== AUTOCOMPLETE & MANUAL ITEM SELECTOR ====================

        let activeSuggestionIndex = -1;
        let currentSuggestions = [];

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/[&<>"']/g, function(m) {
                return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[m];
            });
        }

        function highlightMatch(text, query) {
            if (!query) return escapeHtml(text);
            const escapedText = escapeHtml(text);
            const escapedQuery = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            const regex = new RegExp(`(${escapedQuery})`, 'gi');
            return escapedText.replace(regex, '<mark style="background:#fef08a;padding:0 2px;border-radius:2px;color:#0f172a;">$1</mark>');
        }

        function onItemSearchInput(val) {
            const clearBtn = document.getElementById('clear-item-btn');
            if (clearBtn) clearBtn.style.display = val.trim() ? 'block' : 'none';

            // If user previously selected a master item but now edited the text, unlink
            const selectedType = document.getElementById('selected-item-type').value;
            const selectedTitle = document.getElementById('selected-master-title')?.textContent || '';
            if (selectedType !== 'custom' && val.trim() !== selectedTitle.trim()) {
                unlinkMasterItem(false);
            }

            renderSuggestions(val);
        }

        function onItemSearchFocus() {
            const val = document.getElementById('manual-custom-name').value;
            renderSuggestions(val);
        }

        function clearItemSearch() {
            const input = document.getElementById('manual-custom-name');
            input.value = '';
            unlinkMasterItem(true);
            input.focus();
            renderSuggestions('');
        }

        function unlinkMasterItem(clearName = false) {
            document.getElementById('selected-item-type').value = 'custom';
            document.getElementById('selected-item-id').value = '';
            document.getElementById('selected-item-code').value = '';
            document.getElementById('selected-item-stock').value = '0';
            const strip = document.getElementById('master-item-selected-strip');
            if (strip) strip.style.display = 'none';
            if (manualWarning) manualWarning.style.display = 'none';
            if (clearName) {
                const input = document.getElementById('manual-custom-name');
                if (input) input.value = '';
                const clearBtn = document.getElementById('clear-item-btn');
                if (clearBtn) clearBtn.style.display = 'none';
            }
        }

        function selectSuggestion(type, id, name, unit, code, stock) {
            const input = document.getElementById('manual-custom-name');
            const unitInput = document.getElementById('manual-custom-unit');
            const typeInput = document.getElementById('selected-item-type');
            const idInput = document.getElementById('selected-item-id');
            const codeInput = document.getElementById('selected-item-code');
            const stockInput = document.getElementById('selected-item-stock');
            const strip = document.getElementById('master-item-selected-strip');
            const titleEl = document.getElementById('selected-master-title');
            const stockPill = document.getElementById('selected-master-stock-pill');
            const clearBtn = document.getElementById('clear-item-btn');

            input.value = name;
            unitInput.value = unit || (type === 'tool' ? 'unit' : 'pcs');
            typeInput.value = type;
            idInput.value = id;
            codeInput.value = code || '';
            stockInput.value = stock;

            if (titleEl) titleEl.textContent = name + (code ? ` (${code})` : '');
            if (stockPill) stockPill.textContent = `Stok: ${stock} ${unitInput.value}`;
            if (strip) strip.style.display = 'flex';
            if (clearBtn) clearBtn.style.display = 'block';

            hideSuggestions();
            validateManualQty();

            // Focus on Qty input for fast workflows
            setTimeout(() => {
                manualQty.focus();
                manualQty.select();
            }, 100);
        }

        function selectAsManualCustom(customName) {
            const input = document.getElementById('manual-custom-name');
            const unitInput = document.getElementById('manual-custom-unit');
            input.value = customName.trim();
            unlinkMasterItem(false);
            hideSuggestions();
            setTimeout(() => {
                unitInput.focus();
                unitInput.select();
            }, 100);
        }

        function getAllInventoryItems() {
            const fromWhId = fromSelect.value ? Number(fromSelect.value) : null;
            const items = [];

            materialsData.forEach(m => {
                const stock = fromWhId && m.stocks && m.stocks[fromWhId] !== undefined ? m.stocks[fromWhId] : (fromWhId ? 0 : m.total_stock);
                items.push({
                    type: 'material',
                    id: m.id,
                    name: m.name,
                    unit: m.unit || 'pcs',
                    code: m.code || '',
                    category: m.category || 'Material',
                    stock: stock
                });
            });

            toolsData.forEach(t => {
                const stock = fromWhId && t.stocks && t.stocks[fromWhId] !== undefined ? t.stocks[fromWhId] : (fromWhId ? 0 : t.total_stock);
                items.push({
                    type: 'tool',
                    id: t.id,
                    name: t.name,
                    unit: 'unit',
                    code: t.code || '',
                    category: t.category || 'Alat Kerja',
                    stock: stock
                });
            });

            return items;
        }

        function renderSuggestions(query) {
            const box = document.getElementById('item-suggestions-box');
            if (!box) return;

            const q = (query || '').trim().toLowerCase();
            currentSuggestions = [];
            activeSuggestionIndex = -1;

            const allItems = getAllInventoryItems();

            // Filter HANYA berdasarkan NAMA barang/alat
            let matchedItems = allItems.filter(item => {
                if (!q) return true;
                return item.name.toLowerCase().includes(q);
            });

            // Urutkan murni berdasarkan NAMA:
            // 1. Yang awalan namanya cocok lebih dulu (startsWith)
            // 2. Diurutkan alfabetis A-Z berdasarkan nama
            matchedItems.sort((a, b) => {
                if (q) {
                    const aStarts = a.name.toLowerCase().startsWith(q);
                    const bStarts = b.name.toLowerCase().startsWith(q);
                    if (aStarts && !bStarts) return -1;
                    if (!aStarts && bStarts) return 1;
                }
                return a.name.localeCompare(b.name, 'id', { sensitivity: 'base' });
            });

            // Ambil maksimal 20 item teratas
            matchedItems = matchedItems.slice(0, q ? 20 : 10);

            let html = '';
            let itemIndexCounter = 0;

            if (matchedItems.length > 0) {
                html += `<div style="padding:6px 12px;background:#f8fafc;border-bottom:1px solid #e2e8f0;font-size:11px;font-weight:700;color:#64748b;display:flex;justify-content:space-between;align-items:center;">
                    <span><i class="fas fa-boxes-stacked me-1 text-primary"></i> Data Inventori Berdasarkan Nama (${matchedItems.length} Ditemukan)</span>
                    <span style="font-size:10px;color:#94a3b8;font-weight:normal;">Urut Nama A-Z</span>
                </div>`;

                matchedItems.forEach(item => {
                    const safeName = item.name.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                    const safeUnit = (item.unit || 'pcs').replace(/'/g, "\\'");
                    const safeCode = (item.code || '').replace(/'/g, "\\'");
                    const isMaterial = item.type === 'material';

                    currentSuggestions.push({
                        type: item.type,
                        id: item.id,
                        name: item.name,
                        unit: item.unit,
                        code: item.code,
                        stock: item.stock
                    });

                    html += `<div class="suggestion-item" data-index="${itemIndexCounter}" onclick="selectSuggestion('${item.type}', ${item.id}, '${safeName}', '${safeUnit}', '${safeCode}', ${item.stock})" style="padding:8px 12px;cursor:pointer;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;transition:background .15s;">
                        <div style="min-width:0;padding-right:10px;">
                            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                <span class="badge" style="background:${isMaterial ? '#eff6ff' : '#f5f3ff'};color:${isMaterial ? '#2563eb' : '#7c3aed'};border:1px solid ${isMaterial ? '#bfdbfe' : '#ddd6fe'};font-size:10px;font-weight:700;padding:2px 6px;border-radius:4px;">
                                    ${isMaterial ? 'Material' : 'Alat'}
                                </span>
                                <span style="font-weight:600;font-size:13px;color:#0f172a;">
                                    ${highlightMatch(item.name, q)}
                                </span>
                            </div>
                            <div class="text-muted" style="font-size:11px;display:flex;align-items:center;gap:6px;margin-top:2px;">
                                ${item.code ? `<code style="font-size:10px;background:#f1f5f9;padding:1px 4px;border-radius:3px;color:#475569;">${item.code}</code> &bull; ` : ''}
                                <span>Kategori: ${escapeHtml(item.category || '-')}</span>
                            </div>
                        </div>
                        <div style="text-align:right;flex-shrink:0;">
                            <span class="badge" style="background:${item.stock > 0 ? '#ecfdf5' : '#fef2f2'};color:${item.stock > 0 ? '#047857' : '#b91c1c'};border:1px solid ${item.stock > 0 ? '#a7f3d0' : '#fecaca'};font-size:11px;font-weight:600;padding:2px 7px;">
                                Stok: ${item.stock} ${escapeHtml(item.unit || '')}
                            </span>
                        </div>
                    </div>`;
                    itemIndexCounter++;
                });
            }

            // Opsi untuk jadikan input manual bebas
            if (q) {
                const safeQ = q.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                html += `<div class="suggestion-item suggestion-custom-opt" data-index="${itemIndexCounter}" onclick="selectAsManualCustom('${safeQ}')" style="padding:10px 14px;background:#fffbeb;border-top:1.5px dashed #fde68a;cursor:pointer;display:flex;align-items:center;justify-content:space-between;color:#92400e;transition:background .15s;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-pen-to-square text-warning" style="font-size:13px;"></i>
                        <span style="font-size:12px;">Gunakan nama "<strong>${escapeHtml(query.trim())}</strong>" sebagai Barang Manual</span>
                    </div>
                    <span class="badge" style="background:#fef3c7;color:#92400e;font-size:10px;padding:2px 6px;">Non-Master</span>
                </div>`;
                currentSuggestions.push({
                    type: 'custom',
                    name: query.trim()
                });
            } else if (matchedItems.length === 0) {
                html = `<div style="padding:14px;text-align:center;color:#94a3b8;font-size:12px;">
                    <i class="fas fa-keyboard" style="font-size:20px;color:#cbd5e1;display:block;margin-bottom:6px;"></i>
                    Ketik nama barang atau alat untuk mencari di inventori
                </div>`;
            }

            box.innerHTML = html;
            box.style.display = 'block';
        }

        function hideSuggestions() {
            const box = document.getElementById('item-suggestions-box');
            if (box) box.style.display = 'none';
            activeSuggestionIndex = -1;
        }

        function onItemSearchKeydown(e) {
            const box = document.getElementById('item-suggestions-box');
            const items = box ? box.querySelectorAll('.suggestion-item') : [];

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (items.length === 0) return;
                activeSuggestionIndex = (activeSuggestionIndex + 1) % items.length;
                highlightSuggestionItem(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (items.length === 0) return;
                activeSuggestionIndex = (activeSuggestionIndex - 1 + items.length) % items.length;
                highlightSuggestionItem(items);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (activeSuggestionIndex >= 0 && items[activeSuggestionIndex]) {
                    items[activeSuggestionIndex].click();
                } else {
                    addManualItem();
                }
            } else if (e.key === 'Escape') {
                hideSuggestions();
            }
        }

        function highlightSuggestionItem(items) {
            items.forEach((it, idx) => {
                if (idx === activeSuggestionIndex) {
                    it.style.backgroundColor = '#e0f2fe';
                    it.scrollIntoView({ block: 'nearest' });
                } else {
                    it.style.backgroundColor = it.classList.contains('suggestion-custom-opt') ? '#fffbeb' : '';
                }
            });
        }

        document.addEventListener('click', function(e) {
            const adderBox = document.getElementById('item-adder-box');
            if (adderBox && !adderBox.contains(e.target)) {
                hideSuggestions();
            }
        });

        function validateManualQty() {
            const type = document.getElementById('selected-item-type').value;
            if (type === 'custom') {
                if (manualWarning) manualWarning.style.display = 'none';
                return;
            }

            const stock = parseFloat(document.getElementById('selected-item-stock').value || 0);
            const qty = parseFloat(manualQty.value || 0);
            const unit = document.getElementById('manual-custom-unit').value || 'unit';

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
            const nameInput = document.getElementById('manual-custom-name');
            const unitInput = document.getElementById('manual-custom-unit');
            const typeInput = document.getElementById('selected-item-type');
            const idInput = document.getElementById('selected-item-id');
            const codeInput = document.getElementById('selected-item-code');
            const stockInput = document.getElementById('selected-item-stock');

            const name = nameInput ? nameInput.value.trim() : '';
            let unit = (unitInput && unitInput.value.trim()) ? unitInput.value.trim() : 'pcs';
            const qty = parseFloat(manualQty.value);
            let type = typeInput ? typeInput.value : 'custom';
            let id = idInput ? idInput.value : '';
            let code = codeInput ? codeInput.value : '';
            let stock = parseFloat(stockInput ? stockInput.value : 0);

            // Pencarian otomatis berdasarkan NAMA: jika nama persis sama dengan inventori, otomatis hubungkan
            if ((type === 'custom' || !id) && name) {
                const allItems = getAllInventoryItems();
                const matchedByName = allItems.find(it => it.name.trim().toLowerCase() === name.toLowerCase());
                if (matchedByName) {
                    type = matchedByName.type;
                    id = matchedByName.id;
                    code = matchedByName.code;
                    unit = matchedByName.unit;
                    stock = matchedByName.stock;
                }
            }

            if (!name) {
                alert("Ketik atau pilih nama barang/alat terlebih dahulu.");
                if (nameInput) nameInput.focus();
                return;
            }

            if (isNaN(qty) || qty <= 0) {
                alert("Masukkan jumlah (qty) yang valid.");
                manualQty.focus();
                return;
            }

            // Check if master item is already in table
            if (type !== 'custom' && id) {
                const inputField = type === 'material' ? 'material_id' : 'tool_id';
                const existingInput = itemsBody.querySelector(`input[name$="[${inputField}]"][value="${id}"]`);
                if (existingInput) {
                    const row = existingInput.closest('tr');
                    const qtyField = row.querySelector('.qty-input');
                    const newQty = (parseFloat(qtyField.value) || 0) + qty;
                    qtyField.value = type === 'material' ? Math.round(newQty * 100) / 100 : Math.round(newQty);
                    row.style.transition = 'background-color 0.3s';
                    row.style.backgroundColor = '#ecfdf5';
                    setTimeout(() => row.style.backgroundColor = '', 800);
                    
                    // Reset
                    unlinkMasterItem(true);
                    manualQty.value = '1';
                    nameInput.focus();
                    return;
                }
            }

            const tr = document.createElement('tr');
            tr.dataset.kind = type;
            tr.dataset.source = 'manual';

            if (type === 'material' && id) {
                tr.innerHTML = `
                    <td><span class="badge" style="background:#f1f5f9;color:#334155;font-size:11px;font-weight:600;padding:3px 8px;border-radius:4px;"><i class="fas fa-cube me-1"></i>Material</span></td>
                    <td>
                        <input type="hidden" name="items[${rowIndex}][type]" value="material">
                        <input type="hidden" name="items[${rowIndex}][material_id]" value="${id}">
                        <div class="fw-600" style="color:#1e293b;font-size:13px;">${escapeHtml(name)}</div>
                        <div class="text-muted" style="font-size:11.5px;display:flex;align-items:center;gap:6px;margin-top:2px;">
                            ${code ? `<code style="font-size:11px;background:#f1f5f9;padding:1px 5px;border-radius:4px;color:#475569;">${escapeHtml(code)}</code> &bull; ` : ''}
                            <span>Stok Gudang Asal: <strong>${stock} ${escapeHtml(unit)}</strong></span>
                        </div>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <input type="number" name="items[${rowIndex}][quantity]" class="form-control qty-input"
                                value="${qty}" min="0.01" step="0.01" required style="width:100px;text-align:center;font-weight:700;height:34px;border-radius:6px;font-size:13px;">
                            <span class="text-muted" style="font-size:12.5px;font-weight:500;">${escapeHtml(unit)}</span>
                        </div>
                    </td>
                    <td style="text-align:center;">
                        <button type="button" class="btn btn-sm btn-light border text-danger" onclick="this.closest('tr').remove(); updateBadge();" title="Hapus item" style="width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;">
                            <i class="fas fa-trash-can" style="font-size:11px;"></i>
                        </button>
                    </td>`;
            } else if (type === 'tool' && id) {
                tr.innerHTML = `
                    <td><span class="badge" style="background:#f5f3ff;color:#6d28d9;font-size:11px;font-weight:600;padding:3px 8px;border-radius:4px;"><i class="fas fa-wrench me-1"></i>Alat</span></td>
                    <td>
                        <input type="hidden" name="items[${rowIndex}][type]" value="tool">
                        <input type="hidden" name="items[${rowIndex}][tool_id]" value="${id}">
                        <div class="fw-600" style="color:#1e293b;font-size:13px;">${escapeHtml(name)}</div>
                        <div class="text-muted" style="font-size:11.5px;display:flex;align-items:center;gap:6px;margin-top:2px;">
                            ${code ? `<code style="font-size:11px;background:#f1f5f9;padding:1px 5px;border-radius:4px;color:#475569;">${escapeHtml(code)}</code> &bull; ` : ''}
                            <span>Stok Gudang Asal: <strong>${stock} unit</strong></span>
                        </div>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <input type="number" name="items[${rowIndex}][quantity]" class="form-control qty-input"
                                value="${qty}" min="1" step="1" required style="width:100px;text-align:center;font-weight:700;height:34px;border-radius:6px;font-size:13px;">
                            <span class="text-muted" style="font-size:12.5px;font-weight:500;">unit</span>
                        </div>
                    </td>
                    <td style="text-align:center;">
                        <button type="button" class="btn btn-sm btn-light border text-danger" onclick="this.closest('tr').remove(); updateBadge();" title="Hapus alat" style="width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;">
                            <i class="fas fa-trash-can" style="font-size:11px;"></i>
                        </button>
                    </td>`;
            } else {
                // Custom / Manual item
                tr.innerHTML = `
                    <td><span class="badge" style="background:#fef3c7;color:#92400e;font-size:11px;font-weight:600;padding:3px 8px;border-radius:4px;"><i class="fas fa-pen-nib me-1"></i>Manual</span></td>
                    <td>
                        <input type="hidden" name="items[${rowIndex}][type]" value="custom">
                        <input type="hidden" name="items[${rowIndex}][custom_item_name]" value="${escapeHtml(name)}">
                        <input type="hidden" name="items[${rowIndex}][custom_item_unit]" value="${escapeHtml(unit)}">
                        <div class="fw-600" style="color:#1e293b;font-size:13px;">${escapeHtml(name)}</div>
                        <div class="text-muted" style="font-size:11.5px;display:flex;align-items:center;gap:6px;margin-top:2px;">
                            <span class="badge" style="background:#fef3c7;color:#92400e;font-size:10px;font-weight:600;padding:1px 5px;border-radius:3px;">Input Manual</span>
                            <span>Non-Master Stok</span>
                        </div>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <input type="number" name="items[${rowIndex}][quantity]" class="form-control qty-input"
                                value="${qty}" min="0.01" step="0.01" required style="width:100px;text-align:center;font-weight:700;height:34px;border-radius:6px;font-size:13px;">
                            <span class="text-muted" style="font-size:12.5px;font-weight:500;">${escapeHtml(unit)}</span>
                        </div>
                    </td>
                    <td style="text-align:center;">
                        <button type="button" class="btn btn-sm btn-light border text-danger" onclick="this.closest('tr').remove(); updateBadge();" title="Hapus item" style="width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;">
                            <i class="fas fa-trash-can" style="font-size:11px;"></i>
                        </button>
                    </td>`;
            }

            itemsBody.appendChild(tr);
            rowIndex++;
            updateBadge();

            // Reset inputs for next entry
            unlinkMasterItem(true);
            manualQty.value = '1';
            unitInput.value = 'pcs';
            if (nameInput) nameInput.focus();
        }

        function updateManualSelect() {
            // Re-render suggestions if suggestion box is open when warehouse changes
            const box = document.getElementById('item-suggestions-box');
            if (box && box.style.display !== 'none') {
                const val = document.getElementById('manual-custom-name').value;
                renderSuggestions(val);
            }
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