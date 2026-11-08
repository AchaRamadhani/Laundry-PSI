<x-layouts::app :title="$cucian ? 'Ubah Data Cucian' : 'Tambah Data Cucian'">
    @php
        $editing = $cucian !== null;
        $tanggalMasuk = old('tanggal_masuk', $cucian?->tanggal_masuk ?? now()->toDateString());
        $estimasiSelesai = old('estimasi_selesai', $cucian?->estimasi_selesai ? \Carbon\Carbon::parse($cucian->estimasi_selesai)->format('Y-m-d') : now()->addDays(2)->toDateString());
        $selectedPackages = old('paket_laundry_id', $editing ? $cucian->detailTransaksi->pluck('paket_laundry_id')->all() : ['']);
        $quantities = old('berat_qty', $editing ? $cucian->detailTransaksi->pluck('berat_qty')->all() : ['']);
    @endphp

    <div class="cucian-page cucian-form-page">
        <section class="cucian-heading form-heading">
            <div>
                <h1>{{ $editing ? 'Ubah Data Cucian' : 'Tambah Data Cucian' }}</h1>
                <p>{{ $editing ? 'Perbarui informasi transaksi dan layanan cucian.' : 'Masukkan data pelanggan dan layanan cucian baru.' }}</p>
            </div>
            <a class="cucian-secondary-button" href="{{ route('cucian.index') }}">← Kembali ke Data Cucian</a>
        </section>

        @if ($errors->any())
            <div class="cucian-alert error" role="alert">
                <strong>Periksa kembali data yang dimasukkan.</strong>
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form class="cucian-form-card" action="{{ $editing ? route('cucian.update', $cucian) : route('cucian.store') }}" method="POST">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="cucian-form-section">
                <div class="form-section-heading"><span>01</span><div><h2>Informasi Cucian</h2><p>Lengkapi pelanggan dan tanggal transaksi.</p></div></div>
                <div class="cucian-form-grid">
                    <label class="cucian-field">
                        <span>Pelanggan <b>*</b></span>
                        <select name="pelanggan_id" required>
                            <option value="">Pilih pelanggan</option>
                            @foreach ($pelanggan as $item)
                                <option value="{{ $item->id }}" @selected((string) old('pelanggan_id', $cucian?->pelanggan_id) === (string) $item->id)>{{ $item->nama_pelanggan }} — {{ $item->no_telp }}</option>
                            @endforeach
                        </select>
                        @if ($pelanggan->isEmpty())<small>Belum ada data pelanggan. Tambahkan pelanggan terlebih dahulu.</small>@endif
                    </label>
                    <label class="cucian-field">
                        <span>Tanggal Masuk <b>*</b></span>
                        <input type="date" name="tanggal_masuk" value="{{ $tanggalMasuk }}" required>
                    </label>
                    <label class="cucian-field">
                        <span>Estimasi Selesai</span>
                        <input type="date" name="estimasi_selesai" value="{{ $estimasiSelesai }}">
                    </label>
                    @if ($editing)
                        <div class="cucian-field readonly-status">
                            <span>Status saat ini</span>
                            <div><i></i>{{ ucfirst($cucian->status_cucian) }} <small>Status diubah dari menu Update Status</small></div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="cucian-form-section">
                <div class="form-section-heading"><span>02</span><div><h2>Layanan Laundry</h2><p>Pilih satu atau beberapa layanan. Harga dihitung dari harga paket dan berat/jumlah.</p></div></div>
                <div id="cucianDetailRows" class="cucian-detail-rows">
                    @foreach ($selectedPackages as $index => $selectedPackage)
                        <div class="cucian-detail-row">
                            <label class="cucian-field">
                                <span>Layanan</span>
                                <select name="paket_laundry_id[]" required>
                                    <option value="">Pilih layanan</option>
                                    @foreach ($paketLaundry as $paket)
                                        <option value="{{ $paket->id }}" data-price="{{ $paket->harga_mulai }}" @selected((string) $selectedPackage === (string) $paket->id)>{{ $paket->nama_paket }} · Rp {{ number_format((float) $paket->harga_mulai, 0, ',', '.') }} / {{ strtolower($paket->kategori) === 'satuan' ? 'pcs' : 'kg' }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="cucian-field">
                                <span>Berat / Jumlah</span>
                                <div class="quantity-input"><input type="number" name="berat_qty[]" value="{{ $quantities[$index] ?? '' }}" min="0.01" step="0.01" placeholder="Contoh: 2.5" required><small>kg / pcs</small></div>
                            </label>
                            <button class="remove-detail-row" type="button" aria-label="Hapus layanan" title="Hapus layanan"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14"/></svg></button>
                        </div>
                    @endforeach
                </div>
                @if ($paketLaundry->isEmpty())<p class="cucian-form-help error-text">Belum ada paket laundry. Buat paket aktif terlebih dahulu.</p>@endif
                <button class="add-detail-row" id="addDetailRow" type="button"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>Tambah layanan</button>

                <template id="detailRowTemplate">
                    <div class="cucian-detail-row">
                        <label class="cucian-field">
                            <span>Layanan</span>
                            <select name="paket_laundry_id[]" required>
                                <option value="">Pilih layanan</option>
                                @foreach ($paketLaundry as $paket)
                                    <option value="{{ $paket->id }}" data-price="{{ $paket->harga_mulai }}">{{ $paket->nama_paket }} · Rp {{ number_format((float) $paket->harga_mulai, 0, ',', '.') }} / {{ strtolower($paket->kategori) === 'satuan' ? 'pcs' : 'kg' }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="cucian-field">
                            <span>Berat / Jumlah</span>
                            <div class="quantity-input"><input type="number" name="berat_qty[]" min="0.01" step="0.01" placeholder="Contoh: 2.5" required><small>kg / pcs</small></div>
                        </label>
                        <button class="remove-detail-row" type="button" aria-label="Hapus layanan" title="Hapus layanan"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14"/></svg></button>
                    </div>
                </template>
            </div>

            <div class="cucian-form-section">
                <div class="form-section-heading"><span>03</span><div><h2>Catatan</h2><p>Catatan khusus untuk pesanan ini, jika ada.</p></div></div>
                <label class="cucian-field"><span>Catatan pesanan</span><textarea name="catatan" rows="3" placeholder="Contoh: pisahkan pakaian putih...">{{ old('catatan', $cucian?->catatan) }}</textarea></label>
            </div>

            <div class="cucian-form-footer">
                <p><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5m0-8h.01"/></svg>Status cucian awal akan menjadi Antrean.</p>
                <div><a class="cucian-secondary-button" href="{{ route('cucian.index') }}">Batal</a><button class="cucian-add-button" type="submit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>{{ $editing ? 'Simpan Perubahan' : 'Simpan Data Cucian' }}</button></div>
            </div>
        </form>
    </div>

    <script>
        (() => {
            const rows = document.getElementById('cucianDetailRows');
            const template = document.getElementById('detailRowTemplate');
            document.getElementById('addDetailRow')?.addEventListener('click', () => rows.append(template.content.cloneNode(true)));
            rows?.addEventListener('click', (event) => {
                const button = event.target.closest('.remove-detail-row');
                if (!button) return;
                if (rows.querySelectorAll('.cucian-detail-row').length > 1) button.closest('.cucian-detail-row').remove();
            });
        })();
    </script>
</x-layouts::app>
