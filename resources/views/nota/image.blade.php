@php
    $detailCount = max(1, $transaksi->detailTransaksi->count());
    $rowsStart = 530;
    $rowHeight = 54;
    $itemsBottom = $rowsStart + ($detailCount * $rowHeight);
    $catatanTop = $itemsBottom + 12;
    $catatanBoxHeight = 48 + (18 * count($catatanLines));
    $catatanExtraSpace = $catatanLines !== [] ? 40 + (18 * count($catatanLines)) : 0;
    $totalTop = $itemsBottom + 36 + $catatanExtraSpace;
    $trackingTop = $totalTop + 130;
    $thankTop = $trackingTop + 190;
    $canvasHeight = $thankTop + 70;
    $statusColor = match (strtolower($statusLabel)) {
        'antrean' => '#1683e8', 'diproses' => '#d97706', 'dicuci' => '#2563eb',
        'dikeringkan' => '#0e8b91', 'disetrika' => '#c56810', 'selesai' => '#16834f',
        'diambil' => '#16834f', default => '#667085',
    };
    $tanggalMasuk = \App\Support\LaundryDateFormatter::format($transaksi->tanggal_masuk, true);
    $estimasiSelesai = $transaksi->estimasi_selesai
        ? \App\Support\LaundryDateFormatter::format($transaksi->estimasi_selesai, true)
        : 'Belum ditentukan';
