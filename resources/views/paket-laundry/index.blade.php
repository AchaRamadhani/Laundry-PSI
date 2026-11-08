<x-layouts::app :title="'Paket Laundry'">
    @php
        $lastPage = $paketLaundry->lastPage();
        $startPage = max(2, $paketLaundry->currentPage() - 1);
        $endPage = min($lastPage - 1, $paketLaundry->currentPage() + 1);
        if ($paketLaundry->currentPage() <= 3) $endPage = min($lastPage - 1, 5);
        if ($paketLaundry->currentPage() >= $lastPage - 2) $startPage = max(2, $lastPage - 4);
    @endphp

    <div class="paket-page">
        <section class="paket-heading">
            <div><h1>Paket Laundry</h1><p>Kelola daftar layanan dan paket laundry yang tersedia di G'Laundry.</p></div>
        </section>

        @if (session('success'))<div class="cucian-alert success" role="status">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="cucian-alert error" role="alert">{{ session('error') }}</div>@endif

        <section class="paket-metrics" aria-label="Ringkasan paket laundry">
            <article class="paket-metric"><span class="paket-metric-icon blue"><svg viewBox="0 0 32 32" aria-hidden="true"><path d="m16 3 11 6v14l-11 6-11-6V9zM5 9l11 6 11-6M16 15v14"/></svg></span><div><span>Total Paket</span><strong>{{ number_format($totalPaket, 0, ',', '.') }} Paket</strong><small>Semua paket tersedia</small></div></article>
            <article class="paket-metric"><span class="paket-metric-icon green"><svg viewBox="0 0 32 32" aria-hidden="true"><path d="m16 3 11 4v8c0 7-5 12-11 14C10 27 5 22 5 15V7z"/><path d="m10 16 4 4 8-9"/></svg></span><div><span>Paket Aktif</span><strong>{{ number_format($paketAktif, 0, ',', '.') }} Paket</strong><small>{{ $totalPaket ? number_format(($paketAktif / $totalPaket) * 100, 0) : 0 }}% dari total paket</small></div></article>
            <article class="paket-metric"><span class="paket-metric-icon purple"><svg viewBox="0 0 32 32" aria-hidden="true"><path d="M7 13h18l2 15H5zM11 13V9a5 5 0 0 1 10 0v4"/></svg></span><div><span>Layanan Kiloan</span><strong>{{ number_format($layananKiloan, 0, ',', '.') }} Paket</strong><small>Berdasarkan kategori</small></div></article>
            <article class="paket-metric"><span class="paket-metric-icon orange"><svg viewBox="0 0 32 32" aria-hidden="true"><path d="m16 3 4 8 9 1-6.5 6.2 1.5 9L16 23l-8 4.2 1.5-9L3 12l9-1z"/></svg></span><div><span>Layanan Khusus</span><strong>{{ number_format($layananKhusus, 0, ',', '.') }} Paket</strong><small>Non-kiloan</small></div></article>
        </section>

        <div class="paket-toolbar">
            <a class="paket-add-button" href="{{ route('paket-laundry.create') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>Tambah Paket</a>
            <form class="paket-filters" action="{{ route('paket-laundry.index') }}" method="GET">
                <label class="paket-search"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m16 16 5 5"/></svg><input type="search" name="search" value="{{ $search }}" placeholder="Cari nama, kategori, atau deskripsi..." aria-label="Cari paket laundry"></label>
                <label class="sr-only" for="paket-kategori-filter">Semua kategori</label>
                <select id="paket-kategori-filter" name="kategori"><option value="">Semua Kategori</option>@foreach ($kategoriOptions as $option)<option value="{{ $option }}" @selected($kategori === $option)>{{ $option }}</option>@endforeach</select>
                <label class="sr-only" for="paket-status-filter">Semua status</label>
                <select id="paket-status-filter" name="status"><option value="">Semua Status</option><option value="aktif" @selected($status === 'aktif')>Aktif</option><option value="nonaktif" @selected($status === 'nonaktif')>Nonaktif</option></select>
                <button class="paket-filter-button" type="submit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16l-6.5 7.5V19l-3 1v-7.5z"/></svg>Filter</button>
                <a class="paket-reset-button" href="{{ route('paket-laundry.index') }}">Reset</a>
            </form>
        </div>

        <section class="paket-content-grid">
            <div class="paket-list-column">
                <div class="paket-card-grid">
                    @forelse ($paketLaundry as $paket)
                        @php
                            $kategoriKey = strtolower($paket->kategori);
                            $unitHarga = str_contains($kategoriKey, 'satuan') ? 'pcs' : 'kg';
                            $iconTone = str_contains(strtolower($paket->nama_paket), 'setrika') ? 'orange' : (str_contains(strtolower($paket->nama_paket), 'bayi') ? 'pink' : (str_contains(strtolower($paket->nama_paket), 'express') ? 'blue' : (str_contains($kategoriKey, 'khusus') ? 'purple' : 'blue')));
                            $isActive = strtolower($paket->status) === 'aktif';
                        @endphp
                        <article class="paket-card">
                            <div class="paket-card-top">
                                <span class="paket-card-icon {{ $iconTone }}">
                                    @if (str_contains(strtolower($paket->nama_paket), 'setrika'))<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 16h15l2 3H4zM7 16l2-7h7a4 4 0 0 1 4 4M10 6h5"/></svg>
                                    @elseif (str_contains(strtolower($paket->nama_paket), 'bayi'))<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 14 7l5 1-3.5 3.5.8 5L12 14l-4.3 2.5.8-5L5 8l5-1z"/><circle cx="12" cy="20" r="1"/></svg>
                                    @elseif (str_contains($kategoriKey, 'kilo'))<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 11h14l1 9H4zM8 11V8a4 4 0 0 1 8 0v3"/><path d="M9 15h6"/></svg>
                                    @else<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M12 5v14M7 7l10 10M17 7 7 17"/></svg>@endif
                                </span>
                                <div class="paket-card-title"><h2>{{ $paket->nama_paket }}</h2><p>{{ $paket->deskripsi ?: $paket->kategori }}</p></div>
                                <span class="paket-state {{ $isActive ? 'active' : 'inactive' }}">{{ $isActive ? 'Aktif' : 'Nonaktif' }}</span>
                            </div>
                            <div class="paket-card-meta">
                                <div><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 16 11-11 5 5-11 11-5 1zM14 6l4 4"/></svg><span><small>Harga Mulai</small><strong>Rp {{ number_format((float) $paket->harga_mulai, 0, ',', '.') }} / {{ $unitHarga }}</strong></span></div>
                                <div><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg><span><small>Durasi</small><strong>{{ number_format($paket->durasi, 0, ',', '.') }} hari</strong></span></div>
                            </div>
                            <div class="paket-card-actions">
                                <a href="{{ route('paket-laundry.show', $paket) }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>Lihat</a>
                                <a href="{{ route('paket-laundry.edit', $paket) }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 16-.8 4.8L8 20l11.6-11.6-4-4L4 16Z"/><path d="m13.8 6.2 4 4"/></svg>Edit</a>
                                <form action="{{ route('paket-laundry.destroy', $paket) }}" method="POST" onsubmit="return confirm('Hapus paket {{ $paket->nama_paket }}?')">@csrf @method('DELETE')<button type="submit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3m3 0-.8 13H6.8L6 7m4 4v5m4-5v5"/></svg>Hapus</button></form>
                            </div>
                        </article>
                    @empty
                        <div class="paket-empty-state">
                            <span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 9 5v8l-9 5-9-5V8zM3 8l9 5 9-5m-9 5v8"/></svg></span>
                            @if ($totalPaket === 0)
                                <h2>Belum ada paket laundry</h2><p>Database belum memiliki paket. Tambahkan paket asli milik laundry Anda untuk mulai mengelolanya.</p>
                                <a class="paket-add-button" href="{{ route('paket-laundry.create') }}">Tambah Paket Pertama</a>
                            @else
                                <h2>Paket tidak ditemukan</h2><p>Coba ubah kata pencarian atau filter kategori dan status.</p>
                            @endif
                        </div>
                    @endforelse
                </div>
                @if ($paketLaundry->hasPages())
                    <div class="paket-pagination"><span>Menampilkan {{ $paketLaundry->firstItem() }}–{{ $paketLaundry->lastItem() }} dari {{ number_format($paketLaundry->total(), 0, ',', '.') }} paket</span><nav aria-label="Navigasi halaman">
                        @if ($paketLaundry->onFirstPage())<span class="page-arrow disabled">‹</span>@else<a class="page-arrow" href="{{ $paketLaundry->previousPageUrl() }}">‹</a>@endif
                        <a class="{{ $paketLaundry->currentPage() === 1 ? 'current' : '' }}" href="{{ $paketLaundry->url(1) }}">1</a>
                        @if ($startPage > 2)<span>…</span>@endif
                        @if ($lastPage > 1)@foreach (range($startPage, max($startPage, $endPage)) as $page)@if ($page < $lastPage)<a class="{{ $paketLaundry->currentPage() === $page ? 'current' : '' }}" href="{{ $paketLaundry->url($page) }}">{{ $page }}</a>@endif @endforeach @endif
                        @if ($endPage < $lastPage - 1)<span>…</span>@endif
                        @if ($lastPage > 1)<a class="{{ $paketLaundry->currentPage() === $lastPage ? 'current' : '' }}" href="{{ $paketLaundry->url($lastPage) }}">{{ $lastPage }}</a>@endif
                        @if ($paketLaundry->hasMorePages())<a class="page-arrow" href="{{ $paketLaundry->nextPageUrl() }}">›</a>@else<span class="page-arrow disabled">›</span>@endif
                    </nav></div>
                @endif
            </div>

            <aside class="cucian-panel paket-summary-panel">
                <h2>Ringkasan Kategori Paket</h2>
                <table><thead><tr><th>Kategori</th><th>Jumlah</th><th>Persentase</th></tr></thead>
                    <tbody>
                        @forelse ($kategoriRingkasan as $kategoriData)
                            @php($persentase = $jumlahKategori ? ($kategoriData->jumlah / $jumlahKategori) * 100 : 0)
                            <tr><td>{{ $kategoriData->kategori }}</td><td>{{ number_format($kategoriData->jumlah, 0, ',', '.') }}</td><td><div class="paket-category-share"><small>{{ number_format($persentase, 1, ',', '.') }}%</small><span><i style="width: {{ $persentase }}%"></i></span></div></td></tr>
                        @empty
                            <tr><td colspan="3" class="paket-summary-empty">Belum ada kategori paket.</td></tr>
                        @endforelse
                    </tbody>
                    @if ($jumlahKategori)<tfoot><tr><th>Total</th><th>{{ number_format($jumlahKategori, 0, ',', '.') }}</th><th>100%</th></tr></tfoot>@endif
                </table>
                <p>Ringkasan dihitung dari semua paket yang tersimpan.</p>
            </aside>
        </section>
    </div>
</x-layouts::app>
