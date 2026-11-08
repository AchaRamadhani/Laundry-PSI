<x-layouts::app :title="'Pengaturan'">
    <div class="settings-page">
        <header class="settings-heading">
            <h1>Pengaturan</h1>
            <p>Atur informasi akun admin dan identitas G'Laundry</p>
        </header>

        @if (session('status'))
            <div class="settings-flash" role="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="settings-errors" role="alert">
                <strong>Periksa kembali isian berikut:</strong>
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form class="settings-form" method="POST" action="{{ route('settings.update') }}" id="adminSettingsForm">
            @csrf
            @method('PUT')

            <div class="settings-main-grid">
                <section class="settings-profile-card" aria-labelledby="settings-profile-title">
                    <h2 id="settings-profile-title">Profil admin</h2>
                    <div class="settings-profile-identity">
                        <span class="settings-large-avatar" aria-hidden="true">
                            <svg viewBox="0 0 96 96"><circle cx="48" cy="30" r="19"/><path d="M10 87c3-21 18-33 38-33s35 12 38 33c-10 9-23 13-38 13S20 96 10 87Z"/></svg>
                        </span>
                        <strong>{{ $admin->name }}</strong>
                        <span>Administrator</span>
                    </div>

                    <div class="settings-account-info">
                        <h3>Informasi Akun</h3>
                        <label for="settings-username">Username</label>
                        <div class="settings-readonly-field"><span aria-hidden="true">♙</span><input id="settings-username" value="{{ $admin->username }}" readonly></div>
                        <label for="settings-email">Email</label>
                        <div class="settings-readonly-field"><span aria-hidden="true">✉</span><input id="settings-email" value="{{ $admin->email }}" readonly></div>
                    </div>
                </section>

                <section class="settings-business" aria-labelledby="settings-business-title">
                    <h2 id="settings-business-title">Informasi Laundry</h2>
                    <div class="settings-business-grid">
                        <label class="settings-field">
                            <span>Informasi Laundry</span>
                            <input name="laundry_name" value="{{ old('laundry_name', $settings->laundry_name ?? '') }}" placeholder="Nama laundry" maxlength="120">
                        </label>
                        <label class="settings-field">
                            <span>Nomor WhatsApp</span>
                            <div class="settings-input-icon"><span class="settings-whatsapp-icon"><x-icons.whatsapp /></span><input name="whatsapp_number" value="{{ old('whatsapp_number', $settings->whatsapp_number ?? '') }}" placeholder="08xx-xxxx-xxxx" maxlength="30" inputmode="tel"></div>
                        </label>
                        <label class="settings-field settings-field-wide">
                            <span>Alamat</span>
                            <input name="address" value="{{ old('address', $settings->address ?? '') }}" placeholder="Alamat laundry" maxlength="500">
                        </label>
                        <div class="settings-hours settings-field-wide">
                            <span class="settings-field-label">Jadwal Operasional</span>
                            <div class="settings-hours-row settings-hours-schedule">
                                <label class="settings-hour-field"><span>Jam Buka</span><input type="time" name="opening_time" value="{{ old('opening_time', substr($settings->opening_time ?: '07:00', 0, 5)) }}" required></label>
                                <label class="settings-hour-field"><span>Mulai Istirahat</span><input type="time" name="break_start" value="{{ old('break_start', substr($settings->break_start ?: '12:00', 0, 5)) }}" required></label>
                                <label class="settings-hour-field"><span>Selesai Istirahat</span><input type="time" name="break_end" value="{{ old('break_end', substr($settings->break_end ?: '13:00', 0, 5)) }}" required></label>
                                <label class="settings-hour-field"><span>Jam Tutup</span><input type="time" name="closing_time" value="{{ old('closing_time', substr($settings->closing_time ?: '21:00', 0, 5)) }}" required></label>
                            </div>
                        </div>
                        <label class="settings-field settings-field-wide">
                            <span>Pesan Otomatis WhatsApp saat Status “Selesai”</span>
                            <textarea name="completion_message" maxlength="3000" rows="4" placeholder="Tulis pesan otomatis. Gunakan {nama_pelanggan} dan {no_nota} sebagai placeholder.">{{ old('completion_message', $settings->completion_message ?? '') }}</textarea>
                            <small>Gunakan {nama_pelanggan} dan {no_nota} sebagai placeholder dinamis.</small>
                        </label>
                    </div>
                </section>
            </div>

            <div class="settings-bottom-grid">
                <section class="settings-panel settings-preferences" aria-labelledby="settings-preferences-title">
                    <h2 id="settings-preferences-title">Preferensi Sistem</h2>
                    <input type="hidden" name="notify_browser" value="0">
                    <label class="settings-preference-row">
                        <span class="settings-preference-icon" aria-hidden="true">♧</span>
                        <span class="settings-preference-copy"><strong>Notifikasi Browser</strong><small>Tampilkan notifikasi pada browser</small></span>
                        <span class="settings-switch"><input type="checkbox" name="notify_browser" value="1" @checked(old('notify_browser', $settings->notify_browser ?? true))><i></i></span>
                    </label>
                    <input type="hidden" name="notify_message" value="0">
                    <label class="settings-preference-row">
                        <span class="settings-preference-icon settings-message-icon" aria-hidden="true">▤</span>
                        <span class="settings-preference-copy"><strong>Notifikasi Pesan</strong><small>Terima notifikasi pesan dari sistem</small></span>
                        <span class="settings-switch"><input type="checkbox" name="notify_message" value="1" @checked(old('notify_message', $settings->notify_message ?? true))><i></i></span>
                    </label>
                    <label class="settings-preference-row">
                        <span class="settings-preference-icon settings-calendar-icon" aria-hidden="true">▣</span>
                        <span class="settings-preference-copy"><strong>Format Tanggal</strong><small>Pilih format tanggal yang digunakan</small></span>
                        <select class="settings-date-select" name="date_format">
                            <option value="d-m-Y" @selected(old('date_format', $settings->date_format ?? 'd-m-Y') === 'd-m-Y')>DD MM YYYY</option>
                            <option value="Y-m-d" @selected(old('date_format', $settings->date_format ?? 'd-m-Y') === 'Y-m-d')>YYYY MM DD</option>
                            <option value="d/m/Y" @selected(old('date_format', $settings->date_format ?? 'd-m-Y') === 'd/m/Y')>DD/MM/YYYY</option>
                        </select>
                    </label>
                    <input type="hidden" name="confirm_logout" value="0">
                    <label class="settings-preference-row">
                        <span class="settings-preference-icon settings-shield-icon" aria-hidden="true">⬡</span>
                        <span class="settings-preference-copy"><strong>Tampilkan Konfirmasi Logout</strong><small>Tampilkan dialog konfirmasi saat logout</small></span>
                        <span class="settings-switch"><input type="checkbox" name="confirm_logout" value="1" @checked(old('confirm_logout', $settings->confirm_logout ?? true))><i></i></span>
                    </label>
                </section>

                <section class="settings-panel settings-security" aria-labelledby="settings-security-title">
                    <h2 id="settings-security-title">Keamanan Akun</h2>
                    <p>Ubah Password</p>
                    <small>Kosongkan jika tidak ingin mengubah password</small>
                    <label class="settings-field settings-password-field">
                        <span>Password Saat Ini <em>(wajib jika password baru diisi)</em></span>
                        <div class="settings-password-wrap"><input name="current_password" type="password" autocomplete="current-password" placeholder="Masukkan password saat ini"><button type="button" data-toggle-password aria-label="Tampilkan password">◉</button></div>
                    </label>
                    <label class="settings-field settings-password-field">
                        <span>Password Baru</span>
                        <div class="settings-password-wrap"><input name="new_password" type="password" autocomplete="new-password" placeholder="Masukkan password baru"><button type="button" data-toggle-password aria-label="Tampilkan password">◉</button></div>
                    </label>
                    <label class="settings-field settings-password-field">
                        <span>Konfirmasi Password</span>
                        <div class="settings-password-wrap"><input name="new_password_confirmation" type="password" autocomplete="new-password" placeholder="Konfirmasi password baru"><button type="button" data-toggle-password aria-label="Tampilkan password">◉</button></div>
                    </label>
                </section>
            </div>

            <div class="settings-submit-row">
                <button class="settings-save-button" type="submit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 3h13l4 4v14H3V3zM7 3v6h10V3M7 21v-8h10v8"/></svg>Simpan Perubahan</button>
            </div>
        </form>
    </div>

    <script>
        (() => {
            document.querySelectorAll('[data-toggle-password]').forEach((button) => {
                button.addEventListener('click', () => {
                    const input = button.previousElementSibling;
                    input.type = input.type === 'password' ? 'text' : 'password';
                    button.setAttribute('aria-label', input.type === 'password' ? 'Tampilkan password' : 'Sembunyikan password');
                });
            });

            try {
                localStorage.setItem('g-laundry-confirm-logout', @json((bool) $settings->confirm_logout ? 'true' : 'false'));
            } catch {}
        })();
    </script>
</x-layouts::app>
