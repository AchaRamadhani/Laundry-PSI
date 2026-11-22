(() => {
    const modal = document.getElementById('paymentSettlementModal');
    if (!modal) return;

    const error = document.getElementById('paymentSettlementError');
    const confirmButton = modal.querySelector('[data-payment-confirm]');
    const cancelButton = modal.querySelector('[data-payment-cancel]');
    let activeForm = null;

    const closeModal = () => {
        modal.hidden = true;
        activeForm = null;
    };

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.matches('.update-status-payment-form')) return;
        if (form.dataset.paymentStatus === 'lunas' || form.dataset.paymentConfirmed === 'true') return;

        const statusSelect = [...document.querySelectorAll('select[name="status_baru"]')]
            .find((select) => select.form === form);
        if (statusSelect?.value !== 'diambil') return;

        event.preventDefault();
        activeForm = form;
        error.hidden = true;
        modal.hidden = false;
        modal.querySelector('input[name="payment_settlement_choice"]')?.focus();
    });

    confirmButton?.addEventListener('click', () => {
        if (!activeForm) return;

        const selectedMethod = modal.querySelector('input[name="payment_settlement_choice"]:checked');
        if (!selectedMethod) {
            error.hidden = false;
            return;
        }

        activeForm.querySelector('[name="metode_pembayaran_pelunasan"]').value = selectedMethod.value;
        activeForm.dataset.paymentConfirmed = 'true';
        const formToSubmit = activeForm;
        closeModal();
        formToSubmit.requestSubmit();
    });

    cancelButton?.addEventListener('click', closeModal);
    modal.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.hidden) closeModal();
    });
})();
