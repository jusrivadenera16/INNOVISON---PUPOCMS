<style>
    .school-year-reminder-dialog {
        width: min(100%, 420px);
        max-height: calc(100vh - 40px);
        overflow-y: auto;
        text-align: center;
    }
    .school-year-reminder-current {
        margin: 0 0 14px;
        padding: 12px 14px;
        border: 1px solid rgba(139, 0, 0, .16);
        border-radius: 7px;
        background: #fff7f7;
        text-align: left;
    }
    .school-year-reminder-current small {
        display: block;
        margin-bottom: 4px;
        color: #64748b;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .05em;
        text-transform: uppercase;
    }
    .school-year-reminder-current strong { color: #8B0000; font-size: 21px; }
    .school-year-reminder-update-panel[hidden] { display: none !important; }
    .school-year-reminder-fields {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin: 0 0 16px;
        text-align: left;
    }
    .school-year-reminder-fields label {
        display: block;
        margin-bottom: 6px;
        color: #475569;
        font-size: 12px;
        font-weight: 800;
    }
    .school-year-reminder-fields input {
        box-sizing: border-box;
        width: 100%;
        min-height: 42px;
        border: 1px solid #d1d5db;
        border-radius: 5px;
        padding: 9px 10px;
        color: #111827;
        background: #fff;
        font: inherit;
    }
    .school-year-reminder-fields input[readonly] { color: #8B0000; background: #f9fafb; }
    .school-year-reminder-update-actions { display: grid; gap: 12px; }
    html[data-theme="dark"] .school-year-reminder-current {
        border-color: rgba(250, 204, 21, .2);
        background: rgba(255, 255, 255, .06);
    }
    html[data-theme="dark"] .school-year-reminder-current strong { color: #facc15; }
    html[data-theme="dark"] .school-year-reminder-fields label { color: #cbd5e1; }
    html[data-theme="dark"] .school-year-reminder-fields input {
        border-color: rgba(255, 255, 255, .18);
        color: #f8fafc;
        background: #1f2937;
    }
    html[data-theme="dark"] .school-year-reminder-fields input[readonly] { color: #facc15; }
    @media (max-width: 420px) {
        .school-year-reminder-dialog { padding: 22px 18px; }
        .school-year-reminder-fields { grid-template-columns: 1fr; }
    }
</style>

<div class="admin-logout-confirm is-open school-year-reminder-overlay" id="schoolYearReminderOverlay" aria-hidden="false">
    <section class="admin-logout-confirm-dialog school-year-reminder-dialog" role="dialog" aria-modal="true" aria-labelledby="schoolYearReminderTitle" aria-describedby="schoolYearReminderDescription">
        <span class="admin-logout-confirm-icon" aria-hidden="true">
            <x-outline-icon name="clock" />
        </span>
        <h2 class="admin-logout-confirm-title" id="schoolYearReminderTitle">School Year Check</h2>
        <p class="admin-logout-confirm-copy" id="schoolYearReminderDescription">
            Please confirm whether the configured school year is still current.
        </p>

        <form id="schoolYearReminderForm" action="{{ route('admin.settings.school-year-reminder') }}" method="POST">
            @csrf
            <div class="school-year-reminder-notice-panel" data-school-year-reminder-notice>
                <div class="school-year-reminder-current">
                    <small>Currently configured</small>
                    <strong>{{ $schoolYearReminderRange['from'] }}-{{ $schoolYearReminderRange['to'] }}</strong>
                </div>
                <p class="admin-logout-confirm-copy">
                    Update it if needed. The selected year will apply only to forms started after you save; existing forms and records will not be changed.
                </p>
                <div class="admin-logout-confirm-actions">
                    <button class="admin-logout-confirm-btn is-primary" type="button" data-school-year-reminder-update>
                        <span>Update Now</span>
                    </button>
                    <button class="admin-logout-confirm-btn is-secondary" type="submit" name="action" value="confirm_current">
                        <span>This School Year Is Still Current</span>
                    </button>
                    <button class="admin-logout-confirm-btn is-secondary" type="submit" name="action" value="remind_later">
                        <span>Remind Me in 7 Days</span>
                    </button>
                </div>
            </div>

            <div class="school-year-reminder-update-panel" data-school-year-reminder-update-panel hidden>
                <p class="admin-logout-confirm-copy">Choose the new school year. The ending year will be set to one year after the starting year.</p>
                <div class="school-year-reminder-fields">
                    <div>
                        <label for="schoolYearReminderFrom">From Year</label>
                        <input id="schoolYearReminderFrom" name="academic_year_from" type="number" inputmode="numeric" min="1950" max="2099" step="1" value="{{ $schoolYearReminderRange['from'] }}" required>
                    </div>
                    <div>
                        <label for="schoolYearReminderTo">To Year</label>
                        <input id="schoolYearReminderTo" name="academic_year_to" type="number" min="1951" max="2100" step="1" value="{{ $schoolYearReminderRange['to'] }}" readonly required>
                    </div>
                </div>
                <div class="school-year-reminder-update-actions">
                    <button class="admin-logout-confirm-btn is-secondary" type="button" data-school-year-reminder-back>
                        <span>Back</span>
                    </button>
                    <button class="admin-logout-confirm-btn is-primary" type="submit" name="action" value="update_year">
                        <span>Continue</span>
                    </button>
                </div>
            </div>
        </form>
    </section>
</div>

<script>
(function () {
    const overlay = document.getElementById('schoolYearReminderOverlay');
    const noticePanel = overlay?.querySelector('[data-school-year-reminder-notice]');
    const updatePanel = overlay?.querySelector('[data-school-year-reminder-update-panel]');
    const fromYear = overlay?.querySelector('#schoolYearReminderFrom');
    const toYear = overlay?.querySelector('#schoolYearReminderTo');
    if (!overlay || !noticePanel || !updatePanel || !fromYear || !toYear) return;

    document.body.classList.add('admin-logout-confirm-open');

    const syncToYear = function () {
        const year = Number(fromYear.value);
        if (Number.isInteger(year)) toYear.value = String(year + 1);
    };

    const updateButton = overlay.querySelector('[data-school-year-reminder-update]');
    updateButton?.addEventListener('click', function () {
        noticePanel.hidden = true;
        updatePanel.hidden = false;
        syncToYear();
        fromYear.focus();
    });
    overlay.querySelector('[data-school-year-reminder-back]')?.addEventListener('click', function () {
        updatePanel.hidden = true;
        noticePanel.hidden = false;
        updateButton?.focus();
    });
    fromYear.addEventListener('input', syncToYear);
    overlay.querySelector('#schoolYearReminderForm')?.addEventListener('submit', function (event) {
        if (event.submitter?.value === 'update_year' || updatePanel.hidden === false) syncToYear();
    });
    window.setTimeout(function () {
        updateButton?.focus({ preventScroll: true });
    }, 40);
})();
</script>
