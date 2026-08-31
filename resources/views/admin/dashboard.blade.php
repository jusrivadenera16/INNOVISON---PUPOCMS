@extends('layouts.admin')

@section('title', 'Dashboard')

@push('styles')
<style>
    /* --- DASHBOARD CONTAINER --- */
    .dashboard-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    .dashboard-welcome {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin: 4px 0 18px;
        padding: 0 2px;
    }

    .dashboard-welcome-copy h1 {
        margin: 0;
        color: #111827;
        font-family: "Outfit", "Manrope", sans-serif;
        font-size: 22px;
        font-weight: 800;
        line-height: 1.2;
    }

    .dashboard-welcome-copy h1 .dashboard-welcome-name {
        color: #8b0000;
    }

    .dashboard-welcome-copy p {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 13px;
        font-weight: 600;
    }

    .dashboard-date-badge {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        flex: 0 0 auto;
        min-width: 158px;
        padding: 10px 12px;
        border: 1px solid rgba(128, 0, 0, 0.12);
        border-radius: 10px;
        background: #ffffff;
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.06);
    }

    .dashboard-date-badge svg {
        width: 20px;
        height: 20px;
        color: #8b0000;
        flex: 0 0 auto;
        stroke-width: 1.8;
    }

    .dashboard-date-badge strong,
    .dashboard-date-badge span {
        display: block;
    }

    .dashboard-date-badge strong {
        color: #111827;
        font-size: 12px;
        font-weight: 800;
        line-height: 1.2;
    }

    .dashboard-date-badge span {
        margin-top: 2px;
        color: #64748b;
        font-size: 10px;
        font-weight: 700;
    }

    /* --- 1. STATS ROW (The "MedTrackr" Style) --- */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: #70131B; /* Dark Navy/Slate background like reference */
        color: #fff;
        border-radius: 16px;
        padding: 24px 20px;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 140px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        z-index: 0;
    }

    .stat-card::before {
        content: "";
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 74px;
        background:
            url("data:image/svg+xml,%3Csvg viewBox='0 0 520 140' preserveAspectRatio='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 115 C70 20 130 92 190 58 C260 18 310 112 370 58 C430 6 466 76 520 48 L520 140 L0 140 Z' fill='VAR_FILL'/%3E%3Cpath d='M0 115 C70 20 130 92 190 58 C260 18 310 112 370 58 C430 6 466 76 520 48' fill='none' stroke='VAR_STROKE' stroke-width='7' stroke-linecap='round'/%3E%3C/svg%3E") center bottom / 100% 100% no-repeat;
        opacity: .92;
        transform: translateY(var(--wave-offset, 0px));
        transition: background .22s ease, opacity .22s ease, transform .22s ease;
        z-index: 0;
    }

    .stat-card:nth-child(1)::before {
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 520 140' preserveAspectRatio='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 115 C70 20 130 92 190 58 C260 18 310 112 370 58 C430 6 466 76 520 48 L520 140 L0 140 Z' fill='%23f9d4df' fill-opacity='.46'/%3E%3Cpath d='M0 115 C70 20 130 92 190 58 C260 18 310 112 370 58 C430 6 466 76 520 48' fill='none' stroke='%23f43f5e' stroke-width='7' stroke-linecap='round'/%3E%3C/svg%3E");
    }

    .stat-card:nth-child(2)::before {
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 520 140' preserveAspectRatio='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 115 C70 20 130 92 190 58 C260 18 310 112 370 58 C430 6 466 76 520 48 L520 140 L0 140 Z' fill='%23fde68a' fill-opacity='.42'/%3E%3Cpath d='M0 115 C70 20 130 92 190 58 C260 18 310 112 370 58 C430 6 466 76 520 48' fill='none' stroke='%23f59e0b' stroke-width='7' stroke-linecap='round'/%3E%3C/svg%3E");
    }

    .stat-card:nth-child(3)::before {
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 520 140' preserveAspectRatio='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 115 C70 20 130 92 190 58 C260 18 310 112 370 58 C430 6 466 76 520 48 L520 140 L0 140 Z' fill='%23bbf7d0' fill-opacity='.42'/%3E%3Cpath d='M0 115 C70 20 130 92 190 58 C260 18 310 112 370 58 C430 6 466 76 520 48' fill='none' stroke='%2322c55e' stroke-width='7' stroke-linecap='round'/%3E%3C/svg%3E");
    }

    .stat-card:nth-child(4)::before {
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 520 140' preserveAspectRatio='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 115 C70 20 130 92 190 58 C260 18 310 112 370 58 C430 6 466 76 520 48 L520 140 L0 140 Z' fill='%23bfdbfe' fill-opacity='.42'/%3E%3Cpath d='M0 115 C70 20 130 92 190 58 C260 18 310 112 370 58 C430 6 466 76 520 48' fill='none' stroke='%233b82f6' stroke-width='7' stroke-linecap='round'/%3E%3C/svg%3E");
    }

    .stat-card::after {
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
        z-index: 0;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
    }

    .stat-card:hover::before {
        opacity: 1;
        transform: translateY(calc(var(--wave-offset, 0px) - 3px));
    }

    .stat-card:hover::after {
        transform: translateX(135%);
    }

    .stat-card > * {
        position: relative;
        z-index: 1;
    }

    .stat-card-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
    }

    .stat-percent {
        flex: 0 0 auto;
        padding: 5px 9px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .14);
        border: 1px solid rgba(255, 255, 255, .26);
        color: #ffffff;
        font-size: 11px;
        font-weight: 900;
        line-height: 1;
        white-space: nowrap;
    }

    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }

    /* Distinct Colors for active states if needed, 
       but keeping them uniform looks more professional like the reference */
    
    .stat-label {
        position: relative;
        z-index: 1;
        font-size: 13px;
        font-weight: 500;
        color: #94a3b8; /* Muted gray text */
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 10px;
    }

    .stat-value {
        position: relative;
        z-index: 1;
        font-size: 38px;
        font-weight: 700;
        color: #fff;
        margin-bottom: 12px;
        line-height: 1;
    }

    /* The "Pill" at the bottom (e.g. "Last 7 days") */
    .stat-badge {
        position: relative;
        z-index: 1;
        display: inline-block;
        font-size: 11px;
        font-weight: 900;
        padding: 5px 10px;
        border-radius: 999px;
        width: fit-content;
        color: #70131B !important;
        background: rgba(255, 255, 255, .92) !important;
        border: 1px solid rgba(255, 255, 255, .68);
        box-shadow: 0 8px 16px rgba(15, 23, 42, .10);
    }

    /* Badge Colors */
    .badge-neutral,
    .badge-warning,
    .badge-success,
    .badge-info,
    .badge-danger {
        color: #70131B !important;
    }


    /* --- 2. RECENT ACTIVITY PANEL --- */
    .panel {
        background: #fff; /* Keep white for readability of table */
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0;
    }

    .panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .panel-title {
        font-size: 18px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }

    /* Table Styles */
    table { width: 100%; border-collapse: separate; border-spacing: 0; }
    th { 
        text-align: left; 
        padding: 12px 16px; 
        color: #64748b; 
        font-size: 12px; 
        font-weight: 600; 
        text-transform: uppercase; 
        border-bottom: 1px solid #f1f5f9;
    }
    td { 
        padding: 16px; 
        border-bottom: 1px solid #f8fafc; 
        font-size: 14px; 
        color: #334155; 
    }
    tr:last-child td { border-bottom: none; }

    /* Status Pills in Table */
    .status-pill { padding: 4px 10px; border-radius: 99px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
    .st-approved { background: #dcfce7; color: #15803d; }
    .st-pending { background: #fffbeb; color: #b45309; }
    .st-completed { background: #dbeafe; color: #1e40af; }
    .st-cancelled { background: #fee2e2; color: #b91c1c; }
    .st-missed,
    .st-expired,
    .st-rejected { background: #fee2e2; color: #b91c1c; }
    .st-health { background: #fef3c7; color: #8B0000; }

    .panel-header {
        align-items: center;
        gap: 12px;
    }

    .btn-view-all {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 38px;
        padding: 0 16px;
        border: 1px solid #8B0000;
        border-radius: 999px;
        background: #8B0000;
        color: #ffffff;
        font-family: inherit;
        font-size: 12px;
        font-weight: 900;
        line-height: 1;
        text-decoration: none;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        box-shadow: 0 12px 24px rgba(139, 0, 0, .16);
        transition: transform .18s ease, border-color .18s ease, background .18s ease, color .18s ease, box-shadow .18s ease;
    }

    .btn-view-all::after {
        content: "";
        position: absolute;
        top: -40%;
        left: -130%;
        width: 120%;
        height: 180%;
        background: linear-gradient(115deg, rgba(255, 247, 181, 0) 0%, rgba(255, 247, 181, .58) 45%, rgba(255, 247, 181, 0) 100%);
        transform: skewX(-20deg);
        transition: left 1.35s ease;
        pointer-events: none;
    }

    .btn-view-all:hover,
    .btn-view-all:focus-visible {
        background: #facc15;
        border-color: #facc15;
        color: #70131B;
        transform: translateY(-1px);
        box-shadow: 0 14px 28px rgba(112, 19, 27, .18);
        outline: none;
    }

    .btn-view-all:hover::after,
    .btn-view-all:focus-visible::after {
        left: 125%;
    }

    .btn-view-all > * {
        position: relative;
        z-index: 1;
    }

    .btn-view-all svg {
        width: 16px;
        height: 16px;
        flex: 0 0 auto;
        transition: transform .18s ease;
    }

    .recent-activity-panel.is-expanded .btn-view-all svg {
        transform: rotate(180deg);
    }

    .recent-activity-table-wrap {
        max-height: 420px;
        overflow: auto;
        border-radius: 12px;
        scrollbar-width: thin;
        scrollbar-color: rgba(139, 0, 0, .36) transparent;
    }

    .recent-activity-table-wrap::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .recent-activity-table-wrap::-webkit-scrollbar-thumb {
        border-radius: 999px;
        background: rgba(139, 0, 0, .36);
    }

    .recent-activity-extra {
        display: none;
    }

    .recent-activity-panel.is-expanded .recent-activity-extra {
        display: table-row;
    }

    .recent-activity-kind {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 24px;
        padding: 0 9px;
        border-radius: 999px;
        border: 1px solid rgba(139, 0, 0, .12);
        background: #fff7ed;
        color: #8B0000;
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    html[data-theme="dark"] .btn-view-all {
        border-color: rgba(250, 204, 21, .28);
        background: #8B0000;
        color: #ffffff;
    }

    html[data-theme="dark"] .btn-view-all:hover,
    html[data-theme="dark"] .btn-view-all:focus-visible {
        background: #facc15;
        border-color: #facc15;
        color: #70131B;
    }

    html[data-theme="dark"] .recent-activity-kind {
        border-color: rgba(250, 204, 21, .22);
        background: rgba(250, 204, 21, .10);
        color: #facc15;
    }

    .dashboard-overview-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
        margin: -12px 0 22px;
    }

    .dashboard-overview-card {
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(112, 19, 27, 0.12);
        border-radius: 16px;
        padding: 22px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
    }

    .dashboard-overview-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 22px;
    }

    .dashboard-overview-title {
        margin: 0;
        color: #70131B;
        font-size: 15px;
        font-weight: 900;
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }

    .dashboard-overview-copy {
        margin: 3px 0 0;
        color: #64748b;
        font-size: 12px;
        line-height: 1.4;
    }

    .dashboard-period-btn {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        min-height: 34px;
        padding: 0 10px;
        border: 1px solid transparent;
        border-radius: 999px;
        background: transparent;
        color: #8B0000;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
        transition: background 0.18s ease, border-color 0.18s ease, color 0.18s ease;
    }

    .dashboard-period-btn svg {
        width: 14px;
        height: 14px;
        stroke-width: 2.2;
        transition: transform 0.18s ease;
    }

    .dashboard-period-control {
        position: relative;
        flex: 0 0 auto;
    }

    .dashboard-period-control.is-open .dashboard-period-btn {
        border-color: rgba(139, 0, 0, 0.16);
        background: #fff1f2;
        color: #70131B;
    }

    .dashboard-period-control.is-open .dashboard-period-btn svg {
        transform: rotate(180deg);
    }

    .dashboard-period-menu {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        z-index: 20;
        min-width: 166px;
        padding: 7px;
        border: 1px solid rgba(112, 19, 27, 0.12);
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.16);
        opacity: 0;
        visibility: hidden;
        transform: translateY(-6px);
        transition: opacity 0.18s ease, transform 0.18s ease, visibility 0.18s ease;
    }

    .dashboard-period-control.is-open .dashboard-period-menu {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .dashboard-period-link {
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        min-height: 34px;
        padding: 0 10px;
        border-radius: 8px;
        color: #334155;
        font-size: 12px;
        font-weight: 800;
        text-decoration: none;
        transition: background 0.18s ease, color 0.18s ease;
    }

    .dashboard-period-link::before {
        content: "";
        position: absolute;
        inset: -45% auto -45% -68%;
        width: 58%;
        background: linear-gradient(115deg, transparent 0%, rgba(255, 247, 194, 0.12) 35%, rgba(255, 247, 194, 0.78) 50%, rgba(255, 247, 194, 0.14) 65%, transparent 100%);
        transform: skewX(-18deg);
        transition: left 0.5s ease;
        pointer-events: none;
    }

    .dashboard-period-link span {
        position: relative;
        z-index: 1;
    }

    .dashboard-period-link:hover,
    .dashboard-period-link:focus-visible {
        background: #facc15;
        color: #8B0000;
        outline: none;
    }

    .dashboard-period-link:hover::before,
    .dashboard-period-link:focus-visible::before {
        left: 112%;
    }

    .dashboard-period-link.is-active {
        background: #8B0000;
        color: #facc15 !important;
        outline: none;
    }

    .dashboard-period-link.is-active span {
        color: #facc15 !important;
    }

    .clinic-activity-list,
    .needs-attention-list {
        display: grid;
        gap: 0;
    }

    .clinic-activity-row {
        display: grid;
        grid-template-columns: 44px minmax(0, 1fr) auto;
        align-items: center;
        gap: 14px;
        padding: 12px 0;
    }

    .clinic-activity-row + .clinic-activity-row,
    .needs-attention-row + .needs-attention-row {
        border-top: 1px solid rgba(112, 19, 27, 0.10);
    }

    .dashboard-row-icon {
        width: 42px;
        height: 42px;
        display: inline-grid;
        place-items: center;
        border-radius: 50%;
        background: var(--icon-bg, rgba(148, 163, 184, 0.14));
        color: var(--icon-color, #64748b);
        flex: 0 0 auto;
    }

    .dashboard-row-icon svg {
        width: 20px;
        height: 20px;
        stroke-width: 1.9;
    }

    .dashboard-row-icon.tone-rose {
        --icon-bg: rgba(244, 63, 94, 0.12);
        --icon-color: #e11d48;
    }

    .dashboard-row-icon.tone-violet {
        --icon-bg: rgba(147, 51, 234, 0.12);
        --icon-color: #9333ea;
    }

    .dashboard-row-icon.tone-green {
        --icon-bg: rgba(34, 197, 94, 0.13);
        --icon-color: #16a34a;
    }

    .dashboard-row-icon.tone-amber {
        --icon-bg: rgba(245, 158, 11, 0.14);
        --icon-color: #f59e0b;
    }

    .dashboard-row-icon.tone-blue {
        --icon-bg: rgba(59, 130, 246, 0.12);
        --icon-color: #2563eb;
    }

    .dashboard-row-main {
        min-width: 0;
    }

    .dashboard-row-title {
        color: #111827;
        font-size: 13px;
        font-weight: 800;
        line-height: 1.25;
    }

    .dashboard-row-copy {
        margin-top: 2px;
        color: #64748b;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.35;
    }

    .clinic-activity-meter {
        display: grid;
        grid-template-columns: minmax(92px, 1fr) auto;
        align-items: center;
        gap: 12px;
        min-width: 238px;
    }

    .clinic-activity-track {
        height: 8px;
        overflow: hidden;
        border-radius: 999px;
        background: #eef2f7;
    }

    .clinic-activity-fill {
        display: block;
        height: 100%;
        width: var(--activity-progress, 0%);
        min-width: 5px;
        border-radius: inherit;
        background: var(--activity-color, #94a3b8);
    }

    .clinic-activity-row.tone-rose { --activity-color: #e11d48; }
    .clinic-activity-row.tone-violet { --activity-color: #9333ea; }
    .clinic-activity-row.tone-green { --activity-color: #16a34a; }
    .clinic-activity-row.tone-amber { --activity-color: #f59e0b; }

    .clinic-activity-value {
        min-width: 44px;
        color: #111827;
        font-size: 22px;
        font-weight: 900;
        text-align: right;
        line-height: 1;
    }

    .clinic-activity-trend {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 4px;
        margin-top: 7px;
        color: #16a34a;
        font-size: 12px;
        font-weight: 900;
    }

    .clinic-activity-trend.is-down {
        color: #dc2626;
    }

    .clinic-activity-trend.is-flat {
        color: #64748b;
    }

    .needs-attention-row {
        display: grid;
        grid-template-columns: 44px minmax(0, 1fr) auto 18px;
        align-items: center;
        gap: 14px;
        padding: 13px 0;
        color: inherit;
        text-decoration: none;
        transition: transform 0.18s ease, color 0.18s ease;
    }

    .needs-attention-row:hover,
    .needs-attention-row:focus-visible {
        color: #8B0000;
        transform: translateX(3px);
        outline: none;
    }

    .needs-attention-row .dashboard-row-title,
    .needs-attention-row .dashboard-row-copy {
        display: block;
    }

    .needs-attention-count {
        color: #8B0000;
        font-size: 22px;
        font-weight: 900;
        line-height: 1;
        text-align: right;
    }

    .needs-attention-chevron {
        width: 18px;
        height: 18px;
        color: #70131B;
        stroke-width: 2.5;
    }

    html[data-theme="dark"] .dashboard-overview-card {
        background: rgba(15, 23, 42, 0.78);
        border-color: rgba(250, 204, 21, 0.16);
    }

    html[data-theme="dark"] .dashboard-overview-title,
    html[data-theme="dark"] .dashboard-row-title,
    html[data-theme="dark"] .clinic-activity-value {
        color: #f8fafc;
    }

    html[data-theme="dark"] .dashboard-overview-copy,
    html[data-theme="dark"] .dashboard-row-copy {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .dashboard-period-btn,
    html[data-theme="dark"] .needs-attention-count,
    html[data-theme="dark"] .needs-attention-chevron {
        color: #fde68a;
    }

    html[data-theme="dark"] .dashboard-period-control.is-open .dashboard-period-btn {
        border-color: rgba(250, 204, 21, 0.24);
        background: rgba(250, 204, 21, 0.10);
        color: #fde68a;
    }

    html[data-theme="dark"] .dashboard-period-menu {
        border-color: rgba(250, 204, 21, 0.18);
        background: #111827;
        box-shadow: 0 18px 40px rgba(0, 0, 0, 0.34);
    }

    html[data-theme="dark"] .dashboard-period-link {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .dashboard-period-link:hover,
    html[data-theme="dark"] .dashboard-period-link:focus-visible {
        background: #facc15;
        color: #8B0000;
    }

    html[data-theme="dark"] .dashboard-period-link.is-active {
        background: #8B0000;
        color: #facc15 !important;
    }

    html[data-theme="dark"] .dashboard-period-link.is-active span {
        color: #facc15 !important;
    }

    html[data-theme="dark"] .clinic-activity-track {
        background: rgba(255, 255, 255, 0.12);
    }

    html[data-theme="dark"] .clinic-activity-row + .clinic-activity-row,
    html[data-theme="dark"] .needs-attention-row + .needs-attention-row {
        border-color: rgba(255, 255, 255, 0.10);
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
        .dashboard-overview-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 768px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
        .clinic-activity-row { grid-template-columns: 42px minmax(0, 1fr); }
        .clinic-activity-meter { grid-column: 2; min-width: 0; }
        .needs-attention-row { grid-template-columns: 42px minmax(0, 1fr) auto 18px; }
        .dashboard-welcome { align-items: flex-start; }
    }
    @media (max-width: 500px) {
        .stats-grid { grid-template-columns: 1fr; }
        .dashboard-welcome { gap: 12px; }
        .dashboard-welcome-copy h1 { font-size: 18px; }
        .dashboard-welcome-copy p { font-size: 12px; }
        .dashboard-date-badge { min-width: auto; padding: 9px; }
        .dashboard-date-badge strong { font-size: 11px; }
        .dashboard-date-badge span { display: none; }
    }

    html[data-theme="dark"] .dashboard-welcome-copy h1,
    html[data-theme="dark"] .dashboard-date-badge strong {
        color: #f8fafc;
    }

    html[data-theme="dark"] .dashboard-welcome-copy h1 .dashboard-welcome-name {
        color: #facc15;
        text-shadow: 0 0 10px rgba(250, 204, 21, 0.16);
    }

    html[data-theme="dark"] .dashboard-welcome-copy p,
    html[data-theme="dark"] .dashboard-date-badge span {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .dashboard-date-badge {
        border-color: rgba(250, 204, 21, 0.18);
        background: rgba(15, 23, 42, 0.72);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.24);
    }

    /* Final light-mode surface pass */
    html:not([data-theme="dark"]) .stats-grid .stat-card {
        border: 1px solid rgba(250, 204, 21, 0.22) !important;
        box-shadow: 0 14px 28px rgba(112, 19, 27, 0.12), 0 4px 12px rgba(15, 23, 42, 0.06) !important;
    }

    html:not([data-theme="dark"]) .stats-grid .stat-card:hover {
        box-shadow: 0 20px 36px rgba(112, 19, 27, 0.16), 0 6px 14px rgba(15, 23, 42, 0.08) !important;
    }
</style>
@endpush

@section('content')
@php
    $dashboardPercent = function ($value, $base) {
        $base = max(0, (int) $base);
        return $base > 0 ? (int) round(((int) $value / $base) * 100) : 0;
    };
    $inventoryInStockRow = collect($inventoryChartStats)->firstWhere('label', 'In Stock');
    $inventoryInStock = (int) ($inventoryInStockRow['value'] ?? 0);
    $completionRate = $dashboardPercent($completed, $total);
    $pendingRate = $dashboardPercent($pending, $total);
    $todayRate = $dashboardPercent($upcoming, $total);
    $stockHealthRate = $dashboardPercent($inventoryInStock, $inventoryTotal);
    $dashboardWaveOffset = function ($rate) {
        $rate = max(0, min(100, (int) $rate));
        return (int) round((100 - $rate) * 0.52);
    };
    $dashboardDisplayName = trim((string) (optional(auth()->user())->name ?? 'Clinic User'));
    $dashboardNameParts = preg_split('/\s+/', $dashboardDisplayName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $dashboardFirstName = $dashboardNameParts[0] ?? 'Clinic User';
    $dashboardLastName = count($dashboardNameParts) > 1 ? (string) end($dashboardNameParts) : '';
    $dashboardWelcomeName = trim($dashboardFirstName . ($dashboardLastName !== '' ? ' ' . strtoupper(substr($dashboardLastName, 0, 1)) . '.' : ''));
    $dashboardToday = now();
    $dashboardHour = (int) $dashboardToday->format('G');
    $dashboardGreeting = $dashboardHour < 12
        ? 'Good morning'
        : ($dashboardHour < 18 ? 'Good afternoon' : 'Good evening');
@endphp
<div class="dashboard-container">
    <section class="dashboard-welcome" aria-label="Dashboard summary">
        <div class="dashboard-welcome-copy">
            <h1><span class="dashboard-time-greeting" id="dashboardTimeGreeting">{{ $dashboardGreeting }}!</span> <span class="dashboard-welcome-name">{{ $dashboardWelcomeName }}</span></h1>
            <p>Here's what's happening in the clinic today.</p>
        </div>
        <div class="dashboard-date-badge" aria-label="{{ $dashboardToday->format('F j, Y, l') }}">
            <x-outline-icon name="calendar-days" />
            <div>
                <strong>{{ $dashboardToday->format('F j, Y') }}</strong>
                <span>{{ $dashboardToday->format('l') }}</span>
            </div>
        </div>
    </section>
    <div class="stats-grid">
        
        <div class="stat-card" style="--wave-offset: {{ $dashboardWaveOffset($completionRate) }}px;">
            <div class="stat-card-top">
                <div class="stat-label">Total Appointments</div>
                <span class="stat-percent"><span class="sr-only">Completion rate </span>{{ $completionRate }}%</span>
            </div>
            <div>
                <div class="stat-value">{{ $total }}</div>
            </div>
            <div class="stat-badge badge-neutral">Completion Rate</div>
        </div>

        <div class="stat-card" style="--wave-offset: {{ $dashboardWaveOffset($pendingRate) }}px;">
            <div class="stat-card-top">
                <div class="stat-label">Pending Requests</div>
                <span class="stat-percent"><span class="sr-only">Pending rate </span>{{ $pendingRate }}%</span>
            </div>
            <div>
                <div class="stat-value">{{ $pending }}</div>
            </div>
            <div class="stat-badge badge-warning">Action Needed</div>
        </div>

        <div class="stat-card" style="--wave-offset: {{ $dashboardWaveOffset($todayRate) }}px;">
            <div class="stat-card-top">
                <div class="stat-label">Scheduled Today</div>
                <span class="stat-percent"><span class="sr-only">Scheduled today rate </span>{{ $todayRate }}%</span>
            </div>
            <div>
                <div class="stat-value">{{ $upcoming }}</div>
            </div>
            <div class="stat-badge badge-success">Scheduled</div>
        </div>

        <div class="stat-card" style="--wave-offset: {{ $dashboardWaveOffset($stockHealthRate) }}px;">
            <div class="stat-card-top">
                <div class="stat-label">Inventory Items</div>
                <span class="stat-percent"><span class="sr-only">Stock health rate </span>{{ $stockHealthRate }}%</span>
            </div>
            <div>
                <div class="stat-value">{{ $inventoryTotal }}</div>
            </div>
            <div class="stat-badge badge-info">Stock Health</div>
        </div>

    </div>

    <div class="dashboard-overview-grid" aria-label="Dashboard clinic overview">
        <section class="dashboard-overview-card">
            <div class="dashboard-overview-head">
                <div>
                    <h3 class="dashboard-overview-title">Clinic Activity</h3>
                    <p class="dashboard-overview-copy">Overview of key clinic activities.</p>
                </div>
                <div class="dashboard-period-control">
                    <button type="button" class="dashboard-period-btn" aria-haspopup="true" aria-expanded="false">
                        <span>{{ $activityPeriodOptions[$activityPeriod] ?? 'Today' }}</span>
                        <x-outline-icon name="chevron-down" />
                    </button>
                    <div class="dashboard-period-menu" role="menu" aria-label="Clinic activity period">
                        @foreach($activityPeriodOptions as $periodKey => $periodLabel)
                            <a
                                href="{{ request()->fullUrlWithQuery(['activity_period' => $periodKey]) }}"
                                class="dashboard-period-link {{ $activityPeriod === $periodKey ? 'is-active' : '' }}"
                                role="menuitem"
                            >
                                <span>{{ $periodLabel }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="clinic-activity-list">
                @foreach($clinicActivityItems as $activityItem)
                    <div class="clinic-activity-row tone-{{ $activityItem['tone'] }}">
                        <span class="dashboard-row-icon tone-{{ $activityItem['tone'] }}" aria-hidden="true">
                            <x-outline-icon name="{{ $activityItem['icon'] }}" />
                        </span>
                        <div class="dashboard-row-main">
                            <div class="dashboard-row-title">{{ $activityItem['title'] }}</div>
                            <div class="dashboard-row-copy">{{ $activityItem['description'] }}</div>
                        </div>
                        <div class="clinic-activity-meter">
                            <div class="clinic-activity-track" aria-hidden="true">
                                <span class="clinic-activity-fill" style="--activity-progress: {{ $activityItem['progress'] }}%;"></span>
                            </div>
                            <div>
                                <div class="clinic-activity-value">{{ number_format($activityItem['value']) }}</div>
                                <div class="clinic-activity-trend is-{{ $activityItem['trend']['direction'] }}">
                                    @if($activityItem['trend']['direction'] === 'down')
                                        <span aria-hidden="true">&darr;</span>
                                    @elseif($activityItem['trend']['direction'] === 'up')
                                        <span aria-hidden="true">&uarr;</span>
                                    @else
                                        <span aria-hidden="true">&minus;</span>
                                    @endif
                                    <span>{{ $activityItem['trend']['value'] }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="dashboard-overview-card">
            <div class="dashboard-overview-head">
                <div>
                    <h3 class="dashboard-overview-title">Needs Attention</h3>
                    <p class="dashboard-overview-copy">Items that need your immediate action.</p>
                </div>
            </div>

            <div class="needs-attention-list">
                @foreach($needsAttentionItems as $attentionItem)
                    <a href="{{ $attentionItem['url'] }}" class="needs-attention-row">
                        <span class="dashboard-row-icon tone-{{ $attentionItem['tone'] }}" aria-hidden="true">
                            <x-outline-icon name="{{ $attentionItem['icon'] }}" />
                        </span>
                        <span class="dashboard-row-main">
                            <span class="dashboard-row-title">{{ $attentionItem['title'] }}</span>
                            <span class="dashboard-row-copy">{{ $attentionItem['description'] }}</span>
                        </span>
                        <span class="needs-attention-count">{{ number_format($attentionItem['value']) }}</span>
                        <x-outline-icon name="chevron-right" class="needs-attention-chevron" />
                    </a>
                @endforeach
            </div>
        </section>
    </div>

    <div class="panel recent-activity-panel" id="recentActivityPanel">
        <div class="panel-header">
            <h3 class="panel-title">Recent Activity</h3>
            @if($recentActivities->count() > 5)
                <button type="button" class="btn-view-all" id="recentActivityToggle" aria-expanded="false" aria-controls="recentActivityTable">
                    <span>Show All</span>
                    <x-outline-icon name="chevron-down" />
                </button>
            @endif
        </div>

        <div class="recent-activity-table-wrap" id="recentActivityTable">
            <table>
                <thead>
                    <tr>
                        <th>Patient / Record</th>
                        <th>Activity</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentActivities as $activity)
                        <tr class="{{ $loop->iteration > 5 ? 'recent-activity-extra' : '' }}">
                            <td style="font-weight: 600;">
                                <a href="{{ $activity->url }}" style="color: inherit; text-decoration: none;">
                                    {{ $activity->name }}
                                </a><br>
                                <span style="font-size:11px; color:#94a3b8; font-weight:400;">{{ $activity->identifier ?: ucfirst($activity->kind) }}</span>
                            </td>
                            <td>
                                <span class="recent-activity-kind">{{ $activity->kind }}</span>
                                <span style="display:block; margin-top:6px;">{{ $activity->activity }}</span>
                            </td>
                            <td>{{ $activity->date_label }}</td>
                            <td><span class="status-pill {{ $activity->status_class }}">{{ $activity->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="text-align: center; padding: 30px; color: #94a3b8;">No recent activity.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    (function () {
        const greetingElement = document.getElementById('dashboardTimeGreeting');
        if (!greetingElement) {
            return;
        }

        const updateDashboardGreeting = function () {
            const hour = new Date().getHours();
            const greeting = hour < 12
                ? 'Good morning'
                : (hour < 18 ? 'Good afternoon' : 'Good evening');
            greetingElement.textContent = `${greeting}!`;
        };

        updateDashboardGreeting();
        window.setInterval(updateDashboardGreeting, 60000);
    })();

    (function () {
        const panel = document.getElementById('recentActivityPanel');
        const toggle = document.getElementById('recentActivityToggle');
        if (!panel || !toggle) {
            return;
        }

        toggle.addEventListener('click', function () {
            const isExpanded = panel.classList.toggle('is-expanded');
            toggle.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
            const label = toggle.querySelector('span');
            if (label) {
                label.textContent = isExpanded ? 'Show Less' : 'Show All';
            }
        });
    })();

    (function () {
        const control = document.querySelector('.dashboard-period-control');
        const button = control ? control.querySelector('.dashboard-period-btn') : null;
        if (!control || !button) {
            return;
        }

        const setOpen = function (isOpen) {
            control.classList.toggle('is-open', isOpen);
            button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        };

        button.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            setOpen(!control.classList.contains('is-open'));
        });

        document.addEventListener('click', function (event) {
            if (!control.contains(event.target)) {
                setOpen(false);
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                setOpen(false);
            }
        });
    })();
</script>
@endpush
