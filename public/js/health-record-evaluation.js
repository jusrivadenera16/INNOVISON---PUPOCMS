(() => {
    function initRecordEvaluation() {
        const modal = document.getElementById('recordEvaluationModal');
        if (!modal || modal.dataset.ready) return;
        modal.dataset.ready = 'true';
        const send = modal.querySelector('[data-record-evaluation-send]');
        const cancel = modal.querySelector('[data-record-evaluation-cancel]');
        const status = modal.querySelector('[data-record-evaluation-status]');
        let busy = false;
        const close = () => { if (!busy) modal.close(); };

        document.querySelectorAll('[data-record-evaluation-open]').forEach(trigger => {
            trigger.addEventListener('click', () => {
                document.getElementById('profileActionsMenu')?.classList.remove('is-open');
                document.getElementById('profileActionsToggle')?.setAttribute('aria-expanded', 'false');
                document.getElementById('employeeProfileActions')?.classList.remove('is-open');
                document.getElementById('employeeProfileActionsToggle')?.setAttribute('aria-expanded', 'false');
                document.getElementById('employeeProfileActionsMenu')?.setAttribute('aria-hidden', 'true');
                status.hidden = true;
                if (!modal.open) modal.showModal();
                send.focus();
            });
        });
        cancel.addEventListener('click', close);
        modal.addEventListener('click', event => { if (event.target === modal) close(); });
        modal.addEventListener('cancel', event => { if (busy) event.preventDefault(); });
        modal.addEventListener('close', () => {
            document.querySelector('#profileActionsToggle, #employeeProfileActionsToggle')?.focus();
        });
        send.addEventListener('click', async () => {
            if (busy) return;
            busy = true;
            send.disabled = cancel.disabled = true;
            send.textContent = 'Sending...';
            status.hidden = true;
            try {
                const response = await fetch(modal.dataset.recordEvaluationUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await response.json();
                if (!response.ok || !data.success) throw new Error(data.message || 'Unable to send the evaluation. Please try again.');
                busy = false;
                close();
                window.showAdminToast?.('success', data.message || 'The service evaluation link was sent.');
            } catch (error) {
                status.textContent = error.message || 'Unable to send the evaluation. Please try again.';
                status.hidden = false;
            } finally {
                busy = false;
                send.disabled = cancel.disabled = false;
                send.textContent = 'Send';
            }
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initRecordEvaluation);
    else initRecordEvaluation();
})();
