<?php

namespace App\Http\Controllers;

use App\Models\StatusLaundry;
use App\Models\Transaksi;
use App\Support\LaundryOptions;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UpdateStatusController extends Controller
{
    private const STEPS = [
        'antrean' => 'Antrean',
        'diproses' => 'Diproses',
        'dicuci' => 'Dicuci',
        'dikeringkan' => 'Dikeringkan',
        'disetrika' => 'Disetrika',
        'selesai' => 'Selesai',
        'diambil' => 'Diambil',
    ];

    private const COLORS = [
        'antrean' => '#65b6f7',
        'diproses' => '#f49535',
        'dicuci' => '#286ee8',
        'dikeringkan' => '#28aeca',
        'disetrika' => '#ff885c',
        'selesai' => '#7655ed',
        'diambil' => '#19a879',
    ];

    public function index(Request $request)
    {
        [$bulan, $tahun, $awalBulan, $akhirBulan, $namaBulan] = $this->periode($request);
        $periode = Transaksi::query()->whereBetween('tanggal_masuk', [
            $awalBulan->toDateString(), $akhirBulan->toDateString(),
        ]);

        $jumlahStatus = (clone $periode)
            ->selectRaw('LOWER(status_cucian) as status_key, COUNT(*) as jumlah')
            ->groupBy(DB::raw('LOWER(status_cucian)'))
            ->get()
            ->keyBy('status_key');
        $statusCards = collect(self::STEPS)->map(function ($label, $key) use ($jumlahStatus) {
            $aliases = match ($key) {
                'antrean' => ['antrean', 'antrian'],
                'diproses' => ['diproses', 'proses'],
                'dikeringkan' => ['dikeringkan', 'pengeringan'],
                default => [$key],
            };
            $jumlah = collect($aliases)->sum(fn ($alias) => (int) ($jumlahStatus->get($alias)->jumlah ?? 0));

            return (object) [
                'key' => $key,
                'label' => $label,
                'jumlah' => $jumlah,
                'warna' => self::COLORS[$key],
            ];
        });

        $query = (clone $periode)->with([
            'pelanggan', 'detailTransaksi.paketLaundry', 'statusLaundry.admin',
        ]);
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

        $statusFilter = strtolower(trim((string) $request->query('status', '')));
        if ($statusFilter !== '') {
            $aliases = match ($statusFilter) {
                'antrean' => ['antrean', 'antrian'],
                'diproses' => ['diproses', 'proses'],
                'dikeringkan' => ['dikeringkan', 'pengeringan'],
                default => [$statusFilter],
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

        $cucian = $query->orderByDesc('tanggal_masuk')->orderByDesc('created_at')
            ->paginate(10)->withQueryString();
        $notaTerpilih = (string) $request->query('nota', '');
        $terpilih = $cucian->getCollection()->firstWhere('no_nota', $notaTerpilih)
            ?? $cucian->first();
        $riwayatTerpilih = collect();
        $tahapAktif = 0;
        $tahapan = collect(self::STEPS)->map(fn ($label, $key) => (object) [
            'key' => $key,
            'label' => $label,
            'warna' => self::COLORS[$key],
        ])->values();
        $tahapTanggal = [];

        if ($terpilih) {
            $terpilih->load(['pelanggan', 'detailTransaksi.paketLaundry', 'statusLaundry.admin']);
            $statusKey = $this->normalisasiStatus($terpilih->status_cucian);
            $tahapAktif = array_search($statusKey, array_keys(self::STEPS), true);
            $tahapAktif = $tahapAktif === false ? 0 : $tahapAktif;
            $riwayatTerpilih = $terpilih->statusLaundry->sortBy('waktu_status')->values();

            foreach (array_keys(self::STEPS) as $key) {
                $perubahan = $riwayatTerpilih->filter(fn ($item) => $this->normalisasiStatus($item->status) === $key)->last();
                $tahapTanggal[$key] = $perubahan
                    ? Carbon::parse($perubahan->waktu_status)->format('d M Y, H.i')
                    : '—';
            }
        }

        $riwayatTerbaru = $this->riwayatTerbaru();

        return view('update-status.index', compact(
            'bulan', 'tahun', 'namaBulan', 'statusCards', 'cucian',
            'search', 'statusFilter', 'paketId', 'terpilih', 'tahapAktif',
            'riwayatTerpilih', 'tahapan', 'tahapTanggal', 'riwayatTerbaru'
        ));
    }

    public function update(Request $request, Transaksi $transaksi)
    {
        $validated = $request->validate([
            'status_baru' => ['required', Rule::in(array_keys(self::STEPS))],
            'catatan_status' => ['nullable', 'string', 'max:500'],
        ], [
            'status_baru.required' => 'Pilih status baru untuk cucian ini.',
            'status_baru.in' => 'Status yang dipilih tidak dikenali.',
        ]);

        $statusBaru = $validated['status_baru'];
        $catatan = trim((string) ($validated['catatan_status'] ?? ''));
        $statusSebelumnya = $this->normalisasiStatus($transaksi->status_cucian);

        if ($statusSebelumnya === $statusBaru && $catatan === '') {
            $redirectQuery = $request->query();
            unset($redirectQuery['page']);

            return redirect()->route('update-status.index', array_merge(
                $redirectQuery, ['nota' => $transaksi->no_nota]
            ))->with('success', 'Status cucian tersebut sudah dipilih.');
        }

        DB::transaction(function () use ($transaksi, $statusBaru, $catatan) {
            $transaksi->update(['status_cucian' => $statusBaru]);
            StatusLaundry::create([
                'no_nota' => $transaksi->no_nota,
                'id_admin' => auth()->id(),
                'status' => $statusBaru,
                'catatan_status' => $catatan !== '' ? $catatan : 'Status diperbarui menjadi '.self::STEPS[$statusBaru].'.',
                'waktu_status' => now(),
            ]);
        });

        $redirectQuery = $request->query();
        unset($redirectQuery['status'], $redirectQuery['page']);

        return redirect()->route('update-status.index', array_merge(
            $redirectQuery, ['nota' => $transaksi->no_nota]
        ))->with('success', 'Status cucian berhasil diperbarui dan riwayatnya disimpan.');
    }

    public function riwayat()
    {
        $riwayat = StatusLaundry::query()
            ->with(['transaksi.pelanggan', 'admin'])
            ->orderByDesc('waktu_status')
            ->paginate(20);

        return view('update-status.history', compact('riwayat'));
    }

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

    private function normalisasiStatus(string $status): string
    {
        return match (strtolower(trim($status))) {
            'antrian' => 'antrean',
            'proses' => 'diproses',
            'pengeringan' => 'dikeringkan',
            default => strtolower(trim($status)),
        };
    }

    private function riwayatTerbaru()
    {
        $riwayat = DB::table('status_laundry')
            ->join('transaksi', 'status_laundry.no_nota', '=', 'transaksi.no_nota')
            ->join('pelanggan', 'transaksi.pelanggan_id', '=', 'pelanggan.id')
            ->leftJoin('users', 'status_laundry.id_admin', '=', 'users.id')
            ->select(
                'status_laundry.no_nota', 'status_laundry.status',
                'status_laundry.catatan_status', 'status_laundry.waktu_status',
                'pelanggan.nama_pelanggan', 'users.name as admin_name'
            )
            ->orderByDesc('status_laundry.waktu_status')
            ->limit(4)
            ->get();

        if ($riwayat->isNotEmpty()) {
            return $riwayat;
        }

        return Transaksi::query()->with('pelanggan')->orderByDesc('created_at')->limit(4)->get()
            ->map(fn ($item) => (object) [
                'no_nota' => $item->no_nota,
                'status' => $item->status_cucian,
                'catatan_status' => 'Nota dibuat bersama data cucian.',
                'waktu_status' => $item->created_at,
                'nama_pelanggan' => $item->pelanggan?->nama_pelanggan ?? 'Pelanggan dihapus',
                'admin_name' => 'Admin',
            ]);
    }
}
