(() => {
    const fieldSelector = [
        '.cucian-figma-field',
        '.cucian-field',
        '.pelanggan-field',
        '.paket-modal-field',
        '.settings-hour-field',
        '.settings-field',
        '.field',
    ].join(', ');
    let nextErrorId = 0;
    let requiredPopup = null;
    let requiredPopupFocusTarget = null;

    const getField = (control) => control.closest(fieldSelector) || control.closest('td') || control.parentElement;

    const getFieldLabel = (control, field) => {
        const directLabel = field?.querySelector(':scope > span:first-child, :scope > label:first-child');
        const associatedLabel = control.id
            ? Array.from(document.querySelectorAll('label[for]')).find((label) => label.htmlFor === control.id)
            : null;
        const source = directLabel || associatedLabel;
        const label = source?.textContent.replace(/\*/g, '').replace(/\s+/g, ' ').trim();

        return label || ({
            nama_pelanggan: 'Nama Pelanggan',
            no_telp: 'Nomor Telepon',
            pelanggan_id: 'Pelanggan',
            tanggal_masuk: 'Tanggal Masuk',
            estimasi_selesai: 'Estimasi Selesai',
            status_pembayaran: 'Status Pembayaran',
            metode_pembayaran: 'Metode Pembayaran',
            layanan: 'Jenis Layanan',
            berat_qty: 'Berat / Jumlah',
            total_harga: 'Total Harga',
            nama_paket: 'Nama Paket',
            harga_mulai: 'Harga Mulai',
            opening_time: 'Jam Buka',
            break_start: 'Mulai Istirahat',
            break_end: 'Selesai Istirahat',
            closing_time: 'Jam Tutup',
        })[control.name] || 'Kolom ini';
    };

    const getValidationMessage = (control, label) => {
        if (control.validity.valueMissing) {
            return control.tagName === 'SELECT'
                ? `Silakan pilih ${label.toLowerCase()}.`
                : `${label} wajib diisi.`;
        }

        if (control.validity.typeMismatch) {
            if (control.type === 'email') return 'Masukkan alamat email dengan format yang benar.';
            if (control.type === 'url') return 'Masukkan tautan dengan format yang benar.';
            return `Periksa kembali format ${label.toLowerCase()}.`;
        }

        if (control.validity.patternMismatch && control.name === 'no_telp') {
            const phoneDigits = control.value.replace(/\D/g, '');
            if (phoneDigits.length > 13) return 'Nomor telepon maksimal 13 digit.';
            if (phoneDigits.length < 10) return 'Nomor telepon harus terdiri dari 10 sampai 13 digit dan diawali 08.';
            if (!phoneDigits.startsWith('08')) return 'Nomor telepon harus diawali 08.';
            return 'Nomor telepon harus diawali 08 dan terdiri dari 10 sampai 13 digit.';
        }

        if (control.validity.patternMismatch) {
            return `Format ${label.toLowerCase()} belum sesuai.`;
        }

        if (control.validity.tooShort) {
            return `${label} minimal ${control.minLength} karakter.`;
        }

        if (control.validity.rangeUnderflow) {
            if (control.name === 'berat_qty' && Number(control.min) >= 3) return 'Berat cucian minimal 3 kg.';
            return `${label} minimal ${control.min}.`;
        }

        if (control.validity.rangeOverflow) return `${label} maksimal ${control.max}.`;
        if (control.validity.stepMismatch) return `Periksa kembali nilai ${label.toLowerCase()}.`;

        return `${label} belum valid.`;
    };

    const addDescriptionId = (control, id) => {
        const ids = new Set((control.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean));
        ids.add(id);
        control.setAttribute('aria-describedby', Array.from(ids).join(' '));
    };

    const removeDescriptionId = (control, id) => {
        const ids = (control.getAttribute('aria-describedby') || '').split(/\s+/).filter((item) => item && item !== id);
        if (ids.length) control.setAttribute('aria-describedby', ids.join(' '));
        else control.removeAttribute('aria-describedby');
    };

    const clearError = (control) => {
        const errorId = control.dataset.validationErrorId;
        if (!errorId) return;

        document.getElementById(errorId)?.remove();
        removeDescriptionId(control, errorId);
        control.removeAttribute('aria-invalid');
        delete control.dataset.validationErrorId;
    };

    const showError = (control) => {
        const field = getField(control);
        const label = getFieldLabel(control, field);
        const errorId = control.dataset.validationErrorId || `admin-field-error-${++nextErrorId}`;
        let message = document.getElementById(errorId);

        if (!message) {
            message = document.createElement('span');
            message.className = 'field-validation-error';
            message.id = errorId;
            message.setAttribute('role', 'alert');
            field?.append(message);
        }

        message.textContent = getValidationMessage(control, label);
        control.dataset.validationErrorId = errorId;
        control.setAttribute('aria-invalid', 'true');
        addDescriptionId(control, errorId);
    };

    const getRequiredControls = (form) => Array.from(form.elements).filter((control) =>
        control instanceof HTMLElement
        && 'required' in control
        && control.required
        && !control.disabled
        && control.willValidate
    );

    const closeRequiredPopup = () => {
        if (!requiredPopup) return;
        document.removeEventListener('keydown', requiredPopup._closeOnEscape);
        requiredPopup.remove();
        requiredPopup = null;
        document.body.classList.remove('required-fields-popup-open');
        requiredPopupFocusTarget?.focus?.({ preventScroll: true });
        requiredPopupFocusTarget = null;
    };

    const showRequiredPopup = (focusTarget = document.activeElement) => {
        if (requiredPopup) return;
        requiredPopupFocusTarget = focusTarget instanceof HTMLElement ? focusTarget : null;

        const backdrop = document.createElement('div');
        backdrop.className = 'required-fields-popup-backdrop';
        backdrop.innerHTML = `
            <section class="required-fields-popup" role="alertdialog" aria-modal="true" aria-labelledby="required-fields-popup-title" aria-describedby="required-fields-popup-message" tabindex="-1">
                <span class="required-fields-popup-icon" aria-hidden="true">!</span>
                <h2 id="required-fields-popup-title">Data belum lengkap</h2>
                <p id="required-fields-popup-message">Harap lengkapi seluruh data wajib</p>
                <button type="button" class="required-fields-popup-button">Mengerti</button>
            </section>
        `;

        document.body.append(backdrop);
        document.body.classList.add('required-fields-popup-open');
        requiredPopup = backdrop;

        backdrop.querySelector('.required-fields-popup-button')?.addEventListener('click', closeRequiredPopup);
        backdrop.addEventListener('click', (event) => {
            if (event.target === backdrop) closeRequiredPopup();
        });
        backdrop._closeOnEscape = (event) => {
            if (event.key === 'Escape') closeRequiredPopup();
        };
        document.addEventListener('keydown', backdrop._closeOnEscape);
        backdrop.querySelector('.required-fields-popup')?.focus();
    };

    document.addEventListener('admin:required-missing', (event) => {
        showRequiredPopup(event.detail?.focusTarget);
    });

    document.querySelectorAll('form').forEach((form) => {
        if (getRequiredControls(form).length) form.noValidate = true;
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;

        const invalidControls = getRequiredControls(form).filter((control) => !control.validity.valid);
        if (!invalidControls.length) return;

        event.preventDefault();
        event.stopImmediatePropagation();
        invalidControls.forEach(showError);
        if (invalidControls.some((control) => control.validity.valueMissing)) {
            showRequiredPopup(invalidControls[0]);
        }
        invalidControls[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        if (!requiredPopup) invalidControls[0].focus({ preventScroll: true });
    }, true);

    const refreshError = (event) => {
        const control = event.target;
        if (!(control instanceof HTMLElement) || !control.dataset.validationErrorId) return;
        if (control.validity?.valid) clearError(control);
        else showError(control);
    };

    document.addEventListener('input', refreshError, true);
    document.addEventListener('change', refreshError, true);
    document.addEventListener('reset', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;
        window.setTimeout(() => getRequiredControls(form).forEach(clearError), 0);
    }, true);
})();
