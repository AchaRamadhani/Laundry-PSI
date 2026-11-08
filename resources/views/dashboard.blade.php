<x-layouts::app :title="__('Dashboard')">
    @php($totalLayanan = $dataLayanan->sum('jumlah'))

    <div class="dashboard-page">
        <section class="dashboard-intro">
            <div>
                <p class="dashboard-kicker">Ringkasan Operasional</p>
                <h1>Dashboard</h1>
                <p class="dashboard-welcome">Selamat datang kembali, {{ auth()->user()->name ?? 'Admin' }}! <span aria-hidden="true">👋</span></p>
                <p class="dashboard-caption">Berikut adalah ringkasan data operasional G'Laundry</p>
            </div>

            <form class="dashboard-period" method="GET" action="{{ route('dashboard') }}">
                <label class="sr-only" for="bulan">Pilih bulan</label>
                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg>
                <select id="bulan" name="bulan" onchange="this.form.submit()">
                    @foreach ($namaBulan as $nomor => $nama)
                        <option value="{{ $nomor }}" {{ $bulan == $nomor ? 'selected' : '' }}>{{ $nama }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="tahun" value="{{ $tahun }}">
                <input type="hidden" name="status" value="{{ $statusFilter }}">
                <svg class="period-chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
            </form>
        </section>

        <section class="dashboard-metrics" aria-label="Ringkasan bulan {{ $namaBulan[$bulan] }}">
            <article class="metric-card">
                <span class="metric-icon metric-blue" aria-hidden="true">
                    <svg viewBox="0 0 32 32"><circle cx="16" cy="10" r="5"/><path d="M6 27v-2a10 10 0 0 1 20 0v2zM5 12a4 4 0 1 0 0 8m22-8a4 4 0 1 1 0 8"/></svg>
                </span>
                <div class="metric-copy"><span>Total Pelanggan</span><strong>{{ number_format($totalPelanggan, 0, ',', '.') }}</strong><small>Terdaftar</small></div>
            </article>

            <article class="metric-card">
                <span class="metric-icon metric-green" aria-hidden="true">
                    <svg viewBox="0 0 32 32"><path d="M9 10h14l2 17H7zM12 10V7a4 4 0 0 1 8 0v3"/><path d="M13 17h6"/></svg>
                </span>
                <div class="metric-copy"><span>Total Transaksi</span><strong>{{ number_format($totalTransaksi, 0, ',', '.') }}</strong><small>{{ $namaBulan[$bulan] }} {{ $tahun }}</small></div>
            </article>

            <article class="metric-card">
                <span class="metric-icon metric-purple" aria-hidden="true">
                    <svg viewBox="0 0 32 32"><path d="M7 8h19v20H7a3 3 0 0 1-3-3V9a3 3 0 0 1 3-3h16"/><path d="M20 15h9v7h-9a3.5 3.5 0 0 1 0-7Z"/><circle cx="22" cy="18.5" r=".8"/></svg>
                </span>
                <div class="metric-copy"><span>Total Pendapatan</span><strong class="metric-currency">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</strong><small>{{ $namaBulan[$bulan] }} {{ $tahun }}</small></div>
            </article>

            <article class="metric-card">
                <span class="metric-icon metric-orange" aria-hidden="true">
                    <svg viewBox="0 0 32 32"><path d="M10 5h12v4H10zM8 8H6v20h20V8h-2M11 14h10M11 19h10M11 24h6"/></svg>
                </span>
                <div class="metric-copy"><span>Pesanan Berjalan</span><strong>{{ number_format($totalPesanan, 0, ',', '.') }}</strong><small>Belum selesai atau diambil</small></div>
            </article>
        </section>

        <section class="dashboard-analytics" aria-label="Grafik operasional">
            <article class="dashboard-panel revenue-panel">
                <div class="panel-heading">
                    <div><h2>Grafik Pendapatan</h2><p>Pergerakan pendapatan harian</p></div>
                    <form class="chart-period chart-period-select" method="GET" action="{{ route('dashboard') }}">
                        <label class="sr-only" for="dashboard-revenue-month">Pilih bulan grafik pendapatan</label>
                        <select id="dashboard-revenue-month" name="bulan" onchange="this.form.submit()">
                            @foreach ($namaBulan as $nomor => $nama)
                                <option value="{{ $nomor }}" @selected((int) $bulan === (int) $nomor)>{{ $nama }}</option>
                            @endforeach
                        </select>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                        <input type="hidden" name="tahun" value="{{ $tahun }}">
                        <input type="hidden" name="status" value="{{ $statusFilter }}">
                    </form>
                </div>
                <div class="revenue-chart-wrap"><canvas id="pendapatanChart" aria-label="Grafik pendapatan harian"></canvas></div>
                <div class="revenue-summary">
                    <div class="summary-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18M7 15h3"/></svg></div>
                    <div class="summary-value"><span>Total Pendapatan</span><strong>Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</strong></div>
                    <div class="summary-divider"></div>
                    <div class="summary-value"><span>Rata-rata per Hari</span><strong>Rp {{ number_format($rataRataHarian, 0, ',', '.') }}</strong></div>
                </div>
            </article>

            <article class="dashboard-panel status-panel">
                <div class="panel-heading">
                    <div><h2>Status Cucian</h2><p>Ringkasan status pesanan</p></div>
                    <form class="chart-period dashboard-status-filter" method="GET" action="{{ route('dashboard') }}">
                        <label class="sr-only" for="dashboard-status-filter">Pilih status cucian</label>
                        <select id="dashboard-status-filter" name="status" onchange="this.form.submit()">
                            <option value="" @selected($statusFilter === '')>Semua Status</option>
                            @foreach (\App\Support\LaundryOptions::STATUSES as $key => $label)
                                <option value="{{ $key }}" @selected($statusFilter === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                        <input type="hidden" name="bulan" value="{{ $bulan }}">
                        <input type="hidden" name="tahun" value="{{ $tahun }}">
                    </form>
                </div>
                <div class="status-chart-layout">
                    <div class="status-donut-column">
                        <div class="status-donut-wrap">
                            <canvas id="statusChart" aria-label="Diagram status cucian"></canvas>
                            <div class="donut-center"><span>Total</span><strong>{{ number_format($totalStatus, 0, ',', '.') }}</strong><small>Pesanan</small></div>
                        </div>
                        <div class="status-total-label"><span>Total Pesanan</span><strong>{{ number_format($totalStatus, 0, ',', '.') }}</strong></div>
                    </div>

                    <div class="status-legend">
                        @forelse ($dataStatus as $status)
                            <div class="status-legend-row">
                                <span class="legend-name"><i style="--status-color: {{ $status->warna }}"></i>{{ $status->label }}</span>
                                <span class="legend-value">{{ $status->jumlah }} <small>({{ $totalStatus ? number_format(($status->jumlah / $totalStatus) * 100, 0) : 0 }}%)</small></span>
                            </div>
                        @empty
                            <p class="empty-dashboard-data">Belum ada data status cucian.</p>
                        @endforelse
                    </div>
                </div>
            </article>
        </section>

        <section class="dashboard-details" aria-label="Pesanan dan layanan">
            <article class="dashboard-panel orders-panel">
                <div class="panel-heading">
                    <div><h2>Pesanan Terbaru</h2><p>Transaksi terbaru pada {{ $namaBulan[$bulan] }}</p></div>
                    <a class="panel-action" href="{{ route('dashboard') }}">Lihat Semua</a>
                </div>
                <div class="table-scroll">
                    <table class="dashboard-table orders-table">
                        <thead><tr><th>No. Nota</th><th>Pelanggan</th><th>Tanggal Masuk</th><th>Layanan</th><th>Status</th><th>Total</th></tr></thead>
                        <tbody>
                            @forelse ($transaksiTerbaru as $transaksi)
                                <tr>
                                    <td class="receipt-cell">{{ $transaksi->no_nota }}</td>
                                    <td>{{ $transaksi->nama_pelanggan ?? 'Pelanggan tidak ditemukan' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($transaksi->tanggal_masuk)->format('d M Y') }}</td>
                                    <td class="service-cell" title="{{ $transaksi->nama_layanan }}">{{ $transaksi->nama_layanan }}</td>
                                    <td><span class="order-status" style="--status-color: {{ $transaksi->status_warna }}">{{ $transaksi->status_label }}</span></td>
                                    <td class="total-cell">Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td class="empty-table-row" colspan="6">Belum ada transaksi pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="dashboard-panel services-panel">
                <div class="panel-heading">
                    <div><h2>Transaksi Per Layanan</h2><p>Paket laundry yang paling banyak dipilih</p></div>
                    <form class="chart-period chart-period-select" method="GET" action="{{ route('dashboard') }}">
                        <label class="sr-only" for="dashboard-service-month">Pilih bulan transaksi per layanan</label>
                        <select id="dashboard-service-month" name="bulan" onchange="this.form.submit()">
                            @foreach ($namaBulan as $nomor => $nama)
                                <option value="{{ $nomor }}" @selected((int) $bulan === (int) $nomor)>{{ $nama }}</option>
                            @endforeach
                        </select>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                        <input type="hidden" name="tahun" value="{{ $tahun }}">
                        <input type="hidden" name="status" value="{{ $statusFilter }}">
                    </form>
                </div>
                <div class="table-scroll">
                    <table class="dashboard-table service-table">
                        <thead><tr><th>Layanan</th><th>Jumlah Transaksi</th><th>Persentase</th></tr></thead>
                        <tbody>
                            @forelse ($dataLayanan as $layanan)
                                @php($persentase = $totalLayanan ? ($layanan->jumlah / $totalLayanan) * 100 : 0)
                                <tr>
                                    <td>{{ $layanan->nama_paket }}</td>
                                    <td>{{ number_format($layanan->jumlah, 0, ',', '.') }}</td>
                                    <td>
                                        <div class="service-share">
                                            <span class="service-bar"><i style="width: {{ max(4, $persentase) }}%"></i></span>
                                            <small>{{ number_format($persentase, 1, ',', '.') }}%</small>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td class="empty-table-row" colspan="3">Belum ada transaksi layanan pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const revenueLabels = @json($pendapatanHarian->pluck('hari')->values());
            const revenueValues = @json($pendapatanHarian->pluck('pendapatan')->values());
            const statusLabels = @json($dataStatus->pluck('label')->values());
            const statusValues = @json($dataStatus->pluck('jumlah')->values());
            const statusColors = @json($dataStatus->pluck('warna')->values());

            const revenueCanvas = document.getElementById('pendapatanChart');
            if (revenueCanvas && window.Chart) {
                new Chart(revenueCanvas, {
                    type: 'line',
                    data: {
                        labels: revenueLabels,
                        datasets: [{
                            data: revenueValues,
                            borderColor: '#337fe8',
                            backgroundColor: 'rgba(51, 127, 232, .08)',
                            fill: false,
                            tension: .25,
                            borderWidth: 1.5,
                            pointRadius: 2.5,
                            pointHoverRadius: 4,
                            pointBackgroundColor: '#29337c',
                        }],
                    },
                    options: {
                        maintainAspectRatio: false,
                        responsive: true,
                        plugins: {
                            legend: { display: false },
                            tooltip: { callbacks: { label: context => `Rp ${new Intl.NumberFormat('id-ID').format(context.raw)}` } },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { maxTicksLimit: 4, callback: value => new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(value) },
                                grid: { color: '#e9edf4' },
                            },
                            x: { ticks: { maxTicksLimit: 5 }, grid: { display: false } },
                        },
                    },
                });
            }

            const statusCanvas = document.getElementById('statusChart');
            if (statusCanvas && window.Chart) {
                new Chart(statusCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: statusLabels,
                        datasets: [{ data: statusValues, backgroundColor: statusColors, borderWidth: 0, hoverOffset: 3 }],
                    },
                    options: {
                        maintainAspectRatio: false,
                        responsive: true,
                        cutout: '68%',
                        plugins: { legend: { display: false }, tooltip: { callbacks: { label: context => `${context.label}: ${context.raw}` } } },
                    },
                });
            }
        });
    </script>
</x-layouts::app>
