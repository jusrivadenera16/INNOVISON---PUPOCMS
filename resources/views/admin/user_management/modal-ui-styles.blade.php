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
        #settingsModal .um-actions {
            position:static; display:grid; grid-template-columns:1fr 1fr;
            margin:16px -14px -14px; padding:12px 14px;
        }
        #settingsModal .um-settings-action { width:100%; }
    }
</style>
