<x-app-layout>
    <x-slot name="title">Catat Penerimaan Barang Masuk</x-slot>

    @push('styles')
    <style>
        :root {
            --arsikon-amber: #ea580c;
            --arsikon-amber-hover: #c2410c;
            --arsikon-amber-soft: #fff7ed;
            --arsikon-amber-border: #fed7aa;
            --arsikon-navy: #1e3a8a;
            --arsikon-navy-hover: #1e40af;
            --arsikon-navy-soft: #eff6ff;
            --arsikon-navy-border: #bfdbfe;
            --arsikon-emerald: #059669;
            --arsikon-emerald-soft: #ecfdf5;
            --arsikon-slate-dark: #0f172a;
            --arsikon-slate-muted: #64748b;
            --arsikon-border: #e2e8f0;
            --arsikon-border-focus: #cbd5e1;
        }

        .receipt-page-container {
            display: flex;
            flex-direction: column;
            gap: 16px;
            width: 100%;
        }

        /* ── Header Strip (Open, Breathable, No Boxed Card Fatigue) ── */
        .receipt-header-strip {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            padding: 2px 2px;
        }

        .receipt-breadcrumb {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: var(--arsikon-slate-muted);
            margin-bottom: 4px;
        }

        .receipt-breadcrumb a {
            color: var(--arsikon-navy);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.15s ease;
        }

        .receipt-breadcrumb a:hover {
            color: var(--arsikon-amber);
        }

        .receipt-breadcrumb .active-crumb {
            color: var(--arsikon-slate-dark);
            font-weight: 600;
        }

        .receipt-headline {
            font-size: 18.5px;
            font-weight: 700;
            color: var(--arsikon-slate-dark);
            letter-spacing: -0.3px;
            margin: 0;
            line-height: 1.25;
        }

        .receipt-subheadline {
            font-size: 12.5px;
            color: var(--arsikon-slate-muted);
            margin: 3px 0 0 0;
            line-height: 1.45;
        }

        .btn-erp-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12.5px;
            font-weight: 600;
            color: #475569;
            background: #ffffff;
            border: 1px solid var(--arsikon-border);
            padding: 7px 14px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.15s ease;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .btn-erp-back:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: var(--arsikon-slate-dark);
        }

        /* ── Unified Form Card ── */
        .erp-card {
            background: #ffffff;
            border: 1px solid var(--arsikon-border);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
            overflow: hidden;
        }

        .erp-card-header {
            padding: 13px 20px;
            border-bottom: 1px solid var(--arsikon-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            background: #ffffff;
        }

        .erp-card-title-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .erp-icon-badge {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: var(--arsikon-navy-soft);
            border: 1px solid var(--arsikon-navy-border);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .erp-card-title {
            font-size: 13.5px;
            font-weight: 700;
            color: var(--arsikon-slate-dark);
            margin: 0;
            line-height: 1.3;
        }

        .erp-card-desc {
            font-size: 11.5px;
            color: var(--arsikon-slate-muted);
            margin: 2px 0 0 0;
            line-height: 1.35;
        }

        .erp-status-pill {
            font-size: 10.5px;
            font-weight: 700;
            color: #475569;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            padding: 3px 9px;
            border-radius: 6px;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .erp-card-body {
            padding: 20px;
            background: #ffffff;
        }

        /* ── Balanced Responsive Form Grid ── */
        .form-row-3col {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .form-row-grid2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-row-2col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .form-subcol {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .form-field {
            display: flex;
            flex-direction: column;
        }

        .erp-label {
            font-size: 12px !important;
            font-weight: 600 !important;
            color: #334155 !important;
            margin-bottom: 6px !important;
            line-height: 1.3 !important;
        }

        .label-with-badge {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 6px;
        }

        .required-star {
            color: #ef4444;
            font-weight: 700;
        }

        .recommend-pill {
            font-size: 10.5px;
            font-weight: 600;
            color: #0369a1;
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            padding: 1px 7px;
            border-radius: 5px;
        }

        .lock-pill {
            font-size: 10px;
            font-weight: 700;
            color: #92400e;
            background: var(--arsikon-amber-soft);
            border: 1px solid var(--arsikon-amber-border);
            padding: 2px 7px;
            border-radius: 5px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        /* ── Form Section Divider ── */
        .erp-section-divider {
            position: relative;
            text-align: left;
            margin: 18px 0 16px 0;
            display: flex;
            align-items: center;
        }

        .erp-section-divider::before {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            top: 50%;
            height: 1px;
            background: #f1f5f9;
            z-index: 1;
        }

        .erp-section-divider .divider-text {
            position: relative;
            z-index: 2;
            background: #ffffff;
            padding-right: 12px;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            color: #94a3b8;
        }

        /* ── Input Lens & Precision Alignment (Zero Overlap Guaranteed) ── */
        .input-icon-wrap {
            position: relative;
            width: 100%;
        }

        .input-icon-wrap .input-icon {
            position: absolute !important;
            left: 13px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            width: 15px !important;
            height: 15px !important;
            color: #94a3b8 !important;
            pointer-events: none !important;
            z-index: 5 !important;
            flex-shrink: 0 !important;
        }

        .input-icon-wrap .form-control,
        .input-icon-wrap input.form-control,
        .input-icon-wrap select.form-control {
            height: 40px !important;
            padding-top: 8px !important;
            padding-bottom: 8px !important;
            padding-right: 14px !important;
            padding-left: 42px !important;
            font-size: 13px !important;
            color: var(--arsikon-slate-dark) !important;
            background-color: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
            box-sizing: border-box !important;
            width: 100% !important;
        }

        /* Custom SVG Chevron Arrow for Sleek Enterprise Selects */
        .input-icon-wrap select.form-control {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: right 12px center !important;
            background-size: 14px 14px !important;
            padding-right: 36px !important;
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            cursor: pointer !important;
        }

        .input-icon-wrap .form-control:focus,
        .input-icon-wrap input.form-control:focus,
        .input-icon-wrap select.form-control:focus {
            border-color: var(--arsikon-amber) !important;
            box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.12) !important;
            outline: none !important;
        }

        /* ── Textarea Styling ── */
        .textarea-icon-wrap {
            position: relative;
            width: 100%;
        }

        .textarea-icon-wrap .textarea-icon {
            position: absolute !important;
            left: 13px !important;
            top: 13px !important;
            width: 15px !important;
            height: 15px !important;
            color: #94a3b8 !important;
            pointer-events: none !important;
            z-index: 5 !important;
            flex-shrink: 0 !important;
        }

        .textarea-icon-wrap textarea.form-control {
            padding-top: 10px !important;
            padding-bottom: 10px !important;
            padding-right: 14px !important;
            padding-left: 42px !important;
            min-height: 82px !important;
            font-size: 13px !important;
            color: var(--arsikon-slate-dark) !important;
            background-color: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            resize: vertical !important;
            transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
            box-sizing: border-box !important;
            width: 100% !important;
        }

        .textarea-icon-wrap textarea.form-control:focus {
            border-color: var(--arsikon-amber) !important;
            box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.12) !important;
            outline: none !important;
        }

        /* ── Modern File Dropzone Component ── */
        .erp-file-box {
            position: relative;
            width: 100%;
        }

        .erp-file-input {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 10;
        }

        .erp-file-dropzone {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 14px;
            border: 1.5px dashed #cbd5e1;
            border-radius: 8px;
            background: #f8fafc;
            transition: all 0.18s ease;
            cursor: pointer;
            min-height: 52px;
            box-sizing: border-box;
        }

        .erp-file-box:hover .erp-file-dropzone {
            border-color: var(--arsikon-amber);
            background: var(--arsikon-amber-soft);
        }

        .erp-file-icon-box {
            width: 32px;
            height: 32px;
            border-radius: 7px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            flex-shrink: 0;
            transition: all 0.18s ease;
        }

        .erp-file-box:hover .erp-file-icon-box {
            color: var(--arsikon-amber);
            border-color: var(--arsikon-amber-border);
        }

        .erp-file-texts {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .erp-file-primary-text {
            font-size: 12.5px;
            font-weight: 600;
            color: #1e293b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .erp-file-secondary-text {
            font-size: 11px;
            color: #64748b;
        }

        /* ── Table & Items Section ── */
        .items-card-header {
            padding: 12px 20px;
            border-bottom: 1px solid var(--arsikon-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            background: #ffffff;
        }

        .items-header-left {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .items-header-title-wrap {
            display: flex;
            flex-direction: column;
        }

        .items-header-title {
            font-size: 13.5px;
            font-weight: 700;
            color: var(--arsikon-slate-dark);
            letter-spacing: -0.2px;
        }

        .items-header-sub {
            font-size: 11.5px;
            color: var(--arsikon-slate-muted);
        }

        .receipt-tabs-wrap {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            padding: 3px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }

        .btn-tab-filter {
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 11.5px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid transparent;
            background: transparent;
            color: #475569;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
        }

        .btn-tab-filter:hover {
            background: rgba(255, 255, 255, 0.8);
            color: var(--arsikon-slate-dark);
        }

        .btn-tab-filter.active {
            color: #ffffff !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        #tab-btn-material.active {
            background: var(--arsikon-navy) !important;
            border-color: var(--arsikon-navy);
        }

        #tab-btn-tool.active {
            background: var(--arsikon-amber) !important;
            border-color: var(--arsikon-amber);
        }

        .btn-tab-filter .count-badge {
            background: #e2e8f0;
            color: #475569;
            padding: 1px 6px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 700;
        }

        .btn-tab-filter.active .count-badge {
            background: rgba(255, 255, 255, 0.25) !important;
            color: #ffffff !important;
        }

        .items-header-right {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-add-primary {
            background: var(--arsikon-navy) !important;
            border: 1px solid var(--arsikon-navy) !important;
            color: #ffffff !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            padding: 7px 14px !important;
            border-radius: 8px !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 5px !important;
            cursor: pointer !important;
            transition: all 0.15s ease !important;
            box-shadow: 0 1px 3px rgba(30, 58, 138, 0.2) !important;
        }

        .btn-add-primary:hover {
            background: var(--arsikon-navy-hover) !important;
            box-shadow: 0 2px 6px rgba(30, 58, 138, 0.3) !important;
        }

        .btn-add-tool {
            background: #ffffff !important;
            border: 1px solid #d97706 !important;
            color: #b45309 !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            padding: 7px 14px !important;
            border-radius: 8px !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 5px !important;
            cursor: pointer !important;
            transition: all 0.15s ease !important;
        }

        .btn-add-tool:hover {
            background: var(--arsikon-amber-soft) !important;
            color: var(--arsikon-amber) !important;
        }

        /* ── Table Styling ── */
        .receipt-items-table {
            border-collapse: separate !important;
            border-spacing: 0 !important;
            width: 100% !important;
        }

        .receipt-items-table th {
            padding: 10px 12px !important;
            font-size: 10.5px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            color: #475569 !important;
            background: #f8fafc !important;
            border-bottom: 2px solid var(--arsikon-border) !important;
            white-space: nowrap !important;
        }

        .receipt-items-table td {
            padding: 7px 10px !important;
            vertical-align: middle !important;
            border-bottom: 1px solid #f1f5f9 !important;
        }

        .receipt-items-table tbody tr.item-row {
            transition: background-color 0.15s ease !important;
        }

        .receipt-items-table tbody tr.item-row:hover {
            background-color: #fafbfd !important;
        }

        .receipt-items-table .form-control {
            height: 36px !important;
            padding: 6px 10px !important;
            font-size: 12.5px !important;
            border-radius: 6px !important;
            border: 1px solid #cbd5e1 !important;
            color: var(--arsikon-slate-dark) !important;
            background-color: #ffffff !important;
            transition: all 0.15s ease !important;
            box-sizing: border-box !important;
        }

        .receipt-items-table .form-control:focus {
            border-color: var(--arsikon-amber) !important;
            box-shadow: 0 0 0 2px rgba(234, 88, 12, 0.15) !important;
            outline: none !important;
        }

        .receipt-items-table .qty-shipped-input {
            background-color: #f8fafc !important;
            border: 1px solid #cbd5e1 !important;
            color: #334155 !important;
            font-weight: 700 !important;
            font-size: 13px !important;
            border-radius: 6px !important;
        }

        .receipt-items-table .qty-input {
            border: 1.5px solid #93c5fa !important;
            background-color: #ffffff !important;
            font-weight: 800 !important;
            font-size: 13.5px !important;
            color: var(--arsikon-navy) !important;
            border-radius: 6px !important;
        }

        .receipt-items-table .qty-input:focus {
            border-color: var(--arsikon-amber) !important;
            box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.16) !important;
        }

        .btn-delete-row {
            width: 30px !important;
            height: 30px !important;
            padding: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 6px !important;
            color: #ef4444 !important;
            background: #fef2f2 !important;
            border: 1px solid #fee2e2 !important;
            transition: all 0.15s ease !important;
            cursor: pointer !important;
            font-size: 12px !important;
        }

        .btn-delete-row:hover {
            background: #fee2e2 !important;
            border-color: #fca5a5 !important;
            color: #dc2626 !important;
            transform: scale(1.05) !important;
        }

        /* ── Empty State ── */
        .empty-receipt-canvas {
            padding: 38px 20px !important;
            text-align: center !important;
            background: #fafbfd !important;
            border: none !important;
        }

        .empty-receipt-card {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: center !important;
            max-width: 480px !important;
            margin: 0 auto !important;
            padding: 24px !important;
            border: 1.5px dashed #cbd5e1 !important;
            border-radius: 12px !important;
            background: #ffffff !important;
        }

        .empty-icon-circle {
            width: 48px !important;
            height: 48px !important;
            border-radius: 12px !important;
            background: var(--arsikon-navy-soft) !important;
            border: 1px solid var(--arsikon-navy-border) !important;
            color: var(--arsikon-navy) !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 20px !important;
            margin-bottom: 10px !important;
        }

        .empty-title {
            font-weight: 700 !important;
            color: var(--arsikon-slate-dark) !important;
            font-size: 14px !important;
            margin-bottom: 3px !important;
        }

        .empty-desc {
            font-size: 12px !important;
            color: var(--arsikon-slate-muted) !important;
            line-height: 1.45 !important;
            margin-bottom: 16px !important;
            text-align: center !important;
        }

        .empty-actions-primary {
            display: flex !important;
            gap: 8px !important;
            justify-content: center !important;
            flex-wrap: wrap !important;
        }

        /* ── Summary Strip ── */
        .receipt-summary-strip {
            padding: 10px 20px !important;
            background: #f8fafc !important;
            border-top: 1px solid var(--arsikon-border) !important;
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            flex-wrap: wrap !important;
            gap: 10px !important;
        }

        .summary-count-group {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .summary-label {
            font-size: 12px;
            color: var(--arsikon-slate-muted);
            font-weight: 600;
        }

        .summary-badge {
            font-size: 11px;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .badge-all {
            background: var(--arsikon-slate-dark);
            color: #ffffff;
        }

        .badge-material {
            background: var(--arsikon-navy-soft);
            color: var(--arsikon-navy);
            border: 1px solid var(--arsikon-navy-border);
        }

        .badge-tool {
            background: var(--arsikon-amber-soft);
            color: #b45309;
            border: 1px solid var(--arsikon-amber-border);
        }

        /* ── Submit Bar Card ── */
        .submit-bar-inner {
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
            background: #ffffff;
        }

        .submit-note {
            font-size: 12px;
            color: var(--arsikon-slate-muted);
            display: flex;
            align-items: center;
            gap: 8px;
            line-height: 1.4;
        }

        .submit-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-erp-secondary {
            font-size: 12.5px !important;
            font-weight: 600 !important;
            padding: 9px 18px !important;
            border-radius: 8px !important;
            border: 1px solid #cbd5e1 !important;
            background: #ffffff !important;
            color: #475569 !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            text-decoration: none !important;
            transition: all 0.15s ease !important;
        }

        .btn-erp-secondary:hover {
            background: #f8fafc !important;
            color: var(--arsikon-slate-dark) !important;
            border-color: #94a3b8 !important;
        }

        .btn-erp-primary-amber {
            background: linear-gradient(135deg, var(--arsikon-amber) 0%, #c2410c 100%) !important;
            border: none !important;
            color: #ffffff !important;
            font-size: 13px !important;
            font-weight: 700 !important;
            padding: 9px 24px !important;
            border-radius: 8px !important;
            box-shadow: 0 2px 8px rgba(234, 88, 12, 0.25) !important;
            cursor: pointer !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            transition: all 0.15s ease !important;
        }

        .btn-erp-primary-amber:hover {
            background: linear-gradient(135deg, #c2410c 0%, #9a3412 100%) !important;
            box-shadow: 0 4px 12px rgba(234, 88, 12, 0.35) !important;
            transform: translateY(-1px);
        }

        /* ── Modal Styles ── */
        .scheduled-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
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
            max-width: 680px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.2);
            overflow: hidden;
            border: 1px solid var(--arsikon-border);
        }

        .scheduled-modal-header {
            padding: 14px 20px;
            border-bottom: 1px solid var(--arsikon-border);
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
            padding: 12px 20px;
            border-top: 1px solid var(--arsikon-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
        }

        .mobile-label-tag {
            display: none;
        }

        @media (max-width: 992px) {
            .form-row-3col {
                grid-template-columns: 1fr;
            }
            .form-row-grid2 {
                grid-template-columns: 1fr;
            }
            .form-row-2col {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .items-card-header {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 10px !important;
            }

            .items-header-left {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 8px !important;
            }

            .receipt-tabs-wrap {
                width: 100% !important;
                display: flex !important;
            }

            .receipt-tabs-wrap .btn-tab-filter {
                flex: 1 1 0% !important;
                justify-content: center !important;
            }

            .items-header-right {
                width: 100% !important;
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
            }

            .items-header-right .btn-add-primary {
                grid-column: 1 / -1 !important;
                justify-content: center !important;
            }

            .table-wrap {
                overflow-x: visible !important;
            }

            .receipt-items-table {
                display: block !important;
                width: 100% !important;
            }

            .receipt-items-table thead {
                display: none !important;
            }

            .receipt-items-table tbody {
                display: flex !important;
                flex-direction: column !important;
                gap: 10px !important;
                padding: 10px !important;
                background: #f8fafc !important;
            }

            .receipt-items-table tr.item-row {
                display: grid !important;
                grid-template-columns: 1fr 1fr auto !important;
                grid-template-areas:
                    "type    sku     action"
                    "cat     subgroup subgroup"
                    "item    item    item"
                    "brand   unit    unit"
                    "shipped qty     diff"
                    "notes   notes   notes";
                gap: 8px 10px !important;
                padding: 12px !important;
                background: #ffffff !important;
                border: 1.5px solid #e2e8f0 !important;
                border-radius: 10px !important;
            }

            .receipt-items-table td.col-type { grid-area: type; padding: 0 !important; border: none !important; }
            .receipt-items-table td.col-sku { grid-area: sku; padding: 0 !important; border: none !important; }
            .receipt-items-table td.col-action { grid-area: action; padding: 0 !important; border: none !important; justify-content: flex-end !important; }
            .receipt-items-table td.col-cat { grid-area: cat; padding: 0 !important; border: none !important; }
            .receipt-items-table td.col-subgroup { grid-area: subgroup; padding: 0 !important; border: none !important; }
            .receipt-items-table td.col-item { grid-area: item; padding: 0 !important; border: none !important; }
            .receipt-items-table td.col-brand { grid-area: brand; padding: 0 !important; border: none !important; }
            .receipt-items-table td.col-shipped { grid-area: shipped; padding: 0 !important; border: none !important; }
            .receipt-items-table td.col-qty { grid-area: qty; padding: 0 !important; border: none !important; }
            .receipt-items-table td.col-diff { grid-area: diff; padding: 0 !important; border: none !important; }
            .receipt-items-table td.col-unit { grid-area: unit; padding: 0 !important; border: none !important; }
            .receipt-items-table td.col-notes { grid-area: notes; padding: 0 !important; border: none !important; }

            .mobile-label-tag {
                display: block !important;
                font-size: 10px !important;
                font-weight: 700 !important;
                color: #64748b !important;
                margin-bottom: 3px !important;
                text-transform: uppercase !important;
            }

            .submit-bar-inner {
                flex-direction: column-reverse !important;
                align-items: stretch !important;
                padding: 12px 14px !important;
            }

            .submit-actions {
                display: flex !important;
                flex-direction: column-reverse !important;
                width: 100% !important;
            }

            .submit-actions .btn-erp-secondary,
            .submit-actions .btn-erp-primary-amber {
                width: 100% !important;
                text-align: center !important;
                justify-content: center !important;
            }
        }
    </style>
    @endpush

    {{-- Breadcrumb & Clean Header --}}
    <div class="receipt-header-strip">
        <div>
            <div class="receipt-breadcrumb">
                <a href="{{ route('goods-receipts.index') }}">Penerimaan Barang</a>
                <x-lucide-chevron-right style="width:12px;height:12px;color:#94a3b8;" />
                <span class="active-crumb">Catat Penerimaan Baru</span>
            </div>
            <h1 class="receipt-headline">Penerimaan Barang Fisik (Gudang)</h1>
            <p class="receipt-subheadline">Pencatatan kedatangan fisik material dan alat kerja ke gudang untuk menyelaraskan stok gudang secara otomatis.</p>
        </div>
        <div>
            <a href="{{ route('goods-receipts.index') }}" class="btn-erp-back">
                <x-lucide-arrow-left style="width:13px;height:13px;" /> Kembali ke Riwayat
            </a>
        </div>
    </div>

    @if (isset($errors) && $errors->any())
    <div class="alert alert-danger mb-3" style="border-radius:10px;padding:12px 16px;font-size:13px;display:flex;align-items:flex-start;gap:10px;">
        <x-lucide-circle-alert style="width:18px;height:18px;flex-shrink:0;color:#dc2626;margin-top:2px;" />
        <div>
            <strong style="display:block;margin-bottom:4px;color:#991b1b;">Terdapat kesalahan pengisian data:</strong>
            <ul style="margin:0;padding-left:18px;line-height:1.5;color:#b91c1c;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    @php
        $canAccessPo = true;
    @endphp

    @if(isset($inTransitDistributions) && $inTransitDistributions->isNotEmpty())
    <div class="card mb-3" style="border:1.5px solid var(--arsikon-navy-border);border-radius:12px;background:linear-gradient(135deg, #eff6ff 0%, #f0fdf4 100%);box-shadow:0 2px 6px rgba(37,99,235,0.05);overflow:hidden;">
        <div class="card-body" style="padding:14px 18px;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                <div style="display:flex;align-items:flex-start;gap:12px;">
                    <div style="width:36px;height:36px;border-radius:9px;background:var(--arsikon-navy);color:#ffffff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <x-lucide-truck style="width:17px;height:17px;" />
                    </div>
                    <div>
                        <h4 style="font-size:13.5px;font-weight:700;color:var(--arsikon-navy);margin:0 0 3px 0;">
                            @if($canAccessPo)
                                Barang PO Lapangan Sedang Dikirim dari Gudang Pusat (Surat Jalan Dalam Perjalanan)
                            @else
                                Surat Jalan Pengiriman Sedang Dalam Perjalanan dari Gudang Pusat
                            @endif
                        </h4>
                        <p style="font-size:12px;color:#334155;margin:0;line-height:1.45;">
                            Terdapat <strong>{{ $inTransitDistributions->count() }} Surat Jalan</strong> yang sedang menuju ke gudang proyek Anda. Konfirmasikan penerimaan langsung jika fisik tiba:
                        </p>
                    </div>
                </div>
            </div>
            <div style="margin-top:10px;display:flex;flex-direction:column;gap:8px;">
                @foreach($inTransitDistributions as $dist)
                <div style="background:#ffffff;border:1px solid #dbeafe;border-radius:8px;padding:9px 14px;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;">
                    <div style="font-size:12px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <span class="badge" style="background:#eff6ff;color:#1d4ed8;font-weight:700;border:1px solid #bfdbfe;padding:4px 8px;border-radius:6px;">
                            #{{ $dist->distribution_number }}
                        </span>
                        @if($dist->materialRequest)
                            <span style="color:#64748b;">• {{ $canAccessPo ? 'PO Lapangan:' : 'Permintaan Proyek:' }}</span>
                            <strong style="color:#0f172a;">#{{ $dist->materialRequest->request_number }}</strong>
                        @endif
                        <span style="color:#64748b;">• Tujuan: <strong style="color:#0f172a;">{{ $dist->toWarehouse?->name }}</strong></span>
                        <span style="color:#64748b;">• Supir: {{ $dist->driver_name ?? '-' }} ({{ $dist->vehicle_number ?? '-' }})</span>
                    </div>
                    <a href="{{ route('distributions.show', $dist) }}" class="btn btn-sm btn-primary" style="font-size:11.5px;font-weight:700;padding:5px 12px;border-radius:6px;background:var(--arsikon-navy);border-color:var(--arsikon-navy);">
                        <x-lucide-boxes class="me-1" style="width:13px;height:13px;" /> Terima Surat Jalan Ini
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('goods-receipts.store') }}" id="receiptForm" enctype="multipart/form-data">
        @csrf
        <div class="receipt-page-container">

            {{-- ── 1. Bagian Atas: Form Sesi Penerimaan (Clean Unified Layout) ── --}}
            <div class="erp-card session-card">
                <div class="erp-card-header">
                    <div class="erp-card-title-group">
                        <div class="erp-icon-badge">
                            <x-lucide-clipboard-check style="width:16px;height:16px;color:var(--arsikon-navy);" />
                        </div>
                        <div>
                            <h3 class="erp-card-title">Informasi Dokumen &amp; Surat Jalan</h3>
                            <p class="erp-card-desc">Lengkapi parameter verifikasi dokumen: gudang tujuan, pemasok, tanggal &amp; surat jalan</p>
                        </div>
                    </div>
                    <div>
                        <span class="erp-status-pill">Draft Penerimaan</span>
                    </div>
                </div>

                <div class="erp-card-body">
                    {{-- Row 1: 3 Balanced Columns (Waktu, Gudang, Petugas) --}}
                    <div class="form-row-3col">
                        {{-- Tanggal Terima --}}
                        <div class="form-field">
                            <label class="erp-label" for="receiptDateInput">
                                Tanggal Terima <span class="required-star">*</span>
                            </label>
                            <div class="input-icon-wrap">
                                <x-lucide-calendar-days class="input-icon" />
                                <input type="date" id="receiptDateInput" name="received_at"
                                    value="{{ old('received_at', date('Y-m-d')) }}"
                                    class="form-control" required onchange="onDateChanged()">
                            </div>
                        </div>

                        {{-- Gudang Tujuan --}}
                        <div class="form-field">
                            <div class="label-with-badge">
                                <label class="erp-label mb-0" for="warehouseSelect">
                                    Gudang Tujuan <span class="required-star">*</span>
                                </label>
                                <span id="warehouseLockBadge" class="lock-pill" style="display:none;">
                                    <x-lucide-lock style="width:10px;height:10px;" /> Terkunci Sesuai Tahapan
                                </span>
                            </div>
                            <div class="input-icon-wrap">
                                <x-lucide-warehouse class="input-icon" id="warehouseInputIcon" />
                                <select name="warehouse_id" id="warehouseSelect" class="form-control @error('warehouse_id') is-invalid @enderror" required onchange="onUserWarehouseChange(this)">
                                    @if($warehouses->count() > 1)
                                        <option value="">— Pilih Gudang —</option>
                                    @endif
                                    @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ ($warehouses->count() === 1 || old('warehouse_id') == $wh->id || (empty(old('warehouse_id')) && (session('active_warehouse_id') == $wh->id || $loop->first))) ? 'selected' : '' }}>
                                        {{ $wh->name }} {{ $wh->is_central ? '(Pusat)' : '(Proyek)' }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div id="warehouseAutoHint" style="font-size:11px;margin-top:4px;display:none;"></div>
                            @error('warehouse_id')<div class="invalid-feedback d-block" style="font-size:11px;">{{ $message }}</div>@enderror
                        </div>

                        {{-- Nama Penerima Barang --}}
                        <div class="form-field">
                            <label class="erp-label" for="receivedByNameInput">
                                Nama Penerima Barang
                            </label>
                            <div class="input-icon-wrap">
                                <x-lucide-user-check class="input-icon" />
                                <input type="text" name="received_by_name"
                                    id="receivedByNameInput"
                                    value="{{ old('received_by_name', auth()->user()->name) }}"
                                    class="form-control"
                                    placeholder="Nama petugas penerima...">
                            </div>
                        </div>
                    </div>

                    {{-- Elegant Horizontal Divider with Section Label --}}
                    <div class="erp-section-divider">
                        <span class="divider-text">Dokumen Pengiriman &amp; Pemasok</span>
                    </div>

                    {{-- Row 2: 2 Balanced Sub-Columns (PO & Vendor vs. Foto & Catatan) --}}
                    <div class="form-row-grid2">
                        {{-- Left Sub-Column: Hubungkan PO, Supplier, No Surat Jalan --}}
                        <div class="form-subcol">
                            {{-- Hubungkan PO Lapangan & Kiriman Vendor --}}
                            <div class="form-field mb-3">
                                <div class="label-with-badge">
                                    <label class="erp-label mb-0" for="vendorDeliverySelect">
                                        Hubungkan PO Lapangan &amp; Kiriman Vendor
                                    </label>
                                    <span class="recommend-pill">1-Klik Otomatis</span>
                                </div>
                                <div class="input-icon-wrap">
                                    <x-lucide-truck class="input-icon" style="color:#059669;" />
                                    <select id="vendorDeliverySelect" class="form-control" style="font-weight:600;" onchange="onVendorDeliverySelect(this)">
                                        <option value="">— Pilih Vendor Pengirim yang Sedang Tiba di Gudang (1 Klik) —</option>
                                        @foreach($incomingVendorDeliveries as $delivery)
                                        @php
                                            $isDelivSelected = (old('material_request_id', $selectedMrId ?? '') == ($delivery['mr_id'] ?? '') && (empty($selectedPoId) || $selectedPoId == ($delivery['po_id'] ?? '')));
                                        @endphp
                                        <option value="{{ $delivery['id'] }}"
                                            data-po-id="{{ $delivery['po_id'] ?? '' }}"
                                            data-po-number="{{ $delivery['po_number'] ?? '' }}"
                                            data-mr-id="{{ $delivery['mr_id'] ?? '' }}"
                                            data-mr-number="{{ $delivery['mr_number'] ?? '' }}"
                                            data-supplier-id="{{ $delivery['supplier_id'] ?? '' }}"
                                            data-supplier-name="{{ $delivery['supplier_name'] ?? '' }}"
                                            data-warehouse-id="{{ $delivery['warehouse_id'] ?? '' }}"
                                            data-project="{{ $delivery['project_name'] ?? '' }}"
                                            {{ $isDelivSelected ? 'selected' : '' }}>
                                            {{ $delivery['label'] }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="vendorDeliveryBanner" class="mt-2" style="display:none;padding:8px 12px;background:#f0fdf4;border-radius:8px;border:1px solid #bbf7d0;font-size:11.5px;color:#166534;line-height:1.5;">
                                    <div style="display:flex;align-items:flex-start;gap:6px;">
                                        <x-lucide-circle-check style="width:14px;height:14px;color:#16a34a;flex-shrink:0;margin-top:2px;" />
                                        <span id="vendorDeliveryBannerText"></span>
                                    </div>
                                </div>
                                <div style="display:none;">
                                    <span>Hubungkan Purchase Order (PO)</span>
                                </div>
                            </div>

                            {{-- Hidden inputs to bind Hubungkan PO Lapangan dan Hubungkan Purchase Order (PO) --}}
                            <input type="hidden" id="mrSelect" name="material_request_id" value="{{ old('material_request_id', $selectedMrId ?? '') }}">
                            <input type="hidden" id="poSelect" name="purchase_order_id" value="{{ old('purchase_order_id', $selectedPoId ?? '') }}">

                            {{-- Grid 2 col for Supplier & Surat Jalan --}}
                            <div class="form-row-2col">
                                {{-- Nama Pemasok / Supplier --}}
                                <div class="form-field">
                                    <label class="erp-label" for="supplierNameInput">
                                        Nama Pemasok / Supplier <span class="required-star">*</span>
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
                                    <div id="supplierHint" style="font-size:10.5px;margin-top:4px;color:#64748b;">
                                        Terisi otomatis dari PO atau ketik vendor manual
                                    </div>
                                    @error('supplier_id')<div class="invalid-feedback d-block" style="font-size:11px;">{{ $message }}</div>@enderror
                                </div>

                                {{-- No. Surat Jalan / Nota Fisik Vendor --}}
                                <div class="form-field">
                                    <label class="erp-label">
                                        No. Surat Jalan / Nota Fisik
                                    </label>
                                    <div class="input-icon-wrap">
                                        <x-lucide-hash class="input-icon" />
                                        <input type="text" name="invoice_number" value="{{ old('invoice_number') }}"
                                            class="form-control" placeholder="Contoh: SJ-2026/09/001">
                                    </div>
                                    <div style="font-size:10.5px;margin-top:4px;color:#64748b;">
                                        Nomor nota fisik dari supir / vendor
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Right Sub-Column: Bukti Fisik & Catatan --}}
                        <div class="form-subcol">
                            {{-- Foto / Scan Surat Jalan Vendor --}}
                            <div class="form-field mb-3">
                                <label class="erp-label">
                                    Foto / Scan Surat Jalan Vendor (Bukti Fisik)
                                </label>
                                <div class="erp-file-box">
                                    <input type="file" name="attachment" id="receiptAttachmentInput" accept="image/*,application/pdf" class="erp-file-input" onchange="onFileAttachmentChange(this)">
                                    <div class="erp-file-dropzone" id="fileDropzoneBox">
                                        <div class="erp-file-icon-box">
                                            <x-lucide-upload-cloud style="width:16px;height:16px;" />
                                        </div>
                                        <div class="erp-file-texts">
                                            <span class="erp-file-primary-text" id="erpFileTitle">Pilih Foto / Scan Surat Jalan</span>
                                            <span class="erp-file-secondary-text" id="erpFileSubtitle">JPG, PNG, atau PDF (Maks. 10MB)</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Catatan Penerimaan --}}
                            <div class="form-field">
                                <label class="erp-label">Catatan Penerimaan</label>
                                <div class="textarea-icon-wrap">
                                    <x-lucide-sticky-note class="textarea-icon" />
                                    <textarea name="notes" class="form-control" rows="2"
                                        placeholder="Keterangan pengiriman, supir, plat nomor armada...">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── 2. Bagian Tengah: Rincian Barang & Alat Masuk (Full Width) ── --}}
            <div class="erp-card items-card-container">
                <div class="items-card-header">
                    <div class="items-header-left">
                        <div class="items-header-title-wrap">
                            <span class="items-header-title">Rincian Barang &amp; Alat Diterima</span>
                            <span class="items-header-sub">Verifikasi fisik kuantitas barang sesuai nota yang tiba di gudang</span>
                        </div>

                        {{-- Filter Tabs (Material sendiri & Alat sendiri) --}}
                        <div class="receipt-tabs-wrap">
                            <button type="button" class="btn-tab-filter" id="tab-btn-material" onclick="filterItemsTab('material')">
                                <x-lucide-package style="width:13px;height:13px;" /> Material <span class="count-badge" id="count-material">0</span>
                            </button>
                            <button type="button" class="btn-tab-filter" id="tab-btn-tool" onclick="filterItemsTab('tool')">
                                <x-lucide-hard-hat style="width:13px;height:13px;" /> Alat <span class="count-badge" id="count-tool">0</span>
                            </button>
                        </div>
                    </div>

                    {{-- Tombol Tambah --}}
                    <div class="items-header-right">
                        <button type="button" class="btn-add-primary" id="btnTambahBarisDirect" onclick="addRow('material')">
                            <x-lucide-plus style="width:14px;height:14px;" /> Tambah Baris Barang
                        </button>
                        <button type="button" class="btn-add-tool" id="btnAddTool" onclick="addRow('tool')">
                            <x-lucide-wrench style="width:14px;height:14px;" /> + Alat
                        </button>
                        <button type="button" id="btnAddMaterial" onclick="addRow('material')" style="display:none;">Material</button>
                    </div>
                </div>

                <div class="table-wrap" style="overflow-x:auto;">
                    <table class="data-table receipt-items-table mb-0" id="itemsTable" style="min-width:1160px;width:100%;">
                        <thead>
                            <tr>
                                <th style="width:75px;text-align:center;">Tipe</th>
                                <th style="width:115px;text-align:center;">Kode Material</th>
                                <th style="width:130px;">Kategori <span class="text-danger">*</span></th>
                                <th style="width:130px;">Kelompok <span class="text-danger">*</span></th>
                                <th style="min-width:210px;">Nama Barang <span class="text-danger">*</span></th>
                                <th style="width:130px;">Ukuran / Spesifikasi</th>
                                <th style="width:125px;">Merek</th>
                                <th style="width:85px;text-align:center;">Satuan <span class="text-danger">*</span></th>
                                <th style="width:95px;text-align:center;" title="Kuantitas yang tertera pada surat jalan / nota vendor">Qty Nota / SJ</th>
                                <th style="width:105px;text-align:center;" title="Kuantitas fisik diterima baik yang akan menambah stok gudang">Diterima (Stok) <span class="text-danger">*</span></th>
                                <th style="width:160px;text-align:center;" title="Pilihan status jika kuantitas nota berbeda dengan fisik diterima: Belum Dikirim Semua (Susulan) atau Dinyatakan Hilang">Selisih &amp; Status</th>
                                <th style="min-width:120px;">Catatan Fisik</th>
                                <th style="width:40px;text-align:center;"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            {{-- Diisi secara dinamis oleh addRow() --}}
                        </tbody>
                    </table>
                </div>

                {{-- Total Summary Strip --}}
                <div class="receipt-summary-strip">
                    <div class="summary-count-group" id="totalItemCount">
                        <span class="summary-label">Total Muatan Masuk:</span>
                        <span class="summary-badge badge-all"><span id="count-all">0</span> Item</span>
                        <span class="summary-badge badge-material"><span id="count-material">0</span> Material</span>
                        <span class="summary-badge badge-tool"><span id="count-tool">0</span> Alat</span>
                    </div>
                </div>
            </div>

            {{-- ── 3. Bagian Bawah: Form Action Bar (Kembali & Simpan Draft) ── --}}
            <div class="erp-card">
                <div class="submit-bar-inner">
                    <div class="submit-note">
                        <x-lucide-shield-check style="width:17px;height:17px;color:#059669;flex-shrink:0;" />
                        <span>Data disimpan sebagai <strong>Draft</strong> sebelum diverifikasi dan disahkan ke stok inventori aktif.</span>
                    </div>
                    <div class="submit-actions">
                        <a href="{{ route('goods-receipts.index') }}" class="btn-erp-secondary">
                            <x-lucide-arrow-left style="width:13px;height:13px;" /> Kembali
                        </a>
                        <button type="submit" class="btn-erp-primary-amber">
                            <x-lucide-save style="width:14px;height:14px;" /> Simpan Penerimaan
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>

    {{-- ── Modal: Tarik dari Jadwal Kedatangan ── --}}
    <div class="scheduled-modal-overlay" id="scheduledModal">
        <div class="scheduled-modal-box">
            <div class="scheduled-modal-header">
                <div class="flex items-center gap-2">
                    <div style="width:32px;height:32px;border-radius:8px;background:var(--arsikon-navy-soft);border:1px solid var(--arsikon-navy-border);display:flex;align-items:center;justify-content:center;color:var(--arsikon-navy);">
                        <x-lucide-calendar-check style="width:16px;height:16px;" />
                    </div>
                    <div>
                        <h4 class="fw-700" style="font-size:14px;margin:0;color:#0f172a;">Jadwal Kedatangan Barang &amp; Alat</h4>
                        <p class="text-muted" style="font-size:11.5px;margin:1px 0 0 0;">Pilih item yang tiba untuk langsung dimasukkan tanpa ketik ulang.</p>
                    </div>
                </div>
                <button type="button" class="btn-close-modal" onclick="closeScheduledModal()" style="background:none;border:none;font-size:20px;color:#94a3b8;cursor:pointer;">&times;</button>
            </div>

            <div class="scheduled-modal-body">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:12px;flex-wrap:wrap;background:#f8fafc;padding:9px 14px;border-radius:8px;border:1px solid #e2e8f0;">
                    <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                        <button type="button" id="btnSchedAll" class="btn btn-sm btn-primary" onclick="setModalFilter('all')" style="font-size:11.5px;font-weight:700;border-radius:6px;padding:5px 12px;background:var(--arsikon-navy);border-color:var(--arsikon-navy);">
                            <x-lucide-list-checks class="me-1" style="width:13px;height:13px;" /> Semua Rencana (<span id="countSchedAll">0</span>)
                        </button>
                        <button type="button" id="btnSchedToday" class="btn btn-sm btn-light border" onclick="setModalFilter('today')" style="font-size:11.5px;font-weight:600;border-radius:6px;padding:5px 12px;">
                            <x-lucide-calendar-days class="me-1" style="width:13px;height:13px;" /> Hari Ini (<span id="countSchedToday">0</span>)
                        </button>
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span style="font-size:11px;color:#64748b;font-weight:600;">Tanggal:</span>
                        <input type="date" id="modalScheduleDate" class="form-control form-control-sm" style="font-size:11.5px;width:135px;height:32px;padding:2px 8px;border-radius:6px;" onchange="setModalFilter('date')">
                    </div>
                </div>

                <div id="scheduledItemsList">
                    <div class="text-center p-4 text-muted" style="font-size:13px;">
                        <x-lucide-loader-2 class="me-1 animate-spin" style="width:15px;height:15px;" /> Memuat daftar jadwal kedatangan...
                    </div>
                </div>
            </div>

            <div class="scheduled-modal-footer">
                <div>
                    <button type="button" class="btn btn-sm btn-light border" onclick="toggleSelectAllScheduled()" style="font-size:11.5px;padding:5px 12px;border-radius:6px;">
                        <x-lucide-square-check class="me-1" style="width:13px;height:13px;" /> Pilih / Batal Semua
                    </button>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="closeScheduledModal()" style="font-size:12px;border-radius:6px;">Batal</button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="applySelectedScheduledItems()" style="font-size:12px;font-weight:700;border-radius:6px;background:var(--arsikon-amber);border-color:var(--arsikon-amber);">
                        <x-lucide-check-check class="me-1" style="width:14px;height:14px;" /> Masukkan Barang Terpilih
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
    var grMaterialCategories = @json($materialCategories->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'code' => $c->code]));
    var grToolCategories     = @json($toolCategories->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'code' => $c->code]));
    window.grUnits = @json($unitsJson);
    window.grMaterialGroups = @json($existingMaterialGroups);
    window.grToolGroups = @json($existingToolGroups);
    window.grAllMaterialGroups = @json($allMaterialGroups);
    window.grAllToolGroups = @json($allToolGroups);
    var grUnits = window.grUnits;
    var grMaterialGroups = window.grMaterialGroups;
    var grToolGroups = window.grToolGroups;
    var grAllMaterialGroups = window.grAllMaterialGroups;
    var grAllToolGroups = window.grAllToolGroups;
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

    function onFileAttachmentChange(input) {
        var title = document.getElementById('erpFileTitle');
        var subtitle = document.getElementById('erpFileSubtitle');
        if (input.files && input.files.length > 0) {
            var file = input.files[0];
            if (title) title.textContent = file.name;
            if (subtitle) subtitle.textContent = (file.size / 1024).toFixed(1) + ' KB — File siap diunggah';
        } else {
            if (title) title.textContent = 'Pilih Foto / Scan Surat Jalan';
            if (subtitle) subtitle.textContent = 'JPG, PNG, atau PDF (Maks. 10MB)';
        }
    }
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
