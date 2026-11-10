<style>
    .preferences-settings-page .settings-section-hero,
    .preferences-settings-page .preferences-workflow-content > .preferences-category,
    .preferences-settings-page .preferences-workflow-content > .preferences-category:last-child,
    .preferences-settings-page .preferences-action-grid > .settings-panel {
        box-shadow: 0 7px 20px rgba(75, 20, 35, .07), 0 2px 5px rgba(15, 23, 42, .035);
    }
    html[data-theme="dark"] .preferences-settings-page .settings-section-hero,
    html[data-theme="dark"] .preferences-settings-page .preferences-workflow-content > .preferences-category,
    html[data-theme="dark"] .preferences-settings-page .preferences-workflow-content > .preferences-category:last-child,
    html[data-theme="dark"] .preferences-settings-page .preferences-action-grid > .settings-panel {
        box-shadow: 0 9px 24px rgba(0, 0, 0, .25), 0 2px 6px rgba(0, 0, 0, .15);
    }
    .preferences-settings-page .preferences-workflow-panel { border: 0; background: transparent; box-shadow: none; overflow: visible; }
    .preferences-settings-page .preferences-workflow-panel > .settings-panel-body { padding: 0; }
    .preferences-settings-page .preferences-workflow-content { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; padding: 0; align-items: stretch; }
    .preferences-settings-page .preferences-workflow-content > .preferences-category { position: relative; min-width: 0; padding: 18px; border: 1px solid var(--preferences-line); border-radius: 14px; background: #fff; }
    .preferences-settings-page .preferences-workflow-content > .preferences-category:last-child { grid-column: 1 / -1; padding: 18px; border: 1px solid var(--preferences-line); }
    .preferences-settings-page .preferences-subheading { min-height: 32px; padding: 0; margin: 0 0 10px; border: 0; background: transparent; color: var(--preferences-text); font-size: 14px; gap: 10px; }
    .preferences-settings-page .preferences-subheading > svg { box-sizing: border-box; flex: 0 0 32px; width: 32px; height: 32px; padding: 7px; border-radius: 9px; background: #fff1f2; color: #991b2d; }
    .preferences-settings-page .preferences-card-description,
    .preferences-settings-page .preferences-category > .preferences-card-description { margin: 0 0 16px !important; padding: 0 !important; color: var(--preferences-muted); font-size: 12px; font-weight: 400; line-height: 1.6; }
    .preferences-settings-page .preferences-category--alerts .preferences-subheading { padding-right: 0; }
    .preferences-settings-page .preferences-category--alerts .preferences-card-description { max-width: none; }
    .preferences-settings-page .preferences-admin-grid { position: static; width: auto; margin: 0; }
    .preferences-settings-page .preferences-admin-grid .settings-field { margin: 0; padding: 0; border: 0; min-height: 0; align-content: start; }
    .preferences-settings-page .preferences-category--alerts > .preferences-card-description { margin-bottom: 10px !important; }
    .preferences-settings-page .preferences-switch-row { margin: 0; }
    .preferences-settings-page .preferences-general-grid { gap: 14px; border: 0; }
    .preferences-settings-page .preferences-general-grid .settings-field { min-width: 0; margin: 0; padding: 0; border: 0; }
    .preferences-settings-page .preferences-general-grid .settings-field::before,
    .preferences-settings-page .preferences-general-grid .settings-field::after { display: none !important; }
    .preferences-settings-page .preferences-general-grid .settings-field > label:first-child { display: block; margin: 0 0 7px; font-size: 12px; color: var(--preferences-text); font-weight: 700; }
    #systemPreferencesForm .settings-field input:not([type="checkbox"]):not([type="hidden"]),
    #systemPreferencesForm .settings-field select { box-sizing: border-box; min-width: 0; width: 100%; min-height: 42px; padding: 9px 11px !important; border: 1px solid #d6dce7 !important; border-radius: 9px !important; background: #fff !important; color: #172033; font-size: 13px; font-weight: 500; }
    #systemPreferencesForm .settings-field input:focus,
    #systemPreferencesForm .settings-field select:focus { border-color: #991b2d !important; outline: 2px solid rgba(153, 27, 45, .12); outline-offset: 1px; }
    .preferences-settings-page .academic-year-picker { border: 0; }
    #systemPreferencesForm .academic-year-picker input { padding-right: 36px !important; }
    .preferences-settings-page .academic-year-picker-open { position: absolute; right: 5px; }
    .preferences-settings-page .preferences-student-grid .settings-field:nth-child(n + 4) { border-top: 1px solid var(--preferences-line); padding-top: 15px; margin-top: 3px; }
    .preferences-settings-page .preferences-student-grid .is-quiet-inactive { opacity: .5; }
    #systemPreferencesForm .is-quiet-inactive input { cursor: not-allowed; }
    .preferences-settings-page .preferences-action-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; align-items: stretch; }
    .preferences-settings-page .preferences-action-grid > .settings-panel { display: flex; flex-direction: column; min-width: 0; margin: 0; border-radius: 14px; }
    .preferences-settings-page .preferences-action-grid .settings-panel-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 15px 18px; }
    .preferences-settings-page .preferences-action-grid .settings-panel-head h3 { display: flex; align-items: center; gap: 9px; font-size: 14px; margin: 0; }
    .preferences-settings-page .preferences-action-grid .settings-panel-body,
    .preferences-settings-page .preferences-action-grid .preferences-closure-body { flex: 1; display: flex; flex-direction: column; align-items: flex-start; gap: 12px; padding: 16px 18px; min-height: 0; }
    .preferences-settings-page .preferences-action-grid .preferences-card-description,
    .preferences-settings-page .preferences-action-grid .preferences-closure-message { margin: 0 !important; font-size: 12px; line-height: 1.6; }
    .preferences-settings-page .preferences-action-grid .preferences-closure-open { margin-top: auto; min-height: 38px; }
    .preferences-settings-page .preferences-action-grid .student-request-settings .preferences-closure-open { margin-top: 0; }
    .preferences-card-status { flex: 0 0 auto; padding: 4px 9px; border-radius: 20px; background: #f1f5f9; color: #64748b; font-size: 10px; font-weight: 700; }
    .preferences-card-status.is-enabled { background: #dcfce7; color: #166534; }
    html[data-theme="dark"] .preferences-settings-page { --preferences-text: #f1f5f9; --preferences-muted: #b5c1d3; }
    html[data-theme="dark"] .preferences-settings-page .preferences-workflow-panel { background: transparent; border: 0; }
    html[data-theme="dark"] .preferences-settings-page .preferences-workflow-content > .preferences-category { background: #111827; border-color: #334155; }
    html[data-theme="dark"] .preferences-settings-page .preferences-subheading { background: transparent; color: #f1f5f9; }
    html[data-theme="dark"] .preferences-settings-page .preferences-subheading > svg { background: #3b1923; color: #fda4af; }
    html[data-theme="dark"] #systemPreferencesForm .settings-field input:not([type="checkbox"]):not([type="hidden"]),
    html[data-theme="dark"] #systemPreferencesForm .settings-field select { background: #172235 !important; border-color: #3a485d !important; color: #f1f5f9; color-scheme: dark; }
    html[data-theme="dark"] .preferences-card-status { background: #253145; color: #cbd5e1; }
    html[data-theme="dark"] .preferences-card-status.is-enabled { background: #133c2c; color: #86efac; }
    @media (max-width: 1080px) {
        .preferences-settings-page .preferences-workflow-content { grid-template-columns: 1fr; }
        .preferences-settings-page .preferences-student-grid { grid-template-columns: repeat(3, minmax(0, 1fr)) !important; }
    }
    @media (max-width: 760px) {
        .preferences-settings-page .preferences-action-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
        .preferences-settings-page .preferences-workflow-content > .preferences-category { padding: 15px; }
        .preferences-settings-page .preferences-workflow-content > .preferences-category:last-child { padding: 15px; }
        .preferences-settings-page .preferences-student-grid { grid-template-columns: 1fr !important; }
        .preferences-settings-page .preferences-student-grid .settings-field:nth-child(n + 4) { border-top: 0; padding-top: 0; margin-top: 0; }
        .preferences-settings-page .preferences-student-grid .settings-field:nth-child(4) { border-top: 1px solid var(--preferences-line); padding-top: 15px; }
        .preferences-settings-page .preferences-admin-grid { right: 15px; top: 19px; }
    }
</style>
