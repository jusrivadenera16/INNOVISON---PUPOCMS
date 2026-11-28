(() => {
    'use strict';
    const root = document.querySelector('[data-approved-clearance]');
    if (!root) return;
    const node = key => root.querySelector(`[data-approved-clearance-${key}]`);
    const bubble = node('bubble'), trigger = node('open');
    let record = null, options = null, token = null, starting = false, saving = null;
    const typeDropdown = window.CategoryBubbleSelect?.create({select: node('type'), trigger: node('type-trigger'), menu: node('type-menu'), onToggle: position, onChange: updateContinue});
    const categoryDropdown = window.CategoryBubbleSelect?.create({select: node('editor-category'), trigger: node('editor-category-trigger'), menu: node('editor-category-menu'), onToggle: position});
    function message(text) { node('bubble-status').textContent = text; node('bubble-status').hidden = !text; }
    function categories(data = options) {
        return data?.health_form_category_options || (data?.health_form_categories || []).map(value => ({value, label: value}));
    }
    function clearanceChoices(data = options) {
        return (data?.purpose_options || []).map(item => ({...item,
            category: (data.purpose_categories || []).find(link => link.value === item.value)?.category,
        })).filter(item => categories(data).some(category => category.value === item.category));
    }
    function updateContinue() {
        node('continue').disabled = !options?.can_start_review || starting || Boolean(saving)
            || node('editor')?.hidden === false || !clearanceChoices().some(item => item.value === node('type').value);
    }
    function populate(data, purpose = '', category = '') {
        options = data;
        const choices = clearanceChoices(data);
        node('type').replaceChildren();
        for (const item of [{value: '', label: 'Select Clearance Type'}, ...choices]) {
            const option = document.createElement('option'); option.value = item.value; option.textContent = item.label; node('type').append(option);
        }
        const selected = purpose || data.default_purpose_value || '';
        node('type').value = choices.some(item => item.value === selected) ? selected : '';
        node('type').disabled = !data.can_start_review || !choices.length;
        typeDropdown?.refresh();
        if (node('editor-category')) {
            node('editor-category').replaceChildren();
            for (const item of [{value: '', label: 'Select Health Form Category'}, ...categories(data)]) {
                const option = document.createElement('option'); option.value = item.value; option.textContent = item.label; node('editor-category').append(option);
            }
            node('editor-category').value = categories(data).some(item => item.value === category) ? category : '';
            node('editor-category').disabled = false;
            categoryDropdown?.refresh();
        }
        updateContinue(); position();
    }
    function edit() {
        if (!options?.can_manage_configuration || saving) return;
        node('editor-save').disabled = false;
        node('category-save').disabled = false;
        node('editor').hidden = false;
        node('category-editor').hidden = true;
        node('editor-name').value = '';
        const selected = clearanceChoices().find(item => item.value === node('type').value);
        node('editor-category').value = selected?.category || '';
        categoryDropdown?.refresh(); typeDropdown?.close(); updateContinue(); position(); node('editor-name').focus();
    }
    async function saveChoice(kind = 'type') {
        if (!record || node('editor')?.hidden || saving || !options?.can_manage_configuration) return;
        if (kind === 'category' && node('category-editor').hidden) return;
        const name = node(kind === 'category' ? 'category-name' : 'editor-name').value.trim();
        const category = node('editor-category').value;
        if (!name) { message('Enter a name first.'); return; }
        if (kind === 'type' && !node('category-editor').hidden) { message('Save or cancel the new Health Form Category first.'); return; }
        const categoryRecord = (options.health_form_category_catalog || []).find(item => item.name === category);
        if (kind === 'type' && (!categoryRecord || !categories().some(item => item.value === category))) { message('Select or add a Health Form Category first.'); return; }
        const current = record, currentToken = token, operation = {};
        const body = kind === 'category' ? {name, audiences: [options.category_audience]} : {
            name, count_placement: 'parent', source_category_id: categoryRecord.id,
            source_key: current.type === 'student' ? 'student_health_form_category' : 'employee_health_form_category',
        };
        saving = operation; node('editor-save').disabled = true; node('category-save').disabled = true; updateContinue(); message('Saving...');
        try {
            const response = await fetch(kind === 'category' ? root.dataset.categoryStore : root.dataset.typeStore, {
                method: 'POST', credentials: 'same-origin',
                headers: {Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''},
                body: JSON.stringify(body),
            });
            const result = await response.json();
            if (record !== current || token !== currentToken) return;
            if (!response.ok) throw new Error(Object.values(result.errors || {}).flat()[0] || result.message || 'Unable to save.');
            const refresh = await fetch(current.url, {credentials: 'same-origin', headers: {Accept: 'application/json'}});
            const data = await refresh.json();
            if (record !== current || token !== currentToken) return;
            if (!refresh.ok) throw new Error('Saved. Reopen this dialog to refresh the choices.');
            populate(data, kind === 'type' ? result.purpose_value : node('type').value, kind === 'category' ? result.category.name : category);
            if (kind === 'category') node('category-editor').hidden = true;
            else node('editor').hidden = true;
            message(result.message); position();
        } catch (error) { if (record === current && token === currentToken) message(error.message); }
        finally {
            if (saving === operation) {
                saving = null;
                if (record === current && token === currentToken) { node('editor-save').disabled = false; node('category-save').disabled = false; updateContinue(); }
            }
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
        token = null; typeDropdown?.close(); categoryDropdown?.close();
        if (node('editor')) node('editor').hidden = true;
        if (node('category-editor')) node('category-editor').hidden = true;
        if (!bubble.hidden) bubble.hidePopover?.();
        bubble.hidden = true; trigger.setAttribute('aria-expanded', 'false');
    }
    window.ApprovedClearanceReview = {configure(type, id) {
        close(); options = null; starting = false; saving = null;
        record = ['student', 'employee'].includes(type) && Number(id) > 0
            ? {type, id: Number(id), url: `${root.dataset.base}/${type}/${Number(id)}`} : null;
        root.hidden = !record;
    }};
    trigger.addEventListener('click', async () => {
        if (!record) return;
        if (!bubble.hidden) { close(); return; }
        const loading = {}; token = loading; const target = record; options = null;
        const placeholder = document.createElement('option'); placeholder.value = ''; placeholder.textContent = 'Select Clearance Type';
        node('type').replaceChildren(); node('type').append(placeholder); node('type').disabled = true;
        node('continue').disabled = true; typeDropdown?.refresh(); message('Loading Clearance Types...');
        bubble.hidden = false; bubble.showPopover?.(); trigger.setAttribute('aria-expanded', 'true'); position();
        try {
            const response = await fetch(target.url, {credentials:'same-origin', headers:{Accept:'application/json'}});
            const data = await response.json();
            if (token !== loading) return;
            if (!response.ok) throw new Error(data.message || 'Unable to load Clearance Types.');
            populate(data);
            if (!data.can_start_review) throw new Error('No eligible approved Health Form is available or you do not have permission to start a request. Check Pending Approval for an existing request.');
            message(clearanceChoices().length ? '' : 'No active Clearance Types are configured for this patient type.');
            position(); typeDropdown ? typeDropdown.focus() : node('type').focus();
        } catch (error) { if (token === loading) { message(error.message); position(); } }
    });
    node('continue').addEventListener('click', event => {
        event.preventDefault();
        if (!record || !options?.can_start_review || starting || saving || node('editor')?.hidden === false) return;
        const choice = clearanceChoices().find(item => item.value === node('type').value);
        if (!choice) { message('Select a Clearance Type first.'); return; }
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!options.start_review_url || !csrf) { message('Unable to start this request. Reload the page and try again.'); position(); return; }
        // Use the existing clinic-start endpoint. It persists a submitted request before
        // opening Final Review; the submitted request remains in Pending Approval.
        const form = document.createElement('form');
        form.method = 'POST'; form.action = options.start_review_url; form.hidden = true;
        const fields = {_token: csrf, category: choice.category, review_after_start: '1', clearance_purpose: choice.value};
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
    node('type').addEventListener('change', updateContinue);
    node('add-type')?.addEventListener('click', edit);
    node('add-category')?.addEventListener('click', () => {
        if (!options?.can_manage_configuration || saving || node('editor').hidden) return;
        node('category-editor').hidden = false; node('category-name').value = '';
        categoryDropdown?.close(); position(); node('category-name').focus();
    });
    node('editor-save')?.addEventListener('click', () => saveChoice());
    node('category-save')?.addEventListener('click', () => saveChoice('category'));
    node('editor-name')?.addEventListener('keydown', event => {
        if (event.key === 'Enter') { event.preventDefault(); saveChoice(); }
    });
    node('editor-cancel')?.addEventListener('click', () => { if (!saving) { node('editor').hidden = true; updateContinue(); message(''); position(); } });
    node('category-cancel')?.addEventListener('click', () => { if (!saving) { node('category-editor').hidden = true; position(); } });
    node('category-name')?.addEventListener('keydown', event => {
        if (event.key === 'Enter') { event.preventDefault(); saveChoice('category'); }
    });
    document.addEventListener('click', event => { if (!bubble.hidden && !bubble.contains(event.target) && !trigger.contains(event.target)) close(); });
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && !bubble.hidden) { event.preventDefault(); event.stopImmediatePropagation(); close(); trigger.focus(); } }, true);
    window.addEventListener('resize', position); window.addEventListener('scroll', position, true);
})();
