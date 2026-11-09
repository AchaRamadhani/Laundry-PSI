<x-layouts::app :title="$paket ? 'Ubah Paket Laundry' : 'Tambah Paket Laundry'">
    @php($editing = $paket !== null)

    <div class="paket-modal-backdrop">
        <section class="paket-modal" role="dialog" aria-modal="true" aria-labelledby="paketModalTitle" tabindex="-1">
            <header class="paket-modal-header">
                <div>
                    <h1 id="paketModalTitle">{{ $editing ? 'Ubah Paket Laundry' : 'Tambah Paket Laundry' }}</h1>
                    <p>Masukkan data paket laundry dengan lengkap</p>
                </div>
                <a class="paket-modal-close" href="{{ route('paket-laundry.index') }}" aria-label="Tutup">×</a>
            </header>

            @if ($errors->any())
                <div class="paket-modal-errors" role="alert">
                    <strong>Periksa kembali data paket.</strong>
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form class="paket-modal-form" action="{{ $editing ? route('paket-laundry.update', $paket) : route('paket-laundry.store') }}" method="POST">
                @csrf
                @if ($editing) @method('PUT') @endif

                <label class="paket-modal-field">
                    <span>Nama Paket <b>*</b></span>
                    <input type="text" name="nama_paket" value="{{ old('nama_paket', $paket?->nama_paket) }}" maxlength="255" placeholder="Contoh : Cuci Kering Premium" required autofocus>
                </label>

                <label class="paket-modal-field paket-description-field">
                    <span>Deskripsi paket</span>
                    <textarea name="deskripsi" id="paketDescription" rows="5" maxlength="200" placeholder="Contoh : Paket ini mencakup pencucian, pengeringan, dan pewangian untuk hasil yang bersih dan wangi.">{{ old('deskripsi', $paket?->deskripsi) }}</textarea>
                    <small class="paket-description-count"><span id="paketDescriptionCount">{{ mb_strlen((string) old('deskripsi', $paket?->deskripsi)) }}</span> / 200</small>
                </label>

                <div class="paket-modal-fields-row">
                    <label class="paket-modal-field">
                        <span>Harga Mulai <b>*</b></span>
                        <span class="paket-price-input"><span>Rp</span><input type="number" name="harga_mulai" value="{{ old('harga_mulai', $paket?->harga_mulai) }}" min="0" max="9999999999" step="1" placeholder="Contoh : 15000" required></span>
                    </label>
                    <label class="paket-modal-field">
                        <span>Durasi <b>*</b></span>
                        <select name="durasi" required>
                            <option value="">Pilih durasi</option>
                            @for ($hari = 1; $hari <= 7; $hari++)
                                <option value="{{ $hari }}" @selected((string) old('durasi', $paket?->durasi) === (string) $hari)>{{ $hari }} hari</option>
                            @endfor
                        </select>
                    </label>
                </div>

                <footer class="paket-modal-footer">
                    <button class="paket-clear-button" type="reset"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 20h18M5 17 14 4l5 3-9 13-5 1zM12 7l5 3"/><path d="m4 4 2 2m-2-2 2-2"/></svg>Bersihkan</button>
                    <div>
                        <a class="paket-cancel-button" href="{{ route('paket-laundry.index') }}">Batal</a>
                        <button class="paket-save-button" type="submit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 3h12l4 4v14H3V3z"/><path d="M7 3v6h10V3M7 21v-8h10v8"/></svg>{{ $editing ? 'Simpan Perubahan' : 'Simpan Data' }}</button>
                    </div>
                </footer>
            </form>
        </section>
    </div>

    <script>
        (() => {
            const description = document.getElementById('paketDescription');
            const count = document.getElementById('paketDescriptionCount');
            const modal = document.querySelector('.paket-modal');

            if (description && count) {
                const updateCount = () => { count.textContent = description.value.length; };
                description.addEventListener('input', updateCount);
                description.form?.addEventListener('reset', () => window.setTimeout(updateCount));
            }

            modal?.focus();
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') window.location.href = @json(route('paket-laundry.index'));
            });
        })();
    </script>
</x-layouts::app>
