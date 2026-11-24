<x-layouts::app :title="$cucian ? 'Ubah Data Cucian' : 'Tambah Data Cucian'">
    @php
        $editing = $cucian !== null;
        $tanggalMasukRaw = old('tanggal_masuk', $cucian?->tanggal_masuk ?? today('Asia/Makassar')->toDateString());
        $estimasiSelesaiRaw = old('estimasi_selesai', $cucian?->estimasi_selesai ? \Carbon\Carbon::parse($cucian->estimasi_selesai)->format('Y-m-d') : '');
        $tanggalMasukPicker = \App\Support\LaundryDateFormatter::normalizeInput($tanggalMasukRaw);
        $estimasiSelesaiPicker = \App\Support\LaundryDateFormatter::normalizeInput($estimasiSelesaiRaw);
        $tanggalMasukPicker = is_string($tanggalMasukPicker) && preg_match('/\A\d{4}-\d{2}-\d{2}\z/', $tanggalMasukPicker) ? $tanggalMasukPicker : '';
        $estimasiSelesaiPicker = is_string($estimasiSelesaiPicker) && preg_match('/\A\d{4}-\d{2}-\d{2}\z/', $estimasiSelesaiPicker) ? $estimasiSelesaiPicker : '';
        $dateFormat = \App\Support\LaundryDateFormatter::pattern();
        $datePlaceholder = \App\Support\LaundryDateFormatter::inputPlaceholder();
        $selectedPackages = old('paket_laundry_id', $editing ? $cucian->detailTransaksi->pluck('paket_laundry_id')->all() : ['']);
        $quantities = old('berat_qty', $editing ? $cucian->detailTransaksi->pluck('berat_qty')->all() : ['']);
        $statusPembayaran = old('status_pembayaran', $cucian?->status_pembayaran ?? 'belum_lunas');
        $metodePembayaran = old('metode_pembayaran', $cucian?->metode_pembayaran ?? '');
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
                        <div class="date-input-control" data-date-field>
                            <input type="text" name="tanggal_masuk" value="{{ \App\Support\LaundryDateFormatter::formatInput($tanggalMasukRaw) }}" data-date-display data-date-format="{{ $dateFormat }}" placeholder="{{ $datePlaceholder }}" inputmode="numeric" autocomplete="off" required>
                            <input class="date-picker-native" type="date" data-date-picker value="{{ $tanggalMasukPicker }}" tabindex="-1" aria-hidden="true">
                            <button class="date-picker-trigger" type="button" data-date-picker-trigger aria-label="Pilih tanggal masuk"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg></button>
                        </div>
                    </label>
                    <label class="cucian-field">
                        <span>Estimasi Selesai</span>
                        <div class="date-input-control" data-date-field>
                            <input type="text" name="estimasi_selesai" value="{{ \App\Support\LaundryDateFormatter::formatInput($estimasiSelesaiRaw) }}" data-date-display data-date-format="{{ $dateFormat }}" placeholder="{{ $datePlaceholder }}" inputmode="numeric" autocomplete="off">
                            <input class="date-picker-native" type="date" data-date-picker value="{{ $estimasiSelesaiPicker }}" min="{{ $tanggalMasukPicker }}" tabindex="-1" aria-hidden="true">
                            <button class="date-picker-trigger" type="button" data-date-picker-trigger aria-label="Pilih estimasi selesai"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg></button>
                        </div>
                    </label>
                    @if ($editing)
                        <div class="cucian-field readonly-status">
                            <span>Status saat ini</span>
                            <div><i></i>{{ ucfirst($cucian->status_cucian) }} <small>Status diubah dari menu Update Status</small></div>
                        </div>
                    @endif
                    <label class="cucian-field">
                        <span>Status Pembayaran <b>*</b></span>
                        <select id="statusPembayaran" name="status_pembayaran" required>
                            <option value="lunas" @selected($statusPembayaran === 'lunas')>Lunas (Sudah Dibayar)</option>
                            <option value="belum_lunas" @selected($statusPembayaran === 'belum_lunas')>Belum Lunas (Bayar saat Pengambilan)</option>
                        </select>
                    </label>
                    <label class="cucian-field">
                        <span>Metode Pembayaran <b class="payment-required-mark">*</b></span>
                        <select id="metodePembayaran" name="metode_pembayaran" @disabled($statusPembayaran !== 'lunas') @required($statusPembayaran === 'lunas')>
                            <option value="">Pilih metode pembayaran</option>
                            <option value="tunai" @selected($metodePembayaran === 'tunai')>Tunai / Cash</option>
                            <option value="qris" @selected($metodePembayaran === 'qris')>QRIS</option>
                        </select>
                        <small id="paymentMethodHelp">Pilih metode jika pembayaran sudah lunas.</small>
                    </label>
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
                                        <option value="{{ $paket->id }}" data-price="{{ $paket->harga_mulai }}" data-duration="{{ $paket->durasi }}" @selected((string) $selectedPackage === (string) $paket->id)>{{ $paket->nama_paket }} · Rp {{ number_format((float) $paket->harga_mulai, 0, ',', '.') }} / {{ strtolower($paket->kategori) === 'satuan' ? 'pcs' : 'kg' }}</option>
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
                                    <option value="{{ $paket->id }}" data-price="{{ $paket->harga_mulai }}" data-duration="{{ $paket->durasi }}">{{ $paket->nama_paket }} · Rp {{ number_format((float) $paket->harga_mulai, 0, ',', '.') }} / {{ strtolower($paket->kategori) === 'satuan' ? 'pcs' : 'kg' }}</option>
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
        $customerType = old('jenis_pelanggan') === 'lama' ? 'lama' : 'baru';
        $selectedPelanggan = $selectedPelanggan ?? null;
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

        <form class="cucian-figma-card" action="{{ route('cucian.store') }}" method="POST" id="newCucianForm" data-customer-search-url="{{ route('cucian.customer-search') }}">
            @csrf
            <section class="customer-selection-section" aria-labelledby="customerSelectionTitle">
                <div class="customer-selection-heading">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                    <h2 id="customerSelectionTitle">Informasi Pelanggan</h2>
                </div>
                <div class="customer-type-row">
                    <span class="customer-type-label">Jenis Pelanggan <b>*</b></span>
                    <div class="customer-type-options" role="radiogroup" aria-label="Jenis pelanggan">
                        <label class="customer-type-option">
                            <input type="radio" name="jenis_pelanggan" value="baru" @checked($customerType === 'baru')>
                            <span>Pelanggan Baru</span>
                        </label>
                        <label class="customer-type-option">
                            <input type="radio" name="jenis_pelanggan" value="lama" @checked($customerType === 'lama')>
                            <span>Pelanggan Lama</span>
                        </label>
                    </div>
                    <div class="customer-mode-message" id="customerModeMessage" role="status">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5m0-8h.01"/></svg>
                        <span>Pilih Pelanggan Baru untuk menambahkan pelanggan, atau Pelanggan Lama untuk mencari data yang sudah terdaftar.</span>
                    </div>
                </div>

                <div class="existing-customer-panel" id="existingCustomerPanel" @if ($customerType !== 'lama') hidden @endif>
                    <div class="existing-customer-search-area">
                        <div class="cucian-figma-field">
                            <label for="existingCustomerSearch">Cari Pelanggan Lama <b>*</b></label>
                            <div class="existing-customer-search-control">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m16 16 5 5"/></svg>
                                <input id="existingCustomerSearch" type="search" placeholder="Masukkan nama atau 4 digit terakhir nomor telepon" autocomplete="off" aria-describedby="existingCustomerSearchHelp">
                                <button id="searchExistingCustomer" type="button"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m16 16 5 5"/></svg><span>Cari</span></button>
                            </div>
                            <small id="existingCustomerSearchHelp">Contoh: ketik 0340 untuk mencari pelanggan dengan nomor telepon berakhiran 0340.</small>
                        </div>
                        <div class="existing-customer-results" id="existingCustomerResults" role="region" aria-label="Hasil pencarian pelanggan" aria-live="polite" hidden></div>
                        <p class="existing-customer-feedback" id="existingCustomerFeedback" role="status" hidden></p>
                    </div>
                    <aside class="existing-customer-selected" id="existingCustomerSelected" @if (!$selectedPelanggan) hidden @endif>
                        <h3><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m7.5 12 3 3 6-6"/></svg>Pelanggan Dipilih</h3>
                        <dl>
                            <div><dt>Nama Pelanggan</dt><dd id="selectedCustomerName">{{ $selectedPelanggan?->nama_pelanggan }}</dd></div>
                            <div><dt>No. Telepon</dt><dd id="selectedCustomerPhone">{{ $selectedPelanggan?->no_telp }}</dd></div>
                            <div><dt>Riwayat Transaksi</dt><dd><span id="selectedCustomerTransactions">{{ $selectedPelanggan?->transaksi_count ?? 0 }}</span> Transaksi</dd></div>
                        </dl>
                    </aside>
                </div>
                <input type="hidden" name="pelanggan_id" id="selectedCustomerId" value="{{ $customerType === 'lama' ? ($selectedPelanggan?->id ?? '') : '' }}">
            </section>

            <div class="cucian-figma-grid">
                <label class="cucian-figma-field">
                    <span>No. Nota</span>
                    <input class="nota-preview" type="text" placeholder="Contoh: GL-0000-000" disabled>
                    <small>No. Nota akan digenerate otomatis berdasarkan tanggal masuk.</small>
                </label>
                <label class="cucian-figma-field">
                    <span>Nama Pelanggan <b>*</b></span>
                    <input type="text" id="customerName" name="nama_pelanggan" value="{{ $customerType === 'lama' ? ($selectedPelanggan?->nama_pelanggan ?? '') : old('nama_pelanggan') }}" maxlength="255" placeholder="{{ $customerType === 'lama' ? 'Pilih pelanggan lama terlebih dahulu' : 'Masukkan nama pelanggan' }}" @required($customerType === 'baru') @readonly($customerType === 'lama')>
                </label>

                <label class="cucian-figma-field">
                    <span>No. Telepon <b>*</b></span>
                    <input type="tel" id="customerPhone" name="no_telp" value="{{ $customerType === 'lama' ? ($selectedPelanggan?->no_telp ?? '') : old('no_telp') }}" maxlength="13" inputmode="numeric" pattern="08[0-9]{8,11}" placeholder="{{ $customerType === 'lama' ? 'Pilih pelanggan lama terlebih dahulu' : 'Contoh: 081242910340' }}" @required($customerType === 'baru') @readonly($customerType === 'lama')>
                    <small>Gunakan nomor yang diawali 08, maksimal 13 digit.</small>
                </label>
                <label class="cucian-figma-field">
                    <span>Jenis Layanan <b>*</b></span>
                    <select name="layanan" id="laundryService" required>
                        <option value="">Pilih jenis layanan</option>
                        @foreach ($layananOptions as $key => $layanan)
                            <option value="{{ $key }}" data-unit="{{ $layanan['unit'] }}" data-duration="{{ $paketPerLayanan[$key]->durasi ?? '' }}" data-has-package="{{ !empty($paketPerLayanan[$key]) ? '1' : '0' }}" @selected($selectedService === $key)>{{ $layanan['nama'] }}</option>
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
                    <div class="date-input-control" data-date-field>
                        <input type="text" name="tanggal_masuk" value="{{ \App\Support\LaundryDateFormatter::formatInput($tanggalMasukRaw) }}" data-date-display data-date-format="{{ $dateFormat }}" placeholder="{{ $datePlaceholder }}" inputmode="numeric" autocomplete="off" required>
                        <input class="date-picker-native" type="date" data-date-picker value="{{ $tanggalMasukPicker }}" tabindex="-1" aria-hidden="true">
                        <button class="date-picker-trigger" type="button" data-date-picker-trigger aria-label="Pilih tanggal masuk"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg></button>
                    </div>
                </label>

                <label class="cucian-figma-field">
                    <span>Estimasi Selesai <b>*</b></span>
                    <div class="date-input-control" data-date-field>
                        <input type="text" name="estimasi_selesai" value="{{ \App\Support\LaundryDateFormatter::formatInput($estimasiSelesaiRaw) }}" data-date-display data-date-format="{{ $dateFormat }}" placeholder="{{ $datePlaceholder }}" inputmode="numeric" autocomplete="off" required>
                        <input class="date-picker-native" type="date" data-date-picker value="{{ $estimasiSelesaiPicker }}" min="{{ $tanggalMasukPicker }}" tabindex="-1" aria-hidden="true">
                        <button class="date-picker-trigger" type="button" data-date-picker-trigger aria-label="Pilih estimasi selesai"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg></button>
                    </div>
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
                    <span>Status Pembayaran <b>*</b></span>
                    <select id="statusPembayaran" name="status_pembayaran" required>
                        <option value="lunas" @selected($statusPembayaran === 'lunas')>Lunas (Sudah Dibayar)</option>
                        <option value="belum_lunas" @selected($statusPembayaran === 'belum_lunas')>Belum Lunas (Bayar saat Pengambilan)</option>
                    </select>
                    <small>Pilih status pembayaran transaksi ini.</small>
                </label>
                <label class="cucian-figma-field">
                    <span>Metode Pembayaran <b class="payment-required-mark">*</b></span>
                    <select id="metodePembayaran" name="metode_pembayaran" @disabled($statusPembayaran !== 'lunas') @required($statusPembayaran === 'lunas')>
                        <option value="">Pilih metode pembayaran</option>
                        <option value="tunai" @selected($metodePembayaran === 'tunai')>Tunai / Cash</option>
                        <option value="qris" @selected($metodePembayaran === 'qris')>QRIS</option>
                    </select>
                    <small id="paymentMethodHelp">Metode hanya dipilih jika pembayaran lunas di awal.</small>
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
            const form = document.querySelector('.cucian-form-card, .cucian-figma-card');
            const customerModeInputs = form?.querySelectorAll('[name="jenis_pelanggan"]');
            const customerModeMessage = form?.querySelector('#customerModeMessage span');
            const existingCustomerPanel = form?.querySelector('#existingCustomerPanel');
            const customerSearchInput = form?.querySelector('#existingCustomerSearch');
            const customerSearchButton = form?.querySelector('#searchExistingCustomer');
            const customerResults = form?.querySelector('#existingCustomerResults');
            const customerFeedback = form?.querySelector('#existingCustomerFeedback');
            const selectedCustomerCard = form?.querySelector('#existingCustomerSelected');
            const selectedCustomerId = form?.querySelector('#selectedCustomerId');
            const customerNameInput = form?.querySelector('#customerName');
            const customerPhoneInput = form?.querySelector('#customerPhone');
            const selectedCustomerName = form?.querySelector('#selectedCustomerName');
            const selectedCustomerPhone = form?.querySelector('#selectedCustomerPhone');
            const selectedCustomerTransactions = form?.querySelector('#selectedCustomerTransactions');

            const clearSelectedCustomer = () => {
                if (selectedCustomerId) selectedCustomerId.value = '';
                if (customerNameInput) customerNameInput.value = '';
                if (customerPhoneInput) customerPhoneInput.value = '';
                if (selectedCustomerName) selectedCustomerName.textContent = '';
                if (selectedCustomerPhone) selectedCustomerPhone.textContent = '';
                if (selectedCustomerTransactions) selectedCustomerTransactions.textContent = '0';
                if (selectedCustomerCard) selectedCustomerCard.hidden = true;
            };

            const syncCustomerMode = (clearSelection = false) => {
                const isExisting = form?.querySelector('[name="jenis_pelanggan"]:checked')?.value === 'lama';
                if (existingCustomerPanel) existingCustomerPanel.hidden = !isExisting;
                if (customerNameInput) {
                    customerNameInput.readOnly = isExisting;
                    customerNameInput.required = !isExisting;
                    customerNameInput.placeholder = isExisting ? 'Pilih pelanggan lama terlebih dahulu' : 'Masukkan nama pelanggan';
                }
                if (customerPhoneInput) {
                    customerPhoneInput.readOnly = isExisting;
                    customerPhoneInput.required = !isExisting;
                    customerPhoneInput.placeholder = isExisting ? 'Pilih pelanggan lama terlebih dahulu' : 'Contoh: 0812-4291-0340';
                }
                if (customerModeMessage) {
                    customerModeMessage.textContent = isExisting
                        ? 'Cari pelanggan lama dengan nama atau empat digit terakhir nomor telepon, lalu pilih data yang sesuai.'
                        : 'Pilih Pelanggan Baru untuk menambahkan data pelanggan, atau Pelanggan Lama untuk mencari pelanggan yang sudah terdaftar.';
                }
                if (clearSelection) {
                    clearSelectedCustomer();
                    if (customerSearchInput) customerSearchInput.value = '';
                    if (customerResults) {
                        customerResults.replaceChildren();
                        customerResults.hidden = true;
                    }
                    if (customerFeedback) {
                        customerFeedback.textContent = '';
                        customerFeedback.hidden = true;
                        customerFeedback.classList.remove('is-error');
                    }
                }
            };

            customerModeInputs?.forEach((radio) => radio.addEventListener('change', () => syncCustomerMode(true)));
            syncCustomerMode();

            const showCustomerFeedback = (message, isError = false) => {
                if (!customerFeedback) return;
                customerFeedback.textContent = message;
                customerFeedback.hidden = !message;
                customerFeedback.classList.toggle('is-error', isError);
            };

            const chooseExistingCustomer = (customer) => {
                if (selectedCustomerId) selectedCustomerId.value = customer.id;
                if (customerNameInput) customerNameInput.value = customer.nama;
                if (customerPhoneInput) customerPhoneInput.value = customer.telepon;
                if (selectedCustomerName) selectedCustomerName.textContent = customer.nama;
                if (selectedCustomerPhone) selectedCustomerPhone.textContent = customer.telepon;
                if (selectedCustomerTransactions) selectedCustomerTransactions.textContent = customer.jumlah_transaksi;
                if (selectedCustomerCard) selectedCustomerCard.hidden = false;
                customerResults?.querySelectorAll('[data-customer-id]').forEach((button) => {
                    const selected = button.dataset.customerId === String(customer.id);
                    button.classList.toggle('is-selected', selected);
                    button.setAttribute('aria-pressed', String(selected));
                    const marker = button.querySelector('.existing-customer-result-state');
                    if (marker) marker.textContent = selected ? 'Dipilih' : '';
                });
                showCustomerFeedback('Data pelanggan lama siap digunakan untuk transaksi ini.');
            };

            const renderCustomerResults = (customers) => {
                if (!customerResults) return;
                customerResults.replaceChildren();
                customerResults.hidden = false;

                const heading = document.createElement('p');
                heading.className = 'existing-customer-results-heading';
                heading.textContent = `Hasil Pencarian (${customers.length} pelanggan${customers.length === 10 ? ', menampilkan 10 teratas' : ''})`;
                customerResults.appendChild(heading);

                customers.forEach((customer) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'existing-customer-result';
                    button.dataset.customerId = customer.id;
                    button.setAttribute('aria-pressed', String(selectedCustomerId?.value === String(customer.id)));

                    const personIcon = document.createElement('span');
                    personIcon.className = 'existing-customer-result-icon';
                    personIcon.setAttribute('aria-hidden', 'true');
                    personIcon.innerHTML = '<svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.5"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>';

                    const details = document.createElement('span');
                    details.className = 'existing-customer-result-details';
                    const name = document.createElement('strong');
                    name.textContent = customer.nama;
                    const meta = document.createElement('small');
                    meta.textContent = `${customer.telepon}  |  ${customer.jumlah_transaksi} Transaksi`;
                    details.append(name, meta);

                    const state = document.createElement('span');
                    state.className = 'existing-customer-result-state';
                    const isSelected = selectedCustomerId?.value === String(customer.id);
                    state.textContent = isSelected ? 'Dipilih' : '';

                    button.append(personIcon, details, state);
                    button.addEventListener('click', () => chooseExistingCustomer(customer));
                    customerResults.appendChild(button);
                });
            };

            const searchExistingCustomers = async () => {
                const query = customerSearchInput?.value.trim() || '';
                if (!query) {
                    showCustomerFeedback('Masukkan nama pelanggan atau empat digit terakhir nomor telepon.', true);
                    customerSearchInput?.focus();
                    return;
                }

                const searchUrl = form?.dataset.customerSearchUrl;
                if (!searchUrl) return;
                if (customerSearchButton) customerSearchButton.disabled = true;
                if (customerResults) {
                    customerResults.replaceChildren();
                    customerResults.hidden = true;
                }
                showCustomerFeedback('Mencari data pelanggan...');

                try {
                    const url = new URL(searchUrl, window.location.origin);
                    url.searchParams.set('q', query);
                    const response = await fetch(url, { headers: { Accept: 'application/json' } });
                    const data = await response.json();
                    if (!response.ok) {
                        showCustomerFeedback(data.message || 'Pencarian pelanggan gagal. Periksa kata kunci yang dimasukkan.', true);
                        return;
                    }
                    if (!data.results?.length) {
                        showCustomerFeedback('Pelanggan tidak ditemukan. Periksa kembali nama atau empat digit nomor telepon.');
                        return;
                    }
                    showCustomerFeedback('Pilih salah satu pelanggan dari hasil pencarian.');
                    renderCustomerResults(data.results);
                } catch {
                    showCustomerFeedback('Pencarian belum dapat dilakukan. Silakan coba kembali.', true);
                } finally {
                    if (customerSearchButton) customerSearchButton.disabled = false;
                }
            };

            customerSearchButton?.addEventListener('click', searchExistingCustomers);
            customerSearchInput?.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    searchExistingCustomers();
                }
            });
            customerSearchInput?.addEventListener('input', () => {
                if (selectedCustomerId?.value) {
                    clearSelectedCustomer();
                    showCustomerFeedback('Cari dan pilih kembali pelanggan yang sesuai.');
                }
            });

            form?.addEventListener('submit', (event) => {
                const isExisting = form.querySelector('[name="jenis_pelanggan"]:checked')?.value === 'lama';
                if (isExisting && !selectedCustomerId?.value) {
                    event.preventDefault();
                    showCustomerFeedback('Cari dan pilih pelanggan lama terlebih dahulu.', true);
                    document.dispatchEvent(new CustomEvent('admin:required-missing', { detail: { focusTarget: customerSearchInput } }));
                }
            });
            form?.addEventListener('reset', () => window.setTimeout(() => syncCustomerMode(), 0));

            const entryDate = form?.querySelector('[name="tanggal_masuk"][data-date-display]');
            const entryPicker = entryDate?.closest('[data-date-field]')?.querySelector('[data-date-picker]');
            const estimateDate = form?.querySelector('[name="estimasi_selesai"][data-date-display]');
            const estimatePicker = estimateDate?.closest('[data-date-field]')?.querySelector('[data-date-picker]');
            const dateFormat = entryDate?.dataset.dateFormat || 'd/m/Y';
            const isEditing = @json($editing);
            let automaticEstimate = true;
            const needsInitialEstimate = !isEditing && !estimateDate?.value.trim();

            const getSelectedDuration = () => {
                const service = form?.querySelector('#laundryService');
                if (service) return Number(service.selectedOptions[0]?.dataset.duration) || 0;

                const durations = Array.from(form?.querySelectorAll('#cucianDetailRows select[name="paket_laundry_id[]"]') || [])
                    .map((select) => Number(select.selectedOptions[0]?.dataset.duration) || 0);
                return Math.max(0, ...durations);
            };

            const updateEstimate = () => {
                if (!automaticEstimate || !entryDate || !estimateDate || !estimatePicker) return;
                const dateTools = window.GLaundryDateFormat;
                const entryIso = dateTools?.parseDisplayDate(entryDate.value, dateFormat) || entryPicker?.value;
                const duration = getSelectedDuration();
                if (!entryIso || !duration || !dateTools) return;

                const [year, month, day] = entryIso.split('-').map(Number);
                const estimated = new Date(Date.UTC(year, month - 1, day + duration));
                const estimateIso = `${estimated.getUTCFullYear()}-${String(estimated.getUTCMonth() + 1).padStart(2, '0')}-${String(estimated.getUTCDate()).padStart(2, '0')}`;

                estimatePicker.value = estimateIso;
                estimatePicker.min = entryIso;
                estimateDate.value = dateTools.formatIsoDate(estimateIso, dateFormat);
            };

            entryDate?.addEventListener('date:change', (event) => {
                if (estimatePicker) estimatePicker.min = event.detail.isoDate;
                updateEstimate();
            });
            estimateDate?.addEventListener('input', () => { automaticEstimate = false; });
            estimatePicker?.addEventListener('change', () => { automaticEstimate = false; });
            document.addEventListener('DOMContentLoaded', () => {
                if (needsInitialEstimate) updateEstimate();
            }, { once: true });
            form?.addEventListener('reset', () => {
                automaticEstimate = true;
                window.setTimeout(updateEstimate, 0);
            });

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
                service.addEventListener('change', () => {
                    updateServiceFields();
                    automaticEstimate = true;
                    updateEstimate();
                });
                updateServiceFields();
            }

            const detailRows = document.getElementById('cucianDetailRows');
            detailRows?.addEventListener('change', (event) => {
                if (!event.target.matches('select[name="paket_laundry_id[]"]')) return;
                automaticEstimate = true;
                updateEstimate();
            });

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

            const paymentStatus = document.getElementById('statusPembayaran');
            const paymentMethod = document.getElementById('metodePembayaran');
            const paymentMethodHelp = document.getElementById('paymentMethodHelp');
            const paymentRequiredMark = document.querySelector('.payment-required-mark');
            const syncPaymentMethod = () => {
                if (!paymentStatus || !paymentMethod) return;
                const alreadyPaid = paymentStatus.value === 'lunas';
                paymentMethod.disabled = !alreadyPaid;
                paymentMethod.required = alreadyPaid;
                if (!alreadyPaid) paymentMethod.value = '';
                if (paymentRequiredMark) paymentRequiredMark.hidden = !alreadyPaid;
                if (paymentMethodHelp) {
                    paymentMethodHelp.textContent = alreadyPaid
                        ? 'Pilih Tunai / Cash atau QRIS untuk pembayaran lunas.'
                        : 'Metode pembayaran tidak diperlukan; pelanggan membayar saat pengambilan.';
                }
            };
            paymentStatus?.addEventListener('change', syncPaymentMethod);
            syncPaymentMethod();
            document.querySelector('.cucian-form-card, .cucian-figma-card')?.addEventListener('reset', () => {
                window.setTimeout(syncPaymentMethod, 0);
            });

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
    <script src="{{ asset('js/date-format.js') }}?v={{ filemtime(public_path('js/date-format.js')) }}" defer></script>
</x-layouts::app>
