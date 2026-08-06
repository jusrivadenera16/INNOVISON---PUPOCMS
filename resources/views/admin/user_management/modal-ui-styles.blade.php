<style>
    .um-modal-content {
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, .62);
        border-bottom: 4px solid #70131b;
        border-radius: 20px;
        background: #fff;
    }
    #settingsModal .um-modal-content {
        width: min(1080px, 100%);
        max-height: 92vh;
    }
    .um-modal-head {
        align-items: center;
        padding: 18px 20px !important;
        border-bottom: 0;
        background: linear-gradient(135deg, #7f1d1d, #991b1b 55%, #b91c1c);
        color: #fff;
    }
    .um-modal-head-main { display:flex; min-width:0; align-items:center; gap:14px; }
    .um-modal-head-badge {
        display:inline-flex; width:46px; height:46px; flex:0 0 46px; align-items:center; justify-content:center;
        border:1px solid rgba(255,255,255,.24); border-radius:14px; background:rgba(255,255,255,.16);
        color:#fff; font-size:12px; font-weight:900;
    }
    .um-modal-head h3 { color:#fff !important; font-size:1rem; font-weight:900; text-transform:uppercase; }
    .um-modal-head .um-note { margin-top:5px; color:rgba(255,255,255,.92) !important; font-size:12px; }
    .um-modal-close {
        position:relative; display:inline-flex; width:40px; height:40px; min-width:40px; padding:0 0 3px; overflow:hidden;
        align-items:center; justify-content:center; border:1px solid #8f2230; border-radius:50%; background:#70131b;
        color:#fff; cursor:pointer; font-size:24px; line-height:.75;
        transition:border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }
    .um-modal-close::after {
        position:absolute; inset:0; background:linear-gradient(120deg, transparent, rgba(255,239,181,.52), transparent);
        content:""; transform:translateX(-135%); transition:transform .65s ease;
    }
    .um-modal-close:hover, .um-modal-close:focus-visible {
        border-color:#facc15; box-shadow:0 0 0 3px rgba(250,204,21,.18); outline:none; transform:translateY(-1px);
    }
    .um-modal-close:hover::after, .um-modal-close:focus-visible::after { transform:translateX(135%); }
    .um-modal-body {
        max-height:calc(100vh - 145px); overflow-y:auto; background:#f8fafc;
        scrollbar-color:#8f2230 #e5e7eb; scrollbar-width:thin;
    }
    #settingsModal .um-modal-body { padding:20px; }
    #settingsModal .um-modal-grid {
        display:grid;
        grid-template-columns:minmax(260px, 320px) minmax(0, 1fr);
        gap:18px;
        align-items:start;
    }
    #settingsModal .um-detail-card {
        overflow:hidden; padding:0; border:1px solid rgba(112,19,27,.14);
        border-radius:14px; background:#fff; box-shadow:0 10px 24px rgba(15,23,42,.06);
    }
    #settingsModal .um-settings-form-card { overflow:visible; }
    #settingsModal .um-settings-form-card .um-settings-card-head {
        border-radius:13px 13px 0 0;
    }
    #settingsModal .um-settings-form-card .um-settings-form-body {
        border-radius:0 0 13px 13px;
    }
    #settingsModal .um-profile-summary-card { position:sticky; top:0; }
    #settingsModal .um-profile-identity {
        display:flex; align-items:center; gap:14px; padding:18px;
        border-bottom:1px solid rgba(148,163,184,.18);
        background:linear-gradient(135deg,#fff7ed,#fff 58%,#fef2f2);
    }
    #settingsModal .um-detail-photo {
        width:72px; height:72px; min-width:72px; margin:0; border:3px solid #fff; border-radius:14px;
        background:linear-gradient(135deg,#70131b,#a71928); box-shadow:0 8px 18px rgba(112,19,27,.22);
        color:#fff; font-size:1.45rem;
    }
    #settingsModal .um-profile-eyebrow {
        display:block; margin-bottom:4px; color:#8f2230; font-size:11px; font-weight:900;
        letter-spacing:.08em; text-transform:uppercase;
    }
    #settingsModal .um-profile-heading { margin:0; color:#111827; font-size:17px; font-weight:900; }
    #settingsModal .um-profile-copy { margin:3px 0 0; color:#64748b; font-size:12px; line-height:1.45; }
    #settingsModal .um-profile-fields { display:grid; gap:12px; padding:16px; }
    #settingsModal .um-profile-fields .um-field { margin:0; }
    #settingsModal .um-profile-fields input[readonly] {
        min-height:44px; border-color:#e2e8f0; border-radius:9px; background:#f8fafc;
        color:#1e293b; font-size:13px; font-weight:750;
    }
    #settingsModal .um-settings-card-head {
        display:flex; align-items:center; justify-content:space-between; gap:12px; padding:16px 18px;
        border-bottom:1px solid rgba(148,163,184,.18); background:#fff;
    }
    #settingsModal .um-settings-card-head h4 { margin:0; color:#70131b; font-size:15px; font-weight:900; }
    #settingsModal .um-settings-card-head p { margin:3px 0 0; color:#64748b; font-size:12px; }
    #settingsModal .um-settings-card-badge {
        display:inline-flex; min-width:42px; height:34px; align-items:center; justify-content:center;
        border:1px solid rgba(112,19,27,.16); border-radius:9px; background:#fff7ed;
        color:#70131b; font-size:11px; font-weight:900;
    }
    #settingsModal .um-settings-form-body { padding:18px; }
    #settingsModal .um-section-block {
        padding:16px; border:1px solid rgba(112,19,27,.14); border-radius:12px;
        background:#fff; box-shadow:none;
    }
    #settingsModal .um-section-kicker {
        margin-bottom:8px; padding:5px 9px; border-radius:7px; background:#fef2f2;
        color:#8f2230; font-size:10px; letter-spacing:.08em;
    }
    #settingsModal .um-section-title { color:#70131b; font-size:15px; }
    #settingsModal .um-section-copy { margin-bottom:16px; font-size:12px; }
    #settingsModal .um-field { margin-bottom:14px; }
    #settingsModal .um-field label {
        margin-bottom:7px; color:#475569; font-size:11px; font-weight:900; letter-spacing:.06em;
    }
    #settingsModal .um-field input,
    #settingsModal .um-field textarea {
        min-height:48px; border:1px solid #dbe2ea; border-radius:10px; background:#fff;
        color:#111827; font-size:13px; transition:border-color .18s ease,box-shadow .18s ease;
    }
    #settingsModal .um-field input:focus,
    #settingsModal .um-field textarea:focus {
        border-color:#8f2230; box-shadow:0 0 0 3px rgba(143,34,48,.10); outline:none;
    }
    #settingsModal .um-module-access-preview {
        margin:2px 0 16px; padding:14px; border:1px solid rgba(112,19,27,.14);
        border-radius:11px; background:#f8fafc;
    }
    #settingsModal .um-module-access-preview[hidden] { display:none; }
    #settingsModal .um-module-access-head {
        display:flex; align-items:flex-start; justify-content:space-between; gap:12px; margin-bottom:12px;
    }
    #settingsModal .um-module-access-head h5 {
        margin:0; color:#70131b; font-size:13px; font-weight:900;
    }
    #settingsModal .um-module-access-head p {
        margin:3px 0 0; color:#64748b; font-size:11px; line-height:1.4;
    }
    #settingsModal .um-preview-badge {
        display:inline-flex; min-height:24px; flex:0 0 auto; align-items:center; padding:4px 8px;
        border:1px solid #f3d08a; border-radius:6px; background:#fffbeb;
        color:#92400e; font-size:9px; font-weight:900; text-transform:uppercase;
    }
    #settingsModal .um-module-access-toolbar {
        display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:10px;
        padding:9px 10px; border:1px solid #e2e8f0; border-radius:8px; background:#fff;
    }
    #settingsModal .um-module-selection-summary { min-width:0; }
    #settingsModal .um-module-selection-summary strong {
        display:block; color:#334155; font-size:10px; font-weight:900;
    }
    #settingsModal .um-module-selection-summary span {
        display:block; margin-top:2px; color:#64748b; font-size:8px; font-weight:650;
    }
    #settingsModal .um-reset-module-defaults {
        min-height:30px; flex:0 0 auto; padding:6px 9px; border:1px solid #d7b7ba; border-radius:7px;
        background:#fff; color:#70131b; cursor:pointer; font-size:9px; font-weight:900;
        transition:border-color .18s ease,background .18s ease,color .18s ease;
    }
    #settingsModal .um-reset-module-defaults:hover,
    #settingsModal .um-reset-module-defaults:focus-visible {
        border-color:#8f2230; background:#fef2f2; outline:none;
    }
    #settingsModal .um-reset-module-defaults:disabled { cursor:not-allowed; opacity:.48; }
    #settingsModal .um-module-access-grid {
        display:grid; grid-template-columns:1fr; gap:8px;
    }
    #settingsModal .um-module-item {
        overflow:hidden; border:1px solid #e2e8f0; border-radius:8px; background:#fff;
        transition:border-color .18s ease,background .18s ease,box-shadow .18s ease;
    }
    #settingsModal .um-module-item:hover { border-color:rgba(112,19,27,.30); }
    #settingsModal .um-module-item:has(.um-module-option input:checked) {
        border-color:rgba(112,19,27,.34); background:#fffafa; box-shadow:inset 3px 0 #8f2230;
    }
    #settingsModal .um-module-item.is-disabled { background:#f8fafc; opacity:.62; }
    #settingsModal .um-module-row {
        display:grid; grid-template-columns:minmax(0,1fr) auto; align-items:center;
    }
    #settingsModal .um-module-option {
        position:relative; display:grid; grid-template-columns:30px minmax(0,1fr) 20px;
        min-height:60px; margin:0; padding:9px 9px 9px 10px; align-items:center; gap:9px;
        border:0; border-radius:0; background:transparent; cursor:pointer; text-transform:none; letter-spacing:0;
    }
    #settingsModal .um-module-option:has(input:focus-visible) {
        outline:3px solid rgba(143,34,48,.13); outline-offset:1px;
    }
    #settingsModal .um-module-option input {
        position:absolute; width:1px; height:1px; margin:0; opacity:0;
    }
    #settingsModal .um-module-icon {
        display:inline-flex; width:30px; height:30px; align-items:center; justify-content:center;
        border-radius:7px; background:#fef2f2; color:#8f2230;
    }
    #settingsModal .um-module-icon svg { width:16px; height:16px; }
    #settingsModal .um-module-copy { min-width:0; }
    #settingsModal .um-module-title {
        display:flex; min-width:0; align-items:center; gap:7px;
    }
    #settingsModal .um-module-title strong {
        display:block; min-width:0; overflow:hidden; color:#1e293b; font-size:11px; font-weight:900;
        text-overflow:ellipsis; white-space:nowrap;
    }
    #settingsModal .um-module-title > span {
        display:inline-flex; min-height:17px; flex:0 0 auto; align-items:center; padding:2px 6px;
        border-radius:5px; background:#f1f5f9; color:#475569; font-size:8px; font-weight:900;
        text-transform:uppercase;
    }
    #settingsModal .um-module-copy small {
        display:block; margin-top:2px; color:#64748b; font-size:9px; font-weight:600; line-height:1.3;
    }
    #settingsModal .um-module-check {
        display:inline-flex; width:18px; height:18px; align-items:center; justify-content:center;
        border:1px solid #cbd5e1; border-radius:5px; background:#fff; color:transparent;
    }
    #settingsModal .um-module-check svg { width:12px; height:12px; stroke-width:2.5; }
    #settingsModal .um-module-option input:checked ~ .um-module-check {
        border-color:#8f2230; background:#8f2230; color:#fff;
    }
    #settingsModal .um-module-expand {
        display:inline-flex; width:30px; height:30px; margin-right:10px; padding:0; align-items:center; justify-content:center;
        border:1px solid #e2e8f0; border-radius:7px; background:#fff; color:#70131b; cursor:pointer;
        transition:border-color .18s ease,background .18s ease,transform .18s ease;
    }
    #settingsModal .um-module-expand svg { width:15px; height:15px; transition:transform .18s ease; }
    #settingsModal .um-module-expand:hover,
    #settingsModal .um-module-expand:focus-visible {
        border-color:#8f2230; background:#fef2f2; outline:none;
    }
    #settingsModal .um-module-expand[aria-expanded="true"] svg { transform:rotate(180deg); }
    #settingsModal .um-module-expand:disabled { cursor:not-allowed; opacity:.45; }
    #settingsModal .um-module-actions {
        padding:2px 12px 12px 49px; border-top:1px solid #f1f5f9; background:rgba(248,250,252,.72);
    }
    #settingsModal .um-module-actions[hidden] { display:none; }
    #settingsModal .um-module-actions-head {
        display:flex; align-items:center; justify-content:space-between; gap:10px; padding:9px 0 6px;
    }
    #settingsModal .um-module-actions-head strong { color:#475569; font-size:9px; font-weight:900; text-transform:uppercase; }
    #settingsModal .um-module-actions-head span { color:#94a3b8; font-size:8px; font-weight:700; }
    #settingsModal .um-action-permission {
        position:relative; display:grid; grid-template-columns:18px minmax(0,1fr); min-height:42px;
        margin:0; padding:7px 8px; align-items:center; gap:9px; border-top:1px solid #eef2f7;
        color:#1e293b; cursor:pointer; text-transform:none; letter-spacing:0;
    }
    #settingsModal .um-action-permission:has(input:checked) { background:#fff7f7; }
    #settingsModal .um-action-permission:has(input:focus-visible) { outline:2px solid rgba(143,34,48,.13); }
    #settingsModal .um-action-permission input { position:absolute; width:1px; height:1px; margin:0; opacity:0; }
    #settingsModal .um-action-check {
        display:inline-flex; width:17px; height:17px; align-items:center; justify-content:center;
        border:1px solid #cbd5e1; border-radius:4px; background:#fff; color:transparent;
    }
    #settingsModal .um-action-check svg { width:11px; height:11px; stroke-width:2.5; }
    #settingsModal .um-action-permission input:checked + .um-action-check {
        border-color:#8f2230; background:#8f2230; color:#fff;
    }
    #settingsModal .um-action-permission > span:nth-child(3) { min-width:0; }
    #settingsModal .um-action-permission strong { display:block; color:#334155; font-size:10px; font-weight:850; }
    #settingsModal .um-action-permission small {
        display:block; margin-top:2px; color:#64748b; font-size:8px; font-weight:600; line-height:1.3;
    }
    #settingsModal .um-action-permission.is-locked {
        grid-template-columns:22px minmax(0,1fr) auto; cursor:default; background:#fff8e6;
    }
    #settingsModal .um-action-lock {
        display:inline-flex; width:20px; height:20px; align-items:center; justify-content:center;
        border-radius:50%; background:#70131b; color:#fff;
    }
    #settingsModal .um-action-lock svg { width:12px; height:12px; }
    #settingsModal .um-action-permission.is-locked > span:nth-child(2) { min-width:0; }
    #settingsModal .um-locked-badge {
        display:inline-flex; min-height:20px; align-items:center; padding:3px 6px; border-radius:5px;
        background:#fef3c7; color:#92400e; font-size:8px; font-weight:900; text-transform:uppercase;
    }
    #settingsModal .um-superadmin-access-summary {
        display:grid; grid-template-columns:30px minmax(0,1fr); gap:10px; margin-top:10px; padding:10px;
        border:1px solid #ead8b1; border-radius:8px; background:#fffbeb;
    }
    #settingsModal .um-superadmin-summary-icon {
        display:inline-flex; width:30px; height:30px; align-items:center; justify-content:center;
        border-radius:7px; background:#70131b; color:#fff;
    }
    #settingsModal .um-superadmin-summary-icon svg { width:16px; height:16px; }
    #settingsModal .um-superadmin-access-summary strong {
        display:block; color:#70131b; font-size:10px; font-weight:900;
    }
    #settingsModal .um-superadmin-access-list { display:flex; flex-wrap:wrap; gap:5px; margin-top:6px; }
    #settingsModal .um-superadmin-access-list span {
        display:inline-flex; min-height:20px; align-items:center; padding:3px 6px; border:1px solid #f0dfbb;
        border-radius:5px; background:#fff; color:#78350f; font-size:8px; font-weight:750;
    }
    #settingsModal .um-module-preview-note {
        margin:10px 0 0; color:#92400e; font-size:10px; font-weight:750;
    }
    #settingsModal .um-module-save-warning {
        display:flex; flex-wrap:wrap; gap:4px 7px; margin:12px 0 0; padding:9px 10px;
        border:1px solid #f3d08a; border-radius:8px; background:#fffbeb; color:#78350f;
        font-size:9px; line-height:1.4;
    }
    #settingsModal .um-module-save-warning[hidden] { display:none; }
    #settingsModal .um-module-save-warning strong { font-weight:900; }
    #settingsModal .um-module-save-warning span { color:#92400e; font-weight:650; }
    #settingsModal .um-actions {
        position:sticky; bottom:-18px; z-index:20; display:flex; justify-content:flex-end; gap:9px;
        margin:18px -18px -18px; padding:14px 18px; border-top:1px solid #e2e8f0;
        background:rgba(255,255,255,.96); backdrop-filter:blur(8px);
    }
    #settingsModal .um-settings-action {
        position:relative; min-height:40px; padding:10px 14px; overflow:hidden;
        border:1px solid transparent; border-radius:9px; cursor:pointer; font-size:12px; font-weight:900;
        transition:border-color .18s ease,color .18s ease,background .18s ease,transform .18s ease;
    }
    #settingsModal .um-settings-action::after {
        position:absolute; inset:0; background:linear-gradient(120deg,transparent,rgba(255,255,255,.48),transparent);
        content:""; transform:translateX(-140%); transition:transform .6s ease;
    }
    #settingsModal .um-settings-action:hover::after,
    #settingsModal .um-settings-action:focus-visible::after { transform:translateX(140%); }
    #settingsModal .um-settings-action:hover,
    #settingsModal .um-settings-action:focus-visible { outline:none; transform:translateY(-1px); }
    #settingsModal .um-action-neutral { border-color:#cbd5e1; background:#f8fafc; color:#334155; }
    #settingsModal .um-action-warning { border-color:#f6c945; background:#fff7cc; color:#70131b; }
    #settingsModal .um-action-danger { border-color:#fecaca; background:#fff1f2; color:#991b1b; }
    #settingsModal .um-action-primary { border-color:#70131b; background:#70131b; color:#fff; }
    #settingsModal .um-action-primary:hover,
    #settingsModal .um-action-primary:focus-visible { border-color:#facc15; background:#facc15; color:#70131b; }

    .um-custom-select { position:relative; }
    .um-custom-select-native {
        position:absolute !important; width:1px !important; height:1px !important; margin:0 !important;
        padding:0 !important; overflow:hidden; border:0 !important; opacity:0; pointer-events:none;
    }
    .um-custom-select-button {
        position:relative; width:100%; min-height:48px; padding:12px 44px 12px 14px;
        border:1px solid #dbe2ea; border-radius:10px;
        background:#fff; color:#111827; cursor:pointer; font:inherit;
        font-size:13px; font-weight:800; text-align:left;
    }
    .um-custom-select-button::after {
        position:absolute; top:50%; right:18px; width:8px; height:8px;
        border-right:2px solid #70131b; border-bottom:2px solid #70131b; content:"";
        transform:translateY(-70%) rotate(45deg); transition:transform .18s ease;
    }
    .um-custom-select.is-open .um-custom-select-button {
        border-color:#8f2230; box-shadow:0 0 0 3px rgba(143,34,48,.10),0 12px 24px rgba(15,23,42,.10);
    }
    .um-custom-select.is-open .um-custom-select-button::after { transform:translateY(-25%) rotate(225deg); }
    .um-custom-select-menu {
        position:absolute; z-index:5100; top:calc(100% + 8px); right:0; left:0; display:none; gap:8px;
        max-height:250px; overflow-y:auto; padding:10px; border:1px solid rgba(127,29,29,.18);
        border-radius:10px; background:#fff; box-shadow:0 18px 38px rgba(15,23,42,.18);
    }
    .um-custom-select.is-open .um-custom-select-menu { display:grid; }
    .um-custom-select-option {
        width:100%; padding:11px 13px; border:1px solid rgba(148,163,184,.22); border-radius:8px;
        background:linear-gradient(180deg,#fff,#f8fafc); color:#1e293b; cursor:pointer; font:inherit;
        font-size:13px; font-weight:800; text-align:left;
        transition:background .18s ease,border-color .18s ease,color .18s ease,transform .18s ease;
    }
    .um-custom-select-option:hover, .um-custom-select-option.is-selected {
        border-color:#8b0000; background:linear-gradient(135deg,#8b0000,#70131b); color:#facc15;
        transform:translateY(-1px);
    }
    html[data-theme="dark"] .um-modal-body,
    html[data-theme="dark"] #settingsModal .um-detail-card,
    html[data-theme="dark"] #settingsModal .um-section-block { background:#111827; }
    html[data-theme="dark"] #settingsModal .um-profile-identity,
    html[data-theme="dark"] #settingsModal .um-settings-card-head {
        border-color:rgba(148,163,184,.16); background:#172033;
    }
    html[data-theme="dark"] #settingsModal .um-profile-heading,
    html[data-theme="dark"] #settingsModal .um-settings-card-head h4 { color:#fff; }
    html[data-theme="dark"] #settingsModal .um-profile-copy,
    html[data-theme="dark"] #settingsModal .um-settings-card-head p,
    html[data-theme="dark"] #settingsModal .um-section-copy { color:#cbd5e1; }
    html[data-theme="dark"] #settingsModal .um-profile-fields input[readonly],
    html[data-theme="dark"] #settingsModal .um-field input,
    html[data-theme="dark"] #settingsModal .um-field textarea {
        border-color:rgba(148,163,184,.25); background:#0f172a; color:#fff;
    }
    html[data-theme="dark"] #settingsModal .um-field label { color:#e2e8f0; }
    html[data-theme="dark"] #settingsModal .um-module-access-preview {
        border-color:rgba(248,113,113,.22); background:#0f172a;
    }
    html[data-theme="dark"] #settingsModal .um-module-access-toolbar {
        border-color:rgba(148,163,184,.22); background:#172033;
    }
    html[data-theme="dark"] #settingsModal .um-module-selection-summary strong { color:#fff; }
    html[data-theme="dark"] #settingsModal .um-module-selection-summary span { color:#cbd5e1; }
    html[data-theme="dark"] #settingsModal .um-reset-module-defaults {
        border-color:rgba(248,113,113,.3); background:#0f172a; color:#fecaca;
    }
    html[data-theme="dark"] #settingsModal .um-module-access-head h5,
    html[data-theme="dark"] #settingsModal .um-module-title strong,
    html[data-theme="dark"] #settingsModal .um-action-permission strong { color:#fff; }
    html[data-theme="dark"] #settingsModal .um-module-access-head p,
    html[data-theme="dark"] #settingsModal .um-module-copy small,
    html[data-theme="dark"] #settingsModal .um-action-permission small { color:#cbd5e1; }
    html[data-theme="dark"] #settingsModal .um-module-item {
        border-color:rgba(148,163,184,.24); background:#172033;
    }
    html[data-theme="dark"] #settingsModal .um-module-item:hover,
    html[data-theme="dark"] #settingsModal .um-module-item:has(.um-module-option input:checked) {
        border-color:rgba(248,113,113,.42); background:#23171c;
    }
    html[data-theme="dark"] #settingsModal .um-module-actions {
        border-color:rgba(148,163,184,.16); background:#0f172a;
    }
    html[data-theme="dark"] #settingsModal .um-module-expand,
    html[data-theme="dark"] #settingsModal .um-action-check {
        border-color:rgba(148,163,184,.28); background:#172033; color:#fecaca;
    }
    html[data-theme="dark"] #settingsModal .um-action-permission { border-color:rgba(148,163,184,.14); }
    html[data-theme="dark"] #settingsModal .um-action-permission:has(input:checked) { background:#23171c; }
    html[data-theme="dark"] #settingsModal .um-action-permission.is-locked { background:#2a2114; }
    html[data-theme="dark"] #settingsModal .um-superadmin-access-summary,
    html[data-theme="dark"] #settingsModal .um-module-save-warning {
        border-color:rgba(245,158,11,.28); background:#2a2114; color:#fde68a;
    }
    html[data-theme="dark"] #settingsModal .um-superadmin-access-summary strong,
    html[data-theme="dark"] #settingsModal .um-module-save-warning span { color:#fde68a; }
    html[data-theme="dark"] #settingsModal .um-superadmin-access-list span {
        border-color:rgba(245,158,11,.22); background:#17130c; color:#fef3c7;
    }
    html[data-theme="dark"] #settingsModal .um-actions {
        border-color:rgba(148,163,184,.18); background:rgba(17,24,39,.96);
    }
    html[data-theme="dark"] .um-custom-select-button,
    html[data-theme="dark"] .um-custom-select-menu { border-color:rgba(248,113,113,.28); background:#0f172a; color:#fff; }
    html[data-theme="dark"] .um-custom-select-option {
        border-color:rgba(148,163,184,.22); background:#172033; color:#fff;
    }
    @media (max-width: 820px) {
        #settingsModal .um-modal-content { max-height:95vh; }
        #settingsModal .um-modal-body { padding:14px; }
        #settingsModal .um-modal-grid { grid-template-columns:1fr; }
        #settingsModal .um-profile-summary-card { position:static; }
        #settingsModal .um-profile-fields { grid-template-columns:repeat(2,minmax(0,1fr)); }
    }
    @media (max-width: 560px) {
        .um-modal-backdrop { padding:8px; }
        .um-modal-head { padding:14px !important; }
        .um-modal-head-badge { width:40px; height:40px; flex-basis:40px; }
        #settingsModal .um-profile-fields { grid-template-columns:1fr; }
        #settingsModal .um-settings-form-body { padding:14px; }
        #settingsModal .um-module-access-grid { grid-template-columns:1fr; }
        #settingsModal .um-module-access-toolbar { align-items:stretch; flex-direction:column; }
        #settingsModal .um-reset-module-defaults { width:100%; }
        #settingsModal .um-module-actions { padding-left:38px; }
        #settingsModal .um-actions {
            position:static; display:grid; grid-template-columns:1fr 1fr;
            margin:16px -14px -14px; padding:12px 14px;
        }
        #settingsModal .um-settings-action { width:100%; }
    }
</style>
