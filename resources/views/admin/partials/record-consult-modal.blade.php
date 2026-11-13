@php
    $consultServiceLabels = app(\App\Services\ConsultationServiceOptions::class)->labels();
@endphp
@push('styles')
<style>
    #recordConsultDialog { width: min(480px, calc(100% - 32px)); padding: 0; border: 1px solid #eccdd0; border-radius: 18px; color: #0f172a; background: #ffffff; box-shadow: 0 24px 60px #0f172a47; }
    #recordConsultDialog::backdrop { background: #0f172a99; }
    #recordConsultDialog form { padding: 24px; }
    #recordConsultDialog .record-service-label { display: block; font-size: 13px; font-weight: 800; margin-bottom: 8px; color: #0f172a; }
    #recordConsultDialog .record-service-picker { border: 1px solid #efc4ca; border-radius: 12px; background: #fffafa; }
    #recordConsultDialog summary { display: flex; align-items: center; justify-content: space-between; gap: 12px; min-height: 44px; padding: 10px 14px; box-sizing: border-box; color: #70131b; cursor: pointer; list-style: none; font-size: 14px; font-weight: 700; }
    #recordConsultDialog summary::-webkit-details-marker { display: none; }
    #recordConsultDialog summary::after { content: ''; width: 7px; height: 7px; flex: 0 0 7px; border-right: 2px solid currentColor; border-bottom: 2px solid currentColor; transform: rotate(45deg); }
    #recordConsultDialog details[open] summary::after { transform: rotate(225deg); }
    #recordConsultDialog fieldset { border: 0; border-top: 1px solid #f1d7da; padding: 6px; margin: 0; max-height: 220px; overflow-y: auto; }
    #recordConsultDialog .record-service-option { display: flex; position: relative; align-items: center; margin: 0; border-radius: 8px; cursor: pointer; }
    #recordConsultDialog .record-service-option input { position: absolute; opacity: 0; width: 1px; height: 1px; }
    #recordConsultDialog .record-service-option span { display: block; width: 100%; padding: 11px 12px; border-radius: 8px; color: #70131b; font-size: 13px; font-weight: 700; }
    #recordConsultDialog .record-service-option:hover span { background: #fff2b3; }
    #recordConsultDialog .record-service-option input:checked + span { background: #70131b; color: #ffffff; }
    #recordConsultDialog .record-service-option input:focus-visible + span { outline: 2px solid #eab308; outline-offset: -2px; }
    #recordConsultDialog .record-consult-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px; }
    #recordConsultDialog .record-consult-actions button { position: relative; overflow: hidden; min-width: 100px; min-height: 42px; padding: 11px 16px; border: 1px solid #e7c8cd; border-radius: 10px; background: #fffafa; color: #70131b; font-size: 13px; font-weight: 800; cursor: pointer; transition: background .2s ease, color .2s ease, border-color .2s ease, box-shadow .2s ease, transform .2s ease; }
    #recordConsultDialog .record-consult-actions button[type="submit"] { background: #70131b; color: #ffffff; border-color: #70131b; }
    #recordConsultDialog .record-consult-actions button::before { content: ''; position: absolute; inset: -45% auto -45% -68%; width: 58%; background: linear-gradient(115deg, transparent, #fff7c299, transparent); transform: skewX(-18deg); transition: left .5s ease; pointer-events: none; }
    #recordConsultDialog .record-consult-actions button:hover,
    #recordConsultDialog .record-consult-actions button:focus-visible { background: #facc15; color: #70131b; border-color: #facc15; box-shadow: 0 6px 16px #eab30833; transform: translateY(-1px); }
    #recordConsultDialog .record-consult-actions button:hover::before,
    #recordConsultDialog .record-consult-actions button:focus-visible::before { left: 112%; }
    #recordConsultDialog :focus-visible { outline: 2px solid #eab308; outline-offset: 3px; }
    html[data-theme="dark"] #recordConsultDialog { background: #172033; color: #f8fafc; border-color: #475569; }
    html[data-theme="dark"] #recordConsultDialog .record-service-label { color: #f8fafc; }
    html[data-theme="dark"] #recordConsultDialog .record-service-picker { background: #0f172a; border-color: #475569; }
    html[data-theme="dark"] #recordConsultDialog summary,
    html[data-theme="dark"] #recordConsultDialog .record-service-option span { color: #f8fafc; }
    html[data-theme="dark"] #recordConsultDialog fieldset { border-color: #475569; }
    html[data-theme="dark"] #recordConsultDialog .record-service-option:hover span { color: #70131b; }
    @media (prefers-reduced-motion: reduce) {
        #recordConsultDialog .record-consult-actions button,
        #recordConsultDialog .record-consult-actions button::before { transition: none; }
        #recordConsultDialog .record-consult-actions button::before { display: none; }
        #recordConsultDialog .record-consult-actions button:hover,
        #recordConsultDialog .record-consult-actions button:focus-visible { transform: none; }
    }
