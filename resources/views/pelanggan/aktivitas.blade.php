<x-layouts::app :title="'Aktivitas Pelanggan'">
    <div class="pelanggan-page pelanggan-activity-page">
        <section class="pelanggan-heading pelanggan-form-heading">
            <div><h1>Aktivitas Pelanggan</h1><p>Riwayat transaksi terbaru dari seluruh pelanggan.</p></div>
            <a class="pelanggan-secondary-button" href="{{ route('pelanggan.index') }}">← Kembali ke Pelanggan</a>
        </section>
        <article class="cucian-panel pelanggan-full-activity-card">
            <h2>Aktivitas Laundry</h2>
            <div class="cucian-table-scroll">
                <table class="pelanggan-history-table"><thead><tr><th>Pelanggan</th><th>Aktivitas</th><th>No. Nota</th><th>Tanggal</th><th>Total</th><th>Detail</th></tr></thead>
                    <tbody>
                        @forelse ($aktivitas as $item)
                            @php($aktivitasLabel = match (strtolower($item->status_cucian)) { 'selesai' => 'Pesanan selesai', 'diambil' => 'Cucian telah diambil', default => 'Transaksi cucian dibuat' })
                            <tr><td>{{ $item->pelanggan?->nama_pelanggan ?? 'Pelanggan dihapus' }}</td><td>{{ $aktivitasLabel }}</td><td class="pelanggan-code">{{ $item->no_nota }}</td><td>{{ $item->created_at?->format('d M Y, H.i') ?? '—' }}</td><td>Rp {{ number_format((float) $item->total_harga, 0, ',', '.') }}</td><td><a class="history-view-link" href="{{ route('cucian.show', $item->no_nota) }}">Lihat nota</a></td></tr>
                        @empty
                            <tr><td colspan="6" class="cucian-empty">Belum ada aktivitas pelanggan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="status-history-pagination">{{ $aktivitas->links() }}</div>
        </article>
    </div>
</x-layouts::app>
