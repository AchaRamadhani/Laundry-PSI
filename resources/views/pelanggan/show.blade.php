<x-layouts::app :title="'Detail Pelanggan'">
    <div class="pelanggan-page pelanggan-detail-page">
        <section class="pelanggan-heading pelanggan-form-heading">
            <div><h1>Detail Pelanggan</h1><p>Informasi kontak dan riwayat transaksi pelanggan.</p></div>
            <div class="pelanggan-detail-actions"><a class="pelanggan-secondary-button" href="{{ route('pelanggan.index') }}">← Kembali</a><a class="pelanggan-add-button" href="{{ route('pelanggan.edit', $pelanggan) }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 16-.8 4.8L8 20l11.6-11.6-4-4L4 16Z"/></svg>Ubah Data</a></div>
        </section>

        <div class="pelanggan-detail-grid">
            <article class="cucian-panel pelanggan-profile-card">
                <div class="pelanggan-profile-top"><span class="pelanggan-profile-avatar"><svg viewBox="0 0 32 32" aria-hidden="true"><circle cx="16" cy="10" r="6"/><path d="M4 29v-3a12 12 0 0 1 24 0v3z"/></svg></span><div><small>ID Pelanggan</small><strong>PLG-GL-{{ str_pad((string) $pelanggan->id, 3, '0', STR_PAD_LEFT) }}</strong><h2>{{ $pelanggan->nama_pelanggan }}</h2></div></div>
                <div class="pelanggan-profile-data"><div><small>Nomor Telepon</small><strong>{{ $pelanggan->no_telp }}</strong></div><div><small>Terdaftar Sejak</small><strong>{{ $pelanggan->created_at?->format('d M Y') ?? '—' }}</strong></div><div><small>Total Transaksi</small><strong>{{ number_format($jumlahTransaksi, 0, ',', '.') }}</strong></div></div>
            </article>

            <article class="cucian-panel pelanggan-history-card">
                <div class="pelanggan-history-heading"><div><h2>Riwayat Transaksi</h2><p>10 transaksi terbaru pelanggan ini.</p></div></div>
                <div class="cucian-table-scroll">
                    <table class="pelanggan-history-table"><thead><tr><th>No. Nota</th><th>Tanggal Masuk</th><th>Layanan</th><th>Total</th><th>Status</th><th>Detail</th></tr></thead>
                        <tbody>
                            @forelse ($pelanggan->transaksi as $item)
                                @php
                                    $statusKey = strtolower($item->status_cucian);
                                    $label = match ($statusKey) { 'antrian' => 'Antrean', 'proses' => 'Diproses', 'pengeringan' => 'Dikeringkan', default => ucfirst($statusKey) };
                                    $color = match ($statusKey) { 'antrean', 'antrian' => '#62b5f7', 'proses', 'diproses' => '#ff9f1c', 'dicuci' => '#3588ef', 'dikeringkan', 'pengeringan' => '#20b7bd', 'disetrika' => '#f5a11b', 'selesai' => '#7552e8', 'diambil' => '#55b85f', default => '#9aa4b4' };
                                    $services = $item->detailTransaksi->map(fn ($detail) => $detail->paketLaundry?->nama_paket)->filter()->unique()->implode(', ');
                                @endphp
                                <tr><td class="pelanggan-code">{{ $item->no_nota }}</td><td>{{ \Carbon\Carbon::parse($item->tanggal_masuk)->format('d M Y') }}</td><td>{{ $services ?: '—' }}</td><td>Rp {{ number_format((float) $item->total_harga, 0, ',', '.') }}</td><td><span class="cucian-status" style="--status-color: {{ $color }}">{{ $label }}</span></td><td><a class="history-view-link" href="{{ route('cucian.show', $item->no_nota) }}">Lihat nota</a></td></tr>
                            @empty
                                <tr><td colspan="6" class="cucian-empty">Pelanggan ini belum memiliki transaksi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        </div>
    </div>
</x-layouts::app>
