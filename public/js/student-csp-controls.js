(function () {
    'use strict';

    document.addEventListener('submit', function (event) {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (form.hasAttribute('data-student-prevent-submit')) event.preventDefault();
        if (form.hasAttribute('data-student-confirm-submit') && !window.confirm(form.dataset.studentConfirmSubmit)) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    }, true);

    document.addEventListener('click', function (event) {
        const button = event.target.closest('[data-student-action]');
        if (!button) return;

        switch (button.dataset.studentAction) {
            case 'edit-profile': window.openProfileEditModal(button.dataset.profileEditAllowed === 'true'); break;
            case 'open-health-record': window.openHealthRecordModal(); break;
            case 'close-health-record': window.closeHealthRecordModal(); break;
            case 'open-signature': window.openMissingESignModal(); break;
            case 'close-signature': window.closeMissingESignModal(); break;
            case 'close-document': window.closeMissingDocumentModal(); break;
            case 'replace-signature': window.closeMissingDocumentModal(); window.openMissingESignModal(); break;
            case 'close-notification-settings': window.closeNotificationSettingsModal(); break;
            case 'close-resubmission': window.closeGlobalResubmissionModal(); break;
            case 'request-health-form': document.getElementById('studentNewCycleDialog')?.showModal(); break;
            case 'close-dialog': button.closest('dialog')?.close(); break;
            case 'dismiss-health-form': document.getElementById('healthFormModal')?.style.setProperty('display', 'none', 'important'); break;
            case 'reload': window.location.reload(); break;
            case 'print': window.print(); break;
            case 'close-window': window.close(); break;
        }
    });
})();
