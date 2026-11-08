<x-layouts::app :title="'Transaksi'">
    @php
        $filterStatus = \App\Support\LaundryOptions::STATUSES;
        $donutGradient = $totalStatus
            ? 'conic-gradient(' . $statusRingkasan->map(fn ($item) => $item->warna . ' ' . number_format($item->mulai, 2, '.', '') . 'deg ' . number_format($item->akhir, 2, '.', '') . 'deg')->implode(', ') . ')'
            : 'conic-gradient(#e9edf3 0deg 360deg)';
        $lastPage = $transaksi->lastPage();
        $startPage = max(2, $transaksi->currentPage() - 1);
        $endPage = min($lastPage - 1, $transaksi->currentPage() + 1);
        if ($transaksi->currentPage() <= 3) $endPage = min($lastPage - 1, 5);
        if ($transaksi->currentPage() >= $lastPage - 2) $startPage = max(2, $lastPage - 4);
    @endphp

    <div class="transaksi-page">
        <section class="transaksi-heading">
            <div>
                <h1>Transaksi</h1>
                <p>Lihat riwayat transaksi dan nota pelanggan dengan rapi</p>
                <div class="transaksi-note">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5m0-8h.01"/></svg>
                    Nota digital dibuat otomatis saat data cucian disimpan. Kirim ulang nota akan membuka WhatsApp dengan pesan nota yang sudah terisi otomatis.
                </div>
            </div>
            <a class="transaksi-print-button" href="{{ route('transaksi.cetak', request()->query()) }}" target="_blank" rel="noopener">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9V3h12v6M6 17H4a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-2M6 14h12v7H6z"/><path d="M18 12h.01"/></svg>
                Cetak Rekap
            </a>
        </section>

        @if (session('success'))<div class="cucian-alert success" role="status">{{ session('success') }}</div>@endif

        <section class="transaksi-metrics" aria-label="Ringkasan transaksi {{ $namaBulan[$bulan] }} {{ $tahun }}">
            <article class="transaksi-metric"><span class="transaksi-metric-icon green"><svg viewBox="0 0 32 32" aria-hidden="true"><path d="M6 11h20l-2 17H8zM11 11a5 5 0 0 1 10 0M12 17v5m8-5v5"/></svg></span><div><span>Total Transaksi</span><strong>{{ number_format($totalTransaksi, 0, ',', '.') }}</strong><small>{{ $namaBulan[$bulan] }} {{ $tahun }}</small></div></article>
            <article class="transaksi-metric"><span class="transaksi-metric-icon blue"><svg viewBox="0 0 32 32" aria-hidden="true"><rect x="5" y="7" width="22" height="21" rx="2"/><path d="M10 4v6m12-6v6M5 13h22M10 18h3m6 0h3m-12 5h3m6 0h3"/></svg></span><div><span>Transaksi Hari Ini</span><strong>{{ number_format($transaksiHariIni, 0, ',', '.') }}</strong><small>Transaksi masuk</small></div></article>
            <article class="transaksi-metric"><span class="transaksi-metric-icon orange"><svg viewBox="0 0 32 32" aria-hidden="true"><circle cx="16" cy="16" r="11"/><path d="M16 9v8l5 3"/></svg></span><div><span>Belum Selesai</span><strong>{{ number_format($belumSelesai, 0, ',', '.') }}</strong><small>{{ $namaBulan[$bulan] }} {{ $tahun }}</small></div></article>
            <article class="transaksi-metric"><span class="transaksi-metric-icon green"><svg viewBox="0 0 32 32" aria-hidden="true"><circle cx="16" cy="16" r="11"/><path d="m10.5 16 4 4 7-8"/></svg></span><div><span>Selesai</span><strong>{{ number_format($selesai, 0, ',', '.') }}</strong><small>{{ $namaBulan[$bulan] }} {{ $tahun }}</small></div></article>
        </section>

        <section class="transaksi-content-grid">
            <article class="cucian-panel transaksi-table-panel" id="transaksi-list">
                <form class="transaksi-filters" action="{{ route('transaksi.index') }}" method="GET">
                    <label class="transaksi-search-field"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m16 16 5 5"/></svg><input type="search" name="search" value="{{ $search }}" placeholder="Cari No Nota atau nama pelanggan..." aria-label="Cari transaksi"></label>
                    <label class="sr-only" for="transaksi-status-filter">Semua status</label>
                    <select id="transaksi-status-filter" name="status"><option value="">Semua Status</option>@foreach ($filterStatus as $key => $label)<option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>@endforeach</select>
                    <label class="sr-only" for="transaksi-layanan-filter">Semua layanan</label>
                    <select id="transaksi-layanan-filter" name="layanan"><option value="">Semua Layanan</option>@foreach (\App\Support\LaundryOptions::SERVICES as $serviceKey => $service)<option value="{{ $serviceKey }}" @selected((string) $paketId === $serviceKey)>{{ $service['nama'] }}</option>@endforeach</select>
                    <label class="transaksi-month-field"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg><select name="bulan" aria-label="Pilih bulan">@foreach ($namaBulan as $nomor => $nama)<option value="{{ $nomor }}" @selected((int) $bulan === (int) $nomor)>{{ $nama }}</option>@endforeach</select></label>
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                    <button class="transaksi-filter-button" type="submit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16l-6.5 7.5V19l-3 1v-7.5z"/></svg>Filter</button>
                </form>
                <a class="transaksi-reset" href="{{ route('transaksi.index') }}">↻ &nbsp; Reset Filter</a>

                <div class="cucian-table-scroll transaksi-table-scroll">
                    <table class="transaksi-table">
                        <thead><tr><th>No.</th><th>No. Nota</th><th>Pelanggan</th><th>Layanan</th><th>Berat/Qty</th><th>Tgl Masuk</th><th>Estimasi Selesai</th><th>Total Harga</th><th>Status Cucian</th><th>Aksi</th></tr></thead>
                        <tbody>
                            @forelse ($transaksi as $index => $item)
                                @php
                                    $statusKey = strtolower($item->status_cucian);
                                    $statusColor = match ($statusKey) {
                                        'antrean', 'antrian' => '#62b5f7',
                                        'proses', 'diproses' => '#ff9f1c',
                                        'dicuci' => '#3588ef',
                                        'dikeringkan', 'pengeringan' => '#20b7bd',
                                        'disetrika' => '#f5a11b',
                                        'selesai' => '#7552e8',
                                        'diambil' => '#55b85f',
                                        default => '#9aa4b4',
                                    };
                                    $statusText = match ($statusKey) {
                                        'antrean', 'antrian' => 'Antrean',
                                        'proses', 'diproses' => 'Diproses',
                                        'dikeringkan', 'pengeringan' => 'Dikeringkan',
                                        default => $filterStatus[$statusKey] ?? ucfirst($statusKey),
                                    };
                                    $layananText = $item->detailTransaksi->map(fn ($detail) => $detail->paketLaundry?->nama_paket)->filter()->unique()->implode(', ');
                                    $jumlah = $item->detailTransaksi->sum('berat_qty');
                                    $isSatuan = $item->detailTransaksi->contains(fn ($detail) => strtolower($detail->paketLaundry?->kategori ?? '') === 'satuan');
                                    $telepon = preg_replace('/\D+/', '', $item->pelanggan?->no_telp ?? '');
                                    $telepon = str_starts_with($telepon, '0') ? '62' . substr($telepon, 1) : $telepon;
                                    $barisNota = $item->detailTransaksi->map(function ($detail) {
                                        $unit = strtolower($detail->paketLaundry?->kategori ?? '') === 'satuan' ? 'pcs' : 'kg';
                                        return '- ' . ($detail->paketLaundry?->nama_paket ?? 'Layanan') . ': ' . number_format((float) $detail->berat_qty, 2, ',', '.') . ' ' . $unit . ' (Rp ' . number_format((float) $detail->subtotal, 0, ',', '.') . ')';
                                    })->implode("\n");
                                    $trackingUrl = route('tracking', ['token_tracking' => $item->tracking_token]);
                                    $pesanNota = "Halo " . ($item->pelanggan?->nama_pelanggan ?? 'Pelanggan') . ", berikut nota G'Laundry Anda:\n\nNo. Nota: {$item->no_nota}\nToken Tracking: {$item->tracking_token}\nLacak status cucian: {$trackingUrl}\nTanggal masuk: " . \Carbon\Carbon::parse($item->tanggal_masuk)->format('d M Y') . "\nLayanan:\n{$barisNota}\n\nEstimasi selesai: " . ($item->estimasi_selesai ? \Carbon\Carbon::parse($item->estimasi_selesai)->format('d M Y H.i') : 'Belum ditentukan') . "\nStatus: {$statusText}\nTotal: Rp " . number_format((float) $item->total_harga, 0, ',', '.') . "\n\nTerima kasih telah menggunakan G'Laundry.";
                                @endphp
                                <tr>
                                    <td>{{ $transaksi->firstItem() + $index }}</td>
                                    <td class="transaksi-nota">{{ $item->no_nota }}</td>
                                    <td>{{ $item->pelanggan?->nama_pelanggan ?? 'Pelanggan dihapus' }}</td>
                                    <td class="transaksi-service" title="{{ $layananText }}">{{ $layananText ?: '—' }}</td>
                                    <td>{{ number_format((float) $jumlah, 2, ',', '.') }} {{ $isSatuan ? 'pcs' : 'kg' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->tanggal_masuk)->format('d M Y') }}<small>{{ $item->created_at?->format('H.i') }}</small></td>
                                    <td>{{ $item->estimasi_selesai ? \Carbon\Carbon::parse($item->estimasi_selesai)->format('d M Y') : '—' }}@if ($item->estimasi_selesai)<small>{{ \Carbon\Carbon::parse($item->estimasi_selesai)->format('H.i') }}</small>@endif</td>
                                    <td class="transaksi-price">Rp {{ number_format((float) $item->total_harga, 0, ',', '.') }}</td>
                                    <td><span class="cucian-status" style="--status-color: {{ $statusColor }}">{{ $statusText }}</span></td>
                                    <td>
                                        <div class="transaksi-row-actions">
                                            <a href="{{ route('cucian.show', $item->no_nota) }}" class="row-action view" aria-label="Lihat nota {{ $item->no_nota }}" title="Lihat nota"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg></a>
                                            @if ($telepon !== '')<a href="https://wa.me/{{ $telepon }}?text={{ rawurlencode($pesanNota) }}" target="_blank" rel="noopener" class="row-action whatsapp" aria-label="Kirim nota {{ $item->no_nota }} lewat WhatsApp" title="Kirim nota via WhatsApp"><x-icons.whatsapp /></a>@else<span class="row-action whatsapp disabled" title="Nomor telepon belum tersedia"><x-icons.whatsapp /></span>@endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="cucian-empty">Belum ada transaksi untuk filter yang dipilih.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="cucian-pagination transaksi-pagination">
                    <span>Menampilkan {{ $transaksi->firstItem() ?? 0 }}–{{ $transaksi->lastItem() ?? 0 }} dari {{ number_format($transaksi->total(), 0, ',', '.') }} data</span>
                    <nav class="cucian-pages" aria-label="Navigasi halaman">
                        @if ($transaksi->onFirstPage())<span class="page-arrow disabled" aria-hidden="true">‹</span>@else<a class="page-arrow" href="{{ $transaksi->previousPageUrl() }}" aria-label="Halaman sebelumnya">‹</a>@endif
                        <a class="{{ $transaksi->currentPage() === 1 ? 'current' : '' }}" href="{{ $transaksi->url(1) }}">1</a>
                        @if ($startPage > 2)<span class="page-ellipsis">…</span>@endif
                        @if ($lastPage > 1)
                            @foreach (range($startPage, max($startPage, $endPage)) as $page)
                                @if ($page < $lastPage)<a class="{{ $transaksi->currentPage() === $page ? 'current' : '' }}" href="{{ $transaksi->url($page) }}">{{ $page }}</a>@endif
                            @endforeach
                        @endif
                        @if ($endPage < $lastPage - 1)<span class="page-ellipsis">…</span>@endif
                        @if ($lastPage > 1)<a class="{{ $transaksi->currentPage() === $lastPage ? 'current' : '' }}" href="{{ $transaksi->url($lastPage) }}">{{ $lastPage }}</a>@endif
                        @if ($transaksi->hasMorePages())<a class="page-arrow" href="{{ $transaksi->nextPageUrl() }}" aria-label="Halaman berikutnya">›</a>@else<span class="page-arrow disabled" aria-hidden="true">›</span>@endif
                    </nav>
                </div>
            </article>

            <aside class="transaksi-side-panels">
                <article class="cucian-panel transaksi-summary-panel">
                    <h2>Ringkasan Transaksi</h2>
                    <div class="transaksi-summary-stats">
                        <div class="transaksi-summary-stat"><span class="summary-stat-icon blue"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="5" width="16" height="16" rx="2"/><path d="M8 3v4m8-4v4M4 10h16"/></svg></span><div><small>Total Transaksi</small><strong>{{ number_format($totalTransaksi, 0, ',', '.') }} Transaksi</strong></div></div>
                        <div class="transaksi-summary-stat"><span class="summary-stat-icon green"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18m-13 5h4"/></svg></span><div><small>Total Pendapatan</small><strong>Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</strong></div></div>
                        <div class="transaksi-summary-stat"><span class="summary-stat-icon orange"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19h16M6 16l4-5 3 2 5-7"/><path d="M15 6h3v3"/></svg></span><div><small>Rata-rata per Transaksi</small><strong>Rp {{ number_format($rataRata, 0, ',', '.') }}</strong></div></div>
                    </div>
                    <div class="transaksi-status-mini">
                        <h3>Rekap Status</h3>
                        @forelse ($statusRingkasan as $item)
                            <div><span><i style="--status-color: {{ $item->warna }}"></i>{{ $item->label }}</span><small>{{ number_format($item->jumlah, 0, ',', '.') }} ({{ $totalStatus ? number_format(($item->jumlah / $totalStatus) * 100, 1, ',', '.') : '0,0' }}%)</small></div>
                        @empty
                            <p>Belum ada transaksi pada bulan ini.</p>
                        @endforelse
                    </div>
                </article>

                <article class="cucian-panel transaksi-activity-panel">
                    <div class="transaksi-activity-heading"><h2>Aktivitas Nota</h2><a href="{{ route('transaksi.aktivitas') }}">Lihat semua</a></div>
                    <div class="transaksi-activity-list">
                        @forelse ($aktivitas as $item)
                            <div class="transaksi-activity-item">
                                <span class="activity-icon note-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3h9l4 4v14H6zM14 3v5h5M9 12h7m-7 4h7"/></svg></span>
                                <div><strong>Nota dibuat</strong><small>{{ $item->no_nota }} · {{ $item->pelanggan?->nama_pelanggan ?? 'Pelanggan dihapus' }}</small></div>
                                <time datetime="{{ $item->created_at?->toIso8601String() }}">{{ $item->created_at?->diffForHumans(short: true) ?? '—' }}</time>
                            </div>
                        @empty
                            <p class="cucian-activity-empty">Belum ada nota transaksi.</p>
                        @endforelse
                    </div>
                </article>
            </aside>
        </section>
    </div>
</x-layouts::app>
