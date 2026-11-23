<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 24px 28px; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #202638; font-family: DejaVu Sans, sans-serif; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; }
        .brand { border-bottom: 2px solid #0846e9; }
        .brand td { padding: 0 0 12px; vertical-align: middle; }
        .brand img { width: 100px; height: 65px; }
        .brand h1 { margin: 0; color: #252e70; font-size: 19px; }
        .brand p { margin: 5px 0 0; color: #687187; font-size: 10px; }
        .summary { margin: 14px 0; }
        .summary td { padding: 7px 8px; vertical-align: top; }
        .summary .printed { width: 52%; }
        .summary .metric { width: 24%; border: 1px solid #e4e8f0; background: #f7f9fd; }
        .metric strong { display: block; margin-top: 4px; color: #0846e9; font-size: 14px; }
        .data { table-layout: fixed; }
        .data th, .data td { padding: 6px 4px; border: 1px solid #dfe3ea; text-align: left; vertical-align: top; word-wrap: break-word; }
        .data th { color: #283266; background: #f2f5fb; font-size: 8px; }
        .data td { font-size: 8px; }
        .data thead { display: table-header-group; }
        .data tr { page-break-inside: avoid; }
        .data tfoot th { text-align: right; }
        .footer { margin-top: 16px; color: #70788a; font-size: 8px; text-align: center; }
    </style>
</head>
<body>
    <table class="brand">
        <tr>
            <td style="width:110px">@if ($logoData)<img src="{{ $logoData }}" alt="Logo G'Laundry">@endif</td>
            <td><h1>Rekap Transaksi</h1><p>Periode {{ $namaBulan[$bulan] }} {{ $tahun }} · G'Laundry</p></td>
        </tr>
    </table>
    <table class="summary">
        <tr>
            <td class="printed"><strong>Dicetak:</strong> {{ \App\Support\LaundryDateFormatter::format(now(), true) }}</td>
            <td class="metric">Jumlah transaksi<strong>{{ number_format($transaksi->count(), 0, ',', '.') }}</strong></td>
            <td class="metric">Total pendapatan<strong>Rp {{ number_format((float) $totalPendapatan, 0, ',', '.') }}</strong></td>
        </tr>
    </table>
    <table class="data">
        <colgroup>
            <col style="width:4%"><col style="width:12%"><col style="width:14%">
            <col style="width:14%"><col style="width:8%"><col style="width:10%">
            <col style="width:12%"><col style="width:8%"><col style="width:18%">
        </colgroup>
        <thead>
            <tr><th>No.</th><th>No. Nota</th><th>Pelanggan</th><th>Layanan</th><th>Berat/Qty</th><th>Tanggal Masuk</th><th>Estimasi Selesai</th><th>Status</th><th>Total Harga</th></tr>
        </thead>
        <tbody>
            @forelse ($transaksi as $index => $item)
                @php
                    $statusKey = strtolower($item->status_cucian);
                    $statusText = match ($statusKey) { 'antrean', 'antrian' => 'Antrean', 'proses', 'diproses' => 'Diproses', 'dikeringkan', 'pengeringan' => 'Dikeringkan', default => ucfirst($statusKey) };
                    $layanan = $item->detailTransaksi->map(fn ($detail) => $detail->paketLaundry?->nama_paket)->filter()->unique()->implode(', ');
                    $jumlah = $item->detailTransaksi->sum('berat_qty');
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td><td>{{ $item->no_nota }}</td>
                    <td>{{ $item->pelanggan?->nama_pelanggan ?? 'Pelanggan dihapus' }}<br>{{ $item->pelanggan?->no_telp }}</td>
                    <td>{{ $layanan ?: '—' }}</td>
                    <td>{{ number_format((float) $jumlah, 2, ',', '.') }}</td>
                    <td>{{ \App\Support\LaundryDateFormatter::format($item->tanggal_masuk) }}</td>
                    <td>{{ \App\Support\LaundryDateFormatter::format($item->estimasi_selesai, true) }}</td>
                    <td>{{ $statusText }}</td>
                    <td>Rp {{ number_format((float) $item->total_harga, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="9" style="text-align:center">Tidak ada transaksi pada periode ini.</td></tr>
            @endforelse
        </tbody>
        <tfoot><tr><th colspan="8">Total Pendapatan</th><th>Rp {{ number_format((float) $totalPendapatan, 0, ',', '.') }}</th></tr></tfoot>
    </table>
    <div class="footer">Dokumen rekap transaksi G'Laundry · Nota pelanggan tetap tersedia di sistem.</div>
</body>
</html>
