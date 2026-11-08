<x-layouts::app :title="'Segmentasi Pelanggan'">
    <div class="segmentasi-page">
        <section class="segmentasi-heading">
            <h1>Segmentasi Pelanggan</h1>
            <p>Kenali pola pelanggan dan jaga hubungan tetap dekat.</p>
        </section>

        <section class="segmentasi-metrics" aria-label="Ringkasan segmen pelanggan">
            <article class="segmentasi-metric"><span class="segment-icon blue"><svg viewBox="0 0 32 32" aria-hidden="true"><circle cx="12" cy="11" r="5"/><path d="M3 27v-2a9 9 0 0 1 18 0v2zM21 8a4 4 0 1 1 0 8m2 3a7 7 0 0 1 6 7v1h-5"/></svg></span><div><span>Total Pelanggan</span><strong>{{ number_format($jumlahPelanggan, 0, ',', '.') }}</strong><small>Orang</small></div></article>
            <article class="segmentasi-metric"><span class="segment-icon green"><svg viewBox="0 0 32 32" aria-hidden="true"><path d="m16 4 4 7 8 1-5.8 5.5 1.4 8L16 22l-7.6 3.5 1.4-8L4 12l8-1z"/><circle cx="16" cy="4" r="1.5"/></svg></span><div><span>Pelanggan Loyal</span><strong>{{ number_format($pelangganLoyal, 0, ',', '.') }}</strong><small>Orang</small></div></article>
            <article class="segmentasi-metric"><span class="segment-icon orange"><svg viewBox="0 0 32 32" aria-hidden="true"><rect x="5" y="17" width="5" height="11" rx="1"/><rect x="14" y="10" width="5" height="18" rx="1"/><rect x="23" y="4" width="5" height="24" rx="1"/></svg></span><div><span>Pelanggan Potensial</span><strong>{{ number_format($pelangganPotensial, 0, ',', '.') }}</strong><small>Orang</small></div></article>
            <article class="segmentasi-metric"><span class="segment-icon red"><svg viewBox="0 0 32 32" aria-hidden="true"><path d="M8 22V14a8 8 0 0 1 16 0v8l3 3H5zM13 27a3 3 0 0 0 6 0M16 3V1M5 7 3 5m24 2 2-2"/></svg></span><div><span>Perlu Diaktifkan</span><strong>{{ number_format($pelangganKurangAktif, 0, ',', '.') }}</strong><small>Orang</small></div></article>
        </section>

        <section class="segment-groups-panel">
            <h2>Kelompok Segmentasi</h2>
            <div class="segment-groups">
                <article class="segment-group-card loyal">
                    <span class="segment-group-icon"><svg viewBox="0 0 32 32" aria-hidden="true"><path d="m16 4 4 7 8 1-5.8 5.5 1.4 8L16 22l-7.6 3.5 1.4-8L4 12l8-1z"/><circle cx="16" cy="4" r="1.5"/></svg></span>
                    <div><h3>Loyal</h3><p>Pelanggan dengan 6 atau lebih transaksi dalam 90 hari terakhir dan transaksi dalam 30 hari terakhir.</p><strong>{{ number_format($pelangganLoyal, 0, ',', '.') }} <small>Pelanggan</small></strong></div>
                </article>
                <article class="segment-group-card potential">
                    <span class="segment-group-icon"><svg viewBox="0 0 32 32" aria-hidden="true"><rect x="5" y="17" width="5" height="11" rx="1"/><rect x="14" y="10" width="5" height="18" rx="1"/><rect x="23" y="4" width="5" height="24" rx="1"/></svg></span>
                    <div><h3>Potensial</h3><p>Pelanggan yang masih bertransaksi dalam 60 hari terakhir, tetapi belum masuk kelompok loyal.</p><strong>{{ number_format($pelangganPotensial, 0, ',', '.') }} <small>Pelanggan</small></strong></div>
                </article>
                <article class="segment-group-card inactive">
                    <span class="segment-group-icon"><svg viewBox="0 0 32 32" aria-hidden="true"><path d="M8 22V14a8 8 0 0 1 16 0v8l3 3H5zM13 27a3 3 0 0 0 6 0M16 3V1M5 7 3 5m24 2 2-2"/></svg></span>
                    <div><h3>Kurang Aktif</h3><p>Pelanggan tanpa transaksi dalam 60 hari terakhir atau belum pernah bertransaksi.</p><strong>{{ number_format($pelangganKurangAktif, 0, ',', '.') }} <small>Pelanggan</small></strong></div>
                </article>
            </div>
        </section>

        <section class="segment-customer-panel">
            <div class="segment-table-heading">
                <div><h2>Pelanggan {{ $segmen ? ucfirst(str_replace('-', ' ', $segmen)) : 'Semua Segmen' }}</h2><p>Segmentasi dihitung dari frekuensi transaksi dan aktivitas terbaru.</p></div>
                <form action="{{ route('segmentasi.index') }}" method="GET" class="segment-table-filters">
                    <label class="segment-search"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m16 16 5 5"/></svg><input type="search" name="search" value="{{ $search }}" placeholder="Cari pelanggan..." aria-label="Cari pelanggan"></label>
                    <label class="sr-only" for="filter-segmen">Semua segmen</label>
                    <select name="segmen" id="filter-segmen" onchange="this.form.submit()"><option value="">Semua Segmen</option><option value="loyal" @selected($segmen === 'loyal')>Loyal</option><option value="potensial" @selected($segmen === 'potensial')>Potensial</option><option value="kurang-aktif" @selected($segmen === 'kurang-aktif')>Kurang Aktif</option></select>
                    <button class="sr-only" type="submit">Cari</button>
                </form>
            </div>

            <div class="cucian-table-scroll segment-table-scroll">
                <table class="segment-customer-table">
                    <thead><tr><th>Nama Pelanggan</th><th>No. Telp</th><th>Lama Menjadi Pelanggan</th><th>Jumlah Transaksi</th><th>Transaksi Terakhir</th><th>Segmen</th></tr></thead>
                    <tbody>
                        @forelse ($pelanggan as $item)
                            <tr>
                                <td class="segment-customer-name"><a href="{{ route('pelanggan.show', $item) }}">{{ $item->nama_pelanggan }}</a></td>
                                <td class="segment-phone">{{ $item->no_telp }}</td>
                                <td>{{ $item->lama_menjadi_pelanggan }}</td>
                                <td>{{ number_format($item->jumlah_transaksi, 0, ',', '.') }}</td>
                                <td>{{ $item->transaksi_terakhir ? \Carbon\Carbon::parse($item->transaksi_terakhir)->format('d M Y') : '—' }}</td>
                                <td><span class="segment-pill {{ $item->segment_key }}">{{ $item->segment_label }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="segment-table-empty">@if ($jumlahPelanggan === 0)Belum ada data pelanggan untuk disegmentasikan.@else Tidak ada pelanggan yang cocok dengan pencarian atau segmen ini.@endif</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="segment-pagination">
                <span>Menampilkan {{ $pelanggan->firstItem() ?? 0 }}–{{ $pelanggan->lastItem() ?? 0 }} dari {{ number_format($pelanggan->total(), 0, ',', '.') }} pelanggan</span>
                {{ $pelanggan->links() }}
            </div>
        </section>
    </div>
</x-layouts::app>
