<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/Logo-Dashboard.png') }}">

    <title>{{ $title ?? 'Masuk' }} — PT ARSIKON CIPTA KARYA</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'Inter', sans-serif;
        background: #f1f5f9;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .login-card {
        background: #fff;
        border-radius: 16px;
        width: 100%;
        max-width: 400px;
        padding: 40px 36px 32px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.3);
    }

    .login-card .brand {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-bottom: 26px;
    }

    .login-card .brand img {
        width: 160px;
        height: 160px;
        object-fit: contain;
        background: transparent;
        margin-bottom: 10px;
        filter: drop-shadow(0 6px 14px rgba(0,0,0,0.08));
    }

    .field { margin-bottom: 18px; }

    .form-label {
        display: block;
        font-size: 12.5px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }

    .input-wrap { position: relative; }
    .input-wrap > i {
        position: absolute;
        left: 13px; top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 14px;
        pointer-events: none;
    }

    .form-control {
        width: 100%;
        padding: 11px 13px 11px 40px;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        font-size: 14px;
        color: #1e293b;
        background: #f8fafc;
        font-family: 'Inter', sans-serif;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: #ea580c;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(234,88,12,0.12);
    }

    .form-control.is-invalid { border-color: #ef4444; }
    .invalid-feedback { color: #dc2626; font-size: 12px; margin-top: 5px; }

    .toggle-password {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #94a3b8;
        font-size: 14px;
        cursor: pointer;
        padding: 4px 6px;
        border-radius: 6px;
        transition: color 0.2s;
        z-index: 1;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .toggle-password i { position: static; transform: none; }

    .toggle-password:hover { color: #c2410c; }

    .form-row { display: flex; align-items: center; justify-content: space-between; margin: 0 0 22px; }
    .form-row label { display: flex; align-items: center; gap: 7px; font-size: 13px; color: #475569; cursor: pointer; }
    .form-row input[type="checkbox"] { accent-color: #ea580c; width: 15px; height: 15px; cursor: pointer; }
    .form-row a { font-size: 13px; color: #c2410c; font-weight: 600; text-decoration: none; }
    .form-row a:hover { text-decoration: underline; }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 12px 16px;
        border: none;
        border-radius: 10px;
        background: linear-gradient(135deg, #c2410c, #ea580c);
        color: #fff;
        font-size: 14.5px;
        font-weight: 700;
        font-family: 'Inter', sans-serif;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 6px 16px rgba(234,88,12,0.3);
    }

    .btn-primary:hover { box-shadow: 0 8px 22px rgba(234,88,12,0.4); transform: translateY(-1px); }

    .alert {
        padding: 12px 16px;
        border-radius: 10px;
        font-size: 13px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 18px;
    }
    .alert-success { background: rgba(16,185,129,0.1); color: #065f46; border: 1px solid rgba(16,185,129,0.3); }

    .form-meta {
        margin-top: 22px;
        padding-top: 16px;
        border-top: 1px solid #f1f5f9;
        text-align: center;
        font-size: 12px;
        color: #94a3b8;
    }
</style>

<body>
    <div class="login-card">
        <div class="brand">
            <img src="{{ asset('assets/Logo-Login-web.png') }}" alt="PT ARSIKON CIPTA KARYA">
        </div>

        {{ $slot }}
    </div>
</body>
</html>