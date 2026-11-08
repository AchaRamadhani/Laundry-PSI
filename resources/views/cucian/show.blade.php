<x-layouts::app :title="'Detail Cucian ' . $cucian->no_nota">
    @php
        $statusKey = strtolower($cucian->status_cucian);
        $statusLabels = ['antrean' => 'Antrean', 'antrian' => 'Antrean', 'diproses' => 'Diproses', 'proses' => 'Diproses', 'dicuci' => 'Dicuci', 'dikeringkan' => 'Dikeringkan', 'disetrika' => 'Disetrika', 'selesai' => 'Selesai', 'diambil' => 'Diambil'];
        $statusColors = ['antrean' => '#67b5f6', 'antrian' => '#67b5f6', 'diproses' => '#ff9d22', 'proses' => '#ff9d22', 'dicuci' => '#3185ef', 'dikeringkan' => '#20b8bf', 'disetrika' => '#f2a21c', 'selesai' => '#7552e8', 'diambil' => '#55b96d'];
    @endphp
    <div class="cucian-page cucian-form-page">
        <section class="cucian-heading form-heading">
            <div><h1>Detail Data Cucian</h1><p>Informasi transaksi {{ $cucian->no_nota }}.</p></div>
            <div class="cucian-detail-actions">
                <a class="cucian-secondary-button" href="{{ route('cucian.index') }}">← Kembali</a>
                <a class="cucian-add-button" href="{{ route('cucian.edit', $cucian) }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 16-.8 4.8L8 20l11.6-11.6-4-4L4 16Z"/></svg>Ubah Data</a>
            </div>
        </section>

        <div class="cucian-show-grid">
            <article class="cucian-panel cucian-detail-card">
                <div class="detail-card-heading"><div><small>NO. NOTA</small><h2>{{ $cucian->no_nota }}</h2></div><span class="cucian-status" style="--status-color: {{ $statusColors[$statusKey] ?? '#9aa4b4' }}">{{ $statusLabels[$statusKey] ?? ucfirst($statusKey) }}</span></div>
                <div class="cucian-info-grid">
                    <div><small>Nama Pelanggan</small><strong>{{ $cucian->pelanggan?->nama_pelanggan ?? 'Pelanggan dihapus' }}</strong></div>
                    <div><small>Nomor Telepon</small><strong>{{ $cucian->pelanggan?->no_telp ?? '—' }}</strong></div>
                    <div><small>Token Tracking</small><strong>{{ $cucian->tracking_token }}</strong></div>
                    <div><small>Tanggal Masuk</small><strong>{{ \Carbon\Carbon::parse($cucian->tanggal_masuk)->format('d F Y') }}</strong></div>
                    <div><small>Estimasi Selesai</small><strong>{{ $cucian->estimasi_selesai ? \Carbon\Carbon::parse($cucian->estimasi_selesai)->format('d F Y H.i') : 'Belum ditentukan' }}</strong></div>
                </div>
                <h3 class="detail-subheading">Rincian Layanan</h3>
                <div class="cucian-table-scroll">
                    <table class="cucian-table detail-table"><thead><tr><th>Layanan</th><th>Berat / Qty</th><th>Harga Satuan</th><th>Subtotal</th></tr></thead>
                        <tbody>
                            @foreach ($cucian->detailTransaksi as $detail)
                                <tr><td>{{ $detail->paketLaundry?->nama_paket ?? 'Paket dihapus' }}</td><td>{{ number_format((float) $detail->berat_qty, 2, ',', '.') }} {{ strtolower($detail->paketLaundry?->kategori ?? '') === 'satuan' ? 'pcs' : 'kg' }}</td><td>Rp {{ number_format((float) ($detail->paketLaundry?->harga_mulai ?? 0), 0, ',', '.') }}</td><td>Rp {{ number_format((float) $detail->subtotal, 0, ',', '.') }}</td></tr>
                            @endforeach
                        </tbody>
                        <tfoot><tr><th colspan="3">Total Harga</th><th>Rp {{ number_format((float) $cucian->total_harga, 0, ',', '.') }}</th></tr></tfoot>
                    </table>
                </div>
                @if ($cucian->catatan)<div class="cucian-note detail-note"><strong>Catatan:</strong> {{ $cucian->catatan }}</div>@endif
            </article>

            <article class="cucian-panel cucian-history-card">
                <h2>Riwayat Status</h2>
                <div class="status-history-list">
                    @forelse ($cucian->statusLaundry->sortByDesc('waktu_status') as $riwayat)
                        @php($historyKey = strtolower($riwayat->status))
                        <div class="status-history-item">
                            <i style="--status-color: {{ $statusColors[$historyKey] ?? '#9aa4b4' }}"></i>
                            <div><strong>{{ $statusLabels[$historyKey] ?? ucfirst($historyKey) }}</strong><small>{{ $riwayat->admin?->name ?? 'Admin' }}{{ $riwayat->catatan_status ? ' · ' . $riwayat->catatan_status : '' }}</small><time>{{ \Carbon\Carbon::parse($riwayat->waktu_status)->format('d M Y, H.i') }}</time></div>
                        </div>
                    @empty
                        <p>Belum ada catatan riwayat status.</p>
                    @endforelse
                </div>
            </article>
        </div>
    </div>
</x-layouts::app>
