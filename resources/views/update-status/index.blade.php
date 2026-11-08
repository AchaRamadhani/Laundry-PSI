<x-layouts::app :title="'Update Status'">
    @php
        $statusFilterOptions = \App\Support\LaundryOptions::STATUSES;
        $statusKeyTerpilih = $terpilih ? match (strtolower($terpilih->status_cucian)) {
            'antrian' => 'antrean', 'proses' => 'diproses', 'pengeringan' => 'dikeringkan',
            default => strtolower($terpilih->status_cucian),
        } : null;
    @endphp

    <div class="update-status-page">
        <section class="update-status-heading">
            <div>
                <h1>Update Status</h1>
                <p class="update-status-lead">Perbarui tahapan proses cucian dan simpan riwayat perubahan status.</p>
                <p class="update-status-caption">Perubahan status dilakukan di halaman ini agar riwayat proses cucian tercatat dengan jelas.</p>
                <div class="update-status-note"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5m0-8h.01"/></svg><span>Nota digital dibuat otomatis saat data cucian disimpan. Kirim ulang nota akan membuka WhatsApp dengan pesan nota yang sudah terisi otomatis.</span></div>
            </div>
        </section>

        @if (session('success'))<div class="cucian-alert success" role="status">{{ session('success') }}</div>@endif
        @if ($errors->any())<div class="cucian-alert error" role="alert">{{ $errors->first() }}</div>@endif

        <section class="status-count-grid" aria-label="Jumlah cucian berdasarkan status">
            @foreach ($statusCards as $card)
                <article class="status-count-card status-count-{{ $card->key }}">
                    <span class="status-count-icon" style="--step-color: {{ $card->warna }}">
                        @switch($card->key)
                            @case('antrean')<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M3 20v-2a6 6 0 0 1 12 0v2zM16 15a5 5 0 0 1 5 5"/></svg>@break
                            @case('diproses')<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m19 13 .1-2 2-1.4-2-3.5-2.4 1a8 8 0 0 0-1.7-1L14.5 4h-5L9 7a8 8 0 0 0-1.7 1l-2.4-1-2 3.5L5 12v2l-2 1.5 2 3.5 2.4-1a8 8 0 0 0 1.7 1l.5 3h5l.5-3a8 8 0 0 0 1.7-1l2.4 1 2-3.5z"/><circle cx="12" cy="13" r="3"/></svg>@break
                            @case('dicuci')<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M4 8h16M8 5h.01M11 5h.01"/><circle cx="12" cy="14" r="4"/><path d="M10 14c1-1 3-1 4 0"/></svg>@break
                            @case('dikeringkan')<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12c-2-6 4-8 4-4s-4 5-4 4Zm0 0c6-2 8 4 4 4s-5-4-4-4Zm0 0c2 6-4 8-4 4s4-5 4-4Zm0 0c-6 2-8-4-4-4s5 4 4 4Z"/><circle cx="12" cy="12" r="1"/></svg>@break
                            @case('disetrika')<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 16h15l2 3H4zM7 16l2-7h7a4 4 0 0 1 4 4v3M10 6h5"/></svg>@break
                            @case('selesai')<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m7.5 12 3 3 6-6"/></svg>@break
                            @default<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 10h14v11H5zM9 10V7a3 3 0 0 1 6 0v3"/><path d="M12 14v3"/></svg>
                        @endswitch
                    </span>
                    <div><span>{{ $card->label }}</span><strong>{{ number_format($card->jumlah, 0, ',', '.') }}</strong><small>Cucian</small></div>
                </article>
            @endforeach
        </section>

        <form class="update-status-filters" action="{{ route('update-status.index') }}" method="GET">
            <label class="update-status-search"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m16 16 5 5"/></svg><input type="search" name="search" value="{{ $search }}" placeholder="Cari No nota atau nama pelanggan..." aria-label="Cari cucian"></label>
            <label class="sr-only" for="status-filter-current">Semua status</label>
            <select id="status-filter-current" name="status"><option value="">Semua Status</option>@foreach ($statusFilterOptions as $key => $label)<option value="{{ $key }}" @selected($statusFilter === $key)>{{ $label }}</option>@endforeach</select>
            <label class="sr-only" for="status-filter-service">Semua layanan</label>
            <select id="status-filter-service" name="layanan"><option value="">Semua Layanan</option>@foreach (\App\Support\LaundryOptions::SERVICES as $serviceKey => $service)<option value="{{ $serviceKey }}" @selected((string) $paketId === $serviceKey)>{{ $service['nama'] }}</option>@endforeach</select>
            <label class="update-status-month"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg><select name="bulan" aria-label="Pilih bulan">@foreach ($namaBulan as $nomor => $nama)<option value="{{ $nomor }}" @selected((int) $bulan === (int) $nomor)>{{ $nama }}</option>@endforeach</select></label>
            <input type="hidden" name="tahun" value="{{ $tahun }}">
            <button type="submit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16l-6.5 7.5V19l-3 1v-7.5z"/></svg>Filter</button>
            @if ($terpilih)<input type="hidden" name="nota" value="{{ $terpilih->no_nota }}">@endif
        </form>

        <section class="update-status-content-grid">
            <article class="cucian-panel active-laundry-panel">
                <div class="update-panel-heading"><div><h2>Data Cucian Aktif</h2><p>Simpan perubahan status agar progres dan riwayat ikut diperbarui.</p></div><span>{{ number_format($cucian->total(), 0, ',', '.') }} nota</span></div>
                <div class="update-status-table-scroll">
                    <table class="update-status-table">
                        <thead><tr><th>No. Nota</th><th>Pelanggan</th><th>Layanan</th><th>Status Saat Ini</th><th>Pilih Status Baru</th><th>Catatan Singkat</th><th>Aksi</th></tr></thead>
                        <tbody>
                            @forelse ($cucian as $item)
                                @php
                                    $currentKey = match (strtolower($item->status_cucian)) { 'antrian' => 'antrean', 'proses' => 'diproses', 'pengeringan' => 'dikeringkan', default => strtolower($item->status_cucian) };
                                    $currentLabel = $statusFilterOptions[$currentKey] ?? ucfirst($currentKey);
                                    $currentColor = collect($statusCards)->firstWhere('key', $currentKey)?->warna ?? '#9aa4b4';
                                    $services = $item->detailTransaksi->map(fn ($detail) => $detail->paketLaundry?->nama_paket)->filter()->unique()->implode(', ');
                                    $formId = 'status-form-' . $loop->index;
                                    $formAction = route('update-status.update', array_merge(['transaksi' => $item->no_nota], request()->query(), ['nota' => $item->no_nota]));
                                @endphp
                                <tr class="{{ $terpilih?->no_nota === $item->no_nota ? 'selected' : '' }}">
                                    <td class="update-nota-cell">
                                        <form id="{{ $formId }}" action="{{ $formAction }}" method="POST" hidden>@csrf @method('PUT')</form>
                                        <a href="{{ route('update-status.index', array_merge(request()->query(), ['nota' => $item->no_nota])) }}">{{ $item->no_nota }}</a>
                                    </td>
                                    <td>{{ $item->pelanggan?->nama_pelanggan ?? 'Pelanggan dihapus' }}</td>
                                    <td class="update-service-cell" title="{{ $services }}">{{ $services ?: '—' }}</td>
                                    <td><span class="update-status-badge" style="--status-color: {{ $currentColor }}"><i></i>{{ $currentLabel }}</span></td>
                                    <td><label class="sr-only" for="new-status-{{ $loop->index }}">Pilih status baru untuk {{ $item->no_nota }}</label><select class="new-status-select" id="new-status-{{ $loop->index }}" name="status_baru" form="{{ $formId }}" required>@foreach ($tahapan as $step)<option value="{{ $step->key }}" @selected($currentKey === $step->key)>{{ $step->label }}</option>@endforeach</select></td>
                                    <td><input class="status-note-input" type="text" name="catatan_status" form="{{ $formId }}" maxlength="500" placeholder="Masukkan catatan..."></td>
                                    <td><button class="save-status-button" type="submit" form="{{ $formId }}">Simpan</button></td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="update-status-empty">Tidak ada data cucian pada periode dan filter ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="update-status-pagination">
                    <span>Menampilkan {{ $cucian->firstItem() ?? 0 }}–{{ $cucian->lastItem() ?? 0 }} dari {{ number_format($cucian->total(), 0, ',', '.') }} data</span>
                    {{ $cucian->onEachSide(1)->links() }}
                </div>
            </article>

            <aside class="update-status-side">
                @if ($terpilih)
                    <article class="cucian-panel selected-progress-panel">
                        <h2>Riwayat &amp; Progres Status</h2>
                        <div class="selected-customer-info">
                            <div><small>No. Nota</small><strong>{{ $terpilih->no_nota }}</strong></div>
                            <div><small>Pelanggan</small><strong>{{ $terpilih->pelanggan?->nama_pelanggan ?? 'Pelanggan dihapus' }}</strong></div>
                        </div>
                        <div class="selected-order-meta">
                            <div><small>Layanan</small><strong>{{ $terpilih->detailTransaksi->map(fn ($detail) => $detail->paketLaundry?->nama_paket)->filter()->unique()->implode(', ') ?: '—' }}</strong></div>
                            <div><small>Tanggal Masuk</small><strong>{{ \Carbon\Carbon::parse($terpilih->tanggal_masuk)->format('d M Y') }}{{ $terpilih->created_at ? ', ' . $terpilih->created_at->format('H.i') : '' }}</strong></div>
                            <div><small>Estimasi Selesai</small><strong>{{ $terpilih->estimasi_selesai ? \Carbon\Carbon::parse($terpilih->estimasi_selesai)->format('d M Y, H.i') : 'Belum ditentukan' }}</strong></div>
                        </div>
                        <div class="status-progress-track">
                            @foreach ($tahapan as $index => $step)
                                <div class="progress-step {{ $index < $tahapAktif ? 'complete' : '' }} {{ $index === $tahapAktif ? 'current' : '' }}" style="--step-color: {{ $step->warna }}">
                                    <span class="progress-step-icon">
                                        @if ($index < $tahapAktif)<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
                                        @elseif ($step->key === 'antrean')<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M3 20v-2a6 6 0 0 1 12 0v2zM16 14a5 5 0 0 1 5 5"/></svg>
                                        @elseif ($step->key === 'diproses')<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m19 13 .1-2 2-1.4-2-3.5-2.4 1a8 8 0 0 0-1.7-1L14.5 4h-5L9 7a8 8 0 0 0-1.7 1l-2.4-1-2 3.5L5 12v2l-2 1.5 2 3.5 2.4-1a8 8 0 0 0 1.7 1l.5 3h5l.5-3a8 8 0 0 0 1.7-1l2.4 1 2-3.5z"/></svg>
                                        @elseif ($step->key === 'dicuci')<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="3" width="16" height="18" rx="2"/><circle cx="12" cy="14" r="4"/><path d="M4 8h16"/></svg>
                                        @elseif ($step->key === 'dikeringkan')<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12c-2-6 4-8 4-4s-4 5-4 4Zm0 0c6-2 8 4 4 4s-5-4-4-4Zm0 0c2 6-4 8-4 4s4-5 4-4Zm0 0c-6 2-8-4-4-4s5 4 4 4Z"/></svg>
                                        @elseif ($step->key === 'disetrika')<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 16h15l2 3H4zM7 16l2-7h7a4 4 0 0 1 4 4"/></svg>
                                        @elseif ($step->key === 'selesai')<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m7.5 12 3 3 6-6"/></svg>
                                        @else<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 10h14v11H5zM9 10V7a3 3 0 0 1 6 0v3"/></svg>@endif
                                    </span>
                                    <strong>{{ $step->label }}</strong>
                                    <small>{{ $tahapTanggal[$step->key] ?? '—' }}</small>
                                </div>
                            @endforeach
                        </div>
                    </article>
                @else
                    <article class="cucian-panel selected-progress-panel empty-progress-panel"><h2>Riwayat &amp; Progres Status</h2><p>Pilih salah satu nota untuk melihat progres status cucian.</p></article>
                @endif

                <article class="cucian-panel latest-status-panel">
                    <div class="latest-status-heading"><h2>Riwayat Terbaru</h2><a href="{{ route('update-status.history') }}">Lihat semua</a></div>
                    <div class="latest-status-list">
                        @forelse ($riwayatTerbaru as $item)
                            @php
                                $latestKey = match (strtolower($item->status)) { 'antrian' => 'antrean', 'proses' => 'diproses', 'pengeringan' => 'dikeringkan', default => strtolower($item->status) };
                                $latestLabel = $statusFilterOptions[$latestKey] ?? ucfirst($latestKey);
                                $latestColor = collect($statusCards)->firstWhere('key', $latestKey)?->warna ?? '#9aa4b4';
                            @endphp
                            <div class="latest-status-item">
                                <i style="--step-color: {{ $latestColor }}"></i>
                                <div><strong>{{ $latestLabel }}</strong><small>{{ $item->catatan_status ?: 'Perubahan status dicatat.' }}</small></div>
                                <span>{{ $item->admin_name ?: 'Admin' }}<small>{{ \Carbon\Carbon::parse($item->waktu_status)->format('d M Y, H.i') }}</small></span>
                            </div>
                        @empty
                            <p class="update-status-empty-note">Belum ada riwayat perubahan status.</p>
                        @endforelse
                    </div>
                    <div class="status-history-reminder"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5m0-8h.01"/></svg>Pastikan setiap perubahan status disimpan agar riwayat proses cucian tercatat dengan akurat.</div>
                </article>
            </aside>
        </section>
    </div>
</x-layouts::app>
