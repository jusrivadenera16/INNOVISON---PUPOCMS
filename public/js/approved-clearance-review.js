(() => {
    'use strict';
    const root = document.querySelector('[data-approved-clearance]');
    if (!root) return;
    const node = key => root.querySelector(`[data-approved-clearance-${key}]`);
    const bubble = node('bubble'), trigger = node('open');
    let record = null, options = null, token = null, starting = false, editorMode = null, saving = false;
    const categoryDropdown = window.CategoryBubbleSelect?.create({select: node('category'), trigger: node('category-trigger'), menu: node('category-menu'), onToggle: position, onChange: categoryChanged});
    function message(text) { node('bubble-status').textContent = text; node('bubble-status').hidden = !text; }
    function populate(data, category = '', purpose = '') {
        options = data;
        const categories = data.health_form_category_options || data.health_form_categories.map(value => ({value, label: value}));
        node('category').replaceChildren();
        for (const item of [{value: '', label: 'Select a category'}, ...categories]) {
            const option = document.createElement('option'); option.value = item.value; option.textContent = item.label; node('category').append(option);
        }
        const selectedCategory = category || data.default_category || '';
        if (categories.some(item => item.value === selectedCategory)) node('category').value = selectedCategory;
        node('category').disabled = !data.can_start_review || !categories.length;
        node('continue').disabled = node('category').disabled;
        if (node('type')) {
            node('type').replaceChildren();
            for (const item of [{value: '', label: 'Use category default'}, ...(data.purpose_options || [])]) {
                const option = document.createElement('option'); option.value = item.value; option.textContent = item.label; node('type').append(option);
            }
            node('type').value = purpose;
            selectCategoryDefault();
        }
        categoryDropdown?.refresh(); position();
    }
    function selectCategoryDefault() {
        const type = node('type');
        if (!type || type.value) return;
        const matches = (options?.purpose_categories || []).filter(item => item.category === node('category').value);
        if (matches.length === 1) type.value = matches[0].value;
    }
    function categoryChanged() { if (node('type')) node('type').value = ''; selectCategoryDefault(); }
    function edit(mode) {
        if (!options?.can_manage_configuration || saving) return;
        editorMode = mode;
        node('editor-save').disabled = false;
        node('editor').hidden = false;
        node('editor-label').textContent = mode === 'type' ? 'New Clearance Type' : 'New Health Form Category';
        node('editor-name').value = '';
        node('editor-help').textContent = mode === 'type'
            ? 'Select the Health Form category above to link this clearance type.'
            : 'This category will be available for this patient type and in Medical Configuration.';
        categoryDropdown?.close(); position(); node('editor-name').focus();
    }
    async function saveChoice() {
        if (!record || !editorMode || saving || !options?.can_manage_configuration) return;
        const name = node('editor-name').value.trim(), category = node('category').value;
        if (!name) { message('Enter a name first.'); return; }
        const categoryRecord = (options.health_form_category_catalog || []).find(item => item.name === category);
        if (editorMode === 'type' && !categoryRecord) { message('Select or add a Health Form category first.'); return; }
        const current = record, currentToken = token, mode = editorMode;
        const body = mode === 'category' ? {name, audiences: [options.category_audience]} : {
            name, count_placement: 'parent', source_category_id: categoryRecord.id,
            source_key: current.type === 'student' ? 'student_health_form_category' : 'employee_health_form_category',
        };
        saving = true; node('editor-save').disabled = true; node('continue').disabled = true; message('Saving...');
        try {
            const response = await fetch(mode === 'category' ? root.dataset.categoryStore : root.dataset.typeStore, {
                method: 'POST', credentials: 'same-origin',
                headers: {Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''},
                body: JSON.stringify(body),
            });
            const result = await response.json();
            if (!response.ok) throw new Error(Object.values(result.errors || {}).flat()[0] || result.message || 'Unable to save.');
            const refresh = await fetch(current.url, {credentials: 'same-origin', headers: {Accept: 'application/json'}});
            const data = await refresh.json();
            if (record !== current || token !== currentToken) return;
            if (!refresh.ok) throw new Error('Saved. Reopen this dialog to refresh the choices.');
            populate(data, mode === 'category' ? result.category.name : category, result.purpose_value || '');
            node('editor').hidden = true; message(result.message); position();
        } catch (error) { if (record === current && token === currentToken) message(error.message); }
        finally {
            saving = false;
            if (record === current && token === currentToken) { node('editor-save').disabled = false; node('continue').disabled = node('category').disabled; }
        }
    }
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
        if (node('editor')) node('editor').hidden = true;
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
        const loading = {}; token = loading; const target = record; options = null;
        node('category').replaceChildren(); node('category').disabled = true;
        node('continue').disabled = true; categoryDropdown?.refresh(); message('Loading Health Form categories...');
        bubble.hidden = false; bubble.showPopover?.(); trigger.setAttribute('aria-expanded', 'true'); position();
        try {
            const response = await fetch(target.url, {credentials:'same-origin', headers:{Accept:'application/json'}});
            const data = await response.json();
            if (token !== loading) return;
            if (!response.ok) throw new Error(data.message || 'Unable to load Health Form categories.');
            populate(data);
            if (!data.can_start_review) throw new Error('No eligible approved Health Form is available or you do not have permission to start a request. Check Pending Approval for an existing request.');
            message(data.health_form_categories.length ? '' : 'No active categories are available for this patient type.');
            position(); categoryDropdown ? categoryDropdown.focus() : node('category').focus();
        } catch (error) { if (token === loading) { message(error.message); position(); } }
    });
    node('continue').addEventListener('click', event => {
        event.preventDefault();
        if (!record || !options?.can_start_review || starting || saving) return;
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
        if (node('type')?.value) fields.clearance_purpose = node('type').value;
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
    node('category').addEventListener('change', categoryChanged);
    node('add-type')?.addEventListener('click', () => edit('type'));
    node('add-category')?.addEventListener('click', () => edit('category'));
    node('editor-save')?.addEventListener('click', saveChoice);
    node('editor-name')?.addEventListener('keydown', event => {
        if (event.key === 'Enter') { event.preventDefault(); saveChoice(); }
    });
    node('editor-cancel')?.addEventListener('click', () => { if (!saving) { node('editor').hidden = true; position(); } });
    document.addEventListener('click', event => { if (!bubble.hidden && !bubble.contains(event.target) && !trigger.contains(event.target)) close(); });
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && !bubble.hidden) { event.preventDefault(); event.stopImmediatePropagation(); close(); trigger.focus(); } }, true);
    window.addEventListener('resize', position); window.addEventListener('scroll', position, true);
})();
