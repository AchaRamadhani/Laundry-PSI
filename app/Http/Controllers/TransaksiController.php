<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Support\LaundryOptions;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    private const STATUS_LABELS = [
        'antrean' => 'Antrean', 'antrian' => 'Antrean',
        'proses' => 'Diproses', 'diproses' => 'Diproses',
        'dicuci' => 'Dicuci', 'dikeringkan' => 'Dikeringkan',
        'pengeringan' => 'Dikeringkan', 'disetrika' => 'Disetrika',
        'selesai' => 'Selesai', 'diambil' => 'Diambil',
    ];

    private const STATUS_COLORS = [
        'antrean' => '#62b5f7', 'antrian' => '#62b5f7',
        'proses' => '#ff9f1c', 'diproses' => '#ff9f1c',
        'dicuci' => '#3588ef', 'dikeringkan' => '#20b7bd',
        'pengeringan' => '#20b7bd', 'disetrika' => '#f5a11b',
        'selesai' => '#7552e8', 'diambil' => '#55b85f',
    ];

    public function index(Request $request): View
    {
        [$bulan, $tahun, $awalBulan, $akhirBulan, $namaBulan] = $this->periode($request);
        $periode = Transaksi::query()->whereBetween('tanggal_masuk', [
            $awalBulan->toDateString(), $akhirBulan->toDateString(),
        ]);

        $totalTransaksi = (clone $periode)->count();
        $transaksiHariIni = Transaksi::query()->whereDate('tanggal_masuk', today())->count();
        $belumSelesai = (clone $periode)
            ->whereNotIn(DB::raw('LOWER(status_cucian)'), ['selesai', 'diambil'])
            ->count();
        $selesai = (clone $periode)
            ->whereIn(DB::raw('LOWER(status_cucian)'), ['selesai', 'diambil'])
            ->count();
        $totalPendapatan = (float) (clone $periode)->sum('total_harga');
        $rataRata = $totalTransaksi ? $totalPendapatan / $totalTransaksi : 0;

        $statusRingkasan = (clone $periode)
            ->selectRaw('LOWER(status_cucian) as status_key, COUNT(*) as jumlah')
            ->groupBy(DB::raw('LOWER(status_cucian)'))
            ->orderBy('status_key')
            ->get()
            ->map(function (Transaksi $item) {
                $statusKey = (string) $item->getAttribute('status_key');

                $item->setAttribute(
                    'label',
                    self::STATUS_LABELS[$statusKey]
                        ?? ucfirst(str_replace('_', ' ', $statusKey))
                );

                $item->setAttribute(
                    'warna',
                    self::STATUS_COLORS[$statusKey] ?? '#9aa4b4'
                );

                return $item;
            });

        $totalStatus = (float) $statusRingkasan->sum('jumlah');
        $sudut = 0;

        foreach ($statusRingkasan as $item) {
            $jumlah = (float) $item->getAttribute('jumlah');

            $item->setAttribute('mulai', $sudut);

            $sudut += $totalStatus > 0
                ? ($jumlah / $totalStatus) * 360
                : 0;

            $item->setAttribute('akhir', $sudut);
        }

        $search = trim((string) $request->query('search', ''));
        $status = strtolower(trim((string) $request->query('status', '')));
        $paketId = $request->query('layanan');
        $transaksi = $this->queryTerfilter($request, $awalBulan, $akhirBulan)
            ->orderByDesc('tanggal_masuk')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();
        $aktivitas = Transaksi::query()
            ->with('pelanggan')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('transaksi.index', compact(
            'bulan', 'tahun', 'namaBulan', 'transaksi', 'search',
            'status', 'paketId', 'totalTransaksi', 'transaksiHariIni', 'belumSelesai',
            'selesai', 'totalPendapatan', 'rataRata', 'statusRingkasan', 'totalStatus', 'aktivitas'
        ));
    }

    public function cetak(Request $request): View
    {
        [$bulan, $tahun, $awalBulan, $akhirBulan, $namaBulan] = $this->periode($request);
        $transaksi = $this->queryTerfilter($request, $awalBulan, $akhirBulan)
            ->orderBy('tanggal_masuk')
            ->orderBy('no_nota')
            ->get();

        return view('transaksi.print', [
            'transaksi' => $transaksi,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'namaBulan' => $namaBulan,
            'totalPendapatan' => $transaksi->sum('total_harga'),
        ]);
    }

    public function aktivitas(): View
    {
        $transaksi = Transaksi::query()
            ->with('pelanggan')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('transaksi.aktivitas', compact('transaksi'));
    }

    /**
     * @return array{
     *     0: int,
     *     1: int,
     *     2: Carbon,
     *     3: Carbon,
     *     4: array<int, string>
     * }
     */
    private function periode(Request $request): array
    {
        $input = (string) $request->query('periode', '');
        if (preg_match('/^(\d{4})-(\d{2})$/', $input, $matches)) {
            $tahun = (int) $matches[1];
            $bulan = (int) $matches[2];
        } else {
            $bulan = (int) $request->query('bulan', now()->month);
            $tahun = (int) $request->query('tahun', now()->year);
        }

        $bulan = $bulan >= 1 && $bulan <= 12 ? $bulan : now()->month;
        $tahun = $tahun >= 2000 && $tahun <= 2100 ? $tahun : now()->year;
        $awalBulan = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $akhirBulan = $awalBulan->copy()->endOfMonth();
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return [$bulan, $tahun, $awalBulan, $akhirBulan, $namaBulan];
    }

    /**
     * @return Builder<Transaksi>
     */
    private function queryTerfilter(Request $request, Carbon $awalBulan, Carbon $akhirBulan): Builder
    {
        $query = Transaksi::query()
            ->with(['pelanggan', 'detailTransaksi.paketLaundry'])
            ->whereBetween('tanggal_masuk', [$awalBulan->toDateString(), $akhirBulan->toDateString()]);

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('no_nota', 'like', "%{$search}%")
                    ->orWhereHas('pelanggan', function (Builder $customerQuery) use ($search) {
                        $customerQuery->where('nama_pelanggan', 'like', "%{$search}%")
                            ->orWhere('no_telp', 'like', "%{$search}%");
                    });
            });
        }

        $status = strtolower(trim((string) $request->query('status', '')));
        if ($status !== '') {
            $aliases = match ($status) {
                'antrean' => ['antrean', 'antrian'],
                'diproses' => ['diproses', 'proses'],
                'dikeringkan' => ['dikeringkan', 'pengeringan'],
                default => [$status],
            };
            $query->whereIn(DB::raw('LOWER(status_cucian)'), $aliases);
        }

        $paketId = $request->query('layanan');
        if (is_numeric($paketId)) {
            $query->whereHas('detailTransaksi', function (Builder $detailQuery) use ($paketId) {
                $detailQuery->where('paket_laundry_id', $paketId);
            });
        } else {
            $serviceNames = LaundryOptions::serviceNamesForFilter(is_string($paketId) ? $paketId : null);
            if ($serviceNames !== []) {
                $query->whereHas('detailTransaksi.paketLaundry', function (Builder $packageQuery) use ($serviceNames) {
                    $packageQuery->whereIn(DB::raw('LOWER(nama_paket)'), $serviceNames);
                });
            }
        }

        return $query;
    }
}
