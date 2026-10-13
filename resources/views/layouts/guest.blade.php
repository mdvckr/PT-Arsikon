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
        background: radial-gradient(circle at 50% 12%, rgba(234, 88, 12, 0.42) 0%, transparent 60%),
                    radial-gradient(circle at 50% 88%, rgba(29, 78, 216, 0.52) 0%, transparent 65%),
                    linear-gradient(180deg, #080c14 0%, #101625 50%, #070a12 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px 20px;
        position: relative;
        overflow-x: hidden;
    }

    /* Ambient glow elements */
    body::before {
        content: "";
        position: fixed;
        top: -12%;
        left: 50%;
        transform: translateX(-50%);
        width: 620px;
        height: 420px;
        background: radial-gradient(circle, rgba(234, 88, 12, 0.38) 0%, rgba(234, 88, 12, 0) 70%);
        filter: blur(65px);
        pointer-events: none;
        z-index: 1;
    }

    body::after {
        content: "";
        position: fixed;
        bottom: -12%;
        left: 50%;
        transform: translateX(-50%);
        width: 680px;
        height: 460px;
        background: radial-gradient(circle, rgba(37, 99, 235, 0.4) 0%, rgba(37, 99, 235, 0) 70%);
        filter: blur(75px);
        pointer-events: none;
        z-index: 1;
    }

    /* Liquid Glass Login Card (iOS 26 Style) */
    .login-card {
        position: relative;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.14) 0%, rgba(255, 255, 255, 0.05) 100%);
        -webkit-backdrop-filter: blur(36px) saturate(190%) brightness(1.08);
        backdrop-filter: blur(36px) saturate(190%) brightness(1.08);
        border-radius: 32px;
        width: 100%;
        max-width: 430px;
        padding: 38px 32px 30px;
        box-shadow: 
            inset 0 1px 0 rgba(255, 255, 255, 0.75),
            inset 0 -1px 0 rgba(255, 255, 255, 0.15),
            inset 0 0 28px rgba(255, 255, 255, 0.06),
            0 32px 70px -15px rgba(0, 0, 0, 0.65);
        overflow: hidden;
        z-index: 10;
    }

    /* Specular rim: terang di kiri-atas dan kanan-bawah */
    .login-card::before {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: inherit;
        padding: 1px;
        background: linear-gradient(155deg,
            rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.08) 35%,
            rgba(255, 255, 255, 0.05) 65%, rgba(255, 255, 255, 0.55) 100%);
        -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        pointer-events: none;
        z-index: 2;
    }

    .login-card .brand {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-bottom: 24px;
    }

    .login-card .brand img {
        width: 150px;
        height: 150px;
        object-fit: contain;
        background: transparent;
        margin-bottom: 8px;
        filter: drop-shadow(0 10px 24px rgba(0, 0, 0, 0.45));
    }

    .field { margin-bottom: 18px; }

    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 6px;
        letter-spacing: -0.01em;
    }

    .input-wrap { position: relative; }
    .input-wrap > i {
        position: absolute;
        left: 14px; top: 50%;
        transform: translateY(-50%);
        color: rgba(255, 255, 255, 0.55);
        font-size: 14px;
        pointer-events: none;
    }

    .form-control {
        width: 100%;
        padding: 12px 14px 12px 42px;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 16px;
        font-size: 14px;
        color: #ffffff;
        font-family: 'Inter', sans-serif;
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.2);
        transition: all 0.22s ease;
    }

    .form-control::placeholder {
        color: rgba(255, 255, 255, 0.42);
    }

    .form-control:focus {
        outline: none;
        border-color: #f97316;
        background: rgba(255, 255, 255, 0.14);
        box-shadow: 0 0 0 3.5px rgba(249, 115, 22, 0.28), inset 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .form-control.is-invalid {
        border-color: #ef4444;
        background: rgba(239, 68, 68, 0.12);
    }
    .invalid-feedback {
        color: #fca5a5;
        font-size: 12px;
        margin-top: 5px;
        font-weight: 500;
    }

    .toggle-password {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: rgba(255, 255, 255, 0.6);
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
    .toggle-password:hover { color: #ffffff; }

    .form-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 0 0 24px;
    }
    .form-row label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: rgba(255, 255, 255, 0.85);
        cursor: pointer;
    }
    .form-row input[type="checkbox"] {
        accent-color: #ea580c;
        width: 16px;
        height: 16px;
        cursor: pointer;
    }
    .form-row a {
        font-size: 13px;
        color: #fb923c;
        font-weight: 600;
        text-decoration: none;
        transition: color 0.15s;
    }
    .form-row a:hover {
        color: #fdba74;
        text-decoration: underline;
    }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        height: 52px;
        padding: 0 20px;
        border: none;
        border-radius: 26px;
        background: linear-gradient(180deg, #f97316 0%, #ea580c 55%, #c2410c 100%);
        color: #ffffff;
        font-size: 15px;
        font-weight: 700;
        font-family: 'Inter', sans-serif;
        cursor: pointer;
        box-shadow: 
            0 10px 24px -2px rgba(234, 88, 12, 0.55),
            inset 0 1.5px 2px rgba(255, 255, 255, 0.5),
            inset 0 -2px 4px rgba(0, 0, 0, 0.25);
        transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .btn-primary:hover {
        transform: translateY(-1.5px);
        box-shadow: 
            0 14px 30px -2px rgba(234, 88, 12, 0.65),
            inset 0 1.5px 2px rgba(255, 255, 255, 0.6);
    }
    .btn-primary:active {
        transform: translateY(0.5px);
        box-shadow: 0 6px 14px rgba(234, 88, 12, 0.45);
    }

    .alert {
        padding: 12px 16px;
        border-radius: 14px;
        font-size: 13px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 18px;
    }
    .alert-success {
        background: rgba(16, 185, 129, 0.18);
        color: #6ee7b7;
        border: 1px solid rgba(16, 185, 129, 0.35);
    }

    .form-meta {
        margin-top: 24px;
        padding-top: 18px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        text-align: center;
        font-size: 12px;
        color: rgba(255, 255, 255, 0.45);
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