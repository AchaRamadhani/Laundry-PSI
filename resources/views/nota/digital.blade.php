<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fb">
    <meta name="description" content="Nota digital transaksi laundry {{ $transaksi->no_nota }}.">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Nota digital {{ $transaksi->no_nota }} | {{ $laundryName }}">
    <meta property="og:description" content="Nota digital transaksi {{ $transaksi->no_nota }}. Gunakan token tracking untuk memantau cucian Anda.">
    <meta property="og:url" content="{{ route('nota.digital', ['token' => $trackingToken]) }}">
    <meta property="og:image" content="{{ $receiptImageUrl }}">
    <meta property="og:image:type" content="image/svg+xml">
    <meta property="og:image:width" content="760">
    <meta property="og:image:height" content="{{ $receiptImageHeight }}">
    <meta name="twitter:card" content="summary_large_image">
    <title>Nota {{ $transaksi->no_nota }} | {{ $laundryName }}</title>
    <link rel="icon" href="{{ asset('images/logo-g-laundry.png') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('css/typography.css') }}?v={{ filemtime(public_path('css/typography.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/nota-digital.css') }}?v={{ filemtime(public_path('css/nota-digital.css')) }}">
</head>
<body>
    @php
        $statusColor = match (strtolower($statusLabel)) {
            'antrean' => '#1683e8',
            'diproses' => '#d97706',
            'dicuci' => '#2563eb',
            'dikeringkan' => '#0e8b91',
            'disetrika' => '#c56810',
            'selesai' => '#6844d6',
            'diambil' => '#16834f',
            default => '#667085',
        };
        $tanggalMasuk = \App\Support\LaundryDateFormatter::format($transaksi->tanggal_masuk);
    @endphp

    <main class="receipt-shell">
        <nav class="receipt-actions" aria-label="Aksi nota">
            <a href="{{ $trackingUrl }}">Lacak cucian</a>
            <a id="receipt-image-download" href="{{ $receiptImageUrl }}" data-filename="nota-{{ $transaksi->no_nota }}.png">Unduh nota gambar</a>
            <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
        </nav>

        <article class="receipt-card">
            <header class="receipt-header">
                <div class="receipt-brand">
                    <img src="{{ asset('images/logo-g-laundry.png') }}" alt="Logo {{ $laundryName }}">
                    <div>
                        <p class="receipt-eyebrow">Nota digital</p>
                        <h1>{{ $laundryName }}</h1>
                        @if ($address)<p class="receipt-address">{{ $address }}</p>@endif
                    </div>
                </div>
                <div class="receipt-number">
                    <span>Nomor nota</span>
                    <strong>{{ $transaksi->no_nota }}</strong>
                    <span class="receipt-status" style="--status-color: {{ $statusColor }}">{{ $statusLabel }}</span>
                </div>
            </header>

            <section class="receipt-customer" aria-labelledby="customer-heading">
                <h2 id="customer-heading">Informasi pelanggan</h2>
                <dl>
                    <div><dt>Nama</dt><dd>{{ $transaksi->pelanggan?->nama_pelanggan ?? 'Pelanggan' }}</dd></div>
                    <div><dt>Nomor telepon</dt><dd>{{ $transaksi->pelanggan?->no_telp ?? '—' }}</dd></div>
                    <div><dt>Tanggal masuk</dt><dd>{{ $tanggalMasuk }}{{ $transaksi->created_at ? ' · ' . $transaksi->created_at->format('H.i') : '' }}</dd></div>
                    <div><dt>Estimasi selesai</dt><dd>{{ \App\Support\LaundryDateFormatter::format($transaksi->estimasi_selesai, true) }}</dd></div>
                </dl>
            </section>

            <section class="receipt-items" aria-labelledby="items-heading">
                <h2 id="items-heading">Rincian layanan</h2>
                <div class="receipt-table-wrap">
                    <table>
                        <thead><tr><th>Layanan</th><th>Berat / jumlah</th><th>Subtotal</th></tr></thead>
                        <tbody>
                            @forelse ($transaksi->detailTransaksi as $detail)
                                @php($unit = strtolower($detail->paketLaundry?->kategori ?? '') === 'satuan' ? 'pcs' : 'kg')
                                <tr>
                                    <td>{{ $detail->paketLaundry?->nama_paket ?? 'Layanan laundry' }}</td>
                                    <td>{{ number_format((float) $detail->berat_qty, 2, ',', '.') }} {{ $unit }}</td>
                                    <td>Rp {{ number_format((float) $detail->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3">Rincian layanan tidak tersedia.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            @if (filled($transaksi->catatan))
                <section class="receipt-note"><strong>Catatan pesanan</strong><p>{{ trim((string) $transaksi->catatan) }}</p></section>
            @endif

            <section class="receipt-total" aria-label="Total tagihan">
                <span>Total tagihan</span>
                <strong>Rp {{ number_format((float) $transaksi->total_harga, 0, ',', '.') }}</strong>
            </section>

            <dl class="receipt-payment-summary">
                <div><dt>Status pembayaran</dt><dd>{{ $transaksi->status_pembayaran_label }}</dd></div>
                <div><dt>Metode pembayaran</dt><dd>{{ $transaksi->metode_pembayaran_label }}</dd></div>
            </dl>

            <footer class="receipt-footer">
                <div class="receipt-thanks"><strong>Terima kasih telah mempercayakan cucian Anda kepada {{ $laundryName }}.</strong><p>Simpan nota ini sebagai bukti transaksi. Gunakan token tracking untuk melihat perkembangan cucian Anda.</p></div>
                <div class="receipt-tracking-panel">
                    <div class="receipt-qr-wrap">
                        <img src="{{ $qrSvgDataUri }}" alt="QR Code menuju website {{ $laundryName }}">
                        <span>Pindai untuk membuka website</span>
                    </div>
                    <div class="receipt-tracking">
                        <span>Token tracking</span>
                        <strong>{{ $trackingToken }}</strong>
                        <small>Jaga kerahasiaan token Anda agar data pribadi tetap terjaga.</small>
                        <a href="{{ $trackingUrl }}">Lacak status cucian</a>
                    </div>
                </div>
                @if ($whatsappNumber)<p class="receipt-contact">Kontak laundry: {{ $whatsappNumber }}</p>@endif
            </footer>
        </article>
        <p class="receipt-disclaimer">Nota digital ini dibuat otomatis oleh {{ $laundryName }}.</p>
    </main>
    <script>
        document.getElementById('receipt-image-download')?.addEventListener('click', async (event) => {
            event.preventDefault();
            const link = event.currentTarget;

            try {
                const response = await fetch(link.href, { credentials: 'same-origin' });
                if (!response.ok) throw new Error('Nota gambar tidak dapat dimuat.');

                const svgBlob = await response.blob();
                const sourceUrl = URL.createObjectURL(svgBlob);
                const image = new Image();

                image.onload = () => {
                    const canvas = document.createElement('canvas');
                    canvas.width = image.naturalWidth;
                    canvas.height = image.naturalHeight;
                    const context = canvas.getContext('2d');
                    context.fillStyle = '#ffffff';
                    context.fillRect(0, 0, canvas.width, canvas.height);
                    context.drawImage(image, 0, 0);
                    URL.revokeObjectURL(sourceUrl);

                    canvas.toBlob((pngBlob) => {
                        if (!pngBlob) return;
                        const downloadUrl = URL.createObjectURL(pngBlob);
                        const downloadLink = document.createElement('a');
                        downloadLink.href = downloadUrl;
                        downloadLink.download = link.dataset.filename;
                        downloadLink.click();
                        setTimeout(() => URL.revokeObjectURL(downloadUrl), 1000);
                    }, 'image/png');
                };

                image.onerror = () => {
                    URL.revokeObjectURL(sourceUrl);
                    window.location.href = link.href;
                };
                image.src = sourceUrl;
            } catch {
                window.location.href = link.href;
            }
        });
    </script>
</body>
</html>
