<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="Selamat datang di G'Laundry, layanan laundry yang praktis dan terpercaya untuk pakaian Anda.">
    <title>G'Laundry | Selamat Datang</title>
    <link rel="icon" href="{{ asset('images/logo-g-laundry.png') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}?v={{ filemtime(public_path('css/landing.css')) }}">
</head>
<body>
    <div class="site-frame">
        <header class="site-header">
            <a class="brand" href="{{ route('home') }}" aria-label="G'Laundry, beranda">
                <img src="{{ asset('images/logo-g-laundry.png') }}" alt="G'Laundry">
            </a>

            <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-nav" aria-label="Buka navigasi">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>

            <nav class="primary-nav" id="primary-nav" aria-label="Navigasi utama">
                <a class="active" href="{{ route('home') }}" aria-current="page">Beranda</a>
                <a href="{{ route('tracking') }}">Tracking Cucian</a>
                <a href="{{ route('layanan.index') }}">Layanan</a>
                <a href="{{ route('kontak.index') }}">Kontak</a>
            </nav>

            <a class="admin-button" href="{{ route('login') }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/></svg>
                Login Admin
            </a>
        </header>

        <main class="welcome-main">
            <section class="hero welcome-hero" id="beranda" aria-labelledby="hero-title">
                <div class="hero-copy welcome-hero-copy">
                    <div class="hero-text">
                        <p class="eyebrow">Laundry bersih, rapi, dan wangi</p>
                        <h1 id="hero-title">Selamat Datang di <span>G'Laundry</span></h1>
                        <p class="hero-description">Kami merawat pakaian harian Anda dengan teliti agar bersih, nyaman, dan siap dipakai kembali.</p>
                        <div class="welcome-actions">
                            <a class="welcome-primary" href="{{ route('layanan.index') }}">Lihat Layanan</a>
                            <a class="welcome-secondary" href="{{ route('tracking') }}">Lacak Cucian</a>
                            <a class="welcome-contact" href="{{ route('kontak.index') }}"><x-icons.whatsapp /> Hubungi Admin</a>
                        </div>
                    </div>
                </div>
                <img class="hero-image" src="{{ asset('images/laundry-hero.png') }}" alt="Handuk bersih, keranjang laundry, dan deterjen">
            </section>

            <section class="home-guide-section" id="alur-pelayanan" aria-labelledby="home-guide-title">
                <header class="home-guide-heading">
                    <span class="section-eyebrow">Alur Pelayanan</span>
                    <h2 id="home-guide-title">Tahapan layanan G'Laundry</h2>
                    <p>Alur singkat dari menyerahkan cucian sampai siap diambil.</p>
                </header>
                <div class="home-guide-grid">
                    <article class="home-guide-step">
                        <span class="home-guide-number">1</span>
                        <span class="home-guide-icon" aria-hidden="true"><svg viewBox="0 0 48 48"><path d="M13 7h22a3 3 0 0 1 3 3v31H10V10a3 3 0 0 1 3-3Z"/><path d="M18 7V4h12v3M17 18h14M17 25h14M17 32h9"/></svg></span>
                        <h3>Serahkan Cucian</h3>
                        <p>Pilih layanan dan serahkan cucian kepada admin untuk diinput datanya.</p>
                    </article>
                    <article class="home-guide-step">
                        <span class="home-guide-number">2</span>
                        <span class="home-guide-icon" aria-hidden="true"><svg viewBox="0 0 48 48"><rect x="5" y="8" width="32" height="25" rx="3"/><path d="M15 41h12m-6-8v8"/><circle cx="19" cy="20" r="6"/><path d="m23.5 24.5 4 4"/></svg></span>
                        <h3>Simpan Nota Digital</h3>
                        <p>Nota diberikan admin melalui WhatsApp Anda yang juga berisi token tracking yang dapat digunakan untuk melihat perkembangan cucian Anda.</p>
                    </article>
                    <article class="home-guide-step">
                        <span class="home-guide-number">3</span>
                        <span class="home-guide-icon" aria-hidden="true">@include('layanan.partials.icon', ['icon' => 'washer'])</span>
                        <h3>Pantau Proses</h3>
                        <p>Masukkan token pada halaman Tracking Cucian untuk melihat status terbaru.</p>
                    </article>
                    <article class="home-guide-step">
                        <span class="home-guide-number">4</span>
                        <span class="home-guide-icon" aria-hidden="true"><svg viewBox="0 0 48 48"><path d="M10 15h28l-3 28H13zM17 15V9a7 7 0 0 1 14 0v6"/><path d="m19 29 4 4 8-9"/></svg></span>
                        <h3>Ambil Cucian</h3>
                        <p>Datang ke laundry saat WhatsApp Anda menerima notifikasi status cucian sudah selesai.</p>
                    </article>
                </div>
            </section>
        </main>

        @include('partials.public-footer')
    </div>

    <script>
        const menuButton = document.querySelector('.menu-toggle');
        const navigation = document.querySelector('.primary-nav');
        menuButton?.addEventListener('click', () => {
            const isOpen = menuButton.getAttribute('aria-expanded') === 'true';
            menuButton.setAttribute('aria-expanded', String(!isOpen));
            navigation.classList.toggle('is-open', !isOpen);
        });

        navigation?.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                menuButton?.setAttribute('aria-expanded', 'false');
                navigation.classList.remove('is-open');
            });
        });

    </script>
</body>
</html>
