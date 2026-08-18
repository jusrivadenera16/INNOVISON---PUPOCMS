@extends('layouts.admin')

@section('title', 'Reports')

@push('styles')
<style>
    /* --- DASHBOARD CONTAINER --- */
    .dashboard-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }

    /* --- REPORTS HEADER --- */
    .reports-header {
        margin-bottom: 24px;
        padding-bottom: 24px;
        border-bottom: 1px solid #e2e8f0;
    }

    .reports-header-kicker {
        margin: 0 0 8px;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 1.2px;
        color: #70131B;
        text-transform: uppercase;
    }

    .reports-header-title {
        margin: 0 0 12px;
        font-size: 22px;
        font-weight: 800;
        letter-spacing: 0;
        color: #111827;
        line-height: 1.25;
    }

    .reports-header-description {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: #64748b;
        max-width: 720px;
        line-height: 1.45;
    }

    html[data-theme="dark"] .reports-header-kicker {
        color: #facc15;
    }

    html[data-theme="dark"] .reports-header-title {
        color: #f8fafc;
    }

    html[data-theme="dark"] .reports-header-description {
        color: #cbd5e1;
    }

    /* --- TOP STATS ROW (Naiiwan sa pwesto gaya ng gusto mo) --- */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 40px;
    }

    .stat-card-mini {
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        border-left: 5px solid #8B0000;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        display: flex;
        flex-direction: column;
    }

    .stat-card-mini span { font-size: 13px; color: #64748b; font-weight: 600; text-transform: uppercase; }
    .stat-card-mini h3 { font-size: 24px; color: #4b0f17; margin: 5px 0 0 0; }

    .reports-section-title {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: 18px;
        color: #111827;
        margin: 0;
        font-weight: 800;
        padding: 10px 18px;
        border-radius: 0 0 14px 14px;
        border: 0;
        border-bottom: 2px solid rgba(112, 19, 27, 0.72);
        background: transparent;
        box-shadow: none;
    }

    .reports-section-title svg {
        width: 18px;
        height: 18px;
        flex: 0 0 auto;
    }

    .reports-title-frame {
        display: inline-flex;
        align-items: center;
        border-radius: 0;
        border: 0;
        padding: 0;
        background: transparent;
        box-shadow: none;
        margin-bottom: 20px;
    }

    .reports-frame {
        position: relative;
        overflow: hidden;
        border-radius: 22px;
        border: 1px solid rgba(112, 19, 27, 0.10);
        padding: 24px;
        background: linear-gradient(180deg, rgba(255,255,255,0.92), rgba(250,244,246,0.96));
        box-shadow: 0 18px 34px rgba(112, 19, 27, 0.08);
    }

    .reports-frame::before {
        content: "";
        position: absolute;
        top: 0;
        left: 14px;
        right: 14px;
        height: 5px;
        background: #70131B;
        border-radius: 999px;
        pointer-events: none;
        z-index: 1;
    }

    /* --- REPORT BUTTONS (Gayang-gaya sa Dashboard Stats Cards) --- */
    .report-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr); /* 3 Columns para sa MAR, Inventory, Appt */
        gap: 20px;
    }

    .report-card {
        background: #70131B; /* Dark Maroon/Dark Navy style mo */
        color: #fff;
        border-radius: 16px;
        padding: 24px 20px;
        text-decoration: none; /* Alisin ang underline ng link */
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 160px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        border: 1px solid rgba(112, 19, 27, 0.22);
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .report-card > * {
        position: relative;
        z-index: 2;
    }

    .report-card::after {
        content: "";
        position: absolute;
        top: -45%;
        bottom: -45%;
        left: -85%;
        width: 55%;
        background: linear-gradient(
            120deg,
            transparent 0%,
            rgba(255, 255, 255, 0.16) 45%,
            rgba(255, 255, 255, 0.34) 50%,
            transparent 100%
        );
        transform: translateX(0) skewX(-18deg);
        transition: transform 0.65s ease;
        pointer-events: none;
        z-index: 1;
    }

    .report-card:hover {
        background: #facc15 !important;
        background-image: none !important;
        color: #111111 !important;
        transform: translateY(-8px);
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.12),
            0 20px 30px rgba(139, 0, 0, 0.22);
        filter: none;
        border-color: #facc15 !important;
    }

    .report-card:hover::after {
        transform: translateX(360%) skewX(-18deg);
    }

    .report-card:hover .report-label,
    .report-card:hover .report-main-title,
    .report-card:hover .report-badge,
    .report-card:hover .report-card-icon {
        color: #111111 !important;
    }

    .report-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: 18px;
    }

    .report-card-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 14px;
        color: #facc15;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.18);
        transition: transform 0.3s ease, background 0.3s ease, color 0.3s ease;
    }

    .report-card-icon svg {
        width: 22px;
        height: 22px;
        stroke-width: 1.8;
    }

    .report-card:hover .report-card-icon {
        background: rgba(17, 17, 17, 0.10);
        border-color: rgba(17, 17, 17, 0.14);
        transform: translateX(3px) scale(1.04);
    }

    .report-label {
        font-size: 14px;
        font-weight: 500;
        color: #cbd5e1; /* Muted gray text */
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }

    .report-main-title {
        font-size: 22px;
        font-weight: 700;
        color: #fff;
        line-height: 1.2;
    }

    /* The "Pill" at the bottom gaya ng "Action Needed", etc. */
    .report-badge {
        display: inline-block;
        font-size: 11px;
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 8px;
        width: fit-content;
        background: rgba(255,255,255,0.1);
        color: #fff;
    }

    .report-card:hover .report-badge {
        background: rgba(17, 17, 17, 0.10) !important;
    }

    /* Audit Trail Styling */
    .report-card-audit {
        background: #70131B !important;
        border: 2px solid rgba(250, 204, 21, 0.62) !important;
        position: relative;
    }

    .report-card-audit::before {
        background: none !important;
    }

    .report-card-audit:hover {
        background: #facc15 !important;
        border-color: #facc15 !important;
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.12),
            0 20px 30px rgba(139, 0, 0, 0.22) !important;
    }

    .report-card-audit .report-card-icon {
        color: #facc15;
        background: rgba(255, 255, 255, 0.12);
        border-color: rgba(255, 248, 196, 0.16);
    }

    .report-card-audit:hover .report-card-icon {
        background: rgba(17, 17, 17, 0.10);
        border-color: rgba(17, 17, 17, 0.14);
        color: #000;
    }

    html[data-theme="dark"] .report-card-audit {
        background: #70131B !important;
        border-color: rgba(250, 204, 21, 0.62) !important;
    }

    html[data-theme="dark"] .report-card-audit:hover {
        background: #facc15 !important;
        border-color: #facc15 !important;
    }

    .back-nav {
        margin-top: 40px;
        text-align: center;
    }

    .btn-back-dashboard {
        color: #64748b;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        transition: color 0.2s;
    }

    .btn-back-dashboard:hover { color: #8B0000; }

    html[data-theme="dark"] .stat-card-mini span,
    html[data-theme="dark"] .stat-card-mini h3,
    html[data-theme="dark"] .reports-section-title {
        color: #ffffff;
        border-bottom-color: rgba(143, 34, 48, 0.70);
    }

    html[data-theme="dark"] .reports-title-frame {
        background: transparent;
        border: 0;
        box-shadow: none;
    }

    html[data-theme="dark"] .reports-frame {
        background: linear-gradient(180deg, rgba(70, 19, 27, 0.92), rgba(46, 13, 19, 0.96));
        border-color: rgba(255, 255, 255, 0.08);
        box-shadow: 0 20px 38px rgba(0, 0, 0, 0.24);
    }

    html[data-theme="dark"] .reports-frame::before {
        background: #facc15;
    }

    /* MOBILE RESPONSIVE FIXES */
    @media (max-width: 768px) {
        /* Reports Page Buttons - Stack Vertically on Mobile */
        .reports-actions,
        .action-buttons,
        .control-buttons {
            display: flex !important;
            flex-direction: column !important;
            gap: 8px !important;
            margin: 10px 0 !important;
        }

        .reports-actions button,
        .action-buttons button,
        .control-buttons button {
            width: 100% !important;
            padding: 10px 12px !important;
            font-size: 13px !important;
            min-height: 40px !important;
            white-space: normal !important;
            word-break: break-word !important;
            flex: none !important;
        }

        /* Manage MAR Buttons - Mobile Wrap */
        .manage-mar-controls,
        .category-controls,
        .filter-controls {
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 8px !important;
            align-items: center !important;
        }

        .manage-mar-controls button,
        .manage-mar-controls select,
        .manage-mar-controls input,
        .category-controls button,
        .category-controls select,
        .filter-controls button,
        .filter-controls select {
            flex: 1 1 auto !important;
            min-width: 100px !important;
            max-width: 100% !important;
            font-size: 12px !important;
        }

        /* Table Horizontal Scroll */
        .table-responsive,
        .table-wrapper,
        .inventory-table-container {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
            margin: 0 -8px !important;
            padding: 0 8px !important;
        }

        table {
            min-width: 100% !important;
        }

        /* Scrollbar Styling */
        .table-responsive::-webkit-scrollbar,
        .table-wrapper::-webkit-scrollbar {
            height: 6px;
        }

        .table-responsive::-webkit-scrollbar-track,
        .table-wrapper::-webkit-scrollbar-track {
            background: rgba(0,0,0,0.05);
        }

        .table-responsive::-webkit-scrollbar-thumb,
        .table-wrapper::-webkit-scrollbar-thumb {
            background: #70131B;
            border-radius: 3px;
        }

        /* Modal Responsiveness */
        .modal-box {
            max-height: 95vh !important;
            width: 95vw !important;
            max-width: 95vw !important;
            overflow-y: auto !important;
        }
    }

    /* DARK MODE FIXES */

    /* MAR Date Field - Dark Mode */
    html[data-theme="dark"] input[type="date"],
    html[data-theme="dark"] .date-field,
    html[data-theme="dark"] input[type="datetime-local"] {
        background: rgba(18, 18, 18, 0.55) !important;
        color: #f8fafc !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
    }

    html[data-theme="dark"] input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(1) brightness(1.2) !important;
        cursor: pointer !important;
    }

    /* Inventory Summary Cards - Dark Mode */
    html[data-theme="dark"] .inventory-summary-card,
    html[data-theme="dark"] .summary-card,
    html[data-theme="dark"] .total-card {
        background: rgba(35, 17, 25, 0.96) !important;
        border-color: rgba(255, 255, 255, 0.08) !important;
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .inventory-summary-card h3,
    html[data-theme="dark"] .summary-card h3,
    html[data-theme="dark"] .total-card h3 {
        color: #f3d6da !important;
    }

    html[data-theme="dark"] .inventory-summary-card .value,
    html[data-theme="dark"] .summary-card .value {
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .inventory-summary-card .label,
    html[data-theme="dark"] .summary-card .label {
        color: rgba(248, 250, 252, 0.7) !important;
    }

    /* Inventory Performance Table - Dark Mode */
    html[data-theme="dark"] .inventory-performance-table,
    html[data-theme="dark"] .performance-table {
        background: rgba(18, 18, 18, 0.4) !important;
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .inventory-performance-table thead,
    html[data-theme="dark"] .performance-table thead {
        background: rgba(18, 18, 18, 0.55) !important;
    }

    html[data-theme="dark"] .inventory-performance-table th,
    html[data-theme="dark"] .performance-table th {
        background: rgba(18, 18, 18, 0.55) !important;
        color: #f8fafc !important;
        border-color: rgba(255, 255, 255, 0.08) !important;
    }

    html[data-theme="dark"] .inventory-performance-table td,
    html[data-theme="dark"] .performance-table td {
        border-color: rgba(255, 255, 255, 0.08) !important;
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .inventory-performance-table tbody tr:hover,
    html[data-theme="dark"] .performance-table tbody tr:hover {
        background: rgba(59, 24, 33, 0.5) !important;
    }

    /* Form Controls - Dark Mode */
    html[data-theme="dark"] input[type="text"],
    html[data-theme="dark"] input[type="number"],
    html[data-theme="dark"] input[type="email"],
    html[data-theme="dark"] input[type="search"],
    html[data-theme="dark"] select,
    html[data-theme="dark"] textarea {
        background: rgba(18, 18, 18, 0.55) !important;
        color: #f8fafc !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
    }

    html[data-theme="dark"] input::placeholder,
    html[data-theme="dark"] textarea::placeholder {
        color: rgba(248, 250, 252, 0.5) !important;
    }

    /* Reports Buttons - Dark Mode */
    html[data-theme="dark"] .reports-actions button,
    html[data-theme="dark"] .action-buttons button,
    html[data-theme="dark"] .control-buttons button {
        background: rgba(59, 24, 33, 0.96) !important;
        color: #f8fafc !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
    }

    html[data-theme="dark"] .reports-actions button:hover,
    html[data-theme="dark"] .action-buttons button:hover,
    html[data-theme="dark"] .control-buttons button:hover {
        background: rgba(112, 19, 27, 0.96) !important;
    }

    /* === MOBILE RESPONSIVE FIXES FOR REPORTS MODULE === */
    @media (max-width: 1024px) {
        .report-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
    }

    @media (max-width: 768px) {
        .report-grid {
            grid-template-columns: 1fr;
            gap: 14px;
        }

        .report-card {
            padding: 20px 18px;
            min-height: 140px;
            border-radius: 14px;
        }

        .report-label {
            font-size: 11px !important;
        }

        .report-main-title {
            font-size: 18px !important;
            margin-top: 8px !important;
        }

        .report-card-footer {
            margin-top: 14px;
            gap: 8px;
        }

        .report-card-icon svg {
            width: 24px !important;
            height: 24px !important;
        }

        .report-badge {
            padding: 6px 10px !important;
            font-size: 10px !important;
        }
    }

    @media (max-width: 600px) {
        .report-grid {
            grid-template-columns: 1fr;
            gap: 12px;
            padding: 0;
        }

        .report-card {
            padding: 18px 16px;
            min-height: 130px;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.08);
        }

        .report-label {
            font-size: 10px !important;
            letter-spacing: 0.06em !important;
        }

        .report-main-title {
            font-size: 16px !important;
            margin-top: 6px !important;
            font-weight: 800 !important;
            line-height: 1.3 !important;
        }

        .report-card-footer {
            margin-top: 12px;
            gap: 6px;
        }

        .report-card-icon {
            margin-top: 0 !important;
        }

        .report-card-icon svg {
            width: 22px !important;
            height: 22px !important;
        }

        .report-badge {
            padding: 5px 9px !important;
            font-size: 9px !important;
            min-width: 32px !important;
            min-height: 28px !important;
        }
    }

    @media (max-width: 480px) {
        .report-card {
            padding: 16px 14px;
            min-height: 120px;
        }

        .report-label {
            font-size: 9px !important;
        }

        .report-main-title {
            font-size: 15px !important;
            margin-top: 5px !important;
        }

        .report-card-footer {
            margin-top: 10px;
        }

        .report-card-icon svg {
            width: 20px !important;
            height: 20px !important;
        }

        .report-badge {
            padding: 4px 8px !important;
            font-size: 8px !important;
        }
    }

    /* Dark mode mobile adjustments */
    @media (max-width: 768px) {
        html[data-theme="dark"] .report-card {
            background: rgba(112, 19, 27, 0.96) !important;
            border-color: rgba(250, 204, 21, 0.12) !important;
        }

        html[data-theme="dark"] .report-card:hover {
            background: #facc15 !important;
            border-color: #facc15 !important;
        }
    }

    /* Settings-style Reports hub */
    .reports-frame {
        max-width: 1180px;
        margin: 0 auto;
        padding: 24px 28px 28px;
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.94);
        overflow: hidden;
    }
    .reports-header {
        margin: 0 0 24px;
        padding: 0;
        border: 0;
    }
    .reports-header-title {
        margin: 0 0 10px;
        color: #8b0000;
        font-size: 16px;
        font-weight: 800;
        letter-spacing: 1px;
        line-height: 1.2;
        text-transform: uppercase;
    }
    .reports-header-description {
        max-width: 760px;
        margin: 0;
        color: #0f172a;
        font-size: 22px;
        font-weight: 800;
        line-height: 1.25;
    }
    .report-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
        gap: 16px;
        margin-top: 24px;
    }
    .report-card,
    .report-card.report-card-audit {
        position: relative;
        min-height: 238px;
        height: 100%;
        display: block;
        padding: 18px;
        border-radius: 16px;
        border: 1px solid rgba(112, 19, 27, 0.46) !important;
        background: linear-gradient(135deg, #70131B, #8f2230) !important;
        color: #ffffff !important;
        box-shadow: inset 0 -3px 0 rgba(250, 204, 21, 0.72), 0 10px 24px rgba(112, 19, 27, 0.18);
        overflow: hidden;
        text-decoration: none;
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease, color .2s ease;
    }
    .report-card::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background: linear-gradient(180deg, rgba(255,255,255,0.08) 0%, rgba(255,255,255,0) 38%);
        z-index: 0;
    }
    .report-card::after {
        content: "";
        position: absolute;
        top: -38%;
        bottom: -38%;
        left: -135%;
        width: 34%;
        opacity: 0;
        background: linear-gradient(105deg, rgba(255, 255, 255, 0) 0%, rgba(255, 246, 184, 0.18) 42%, rgba(255, 246, 184, 0.54) 50%, rgba(255, 246, 184, 0.18) 58%, rgba(255, 255, 255, 0) 100%);
        transform: translateX(0) skewX(-18deg);
        pointer-events: none;
        z-index: 0;
    }
    .report-card:hover,
    .report-card:focus-visible,
    .report-card.report-card-audit:hover,
    .report-card.report-card-audit:focus-visible {
        background: #facc15 !important;
        background-image: none !important;
        color: #70131B !important;
        transform: translateY(-8px);
        box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.12), 0 20px 30px rgba(139, 0, 0, 0.22) !important;
        border-color: #facc15 !important;
        outline: none;
    }
    .report-card:hover::after,
    .report-card:focus-visible::after {
        animation: reportsSettingsSweep .92s ease both;
    }
    @keyframes reportsSettingsSweep {
        0% { opacity: 0; transform: translateX(0) skewX(-18deg); }
        18% { opacity: .72; }
        72% { opacity: .72; }
        100% { opacity: 0; transform: translateX(820%) skewX(-18deg); }
    }
    .report-card > * {
        position: relative;
        z-index: 1;
    }
    .report-card-chip {
        position: absolute;
        top: 12px;
        right: 12px;
        width: 28px;
        height: 28px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
        border: 1px solid rgba(255, 248, 196, 0.72);
        background: rgba(255, 248, 196, 0.12);
        color: #ffffff;
        box-shadow: 0 8px 14px rgba(15, 23, 42, 0.14);
        transition: background .2s ease, color .2s ease, border-color .2s ease;
    }
    .report-card-chip svg {
        width: 13px;
        height: 13px;
        stroke-width: 2.6;
    }
    .report-card-icon,
    .report-card.report-card-audit .report-card-icon {
        position: relative;
        z-index: 1;
        width: 58px;
        height: 58px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
        background: rgba(255, 248, 196, 0.12) !important;
        color: #ffffff !important;
        border: 1px solid rgba(255, 248, 196, 0.16) !important;
        animation: reportsSettingsFloat 3.8s ease-in-out infinite;
        transition: background .2s ease, color .2s ease, border-color .2s ease;
    }
    .report-card-icon svg {
        width: 24px !important;
        height: 24px !important;
        stroke-width: 2.1;
    }
    @keyframes reportsSettingsFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }
    .report-main-title {
        margin: 0 0 10px;
        color: #ffffff !important;
        font-size: 18px;
        font-weight: 800;
        line-height: 1.25;
        text-align: left;
        transition: color .2s ease;
    }
    .report-card-copy {
        margin: 0;
        color: #ffffff !important;
        font-size: 16px;
        font-weight: 600;
        line-height: 1.42;
        text-align: left;
        transition: color .2s ease;
    }
    .report-label,
    .report-card-footer,
    .report-badge {
        display: none !important;
    }
    .report-card:hover .report-main-title,
    .report-card:focus-visible .report-main-title,
    .report-card:hover .report-card-copy,
    .report-card:focus-visible .report-card-copy {
        color: #70131B !important;
    }
    .report-card:hover .report-card-icon,
    .report-card:focus-visible .report-card-icon,
    .report-card:hover .report-card-chip,
    .report-card:focus-visible .report-card-chip {
        background: #70131B !important;
        color: #ffffff !important;
        border-color: rgba(112, 19, 27, 0.62) !important;
        transform: none;
    }
    html[data-theme="dark"] .reports-frame {
        background: linear-gradient(180deg, rgba(70, 19, 27, 0.92), rgba(46, 13, 19, 0.96));
        border-color: rgba(255,255,255,.08);
    }
    html[data-theme="dark"] .reports-header-title,
    html[data-theme="dark"] .reports-header-description {
        color: #ffffff;
    }
    html[data-theme="dark"] .report-card,
    html[data-theme="dark"] .report-card.report-card-audit {
        background: #70131B !important;
        border-color: rgba(250, 204, 21, 0.62) !important;
        box-shadow: inset 0 -3px 0 rgba(250, 204, 21, 0.92), 0 14px 26px rgba(0,0,0,.22);
    }
    html[data-theme="dark"] .report-card::before {
        background: none;
    }
    html[data-theme="dark"] .report-card:hover,
    html[data-theme="dark"] .report-card:focus-visible {
        background: #facc15 !important;
        border-color: #facc15 !important;
    }
    @media (max-width: 980px) {
        .report-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }
    }
    @media (max-width: 720px) {
        .dashboard-container {
            padding: 12px;
        }
        .reports-frame {
            max-width: 100%;
            padding: 20px 18px;
        }
        .report-grid {
            grid-template-columns: 1fr !important;
        }
        .report-card {
            min-height: 210px;
        }
    }
    /* Patient Intake-style compact container behavior */
    .reports-frame {
        width: min(100%, 980px) !important;
        max-width: 980px !important;
        margin: 0 auto !important;
        padding: 16px 16px 18px !important;
        border-radius: 12px !important;
    }
    .reports-frame::before {
        left: 12px !important;
        right: 12px !important;
    }
    .reports-header {
        margin: 0 0 18px !important;
        padding: 0 !important;
    }
    .reports-header-title {
        font-size: 13px !important;
        margin-bottom: 8px !important;
    }
    .reports-header-description {
        max-width: 760px !important;
        font-size: 20px !important;
        line-height: 1.2 !important;
    }
    .report-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
        gap: 12px !important;
        margin-top: 20px !important;
    }
    .report-card,
    .report-card.report-card-audit {
        min-height: 258px !important;
        padding: 20px !important;
        border-radius: 12px !important;
    }
    .report-card-icon,
    .report-card.report-card-audit .report-card-icon {
        width: 48px !important;
        height: 48px !important;
        border-radius: 13px !important;
        margin-bottom: 18px !important;
    }
    .report-card-icon svg {
        width: 21px !important;
        height: 21px !important;
    }
    .report-main-title {
        font-size: 18px !important;
        margin-bottom: 10px !important;
    }
    .report-card-copy {
        font-size: 15px !important;
        line-height: 1.42 !important;
    }
    @media (max-width: 920px) {
        .reports-frame {
            width: 100% !important;
            max-width: 100% !important;
        }
        .report-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }
    }
    @media (max-width: 560px) {
        .reports-frame {
            width: 100% !important;
            max-width: 100% !important;
            padding: 18px !important;
        }
        .report-grid {
            grid-template-columns: 1fr !important;
        }
    }
    .report-card .report-card-icon,
    .report-card.report-card-audit .report-card-icon,
    .report-card:hover .report-card-icon,
    .report-card:focus-visible .report-card-icon,
    .report-card.report-card-audit:hover .report-card-icon,
    .report-card.report-card-audit:focus-visible .report-card-icon {
        color: #facc15 !important;
    }
    .report-card .report-card-icon svg,
    .report-card:hover .report-card-icon svg,
    .report-card:focus-visible .report-card-icon svg {
        stroke: currentColor !important;
    }

    /* Patient Intake sizing parity */
    .report-grid {
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)) !important;
        gap: 16px !important;
        margin-top: 24px !important;
    }
    .report-card,
    .report-card.report-card-audit {
        min-height: 314px !important;
        height: 100% !important;
        padding: 20px !important;
        border-radius: 16px !important;
    }
    .report-card-chip {
        top: 12px !important;
        right: 12px !important;
        width: 28px !important;
        height: 28px !important;
    }
    .report-card-icon,
    .report-card.report-card-audit .report-card-icon {
        width: 58px !important;
        height: 58px !important;
        border-radius: 16px !important;
        margin-bottom: 14px !important;
    }
    .report-card-icon svg {
        width: 24px !important;
        height: 24px !important;
    }
    .report-main-title {
        margin: 0 0 8px !important;
        font-size: 18px !important;
        line-height: 1.25 !important;
    }
    .report-card-copy {
        font-size: 16px !important;
        line-height: 1.55 !important;
    }
    .report-card,
    .report-card.report-card-primary,
    .report-card.report-card-audit,
    html[data-theme="dark"] .report-card,
    html[data-theme="dark"] .report-card.report-card-primary,
    html[data-theme="dark"] .report-card.report-card-audit {
        background: #70131B !important;
        background-image: none !important;
    }
    .report-card::before,
    .report-card.report-card-primary::before,
    .report-card.report-card-audit::before {
        background: linear-gradient(180deg, rgba(255,255,255,0.08) 0%, rgba(255,255,255,0) 38%) !important;
    }
    .report-card:hover,
    .report-card:focus-visible,
    .report-card.report-card-primary:hover,
    .report-card.report-card-primary:focus-visible,
    .report-card.report-card-audit:hover,
    .report-card.report-card-audit:focus-visible,
    html[data-theme="dark"] .report-card:hover,
    html[data-theme="dark"] .report-card:focus-visible,
    html[data-theme="dark"] .report-card.report-card-primary:hover,
    html[data-theme="dark"] .report-card.report-card-primary:focus-visible,
    html[data-theme="dark"] .report-card.report-card-audit:hover,
    html[data-theme="dark"] .report-card.report-card-audit:focus-visible {
        background: #facc15 !important;
        background-image: none !important;
    }
    .report-card .report-card-icon,
    .report-card.report-card-primary .report-card-icon,
    .report-card.report-card-audit .report-card-icon,
    .report-card:hover .report-card-icon,
    .report-card:focus-visible .report-card-icon,
    .report-card.report-card-primary:hover .report-card-icon,
    .report-card.report-card-primary:focus-visible .report-card-icon,
    .report-card.report-card-audit:hover .report-card-icon,
    .report-card.report-card-audit:focus-visible .report-card-icon {
        color: #facc15 !important;
    }
    .report-card .report-card-icon svg,
    .report-card .report-card-icon svg *,
    .report-card.report-card-primary .report-card-icon svg,
    .report-card.report-card-primary .report-card-icon svg *,
    .report-card.report-card-audit .report-card-icon svg,
    .report-card.report-card-audit .report-card-icon svg *,
    .report-card:hover .report-card-icon svg,
    .report-card:hover .report-card-icon svg *,
    .report-card:focus-visible .report-card-icon svg,
    .report-card:focus-visible .report-card-icon svg *,
    .report-card.report-card-primary:hover .report-card-icon svg,
    .report-card.report-card-primary:hover .report-card-icon svg *,
    .report-card.report-card-primary:focus-visible .report-card-icon svg,
    .report-card.report-card-primary:focus-visible .report-card-icon svg *,
    .report-card.report-card-audit:hover .report-card-icon svg,
    .report-card.report-card-audit:hover .report-card-icon svg *,
    .report-card.report-card-audit:focus-visible .report-card-icon svg,
    .report-card.report-card-audit:focus-visible .report-card-icon svg * {
        color: #facc15 !important;
        stroke: #facc15 !important;
    }
    .reports-header-description {
        max-width: none !important;
        width: 100% !important;
    }

    /* Final uniform report-card color pass */
    .report-card,
    .report-card.report-card-primary,
    .report-card.report-card-audit,
    html[data-theme="dark"] .report-card,
    html[data-theme="dark"] .report-card.report-card-primary,
    html[data-theme="dark"] .report-card.report-card-audit {
        background: #70131B !important;
        background-image: none !important;
        border: 1px solid rgba(250, 204, 21, 0.62) !important;
        box-shadow: inset 0 -3px 0 rgba(250, 204, 21, 0.92), 0 14px 26px rgba(0, 0, 0, 0.18) !important;
        color: #ffffff !important;
    }

    .report-card::before,
    .report-card.report-card-primary::before,
    .report-card.report-card-audit::before,
    html[data-theme="dark"] .report-card::before,
    html[data-theme="dark"] .report-card.report-card-primary::before,
    html[data-theme="dark"] .report-card.report-card-audit::before {
        background: linear-gradient(180deg, rgba(255,255,255,0.08) 0%, rgba(255,255,255,0) 38%) !important;
    }

    .report-card:hover,
    .report-card:focus-visible,
    .report-card.report-card-primary:hover,
    .report-card.report-card-primary:focus-visible,
    .report-card.report-card-audit:hover,
    .report-card.report-card-audit:focus-visible,
    html[data-theme="dark"] .report-card:hover,
    html[data-theme="dark"] .report-card:focus-visible,
    html[data-theme="dark"] .report-card.report-card-primary:hover,
    html[data-theme="dark"] .report-card.report-card-primary:focus-visible,
    html[data-theme="dark"] .report-card.report-card-audit:hover,
    html[data-theme="dark"] .report-card.report-card-audit:focus-visible {
        background: #facc15 !important;
        background-image: none !important;
        border-color: #facc15 !important;
        color: #70131B !important;
    }

    .report-card .report-main-title,
    .report-card .report-card-copy,
    html[data-theme="dark"] .report-card .report-main-title,
    html[data-theme="dark"] .report-card .report-card-copy {
        color: #ffffff !important;
    }

    .report-card:hover .report-main-title,
    .report-card:hover .report-card-copy,
    .report-card:focus-visible .report-main-title,
    .report-card:focus-visible .report-card-copy,
    html[data-theme="dark"] .report-card:hover .report-main-title,
    html[data-theme="dark"] .report-card:hover .report-card-copy,
    html[data-theme="dark"] .report-card:focus-visible .report-main-title,
    html[data-theme="dark"] .report-card:focus-visible .report-card-copy {
        color: #70131B !important;
    }

    .report-card .report-card-icon,
    .report-card.report-card-primary .report-card-icon,
    .report-card.report-card-audit .report-card-icon,
    html[data-theme="dark"] .report-card .report-card-icon,
    html[data-theme="dark"] .report-card.report-card-primary .report-card-icon,
    html[data-theme="dark"] .report-card.report-card-audit .report-card-icon {
        color: #facc15 !important;
        background: rgba(255, 248, 196, 0.12) !important;
        border-color: rgba(255, 248, 196, 0.16) !important;
    }

    .report-card .report-card-icon svg,
    .report-card .report-card-icon svg *,
    .report-card.report-card-primary .report-card-icon svg,
    .report-card.report-card-primary .report-card-icon svg *,
    .report-card.report-card-audit .report-card-icon svg,
    .report-card.report-card-audit .report-card-icon svg *,
    html[data-theme="dark"] .report-card .report-card-icon svg,
    html[data-theme="dark"] .report-card .report-card-icon svg *,
    html[data-theme="dark"] .report-card.report-card-primary .report-card-icon svg,
    html[data-theme="dark"] .report-card.report-card-primary .report-card-icon svg *,
    html[data-theme="dark"] .report-card.report-card-audit .report-card-icon svg,
    html[data-theme="dark"] .report-card.report-card-audit .report-card-icon svg * {
        color: #facc15 !important;
        stroke: #facc15 !important;
    }

    .report-card:hover .report-card-icon,
    .report-card:focus-visible .report-card-icon,
    .report-card.report-card-primary:hover .report-card-icon,
    .report-card.report-card-primary:focus-visible .report-card-icon,
    .report-card.report-card-audit:hover .report-card-icon,
    .report-card.report-card-audit:focus-visible .report-card-icon,
    html[data-theme="dark"] .report-card:hover .report-card-icon,
    html[data-theme="dark"] .report-card:focus-visible .report-card-icon,
    html[data-theme="dark"] .report-card.report-card-primary:hover .report-card-icon,
    html[data-theme="dark"] .report-card.report-card-primary:focus-visible .report-card-icon,
    html[data-theme="dark"] .report-card.report-card-audit:hover .report-card-icon,
    html[data-theme="dark"] .report-card.report-card-audit:focus-visible .report-card-icon {
        background: #70131B !important;
        border-color: rgba(112, 19, 27, 0.88) !important;
        color: #facc15 !important;
        box-shadow: 0 12px 22px rgba(112, 19, 27, 0.28) !important;
    }

    .report-card:hover .report-card-icon svg,
    .report-card:hover .report-card-icon svg *,
    .report-card:focus-visible .report-card-icon svg,
    .report-card:focus-visible .report-card-icon svg *,
    html[data-theme="dark"] .report-card:hover .report-card-icon svg,
    html[data-theme="dark"] .report-card:hover .report-card-icon svg *,
    html[data-theme="dark"] .report-card:focus-visible .report-card-icon svg,
    html[data-theme="dark"] .report-card:focus-visible .report-card-icon svg * {
        color: #facc15 !important;
        stroke: #facc15 !important;
    }

    /* Match Developer Tools card treatment in dark mode */
    html[data-theme="dark"] .report-card,
    html[data-theme="dark"] .report-card.report-card-primary,
    html[data-theme="dark"] .report-card.report-card-audit,
    html[data-theme="dark"] .report-grid .report-card,
    html[data-theme="dark"] .report-grid a.report-card {
        background: transparent !important;
        background-image: none !important;
        border-color: rgba(250, 204, 21, 0.18) !important;
        box-shadow: none !important;
        color: #ffffff !important;
    }

    html[data-theme="dark"] .report-card::before,
    html[data-theme="dark"] .report-card.report-card-primary::before,
    html[data-theme="dark"] .report-card.report-card-audit::before {
        background: transparent !important;
    }

    html[data-theme="dark"] .report-card .report-main-title,
    html[data-theme="dark"] .report-card .report-card-copy,
    html[data-theme="dark"] .report-card .report-card-footer,
    html[data-theme="dark"] .report-card .report-badge,
    html[data-theme="dark"] .report-card .report-label {
        color: #ffffff !important;
    }

    html[data-theme="dark"] .report-card:hover,
    html[data-theme="dark"] .report-card:focus-visible,
    html[data-theme="dark"] .report-card.report-card-primary:hover,
    html[data-theme="dark"] .report-card.report-card-primary:focus-visible,
    html[data-theme="dark"] .report-card.report-card-audit:hover,
    html[data-theme="dark"] .report-card.report-card-audit:focus-visible {
        background: #facc15 !important;
        background-image: none !important;
        border-color: #facc15 !important;
        color: #70131B !important;
    }

    html[data-theme="dark"] .report-card:hover .report-main-title,
    html[data-theme="dark"] .report-card:focus-visible .report-main-title,
    html[data-theme="dark"] .report-card:hover .report-card-copy,
    html[data-theme="dark"] .report-card:focus-visible .report-card-copy,
    html[data-theme="dark"] .report-card:hover .report-card-footer,
    html[data-theme="dark"] .report-card:focus-visible .report-card-footer,
    html[data-theme="dark"] .report-card:hover .report-badge,
    html[data-theme="dark"] .report-card:focus-visible .report-badge,
    html[data-theme="dark"] .report-card:hover .report-label,
    html[data-theme="dark"] .report-card:focus-visible .report-label {
        color: #70131B !important;
    }

    html[data-theme="dark"] .report-card::after,
    html[data-theme="dark"] .report-card.report-card-primary::after,
    html[data-theme="dark"] .report-card.report-card-audit::after {
        background: linear-gradient(105deg, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 0.12) 42%, rgba(255, 255, 255, 0.44) 50%, rgba(255, 255, 255, 0.12) 58%, rgba(255, 255, 255, 0) 100%) !important;
    }

    /* Final card surface pass: subtle border only, no thick yellow bottom edge */
    .reports-frame .report-grid > .report-card,
    .reports-frame .report-grid > a.report-card,
    .reports-frame .report-grid > .report-card.report-card-primary,
    .reports-frame .report-grid > .report-card.report-card-audit {
        background: #70131B !important;
        background-image: none !important;
        border: 1px solid rgba(250, 204, 21, 0.18) !important;
        box-shadow: 0 10px 24px rgba(112, 19, 27, 0.18) !important;
        color: #ffffff !important;
    }

    html[data-theme="dark"] .reports-frame .report-grid > .report-card,
    html[data-theme="dark"] .reports-frame .report-grid > a.report-card,
    html[data-theme="dark"] .reports-frame .report-grid > .report-card.report-card-primary,
    html[data-theme="dark"] .reports-frame .report-grid > .report-card.report-card-audit {
        background: transparent !important;
        background-image: none !important;
        border: 1px solid rgba(250, 204, 21, 0.18) !important;
        box-shadow: 0 18px 34px rgba(0, 0, 0, 0.34), 0 4px 12px rgba(0, 0, 0, 0.22) !important;
        color: #ffffff !important;
    }

    .reports-frame .report-grid > .report-card:hover,
    .reports-frame .report-grid > .report-card:focus-visible,
    .reports-frame .report-grid > a.report-card:hover,
    .reports-frame .report-grid > a.report-card:focus-visible,
    html[data-theme="dark"] .reports-frame .report-grid > .report-card:hover,
    html[data-theme="dark"] .reports-frame .report-grid > .report-card:focus-visible,
    html[data-theme="dark"] .reports-frame .report-grid > a.report-card:hover,
    html[data-theme="dark"] .reports-frame .report-grid > a.report-card:focus-visible {
        background: #facc15 !important;
        background-image: none !important;
        border-color: #facc15 !important;
        box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.12), 0 20px 30px rgba(139, 0, 0, 0.22) !important;
        color: #70131B !important;
    }

    html[data-theme="dark"] .reports-frame {
        background: transparent !important;
        background-image: none !important;
        border-color: rgba(250, 204, 21, 0.20) !important;
        box-shadow: 0 18px 34px rgba(0, 0, 0, 0.18) !important;
    }

    html[data-theme="dark"] .reports-frame::before {
        background: #facc15 !important;
    }

    /* Final light-mode surface pass */
    html:not([data-theme="dark"]) .reports-frame {
        border: 1px solid rgba(250, 204, 21, 0.20) !important;
        box-shadow: 0 18px 34px rgba(112, 19, 27, 0.08) !important;
    }

    html:not([data-theme="dark"]) .reports-frame .report-grid > .report-card,
    html:not([data-theme="dark"]) .reports-frame .report-grid > a.report-card,
    html:not([data-theme="dark"]) .reports-frame .report-grid > .report-card.report-card-primary,
    html:not([data-theme="dark"]) .reports-frame .report-grid > .report-card.report-card-audit {
        border: 1px solid rgba(250, 204, 21, 0.22) !important;
        box-shadow: 0 14px 28px rgba(112, 19, 27, 0.12), 0 4px 12px rgba(15, 23, 42, 0.06) !important;
    }

