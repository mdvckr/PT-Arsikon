<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/Logo-Dashboard.png') }}">
    <title>{{ $title ?? config('app.name', 'CWMS') }} — Arsikon Warehouse</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')

    <style>
        :root {
            --sidebar-width: 260px;
            --topbar-height: 64px;
            --primary: #1e40af;
            --primary-light: #3b82f6;
            --accent: #f59e0b;
            --dark: #0f172a;
            --dark-2: #1e293b;
            --dark-3: #334155;
            --text-muted: #94a3b8;
            --border: #e2e8f0;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #06b6d4;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            overflow-x: hidden;
            width: 100%;
            max-width: 100vw;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            display: flex;
            min-height: 100vh;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: var(--sidebar-width);
            height: calc(100vh - var(--topbar-height));
            background: #ea580c;
            position: fixed;
            top: var(--topbar-height);
            left: 0;
            bottom: 0;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
            overflow-y: auto;
        }

        /* Workspace Card */
        .workspace-card {
            margin: 12px 14px 10px 14px;
            padding: 10px 12px;
            background: rgba(0, 0, 0, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 9px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }

        .workspace-card:hover {
            background: rgba(0, 0, 0, 0.2);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .workspace-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 6px;
            gap: 6px;
        }

        .workspace-card .workspace-label {
            font-size: 10px;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 700;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ws-badge {
            font-size: 9px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 12px;
            letter-spacing: 0.4px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
            background: rgba(255, 255, 255, 0.22);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.3);
            flex-shrink: 0;
            text-transform: uppercase;
        }

        .workspace-card .workspace-name {
            font-size: 13px;
            font-weight: 700;
            color: #ffffff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.35;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .workspace-card .workspace-select-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .workspace-card select.workspace-select {
            width: 100%;
            background: rgba(0, 0, 0, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 6px;
            color: #ffffff;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            outline: none;
            padding: 5px 22px 5px 8px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-family: inherit;
        }

        .workspace-card select.workspace-select:hover {
            background: rgba(0, 0, 0, 0.25);
            border-color: rgba(255, 255, 255, 0.45);
        }

        .workspace-card select.workspace-select option {
            background: #1e293b;
            color: #ffffff;
            font-weight: 500;
            padding: 6px;
        }

        .workspace-card .workspace-arrow {
            position: absolute;
            right: 8px;
            color: rgba(255, 255, 255, 0.85);
            font-size: 10px;
            pointer-events: none;
        }

        .workspace-sub-row {
            margin-top: 6px;
            padding-top: 5px;
            border-top: 1px solid rgba(255, 255, 255, 0.16);
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .workspace-sub-item {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.85);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: flex;
            align-items: center;
            gap: 5px;
            line-height: 1.3;
        }

        .workspace-sub-item.highlight {
            color: #fed7aa;
            font-weight: 600;
        }

        .workspace-sub-item i {
            font-size: 10px;
            opacity: 0.95;
            flex-shrink: 0;
        }

        /* Nav Section */
        .nav-section {
            padding: 8px 12px 4px;
        }

        .nav-section-label {
            font-size: 10px;
            color: rgba(255,255,255,0.55);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 0 8px;
            margin-bottom: 4px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: 9px;
            color: rgba(255,255,255,0.82);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 600;
            transition: all 0.2s;
            margin-bottom: 2px;
            cursor: pointer;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }

        .nav-item i {
            width: 18px;
            text-align: center;
            font-size: 14px;
        }

        .nav-item:hover {
            background: rgba(255,255,255,0.16);
            color: #fff;
        }

        .nav-item.active {
            background: linear-gradient(90deg, rgba(255,255,255,0.28), rgba(255,255,255,0.12));
            color: #fff;
            border-left: 3px solid #fff;
            padding-left: 9px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
        }

        .nav-item .badge {
            margin-left: auto;
            background: #fff;
            color: #c2410c;
            font-size: 10px;
            font-weight: 800;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
        }

        /* Sub-menu collapse */
        .nav-collapse .nav-item { padding-left: 38px; font-size: 13px; }

        .sidebar-footer {
            margin-top: auto;
            padding: 14px 12px;
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 9px 12px;
            border-radius: 12px;
            border: 1.5px solid rgba(0, 0, 0, 0.4);
            background: rgba(0, 0, 0, 0.04);
            transition: all 0.25s ease;
            position: relative;
        }

        .sidebar-user:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.45);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.18);
            transform: translateY(-1px);
        }

        .sidebar-user .avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            color: #52525b;
            flex-shrink: 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
            transition: transform 0.25s ease, box-shadow 0.25s ease, color 0.25s ease;
        }

        .sidebar-user:hover .avatar {
            transform: scale(1.08);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.22);
            color: #1e293b;
        }

        .sidebar-user .user-info {
            flex: 1;
            min-width: 0;
        }

        .sidebar-user .user-name {
            font-size: 13.5px;
            font-weight: 700;
            color: #ffffff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.25;
            margin-bottom: 2px;
        }

        .sidebar-user .user-role {
            font-size: 10px;
            color: rgba(255, 255, 255, 0.85);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            line-height: 1;
        }

        .btn-logout {
            color: #ffffff;
            background: transparent;
            border: none;
            cursor: pointer;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 19px;
            transition: all 0.25s ease;
        }

        .btn-logout:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.22);
            transform: translateX(-2px) scale(1.12);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .btn-logout:active {
            transform: translateX(-4px) scale(1.05);
        }

        /* ===== MAIN CONTENT ===== */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            margin-top: var(--topbar-height);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: calc(100vh - var(--topbar-height));
            min-width: 0;
            width: 100%;
            overflow-x: hidden;
        }

        /* ===== TOPBAR (Full Width) ===== */
        .topbar {
            height: var(--topbar-height);
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            z-index: 1050;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        }

        .topbar-brand {
            width: var(--sidebar-width);
            height: 100%;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 18px;
            background: #fff8f3;
            border-right: 1px solid #fed7aa;
            flex-shrink: 0;
        }

        .brand-link {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            width: 100%;
        }

        .brand-logo {
            width: 38px;
            height: 38px;
            object-fit: contain;
            flex-shrink: 0;
            filter: drop-shadow(0 1px 2px rgba(0,0,0,0.1));
        }

        .brand-text {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .brand-title {
            font-size: 14.5px;
            font-weight: 900;
            color: #0f172a;
            line-height: 1.15;
            letter-spacing: 0.04em;
            font-family: 'Inter', sans-serif;
        }

        .brand-subtitle {
            font-size: 9px;
            font-weight: 800;
            color: #0284c7;
            letter-spacing: 0.12em;
            margin-top: 2px;
            font-family: 'Inter', sans-serif;
        }

        .topbar-center {
            flex: 1;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-right: 24px;
        }

        .topbar-circle-btn {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #334155;
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            text-decoration: none;
            position: relative;
            transition: all 0.2s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.12);
            border: none;
            cursor: pointer;
        }

        .topbar-circle-btn:hover {
            background: #1e293b;
            color: #ffffff;
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .topbar-circle-btn .notif-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            min-width: 19px;
            height: 19px;
            padding: 0 4px;
            background: #ef4444;
            color: #ffffff;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #ffffff;
            box-shadow: 0 2px 5px rgba(239, 68, 68, 0.5);
            animation: pulse-badge 2s infinite;
        }

        @keyframes pulse-badge {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.12); }
        }

        /* ===== PAGE CONTENT ===== */
        .page-content {
            padding: 24px;
            flex: 1;
        }

        /* ===== CARDS ===== */
        .card {
            background: white;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .card-header {
            padding: 18px 22px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card-title {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            flex: 1;
        }

        .card-body { padding: 22px; }

        /* ===== STAT CARDS ===== */
        .stat-card {
            background: white;
            border-radius: 14px;
            padding: 20px;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: flex-start;
            gap: 16px;
            transition: all 0.25s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }

        .stat-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .stat-icon.blue   { background: rgba(59,130,246,0.12); color: #3b82f6; }
        .stat-icon.green  { background: rgba(16,185,129,0.12); color: #10b981; }
        .stat-icon.amber  { background: rgba(245,158,11,0.12); color: #f59e0b; }
        .stat-icon.red    { background: rgba(239,68,68,0.12);  color: #ef4444; }
        .stat-icon.purple { background: rgba(139,92,246,0.12); color: #8b5cf6; }
        .stat-icon.cyan   { background: rgba(6,182,212,0.12);  color: #06b6d4; }

        .stat-info .label { font-size: 12px; color: #94a3b8; font-weight: 500; margin-bottom: 4px; }
        .stat-info .value { font-size: 26px; font-weight: 800; color: #0f172a; line-height: 1; }
        .stat-info .sub   { font-size: 11.5px; color: #94a3b8; margin-top: 4px; }

        /* ===== BUTTONS ===== */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .btn-primary { background: #2563eb; color: white; }
        .btn-primary:hover { background: #1d4ed8; box-shadow: 0 4px 14px rgba(37,99,235,0.35); color: white; }
        .btn-success { background: #059669; color: white; }
        .btn-success:hover { background: #047857; color: white; }
        .btn-warning { background: #d97706; color: white; }
        .btn-warning:hover { background: #b45309; color: white; }
        .btn-danger { background: #dc2626; color: white; }
        .btn-danger:hover { background: #b91c1c; color: white; }
        .btn-secondary { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .btn-secondary:hover { background: #e2e8f0; color: #1e293b; }
        .btn-outline { background: transparent; border: 1.5px solid currentColor; }
        .btn-sm { padding: 5px 11px; font-size: 12px; }
        .btn-icon { padding: 8px; }

        /* ===== TABLES ===== */
        .table-wrap { overflow-x: auto; }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13.5px;
        }

        table.data-table th {
            background: #f8fafc;
            padding: 12px 14px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            border-bottom: 2px solid #e2e8f0;
            white-space: nowrap;
        }

        table.data-table td {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            vertical-align: middle;
        }

        table.data-table tr:last-child td { border-bottom: none; }
        table.data-table tr:hover td { background: #f8fafc; }

        /* ===== BADGES ===== */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 600;
        }

        .badge-success { background: rgba(16,185,129,0.12); color: #059669; }
        .badge-warning { background: rgba(245,158,11,0.12); color: #d97706; }
        .badge-danger  { background: rgba(239,68,68,0.12);  color: #dc2626; }
        .badge-info    { background: rgba(6,182,212,0.12);  color: #0891b2; }
        .badge-primary { background: rgba(37,99,235,0.12); color: #2563eb; }
        .badge-purple  { background: rgba(139,92,246,0.12); color: #7c3aed; }
        .badge-gray    { background: #f1f5f9; color: #64748b; }

        /* ===== FORMS ===== */
        .form-label {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 5px;
        }

        .form-control {
            width: 100%;
            padding: 9px 12px;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-size: 13.5px;
            color: #1e293b;
            background: white;
            transition: border-color 0.2s, box-shadow 0.2s;
            font-family: 'Inter', sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
        }

        .form-control.is-invalid { border-color: #ef4444; }
        .invalid-feedback { color: #dc2626; font-size: 12px; margin-top: 4px; }

        /* ===== GRID ===== */
        .grid { display: grid; gap: 16px; }
        .grid-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-4 { grid-template-columns: repeat(4, 1fr); }

        /* ===== ALERTS ===== */
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13.5px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 16px;
        }

        .alert-success { background: rgba(16,185,129,0.1); color: #065f46; border: 1px solid rgba(16,185,129,0.3); }
        .alert-warning { background: rgba(245,158,11,0.1); color: #78350f; border: 1px solid rgba(245,158,11,0.3); }
        .alert-danger  { background: rgba(239,68,68,0.1);  color: #7f1d1d; border: 1px solid rgba(239,68,68,0.3); }
        .alert-info    { background: rgba(6,182,212,0.1);  color: #164e63; border: 1px solid rgba(6,182,212,0.3); }

        /* ===== BREADCRUMB ===== */
        .breadcrumb { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #94a3b8; margin-bottom: 16px; }
        .breadcrumb a { color: #64748b; text-decoration: none; }
        .breadcrumb a:hover { color: #3b82f6; }
        .breadcrumb-sep { color: #cbd5e1; }

        /* ===== TABS ===== */
        .tabs { display: flex; gap: 2px; border-bottom: 2px solid #e2e8f0; margin-bottom: 20px; }
        .tab-btn {
            padding: 10px 18px;
            font-size: 13.5px;
            font-weight: 600;
            color: #64748b;
            background: none;
            border: none;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: all 0.2s;
        }
        .tab-btn.active { color: #2563eb; border-bottom-color: #2563eb; }
        .tab-btn:hover:not(.active) { color: #374151; }

        /* ===== MODAL ===== */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15,23,42,0.6);
            backdrop-filter: blur(4px);
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.show { display: flex; }

        .modal-box {
            background: white;
            border-radius: 16px;
            width: 90%;
            max-width: 520px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
            animation: modalIn 0.2s ease;
        }

        @keyframes modalIn {
            from { opacity: 0; transform: scale(0.95) translateY(-10px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }

        .modal-header {
            padding: 20px 22px 16px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .modal-title { font-size: 16px; font-weight: 700; color: #0f172a; flex: 1; }

        .modal-body { padding: 22px; }

        .modal-footer {
            padding: 16px 22px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .btn-close-modal { background: none; border: none; cursor: pointer; color: #94a3b8; font-size: 18px; }
        .btn-close-modal:hover { color: #1e293b; }

        /* ===== RESPONSIVE ===== */
        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 20px;
            color: #374151;
            padding: 4px;
        }

        /* Sidebar backdrop overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }

        /* ===== TABLET (max 1024px) ===== */
        @media (max-width: 1024px) {
            .mobile-toggle { display: flex; align-items: center; justify-content: center; }
            .topbar-brand { width: auto; border-right: none; padding: 0 12px; }
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-wrapper { margin-left: 0; }
            .grid-4 { grid-template-columns: repeat(2, 1fr); }
            .grid-3 { grid-template-columns: repeat(2, 1fr); }

            /* Card headers with filters should wrap */
            .card-header {
                flex-wrap: wrap;
                gap: 10px;
            }
            .card-header form {
                width: 100%;
                flex-wrap: wrap;
            }
        }

        /* ===== SMALL TABLET (max 768px) ===== */
        @media (max-width: 768px) {
            .page-content { padding: 16px 14px; }

            /* Page header: title + button should stack */
            .flex.justify-between.items-center.mb-4,
            .flex.items-center.justify-between.mb-4 {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 12px;
            }

            /* KPI stat cards compact */
            .grid-4 { grid-template-columns: repeat(2, 1fr); gap: 10px; }
            .stat-card { padding: 14px; gap: 10px; }
            .stat-info .value { font-size: 20px; }
            .stat-icon { width: 40px; height: 40px; font-size: 16px; border-radius: 10px; }

            /* Card header with search/filter form */
            .card-header {
                padding: 14px 16px;
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            .card-header form {
                width: 100%;
                margin-left: 0 !important;
            }
            .card-header .flex.gap-2,
            .card-header form .flex.gap-2,
            .card-header form.flex {
                flex-wrap: wrap;
                width: 100%;
            }
            .card-header form .form-control,
            .card-header form select.form-control,
            .card-header form input.form-control {
                width: 100% !important;
                min-width: 0 !important;
                flex: 1 1 100%;
            }
            .card-header form .flex > input,
            .card-header form .flex > select {
                flex: 1;
                min-width: 0;
            }

            /* Card body compact */
            .card-body { padding: 16px; }

            /* Table font size */
            table.data-table { font-size: 12.5px; }
            table.data-table th { padding: 10px 10px; font-size: 10px; }
            table.data-table td { padding: 10px 10px; }

            /* Buttons compact */
            .btn { padding: 7px 12px; font-size: 12.5px; }
            .btn-sm { padding: 4px 8px; font-size: 11px; }

            /* Topbar actions compact */
            .topbar-actions { gap: 8px; padding-right: 14px; }
            .topbar-circle-btn { width: 34px; height: 34px; font-size: 13px; }

            /* Breadcrumb compact */
            .breadcrumb { font-size: 12px; margin-bottom: 12px; }

            /* Grid 2 col */
            .grid-2 { grid-template-columns: 1fr; }

            /* Modal responsive */
            .modal-box { width: 95%; border-radius: 12px; }

            /* Empty state compact */
            .empty-state { padding: 32px 16px; }
            .empty-state i { font-size: 32px; }

            /* Filter form within card-body */
            .card-body form.flex {
                flex-direction: column;
                gap: 10px;
            }
            .card-body form.flex > div {
                min-width: 0 !important;
                width: 100%;
            }
            .card-body form.flex > div[style*="flex:1"] {
                flex: none !important;
            }
            .card-body form .flex.gap-2 {
                width: 100%;
            }
            .card-body form .flex.gap-2 .btn {
                flex: 1;
            }

            /* Notifications toast mobile */
            .notif-toast-container {
                bottom: 16px;
                right: 12px;
                left: 12px;
            }
            .notif-toast {
                max-width: 100%;
            }
        }

        /* ===== PHONE (max 640px) ===== */
        @media (max-width: 640px) {
            .page-content { padding: 12px 10px; }

            .grid-2, .grid-3, .grid-4 { grid-template-columns: 1fr; gap: 8px; }

            /* Stat cards: 2-col grid on small phones */
            .grid-4 { grid-template-columns: repeat(2, 1fr); }
            .stat-card { padding: 12px; gap: 8px; }
            .stat-info .label { font-size: 10.5px; }
            .stat-info .value { font-size: 18px; }
            .stat-info .sub { font-size: 10px; }
            .stat-icon { width: 36px; height: 36px; font-size: 14px; }

            /* Card header - full stack on phone */
            .card-header { padding: 12px 14px; }
            .card-title { font-size: 13.5px; }
            .card-body { padding: 14px; }

            /* Table */
            table.data-table { font-size: 12px; }
            table.data-table th { padding: 8px; font-size: 9.5px; letter-spacing: 0.04em; }
            table.data-table td { padding: 8px; }

            /* h2 page title */
            h2.fw-700, h2[style*="font-size:20px"] {
                font-size: 17px !important;
            }

            /* Alerts compact */
            .alert { padding: 10px 12px; font-size: 12.5px; border-radius: 8px; }

            /* Topbar brand text hide subtitle */
            .brand-subtitle { display: none; }
            .brand-title { font-size: 13px; }
            .brand-logo { width: 32px; height: 32px; }
            .topbar-brand { gap: 8px; }

            /* Tabs scroll */
            .tabs { overflow-x: auto; -webkit-overflow-scrolling: touch; }
            .tab-btn { padding: 8px 14px; font-size: 12.5px; white-space: nowrap; }

            /* Form controls compact */
            .form-control { padding: 8px 10px; font-size: 13px; border-radius: 6px; }
            .form-label { font-size: 11.5px; margin-bottom: 4px; }

            /* Badges compact */
            .badge { padding: 2px 7px; font-size: 10.5px; }
        }

        /* ===== VERY SMALL PHONE (max 400px) ===== */
        @media (max-width: 400px) {
            .page-content { padding: 10px 8px; }

            .grid-4 { grid-template-columns: 1fr 1fr; gap: 6px; }
            .stat-card { padding: 10px; }
            .stat-info .value { font-size: 16px; }

            .brand-text { display: none; }
            .topbar-brand { padding: 0 8px; }
            .topbar-actions { padding-right: 10px; gap: 6px; }
            .topbar-circle-btn { width: 32px; height: 32px; font-size: 12px; }

            .card-header { padding: 10px 12px; }
            .card-body { padding: 12px; }

            table.data-table th { padding: 6px; font-size: 9px; }
            table.data-table td { padding: 6px; font-size: 11.5px; }
        }

        /* ===== SCROLLBAR ===== */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* ===== MISC UTILITIES ===== */
        .text-muted   { color: #94a3b8; }
        .text-success { color: #059669; }
        .text-danger  { color: #dc2626; }
        .text-warning { color: #d97706; }
        .text-primary { color: #2563eb; }
        .fw-600 { font-weight: 600; }
        .fw-700 { font-weight: 700; }
        .mt-1 { margin-top: 4px; }  .mt-2 { margin-top: 8px; }
        .mt-3 { margin-top: 12px; } .mt-4 { margin-top: 16px; }
        .mb-1 { margin-bottom: 4px; }  .mb-2 { margin-bottom: 8px; }
        .mb-3 { margin-bottom: 12px; } .mb-4 { margin-bottom: 16px; }
        .flex { display: flex; } .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .gap-1 { gap: 4px; } .gap-2 { gap: 8px; } .gap-3 { gap: 12px; }
        .w-full { width: 100%; }
        .empty-state { text-align: center; padding: 48px 24px; color: #94a3b8; }
        .empty-state i { font-size: 40px; margin-bottom: 12px; display: block; opacity: 0.4; }
        .empty-state h3 { font-size: 15px; font-weight: 600; color: #475569; margin-bottom: 6px; }

        /* ===== NOTIFICATION TOAST ===== */
        .notif-toast-container {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        }

        .notif-toast {
            background: #0f172a;
            color: #ffffff;
            padding: 14px 18px;
            border-radius: 12px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.28);
            display: flex;
            align-items: center;
            gap: 12px;
            pointer-events: auto;
            cursor: pointer;
            border-left: 4px solid #ea580c;
            max-width: 380px;
            animation: toastIn 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            transition: all 0.3s ease;
        }

        .notif-toast:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 35px rgba(0, 0, 0, 0.38);
        }

        .notif-toast .toast-icon {
            width: 36px;
            height: 36px;
            background: rgba(234, 88, 12, 0.2);
            color: #ea580c;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }

        .notif-toast .toast-content {
            flex: 1;
            min-width: 0;
        }

        .notif-toast .toast-title {
            font-size: 13px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .notif-toast .toast-desc {
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .notif-toast .toast-close {
            background: none;
            border: none;
            color: #94a3b8;
            font-size: 18px;
            cursor: pointer;
            padding: 2px;
            line-height: 1;
        }

        .notif-toast .toast-close:hover {
            color: #ffffff;
        }

        @keyframes toastIn {
            from { transform: translateX(50px) scale(0.95); opacity: 0; }
            to   { transform: translateX(0) scale(1); opacity: 1; }
        }

        .toast-out {
            transform: translateX(50px) scale(0.9);
            opacity: 0;
        }

        /* Global Print Rules */
        @media print {
            .topbar, .sidebar, .notif-toast-container, .mobile-toggle {
                display: none !important;
            }
            body {
                display: block !important;
                background: #ffffff !important;
                min-height: auto !important;
                height: auto !important;
            }
            .main-wrapper {
                margin: 0 !important;
                padding: 0 !important;
                min-height: auto !important;
                display: block !important;
            }
            .page-content {
                margin: 0 !important;
                padding: 0 !important;
                display: block !important;
            }
        }
    </style>
</head>

<body
    data-notif-count="{{ auth()->user()?->unreadNotifications()->count() ?? 0 }}"
    data-notif-last-id="{{ auth()->user()?->unreadNotifications()->latest()->first()?->id ?? '' }}"
    data-notif-url="{{ route('notifications.index') }}"
    data-notif-fetch-url="{{ route('notifications.unreadCount') }}">
    <!-- TOPBAR (Full Width) -->
    <header class="topbar">
        <div class="topbar-brand">
            <button type="button" class="mobile-toggle" onclick="toggleSidebar()" aria-label="Toggle Menu">
                <i class="fas fa-bars"></i>
            </button>
            <a href="{{ route('dashboard') }}" class="brand-link">
                <img src="{{ asset('assets/Logo-Dashboard.png') }}" class="brand-logo" alt="PT ARSIKON CIPTA KARYA">
                <div class="brand-text">
                    <div class="brand-title">PT ARSIKON</div>
                    <div class="brand-subtitle">CIPTA KARYA</div>
                </div>
            </a>
        </div>

        <div class="topbar-center"></div>

        @php
            $unreadNotificationsCount = auth()->user()->unreadNotifications()->count();
        @endphp

        <div class="topbar-actions">
            <a href="{{ route('notifications.index') }}" class="topbar-circle-btn" title="Notifikasi ({{ $unreadNotificationsCount }} belum dibaca)">
                <i class="fas fa-bell"></i>
                @if($unreadNotificationsCount > 0)
                <span class="notif-badge">{{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}</span>
                @endif
            </a>
            <a href="{{ route('profile.edit') }}" class="topbar-circle-btn" title="Profil">
                <i class="fas fa-user"></i>
            </a>
        </div>
    </header>

    <!-- SIDEBAR OVERLAY (Mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        @php
            $authUser = auth()->user();
            $warehouses = $authUser->hasRole('Owner') ? \App\Models\Warehouse::all() : $authUser->warehouses;
            if ($warehouses->isEmpty()) {
                $warehouses = \App\Models\Warehouse::all();
            }
            $activeWarehouse = $authUser->activeWarehouse();
            $activeWarehouseId = $activeWarehouse?->id ?? session('active_warehouse_id');
            $activeWarehouseName = $activeWarehouse?->name ?? 'Gudang Pusat PT Arsikon';

            // Menentukan keterangan dan peran akun secara ringkas & rapi
            if ($authUser->hasRole('Owner')) {
                $roleLabel = 'Owner / Direksi';
                $roleBadge = 'Owner';
                $roleIcon = 'fa-crown';
                $subHeadline = 'Multi-Gudang (Akses Penuh)';
                $subDesc = 'Monitoring pusat & seluruh proyek';
            } elseif ($authUser->hasRole('Admin Gudang Pusat') || ($authUser->hasRole('Admin') && !$authUser->hasRole('Admin Gudang Proyek'))) {
                $roleLabel = 'Admin Gudang Pusat';
                $roleBadge = 'Pusat';
                $roleIcon = 'fa-building';
                $subHeadline = 'Sentral Logistik Jakarta';
                $subDesc = 'Distribusi material & stok sentral';
            } elseif ($authUser->hasRole('Admin Gudang Proyek')) {
                $roleLabel = 'Admin Gudang Proyek';
                $roleBadge = 'Proyek';
                $roleIcon = 'fa-helmet-safety';
                $subHeadline = $activeWarehouse?->project?->name ?? 'Site Proyek Lapangan';
                $subDesc = 'Permintaan material & alat proyek';
            } elseif ($authUser->hasRole('Admin PO')) {
                $roleLabel = 'Admin Pengadaan (PO)';
                $roleBadge = 'Pengadaan';
                $roleIcon = 'fa-truck-ramp-box';
                $subHeadline = 'Divisi Pembelian & Vendor';
                $subDesc = 'Purchase order & pembayaran';
            } else {
                $roleLabel = $authUser->roles->first()?->name ?? 'User';
                $roleBadge = 'Petugas';
                $roleIcon = 'fa-warehouse';
                $subHeadline = $activeWarehouseName;
                $subDesc = 'Operasional inventaris';
            }
        @endphp

        <!-- <div class="workspace-card">
            <div class="workspace-header-row">
                <span class="workspace-label">WORKSPACE</span>
                <span class="ws-badge">
                    <i class="fas {{ $roleIcon }}"></i> {{ $roleBadge }}
                </span>
            </div>

            @if($warehouses->count() > 1)
            <form method="POST" action="{{ route('workspace.switch') }}" id="workspaceForm">
                @csrf
                <div class="workspace-select-wrapper">
                    <select name="warehouse_id" onchange="document.getElementById('workspaceForm').submit()" class="workspace-select" title="Ganti Workspace Gudang">
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ $activeWarehouseId == $wh->id ? 'selected' : '' }}>
                                {{ $wh->is_central ? '🏢 ' : '🏗️ ' }}{{ $wh->name }}
                            </option>
                        @endforeach
                    </select>
                    <i class="fas fa-chevron-down workspace-arrow"></i>
                </div>
            </form>
            @else
            <div class="workspace-name" title="{{ $activeWarehouseName }}">
                <i class="{{ ($activeWarehouse?->is_central ?? false) ? 'fas fa-building' : 'fas fa-helmet-safety' }}" style="font-size:12px; opacity:0.95; flex-shrink:0;"></i>
                <span style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $activeWarehouseName }}</span>
            </div>
            @endif

            <div class="workspace-sub-row">
                @if($subHeadline)
                <div class="workspace-sub-item highlight" title="{{ $subHeadline }}">
                    <i class="{{ ($activeWarehouse?->is_central ?? false) ? 'fas fa-city' : 'fas fa-location-dot' }}"></i>
                    <span>{{ $subHeadline }}</span>
                </div>
                @endif
                <div class="workspace-sub-item" title="{{ $subDesc }}">
                    <i class="fas fa-circle-info" style="font-size:9px;"></i>
                    <span>{{ $subDesc }}</span>
                </div>
            </div>
        </div> -->

        <nav style="flex:1; padding: 8px 0;">
            <!-- DASHBOARD -->
            <div class="nav-section">
                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-table-cells-large"></i> Dashboard
                </a>
            </div>

            <!-- MASTER DATA -->
            @if(auth()->user()->hasAnyRole(['Owner', 'Admin', 'Admin Gudang Pusat']))
            <div class="nav-section">
                <div class="nav-section-label" style="padding: 8px 20px 4px;">Master Data</div>
                <a href="{{ route('materials.index') }}" class="nav-item {{ request()->routeIs('materials.*') ? 'active' : '' }}">
                    <i class="fas fa-boxes-stacked"></i> Material
                </a>
                <a href="{{ route('tools.index') }}" class="nav-item {{ request()->routeIs('tools.*') ? 'active' : '' }}">
                    <i class="fas fa-screwdriver-wrench"></i> Alat
                </a>
            </div>
            @endif

            <!-- TRANSAKSI -->
            <div class="nav-section">
                <div class="nav-section-label" style="padding: 8px 20px 4px;">Transaksi</div>
                <a href="{{ route('material-requests.index') }}" class="nav-item {{ request()->routeIs('material-requests.*') ? 'active' : '' }}">
                    <i class="fas fa-file-circle-plus"></i> Permintaan Material
                </a>
                <a href="{{ route('distributions.index') }}" class="nav-item {{ request()->routeIs('distributions.*') ? 'active' : '' }}">
                    <i class="fas fa-right-left"></i> Surat Jalan
                </a>
            </div>

            <!-- ALAT & STOK -->
            <div class="nav-section">
                <div class="nav-section-label" style="padding: 8px 20px 4px;">Alat & Stok</div>
                <a href="{{ route('tool-assignments.index') }}" class="nav-item {{ request()->routeIs('tool-assignments.*') ? 'active' : '' }}">
                    <i class="fas fa-hand-holding"></i> Peminjaman Alat
                </a>
                <a href="{{ route('stock-opnames.index') }}" class="nav-item {{ request()->routeIs('stock-opnames.*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-check"></i> Stock Opname
                </a>
                <a href="{{ route('inventory.index') }}" class="nav-item {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                    <i class="fas fa-layer-group"></i> Inventori
                </a>
            </div>

            <!-- PENGADAAN (Admin PO & Owner) -->
            @if(auth()->user()->hasAnyRole(['Owner', 'Admin', 'Admin Gudang Pusat', 'Admin PO']))
            <div class="nav-section">
                <div class="nav-section-label" style="padding: 8px 20px 4px;">Pengadaan</div>
                <a href="{{ route('procurement.index') }}" class="nav-item {{ request()->routeIs('procurement.*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list"></i> Permintaan Pengadaan
                </a>
                <a href="{{ route('payments.index') }}" class="nav-item {{ request()->routeIs('payments.*') ? 'active' : '' }}">
                    <i class="fas fa-money-bill-wave"></i> Pembayaran
                </a>
                <a href="{{ route('print-templates.index') }}" class="nav-item {{ request()->routeIs('print-templates.*') ? 'active' : '' }}">
                    <i class="fas fa-stamp"></i> Template Kop Surat
                </a>
            </div>
            @endif

            <!-- PENGEMBALIAN -->
            <div class="nav-section">
                <a href="{{ route('returns.index') }}" class="nav-item {{ request()->routeIs('returns.*') ? 'active' : '' }}">
                    <i class="fas fa-rotate-left"></i> Pengembalian
                </a>
            </div>

            <!-- LAPORAN -->
            @if(auth()->user()->hasAnyRole(['Owner', 'Admin', 'Admin Gudang Pusat']))
            <div class="nav-section">
                <div class="nav-section-label" style="padding: 8px 20px 4px;">Laporan</div>
                <a href="{{ route('reports.index') }}" class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar"></i> Laporan
                </a>
                <a href="{{ route('audit-logs.index') }}" class="nav-item {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">
                    <i class="fas fa-shield-halved"></i> Audit Log
                </a>
            </div>
            @endif

            <!-- ADMIN -->
            @if(auth()->user()->hasAnyRole(['Owner', 'Admin']))
            <div class="nav-section">
                <div class="nav-section-label" style="padding: 8px 20px 4px;">Admin</div>
                <a href="{{ route('users.index') }}" class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i> Pengguna
                </a>
                <a href="{{ route('warehouses.index') }}" class="nav-item {{ request()->routeIs('warehouses.*') ? 'active' : '' }}">
                    <i class="fas fa-warehouse"></i> Gudang
                </a>
                <a href="{{ route('projects.index') }}" class="nav-item {{ request()->routeIs('projects.*') ? 'active' : '' }}">
                    <i class="fas fa-building-columns"></i> Proyek
                </a>
            </div>
            @endif
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-info">
                    <div class="user-name">{{ auth()->user()->name }}</div>
                    <div class="user-role">{{ strtoupper($roleLabel) }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-logout" title="Logout">
                        <i class="fas fa-right-from-bracket fa-flip-horizontal"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- MAIN WRAPPER -->
    <div class="main-wrapper">
        <!-- PAGE CONTENT -->
        <main class="page-content">
            @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-circle-check"></i>
                <div>{{ session('success') }}</div>
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-circle-xmark"></i>
                <div>{{ session('error') }}</div>
            </div>
            @endif
            @if(session('warning'))
            <div class="alert alert-warning">
                <i class="fas fa-triangle-exclamation"></i>
                <div>{{ session('warning') }}</div>
            </div>
            @endif

            {{ $slot }}
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (sidebar) {
                const isOpen = sidebar.classList.toggle('open');
                if (overlay) {
                    if (isOpen) {
                        overlay.classList.add('active');
                        document.body.style.overflow = 'hidden';
                    } else {
                        overlay.classList.remove('active');
                        document.body.style.overflow = '';
                    }
                }
            }
        }

        // Close sidebar on outside click or resize
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (window.innerWidth <= 1024 && sidebar && sidebar.classList.contains('open')) {
                if (!sidebar.contains(e.target) && !e.target.closest('.mobile-toggle')) {
                    sidebar.classList.remove('open');
                    if (overlay) overlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            }
        });

        window.addEventListener('resize', function() {
            if (window.innerWidth > 1024) {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebarOverlay');
                if (sidebar) sidebar.classList.remove('open');
                if (overlay) overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });

        // Auto-dismiss alerts
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(el => {
                el.style.transition = 'opacity 0.4s';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 400);
            });
        }, 4000);
    </script>

    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInputs = document.querySelectorAll('input[name="search"]');
            searchInputs.forEach(input => {
                let timeout = null;
                input.addEventListener('input', function() {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => {
                        if (this.form) {
                            this.form.submit();
                        }
                    }, 400); // submit 400ms after user stops typing
                });

                // Set cursor at the end of input if already filled
                if(input.value) {
                    input.focus();
                    const len = input.value.length;
                    input.setSelectionRange(len, len);
                }
            });
        });
    </script>

    <!-- Toast Container -->
    <div class="notif-toast-container" id="notifToastContainer"></div>

    <script>
        // Web Audio Chime Sound (Two-tone harmony)
        let audioCtx = null;
        function getAudioContext() {
            if (!audioCtx) {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (AudioContext) audioCtx = new AudioContext();
            }
            if (audioCtx && audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
            return audioCtx;
        }

        // Unlock Web Audio on first user interaction
        document.addEventListener('click', function unlockAudio() {
            getAudioContext();
            document.removeEventListener('click', unlockAudio);
        }, { once: true });

        function playNotificationChime() {
            try {
                const ctx = getAudioContext();
                if (!ctx) return;

                const now = ctx.currentTime;

                // First Note: D5 (587.33 Hz)
                const osc1 = ctx.createOscillator();
                const gain1 = ctx.createGain();
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(587.33, now);
                osc1.frequency.exponentialRampToValueAtTime(880, now + 0.12);
                gain1.gain.setValueAtTime(0.25, now);
                gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
                osc1.connect(gain1);
                gain1.connect(ctx.destination);
                osc1.start(now);
                osc1.stop(now + 0.35);

                // Second Note: A5 -> D6 (880 Hz -> 1174.66 Hz)
                const osc2 = ctx.createOscillator();
                const gain2 = ctx.createGain();
                osc2.type = 'sine';
                osc2.frequency.setValueAtTime(880, now + 0.1);
                osc2.frequency.exponentialRampToValueAtTime(1174.66, now + 0.28);
                gain2.gain.setValueAtTime(0, now);
                gain2.gain.setValueAtTime(0.3, now + 0.1);
                gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.65);
                osc2.connect(gain2);
                gain2.connect(ctx.destination);
                osc2.start(now + 0.1);
                osc2.stop(now + 0.65);
            } catch (err) {
                console.warn('Audio playback error:', err);
            }
        }

        // Realtime Notification Poller
        let lastNotifCount = parseInt(document.body.dataset.notifCount || '0', 10);
        let lastNotifId = document.body.dataset.notifLastId || '';

        function showNotificationToast(data) {
            const container = document.getElementById('notifToastContainer');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = 'notif-toast';
            toast.innerHTML = `
                <div class="toast-icon"><i class="fas fa-bell"></i></div>
                <div class="toast-content">
                    <div class="toast-title">${escapeHtml(data.title || 'Notifikasi Baru')}</div>
                    <div class="toast-desc">${escapeHtml(data.message || '')}</div>
                </div>
                <button class="toast-close" type="button" title="Tutup">&times;</button>
            `;

            toast.onclick = function(e) {
                if (e.target.closest('.toast-close')) {
                    toast.classList.add('toast-out');
                    setTimeout(() => toast.remove(), 300);
                    return;
                }
                window.location.href = data.url || document.body.dataset.notifUrl;
            };

            container.appendChild(toast);

            setTimeout(() => {
                toast.classList.add('toast-out');
                setTimeout(() => toast.remove(), 300);
            }, 7000);
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function pollNotifications() {
            fetch(document.body.dataset.notifFetchUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(res => {
                const count = res.count || 0;
                const bell = document.querySelector('.topbar-circle-btn[href*="notifications"]');

                if (bell) {
                    let badge = bell.querySelector('.notif-badge');
                    if (count > 0) {
                        if (!badge) {
                            badge = document.createElement('span');
                            badge.className = 'notif-badge';
                            bell.appendChild(badge);
                        }
                        badge.textContent = count > 99 ? '99+' : count;
                    } else if (badge) {
                        badge.remove();
                    }
                }

                // If new notification detected
                if (count > lastNotifCount && res.latest && res.latest.id !== lastNotifId) {
                    playNotificationChime();
                    showNotificationToast(res.latest);
                }

                lastNotifCount = count;
                if (res.latest) lastNotifId = res.latest.id;
            })
            .catch(() => {});
        }

        // Poll every 15 seconds
        setInterval(pollNotifications, 15000);

        // Preserve Sidebar Scroll Position
        (function() {
            const sidebar = document.getElementById('sidebar');
            if (!sidebar) return;

            // Restore scroll position on load
            const savedScrollPos = sessionStorage.getItem('sidebar_scroll_pos');
            if (savedScrollPos !== null) {
                sidebar.scrollTop = parseInt(savedScrollPos, 10);
            } else {
                // If first time or no saved position, ensure active item is visible
                const activeItem = sidebar.querySelector('.nav-item.active');
                if (activeItem) {
                    activeItem.scrollIntoView({ block: 'nearest', behavior: 'instant' });
                }
            }

            // Save scroll position when scrolling
            sidebar.addEventListener('scroll', function() {
                sessionStorage.setItem('sidebar_scroll_pos', sidebar.scrollTop);
            }, { passive: true });

            // Also save on link click inside sidebar
            sidebar.querySelectorAll('a').forEach(function(link) {
                link.addEventListener('click', function() {
                    sessionStorage.setItem('sidebar_scroll_pos', sidebar.scrollTop);
                });
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
