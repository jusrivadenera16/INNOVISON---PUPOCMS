@extends('layouts.admin')

@section('content')
@php
    $role = \App\Models\User::normalizeRole(optional(auth()->user())->user_role ?? '');
    $printReportUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports/print-reports') : url('/admin/reports/print-reports');
    $reportsHomeUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports') : url('/admin/reports');
    $healthFormsExportRouteName = request()->routeIs('assistant.*') ? 'assistant.reports.health-forms.export' : 'reports.health-forms.export';
    $healthFormCourses = $healthFormCourses ?? collect();
@endphp
<style>
    /* Main Container */
    .export-hub-container {
        padding: 14px;
        background: transparent;
    }

    .hub-header {
        margin-bottom: 30px;
    }

    .hub-header h2 {
        color: #4b0f17;
        margin: 0;
    }

    .hub-header p {
        color: #64748b;
    }

    /* Grid Layout */
    .hub-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }

    /* Card Styling */
    .report-card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .report-card h3 {
        margin-top: 0;
        color: #1e293b;
    }

    .report-card p {
        color: #64748b;
        font-size: 14px;
        margin-bottom: 20px;
    }

    /* Form Elements */
    .form-group {
        margin-bottom: 15px;
    }

    .inventory-scope-group {
        margin-bottom: 0;
        margin-top: 0;
    }

    .form-group label {
        display: block;
        font-size: 12px;
        font-weight: bold;
        color: #475569;
        margin-bottom: 5px;
    }

    .input-month {
        width: 100%;
        padding: 8px;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        outline-color: #800000;
    }

    .inventory-scope-wrap {
        position: relative;
    }

    .inventory-scope-select {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .inventory-scope-display {
        width: 100%;
        min-height: 48px;
        padding: 11px 42px 11px 16px;
        border-radius: 12px;
        border: 1px solid var(--admin-primary-btn-border, #8b0000);
        background: #ffffff;
        color: #70131B;
        font-weight: 800;
        text-align: center;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        box-shadow:
            0 0 0 3px rgba(112, 19, 27, 0.12),
            0 10px 22px rgba(112, 19, 27, 0.20);
        transition: color .08s linear, transform .18s ease, box-shadow .18s ease, border-color .18s ease, background .18s ease;
        z-index: 0;
    }

    .inventory-scope-display::before {
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

    .inventory-scope-display::after {
        content: "";
        position: absolute;
        right: 18px;
        top: 50%;
        width: 8px;
        height: 8px;
        border-right: 2px solid currentColor;
        border-bottom: 2px solid currentColor;
        transform: translateY(-50%) rotate(-45deg);
        transition: transform .2s ease, border-color .2s ease;
    }

    .inventory-scope-display:hover,
    .inventory-scope-display:focus,
    .inventory-scope-display.is-open {
        outline: none;
        background: #facc15 !important;
        border-color: #facc15;
        color: #111827;
        transform: translateY(-1px);
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.18),
            0 14px 24px rgba(112, 19, 27, 0.16);
    }

    .inventory-scope-display.is-open::after {
        transform: translateY(-50%) rotate(135deg);
    }

    .inventory-scope-display:hover::before,
    .inventory-scope-display:focus::before,
    .inventory-scope-display.is-open::before {
        transform: translateX(135%);
    }

    .inventory-scope-menu {
        position: absolute;
        top: 0;
        left: calc(100% + 12px);
        right: auto;
        width: min(260px, 80vw);
        display: none;
        padding: 8px;
        border-radius: 16px;
        border: 1px solid rgba(112, 19, 27, 0.14);
        background: #ffffff;
        box-shadow: 0 18px 34px rgba(15, 23, 42, 0.16);
        z-index: 20;
    }

    .inventory-scope-wrap.is-open .inventory-scope-menu {
        display: grid;
        gap: 6px;
    }

    .inventory-scope-option {
        width: 100%;
        min-height: 42px;
        padding: 10px 12px;
        border: 1px solid rgba(112, 19, 27, 0.16);
        border-radius: 12px;
        background: #ffffff;
        color: #111827;
        cursor: pointer;
        font-weight: 800;
        text-align: left;
        box-shadow:
            0 0 0 2px rgba(112, 19, 27, 0.04),
            0 10px 22px rgba(112, 19, 27, 0.14);
        transition: background .18s ease, color .18s ease, transform .18s ease, border-color .18s ease, box-shadow .18s ease;
    }

    .inventory-scope-option:hover {
        border-color: rgba(143, 34, 48, 0.34);
        background: #8b0000;
        color: #facc15;
        transform: translateX(2px);
        box-shadow:
            0 0 0 2px rgba(250, 204, 21, 0.14),
            0 14px 26px rgba(112, 19, 27, 0.22);
    }

    .inventory-scope-option.is-selected {
        background: #facc15;
        border-color: #facc15;
        color: #111827;
        transform: translateX(2px);
        box-shadow:
            0 0 0 2px rgba(250, 204, 21, 0.22),
            0 14px 26px rgba(250, 204, 21, 0.26);
    }

    /* Buttons & Links */
    .btn-generate {
        width: 100%;
        min-height: 60px;
        border: 1px solid #8f2230;
        padding: 12px 16px;
        border-radius: 12px;
        cursor: pointer;
        font-weight: 900;
        color: #70131B;
        text-align: center;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        background: #ffffff;
        box-shadow:
            0 0 0 3px rgba(112, 19, 27, 0.12),
            0 10px 22px rgba(112, 19, 27, 0.20);
        transition: color .08s linear, transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        z-index: 0;
    }

    .btn-generate::after {
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

    .btn-generate:hover,
    .btn-generate:focus {
        transform: translateY(-1px);
        background: #facc15 !important;
        border-color: #facc15;
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.18),
            0 14px 24px rgba(112, 19, 27, 0.16);
        color: #111827 !important;
        outline: none;
    }

    .btn-generate:hover::after,
    .btn-generate:focus::after {
        transform: translateX(135%);
    }

    /* Dynamic Border & Button Colors */
    .border-mar { border-top: 5px solid #800000; }
    .bg-mar { background: #ffffff; color: #70131B; }

    .border-inventory { border-top: 5px solid #800000; }
    .bg-inventory { background: #ffffff; color: #70131B; }

    .border-appointment { border-top: 5px solid #800000; }
    .bg-appointment { background: #ffffff; color: #70131B; }

    .border-audit { border-top: 5px solid #800000; }
    .bg-audit {
        background: #ffffff !important;
        border-color: #8f2230 !important;
        color: #70131B !important;
    }

    .bg-audit:hover {
        background: #facc15 !important;
        border-color: #facc15 !important;
        color: #111827 !important;
        box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.18), 0 14px 24px rgba(112, 19, 27, 0.16) !important;
    }

    @media (max-width: 900px) {
        .inventory-scope-menu {
            top: calc(100% + 8px);
            left: 0;
            right: 0;
            width: auto;
        }
    }

    /* Date Range Modal */
    .date-range-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .date-range-modal.is-open {
        display: flex;
    }

    .date-range-modal-content {
        background: white;
        border-radius: 16px;
        padding: 32px;
        max-width: 450px;
        width: 90%;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        animation: slideUp 0.3s ease;
    }

    @keyframes slideUp {
        from {
            transform: translateY(20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .date-range-modal-header {
        margin-bottom: 24px;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 16px;
    }

    .date-range-modal-header h3 {
        margin: 0;
        color: #111827;
        font-size: 20px;
        font-weight: 800;
    }

    .date-range-modal-body {
        margin-bottom: 24px;
    }

    .date-range-field {
        margin-bottom: 18px;
    }

    .date-range-field label {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .date-range-field input {
        width: 100%;
        padding: 12px 14px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 14px;
        color: #111827;
        transition: border-color 0.2s;
    }

    .date-range-field input:focus {
        outline: none;
        border-color: #8b0000;
        background: rgba(139, 0, 0, 0.02);
    }

    .date-range-modal-actions {
        display: flex;
        gap: 12px;
    }

    .date-range-modal-btn {
        flex: 1;
        padding: 12px 16px;
        border: none;
        border-radius: 999px;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 14px;
    }

    .date-range-modal-btn-cancel {
        background: #e2e8f0;
        color: #475569;
    }

    .date-range-modal-btn-cancel:hover {
        background: #cbd5e1;
    }

    .date-range-modal-btn-select {
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: white;
        box-shadow: 0 10px 22px rgba(112, 19, 27, 0.20);
    }

    .date-range-modal-btn-select:hover {
        background: #facc15;
        color: #111827;
        box-shadow: 0 14px 24px rgba(112, 19, 27, 0.16);
    }

    .health-filter-grid {
        display: grid;
        gap: 16px;
    }

    .health-filter-field label {
        display: block;
        margin-bottom: 8px;
        color: #475569;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .health-filter-field input,
    .health-filter-field select {
        width: 100%;
        min-height: 46px;
        padding: 10px 13px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        background: #ffffff;
        color: #111827;
        font-size: 14px;
        font-weight: 700;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    .health-filter-field input:focus,
    .health-filter-field select:focus {
        outline: none;
        border-color: #8b0000;
        box-shadow: 0 0 0 3px rgba(112, 19, 27, 0.10);
    }

    .health-status-select {
        cursor: pointer;
    }

    .health-filter-modal-content {
        width: min(980px, calc(100vw - 28px));
        max-width: 980px;
        padding: 0;
        overflow: hidden;
        border-radius: 18px;
        background: #ffffff;
        border: 1px solid rgba(112, 19, 27, 0.18);
        box-shadow: 0 28px 70px rgba(15, 23, 42, 0.24);
    }

    .health-filter-modal-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 24px;
        background: #b91c1c;
        color: #ffffff;
    }

    .health-filter-modal-title-wrap {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
    }

    .health-filter-modal-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.22);
        flex: 0 0 auto;
    }

    .health-filter-modal-icon svg {
        width: 26px;
        height: 26px;
    }

    .health-filter-modal-head h3 {
        margin: 0;
        color: #ffffff;
        font-size: 22px;
        font-weight: 950;
        line-height: 1.12;
    }

    .health-filter-modal-head p {
        margin: 4px 0 0;
        color: rgba(255,255,255,.82);
        font-size: 13px;
        font-weight: 700;
        line-height: 1.45;
    }

    .health-filter-modal-close {
        width: 42px;
        height: 42px;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,.28);
        background: rgba(255,255,255,.14);
        color: #ffffff;
        display: grid;
        place-items: center;
        cursor: pointer;
        transition: background .18s ease, transform .18s ease;
    }

    .health-filter-modal-close:hover {
        background: rgba(255,255,255,.26);
        transform: translateY(-1px);
    }

    .health-filter-modal-close svg {
        width: 18px;
        height: 18px;
    }

    .health-filter-modal-body {
        padding: 22px 24px;
        background: #ffffff;
    }

    .health-filter-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .health-filter-field.is-full {
        grid-column: 1 / -1;
    }

    .health-filter-chip-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .health-filter-chip {
        position: relative;
        display: flex;
        align-items: center;
        gap: 9px;
        min-height: 44px;
        padding: 10px 12px;
        border-radius: 12px;
        border: 1px solid rgba(112, 19, 27, 0.16);
        background: #ffffff;
        color: #111827;
        font-size: 13px;
        font-weight: 850;
        cursor: pointer;
        transition: background .18s ease, color .18s ease, border-color .18s ease, transform .18s ease;
    }

    .health-filter-chip input {
        width: 16px;
        height: 16px;
        accent-color: #8f1727;
    }

    .health-filter-chip:has(input:checked) {
        background: #70131B;
        border-color: #70131B;
        color: #ffffff;
        transform: translateY(-1px);
    }

    .health-filter-modal-actions {
        display: flex;
        gap: 12px;
        padding: 16px 24px 22px;
        border-top: 1px solid #fee2e2;
        background: #ffffff;
    }

    .border-health-forms { border-top: 5px solid #800000; }

    .btn-select-month {
        width: 100%;
        min-height: 60px;
        border: 1px solid #8f2230;
        padding: 12px 16px;
        border-radius: 12px;
        cursor: pointer;
        font-weight: 900;
        color: #70131B;
        text-align: center;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(112, 19, 27, 0.12), 0 10px 22px rgba(112, 19, 27, 0.20);
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 12px;
        position: relative;
        overflow: hidden;
    }

    .btn-select-month::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(120deg, rgba(255, 248, 196, 0) 0%, rgba(255, 239, 181, 0.14) 22%, rgba(255, 239, 181, 0.52) 48%, rgba(255, 239, 181, 0.14) 72%, rgba(255, 248, 196, 0) 100%);
        transform: translateX(-135%);
        transition: transform 1.5s ease;
        z-index: -1;
    }

    .btn-select-month:hover {
        transform: translateY(-1px);
        background: #facc15 !important;
        border-color: #facc15;
        color: #111827 !important;
        box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.18), 0 14px 24px rgba(112, 19, 27, 0.16);
    }

    .btn-select-month:hover::before {
        transform: translateX(135%);
    }

    .selected-date-range {
        font-size: 12px;
        color: #64748b;
        margin-top: 6px;
        font-weight: 500;
    }

    .export-hub-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }

    .export-hub-frame {
        position: relative;
        overflow: visible;
        border-radius: 22px;
        border: 1px solid rgba(112, 19, 27, 0.10);
        padding: 24px;
        background: linear-gradient(180deg, rgba(255,255,255,0.92), rgba(250,244,246,0.96));
        box-shadow: 0 18px 34px rgba(112, 19, 27, 0.08);
    }

    .export-hub-frame::before {
        content: "";
        position: absolute;
        top: 0;
        left: 14px;
        right: 14px;
        height: 5px;
        background: #70131B;
        border-radius: 999px;
        pointer-events: none;
    }

    .export-hub-container .hub-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 24px;
        padding-bottom: 22px;
        border-bottom: 1px solid #e2e8f0;
    }

    .export-hub-container .hub-header h2 {
        margin: 0 0 10px;
        color: #111827;
        font-size: 30px;
        font-weight: 950;
        line-height: 1.1;
    }

    .export-hub-container .hub-header p {
        margin: 0;
        max-width: 680px;
        color: #64748b;
        font-size: 15px;
        font-weight: 600;
        line-height: 1.5;
    }

    .hub-back-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        padding: 0 16px;
        border-radius: 999px;
        border: 1px solid rgba(112, 19, 27, 0.22);
        background: #ffffff;
        color: #70131B;
        font-size: 13px;
        font-weight: 900;
        text-decoration: none;
        white-space: nowrap;
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
        transition: background .18s ease, color .18s ease, border-color .18s ease, transform .18s ease;
    }

    .hub-back-link:hover,
    .hub-back-link:focus {
        background: #facc15;
        border-color: #facc15;
        color: #111827;
        transform: translateY(-1px);
        outline: none;
    }

    .export-hub-container .hub-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .export-hub-container .report-card {
        position: relative;
        overflow: visible;
        min-height: 260px;
        padding: 18px;
        display: flex;
        flex-direction: column;
        border-radius: 16px;
        border: 1px solid rgba(112, 19, 27, 0.14);
        background: #ffffff;
        box-shadow:
            0 0 0 1px rgba(112, 19, 27, 0.04),
            0 16px 32px rgba(15, 23, 42, 0.08);
    }

    .export-hub-container .report-card > div:first-child {
        min-height: 148px;
    }

    .export-hub-container .report-card form {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: auto;
    }

    .export-hub-container .report-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 18px;
        right: 18px;
        height: 5px;
        border-radius: 999px;
        background: #70131B;
    }

    .export-hub-container .report-card h3 {
        margin: 8px 0 8px;
        color: #111827;
        font-size: 17px;
        font-weight: 900;
        line-height: 1.2;
    }

    .export-hub-container .report-card p {
        margin: 0 0 18px;
        color: #64748b;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.55;
    }

    .export-card-kicker {
        margin: 0 0 10px;
        color: #70131B;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .export-hub-container .form-group {
        margin: 0;
    }

    .export-hub-container .btn-select-month,
    .export-hub-container .inventory-scope-display,
    .export-hub-container .btn-generate {
        width: 100%;
        border-radius: 12px;
        min-height: 60px;
        padding: 12px 16px;
        font-size: 12px;
        font-weight: 900;
        line-height: 1.25;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    .export-hub-container .btn-select-month {
        flex-direction: column;
        gap: 2px;
        background: #ffffff;
        color: #70131B;
        border-color: #8f2230;
        box-shadow: 0 10px 22px rgba(112, 19, 27, 0.18);
    }

    .export-hub-container .selected-date-range {
        margin: 0;
        color: inherit;
        font-size: 12px;
        font-weight: 900;
    }

    .export-hub-container .btn-generate,
    .export-hub-container .inventory-scope-display {
        background: #ffffff;
        color: #70131B;
        border-color: #8f2230;
        box-shadow: 0 10px 22px rgba(112, 19, 27, 0.18);
    }

    .export-hub-container .inventory-scope-display {
        padding-right: 42px;
    }

    .export-hub-container .btn-generate:hover,
    .export-hub-container .btn-generate:focus,
    .export-hub-container .inventory-scope-display:hover,
    .export-hub-container .inventory-scope-display:focus,
    .export-hub-container .inventory-scope-display.is-open,
    .export-hub-container .btn-select-month:hover,
    .export-hub-container .btn-select-month:focus {
        background: #facc15 !important;
        border-color: #facc15 !important;
        color: #111827 !important;
    }

    .export-hub-container .bg-audit,
    .export-hub-container .bg-audit:not(:hover):not(:focus) {
        background: #ffffff !important;
        border-color: #8f2230 !important;
        color: #70131B !important;
    }

    .export-hub-container .bg-audit:hover,
    .export-hub-container .bg-audit:focus {
        background: #facc15 !important;
        border-color: #facc15 !important;
        color: #111827 !important;
    }

    .date-range-modal {
        backdrop-filter: blur(4px);
    }

    .date-range-modal-content {
        border: 1px solid rgba(112, 19, 27, 0.12);
        box-shadow: 0 26px 70px rgba(15, 23, 42, 0.24);
    }

    html[data-theme="dark"] .export-hub-frame,
    html[data-theme="dark"] .export-hub-container .report-card,
    html[data-theme="dark"] .date-range-modal-content {
        background: rgba(15, 23, 42, 0.92);
        border-color: rgba(250, 204, 21, 0.16);
    }

    html[data-theme="dark"] .health-filter-modal-content,
    html[data-theme="dark"] .health-filter-modal-body,
    html[data-theme="dark"] .health-filter-modal-actions {
        background: rgba(15, 23, 42, 0.98);
        border-color: rgba(250, 204, 21, 0.18);
    }

    html[data-theme="dark"] .health-filter-chip {
        background: rgba(17, 24, 39, 0.96);
        color: #f8fafc;
        border-color: rgba(250, 204, 21, 0.18);
    }

    html[data-theme="dark"] .health-filter-chip:has(input:checked) {
        background: #70131B;
        border-color: rgba(250, 204, 21, 0.62);
    }

    html[data-theme="dark"] .export-hub-container .hub-header {
        border-bottom-color: rgba(250, 204, 21, 0.14);
    }

    html[data-theme="dark"] .export-hub-container .hub-header h2,
    html[data-theme="dark"] .export-hub-container .report-card h3,
    html[data-theme="dark"] .date-range-modal-header h3 {
        color: #f8fafc;
    }

    html[data-theme="dark"] .export-hub-container .hub-header p,
    html[data-theme="dark"] .export-hub-container .report-card p {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .export-hub-container .btn-select-month,
    html[data-theme="dark"] .date-range-field input {
        background: rgba(18, 18, 18, 0.55);
        color: #f8fafc;
        border-color: rgba(255, 255, 255, 0.08);
    }

    @media (max-width: 1200px) {
        .export-hub-container .hub-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 900px) {
        .export-hub-container .hub-header {
            flex-direction: column;
        }

        .hub-back-link,
        .export-hub-container .hub-grid {
            width: 100%;
        }

        .export-hub-container .hub-grid {
            grid-template-columns: 1fr;
        }

        .health-filter-grid,
        .health-filter-chip-grid {
            grid-template-columns: 1fr;
        }

        .health-filter-modal-actions {
            flex-direction: column;
        }
    }

    /* Compact Health Forms export modal */
    #healthFormsFilterModal.date-range-modal {
        align-items: flex-start;
        justify-content: center;
        padding: clamp(10px, 2vh, 18px) 16px;
        overflow-y: auto;
    }
    #healthFormsFilterModal .health-filter-modal-content {
        width: min(920px, calc(100vw - 32px));
        max-width: 920px;
        max-height: calc(100vh - 24px);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border-radius: 16px;
    }
    #healthFormsFilterModal .health-filter-modal-head {
        flex: 0 0 auto;
        padding: 16px 22px;
        background: #b91c1c;
    }
    #healthFormsFilterModal .health-filter-modal-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
    }
    #healthFormsFilterModal .health-filter-modal-icon svg {
        width: 22px;
        height: 22px;
    }
    #healthFormsFilterModal .health-filter-modal-head h3 {
        font-size: 20px;
    }
    #healthFormsFilterModal .health-filter-modal-head p {
        font-size: 12px;
        line-height: 1.35;
    }
    #healthFormsExportForm {
        min-height: 0;
        display: flex;
        flex-direction: column;
    }
    #healthFormsFilterModal .health-filter-modal-body {
        min-height: 0;
        max-height: none;
        overflow-y: auto;
        padding: 18px 22px;
        margin-bottom: 0;
        gap: 14px;
    }
    #healthFormsFilterModal .health-filter-field input,
    #healthFormsFilterModal .health-filter-field select {
        min-height: 44px;
        border-width: 1px;
    }
    #healthFormsFilterModal .health-filter-chip-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }
    #healthFormsFilterModal .health-filter-chip {
        min-height: 48px;
        padding: 10px 12px;
        border: 1px solid rgba(112, 19, 27, .18);
        background: #ffffff;
        color: #111827;
        box-shadow: none;
    }
    #healthFormsFilterModal .health-filter-chip:hover,
    #healthFormsFilterModal .health-filter-chip:focus-within {
        background: #fffaf0;
        border-color: #facc15;
        color: #70131B;
        transform: translateY(-1px);
        box-shadow: 0 10px 20px rgba(112, 19, 27, .08);
    }
    #healthFormsFilterModal .health-filter-chip input {
        flex: 0 0 16px;
        margin: 0;
        accent-color: #70131B;
    }
    #healthFormsFilterModal .health-filter-chip:has(input:checked) {
        background: #fff1f2;
        border-color: #70131B;
        color: #70131B;
        transform: none;
        box-shadow: inset 0 0 0 1px rgba(112, 19, 27, .16);
    }
    #healthFormsFilterModal .health-filter-chip:has(input:checked):hover,
    #healthFormsFilterModal .health-filter-chip:has(input:checked):focus-within {
        background: #facc15;
        border-color: #facc15;
        color: #70131B;
        transform: translateY(-1px);
    }
    #healthFormsFilterModal .health-filter-modal-actions {
        flex: 0 0 auto;
        margin: 0;
        padding: 14px 22px 16px;
        background: #ffffff;
    }
    html[data-theme="dark"] #healthFormsFilterModal .health-filter-modal-content,
    html[data-theme="dark"] #healthFormsFilterModal .health-filter-modal-body,
    html[data-theme="dark"] #healthFormsFilterModal .health-filter-modal-actions {
        background: #111827;
    }
    html[data-theme="dark"] #healthFormsFilterModal .health-filter-chip {
        background: rgba(15, 23, 42, .96);
        border-color: rgba(250, 204, 21, .18);
        color: #ffffff;
    }
    html[data-theme="dark"] #healthFormsFilterModal .health-filter-chip:hover,
    html[data-theme="dark"] #healthFormsFilterModal .health-filter-chip:focus-within,
    html[data-theme="dark"] #healthFormsFilterModal .health-filter-chip:has(input:checked) {
        background: rgba(250, 204, 21, .14);
        border-color: #facc15;
        color: #ffffff;
    }
    #healthFormsFilterModal .health-bmi-dropdown-wrap {
        position: relative;
    }
    #healthFormsFilterModal .health-bmi-display {
        width: 100%;
        min-height: 48px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 12px 48px 12px 16px;
        border-radius: 16px;
        border: 1px solid #8f2230;
        background: #ffffff;
        color: #111827;
        font-size: 14px;
        font-weight: 850;
        text-align: left;
        cursor: pointer;
        box-shadow: 0 10px 22px rgba(112, 19, 27, .08);
        position: relative;
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }
    #healthFormsFilterModal .health-bmi-display::after {
        content: "";
        position: absolute;
        right: 18px;
        top: 50%;
        width: 11px;
        height: 11px;
        border-right: 2px solid #8f2230;
        border-bottom: 2px solid #8f2230;
        transform: translateY(-70%) rotate(45deg);
        transition: transform .18s ease;
    }
    #healthFormsFilterModal .health-bmi-dropdown-wrap.is-open .health-bmi-display::after {
        transform: translateY(-25%) rotate(225deg);
    }
    #healthFormsFilterModal .health-bmi-display:hover,
    #healthFormsFilterModal .health-bmi-display:focus-visible,
    #healthFormsFilterModal .health-bmi-dropdown-wrap.is-open .health-bmi-display {
        border-color: #70131B;
        box-shadow: 0 0 0 3px rgba(112, 19, 27, .08), 0 12px 24px rgba(112, 19, 27, .12);
        outline: none;
        transform: translateY(-1px);
    }
    #healthFormsFilterModal .health-bmi-menu {
        position: absolute;
        top: calc(100% + 10px);
        left: 0;
        right: 0;
        z-index: 25;
        display: none;
        padding: 14px;
        border-radius: 18px;
        border: 1px solid rgba(112, 19, 27, .14);
        background: rgba(255, 255, 255, .98);
        box-shadow: 0 18px 34px rgba(15, 23, 42, .16);
    }
    #healthFormsFilterModal .health-bmi-dropdown-wrap.is-open .health-bmi-menu {
        display: grid;
        gap: 10px;
    }
    #healthFormsFilterModal .health-bmi-option {
        width: 100%;
        min-height: 42px;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 11px 14px;
        border-radius: 999px;
        border: 1px solid rgba(148, 163, 184, .22);
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        color: #111827;
        font-size: 13px;
        font-weight: 850;
        cursor: pointer;
        transition: transform .18s ease, border-color .18s ease, background .18s ease, color .18s ease;
    }
    #healthFormsFilterModal .health-bmi-option input {
        width: 16px;
        height: 16px;
        margin: 0;
        accent-color: #70131B;
    }
    #healthFormsFilterModal .health-bmi-option:hover,
    #healthFormsFilterModal .health-bmi-option:focus-within {
        background: linear-gradient(135deg, #8B0000, #70131B);
        border-color: #8B0000;
        color: #ffffff;
        transform: translateY(-1px);
    }
    #healthFormsFilterModal .health-bmi-option:has(input:checked) {
        background: linear-gradient(135deg, #8B0000, #70131B);
        border-color: #8B0000;
        color: #facc15;
        box-shadow: 0 12px 20px rgba(139, 0, 0, .16);
    }
    html[data-theme="dark"] #healthFormsFilterModal .health-bmi-display {
        background: rgba(15, 23, 42, .96);
        border-color: rgba(250, 204, 21, .26);
        color: #ffffff;
    }
    html[data-theme="dark"] #healthFormsFilterModal .health-bmi-display::after {
        border-color: #facc15;
    }
    html[data-theme="dark"] #healthFormsFilterModal .health-bmi-menu {
        background: rgba(15, 23, 42, .98);
        border-color: rgba(250, 204, 21, .18);
    }
    html[data-theme="dark"] #healthFormsFilterModal .health-bmi-option {
        background: rgba(30, 41, 59, .94);
        border-color: rgba(148, 163, 184, .18);
        color: #ffffff;
    }
    html[data-theme="dark"] #healthFormsFilterModal .health-bmi-option:hover,
    html[data-theme="dark"] #healthFormsFilterModal .health-bmi-option:focus-within {
        background: linear-gradient(135deg, #8B0000, #70131B);
        color: #ffffff;
    }
    html[data-theme="dark"] #healthFormsFilterModal .health-bmi-option:has(input:checked) {
        background: linear-gradient(135deg, #8B0000, #70131B);
        border-color: #facc15;
        color: #facc15;
    }
    @media (max-width: 760px) {
        #healthFormsFilterModal .health-filter-chip-grid {
            grid-template-columns: 1fr;
        }
        #healthFormsFilterModal .health-filter-modal-head {
            align-items: flex-start;
        }
    }
