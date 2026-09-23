<x-app-layout>
    <x-slot name="title">Catat Pengeluaran Material</x-slot>

    @push('styles')
    <style>
        .material-usage-layout {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 16px;
            align-items: start;
        }

        .mode-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .mode-select-box {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 12px;
            background: #ffffff;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .mode-select-box:hover {
            border-color: #93c5fd;
            background: #fbfdff;
        }

        .mode-select-box.active {
            border-color: #2563eb;
            background: #eff6ff;
            box-shadow: 0 0 0 1px #2563eb;
        }

        .mode-radio {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            border: 2px solid #cbd5e1;
            display: inline-block;
            transition: all 0.15s;
            flex-shrink: 0;
        }

        .mode-select-box.active .mode-radio {
            border-color: #2563eb;
            background: #2563eb;
            box-shadow: inset 0 0 0 2.5px #ffffff;
        }

        .panel-mr-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }

        .is-invalid {
            border-color: #ef4444 !important;
            background-color: #fef2f2 !important;
        }

        .row-fulfilled {
            background: #f8fafc !important;
            opacity: 0.65;
        }

        .row-out-of-stock {
            background: #fffbeb !important;
        }

        .sidebar-sticky-bon .form-control {
            height: 36px;
            padding: 7px 10px;
            font-size: 12px;
            line-height: 1.4;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            box-sizing: border-box;
            background-color: #ffffff;
            color: #0f172a;
        }

        .sidebar-sticky-bon .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
            outline: none;
        }

        .sidebar-sticky-bon textarea.form-control {
            height: auto !important;
            min-height: 64px;
            line-height: 1.4;
            padding: 8px 10px;
        }

        #mrSelect {
            height: 38px !important;
            padding: 7px 12px !important;
            font-size: 12.5px !important;
            line-height: 1.4 !important;
            border-radius: 8px !important;
            border: 1.5px solid #cbd5e1 !important;
            box-sizing: border-box !important;
            background-color: #ffffff !important;
            color: #0f172a !important;
            font-weight: 500 !important;
            transition: all 0.2s ease !important;
        }

        #mrSelect:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.15) !important;
            outline: none !important;
        }

        .mr-detail-card {
            margin-top: 12px;
            background: #ffffff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 6px -1px rgba(37,99,235,0.08);
        }

        /* Responsive Breakpoints */
        @media (max-width: 992px) {
            .material-usage-layout {
                grid-template-columns: 1fr !important;
            }
            .sidebar-sticky-bon {
                position: static !important;
            }
        }

        @media (max-width: 640px) {
            .mode-grid {
                grid-template-columns: 1fr !important;
                gap: 8px;
            }
            .panel-mr-grid {
                grid-template-columns: 1fr 1fr !important;
                gap: 8px !important;
            }
        }
    </style>
    @endpush

    <div class="breadcrumb mb-2" style="font-size:12px;">
        <a href="{{ route('material-usages.index') }}">Pemakaian Material</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:9px;"></i></span>
        <span>Catat Pengeluaran Baru</span>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger mb-3" style="border-radius: 6px; font-size:12px; padding:8px 12px;">
        <i class="fas fa-triangle-exclamation text-danger" style="font-size: 14px;"></i>
        <div>
            <strong>Terdapat kesalahan pada input pengeluaran:</strong>
            <ul style="margin: 3px 0 0 16px; font-size: 11.5px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('material-usages.store') }}" id="usageForm" onsubmit="return validateBeforeSubmit(this)">
        @csrf
        <input type="hidden" name="input_mode" id="inputMode" value="mr">

        <div class="material-usage-layout mb-3">

            {{-- ── Kiri: Metode MR + Tabel Material ── --}}
            <div style="display:flex; flex-direction:column; gap:16px;">

                {{-- ==================== SECTION 1: METODE & SURAT PERMINTAAN (MR) ==================== --}}
                <div class="card" style="border:1px solid #e2e8f0; border-radius:10px; background:#ffffff; box-shadow:0 2px 6px -1px rgba(0,0,0,0.03); overflow:hidden;">
                    <div class="card-header" style="background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:10px 14px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                        <div style="display:flex; align-items:center; gap:6px;">
                            <i class="fas fa-file-invoice text-primary" style="font-size:13px;"></i>
                            <span class="card-title" style="font-size:13px; font-weight:700; color:#1e293b;">Metode & Surat Permintaan Material</span>
                        </div>
                        <span class="badge" style="background:#eff6ff; color:#2563eb; font-size:10.5px; padding:2px 8px; border-radius:12px; border:1px solid #bfdbfe; font-weight:600;">
                            Gudang: {{ $selectedWarehouse?->name ?? '-' }}
                        </span>
                    </div>
                    <div class="card-body" style="padding:14px;">
                        {{-- Mode Selector Cards --}}
                        <div class="mode-grid mb-2">
                            <!-- Mode MR -->
                            <div class="mode-select-box active" id="modeCardMR" onclick="switchMode('mr')">
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-file-shield text-primary" style="font-size: 13px;"></i>
                                        <strong style="font-size: 12.5px; color: #0f172a;">Tarik dari Permintaan (MR)</strong>
                                    </div>
                                    <span class="mode-radio"></span>
                                </div>
                                <p class="text-muted mb-0" style="font-size: 11px; line-height: 1.35; color:#64748b;">
                                    Pengeluaran terkontrol sesuai kuota MR yang telah disetujui. Sisa kuota diperbarui otomatis.
                                </p>
                            </div>

                            <!-- Mode Manual -->
                            <div class="mode-select-box" id="modeCardManual" onclick="switchMode('manual')">
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-pen-to-square text-warning" style="font-size: 13px;"></i>
                                        <strong style="font-size: 12.5px; color: #0f172a;">Input Bebas / Insidentil</strong>
                                    </div>
                                    <span class="mode-radio"></span>
                                </div>
                                <p class="text-muted mb-0" style="font-size: 11px; line-height: 1.35; color:#64748b;">
                                    Pengeluaran langsung tanpa surat MR untuk kebutuhan mendesak lapangan.
                                </p>
                            </div>
                        </div>

                        {{-- Dropdown MR --}}
                        <div id="mrPickerWrapper">
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:5px;">
                                <label class="form-label mb-0" style="font-size:11.5px; font-weight:600; color:#334155; display:flex; align-items:center; gap:6px;">
                                    <i class="fas fa-file-signature text-primary" style="font-size:12px;"></i>
                                    Pilih Nomor Permintaan Material (MR) <span class="text-danger">*</span>
                                </label>
                                @if($approvedMRs->isNotEmpty())
                                <span class="badge" style="background:#eff6ff; color:#2563eb; font-size:10px; font-weight:600; padding:2px 8px; border-radius:12px; border:1px solid #bfdbfe;">
                                    {{ $approvedMRs->count() }} Siap Dikeluarkan
                                </span>
                                @endif
                            </div>

                            <select name="material_request_id" id="mrSelect" class="form-control" onchange="loadMRDetails(this.value)">
                                <option value="">— Pilih Nomor MR yang Telah Disetujui —</option>
                                @foreach($approvedMRs as $mr)
                                <option value="{{ $mr->id }}" {{ old('material_request_id') == $mr->id ? 'selected' : '' }}>
                                    #{{ $mr->request_number }} • {{ $mr->items->count() }} Jenis Item | Pemohon: {{ $mr->requestedBy?->name ?? 'User' }} [{{ $mr->status === 'partially_fulfilled' ? 'Terkirim Sebagian' : 'Disetujui' }}]
                                </option>
                                @endforeach
                            </select>

                            @if($approvedMRs->isEmpty())
                            <div style="display:flex; align-items:center; gap:8px; margin-top:8px; padding:9px 12px; border-radius:6px; background:#fffbeb; border:1px solid #fde68a; color:#92400e; font-size:11.5px;">
                                <i class="fas fa-circle-info" style="font-size:13px; color:#d97706; flex-shrink:0;"></i>
                                <span>Tidak ditemukan MR berstatus <strong>Disetujui / Terkirim Sebagian</strong> pada gudang ini. Anda dapat menggunakan mode <em>Input Bebas / Insidentil</em> di atas.</span>
                            </div>
                            @endif

                            <!-- Summary Box MR Terpilih -->
                            <div id="mrDetailPanel" class="mr-detail-card" style="display: none;">
                                <div style="padding: 8px 12px; background: #eff6ff; border-bottom: 1px solid #bfdbfe; display: flex; justify-content: space-between; align-items: center;">
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <div style="width:24px; height:24px; border-radius:6px; background:#2563eb; color:#ffffff; display:flex; align-items:center; justify-content:center; font-size:11px;">
                                            <i class="fas fa-file-lines"></i>
                                        </div>
                                        <strong id="panelMrNumber" style="font-size: 12.5px; color:#1e40af; font-weight:700;">#REQ-...</strong>
                                    </div>
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <span class="badge" id="panelMrStatus" style="font-size: 10.5px; background:#ecfdf5; color:#047857; border:1px solid #a7f3d0; border-radius:12px; padding:2px 8px; font-weight:600;">Disetujui</span>
                                        <button type="button" onclick="clearSelectedMR()" style="background:none; border:none; color:#64748b; cursor:pointer; font-size:11px; padding:2px 6px; display:inline-flex; align-items:center; gap:3px;" title="Reset / Ganti MR">
                                            <i class="fas fa-xmark"></i> Batal
                                        </button>
                                    </div>
                                </div>
                                <div class="panel-mr-grid" style="padding: 10px 14px; font-size: 11.5px; background:#ffffff;">
                                    <div>
                                        <span class="text-muted" style="font-size: 10.5px; display: block; margin-bottom:1px;">Pemohon:</span>
                                        <strong id="panelMrRequester" style="color: #0f172a;">-</strong>
                                    </div>
                                    <div>
                                        <span class="text-muted" style="font-size: 10.5px; display: block; margin-bottom:1px;">Penyetuju (SM):</span>
                                        <strong id="panelMrApprover" style="color: #0f172a;">-</strong>
                                    </div>
                                    <div>
                                        <span class="text-muted" style="font-size: 10.5px; display: block; margin-bottom:1px;">Tgl Pengajuan:</span>
                                        <span id="panelMrDate" style="font-weight: 600; color: #0f172a;">-</span>
                                    </div>
                                    <div>
                                        <span class="text-muted" style="font-size: 10.5px; display: block; margin-bottom:1px;">Total Item:</span>
                                        <span id="panelMrItemCount" class="badge" style="font-size: 10.5px; background:#eff6ff; color:#2563eb; font-weight:700; padding:1px 7px; border-radius:4px; border:1px solid #bfdbfe;">-</span>
                                    </div>
                                </div>
                                <div id="panelMrNotesWrapper" style="display: none; padding: 8px 12px; background: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 11.5px; color: #334155;">
                                    <i class="fas fa-quote-left text-muted me-1" style="font-size:10px;"></i>
                                    <strong>Catatan:</strong> <span id="panelMrNotes"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Warning Mode Manual --}}
                        <div id="manualModeAlert" style="display: none; padding: 8px 12px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; font-size: 11.5px; color: #92400e;">
                            <i class="fas fa-triangle-exclamation me-1 text-warning"></i>
                            <strong>Perhatian:</strong> Pengeluaran manual tidak terikat dengan surat MR. Khusus kebutuhan mendesak lapangan.
                        </div>
                    </div>
                </div>

                {{-- ==================== SECTION 2: RINCIAN MATERIAL YANG DIKELUARKAN ==================== --}}
                <div class="card" style="border:1px solid #e2e8f0; border-radius:10px; background:#ffffff; box-shadow:0 2px 6px -1px rgba(0,0,0,0.03); overflow:hidden;">
                    <div class="card-header" style="background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:10px 14px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                        <div style="display:flex; align-items:center; gap:6px;">
                            <i class="fas fa-boxes-stacked text-primary" style="font-size:13px;"></i>
                            <span class="card-title" style="font-size:13px; font-weight:700; color:#1e293b;">Rincian Material yang Dikeluarkan</span>
                        </div>
                        <span id="itemsLoadingSpinner" style="display: none; font-size: 11.5px; color: #2563eb; font-weight: 600;">
                            <i class="fas fa-spinner fa-spin me-1"></i> Memuat data MR...
                        </span>

                        <div id="mrQuickActions" style="display: none;" class="flex gap-1">
                            <button type="button" class="btn btn-sm btn-light border text-primary fw-600" onclick="fillAllRemainingQuota()" title="Otomatis mengisi kuantitas sesuai sisa kuota yang disetujui" style="height: 28px; font-size: 11.5px; border-radius:5px; padding:0 10px;">
                                <i class="fas fa-check-double text-primary me-1"></i> Penuhi Kuota
                            </button>
                            <button type="button" class="btn btn-sm btn-light border text-muted fw-600" onclick="resetAllQuantities()" title="Kosongkan nilai input kuantitas" style="height: 28px; font-size: 11.5px; border-radius:5px; padding:0 8px;">
                                <i class="fas fa-rotate-left me-1"></i> Reset
                            </button>
                        </div>
                        <div id="manualButtonsGroup" style="display: none; gap: 6px;">
                            <button type="button" class="btn btn-sm btn-primary" id="btnAddManualRow" onclick="addManualRow()" style="border-radius:5px; font-size:11.5px; height:28px; padding:0 10px;">
                                <i class="fas fa-plus"></i> Tambah Baris
                            </button>
                            <button type="button" class="btn btn-sm btn-light border text-primary" id="btnAddCustomRow" onclick="addManualRow(null, '', true)" style="border-radius:5px; font-size:11.5px; height:28px; padding:0 10px; font-weight:600;">
                                <i class="fas fa-plus"></i> Item Custom
                            </button>
                        </div>
                    </div>

                    {{-- Search Toolbar --}}
                    <div style="padding: 8px 14px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                        <div style="position: relative; max-width: 320px; width: 100%;">
                            <i class="fas fa-search" style="position: absolute; left: 10px; top: 50%; transform:translateY(-50%); font-size: 11px; color: #94a3b8;"></i>
                            <input type="text" id="tableSearchInput" class="form-control" placeholder="Cari nama atau kode..." oninput="filterTableRows(this.value)" style="height: 30px; padding-left: 28px; font-size: 11.5px; border-radius: 5px; border-color:#cbd5e1;">
                        </div>
                        <span class="text-muted" id="rowCountIndicator" style="font-size: 11px; font-weight: 600;">
                            0 item
                        </span>
                    </div>

                    {{-- Table Rincian Material --}}
                    <div class="table-wrap" style="overflow-x:auto; -webkit-overflow-scrolling:touch;">
                        <table class="data-table mb-0" id="itemsTable">
                            <thead>
                                <tr style="background:#fafafa; border-bottom:1px solid #e2e8f0; font-size:10.5px; text-transform:uppercase; letter-spacing:0.3px; color:#64748b;">
                                    <th style="text-align: left; padding:7px 12px;">Nama Material</th>
                                    <th style="text-align: center; width: 110px; padding:7px 8px;" class="col-mr-only">MR Disetujui</th>
                                    <th style="text-align: center; width: 100px; padding:7px 8px;" class="col-mr-only">Sisa Kuota</th>
                                    <th style="text-align: center; width: 100px; padding:7px 8px;">Stok Gudang</th>
                                    <th style="text-align: center; width: 140px; padding:7px 8px;">Jumlah Keluar <span class="text-danger">*</span></th>
                                    <th style="text-align: left; padding:7px 12px;">Keterangan</th>
                                    <th style="width: 36px; text-align: center; padding:7px 6px;"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                {{-- Dynamic rows --}}
                            </tbody>
                        </table>

                        {{-- Empty State jika belum pilih MR --}}
                        <div id="mrEmptyState" style="padding: 28px 16px; text-align: center; color: #94a3b8;">
                            <i class="fas fa-inbox" style="font-size: 24px; color: #cbd5e1; margin-bottom: 6px; display:block;"></i>
                            <div style="font-size: 12.5px; font-weight: 600; color: #475569;">Belum Ada Permintaan (MR) yang Dipilih</div>
                            <div style="font-size: 11px; margin-top: 2px;">Silakan pilih nomor MR pada bagian <strong>Metode & Surat Permintaan Material</strong> di atas.</div>
                        </div>
                    </div>

                    {{-- Summary Bar --}}
                    <div style="padding: 8px 14px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                        <div style="display: flex; align-items: center; gap: 14px; font-size: 11.5px;">
                            <div>Material: <strong id="sumItemCount" style="color: #0f172a;">0</strong> jenis</div>
                            <div>Total Kuantitas: <strong id="sumQuantityCount" style="color: #2563eb;">0</strong></div>
                        </div>
                        <div id="statusValidationBadge">
                            <span class="badge" style="background:#f1f5f9; color:#64748b; font-size: 10.5px; padding:2px 8px; border-radius:12px; border:1px solid #cbd5e1;">
                                Belum Ada Kuantitas
                            </span>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ── Kanan: Informasi Bon Pengeluaran ── --}}
            <div class="card sidebar-sticky-bon" id="bonSidebarCard" style="position:sticky; top:20px; border:1px solid #e2e8f0; border-radius:10px; background:#ffffff; box-shadow:0 2px 6px -1px rgba(0,0,0,0.03); overflow:hidden;">
                <div class="card-header" style="background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:10px 14px; display:flex; align-items:center; gap:6px;">
                    <i class="fas fa-file-invoice text-primary" style="font-size:13px;"></i>
                    <span class="card-title" style="font-size:13px; font-weight:700; color:#1e293b;">Informasi Bon Pengeluaran</span>
                </div>
                <div class="card-body" style="padding:14px;">

                    {{-- Gudang Sumber (Otomatis seperti di Surat Jalan) --}}
                    <div class="mb-2">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:3px;">
                            <label class="form-label mb-0" style="font-size:11px; font-weight:600; color:#334155;">Gudang Sumber <span class="text-danger">*</span></label>
                            @if($warehouses->count() > 1 && auth()->user()->hasAnyRole(['Owner', 'Admin', 'Admin Gudang Pusat', 'Admin PO']))
                            <button type="button" onclick="toggleWarehouseSwitch()" style="background:none; border:none; padding:0; font-size:10.5px; color:#2563eb; font-weight:600; cursor:pointer;" title="Ganti Gudang">
                                <i class="fas fa-arrow-right-arrow-left" style="font-size:9.5px;"></i> Ganti
                            </button>
                            @endif
                        </div>

                        {{-- Hidden actual value submitted with form --}}
                        <input type="hidden" name="warehouse_id" id="warehouseSelect" value="{{ $selectedWarehouse?->id }}">

                        {{-- Readonly Box: Otomatis --}}
                        <div id="warehouseLockedDisplay" style="display:flex; align-items:center; justify-content:space-between; padding:7px 10px; background:#f8fafc; border:1px solid #cbd5e1; border-radius:6px; min-height:36px; box-sizing:border-box;">
                            <div style="display:flex; align-items:center; gap:7px; overflow:hidden;">
                                <i class="fas fa-warehouse text-primary" style="font-size:12px; flex-shrink:0;"></i>
                                <span style="font-size:12px; font-weight:600; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" id="displayWarehouseName">
                                    {{ $selectedWarehouse?->name ?? 'Gudang Pusat' }} {{ $selectedWarehouse?->is_central ? '(Pusat)' : '' }}
                                </span>
                            </div>
                            <span class="badge" style="background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe; font-size:10px; padding:2px 7px; border-radius:4px; font-weight:600; flex-shrink:0;">
                                <i class="fas fa-lock" style="font-size:8.5px;"></i> Otomatis
                            </span>
                        </div>

                        @if($warehouses->count() > 1 && auth()->user()->hasAnyRole(['Owner', 'Admin', 'Admin Gudang Pusat', 'Admin PO']))
                        <div id="warehouseSwitchWrapper" style="display:none; margin-top:5px;">
                            <select id="warehousePickerDropdown" class="form-control" onchange="onWarehouseChanged(this.value)" style="height:34px; padding:5px 9px; font-size:12px; border-radius:6px; border-color:#93c5fd; box-sizing:border-box;">
                                @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ $selectedWarehouse?->id == $wh->id ? 'selected' : '' }}>
                                    {{ $wh->name }} {{ $wh->is_central ? '(Pusat)' : '' }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                    </div>

                    {{-- Nama Penerima --}}
                    <div class="mb-2">
                        <label class="form-label" style="font-size:11px; font-weight:600; color:#334155; margin-bottom:3px;">Penerima (Mandor / Tukang) <span class="text-danger">*</span></label>
                        <input type="text" name="recipient_name" id="recipientNameInput" class="form-control"
                               placeholder="Contoh: Pak Supri (Mandor)"
                               value="{{ old('recipient_name') }}" required>
                    </div>

                    {{-- Bagian Pekerjaan / Zona --}}
                    <div class="mb-2">
                        <label class="form-label" style="font-size:11px; font-weight:600; color:#334155; margin-bottom:3px;">Pekerjaan / Zona <span class="text-muted fw-400">(Opsional)</span></label>
                        <input type="text" name="job_section" id="jobSectionInput" class="form-control"
                               placeholder="Contoh: Kolom Lt. 2"
                               value="{{ old('job_section') }}">
                    </div>

                    {{-- Tanggal Pengeluaran --}}
                    <div class="mb-2">
                        <label class="form-label" style="font-size:11px; font-weight:600; color:#334155; margin-bottom:3px;">Tanggal Pengeluaran <span class="text-danger">*</span></label>
                        <input type="date" name="usage_date" class="form-control"
                               value="{{ old('usage_date', date('Y-m-d')) }}" required>
                    </div>

                    {{-- Catatan Tambahan --}}
                    <div class="mb-3">
                        <label class="form-label" style="font-size:11px; font-weight:600; color:#334155; margin-bottom:3px;">Catatan Tambahan</label>
                        <textarea name="notes" id="notesInput" class="form-control" rows="2"
                            placeholder="Catatan serah terima material...">{{ old('notes') }}</textarea>
                    </div>

                    <div style="display:flex; flex-direction:column; gap:6px;">
                        <button type="submit" class="btn btn-primary w-full" id="submitBtn" style="justify-content:center; padding:8px 12px; height:38px; font-size:12.5px; font-weight:600; border-radius:6px; box-shadow:0 2px 4px rgba(37,99,235,0.2);">
                            <i class="fas fa-check-circle me-1"></i> Simpan &amp; Potong Stok
                        </button>
                        <a href="{{ route('material-usages.index') }}" class="btn btn-light border w-full text-center" style="justify-content:center; padding:6px 12px; height:32px; font-size:12px; font-weight:500; border-radius:6px; color:#64748b;">
                            Batal
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </form>

    {{-- Floating Action Bar for Mobile View --}}
    <div id="mobileUsageSummaryBar" style="display:none;position:fixed;bottom:14px;left:14px;right:14px;z-index:99;background:#0f172a;color:#ffffff;border-radius:10px;padding:10px 14px;box-shadow:0 8px 20px -3px rgba(0,0,0,0.3);align-items:center;justify-content:space-between;gap:10px;">
        <div style="display:flex;align-items:center;gap:6px;">
            <i class="fas fa-boxes-stacked text-primary" style="font-size:13px;"></i>
            <span style="font-size:12px;font-weight:600;"><span id="mobileUsageItemCount">0</span> Item Siap</span>
        </div>
        <button type="button" onclick="scrollToBonForm()" class="btn btn-sm btn-primary" style="padding:5px 12px;font-size:11.5px;font-weight:600;border-radius:5px;box-shadow:none;">
            Simpan Bon &darr;
        </button>
    </div>

    {{-- ==================== JAVASCRIPT ==================== --}}
    @push('scripts')
    <script>
        /* ts-nocheck: Blade directives inside script are server-rendered */
        const availableMaterials = @json($materialsData);
        const materialsGrouped = @json($materialsGrouped ?? []);
        const selectedWarehouseId = {{ $selectedWarehouse?->id ?? 'null' }};
        let currentMode = 'mr';
        let rowIndex = 0;
        let mrItemsCache = [];
        let isLoadingMR = false;

        function scrollToBonForm() {
            const sidebar = document.getElementById('bonSidebarCard');
            if (sidebar) {
                sidebar.scrollIntoView({ behavior: 'smooth' });
                const recipientInput = document.getElementById('recipientNameInput');
                if (recipientInput) setTimeout(() => recipientInput.focus(), 300);
            }
        }

        function switchMode(mode) {
            currentMode = mode;
            document.getElementById('inputMode').value = mode;

            const cardMR = document.getElementById('modeCardMR');
            const cardManual = document.getElementById('modeCardManual');
            const mrPicker = document.getElementById('mrPickerWrapper');
            const manualAlert = document.getElementById('manualModeAlert');
            const manualButtonsGroup = document.getElementById('manualButtonsGroup');
            const mrQuickActions = document.getElementById('mrQuickActions');
            const mrCols = document.querySelectorAll('.col-mr-only');
            const mrSelect = document.getElementById('mrSelect');
            const emptyState = document.getElementById('mrEmptyState');

            if (mode === 'mr') {
                cardMR.classList.add('active');
                cardManual.classList.remove('active');

                mrPicker.style.display = 'block';
                manualAlert.style.display = 'none';
                if (manualButtonsGroup) manualButtonsGroup.style.display = 'none';
                mrCols.forEach(el => el.style.display = '');

                if (mrSelect.value) {
                    mrQuickActions.style.display = 'flex';
                    emptyState.style.display = 'none';
                    loadMRDetails(mrSelect.value);
                } else {
                    mrQuickActions.style.display = 'none';
                    document.getElementById('itemsBody').innerHTML = '';
                    emptyState.style.display = 'block';
                }
            } else {
                cardManual.classList.add('active');
                cardMR.classList.remove('active');

                mrPicker.style.display = 'none';
                manualAlert.style.display = 'block';
                if (manualButtonsGroup) manualButtonsGroup.style.display = 'inline-flex';
                mrQuickActions.style.display = 'none';
                emptyState.style.display = 'none';
                mrCols.forEach(el => el.style.display = 'none');

                mrSelect.value = '';
                document.getElementById('mrDetailPanel').style.display = 'none';

                const tbody = document.getElementById('itemsBody');
                tbody.innerHTML = '';
                addManualRow();
            }
            updateTableSummary();
        }

        function clearSelectedMR() {
            const mrSelect = document.getElementById('mrSelect');
            if (mrSelect) {
                mrSelect.value = '';
                loadMRDetails('');
            }
        }

        function toggleWarehouseSwitch() {
            const wrapper = document.getElementById('warehouseSwitchWrapper');
            if (wrapper) {
                wrapper.style.display = (wrapper.style.display === 'none' || wrapper.style.display === '') ? 'block' : 'none';
            }
        }

        function onWarehouseChanged(warehouseId) {
            window.location.href = '{{ route('material-usages.create') }}?warehouse_id=' + warehouseId;
        }

        async function loadMRDetails(mrId) {
            if (isLoadingMR) return;

            const tbody = document.getElementById('itemsBody');
            const panel = document.getElementById('mrDetailPanel');
            const spinner = document.getElementById('itemsLoadingSpinner');
            const emptyState = document.getElementById('mrEmptyState');
            const quickActions = document.getElementById('mrQuickActions');

            if (!mrId) {
                panel.style.display = 'none';
                quickActions.style.display = 'none';
                tbody.innerHTML = '';
                rowIndex = 0;
                emptyState.style.display = 'block';
                updateTableSummary();
                return;
            }

            isLoadingMR = true;
            emptyState.style.display = 'none';
            spinner.style.display = 'inline-block';

            try {
                const response = await fetch(`/material-requests/${mrId}/details?warehouse_id=${selectedWarehouseId}`);
                if (!response.ok) {
                    throw new Error('Gagal memuat rincian MR dari server');
                }
                const data = await response.json();
                spinner.style.display = 'none';
                quickActions.style.display = 'flex';

                // Fill MR executive panel
                document.getElementById('panelMrNumber').innerText = '#' + data.request_number;
                document.getElementById('panelMrRequester').innerText = data.requested_by;
                document.getElementById('panelMrApprover').innerText = data.approved_by;
                document.getElementById('panelMrDate').innerText = data.created_at;
                document.getElementById('panelMrItemCount').innerText = data.items.length + ' Item';

                const statusBadge = document.getElementById('panelMrStatus');
                if (data.status === 'partially_fulfilled') {
                    statusBadge.style.background = '#eff6ff';
                    statusBadge.style.color = '#2563eb';
                    statusBadge.style.borderColor = '#bfdbfe';
                    statusBadge.innerText = 'Terkirim Sebagian';
                } else {
                    statusBadge.style.background = '#ecfdf5';
                    statusBadge.style.color = '#047857';
                    statusBadge.style.borderColor = '#a7f3d0';
                    statusBadge.innerText = 'Disetujui Site Manager';
                }

                if (data.notes) {
                    document.getElementById('panelMrNotes').innerText = data.notes;
                    document.getElementById('panelMrNotesWrapper').style.display = 'block';
                } else {
                    document.getElementById('panelMrNotesWrapper').style.display = 'none';
                }
                panel.style.display = 'block';

                const notesInput = document.getElementById('notesInput');
                if (!notesInput.value && data.notes) {
                    notesInput.value = 'Berdasarkan MR #' + data.request_number + ': ' + data.notes;
                }

                tbody.innerHTML = '';
                rowIndex = 0;
                mrItemsCache = data.items;

                data.items.forEach(item => {
                    const rowId = 'row-' + rowIndex;
                    const remaining = parseFloat(item.qty_remaining || 0);
                    const stock = parseFloat(item.stock || 0);
                    const defaultQty = remaining > 0 && stock > 0 ? Math.min(remaining, stock) : (remaining > 0 ? remaining : 0);
                    const isFullyFulfilled = remaining <= 0;
                    const isOutOfStock = stock <= 0;

                    const tr = document.createElement('tr');
                    tr.id = rowId;
                    tr.className = (isFullyFulfilled ? 'row-fulfilled' : (isOutOfStock ? 'row-out-of-stock' : ''));
                    tr.style.borderBottom = '1px solid #f1f5f9';

                    tr.innerHTML = `
                        <td style="padding: 7px 12px;">
                            <input type="hidden" name="items[${rowIndex}][material_id]" value="${item.material_id}">
                            <div class="fw-600 text-dark" style="font-size: 12.5px; color:#0f172a;">${item.name}</div>
                            <div class="flex items-center gap-2 mt-1">
                                <code style="font-size: 10.5px; background: #f1f5f9; padding: 1px 4px; border-radius: 3px; color: #475569;">${item.code}</code>
                                <span class="badge" style="font-size: 10px; background:#f1f5f9; color:#64748b; border-radius:3px; padding:1px 5px;">${item.category}</span>
                            </div>
                            ${isFullyFulfilled ? '<span class="badge" style="font-size: 10px; background:#f1f5f9; color:#64748b; margin-top:2px; display:inline-block;"><i class="fas fa-check"></i> Terpenuhi</span>' : ''}
                        </td>
                        <td style="padding: 7px 8px; text-align: center;">
                            <div class="fw-700" style="font-size: 12px; color:#1e293b;">${item.qty_approved} ${item.unit}</div>
                            <div class="text-muted" style="font-size: 10.5px;">Sudah: ${item.qty_fulfilled}</div>
                        </td>
                        <td style="padding: 7px 8px; text-align: center;">
                            <span class="badge" style="font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius:4px; ${remaining > 0 ? 'background:#ecfdf5; color:#047857; border:1px solid #a7f3d0;' : 'background:#f1f5f9; color:#64748b;'}">
                                ${remaining} ${item.unit}
                            </span>
                        </td>
                        <td style="padding: 7px 8px; text-align: center;">
                            <span class="badge" style="font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius:4px; ${stock > 0 ? 'background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe;' : 'background:#fef2f2; color:#b91c1c; border:1px solid #fecaca;'}">
                                ${stock} ${item.unit}
                            </span>
                            ${isOutOfStock ? '<div class="text-danger" style="font-size: 10px; font-weight: 600; margin-top: 1px;">Stok 0</div>' : ''}
                        </td>
                        <td style="padding: 7px 8px;">
                            <div style="display: flex; align-items: center; justify-content:center; gap: 4px;">
                                <input type="number" 
                                       name="items[${rowIndex}][quantity]" 
                                       id="${rowId}-qty" 
                                       class="form-control qty-input text-center"
                                       step="0.01" 
                                       min="0"
                                       data-remaining="${remaining}"
                                       data-stock="${stock}"
                                       placeholder="0" 
                                       value="${isFullyFulfilled ? 0 : defaultQty}" 
                                       ${isFullyFulfilled ? 'readonly' : 'required'}
                                       oninput="validateMRQty('${rowId}')"
                                       style="font-weight: 700; font-size: 12px; height: 30px; border-radius: 5px; width:75px; padding:0 4px;">
                                <span class="text-muted fw-600" style="font-size: 11px; min-width: 24px;">${item.unit}</span>
                            </div>
                            <div id="${rowId}-warning" class="text-danger fw-600" style="font-size: 10px; margin-top: 2px; text-align:center; display: none;"></div>
                        </td>
                        <td style="padding: 7px 10px;">
                            <input type="text" name="items[${rowIndex}][notes]" class="form-control" placeholder="Catatan..." value="${item.notes || ''}" style="font-size: 11.5px; height: 30px; border-radius: 5px;">
                        </td>
                        <td style="padding: 7px 6px; text-align: center;">
                            <button type="button" class="btn btn-sm btn-light text-danger border" onclick="removeRow('${rowId}')" title="Hapus" style="width:26px; height:26px; padding:0; display:inline-flex; align-items:center; justify-content:center; border-radius: 4px;">
                                <i class="fas fa-trash-can" style="font-size: 10px;"></i>
                            </button>
                        </td>
                    `;

                    tbody.appendChild(tr);
                    rowIndex++;
                    validateMRQty(rowId);
                });

                updateTableSummary();

            } catch (err) {
                spinner.style.display = 'none';
                alert('Gagal memuat rincian MR: ' + err.message);
            } finally {
                isLoadingMR = false;
            }
        }

        function validateMRQty(rowId) {
            const input = document.getElementById(rowId + '-qty');
            if (!input) return;

            const remaining = parseFloat(input.getAttribute('data-remaining') || 0);
            const stock = parseFloat(input.getAttribute('data-stock') || 0);
            const val = parseFloat(input.value || 0);
            const warningEl = document.getElementById(rowId + '-warning');

            if (val > remaining) {
                input.classList.add('is-invalid');
                warningEl.style.display = 'block';
                warningEl.innerHTML = `<i class="fas fa-circle-exclamation me-1"></i> Max: ${remaining}`;
            } else if (val > stock) {
                input.classList.add('is-invalid');
                warningEl.style.display = 'block';
                warningEl.innerHTML = `<i class="fas fa-triangle-exclamation me-1"></i> Stok: ${stock}`;
            } else {
                input.classList.remove('is-invalid');
                warningEl.style.display = 'none';
                warningEl.innerText = '';
            }
            updateTableSummary();
        }

        function fillAllRemainingQuota() {
            const rows = document.querySelectorAll('#itemsBody tr');
            rows.forEach(tr => {
                const input = tr.querySelector('.qty-input');
                if (input && !input.readOnly) {
                    const remaining = parseFloat(input.getAttribute('data-remaining') || 0);
                    const stock = parseFloat(input.getAttribute('data-stock') || 0);
                    const autoQty = Math.min(remaining, stock);
                    input.value = autoQty > 0 ? autoQty : 0;
                    validateMRQty(tr.id);
                }
            });
            updateTableSummary();
        }

        function resetAllQuantities() {
            const rows = document.querySelectorAll('#itemsBody tr');
            rows.forEach(tr => {
                const input = tr.querySelector('.qty-input');
                if (input && !input.readOnly) {
                    input.value = 0;
                    validateMRQty(tr.id);
                }
            });
            updateTableSummary();
        }

        function addManualRow(preselectedId = null, qtyVal = '', isCustom = false) {
            const tbody = document.getElementById('itemsBody');
            const rowId = 'row-' + rowIndex;

            let optionsHtml = '<option value="">— Pilih Material —</option>';
            if (materialsGrouped && typeof materialsGrouped === 'object' && !Array.isArray(materialsGrouped)) {
                Object.keys(materialsGrouped).sort().forEach(cat => {
                    optionsHtml += `<optgroup label="${cat}">`;
                    materialsGrouped[cat].forEach(m => {
                        const selected = (!isCustom && preselectedId && preselectedId == m.id) ? 'selected' : '';
                        optionsHtml += `<option value="${m.id}" data-stock="${m.stock}" data-unit="${m.unit}" ${selected}>${m.name} (${m.code}) — Stok: ${m.stock} ${m.unit}</option>`;
                    });
                    optionsHtml += `</optgroup>`;
                });
            } else {
                availableMaterials.forEach(m => {
                    const selected = (!isCustom && preselectedId && preselectedId == m.id) ? 'selected' : '';
                    optionsHtml += `<option value="${m.id}" data-stock="${m.stock}" data-unit="${m.unit}" ${selected}>${m.name} (${m.code}) — Stok: ${m.stock} ${m.unit}</option>`;
                });
            }
            optionsHtml += `<option value="__custom__" ${isCustom ? 'selected' : ''}>+ Item Custom (Tulis Manual)</option>`;

            const tr = document.createElement('tr');
            tr.id = rowId;
            tr.style.borderBottom = '1px solid #f1f5f9';
            tr.innerHTML = `
                <td style="padding: 7px 12px;">
                    <input type="hidden" name="items[${rowIndex}][material_id]" id="${rowId}-material-id" value="">
                    <select id="${rowId}-select" class="form-control material-select" onchange="onManualMaterialChange(this, '${rowId}')" style="font-size: 12px; height: 30px; border-radius: 5px;">
                        ${optionsHtml}
                    </select>
                    <div id="${rowId}-custom-box" style="display: ${isCustom ? 'block' : 'none'}; margin-top: 4px;">
                        <div style="display: flex; gap: 4px;">
                            <input type="text" name="items[${rowIndex}][custom_item_name]" id="${rowId}-custom-name" class="form-control" placeholder="Nama item..." style="font-size: 11.5px; height: 28px; border-radius: 4px;" ${isCustom ? 'required' : ''}>
                            <input type="text" name="items[${rowIndex}][custom_item_unit]" id="${rowId}-custom-unit" class="form-control" placeholder="Satuan" style="font-size: 11.5px; height: 28px; width: 80px; border-radius: 4px;" oninput="onCustomUnitChange(this, '${rowId}')">
                        </div>
                    </div>
                </td>
                <td style="padding: 7px 8px; text-align: center;">
                    <span class="badge" id="${rowId}-stock" style="font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius:4px; background:#f1f5f9; color:#64748b;">-</span>
                </td>
                <td style="padding: 7px 8px;">
                    <div style="display: flex; align-items: center; justify-content:center; gap: 4px;">
                        <input type="number" name="items[${rowIndex}][quantity]" id="${rowId}-qty" class="form-control qty-input text-center"
                               step="0.01" min="0.01" placeholder="0" value="${qtyVal}" required oninput="validateManualQty('${rowId}')"
                               style="font-weight: 700; font-size: 12px; height: 30px; border-radius: 5px; width:75px; padding:0 4px;">
                        <span class="text-muted fw-600" id="${rowId}-unit" style="font-size: 11px; min-width: 24px;">-</span>
                    </div>
                    <div id="${rowId}-warning" class="text-danger fw-600" style="font-size: 10px; margin-top: 2px; text-align:center; display: none;"></div>
                </td>
                <td style="padding: 7px 10px;">
                    <input type="text" name="items[${rowIndex}][notes]" class="form-control" placeholder="Keterangan..." style="font-size: 11.5px; height: 30px; border-radius: 5px;">
                </td>
                <td style="padding: 7px 6px; text-align: center;">
                    <button type="button" class="btn btn-sm btn-light text-danger border" onclick="removeRow('${rowId}')" title="Hapus Item" style="width:26px; height:26px; padding:0; display:inline-flex; align-items:center; justify-content:center; border-radius: 4px;">
                        <i class="fas fa-trash-can" style="font-size: 10px;"></i>
                    </button>
                </td>
            `;

            tbody.appendChild(tr);
            rowIndex++;

            const sel = tr.querySelector('.material-select');
            onManualMaterialChange(sel, rowId);
            updateTableSummary();
        }

        function onManualMaterialChange(select, rowId) {
            const val = select.value;
            const stockBadge = document.getElementById(rowId + '-stock');
            const unitLabel = document.getElementById(rowId + '-unit');
            const qtyInput = document.getElementById(rowId + '-qty');
            const customBox = document.getElementById(rowId + '-custom-box');
            const customName = document.getElementById(rowId + '-custom-name');
            const customUnit = document.getElementById(rowId + '-custom-unit');
            const hiddenMaterialId = document.getElementById(rowId + '-material-id');

            if (val === '__custom__') {
                hiddenMaterialId.value = '';
                customBox.style.display = 'block';
                customName.required = true;
                stockBadge.style.background = '#f5f3ff';
                stockBadge.style.color = '#7c3aed';
                stockBadge.style.border = '1px solid #ddd6fe';
                stockBadge.innerText = 'Custom';
                unitLabel.innerText = customUnit.value.trim() || 'unit';
                qtyInput.removeAttribute('max');
            } else if (val) {
                hiddenMaterialId.value = val;
                customBox.style.display = 'none';
                customName.required = false;
                customName.value = '';
                
                const selectedOpt = select.options[select.selectedIndex];
                const stock = parseFloat(selectedOpt.getAttribute('data-stock') || 0);
                const unit = selectedOpt.getAttribute('data-unit') || '';

                stockBadge.style.background = stock > 0 ? '#eff6ff' : '#fef2f2';
                stockBadge.style.color = stock > 0 ? '#2563eb' : '#b91c1c';
                stockBadge.style.border = stock > 0 ? '1px solid #bfdbfe' : '1px solid #fecaca';
                stockBadge.innerText = stock + ' ' + unit;
                unitLabel.innerText = unit;
                qtyInput.max = stock;
            } else {
                hiddenMaterialId.value = '';
                customBox.style.display = 'none';
                customName.required = false;
                stockBadge.style.background = '#f1f5f9';
                stockBadge.style.color = '#64748b';
                stockBadge.style.border = '1px solid #cbd5e1';
                stockBadge.innerText = '-';
                unitLabel.innerText = '-';
                qtyInput.removeAttribute('max');
            }
            validateManualQty(rowId);
        }

        function onCustomUnitChange(input, rowId) {
            const unitLabel = document.getElementById(rowId + '-unit');
            if (unitLabel) {
                unitLabel.innerText = input.value.trim() || 'unit';
            }
        }

        function validateManualQty(rowId) {
            const qtyInput = document.getElementById(rowId + '-qty');
            if (!qtyInput) return;
            const max = parseFloat(qtyInput.max || 0);
            const val = parseFloat(qtyInput.value || 0);
            const warningEl = document.getElementById(rowId + '-warning');

            if (max > 0 && val > max) {
                qtyInput.classList.add('is-invalid');
                warningEl.style.display = 'block';
                warningEl.innerHTML = `<i class="fas fa-triangle-exclamation me-1"></i> Max: ${max}`;
            } else {
                qtyInput.classList.remove('is-invalid');
                warningEl.style.display = 'none';
            }
            updateTableSummary();
        }

        function removeRow(rowId) {
            const row = document.getElementById(rowId);
            if (row) {
                row.remove();
            }
            const tbody = document.getElementById('itemsBody');
            if (tbody.children.length === 0) {
                if (currentMode === 'manual') {
                    addManualRow();
                } else {
                    document.getElementById('mrEmptyState').style.display = 'block';
                }
            }
            updateTableSummary();
        }

        function filterTableRows(query) {
            const q = query.toLowerCase().trim();
            const rows = document.querySelectorAll('#itemsBody tr');
            let visibleCount = 0;

            rows.forEach(tr => {
                const text = tr.innerText.toLowerCase();
                const match = !q || text.includes(q);
                tr.style.display = match ? '' : 'none';
                if (match) {
                    visibleCount++;
                }
            });

            document.getElementById('rowCountIndicator').innerText = visibleCount + ' item ditampilkan';
        }

        function updateTableSummary() {
            const rows = document.querySelectorAll('#itemsBody tr');
            let totalItems = 0;
            let totalQuantity = 0;
            let hasInvalid = false;

            rows.forEach(tr => {
                const qtyInput = tr.querySelector('.qty-input');
                if (qtyInput) {
                    const q = parseFloat(qtyInput.value || 0);
                    if (q > 0) {
                        totalItems++;
                        totalQuantity += q;
                    }
                    if (qtyInput.classList.contains('is-invalid')) {
                        hasInvalid = true;
                    }
                }
            });

            document.getElementById('sumItemCount').innerText = totalItems;
            document.getElementById('sumQuantityCount').innerText = totalQuantity.toLocaleString('id-ID', { maximumFractionDigits: 2 });
            document.getElementById('rowCountIndicator').innerText = rows.length + ' item terdaftar';

            const badge = document.getElementById('statusValidationBadge');
            if (hasInvalid) {
                badge.innerHTML = `<span class="badge" style="background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; font-size: 10.5px; padding:2px 7px; border-radius:12px;"><i class="fas fa-circle-xmark me-1"></i> Melebihi Batas</span>`;
            } else if (totalItems === 0) {
                badge.innerHTML = `<span class="badge" style="background:#f1f5f9; color:#64748b; border:1px solid #cbd5e1; font-size: 10.5px; padding:2px 7px; border-radius:12px;">Belum Ada Kuantitas</span>`;
            } else {
                badge.innerHTML = `<span class="badge" style="background:#ecfdf5; color:#047857; border:1px solid #a7f3d0; font-size: 10.5px; padding:2px 7px; border-radius:12px;"><i class="fas fa-circle-check me-1"></i> Siap (${totalItems} item)</span>`;
            }

            // Mobile Floating Action Bar
            const mobileBar = document.getElementById('mobileUsageSummaryBar');
            const mobileCount = document.getElementById('mobileUsageItemCount');
            if (mobileBar && mobileCount) {
                mobileCount.innerText = totalItems;
                if (window.innerWidth <= 992 && totalItems > 0 && !hasInvalid) {
                    mobileBar.style.display = 'flex';
                } else {
                    mobileBar.style.display = 'none';
                }
            }
        }

        function validateBeforeSubmit(form) {
            if (currentMode === 'mr') {
                const mrSelect = document.getElementById('mrSelect');
                if (!mrSelect.value) {
                    alert('Silakan pilih Nomor Permintaan (MR) yang telah disetujui terlebih dahulu.');
                    mrSelect.focus();
                    return false;
                }
            }

            const rows = document.querySelectorAll('#itemsBody tr');
            if (rows.length === 0) {
                alert('Daftar material yang dikeluarkan tidak boleh kosong.');
                return false;
            }

            let hasValidQty = false;
            let hasError = false;

            rows.forEach(tr => {
                const qtyInput = tr.querySelector('.qty-input');
                if (qtyInput) {
                    const qty = parseFloat(qtyInput.value || 0);
                    if (qty > 0) hasValidQty = true;

                    if (qtyInput.classList.contains('is-invalid')) {
                        hasError = true;
                    }
                }
            });

            if (!hasValidQty) {
                alert('Silakan masukkan jumlah pengeluaran minimal 1 material dengan kuantitas lebih dari 0.');
                return false;
            }

            if (hasError) {
                alert('Terdapat input kuantitas yang melebihi kuota persetujuan MR atau stok fisik gudang. Mohon perbaiki baris bertanda merah.');
                return false;
            }

            const btn = form.querySelector('button[type=submit]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...';
            }

            return true;
        }

        window.addEventListener('resize', function() {
            updateTableSummary();
        });

        // Initialize on DOM ready
        document.addEventListener('DOMContentLoaded', function() {
            switchMode('mr');
        });
    </script>
    @endpush
</x-app-layout>
