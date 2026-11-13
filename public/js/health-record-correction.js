(() => {
    const button = document.getElementById('verifyReturnForCorrectionBtn');
    const dialog = document.getElementById('returnForCorrectionDialog');
    if (!button || !dialog) return;
    const form = dialog.querySelector('form');
    const submission = form.querySelector('[name="submission_id"]');
    window.configureHealthRecordCorrection = payload => {
        form.reset();
        const id = Number(payload.correction_submission_id || 0);
        button.disabled = !payload.correction_url || !Number.isInteger(id) || id < 1;
        form.setAttribute('action', payload.correction_url || '');
        submission.value = id > 0 ? String(id) : '';
    };
    button.addEventListener('click', () => {
        if (button.disabled) return;
        dialog.showModal();
        dialog.querySelector('[data-return-correction-cancel]').focus();
    });
    dialog.querySelector('[data-return-correction-cancel]').addEventListener('click', () => dialog.close());
    form.addEventListener('submit', event => {
        if (button.disabled || !form.getAttribute('action') || !submission.value) {
            event.preventDefault();
            return;
        }
        // Neither native dialog may remain in the top layer above the loader.
        document.querySelectorAll('dialog[open]').forEach(openDialog => openDialog.close());
    });
})();
