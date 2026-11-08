<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="Lacak status cucian G'Laundry menggunakan token tracking.">
    <title>Tracking Cucian | G'Laundry</title>
    <link rel="icon" href="{{ asset('images/logo-g-laundry.png') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}?v={{ filemtime(public_path('css/landing.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/tracking.css') }}?v={{ filemtime(public_path('css/tracking.css')) }}">
</head>
<body class="tracking-body">
    <div class="site-frame tracking-frame" id="tracking-page">
        <header class="site-header">
            <a class="brand" href="{{ route('home') }}" aria-label="G'Laundry, beranda">
                <img src="{{ asset('images/logo-g-laundry.png') }}" alt="G'Laundry">
            </a>

            <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-nav" aria-label="Buka navigasi">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>

            <nav class="primary-nav" id="primary-nav" aria-label="Navigasi utama">
                <a href="{{ route('home') }}#beranda">Beranda</a>
                <a class="active" href="{{ route('tracking') }}" aria-current="page">Tracking Cucian</a>
                <a href="{{ route('layanan.index') }}">Layanan</a>
                <a href="{{ route('kontak.index') }}">Kontak</a>
            </nav>

            <a class="admin-button" href="{{ route('login') }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/></svg>
                Login Admin
            </a>
        </header>

        <main class="tracking-page-main">
            <div class="tracking-page-grid">
                <section class="tracking-left-column" aria-labelledby="tracking-page-title">
                    <div class="tracking-page-intro">
                        <h1 id="tracking-page-title">Tracking <span>Cucian</span></h1>
                        <p>Lacak status cucian Anda secara real-time hanya dengan token tracking.</p>
                        <img class="tracking-page-hero-image" src="{{ asset('images/laundry-hero.png') }}" alt="Handuk bersih, keranjang laundry, dan deterjen">
                    </div>

                    <section class="tracking-detail-card" aria-live="polite" aria-label="Hasil tracking cucian">
                        <h2>Hasil Tracking</h2>
                        @if ($transaksi)
                            @php
                                $namaLayanan = $transaksi->detailTransaksi
                                    ->map(fn ($detail) => $detail->paketLaundry?->nama_paket)
                                    ->filter()
                                    ->unique()
                                    ->implode(', ');
                                $statusLabel = $stages[$currentKey] ?? ucfirst($transaksi->status_cucian);
                            @endphp
                            <div class="tracking-result-layout">
                                <dl class="tracking-receipt-details">
                                    <div><dt>Token Tracking</dt><dd>{{ $transaksi->tracking_token }}</dd></div>
                                    <div><dt>Nama Pelanggan</dt><dd>{{ $transaksi->pelanggan?->nama_pelanggan ?? '—' }}</dd></div>
                                    <div><dt>Layanan</dt><dd>{{ $namaLayanan !== '' ? $namaLayanan : '—' }}</dd></div>
                                    <div><dt>Tanggal Masuk</dt><dd>{{ \Carbon\Carbon::parse($transaksi->tanggal_masuk)->locale('id')->translatedFormat('d M Y') }}</dd></div>
                                    <div><dt>Estimasi Selesai</dt><dd>{{ $transaksi->estimasi_selesai ? \Carbon\Carbon::parse($transaksi->estimasi_selesai)->locale('id')->translatedFormat('d M Y, H.i') : 'Belum ditentukan' }}</dd></div>
                                    <div><dt>Status saat ini</dt><dd class="tracking-current-status">{{ $statusLabel }}</dd></div>
                                </dl>

                                <div class="tracking-progress" aria-label="Tahapan cucian">
                                    @foreach ($stages as $key => $label)
                                        @php
                                            $stageIndex = $loop->index;
                                            $stageDone = $currentIndex >= $stageIndex;
                                        @endphp
                                        <div class="tracking-progress-step {{ $stageDone ? 'is-done' : '' }} {{ $currentIndex === $stageIndex ? 'is-current' : '' }}">
                                            <span class="tracking-stage-icon">@include('tracking.partials.status-icon', ['key' => $key])</span>
                                            <small>{{ $label }}</small>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @elseif ($tokenTracking !== '')
                            <div class="tracking-empty-result tracking-not-found">
                                <span class="tracking-empty-icon" aria-hidden="true">!</span>
                                <div><strong>Token tracking tidak ditemukan</strong><p>Pastikan token tracking yang dimasukkan sudah benar.</p></div>
                            </div>
                        @else
                            <div class="tracking-empty-result">
                                <span class="tracking-empty-icon" aria-hidden="true">i</span>
                                <div><strong>Detail cucian akan tampil di sini</strong><p>Masukkan token tracking untuk melihat ringkasan transaksi dan progres cucian.</p></div>
                            </div>
                        @endif
                    </section>
                </section>

                <section class="tracking-right-column" aria-label="Cari cucian dan riwayat status">
                    <section class="tracking-search-card" aria-labelledby="tracking-search-title">
                        <h2 id="tracking-search-title">Masukkan Token Tracking</h2>
                        <p>Gunakan token tracking yang diberikan bersama informasi transaksi Anda.</p>
                        <form class="tracking-search-form" action="{{ route('tracking') }}" method="GET">
                            <label class="sr-only" for="tracking-token">Token tracking</label>
                            <div class="input-wrap">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 3h6l5 5v13H8z"/><path d="M14 3v5h5M11 13h5M11 17h5"/></svg>
                                <input id="tracking-token" name="token_tracking" type="text" value="{{ $tokenTracking }}" placeholder="Masukkan token tracking" autocomplete="off" maxlength="12" required>
                            </div>
                            @error('token_tracking')<p class="form-error">{{ $message }}</p>@enderror
                            <button class="check-button" type="submit">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 4.5 4.5"/></svg>
                                Cek Status
                            </button>
                        </form>
                        <a class="tracking-whatsapp-button" href="{{ $customerWhatsappUrl }}" target="_blank" rel="noopener noreferrer">
                            <x-icons.whatsapp />
                            Hubungi Admin WhatsApp
                        </a>
                    </section>

                    <section class="tracking-history-card" aria-labelledby="tracking-history-title" aria-live="polite">
                        <div class="tracking-history-heading">
                            <h2 id="tracking-history-title">Riwayat Status</h2>
                            @if ($transaksi)
                                <span>{{ $transaksi->tracking_token }}</span>
                            @endif
                        </div>
                        @if ($tokenTracking !== '' && ! $transaksi)
                            <p class="tracking-history-message">Riwayat belum tersedia karena token tracking tidak ditemukan.</p>
                        @elseif ($tokenTracking === '')
                            <p class="tracking-history-message">Riwayat status aktual akan tampil setelah token tracking ditemukan.</p>
                        @endif
                        <ol class="tracking-history-list">
                            @foreach ($stages as $key => $label)
                                @php
                                    $stageIndex = $loop->index;
                                    $stageDone = $transaksi && $currentIndex >= $stageIndex;
                                @endphp
                                <li class="tracking-history-item {{ $stageDone ? 'is-done' : '' }} {{ $currentIndex === $stageIndex && $transaksi ? 'is-current' : '' }}">
                                    <span class="tracking-history-icon">@include('tracking.partials.status-icon', ['key' => $key])</span>
                                    <span class="tracking-history-label">{{ $label }}</span>
                                    <time>{{ $historyByStage[$key] ?? '—' }}</time>
                                </li>
                            @endforeach
                        </ol>
                    </section>
                </section>
            </div>

        </main>

        @include('partials.public-footer')
    </div>

    <script>
        const trackingMenuToggle = document.querySelector('.menu-toggle');
        const trackingPrimaryNav = document.querySelector('.primary-nav');

        trackingMenuToggle?.addEventListener('click', () => {
            const isOpen = trackingPrimaryNav.classList.toggle('is-open');
            trackingMenuToggle.setAttribute('aria-expanded', String(isOpen));
        });
    </script>
</body>
</html>
