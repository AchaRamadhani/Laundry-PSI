<x-layouts::app :title="'Pelanggan'">
    @php
        $donutGradient = $jumlahSegment
            ? 'conic-gradient(' . $statusRingkasan->map(fn ($item) => $item->warna . ' ' . number_format($item->mulai, 2, '.', '') . 'deg ' . number_format($item->akhir, 2, '.', '') . 'deg')->implode(', ') . ')'
            : 'conic-gradient(#e9edf3 0deg 360deg)';
        $lastPage = $pelanggan->lastPage();
        $startPage = max(2, $pelanggan->currentPage() - 1);
        $endPage = min($lastPage - 1, $pelanggan->currentPage() + 1);
        if ($pelanggan->currentPage() <= 3) $endPage = min($lastPage - 1, 5);
        if ($pelanggan->currentPage() >= $lastPage - 2) $startPage = max(2, $lastPage - 4);
    @endphp

    <div class="pelanggan-page">
        <section class="pelanggan-heading">
            <div><h1>Pelanggan</h1><p>Kelola data pelanggan dan lihat riwayat aktivitas laundry mereka</p></div>
            <a class="pelanggan-add-button" href="{{ route('pelanggan.create') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>Tambah Pelanggan</a>
        </section>

        @if (session('pelanggan_created_id'))
            <div class="pelanggan-save-success-backdrop" id="pelanggan-save-success" role="status" aria-live="polite">
                <section class="pelanggan-save-success-card" aria-label="Data pelanggan berhasil disimpan">
                    <span class="pelanggan-save-success-icon" aria-hidden="true"><span><svg viewBox="0 0 48 48"><path d="m11 25 9 9 18-21"/></svg></span></span>
                    <h2>Data Berhasil Disimpan</h2>
                    <p>ID Pelanggan: <strong>PLG-GL-{{ str_pad((string) session('pelanggan_created_id'), 3, '0', STR_PAD_LEFT) }}</strong></p>
                </section>
            </div>
            <script>
                (() => {
                    const notification = document.getElementById('pelanggan-save-success');
                    if (!notification) return;

                    let dismissed = false;
                    const fadeDuration = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 1000;
                    const dismiss = () => {
                        if (dismissed) return;
                        dismissed = true;
                        notification.classList.add('is-closing');
                        window.setTimeout(() => notification.remove(), fadeDuration);
                    };

                    notification.addEventListener('click', (event) => {
                        if (event.target === notification) dismiss();
                    });
                    const handleEscape = (event) => {
                        if (event.key !== 'Escape') return;
                        document.removeEventListener('keydown', handleEscape);
                        dismiss();
                    };
                    document.addEventListener('keydown', handleEscape);
                    window.setTimeout(dismiss, 3500);
                })();
            </script>
        @elseif (session('success'))<div class="cucian-alert success" role="status">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="cucian-alert error" role="alert">{{ session('error') }}</div>@endif

        <section class="pelanggan-metrics" aria-label="Ringkasan pelanggan">
            <article class="pelanggan-metric"><span class="pelanggan-metric-icon blue"><svg viewBox="0 0 32 32" aria-hidden="true"><circle cx="12" cy="11" r="5"/><path d="M3 27v-2a9 9 0 0 1 18 0v2zM21 8a4 4 0 1 1 0 8m2 3a7 7 0 0 1 6 7v1h-5"/></svg></span><div><span>Total Pelanggan</span><strong>{{ number_format($totalPelanggan, 0, ',', '.') }}</strong><small>Orang</small></div></article>
            <article class="pelanggan-metric"><span class="pelanggan-metric-icon green"><svg viewBox="0 0 32 32" aria-hidden="true"><circle cx="16" cy="9" r="5"/><path d="M5 28v-4a7 7 0 0 1 7-7h8a7 7 0 0 1 7 7v4z"/></svg></span><div><span>Pelanggan Bulan Ini</span><strong>{{ number_format($pelangganBulanIni, 0, ',', '.') }}</strong><small>{{ $namaBulan[$bulan] }} {{ $tahun }}</small></div></article>
            <article class="pelanggan-metric"><span class="pelanggan-metric-icon purple"><svg viewBox="0 0 32 32" aria-hidden="true"><circle cx="13" cy="11" r="5"/><path d="M3 28v-3a10 10 0 0 1 17-7M25 18v10m-5-5h10"/></svg></span><div><span>Pelanggan Baru</span><strong>{{ number_format($pelangganBaru, 0, ',', '.') }}</strong><small>{{ $namaBulan[$bulan] }} {{ $tahun }}</small></div></article>
            <article class="pelanggan-metric"><span class="pelanggan-metric-icon orange"><svg viewBox="0 0 32 32" aria-hidden="true"><rect x="6" y="4" width="20" height="24" rx="3"/><path d="M6 10h20"/><circle cx="16" cy="19" r="5"/><path d="M10 7h.01"/></svg></span><div><span>Cucian Berjalan</span><strong>{{ number_format($cucianBerjalan, 0, ',', '.') }}</strong><small>{{ $namaBulan[$bulan] }} {{ $tahun }}</small></div></article>
        </section>

        <section class="pelanggan-content-grid">
            <article class="cucian-panel pelanggan-table-panel">
                <form class="pelanggan-filters" action="{{ route('pelanggan.index') }}" method="GET">
                    <label class="pelanggan-search"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m16 16 5 5"/></svg><input type="search" name="search" value="{{ $search }}" placeholder="Cari nama pelanggan atau nomor telepon..." aria-label="Cari pelanggan"></label>
                    <label class="sr-only" for="pelanggan-layanan">Semua layanan</label>
                    <select id="pelanggan-layanan" name="layanan"><option value="">Semua Layanan</option>@foreach (\App\Support\LaundryOptions::SERVICES as $serviceKey => $service)<option value="{{ $serviceKey }}" @selected((string) $paketId === $serviceKey)>{{ $service['nama'] }}</option>@endforeach</select>
                    <label class="pelanggan-month"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg><select name="bulan" aria-label="Pilih bulan">@foreach ($namaBulan as $nomor => $nama)<option value="{{ $nomor }}" @selected((int) $bulan === (int) $nomor)>{{ $nama }}</option>@endforeach</select></label>
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                    <button type="submit" class="pelanggan-filter-button"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16l-6.5 7.5V19l-3 1v-7.5z"/></svg>Filter</button>
                    <a href="{{ route('pelanggan.index') }}" class="pelanggan-reset-button">↻ &nbsp; Reset Filter</a>
                </form>

                <div class="cucian-table-scroll pelanggan-table-scroll">
                    <table class="pelanggan-table">
                        <thead><tr><th>No.</th><th>ID Pelanggan</th><th>Nama Pelanggan</th><th>No. Telp</th><th>Jumlah Transaksi</th><th>Cucian Berjalan</th><th>Transaksi Terakhir</th><th>Aksi</th></tr></thead>
                        <tbody>
                            @forelse ($pelanggan as $index => $item)
                                <tr>
                                    <td>{{ $pelanggan->firstItem() + $index }}</td>
                                    <td class="pelanggan-code">PLG-GL-{{ str_pad((string) $item->id, 3, '0', STR_PAD_LEFT) }}</td>
                                    <td class="pelanggan-name">{{ $item->nama_pelanggan }}</td>
                                    <td class="pelanggan-phone">{{ $item->no_telp }}</td>
                                    <td>{{ number_format($item->jumlah_transaksi, 0, ',', '.') }}</td>
                                    <td>{{ number_format($item->cucian_berjalan, 0, ',', '.') }}</td>
                                    <td>{{ $item->transaksi_terakhir ? \Carbon\Carbon::parse($item->transaksi_terakhir)->format('d M Y') : '—' }}</td>
                                    <td>
                                        <div class="pelanggan-row-actions">
                                            <a href="{{ route('pelanggan.show', $item) }}" class="row-action view" aria-label="Lihat {{ $item->nama_pelanggan }}" title="Lihat detail"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg></a>
                                            <a href="{{ route('pelanggan.edit', $item) }}" class="row-action edit" aria-label="Ubah {{ $item->nama_pelanggan }}" title="Ubah"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 16-.8 4.8L8 20l11.6-11.6-4-4L4 16Z"/><path d="m13.8 6.2 4 4"/></svg></a>
                                            <form action="{{ route('pelanggan.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus data pelanggan {{ $item->nama_pelanggan }}?')">@csrf @method('DELETE')<button class="row-action delete" type="submit" aria-label="Hapus {{ $item->nama_pelanggan }}" title="Hapus"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3m3 0-.8 13H6.8L6 7m4 4v5m4-5v5"/></svg></button></form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="cucian-empty">Belum ada data pelanggan yang cocok.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pelanggan-pagination">
                    <span>Menampilkan {{ $pelanggan->firstItem() ?? 0 }}–{{ $pelanggan->lastItem() ?? 0 }} dari {{ number_format($pelanggan->total(), 0, ',', '.') }} pelanggan</span>
                    <nav class="cucian-pages" aria-label="Navigasi halaman">
                        @if ($pelanggan->onFirstPage())<span class="page-arrow disabled" aria-hidden="true">‹</span>@else<a class="page-arrow" href="{{ $pelanggan->previousPageUrl() }}" aria-label="Halaman sebelumnya">‹</a>@endif
                        <a class="{{ $pelanggan->currentPage() === 1 ? 'current' : '' }}" href="{{ $pelanggan->url(1) }}">1</a>
                        @if ($startPage > 2)<span class="page-ellipsis">…</span>@endif
                        @if ($lastPage > 1)
                            @foreach (range($startPage, max($startPage, $endPage)) as $page)
                                @if ($page < $lastPage)<a class="{{ $pelanggan->currentPage() === $page ? 'current' : '' }}" href="{{ $pelanggan->url($page) }}">{{ $page }}</a>@endif
                            @endforeach
                        @endif
                        @if ($endPage < $lastPage - 1)<span class="page-ellipsis">…</span>@endif
                        @if ($lastPage > 1)<a class="{{ $pelanggan->currentPage() === $lastPage ? 'current' : '' }}" href="{{ $pelanggan->url($lastPage) }}">{{ $lastPage }}</a>@endif
                        @if ($pelanggan->hasMorePages())<a class="page-arrow" href="{{ $pelanggan->nextPageUrl() }}" aria-label="Halaman berikutnya">›</a>@else<span class="page-arrow disabled" aria-hidden="true">›</span>@endif
                    </nav>
                </div>
            </article>

            <aside class="pelanggan-side-panels">
                <article class="cucian-panel pelanggan-summary-panel">
                    <h2>Ringkasan Status</h2>
                    <div class="pelanggan-summary-layout">
                        <div class="pelanggan-donut" style="--donut-background: {{ $donutGradient }}"><div><strong>{{ number_format($totalPelanggan, 0, ',', '.') }}</strong><span>Total</span></div></div>
                        <div class="pelanggan-legend">
                            @foreach ($statusRingkasan as $segment)
                                <div><span><i style="--status-color: {{ $segment->warna }}"></i>{{ $segment->label }}</span><small>{{ number_format($segment->jumlah, 0, ',', '.') }} ({{ $jumlahSegment ? number_format(($segment->jumlah / $jumlahSegment) * 100, 0) : 0 }}%)</small></div>
                            @endforeach
                        </div>
                    </div>
                </article>

                <article class="cucian-panel pelanggan-activity-panel">
                    <div class="pelanggan-activity-heading"><h2>Aktivitas Pelanggan</h2><a href="{{ route('pelanggan.aktivitas') }}">Lihat semua aktivitas <span aria-hidden="true">→</span></a></div>
                    <div class="pelanggan-activity-list">
                        @forelse ($aktivitas as $item)
                            <a class="pelanggan-activity-item" href="{{ route('pelanggan.show', $item->pelanggan) }}">
                                <span class="pelanggan-activity-avatar"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2z"/></svg></span>
                                <span class="pelanggan-activity-copy"><strong>{{ $item->pelanggan?->nama_pelanggan ?? 'Pelanggan dihapus' }}</strong><small>{{ $item->aktivitas }}</small></span>
                                <time datetime="{{ $item->created_at?->toIso8601String() }}">{{ $item->created_at?->diffForHumans(short: true) ?? '—' }}</time>
                            </a>
                        @empty
                            <p class="pelanggan-activity-empty">Belum ada aktivitas pelanggan.</p>
                        @endforelse
                    </div>
                </article>
            </aside>
        </section>
    </div>
</x-layouts::app>
