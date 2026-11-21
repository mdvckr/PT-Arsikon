<style>
    /* ============================================================
       Proyek — shared UI system (index, create, edit)
       Mengikuti design system Arsikon: Inter, radius 14px,
       border #e2e8f0, aksen orange #ea580c / blue #2563eb.
       ============================================================ */

    .proj-page {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    /* ---------- Header ---------- */
    .proj-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .proj-header-main {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .proj-header-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: rgba(234, 88, 12, 0.12);
        color: #ea580c;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }

    .proj-header-title {
        font-size: 22px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
        margin: 0;
    }

    .proj-header-subtitle {
        font-size: 13px;
        color: #64748b;
        margin: 3px 0 0;
        line-height: 1.45;
    }

    .proj-header-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    .proj-btn-primary {
        padding: 9px 18px;
        font-weight: 700;
        border-radius: 9px;
    }

    /* ---------- Tabs ---------- */
    .proj-tabs {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 5px;
        background: #eef2f7;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        width: fit-content;
        max-width: 100%;
    }

    .proj-tab {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        text-decoration: none;
        transition: background 0.18s ease, color 0.18s ease, box-shadow 0.18s ease;
        white-space: nowrap;
    }

    .proj-tab:hover {
        background: #ffffff;
        color: #1e293b;
    }

    .proj-tab.is-active {
        background: #2563eb;
        color: #ffffff;
        font-weight: 700;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
    }

    .proj-tab-count {
        font-size: 11px;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 10px;
        background: #e2e8f0;
        color: #334155;
        line-height: 1.4;
    }

    .proj-tab.is-active .proj-tab-count {
        background: rgba(255, 255, 255, 0.22);
        color: #ffffff;
    }

    /* ---------- KPI ---------- */
    .proj-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .proj-kpi {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        text-decoration: none;
        transition: border-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
    }

    .proj-kpi:hover {
        border-color: #cbd5e1;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
        transform: translateY(-1px);
    }

    .proj-kpi.is-active {
        border-color: #93c5fd;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
    }

    .proj-kpi-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        flex-shrink: 0;
    }

    .proj-kpi-icon.is-total {
        background: rgba(37, 99, 235, 0.1);
        color: #2563eb;
    }

    .proj-kpi-icon.is-active {
        background: rgba(16, 185, 129, 0.12);
        color: #059669;
    }

    .proj-kpi-icon.is-planning {
        background: rgba(100, 116, 139, 0.12);
        color: #475569;
    }

    .proj-kpi-icon.is-completed {
        background: rgba(8, 145, 178, 0.12);
        color: #0891b2;
    }

    .proj-kpi-body {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
    }

    .proj-kpi-label {
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .proj-kpi-value {
        font-size: 22px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
        font-variant-numeric: tabular-nums;
    }

    /* ---------- Filter ---------- */
    .proj-filter-card {
        padding: 0;
    }

    .proj-filter-form {
        display: flex;
        align-items: flex-end;
        gap: 12px;
        flex-wrap: wrap;
        padding: 16px 20px;
    }

    .proj-field {
        display: flex;
        flex-direction: column;
        min-width: 160px;
    }

    .proj-field-search {
        flex: 2 1 260px;
    }

    .proj-filter-form .proj-field:not(.proj-field-search) {
        flex: 1 1 180px;
    }

    .proj-filter-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    .proj-filter-actions .btn {
        height: 38px;
        justify-content: center;
    }

    /* Input with icon (shared with form pages) */
    .input-icon-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }

    .input-icon-wrap .input-icon {
        position: absolute;
        left: 12px;
        color: #94a3b8;
        font-size: 14px;
        pointer-events: none;
        transition: color 0.2s;
    }

    .input-icon-wrap .form-control.with-icon {
        padding-left: 36px;
    }

    .input-icon-wrap:focus-within .input-icon {
        color: #2563eb;
    }

    /* ---------- Table ---------- */
    .proj-table-card {
        overflow: hidden;
    }

    .proj-table td {
        padding: 14px 16px;
        vertical-align: middle;
    }

    .proj-table th {
        padding: 12px 16px;
    }

    .proj-table td:last-child,
    .proj-th-actions {
        text-align: right;
    }

    .proj-identity {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        min-width: 260px;
    }

    .proj-identity-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: rgba(37, 99, 235, 0.1);
        color: #2563eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        flex-shrink: 0;
    }

    .proj-identity-body {
        display: flex;
        flex-direction: column;
        gap: 1px;
        min-width: 0;
    }

    .proj-identity-name {
        font-size: 13.5px;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.35;
    }

    .proj-identity-code {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
        color: #64748b;
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        line-height: 1.4;
    }

    .proj-identity-code svg {
        width: 11px;
        height: 11px;
    }

    .proj-identity-desc {
        font-size: 12px;
        color: #64748b;
        margin-top: 2px;
        max-width: 320px;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .proj-identity-desc.is-empty {
        color: #94a3b8;
        font-style: italic;
    }

    .proj-location {
        display: inline-flex;
        align-items: flex-start;
        gap: 6px;
        font-size: 12.5px;
        color: #334155;
        max-width: 220px;
        line-height: 1.45;
    }

    .proj-location svg {
        width: 13px;
        height: 13px;
        color: #ef4444;
        margin-top: 2px;
        flex-shrink: 0;
    }

    .proj-dates {
        display: flex;
        flex-direction: column;
        gap: 2px;
        font-size: 12px;
        color: #475569;
    }

    .proj-dates span {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .proj-dates svg {
        width: 12px;
        height: 12px;
        color: #94a3b8;
        flex-shrink: 0;
    }

    .proj-dates-sep {
        color: #94a3b8;
        font-size: 10.5px;
        padding-left: 17px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .proj-wh-badge {
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }

    .proj-status {
        white-space: nowrap;
    }

    .proj-row-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
    }

    .proj-row-actions form {
        display: inline-flex;
    }

    .proj-pagination {
        padding: 14px 20px;
        border-top: 1px solid #f1f5f9;
        background: #ffffff;
    }

    /* ---------- Empty state ---------- */
    .proj-empty {
        padding: 40px 16px;
        text-align: center;
    }

    .proj-empty-icon {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        background: #f1f5f9;
        color: #94a3b8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        margin-bottom: 14px;
    }

    .proj-empty h3 {
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 4px;
    }

    .proj-empty p {
        font-size: 13px;
        color: #64748b;
        max-width: 420px;
        margin: 0 auto 16px;
        line-height: 1.55;
    }

    /* ---------- Form pages ---------- */
    .proj-form-shell {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .proj-breadcrumb {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12.5px;
        color: #94a3b8;
    }

    .proj-breadcrumb a {
        color: #64748b;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: color 0.18s ease;
    }

    .proj-breadcrumb a:hover {
        color: #2563eb;
    }

    .proj-breadcrumb svg {
        width: 12px;
        height: 12px;
    }

    .proj-form-card {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }

    .proj-form-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
        padding: 18px 24px;
        border-bottom: 1px solid #f1f5f9;
        background: #ffffff;
    }

    .proj-form-head-main {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .proj-form-head-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        flex-shrink: 0;
    }

    .proj-form-head-icon.is-blue {
        background: rgba(37, 99, 235, 0.12);
        color: #2563eb;
    }

    .proj-form-head-icon.is-amber {
        background: rgba(245, 158, 11, 0.12);
        color: #d97706;
    }

    .proj-form-title {
        font-size: 16px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        line-height: 1.3;
    }

    .proj-form-sub {
        font-size: 12px;
        color: #64748b;
        margin: 2px 0 0;
        line-height: 1.45;
    }

    .proj-form-body {
        padding: 24px;
    }

    .proj-section {
        margin-bottom: 26px;
    }

    .proj-section:last-of-type {
        margin-bottom: 0;
    }

    .proj-section-head {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 14px;
    }

    .proj-section-head svg {
        width: 16px;
        height: 16px;
    }

    .proj-section-title {
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }

    .proj-grid-2 {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .proj-field-block {
        display: flex;
        flex-direction: column;
    }

    .proj-field-block .form-control {
        width: 100%;
    }

    .proj-help {
        font-size: 11.5px;
        color: #94a3b8;
        margin-top: 4px;
        line-height: 1.45;
    }

    .proj-error {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 12px;
        color: #dc2626;
        margin-top: 4px;
    }

    .proj-error svg {
        width: 12px;
        height: 12px;
        flex-shrink: 0;
    }

    .proj-form-divider {
        height: 1px;
        background: #f1f5f9;
        margin: 26px 0;
    }

    /* ---------- Status radio cards ---------- */
    .proj-status-options {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
    }

    .proj-status-option {
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 4px;
        padding: 12px 14px;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        background: #ffffff;
        cursor: pointer;
        transition: border-color 0.16s ease, background 0.16s ease, box-shadow 0.16s ease;
    }

    .proj-status-option:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
    }

    .proj-status-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .proj-status-option-top {
        display: flex;
        align-items: center;
        gap: 7px;
        font-size: 13px;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.3;
    }

    .proj-status-option-top svg {
        width: 15px;
        height: 15px;
        color: #64748b;
        flex-shrink: 0;
    }

    .proj-status-option-desc {
        font-size: 11px;
        color: #64748b;
        line-height: 1.35;
    }

    .proj-status-option:has(input:checked) {
        border-color: #2563eb;
        background: rgba(37, 99, 235, 0.05);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .proj-status-option:has(input:checked) .proj-status-option-top svg {
        color: #2563eb;
    }

    .proj-status-option:has(input:focus-visible) {
        outline: 2px solid #2563eb;
        outline-offset: 2px;
    }

    /* ---------- Warehouse section ---------- */
    .proj-warehouse-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 18px;
    }

    .proj-warehouse-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .proj-warehouse-title-wrap {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .proj-warehouse-icon {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        background: rgba(234, 88, 12, 0.12);
        color: #ea580c;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        flex-shrink: 0;
    }

    .proj-warehouse-title {
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }

    .proj-warehouse-sub {
        font-size: 12px;
        color: #64748b;
        margin: 2px 0 0;
        line-height: 1.4;
    }

    .proj-switch {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 13px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        font-size: 12.5px;
        font-weight: 600;
        color: #1e293b;
        cursor: pointer;
        user-select: none;
        transition: border-color 0.16s ease, box-shadow 0.16s ease;
    }

    .proj-switch:hover {
        border-color: #94a3b8;
    }

    .proj-switch input {
        width: 16px;
        height: 16px;
        accent-color: #ea580c;
        cursor: pointer;
    }

    .proj-warehouse-fields {
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px dashed #cbd5e1;
    }

    /* ---------- Actions ---------- */
    .proj-form-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 26px;
        padding-top: 18px;
        border-top: 1px solid #f1f5f9;
    }

    .proj-form-actions .btn {
        padding: 10px 20px;
        font-size: 13.5px;
        font-weight: 700;
    }

    /* ---------- Edit banner ---------- */
    .proj-banner {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        padding: 18px 22px;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid #e2e8f0;
        border-radius: 14px;
    }

    .proj-banner-icon {
        width: 54px;
        height: 54px;
        border-radius: 14px;
        background: linear-gradient(135deg, #ea580c 0%, #f97316 100%);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 23px;
        flex-shrink: 0;
        box-shadow: 0 6px 16px rgba(234, 88, 12, 0.22);
    }

    .proj-banner-info {
        flex: 1;
        min-width: 220px;
    }

    .proj-banner-title-row {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 2px;
    }

    .proj-banner-title {
        font-size: 19px;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        line-height: 1.25;
    }

    .proj-banner-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        font-size: 12px;
        color: #64748b;
        margin-top: 5px;
    }

    .proj-banner-meta span {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .proj-banner-meta svg {
        width: 12px;
        height: 12px;
    }

    .proj-banner-actions {
        flex-shrink: 0;
    }

    /* ---------- Alerts ---------- */
    .proj-alert {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        padding: 13px 15px;
        border-radius: 10px;
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
        margin-bottom: 20px;
        font-size: 13px;
        line-height: 1.5;
    }

    .proj-alert svg {
        width: 17px;
        height: 17px;
        flex-shrink: 0;
        margin-top: 1px;
    }

    .proj-alert ul {
        margin: 4px 0 0;
        padding-left: 18px;
    }

    /* ---------- Responsive ---------- */
    @media (max-width: 1024px) {
        .proj-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .proj-status-options {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .proj-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .proj-header-actions,
        .proj-header-actions .btn {
            width: 100%;
            justify-content: center;
        }

        .proj-filter-form {
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
        }

        .proj-field,
        .proj-field-search {
            flex: 1 1 100%;
            min-width: 0;
        }

        .proj-filter-actions {
            width: 100%;
        }

        .proj-filter-actions .btn {
            flex: 1;
        }

        .proj-grid-2 {
            grid-template-columns: 1fr;
            gap: 0;
        }

        .proj-form-body {
            padding: 18px 16px;
        }

        .proj-form-head {
            padding: 16px;
        }

        .proj-col-location,
        .proj-col-dates {
            display: none;
        }

        .proj-identity {
            min-width: 220px;
        }

        .proj-form-actions {
            flex-direction: column-reverse;
        }

        .proj-form-actions .btn {
            width: 100%;
            justify-content: center;
        }

        .proj-banner {
            flex-direction: column;
            align-items: flex-start;
        }

        .proj-banner-actions,
        .proj-banner-actions .btn {
            width: 100%;
            justify-content: center;
        }

        .proj-warehouse-head {
            align-items: flex-start;
        }

        .proj-switch {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 640px) {
        .proj-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        .proj-kpi {
            padding: 12px;
            gap: 9px;
        }

        .proj-kpi-icon {
            width: 34px;
            height: 34px;
            font-size: 15px;
        }

        .proj-kpi-value {
            font-size: 18px;
        }

        .proj-kpi-label {
            font-size: 9.5px;
        }

        .proj-tabs {
            width: 100%;
        }

        .proj-tab {
            flex: 1;
            justify-content: center;
            padding: 8px 10px;
        }

        .proj-status-options {
            grid-template-columns: 1fr;
        }

        .proj-header-title {
            font-size: 19px;
        }
    }
</style>
