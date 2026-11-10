@php
    $requestPolicy = app(\App\Services\StudentHealthFormRequestService::class);
    $requestSettings = $requestPolicy->settings();
    $requestYears = old('student_request_years', $requestSettings['eligible_years']);
    $requestPurposeIds = array_map('intval', old('student_request_purposes', $requestSettings['purpose_ids']));
    $requestCourses = old('student_request_course_selection')
        ? old('student_request_courses', [])
        : ($requestSettings['eligible_courses'] ?? array_keys($requestSettings['eligible_years']));
@endphp
<section class="settings-panel student-request-settings">
    <div class="settings-panel-head"><h3><x-outline-icon name="document-text" /> Health Form Requests</h3><span class="preferences-card-status {{ $requestSettings['enabled'] ? 'is-enabled' : '' }}">{{ $requestSettings['enabled'] ? 'Enabled' : 'Disabled' }}</span></div>
    <div class="settings-panel-body">
        <p class="preferences-card-description">Choose which students can request a new Health Form and when.</p>
        <button type="button" class="preferences-closure-open" data-preferences-modal-open="#studentRequestRulesModal"><x-outline-icon name="document-text" /> Configure Request Rules</button>
    </div>
</section>
<div class="preferences-modal student-request-settings" id="studentRequestRulesModal" hidden>
    <section class="preferences-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="studentRequestRulesTitle">
        <div class="preferences-modal-header">
            <x-outline-icon name="document-text" />
            <div><h3 id="studentRequestRulesTitle">Student Health Form Requests</h3><p>Configure allowed purposes, course/year eligibility, and request period.</p></div>
            <button type="button" class="preferences-modal-close" data-preferences-modal-close aria-label="Close request rules"><x-outline-icon name="x-mark" /></button>
        </div>
    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf @method('PUT')
        <input type="hidden" name="student_health_request_settings_form" value="1">
        <input type="hidden" name="student_request_course_selection" value="1">
        <div class="preferences-modal-body">
        <div class="student-request-enable-row">
            <input type="hidden" name="student_request_enabled" value="0">
            <label for="student_request_enabled">
                <input type="checkbox" id="student_request_enabled" name="student_request_enabled" value="1" @checked(old('student_request_enabled', $requestSettings['enabled']))>
                <span>Enabled/Disabled</span>
            </label>
        </div>
        <fieldset class="student-request-purposes"><legend>Allowed Purposes</legend>
            @forelse($requestPolicy->availablePurposes() as $purpose)
                <label><input type="checkbox" name="student_request_purposes[]" value="{{ $purpose->id }}" @checked(in_array((int) $purpose->id, $requestPurposeIds, true))> {{ $purpose->name }}</label>
            @empty
                <p class="settings-field-note">Add an active category available for Students in Manage Health Form Categories first.</p>
            @endforelse
        </fieldset>
        <div class="student-request-period-grid">
            <div class="settings-field"><label for="student_request_starts_on">Request Period Starts (optional)</label><input id="student_request_starts_on" type="date" name="student_request_starts_on" value="{{ old('student_request_starts_on', $requestSettings['starts_on']) }}"></div>
            <div class="settings-field"><label for="student_request_ends_on">Request Period Ends (optional)</label><input id="student_request_ends_on" type="date" name="student_request_ends_on" value="{{ old('student_request_ends_on', $requestSettings['ends_on']) }}"></div>
        </div>
        <div class="student-request-notice"><x-outline-icon name="information-circle" /><p>Choose the year levels allowed for each course. Unchecked courses cannot request. A previous approved Health Form is required, and only one active cycle is allowed per student.</p></div>
        <div class="student-request-course-scroll"><table class="student-request-course-table">
            <thead><tr><th scope="col"><label class="student-request-header-choice"><input type="checkbox" data-request-select-all aria-label="Select all courses"> Course</label></th>@foreach(range(1, 5) as $year)<th scope="col"><label class="student-request-header-choice"><input type="checkbox" data-request-select-year="{{ $year }}" aria-label="Select all checkboxes in Year {{ $year }} column"> Year {{ $year }}</label></th>@endforeach</tr></thead>
            <tbody>@foreach(\App\Services\StudentHealthFormRequestService::COURSES as $code => $name)
                <tr><th scope="row" title="{{ $name }}"><label class="student-request-course-choice"><input type="checkbox" name="student_request_courses[]" value="{{ $code }}" data-request-course="{{ $code }}" @checked(in_array($code, $requestCourses, true))> {{ $code }}</label></th>@foreach(range(1, 5) as $year)
                    <td><input type="checkbox" name="student_request_years[{{ $code }}][]" value="{{ $year }}" data-request-course-code="{{ $code }}" data-request-course-year="{{ $year }}" aria-label="{{ $code }} Year {{ $year }}" @checked(in_array($year, array_map('intval', $requestYears[$code] ?? []), true))></td>
                @endforeach</tr>
            @endforeach</tbody>
        </table></div>
        @foreach($errors->getMessages() as $field => $messages)
            @if(str_starts_with($field, 'student_request_'))<p role="alert" class="student-request-error">{{ $messages[0] }}</p>@endif
        @endforeach
        </div>
        <div class="preferences-modal-footer">
            <button type="button" class="settings-cancel-btn" data-preferences-modal-cancel>Cancel</button>
            <button type="submit" class="settings-save-btn"><x-outline-icon name="check" /> Save Request Settings</button>
        </div>
    </form>
    </section>
