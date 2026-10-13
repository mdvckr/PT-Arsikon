<x-guest-layout>
    <style>
        /* ===== iOS 26 LIQUID GLASS LOGIN ERROR MODAL ===== */
        .login-modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at 50% 18%, rgba(234, 88, 12, 0.45) 0%, transparent 60%),
                        radial-gradient(circle at 50% 85%, rgba(29, 78, 216, 0.55) 0%, transparent 65%),
                        rgba(9, 13, 22, 0.75);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
            backdrop-filter: blur(24px) saturate(180%);
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 20px;
            animation: fadeInOverlay 0.28s ease-out;
        }

        .login-modal-box {
            position: relative;
            width: 100%;
            max-width: 440px;
            border-radius: 32px;
            padding: 34px 28px 28px;
            text-align: center;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.16) 0%, rgba(255, 255, 255, 0.05) 100%);
            -webkit-backdrop-filter: blur(36px) saturate(190%) brightness(1.08);
            backdrop-filter: blur(36px) saturate(190%) brightness(1.08);
            box-shadow: 
                inset 0 1px 0 rgba(255, 255, 255, 0.75),
                inset 0 -1px 0 rgba(255, 255, 255, 0.15),
                inset 0 0 28px rgba(255, 255, 255, 0.06),
                0 32px 70px -15px rgba(0, 0, 0, 0.65);
            animation: popInModal 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden;
        }

        /* Specular rim: terang di kiri-atas dan kanan-bawah */
        .login-modal-box::before {
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

        .login-modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.18);
            color: rgba(255, 255, 255, 0.75);
            font-size: 13px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            z-index: 10;
        }
        .login-modal-close:hover {
            background: rgba(255, 255, 255, 0.25);
            color: #ffffff;
            transform: scale(1.06);
        }

        .login-modal-icon-wrap {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        /* 3D Glossy Sphere Badge */
        .login-modal-icon-circle {
            width: 76px;
            height: 76px;
            border-radius: 50%;
            background: radial-gradient(circle at 35% 30%, #ff6b6b 0%, #ef4444 32%, #dc2626 65%, #991b1b 100%);
            box-shadow: 
                0 14px 30px -4px rgba(220, 38, 38, 0.65),
                0 6px 14px rgba(0, 0, 0, 0.45),
                inset 0 3px 6px rgba(255, 255, 255, 0.75),
                inset 0 -4px 8px rgba(0, 0, 0, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 28px;
            position: relative;
            animation: iconPulse 2.5s infinite ease-in-out;
        }

        .login-modal-icon-circle i {
            filter: drop-shadow(0 2px 5px rgba(0, 0, 0, 0.35));
        }

        @keyframes iconPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.04); }
        }

        @keyframes fadeInOverlay {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes popInModal {
            from { opacity: 0; transform: scale(0.92) translateY(16px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .login-modal-title {
            font-size: 22px;
            font-weight: 800;
            color: #ffffff;
            margin: 0 0 10px 0;
            letter-spacing: -0.02em;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.35);
        }

        .login-modal-desc {
            font-size: 13.5px;
            color: rgba(255, 255, 255, 0.82);
            line-height: 1.55;
            margin: 0 0 22px 0;
            padding: 0 6px;
        }

        .login-modal-tips {
            background: rgba(255, 255, 255, 0.07);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 20px;
            padding: 16px 18px;
            margin-bottom: 24px;
            text-align: left;
            font-size: 12.5px;
            color: rgba(255, 255, 255, 0.88);
            display: flex;
            flex-direction: column;
            gap: 12px;
            box-shadow: inset 0 1px 3px rgba(255, 255, 255, 0.08);
        }

        .login-modal-tip-item {
            display: flex;
            align-items: center;
            gap: 12px;
            line-height: 1.45;
        }

        .tip-icon-pill {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            flex-shrink: 0;
        }
        .tip-icon-warning {
            background: rgba(245, 158, 11, 0.22);
            border: 1px solid rgba(245, 158, 11, 0.45);
            color: #fbbf24;
        }
        .tip-icon-success {
            background: rgba(16, 185, 129, 0.22);
            border: 1px solid rgba(16, 185, 129, 0.45);
            color: #34d399;
        }

        .btn-try-again {
            width: 100%;
            height: 52px;
            padding: 0 20px;
            border-radius: 26px;
            background: linear-gradient(180deg, #f97316 0%, #ea580c 55%, #c2410c 100%);
            color: #ffffff;
            font-size: 14.5px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            box-shadow: 
                0 10px 24px -2px rgba(234, 88, 12, 0.55),
                inset 0 1.5px 2px rgba(255, 255, 255, 0.5),
                inset 0 -2px 4px rgba(0, 0, 0, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .btn-try-again:hover {
            transform: translateY(-1.5px);
            box-shadow: 
                0 14px 30px -2px rgba(234, 88, 12, 0.65),
                inset 0 1.5px 2px rgba(255, 255, 255, 0.6);
        }
        .btn-try-again:active {
            transform: translateY(0.5px);
            box-shadow: 0 6px 14px rgba(234, 88, 12, 0.45);
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

    {{-- Error Modal Popup saat Login Gagal / Kata Sandi Salah (iOS 26 Liquid Glass Style) --}}
    @if ($errors->any() || request()->has('preview_error'))
    <div id="loginErrorModal" class="login-modal-overlay" onclick="if(event.target === this) closeLoginErrorModal()">
        <div class="login-modal-box">
            <button type="button" class="login-modal-close" onclick="closeLoginErrorModal()" aria-label="Tutup">
                <i class="fas fa-xmark"></i>
            </button>
            
            <div class="login-modal-icon-wrap">
                <div class="login-modal-icon-circle">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
            </div>

            <h3 class="login-modal-title">Gagal masuk</h3>
            
            <p class="login-modal-desc">
                Kombinasi email atau kata sandi yang Anda masukkan salah. Silakan periksa kembali.
            </p>

            <div class="login-modal-tips">
                <div class="login-modal-tip-item">
                    <div class="tip-icon-pill tip-icon-warning">
                        <i class="fas fa-keyboard"></i>
                    </div>
                    <span>Periksa tombol <strong style="color:#ffffff;font-weight:700;">Caps Lock</strong> pada keyboard Anda.</span>
                </div>
                <div class="login-modal-tip-item">
                    <div class="tip-icon-pill tip-icon-success">
                        <i class="fas fa-check"></i>
                    </div>
                    <span>Pastikan alamat email dan kata sandi yang Anda ketik sudah tepat.</span>
                </div>
            </div>

            <div class="login-modal-actions">
                <button type="button" class="btn-try-again" onclick="closeLoginErrorModal()">
                    <i class="fas fa-rotate-right"></i>
                    <span>Coba masuk kembali</span>
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