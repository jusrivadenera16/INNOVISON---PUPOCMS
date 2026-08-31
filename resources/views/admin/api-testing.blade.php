@extends('layouts.admin')

@section('title', 'API Dashboard')
@section('disable_voice_inputs', 'true')

@section('content')
<style>
    .api-testing-shell {
        display: grid;
        gap: 20px;
    }

    .guisis-sync-card {
        border-color: rgba(127, 29, 45, 0.18);
        background: linear-gradient(135deg, rgba(255, 250, 247, 0.98), rgba(255, 244, 239, 0.98));
    }

    html[data-theme="dark"] .guisis-sync-card {
        border-color: rgba(245, 190, 83, 0.22);
        background: linear-gradient(135deg, rgba(49, 23, 31, 0.98), rgba(35, 17, 25, 0.98));
    }

    .guisis-sync-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
    }

    .guisis-sync-head h3 {
        margin: 0 0 6px;
        color: #7f1d2d;
        font-size: 19px;
        font-weight: 900;
    }

    html[data-theme="dark"] .guisis-sync-head h3 {
        color: #f5d27a;
    }

    .guisis-sync-head p,
    .guisis-sync-note {
        margin: 0;
        color: #64748b;
        font-size: 13px;
        line-height: 1.55;
    }

    html[data-theme="dark"] .guisis-sync-head p,
    html[data-theme="dark"] .guisis-sync-note {
        color: #cbd5e1;
    }

    .guisis-sync-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 10px;
    }

    .guisis-sync-actions form {
        margin: 0;
    }

    .guisis-sync-button {
        min-height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 9px 14px;
        border: 1px solid rgba(127, 29, 45, 0.22);
        border-radius: 9px;
        background: transparent;
        color: #7f1d2d;
        font-size: 12px;
        font-weight: 850;
        cursor: pointer;
    }

    .guisis-sync-button:hover,
    .guisis-sync-button:focus-visible {
        border-color: #7f1d2d;
        background: rgba(127, 29, 45, 0.07);
    }

    .guisis-sync-button.is-primary {
        border-color: #7f1d2d;
        background: #7f1d2d;
        color: #ffffff;
    }

    .guisis-sync-button.is-primary:hover,
    .guisis-sync-button.is-primary:focus-visible {
        background: #641523;
    }

    html[data-theme="dark"] .guisis-sync-button {
        border-color: rgba(245, 210, 122, 0.35);
        color: #f5d27a;
    }

    html[data-theme="dark"] .guisis-sync-button:hover,
    html[data-theme="dark"] .guisis-sync-button:focus-visible {
        background: rgba(245, 210, 122, 0.08);
    }

    html[data-theme="dark"] .guisis-sync-button.is-primary {
        border-color: #f5d27a;
        background: #f5d27a;
        color: #35121a;
    }

    .guisis-sync-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(118px, 1fr));
        gap: 8px;
        margin-top: 16px;
    }

    .guisis-sync-stat {
        padding: 10px 12px;
        border: 1px solid rgba(127, 29, 45, 0.12);
        border-radius: 9px;
        background: rgba(255, 255, 255, 0.58);
    }

    html[data-theme="dark"] .guisis-sync-stat {
        border-color: rgba(255, 255, 255, 0.08);
        background: rgba(0, 0, 0, 0.16);
    }

    .guisis-sync-stat strong,
    .guisis-sync-stat span {
        display: block;
    }

    .guisis-sync-stat strong {
        color: #7f1d2d;
        font-size: 18px;
        line-height: 1.1;
    }

    html[data-theme="dark"] .guisis-sync-stat strong {
        color: #f5d27a;
    }

    .guisis-sync-stat span {
        margin-top: 4px;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
    }

    html[data-theme="dark"] .guisis-sync-stat span {
        color: #cbd5e1;
    }

    .guisis-sync-result {
        margin-top: 14px;
        padding: 12px 14px;
        border: 1px solid rgba(127, 29, 45, 0.14);
        border-radius: 9px;
        background: rgba(255, 255, 255, 0.66);
    }

    html[data-theme="dark"] .guisis-sync-result {
        border-color: rgba(255, 255, 255, 0.08);
        background: rgba(0, 0, 0, 0.16);
    }

    .guisis-sync-result-list {
        display: grid;
        gap: 5px;
        max-height: 190px;
        margin-top: 9px;
        overflow: auto;
    }

    .guisis-sync-result-item {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 7px 0;
        border-bottom: 1px solid rgba(127, 29, 45, 0.08);
        color: #334155;
        font-size: 12px;
    }

    html[data-theme="dark"] .guisis-sync-result-item {
        border-bottom-color: rgba(255, 255, 255, 0.07);
        color: #e2e8f0;
    }

    .guisis-sync-result-item strong {
        color: #7f1d2d;
        text-transform: uppercase;
        white-space: nowrap;
    }

    html[data-theme="dark"] .guisis-sync-result-item strong {
        color: #f5d27a;
    }

    @media (max-width: 760px) {
        .guisis-sync-head {
            display: grid;
        }

        .guisis-sync-actions {
            justify-content: flex-start;
        }
    }

    .api-tabs {
        display: flex;
        gap: 0;
        border-bottom: 2px solid rgba(127, 29, 45, 0.1);
        margin-bottom: 20px;
    }

    .api-tab-button {
        flex: 1;
        padding: 16px 20px;
        border: none;
        background: transparent;
        color: #6b7280;
        font-weight: 700;
        cursor: pointer;
        border-bottom: 3px solid transparent;
        transition: all 0.2s ease;
        position: relative;
        bottom: -2px;
    }

    .api-tab-button:hover {
        background: rgba(127, 29, 45, 0.04);
        color: #7f1d2d;
    }

    .api-tab-button.is-active {
        color: #7f1d2d;
        border-bottom-color: #7f1d2d;
    }

    html[data-theme="dark"] .api-tab-button {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .api-tab-button:hover {
        background: rgba(255, 255, 255, 0.05);
        color: #f3d6da;
    }

    html[data-theme="dark"] .api-tab-button.is-active {
        color: #f3d6da;
        border-bottom-color: #f3d6da;
    }

    .api-tab-content {
        display: none;
    }

    .api-tab-content.is-active {
        display: block;
    }

    .api-health-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 16px;
        margin-top: 20px;
    }

    .api-health-card {
        border-radius: 16px;
        padding: 18px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(247, 244, 245, 0.98));
        border: 1px solid rgba(127, 29, 45, 0.12);
    }

    html[data-theme="dark"] .api-health-card {
        background: linear-gradient(180deg, rgba(59, 24, 33, 0.96), rgba(35, 17, 25, 0.98));
        border-color: rgba(255, 255, 255, 0.08);
    }

    .api-health-card h4 {
        margin: 0 0 12px;
        color: #7f1d2d;
        font-size: 15px;
    }

    html[data-theme="dark"] .api-health-card h4 {
        color: #f3d6da;
    }

    .api-health-status {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
    }

    .api-health-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 700;
    }

    .api-health-badge.healthy {
        background: rgba(34, 197, 94, 0.1);
        color: #166534;
    }

    .api-health-badge.unhealthy {
        background: rgba(239, 68, 68, 0.1);
        color: #991b1b;
    }

    .api-health-badge.down {
        background: rgba(156, 163, 175, 0.1);
        color: #374151;
    }

    .api-health-badge.unconfigured {
        background: rgba(234, 179, 8, 0.1);
        color: #7c2d12;
    }

    .api-error-log-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .api-error-log-table th,
    .api-error-log-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid rgba(127, 29, 45, 0.08);
    }

    .api-error-log-table th {
        background: rgba(127, 29, 45, 0.04);
        font-weight: 700;
        color: #7f1d2d;
    }

    html[data-theme="dark"] .api-error-log-table th {
        background: rgba(255, 255, 255, 0.05);
        color: #f3d6da;
    }

    .api-error-log-table small {
        display: block;
        color: #6b7280;
        margin-top: 4px;
    }

    html[data-theme="dark"] .api-error-log-table small {
        color: #cbd5e1;
    }

    .api-loading {
        text-align: center;
        padding: 40px 20px;
        color: #6b7280;
    }

    .api-loading-spinner {
        display: inline-block;
        width: 24px;
        height: 24px;
        border: 3px solid rgba(127, 29, 45, 0.1);
        border-top-color: #7f1d2d;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .api-testing-card {
        position: relative;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.96);
        border-radius: 22px;
        padding: 26px;
        box-shadow: 0 22px 50px rgba(15, 23, 42, 0.14);
        border: 1px solid rgba(128, 0, 0, 0.08);
    }

    html[data-theme="dark"] .api-testing-card {
        background: rgba(35, 17, 25, 0.94);
        border-color: rgba(255, 255, 255, 0.08);
        box-shadow: 0 22px 50px rgba(0, 0, 0, 0.28);
    }

    .api-testing-head h2 {
        margin: 0 0 8px;
        font-size: 1.45rem;
        font-weight: 900;
        color: #7f1d2d;
    }

    html[data-theme="dark"] .api-testing-head h2 {
        color: #f3d6da;
    }

    .api-testing-head p,
    .api-testing-meta,
    .api-empty {
        margin: 0;
        color: #6b7280;
    }

    html[data-theme="dark"] .api-testing-head p,
    html[data-theme="dark"] .api-testing-meta,
    html[data-theme="dark"] .api-empty {
        color: #cbd5e1;
    }

    .api-search-form {
        display: grid;
        grid-template-columns: 220px minmax(0, 1fr) auto;
        gap: 14px;
        align-items: end;
        margin-top: 22px;
        padding: 18px;
        border-radius: 20px;
        background: linear-gradient(180deg, rgba(255,255,255,.82), rgba(248,250,252,.72));
        border: 1px solid rgba(127, 29, 45, 0.10);
        box-shadow: inset 0 1px 0 rgba(255,255,255,.86);
    }

    .api-system-group.is-hidden {
        display: none;
    }

    .api-db-switch {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 18px;
    }

    .api-db-btn {
        border: 1px solid rgba(127, 29, 45, 0.16);
        border-radius: 16px;
        padding: 12px 18px;
        background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(250,244,246,0.98));
        color: #7f1d2d;
        font-weight: 800;
        text-decoration: none;
        box-shadow: 0 12px 24px rgba(127, 29, 45, 0.08);
    }

    .api-db-btn.is-active {
        background: linear-gradient(135deg, #7f1d2d, #5b0c0e);
        color: #fff;
    }

    .api-db-list {
        display: grid;
        gap: 18px;
    }

    .api-db-card {
        border-radius: 20px;
        padding: 20px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(250, 244, 246, 0.98));
        border: 1px solid rgba(127, 29, 45, 0.12);
    }

    .api-db-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 16px;
    }

    .api-db-head h3 {
        margin: 0;
        color: #7f1d2d;
    }

    .api-db-head p {
        margin: 4px 0 0;
        color: #6b7280;
        font-size: 13px;
    }

    .api-db-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .api-db-action-btn {
        border: 1px solid rgba(127, 29, 45, 0.16);
        border-radius: 12px;
        padding: 10px 14px;
        background: #fff;
        color: #7f1d2d;
        font-weight: 800;
        cursor: pointer;
    }

    .api-db-action-btn.delete {
        border-color: rgba(185, 28, 28, 0.18);
        background: #fee2e2;
        color: #991b1b;
    }

    .api-db-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .api-edit-modal {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.48);
        backdrop-filter: blur(8px);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        z-index: 5000;
    }

    .api-edit-modal.show {
        display: flex;
    }

    .api-edit-content {
        width: min(720px, 100%);
        border-radius: 24px;
        background: rgba(255,255,255,0.98);
        border: 1px solid rgba(127, 29, 45, 0.12);
        box-shadow: 0 24px 54px rgba(15,23,42,0.18);
        padding: 22px;
    }

    .api-edit-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        margin-top: 16px;
    }

    .api-edit-field label {
        display: block;
        margin-bottom: 6px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: #6b7280;
    }

    .api-edit-field input,
    .api-edit-field select {
        width: 100%;
        border-radius: 14px;
        border: 1px solid rgba(127, 29, 45, 0.16);
        padding: 12px 14px;
    }

    .api-edit-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 18px;
    }

    .api-search-form label {
        display: block;
        margin-bottom: 8px;
        font-weight: 700;
        color: #374151;
    }

    html[data-theme="dark"] .api-search-form label {
        color: #f8fafc;
    }

    .api-search-form input,
    .api-search-form select {
        width: 100%;
        border-radius: 16px;
        border: 1px solid rgba(127, 29, 45, 0.2);
        padding: 14px 16px;
        font-size: 15px;
        outline: none;
        background: rgba(255,255,255,.98);
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .api-search-form input:focus,
    .api-search-form select:focus {
        border-color: #7f1d2d;
        box-shadow: 0 0 0 4px rgba(127, 29, 45, 0.10);
        transform: translateY(-1px);
    }

    .api-search-form button {
        min-width: 148px;
        border: 1px solid #8f2230;
        border-radius: 16px;
        padding: 14px 18px;
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #fff;
        font-weight: 900;
        box-shadow: 0 12px 24px rgba(112,19,27,.18);
        cursor: pointer;
        transition: transform .18s ease, background .18s ease, color .18s ease, border-color .18s ease;
    }

    .api-search-form button:hover {
        transform: translateY(-1px);
        background: #facc15;
        color: #111827;
        border-color: #facc15;
    }

    .api-alert {
        margin-top: 16px;
        padding: 14px 16px;
        border-radius: 16px;
        background: rgba(127, 29, 45, 0.08);
        border: 1px solid rgba(127, 29, 45, 0.18);
        color: #7f1d2d;
    }

    .api-connection-note {
        margin-top: 14px;
        border-radius: 18px;
        padding: 13px 16px;
        background: linear-gradient(180deg, rgba(255, 248, 230, 0.98), rgba(255, 252, 244, 0.98));
        border: 1px solid rgba(234, 179, 8, 0.28);
        box-shadow: 0 16px 28px rgba(234, 179, 8, 0.10);
    }

    .api-connection-note strong {
        color: #7c2d12;
    }

    .api-connection-note code {
        display: inline-block;
        margin-top: 6px;
        padding: 6px 10px;
        border-radius: 10px;
        background: rgba(120, 53, 15, 0.08);
        color: #7c2d12;
        word-break: break-all;
    }

    .api-connection-note small {
        display: block;
        margin-top: 8px;
        color: #92400e;
        line-height: 1.5;
    }

    .api-results {
        display: grid;
        gap: 18px;
    }

    .api-result-card {
        border-radius: 20px;
        padding: 22px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(247, 244, 245, 0.98));
        border: 1px solid rgba(127, 29, 45, 0.1);
    }

    html[data-theme="dark"] .api-result-card {
        background: linear-gradient(180deg, rgba(59, 24, 33, 0.96), rgba(35, 17, 25, 0.98));
        border-color: rgba(255, 255, 255, 0.08);
    }

    html[data-theme="dark"] .api-connection-note {
        background: linear-gradient(180deg, rgba(84, 45, 12, 0.92), rgba(55, 28, 9, 0.96));
        border-color: rgba(255, 214, 102, 0.24);
        box-shadow: 0 16px 28px rgba(0, 0, 0, 0.18);
        color: #fde68a;
    }

    html[data-theme="dark"] .api-connection-note strong,
    html[data-theme="dark"] .api-connection-note small,
    html[data-theme="dark"] .api-connection-note code {
        color: #fde68a;
    }

    html[data-theme="dark"] .api-connection-note code {
        background: rgba(255, 255, 255, 0.08);
    }

    .api-result-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-top: 16px;
    }

    .api-field {
        border-radius: 14px;
        padding: 12px 14px;
        background: rgba(127, 29, 45, 0.06);
    }

    html[data-theme="dark"] .api-field {
        background: rgba(255, 255, 255, 0.05);
    }

    .api-field small {
        display: block;
        margin-bottom: 6px;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b7280;
    }

    .api-field strong {
        color: #111827;
    }

    html[data-theme="dark"] .api-field strong {
        color: #f8fafc;
    }

    .api-raw-toggle {
        margin-top: 16px;
    }

    .api-raw-toggle summary {
        cursor: pointer;
        font-weight: 700;
        color: #7f1d2d;
        list-style: none;
    }

    .api-raw-toggle summary::-webkit-details-marker {
        display: none;
    }

    html[data-theme="dark"] .api-raw-toggle summary {
        color: #f3d6da;
    }

    .api-json {
        margin-top: 12px;
        border-radius: 16px;
        padding: 16px;
        background: #111827;
        color: #f8fafc;
        overflow: auto;
        font-size: 12px;
        line-height: 1.5;
    }

    .admin-option-list {
        display: grid;
        gap: 12px;
    }

    .admin-option-item,
    .faculty-option-item {
        width: 100%;
        text-align: left;
        border: 1px solid rgba(127, 29, 45, 0.16);
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(250, 244, 246, 0.98));
        border-radius: 18px;
        padding: 16px 18px;
        cursor: pointer;
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    }

    .admin-option-item:hover,
    .faculty-option-item:hover {
        transform: translateY(-1px);
        border-color: rgba(127, 29, 45, 0.28);
        box-shadow: 0 12px 24px rgba(127, 29, 45, 0.08);
    }

    .admin-option-name,
    .faculty-option-name {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
        color: #7f1d2d;
    }

    .admin-option-email,
    .faculty-option-email {
        margin-top: 4px;
        font-size: 13px;
        color: #6b7280;
    }

    .admin-option-meta,
    .faculty-option-meta {
        margin-top: 10px;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .admin-option-chip,
    .faculty-option-chip {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        border-radius: 999px;
        background: rgba(127, 29, 45, 0.08);
        color: #7f1d2d;
        font-size: 12px;
        font-weight: 700;
    }

    .admin-autofill-panel {
        margin-top: 20px;
        border-radius: 20px;
        padding: 20px;
        background: rgba(127, 29, 45, 0.05);
        border: 1px solid rgba(127, 29, 45, 0.12);
    }

    .admin-autofill-panel h3 {
        margin: 0 0 14px;
        color: #7f1d2d;
    }

    .admin-autofill-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .admin-autofill-field label {
        display: block;
        margin-bottom: 6px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b7280;
    }

    .admin-autofill-field input {
        width: 100%;
        border-radius: 14px;
        border: 1px solid rgba(127, 29, 45, 0.16);
        padding: 12px 14px;
        font-size: 14px;
        color: #111827;
        background: rgba(255, 255, 255, 0.96);
    }

    .faculty-autofill-panel {
        margin-top: 20px;
        border-radius: 20px;
        padding: 20px;
        background: rgba(127, 29, 45, 0.05);
        border: 1px solid rgba(127, 29, 45, 0.12);
    }

    .faculty-autofill-panel h3 {
        margin: 0 0 14px;
        color: #7f1d2d;
    }

    .faculty-autofill-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .faculty-autofill-field label {
        display: block;
        margin-bottom: 6px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b7280;
    }

    .faculty-autofill-field input {
        width: 100%;
        border-radius: 14px;
        border: 1px solid rgba(127, 29, 45, 0.16);
        padding: 12px 14px;
        font-size: 14px;
        color: #111827;
        background: rgba(255, 255, 255, 0.96);
    }

    html[data-theme="dark"] .admin-option-item,
    html[data-theme="dark"] .faculty-option-item {
        background: linear-gradient(180deg, rgba(59, 24, 33, 0.96), rgba(35, 17, 25, 0.98));
        border-color: rgba(255, 255, 255, 0.08);
    }

    html[data-theme="dark"] .api-db-btn:not(.is-active),
    html[data-theme="dark"] .api-db-card,
    html[data-theme="dark"] .api-edit-content {
        background: linear-gradient(180deg, rgba(59, 24, 33, 0.96), rgba(35, 17, 25, 0.98));
        border-color: rgba(255, 255, 255, 0.08);
        color: #f8fafc;
    }

    html[data-theme="dark"] .api-db-head h3 {
        color: #f3d6da;
    }

    html[data-theme="dark"] .api-db-head p,
    html[data-theme="dark"] .api-edit-field label {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .api-db-action-btn {
        background: rgba(255,255,255,0.08);
        color: #fff;
        border-color: rgba(255,255,255,0.12);
    }

    html[data-theme="dark"] .api-edit-field input,
    html[data-theme="dark"] .api-edit-field select {
        background: rgba(18, 18, 18, 0.55);
        border-color: rgba(255, 255, 255, 0.08);
        color: #f8fafc;
    }

    html[data-theme="dark"] .admin-option-name,
    html[data-theme="dark"] .faculty-option-name {
        color: #f3d6da;
    }

    html[data-theme="dark"] .admin-option-email,
    html[data-theme="dark"] .faculty-option-email,
    html[data-theme="dark"] .admin-autofill-field label,
    html[data-theme="dark"] .faculty-autofill-field label {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .admin-option-chip,
    html[data-theme="dark"] .faculty-option-chip,
    html[data-theme="dark"] .admin-autofill-panel,
    html[data-theme="dark"] .faculty-autofill-panel {
        background: rgba(255, 255, 255, 0.05);
        color: #f8fafc;
        border-color: rgba(255, 255, 255, 0.08);
    }

    html[data-theme="dark"] .admin-autofill-panel h3,
    html[data-theme="dark"] .faculty-autofill-panel h3 {
        color: #f3d6da;
    }

    html[data-theme="dark"] .admin-autofill-field input,
    html[data-theme="dark"] .faculty-autofill-field input {
        background: rgba(18, 18, 18, 0.55);
        border-color: rgba(255, 255, 255, 0.08);
        color: #f8fafc;
    }

    .api-testing-shell {
        width: min(1180px, calc(100% - 32px));
        margin: 24px auto;
    }

    .api-testing-card {
        border-radius: 18px;
        padding: 18px;
        background: #ffffff;
        border: 1px solid rgba(127, 29, 45, .16);
        box-shadow: 0 18px 42px rgba(15, 23, 42, .08);
    }

    .api-testing-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 18px;
        right: 18px;
        height: 5px;
        border-radius: 0 0 999px 999px;
        background: #70131B;
    }

    .api-testing-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 16px;
    }

    .api-testing-head h2 {
        margin: 0 0 5px;
        color: #0f172a;
        font-size: 26px;
        line-height: 1.1;
        font-weight: 950;
    }

    .api-testing-head p {
        color: #334155;
        font-size: 13px;
        font-weight: 700;
    }

    .api-refresh-chip {
        min-height: 36px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid rgba(112, 19, 27, .16);
        border-radius: 10px;
        background: #fff;
        color: #475569;
        padding: 0 12px;
        font-size: 12px;
        font-weight: 850;
        white-space: nowrap;
    }

    .api-refresh-chip button {
        width: 28px;
        height: 28px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(112, 19, 27, .14);
        border-radius: 8px;
        background: #f8fafc;
        color: #70131B;
        cursor: pointer;
    }

    .api-dashboard-stats {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 12px;
        margin: 16px 0 18px;
    }

    .api-stat-card {
        min-height: 86px;
        display: grid;
        grid-template-columns: 42px minmax(0, 1fr);
        align-items: center;
        gap: 12px;
        border-radius: 10px;
        border: 1px solid rgba(127, 29, 45, .12);
        background: #ffffff;
        padding: 12px;
        box-shadow: 0 10px 22px rgba(15, 23, 42, .04);
    }

    .api-stat-icon {
        width: 42px;
        height: 42px;
        display: grid;
        place-items: center;
        border-radius: 12px;
        color: #70131B;
        background: #fff1f2;
    }

    .api-stat-icon svg {
        width: 20px;
        height: 20px;
    }

    .api-stat-card.is-green .api-stat-icon { color: #16a34a; background: #dcfce7; }
    .api-stat-card.is-blue .api-stat-icon { color: #2563eb; background: #dbeafe; }
    .api-stat-card.is-red .api-stat-icon { color: #dc2626; background: #fee2e2; }
    .api-stat-card.is-amber .api-stat-icon { color: #ea580c; background: #ffedd5; }

    .api-stat-label {
        display: block;
        color: #475569;
        font-size: 11px;
        font-weight: 900;
    }

    .api-stat-value {
        display: block;
        margin-top: 4px;
        color: #0f172a;
        font-size: 21px;
        font-weight: 950;
        line-height: 1;
    }

    .api-stat-sub {
        display: block;
        margin-top: 5px;
        color: #64748b;
        font-size: 11px;
        font-weight: 750;
    }

    .api-tabs {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        overflow: hidden;
        border: 1px solid rgba(127, 29, 45, .14);
        border-radius: 10px;
        margin: 0 0 18px;
        background: #fff;
    }

    .api-tab-button {
        bottom: auto;
        min-height: 44px;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: 0;
        border-right: 1px solid rgba(127, 29, 45, .12);
        border-bottom: 0;
        background: #ffffff;
        color: #0f172a;
        padding: 0 12px;
        font-size: 12px;
        font-weight: 900;
        text-decoration: none;
    }

    .api-tab-button:last-child {
        border-right: 0;
    }

    .api-tab-button:hover,
    .api-tab-button.is-active {
        background: #8f1827;
        color: #ffffff;
        border-bottom-color: transparent;
    }

    .api-tab-button.is-disabled {
        opacity: .52;
        cursor: not-allowed;
    }

    .api-pin-modal {
        position: fixed;
        inset: 0;
        z-index: 6000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(15, 23, 42, .56);
        backdrop-filter: blur(8px);
    }

    .api-pin-modal.is-open {
        display: flex;
    }

    .api-pin-dialog {
        width: min(620px, 100%);
        overflow: hidden;
        border-radius: 18px;
        border: 1px solid rgba(255, 255, 255, .18);
        background: #ffffff;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .26);
    }

    .api-pin-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 20px 22px;
        background: linear-gradient(135deg, #9d1427 0%, #710012 100%);
        color: #ffffff;
    }

    .api-pin-head-main {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .api-pin-head-icon {
        width: 52px;
        height: 52px;
        flex: 0 0 52px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 18px;
        background: rgba(255, 255, 255, .12);
        border: 1px solid rgba(255, 255, 255, .18);
        color: #ffffff;
    }

    .api-pin-head-icon svg {
        width: 30px;
        height: 30px;
    }

    .api-pin-head h3 {
        margin: 0;
        color: #ffffff;
        font-size: 22px;
        font-weight: 950;
    }

    .api-pin-head p {
        margin: 5px 0 0;
        color: rgba(255,255,255,.9);
        font-size: 14px;
        font-weight: 700;
    }

    .api-pin-close {
        position: relative;
        overflow: hidden;
        width: 42px;
        height: 42px;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,.22);
        background: rgba(255,255,255,.12);
        color: #ffffff;
        font-size: 24px;
        cursor: pointer;
    }

    .api-pin-close:hover,
    .api-pin-close:focus-visible {
        background: #facc15;
        border-color: #facc15;
        color: #70131B;
        outline: none;
    }

    .api-pin-body {
        display: grid;
        gap: 14px;
        padding: 18px 24px 0;
    }

    .api-pin-warning {
        display: flex;
        align-items: center;
        gap: 12px;
        border-radius: 10px;
        border: 1px solid #f1ccd3;
        background: linear-gradient(135deg, #fff7f8, #ffffff);
        padding: 12px 16px;
    }

    .api-pin-warning-icon {
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: #ffe7eb;
        color: #8f1827;
    }

    .api-pin-warning-icon svg {
        width: 20px;
        height: 20px;
    }

    .api-pin-warning strong {
        display: block;
        color: #8f1827;
        font-size: 15px;
        font-weight: 950;
    }

    .api-pin-warning span {
        display: block;
        margin-top: 3px;
        color: #3f2a32;
        font-size: 13px;
        font-weight: 750;
    }

    .api-pin-entry {
        display: grid;
        justify-items: center;
        gap: 8px;
        padding: 0 0 2px;
        text-align: center;
    }

    .api-pin-entry-icon {
        color: #111827;
    }

    .api-pin-entry-icon svg {
        width: 22px;
        height: 22px;
    }

    .api-pin-entry-title {
        color: #111827;
        font-size: 15px;
        font-weight: 950;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .api-pin-entry-copy {
        margin-top: -6px;
        color: #4b5563;
        font-size: 13px;
        font-weight: 750;
    }

    .api-pin-digits {
        display: grid;
        grid-template-columns: repeat(4, minmax(44px, 60px));
        gap: 22px;
        justify-content: center;
        margin: 5px 0;
    }

    .api-pin-digits input {
        width: 100%;
        aspect-ratio: 1;
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #0f172a;
        padding: 0;
        font-size: 22px;
        font-weight: 950;
        text-align: center;
        caret-color: #8f1827;
    }

    .api-pin-digits input:focus {
        border-color: #8f1827;
        box-shadow: 0 0 0 3px rgba(143, 24, 39, .12);
        outline: none;
    }

    .api-pin-hidden {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
        pointer-events: none;
    }

    .api-pin-security-note {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        color: #4b5563;
        font-size: 13px;
        font-weight: 750;
    }

    .api-pin-security-note svg {
        width: 18px;
        height: 18px;
        color: #64748b;
    }

    .api-pin-reset-row {
        width: 100%;
        margin-top: 5px;
        padding-top: 13px;
        border-top: 1px dashed #d1d5db;
        color: #4b5563;
        font-size: 14px;
        font-weight: 750;
    }

    .api-pin-reset-row a {
        color: #8f1827;
        font-weight: 950;
        text-decoration: underline;
        text-underline-offset: 3px;
    }

    .api-pin-error {
        display: none;
        padding: 10px 12px;
        border-radius: 12px;
        background: #fee2e2;
        color: #991b1b;
        font-size: 13px;
        font-weight: 850;
    }

    .api-pin-error.is-visible {
        display: block;
    }

    .api-pin-actions {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin: 0 -24px;
        padding: 16px 130px;
        border-top: 1px solid #e5e7eb;
        background: #ffffff;
    }

    .api-pin-cancel,
    .api-pin-submit {
        min-height: 44px;
        width: 100%;
        min-width: 0;
        border-radius: 8px;
        padding: 0 22px;
        border: 1px solid rgba(112, 19, 27, .16);
        font-weight: 950;
        cursor: pointer;
    }

    .api-pin-cancel {
        background: #ffffff;
        color: #0f172a;
    }

    .api-pin-submit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        background: #70131B;
        color: #ffffff;
        border-color: #70131B;
    }

    .api-pin-cancel:hover,
    .api-pin-cancel:focus-visible,
    .api-pin-submit:hover,
    .api-pin-submit:focus-visible {
        background: #facc15;
        border-color: #facc15;
        color: #70131B;
        outline: none;
    }

    @media (max-width: 640px) {
        .api-pin-head {
            padding: 18px;
        }

        .api-pin-head-main {
            gap: 12px;
        }

        .api-pin-head-icon {
            width: 48px;
            height: 48px;
            flex-basis: 48px;
        }

        .api-pin-digits {
            grid-template-columns: repeat(4, minmax(42px, 58px));
            gap: 12px;
        }

        .api-pin-actions {
            padding: 16px 24px;
        }

        .api-pin-cancel,
        .api-pin-submit {
            width: 100%;
        }
    }

    .api-search-form {
        grid-template-columns: minmax(220px, .95fr) minmax(260px, 1.4fr) auto;
        gap: 14px;
        margin-top: 0;
        padding: 16px;
        border-radius: 14px;
        background: #ffffff;
        border: 1px solid rgba(127, 29, 45, .12);
        box-shadow: 0 12px 26px rgba(15, 23, 42, .04);
    }

    .api-builder-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 12px;
    }

    .api-builder-head h3 {
        margin: 0;
        color: #0f172a;
        font-size: 17px;
        font-weight: 950;
    }

    .api-builder-head p {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 12px;
        font-weight: 750;
    }

    .api-builder-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .api-builder-actions a,
    .api-builder-actions button {
        min-height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(112, 19, 27, .16);
        border-radius: 8px;
        background: #ffffff;
        color: #0f172a;
        padding: 0 12px;
        font-size: 12px;
        font-weight: 850;
        text-decoration: none;
        cursor: pointer;
    }

    .api-builder-actions a:hover,
    .api-builder-actions button:hover,
    .api-builder-actions a:focus-visible,
    .api-builder-actions button:focus-visible {
        background: #facc15;
        border-color: #facc15;
        color: #70131B;
        outline: none;
    }

    .api-search-form:has(.api-system-group:not(.is-hidden)) {
        grid-template-columns: minmax(200px, .8fr) minmax(180px, .7fr) minmax(240px, 1.2fr) auto;
    }

    .api-search-form label {
        color: #0f172a;
        font-size: 12px;
        font-weight: 950;
        letter-spacing: .02em;
    }

    .api-search-form input,
    .api-search-form select {
        min-height: 42px;
        border-radius: 8px;
        border: 1px solid rgba(127, 29, 45, .18);
        background: #ffffff;
        color: #0f172a;
        padding: 0 12px;
        font-size: 13px;
        font-weight: 750;
    }

    .api-search-form button[type="submit"] {
        position: relative;
        overflow: hidden;
        min-width: 140px;
        min-height: 42px;
        border-radius: 8px;
        border: 1px solid #70131B;
        background: #70131B;
        color: #ffffff;
        box-shadow: 0 12px 24px rgba(112,19,27,.18);
    }

    .api-search-form button[type="submit"]::after,
    .api-db-action-btn::after {
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

    .api-search-form button[type="submit"]:hover,
    .api-search-form button[type="submit"]:focus-visible,
    .api-db-action-btn:hover,
    .api-db-action-btn:focus-visible {
        background: #facc15;
        border-color: #facc15;
        color: #70131B;
        outline: none;
    }

    .api-search-form button[type="submit"]:hover::after,
    .api-search-form button[type="submit"]:focus-visible::after,
    .api-db-action-btn:hover::after,
    .api-db-action-btn:focus-visible::after {
        left: 128%;
    }

    .api-db-action-btn {
        position: relative;
        overflow: hidden;
        border-radius: 8px;
        border-color: #70131B;
        background: #70131B;
        color: #ffffff;
        box-shadow: 0 10px 20px rgba(112,19,27,.12);
    }

    .api-testing-meta {
        border-radius: 12px;
        border: 1px solid rgba(127, 29, 45, .12);
        background: #fff7f7;
        padding: 11px 13px;
        font-size: 12px;
        line-height: 1.6;
    }

    .api-response-panel {
        margin-top: -2px;
    }

    .api-response-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        margin-bottom: 14px;
    }

    .api-response-head h3 {
        margin: 0;
        color: #0f172a;
        font-size: 18px;
        font-weight: 950;
    }

    .api-response-status {
        display: inline-flex;
        align-items: center;
        min-height: 30px;
        border-radius: 999px;
        background: #dcfce7;
        color: #166534;
        padding: 0 12px;
        font-size: 12px;
        font-weight: 950;
    }

    .api-results,
    .admin-option-list,
    .api-db-list {
        gap: 12px;
    }

    .api-result-card,
    .admin-option-item,
    .faculty-option-item,
    .api-db-card {
        border-radius: 12px;
        background: #ffffff;
        border-color: rgba(127, 29, 45, .12);
        box-shadow: 0 10px 22px rgba(15, 23, 42, .04);
    }

    .api-json {
        border-radius: 10px;
        background: #0f172a;
        color: #e2e8f0;
    }

    html[data-theme="dark"] .api-testing-card,
    html[data-theme="dark"] .api-stat-card,
    html[data-theme="dark"] .api-tabs,
    html[data-theme="dark"] .api-tab-button,
    html[data-theme="dark"] .api-search-form,
    html[data-theme="dark"] .api-result-card,
    html[data-theme="dark"] .admin-option-item,
    html[data-theme="dark"] .faculty-option-item,
    html[data-theme="dark"] .api-db-card {
        background: rgba(15, 23, 42, .94);
        border-color: rgba(250, 204, 21, .16);
    }

    html[data-theme="dark"] .api-testing-head h2,
    html[data-theme="dark"] .api-stat-value,
    html[data-theme="dark"] .api-response-head h3,
    html[data-theme="dark"] .api-builder-head h3,
    html[data-theme="dark"] .api-search-form label {
        color: #ffffff;
    }

    html[data-theme="dark"] .api-testing-head p,
    html[data-theme="dark"] .api-stat-label,
    html[data-theme="dark"] .api-stat-sub,
    html[data-theme="dark"] .api-builder-head p {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .api-search-form input,
    html[data-theme="dark"] .api-search-form select,
    html[data-theme="dark"] .api-testing-meta,
    html[data-theme="dark"] .api-builder-actions a,
    html[data-theme="dark"] .api-builder-actions button,
    html[data-theme="dark"] .api-pin-dialog,
    html[data-theme="dark"] .api-pin-body input,
    html[data-theme="dark"] .api-pin-cancel {
        background: rgba(17, 24, 39, .95);
        border-color: rgba(250, 204, 21, .16);
        color: #ffffff;
    }

    html[data-theme="dark"] .api-pin-body label {
        color: #ffffff;
    }

    @media (max-width: 900px) {
        .api-dashboard-stats,
        .api-tabs {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .api-search-form,
        .api-search-form:has(.api-system-group:not(.is-hidden)) {
            grid-template-columns: 1fr;
        }

        .api-result-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 680px) {
        .api-search-form {
            grid-template-columns: 1fr;
        }

        .api-result-grid {
            grid-template-columns: 1fr;
        }

        .admin-autofill-grid {
            grid-template-columns: 1fr;
        }

        .faculty-autofill-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="api-testing-shell">
    <section class="api-testing-card">
        @php
            $apiSourceCount = 12 + count($availableSystems ?? []);
            $apiResultCount = (int) ($apiResponseMeta['result_count'] ?? 0);
            $apiStatusCode = (int) ($apiResponseMeta['status'] ?? 200);
            $apiErrorCount = $errorMessage ? 1 : 0;
            $apiPinUser = \Illuminate\Support\Facades\Auth::guard('admin')->user() ?? auth()->user();
            $integrationPinDisabled = (bool) ($apiPinUser->api_pin_disabled ?? false);
            $legacyIntegrationPinEnabled = (bool) ($apiPinUser->api_pin_enabled ?? false);
            $integrationPinEnabled = $legacyIntegrationPinEnabled && (bool) ($apiPinUser->api_pin_page_enabled ?? true);
            $apiTestingCurrentSource = $source ?? 'faculty';
            $apiTestingSearchLabel = 'Search by name, email, or ID';
            $apiTestingSearchPlaceholder = 'Try a name, email address, or identifier';

            if ($apiTestingCurrentSource === 'puptas_applicant') {
                $apiTestingSearchLabel = 'Search by Student Number';
                $apiTestingSearchPlaceholder = 'Try a student number';
            } elseif ($apiTestingCurrentSource === 'puptas_applicant_idp') {
                $apiTestingSearchLabel = 'Search by IDP User ID';
                $apiTestingSearchPlaceholder = 'Try an IDP user ID';
            } elseif ($apiTestingCurrentSource === 'guisis_profile') {
                $apiTestingSearchLabel = 'Search by Student Email';
                $apiTestingSearchPlaceholder = 'Try a student email address';
            } elseif (in_array($apiTestingCurrentSource, ['guisis_student', 'guisis_addresses', 'guisis_personal_info'], true)) {
                $apiTestingSearchLabel = 'Search by Student Number';
                $apiTestingSearchPlaceholder = 'Try a student number';
            }
        @endphp
        <div class="api-testing-head">
            <div>
                <h2>API Dashboard</h2>
                <p>Monitor, test, and troubleshoot external system integrations.</p>
            </div>
            <div class="api-refresh-chip">
                <span>Last updated: {{ now()->format('g:i A') }}</span>
                <button type="button" onclick="window.location.reload()" aria-label="Refresh API dashboard">
                    <x-outline-icon name="clock" />
                </button>
            </div>
        </div>

        <div class="api-dashboard-stats">
            <article class="api-stat-card is-green">
                <span class="api-stat-icon"><x-outline-icon name="check" /></span>
                <span>
                    <span class="api-stat-label">API Health</span>
                    <span class="api-stat-value">{{ $errorMessage ? 'Check' : '98%' }}</span>
                    <span class="api-stat-sub">{{ $errorMessage ? 'Review latest error' : 'All systems operational' }}</span>
                </span>
            </article>
            <article class="api-stat-card">
                <span class="api-stat-icon"><x-outline-icon name="cube" /></span>
                <span>
                    <span class="api-stat-label">Sources</span>
                    <span class="api-stat-value">{{ number_format($apiSourceCount) }}</span>
                    <span class="api-stat-sub">Connected options</span>
                </span>
            </article>
            <article class="api-stat-card is-blue">
                <span class="api-stat-icon"><x-outline-icon name="document-text" /></span>
                <span>
                    <span class="api-stat-label">Matches</span>
                    <span class="api-stat-value">{{ number_format($apiResultCount) }}</span>
                    <span class="api-stat-sub">Current response</span>
                </span>
            </article>
            <article class="api-stat-card is-red">
                <span class="api-stat-icon"><x-outline-icon name="exclamation-triangle" /></span>
                <span>
                    <span class="api-stat-label">Errors</span>
                    <span class="api-stat-value">{{ number_format($apiErrorCount) }}</span>
                    <span class="api-stat-sub">Current request</span>
                </span>
            </article>
            <article class="api-stat-card is-amber">
                <span class="api-stat-icon"><x-outline-icon name="clock" /></span>
                <span>
                    <span class="api-stat-label">Status</span>
                    <span class="api-stat-value">{{ $apiStatusCode ?: 'N/A' }}</span>
                    <span class="api-stat-sub">{{ $apiStatusCode >= 400 ? 'Needs attention' : 'Good performance' }}</span>
                </span>
            </article>
        </div>

        @php
            $studentNumberSyncSummary = session('student_number_sync_summary');
        @endphp
        <section class="api-testing-card guisis-sync-card" aria-labelledby="guisisStudentNumberSyncTitle">
            <div class="guisis-sync-head">
                <div>
                    <h3 id="guisisStudentNumberSyncTitle">GuiSIS Student Number Sync</h3>
                    <p>Check health-record users against GuiSIS and sync their official IDP ID, student number, year level, and section in batches of 25.</p>
                    <p class="guisis-sync-note" style="margin-top: 6px;">Matching uses IDP UUID first, then exact email. Name-only matches are never applied automatically.</p>
                    <p class="guisis-sync-note" style="margin-top: 6px;"><strong>{{ number_format((int) ($studentNumberSyncPendingCount ?? 0)) }}</strong> local health-record users currently need checking.</p>
                </div>
                <div class="guisis-sync-actions">
                    <form method="POST" action="{{ route('admin.api-testing.sync-student-numbers') }}">
                        @csrf
                        <input type="hidden" name="mode" value="preview">
                        <input type="hidden" name="batch_size" value="25">
                        <button type="submit" class="guisis-sync-button">Preview Next 25</button>
                    </form>
                    <form method="POST" action="{{ route('admin.api-testing.sync-student-numbers') }}" onsubmit="return confirm('Sync the next 25 matched student records from GuiSIS?');">
                        @csrf
                        <input type="hidden" name="mode" value="apply">
                        <input type="hidden" name="batch_size" value="25">
                        <button type="submit" class="guisis-sync-button is-primary">Sync Next 25</button>
                    </form>
                </div>
            </div>

            @if(is_array($studentNumberSyncSummary))
                <div class="guisis-sync-summary" aria-label="GuiSIS synchronization summary">
                    <div class="guisis-sync-stat"><strong>{{ $studentNumberSyncSummary['candidates'] ?? 0 }}</strong><span>Candidates</span></div>
                    <div class="guisis-sync-stat"><strong>{{ $studentNumberSyncSummary['synced'] ?? 0 }}</strong><span>{{ ($studentNumberSyncSummary['mode'] ?? '') === 'preview' ? 'Ready' : 'Synced' }}</span></div>
                    <div class="guisis-sync-stat"><strong>{{ $studentNumberSyncSummary['already_complete'] ?? 0 }}</strong><span>Complete</span></div>
                    <div class="guisis-sync-stat"><strong>{{ $studentNumberSyncSummary['no_match'] ?? 0 }}</strong><span>No match</span></div>
                    <div class="guisis-sync-stat"><strong>{{ $studentNumberSyncSummary['manual_review'] ?? 0 }}</strong><span>Review</span></div>
                    <div class="guisis-sync-stat"><strong>{{ $studentNumberSyncSummary['remaining'] ?? 0 }}</strong><span>Remaining</span></div>
                </div>
                <div class="guisis-sync-result" aria-live="polite">
                    <p class="guisis-sync-note">
                        @if(!empty($studentNumberSyncSummary['cycle_completed']))
                            Sync cycle complete. The next sync starts again from the first candidate.
                        @else
                            {{ ($studentNumberSyncSummary['mode'] ?? '') === 'preview' ? 'Preview complete. No records were changed.' : 'Sync complete.' }}
                            Appointment records updated: {{ $studentNumberSyncSummary['appointment_records_updated'] ?? 0 }}.
                        @endif
                    </p>
                    @if(!empty($studentNumberSyncSummary['details']))
                        <div class="guisis-sync-result-list">
                            @foreach($studentNumberSyncSummary['details'] as $detail)
                                <div class="guisis-sync-result-item">
                                    <span>{{ $detail['name'] ?? 'Unnamed user' }}{{ !empty($detail['email']) ? ' - ' . $detail['email'] : '' }}<br>{{ $detail['message'] ?? '' }}</span>
                                    <strong>{{ $detail['status'] ?? 'unknown' }}</strong>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </section>

        <div class="api-tabs">
            <button class="api-tab-button is-active" data-tab="tests">
                🔍 API Tests
            </button>
            <button class="api-tab-button" data-tab="health">
                💚 Health Monitor
            </button>
            <button class="api-tab-button" data-tab="errors">
                📋 Error Log
            </button>
            <button class="api-tab-button" data-tab="systems">
                🔗 System Status
            </button>
            <a href="{{ route('admin.integration-tokens') }}" class="api-tab-button {{ ($integrationPinDisabled ?? false) ? 'is-disabled' : '' }}" id="integrationTokensGateButton" data-pin-disabled="{{ ($integrationPinDisabled ?? false) ? '1' : '0' }}" data-pin-enabled="{{ ($integrationPinEnabled ?? false) ? '1' : '0' }}" style="text-decoration: none; display: flex; align-items: center; justify-content: center;" aria-disabled="{{ ($integrationPinDisabled ?? false) ? 'true' : 'false' }}">
                🔐 Integration Tokens
            </a>
        </div>

        <!-- TAB 1: API TESTS (Original Content) -->
        <div class="api-tab-content is-active" id="tab-tests">

        <div class="api-builder-head">
            <div>
                <h3>Request Builder</h3>
                <p>Select a source, enter details, and test the API connection.</p>
            </div>
            <div class="api-builder-actions">
                <a href="{{ route('admin.integration-tokens.docs') }}">View Docs</a>
                <button type="button" onclick="navigator.clipboard?.writeText(document.querySelector('#source')?.value || '')">Copy Source</button>
                <button type="button" onclick="window.location.reload()">Refresh Status</button>
            </div>
        </div>

        <form method="GET" class="api-search-form" id="apiTestingForm">
            <div>
                <label for="source">API Source</label>
                <select id="source" name="source">
                    <option value="faculty" {{ ($source ?? 'faculty') === 'faculty' ? 'selected' : '' }}>Faculty API (Test FLSS)</option>
                    <option value="guisis_profile" {{ ($source ?? 'faculty') === 'guisis_profile' ? 'selected' : '' }}>GuiSIS Student by Email</option>
                    <option value="guisis_profiles" {{ ($source ?? 'faculty') === 'guisis_profiles' ? 'selected' : '' }}>GuiSIS List Students</option>
                    <option value="guisis_student" {{ ($source ?? 'faculty') === 'guisis_student' ? 'selected' : '' }}>GuiSIS Student by Student Number</option>
                    <option value="guisis_addresses" {{ ($source ?? 'faculty') === 'guisis_addresses' ? 'selected' : '' }}>GuiSIS Student Addresses</option>
                    <option value="guisis_personal_info" {{ ($source ?? 'faculty') === 'guisis_personal_info' ? 'selected' : '' }}>GuiSIS Student Personal Info</option>
                    <option value="puptas_applicant" {{ ($source ?? 'faculty') === 'puptas_applicant' ? 'selected' : '' }}>PUPTAS Applicant API</option>
                    <option value="puptas_applicant_idp" {{ ($source ?? 'faculty') === 'puptas_applicant_idp' ? 'selected' : '' }}>PUPTAS Applicant API by IDP User ID</option>
                    <option value="admin_api" {{ ($source ?? 'faculty') === 'admin_api' ? 'selected' : '' }}>Our Admin API</option>
                    <option value="admin_options" {{ ($source ?? 'faculty') === 'admin_options' ? 'selected' : '' }}>Our Admin Options API</option>
                    <option value="database_info" {{ ($source ?? 'faculty') === 'database_info' ? 'selected' : '' }}>Database Info</option>
                    <option value="custom" {{ ($source ?? 'faculty') === 'custom' ? 'selected' : '' }}>Custom Temp API</option>
                </select>
            </div>
            <div id="apiSystemGroup" class="api-system-group {{ in_array(($source ?? 'faculty'), ['admin_api', 'admin_options'], true) ? '' : 'is-hidden' }}">
                <label for="system">External System</label>
                <select id="system" name="system">
                    <option value="">Choose system</option>
                    @foreach(($availableSystems ?? []) as $systemOption)
                        <option value="{{ $systemOption }}" {{ ($selectedSystem ?? '') === $systemOption ? 'selected' : '' }}>
                            {{ strtoupper(str_replace('_', ' ', $systemOption)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="search">{{ $apiTestingSearchLabel ?? 'Search by name, email, or ID' }}</label>
                <input
                    type="text"
                    id="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="{{ $apiTestingSearchPlaceholder ?? 'Try a name, email address, or identifier' }}"
                >
            </div>
            <button type="submit">Run Test</button>
        </form>

        @if(($source ?? '') === 'database_info')
            <div class="api-db-switch">
                <a href="{{ route('admin.api-testing', ['source' => 'database_info', 'db_table' => 'users', 'search' => $search]) }}" class="api-db-btn {{ ($dbTable ?? 'users') === 'users' ? 'is-active' : '' }}">Users</a>
                <a href="{{ route('admin.api-testing', ['source' => 'database_info', 'db_table' => 'admins', 'search' => $search]) }}" class="api-db-btn {{ ($dbTable ?? 'users') === 'admins' ? 'is-active' : '' }}">Admins</a>
            </div>
        @endif

        @if($apiResponseMeta)
            <p class="api-testing-meta" style="margin-top: 16px;">
                Endpoint: <strong><code style="font-size: 12px; color: #7f1d2d; word-break: break-all;">{{ htmlspecialchars($apiResponseMeta['endpoint'], ENT_QUOTES, 'UTF-8') }}</code></strong>
                | Status: <strong>{{ $apiResponseMeta['status'] }}</strong>
                | Matches: <strong>{{ $apiResponseMeta['result_count'] }}</strong>
                @if(!empty($apiResponseMeta['auth_mode']))
                | Auth: <strong>{{ $apiResponseMeta['auth_mode'] }}</strong>
                @endif
                @if(!empty($apiResponseMeta['source']))
                | Source: <strong>{{ $apiResponseMeta['source'] }}</strong>
                @endif
                @if(!empty($apiResponseMeta['system']))
                | System: <strong>{{ $apiResponseMeta['system'] }}</strong>
                @endif
            </p>
            @if(!empty($apiResponseMeta['header_name']) || !empty($apiResponseMeta['system_header_name']) || !empty($apiResponseMeta['api_key_preview']))
                <p class="api-testing-meta" style="margin-top: 8px;">
                    @if(!empty($apiResponseMeta['system_header_name']))
                    {{ $apiResponseMeta['system_header_name'] }}: <strong>{{ $apiResponseMeta['system'] ?? 'N/A' }}</strong>
                    @endif
                    @if(!empty($apiResponseMeta['header_name']))
                    | {{ $apiResponseMeta['header_name'] }}: <strong>{{ $apiResponseMeta['api_key_preview'] ?? 'configured' }}</strong>
                    @endif
                </p>
            @endif
            @if(!empty($apiResponseMeta['auth_note']))
                <div class="api-connection-note">{{ $apiResponseMeta['auth_note'] }}</div>
            @endif
            @if(!empty($apiResponseMeta['auth_status']) || !empty($apiResponseMeta['auth_token_source']) || !empty($apiResponseMeta['auth_endpoint']))
                <p class="api-testing-meta" style="margin-top: 8px;">
                    @if(!empty($apiResponseMeta['auth_status']))
                    Token Status: <strong>{{ $apiResponseMeta['auth_status'] }}</strong>
                    @endif
                    @if(!empty($apiResponseMeta['auth_token_source']))
                    | Token Source: <strong>{{ $apiResponseMeta['auth_token_source'] }}</strong>
                    @endif
                    @if(!empty($apiResponseMeta['auth_endpoint']))
                    | Token Endpoint: <strong>{{ $apiResponseMeta['auth_endpoint'] }}</strong>
                    @endif
                </p>
            @endif
        @endif

        @if($errorMessage)
            <div class="api-alert">{{ $errorMessage }}</div>
            @if(!empty($errorDetails))
                <details class="api-raw-toggle">
                    <summary>Show error details</summary>
                    <div class="api-json">{{ $errorDetails }}</div>
                </details>
            @endif
        @endif
    </section>

   

<section class="api-testing-card api-response-panel">
    <div class="api-response-head">
        <h3>Response</h3>
        <span class="api-response-status">{{ $errorMessage ? 'Failed' : ($apiResponseMeta ? 'Successful' : 'Ready') }}</span>
    </div>
    @if(!empty($results))
        @if(($source ?? '') === 'admin_options')
            <div class="admin-option-list">
                @foreach($results as $result)
                    <button
                        type="button"
                        class="admin-option-item"
                        data-first-name="{{ $result['first_name'] ?? '' }}"
                        data-last-name="{{ $result['last_name'] ?? '' }}"
                        data-suffix-name="{{ ($result['suffix_name'] ?? '') === 'N/A' ? '' : ($result['suffix_name'] ?? '') }}"
                        data-email="{{ $result['email'] ?? '' }}"
                        data-status="{{ $result['status'] ?? '' }}"
                    >
                        <p class="admin-option-name">{{ $result['name'] ?? 'N/A' }}</p>
                        <div class="admin-option-email">{{ $result['email'] ?? 'N/A' }}</div>
                        <div class="admin-option-meta">
                            <span class="admin-option-chip">ID: {{ $result['admin_id'] ?? ($result['identifier'] ?? 'N/A') }}</span>
                            <span class="admin-option-chip">Status: {{ $result['status'] ?? 'N/A' }}</span>
                        </div>

                        {{-- Raw Response para sa Admin Options --}}
                        <details class="api-raw-toggle" style="margin-top: 10px;">
                            <summary onclick="event.stopPropagation()">Show raw response</summary>
                            <div class="api-json">{{ json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</div>
                        </details>
                    </button>
                @endforeach
            </div>

            {{-- ... (Selected Admin Panel stays the same) ... --}}
            <div class="admin-autofill-panel">
                <h3>Selected Admin</h3>
                <div class="admin-autofill-grid">
                    <div class="admin-autofill-field"><label>First Name</label><input type="text" id="selectedFirstName" readonly></div>
                    <div class="admin-autofill-field"><label>Last Name</label><input type="text" id="selectedLastName" readonly></div>
                    <div class="admin-autofill-field"><label>Suffix Name</label><input type="text" id="selectedSuffixName" readonly></div>
                    <div class="admin-autofill-field"><label>Email</label><input type="text" id="selectedEmail" readonly></div>
                    <div class="admin-autofill-field"><label>Status</label><input type="text" id="selectedStatus" readonly></div>
                </div>
            </div>

       @elseif(($source ?? '') === 'faculty')
    <div class="admin-option-list">
        @foreach($results as $result)
            @php
                $facultyFields = $result['fields'] ?? [];
                $facultyFirstName = $result['first_name'] ?? ($facultyFields['first_name'] ?? '');
                $facultyMiddleName = $result['middle_name'] ?? ($facultyFields['middle_name'] ?? '');
                $facultyLastName = $result['last_name'] ?? ($facultyFields['last_name'] ?? '');
                $facultySuffixName = $result['suffix_name'] ?? ($facultyFields['suffix_name'] ?? '');
                $facultyEmail = $result['email'] ?? ($facultyFields['email'] ?? 'N/A');
                $facultyStatus = $result['status'] ?? ($facultyFields['status'] ?? 'Active');
                $facultyOffice = $result['office'] ?? ($facultyFields['department'] ?? $facultyFields['office'] ?? 'N/A');
                $facultyIdentifier = $result['identifier'] ?? ($facultyFields['faculty_id'] ?? ($facultyFields['faculty_code'] ?? 'N/A'));
            @endphp
            <button
                type="button"
                class="faculty-option-item"
                data-first-name="{{ $facultyFirstName }}"
                data-middle-name="{{ ($facultyMiddleName ?? '') === 'N/A' ? '' : ($facultyMiddleName ?? '') }}"
                data-last-name="{{ $facultyLastName }}"
                data-suffix-name="{{ ($facultySuffixName ?? '') === 'N/A' ? '' : ($facultySuffixName ?? '') }}"
                data-email="{{ $facultyEmail }}"
                data-status="{{ $facultyStatus }}"
                data-office="{{ $facultyOffice }}"
                data-identifier="{{ $facultyIdentifier }}"
            >
                <p class="faculty-option-name">{{ $result['name'] ?? trim($facultyFirstName . ' ' . $facultyLastName) ?: 'N/A' }}</p>
                <div class="faculty-option-email">{{ $facultyEmail }}</div>

                <div class="faculty-option-meta">
                    <span class="faculty-option-chip">ID: {{ $facultyIdentifier }}</span>
                    <span class="faculty-option-chip">Office: {{ $facultyOffice }}</span>
                    <span class="faculty-option-chip">Status: {{ $facultyStatus }}</span>
                </div>

                <details class="api-raw-toggle" style="margin-top: 10px;">
                    <summary onclick="event.stopPropagation()">Show raw response</summary>
                    <div class="api-json">{{ json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</div>
                </details>
            </button>
        @endforeach
    </div>

    <div class="faculty-autofill-panel">
        <h3>Selected Faculty Details</h3>
        <div class="faculty-autofill-grid">
            <div class="faculty-autofill-field"><label>Faculty ID</label><input type="text" id="selectedFacultyIdentifier" readonly></div>
            <div class="faculty-autofill-field"><label>First Name</label><input type="text" id="selectedFacultyFirstName" readonly></div>
            <div class="faculty-autofill-field"><label>Middle Name</label><input type="text" id="selectedFacultyMiddleName" readonly></div>
            <div class="faculty-autofill-field"><label>Last Name</label><input type="text" id="selectedFacultyLastName" readonly></div>
            <div class="faculty-autofill-field"><label>Suffix Name</label><input type="text" id="selectedFacultySuffixName" readonly></div>
            <div class="faculty-autofill-field"><label>Email</label><input type="text" id="selectedFacultyEmail" readonly></div>
            <div class="faculty-autofill-field"><label>Status</label><input type="text" id="selectedFacultyStatus" readonly></div>
            <div class="faculty-autofill-field"><label>Department/Office</label><input type="text" id="selectedFacultyOffice" readonly></div>
        </div>
    </div>
        @elseif(in_array(($source ?? ''), ['guisis_profile', 'guisis_profiles', 'guisis_student'], true))
            <div class="api-results">
                @foreach($results as $result)
                    @php
                        $guisisFields = $result['fields'] ?? [];
                    @endphp
                    <article class="api-result-card">
                        <h3 style="margin: 0; color: #7f1d2d;">{{ $result['name'] ?? 'GuiSIS Student' }}</h3>
                        <div class="api-result-grid">
                            <div class="api-field">
                                <small>Student Number</small>
                                <strong>{{ $result['student_number'] ?? $result['identifier'] ?? data_get($guisisFields, 'studentNumber', data_get($guisisFields, 'student_number', 'N/A')) }}</strong>
                            </div>
                            <div class="api-field">
                                <small>Email</small>
                                <strong>{{ $result['email'] ?? data_get($guisisFields, 'email', 'N/A') }}</strong>
                            </div>
                            <div class="api-field">
                                <small>First Name</small>
                                <strong>{{ $result['first_name'] ?? data_get($guisisFields, 'firstName', data_get($guisisFields, 'first_name', 'N/A')) }}</strong>
                            </div>
                            <div class="api-field">
                                <small>Last Name</small>
                                <strong>{{ $result['last_name'] ?? data_get($guisisFields, 'lastName', data_get($guisisFields, 'last_name', 'N/A')) }}</strong>
                            </div>
                            <div class="api-field">
                                <small>Course</small>
                                <strong>{{ data_get($guisisFields, 'course.name', data_get($guisisFields, 'program.name', data_get($guisisFields, 'course', data_get($guisisFields, 'program', 'N/A')))) }}</strong>
                            </div>
                            <div class="api-field">
                                <small>Year Level</small>
                                <strong>{{ data_get($guisisFields, 'yearLevel', data_get($guisisFields, 'year_level', 'N/A')) }}</strong>
                            </div>
                            <div class="api-field">
                                <small>Section</small>
                                <strong>{{ data_get($guisisFields, 'section', data_get($guisisFields, 'section_name', data_get($guisisFields, 'sectionName', 'N/A'))) }}</strong>
                            </div>
                            <div class="api-field">
                                <small>Gender</small>
                                <strong>{{ data_get($guisisFields, 'gender.name', data_get($guisisFields, 'gender', 'N/A')) }}</strong>
                            </div>
                            <div class="api-field">
                                <small>Status</small>
                                <strong>{{ $result['status'] ?? data_get($guisisFields, 'status', 'N/A') }}</strong>
                            </div>
                        </div>

                        <details class="api-raw-toggle">
                            <summary>Show raw response</summary>
                            <div class="api-json">{{ json_encode($guisisFields, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</div>
                        </details>
                    </article>
                @endforeach
            </div>
        @elseif(in_array(($source ?? ''), ['guisis_addresses', 'guisis_personal_info'], true))
            <div class="api-results">
                @foreach($results as $result)
                    <article class="api-result-card">
                        <h3 style="margin: 0; color: #7f1d2d;">
                            {{ ($source ?? '') === 'guisis_addresses' ? 'GuiSIS Address Response' : 'GuiSIS Personal Info Response' }}
                        </h3>
                        <details class="api-raw-toggle" open>
                            <summary>Show raw response</summary>
                            <div class="api-json">{{ json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</div>
                        </details>
                    </article>
                @endforeach
            </div>
        @elseif(in_array(($source ?? ''), ['puptas_applicant', 'puptas_applicant_idp'], true))
            <div class="api-results">
                @foreach($results as $result)
                    @php
                        $applicantFirstName = $result['first_name'] ?? $result['firstname'] ?? null;
                        $applicantMiddleName = $result['middle_name'] ?? $result['middlename'] ?? null;
                        $applicantLastName = $result['last_name'] ?? $result['lastname'] ?? null;
                        $applicantDisplayName = trim(implode(' ', array_filter([$applicantFirstName, $applicantMiddleName, $applicantLastName])));
                        $applicantAddress = trim(implode(', ', array_filter([
                            $result['street_address'] ?? null,
                            $result['barangay'] ?? null,
                            $result['city'] ?? null,
                            $result['province'] ?? null,
                            $result['postal_code'] ?? null,
                        ])));
                    @endphp
                    <article class="api-result-card">
                        <h3 style="margin: 0; color: #7f1d2d;">{{ $applicantDisplayName ?: 'PUPTAS Applicant' }}</h3>
                        <div class="api-result-grid">
                            <div class="api-field">
                                <small>Student Number</small>
                                <strong>{{ $result['student_number'] ?? 'N/A' }}</strong>
                            </div>
                            <div class="api-field">
                                <small>IDP User ID</small>
                                <strong>{{ $result['idp_user_id'] ?? 'N/A' }}</strong>
                            </div>
                            <div class="api-field">
                                <small>Email</small>
                                <strong>{{ $result['email'] ?? 'N/A' }}</strong>
                            </div>
                            <div class="api-field">
                                <small>Birthday</small>
                                <strong>{{ $result['birthday'] ?? 'N/A' }}</strong>
                            </div>
                            <div class="api-field">
                                <small>Sex</small>
                                <strong>{{ $result['sex'] ?? 'N/A' }}</strong>
                            </div>
                            <div class="api-field">
                                <small>Contact Number</small>
                                <strong>{{ $result['contactnumber'] ?? 'N/A' }}</strong>
                            </div>
                            <div class="api-field">
                                <small>Program Code</small>
                                <strong>{{ data_get($result, 'program.code', 'N/A') }}</strong>
                            </div>
                            <div class="api-field">
                                <small>Program Name</small>
                                <strong>{{ data_get($result, 'program.name', 'N/A') }}</strong>
                            </div>
                            <div class="api-field">
                                <small>Application Status</small>
                                <strong>{{ data_get($result, 'application.status', 'N/A') }}</strong>
                            </div>
                            <div class="api-field">
                                <small>Medical Process</small>
                                <strong>{{ $result['medical_process_status'] ?? $result['lifecycle_status'] ?? 'N/A' }}</strong>
                            </div>
                            <div class="api-field">
                                <small>Address</small>
                                <strong>{{ $applicantAddress ?: 'N/A' }}</strong>
                            </div>
                        </div>

                        <details class="api-raw-toggle">
                            <summary>Show raw response</summary>
                            <div class="api-json">{{ json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</div>
                        </details>
                    </article>
                @endforeach
            </div>
        @else
            {{-- Unified Display for Other APIs (Custom / Admin API) --}}
            <div class="api-results">
                @foreach($results as $result)
                    <article class="api-result-card">
                        <h3 style="margin: 0; color: #7f1d2d;">{{ $result['name'] ?? 'N/A' }}</h3>
                        <div class="api-result-grid">
                            {{-- ... existing fields (ID, Name, Email, etc.) ... --}}
                            {{-- Siguraduhin lang na $result gamit mo dito, hindi $result['fields'] kung hindi consistent ang API --}}
                        </div>

                        <details class="api-raw-toggle">
                            <summary>Show raw response</summary>
                            <div class="api-json">{{ json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</div>
                        </details>
                    </article>
                @endforeach
            </div>
        @endif
    @elseif(($source ?? '') === 'database_info')
        <div class="api-db-list">
            @forelse(($databaseInfo ?? []) as $record)
                <article class="api-db-card">
                    <div class="api-db-head">
                        <div>
                            <h3>{{ $record['name'] ?: 'Unnamed Record' }}</h3>
                            <p>{{ $record['email'] ?: 'No email available' }}</p>
                        </div>
                        @if(\App\Models\User::normalizeRole(optional(auth()->user())->user_role ?? '') === \App\Models\User::ROLE_SUPERADMIN)
                            <div class="api-db-actions">
                                <button
                                    type="button"
                                    class="api-db-action-btn"
                                    data-db-edit
                                    data-table="{{ $dbTable ?? 'users' }}"
                                    data-id="{{ $record['id'] }}"
                                    data-raw='@json($record["raw"])'
                                >
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('admin.api-testing.database.delete', ['table' => $dbTable ?? 'users', 'id' => $record['id']]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="api-db-action-btn delete" onclick="return confirm('Delete this database record?')">Delete</button>
                                </form>
                            </div>
                        @endif
                    </div>
                    <div class="api-db-grid">
                        @foreach(($record['primary'] ?? []) as $label => $value)
                            <div class="api-field">
                                <small>{{ $label }}</small>
                                <strong>{{ $value ?: 'N/A' }}</strong>
                            </div>
                        @endforeach
                    </div>
                    <details class="api-raw-toggle">
                        <summary>Show raw response</summary>
                        <div class="api-json">{{ json_encode($record['raw'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</div>
                    </details>
                </article>
            @empty
                <p class="api-empty">No database records matched the current filter.</p>
            @endforelse
        </div>
    @else
        <p class="api-empty">Search results will appear here once you choose a source and enter a name, email, or ID.</p>
    @endif
        </div>
        <!-- End Tab 1: API Tests -->

        <!-- TAB 2: Health Monitor -->
        <div class="api-tab-content" id="tab-health">
            <button type="button" class="api-db-action-btn" id="refreshHealthBtn" style="margin-bottom: 20px;">
                🔄 Refresh Health Status
            </button>
            <div class="api-health-grid" id="healthGrid">
                <div class="api-loading">
                    <div class="api-loading-spinner"></div>
                    <p>Checking system health...</p>
                </div>
            </div>
        </div>
        <!-- End Tab 2: Health Monitor -->

        <!-- TAB 3: Error Log -->
        <div class="api-tab-content" id="tab-errors">
            <div style="display: grid; grid-template-columns: auto auto auto 1fr auto; gap: 12px; margin-bottom: 20px; align-items: end;">
                <div>
                    <label for="errorHours" style="display: block; margin-bottom: 6px; font-weight: 700; color: #374151;">Hours</label>
                    <select id="errorHours" style="border-radius: 12px; border: 1px solid rgba(127, 29, 45, 0.16); padding: 8px 12px;">
                        <option value="1">Last 1 Hour</option>
                        <option value="6">Last 6 Hours</option>
                        <option value="24" selected>Last 24 Hours</option>
                        <option value="168">Last 7 Days</option>
                    </select>
                </div>
                <div>
                    <label for="errorSystem" style="display: block; margin-bottom: 6px; font-weight: 700; color: #374151;">System</label>
                    <select id="errorSystem" style="border-radius: 12px; border: 1px solid rgba(127, 29, 45, 0.16); padding: 8px 12px;">
                        <option value="">All Systems</option>
                        <option value="pupt">PUPT</option>
                        <option value="dental">Dental</option>
                        <option value="sis">SIS</option>
                        <option value="puptas">PUPTAS</option>
                        <option value="guisis">GuiSIS</option>
                        <option value="one_portal">One Portal</option>
                    </select>
                </div>
                <button type="button" class="api-db-action-btn" id="loadErrorsBtn">
                    Load Errors
                </button>
                <div id="errorStats" style="text-align: right; color: #6b7280; font-size: 13px;"></div>
            </div>
            <table class="api-error-log-table" id="errorTable">
                <thead>
                    <tr>
                        <th>System</th>
                        <th>Endpoint</th>
                        <th>Error Code</th>
                        <th>Affected Account</th>
                        <th>Attempts</th>
                        <th>Message</th>
                        <th>Response Time</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody id="errorBody">
                    <tr><td colspan="8" style="text-align: center; color: #6b7280;">Click "Load Errors" to fetch error logs</td></tr>
                </tbody>
            </table>
        </div>
        <!-- End Tab 3: Error Log -->

        <!-- TAB 4: System Status -->
        <div class="api-tab-content" id="tab-systems">
            <div class="api-health-grid" id="systemsGrid">
                <div class="api-loading">
                    <div class="api-loading-spinner"></div>
                    <p>Loading system status...</p>
                </div>
            </div>
        </div>
        <!-- End Tab 4: System Status -->
    </section>
</div>

<div class="api-pin-modal" id="integrationPinModal" aria-hidden="true">
    <section class="api-pin-dialog" role="dialog" aria-modal="true" aria-labelledby="integrationPinTitle">
        <header class="api-pin-head">
            <div class="api-pin-head-main">
                <span class="api-pin-head-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2.75 4.75 5.5v5.4c0 4.72 3.02 8.92 7.25 10.35 4.23-1.43 7.25-5.63 7.25-10.35V5.5L12 2.75Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.25 11.25v-1.5a3.25 3.25 0 1 0-6.5 0v1.5m-.5 0h7.5v5.5h-7.5v-5.5Z" />
                    </svg>
                </span>
                <div>
                    <h3 id="integrationPinTitle">Integration PIN</h3>
                    <p>Enter your 4-digit PIN to access Integration Tokens.</p>
                </div>
            </div>
            <button type="button" class="api-pin-close" id="closeIntegrationPinModal" aria-label="Close PIN modal">&times;</button>
        </header>
        <form class="api-pin-body" id="integrationPinForm">
            <div class="api-pin-warning">
                <span class="api-pin-warning-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.75A11.959 11.959 0 0 1 12 2.714Z" />
                    </svg>
                </span>
                <div>
                    <strong>Administrator Verification Required</strong>
                    <span>Only authorized administrators can access Integration Tokens.</span>
                </div>
            </div>
            <div class="api-pin-entry">
                <span class="api-pin-entry-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 0 0-9 0v3.75m-.75 0h10.5c.621 0 1.125.504 1.125 1.125v7.5c0 .621-.504 1.125-1.125 1.125H6.75a1.125 1.125 0 0 1-1.125-1.125v-7.5c0-.621.504-1.125 1.125-1.125Z" />
                    </svg>
                </span>
                <div class="api-pin-entry-title">Enter 4-Digit PIN</div>
                <div class="api-pin-entry-copy">Please enter your 4-digit Integration PIN.</div>
                <div class="api-pin-digits" id="integrationPinDigits">
                    <input type="password" inputmode="numeric" maxlength="1" autocomplete="one-time-code" aria-label="PIN digit 1" required>
                    <input type="password" inputmode="numeric" maxlength="1" autocomplete="one-time-code" aria-label="PIN digit 2" required>
                    <input type="password" inputmode="numeric" maxlength="1" autocomplete="one-time-code" aria-label="PIN digit 3" required>
                    <input type="password" inputmode="numeric" maxlength="1" autocomplete="one-time-code" aria-label="PIN digit 4" required>
                </div>
                <input class="api-pin-hidden" type="password" id="integrationPinInput" name="pin" pattern="[0-9]{4}" maxlength="4" tabindex="-1">
                <div class="api-pin-reset-row">
                    Your PIN is encrypted and safe.
                </div>
            </div>
            <div class="api-pin-error" id="integrationPinError"></div>
            <div class="api-pin-actions">
                <button type="button" class="api-pin-cancel" id="cancelIntegrationPin">Cancel</button>
                <button type="submit" class="api-pin-submit">
                    <svg aria-hidden="true" width="17" height="17" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 0 0-9 0v3.75m-.75 0h10.5c.621 0 1.125.504 1.125 1.125v7.5c0 .621-.504 1.125-1.125 1.125H6.75a1.125 1.125 0 0 1-1.125-1.125v-7.5c0-.621.504-1.125 1.125-1.125Z" />
                    </svg>
                    Verify PIN
                </button>
            </div>
        </form>
    </section>
</div>

<div class="api-edit-modal" id="databaseEditModal">
    <div class="api-edit-content">
        <div class="api-testing-head">
            <h2>Edit Database Record</h2>
            <p>Temporary local editor for API Testing.</p>
        </div>
        <form method="POST" id="databaseEditForm">
            @csrf
            @method('PUT')
            <div class="api-edit-grid" id="databaseEditFields"></div>
            <div class="api-edit-actions">
                <button type="button" class="api-db-action-btn" id="closeDatabaseEditModal">Cancel</button>
                <button type="submit" class="api-db-action-btn" style="background:linear-gradient(135deg,#7f1d2d,#5b0c0e);color:#fff;">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // ===== TAB SWITCHING =====
        const tabButtons = document.querySelectorAll('.api-tab-button');
        const tabContents = document.querySelectorAll('.api-tab-content');

        tabButtons.forEach(button => {
            button.addEventListener('click', (event) => {
                const tabName = button.dataset.tab;
                if (!tabName) {
                    return;
                }

                tabButtons.forEach(b => b.classList.remove('is-active'));
                tabContents.forEach(c => c.classList.remove('is-active'));

                button.classList.add('is-active');
                document.getElementById(`tab-${tabName}`).classList.add('is-active');

                if (tabName === 'health') {
                    loadHealthMonitor();
                } else if (tabName === 'systems') {
                    loadSystemStatus();
                }
            });
        });

        const integrationTokensButton = document.getElementById('integrationTokensGateButton');
        const integrationPinModal = document.getElementById('integrationPinModal');
        const integrationPinForm = document.getElementById('integrationPinForm');
        const integrationPinInput = document.getElementById('integrationPinInput');
        const integrationPinDigits = Array.from(document.querySelectorAll('#integrationPinDigits input'));
        const integrationPinError = document.getElementById('integrationPinError');
        const closeIntegrationPinModal = document.getElementById('closeIntegrationPinModal');
        const cancelIntegrationPin = document.getElementById('cancelIntegrationPin');

        function syncIntegrationPinInput() {
            if (!integrationPinInput) return;
            integrationPinInput.value = integrationPinDigits.map((input) => input.value).join('');
        }

        function clearIntegrationPinInputs() {
            integrationPinDigits.forEach((input) => {
                input.value = '';
            });
            syncIntegrationPinInput();
        }

        function setIntegrationPinModalOpen(isOpen) {
            if (!integrationPinModal) return;
            integrationPinModal.classList.toggle('is-open', isOpen);
            integrationPinModal.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
            if (isOpen) {
                integrationPinError?.classList.remove('is-visible');
                if (integrationPinError) integrationPinError.textContent = '';
                window.setTimeout(() => integrationPinDigits[0]?.focus(), 80);
            } else {
                clearIntegrationPinInputs();
            }
        }

        integrationPinDigits.forEach((input, index) => {
            input.addEventListener('input', () => {
                input.value = input.value.replace(/\D/g, '').slice(0, 1);
                syncIntegrationPinInput();

                if (input.value && integrationPinDigits[index + 1]) {
                    integrationPinDigits[index + 1].focus();
                }
            });

            input.addEventListener('keydown', (event) => {
                if (event.key === 'Backspace' && !input.value && integrationPinDigits[index - 1]) {
                    integrationPinDigits[index - 1].focus();
                }
            });

            input.addEventListener('paste', (event) => {
                event.preventDefault();
                const pasted = (event.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, integrationPinDigits.length);
                pasted.split('').forEach((value, pasteIndex) => {
                    if (integrationPinDigits[pasteIndex]) {
                        integrationPinDigits[pasteIndex].value = value;
                    }
                });
                syncIntegrationPinInput();
                integrationPinDigits[Math.min(pasted.length, integrationPinDigits.length) - 1]?.focus();
            });
        });

        integrationTokensButton?.addEventListener('click', async function (event) {
            event.preventDefault();

            if (this.dataset.pinDisabled === '1') {
                setIntegrationPinModalOpen(false);
                alert('Integration Tokens access is disabled in Developer Options.');
                return;
            }

            try {
                const statusResponse = await fetch('{{ route('admin.integration-pin.status') }}', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                    }
                });
                const statusData = await statusResponse.json();
                const state = statusData.state || {};

                this.dataset.pinDisabled = state.disabled ? '1' : '0';
                this.dataset.pinEnabled = state.page_pin_enabled ? '1' : '0';

                if (state.disabled) {
                    setIntegrationPinModalOpen(false);
                    alert('Integration Tokens access is disabled in Developer Options.');
                    return;
                }

                if (state.page_pin_enabled) {
                    setIntegrationPinModalOpen(true);
                    return;
                }
            } catch (error) {
                setIntegrationPinModalOpen(false);
                alert('Unable to check Integration PIN status. Please try again.');
                return;
            }

            window.location.href = this.href;
        });

        integrationPinForm?.addEventListener('submit', function (event) {
            event.preventDefault();
            syncIntegrationPinInput();
            const pin = (integrationPinInput?.value || '').trim();

            if (!/^\d{4}$/.test(pin)) {
                if (integrationPinError) {
                    integrationPinError.textContent = 'Enter a valid 4-digit Integration PIN.';
                    integrationPinError.classList.add('is-visible');
                }
                integrationPinDigits.find((input) => !input.value)?.focus();
                return;
            }

            fetch('{{ route('admin.integration-pin.verify') }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                },
                body: JSON.stringify({ pin, purpose: 'open_integration_tokens' })
            })
                .then(async response => {
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Unable to verify Integration PIN.');
                    }
                    window.location.href = integrationTokensButton.href;
                })
                .catch(error => {
                    if (integrationPinError) {
                        integrationPinError.textContent = error.message;
                        integrationPinError.classList.add('is-visible');
                    }
                });
        });

        closeIntegrationPinModal?.addEventListener('click', () => setIntegrationPinModalOpen(false));
        cancelIntegrationPin?.addEventListener('click', () => setIntegrationPinModalOpen(false));
        integrationPinModal?.addEventListener('click', function (event) {
            if (event.target === integrationPinModal) {
                setIntegrationPinModalOpen(false);
            }
        });

        // ===== HEALTH MONITOR =====
        function loadHealthMonitor() {
            const grid = document.getElementById('healthGrid');
            grid.innerHTML = '<div class="api-loading"><div class="api-loading-spinner"></div><p>Checking system health...</p></div>';

            fetch('/admin/api/health-monitor')
                .then(r => r.json())
                .then(data => {
                    grid.innerHTML = '';
                    Object.entries(data).forEach(([key, status]) => {
                        const statusClass = status.status.toLowerCase();
                        const statusEmoji = {
                            'healthy': '✅',
                            'unhealthy': '⚠️',
                            'down': '❌',
                            'unconfigured': '⚙️'
                        }[statusClass] || '❓';

                        const card = document.createElement('div');
                        card.className = 'api-health-card';
                        card.innerHTML = `
                            <h4>${key.toUpperCase()}</h4>
                            <div class="api-health-status">
                                <span class="api-health-badge ${statusClass}">${statusEmoji} ${status.status}</span>
                            </div>
                            <small>${status.message}</small>
                            <small style="margin-top: 8px; color: #9ca3af;">Response: ${status.response_time}ms</small>
                            <small style="margin-top: 4px; color: #9ca3af;">Last check: ${status.last_check || 'N/A'}</small>
                        `;
                        grid.appendChild(card);
                    });
                })
                .catch(e => {
                    grid.innerHTML = `<div class="api-alert">Failed to load health status: ${e.message}</div>`;
                });
        }

        // ===== ERROR LOG =====
        const escapeApiLogValue = (value) => String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');

        document.getElementById('loadErrorsBtn')?.addEventListener('click', function () {
            const hours = document.getElementById('errorHours').value;
            const system = document.getElementById('errorSystem').value;
            const body = document.getElementById('errorBody');
            const stats = document.getElementById('errorStats');

            body.innerHTML = '<tr><td colspan="8"><div class="api-loading"><div class="api-loading-spinner"></div>Loading errors...</div></td></tr>';

            const url = new URL('/admin/api/error-logs', window.location.origin);
            url.searchParams.append('hours', hours);
            if (system) url.searchParams.append('system', system);

            fetch(url)
                .then(r => r.json())
                .then(data => {
                    const errors = data.errors || [];
                    const errorStats = data.stats || {};
                    const puptasSummary = data.puptas_summary || {};

                    if (errors.length === 0) {
                        body.innerHTML = '<tr><td colspan="8" style="text-align: center; color: #6b7280;">No errors found</td></tr>';
                        stats.innerHTML = '';
                        return;
                    }

                    body.innerHTML = '';
                    errors.forEach(err => {
                        let requestMeta = {};
                        try {
                            requestMeta = JSON.parse(err.request_payload || '{}');
                        } catch (error) {
                            requestMeta = {};
                        }
                        const affectedAccount = requestMeta.user_id ? `User #${requestMeta.user_id}` : 'System';
                        const attempts = requestMeta.attempts || 'N/A';
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td><strong>${escapeApiLogValue(err.system_name)}</strong></td>
                            <td><code style="font-size: 11px; color: #7f1d2d;">${escapeApiLogValue(err.endpoint || 'N/A')}</code></td>
                            <td><code style="font-size: 11px;">${escapeApiLogValue(err.error_code || 'N/A')}</code></td>
                            <td>${escapeApiLogValue(affectedAccount)}</td>
                            <td>${escapeApiLogValue(attempts)}</td>
                            <td>
                                ${escapeApiLogValue(err.error_message)}
                                <details style="margin-top: 6px;">
                                    <summary style="cursor: pointer; color: #7f1d2d; font-size: 12px;">Details</summary>
                                    <pre style="font-size: 11px; background: #f3f4f6; padding: 8px; border-radius: 6px; overflow: auto; max-height: 150px;">Error Type: ${escapeApiLogValue(err.error_type || 'N/A')}
HTTP Status: ${escapeApiLogValue(err.http_status || 'N/A')}
Request: ${escapeApiLogValue(err.request_payload || 'N/A')}</pre>
                                </details>
                            </td>
                            <td>${err.response_time_ms ? err.response_time_ms + 'ms' : 'N/A'}</td>
                            <td><small>${new Date(err.created_at).toLocaleString()}</small></td>
                        `;
                        body.appendChild(row);
                    });

                    let statsHtml = `Total: ${errors.length} errors`;
                    if (Object.keys(errorStats).length > 0) {
                        statsHtml += '<br>';
                        Object.entries(errorStats).forEach(([sys, stat]) => {
                            statsHtml += `${sys}: ${stat.error_count} | `;
                        });
                    }
                    if (puptasSummary.failure_count) {
                        statsHtml += `<br>PUPTAS failures: ${puptasSummary.failure_count} | Affected accounts: ${puptasSummary.affected_account_count || 0}`;
                    }
                    stats.innerHTML = statsHtml;
                })
                .catch(e => {
                    body.innerHTML = `<tr><td colspan="8"><div class="api-alert">Failed to load errors: ${escapeApiLogValue(e.message)}</div></td></tr>`;
                });
        });

        // ===== SYSTEM STATUS =====
        function loadSystemStatus() {
            const grid = document.getElementById('systemsGrid');
            grid.innerHTML = '<div class="api-loading"><div class="api-loading-spinner"></div><p>Loading system status...</p></div>';

            fetch('/admin/api/system-status')
                .then(r => r.json())
                .then(data => {
                    grid.innerHTML = '';
                    Object.entries(data).forEach(([key, sys]) => {
                        const isConfigured = sys.configured;
                        const statusEmoji = isConfigured ? '✅' : '⚙️';
                        const statusClass = isConfigured ? 'healthy' : 'unconfigured';

                        const card = document.createElement('div');
                        card.className = 'api-health-card';

                        let content = `
                            <h4>${sys.name}</h4>
                            <div class="api-health-status">
                                <span class="api-health-badge ${statusClass}">${statusEmoji} ${isConfigured ? 'Configured' : 'Not Configured'}</span>
                            </div>
                        `;

                        if (sys.endpoint) {
                            content += `<small><strong>Endpoint:</strong><br><code style="font-size: 10px; word-break: break-all;">${sys.endpoint}</code></small>`;
                        }

                        if (sys.timeout) {
                            content += `<small style="margin-top: 6px;"><strong>Timeout:</strong> ${sys.timeout}s</small>`;
                        }

                        if (sys.system_id) {
                            content += `<small style="margin-top: 6px;"><strong>System ID:</strong> ${sys.system_id}</small>`;
                        }

                        if (sys.client_id) {
                            content += `<small style="margin-top: 6px;"><strong>Client ID:</strong> <code style="font-size: 10px;">${sys.client_id}</code></small>`;
                        }

                        if (sys.auth_method) {
                            content += `<small style="margin-top: 6px;"><strong>Auth:</strong> ${sys.auth_method}</small>`;
                        }

                        if (sys.systems && sys.systems.length > 0) {
                            content += `<small style="margin-top: 6px;"><strong>External Systems:</strong> ${sys.systems.join(', ')}</small>`;
                        }

                        if (sys.header) {
                            content += `<small style="margin-top: 6px;"><strong>Header:</strong> ${sys.header}</small>`;
                        }

                        card.innerHTML = content;
                        grid.appendChild(card);
                    });
                })
                .catch(e => {
                    grid.innerHTML = `<div class="api-alert">Failed to load system status: ${e.message}</div>`;
                });
        }

        // ===== REFRESH HEALTH BUTTON =====
        document.getElementById('refreshHealthBtn')?.addEventListener('click', function () {
            this.disabled = true;
            this.textContent = '🔄 Refreshing...';
            loadHealthMonitor();
            setTimeout(() => {
                this.disabled = false;
                this.textContent = '🔄 Refresh Health Status';
            }, 2000);
        });

        // ===== ORIGINAL API TESTS CODE =====
        const form = document.getElementById('apiTestingForm');
        const sourceField = document.getElementById('source');
        const searchField = document.getElementById('search');
        const systemField = document.getElementById('system');
        const systemGroup = document.getElementById('apiSystemGroup');
        const dbEditModal = document.getElementById('databaseEditModal');
        const dbEditForm = document.getElementById('databaseEditForm');
        const dbEditFields = document.getElementById('databaseEditFields');
        const closeDbEditModal = document.getElementById('closeDatabaseEditModal');

        if (!form || !sourceField || !searchField || !systemField || !systemGroup) return;

        let hasAutoSubmitted = false;
        const syncSystemVisibility = () => {
            const needsSystem = ['admin_api', 'admin_options'].includes(sourceField.value);
            systemField.disabled = !needsSystem;
            systemGroup.classList.toggle('is-hidden', !needsSystem);

            if (!needsSystem) {
                systemField.value = '';
            }
        };

        // Auto-submit logic kapag nag-focus sa search field for specific sources
        searchField.addEventListener('focus', function () {
            const source = sourceField.value;
            const shouldAutoLoad = ['admin_api', 'admin_options'].includes(source);

            if (!shouldAutoLoad || searchField.value.trim() !== '' || hasAutoSubmitted) return;

            hasAutoSubmitted = true;
            form.requestSubmit();
        });

        sourceField.addEventListener('change', function () {
            syncSystemVisibility();
            const searchLabel = document.querySelector('label[for="search"]');
            const isPuptasApplicant = sourceField.value === 'puptas_applicant';
            const isPuptasApplicantIdp = sourceField.value === 'puptas_applicant_idp';
            const isGuisisProfile = sourceField.value === 'guisis_profile';
            const isGuisisStudentNumber = ['guisis_student', 'guisis_addresses', 'guisis_personal_info'].includes(sourceField.value);
            if (searchLabel) {
                searchLabel.textContent = isPuptasApplicant
                    ? 'Search by Student Number'
                    : (isPuptasApplicantIdp
                        ? 'Search by IDP User ID'
                        : (isGuisisProfile ? 'Search by Student Email' : (isGuisisStudentNumber ? 'Search by Student Number' : 'Search by name, email, or ID')));
            }
            searchField.placeholder = isPuptasApplicant
                ? 'Try a student number'
                : (isPuptasApplicantIdp
                    ? 'Try an IDP user ID'
                    : (isGuisisProfile ? 'Try a student email address' : (isGuisisStudentNumber ? 'Try a student number' : 'Try a name, email address, or identifier')));
        });

        syncSystemVisibility();

        const fieldSets = {
            users: [
                { name: 'first_name', label: 'First Name', type: 'text' },
                { name: 'last_name', label: 'Last Name', type: 'text' },
                { name: 'email', label: 'Email', type: 'email' },
                { name: 'student_id', label: 'Student ID', type: 'text' },
                { name: 'student_number', label: 'Student Number', type: 'text' },
                { name: 'gender', label: 'Gender', type: 'text' },
                { name: 'user_role', label: 'Role', type: 'select', options: ['student', 'student_assistant', 'admin', 'superadmin'] },
                { name: 'status', label: 'Status', type: 'select', options: ['active', 'inactive'] },
            ],
            admins: [
                { name: 'first_name', label: 'First Name', type: 'text' },
                { name: 'last_name', label: 'Last Name', type: 'text' },
                { name: 'email', label: 'Email', type: 'email' },
                { name: 'office', label: 'Office', type: 'text' },
                { name: 'access_level', label: 'Access Level', type: 'text' },
                { name: 'status', label: 'Status', type: 'select', options: ['active', 'inactive'] },
            ]
        };

        document.querySelectorAll('[data-db-edit]').forEach((button) => {
            button.addEventListener('click', () => {
                if (!dbEditModal || !dbEditForm || !dbEditFields) return;
                const table = button.dataset.table || 'users';
                const id = button.dataset.id;
                let raw = {};
                try {
                    raw = JSON.parse(button.dataset.raw || '{}') || {};
                } catch (error) {
                    raw = {};
                }

                dbEditForm.action = `{{ url('/admin/api-testing/database') }}/${table}/${id}`;
                dbEditFields.innerHTML = '';

                (fieldSets[table] || []).forEach((field) => {
                    const wrap = document.createElement('div');
                    wrap.className = 'api-edit-field';

                    const label = document.createElement('label');
                    label.textContent = field.label;
                    wrap.appendChild(label);

                    let control;
                    if (field.type === 'select') {
                        control = document.createElement('select');
                        field.options.forEach((optionValue) => {
                            const option = document.createElement('option');
                            option.value = optionValue;
                            option.textContent = optionValue;
                            if ((raw[field.name] || '') === optionValue) {
                                option.selected = true;
                            }
                            control.appendChild(option);
                        });
                    } else {
                        control = document.createElement('input');
                        control.type = field.type;
                        control.value = raw[field.name] || '';
                    }
                    control.name = field.name;
                    wrap.appendChild(control);
                    dbEditFields.appendChild(wrap);
                });

                dbEditModal.classList.add('show');
            });
        });

        if (closeDbEditModal && dbEditModal) {
            closeDbEditModal.addEventListener('click', () => dbEditModal.classList.remove('show'));
            dbEditModal.addEventListener('click', (event) => {
                if (event.target === dbEditModal) {
                    dbEditModal.classList.remove('show');
                }
            });
        }

        // Handler para sa Admin Options & Admin API (Unified)
        const handleAdminSelection = (button) => {
            const fields = {
                'selectedFirstName': button.dataset.firstName,
                'selectedLastName': button.dataset.lastName,
                'selectedSuffixName': button.dataset.suffixName,
                'selectedEmail': button.dataset.email,
                'selectedStatus': button.dataset.status
            };

            Object.keys(fields).forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = fields[id] || '';
            });
        };

        // Handler para sa Faculty Selection
        const handleFacultySelection = (button) => {
            const fields = {
                'selectedFacultyIdentifier': button.dataset.identifier,
                'selectedFacultyFirstName': button.dataset.firstName,
                'selectedFacultyMiddleName': button.dataset.middleName,
                'selectedFacultyLastName': button.dataset.lastName,
                'selectedFacultySuffixName': button.dataset.suffixName,
                'selectedFacultyEmail': button.dataset.email,
                'selectedFacultyStatus': button.dataset.status,
                'selectedFacultyOffice': button.dataset.office
            };

            Object.keys(fields).forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = fields[id] || '';
            });
        };

        // Event Delegation para mas malinis at gumana kahit dynamic ang results
        document.addEventListener('click', function (e) {
            const adminBtn = e.target.closest('.admin-option-item');
            const facultyBtn = e.target.closest('.faculty-option-item');

            if (adminBtn) handleAdminSelection(adminBtn);
            if (facultyBtn) handleFacultySelection(facultyBtn);
        });

        const firstFacultyButton = document.querySelector('.faculty-option-item');
        if (firstFacultyButton) {
            handleFacultySelection(firstFacultyButton);
        }
    });
</script>
@endpush
