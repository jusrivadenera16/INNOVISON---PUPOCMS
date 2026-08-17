@extends('layouts.admin')

@section('title', 'Account Access')

@push('styles')
<style>
    .user-management-shell {
        max-width: 1480px;
        margin: 0 auto;
        padding: 20px 24px 40px;
        color: #0f172a;
    }

    .um-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 18px;
        padding: 16px 18px;
        border-radius: 0 0 20px 20px;
        border: 0;
        border-bottom: 2px solid rgba(234, 215, 160, 0.9);
        background: linear-gradient(135deg, rgba(255, 253, 246, 0.76) 0%, rgba(255, 249, 231, 0.58) 42%, rgba(255, 255, 255, 0.82) 100%);
        box-shadow: 0 14px 26px rgba(112, 19, 27, 0.05);
    }

    .um-hero h1 {
        margin: 0;
        font-size: 1.85rem;
        font-weight: 800;
        color: #111827;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 10px 18px;
        border-radius: 0 0 14px 14px;
        border: 0;
        border-bottom: 2px solid rgba(112, 19, 27, 0.72);
        background: transparent;
        box-shadow: none;
    }

    .um-hero h1 svg {
        width: 22px;
        height: 22px;
        flex: 0 0 auto;
    }

    .um-hero p {
        margin: 6px 0 0;
        color: #475569;
        font-size: .82rem;
    }


    .um-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 18px;
    }

    .um-stat {
        background: rgba(255,255,255,0.96);
        border: 1px solid rgba(128, 0, 0, 0.10);
        border-radius: 16px;
        padding: 16px;
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.05);
    }

    html[data-theme="dark"] .um-stat,
    html[data-theme="dark"] .um-card,
    html[data-theme="dark"] .um-modal-content {
        background: rgba(12, 18, 32, 0.96);
        color: #e5eefb;
        border-color: rgba(148, 163, 184, 0.14);
        box-shadow: 0 18px 32px rgba(0, 0, 0, 0.28);
    }


    html[data-theme="dark"] .um-hero {
        border-bottom-color: rgba(143, 34, 48, 0.70);
        background: linear-gradient(135deg, rgba(112, 19, 27, 0.68) 0%, rgba(86, 16, 26, 0.64) 48%, rgba(44, 14, 18, 0.72) 100%);
        box-shadow: 0 16px 28px rgba(0, 0, 0, 0.22);
    }

    html[data-theme="dark"] .user-management-shell,
    html[data-theme="dark"] .um-hero h1,
    html[data-theme="dark"] .um-name,
    html[data-theme="dark"] .um-modal-head h3,
    html[data-theme="dark"] .um-field input,
    html[data-theme="dark"] .um-field select,
    html[data-theme="dark"] .um-field textarea {
        color: #e5eefb;
    }

    html[data-theme="dark"] .um-hero h1 {
        border-bottom-color: rgba(143, 34, 48, 0.70);
        background: transparent;
        box-shadow: none;
    }

    html[data-theme="dark"] .um-hero p,
    html[data-theme="dark"] .um-summary-note,
    html[data-theme="dark"] .um-directory-toggle .hint,
    html[data-theme="dark"] .um-sub,
    html[data-theme="dark"] .um-note,
    html[data-theme="dark"] .um-empty,
    html[data-theme="dark"] .um-stat .label,
    html[data-theme="dark"] .um-summary-label,
    html[data-theme="dark"] .um-field label {
        color: #ffffff;
    }

    html[data-theme="dark"] .user-management-shell,
    html[data-theme="dark"] .user-management-shell * {
        color: #ffffff;
    }

    html[data-theme="dark"] .um-stat .value,
    html[data-theme="dark"] .um-summary-value {
        color: #fca5a5;
    }

    html[data-theme="dark"] .um-card,
    html[data-theme="dark"] .um-detail-card {
        background: rgba(9, 14, 26, 0.96);
        border-color: rgba(148, 163, 184, 0.16);
    }

    html[data-theme="dark"] .um-summary-card {
        background: linear-gradient(180deg, rgba(15, 23, 42, 0.98), rgba(17, 24, 39, 0.94));
        border-color: rgba(148, 163, 184, 0.14);
    }

    html[data-theme="dark"] .um-panel-intro,
    html[data-theme="dark"] .um-panel-header h2,
    html[data-theme="dark"] .um-panel-header p {
        color: #fff;
    }

    html[data-theme="dark"] .um-card-head,
    html[data-theme="dark"] .um-modal-head {
        border-color: rgba(148, 163, 184, 0.14);
    }

    html[data-theme="dark"] .um-table thead th {
        background: rgba(15, 23, 42, 0.98);
        color: #cbd5e1;
        border-bottom-color: rgba(148, 163, 184, 0.14);
    }

    html[data-theme="dark"] .um-table tbody td {
        border-bottom-color: rgba(148, 163, 184, 0.12);
        color: #e5eefb;
    }

    html[data-theme="dark"] .um-search input,
    html[data-theme="dark"] .um-field input,
    html[data-theme="dark"] .um-field select,
    html[data-theme="dark"] .um-field textarea {
        background: rgba(15, 23, 42, 0.92);
        border-color: rgba(148, 163, 184, 0.24);
    }

    html[data-theme="dark"] .um-search input::placeholder,
    html[data-theme="dark"] .um-field input::placeholder,
    html[data-theme="dark"] .um-field textarea::placeholder {
        color: #64748b;
    }

    html[data-theme="dark"] .um-btn-soft {
        background: rgba(15, 23, 42, 0.92);
        color: #e5eefb;
        border-color: rgba(148, 163, 184, 0.22);
    }

    html[data-theme="dark"] .um-action-btn {
        background: rgba(15, 23, 42, 0.92);
        color: #fca5a5;
        border-color: rgba(248, 113, 113, 0.28);
    }

    html[data-theme="dark"] .um-action-btn:hover {
        background: rgba(127, 29, 29, 0.26);
    }

    html[data-theme="dark"] .um-badge.source {
        background: rgba(127, 29, 29, 0.22);
        color: #fda4af;
    }

    html[data-theme="dark"] .um-badge.active {
        background: rgba(34, 197, 94, 0.16);
        color: #86efac;
    }

    html[data-theme="dark"] .um-badge.inactive {
        background: rgba(239, 68, 68, 0.16);
        color: #fca5a5;
    }

    html[data-theme="dark"] .um-cursor-hint {
        background: rgba(226, 232, 240, 0.96);
        color: #0f172a;
    }

    .um-stat .label {
        font-size: .8rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #64748b;
        margin-bottom: 6px;
    }

    .um-stat .value {
        font-size: 1.65rem;
        font-weight: 800;
        color: #800000;
    }

    .um-card {
        background: rgba(255,255,255,0.98);
        border: 1px solid rgba(100, 116, 139, 0.16);
        border-radius: 18px;
        box-shadow: 0 18px 32px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .um-panel-intro {
        padding: 16px 20px 0;
        color: #64748b;
        line-height: 1.6;
    }

    .um-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 18px 20px 12px;
    }

    .um-panel-header h2 {
        margin: 0;
        font-size: 1.08rem;
        font-weight: 900;
        color: #111827;
    }

    .um-panel-header p {
        margin: 6px 0 0;
        color: #64748b;
        font-size: .8rem;
    }

    .um-btn-ghost {
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #ffffff !important;
        border: 1px solid #8f2230;
    }

    .um-btn-ghost:hover,
    .um-btn-ghost:focus-visible {
        color: #ffffff !important;
    }

    @keyframes umModeFloat {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-8px);
        }
    }

    .um-summary-grid {
        display: flex;
        gap: 12px;
        padding: 16px 20px 8px;
        overflow-x: auto;
        scrollbar-width: thin;
    }

    .um-summary-card {
        min-width: 220px;
        flex: 0 0 220px;
        background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(248,250,252,0.94));
        border: 1px solid rgba(148, 163, 184, 0.16);
        border-radius: 16px;
        padding: 12px 14px;
        box-shadow: 0 8px 16px rgba(15, 23, 42, 0.04);
    }

    .um-summary-label {
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #64748b;
        font-weight: 800;
        margin-bottom: 6px;
    }

    .um-summary-value {
        font-size: 1.35rem;
        font-weight: 900;
        color: #800000;
        line-height: 1;
    }

    .um-summary-note {
        margin: 4px 0 0;
        color: #64748b;
        font-size: .8rem;
    }

    .um-directory-toggle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 0 20px 18px;
    }

    .um-directory-toggle .hint {
        color: #64748b;
        font-size: .92rem;
    }

    .um-directory-panel {
        display: none;
    }

    .um-directory-panel.is-open {
        display: block;
    }

    .um-card-head {
        padding: 18px 20px;
        border-bottom: 1px solid rgba(100, 116, 139, 0.12);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .um-search {
        display: flex;
        gap: 10px;
        align-items: center;
        flex: 1;
    }

    .um-search input {
        width: 100%;
        border-radius: 12px;
        border: 1px solid rgba(148, 163, 184, 0.45);
        padding: 12px 14px;
        font-size: 0.95rem;
        color: #111827;
        background: #fff;
    }

    .um-search input:focus {
        outline: none;
        border-color: #800000;
        box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.12);
    }

    .um-btn {
        border: 1px solid #8f2230;
        border-radius: 10px;
        padding: 10px 16px;
        font-weight: 800;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        position: relative;
        overflow: hidden;
        transition: color .08s linear, transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        z-index: 0;
    }

    .um-btn::after {
        content: "";
        position: absolute;
        inset: 0;
        background:
            linear-gradient(120deg,
                rgba(255, 248, 196, 0) 0%,
                rgba(255, 239, 181, 0.14) 22%,
                rgba(255, 239, 181, 0.52) 48%,
                rgba(255, 239, 181, 0.14) 72%,
                rgba(255, 248, 196, 0) 100%);
        transform: translateX(-135%);
        transition: transform 1.5s ease;
        z-index: -1;
    }

    .um-btn:hover {
        transform: translateY(-1px);
        border-color: #facc15;
        background: #facc15 !important;
        color: #70131B !important;
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.18),
            0 14px 24px rgba(112, 19, 27, 0.16);
    }

    .um-btn:hover::after {
        transform: translateX(135%);
    }

    .um-btn-primary {
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #fff;
        box-shadow:
            0 0 0 3px rgba(112, 19, 27, 0.12),
            0 10px 22px rgba(112, 19, 27, 0.20);
    }

    .um-btn-soft {
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #ffffff;
        box-shadow:
            0 0 0 3px rgba(112, 19, 27, 0.12),
            0 10px 22px rgba(112, 19, 27, 0.20);
    }

    .um-table-wrap {
        overflow-x: auto;
    }
    .um-lookup-pagination {
        display: none;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin: 14px 0 0;
        padding: 12px;
        border: 1px solid rgba(112, 19, 27, 0.12);
        border-radius: 16px;
        background: #fffaf7;
    }
    .um-lookup-pagination.is-visible {
        display: flex;
    }
    .um-lookup-pagination-summary {
        color: #64748b;
        font-size: .75rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .05em;
    }
    .um-lookup-pagination-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .um-lookup-page-btn {
        min-width: 92px;
        min-height: 40px;
        border: 1px solid rgba(112, 19, 27, 0.35);
        border-radius: 999px;
        background: #fff;
        color: #70131B;
        font-size: .78rem;
        font-weight: 900;
        cursor: pointer;
        transition: background-color .18s ease, border-color .18s ease, color .18s ease, transform .18s ease;
    }
    .um-lookup-page-btn:hover:not(:disabled) {
        background: #ffcc00;
        border-color: #ffcc00;
        color: #111827;
        transform: translateY(-1px);
    }
    .um-lookup-page-btn:disabled {
        cursor: not-allowed;
        opacity: .45;
    }

    .um-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1080px;
    }

    .um-table thead th {
        text-align: left;
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #64748b;
        padding: 14px 18px;
        background: rgba(248, 250, 252, 0.9);
        border-bottom: 1px solid rgba(148, 163, 184, 0.14);
    }

    .um-table tbody td {
        padding: 16px 18px;
        border-bottom: 1px solid rgba(148, 163, 184, 0.12);
        vertical-align: middle;
        color: #0f172a;
    }

    .um-table tbody td:nth-child(1),
    .um-table tbody td:nth-child(2),
    .um-table tbody td:nth-child(3) {
        font-size: .82rem;
    }

    .um-table .um-name {
        font-size: .84rem;
    }

    .um-table .um-sub {
        font-size: .72rem;
    }

    .um-table thead th:last-child,
    .um-table tbody td:last-child {
        width: 190px;
        min-width: 190px;
        white-space: nowrap;
    }

    .um-user {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .um-user-card {
        cursor: pointer;
    }

    tr[data-user-card][data-can-edit="1"],
    tr[data-user-card][data-can-onboard="1"] {
        cursor: pointer;
    }

    tr[data-user-card][data-can-edit="1"]:hover .um-user,
    tr[data-user-card][data-can-onboard="1"]:hover .um-user {
        background: rgba(128, 0, 0, 0.04);
        border-radius: 12px;
    }

    .um-avatar {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        overflow: hidden;
        flex: 0 0 44px;
        position: relative;
        background: linear-gradient(145deg, #8f1725 0%, #70131b 56%, #4e0a12 100%);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(234, 179, 8, 0.78);
        font-weight: 900;
        letter-spacing: .04em;
        box-shadow:
            inset 0 1px 0 rgba(255,255,255,0.18),
            0 8px 16px rgba(112, 19, 27, 0.20);
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }

    .um-avatar::after {
        content: "";
        position: absolute;
        right: 4px;
        bottom: 4px;
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #facc15;
        border: 2px solid #70131b;
        box-shadow: 0 0 0 1px rgba(255,255,255,0.45);
    }

    tr[data-user-card]:hover .um-avatar {
        transform: translateY(-2px);
        border-color: #facc15;
        box-shadow:
            inset 0 1px 0 rgba(255,255,255,0.20),
            0 11px 20px rgba(112, 19, 27, 0.28);
    }

    .um-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .um-avatar:has(img)::after {
        border-color: #ffffff;
    }

    .um-name {
        font-weight: 800;
        color: #111827;
        line-height: 1.2;
    }

    .um-sub {
        font-size: .82rem;
        color: #64748b;
        margin-top: 2px;
    }

    .um-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 11px;
        border-radius: 999px;
        font-size: .8rem;
        font-weight: 700;
    }

    .um-badge.active {
        background: rgba(34, 197, 94, 0.12);
        color: #166534;
    }

    .um-badge.inactive {
        background: rgba(239, 68, 68, 0.12);
        color: #991b1b;
    }

    .um-badge.source {
        background: rgba(128, 0, 0, 0.10);
        color: #800000;
    }

    .um-cursor-hint {
        position: fixed;
        z-index: 9999;
        display: none;
        pointer-events: none;
        background: rgba(17, 24, 39, 0.92);
        color: #fff;
        border-radius: 999px;
        padding: 8px 12px;
        font-size: .78rem;
        font-weight: 800;
        letter-spacing: .03em;
        box-shadow: 0 10px 18px rgba(15, 23, 42, 0.18);
        white-space: nowrap;
    }

    .um-action-btn {
        border: 1px solid rgba(128, 0, 0, 0.18);
        background: #fff;
        color: #800000;
        border-radius: 10px;
        padding: 9px 12px;
        font-weight: 700;
        cursor: pointer;
    }

    .um-action-btn:hover {
        background: rgba(128, 0, 0, 0.06);
    }

    .um-empty {
        padding: 56px 18px;
        text-align: center;
        color: #64748b;
    }

    .um-modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(10px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 5000;
        padding: 18px;
    }

    .um-modal-backdrop.show {
        display: flex;
    }

    .um-modal-content {
        width: min(1200px, 100%);
        max-height: 92vh;
        overflow: auto;
        background: #fff;
        border-radius: 22px;
        border: 1px solid rgba(148, 163, 184, 0.18);
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.28);
    }

    #lookupModal .um-modal-content {
        width: min(920px, 100%);
        max-height: 88vh;
        border-radius: 18px;
    }

    #settingsModal .um-modal-content {
        width: min(980px, 100%);
        max-height: 88vh;
        border-radius: 18px;
    }

    .um-modal-head {
        padding: 18px 20px;
        border-bottom: 1px solid rgba(148, 163, 184, 0.14);
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
    }

    #lookupModal .um-modal-head {
        padding: 14px 16px;
    }

    #settingsModal .um-modal-head {
        padding: 14px 16px;
    }

    .um-modal-head h3 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 800;
        color: #111827;
    }

    .um-modal-body {
        padding: 18px 20px 22px;
    }

    #lookupModal .um-modal-body {
        padding: 14px 16px 18px;
    }

    #settingsModal .um-modal-body {
        padding: 14px 16px 18px;
    }

    .um-modal-grid {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 20px;
    }

    #lookupModal .um-modal-grid {
        grid-template-columns: 240px 1fr;
        gap: 14px;
    }

    #settingsModal .um-modal-grid {
        grid-template-columns: 240px 1fr;
        gap: 14px;
    }

    #settingsModal .um-detail-card {
        padding: 14px;
        border-radius: 16px;
    }

    #settingsModal .um-detail-photo {
        width: 84px;
        height: 84px;
        font-size: 1.65rem;
        margin-bottom: 12px;
        border-radius: 16px;
    }

    #settingsModal .um-field {
        margin-bottom: 12px;
    }

    #lookupModal .um-table {
        min-width: 760px;
    }

    #lookupModal .um-table thead th,
    #lookupModal .um-table tbody td {
        padding: 12px 14px;
    }

    #lookupModal .um-table thead th:last-child,
    #lookupModal .um-table tbody td:last-child {
        width: 200px;
        min-width: 200px;
    }

    #lookupModal .um-search input {
        padding: 10px 12px;
    }

    .um-detail-card {
        background: rgba(248, 250, 252, 0.85);
        border: 1px solid rgba(148, 163, 184, 0.16);
        border-radius: 18px;
        padding: 18px;
    }

    .um-section-block {
        padding: 18px;
        border-radius: 18px;
        border: 1px solid rgba(148, 163, 184, 0.16);
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(248, 250, 252, 0.9));
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.05);
    }

    .um-section-block + .um-section-block {
        margin-top: 18px;
    }

    .um-section-block.account-access {
        border-color: rgba(128, 0, 0, 0.14);
    }

    .um-section-block.admin-hub {
        border-color: rgba(30, 64, 175, 0.16);
        background: linear-gradient(180deg, rgba(239, 246, 255, 0.92), rgba(248, 250, 252, 0.95));
    }

    .um-section-block.is-hidden {
        display: none;
    }

    #settingsModal.admin-hub-mode #accountAccessSection {
        display: none !important;
    }

    #settingsModal.account-access-mode #accountAccessSection {
        display: block !important;
    }

    .um-section-title {
        margin: 0 0 4px;
        font-size: 1rem;
        font-weight: 800;
        color: #800000;
    }

    .um-section-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(128, 0, 0, 0.08);
        color: #800000;
        font-size: .76rem;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .um-section-block.admin-hub .um-section-kicker {
        background: rgba(30, 64, 175, 0.10);
        color: #1d4ed8;
    }

    .um-section-copy {
        margin: 0 0 14px;
        color: #64748b;
        font-size: .9rem;
        line-height: 1.55;
    }

    .um-profile-list {
        display: grid;
        gap: 12px;
    }

    .um-profile-row {
        display: grid;
        grid-template-columns: 150px 1fr;
        gap: 12px;
        align-items: center;
        padding: 12px 14px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.8);
        border: 1px solid rgba(148, 163, 184, 0.14);
    }

    .um-profile-row .label {
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        font-weight: 800;
        color: #64748b;
    }

    .um-profile-row .value {
        font-weight: 700;
        color: #0f172a;
        word-break: break-word;
    }

    .um-detail-photo {
        width: 100px;
        height: 100px;
        border-radius: 18px;
        overflow: hidden;
        background: linear-gradient(135deg, #800000, #d97706);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 14px;
    }

    .um-detail-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .um-field {
        margin-bottom: 14px;
    }

    .um-field label {
        display: block;
        font-size: .8rem;
        font-weight: 700;
        color: #64748b;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .um-field input,
    .um-field select,
    .um-field textarea {
        width: 100%;
        border: 1px solid rgba(148, 163, 184, 0.45);
        border-radius: 12px;
        padding: 11px 12px;
        color: #111827;
        background: #fff;
    }

    .um-field input[readonly],
    .um-field textarea[readonly] {
        background: #f8fafc;
        color: #475569;
    }

    /* Reference Lookup modal language */
    .um-modal-content {
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.62);
        border-bottom: 4px solid #70131b;
        border-radius: 24px;
        background: #ffffff;
    }

    .um-modal-head {
        align-items: center;
        padding: 18px 20px !important;
        border-bottom: 0;
        background: linear-gradient(135deg, #7f1d1d, #991b1b 55%, #b91c1c);
        color: #ffffff;
    }

    .um-modal-head-main {
        display: flex;
        min-width: 0;
        align-items: center;
        gap: 14px;
    }

    .um-modal-head-badge {
        display: inline-flex;
        width: 46px;
        height: 46px;
        flex: 0 0 46px;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(255, 255, 255, 0.24);
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.16);
        color: #ffffff;
        font-size: 12px;
        font-weight: 900;
    }

    .um-modal-head h3 {
        color: #ffffff !important;
        font-size: 1rem;
        font-weight: 900;
        text-transform: uppercase;
    }

    .um-modal-head .um-note {
        margin-top: 5px;
        color: rgba(255, 255, 255, 0.92) !important;
        font-size: 12px;
    }

    .um-modal-close {
        position: relative;
        display: inline-flex;
        width: 40px;
        height: 40px;
        min-width: 40px;
        padding: 0 0 3px;
        overflow: hidden;
        align-items: center;
        justify-content: center;
        border: 1px solid #8f2230;
        border-radius: 50%;
        background: #70131b;
        color: #ffffff;
        cursor: pointer;
        font-size: 24px;
        line-height: .75;
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .um-modal-close::after {
        position: absolute;
        inset: 0;
        background: linear-gradient(120deg, transparent, rgba(255, 239, 181, .52), transparent);
        content: "";
        transform: translateX(-135%);
        transition: transform .65s ease;
    }

    .um-modal-close:hover,
    .um-modal-close:focus-visible {
        border-color: #facc15;
        box-shadow: 0 0 0 3px rgba(250, 204, 21, .18);
        outline: none;
        transform: translateY(-1px);
    }

    .um-modal-close:hover::after,
    .um-modal-close:focus-visible::after {
        transform: translateX(135%);
    }

    .um-modal-body {
        max-height: calc(100vh - 145px);
        overflow-y: auto;
        background: #f8fafc;
        scrollbar-color: #8f2230 #e5e7eb;
        scrollbar-width: thin;
    }

    #settingsModal .um-detail-card {
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .06);
    }

    #settingsModal .um-section-block {
        border-radius: 14px;
        background: #ffffff;
    }

    /* Inventory-style custom dropdown */
    .um-custom-select {
        position: relative;
    }

    .um-custom-select-native {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: hidden;
        border: 0 !important;
        opacity: 0;
        pointer-events: none;
    }

    .um-custom-select-button {
        position: relative;
        width: 100%;
        min-height: 52px;
        padding: 14px 48px 14px 16px;
        border: 1px solid rgba(127, 29, 29, .22);
        border-radius: 18px;
        background: linear-gradient(180deg, #ffffff, #fff8f6);
        color: #111827;
        box-shadow: 0 10px 22px rgba(15, 23, 42, .08);
        cursor: pointer;
        font: inherit;
        font-size: 14px;
        font-weight: 800;
        text-align: left;
    }

    .um-custom-select-button::after {
        position: absolute;
        top: 50%;
        right: 18px;
        width: 8px;
        height: 8px;
        border-right: 2px solid #70131b;
        border-bottom: 2px solid #70131b;
        content: "";
        transform: translateY(-70%) rotate(45deg);
        transition: transform .18s ease;
    }

    .um-custom-select.is-open .um-custom-select-button {
        border-color: #8f2230;
        box-shadow: 0 0 0 3px rgba(143, 34, 48, .10), 0 12px 24px rgba(15, 23, 42, .10);
    }

    .um-custom-select.is-open .um-custom-select-button::after {
        transform: translateY(-25%) rotate(225deg);
    }

    .um-custom-select-menu {
        position: absolute;
        z-index: 5100;
        top: calc(100% + 8px);
        right: 0;
        left: 0;
        display: none;
        gap: 8px;
        max-height: 250px;
        overflow-y: auto;
        padding: 10px;
        border: 1px solid rgba(127, 29, 29, .18);
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 18px 38px rgba(15, 23, 42, .18);
    }

    .um-custom-select.is-open .um-custom-select-menu {
        display: grid;
    }

    .um-custom-select-option {
        width: 100%;
        padding: 11px 14px;
        border: 1px solid rgba(148, 163, 184, .22);
        border-radius: 999px;
        background: linear-gradient(180deg, #ffffff, #f8fafc);
        color: #1e293b;
        cursor: pointer;
        font: inherit;
        font-size: 13px;
        font-weight: 800;
        text-align: left;
        transition: background .18s ease, border-color .18s ease, color .18s ease, transform .18s ease;
    }

    .um-custom-select-option:hover,
    .um-custom-select-option.is-selected {
        border-color: #8b0000;
        background: linear-gradient(135deg, #8b0000, #70131b);
        color: #facc15;
        transform: translateY(-1px);
    }

    html[data-theme="dark"] .um-modal-body,
    html[data-theme="dark"] #settingsModal .um-detail-card,
    html[data-theme="dark"] #settingsModal .um-section-block {
        background: #111827;
    }

    html[data-theme="dark"] .um-custom-select-button,
    html[data-theme="dark"] .um-custom-select-menu {
        border-color: rgba(248, 113, 113, .28);
        background: #0f172a;
        color: #ffffff;
    }

    html[data-theme="dark"] .um-custom-select-option {
        border-color: rgba(148, 163, 184, .22);
        background: #172033;
        color: #ffffff;
    }

    .um-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
        margin-top: 16px;
    }

    .um-note {
        color: #64748b;
        font-size: .92rem;
        line-height: 1.55;
    }

    html[data-theme="dark"] .um-section-copy,
    html[data-theme="dark"] .um-profile-row .label {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .um-section-block {
        background: linear-gradient(180deg, rgba(15, 23, 42, 0.98), rgba(17, 24, 39, 0.94));
        border-color: rgba(148, 163, 184, 0.14);
        box-shadow: 0 18px 32px rgba(0, 0, 0, 0.22);
    }

    html[data-theme="dark"] .um-section-block.admin-hub {
        background: linear-gradient(180deg, rgba(15, 23, 42, 0.98), rgba(17, 24, 39, 0.94));
        border-color: rgba(59, 130, 246, 0.24);
    }

    html[data-theme="dark"] .um-profile-row {
        background: rgba(15, 23, 42, 0.84);
        border-color: rgba(148, 163, 184, 0.14);
    }

    html[data-theme="dark"] .um-profile-row .value,
    html[data-theme="dark"] .um-section-title {
        color: #fff;
    }

    html[data-theme="dark"] .um-section-kicker {
        background: rgba(248, 113, 113, 0.16);
        color: #fecaca;
    }

    html[data-theme="dark"] .um-section-block.admin-hub .um-section-kicker {
        background: rgba(96, 165, 250, 0.16);
        color: #bfdbfe;
    }

    @media (max-width: 1024px) {
        .um-grid,
        .um-modal-grid {
            grid-template-columns: 1fr;
        }

        .um-hero {
            align-items: flex-start;
            flex-direction: column;
            border-radius: 0 0 18px 18px;
        }

        .um-summary-card {
            min-width: 200px;
            flex-basis: 200px;
        }
    }

    @media (max-width: 768px) {
        .user-management-shell {
            padding: 14px 14px 30px;
        }

        .um-card-head {
            flex-direction: column;
            align-items: stretch;
        }

        .um-directory-toggle {
            align-items: flex-start;
            flex-direction: column;
        }

        .um-summary-card {
            min-width: 180px;
            flex-basis: 180px;
        }
    }
</style>
@endpush
@push('styles')
    @include('admin.user_management.modal-ui-styles')
@endpush

@section('content')
<div class="user-management-shell">
    <div class="um-hero">
        <div>
            <h1><x-outline-icon name="users" />Account Access</h1>
            <p>Manage clinic login role, student-side email, and active or inactive access.</p>
        </div>
    </div>

    <div id="account-access-panel">
        <div class="um-card">
            <div class="um-panel-header">
                <div>
                    <h2>Account Access</h2>
                </div>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <a href="{{ route('admin.user-management') }}" class="um-btn um-btn-ghost">Back</a>
                    <button type="button" class="um-btn um-btn-primary" data-open-lookup="account-access">
                        <span>+</span> Add User Roles
                    </button>
                </div>
            </div>
            <div class="um-directory-panel is-open" id="directoryPanel">
            <div class="um-table-wrap">
                <table class="um-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($localRecords as $record)
                            <tr
                                data-user-card
                                data-update-url="{{ $record['can_edit'] ? route('admin.user-management.update', $record['id']) : '' }}"
                                data-create-url="{{ !$record['can_edit'] && !empty($record['can_onboard']) ? route('admin.user-management.store-from-lookup') : '' }}"
                                data-delete-url="{{ $record['can_edit'] ? route('admin.user-management.destroy', $record['id']) : '' }}"
                                data-delete-account-url="{{ $record['can_edit'] ? route('admin.user-management.delete-account', $record['id']) : '' }}"
                                data-can-edit="{{ $record['can_edit'] ? '1' : '0' }}"
                                data-can-onboard="{{ !empty($record['can_onboard']) ? '1' : '0' }}"
                            data-id="{{ $record['record_id'] }}"
                            data-name="{{ $record['name'] }}"
                            data-first-name="{{ $record['first_name'] }}"
                            data-last-name="{{ $record['last_name'] }}"
                            data-email="{{ $record['email'] }}"
                                data-role="{{ $record['raw_role'] }}"
                                data-role-label="{{ $record['role'] }}"
                                data-status="{{ $record['status'] }}"
                                data-source="{{ $record['source'] }}"
                                data-source-label="{{ $record['source_label'] }}"
                                data-student-id="{{ $record['student_id'] }}"
                            data-avatar-url="{{ $record['avatar_url'] ?? '' }}"
                            data-avatar-letter="{{ $record['avatar_letter'] }}"
                            data-updated="{{ $record['meta']['updated_at'] ?? '' }}"
                            data-management-view="account-access"
                            data-meta='@json($record["meta"])'
                        >
                                <td>
                                    <div class="um-user">
                                        <div class="um-avatar">
                                            @if(!empty($record['avatar_url']))
                                                <img src="{{ $record['avatar_url'] }}" alt="{{ $record['name'] }}">
                                            @else
                                                {{ $record['avatar_letter'] }}
                                            @endif
                                        </div>
                                        <div>
                                            <div class="um-name">{{ $record['name'] }}</div>
                                            <div class="um-sub">{{ $record['student_id'] ?: 'ID not available' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $record['email'] ?: 'N/A' }}</td>
                                <td>{{ $record['role'] }}</td>
                                <td>
                                    <span class="um-badge {{ $record['status'] === 'inactive' ? 'inactive' : 'active' }}">
                                        {{ ucfirst($record['status']) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4"><div class="um-empty">No managed clinic users found yet.</div></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            </div>
        </div>
    </div>

</div>

<div class="um-modal-backdrop {{ $lookupSearch !== '' ? 'show' : '' }}" id="lookupModal">
    <div class="um-modal-content">
        <div class="um-modal-head">
            <div class="um-modal-head-main">
                <div class="um-modal-head-badge">AR</div>
                <div>
                    <h3>Add User Roles</h3>
                    <div class="um-note">Search across students, faculty, or admin profiles to add roles.</div>
                </div>
            </div>
            <button type="button" class="um-modal-close" data-close-lookup aria-label="Close role lookup">&times;</button>
        </div>
        <div class="um-modal-body">
            <form class="um-search" method="GET" action="{{ route('admin.user-management.account-access') }}">
                <input type="hidden" name="management_view" value="{{ $managementView ?: 'account-access' }}" id="lookupManagementViewField">
                <input type="search" name="lookup_search" value="{{ $lookupSearch }}" placeholder="Search users by email, name, or ID" id="lookupSearchField">
                <button class="um-btn um-btn-primary" type="submit">Search</button>
            </form>
            <div class="um-directory-toggle" style="padding: 14px 0 10px;">
                <div class="hint">Type a search term to show matching users below, or find users from the list manually.</div>
            </div>
            <div style="margin-top: 16px;" class="um-directory-panel {{ $lookupSearch !== '' ? 'is-open' : '' }}" id="lookupDirectoryPanel">
            <div class="um-table-wrap">
                <table class="um-table" style="min-width: 900px;">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="lookupResultsBody">
                        @forelse($lookupRecords as $record)
                            <tr
                                data-user-card
                                data-lookup-result-row
                                data-update-url="{{ $record['can_edit'] ? route('admin.user-management.update', $record['id']) : '' }}"
                                data-delete-url="{{ $record['can_edit'] ? route('admin.user-management.destroy', $record['id']) : '' }}"
                                data-delete-account-url="{{ $record['can_edit'] ? route('admin.user-management.delete-account', $record['id']) : '' }}"
                                data-create-url="{{ !$record['can_edit'] && !empty($record['can_onboard']) ? route('admin.user-management.store-from-lookup') : '' }}"
                                data-can-edit="{{ $record['can_edit'] ? '1' : '0' }}"
                                data-can-onboard="{{ !empty($record['can_onboard']) ? '1' : '0' }}"
                                data-id="{{ $record['record_id'] }}"
                                data-name="{{ $record['name'] }}"
                                data-first-name="{{ $record['first_name'] }}"
                                data-last-name="{{ $record['last_name'] }}"
                                data-email="{{ $record['email'] }}"
                                data-role="{{ $record['raw_role'] }}"
                                data-role-label="{{ $record['role'] }}"
                                data-status="{{ $record['status'] }}"
                                data-source="{{ $record['source'] }}"
                                data-source-label="{{ $record['source_label'] }}"
                                data-student-id="{{ $record['student_id'] }}"
                                data-avatar-url="{{ $record['avatar_url'] ?? '' }}"
                                data-avatar-letter="{{ $record['avatar_letter'] }}"
                                data-updated="{{ $record['meta']['updated_at'] ?? '' }}"
                                data-meta='@json($record["meta"])'
                            >
                                <td>
                                    <div class="um-user">
                                        <div class="um-avatar">
                                            @if(!empty($record['avatar_url']))
                                                <img src="{{ $record['avatar_url'] }}" alt="{{ $record['name'] }}">
                                            @else
                                                {{ $record['avatar_letter'] }}
                                            @endif
                                        </div>
                                        <div>
                                            <div class="um-name">{{ $record['name'] }}</div>
                                            <div class="um-sub">{{ $record['student_id'] ?: 'ID not available' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $record['email'] ?: 'N/A' }}</td>
                                <td>{{ $record['role'] }}</td>
                                <td><span class="um-badge {{ $record['status'] === 'inactive' ? 'inactive' : 'active' }}">{{ ucfirst($record['status']) }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4"><div class="um-empty">No Users matched the current search.</div></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($lookupSearch !== '' && count($lookupRecords) > 0)
                <div class="um-lookup-pagination" id="lookupPagination" data-page-size="8">
                    <span class="um-lookup-pagination-summary" id="lookupPaginationSummary">Showing 0-0 of 0</span>
                    <div class="um-lookup-pagination-actions">
                        <button type="button" class="um-lookup-page-btn" id="lookupPrevPage">Previous</button>
                        <button type="button" class="um-lookup-page-btn" id="lookupNextPage">Next</button>
                    </div>
                </div>
            @endif
            </div>
        </div>
    </div>
</div>

<div class="um-modal-backdrop" id="settingsModal">
    <div class="um-modal-content">
        <div class="um-modal-head">
            <div class="um-modal-head-main">
                <div class="um-modal-head-badge">AA</div>
                <div>
                    <h3>User Settings</h3>
                    <div class="um-note">Review the account, adjust the role or status, deactivate if needed, or delete the account.</div>
                </div>
            </div>
            <button type="button" class="um-modal-close" data-close-settings aria-label="Close user settings">&times;</button>
        </div>
        <div class="um-modal-body">
            <div class="um-modal-grid">
                <div class="um-detail-card um-profile-summary-card">
                    <div class="um-profile-identity">
                        <div class="um-detail-photo" id="detailAvatar">U</div>
                        <div>
                            <span class="um-profile-eyebrow">Account Profile</span>
                            <h4 class="um-profile-heading">User Information</h4>
                            <p class="um-profile-copy">Identity details synchronized with the selected account.</p>
                        </div>
                    </div>
                    <div class="um-profile-fields">
                        <div class="um-field">
                            <label>Name</label>
                            <input type="text" id="detailName" readonly>
                        </div>
                        <div class="um-field">
                            <label>Email</label>
                            <input type="text" id="detailEmail" readonly>
                        </div>
                        <div class="um-field">
                            <label id="detailIdentifierLabel">Student / Faculty ID</label>
                            <input type="text" id="detailIdentifier" readonly>
                        </div>
                        <div class="um-field">
                            <label>Source</label>
                            <input type="text" id="detailSource" readonly>
                        </div>
                        <div class="um-field">
                            <label>Original Role</label>
                            <input type="text" id="detailOriginalRole" readonly>
                        </div>
                        <div class="um-field">
                            <label>Last Updated</label>
                            <input type="text" id="detailUpdated" readonly>
                        </div>
                    </div>
                </div>
                <div class="um-detail-card um-settings-form-card">
                    <div class="um-settings-card-head">
                        <div>
                            <h4>Access Configuration</h4>
                            <p>Set the clinic role, account email, and access status.</p>
                        </div>
                        <span class="um-settings-card-badge">AA</span>
                    </div>
                    <div class="um-settings-form-body">
                    <form method="POST" id="settingsForm">
                        @csrf
                        <input type="hidden" name="_method" id="settingsMethod" value="PUT">
                        <input type="hidden" name="management_view" id="detailManagementView" value="account-access">
                        <input type="hidden" name="lookup_source" id="detailLookupSource" value="">
                        <input type="hidden" name="first_name" id="detailFirstName" value="">
                        <input type="hidden" name="last_name" id="detailLastName" value="">
                        <input type="hidden" name="full_name" id="detailFullName" value="">
                        <input type="hidden" name="external_identifier" id="detailExternalIdentifier" value="">
                        @include('admin.user_management.account-access-section')
                        <div class="um-note" id="externalNote" style="display:none; margin-top: 6px;">
                            This faculty profile comes from the external source. Saving here will add a clinic-side user and admin hub record without changing the source system.
                        </div>
                        <div class="um-module-save-warning" id="moduleAccessSaveWarning" hidden>
                            <strong>Module access is saved with this account.</strong>
                            <span>Changes take effect on the user&apos;s next request.</span>
                        </div>
                        <div class="um-actions">
                            <button type="button" class="um-settings-action um-action-neutral" id="deactivateBtn">Deactivate Account</button>
                            <button
                                type="submit"
                                form="deleteForm"
                                class="um-settings-action um-action-warning"
                                onclick="return confirm('Remove this clinic access and restore the account role provided by the IDP?')"
                            >
                                Remove Access
                            </button>
                            <button
                                type="submit"
                                form="deleteAccountForm"
                                class="um-settings-action um-action-danger"
                                id="deleteAccountBtn"
                                onclick="return confirm('Permanently delete this user account? Linked admin profile records will be removed and related clinic records may be deleted by database rules. This cannot be undone.')"
                            >
                                Delete User Account
                            </button>
                            <button
                                type="submit"
                                form="deleteAdminHubForm"
                                class="um-settings-action um-action-danger"
                                id="deleteAdminHubBtn"
                                style="display:none;"
                                onclick="return confirm('Delete this admin hub record from the admins table? This cannot be undone.')"
                            >
                                Delete Admin Record
                            </button>
                            <button type="submit" class="um-settings-action um-action-primary" id="saveSettingsBtn">Save Changes</button>
                        </div>
                    </form>

                    <form method="POST" id="deleteForm" style="margin-top: 10px;">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="management_view" id="deleteManagementView" value="account-access">
                        <input type="hidden" name="admin_profile_id" id="deleteAdminProfileId">
                    </form>

                    <form method="POST" id="deleteAccountForm" style="display:none;">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="management_view" id="deleteAccountManagementView" value="account-access">
                    </form>

                    <form method="POST" id="deleteAdminHubForm" style="display:none;">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="management_view" id="deleteAdminHubManagementView" value="account-access">
                    </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="um-cursor-hint" id="userHoverHint">Click to enter</div>

@push('scripts')
<script>
    const lookupModal = document.getElementById('lookupModal');
    const settingsModal = document.getElementById('settingsModal');
    const settingsForm = document.getElementById('settingsForm');
    const settingsMethod = document.getElementById('settingsMethod');
    const deleteForm = document.getElementById('deleteForm');
    const detailAvatar = document.getElementById('detailAvatar');
    const detailName = document.getElementById('detailName');
    const detailEmail = document.getElementById('detailEmail');
    const detailIdentifierLabel = document.getElementById('detailIdentifierLabel');
    const detailEditEmail = document.getElementById('detailEditEmail');
    const detailEmailLabel = document.getElementById('detailEmailLabel');
    const emailRoleNote = document.getElementById('emailRoleNote');
    const accountAccessSection = document.getElementById('accountAccessSection');
    const accessLevelWrap = document.getElementById('accessLevelWrap');
    const detailAccessLevel = document.getElementById('detailAccessLevel');
    const detailAccessLevelLabel = document.getElementById('detailAccessLevelLabel');
    const detailIdentifier = document.getElementById('detailIdentifier');
    const detailSource = document.getElementById('detailSource');
    const detailOriginalRole = document.getElementById('detailOriginalRole');
    const detailUpdated = document.getElementById('detailUpdated');
    const detailRole = document.getElementById('detailRole');
    const detailStatus = document.getElementById('detailStatus');
    const moduleAccessPreview = document.getElementById('moduleAccessPreview');
    const moduleAccessRoleSummary = document.getElementById('moduleAccessRoleSummary');
    const moduleAccessSelectionSummary = document.getElementById('moduleAccessSelectionSummary');
    const resetModuleDefaultsButton = document.getElementById('resetModuleDefaultsButton');
    const moduleAccessSaveWarning = document.getElementById('moduleAccessSaveWarning');
    const modulePermissionInputs = Array.from(document.querySelectorAll('[data-module-permission]'));
    const moduleAccessItems = Array.from(document.querySelectorAll('[data-module-item]'));
    const detailManagementView = document.getElementById('detailManagementView');
    const detailLookupSource = document.getElementById('detailLookupSource');
    const detailFirstName = document.getElementById('detailFirstName');
    const detailLastName = document.getElementById('detailLastName');
    const detailFullName = document.getElementById('detailFullName');
    const detailExternalIdentifier = document.getElementById('detailExternalIdentifier');
    const adminEmailWrap = document.getElementById('adminEmailWrap');
    const detailAdminEmail = document.getElementById('detailAdminEmail');
    const detailOffice = document.getElementById('detailOffice');
    const adminHubSection = document.getElementById('adminHubSection');
    const adminOfficeWrap = document.getElementById('adminOfficeWrap');
    const detailAdminProfileStatus = document.getElementById('detailAdminProfileStatus');
    const adminEmailNote = document.getElementById('adminEmailNote');
    const deleteAdminProfileId = document.getElementById('deleteAdminProfileId');
    const deleteManagementView = document.getElementById('deleteManagementView');
    const deleteAccountManagementView = document.getElementById('deleteAccountManagementView');
    const deleteAccountForm = document.getElementById('deleteAccountForm');
    const deleteAccountBtn = document.getElementById('deleteAccountBtn');
    const deleteAdminHubManagementView = document.getElementById('deleteAdminHubManagementView');
    const deleteAdminHubForm = document.getElementById('deleteAdminHubForm');
    const deleteAdminHubBtn = document.getElementById('deleteAdminHubBtn');
    const externalNote = document.getElementById('externalNote');
    const deactivateBtn = document.getElementById('deactivateBtn');
    const saveSettingsBtn = document.getElementById('saveSettingsBtn');
    const directoryPanel = document.getElementById('directoryPanel');
    const lookupDirectoryPanel = document.getElementById('lookupDirectoryPanel');
    const lookupSearchField = document.getElementById('lookupSearchField');
    const lookupManagementViewField = document.getElementById('lookupManagementViewField');
    const userHoverHint = document.getElementById('userHoverHint');
    const currentLookupContext = 'account-access';
    const lookupResultsBody = document.getElementById('lookupResultsBody');
    const lookupPagination = document.getElementById('lookupPagination');
    const lookupPaginationSummary = document.getElementById('lookupPaginationSummary');
    const lookupPrevPage = document.getElementById('lookupPrevPage');
    const lookupNextPage = document.getElementById('lookupNextPage');
    let lookupCurrentPage = 1;

    const updateLookupPagination = () => {
        if (!lookupResultsBody || !lookupPagination) {
            return;
        }

        const rows = Array.from(lookupResultsBody.querySelectorAll('[data-lookup-result-row]'));
        const pageSize = Math.max(1, parseInt(lookupPagination.dataset.pageSize || '8', 10));
        const totalPages = Math.max(1, Math.ceil(rows.length / pageSize));
        lookupCurrentPage = Math.min(Math.max(lookupCurrentPage, 1), totalPages);

        rows.forEach((row) => {
            row.style.display = 'none';
        });

        const startIndex = (lookupCurrentPage - 1) * pageSize;
        const endIndex = startIndex + pageSize;
        rows.slice(startIndex, endIndex).forEach((row) => {
            row.style.display = '';
        });

        lookupPagination.classList.toggle('is-visible', rows.length > pageSize);

        if (lookupPaginationSummary) {
            const showingStart = rows.length === 0 ? 0 : startIndex + 1;
            const showingEnd = Math.min(endIndex, rows.length);
            lookupPaginationSummary.textContent = `Showing ${showingStart}-${showingEnd} of ${rows.length}`;
        }

        if (lookupPrevPage) {
            lookupPrevPage.disabled = lookupCurrentPage <= 1;
        }
        if (lookupNextPage) {
            lookupNextPage.disabled = lookupCurrentPage >= totalPages;
        }
    };

    if (lookupPrevPage) {
        lookupPrevPage.addEventListener('click', () => {
            lookupCurrentPage -= 1;
            updateLookupPagination();
        });
    }

    if (lookupNextPage) {
        lookupNextPage.addEventListener('click', () => {
            lookupCurrentPage += 1;
            updateLookupPagination();
        });
    }

    updateLookupPagination();

    const applySettingsSectionMode = (managementView, canEdit, canOnboard) => {
        const isAdminHubOnly = managementView === 'admin-hub';
        if (settingsModal) {
            settingsModal.classList.toggle('admin-hub-mode', isAdminHubOnly);
            settingsModal.classList.toggle('account-access-mode', !isAdminHubOnly);
        }

        if (accountAccessSection) {
            accountAccessSection.classList.toggle('is-hidden', isAdminHubOnly);
            accountAccessSection.style.display = isAdminHubOnly ? 'none' : '';
        }

        if (adminHubSection) {
            adminHubSection.classList.remove('is-hidden');
            adminHubSection.style.display = (canEdit || canOnboard) ? '' : 'none';
        }
    };

    const setModuleExpanded = (item, expanded) => {
        const button = item?.querySelector('[data-module-expand]');
        const panel = item?.querySelector('[data-module-actions]');
        if (!button || !panel) {
            return;
        }

        button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        panel.hidden = !expanded;
    };

    const updateModuleAccessSummary = () => {
        if (!moduleAccessSelectionSummary) {
            return;
        }

        const selectedModules = modulePermissionInputs.filter((input) => input.checked).length;
        const selectedActions = moduleAccessItems.reduce((total, item) => {
            return total + Array.from(item.querySelectorAll('[data-module-action]'))
                .filter((input) => input.checked).length;
        }, 0);
        const moduleLabel = selectedModules === 1 ? 'module' : 'modules';
        const actionLabel = selectedActions === 1 ? 'additional permission' : 'additional permissions';

        moduleAccessSelectionSummary.textContent = `${selectedModules} ${moduleLabel} and ${selectedActions} ${actionLabel} selected`;
    };

    const syncModuleItem = (item, options = {}) => {
        const moduleInput = item.querySelector('[data-module-permission]');
        const actionInputs = Array.from(item.querySelectorAll('[data-module-action]'));
        const expandButton = item.querySelector('[data-module-expand]');
        const canConfigure = moduleAccessPreview?.dataset.canConfigure === '1';
        const hasModuleAccess = moduleInput?.checked === true;

        if (moduleInput) {
            moduleInput.disabled = !canConfigure;
        }

        if (options.resetActions === true) {
            actionInputs.forEach((input) => {
                input.checked = false;
            });
        }

        actionInputs.forEach((input) => {
            input.disabled = !canConfigure || !hasModuleAccess;
            if (!hasModuleAccess) {
                input.checked = false;
            }
        });

        if (expandButton) {
            expandButton.disabled = !canConfigure || !hasModuleAccess;
        }
        item.classList.toggle('is-disabled', !hasModuleAccess);

        if (!hasModuleAccess || options.collapse === true) {
            setModuleExpanded(item, false);
        }
    };

    const resetModuleAccessDefaults = (collapse = false) => {
        const roleKey = moduleAccessPreview?.dataset.role || '';
        if (roleKey === '') {
            return;
        }

        modulePermissionInputs.forEach((input) => {
            input.checked = input.dataset[roleKey] === '1';
        });
        moduleAccessItems.forEach((item) => {
            syncModuleItem(item, { resetActions: true, collapse });
        });
        updateModuleAccessSummary();
    };

    const syncRoleUi = (options = {}) => {
        const canEdit = options.canEdit === true;
        const canOnboard = options.canOnboard === true;
        const managementView = detailManagementView ? detailManagementView.value : 'account-access';
        const isStudent = false;
        const isStudentAssistant = detailRole.value === 'student_assistant';
        const isAdmin = detailRole.value === 'admin_clinic_staff';
        const isSuperAdmin = detailRole.value === 'super_admin';
        const hasAdminHub = isStudentAssistant || isAdmin || isSuperAdmin;
        const usesSeparateAdminEmail = managementView !== 'admin-hub' && isStudentAssistant;

        if (moduleAccessPreview) {
            const showsModuleAccess = managementView !== 'admin-hub' && (isStudentAssistant || isAdmin);
            moduleAccessPreview.hidden = !showsModuleAccess;

            if (showsModuleAccess) {
                const roleKey = isStudentAssistant ? 'studentAssistant' : 'clinicStaff';
                moduleAccessPreview.dataset.canConfigure = (canEdit || canOnboard) ? '1' : '0';
                if (moduleAccessRoleSummary) {
                    moduleAccessRoleSummary.textContent = isStudentAssistant
                        ? 'Suggested starting access for a Student Assistant.'
                        : 'Suggested starting access for Clinic Staff.';
                }

                const savedPermissions = Array.isArray(options.modulePermissions)
                    ? options.modulePermissions
                    : null;
                const shouldLoadDefaults = savedPermissions === null
                    && (options.resetModuleDefaults === true || moduleAccessPreview.dataset.role !== roleKey);

                if (savedPermissions !== null) {
                    moduleAccessItems.forEach((item) => item
                        .querySelectorAll('[data-module-permission], [data-module-action]')
                        .forEach((input) => {
                        input.checked = savedPermissions.includes(input.value);
                        }));
                } else if (shouldLoadDefaults) {
                    modulePermissionInputs.forEach((input) => {
                        input.checked = input.dataset[roleKey] === '1';
                    });
                }
                moduleAccessPreview.dataset.role = roleKey;
                moduleAccessItems.forEach((item) => {
                    syncModuleItem(item, {
                        resetActions: shouldLoadDefaults,
                        collapse: shouldLoadDefaults,
                    });
                });
                if (resetModuleDefaultsButton) {
                    resetModuleDefaultsButton.disabled = !(canEdit || canOnboard);
                }
                if (moduleAccessSaveWarning) {
                    moduleAccessSaveWarning.hidden = false;
                }
                updateModuleAccessSummary();
            } else {
                delete moduleAccessPreview.dataset.role;
                delete moduleAccessPreview.dataset.canConfigure;
                if (moduleAccessSaveWarning) {
                    moduleAccessSaveWarning.hidden = true;
                }
            }
        }

        applySettingsSectionMode(managementView, canEdit, canOnboard);

        if (isStudent) {
            if (detailEmailLabel) detailEmailLabel.textContent = 'Student Email';
            if (emailRoleNote) emailRoleNote.textContent = 'This email stays with the student account.';
            if (accessLevelWrap) accessLevelWrap.style.display = 'none';
            if (detailAccessLevel) detailAccessLevel.disabled = true;
            if (adminEmailWrap) adminEmailWrap.style.display = 'none';
            if (adminOfficeWrap) adminOfficeWrap.style.display = 'none';
        } else {
            if (detailEmailLabel) detailEmailLabel.textContent = 'Student Email';
            if (emailRoleNote) emailRoleNote.textContent = 'Keep this email for the student side.';
            if (accessLevelWrap) accessLevelWrap.style.display = 'none';
            if (detailAccessLevel) detailAccessLevel.disabled = true;
            if (adminEmailWrap) adminEmailWrap.style.display = usesSeparateAdminEmail ? 'block' : 'none';
            if (adminOfficeWrap) adminOfficeWrap.style.display = hasAdminHub ? 'block' : 'none';
        }

        if (adminEmailNote) {
            adminEmailNote.textContent = 'Use a separate login email only for Student Assistant accounts.';
        }

        if (detailAdminProfileStatus && !hasAdminHub) {
            detailAdminProfileStatus.textContent = 'Not needed while this account stays on the student side only.';
        }
    };

    const openSettingsFromRow = (row) => {
        if (!row) {
            return;
        }

        const canEdit = row.dataset.canEdit === '1';
        const canOnboard = row.dataset.canOnboard === '1';
        const avatarUrl = row.dataset.avatarUrl || '';
        const avatarLetter = row.dataset.avatarLetter || 'U';
        const managementView = row.dataset.managementView || currentLookupContext || 'account-access';
        if (detailManagementView) {
            detailManagementView.value = managementView;
        }
        if (deleteManagementView) {
            deleteManagementView.value = managementView;
        }
        if (deleteAccountManagementView) {
            deleteAccountManagementView.value = managementView;
        }
        if (deleteAdminHubManagementView) {
            deleteAdminHubManagementView.value = managementView;
        }

        detailName.value = row.dataset.name || '';
        detailEmail.value = row.dataset.email || '';
        detailIdentifier.value = row.dataset.studentId || 'N/A';
        if (detailIdentifierLabel) {
            detailIdentifierLabel.textContent = managementView === 'admin-hub' ? 'Faculty / External ID' : 'Student / Faculty ID';
        }
        detailSource.value = row.dataset.sourceLabel || row.dataset.source || '';
        detailUpdated.value = row.dataset.updated || 'N/A';
        const normalizedRole = (() => {
            const raw = (row.dataset.role || 'student').toLowerCase();
            const source = (row.dataset.source || '').toLowerCase();
            if (raw === 'superadmin' || raw === 'super_admin' || source === 'superadmin') {
                return 'super_admin';
            }
            if (source === 'student_assistant') {
                return 'student_assistant';
            }
            if (raw === 'student_assistant' || raw === 'studentassistant' || raw === 'assistant') {
                return 'student_assistant';
            }
            return 'admin_clinic_staff';
        })();
        detailRole.value = normalizedRole;
        detailStatus.value = row.dataset.status || 'active';
        const meta = (() => {
            try {
                return JSON.parse(row.dataset.meta || '{}') || {};
            } catch (error) {
                return {};
            }
        })();
        const savedModulePermissions = Array.isArray(meta.module_permissions)
            ? meta.module_permissions
            : null;
        const originalRole = String(meta.idp_role || '').trim();
        detailOriginalRole.value = originalRole
            ? originalRole.replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase())
            : 'N/A';
        if (detailIdentifierLabel && managementView !== 'admin-hub') {
            const normalizedOriginalRole = originalRole.toLowerCase();
            const normalizedSource = String(row.dataset.source || '').toLowerCase();
            detailIdentifierLabel.textContent = normalizedSource === 'student_assistant'
                ? 'Student Number'
                : (normalizedOriginalRole === 'faculty'
                    ? 'Faculty Code'
                    : (normalizedOriginalRole === 'admin' ? 'Admin ID' : 'Student Number'));
        }
        const accessLevel = (meta.access_level || '').toLowerCase();
        const adminLoginEmail = meta.admin_login_email || '';
        const office = meta.office || '';
        const adminProfileId = meta.admin_profile_id || '';
        const lookupSource = meta.lookup_source || '';
        if (deleteAdminProfileId) {
            deleteAdminProfileId.value = adminProfileId;
        }
        if (detailAccessLevel) {
            detailAccessLevel.value = ['clinic_staff', 'designee'].includes(accessLevel) ? accessLevel : 'clinic_staff';
        }
        applySettingsSectionMode(managementView, canEdit, canOnboard);

        detailEditEmail.value = row.dataset.email || '';
        if (detailEmailLabel) detailEmailLabel.textContent = 'Student Email';
        if (emailRoleNote) {
            emailRoleNote.textContent = normalizedRole === 'student'
                ? 'This email stays with the student account.'
                : 'Keep this email for the student side.';
        }
        if (detailAdminEmail) {
            detailAdminEmail.value = adminLoginEmail;
        }
        if (detailOffice) {
            detailOffice.value = office;
        }
        if (detailLookupSource) {
            detailLookupSource.value = lookupSource;
        }
        if (detailFirstName) {
            detailFirstName.value = row.dataset.firstName || '';
        }
        if (detailLastName) {
            detailLastName.value = row.dataset.lastName || '';
        }
        if (detailFullName) {
            detailFullName.value = row.dataset.name || '';
        }
        if (detailExternalIdentifier) {
            detailExternalIdentifier.value = row.dataset.studentId || row.dataset.id || '';
        }
        if (detailAdminProfileStatus) {
            detailAdminProfileStatus.textContent = adminProfileId
                ? `Linked to admin hub record #${adminProfileId}${meta.admin_profile_name ? ` | ${meta.admin_profile_name}` : ''}`
                : (managementView === 'admin-hub'
                    ? 'No linked admin hub record yet. Saving here will create the selected Admin Hub role.'
                    : 'No linked admin hub record yet. One will be created when you save an admin-side role.');
        }
        if (avatarUrl) {
            detailAvatar.innerHTML = `<img src="${avatarUrl}" alt="">`;
        } else {
            detailAvatar.textContent = avatarLetter;
        }

        settingsForm.action = canEdit ? (row.dataset.updateUrl || '#') : (row.dataset.createUrl || '#');
        if (settingsMethod) {
            settingsMethod.value = canEdit ? 'PUT' : 'POST';
        }
        deleteForm.action = row.dataset.deleteUrl || '#';
        if (deleteAccountForm) {
            deleteAccountForm.action = row.dataset.deleteAccountUrl || '#';
        }
        if (deleteAdminHubForm) {
            deleteAdminHubForm.action = row.dataset.deleteAdminHubUrl || '#';
        }

        settingsForm.querySelectorAll('input, select, button').forEach((field) => {
            if (field.id === 'deactivateBtn') {
                return;
            }
            if (field.id === 'settingsMethod' || field.id === 'detailLookupSource' || field.id === 'detailFirstName' || field.id === 'detailLastName' || field.id === 'detailFullName' || field.id === 'detailExternalIdentifier') {
                field.disabled = false;
                return;
            }
            field.disabled = !(canEdit || canOnboard);
        });
        deactivateBtn.disabled = !canEdit;
        deactivateBtn.style.display = canEdit ? '' : 'none';
        externalNote.style.display = canOnboard ? 'block' : 'none';
        detailEditEmail.readOnly = !(canEdit || canOnboard);

        deleteForm.style.display = canEdit ? 'block' : 'none';
        if (deleteAccountBtn) {
            deleteAccountBtn.style.display = canEdit ? '' : 'none';
            deleteAccountBtn.disabled = !canEdit;
        }
        if (deleteAccountForm) {
            deleteAccountForm.style.display = 'none';
        }
        if (deleteAdminHubBtn) {
            const showDeleteAdminHub = managementView === 'admin-hub' && canEdit && adminProfileId;
            deleteAdminHubBtn.style.display = showDeleteAdminHub ? '' : 'none';
            deleteAdminHubBtn.disabled = !showDeleteAdminHub;
        }
        if (deleteAdminHubForm) {
            deleteAdminHubForm.style.display = 'none';
        }
        if (!canEdit && canOnboard) {
            detailRole.value = 'admin_clinic_staff';
            if (detailAccessLevel) {
                detailAccessLevel.value = 'designee';
            }
            detailStatus.value = row.dataset.status || 'active';
        }
        syncRoleUi({
            canEdit,
            canOnboard,
            modulePermissions: savedModulePermissions,
            resetModuleDefaults: savedModulePermissions === null,
        });
        if (saveSettingsBtn) {
            saveSettingsBtn.textContent = canEdit ? 'Save Changes' : 'Add to Clinic';
        }
        settingsModal.classList.add('show');
    };

    if (lookupModal && lookupSearchField && lookupSearchField.value.trim() !== '') {
        lookupModal.classList.add('show');
    }

    document.querySelectorAll('[data-open-lookup]').forEach((button) => {
        button.addEventListener('click', () => {
            if (lookupManagementViewField) {
                lookupManagementViewField.value = currentLookupContext;
            }
            lookupModal.classList.add('show');
        });
    });

    document.querySelectorAll('[data-close-lookup]').forEach((button) => {
        button.addEventListener('click', () => lookupModal.classList.remove('show'));
    });

    document.querySelectorAll('[data-close-settings]').forEach((button) => {
        button.addEventListener('click', () => settingsModal.classList.remove('show'));
    });

    document.querySelectorAll('tr[data-user-card]').forEach((row) => {
        if (row.dataset.canEdit !== '1' && row.dataset.canOnboard !== '1') {
            return;
        }

        const moveHint = (event) => {
            if (!userHoverHint) {
                return;
            }
            userHoverHint.style.display = 'block';
            const offsetX = 18;
            const offsetY = 18;
            const maxX = window.innerWidth - userHoverHint.offsetWidth - 12;
            const maxY = window.innerHeight - userHoverHint.offsetHeight - 12;
            const left = Math.min(event.clientX + offsetX, maxX);
            const top = Math.min(event.clientY + offsetY, maxY);
            userHoverHint.style.left = `${Math.max(left, 12)}px`;
            userHoverHint.style.top = `${Math.max(top, 12)}px`;
        };

        row.addEventListener('mouseenter', moveHint);
        row.addEventListener('mousemove', moveHint);
        row.addEventListener('mouseleave', () => {
            if (userHoverHint) {
                userHoverHint.style.display = 'none';
            }
        });

        row.addEventListener('click', () => openSettingsFromRow(row));
    });

    moduleAccessItems.forEach((item) => {
        const moduleInput = item.querySelector('[data-module-permission]');
        const expandButton = item.querySelector('[data-module-expand]');

        moduleInput?.addEventListener('change', () => {
            syncModuleItem(item, { resetActions: moduleInput.checked, collapse: !moduleInput.checked });
            updateModuleAccessSummary();
        });

        item.querySelectorAll('[data-module-action]').forEach((input) => {
            input.addEventListener('change', updateModuleAccessSummary);
        });

        expandButton?.addEventListener('click', () => {
            if (expandButton.disabled) {
                return;
            }
            setModuleExpanded(item, expandButton.getAttribute('aria-expanded') !== 'true');
        });
    });

    resetModuleDefaultsButton?.addEventListener('click', () => {
        resetModuleAccessDefaults(false);
    });

    detailRole.addEventListener('change', () => {
        const canEdit = !deactivateBtn.disabled;
        const canOnboard = externalNote.style.display !== 'none';
        syncRoleUi({ canEdit, canOnboard, resetModuleDefaults: true });
    });

    deactivateBtn.addEventListener('click', () => {
        const confirmDeactivate = window.confirm('Deactivate this account? The user will lose access until reactivated.');
        if (!confirmDeactivate) {
            return;
        }
        detailStatus.value = 'inactive';
        settingsForm.submit();
    });

    document.getElementById('settingsModal').addEventListener('click', function (event) {
        if (event.target === this) {
            this.classList.remove('show');
        }
    });

    document.getElementById('lookupModal').addEventListener('click', function (event) {
        if (event.target === this) {
            this.classList.remove('show');
        }
    });
</script>
@include('admin.user_management.modal-ui-script')
@endpush
@endsection
