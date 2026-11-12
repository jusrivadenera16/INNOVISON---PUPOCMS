(() => {
    'use strict';
    const modal = document.getElementById('medicalClearanceModal');
    if (!modal) return;
    const form = modal.querySelector('[data-clearance-form]');
    const field = name => form.elements.namedItem(name);
    const status = modal.querySelector('[data-clearance-status]');
    const otherLabel = modal.querySelector('[data-clearance-other]');
    const states = new WeakMap();
    let active = null;
    let busy = false;
    const setStatus = (node, message, success = false) => {
        node.textContent = message;
        node.hidden = !message;
        node.dataset.success = String(success);
    };
    async function json(url, options = {}) {
        const response = await fetch(url, { credentials: 'same-origin', ...options,
            headers: { Accept: 'application/json', ...options.headers } });
        const data = await response.json();
        if (!response.ok) throw new Error(Object.values(data.errors || {}).flat()[0] || data.message || 'Unable to complete the request.');
        return data;
    }
    function renderDocuments(panel, documents) {
        const list = panel.querySelector('[data-clearance-list]');
        list.replaceChildren();
        for (const documentData of documents) {
            const row = document.createElement('li');
            const description = document.createElement('span');
            description.textContent = [documentData.control_number, documentData.purpose, documentData.issued_on].filter(Boolean).join(' - ');
            const link = document.createElement('a');
            link.href = documentData.url;
            link.target = '_blank';
            link.rel = 'noopener';
            link.textContent = 'View / Reprint';
            row.append(description, link);
            list.append(row);
        }
        panel.querySelector('[data-clearance-documents]').hidden = documents.length === 0;
    }
    async function load(panel, url) {
        if (!panel) return;
        const token = {};
        states.set(panel, { token, url });
        panel.dataset.clearanceUrl = url || '';
        const documentsOnly = panel.dataset.clearanceDocumentsOnly === 'true';
        panel.hidden = !url || documentsOnly;
        panel.querySelector('[data-clearance-open]').hidden = true;
        renderDocuments(panel, []);
        const panelStatus = panel.querySelector('[data-clearance-panel-status]');
        setStatus(panelStatus, '');
        if (!url) return;
        try {
            const data = await json(url);
            if (states.get(panel)?.token !== token) return;
            states.set(panel, { token, url, data });
            panel.querySelector('[data-clearance-open]').hidden = documentsOnly || !data.can_issue;
            renderDocuments(panel, data.documents || []);
            panel.hidden = (documentsOnly || !data.can_issue) && !data.documents?.length;
        } catch (error) {
            if (states.get(panel)?.token === token) setStatus(panelStatus, error.message);
        }
    }
    function syncOther() {
        const other = field('purpose').value === '__other__';
        otherLabel.hidden = !other;
        field('other_purpose').disabled = !other;
        field('other_purpose').required = other;
    }
    function syncPatient() {
        const identity = active?.state.data.identity;
        if (!identity) return;
        let age = identity.age;
        if (identity.birth_date && /^\d{4}-\d{2}-\d{2}$/.test(field('issued_on').value)) {
            const [by, bm, bd] = identity.birth_date.split('-').map(Number);
            const [year, month, day] = field('issued_on').value.split('-').map(Number);
            age = year - by - (month < bm || (month === bm && day < bd) ? 1 : 0);
        }
        modal.querySelector('[data-clearance-patient]').value = `${identity.name}  ${age ?? '-'} / ${identity.sex || '-'}`;
    }
    function open(panel) {
        const state = states.get(panel);
        if (!state?.data?.can_issue || busy) return;
        active = { panel, state, key: crypto.randomUUID() };
        form.reset();
        setStatus(status, '');
        field('issued_on').value = state.data.today;
        syncPatient();
        field('purpose').replaceChildren();
        for (const [value, label] of [['', 'Select a purpose'], ...state.data.purposes.map(value => [value, value]), ['__other__', 'Others']]) {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = label;
            field('purpose').append(option);
        }
        field('purpose').value = state.data.default_purpose;
        syncOther();
        modal.showModal();
    }
    const close = () => { if (!busy) { modal.close(); active = null; } };
    modal.querySelectorAll('[data-clearance-close]').forEach(button => button.addEventListener('click', close));
    modal.addEventListener('cancel', event => { if (busy) event.preventDefault(); });
    modal.addEventListener('keydown', event => { if (event.key === 'Escape') event.stopPropagation(); });
    field('purpose').addEventListener('change', syncOther);
    field('issued_on').addEventListener('change', syncPatient);
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (busy || !active || !form.reportValidity()) return;
        busy = true;
        const current = active;
        modal.querySelectorAll('button').forEach(button => { button.disabled = true; });
        setStatus(status, 'Generating and saving Medical Clearance...');
        try {
            const data = await json(current.state.url, { method: 'POST', headers: {
                'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            }, body: JSON.stringify({ request_key: current.key, issued_on: field('issued_on').value,
                purpose: field('purpose').value, other_purpose: field('other_purpose').value }) });
            // The saved document must remain accessible even if refreshing the list fails.
            const documents = [data.document, ...(current.state.data.documents || []).filter(item => item.id !== data.document.id)];
            current.state.data.documents = documents;
            renderDocuments(current.panel, documents);
            current.panel.hidden = false;
            setStatus(current.panel.querySelector('[data-clearance-panel-status]'), data.message, true);
            modal.close();
            active = null;
        } catch (error) {
            setStatus(status, error.message);
        } finally {
            busy = false;
            modal.querySelectorAll('button').forEach(button => { button.disabled = false; });
        }
    });
    document.addEventListener('click', event => {
        const button = event.target.closest('[data-clearance-open]');
        if (button) open(button.closest('[data-clearance-panel]'));
    });
    window.ClinicMedicalClearance = {
        load,
        loadRecord(panel, type, id) {
            const url = ['student', 'employee'].includes(type) && Number.isInteger(Number(id)) && Number(id) > 0
                ? `${modal.dataset.clearanceBase}/${type}/${id}` : null;
            return load(panel, url);
        },
    };
    document.querySelectorAll('[data-clearance-panel][data-clearance-url]').forEach(panel => load(panel, panel.dataset.clearanceUrl));
})();
