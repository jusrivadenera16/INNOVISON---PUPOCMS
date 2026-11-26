(function () {
    'use strict';

    document.addEventListener('submit', function (event) {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-confirm-submit')) return;
        if (!window.confirm(form.dataset.confirmSubmit)) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    }, true);

    document.addEventListener('change', function (event) {
        const control = event.target.closest('[data-submit-on-change]');
        if (control?.form) control.form.requestSubmit();
    });

    document.addEventListener('click', function (event) {
        const button = event.target.closest('[data-admin-reload], [data-admin-copy-source]');
        if (!button) return;
        if (button.hasAttribute('data-admin-reload')) window.location.reload();
        if (button.hasAttribute('data-admin-copy-source')) {
            navigator.clipboard?.writeText(document.getElementById('source')?.value || '');
        }
    });

    document.querySelectorAll('[data-stop-click-propagation]').forEach(function (element) {
        element.addEventListener('click', function (event) { event.stopPropagation(); });
    });
})();
