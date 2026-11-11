(() => {
    'use strict';
    const root = document.querySelector('[data-approved-clearance]');
    if (!root) return;
    const node = key => root.querySelector(`[data-approved-clearance-${key}]`);
    const bubble = node('bubble'), form = node('form'), trigger = node('open');
    const result = root.closest('.applicant-approved-result');
    let record = null, options = null, token = null, busy = false, issued = false, requestKey = null;
    const categoryDropdown = window.CategoryBubbleSelect?.create({ select: node('category'), trigger: node('category-trigger'), menu: node('category-menu'), onToggle: position });
    function message(key, text) { node(key).textContent = text; node(key).hidden = !text; }
    function position() {
        if (bubble.hidden) return;
        const rect = trigger.getBoundingClientRect(), below = rect.top < bubble.offsetHeight + 22;
        bubble.toggleAttribute('data-below', below);
        bubble.style.left = `${Math.max(12, Math.min(rect.left, window.innerWidth - bubble.offsetWidth - 12))}px`;
        bubble.style.top = `${Math.max(12, Math.min(below ? rect.bottom + 12 : rect.top - bubble.offsetHeight - 12, window.innerHeight - bubble.offsetHeight - 12))}px`;
    }
    function close() {
        token = null;
        categoryDropdown?.close();
        if (!bubble.hidden) bubble.hidePopover?.();
        bubble.hidden = true; trigger.setAttribute('aria-expanded', 'false');
    }
    function review(show) {
        form.hidden = !show; node('prompt').hidden = show; result?.classList.toggle('is-clearance-review', show);
        const title = document.getElementById('lookupModalTitle');
        if (title && record) title.textContent = show ? 'Doctor Review' : 'Health Form Status';
    }
    async function json(url, init = {}) {
        const response = await fetch(url, { credentials: 'same-origin', ...init, headers: { Accept: 'application/json', ...init.headers } });
        const data = await response.json();
        if (!response.ok) throw new Error(Object.values(data.errors || {}).flat()[0] || data.message || 'Unable to issue Medical Clearance.');
        return data;
    }
    function patient() {
        if (!options) return;
        const identity = options.identity;
        let age = identity.age;
        if (identity.birth_date && node('date').value) {
            const [by, bm, bd] = identity.birth_date.split('-').map(Number), [y, m, d] = node('date').value.split('-').map(Number);
            age = y - by - (m < bm || (m === bm && d < bd) ? 1 : 0);
        }
        node('patient').value = `${identity.name}  ${age ?? '-'} / ${identity.sex || '-'}`;
    }
    function other() {
        const enabled = node('purpose').value === '__other__';
        node('other-label').hidden = !enabled; node('other').disabled = !enabled; node('other').required = enabled;
    }
    window.ApprovedClearanceReview = { configure(type, id) {
        close(); review(false); options = null; busy = false; issued = false; requestKey = null;
        record = ['student', 'employee'].includes(type) && Number(id) > 0 ? `${root.dataset.base}/${type}/${Number(id)}` : null;
        root.hidden = !record; form.reset(); node('document').hidden = true; node('issue').disabled = false; node('back').disabled = false;
        message('status', '');
    } };
    trigger.addEventListener('click', async () => {
        if (!record || busy) return;
        if (!bubble.hidden) { close(); return; }
        const loading = {}; token = loading;
        node('category').replaceChildren(); node('continue').disabled = true; message('bubble-status', 'Loading Health Form categories...');
        node('category').disabled = true; categoryDropdown?.refresh();
        bubble.hidden = false; bubble.showPopover?.(); trigger.setAttribute('aria-expanded', 'true'); position();
        try {
            const data = await json(record);
            if (token !== loading) return;
            options = data;
            for (const value of ['', ...data.health_form_categories]) {
                const option = document.createElement('option'); option.value = value; option.textContent = value || 'Select a category'; node('category').append(option);
            }
            node('continue').disabled = !data.can_issue || !data.health_form_categories.length;
            node('category').disabled = !data.can_issue || !data.health_form_categories.length; categoryDropdown?.refresh();
            if (!data.can_issue) throw new Error('No eligible approved Health Form is available for another clearance. Check for a pending form or withdrawn record.');
            message('bubble-status', data.health_form_categories.length ? '' : 'No active categories are available for this patient type.'); position();
            if (categoryDropdown) categoryDropdown.focus(); else node('category').focus();
        } catch (error) { if (token === loading) { message('bubble-status', error.message); position(); } }
    });
    node('close').addEventListener('click', close);
    node('continue').addEventListener('click', event => {
        event.preventDefault();
        if (!options?.can_issue) return;
        if (!options.health_form_categories.includes(node('category').value)) { message('bubble-status', 'Select a Health Form category first.'); return; }
        const category = node('category').value;
        form.reset(); issued = false; node('issue').disabled = false; node('document').hidden = true;
        node('selected').value = category; node('date').value = options.today;
        node('purpose').replaceChildren();
        for (const value of [...options.purposes, '__other__']) {
            const option = document.createElement('option'); option.value = value; option.textContent = value === '__other__' ? 'Others' : value; node('purpose').append(option);
        }
        node('purpose').value = category; requestKey = crypto.randomUUID(); message('status', ''); patient(); other(); close(); review(true); node('decision').focus();
    });
    node('back').addEventListener('click', () => {
        if (busy) return;
        review(false);
        if (issued) window.ClinicMedicalClearance?.loadRecord(result.querySelector('[data-clearance-panel]'), record?.split('/').at(-2), record?.split('/').at(-1));
    });
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (busy || issued || !record || !options || !form.reportValidity()) return;
        if (node('decision').value !== 'issue') { message('status', 'No clearance issued. Complete the Doctor Review and select Cleared / Issue when appropriate.'); return; }
        busy = true; node('issue').disabled = true; node('back').disabled = true;
        const target = record;
        message('status', 'Issuing and saving Medical Clearance...');
        try {
            const data = await json(target, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                body: JSON.stringify({ request_key: requestKey, issued_on: node('date').value, purpose: node('purpose').value, other_purpose: node('other').value.trim(), submission_id: options.submission_id,
                    doctor_review: { category: node('selected').value, findings: node('findings').value, decision: 'issue', remarks: node('remarks').value.trim() } }) });
            if (record !== target) return;
            issued = true; node('document').href = data.document.url; node('document').hidden = false;
            message('status', 'Medical Clearance issued and saved to Documents. The existing Health Form approval was not changed.');
        } catch (error) { if (record === target) { message('status', error.message); node('issue').disabled = false; } }
        finally { if (record === target) { busy = false; node('back').disabled = false; } }
    });
    node('purpose').addEventListener('change', other); node('date').addEventListener('change', patient);
    document.addEventListener('click', event => { if (!bubble.hidden && !bubble.contains(event.target) && !trigger.contains(event.target)) close(); });
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && !bubble.hidden) { event.preventDefault(); event.stopImmediatePropagation(); close(); trigger.focus(); } }, true);
    window.addEventListener('resize', position); window.addEventListener('scroll', position, true);
})();
