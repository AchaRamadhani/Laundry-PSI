<x-layouts::app :title="'Detail Paket Laundry'">
    @php
        $unitHarga = str_contains(strtolower($paket->kategori), 'satuan') ? 'pcs' : 'kg';
        $aktif = strtolower($paket->status) === 'aktif';
    @endphp
    <div class="paket-page paket-detail-page">
        <section class="paket-heading paket-form-heading">
            <div><h1>Detail Paket Laundry</h1><p>Informasi paket dan penggunaannya dalam transaksi.</p></div>
            <div class="paket-detail-actions"><a class="paket-secondary-button" href="{{ route('paket-laundry.index') }}">← Kembali</a><a class="paket-add-button" href="{{ route('paket-laundry.edit', $paket) }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 16-.8 4.8L8 20l11.6-11.6-4-4L4 16Z"/></svg>Edit Paket</a></div>
        </section>

        <div class="paket-detail-grid">
            <article class="cucian-panel paket-detail-card">
                <div class="paket-detail-title"><div><small>KATEGORI {{ strtoupper($paket->kategori) }}</small><h2>{{ $paket->nama_paket }}</h2></div><span class="paket-state {{ $aktif ? 'active' : 'inactive' }}">{{ $aktif ? 'Aktif' : 'Nonaktif' }}</span></div>
                <p class="paket-detail-description">{{ $paket->deskripsi ?: 'Tidak ada deskripsi tambahan untuk paket ini.' }}</p>
                <div class="paket-detail-stats"><div><small>Harga Mulai</small><strong>Rp {{ number_format((float) $paket->harga_mulai, 0, ',', '.') }} / {{ $unitHarga }}</strong></div><div><small>Durasi</small><strong>{{ number_format($paket->durasi, 0, ',', '.') }} hari</strong></div><div><small>Jumlah Penggunaan</small><strong>{{ number_format($jumlahPenggunaan, 0, ',', '.') }} transaksi</strong></div><div><small>Dibuat</small><strong>{{ $paket->created_at?->format('d M Y') ?? '—' }}</strong></div></div>
            </article>

            <article class="cucian-panel paket-usage-card">
                <div class="paket-usage-heading"><div><h2>Transaksi yang Menggunakan Paket</h2><p>10 penggunaan terbaru.</p></div><span>{{ number_format($jumlahPenggunaan, 0, ',', '.') }}</span></div>
                <div class="cucian-table-scroll">
                    <table class="paket-usage-table"><thead><tr><th>No. Nota</th><th>Pelanggan</th><th>Berat/Qty</th><th>Subtotal</th><th>Tanggal</th></tr></thead>
                        <tbody>
                            @forelse ($paket->detailTransaksi as $detail)
                                <tr><td>{{ $detail->no_nota }}</td><td>{{ $detail->transaksi?->pelanggan?->nama_pelanggan ?? 'Pelanggan dihapus' }}</td><td>{{ number_format((float) $detail->berat_qty, 2, ',', '.') }} {{ $unitHarga === 'kg' ? 'kg' : ($unitHarga === 'pcs' ? 'pcs' : 'layanan') }}</td><td>Rp {{ number_format((float) $detail->subtotal, 0, ',', '.') }}</td><td>{{ $detail->created_at?->format('d M Y') ?? '—' }}</td></tr>
                            @empty
                                <tr><td colspan="5" class="cucian-empty">Paket ini belum digunakan dalam transaksi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        </div>
    </div>
</x-layouts::app>
