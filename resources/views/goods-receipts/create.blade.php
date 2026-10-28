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

        .receipt-items-table {
            border-collapse: separate !important;
            border-spacing: 0 !important;
            width: 100% !important;
        }

        .receipt-items-table th {
            padding: 12px 14px !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            color: #475569 !important;
            background: #f8fafc !important;
            border-bottom: 2px solid #e2e8f0 !important;
            white-space: nowrap !important;
        }

        .receipt-items-table td {
            padding: 9px 12px !important;
            vertical-align: middle !important;
            border-bottom: 1px solid #f1f5f9 !important;
        }

        .receipt-items-table tbody tr.item-row {
            transition: background-color 0.15s ease !important;
        }

        .receipt-items-table tbody tr.item-row:hover {
            background-color: #f8fafc !important;
        }

        .receipt-items-table .form-control {
            height: 38px !important;
            padding: 6px 10px !important;
            font-size: 12.5px !important;
            line-height: 1.4 !important;
            border-radius: 7px !important;
            border: 1px solid #cbd5e1 !important;
            box-sizing: border-box !important;
            background-color: #ffffff !important;
            color: #0f172a !important;
            transition: all 0.15s ease !important;
        }

        .receipt-items-table .form-control:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.14) !important;
            outline: none !important;
        }

        .receipt-items-table .qty-shipped-input {
            background-color: #f8fafc !important;
            border: 1.5px solid #e2e8f0 !important;
            color: #334155 !important;
            font-weight: 700 !important;
            font-size: 13px !important;
            border-radius: 7px !important;
        }

        .receipt-items-table .qty-input {
            border: 2px solid #3b82f6 !important;
            background-color: #ffffff !important;
            font-weight: 800 !important;
            font-size: 14px !important;
            color: #1e3a8a !important;
            border-radius: 7px !important;
            box-shadow: 0 1px 3px rgba(37,99,235,0.08) !important;
        }

        .receipt-items-table .qty-input:focus {
            border-color: #1d4ed8 !important;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.2) !important;
        }

        .receipt-items-table .qty-missing-input {
            border: 1.5px solid #cbd5e1 !important;
            font-weight: 700 !important;
            font-size: 13px !important;
            border-radius: 7px !important;
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
            border-radius: 8px !important;
            color: #ef4444 !important;
            background: #fef2f2 !important;
            border: 1px solid #fee2e2 !important;
            transition: all 0.18s ease !important;
            cursor: pointer !important;
            font-size: 13px !important;
        }

        .btn-delete-row:hover {
            background: #fee2e2 !important;
            border-color: #fca5a5 !important;
            color: #dc2626 !important;
            transform: scale(1.08) !important;
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

        .mobile-label-tag {
            display: none;
        }

        @media (max-width: 992px) {
            .receipt-create-layout {
                grid-template-columns: 1fr;
            }
            .sidebar-sticky-box {
                position: static !important;
            }
        }

        @media (max-width: 768px) {
            .receipt-card-header {
                flex-direction: column !important;
                align-items: stretch !important;
                padding: 12px 14px !important;
                gap: 12px !important;
            }

            .receipt-header-title-box {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 10px !important;
            }

            .receipt-tabs-wrap {
                width: 100% !important;
                display: flex !important;
                justify-content: stretch !important;
                gap: 4px !important;
            }

            .receipt-tabs-wrap .btn-tab-filter {
                flex: 1 1 0% !important;
                justify-content: center !important;
                padding: 6px 4px !important;
                font-size: 11.5px !important;
            }

            .receipt-btn-actions {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 8px !important;
                width: 100% !important;
            }

            .receipt-btn-actions .btn-sched-import {
                grid-column: 1 / -1 !important;
                width: 100% !important;
                padding: 9px 12px !important;
                font-size: 12px !important;
                text-align: center !important;
                justify-content: center !important;
                display: flex !important;
                align-items: center !important;
            }

            .receipt-btn-actions .btn-add-mat,
            .receipt-btn-actions .btn-add-tool {
                width: 100% !important;
                padding: 8px 10px !important;
                text-align: center !important;
                justify-content: center !important;
                display: flex !important;
                align-items: center !important;
            }

            /* Responsive Card Items for Table on Mobile */
            .table-wrap {
                overflow-x: visible !important;
            }

            .receipt-items-table {
                display: block !important;
                width: 100% !important;
                border: none !important;
            }

            .receipt-items-table thead {
                display: none !important;
            }

            .receipt-items-table tbody {
                display: flex !important;
                flex-direction: column !important;
                gap: 12px !important;
                padding: 12px !important;
                background: #f8fafc !important;
            }

            .receipt-items-table tr.item-row {
                display: grid !important;
                grid-template-columns: 1fr 1fr auto !important;
                grid-template-areas:
                    "type    type    action"
                    "item    item    item"
                    "shipped qty     missing"
                    "unit    notes   notes";
                gap: 10px 12px !important;
                padding: 14px !important;
                background: #ffffff !important;
                border: 1.5px solid #e2e8f0 !important;
                border-radius: 12px !important;
                box-shadow: 0 2px 6px rgba(15, 23, 42, 0.05) !important;
                margin-bottom: 0 !important;
            }

            .receipt-items-table tr.item-row[data-type="tool"] {
                border-left: 5px solid #d97706 !important;
            }

            .receipt-items-table tr.item-row[data-type="material"] {
                border-left: 5px solid #2563eb !important;
            }

            .receipt-items-table td.col-type {
                grid-area: type;
                padding: 0 !important;
                border: none !important;
                display: flex !important;
                align-items: center !important;
            }

            .receipt-items-table td.col-action {
                grid-area: action;
                padding: 0 !important;
                border: none !important;
                display: flex !important;
                justify-content: flex-end !important;
                align-items: center !important;
            }

            .receipt-items-table td.col-action .btn-delete-row {
                width: 32px !important;
                height: 32px !important;
                font-size: 13px !important;
                border-radius: 7px !important;
                background: #fef2f2 !important;
                border: 1px solid #fecaca !important;
                color: #ef4444 !important;
            }

            .receipt-items-table td.col-item {
                grid-area: item;
                padding: 0 !important;
                border: none !important;
                width: 100% !important;
            }

            .receipt-items-table td.col-item .form-control.item-select {
                width: 100% !important;
                font-size: 13px !important;
                padding: 9px 12px !important;
                border-radius: 8px !important;
                border: 1.5px solid #cbd5e1 !important;
                background-color: #ffffff !important;
            }

            .receipt-items-table td.col-shipped {
                grid-area: shipped;
                padding: 0 !important;
                border: none !important;
            }

            .receipt-items-table td.col-qty {
                grid-area: qty;
                padding: 0 !important;
                border: none !important;
            }

            .receipt-items-table td.col-qty .form-control.qty-input {
                width: 100% !important;
                font-size: 14px !important;
                font-weight: 700 !important;
                padding: 8px 12px !important;
                border-radius: 8px !important;
                text-align: left !important;
                border: 1.5px solid #cbd5e1 !important;
            }

            .receipt-items-table td.col-missing {
                grid-area: missing;
                padding: 0 !important;
                border: none !important;
            }

            .receipt-items-table td.col-unit {
                grid-area: unit;
                padding: 0 !important;
                border: none !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: flex-end !important;
                min-width: 85px !important;
            }

            .receipt-items-table td.col-notes {
                grid-area: notes;
                padding: 0 !important;
                border: none !important;
            }

            .receipt-items-table td.col-unit .badge {
                height: 38px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                font-size: 12px !important;
                font-weight: 700 !important;
                border-radius: 8px !important;
                background: #f1f5f9 !important;
                color: #475569 !important;
                border: 1.5px solid #cbd5e1 !important;
            }

            .mobile-label-tag {
                display: block !important;
                font-size: 10.5px !important;
                font-weight: 700 !important;
                color: #64748b !important;
                margin-bottom: 4px !important;
                text-transform: uppercase !important;
                letter-spacing: 0.5px !important;
            }

            .item-stages-box {
                padding: 8px 10px !important;
            }

            .stage-chips-wrap {
                gap: 6px !important;
            }

            .btn-stage-pill {
                padding: 5px 10px !important;
                font-size: 11.5px !important;
            }

            .receipt-summary-strip {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 8px !important;
                padding: 12px 14px !important;
            }

            .receipt-summary-strip .summary-links {
                flex-wrap: wrap !important;
                gap: 6px !important;
            }

            .submit-bar {
                flex-direction: column-reverse !important;
                align-items: stretch !important;
                padding: 14px 16px !important;
                gap: 12px !important;
            }

            .submit-bar > div:last-child {
                display: flex !important;
                flex-direction: column-reverse !important;
                width: 100% !important;
                gap: 8px !important;
            }

            .submit-bar .save-btn,
            .submit-bar .btn-secondary {
                width: 100% !important;
                text-align: center !important;
                justify-content: center !important;
                display: flex !important;
                align-items: center !important;
                padding: 11px 18px !important;
            }
        }
    </style>
    @endpush

    {{-- Breadcrumb --}}
    <div class="breadcrumb mb-2" style="font-size:12px;">
        <a href="{{ route('goods-receipts.index') }}"><x-lucide-package-check style="font-size:11px;" /> Penerimaan Barang</a>
        <span class="breadcrumb-sep"><x-lucide-chevron-right style="font-size:9px;" /></span>
        <span>Catat Penerimaan Baru</span>
    </div>

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-2 mb-3">
        <div>
            <h2 class="fw-700" style="font-size:18px;color:#0f172a;margin:0;display:flex;align-items:center;gap:8px;">
                <x-lucide-package-check class="text-primary" /> Catat Penerimaan Barang & Alat Masuk
            </h2>
            <p class="text-muted" style="font-size:12.5px;margin-top:2px;margin-bottom:0;">
                Input kedatangan fisik material dan alat kerja dari supplier atau PO dalam satu sesi dokumen penerimaan.
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('goods-receipts.index') }}" class="btn btn-secondary btn-sm" style="border-radius:6px;font-size:12px;">
                <x-lucide-arrow-left /> Kembali
            </a>
        </div>
    </div>

    @if (isset($errors) && $errors->any())
    <div class="alert alert-danger mb-3" style="border-radius:8px;padding:12px 14px;font-size:13px;">
        <x-lucide-circle-alert style="font-size:16px;" />
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

    @if(isset($inTransitDistributions) && $inTransitDistributions->isNotEmpty())
    <div class="card mb-3" style="border:1.5px solid #bfdbfe;border-radius:12px;background:linear-gradient(135deg, #eff6ff 0%, #f0fdf4 100%);box-shadow:0 3px 12px rgba(37,99,235,0.08);overflow:hidden;">
        <div class="card-body" style="padding:14px 18px;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                <div style="display:flex;align-items:flex-start;gap:12px;">
                    <div style="width:38px;height:38px;border-radius:9px;background:#2563eb;color:#ffffff;display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 6px rgba(37,99,235,0.3);">
                        <x-lucide-truck style="font-size:16px;" />
                    </div>
                    <div>
                        <h4 style="font-size:13.5px;font-weight:700;color:#1e3a8a;margin:0 0 3px 0;">
                            Barang PO Lapangan Sedang Dikirim dari Gudang Pusat (Surat Jalan Dalam Perjalanan)
                        </h4>
                        <p style="font-size:12px;color:#334155;margin:0;line-height:1.4;">
                            Terdapat <strong>{{ $inTransitDistributions->count() }} Surat Jalan</strong> yang sedang menuju ke gudang proyek Anda. Jika barang fisik telah tiba bersama supir, konfirmasikan penerimaan langsung melalui Surat Jalan terkait:
                        </p>
                    </div>
                </div>
            </div>
            <div style="margin-top:12px;display:flex;flex-direction:column;gap:8px;">
                @foreach($inTransitDistributions as $dist)
                <div style="background:#ffffff;border:1px solid #dbeafe;border-radius:8px;padding:9px 14px;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;box-shadow:0 1px 3px rgba(0,0,0,0.03);">
                    <div style="font-size:12px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <span class="badge" style="background:#eff6ff;color:#1d4ed8;font-weight:700;border:1px solid #bfdbfe;padding:3px 8px;border-radius:6px;">
                            #{{ $dist->distribution_number }}
                        </span>
                        @if($dist->materialRequest)
                            <span style="color:#64748b;">• PO Lapangan:</span>
                            <strong style="color:#0f172a;">#{{ $dist->materialRequest->request_number }}</strong>
                        @endif
                        <span style="color:#64748b;">• Tujuan: <strong style="color:#0f172a;">{{ $dist->toWarehouse?->name }}</strong></span>
                        <span style="color:#64748b;">• Supir: {{ $dist->driver_name ?? '-' }} ({{ $dist->vehicle_number ?? '-' }})</span>
                    </div>
                    <a href="{{ route('distributions.show', $dist) }}" class="btn btn-sm btn-primary" style="font-size:11.5px;font-weight:700;padding:5px 14px;border-radius:6px;box-shadow:0 2px 4px rgba(37,99,235,0.2);">
                        <x-lucide-boxes class="me-1" /> Terima Surat Jalan Ini
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('goods-receipts.store') }}" id="receiptForm">
        @csrf
        <div class="receipt-create-layout">

            {{-- ── 1. Bagian Atas: Rincian Barang & Alat Masuk (Full Width) ── --}}
            <div class="card shadow-sm" style="border:1px solid #e2e8f0;border-radius:12px;background:#ffffff;overflow:hidden;">
                <div class="card-header receipt-card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:12px 18px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
                    
                    {{-- Judul & Filter Tab (Material sendiri, Alat sendiri, Semua) --}}
                    <div class="flex items-center gap-3 flex-wrap receipt-header-title-box">
                        <div style="display:flex;align-items:center;gap:6px;">
                            <x-lucide-boxes class="text-primary" style="font-size:15px;" />
                            <span class="card-title" style="font-size:14px;font-weight:700;color:#1e293b;">Rincian Barang & Alat Diterima</span>
                        </div>

                        {{-- Filter Tabs (Material sendiri & Alat sendiri) --}}
                        <div class="flex items-center gap-1 receipt-tabs-wrap" style="background:#e2e8f0;padding:3px;border-radius:8px;">
                            <button type="button" class="btn-tab-filter active" id="tab-btn-all" onclick="filterItemsTab('all')">
                                <x-lucide-list /> Semua <span class="count-badge" id="count-all">0</span>
                            </button>
                            <button type="button" class="btn-tab-filter" id="tab-btn-material" onclick="filterItemsTab('material')">
                                <x-lucide-package style="color:#2563eb;" /> Material <span class="count-badge" id="count-material">0</span>
                            </button>
                            <button type="button" class="btn-tab-filter" id="tab-btn-tool" onclick="filterItemsTab('tool')">
                                <x-lucide-hard-hat style="color:#d97706;" /> Alat <span class="count-badge" id="count-tool">0</span>
                            </button>
                        </div>
                    </div>

                    {{-- Tombol Tambah & Tarik dari Jadwal --}}
                    <div class="flex items-center gap-2 flex-wrap receipt-btn-actions">
                        <button type="button" class="btn btn-sm btn-info btn-sched-import" id="btnOpenScheduleModal" onclick="openScheduledModal()" style="border-radius:6px;font-size:12px;font-weight:600;padding:6px 14px;color:#ffffff;background:#0891b2;border:none;">
                            <x-lucide-calendar-check class="me-1" /> Tarik dari Jadwal Kedatangan <span class="badge" id="badgeSchedCount" style="background:rgba(255,255,255,0.25);font-size:11px;margin-left:3px;display:none;">0</span>
                        </button>
                        <button type="button" class="btn btn-sm btn-primary btn-add-mat" id="btnAddMaterial" onclick="addRow('material')" style="border-radius:6px;font-size:12px;font-weight:600;padding:6px 14px;">
                            <x-lucide-package-open class="me-1" /> + Material
                        </button>
                        <button type="button" class="btn btn-sm btn-warning btn-add-tool" id="btnAddTool" onclick="addRow('tool')" style="border-radius:6px;font-size:12px;font-weight:600;padding:6px 14px;color:#ffffff;background:#d97706;">
                            <x-lucide-hard-hat class="me-1" /> + Alat
                        </button>
                    </div>
                </div>

                {{-- Fast 1-Click Scheduled Import Banner --}}
                <div id="scheduledBanner" style="display:none;margin:12px 18px 0;padding:10px 14px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                    <div style="display:flex;align-items:center;gap:10px;font-size:12.5px;color:#166534;">
                        <x-lucide-zap class="text-warning" style="font-size:16px;" />
                        <span>Terdeteksi <strong id="scheduledBannerCount">0</strong> barang dalam jadwal rencana kedatangan.</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <button type="button" class="btn btn-sm btn-success" onclick="autoImportAllScheduled()" style="font-size:11.5px;font-weight:700;padding:5px 12px;border-radius:6px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                            <x-lucide-check-check class="me-1" /> Tarik Semua Otomatis (Cepat)
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
                                <th style="width:105px;">Tipe</th>
                                <th style="min-width:220px;">Nama Barang / Item &amp; Referensi <span class="text-danger">*</span></th>
                                <th style="width:115px;text-align:center;" title="Kuantitas yang dipesan pada Purchase Order / PO Lapangan">Qty Target (PO)</th>
                                <th style="width:125px;text-align:center;" title="Kuantitas fisik diterima baik yang akan menambah stok gudang">Qty Masuk (Stok) <span class="text-danger">*</span></th>
                                <th style="width:110px;text-align:center;" title="Selisih jika barang tidak sesuai PO (misal PO 100, selisih 5, maka stok update 95)">Selisih</th>
                                <th style="width:80px;text-align:center;">Satuan</th>
                                <th style="width:170px;">Catatan / Keterangan</th>
                                <th style="width:45px;text-align:center;"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            {{-- Diisi oleh JS addRow() atau prefill AJAX dari PO / Jadwal --}}
                        </tbody>
                    </table>
                </div>

                {{-- Total Summary Strip --}}
                <div class="receipt-summary-strip" style="padding:10px 18px;background:#f8fafc;border-top:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                    <div style="font-size:12px;color:#64748b;" id="totalItemCount">
                        Total: <strong>0</strong> item barang & alat
                    </div>
                    <div class="flex items-center gap-2 summary-links" style="font-size:11.5px;color:#64748b;">
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
                        <x-lucide-clipboard-list style="color:#93c5fd;font-size:15px;" />
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
                                    <x-lucide-calendar style="font-size:11px;color:#2563eb;" />
                                </span>
                                Waktu &amp; Lokasi
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    Tanggal Terima <span class="text-danger">*</span>
                                </label>
                                <div class="input-icon-wrap">
                                    <x-lucide-calendar-days class="input-icon" />
                                    <input type="date" id="receiptDateInput" name="received_at"
                                        value="{{ old('received_at', date('Y-m-d')) }}"
                                        class="form-control" required onchange="onDateChanged()">
                                </div>
                            </div>

                            <div class="mb-0">
                                <label class="form-label" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                                    <span>Gudang Tujuan <span class="text-danger">*</span></span>
                                    <span id="warehouseLockBadge" style="display:none;font-size:10.5px;font-weight:700;color:#92400e;background:#fef3c7;border:1px solid #fde68a;padding:2px 8px;border-radius:4px;align-items:center;gap:4px;">
                                        <x-lucide-lock /> Terkunci Sesuai Tahapan
                                    </span>
                                </label>
                                <div class="input-icon-wrap">
                                    <x-lucide-warehouse class="input-icon" id="warehouseInputIcon" />
                                    <select name="warehouse_id" id="warehouseSelect" class="form-control @error('warehouse_id') is-invalid @enderror" required onchange="onUserWarehouseChange(this)">
                                        <option value="">— Pilih Gudang —</option>
                                        @foreach($warehouses as $wh)
                                        <option value="{{ $wh->id }}" {{ (old('warehouse_id') == $wh->id || (empty(old('warehouse_id')) && (session('active_warehouse_id') == $wh->id || $warehouses->count() === 1))) ? 'selected' : '' }}>
                                            {{ $wh->name }} {{ $wh->is_central ? '(Pusat)' : '(Proyek)' }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="warehouseAutoHint" style="font-size:11px;margin-top:5px;display:none;"></div>
                                @error('warehouse_id')<div class="invalid-feedback d-block" style="font-size:11px;">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- ─── Kolom 2: Supplier & PO ─── --}}
                        <div class="session-field-group">
                            <div class="group-label" style="color:#059669;">
                                <span style="width:26px;height:26px;border-radius:6px;background:#f0fdf4;display:inline-flex;align-items:center;justify-content:center;">
                                    <x-lucide-building-2 style="font-size:11px;color:#059669;" />
                                </span>
                                Pemasok &amp; Dokumen PO
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    Nama Pemasok / Supplier <span class="text-danger">*</span>
                                </label>
                                <input type="hidden" name="supplier_id" id="supplierIdHidden" value="{{ old('supplier_id') }}">
                                <div class="input-icon-wrap">
                                    <x-lucide-store class="input-icon" />
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
                                    <x-lucide-info style="color:#94a3b8;" />
                                    Pilih dari daftar atau ketik supplier baru — akan otomatis terdaftar
                                </div>
                                @error('supplier_id')<div class="invalid-feedback d-block" style="font-size:11px;">{{ $message }}</div>@enderror
                            </div>

                            {{-- Field Hubungkan PO Lapangan --}}
                            <div class="mb-3">
                                <label class="form-label" style="display:flex;align-items:center;justify-content:space-between;">
                                    <span>Hubungkan PO Lapangan</span>
                                    <span style="font-size:10px;font-weight:500;color:#94a3b8;">(Permintaan Proyek)</span>
                                </label>
                                <div class="input-icon-wrap">
                                    <x-lucide-clipboard-check class="input-icon" />
                                    <select id="mrSelect" name="material_request_id" class="form-control" onchange="onMrSelectChange(this.value)">
                                        <option value="">— Pilih PO Lapangan (Opsional) —</option>
                                        @foreach($materialRequests as $mr)
                                        @php
                                            $projectName = $mr->toWarehouse?->project?->name ?? $mr->toWarehouse?->name;
                                            $poIds = $mr->purchaseOrders->pluck('id')
                                                ->merge($mr->items->flatMap->purchaseOrderItems->pluck('purchase_order_id'))
                                                ->filter()->unique()->implode(',');
                                            $inTransitCount = $mr->distributions->where('status', 'in_transit')->count();
                                        @endphp
                                        <option value="{{ $mr->id }}"
                                            data-warehouse-id="{{ $mr->to_warehouse_id }}"
                                            data-project="{{ $projectName }}"
                                            data-po-ids="{{ $poIds }}"
                                            data-in-transit="{{ $inTransitCount }}"
                                            {{ (old('material_request_id', $selectedMrId ?? '') == $mr->id) ? 'selected' : '' }}>
                                            #{{ $mr->request_number }} • {{ $projectName }} ({{ $mr->requestedBy?->name ?? '-' }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="mrInfoBanner" class="mt-2" style="display:none;padding:7px 11px;background:#f0fdf4;border-radius:7px;border:1px solid #bbf7d0;font-size:11px;color:#166534;line-height:1.5;">
                                    <x-lucide-circle-check class="me-1" />
                                    <span id="mrBannerText"></span>
                                </div>
                            </div>

                            <div class="mb-0">
                                <label class="form-label">
                                    Hubungkan Purchase Order (PO)
                                    <span style="font-size:10px;font-weight:500;color:#94a3b8;margin-left:4px;">(Pesanan Vendor)</span>
                                </label>
                                <div class="input-icon-wrap">
                                    <x-lucide-file-text class="input-icon" />
                                    <select id="poSelect" name="purchase_order_id" class="form-control" onchange="onPoSelectChange(this.value)">
                                        <option value="">— Tanpa PO (Pembelian Langsung) —</option>
                                        @foreach($purchaseOrders as $po)
                                        @php
                                            $mrInfo = $po->materialRequest ?? $po->items->map(fn($it) => $it->materialRequestItem?->materialRequest)->filter()->unique('id')->first();
                                            $mrText = $mrInfo ? ' [PO Lapangan: #' . $mrInfo->request_number . ' - ' . ($mrInfo->toWarehouse?->project?->name ?? $mrInfo->toWarehouse?->name) . ']' : '';
                                        @endphp
                                        <option value="{{ $po->id }}"
                                            data-supplier="{{ $po->supplier_id }}"
                                            data-supplier-name="{{ $po->supplier?->name }}"
                                            data-warehouse-id="{{ $po->delivery_warehouse_id }}"
                                            data-mr-id="{{ $po->material_request_id ?? $mrInfo?->id ?? '' }}"
                                            {{ (old('purchase_order_id', $selectedPoId ?? '') == $po->id) ? 'selected' : '' }}>
                                            #{{ $po->po_number }} • {{ $po->supplier?->name }}{{ $mrText }}
                                            @if($po->status === 'partial_received') (Sebagian Diterima) @endif
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div style="font-size:10.5px;margin-top:4px;color:#64748b;display:flex;align-items:center;gap:4px;">
                                    <x-lucide-info style="color:#94a3b8;" />
                                    Pilih PO vendor yang armadanya sedang tiba di lokasi proyek
                                </div>
                                <div id="poInfoBanner" class="mt-2" style="display:none;padding:7px 11px;background:#eff6ff;border-radius:7px;border:1px solid #bfdbfe;font-size:11px;color:#1d4ed8;line-height:1.5;">
                                    <x-lucide-link class="me-1" />
                                    <span id="poBannerText"></span>
                                </div>
                            </div>
                        </div>

                        {{-- ─── Kolom 3: Penerima, Surat Jalan & Catatan ─── --}}
                        <div class="session-field-group">
                            <div class="group-label" style="color:#d97706;">
                                <span style="width:26px;height:26px;border-radius:6px;background:#fffbeb;display:inline-flex;align-items:center;justify-content:center;">
                                    <x-lucide-user-check style="font-size:11px;color:#d97706;" />
                                </span>
                                Penerima &amp; Referensi
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    Nama Penerima Barang
                                    <span style="font-size:10px;font-weight:500;color:#94a3b8;margin-left:4px;">(Wajib)</span>
                                </label>
                                <div class="input-icon-wrap">
                                    <x-lucide-user-check class="input-icon" />
                                    <input type="text" name="received_by_name"
                                        id="receivedByNameInput"
                                        value="{{ old('received_by_name', auth()->user()->name) }}"
                                        class="form-control"
                                        placeholder="Nama lengkap penerima barang...">
                                </div>
                                <div style="font-size:10.5px;margin-top:4px;color:#64748b;display:flex;align-items:center;gap:4px;">
                                    <x-lucide-info style="color:#94a3b8;" />
                                    Terisi otomatis nama Anda — ubah jika penerima orang lain
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    No. Surat Jalan / Faktur Vendor
                                </label>
                                <div class="input-icon-wrap">
                                    <x-lucide-hash class="input-icon" />
                                    <input type="text" name="invoice_number" value="{{ old('invoice_number') }}"
                                        class="form-control" placeholder="Contoh: SJ-2026/09/001">
                                </div>
                            </div>

                            <div class="mb-0">
                                <label class="form-label">Catatan Penerimaan</label>
                                <div style="position:relative;">
                                    <x-lucide-sticky-note style="position:absolute;left:11px;top:11px;color:#94a3b8;font-size:12px;pointer-events:none;" />
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
                        <x-lucide-shield style="color:#94a3b8;" />
                        Data akan disimpan sebagai <strong style="color:#334155;">Draft</strong> — dapat dikonfirmasi setelah diperiksa
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <a href="{{ route('goods-receipts.index') }}" class="btn btn-secondary" style="font-size:13px;padding:9px 18px;border-radius:8px;font-weight:600;">
                            <x-lucide-arrow-left class="me-1" /> Kembali
                        </a>
                        <button type="submit" class="save-btn">
                            <x-lucide-save class="me-2" /> Simpan Penerimaan (Draft)
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
                    <x-lucide-calendar-check class="text-primary" style="font-size:18px;" />
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
                            <x-lucide-list-checks class="me-1" /> Semua Rencana (<span id="countSchedAll">0</span>)
                        </button>
                        <button type="button" id="btnSchedToday" class="btn btn-sm btn-light border" onclick="setModalFilter('today')" style="font-size:11.5px;font-weight:600;border-radius:6px;padding:4px 10px;">
                            <x-lucide-calendar-days class="me-1" /> Hari Ini (<span id="countSchedToday">0</span>)
                        </button>
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span style="font-size:11px;color:#64748b;font-weight:600;">Tanggal:</span>
                        <input type="date" id="modalScheduleDate" class="form-control form-control-sm" style="font-size:11.5px;width:135px;height:30px;padding:2px 8px;" onchange="setModalFilter('date')">
                    </div>
                </div>

                <div id="scheduledItemsList">
                    <div class="text-center p-4 text-muted" style="font-size:13px;">
                        <x-lucide-loader-2 class="me-1 animate-spin" /> Memuat daftar jadwal kedatangan...
                    </div>
                </div>
            </div>

            <div class="scheduled-modal-footer" style="display:flex;align-items:center;justify-content:space-between;padding:12px 18px;border-top:1px solid #e2e8f0;background:#f8fafc;">
                <div>
                    <button type="button" class="btn btn-sm btn-light border" onclick="toggleSelectAllScheduled()" style="font-size:11.5px;padding:4px 10px;">
                        <x-lucide-square-check class="me-1" /> Pilih / Batal Semua
                    </button>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="closeScheduledModal()" style="font-size:12px;">Batal</button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="applySelectedScheduledItems()" style="font-size:12px;font-weight:700;">
                        <x-lucide-check-check class="me-1" /> Masukkan Barang Terpilih
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    // Data dari server
    var grMaterials = @json($materialsJson);
    var grTools     = @json($toolsJson);
    @php
        $suppliersData = $suppliers->map(function($s) {
            return ['id' => $s->id, 'name' => $s->name];
        });
        $mrsData = $materialRequests->map(function($mr) {
            $poIds = $mr->purchaseOrders->pluck('id')
                ->merge($mr->items->flatMap->purchaseOrderItems->pluck('purchase_order_id'))
                ->filter()->unique()->values()->all();
            $inTransitCount = $mr->distributions->where('status', 'in_transit')->count();
            return [
                'id' => $mr->id,
                'request_number' => $mr->request_number,
                'warehouse_id' => $mr->to_warehouse_id,
                'project_name' => $mr->toWarehouse?->project?->name ?? $mr->toWarehouse?->name,
                'po_ids' => $poIds,
                'in_transit_count' => $inTransitCount,
            ];
        });
    @endphp
    var grSuppliers = @json($suppliersData);
    var grMaterialRequests = @json($mrsData);
    var grConfig = {
        poItemsUrl:   '{{ url("/goods-receipts/po-items") }}',
        mrItemsUrl:   '{{ url("/goods-receipts/mr-items") }}',
        scheduledUrl: '{{ route("goods-receipts.scheduledIncoming") }}',
        today:        '{{ date("Y-m-d") }}',
        selectedMrId: '{{ old("material_request_id", $selectedMrId ?? "") }}',
        selectedPoId: '{{ old("purchase_order_id", $selectedPoId ?? "") }}'
    };
    </script>
    <script src="{{ asset('js/goods-receipt-create.js') }}?v={{ filemtime(public_path('js/goods-receipt-create.js')) }}"></script>
    <script>
    // Safeguard tambahan untuk memastikan input Qty alat adalah bilangan bulat (integer >= 1)
    document.addEventListener('input', function(e) {
        if (e.target && e.target.classList.contains('qty-input')) {
            var row = e.target.closest('tr');
            var isTool = row && row.getAttribute('data-type') === 'tool';
            if (isTool) {
                e.target.min = "1";
                e.target.step = "1";
            } else {
                e.target.min = "0.01";
                e.target.step = "any";
            }
        }
    }, true);
    </script>
    @endpush
</x-app-layout>

