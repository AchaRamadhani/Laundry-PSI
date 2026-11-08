<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="Lihat layanan laundry G'Laundry untuk kebutuhan cucian Anda.">
    <title>Layanan | G'Laundry</title>
    <link rel="icon" href="{{ asset('images/logo-g-laundry.png') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}?v={{ filemtime(public_path('css/landing.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/services.css') }}?v={{ filemtime(public_path('css/services.css')) }}">
</head>
<body class="services-body">
    <div class="site-frame services-frame" id="layanan-page">
        <header class="site-header">
            <a class="brand" href="{{ route('home') }}" aria-label="G'Laundry, beranda">
                <img src="{{ asset('images/logo-g-laundry.png') }}" alt="G'Laundry">
            </a>

            <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-nav" aria-label="Buka navigasi">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>

            <nav class="primary-nav" id="primary-nav" aria-label="Navigasi utama">
                <a href="{{ route('home') }}#beranda">Beranda</a>
                <a href="{{ route('tracking') }}">Tracking Cucian</a>
                <a class="active" href="{{ route('layanan.index') }}" aria-current="page">Layanan</a>
                <a href="{{ route('kontak.index') }}">Kontak</a>
            </nav>

            <a class="admin-button" href="{{ route('login') }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/></svg>
                Login Admin
            </a>
        </header>

        <main class="services-page-main">
            <section class="services-hero" aria-labelledby="services-title">
                <div class="services-hero-copy">
                    <h1 id="services-title">Layanan Lengkap<br>Untuk <span>Kebutuhan Cucian</span> Anda</h1>
                    <p>Kami menyediakan berbagai jenis layanan untuk memenuhi kebutuhan cucian Anda dengan hasil bersih, wangi, dan rapi.</p>
                </div>
                <img src="{{ asset('images/laundry-hero.png') }}" alt="Handuk bersih, keranjang laundry, dan deterjen" class="services-hero-image">
            </section>

            <section class="services-category kiloan-category" aria-labelledby="kiloan-title">
                <header class="services-category-bar">
                    <span class="services-category-symbol" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 8h10l2 13H5L7 8Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2M12 12v5m-2.5-2.5h5"/></svg></span>
                    <h2 id="kiloan-title">1. Kategori kiloan (minimal 3kg)</h2>
                    <span class="services-category-note"><b aria-hidden="true">!</b> Perhitungan kiloan minimal 3kg berat cucian</span>
                </header>
                <p class="services-category-caption">Semua layanan pada kategori ini dihitung dengan berat cucian minimal 3kg.</p>
                <div class="services-kiloan-grid">
                    @foreach ($layananKiloan as $layanan)
                        <article class="public-service-card">
                            <span class="public-service-icon">@include('layanan.partials.icon', ['icon' => $layanan['icon']])</span>
                            <h3>{{ $layanan['nama'] }}</h3>
                            <p>{{ $layanan['deskripsi'] }}</p>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="services-category non-kiloan-category" aria-labelledby="non-kiloan-title">
                <header class="services-nonkiloan-heading">
                    <span class="services-nonkiloan-star" aria-hidden="true">★</span>
                    <h2 id="non-kiloan-title">2. Kategori kiloan (Non-Kiloan)</h2>
                    <span>*Layanan ini tidak dihitung berdasarkan kiloan dan memiliki ketentuan tersendiri.</span>
                </header>
                <div class="services-nonkiloan-grid">
                    @foreach ($layananNonKiloan as $layanan)
                        <article class="nonkiloan-service-card">
                            <span class="public-service-icon">@include('layanan.partials.icon', ['icon' => $layanan['icon']])</span>
                            <div><h3>{{ $layanan['nama'] }}</h3><p>{{ $layanan['deskripsi'] }}</p></div>
                        </article>
                    @endforeach
                </div>
            </section>

        </main>

        @include('partials.public-footer')
    </div>

    <script>
        const servicesMenuToggle = document.querySelector('.menu-toggle');
        const servicesPrimaryNav = document.querySelector('.primary-nav');

        servicesMenuToggle?.addEventListener('click', () => {
            const isOpen = servicesPrimaryNav.classList.toggle('is-open');
            servicesMenuToggle.setAttribute('aria-expanded', String(isOpen));
        });
    </script>
</body>
</html>
