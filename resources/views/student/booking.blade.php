@extends('layouts.student')

@section('title', 'Book Appointment')

@push('styles')
<style>
    /* --- PAGE LAYOUT --- */
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
        padding: 20px;
        z-index: 1100;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }
    .confirmation-modal {
        width: min(720px, 100%);
        background: #ffffff;
        border-radius: 22px;
        border: 1px solid rgba(255, 255, 255, 0.72);
        border-top: 3px solid #facc15;
        border-bottom: 4px solid #facc15;
        box-shadow: 0 34px 84px rgba(15, 23, 42, 0.34);
        padding: 0;
        position: relative;
        overflow: hidden;
    }
    .confirmation-head {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 24px 70px 24px 28px;
        background:
            radial-gradient(circle at 92% 10%, rgba(250, 204, 21, .16), transparent 34%),
            linear-gradient(135deg, #8f1823 0%, #70131B 55%, #5f1017 100%);
        border-bottom: 1px solid rgba(255, 255, 255, 0.12);
    }
    .confirmation-head-badge {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        color: #ffffff;
        font-size: 1.35rem;
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
        top: 16px;
        right: 16px;
        width: 38px;
        height: 38px;
        border: 1px solid rgba(255, 255, 255, 0.22);
        background: rgba(255, 255, 255, 0.16);
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
        transition: background 0.18s ease, transform 0.18s ease, border-color 0.18s ease;
    }
    .confirmation-close::after {
        content: "";
        position: absolute;
        inset: 0;
        background:
            linear-gradient(120deg,
                rgba(255, 255, 255, 0) 0%,
                rgba(248, 213, 220, 0.16) 22%,
                rgba(248, 213, 220, 0.42) 48%,
                rgba(248, 213, 220, 0.16) 72%,
                rgba(255, 255, 255, 0) 100%);
        transform: translateX(-135%);
        transition: transform 0.5s ease;
        pointer-events: none;
    }
    .confirmation-close:hover {
        background: rgba(143, 34, 48, 0.30);
        border-color: #facc15;
        transform: translateY(-1px);
    }
    .confirmation-close:hover::after {
        transform: translateX(135%);
    }
    .confirmation-title {
        margin: 0 0 6px 0;
        color: #ffffff;
        font-size: clamp(1.5rem, 3vw, 2rem);
        font-weight: 900;
        letter-spacing: 0;
    }
    .confirmation-subtitle {
        margin: 0;
        color: rgba(255, 255, 255, 0.88);
        font-size: 14px;
        line-height: 1.55;
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
        font-size: 11px;
        color: #64748b;
        margin-bottom: 5px;
        text-transform: uppercase;
        letter-spacing: .06em;
        font-weight: 900;
    }
    .confirmation-value {
        color: #111827;
        font-size: 15px;
        font-weight: 900;
        overflow-wrap: anywhere;
    }
    .confirmation-status {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 6px 12px;
        font-size: 12px;
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
        border-top-color: #facc15 !important;
        border-bottom-color: #facc15 !important;
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
    html[data-theme="dark"] .confirmation-close {
        color: #f8fafc !important;
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
            padding: 18px;
        }
        .confirmation-head {
            padding: 22px 62px 22px 20px;
        }
        .confirmation-body {
            padding: 20px;
        }
        .confirmation-grid {
            grid-template-columns: 1fr;
        }
        .confirmation-actions {
            justify-content: stretch;
        }
        .confirmation-btn {
            width: 100%;
            text-align: center;
        }
    }
</style>
@endpush

@section('content')
<div class="container" style="padding: 8px 20px 40px;">
    
    <div class="page-header">
        <div class="page-header-icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
            </svg>
        </div>
        <div class="page-kicker">Clinic Appointments</div>
        <h1 class="page-title">Book an Appointment</h1>
        <p class="page-subtitle">Fill out the form below to request a consultation with the school nurse.</p>
        <div class="page-steps">
            <div class="page-step">
                <span class="page-step-index">1</span>
                <span>Enter appointment details</span>
            </div>
            <div class="page-step">
                <span class="page-step-index">2</span>
                <span>Choose an available schedule</span>
            </div>
            <div class="page-step">
                <span class="page-step-index">3</span>
                <span>Submit and wait for approval</span>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <strong>Success!</strong> {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please check the form:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

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
                
                <div class="booking-grid-2">
                    <div class="input-group">
                        <label class="input-label">Full Name</label>
                        <div class="input-wrapper">
                            <input type="text" name="name" class="form-control" value="{{ $user->name }}" readonly>
                        </div>
                    </div>

                    <div class="input-group">
                        <label class="input-label">{{ $studentContext['id_number_label'] ?? 'Student Number' }}</label>
                        <div class="input-wrapper">
                           <input type="text" name="student_number" class="form-control" value="{{ $studentContext['student_number'] ?? $user->student_number }}" readonly>
                        </div>
                    </div>

                    
                </div>

                <div class="input-group">
                    <label class="input-label">Email Address</label>
                    <div class="input-wrapper">
                        <input type="email" name="email" class="form-control" value="{{ $user->email }}" readonly>
                    </div>
                </div>

                

                <div class="booking-grid-2">
                    <div class="input-group">
                        <label class="input-label">Preferred Date</label>
                        <div class="input-wrapper date-picker-wrapper">
                            <input id="preferredDate" type="hidden" name="date" value="{{ old('date') }}" required>
                            <input id="preferredDateDisplay" type="text" class="form-control date-display-input" placeholder="Select a date" readonly>
                            <button type="button" class="date-picker-toggle" id="preferredDateToggle">Pick</button>
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
                        <div class="input-wrapper">
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
                    <div class="input-wrapper service-select-wrap">
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

                <div class="input-group">
                    <label class="input-label">Reason / Symptoms</label>
                    <textarea name="remarks" class="form-control" placeholder="Briefly describe what you are feeling..." rows="3">{{ old('remarks') }}</textarea>
                </div>

                <button type="submit" class="btn-submit">
                    {{ !empty($clinicClosure) ? 'Booking Temporarily Closed' : 'Confirm Appointment' }}
                </button>
                </fieldset>
            </form>
        </div>

        <div class="booking-info-section">
            
            <div class="info-card">
                <h4 class="info-title">Upcoming Schedule</h4>
                
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
                                <span style="font-weight:normal; font-size:12px; color:#777;">
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
                        <div class="empty-state">
                            <span class="empty-icon">📆</span>
                            <div>No appointments scheduled.</div>
                        </div>
                    @endforelse

                    @if($overflowAppointments->isNotEmpty())
                        <div id="moreAppointmentsList" class="appt-hidden-list">
                            @foreach($overflowAppointments as $appt)
                                <div class="appt-item">
                                    <div class="appt-service">{{ $appt->service }}</div>
                                    <div class="appt-time">
                                        {{ \Carbon\Carbon::parse($appt->date)->format('M d, Y') }} <br>
                                        <span style="font-weight:normal; font-size:12px; color:#777;">
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

            <div class="note-widget">
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

@if(session('appointment_confirmation'))
    @php($confirmation = session('appointment_confirmation'))
    <div class="confirmation-overlay" id="appointmentConfirmationOverlay">
        <div class="confirmation-modal" role="dialog" aria-modal="true" aria-labelledby="appointmentConfirmationTitle">
            <button type="button" class="confirmation-close" id="appointmentConfirmationClose" aria-label="Close confirmation">x</button>
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
                        <span class="confirmation-ref-icon">#</span>
                        <span>
                            <span class="confirmation-label">Appointment Number</span>
                            <span class="confirmation-value">{{ $confirmation['apt_id'] ?? 'N/A' }}</span>
                        </span>
                    </div>
                    <div class="confirmation-item">
                        <span class="confirmation-label">Service</span>
                        <span class="confirmation-value">{{ $confirmation['service'] ?? '-' }}</span>
                    </div>
                    <div class="confirmation-item">
                        <span class="confirmation-label">Preferred Date</span>
                        <span class="confirmation-value">{{ $confirmation['date'] ?? '-' }}</span>
                    </div>
                    <div class="confirmation-item">
                        <span class="confirmation-label">Preferred Time</span>
                        <span class="confirmation-value">{{ $confirmation['time'] ?? '-' }}</span>
                    </div>
                    <div class="confirmation-item">
                        <span class="confirmation-label">Current Status</span>
                        <span class="confirmation-status">{{ $confirmation['status'] ?? 'Pending' }}</span>
                    </div>
                </div>

                <div class="confirmation-actions">
                    <button type="button" class="confirmation-btn confirmation-btn-secondary" id="appointmentConfirmationDone">Stay Here</button>
                    <a href="/student/account" class="confirmation-btn confirmation-btn-primary">Go To My Profile</a>
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

        function renderMessage(message) {
            timeSlots.innerHTML = '';
            setTimeSlotsOpenState(false);
            slotsHint.textContent = message;
            setSelectedTime('');
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
                return;
            }

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
                }
            });
        }

        renderCalendar();
        setTimeSlotsOpenState(false);

        const confirmationOverlay = document.getElementById('appointmentConfirmationOverlay');
        const confirmationClose = document.getElementById('appointmentConfirmationClose');
        const confirmationDone = document.getElementById('appointmentConfirmationDone');
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
            if (confirmationDone) {
                confirmationDone.addEventListener('click', closeConfirmation);
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
    });
</script>
@endpush