</style>

<div class="export-hub-container">
    <div class="export-hub-frame">
        <div class="hub-header">
            <div>
                <h2>Export Reports Hub</h2>
                <p>Select a report type, choose the date range when needed, then generate a printable document.</p>
            </div>
            <a href="{{ $reportsHomeUrl }}" class="hub-back-link">Back to Reports</a>
        </div>

        <div class="hub-grid">
        
        <div class="report-card border-mar">
            <div>
                <div class="export-card-kicker">Monthly Report</div>
                <h3>MAR Report</h3>
                <p>Monthly Accomplishment Report showing patient counts.</p>
            </div>
            <form action="{{ $printReportUrl }}" method="GET" target="_blank" class="mar-form">
                <input type="hidden" name="type" value="mar">
                <input type="hidden" name="output" value="pdf">
                <input type="hidden" name="month" class="mar-month" value="{{ date('Y-m') }}">
                <input type="hidden" name="month_from" class="mar-month-from" value="{{ date('Y-m') }}">
                <input type="hidden" name="month_to" class="mar-month-to" value="{{ date('Y-m') }}">
                <input type="hidden" name="date_from" class="mar-date-from" value="{{ date('Y-m-d') }}">
                <input type="hidden" name="date_to" class="mar-date-to" value="{{ date('Y-m-d') }}">
                <div class="form-group">
                    <button type="button" class="btn-select-month mar-date-btn" onclick="openDateRangeModal('mar')">
                        <span>Select Date Range</span>
                        <span class="selected-date-range mar-date-display">{{ date('M d, Y') }}</span>
                    </button>
                </div>
                <button type="submit" class="btn-generate bg-mar">
                    Generate MAR Report
                </button>
            </form>
        </div>



        
        <div class="report-card border-inventory">
            <div>
                <div class="export-card-kicker">Stocks & Supplies</div>
                <h3>Inventory Stock</h3>
                <p>View unit-based inventory movement with starting stock, consumed quantity, and current balance for the selected month.</p>
            </div>
            <form action="{{ $printReportUrl }}" method="GET" target="_blank" class="inventory-form">
                <input type="hidden" name="type" value="inventory">
                <input type="hidden" name="output" value="pdf">
                <input type="hidden" name="month" class="inventory-month" value="{{ date('Y-m') }}">
                <input type="hidden" name="month_from" class="inventory-month-from" value="{{ date('Y-m') }}">
                <input type="hidden" name="month_to" class="inventory-month-to" value="{{ date('Y-m') }}">
                <input type="hidden" name="date_from" class="inventory-date-from" value="{{ date('Y-m-d') }}">
                <input type="hidden" name="date_to" class="inventory-date-to" value="{{ date('Y-m-d') }}">
                <div class="form-group">
                    <button type="button" class="btn-select-month inventory-date-btn" onclick="openDateRangeModal('inventory')">
                        <span>Select Date Range</span>
                        <span class="selected-date-range inventory-date-display">{{ date('M d, Y') }}</span>
                    </button>
                </div>
                <div class="form-group inventory-scope-group">
                    <div class="inventory-scope-wrap" id="inventoryScopeWrap">
                        <select name="inventory_scope" id="inventoryScopeSelect" class="inventory-scope-select">
                            <option value="medicines" selected>Inventory of Medicines</option>
                            <option value="supplies">Inventory of Supplies</option>
                        </select>
                        <button type="button" class="inventory-scope-display" id="inventoryScopeDisplay" aria-haspopup="listbox" aria-expanded="false">
                            Generate Inventory Report
                        </button>
                        <div class="inventory-scope-menu" id="inventoryScopeMenu" role="listbox" aria-label="Inventory report type">
                            <button type="button" class="inventory-scope-option" data-scope-value="medicines">Inventory of Medicines</button>
                            <button type="button" class="inventory-scope-option" data-scope-value="supplies">Inventory of Supplies</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>




        <div class="report-card border-appointment">
            <div>
                <div class="export-card-kicker">Clinic Activity</div>
                <h3>Appointments</h3>
                <p>Summary of student appointments and medical consultations for the selected period.</p>
            </div>
            <form action="{{ $printReportUrl }}" method="GET" target="_blank" class="appointment-form">
                <input type="hidden" name="type" value="appointment">
                <input type="hidden" name="output" value="pdf">
                <input type="hidden" name="month" class="appointment-month" value="{{ date('Y-m') }}">
                <input type="hidden" name="month_from" class="appointment-month-from" value="{{ date('Y-m') }}">
                <input type="hidden" name="month_to" class="appointment-month-to" value="{{ date('Y-m') }}">
                <input type="hidden" name="date_from" class="appointment-date-from" value="{{ date('Y-m-d') }}">
                <input type="hidden" name="date_to" class="appointment-date-to" value="{{ date('Y-m-d') }}">
                <div class="form-group">
                    <button type="button" class="btn-select-month appointment-date-btn" onclick="openDateRangeModal('appointment')">
                        <span>Select Date Range</span>
                        <span class="selected-date-range appointment-date-display">{{ date('M d, Y') }}</span>
                    </button>
                </div>
                <button type="submit" class="btn-generate bg-appointment">
                    Generate Appointment Report
                </button>
            </form>
        </div>

        <div class="report-card border-audit">
            <div>
                <div class="export-card-kicker">System Monitoring</div>
                <h3>Audit Trail</h3>
                <p>System activity log showing all user actions, changes, and administrative operations.</p>
            </div>
            <form action="{{ $printReportUrl }}" method="GET" target="_blank" class="audit-form">
                <input type="hidden" name="type" value="audit">
                <input type="hidden" name="output" value="pdf">
                <input type="hidden" name="month" class="audit-month" value="{{ date('Y-m') }}">
                <input type="hidden" name="month_from" class="audit-month-from" value="{{ date('Y-m') }}">
                <input type="hidden" name="month_to" class="audit-month-to" value="{{ date('Y-m') }}">
                <input type="hidden" name="date_from" class="audit-date-from" value="{{ date('Y-m-d') }}">
                <input type="hidden" name="date_to" class="audit-date-to" value="{{ date('Y-m-d') }}">
                <div class="form-group">
                    <button type="button" class="btn-select-month audit-date-btn" onclick="openDateRangeModal('audit')">
                        <span>Select Date Range</span>
                        <span class="selected-date-range audit-date-display">{{ date('M d, Y') }}</span>
                    </button>
                </div>
                <button type="submit" class="btn-generate bg-audit">
                    Generate Audit Trail
                </button>
            </form>
        </div>

        <div class="report-card border-health-forms">
            <div>
                <div class="export-card-kicker">Medical Clearance</div>
                <h3>Health Forms</h3>
                <p>Export health form records with reference number, course, status, condition, and approval timestamp.</p>
            </div>
            <button type="button" class="btn-select-month" onclick="openHealthFormsFilterModal()">
                <span>Filter</span>
                <span class="selected-date-range" id="healthFormsFilterSummary">{{ now()->startOfMonth()->format('M d, Y') }} to {{ now()->format('M d, Y') }}</span>
            </button>
        </div>

        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const wrap = document.getElementById('inventoryScopeWrap');
    const select = document.getElementById('inventoryScopeSelect');
    const display = document.getElementById('inventoryScopeDisplay');
    const options = Array.from(document.querySelectorAll('.inventory-scope-option'));

    if (!wrap || !select || !display || options.length === 0) {
        return;
    }

    let hasSelectedScope = false;

    const syncDisplay = function() {
        display.textContent = 'Generate Inventory Report';
        options.forEach(function(option) {
            option.classList.toggle('is-selected', hasSelectedScope && option.dataset.scopeValue === select.value);
        });
    };

    const setOpen = function(isOpen) {
        wrap.classList.toggle('is-open', isOpen);
        display.classList.toggle('is-open', isOpen);
        display.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    };

    display.addEventListener('click', function(event) {
        event.preventDefault();
        setOpen(!wrap.classList.contains('is-open'));
    });

    options.forEach(function(option) {
        option.addEventListener('click', function() {
            select.value = option.dataset.scopeValue || 'medicines';
            hasSelectedScope = true;
            syncDisplay();
            setOpen(false);
            if (select.form) {
                select.form.submit();
            }
        });
    });

    document.addEventListener('click', function(event) {
        if (!wrap.contains(event.target)) {
            setOpen(false);
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            setOpen(false);
        }
    });

    syncDisplay();
});