</style>
@endpush

@section('content')
@php
    $role = \App\Models\User::normalizeRole(optional(auth()->user())->user_role ?? '');
    $dashboardUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/dashboard') : url('/admin/dashboard');
    $marUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports/mar') : url('/admin/reports/mar');
    $inventorySummaryUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports/inventory-summary') : url('/admin/reports/inventory-summary');
    $digitalLogbookUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports/digital-logbook') : url('/admin/reports/digital-logbook');
    $appointmentStatisticsUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports/appointment-statistics') : url('/admin/reports/appointment-statistics');
    $healthFormsUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports/health-forms') : url('/admin/reports/health-forms');
    $feedbacksUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports/feedbacks') : url('/admin/reports/feedbacks');
    $exportHubUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports/export-hub') : url('/admin/reports/export-hub');
    $canAccessReport = fn (string $permission): bool => optional(auth()->user())->canAccessPermission($permission) ?? false;
@endphp
<div class="dashboard-container">
    <div class="reports-frame">
        {{-- Reports Header --}}
        <div class="reports-header">
            <h1 class="reports-header-title">Reports</h1>
            <p class="reports-header-description">Access reports on clinic operations, medical records, inventory, and patient statistics.</p>
        </div>

        <div class="report-grid">
        
        @if($canAccessReport('reports.mar'))
        <a href="{{ $marUrl }}" class="report-card report-card-primary">
            <span class="report-card-chip" aria-hidden="true"><x-outline-icon name="chevron-right" /></span>
            <span class="report-card-icon"><x-outline-icon name="clipboard-document-list" /></span>
            <div>
                <div class="report-main-title">MAR Reports</div>
                <p class="report-card-copy">Review personnel medical accomplishment records.</p>
            </div>
        </a>
        @endif

        @if($canAccessReport('reports.inventory_summary'))
        <a href="{{ $inventorySummaryUrl }}" class="report-card report-card-primary">
            <span class="report-card-chip" aria-hidden="true"><x-outline-icon name="chevron-right" /></span>
            <span class="report-card-icon"><x-outline-icon name="cube" /></span>
            <div>
                <div class="report-main-title">Inventory Summary</div>
                <p class="report-card-copy">Track stock movement, supplies, medicine records, and inventory levels.</p>
            </div>
        </a>
        @endif

        @if($canAccessReport('reports.health_forms'))
        <a href="{{ $healthFormsUrl }}" class="report-card">
            <span class="report-card-chip" aria-hidden="true"><x-outline-icon name="chevron-right" /></span>
            <span class="report-card-icon"><x-outline-icon name="document-text" /></span>
            <div>
                <div class="report-main-title">Health Forms</div>
                <p class="report-card-copy">View issued health forms summarized by course and selected date range.</p>
            </div>
        </a>
        @endif

        @if($canAccessReport('reports.appointment_statistics'))
        <a href="{{ $appointmentStatisticsUrl }}" class="report-card report-card-primary">
            <span class="report-card-chip" aria-hidden="true"><x-outline-icon name="chevron-right" /></span>
            <span class="report-card-icon"><x-outline-icon name="calendar-days" /></span>
            <div>
                <div class="report-main-title">Appointment Statistics</div>
                <p class="report-card-copy">Analyze appointment trends, service demand, schedules, and clinic flow.</p>
            </div>
        </a>
        @endif

        @if($canAccessReport('reports.digital_logbook'))
        <a href="{{ $digitalLogbookUrl }}" class="report-card report-card-primary">
            <span class="report-card-chip" aria-hidden="true"><x-outline-icon name="chevron-right" /></span>
            <span class="report-card-icon"><x-outline-icon name="clipboard-document-list" /></span>
            <div>
                <div class="report-main-title">Digital Logbook</div>
                <p class="report-card-copy">Monitor clinic treatments, visit logs, and submitted form activity.</p>
            </div>
        </a>
        @endif

        @if($canAccessReport('reports.feedbacks'))
        <a href="{{ $feedbacksUrl }}" class="report-card">
            <span class="report-card-chip" aria-hidden="true"><x-outline-icon name="chevron-right" /></span>
            <span class="report-card-icon"><x-outline-icon name="megaphone" /></span>
            <div>
                <div class="report-main-title">Feedbacks</div>
                <p class="report-card-copy">Review patient feedback, ratings, comments, and clinic experience notes to improve care.</p>
            </div>
        </a>
        @endif

        @if($canAccessReport('reports.export_reports'))
        <a href="{{ $exportHubUrl }}" class="report-card">
            <span class="report-card-chip" aria-hidden="true"><x-outline-icon name="chevron-right" /></span>
            <span class="report-card-icon"><x-outline-icon name="arrow-down-tray" /></span>
            <div>
                <div class="report-main-title">Export Reports</div>
                <p class="report-card-copy">Open the export hub for printable and downloadable clinic reports.</p>
            </div>
        </a>
        @endif

        @if($role === \App\Models\User::ROLE_SUPERADMIN)
        <a href="{{ route('admin.logs') }}" class="report-card report-card-audit">
            <span class="report-card-chip" aria-hidden="true"><x-outline-icon name="chevron-right" /></span>
            <span class="report-card-icon"><x-outline-icon name="clock" /></span>
            <div>
                <div class="report-main-title">Audit Trail</div>
                <p class="report-card-copy">Inspect user activity logs, system actions, and administrative changes.</p>
            </div>
        </a>
        @endif

        </div>
    </div>

    

</div>
@endsection
