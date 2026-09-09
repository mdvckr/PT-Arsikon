<x-guest-layout>
    @if (session('status'))
    <div class="alert alert-success">
        <i class="fas fa-circle-check"></i>
        <div>{{ session('status') }}</div>
    </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
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

    <script>
        function togglePassword() {
            var input = document.getElementById('password');
            var icon = document.getElementById('toggle-icon');
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        }
    </script>
</x-guest-layout>