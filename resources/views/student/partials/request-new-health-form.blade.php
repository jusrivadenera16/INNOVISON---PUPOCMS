@if($studentRequestPurposes->isNotEmpty())
    <dialog id="studentNewCycleDialog" class="student-new-cycle-dialog" aria-labelledby="studentNewCycleTitle">
        <form method="POST" action="{{ route('student.health_form.request') }}">
            @csrf
            <div class="profile-edit-modal-head">
                <div><span class="profile-edit-modal-kicker">Health Form Request</span><h2 id="studentNewCycleTitle">Request New Health Form</h2></div>
                <button type="button" class="profile-edit-close" onclick="this.closest('dialog').close()" aria-label="Close request"><x-outline-icon name="x-mark" /></button>
            </div>
            <div class="profile-edit-modal-body">
                <p>Your submitted form will go to the clinic for assessment and final approval.</p>
                <label class="profile-edit-field"><span>Purpose</span><select name="purpose_id" required>
                    <option value="" disabled selected>Select purpose</option>
                    @foreach($studentRequestPurposes as $purpose)<option value="{{ $purpose->id }}">{{ $purpose->name }}</option>@endforeach
                </select></label>
                <fieldset class="student-cycle-choices"><legend>Information for this Health Form</legend>
                    <label><input type="radio" name="information_handling" value="existing" checked><span><strong>Use Existing Information</strong><small>Submit your saved information directly for clinic approval.</small></span></label>
                    <label><input type="radio" name="information_handling" value="update"><span><strong>Update My Information</strong><small>Open your prefilled form, edit your information, then submit.</small></span></label>
                </fieldset>
            </div>
            <div class="profile-edit-modal-footer"><button type="submit" class="profile-action-btn save" data-student-cycle-submit>Submit Existing Information</button></div>
        </form>
    </dialog>
    <style>
        .health-doc-history-request > span { flex-wrap: wrap; }
        .health-doc-history-request .health-doc-request-link { display: inline; padding: 0; border: 0; background: transparent; color: #801b2d; font: inherit; font-size: 14px; font-weight: 800; text-decoration: underline; text-underline-offset: 3px; cursor: pointer; }
        .health-doc-history-request .health-doc-request-link:hover,
        .health-doc-history-request .health-doc-request-link:focus-visible { color: #a16207; }
        html[data-theme="dark"] .health-doc-history-request .health-doc-request-link { color: #facc15; }
        .student-new-cycle-dialog { width: min(600px, calc(100% - 24px)); max-height: calc(100dvh - 24px); padding: 0; margin: auto; border: 1px solid #facc15; border-radius: 14px; background: #fffaf7; color: #182033; overflow-y: auto; }
        .student-new-cycle-dialog::backdrop { background: rgba(15,23,42,.7); backdrop-filter: blur(6px); }
        .student-new-cycle-dialog h2 { color: white !important; }
        .student-cycle-choices { margin: 0; padding: 0; border: 0; min-width: 0; }
        .student-cycle-choices legend { font-size: 13px; font-weight: 800; margin-bottom: 8px; }
        .student-cycle-choices label { display: flex; align-items: flex-start; gap: 10px; border: 1px solid #cbd5e1; border-radius: 9px; margin-top: 9px; padding: 14px; cursor: pointer; }
        .student-cycle-choices label:has(input:checked) { border-color: #801b2d; background: #fff8e1; }
        .student-cycle-choices input { flex: 0 0 16px; width: 16px; height: 16px; margin-top: 3px; accent-color: #801b2d; }
        .student-cycle-choices span { min-width: 0; }
        .student-cycle-choices strong, .student-cycle-choices small { display: block; line-height: 1.5; }
        .student-cycle-choices small { font-size: 12px; margin-top: 4px; }
        html[data-theme="dark"] .student-new-cycle-dialog { background: #111827; color: #f8fafc; }
        html[data-theme="dark"] .student-cycle-choices label:has(input:checked) { background: #391520; border-color: #facc15; }
    </style>
    <script>
        (function () {
            const dialog = document.getElementById('studentNewCycleDialog');
            const button = dialog.querySelector('[data-student-cycle-submit]');
            dialog.querySelectorAll('[name="information_handling"]').forEach(function (radio) {
                radio.addEventListener('change', function () {
                    button.textContent = this.value === 'update' ? 'Fill Up New Health Form' : 'Submit Existing Information';
                });
            });
            dialog.querySelector('form').addEventListener('submit', function () { button.disabled = true; button.textContent = 'Processing...'; });
        })();
    </script>
@endif
