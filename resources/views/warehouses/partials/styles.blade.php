<style>
    .wh-page {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .wh-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .wh-header-main {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .wh-header-icon {
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

    .wh-header-title {
        font-size: 22px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
        margin: 0;
    }

    .wh-header-subtitle {
        font-size: 13px;
        color: #64748b;
        margin: 3px 0 0;
        line-height: 1.45;
    }

    .wh-header-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    .wh-btn-primary {
        padding: 9px 18px;
        font-weight: 700;
        border-radius: 9px;
    }

    .wh-btn-secondary {
        padding: 9px 15px;
        font-size: 13px;
        border-radius: 9px;
    }

    .wh-tabs {
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

    .wh-tab {
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

    .wh-tab:hover {
        background: #ffffff;
        color: #1e293b;
    }

    .wh-tab.is-active {
        background: #2563eb;
        color: #ffffff;
        font-weight: 700;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
    }

    .wh-tab-count {
        font-size: 11px;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 10px;
        background: #e2e8f0;
        color: #334155;
        line-height: 1.4;
    }

    .wh-tab.is-active .wh-tab-count {
        background: rgba(255, 255, 255, 0.22);
        color: #ffffff;
    }

    .wh-filter-card {
        padding: 0;
    }

    .wh-filter-form {
        display: flex;
        align-items: flex-end;
        gap: 12px;
        flex-wrap: wrap;
        padding: 16px 20px;
    }

    .wh-field {
        display: flex;
        flex-direction: column;
        min-width: 160px;
    }

    .wh-field-search {
        flex: 2 1 260px;
    }

    .wh-filter-form .wh-field:not(.wh-field-search) {
        flex: 1 1 180px;
    }

    .wh-filter-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    .wh-filter-actions .btn {
        height: 38px;
        justify-content: center;
    }

    .wh-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }

    .wh-input-wrap .wh-input-icon {
        position: absolute;
        left: 12px;
        color: #94a3b8;
        font-size: 14px;
        pointer-events: none;
        transition: color 0.2s;
    }

    .wh-input-wrap .form-control.with-icon {
        padding-left: 36px;
    }

    .wh-input-wrap:focus-within .wh-input-icon {
        color: #2563eb;
    }

    .wh-table-card {
        overflow: hidden;
    }

    .wh-table-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
    }

    .wh-table-title {
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }

    .wh-table-sub {
        font-size: 12px;
        color: #64748b;
        margin: 2px 0 0;
    }

    .wh-table td {
        padding: 14px 16px;
        vertical-align: middle;
    }

    .wh-table th {
        padding: 12px 16px;
    }

    .wh-table td:last-child,
    .wh-th-actions {
        text-align: right;
    }

    .wh-identity {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        min-width: 230px;
    }

    .wh-identity-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        flex-shrink: 0;
    }

    .wh-identity-icon.is-central {
        background: rgba(37, 99, 235, 0.1);
        color: #2563eb;
    }

    .wh-identity-icon.is-project {
        background: rgba(8, 145, 178, 0.1);
        color: #0891b2;
    }

    .wh-identity-body {
        display: flex;
        flex-direction: column;
        gap: 1px;
        min-width: 0;
    }

    .wh-identity-name {
        font-size: 13.5px;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.35;
    }

    .wh-identity-code {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
        color: #64748b;
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        line-height: 1.4;
    }

    .wh-identity-code svg {
        width: 11px;
        height: 11px;
    }

    .wh-identity-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        font-size: 11.5px;
        color: #64748b;
        margin-top: 2px;
    }

    .wh-identity-meta span {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .wh-identity-meta svg {
        width: 11px;
        height: 11px;
    }

    .wh-mobile-meta {
        display: none;
    }

    .wh-project {
        display: inline-flex;
        align-items: flex-start;
        gap: 6px;
        font-size: 12.5px;
        color: #334155;
        max-width: 220px;
        line-height: 1.45;
        text-decoration: none;
    }

    .wh-project svg {
        width: 13px;
        height: 13px;
        color: #d97706;
        margin-top: 2px;
        flex-shrink: 0;
    }

    a.wh-project:hover {
        color: #2563eb;
    }

    .wh-muted {
        color: #94a3b8;
        font-size: 12px;
        font-style: italic;
    }

    .wh-address {
        display: inline-flex;
        align-items: flex-start;
        gap: 6px;
        font-size: 12.5px;
        color: #334155;
        max-width: 220px;
        line-height: 1.45;
    }

    .wh-address svg {
        width: 13px;
        height: 13px;
        color: #ef4444;
        margin-top: 2px;
        flex-shrink: 0;
    }

    .wh-inventory {
        display: flex;
        flex-direction: column;
        gap: 4px;
        align-items: flex-start;
    }

    .wh-inventory-main {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 12.5px;
        color: #334155;
        font-weight: 600;
    }

    .wh-inventory-main svg {
        width: 13px;
        height: 13px;
        color: #64748b;
    }

    .wh-low-stock {
        font-size: 10.5px;
        padding: 2px 7px;
    }

    .wh-status {
        display: flex;
        flex-direction: column;
        gap: 3px;
        align-items: flex-start;
    }

    .wh-status-sub {
        font-size: 11px;
        color: #64748b;
        line-height: 1.35;
    }

    .wh-row-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
    }

    .wh-row-actions form {
        display: inline-flex;
    }

    .wh-row-actions .btn-icon.is-disabled {
        opacity: 0.55;
        cursor: not-allowed;
    }

    .wh-pagination {
        padding: 14px 20px;
        border-top: 1px solid #f1f5f9;
        background: #ffffff;
    }

    .wh-empty {
        padding: 40px 16px;
        text-align: center;
    }

    .wh-empty-icon {
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

    .wh-empty h3 {
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 4px;
    }

    .wh-empty p {
        font-size: 13px;
        color: #64748b;
        max-width: 420px;
        margin: 0 auto 16px;
        line-height: 1.55;
    }

    .wh-form-shell {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .wh-breadcrumb {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12.5px;
        color: #94a3b8;
    }

    .wh-breadcrumb a {
        color: #64748b;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: color 0.18s ease;
    }

    .wh-breadcrumb a:hover {
        color: #2563eb;
    }

    .wh-breadcrumb svg {
        width: 12px;
        height: 12px;
    }

    .wh-form-card {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }

    .wh-form-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
        padding: 18px 24px;
        border-bottom: 1px solid #f1f5f9;
        background: #ffffff;
    }

    .wh-form-head-main {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .wh-form-head-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        flex-shrink: 0;
    }

    .wh-form-head-icon.is-blue {
        background: rgba(37, 99, 235, 0.12);
        color: #2563eb;
    }

    .wh-form-head-icon.is-amber {
        background: rgba(245, 158, 11, 0.12);
        color: #d97706;
    }

    .wh-form-title {
        font-size: 16px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        line-height: 1.3;
    }

    .wh-form-sub {
        font-size: 12px;
        color: #64748b;
        margin: 2px 0 0;
        line-height: 1.45;
    }

    .wh-form-body {
        padding: 24px;
    }

    .wh-section {
        margin-bottom: 26px;
    }

    .wh-section:last-of-type {
        margin-bottom: 0;
    }

    .wh-section-head {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 14px;
    }

    .wh-section-head svg {
        width: 16px;
        height: 16px;
    }

    .wh-section-title {
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }

    .wh-grid-2 {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .wh-field-block {
        display: flex;
        flex-direction: column;
    }

    .wh-field-block .form-control {
        width: 100%;
    }

    .wh-help {
        font-size: 11.5px;
        color: #94a3b8;
        margin-top: 4px;
        line-height: 1.45;
    }

    .wh-optional {
        font-size: 11px;
        font-weight: 500;
        color: #94a3b8;
    }

    .wh-is-hidden {
        display: none !important;
    }

    .wh-error {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 12px;
        color: #dc2626;
        margin-top: 4px;
    }

    .wh-error svg {
        width: 12px;
        height: 12px;
        flex-shrink: 0;
    }

    .wh-form-divider {
        height: 1px;
        background: #f1f5f9;
        margin: 26px 0;
    }

    .wh-type-options {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .wh-type-option {
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 4px;
        padding: 14px 16px;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        background: #ffffff;
        cursor: pointer;
        transition: border-color 0.16s ease, background 0.16s ease, box-shadow 0.16s ease;
    }

    .wh-type-option:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
    }

    .wh-type-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .wh-type-option-top {
        display: flex;
        align-items: center;
        gap: 7px;
        font-size: 13px;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.3;
    }

    .wh-type-option-top svg {
        width: 16px;
        height: 16px;
        color: #64748b;
        flex-shrink: 0;
    }

    .wh-type-option-desc {
        font-size: 11px;
        color: #64748b;
        line-height: 1.35;
    }

    .wh-type-option:has(input:checked) {
        border-color: #2563eb;
        background: rgba(37, 99, 235, 0.05);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .wh-type-option:has(input:checked) .wh-type-option-top svg {
        color: #2563eb;
    }

    .wh-type-option:has(input:focus-visible) {
        outline: 2px solid #2563eb;
        outline-offset: 2px;
    }

    .wh-switch {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        padding: 8px 13px;
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

    .wh-switch:hover {
        border-color: #94a3b8;
    }

    .wh-switch input {
        width: 16px;
        height: 16px;
        accent-color: #2563eb;
        cursor: pointer;
    }

    .wh-form-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 26px;
        padding-top: 18px;
        border-top: 1px solid #f1f5f9;
    }

    .wh-form-actions .btn {
        padding: 10px 20px;
        font-size: 13.5px;
        font-weight: 700;
    }

    .wh-banner {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        padding: 18px 22px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
    }

    .wh-banner-icon {
        width: 54px;
        height: 54px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 23px;
        flex-shrink: 0;
    }

    .wh-banner-icon.is-central {
        background: rgba(37, 99, 235, 0.1);
        color: #2563eb;
    }

    .wh-banner-icon.is-project {
        background: rgba(8, 145, 178, 0.1);
        color: #0891b2;
    }

    .wh-banner-info {
        flex: 1;
        min-width: 220px;
    }

    .wh-banner-title-row {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 2px;
    }

    .wh-banner-title {
        font-size: 19px;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        line-height: 1.25;
    }

    .wh-banner-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        font-size: 12px;
        color: #64748b;
        margin-top: 5px;
    }

    .wh-banner-meta span {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .wh-banner-meta svg {
        width: 12px;
        height: 12px;
    }

    .wh-banner-actions {
        flex-shrink: 0;
    }

    .wh-alert {
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

    .wh-alert svg {
        width: 17px;
        height: 17px;
        flex-shrink: 0;
        margin-top: 1px;
    }

    .wh-alert ul {
        margin: 4px 0 0;
        padding-left: 18px;
    }

    @media (max-width: 1024px) {
        .wh-type-options {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .wh-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .wh-header-actions {
            width: 100%;
        }

        .wh-header-actions .btn {
            flex: 1;
            justify-content: center;
        }

        .wh-filter-form {
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
        }

        .wh-field,
        .wh-field-search {
            flex: 1 1 100%;
            min-width: 0;
        }

        .wh-filter-actions {
            width: 100%;
        }

        .wh-filter-actions .btn {
            flex: 1;
        }

        .wh-grid-2 {
            grid-template-columns: 1fr;
            gap: 0;
        }

        .wh-form-body {
            padding: 18px 16px;
        }

        .wh-form-head {
            padding: 16px;
        }

        .wh-col-type,
        .wh-col-project,
        .wh-col-address,
        .wh-col-inventory {
            display: none;
        }

        .wh-mobile-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 5px;
        }

        .wh-mobile-meta .badge {
            font-size: 10px;
            padding: 2px 7px;
        }

        .wh-mobile-meta .wh-mobile-item {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            color: #64748b;
        }

        .wh-mobile-meta .wh-mobile-item svg {
            width: 11px;
            height: 11px;
        }

        .wh-identity {
            min-width: 220px;
        }

        .wh-form-actions {
            flex-direction: column-reverse;
        }

        .wh-form-actions .btn {
            width: 100%;
            justify-content: center;
        }

        .wh-banner {
            flex-direction: column;
            align-items: flex-start;
        }

        .wh-banner-actions,
        .wh-banner-actions .btn {
            width: 100%;
            justify-content: center;
        }

        .wh-switch {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 640px) {
        .wh-tabs {
            width: 100%;
        }

        .wh-tab {
            flex: 1;
            justify-content: center;
            padding: 8px 10px;
        }

        .wh-header-title {
            font-size: 19px;
        }

        .wh-header-actions {
            flex-direction: column;
        }

        .wh-header-actions .btn {
            width: 100%;
        }
    }
</style>
