<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="Hubungi admin G'Laundry untuk pertanyaan cucian dan layanan laundry.">
    <title>Kontak | G'Laundry</title>
    <link rel="icon" href="{{ asset('images/logo-g-laundry.png') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}?v={{ filemtime(public_path('css/landing.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/contact.css') }}?v={{ filemtime(public_path('css/contact.css')) }}">
</head>
<body class="contact-page-body">
    <div class="site-frame contact-frame" id="kontak-page">
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
                <a href="{{ route('layanan.index') }}">Layanan</a>
                <a class="active" href="{{ route('kontak.index') }}" aria-current="page">Kontak</a>
            </nav>

            <a class="admin-button" href="{{ route('login') }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/></svg>
                Login Admin
            </a>
        </header>

        <main class="contact-page-main">
            <div class="contact-main-grid">
                <section class="contact-left-column" aria-labelledby="contact-page-title">
                    <div class="contact-intro">
                        <h1 id="contact-page-title">Hubungi <span>Admin</span></h1>
                        <p>Hubungi admin G'Laundry melalui WhatsApp untuk bantuan tracking cucian, pertanyaan pickup, ataupun informasi layanan lainnya.</p>
                    </div>

                    <section class="contact-admin-card" aria-labelledby="contact-admin-title">
                        <div class="contact-admin-illustration">
                            <h2 id="contact-admin-title">Informasi <span>Admin</span></h2>
                            <div class="contact-admin-avatar" aria-hidden="true">
                                <img src="{{ asset('images/contact-admin-illustration.png') }}" alt="">
                            </div>
                        </div>

                        <div class="contact-admin-details">
                            <div class="contact-admin-detail-row">
                                <span class="contact-admin-detail-icon profile-detail-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2z"/></svg></span>
                                <span><strong>Admin G'Laundry</strong><small>Customer Service</small></span>
                            </div>
                            <a class="contact-admin-detail-row" href="{{ $customerWhatsappUrl }}" target="_blank" rel="noopener noreferrer">
                                <span class="contact-admin-detail-icon whatsapp-detail-icon"><x-icons.whatsapp /></span>
                                <span><strong>WhatsApp</strong><b>{{ $whatsappDisplay }}</b></span>
                            </a>
                            <div class="contact-admin-detail-row">
                                <span class="contact-admin-detail-icon hours-detail-icon"><x-icons.clock /></span>
                                <span><strong>Jam Operasional</strong><small>{{ $openingTime }} - {{ $closingTime }} WITA</small></span>
                            </div>
                            <div class="contact-admin-detail-row contact-address-row">
                                <span class="contact-admin-detail-icon address-detail-icon"><x-icons.map-pin /></span>
                                <span><strong>Alamat</strong><small>{{ $address }}</small></span>
                            </div>
                        </div>
                    </section>
                </section>

                <section class="contact-right-column" aria-label="WhatsApp dan status operasional">
                    <section class="contact-whatsapp-card" aria-labelledby="contact-whatsapp-title">
                        <div class="contact-card-heading">
                            <h2 id="contact-whatsapp-title">Hubungi Via WhatsApp</h2>
                            <p>Kirim pesan langsung ke admin melalui WhatsApp</p>
                        </div>
                        <div class="contact-whatsapp-content">
                            <span class="contact-large-whatsapp"><x-icons.whatsapp /></span>
                            <div class="contact-whatsapp-message-area">
                                <label class="sr-only" for="contact-whatsapp-message">Pesan untuk admin</label>
                                <textarea id="contact-whatsapp-message" rows="6">{{ $customerWhatsappMessage }}</textarea>
                                <button type="button" class="contact-whatsapp-send" data-whatsapp-url="{{ $whatsappUrl }}">
                                    <x-icons.whatsapp />
                                    Buka WhatsApp
                                </button>
                            </div>
                        </div>
                    </section>

                    <section class="contact-operation-card" aria-labelledby="contact-operation-title">
                        <h2 id="contact-operation-title">Status operasional Hari ini</h2>
                        <div class="contact-operation-content">
                            <div class="contact-status-summary status-{{ \Illuminate\Support\Str::slug($todayStatus) }}" data-operational-hours data-opening-time="{{ $openingTime }}" data-break-start-time="{{ $breakStartTime }}" data-break-end-time="{{ $breakEndTime }}" data-closing-time="{{ $closingTime }}" data-timezone="{{ $operationalTimezone }}">
                                <strong data-operational-status role="status">{{ $todayStatus }}</strong>
                                <span data-status-message>{{ $todayStatus === 'Buka' ? 'Kami siap melayani Anda' : ($todayStatus === 'Istirahat' ? 'Kami sedang istirahat' : 'Kami kembali melayani saat jam buka') }}</span>
                            </div>
                            <div class="contact-status-legend" aria-label="Arti status operasional">
                                <div><span class="status-label status-open">Buka</span><small>Layanan normal</small></div>
                                <div><span class="status-label status-break">Istirahat</span><small>{{ $breakStartTime }}–{{ $breakEndTime }} WITA</small></div>
                                <div><span class="status-label status-closed">Tutup</span><small>Toko sedang tutup</small></div>
                            </div>
                        </div>
                    </section>
                </section>
            </div>

            <section class="contact-faq-card" aria-labelledby="contact-faq-title">
                <h2 id="contact-faq-title">FAQ - Pertanyaan yang Sering Diajukan</h2>
                <div class="contact-faq-grid">
                    @foreach ($faqs as $faq)
                        <details class="contact-faq-item">
                            <summary><span class="contact-faq-question-icon" aria-hidden="true">?</span><span>{{ $faq['question'] }}</span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg></summary>
                            <p>{{ $faq['answer'] }}</p>
                        </details>
                    @endforeach
                </div>
            </section>

        </main>

        @include('partials.public-footer')
    </div>

    <script>
        const contactMenuToggle = document.querySelector('.menu-toggle');
        const contactPrimaryNav = document.querySelector('.primary-nav');

        contactMenuToggle?.addEventListener('click', () => {
            const isOpen = contactPrimaryNav.classList.toggle('is-open');
            contactMenuToggle.setAttribute('aria-expanded', String(isOpen));
        });

        document.querySelector('.contact-whatsapp-send')?.addEventListener('click', (event) => {
            const message = document.getElementById('contact-whatsapp-message').value.trim();
            if (!message) return;

            const url = event.currentTarget.dataset.whatsappUrl + '?text=' + encodeURIComponent(message);
            window.open(url, '_blank', 'noopener,noreferrer');
        });
    </script>
</body>
</html>
