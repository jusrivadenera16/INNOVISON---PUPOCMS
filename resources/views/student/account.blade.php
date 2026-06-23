@extends('layouts.student')

@section('title', 'My Account')

@push('styles')
<style>
    /* --- HERO PROFILE SECTION --- */
    .profile-hero {
        background: linear-gradient(135deg, #8B0000 0%, #5a0f15 100%);
        border-radius: 16px;
        padding: 40px;
        color: white;
        margin-bottom: 30px;
        box-shadow: 0 10px 20px rgba(139, 0, 0, 0.15);
        display: flex;
        align-items: center;
        gap: 30px;
        flex-wrap: wrap;
    }

    .hero-avatar {
        width: 100px;
        height: 100px;
        background: rgba(255,255,255,0.2);
        border: 2px solid rgba(255,255,255,0.4);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        font-weight: 800;
        color: #fff;
        overflow: hidden;
    }

    .hero-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .hero-info { flex: 1; }
    .hero-name { font-size: 32px; font-weight: 800; margin: 0; line-height: 1.2; color: white; }
    .hero-course { font-size: 16px; opacity: 0.9; margin-top: 5px; font-weight: 500; }
    .hero-badge {
        background: #ffc107;
        color: #70131B;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        margin-left: 10px;
        vertical-align: middle;
    }

    /* Stats Row inside Hero */
    .hero-stats {
        display: flex;
        gap: 40px;
        margin-top: 10px;
        padding-top: 20px;
        border-top: 1px solid rgba(255,255,255,0.2);
    }
    .stat-item { text-align: left; }
    .stat-val { font-size: 24px; font-weight: 700; display: block; }
    .stat-label { font-size: 12px; text-transform: uppercase; opacity: 0.8; letter-spacing: 0.5px; }

    /* --- LAYOUT GRID --- */
    .account-layout {
        max-width: 960px;
        margin: 0 auto;
    }

    .page-intro {
        margin-bottom: 18px;
    }

    .page-intro-title {
        margin: 0;
        font-size: 28px;
        color: #600000;
        font-weight: 800;
    }

    .page-intro-text {
        margin: 6px 0 0;
        font-size: 14px;
        color: #6b7b7d;
        line-height: 1.5;
    }
    .page-hero {
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
    .page-hero::before {
        content: "";
        position: absolute;
        inset: auto -60px -80px auto;
        width: 220px;
        height: 220px;
        background: radial-gradient(circle, rgba(139, 0, 0, 0.10) 0%, rgba(139, 0, 0, 0) 70%);
        pointer-events: none;
    }
    .page-hero-icon {
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
    .page-hero-icon svg {
        width: 100%;
        height: 100%;
        stroke-width: 1.7;
    }
    .page-hero-kicker,
    .page-hero-title,
    .page-hero-text,
    .page-hero-steps {
        position: relative;
        z-index: 1;
    }
    .page-hero-kicker {
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
    .page-hero-title {
        color: #8B0000;
        font-weight: 800;
        font-size: 28px;
        margin: 0 0 8px 0;
        letter-spacing: -0.03em;
    }
    .page-hero-text {
        color: #64748b;
        font-size: 14px;
        margin: 0;
        max-width: 620px;
        line-height: 1.6;
    }
    .page-hero-steps {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 14px;
    }
    .page-hero-step {
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
    .page-hero-step::before {
        content: "";
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #8B0000;
        flex: 0 0 auto;
        box-shadow: 0 0 0 4px rgba(139, 0, 0, 0.08);
    }

    /* --- APPOINTMENT CARDS --- */
    .section-title {
        color: #20343a;
        font-size: 20px;
        font-weight: 800;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .appt-card {
        background: #fff;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 16px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.03);
        border: 1px solid #f1f5f9;
        border-left: 5px solid #cbd5e1;
        transition: transform 0.2s;
    }
    .appt-card:hover { transform: translateY(-3px); }

    .appt-card.approved { border-left-color: #10b981; }
    .appt-card.pending { border-left-color: #f59e0b; }
    .appt-card.cancelled { border-left-color: #ef4444; }
    .appt-card.completed { border-left-color: #3b82f6; }
    .appt-card.missed { border-left-color: #c2410c; }
    .appt-card.expired { border-left-color: #6b7280; }

    .appt-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
    .appt-service { font-size: 18px; font-weight: 700; color: #334155; }
    .appt-date { font-size: 14px; color: #64748b; font-weight: 600; text-align: right; }
    
    .appt-notes {
        background: #f8fafc;
        padding: 12px;
        border-radius: 8px;
        font-size: 14px;
        color: #475569;
        margin-bottom: 15px;
        border: 1px dashed #cbd5e1;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 30px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
    }
    .status-badge.approved { background: #dcfce7; color: #15803d; }
    .status-badge.pending { background: #fffbeb; color: #b45309; }
    .status-badge.cancelled { background: #fee2e2; color: #b91c1c; }
    .status-badge.completed { background: #dbeafe; color: #1e40af; }
    .status-badge.missed { background: #ffedd5; color: #9a3412; }
    .status-badge.expired { background: #f3f4f6; color: #4b5563; }

    .profile-grid-3,
    .profile-grid-2 {
        display: grid;
        gap: 12px;
        margin-bottom: 15px;
    }
    .profile-grid-3,
    .profile-grid-2 { grid-template-columns: 1fr; }

    /* --- SIDEBAR WIDGETS --- */
    .widget-card {
        background: #fff;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.03);
        border: 1px solid #e2e8f0;
        margin-bottom: 24px;
    }

    .profile-form-section {
        position: relative;
        overflow: visible;
        --field-bottom: #8f2230;
        --field-bottom-focus: #f59e0b;
        margin-bottom: 18px;
        padding: 18px 16px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: linear-gradient(180deg, #ffffff 0%, #fffdf6 100%);
    }
    .profile-form-section::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: #70131B;
        opacity: 0.95;
    }
    .profile-form-section.accent-gold::before {
        background: #facc15;
    }
    .profile-form-section.accent-maroon::before {
        background: #70131B;
    }
    .profile-form-section.accent-maroon {
        --field-bottom: #8f2230;
        --field-bottom-focus: #f59e0b;
    }
    .profile-form-section.accent-gold {
        --field-bottom: #d4a60a;
        --field-bottom-focus: #b45309;
    }

    .profile-sections-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        align-items: stretch;
        margin-bottom: 18px;
    }

    .profile-column-stack {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .profile-sections-grid .profile-form-section {
        margin-bottom: 0;
        height: 100%;
        box-shadow:
            0 12px 24px rgba(112, 19, 27, 0.08),
            0 4px 10px rgba(15, 23, 42, 0.06);
    }
    .profile-frame-equal {
        min-height: 280px;
        height: auto;
        display: flex;
        flex-direction: column;
    }

    .profile-frame-equal .profile-grid-3,
    .profile-frame-equal .profile-grid-2 {
        margin-bottom: 10px;
    }

    .profile-frame-equal .profile-info-row:last-child {
        margin-bottom: 0;
    }

    .profile-form-section-title {
        margin: 4px 0 14px;
        font-size: 15px;
        font-weight: 900;
        letter-spacing: 0.02em;
        color: #70131B;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        gap: 7px;
    }
    .profile-form-section-title svg {
        width: 16px;
        height: 16px;
        flex: 0 0 auto;
    }

    .profile-card-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
    }

    .profile-card-heading {
        min-width: 0;
    }
    .profile-card-title {
        margin: 0;
        font-size: 28px;
        color: #600000;
        font-weight: 800;
        line-height: 1.1;
    }

    .profile-card-description {
        margin: 6px 0 0;
        font-size: 14px;
        color: #6b7b7d;
        line-height: 1.5;
    }

    .profile-edit-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        position: relative;
        overflow: hidden;
        white-space: nowrap;
        padding: 11px 18px;
        border: 1px solid #8f2230;
        border-radius: 999px;
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #ffffff;
        font-size: 14px;
        font-weight: 800;
        letter-spacing: 0.01em;
        cursor: pointer;
        box-shadow:
            0 0 0 3px rgba(112, 19, 27, 0.12),
            0 10px 22px rgba(112, 19, 27, 0.20);
        transition: color .08s linear, transform .18s ease, box-shadow .18s ease, background .18s ease;
        z-index: 0;
    }

    .profile-edit-btn::after {
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

    .profile-edit-btn:hover {
        transform: translateY(-1px);
        border-color: #facc15;
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.22),
            0 14px 24px rgba(112, 19, 27, 0.16);
        color: #70131B;
        background: #facc15;
    }

    .profile-edit-btn:hover::after {
        transform: translateX(135%);
    }

    .profile-enrollment-empty {
        display: grid;
        gap: 14px;
        padding: 22px;
        border-radius: 20px;
        border: 1px solid rgba(139, 0, 0, 0.12);
        background: linear-gradient(180deg, #ffffff 0%, #fffaf6 100%);
        box-shadow: 0 16px 30px rgba(15, 23, 42, 0.06);
        margin-top: 10px;
    }
    .profile-enrollment-empty-head {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .profile-enrollment-empty-icon {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(139, 0, 0, 0.08);
        color: #8B0000;
        flex: 0 0 auto;
    }
    .profile-enrollment-empty-title {
        margin: 0;
        color: #8B0000;
        font-size: 18px;
        font-weight: 800;
    }
    .profile-enrollment-empty-copy {
        margin: 0;
        color: #64748b;
        font-size: 14px;
        line-height: 1.6;
    }
    .profile-enrollment-empty-note {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        width: fit-content;
        padding: 8px 12px;
        border-radius: 999px;
        background: rgba(250, 204, 21, 0.12);
        color: #7c2d12;
        font-size: 12px;
        font-weight: 800;
    }

    #profileActionBar {
        margin-top: 14px;
    }

    #saveAction {
        display: none;
        gap: 10px;
        justify-content: flex-end;
        align-items: center;
    }

    .profile-action-btn {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        position: relative;
        overflow: hidden;
        white-space: nowrap;
        padding: 11px 18px;
        border-radius: 999px;
        border: 1px solid #8f2230;
        color: #ffffff;
        font-size: 14px;
        font-weight: 800;
        letter-spacing: 0.01em;
        cursor: pointer;
        transition: color .08s linear, transform .18s ease, box-shadow .18s ease, background .18s ease;
        z-index: 0;
    }

    .profile-action-btn::after {
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

    .profile-action-btn:hover {
        transform: translateY(-1px);
    }

    .profile-action-btn:hover::after {
        transform: translateX(135%);
    }

    .profile-action-btn.save {
        background: linear-gradient(135deg, #70131B, #8f2230);
        border-color: #8f2230;
        box-shadow:
            0 0 0 3px rgba(112, 19, 27, 0.12),
            0 10px 22px rgba(112, 19, 27, 0.20);
    }

    .profile-action-btn.save:hover {
        border-color: #facc15;
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.18),
            0 14px 24px rgba(112, 19, 27, 0.16);
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #ffffff;
    }

    .profile-action-btn.cancel {
        background: linear-gradient(135deg, #6b7280, #4b5563);
        border-color: #6b7280;
        box-shadow:
            0 0 0 3px rgba(100, 116, 139, 0.16),
            0 10px 22px rgba(15, 23, 42, 0.18);
        color: #ffffff;
    }

    .profile-action-btn.cancel:hover {
        border-color: #facc15;
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.18),
            0 14px 24px rgba(15, 23, 42, 0.22);
        background: linear-gradient(135deg, #4b5563, #374151);
        color: #ffffff;
    }

    .input-label { font-size: 12px; font-weight: 700; color: #64748b; margin-bottom: 6px; text-transform: uppercase; display: block; }

    .profile-grid-3 > div,
    .profile-grid-2 > div,
    .profile-info-row {
        display: grid;
        grid-template-columns: minmax(150px, 42%) minmax(0, 1fr);
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: linear-gradient(180deg, #ffffff 0%, #fffdf6 100%);
        transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
    }

    .profile-grid-3 > div.is-editing,
    .profile-grid-2 > div.is-editing,
    .profile-info-row.is-editing {
        border-color: rgba(250, 204, 21, 0.95);
        background: linear-gradient(180deg, #fffbea 0%, #fff7d1 100%);
        box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.22);
    }

    .profile-info-row {
        margin-bottom: 12px;
    }

    .profile-grid-3 > div .input-label,
    .profile-grid-2 > div .input-label,
    .profile-info-row .input-label {
        margin: 0;
        font-size: 14px;
        font-weight: 800;
        letter-spacing: 0.01em;
        text-transform: none;
        color: #111827;
    }
    
    .form-control { 
        width: 100%; 
        padding: 10px 14px; 
        border: 1px solid #cbd5e1; 
        border-radius: 8px; 
        font-size: 14px; 
        color: #334155; 
        transition: 0.2s; 
        background: #fff; 
    }
    .profile-grid-3 > div .form-control,
    .profile-grid-2 > div .form-control,
    .profile-info-row .form-control {
        font-size: 15px;
        color: #111827;
        text-align: left;
        font-weight: 400;
        letter-spacing: 0;
        min-height: 50px;
        padding: 12px 16px;
        border: 1px solid rgba(148, 163, 184, 0.20) !important;
        border-radius: 18px !important;
        background: linear-gradient(180deg, #ffffff 0%, #fff8f6 100%) !important;
        box-shadow:
            0 10px 18px rgba(15, 23, 42, 0.05),
            inset 0 1px 0 rgba(255,255,255,0.86) !important;
        transition: border-color 0.18s ease, background 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
    }
    .metric-field {
        position: relative;
        display: flex;
        align-items: center;
    }
    .metric-field .form-control {
        padding-right: 16px;
        text-align: left !important;
    }
    .metric-field .form-control:focus {
        border-color: #8B0000 !important;
        box-shadow:
            0 0 0 4px rgba(139, 0, 0, 0.06),
            0 14px 24px rgba(139, 0, 0, 0.10),
            inset 0 1px 0 rgba(255,255,255,0.88) !important;
        background: linear-gradient(180deg, #ffffff 0%, #fff8f6 100%) !important;
        transform: translateY(-1px);
    }
    .soft-field .form-control:focus {
        border-color: #8B0000 !important;
        box-shadow:
            0 0 0 4px rgba(139, 0, 0, 0.06),
            0 14px 24px rgba(139, 0, 0, 0.10),
            inset 0 1px 0 rgba(255,255,255,0.88) !important;
        background: linear-gradient(180deg, #ffffff 0%, #fff8f6 100%) !important;
        transform: translateY(-1px);
    }
    .widget-card .voice-field-wrap input[type="text"],
    .widget-card .voice-field-wrap input[type="email"],
    .widget-card .voice-field-wrap input[type="tel"],
    .widget-card .voice-field-wrap input[type="number"],
    .widget-card .voice-field-wrap input[type="search"],
    .widget-card .voice-field-wrap input:not([type]),
    .widget-card .voice-field-wrap textarea {
        padding-right: 16px !important;
        padding-left: 44px !important;
    }
    .widget-card .voice-field-inline-mic {
        left: 10px !important;
        right: auto !important;
    }
    .profile-info-row textarea.form-control {
        text-align: left;
        min-height: 76px;
        resize: none;
    }
    .profile-course-field {
        min-height: 58px !important;
        line-height: 1.45;
        overflow: hidden;
    }
    .profile-static-field {
        display: flex;
        align-items: center;
        white-space: normal;
        word-break: break-word;
    }
    .guisis-sync-banner {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        gap: 12px;
        align-items: center;
        margin-bottom: 16px;
        padding: 14px 16px;
        border-radius: 18px;
        border: 1px solid rgba(250, 204, 21, 0.46);
        background: linear-gradient(135deg, #fff8d6 0%, #fffef4 100%);
        color: #4b2e05;
        box-shadow: 0 12px 24px rgba(112, 19, 27, 0.06);
    }
    .guisis-sync-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 32px;
        padding: 0 12px;
        border-radius: 999px;
        background: #70131B;
        color: #ffffff;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .guisis-sync-copy {
        margin: 0;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.55;
    }
    .guisis-pending-value {
        color: #7f1d2d !important;
        font-weight: 800;
    }
    .profile-grid-3 > div .form-control:disabled,
    .profile-grid-2 > div .form-control:disabled,
    .profile-info-row .form-control:disabled {
        color: #111827;
        background: linear-gradient(180deg, #ffffff 0%, #fff8f6 100%) !important;
        opacity: 1;
    }
    .profile-grid-3 > div .form-control:focus,
    .profile-grid-2 > div .form-control:focus,
    .profile-info-row .form-control:focus {
        border-color: #8B0000 !important;
        background: linear-gradient(180deg, #ffffff 0%, #fff8f6 100%) !important;
        box-shadow:
            0 0 0 4px rgba(139, 0, 0, 0.06),
            0 14px 24px rgba(139, 0, 0, 0.10),
            inset 0 1px 0 rgba(255,255,255,0.88) !important;
        transform: translateY(-1px);
    }
    .form-control:focus { border-color: #8B0000; box-shadow: 0 0 0 3px rgba(139, 0, 0, 0.05); outline: none; }
    .form-control:disabled { background: #f8fafc; color: #94a3b8; cursor: not-allowed; }

    html[data-theme="dark"] .profile-grid-3 > div,
    html[data-theme="dark"] .profile-grid-2 > div,
    html[data-theme="dark"] .profile-info-row {
        background: linear-gradient(180deg, rgba(15, 23, 42, 0.95) 0%, rgba(30, 41, 59, 0.94) 100%);
        border-color: rgba(148, 163, 184, 0.3);
    }
    html[data-theme="dark"] .profile-grid-3 > div.is-editing,
    html[data-theme="dark"] .profile-grid-2 > div.is-editing,
    html[data-theme="dark"] .profile-info-row.is-editing {
        border-color: rgba(250, 204, 21, 0.62);
        background: linear-gradient(180deg, rgba(133, 77, 14, 0.35) 0%, rgba(146, 64, 14, 0.24) 100%);
        box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.18);
    }
    html[data-theme="dark"] .profile-form-section {
        background: linear-gradient(180deg, rgba(15, 23, 42, 0.92) 0%, rgba(30, 41, 59, 0.9) 100%);
        border-color: rgba(148, 163, 184, 0.3);
    }
    html[data-theme="dark"] .profile-form-section.accent-maroon {
        --field-bottom: #fca5a5;
        --field-bottom-focus: #fde047;
    }
    html[data-theme="dark"] .profile-form-section.accent-gold {
        --field-bottom: #facc15;
        --field-bottom-focus: #fde047;
    }
    html[data-theme="dark"] .profile-sections-grid .profile-form-section {
        box-shadow:
            0 14px 28px rgba(0, 0, 0, 0.28),
            0 4px 14px rgba(250, 204, 21, 0.08);
    }
    html[data-theme="dark"] .profile-form-section-title {
        color: #f8fafc;
    }
    html[data-theme="dark"] .profile-card-title {
        color: #ffffff;
    }
    html[data-theme="dark"] .profile-card-description {
        color: #cbd5e1;
    }
    html[data-theme="dark"] .page-hero {
        background: linear-gradient(180deg, #0f0f10 0%, #161618 100%) !important;
        border-color: rgba(250, 204, 21, 0.16) !important;
        box-shadow:
            0 18px 36px rgba(0, 0, 0, 0.42),
            0 0 0 1px rgba(250, 204, 21, 0.05) inset !important;
    }
    html[data-theme="dark"] .page-hero-kicker,
    html[data-theme="dark"] .page-hero-step {
        background: linear-gradient(180deg, #17171a 0%, #1d1d21 100%) !important;
        border-color: rgba(250, 204, 21, 0.14) !important;
        color: #f8fafc !important;
    }
    html[data-theme="dark"] .page-hero-title {
        color: #ffffff !important;
    }
    html[data-theme="dark"] .page-hero-text {
        color: #e5e7eb !important;
    }
    html[data-theme="dark"] .page-hero-icon {
        color: rgba(250, 204, 21, 0.08) !important;
    }
    html[data-theme="dark"] .profile-edit-btn {
        background: linear-gradient(135deg, #70131B, #8f2230);
        border-color: #8f2230;
        color: #ffffff;
        box-shadow:
            0 0 0 3px rgba(112, 19, 27, 0.12),
            0 12px 24px rgba(0, 0, 0, 0.28);
    }
    html[data-theme="dark"] .profile-edit-btn:hover {
        background: #facc15;
        color: #70131B;
        border-color: #facc15;
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.22),
            0 14px 24px rgba(0, 0, 0, 0.28);
    }
    html[data-theme="dark"] .profile-enrollment-empty {
        background: linear-gradient(180deg, #0f0f10 0%, #161618 100%) !important;
        border-color: rgba(250, 204, 21, 0.14) !important;
        box-shadow:
            0 18px 36px rgba(0, 0, 0, 0.30),
            0 0 0 1px rgba(250, 204, 21, 0.04) inset !important;
    }
    html[data-theme="dark"] .profile-enrollment-empty-icon {
        background: rgba(250, 204, 21, 0.10) !important;
        color: #facc15 !important;
    }
    html[data-theme="dark"] .profile-enrollment-empty-title {
        color: #ffffff !important;
    }
    html[data-theme="dark"] .profile-enrollment-empty-copy {
        color: #cbd5e1 !important;
    }
    html[data-theme="dark"] .profile-enrollment-empty-note {
        background: rgba(250, 204, 21, 0.12) !important;
        color: #facc15 !important;
    }
    html[data-theme="dark"] .profile-action-btn.save {
        background: linear-gradient(135deg, #70131B, #8f2230);
        border-color: #8f2230;
    }
    html[data-theme="dark"] .profile-action-btn.cancel {
        background: linear-gradient(135deg, #475569, #334155);
        border-color: #64748b;
        color: #ffffff;
    }

    html[data-theme="dark"] .profile-grid-3 > div .input-label,
    html[data-theme="dark"] .profile-grid-2 > div .input-label,
    html[data-theme="dark"] .profile-info-row .input-label,
    html[data-theme="dark"] .profile-grid-3 > div .form-control,
    html[data-theme="dark"] .profile-grid-2 > div .form-control,
    html[data-theme="dark"] .profile-info-row .form-control {
        color: #f8fafc;
    }
    html[data-theme="dark"] .profile-grid-3 > div .form-control:disabled,
    html[data-theme="dark"] .profile-grid-2 > div .form-control:disabled,
    html[data-theme="dark"] .profile-info-row .form-control:disabled {
        background: linear-gradient(180deg, #111214 0%, #17171a 100%) !important;
        border-color: rgba(250, 204, 21, 0.16) !important;
        color: #f8fafc;
    }
    html[data-theme="dark"] .guisis-sync-banner {
        background: linear-gradient(135deg, rgba(112, 19, 27, 0.58), rgba(15, 23, 42, 0.92));
        border-color: rgba(250, 204, 21, 0.24);
        color: #fef3c7;
        box-shadow: 0 14px 26px rgba(0, 0, 0, 0.24);
    }
    html[data-theme="dark"] .guisis-sync-badge {
        background: #facc15;
        color: #111827;
    }
    html[data-theme="dark"] .guisis-pending-value {
        color: #fde68a !important;
    }
    html[data-theme="dark"] .profile-grid-3 > div .form-control:focus,
    html[data-theme="dark"] .profile-grid-2 > div .form-control:focus,
    html[data-theme="dark"] .profile-info-row .form-control:focus {
        background: linear-gradient(180deg, #111214 0%, #17171a 100%) !important;
        border-color: rgba(250, 204, 21, 0.28) !important;
        box-shadow:
            0 0 0 4px rgba(250, 204, 21, 0.08),
            0 14px 24px rgba(0, 0, 0, 0.24) !important;
    }

    /* --- NOTIFICATIONS --- */
    .alert-success { background: #dcfce7; color: #15803d; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: 600; border: 1px solid #bbf7d0; }
    .health-print-reminder {
        position: fixed;
        inset: 0;
        z-index: 1210;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(15, 23, 42, 0.66);
        backdrop-filter: blur(10px);
    }
    .health-print-reminder.is-open {
        display: flex;
        animation: overlayFadeIn 0.24s ease;
    }
    .health-print-reminder-card {
        width: min(480px, 100%);
        border: 1px solid rgba(127, 29, 45, 0.18);
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.28);
        padding: 28px 24px 24px;
        text-align: center;
    }
    .health-print-reminder-card h2 {
        margin: 0 0 12px;
        color: #111827;
        font-size: 1.35rem;
        font-weight: 800;
    }
    .health-print-reminder-card p {
        margin: 0;
        color: #374151;
        font-size: 0.95rem;
        line-height: 1.65;
    }
    .health-print-reminder-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 150px;
        margin-top: 22px;
        padding: 11px 22px;
        border: 1px solid #6f101c;
        border-radius: 8px;
        background: #800000;
        color: #ffffff;
        font-size: 0.9rem;
        font-weight: 800;
        text-decoration: none;
        transition: background-color 0.2s ease, color 0.2s ease;
    }
    .health-print-reminder-button:hover {
        background: #facc15;
        color: #111827;
    }
    @keyframes overlayFadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    .notif-item { display: flex; gap: 10px; align-items: flex-start; padding: 10px 0; border-bottom: 1px solid #f1f5f9; }
    .notif-item:last-child { border-bottom: none; }
    .notif-icon { font-size: 16px; }
    .notif-text { font-size: 13px; color: #334155; line-height: 1.4; }
    .notif-time { display: block; font-size: 11px; color: #94a3b8; margin-top: 4px; }
    .notif-panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 14px;
    }
    .notif-panel-title {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
        color: #600000;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .notif-panel-title svg {
        width: 18px;
        height: 18px;
        flex: 0 0 auto;
    }
    .notif-mark-btn {
        border: 1px solid #8f2230;
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #ffffff;
        border-radius: 999px;
        padding: 9px 14px;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        box-shadow:
            0 0 0 3px rgba(112, 19, 27, 0.12),
            0 10px 20px rgba(112, 19, 27, 0.18);
    }
    .notif-mark-btn:hover {
        transform: translateY(-1px);
        border-color: #facc15;
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.18),
            0 12px 22px rgba(112, 19, 27, 0.16);
    }
    .notif-mark-btn:disabled {
        cursor: not-allowed;
        opacity: 0.6;
        box-shadow: none;
    }
    .notif-list {
        display: grid;
        gap: 10px;
    }
    .notif-record {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        padding: 14px 16px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #ffffff;
        text-decoration: none;
        transition: transform 0.16s ease, border-color 0.16s ease, box-shadow 0.16s ease, background 0.16s ease;
    }
    .notif-record:hover {
        transform: translateY(-1px);
        border-color: #f5d0d0;
        box-shadow: 0 10px 20px rgba(112, 19, 27, 0.08);
    }
    .notif-record.is-unread {
        border-color: #f5d0d0;
        background: #fff7f7;
    }
    .notif-record-dot {
        width: 10px;
        height: 10px;
        margin-top: 6px;
        border-radius: 999px;
        background: #cbd5e1;
        flex: 0 0 auto;
    }
    .notif-record.is-unread .notif-record-dot {
        background: #8B0000;
    }
    .notif-record-content {
        flex: 1;
        min-width: 0;
    }
    .notif-record-message {
        display: block;
        font-size: 14px;
        line-height: 1.5;
        color: #1f2937;
        font-weight: 600;
    }
    .notif-record.is-unread .notif-record-message {
        font-weight: 800;
    }
    .notif-record-time {
        display: block;
        margin-top: 5px;
        font-size: 12px;
        color: #64748b;
    }
    .notif-empty {
        padding: 18px;
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
        color: #64748b;
        text-align: center;
        background: #ffffff;
    }
    html[data-theme="dark"] .notif-panel-title {
        color: #ffffff;
    }
    html[data-theme="dark"] .notif-mark-btn {
        background: linear-gradient(135deg, #70131B, #8f2230);
        border-color: #8f2230;
    }
    html[data-theme="dark"] .notif-record {
        background: rgba(15, 23, 42, 0.9);
        border-color: rgba(148, 163, 184, 0.24);
    }
    html[data-theme="dark"] .notif-record.is-unread {
        background: rgba(127, 29, 45, 0.20);
        border-color: rgba(248, 113, 113, 0.35);
    }
    html[data-theme="dark"] .notif-record-message {
        color: #f8fafc;
    }
    html[data-theme="dark"] .notif-record-time,
    html[data-theme="dark"] .notif-empty {
        color: #cbd5e1;
    }
    html[data-theme="dark"] .notif-empty {
        border-color: rgba(148, 163, 184, 0.3);
        background: rgba(15, 23, 42, 0.72);
    }

    /* --- BARCODE WIDGET --- */
    .barcode-status-card {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.03);
        border: 1px solid #e2e8f0;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }
    .barcode-status-card::before {
        content: "";
        position: absolute;
        top: 0; left: 0; width: 4px; height: 100%;
    }
    .barcode-status-card.linked::before { background: #10b981; }
    .barcode-status-card.not-linked::before { background: #f59e0b; }

    .barcode-icon-box { font-size: 24px; margin-bottom: 10px; }
    .barcode-label { font-size: 11px; text-transform: uppercase; font-weight: 800; color: #64748b; letter-spacing: 0.5px; }
    .barcode-value { font-size: 16px; font-weight: 700; color: #1e293b; display: block; margin: 4px 0; }
    .btn-barcode-action { display: inline-block; margin-top: 10px; font-size: 12px; font-weight: 700; text-decoration: none; color: #8B0000; }
    .btn-barcode-action:hover { text-decoration: underline; }

    @media (max-width: 900px) {
        .account-layout { grid-template-columns: 1fr; }
        .hero-stats { gap: 20px; flex-wrap: wrap; }
    }

    @media (max-width: 760px) {
        .page-hero {
            padding: 16px 16px;
            margin-bottom: 18px;
            margin-top: -8px;
        }
        .page-hero-icon {
            top: 4px;
            right: -10px;
            width: 118px;
            height: 118px;
        }
        .page-hero-step {
            width: 100%;
            justify-content: flex-start;
        }
        .profile-hero {
            padding: 24px 18px;
            gap: 18px;
        }
        .hero-name {
            font-size: 24px;
        }
        .profile-grid-3,
        .profile-grid-2 {
            grid-template-columns: 1fr;
        }
        .profile-sections-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }
        .profile-frame-equal {
            height: auto;
            min-height: 0;
        }
        .profile-card-head {
            flex-direction: column;
            align-items: stretch;
        }
        .profile-edit-btn {
            width: 100%;
        }
        #saveAction {
            flex-direction: column;
            align-items: stretch;
        }
        .profile-action-btn {
            width: 100%;
        }
        .profile-column-stack {
            gap: 12px;
        }
        .profile-grid-3 > div,
        .profile-grid-2 > div,
        .profile-info-row {
            grid-template-columns: 1fr;
            gap: 8px;
        }
        .profile-grid-3 > div .form-control,
        .profile-grid-2 > div .form-control,
        .profile-info-row .form-control {
            text-align: left;
        }
        .health-status-meta-grid,
        .record-modal-summary,
        .record-modal-grid {
            grid-template-columns: 1fr;
        }
        .health-status-steps {
            grid-template-columns: auto minmax(0, 1fr) auto;
            gap: 8px;
            align-items: stretch;
        }
        .health-step {
            justify-content: center;
            min-width: 0;
            width: 46px;
            padding: 8px 5px;
            gap: 0;
        }
        .health-step.is-active {
            justify-content: flex-start;
            width: auto;
            padding: 8px 10px;
            gap: 8px;
        }
        .health-step:not(.is-active) .health-step-label {
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
        .health-step-icon {
            width: 34px;
            height: 34px;
        }
        .health-step-icon svg {
            width: 18px;
            height: 18px;
        }
        .health-step.is-active .health-step-icon {
            width: 40px;
            height: 40px;
        }
        .health-step.is-active .health-step-icon svg {
            width: 20px;
            height: 20px;
        }
        .health-step.is-active .health-step-label {
            font-size: 11px;
            line-height: 1.15;
            letter-spacing: 0.02em;
            white-space: nowrap;
            overflow-wrap: normal;
            word-break: normal;
        }
        .record-modal-links {
            grid-template-columns: 1fr;
        }
    }

  
    /* --- HEALTH PROFILE STATUS WIDGET --- */
    .health-status-card {
        background: var(--card-bg, #fff);
        border-radius: 14px;
        padding: 26px;
        box-shadow: 0 8px 22px rgba(139, 0, 0, 0.1);
        border: 1px solid #fce7e7;
        margin-bottom: 24px;
        color: var(--text-main, #1e293b);
    }
    .health-status-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
    }
    .health-status-title {
        font-size: 18px;
        font-weight: 800;
        color: #8B0000;
        margin: 0;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        letter-spacing: 0.01em;
    }
    .health-status-title svg {
        width: 20px;
        height: 20px;
        flex: 0 0 auto;
    }
    .health-status-summary {
        margin-bottom: 12px;
    }
    .health-status-state {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.4px;
        text-transform: uppercase;
    }
    .health-status-state svg {
        width: 14px;
        height: 14px;
        flex: 0 0 auto;
    }
    .health-status-state.issued { background: #fbecef; color: #70131B; }
    .health-status-state.pending { background: #fffbeb; color: #92400e; }
    .health-status-state.incomplete { background: #fef2f2; color: #991b1b; }
    .health-status-message {
        font-size: 15px;
        color: var(--text-main, #1e293b);
        margin: 10px 0 0;
        line-height: 1.55;
    }
    .health-status-steps {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin: 14px 0 16px;
    }
    .health-step {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #f8fafc;
        padding: 10px;
        text-align: left;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .health-step.is-complete {
        border-color: rgba(139, 0, 0, 0.18);
        background: #fff3f5;
        color: #70131B;
    }
    .health-step.is-active {
        border-color: #fde68a;
        background: #fffbeb;
        color: #92400e;
    }
    .health-step-icon {
        width: 36px;
        height: 36px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        background: #fff1f2;
        border: 1px solid #fecdd3;
        color: #b91c1c;
        box-shadow: 0 8px 16px rgba(15, 23, 42, 0.06);
    }
    .health-step-icon svg {
        width: 20px;
        height: 20px;
        stroke-width: 2.5;
    }
    .health-step.is-complete .health-step-icon {
        background: #facc15;
        border-color: #facc15;
        color: #70131B;
    }
    .health-step.is-active .health-step-icon {
        background: #fff3cd;
        border-color: #f59e0b;
        color: #92400e;
    }
    .health-step-label {
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .health-status-sync {
        margin-top: 10px;
        padding: 12px 14px;
        border-radius: 10px;
        font-size: 13px;
        line-height: 1.5;
        text-align: left;
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }
    .health-status-sync svg {
        width: 16px;
        height: 16px;
        flex: 0 0 auto;
        margin-top: 1px;
    }
    .health-status-sync.syncing {
        background: #eff6ff;
        color: #1d4ed8;
    }
    .health-status-sync.synced {
        background: #fff3f5;
        color: #70131B;
    }
    .health-status-sync.failed,
    .health-status-sync.missing {
        background: #fef2f2;
        color: #991b1b;
    }
    .health-status-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
    }
    .health-status-link {
        font-size: 13px;
        color: #475569;
        text-decoration: none;
        text-align: center;
        padding: 11px 12px;
        border-radius: 8px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        flex: 1 1 220px;
    }
    .health-status-link svg {
        width: 15px;
        height: 15px;
        flex: 0 0 auto;
    }
    .health-status-note {
        font-size: 13px;
        color: var(--text-light, #64748b);
        margin-top: 12px;
        display: block;
    }
    .health-status-meta-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin-top: 14px;
    }
    .health-status-meta {
        padding: 12px 14px;
        border-radius: 12px;
        border: 1px solid rgba(112, 19, 27, 0.10);
        background: linear-gradient(180deg, #ffffff 0%, #fffaf7 100%);
        box-shadow:
            0 10px 18px rgba(15, 23, 42, 0.05),
            inset 0 1px 0 rgba(255,255,255,0.8);
    }
    .health-status-meta-label {
        display: block;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-weight: 800;
        color: #64748b;
        margin-bottom: 4px;
    }
    .health-status-meta-value {
        font-size: 14px;
        font-weight: 800;
        color: #111827;
        line-height: 1.45;
        word-break: break-word;
    }
    .btn-print-form {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 12px 14px;
        flex: 1 1 220px;
        background: #8B0000;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 700;
        font-size: 15px;
        transition: 0.3s;
    }
    .btn-print-form svg {
        width: 16px;
        height: 16px;
        flex: 0 0 auto;
    }
    .btn-print-form:hover {
        background: #facc15;
        box-shadow: 0 4px 12px rgba(139, 0, 0, 0.2);
        color: #70131B;
    }
    .btn-print-form.approved { background: #70131B; }
    .btn-print-form.approved:hover {
        background: #facc15;
        color: #70131B;
    }
    .btn-print-form.pending {
        position: relative;
        overflow: hidden;
        isolation: isolate;
        background: #8f2724;
        border: 0;
        cursor: pointer;
    }
    .btn-print-form.pending::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -1;
        background: #facc15;
        transform: translateX(-101%);
        transition: transform 0.38s ease;
    }
    .btn-print-form.pending:hover {
        background: #8f2724;
        color: #70131B;
    }
    .btn-print-form.pending:hover::before {
        transform: translateX(0);
    }
    .btn-print-form.incomplete { background: #800000; }
    .btn-print-form.disabled {
        background: #dd4b4b;
        cursor: not-allowed;
        font-size: 13px;
        opacity: 0.85;
    }
    html[data-theme="dark"] .health-status-card {
        border-color: var(--border);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.35);
    }
    html[data-theme="dark"] .health-status-title {
        color: #facc15;
    }
    html[data-theme="dark"] .health-step {
        background: rgba(17, 24, 39, 0.72);
        border-color: #374151;
        color: #cbd5e1;
    }
    html[data-theme="dark"] .health-step.is-complete {
        background: rgba(250, 204, 21, 0.12);
        border-color: rgba(250, 204, 21, 0.30);
        color: #fde68a;
    }
    html[data-theme="dark"] .health-step.is-active {
        background: rgba(146, 64, 14, 0.24);
        border-color: rgba(250, 204, 21, 0.42);
        color: #fde68a;
    }
    html[data-theme="dark"] .health-step-icon {
        background: rgba(127, 29, 45, 0.34);
        border-color: rgba(248, 113, 113, 0.24);
        color: #fca5a5;
        box-shadow: 0 10px 18px rgba(0, 0, 0, 0.22);
    }
    html[data-theme="dark"] .health-step.is-complete .health-step-icon {
        background: #facc15;
        border-color: #facc15;
        color: #111827;
    }
    html[data-theme="dark"] .health-status-state.issued {
        background: rgba(250, 204, 21, 0.16) !important;
        color: #fde68a !important;
    }
    html[data-theme="dark"] .health-status-sync.synced {
        background: rgba(250, 204, 21, 0.12) !important;
        color: #fde68a !important;
    }
    html[data-theme="dark"] .btn-print-form.approved {
        background: #facc15 !important;
        color: #111827 !important;
    }
    html[data-theme="dark"] .health-step.is-active .health-step-icon {
        background: rgba(146, 64, 14, 0.46);
        border-color: rgba(250, 204, 21, 0.42);
        color: #fde68a;
    }
    html[data-theme="dark"] .health-status-link {
        background: rgba(30, 41, 59, 0.75);
        border-color: #475569;
        color: #e2e8f0;
    }
    html[data-theme="dark"] .health-status-link,
    html[data-theme="dark"] .health-status-note {
        color: var(--text-light, #a9b4c4);
    }
    html[data-theme="dark"] .health-status-meta {
        background: rgba(17, 24, 39, 0.78);
        border-color: rgba(250, 204, 21, 0.18);
    }
    html[data-theme="dark"] .health-status-meta-label {
        color: #cbd5e1;
    }
    html[data-theme="dark"] .health-status-meta-value {
        color: #f8fafc;
    }
    html[data-theme="dark"] .record-modal-summary-card,
    html[data-theme="dark"] .record-modal-card {
        background: rgba(17, 24, 39, 0.82);
        border-color: rgba(250, 204, 21, 0.16);
    }
    html[data-theme="dark"] .record-modal-label {
        color: #cbd5e1;
    }
    html[data-theme="dark"] .record-modal-value {
        color: #f8fafc;
    }
    html[data-theme="dark"] .record-modal-empty {
        background: rgba(69, 26, 3, 0.32);
        border-color: rgba(250, 204, 21, 0.18);
        color: #fde68a;
    }
    @media (max-width: 760px) {
        .health-status-steps {
            display: grid;
            grid-template-columns: 40px minmax(156px, 1fr) 40px;
            gap: 8px;
            align-items: stretch;
        }
        .health-step {
            width: auto;
            min-width: 0;
            justify-content: center;
            padding: 7px 4px;
            gap: 0;
        }
        .health-step.is-active {
            justify-content: flex-start;
            padding: 7px 9px;
            gap: 7px;
        }
        .health-step:not(.is-active) .health-step-label {
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
        .health-step-icon {
            width: 30px;
            height: 30px;
        }
        .health-step-icon svg {
            width: 16px;
            height: 16px;
        }
        .health-step.is-active .health-step-icon {
            width: 36px;
            height: 36px;
        }
        .health-step.is-active .health-step-icon svg {
            width: 18px;
            height: 18px;
        }
        .health-step.is-active .health-step-label {
            min-width: 0;
            font-size: 11px;
            line-height: 1.15;
            letter-spacing: 0.02em;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    }
    .record-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        z-index: 1200;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .record-modal-overlay.is-open {
        display: flex;
    }
    .record-modal {
        width: min(860px, 100%);
        max-height: min(88vh, 900px);
        background: #ffffff;
        border-radius: 18px;
        border-left: 1px solid rgba(112, 19, 27, 0.12);
        border-right: 1px solid rgba(112, 19, 27, 0.12);
        border-top: 4px solid #facc15;
        border-bottom: 4px solid #facc15;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: auto;
        overflow-x: hidden;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .record-modal::-webkit-scrollbar {
        width: 0;
        height: 0;
    }
    .record-modal-head {
        position: sticky;
        top: 0;
        z-index: 3;
        padding: 24px 24px 18px;
        background: linear-gradient(135deg, #70131B, #8f2230);
    }
    .record-modal-title {
        margin: 0 0 8px;
        color: #ffffff;
        font-size: 24px;
        font-weight: 800;
    }
    .record-modal-subtitle {
        margin: 0;
        color: rgba(255, 255, 255, 0.88);
        font-size: 14px;
        line-height: 1.6;
        max-width: 640px;
    }
    .record-modal-close {
        position: absolute;
        top: 16px;
        right: 16px;
        width: 40px;
        height: 40px;
        min-width: 40px;
        min-height: 40px;
        padding: 0;
        flex: 0 0 40px;
        border-radius: 999px;
        border: 1px solid #8f2230;
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        overflow: hidden;
        box-shadow:
            0 0 0 3px rgba(112, 19, 27, 0.12),
            0 10px 22px rgba(112, 19, 27, 0.20);
        transition: color .08s linear, transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .record-modal-close svg {
        width: 18px;
        height: 18px;
        stroke-width: 2.2;
        flex: 0 0 auto;
        position: relative;
        z-index: 1;
    }
    .record-modal-close::after {
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
        pointer-events: none;
        z-index: 0;
    }
    .record-modal-close:hover {
        border-color: #facc15;
        transform: translateY(-1px);
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.18),
            0 14px 24px rgba(112, 19, 27, 0.16);
    }
    .record-modal-close:hover::after {
        transform: translateX(135%);
    }
    .record-modal-body {
        padding: 20px 24px 24px;
        position: relative;
    }
    .record-modal-summary {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 14px;
    }
    .record-modal-summary-card {
        border: 1px solid rgba(112, 19, 27, 0.10);
        border-radius: 14px;
        padding: 14px 16px;
        background: #ffffff;
        box-shadow:
            0 10px 18px rgba(15, 23, 42, 0.06),
            inset 0 1px 0 rgba(255,255,255,0.82);
    }
    .record-modal-summary-download {
        background: #facc15 !important;
        border-color: #facc15 !important;
        color: #111827 !important;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease, background .18s ease;
    }
    .record-modal-summary-download:hover {
        transform: translateY(-2px);
        border-color: #eab308 !important;
        background: #fbbf24 !important;
        box-shadow: 0 16px 26px rgba(112, 19, 27, 0.12);
        color: #111827 !important;
        text-decoration: none;
    }
    .record-modal-summary-download .record-modal-label,
    .record-modal-summary-download .record-modal-value {
        color: #111827 !important;
    }
    .record-modal-download-icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.42) !important;
        color: #111827 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        box-shadow: 0 10px 18px rgba(250, 204, 21, 0.18);
    }
    .record-modal-download-icon svg {
        width: 20px;
        height: 20px;
        stroke-width: 2.4;
        color: #111827 !important;
        stroke: #111827 !important;
    }
    .record-modal-body-fade {
        display: none;
    }
    .record-modal-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 14px;
    }
    .record-modal-card {
        border: 1px solid rgba(112, 19, 27, 0.10);
        border-radius: 14px;
        padding: 14px 16px;
        background: #ffffff;
        box-shadow:
            0 10px 18px rgba(15, 23, 42, 0.06),
            inset 0 1px 0 rgba(255,255,255,0.82);
    }
    .record-modal-card.is-full {
        grid-column: 1 / -1;
    }
    .record-modal-label {
        display: block;
        font-size: 12px;
        color: #64748b;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-weight: 800;
    }
    .record-modal-value {
        color: #111827;
        font-size: 15px;
        font-weight: 700;
        line-height: 1.55;
        word-break: break-word;
    }
    .record-modal-empty {
        padding: 14px 16px;
        border-radius: 14px;
        border: 1px dashed rgba(112, 19, 27, 0.18);
        background: rgba(255, 251, 247, 0.84);
        color: #7c2d12;
        font-size: 14px;
        font-weight: 700;
    }
    .record-modal-status {
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
    .record-modal-links {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }
    .record-modal-link {
        min-height: 108px;
        padding: 14px 14px 12px;
        border-radius: 16px;
        border: 1px solid rgba(139, 0, 0, 0.14);
        background: linear-gradient(180deg, #ffffff 0%, #fff9f7 100%);
        color: #8B0000;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        box-shadow: 0 12px 22px rgba(15, 23, 42, 0.05);
        transition: all 0.18s ease;
    }
    .record-modal-link:hover {
        transform: translateY(-2px);
        background: linear-gradient(135deg, #8B0000, #70131B);
        color: #facc15;
        border-color: #8B0000;
        box-shadow: 0 16px 28px rgba(139, 0, 0, 0.16);
        text-decoration: none;
    }
    .record-modal-link-top {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: rgba(139, 0, 0, 0.08);
        color: inherit;
        flex: 0 0 auto;
    }
    .record-modal-link-top svg {
        width: 18px;
        height: 18px;
        flex: 0 0 auto;
    }
    .record-modal-photo-thumb {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        object-fit: cover;
        display: block;
        border: 1px solid rgba(139, 0, 0, 0.12);
        box-shadow: 0 6px 14px rgba(15, 23, 42, 0.08);
    }
    .record-modal-link-body {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .record-modal-link-title {
        font-size: 14px;
        font-weight: 800;
        color: inherit;
        line-height: 1.35;
    }
    .record-modal-link-meta {
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        opacity: 0.72;
        color: inherit;
    }
    .record-modal-link-arrow {
        font-size: 12px;
        font-weight: 800;
        color: inherit;
        opacity: 0.88;
    }
    .record-document-card {
        min-height: 116px;
        padding: 12px;
        border-radius: 16px;
        border: 1px solid rgba(139, 0, 0, 0.14);
        background: linear-gradient(180deg, #ffffff 0%, #fff9f7 100%);
        display: grid;
        grid-template-columns: 58px minmax(0, 1fr);
        gap: 12px;
        align-items: center;
        box-shadow: 0 12px 22px rgba(15, 23, 42, 0.05);
    }
    .record-document-preview {
        width: 58px;
        height: 58px;
        border-radius: 14px;
        border: 1px solid rgba(139, 0, 0, 0.14);
        background:
            linear-gradient(135deg, rgba(139, 0, 0, 0.10), rgba(250, 204, 21, 0.16)),
            #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        color: #8B0000;
        box-shadow: 0 8px 16px rgba(15, 23, 42, 0.08);
    }
    .record-document-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .record-document-preview svg {
        width: 25px;
        height: 25px;
    }
    .record-document-body {
        min-width: 0;
    }
    .record-document-title {
        display: block;
        color: #70131B;
        font-size: 14px;
        line-height: 1.3;
        font-weight: 850;
        margin-bottom: 4px;
    }
    .record-document-meta {
        display: block;
        color: #64748b;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-bottom: 10px;
    }
    .record-document-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .record-document-btn {
        border: 1px solid rgba(139, 0, 0, 0.16);
        border-radius: 999px;
        background: #ffffff;
        color: #70131B;
        padding: 7px 12px;
        font-size: 12px;
        font-weight: 850;
        line-height: 1;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.18s ease;
    }
    .record-document-btn:hover {
        background: linear-gradient(135deg, #8B0000, #70131B);
        color: #facc15;
        border-color: transparent;
        text-decoration: none;
        transform: translateY(-1px);
    }
    html[data-theme="dark"] .record-modal {
        background: linear-gradient(180deg, #0f0f10 0%, #161618 100%) !important;
        border-color: rgba(250, 204, 21, 0.16) !important;
        box-shadow:
            0 18px 36px rgba(0, 0, 0, 0.42),
            0 0 0 1px rgba(250, 204, 21, 0.05) inset !important;
    }
    html[data-theme="dark"] .record-modal-card {
        background: #111214 !important;
        border-color: rgba(250, 204, 21, 0.14) !important;
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.24) !important;
    }
    html[data-theme="dark"] .record-modal-summary-download {
        background: #facc15 !important;
        border-color: #facc15 !important;
        color: #111827 !important;
    }
    html[data-theme="dark"] .record-modal-summary-download:hover {
        background: #fbbf24 !important;
        border-color: #eab308 !important;
    }
    html[data-theme="dark"] .record-modal-label,
    html[data-theme="dark"] .record-modal-value {
        color: #f8fafc !important;
    }
    html[data-theme="dark"] .record-modal-close {
        background: linear-gradient(135deg, #70131B, #8f2230) !important;
        border-color: #8f2230 !important;
        color: #f8fafc !important;
        box-shadow:
            0 0 0 3px rgba(112, 19, 27, 0.18),
            0 10px 22px rgba(0, 0, 0, 0.28) !important;
    }
    html[data-theme="dark"] .record-modal-status {
        background: rgba(250, 204, 21, 0.16) !important;
        color: #fef3c7 !important;
        border-color: rgba(250, 204, 21, 0.24) !important;
    }
    html[data-theme="dark"] .record-modal-link {
        background: #17171a !important;
        color: #ffffff !important;
        border-color: rgba(250, 204, 21, 0.14) !important;
    }
    html[data-theme="dark"] .record-modal-link:hover {
        background: #8B0000 !important;
        color: #facc15 !important;
        border-color: #8B0000 !important;
    }
    html[data-theme="dark"] .record-modal-link-top {
        background: rgba(250, 204, 21, 0.10) !important;
    }
    html[data-theme="dark"] .record-modal-photo-thumb {
        border-color: rgba(250, 204, 21, 0.16) !important;
    }
    html[data-theme="dark"] .record-document-card {
        background: #111214 !important;
        border-color: rgba(250, 204, 21, 0.14) !important;
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.24) !important;
    }
    html[data-theme="dark"] .record-document-title {
        color: #f8fafc !important;
    }
    html[data-theme="dark"] .record-document-meta {
        color: #cbd5e1 !important;
    }
    html[data-theme="dark"] .record-document-preview,
    html[data-theme="dark"] .record-document-btn {
        background: #161618 !important;
        border-color: rgba(250, 204, 21, 0.16) !important;
        color: #fef3c7 !important;
    }
    @media (max-width: 760px) {
        .record-modal-grid {
            grid-template-columns: 1fr;
        }
        .record-modal-links {
            grid-template-columns: 1fr;
        }
        .record-document-card {
            grid-template-columns: 52px minmax(0, 1fr);
        }
        .record-document-preview {
            width: 52px;
            height: 52px;
        }
    }
</style>
@endpush

@section('content')
@php
    $linkedAccessLevel = strtolower(trim((string) (
        optional($linkedAdminProfile)->access_level
        ?? optional($linkedAdminProfile)->admin_hub_role
        ?? ''
    )));
    $linkedRoleLabel = in_array($linkedAccessLevel, ['clinic_staff', 'designee', 'admin_designee', 'superadmin', 'super_admin'], true)
        ? (str_contains($linkedAccessLevel, 'faculty') ? 'Faculty' : 'Admin')
        : null;
    $accountProfileData = $accountProfileData ?? [];
    $guisisAccountData = $guisisAccountData ?? ['available' => false, 'status' => 'not_checked'];
    $isEnrolled = (bool) ($isEnrolled ?? false);
    $accountView = in_array(($accountView ?? 'profile'), ['profile', 'health-record', 'notifications'], true) ? $accountView : 'profile';
    $showOfficeField = in_array($linkedAccessLevel, ['clinic_staff', 'designee', 'admin_designee', 'superadmin', 'super_admin', 'faculty'], true) || str_contains($linkedAccessLevel, 'faculty');
    $hasGuisisAccountData = (bool) ($guisisAccountData['available'] ?? false);
    $displayStudentNumber = trim((string) ($accountProfileData['student_number'] ?? ''));
    if ($displayStudentNumber === '') {
        $displayStudentNumber = trim((string) ($accountProfileData['reference_number'] ?? ''));
    }
    $referenceMode = trim((string) ($accountProfileData['reference_mode'] ?? 'admission'));
    $referenceHeading = $referenceMode === 'admission' ? 'Admission Reference' : 'Clinic Reference';
    $idNumberHeading = $referenceMode === 'admission' ? 'Student Number' : 'ID Number';
    $displayCourse = trim((string) ($accountProfileData['course_college'] ?? ''));
    $displayFullName = trim((string) ($accountProfileData['full_name'] ?? ''));
    $displayFullName = $displayFullName !== '' ? $displayFullName : ($hasGuisisAccountData ? 'Available once enrolled' : ($user->name ?? 'Student'));
    $localMiddleName = trim((string) ($accountProfileData['middle_name'] ?? $user->middle_name ?? optional($linkedAdminProfile)->middle_name ?? ''));
    $localMiddleName = $localMiddleName !== '' ? $localMiddleName : 'N/A';
    $localSuffixName = trim((string) ($accountProfileData['suffix_name'] ?? optional($linkedAdminProfile)->suffix_name ?? ''));
    $localSuffixName = $localSuffixName !== '' ? $localSuffixName : 'N/A';
    $guisisPendingText = 'Available once enrolled';
    $guisisValue = fn ($value) => trim((string) $value) !== '' ? trim((string) $value) : $guisisPendingText;
    $guisisPendingClass = fn ($value) => trim((string) $value) === '' ? ' guisis-pending-value' : '';
    $heightRaw = old('height', $accountProfileData['height'] ?? $user->height ?? '');
    $weightRaw = old('weight', $accountProfileData['weight'] ?? $user->weight ?? '');
    preg_match('/\d+(?:\.\d+)?/', (string) $heightRaw, $heightMatch);
    preg_match('/\d+(?:\.\d+)?/', (string) $weightRaw, $weightMatch);
    $heightDisplay = $heightMatch[0] ?? trim((string) $heightRaw);
    $weightDisplay = $weightMatch[0] ?? trim((string) $weightRaw);
@endphp
<div class="container" style="padding: 0 20px 40px;">

    @if(session('show_health_print_reminder'))
        <div class="health-print-reminder" id="healthPrintReminder" aria-hidden="true">
            <section class="health-print-reminder-card" role="dialog" aria-modal="true" aria-labelledby="healthPrintReminderTitle">
                <h2 id="healthPrintReminderTitle">Print / Download your Health Form</h2>
                <p>
                    Please print your Health Form before proceeding to the Medical Clinic to submit the physical copy.
                    Do not forget to bring a hard copy of your 2x2 photo.
                </p>
                <a
                    id="healthPrintReminderOpen"
                    class="health-print-reminder-button"
                    href="{{ route('student.health_form.print') }}"
                    target="_blank"
                    rel="noopener"
                >Open Health Form</a>
            </section>
        </div>
    @endif

    @if(session('success') && !session('health_profile_submitted'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    @if($errors->any())
        <div style="background:#fee2e2; color:#b91c1c; padding:12px; border-radius:8px; margin-bottom:20px; text-align:center; font-size:14px; border:1px solid #fecaca;">
            {{ $errors->first() }}
        </div>
    @endif

    @if($accountView === 'profile')
    <div class="profile-hero">
        <div class="hero-avatar">
            @php
                $healthProfile = \App\Models\HealthProfile::where('user_id', $user->id)->first();
            @endphp
            @if(!empty($healthProfile?->student_photo))
                <img src="{{ route('student.health_record.document', ['document' => 'student_photo']) }}" alt="Student 2x2 Picture">
            @else
                {{ strtoupper(substr($displayFullName, 0, 1)) }}
            @endif
        </div>
        <div class="hero-info">
            <h1 class="hero-name">{{ $displayFullName }} <span class="hero-badge">Active</span></h1>
            @if($isEnrolled)
                <div class="hero-course" @if($linkedRoleLabel) style="display: none;" @endif>
                    {{ $guisisValue($displayStudentNumber) }} &bull; {{ $guisisValue($displayCourse) }}
                </div>
                @if($linkedRoleLabel)
                    <div class="hero-course">
                        {{ $guisisValue($displayStudentNumber) }} - {{ $linkedRoleLabel }}
                    </div>
                @endif
            @else
                <div class="hero-course">Available once enrolled</div>
            @endif

            <div class="hero-stats">
                <div class="stat-item">
                    <span class="stat-val">{{ $pendingCount ?? 0 }}</span>
                    <span class="stat-label">Pending</span>
                </div>
                <div class="stat-item">
                    <span class="stat-val">{{ $approvedCount ?? 0 }}</span>
                    <span class="stat-label">Approved</span>
                </div>
                <div class="stat-item">
                    <span class="stat-val">{{ $completedCount ?? 0 }}</span>
                    <span class="stat-label">Completed</span>
                </div>
                <div class="stat-item">
                    <span class="stat-val">{{ $cancelledCount ?? 0 }}</span>
                    <span class="stat-label">Cancelled</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="account-layout">
        @if(session('health_profile_submitted'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    const printReminder = document.getElementById('healthPrintReminder');
    if (!printReminder) return;
    printReminder.classList.add('is-open');
    printReminder.setAttribute('aria-hidden', 'false');

    document.getElementById('healthPrintReminderOpen')?.addEventListener('click', function () {
        window.setTimeout(function () {
            printReminder.classList.remove('is-open');
            printReminder.setAttribute('aria-hidden', 'true');
        }, 120);
    });
});
</script>
@endif

@if($accountView === 'profile')
{{-- Full Profile Widget --}}
            <div class="widget-card">
    <div class="profile-card-head">
        <div class="profile-card-heading">
            <h1 class="profile-card-title">Personal Information</h1>
            <p class="profile-card-description">Review your personal account details and keep your clinic information up to date.</p>
        </div>
        @if($isEnrolled)
            <button type="button" id="editBtn" class="profile-edit-btn" onclick="enableEditing()">Edit Profile</button>
        @endif
    </div>

    <form action="{{ route('student.updateContact') }}" method="POST">
        @csrf
        @if(!empty($linkedAdminProfile))
            <input type="hidden" name="admin_profile_id" value="{{ $linkedAdminProfile->admin_id }}">
        @endif
        
        @if(!$isEnrolled)
            <div class="profile-enrollment-empty">
                <div class="profile-enrollment-empty-head">
                    <div class="profile-enrollment-empty-icon" aria-hidden="true">
                        <x-outline-icon name="lock-closed" />
                    </div>
                    <div>
                        <h3 class="profile-enrollment-empty-title">Student information is locked</h3>
                        <p class="profile-enrollment-empty-copy">
                            These fields will appear once your enrollment record is available in the system.
                        </p>
                    </div>
                </div>
                <div class="profile-enrollment-empty-note">Available once enrolled</div>
            </div>
        @elseif(empty($linkedAdminProfile))
            <div class="profile-sections-grid">
                <div class="profile-column-stack">
                    <section class="profile-form-section accent-maroon profile-frame-equal">
                        <h3 class="profile-form-section-title"><x-outline-icon name="document-text" />Academic Information</h3>
                        <div class="profile-grid-3">
                            <div>
                                <label class="input-label">{{ $idNumberHeading }}</label>
                                <div class="form-control profile-static-field{{ $guisisPendingClass($displayStudentNumber) }}">{{ $guisisValue($displayStudentNumber) }}</div>
                            </div>
                            <div>
                                <label class="input-label">Course</label>
                                <div class="form-control profile-course-field profile-static-field{{ $guisisPendingClass($displayCourse) }}">{{ $guisisValue($displayCourse) }}</div>
                            </div>
                            <div>
                                <label class="input-label">Year</label>
                                <input type="text" name="year" class="form-control{{ $guisisPendingClass($accountProfileData['year'] ?? $user->year) }}" value="{{ $guisisValue($accountProfileData['year'] ?? $user->year) }}" readonly>
                            </div>
                            <div>
                                <label class="input-label">Section</label>
                                <input type="text" name="section" class="form-control{{ $guisisPendingClass($accountProfileData['section'] ?? $user->section) }}" value="{{ $guisisValue($accountProfileData['section'] ?? $user->section) }}" readonly>
                            </div>
                        </div>
                    </section>

                    <section class="profile-form-section accent-gold">
                        <h3 class="profile-form-section-title"><x-outline-icon name="information-circle" />Personal Details</h3>
                        <div class="profile-grid-3">
                            <div>
                                <label class="input-label">First Name</label>
                                <input type="text" class="form-control{{ $guisisPendingClass($accountProfileData['first_name'] ?? '') }}" value="{{ $guisisValue($accountProfileData['first_name'] ?? '') }}" readonly>
                            </div>
                            <div>
                                <label class="input-label">Middle Name</label>
                                <input type="text" class="form-control" value="{{ $localMiddleName }}" readonly>
                            </div>
                            <div>
                                <label class="input-label">Last Name</label>
                                <input type="text" class="form-control{{ $guisisPendingClass($accountProfileData['last_name'] ?? '') }}" value="{{ $guisisValue($accountProfileData['last_name'] ?? '') }}" readonly>
                            </div>
                        </div>
                        <div class="profile-grid-2">
                            <div>
                                <label class="input-label">Gender</label>
                                <input type="text" class="form-control{{ $guisisPendingClass($accountProfileData['sex'] ?? '') }}" value="{{ $guisisValue($accountProfileData['sex'] ?? '') }}" readonly style="background-color: #f8fafc;">
                            </div>
                            <div>
                                <label class="input-label">Birthday (DOB)</label>
                                <input type="text" class="form-control{{ $guisisPendingClass($accountProfileData['birthday'] ?? '') }}" value="{{ $guisisValue($accountProfileData['birthday'] ?? '') }}" readonly style="background-color: #f8fafc;">
                            </div>
                        </div>
                        <div class="profile-grid-2">
                            <div>
                                <label class="input-label">Age</label>
                                <input type="text" class="form-control{{ $guisisPendingClass($accountProfileData['age'] ?? '') }}" value="{{ $guisisValue($accountProfileData['age'] ?? '') }}" readonly>
                            </div>
                            <div>
                                <label class="input-label">Civil Status</label>
                                <input type="text" class="form-control{{ $guisisPendingClass($accountProfileData['civil_status'] ?? '') }}" value="{{ $guisisValue($accountProfileData['civil_status'] ?? '') }}" readonly>
                            </div>
                        </div>
                        <div class="profile-grid-2">
                            <div>
                                <label class="input-label">Height (cm)</label>
                                <div class="metric-field">
                                    <input type="text" name="height" class="form-control editable-input" inputmode="decimal" value="{{ $heightDisplay }}" disabled>
                                </div>
                            </div>
                            <div>
                                <label class="input-label">Weight (kg)</label>
                                <div class="metric-field">
                                    <input type="text" name="weight" class="form-control editable-input" inputmode="decimal" value="{{ $weightDisplay }}" disabled>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="profile-column-stack">
                    <section class="profile-form-section accent-maroon profile-frame-equal">
                        <h3 class="profile-form-section-title"><x-outline-icon name="clock" />Contact Information</h3>
                        <div class="profile-info-row">
                            <label class="input-label">Email Address</label>
                            <input type="email" class="form-control{{ $guisisPendingClass($accountProfileData['email'] ?? '') }}" value="{{ $guisisValue($accountProfileData['email'] ?? '') }}" readonly>
                        </div>
                        <div class="profile-info-row">
                            <label class="input-label">Contact Number</label>
                            <input type="text" name="contact_no" class="form-control{{ $guisisPendingClass($accountProfileData['contact_number'] ?? '') }}" value="{{ $guisisValue($accountProfileData['contact_number'] ?? '') }}" readonly>
                        </div>
                        <div class="profile-info-row">
                            <label class="input-label">Address</label>
                            <textarea class="form-control{{ $guisisPendingClass(old('home_address', $accountProfileData['home_address'] ?? '')) }}" rows="2" placeholder="{{ $guisisPendingText }}" disabled>{{ old('home_address', $accountProfileData['home_address'] ?? '') }}</textarea>
                        </div>
                    </section>

                    <section class="profile-form-section accent-gold">
                        <h3 class="profile-form-section-title"><x-outline-icon name="exclamation-triangle" />Emergency Contact</h3>
                        <div class="profile-grid-2">
                            <div class="soft-field">
                                <label class="input-label">Emergency Contact Person</label>
                                <input type="text" name="guardian_name" class="form-control{{ $guisisPendingClass(old('guardian_name', $accountProfileData['guardian_name'] ?? '')) }}" value="{{ old('guardian_name', $accountProfileData['guardian_name'] ?? '') }}" placeholder="{{ $guisisPendingText }}" disabled>
                            </div>
                            <div class="soft-field">
                                <label class="input-label">Emergency Contact Number</label>
                                <input type="text" name="cellphone" class="form-control{{ $guisisPendingClass(old('cellphone', $accountProfileData['cellphone'] ?? '')) }}" value="{{ old('cellphone', $accountProfileData['cellphone'] ?? '') }}" placeholder="{{ $guisisPendingText }}" disabled>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        @endif

        @if($isEnrolled && !empty($linkedAdminProfile))
            <div class="profile-sections-grid">
            <section class="profile-form-section accent-maroon">
                <h3 class="profile-form-section-title"><x-outline-icon name="information-circle" />Personal Information</h3>
                <div class="profile-info-row">
                    <label class="input-label">{{ $idNumberHeading }}</label>
                    <div class="form-control profile-static-field{{ $guisisPendingClass($displayStudentNumber) }}">{{ $guisisValue($displayStudentNumber) }}</div>
                </div>
                <div class="profile-grid-2">
                    <div>
                        <label class="input-label">First Name</label>
                        <input type="text" name="first_name" class="form-control{{ $guisisPendingClass(old('first_name', $accountProfileData['first_name'] ?? $linkedAdminProfile->first_name)) }}" value="{{ old('first_name', $accountProfileData['first_name'] ?? $linkedAdminProfile->first_name) }}" placeholder="{{ $guisisPendingText }}" disabled>
                    </div>
                    <div>
                        <label class="input-label">Middle Name</label>
                        <input type="text" name="middle_name" class="form-control" value="{{ $localMiddleName }}" disabled>
                    </div>
                </div>

                <div class="profile-grid-2">
                    <div>
                        <label class="input-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control{{ $guisisPendingClass(old('last_name', $accountProfileData['last_name'] ?? $linkedAdminProfile->last_name)) }}" value="{{ old('last_name', $accountProfileData['last_name'] ?? $linkedAdminProfile->last_name) }}" placeholder="{{ $guisisPendingText }}" disabled>
                    </div>
                    <div>
                        <label class="input-label">Suffix Name</label>
                        <input type="text" name="suffix_name" class="form-control" value="{{ $localSuffixName }}" disabled>
                    </div>
                </div>

                <div class="profile-grid-2">
                    <div>
                        <label class="input-label">Birthday</label>
                        <input type="text" name="birthday" class="form-control{{ $guisisPendingClass(old('birthday', $accountProfileData['birthday'] ?? $linkedAdminProfile->birthday)) }}" value="{{ old('birthday', $accountProfileData['birthday'] ?? $linkedAdminProfile->birthday) }}" placeholder="{{ $guisisPendingText }}" disabled>
                    </div>
                    <div>
                        <label class="input-label">Gender</label>
                        <input type="text" name="gender" class="form-control{{ $guisisPendingClass(old('gender', $accountProfileData['sex'] ?? $linkedAdminProfile->gender)) }}" value="{{ old('gender', $accountProfileData['sex'] ?? $linkedAdminProfile->gender) }}" placeholder="{{ $guisisPendingText }}" disabled>
                    </div>
                </div>

                <div class="profile-grid-2">
                    <div>
                        <label class="input-label">Age</label>
                        <input type="text" name="age" class="form-control{{ $guisisPendingClass(old('age', $accountProfileData['age'] ?? $linkedAdminProfile->age)) }}" value="{{ old('age', $accountProfileData['age'] ?? $linkedAdminProfile->age) }}" placeholder="{{ $guisisPendingText }}" disabled>
                    </div>
                    <div>
                        <label class="input-label">Civil Status</label>
                        <input type="text" name="civil_status" class="form-control{{ $guisisPendingClass(old('civil_status', $accountProfileData['civil_status'] ?? $linkedAdminProfile->civil_status)) }}" value="{{ old('civil_status', $accountProfileData['civil_status'] ?? $linkedAdminProfile->civil_status) }}" placeholder="{{ $guisisPendingText }}" disabled>
                    </div>
                </div>

                <div class="profile-grid-2">
                    <div>
                        <label class="input-label">Height (cm)</label>
                        <div class="metric-field">
                            <input type="text" name="height" class="form-control editable-input" inputmode="decimal" value="{{ $heightDisplay }}" disabled>
                        </div>
                    </div>
                    <div>
                        <label class="input-label">Weight (kg)</label>
                        <div class="metric-field">
                            <input type="text" name="weight" class="form-control editable-input" inputmode="decimal" value="{{ $weightDisplay }}" disabled>
                        </div>
                    </div>
                </div>
            </section>

            <section class="profile-form-section accent-gold">
                <h3 class="profile-form-section-title"><x-outline-icon name="clock" />Contact Information</h3>
                <div class="profile-grid-2">
                    <div>
                        <label class="input-label">Email</label>
                        <input type="email" name="email" class="form-control{{ $guisisPendingClass(old('email', $accountProfileData['email'] ?? $linkedAdminProfile->email)) }}" value="{{ old('email', $accountProfileData['email'] ?? $linkedAdminProfile->email) }}" placeholder="{{ $guisisPendingText }}" disabled>
                    </div>
                    <div>
                        <label class="input-label">Contact Number</label>
                        <input type="text" name="contact_no" class="form-control{{ $guisisPendingClass(old('contact_no', $accountProfileData['contact_number'] ?? $user->contact_no)) }}" value="{{ $guisisValue(old('contact_no', $accountProfileData['contact_number'] ?? $user->contact_no)) }}" disabled>
                    </div>
                </div>
                <div class="profile-info-row">
                    <label class="input-label">Address</label>
                    <textarea name="address" class="form-control{{ $guisisPendingClass(old('address', $accountProfileData['home_address'] ?? $linkedAdminProfile->address)) }}" rows="2" placeholder="{{ $guisisPendingText }}" disabled>{{ old('address', $accountProfileData['home_address'] ?? $linkedAdminProfile->address) }}</textarea>
                </div>

                <div class="profile-grid-2">
                    <div class="soft-field">
                        <label class="input-label">Emergency Contact Person</label>
                        <input type="text" name="emergency_contact_person" class="form-control{{ $guisisPendingClass(old('emergency_contact_person', $accountProfileData['guardian_name'] ?? $linkedAdminProfile->emergency_contact_person)) }}" value="{{ old('emergency_contact_person', $accountProfileData['guardian_name'] ?? $linkedAdminProfile->emergency_contact_person) }}" placeholder="{{ $guisisPendingText }}" disabled>
                    </div>
                    <div class="soft-field">
                        <label class="input-label">Emergency Contact Number</label>
                        <input type="text" name="emergency_contact_no" class="form-control{{ $guisisPendingClass(old('emergency_contact_no', $accountProfileData['cellphone'] ?? $linkedAdminProfile->emergency_contact_no)) }}" value="{{ old('emergency_contact_no', $accountProfileData['cellphone'] ?? $linkedAdminProfile->emergency_contact_no) }}" placeholder="{{ $guisisPendingText }}" disabled>
                    </div>
                </div>

                @if($showOfficeField)
                <div class="profile-grid-2">
                    <div>
                        <label class="input-label">Office</label>
                        <input type="text" name="office" class="form-control{{ $guisisPendingClass(old('office', $accountProfileData['office'] ?? $linkedAdminProfile->office)) }}" value="{{ old('office', $accountProfileData['office'] ?? $linkedAdminProfile->office) }}" placeholder="{{ $guisisPendingText }}" disabled>
                    </div>
                </div>
                @endif
            </section>
            </div>
        @endif

        @if($isEnrolled)
            <div id="profileActionBar">
                <div id="saveAction">
                    <button type="submit" class="profile-action-btn save">
                        Save Changes
                    </button>
                    <button type="button" class="profile-action-btn cancel" onclick="window.location.reload()">
                        Cancel
                    </button>
                </div>
            </div>
        @endif
    </form>
</div>
@elseif($accountView === 'health-record')
    @php
        $healthProfileRecord = $user->healthProfile;
        $healthFormSubmitted = $hasSubmittedHealthProfile ?? ($user->healthProfile !== null);
        $status = $user->healthProfile->clearance_status ?? 'For Verification';
        $statusNormalized = strtolower(trim((string) $status));
        $isIssuedStatus = $statusNormalized === 'issued';
        $isRejectedStatus = $statusNormalized === 'rejected';
        $isConditionalStatus = str_contains($statusNormalized, 'pending') || str_contains($statusNormalized, 'conditional');
        $isPendingStatus = !$isIssuedStatus && !$isRejectedStatus;
        $recordPendingReason = trim((string) optional($user->healthProfile)->pending_reason);
        $recordSubmissionStatus = $isConditionalStatus ? 'Pending Compliance' : 'Waiting for clinic review';
        $recordStatusMessage = $isConditionalStatus
            ? 'Your health profile needs follow-up before it can be issued. Please check the pending reason and coordinate with the Medical Clinic.'
            : 'Your health profile has been submitted. Please proceed to the Medical Clinic on your designated schedule for medical review.';
        $recordStatusNote = $isConditionalStatus
            ? 'Please complete the pending requirement before your record can be marked as issued.'
            : 'Clinic approval is required before your record can be marked as issued.';
        $puptasSyncStatus = optional($user->healthProfile)->puptas_sync_status;
        $puptasSyncMessage = trim((string) optional($user->healthProfile)->puptas_sync_message);
        $puptasSyncedAt = optional(optional($user->healthProfile)->puptas_synced_at)->format('M d, Y g:i A');
        $recordVerifiedAt = optional(optional($user->healthProfile)->verified_at)->format('M d, Y g:i A');
        $recordReferenceNumber = trim((string) optional($healthProfileRecord)->reference_number);
        $recordReferenceNumber = $recordReferenceNumber !== '' ? $recordReferenceNumber : trim((string) (optional($healthProfileRecord)->student_number ?: ($accountProfileData['student_number'] ?? $user->student_number ?? $user->student_id ?? '-')));
        $recordBirthday = trim((string) optional($healthProfileRecord)->birthday);
        $recordBirthday = $recordBirthday !== '' ? optional(\Carbon\Carbon::parse($recordBirthday))->format('M d, Y') : '-';
        $recordAssessmentDate = optional(optional($healthProfileRecord)->assessment_date)->format('M d, Y');
        $recordChestXrayDate = optional(optional($healthProfileRecord)->xray_date)->format('M d, Y');
        $recordMedicalIssuedAt = optional(optional($healthProfileRecord)->med_cert_date)->format('M d, Y');
        $recordCourseValue = trim((string) (optional($healthProfileRecord)->course_college ?: ($accountProfileData['course_college'] ?? $user->course ?? '')));
        $recordCourseLabel = strcasecmp($recordCourseValue, 'Faculty / Staff') === 0 ? 'Classification' : 'Course';
    @endphp
    <div class="page-hero">
        <div class="page-hero-icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
            </svg>
        </div>
        <div class="page-hero-kicker">Health Record</div>
        <h1 class="page-hero-title">Record Review</h1>
        <p class="page-hero-text">Track your submitted profile, clinic approval, and uploaded digital copies in one place.</p>
        <div class="page-hero-steps">
            <div class="page-hero-step">
                <span>Submitted</span>
            </div>
            <div class="page-hero-step">
                <span>Under Review</span>
            </div>
            <div class="page-hero-step">
                <span>Issued</span>
            </div>
        </div>
    </div>
    <div class="health-status-card">
        <div class="health-status-head">
            <span class="health-status-title">
                <x-outline-icon name="clipboard-document-list" />
                Record Summary
            </span>
        </div>

        <div class="health-status-steps">
            <div class="health-step {{ $healthFormSubmitted ? 'is-complete' : '' }}">
                <span class="health-step-icon">
                    @if($healthFormSubmitted)
                        <x-outline-icon name="check" />
                    @else
                        <x-outline-icon name="x-mark" />
                    @endif
                </span>
                <div class="health-step-label">Submitted</div>
            </div>
            <div class="health-step {{ $healthFormSubmitted ? ($isIssuedStatus ? 'is-complete' : 'is-active') : '' }}">
                <span class="health-step-icon">
                    @if($isIssuedStatus)
                        <x-outline-icon name="check" />
                    @elseif($healthFormSubmitted)
                        <x-outline-icon name="clock" />
                    @else
                        <x-outline-icon name="x-mark" />
                    @endif
                </span>
                <div class="health-step-label">Verification</div>
            </div>
            <div class="health-step {{ $isIssuedStatus ? 'is-complete' : '' }}">
                <span class="health-step-icon">
                    @if($isIssuedStatus)
                        <x-outline-icon name="check" />
                    @else
                        <x-outline-icon name="x-mark" />
                    @endif
                </span>
                <div class="health-step-label">Issued</div>
            </div>
        </div>

        @if($healthFormSubmitted)
            @if($isIssuedStatus)
                <div class="health-status-summary">
                    <span class="health-status-state issued"><x-outline-icon name="check" /> Approved</span>
                    <p class="health-status-message">Your health profile is approved and already available in your health record.</p>
                </div>

                <div class="health-status-meta-grid">
                    <div class="health-status-meta">
                        <span class="health-status-meta-label">{{ $referenceHeading }}</span>
                        <span class="health-status-meta-value">{{ $recordReferenceNumber !== '' ? $recordReferenceNumber : '-' }}</span>
                    </div>
                    <div class="health-status-meta">
                        <span class="health-status-meta-label">Assessment Date</span>
                        <span class="health-status-meta-value">{{ $recordAssessmentDate ?: '-' }}</span>
                    </div>
                    <div class="health-status-meta">
                        <span class="health-status-meta-label">Verified At</span>
                        <span class="health-status-meta-value">{{ $recordVerifiedAt ?: '-' }}</span>
                    </div>
                </div>

                @if($puptasSyncStatus === 'synced')
                    <div class="health-status-sync synced">
                        <x-outline-icon name="check" />
                        <span>
                            <strong>PUPTAS sync complete.</strong>
                            @if($puptasSyncedAt)
                                Synced on {{ $puptasSyncedAt }}.
                            @endif
                        </span>
                    </div>
                @elseif($puptasSyncStatus === 'syncing')
                    <div class="health-status-sync syncing">
                        <x-outline-icon name="clock" />
                        <span>
                            <strong>PUPTAS sync in progress.</strong>
                            {{ $puptasSyncMessage !== '' ? $puptasSyncMessage : 'Your approved clearance is being prepared for PUPTAS sync.' }}
                        </span>
                    </div>
                @elseif($puptasSyncStatus === 'failed')
                    <div class="health-status-sync failed">
                        <x-outline-icon name="exclamation-triangle" />
                        <span>
                            <strong>PUPTAS sync failed.</strong>
                            {{ $puptasSyncMessage !== '' ? $puptasSyncMessage : 'The approved clearance has not been accepted by PUPTAS yet.' }}
                        </span>
                    </div>
                @elseif(in_array($puptasSyncStatus, ['missing_reference_number', 'missing_student_number'], true))
                    <div class="health-status-sync missing">
                        <x-outline-icon name="information-circle" />
                        <span>
                            <strong>PUPTAS sync is waiting for a valid reference number.</strong>
                            {{ $puptasSyncMessage !== '' ? $puptasSyncMessage : 'The clinic approval is complete, but the admission sync cannot finish until the Admission reference number is resolved.' }}
                        </span>
                    </div>
                @elseif($puptasSyncStatus === 'missing_student_id')
                    <div class="health-status-sync missing">
                        <x-outline-icon name="information-circle" />
                        <span>
                            <strong>PUPTAS sync is waiting for the IdP student ID.</strong>
                            {{ $puptasSyncMessage !== '' ? $puptasSyncMessage : 'The clinic approval is complete, but the admission sync cannot finish until the IdP student ID is resolved.' }}
                        </span>
                    </div>
                @endif

                <div class="health-status-actions">
                    <button type="button" class="btn-print-form approved" onclick="openHealthRecordModal()">
                        <x-outline-icon name="eye" />
                        View Record Details
                    </button>
                    <a href="https://puptas.undraftedbsit2027.com/applicant-dashboard" class="health-status-link">
                        <x-outline-icon name="document-text" />
                        Proceed to Admission System
                    </a>
                </div>
                <span class="health-status-note">Valid for Academic Year 2025-2026</span>
            @else
                <div class="health-status-summary">
                    @if($isConditionalStatus)
                        <span class="health-status-state pending"><x-outline-icon name="clock" /> Pending Compliance</span>
                    @endif
                    <p class="health-status-message">{{ $recordStatusMessage }}</p>
                </div>

                <div class="health-status-meta-grid">
                    <div class="health-status-meta">
                        <span class="health-status-meta-label">{{ $referenceHeading }}</span>
                        <span class="health-status-meta-value">{{ $recordReferenceNumber !== '' ? $recordReferenceNumber : '-' }}</span>
                    </div>
                    <div class="health-status-meta">
                        <span class="health-status-meta-label">Submission Status</span>
                        <span class="health-status-meta-value">{{ $recordSubmissionStatus }}</span>
                    </div>
                    <div class="health-status-meta">
                        <span class="health-status-meta-label">{{ $isConditionalStatus ? 'Pending Reason' : 'View Mode' }}</span>
                        <span class="health-status-meta-value">{{ $isConditionalStatus ? ($recordPendingReason !== '' ? $recordPendingReason : '-') : 'Digital copy and status only' }}</span>
                    </div>
                </div>

                <div class="health-status-actions">
                    <button type="button" class="btn-print-form pending" onclick="openHealthRecordModal()">
                        <x-outline-icon name="eye" />
                        View Submitted Record
                    </button>
                    <button class="btn-print-form disabled" disabled>
                        <x-outline-icon name="clock" />
                        Approval Required
                    </button>
                </div>
                <span class="health-status-note">{{ $recordStatusNote }}</span>
            @endif
            @else
                <div class="health-status-summary">
                    <span class="health-status-state incomplete"><x-outline-icon name="x-mark" /> Not Yet Submitted</span>
                    <p class="health-status-message">Your health profile has not been submitted yet.</p>
                </div>
                <a href="{{ route('health.form') }}" class="btn-print-form incomplete">
                    <x-outline-icon name="document-text" />
                    Complete Form Now
                </a>
            <span class="health-status-note">Submit your health profile to unlock clinic review.</span>
        @endif
    </div>
    @if($healthFormSubmitted && $healthProfileRecord)
        <div class="record-modal-overlay" id="healthRecordModal" aria-hidden="true">
            <div class="record-modal" role="dialog" aria-modal="true" aria-labelledby="healthRecordModalTitle">
                <div class="record-modal-head">
                    <button type="button" class="record-modal-close" aria-label="Close record details" onclick="closeHealthRecordModal()">
                        <x-outline-icon name="x-mark" />
                    </button>
                    <div class="record-modal-head-main">
                        <h2 class="record-modal-title" id="healthRecordModalTitle">Health Record Details</h2>
                        <p class="record-modal-subtitle">Review the information and uploaded digital copies submitted through your Student Health Profile.</p>
                    </div>
                </div>
                <div class="record-modal-body" id="healthRecordModalBody">
                    <div class="record-modal-summary">
                        <div class="record-modal-summary-card">
                            <span class="record-modal-label">Current Status</span>
                            <span class="record-modal-status">{{ $status }}</span>
                        </div>
                        <a class="record-modal-summary-card record-modal-summary-download" href="{{ route('student.health_form.print') }}" target="_blank" rel="noopener">
                            <span>
                                <span class="record-modal-label">Health Form</span>
                                <span class="record-modal-value">Download</span>
                            </span>
                            <span class="record-modal-download-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 3v12"></path>
                                    <path d="m7 10 5 5 5-5"></path>
                                    <path d="M5 21h14"></path>
                                </svg>
                            </span>
                        </a>
                    </div>

                    <div class="record-modal-grid">
                        <div class="record-modal-card">
                            <span class="record-modal-label">{{ $referenceHeading }}</span>
                            <div class="record-modal-value">{{ $recordReferenceNumber !== '' ? $recordReferenceNumber : '-' }}</div>
                        </div>
                        <div class="record-modal-card">
                            <span class="record-modal-label">{{ $recordCourseLabel }}</span>
                            <div class="record-modal-value">{{ $recordCourseValue !== '' ? $recordCourseValue : '-' }}</div>
                        </div>
                        <div class="record-modal-card">
                            <span class="record-modal-label">School Year</span>
                            <div class="record-modal-value">{{ optional($healthProfileRecord)->school_year ?: '-' }}</div>
                        </div>
                        <div class="record-modal-card">
                            <span class="record-modal-label">Birthday</span>
                            <div class="record-modal-value">{{ $recordBirthday }}</div>
                        </div>
                        <div class="record-modal-card">
                            <span class="record-modal-label">Sex</span>
                            <div class="record-modal-value">{{ optional($healthProfileRecord)->sex ?: '-' }}</div>
                        </div>
                        <div class="record-modal-card">
                            <span class="record-modal-label">Height</span>
                            <div class="record-modal-value">{{ optional($healthProfileRecord)->height ?: '-' }}</div>
                        </div>
                        <div class="record-modal-card">
                            <span class="record-modal-label">Weight</span>
                            <div class="record-modal-value">{{ optional($healthProfileRecord)->weight ?: '-' }}</div>
                        </div>
                        <div class="record-modal-card">
                            <span class="record-modal-label">Blood Type</span>
                            <div class="record-modal-value">{{ optional($healthProfileRecord)->blood_type ?: '-' }}</div>
                        </div>
                        <div class="record-modal-card">
                            <span class="record-modal-label">Guardian Name</span>
                            <div class="record-modal-value">{{ optional($healthProfileRecord)->guardian_name ?: '-' }}</div>
                        </div>
                        <div class="record-modal-card">
                            <span class="record-modal-label">Contact Number</span>
                            <div class="record-modal-value">{{ optional($healthProfileRecord)->cellphone ?: optional($healthProfileRecord)->landline ?: '-' }}</div>
                        </div>
                        <div class="record-modal-card is-full">
                            <span class="record-modal-label">Home Address</span>
                            <div class="record-modal-value">{{ optional($healthProfileRecord)->home_address ?: '-' }}</div>
                        </div>
                        <div class="record-modal-card">
                            <span class="record-modal-label">Chest X-Ray Examination Date</span>
                            <div class="record-modal-value">{{ $recordChestXrayDate ?: '-' }}</div>
                        </div>
                        <div class="record-modal-card">
                            <span class="record-modal-label">Medical Certificate Date</span>
                            <div class="record-modal-value">{{ $recordMedicalIssuedAt ?: '-' }}</div>
                        </div>
                        <div class="record-modal-card is-full">
                            <span class="record-modal-label">Assessment Remarks</span>
                            <div class="record-modal-value">{{ optional($healthProfileRecord)->assessment_remarks ?: '-' }}</div>
                        </div>
                        @if($isPendingStatus)
                            <div class="record-modal-card is-full">
                                <span class="record-modal-label">Pending Reason</span>
                                <div class="record-modal-value">{{ optional($healthProfileRecord)->pending_reason ?: '-' }}</div>
                            </div>
                        @endif
                        <div class="record-modal-card is-full">
                            <span class="record-modal-label">Uploaded Digital Copies</span>
                            @php
                                $recordDocuments = [
                                    [
                                        'key' => 'student_photo',
                                        'title' => '2x2 Student Photo',
                                        'meta' => 'Image Upload',
                                        'path' => optional($healthProfileRecord)->student_photo,
                                        'is_image' => true,
                                    ],
                                    [
                                        'key' => 'medical_certificate',
                                        'title' => 'Medical Certificate',
                                        'meta' => 'PDF or Image Upload',
                                        'path' => optional($healthProfileRecord)->medical_certificate,
                                        'is_image' => false,
                                    ],
                                    [
                                        'key' => 'chest_xray_result',
                                        'title' => 'Chest X-ray Result',
                                        'meta' => 'PDF or Image Upload',
                                        'path' => optional($healthProfileRecord)->chest_xray_result,
                                        'is_image' => false,
                                    ],
                                    [
                                        'key' => 'medical_assessment_upload',
                                        'title' => 'Medical Assessment Copy',
                                        'meta' => 'Generated PDF',
                                        'path' => optional($healthProfileRecord)->medical_assessment_upload,
                                        'is_image' => false,
                                    ],
                                    [
                                        'key' => 'pwd_id_proof',
                                        'title' => 'PWD ID Proof',
                                        'meta' => 'PDF Upload',
                                        'path' => optional($healthProfileRecord)->pwd_id_proof,
                                        'is_image' => false,
                                    ],
                                ];
                                $visibleRecordDocuments = collect($recordDocuments)
                                    ->filter(fn ($document) => filled($document['path']))
                                    ->map(function ($document) {
                                        $extension = strtolower(pathinfo((string) $document['path'], PATHINFO_EXTENSION));
                                        $document['is_image'] = $document['is_image'] || in_array($extension, ['jpg', 'jpeg', 'png'], true);

                                        return $document;
                                    });
                            @endphp
                            <div class="record-modal-links">
                                @forelse($visibleRecordDocuments as $document)
                                    @php
                                        $documentUrl = route('student.health_record.document', ['document' => $document['key'] ?? $document['id'] ?? '']);
                                    @endphp
                                    <div class="record-document-card">
                                        <div class="record-document-preview" aria-hidden="true">
                                            @if($document['is_image'])
                                                <img src="{{ $documentUrl }}" alt="">
                                            @else
                                                <x-outline-icon name="document-text" />
                                            @endif
                                        </div>
                                        <div class="record-document-body">
                                            <span class="record-document-title">{{ $document['title'] }}</span>
                                            <span class="record-document-meta">{{ $document['meta'] }}</span>
                                            <div class="record-document-actions">
                                                <a class="record-document-btn" href="{{ $documentUrl }}" target="_blank" rel="noopener noreferrer">View</a>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="record-modal-empty">No digital copies uploaded yet.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="record-modal-body-fade" aria-hidden="true"></div>
                </div>
            </div>
        </div>
    @endif
@else
    <div class="page-hero">
        <div class="page-hero-icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
            </svg>
        </div>
        <div class="page-hero-kicker">Clinic Updates</div>
        <h1 class="page-hero-title">Notifications</h1>
        <p class="page-hero-text">Stay updated with appointment changes, health record progress, and important clinic activity.</p>
        <div class="page-hero-steps">
            <div class="page-hero-step">
                <span>Clinic Updates</span>
            </div>
            <div class="page-hero-step">
                <span>Important Alerts</span>
            </div>
            <div class="page-hero-step">
                <span>Status Changes</span>
            </div>
        </div>
    </div>
    <div class="widget-card">
        <div class="notif-panel-head">
            <h2 class="notif-panel-title">
                <x-outline-icon name="bell" />
                Notifications
            </h2>
            @if(collect($notifications ?? [])->isNotEmpty())
                <form action="{{ route('student.notifications.read_all') }}" method="POST">
                    @csrf
                    <button type="submit" class="notif-mark-btn">
                        Mark all as read
                    </button>
                </form>
            @endif
        </div>

        <div class="notif-list">
            @forelse(collect($notifications ?? []) as $notif)
                <a href="{{ route('student.notifications.open', ['notificationId' => $notif['id']]) }}"
                   class="notif-record {{ !empty($notif['is_unread']) ? 'is-unread' : '' }}">
                    <span class="notif-record-dot"></span>
                    <span class="notif-record-content">
                        <span class="notif-record-message">
                            {{ $notif['message'] ?? 'Notification available.' }}
                        </span>
                        <span class="notif-record-time">
                            {{ $notif['time'] ?? 'Just now' }}
                        </span>
                    </span>
                </a>
            @empty
                <div class="notif-empty">
                    No notifications available right now.
                </div>
            @endforelse
        </div>
    </div>
@endif
        </div>
    </div>
</div>

<script>
function openHealthRecordModal() {
    const modal = document.getElementById('healthRecordModal');
    if (!modal) {
        return;
    }
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
}

function closeHealthRecordModal() {
    const modal = document.getElementById('healthRecordModal');
    if (!modal) {
        return;
    }
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
}

function enableEditing() {
    const form = document.querySelector('form[action="{{ route('student.updateContact') }}"]');
    if (form) {
        form.classList.add('profile-is-editing');
    }
    const inputs = form
        ? form.querySelectorAll('[name="height"], [name="weight"]')
        : [];
    
    inputs.forEach(input => {
        input.disabled = false;
        input.readOnly = false;
        input.style.borderColor = '#8B0000'; 
        input.style.backgroundColor = document.documentElement.getAttribute('data-theme') === 'dark'
            ? 'rgba(30, 41, 59, 0.92)'
            : '#fff';

        const editableRow = input.closest('.profile-info-row, .profile-grid-2 > div, .profile-grid-3 > div');
        if (editableRow) {
            editableRow.classList.add('is-editing');
        }
    });

    document.getElementById('editBtn').style.display = 'none';
    document.getElementById('saveAction').style.display = 'flex';
}

document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('healthRecordModal');
    if (!modal) {
        return;
    }
    const modalCard = modal.querySelector('.record-modal');

    modalCard?.addEventListener('scroll', updateHealthRecordModalIndicator);

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeHealthRecordModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            closeHealthRecordModal();
        }
    });
});
</script>
@endsection
