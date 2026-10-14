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
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background-color: #f1f4f9;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 32px 20px;
        position: relative;
        overflow-x: hidden;
    }

    /* Clean White Login Card */
    .login-card {
        background: #ffffff;
        border-radius: 28px;
        width: 100%;
        max-width: 440px;
        padding: 44px 38px 36px;
        box-shadow: 0 20px 45px -12px rgba(15, 23, 42, 0.08), 0 4px 16px -2px rgba(15, 23, 42, 0.03);
        position: relative;
        z-index: 10;
    }

    .login-card .brand {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-bottom: 34px;
    }

    .login-card .brand img {
        width: 145px;
        height: auto;
        max-height: 140px;
        object-fit: contain;
        background: transparent;
    }

    .field {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-size: 13.5px;
        font-weight: 500;
        color: #64748b;
        margin-bottom: 8px;
        letter-spacing: -0.01em;
    }

    .input-wrap {
        position: relative;
        background: #edf2fa;
        border-radius: 12px;
        border: 1.5px solid transparent;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
    }

    .input-wrap:focus-within {
        border-color: #c2410c;
        background: #f4f7fd;
        box-shadow: 0 0 0 3.5px rgba(194, 65, 12, 0.12);
    }

    .input-wrap.has-error {
        border-color: #ef4444;
        background: #fef2f2;
    }

    .input-wrap > i {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 14px;
        pointer-events: none;
    }

    .form-control {
        width: 100%;
        height: 48px;
        padding: 0 16px 0 44px;
        background: transparent;
        border: none;
        border-radius: 12px;
        font-size: 14px;
        color: #1e293b;
        font-family: inherit;
        outline: none;
    }

    .form-control::placeholder {
        color: #94a3b8;
    }

    .invalid-feedback {
        color: #ef4444;
        font-size: 12px;
        margin-top: 6px;
        font-weight: 500;
    }

    .toggle-password {
        position: absolute;
        right: 14px;
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
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .toggle-password:hover {
        color: #64748b;
    }

    .form-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 14px 0 24px;
    }

    .form-row label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12.5px;
        color: #64748b;
        cursor: pointer;
        user-select: none;
    }

    .form-row input[type="checkbox"] {
        accent-color: #c2410c;
        width: 16px;
        height: 16px;
        border-radius: 4px;
        border: 1.5px solid #cbd5e1;
        cursor: pointer;
    }

    .form-row a {
        font-size: 12.5px;
        color: #c2410c;
        font-weight: 500;
        text-decoration: none;
        transition: color 0.15s;
    }

    .form-row a:hover {
        color: #9a3412;
        text-decoration: underline;
    }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        height: 48px;
        padding: 0 20px;
        border: none;
        border-radius: 12px;
        background: #bd3a13;
        color: #ffffff;
        font-size: 14.5px;
        font-weight: 600;
        font-family: inherit;
        cursor: pointer;
        box-shadow: 0 4px 14px rgba(189, 58, 19, 0.25);
        transition: all 0.2s ease;
    }

    .btn-primary:hover {
        background: #a8320e;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(189, 58, 19, 0.35);
    }

    .btn-primary:active {
        transform: translateY(0);
        box-shadow: 0 2px 8px rgba(189, 58, 19, 0.2);
    }

    .btn-primary i {
        font-size: 14px;
    }

    .alert {
        padding: 12px 16px;
        border-radius: 12px;
        font-size: 13px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 20px;
    }

    .alert-success {
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .form-meta {
        margin-top: 38px;
        text-align: center;
        font-size: 11.5px;
        font-weight: 500;
        color: #94a3b8;
        letter-spacing: 0.02em;
    }
</style>

<body>
    <div class="login-card">
        <div class="brand">
            <img src="{{ asset('assets/Logo-Login-web.png') }}" alt="PT ARSIKON CIPTA KARYA">
        </div>

        {{ $slot }}
    </div>

    @stack('modals')
</body>
</html>