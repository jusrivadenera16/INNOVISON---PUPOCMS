@extends('layouts.admin')

@section('title', 'Audit Trail')

@push('styles')
<style>
    .audit-wrap {
        display: grid;
        gap: 18px;
    }

    .audit-card {
        position: relative;
        background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(248,250,252,0.98));
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 20px;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
        padding: 20px;
        overflow: hidden;
    }

    .audit-card::before {
        content: "";
        position: absolute;
        inset: 0 auto 0 0;
        width: 4px;
        background: linear-gradient(180deg, #facc15, #8B0000 70%, #5e0000);
        opacity: 0.9;
    }

    .audit-card,
    .audit-card *:not(.audit-role-badge):not(.audit-event-badge):not(.audit-status-badge):not(.audit-btn-primary):not(.audit-stat-value) {
        color: #111827;
    }

    .audit-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        flex-wrap: wrap;
    }

    .audit-head-main {
        display: grid;
        gap: 8px;
        max-width: 760px;
    }

    .audit-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        width: fit-content;
        padding: 7px 11px;
        border-radius: 999px;
        background: rgba(139, 0, 0, 0.08);
        border: 1px solid rgba(139, 0, 0, 0.14);
        color: #8B0000;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .audit-kicker span {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #facc15;
        box-shadow: 0 0 0 4px rgba(250, 204, 21, 0.14);
    }

    .audit-title {
        margin: 0;
        color: #0f172a;
        font-size: 26px;
        font-weight: 900;
        line-height: 1.08;
    }

    .audit-subtitle {
        margin: 0;
        color: #475569;
        font-size: 14px;
        line-height: 1.7;
        max-width: 72ch;
    }

    .audit-head-aside {
        display: grid;
        gap: 8px;
        justify-items: end;
        min-width: 220px;
    }

    .audit-head-note {
        color: #64748b;
        font-size: 12px;
        line-height: 1.5;
        text-align: right;
    }

    .audit-chip-row {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 8px;
    }

    .audit-chip {
        padding: 7px 10px;
        border-radius: 999px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #334155;
        font-size: 11px;
        font-weight: 700;
    }

    .audit-card-link {
        display: block;
        text-decoration: none;
    }

    .audit-stats {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 12px;
    }

    .audit-stat {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 16px;
        padding: 15px 16px;
        background: linear-gradient(180deg, #ffffff, #f8fafc);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.88);
    }

    .audit-stat::after {
        content: "";
        position: absolute;
        inset: auto 0 0 0;
        height: 3px;
        background: linear-gradient(90deg, #facc15, #8B0000, #5e0000);
        opacity: 0.85;
    }

    .audit-stat-label {
        color: #475569;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 800;
        margin-bottom: 8px;
    }

    .audit-stat-value {
        color: #0f172a;
        font-size: 28px;
        font-weight: 900;
        line-height: 1;
    }

    .audit-filters {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .audit-filter-group {
        display: grid;
        gap: 6px;
    }

    .audit-filter-label {
        color: #475569;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 800;
    }

    .audit-input,
    .audit-select {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 10px 12px;
        font-size: 13px;
        color: #0f172a;
        background: #fff;
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .audit-input:focus,
    .audit-select:focus {
        outline: none;
        border-color: #8B0000;
        box-shadow: 0 0 0 4px rgba(139, 0, 0, 0.08);
        transform: translateY(-1px);
    }

    .premium-select-native {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        opacity: 0 !important;
        pointer-events: none !important;
    }

    .premium-select-shell {
        position: relative;
        width: 100%;
        min-width: 132px;
        z-index: 20;
    }

    .premium-select-button {
        position: relative;
        width: 100%;
        min-height: 42px;
        border: 1px solid rgba(112, 19, 27, 0.22);
        border-radius: 14px;
        background: #fff;
        color: #111827;
        padding: 0 42px 0 14px;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        font-size: 13px;
        font-weight: 800;
        text-align: left;
        cursor: pointer;
        box-shadow: 0 10px 18px rgba(15, 23, 42, 0.06);
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .premium-select-button::after {
        content: "";
        position: absolute;
        right: 15px;
        width: 10px;
        height: 10px;
        border-right: 2px solid #8B0000;
        border-bottom: 2px solid #8B0000;
        transform: rotate(45deg);
        transition: transform .18s ease;
    }

    .premium-select-shell.is-open .premium-select-button {
        border-color: #8B0000;
        box-shadow: 0 0 0 4px rgba(139, 0, 0, .08);
    }

    .premium-select-shell.is-open .premium-select-button::after {
        transform: rotate(225deg);
    }

    .premium-select-menu {
        position: absolute;
        z-index: 50;
        top: calc(100% + 8px);
        left: 0;
        right: 0;
        max-height: 230px;
        overflow-y: auto;
        padding: 8px;
        border: 1px solid rgba(112, 19, 27, .16);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 22px 46px rgba(15, 23, 42, .18);
        display: none;
    }

    .premium-select-shell.is-open .premium-select-menu {
        display: grid;
        gap: 7px;
    }

    .premium-select-option {
        border: 1px solid rgba(148, 163, 184, .20);
        border-radius: 999px;
        background: #f8fafc;
        color: #111827;
        padding: 10px 14px;
        font-size: 12px;
        font-weight: 900;
        text-align: left;
        cursor: pointer;
        transition: background .18s ease, color .18s ease, transform .18s ease, border-color .18s ease;
    }

    .premium-select-option:hover,
    .premium-select-option.is-selected {
        background: #8B0000;
        color: #ffd700;
        border-color: #8B0000;
        transform: translateY(-1px);
    }

    .audit-filter-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin-top: 6px;
    }

    .audit-btn {
        border: 1px solid transparent;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 800;
        padding: 9px 13px;
        text-decoration: none;
        cursor: pointer;
        transition: transform .18s ease, background .18s ease, border-color .18s ease, color .18s ease, box-shadow .18s ease;
    }

    .audit-btn:hover {
        transform: translateY(-1px);
    }

    .audit-btn-primary {
        background: linear-gradient(135deg, #5e0000, #8B0000 60%, #a61b1b);
        color: #fff;
        box-shadow: 0 12px 22px rgba(91,0,0,0.18);
    }

    .audit-btn-primary:hover {
        background: #facc15;
        color: #8B0000;
    }

    .audit-btn-light {
        background: #fff;
        border-color: #cbd5e1;
        color: #334155;
    }

    .audit-btn-light:hover {
        border-color: #94a3b8;
        background: #f8fafc;
    }

    .audit-breakdowns {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .audit-collapsible {
        padding: 0;
    }

    .audit-collapsible details {
        position: relative;
        z-index: 1;
    }

    .audit-collapse-summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 20px;
        cursor: pointer;
        user-select: none;
        color: #0f172a;
        font-size: 14px;
        font-weight: 900;
        list-style: none;
    }

    .audit-collapse-summary::-webkit-details-marker {
        display: none;
    }

    .audit-collapse-summary::after {
        content: "";
        width: 10px;
        height: 10px;
        border-right: 2px solid currentColor;
        border-bottom: 2px solid currentColor;
        transform: rotate(45deg);
        transition: transform .18s ease;
        flex: 0 0 auto;
    }

    .audit-collapsible details[open] .audit-collapse-summary::after {
        transform: rotate(225deg) translate(-2px, -2px);
    }

    .audit-collapse-summary:hover {
        color: #8B0000;
    }

    .audit-collapse-summary-sub {
        color: #64748b;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .audit-collapse-body {
        padding: 0 20px 20px;
    }

    .audit-list-title {
        margin: 0;
        color: #0f172a;
        font-size: 14px;
        font-weight: 900;
    }

    .audit-mini-list {
        margin: 0;
        padding: 0;
        list-style: none;
        display: grid;
        gap: 8px;
    }

    .audit-mini-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 10px 12px;
        gap: 8px;
    }

    .audit-mini-label {
        color: #111827;
        font-size: 12px;
        font-weight: 700;
    }

    .audit-mini-count {
        color: #8B0000;
        font-size: 12px;
        font-weight: 900;
    }

    .audit-table-wrap {
        overflow-x: auto;
        border-radius: 16px;
    }

    .audit-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 10px;
        min-width: 1100px;
    }

    .audit-table thead th {
        text-align: left;
        font-size: 11px;
        text-transform: uppercase;
        color: #64748b;
        font-weight: 900;
        letter-spacing: 0.08em;
        padding: 0 12px 10px;
    }

    .audit-table tbody tr {
        background: linear-gradient(180deg, #ffffff, #f8fafc);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
        transition: transform .18s ease, box-shadow .18s ease;
    }

    .audit-table tbody tr:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 30px rgba(15, 23, 42, 0.09);
    }

    .audit-table td {
        border-top: 1px solid #edf2f7;
        border-bottom: 1px solid #edf2f7;
        padding: 12px;
        vertical-align: top;
        font-size: 13px;
        color: #1e293b;
        background: transparent;
    }

    .audit-table td:first-child {
        border-left: 1px solid #edf2f7;
        border-top-left-radius: 14px;
        border-bottom-left-radius: 14px;
    }

    .audit-table td:last-child {
        border-right: 1px solid #edf2f7;
        border-top-right-radius: 14px;
        border-bottom-right-radius: 14px;
    }

    .audit-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: linear-gradient(135deg, #fee2e2, #fef3c7);
        color: #8B0000;
        font-size: 12px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .audit-user {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .audit-role-badge,
    .audit-event-badge,
    .audit-status-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 3px 10px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .audit-role-admin { background: #dbeafe; color: #1e40af; }
    .audit-role-superadmin { background: #ede9fe; color: #5b21b6; }
    .audit-role-super_admin { background: #ede9fe; color: #5b21b6; }
    .audit-role-student_assistant { background: #dbeafe; color: #1e40af; }
    .audit-role-student { background: #dcfce7; color: #166534; }
    .audit-role-unknown { background: #e2e8f0; color: #334155; }

    .audit-event-view { background: #e0f2fe; color: #0369a1; }
    .audit-event-create { background: #dcfce7; color: #166534; }
    .audit-event-update { background: #fef3c7; color: #92400e; }
    .audit-event-delete { background: #fee2e2; color: #991b1b; }
    .audit-event-auth { background: #ede9fe; color: #5b21b6; }
    .audit-event-error { background: #fecaca; color: #7f1d1d; }
    .audit-event-action { background: #e2e8f0; color: #334155; }

    .audit-status-ok { background: #dcfce7; color: #166534; }
    .audit-status-failed { background: #fee2e2; color: #991b1b; }
    .audit-status-unknown { background: #e2e8f0; color: #334155; }

    .audit-mono {
        font-family: Consolas, "Courier New", monospace;
        font-size: 12px;
        color: #334155;
    }

    .audit-muted {
        color: #64748b;
    }

    .audit-empty {
        text-align: center;
        color: #64748b;
        font-size: 13px;
        padding: 28px 12px;
    }

    .audit-pagination {
        margin-top: 18px;
        padding: 14px 18px;
        display: flex;
        justify-content: center;
        border: 1px solid #edf2f7;
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
    }
    .audit-pagination nav {
        width: 100%;
    }
    .audit-pagination nav > div:first-child {
        display: none;
    }
    .audit-pagination nav > div:last-child {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }
    .audit-pagination nav p {
        margin: 0;
        color: #475569;
        font-size: 13px;
        font-weight: 700;
    }
    .audit-pagination nav a,
    .audit-pagination nav span[aria-current] span,
    .audit-pagination nav span:not([aria-current]) {
        min-width: 38px !important;
        height: 38px !important;
        max-height: 38px !important;
        padding: 0 14px !important;
        border-radius: 10px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 12px !important;
        font-weight: 900 !important;
        line-height: 1 !important;
    }
    .audit-pagination nav span[aria-current] span {
        background: #7f0010 !important;
        border-color: #7f0010 !important;
        color: #ffffff !important;
    }
    .audit-pagination nav svg {
        width: 16px !important;
        height: 16px !important;
        max-width: 16px !important;
        max-height: 16px !important;
    }
    .audit-pagination .pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    .audit-pagination .pagination li {
        display: inline-flex;
    }
    .audit-pagination .pagination a,
    .audit-pagination .pagination span {
        min-width: 38px;
        height: 38px;
        padding: 0 14px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #334155;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 900;
        text-decoration: none;
        line-height: 1;
        transition: transform .18s ease, background .18s ease, color .18s ease, border-color .18s ease, box-shadow .18s ease;
    }
    .audit-pagination .pagination a:hover {
        transform: translateY(-1px);
        background: #fff7ed;
        border-color: #f8cfd4;
        color: #70131B;
        box-shadow: 0 10px 20px rgba(112, 19, 27, .10);
    }
    .audit-pagination .pagination .active span {
        background: #7f0010;
        border-color: #7f0010;
        color: #ffffff;
    }
    .audit-pagination .pagination .disabled span {
        background: #f8fafc;
        color: #94a3b8;
        cursor: not-allowed;
    }
    .audit-pagination .pagination svg {
        width: 14px;
        height: 14px;
    }

    html[data-theme="dark"] .audit-card {
        background: linear-gradient(180deg, rgba(17,24,39,0.98), rgba(15,23,42,0.96));
        border-color: rgba(148,163,184,0.18);
        box-shadow: 0 24px 54px rgba(0,0,0,0.26);
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .audit-card::before {
        background: linear-gradient(180deg, #facc15, #8B0000 70%, #5e0000);
    }

    html[data-theme="dark"] .audit-title,
    html[data-theme="dark"] .audit-list-title,
    html[data-theme="dark"] .audit-collapse-summary,
    html[data-theme="dark"] .audit-stat-value,
    html[data-theme="dark"] .audit-mini-label,
    html[data-theme="dark"] .audit-table td {
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .audit-subtitle,
    html[data-theme="dark"] .audit-head-note,
    html[data-theme="dark"] .audit-filter-label,
    html[data-theme="dark"] .audit-collapse-summary-sub,
    html[data-theme="dark"] .audit-mono,
    html[data-theme="dark"] .audit-muted,
    html[data-theme="dark"] .audit-empty {
        color: #cbd5e1 !important;
    }

    html[data-theme="dark"] .audit-chip,
    html[data-theme="dark"] .audit-stat,
    html[data-theme="dark"] .audit-mini-item,
    html[data-theme="dark"] .audit-input,
    html[data-theme="dark"] .audit-select,
    html[data-theme="dark"] .audit-btn-light {
        background: rgba(17,24,39,0.92);
        border-color: rgba(148,163,184,0.2);
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .audit-input::placeholder {
        color: #cbd5e1;
        opacity: 0.78;
    }

    html[data-theme="dark"] .premium-select-button,
    html[data-theme="dark"] .premium-select-menu {
        background: rgba(17,24,39,0.96);
        border-color: rgba(148,163,184,0.24);
        color: #f8fafc;
    }

    html[data-theme="dark"] .premium-select-option {
        background: rgba(15,23,42,0.92);
        border-color: rgba(148,163,184,0.2);
        color: #f8fafc;
    }

    html[data-theme="dark"] .premium-select-option:hover,
    html[data-theme="dark"] .premium-select-option.is-selected {
        background: #8B0000;
        color: #ffd700;
        border-color: #ffd700;
    }

    html[data-theme="dark"] .audit-mini-count {
        color: #fde68a !important;
    }

    html[data-theme="dark"] .audit-table tbody tr {
        background: linear-gradient(180deg, rgba(17,24,39,0.98), rgba(15,23,42,0.96));
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.18);
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .audit-table thead th {
        color: #94a3b8;
    }

    html[data-theme="dark"] .audit-table td,
    html[data-theme="dark"] .audit-table td:first-child,
    html[data-theme="dark"] .audit-table td:last-child {
        border-color: rgba(148,163,184,0.14);
    }

    html[data-theme="dark"] .audit-table td *:not(.audit-role-badge):not(.audit-event-badge):not(.audit-status-badge):not(.audit-muted):not(.audit-mono) {
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .audit-kicker {
        background: rgba(250, 204, 21, 0.12);
        border-color: rgba(250, 204, 21, 0.22);
        color: #fde68a !important;
    }

    html[data-theme="dark"] .audit-btn-light:hover,
    html[data-theme="dark"] .audit-btn-primary:hover {
        color: #111827;
    }

    /* Professional Audit Trail console layout. */
    .audit-wrap {
        gap: 16px;
    }

    .audit-card,
    .audit-overview,
    .audit-filter-panel {
        background: #ffffff;
        border: 1px solid #e8edf3;
        border-radius: 10px;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.045);
    }

    .audit-card::before,
    .audit-stat::after {
        display: none;
    }

    .audit-overview {
        overflow: hidden;
        border-radius: 16px;
    }

    .audit-overview .audit-head {
        min-height: 90px;
        align-items: center;
        flex-wrap: nowrap;
        padding: 18px 20px;
        border-bottom: 1px solid #edf1f5;
    }

    .audit-head-main {
        gap: 5px;
        }

    .audit-title {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .audit-hero-icon {
        display: inline-grid;
        width: 54px;
        height: 54px;
        place-items: center;
        flex: 0 0 auto;
        border-radius: 14px;
        background: #fff0f2;
        color: #8f1222;
    }

    .audit-hero-icon svg {
        width: 28px;
        height: 28px;
    }

    .audit-kicker {
        padding: 0;
        border: 0;
        border-radius: 0;
        background: transparent;
        font-size: 10px;
    }

    .audit-overview .audit-kicker {
        color: #8B0000;
    }

    .audit-kicker span {
        width: 6px;
        height: 6px;
        box-shadow: none;
    }

    .audit-subtitle {
        margin-left: 68px;
        line-height: 1.5;
    }

    .audit-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 10px;
        border-radius: 6px;
        background: #ffffff;
        font-size: 11px;
    }

    .audit-chip svg {
        width: 14px;
        height: 14px;
        color: #8B0000;
    }

    .audit-overview .audit-chip {
        color: #334155;
    }

    .audit-head-aside {
        display: grid;
        grid-template-columns: 42px auto;
        align-items: center;
        gap: 10px;
        min-width: 180px;
    }

    .audit-head-sync-icon {
        display: grid;
        width: 42px;
        height: 42px;
        place-items: center;
        border-radius: 12px;
        background: #fff1f2;
        color: #b91c1c;
    }

    .audit-head-sync-icon svg {
        width: 20px;
        height: 20px;
    }

    .audit-head-sync-label,
    .audit-head-sync-value,
    .audit-head-sync-note {
        display: block;
    }

    .audit-head-sync-label {
        color: #64748b;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .audit-head-sync-value {
        margin-top: 2px;
        color: #0f172a;
        font-size: 13px;
        font-weight: 900;
        line-height: 1.25;
    }

    .audit-head-sync-note {
        margin-top: 2px;
        color: #737887;
        font-size: 11px;
        font-weight: 700;
    }

    .audit-stats {
        gap: 12px;
    }

    .audit-overview .audit-stats {
        padding: 14px 20px 18px;
    }

    .audit-stat {
        display: grid;
        grid-template-columns: 36px minmax(0, 1fr);
        align-items: center;
        gap: 10px;
        min-height: 78px;
        padding: 14px;
        border-radius: 9px;
        background: #ffffff;
        box-shadow: 0 7px 18px rgba(15, 23, 42, 0.035);
    }

    .audit-stat-label {
        margin: 0 0 5px;
    }

    .audit-stat-icon {
        display: inline-flex;
        width: 36px;
        height: 36px;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }

    .audit-stat-icon svg {
        width: 19px;
        height: 19px;
    }

    .audit-stat-icon--events { background: #eff6ff; color: #2563eb; }
    .audit-stat-icon--actors { background: #ecfdf5; color: #16a34a; }
    .audit-stat-icon--failed { background: #fff7ed; color: #ea580c; }
    .audit-stat-icon--emergency { background: #fff1f2; color: #e11d48; }
    .audit-stat-icon--total { background: #f5f3ff; color: #7c3aed; }

    .audit-filter-panel {
        margin: 8px 0;
        padding: 16px;
    }

    .audit-primary-filters {
        display: grid;
        grid-template-columns: minmax(250px, 1.7fr) repeat(2, minmax(135px, .8fr)) auto;
        align-items: end;
        gap: 14px;
    }

    .audit-filter-group {
        gap: 5px;
    }

    .audit-filter-group--search {
        min-width: 0;
    }

    .audit-search-control,
    .audit-date-control {
        position: relative;
        display: block;
    }

    .audit-search-control svg,
    .audit-date-control svg {
        position: absolute;
        z-index: 1;
        top: 50%;
        left: 11px;
        width: 15px;
        height: 15px;
        color: #8B0000;
        pointer-events: none;
        transform: translateY(-50%);
    }

    .audit-search-control .audit-input,
    .audit-date-control .audit-input {
        padding-left: 34px;
    }

    .audit-input,
    .audit-select {
        min-height: 42px;
        border-radius: 7px;
        box-shadow: none;
    }

    .premium-select-button {
        min-height: 42px;
        border-radius: 7px;
        box-shadow: none;
        font-weight: 700;
    }

    .audit-primary-filters .premium-select-button {
        font-size: 12px;
    }

    .premium-select-menu {
        border-radius: 8px;
    }

    .premium-select-option {
        border-radius: 6px;
        font-weight: 700;
    }

    .audit-more-filters {
        display: inline-flex;
        min-height: 42px;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 0 14px;
        border: 1px solid #d7dee8;
        border-radius: 7px;
        background: #ffffff;
        color: #1e293b;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
        cursor: pointer;
        transition: background .18s ease, border-color .18s ease, color .18s ease, box-shadow .18s ease;
    }

    .audit-more-filters svg {
        width: 15px;
        height: 15px;
        color: #8B0000;
    }

    .audit-more-filters:hover,
    .audit-more-filters[aria-expanded="true"] {
        border-color: #8B0000;
        background: #fff7f7;
        color: #8B0000;
        box-shadow: 0 5px 14px rgba(139, 0, 0, 0.08);
    }

    .audit-more-modal {
        position: fixed;
        z-index: 10050;
        inset: 0;
        display: grid;
        place-items: center;
        padding: 20px;
    }

    .audit-more-modal[hidden] {
        display: none;
    }

    .audit-more-modal-backdrop {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        border: 0;
        background: rgba(15, 23, 42, .48);
        cursor: default;
    }

    .audit-more-modal__dialog {
        position: relative;
        width: min(100%, 700px);
        overflow: hidden;
        border: 1px solid #eadde0;
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 24px 60px rgba(15, 23, 42, .26);
    }

    .audit-more-modal__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 15px 18px;
        border-bottom: 1px solid #edf1f5;
    }

    .audit-more-modal__head-copy {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .audit-more-modal__icon {
        display: grid;
        width: 38px;
        height: 38px;
        place-items: center;
        border-radius: 10px;
        background: #fff0f2;
        color: #8f1222;
    }

    .audit-more-modal__icon svg {
        width: 19px;
        height: 19px;
    }

    .audit-more-modal__title {
        margin: 0;
        color: #111827;
        font-size: 16px;
        font-weight: 900;
    }

    .audit-more-modal__copy {
        margin: 3px 0 0;
        color: #64748b;
        font-size: 12px;
    }

    .audit-more-modal__close {
        display: inline-grid;
        width: 34px;
        height: 34px;
        place-items: center;
        border: 1px solid #e5d6da;
        border-radius: 50%;
        background: #ffffff;
        color: #8B0000;
        cursor: pointer;
        transition: background .18s ease, color .18s ease, border-color .18s ease, box-shadow .18s ease;
    }

    .audit-more-modal__close svg {
        width: 18px;
        height: 18px;
    }

    .audit-more-modal__close:hover {
        border-color: #facc15;
        background: #facc15;
        color: #8B0000;
        box-shadow: 0 6px 14px rgba(139, 0, 0, .12);
    }

    .audit-more-modal__body {
        position: relative;
        z-index: 1;
        padding: 18px;
    }

    .audit-more-modal .audit-advanced-filters {
        margin: 0;
        padding: 0;
        border: 0;
    }

    .audit-more-modal .audit-advanced-filters .audit-filters {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .audit-more-modal .premium-select-shell.is-open {
        z-index: 70;
    }

    .audit-more-modal__actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        padding: 14px 18px;
        border-top: 1px solid #edf1f5;
        background: #fbfcfe;
    }

    body.audit-more-modal-open {
        overflow: hidden;
    }

    .audit-breakdowns {
        display: none;
    }

    .audit-feed-card {
        padding: 0;
        overflow: hidden;
    }

    .audit-feed-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 16px;
        border-bottom: 1px solid #edf1f5;
    }

    .audit-feed-head > div {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .audit-live-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 7px;
        border-radius: 5px;
        background: #ecfdf3;
        color: #15803d;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .audit-live-status i {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .audit-refresh {
        display: inline-flex;
        width: 32px;
        height: 32px;
        align-items: center;
        justify-content: center;
        border: 1px solid #dfe6ee;
        border-radius: 7px;
        color: #64748b;
        text-decoration: none;
    }

    .audit-refresh:hover {
        border-color: #8B0000;
        color: #8B0000;
        background: #fff7f7;
    }

    .audit-refresh svg {
        width: 15px;
        height: 15px;
    }

    .audit-table-wrap {
        border-radius: 0;
    }

    .audit-table {
        min-width: 1450px;
        border-collapse: collapse;
        border-spacing: 0;
    }

    .audit-table thead th {
        padding: 10px 12px;
        border-bottom: 1px solid #e8edf3;
        background: #f8fafc;
    }

    .audit-table tbody tr {
        background: #ffffff;
        box-shadow: none;
    }

    .audit-table tbody tr:hover {
        background: #fffafa;
        box-shadow: none;
        transform: none;
    }

    .audit-table td {
        padding: 11px 12px;
        border: 0;
        border-bottom: 1px solid #edf1f5;
    }

    .audit-table td:first-child,
    .audit-table td:last-child {
        border-right: 0;
        border-left: 0;
        border-radius: 0;
    }

    .audit-role-badge,
    .audit-event-badge,
    .audit-status-badge {
        border-radius: 5px;
        padding: 3px 8px;
    }

    .audit-pagination {
        margin: 0;
        border: 0;
        border-radius: 0;
        box-shadow: none;
    }

    html[data-theme="dark"] .audit-card,
    html[data-theme="dark"] .audit-overview,
    html[data-theme="dark"] .audit-filter-panel,
    html[data-theme="dark"] .audit-stat,
    html[data-theme="dark"] .audit-table tbody tr {
        background: #111827;
        border-color: rgba(148, 163, 184, 0.2);
    }

    html[data-theme="dark"] .audit-filter-panel,
    html[data-theme="dark"] .audit-feed-head,
    html[data-theme="dark"] .audit-table thead th,
    html[data-theme="dark"] .audit-table td {
        border-color: rgba(148, 163, 184, 0.16);
    }

    html[data-theme="dark"] .audit-table thead th {
        background: #172033;
    }

    html[data-theme="dark"] .audit-table tbody tr:hover {
        background: #1b2435;
    }

    html[data-theme="dark"] .audit-more-filters,
    html[data-theme="dark"] .audit-refresh {
        border-color: rgba(148, 163, 184, 0.25);
        background: #172033;
        color: #e2e8f0;
    }

    html[data-theme="dark"] .audit-more-filters:hover,
    html[data-theme="dark"] .audit-more-filters[aria-expanded="true"],
    html[data-theme="dark"] .audit-refresh:hover {
        border-color: #facc15;
        background: #331315;
        color: #facc15;
    }

    html[data-theme="dark"] .audit-hero-icon {
        background: rgba(159, 31, 48, .3);
        color: #fecdd3;
    }

    html[data-theme="dark"] .audit-head-sync-icon {
        background: rgba(180, 83, 35, .24);
        color: #fbbf24;
    }

    html[data-theme="dark"] .audit-head-sync-label,
    html[data-theme="dark"] .audit-head-sync-note {
        color: #aebacd;
    }

    html[data-theme="dark"] .audit-head-sync-value {
        color: #f4f7fb;
    }

    html[data-theme="dark"] .audit-more-modal__dialog {
        border-color: #324055;
        background: #121a26;
    }

    html[data-theme="dark"] .audit-more-modal__head,
    html[data-theme="dark"] .audit-more-modal__actions {
        border-color: #2f3c4e;
        background: #172130;
    }

    html[data-theme="dark"] .audit-more-modal__title {
        color: #f4f7fb;
    }

    html[data-theme="dark"] .audit-more-modal__copy {
        color: #aebacd;
    }

    html[data-theme="dark"] .audit-more-modal__icon {
        background: rgba(159, 31, 48, .3);
        color: #fecdd3;
    }

    html[data-theme="dark"] .audit-more-modal__close {
        border-color: #42516a;
        background: #172130;
        color: #fecdd3;
    }

    html[data-theme="dark"] .audit-overview .audit-head {
        border-color: rgba(148, 163, 184, 0.16);
    }

    @media (max-width: 1200px) {
        .audit-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .audit-primary-filters {
            grid-template-columns: minmax(220px, 1.35fr) repeat(2, minmax(140px, .8fr));
        }

        .audit-more-filters {
            justify-self: start;
        }

    }

    @media (max-width: 760px) {
        .audit-stats,
        .audit-primary-filters,
        .audit-more-modal .audit-advanced-filters .audit-filters {
            grid-template-columns: 1fr;
        }

        .audit-overview .audit-head {
            align-items: flex-start;
            flex-wrap: wrap;
            padding: 16px;
        }

        .audit-subtitle {
            margin-left: 0;
        }

        .audit-overview .audit-stats {
            padding: 12px 16px 16px;
        }

        .audit-head-aside {
            justify-items: initial;
        }

        .audit-chip-row {
            justify-content: flex-start;
        }
    }
</style>
@endpush

@section('content')
@php
    $roleLabelMap = [
        'admin' => 'Admin',
        'superadmin' => 'Super Admin',
        'super_admin' => 'Super Admin',
        'student_assistant' => 'Admin',
        'student' => 'Student',
        'applicant' => 'Applicant',
        'faculty' => 'Faculty',
        'guest' => 'Guest',
        'unknown' => 'Unknown',
    ];
    $showAdvancedFilters = request()->filled('event_type')
        || request()->filled('http_method')
        || request()->filled('status_class')
        || request()->filled('date_from')
        || request()->filled('date_to')
        || request('per_page', '25') !== '25';
    $latestAuditLogAt = optional($logs->first())->created_at;
@endphp

<div class="audit-wrap">
    <section class="audit-overview">
        <div class="audit-head">
            <div class="audit-head-main">
                <h1 class="audit-title">
                    <span class="audit-hero-icon"><x-outline-icon name="clipboard-document-list" /></span>
                    Professional Audit Trail
                </h1>
                <p class="audit-subtitle">Monitor all user activities across students, student assistants, and clinic administrators with a cleaner, high-signal view.</p>
            </div>
            <div class="audit-head-aside">
                <span class="audit-head-sync-icon"><x-outline-icon name="clock" /></span>
                <div>
                    <span class="audit-head-sync-label">Last Updated</span>
                    <strong class="audit-head-sync-value">{{ $latestAuditLogAt ? $latestAuditLogAt->format('M j, Y') : 'No activity yet' }}</strong>
                    <small class="audit-head-sync-note">{{ $latestAuditLogAt ? $latestAuditLogAt->format('g:i A') : 'Records shown: ' . $logs->count() }}</small>
                </div>
            </div>
        </div>
        <section class="audit-stats">
            <article class="audit-stat">
                <span class="audit-stat-icon audit-stat-icon--events"><x-outline-icon name="clipboard-document-list" /></span>
                <div><div class="audit-stat-label">Events (24H)</div><div class="audit-stat-value">{{ number_format($todayEvents) }}</div></div>
            </article>
            <article class="audit-stat">
                <span class="audit-stat-icon audit-stat-icon--actors"><x-outline-icon name="users" /></span>
                <div><div class="audit-stat-label">Unique Actors</div><div class="audit-stat-value">{{ number_format($uniqueActors) }}</div></div>
            </article>
            <article class="audit-stat">
                <span class="audit-stat-icon audit-stat-icon--failed"><x-outline-icon name="shield-check" /></span>
                <div><div class="audit-stat-label">Failed Events</div><div class="audit-stat-value">{{ number_format($failedEvents) }}</div></div>
            </article>
            <article class="audit-stat">
                <span class="audit-stat-icon audit-stat-icon--emergency"><x-outline-icon name="exclamation-triangle" /></span>
                <div><div class="audit-stat-label">Emergency Events</div><div class="audit-stat-value">{{ number_format($emergencyEvents) }}</div></div>
            </article>
            <article class="audit-stat">
                <span class="audit-stat-icon audit-stat-icon--total"><x-outline-icon name="chart-bar" /></span>
                <div><div class="audit-stat-label">Total Events</div><div class="audit-stat-value">{{ number_format($totalEvents) }}</div></div>
            </article>
        </section>
    </section>

    <section class="audit-filter-panel">
        <form method="GET" action="{{ route('admin.logs') }}">
            <div class="audit-primary-filters">
                <div class="audit-filter-group audit-filter-group--search">
                    <label class="audit-filter-label" for="audit_q">Search</label>
                    <span class="audit-search-control"><x-outline-icon name="magnifying-glass" /><input id="audit_q" name="q" type="text" class="audit-input" value="{{ request('q') }}" placeholder="Search events, actors, modules, or endpoints..."></span>
                </div>
                <div class="audit-filter-group">
                    <label class="audit-filter-label" for="audit_module">Module</label>
                    <select id="audit_module" name="module" class="audit-select">
                        <option value="">All Modules</option>
                        @foreach($moduleOptions as $moduleOption)
                            <option value="{{ $moduleOption }}" @selected(request('module') === $moduleOption)>{{ $moduleOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="audit-filter-group">
                    <label class="audit-filter-label" for="audit_actor_role">Role</label>
                    <select id="audit_actor_role" name="actor_role" class="audit-select">
                        <option value="">All Roles</option>
                        @foreach($roleOptions as $roleOption)
                            <option value="{{ $roleOption }}" @selected(request('actor_role') === $roleOption)>{{ $roleLabelMap[$roleOption] ?? ucwords(str_replace('_', ' ', $roleOption)) }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="button" class="audit-more-filters" data-audit-more-filters aria-controls="auditMoreFiltersModal" aria-expanded="{{ $showAdvancedFilters ? 'true' : 'false' }}" aria-label="More filters" title="More filters">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" focusable="false">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                    </svg>
                    <span>More Filters</span>
                </button>
            </div>

            <div class="audit-more-modal" id="auditMoreFiltersModal" role="dialog" aria-modal="true" aria-labelledby="auditMoreFiltersTitle" aria-hidden="{{ $showAdvancedFilters ? 'false' : 'true' }}" @unless($showAdvancedFilters) hidden @endunless>
                <button type="button" class="audit-more-modal-backdrop" data-audit-close-more-filters aria-label="Close more filters"></button>
                <section class="audit-more-modal__dialog">
                    <header class="audit-more-modal__head">
                        <div class="audit-more-modal__head-copy">
                            <span class="audit-more-modal__icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" focusable="false">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                                </svg>
                            </span>
                            <div>
                                <h2 class="audit-more-modal__title" id="auditMoreFiltersTitle">More Filters</h2>
                                <p class="audit-more-modal__copy">Refine the audit events shown in the activity feed.</p>
                            </div>
                        </div>
                        <button type="button" class="audit-more-modal__close" data-audit-close-more-filters aria-label="Close more filters"><x-outline-icon name="x-mark" /></button>
                    </header>
                    <div class="audit-more-modal__body">
                        <div class="audit-advanced-filters">
                            <div class="audit-filters">
                                <div class="audit-filter-group">
                                    <label class="audit-filter-label" for="audit_date_from">Date From</label>
                                    <span class="audit-date-control"><x-outline-icon name="calendar-days" /><input id="audit_date_from" name="date_from" type="date" class="audit-input" value="{{ request('date_from') }}"></span>
                                </div>
                                <div class="audit-filter-group">
                                    <label class="audit-filter-label" for="audit_date_to">Date To</label>
                                    <span class="audit-date-control"><x-outline-icon name="calendar-days" /><input id="audit_date_to" name="date_to" type="date" class="audit-input" value="{{ request('date_to') }}"></span>
                                </div>
                                <div class="audit-filter-group">
                                    <label class="audit-filter-label" for="audit_event_type">Event Type</label>
                                    <select id="audit_event_type" name="event_type" class="audit-select">
                                        <option value="">All Types</option>
                                        @foreach($eventTypeOptions as $eventTypeOption)
                                            <option value="{{ $eventTypeOption }}" @selected(request('event_type') === $eventTypeOption)>{{ strtoupper($eventTypeOption) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="audit-filter-group">
                                    <label class="audit-filter-label" for="audit_http_method">HTTP Method</label>
                                    <select id="audit_http_method" name="http_method" class="audit-select">
                                        <option value="">All Methods</option>
                                        @foreach(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as $methodOption)
                                            <option value="{{ $methodOption }}" @selected(request('http_method') === $methodOption)>{{ $methodOption }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="audit-filter-group">
                                    <label class="audit-filter-label" for="audit_status_class">Status Class</label>
                                    <select id="audit_status_class" name="status_class" class="audit-select">
                                        <option value="">All Statuses</option>
                                        <option value="success" @selected(request('status_class') === 'success')>Success (&lt; 400)</option>
                                        <option value="error" @selected(request('status_class') === 'error')>Error (400+)</option>
                                    </select>
                                </div>
                                <div class="audit-filter-group">
                                    <label class="audit-filter-label" for="audit_per_page">Rows</label>
                                    <select id="audit_per_page" name="per_page" class="audit-select">
                                        @foreach(['25' => '25 per page', '50' => '50 per page', '100' => '100 per page', 'all' => 'Show all'] as $rowsOption => $rowsLabel)
                                            <option value="{{ $rowsOption }}" @selected(($perPageInput ?? '25') === $rowsOption)>{{ $rowsLabel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <footer class="audit-more-modal__actions">
                        <a href="{{ route('admin.logs') }}" class="audit-btn audit-btn-light">Reset</a>
                        <button type="submit" class="audit-btn audit-btn-primary">Apply Filters</button>
                    </footer>
                </section>
            </div>
        </form>
    </section>

    <section class="audit-breakdowns">
        <article class="audit-card audit-collapsible">
            <details open>
                <summary class="audit-collapse-summary">
                    <span class="audit-list-title">Activity by Role</span>
                    <span class="audit-collapse-summary-sub">Actor totals</span>
                </summary>
                <div class="audit-collapse-body">
                    <ul class="audit-mini-list">
                        @forelse($roleBreakdown as $row)
                            <li class="audit-mini-item">
                                <span class="audit-mini-label">{{ $roleLabelMap[$row->role] ?? ucwords(str_replace('_', ' ', $row->role)) }}</span>
                                <span class="audit-mini-count">{{ number_format($row->total) }}</span>
                            </li>
                        @empty
                            <li class="audit-empty">No role activity yet.</li>
                        @endforelse
                    </ul>
                </div>
            </details>
        </article>
        <article class="audit-card audit-collapsible">
            <details open>
                <summary class="audit-collapse-summary">
                    <span class="audit-list-title">Top Modules</span>
                    <span class="audit-collapse-summary-sub">Most active areas</span>
                </summary>
                <div class="audit-collapse-body">
                    <ul class="audit-mini-list">
                        @forelse($moduleBreakdown as $row)
                            <li class="audit-mini-item">
                                <span class="audit-mini-label">{{ $row->module_name }}</span>
                                <span class="audit-mini-count">{{ number_format($row->total) }}</span>
                            </li>
                        @empty
                            <li class="audit-empty">No module activity yet.</li>
                        @endforelse
                    </ul>
                </div>
            </details>
        </article>
    </section>

    <section class="audit-card audit-feed-card">
        <div class="audit-feed-head">
            <div><h2 class="audit-list-title">Activity Feed</h2><span class="audit-live-status"><i></i>Live</span></div>
            <a href="{{ request()->fullUrl() }}" class="audit-refresh" title="Refresh audit trail" aria-label="Refresh audit trail">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" focusable="false">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
            </a>
        </div>
        <div class="audit-table-wrap">
            <table class="audit-table">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Actor</th>
                        <th>Role</th>
                        <th>Module</th>
                        <th>Event</th>
                        <th>Action</th>
                        <th>Subject</th>
                        <th>HTTP</th>
                        <th>Status</th>
                        <th>IP</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        @php
                            $roleKey = strtolower((string) ($log->user_role ?? ''));
                            $isEmergencyLog = ($log->action ?? '') === 'Emergency Login'
                                && (bool) data_get($log->metadata, 'emergency_login');
                            $emergencyEmail = strtolower(trim((string) config('services.emergency.email', '')));
                            $loggedEmail = strtolower(trim((string) data_get($log->metadata, 'email', '')));
                            if ($roleKey === '' && $isEmergencyLog && $emergencyEmail !== '' && $loggedEmail === $emergencyEmail) {
                                $roleKey = \App\Models\User::normalizeRole((string) config('services.emergency.role', ''));
                            }
                            $roleKey = $roleKey !== '' ? $roleKey : 'unknown';
                            $eventKey = strtolower((string) ($log->event_type ?? 'action'));
                            $statusCode = $log->status_code;
                            $statusClass = is_null($statusCode) ? 'unknown' : ((int) $statusCode >= 400 ? 'failed' : 'ok');
                            $subjectText = trim((string) (($log->subject_type ?? '-') . ((string) ($log->subject_id ?? '') !== '' ? ' #' . $log->subject_id : '')));
                        @endphp
                        <tr>
                            <td>
                                <div>{{ optional($log->created_at)->format('M d, Y') }}</div>
                                <div class="audit-muted" style="font-size:12px;">{{ optional($log->created_at)->format('g:i:s A') }}</div>
                            </td>
                            <td>
                                <div class="audit-user">
                                    <span class="audit-avatar">{{ strtoupper(substr((string) ($log->user_name ?? 'U'), 0, 1)) }}</span>
                                    <div>
                                        <div style="font-weight:700;">{{ $log->user_name ?? 'Unknown User' }}</div>
                                        <div class="audit-muted" style="font-size:11px;">UID: {{ $log->user_id ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="audit-role-badge audit-role-{{ $roleKey }}">
                                    {{ $roleLabelMap[$roleKey] ?? ucwords(str_replace('_', ' ', $roleKey)) }}
                                </span>
                            </td>
                            <td>{{ $log->module ?? 'Uncategorized' }}</td>
                            <td>
                                <span class="audit-event-badge audit-event-{{ $eventKey }}">
                                    {{ strtoupper($eventKey) }}
                                </span>
                            </td>
                            <td>{{ $log->action }}</td>
                            <td>{{ $subjectText !== '' ? $subjectText : '-' }}</td>
                            <td>
                                <div class="audit-mono">{{ $log->http_method ?? '-' }}</div>
                                <div class="audit-mono audit-muted">{{ $log->request_path ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="audit-status-badge audit-status-{{ $statusClass }}">
                                    {{ is_null($statusCode) ? 'N/A' : $statusCode }}
                                </span>
                            </td>
                            <td class="audit-mono">{{ $log->ip_address ?? '-' }}</td>
                            <td style="min-width:250px;">
                                <div>{{ $log->description }}</div>
                                @if($log->route_name)
                                    <div class="audit-muted" style="margin-top:4px; font-size:11px;">Route: {{ $log->route_name }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="audit-empty">No audit records found for the selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="audit-pagination">
                {{ $logs->links() }}
            </div>
        @endif
    </section>
</div>
<script>
(function initAuditPremiumSelects() {
    const shells = [];

    function enhance(select) {
        if (!select || select.dataset.premiumSelectReady === '1') return;

        select.dataset.premiumSelectReady = '1';
        select.classList.add('premium-select-native');

        const shell = document.createElement('div');
        shell.className = 'premium-select-shell';

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'premium-select-button';
        button.textContent = select.options[select.selectedIndex]?.textContent?.trim() || 'Select option';

        const menu = document.createElement('div');
        menu.className = 'premium-select-menu';

        Array.from(select.options).forEach((option) => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'premium-select-option';
            item.textContent = option.textContent.trim();
            item.dataset.value = option.value;
            item.classList.toggle('is-selected', option.selected);

            item.addEventListener('click', () => {
                select.value = option.value;
                button.textContent = option.textContent.trim();
                menu.querySelectorAll('.premium-select-option').forEach((node) => {
                    node.classList.toggle('is-selected', node === item);
                });
                shell.classList.remove('is-open');
                select.dispatchEvent(new Event('change', { bubbles: true }));
            });

            menu.appendChild(item);
        });

        select.parentNode.insertBefore(shell, select);
        shell.appendChild(button);
        shell.appendChild(menu);
        shell.appendChild(select);
        shells.push(shell);

        button.addEventListener('click', (event) => {
            event.stopPropagation();
            shells.forEach((otherShell) => {
                if (otherShell !== shell) otherShell.classList.remove('is-open');
            });
            shell.classList.toggle('is-open');
        });
    }

    document.querySelectorAll('.audit-select').forEach(enhance);
    document.addEventListener('click', () => shells.forEach((shell) => shell.classList.remove('is-open')));
})();

(function initAuditMoreFilters() {
    const trigger = document.querySelector('[data-audit-more-filters]');
    const modal = document.getElementById('auditMoreFiltersModal');
    const closeButtons = document.querySelectorAll('[data-audit-close-more-filters]');

    if (!trigger || !modal) return;

    const setOpen = (open) => {
        modal.toggleAttribute('hidden', !open);
        modal.setAttribute('aria-hidden', String(!open));
        trigger.setAttribute('aria-expanded', String(open));
        document.body.classList.toggle('audit-more-modal-open', open);

        if (open) {
            modal.querySelector('.audit-more-modal__close')?.focus();
        } else {
            trigger.focus();
        }
    };

    trigger.addEventListener('click', () => setOpen(true));
    closeButtons.forEach((button) => button.addEventListener('click', () => setOpen(false)));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.hasAttribute('hidden')) {
            setOpen(false);
        }
    });

    if (!modal.hasAttribute('hidden')) {
        document.body.classList.add('audit-more-modal-open');
    }
})();
</script>
@endsection
