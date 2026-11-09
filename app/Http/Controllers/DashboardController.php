<?php

namespace App\Http\Controllers;

use App\Support\LaundryOptions;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);

        if ($bulan < 1 || $bulan > 12) {
            $bulan = now()->month;
        }

        if ($tahun < 2000 || $tahun > 2100) {
            $tahun = now()->year;
        }

        $namaBulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $awalBulan = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $akhirBulan = $awalBulan->copy()->endOfMonth();

        // Data transaksi pada periode terpilih
        $transaksiBulan = DB::table('transaksi')
            ->whereBetween('tanggal_masuk', [
                $awalBulan->toDateString(),
                $akhirBulan->toDateString(),
            ]);

        // KPI
        $totalPelanggan = DB::table('pelanggan')->count();

        $totalTransaksi = (clone $transaksiBulan)->count();

        $totalPendapatan = (clone $transaksiBulan)
            ->sum('total_harga');
        $rataRataHarian = $totalPendapatan / max(1, $awalBulan->daysInMonth);

        // Status selesai dan diambil tidak dihitung sebagai pesanan berjalan
        $statusSelesai = ['selesai', 'diambil'];

        $totalPesanan = (clone $transaksiBulan)
            ->whereNotIn(DB::raw('LOWER(status_cucian)'), $statusSelesai)
            ->count();

        // Ringkasan status cucian
        $warnaStatus = [
            'antrean' => '#62b5f7',
            'antrian' => '#62b5f7',
            'proses' => '#ff9f1c',
            'diproses' => '#ff9f1c',
            'dicuci' => '#3588ef',
            'dikeringkan' => '#20b7bd',
            'pengeringan' => '#20b7bd',
            'disetrika' => '#f5a11b',
            'selesai' => '#7552e8',
            'diambil' => '#55b85f',
        ];

        $labelStatus = static fn (string $status): string => match (strtolower(trim($status))) {
            'antrean', 'antrian' => 'Antrean',
            'proses', 'diproses' => 'Diproses',
            'dicuci' => 'Dicuci',
            'dikeringkan', 'pengeringan' => 'Dikeringkan',
            'disetrika' => 'Disetrika',
            'selesai' => 'Selesai',
            'diambil' => 'Diambil',
            default => ucfirst(str_replace('_', ' ', $status)),
        };

        $statusFilter = strtolower(trim((string) $request->query('status', '')));
        if (! array_key_exists($statusFilter, LaundryOptions::STATUSES)) {
            $statusFilter = '';
        }

        $transaksiStatus = clone $transaksiBulan;
        if ($statusFilter !== '') {
            $statusAliases = match ($statusFilter) {
                'antrean' => ['antrean', 'antrian'],
                'diproses' => ['diproses', 'proses'],
                'dikeringkan' => ['dikeringkan', 'pengeringan'],
                default => [$statusFilter],
            };
            $transaksiStatus->whereIn(DB::raw('LOWER(status_cucian)'), $statusAliases);
        }

        $dataStatus = $transaksiStatus
            ->select(
                'status_cucian',
                DB::raw('COUNT(*) as jumlah')
            )
            ->groupBy('status_cucian')
            ->orderBy('status_cucian')
            ->get()
            ->map(function ($status) use ($labelStatus, $warnaStatus) {
                $key = strtolower(trim($status->status_cucian));
                $status->label = $labelStatus($status->status_cucian);
                $status->warna = $warnaStatus[$key] ?? '#96a3b8';

                return $status;
            });

        $totalStatus = $dataStatus->sum('jumlah');

        // Grafik pendapatan harian, termasuk tanggal dengan pendapatan nol
        $pendapatanDatabase = (clone $transaksiBulan)
            ->select(
                DB::raw('DAY(tanggal_masuk) as hari'),
                DB::raw('SUM(total_harga) as pendapatan')
            )
            ->groupBy(DB::raw('DAY(tanggal_masuk)'))
            ->orderBy('hari')
            ->get()
            ->keyBy('hari');

        $pendapatanHarian = collect();

        for ($hari = 1; $hari <= $awalBulan->daysInMonth; $hari++) {
            $pendapatanHarian->push((object) [
                'hari' => $hari,
                'pendapatan' => (float) (
                    $pendapatanDatabase->get($hari)->pendapatan ?? 0
                ),
            ]);
        }

        // Transaksi terbaru
        $transaksiTerbaru = (clone $transaksiBulan)
            ->leftJoin(
                'pelanggan',
                'transaksi.pelanggan_id',
                '=',
                'pelanggan.id'
            )
            ->select(
                'transaksi.no_nota',
                'transaksi.tanggal_masuk',
                'transaksi.total_harga',
                'transaksi.status_cucian',
                'pelanggan.nama_pelanggan'
            )
            ->orderByDesc('transaksi.tanggal_masuk')
            ->orderByDesc('transaksi.created_at')
            ->limit(7)
            ->get();

        $layananPerNota = DB::table('detail_transaksi')
            ->join(
                'paket_laundry',
                'detail_transaksi.paket_laundry_id',
                '=',
                'paket_laundry.id'
            )
            ->whereIn('detail_transaksi.no_nota', $transaksiTerbaru->pluck('no_nota'))
            ->select('detail_transaksi.no_nota', 'paket_laundry.nama_paket')
            ->get()
            ->groupBy('no_nota')
            ->map(fn ($detail) => $detail->pluck('nama_paket')->unique()->implode(', '));

        $transaksiTerbaru->each(function ($transaksi) use ($labelStatus, $layananPerNota, $warnaStatus) {
            $key = strtolower(trim($transaksi->status_cucian));
            $transaksi->status_label = $labelStatus($transaksi->status_cucian);
            $transaksi->status_warna = $warnaStatus[$key] ?? '#96a3b8';
            $transaksi->nama_layanan = $layananPerNota->get($transaksi->no_nota) ?: '—';
        });

        // Jumlah nota unik untuk setiap paket laundry
        $dataLayanan = DB::table('detail_transaksi')
            ->join(
                'transaksi',
                'detail_transaksi.no_nota',
                '=',
                'transaksi.no_nota'
            )
            ->join(
                'paket_laundry',
                'detail_transaksi.paket_laundry_id',
                '=',
                'paket_laundry.id'
            )
            ->whereBetween('transaksi.tanggal_masuk', [
                $awalBulan->toDateString(),
                $akhirBulan->toDateString(),
            ])
            ->select(
                'paket_laundry.nama_paket',
                DB::raw('COUNT(DISTINCT detail_transaksi.no_nota) as jumlah')
            )
            ->groupBy('paket_laundry.id', 'paket_laundry.nama_paket')
            ->orderByDesc('jumlah')
            ->get();

        return view('dashboard', compact(
            'bulan',
            'tahun',
            'namaBulan',
            'totalPelanggan',
            'totalTransaksi',
            'totalPendapatan',
            'rataRataHarian',
            'totalPesanan',
            'dataStatus',
            'totalStatus',
            'statusFilter',
            'pendapatanHarian',
            'transaksiTerbaru',
            'dataLayanan'
        ));
    }
}