// Date Range Modal Handler
let currentReportType = null;

function openDateRangeModal(reportType) {
    currentReportType = reportType;
    const modal = document.getElementById('dateRangeModal');
    const form = document.querySelector('.' + reportType + '-form');
    const dateFromInput = form.querySelector('.' + reportType + '-date-from');
    const dateToInput = form.querySelector('.' + reportType + '-date-to');

    document.getElementById('dateRangeFrom').value = dateFromInput.value;
    document.getElementById('dateRangeTo').value = dateToInput.value;

    modal.classList.add('is-open');
}

function closeDateRangeModal() {
    const modal = document.getElementById('dateRangeModal');
    modal.classList.remove('is-open');
    currentReportType = null;
}

function openHealthFormsFilterModal() {
    document.getElementById('healthFormsFilterModal')?.classList.add('is-open');
}

function closeHealthFormsFilterModal() {
    document.getElementById('healthFormsFilterModal')?.classList.remove('is-open');
    setHealthBmiDropdownOpen(false);
}

function setHealthBmiDropdownOpen(isOpen) {
    const wrap = document.getElementById('healthBmiDropdownWrap');
    const display = document.getElementById('healthBmiDisplay');
    if (!wrap || !display) return;
    wrap.classList.toggle('is-open', isOpen);
    display.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
}

