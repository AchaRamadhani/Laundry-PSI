<x-layouts::app :title="$pelanggan ? 'Ubah Pelanggan' : 'Tambah Pelanggan'">
    @php($editing = $pelanggan !== null)
    <div class="pelanggan-page pelanggan-form-page">
        <section class="pelanggan-heading pelanggan-form-heading">
            <div><h1>{{ $editing ? 'Ubah Pelanggan' : 'Tambah Pelanggan' }}</h1><p>{{ $editing ? 'Perbarui informasi kontak pelanggan.' : "Tambahkan informasi pelanggan ke G'Laundry." }}</p></div>
            <a class="pelanggan-secondary-button" href="{{ route('pelanggan.index') }}">← Kembali ke Pelanggan</a>
        </section>

        @if ($errors->any())<div class="cucian-alert error" role="alert"><strong>Periksa kembali data yang dimasukkan.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <form class="pelanggan-form-card" action="{{ $editing ? route('pelanggan.update', $pelanggan) : route('pelanggan.store') }}" method="POST">
            @csrf
            @if ($editing) @method('PUT') @endif
            <div class="pelanggan-form-intro"><span class="pelanggan-form-icon"><svg viewBox="0 0 32 32" aria-hidden="true"><circle cx="12" cy="11" r="5"/><path d="M3 28v-3a9 9 0 0 1 18 0v3zM22 18v10m-5-5h10"/></svg></span><div><h2>Informasi Pelanggan</h2><p>Nama dan nomor telepon digunakan untuk identifikasi serta pengiriman nota.</p></div></div>
            <label class="pelanggan-field"><span>Nama Pelanggan <b>*</b></span><input type="text" name="nama_pelanggan" value="{{ old('nama_pelanggan', $pelanggan?->nama_pelanggan) }}" maxlength="255" placeholder="Masukkan nama lengkap" required autofocus></label>
            <label class="pelanggan-field"><span>Nomor Telepon <b>*</b></span><input type="tel" name="no_telp" value="{{ old('no_telp', $pelanggan?->no_telp) }}" inputmode="numeric" pattern="[0-9]{10,13}" minlength="10" maxlength="13" placeholder="Contoh: 081234567890" required><small>Masukkan 10 sampai 13 angka tanpa spasi.</small></label>
            <div class="pelanggan-form-footer"><p><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5m0-8h.01"/></svg>Data ini akan tersimpan di daftar pelanggan.</p><div><a class="pelanggan-secondary-button" href="{{ route('pelanggan.index') }}">Batal</a><button class="pelanggan-add-button" type="submit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>{{ $editing ? 'Simpan Perubahan' : 'Simpan Pelanggan' }}</button></div></div>
        </form>
    </div>
</x-layouts::app>
