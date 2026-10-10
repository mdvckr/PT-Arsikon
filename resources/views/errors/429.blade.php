<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>429 - Terlalu Banyak Permintaan | PT ARSIKON CIPTA KARYA</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .container {
            width: 100%;
            max-width: 520px;
        }

        .card {
            background: rgba(30, 41, 59, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 40px 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 40px rgba(245, 158, 11, 0.1);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #f59e0b, #ef4444, #f59e0b);
        }

        .badge-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 24px;
            background: rgba(245, 158, 11, 0.15);
            border: 2px solid rgba(245, 158, 11, 0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 34px;
            color: #fbbf24;
            animation: pulse-ring 2.5s infinite;
        }

        @keyframes pulse-ring {
            0% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); }
            70% { box-shadow: 0 0 0 16px rgba(245, 158, 11, 0); }
            100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
        }

        .code {
            display: inline-block;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            padding: 4px 12px;
            background: rgba(245, 158, 11, 0.2);
            color: #fbbf24;
            border-radius: 9999px;
            margin-bottom: 12px;
        }

        h1 {
            font-size: 24px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 12px;
            line-height: 1.3;
        }

        p.description {
            font-size: 14px;
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .timer-box {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 28px;
        }

        .timer-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-bottom: 6px;
        }

        .countdown {
            font-size: 32px;
            font-weight: 800;
            color: #fbbf24;
            font-variant-numeric: tabular-nums;
        }

        .progress-bar-container {
            width: 100%;
            height: 6px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 9999px;
            margin-top: 14px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            width: 100%;
            background: linear-gradient(90deg, #f59e0b, #fbbf24);
            border-radius: 9999px;
            transition: width 1s linear;
        }

        .actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 22px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
            outline: none;
        }

        .btn-primary {
            background: #2563eb;
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
        }

        .btn-primary:hover:not(:disabled) {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        .btn-primary:disabled {
            background: #334155;
            color: #64748b;
            cursor: not-allowed;
            box-shadow: none;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.08);
            color: #cbd5e1;
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
        }

        .footer {
            margin-top: 24px;
            font-size: 12px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="badge-icon">
                <i class="fa-solid fa-shield-halved"></i>
            </div>

            <div class="code">HTTP 429 &bull; Rate Limit Reached</div>
            <h1>Batas Permintaan Terlampaui</h1>

            <p class="description">
                {{ $message ?? 'Sistem mendeteksi lonjakan aktivitas yang melebihi ambang batas wajar dari koneksi Anda. Fitur proteksi Anti-DDoS dan Rate Limiting diaktifkan secara otomatis untuk menjaga stabilitas sistem.' }}
            </p>

            <div class="timer-box">
                <div class="timer-label">Dapat mencoba kembali dalam</div>
                <div class="countdown" id="countdownTimer">--:--</div>
                <div class="progress-bar-container">
                    <div class="progress-bar-fill" id="progressBar"></div>
                </div>
            </div>

            <div class="actions">
                <button type="button" class="btn btn-primary" id="retryButton" onclick="window.location.reload();" disabled>
                    <i class="fa-solid fa-rotate-right"></i>
                    <span>Coba Lagi Sekarang</span>
                </button>

                <a href="{{ url('/') }}" class="btn btn-secondary">
                    <i class="fa-solid fa-house"></i>
                    <span>Beranda</span>
                </a>
            </div>

            <div class="footer">
                PT ARSIKON CIPTA KARYA &bull; Sistem Perlindungan Keamanan Terpadu
            </div>
        </div>
    </div>

    <script>
        (function() {
            var initialSeconds = parseInt('{{ $retryAfter ?? 60 }}', 10);
            if (isNaN(initialSeconds) || initialSeconds <= 0) {
                initialSeconds = 60;
            }

            var totalSeconds = initialSeconds;
            var remaining = initialSeconds;

            var countdownEl = document.getElementById('countdownTimer');
            var progressEl = document.getElementById('progressBar');
            var retryBtn = document.getElementById('retryButton');

            function updateDisplay() {
                var mins = Math.floor(remaining / 60);
                var secs = remaining % 60;
                var formatted = (mins > 0 ? (mins < 10 ? '0' : '') + mins + ':' : '') +
                                (secs < 10 ? '0' : '') + secs + (mins === 0 ? ' dtk' : '');

                countdownEl.textContent = formatted;

                var percent = (remaining / totalSeconds) * 100;
                progressEl.style.width = Math.max(0, percent) + '%';

                if (remaining <= 0) {
                    countdownEl.textContent = "Siap!";
                    countdownEl.style.color = "#10b981";
                    progressEl.style.width = '0%';
                    retryBtn.removeAttribute('disabled');
                    return;
                }

                remaining--;
                setTimeout(updateDisplay, 1000);
            }

            updateDisplay();
        })();
    </script>
</body>
</html>