</div>
<style>
    .student-request-settings > .settings-panel-body > .preferences-closure-open { position: relative; overflow: hidden; isolation: isolate; }
    .student-request-settings > .settings-panel-body > .preferences-closure-open::after { content: ''; position: absolute; inset: 0; z-index: -1; background: linear-gradient(110deg, transparent, #fff7bd, transparent); transform: translateX(-110%); pointer-events: none; }
    .student-request-settings > .settings-panel-body > .preferences-closure-open:hover,
    .student-request-settings > .settings-panel-body > .preferences-closure-open:focus-visible,
    html[data-theme="dark"] .student-request-settings > .settings-panel-body > .preferences-closure-open:hover,
    html[data-theme="dark"] .student-request-settings > .settings-panel-body > .preferences-closure-open:focus-visible { background: #facc15; color: #70131b; border-color: #facc15; outline: 2px solid transparent; }
    .student-request-settings > .settings-panel-body > .preferences-closure-open:hover::after,
    .student-request-settings > .settings-panel-body > .preferences-closure-open:focus-visible::after { animation: requestRulesButtonSweep .7s ease-out; }
    @keyframes requestRulesButtonSweep { from { transform: translateX(-110%); } to { transform: translateX(110%); } }
    @media (prefers-reduced-motion: reduce) { .student-request-settings > .settings-panel-body > .preferences-closure-open::after { animation: none !important; } }
    #studentRequestRulesModal .preferences-modal-dialog { width: min(860px, 100%); }
    #studentRequestRulesModal form { display: flex; flex-direction: column; min-height: 0; overflow: hidden; }
    #studentRequestRulesModal .preferences-modal-footer { flex-shrink: 0; }
    #studentRequestRulesModal .preferences-modal-footer :is(.settings-save-btn, .settings-cancel-btn):is(:hover, :focus-visible),
    html[data-theme="dark"] #studentRequestRulesModal .preferences-modal-footer :is(.settings-save-btn, .settings-cancel-btn):is(:hover, :focus-visible) {
        background: #facc15 !important;
        border-color: #facc15 !important;
        color: #70131b !important;
        -webkit-text-fill-color: #70131b !important;
    }
    #studentRequestRulesModal .preferences-modal-footer :is(.settings-save-btn, .settings-cancel-btn):is(:hover, :focus-visible) * {
        color: #70131b !important;
        -webkit-text-fill-color: #70131b !important;
    }
    #studentRequestRulesModal .preferences-switch-label { color: var(--preferences-text, #182033); }
    html[data-theme="dark"] #studentRequestRulesModal .preferences-switch-label { color: #f8fafc; }
    .student-request-settings .settings-panel-body { padding: 20px; }
    .student-request-settings .student-request-purposes { border: 0; padding: 10px 0; margin: 12px 0; display: flex; flex-wrap: wrap; gap: 12px; }
    .student-request-settings legend { font-weight: 800; font-size: 13px; }
    .student-request-settings .student-request-purposes label { display: inline-flex; align-items: center; gap: 7px; font-size: 13px; cursor: pointer; }
    .student-request-settings input[type="checkbox"] { width: 16px; height: 16px; accent-color: #801b2d; cursor: pointer; }
    .student-request-course-scroll { overflow-x: auto; margin: 14px 0 20px; border: 1px solid #dbe1ea; border-radius: 9px; }
    .student-request-course-table { width: 100%; min-width: 440px; border-collapse: collapse; font-size: 13px; }
    .student-request-course-table th, .student-request-course-table td { padding: 11px; text-align: center; border-bottom: 1px solid #dbe1ea; }
    .student-request-course-table th:first-child { text-align: left; }
    .student-request-course-table thead { background: #801b2d; color: white; }
    .student-request-error { color: #b91c1c; font-size: 13px; }
    html[data-theme="dark"] .student-request-error { color: #fda4af; }
    html[data-theme="dark"] .student-request-course-scroll,
    html[data-theme="dark"] .student-request-course-table th,
    html[data-theme="dark"] .student-request-course-table td { border-color: #334155; }
    #studentRequestRulesModal .preferences-modal-body { padding: 16px; }
    #studentRequestRulesModal .student-request-enable-row { padding: 10px 13px; border: 1px solid #fde4bb; border-radius: 9px; background: linear-gradient(110deg, #fff7e6, #fff0d7); }
    #studentRequestRulesModal .student-request-enable-row label { display: flex; align-items: center; gap: 12px; margin: 0; cursor: pointer; color: #8b0015; font-size: 12px; font-weight: 800; text-transform: uppercase; }
    #studentRequestRulesModal input[type="checkbox"] { appearance: auto; flex: 0 0 15px; width: 15px; height: 15px; min-height: 15px; padding: 0; margin: 0; vertical-align: middle; }
    #studentRequestRulesModal .student-request-purposes { display: block; min-width: 0; margin: 12px 0; padding: 10px 12px; border: 1px solid #e2e5ed; border-radius: 9px; }
    #studentRequestRulesModal .student-request-purposes legend { padding: 0 7px; border-left: 4px solid #990019; font-size: 13px; color: #8b0015; }
    #studentRequestRulesModal .student-request-purposes label { margin: 3px 18px 3px 0; font-size: 12px; }
    #studentRequestRulesModal .student-request-period-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; margin: 12px 0; }
    #studentRequestRulesModal .student-request-period-grid .settings-field { padding: 0; min-width: 0; }
    #studentRequestRulesModal .student-request-period-grid label { display: block; border-left: 4px solid #990019; margin-bottom: 7px; padding-left: 7px; font-size: 11px; font-weight: 800; color: #8b0015; text-transform: uppercase; }
    #studentRequestRulesModal .student-request-period-grid input { box-sizing: border-box; width: 100%; min-width: 0; min-height: 38px; padding: 7px 10px; border: 1px solid #b91c32; border-radius: 7px; background: #fff; color: #182033; font: inherit; font-size: 12px; color-scheme: light; }
    #studentRequestRulesModal .student-request-notice { display: flex; align-items: flex-start; gap: 9px; padding: 9px 11px; border: 1px solid #f5d9d2; border-radius: 7px; background: #fff0eb; }
    #studentRequestRulesModal .student-request-notice svg { flex: 0 0 15px; width: 15px; height: 15px; color: #990019; }
    #studentRequestRulesModal .student-request-notice p { margin: 0; font-size: 11px; line-height: 1.45; }
    #studentRequestRulesModal .student-request-course-scroll { margin: 12px 0 0; border-radius: 8px; }
    #studentRequestRulesModal .student-request-course-table { table-layout: fixed; font-size: 12px; }
    #studentRequestRulesModal .student-request-course-table th,
    #studentRequestRulesModal .student-request-course-table td { padding: 8px; border-right: 1px solid #e2e5ed; }
    #studentRequestRulesModal .student-request-course-table th:first-child { width: 23%; }
    #studentRequestRulesModal .student-request-course-table thead { background: linear-gradient(110deg, #8b0015, #a3001b); }
    #studentRequestRulesModal .student-request-course-table thead th { color: #fff; }
    #studentRequestRulesModal .student-request-header-choice { display: inline-flex; align-items: center; justify-content: center; gap: 7px; margin: 0; color: #fff; font: inherit; cursor: pointer; white-space: nowrap; }
    #studentRequestRulesModal .student-request-course-choice { display: flex; align-items: center; gap: 7px; margin: 0; font: inherit; cursor: pointer; }
    #studentRequestRulesModal input[type="checkbox"]:disabled { opacity: .35; cursor: not-allowed; }
    #studentRequestRulesModal .student-request-course-table tbody tr:nth-child(even) { background: #f8fafc; }
    html[data-theme="dark"] #studentRequestRulesModal .student-request-enable-row { background: #3c2617; border-color: #6b4626; }
    html[data-theme="dark"] #studentRequestRulesModal .student-request-enable-row label,
    html[data-theme="dark"] #studentRequestRulesModal .student-request-purposes legend,
    html[data-theme="dark"] #studentRequestRulesModal .student-request-period-grid label { color: #fde68a; }
    html[data-theme="dark"] #studentRequestRulesModal .student-request-purposes { border-color: #334155; }
    html[data-theme="dark"] #studentRequestRulesModal .student-request-period-grid input { background: #1e293b; color: #f8fafc; border-color: #934452; color-scheme: dark; }
    html[data-theme="dark"] #studentRequestRulesModal .student-request-notice { background: #331e27; border-color: #663443; color: #e2e8f0; }
    html[data-theme="dark"] #studentRequestRulesModal .student-request-notice svg { color: #fda4af; }
    html[data-theme="dark"] #studentRequestRulesModal .student-request-course-table tbody tr:nth-child(even) { background: #172235; }
    html[data-theme="dark"] #studentRequestRulesModal .student-request-course-table th,
    html[data-theme="dark"] #studentRequestRulesModal .student-request-course-table td { border-color: #334155; }
    @media (max-width: 540px) { #studentRequestRulesModal .student-request-period-grid { grid-template-columns: 1fr; } }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('studentRequestRulesModal');
    const cells = Array.from(modal.querySelectorAll('[data-request-course-year]'));
    const all = modal.querySelector('[data-request-select-all]');
    const courses = Array.from(modal.querySelectorAll('[data-request-course]'));
    const years = Array.from(modal.querySelectorAll('[data-request-select-year]'));
    function syncHeader(checkbox, items) {
        const count = items.filter(function (item) { return item.checked; }).length;
        checkbox.checked = items.length > 0 && count === items.length;
        checkbox.indeterminate = count > 0 && count < items.length;
    }
    function syncHeaders() {
        syncHeader(all, courses);
        years.forEach(function (header) {
            syncHeader(header, cells.filter(function (cell) { return cell.dataset.requestCourseYear === header.dataset.requestSelectYear; }));
        });
    }
    all.addEventListener('change', function () {
        courses.forEach(function (course) { course.checked = all.checked; });
        syncHeaders();
    });
    courses.forEach(function (course) { course.addEventListener('change', syncHeaders); });
    years.forEach(function (header) {
        header.addEventListener('change', function () {
            cells.filter(function (cell) { return cell.dataset.requestCourseYear === header.dataset.requestSelectYear; })
                .forEach(function (cell) { cell.checked = header.checked; });
            syncHeaders();
        });
    });
    cells.forEach(function (cell) { cell.addEventListener('change', syncHeaders); });
    modal.querySelector('form').addEventListener('reset', function () { window.setTimeout(syncHeaders, 0); });
    syncHeaders();
});
</script>
@if(collect(array_keys($errors->getMessages()))->contains(fn ($field) => str_starts_with($field, 'student_request_')))
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('studentRequestRulesModal').hidden = false;
    document.body.classList.add('modal-open');
});
</script>
@endif
