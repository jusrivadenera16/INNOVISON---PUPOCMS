@extends('layouts.student')

@section('title', 'Book Appointment')

@push('styles')
<style>
    /* --- PAGE LAYOUT --- */
    .booking-page-shell {
        position: relative;
        isolation: isolate;
        min-height: calc(100vh - 72px);
        padding: 8px 0 40px;
        background:
            linear-gradient(180deg, rgba(255, 250, 250, 0.70), rgba(255, 255, 255, 0.58) 42%, rgba(245, 248, 247, 0.72) 100%),
            url('{{ asset("images/student-bg.png") }}') center top / cover no-repeat;
        background-attachment: fixed, fixed;
        overflow: hidden;
    }
    .booking-page-shell::before {
        content: none;
    }
    .booking-page-container {
        position: relative;
        z-index: 1;
        padding: 8px 20px 40px;
    }
    .page-header {
        position: relative;
        margin-bottom: 22px;
        margin-top: -12px;
        padding: 18px 22px;
        border-radius: 24px;
        border: 1px solid rgba(139, 0, 0, 0.12);
        background:
            radial-gradient(circle at top right, rgba(255, 244, 194, 0.68), transparent 30%),
            linear-gradient(135deg, #fffef4 0%, #fff8fb 36%, #ffffff 100%);
        box-shadow:
            0 20px 40px rgba(15, 23, 42, 0.09),
            0 0 0 1px rgba(255,255,255,0.78) inset;
        overflow: hidden;
    }
    .page-header::before {
        content: "";
        position: absolute;
        inset: auto -60px -80px auto;
        width: 220px;
        height: 220px;
        background: radial-gradient(circle, rgba(139, 0, 0, 0.10) 0%, rgba(139, 0, 0, 0) 70%);
        pointer-events: none;
    }
    .page-header-icon {
        position: absolute;
        top: -12px;
        right: -8px;
        width: 180px;
        height: 180px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: rgba(112, 19, 27, 0.10);
        transform: rotate(-12deg);
        pointer-events: none;
        z-index: 0;
    }
    .page-header-icon svg {
        width: 100%;
        height: 100%;
        stroke-width: 1.7;
    }
    .page-kicker,
    .page-title,
    .page-subtitle,
    .page-steps {
        position: relative;
        z-index: 1;
    }
    .page-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 10px;
        border-radius: 999px;
        background: rgba(139, 0, 0, 0.08);
        color: #8B0000;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-bottom: 10px;
    }
    .page-title { color: #8B0000; font-weight: 800; font-size: 28px; margin: 0 0 8px 0; letter-spacing: -0.03em; }
    .page-subtitle { color: #64748b; font-size: 14px; margin: 0; max-width: 620px; line-height: 1.6; }
    .page-steps {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 14px;
    }
    .page-step {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.82);
        border: 1px solid rgba(148, 163, 184, 0.18);
        color: #334155;
        font-size: 12px;
        font-weight: 700;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    }
    .page-step-index {
        width: 22px;
        height: 22px;
        border-radius: 999px;
        background: #8B0000;
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 800;
        flex: 0 0 auto;
    }

    /* --- ALERTS --- */
    .alert { padding: 15px; border-radius: 8px; margin-bottom: 24px; border: 1px solid transparent; font-size: 14px; }
    .alert-success { background: #dcfce7; color: #155724; border-color: #c3e6cb; }
    .alert-danger { background: #fee2e2; color: #721c24; border-color: #f5c6cb; }
    .alert ul { margin: 5px 0 0 20px; padding: 0; }

    /* --- MAIN CARD --- */
    .booking-card {
        background: transparent;
        border-radius: 0;
        box-shadow: none;
        overflow: visible;
        border-top: 0;
        display: flex;
        flex-wrap: wrap;
        gap: 22px;
        padding: 0;
    }

    .booking-closure-notice {
        margin-bottom: 18px;
        padding: 16px 18px;
        border: 1px solid #facc15;
        border-left: 5px solid #7f1d2d;
        border-radius: 10px;
        background: #fff8dc;
        color: #64101d;
    }

    .booking-closure-notice strong {
        display: block;
        margin-bottom: 4px;
        font-size: 14px;
    }

    .booking-closure-notice p {
        margin: 0;
        font-size: 12px;
        line-height: 1.55;
    }

    .booking-disabled-fields {
        min-width: 0;
        margin: 0;
        padding: 0;
        border: 0;
    }

    .booking-disabled-fields:disabled {
        opacity: 0.62;
    }

    .booking-form-section {
        flex: 2;
        padding: 32px;
        min-width: 0;
        border: 1px solid rgba(139, 0, 0, 0.12);
        border-radius: 22px;
        background:
            linear-gradient(180deg, rgba(255,255,255,0.98) 0%, rgba(255,250,249,0.98) 100%);
        box-shadow:
            0 18px 38px rgba(15, 23, 42, 0.08),
            0 0 0 1px rgba(139, 0, 0, 0.04);
        position: relative;
        overflow: hidden;
    }
    .booking-form-section,
    .booking-form-section .form-section-title,
    .booking-form-section .input-label,
    .booking-form-section .form-control,
    .booking-form-section .form-control::placeholder,
    .booking-form-section .time-slot-hint,
    .booking-form-section .date-picker-month,
    .booking-form-section .date-picker-weekdays span,
    .booking-form-section .calendar-day,
    .booking-form-section .date-picker-toggle {
        color: #111111;
    }
    .booking-form-section::before {
        content: "";
        position: absolute;
        top: 0;
        left: 22px;
        right: 22px;
        height: 5px;
        border-radius: 999px;
        background: linear-gradient(90deg, #8B0000 0%, #c9872d 55%, #facc15 100%);
        pointer-events: none;
    }
    .booking-info-section {
        flex: 1;
        padding: 0;
        background: transparent;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .booking-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    /* --- FORM STYLING --- */
    .form-section-title {
        color: #20343a;
        font-size: 20px;
        font-weight: 800;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 12px;
        letter-spacing: -0.01em;
    }
    .section-title-badge {
        background: linear-gradient(135deg, #fee2e2, #fff1f2);
        color: #8B0000;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        border: 1px solid rgba(139, 0, 0, 0.10);
        box-shadow: 0 8px 18px rgba(139, 0, 0, 0.10);
        flex: 0 0 auto;
    }
    
    .input-group { position: relative; margin-bottom: 24px; }
    .input-label { display: block; font-size: 13px; font-weight: 700; color: #64748b; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
    .input-wrapper { position: relative; }
    
    .form-control {
        width: 100%;
        min-height: 50px;
        padding: 12px 16px;
        border: 1px solid rgba(148, 163, 184, 0.20);
        border-radius: 18px;
        font-size: 15px;
        color: #111111;
        transition: all 0.2s ease;
        background: linear-gradient(180deg, #ffffff 0%, #fff8f6 100%);
        box-shadow:
            0 10px 18px rgba(15, 23, 42, 0.05),
            inset 0 1px 0 rgba(255,255,255,0.86);
        font-weight: 400;
    }
    .form-control,
    .form-control option,
    textarea.form-control,
    input.form-control,
    select.form-control {
        color: #111111 !important;
    }
    .form-control:focus {
        border-color: #8B0000;
        box-shadow:
            0 0 0 4px rgba(139, 0, 0, 0.06),
            0 14px 24px rgba(139, 0, 0, 0.10),
            inset 0 1px 0 rgba(255,255,255,0.88);
        outline: none;
    }
    
    /* READONLY STYLE */
    .form-control[readonly] {
        background: linear-gradient(180deg, #fffaf8 0%, #f8fafc 100%);
        color: #111111;
        cursor: default;
        border-color: rgba(148, 163, 184, 0.16);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 118px;
        padding-top: 14px;
        line-height: 1.6;
        border-radius: 20px;
    }
    .time-display-input {
        background: linear-gradient(180deg, #ffffff 0%, #fff8f6 100%);
        cursor: pointer;
    }
    .time-display-input.is-disabled {
        background: linear-gradient(180deg, #fffaf8 0%, #f8fafc 100%);
        color: #94a3b8 !important;
        cursor: not-allowed;
    }
    .time-slots-container {
        display: none;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-top: 12px;
        align-items: stretch;
        padding: 14px;
        border: 1px solid rgba(139, 0, 0, 0.22);
        border-radius: 20px;
        background: linear-gradient(180deg, rgba(255, 251, 249, 0.92) 0%, rgba(255, 255, 255, 0.96) 100%);
        box-shadow:
            inset 0 1px 0 rgba(255,255,255,0.8),
            0 16px 30px rgba(15, 23, 42, 0.08),
            0 6px 14px rgba(139, 0, 0, 0.06);
    }
    .time-slot-btn {
        position: relative;
        width: 100%;
        min-height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(148, 163, 184, 0.22);
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        color: #1e293b;
        border-radius: 999px;
        padding: 7px 8px;
        font-size: 10px;
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: 0;
        text-align: center;
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
        cursor: pointer;
        transition: all 0.18s ease;
        box-shadow:
            0 12px 22px rgba(15, 23, 42, 0.08),
            0 1px 0 rgba(255,255,255,0.85) inset;
    }
    .time-slot-btn:hover {
        transform: translateY(-1px);
        border-color: #8B0000;
        background: linear-gradient(135deg, #8B0000, #70131B);
        color: #facc15;
        box-shadow: 0 12px 20px rgba(139, 0, 0, 0.16);
    }
    .time-slot-btn.selected {
        background: linear-gradient(135deg, #8B0000, #70131B);
        border-color: #8B0000;
        color: #ffffff;
        box-shadow: 0 14px 22px rgba(139, 0, 0, 0.20);
    }
    .time-slot-btn:disabled {
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        color: #94a3b8;
        border-color: rgba(226, 232, 240, 0.9);
        cursor: not-allowed;
        box-shadow: none;
    }
    .time-slot-btn:disabled:hover {
        transform: none;
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        color: #94a3b8;
        border-color: rgba(226, 232, 240, 0.9);
        box-shadow: none;
    }
    .time-slot-hint {
        display: block;
        margin-top: 10px;
        padding-left: 16px;
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        line-height: 1.55;
        position: relative;
    }
    .time-slot-hint::before {
        content: "*";
        position: absolute;
        top: 50%;
        left: 0;
        transform: translateY(-50%);
        color: #8B0000;
        font-size: 13px;
        font-weight: 900;
        line-height: 1;
    }
    .date-picker-wrapper {
        position: relative;
    }
    .date-display-input {
        background: linear-gradient(180deg, #ffffff 0%, #fff8f6 100%);
        cursor: pointer;
    }
    .date-picker-toggle {
        position: absolute;
        top: 50%;
        right: 10px;
        transform: translateY(-50%);
        border: 1px solid rgba(148, 163, 184, 0.18);
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        color: #8B0000;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        padding: 7px 12px;
        cursor: pointer;
        box-shadow:
            0 12px 22px rgba(15, 23, 42, 0.08),
            0 1px 0 rgba(255,255,255,0.82) inset;
    }
    .date-picker-toggle:hover {
        border-color: #8B0000;
        color: #8B0000;
        transform: translateY(calc(-50% - 1px));
    }
    .service-select-wrap {
        position: relative;
    }
    .service-select {
        position: absolute;
        opacity: 0;
        pointer-events: none;
        width: 0;
        height: 0;
        padding: 0;
        border: 0;
        margin: 0;
    }
    .service-select-display {
        width: 100%;
        min-height: 50px;
        padding: 12px 52px 12px 16px;
        border: 1px solid rgba(148, 163, 184, 0.20);
        border-radius: 18px;
        font-size: 15px;
        color: #111111;
        background:
            linear-gradient(180deg, #ffffff 0%, #fff8f6 100%);
        box-shadow:
            0 12px 22px rgba(15, 23, 42, 0.08),
            inset 0 1px 0 rgba(255,255,255,0.86);
        cursor: pointer;
        font-weight: 400;
        text-align: left;
        transition: all 0.2s ease;
    }
    .service-select-display:hover {
        border-color: rgba(139, 0, 0, 0.28);
        box-shadow:
            0 10px 18px rgba(139, 0, 0, 0.05),
            inset 0 1px 0 rgba(255,255,255,0.86);
    }
    .service-select-display.is-open,
    .service-select-display:focus {
        outline: none;
        border-color: #8B0000;
        box-shadow:
            0 0 0 4px rgba(139, 0, 0, 0.06),
            0 10px 18px rgba(139, 0, 0, 0.08);
    }
    .service-select option {
        color: #111111;
        background: #ffffff;
        font-weight: 700;
        padding: 10px 12px;
    }
    .service-select option[disabled] {
        color: #64748b;
        font-weight: 600;
    }
    .service-select:hover {
        border-color: rgba(139, 0, 0, 0.28);
        box-shadow:
            0 10px 18px rgba(139, 0, 0, 0.05),
            inset 0 1px 0 rgba(255,255,255,0.86);
    }
    .service-select-wrap::after {
        content: "";
        position: absolute;
        top: 50%;
        right: 18px;
        width: 10px;
        height: 10px;
        border-right: 2px solid #8B0000;
        border-bottom: 2px solid #8B0000;
        transform: translateY(-65%) rotate(45deg);
        pointer-events: none;
        transition: transform 0.18s ease;
    }
    .service-select-wrap::before {
        content: "";
        position: absolute;
        top: 50%;
        right: 42px;
        transform: translateY(-50%);
        width: 1px;
        height: 24px;
        background: rgba(148, 163, 184, 0.24);
        pointer-events: none;
    }
    .service-select-focus {
        position: absolute;
        inset: 0;
        border-radius: 14px;
        box-shadow: 0 0 0 4px rgba(139, 0, 0, 0.06);
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.18s ease;
    }
    .service-select-menu {
        position: absolute;
        top: calc(100% + 10px);
        left: 0;
        right: 0;
        display: none;
        gap: 10px;
        padding: 14px;
        border-radius: 18px;
        border: 1px solid rgba(139, 0, 0, 0.12);
        background: rgba(255, 255, 255, 0.98);
        box-shadow: 0 18px 34px rgba(15, 23, 42, 0.14);
        z-index: 80;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }
    .service-select-wrap.is-open .service-select-menu {
        display: grid;
    }
    .service-select-option {
        position: relative;
        width: 100%;
        border: 1px solid rgba(148, 163, 184, 0.22);
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        color: #1e293b;
        border-radius: 999px;
        padding: 12px 14px;
        font-size: 13px;
        font-weight: 800;
        letter-spacing: 0.01em;
        text-align: left;
        cursor: pointer;
        transition: all 0.18s ease;
        box-shadow:
            0 12px 22px rgba(15, 23, 42, 0.08),
            0 1px 0 rgba(255,255,255,0.82) inset;
    }
    .service-select-option:hover {
        transform: translateY(-1px);
        border-color: #8B0000;
        background: linear-gradient(135deg, #8B0000, #70131B);
        color: #facc15;
        box-shadow: 0 12px 20px rgba(139, 0, 0, 0.16);
    }
    .service-select-option.is-selected {
        background: linear-gradient(135deg, #8B0000, #70131B);
        color: #ffffff;
        border-color: #8B0000;
        box-shadow: 0 14px 24px rgba(139, 0, 0, 0.18);
    }
    .date-picker-panel {
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        width: 320px;
        max-width: min(100vw - 40px, 320px);
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.18);
        padding: 12px;
        z-index: 60;
    }
    .date-picker-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    .date-picker-nav {
        width: 32px;
        height: 32px;
        border: 1px solid #cbd5e1;
        background: #fff;
        border-radius: 8px;
        color: #334155;
        cursor: pointer;
        font-size: 16px;
        line-height: 1;
    }
    .date-picker-nav:hover:not(:disabled) {
        border-color: #8B0000;
        color: #8B0000;
    }
    .date-picker-nav:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }
    .date-picker-month {
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
    }
    .date-picker-weekdays,
    .date-picker-days {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 6px;
    }
    .date-picker-weekdays span {
        text-align: center;
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        padding: 4px 0;
    }
    .calendar-day,
    .calendar-empty {
        height: 36px;
        border-radius: 8px;
    }
    .calendar-empty {
        display: block;
    }
    .calendar-day {
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #1e293b;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
    }
    .calendar-day:hover:not(:disabled) {
        border-color: #8B0000;
        color: #8B0000;
    }
    .calendar-day:disabled {
        background: #f8fafc;
        color: #94a3b8;
        border-color: #e2e8f0;
        cursor: not-allowed;
        text-decoration: line-through;
    }
    .calendar-day.selected {
        background: #8B0000;
        border-color: #8B0000;
        color: #fff;
    }

    .btn-submit {
        background: linear-gradient(135deg, #8B0000, #70131B);
        color: white;
        border: none;
        padding: 14px 28px;
        border-radius: 14px;
        font-weight: 700;
        font-size: 16px;
        cursor: pointer;
        width: 100%;
        transition: transform 0.2s, box-shadow 0.2s;
        display: flex; align-items: center; justify-content: center; gap: 10px;
        box-shadow:
            0 0 0 3px rgba(139, 0, 0, 0.10),
            0 16px 28px rgba(112, 19, 27, 0.20);
    }
    .btn-submit:hover {
        background: #facc15;
        color: #8B0000;
        transform: translateY(-2px);
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.12),
            0 18px 30px rgba(139, 0, 0, 0.22);
    }

    /* --- WIDGETS --- */
    .info-card {
        background:
            linear-gradient(180deg, #ffffff 0%, #fcfcfe 100%);
        border: 1px solid rgba(30, 41, 59, 0.10);
        border-radius: 22px;
        padding: 22px;
        margin-bottom: 0;
        box-shadow:
            0 16px 32px rgba(15, 23, 42, 0.08),
            0 0 0 1px rgba(255,255,255,0.75) inset;
        position: relative;
        overflow: hidden;
    }
    .info-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 18px;
        right: 18px;
        height: 4px;
        border-radius: 999px;
        background: linear-gradient(90deg, #8B0000 0%, #facc15 100%);
    }
    .info-title {
        font-size: 16px;
        font-weight: 800;
        color: #20343a;
        margin: 0 0 16px 0;
        border-bottom: 2px solid #f1f5f9;
        padding-bottom: 12px;
    }
    .empty-state { text-align: center; padding: 20px 0; color: #94a3b8; }
    .empty-icon { font-size: 32px; margin-bottom: 10px; opacity: 0.5; display: block; }
    
    .app-list {
        display: grid;
        gap: 12px;
    }
    .appt-item {
        padding: 14px 14px 14px 16px;
        border: 1px solid rgba(226, 232, 240, 0.92);
        border-left: 4px solid #8B0000;
        border-radius: 16px;
        background: linear-gradient(180deg, #ffffff 0%, #fafbff 100%);
        margin-bottom: 0;
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.05);
    }
    .appt-service { font-weight: 800; color: #8B0000; font-size: 14px; letter-spacing: 0.01em; }
    .appt-time { font-size: 13px; color: #555; margin-top: 6px; line-height: 1.6; }
    .appt-status { display: inline-block; margin-top: 8px; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 800; }
    .appt-overflow-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        justify-content: center;
        flex-wrap: wrap;
        margin-top: 16px;
    }
    .appt-overflow-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        padding: 0 16px;
        border-radius: 999px;
        border: 1px solid rgba(139, 0, 0, 0.18);
        background: #ffffff;
        color: #8B0000;
        font-size: 13px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.18s ease;
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.05);
    }
    .appt-overflow-btn:hover {
        transform: translateY(-1px);
        background: #8B0000;
        color: #facc15;
        border-color: #8B0000;
        box-shadow: 0 14px 24px rgba(139, 0, 0, 0.16);
    }
    .appt-hidden-list {
        display: none;
        gap: 12px;
        margin-top: 12px;
    }
    .appt-hidden-list.is-open {
        display: grid;
    }
    
    .note-widget {
        background: linear-gradient(180deg, #fffdf5 0%, #fffbeb 100%);
        border: 1px solid rgba(245, 158, 11, 0.22);
        border-left: 5px solid #f59e0b;
        padding: 22px;
        border-radius: 16px;
        color: #92400e;
        font-size: 14px;
        line-height: 1.7;
        box-shadow:
            0 16px 32px rgba(146, 64, 14, 0.10),
            0 0 0 1px rgba(255,255,255,0.7) inset;
        position: relative;
        overflow: hidden;
    }
    .note-widget::before {
        content: "";
        position: absolute;
        top: -22px;
        right: -22px;
        width: 88px;
        height: 88px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(245, 158, 11, 0.18) 0%, rgba(245, 158, 11, 0) 72%);
        pointer-events: none;
    }
    .note-header {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 800;
        margin-bottom: 10px;
        color: #b45309;
        font-size: 16px;
    }

    .confirmation-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.58);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
        z-index: 1100;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }
    .confirmation-modal {
        width: min(640px, 100%);
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.72);
        border-top: 0;
        border-bottom: 0;
        box-shadow: 0 34px 84px rgba(15, 23, 42, 0.34);
        padding: 0;
        position: relative;
        overflow: hidden;
    }
    .confirmation-head {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 18px 58px 18px 22px;
        background: linear-gradient(135deg, #70131B, #8f2230);
        border-bottom: 1px solid rgba(255, 255, 255, 0.12);
    }
    .confirmation-head-badge {
        width: 46px;
        height: 46px;
        border-radius: 13px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        color: #ffffff;
        font-size: 1.1rem;
        font-weight: 900;
        background: rgba(255, 255, 255, .14);
        border: 1px solid rgba(255, 255, 255, .24);
        box-shadow: inset 0 1px 0 rgba(255,255,255,.16);
    }
    .confirmation-head-copy {
        min-width: 0;
    }
    .confirmation-close {
        position: absolute;
        top: 13px;
        right: 13px;
        width: 34px;
        height: 34px;
        border: 1px solid transparent;
        background: #70131b;
        color: #ffffff;
        font-size: 22px;
        line-height: 1;
        cursor: pointer;
        padding: 0;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        outline: none;
        transition: color .2s ease, background .2s ease, transform .2s ease;
    }
    .confirmation-close::after {
        content: "";
        position: absolute;
        top: -35%;
        left: -78%;
        width: 42%;
        height: 170%;
        background: linear-gradient(
            115deg,
            transparent 0%,
            rgba(255, 255, 255, .18) 30%,
            rgba(255, 255, 255, .82) 50%,
            rgba(255, 255, 255, .18) 70%,
            transparent 100%
        );
        transform: skewX(-18deg);
        transition: left .48s ease;
        pointer-events: none;
    }
    .confirmation-close:hover,
    .confirmation-close:focus-visible {
        background: #facc15;
        border-color: transparent;
        color: #70131b;
        transform: translateY(-1px);
        outline: none;
    }
    .confirmation-close:hover::after,
    .confirmation-close:focus-visible::after {
        left: 136%;
    }
    .confirmation-close svg {
        width: 17px;
        height: 17px;
        position: relative;
        z-index: 1;
    }
    .confirmation-title {
        margin: 0 0 4px 0;
        color: #ffffff;
        font-size: clamp(1.25rem, 2.4vw, 1.65rem);
        font-weight: 900;
        letter-spacing: 0;
    }
    .confirmation-subtitle {
        margin: 0;
        color: rgba(255, 255, 255, 0.88);
        font-size: 12px;
        line-height: 1.45;
    }
    .confirmation-body {
        padding: 24px 28px 28px;
        background: #fff;
    }
    .confirmation-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 22px;
    }
    .confirmation-item {
        min-height: 82px;
        border: 1px solid rgba(112, 19, 27, 0.12);
        border-radius: 16px;
        padding: 15px 16px;
        background: linear-gradient(180deg, #ffffff 0%, #fffafa 100%);
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.04);
    }
    .confirmation-item.is-reference {
        grid-column: 1 / -1;
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 14px;
        align-items: center;
        border-color: rgba(250, 204, 21, .70);
        background: linear-gradient(135deg, #fff8dc 0%, #fffef8 100%);
    }
    .confirmation-ref-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: #facc15;
        color: #111827;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
    }
    .confirmation-label {
        display: block;
        font-size: 10px;
        color: #64748b;
        margin-bottom: 3px;
        text-transform: uppercase;
        letter-spacing: .06em;
        font-weight: 900;
    }
    .confirmation-value {
        color: #111827;
        font-size: 13px;
        font-weight: 900;
        overflow-wrap: anywhere;
    }
    .confirmation-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 5px 9px;
        font-size: 10.5px;
        font-weight: 800;
        background: #fff3cd;
        color: #b45309;
        border: 1px solid rgba(245, 158, 11, 0.18);
    }
    .confirmation-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        flex-wrap: wrap;
        padding-top: 4px;
    }
    .confirmation-btn {
        border-radius: 999px;
        min-height: 46px;
        padding: 12px 20px;
        font-weight: 900;
        text-decoration: none;
        border: 1px solid transparent;
        cursor: pointer;
        font-size: 14px;
        transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease, color 0.18s ease;
    }
    .confirmation-btn-primary {
        background: #8f1823;
        color: #fff;
        border-color: #8f1823;
        box-shadow: 0 16px 28px rgba(143, 24, 35, .18);
    }
    .confirmation-btn-primary:hover {
        background: #facc15;
        color: #111827;
        border-color: #facc15;
        transform: translateY(-2px);
        box-shadow: 0 16px 26px rgba(112, 19, 27, 0.20);
    }
    .confirmation-btn-secondary {
        background: #ffffff;
        color: #70131B;
        border-color: rgba(112, 19, 27, 0.22);
    }
    .confirmation-btn-secondary:hover {
        background: #fffaf0;
        border-color: #facc15;
        transform: translateY(-2px);
    }

    /* Appointment submitted reference layout. */
    .confirmation-body {
        padding: 16px 18px 16px;
    }
    .confirmation-grid {
        gap: 8px;
        margin-bottom: 9px;
    }
    .confirmation-item {
        min-height: 58px;
        display: grid;
        grid-template-columns: 34px minmax(0, 1fr);
        gap: 10px;
        align-items: center;
        padding: 9px 12px;
        border-radius: 8px;
        background: #ffffff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, .035);
    }
    .confirmation-item.is-reference {
        min-height: 72px;
        grid-template-columns: 44px minmax(0, 1fr) auto;
        gap: 11px;
        padding: 11px 14px;
        border-radius: 8px;
        background: linear-gradient(135deg, #fff9e8 0%, #fffdf6 100%);
    }
    .confirmation-ref-icon,
    .confirmation-detail-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }
    .confirmation-ref-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: #facc15;
        color: #70131b;
        box-shadow: 0 10px 20px rgba(250, 204, 21, .2);
    }
    .confirmation-ref-icon svg { width: 21px; height: 21px; }
    .confirmation-detail-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #fff1f2;
        color: #9f1239;
    }
    .confirmation-detail-icon svg { width: 17px; height: 17px; }
    .confirmation-item-copy { min-width: 0; }
    .confirmation-item.is-reference .confirmation-label { margin-bottom: 3px; }
    .confirmation-item.is-reference .confirmation-value {
        color: #8f1823;
        font-size: clamp(17px, 2.5vw, 20px);
        line-height: 1.15;
    }
    .confirmation-copy-number {
        min-height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 7px 10px;
        border: 1px solid rgba(245, 158, 11, .48);
        border-radius: 8px;
        background: rgba(255, 255, 255, .82);
        color: #70131b;
        font-size: 10px;
        font-weight: 850;
        cursor: pointer;
        white-space: nowrap;
        transition: color .2s ease, background .2s ease, border-color .2s ease, transform .2s ease;
    }
    .confirmation-copy-number svg { width: 15px; height: 15px; }
    .confirmation-copy-number:hover,
    .confirmation-copy-number:focus-visible {
        border-color: #facc15;
        background: #facc15;
        color: #70131b;
        transform: translateY(-1px);
        outline: none;
    }
    .confirmation-status::before {
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #f59e0b;
    }
    .confirmation-next {
        margin-top: 3px;
        padding: 10px 10px 9px;
        border: 1px solid rgba(112, 19, 27, .12);
        border-radius: 10px;
        background: #ffffff;
    }
    .confirmation-next h3 {
        margin: 0 0 8px;
        color: #8f1823;
        font-size: 11px;
        font-weight: 900;
    }
    .confirmation-timeline {
        position: relative;
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 8px;
    }
    .confirmation-timeline::before {
        content: "";
        position: absolute;
        top: 10px;
        left: 12.5%;
        right: 12.5%;
        height: 1px;
        background: linear-gradient(90deg, #e89ca4 0 33%, rgba(232, 156, 164, .45) 33% 100%);
    }
    .confirmation-step {
        position: relative;
        z-index: 1;
        min-width: 0;
        text-align: center;
    }
    .confirmation-step-marker {
        width: 20px;
        height: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1.5px solid #f59e0b;
        border-radius: 50%;
        background: #ffffff;
        color: #ffffff;
    }
    .confirmation-step-marker svg { width: 12px; height: 12px; stroke-width: 2.5; }
    .confirmation-step.is-complete .confirmation-step-marker {
        border-color: #8f1823;
        background: #8f1823;
        box-shadow: 0 4px 10px rgba(143, 24, 35, .2);
    }
    .confirmation-step strong,
    .confirmation-step span { display: block; }
    .confirmation-step strong {
        margin-top: 5px;
        color: #1f2937;
        font-size: 9px;
        font-weight: 850;
    }
    .confirmation-step p {
        max-width: 108px;
        margin: 3px auto 0;
        color: #64748b;
        font-size: 8px;
        line-height: 1.35;
    }
    .confirmation-actions {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid rgba(112, 19, 27, .12);
    }
    .confirmation-btn {
        min-width: 118px;
        min-height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 10.5px;
    }
    .confirmation-btn-primary { min-width: 160px; }
    .confirmation-btn svg { width: 16px; height: 16px; }

    html[data-theme="dark"] .page-header,
    html[data-theme="dark"] .booking-form-section,
    html[data-theme="dark"] .info-card,
    html[data-theme="dark"] .note-widget,
    html[data-theme="dark"] .confirmation-modal {
        background: linear-gradient(180deg, #0f0f10 0%, #161618 100%) !important;
        border-color: rgba(250, 204, 21, 0.16) !important;
        box-shadow:
            0 18px 36px rgba(0, 0, 0, 0.42),
            0 0 0 1px rgba(250, 204, 21, 0.05) inset !important;
    }
    html[data-theme="dark"] .confirmation-modal {
        border-top: 0 !important;
        border-bottom: 0 !important;
    }
    html[data-theme="dark"] .booking-page-shell {
        background:
            linear-gradient(180deg, rgba(2, 6, 23, 0.82), rgba(15, 23, 42, 0.74) 42%, rgba(2, 6, 23, 0.84) 100%),
            url('{{ asset("images/student-bg.png") }}') center top / cover no-repeat;
        background-attachment: fixed, fixed;
        padding-top: 0;
    }
    html[data-theme="dark"] .booking-page-container {
        padding-top: 0;
    }
    html[data-theme="dark"] .booking-page-container .page-header {
        margin-top: 0;
    }
    html[data-theme="dark"] .booking-page-shell::before {
        content: none;
    }

    html[data-theme="dark"] .page-kicker,
    html[data-theme="dark"] .page-step,
    html[data-theme="dark"] .appt-item {
        background: linear-gradient(180deg, #17171a 0%, #1d1d21 100%) !important;
        border-color: rgba(250, 204, 21, 0.14) !important;
        color: #f8fafc !important;
    }
    html[data-theme="dark"] .page-header-icon {
        color: rgba(250, 204, 21, 0.08) !important;
    }

    html[data-theme="dark"] .page-title,
    html[data-theme="dark"] .form-section-title,
    html[data-theme="dark"] .info-title,
    html[data-theme="dark"] .appt-service,
    html[data-theme="dark"] .note-header,
    html[data-theme="dark"] .confirmation-title {
        color: #ffffff !important;
    }

    html[data-theme="dark"] .page-subtitle,
    html[data-theme="dark"] .page-step,
    html[data-theme="dark"] .input-label,
    html[data-theme="dark"] .appt-time,
    html[data-theme="dark"] .note-widget p,
    html[data-theme="dark"] .confirmation-subtitle,
    html[data-theme="dark"] .confirmation-label,
    html[data-theme="dark"] .confirmation-value,
    html[data-theme="dark"] .confirmation-status {
        color: #e5e7eb !important;
    }
    html[data-theme="dark"] .time-slot-hint {
        color: #e5e7eb !important;
    }
    html[data-theme="dark"] .time-slot-hint::before {
        color: #facc15 !important;
    }

    html[data-theme="dark"] .form-control,
    html[data-theme="dark"] .form-control option,
    html[data-theme="dark"] textarea.form-control,
    html[data-theme="dark"] input.form-control,
    html[data-theme="dark"] select.form-control,
    html[data-theme="dark"] .time-display-input,
    html[data-theme="dark"] .date-display-input,
    html[data-theme="dark"] .date-picker-month,
    html[data-theme="dark"] .date-picker-weekdays span,
    html[data-theme="dark"] .calendar-day,
    html[data-theme="dark"] .date-picker-toggle {
        background: #111214 !important;
        color: #ffffff !important;
        border-color: rgba(250, 204, 21, 0.16) !important;
        box-shadow: none !important;
    }
    html[data-theme="dark"] .time-display-input.is-disabled {
        background: #1a1c20 !important;
        color: #6b7280 !important;
    }
    html[data-theme="dark"] .service-select {
        background: linear-gradient(180deg, #111214 0%, #17171a 100%) !important;
    }
    html[data-theme="dark"] .service-select-display {
        background: linear-gradient(180deg, #111214 0%, #17171a 100%) !important;
        color: #ffffff !important;
        border-color: rgba(250, 204, 21, 0.16) !important;
        box-shadow: none !important;
    }
    html[data-theme="dark"] .service-select option {
        background: #111214 !important;
        color: #ffffff !important;
    }
    html[data-theme="dark"] .service-select option[disabled] {
        color: #94a3b8 !important;
    }
    html[data-theme="dark"] .service-select-menu {
        background: rgba(15, 18, 20, 0.98) !important;
        border-color: rgba(250, 204, 21, 0.14) !important;
        box-shadow: 0 20px 36px rgba(0, 0, 0, 0.34) !important;
    }
    html[data-theme="dark"] .service-select-option {
        background: linear-gradient(180deg, #17171a 0%, #1d1d21 100%) !important;
        color: #f8fafc !important;
        border-color: rgba(250, 204, 21, 0.12) !important;
        box-shadow: 0 10px 18px rgba(0, 0, 0, 0.22) !important;
    }
    html[data-theme="dark"] .service-select-option:hover {
        background: linear-gradient(135deg, #8B0000, #70131B) !important;
        color: #facc15 !important;
        border-color: #8B0000 !important;
    }
    html[data-theme="dark"] .service-select-option.is-selected {
        background: linear-gradient(135deg, #8B0000, #70131B) !important;
        color: #ffffff !important;
        border-color: #8B0000 !important;
    }
    html[data-theme="dark"] .service-select-wrap::after {
        border-right-color: #facc15;
        border-bottom-color: #facc15;
    }
    html[data-theme="dark"] .service-select-wrap::before {
        background: rgba(250, 204, 21, 0.16);
    }

    html[data-theme="dark"] .form-control::placeholder,
    html[data-theme="dark"] .time-display-input::placeholder,
    html[data-theme="dark"] .date-display-input::placeholder {
        color: #94a3b8 !important;
    }

    html[data-theme="dark"] .calendar-day:disabled {
        background: #1a1c20 !important;
        color: #6b7280 !important;
        border-color: rgba(148, 163, 184, 0.12) !important;
    }

    html[data-theme="dark"] .calendar-day.selected,
    html[data-theme="dark"] .time-slot-btn.selected,
    html[data-theme="dark"] .btn-submit,
    html[data-theme="dark"] .confirmation-btn-primary {
        background: linear-gradient(135deg, #8B0000, #70131B) !important;
        color: #ffffff !important;
        border-color: #8B0000 !important;
    }
    html[data-theme="dark"] .btn-submit:hover {
        background: #facc15 !important;
        color: #8B0000 !important;
        border-color: #facc15 !important;
    }

    html[data-theme="dark"] .time-slot-btn,
    html[data-theme="dark"] .date-picker-panel,
    html[data-theme="dark"] .confirmation-item {
        background: #111214 !important;
        color: #ffffff !important;
        border-color: rgba(250, 204, 21, 0.14) !important;
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.24) !important;
    }
    html[data-theme="dark"] .time-slots-container {
        background: linear-gradient(180deg, #121315 0%, #17171a 100%) !important;
        border-color: rgba(139, 0, 0, 0.5) !important;
        box-shadow:
            inset 0 1px 0 rgba(255,255,255,0.03),
            0 18px 34px rgba(0, 0, 0, 0.30),
            0 6px 16px rgba(139, 0, 0, 0.12) !important;
    }
    html[data-theme="dark"] .time-slot-btn:hover {
        background: linear-gradient(135deg, #8B0000, #70131B) !important;
        color: #facc15 !important;
        border-color: #8B0000 !important;
    }
    html[data-theme="dark"] .time-slot-btn:disabled,
    html[data-theme="dark"] .time-slot-btn:disabled:hover {
        background: #1a1c20 !important;
        color: #6b7280 !important;
        border-color: rgba(148, 163, 184, 0.12) !important;
        box-shadow: none !important;
        transform: none !important;
    }

    html[data-theme="dark"] .confirmation-btn-secondary {
        background: #17171a !important;
        color: #ffffff !important;
        border-color: rgba(250, 204, 21, 0.18) !important;
    }
    html[data-theme="dark"] .confirmation-status {
        background: rgba(250, 204, 21, 0.16) !important;
        color: #fef3c7 !important;
        border-color: rgba(250, 204, 21, 0.24) !important;
    }
    html[data-theme="dark"] .confirmation-body,
    html[data-theme="dark"] .confirmation-next {
        background: #0f1012 !important;
    }
    html[data-theme="dark"] .confirmation-next,
    html[data-theme="dark"] .confirmation-actions {
        border-color: rgba(250, 204, 21, .14) !important;
    }
    html[data-theme="dark"] .confirmation-detail-icon {
        background: rgba(143, 24, 35, .2);
        color: #fda4af;
    }
    html[data-theme="dark"] .confirmation-copy-number {
        background: #17171a;
        color: #f8fafc;
        border-color: rgba(250, 204, 21, .32);
    }
    html[data-theme="dark"] .confirmation-copy-number:hover,
    html[data-theme="dark"] .confirmation-copy-number:focus-visible {
        background: #facc15;
        color: #70131b;
    }
    html[data-theme="dark"] .confirmation-next h3,
    html[data-theme="dark"] .confirmation-step strong {
        color: #f8fafc;
    }
    html[data-theme="dark"] .confirmation-step p {
        color: #94a3b8;
    }
    html[data-theme="dark"] .confirmation-step-marker {
        background: #111214;
    }
    html[data-theme="dark"] .confirmation-close {
        color: #f8fafc !important;
    }
    html[data-theme="dark"] .confirmation-close:hover,
    html[data-theme="dark"] .confirmation-close:focus-visible {
        color: #70131b !important;
    }
    html[data-theme="dark"] .appt-overflow-btn {
        background: #17171a !important;
        color: #ffffff !important;
        border-color: rgba(250, 204, 21, 0.14) !important;
        box-shadow: 0 12px 22px rgba(0, 0, 0, 0.22) !important;
    }
    html[data-theme="dark"] .appt-overflow-btn:hover {
        background: #8B0000 !important;
        color: #facc15 !important;
        border-color: #8B0000 !important;
    }

    @media (max-width: 900px) {
        .booking-card { flex-direction: column; }
        .booking-form-section { border-right: none; }
    }

    @media (max-width: 680px) {
        .page-title { font-size: 26px; }
        .page-header {
            padding: 16px 16px;
            margin-bottom: 18px;
            margin-top: -8px;
        }
        .page-header-icon {
            top: 4px;
            right: -10px;
            width: 118px;
            height: 118px;
        }
        .page-steps {
            gap: 10px;
        }
        .page-step {
            width: 100%;
            justify-content: flex-start;
        }
        .booking-card {
            gap: 16px;
        }
        .booking-form-section,
        .booking-info-section {
            padding: 22px 16px;
        }
        .booking-grid-2 {
            grid-template-columns: 1fr;
            gap: 12px;
        }
        .time-slots-container {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .confirmation-modal {
            padding: 0;
        }
        .confirmation-head {
            padding: 16px 52px 16px 16px;
        }
        .confirmation-body {
            padding: 14px;
        }
        .confirmation-grid {
            grid-template-columns: 1fr;
        }
        .confirmation-item.is-reference {
            grid-template-columns: 40px minmax(0, 1fr);
            padding: 11px;
        }
        .confirmation-ref-icon {
            width: 40px;
            height: 40px;
        }
        .confirmation-copy-number {
            grid-column: 1 / -1;
            width: 100%;
        }
        .confirmation-timeline {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px 8px;
        }
        .confirmation-timeline::before {
            content: none;
        }
        .confirmation-actions {
            justify-content: stretch;
        }
        .confirmation-btn {
            width: 100%;
            text-align: center;
        }
    }

    .booking-page-shell {
        height: auto;
        min-height: calc(100vh - 72px);
        overflow: visible !important;
    }

    /* --- APPOINTMENT PAGE REFERENCE REDESIGN --- */
    .booking-page-shell {
        padding: 18px 0 36px;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, .82), rgba(255, 250, 250, .76)),
            url('{{ asset("images/student-bg.png") }}') center top / cover fixed no-repeat;
    }
    .booking-page-container {
        width: min(1180px, calc(100% - 32px));
        max-width: 1180px;
        margin: 0 auto;
        padding: 0;
    }
    .page-header {
        position: relative;
        min-height: 184px;
        display: grid;
        grid-template-columns: 104px minmax(0, 1fr) 292px;
        align-items: center;
        gap: 24px;
        margin: 0 0 14px;
        padding: 22px 28px;
        border: 1px solid rgba(255, 255, 255, .18);
        border-radius: 10px;
        background:
            linear-gradient(90deg, rgba(111, 0, 31, .99) 0%, rgba(105, 0, 29, .97) 50%, rgba(71, 0, 22, .79) 100%),
            url('{{ asset("images/PUPBG.jpg") }}') right 42% / 58% auto no-repeat;
        background-blend-mode: normal, luminosity;
        box-shadow: 0 16px 34px rgba(76, 5, 23, .22), inset 0 1px rgba(255,255,255,.08);
        color: #ffffff;
        overflow: hidden;
    }
    .page-header::before {
        content: "";
        position: absolute;
        inset: 0;
        width: auto;
        height: auto;
        background:
            radial-gradient(circle at 12% 18%, transparent 0 42px, rgba(255,255,255,.045) 43px 44px, transparent 45px 59px, rgba(255,255,255,.03) 60px 61px, transparent 62px),
            linear-gradient(105deg, transparent 42%, rgba(250, 204, 21, .045) 72%, transparent 100%);
        pointer-events: none;
    }
    .page-header::after {
        content: "";
        position: absolute;
        inset: 0 0 0 44%;
        background: url('{{ asset("images/PUPBG.jpg") }}') right center / cover no-repeat;
        opacity: .14;
        filter: saturate(.3) contrast(1.1);
        -webkit-mask-image: linear-gradient(90deg, transparent 0%, rgba(0,0,0,.78) 30%, #000 100%);
        mask-image: linear-gradient(90deg, transparent 0%, rgba(0,0,0,.78) 30%, #000 100%);
        pointer-events: none;
    }
    .page-header-visual {
        position: relative;
        z-index: 2;
        width: 96px;
        height: 96px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 7px solid rgba(255,255,255,.13);
        border-radius: 999px;
        background: #fffafa;
        box-shadow: 0 12px 24px rgba(20,0,7,.28);
    }
    .page-header-icon {
        position: static;
        width: 54px;
        height: 54px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #8b0b24;
        transform: none;
    }
    .page-header-icon svg { width: 54px; height: 54px; stroke-width: 1.7; }
    .page-header-visual-plus {
        position: absolute;
        right: -4px;
        bottom: 8px;
        width: 26px;
        height: 26px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 3px solid #fffafa;
        border-radius: 999px;
        background: #8b0b24;
        color: #ffffff;
        font-size: 17px;
        font-weight: 900;
        line-height: 1;
    }
    .page-header-content,
    .page-header-stats { position: relative; z-index: 2; min-width: 0; }
    .page-kicker {
        display: inline-flex;
        padding: 0;
        margin: 0 0 7px;
        border-radius: 0;
        background: transparent;
        color: #facc15;
        font-size: 10px;
        font-weight: 850;
        letter-spacing: 0;
    }
    .page-kicker svg { width: 13px; height: 13px; }
    .page-title {
        margin: 0 0 6px;
        color: #ffffff;
        font-size: clamp(26px, 3vw, 34px);
        font-weight: 900;
        letter-spacing: 0;
        line-height: 1.15;
    }
    .page-subtitle {
        max-width: 360px;
        margin: 0;
        color: rgba(255,255,255,.88);
        font-size: 13px;
        line-height: 1.55;
    }
    .page-steps {
        display: flex;
        align-items: center;
        flex-wrap: nowrap;
        gap: 8px;
        margin-top: 14px;
    }
    .page-step {
        min-height: 30px;
        display: inline-flex;
        padding: 5px 10px;
        gap: 7px;
        border: 1px solid rgba(255,255,255,.22);
        border-radius: 999px;
        background: rgba(255,255,255,.06);
        box-shadow: none;
        color: rgba(255,255,255,.82);
        font-size: 10px;
        font-weight: 750;
        white-space: nowrap;
    }
    .page-step.is-active { background: #ffffff; color: #70131b; }
    .page-step-index {
        width: 18px;
        height: 18px;
        background: rgba(255,255,255,.12);
        color: #ffffff;
        font-size: 8px;
    }
    .page-step.is-active .page-step-index { background: #8b0b24; color: #ffffff; }
    .page-step-connector { width: 16px; height: 1px; background: rgba(255,255,255,.42); }
    .page-header-stats {
        align-self: stretch;
        display: grid;
        align-content: center;
        gap: 0;
        padding-left: 26px;
        border-left: 1px solid rgba(255,255,255,.18);
    }
    .page-header-stat {
        display: grid;
        grid-template-columns: 42px minmax(0, 1fr);
        gap: 12px;
        align-items: center;
        padding: 12px 0;
    }
    .page-header-stat + .page-header-stat { border-top: 1px solid rgba(255,255,255,.13); }
    .page-header-stat-icon {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(250,204,21,.42);
        border-radius: 999px;
        background: rgba(79,0,19,.48);
        color: #facc15;
    }
    .page-header-stat-icon svg { width: 20px; height: 20px; }
    .page-header-stat-copy,
    .page-header-stat-copy small,
    .page-header-stat-copy strong,
    .page-header-stat-copy span { display: block; }
    .page-header-stat-copy small { color: rgba(255,255,255,.68); font-size: 9px; font-weight: 850; letter-spacing: .06em; text-transform: uppercase; }
    .page-header-stat-copy strong { margin-top: 2px; color: #ffffff; font-size: 14px; font-weight: 850; }
    .page-header-stat-copy span { margin-top: 2px; color: rgba(255,255,255,.72); font-size: 10px; }

    .booking-card {
        display: grid;
        grid-template-columns: minmax(0, 2.25fr) minmax(270px, .98fr);
        align-items: start;
        gap: 14px;
    }
    .booking-form-section {
        padding: 20px;
        border: 1px solid rgba(112,19,27,.11);
        border-radius: 8px;
        background: rgba(255,255,255,.96);
        box-shadow: 0 12px 28px rgba(15,23,42,.08);
    }
    .booking-form-section::before,
    .info-card::before { content: none; }
    .form-section-title {
        min-height: 36px;
        margin: 0 0 12px;
        padding: 0 0 12px;
        border-bottom: 1px solid #e8e4e4;
        color: #6f1120;
        font-size: 17px;
        font-weight: 850;
        gap: 9px;
    }
    .section-title-badge {
        width: 26px;
        height: 26px;
        border: 0;
        background: #fff1f2;
        box-shadow: none;
        font-size: 10px;
    }
    .booking-subsection-title {
        display: flex;
        align-items: center;
        gap: 7px;
        margin: 12px 0 10px;
        padding-bottom: 8px;
        border-bottom: 1px solid rgba(112,19,27,.10);
        color: #7c1b2b;
        font-size: 14px;
        font-weight: 900;
        text-transform: uppercase;
    }
    .booking-subsection-title svg { width: 14px; height: 14px; }
    .booking-grid-2 { gap: 14px; }
    .input-group { margin-bottom: 12px; }
    .input-label {
        margin-bottom: 5px;
        color: #4b5563;
        font-size: 14px;
        font-weight: 850;
        letter-spacing: 0;
    }
    .input-wrapper.has-leading-icon .form-control,
    .input-wrapper.has-leading-icon .service-select-display { padding-left: 38px; }
    .booking-field-icon {
        position: absolute;
        top: 50%;
        left: 12px;
        z-index: 3;
        width: 15px;
        height: 15px;
        color: #8b2332;
        transform: translateY(-50%);
        pointer-events: none;
    }
    .booking-field-icon svg { display: block; width: 15px; height: 15px; }
    .form-control,
    .service-select-display {
        min-height: 40px;
        padding: 9px 12px;
        border: 1px solid #dfe3e8;
        border-radius: 8px;
        background: #ffffff;
        box-shadow: none;
        color: #1f2937;
        font-size: 15px;
    }
    .form-control[readonly] { background: #fbfcfd; border-color: #e2e5e9; }
    .form-control:focus,
    .service-select-display:focus,
    .service-select-display.is-open {
        border-color: #8b0b24;
        box-shadow: 0 0 0 3px rgba(139,11,36,.08);
    }
    .service-select-wrap::before { right: 37px; height: 20px; }
    .service-select-wrap::after { right: 15px; width: 8px; height: 8px; }
    .date-picker-toggle {
        right: 7px;
        width: 28px;
        height: 28px;
        padding: 0;
        border: 0;
        border-radius: 6px;
        background: transparent;
        box-shadow: none;
        color: #6b7280;
    }
    .date-picker-toggle svg { width: 15px; height: 15px; }
    .date-display-input { padding-right: 42px; }
    textarea.form-control {
        min-height: 78px;
        padding: 11px 12px;
        border-radius: 8px;
        line-height: 1.5;
    }
    .booking-form-section textarea[name="remarks"]::placeholder {
        color: rgba(71, 85, 105, .48) !important;
        opacity: 1;
    }
    .time-slot-hint,
    .booking-field-help {
        display: block;
        margin-top: 5px;
        padding-left: 0;
        color: #7b8492;
        font-size: 13px;
        font-weight: 500;
        line-height: 1.45;
    }
    .time-slot-hint::before { content: none; }
    .appointment-summary-notice {
        display: grid;
        grid-template-columns: 30px minmax(0, 1fr);
        gap: 10px;
        align-items: center;
        margin: 4px 0 10px;
        padding: 10px 12px;
        border: 1px solid rgba(245,158,11,.12);
        border-radius: 7px;
        background: linear-gradient(90deg, #fffaf0, #fffdf7);
        color: #6f1120;
    }
    .appointment-summary-icon {
        width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        background: #fff3d6;
        color: #d68700;
    }
    .appointment-summary-icon svg { width: 15px; height: 15px; }
    .appointment-summary-notice strong,
    .appointment-summary-notice small { display: block; }
    .appointment-summary-notice strong { font-size: 14px; text-transform: uppercase; }
    .appointment-summary-notice small { margin-top: 3px; color: #5f6875; font-size: 13px; font-weight: 500; }
    .btn-submit {
        min-height: 42px;
        padding: 9px 18px;
        border-radius: 7px;
        background: linear-gradient(90deg, #780018, #97051f 70%, #780018);
        box-shadow: 0 8px 18px rgba(112,19,27,.18);
        font-size: 14px;
        font-weight: 850;
        transform: none;
    }
    .btn-submit svg { width: 15px; height: 15px; }
    .btn-submit svg:last-child { margin-left: 4px; }
    .btn-submit:hover { transform: translateY(-1px); }
    .btn-submit-idle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    .btn-submit-heartbeat {
        position: relative;
        display: none;
        width: 104px;
        height: 28px;
        overflow: hidden;
        background: transparent;
    }
    .btn-submit .btn-submit-heartbeat svg {
        position: absolute;
        top: -10px;
        left: 0;
        width: 104px;
        height: 50px;
        margin: 0;
    }
    .btn-submit-heartbeat polyline {
        stroke: #facc15;
        stroke-dasharray: 360;
        stroke-dashoffset: 360;
        vector-effect: non-scaling-stroke;
        animation: bookingHeartbeatTrace 2s linear infinite;
    }
    .btn-submit.is-loading,
    .btn-submit.is-loading:hover,
    html[data-theme="dark"] .btn-submit.is-loading,
    html[data-theme="dark"] .btn-submit.is-loading:hover {
        background: linear-gradient(90deg, #780018, #97051f 70%, #780018) !important;
        color: #ffffff !important;
        cursor: wait;
        opacity: 1;
        transform: none;
    }
    .btn-submit.is-loading .btn-submit-idle { display: none; }
    .btn-submit.is-loading .btn-submit-heartbeat { display: inline-block; }

    @keyframes bookingHeartbeatTrace {
        0% { stroke-dashoffset: 360; opacity: 0; }
        8% { opacity: 1; }
        62% { stroke-dashoffset: 0; opacity: 1; }
        82%, 100% { stroke-dashoffset: -360; opacity: 0; }
    }

    @media (prefers-reduced-motion: reduce) {
        .btn-submit-heartbeat polyline {
            animation: none;
            stroke-dashoffset: 0;
            opacity: 1;
        }
    }

    .booking-info-section { padding: 0; gap: 12px; }
    .info-card {
        margin: 0;
        padding: 15px;
        border: 1px solid rgba(112,19,27,.11);
        border-radius: 8px;
        background: rgba(255,255,255,.96);
        box-shadow: 0 10px 24px rgba(15,23,42,.07);
    }
    .info-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 11px;
        padding: 0 0 10px;
        border-bottom: 1px solid #ece7e7;
        color: #70131b;
        font-size: 15px;
        font-weight: 850;
    }
    .info-title-icon { width: 16px; height: 16px; display: inline-flex; color: #a51b32; }
    .info-title-icon svg { width: 16px; height: 16px; }
    .booking-empty-state {
        min-height: 170px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 18px;
        border: 1px solid #eee3e4;
        border-radius: 7px;
        background: linear-gradient(145deg, #fffdfd, #fff8f8);
        color: #4b5563;
    }
    .booking-empty-state .empty-icon {
        width: 52px;
        height: 52px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 0 10px;
        border-radius: 999px;
        background: #fff1f2;
        color: #b62239;
        opacity: 1;
    }
    .booking-empty-state .empty-icon svg { width: 29px; height: 29px; }
    .booking-empty-state strong { color: #252b35; font-size: 14px; }
    .booking-empty-state p { max-width: 220px; margin: 6px 0 0; color: #697386; font-size: 13px; line-height: 1.45; }
    .app-list { gap: 9px; }
    .appt-item {
        padding: 11px 12px;
        border: 1px solid #e6e1e2;
        border-left: 3px solid #8b0b24;
        border-radius: 7px;
        background: #ffffff;
        box-shadow: none;
    }
    .appt-service { font-size: 14px; }
    .appt-time { margin-top: 4px; font-size: 13px; }
    .appt-status { font-size: 13px; }
    .appt-overflow-btn { font-size: 14px; }
    .clinic-information-list { display: grid; gap: 12px; }
    .clinic-information-item {
        display: grid;
        grid-template-columns: 32px minmax(0, 1fr);
        gap: 9px;
        align-items: start;
    }
    .clinic-information-icon {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: #fff1f2;
        color: #a91d35;
    }
    .clinic-information-icon svg { width: 15px; height: 15px; }
    .clinic-information-item small,
    .clinic-information-item strong { display: block; }
    .clinic-information-item small { margin-bottom: 2px; color: #5e6672; font-size: 13px; font-weight: 850; }
    .clinic-information-item strong { color: #303743; font-size: 14px; font-weight: 600; line-height: 1.45; }
    .booking-reminder-card {
        padding: 15px;
        border: 1px solid rgba(245,158,11,.24);
        border-left: 1px solid rgba(245,158,11,.24);
        border-radius: 8px;
        background: linear-gradient(145deg, #fff9e8, #fff4d8);
        box-shadow: 0 10px 24px rgba(146,64,14,.08);
    }
    .booking-reminder-card::before { content: none; }
    .booking-reminder-card .note-header {
        margin-bottom: 9px;
        color: #8f1b28;
        font-size: 15px;
        font-weight: 850;
    }
    .note-header-icon { width: 16px; height: 16px; display: inline-flex; color: #e2a400; }
    .note-header-icon svg { width: 16px; height: 16px; }
    .booking-reminder-card p {
        margin: 0;
        color: #534a3d;
        font-size: 14px;
        line-height: 1.55;
    }
    .booking-reminder-card p + p { margin-top: 7px; }

    .time-slot-btn,
    .service-select-option {
        font-size: 14px;
    }

    html[data-theme="dark"] .booking-page-shell {
        background:
            linear-gradient(180deg, rgba(5,13,27,.88), rgba(9,22,39,.84)),
            url('{{ asset("images/student-bg.png") }}') center top / cover fixed no-repeat !important;
        padding-top: 18px;
    }
    html[data-theme="dark"] .booking-page-container .page-header {
        margin-top: 0;
        background:
            linear-gradient(90deg, rgba(43,0,14,.99) 0%, rgba(55,0,18,.97) 52%, rgba(14,8,18,.82) 100%),
            url('{{ asset("images/PUPBG.jpg") }}') right 42% / 58% auto no-repeat !important;
        background-blend-mode: normal, luminosity !important;
        border-color: rgba(255,255,255,.12) !important;
    }
    html[data-theme="dark"] .page-header-icon { color: #8b0b24 !important; }
    html[data-theme="dark"] .page-kicker,
    html[data-theme="dark"] .page-step { background: transparent !important; }
    html[data-theme="dark"] .page-step.is-active { background: #ffffff !important; color: #70131b !important; }
    html[data-theme="dark"] .booking-form-section,
    html[data-theme="dark"] .info-card {
        background: #121b2a !important;
        border-color: rgba(148,163,184,.24) !important;
        box-shadow: 0 14px 30px rgba(0,0,0,.28) !important;
    }
    html[data-theme="dark"] .form-section-title,
    html[data-theme="dark"] .booking-subsection-title,
    html[data-theme="dark"] .info-title { border-color: rgba(148,163,184,.18); }
    html[data-theme="dark"] .booking-subsection-title,
    html[data-theme="dark"] .booking-field-icon,
    html[data-theme="dark"] .info-title-icon { color: #fda4af; }
    html[data-theme="dark"] .booking-empty-state,
    html[data-theme="dark"] .appt-item {
        background: #172235 !important;
        border-color: rgba(148,163,184,.18) !important;
    }
    html[data-theme="dark"] .booking-empty-state strong,
    html[data-theme="dark"] .clinic-information-item strong { color: #f8fafc; }
    html[data-theme="dark"] .booking-empty-state p,
    html[data-theme="dark"] .clinic-information-item small { color: #b5c0d0; }
    html[data-theme="dark"] .appointment-summary-notice {
        background: #182235;
        border-color: rgba(250,204,21,.18);
    }
    html[data-theme="dark"] .appointment-summary-notice small { color: #cbd5e1; }
    html[data-theme="dark"] .booking-form-section textarea[name="remarks"]::placeholder {
        color: rgba(203, 213, 225, .48) !important;
    }
    html[data-theme="dark"] .booking-reminder-card {
        background: #242114 !important;
        border-color: rgba(250,204,21,.22) !important;
    }
    html[data-theme="dark"] .booking-reminder-card p { color: #e5dfc9 !important; }

    @media (max-width: 900px) {
        .page-header { grid-template-columns: 88px minmax(0, 1fr); }
        .page-header-visual { width: 82px; height: 82px; }
        .page-header-icon,
        .page-header-icon svg { width: 46px; height: 46px; }
        .page-header-stats {
            grid-column: 1 / -1;
            grid-template-columns: 1fr 1fr;
            padding: 10px 0 0;
            border-top: 1px solid rgba(255,255,255,.16);
            border-left: 0;
        }
        .page-header-stat { padding: 10px 16px; }
        .page-header-stat + .page-header-stat { border-top: 0; border-left: 1px solid rgba(255,255,255,.13); }
        .booking-card { grid-template-columns: 1fr; }
        .booking-info-section { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .booking-info-section .info-card:first-child { grid-row: span 2; }
    }
    @media (max-width: 680px) {
        .booking-page-container { width: min(100% - 24px, 1180px); }
        .page-header {
            grid-template-columns: 66px minmax(0, 1fr);
            gap: 14px;
            min-height: 0;
            padding: 18px 16px;
        }
        .page-header-visual { width: 64px; height: 64px; border-width: 5px; }
        .page-header-icon,
        .page-header-icon svg { width: 35px; height: 35px; }
        .page-header-visual-plus { right: -3px; bottom: 2px; width: 22px; height: 22px; font-size: 14px; }
        .page-title { font-size: 25px; }
        .page-subtitle { font-size: 13px; }
        .page-steps { max-width: 100%; overflow-x: auto; padding-bottom: 2px; scrollbar-width: none; }
        .page-steps::-webkit-scrollbar { display: none; }
        .page-step-connector { flex: 0 0 12px; }
        .page-header-stats { grid-template-columns: 1fr 1fr; }
        .page-header-stat { grid-template-columns: 34px minmax(0,1fr); padding: 10px 5px; }
        .page-header-stat-icon { width: 30px; height: 30px; }
        .page-header-stat-icon svg { width: 16px; height: 16px; }
        .booking-form-section { padding: 16px; }
        .booking-info-section { display: grid; grid-template-columns: 1fr; padding: 0; }
        .booking-info-section .info-card:first-child { grid-row: auto; }
    }
    @media (max-width: 480px) {
        .page-header { grid-template-columns: 1fr; text-align: center; }
        .page-header-visual { margin: 0 auto; }
        .page-subtitle { margin-inline: auto; }
        .page-steps { justify-content: flex-start; }
        .page-header-stats { grid-template-columns: 1fr; text-align: left; }
        .page-header-stat + .page-header-stat { border-top: 1px solid rgba(255,255,255,.13); border-left: 0; }
        .booking-grid-2 { grid-template-columns: 1fr; gap: 0; }
    }
</style>
@endpush

@section('content')
<div class="booking-page-shell">
<div class="container booking-page-container">
    
    <div class="page-header">
        <div class="page-header-visual" aria-hidden="true">
            <span class="page-header-icon"><x-outline-icon name="calendar-days" /></span>
            <span class="page-header-visual-plus">+</span>
        </div>

        <div class="page-header-content">
            <div class="page-kicker"><x-outline-icon name="sparkles" /> Clinic Appointments</div>
            <h1 class="page-title">Book an Appointment</h1>
            <p class="page-subtitle">Schedule a consultation with the PUP Taguig Medical Clinic.</p>
            <div class="page-steps" aria-label="Appointment booking steps">
                <div class="page-step is-active">
                    <span class="page-step-index">1</span>
                    <span>Appointment Details</span>
                </div>
                <span class="page-step-connector" aria-hidden="true"></span>
                <div class="page-step">
                    <span class="page-step-index">2</span>
                    <span>Choose Schedule</span>
                </div>
                <span class="page-step-connector" aria-hidden="true"></span>
                <div class="page-step">
                    <span class="page-step-index">3</span>
                    <span>Confirmation</span>
                </div>
            </div>
        </div>

        <div class="page-header-stats">
            <div class="page-header-stat">
                <span class="page-header-stat-icon"><x-outline-icon name="clock" /></span>
                <span class="page-header-stat-copy">
                    <small>Clinic Hours</small>
                    <strong>{{ $clinicHours['hours'] ?? '8:00 AM - 5:00 PM' }}</strong>
                    <span>{{ $clinicHours['operating_days_label'] ?? 'Mon-Fri' }}</span>
                </span>
            </div>
            <div class="page-header-stat">
                <span class="page-header-stat-icon"><x-outline-icon name="calendar-days" /></span>
                <span class="page-header-stat-copy">
                    <small>Available Slots</small>
                    <strong id="headerAvailableSlots">{{ !empty($clinicClosure) ? 'Booking Closed' : 'Select a Date' }}</strong>
                    <span id="headerAvailableSlotsNote">{{ !empty($clinicClosure) ? 'Temporarily unavailable' : 'Updates with your schedule' }}</span>
                </span>
            </div>
        </div>
    </div>

    <div class="booking-card">
        
        <div class="booking-form-section">
            <div class="form-section-title">
                <span class="section-title-badge">1</span>
                Appointment Details
            </div>

            
            @if(!empty($clinicClosure))
                <div class="booking-closure-notice" role="status">
                    <strong>New appointment booking is temporarily unavailable</strong>
                    <p>
                        {{ $clinicClosure['message'] }}
                        @if(!empty($clinicClosure['ends_at']))
                            Expected reopening: {{ $clinicClosure['ends_at']->format('M d, Y g:i A') }}.
                        @endif
                        Existing appointments and student records remain accessible.
                    </p>
                </div>
            @endif

            <form id="bookingForm" method="POST" action="/student/appointments/store" autocomplete="off">
                @csrf 
                <fieldset class="booking-disabled-fields" {{ !empty($clinicClosure) ? 'disabled' : '' }}>

                <div class="booking-subsection-title">
                    <x-outline-icon name="identification" />
                    <span>Personal Information</span>
                </div>
                
                <div class="booking-grid-2">
                    <div class="input-group">
                        <label class="input-label">Full Name</label>
                        <div class="input-wrapper has-leading-icon">
                            <span class="booking-field-icon" aria-hidden="true"><x-outline-icon name="user-circle" /></span>
                            <input type="text" name="name" class="form-control" value="{{ $user->name }}" readonly>
                        </div>
                    </div>

                    <div class="input-group">
                        <label class="input-label">{{ $studentContext['id_number_label'] ?? 'Student Number' }}</label>
                        <div class="input-wrapper has-leading-icon">
                           <span class="booking-field-icon" aria-hidden="true"><x-outline-icon name="identification" /></span>
                           <input type="text" name="student_number" class="form-control" value="{{ $studentContext['student_number'] ?? $user->student_number }}" readonly>
                        </div>
                    </div>

                    
                </div>

                <div class="input-group">
                    <label class="input-label">Email Address</label>
                    <div class="input-wrapper has-leading-icon">
                        <span class="booking-field-icon" aria-hidden="true"><x-outline-icon name="envelope" /></span>
                        <input type="email" name="email" class="form-control" value="{{ $user->email }}" readonly>
                    </div>
                </div>

                <div class="booking-subsection-title">
                    <x-outline-icon name="calendar-days" />
                    <span>Appointment Details</span>
                </div>

                <div class="booking-grid-2">
                    <div class="input-group">
                        <label class="input-label">Preferred Date</label>
                        <div class="input-wrapper date-picker-wrapper has-leading-icon">
                            <span class="booking-field-icon" aria-hidden="true"><x-outline-icon name="calendar-days" /></span>
                            <input id="preferredDate" type="hidden" name="date" value="{{ old('date') }}" required>
                            <input id="preferredDateDisplay" type="text" class="form-control date-display-input" placeholder="Select a date" readonly>
                            <button type="button" class="date-picker-toggle" id="preferredDateToggle" aria-label="Open date picker"><x-outline-icon name="calendar-days" /></button>
                            <div class="date-picker-panel" id="datePickerPanel" hidden>
                                <div class="date-picker-header">
                                    <button type="button" class="date-picker-nav" id="calendarPrev" aria-label="Previous month">&lt;</button>
                                    <div class="date-picker-month" id="calendarMonthLabel">Month 2026</div>
                                    <button type="button" class="date-picker-nav" id="calendarNext" aria-label="Next month">&gt;</button>
                                </div>
                                <div class="date-picker-weekdays">
                                    <span>Sun</span>
                                    <span>Mon</span>
                                    <span>Tue</span>
                                    <span>Wed</span>
                                    <span>Thu</span>
                                    <span>Fri</span>
                                    <span>Sat</span>
                                </div>
                                <div class="date-picker-days" id="calendarDays"></div>
                            </div>
                        </div>
                        <small class="time-slot-hint" id="dateHint">Weekends and past dates are unavailable.</small>
                    </div>

                    <div class="input-group">
                        <label class="input-label">Preferred Time</label>
                        <div class="input-wrapper has-leading-icon">
                            <span class="booking-field-icon" aria-hidden="true"><x-outline-icon name="clock" /></span>
                            <input id="preferredTimeDisplay" type="text" class="form-control time-display-input" readonly placeholder="Select a date first">
                            <input id="preferredTimeInput" type="hidden" name="time" value="{{ old('time') }}" required>
                        </div>
                        <div id="timeSlots" class="time-slots-container"></div>
                    <small class="time-slot-hint" id="timeSlotsHint">
                        Select a date to view available time slots.
                    </small>

                    
                    </div>
                </div>
                
                <div class="input-group">
                    <label class="input-label">Service Type</label>
                    <div class="input-wrapper service-select-wrap has-leading-icon">
                        <span class="booking-field-icon" aria-hidden="true"><x-outline-icon name="heart-pulse" /></span>
                        <select name="service" class="form-control service-select" id="serviceTypeSelect" required>
                            <option value="" disabled selected>Select a Service...</option>
                            <option value="General Consultation">General Consultation</option>
                            <option value="Blood Pressure Monitoring">Blood Pressure Monitoring</option>
                        </select>
                        <button type="button" class="service-select-display" id="serviceTypeDisplay" aria-haspopup="listbox" aria-expanded="false">
                            Select a Service...
                        </button>
                        <div class="service-select-menu" id="serviceTypeMenu" role="listbox" aria-label="Service Type options">
                            <button type="button" class="service-select-option" data-service-value="General Consultation">General Consultation</button>
                            <button type="button" class="service-select-option" data-service-value="Blood Pressure Monitoring">Blood Pressure Monitoring</button>
                        </div>
                    </div>
                </div>

                <div class="booking-subsection-title">
                    <x-outline-icon name="document-text" />
                    <span>Reason / Symptoms</span>
                </div>

                <div class="input-group">
                    <textarea name="remarks" class="form-control" placeholder="Briefly describe what you are feeling..." rows="3">{{ old('remarks') }}</textarea>
                    <small class="booking-field-help">Please provide as much detail as possible to help our nurse prepare for your consultation.</small>
                </div>

                <div class="appointment-summary-notice">
                    <span class="appointment-summary-icon" aria-hidden="true"><x-outline-icon name="clipboard-document-list" /></span>
                    <span>
                        <strong>Appointment Summary</strong>
                        <small>Your appointment details will be shown after you confirm your booking.</small>
                    </span>
                </div>

                <button type="submit" class="btn-submit" aria-label="{{ !empty($clinicClosure) ? 'Booking Temporarily Closed' : 'Confirm Appointment' }}">
                    <span class="btn-submit-idle">
                        <x-outline-icon name="calendar-days" />
                        <span>{{ !empty($clinicClosure) ? 'Booking Temporarily Closed' : 'Confirm Appointment' }}</span>
                        <x-outline-icon name="arrow-long-right" />
                    </span>
                    <span class="btn-submit-heartbeat" aria-hidden="true">
                        <svg viewBox="0 0 150 73" xmlns="http://www.w3.org/2000/svg" role="presentation">
                            <polyline
                                points="0,45.486 38.514,45.486 44.595,33.324 50.676,45.486 57.771,45.486 62.838,55.622 71.959,9 80.067,63.729 84.122,45.486 97.297,45.486 103.379,40.419 110.473,45.486 150,45.486"
                                stroke-miterlimit="10"
                                stroke-width="3"
                                fill="none"
                            ></polyline>
                        </svg>
                    </span>
                </button>
                </fieldset>
            </form>
        </div>

        <div class="booking-info-section">
            
            <div class="info-card">
                <h4 class="info-title">
                    <span class="info-title-icon"><x-outline-icon name="calendar-days" /></span>
                    <span>Upcoming Appointment</span>
                </h4>
                
                <div class="app-list">
                    @php
                        $visibleAppointments = $appointments->take(4);
                        $overflowAppointments = $appointments->slice(4);
                    @endphp

                    @forelse($visibleAppointments as $appt)
                        <div class="appt-item">
                            <div class="appt-service">{{ $appt->service }}</div>
                            <div class="appt-time">
                                {{ \Carbon\Carbon::parse($appt->date)->format('M d, Y') }} <br> 
                                <span style="font-weight:normal; font-size:13px; color:#777;">
                                    {{ \Carbon\Carbon::parse($appt->time)->format('g:i A') }}
                                </span>
                            </div>
                            
                            <div style="margin-top: 5px;">
                                @if($appt->status == 'Approved')
                                    <span class="appt-status" style="background: #dcfce7; color: #15803d;">
                                        ● Approved
                                    </span>
                                @else
                                    <span class="appt-status" style="background: #fff3cd; color: #b45309;">
                                        ● Pending
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="empty-state booking-empty-state">
                            <span class="empty-icon" aria-hidden="true"><x-outline-icon name="calendar-days" /></span>
                            <strong>No upcoming appointments</strong>
                            <p>Once your booking is approved, it will appear here.</p>
                        </div>
                        <div hidden>
                        <div class="empty-state">
                            <span class="empty-icon">📆</span>
                            <div>No appointments scheduled.</div>
                        </div>
                        </div>
                    @endforelse

                    @if($overflowAppointments->isNotEmpty())
                        <div id="moreAppointmentsList" class="appt-hidden-list">
                            @foreach($overflowAppointments as $appt)
                                <div class="appt-item">
                                    <div class="appt-service">{{ $appt->service }}</div>
                                    <div class="appt-time">
                                        {{ \Carbon\Carbon::parse($appt->date)->format('M d, Y') }} <br>
                                        <span style="font-weight:normal; font-size:13px; color:#777;">
                                            {{ \Carbon\Carbon::parse($appt->time)->format('g:i A') }}
                                        </span>
                                    </div>

                                    <div style="margin-top: 5px;">
                                        @if($appt->status == 'Approved')
                                            <span class="appt-status" style="background: #dcfce7; color: #15803d;">
                                                ● Approved
                                            </span>
                                        @else
                                            <span class="appt-status" style="background: #fff3cd; color: #b45309;">
                                                ● Pending
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="appt-overflow-actions">
                            <button type="button" class="appt-overflow-btn" id="seeMoreAppointmentsBtn">See more</button>
                            <a href="{{ url('/student/history') }}" class="appt-overflow-btn">View another schedule</a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="info-card clinic-information-card">
                <h4 class="info-title">
                    <span class="info-title-icon"><x-outline-icon name="identification" /></span>
                    <span>Clinic Information</span>
                </h4>
                <div class="clinic-information-list">
                    <div class="clinic-information-item">
                        <span class="clinic-information-icon"><x-outline-icon name="map-pin" /></span>
                        <span><small>Clinic Location</small><strong>PUP Taguig Medical Clinic,<br>General Santos Avenue, Taguig City</strong></span>
                    </div>
                    <div class="clinic-information-item">
                        <span class="clinic-information-icon"><x-outline-icon name="clock" /></span>
                        <span><small>Clinic Hours</small><strong>{{ $clinicHours['hours'] ?? '8:00 AM - 5:00 PM' }}<br>{{ $clinicHours['operating_days_label'] ?? 'Mon-Fri' }}</strong></span>
                    </div>
                    <div class="clinic-information-item">
                        <span class="clinic-information-icon"><x-outline-icon name="phone" /></span>
                        <span><small>Contact Number</small><strong>(02) 8837-5858</strong></span>
                    </div>
                </div>
            </div>

            <div class="note-widget booking-reminder-card">
                <div class="note-header">
                    <span class="note-header-icon"><x-outline-icon name="bell" /></span>
                    <span>Important Reminder</span>
                </div>
                <p>Please arrive 15 minutes before your scheduled appointment time.</p>
                <p>Bring any relevant medical documents or previous consultation records.</p>
            </div>

            <div class="note-widget legacy-note-widget" hidden>
                <div class="note-header">
                    <span>⚠️</span> Important Reminder
                </div>
                <p style="margin: 0;">
                    Clinic hours are <strong>8:00 AM - 7:00 PM</strong>, Mondays to Fridays. 
                    <br><br>
                    Please ensure your selected time falls within this range.
                </p>
            </div>

        </div>
    </div>
</div>
</div>

@if(session('appointment_confirmation'))
    @php
        $confirmation = session('appointment_confirmation');
        $appointmentNumber = (string) ($confirmation['apt_id'] ?? 'N/A');
        $rawConfirmationStatus = trim((string) ($confirmation['status'] ?? 'Pending'));
        $confirmationStatusLabel = strtolower(str_replace('_', ' ', $rawConfirmationStatus)) === 'pending'
            ? 'Pending Review'
            : ucwords(str_replace('_', ' ', $rawConfirmationStatus));
    @endphp
    <div class="confirmation-overlay" id="appointmentConfirmationOverlay">
        <div class="confirmation-modal" role="dialog" aria-modal="true" aria-labelledby="appointmentConfirmationTitle">
            <button type="button" class="confirmation-close" id="appointmentConfirmationClose" aria-label="Close confirmation">
                <x-outline-icon name="x-mark" />
            </button>
            <div class="confirmation-head">
                <div class="confirmation-head-badge">AP</div>
                <div class="confirmation-head-copy">
                    <h2 class="confirmation-title" id="appointmentConfirmationTitle">Appointment Submitted</h2>
                    <p class="confirmation-subtitle">Your request has been received. Keep your appointment number for tracking and updates.</p>
                </div>
            </div>
            <div class="confirmation-body">
                <div class="confirmation-grid">
                    <div class="confirmation-item is-reference">
                        <span class="confirmation-ref-icon" aria-hidden="true">
                            <x-outline-icon name="calendar-days" />
                        </span>
                        <span class="confirmation-item-copy">
                            <span class="confirmation-label">Appointment Number</span>
                            <span class="confirmation-value">{{ $appointmentNumber }}</span>
                        </span>
                        <button
                            type="button"
                            class="confirmation-copy-number"
                            id="appointmentConfirmationCopy"
                            data-copy-value="{{ $appointmentNumber }}"
                            aria-label="Copy appointment number"
                        >
                            <x-outline-icon name="clipboard-document-list" />
                            <span>Copy Number</span>
                        </button>
                    </div>
                    <div class="confirmation-item">
                        <span class="confirmation-detail-icon" aria-hidden="true"><x-outline-icon name="heart-pulse" /></span>
                        <span class="confirmation-item-copy">
                            <span class="confirmation-label">Service</span>
                            <span class="confirmation-value">{{ $confirmation['service'] ?? '-' }}</span>
                        </span>
                    </div>
                    <div class="confirmation-item">
                        <span class="confirmation-detail-icon" aria-hidden="true"><x-outline-icon name="calendar-days" /></span>
                        <span class="confirmation-item-copy">
                            <span class="confirmation-label">Preferred Date</span>
                            <span class="confirmation-value">{{ $confirmation['date'] ?? '-' }}</span>
                        </span>
                    </div>
                    <div class="confirmation-item">
                        <span class="confirmation-detail-icon" aria-hidden="true"><x-outline-icon name="clock" /></span>
                        <span class="confirmation-item-copy">
                            <span class="confirmation-label">Preferred Time</span>
                            <span class="confirmation-value">{{ $confirmation['time'] ?? '-' }}</span>
                        </span>
                    </div>
                    <div class="confirmation-item">
                        <span class="confirmation-detail-icon" aria-hidden="true"><x-outline-icon name="clipboard-document-list" /></span>
                        <span class="confirmation-item-copy">
                            <span class="confirmation-label">Current Status</span>
                            <span class="confirmation-status">{{ $confirmationStatusLabel }}</span>
                        </span>
                    </div>
                </div>

                <section class="confirmation-next" aria-labelledby="confirmationNextTitle">
                    <h3 id="confirmationNextTitle">What happens next?</h3>
                    <div class="confirmation-timeline">
                        <div class="confirmation-step is-complete">
                            <span class="confirmation-step-marker" aria-hidden="true"><x-outline-icon name="check" /></span>
                            <strong>Submitted</strong>
                            <p>Your appointment has been received.</p>
                        </div>
                        <div class="confirmation-step">
                            <span class="confirmation-step-marker" aria-hidden="true"></span>
                            <strong>Under Review</strong>
                            <p>Our team will review your request.</p>
                        </div>
                        <div class="confirmation-step">
                            <span class="confirmation-step-marker" aria-hidden="true"></span>
                            <strong>Approved</strong>
                            <p>You will receive a confirmation soon.</p>
                        </div>
                        <div class="confirmation-step">
                            <span class="confirmation-step-marker" aria-hidden="true"></span>
                            <strong>Completed</strong>
                            <p>Your appointment will be completed.</p>
                        </div>
                    </div>
                </section>

                <div class="confirmation-actions">
                    <a href="{{ url('/student/home') }}" class="confirmation-btn confirmation-btn-secondary">Back to Home</a>
                    <a href="{{ url('/student/account') }}" class="confirmation-btn confirmation-btn-primary">
                        <span>Go To My Profile</span>
                        <x-outline-icon name="arrow-long-right" />
                    </a>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const bookingForm = document.getElementById('bookingForm');
        const dateInput = document.getElementById('preferredDate');
        const dateDisplayInput = document.getElementById('preferredDateDisplay');
        const dateToggle = document.getElementById('preferredDateToggle');
        const datePickerPanel = document.getElementById('datePickerPanel');
        const calendarMonthLabel = document.getElementById('calendarMonthLabel');
        const calendarDays = document.getElementById('calendarDays');
        const calendarPrev = document.getElementById('calendarPrev');
        const calendarNext = document.getElementById('calendarNext');
        const timeInput = document.getElementById('preferredTimeInput');
        const timeDisplay = document.getElementById('preferredTimeDisplay');
        const timeSlots = document.getElementById('timeSlots');
        const slotsHint = document.getElementById('timeSlotsHint');
        const dateHint = document.getElementById('dateHint');
        const serviceTypeSelect = document.getElementById('serviceTypeSelect');
        const serviceTypeDisplay = document.getElementById('serviceTypeDisplay');
        const serviceTypeMenu = document.getElementById('serviceTypeMenu');
        const serviceTypeOptions = Array.from(document.querySelectorAll('.service-select-option'));
        const serviceTypeWrap = serviceTypeDisplay ? serviceTypeDisplay.closest('.service-select-wrap') : null;
        const headerAvailableSlots = document.getElementById('headerAvailableSlots');
        const headerAvailableSlotsNote = document.getElementById('headerAvailableSlotsNote');
        const bookingClosed = @json(!empty($clinicClosure));
        const availabilityUrl = @json(url('/student/appointments/availability'));

        if (!dateInput || !dateDisplayInput || !dateToggle || !datePickerPanel || !calendarMonthLabel || !calendarDays || !calendarPrev || !calendarNext || !timeInput || !timeDisplay || !timeSlots || !slotsHint) {
            return;
        }

        const today = new Date();
        today.setHours(0, 0, 0, 0);
        let viewMonth = new Date(today.getFullYear(), today.getMonth(), 1);

        function pad2(value) {
            return String(value).padStart(2, '0');
        }

        function parseDateValue(value) {
            if (!value) return null;
            const parts = String(value).split('-');
            if (parts.length !== 3) return null;

            const year = Number(parts[0]);
            const month = Number(parts[1]);
            const day = Number(parts[2]);
            if (!year || !month || !day) return null;

            const parsed = new Date(year, month - 1, day);
            if (
                parsed.getFullYear() !== year ||
                parsed.getMonth() !== month - 1 ||
                parsed.getDate() !== day
            ) {
                return null;
            }

            parsed.setHours(0, 0, 0, 0);
            return parsed;
        }

        function toDateValue(dateObj) {
            return dateObj.getFullYear() + '-' + pad2(dateObj.getMonth() + 1) + '-' + pad2(dateObj.getDate());
        }

        function formatDateDisplay(value) {
            const parsed = parseDateValue(value);
            if (!parsed) return '';
            return parsed.toLocaleDateString([], { month: 'long', day: 'numeric', year: 'numeric' });
        }

        function isWeekendDateObj(dateObj) {
            const day = dateObj.getDay();
            return day === 0 || day === 6;
        }

        function isPastDateObj(dateObj) {
            return dateObj.getTime() < today.getTime();
        }

        function isSelectableDateObj(dateObj) {
            return !isPastDateObj(dateObj) && !isWeekendDateObj(dateObj);
        }

        function normalizeTime(raw) {
            if (!raw) return '';
            const text = String(raw).trim();
            return text.length >= 5 ? text.slice(0, 5) : text;
        }

        function syncServiceTypeDisplay() {
            if (!serviceTypeSelect || !serviceTypeDisplay) return;

            const selectedValue = serviceTypeSelect.value || '';
            const selectedText = selectedValue
                ? (serviceTypeSelect.options[serviceTypeSelect.selectedIndex]?.text || selectedValue)
                : 'Select a Service...';

            serviceTypeDisplay.textContent = selectedText;

            serviceTypeOptions.forEach(function (option) {
                option.classList.toggle('is-selected', option.dataset.serviceValue === selectedValue);
            });
        }

        function setServiceTypeOpenState(isOpen) {
            if (!serviceTypeWrap || !serviceTypeDisplay) return;

            serviceTypeWrap.classList.toggle('is-open', isOpen);
            serviceTypeDisplay.classList.toggle('is-open', isOpen);
            serviceTypeDisplay.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        }

        function formatTimeLabel(value) {
            if (!value) return '';
            const parts = value.split(':');
            const hour = Number(parts[0] || 0);
            const minute = Number(parts[1] || 0);
            const dt = new Date();
            dt.setHours(hour, minute, 0, 0);
            return dt.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
        }

        function setTimeSlotsOpenState(isOpen) {
            timeSlots.style.display = isOpen ? 'grid' : 'none';
        }

        function syncTimeFieldState() {
            const hasDate = Boolean(dateInput.value);
            timeDisplay.classList.toggle('is-disabled', !hasDate);
            timeDisplay.setAttribute('aria-disabled', hasDate ? 'false' : 'true');
        }

        function setSelectedTime(value) {
            const normalized = normalizeTime(value);
            timeInput.value = normalized;
            timeDisplay.value = normalized ? formatTimeLabel(normalized) : '';
            timeDisplay.placeholder = normalized
                ? ''
                : (dateInput.value ? 'Choose an available time' : 'Select a date first');

            timeSlots.querySelectorAll('.time-slot-btn').forEach(function (btn) {
                btn.classList.toggle('selected', btn.dataset.value === normalized);
            });

            if (normalized) {
                setTimeSlotsOpenState(false);
                slotsHint.textContent = 'Time selected. Click the Preferred Time field to change it.';
            }

            syncTimeFieldState();
        }

        function closeDatePanel() {
            datePickerPanel.hidden = true;
        }

        if (serviceTypeSelect && serviceTypeDisplay && serviceTypeWrap) {
            syncServiceTypeDisplay();

            serviceTypeDisplay.addEventListener('click', function () {
                const shouldOpen = !serviceTypeWrap.classList.contains('is-open');
                setServiceTypeOpenState(shouldOpen);
            });

            serviceTypeOptions.forEach(function (option) {
                option.addEventListener('click', function () {
                    const value = option.dataset.serviceValue || '';
                    serviceTypeSelect.value = value;
                    syncServiceTypeDisplay();
                    setServiceTypeOpenState(false);
                });
            });

            document.addEventListener('click', function (event) {
                if (!serviceTypeWrap.contains(event.target)) {
                    setServiceTypeOpenState(false);
                }
            });
        }

        function renderCalendar() {
            const year = viewMonth.getFullYear();
            const month = viewMonth.getMonth();
            const firstOfMonth = new Date(year, month, 1);
            const firstWeekDay = firstOfMonth.getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();

            calendarMonthLabel.textContent = viewMonth.toLocaleDateString([], { month: 'long', year: 'numeric' });
            calendarDays.innerHTML = '';

            for (let i = 0; i < firstWeekDay; i++) {
                const emptyCell = document.createElement('span');
                emptyCell.className = 'calendar-empty';
                calendarDays.appendChild(emptyCell);
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const dayDate = new Date(year, month, day);
                dayDate.setHours(0, 0, 0, 0);
                const dateValue = toDateValue(dayDate);
                const dayButton = document.createElement('button');

                dayButton.type = 'button';
                dayButton.className = 'calendar-day';
                dayButton.textContent = String(day);

                const selectable = isSelectableDateObj(dayDate);
                if (!selectable) {
                    dayButton.disabled = true;
                    dayButton.title = isWeekendDateObj(dayDate)
                        ? 'Weekends are unavailable.'
                        : 'Past dates are unavailable.';
                } else {
                    dayButton.addEventListener('click', function () {
                        dateInput.value = dateValue;
                        dateDisplayInput.value = formatDateDisplay(dateValue);
                        loadAvailability(dateValue, '');
                        syncTimeFieldState();
                        renderCalendar();
                        closeDatePanel();
                    });
                }

                if (dateInput.value === dateValue) {
                    dayButton.classList.add('selected');
                }

                calendarDays.appendChild(dayButton);
            }

            const renderedCells = firstWeekDay + daysInMonth;
            const trailingCells = renderedCells % 7 === 0 ? 0 : (7 - (renderedCells % 7));
            for (let i = 0; i < trailingCells; i++) {
                const emptyCell = document.createElement('span');
                emptyCell.className = 'calendar-empty';
                calendarDays.appendChild(emptyCell);
            }

            const currentMonthStart = new Date(today.getFullYear(), today.getMonth(), 1).getTime();
            const viewingMonthStart = new Date(viewMonth.getFullYear(), viewMonth.getMonth(), 1).getTime();
            calendarPrev.disabled = viewingMonthStart <= currentMonthStart;
        }

        function openDatePanel() {
            datePickerPanel.hidden = false;
            renderCalendar();
        }

        function updateHeaderAvailability(value, note) {
            if (bookingClosed) {
                if (headerAvailableSlots) headerAvailableSlots.textContent = 'Booking Closed';
                if (headerAvailableSlotsNote) headerAvailableSlotsNote.textContent = 'Temporarily unavailable';
                return;
            }
            if (headerAvailableSlots) headerAvailableSlots.textContent = value;
            if (headerAvailableSlotsNote) headerAvailableSlotsNote.textContent = note;
        }

        function renderMessage(message) {
            timeSlots.innerHTML = '';
            setTimeSlotsOpenState(false);
            slotsHint.textContent = message;
            setSelectedTime('');
            if (dateInput.value) {
                updateHeaderAvailability('No Slots Available', 'Try another clinic date');
            } else {
                updateHeaderAvailability('Select a Date', 'Updates with your schedule');
            }
        }

        function renderSlots(slots, preselectedTime) {
            timeSlots.innerHTML = '';
            setTimeSlotsOpenState(true);
            const selected = normalizeTime(preselectedTime);
            let availableCount = 0;

            (slots || []).forEach(function (slot) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'time-slot-btn';
                btn.dataset.value = slot.value;
                btn.textContent = slot.label;

                if (!slot.available) {
                    btn.disabled = true;
                } else {
                    availableCount++;
                    btn.addEventListener('click', function () {
                        setSelectedTime(slot.value);
                    });
                }

                if (slot.available && selected && slot.value === selected) {
                    btn.classList.add('selected');
                }

                timeSlots.appendChild(btn);
            });

            if (availableCount === 0) {
                slotsHint.textContent = 'No available time slots for this date.';
                setSelectedTime('');
                updateHeaderAvailability('No Slots Available', 'Try another clinic date');
                return;
            }

            updateHeaderAvailability(
                availableCount + (availableCount === 1 ? ' Slot Available' : ' Slots Available'),
                'For your selected date'
            );

            if (selected && slots.some(function (slot) { return slot.available && slot.value === selected; })) {
                setSelectedTime(selected);
            } else {
                setSelectedTime('');
            }

            slotsHint.textContent = 'Select one available time slot.';
        }

        function isWeekendDate(value) {
            if (!value) return false;
            const parsed = new Date(value + 'T00:00:00');
            const day = parsed.getDay();
            return day === 0 || day === 6;
        }

        async function loadAvailability(dateValue, preselectedTime) {
            if (!dateValue) {
                renderMessage('Select a date to view available time slots.');
                return;
            }

            slotsHint.textContent = 'Loading available time slots...';
            timeSlots.innerHTML = '';
            updateHeaderAvailability('Checking Slots', 'Loading selected date');

            try {
                const response = await fetch(availabilityUrl + '?date=' + encodeURIComponent(dateValue), {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || 'Unable to load available schedules.');
                }

                if (!data.available && (!data.slots || data.slots.length === 0)) {
                    renderMessage(data.message || 'No available time slots for this date.');
                    return;
                }

                renderSlots(data.slots, preselectedTime);

                if (data.message) {
                    slotsHint.textContent = data.message;
                }
            } catch (error) {
                renderMessage(error.message || 'Unable to load available schedules right now.');
            }
        }

        dateToggle.addEventListener('click', function () {
            if (datePickerPanel.hidden) {
                openDatePanel();
            } else {
                closeDatePanel();
            }
        });

        dateDisplayInput.addEventListener('click', function () {
            openDatePanel();
        });
        dateDisplayInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openDatePanel();
            }
        });

        timeDisplay.addEventListener('click', function () {
            if (!dateInput.value) {
                slotsHint.textContent = 'Select a date first to view available time slots.';
                syncTimeFieldState();
                return;
            }

            if (timeSlots.children.length === 0) {
                loadAvailability(dateInput.value, timeInput.value);
                return;
            }

            setTimeSlotsOpenState(true);
            slotsHint.textContent = 'Select one available time slot.';
        });

        timeDisplay.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                timeDisplay.click();
            }
        });

        calendarPrev.addEventListener('click', function () {
            if (calendarPrev.disabled) {
                return;
            }
            viewMonth = new Date(viewMonth.getFullYear(), viewMonth.getMonth() - 1, 1);
            renderCalendar();
        });
        calendarNext.addEventListener('click', function () {
            viewMonth = new Date(viewMonth.getFullYear(), viewMonth.getMonth() + 1, 1);
            renderCalendar();
        });

        document.addEventListener('click', function (event) {
            const clickedInsidePanel = datePickerPanel.contains(event.target);
            const clickedDisplay = dateDisplayInput.contains(event.target);
            const clickedToggle = dateToggle.contains(event.target);
            if (!clickedInsidePanel && !clickedDisplay && !clickedToggle) {
                closeDatePanel();
            }
        });

        const initialDate = dateInput.value;
        const initialTime = normalizeTime(timeInput.value);
        if (initialTime) {
            timeInput.value = initialTime;
        }

        if (initialDate && parseDateValue(initialDate) && isSelectableDateObj(parseDateValue(initialDate))) {
            const parsedInitial = parseDateValue(initialDate);
            viewMonth = new Date(parsedInitial.getFullYear(), parsedInitial.getMonth(), 1);
            dateDisplayInput.value = formatDateDisplay(initialDate);
            loadAvailability(initialDate, initialTime);
        } else {
            dateInput.value = '';
            dateDisplayInput.value = '';
            renderMessage('Select a date to view available time slots.');
        }

        syncTimeFieldState();

        if (bookingForm) {
            bookingForm.addEventListener('submit', function (event) {
                let isValid = true;

                if (!dateInput.value) {
                    isValid = false;
                    openDatePanel();
                }

                if (!timeInput.value) {
                    isValid = false;
                    slotsHint.textContent = 'Please select one available time slot.';
                }

                if (!isValid) {
                    event.preventDefault();
                    event.stopPropagation();
                    return;
                }

                const submitButton = bookingForm.querySelector('.btn-submit');
                if (submitButton) {
                    if (submitButton.classList.contains('is-loading')) {
                        event.preventDefault();
                        return;
                    }

                    submitButton.classList.add('is-loading');
                    submitButton.disabled = true;
                    submitButton.setAttribute('aria-busy', 'true');
                    submitButton.setAttribute('aria-label', 'Submitting appointment');
                }
            });
        }

        renderCalendar();
        setTimeSlotsOpenState(false);

        const confirmationOverlay = document.getElementById('appointmentConfirmationOverlay');
        const confirmationClose = document.getElementById('appointmentConfirmationClose');
        const confirmationCopy = document.getElementById('appointmentConfirmationCopy');
        const seeMoreAppointmentsBtn = document.getElementById('seeMoreAppointmentsBtn');
        const moreAppointmentsList = document.getElementById('moreAppointmentsList');

        if (seeMoreAppointmentsBtn && moreAppointmentsList) {
            seeMoreAppointmentsBtn.addEventListener('click', function () {
                const isOpen = moreAppointmentsList.classList.toggle('is-open');
                seeMoreAppointmentsBtn.textContent = isOpen ? 'Show less' : 'See more';
            });
        }

        if (confirmationOverlay) {
            const closeConfirmation = function () {
                confirmationOverlay.style.display = 'none';
            };

            if (confirmationClose) {
                confirmationClose.addEventListener('click', closeConfirmation);
            }
            confirmationOverlay.addEventListener('click', function (event) {
                if (event.target === confirmationOverlay) {
                    closeConfirmation();
                }
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && confirmationOverlay.style.display !== 'none') {
                    closeConfirmation();
                }
            });
        }

        if (confirmationCopy) {
            confirmationCopy.addEventListener('click', async function () {
                const value = confirmationCopy.dataset.copyValue || '';
                const label = confirmationCopy.querySelector('span');

                try {
                    if (navigator.clipboard && window.isSecureContext) {
                        await navigator.clipboard.writeText(value);
                    } else {
                        const temporaryInput = document.createElement('textarea');
                        temporaryInput.value = value;
                        temporaryInput.setAttribute('readonly', '');
                        temporaryInput.style.position = 'fixed';
                        temporaryInput.style.opacity = '0';
                        document.body.appendChild(temporaryInput);
                        temporaryInput.select();
                        document.execCommand('copy');
                        temporaryInput.remove();
                    }

                    if (label) label.textContent = 'Copied';
                    window.setTimeout(function () {
                        if (label) label.textContent = 'Copy Number';
                    }, 1600);
                } catch (error) {
                    if (label) label.textContent = 'Copy failed';
                    window.setTimeout(function () {
                        if (label) label.textContent = 'Copy Number';
                    }, 1600);
                }
            });
        }
    });
</script>
@endpush
