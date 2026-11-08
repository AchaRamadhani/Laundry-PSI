<x-layouts::app :title="'Aktivitas Nota'">
    <div class="transaksi-page transaksi-activity-page">
        <section class="transaksi-heading">
            <div><h1>Aktivitas Nota</h1><p>Riwayat nota transaksi yang dibuat saat data cucian disimpan.</p></div>
            <a class="cucian-secondary-button" href="{{ route('transaksi.index') }}">← Kembali ke Transaksi</a>
        </section>

        <article class="cucian-panel activity-full-panel">
            <div class="activity-full-heading"><h2>Riwayat Nota</h2><span>{{ number_format($transaksi->total(), 0, ',', '.') }} transaksi</span></div>
            <div class="cucian-table-scroll">
                <table class="transaksi-table activity-full-table">
                    <thead><tr><th>No. Nota</th><th>Pelanggan</th><th>Tanggal Dibuat</th><th>Tanggal Masuk</th><th>Total</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>
                        @forelse ($transaksi as $item)
                            @php
                                $key = strtolower($item->status_cucian);
                                $label = match ($key) { 'antrean', 'antrian' => 'Antrean', 'proses', 'diproses' => 'Diproses', 'dikeringkan', 'pengeringan' => 'Dikeringkan', default => ucfirst($key) };
                                $color = match ($key) { 'antrean', 'antrian' => '#62b5f7', 'proses', 'diproses' => '#ff9f1c', 'dicuci' => '#3588ef', 'dikeringkan', 'pengeringan' => '#20b7bd', 'disetrika' => '#f5a11b', 'selesai' => '#7552e8', 'diambil' => '#55b85f', default => '#9aa4b4' };
                            @endphp
                            <tr>
                                <td class="transaksi-nota">{{ $item->no_nota }}</td>
                                <td>{{ $item->pelanggan?->nama_pelanggan ?? 'Pelanggan dihapus' }}</td>
                                <td>{{ $item->created_at?->format('d M Y, H.i') ?? '—' }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->tanggal_masuk)->format('d M Y') }}</td>
                                <td class="transaksi-price">Rp {{ number_format((float) $item->total_harga, 0, ',', '.') }}</td>
                                <td><span class="cucian-status" style="--status-color: {{ $color }}">{{ $label }}</span></td>
                                <td><a class="row-action view" href="{{ route('cucian.show', $item->no_nota) }}" aria-label="Lihat nota {{ $item->no_nota }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="cucian-empty">Belum ada aktivitas nota.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="activity-full-pagination">{{ $transaksi->links() }}</div>
        </article>
    </div>
</x-layouts::app>
