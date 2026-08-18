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
        cursor: pointer;
        font-family: inherit;
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
    .profile-top-form {
        margin: 0;
        display: inline-flex;
    }
    .profile-top-btn-warning {
        background: #9a3412;
        border-color: #c2410c;
    }
    .profile-top-btn-warning:hover,
    .profile-top-btn-warning:focus {
        background: #facc15;
        border-color: #facc15;
    }
    .profile-switch { display: flex; gap: 10px; flex-wrap: wrap; }
    .profile-switch-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .profile-actions-menu-wrap {
        position: relative;
        margin-left: auto;
    }
    .profile-actions-toggle {
        width: 42px;
        height: 42px;
        display: inline-grid;
        place-items: center;
        border-radius: 12px;
        border: 1px solid rgba(112, 19, 27, 0.18);
        background: #ffffff;
        color: #70131B;
        cursor: pointer;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
        transition: transform .18s ease, background .18s ease, color .18s ease, border-color .18s ease, box-shadow .18s ease;
    }
    .profile-actions-toggle svg {
        width: 22px;
        height: 22px;
        stroke-width: 2;
    }
    .profile-actions-toggle:hover,
    .profile-actions-toggle:focus {
        transform: translateY(-1px);
        background: #facc15;
        border-color: #facc15;
        color: #70131B;
        box-shadow: 0 12px 26px rgba(250, 204, 21, 0.26);
        outline: none;
    }
    .profile-actions-menu {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        z-index: 20;
        min-width: 232px;
        display: none;
        padding: 8px;
        border-radius: 14px;
        border: 1px solid rgba(112, 19, 27, 0.16);
        background: #ffffff;
        box-shadow: 0 20px 42px rgba(15, 23, 42, 0.16);
    }
    .profile-actions-menu.is-open {
        display: grid;
        gap: 8px;
    }
    .profile-actions-menu button {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        min-height: 40px;
        padding: 10px 12px;
        border-radius: 10px;
        border: 1px solid rgba(112, 19, 27, 0.12);
        background: #fffafa;
        color: #70131B;
        font-size: 12px;
        font-weight: 900;
        text-align: left;
        cursor: pointer;
        transition: background .18s ease, border-color .18s ease, color .18s ease, transform .18s ease;
    }
    .profile-actions-menu button:hover,
    .profile-actions-menu button:focus {
        transform: translateY(-1px);
        background: #facc15;
        border-color: #facc15;
        color: #70131B;
        outline: none;
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
    .correction-head-title.is-hidden {
        display: none;
    }
    .correction-modal-back {
        display: none;
        width: 38px;
        height: 38px;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,0.24);
        background: rgba(112, 19, 27, 0.45);
        color: #ffffff;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: transform .18s ease, background .18s ease, color .18s ease;
    }
    .correction-modal-back.is-visible {
        display: inline-flex;
    }
    .correction-modal-back:hover,
    .correction-modal-back:focus {
        transform: translateY(-1px);
        background: #facc15;
        color: #70131B;
        outline: none;
    }
    .correction-modal-back svg {
        width: 18px;
        height: 18px;
    }
    .correction-head-actions {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        flex: 0 0 auto;
    }
    .correction-actions-menu-wrap {
        position: relative;
    }
    .correction-actions-toggle {
        width: 38px;
        height: 38px;
        display: inline-grid;
        place-items: center;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,0.24);
        background: rgba(112, 19, 27, 0.45);
        color: #ffffff;
        cursor: pointer;
        transition: transform .18s ease, background .18s ease, color .18s ease;
    }
    .correction-actions-toggle svg {
        width: 20px;
        height: 20px;
    }
    .correction-actions-toggle:hover,
    .correction-actions-toggle:focus {
        transform: translateY(-1px);
        background: #facc15;
        color: #70131B;
        outline: none;
    }
    .correction-actions-menu {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        z-index: 30;
        min-width: 170px;
        display: none;
        padding: 8px;
        border-radius: 14px;
        border: 1px solid rgba(112, 19, 27, .18);
        background: #ffffff;
        box-shadow: 0 20px 42px rgba(15, 23, 42, .18);
    }
    .correction-actions-menu.is-open {
        display: grid;
        gap: 8px;
    }
    .correction-actions-menu button {
        min-height: 38px;
        padding: 9px 12px;
        border-radius: 10px;
        border: 1px solid rgba(112, 19, 27, .14);
        background: #fffafa;
        color: #70131B;
        text-align: left;
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
    }
    .correction-actions-menu button:hover,
    .correction-actions-menu button:focus {
        background: #facc15;
        border-color: #facc15;
        outline: none;
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
    .correction-body.is-hidden {
        display: none;
    }
    .correction-status-view,
    .correction-history-view {
        display: none;
        gap: 14px;
        padding: 20px;
    }
    .correction-status-view.is-active,
    .correction-history-view.is-active {
        display: grid;
    }
    .correction-info-list {
        display: grid;
        gap: 10px;
    }
    .correction-info-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        min-height: 48px;
        padding: 12px 14px;
        border: 1px solid #f3c7c7;
        border-radius: 12px;
        background: #fffafa;
        color: #111827;
        font-size: 13px;
        font-weight: 900;
    }
    .correction-info-row span:first-child {
        color: #64748b;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .04em;
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
    .return-pending-card {
        width: min(620px, 100%);
    }
    .return-pending-summary {
        display: grid;
        grid-template-columns: 46px minmax(0, 1fr);
        gap: 12px;
        align-items: flex-start;
        border: 1px solid #fed7aa;
        border-radius: 14px;
        background: #fffbeb;
        color: #7c2d12;
        padding: 14px;
    }
    .return-pending-icon {
        width: 46px;
        height: 46px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fef3c7;
        color: #9a3412;
    }
    .return-pending-icon svg {
        width: 22px;
        height: 22px;
    }
    .return-pending-summary strong {
        display: block;
        margin-bottom: 4px;
        color: #70131B;
        font-size: 15px;
        font-weight: 950;
    }
    .return-pending-summary p {
        margin: 0;
        color: #7c2d12;
        font-size: 13px;
        font-weight: 800;
        line-height: 1.5;
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
    .health-form-history-table-wrap { overflow-x: auto; border: 1px solid rgba(112, 19, 27, .12); border-radius: 12px; background: #fff; }
    .health-form-history-table { width: 100%; border-collapse: collapse; min-width: 640px; }
    .health-form-history-table th { background: #f8eeee; color: #70131B; padding: 12px; text-align: left; font-size: 12px; font-weight: 900; text-transform: uppercase; letter-spacing: .04em; }
    .health-form-history-table td { padding: 12px; border-top: 1px solid #f1dada; color: #111827; font-size: 13px; font-weight: 800; vertical-align: middle; }
    .health-form-history-actions { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
    .health-form-history-actions form { margin: 0; }
    .health-form-history-pill,
    .health-form-history-btn { border-radius: 999px; padding: 7px 11px; font-size: 11px; font-weight: 900; text-decoration: none; border: 1px solid #fecaca; background: #fff7ed; color: #70131B; cursor: pointer; }
    .health-form-history-btn.is-primary { background: #70131B; color: #fff; border-color: #70131B; }
    #healthFormHistoryModal .correction-card {
        max-width: min(940px, calc(100vw - 28px));
    }
    #healthFormHistoryModal .correction-body {
        gap: 14px;
    }
    #healthFormHistoryModal .health-form-history-table-wrap {
        max-height: min(58vh, 520px);
        overflow: auto;
    }
    .health-form-history-footer {
        display: flex;
        justify-content: flex-end;
        padding-top: 2px;
    }
    .pullout-static-note {
        border: 1px solid #facc15;
        border-radius: 14px;
        background: #fff8db;
        color: #70131B;
        padding: 16px;
        font-size: 14px;
        font-weight: 900;
        line-height: 1.5;
    }

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
    .profile-meta-k { font-size: 10px; color: #111827; text-transform: uppercase; font-weight: 900; letter-spacing: .05em; margin-bottom: 4px; }
    #summaryPanel .profile-meta-k,
    #healthPanel .profile-meta-k { margin-bottom: 0; }
    .profile-meta-v { font-size: 14px; color: #0f172a; font-weight: 900; word-break: break-word; line-height: 1.25; }
    .profile-meta.is-wide { grid-column: span 2; }
    .profile-meta.is-full { grid-column: 1 / -1; }
    #healthPanel .profile-meta.is-wide,
    #healthPanel .profile-meta.is-full { grid-column: auto; }

    .profile-timeline-card {
        padding: 18px;
        margin-top: 12px;
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
    .timeline-node > span {
        display: grid;
        place-items: center;
        width: 100%;
        height: 100%;
        font-size: 18px;
        font-weight: 900;
        line-height: 1;
    }
    .profile-timeline-step.is-unsynced .timeline-node {
        background: #dc2626;
        border-color: #fee2e2;
    }
    .profile-timeline-step.is-unsynced strong {
        color: #991b1b;
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
    [data-theme="dark"] .profile-quick-item strong,
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
    [data-theme="dark"] .profile-actions-toggle,
    [data-theme="dark"] .profile-actions-menu {
        background: #111827;
        border-color: #475569;
        color: #f8fafc;
    }
    [data-theme="dark"] .profile-actions-menu button {
        background: #1f2937;
        border-color: #475569;
        color: #f8fafc;
    }
    [data-theme="dark"] .health-form-history-table-wrap {
        background: #0f172a;
        border-color: #334155;
    }
    [data-theme="dark"] .health-form-history-table th {
        background: #1f2937;
        color: #facc15;
    }
    [data-theme="dark"] .health-form-history-table td {
        border-color: #334155;
        color: #f8fafc;
    }
    [data-theme="dark"] .pullout-static-note {
        background: rgba(250, 204, 21, .14);
        border-color: rgba(250, 204, 21, .45);
        color: #f8fafc;
    }
    [data-theme="dark"] .profile-actions-toggle:hover,
    [data-theme="dark"] .profile-actions-toggle:focus,
    [data-theme="dark"] .profile-actions-menu button:hover,
    [data-theme="dark"] .profile-actions-menu button:focus {
        background: #facc15;
        border-color: #facc15;
        color: #70131B;
    }
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
    [data-theme="dark"] .correction-field select {
        color-scheme: dark;
    }
    [data-theme="dark"] .correction-field select option {
        background: #111827;
        color: #f8fafc;
    }
    [data-theme="dark"] .correction-field select option:checked {
        background: #70131B;
        color: #ffffff;
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

    /* Standard modal chrome: new form, correction, history, and pullout */
    #newHealthFormModal .correction-card,
    #correctionModal .correction-card,
    #healthFormHistoryModal .correction-card,
    #pulloutRequestModal .correction-card,
    [data-theme="dark"] #newHealthFormModal .correction-card,
    [data-theme="dark"] #correctionModal .correction-card,
    [data-theme="dark"] #healthFormHistoryModal .correction-card,
    [data-theme="dark"] #pulloutRequestModal .correction-card {
        border: 1px solid rgba(250, 204, 21, .34) !important;
    }

    #newHealthFormModal .correction-head,
    #correctionModal .correction-head,
    #healthFormHistoryModal .correction-head,
    #pulloutRequestModal .correction-head {
        background: linear-gradient(135deg, #70131B, #8f2230) !important;
    }

    #newHealthFormModal .correction-head-icon,
    #correctionModal .correction-head-icon,
    #healthFormHistoryModal .correction-head-icon,
    #pulloutRequestModal .correction-head-icon,
    #newHealthFormModal .correction-head-icon svg,
    #correctionModal .correction-head-icon svg,
    #healthFormHistoryModal .correction-head-icon svg,
    #pulloutRequestModal .correction-head-icon svg {
        color: #ffffff !important;
        stroke: currentColor !important;
    }

    #newHealthFormModal .correction-close,
    #correctionModal .correction-close,
    #healthFormHistoryModal .correction-close,
    #pulloutRequestModal .correction-close {
        position: relative !important;
        overflow: hidden !important;
        width: 40px !important;
        min-width: 40px !important;
        height: 40px !important;
        min-height: 40px !important;
        flex: 0 0 40px !important;
        border: 1px solid #8f2230 !important;
        border-radius: 999px !important;
        background: linear-gradient(135deg, #70131B, #8f2230) !important;
        color: #ffffff !important;
        box-shadow: 0 8px 18px rgba(77, 13, 23, .24) !important;
        transition: transform .18s ease, background .18s ease, color .18s ease, border-color .18s ease, box-shadow .18s ease !important;
    }

    #newHealthFormModal .correction-close::after,
    #correctionModal .correction-close::after,
    #healthFormHistoryModal .correction-close::after,
    #pulloutRequestModal .correction-close::after {
        content: "" !important;
        position: absolute !important;
        top: -35% !important;
        left: -72% !important;
        width: 48% !important;
        height: 170% !important;
        border-radius: 999px !important;
        background: linear-gradient(120deg, transparent 0%, rgba(255, 244, 180, .18) 34%, rgba(255, 244, 180, .58) 50%, rgba(255, 244, 180, .18) 66%, transparent 100%) !important;
        transform: skewX(-18deg) !important;
        transition: left .48s ease !important;
        pointer-events: none !important;
        z-index: 0 !important;
    }

    #newHealthFormModal .correction-close svg,
    #correctionModal .correction-close svg,
    #healthFormHistoryModal .correction-close svg,
    #pulloutRequestModal .correction-close svg {
        position: relative !important;
        z-index: 1 !important;
        width: 18px !important;
        height: 18px !important;
        color: currentColor !important;
        stroke: currentColor !important;
    }

    #newHealthFormModal .correction-close:hover,
    #newHealthFormModal .correction-close:focus-visible,
    #correctionModal .correction-close:hover,
    #correctionModal .correction-close:focus-visible,
    #healthFormHistoryModal .correction-close:hover,
    #healthFormHistoryModal .correction-close:focus-visible,
    #pulloutRequestModal .correction-close:hover,
    #pulloutRequestModal .correction-close:focus-visible {
        border-color: #facc15 !important;
        background: #facc15 !important;
        color: #70131B !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 10px 22px rgba(250, 204, 21, .22) !important;
        outline: none !important;
    }

    #newHealthFormModal .correction-close:hover::after,
    #newHealthFormModal .correction-close:focus-visible::after,
    #correctionModal .correction-close:hover::after,
    #correctionModal .correction-close:focus-visible::after,
    #healthFormHistoryModal .correction-close:hover::after,
    #healthFormHistoryModal .correction-close:focus-visible::after,
    #pulloutRequestModal .correction-close:hover::after,
    #pulloutRequestModal .correction-close:focus-visible::after {
        left: 128% !important;
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
        && !in_array($puptasSyncRaw, ['synced', 'not_applicable'], true)
        && (optional(auth()->user())->canAccessPermission('health_records.update_assessment') ?? false);
    $canRequestFileCorrection = in_array($profileStatusNormalized, ['Issued', 'Fully Cleared'], true)
        && (optional(auth()->user())->canAccessPermission('health_records.request_resubmission') ?? false);
    $canReturnToPending = in_array($profileStatusNormalized, ['Issued', 'Fully Cleared'], true)
        && (optional(auth()->user())->canAccessPermission('health_records.request_resubmission') ?? false);
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
                @if($canReturnToPending)
                    <button type="button" class="profile-top-btn profile-top-btn-warning" id="openReturnToPendingModal">
                        <span aria-hidden="true">&crarr;</span>
                        Return to Pending
                    </button>
                @endif
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
                            <x-outline-icon name="exclamation-circle" />
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
            <div class="profile-actions-menu-wrap">
                <button type="button" class="profile-actions-toggle" id="profileActionsToggle" aria-label="Open health profile actions" aria-expanded="false" aria-controls="profileActionsMenu">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z" />
                    </svg>
                </button>
                <div class="profile-actions-menu" id="profileActionsMenu">
                    <button type="button" id="openNewHealthFormModal">
                        Request New Health Form
                        <span aria-hidden="true">+</span>
                    </button>
                    @if($canRequestFileCorrection)
                        <button type="button" id="openCorrectionModal">
                            Request File Correction
                            <span aria-hidden="true">&rarr;</span>
                        </button>
                    @endif
                    <button type="button" id="openHealthFormHistoryModal">
                        Health Form History
                        <span aria-hidden="true">&rarr;</span>
                    </button>
                    <button type="button" id="openPulloutRequestModal">
                        Request for Pullout
                        <span aria-hidden="true">&minus;</span>
                    </button>
                </div>
            </div>
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
                @if(!empty($pendingHealthFormRequest))
                    <div class="doc-missing" style="margin-bottom:10px;">
                        New form requested: {{ $pendingHealthFormRequest->category }}{{ $pendingHealthFormRequest->requested_at ? ' on ' . $pendingHealthFormRequest->requested_at->format('M d, Y h:i A') : '' }}
                    </div>
                @endif
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
            <div class="profile-timeline-step {{ $puptasSyncRaw === 'synced' ? '' : 'is-unsynced' }}">
                <span class="timeline-node">
                    @if($puptasSyncRaw === 'synced')
                        <x-outline-icon name="check" />
                    @else
                        <x-outline-icon name="exclamation-circle" />
                    @endif
                </span>
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

<div class="correction-modal" id="newHealthFormModal" aria-hidden="true">
    <div class="correction-card">
        <div class="correction-head">
            <div class="correction-head-title">
                <span class="correction-head-icon"><x-outline-icon name="document-text" /></span>
                <div>
                    <h3>Request New Health Form</h3>
                    <p>Ask this student to submit a fresh Health Information Form for a specific purpose.</p>
                </div>
            </div>
            <button type="button" class="correction-close" id="closeNewHealthFormModal" aria-label="Close new health form modal">
                <x-outline-icon name="x-mark" />
            </button>
        </div>
        <form method="POST" action="{{ route('admin.health_profile.request_health_form', $profile->id) }}" class="correction-body">
            @csrf
            <div class="correction-field">
                <label for="newHealthFormCategory">Category / Purpose</label>
                <div class="correction-select-wrap">
                    <select id="newHealthFormCategory" name="category" required>
                        <option value="">Select category</option>
                        @foreach(($healthFormCategories ?? collect()) as $category)
                            <option value="{{ $category }}">{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="correction-field">
                <label for="newHealthFormRemarks">Remarks</label>
                <textarea id="newHealthFormRemarks" name="remarks" placeholder="Optional note for why a new form is needed.">{{ old('remarks') }}</textarea>
            </div>
            <div class="correction-actions">
                <button type="button" class="correction-cancel" id="cancelNewHealthFormModal">Cancel</button>
                <button type="submit" class="correction-submit">Send Request</button>
            </div>
        </form>
    </div>
</div>

<div class="correction-modal" id="healthFormHistoryModal" aria-hidden="true">
    <div class="correction-card">
        <div class="correction-head">
            <div class="correction-head-title">
                <span class="correction-head-icon"><x-outline-icon name="document-text" /></span>
                <div>
                    <h3>Health Form History</h3>
                    <p>Saved PDF snapshots submitted by the student for each category or request.</p>
                </div>
            </div>
            <button type="button" class="correction-close" id="closeHealthFormHistoryModal" aria-label="Close health form history modal">
                <x-outline-icon name="x-mark" />
            </button>
        </div>
        <div class="correction-body">
            <div class="health-form-history-table-wrap">
                <table class="health-form-history-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>School Year</th>
                            <th>Status</th>
                            <th>Submitted At</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($healthFormSubmissions ?? collect()) as $submission)
                            <tr>
                                <td>{{ $submission->category }}</td>
                                <td>{{ $submission->school_year ?: '-' }}</td>
                                <td><span class="health-form-history-pill">{{ ucwords(str_replace('_', ' ', $submission->status)) }}</span></td>
                                <td>{{ $submission->submitted_at ? $submission->submitted_at->format('M d, Y h:i A') : ($submission->requested_at ? 'Requested ' . $submission->requested_at->format('M d, Y h:i A') : '-') }}</td>
                                <td>{{ $submission->remarks ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">No saved Health Form PDFs yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="correction-modal" id="pulloutRequestModal" aria-hidden="true">
    <div class="correction-card">
        <div class="correction-head">
            <div class="correction-head-title">
                <span class="correction-head-icon"><x-outline-icon name="document-text" /></span>
                <div>
                    <h3>Request for Pullout</h3>
                    <p>Health form pullout request status.</p>
                </div>
            </div>
            <button type="button" class="correction-close" id="closePulloutRequestModal" aria-label="Close request pullout modal">
                <x-outline-icon name="x-mark" />
            </button>
        </div>
        <div class="correction-body">
            <div class="pullout-static-note">
                Currently coordination with PUPTAS.
            </div>
            <div class="correction-actions">
                <button type="button" class="correction-submit" id="closePulloutRequestDone">Done</button>
            </div>
        </div>
    </div>
</div>

@if($canReturnToPending)
    <div class="correction-modal" id="returnToPendingModal" aria-hidden="true">
        <div class="correction-card return-pending-card" role="dialog" aria-modal="true" aria-labelledby="returnPendingTitle">
            <div class="correction-head">
                <div class="correction-head-title">
                    <span class="correction-head-icon"><x-outline-icon name="clock" /></span>
                    <div>
                        <h3 id="returnPendingTitle">Return to Pending Approval</h3>
                        <p>Move this approved health record back to clinic review.</p>
                    </div>
                </div>
                <button type="button" class="correction-close" id="closeReturnToPendingModal" aria-label="Close return to pending confirmation">
                    <x-outline-icon name="x-mark" />
                </button>
            </div>
            <form method="POST" action="{{ route('admin.health_profile.return_to_pending', $profile->id) }}" class="correction-body">
                @csrf
                <div class="return-pending-summary">
                    <span class="return-pending-icon"><x-outline-icon name="exclamation-triangle" /></span>
                    <div>
                        <strong>{{ $profileName }}</strong>
                        <p>This will remove the approval status and place the record under Pending Approval. Submitted profile details and uploaded documents will remain saved.</p>
                    </div>
                </div>
                <div class="correction-info-list">
                    <div class="correction-info-row">
                        <span>Current Status</span>
                        <strong>{{ $profileStatusLabel }}</strong>
                    </div>
                    <div class="correction-info-row">
                        <span>Next Status</span>
                        <strong>For Verification</strong>
                    </div>
                    <div class="correction-info-row">
                        <span>PUPTAS Sync</span>
                        <strong>Will be cleared</strong>
                    </div>
                </div>
                <div class="correction-actions">
                    <button type="button" class="correction-cancel" id="cancelReturnToPendingModal">Cancel</button>
                    <button type="submit" class="correction-submit">Confirm Return</button>
                </div>
            </form>
        </div>
    </div>
@endif

@if($canRequestFileCorrection)
    <div class="correction-modal" id="correctionModal" aria-hidden="true">
        <div class="correction-card">
            <div class="correction-head">
                <button type="button" class="correction-modal-back" id="correctionModalBack" aria-label="Back to correction request">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                </button>
                <div class="correction-head-title">
                    <span class="correction-head-icon"><x-outline-icon name="document-text" /></span>
                    <div>
                        <h3>Request File Correction</h3>
                        <p>Select file/s for replacement or Health Form Correction so the student can update their health information.</p>
                    </div>
                </div>
                <div class="correction-head-actions">
                    <div class="correction-actions-menu-wrap">
                        <button type="button" class="correction-actions-toggle" id="correctionActionsToggle" aria-label="Open correction actions" aria-expanded="false" aria-controls="correctionActionsMenu">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z" />
                            </svg>
                        </button>
                        <div class="correction-actions-menu" id="correctionActionsMenu">
                            <button type="button" data-correction-view-trigger="status">Status</button>
                            <button type="button" data-correction-view-trigger="history">History</button>
                        </div>
                    </div>
                    <button type="button" class="correction-close" id="closeCorrectionModal" aria-label="Close correction modal">
                        <x-outline-icon name="x-mark" />
                    </button>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.health_profile.request_resubmission', $profile->id) }}" class="correction-body" id="correctionRequestView">
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
            <div class="correction-status-view" id="correctionStatusView">
                <div class="correction-info-list">
                    <div class="correction-info-row">
                        <span>Current Status</span>
                        <strong>{{ $correctionStatusLabel }}</strong>
                    </div>
                    <div class="correction-info-row">
                        <span>Last Request</span>
                        <strong>{{ $profile->resubmission_requested_at ? $profile->resubmission_requested_at->format('M d, Y h:i A') : 'None' }}</strong>
                    </div>
                    <div class="correction-info-row">
                        <span>Submitted At</span>
                        <strong>{{ $profile->resubmitted_at ? $profile->resubmitted_at->format('M d, Y h:i A') : 'None' }}</strong>
                    </div>
                    <div class="correction-info-row">
                        <span>Reason</span>
                        <strong>{{ $profile->pending_reason ?: 'None' }}</strong>
                    </div>
                </div>
            </div>
            <div class="correction-history-view" id="correctionHistoryView">
                <div class="correction-note">
                    {!! nl2br(e($correctionHistoryText !== '' ? $correctionHistoryText : 'No correction history yet.')) !!}
                </div>
            </div>
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
    const correctionForm = correctionModal?.querySelector('.correction-body');
    const correctionRequestView = document.getElementById('correctionRequestView');
    const correctionStatusView = document.getElementById('correctionStatusView');
    const correctionHistoryView = document.getElementById('correctionHistoryView');
    const correctionModalBack = document.getElementById('correctionModalBack');
    const correctionActionsToggle = document.getElementById('correctionActionsToggle');
    const correctionActionsMenu = document.getElementById('correctionActionsMenu');
    const newHealthFormModal = document.getElementById('newHealthFormModal');
    const openNewHealthFormModal = document.getElementById('openNewHealthFormModal');
    const closeNewHealthFormModal = document.getElementById('closeNewHealthFormModal');
    const cancelNewHealthFormModal = document.getElementById('cancelNewHealthFormModal');
    const profileActionsToggle = document.getElementById('profileActionsToggle');
    const profileActionsMenu = document.getElementById('profileActionsMenu');
    const healthFormHistoryModal = document.getElementById('healthFormHistoryModal');
    const openHealthFormHistoryModal = document.getElementById('openHealthFormHistoryModal');
    const closeHealthFormHistoryModal = document.getElementById('closeHealthFormHistoryModal');
    const pulloutRequestModal = document.getElementById('pulloutRequestModal');
    const openPulloutRequestModal = document.getElementById('openPulloutRequestModal');
    const closePulloutRequestModal = document.getElementById('closePulloutRequestModal');
    const closePulloutRequestDone = document.getElementById('closePulloutRequestDone');
    const returnToPendingModal = document.getElementById('returnToPendingModal');
    const openReturnToPendingModal = document.getElementById('openReturnToPendingModal');
    const closeReturnToPendingModal = document.getElementById('closeReturnToPendingModal');
    const cancelReturnToPendingModal = document.getElementById('cancelReturnToPendingModal');

    function setProfileActionsMenu(open) {
        if (!profileActionsMenu || !profileActionsToggle) return;
        profileActionsMenu.classList.toggle('is-open', open);
        profileActionsToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    profileActionsToggle?.addEventListener('click', function (event) {
        event.stopPropagation();
        setProfileActionsMenu(!profileActionsMenu?.classList.contains('is-open'));
    });

    profileActionsMenu?.addEventListener('click', function (event) {
        event.stopPropagation();
    });

    document.addEventListener('click', function () {
        setProfileActionsMenu(false);
        setCorrectionActionsMenu(false);
    });

    function setNewHealthFormModal(open) {
        if (!newHealthFormModal) return;
        newHealthFormModal.classList.toggle('is-open', open);
        newHealthFormModal.setAttribute('aria-hidden', open ? 'false' : 'true');
    }

    openNewHealthFormModal?.addEventListener('click', function () {
        setProfileActionsMenu(false);
        setNewHealthFormModal(true);
    });

    closeNewHealthFormModal?.addEventListener('click', function () {
        setNewHealthFormModal(false);
    });

    cancelNewHealthFormModal?.addEventListener('click', function () {
        setNewHealthFormModal(false);
    });

    newHealthFormModal?.addEventListener('click', function (event) {
        if (event.target === newHealthFormModal) {
            setNewHealthFormModal(false);
        }
    });

    function setHealthFormHistoryModal(open) {
        if (!healthFormHistoryModal) return;
        healthFormHistoryModal.classList.toggle('is-open', open);
        healthFormHistoryModal.setAttribute('aria-hidden', open ? 'false' : 'true');
    }

    openHealthFormHistoryModal?.addEventListener('click', function () {
        setProfileActionsMenu(false);
        setHealthFormHistoryModal(true);
    });

    closeHealthFormHistoryModal?.addEventListener('click', function () {
        setHealthFormHistoryModal(false);
    });

    healthFormHistoryModal?.addEventListener('click', function (event) {
        if (event.target === healthFormHistoryModal) {
            setHealthFormHistoryModal(false);
        }
    });

    function setPulloutRequestModal(open) {
        if (!pulloutRequestModal) return;
        pulloutRequestModal.classList.toggle('is-open', open);
        pulloutRequestModal.setAttribute('aria-hidden', open ? 'false' : 'true');
    }

    openPulloutRequestModal?.addEventListener('click', function () {
        setPulloutRequestModal(true);
    });

    closePulloutRequestModal?.addEventListener('click', function () {
        setPulloutRequestModal(false);
    });

    closePulloutRequestDone?.addEventListener('click', function () {
        setPulloutRequestModal(false);
    });

    pulloutRequestModal?.addEventListener('click', function (event) {
        if (event.target === pulloutRequestModal) {
            setPulloutRequestModal(false);
        }
    });

    function setReturnToPendingModal(open) {
        if (!returnToPendingModal) return;
        returnToPendingModal.classList.toggle('is-open', open);
        returnToPendingModal.setAttribute('aria-hidden', open ? 'false' : 'true');
    }

    openReturnToPendingModal?.addEventListener('click', function () {
        setReturnToPendingModal(true);
    });

    closeReturnToPendingModal?.addEventListener('click', function () {
        setReturnToPendingModal(false);
    });

    cancelReturnToPendingModal?.addEventListener('click', function () {
        setReturnToPendingModal(false);
    });

    returnToPendingModal?.addEventListener('click', function (event) {
        if (event.target === returnToPendingModal) {
            setReturnToPendingModal(false);
        }
    });

    function setCorrectionActionsMenu(open) {
        if (!correctionActionsMenu || !correctionActionsToggle) return;
        correctionActionsMenu.classList.toggle('is-open', open);
        correctionActionsToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    function setCorrectionView(view) {
        const isRequest = view === 'request';
        correctionRequestView?.classList.toggle('is-hidden', !isRequest);
        correctionStatusView?.classList.toggle('is-active', view === 'status');
        correctionHistoryView?.classList.toggle('is-active', view === 'history');
        correctionModalBack?.classList.toggle('is-visible', !isRequest);
        setCorrectionActionsMenu(false);
    }

    correctionActionsToggle?.addEventListener('click', function (event) {
        event.stopPropagation();
        setCorrectionActionsMenu(!correctionActionsMenu?.classList.contains('is-open'));
    });

    correctionActionsMenu?.addEventListener('click', function (event) {
        event.stopPropagation();
    });

    document.querySelectorAll('[data-correction-view-trigger]').forEach(function (button) {
        button.addEventListener('click', function () {
            setCorrectionView(button.dataset.correctionViewTrigger || 'request');
        });
    });

    correctionModalBack?.addEventListener('click', function () {
        setCorrectionView('request');
    });

    function syncCorrectionReason() {
        if (!correctionReason || !correctionReasonSelect) return '';

        const selectedReason = correctionReasonSelect.value.trim();
        const otherReason = correctionReasonOther ? correctionReasonOther.value.trim() : '';
        const finalReason = selectedReason === 'Others' ? otherReason : selectedReason;

        if (selectedReason === 'Health Form Correction' && correctionHealthFormOption) {
            correctionHealthFormOption.checked = true;
        }

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
        if (open) {
            setCorrectionView('request');
        } else {
            setCorrectionActionsMenu(false);
        }
    }

    openCorrectionModal?.addEventListener('click', function () {
        setProfileActionsMenu(false);
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
        const hasHealthFormCorrection = Boolean(correctionHealthFormOption?.checked)
            || finalReason.toLowerCase().includes('health form correction');
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
