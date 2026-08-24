@extends('layouts.admin')

@section('title', 'Student Health Profile')

@push('styles')
<style>
    .health-profile-wrap {
        width: 100%;
        max-width: 1120px;
        margin: 0 auto;
        display: grid;
        gap: 16px;
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
    .profile-content-card {
        --profile-version-sticky-top: 78px;
        margin-top: 8px;
        padding: 0;
        overflow: visible;
    }
    .profile-switch-sticky {
        position: sticky;
        top: -10px;
        z-index: 60;
        padding: 16px 18px;
        border-bottom: 1px solid #e2e8f0;
        border-radius: 14px 14px 0 0;
        background: #ffffff;
        box-shadow: 0 10px 22px rgba(15, 23, 42, .08);
        backdrop-filter: blur(12px);
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
        overflow: visible;
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
        border-radius: 17px 17px 0 0;
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
    .correction-custom-select-wrap {
        position: relative;
        z-index: 35;
    }
    .correction-custom-select-wrap::after {
        display: none;
    }
    .correction-custom-source {
        position: absolute !important;
        inset: auto auto 0 0;
        width: 1px !important;
        min-height: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        border: 0 !important;
        opacity: 0;
        pointer-events: none;
    }
    body .main button.correction-custom-trigger {
        position: relative;
        width: 100%;
        min-height: 48px;
        padding: 0 46px 0 12px;
        border: 1px solid #f3c7c7 !important;
        border-radius: 12px;
        background: #ffffff !important;
        color: #70131B !important;
        -webkit-text-fill-color: #70131B !important;
        font-size: 14px;
        font-weight: 800;
        line-height: 1.35;
        text-align: left;
        cursor: pointer;
        box-shadow: none;
        transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
    }
    body .main button.correction-custom-trigger::after {
        content: "";
        position: absolute;
        right: 17px;
        top: 50%;
        width: 9px;
        height: 9px;
        border-right: 2px solid currentColor;
        border-bottom: 2px solid currentColor;
        transform: translateY(-68%) rotate(45deg);
        transition: transform .18s ease;
    }
    body .main .correction-custom-select-wrap.is-open .correction-custom-trigger {
        border-color: #8f2230 !important;
        box-shadow: 0 0 0 3px rgba(112, 19, 27, 0.12);
    }
    body .main .correction-custom-select-wrap.is-open .correction-custom-trigger::after {
        transform: translateY(-30%) rotate(225deg);
    }
    .correction-custom-menu {
        position: absolute;
        z-index: 90;
        top: calc(100% + 7px);
        left: 0;
        right: 0;
        display: none;
        max-height: 250px;
        overflow-y: auto;
        padding: 8px;
        border: 1px solid #f0caca;
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 18px 36px rgba(58, 12, 18, 0.2);
        scrollbar-width: thin;
        scrollbar-color: #8f2230 transparent;
    }
    .correction-custom-menu::-webkit-scrollbar {
        width: 6px;
    }
    .correction-custom-menu::-webkit-scrollbar-track {
        background: transparent;
    }
    .correction-custom-menu::-webkit-scrollbar-thumb {
        border-radius: 999px;
        background: #8f2230;
    }
    .correction-custom-select-wrap.is-open .correction-custom-menu {
        display: grid;
        gap: 7px;
        animation: correctionDropdownIn .18s ease both;
    }
    .correction-custom-select-wrap.is-dropup .correction-custom-menu {
        top: auto;
        bottom: calc(100% + 7px);
        transform-origin: bottom center;
    }
    body .main button.correction-custom-option {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        width: 100%;
        min-height: 40px;
        padding: 9px 12px;
        border: 1px solid #efcaca !important;
        border-radius: 8px;
        background: #ffffff !important;
        color: #70131B !important;
        -webkit-text-fill-color: #70131B !important;
        font-size: 13px;
        font-weight: 800;
        line-height: 1.3;
        text-align: left;
        cursor: pointer;
        box-shadow: none;
        transition: border-color .2s ease, color .2s ease;
    }
    body .main button.correction-custom-option::after {
        content: "";
        position: absolute;
        z-index: 0;
        top: -45%;
        left: -130%;
        width: 120%;
        height: 190%;
        background: linear-gradient(115deg, rgba(255,247,181,0) 0%, rgba(255,247,181,.78) 46%, rgba(255,247,181,0) 100%);
        transform: skewX(-20deg);
        transition: left .9s ease;
    }
    body .main button.correction-custom-option > span {
        position: relative;
        z-index: 1;
    }
    body .main button.correction-custom-option:hover,
    body .main button.correction-custom-option:focus-visible {
        border-color: #facc15 !important;
        background: #facc15 !important;
        color: #70131B !important;
        -webkit-text-fill-color: #70131B !important;
        outline: none;
    }
    body .main button.correction-custom-option:hover::after,
    body .main button.correction-custom-option:focus-visible::after {
        left: 125%;
    }
    body .main button.correction-custom-option.is-selected {
        border-color: #70131B !important;
        background: #70131B !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }
    body .main button.correction-custom-option.is-selected:hover,
    body .main button.correction-custom-option.is-selected:focus-visible {
        border-color: #facc15 !important;
        background: #facc15 !important;
        color: #70131B !important;
        -webkit-text-fill-color: #70131B !important;
    }
    @keyframes correctionDropdownIn {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
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
    .profile-panel {
        display: none;
        margin: 0;
        padding: 18px;
        border: 0;
        border-radius: 0 0 14px 14px;
        background: transparent;
        box-shadow: none;
    }
    .profile-panel.is-active { display: block; }
    .profile-version-shell {
        display: grid;
        grid-template-columns: minmax(220px, 260px) minmax(0, 1fr);
        gap: 18px;
        align-items: start;
    }
    .profile-version-sidebar {
        position: sticky;
        top: var(--profile-version-sticky-top);
        z-index: 35;
        display: flex;
        flex-direction: column;
        min-width: 0;
        max-height: calc(100vh - var(--profile-version-sticky-top) - 22px);
        overflow: hidden;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #f8fafc;
        padding: 14px;
    }
    .profile-version-sidebar-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 12px;
        padding-bottom: 12px;
        border-bottom: 1px solid #e2e8f0;
    }
    .profile-version-sidebar-head h3,
    .profile-version-pane-head h3 {
        margin: 0;
        color: #0f172a;
        font-size: 16px;
        font-weight: 900;
    }
    .profile-version-sidebar-head p,
    .profile-version-pane-head p {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.45;
    }
    .profile-version-nav {
        display: grid;
        gap: 8px;
        min-height: 0;
        overflow-y: auto;
        padding-right: 4px;
        scrollbar-width: thin;
        scrollbar-color: #8f2230 transparent;
    }
    .profile-version-nav::-webkit-scrollbar {
        width: 6px;
    }
    .profile-version-nav::-webkit-scrollbar-track {
        background: transparent;
    }
    .profile-version-nav::-webkit-scrollbar-thumb {
        border-radius: 999px;
        background: #8f2230;
    }
    .profile-version-choice {
        width: 100%;
        min-width: 0;
        display: grid;
        grid-template-columns: 42px minmax(0, 1fr);
        align-items: center;
        gap: 10px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #ffffff;
        color: #111827;
        padding: 10px;
        text-align: left;
        font: inherit;
        cursor: pointer;
        box-shadow: 0 7px 16px rgba(15, 23, 42, .05);
        transition: transform .18s ease, border-color .18s ease, background .18s ease, box-shadow .18s ease;
    }
    .profile-version-choice:hover,
    .profile-version-choice:focus-visible {
        transform: translateY(-1px);
        border-color: #facc15;
        background: #fffbea;
        box-shadow: 0 10px 20px rgba(112, 19, 27, .10);
        outline: none;
    }
    .profile-version-choice.is-active {
        border-color: #70131B;
        background: #70131B;
        color: #ffffff;
        box-shadow: 0 12px 24px rgba(112, 19, 27, .20);
    }
    .profile-version-choice-number {
        width: 42px;
        height: 42px;
        display: grid;
        place-items: center;
        border-radius: 8px;
        background: #fff1f2;
        color: #70131B;
        font-size: 11px;
        font-weight: 900;
    }
    .profile-version-choice.is-active .profile-version-choice-number {
        background: rgba(255, 255, 255, .14);
        color: #facc15;
    }
    .profile-version-choice-copy {
        min-width: 0;
    }
    .profile-version-choice-copy strong,
    .profile-version-choice-copy small {
        display: block;
    }
    .profile-version-choice-copy strong {
        overflow: hidden;
        color: inherit;
        font-size: 12px;
        font-weight: 900;
        line-height: 1.3;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .profile-version-choice-copy small {
        margin-top: 3px;
        overflow: hidden;
        color: #64748b;
        font-size: 10px;
        font-weight: 700;
        line-height: 1.35;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .profile-version-choice.is-active .profile-version-choice-copy small {
        color: rgba(255, 255, 255, .78);
    }
    .profile-version-choice.is-active .profile-version-choice-copy strong {
        color: #ffffff;
    }
    .profile-version-content {
        min-width: 0;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #f8fafc;
        padding: 14px;
        box-shadow: 0 8px 20px rgba(15, 23, 42, .04);
    }
    .profile-version-pane {
        display: none;
        min-width: 0;
    }
    .profile-version-pane.is-active {
        display: block;
        animation: profile-version-reveal .24s ease both;
    }
    .profile-version-pane-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 14px;
        padding-bottom: 13px;
        border-bottom: 1px solid #e2e8f0;
    }
    .profile-version-pane-badges {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
        flex-wrap: wrap;
    }
    @keyframes profile-version-reveal {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .profile-history-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 16px;
    }
    .profile-history-heading h3,
    .profile-history-section-head h4 {
        margin: 0;
        color: #0f172a;
        font-size: 16px;
        font-weight: 900;
    }
    .profile-history-heading p,
    .profile-history-section-head p {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
    }
    .profile-history-count {
        flex: 0 0 auto;
        border: 1px solid #fecaca;
        border-radius: 8px;
        background: #fff7f7;
        color: #70131B;
        padding: 7px 11px;
        font-size: 11px;
        font-weight: 900;
    }
    .profile-history-list {
        display: grid;
        gap: 12px;
    }
    .profile-history-item {
        overflow: hidden;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #ffffff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, .06);
    }
    .profile-history-item.is-current {
        border-color: rgba(112, 19, 27, .45);
        box-shadow: 0 10px 24px rgba(112, 19, 27, .10);
    }
    .profile-history-toggle {
        width: 100%;
        min-height: 78px;
        display: grid;
        grid-template-columns: 48px minmax(170px, 1fr) minmax(180px, .85fr) auto 34px;
        align-items: center;
        gap: 14px;
        border: 0;
        background: #ffffff;
        padding: 14px 16px;
        color: #111827;
        text-align: left;
        font: inherit;
        cursor: pointer;
        transition: background .18s ease, box-shadow .18s ease;
    }
    .profile-history-toggle:hover,
    .profile-history-toggle:focus-visible {
        background: #fffbea;
        outline: none;
        box-shadow: inset 4px 0 0 #facc15;
    }
    .profile-history-version {
        width: 42px;
        height: 42px;
        display: grid;
        place-items: center;
        border-radius: 8px;
        background: #70131B;
        color: #ffffff;
        font-size: 13px;
        font-weight: 900;
    }
    .profile-history-primary,
    .profile-history-date {
        min-width: 0;
    }
    .profile-history-primary strong,
    .profile-history-date strong {
        display: block;
        color: #111827;
        font-size: 13px;
        font-weight: 900;
        line-height: 1.3;
    }
    .profile-history-primary small,
    .profile-history-date small {
        display: block;
        margin-bottom: 3px;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
    }
    .profile-history-badges {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
        flex-wrap: wrap;
    }
    .profile-history-badge {
        display: inline-flex;
        align-items: center;
        min-height: 25px;
        border-radius: 999px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #475569;
        padding: 4px 9px;
        font-size: 10px;
        font-weight: 900;
        white-space: nowrap;
    }
    .profile-history-badge.is-current,
    .profile-history-badge.is-approved {
        border-color: #86efac;
        background: #dcfce7;
        color: #166534;
    }
    .profile-history-badge.is-requested,
    .profile-history-badge.is-needs-correction {
        border-color: #fde68a;
        background: #fef3c7;
        color: #92400e;
    }
    .profile-history-badge.is-submitted {
        border-color: #bfdbfe;
        background: #dbeafe;
        color: #1d4ed8;
    }
    .profile-history-chevron {
        width: 32px;
        height: 32px;
        display: grid;
        place-items: center;
        border: 1px solid #fecaca;
        border-radius: 8px;
        color: #70131B;
        transition: transform .2s ease, background .2s ease;
    }
    .profile-history-chevron svg {
        width: 16px;
        height: 16px;
    }
    .profile-history-toggle[aria-expanded="true"] .profile-history-chevron {
        transform: rotate(180deg);
        background: #facc15;
    }
    .profile-history-details {
        padding: 18px;
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
    }
    .profile-history-meta {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 18px;
    }
    .profile-history-meta > div {
        min-width: 0;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #ffffff;
        padding: 10px 12px;
    }
    .profile-history-meta span,
    .profile-history-meta strong {
        display: block;
    }
    .profile-history-meta span {
        margin-bottom: 4px;
        color: #64748b;
        font-size: 9px;
        font-weight: 900;
        text-transform: uppercase;
    }
    .profile-history-meta strong {
        color: #111827;
        font-size: 12px;
        font-weight: 900;
        overflow-wrap: anywhere;
    }
    .profile-history-section-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin: 18px 0 12px;
    }
    .profile-history-notice,
    .profile-history-empty-detail,
    .profile-history-no-documents {
        border: 1px solid #fde68a;
        border-radius: 8px;
        background: #fffbeb;
        color: #92400e;
        padding: 12px 14px;
        font-size: 12px;
        font-weight: 800;
        line-height: 1.5;
    }
    .profile-history-documents {
        margin-top: 18px;
        padding-top: 2px;
        border-top: 1px solid #e2e8f0;
    }
    .profile-history-document-links {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .profile-history-document-links a {
        position: relative;
        overflow: hidden;
        isolation: isolate;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 38px;
        border: 1px solid #8f2230;
        border-radius: 8px;
        background: #70131B;
        color: #ffffff;
        padding: 8px 12px;
        text-decoration: none;
        font-size: 11px;
        font-weight: 900;
        box-shadow: 0 8px 16px rgba(112, 19, 27, .12);
        transition: color .12s ease, transform .18s ease;
    }
    .profile-history-document-links a::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -1;
        background: #facc15;
        transform: translateX(-102%);
        transition: transform .24s ease;
    }
    .profile-history-document-links a:hover,
    .profile-history-document-links a:focus-visible {
        color: #70131B;
        transform: translateY(-1px);
        outline: none;
    }
    .profile-history-document-links a:hover::before,
    .profile-history-document-links a:focus-visible::before {
        transform: translateX(0);
    }
    .profile-history-document-links svg {
        width: 15px;
        height: 15px;
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
    .pullout-form {
        display: grid;
        gap: 14px;
    }
    .pullout-status-summary {
        display: grid;
        gap: 14px;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #f8fafc;
        padding: 16px;
    }
    .pullout-status-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        color: #334155;
        font-size: 13px;
    }
    .pullout-status-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin: 0;
    }
    .pullout-status-grid > div {
        min-width: 0;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #ffffff;
        padding: 11px 12px;
    }
    .pullout-status-grid dt {
        color: #64748b;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .pullout-status-grid dd {
        margin: 5px 0 0;
        color: #111827;
        font-size: 13px;
        font-weight: 800;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }
    .pullout-summary-note {
        margin: 0;
        border-top: 1px solid #e2e8f0;
        padding-top: 11px;
        color: #475569;
        font-size: 13px;
        line-height: 1.55;
    }
    .pullout-confirmation {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        border: 1px solid #f3c7c7;
        border-radius: 12px;
        background: #fff7f7;
        padding: 13px 14px;
        color: #334155;
        font-size: 13px;
        font-weight: 800;
        line-height: 1.45;
        cursor: pointer;
    }
    .pullout-confirmation input {
        width: 18px;
        height: 18px;
        flex: 0 0 auto;
        margin-top: 1px;
        accent-color: #70131B;
    }
    .pullout-error {
        border: 1px solid #fca5a5;
        border-radius: 12px;
        background: #fef2f2;
        color: #991b1b;
        padding: 12px 14px;
        font-size: 13px;
        font-weight: 800;
    }
    .profile-status-card.is-pulled-out {
        background: #fff1f2;
        border-color: #fda4af;
    }
    .profile-status-card.is-pullout-pending {
        background: #fff7ed;
        border-color: #fdba74;
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
    .profile-timeline-subtitle {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
    }
    #healthPanel .profile-timeline-card {
        border: 1px solid rgba(112, 19, 27, .12);
        border-radius: 12px;
        background: #f8fafc;
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
    [data-theme="dark"] .profile-switch-sticky {
        background: #0f172a;
        border-color: #334155;
        box-shadow: 0 12px 26px rgba(0, 0, 0, .28);
    }
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
    [data-theme="dark"] #healthPanel .profile-timeline-card,
    [data-theme="dark"] .profile-version-sidebar,
    [data-theme="dark"] .profile-version-choice,
    [data-theme="dark"] .profile-history-item,
    [data-theme="dark"] .profile-history-toggle,
    [data-theme="dark"] .profile-history-meta > div {
        background: #111827;
        border-color: #334155;
    }
    [data-theme="dark"] .profile-version-sidebar-head,
    [data-theme="dark"] .profile-version-pane-head {
        border-color: #334155;
    }
    [data-theme="dark"] .profile-version-content {
        background: #0b1220;
        border-color: #334155;
        box-shadow: 0 10px 24px rgba(0, 0, 0, .20);
    }
    [data-theme="dark"] .profile-version-nav {
        scrollbar-color: #facc15 transparent;
    }
    [data-theme="dark"] .profile-version-nav::-webkit-scrollbar-thumb {
        background: #facc15;
    }
    [data-theme="dark"] .profile-version-sidebar-head h3,
    [data-theme="dark"] .profile-version-pane-head h3,
    [data-theme="dark"] .profile-version-choice {
        color: #f8fafc;
    }
    [data-theme="dark"] .profile-version-sidebar-head p,
    [data-theme="dark"] .profile-version-pane-head p,
    [data-theme="dark"] .profile-version-choice-copy small {
        color: #cbd5e1;
    }
    [data-theme="dark"] .profile-version-choice-number {
        background: #1f2937;
        color: #facc15;
    }
    [data-theme="dark"] .profile-version-choice:hover,
    [data-theme="dark"] .profile-version-choice:focus-visible {
        background: #1f2937;
        border-color: #facc15;
    }
    [data-theme="dark"] .profile-version-choice.is-active {
        background: #70131B;
        border-color: #facc15;
        color: #ffffff;
    }
    [data-theme="dark"] .profile-history-details {
        background: #0b1220;
        border-color: #334155;
    }
    [data-theme="dark"] .profile-history-heading h3,
    [data-theme="dark"] .profile-history-section-head h4,
    [data-theme="dark"] .profile-history-primary strong,
    [data-theme="dark"] .profile-history-date strong,
    [data-theme="dark"] .profile-history-meta strong {
        color: #f8fafc;
    }
    [data-theme="dark"] .profile-history-heading p,
    [data-theme="dark"] .profile-history-section-head p,
    [data-theme="dark"] .profile-history-primary small,
    [data-theme="dark"] .profile-history-date small,
    [data-theme="dark"] .profile-history-meta span,
    [data-theme="dark"] .profile-timeline-subtitle {
        color: #cbd5e1;
    }
    [data-theme="dark"] .profile-history-count {
        background: #1f2937;
        border-color: #475569;
        color: #facc15;
    }
    [data-theme="dark"] .profile-history-toggle:hover,
    [data-theme="dark"] .profile-history-toggle:focus-visible {
        background: #1f2937;
    }
    [data-theme="dark"] .profile-history-notice,
    [data-theme="dark"] .profile-history-empty-detail,
    [data-theme="dark"] .profile-history-no-documents {
        background: rgba(146, 64, 14, .22);
        border-color: rgba(250, 204, 21, .38);
        color: #fde68a;
    }
    [data-theme="dark"] .profile-history-documents {
        border-color: #334155;
    }
    [data-theme="dark"] .pullout-static-note {
        background: rgba(250, 204, 21, .14);
        border-color: rgba(250, 204, 21, .45);
        color: #f8fafc;
    }
    [data-theme="dark"] .pullout-status-summary,
    [data-theme="dark"] .pullout-status-grid > div {
        background: #0f172a;
        border-color: #334155;
    }
    [data-theme="dark"] .pullout-status-heading,
    [data-theme="dark"] .pullout-status-grid dd,
    [data-theme="dark"] .pullout-confirmation {
        color: #f8fafc;
    }
    [data-theme="dark"] .pullout-status-grid dt,
    [data-theme="dark"] .pullout-summary-note {
        color: #cbd5e1;
    }
    [data-theme="dark"] .pullout-summary-note {
        border-color: #334155;
    }
    [data-theme="dark"] .pullout-confirmation {
        background: #111827;
        border-color: #475569;
    }
    [data-theme="dark"] .pullout-error {
        background: rgba(153, 27, 27, .24);
        border-color: rgba(248, 113, 113, .55);
        color: #fecaca;
    }
    [data-theme="dark"] .profile-status-card.is-pulled-out {
        background: rgba(127, 29, 29, .3);
        border-color: rgba(248, 113, 113, .45);
    }
    [data-theme="dark"] .profile-status-card.is-pullout-pending {
        background: rgba(124, 45, 18, .3);
        border-color: rgba(251, 146, 60, .45);
    }
    @media (max-width: 720px) {
        .pullout-status-grid { grid-template-columns: 1fr; }
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
    [data-theme="dark"] .correction-custom-menu {
        border-color: #475569;
        background: #111827;
        box-shadow: 0 18px 38px rgba(0, 0, 0, 0.48);
    }
    [data-theme="dark"] body .main button.correction-custom-trigger,
    [data-theme="dark"] body .main button.correction-custom-option {
        border-color: #475569 !important;
        background: #182334 !important;
        color: #f8fafc !important;
        -webkit-text-fill-color: #f8fafc !important;
    }
    [data-theme="dark"] body .main button.correction-custom-option {
        border-bottom-width: 1px !important;
    }
    [data-theme="dark"] body .main button.correction-custom-option.is-selected {
        border-color: #be3445 !important;
        background: #9f1d2d !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }
    [data-theme="dark"] body .main button.correction-custom-option:hover,
    [data-theme="dark"] body .main button.correction-custom-option:focus-visible,
    [data-theme="dark"] body .main button.correction-custom-option.is-selected:hover,
    [data-theme="dark"] body .main button.correction-custom-option.is-selected:focus-visible {
        border-color: #facc15 !important;
        background: #facc15 !important;
        color: #70131B !important;
        -webkit-text-fill-color: #70131B !important;
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
        .profile-version-shell {
            grid-template-columns: minmax(200px, 230px) minmax(0, 1fr);
        }
        .profile-history-toggle {
            grid-template-columns: 48px minmax(150px, 1fr) minmax(150px, .85fr) 34px;
        }
        .profile-history-badges {
            grid-column: 2 / 4;
            grid-row: 2;
            justify-content: flex-start;
        }
        .profile-history-chevron {
            grid-column: 4;
            grid-row: 1 / 3;
        }
        .profile-history-meta { grid-template-columns: repeat(2, minmax(0, 1fr)); }
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
        .profile-switch-sticky {
            padding: 12px;
        }
        .profile-switch {
            flex: 1 1 auto;
            flex-wrap: nowrap;
            overflow-x: auto;
            padding-bottom: 3px;
            scrollbar-width: thin;
        }
        .profile-tab {
            flex: 0 0 auto;
        }
        .profile-version-shell {
            grid-template-columns: 1fr;
        }
        .profile-version-sidebar {
            padding: 12px;
            max-height: none;
            overflow: visible;
        }
        .profile-version-nav {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            overflow-y: hidden;
            padding-bottom: 4px;
            padding-right: 0;
            scrollbar-width: thin;
        }
        .profile-version-choice {
            flex: 0 0 230px;
        }
        .profile-version-pane-head {
            align-items: flex-start;
        }
        .profile-meta.is-wide,
        .profile-meta.is-full { grid-column: auto; }
        .profile-history-heading {
            align-items: flex-start;
        }
        .profile-history-toggle {
            grid-template-columns: 44px minmax(0, 1fr) 34px;
            gap: 10px;
            padding: 12px;
        }
        .profile-history-version {
            width: 40px;
            height: 40px;
        }
        .profile-history-date,
        .profile-history-badges {
            grid-column: 2;
        }
        .profile-history-date { grid-row: 2; }
        .profile-history-badges {
            grid-row: 3;
            justify-content: flex-start;
        }
        .profile-history-chevron {
            grid-column: 3;
            grid-row: 1 / 4;
        }
        .profile-history-meta { grid-template-columns: 1fr; }
        .profile-history-details { padding: 14px; }
    }

    /* Standard modal chrome: new form, correction, and pullout */
    #newHealthFormModal .correction-card,
    #correctionModal .correction-card,
    #pulloutRequestModal .correction-card,
    [data-theme="dark"] #newHealthFormModal .correction-card,
    [data-theme="dark"] #correctionModal .correction-card,
    [data-theme="dark"] #pulloutRequestModal .correction-card {
        border: 1px solid rgba(250, 204, 21, .34) !important;
    }

    #newHealthFormModal .correction-head,
    #correctionModal .correction-head,
    #pulloutRequestModal .correction-head {
        background: linear-gradient(135deg, #70131B, #8f2230) !important;
    }

    #newHealthFormModal .correction-head-icon,
    #correctionModal .correction-head-icon,
    #pulloutRequestModal .correction-head-icon,
    #newHealthFormModal .correction-head-icon svg,
    #correctionModal .correction-head-icon svg,
    #pulloutRequestModal .correction-head-icon svg {
        color: #ffffff !important;
        stroke: currentColor !important;
    }

    #newHealthFormModal .correction-close,
    #correctionModal .correction-close,
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
    #pulloutRequestModal .correction-close:hover::after,
    #pulloutRequestModal .correction-close:focus-visible::after {
        left: 128% !important;
    }
</style>
@endpush

@push('late-styles')
<style>
    .main .profile-tab {
        position: relative;
        isolation: isolate;
        overflow: hidden;
    }
    .main .profile-tab::before {
        content: "";
        position: absolute;
        z-index: 0;
        top: -45%;
        left: -130%;
        width: 120%;
        height: 190%;
        background: linear-gradient(115deg, rgba(255, 247, 181, 0) 0%, rgba(255, 247, 181, .78) 46%, rgba(255, 247, 181, 0) 100%);
        transform: skewX(-20deg);
        transition: left .9s ease;
        pointer-events: none;
    }
    .main .profile-tab > svg,
    .main .profile-tab > .profile-tab-label {
        position: relative;
        z-index: 1;
    }
    .main .profile-tab:hover,
    .main .profile-tab:focus-visible,
    .main .profile-tab.is-active:hover,
    .main .profile-tab.is-active:focus-visible {
        border-color: #facc15 !important;
        background: #facc15 !important;
        color: #70131B !important;
        transform: translateY(-2px);
        box-shadow: 0 12px 24px rgba(112, 19, 27, .14) !important;
        outline: none;
    }
    .main .profile-tab:hover::before,
    .main .profile-tab:focus-visible::before {
        left: 125%;
    }
    .main .profile-tab:hover > svg,
    .main .profile-tab:focus-visible > svg,
    .main .profile-tab.is-active:hover > svg,
    .main .profile-tab.is-active:focus-visible > svg {
        color: #70131B !important;
        stroke: currentColor !important;
    }
    html[data-theme="dark"] .main .profile-tab:hover,
    html[data-theme="dark"] .main .profile-tab:focus-visible,
    html[data-theme="dark"] .main .profile-tab.is-active:hover,
    html[data-theme="dark"] .main .profile-tab.is-active:focus-visible {
        border-color: #facc15 !important;
        background: #facc15 !important;
        color: #70131B !important;
    }
</style>
@endpush

@section('content')
@php
    $pulloutValidationErrors = isset($errors)
        ? $errors
        : new \Illuminate\Support\ViewErrorBag();
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

    $profileStatusRaw = trim((string) ($profile->clearance_status ?? ''));
    $pulloutStatus = trim((string) ($profile->pullout_status ?? ''));
    $isPulloutPending = $pulloutStatus === \App\Models\HealthProfile::PULLOUT_PENDING;
    $isPulledOut = $pulloutStatus === \App\Models\HealthProfile::PULLOUT_COMPLETED;
    $isPulloutRestored = $pulloutStatus === \App\Models\HealthProfile::PULLOUT_RESTORED;
    $currentAdmin = auth()->user();
    $isSuperAdmin = $currentAdmin && $currentAdmin->hasRole(\App\Models\User::ROLE_SUPERADMIN);
    $profileStatusNormalized = $isPulloutPending
        ? 'Pullout Pending'
        : ($isPulledOut
            ? 'Pulled Out'
            : (in_array($profileStatusRaw, ['Pending', 'For Verification'], true) ? 'Pending' : $profileStatusRaw));
    $profileStatusClass = match ($profileStatusNormalized) {
        'Issued', 'Fully Cleared' => 'profile-status-issued',
        'Pending', 'Pullout Pending' => 'profile-status-pending',
        'Rejected', 'Pulled Out' => 'profile-status-rejected',
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
    $canResyncPuptas = !$isPulloutPending && !$isPulledOut
        && in_array($profileStatusNormalized, ['Issued', 'Fully Cleared'], true)
        && !in_array($puptasSyncRaw, ['synced', 'not_applicable'], true)
        && (optional(auth()->user())->canAccessPermission('health_records.update_assessment') ?? false);
    $canRequestFileCorrection = !$isPulloutPending && !$isPulledOut
        && in_array($profileStatusNormalized, ['Issued', 'Fully Cleared'], true)
        && (optional(auth()->user())->canAccessPermission('health_records.request_resubmission') ?? false);
    $canReturnToPending = !$isPulloutPending && !$isPulledOut
        && in_array($profileStatusNormalized, ['Issued', 'Fully Cleared'], true)
        && (optional(auth()->user())->canAccessPermission('health_records.request_resubmission') ?? false);
    $canRequestPullout = in_array($profileStatusRaw, ['Issued', 'Fully Cleared'], true)
        && !$isPulloutPending
        && !$isPulledOut
        && $isSuperAdmin;
    $canViewPullout = $isSuperAdmin
        && ($canRequestPullout || $isPulloutPending || $isPulledOut || $isPulloutRestored);
    $pulloutActionLabel = $isPulloutPending
        ? 'Mark as Pulled Out'
        : ($isPulledOut
            ? 'Restore Health Record'
            : 'Mark as Pulled Out');
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

            <div class="profile-status-card {{ $isPulledOut ? 'is-pulled-out' : ($isPulloutPending ? 'is-pullout-pending' : '') }}">
                <div class="profile-status-shield">
                    @if($isPulledOut)
                        <x-outline-icon name="exclamation-circle" />
                    @elseif($isPulloutPending)
                        <x-outline-icon name="clock" />
                    @else
                        <x-outline-icon name="check" />
                    @endif
                </div>
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

    <div class="profile-card profile-content-card">
        <div class="profile-switch-head profile-switch-sticky">
            <div class="profile-switch" role="tablist" aria-label="Health profile sections">
                <button type="button" class="profile-tab is-active" data-profile-tab-target="summaryPanel">
                    <x-outline-icon name="user-circle" />
                    <span class="profile-tab-label">Personal Information</span>
                </button>
                <button type="button" class="profile-tab" data-profile-tab-target="healthPanel">
                    <x-outline-icon name="information-circle" />
                    <span class="profile-tab-label">Health Profile</span>
                </button>
                <button type="button" class="profile-tab" data-profile-tab-target="docsPanel">
                    <x-outline-icon name="document-text" />
                    <span class="profile-tab-label">Uploaded Documents</span>
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
                    @if($canViewPullout)
                        <button type="button" id="openPulloutRequestModal">
                            {{ $pulloutActionLabel }}
                            <span aria-hidden="true">&minus;</span>
                        </button>
                    @endif
                    @if($canReturnToPending)
                        <button type="button" id="openReturnToPendingModal">
                            Return to Pending
                            <span aria-hidden="true">&crarr;</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>

    <div class="profile-panel is-active" id="summaryPanel">
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

    <div class="profile-panel" id="healthPanel">
        @php
            $profileHistory = $healthProfileHistory ?? collect();
            $currentProfileHistory = $profileHistory->firstWhere('is_current', true);
            $previousProfileHistory = $profileHistory
                ->reject(fn ($history) => !empty($history['is_current']))
                ->values();
            $currentProfileVersion = $currentProfileHistory['version'] ?? null;
            $profileVersionCount = $previousProfileHistory->count() + 1;
            $currentProfileDate = optional($currentHealthFormSubmission)->submitted_at ?: $profile->updated_at;
        @endphp

        <div class="profile-version-shell">
            <aside class="profile-version-sidebar" aria-label="Health Profile versions">
                <div class="profile-version-sidebar-head">
                    <div>
                        <h3>Profile Versions</h3>
                        <p>Select a version to review its saved health information.</p>
                    </div>
                    <span class="profile-history-count">{{ $profileVersionCount }}</span>
                </div>

                <div class="profile-version-nav" role="tablist" aria-label="Health Profile versions">
                    <button
                        type="button"
                        class="profile-version-choice is-active"
                        data-profile-version-target="currentHealthProfileVersion"
                        role="tab"
                        aria-selected="true"
                    >
                        <span class="profile-version-choice-number">{{ $currentProfileVersion ? 'V' . $currentProfileVersion : 'LIVE' }}</span>
                        <span class="profile-version-choice-copy">
                            <strong>Current Health Profile</strong>
                            <small>{{ $currentProfileDate ? $currentProfileDate->format('M d, Y') : 'Latest saved information' }}</small>
                        </span>
                    </button>

                    @foreach($previousProfileHistory as $history)
                        @php
                            $historySubmission = $history['submission'];
                            $historyDate = $historySubmission->submitted_at
                                ?: $historySubmission->requested_at
                                ?: $historySubmission->created_at;
                        @endphp
                        <button
                            type="button"
                            class="profile-version-choice"
                            data-profile-version-target="healthProfileVersion{{ $historySubmission->id }}"
                            role="tab"
                            aria-selected="false"
                        >
                            <span class="profile-version-choice-number">V{{ $history['version'] }}</span>
                            <span class="profile-version-choice-copy">
                                <strong>{{ $historySubmission->category ?: 'General Health Form' }}</strong>
                                <small>{{ $historyDate ? $historyDate->format('M d, Y') : 'Date unavailable' }}</small>
                            </span>
                        </button>
                    @endforeach
                </div>
            </aside>

            <div class="profile-version-content">
                <section class="profile-version-pane is-active" id="currentHealthProfileVersion" role="tabpanel">
                    <div class="profile-version-pane-head">
                        <div>
                            <h3>Current Health Profile</h3>
                            <p>Latest medical information used for the active health record.</p>
                        </div>
                        <div class="profile-version-pane-badges">
                            <span class="profile-history-badge is-current">Current</span>
                            <span class="profile-history-badge is-approved">{{ $profileStatusLabel }}</span>
                        </div>
                    </div>

                    @include('admin.partials.health-profile-detail-grid', [
                        'healthData' => $profile->attributesToArray(),
                    ])

                    <div class="profile-timeline-card">
                        <div class="profile-timeline-head">
                            <div>
                                <h3 class="profile-timeline-title">Current Health Record Timeline</h3>
                                <p class="profile-timeline-subtitle">Events for the current Health Profile only.</p>
                            </div>
                        </div>
                        <div class="profile-timeline">
                            <div class="profile-timeline-step">
                                <span class="timeline-node"><x-outline-icon name="check" /></span>
                                <strong>Profile Submitted</strong>
                                <span>{{ optional(optional($currentHealthFormSubmission)->submitted_at ?: $profile->created_at)->format('M d, Y h:i A') ?: 'N/A' }}</span>
                                <small>Student submitted current health requirements</small>
                            </div>
                            <div class="profile-timeline-step">
                                <span class="timeline-node"><x-outline-icon name="check" /></span>
                                <strong>Assessment Completed</strong>
                                <span>{{ $formatProfileDate($profile->assessment_date ?: $profile->verified_at) }}</span>
                                <small>Current assessment and review completed</small>
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
                                <small>Current health record sync status</small>
                            </div>
                            <div class="profile-timeline-step">
                                <span class="timeline-node"><x-outline-icon name="check" /></span>
                                <strong>{{ $profileStatusLabel }}</strong>
                                <span>{{ $formatProfileDate($profile->verified_at ?: $profile->updated_at) }}</span>
                                <small>Current clearance state</small>
                            </div>
                        </div>
                    </div>
                </section>

                @foreach($previousProfileHistory as $history)
                    @php
                        $historySubmission = $history['submission'];
                        $historyStatus = strtolower((string) $historySubmission->status);
                        $historyStatusLabel = ucwords(str_replace('_', ' ', $historyStatus));
                        $historyDocuments = [
                            'student_photo' => '2x2 Student Photo',
                            'health_declaration' => 'Health Declaration',
                            'medical_certificate' => 'Medical Certificate',
                            'medical_assessment_upload' => 'Medical Assessment Copy',
                            'chest_xray_result' => 'Chest X-ray Result',
                            'pwd_id_proof' => 'PWD ID Proof',
                        ];
                        $historyDocumentCount = collect($historyDocuments)
                            ->keys()
                            ->filter(fn ($key) => filled($history['profile'][$key] ?? null))
                            ->count();
                    @endphp
                    <section class="profile-version-pane" id="healthProfileVersion{{ $historySubmission->id }}" role="tabpanel">
                        <div class="profile-version-pane-head">
                            <div>
                                <h3>Version {{ $history['version'] }} - {{ $historySubmission->category ?: 'General Health Form' }}</h3>
                                <p>Frozen health information saved for this submission.</p>
                            </div>
                            <div class="profile-version-pane-badges">
                                <span class="profile-history-badge is-{{ str_replace('_', '-', $historyStatus) }}">{{ $historyStatusLabel }}</span>
                            </div>
                        </div>

                        <div class="profile-history-meta">
                            <div><span>Requested At</span><strong>{{ optional($historySubmission->requested_at)->format('M d, Y h:i A') ?: 'N/A' }}</strong></div>
                            <div><span>Submitted At</span><strong>{{ optional($historySubmission->submitted_at)->format('M d, Y h:i A') ?: 'N/A' }}</strong></div>
                            <div><span>Approved At</span><strong>{{ optional($historySubmission->approved_at)->format('M d, Y h:i A') ?: 'N/A' }}</strong></div>
                            <div><span>Remarks</span><strong>{{ $historySubmission->remarks ?: 'N/A' }}</strong></div>
                        </div>

                        @if($history['has_snapshot'])
                            @if($history['uses_current_fallback'])
                                <div class="profile-history-notice">This legacy entry is showing the current profile because a frozen snapshot was not available when it was created.</div>
                            @endif
                            @include('admin.partials.health-profile-detail-grid', [
                                'healthData' => $history['profile'],
                            ])
                        @else
                            <div class="profile-history-empty-detail">
                                @if($historyStatus === \App\Models\HealthFormSubmission::STATUS_REQUESTED)
                                    This Health Profile version is awaiting submission.
                                @else
                                    Detailed fields are unavailable for this legacy version. Its saved Health Form PDF remains available below when present.
                                @endif
                            </div>
                        @endif

                        <div class="profile-history-documents">
                            <div class="profile-history-section-head">
                                <div>
                                    <h4>Saved Documents</h4>
                                    <p>{{ $historyDocumentCount }} uploaded documents linked to this version.</p>
                                </div>
                            </div>
                            <div class="profile-history-document-links">
                                @if(filled($historySubmission->pdf_path))
                                    <a href="{{ route('admin.health_form_submissions.pdf', $historySubmission) }}" target="_blank" rel="noopener">
                                        <x-outline-icon name="document-text" />
                                        Health Form PDF
                                    </a>
                                @endif
                                @foreach($historyDocuments as $documentKey => $documentLabel)
                                    @if(filled($history['profile'][$documentKey] ?? null))
                                        <a href="{{ route('admin.health_form_submissions.document', ['submission' => $historySubmission, 'document' => $documentKey]) }}" target="_blank" rel="noopener">
                                            <x-outline-icon name="eye" />
                                            {{ $documentLabel }}
                                        </a>
                                    @endif
                                @endforeach
                                @if(blank($historySubmission->pdf_path) && $historyDocumentCount === 0)
                                    <span class="profile-history-no-documents">No saved documents for this version.</span>
                                @endif
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </div>

    <div class="profile-panel" id="docsPanel">
        <div class="doc-grid">
            <div class="doc-file">
                <h4>Health Information Form</h4>
                @php
                    $healthInformationFormUrl = route('walkin.healthForm', ['healthProfile' => $profile->id]);
                @endphp
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
                    @php
                        $medicalCertificateUrl = route($documentRouteName, ['healthProfile' => $profile->id, 'document' => 'medical_certificate']);
                    @endphp
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
                    @php
                        $medicalAssessmentUrl = route($documentRouteName, ['healthProfile' => $profile->id, 'document' => 'medical_assessment_upload']);
                    @endphp
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
                    @php
                        $healthDeclarationUrl = route($documentRouteName, ['healthProfile' => $profile->id, 'document' => 'health_declaration']);
                    @endphp
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
                    @php
                        $chestXrayUrl = route($documentRouteName, ['healthProfile' => $profile->id, 'document' => 'chest_xray_result']);
                    @endphp
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
                        @php
                            $pwdIdProofUrl = route($documentRouteName, ['healthProfile' => $profile->id, 'document' => 'pwd_id_proof']);
                        @endphp
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
                    @php
                        $studentPhotoUrl = route($documentRouteName, ['healthProfile' => $profile->id, 'document' => 'student_photo']);
                    @endphp
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
                <div class="correction-select-wrap correction-custom-select-wrap">
                    <select id="newHealthFormCategory" name="category" class="correction-custom-source" required>
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

@if($canViewPullout)
<div class="correction-modal" id="pulloutRequestModal" aria-hidden="true">
    <div class="correction-card" role="dialog" aria-modal="true" aria-labelledby="pulloutModalTitle">
        <div class="correction-head">
            <div class="correction-head-title">
                <span class="correction-head-icon"><x-outline-icon name="document-text" /></span>
                <div>
                    <h3 id="pulloutModalTitle">{{ $pulloutActionLabel }}</h3>
                    <p>Manage the manual pullout status without deleting the health record.</p>
                </div>
            </div>
            <button type="button" class="correction-close" id="closePulloutRequestModal" aria-label="Close request pullout modal">
                <x-outline-icon name="x-mark" />
            </button>
        </div>
        <div class="correction-body">
            @if($pulloutValidationErrors->hasAny([
                'pullout_reason',
                'pullout_request_remarks',
                'pullout_reference',
                'pullout_completion_remarks',
                'pullout_restore_reason',
            ]))
                <div class="pullout-error" role="alert">{{ $pulloutValidationErrors->first() }}</div>
            @endif
            @if($isPulloutPending || $isPulledOut || $isPulloutRestored)
                <div class="pullout-status-summary">
                    <div class="pullout-status-heading">
                        <span class="profile-status-badge {{ $isPulledOut ? 'profile-status-rejected' : ($isPulloutPending ? 'profile-status-pending' : 'profile-status-issued') }}">
                            {{ $isPulledOut ? 'Pulled Out' : ($isPulloutPending ? 'Pullout Pending' : 'Restored') }}
                        </span>
                        <strong>No medical information or uploaded file has been deleted.</strong>
                    </div>
                    <dl class="pullout-status-grid">
                        <div>
                            <dt>Reason</dt>
                            <dd>{{ $profile->pullout_reason ?: 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt>Requested By</dt>
                            <dd>{{ optional($profile->pulloutRequestedBy)->name ?: optional($profile->pulloutRequestedBy)->email ?: 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt>Requested At</dt>
                            <dd>{{ $profile->pullout_requested_at ? $profile->pullout_requested_at->format('M d, Y h:i A') : 'N/A' }}</dd>
                        </div>
                        @if($isPulledOut || $isPulloutRestored)
                            <div>
                                <dt>Completed By</dt>
                                <dd>{{ optional($profile->pulloutCompletedBy)->name ?: optional($profile->pulloutCompletedBy)->email ?: 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt>Completed At</dt>
                                <dd>{{ $profile->pullout_completed_at ? $profile->pullout_completed_at->format('M d, Y h:i A') : 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt>Reference</dt>
                                <dd>{{ $profile->pullout_reference ?: 'N/A' }}</dd>
                            </div>
                        @endif
                        @if($isPulloutRestored)
                            <div>
                                <dt>Restored By</dt>
                                <dd>{{ optional($profile->pulloutRestoredBy)->name ?: optional($profile->pulloutRestoredBy)->email ?: 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt>Restored At</dt>
                                <dd>{{ $profile->pullout_restored_at ? $profile->pullout_restored_at->format('M d, Y h:i A') : 'N/A' }}</dd>
                            </div>
                        @endif
                    </dl>
                    @if($profile->pullout_request_remarks)
                        <p class="pullout-summary-note"><strong>Request remarks:</strong> {{ $profile->pullout_request_remarks }}</p>
                    @endif
                    @if($profile->pullout_completion_remarks)
                        <p class="pullout-summary-note"><strong>Completion remarks:</strong> {{ $profile->pullout_completion_remarks }}</p>
                    @endif
                    @if($isPulloutRestored && $profile->pullout_restore_reason)
                        <p class="pullout-summary-note"><strong>Restore reason:</strong> {{ $profile->pullout_restore_reason }}</p>
                    @endif
                </div>
            @endif

            @if($isPulloutPending && $isSuperAdmin)
                <form method="POST" action="{{ route('admin.health_profile.pullout.complete', $profile->id) }}" class="pullout-form">
                    @csrf
                    <div class="correction-actions">
                        <button type="button" class="correction-cancel" data-close-pullout>Cancel</button>
                        <button type="submit" class="correction-submit">Mark as Pulled Out</button>
                    </div>
                </form>
            @elseif($isPulledOut && $isSuperAdmin)
                <form method="POST" action="{{ route('admin.health_profile.pullout.restore', $profile->id) }}" class="pullout-form">
                    @csrf
                    <div class="correction-field">
                        <label for="pulloutRestoreReason">Restore Reason</label>
                        <textarea id="pulloutRestoreReason" name="pullout_restore_reason" required placeholder="Explain why this health record is being restored.">{{ old('pullout_restore_reason') }}</textarea>
                    </div>
                    <div class="correction-actions">
                        <button type="button" class="correction-cancel" data-close-pullout>Cancel</button>
                        <button type="submit" class="correction-submit">Restore Health Record</button>
                    </div>
                </form>
            @elseif($canRequestPullout)
                <form method="POST" action="{{ route('admin.health_profile.pullout.request', $profile->id) }}" class="pullout-form">
                    @csrf
                    @if($isPulloutRestored)
                        <div class="pullout-static-note">
                            The previous pullout was restored. A new request will begin a separate manual workflow.
                        </div>
                    @endif
                    <div class="correction-field">
                        <label for="pulloutReason">Pullout Reason</label>
                        <div class="correction-select-wrap correction-custom-select-wrap">
                            <select id="pulloutReason" name="pullout_reason" class="correction-custom-source" required>
                                <option value="">Select a reason</option>
                                @foreach(['Requested by the record owner', 'Transfer or separation', 'Duplicate health record', 'Record issued in error', 'Other administrative reason'] as $reason)
                                    <option value="{{ $reason }}" @selected(old('pullout_reason') === $reason)>{{ $reason }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="correction-field">
                        <label for="pulloutRequestRemarks">Remarks (Optional)</label>
                        <textarea id="pulloutRequestRemarks" name="pullout_request_remarks" placeholder="Add any supporting details for this pullout.">{{ old('pullout_request_remarks') }}</textarea>
                    </div>
                    <div class="pullout-static-note">
                        This request changes only the workflow status. The approved clearance, health profile history, and uploaded documents will remain stored.
                    </div>
                    <div class="correction-actions">
                        <button type="button" class="correction-cancel" data-close-pullout>Cancel</button>
                        <button type="submit" class="correction-submit">Mark as Pulled Out</button>
                    </div>
                </form>
            @else
                <div class="correction-actions">
                    <button type="button" class="correction-submit" data-close-pullout>Done</button>
                </div>
            @endif
        </div>
    </div>
</div>
@endif

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
                    <div class="correction-select-wrap correction-custom-select-wrap">
                        <select id="correctionReasonSelect" class="correction-custom-source" required>
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
    const profileContentCard = document.querySelector('.profile-content-card');
    const profileStickyBar = document.querySelector('.profile-switch-sticky');

    function syncProfileStickyOffsets() {
        if (!profileContentCard || !profileStickyBar) return;
        const versionTop = Math.max(58, profileStickyBar.offsetHeight - 2);
        profileContentCard.style.setProperty('--profile-version-sticky-top', versionTop + 'px');
    }

    syncProfileStickyOffsets();
    window.addEventListener('resize', syncProfileStickyOffsets);
    if (window.ResizeObserver && profileStickyBar) {
        new ResizeObserver(syncProfileStickyOffsets).observe(profileStickyBar);
    }

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
    const pulloutRequestModal = document.getElementById('pulloutRequestModal');
    const openPulloutRequestModal = document.getElementById('openPulloutRequestModal');
    const closePulloutRequestModal = document.getElementById('closePulloutRequestModal');
    const shouldOpenPulloutModal = @json(
        $pulloutValidationErrors->has('pullout_reason')
        || $pulloutValidationErrors->has('pullout_request_remarks')
        || $pulloutValidationErrors->has('pullout_reference')
        || $pulloutValidationErrors->has('pullout_completion_remarks')
        || $pulloutValidationErrors->has('pullout_restore_reason')
    );
    const returnToPendingModal = document.getElementById('returnToPendingModal');
    const openReturnToPendingModal = document.getElementById('openReturnToPendingModal');
    const closeReturnToPendingModal = document.getElementById('closeReturnToPendingModal');
    const cancelReturnToPendingModal = document.getElementById('cancelReturnToPendingModal');

    function setCorrectionDropdownOpen(wrapper, open) {
        if (!wrapper) return;
        wrapper.classList.toggle('is-open', open);
        const trigger = wrapper.querySelector('.correction-custom-trigger');
        trigger?.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (!open) {
            wrapper.classList.remove('is-dropup');
            return;
        }

        const menu = wrapper.querySelector('.correction-custom-menu');
        if (!trigger || !menu) return;
        const triggerRect = trigger.getBoundingClientRect();
        const availableBelow = window.innerHeight - triggerRect.bottom - 18;
        const availableAbove = triggerRect.top - 18;
        const requiredSpace = Math.min(menu.scrollHeight || 250, 250) + 10;
        wrapper.classList.toggle('is-dropup', availableBelow < requiredSpace && availableAbove > availableBelow);
    }

    function closeCorrectionDropdowns(exceptWrapper = null) {
        document.querySelectorAll('.correction-custom-select-wrap.is-open').forEach(function (wrapper) {
            if (wrapper !== exceptWrapper) {
                setCorrectionDropdownOpen(wrapper, false);
            }
        });
    }

    function initializeCorrectionDropdown(select) {
        if (!select || select.dataset.customDropdownReady === 'true') return;
        const wrapper = select.closest('.correction-custom-select-wrap');
        if (!wrapper) return;

        select.dataset.customDropdownReady = 'true';
        const trigger = document.createElement('button');
        const menu = document.createElement('div');
        const menuId = (select.id || 'correction-select') + '-custom-menu';

        trigger.type = 'button';
        trigger.className = 'correction-custom-trigger';
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');
        trigger.setAttribute('aria-controls', menuId);

        menu.id = menuId;
        menu.className = 'correction-custom-menu';
        menu.setAttribute('role', 'listbox');

        function syncDropdown() {
            const selectedOption = select.options[select.selectedIndex] || select.options[0];
            trigger.textContent = selectedOption?.textContent?.trim() || 'Select an option';
            menu.querySelectorAll('.correction-custom-option').forEach(function (button) {
                const selected = button.dataset.value === select.value;
                button.classList.toggle('is-selected', selected);
                button.setAttribute('aria-selected', selected ? 'true' : 'false');
            });
        }

        Array.from(select.options).forEach(function (option) {
            const optionButton = document.createElement('button');
            optionButton.type = 'button';
            optionButton.className = 'correction-custom-option';
            optionButton.dataset.value = option.value;
            const optionLabel = document.createElement('span');
            optionLabel.textContent = option.textContent.trim();
            optionButton.appendChild(optionLabel);
            optionButton.setAttribute('role', 'option');
            optionButton.addEventListener('click', function (event) {
                event.stopPropagation();
                select.value = option.value;
                select.dispatchEvent(new Event('change', { bubbles: true }));
                syncDropdown();
                setCorrectionDropdownOpen(wrapper, false);
                trigger.focus();
            });
            menu.appendChild(optionButton);
        });

        trigger.addEventListener('click', function (event) {
            event.stopPropagation();
            const willOpen = !wrapper.classList.contains('is-open');
            closeCorrectionDropdowns(wrapper);
            setCorrectionDropdownOpen(wrapper, willOpen);
        });
        trigger.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowDown' || event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                closeCorrectionDropdowns(wrapper);
                setCorrectionDropdownOpen(wrapper, true);
                menu.querySelector('.correction-custom-option.is-selected, .correction-custom-option')?.focus();
            }
        });
        select.addEventListener('change', syncDropdown);

        wrapper.appendChild(trigger);
        wrapper.appendChild(menu);
        syncDropdown();
    }

    document.querySelectorAll('.correction-custom-source').forEach(initializeCorrectionDropdown);

    document.addEventListener('click', function (event) {
        if (!event.target.closest('.correction-custom-select-wrap')) {
            closeCorrectionDropdowns();
        }
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeCorrectionDropdowns();
        }
    });

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

    document.querySelectorAll('[data-profile-version-target]').forEach(function (button) {
        button.addEventListener('click', function () {
            const targetId = button.getAttribute('data-profile-version-target');
            const targetPane = targetId ? document.getElementById(targetId) : null;
            if (!targetPane) return;

            document.querySelectorAll('[data-profile-version-target]').forEach(function (versionButton) {
                const selected = versionButton === button;
                versionButton.classList.toggle('is-active', selected);
                versionButton.setAttribute('aria-selected', selected ? 'true' : 'false');
            });
            document.querySelectorAll('.profile-version-pane').forEach(function (pane) {
                pane.classList.toggle('is-active', pane === targetPane);
            });
        });
    });

    function setPulloutRequestModal(open) {
        if (!pulloutRequestModal) return;
        pulloutRequestModal.classList.toggle('is-open', open);
        pulloutRequestModal.setAttribute('aria-hidden', open ? 'false' : 'true');
    }

    openPulloutRequestModal?.addEventListener('click', function () {
        setProfileActionsMenu(false);
        setPulloutRequestModal(true);
    });

    closePulloutRequestModal?.addEventListener('click', function () {
        setPulloutRequestModal(false);
    });

    document.querySelectorAll('[data-close-pullout]').forEach(function (button) {
        button.addEventListener('click', function () {
            setPulloutRequestModal(false);
        });
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
        setProfileActionsMenu(false);
        setReturnToPendingModal(true);
    });

    if (shouldOpenPulloutModal) {
        setPulloutRequestModal(true);
    }

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
            correctionReasonSelect.dispatchEvent(new Event('change', { bubbles: true }));
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
