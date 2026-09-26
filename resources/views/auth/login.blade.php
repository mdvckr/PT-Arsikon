<x-guest-layout>
    <style>
        /* Modal Popup Saat Login Gagal */
        .login-modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(5px);
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            animation: fadeInOverlay 0.25s ease-out;
        }

        .login-modal-box {
            background: #ffffff;
            width: 100%;
            max-width: 420px;
            border-radius: 18px;
            padding: 28px 24px 22px 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            text-align: center;
            position: relative;
            animation: popInModal 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid #fee2e2;
        }

        .login-modal-close {
            position: absolute;
            top: 14px;
            right: 16px;
            background: none;
            border: none;
            font-size: 22px;
            color: #94a3b8;
            cursor: pointer;
            line-height: 1;
            padding: 4px;
            transition: color 0.15s;
        }
        .login-modal-close:hover { color: #1e293b; }

        .login-modal-icon-wrap {
            display: flex;
            justify-content: center;
            margin-bottom: 16px;
        }

        .login-modal-icon-circle {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #fef2f2;
            border: 4px solid #fee2e2;
            color: #dc2626;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            box-shadow: 0 8px 18px rgba(220, 38, 38, 0.18);
            animation: iconPulse 2s infinite ease-in-out;
        }

        @keyframes iconPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.06); }
        }

        @keyframes fadeInOverlay {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes popInModal {
            from { opacity: 0; transform: scale(0.9) translateY(12px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .login-modal-title {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 8px 0;
            letter-spacing: -0.01em;
        }

        .login-modal-desc {
            font-size: 13.5px;
            color: #475569;
            line-height: 1.5;
            margin: 0 0 16px 0;
        }

        .login-modal-tips {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 20px;
            text-align: left;
            font-size: 12px;
            color: #475569;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .login-modal-tip-item {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            line-height: 1.4;
        }

        .login-modal-tip-item i {
            margin-top: 2px;
            font-size: 12px;
            flex-shrink: 0;
        }

        .btn-try-again {
            width: 100%;
            padding: 11px 16px;
            border-radius: 10px;
            background: linear-gradient(135deg, #c2410c, #ea580c);
            color: #ffffff;
            font-size: 13.5px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(234, 88, 12, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s ease;
        }

        .btn-try-again:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(234, 88, 12, 0.4);
        }
    </style>

    @if (session('status'))
    <div class="alert alert-success">
        <i class="fas fa-circle-check"></i>
        <div>{{ session('status') }}</div>
    </div>
    @endif

    <form method="POST" action="{{ route('login') }}" onsubmit="const btn = this.querySelector('button[type=submit]'); if (btn) { btn.disabled = true; btn.style.opacity = '0.7'; }">
        @csrf

        <!-- Email Address -->
        <div class="field">
            <label class="form-label" for="email">Email</label>
            <div class="input-wrap">
                <i class="fas fa-envelope"></i>
                <input id="email" class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email') }}" placeholder="nama@perusahaan.com" required autofocus autocomplete="username">
            </div>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="field">
            <label class="form-label" for="password">Password</label>
            <div class="input-wrap">
                <i class="fas fa-lock"></i>
                <input id="password" class="form-control @error('password') is-invalid @enderror" type="password" name="password" placeholder="Masukkan password" required autocomplete="current-password" style="padding-right:44px;">
                <button type="button" class="toggle-password" onclick="togglePassword()" tabindex="-1" aria-label="Tampilkan password">
                    <i id="toggle-icon" class="fas fa-eye"></i>
                </button>
            </div>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="form-row">
            <label for="remember_me">
                <input id="remember_me" type="checkbox" name="remember">
                Ingat saya
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}">Lupa Password?</a>
            @endif
        </div>

        <button type="submit" class="btn-primary">
            <i class="fas fa-right-to-bracket"></i> Masuk
        </button>
    </form>

    <div class="form-meta">
        &copy; {{ date('Y') }} PT ARSIKON CIPTA KARYA
    </div>

    {{-- Error Modal Popup saat Login Gagal / Kata Sandi Salah --}}
    @if ($errors->any())
    <div id="loginErrorModal" class="login-modal-overlay" onclick="if(event.target === this) closeLoginErrorModal()">
        <div class="login-modal-box">
            <button type="button" class="login-modal-close" onclick="closeLoginErrorModal()">&times;</button>
            
            <div class="login-modal-icon-wrap">
                <div class="login-modal-icon-circle">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
            </div>

            <h3 class="login-modal-title">Gagal Masuk</h3>
            
            <p class="login-modal-desc">
                @if ($errors->has('email'))
                    {{ $errors->first('email') }}
                @elseif ($errors->has('password'))
                    {{ $errors->first('password') }}
                @else
                    {{ $errors->first() }}
                @endif
            </p>

            <div class="login-modal-tips">
                <div class="login-modal-tip-item">
                    <i class="fas fa-keyboard text-warning" style="color:#d97706;"></i>
                    <span>Periksa tombol <strong>Caps Lock</strong> pada keyboard Anda.</span>
                </div>
                <div class="login-modal-tip-item">
                    <i class="fas fa-circle-check text-success" style="color:#10b981;"></i>
                    <span>Pastikan alamat email dan kata sandi yang Anda ketik sudah tepat.</span>
                </div>
            </div>

            <div class="login-modal-actions">
                <button type="button" class="btn-try-again" onclick="closeLoginErrorModal()">
                    <i class="fas fa-rotate-right me-1"></i> Coba Masuk Kembali
                </button>
            </div>
        </div>
    </div>
    @endif

    <script>
        function togglePassword() {
            var input = document.getElementById('password');
            var icon = document.getElementById('toggle-icon');
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        }

        function closeLoginErrorModal() {
            var modal = document.getElementById('loginErrorModal');
            if (modal) {
                modal.style.display = 'none';
                var passInput = document.getElementById('password');
                if (passInput) {
                    passInput.focus();
                    passInput.select();
                }
            }
        }

        // Close popup with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLoginErrorModal();
            }
        });
    </script>
</x-guest-layout>