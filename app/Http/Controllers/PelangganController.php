<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\Transaksi;
use App\Support\LaundryOptions;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PelangganController extends Controller
{
    public function index(Request $request): View
    {
        [$bulan, $tahun, $awalBulan, $akhirBulan, $namaBulan] = $this->periode($request);
        $paketId = $request->query('layanan');
        $serviceNames = LaundryOptions::serviceNamesForFilter(is_string($paketId) ? $paketId : null);

        $filterTransaksi = function (Builder $query) use ($awalBulan, $akhirBulan, $paketId, $serviceNames) {
            $query->whereBetween('tanggal_masuk', [$awalBulan->toDateString(), $akhirBulan->toDateString()]);
            if (is_numeric($paketId)) {
                $query->whereHas('detailTransaksi', function (Builder $detailQuery) use ($paketId) {
                    $detailQuery->where('paket_laundry_id', $paketId);
                });
            } elseif ($serviceNames !== []) {
                $query->whereHas('detailTransaksi.paketLaundry', function (Builder $packageQuery) use ($serviceNames) {
                    $packageQuery->whereIn(DB::raw('LOWER(nama_paket)'), $serviceNames);
                });
            }
        };

        $totalPelanggan = Pelanggan::query()->count();
        $pelangganBulanIni = Transaksi::query()
            ->whereBetween('tanggal_masuk', [$awalBulan->toDateString(), $akhirBulan->toDateString()])
            ->distinct()
            ->count('pelanggan_id');
        $pelangganBaru = Pelanggan::query()->whereBetween('created_at', [
            $awalBulan->copy()->startOfDay(), $akhirBulan->copy()->endOfDay(),
        ])->count();
        $cucianBerjalan = Transaksi::query()
            ->whereBetween('tanggal_masuk', [$awalBulan->toDateString(), $akhirBulan->toDateString()])
            ->whereNotIn(DB::raw('LOWER(status_cucian)'), ['selesai', 'diambil'])
            ->count();
        $belumBertransaksi = Pelanggan::query()->doesntHave('transaksi')->count();
        $pernahBertransaksi = max(0, $totalPelanggan - $belumBertransaksi - $pelangganBulanIni);

        $statusRingkasan = collect([
            ['label' => 'Aktif bulan ini', 'jumlah' => $pelangganBulanIni, 'warna' => '#378bf4'],
            ['label' => 'Pernah bertransaksi', 'jumlah' => $pernahBertransaksi, 'warna' => '#36b86c'],
            ['label' => 'Belum bertransaksi', 'jumlah' => $belumBertransaksi, 'warna' => '#ff9b28'],
        ])->map(fn ($item) => (object) $item);
        $jumlahSegment = $statusRingkasan->sum('jumlah');
        $sudut = 0;
        foreach ($statusRingkasan as $segment) {
            $segment->mulai = $sudut;
            $sudut += $jumlahSegment ? ($segment->jumlah / $jumlahSegment) * 360 : 0;
            $segment->akhir = $sudut;
        }

        $search = trim((string) $request->query('search', ''));
        $query = Pelanggan::query()
            ->withCount(['transaksi as jumlah_transaksi' => $filterTransaksi])
            ->withCount(['transaksi as cucian_berjalan' => function (Builder $transactionQuery) use ($filterTransaksi) {
                $filterTransaksi($transactionQuery);
                $transactionQuery->whereNotIn(DB::raw('LOWER(status_cucian)'), ['selesai', 'diambil']);
            }])
            ->withMax(['transaksi as transaksi_terakhir' => $filterTransaksi], 'tanggal_masuk');

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('nama_pelanggan', 'like', "%{$search}%")
                    ->orWhere('no_telp', 'like', "%{$search}%");
            });
        }

        $pelanggan = $query->orderByDesc('transaksi_terakhir')
            ->orderBy('nama_pelanggan')
            ->paginate(10)
            ->withQueryString();
        $aktivitas = $this->aktivitasTerbaru();

        return view('pelanggan.index', compact(
            'pelanggan', 'totalPelanggan', 'pelangganBulanIni', 'pelangganBaru',
            'cucianBerjalan', 'statusRingkasan', 'jumlahSegment', 'bulan', 'tahun',
            'namaBulan', 'search', 'paketId', 'aktivitas'
        ));
    }

    public function create(): View
    {
        return view('pelanggan.form', ['pelanggan' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_pelanggan' => ['required', 'string', 'max:255'],
            'no_telp' => ['required', 'digits_between:10,13', 'unique:pelanggan,no_telp'],
        ], [
            'nama_pelanggan.required' => 'Nama pelanggan wajib diisi.',
            'no_telp.required' => 'Nomor telepon wajib diisi.',
            'no_telp.digits_between' => 'Nomor telepon harus terdiri dari 10 sampai 13 angka.',
            'no_telp.unique' => 'Nomor telepon sudah terdaftar.',
        ]);

        Pelanggan::create($validated);

        return redirect()->route('pelanggan.index')
            ->with('success', 'Data pelanggan berhasil ditambahkan.');
    }

    public function show(Pelanggan $pelanggan): View
    {
        $jumlahTransaksi = $pelanggan->transaksi()->count();
        $pelanggan->load(['transaksi' => fn ($query) => $query
            ->with('detailTransaksi.paketLaundry')
            ->orderByDesc('tanggal_masuk')
            ->limit(10)]);

        return view('pelanggan.show', compact('pelanggan', 'jumlahTransaksi'));
    }

    public function edit(Pelanggan $pelanggan): View
    {
        return view('pelanggan.form', compact('pelanggan'));
    }

    public function update(Request $request, Pelanggan $pelanggan): RedirectResponse
    {
        $validated = $request->validate([
            'nama_pelanggan' => ['required', 'string', 'max:255'],
            'no_telp' => [
                'required',
                'digits_between:10,13',
                Rule::unique('pelanggan', 'no_telp')->ignore($pelanggan->id),
            ],
        ], [
            'nama_pelanggan.required' => 'Nama pelanggan wajib diisi.',
            'no_telp.required' => 'Nomor telepon wajib diisi.',
            'no_telp.digits_between' => 'Nomor telepon harus terdiri dari 10 sampai 13 angka.',
            'no_telp.unique' => 'Nomor telepon sudah digunakan pelanggan lain.',
        ]);

        $pelanggan->update($validated);

        return redirect()->route('pelanggan.index')
            ->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    public function destroy(Pelanggan $pelanggan): RedirectResponse
    {
        if ($pelanggan->transaksi()->exists()) {
            return redirect()->route('pelanggan.index')
                ->with('error', 'Pelanggan tidak dapat dihapus karena memiliki riwayat transaksi.');
        }

        $pelanggan->delete();

        return redirect()->route('pelanggan.index')
            ->with('success', 'Data pelanggan berhasil dihapus.');
    }

    public function aktivitas(): View
    {
        $aktivitas = Transaksi::query()
            ->with(['pelanggan', 'detailTransaksi.paketLaundry'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('pelanggan.aktivitas', compact('aktivitas'));
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
     * @return EloquentCollection<int, Transaksi>
     */
    private function aktivitasTerbaru(): EloquentCollection
    {
        return Transaksi::query()
            ->with('pelanggan')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function (Transaksi $transaksi): Transaksi {
                $aktivitas = match (strtolower($transaksi->status_cucian)) {
                    'selesai' => 'Pesanan selesai',
                    'diambil' => 'Cucian telah diambil',
                    default => 'Transaksi cucian dibuat',
                };

                $transaksi->setAttribute('aktivitas', $aktivitas);

                return $transaksi;
            });
    }
}
