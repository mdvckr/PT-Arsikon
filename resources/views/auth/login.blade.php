<x-guest-layout>
    <style>
        /* ===== ACCESSIBLE ALERT BANNERS ===== */
        .alert-error-banner {
            background: #fef2f2;
            border: 1.5px solid #fecaca;
            color: #991b1b;
            padding: 13px 16px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.08);
            line-height: 1.45;
        }

        .alert-info-banner {
            background: #eff6ff;
            border: 1.5px solid #bfdbfe;
            color: #1e40af;
            padding: 13px 16px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.08);
            line-height: 1.45;
        }

        /* ===== 3-STATE INTERACTIVE LOGIN BUTTON ===== */
        .btn-login-interactive {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            height: 50px;
            padding: 0 20px;
            border: none;
            border-radius: 14px;
            background: #bd3a13;
            color: #ffffff;
            font-size: 15px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(189, 58, 19, 0.28);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            user-select: none;
        }

        .btn-login-interactive:hover:not(:disabled) {
            background: #a8320e;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(189, 58, 19, 0.38);
        }

        .btn-login-interactive:active:not(:disabled) {
            transform: translateY(0);
        }

        .btn-login-interactive:disabled {
            cursor: not-allowed;
            opacity: 0.65;
            box-shadow: none;
            transform: none;
        }

        .btn-login-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            transition: transform 0.2s ease;
        }

        /* State 2: Saat proses login (Memverifikasi...) */
        .btn-login-interactive.is-loading {
            background: #bd3a13 !important;
            opacity: 0.94;
            cursor: wait !important;
            pointer-events: none;
            box-shadow: 0 4px 14px rgba(189, 58, 19, 0.25);
        }

        /* State 3: Setelah berhasil (Login Berhasil) - Hijau Emerald */
        .btn-login-interactive.is-success {
            background: #196c4e !important;
            color: #ffffff !important;
            cursor: default !important;
            pointer-events: none;
            box-shadow: 0 6px 20px rgba(25, 108, 78, 0.42) !important;
            transform: scale(1.008);
        }

        /* ===== HIGH VISIBILITY SHAKE ANIMATION ===== */
        @keyframes shakeX {
            0%, 100% {
                transform: translate3d(0, 0, 0);
            }
            10%, 30%, 50%, 70%, 90% {
                transform: translate3d(-18px, 0, 0);
            }
            20%, 40%, 60%, 80% {
                transform: translate3d(18px, 0, 0);
            }
        }

        .shake-error {
            animation: shakeX 0.7s ease-in-out both !important;
            will-change: transform;
        }
    </style>

    {{-- L5: Sesi Berakhir Notifikasi --}}
    @if (request()->has('session_expired'))
    <div role="status" class="alert-info-banner">
        <x-lucide-info class="w-5 h-5 flex-shrink-0 text-blue-600" />
        <span style="font-size:13.5px; font-weight:500;">Sesi Anda sudah berakhir. Silakan masuk kembali.</span>
    </div>
    @elseif (session('status'))
    <div class="alert alert-success">
        <x-lucide-circle-check />
        <div>{{ session('status') }}</div>
    </div>
    @endif

    {{-- L1, L3, L4 Global Server Error Alert --}}
    @if ($errors->has('email') && !request()->has('session_expired'))
    <div id="loginGlobalAlert" role="alert" class="alert-error-banner">
        <x-lucide-circle-alert class="w-5 h-5 flex-shrink-0 text-red-600" />
        <span id="loginGlobalAlertText" style="font-size:13.5px; font-weight:500;">{{ $errors->first('email') }}</span>
    </div>
    @else
    <div id="loginGlobalAlert" role="alert" class="alert-error-banner" style="display: none;">
        <x-lucide-circle-alert class="w-5 h-5 flex-shrink-0 text-red-600" />
        <span id="loginGlobalAlertText" style="font-size:13.5px; font-weight:500;"></span>
    </div>
    @endif

    <form id="loginForm" method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <!-- Email Address -->
        <div class="field">
            <label class="form-label" for="email">Email</label>
            <div id="emailWrap" class="input-wrap @error('email') has-error @enderror">
                <x-lucide-mail />
                <input id="email" 
                       class="form-control @error('email') is-invalid @enderror" 
                       type="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       required 
                       autocomplete="username"
                       aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                       aria-describedby="email-error">
            </div>
            @error('email')
                <div id="email-error" role="alert" class="invalid-feedback">{{ $message }}</div>
            @else
                <div id="email-error" role="alert" class="invalid-feedback" style="display: none;"></div>
            @enderror
        </div>

        <!-- Password -->
        <div class="field">
            <label class="form-label" for="password">Password</label>
            <div id="passwordWrap" class="input-wrap @if($errors->has('password') || $errors->has('email')) has-error @endif">
                <x-lucide-lock />
                <input id="password" 
                       class="form-control @if($errors->has('password') || $errors->has('email')) is-invalid @endif" 
                       type="password" 
                       name="password" 
                       required 
                       autocomplete="current-password" 
                       style="padding-right:44px;"
                       aria-invalid="{{ ($errors->has('password') || $errors->has('email')) ? 'true' : 'false' }}"
                       aria-describedby="password-error">
                <button type="button" class="toggle-password" onclick="togglePassword()" tabindex="-1" aria-label="Tampilkan password">
                    <span id="iconEye"><x-lucide-eye class="w-4 h-4" /></span>
                    <span id="iconEyeOff" style="display:none;"><x-lucide-eye-off class="w-4 h-4" /></span>
                </button>
            </div>
            @error('password')
                <div id="password-error" role="alert" class="invalid-feedback">{{ $message }}</div>
            @else
                <div id="password-error" role="alert" class="invalid-feedback" style="display: none;"></div>
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

        <!-- Tombol Login Rekomendasi 3-State -->
        <button type="submit" id="btnLogin" class="btn-primary btn-login-interactive">
            <span id="btnLoginIcon" class="btn-login-icon" style="display: none;"></span>
            <span id="btnLoginText">Masuk</span>
        </button>
    </form>

    <div class="form-meta">
        &copy; {{ date('Y') }} PT ARSIKON CIPTA KARYA
    </div>

    <script>
        function togglePassword() {
            var input = document.getElementById('password');
            var iconEye = document.getElementById('iconEye');
            var iconEyeOff = document.getElementById('iconEyeOff');
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            if (iconEye && iconEyeOff) {
                iconEye.style.display = show ? 'none' : 'inline-block';
                iconEyeOff.style.display = show ? 'inline-block' : 'none';
            }
        }

        function triggerCardShake() {
            const card = document.querySelector('.login-card');
            const alertBox = document.getElementById('loginGlobalAlert');
            const emailWrap = document.getElementById('emailWrap');
            const passWrap = document.getElementById('passwordWrap');

            const targets = [card, alertBox, emailWrap, passWrap].filter(Boolean);

            targets.forEach(function (el) {
                el.classList.remove('shake-error');
                void el.offsetWidth; // Force synchronous browser DOM reflow
                el.classList.add('shake-error');
            });
        }

        // Accessibility & Autofocus Management
        document.addEventListener('DOMContentLoaded', function () {
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const emailErrorMsg = @json($errors->first('email') ?? '');
            const isRateLimited = emailErrorMsg.includes('Terlalu banyak percobaan');

            // Remove shake-error after animation finishes so it can re-trigger on subsequent clicks
            document.addEventListener('animationend', function (e) {
                if (e.target && e.target.classList && e.target.classList.contains('shake-error')) {
                    e.target.classList.remove('shake-error');
                }
            });

            // Trigger noticeable shake after initial paint
            const hasServerError = {{ ($errors->any() && !request()->has('session_expired')) ? 'true' : 'false' }};
            if (hasServerError) {
                setTimeout(function () {
                    triggerCardShake();
                }, 120);
            }

            if (isRateLimited) {
                const submitBtn = document.getElementById('btnLogin');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.6';
                    submitBtn.style.cursor = 'not-allowed';
                }
            } else if ({{ $errors->any() ? 'true' : 'false' }}) {
                if (emailInput && emailInput.value.trim() !== '') {
                    passwordInput.focus();
                    passwordInput.select();
                } else if (emailInput) {
                    emailInput.focus();
                }
            } else if (emailInput) {
                emailInput.focus();
            }
        });

        // 3-State Button & AJAX Transition Management
        (function() {
            const loginForm = document.getElementById('loginForm');
            const btnLogin = document.getElementById('btnLogin');
            const btnLoginIcon = document.getElementById('btnLoginIcon');
            const btnLoginText = document.getElementById('btnLoginText');

            function setButtonState(state) {
                if (!btnLogin || !btnLoginIcon || !btnLoginText) return;

                if (state === 'loading') {
                    btnLogin.disabled = true;
                    btnLogin.classList.remove('is-success');
                    btnLogin.classList.add('is-loading');
                    btnLoginIcon.style.display = 'inline-flex';
                    btnLoginIcon.innerHTML = `<x-lucide-loader-2 class="animate-spin" />`;
                    btnLoginText.textContent = 'Memverifikasi...';
                } else if (state === 'success') {
                    btnLogin.disabled = true;
                    btnLogin.classList.remove('is-loading');
                    btnLogin.classList.add('is-success');
                    btnLoginIcon.style.display = 'inline-flex';
                    btnLoginIcon.innerHTML = `<x-lucide-circle-check />`;
                    btnLoginText.textContent = 'Login Berhasil';
                } else {
                    btnLogin.disabled = false;
                    btnLogin.classList.remove('is-loading', 'is-success');
                    btnLoginIcon.style.display = 'none';
                    btnLoginIcon.innerHTML = '';
                    btnLoginText.textContent = 'Masuk';
                }
            }

            if (loginForm) {
                loginForm.addEventListener('submit', async function(e) {
                    const emailInput = document.getElementById('email');
                    const passwordInput = document.getElementById('password');

                    // Client validation for empty fields (L2)
                    if (!emailInput.value.trim()) {
                        e.preventDefault();
                        showInlineError('Email wajib diisi.', 'email');
                        emailInput.focus();
                        return;
                    }

                    if (!passwordInput.value) {
                        e.preventDefault();
                        showInlineError('Kata sandi wajib diisi.', 'password');
                        passwordInput.focus();
                        return;
                    }

                    e.preventDefault();
                    setButtonState('loading');

                    const formData = new FormData(loginForm);

                    try {
                        const response = await fetch(loginForm.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });

                        if (response.ok) {
                            const data = await response.json().catch(() => ({}));
                            setButtonState('success');
                            setTimeout(function() {
                                window.location.href = data.redirect || "{{ route('dashboard') }}";
                            }, 550);
                            return;
                        }

                        // Jika gagal (422 / 429)
                        const data = await response.json().catch(() => ({}));
                        setButtonState('normal');

                        const errorMsg = (data.errors && data.errors.email ? data.errors.email[0] : null) ||
                            (data.errors && data.errors.password ? data.errors.password[0] : null) ||
                            data.message ||
                            'Email atau kata sandi salah.';

                        showInlineError(errorMsg, (data.errors && data.errors.password) ? 'password' : 'email');

                        if (response.status === 429 || errorMsg.includes('Terlalu banyak percobaan')) {
                            if (btnLogin) {
                                btnLogin.disabled = true;
                                btnLogin.style.opacity = '0.6';
                                btnLogin.style.cursor = 'not-allowed';
                            }
                        } else {
                            if (emailInput && emailInput.value.trim() !== '') {
                                passwordInput.focus();
                                passwordInput.select();
                            } else if (emailInput) {
                                emailInput.focus();
                            }
                        }
                    } catch (err) {
                        setButtonState('loading');
                        loginForm.submit();
                    }
                });
            }

            function showInlineError(msg, targetField) {
                const alertBox = document.getElementById('loginGlobalAlert');
                const alertText = document.getElementById('loginGlobalAlertText');
                if (alertBox && alertText) {
                    alertText.textContent = msg;
                    alertBox.style.display = 'flex';
                }

                const emailWrap = document.getElementById('emailWrap');
                const emailInput = document.getElementById('email');
                const passWrap = document.getElementById('passwordWrap');
                const passInput = document.getElementById('password');

                if (targetField === 'email' || targetField === 'both') {
                    if (emailWrap) emailWrap.classList.add('has-error');
                    if (emailInput) {
                        emailInput.classList.add('is-invalid');
                        emailInput.setAttribute('aria-invalid', 'true');
                    }
                }

                if (targetField === 'password' || targetField === 'both' || targetField === 'email') {
                    if (passWrap) passWrap.classList.add('has-error');
                    if (passInput) {
                        passInput.classList.add('is-invalid');
                        passInput.setAttribute('aria-invalid', 'true');
                    }
                }

                // Trigger single shake animation
                triggerCardShake();
            }
        })();
    </script>
</x-guest-layout>
