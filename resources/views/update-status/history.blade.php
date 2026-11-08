<x-layouts::app :title="'Riwayat Status Cucian'">
    <div class="update-status-page status-history-page">
        <section class="update-status-heading history-page-heading">
            <div><h1>Riwayat Status Cucian</h1><p>Catatan semua perubahan status yang tersimpan di sistem.</p></div>
            <a class="history-back-button" href="{{ route('update-status.index') }}">← Kembali ke Update Status</a>
        </section>

        <article class="cucian-panel full-status-history-panel">
            <div class="full-history-title"><div><h2>Riwayat Perubahan Status</h2><p>{{ number_format($riwayat->total(), 0, ',', '.') }} perubahan status</p></div></div>
            <div class="cucian-table-scroll">
                <table class="update-history-table">
                    <thead><tr><th>Tanggal &amp; Waktu</th><th>No. Nota</th><th>Pelanggan</th><th>Status Baru</th><th>Catatan</th><th>Admin</th><th>Detail</th></tr></thead>
                    <tbody>
                        @forelse ($riwayat as $item)
                            @php
                                $key = match (strtolower($item->status)) { 'antrian' => 'antrean', 'proses' => 'diproses', 'pengeringan' => 'dikeringkan', default => strtolower($item->status) };
                                $labels = ['antrean' => 'Antrean', 'diproses' => 'Diproses', 'dicuci' => 'Dicuci', 'dikeringkan' => 'Dikeringkan', 'disetrika' => 'Disetrika', 'selesai' => 'Selesai', 'diambil' => 'Diambil'];
                                $colors = ['antrean' => '#65b6f7', 'diproses' => '#f49535', 'dicuci' => '#286ee8', 'dikeringkan' => '#28aeca', 'disetrika' => '#ff885c', 'selesai' => '#7655ed', 'diambil' => '#19a879'];
                            @endphp
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($item->waktu_status)->format('d M Y, H.i') }}</td>
                                <td class="update-history-nota">{{ $item->no_nota }}</td>
                                <td>{{ $item->transaksi?->pelanggan?->nama_pelanggan ?? 'Pelanggan dihapus' }}</td>
                                <td><span class="update-status-badge" style="--status-color: {{ $colors[$key] ?? '#9aa4b4' }}"><i></i>{{ $labels[$key] ?? ucfirst($key) }}</span></td>
                                <td>{{ $item->catatan_status ?: '—' }}</td>
                                <td>{{ $item->admin?->name ?? 'Admin' }}</td>
                                <td><a class="history-view-link" href="{{ route('cucian.show', $item->no_nota) }}">Lihat nota</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="update-status-empty">Belum ada riwayat perubahan status.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="status-history-pagination">{{ $riwayat->links() }}</div>
        </article>
    </div>
</x-layouts::app>
