<script>
    const umEnhancedSelects = [];

    function closeUmCustomSelects(except = null) {
        umEnhancedSelects.forEach((control) => {
            if (control.wrapper !== except) {
                control.wrapper.classList.remove('is-open');
                control.button.setAttribute('aria-expanded', 'false');
            }
        });
    }

    function refreshUmCustomSelects() {
        umEnhancedSelects.forEach((control) => {
            const selected = control.select.options[control.select.selectedIndex];
            control.button.textContent = selected ? selected.textContent.trim() : 'Select an option';
            control.options.forEach((optionButton) => {
                optionButton.classList.toggle('is-selected', optionButton.dataset.value === control.select.value);
            });
            const disabled = control.select.disabled;
            control.button.disabled = disabled;
            control.wrapper.style.display = control.select.closest('.um-field')?.style.display === 'none' ? 'none' : '';
        });
    }

    function enhanceUmSelect(select) {
        if (!select || select.dataset.customSelectReady === '1') return;
        select.dataset.customSelectReady = '1';

        const wrapper = document.createElement('div');
        wrapper.className = 'um-custom-select';
        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(select);
        select.classList.add('um-custom-select-native');

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'um-custom-select-button';
        button.setAttribute('aria-haspopup', 'listbox');
        button.setAttribute('aria-expanded', 'false');

        const menu = document.createElement('div');
        menu.className = 'um-custom-select-menu';
        menu.setAttribute('role', 'listbox');

        const optionButtons = Array.from(select.options).map((option) => {
            const optionButton = document.createElement('button');
            optionButton.type = 'button';
            optionButton.className = 'um-custom-select-option';
            optionButton.dataset.value = option.value;
            optionButton.textContent = option.textContent.trim();
            optionButton.setAttribute('role', 'option');
            optionButton.addEventListener('click', () => {
                select.value = option.value;
                select.dispatchEvent(new Event('change', { bubbles: true }));
                wrapper.classList.remove('is-open');
                button.setAttribute('aria-expanded', 'false');
                refreshUmCustomSelects();
            });
            menu.appendChild(optionButton);
            return optionButton;
        });

        button.addEventListener('click', () => {
            if (button.disabled) return;
            const willOpen = !wrapper.classList.contains('is-open');
            closeUmCustomSelects(wrapper);
            wrapper.classList.toggle('is-open', willOpen);
            button.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });

        wrapper.appendChild(button);
        wrapper.appendChild(menu);
        umEnhancedSelects.push({ select, wrapper, button, options: optionButtons });
        refreshUmCustomSelects();
    }

    document.querySelectorAll('#settingsModal .um-field select').forEach(enhanceUmSelect);
    document.addEventListener('click', (event) => {
        if (!event.target.closest('.um-custom-select')) closeUmCustomSelects();
    });

    const umSettingsModal = document.getElementById('settingsModal');
    if (umSettingsModal) {
        new MutationObserver(() => {
            if (umSettingsModal.classList.contains('show')) {
                requestAnimationFrame(refreshUmCustomSelects);
            }
        }).observe(umSettingsModal, { attributes: true, attributeFilter: ['class'] });
    }

    const umActionConfirmDialog = document.getElementById('umActionConfirmDialog');
    const umActionConfirmTitle = document.getElementById('umActionConfirmTitle');
    const umActionConfirmCopy = document.getElementById('umActionConfirmCopy');
    const umActionConfirmCancel = document.getElementById('umActionConfirmCancel');
    const umActionConfirmProceed = document.getElementById('umActionConfirmProceed');
    let umPendingConfirmation = null;

    window.openUmActionConfirmation = function({ title, message, confirmLabel = 'Confirm', onConfirm }) {
        if (!umActionConfirmDialog?.showModal || umActionConfirmDialog.open) return;
        umActionConfirmTitle.textContent = title;
        umActionConfirmCopy.textContent = message;
        umActionConfirmProceed.textContent = confirmLabel;
        umPendingConfirmation = onConfirm;
        umActionConfirmDialog.showModal();
        umActionConfirmCancel.focus();
    };

    const submitUmFormAfterConfirmation = (form, submitter) => {
        form.dataset.umConfirmBypass = '1';
        if (typeof form.requestSubmit === 'function') {
            if (submitter?.form === form) {
                form.requestSubmit(submitter);
            } else {
                form.requestSubmit();
            }
        } else {
            form.submit();
        }
        if (form.dataset.umConfirmBypass === '1') {
            delete form.dataset.umConfirmBypass;
        }
    };

    const getUmFormConfirmation = (form, submitter) => {
        if (form.id !== 'settingsForm') {
            return {
                title: submitter?.dataset.umConfirmTitle || 'Confirm Account Action',
                message: submitter?.dataset.umConfirmMessage || 'Continue with this account change?',
                confirmLabel: submitter?.dataset.umConfirmLabel || submitter?.textContent.trim() || 'Confirm',
            };
        }

        const isCreate = form.querySelector('#settingsMethod')?.value.toUpperCase() === 'POST';
        const managementView = form.querySelector('#detailManagementView')?.value;
        const personName = form.querySelector('#detailFullName')?.value
            || document.getElementById('detailName')?.value
            || 'this user';
        const selectedRole = form.querySelector('#detailRole')?.selectedOptions[0]?.textContent.trim();
        const accessType = managementView === 'admin-hub' ? 'Admin Hub' : 'Clinic';
        const roleCopy = selectedRole ? ` as ${selectedRole}` : '';

        return isCreate
            ? {
                title: `Confirm ${accessType} Access`,
                message: `Add ${personName} to ${accessType}${roleCopy} with the selected account and module permissions?`,
                confirmLabel: `Add to ${accessType}`,
            }
            : {
                title: 'Confirm User Update',
                message: `Save the updated account and access settings for ${personName}?`,
                confirmLabel: 'Save Changes',
            };
    };

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !['settingsForm', 'deleteForm', 'deleteAccountForm', 'deleteAdminHubForm'].includes(form.id)) return;
        if (form.dataset.umConfirmBypass === '1') {
            delete form.dataset.umConfirmBypass;
            return;
        }

        event.preventDefault();
        if (umActionConfirmDialog?.open) return;
        const submitter = event.submitter;
        const confirmation = getUmFormConfirmation(form, submitter);
        window.openUmActionConfirmation({
            ...confirmation,
            onConfirm: () => submitUmFormAfterConfirmation(form, submitter),
        });
    }, true);

    umActionConfirmProceed?.addEventListener('click', () => {
        const confirm = umPendingConfirmation;
        umPendingConfirmation = null;
        umActionConfirmDialog.close();
        confirm?.();
    });

    umActionConfirmCancel?.addEventListener('click', () => {
        umPendingConfirmation = null;
        umActionConfirmDialog.close();
    });

    umActionConfirmDialog?.addEventListener('cancel', () => {
        umPendingConfirmation = null;
    });

    umActionConfirmDialog?.addEventListener('click', (event) => {
        if (event.target === umActionConfirmDialog) {
            umPendingConfirmation = null;
            umActionConfirmDialog.close();
        }
    });
</script>
