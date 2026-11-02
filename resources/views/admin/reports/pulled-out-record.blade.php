@extends('layouts.admin')

@section('title', 'Pulled Out Record')

@push('styles')
<style>
    .pulled-record-page {
        --pr-maroon: #8f1422;
        --pr-yellow: #facc15;
        --pr-border: #ead8dc;
        --pr-muted: #64748b;
        display: grid;
        gap: 16px;
        padding: 10px;
    }
    .pulled-record-card {
        border: 1px solid var(--pr-border);
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 14px 34px rgba(72,16,27,.08);
    }
    .pulled-record-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 20px 22px;
        color: #fff;
        background: var(--pr-maroon);
        border-radius: 7px 7px 0 0;
    }
    .pulled-record-title { display: flex; align-items: center; gap: 14px; min-width: 0; }
    .pulled-record-title > span {
        display: grid; place-items: center; width: 46px; height: 46px; flex: 0 0 auto;
        border: 1px solid rgba(255,255,255,.28); border-radius: 8px; background: rgba(255,255,255,.1);
    }
    .pulled-record-title svg { width: 24px; height: 24px; }
    .pulled-record-title h1 { margin: 0; font-size: 23px; }
    .pulled-record-title p { margin: 4px 0 0; font-size: 13px; color: rgba(255,255,255,.84); }
    .pulled-record-back {
        display: inline-flex; align-items: center; justify-content: center; min-height: 40px; padding: 0 14px;
        border: 1px solid rgba(255,255,255,.35); border-radius: 7px; color: #fff; text-decoration: none; font-weight: 800;
    }
    .pulled-record-body { padding: 20px 22px; }
    .pulled-record-alert {
        display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 18px; padding: 14px 16px;
        border: 1px solid #efb9c1; border-radius: 8px; color: #74101c; background: #fff1f3;
    }
    .pulled-record-alert strong { display: block; font-size: 14px; }
    .pulled-record-alert span { display: block; margin-top: 3px; font-size: 12px; }
    .pulled-record-grid { display: grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap: 12px; }
    .pulled-record-field { padding: 13px; border: 1px solid #eadde0; border-radius: 8px; background: #fffafa; }
    .pulled-record-field dt { margin-bottom: 6px; color: var(--pr-muted); font-size: 10px; font-weight: 800; text-transform: uppercase; }
    .pulled-record-field dd { margin: 0; color: #172033; font-size: 13px; font-weight: 750; overflow-wrap: anywhere; }
    .pulled-record-section-title { margin: 22px 0 12px; color: #7c111e; font-size: 15px; }
    .pulled-record-docs { display: grid; grid-template-columns: repeat(3,minmax(0,1fr)); gap: 10px; }
    .pulled-record-doc {
        display: flex; align-items: center; justify-content: space-between; gap: 10px; min-height: 58px; padding: 12px;
        border: 1px solid #eadde0; border-radius: 8px; color: #172033; background: #fff; font-size: 12px; font-weight: 750;
    }
    .pulled-record-doc a { color: var(--pr-maroon); font-weight: 850; }
    .pulled-record-restore { margin-top: 20px; padding: 18px; border: 1px solid #eadde0; border-radius: 8px; background: #fffafa; }
    .pulled-record-restore label { display: block; margin-bottom: 8px; color: #7c111e; font-size: 12px; font-weight: 850; }
    .pulled-record-restore textarea {
        width: 100%; min-height: 96px; resize: vertical; padding: 12px; border: 1px solid #dcc8cd; border-radius: 7px;
        color: #172033; background: #fff; font: inherit; font-size: 13px; outline: none;
    }
    .pulled-record-restore textarea:focus { border-color: var(--pr-maroon); box-shadow: 0 0 0 3px rgba(143,20,34,.1); }
    .pulled-record-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 12px; }
    .pulled-record-restore-btn {
        min-height: 42px; padding: 0 18px; border: 1px solid var(--pr-maroon); border-radius: 7px; color: #fff;
        background: var(--pr-maroon); font-weight: 850; cursor: pointer; box-shadow: 0 9px 20px rgba(111,15,25,.18);
        transition: background .2s ease, color .2s ease, transform .2s ease;
    }
    .pulled-record-restore-btn:hover { color: var(--pr-maroon); background: var(--pr-yellow); transform: translateY(-1px); }
    .pullout-error { margin-bottom: 12px; padding: 11px 13px; border: 1px solid #ef9ba7; border-radius: 7px; color: #8b1020; background: #fff0f2; font-size: 12px; }
    [data-theme="dark"] .pulled-record-page { --pr-border: #344157; --pr-muted: #aeb9cc; }
    [data-theme="dark"] .pulled-record-card { background: #111a2d; box-shadow: 0 16px 38px rgba(0,0,0,.38); }
    [data-theme="dark"] .pulled-record-field,
    [data-theme="dark"] .pulled-record-doc,
    [data-theme="dark"] .pulled-record-restore { border-color: #3b4960; background: #192338; }
    [data-theme="dark"] .pulled-record-field dd,
    [data-theme="dark"] .pulled-record-doc { color: #f7f9fd; }
    [data-theme="dark"] .pulled-record-restore textarea { color: #f7f9fd; background: #111a2d; border-color: #46516a; }
    [data-theme="dark"] .pulled-record-alert { color: #ffd6dc; background: #3a1720; border-color: #7d3946; }
    @media (max-width: 1000px) { .pulled-record-grid { grid-template-columns: repeat(2,minmax(0,1fr)); } }
    @media (max-width: 680px) {
        .pulled-record-page { padding: 4px; }
        .pulled-record-head { align-items: flex-start; flex-direction: column; }
        .pulled-record-grid, .pulled-record-docs { grid-template-columns: 1fr; }
        .pulled-record-alert { align-items: flex-start; flex-direction: column; }
        .pulled-record-actions, .pulled-record-restore-btn { width: 100%; }
    }

    .pulled-record-page {
        --pr-surface: #fff;
        --pr-soft: #fff8f9;
        --pr-text: #172033;
        --pr-muted: #64748b;
        --pr-border: #ead8dc;
        gap: 12px;
        max-width: 1320px;
        margin: 0 auto;
        padding: 8px 10px 20px;
        color: var(--pr-text);
    }
    .pulled-record-card {
        overflow: hidden;
        border-radius: 11px;
        border-color: var(--pr-border);
        background: var(--pr-surface);
        box-shadow: 0 10px 28px rgba(72,16,27,.07);
    }
    .pulled-record-head {
        min-height: 64px;
        padding: 12px 18px;
        border-radius: 0;
        background: linear-gradient(110deg, #7c1020 0%, #991b2d 58%, #74101d 100%);
    }
    .pulled-record-title { gap: 12px; }
    .pulled-record-title > span {
        width: 42px;
        height: 42px;
        border-radius: 9px;
    }
    .pulled-record-head .pulled-record-title h1 { margin: 0; color: #fff !important; font-size: clamp(17px, 2vw, 21px); line-height: 1.2; }
    .pulled-record-head .pulled-record-title p { margin: 4px 0 0; color: rgba(255,255,255,.88) !important; font-size: 11px; line-height: 1.4; }
    .pulled-record-body { padding: 12px; }
    .pulled-record-flash {
        margin-bottom: 10px;
        padding: 10px 12px;
        border: 1px solid #b8e4c3;
        border-radius: 8px;
        color: #166534;
        background: #f0fdf4;
        font-size: 12px;
        font-weight: 750;
    }
    .pulled-record-access-alert {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
        padding: 9px 12px;
        border: 1px solid #f2c6ce;
        border-radius: 8px;
        color: #70131b;
        background: #fff1f3;
    }
    .pulled-record-access-icon { display: grid; place-items: center; flex: 0 0 auto; width: 23px; height: 23px; }
    .pulled-record-access-icon svg { width: 20px; height: 20px; }
    .pulled-record-access-copy { min-width: 0; flex: 1 1 auto; }
    .pulled-record-access-copy strong { display: block; font-size: 13px; line-height: 1.35; }
    .pulled-record-access-copy span { display: block; margin-top: 2px; font-size: 11px; line-height: 1.4; }
    .pulled-record-status-pill {
        flex: 0 0 auto;
        padding: 5px 10px;
        border-radius: 999px;
        color: #fff;
        background: #b91c35;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .04em;
    }
    .pulled-record-columns { display: grid; grid-template-columns: minmax(245px, .86fr) minmax(0, 1.34fr); gap: 12px; align-items: start; }
    .pulled-record-column { display: grid; gap: 10px; min-width: 0; }
    .pulled-record-panel { min-width: 0; padding: 10px; border: 1px solid var(--pr-border); border-radius: 9px; background: var(--pr-surface); }
    .pulled-record-panel-title {
        display: flex;
        align-items: center;
        gap: 7px;
        margin: 0 0 9px;
        color: #821322;
        font-size: 14px;
        font-weight: 900;
        line-height: 1.3;
    }
    .pulled-record-panel-title svg { flex: 0 0 auto; width: 15px; height: 15px; }
    .pulled-record-identity { display: flex; align-items: center; gap: 10px; min-width: 0; }
    .pulled-record-identity-icon {
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        color: #861426;
        background: #fde8ed;
    }
    .pulled-record-identity-icon svg { width: 21px; height: 21px; }
    .pulled-record-identity-copy { min-width: 0; }
    .pulled-record-identity-copy strong { display: block; margin-bottom: 4px; color: var(--pr-text); font-size: 14px; line-height: 1.25; overflow-wrap: anywhere; }
    .pulled-record-identity-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 4px 9px; color: var(--pr-muted); font-size: 10px; line-height: 1.4; }
    .pulled-record-identity-meta span { display: inline-flex; align-items: center; gap: 4px; min-width: 0; overflow-wrap: anywhere; }
    .pulled-record-identity-meta svg { flex: 0 0 auto; width: 11px; height: 11px; color: #7c1020; }
    .pulled-record-category { padding: 3px 7px; border-radius: 999px; color: #761222; background: #fbe5e9; font-size: 9px; font-weight: 850; }
    .pulled-record-medical-list { display: grid; gap: 6px; }
    .pulled-record-medical-row { display: grid; grid-template-columns: 25px minmax(0,1fr); align-items: center; gap: 7px; min-height: 38px; padding: 6px 8px; border: 1px solid #e7eaf0; border-radius: 6px; background: var(--pr-soft); }
    .pulled-record-medical-row > span { display: grid; place-items: center; color: #8b1525; }
    .pulled-record-medical-row svg { width: 15px; height: 15px; }
    .pulled-record-medical-row div { min-width: 0; }
    .pulled-record-medical-row small { display: block; margin-bottom: 1px; color: var(--pr-muted); font-size: 10px; font-weight: 750; line-height: 1.25; }
    .pulled-record-medical-row strong { display: block; color: var(--pr-text); font-size: 12px; line-height: 1.3; overflow-wrap: anywhere; }
    .pulled-record-details { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 5px; }
    .pulled-record-detail { display: grid; grid-template-columns: 20px minmax(0,1fr); align-items: center; gap: 6px; min-height: 38px; padding: 6px 8px; border: 1px solid #eee5e7; border-radius: 6px; background: var(--pr-soft); }
    .pulled-record-detail > span { display: grid; place-items: center; color: #9b1c2d; }
    .pulled-record-detail svg { width: 14px; height: 14px; }
    .pulled-record-detail div { min-width: 0; }
    .pulled-record-detail dt { margin: 0 0 2px; color: var(--pr-muted); font-size: 10px; line-height: 1.2; }
    .pulled-record-detail dd { margin: 0; color: var(--pr-text); font-size: 11px; font-weight: 850; line-height: 1.3; overflow-wrap: anywhere; }
    .pulled-record-document-list { display: grid; gap: 4px; }
    .pulled-record-document-row { display: grid; grid-template-columns: 18px minmax(0,1fr) auto auto; align-items: center; gap: 8px; min-height: 32px; padding: 5px 8px; border: 1px solid #edf0f4; border-radius: 5px; background: var(--pr-soft); }
    .pulled-record-document-row > svg { width: 13px; height: 13px; color: #9b1c2d; }
    .pulled-record-document-name { min-width: 0; color: var(--pr-text); font-size: 11px; font-weight: 800; overflow-wrap: anywhere; }
    .pulled-record-document-status { padding: 4px 8px; border-radius: 999px; color: #64748b; background: #e9edf3; font-size: 9px; line-height: 1; white-space: nowrap; }
    .pulled-record-document-status.is-available { color: #166534; background: #dcfce7; }
    .pulled-record-document-row a { display: inline-flex; align-items: center; gap: 3px; color: #8b1423; font-size: 10px; font-weight: 900; text-decoration: none; white-space: nowrap; }
    .pulled-record-document-row a:hover { color: #a16207; text-decoration: underline; }
    .pulled-record-document-row a svg { width: 12px; height: 12px; }
    .pulled-record-remarks { margin-top: 10px; }
    .pulled-record-remarks p { margin: 0; padding: 8px 10px; border: 1px solid #e7eaf0; border-radius: 6px; color: var(--pr-text); background: var(--pr-soft); font-size: 11px; line-height: 1.45; white-space: pre-wrap; overflow-wrap: anywhere; }
    .pulled-record-restore {
        display: grid;
        grid-template-columns: minmax(185px, .8fr) minmax(260px, 1.4fr) auto;
        align-items: end;
        gap: 12px;
        margin-top: 12px;
        padding: 12px;
        border: 1px solid #f1d7dd;
        border-radius: 9px;
        background: #fff5f6;
    }
    .pulled-record-restore-intro { display: grid; grid-template-columns: 22px minmax(0,1fr); align-items: start; gap: 8px; }
    .pulled-record-restore-intro > svg { width: 20px; height: 20px; color: #9b1c2d; }
    .pulled-record-restore-intro h2 { margin: 0; color: #831322; font-size: 15px; line-height: 1.3; }
    .pulled-record-restore-intro p { grid-column: 2; margin: -2px 0 0; color: var(--pr-muted); font-size: 10px; line-height: 1.4; }
    .pulled-record-restore-field { min-width: 0; }
    .pulled-record-restore label { display: block; margin: 0 0 6px; color: #7c111e; font-size: 11px; }
    .pulled-record-reason-wrap { position: relative; }
    .pulled-record-reason-display {
        position: relative;
        width: 100%;
        min-height: 46px;
        padding: 11px 52px 11px 15px;
        border: 1px solid rgba(127,29,29,.22);
        border-radius: 16px;
        color: var(--pr-text);
        background: radial-gradient(circle at top right, rgba(250,204,21,.1), transparent 36%), linear-gradient(180deg,#fff 0%,#fff8f6 100%);
        box-shadow: 0 8px 18px rgba(15,23,42,.07), inset 0 1px 0 rgba(255,255,255,.86);
        text-align: left;
        font: inherit;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
        transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
    }
    .pulled-record-reason-display::before {
        content: "";
        position: absolute;
        top: 50%;
        right: 40px;
        width: 1px;
        height: 23px;
        background: rgba(148,163,184,.3);
        transform: translateY(-50%);
    }
    .pulled-record-reason-display::after {
        content: "";
        position: absolute;
        top: 50%;
        right: 17px;
        width: 9px;
        height: 9px;
        border-right: 2px solid #8b0000;
        border-bottom: 2px solid #8b0000;
        transform: translateY(-65%) rotate(45deg);
        transition: transform .18s ease;
    }
    .pulled-record-reason-display:hover,
    .pulled-record-reason-display:focus-visible,
    .pulled-record-reason-wrap.is-open .pulled-record-reason-display {
        border-color: #8b0000;
        box-shadow: 0 0 0 3px rgba(139,0,0,.08), 0 12px 22px rgba(15,23,42,.1);
        outline: none;
        transform: translateY(-1px);
    }
    .pulled-record-reason-wrap.is-open .pulled-record-reason-display::after { transform: translateY(-20%) rotate(225deg); }
    .pulled-record-reason-display.is-invalid { border-color: #b91c35; box-shadow: 0 0 0 3px rgba(185,28,53,.12); }
    .pulled-record-reason-menu {
        position: absolute;
        left: 0;
        bottom: calc(100% + 9px);
        z-index: 90;
        display: none;
        gap: 8px;
        width: min(330px, calc(100vw - 48px));
        max-height: 260px;
        overflow-y: auto;
        padding: 12px;
        border: 1px solid rgba(139,0,0,.14);
        border-radius: 18px;
        background: rgba(255,255,255,.98);
        box-shadow: 0 18px 34px rgba(15,23,42,.16);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }
    .pulled-record-reason-wrap.is-open .pulled-record-reason-menu { display: grid; animation: pulledReasonMenuIn .16s ease-out both; }
    .pulled-record-reason-option {
        width: 100%;
        min-height: 40px;
        padding: 10px 14px;
        border: 1px solid rgba(148,163,184,.22);
        border-radius: 999px;
        color: #1e293b;
        background: linear-gradient(180deg,#fff 0%,#f8fafc 100%);
        box-shadow: 0 6px 14px rgba(15,23,42,.07), inset 0 1px 0 rgba(255,255,255,.82);
        text-align: left;
        font: inherit;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
        transition: color .18s ease, border-color .18s ease, background .18s ease, transform .18s ease, box-shadow .18s ease;
    }
    .pulled-record-reason-option:hover,
    .pulled-record-reason-option:focus-visible,
    .pulled-record-reason-option.is-selected {
        border-color: #8b0000;
        color: #facc15;
        background: linear-gradient(135deg,#8b0000,#70131b);
        box-shadow: 0 10px 18px rgba(139,0,0,.16);
        outline: none;
        transform: translateY(-1px);
    }
    .pulled-record-reason-error,
    .pulled-record-input-error { display: block; margin: 5px 2px 0; color: #b91c35; font-size: 11px; font-weight: 750; line-height: 1.35; }
    .pulled-record-reason-error[hidden] { display: none; }
    .pulled-record-restore-other { margin-top: 10px; }
    .pulled-record-restore-other[hidden] { display: none; }
    .pulled-record-restore input { display: block; width: 100%; min-height: 40px; padding: 8px 10px; border: 1px solid #e4cbd0; border-radius: 8px; background: var(--pr-surface); color: var(--pr-text); font: inherit; font-size: 12px; line-height: 1.4; }
    .pulled-record-restore input:focus { border-color: var(--pr-maroon); outline: none; box-shadow: 0 0 0 3px rgba(143,20,34,.1); }
    @keyframes pulledReasonMenuIn { from { opacity: 0; transform: translateY(5px) scale(.985); } to { opacity: 1; transform: translateY(0) scale(1); } }
    .pulled-record-actions { margin: 0; }
    .pulled-record-restore-btn { min-height: 40px; padding: 0 13px; font-size: 11px; white-space: nowrap; }
    .pulled-record-restore-btn svg { width: 13px; height: 13px; margin-right: 4px; vertical-align: -2px; }
    .pullout-error { grid-column: 1 / -1; margin: 0; }
    [data-theme="dark"] .pulled-record-page { --pr-surface: #111a2b; --pr-soft: #182338; --pr-text: #f4f6fb; --pr-muted: #aeb9cc; --pr-border: #344157; }
    [data-theme="dark"] .pulled-record-panel-title,
    [data-theme="dark"] .pulled-record-restore-intro h2 { color: #fda4af; }
    [data-theme="dark"] .pulled-record-identity-icon { color: #fda4af; background: #3c1b26; }
    [data-theme="dark"] .pulled-record-category { color: #fecdd3; background: #43202a; }
    [data-theme="dark"] .pulled-record-medical-row,
    [data-theme="dark"] .pulled-record-detail,
    [data-theme="dark"] .pulled-record-document-row,
    [data-theme="dark"] .pulled-record-remarks p { border-color: #344157; background: var(--pr-soft); }
    [data-theme="dark"] .pulled-record-medical-row > span,
    [data-theme="dark"] .pulled-record-detail > span,
    [data-theme="dark"] .pulled-record-document-row > svg { color: #fda4af; }
    [data-theme="dark"] .pulled-record-document-row a { color: #fda4af; }
    [data-theme="dark"] .pulled-record-document-status { color: #d2d9e5; background: #344157; }
    [data-theme="dark"] .pulled-record-document-status.is-available { color: #bbf7d0; background: #14532d; }
    [data-theme="dark"] .pulled-record-access-alert { color: #fecdd3; border-color: #713541; background: #351b25; }
    [data-theme="dark"] .pulled-record-status-pill { color: #fff; background: #a61b31; }
    [data-theme="dark"] .pulled-record-restore { border-color: #51303a; background: #211b2b; }
    [data-theme="dark"] .pulled-record-restore label { color: #fda4af; }
    [data-theme="dark"] .pulled-record-restore textarea { border-color: #46516a; }
    [data-theme="dark"] .pulled-record-restore textarea:focus { border-color: #fda4af; box-shadow: 0 0 0 3px rgba(253,164,175,.12); }
    [data-theme="dark"] .pulled-record-restore input { border-color: #46516a; }
    [data-theme="dark"] .pulled-record-restore input:focus { border-color: #fda4af; box-shadow: 0 0 0 3px rgba(253,164,175,.12); }
    [data-theme="dark"] .pulled-record-reason-display { border-color: rgba(148,163,184,.22); color: #e5eefb; background: rgba(15,23,42,.92); }
    [data-theme="dark"] .pulled-record-reason-display::after { border-color: #facc15; }
    [data-theme="dark"] .pulled-record-reason-menu { border-color: rgba(250,204,21,.14); background: rgba(24,35,52,.98); box-shadow: 0 22px 42px rgba(0,0,0,.34); }
    [data-theme="dark"] .pulled-record-reason-option { border-color: rgba(148,163,184,.18); color: #e2e8f0; background: rgba(30,41,59,.92); }
    [data-theme="dark"] .pulled-record-reason-option:hover,
    [data-theme="dark"] .pulled-record-reason-option:focus-visible,
    [data-theme="dark"] .pulled-record-reason-option.is-selected { border-color: #facc15; color: #facc15; background: linear-gradient(135deg,#8b0000,#70131b); }
    [data-theme="dark"] .pulled-record-reason-error,
    [data-theme="dark"] .pulled-record-input-error { color: #fda4af; }
    [data-theme="dark"] .pulled-record-flash { color: #bbf7d0; border-color: #245a3a; background: #142a21; }
    @media (max-width: 900px) {
        .pulled-record-columns { grid-template-columns: minmax(220px,.8fr) minmax(0,1.2fr); }
        .pulled-record-restore { grid-template-columns: minmax(160px,.75fr) minmax(220px,1.25fr); }
        .pulled-record-restore .pulled-record-actions { grid-column: 2; justify-content: flex-end; }
    }
    @media (max-width: 680px) {
        .pulled-record-page { padding: 4px 0 14px; }
        .pulled-record-head { align-items: center; flex-direction: row; padding: 10px 12px; }
        .pulled-record-title { gap: 9px; }
        .pulled-record-title > span { width: 36px; height: 36px; }
        .pulled-record-title > span svg { width: 20px; height: 20px; }
        .pulled-record-title h1 { font-size: 16px; }
        .pulled-record-title p { font-size: 10px; }
        .pulled-record-body { padding: 9px; }
        .pulled-record-access-alert { align-items: flex-start; gap: 7px; padding: 8px; }
        .pulled-record-access-copy span { font-size: 10px; }
        .pulled-record-status-pill { padding: 5px 7px; font-size: 9px; }
        .pulled-record-columns { grid-template-columns: minmax(0,1fr); gap: 9px; }
        .pulled-record-panel { padding: 9px; }
        .pulled-record-details { gap: 4px; }
        .pulled-record-document-row { grid-template-columns: 16px minmax(0,1fr) auto; }
        .pulled-record-document-row a { grid-column: 2 / -1; justify-self: end; }
        .pulled-record-restore { grid-template-columns: minmax(0,1fr); gap: 10px; padding: 10px; }
        .pulled-record-restore-intro { grid-template-columns: 22px minmax(0,1fr); }
        .pulled-record-restore .pulled-record-actions { grid-column: auto; }
        .pulled-record-actions, .pulled-record-restore-btn { width: 100%; }
    }
    @media (max-width: 360px) {
        .pulled-record-details { grid-template-columns: minmax(0,1fr); }
        .pulled-record-document-row { grid-template-columns: 16px minmax(0,1fr) auto; }
    }
</style>
@endpush

@section('content')
@php
    $role = \App\Models\User::normalizeRole(optional(auth()->user())->user_role ?? '');
    $reportsRootUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports') : url('/admin/reports');
    $digitalLogbookUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports/digital-logbook') : url('/admin/reports/digital-logbook');
    $pulledOutRecordsUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports/digital-logbook/pulled-out-records') : url('/admin/reports/digital-logbook/pulled-out-records');
    $recordUser = $profile->user;
    $recordUserRole = trim((string) ($recordUser?->user_type ?: $recordUser?->idp_role ?: $recordUser?->user_role ?: 'User'));
    $recordUserRoleLower = strtolower($recordUserRole . ' ' . (string) $profile->health_form_category);
    $recordIsEmployee = str_contains($recordUserRoleLower, 'faculty')
        || str_contains($recordUserRoleLower, 'employee')
        || str_contains($recordUserRoleLower, 'staff')
        || str_contains($recordUserRoleLower, 'admin');
    $recordPersonLabel = $recordIsEmployee ? 'Employee' : 'Student';
    $recordCategoryLabel = trim((string) ($profile->health_form_category ?: \Illuminate\Support\Str::headline($recordUserRole)));
    $recordReferenceNumber = $profile->reference_number ?: $profile->student_number ?: 'N/A';
    $medicalConditionSummary = $profile->medical_condition_remarks
        ?: ($profile->hasMedicalCondition() ? 'With Condition' : 'No Medical Condition Recorded');
    $documentLabels = [
        'student_photo' => '2x2 Photo',
        'health_declaration' => 'Health Declaration',
        'medical_certificate' => 'Medical Certificate',
        'chest_xray_result' => 'Chest X-ray Result',
        'pwd_id_proof' => 'PWD Proof',
        'medical_assessment_upload' => 'Medical Assessment',
    ];
    $archivedDocuments = array_filter(
        $documentLabels,
        fn ($label, $field) => filled($profile->getAttribute($field)),
        ARRAY_FILTER_USE_BOTH
    );
    $restoreReasonOptions = [
        'pullout_error' => 'Record was pulled out in error',
        'owner_request' => 'Record owner requested restoration',
        'issue_resolved' => 'The pullout concern has been resolved',
        'eligibility_confirmed' => 'Clinic access eligibility was confirmed',
        'other' => 'Other',
    ];
    $oldRestoreReasonOption = old('pullout_restore_reason_option', '');
    $oldRestoreReasonOption = is_string($oldRestoreReasonOption) ? $oldRestoreReasonOption : '';
    $oldRestoreReasonLabel = $restoreReasonOptions[$oldRestoreReasonOption] ?? 'Select a reason';
@endphp
<div class="pulled-record-page">
    @include('admin.partials.report-breadcrumb', ['items' => [
        ['label' => 'Reports', 'url' => $reportsRootUrl],
        ['label' => 'Clinic Records', 'url' => $digitalLogbookUrl],
        ['label' => 'Pulled Out', 'url' => $pulledOutRecordsUrl],
        ['label' => 'Record'],
    ]])
    <section class="pulled-record-card">
        <header class="pulled-record-head">
            <div class="pulled-record-title">
                <span><x-outline-icon name="document-check" /></span>
                <div>
                    <h1>Pulled Out Health Record</h1>
                    <p>Archived record retained for audit, review, and authorized restoration.</p>
                </div>
            </div>
        </header>
        <div class="pulled-record-body">
            @if(session('success'))
                <div class="pulled-record-flash" role="status">{{ session('success') }}</div>
            @endif
            <div class="pulled-record-access-alert" role="status">
                <span class="pulled-record-access-icon"><x-outline-icon name="exclamation-triangle" /></span>
                <div class="pulled-record-access-copy">
                    <strong>Clinic system access is blocked</strong>
                    <span>This profile is excluded from active Health Records. Its medical data and files remain stored.</span>
                </div>
                <span class="pulled-record-status-pill">PULLED OUT</span>
            </div>

            <div class="pulled-record-columns">
                <div class="pulled-record-column">
                    <section class="pulled-record-panel">
                        <h2 class="pulled-record-panel-title"><x-outline-icon name="user-circle" /> {{ $recordPersonLabel }} Information</h2>
                        <div class="pulled-record-identity">
                            <span class="pulled-record-identity-icon"><x-outline-icon name="academic-cap" /></span>
                            <div class="pulled-record-identity-copy">
                                <strong>{{ $recordUser?->name ?: 'Unknown User' }}</strong>
                                <div class="pulled-record-identity-meta">
                                    <span class="pulled-record-category">{{ $recordCategoryLabel }}</span>
                                    <span><x-outline-icon name="identification" /> {{ $recordReferenceNumber }}</span>
                                    @if(filled($recordUser?->email))
                                        <span><x-outline-icon name="envelope" /> {{ $recordUser->email }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="pulled-record-panel">
                        <h2 class="pulled-record-panel-title"><x-outline-icon name="heart-pulse" /> Medical Information</h2>
                        <div class="pulled-record-medical-list">
                            <div class="pulled-record-medical-row">
                                <span><x-outline-icon name="heart-pulse" /></span>
                                <div><small>Medical Condition</small><strong>{{ $medicalConditionSummary }}</strong></div>
                            </div>
                            <div class="pulled-record-medical-row">
                                <span><x-outline-icon name="clipboard-document-list" /></span>
                                <div><small>Physical Assessment</small><strong>{{ $profile->physical_assessment_status ?: 'Not Yet Conducted' }}</strong></div>
                            </div>
                        </div>
                    </section>

                    <section class="pulled-record-panel">
                        <h2 class="pulled-record-panel-title"><x-outline-icon name="information-circle" /> Pullout Remarks</h2>
                        <div class="pulled-record-remarks">
                            <p>{{ $profile->pullout_request_remarks ?: 'No pullout remarks were recorded.' }}</p>
                        </div>
                    </section>
                </div>

                <div class="pulled-record-column">
                    <section class="pulled-record-panel">
                        <h2 class="pulled-record-panel-title"><x-outline-icon name="clipboard-document-list" /> Pullout Details</h2>
                        <dl class="pulled-record-details">
                            <div class="pulled-record-detail"><span><x-outline-icon name="document-check" /></span><div><dt>Previous Clearance</dt><dd>{{ $profile->clearance_status ?: 'N/A' }}</dd></div></div>
                            <div class="pulled-record-detail"><span><x-outline-icon name="cube" /></span><div><dt>Previous Account Status</dt><dd>{{ \Illuminate\Support\Str::headline($profile->pullout_previous_user_status ?: 'Active') }}</dd></div></div>
                            <div class="pulled-record-detail"><span><x-outline-icon name="information-circle" /></span><div><dt>Pullout Reason</dt><dd>{{ $profile->pullout_reason ?: 'N/A' }}</dd></div></div>
                            <div class="pulled-record-detail"><span><x-outline-icon name="user-circle" /></span><div><dt>Pulled Out By</dt><dd>{{ $profile->pulloutCompletedBy?->name ?: $profile->pulloutCompletedBy?->email ?: 'N/A' }}</dd></div></div>
                            <div class="pulled-record-detail"><span><x-outline-icon name="calendar-days" /></span><div><dt>Pulled Out At</dt><dd>{{ $profile->pullout_completed_at?->format('M d, Y h:i A') ?: 'N/A' }}</dd></div></div>
                            <div class="pulled-record-detail"><span><x-outline-icon name="check" /></span><div><dt>Verified At</dt><dd>{{ $profile->verified_at?->format('M d, Y h:i A') ?: 'N/A' }}</dd></div></div>
                        </dl>
                    </section>

                    @if(count($archivedDocuments))
                    <section class="pulled-record-panel">
                        <h2 class="pulled-record-panel-title"><x-outline-icon name="document-text" /> Archived Documents</h2>
                        <div class="pulled-record-document-list">
                            @foreach($archivedDocuments as $field => $label)
                                @php $hasDocument = filled($profile->getAttribute($field)); @endphp
                                <div class="pulled-record-document-row">
                                    <x-outline-icon name="document-text" />
                                    <span class="pulled-record-document-name">{{ $label }}</span>
                                    @if($hasDocument)
                                        <span class="pulled-record-document-status is-available">Available</span>
                                        <a href="{{ route('walkin.document', ['healthProfile' => $profile->id, 'document' => $field]) }}" target="_blank" rel="noopener" aria-label="View {{ $label }}"><x-outline-icon name="eye" /> View</a>
                                    @else
                                        <span class="pulled-record-document-status">Not Available</span>
                                        <span aria-hidden="true">—</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </section>
                    @endif
                </div>
            </div>

            <form class="pulled-record-restore" method="POST" action="{{ route('admin.health_profile.pullout.restore', $profile->id) }}">
                @csrf
                @error('pullout_restore_reason')<div class="pullout-error">{{ $message }}</div>@enderror
                <div class="pulled-record-restore-intro">
                    <x-outline-icon name="arrow-path" />
                    <h2>Restore Health Record</h2>
                    <p>Restoring this record will return it to active health records and restore the user's clinic system access.</p>
                </div>
                <div class="pulled-record-restore-field">
                    <label id="pulloutRestoreReasonLabel" for="pulloutRestoreReasonDisplay">Reason for Restoration <span aria-hidden="true">*</span></label>
                    <div class="pulled-record-reason-wrap" id="pulloutRestoreReasonWrap">
                        <input id="pulloutRestoreReasonValue" type="hidden" name="pullout_restore_reason_option" value="{{ $oldRestoreReasonOption }}">
                        <button id="pulloutRestoreReasonDisplay" class="pulled-record-reason-display" type="button" aria-haspopup="listbox" aria-expanded="false" aria-controls="pulloutRestoreReasonMenu" aria-describedby="pulloutRestoreReasonError" aria-invalid="{{ $errors->has('pullout_restore_reason_option') ? 'true' : 'false' }}">
                            <span id="pulloutRestoreReasonText">{{ $oldRestoreReasonLabel }}</span>
                        </button>
                        <div class="pulled-record-reason-menu" id="pulloutRestoreReasonMenu" role="listbox" aria-label="Restoration reason options" aria-hidden="true">
                        @foreach($restoreReasonOptions as $value => $label)
                            <button class="pulled-record-reason-option{{ $oldRestoreReasonOption === $value ? ' is-selected' : '' }}" type="button" role="option" data-reason-value="{{ $value }}" aria-selected="{{ $oldRestoreReasonOption === $value ? 'true' : 'false' }}">{{ $label }}</button>
                        @endforeach
                        </div>
                        @error('pullout_restore_reason_option')
                            <span class="pulled-record-reason-error" id="pulloutRestoreReasonError">{{ $message }}</span>
                        @else
                            <span class="pulled-record-reason-error" id="pulloutRestoreReasonError" hidden></span>
                        @enderror
                    </div>
                    <div class="pulled-record-restore-other" id="pulloutRestoreOtherWrap" @if($oldRestoreReasonOption !== 'other') hidden @endif>
                        <label for="pulloutRestoreOther">Please specify <span aria-hidden="true">*</span></label>
                        <input id="pulloutRestoreOther" name="pullout_restore_reason_other" type="text" maxlength="2000" placeholder="Enter the reason" value="{{ old('pullout_restore_reason_other') }}" @if($oldRestoreReasonOption !== 'other') disabled @else required @endif aria-describedby="pulloutRestoreOtherError">
                        @error('pullout_restore_reason_other')
                            <span class="pulled-record-input-error" id="pulloutRestoreOtherError">{{ $message }}</span>
                        @else
                            <span class="pulled-record-input-error" id="pulloutRestoreOtherError" hidden></span>
                        @enderror
                    </div>
                </div>
                <div class="pulled-record-actions">
                    <button class="pulled-record-restore-btn" type="submit"><x-outline-icon name="arrow-path" /> Restore Record &amp; Access</button>
                </div>
            </form>
        </div>
    </section>
</div>
<script>
    (() => {
        const form = document.querySelector('.pulled-record-restore');
        const reasonWrap = document.getElementById('pulloutRestoreReasonWrap');
        const reasonValue = document.getElementById('pulloutRestoreReasonValue');
        const reasonDisplay = document.getElementById('pulloutRestoreReasonDisplay');
        const reasonText = document.getElementById('pulloutRestoreReasonText');
        const reasonMenu = document.getElementById('pulloutRestoreReasonMenu');
        const reasonOptions = Array.from(document.querySelectorAll('.pulled-record-reason-option'));
        const reasonError = document.getElementById('pulloutRestoreReasonError');
        const otherWrap = document.getElementById('pulloutRestoreOtherWrap');
        const otherInput = document.getElementById('pulloutRestoreOther');
        const otherError = document.getElementById('pulloutRestoreOtherError');

        if (!form || !reasonWrap || !reasonValue || !reasonDisplay || !reasonText || !reasonMenu || !otherWrap || !otherInput) return;

        const setReasonMenuOpen = (isOpen) => {
            reasonWrap.classList.toggle('is-open', isOpen);
            reasonDisplay.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            reasonMenu.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        };

        const syncOtherReason = () => {
            const isOther = reasonValue.value === 'other';
            otherWrap.hidden = !isOther;
            otherInput.disabled = !isOther;
            otherInput.required = isOther;
        };

        reasonDisplay.addEventListener('click', () => {
            setReasonMenuOpen(!reasonWrap.classList.contains('is-open'));
        });

        reasonOptions.forEach((option, index) => {
            option.addEventListener('click', () => {
                reasonValue.value = option.dataset.reasonValue || '';
                reasonText.textContent = option.textContent.trim();
                reasonOptions.forEach((item) => {
                    const isSelected = item === option;
                    item.classList.toggle('is-selected', isSelected);
                    item.setAttribute('aria-selected', isSelected ? 'true' : 'false');
                });
                reasonDisplay.classList.remove('is-invalid');
                reasonDisplay.setAttribute('aria-invalid', 'false');
                reasonError.hidden = true;
                reasonError.textContent = '';
                if (otherError) {
                    otherError.hidden = true;
                    otherError.textContent = '';
                }
                syncOtherReason();
                setReasonMenuOpen(false);
                if (reasonValue.value === 'other') otherInput.focus();
                else reasonDisplay.focus();
            });

            option.addEventListener('keydown', (event) => {
                if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                    event.preventDefault();
                    const nextIndex = event.key === 'ArrowDown'
                        ? (index + 1) % reasonOptions.length
                        : (index - 1 + reasonOptions.length) % reasonOptions.length;
                    reasonOptions[nextIndex].focus();
                } else if (event.key === 'Escape') {
                    setReasonMenuOpen(false);
                    reasonDisplay.focus();
                }
            });
        });

        reasonDisplay.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                setReasonMenuOpen(true);
                reasonOptions[0]?.focus();
            } else if (event.key === 'Escape') {
                setReasonMenuOpen(false);
            }
        });

        document.addEventListener('click', (event) => {
            if (!reasonWrap.contains(event.target)) setReasonMenuOpen(false);
        });

        form.addEventListener('submit', (event) => {
            if (!reasonValue.value) {
                event.preventDefault();
                reasonError.textContent = 'Select a reason for restoration.';
                reasonError.hidden = false;
                reasonDisplay.classList.add('is-invalid');
                reasonDisplay.setAttribute('aria-invalid', 'true');
                setReasonMenuOpen(true);
                reasonDisplay.focus();
                return;
            }

            if (reasonValue.value === 'other' && !otherInput.value.trim()) {
                event.preventDefault();
                if (otherError) {
                    otherError.textContent = 'Please enter the reason for restoration.';
                    otherError.hidden = false;
                }
                otherInput.focus();
            }
        });

        syncOtherReason();
    })();
</script>
@endsection
