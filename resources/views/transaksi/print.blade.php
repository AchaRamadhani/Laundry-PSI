<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rekap Transaksi {{ $namaBulan[$bulan] }} {{ $tahun }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 28px; color: #202638; font-family: Arial, Helvetica, sans-serif; font-size: 12px; }
        .print-toolbar { display: flex; justify-content: flex-end; gap: 8px; margin: 0 auto 18px; max-width: 1100px; }
        .print-toolbar button, .print-toolbar a { padding: 9px 14px; color: #fff; border: 0; border-radius: 5px; background: #0846e9; cursor: pointer; font: inherit; text-decoration: none; }
        .print-toolbar a { color: #35405a; border: 1px solid #d9dde6; background: #fff; }
        .print-sheet { max-width: 1100px; margin: 0 auto; }
        .print-brand { display: flex; align-items: center; gap: 16px; padding-bottom: 16px; border-bottom: 2px solid #0846e9; }
        .print-brand img { width: 125px; height: 85px; object-fit: contain; }
        .print-brand h1 { margin: 0; color: #252e70; font-size: 22px; }
        .print-brand p { margin: 5px 0 0; color: #687187; }
        .print-meta { display: flex; justify-content: space-between; gap: 18px; margin: 18px 0; }
        .print-total { padding: 12px 16px; border: 1px solid #e4e8f0; border-radius: 6px; background: #f7f9fd; }
        .print-total strong { display: block; margin-top: 4px; color: #0846e9; font-size: 17px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px 7px; border: 1px solid #dfe3ea; text-align: left; vertical-align: top; }
        th { color: #283266; background: #f2f5fb; font-size: 10px; }
        td { font-size: 10px; }
        tfoot th { text-align: right; }
        .print-footer { margin-top: 22px; color: #70788a; font-size: 9px; text-align: center; }
        @media print { body { padding: 0; } .print-toolbar { display: none; } .print-sheet { max-width: none; } tr { break-inside: avoid; } }
    </style>
</head>
<body>
    <div class="print-toolbar"><a href="{{ route('transaksi.index') }}">Kembali</a><button type="button" onclick="window.print()">Cetak</button></div>
    <main class="print-sheet">
        <header class="print-brand"><img src="{{ asset('images/logo-g-laundry.png') }}" alt="Logo G'Laundry"><div><h1>Rekap Transaksi</h1><p>Periode {{ $namaBulan[$bulan] }} {{ $tahun }} · G'Laundry</p></div></header>
        <div class="print-meta"><div><strong>Dicetak:</strong> {{ now()->format('d M Y, H.i') }}</div><div class="print-total">Jumlah transaksi<strong>{{ number_format($transaksi->count(), 0, ',', '.') }}</strong></div><div class="print-total">Total pendapatan<strong>Rp {{ number_format((float) $totalPendapatan, 0, ',', '.') }}</strong></div></div>
        <table>
            <thead><tr><th>No.</th><th>No. Nota</th><th>Pelanggan</th><th>Layanan</th><th>Berat/Qty</th><th>Tanggal Masuk</th><th>Estimasi Selesai</th><th>Status</th><th>Total Harga</th></tr></thead>
            <tbody>
                @forelse ($transaksi as $index => $item)
                    @php
                        $statusKey = strtolower($item->status_cucian);
                        $statusText = match ($statusKey) { 'antrean', 'antrian' => 'Antrean', 'proses', 'diproses' => 'Diproses', 'dikeringkan', 'pengeringan' => 'Dikeringkan', default => ucfirst($statusKey) };
                        $layanan = $item->detailTransaksi->map(fn ($detail) => $detail->paketLaundry?->nama_paket)->filter()->unique()->implode(', ');
                        $jumlah = $item->detailTransaksi->sum('berat_qty');
                    @endphp
                    <tr><td>{{ $index + 1 }}</td><td>{{ $item->no_nota }}</td><td>{{ $item->pelanggan?->nama_pelanggan ?? 'Pelanggan dihapus' }}<br>{{ $item->pelanggan?->no_telp }}</td><td>{{ $layanan ?: '—' }}</td><td>{{ number_format((float) $jumlah, 2, ',', '.') }}</td><td>{{ \Carbon\Carbon::parse($item->tanggal_masuk)->format('d M Y') }}</td><td>{{ $item->estimasi_selesai ? \Carbon\Carbon::parse($item->estimasi_selesai)->format('d M Y H.i') : '—' }}</td><td>{{ $statusText }}</td><td>Rp {{ number_format((float) $item->total_harga, 0, ',', '.') }}</td></tr>
                @empty
                    <tr><td colspan="9" style="text-align:center">Tidak ada transaksi pada periode ini.</td></tr>
                @endforelse
            </tbody>
            <tfoot><tr><th colspan="8">Total Pendapatan</th><th>Rp {{ number_format((float) $totalPendapatan, 0, ',', '.') }}</th></tr></tfoot>
        </table>
        <footer class="print-footer">Dokumen rekap transaksi G'Laundry · Nota pelanggan tetap tersedia di sistem.</footer>
    </main>
    <script>window.addEventListener('load', () => window.print());</script>
</body>
</html>
