@extends('layouts.admin')

@section('title', 'Admin Hub Management')

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

    .um-table .um-office-cell {
        color: #475569;
        font-size: .74rem;
        font-weight: 750;
        line-height: 1.35;
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
        display: block !important;
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
@push('styles')
    @include('admin.user_management.access-console-styles')
    <style>
        .user-management-shell > .um-hero,
        #admin-hub-panel { display: none; }
    </style>
@endpush

@section('content')
<div class="user-management-shell">
    @php
        $hubTotal = count($adminHubRecords);
        $hubActive = collect($adminHubRecords)->where('status', 'active')->count();
        $hubOffices = collect($adminHubRecords)->filter(fn ($record) => trim((string) data_get($record, 'meta.office', '')) !== '')->count();
    @endphp
    <div class="access-console access-console--hub">
        <section class="access-console__hero">
            <div>
                <h1 class="access-console__hero-title">
                    <span class="access-console__hero-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" />
                        </svg>
                    </span>
                    Admin Hub Management
                </h1>
                <p class="access-console__hero-copy">Manage Admin Designee profiles, office assignments, and directory-linked access without changing source account information.</p>
            </div>
            <div class="access-console__sync">
                <span class="access-console__sync-icon"><x-outline-icon name="arrow-long-right" /></span>
                <div>
                    <span>API Sync Status</span>
                    <strong>Connected</strong>
                    <small>Shared directory profile sync is available</small>
                </div>
            </div>
        </section>

        <section class="access-console__stats" aria-label="Admin Hub summary">
            <article class="access-console__stat access-console__summary-card" data-summary-popup data-summary-title="Admin Hub Profiles" data-summary-value="{{ number_format($hubTotal) }}" data-summary-copy="Administrator profiles currently recorded in Admin Hub.">
                <span class="access-console__stat-icon"><x-outline-icon name="users" /></span>
                <span><span class="access-console__stat-label">Total Administrators</span><strong class="access-console__stat-value">{{ number_format($hubTotal) }}</strong><small class="access-console__stat-note">Admin Hub profiles</small></span>
            </article>
            <article class="access-console__stat access-console__summary-card" data-summary-popup data-summary-title="Active Accounts" data-summary-value="{{ number_format($hubActive) }}" data-summary-copy="Admin Designee accounts currently active.">
                <span class="access-console__stat-icon"><x-outline-icon name="user-plus" /></span>
                <span><span class="access-console__stat-label">Active Accounts</span><strong class="access-console__stat-value">{{ number_format($hubActive) }}</strong><small class="access-console__stat-note">Current active designees</small></span>
            </article>
            <article class="access-console__stat access-console__summary-card" data-summary-popup data-summary-title="Assigned Offices" data-summary-value="{{ number_format($hubOffices) }}" data-summary-copy="Admin Designee profiles currently assigned to an office.">
                <span class="access-console__stat-icon"><x-outline-icon name="briefcase" /></span>
                <span><span class="access-console__stat-label">Assigned Offices</span><strong class="access-console__stat-value">{{ number_format($hubOffices) }}</strong><small class="access-console__stat-note">With office assignment</small></span>
            </article>
            <article class="access-console__stat access-console__summary-card" data-summary-popup data-summary-title="API Sync Status" data-summary-value="Connected" data-summary-copy="The shared directory connection is available for Admin Hub profile synchronization.">
                <span class="access-console__stat-icon"><x-outline-icon name="arrow-long-right" /></span>
                <span><span class="access-console__stat-label">API Sync Status</span><strong class="access-console__stat-value" style="font-size:.9rem; color:#12733d;">Connected</strong><small class="access-console__stat-note">Shared records up to date</small></span>
            </article>
        </section>

        <section class="access-console__panel">
            <div class="access-console__filters">
                <label class="access-console__search">
                    <x-outline-icon name="magnifying-glass" />
                    <input type="search" placeholder="Search by name, email, or employee number" data-access-filter="search" aria-label="Search Admin Hub profiles">
                </label>
                <select class="access-console__filter" data-access-filter="office" aria-label="Filter by office">
                    <option value="">All Offices</option>
                    @foreach(collect($adminHubRecords)->map(fn ($record) => trim((string) data_get($record, 'meta.office', '')))->filter()->unique()->sort() as $office)
                        <option value="{{ strtolower($office) }}">{{ $office }}</option>
                    @endforeach
                </select>
                <select class="access-console__filter" data-access-filter="status" aria-label="Filter by status">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <button type="button" class="access-console__add" data-open-lookup="admin-hub">
                    <span aria-hidden="true">+</span> Add Admin Designee
                </button>
            </div>
            <div class="access-console__list" data-access-list>
                @forelse($adminHubRecords as $record)
                    @php
                        $office = trim((string) data_get($record, 'meta.office', ''));
                        $lastSynced = data_get($record, 'meta.updated_at');
                    @endphp
                    <article
                        class="access-console__row"
                        data-user-card
                        data-access-record
                        data-update-url="{{ $record['update_url'] ?? ($record['can_edit'] ? route('admin.user-management.update', $record['id']) : '') }}"
                        data-delete-url="{{ $record['delete_url'] ?? ($record['can_edit'] ? route('admin.user-management.destroy', $record['id']) : '') }}"
                        data-delete-admin-hub-url="{{ $record['delete_admin_hub_url'] ?? '' }}"
                        data-can-edit="{{ $record['can_edit'] ? '1' : '0' }}"
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
                        data-avatar-letter="{{ $record['avatar_letter'] }}"
                        data-updated="{{ $record['meta']['updated_at'] ?? '' }}"
                        data-management-view="admin-hub"
                        data-office="{{ strtolower($office) }}"
                        data-meta='@json($record["meta"])'
                    >
                        <span class="access-console__initial">{{ $record['avatar_letter'] }}</span>
                        <div class="access-console__person">
                            <span class="access-console__name">{{ $record['name'] }}</span>
                            <span class="access-console__email"><x-outline-icon name="envelope" />{{ $record['email'] ?: 'No email assigned' }}</span>
                        </div>
                        <div class="access-console__meta">
                            <span class="access-console__tag"><x-outline-icon name="briefcase" />{{ $office !== '' ? $office : 'Office unassigned' }}</span>
                            <span class="access-console__role"><x-outline-icon name="shield-check" />Admin Designee</span>
                        </div>
                        <div class="access-console__state {{ $record['status'] === 'inactive' ? 'access-console__state--inactive' : '' }}">
                            <strong>{{ ucfirst($record['status']) }}</strong>
                            <small>{{ $lastSynced ? 'Last synced ' . \Carbon\Carbon::parse($lastSynced)->format('M j, g:i A') : 'Not synced yet' }}</small>
                        </div>
                        <button type="button" class="access-console__manage">Manage</button>
                    </article>
                @empty
                    <div class="access-console__empty">No Admin Hub profiles are available yet.</div>
                @endforelse
            </div>
            <footer class="access-console__footer">
                <span data-access-result-count>Showing {{ $hubTotal }} administrator{{ $hubTotal === 1 ? '' : 's' }}</span>
                <span>Admin Hub directory</span>
            </footer>
        </section>
    </div>

    <div class="access-summary-modal" id="accessSummaryModal" aria-hidden="true">
        <section class="access-summary-modal__card" role="dialog" aria-modal="true" aria-labelledby="accessSummaryTitle">
            <div class="access-summary-modal__head">
                <div>
                    <p class="access-summary-modal__eyebrow">Admin Hub Summary</p>
                    <h2 class="access-summary-modal__title" id="accessSummaryTitle">Summary</h2>
                </div>
                <button type="button" class="access-summary-modal__close" data-close-summary-modal aria-label="Close summary popup">&times;</button>
            </div>
            <div class="access-summary-modal__value" id="accessSummaryValue"></div>
            <p class="access-summary-modal__copy" id="accessSummaryCopy"></p>
        </section>
    </div>

    <div class="um-hero">
        <div>
            <h1>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" />
                </svg>
                Admin Hub Management
            </h1>
            <p>Manage Admin Designee profiles, office assignments, and directory-linked access without changing source account information.</p>
        </div>
    </div>

    <div id="admin-hub-panel">
        <div class="um-card">
            <div class="um-panel-header">
                <div>
                    <h2>Admin Hub Management</h2>
                </div>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <a href="{{ route('admin.user-management') }}" class="um-btn um-btn-ghost">Back</a>
                    <button type="button" class="um-btn um-btn-primary" data-open-lookup="admin-hub">
                        <span>+</span> Add User Roles
                    </button>
                </div>
            </div>
            <div class="um-directory-panel is-open">
            <div class="um-table-wrap">
                <table class="um-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Admin Type</th>
                            <th>Office</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($adminHubRecords as $record)
                            <tr
                                data-user-card
                                data-update-url="{{ $record['update_url'] ?? ($record['can_edit'] ? route('admin.user-management.update', $record['id']) : '') }}"
                                data-delete-url="{{ $record['delete_url'] ?? ($record['can_edit'] ? route('admin.user-management.destroy', $record['id']) : '') }}"
                                data-delete-admin-hub-url="{{ $record['delete_admin_hub_url'] ?? '' }}"
                                data-can-edit="{{ $record['can_edit'] ? '1' : '0' }}"
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
                            data-management-view="admin-hub"
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
                                            <div class="um-sub">{{ $record['student_id'] ? 'ID: ' . $record['student_id'] : 'No ID yet' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $record['meta']['admin_login_email'] ?: 'Not assigned yet' }}</td>
                                <td>{{ $record['role'] }}</td>
                                <td class="um-office-cell">{{ $record['meta']['office'] ?: 'Not assigned yet' }}</td>
                                <td>
                                    <span class="um-badge {{ $record['status'] === 'inactive' ? 'inactive' : 'active' }}">
                                        {{ ucfirst($record['status']) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5"><div class="um-empty">No admin-hub-linked users are available yet.</div></td>
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
    <div class="um-modal-content access-onboard-modal">
        <div class="um-modal-head">
            <div class="um-modal-head-main">
                <div class="um-modal-head-badge">AR</div>
                <div>
                    <h3>Add Admin Designee</h3>
                    <div class="um-note">Select a faculty or directory profile and assign Admin Designee access.</div>
                </div>
            </div>
            <button type="button" class="um-modal-close" data-close-lookup aria-label="Close role lookup">&times;</button>
        </div>
        <div class="access-onboard-steps" aria-label="Add Admin Designee steps">
            <div class="access-onboard-step is-current"><span class="access-onboard-step__number">1</span><span><strong>Search User</strong><small>Find a faculty or directory profile</small></span></div>
            <div class="access-onboard-step"><span class="access-onboard-step__number">2</span><span><strong>Assign Access</strong><small>Admin Designee</small></span></div>
            <div class="access-onboard-step"><span class="access-onboard-step__number">3</span><span><strong>Confirm</strong><small>Review before saving</small></span></div>
        </div>
        <div class="um-modal-body">
            <div class="access-onboard-layout">
            <div class="access-onboard-search">
            <form class="um-search" method="GET" action="{{ route('admin.user-management.admin-hub') }}">
                <input type="hidden" name="management_view" value="{{ $managementView ?: 'admin-hub' }}" id="lookupManagementViewField">
                <input type="search" name="lookup_search" value="{{ $lookupSearch }}" placeholder="Search by name, email, or employee number" id="lookupSearchField">
                <button class="um-btn um-btn-primary" type="submit">Search</button>
            </form>
            <div class="access-onboard-filter-row">
                <select id="lookupSourceFilter" aria-label="Filter lookup source"><option value="">All Sources</option><option value="faculty">Faculty</option><option value="admin">Admin Hub</option></select>
                <select id="lookupStatusFilter" aria-label="Filter lookup status"><option value="">All Status</option><option value="active">Active</option><option value="inactive">Inactive</option></select>
            </div>
            <div class="access-onboard-count"><span data-lookup-result-count>{{ count($lookupRecords) }} result{{ count($lookupRecords) === 1 ? '' : 's' }} found</span><span>Employee number shown when available</span></div>
            <div style="margin-top: 16px;" class="um-directory-panel {{ $lookupSearch !== '' ? 'is-open' : '' }}" id="lookupDirectoryPanel">
            <div class="um-table-wrap">
                <table class="um-table" style="min-width: 900px;">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Source</th>
                        </tr>
                    </thead>
                    <tbody id="lookupResultsBody">
                        @forelse($lookupRecords as $record)
                            <tr
                                data-user-card
                                data-lookup-result-row
                                data-update-url="{{ $record['can_edit'] ? route('admin.user-management.update', $record['id']) : '' }}"
                                data-delete-url="{{ $record['can_edit'] ? route('admin.user-management.destroy', $record['id']) : '' }}"
                                data-delete-admin-hub-url="{{ $record['delete_admin_hub_url'] ?? '' }}"
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
                                            {{ $record['avatar_letter'] }}
                                        </div>
                                        <div>
                                            <div class="um-name">{{ $record['name'] }}</div>
                                            @php($employeeNumber = trim((string) ($record['meta']['employee_number'] ?? $record['meta']['faculty_identifier'] ?? '')))
                                            <div class="um-sub">{{ $employeeNumber !== '' ? $employeeNumber : 'Employee number not available' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $record['email'] ?: 'N/A' }}</td>
                                <td>{{ $record['role'] }}</td>
                                <td><span class="um-badge {{ $record['status'] === 'inactive' ? 'inactive' : 'active' }}">{{ ucfirst($record['status']) }}</span></td>
                                <td><span class="um-badge source">{{ $record['source_label'] }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5"><div class="um-empty">No Users matched the current search.</div></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            </div>
            </div>
            <aside class="access-onboard-profile" id="lookupSelectedProfile">
                <p class="access-onboard-profile__eyebrow">Selected Profile</p>
                <div class="access-onboard-profile__empty">No User Selected Yet</div>
                <div class="access-onboard-profile__identity">
                    <span class="access-onboard-profile__avatar" id="lookupSelectedAvatar">U</span>
                    <div><div class="access-onboard-profile__name" id="lookupSelectedName"></div><div class="access-onboard-profile__email" id="lookupSelectedEmail"></div></div>
                </div>
                <div class="access-onboard-profile__details">
                    <span>Employee Number<strong id="lookupSelectedIdentifier">Not available</strong></span>
                    <span>Source System<strong id="lookupSelectedSource">Not available</strong></span>
                </div>
                <div class="access-onboard-profile__access">
                    <p class="access-onboard-role-title">Assign Admin Hub Access</p>
                    <label class="access-onboard-role-option">
                        <input type="radio" name="lookup_role" value="admin_designee" checked>
                        <span><strong>Admin Designee</strong><small>Centralized directory access without clinic module permissions</small></span>
                    </label>
                </div>
            </aside>
            </div>
        </div>
            <footer class="access-onboard-footer">
                <small>Step 1 of 3</small>
                <span class="access-onboard-footer__actions"><button type="button" class="access-onboard-cancel" data-close-lookup>Cancel</button><button type="button" class="access-onboard-continue" id="onboardContinue" disabled>Continue</button></span>
            </footer>
    </div>
</div>

<div class="um-modal-backdrop" id="settingsModal">
    <div class="um-modal-content um-settings-console">
        <div class="um-modal-head">
            <div class="um-modal-head-main">
                <div class="um-modal-head-badge">AH</div>
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
                            <span class="um-profile-eyebrow">Admin Profile</span>
                            <h4 class="um-profile-heading">User Information</h4>
                            <p class="um-profile-copy">Identity details linked to the Admin Hub account.</p>
                            <span class="um-profile-verified"><x-outline-icon name="check" />Verified</span>
                        </div>
                    </div>
                    <div class="um-profile-fields">
                        <div class="um-field">
                            <span class="um-profile-field-icon"><x-outline-icon name="user-circle" /></span>
                            <label>Name</label>
                            <input type="text" id="detailName" readonly>
                        </div>
                        <div class="um-field">
                            <span class="um-profile-field-icon"><x-outline-icon name="envelope" /></span>
                            <label>Email</label>
                            <input type="text" id="detailEmail" readonly>
                        </div>
                        <div class="um-field">
                            <span class="um-profile-field-icon"><x-outline-icon name="identification" /></span>
                            <label id="detailIdentifierLabel">ID Number</label>
                            <input type="text" id="detailIdentifier" readonly>
                        </div>
                        <div class="um-field">
                            <span class="um-profile-field-icon"><x-outline-icon name="calendar-days" /></span>
                            <label>DOB</label>
                            <input type="text" id="detailDob" readonly>
                        </div>
                        <div class="um-field">
                            <span class="um-profile-field-icon"><x-outline-icon name="phone" /></span>
                            <label>Number</label>
                            <input type="text" id="detailContactNo" readonly>
                        </div>
                        <div class="um-field">
                            <span class="um-profile-field-icon"><x-outline-icon name="home" /></span>
                            <label>Address</label>
                            <input type="text" id="detailAddress" readonly>
                        </div>
                        <div class="um-field">
                            <span class="um-profile-field-icon"><x-outline-icon name="briefcase" /></span>
                            <label>Source</label>
                            <input type="text" id="detailSource" readonly>
                        </div>
                        <div class="um-field">
                            <span class="um-profile-field-icon"><x-outline-icon name="clock" /></span>
                            <label>Last Updated</label>
                            <input type="text" id="detailUpdated" readonly>
                        </div>
                    </div>
                </div>
                <div class="um-detail-card um-settings-form-card">
                    <div class="um-settings-card-head">
                        <div>
                            <h4><span class="um-settings-title-icon"><x-outline-icon name="shield-check" /></span>Admin Hub Configuration</h4>
                            <p>Manage the shared directory role, department or office, and status.</p>
                        </div>
                        <span class="um-settings-card-badge">AH</span>
                    </div>
                    <div class="um-settings-form-body">
                    <form method="POST" id="settingsForm">
                        @csrf
                        <input type="hidden" name="_method" id="settingsMethod" value="PUT">
                        <input type="hidden" name="management_view" id="detailManagementView" value="admin-hub">
                        <input type="hidden" name="lookup_source" id="detailLookupSource" value="">
                        <input type="hidden" name="first_name" id="detailFirstName" value="">
                        <input type="hidden" name="last_name" id="detailLastName" value="">
                        <input type="hidden" name="full_name" id="detailFullName" value="">
                        <input type="hidden" name="admin_uuid" id="detailAdminUuid" value="">
                        <input type="hidden" name="employee_number" id="detailEmployeeNumber" value="">
                        <input type="hidden" name="birthday" id="detailBirthday" value="">
                        <input type="hidden" name="age" id="detailAge" value="">
                        <input type="hidden" name="gender" id="detailGenderValue" value="">
                        <input type="hidden" name="civil_status" id="detailCivilStatus" value="">
                        <input type="hidden" name="address" id="detailAddressValue" value="">
                        <input type="hidden" name="emergency_contact_person" id="detailEmergencyContactPerson" value="">
                        <input type="hidden" name="emergency_contact_no" id="detailEmergencyContactNo" value="">
                        @include('admin.user_management.account-access-section')
                        <div class="um-note" id="externalNote" style="display:none; margin-top: 6px;">
                            This profile comes from an external source. Saving here adds it to the Admin Hub and links an existing clinic account when available without changing the source system.
                        </div>
                    </form>

                    <form method="POST" id="deleteForm" style="margin-top: 10px;">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="management_view" id="deleteManagementView" value="admin-hub">
                        <input type="hidden" name="admin_profile_id" id="deleteAdminProfileId">
                    </form>

                    <form method="POST" id="deleteAdminHubForm" style="display:none;">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="management_view" id="deleteAdminHubManagementView" value="admin-hub">
                    </form>
                    </div>
                    <div class="um-actions um-settings-actions-footer">
                        <button type="button" class="um-settings-action um-action-neutral" id="deactivateBtn">Deactivate Account</button>
                        <button
                            type="submit"
                            form="deleteForm"
                            class="um-settings-action um-action-warning"
                            onclick="return confirm('Remove only the Admin Designee role and restore the linked account to its base role?')"
                        >
                            Remove Admin Designee Role
                        </button>
                        <button
                            type="submit"
                            form="deleteAdminHubForm"
                            class="um-settings-action um-action-danger"
                            id="deleteAdminHubBtn"
                            style="display:none;"
                            onclick="return confirm('Delete this standalone directory record? Linked clinic accounts will be preserved and only removed from the Admin Hub.')"
                        >
                            Delete Directory Record
                        </button>
                        <button type="submit" form="settingsForm" class="um-settings-action um-action-primary" id="saveSettingsBtn">Save Changes</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
    const detailDob = document.getElementById('detailDob');
    const detailContactNo = document.getElementById('detailContactNo');
    const detailAddress = document.getElementById('detailAddress');
    const detailSource = document.getElementById('detailSource');
    const detailUpdated = document.getElementById('detailUpdated');
    const detailRole = document.getElementById('detailRole');
    const detailStatus = document.getElementById('detailStatus');
    const detailManagementView = document.getElementById('detailManagementView');
    const detailLookupSource = document.getElementById('detailLookupSource');
    const detailFirstName = document.getElementById('detailFirstName');
    const detailLastName = document.getElementById('detailLastName');
    const detailFullName = document.getElementById('detailFullName');
    const detailAdminUuid = document.getElementById('detailAdminUuid');
    const detailEmployeeNumber = document.getElementById('detailEmployeeNumber');
    const detailBirthday = document.getElementById('detailBirthday');
    const detailAge = document.getElementById('detailAge');
    const detailGenderValue = document.getElementById('detailGenderValue');
    const detailCivilStatus = document.getElementById('detailCivilStatus');
    const detailAddressValue = document.getElementById('detailAddressValue');
    const detailEmergencyContactPerson = document.getElementById('detailEmergencyContactPerson');
    const detailEmergencyContactNo = document.getElementById('detailEmergencyContactNo');
    const adminEmailWrap = document.getElementById('adminEmailWrap');
    const detailAdminEmail = document.getElementById('detailAdminEmail');
    const detailOffice = document.getElementById('detailOffice');
    const adminHubSection = document.getElementById('adminHubSection');
    const adminOfficeWrap = document.getElementById('adminOfficeWrap');
    const detailAdminProfileStatus = document.getElementById('detailAdminProfileStatus');
    const adminEmailNote = document.getElementById('adminEmailNote');
    const deleteAdminProfileId = document.getElementById('deleteAdminProfileId');
    const deleteManagementView = document.getElementById('deleteManagementView');
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
    const currentLookupContext = 'admin-hub';
    const lookupSelectedProfile = document.getElementById('lookupSelectedProfile');
    const lookupSelectedAvatar = document.getElementById('lookupSelectedAvatar');
    const lookupSelectedName = document.getElementById('lookupSelectedName');
    const lookupSelectedEmail = document.getElementById('lookupSelectedEmail');
    const lookupSelectedIdentifier = document.getElementById('lookupSelectedIdentifier');
    const lookupSelectedSource = document.getElementById('lookupSelectedSource');
    const lookupSourceFilter = document.getElementById('lookupSourceFilter');
    const lookupStatusFilter = document.getElementById('lookupStatusFilter');
    const lookupRoleFilter = document.getElementById('lookupRoleFilter');
    const lookupResultCount = document.querySelector('[data-lookup-result-count]');
    const onboardContinue = document.getElementById('onboardContinue');
    let selectedLookupRow = null;

    const applySettingsSectionMode = (managementView, canEdit, canOnboard) => {
        const isAdminHubOnly = managementView === 'admin-hub';
        if (settingsModal) {
            settingsModal.classList.toggle('admin-hub-mode', isAdminHubOnly);
            settingsModal.classList.toggle('account-access-mode', !isAdminHubOnly);
        }

        if (accountAccessSection) {
            accountAccessSection.classList.remove('is-hidden');
            accountAccessSection.style.display = '';
        }

        if (adminHubSection) {
            adminHubSection.classList.remove('is-hidden');
            adminHubSection.style.display = (canEdit || canOnboard) ? '' : 'none';
        }
    };

    const syncRoleUi = (options = {}) => {
        const canEdit = options.canEdit === true;
        const canOnboard = options.canOnboard === true;
        const managementView = detailManagementView ? detailManagementView.value : 'account-access';
        const isStudent = false;
        const isStudentAssistant = detailRole.value === 'student_assistant';
        const isAdmin = detailRole.value === 'admin_designee';
        const isSuperAdmin = detailRole.value === 'super_admin';
        const hasAdminHub = isStudentAssistant || isAdmin || isSuperAdmin;
        const usesSeparateAdminEmail = managementView !== 'admin-hub' && isStudentAssistant;

        applySettingsSectionMode(managementView, canEdit, canOnboard);

        if (isStudent) {
            if (detailEmailLabel) detailEmailLabel.textContent = 'Directory Email';
            if (emailRoleNote) emailRoleNote.textContent = 'This email is shared as part of the Admin Hub profile.';
            if (accessLevelWrap) accessLevelWrap.style.display = 'none';
            if (detailAccessLevel) detailAccessLevel.disabled = true;
            if (adminEmailWrap) adminEmailWrap.style.display = 'none';
            if (adminOfficeWrap) adminOfficeWrap.style.display = 'none';
        } else {
            if (detailEmailLabel) detailEmailLabel.textContent = 'Directory Email';
            if (emailRoleNote) emailRoleNote.textContent = 'This email is shared as part of the Admin Hub profile.';
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
        if (deleteAdminHubManagementView) {
            deleteAdminHubManagementView.value = managementView;
        }

        const meta = (() => {
            try {
                return JSON.parse(row.dataset.meta || '{}') || {};
            } catch (error) {
                return {};
            }
        })();
        const displayValue = (value) => {
            const normalized = String(value || '').trim();
            return normalized !== '' ? normalized : 'N/A';
        };

        detailName.value = row.dataset.name || '';
        detailEmail.value = row.dataset.email || '';
        detailIdentifier.value = displayValue(meta.employee_number || meta.faculty_identifier);
        if (detailIdentifierLabel) {
            detailIdentifierLabel.textContent = 'Employee Number';
        }
        if (detailDob) {
            detailDob.value = displayValue(meta.DOB || meta.birthday);
        }
        if (detailContactNo) {
            detailContactNo.value = displayValue(meta.contact_no || meta.emergency_contact_no);
        }
        if (detailAddress) {
            detailAddress.value = displayValue(meta.address || meta.home_address);
        }
        detailSource.value = row.dataset.sourceLabel || row.dataset.source || '';
        detailUpdated.value = row.dataset.updated || 'N/A';
        const normalizedRole = (() => {
            if (managementView === 'admin-hub') {
                return 'admin_designee';
            }
            const raw = (row.dataset.role || 'student').toLowerCase();
            const source = (row.dataset.source || '').toLowerCase();
            if (source === 'student_assistant') {
                return 'student_assistant';
            }
            if (raw === 'superadmin' || raw === 'super_admin') {
                return 'super_admin';
            }
            return 'admin_designee';
        })();
        detailRole.value = normalizedRole;
        detailStatus.value = row.dataset.status || 'active';
        const accessLevel = (meta.access_level || '').toLowerCase();
        const adminLoginEmail = meta.admin_login_email || '';
        const office = meta.office || '';
        const adminProfileId = meta.admin_profile_id || '';
        const adminHubProfileId = meta.admin_hub_profile_id || '';
        const adminHubProfileName = meta.admin_hub_profile_name || '';
        const lookupSource = meta.lookup_source || '';
        if (deleteAdminProfileId) {
            deleteAdminProfileId.value = adminProfileId;
        }
        if (detailAccessLevel) {
            detailAccessLevel.value = ['clinic_staff', 'designee'].includes(accessLevel) ? accessLevel : 'clinic_staff';
        }
        applySettingsSectionMode(managementView, canEdit, canOnboard);

        detailEditEmail.value = row.dataset.email || '';
        if (detailEmailLabel) detailEmailLabel.textContent = 'Directory Email';
        if (emailRoleNote) emailRoleNote.textContent = 'This email is shared as part of the Admin Hub profile.';
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
        if (detailAdminUuid) {
            detailAdminUuid.value = meta.admin_uuid || '';
        }
        if (detailEmployeeNumber) {
            detailEmployeeNumber.value = meta.employee_number || meta.faculty_identifier || '';
        }
        if (detailBirthday) {
            detailBirthday.value = meta.birthday || meta.DOB || '';
        }
        if (detailAge) {
            detailAge.value = meta.age || '';
        }
        if (detailGenderValue) {
            detailGenderValue.value = meta.gender || '';
        }
        if (detailCivilStatus) {
            detailCivilStatus.value = meta.civil_status || '';
        }
        if (detailAddressValue) {
            detailAddressValue.value = meta.address || '';
        }
        if (detailEmergencyContactPerson) {
            detailEmergencyContactPerson.value = meta.emergency_contact_person || '';
        }
        if (detailEmergencyContactNo) {
            detailEmergencyContactNo.value = meta.emergency_contact_no || meta.contact_no || '';
        }
        if (detailAdminProfileStatus) {
            detailAdminProfileStatus.textContent = adminHubProfileId
                ? `Linked to Admin Hub${adminHubProfileName ? ` | ${adminHubProfileName}` : ''}`
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
        if (deleteAdminHubForm) {
            deleteAdminHubForm.action = row.dataset.deleteAdminHubUrl || '#';
        }

        settingsForm.querySelectorAll('input, select, button').forEach((field) => {
            if (field.id === 'deactivateBtn') {
                return;
            }
            if (['settingsMethod', 'detailLookupSource', 'detailFirstName', 'detailLastName', 'detailFullName', 'detailAdminUuid', 'detailEmployeeNumber', 'detailBirthday', 'detailAge', 'detailGenderValue', 'detailCivilStatus', 'detailAddressValue', 'detailEmergencyContactPerson', 'detailEmergencyContactNo'].includes(field.id)) {
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
        if (deleteAdminHubBtn) {
            const showDeleteAdminHub = managementView === 'admin-hub' && canEdit && adminHubProfileId;
            deleteAdminHubBtn.style.display = showDeleteAdminHub ? '' : 'none';
            deleteAdminHubBtn.disabled = !showDeleteAdminHub;
        }
        if (deleteAdminHubForm) {
            deleteAdminHubForm.style.display = 'none';
        }
        if (!canEdit && canOnboard) {
            detailRole.value = 'admin_designee';
            if (detailAccessLevel) {
                detailAccessLevel.value = 'designee';
            }
            detailStatus.value = row.dataset.status || 'active';
        }
        syncRoleUi({ canEdit, canOnboard });
        if (saveSettingsBtn) {
            saveSettingsBtn.textContent = canEdit ? 'Save Changes' : 'Add to Admin Hub';
        }
        settingsModal.classList.add('show');
    };

    const lookupRows = () => Array.from(document.querySelectorAll('[data-lookup-result-row]'));
    const getLookupMeta = (row) => {
        try {
            return JSON.parse(row.dataset.meta || '{}');
        } catch (error) {
            return {};
        }
    };
    const resetLookupSelection = () => {
        selectedLookupRow = null;
        lookupRows().forEach((row) => row.classList.remove('is-selected'));
        lookupSelectedProfile?.classList.remove('has-selection');
        if (onboardContinue) onboardContinue.disabled = true;
    };
    const selectLookupProfile = (row) => {
        if (!row || (row.dataset.canEdit !== '1' && row.dataset.canOnboard !== '1')) {
            return;
        }

        const meta = getLookupMeta(row);
        const identifier = meta.employee_number || meta.faculty_identifier || meta.employee_id || 'Not available';
        selectedLookupRow = row;
        lookupRows().forEach((item) => item.classList.toggle('is-selected', item === row));
        lookupSelectedProfile?.classList.add('has-selection');
        if (lookupSelectedAvatar) lookupSelectedAvatar.textContent = row.dataset.avatarLetter || 'U';
        if (lookupSelectedName) lookupSelectedName.textContent = row.dataset.name || 'Unnamed user';
        if (lookupSelectedEmail) lookupSelectedEmail.textContent = row.dataset.email || 'No email assigned';
        if (lookupSelectedIdentifier) lookupSelectedIdentifier.textContent = identifier;
        if (lookupSelectedSource) lookupSelectedSource.textContent = row.dataset.sourceLabel || row.dataset.source || 'Not available';
        if (onboardContinue) onboardContinue.disabled = false;
    };
    const applyLookupFilters = () => {
        const source = (lookupSourceFilter?.value || '').toLowerCase();
        const status = (lookupStatusFilter?.value || '').toLowerCase();
        const role = (lookupRoleFilter?.value || '').toLowerCase();
        let count = 0;

        lookupRows().forEach((row) => {
            const rowSource = (row.dataset.source || '').toLowerCase();
            const rowStatus = (row.dataset.status || '').toLowerCase();
            const rowRole = (row.dataset.roleLabel || row.dataset.role || '').toLowerCase();
            const matches = (!source || rowSource.includes(source))
                && (!status || rowStatus === status)
                && (!role || rowRole === role);
            row.hidden = !matches;
            if (matches) count += 1;
        });

        if (lookupResultCount) {
            lookupResultCount.textContent = `${count} result${count === 1 ? '' : 's'} found`;
        }
    };

    [lookupSourceFilter, lookupStatusFilter, lookupRoleFilter].filter(Boolean).forEach((filter) => {
        filter.addEventListener('change', applyLookupFilters);
    });

    document.querySelectorAll('[data-lookup-result-row]').forEach((row) => {
        row.addEventListener('click', () => selectLookupProfile(row));
    });

    onboardContinue?.addEventListener('click', () => {
        if (!selectedLookupRow) return;

        const selectedRole = document.querySelector('input[name="lookup_role"]:checked')?.value;
        lookupModal.classList.remove('show');
        openSettingsFromRow(selectedLookupRow);
        if (selectedRole && detailRole) {
            detailRole.value = selectedRole;
            detailRole.dispatchEvent(new Event('change'));
        }
    });

    if (lookupModal && lookupSearchField && lookupSearchField.value.trim() !== '') {
        lookupModal.classList.add('show');
    }

    document.querySelectorAll('[data-open-lookup]').forEach((button) => {
        button.addEventListener('click', () => {
            if (lookupManagementViewField) {
                lookupManagementViewField.value = currentLookupContext;
            }
            resetLookupSelection();
            lookupModal.classList.add('show');
        });
    });

    document.querySelectorAll('[data-close-lookup]').forEach((button) => {
        button.addEventListener('click', () => {
            lookupModal.classList.remove('show');
            resetLookupSelection();
        });
    });

    document.querySelectorAll('[data-close-settings]').forEach((button) => {
        button.addEventListener('click', () => settingsModal.classList.remove('show'));
    });

    document.querySelectorAll('[data-user-card]:not([data-lookup-result-row])').forEach((row) => {
        if (row.dataset.canEdit !== '1' && row.dataset.canOnboard !== '1') {
            return;
        }

        row.addEventListener('click', () => openSettingsFromRow(row));
    });

    detailRole.addEventListener('change', () => {
        const canEdit = !deactivateBtn.disabled;
        const canOnboard = externalNote.style.display !== 'none';
        syncRoleUi({ canEdit, canOnboard });
    });

    deactivateBtn.addEventListener('click', () => {
        const confirmDeactivate = window.confirm('Deactivate this account as resigned/inactive? This restores the base role, revokes access tokens, and preserves audit history.');
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

    const accessSummaryModal = document.getElementById('accessSummaryModal');
    const accessSummaryTitle = document.getElementById('accessSummaryTitle');
    const accessSummaryValue = document.getElementById('accessSummaryValue');
    const accessSummaryCopy = document.getElementById('accessSummaryCopy');
    const closeAccessSummaryModal = () => {
        if (!accessSummaryModal) return;
        accessSummaryModal.classList.remove('is-open');
        accessSummaryModal.setAttribute('aria-hidden', 'true');
    };

    document.querySelectorAll('[data-summary-popup]').forEach((card) => {
        card.addEventListener('click', () => {
            if (!accessSummaryModal) return;
            accessSummaryTitle.textContent = card.dataset.summaryTitle || 'Summary';
            accessSummaryValue.textContent = card.dataset.summaryValue || '';
            accessSummaryCopy.textContent = card.dataset.summaryCopy || '';
            accessSummaryModal.classList.add('is-open');
            accessSummaryModal.setAttribute('aria-hidden', 'false');
        });
    });
    document.querySelectorAll('[data-close-summary-modal]').forEach((button) => button.addEventListener('click', closeAccessSummaryModal));
    accessSummaryModal?.addEventListener('click', (event) => {
        if (event.target === accessSummaryModal) closeAccessSummaryModal();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && accessSummaryModal?.classList.contains('is-open')) closeAccessSummaryModal();
    });
</script>
@include('admin.user_management.modal-ui-script')
@endpush
@endsection
