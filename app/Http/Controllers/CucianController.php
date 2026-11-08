<?php

namespace App\Http\Controllers;

use App\Models\DetailTransaksi;
use App\Models\PaketLaundry;
use App\Models\Pelanggan;
use App\Models\StatusLaundry;
use App\Models\Transaksi;
use App\Support\LaundryOptions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CucianController extends Controller
{
    private const STATUS_COLORS = [
        'antrean' => '#67b5f6',
        'antrian' => '#67b5f6',
        'diproses' => '#ff9d22',
        'proses' => '#ff9d22',
        'dicuci' => '#3185ef',
        'dikeringkan' => '#20b8bf',
        'pengeringan' => '#20b8bf',
        'disetrika' => '#f2a21c',
        'selesai' => '#7552e8',
        'diambil' => '#55b96d',
    ];

    private const STATUS_LABELS = [
        'antrean' => 'Antrean',
        'antrian' => 'Antrean',
        'diproses' => 'Diproses',
        'proses' => 'Diproses',
        'dicuci' => 'Dicuci',
        'dikeringkan' => 'Dikeringkan',
        'pengeringan' => 'Dikeringkan',
        'disetrika' => 'Disetrika',
        'selesai' => 'Selesai',
        'diambil' => 'Diambil',
    ];

    public function index(Request $request)
    {
        $periodeInput = (string) $request->query('periode', '');
        if (preg_match('/^(\d{4})-(\d{2})$/', $periodeInput, $periodeMatch)) {
            $tahun = (int) $periodeMatch[1];
            $bulan = (int) $periodeMatch[2];
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

        $periode = Transaksi::query()->whereBetween('tanggal_masuk', [
            $awalBulan->toDateString(),
            $akhirBulan->toDateString(),
        ]);

        $totalCucian = (clone $periode)->count();
        $sedangDiproses = (clone $periode)
            ->whereNotIn(DB::raw('LOWER(status_cucian)'), ['selesai', 'diambil'])
            ->count();
        $totalSelesai = (clone $periode)
            ->whereRaw('LOWER(status_cucian) = ?', ['selesai'])
            ->count();
        $totalDiambil = (clone $periode)
            ->whereRaw('LOWER(status_cucian) = ?', ['diambil'])
            ->count();

        $statusRingkasan = (clone $periode)
            ->selectRaw('LOWER(status_cucian) as status_key, COUNT(*) as jumlah')
            ->groupBy(DB::raw('LOWER(status_cucian)'))
            ->orderBy('status_key')
            ->get()
            ->map(function ($item) {
                $item->label = self::STATUS_LABELS[$item->status_key]
                    ?? ucfirst(str_replace('_', ' ', $item->status_key));
                $item->warna = self::STATUS_COLORS[$item->status_key] ?? '#9aa4b4';

                return $item;
            });

        $totalStatus = $statusRingkasan->sum('jumlah');
        $sudut = 0;
        foreach ($statusRingkasan as $item) {
            $item->mulai = $sudut;
            $sudut += $totalStatus ? ($item->jumlah / $totalStatus) * 360 : 0;
            $item->akhir = $sudut;
        }

        $query = (clone $periode)->with([
            'pelanggan',
            'detailTransaksi.paketLaundry',
        ]);

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('no_nota', 'like', "%{$search}%")
                    ->orWhereHas('pelanggan', function ($customerQuery) use ($search) {
                        $customerQuery->where('nama_pelanggan', 'like', "%{$search}%")
                            ->orWhere('no_telp', 'like', "%{$search}%");
                    });
            });
        }

        $status = strtolower(trim((string) $request->query('status', '')));
        if ($status !== '') {
            $statusAliases = match ($status) {
                'antrean' => ['antrean', 'antrian'],
                'diproses' => ['diproses', 'proses'],
                'dikeringkan' => ['dikeringkan', 'pengeringan'],
                default => [$status],
            };
            $query->whereIn(DB::raw('LOWER(status_cucian)'), $statusAliases);
        }

        $paketId = $request->query('layanan');
        if (is_numeric($paketId)) {
            $query->whereHas('detailTransaksi', function ($detailQuery) use ($paketId) {
                $detailQuery->where('paket_laundry_id', $paketId);
            });
        } else {
            $serviceNames = LaundryOptions::serviceNamesForFilter(is_string($paketId) ? $paketId : null);
            if ($serviceNames !== []) {
                $query->whereHas('detailTransaksi.paketLaundry', function ($packageQuery) use ($serviceNames) {
                    $packageQuery->whereIn(DB::raw('LOWER(nama_paket)'), $serviceNames);
                });
            }
        }

        $cucian = $query
            ->orderByDesc('tanggal_masuk')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $aktivitas = $this->aktivitasTerbaru();

        return view('cucian.index', compact(
            'bulan', 'tahun', 'namaBulan', 'cucian', 'search',
            'status', 'paketId', 'totalCucian', 'sedangDiproses', 'totalSelesai',
            'totalDiambil', 'statusRingkasan', 'totalStatus', 'aktivitas'
        ));
    }

    public function create()
    {
        return view('cucian.form', [
            'cucian' => null,
            'pelanggan' => Pelanggan::query()->orderBy('nama_pelanggan')->get(),
            'paketLaundry' => PaketLaundry::query()
                ->whereRaw('LOWER(status) = ?', ['aktif'])
                ->orderBy('nama_paket')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatedData($request);

        DB::transaction(function () use ($validated) {
            $transaksi = Transaksi::create([
                'no_nota' => $this->nomorNota($validated['tanggal_masuk']),
                'pelanggan_id' => $validated['pelanggan_id'],
                'id_admin' => auth()->id(),
                'tanggal_masuk' => $validated['tanggal_masuk'],
                'estimasi_selesai' => $validated['estimasi_selesai'] ?? null,
                'total_harga' => 0,
                'status_cucian' => 'antrean',
                'catatan' => $validated['catatan'] ?? null,
            ]);

            $total = $this->simpanDetail($transaksi, $validated);
            $transaksi->update(['total_harga' => $total]);

            StatusLaundry::create([
                'no_nota' => $transaksi->no_nota,
                'id_admin' => auth()->id(),
                'status' => 'antrean',
                'catatan_status' => 'Data cucian baru ditambahkan.',
                'waktu_status' => now(),
            ]);
        });

        return redirect()->route('cucian.index')->with('success', 'Data cucian berhasil ditambahkan.');
    }

    public function show(Transaksi $cucian)
    {
        $cucian->load(['pelanggan', 'detailTransaksi.paketLaundry', 'statusLaundry.admin']);

        return view('cucian.show', compact('cucian'));
    }

    public function edit(Transaksi $cucian)
    {
        $cucian->load(['detailTransaksi.paketLaundry']);
        $paketTerpakai = $cucian->detailTransaksi->pluck('paket_laundry_id');

        return view('cucian.form', [
            'cucian' => $cucian,
            'pelanggan' => Pelanggan::query()->orderBy('nama_pelanggan')->get(),
            'paketLaundry' => PaketLaundry::query()
                ->where(function ($query) use ($paketTerpakai) {
                    $query->whereRaw('LOWER(status) = ?', ['aktif'])
                        ->orWhereIn('id', $paketTerpakai);
                })
                ->orderBy('nama_paket')
                ->get(),
        ]);
    }

    public function update(Request $request, Transaksi $cucian)
    {
        $validated = $this->validatedData($request);

        DB::transaction(function () use ($validated, $cucian) {
            $cucian->update([
                'pelanggan_id' => $validated['pelanggan_id'],
                'tanggal_masuk' => $validated['tanggal_masuk'],
                'estimasi_selesai' => $validated['estimasi_selesai'] ?? null,
                'catatan' => $validated['catatan'] ?? null,
            ]);

            $cucian->detailTransaksi()->delete();
            $total = $this->simpanDetail($cucian, $validated);
            $cucian->update(['total_harga' => $total]);
        });

        return redirect()->route('cucian.index')->with('success', 'Data cucian berhasil diperbarui.');
    }

    public function destroy(Transaksi $cucian)
    {
        $cucian->delete();

        return redirect()->route('cucian.index')->with('success', 'Data cucian berhasil dihapus.');
    }

    private function validatedData(Request $request): array
    {
        $jumlahLayanan = count((array) $request->input('paket_laundry_id', []));

        return $request->validate([
            'pelanggan_id' => ['required', 'integer', 'exists:pelanggan,id'],
            'tanggal_masuk' => ['required', 'date'],
            'estimasi_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_masuk'],
            'catatan' => ['nullable', 'string', 'max:2000'],
            'paket_laundry_id' => ['required', 'array', 'min:1'],
            'paket_laundry_id.*' => ['required', 'integer', 'exists:paket_laundry,id'],
            'berat_qty' => ['required', 'array', 'size:'.$jumlahLayanan],
            'berat_qty.*' => ['required', 'numeric', 'gt:0'],
        ], [
            'pelanggan_id.required' => 'Pilih pelanggan terlebih dahulu.',
            'tanggal_masuk.required' => 'Tanggal masuk wajib diisi.',
            'estimasi_selesai.after_or_equal' => 'Estimasi selesai tidak boleh sebelum tanggal masuk.',
            'paket_laundry_id.required' => 'Tambahkan minimal satu layanan.',
            'paket_laundry_id.*.exists' => 'Paket laundry yang dipilih tidak tersedia.',
            'berat_qty.*.gt' => 'Berat atau jumlah harus lebih besar dari nol.',
        ]);
    }

    private function simpanDetail(Transaksi $transaksi, array $validated): float
    {
        $paket = PaketLaundry::query()
            ->whereIn('id', $validated['paket_laundry_id'])
            ->get()
            ->keyBy('id');

        $total = 0;
        foreach ($validated['paket_laundry_id'] as $index => $paketId) {
            $jumlah = (float) $validated['berat_qty'][$index];
            $subtotal = round((float) $paket->get($paketId)->harga_mulai * $jumlah, 2);

            DetailTransaksi::create([
                'no_nota' => $transaksi->no_nota,
                'paket_laundry_id' => $paketId,
                'berat_qty' => $jumlah,
                'subtotal' => $subtotal,
            ]);

            $total += $subtotal;
        }

        return round($total, 2);
    }

    private function nomorNota(string $tanggalMasuk): string
    {
        $prefix = 'GL-'.Carbon::parse($tanggalMasuk)->format('ymd').'-';
        // Ambil akhiran nota sebagai nomor urut untuk tanggal masuk yang dipilih.
        $terakhir = Transaksi::query()
            ->where('no_nota', 'like', $prefix.'%')
            ->orderByDesc('no_nota')
            ->value('no_nota');
        $nomor = $terakhir ? (int) substr($terakhir, -3) + 1 : 1;

        do {
            $nota = $prefix.str_pad((string) $nomor, 3, '0', STR_PAD_LEFT);
            $nomor++;
        } while (Transaksi::query()->whereKey($nota)->exists());

        return $nota;
    }

    private function aktivitasTerbaru()
    {
        $aktivitas = DB::table('status_laundry')
            ->join('transaksi', 'status_laundry.no_nota', '=', 'transaksi.no_nota')
            ->join('pelanggan', 'transaksi.pelanggan_id', '=', 'pelanggan.id')
            ->select(
                'status_laundry.status',
                'status_laundry.catatan_status',
                'status_laundry.waktu_status',
                'transaksi.no_nota',
                'pelanggan.nama_pelanggan'
            )
            ->orderByDesc('status_laundry.waktu_status')
            ->limit(5)
            ->get();

        if ($aktivitas->isEmpty()) {
            return DB::table('transaksi')
                ->join('pelanggan', 'transaksi.pelanggan_id', '=', 'pelanggan.id')
                ->select(
                    'transaksi.status_cucian as status',
                    'transaksi.created_at as waktu_status',
                    'transaksi.no_nota',
                    'pelanggan.nama_pelanggan'
                )
                ->orderByDesc('transaksi.created_at')
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    $status = strtolower($item->status);
                    $item->label = 'Data cucian tercatat';
                    $item->status_label = self::STATUS_LABELS[$status] ?? ucfirst($status);
                    $item->warna = self::STATUS_COLORS[$status] ?? '#4b91ed';

                    return $item;
                });
        }

        return $aktivitas->map(function ($item) {
            $status = strtolower($item->status);
            $item->label = in_array($status, ['antrean', 'antrian'], true)
                ? 'Data cucian baru ditambahkan'
                : 'Status cucian diperbarui';
            $item->status_label = self::STATUS_LABELS[$status] ?? ucfirst($status);
            $item->warna = self::STATUS_COLORS[$status] ?? '#4b91ed';

            return $item;
        });
    }
}
