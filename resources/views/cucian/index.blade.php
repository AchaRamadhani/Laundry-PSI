<x-layouts::app :title="'Data Cucian'">
    @php
        $filterStatus = \App\Support\LaundryOptions::STATUSES;
        $donutGradient = $totalStatus
            ? 'conic-gradient(' . $statusRingkasan->map(fn ($item) => $item->warna . ' ' . number_format($item->mulai, 2, '.', '') . 'deg ' . number_format($item->akhir, 2, '.', '') . 'deg')->implode(', ') . ')'
            : 'conic-gradient(#e9edf3 0deg 360deg)';
    @endphp

    <div class="cucian-page">
        <section class="cucian-heading">
            <div>
                <h1>Data Cucian</h1>
                <p>Kelola seluruh data cucian pelanggan: tambah, lihat, ubah, dan hapus data cucian.</p>
                <div class="cucian-note">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5m0-8h.01"/></svg>
                    <span>Catatan: halaman ini untuk pengelolaan data cucian (CRUD). Update status cucian dilakukan melalui menu <strong>Update Status</strong>.</span>
                </div>
            </div>
            <a class="cucian-add-button" href="{{ route('cucian.create') }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                Tambah Data Cucian
            </a>
        </section>

        @if (session('success'))
            <div class="cucian-alert success" role="status">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="cucian-alert error" role="alert">{{ session('error') }}</div>
        @endif

        <section class="cucian-metrics" aria-label="Ringkasan data cucian bulan {{ $namaBulan[$bulan] }} {{ $tahun }}">
            <article class="cucian-metric">
                <span class="cucian-metric-icon blue"><svg viewBox="0 0 32 32" aria-hidden="true"><ellipse cx="16" cy="7" rx="10" ry="4"/><path d="M6 7v18c0 2.2 4.5 4 10 4s10-1.8 10-4V7M6 13c0 2.2 4.5 4 10 4s10-1.8 10-4M6 19c0 2.2 4.5 4 10 4s10-1.8 10-4"/></svg></span>
                <div><span>Total Data Cucian</span><strong>{{ number_format($totalCucian, 0, ',', '.') }}</strong><small>{{ $namaBulan[$bulan] }} {{ $tahun }}</small></div>
            </article>
            <article class="cucian-metric">
                <span class="cucian-metric-icon orange"><svg viewBox="0 0 32 32" aria-hidden="true"><circle cx="16" cy="16" r="11"/><path d="M16 9v8l5 3"/></svg></span>
                <div><span>Sedang Diproses</span><strong>{{ number_format($sedangDiproses, 0, ',', '.') }}</strong><small>{{ $namaBulan[$bulan] }} {{ $tahun }}</small></div>
            </article>
            <article class="cucian-metric">
                <span class="cucian-metric-icon green"><svg viewBox="0 0 32 32" aria-hidden="true"><circle cx="16" cy="16" r="11"/><path d="m10.5 16 4 4 7-8"/></svg></span>
                <div><span>Selesai</span><strong>{{ number_format($totalSelesai, 0, ',', '.') }}</strong><small>{{ $namaBulan[$bulan] }} {{ $tahun }}</small></div>
            </article>
            <article class="cucian-metric">
                <span class="cucian-metric-icon purple"><svg viewBox="0 0 32 32" aria-hidden="true"><path d="M5 12h22v16H5zM10 12V8a3 3 0 0 1 6 0v4m0 0V7a3 3 0 0 1 6 0v5m0 0V9a3 3 0 0 1 6 0v3"/></svg></span>
                <div><span>Diambil</span><strong>{{ number_format($totalDiambil, 0, ',', '.') }}</strong><small>{{ $namaBulan[$bulan] }} {{ $tahun }}</small></div>
            </article>
        </section>

        <section class="cucian-content-grid">
            <article class="cucian-panel cucian-list-panel">
                <form class="cucian-filters" action="{{ route('cucian.index') }}" method="GET">
                    <label class="cucian-search-field">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m16 16 5 5"/></svg>
                        <input type="search" name="search" value="{{ $search }}" placeholder="Cari no nota atau nama pelanggan..." aria-label="Cari cucian">
                    </label>
                    <label class="sr-only" for="filter-status">Semua status</label>
                    <select id="filter-status" name="status">
                        <option value="">Semua Status</option>
                        @foreach ($filterStatus as $key => $label)
                            <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <label class="sr-only" for="filter-layanan">Semua layanan</label>
                    <select id="filter-layanan" name="layanan">
                        <option value="">Semua Layanan</option>
                        @foreach (\App\Support\LaundryOptions::SERVICES as $serviceKey => $service)
                            <option value="{{ $serviceKey }}" @selected((string) $paketId === $serviceKey)>{{ $service['nama'] }}</option>
                        @endforeach
                    </select>
                    <label class="cucian-month-field">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg>
                        <select name="bulan" aria-label="Pilih bulan">
                            @foreach ($namaBulan as $nomor => $nama)
                                <option value="{{ $nomor }}" @selected((int) $bulan === (int) $nomor)>{{ $nama }}</option>
                            @endforeach
                        </select>
                    </label>
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                    <button class="cucian-filter-button" type="submit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16l-6.5 7.5V19l-3 1v-7.5z"/></svg>Filter</button>
                </form>
                <a class="cucian-reset" href="{{ route('cucian.index') }}">↻ &nbsp; Reset Filter</a>

                <div class="cucian-table-scroll">
                    <table class="cucian-table">
                        <thead>
                            <tr><th>No.</th><th>No. Nota</th><th>Pelanggan</th><th>No. Telp</th><th>Layanan</th><th>Berat/Qty</th><th>Tgl Masuk</th><th>Estimasi Selesai</th><th>Status Cucian</th><th>Total Harga</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($cucian as $index => $item)
                                @php
                                    $statusKey = strtolower($item->status_cucian);
                                    $statusColor = match ($statusKey) {
                                        'antrean', 'antrian' => '#67b5f6',
                                        'diproses', 'proses' => '#ff9d22',
                                        'dicuci' => '#3185ef',
                                        'dikeringkan', 'pengeringan' => '#20b8bf',
                                        'disetrika' => '#f2a21c',
                                        'selesai' => '#7552e8',
                                        'diambil' => '#55b96d',
                                        default => '#9aa4b4',
                                    };
                                    $statusText = match ($statusKey) {
                                        'antrean', 'antrian' => 'Antrean',
                                        'diproses', 'proses' => 'Diproses',
                                        'dikeringkan', 'pengeringan' => 'Dikeringkan',
                                        default => $filterStatus[$statusKey] ?? ucfirst($statusKey),
                                    };
                                    $jumlah = $item->detailTransaksi->sum('berat_qty');
                                    $layananText = $item->detailTransaksi->map(fn ($detail) => $detail->paketLaundry?->nama_paket)->filter()->unique()->implode(', ');
                                    $telepon = preg_replace('/\D+/', '', $item->pelanggan?->no_telp ?? '');
                                    $telepon = str_starts_with($telepon, '0') ? '62' . substr($telepon, 1) : $telepon;
                                @endphp
                                <tr>
                                    <td>{{ $cucian->firstItem() + $index }}</td>
                                    <td class="cucian-nota">{{ $item->no_nota }}</td>
                                    <td>{{ $item->pelanggan?->nama_pelanggan ?? 'Pelanggan dihapus' }}</td>
                                    <td class="cucian-phone">{{ $item->pelanggan?->no_telp ?? '—' }}</td>
                                    <td class="cucian-service" title="{{ $layananText }}">{{ $layananText ?: '—' }}</td>
                                    <td>{{ number_format((float) $jumlah, 2, ',', '.') }} {{ $item->detailTransaksi->contains(fn ($detail) => strtolower($detail->paketLaundry?->kategori ?? '') === 'satuan') ? 'pcs' : 'kg' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->tanggal_masuk)->format('d M Y') }}</td>
                                    <td>{{ $item->estimasi_selesai ? \Carbon\Carbon::parse($item->estimasi_selesai)->format('d M Y') : '—' }}</td>
                                    <td><span class="cucian-status" style="--status-color: {{ $statusColor }}">{{ $statusText }}</span></td>
                                    <td class="cucian-price">Rp {{ number_format((float) $item->total_harga, 0, ',', '.') }}</td>
                                    <td>
                                        <div class="cucian-row-actions">
                                            <a href="{{ route('cucian.show', $item) }}" class="row-action view" aria-label="Lihat {{ $item->no_nota }}" title="Lihat detail"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg></a>
                                            <a href="{{ route('cucian.edit', $item) }}" class="row-action edit" aria-label="Ubah {{ $item->no_nota }}" title="Ubah data"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 16-.8 4.8L8 20l11.6-11.6-4-4L4 16Z"/><path d="m13.8 6.2 4 4"/></svg></a>
                                            <form action="{{ route('cucian.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus data cucian {{ $item->no_nota }}?')">
                                                @csrf @method('DELETE')
                                                <button class="row-action delete" type="submit" aria-label="Hapus {{ $item->no_nota }}" title="Hapus data"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3m3 0-.8 13H6.8L6 7m4 4v5m4-5v5"/></svg></button>
                                            </form>
                                            @if ($telepon !== '')
                                                <a href="https://wa.me/{{ $telepon }}?text={{ rawurlencode('Halo ' . ($item->pelanggan?->nama_pelanggan ?? '') . ', kami menghubungi Anda terkait cucian dengan nota ' . $item->no_nota . '.') }}" target="_blank" rel="noopener" class="row-action whatsapp" aria-label="Hubungi {{ $item->pelanggan?->nama_pelanggan }} melalui WhatsApp" title="WhatsApp"><x-icons.whatsapp /></a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="11" class="cucian-empty">Tidak ada data cucian untuk filter yang dipilih.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="cucian-pagination">
                    <span>Menampilkan {{ $cucian->firstItem() ?? 0 }}–{{ $cucian->lastItem() ?? 0 }} dari {{ number_format($cucian->total(), 0, ',', '.') }} data</span>
                    @php
                        $lastPage = $cucian->lastPage();
                        $startPage = max(2, $cucian->currentPage() - 1);
                        $endPage = min($lastPage - 1, $cucian->currentPage() + 1);
                        if ($cucian->currentPage() <= 3) $endPage = min($lastPage - 1, 5);
                        if ($cucian->currentPage() >= $lastPage - 2) $startPage = max(2, $lastPage - 4);
                    @endphp
                    <nav class="cucian-pages" aria-label="Navigasi halaman">
                        @if ($cucian->onFirstPage())<span class="page-arrow disabled" aria-hidden="true">‹</span>@else<a class="page-arrow" href="{{ $cucian->previousPageUrl() }}" aria-label="Halaman sebelumnya">‹</a>@endif
                        <a class="{{ $cucian->currentPage() === 1 ? 'current' : '' }}" href="{{ $cucian->url(1) }}">1</a>
                        @if ($startPage > 2)<span class="page-ellipsis">…</span>@endif
                        @if ($lastPage > 1)
                            @foreach (range($startPage, max($startPage, $endPage)) as $page)
                                @if ($page < $lastPage)<a class="{{ $cucian->currentPage() === $page ? 'current' : '' }}" href="{{ $cucian->url($page) }}">{{ $page }}</a>@endif
                            @endforeach
                        @endif
                        @if ($endPage < $lastPage - 1)<span class="page-ellipsis">…</span>@endif
                        @if ($lastPage > 1)<a class="{{ $cucian->currentPage() === $lastPage ? 'current' : '' }}" href="{{ $cucian->url($lastPage) }}">{{ $lastPage }}</a>@endif
                        @if ($cucian->hasMorePages())<a class="page-arrow" href="{{ $cucian->nextPageUrl() }}" aria-label="Halaman berikutnya">›</a>@else<span class="page-arrow disabled" aria-hidden="true">›</span>@endif
                    </nav>
                    <label class="per-page">10 / halaman</label>
                </div>
            </article>

            <aside class="cucian-side-panels">
                <article class="cucian-panel status-summary-panel">
                    <h2>Ringkasan Status</h2>
                    <div class="cucian-status-summary">
                        <div class="cucian-donut" style="--donut-background: {{ $donutGradient }}">
                            <div><strong>{{ number_format($totalStatus, 0, ',', '.') }}</strong><span>Total</span></div>
                        </div>
                        <div class="cucian-status-legend">
                            @forelse ($statusRingkasan as $item)
                                <div><span><i style="--status-color: {{ $item->warna }}"></i>{{ $item->label }}</span><small>{{ number_format($item->jumlah, 0, ',', '.') }} ({{ $totalStatus ? number_format(($item->jumlah / $totalStatus) * 100, 1, ',', '.') : '0,0' }}%)</small></div>
                            @empty
                                <p>Belum ada status cucian pada bulan ini.</p>
                            @endforelse
                        </div>
                    </div>
                </article>

                <article class="cucian-panel activity-panel">
                    <h2>Aktivitas Terbaru</h2>
                    <div class="activity-list">
                        @forelse ($aktivitas as $item)
                            @php($activityStatus = strtolower($item->status))
                            <div class="activity-item">
                                <span class="activity-icon" style="--activity-color: {{ $item->warna }}">
                                    @if (in_array($activityStatus, ['antrean', 'antrian'], true))
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                                    @elseif ($activityStatus === 'selesai')
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
                                    @elseif ($activityStatus === 'diambil')
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 10h14v11H5zM9 10V7a3 3 0 0 1 6 0v3"/></svg>
                                    @else
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 16.5V20l3.5-.5L19 8l-3-3L4 16.5Z"/><path d="m14.5 6.5 3 3"/></svg>
                                    @endif
                                </span>
                                <div><strong>{{ $item->label }}</strong><small>{{ $item->no_nota }} · {{ $item->nama_pelanggan }}{{ $item->label === 'Status cucian diperbarui' ? ' · ' . $item->status_label : '' }}</small></div>
                                <time datetime="{{ \Carbon\Carbon::parse($item->waktu_status)->toIso8601String() }}">{{ \Carbon\Carbon::parse($item->waktu_status)->format('H.i') }}</time>
                            </div>
                        @empty
                            <p class="cucian-activity-empty">Belum ada aktivitas cucian.</p>
                        @endforelse
                    </div>
                </article>
            </aside>
        </section>
    </div>
</x-layouts::app>
