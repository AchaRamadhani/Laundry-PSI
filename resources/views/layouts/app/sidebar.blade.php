<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
</head>
<body class="admin-body">
    <div class="admin-shell">
        <aside class="admin-sidebar" id="laundrySidebar">
            <a class="admin-brand" href="{{ route('dashboard') }}" aria-label="G'Laundry, dashboard">
                <img src="{{ asset('images/logo-g-laundry.png') }}" alt="Logo G'Laundry">
            </a>

            <nav class="admin-nav" aria-label="Navigasi admin">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 10 9-7 9 7"/><path d="M5 9.5V21h14V9.5M9 21v-7h6v7"/></svg>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('cucian.index') }}" class="{{ request()->routeIs('cucian.*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="5" width="14" height="16" rx="1.5"/><path d="M9 5V3h6v2M8 10h8M8 14h8M8 18h5"/></svg>
                    <span>Data Cucian</span>
                </a>
                <a href="{{ route('transaksi.index') }}" class="{{ request()->routeIs('transaksi.*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h2l2.4 11h11L21 8H6"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
                    <span>Transaksi</span>
                </a>
                <a href="{{ route('update-status.index') }}" class="{{ request()->routeIs('update-status.*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 7v5h-5M4 17v-5h5"/><path d="M5.5 9A7.5 7.5 0 0 1 18 6l2 2M4 16l2 2a7.5 7.5 0 0 0 12.5-3"/></svg>
                    <span>Update Status</span>
                </a>
                <a href="{{ route('pelanggan.index') }}" class="{{ request()->routeIs('pelanggan.*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span>Pelanggan</span>
                </a>
                <a href="{{ route('paket-laundry.index') }}" class="{{ request()->routeIs('paket-laundry.*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 9 4.5v9L12 21l-9-4.5v-9L12 3Z"/><path d="m3.5 7.7 8.5 4.6 8.5-4.6M12 21v-8.7"/></svg>
                    <span>Paket Laundry</span>
                </a>
                <a href="{{ route('segmentasi.index') }}" class="{{ request()->routeIs('segmentasi.*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M11 3a9 9 0 1 0 10 10H11V3Z"/><path d="M14 3.3V10h6.7A9 9 0 0 0 14 3.3Z"/></svg>
                    <span>Segmentasi</span>
                </a>
                <a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m19.4 13 .1-2 2-1.5-2-3.5-2.4 1a8 8 0 0 0-1.7-1L15 3H9l-.4 3a8 8 0 0 0-1.7 1l-2.4-1-2 3.5 2 1.5v2l-2 1.5 2 3.5 2.4-1a8 8 0 0 0 1.7 1l.4 3h6l.4-3a8 8 0 0 0 1.7-1l2.4 1 2-3.5z"/><circle cx="12" cy="12" r="3.5"/></svg>
                    <span>Pengaturan</span>
                </a>
            </nav>

            <div class="admin-sidebar-bottom">
                <form method="POST" action="{{ route('logout') }}" id="adminLogoutForm">
                    @csrf
                    <button class="admin-logout" type="button" onclick="requestAdminLogout(this)">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17l5-5-5-5M15 12H3M13 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/></svg>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <div class="admin-overlay" id="laundryOverlay" onclick="toggleLaundrySidebar()"></div>

        <div class="admin-workspace">
            <header class="admin-header">
                <button class="admin-menu-toggle" type="button" onclick="toggleLaundrySidebar()" aria-label="Buka menu admin" aria-expanded="false">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>

                <label class="admin-search">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m16 16 5 5"/></svg>
                    <input type="search" placeholder="Cari pelanggan, nota, atau layanan..." aria-label="Cari pelanggan, nota, atau layanan">
                </label>

                <div class="admin-profile" role="group" aria-label="Akun admin">
                    <span class="admin-avatar" aria-hidden="true"><svg viewBox="0 0 32 32"><circle cx="16" cy="11" r="6"/><path d="M5 29v-3a11 11 0 0 1 22 0v3z"/></svg></span>
                    <span class="admin-profile-text"><strong>{{ auth()->user()->name ?? 'Admin Laundry' }}</strong><small>Administrator</small></span>
                </div>
            </header>

            <main class="admin-main">
                {{ $slot }}
            </main>
        </div>
    </div>

    <div class="logout-confirm-backdrop" id="logoutConfirmModal" hidden>
        <section class="logout-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="logoutConfirmTitle" aria-describedby="logoutConfirmDescription" tabindex="-1">
            <img class="logout-confirm-logo" src="{{ asset('images/logo-g-laundry.png') }}" alt="Logo G'Laundry">
            <h2 id="logoutConfirmTitle">Keluar dari Sistem?</h2>
            <p id="logoutConfirmDescription">Apakah Anda yakin logout dari akun<br class="logout-confirm-desktop-break"> admin G'Laundry?</p>
            <div class="logout-confirm-divider"></div>
            <div class="logout-confirm-actions">
                <button class="logout-cancel-button" type="button" onclick="closeAdminLogoutModal()">Batal</button>
                <button class="logout-accept-button" type="button" onclick="submitAdminLogout()">Ya, logout</button>
            </div>
            <div class="logout-confirm-divider logout-confirm-footer-divider"></div>
            <div class="logout-confirm-note">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22s9-4.2 9-11V5l-9-3-9 3v6c0 6.8 9 11 9 11Z"/><path d="m8 12 2.5 2.5L16 9"/></svg>
                <span>Pastikan perubahan yang diperlukan sudah disimpan sebelum Anda logout.</span>
            </div>
        </section>
    </div>

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts

    <script>
        let logoutTriggerButton = null;

        function requestAdminLogout(button) {
            try {
                if (localStorage.getItem('g-laundry-confirm-logout') === 'false') {
                    document.getElementById('adminLogoutForm').requestSubmit();
                    return;
                }
            } catch (error) {
                // Show the dialog if browser storage is unavailable.
            }

            logoutTriggerButton = button;
            const modal = document.getElementById('logoutConfirmModal');
            modal.hidden = false;
            document.body.classList.add('logout-modal-open');
            modal.querySelector('.logout-cancel-button').focus();
        }

        function closeAdminLogoutModal() {
            const modal = document.getElementById('logoutConfirmModal');
            modal.hidden = true;
            document.body.classList.remove('logout-modal-open');
            logoutTriggerButton?.focus();
        }

        function submitAdminLogout() {
            document.getElementById('adminLogoutForm').requestSubmit();
        }

        document.getElementById('logoutConfirmModal').addEventListener('click', (event) => {
            if (event.target === event.currentTarget) closeAdminLogoutModal();
        });

        document.addEventListener('keydown', (event) => {
            const modal = document.getElementById('logoutConfirmModal');
            if (modal.hidden) return;

            if (event.key === 'Escape') {
                closeAdminLogoutModal();
                return;
            }

            if (event.key === 'Tab') {
                const focusable = [...modal.querySelectorAll('button:not([disabled])')];
                const first = focusable[0];
                const last = focusable[focusable.length - 1];

                if (event.shiftKey && document.activeElement === first) {
                    event.preventDefault();
                    last.focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first.focus();
                }
            }
        });

        function toggleLaundrySidebar() {
            const sidebar = document.getElementById('laundrySidebar');
            const overlay = document.getElementById('laundryOverlay');
            const toggle = document.querySelector('.admin-menu-toggle');
            const isOpen = sidebar.classList.toggle('open');
            overlay.classList.toggle('show', isOpen);
            toggle?.setAttribute('aria-expanded', String(isOpen));
        }
    </script>
</body>
</html>
