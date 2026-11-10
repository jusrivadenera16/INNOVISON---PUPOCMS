<dialog id="clinicStudentFormChoice" class="clinic-student-form-choice" aria-labelledby="clinicStudentFormChoiceTitle">
    <header><div class="clinic-student-form-heading"><span class="clinic-student-form-heading-icon" aria-hidden="true"><x-outline-icon name="document-text" /></span><div><h2 id="clinicStudentFormChoiceTitle">Request New Health Form</h2><p>Choose how to start the student's new Health Information Form.</p></div></div><button type="button" data-clinic-choice-close aria-label="Close choices"><x-outline-icon name="x-mark" /></button></header>
    <div class="clinic-student-form-options">
        <button type="button" data-clinic-form-choice="start"><x-outline-icon name="document-text" /><span><strong>Start New Health Form</strong><small>Use saved student information and go directly to Pending Approval. Clinic staff can review and update details before approval.</small></span></button>
        <button type="button" data-clinic-form-choice="send"><x-outline-icon name="bell" /><span><strong>Send Request</strong><small>Ask the student to use existing information or update their form, then submit through their account.</small></span></button>
    </div>
    <footer><button type="button" data-clinic-choice-close>Cancel</button></footer>
</dialog>
<style>
    .clinic-student-form-choice { width: min(620px, calc(100% - 28px)); max-height: calc(100dvh - 28px); margin: auto; padding: 0; border: 1px solid #facc15; border-radius: 16px; background: #fff; color: #172033; overflow: auto; }
    .clinic-student-form-choice::backdrop { background: rgba(15, 23, 42, .7); backdrop-filter: blur(5px); }
    .clinic-student-form-choice header { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 18px 20px; background: linear-gradient(135deg, #70131b, #8f2230); color: #fff; }
    .clinic-student-form-heading { display: flex; align-items: center; gap: 12px; min-width: 0; }
    .clinic-student-form-heading-icon { display: grid; place-items: center; flex: 0 0 48px; width: 48px; height: 48px; border: 1px solid rgba(255, 255, 255, .26); border-radius: 10px; background: rgba(255, 255, 255, .1); color: #fff; }
    .clinic-student-form-choice h2 { margin: 0; font-size: 18px; font-weight: 900; color: #fff; }
    .clinic-student-form-choice header p { margin: 4px 0 0; color: rgba(255, 255, 255, .88); font-size: 13px; line-height: 1.4; }
    .clinic-student-form-choice header button { display: grid; place-items: center; flex: 0 0 40px; width: 40px; height: 40px; padding: 0; border: 1px solid #8f2230; border-radius: 50%; background: linear-gradient(135deg, #70131b, #8f2230); color: #fff; box-shadow: 0 8px 18px rgba(77, 13, 23, .24); cursor: pointer; }
    .clinic-student-form-choice svg { width: 22px; height: 22px; flex-shrink: 0; }
    .clinic-student-form-choice header button svg { width: 16px; height: 16px; }
    .clinic-student-form-options { display: grid; gap: 12px; padding: 22px; }
    .clinic-student-form-options > button { display: flex; align-items: flex-start; gap: 14px; padding: 18px; border: 1px solid #e3c4cc; border-radius: 11px; background: #fffaf7; color: #70131b; text-align: left; cursor: pointer; }
    .clinic-student-form-options strong, .clinic-student-form-options small { display: block; }
    .clinic-student-form-options strong { font-size: 15px; margin-bottom: 5px; }
    .clinic-student-form-options small { color: #566176; font-size: 12px; line-height: 1.6; }
    .clinic-student-form-choice footer { padding: 0 22px 20px; text-align: right; }
    .clinic-student-form-choice footer button { padding: 9px 20px; border: 1px solid #d5dbe6; border-radius: 8px; background: transparent; color: inherit; cursor: pointer; }
    html[data-theme="dark"] .clinic-student-form-choice { background: #111827; color: #f1f5f9; }
    html[data-theme="dark"] .clinic-student-form-options > button { background: #271421; border-color: #69404d; color: #fff7ed; }
    html[data-theme="dark"] .clinic-student-form-options small { color: #cbd5e1; }
    .clinic-student-form-choice button { position: relative; overflow: hidden; transition: transform .18s ease, background .18s ease, color .18s ease, border-color .18s ease, box-shadow .18s ease; }
    .clinic-student-form-choice button::after { content: ""; position: absolute; top: -35%; left: -72%; width: 48%; height: 170%; background: linear-gradient(120deg, transparent 0%, rgba(255, 244, 180, .18) 34%, rgba(255, 244, 180, .58) 50%, rgba(255, 244, 180, .18) 66%, transparent 100%); transform: skewX(-18deg); transition: left .48s ease; pointer-events: none; }
    .clinic-student-form-choice button:is(:hover, :focus-visible), html[data-theme="dark"] .clinic-student-form-choice button:is(:hover, :focus-visible) { background: #facc15; border-color: #facc15; color: #70131b; transform: translateY(-1px); box-shadow: 0 10px 22px rgba(250, 204, 21, .22); outline: none; }
    .clinic-student-form-choice button:is(:hover, :focus-visible)::after { left: 128%; }
    .clinic-student-form-options > button:is(:hover, :focus-visible) small { color: #70131b; }
    @media (prefers-reduced-motion: reduce) { .clinic-student-form-choice button, .clinic-student-form-choice button::after { transition: none; } .clinic-student-form-choice button:is(:hover, :focus-visible) { transform: none; } }
</style>
<script>
    (function () {
        const dialog = document.getElementById('clinicStudentFormChoice');
        document.body.appendChild(dialog);
        let onChoose;
        window.openClinicStudentFormChoice = function (callback) { onChoose = callback; dialog.showModal(); };
        dialog.querySelectorAll('[data-clinic-choice-close]').forEach(button => button.addEventListener('click', () => dialog.close()));
        dialog.querySelectorAll('[data-clinic-form-choice]').forEach(button => button.addEventListener('click', function () {
            dialog.close();
            if (typeof onChoose === 'function') onChoose(this.dataset.clinicFormChoice);
        }));
        window.configureClinicStudentForm = function (form, mode) {
            form.querySelector('[name="clinic_action"]').value = mode;
            const title = mode === 'start' ? 'Start New Health Form' : 'Send Request';
            form.querySelector('[data-clinic-submit-label]').textContent = title;
            const container = form.closest('[data-clinic-student-modal]');
            container.querySelector('[data-clinic-form-heading]').textContent = title;
            container.querySelector('[data-clinic-form-description]').textContent = mode === 'start'
                ? 'Use saved student information and go directly to Pending Approval. No student login is needed.'
                : 'Ask the student to submit a new Health Information Form through their account.';
            form.dataset.loaderMessage = mode === 'start' ? 'Starting new Health Form' : 'Sending Health Form request';
            form.dataset.loaderDetail = mode === 'start' ? 'Using saved student information and preparing Pending Approval.' : 'Creating the request for the student.';
        };
    })();
</script>