function syncHealthBmiDisplay() {
    const display = document.getElementById('healthBmiDisplay');
    const form = document.getElementById('healthFormsExportForm');
    if (!display || !form) return;

    const checked = Array.from(form.querySelectorAll('[name="bmi_categories[]"]:checked'));
    if (checked.length === 0) {
        display.textContent = 'All BMI Categories';
        return;
    }
    if (checked.length === 1) {
        display.textContent = checked[0].closest('label')?.textContent?.trim() || '1 BMI Category';
        return;
    }
    display.textContent = checked.length + ' BMI Categories';
}

function updateHealthFormsFilterSummary() {
    const form = document.getElementById('healthFormsExportForm');
    const summary = document.getElementById('healthFormsFilterSummary');
    if (!form || !summary) return;

    const dateFrom = form.querySelector('[name="date_from"]')?.value || '';
    const dateTo = form.querySelector('[name="date_to"]')?.value || '';
    const status = form.querySelector('[name="status"]')?.selectedOptions?.[0]?.textContent?.trim() || 'All Status';

    const formatDate = function(value) {
        if (!value) return '';
        return new Date(value + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
    };

    const dateText = dateFrom && dateTo
        ? (dateFrom === dateTo ? formatDate(dateFrom) : formatDate(dateFrom) + ' to ' + formatDate(dateTo))
        : 'Date range';
    summary.textContent = dateText + ' • ' + status;
}

function updateHealthFormsFilterSummary() {
    const form = document.getElementById('healthFormsExportForm');
    const summary = document.getElementById('healthFormsFilterSummary');
    if (!form || !summary) return;

    const dateFrom = form.querySelector('[name="date_from"]')?.value || '';
    const dateTo = form.querySelector('[name="date_to"]')?.value || '';
    const status = form.querySelector('[name="status"]')?.selectedOptions?.[0]?.textContent?.trim() || 'All Status';
    const bmiCount = form.querySelectorAll('[name="bmi_categories[]"]:checked').length;
    const keyword = form.querySelector('[name="condition_keyword"]')?.value?.trim() || '';

    const formatDate = function(value) {
        if (!value) return '';
        return new Date(value + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
    };

    const dateText = dateFrom && dateTo
        ? (dateFrom === dateTo ? formatDate(dateFrom) : formatDate(dateFrom) + ' to ' + formatDate(dateTo))
        : 'Date range';
    const extras = [];
    if (bmiCount > 0) extras.push(bmiCount + ' BMI');
    if (keyword !== '') extras.push('Condition');
    summary.textContent = [dateText, status].concat(extras).join(' - ');
    syncHealthBmiDisplay();
}

function applyDateRange() {
    if (!currentReportType) return;

    let dateFrom = document.getElementById('dateRangeFrom').value;
    let dateTo = document.getElementById('dateRangeTo').value;

    if (!dateFrom || !dateTo) {
        alert('Please select both From and To dates');
        return;
    }

    if (dateFrom > dateTo) {
        const previousFrom = dateFrom;
        dateFrom = dateTo;
        dateTo = previousFrom;
    }

    const form = document.querySelector('.' + currentReportType + '-form');
    const monthInput = form.querySelector('.' + currentReportType + '-month');
    const monthFromInput = form.querySelector('.' + currentReportType + '-month-from');
    const monthToInput = form.querySelector('.' + currentReportType + '-month-to');
    const dateFromInput = form.querySelector('.' + currentReportType + '-date-from');
    const dateToInput = form.querySelector('.' + currentReportType + '-date-to');
    const dateDisplay = form.querySelector('.' + currentReportType + '-date-display');

    const monthFrom = dateFrom.slice(0, 7);
    const monthTo = dateTo.slice(0, 7);

    if (monthInput) {
        monthInput.value = monthFrom;
    }
    monthFromInput.value = monthFrom;
    monthToInput.value = monthTo;
    dateFromInput.value = dateFrom;
    dateToInput.value = dateTo;

    // Format display text
    const fromDate = new Date(dateFrom + 'T00:00:00');
    const toDate = new Date(dateTo + 'T00:00:00');
    const fromText = fromDate.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
    const toText = toDate.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });

    dateDisplay.textContent = fromText === toText ? fromText : fromText + ' to ' + toText;

    closeDateRangeModal();
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('dateRangeModal');
    const modalContent = document.querySelector('.date-range-modal-content');
    if (modal && modal.classList.contains('is-open') && !modalContent.contains(event.target) && !event.target.closest('.btn-select-month')) {
        closeDateRangeModal();
    }

    const healthModal = document.getElementById('healthFormsFilterModal');
    const healthModalContent = document.getElementById('healthFormsFilterModalContent');
    if (healthModal && healthModal.classList.contains('is-open') && healthModalContent && !healthModalContent.contains(event.target) && !event.target.closest('[onclick="openHealthFormsFilterModal()"]')) {
        closeHealthFormsFilterModal();
    }

    const bmiWrap = document.getElementById('healthBmiDropdownWrap');
    if (bmiWrap && !bmiWrap.contains(event.target)) {
        setHealthBmiDropdownOpen(false);
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeDateRangeModal();
        closeHealthFormsFilterModal();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    updateHealthFormsFilterSummary();
    document.querySelectorAll('#healthFormsExportForm input, #healthFormsExportForm select').forEach(function(field) {
        field.addEventListener('change', updateHealthFormsFilterSummary);
        field.addEventListener('input', updateHealthFormsFilterSummary);
    });

    const bmiDisplay = document.getElementById('healthBmiDisplay');
    const bmiWrap = document.getElementById('healthBmiDropdownWrap');
    if (bmiDisplay && bmiWrap) {
        bmiDisplay.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            setHealthBmiDropdownOpen(!bmiWrap.classList.contains('is-open'));
        });
        bmiWrap.querySelectorAll('input[name="bmi_categories[]"]').forEach(function(input) {
            input.addEventListener('change', function() {
                syncHealthBmiDisplay();
                updateHealthFormsFilterSummary();
            });
        });
    }
});
</script>

