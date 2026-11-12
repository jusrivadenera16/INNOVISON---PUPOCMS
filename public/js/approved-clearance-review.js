(() => {
    'use strict';
    const root = document.querySelector('[data-approved-clearance]');
    if (!root) return;
    const node = key => root.querySelector(`[data-approved-clearance-${key}]`);
    const bubble = node('bubble'), trigger = node('open');
    let record = null, options = null, token = null, starting = false;
    const categoryDropdown = window.CategoryBubbleSelect?.create({select: node('category'), trigger: node('category-trigger'), menu: node('category-menu'), onToggle: position});
    function message(text) { node('bubble-status').textContent = text; node('bubble-status').hidden = !text; }
    function position() {
        if (bubble.hidden) return;
        const shell = trigger.closest?.('.applicant-modal-shell');
        const rect = shell ? shell.getBoundingClientRect() : {left: 0, top: 0, width: window.innerWidth, height: window.innerHeight};
        const centerX = rect.left + rect.width / 2, centerY = rect.top + rect.height / 2;
        const below = false;
        bubble.toggleAttribute('data-below', below);
        bubble.style.left = `${Math.max(12, Math.min(centerX - bubble.offsetWidth / 2, window.innerWidth - bubble.offsetWidth - 12))}px`;
        bubble.style.top = `${Math.max(12, Math.min(centerY - bubble.offsetHeight / 2, window.innerHeight - bubble.offsetHeight - 12))}px`;
    }
    function close() {
        token = null; categoryDropdown?.close();
        if (!bubble.hidden) bubble.hidePopover?.();
        bubble.hidden = true; trigger.setAttribute('aria-expanded', 'false');
    }
    window.ApprovedClearanceReview = {configure(type, id) {
        close(); options = null; starting = false;
        record = ['student', 'employee'].includes(type) && Number(id) > 0
            ? {type, id: Number(id), url: `${root.dataset.base}/${type}/${Number(id)}`} : null;
        root.hidden = !record;
    }};
    trigger.addEventListener('click', async () => {
        if (!record) return;
        if (!bubble.hidden) { close(); return; }
        const loading = {}; token = loading; const target = record;
        node('category').replaceChildren(); node('category').disabled = true;
        node('continue').disabled = true; categoryDropdown?.refresh(); message('Loading Health Form categories...');
        bubble.hidden = false; bubble.showPopover?.(); trigger.setAttribute('aria-expanded', 'true'); position();
        try {
            const response = await fetch(target.url, {credentials:'same-origin', headers:{Accept:'application/json'}});
            const data = await response.json();
            if (token !== loading) return;
            if (!response.ok) throw new Error(data.message || 'Unable to load Health Form categories.');
            options = data;
            const categories = data.health_form_category_options || data.health_form_categories.map(value => ({value, label: value}));
            for (const item of [{value: '', label: 'Select a category'}, ...categories]) {
                const option = document.createElement('option'); option.value = item.value; option.textContent = item.label; node('category').append(option);
            }
            node('category').disabled = !data.can_start_review || !categories.length;
            node('continue').disabled = node('category').disabled; categoryDropdown?.refresh();
            if (!data.can_start_review) throw new Error('No eligible approved Health Form is available or you do not have permission to start a request. Check Pending Approval for an existing request.');
            message(data.health_form_categories.length ? '' : 'No active categories are available for this patient type.');
            position(); categoryDropdown ? categoryDropdown.focus() : node('category').focus();
        } catch (error) { if (token === loading) { message(error.message); position(); } }
    });
    node('continue').addEventListener('click', event => {
        event.preventDefault();
        if (!record || !options?.can_start_review || starting) return;
        const category = node('category').value;
        const categories = options.health_form_category_options || options.health_form_categories.map(value => ({value, label: value}));
        if (!categories.some(item => item.value === category)) { message('Select a Health Form category first.'); return; }
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!options.start_review_url || !csrf) { message('Unable to start this request. Reload the page and try again.'); position(); return; }
        // Use the existing clinic-start endpoint. It persists a submitted request before
        // opening Final Review; the submitted request remains in Pending Approval.
        const form = document.createElement('form');
        form.method = 'POST'; form.action = options.start_review_url; form.hidden = true;
        const fields = {_token: csrf, category, review_after_start: '1'};
        if (record.type === 'student') fields.clinic_action = 'start';
        for (const [name, value] of Object.entries(fields)) {
            const input = document.createElement('input'); input.type = 'hidden'; input.name = name; input.value = value; form.append(input);
        }
        document.body.append(form); starting = true; node('continue').disabled = true;
        message('Saving request to Pending Approval...');
        try { form.submit(); }
        catch (error) { form.remove(); starting = false; node('continue').disabled = false; message('Unable to start the request. Please try again.'); }
    });
    node('close').addEventListener('click', close);
    document.addEventListener('click', event => { if (!bubble.hidden && !bubble.contains(event.target) && !trigger.contains(event.target)) close(); });
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && !bubble.hidden) { event.preventDefault(); event.stopImmediatePropagation(); close(); trigger.focus(); } }, true);
    window.addEventListener('resize', position); window.addEventListener('scroll', position, true);
})();
