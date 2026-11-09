<x-layouts::app :title="$cucian ? 'Ubah Data Cucian' : 'Tambah Data Cucian'">
    @php
        $editing = $cucian !== null;
        $tanggalMasuk = old('tanggal_masuk', $cucian?->tanggal_masuk ?? now()->toDateString());
        $estimasiSelesai = old('estimasi_selesai', $cucian?->estimasi_selesai ? \Carbon\Carbon::parse($cucian->estimasi_selesai)->format('Y-m-d') : now()->addDays(2)->toDateString());
        $selectedPackages = old('paket_laundry_id', $editing ? $cucian->detailTransaksi->pluck('paket_laundry_id')->all() : ['']);
        $quantities = old('berat_qty', $editing ? $cucian->detailTransaksi->pluck('berat_qty')->all() : ['']);
    @endphp

    @if ($editing)
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
    @else
    @php
        $selectedService = old('layanan', '');
        $selectedServiceOption = $layananOptions[$selectedService] ?? null;
        $selectedUnit = $selectedServiceOption['unit'] ?? 'kg';
    @endphp
    <main class="cucian-figma-page">
        <header class="cucian-figma-heading">
            <div>
                <h1>Data Cucian</h1>
                <p>Masukkan data cucian pelanggan dengan lengkap.</p>
            </div>
            <a class="cucian-figma-back" href="{{ route('cucian.index') }}">← Kembali ke Data Cucian</a>
        </header>

        @if ($errors->any())
            <div class="cucian-alert error" role="alert">
                <strong>Periksa kembali data yang dimasukkan.</strong>
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form class="cucian-figma-card" action="{{ route('cucian.store') }}" method="POST" id="newCucianForm">
            @csrf
            <div class="cucian-figma-grid">
                <label class="cucian-figma-field">
                    <span>No. Nota</span>
                    <input class="nota-preview" type="text" placeholder="Contoh: GL-0000-000" disabled>
                    <small>No. Nota akan digenerate otomatis berdasarkan tanggal masuk.</small>
                </label>
                <label class="cucian-figma-field">
                    <span>Nama Pelanggan <b>*</b></span>
                    <input type="text" name="nama_pelanggan" value="{{ old('nama_pelanggan') }}" maxlength="255" placeholder="Masukkan nama pelanggan" required>
                </label>

                <label class="cucian-figma-field">
                    <span>No. Telepon <b>*</b></span>
                    <input type="tel" name="no_telp" value="{{ old('no_telp') }}" maxlength="18" inputmode="tel" placeholder="Contoh: 0812-4291-0340" required>
                    <small>Masukkan nomor yang digunakan pada WhatsApp pelanggan.</small>
                </label>
                <label class="cucian-figma-field">
                    <span>Jenis Layanan <b>*</b></span>
                    <select name="layanan" id="laundryService" required>
                        <option value="">Pilih jenis layanan</option>
                        @foreach ($layananOptions as $key => $layanan)
                            <option value="{{ $key }}" data-unit="{{ $layanan['unit'] }}" data-has-package="{{ !empty($paketPerLayanan[$key]) ? '1' : '0' }}" @selected($selectedService === $key)>{{ $layanan['nama'] }}</option>
                        @endforeach
                    </select>
                    <small id="servicePackageHelp" class="{{ $selectedService && empty($paketPerLayanan[$selectedService]) ? 'is-error' : '' }}">{{ $selectedService && empty($paketPerLayanan[$selectedService]) ? 'Paket aktif untuk layanan ini belum tersedia. Tambahkan paketnya melalui menu Paket Laundry.' : '' }}</small>
                </label>

                <label class="cucian-figma-field">
                    <span>Berat / Qty <b>*</b></span>
                    <div class="figma-quantity-field">
                        <input type="number" id="laundryQuantity" name="berat_qty" value="{{ old('berat_qty') }}" min="{{ $selectedUnit === 'kg' ? '3' : '1' }}" step="{{ $selectedUnit === 'kg' ? '0.01' : '1' }}" placeholder="Contoh: {{ $selectedUnit === 'kg' ? '3,5' : '2' }}" required>
                        <span id="laundryUnit">{{ $selectedUnit }}</span>
                    </div>
                    <small id="quantityHelp">{{ $selectedUnit === 'kg' ? 'Berat cucian minimal 3 kg.' : 'Masukkan jumlah cucian dalam pcs.' }}</small>
                </label>
                <label class="cucian-figma-field">
                    <span>Tanggal Masuk <b>*</b></span>
                    <input type="date" name="tanggal_masuk" value="{{ $tanggalMasuk }}" required>
                </label>

                <label class="cucian-figma-field">
                    <span>Estimasi Selesai <b>*</b></span>
                    <input type="date" name="estimasi_selesai" value="{{ $estimasiSelesai }}" min="{{ $tanggalMasuk }}" required>
                </label>
                <label class="cucian-figma-field">
                    <span>Status Awal</span>
                    <input type="text" value="Antrean" readonly>
                    <small>Antrean merupakan status awal cucian.</small>
                </label>

                <label class="cucian-figma-field">
                    <span>Total Harga <b>*</b></span>
                    <div class="figma-price-field"><span>Rp</span><input type="text" id="cucianTotalHarga" name="total_harga" value="{{ old('total_harga') }}" inputmode="numeric" placeholder="Contoh: 75.000" required></div>
                    <small>Masukkan total harga berdasarkan hasil perhitungan berat/jumlah cucian.</small>
                </label>
                <label class="cucian-figma-field">
                    <span>Catatan</span>
                    <input type="text" name="catatan" value="{{ old('catatan') }}" maxlength="2000" placeholder="Contoh: Ada noda membandel di kerah, dll">
                    <small>Catatan tambahan untuk cucian (opsional).</small>
                </label>
            </div>

            <footer class="cucian-figma-footer">
                <button class="figma-clear-button" type="reset"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 11a8 8 0 0 1 14-5l2 2M20 4v4h-4M20 13a8 8 0 0 1-14 5l-2-2m0 5v-5h5"/></svg>Bersihkan</button>
                <div><a class="figma-cancel-button" href="{{ route('cucian.index') }}">Batal</a><button class="figma-save-button" type="submit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h12l3 3v13H4V4zM8 4v6h8V4M8 20v-7h8v7"/></svg>Simpan Data</button></div>
            </footer>
        </form>
    </main>
    @endif

    <script>
        (() => {
            const service = document.getElementById('laundryService');
            if (service) {
                const quantity = document.getElementById('laundryQuantity');
                const unit = document.getElementById('laundryUnit');
                const quantityHelp = document.getElementById('quantityHelp');
                const packageHelp = document.getElementById('servicePackageHelp');
                const updateServiceFields = () => {
                    const option = service.selectedOptions[0];
                    if (!option?.value) return;
                    const selectedUnit = option.dataset.unit || 'kg';
                    const isKiloan = selectedUnit === 'kg';
                    unit.textContent = selectedUnit;
                    quantity.min = isKiloan ? '3' : '1';
                    quantity.step = isKiloan ? '0.01' : '1';
                    quantity.placeholder = isKiloan ? 'Contoh: 3,5' : 'Contoh: 2';
                    quantityHelp.textContent = isKiloan ? 'Berat cucian minimal 3 kg.' : 'Masukkan jumlah cucian dalam pcs.';
                    const available = option.dataset.hasPackage === '1';
                    packageHelp.textContent = available ? '' : 'Paket aktif untuk layanan ini belum tersedia. Tambahkan paketnya melalui menu Paket Laundry.';
                    packageHelp.classList.toggle('is-error', !available);
                };
                service.addEventListener('change', updateServiceFields);
                updateServiceFields();
            }

            const totalHarga = document.getElementById('cucianTotalHarga');
            if (totalHarga) {
                const hanyaAngka = () => { totalHarga.value = totalHarga.value.replace(/\D/g, ''); };
                const formatRupiah = () => {
                    hanyaAngka();
                    if (totalHarga.value) totalHarga.value = new Intl.NumberFormat('id-ID').format(Number(totalHarga.value));
                };
                totalHarga.addEventListener('focus', hanyaAngka);
                totalHarga.addEventListener('input', hanyaAngka);
                totalHarga.addEventListener('blur', formatRupiah);
                formatRupiah();
            }

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
