<x-app-layout>
    <x-slot name="title">Catat Penerimaan Barang Masuk</x-slot>

    @push('styles')
    <style>
        .receipt-create-layout {
            display: flex;
            flex-direction: column;
            gap: 20px;
            width: 100%;
        }

        .receipt-items-table th {
            padding: 10px 12px !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.4px !important;
            color: #64748b !important;
            background: #f8fafc !important;
            border-bottom: 2px solid #e2e8f0 !important;
            white-space: nowrap !important;
        }

        .receipt-items-table td {
            padding: 8px 10px !important;
            vertical-align: middle !important;
        }

        .receipt-items-table .form-control {
            height: 38px !important;
            padding: 6px 10px !important;
            font-size: 12.5px !important;
            line-height: 1.4 !important;
            border-radius: 6px !important;
            border: 1px solid #cbd5e1 !important;
            box-sizing: border-box !important;
            background-color: #ffffff !important;
            color: #0f172a !important;
        }

        .receipt-items-table .form-control:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 2px rgba(37,99,235,0.15) !important;
            outline: none !important;
        }

        /* ── Session Card Premium UI ── */
        .session-card {
            border: none !important;
            border-radius: 14px !important;
            overflow: hidden !important;
            box-shadow: 0 2px 16px rgba(15,23,42,0.07), 0 1px 4px rgba(15,23,42,0.04) !important;
        }

        .session-card .form-control {
            height: 40px !important;
            padding: 6px 12px !important;
            font-size: 12.5px !important;
            line-height: 1.4 !important;
            border-radius: 8px !important;
            border: 1.5px solid #e2e8f0 !important;
            box-sizing: border-box !important;
            background-color: #f8fafc !important;
            color: #0f172a !important;
            transition: all 0.2s ease !important;
        }

        .session-card .form-control:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.12) !important;
            background-color: #ffffff !important;
            outline: none !important;
        }

        .session-card textarea.form-control {
            height: auto !important;
            min-height: 72px !important;
            padding: 10px 12px !important;
            resize: vertical !important;
        }

        .session-card select.form-control {
            cursor: pointer !important;
        }

        .session-field-group {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 14px 16px;
            position: relative;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .session-field-group:hover {
            border-color: #cbd5e1;
            box-shadow: 0 2px 8px rgba(15,23,42,0.05);
        }

        .session-field-group .field-group-icon {
            width: 30px;
            height: 30px;
            border-radius: 7px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            flex-shrink: 0;
            margin-bottom: 10px;
        }

        .session-field-group .group-label {
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .session-field-group .form-label {
            font-size: 11.5px !important;
            font-weight: 600 !important;
            color: #475569 !important;
            margin-bottom: 5px !important;
            display: block !important;
        }

        .input-icon-wrap {
            position: relative;
        }

        .input-icon-wrap .input-icon {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 12.5px;
            pointer-events: none;
            z-index: 1;
        }

        .input-icon-wrap .form-control {
            padding-left: 34px !important;
        }

        .input-icon-wrap .form-control:focus ~ .input-icon,
        .input-icon-wrap .form-control:not(:placeholder-shown) ~ .input-icon {
            color: #2563eb;
        }

        .session-info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
        }

        @media (max-width: 992px) {
            .session-info-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 640px) {
            .session-info-grid {
                grid-template-columns: 1fr;
            }
        }

        .submit-bar {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-top: 1.5px solid #e2e8f0;
            padding: 16px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .submit-bar .save-btn {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border: none;
            color: #ffffff;
            font-size: 13.5px;
            font-weight: 700;
            padding: 10px 28px;
            border-radius: 9px;
            box-shadow: 0 3px 10px rgba(37,99,235,0.35);
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .submit-bar .save-btn:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            box-shadow: 0 5px 16px rgba(37,99,235,0.45);
            transform: translateY(-1px);
        }

        .btn-tab-filter {
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 11.5px;
            font-weight: 700;
            cursor: pointer;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #475569;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
        }

        .btn-tab-filter:hover {
            background: #f8fafc;
            color: #0f172a;
            border-color: #94a3b8;
        }

        .btn-tab-filter.active {
            color: #ffffff !important;
        }

        #tab-btn-all.active {
            background: #1e293b !important;
            border-color: #1e293b !important;
            box-shadow: 0 1px 3px rgba(30,41,59,0.3);
        }

        #tab-btn-material.active {
            background: #2563eb !important;
            border-color: #2563eb !important;
            box-shadow: 0 1px 3px rgba(37,99,235,0.3);
        }

        #tab-btn-tool.active {
            background: #d97706 !important;
            border-color: #d97706 !important;
            box-shadow: 0 1px 3px rgba(217,119,6,0.3);
        }

        .btn-tab-filter.active i {
            color: #ffffff !important;
        }

        .btn-tab-filter .count-badge {
            background: #f1f5f9;
            color: #475569;
            padding: 1px 6px;
            border-radius: 10px;
            font-size: 10.5px;
            font-weight: 700;
        }

        .btn-tab-filter.active .count-badge {
            background: rgba(255,255,255,0.25) !important;
            color: #ffffff !important;
        }

        .btn-delete-row {
            width: 32px !important;
            height: 32px !important;
            padding: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 6px !important;
            color: #dc2626 !important;
            background: #ffffff !important;
            border: 1px solid #fecaca !important;
            transition: all 0.15s ease !important;
            cursor: pointer !important;
            font-size: 12px !important;
        }

        .btn-delete-row:hover {
            background: #fef2f2 !important;
            border-color: #ef4444 !important;
            color: #b91c1c !important;
            transform: scale(1.05) !important;
        }

        /* Item Metadata & Stage Pill Styles */
        .item-meta-container {
            margin-top: 6px;
            font-size: 11px;
            line-height: 1.4;
        }

        .item-meta-tags {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-wrap: wrap;
            margin-bottom: 4px;
        }

        .item-stages-box {
            background: #fffdf5;
            border: 1px solid #fef08a;
            border-radius: 6px;
            padding: 6px 8px;
            margin-top: 5px;
        }

        .stage-chips-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }

        .btn-stage-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 5px;
            font-size: 11px;
            font-weight: 600;
            background: #ffffff;
            border: 1px solid #fde68a;
            color: #92400e;
            cursor: pointer;
            transition: all 0.15s ease;
            text-decoration: none;
            margin-top: 2px;
            margin-right: 2px;
        }

        .btn-stage-pill:hover {
            background: #fef3c7;
            border-color: #f59e0b;
            color: #78350f;
            transform: translateY(-1px);
        }

        .btn-stage-pill.active {
            background: #2563eb !important;
            border-color: #1d4ed8 !important;
            color: #ffffff !important;
            box-shadow: 0 1px 4px rgba(37,99,235,0.25);
        }

        .btn-stage-pill.active i {
            color: #93c5fd !important;
        }

        .stage-ref-input:focus {
            border-color: #f59e0b !important;
            box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2) !important;
        }

        /* Modal Styles */
        .scheduled-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(4px);
            z-index: 2500;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        .scheduled-modal-overlay.active {
            display: flex;
        }

        .scheduled-modal-box {
            background: #ffffff;
            border-radius: 14px;
            width: 100%;
            max-width: 720px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 40px rgba(0,0,0,0.25);
            animation: modalFadeIn 0.2s ease;
            overflow: hidden;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-12px) scale(0.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .scheduled-modal-header {
            padding: 16px 20px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
        }

        .scheduled-modal-body {
            padding: 16px 20px;
            overflow-y: auto;
            flex: 1;
        }

        .scheduled-modal-footer {
            padding: 14px 20px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            background: #f8fafc;
        }

        .scheduled-item-card {
            border: 1.5px solid #e2e8f0;
            border-radius: 9px;
            padding: 10px 14px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            background: #ffffff;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .scheduled-item-card:hover {
            border-color: #3b82f6;
            background: #f0f7ff;
        }

        .scheduled-item-card.selected {
            border-color: #2563eb;
            background: #eff6ff;
            box-shadow: 0 1px 4px rgba(37,99,235,0.1);
        }

        @media (max-width: 992px) {
            .receipt-create-layout {
                grid-template-columns: 1fr;
            }
            .sidebar-sticky-box {
                position: static !important;
            }
        }
    </style>
    @endpush

    {{-- Breadcrumb --}}
    <div class="breadcrumb mb-2" style="font-size:12px;">
        <a href="{{ route('goods-receipts.index') }}"><i class="fas fa-truck-ramp-box" style="font-size:11px;"></i> Penerimaan Barang</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:9px;"></i></span>
        <span>Catat Penerimaan Baru</span>
    </div>

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-2 mb-3">
        <div>
            <h2 class="fw-700" style="font-size:18px;color:#0f172a;margin:0;display:flex;align-items:center;gap:8px;">
                <i class="fas fa-dolly text-primary"></i> Catat Penerimaan Barang & Alat Masuk
            </h2>
            <p class="text-muted" style="font-size:12.5px;margin-top:2px;margin-bottom:0;">
                Input kedatangan fisik material dan alat kerja dari supplier atau PO dalam satu sesi dokumen penerimaan.
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('goods-receipts.index') }}" class="btn btn-secondary btn-sm" style="border-radius:6px;font-size:12px;">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    @if (isset($errors) && $errors->any())
    <div class="alert alert-danger mb-3" style="border-radius:8px;padding:12px 14px;font-size:13px;">
        <i class="fas fa-circle-exclamation" style="font-size:16px;"></i>
        <div>
            <strong style="display:block;margin-bottom:4px;">Terdapat kesalahan pengisian data:</strong>
            <ul style="margin:0;padding-left:18px;line-height:1.5;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('goods-receipts.store') }}" id="receiptForm">
        @csrf
        <div class="receipt-create-layout">

            {{-- ── 1. Bagian Atas: Rincian Barang & Alat Masuk (Full Width) ── --}}
            <div class="card shadow-sm" style="border:1px solid #e2e8f0;border-radius:12px;background:#ffffff;overflow:hidden;">
                <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:12px 18px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
                    
                    {{-- Judul & Filter Tab (Material sendiri, Alat sendiri, Semua) --}}
                    <div class="flex items-center gap-3 flex-wrap">
                        <div style="display:flex;align-items:center;gap:6px;">
                            <i class="fas fa-boxes-stacked text-primary" style="font-size:15px;"></i>
                            <span class="card-title" style="font-size:14px;font-weight:700;color:#1e293b;">Rincian Barang & Alat Diterima</span>
                        </div>

                        {{-- Filter Tabs (Material sendiri & Alat sendiri) --}}
                        <div class="flex items-center gap-1" style="background:#e2e8f0;padding:3px;border-radius:8px;">
                            <button type="button" class="btn-tab-filter active" id="tab-btn-all" onclick="filterItemsTab('all')">
                                <i class="fas fa-list"></i> Semua <span class="count-badge" id="count-all">0</span>
                            </button>
                            <button type="button" class="btn-tab-filter" id="tab-btn-material" onclick="filterItemsTab('material')">
                                <i class="fas fa-box" style="color:#2563eb;"></i> Material <span class="count-badge" id="count-material">0</span>
                            </button>
                            <button type="button" class="btn-tab-filter" id="tab-btn-tool" onclick="filterItemsTab('tool')">
                                <i class="fas fa-helmet-safety" style="color:#d97706;"></i> Alat <span class="count-badge" id="count-tool">0</span>
                            </button>
                        </div>
                    </div>

                    {{-- Tombol Tambah & Tarik dari Jadwal --}}
                    <div class="flex items-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-sm btn-info" id="btnOpenScheduleModal" onclick="openScheduledModal()" style="border-radius:6px;font-size:12px;font-weight:600;padding:6px 14px;color:#ffffff;background:#0891b2;border:none;">
                            <i class="fas fa-calendar-check me-1"></i> Tarik dari Jadwal Kedatangan <span class="badge" id="badgeSchedCount" style="background:rgba(255,255,255,0.25);font-size:11px;margin-left:3px;display:none;">0</span>
                        </button>
                        <button type="button" class="btn btn-sm btn-primary" id="btnAddMaterial" onclick="addRow('material')" style="border-radius:6px;font-size:12px;font-weight:600;padding:6px 14px;">
                            <i class="fas fa-box-open me-1"></i> + Material
                        </button>
                        <button type="button" class="btn btn-sm btn-warning" id="btnAddTool" onclick="addRow('tool')" style="border-radius:6px;font-size:12px;font-weight:600;padding:6px 14px;color:#ffffff;background:#d97706;">
                            <i class="fas fa-helmet-safety me-1"></i> + Alat
                        </button>
                    </div>
                </div>

                {{-- Fast 1-Click Scheduled Import Banner --}}
                <div id="scheduledBanner" style="display:none;margin:12px 18px 0;padding:10px 14px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                    <div style="display:flex;align-items:center;gap:10px;font-size:12.5px;color:#166534;">
                        <i class="fas fa-bolt text-warning" style="font-size:16px;"></i>
                        <span>Terdeteksi <strong id="scheduledBannerCount">0</strong> barang dalam jadwal rencana kedatangan.</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <button type="button" class="btn btn-sm btn-success" onclick="autoImportAllScheduled()" style="font-size:11.5px;font-weight:700;padding:5px 12px;border-radius:6px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                            <i class="fas fa-check-double me-1"></i> Tarik Semua Otomatis (Cepat)
                        </button>
                        <button type="button" class="btn btn-sm btn-light border" onclick="openScheduledModal()" style="font-size:11.5px;padding:5px 10px;border-radius:6px;">
                            Pilih Satuan
                        </button>
                    </div>
                </div>

                <div class="table-wrap">
                    <table class="data-table receipt-items-table mb-0" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="width:110px;">Tipe</th>
                                <th>Nama Barang / Item <span class="text-danger">*</span></th>
                                <th style="width:130px;text-align:center;">Qty Masuk <span class="text-danger">*</span></th>
                                <th style="width:90px;text-align:center;">Satuan</th>
                                <th style="width:45px;text-align:center;"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            {{-- Diisi oleh JS addRow() atau prefill AJAX dari PO / Jadwal --}}
                        </tbody>
                    </table>
                </div>

                {{-- Total Summary Strip --}}
                <div style="padding:10px 18px;background:#f8fafc;border-top:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                    <div style="font-size:12px;color:#64748b;" id="totalItemCount">
                        Total: <strong>0</strong> item barang & alat
                    </div>
                    <div class="flex items-center gap-2" style="font-size:11.5px;color:#64748b;">
                        <span>Barang belum ada di pilihan?</span>
                        <a href="{{ route('materials.create') }}" target="_blank" class="text-primary fw-600">+ Master Material</a>
                        <span>•</span>
                        <a href="{{ route('tools.create') }}" target="_blank" class="text-warning fw-600" style="color:#d97706 !important;">+ Master Alat</a>
                    </div>
                </div>
            </div>

            {{-- ── 2. Bagian Bawah: Data Sesi Penerimaan (Premium UI) ── --}}
            <div class="card session-card">

                {{-- Header --}}
                <div style="background:linear-gradient(135deg,#1e293b 0%,#0f172a 100%);padding:14px 22px;display:flex;align-items:center;gap:12px;">
                    <div style="width:36px;height:36px;border-radius:9px;background:rgba(255,255,255,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-clipboard-list" style="color:#93c5fd;font-size:15px;"></i>
                    </div>
                    <div>
                        <div style="font-size:14px;font-weight:700;color:#f1f5f9;letter-spacing:0.2px;">Data Sesi Penerimaan</div>
                        <div style="font-size:11px;color:#94a3b8;margin-top:1px;">Lengkapi informasi dokumen: gudang tujuan, pemasok, tanggal &amp; surat jalan</div>
                    </div>
                </div>

                {{-- Form Body --}}
                <div style="padding:20px 22px;background:#fafbfd;">
                    <div class="session-info-grid">

                        {{-- ─── Kolom 1: Tanggal & Gudang ─── --}}
                        <div class="session-field-group">
                            <div class="group-label" style="color:#2563eb;">
                                <span style="width:26px;height:26px;border-radius:6px;background:#eff6ff;display:inline-flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-calendar-alt" style="font-size:11px;color:#2563eb;"></i>
                                </span>
                                Waktu &amp; Lokasi
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    Tanggal Terima <span class="text-danger">*</span>
                                </label>
                                <div class="input-icon-wrap">
                                    <i class="fas fa-calendar-day input-icon"></i>
                                    <input type="date" id="receiptDateInput" name="received_at"
                                        value="{{ old('received_at', date('Y-m-d')) }}"
                                        class="form-control" required onchange="onDateChanged()">
                                </div>
                            </div>

                            <div class="mb-0">
                                <label class="form-label">
                                    Gudang Tujuan <span class="text-danger">*</span>
                                </label>
                                <div class="input-icon-wrap">
                                    <i class="fas fa-warehouse input-icon"></i>
                                    <select name="warehouse_id" class="form-control @error('warehouse_id') is-invalid @enderror" required>
                                        <option value="">— Pilih Gudang —</option>
                                        @foreach($warehouses as $wh)
                                        <option value="{{ $wh->id }}" {{ (old('warehouse_id') == $wh->id || (empty(old('warehouse_id')) && (session('active_warehouse_id') == $wh->id || $warehouses->count() === 1))) ? 'selected' : '' }}>
                                            {{ $wh->name }} {{ $wh->is_central ? '(Pusat)' : '(Proyek)' }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('warehouse_id')<div class="invalid-feedback d-block" style="font-size:11px;">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- ─── Kolom 2: Supplier & PO ─── --}}
                        <div class="session-field-group">
                            <div class="group-label" style="color:#059669;">
                                <span style="width:26px;height:26px;border-radius:6px;background:#f0fdf4;display:inline-flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-building" style="font-size:11px;color:#059669;"></i>
                                </span>
                                Pemasok &amp; Dokumen PO
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    Nama Pemasok / Supplier <span class="text-danger">*</span>
                                </label>
                                <input type="hidden" name="supplier_id" id="supplierIdHidden" value="{{ old('supplier_id') }}">
                                <div class="input-icon-wrap">
                                    <i class="fas fa-store input-icon"></i>
                                    <input type="text"
                                        id="supplierNameInput"
                                        name="supplier_name"
                                        class="form-control @error('supplier_id') is-invalid @enderror"
                                        value="{{ old('supplier_name', old('supplier_id') ? $suppliers->find(old('supplier_id'))?->name : '') }}"
                                        placeholder="Cari atau ketik nama supplier..."
                                        list="supplierDatalist"
                                        autocomplete="off"
                                        oninput="onSupplierInput(this)"
                                        required>
                                </div>
                                <datalist id="supplierDatalist">
                                    @foreach($suppliers as $sup)
                                    <option value="{{ $sup->name }}" data-id="{{ $sup->id }}">{{ $sup->name }}</option>
                                    @endforeach
                                </datalist>
                                <div id="supplierHint" style="font-size:10.5px;margin-top:4px;color:#64748b;display:flex;align-items:center;gap:4px;">
                                    <i class="fas fa-circle-info" style="color:#94a3b8;"></i>
                                    Pilih dari daftar atau ketik supplier baru — akan otomatis terdaftar
                                </div>
                                @error('supplier_id')<div class="invalid-feedback d-block" style="font-size:11px;">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-0">
                                <label class="form-label">
                                    Hubungkan Purchase Order (PO)
                                    <span style="font-size:10px;font-weight:500;color:#94a3b8;margin-left:4px;">(Opsional)</span>
                                </label>
                                <div class="input-icon-wrap">
                                    <i class="fas fa-file-contract input-icon"></i>
                                    <select id="poSelect" name="purchase_order_id" class="form-control" onchange="loadPoItems(this.value)">
                                        <option value="">— Tanpa PO (Pembelian Langsung) —</option>
                                        @foreach($purchaseOrders as $po)
                                        <option value="{{ $po->id }}" data-supplier="{{ $po->supplier_id }}" data-supplier-name="{{ $po->supplier?->name }}" {{ old('purchase_order_id') == $po->id ? 'selected' : '' }}>
                                            #{{ $po->po_number }} • {{ $po->supplier?->name }}
                                            @if($po->status === 'partial_received') (Sebagian Diterima) @endif
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="poInfoBanner" class="mt-2" style="display:none;padding:7px 11px;background:#eff6ff;border-radius:7px;border:1px solid #bfdbfe;font-size:11px;color:#1d4ed8;line-height:1.5;">
                                    <i class="fas fa-link me-1"></i>
                                    <span id="poBannerText"></span>
                                </div>
                            </div>
                        </div>

                        {{-- ─── Kolom 3: Penerima, Surat Jalan & Catatan ─── --}}
                        <div class="session-field-group">
                            <div class="group-label" style="color:#d97706;">
                                <span style="width:26px;height:26px;border-radius:6px;background:#fffbeb;display:inline-flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-user-tie" style="font-size:11px;color:#d97706;"></i>
                                </span>
                                Penerima &amp; Referensi
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    Nama Penerima Barang
                                    <span style="font-size:10px;font-weight:500;color:#94a3b8;margin-left:4px;">(Wajib)</span>
                                </label>
                                <div class="input-icon-wrap">
                                    <i class="fas fa-user-check input-icon"></i>
                                    <input type="text" name="received_by_name"
                                        id="receivedByNameInput"
                                        value="{{ old('received_by_name', auth()->user()->name) }}"
                                        class="form-control"
                                        placeholder="Nama lengkap penerima barang...">
                                </div>
                                <div style="font-size:10.5px;margin-top:4px;color:#64748b;display:flex;align-items:center;gap:4px;">
                                    <i class="fas fa-circle-info" style="color:#94a3b8;"></i>
                                    Terisi otomatis nama Anda — ubah jika penerima orang lain
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    No. Surat Jalan / Faktur Vendor
                                </label>
                                <div class="input-icon-wrap">
                                    <i class="fas fa-hashtag input-icon"></i>
                                    <input type="text" name="invoice_number" value="{{ old('invoice_number') }}"
                                        class="form-control" placeholder="Contoh: SJ-2026/09/001">
                                </div>
                            </div>

                            <div class="mb-0">
                                <label class="form-label">Catatan Penerimaan</label>
                                <div style="position:relative;">
                                    <i class="fas fa-note-sticky" style="position:absolute;left:11px;top:11px;color:#94a3b8;font-size:12px;pointer-events:none;"></i>
                                    <textarea name="notes" class="form-control" rows="2"
                                        style="padding-left:34px !important;"
                                        placeholder="Keterangan pengiriman, supir, nomor plat armada...">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ─── Submit Bar ─── --}}
                <div class="submit-bar">
                    <div style="font-size:11.5px;color:#64748b;display:flex;align-items:center;gap:6px;">
                        <i class="fas fa-shield-halved" style="color:#94a3b8;"></i>
                        Data akan disimpan sebagai <strong style="color:#334155;">Draft</strong> — dapat dikonfirmasi setelah diperiksa
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <a href="{{ route('goods-receipts.index') }}" class="btn btn-secondary" style="font-size:13px;padding:9px 18px;border-radius:8px;font-weight:600;">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                        <button type="submit" class="save-btn">
                            <i class="fas fa-floppy-disk me-2"></i> Simpan Penerimaan (Draft)
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>

    {{-- ── Modal: Tarik dari Jadwal Kedatangan ── --}}
    <div class="scheduled-modal-overlay" id="scheduledModal">
        <div class="scheduled-modal-box" style="max-width:680px;">
            <div class="scheduled-modal-header">
                <div class="flex items-center gap-2">
                    <i class="fas fa-calendar-check text-primary" style="font-size:18px;"></i>
                    <div>
                        <h4 class="fw-700" style="font-size:15px;margin:0;color:#0f172a;">Jadwal Kedatangan Barang & Alat</h4>
                        <p class="text-muted" style="font-size:11.5px;margin:1px 0 0 0;">Pilih item yang tiba untuk langsung dimasukkan tanpa ketik ulang.</p>
                    </div>
                </div>
                <button type="button" class="btn-close-modal" onclick="closeScheduledModal()">&times;</button>
            </div>

            <div class="scheduled-modal-body">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:12px;flex-wrap:wrap;background:#f8fafc;padding:8px 12px;border-radius:8px;border:1px solid #e2e8f0;">
                    <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                        <button type="button" id="btnSchedAll" class="btn btn-sm btn-primary" onclick="setModalFilter('all')" style="font-size:11.5px;font-weight:700;border-radius:6px;padding:4px 10px;">
                            <i class="fas fa-list-check me-1"></i> Semua Rencana (<span id="countSchedAll">0</span>)
                        </button>
                        <button type="button" id="btnSchedToday" class="btn btn-sm btn-light border" onclick="setModalFilter('today')" style="font-size:11.5px;font-weight:600;border-radius:6px;padding:4px 10px;">
                            <i class="fas fa-calendar-day me-1"></i> Hari Ini (<span id="countSchedToday">0</span>)
                        </button>
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span style="font-size:11px;color:#64748b;font-weight:600;">Tanggal:</span>
                        <input type="date" id="modalScheduleDate" class="form-control form-control-sm" style="font-size:11.5px;width:135px;height:30px;padding:2px 8px;" onchange="setModalFilter('date')">
                    </div>
                </div>

                <div id="scheduledItemsList">
                    <div class="text-center p-4 text-muted" style="font-size:13px;">
                        <i class="fas fa-spinner fa-spin me-1"></i> Memuat daftar jadwal kedatangan...
                    </div>
                </div>
            </div>

            <div class="scheduled-modal-footer" style="display:flex;align-items:center;justify-content:space-between;padding:12px 18px;border-top:1px solid #e2e8f0;background:#f8fafc;">
                <div>
                    <button type="button" class="btn btn-sm btn-light border" onclick="toggleSelectAllScheduled()" style="font-size:11.5px;padding:4px 10px;">
                        <i class="fas fa-check-square me-1"></i> Pilih / Batal Semua
                    </button>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="closeScheduledModal()" style="font-size:12px;">Batal</button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="applySelectedScheduledItems()" style="font-size:12px;font-weight:700;">
                        <i class="fas fa-check-double me-1"></i> Masukkan Barang Terpilih
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    // Data dari server
    var grMaterials = {!! json_encode($materialsJson) !!};
    var grTools     = {!! json_encode($toolsJson) !!};
    @php
        $suppliersData = $suppliers->map(function($s) {
            return ['id' => $s->id, 'name' => $s->name];
        });
    @endphp
    var grSuppliers = {!! json_encode($suppliersData) !!};
    var grConfig = {
        poItemsUrl:   '{{ url("/goods-receipts/po-items") }}',
        scheduledUrl: '{{ route("goods-receipts.scheduledIncoming") }}',
        today:        '{{ date("Y-m-d") }}'
    };
    </script>
    <script src="{{ asset('js/goods-receipt-create.js') }}"></script>
    @endpush
</x-app-layout>

