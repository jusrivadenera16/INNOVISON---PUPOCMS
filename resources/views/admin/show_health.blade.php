@extends('layouts.admin')

@section('title', 'Student Health Profile')

@push('styles')
<style>
    .health-profile-wrap {
        max-width: 1120px;
        margin: 0 auto;
        display: grid;
        gap: 16px;
        padding-right: 116px;
        padding-bottom: 124px;
        box-sizing: border-box;
    }
    #headerQuickActions,
    .quick-actions-wrap,
    .quick-actions-toggle,
    .quick-actions-panel,
    .medicine-alert-fab,
    .medicine-alert-panel {
        z-index: 2147483000 !important;
    }
    .profile-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06); padding: 18px; }
    .profile-hero-card {
        padding: 20px;
        border-color: rgba(112, 19, 27, 0.10);
        box-shadow: 0 18px 38px rgba(15, 23, 42, 0.06);
    }
    .profile-hero-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding-bottom: 16px;
        border-bottom: 1px solid rgba(112, 19, 27, 0.08);
    }
    .profile-hero-main {
        min-width: 0;
    }
    .profile-hero-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(260px, 340px);
        gap: 18px;
        margin-top: 18px;
    }
    .profile-identity {
        display: grid;
        grid-template-columns: 96px minmax(0, 1fr);
        gap: 18px;
        align-items: center;
    }
    .profile-avatar {
        width: 96px;
        height: 96px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        overflow: hidden;
        background: linear-gradient(135deg, #fff7ed, #f1f5f9);
        border: 4px solid #ffffff;
        box-shadow: 0 12px 26px rgba(112, 19, 27, 0.14);
        color: #70131B;
        font-size: 24px;
        font-weight: 900;
    }
    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .profile-name {
        margin: 0;
        color: #0f172a;
        font-size: 24px;
        font-weight: 900;
        line-height: 1.12;
    }
    .profile-course-line {
        margin: 5px 0 0;
        color: #64748b;
        font-size: 13px;
        font-weight: 800;
    }
    .profile-quick-row {
        display: grid;
        grid-template-columns: minmax(146px, 1.25fr) minmax(86px, 0.8fr) minmax(72px, 0.65fr) minmax(188px, 1.55fr) minmax(156px, 1.25fr);
        gap: 12px;
        margin-top: 16px;
    }
    .profile-quick-item {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
        color: #475569;
        font-size: 11px;
        font-weight: 800;
    }
    .profile-quick-icon,
    .profile-meta-icon,
    .timeline-node {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }
    .profile-quick-icon {
        width: 28px;
        height: 28px;
        border-radius: 9px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #70131B;
    }
    .profile-quick-icon svg,
    .profile-meta-icon svg,
    .timeline-node svg {
        width: 15px;
        height: 15px;
    }
    .profile-quick-item strong {
        display: block;
        color: #111827;
        font-size: 11px;
        font-weight: 900;
        word-break: break-word;
    }
    .profile-quick-item:nth-child(4) strong,
    .profile-quick-item:nth-child(5) strong {
        white-space: nowrap;
        word-break: normal;
        overflow-wrap: normal;
    }
    .profile-status-card {
        min-height: 82px;
        border-radius: 14px;
        padding: 10px 12px;
        border: 1px solid #bbf7d0;
        background: linear-gradient(135deg, #f0fdf4, #ecfeff);
        display: flex;
        gap: 10px;
        align-items: center;
        max-width: 285px;
        justify-self: end;
    }
    .profile-status-shield {
        width: 38px;
        height: 38px;
        border-radius: 999px;
        display: grid;
        place-items: center;
        background: #dcfce7;
        color: #16a34a;
    }
    .profile-status-shield svg {
        width: 20px;
        height: 20px;
    }
    .profile-status-card-title {
        margin: 0 0 2px;
        color: #64748b;
        font-size: 9px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .06em;
    }
    .profile-status-card-value {
        margin: 0 0 5px;
        color: #16a34a;
        font-size: 16px;
        font-weight: 900;
    }
    .profile-correction-card {
        margin-top: 16px;
        border: 1px solid #fecaca;
        border-radius: 16px;
        padding: 16px;
        background: linear-gradient(135deg, #fff7f7, #fffefe);
        display: grid;
        grid-template-columns: 44px minmax(0, 1fr) auto;
        align-items: center;
        gap: 14px;
    }
    .profile-correction-icon {
        width: 44px;
        height: 44px;
        border-radius: 999px;
        display: grid;
        place-items: center;
        background: #fee2e2;
        color: #b91c1c;
    }
    .profile-correction-meta {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        color: #64748b;
        font-size: 12px;
        font-weight: 800;
    }
    .profile-last-request {
        display: inline-flex;
        align-items: center;
        min-height: 24px;
        border-radius: 999px;
        padding: 3px 10px;
        background: #f1f5f9;
        color: #475569;
        font-size: 11px;
        font-weight: 900;
    }
    .profile-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
    .profile-title { margin: 0; font-size: 21px; font-weight: 800; color: #0f172a; }
    .profile-sub { margin: 6px 0 0; font-size: 14px; color: #64748b; }
    .profile-top-btn {
        position: relative;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-radius: 10px;
        min-height: 44px;
        padding: 11px 18px;
        font-size: 15px;
        font-weight: 800;
        color: #ffffff;
        background: #70131B;
        border: 1px solid #8f2230;
        text-decoration: none;
        box-shadow:
            0 0 0 2px rgba(112, 19, 27, 0.08),
            0 10px 22px rgba(15, 23, 42, 0.10);
        transition: color .08s linear, transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        z-index: 0;
    }
    .profile-top-btn::after {
        content: "";
        position: absolute;
        top: -35%;
        left: -70%;
        width: 46%;
        height: 170%;
        background:
            linear-gradient(120deg,
                rgba(255, 248, 196, 0) 0%,
                rgba(255, 239, 181, 0.14) 22%,
                rgba(255, 239, 181, 0.52) 48%,
                rgba(255, 239, 181, 0.14) 72%,
                rgba(255, 248, 196, 0) 100%);
        transform: skewX(-18deg);
        transition: left .48s ease;
        z-index: -1;
        pointer-events: none;
    }
    .profile-top-btn:hover {
        color: #70131B !important;
        text-decoration: none;
        transform: translateY(-1px);
        border-color: #facc15;
        background: #facc15;
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.18),
            0 14px 24px rgba(112, 19, 27, 0.16);
    }
    .profile-top-btn,
    .profile-top-btn:visited,
    .profile-top-btn:active,
    .profile-top-btn:focus,
    .profile-top-btn:hover,
    .profile-top-btn span,
    .profile-top-btn svg {
        color: #ffffff !important;
    }
    .profile-top-btn svg,
    .profile-top-btn svg * {
        stroke: #ffffff !important;
    }
    .profile-top-btn:hover span,
    .profile-top-btn:hover svg {
        color: #70131B !important;
    }
    .profile-top-btn:hover svg,
    .profile-top-btn:hover svg * {
        stroke: #70131B !important;
    }
    .profile-top-btn:hover::after {
        left: 128%;
    }
    .profile-top-btn:hover,
    .profile-top-btn:focus {
        color: #70131B !important;
        background: #facc15;
        border-color: #facc15;
    }
    .profile-head-actions { display: inline-flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .profile-switch { display: flex; gap: 10px; flex-wrap: wrap; }
    .profile-switch-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .profile-tab {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #334155;
        border-radius: 10px;
        padding: 11px 16px;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
        box-shadow: 0 6px 14px rgba(15, 23, 42, 0.04);
        transition: transform .18s ease, background .18s ease, color .18s ease, border-color .18s ease;
    }
    .profile-tab.is-active {
        background: #70131B;
        border-color: #8f2230;
        color: #ffffff;
    }
    .profile-tab:hover {
        transform: translateY(-1px);
        border-color: rgba(112, 19, 27, 0.32);
    }
    .profile-tab svg {
        width: 15px;
        height: 15px;
    }
    .profile-status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 30px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        border: 1px solid transparent;
        letter-spacing: 0.02em;
    }
    .profile-status-badge svg {
        width: 14px;
        height: 14px;
        margin-right: 6px;
        stroke-width: 2.2;
        flex: 0 0 auto;
    }
    .profile-status-issued {
        background: #dcfce7;
        color: #166534;
        border-color: #86efac;
    }
    .profile-status-pending {
        background: #ffedd5;
        color: #9a3412;
        border-color: #fdba74;
    }
    .profile-status-rejected {
        background: #fee2e2;
        color: #991b1b;
        border-color: #fca5a5;
    }
    .profile-status-default {
        background: #e2e8f0;
        color: #334155;
        border-color: #cbd5e1;
    }
    .profile-sync-panel {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 14px 16px;
        background: #f8fafc;
    }
    .profile-sync-main {
        display: grid;
        gap: 6px;
    }
    .profile-sync-title {
        margin: 0;
        font-size: 13px;
        font-weight: 900;
        color: #64748b;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .profile-sync-row {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .profile-sync-message {
        margin: 0;
        color: #475569;
        font-size: 13px;
        font-weight: 600;
    }
    .profile-sync-button {
        border: 1px solid #8f2230;
        border-radius: 10px;
        background: #70131B;
        color: #ffffff;
        min-height: 42px;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 900;
        cursor: pointer;
        box-shadow: 0 10px 22px rgba(112, 19, 27, 0.18);
        transition: transform .18s ease, background .18s ease, color .18s ease;
    }
    .profile-sync-button:hover,
    .profile-sync-button:focus {
        transform: translateY(-1px);
        background: #facc15;
        color: #70131B;
    }
    .profile-correction-panel {
        border: 1px solid #fecaca;
        border-radius: 14px;
        padding: 16px;
        background: linear-gradient(135deg, #fff7f7, #ffffff);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
    }
    .profile-correction-title {
        margin: 0;
        color: #7f1d2d;
        font-size: 14px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .profile-correction-copy {
        margin: 5px 0 0;
        color: #64748b;
        font-size: 13px;
        line-height: 1.55;
        max-width: 680px;
    }
    .profile-correction-button,
    .correction-submit {
        position: relative;
        overflow: hidden;
        border: 1px solid #8f2230;
        border-radius: 12px;
        background: #70131B;
        color: #ffffff;
        min-height: 42px;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 900;
        cursor: pointer;
        box-shadow: 0 10px 22px rgba(112, 19, 27, 0.18);
        transition: transform .18s ease, background .18s ease, color .18s ease;
    }
    .profile-correction-button::after,
    .correction-submit::after {
        content: "";
        position: absolute;
        top: -35%;
        left: -70%;
        width: 46%;
        height: 170%;
        background: linear-gradient(120deg, transparent 0%, rgba(255, 244, 180, .18) 34%, rgba(255, 244, 180, .58) 50%, rgba(255, 244, 180, .18) 66%, transparent 100%);
        transform: skewX(-18deg);
        transition: left .48s ease;
        pointer-events: none;
    }
    .profile-correction-button:hover,
    .profile-correction-button:focus,
    .correction-submit:hover,
    .correction-submit:focus {
        transform: translateY(-1px);
        background: #facc15;
        color: #70131B;
    }
    .profile-correction-button:hover::after,
    .profile-correction-button:focus::after,
    .correction-submit:hover::after,
    .correction-submit:focus::after {
        left: 128%;
    }
    .correction-modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 2147482500;
        background: rgba(15, 23, 42, 0.62);
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .correction-modal.is-open {
        display: flex;
    }
    .correction-card {
        width: min(720px, 100%);
        border-radius: 18px;
        background: #ffffff;
        border: 1px solid #fecaca;
        border-bottom: 4px solid #70131B;
        overflow: hidden;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.28);
    }
    .correction-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 18px 20px;
        background: linear-gradient(135deg, #7f1d1d, #b91c1c);
        color: #ffffff;
    }
    .correction-head-title {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }
    .correction-head-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.26);
        background: rgba(255, 255, 255, 0.10);
        color: #facc15;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
    }
    .correction-head-icon svg {
        width: 22px;
        height: 22px;
    }
    .correction-head h3 {
        margin: 0;
        color: #ffffff !important;
        font-size: 18px;
        font-weight: 900;
    }
    .correction-head p {
        margin: 4px 0 0;
        color: rgba(255, 255, 255, 0.88) !important;
        font-size: 13px;
    }
    .correction-close {
        position: relative;
        overflow: hidden;
        width: 38px;
        height: 38px;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,0.24);
        background: rgba(112, 19, 27, 0.45);
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: transform .18s ease, background .18s ease, color .18s ease, border-color .18s ease;
    }
    .correction-close::after {
        content: "";
        position: absolute;
        top: -35%;
        left: -72%;
        width: 48%;
        height: 170%;
        background: linear-gradient(120deg, transparent 0%, rgba(255, 244, 180, .18) 34%, rgba(255, 244, 180, .58) 50%, rgba(255, 244, 180, .18) 66%, transparent 100%);
        transform: skewX(-18deg);
        transition: left .48s ease;
        pointer-events: none;
    }
    .correction-close:hover,
    .correction-close:focus {
        background: #facc15;
        border-color: #facc15;
        color: #70131B;
        transform: translateY(-1px);
    }
    .correction-close:hover::after,
    .correction-close:focus::after {
        left: 128%;
    }
    .correction-close svg {
        width: 18px;
        height: 18px;
    }
    .correction-body {
        padding: 20px;
        display: grid;
        gap: 16px;
    }
    .correction-note {
        border: 1px solid #fed7aa;
        border-radius: 12px;
        background: #fffbeb;
        color: #7c2d12;
        padding: 12px 14px;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.55;
    }
    .correction-doc-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }
    .correction-doc-option {
        display: flex;
        align-items: center;
        gap: 10px;
        border: 1px solid #fecaca;
        border-radius: 12px;
        padding: 12px;
        color: #111827;
        font-size: 13px;
        font-weight: 800;
        background: #fff7f7;
    }
    .correction-doc-option input {
        width: 18px;
        height: 18px;
        accent-color: #7f1d2d;
    }
    .correction-field label {
        display: block;
        margin-bottom: 7px;
        color: #334155;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .correction-field textarea {
        width: 100%;
        min-height: 120px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 12px;
        color: #111827;
        font-size: 14px;
        resize: vertical;
    }
    .correction-select-wrap {
        position: relative;
    }
    .correction-select-wrap::after {
        content: "";
        position: absolute;
        right: 15px;
        top: 50%;
        width: 10px;
        height: 10px;
        border-right: 2px solid #70131B;
        border-bottom: 2px solid #70131B;
        transform: translateY(-65%) rotate(45deg);
        pointer-events: none;
    }
    .correction-field select,
    .correction-field input[type="text"] {
        width: 100%;
        min-height: 48px;
        border: 1px solid #f3c7c7;
        border-bottom: 3px solid #70131B;
        border-radius: 12px;
        padding: 0 42px 0 12px;
        color: #111827;
        background: #ffffff;
        font-size: 14px;
        font-weight: 800;
    }
    .correction-field select {
        appearance: none;
        cursor: pointer;
    }
    .correction-field select:focus,
    .correction-field input[type="text"]:focus,
    .correction-field textarea:focus {
        outline: 0;
        border-color: #8f2230;
        box-shadow: 0 0 0 3px rgba(112, 19, 27, 0.12);
    }
    .correction-other-field {
        display: none;
        margin-top: 10px;
    }
    .correction-other-field.is-open {
        display: block;
    }
    .correction-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
    }
    .correction-cancel {
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        background: #ffffff;
        color: #334155;
        min-height: 42px;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 900;
        cursor: pointer;
    }
    .profile-panel { display: none; }
    .profile-panel.is-active { display: block; }

    .profile-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
    #summaryPanel .profile-grid,
    #healthPanel .profile-grid {
        grid-template-columns: 1fr;
        gap: 8px;
    }
    .profile-meta {
        min-height: 72px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.035);
    }
    #summaryPanel .profile-meta,
    #healthPanel .profile-meta {
        min-height: 52px;
        display: grid;
        grid-template-columns: minmax(170px, 0.45fr) minmax(0, 1fr);
        align-items: center;
        gap: 14px;
        padding: 10px 14px;
    }
    .profile-meta-icon {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: #fff7f7;
        border: 1px solid #fee2e2;
        color: #9f1239;
    }
    .profile-meta-k { font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 900; letter-spacing: .05em; margin-bottom: 4px; }
    #summaryPanel .profile-meta-k,
    #healthPanel .profile-meta-k { margin-bottom: 0; }
    .profile-meta-v { font-size: 14px; color: #0f172a; font-weight: 900; word-break: break-word; line-height: 1.25; }
    .profile-meta.is-wide { grid-column: span 2; }
    .profile-meta.is-full { grid-column: 1 / -1; }
    #healthPanel .profile-meta.is-wide,
    #healthPanel .profile-meta.is-full { grid-column: auto; }

    .profile-timeline-card {
        padding: 18px;
    }
    .profile-timeline-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
    }
    .profile-timeline-title {
        margin: 0;
        color: #0f172a;
        font-size: 13px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .05em;
    }
    .profile-timeline {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0;
        position: relative;
    }
    .profile-timeline::before {
        content: "";
        position: absolute;
        left: 9%;
        right: 9%;
        top: 18px;
        height: 2px;
        background: #86efac;
    }
    .profile-timeline-step {
        position: relative;
        display: grid;
        justify-items: center;
        gap: 8px;
        text-align: center;
        z-index: 1;
    }
    .timeline-node {
        width: 36px;
        height: 36px;
        border-radius: 999px;
        background: #22c55e;
        color: #ffffff;
        border: 4px solid #dcfce7;
    }
    .profile-timeline-step strong,
    .timeline-copy strong {
        display: block;
        color: #0f172a;
        font-size: 12px;
        font-weight: 900;
    }
    .profile-timeline-step span:not(.timeline-node),
    .profile-timeline-step small,
    .timeline-copy span {
        display: block;
        margin-top: 3px;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.35;
    }

    .doc-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px; }
    .doc-file { border: 1px solid #f3c7c7; border-radius: 12px; padding: 12px; background: #fffafa; min-height: 270px; display: flex; flex-direction: column; gap: 8px; }
    .doc-file h4 { margin: 8px 0 3px; font-size: 12px; font-weight: 900; color: #1e293b; }
    .doc-actions { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 0; }
    .doc-link { display: inline-flex; align-items: center; gap: 6px; border: 0; border-radius: 0; padding: 0; color: #475569; font-size: 12px; font-weight: 900; text-decoration: none; background: transparent; }
    .doc-preview { width: 100%; aspect-ratio: 4 / 3; min-height: 210px; height: auto; border: 1px solid #f3d0d0; border-radius: 9px; overflow: hidden; background: #ffffff; }
    .doc-preview iframe, .doc-preview img { width: 100%; height: 100%; border: 0; background: #fff; }
    .doc-preview img { object-fit: cover; }
    .doc-preview-health-form {
        display: grid;
        place-items: center;
        background:
            radial-gradient(circle at 50% 45%, rgba(128, 0, 0, 0.10), transparent 48%),
            linear-gradient(180deg, #fffafa, #ffffff);
    }
    .health-form-thumb {
        position: relative;
        display: grid;
        place-items: center;
        gap: 2px;
        min-height: 152px;
        width: 100%;
        color: #475569;
        font-size: 14px;
        font-weight: 900;
        line-height: 1.05;
        text-transform: uppercase;
        text-align: center;
        isolation: isolate;
    }
    .health-form-thumb::before {
        content: "";
        position: absolute;
        width: min(160px, 82%);
        aspect-ratio: 1;
        border-radius: 999px;
        background: url('{{ asset('images/pup_logo.png') }}') center / contain no-repeat;
        opacity: .09;
        filter: blur(.2px);
        z-index: -1;
    }
    .doc-missing { border: 1px dashed #cbd5e1; color: #64748b; border-radius: 8px; padding: 14px; font-size: 14px; font-weight: 600; background: #f8fafc; }
    .profile-correction-history-wrap {
        position: relative;
        display: inline-flex;
    }
    .profile-correction-history-btn {
        min-height: 24px;
        border-radius: 999px;
        border: 1px solid #fecaca;
        background: #fff7f7;
        color: #7f1d2d;
        padding: 3px 10px;
        font-size: 11px;
        font-weight: 900;
        cursor: default;
    }
    .profile-correction-history-bubble {
        position: absolute;
        right: 0;
        bottom: calc(100% + 8px);
        width: min(300px, 78vw);
        display: none;
        padding: 12px;
        border-radius: 12px;
        border: 1px solid #fecaca;
        background: #ffffff;
        box-shadow: 0 16px 34px rgba(15, 23, 42, .16);
        color: #334155;
        z-index: 10;
    }
    .profile-correction-history-wrap:hover .profile-correction-history-bubble {
        display: block;
    }
    .profile-correction-history-bubble strong {
        display: block;
        color: #7f1d2d;
        font-size: 12px;
        font-weight: 900;
        margin-bottom: 6px;
    }
    .profile-correction-history-bubble span {
        display: block;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.45;
    }

    [data-theme="dark"] .profile-card,
    [data-theme="dark"] .doc-file { background: #0f172a; border-color: #334155; box-shadow: none; }
    [data-theme="dark"] .profile-title,
    [data-theme="dark"] .profile-name,
    [data-theme="dark"] .profile-timeline-title,
    [data-theme="dark"] .profile-meta-v,
    [data-theme="dark"] .doc-file h4 { color: #f8fafc; }
    [data-theme="dark"] .profile-sub,
    [data-theme="dark"] .profile-course-line,
    [data-theme="dark"] .profile-quick-item,
    [data-theme="dark"] .profile-meta-k,
    [data-theme="dark"] .doc-missing { color: #cbd5e1; }
    [data-theme="dark"] .profile-meta { background: #111827; border-color: #334155; }
    [data-theme="dark"] .profile-meta-icon,
    [data-theme="dark"] .profile-quick-icon {
        background: #111827;
        border-color: #334155;
        color: #facc15;
    }
    [data-theme="dark"] .profile-top-btn {
        background: #70131B;
        color: #ffffff !important;
        border-color: rgba(250, 204, 21, 0.30);
        box-shadow:
            0 0 0 3px rgba(112, 19, 27, 0.16),
            0 12px 22px rgba(0, 0, 0, 0.24);
    }
    [data-theme="dark"] .profile-tab { background: #111827; border-color: #475569; color: #f8fafc; }
    [data-theme="dark"] .profile-tab.is-active { background: #70131B; border-color: #8f2230; color: #fff; }
    [data-theme="dark"] .doc-link { background: #111827; border-color: #475569; color: #f8fafc; }
    [data-theme="dark"] .profile-status-issued {
        background: rgba(21, 128, 61, 0.25);
        color: #bbf7d0;
        border-color: rgba(74, 222, 128, 0.55);
    }
    [data-theme="dark"] .profile-status-pending {
        background: rgba(154, 52, 18, 0.25);
        color: #fed7aa;
        border-color: rgba(251, 146, 60, 0.55);
    }
    [data-theme="dark"] .profile-status-rejected {
        background: rgba(153, 27, 27, 0.25);
        color: #fecaca;
        border-color: rgba(248, 113, 113, 0.55);
    }
    [data-theme="dark"] .profile-status-default {
        background: rgba(51, 65, 85, 0.6);
        color: #e2e8f0;
        border-color: rgba(148, 163, 184, 0.35);
    }
    [data-theme="dark"] .profile-sync-panel {
        background: #111827;
        border-color: #334155;
    }
    [data-theme="dark"] .profile-status-card {
        background: rgba(20, 83, 45, 0.28);
        border-color: rgba(74, 222, 128, 0.35);
    }
    [data-theme="dark"] .profile-correction-panel,
    [data-theme="dark"] .profile-correction-card,
    [data-theme="dark"] .correction-card {
        background: #111827;
        border-color: rgba(248, 113, 113, 0.45);
    }
    [data-theme="dark"] .correction-doc-option {
        background: #0f172a;
        border-color: rgba(248, 113, 113, 0.36);
        color: #f8fafc;
    }
    [data-theme="dark"] .correction-field label,
    [data-theme="dark"] .profile-correction-title,
    [data-theme="dark"] .profile-correction-copy {
        color: #cbd5e1;
    }
    [data-theme="dark"] .correction-field textarea {
        background: #0f172a;
        border-color: #334155;
        color: #f8fafc;
    }
    [data-theme="dark"] .correction-field select,
    [data-theme="dark"] .correction-field input[type="text"] {
        background: #0f172a;
        border-color: #334155;
        border-bottom-color: #70131B;
        color: #f8fafc;
    }
    [data-theme="dark"] .correction-select-wrap::after {
        border-color: #facc15;
    }
    [data-theme="dark"] .profile-sync-title,
    [data-theme="dark"] .profile-sync-message {
        color: #cbd5e1;
    }

    @media (max-width: 1024px) {
        .profile-hero-layout {
            grid-template-columns: 1fr;
        }
        .profile-quick-row {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        .profile-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 768px) {
        .health-profile-wrap {
            padding-right: 0;
            padding-bottom: 152px;
        }
        .profile-hero-head,
        .profile-correction-card {
            grid-template-columns: 1fr;
        }
        .profile-identity {
            grid-template-columns: 76px minmax(0, 1fr);
        }
        .profile-avatar {
            width: 76px;
            height: 76px;
        }
        .profile-name {
            font-size: 20px;
        }
        .profile-quick-row,
        .profile-timeline {
            grid-template-columns: 1fr;
        }
        .profile-timeline::before {
            display: none;
        }
        .profile-grid,
        .doc-grid { grid-template-columns: 1fr; }
        .profile-meta.is-wide,
        .profile-meta.is-full { grid-column: auto; }
    }
</style>
@endpush

@section('content')
@php
    $formatProfileDate = function ($value) {
        if (blank($value)) {
            return 'N/A';
        }

        try {
            return \Carbon\Carbon::parse($value)->format('M d, Y');
        } catch (\Throwable $exception) {
            return (string) $value;
        }
    };

    $formatProfileList = function ($value) {
        if (blank($value)) {
            return 'N/A';
        }

        if (is_array($value)) {
            $items = collect($value)
                ->filter(fn ($item) => filled($item))
                ->values();

            return $items->isNotEmpty() ? $items->implode(', ') : 'N/A';
        }

        return (string) $value;
    };

    $medicineAllergies = $formatProfileList($profile->medicine_allergies);
    $medicalHistory = $formatProfileList($profile->medical_history);
    $vaccineHistory = collect($profile->vaccine_history ?? [])
        ->map(function ($dose, $key) use ($formatProfileDate) {
            if (!is_array($dose)) {
                return filled($dose) ? (string) $dose : null;
            }

            $label = \Illuminate\Support\Str::of((string) $key)->replace('_', ' ')->title();
            $date = $formatProfileDate($dose['date'] ?? null);
            $brand = trim((string) ($dose['brand'] ?? ''));
            $details = collect([$date !== 'N/A' ? $date : null, $brand !== '' ? $brand : null])
                ->filter()
                ->implode(' - ');

            return $details !== '' ? "{$label}: {$details}" : null;
        })
        ->filter()
        ->values()
        ->implode('; ');
    $vaccineHistory = $vaccineHistory !== '' ? $vaccineHistory : 'N/A';

    $medicalConditionValue = trim((string) ($profile->medical_condition_remarks ?? ''));
    if ($medicalConditionValue === '') {
        $medicalConditionValue = $profile->hasMedicalCondition()
            ? 'With Condition'
            : 'No Medical Condition Recorded';
    }

    $profileStatusRaw = trim((string) ($profile->clearance_status ?? ''));
    $profileStatusNormalized = in_array($profileStatusRaw, ['Pending', 'For Verification'], true) ? 'Pending' : $profileStatusRaw;
    $profileStatusClass = match ($profileStatusNormalized) {
        'Issued', 'Fully Cleared' => 'profile-status-issued',
        'Pending' => 'profile-status-pending',
        'Rejected' => 'profile-status-rejected',
        default => 'profile-status-default',
    };
    $profileStatusLabel = $profileStatusNormalized !== '' ? $profileStatusNormalized : 'Not Processed';
    $documentRouteName = request()->routeIs('assistant.*') ? 'assistant.walkin.document' : 'walkin.document';
    $isImageDocument = function ($path) {
        $path = parse_url((string) $path, PHP_URL_PATH) ?: (string) $path;
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'], true);
    };
    $displayStudentNumber = trim((string) optional($profile->user)->student_number);
    if ($displayStudentNumber === '' || \Illuminate\Support\Str::startsWith(\Illuminate\Support\Str::upper($displayStudentNumber), 'CLN-')) {
        $displayStudentNumber = 'N/A';
    }
    $profileName = trim((string) ($profile->user->name ?? 'N/A'));
    $profileInitials = collect(explode(' ', $profileName))
        ->filter()
        ->take(2)
        ->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
        ->implode('');
    $profileInitials = $profileInitials !== '' ? $profileInitials : 'HP';
    $profileCourse = trim((string) ($profile->course_college ?: ($profile->user->course ?? 'N/A')));
    $studentPhotoUrl = !empty($profile->student_photo)
        ? route($documentRouteName, ['healthProfile' => $profile->id, 'document' => 'student_photo'])
        : null;
    $puptasSyncRaw = strtolower(trim((string) ($profile->puptas_sync_status ?? '')));
    $puptasReference = strtoupper(trim((string) ($profile->reference_number ?: $profile->student_number ?: optional($profile->user)->student_number)));
    $isLocalPuptasReference = $puptasReference === ''
        || \Illuminate\Support\Str::startsWith($puptasReference, ['CLN-', 'LOC-', 'TEST-LOCAL']);
    if ($puptasSyncRaw === '' && $isLocalPuptasReference) {
        $puptasSyncRaw = 'not_applicable';
    }
    $puptasSyncLabel = match ($puptasSyncRaw) {
        'synced' => 'Synced to PUPTAS',
        'failed' => 'Sync Failed',
        'syncing' => 'Syncing',
        'pending' => 'Pending Sync',
        'not_applicable' => 'Not Applicable',
        'missing_reference_number' => 'Missing Reference',
        default => 'Not Synced',
    };
    $puptasSyncClass = match ($puptasSyncRaw) {
        'synced' => 'profile-status-issued',
        'failed', 'missing_reference_number' => 'profile-status-rejected',
        'syncing', 'pending' => 'profile-status-pending',
        'not_applicable' => 'profile-status-default',
        default => 'profile-status-pending',
    };
    $canResyncPuptas = in_array($profileStatusNormalized, ['Issued', 'Fully Cleared'], true)
        && !in_array($puptasSyncRaw, ['synced', 'not_applicable'], true);
    $canRequestFileCorrection = in_array($profileStatusNormalized, ['Issued', 'Fully Cleared'], true);
    $hasCorrectionRequest = !empty($profile->resubmission_required_documents) || !empty($profile->resubmission_requested_at);
    $correctionStatusLabel = !$hasCorrectionRequest
        ? 'None'
        : (!empty($profile->resubmitted_at) && (empty($profile->resubmission_requested_at) || $profile->resubmitted_at->greaterThanOrEqualTo($profile->resubmission_requested_at))
            ? 'Submitted'
            : 'Pending');
    $correctionHistoryText = collect([
        $profile->resubmission_requested_at ? 'Requested: ' . $profile->resubmission_requested_at->format('M d, Y h:i A') : null,
        $profile->resubmitted_at ? 'Submitted: ' . $profile->resubmitted_at->format('M d, Y h:i A') : null,
        $profile->pending_reason ? 'Reason: ' . $profile->pending_reason : null,
    ])->filter()->implode("\n");
@endphp
<div class="health-profile-wrap">
    <div class="profile-card profile-hero-card">
        <div class="profile-hero-head">
            <div class="profile-hero-main">
                <h1 class="profile-title">Student Health Profile</h1>
                <p class="profile-sub">Issued health profile details and submitted documents.</p>
            </div>
            <div class="profile-head-actions">
                <a href="{{ route('admin.health_records') }}" class="profile-top-btn">
                    <span aria-hidden="true">&larr;</span>
                    Back
                </a>
            </div>
        </div>

        <div class="profile-hero-layout">
            <div>
                <div class="profile-identity">
                    <div class="profile-avatar">
                        @if($studentPhotoUrl)
                            <img src="{{ $studentPhotoUrl }}" alt="{{ $profileName }}">
                        @else
                            {{ $profileInitials }}
                        @endif
                    </div>
                    <div>
                        <h2 class="profile-name">{{ $profileName }}</h2>
                        <p class="profile-course-line">{{ $profileCourse }}</p>
                    </div>
                </div>

                <div class="profile-quick-row">
                    <div class="profile-quick-item">
                        <span class="profile-quick-icon"><x-outline-icon name="academic-cap" /></span>
                        <span>Student No.<strong>{{ $displayStudentNumber }}</strong></span>
                    </div>
                    <div class="profile-quick-item">
                        <span class="profile-quick-icon"><x-outline-icon name="user-circle" /></span>
                        <span>Gender<strong>{{ $profile->sex ?: 'N/A' }}</strong></span>
                    </div>
                    <div class="profile-quick-item">
                        <span class="profile-quick-icon"><x-outline-icon name="calendar-days" /></span>
                        <span>Age<strong>{{ $profile->age ?: ($calculatedAge ?: 'N/A') }}</strong></span>
                    </div>
                    <div class="profile-quick-item">
                        <span class="profile-quick-icon"><x-outline-icon name="envelope" /></span>
                        <span>Email<strong>{{ $profile->user->email ?? 'N/A' }}</strong></span>
                    </div>
                    <div class="profile-quick-item">
                        <span class="profile-quick-icon"><x-outline-icon name="phone" /></span>
                        <span>Contact<strong>{{ $profile->cellphone ?: ($profile->contact_no ?: 'N/A') }}</strong></span>
                    </div>
                </div>
            </div>

            <div class="profile-status-card">
                <div class="profile-status-shield"><x-outline-icon name="check" /></div>
                <div>
                    <p class="profile-status-card-title">Health Record Status</p>
                    <p class="profile-status-card-value">{{ $profileStatusLabel }}</p>
                    <span class="profile-status-badge {{ $puptasSyncClass }}">
                        @if($puptasSyncRaw === 'synced')
                            <x-outline-icon name="check" />
                        @elseif(in_array($puptasSyncRaw, ['failed', 'missing_reference_number'], true))
                            <x-outline-icon name="exclamation-triangle" />
                        @elseif(in_array($puptasSyncRaw, ['syncing', 'pending'], true))
                            <x-outline-icon name="clock" />
                        @else
                            <x-outline-icon name="information-circle" />
                        @endif
                        {{ $puptasSyncLabel }}
                    </span>
                    @if($profile->puptas_synced_at)
                        <p class="profile-sync-message" style="margin-top:8px;">Last synced: {{ $profile->puptas_synced_at->format('M d, Y h:i A') }}</p>
                    @endif
                    @if($canResyncPuptas)
                        <form method="POST" action="{{ route('admin.health_profile.resync_puptas', $profile->id) }}" style="margin-top:10px;">
                            @csrf
                            <button type="submit" class="profile-sync-button">Resync to PUPTAS</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

    </div>

    <div class="profile-card">
        <div class="profile-switch-head">
            <div class="profile-switch" role="tablist" aria-label="Health profile sections">
                <button type="button" class="profile-tab is-active" data-profile-tab-target="summaryPanel">
                    <x-outline-icon name="user-circle" />
                    Personal Information
                </button>
                <button type="button" class="profile-tab" data-profile-tab-target="healthPanel">
                    <x-outline-icon name="information-circle" />
                    Health Profile
                </button>
                <button type="button" class="profile-tab" data-profile-tab-target="docsPanel">
                    <x-outline-icon name="document-text" />
                    Uploaded Documents
                </button>
            </div>
            <span class="profile-status-badge {{ $profileStatusClass }}">
                @if(in_array($profileStatusNormalized, ['Issued', 'Fully Cleared'], true))
                    <x-outline-icon name="check" />
                @elseif($profileStatusNormalized === 'Pending')
                    <x-outline-icon name="clock" />
                @elseif($profileStatusNormalized === 'Rejected')
                    <x-outline-icon name="exclamation-triangle" />
                @else
                    <x-outline-icon name="information-circle" />
                @endif
                Status: {{ $profileStatusLabel }}
            </span>
        </div>
    </div>

    <div class="profile-card profile-panel is-active" id="summaryPanel">
        <div class="profile-grid">
            <div class="profile-meta"><div class="profile-meta-k">Student Name</div><div class="profile-meta-v">{{ $profile->user->name ?? 'N/A' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Student Number</div><div class="profile-meta-v">{{ $displayStudentNumber }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Course</div><div class="profile-meta-v">{{ $profile->course_college ?: ($profile->user->course ?? 'N/A') }}</div></div>

            <div class="profile-meta"><div class="profile-meta-k">Year / Section</div><div class="profile-meta-v">{{ trim(($profile->user->year ?? '') . '-' . ($profile->user->section ?? '')) ?: 'N/A' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Email</div><div class="profile-meta-v">{{ $profile->user->email ?? 'N/A' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Status</div><div class="profile-meta-v">{{ in_array($profile->clearance_status, ['Pending', 'For Verification'], true) ? 'For Verification' : ($profile->clearance_status ?: 'Not Processed') }}</div></div>

            <div class="profile-meta"><div class="profile-meta-k">Gender</div><div class="profile-meta-v">{{ $profile->sex ?: 'N/A' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Civil Status</div><div class="profile-meta-v">{{ $profile->civil_status ?: 'N/A' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Age</div><div class="profile-meta-v">{{ $profile->age ?: ($calculatedAge ?: 'N/A') }}</div></div>

            <div class="profile-meta"><div class="profile-meta-k">Blood Type</div><div class="profile-meta-v">{{ $profile->blood_type ?: 'N/A' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Height</div><div class="profile-meta-v">{{ $profile->height ?: 'N/A' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Weight</div><div class="profile-meta-v">{{ $profile->weight ?: 'N/A' }}</div></div>

            <div class="profile-meta"><div class="profile-meta-k">Guardian Name</div><div class="profile-meta-v">{{ $profile->guardian_name ?: 'N/A' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Guardian Contact</div><div class="profile-meta-v">{{ $profile->cellphone ?: ($profile->contact_no ?: 'N/A') }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Submitted At</div><div class="profile-meta-v">{{ optional($profile->created_at)->format('M d, Y h:i A') ?: 'N/A' }}</div></div>
        </div>
    </div>

    <div class="profile-card profile-panel" id="healthPanel">
        <div class="profile-grid">
            <div class="profile-meta"><div class="profile-meta-k">Medical Condition</div><div class="profile-meta-v">{{ $medicalConditionValue }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Physical Assessment</div><div class="profile-meta-v">{{ $profile->physical_assessment_status ?: 'N/A' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Documents Valid</div><div class="profile-meta-v">{{ $profile->documents_valid ? 'Yes' : 'No' }}</div></div>

            <div class="profile-meta"><div class="profile-meta-k">Assessment Date</div><div class="profile-meta-v">{{ $formatProfileDate($profile->assessment_date) }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Verified At</div><div class="profile-meta-v">{{ $formatProfileDate($profile->verified_at) }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Pending Reason</div><div class="profile-meta-v">{{ $profile->pending_reason ?: 'N/A' }}</div></div>

            <div class="profile-meta"><div class="profile-meta-k">Blood Pressure</div><div class="profile-meta-v">{{ $profile->blood_pressure ?: 'N/A' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Pulse Rate</div><div class="profile-meta-v">{{ $profile->pulse_rate ?: 'N/A' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Respiratory Rate</div><div class="profile-meta-v">{{ $profile->respiratory_rate ?: 'N/A' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Temperature</div><div class="profile-meta-v">{{ $profile->temperature ?: 'N/A' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">COVID Positive</div><div class="profile-meta-v">{{ $profile->covid_positive ?: 'N/A' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">COVID Positive Date</div><div class="profile-meta-v">{{ $formatProfileDate($profile->covid_positive_date) }}</div></div>

            <div class="profile-meta"><div class="profile-meta-k">Known Medical Illness</div><div class="profile-meta-v">{{ $profile->has_illness ?: 'N/A' }}</div></div>
            <div class="profile-meta is-wide"><div class="profile-meta-k">Medical History</div><div class="profile-meta-v">{{ $medicalHistory }}</div></div>
            <div class="profile-meta is-full"><div class="profile-meta-k">Other Illness / Medical Notes</div><div class="profile-meta-v">{{ $profile->other_illness ?: 'N/A' }}</div></div>

            <div class="profile-meta"><div class="profile-meta-k">Has Disability</div><div class="profile-meta-v">{{ $profile->has_disability ?: 'N/A' }}</div></div>
            <div class="profile-meta is-wide"><div class="profile-meta-k">Disability Type</div><div class="profile-meta-v">{{ $profile->disability_type ?: 'N/A' }}</div></div>

            <div class="profile-meta"><div class="profile-meta-k">No Known Allergies</div><div class="profile-meta-v">{{ $profile->no_allergies ? 'Yes' : 'No' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Food Allergies</div><div class="profile-meta-v">{{ $profile->food_allergies ?: 'N/A' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Medicine Allergies</div><div class="profile-meta-v">{{ $medicineAllergies }}</div></div>
            <div class="profile-meta is-full"><div class="profile-meta-k">Other Medicine Allergies</div><div class="profile-meta-v">{{ $profile->other_med_allergies ?: 'N/A' }}</div></div>

            <div class="profile-meta"><div class="profile-meta-k">Smoker</div><div class="profile-meta-v">{{ $profile->is_smoker ?: 'N/A' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Alcohol Drinker</div><div class="profile-meta-v">{{ $profile->is_drinker ?: 'N/A' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">COVID Vaccinated</div><div class="profile-meta-v">{{ $profile->covid_vaccinated ?: 'N/A' }}</div></div>
            <div class="profile-meta is-full"><div class="profile-meta-k">Vaccination History</div><div class="profile-meta-v">{{ $vaccineHistory }}</div></div>

            <div class="profile-meta"><div class="profile-meta-k">Medical Certificate Doctor</div><div class="profile-meta-v">{{ $profile->doctor_name ?: ($profile->medical_certificate_issued_by ?: 'N/A') }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Medical Certificate Date</div><div class="profile-meta-v">{{ $formatProfileDate($profile->med_cert_date ?: $profile->medical_certificate_issued_at) }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Medical Certificate Result</div><div class="profile-meta-v">{{ $profile->med_cert_findings ?: 'N/A' }}</div></div>
            <div class="profile-meta is-full"><div class="profile-meta-k">Student Declared Medical Findings</div><div class="profile-meta-v">{{ $profile->med_cert_findings_details ?: 'N/A' }}</div></div>

            <div class="profile-meta"><div class="profile-meta-k">Chest X-ray Date</div><div class="profile-meta-v">{{ $formatProfileDate($profile->xray_date ?: $profile->chest_xray_date) }}</div></div>
            <div class="profile-meta is-wide"><div class="profile-meta-k">Chest X-ray Result</div><div class="profile-meta-v">{{ $profile->xray_findings ?: ($profile->chest_xray_result_text ?: 'N/A') }}</div></div>
            <div class="profile-meta is-full"><div class="profile-meta-k">Student Declared X-ray Findings</div><div class="profile-meta-v">{{ $profile->xray_findings_details ?: 'N/A' }}</div></div>

            <div class="profile-meta is-full"><div class="profile-meta-k">Assessment Remarks</div><div class="profile-meta-v">{{ $profile->assessment_remarks ?: 'N/A' }}</div></div>
        </div>
    </div>

    <div class="profile-card profile-panel" id="docsPanel">
        <div class="doc-grid">
            <div class="doc-file">
                <h4>Health Information Form</h4>
                @php($healthInformationFormUrl = route('walkin.healthForm', ['healthProfile' => $profile->id]))
                <div class="doc-actions">
                    <a class="doc-link" href="{{ $healthInformationFormUrl }}" target="_blank" rel="noopener">
                        <x-outline-icon name="document-text" /> Open
                    </a>
                </div>
                <div class="doc-preview doc-preview-health-form">
                    <div class="health-form-thumb">
                        <span>Health</span>
                        <span>Information</span>
                        <span>Form</span>
                    </div>
                </div>
            </div>

            <div class="doc-file">
                <h4>Medical Certificate (PDF)</h4>
                @if(!empty($profile->medical_certificate))
                    @php($medicalCertificateUrl = route($documentRouteName, ['healthProfile' => $profile->id, 'document' => 'medical_certificate']))
                    <div class="doc-actions">
                        <a class="doc-link" href="{{ $medicalCertificateUrl }}" target="_blank" rel="noopener">
                            <x-outline-icon name="document-text" /> Open
                        </a>
                    </div>
                    <div class="doc-preview">
                        @if($isImageDocument($profile->medical_certificate))
                            <img src="{{ $medicalCertificateUrl }}" alt="Medical Certificate">
                        @else
                            <iframe src="{{ $medicalCertificateUrl }}" title="Medical Certificate preview"></iframe>
                        @endif
                    </div>
                @else
                    <div class="doc-missing">No medical certificate uploaded.</div>
                @endif
            </div>

            <div class="doc-file">
                <h4>Medical Assessment Copy</h4>
                @if(!empty($profile->medical_assessment_upload))
                    @php($medicalAssessmentUrl = route($documentRouteName, ['healthProfile' => $profile->id, 'document' => 'medical_assessment_upload']))
                    <div class="doc-actions">
                        <a class="doc-link" href="{{ $medicalAssessmentUrl }}" target="_blank" rel="noopener">
                            <x-outline-icon name="document-text" /> Open
                        </a>
                    </div>
                    <div class="doc-preview">
                        @if($isImageDocument($profile->medical_assessment_upload))
                            <img src="{{ $medicalAssessmentUrl }}" alt="Medical Assessment Copy">
                        @else
                            <iframe src="{{ $medicalAssessmentUrl }}" title="Medical Assessment Copy preview"></iframe>
                        @endif
                    </div>
                @else
                    <div class="doc-missing">No medical assessment copy uploaded.</div>
                @endif
            </div>

            <div class="doc-file">
                <h4>Health Declaration</h4>
                @if(!empty($profile->health_declaration))
                    @php($healthDeclarationUrl = route($documentRouteName, ['healthProfile' => $profile->id, 'document' => 'health_declaration']))
                    <div class="doc-actions">
                        <a class="doc-link" href="{{ $healthDeclarationUrl }}" target="_blank" rel="noopener">
                            <x-outline-icon name="document-text" /> Open
                        </a>
                    </div>
                    <div class="doc-preview">
                        @if($isImageDocument($profile->health_declaration))
                            <img src="{{ $healthDeclarationUrl }}" alt="Health Declaration">
                        @else
                            <iframe src="{{ $healthDeclarationUrl }}" title="Health Declaration preview"></iframe>
                        @endif
                    </div>
                @else
                    <div class="doc-missing">No health declaration uploaded.</div>
                @endif
            </div>

            <div class="doc-file">
                <h4>Chest X-ray Result (PDF)</h4>
                @if(!empty($profile->chest_xray_result))
                    @php($chestXrayUrl = route($documentRouteName, ['healthProfile' => $profile->id, 'document' => 'chest_xray_result']))
                    <div class="doc-actions">
                        <a class="doc-link" href="{{ $chestXrayUrl }}" target="_blank" rel="noopener">
                            <x-outline-icon name="document-text" /> Open
                        </a>
                    </div>
                    <div class="doc-preview">
                        @if($isImageDocument($profile->chest_xray_result))
                            <img src="{{ $chestXrayUrl }}" alt="Chest X-ray Result">
                        @else
                            <iframe src="{{ $chestXrayUrl }}" title="Chest X-ray Result preview"></iframe>
                        @endif
                    </div>
                @else
                    <div class="doc-missing">No chest X-ray result uploaded.</div>
                @endif
            </div>

            @if(($profile->has_disability ?? 'No') === 'Yes' || !empty($profile->pwd_id_proof))
                <div class="doc-file">
                    <h4>PWD ID Proof (PDF)</h4>
                    @if(!empty($profile->pwd_id_proof))
                        @php($pwdIdProofUrl = route($documentRouteName, ['healthProfile' => $profile->id, 'document' => 'pwd_id_proof']))
                        <div class="doc-actions">
                            <a class="doc-link" href="{{ $pwdIdProofUrl }}" target="_blank" rel="noopener">
                                <x-outline-icon name="document-text" /> Open
                            </a>
                        </div>
                        <div class="doc-preview"><iframe src="{{ $pwdIdProofUrl }}"></iframe></div>
                    @else
                        <div class="doc-missing">PWD is Yes but no proof uploaded.</div>
                    @endif
                </div>
            @endif

            <div class="doc-file">
                <h4>2x2 Student Photo</h4>
                @if(!empty($profile->student_photo))
                    @php($studentPhotoUrl = route($documentRouteName, ['healthProfile' => $profile->id, 'document' => 'student_photo']))
                    <div class="doc-actions">
                        <a class="doc-link" href="{{ $studentPhotoUrl }}" target="_blank" rel="noopener">
                            <x-outline-icon name="eye" /> Open
                        </a>
                    </div>
                    <div class="doc-preview"><img src="{{ $studentPhotoUrl }}" alt="2x2 Student Photo"></div>
                @else
                    <div class="doc-missing">No 2x2 student photo uploaded.</div>
                @endif
            </div>
        </div>

        @if($canRequestFileCorrection)
            <div class="profile-correction-card">
                <div class="profile-correction-icon"><x-outline-icon name="document-text" /></div>
                <div>
                    <p class="profile-correction-title">File Correction</p>
                    <p class="profile-correction-copy">Request file replacement or health form correction without deleting approval history or PUPTAS sync records.</p>
                    <div class="profile-correction-meta">
                        <span>Last Request:</span>
                        <span class="profile-last-request">{{ $profile->resubmission_requested_at ? $profile->resubmission_requested_at->format('M d, Y h:i A') : 'None' }}</span>
                        <span>Status:</span>
                        <span class="profile-last-request">{{ $correctionStatusLabel }}</span>
                        @if($hasCorrectionRequest)
                            <span class="profile-correction-history-wrap">
                                <button type="button" class="profile-correction-history-btn">History</button>
                                <span class="profile-correction-history-bubble">
                                    <strong>Correction History</strong>
                                    <span>{!! nl2br(e($correctionHistoryText !== '' ? $correctionHistoryText : 'No history yet.')) !!}</span>
                                </span>
                            </span>
                        @endif
                    </div>
                </div>
                <button type="button" class="profile-correction-button" id="openCorrectionModal">
                    Request File Correction
                </button>
            </div>
        @endif
    </div>

    <div class="profile-card profile-timeline-card">
        <div class="profile-timeline-head">
            <h3 class="profile-timeline-title">Health Record Timeline</h3>
        </div>
        <div class="profile-timeline">
            <div class="profile-timeline-step">
                <span class="timeline-node"><x-outline-icon name="check" /></span>
                <strong>Profile Submitted</strong>
                <span>{{ optional($profile->created_at)->format('M d, Y h:i A') ?: 'N/A' }}</span>
                <small>Student submitted health requirements</small>
            </div>
            <div class="profile-timeline-step">
                <span class="timeline-node"><x-outline-icon name="check" /></span>
                <strong>Assessment Completed</strong>
                <span>{{ $formatProfileDate($profile->assessment_date ?: $profile->verified_at) }}</span>
                <small>Initial assessment and review completed</small>
            </div>
            <div class="profile-timeline-step">
                <span class="timeline-node"><x-outline-icon name="check" /></span>
                <strong>Synced to PUPTAS</strong>
                <span>{{ $profile->puptas_synced_at ? $profile->puptas_synced_at->format('M d, Y h:i A') : $puptasSyncLabel }}</span>
                <small>Health record sync status</small>
            </div>
            <div class="profile-timeline-step">
                <span class="timeline-node"><x-outline-icon name="check" /></span>
                <strong>{{ $profileStatusLabel }}</strong>
                <span>{{ $formatProfileDate($profile->verified_at ?: $profile->updated_at) }}</span>
                <small>Current clearance state</small>
            </div>
        </div>
    </div>

</div>

@if($canRequestFileCorrection)
    <div class="correction-modal" id="correctionModal" aria-hidden="true">
        <div class="correction-card">
            <div class="correction-head">
                <div class="correction-head-title">
                    <span class="correction-head-icon"><x-outline-icon name="document-text" /></span>
                    <div>
                        <h3>Request File Correction</h3>
                        <p>Select file/s for replacement or Health Form Correction so the student can update their health information.</p>
                    </div>
                </div>
                <button type="button" class="correction-close" id="closeCorrectionModal" aria-label="Close correction modal">
                    <x-outline-icon name="x-mark" />
                </button>
            </div>
            <form method="POST" action="{{ route('admin.health_profile.request_resubmission', $profile->id) }}" class="correction-body">
                @csrf
                <div class="correction-note">
                    This request keeps the existing approval and PUPTAS sync history. The student will only update the selected file/s or health form data.
                </div>
                <div class="correction-doc-grid">
                    <label class="correction-doc-option">
                        <input type="checkbox" name="resubmission_required_documents[]" value="student_photo">
                        <span>2x2 Student Photo</span>
                    </label>
                    <label class="correction-doc-option">
                        <input type="checkbox" name="resubmission_required_documents[]" value="health_declaration">
                        <span>Declaration Form</span>
                    </label>
                    <label class="correction-doc-option">
                        <input type="checkbox" name="resubmission_required_documents[]" value="medical_certificate">
                        <span>Medical Certificate</span>
                    </label>
                    <label class="correction-doc-option">
                        <input type="checkbox" name="resubmission_required_documents[]" value="chest_xray_result">
                        <span>Chest X-ray Result</span>
                    </label>
                    <label class="correction-doc-option">
                        <input type="checkbox" name="resubmission_required_documents[]" value="pwd_id_proof">
                        <span>PWD ID Proof</span>
                    </label>
                    <label class="correction-doc-option">
                        <input type="checkbox" id="correctionHealthFormOption" name="needs_health_form_correction" value="1">
                        <span>Health Form Correction</span>
                    </label>
                </div>
                <div class="correction-field">
                    <label for="correctionReasonSelect">Reason</label>
                    <input type="hidden" id="correctionReason" name="pending_reason" required>
                    <div class="correction-select-wrap">
                        <select id="correctionReasonSelect" required>
                            <option value="">Select a reason</option>
                            <option value="Blurred or unreadable uploaded document">Blurred or unreadable uploaded document</option>
                            <option value="Incorrect file was uploaded">Incorrect file was uploaded</option>
                            <option value="Document is missing signature">Document is missing signature</option>
                            <option value="Document is expired or outdated">Document is expired or outdated</option>
                            <option value="Document has incomplete information">Document has incomplete information</option>
                            <option value="Wrong student document was uploaded">Wrong student document was uploaded</option>
                            <option value="Photo or scan must be clearer">Photo or scan must be clearer</option>
                            <option value="Health Form Correction">Health Form Correction</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>
                    <div class="correction-other-field" id="correctionOtherField">
                        <label for="correctionReasonOther">Other Reason</label>
                        <textarea id="correctionReasonOther" placeholder="Type the specific reason for replacement."></textarea>
                    </div>
                </div>
                <div class="correction-actions">
                    <button type="button" class="correction-cancel" id="cancelCorrectionModal">Cancel</button>
                    <button type="submit" class="correction-submit">Send Correction Request</button>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-profile-tab-target]').forEach(function (button) {
        button.addEventListener('click', function () {
            const targetId = button.getAttribute('data-profile-tab-target');
            if (!targetId) return;

            document.querySelectorAll('[data-profile-tab-target]').forEach(function (tabButton) {
                tabButton.classList.remove('is-active');
            });
            document.querySelectorAll('.profile-panel').forEach(function (panel) {
                panel.classList.remove('is-active');
            });

            button.classList.add('is-active');
            const targetPanel = document.getElementById(targetId);
            if (targetPanel) {
                targetPanel.classList.add('is-active');
            }
        });
    });

    const correctionModal = document.getElementById('correctionModal');
    const openCorrectionModal = document.getElementById('openCorrectionModal');
    const closeCorrectionModal = document.getElementById('closeCorrectionModal');
    const cancelCorrectionModal = document.getElementById('cancelCorrectionModal');
    const correctionReason = document.getElementById('correctionReason');
    const correctionReasonSelect = document.getElementById('correctionReasonSelect');
    const correctionReasonOther = document.getElementById('correctionReasonOther');
    const correctionOtherField = document.getElementById('correctionOtherField');
    const correctionHealthFormOption = document.getElementById('correctionHealthFormOption');
    const correctionForm = document.querySelector('.correction-body');

    function syncCorrectionReason() {
        if (!correctionReason || !correctionReasonSelect) return '';

        const selectedReason = correctionReasonSelect.value.trim();
        const otherReason = correctionReasonOther ? correctionReasonOther.value.trim() : '';
        const finalReason = selectedReason === 'Others' ? otherReason : selectedReason;

        correctionReason.value = finalReason;
        if (correctionOtherField) {
            correctionOtherField.classList.toggle('is-open', selectedReason === 'Others');
        }
        if (correctionReasonOther) {
            correctionReasonOther.required = selectedReason === 'Others';
        }

        return finalReason;
    }

    function setCorrectionModal(open) {
        if (!correctionModal) return;
        correctionModal.classList.toggle('is-open', open);
        correctionModal.setAttribute('aria-hidden', open ? 'false' : 'true');
    }

    openCorrectionModal?.addEventListener('click', function () {
        setCorrectionModal(true);
    });

    closeCorrectionModal?.addEventListener('click', function () {
        setCorrectionModal(false);
    });

    cancelCorrectionModal?.addEventListener('click', function () {
        setCorrectionModal(false);
    });

    correctionReasonSelect?.addEventListener('change', syncCorrectionReason);
    correctionReasonOther?.addEventListener('input', syncCorrectionReason);
    correctionHealthFormOption?.addEventListener('change', function () {
        if (correctionHealthFormOption.checked && correctionReasonSelect && correctionReasonSelect.value === '') {
            correctionReasonSelect.value = 'Health Form Correction';
            syncCorrectionReason();
        }
    });
    correctionForm?.addEventListener('submit', function (event) {
        const finalReason = syncCorrectionReason();
        const hasSelectedDocument = Boolean(correctionForm.querySelector('input[name="resubmission_required_documents[]"]:checked'));
        const hasHealthFormCorrection = Boolean(correctionHealthFormOption?.checked);
        if (!hasSelectedDocument && !hasHealthFormCorrection) {
            event.preventDefault();
            alert('Select at least one file or Health Form Correction.');
            return;
        }
        if (!finalReason) {
            event.preventDefault();
            if (correctionReasonSelect?.value === 'Others' && correctionReasonOther) {
                correctionReasonOther.reportValidity();
            } else {
                correctionReasonSelect?.reportValidity();
            }
        }
    });

    correctionModal?.addEventListener('click', function (event) {
        if (event.target === correctionModal) {
            setCorrectionModal(false);
        }
    });
</script>
@endpush
