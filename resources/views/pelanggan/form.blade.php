<x-layouts::app :title="$pelanggan ? 'Ubah Pelanggan' : 'Tambah Pelanggan'">
    @php($editing = $pelanggan !== null)

    <div class="pelanggan-form-overlay">
        <section class="pelanggan-form-dialog" role="dialog" aria-modal="true" aria-labelledby="pelanggan-form-title">
            <header class="pelanggan-form-dialog-header">
                <h1 id="pelanggan-form-title">{{ $editing ? 'Ubah Data Pelanggan' : 'Tambah Data Pelanggan' }}</h1>
                <a class="pelanggan-form-close" href="{{ route('pelanggan.index') }}" aria-label="Tutup form pelanggan">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </a>
            </header>

            <form class="pelanggan-form-card" data-create-form="{{ $editing ? 'false' : 'true' }}" action="{{ $editing ? route('pelanggan.update', $pelanggan) : route('pelanggan.store') }}" method="POST">
                @csrf
                @if ($editing) @method('PUT') @endif

                <div class="cucian-alert error pelanggan-form-errors" id="pelanggan-form-errors" role="alert" aria-live="assertive" @if (!$errors->any()) hidden @endif>
                    @if ($errors->any())
                        <strong>Periksa kembali data yang dimasukkan.</strong>
                        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    @endif
                </div>

                <div class="pelanggan-form-body">
                    <label class="pelanggan-field pelanggan-id-field">
                        <span>ID Pelanggan</span>
                        <input type="text" value="{{ $editing ? ('PLG-GL-' . str_pad((string) $pelanggan->id, 3, '0', STR_PAD_LEFT)) : '' }}" placeholder="Akan dibuat otomatis oleh sistem" disabled>
                        <small>{{ $editing ? 'ID pelanggan tidak dapat diubah.' : 'ID pelanggan akan dibuat secara otomatis setelah data disimpan.' }}</small>
                    </label>

                    <div class="pelanggan-form-fields">
                        <label class="pelanggan-field">
                            <span>Nama Pelanggan <b>*</b></span>
                            <input type="text" name="nama_pelanggan" value="{{ old('nama_pelanggan', $pelanggan?->nama_pelanggan) }}" maxlength="255" placeholder="Masukkan nama pelanggan" required autofocus>
                        </label>
                        <label class="pelanggan-field">
                            <span>No telepon <b>*</b></span>
                            <input type="tel" name="no_telp" value="{{ old('no_telp', $pelanggan?->no_telp) }}" inputmode="numeric" pattern="[0-9]{10,13}" minlength="10" maxlength="13" placeholder="Masukkan nomor telepon" required>
                        </label>
                    </div>
                </div>

                <footer class="pelanggan-form-footer">
                    @unless ($editing)
                        <button class="pelanggan-form-clear" type="reset">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 17 5-5 6 6-5 5H3zM8 12l4-4 6 6-4 4M14 6l4-4 4 4-4 4M13 7l4 4M5 19l2 2m8-12 4 4"/></svg>
                            Bersihkan
                        </button>
                    @endunless
                    <div class="pelanggan-form-actions">
                        <a class="pelanggan-form-cancel" href="{{ route('pelanggan.index') }}">Batal</a>
                        <button class="pelanggan-add-button pelanggan-form-submit" type="submit">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 3h12l4 4v14H3V3h2zM7 3v6h10V3M7 21v-8h10v8"/></svg>
                            {{ $editing ? 'Simpan Perubahan' : 'Simpan Data' }}
                        </button>
                    </div>
                </footer>
            </form>

        </section>
    </div>

    <div class="pelanggan-save-success-backdrop" id="pelanggan-save-success" role="status" aria-live="polite" hidden>
        <section class="pelanggan-save-success-card" aria-label="Data pelanggan berhasil disimpan">
            <span class="pelanggan-save-success-icon" aria-hidden="true"><span><svg viewBox="0 0 48 48"><path d="m11 25 9 9 18-21"/></svg></span></span>
            <h2>Data Berhasil Disimpan</h2>
            <p>ID Pelanggan: <strong id="pelanggan-save-success-code"></strong></p>
        </section>
    </div>

    <script>
        (() => {
            const form = document.querySelector('.pelanggan-form-card[data-create-form="true"]');
            if (!form) return;

            const submitButton = form.querySelector('.pelanggan-form-submit');
            const errorBox = document.getElementById('pelanggan-form-errors');
            const notification = document.getElementById('pelanggan-save-success');
            const codeLabel = document.getElementById('pelanggan-save-success-code');
            const customersUrl = @json(route('pelanggan.index'));

            const showErrors = (messages) => {
                errorBox.replaceChildren();
                const heading = document.createElement('strong');
                heading.textContent = 'Periksa kembali data yang dimasukkan.';
                errorBox.append(heading);

                const list = document.createElement('ul');
                messages.forEach((message) => {
                    const item = document.createElement('li');
                    item.textContent = message;
                    list.append(item);
                });
                errorBox.append(list);
                errorBox.hidden = false;
            };

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                errorBox.hidden = true;
                submitButton.disabled = true;

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    let result = {};
                    try { result = await response.json(); } catch {}

                    if (response.status === 422 && result.errors) {
                        showErrors(Object.values(result.errors).flat());
                        errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        form.querySelector('[name="nama_pelanggan"]')?.focus();
                        return;
                    }

                    if (!response.ok || !result.customer_code) {
                        throw new Error(result.message || 'Data pelanggan belum berhasil disimpan.');
                    }

                    codeLabel.textContent = result.customer_code;
                    notification.hidden = false;

                    let dismissed = false;
                    const fadeDuration = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 1000;
                    const dismiss = () => {
                        if (dismissed) return;
                        dismissed = true;
                        notification.classList.add('is-closing');
                        window.setTimeout(() => window.location.assign(result.redirect_url || customersUrl), fadeDuration);
                    };

                    notification.addEventListener('click', (closeEvent) => {
                        if (closeEvent.target === notification) dismiss();
                    });
                    const handleEscape = (keyEvent) => {
                        if (keyEvent.key !== 'Escape') return;
                        document.removeEventListener('keydown', handleEscape);
                        dismiss();
                    };
                    document.addEventListener('keydown', handleEscape);
                    window.setTimeout(dismiss, 3500);
                } catch (error) {
                    showErrors([error.message || 'Terjadi kesalahan saat menyimpan data. Coba lagi.']);
                } finally {
                    if (notification.hidden) submitButton.disabled = false;
                }
            });
        })();
    </script>
</x-layouts::app>
