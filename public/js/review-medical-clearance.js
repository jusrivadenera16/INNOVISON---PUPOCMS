(() => {
    'use strict';
    const root = document.querySelector('[data-review-clearance]');
    if (!root) return;
    const node = key => root.querySelector(`[data-review-clearance-${key}]`);
    let current = null, token = null;
    const purposeSelect = window.CategoryBubbleSelect?.create({
        select: node('purpose'), trigger: node('purpose-trigger'), menu: node('purpose-menu'), onChange: sync,
    });
    function status(message) { node('status').textContent = message; node('status').hidden = !message; }
    function sync() {
        const pending = document.querySelector('[name="applicant_clearance_decision"]:checked')?.value === 'pending';
        const other = node('purpose').value === '__other__';
        node('other-label').hidden = !other;
        for (const key of ['purpose', 'other']) {
            node(key).disabled = pending || !current || (key === 'other' && !other);
            node(key).required = !root.hidden && !pending && (key !== 'other' || other);
        }
        if (pending) status('Pending decision selected. No Medical Clearance will be issued.');
        else if (current) status('');
        purposeSelect?.refresh();
    }
    function reset() {
        token = null; current = null; root.hidden = true;
        node('other').value = ''; node('document').hidden = true; status('');
        sync();
    }
    function populate(data) {
        current = { ...data, request_key: crypto.randomUUID() };
        node('purpose').replaceChildren();
        const choices = (data.purpose_options || data.purposes.map(value => ({ value, label: value })))
            .filter(option => option.label.toLowerCase() !== 'others');
        for (const [value, label] of [['', 'Select a purpose'], ...choices.map(option => [option.value, option.label]), ...(data.allow_other === false ? [] : [['__other__', 'Others']])]) {
            const option = document.createElement('option'); option.value = value; option.textContent = label; node('purpose').append(option);
        }
        node('purpose').value = '';
        sync();
        if (!choices.length && data.allow_other === false) status('No certificate purposes are configured for this patient type. Check Medical Configuration > Clearance / Certificate Types.');
    }
    window.ReviewMedicalClearance = {
        reset,
        exportDraft() {
            return root.hidden || !current ? null : {request_key: current.request_key, purpose: node('purpose').value, other_purpose: node('other').value};
        },
        restoreDraft(draft) {
            if (!current || !draft || typeof draft !== 'object') return;
            if (/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(draft.request_key || '')) current.request_key = draft.request_key;
            node('purpose').value = [...node('purpose').children].some(option => option.value === draft.purpose) ? draft.purpose : '';
            node('other').value = String(draft.other_purpose || '').slice(0, 180);
            sync();
        },
        configureApproved(data, category, draft = null) {
            reset(); root.hidden = false; populate(data);
            this.restoreDraft(draft);
        },
        async configure(type, id, reviewable, draft = null) {
            reset();
            if (!reviewable || !['student', 'employee'].includes(type) || !Number.isInteger(Number(id)) || Number(id) < 1) return;
            root.hidden = false;
            const loading = {}; token = loading;
            status('Loading Medical Clearance options...');
            try {
                const response = await fetch(`${root.dataset.optionsBase}/${type}/${id}/review-options`, { credentials: 'same-origin', headers: { Accept: 'application/json' } });
                const data = await response.json();
                if (token !== loading) return;
                if (!response.ok) throw new Error(data.message || 'Unable to load Medical Clearance options.');
                populate(data);
                this.restoreDraft(draft);
            } catch (error) { if (token === loading) status(error.message); }
        },
        collect(approved) {
            if (root.hidden || !approved) return { issue_medical_clearance: false };
            if (!current) throw new Error('Medical Clearance purposes are not ready. Please wait or try again.');
            for (const key of ['purpose', 'other']) {
                const field = node(key);
                if (!field.disabled && field.required && !field.value.trim()) {
                    if (key === 'purpose' && purposeSelect) purposeSelect.focus();
                    else field.reportValidity();
                    throw new Error('Select the required Request for / Purpose.');
                }
            }
            return { issue_medical_clearance: true, medical_clearance: { request_key: current.request_key,
                purpose: node('purpose').value, other_purpose: node('other').value.trim() } };
        },
    };
    node('purpose').addEventListener('change', sync);
    document.addEventListener('change', event => { if (event.target.name === 'applicant_clearance_decision') sync(); });
    reset();
})();
