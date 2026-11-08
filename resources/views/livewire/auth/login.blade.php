<x-layouts::auth.admin-login :title="__('Login Admin')">
    <div class="admin-login-content">
        <a class="admin-login-brand" href="{{ route('home') }}" aria-label="G'Laundry, kembali ke beranda">
            <img src="{{ asset('images/logo-g-laundry.png') }}" alt="Logo G'Laundry">
        </a>

        <div class="admin-login-heading">
            <h1>Login Admin G'Laundry</h1>
            <p>Silahkan login untuk mengakses dashboard admin, mengelola data cucian, dan memantau transaksi</p>
        </div>

        <x-auth-session-status class="admin-login-status" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="admin-login-form">
            @csrf

            <div class="admin-login-field-wrap">
                <label class="sr-only" for="login-username">Username</label>
                <span class="admin-login-field-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2z"/></svg>
                </span>
                <input
                    id="login-username"
                    name="email"
                    type="text"
                    value="{{ old('email') }}"
                    placeholder="Username"
                    autocomplete="username"
                    required
                    autofocus
                    aria-describedby="login-username-error"
                >
            </div>
            @error('email')
                <p class="admin-login-error" id="login-username-error">{{ $message }}</p>
            @enderror

            <div class="admin-login-field-wrap">
                <label class="sr-only" for="login-password">Password</label>
                <span class="admin-login-field-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/></svg>
                </span>
                <input
                    id="login-password"
                    name="password"
                    type="password"
                    placeholder="Password"
                    autocomplete="current-password"
                    required
                    aria-describedby="login-password-error"
                >
                <button class="password-toggle" type="button" aria-label="Tampilkan password" aria-pressed="false" data-password-toggle>
                    <svg class="eye-open" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg class="eye-closed" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 5.2A10.9 10.9 0 0 1 12 5c6.4 0 10 7 10 7a16.4 16.4 0 0 1-3.2 4.1M6.2 6.2C3.5 8.1 2 12 2 12s3.6 7 10 7a10 10 0 0 0 3.3-.5"/></svg>
                </button>
            </div>
            @error('password')
                <p class="admin-login-error" id="login-password-error">{{ $message }}</p>
            @enderror

            <div class="admin-login-options">
                <label class="remember-option" for="remember">
                    <input id="remember" name="remember" type="checkbox" value="1" @checked(old('remember'))>
                    <span>Ingat saya</span>
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" wire:navigate>Lupa password?</a>
                @endif
            </div>

            <button class="admin-login-submit" type="submit" data-test="login-button">
                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/></svg>
                Login Admin
            </button>
        </form>

        <a class="admin-login-back" href="{{ route('home') }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m14 18-6-6 6-6M8 12h12"/></svg>
            Kembali ke Beranda
        </a>

        <div class="admin-login-notice" role="note">
            <span class="notice-icon" aria-hidden="true">i</span>
            <p>Halaman ini hanya dapat diakses oleh admin/pemilik laundry.</p>
        </div>
    </div>

    <script>
        const passwordToggle = document.querySelector('[data-password-toggle]');
        const passwordField = document.getElementById('login-password');

        passwordToggle?.addEventListener('click', () => {
            const showPassword = passwordField.type === 'password';
            passwordField.type = showPassword ? 'text' : 'password';
            passwordToggle.setAttribute('aria-pressed', String(showPassword));
            passwordToggle.setAttribute('aria-label', showPassword ? 'Sembunyikan password' : 'Tampilkan password');
            passwordToggle.classList.toggle('is-visible', showPassword);
        });
    </script>
</x-layouts::auth.admin-login>
