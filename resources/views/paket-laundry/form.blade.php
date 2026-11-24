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
                    <div class="paket-modal-field">
                        <label id="paketDurationLabel" for="paketDurationTrigger">Durasi <b>*</b></label>
                        @php($durasiTerpilih = (string) old('durasi', $paket?->durasi ?? ''))
                        <div class="paket-duration-control" data-paket-duration>
                            <input type="hidden" name="durasi" value="{{ $durasiTerpilih }}">
                            <button
                                class="paket-duration-trigger"
                                id="paketDurationTrigger"
                                type="button"
                                role="combobox"
                                aria-labelledby="paketDurationLabel"
                                aria-haspopup="listbox"
                                aria-expanded="false"
                                aria-controls="paketDurationOptions"
                            >
                                <span data-paket-duration-value>{{ $durasiTerpilih !== '' ? $durasiTerpilih . ' hari' : 'Pilih durasi' }}</span>
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                            </button>
                            <div class="paket-duration-menu" id="paketDurationOptions" role="listbox" aria-labelledby="paketDurationLabel" hidden>
                                @for ($hari = 1; $hari <= 7; $hari++)
                                    <button
                                        class="paket-duration-option"
                                        type="button"
                                        role="option"
                                        data-value="{{ $hari }}"
                                        aria-selected="{{ $durasiTerpilih === (string) $hari ? 'true' : 'false' }}"
                                    >{{ $hari }} hari</button>
                                @endfor
                            </div>
                            <small class="paket-duration-error" data-paket-duration-error role="alert" hidden>Pilih durasi terlebih dahulu.</small>
                        </div>
                    </div>
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
            const duration = document.querySelector('[data-paket-duration]');

            if (description && count) {
                const updateCount = () => { count.textContent = description.value.length; };
                description.addEventListener('input', updateCount);
                description.form?.addEventListener('reset', () => window.setTimeout(updateCount));
            }

            if (duration) {
                const input = duration.querySelector('input[name="durasi"]');
                const trigger = duration.querySelector('.paket-duration-trigger');
                const valueLabel = duration.querySelector('[data-paket-duration-value]');
                const menu = duration.querySelector('.paket-duration-menu');
                const error = duration.querySelector('[data-paket-duration-error]');
                const options = Array.from(duration.querySelectorAll('.paket-duration-option'));
                const placeholder = 'Pilih durasi';

                document.body.append(menu);

                const syncSelection = (value) => {
                    input.value = value;
                    const selected = options.find((option) => option.dataset.value === value);
                    valueLabel.textContent = selected?.textContent.trim() || placeholder;
                    options.forEach((option) => option.setAttribute('aria-selected', String(option === selected)));
                    trigger.removeAttribute('aria-invalid');
                    error.hidden = true;
                };

                const positionMenu = () => {
                    const rect = trigger.getBoundingClientRect();
                    const availableBelow = Math.max(0, window.innerHeight - rect.bottom - 8);
                    const availableAbove = Math.max(0, rect.top - 8);
                    const openAbove = availableBelow < 220 && availableAbove > availableBelow;
                    const available = openAbove ? availableAbove : availableBelow;
                    const maxHeight = Math.max(80, Math.min(252, available));

                    menu.style.maxHeight = `${maxHeight}px`;
                    menu.style.left = `${Math.max(8, Math.min(rect.left, window.innerWidth - rect.width - 8))}px`;
                    menu.style.width = `${rect.width}px`;
                    menu.style.top = openAbove
                        ? `${Math.max(8, rect.top - Math.min(menu.scrollHeight, maxHeight) - 6)}px`
                        : `${Math.min(window.innerHeight - maxHeight - 8, rect.bottom + 6)}px`;
                };

                const closeMenu = (restoreFocus = false) => {
                    menu.hidden = true;
                    trigger.setAttribute('aria-expanded', 'false');
                    if (restoreFocus) trigger.focus();
                };

                const openMenu = (focusLast = false) => {
                    menu.hidden = false;
                    trigger.setAttribute('aria-expanded', 'true');
                    positionMenu();
                    (focusLast ? options.at(-1) : options.find((option) => option.dataset.value === input.value) || options[0])?.focus();
                };

                trigger.addEventListener('click', () => {
                    if (menu.hidden) openMenu();
                    else closeMenu();
                });

                trigger.addEventListener('keydown', (event) => {
                    if (['ArrowDown', 'Enter', ' '].includes(event.key)) {
                        event.preventDefault();
                        openMenu();
                    } else if (event.key === 'ArrowUp') {
                        event.preventDefault();
                        openMenu(true);
                    }
                });

                menu.addEventListener('click', (event) => {
                    const option = event.target.closest('.paket-duration-option');
                    if (!option) return;
                    syncSelection(option.dataset.value);
                    closeMenu(true);
                });

                menu.addEventListener('keydown', (event) => {
                    const currentIndex = options.indexOf(document.activeElement);
                    let nextIndex = currentIndex;

                    if (event.key === 'ArrowDown') nextIndex = Math.min(options.length - 1, currentIndex + 1);
                    else if (event.key === 'ArrowUp') nextIndex = Math.max(0, currentIndex - 1);
                    else if (event.key === 'Home') nextIndex = 0;
                    else if (event.key === 'End') nextIndex = options.length - 1;
                    else if (event.key === 'Escape') {
                        event.preventDefault();
                        event.stopPropagation();
                        closeMenu(true);
                        return;
                    } else if (event.key === 'Tab') {
                        closeMenu();
                        return;
                    } else {
                        return;
                    }

                    event.preventDefault();
                    options[nextIndex]?.focus();
                });

                document.addEventListener('pointerdown', (event) => {
                    if (!duration.contains(event.target) && !menu.contains(event.target)) closeMenu();
                });

                window.addEventListener('resize', () => {
                    if (!menu.hidden) positionMenu();
                });
                window.addEventListener('scroll', () => {
                    if (!menu.hidden) positionMenu();
                }, true);

                duration.closest('form')?.addEventListener('submit', (event) => {
                    if (input.value) return;
                    event.preventDefault();
                    trigger.setAttribute('aria-invalid', 'true');
                    error.hidden = false;
                    document.dispatchEvent(new CustomEvent('admin:required-missing', { detail: { focusTarget: trigger } }));
                });

                duration.closest('form')?.addEventListener('reset', () => window.setTimeout(() => {
                    syncSelection(input.defaultValue);
                    closeMenu();
                }));
            }

            modal?.focus();
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') window.location.href = @json(route('paket-laundry.index'));
            });
        })();
    </script>
</x-layouts::app>
