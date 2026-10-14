<x-guest-layout>
    <style>
        /* ===== CLEAN ERROR MODAL ===== */
        .login-modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.45);
            -webkit-backdrop-filter: blur(6px);
            backdrop-filter: blur(6px);
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 20px;
            animation: fadeInOverlay 0.2s ease-out;
        }

        .login-modal-box {
            position: relative;
            width: 100%;
            max-width: 420px;
            border-radius: 24px;
            padding: 32px 28px 26px;
            text-align: center;
            background: #ffffff;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25);
            animation: popInModal 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden;
        }

        .login-modal-close {
            position: absolute;
            top: 18px;
            right: 18px;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #f1f5f9;
            border: none;
            color: #64748b;
            font-size: 13px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            z-index: 10;
        }
        .login-modal-close:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .login-modal-icon-wrap {
            display: flex;
            justify-content: center;
            margin-bottom: 18px;
        }

        .login-modal-icon-circle {
            width: 68px;
            height: 68px;
            border-radius: 50%;
            background: #fee2e2;
            border: 2px solid #fecaca;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #dc2626;
            font-size: 26px;
        }

        @keyframes fadeInOverlay {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes popInModal {
            from { opacity: 0; transform: scale(0.95) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .login-modal-title {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 8px 0;
            letter-spacing: -0.01em;
        }

        .login-modal-desc {
            font-size: 13.5px;
            color: #64748b;
            line-height: 1.55;
            margin: 0 0 20px 0;
            padding: 0 4px;
        }

        .login-modal-tips {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 14px 16px;
            margin-bottom: 22px;
            text-align: left;
            font-size: 12.5px;
            color: #475569;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .login-modal-tip-item {
            display: flex;
            align-items: center;
            gap: 10px;
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
            background: #fef3c7;
            border: 1px solid #fde68a;
            color: #d97706;
        }
        .tip-icon-success {
            background: #d1fae5;
            border: 1px solid #a7f3d0;
            color: #059669;
        }

        .btn-try-again {
            width: 100%;
            height: 46px;
            padding: 0 20px;
            border-radius: 12px;
            background: #bd3a13;
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(189, 58, 19, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .btn-try-again:hover {
            background: #a8320e;
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
            <div class="input-wrap @error('email') has-error @enderror">
                <i class="fas fa-envelope"></i>
                <input id="email" class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            </div>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="field">
            <label class="form-label" for="password">Password</label>
            <div class="input-wrap @error('password') has-error @enderror">
                <i class="fas fa-lock"></i>
                <input id="password" class="form-control @error('password') is-invalid @enderror" type="password" name="password" required autocomplete="current-password" style="padding-right:44px;">
                <button type="button" class="toggle-password" onclick="togglePassword()" tabindex="-1" aria-label="Tampilkan password">
                    <i id="toggle-icon" class="fas fa-eye"></i>
                </button>
            </div>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="form-row">
            <label for="remember_me">
                <input id="remember_me" type="checkbox" name="remember">
                Ingat Saya
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
    @push('modals')
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
                    <span>Periksa tombol <strong style="color:#0f172a;font-weight:700;">Caps Lock</strong> pada keyboard Anda.</span>
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
    @endpush

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