@endphp
<svg xmlns="http://www.w3.org/2000/svg" width="760" height="{{ $canvasHeight }}" viewBox="0 0 760 {{ $canvasHeight }}" role="img" aria-labelledby="title desc">
    <title id="title">Nota digital {{ $transaksi->no_nota }} | {{ $laundryName }}</title>
    <desc id="desc">Nota transaksi laundry atas nama {{ $transaksi->pelanggan?->nama_pelanggan ?? 'Pelanggan' }}, dengan token tracking {{ $trackingToken }}.</desc>
    <style>text { font-family: 'Inter', 'Segoe UI', system-ui, sans-serif !important; font-variant-numeric: lining-nums tabular-nums; }</style>
    <rect width="760" height="{{ $canvasHeight }}" fill="#f2f6fc"/>
    <rect x="20" y="20" width="720" height="{{ $canvasHeight - 40 }}" rx="24" fill="#ffffff" stroke="#d8e0ec" stroke-width="2"/>

    @if ($logoDataUri)
        <image href="{{ $logoDataUri }}" x="54" y="44" width="156" height="92" preserveAspectRatio="xMidYMid meet"/>
    @endif
    <text x="228" y="78" fill="#0754b8" font-family="Arial, sans-serif" font-size="28" font-weight="700">{{ $laundryName }}</text>
    <text x="228" y="106" fill="#56657c" font-family="Arial, sans-serif" font-size="15">Nota transaksi laundry</text>
    <text x="228" y="133" fill="#56657c" font-family="Arial, sans-serif" font-size="13">{{ $address ?: 'Laundry bersih, rapi, dan wangi' }}</text>
    <line x1="50" y1="160" x2="710" y2="160" stroke="#d9e2ef" stroke-width="2"/>

    <text x="50" y="196" fill="#172033" font-family="Arial, sans-serif" font-size="21" font-weight="700">NOTA TRANSAKSI LAUNDRY</text>
    <rect x="590" y="174" width="120" height="34" rx="17" fill="{{ $statusColor }}"/>
    <text x="650" y="197" fill="#ffffff" font-family="Arial, sans-serif" font-size="14" font-weight="700" text-anchor="middle">{{ $statusLabel }}</text>

    <rect x="50" y="220" width="660" height="116" rx="14" fill="#f8fbff" stroke="#e0e8f3"/>
    <text x="70" y="250" fill="#627087" font-family="Arial, sans-serif" font-size="13">Nomor nota</text>
    <text x="235" y="250" fill="#172033" font-family="Arial, sans-serif" font-size="14" font-weight="700">{{ $transaksi->no_nota }}</text>
    <text x="70" y="278" fill="#627087" font-family="Arial, sans-serif" font-size="13">Tanggal masuk</text>
    <text x="235" y="278" fill="#172033" font-family="Arial, sans-serif" font-size="14">{{ $tanggalMasuk }}</text>
    <text x="70" y="306" fill="#627087" font-family="Arial, sans-serif" font-size="13">Estimasi selesai</text>
    <text x="235" y="306" fill="#172033" font-family="Arial, sans-serif" font-size="14">{{ $estimasiSelesai }}</text>
    <text x="70" y="326" fill="#627087" font-family="Arial, sans-serif" font-size="11">Admin: {{ $transaksi->admin?->name ?? "Admin G'Laundry" }}</text>

    <rect x="50" y="354" width="660" height="84" rx="14" fill="#eaf4ff"/>
    <text x="70" y="380" fill="#0754b8" font-family="Arial, sans-serif" font-size="14" font-weight="700">INFORMASI PELANGGAN</text>
    <text x="70" y="411" fill="#627087" font-family="Arial, sans-serif" font-size="13">Nama pelanggan</text>
    <text x="235" y="411" fill="#172033" font-family="Arial, sans-serif" font-size="14" font-weight="700">{{ $transaksi->pelanggan?->nama_pelanggan ?? 'Pelanggan' }}</text>
    <text x="470" y="411" fill="#627087" font-family="Arial, sans-serif" font-size="13">No. telepon</text>
    <text x="558" y="411" fill="#172033" font-family="Arial, sans-serif" font-size="14">{{ $transaksi->pelanggan?->no_telp ?? '—' }}</text>

    <text x="50" y="477" fill="#0754b8" font-family="Arial, sans-serif" font-size="15" font-weight="700">RINCIAN LAYANAN</text>
    <rect x="50" y="490" width="660" height="34" rx="8" fill="#eaf4ff"/>
    <text x="66" y="512" fill="#34425a" font-family="Arial, sans-serif" font-size="12" font-weight="700">Layanan</text>
    <text x="440" y="512" fill="#34425a" font-family="Arial, sans-serif" font-size="12" font-weight="700">Berat / Qty</text>
    <text x="690" y="512" fill="#34425a" font-family="Arial, sans-serif" font-size="12" font-weight="700" text-anchor="end">Subtotal</text>
    @foreach ($transaksi->detailTransaksi as $index => $detail)
        @php
            $rowY = $rowsStart + ($index * $rowHeight);
            $unit = strtolower($detail->paketLaundry?->kategori ?? '') === 'satuan' ? 'pcs' : 'kg';
            $serviceName = $detail->paketLaundry?->nama_paket ?? 'Layanan laundry';
        @endphp
        <rect x="50" y="{{ $rowY }}" width="660" height="{{ $rowHeight }}" rx="8" fill="{{ $index % 2 === 0 ? '#ffffff' : '#f8fbff' }}"/>
        <text x="66" y="{{ $rowY + 23 }}" fill="#172033" font-family="Arial, sans-serif" font-size="14" font-weight="700">{{ \Illuminate\Support\Str::limit($serviceName, 37) }}</text>
        <text x="440" y="{{ $rowY + 23 }}" fill="#34425a" font-family="Arial, sans-serif" font-size="13">{{ number_format((float) $detail->berat_qty, 2, ',', '.') }} {{ $unit }}</text>
        <text x="690" y="{{ $rowY + 23 }}" fill="#172033" font-family="Arial, sans-serif" font-size="14" font-weight="700" text-anchor="end">Rp {{ number_format((float) $detail->subtotal, 0, ',', '.') }}</text>
        <line x1="66" y1="{{ $rowY + 39 }}" x2="694" y2="{{ $rowY + 39 }}" stroke="#e6ebf2"/>
    @endforeach

    @if ($catatanLines !== [])
        <rect x="50" y="{{ $catatanTop }}" width="660" height="{{ $catatanBoxHeight }}" rx="10" fill="#f3f7fd" stroke="#e0e8f3"/>
        <text x="68" y="{{ $catatanTop + 21 }}" fill="#0754b8" font-family="Arial, sans-serif" font-size="13" font-weight="700">CATATAN PESANAN</text>
        <text x="68" y="{{ $catatanTop + 42 }}" fill="#34425a" font-family="Arial, sans-serif" font-size="13">
            @foreach ($catatanLines as $lineIndex => $line)
                <tspan x="68" dy="{{ $lineIndex === 0 ? 0 : 18 }}">{{ $line }}</tspan>
            @endforeach
        </text>
    @endif

    <rect x="50" y="{{ $totalTop }}" width="660" height="104" rx="12" fill="#d9edff"/>
    <text x="70" y="{{ $totalTop + 38 }}" fill="#0754b8" font-family="Arial, sans-serif" font-size="16" font-weight="700">TOTAL PEMBAYARAN</text>
    <text x="690" y="{{ $totalTop + 40 }}" fill="#0754b8" font-family="Arial, sans-serif" font-size="23" font-weight="700" text-anchor="end">Rp {{ number_format((float) $transaksi->total_harga, 0, ',', '.') }}</text>
    <text x="70" y="{{ $totalTop + 69 }}" fill="#34425a" font-family="Arial, sans-serif" font-size="12">Status pembayaran</text>
    <text x="235" y="{{ $totalTop + 69 }}" fill="#172033" font-family="Arial, sans-serif" font-size="12" font-weight="700">{{ $transaksi->status_pembayaran_label }}</text>
    <text x="70" y="{{ $totalTop + 91 }}" fill="#34425a" font-family="Arial, sans-serif" font-size="12">Metode pembayaran</text>
    <text x="235" y="{{ $totalTop + 91 }}" fill="#172033" font-family="Arial, sans-serif" font-size="12" font-weight="700">{{ $transaksi->metode_pembayaran_label }}</text>

    <rect x="50" y="{{ $trackingTop }}" width="660" height="164" rx="14" fill="#f3f7fd" stroke="#dce7f5"/>
    <image href="{{ $qrSvgDataUri }}" x="68" y="{{ $trackingTop + 12 }}" width="140" height="140"/>
    <text x="232" y="{{ $trackingTop + 36 }}" fill="#0754b8" font-family="Arial, sans-serif" font-size="16" font-weight="700">TOKEN TRACKING</text>
    @foreach ($trackingTokenLines as $lineIndex => $trackingTokenLine)
        <text x="232" y="{{ $trackingTop + 61 + ($lineIndex * 17) }}" fill="#172033" font-family="Arial, sans-serif" font-size="12" font-weight="700" letter-spacing="1">{{ $trackingTokenLine }}</text>
    @endforeach
    <text x="232" y="{{ $trackingTop + 104 }}" fill="#58677e" font-family="Arial, sans-serif" font-size="12">Masukkan token ini pada halaman Tracking Cucian.</text>
    <text x="232" y="{{ $trackingTop + 124 }}" fill="#58677e" font-family="Arial, sans-serif" font-size="11">Jaga kerahasiaan token Anda agar data pribadi tetap terjaga.</text>
    <text x="232" y="{{ $trackingTop + 143 }}" fill="#0754b8" font-family="Arial, sans-serif" font-size="10">Lacak: {{ $trackingUrl }}</text>
    <text x="138" y="{{ $trackingTop + 158 }}" fill="#58677e" font-family="Arial, sans-serif" font-size="10" text-anchor="middle">QR menuju website G'Laundry</text>

    <text x="380" y="{{ $thankTop }}" fill="#0754b8" font-family="Arial, sans-serif" font-size="14" font-style="italic" font-weight="700" text-anchor="middle">Terima kasih telah mempercayakan cucian Anda kepada {{ $laundryName }}.</text>
</svg>
