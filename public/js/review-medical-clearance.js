(() => {
    'use strict';
    const root = document.querySelector('[data-review-clearance]');
    if (!root) return;
    const node = key => root.querySelector(`[data-review-clearance-${key}]`);
    let current = null, token = null;
    let issueChoiceRequired = true, forcedIssueChoice = false;
    let purposeSelect = null;
    const selectedIssueChoice = () => root.querySelector('[name="issue_medical_clearance"]:checked')?.value || '';
    function enhancePurposeSelect() {
        // Keep a usable native fallback if the shared helper has not loaded yet.
        // Retry on configuration instead of permanently losing the custom UI.
        purposeSelect ||= window.CategoryBubbleSelect?.create({
            select: node('purpose'), trigger: node('purpose-trigger'), menu: node('purpose-menu'), onChange: sync,
        });
        purposeSelect?.refresh();
    }
    function status(message) { node('status').textContent = message; node('status').hidden = !message; }
    function sync() {
        const pending = document.querySelector('[name="applicant_clearance_decision"]:checked')?.value === 'pending';
        const issueChoice = selectedIssueChoice();
        const shouldIssue = !pending && (forcedIssueChoice || (issueChoiceRequired && issueChoice === '1'));
        const other = node('purpose').value === '__other__';
        node('issue-choice').hidden = pending || !issueChoiceRequired;
        node('purpose-fields').hidden = !shouldIssue;
        node('other-label').hidden = !shouldIssue || !other;
        node('purpose').disabled = !shouldIssue || !current;
        node('purpose').required = shouldIssue;
        node('other').disabled = !shouldIssue || !current || !other;
        node('other').required = shouldIssue && other;
        if (pending) status('Pending decision selected. No Medical Clearance will be issued.');
        else if (current) status('');
        enhancePurposeSelect();
    }
    function reset() {
        token = null; current = null; issueChoiceRequired = true; forcedIssueChoice = false; root.hidden = true;
        node('issue-yes').checked = false; node('issue-no').checked = false;
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
            if (root.hidden) return null;
            return {
                request_key: current?.request_key || null,
                issue_medical_clearance: forcedIssueChoice ? true : (issueChoiceRequired && selectedIssueChoice() !== '' ? selectedIssueChoice() === '1' : null),
                purpose: selectedIssueChoice() === '1' || forcedIssueChoice ? node('purpose').value : '',
                other_purpose: selectedIssueChoice() === '1' || forcedIssueChoice ? node('other').value : '',
            };
        },
        restoreDraft(draft) {
            if (!current || !draft || typeof draft !== 'object') return;
            if (/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(draft.request_key || '')) current.request_key = draft.request_key;
            if (issueChoiceRequired) {
                const choice = draft.issue_medical_clearance;
                const shouldIssue = choice === true || choice === 1 || choice === '1' || choice === 'yes'
                    || (choice === undefined && Boolean(draft.purpose));
                const shouldSkip = choice === false || choice === 0 || choice === '0' || choice === 'no';
                node(shouldIssue ? 'issue-yes' : 'issue-no').checked = shouldIssue || shouldSkip;
            }
            node('purpose').value = [...node('purpose').children].some(option => option.value === draft.purpose) ? draft.purpose : '';
            node('other').value = String(draft.other_purpose || '').slice(0, 180);
            sync();
        },
        configureApproved(data, category, draft = null) {
            reset(); issueChoiceRequired = false; forcedIssueChoice = true; root.hidden = false; populate(data);
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
            if (issueChoiceRequired && selectedIssueChoice() === '') {
                throw new Error('Choose Yes or No for issuing a Medical Clearance.');
            }
            if (issueChoiceRequired && selectedIssueChoice() === '0') {
                return { issue_medical_clearance: false };
            }
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
    node('issue-yes').addEventListener('change', sync);
    node('issue-no').addEventListener('change', sync);
    document.addEventListener('change', event => { if (event.target.name === 'applicant_clearance_decision') sync(); });
    reset();
})();