</style>
@endpush
<dialog id="recordConsultDialog" aria-label="Select consultation service">
    <form method="GET" action="{{ route('walkin.form', ['student_id' => $consultPatientIdentifier]) }}">
        <input type="hidden" name="source" value="walkin">
        <span class="record-service-label" id="recordConsultServiceLabel">Clinic Service <span aria-hidden="true">*</span></span>
        <details class="record-service-picker">
            <summary aria-labelledby="recordConsultServiceLabel recordConsultSelected"><span id="recordConsultSelected">Select a service</span></summary>
            <fieldset aria-labelledby="recordConsultServiceLabel">
                @foreach($consultServiceLabels as $label)
                    <label class="record-service-option"><input type="radio" name="service" value="{{ $label }}" required><span>{{ $label }}</span></label>
                @endforeach
            </fieldset>
        </details>
        <div class="record-consult-actions">
            <button type="button" data-record-consult-cancel>Cancel</button>
            <button type="submit">Continue</button>
        </div>
    </form>
</dialog>
@push('scripts')
<script>
(() => {
    const dialog = document.getElementById('recordConsultDialog');
    const trigger = document.querySelector('[data-record-consult-open]');
    if (!dialog || !trigger) return;
    const picker = dialog.querySelector('details');
    const summary = dialog.querySelector('summary');
    // Native dialogs sit above ordinary loader overlays. Close before the
    // valid form submission bubbles to the shared navigation loader.
    dialog.querySelector('form')?.addEventListener('submit', () => {
        dialog.close();
    });
    dialog.querySelectorAll('input[name="service"]').forEach(radio => {
        radio.addEventListener('change', () => {
            dialog.querySelector('#recordConsultSelected').textContent = radio.value;
            picker.open = false;
            summary.focus();
        });
        radio.addEventListener('invalid', event => {
            event.preventDefault();
            picker.open = true;
            summary.focus();
        });
    });
    trigger.addEventListener('click', () => {
        document.getElementById('profileActionsMenu')?.classList.remove('is-open');
        document.getElementById('profileActionsToggle')?.setAttribute('aria-expanded', 'false');
        document.getElementById('employeeProfileActions')?.classList.remove('is-open');
        document.getElementById('employeeProfileActionsToggle')?.setAttribute('aria-expanded', 'false');
        document.getElementById('employeeProfileActionsMenu')?.setAttribute('aria-hidden', 'true');
        dialog.showModal();
        summary?.focus();
    });
    dialog.querySelectorAll('[data-record-consult-close], [data-record-consult-cancel]').forEach(button => {
        button.addEventListener('click', () => dialog.close());
    });
    dialog.addEventListener('click', event => {
        if (event.target !== dialog) return;
        const bounds = dialog.getBoundingClientRect();
        if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) dialog.close();
    });
    dialog.addEventListener('close', () => { picker.open = false; trigger.focus(); });
})();
</script>
@endpush
