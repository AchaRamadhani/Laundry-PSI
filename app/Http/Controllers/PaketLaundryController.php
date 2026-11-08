<?php

namespace App\Http\Controllers;

use App\Models\PaketLaundry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PaketLaundryController extends Controller
{
    private const KATEGORI_DEFAULT = ['Kiloan', 'Khusus', 'Satuan'];

    private const STATUS_VALID = ['aktif', 'nonaktif'];

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $kategori = trim((string) $request->query('kategori', ''));
        $status = strtolower(trim((string) $request->query('status', '')));

        $totalPaket = PaketLaundry::query()->count();
        $paketAktif = PaketLaundry::query()->whereRaw('LOWER(status) = ?', ['aktif'])->count();
        $layananKiloan = PaketLaundry::query()
            ->whereRaw('LOWER(kategori) LIKE ?', ['%kilo%'])
            ->count();
        $layananKhusus = max(0, $totalPaket - $layananKiloan);

        $kategoriRingkasan = DB::table('paket_laundry')
            ->select('kategori', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('kategori')
            ->orderBy('kategori')
            ->get();
        $jumlahKategori = $kategoriRingkasan->sum('jumlah');

        $query = PaketLaundry::query();
        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('nama_paket', 'like', "%{$search}%")
                    ->orWhere('kategori', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }
        if ($kategori !== '') {
            $query->where('kategori', $kategori);
        }
        if (in_array($status, self::STATUS_VALID, true)) {
            $query->whereRaw('LOWER(status) = ?', [$status]);
        }

        $paketLaundry = $query->orderBy('kategori')->orderBy('nama_paket')
            ->paginate(9)
            ->withQueryString();
        $kategoriOptions = PaketLaundry::query()->distinct()->orderBy('kategori')->pluck('kategori')
            ->merge(self::KATEGORI_DEFAULT)
            ->unique(fn ($value) => strtolower($value))
            ->values();

        return view('paket-laundry.index', compact(
            'paketLaundry', 'totalPaket', 'paketAktif', 'layananKiloan',
            'layananKhusus', 'kategoriRingkasan', 'jumlahKategori',
            'kategoriOptions', 'search', 'kategori', 'status'
        ));
    }

    public function create()
    {
        return view('paket-laundry.form', [
            'paket' => null,
            'kategoriOptions' => $this->kategoriOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $paket = PaketLaundry::create($this->validatedData($request));

        return redirect()->route('paket-laundry.index')
            ->with('success', 'Paket laundry '.$paket->nama_paket.' berhasil ditambahkan.');
    }

    public function show(PaketLaundry $paket)
    {
        $jumlahPenggunaan = DB::table('detail_transaksi')
            ->where('paket_laundry_id', $paket->id)
            ->distinct()
            ->count('no_nota');
        $paket->load(['detailTransaksi' => fn ($query) => $query
            ->with('transaksi.pelanggan')
            ->orderByDesc('created_at')
            ->limit(10)]);

        return view('paket-laundry.show', compact('paket', 'jumlahPenggunaan'));
    }

    public function edit(PaketLaundry $paket)
    {
        return view('paket-laundry.form', [
            'paket' => $paket,
            'kategoriOptions' => $this->kategoriOptions($paket->kategori),
        ]);
    }

    public function update(Request $request, PaketLaundry $paket)
    {
        $paket->update($this->validatedData($request));

        return redirect()->route('paket-laundry.index')
            ->with('success', 'Paket laundry '.$paket->nama_paket.' berhasil diperbarui.');
    }

    public function destroy(PaketLaundry $paket)
    {
        if ($paket->detailTransaksi()->exists()) {
            return redirect()->route('paket-laundry.index')
                ->with('error', 'Paket tidak dapat dihapus karena sudah digunakan dalam transaksi. Ubah status menjadi nonaktif jika paket tidak lagi tersedia.');
        }

        $nama = $paket->nama_paket;
        $paket->delete();

        return redirect()->route('paket-laundry.index')
            ->with('success', 'Paket laundry '.$nama.' berhasil dihapus.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'nama_paket' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:80'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'harga_mulai' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'durasi' => ['required', 'integer', 'min:0', 'max:365'],
            'status' => ['required', Rule::in(self::STATUS_VALID)],
        ], [
            'nama_paket.required' => 'Nama paket wajib diisi.',
            'kategori.required' => 'Pilih kategori paket.',
            'harga_mulai.required' => 'Harga mulai wajib diisi.',
            'harga_mulai.numeric' => 'Harga harus berupa angka.',
            'durasi.required' => 'Durasi layanan wajib diisi.',
            'durasi.integer' => 'Durasi harus berupa jumlah hari.',
            'status.required' => 'Pilih status paket.',
        ]);
    }

    private function kategoriOptions(?string $kategoriSaatIni = null)
    {
        return PaketLaundry::query()->distinct()->orderBy('kategori')->pluck('kategori')
            ->merge(self::KATEGORI_DEFAULT)
            ->when($kategoriSaatIni, fn ($options) => $options->push($kategoriSaatIni))
            ->unique(fn ($value) => strtolower($value))
            ->values();
    }
}
