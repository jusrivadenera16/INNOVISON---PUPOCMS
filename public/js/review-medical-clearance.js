(() => {
    'use strict';
    const root = document.querySelector('[data-review-clearance]');
    if (!root) return;
    const node = key => root.querySelector(`[data-review-clearance-${key}]`);
    let current = null, token = null;
    function status(message) { node('status').textContent = message; node('status').hidden = !message; }
    function sync() {
        const checked = node('enable').checked;
        const pending = document.querySelector('[name="applicant_clearance_decision"]:checked')?.value === 'pending';
        node('fields').hidden = !checked;
        const other = node('purpose').value === '__other__';
        node('other-label').hidden = !other;
        for (const key of ['date', 'purpose', 'other']) {
            node(key).disabled = !checked || pending || !current || (key === 'other' && !other);
            node(key).required = checked && !pending && (key !== 'other' || other);
        }
        if (checked && pending) status('Pending decision selected. No Medical Clearance will be issued.');
        else if (current) status('');
    }
    function patient() {
        if (!current) return;
        const identity = current.identity;
        let age = identity.age;
        if (identity.birth_date && /^\d{4}-\d{2}-\d{2}$/.test(node('date').value)) {
            const [by, bm, bd] = identity.birth_date.split('-').map(Number);
            const [year, month, day] = node('date').value.split('-').map(Number);
            age = year - by - (month < bm || (month === bm && day < bd) ? 1 : 0);
        }
        node('patient').value = `${identity.name}  ${age ?? '-'} / ${identity.sex || '-'}`;
    }
    function reset() {
        token = null; current = null; root.hidden = true; node('enable').checked = false;
        node('fields').hidden = true; node('patient').value = ''; node('other').value = ''; status('');
        sync();
    }
    window.ReviewMedicalClearance = {
        reset,
        async configure(type, id, reviewable) {
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
                current = { ...data, request_key: crypto.randomUUID() };
                node('date').value = data.today;
                node('purpose').replaceChildren();
                for (const [value, label] of [['', 'Select a purpose'], ...data.purposes.map(value => [value, value]), ['__other__', 'Others']]) {
                    const option = document.createElement('option'); option.value = value; option.textContent = label; node('purpose').append(option);
                }
                node('purpose').value = data.default_purpose;
                patient(); sync();
            } catch (error) { if (token === loading) status(error.message); }
        },
        collect(approved) {
            if (root.hidden || !approved || !node('enable').checked) return { issue_medical_clearance: false };
            if (!current) throw new Error('Medical Clearance options are not ready. Wait or uncheck Issue Medical Clearance.');
            for (const key of ['date', 'purpose', 'other']) {
                if (!node(key).disabled && !node(key).reportValidity()) throw new Error('Complete the Medical Clearance date and purpose.');
            }
            return { issue_medical_clearance: true, medical_clearance: { request_key: current.request_key,
                issued_on: node('date').value, purpose: node('purpose').value, other_purpose: node('other').value.trim() } };
        },
    };
    node('enable').addEventListener('change', sync);
    node('purpose').addEventListener('change', sync);
    node('date').addEventListener('change', patient);
    document.addEventListener('change', event => { if (event.target.name === 'applicant_clearance_decision') sync(); });
    reset();
})();