<!-- Date Range Modal -->
<div class="date-range-modal" id="dateRangeModal">
    <div class="date-range-modal-content">
        <div class="date-range-modal-header">
            <h3>Select Date Range</h3>
        </div>
        <div class="date-range-modal-body">
            <div class="date-range-field">
                <label for="dateRangeFrom">From Date</label>
                <input type="date" id="dateRangeFrom" value="{{ date('Y-m-d') }}">
            </div>
            <div class="date-range-field">
                <label for="dateRangeTo">To Date</label>
                <input type="date" id="dateRangeTo" value="{{ date('Y-m-d') }}">
            </div>
        </div>
        <div class="date-range-modal-actions">
            <button type="button" class="date-range-modal-btn date-range-modal-btn-cancel" onclick="closeDateRangeModal()">
                Cancel
            </button>
            <button type="button" class="date-range-modal-btn date-range-modal-btn-select" onclick="applyDateRange()">
                Select
            </button>
        </div>
    </div>
</div>

<!-- Health Forms Filter Modal -->
<div class="date-range-modal" id="healthFormsFilterModal">
    <div class="date-range-modal-content health-filter-modal-content" id="healthFormsFilterModalContent">
        <div class="health-filter-modal-head">
            <div class="health-filter-modal-title-wrap">
                <span class="health-filter-modal-icon" aria-hidden="true"><x-outline-icon name="document-text" /></span>
                <div>
                    <h3>Filter Health Forms</h3>
                    <p>Export health form records by date, status, BMI, and condition details.</p>
                </div>
            </div>
            <button type="button" class="health-filter-modal-close" onclick="closeHealthFormsFilterModal()" aria-label="Close health forms filter">
                <x-outline-icon name="x-mark" />
            </button>
        </div>
        <form action="{{ route($healthFormsExportRouteName) }}" method="GET" id="healthFormsExportForm">
            <div class="date-range-modal-body health-filter-modal-body health-filter-grid">
                <div class="health-filter-field">
                    <label for="healthFormsDateFrom">From Date</label>
                    <input type="date" id="healthFormsDateFrom" name="date_from" value="{{ now()->startOfMonth()->toDateString() }}">
                </div>
                <div class="health-filter-field">
                    <label for="healthFormsDateTo">To Date</label>
                    <input type="date" id="healthFormsDateTo" name="date_to" value="{{ now()->toDateString() }}">
                </div>
                <div class="health-filter-field">
                    <label for="healthFormsCourse">Course</label>
                    <select id="healthFormsCourse" name="course">
                        <option value="">All Courses</option>
                        @foreach($healthFormCourses as $course)
                            <option value="{{ $course }}">{{ $course }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="health-filter-field">
                    <label for="healthFormsStatus">Status</label>
                    <select id="healthFormsStatus" name="status" class="health-status-select">
                        <option value="">All Status</option>
                        <option value="approved">Approved</option>
                        <option value="pending">Pending</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="health-filter-field is-full">
                    <label>BMI Category</label>
                    <div class="health-bmi-dropdown-wrap" id="healthBmiDropdownWrap">
                        <button type="button" class="health-bmi-display" id="healthBmiDisplay" aria-haspopup="listbox" aria-expanded="false">
                            All BMI Categories
                        </button>
                        <div class="health-bmi-menu" id="healthBmiMenu" role="listbox" aria-label="BMI category options">
                            <label class="health-bmi-option">
                                <input type="checkbox" name="bmi_categories[]" value="underweight">
                                Underweight
                            </label>
                            <label class="health-bmi-option">
                                <input type="checkbox" name="bmi_categories[]" value="normal">
                                Normal
                            </label>
                            <label class="health-bmi-option">
                                <input type="checkbox" name="bmi_categories[]" value="overweight">
                                Overweight
                            </label>
                            <label class="health-bmi-option">
                                <input type="checkbox" name="bmi_categories[]" value="obese">
                                Obese
                            </label>
                            <label class="health-bmi-option">
                                <input type="checkbox" name="bmi_categories[]" value="no_bmi">
                                No BMI recorded
                            </label>
                        </div>
                    </div>
                </div>
                <div class="health-filter-field is-full">
                    <label for="healthFormsConditionKeyword">Condition Keyword</label>
                    <input type="search" id="healthFormsConditionKeyword" name="condition_keyword" placeholder="Example: asthma, allergy, hypertension, remarks">
                </div>
                <div class="health-filter-field">
                    <label for="healthFormsConditionSource">Search Source</label>
                    <select id="healthFormsConditionSource" name="condition_source">
                        <option value="all">All condition fields</option>
                        <option value="health_form">Health form answers</option>
                        <option value="final_review">Final review condition</option>
                        <option value="remarks">Remarks only</option>
                    </select>
                </div>
                <div class="health-filter-field">
                    <label for="healthFormsConditionMatch">Keyword Match</label>
                    <select id="healthFormsConditionMatch" name="condition_match">
                        <option value="any">Any keyword</option>
                        <option value="all">All keywords</option>
                        <option value="exact">Exact phrase</option>
                    </select>
                </div>
            </div>
            <div class="date-range-modal-actions health-filter-modal-actions">
                <button type="button" class="date-range-modal-btn date-range-modal-btn-cancel" onclick="closeHealthFormsFilterModal()">
                    Cancel
                </button>
                <button type="submit" class="date-range-modal-btn date-range-modal-btn-select">
                    Generate CSV
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
