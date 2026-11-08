<x-layouts::app :title="$paket ? 'Ubah Paket Laundry' : 'Tambah Paket Laundry'">
    @php($editing = $paket !== null)
    <div class="paket-page paket-form-page">
        <section class="paket-heading paket-form-heading">
            <div><h1>{{ $editing ? 'Ubah Paket Laundry' : 'Tambah Paket Laundry' }}</h1><p>{{ $editing ? 'Perbarui data paket yang tersimpan.' : 'Masukkan informasi paket dan harga layanan yang sebenarnya.' }}</p></div>
            <a class="paket-secondary-button" href="{{ route('paket-laundry.index') }}">← Kembali ke Paket Laundry</a>
        </section>

        @if ($errors->any())<div class="cucian-alert error" role="alert"><strong>Periksa kembali data paket.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <form class="paket-form-card" action="{{ $editing ? route('paket-laundry.update', $paket) : route('paket-laundry.store') }}" method="POST">
            @csrf
            @if ($editing) @method('PUT') @endif
            <div class="paket-form-intro"><span><svg viewBox="0 0 32 32" aria-hidden="true"><path d="m16 3 11 6v14l-11 6-11-6V9zM5 9l11 6 11-6M16 15v14"/></svg></span><div><h2>Informasi Paket</h2><p>Data yang disimpan di sini akan menjadi sumber daftar paket laundry.</p></div></div>

            <div class="paket-form-grid">
                <label class="paket-field"><span>Nama Paket <b>*</b></span><input type="text" name="nama_paket" value="{{ old('nama_paket', $paket?->nama_paket) }}" maxlength="255" placeholder="Masukkan nama layanan" required autofocus></label>
                <label class="paket-field"><span>Kategori <b>*</b></span><select name="kategori" required><option value="">Pilih kategori</option>@foreach ($kategoriOptions as $option)<option value="{{ $option }}" @selected(old('kategori', $paket?->kategori) === $option)>{{ $option }}</option>@endforeach</select></label>
                <label class="paket-field"><span>Harga Mulai (Rp) <b>*</b></span><input type="number" name="harga_mulai" value="{{ old('harga_mulai', $paket?->harga_mulai) }}" min="0" max="9999999999.99" step="0.01" placeholder="Masukkan harga layanan" required><small>Harga ini dipakai saat menghitung subtotal transaksi.</small></label>
                <label class="paket-field"><span>Durasi (hari) <b>*</b></span><input type="number" name="durasi" value="{{ old('durasi', $paket?->durasi) }}" min="0" max="365" step="1" placeholder="Masukkan durasi" required></label>
                <label class="paket-field"><span>Status Paket <b>*</b></span><select name="status" required><option value="aktif" @selected(strtolower(old('status', $paket?->status ?? 'aktif')) === 'aktif')>Aktif</option><option value="nonaktif" @selected(strtolower(old('status', $paket?->status ?? 'aktif')) === 'nonaktif')>Nonaktif</option></select><small>Paket nonaktif tidak dapat dipilih untuk transaksi baru.</small></label>
                <label class="paket-field full"><span>Deskripsi Layanan</span><textarea name="deskripsi" rows="4" maxlength="2000" placeholder="Jelaskan layanan yang termasuk dalam paket ini">{{ old('deskripsi', $paket?->deskripsi) }}</textarea></label>
            </div>

            <div class="paket-form-footer"><p><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5m0-8h.01"/></svg>Harga dan durasi yang dimasukkan akan digunakan pada transaksi baru.</p><div><a class="paket-secondary-button" href="{{ route('paket-laundry.index') }}">Batal</a><button class="paket-add-button" type="submit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>{{ $editing ? 'Simpan Perubahan' : 'Simpan Paket' }}</button></div></div>
        </form>
    </div>
</x-layouts::app>
