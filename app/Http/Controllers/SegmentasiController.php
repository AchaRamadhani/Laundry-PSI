<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SegmentasiController extends Controller
{
    private const LOYAL_MIN_TRANSACTIONS = 6;

    private const LOYAL_RECENT_DAYS = 30;

    private const ACTIVE_WINDOW_DAYS = 60;

    private const FREQUENCY_WINDOW_DAYS = 90;

    public function index(Request $request)
    {
        $hariIni = Carbon::today();
        $batas90Hari = $hariIni->copy()->subDays(self::FREQUENCY_WINDOW_DAYS)->toDateString();
        $batas60Hari = $hariIni->copy()->subDays(self::ACTIVE_WINDOW_DAYS)->toDateString();
        $batas30Hari = $hariIni->copy()->subDays(self::LOYAL_RECENT_DAYS)->toDateString();
        $akhirHariIni = $hariIni->toDateString();

        $jumlahPelanggan = Pelanggan::query()->count();
        $pelangganLoyal = $this->pelangganLoyalQuery($batas90Hari, $batas30Hari, $akhirHariIni)->count();
        $pelangganAktif60Hari = Pelanggan::query()->whereHas('transaksi', function (Builder $query) use ($batas60Hari, $akhirHariIni) {
            $query->whereBetween('tanggal_masuk', [$batas60Hari, $akhirHariIni]);
        })->count();
        $pelangganPotensial = max(0, $pelangganAktif60Hari - $pelangganLoyal);
        $pelangganKurangAktif = max(0, $jumlahPelanggan - $pelangganAktif60Hari);

        $query = Pelanggan::query()
            ->withCount('transaksi as jumlah_transaksi')
            ->withCount(['transaksi as transaksi_90_hari' => function (Builder $transactionQuery) use ($batas90Hari, $akhirHariIni) {
                $transactionQuery->whereBetween('tanggal_masuk', [$batas90Hari, $akhirHariIni]);
            }])
            ->withMax('transaksi as transaksi_terakhir', 'tanggal_masuk');

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('nama_pelanggan', 'like', "%{$search}%")
                    ->orWhere('no_telp', 'like', "%{$search}%");
            });
        }

        $segmen = strtolower(trim((string) $request->query('segmen', '')));
        if (! in_array($segmen, ['', 'loyal', 'potensial', 'kurang-aktif'], true)) {
            $segmen = '';
        }
        if ($segmen === 'loyal') {
            $query->whereHas('transaksi', function (Builder $transactionQuery) use ($batas90Hari, $akhirHariIni) {
                $transactionQuery->whereBetween('tanggal_masuk', [$batas90Hari, $akhirHariIni]);
            }, '>=', self::LOYAL_MIN_TRANSACTIONS)
                ->whereHas('transaksi', function (Builder $transactionQuery) use ($batas30Hari, $akhirHariIni) {
                    $transactionQuery->whereBetween('tanggal_masuk', [$batas30Hari, $akhirHariIni]);
                });
        } elseif ($segmen === 'potensial') {
            $query->whereHas('transaksi', function (Builder $transactionQuery) use ($batas60Hari, $akhirHariIni) {
                $transactionQuery->whereBetween('tanggal_masuk', [$batas60Hari, $akhirHariIni]);
            })->where(function (Builder $builder) use ($batas90Hari, $batas30Hari, $akhirHariIni) {
                $builder->whereHas('transaksi', function (Builder $transactionQuery) use ($batas90Hari, $akhirHariIni) {
                    $transactionQuery->whereBetween('tanggal_masuk', [$batas90Hari, $akhirHariIni]);
                }, '<', self::LOYAL_MIN_TRANSACTIONS)
                    ->orWhereDoesntHave('transaksi', function (Builder $transactionQuery) use ($batas30Hari, $akhirHariIni) {
                        $transactionQuery->whereBetween('tanggal_masuk', [$batas30Hari, $akhirHariIni]);
                    });
            });
        } elseif ($segmen === 'kurang-aktif') {
            $query->whereDoesntHave('transaksi', function (Builder $transactionQuery) use ($batas60Hari, $akhirHariIni) {
                $transactionQuery->whereBetween('tanggal_masuk', [$batas60Hari, $akhirHariIni]);
            });
        }

        $pelanggan = $query->orderByDesc('transaksi_terakhir')
            ->orderBy('nama_pelanggan')
            ->paginate(10)
            ->withQueryString();

        $pelanggan->getCollection()->transform(function (Pelanggan $item) use ($batas30Hari, $batas60Hari) {
            $terakhir = $item->transaksi_terakhir
                ? Carbon::parse($item->transaksi_terakhir)->toDateString()
                : null;

            if ($item->transaksi_90_hari >= self::LOYAL_MIN_TRANSACTIONS && $terakhir && $terakhir >= $batas30Hari) {
                $item->segment_key = 'loyal';
                $item->segment_label = 'Loyal';
            } elseif ($terakhir && $terakhir >= $batas60Hari) {
                $item->segment_key = 'potensial';
                $item->segment_label = 'Potensial';
            } else {
                $item->segment_key = 'kurang-aktif';
                $item->segment_label = 'Kurang Aktif';
            }

            $item->lama_menjadi_pelanggan = $this->lamaMenjadiPelanggan($item->created_at);

            return $item;
        });

        return view('segmentasi.index', compact(
            'pelanggan', 'jumlahPelanggan', 'pelangganLoyal', 'pelangganPotensial',
            'pelangganKurangAktif', 'search', 'segmen'
        ));
    }

    private function pelangganLoyalQuery(string $batas90Hari, string $batas30Hari, string $akhirHariIni): Builder
    {
        return Pelanggan::query()
            ->whereHas('transaksi', function (Builder $query) use ($batas90Hari, $akhirHariIni) {
                $query->whereBetween('tanggal_masuk', [$batas90Hari, $akhirHariIni]);
            }, '>=', self::LOYAL_MIN_TRANSACTIONS)
            ->whereHas('transaksi', function (Builder $query) use ($batas30Hari, $akhirHariIni) {
                $query->whereBetween('tanggal_masuk', [$batas30Hari, $akhirHariIni]);
            });
    }

    private function lamaMenjadiPelanggan(?Carbon $tanggalDaftar): string
    {
        if (! $tanggalDaftar) {
            return '—';
        }

        $selisih = $tanggalDaftar->diff(Carbon::now());
        if ($selisih->y > 0) {
            return $selisih->y.' tahun '.$selisih->m.' bulan';
        }
        if ($selisih->m > 0) {
            return $selisih->m.' bulan';
        }

        return 'Kurang dari 1 bulan';
    }
}
