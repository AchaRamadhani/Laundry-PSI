(() => {
    const tokens = (format) => format.match(/d|m|Y/g) || ['d', 'm', 'Y'];
    const splitDate = (value) => value.trim().split(/[/-]/);
    const indonesianMonths = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    const englishMonths = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    const toIsoDate = (year, month, day) => {
        year = Number(year);
        month = Number(month);
        day = Number(day);
        if (!Number.isInteger(day) || !Number.isInteger(month) || !Number.isInteger(year) || year < 1000 || year > 9999) return null;

        const date = new Date(Date.UTC(year, month - 1, day));
        if (date.getUTCFullYear() !== year || date.getUTCMonth() !== month - 1 || date.getUTCDate() !== day) return null;

        return `${String(year).padStart(4, '0')}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    };

    const parseDisplayDate = (value, format) => {
        const trimmed = value.trim();

        if (format === 'd F Y') {
            const match = trimmed.match(/^(\d{1,2})\s+([\p{L}]+)\s+(\d{4})$/u);
            if (!match) return null;
            const month = indonesianMonths.findIndex((name) => name.toLocaleLowerCase('id') === match[2].toLocaleLowerCase('id')) + 1;
            return month ? toIsoDate(match[3], month, match[1]) : null;
        }

        if (format === 'F d, Y') {
            const match = trimmed.match(/^([A-Za-z]+)\s+(\d{1,2}),\s*(\d{4})$/);
            if (!match) return null;
            const month = englishMonths.findIndex((name) => name.toLowerCase() === match[1].toLowerCase()) + 1;
            return month ? toIsoDate(match[3], month, match[2]) : null;
        }

        const parts = splitDate(value);
        const order = tokens(format);
        if (parts.length !== 3 || order.length !== 3) return null;

        const values = Object.fromEntries(order.map((token, index) => [token, parts[index]]));
        return toIsoDate(values.Y, values.m, values.d);
    };

    const formatIsoDate = (isoDate, format) => {
        const [year, month, day] = isoDate.split('-');
        const monthIndex = Number(month) - 1;
        if (format === 'd F Y') return `${day} ${indonesianMonths[monthIndex]} ${year}`;
        if (format === 'F d, Y') return `${englishMonths[monthIndex]} ${Number(day)}, ${year}`;
        const values = { d: day, m: month, Y: year };
        return tokens(format).map((token) => values[token]).join(format.includes('/') ? '/' : '-');
    };

    window.GLaundryDateFormat = { parseDisplayDate, formatIsoDate };

    document.querySelectorAll('[data-date-display]').forEach((display) => {
        const field = display.closest('[data-date-field]');
        const picker = field?.querySelector('[data-date-picker]');
        const trigger = field?.querySelector('[data-date-picker-trigger]');
        const format = display.dataset.dateFormat || 'd/m/Y';

        if (!picker) return;
        display.setAttribute('inputmode', format.includes('F') ? 'text' : 'numeric');

        const syncPicker = () => {
            const isoDate = parseDisplayDate(display.value, format);
            if (isoDate) picker.value = isoDate;
            else if (display.value.trim() === '') picker.value = '';
            return isoDate;
        };

        const notifyDateChange = (isoDate) => {
            if (isoDate) display.dispatchEvent(new CustomEvent('date:change', { detail: { isoDate } }));
        };

        display.addEventListener('blur', () => {
            const isoDate = parseDisplayDate(display.value, format);
            if (isoDate) display.value = formatIsoDate(isoDate, format);
            syncPicker();
            notifyDateChange(isoDate);
        });

        display.addEventListener('input', () => notifyDateChange(syncPicker()));
        picker.addEventListener('change', () => {
            if (picker.value) {
                display.value = formatIsoDate(picker.value, format);
                notifyDateChange(picker.value);
            }
        });

        trigger?.addEventListener('click', () => {
            if (typeof picker.showPicker === 'function') picker.showPicker();
            else picker.click();
        });
    });
})();
