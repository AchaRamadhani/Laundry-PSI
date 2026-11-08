<footer class="site-footer public-footer">
    <div class="public-footer-main">
        <div class="public-footer-brand">
            <a class="public-footer-logo" href="{{ route('home') }}" aria-label="G'Laundry, Beranda">
                <img src="{{ asset('images/logo-g-laundry-footer.png') }}" alt="G'Laundry — Rapi, Bersih, Wangi">
            </a>
            <p>Perawatan pakaian yang bersih, rapi, dan nyaman untuk keseharian Anda.</p>
        </div>

        <div class="public-footer-operations" aria-label="Informasi operasional G'Laundry">
            <div class="public-footer-item">
                <span class="public-footer-icon whatsapp-icon-wrap"><x-icons.whatsapp /></span>
                <span class="public-footer-copy"><small>WhatsApp</small><strong>{{ $whatsappDisplay }}</strong><small>Respons selama jam operasional</small></span>
            </div>

            <div class="public-footer-item">
                <span class="public-footer-icon"><x-icons.clock /></span>
                <span class="public-footer-copy"><small>Jam Operasional</small><strong>{{ $openingTime }}–{{ $closingTime }} WITA</strong><small>Istirahat {{ $breakStartTime }}–{{ $breakEndTime }}</small></span>
            </div>

            <div class="public-footer-item public-footer-status" data-operational-hours data-opening-time="{{ $openingTime }}" data-break-start-time="{{ $breakStartTime }}" data-break-end-time="{{ $breakEndTime }}" data-closing-time="{{ $closingTime }}" data-timezone="{{ $operationalTimezone }}">
                <span class="public-footer-icon public-footer-state-icon status-{{ \Illuminate\Support\Str::slug($todayStatus) }}"><x-icons.operational-status :status="$todayStatus" /></span>
                <span class="public-footer-copy"><small>Status Operasional Hari Ini</small><strong class="public-footer-state status-{{ \Illuminate\Support\Str::slug($todayStatus) }}" data-operational-status role="status">{{ $todayStatus }}</strong><small data-status-message>{{ $todayStatus === 'Buka' ? 'Kami siap melayani Anda' : ($todayStatus === 'Istirahat' ? 'Kami sedang istirahat' : 'Kami kembali melayani saat jam buka') }}</small></span>
            </div>

            <div class="public-footer-item public-footer-address">
                <span class="public-footer-icon"><x-icons.map-pin /></span>
                <span class="public-footer-copy"><small>Alamat</small><strong>{{ $address }}</strong></span>
            </div>
        </div>
    </div>

    <div class="public-footer-copyright">&copy; {{ date('Y') }} G'Laundry. All rights reserved.</div>
</footer>

<script>
    (() => {
        const scheduleBlocks = document.querySelectorAll('[data-operational-hours]');
        if (!scheduleBlocks.length) return;

        const getMakassarTime = (timeZone) => {
            const parts = new Intl.DateTimeFormat('en-GB', {
                timeZone,
                hour: '2-digit',
                minute: '2-digit',
                hourCycle: 'h23',
            }).formatToParts(new Date());
            const hour = parts.find((part) => part.type === 'hour')?.value ?? '00';
            const minute = parts.find((part) => part.type === 'minute')?.value ?? '00';
            return `${hour}:${minute}`;
        };

        const updateOperationalStatus = () => {
            scheduleBlocks.forEach((block) => {
                const time = getMakassarTime(block.dataset.timezone || 'Asia/Makassar');
                const { openingTime, breakStartTime, breakEndTime, closingTime } = block.dataset;
                const status = time < openingTime || time >= closingTime
                    ? 'Tutup'
                    : time >= breakStartTime && time < breakEndTime
                        ? 'Istirahat'
                        : 'Buka';
                const slug = status.toLowerCase();
                const statusLabel = block.querySelector('[data-operational-status]');
                const statusMessage = block.querySelector('[data-status-message]');
                const icon = block.querySelector('.public-footer-state-icon svg');

                block.classList.remove('status-buka', 'status-istirahat', 'status-tutup');
                block.classList.add(`status-${slug}`);
                statusLabel?.classList.remove('status-buka', 'status-istirahat', 'status-tutup');
                statusLabel?.classList.add(`status-${slug}`);
                if (statusLabel) statusLabel.textContent = status;
                if (statusMessage) {
                    statusMessage.textContent = status === 'Buka'
                        ? 'Kami siap melayani Anda'
                        : status === 'Istirahat'
                            ? 'Kami sedang istirahat'
                            : 'Kami kembali melayani saat jam buka';
                }

                const iconPath = status === 'Buka'
                    ? 'm8.5 12 2.2 2.2 4.8-4.8'
                    : status === 'Istirahat'
                        ? 'M10 9v6m4-6v6'
                        : 'm9 9 6 6m0-6-6 6';
                icon?.querySelector('path')?.setAttribute('d', iconPath);
                icon?.parentElement?.classList.remove('status-buka', 'status-istirahat', 'status-tutup');
                icon?.parentElement?.classList.add(`status-${slug}`);
            });
        };

        updateOperationalStatus();
        window.setInterval(updateOperationalStatus, 30000);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) updateOperationalStatus();
        });
    })();
</script>
