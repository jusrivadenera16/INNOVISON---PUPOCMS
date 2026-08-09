@extends('layouts.student')

@section('title', 'My Account')

@push('styles')
<style>
    body:has(.account-layout) {
        background:
            linear-gradient(180deg, rgba(255, 250, 250, 0.70), rgba(255, 255, 255, 0.58) 42%, rgba(245, 248, 247, 0.72) 100%),
            url('{{ asset("images/student-bg.png") }}') center top / cover no-repeat fixed !important;
    }
    html[data-theme="dark"] body:has(.account-layout) {
        background:
            linear-gradient(180deg, rgba(2, 6, 23, 0.82), rgba(15, 23, 42, 0.74) 42%, rgba(2, 6, 23, 0.84) 100%),
            url('{{ asset("images/student-bg.png") }}') center top / cover no-repeat fixed !important;
    }
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

    .profile-dashboard {
        max-width: 1180px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: minmax(330px, 0.82fr) minmax(0, 1fr) minmax(0, 1fr);
        gap: 16px;
        align-items: start;
    }

    .profile-dashboard .account-layout {
        display: contents;
    }

    .profile-dashboard .widget-card {
        display: contents;
    }

    .profile-dashboard .widget-card > form,
    .profile-dashboard .profile-sections-grid {
        display: contents;
    }

    .profile-dashboard .profile-card-head {
        display: flex;
        grid-column: 1;
        grid-row: 1;
        align-self: start;
        justify-content: center;
        margin: 322px 0 0;
        z-index: 4;
        pointer-events: none;
    }

    .profile-dashboard .profile-card-heading {
        display: none;
    }

    .profile-dashboard.is-photo-expanded .profile-card-head {
        display: none;
    }

    .profile-dashboard .profile-card-head .profile-edit-btn {
        pointer-events: auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-width: 146px;
        padding: 9px 18px;
        font-size: 13px;
        font-weight: 700;
        border-color: rgba(250, 204, 21, .68);
        background: transparent;
        color: #ffffff;
        box-shadow:
            0 9px 16px rgba(25, 0, 8, .38),
            inset 0 1px 0 rgba(255, 255, 255, .08);
    }

    .profile-dashboard .profile-card-head .profile-edit-btn svg {
        width: 14px;
        height: 14px;
    }

    .profile-dashboard .profile-card-head .profile-edit-btn:hover {
        background: #facc15;
        color: #70131b;
    }

    .profile-dashboard .profile-hero {
        grid-column: 1;
        grid-row: 1;
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        align-items: start;
        align-self: stretch;
        min-height: 560px;
        margin: 0;
        padding: 40px 28px 28px;
        border-radius: 10px;
        background:
            radial-gradient(circle at 22% 10%, transparent 0 48px, rgba(255, 255, 255, .035) 49px 50px, transparent 51px 67px, rgba(255, 255, 255, .025) 68px 69px, transparent 70px),
            radial-gradient(circle at top right, rgba(250, 204, 21, .08), transparent 34%),
            linear-gradient(180deg, #6f001f 0%, #560017 54%, #3d0012 100%);
        box-shadow: 0 18px 36px rgba(76, 5, 25, 0.18);
        gap: 20px;
        position: relative;
        overflow: hidden;
    }

    .profile-dashboard .profile-hero::before {
        content: "";
        position: absolute;
        left: -12%;
        right: -12%;
        bottom: -36px;
        height: 128px;
        border-top: 2px solid rgba(250, 204, 21, .9);
        border-radius: 50% 50% 0 0 / 28% 28% 0 0;
        background:
            linear-gradient(180deg, rgba(127, 29, 45, .04), rgba(255, 247, 237, .16)),
            url('{{ asset("images/PUPBG.jpg") }}') center bottom / cover no-repeat;
        opacity: .62;
        pointer-events: none;
        z-index: 0;
    }

    .profile-dashboard .profile-hero::before {
        display: none;
    }

    .profile-dashboard .profile-hero-wave {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100%;
        height: 20%;
        z-index: 1;
        pointer-events: none;
        overflow: visible;
    }

    .profile-dashboard .profile-hero-wave path {
        fill: #fff8ed;
        stroke: #facc15;
        stroke-width: 4;
        stroke-linejoin: round;
    }

    html[data-theme="dark"] .profile-dashboard .profile-hero-wave path {
        fill: #0f131a;
    }

    .profile-dashboard .profile-hero > * {
        position: relative;
        z-index: 1;
    }

    .profile-dashboard .profile-hero > .profile-hero-wave {
        position: absolute;
        z-index: 1;
    }

    .profile-dashboard .profile-hero::after {
        display: none;
    }

    .profile-dashboard .profile-hero-campus {
        position: absolute;
        left: 0;
        right: -8%;
        bottom: 46px;
        height: 230px;
        z-index: 0;
        pointer-events: none;
        opacity: .18;
        background: url('{{ asset("images/PUPBG.jpg") }}') center bottom / cover no-repeat;
        filter: saturate(.25) contrast(1.1);
        -webkit-mask-image: radial-gradient(ellipse 88% 82% at 76% 72%, #000 38%, rgba(0, 0, 0, .82) 58%, rgba(0, 0, 0, .28) 78%, transparent 100%);
        mask-image: radial-gradient(ellipse 88% 82% at 76% 72%, #000 38%, rgba(0, 0, 0, .82) 58%, rgba(0, 0, 0, .28) 78%, transparent 100%);
    }

    .profile-dashboard .profile-hero-quote {
        position: absolute;
        left: 28px;
        bottom: 126px;
        z-index: 2;
        color: #fffaf2;
        font-family: Georgia, serif;
        text-shadow: 0 2px 10px rgba(43, 0, 16, .58);
    }

    .profile-dashboard .profile-hero-quote-mark {
        display: block;
        height: 26px;
        color: #facc15;
        font-family: Georgia, serif;
        font-size: 35px;
        font-weight: 800;
        line-height: 1;
    }

    .profile-dashboard .profile-hero-quote p {
        margin: 2px 0 0;
        font-size: 20px;
        line-height: 1.35;
    }

    .profile-dashboard .profile-hero.is-photo-expanded {
        grid-template-columns: 1fr;
        place-items: center;
        min-height: 360px;
        cursor: zoom-out;
    }

    .profile-dashboard .hero-info {
        min-width: 0;
        position: static !important;
    }

    .profile-dashboard .hero-avatar {
        position: relative;
        flex: 0 0 auto;
        margin-top: 4px;
        padding: 0;
        width: 92px;
        height: 92px;
        background: rgba(255, 255, 255, 0.90);
        border: 2px solid #f5c542;
        color: #7f1d2d;
        font-size: 28px;
        overflow: visible;
        cursor: zoom-in;
        appearance: none;
        transition: width 0.22s ease, height 0.22s ease, border-radius 0.22s ease, transform 0.22s ease;
    }

    .profile-dashboard .hero-avatar img {
        border-radius: 50%;
        overflow: hidden;
    }

    .profile-dashboard .hero-name {
        margin-top: 9px;
        font-size: 21px;
        line-height: 1.13;
        letter-spacing: 0;
    }

    .hero-active-dot {
        position: absolute;
        right: -1px;
        bottom: 3px;
        width: 22px;
        height: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: #16a34a;
        border: 3px solid #7f1d2d;
        color: #ffffff;
        box-shadow: 0 8px 16px rgba(22, 163, 74, 0.28);
        z-index: 2;
    }

    .hero-active-dot svg {
        width: 12px;
        height: 12px;
        stroke-width: 3;
    }

    .profile-dashboard .hero-name-main,
    .profile-dashboard .hero-name-sub {
        display: block;
    }

    .profile-dashboard .hero-name-sub {
        margin-top: 3px;
        font-size: 0.82em;
        color: rgba(255, 255, 255, 0.88);
    }

    .hero-identity-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        margin-top: 8px;
    }

    .hero-identity-pill {
        display: inline-flex;
        align-items: center;
        min-height: 24px;
        padding: 0 11px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.13);
        color: #fff7ed;
        border: 1px solid rgba(255, 255, 255, 0.16);
        font-size: 12px;
        font-weight: 800;
        line-height: 1;
    }

    .profile-dashboard .hero-status-line {
        display: flex;
        align-items: center;
        gap: 7px;
        margin-top: 8px;
        padding-bottom: 14px;
        color: rgba(255, 247, 237, .92);
        font-size: 13px;
        font-weight: 500;
        position: relative;
    }

    .profile-dashboard .hero-status-line::before {
        content: "";
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #22c55e;
        box-shadow: 0 0 0 3px rgba(34, 197, 94, .14);
    }

    .profile-dashboard .hero-status-line::after {
        content: "";
        position: absolute;
        left: 0;
        bottom: 0;
        width: 28px;
        height: 2px;
        border-radius: 999px;
        background: #facc15;
    }

    .profile-dashboard .profile-hero.is-photo-expanded .hero-avatar {
        width: min(100%, 310px);
        height: min(310px, 72vw);
        margin-top: 0;
        border-radius: 18px;
        transform: none;
        cursor: zoom-out;
    }

    .profile-dashboard .profile-hero.is-photo-expanded .hero-avatar img {
        border-radius: 14px;
    }

    .profile-dashboard .profile-hero.is-photo-expanded .hero-info,
    .profile-dashboard .profile-hero.is-photo-expanded .hero-stats,
    .profile-dashboard .profile-hero.is-photo-expanded .hero-active-dot,
    .profile-dashboard .profile-hero.is-photo-expanded .profile-hero-campus,
    .profile-dashboard .profile-hero.is-photo-expanded .profile-hero-quote,
    .profile-dashboard .profile-hero.is-photo-expanded .profile-hero-wave {
        display: none;
    }

    .profile-dashboard .hero-course {
        color: #fff7ed;
        opacity: 1;
        font-weight: 700;
        line-height: 1.35;
    }

    .profile-dashboard .hero-info > .hero-course {
        display: none !important;
    }

    .profile-dashboard .hero-stats {
        grid-column: 1 / -1;
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        width: 100%;
        box-sizing: border-box;
        gap: 0;
        margin-top: 16px;
        margin-left: 0;
        margin-right: 0;
        padding: 16px 8px 14px;
    }

    .profile-dashboard .stat-item {
        min-width: 0;
        text-align: center;
    }

    .profile-dashboard .stat-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 22px;
        margin-bottom: 7px;
        color: #facc15;
    }

    .profile-dashboard .stat-icon svg {
        width: 20px;
        height: 20px;
        stroke-width: 2;
    }

    .profile-dashboard .stat-label {
        font-size: 11px;
        white-space: normal;
        word-break: normal;
        letter-spacing: 0.02em;
    }

    .profile-dashboard .stat-val {
        font-size: 21px;
    }

    .profile-dashboard .stat-label {
        color: rgba(255, 255, 255, 0.88);
        opacity: 1;
    }

    .profile-dashboard .profile-sections-grid {
        display: contents;
        margin-bottom: 0;
    }

    .profile-dashboard .profile-column-stack {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .profile-dashboard .profile-sections-grid > .profile-column-stack:first-child {
        grid-column: 2;
        grid-row: 1;
    }

    .profile-dashboard .profile-sections-grid > .profile-column-stack:last-child {
        grid-column: 3;
        grid-row: 1;
    }

    .profile-dashboard .profile-personal-section,
    .profile-dashboard .profile-academic-section,
    .profile-dashboard .profile-contact-section {
        grid-column: auto;
        grid-row: auto;
    }

    .profile-dashboard .profile-emergency-section {
        grid-column: auto;
        grid-row: auto;
    }

    .profile-dashboard #profileActionBar,
    .profile-dashboard .profile-enrollment-empty {
        grid-column: 1 / -1;
    }

    .profile-dashboard .profile-form-section {
        border-radius: 14px;
        border-color: rgba(127, 29, 45, 0.15);
        background: linear-gradient(180deg, #ffffff 0%, #fffdf9 100%);
        box-shadow:
            0 12px 26px rgba(15, 23, 42, .08),
            0 0 0 1px rgba(255,255,255,.78) inset;
    }

    .profile-dashboard .profile-form-section-title {
        gap: 9px;
        margin: 0 0 16px;
        color: #70131b;
        font-size: 15px;
        font-weight: 900;
    }

    .profile-dashboard .profile-form-section-title svg {
        width: 34px;
        height: 34px;
        padding: 8px;
        box-sizing: border-box;
        border-radius: 999px;
        background: #7f1d2d;
        color: #ffffff;
        stroke-width: 1.8;
    }

    .profile-dashboard .profile-grid-3 > div,
    .profile-dashboard .profile-grid-2 > div,
    .profile-dashboard .profile-info-row {
        min-height: 38px;
        display: grid;
        grid-template-columns: minmax(120px, 42%) minmax(0, 1fr);
        align-items: center;
        gap: 10px;
        padding: 8px 0;
        border: 0 !important;
        border-bottom: 1px solid rgba(250, 204, 21, .30) !important;
        border-radius: 0;
        background: transparent !important;
        box-shadow: none !important;
    }

    .profile-dashboard .profile-grid-3 > div:last-child,
    .profile-dashboard .profile-grid-2 > div:last-child,
    .profile-dashboard .profile-info-row:last-child {
        border-bottom: 1px solid rgba(250, 204, 21, .30) !important;
    }

    .profile-dashboard .profile-grid-3 > div .input-label,
    .profile-dashboard .profile-grid-2 > div .input-label,
    .profile-dashboard .profile-info-row .input-label {
        font-family: inherit;
        font-style: normal;
        font-size: 12px;
        font-weight: 700;
        color: #475569;
        line-height: 1.25;
    }

    .profile-dashboard .profile-grid-3 > div .form-control,
    .profile-dashboard .profile-grid-2 > div .form-control,
    .profile-dashboard .profile-info-row .form-control,
    .profile-dashboard .profile-static-field {
        font-family: inherit;
        font-style: normal;
        min-height: 0;
        padding: 0;
        border: 0 !important;
        border-radius: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        color: #111827;
        font-size: 13px;
        font-weight: 400;
        line-height: 1.35;
    }

    .profile-dashboard .profile-info-row textarea.form-control {
        min-height: 34px;
    }

    .profile-dashboard .profile-academic-section .profile-grid-3 > div {
        min-height: 38px;
        padding: 8px 0;
        border: 0 !important;
        border-bottom: 1px solid rgba(250, 204, 21, .30) !important;
        border-radius: 0;
        background: transparent !important;
    }

    .profile-dashboard .profile-academic-section .profile-grid-3 > div .form-control,
    .profile-dashboard .profile-academic-section .profile-static-field {
        font-size: 14px;
        font-weight: 400;
    }

    .profile-dashboard .profile-contact-section,
    .profile-dashboard .profile-emergency-section {
        padding-top: 18px;
    }

    .profile-dashboard .profile-emergency-section {
        align-self: start;
    }

    .profile-dashboard .hero-stats {
        position: absolute;
        top: 178px;
        left: 50% !important;
        right: auto !important;
        width: calc(100% - 40px) !important;
        transform: translateX(-50%);
        z-index: 2;
        padding: 17px 0 15px;
        border-top: 0;
        border-radius: 12px;
        background: rgba(255, 255, 255, .105);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .07);
        overflow: hidden;
    }

    .profile-dashboard .hero-stats .stat-item {
        padding: 0 8px;
    }

    .profile-dashboard .hero-stats .stat-item + .stat-item {
        border-left: 1px solid rgba(255, 255, 255, .18);
    }

    .profile-dashboard .stat-label {
        white-space: nowrap;
        font-size: 10.5px;
        text-transform: none;
        letter-spacing: 0;
    }

    html[data-theme="dark"] .profile-dashboard .profile-grid-3 > div,
    html[data-theme="dark"] .profile-dashboard .profile-grid-2 > div,
    html[data-theme="dark"] .profile-dashboard .profile-info-row {
        border-bottom-color: rgba(250, 204, 21, .18) !important;
        background: transparent !important;
    }

    html[data-theme="dark"] .profile-dashboard .profile-grid-3 > div .input-label,
    html[data-theme="dark"] .profile-dashboard .profile-grid-2 > div .input-label,
    html[data-theme="dark"] .profile-dashboard .profile-info-row .input-label,
    html[data-theme="dark"] .profile-dashboard .profile-grid-3 > div .form-control,
    html[data-theme="dark"] .profile-dashboard .profile-grid-2 > div .form-control,
    html[data-theme="dark"] .profile-dashboard .profile-info-row .form-control,
    html[data-theme="dark"] .profile-dashboard .profile-static-field {
        color: #f8fafc !important;
    }

    html[data-theme="dark"] body .profile-dashboard .profile-form-section .form-control:disabled,
    html[data-theme="dark"] body .profile-dashboard .profile-form-section .form-control[readonly],
    html[data-theme="dark"] body .profile-dashboard .profile-form-section textarea.form-control:disabled {
        background: transparent !important;
        background-color: transparent !important;
        border-color: transparent !important;
        box-shadow: none !important;
        color: #f8fafc !important;
        -webkit-text-fill-color: #f8fafc;
        opacity: 1;
    }

    .profile-dashboard .profile-form-section::before {
        left: 14px;
        right: 14px;
        border-radius: 999px;
    }

    .profile-dashboard .profile-sections-grid .profile-form-section {
        height: auto;
        min-height: 0;
    }

    .profile-dashboard .profile-frame-equal {
        min-height: 0;
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
        content: none;
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
        text-transform: none;
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
        color: #111827 !important;
        font-size: 1.35rem;
        font-weight: 800;
    }
    #healthPrintReminderTitle {
        color: #111827 !important;
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
        .profile-dashboard {
            grid-template-columns: 1fr;
            max-width: 960px;
        }
        .profile-dashboard .profile-hero,
        .profile-dashboard .profile-sections-grid > .profile-column-stack:first-child,
        .profile-dashboard .profile-sections-grid > .profile-column-stack:last-child {
            grid-column: 1;
            grid-row: auto;
        }
        .profile-dashboard .profile-card-head {
            grid-column: 1;
            grid-row: auto;
            position: absolute;
            top: 322px;
            left: 0;
            margin: 0;
            justify-content: center;
        }
        .profile-dashboard .profile-card-head .profile-edit-btn {
            margin: 0 auto;
        }
        .profile-dashboard .profile-personal-section,
        .profile-dashboard .profile-academic-section,
        .profile-dashboard .profile-contact-section,
        .profile-dashboard .profile-emergency-section {
            grid-column: 1;
            grid-row: auto;
        }
        .profile-dashboard .profile-hero {
            min-height: 560px;
        }
        .profile-dashboard {
            gap: 12px;
        }
        .profile-dashboard {
            display: block;
            position: relative;
        }
        .profile-dashboard .profile-hero {
            width: 100%;
            margin-bottom: 12px;
        }
        .profile-dashboard .profile-card-head {
            width: 100%;
            margin-bottom: 0;
        }
        .profile-dashboard .profile-form-section {
            width: 100%;
            box-sizing: border-box;
            margin-bottom: 12px;
        }
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
        .profile-dashboard .profile-hero {
            border-radius: 8px;
            align-items: start;
        }
        .hero-name {
            font-size: 24px;
        }
        .profile-dashboard .hero-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
        }
        .profile-grid-3,
        .profile-grid-2 {
            grid-template-columns: 1fr;
        }
        .profile-sections-grid {
            grid-template-columns: 1fr;
            gap: 18px;
        }
        .profile-sections-grid .profile-form-section {
            height: auto;
            min-height: 0;
            overflow: visible;
        }
        .profile-frame-equal {
            height: auto;
            min-height: 0;
            overflow: visible;
        }
        .profile-card-head {
            flex-direction: column;
            align-items: stretch;
        }
        .profile-edit-btn {
            width: 100%;
        }
        .profile-dashboard .profile-card-head {
            align-items: center;
        }
        .profile-dashboard .profile-card-head .profile-edit-btn {
            width: auto;
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
        .profile-info-row textarea.form-control {
            min-height: 92px;
            height: auto;
            line-height: 1.45;
            overflow: visible;
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
    .missing-requirements-panel {
        margin: 18px 0 24px;
        padding: 22px;
        border-radius: 14px;
        border: 1px solid #fecaca;
        background: linear-gradient(135deg, #fff7f7 0%, #ffffff 100%);
        box-shadow: 0 14px 34px rgba(112, 19, 27, 0.08);
    }
    .missing-requirements-main {
        display: grid;
        gap: 18px;
        align-items: stretch;
    }
    .missing-requirements-lead {
        display: grid;
        grid-template-columns: 42px minmax(0, 1fr);
        gap: 14px;
        align-items: start;
    }
    .missing-requirements-icon,
    .missing-requirements-doc-icon,
    .missing-requirements-upload-icon,
    .missing-requirements-reminder-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }
    .missing-requirements-icon {
        width: 42px;
        height: 42px;
        border-radius: 999px;
        color: #dc2626;
        background: #fff7f7;
        border: 2px solid #fca5a5;
    }
    .missing-requirements-icon svg,
    .missing-requirements-doc-icon svg,
    .missing-requirements-upload-icon svg,
    .missing-requirements-reminder-icon svg {
        width: 20px;
        height: 20px;
    }
    .missing-requirements-title {
        margin: 0;
        color: #8B0000;
        font-size: 18px;
        font-weight: 950;
        line-height: 1.2;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .missing-requirements-title svg {
        width: 19px;
        height: 19px;
    }
    .missing-requirements-copy {
        margin: 8px 0 0;
        color: #475569;
        font-size: 13px;
        font-weight: 750;
        line-height: 1.45;
    }
    .missing-requirements-list {
        margin: 0;
        padding: 14px 18px 14px 38px;
        border-radius: 12px;
        border: 1px solid rgba(112, 19, 27, .10);
        background: #ffffff;
        box-shadow: 0 12px 26px rgba(15, 23, 42, .06);
    }
    .missing-requirement-item {
        padding: 6px 0;
        color: #111827;
        font-size: 13px;
        font-weight: 900;
        line-height: 1.35;
    }
    .missing-requirements-doc-icon {
        width: 24px;
        height: 24px;
        color: #dc2626;
    }
    .missing-requirements-footer {
        display: flex;
        justify-content: flex-end;
    }
    .missing-requirement-action {
        border: 1px solid #8B0000;
        border-radius: 10px;
        background: #8B0000;
        color: #facc15;
        padding: 9px 16px;
        font-size: 12px;
        font-weight: 950;
        cursor: pointer;
        transition: background .2s ease, color .2s ease, border-color .2s ease, transform .2s ease;
    }
    .missing-requirement-action:hover,
    .missing-requirement-action:focus-visible {
        background: #facc15;
        color: #70131B;
        border-color: #facc15;
        transform: translateY(-1px);
        outline: none;
    }
    .missing-requirement-action:disabled {
        opacity: .55;
        cursor: not-allowed;
        transform: none;
    }
    .missing-requirements-upload-card {
        min-height: 136px;
        border-radius: 12px;
        border: 1px dashed #fca5a5;
        background: #fffafa;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 9px;
        padding: 18px;
        text-align: center;
    }
    .missing-inline-upload-form {
        width: 100%;
        display: grid;
        gap: 10px;
        text-align: left;
    }
    .missing-inline-upload-form + .missing-inline-upload-form,
    .missing-inline-upload-form + .missing-requirements-upload-btn {
        margin-top: 10px;
    }
    .missing-inline-upload-form strong {
        color: #70131B;
        font-size: 12px;
        font-weight: 950;
    }
    .missing-inline-upload-field {
        display: grid;
        gap: 5px;
        border: 1px solid rgba(112, 19, 27, .12);
        border-radius: 10px;
        background: #ffffff;
        padding: 10px;
        color: #111827;
        font-size: 12px;
        font-weight: 850;
    }
    .missing-inline-upload-field input {
        width: 100%;
        font-size: 11px;
    }
    .missing-inline-upload-field small {
        color: #64748b;
        font-size: 10px;
        font-weight: 750;
    }
    .missing-inline-upload-field em {
        color: #dc2626;
        font-size: 10px;
        font-style: normal;
        font-weight: 850;
    }
    .missing-requirements-upload-icon {
        color: #8B0000;
    }
    .missing-requirements-upload-btn {
        border: 1px solid #8B0000;
        border-radius: 10px;
        background: #8B0000;
        color: #ffffff;
        padding: 12px 18px;
        font-size: 13px;
        font-weight: 950;
        cursor: pointer;
        text-decoration: none;
        transition: background .2s ease, color .2s ease, border-color .2s ease, transform .2s ease;
    }
    .missing-requirements-upload-btn:hover,
    .missing-requirements-upload-btn:focus-visible {
        background: #facc15;
        color: #70131B;
        border-color: #facc15;
        transform: translateY(-1px);
        outline: none;
    }
    .missing-requirements-upload-help {
        margin: 0;
        color: #475569;
        font-size: 11px;
        font-weight: 800;
        line-height: 1.35;
    }
    .missing-requirements-reminder {
        margin-top: 18px;
        padding: 14px 16px;
        border-radius: 12px;
        border: 1px solid #bfdbfe;
        background: #eff6ff;
        color: #1e3a8a;
        display: grid;
        grid-template-columns: 26px minmax(0, 1fr);
        gap: 10px;
    }
    .missing-requirements-reminder strong {
        display: block;
        margin-bottom: 4px;
        font-size: 13px;
        font-weight: 950;
    }
    .missing-requirements-reminder p {
        margin: 0;
        font-size: 12px;
        font-weight: 750;
        line-height: 1.45;
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
    .health-declaration-card {
        margin: 18px 0;
        padding: 18px;
        overflow: hidden;
        border: 1px solid #fecaca;
        border-radius: 14px;
        background: linear-gradient(135deg, #fff1f2, #ffffff);
        box-shadow: 0 14px 34px rgba(112, 19, 27, 0.08);
    }
    .health-declaration-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 14px;
    }
    .health-declaration-title {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #70131b;
        font-size: 18px;
        font-weight: 900;
    }
    .health-declaration-title svg {
        width: 20px;
        height: 20px;
        flex: 0 0 auto;
    }
    .health-declaration-note {
        margin: 6px 0 0;
        color: #475569;
        font-size: 14px;
        line-height: 1.55;
    }
    .health-declaration-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 30px;
        padding: 7px 12px;
        border-radius: 999px;
        border: 1px solid #fca5a5;
        background: #fef2f2;
        color: #991b1b;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .health-declaration-badge.is-uploaded {
        border-color: #86efac;
        background: #f0fdf4;
        color: #166534;
    }
    .health-declaration-form,
    .health-declaration-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
    }
    .health-declaration-actions {
        align-items: stretch;
    }
    .health-declaration-upload {
        flex: 1 1 280px;
        display: grid;
        gap: 8px;
    }
    .health-declaration-picker {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        min-height: 46px;
        padding: 7px 10px;
        border: 1px solid rgba(112, 19, 27, 0.18);
        border-radius: 12px;
        background: #ffffff;
        color: #1f2937;
        font-size: 13px;
        cursor: pointer;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9);
    }
    .health-declaration-picker:hover,
    .health-declaration-picker:focus-within {
        border-color: #70131b;
        box-shadow: 0 0 0 3px rgba(112, 19, 27, 0.08);
    }
    .health-declaration-file {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
        overflow: hidden;
        pointer-events: none;
    }
    .health-declaration-choose {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 32px;
        padding: 0 12px;
        border-radius: 9px;
        background: #70131b;
        color: #facc15;
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }
    .health-declaration-filename {
        min-width: 0;
        color: #475569;
        font-size: 13px;
        font-weight: 700;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .health-declaration-limit {
        color: #991b1b;
        font-size: 12px;
        font-weight: 800;
    }
    .health-declaration-preview {
        display: none;
        align-items: center;
        gap: 10px;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        padding: 10px;
        border: 1px solid rgba(112, 19, 27, 0.12);
        border-radius: 12px;
        background: #ffffff;
    }
    .health-declaration-preview.is-visible {
        display: flex;
    }
    .health-declaration-preview-thumb {
        width: 58px;
        height: 58px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: #fff1f2;
        color: #70131b;
        font-size: 11px;
        font-weight: 900;
        overflow: hidden;
        flex: 0 0 auto;
    }
    .health-declaration-preview-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .health-declaration-preview-copy {
        min-width: 0;
        display: grid;
        gap: 3px;
        color: #1f2937;
        font-size: 13px;
        font-weight: 800;
    }
    .health-declaration-preview-copy small {
        color: #64748b;
        font-size: 11px;
        font-weight: 800;
    }
    .health-declaration-preview-copy strong,
    .health-declaration-preview-copy small {
        overflow-wrap: anywhere;
        word-break: break-word;
    }
    .health-declaration-submit {
        min-height: 46px;
        padding: 0 18px;
        border: 1px solid #70131b;
        border-radius: 12px;
        background: #70131b;
        color: #facc15;
        font-weight: 900;
        box-shadow: 0 12px 24px rgba(112, 19, 27, 0.2);
    }
    .health-declaration-submit:hover,
    .health-declaration-submit:focus {
        background: #facc15;
        color: #70131b;
        border-color: #facc15;
        outline: none;
    }
    @media (max-width: 640px) {
        .missing-document-modal {
            width: calc(100vw - 24px);
            max-height: calc(100vh - 24px);
        }
        .missing-document-body {
            padding: 16px 16px 0;
        }
        .missing-document-progress,
        .missing-document-row,
        .missing-document-esign-row,
        .missing-document-footer {
            grid-template-columns: 1fr;
        }
        .missing-document-esign-action {
            width: 100%;
        }
        .missing-document-stat {
            padding-left: 0;
            border-left: 0;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
        .missing-upload-actions {
            flex-direction: column;
        }
        .missing-esign-submit,
        .missing-esign-secondary {
            width: 100%;
        }
        .missing-requirements-panel {
            padding: 16px;
        }
        .missing-requirements-main {
            grid-template-columns: 1fr;
        }
        .missing-requirements-list {
            padding: 14px;
        }
        .missing-requirement-item {
            grid-template-columns: 24px 22px minmax(0, 1fr);
            align-items: start;
        }
        .missing-requirements-upload-card {
            min-height: 118px;
        }
        .health-declaration-card {
            padding: 16px;
        }
        .health-declaration-head {
            display: grid;
            gap: 12px;
        }
        .health-declaration-badge {
            width: fit-content;
            max-width: 100%;
            white-space: normal;
            text-align: center;
        }
        .health-declaration-form,
        .health-declaration-actions {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            width: 100%;
        }
        .health-declaration-upload {
            min-width: 0;
            width: 100%;
        }
        .health-declaration-picker {
            align-items: stretch;
            flex-direction: column;
            gap: 8px;
        }
        .health-declaration-choose {
            width: fit-content;
        }
        .health-declaration-filename {
            width: 100%;
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
        }
        .health-declaration-submit {
            width: 100%;
        }
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
    .health-record-details-modal {
        border: 1px solid rgba(250, 204, 21, .34);
    }
    .health-record-details-modal .record-modal-head {
        min-height: 108px;
        display: grid;
        grid-template-columns: 54px minmax(0, 1fr);
        align-items: center;
        gap: 16px;
        padding: 22px 74px 22px 24px;
        box-sizing: border-box;
    }
    .health-record-details-head-icon {
        width: 52px;
        height: 52px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(255,255,255,.22);
        border-radius: 12px;
        background: rgba(255,255,255,.1);
        color: #ffffff;
        box-shadow: 0 10px 20px rgba(40,0,12,.18);
    }
    .health-record-details-head-icon svg {
        width: 28px;
        height: 28px;
        stroke-width: 1.7;
    }
    .health-record-details-modal .record-modal-head-main {
        min-width: 0;
    }
    .health-record-details-modal .record-modal-summary {
        grid-template-columns: 1fr;
    }
    .record-modal-title {
        margin: 0 0 8px;
        color: #ffffff;
        font-size: 24px;
        font-weight: 800;
    }
    @media (max-width: 620px) {
        .health-record-details-modal .record-modal-head {
            min-height: 96px;
            grid-template-columns: 44px minmax(0, 1fr);
            gap: 12px;
            padding: 18px 60px 18px 18px;
        }
        .health-record-details-head-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
        }
        .health-record-details-head-icon svg {
            width: 23px;
            height: 23px;
        }
        .health-record-details-modal .record-modal-title {
            font-size: 20px;
        }
    }
    .record-modal-subtitle {
        margin: 0;
        color: rgba(255, 255, 255, 0.88);
        font-size: 14px;
        line-height: 1.6;
        max-width: 640px;
    }
    @media (max-width: 620px) {
        .health-record-details-modal .record-modal-subtitle {
            font-size: 12px;
            line-height: 1.45;
        }
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
        transition: color .08s linear, box-shadow .18s ease, border-color .18s ease;
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
        transition: transform .32s ease;
        pointer-events: none;
        z-index: 0;
    }
    .record-modal-close:hover {
        border-color: #facc15;
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.18),
            0 14px 24px rgba(112, 19, 27, 0.16);
    }
    .record-modal-close:hover::after {
        transform: translateX(135%);
    }
    .health-record-details-modal .record-modal-close:hover,
    .health-record-details-modal .record-modal-close:focus-visible {
        border-color: #facc15;
        background: #facc15;
        color: #70131B;
        transform: translateY(-1px);
        outline: none;
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
    .resubmission-upload-modal {
        width: min(920px, 100%);
        border-top: 0;
        border-bottom: 4px solid #facc15;
    }
    .missing-requirements-hidden-input {
        display: none;
    }
    .missing-esign-modal {
        width: min(760px, 100%);
        border-top: 0;
        border-bottom: 4px solid #facc15;
    }
    .missing-document-modal {
        width: min(915px, calc(100vw - 48px));
        height: auto;
        max-height: min(820px, calc(100vh - 36px));
        border-top: 0;
        border-bottom: 0;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        background: #ffffff;
    }
    .missing-document-modal .record-modal-head {
        min-height: 108px;
        padding: 22px 74px 22px 28px;
        background: linear-gradient(135deg, #8B0000 0%, #70131B 100%);
        border-radius: 16px 16px 0 0;
        display: flex;
        align-items: center;
        gap: 18px;
    }
    .missing-document-head-icon {
        width: 54px;
        height: 54px;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,.22);
        background: rgba(255,255,255,.08);
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }
    .missing-document-head-icon svg {
        width: 28px;
        height: 28px;
    }
    .missing-document-modal .record-modal-head-main {
        display: block;
    }
    .missing-document-modal .record-modal-title {
        color: #ffffff;
        font-size: 24px;
        line-height: 1.1;
    }
    .missing-document-modal .record-modal-subtitle {
        color: rgba(255,255,255,.92);
        max-width: 420px;
        font-size: 13px;
        line-height: 1.35;
    }
    .missing-document-modal .record-modal-close {
        display: inline-flex;
        background: rgba(112, 19, 27, .45);
        border-color: rgba(255,255,255,.18);
        color: #ffffff;
        transition: background-color .18s ease, color .18s ease, border-color .18s ease, transform .18s ease;
    }
    .missing-document-modal .record-modal-close:hover,
    .missing-document-modal .record-modal-close:focus-visible {
        background: #facc15;
        border-color: #facc15;
        color: #70131b;
        transform: translateY(-1px);
        outline: none;
    }
    .missing-document-modal .record-modal-close:active {
        transform: translateY(0) scale(.96);
    }
    .missing-document-body {
        min-height: 0;
        display: flex;
        flex: 1;
        flex-direction: column;
        padding: 22px 28px 0;
        background: #ffffff;
    }
    .missing-document-progress {
        display: grid;
        grid-template-columns: minmax(250px, 1.3fr) repeat(3, minmax(130px, .65fr));
        gap: 18px;
        align-items: center;
        padding: 12px 18px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #ffffff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, .04);
    }
    .missing-document-progress-title {
        margin: 0;
        color: #111827;
        font-size: 12px;
        font-weight: 950;
    }
    .missing-document-progress-track {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 14px;
        align-items: center;
        margin-top: 8px;
    }
    .missing-document-progress-bar {
        height: 8px;
        border-radius: 999px;
        background: #f1f5f9;
        overflow: hidden;
    }
    .missing-document-progress-fill {
        display: block;
        width: 0%;
        height: 100%;
        border-radius: inherit;
        background: #8B0000;
        transition: width .2s ease;
    }
    .missing-document-progress-count {
        color: #8B0000;
        font-size: 12px;
        font-weight: 950;
        white-space: nowrap;
    }
    .missing-document-stat {
        display: grid;
        grid-template-columns: 38px minmax(0, 1fr);
        gap: 10px;
        align-items: center;
        padding-left: 18px;
        border-left: 1px solid #e5e7eb;
    }
    .missing-document-stat-icon,
    .missing-document-row-icon,
    .missing-document-secure-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #8B0000;
        background: #fff1f2;
    }
    .missing-document-stat-icon {
        width: 34px;
        height: 34px;
        border-radius: 999px;
    }
    .missing-document-stat-icon svg,
    .missing-document-row-icon svg,
    .missing-document-secure-icon svg,
    .missing-document-drop-icon svg {
        width: 20px;
        height: 20px;
    }
    .missing-document-stat strong {
        display: block;
        color: #111827;
        font-size: 12px;
        font-weight: 950;
        line-height: 1.15;
    }
    .missing-document-stat > div > span {
        display: block;
        margin-top: 2px;
        color: #64748b;
        font-size: 11px;
        font-weight: 750;
    }
    .missing-document-scroll {
        min-height: 0;
        overflow-y: auto;
        display: grid;
        gap: 10px;
        padding: 20px 0;
    }
    .missing-document-row {
        display: grid;
        grid-template-columns: 72px minmax(220px, 1fr) minmax(280px, .9fr);
        gap: 18px;
        align-items: center;
        padding: 14px 16px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #ffffff;
    }
    .missing-document-esign-row {
        display: grid;
        grid-template-columns: 72px minmax(220px, 1fr) auto;
        gap: 18px;
        align-items: center;
        padding: 14px 16px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #ffffff;
    }
    .missing-document-esign-action {
        min-width: 130px;
        min-height: 38px;
        border: 1px solid #8B0000;
        border-radius: 7px;
        background: #8B0000;
        color: #facc15;
        padding: 8px 14px;
        font-size: 11px;
        font-weight: 950;
        cursor: pointer;
        transition: background-color .18s ease, color .18s ease, transform .18s ease, box-shadow .18s ease;
    }
    .missing-document-esign-action:hover,
    .missing-document-esign-action:focus-visible {
        border-color: #facc15;
        background: #facc15;
        color: #70131b;
        box-shadow: 0 6px 14px rgba(250, 204, 21, .28);
        transform: translateY(-1px);
        outline: none;
    }
    .missing-document-esign-action:active {
        background: #facc15;
        color: #70131b;
        transform: translateY(0) scale(.98);
    }
    .missing-document-row-icon {
        width: 56px;
        height: 56px;
        border-radius: 10px;
    }
    .missing-document-row-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        color: #111827;
        font-size: 14px;
        font-weight: 950;
        line-height: 1.2;
    }
    .missing-document-required {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: #fff1f2;
        color: #b91c1c;
        padding: 3px 7px;
        font-size: 8px;
        font-weight: 950;
        text-transform: uppercase;
    }
    .missing-document-description {
        margin: 8px 0 0;
        color: #64748b;
        font-size: 11px;
        font-weight: 750;
        line-height: 1.35;
    }
    .missing-document-meta {
        margin: 8px 0 0;
        color: #475569;
        font-size: 10px;
        font-weight: 850;
    }
    .missing-document-upload-box {
        position: relative;
        display: grid;
        place-items: center;
        min-height: 92px;
        border: 1.5px dashed #f3b4b4;
        border-radius: 10px;
        background: #fffafa;
        color: #111827;
        text-align: center;
        cursor: pointer;
    }
    .missing-document-upload-box.is-selected {
        place-items: stretch;
        padding: 12px;
        text-align: left;
        cursor: default;
    }
    .missing-document-upload-box.is-dragging {
        border-color: #8B0000;
        background: #fff1f2;
    }
    .missing-document-upload-box input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .missing-document-drop-icon {
        display: inline-flex;
        color: #8B0000;
        margin-bottom: 6px;
    }
    .missing-document-upload-title,
    .missing-document-file-name {
        display: block;
        color: #111827;
        font-size: 10px;
        font-weight: 850;
    }
    .missing-document-upload-or {
        display: block;
        margin: 3px 0;
        color: #64748b;
        font-size: 10px;
        font-weight: 750;
    }
    .missing-document-choose {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 110px;
        min-height: 26px;
        border-radius: 6px;
        background: #8B0000;
        color: #ffffff;
        padding: 5px 14px;
        font-size: 10px;
        font-weight: 950;
        transition: background-color .18s ease, color .18s ease, transform .18s ease, box-shadow .18s ease;
    }
    .missing-document-choose:hover,
    .missing-document-choose:focus-visible {
        background: #facc15;
        color: #70131b;
        box-shadow: 0 6px 14px rgba(250, 204, 21, .28);
        transform: translateY(-1px);
        outline: none;
    }
    .missing-document-choose:active {
        background: #facc15;
        color: #70131b;
        transform: translateY(0) scale(.98);
    }
    .missing-document-file-name {
        margin-top: 6px;
        color: #8B0000;
    }
    .missing-document-preview {
        display: none;
        align-items: center;
        gap: 10px;
        min-width: 0;
        height: 100%;
    }
    .missing-document-upload-box.is-selected .missing-document-preview {
        display: flex;
    }
    .missing-document-upload-box.is-selected > span:not(.missing-document-preview) {
        display: none;
    }
    .missing-document-preview-thumb {
        width: 54px;
        height: 54px;
        flex: 0 0 54px;
        display: grid;
        place-items: center;
        overflow: hidden;
        border-radius: 8px;
        background: #fff1f2;
        color: #8B0000;
        font-size: 11px;
        font-weight: 950;
        text-transform: uppercase;
    }
    .missing-document-preview-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .missing-document-preview-copy {
        min-width: 0;
        flex: 1;
    }
    .missing-document-preview-copy strong,
    .missing-document-preview-copy span {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .missing-document-preview-copy strong {
        color: #111827;
        font-size: 11px;
        font-weight: 950;
    }
    .missing-document-preview-copy span {
        margin-top: 4px;
        color: #64748b;
        font-size: 10px;
        font-weight: 750;
    }
    .missing-document-preview-actions {
        display: flex;
        flex: 0 0 auto;
        flex-direction: column;
        gap: 6px;
    }
    .missing-document-preview-actions label,
    .missing-document-preview-actions button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 66px;
        min-height: 24px;
        border: 1px solid #8B0000;
        border-radius: 5px;
        background: #8B0000;
        color: #facc15;
        padding: 4px 8px;
        font-size: 9px;
        font-weight: 950;
        cursor: pointer;
        transition: background-color .18s ease, color .18s ease, transform .18s ease, box-shadow .18s ease;
    }
    .missing-document-preview-actions button {
        background: #ffffff;
        color: #8B0000;
    }
    .missing-document-preview-actions label:hover,
    .missing-document-preview-actions label:focus-visible,
    .missing-document-preview-actions button:hover,
    .missing-document-preview-actions button:focus-visible {
        border-color: #facc15;
        background: #facc15;
        color: #70131b;
        box-shadow: 0 5px 12px rgba(250, 204, 21, .28);
        transform: translateY(-1px);
        outline: none;
    }
    .missing-document-preview-actions label:active,
    .missing-document-preview-actions button:active {
        background: #facc15;
        color: #70131b;
        transform: translateY(0) scale(.98);
    }
    .missing-document-footer {
        position: relative;
        bottom: 0;
        z-index: 2;
        display: grid;
        grid-template-columns: minmax(220px, 1fr) auto;
        gap: 18px;
        align-items: center;
        padding: 16px 0 20px;
        border-top: 1px solid #f1f5f9;
        background: #ffffff;
    }
    .missing-document-secure {
        display: grid;
        grid-template-columns: 44px minmax(0, 1fr);
        gap: 12px;
        align-items: center;
        max-width: 330px;
        border-radius: 10px;
        background: #fff7f7;
        padding: 14px 16px;
    }
    .missing-document-secure-icon {
        width: 36px;
        height: 36px;
        border-radius: 999px;
        background: #ffffff;
    }
    .missing-document-secure strong {
        display: block;
        color: #111827;
        font-size: 12px;
        font-weight: 950;
    }
    .missing-document-secure span {
        display: block;
        margin-top: 4px;
        color: #64748b;
        font-size: 10px;
        font-weight: 750;
        line-height: 1.35;
    }
    .missing-document-field {
        width: 100%;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
        padding: 10px;
        color: #111827;
        font-size: 13px;
        font-weight: 700;
    }
    .missing-esign-body {
        padding: 24px;
    }
    .missing-esign-card {
        border: 1px solid rgba(112, 19, 27, .12);
        border-radius: 12px;
        background: #ffffff;
        padding: 14px;
        box-shadow: 0 12px 26px rgba(15, 23, 42, .06);
    }
    .missing-esign-label {
        display: block;
        margin-bottom: 8px;
        color: #70131B;
        font-size: 12px;
        font-weight: 950;
    }
    .missing-esign-upload {
        width: 100%;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
        padding: 10px;
        color: #111827;
        font-size: 13px;
        font-weight: 700;
    }
    .missing-esign-hint {
        margin: 7px 0 0;
        color: #64748b;
        font-size: 11px;
        font-weight: 750;
    }
    .missing-esign-methods {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 14px;
    }
    .missing-esign-methods input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .missing-esign-methods label {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 44px;
        border: 1px solid rgba(112, 19, 27, .16);
        border-radius: 12px;
        background: #fff;
        color: #70131B;
        font-size: 13px;
        font-weight: 950;
        cursor: pointer;
    }
    .missing-esign-methods input:checked + label {
        background: #8B0000;
        border-color: #8B0000;
        color: #facc15;
        box-shadow: 0 14px 28px rgba(139, 0, 0, .18);
    }
    .missing-esign-pad-wrap {
        border: 1px dashed #fca5a5;
        border-radius: 12px;
        background: #fffafa;
        padding: 10px;
    }
    #missingESignPad {
        width: 100%;
        height: 180px;
        display: block;
        border-radius: 8px;
        background: #ffffff;
        touch-action: none;
        cursor: crosshair;
    }
    .missing-esign-panel.is-hidden {
        display: none;
    }
    .missing-esign-actions,
    .missing-upload-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 18px;
    }
    .missing-esign-secondary,
    .missing-esign-submit {
        border-radius: 10px;
        padding: 11px 16px;
        font-size: 13px;
        font-weight: 950;
        cursor: pointer;
    }
    .missing-esign-secondary {
        border: 1px solid rgba(112, 19, 27, .16);
        background: #ffffff;
        color: #70131B;
    }
    .missing-esign-submit {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        border: 1px solid #8B0000;
        background: #8B0000;
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-width: 230px;
        transition: background-color .18s ease, color .18s ease, transform .18s ease, box-shadow .18s ease;
    }
    .missing-document-footer .missing-esign-submit::after {
        content: '';
        position: absolute;
        z-index: -1;
        top: -45%;
        bottom: -45%;
        left: -48%;
        width: 34%;
        pointer-events: none;
        opacity: 0;
        transform: skewX(-20deg);
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .78), transparent);
    }
    .missing-document-footer .missing-esign-submit:hover,
    .missing-document-footer .missing-esign-submit:focus-visible {
        border-color: #facc15;
        background: #facc15;
        color: #70131b;
        box-shadow: 0 6px 14px rgba(250, 204, 21, .28);
        transform: translateY(-1px);
        outline: none;
    }
    .missing-document-footer .missing-esign-submit:hover::after,
    .missing-document-footer .missing-esign-submit:focus-visible::after {
        animation: missingDocumentSubmitSweep .68s ease-out;
    }
    @keyframes missingDocumentSubmitSweep {
        0% { left: -48%; opacity: 0; }
        18% { opacity: .9; }
        100% { left: 120%; opacity: 0; }
    }
    .missing-document-footer .missing-esign-submit:active {
        background: #facc15;
        color: #70131b;
        transform: translateY(0) scale(.98);
    }
    .missing-esign-submit svg {
        width: 17px;
        height: 17px;
    }
    .missing-esign-modal .record-modal-close {
        transition: background-color .18s ease, color .18s ease, border-color .18s ease, transform .18s ease;
    }
    .missing-esign-modal .record-modal-close:hover,
    .missing-esign-modal .record-modal-close:focus-visible {
        background: #facc15;
        border-color: #facc15;
        color: #70131b;
        transform: translateY(-1px);
        outline: none;
    }
    .missing-esign-modal .record-modal-close:active {
        transform: translateY(0) scale(.96);
    }
    .missing-esign-modal .missing-esign-submit:hover,
    .missing-esign-modal .missing-esign-submit:focus-visible {
        border-color: #facc15;
        background: #facc15;
        color: #70131b;
        box-shadow: 0 6px 14px rgba(250, 204, 21, .28);
        transform: translateY(-1px);
        outline: none;
    }
    .missing-esign-modal .missing-esign-submit:active {
        background: #facc15;
        color: #70131b;
        transform: translateY(0) scale(.98);
    }
    @media (max-width: 640px) {
        .missing-document-modal {
            width: calc(100vw - 24px);
            height: auto;
            max-height: calc(100vh - 24px);
        }
        .missing-document-progress,
        .missing-document-row,
        .missing-document-esign-row,
        .missing-document-footer {
            grid-template-columns: minmax(0, 1fr) !important;
        }
        .missing-document-row,
        .missing-document-esign-row {
            min-width: 0;
        }
        .missing-document-upload-box {
            width: 100%;
            min-width: 0;
        }
        .missing-upload-actions {
            width: 100%;
            flex-direction: column !important;
            align-items: stretch;
        }
        .missing-upload-actions .missing-esign-secondary,
        .missing-upload-actions .missing-esign-submit {
            width: 100%;
            min-width: 0;
        }
    }
    .resubmission-upload-modal .record-modal-head {
        min-height: 108px;
        padding: 24px 72px 22px 30px;
        display: flex;
        align-items: center;
        gap: 18px;
    }
    .resubmission-head-icon,
    .resubmission-doc-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }
    .resubmission-head-icon {
        width: 54px;
        height: 54px;
        border-radius: 18px;
        color: #ffffff;
        background: rgba(255, 255, 255, .12);
        border: 1px solid rgba(255, 255, 255, .18);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .14);
    }
    .resubmission-head-icon svg {
        width: 28px;
        height: 28px;
        stroke-width: 1.8;
    }
    .resubmission-progress-card,
    .resubmission-note-card,
    .resubmission-doc-card,
    .resubmission-selected-summary {
        border: 1px solid rgba(112, 19, 27, .12);
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 12px 26px rgba(15, 23, 42, .06);
    }
    .resubmission-progress-card {
        display: grid;
        grid-template-columns: auto auto minmax(0, 1fr) auto;
        align-items: center;
        gap: 18px;
        padding: 14px 18px;
        margin-bottom: 18px;
    }
    .resubmission-progress-icon,
    .resubmission-note-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }
    .resubmission-progress-icon {
        color: #be123c;
        background: #fff1f2;
    }
    .resubmission-progress-icon svg,
    .resubmission-note-icon svg,
    .resubmission-doc-icon svg,
    .resubmission-remove-file svg {
        width: 20px;
        height: 20px;
    }
    .resubmission-progress-label,
    .resubmission-progress-count,
    .resubmission-note-title,
    .resubmission-doc-title,
    .resubmission-selected-title {
        display: block;
        color: #70131B;
        font-weight: 900;
    }
    .resubmission-progress-label {
        color: #64748b;
        font-size: 11px;
    }
    .resubmission-progress-count {
        font-size: 13px;
    }
    .resubmission-progress-track {
        height: 8px;
        border-radius: 999px;
        background: #f9dce0;
        overflow: hidden;
    }
    .resubmission-progress-fill {
        display: block;
        height: 100%;
        width: 0%;
        border-radius: inherit;
        background: linear-gradient(90deg, #70131B, #a2162b);
        transition: width .24s ease;
    }
    .resubmission-progress-percent {
        min-width: 44px;
        color: #70131B;
        font-size: 15px;
        font-weight: 950;
        text-align: right;
    }
    .resubmission-note-card {
        position: relative;
        display: flex;
        gap: 14px;
        padding: 16px 18px;
        margin-bottom: 22px;
        border-color: rgba(250, 204, 21, .55);
        background: linear-gradient(135deg, #fff8dd, #ffffff);
        overflow: hidden;
    }
    .resubmission-note-card::after {
        content: "";
        position: absolute;
        right: 18px;
        bottom: -12px;
        width: 84px;
        height: 70px;
        border: 3px solid rgba(250, 204, 21, .18);
        border-radius: 18px;
        opacity: .7;
    }
    .resubmission-note-icon {
        color: #f59e0b;
        background: #ffffff;
        border: 1px solid rgba(250, 204, 21, .45);
    }
    .resubmission-note-title {
        margin-bottom: 5px;
        font-size: 13px;
    }
    .resubmission-note-text {
        margin: 0;
        color: #4b5563;
        font-size: 12px;
        font-weight: 750;
        line-height: 1.55;
        max-width: 680px;
    }
    .resubmission-doc-list {
        display: grid;
        gap: 14px;
    }
    .resubmission-doc-card {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(250px, .9fr);
        align-items: stretch;
        gap: 20px;
        padding: 18px;
    }
    .resubmission-doc-info {
        display: grid;
        grid-template-columns: 58px minmax(0, 1fr);
        gap: 16px;
        align-items: start;
        min-width: 0;
        padding-right: 8px;
        border-right: 1px solid rgba(112, 19, 27, .12);
    }
    .resubmission-doc-icon {
        width: 54px;
        height: 54px;
        border-radius: 14px;
        background: #fff1f2;
        color: #be123c;
        box-shadow: 0 10px 20px rgba(112, 19, 27, .08);
    }
    .resubmission-doc-title {
        margin: 0 0 6px;
        font-size: 14px;
        line-height: 1.25;
    }
    .resubmission-needed-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        width: fit-content;
        margin-bottom: 10px;
        padding: 4px 8px;
        border-radius: 999px;
        background: #ffe4e6;
        color: #be123c;
        font-size: 10px;
        font-weight: 950;
    }
    .resubmission-needed-badge::before {
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 999px;
        background: currentColor;
    }
    .resubmission-doc-reason,
    .resubmission-doc-hint {
        margin: 0;
        color: #475569;
        font-size: 12px;
        font-weight: 750;
        line-height: 1.45;
    }
    .resubmission-doc-hint {
        margin-top: 10px;
        color: #64748b;
        font-size: 11px;
        font-weight: 850;
    }
    .resubmission-upload-zone {
        position: relative;
        min-height: 112px;
        border: 1px dashed rgba(190, 18, 60, .36);
        border-radius: 10px;
        background: linear-gradient(180deg, #fffafa, #ffffff);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 5px;
        color: #70131B;
        text-align: center;
        cursor: pointer;
        transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
    }
    .resubmission-upload-zone:hover,
    .resubmission-upload-zone:focus-within,
    .resubmission-doc-card.has-file .resubmission-upload-zone {
        border-color: #facc15;
        background: #fffdf2;
        box-shadow: 0 0 0 4px rgba(250, 204, 21, .12);
    }
    .resubmission-doc-card.has-file .resubmission-upload-zone > svg,
    .resubmission-doc-card.has-file .resubmission-upload-copy,
    .resubmission-doc-card.has-file .resubmission-upload-or,
    .resubmission-doc-card.has-file .resubmission-choose-file {
        display: none;
    }
    .resubmission-upload-zone svg {
        width: 24px;
        height: 24px;
    }
    .resubmission-upload-copy {
        color: #70131B;
        font-size: 11px;
        font-weight: 850;
    }
    .resubmission-upload-or {
        color: #64748b;
        font-size: 10px;
        font-weight: 850;
    }
    .resubmission-file-input {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
    }
    .resubmission-choose-file {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 92px;
        min-height: 28px;
        padding: 7px 12px;
        border-radius: 6px;
        background: #70131B;
        color: #ffffff;
        font-size: 11px;
        font-weight: 900;
    }
    .resubmission-file-preview {
        display: none;
        width: 100%;
        height: 100%;
        min-height: 96px;
        grid-template-columns: 54px minmax(0, 1fr) auto auto;
        align-items: center;
        gap: 10px;
        padding: 10px;
        color: #475569;
        font-size: 11px;
        font-weight: 800;
        text-align: left;
        pointer-events: none;
    }
    .resubmission-file-preview.is-visible {
        display: grid;
    }
    .resubmission-file-thumb {
        width: 54px;
        height: 54px;
        border-radius: 10px;
        border: 1px solid #f0c9ce;
        background: #fff7ed;
        color: #70131B;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        font-size: 11px;
        font-weight: 950;
        text-transform: uppercase;
    }
    .resubmission-file-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .resubmission-file-name {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #0f172a;
        font-weight: 900;
    }
    .resubmission-ready {
        color: #16a34a;
        font-weight: 900;
        white-space: nowrap;
    }
    .resubmission-remove-file {
        width: 26px;
        height: 26px;
        border: 0;
        border-radius: 8px;
        background: #fff1f2;
        color: #be123c;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        pointer-events: auto;
        transition: background .18s ease, color .18s ease, transform .18s ease;
    }
    .resubmission-remove-file:hover {
        background: #be123c;
        color: #ffffff;
        transform: translateY(-1px);
    }
    .resubmission-error {
        display: block;
        margin-top: 8px;
        color: #be123c;
        font-size: 12px;
        font-weight: 850;
    }
    .resubmission-footer-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 18px;
        align-items: center;
        margin-top: 20px;
        padding-top: 18px;
        border-top: 1px solid rgba(112, 19, 27, .12);
    }
    .resubmission-selected-summary {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        background: #fff7f7;
    }
    .resubmission-selected-summary svg {
        width: 24px;
        height: 24px;
        color: #be123c;
        flex: 0 0 auto;
    }
    .resubmission-selected-title {
        font-size: 12px;
    }
    .resubmission-selected-help,
    .resubmission-secure-note {
        margin: 0;
        color: #64748b;
        font-size: 11px;
        font-weight: 750;
    }
    .resubmission-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }
    .resubmission-cancel,
    .resubmission-submit {
        min-height: 44px;
        border-radius: 10px;
        padding: 0 22px;
        font-size: 13px;
        font-weight: 950;
        cursor: pointer;
        transition: background-color .2s ease, color .2s ease, border-color .2s ease, transform .2s ease, opacity .2s ease;
    }
    .resubmission-cancel {
        border: 1px solid rgba(112, 19, 27, .24);
        background: #ffffff;
        color: #70131B;
    }
    .resubmission-submit {
        border: 1px solid #70131B;
        background: #70131B;
        color: #ffffff;
    }
    .resubmission-cancel:hover,
    .resubmission-submit:hover:not(:disabled) {
        background: #facc15;
        color: #70131B;
        border-color: #facc15;
        transform: translateY(-1px);
    }
    .resubmission-submit:disabled {
        cursor: not-allowed;
        opacity: .58;
    }
    .resubmission-secure-note {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        margin-top: 16px;
    }
    .resubmission-secure-note svg {
        width: 14px;
        height: 14px;
    }
    @media (max-width: 760px) {
        .resubmission-upload-modal .record-modal-head {
            padding: 20px 64px 18px 20px;
        }
        .resubmission-progress-card,
        .resubmission-doc-card,
        .resubmission-footer-row {
            grid-template-columns: 1fr;
        }
        .resubmission-progress-percent {
            text-align: left;
        }
        .resubmission-doc-info {
            border-right: 0;
            border-bottom: 1px solid rgba(112, 19, 27, .12);
            padding-right: 0;
            padding-bottom: 14px;
        }
        .resubmission-actions {
            display: grid;
            grid-template-columns: 1fr;
        }
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

    /* --- HEALTH RECORD REVIEW --- */
    .account-layout.health-record-layout {
        max-width: 1180px;
    }
    .health-record-dashboard {
        width: 100%;
    }
    .health-record-hero.page-hero {
        position: relative;
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(270px, 340px);
        align-items: stretch;
        gap: 28px;
        min-height: 205px;
        margin: -12px 0 18px;
        padding: 28px 30px;
        overflow: hidden;
        border: 1px solid rgba(250, 204, 21, .28);
        border-radius: 10px;
        background:
            linear-gradient(90deg, rgba(111, 0, 31, .99) 0%, rgba(105, 0, 29, .97) 50%, rgba(71, 0, 22, .79) 100%),
            url('{{ asset("images/PUPBG.jpg") }}') right 42% / 58% auto no-repeat;
        background-blend-mode: normal, luminosity;
        box-shadow: 0 18px 38px rgba(76, 5, 25, .2);
        color: #ffffff;
    }
    .health-record-hero.page-hero::before {
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
    .health-record-hero.page-hero::after {
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
    .health-record-hero-main,
    .health-record-hero-overview {
        position: relative;
        z-index: 2;
    }
    .health-record-hero-main {
        display: grid;
        grid-template-columns: 112px minmax(0, 1fr);
        align-items: center;
        gap: 22px;
        min-width: 0;
    }
    .health-record-hero-emblem {
        width: 106px;
        height: 106px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 2px solid rgba(255,255,255,.88);
        border-radius: 50%;
        background: linear-gradient(145deg, rgba(255,255,255,.97), rgba(255,239,241,.92));
        color: #8b0018;
        box-shadow: 0 14px 30px rgba(27, 0, 9, .28);
    }
    .health-record-hero-emblem svg {
        width: 58px;
        height: 58px;
        stroke-width: 1.35;
    }
    .health-record-hero-copy { min-width: 0; }
    .health-record-hero .page-hero-kicker {
        padding: 0;
        margin: 0 0 6px;
        border-radius: 0;
        background: transparent;
        color: #facc15;
        font-size: 10px;
        letter-spacing: .08em;
    }
    .health-record-hero .page-hero-title {
        margin: 0 0 6px;
        color: #ffffff;
        font-size: clamp(26px, 3vw, 34px);
        font-weight: 900;
        letter-spacing: 0;
    }
    .health-record-hero .page-hero-text {
        max-width: 520px;
        color: rgba(255,255,255,.88);
        font-size: 13px;
        line-height: 1.55;
    }
    .health-record-hero .page-hero-steps {
        gap: 7px;
        margin-top: 13px;
    }
    .health-record-hero .page-hero-step {
        min-height: 30px;
        padding: 6px 10px;
        border-color: rgba(255,255,255,.1);
        border-radius: 999px;
        background: rgba(255,255,255,.1);
        color: rgba(255,255,255,.78);
        box-shadow: none;
        font-size: 10px;
    }
    .health-record-hero .page-hero-step::before {
        width: 17px;
        height: 17px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,.94);
        color: #7f001d;
        box-shadow: none;
        content: "";
        font-size: 11px;
        font-weight: 900;
    }
    .health-record-hero .page-hero-step.is-complete {
        color: #ffffff;
        background: rgba(255,255,255,.16);
    }
    .health-record-hero .page-hero-step.is-complete::before { content: "\2713"; }
    .health-record-hero .page-hero-step.is-active {
        color: #fff6bf;
        border-color: rgba(250,204,21,.42);
        background: rgba(250,204,21,.13);
    }
    .health-record-hero .page-hero-step.is-active::before {
        background: #facc15;
        box-shadow: 0 0 0 4px rgba(250,204,21,.12);
    }
    .health-record-hero-overview {
        display: grid;
        align-content: center;
        border-left: 1px solid rgba(255,255,255,.18);
        padding-left: 26px;
    }
    .health-record-hero-overview-item {
        display: grid;
        grid-template-columns: 42px minmax(0, 1fr);
        align-items: center;
        gap: 12px;
        min-width: 0;
        padding: 14px 0;
    }
    .health-record-hero-overview-item + .health-record-hero-overview-item {
        border-top: 1px solid rgba(255,255,255,.14);
    }
    .health-record-hero-overview-icon {
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(250,204,21,.26);
        border-radius: 50%;
        background: rgba(27, 0, 9, .22);
        color: #facc15;
    }
    .health-record-hero-overview-icon.is-approved {
        color: #22c55e;
        border-color: rgba(34,197,94,.42);
        background: rgba(10,75,31,.28);
    }
    .health-record-hero-overview-icon.is-pending {
        color: #facc15;
        border-color: rgba(250,204,21,.38);
        background: rgba(113,63,18,.28);
    }
    .health-record-hero-overview-icon.is-rejected {
        color: #fecaca;
        border-color: rgba(248,113,113,.4);
        background: rgba(127,29,29,.3);
    }
    .health-record-hero-overview-icon svg {
        width: 20px;
        height: 20px;
        stroke-width: 2;
    }
    .health-record-hero-overview-label {
        display: block;
        margin-bottom: 3px;
        color: rgba(255,255,255,.68);
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
    }
    .health-record-hero-overview-value {
        display: block;
        color: #ffffff;
        font-size: 14px;
        font-weight: 850;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }
    .health-record-content-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 292px;
        gap: 16px;
        align-items: start;
    }
    .health-record-main-column,
    .health-record-sidebar {
        display: grid;
        gap: 14px;
        min-width: 0;
    }
    .health-record-dashboard .health-declaration-card,
    .health-record-dashboard .missing-requirements-panel,
    .health-record-dashboard .health-status-card {
        margin: 0;
        border-radius: 8px;
    }
    .health-record-dashboard .health-status-card {
        padding: 0;
        overflow: hidden;
        border-color: rgba(112,19,27,.13);
        box-shadow: 0 14px 30px rgba(39, 13, 20, .09);
    }
    .health-record-dashboard .health-status-head {
        margin: 0;
        padding: 18px 20px 14px;
        border-bottom: 1px solid #e8edf2;
    }
    .health-record-dashboard .health-status-title {
        color: #7f001d;
        font-size: 17px;
    }
    .health-record-dashboard .health-status-summary {
        display: flex;
        align-items: center;
        gap: 14px;
        min-height: 52px;
        margin: 0 0 14px;
        padding: 9px 14px;
        border: 1px solid rgba(34,197,94,.12);
        border-radius: 7px;
        background: linear-gradient(90deg, #effaf0, #f7fcf7);
    }
    .health-record-status-body {
        padding: 16px 16px 0;
    }
    .health-record-dashboard .health-status-summary.is-approved .health-status-state {
        gap: 10px;
        padding: 0 18px 0 0;
        border-right: 1px solid rgba(22,163,74,.2);
        border-radius: 0;
        background: transparent;
        color: #159436;
    }
    .health-record-dashboard .health-status-summary.is-approved .health-status-state svg {
        width: 36px;
        height: 36px;
        padding: 8px;
        border-radius: 50%;
        box-sizing: border-box;
        background: #e3f7e7;
        color: #16a34a;
        stroke-width: 2;
    }
    .health-record-dashboard .health-status-summary.is-pending {
        border-color: rgba(245,158,11,.18);
        background: linear-gradient(90deg, #fff9e8, #fffdf5);
    }
    .health-record-dashboard .health-status-summary.is-pending .health-status-message { color: #865711; }
    .health-record-dashboard .health-status-summary.is-incomplete {
        border-color: rgba(239,68,68,.16);
        background: linear-gradient(90deg, #fff3f3, #fffafa);
    }
    .health-record-dashboard .health-status-summary.is-incomplete .health-status-message { color: #991b1b; }
    .health-record-dashboard .health-status-state {
        flex: 0 0 auto;
        padding: 6px 9px;
        border-radius: 6px;
    }
    .health-record-dashboard .health-status-message {
        margin: 0;
        color: #357146;
        font-size: 12px;
        line-height: 1.45;
    }
    .health-record-dashboard .health-status-meta-grid {
        gap: 9px;
        margin: 0;
    }
    .health-record-dashboard .health-status-meta {
        min-height: 78px;
        display: grid;
        grid-template-columns: 46px minmax(0, 1fr);
        align-items: center;
        gap: 12px;
        padding: 11px 12px;
        border-radius: 7px;
        border-color: rgba(112,19,27,.11);
        background: #ffffff;
        box-shadow: 0 8px 18px rgba(15,23,42,.045);
    }
    .health-status-meta-icon {
        width: 46px;
        height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: #fff0f2;
        color: #a40021;
    }
    .health-status-meta-icon svg {
        width: 25px;
        height: 25px;
        stroke-width: 1.8;
    }
    .health-status-meta-copy { min-width: 0; }
    .health-record-dashboard .health-status-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(165px, 1fr));
        gap: 9px;
        margin-top: 14px;
    }
    .health-record-action-card {
        width: 100%;
        min-height: 84px;
        display: grid;
        grid-template-columns: 48px minmax(0, 1fr) 18px;
        align-items: center;
        gap: 11px;
        padding: 12px;
        border: 1px solid rgba(176,0,32,.16);
        border-radius: 7px;
        box-sizing: border-box;
        background: linear-gradient(135deg, #fff8f8, #ffffff);
        box-shadow: 0 8px 18px rgba(15,23,42,.045);
        color: #64111c;
        font: inherit;
        text-align: left;
        text-decoration: none;
        cursor: pointer;
        transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
    }
    .health-record-action-card:hover {
        transform: translateY(-2px);
        border-color: rgba(176,0,32,.3);
        box-shadow: 0 12px 22px rgba(69,12,24,.1);
    }
    .health-record-action-icon {
        width: 48px;
        height: 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: #ffe9ed;
        color: #a90025;
    }
    .health-record-action-icon svg {
        width: 25px;
        height: 25px;
        stroke-width: 2;
    }
    .health-record-action-copy { min-width: 0; }
    .health-record-action-title {
        display: block;
        margin-bottom: 4px;
        color: inherit;
        font-size: 12px;
        font-weight: 900;
        line-height: 1.3;
    }
    .health-record-action-description {
        display: block;
        color: #374151;
        font-size: 10px;
        line-height: 1.55;
    }
    .health-record-action-arrow {
        color: inherit;
    }
    .health-record-action-arrow svg {
        width: 17px;
        height: 17px;
        stroke-width: 2;
    }
    .health-record-action-card.is-edit {
        border-color: rgba(217,119,6,.22);
        background: linear-gradient(135deg, #fffaf0, #ffffff);
        color: #8b3f05;
    }
    .health-record-action-card.is-edit .health-record-action-icon {
        background: #fff0c9;
        color: #e27800;
    }
    .health-record-action-card.is-download {
        border-color: rgba(22,163,74,.2);
        background: linear-gradient(135deg, #f3fcf2, #ffffff);
        color: #147229;
    }
    .health-record-action-card.is-download .health-record-action-icon {
        background: #e3f6df;
        color: #25a73b;
    }
    .health-correction-notice {
        display: grid;
        grid-template-columns: 24px minmax(0, 1fr) auto;
        align-items: center;
        gap: 10px;
        margin-top: 14px;
        padding: 8px 10px;
        border: 1px solid rgba(245,158,11,.18);
        border-radius: 7px;
        background: linear-gradient(90deg, #fff9ed, #fffcf6);
        color: #a44b08;
    }
    .health-correction-notice > svg {
        width: 19px;
        height: 19px;
        color: #f59e0b;
        stroke-width: 2;
    }
    .health-correction-copy {
        min-width: 0;
        display: flex;
        align-items: baseline;
        gap: 12px;
        font-size: 10px;
        line-height: 1.45;
    }
    .health-correction-copy strong {
        color: #d36100;
        font-size: 11px;
        white-space: nowrap;
    }
    .health-correction-details {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 10px;
        border: 1px solid rgba(217,119,6,.3);
        border-radius: 999px;
        background: #ffffff;
        color: #7f3010;
        font-size: 10px;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
        transition: border-color .2s ease, background .2s ease;
    }
    .health-correction-details:hover {
        border-color: #f59e0b;
        background: #fff8df;
    }
    .health-correction-details svg {
        width: 13px;
        height: 13px;
        stroke-width: 2;
    }
    .health-record-dashboard .health-status-note {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 14px -16px 0;
        padding: 11px 16px;
        border-top: 1px solid #e8edf2;
        font-size: 11px;
    }
    .health-record-dashboard .health-status-note svg {
        width: 16px;
        height: 16px;
        color: #64748b;
        stroke-width: 1.8;
    }
    .health-record-side-card {
        padding: 18px;
        border: 1px solid rgba(112,19,27,.13);
        border-radius: 8px;
        background: rgba(255,255,255,.96);
        box-shadow: 0 14px 30px rgba(39,13,20,.08);
    }
    .health-record-side-card.is-reminder {
        background: linear-gradient(160deg, #fffafa, #fffdfd);
    }
    .health-record-side-title {
        display: flex;
        align-items: center;
        gap: 9px;
        margin: 0 0 16px;
        color: #7f001d;
        font-size: 14px;
        font-weight: 900;
    }
    .health-record-side-title-icon {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 7px;
        background: #fff1f2;
        color: #b00020;
    }
    .health-record-side-title-icon.is-yellow {
        background: #fff8df;
        color: #d97706;
    }
    .health-record-side-title-icon svg {
        width: 17px;
        height: 17px;
        stroke-width: 2;
    }
    .health-record-progress-list {
        display: grid;
        gap: 0;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    .health-record-progress-item {
        position: relative;
        display: grid;
        grid-template-columns: 16px minmax(0, 1fr) 22px;
        gap: 9px;
        min-height: 62px;
        padding-bottom: 12px;
    }
    .health-record-progress-item:last-child {
        min-height: 38px;
        padding-bottom: 0;
    }
    .health-record-progress-item::after {
        content: "";
        position: absolute;
        top: 14px;
        bottom: 0;
        left: 6px;
        width: 2px;
        background: #dbe3e8;
    }
    .health-record-progress-item:last-child::after { display: none; }
    .health-record-progress-dot {
        position: relative;
        z-index: 1;
        width: 12px;
        height: 12px;
        margin-top: 3px;
        border: 3px solid #ffffff;
        border-radius: 50%;
        background: #cbd5e1;
        box-shadow: 0 0 0 1px #cbd5e1;
    }
    .health-record-progress-item.is-complete .health-record-progress-dot {
        background: #22c55e;
        box-shadow: 0 0 0 1px #22c55e;
    }
    .health-record-progress-item.is-active .health-record-progress-dot {
        background: #facc15;
        box-shadow: 0 0 0 1px #d4a700, 0 0 0 5px rgba(250,204,21,.13);
        animation: healthRecordPulse 1.8s ease-in-out infinite;
    }
    .health-record-progress-copy strong {
        display: block;
        margin-bottom: 4px;
        color: #1f2937;
        font-size: 12px;
        font-weight: 900;
    }
    .health-record-progress-copy span {
        display: block;
        color: #64748b;
        font-size: 10px;
        line-height: 1.35;
    }
    .health-record-progress-check {
        width: 20px;
        height: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #bbefc5;
        border-radius: 50%;
        color: #16a34a;
    }
    .health-record-progress-check svg {
        width: 12px;
        height: 12px;
        stroke-width: 2.5;
    }
    .health-record-reminder-copy {
        margin: 0;
        color: #334155;
        font-size: 11px;
        line-height: 1.65;
    }
    .health-record-reminder-copy + .health-record-reminder-copy {
        margin-top: 13px;
        padding-top: 13px;
        border-top: 1px solid rgba(112,19,27,.1);
    }
    .health-record-reminder-copy strong {
        color: #7f001d;
    }
    @keyframes healthRecordPulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.12); }
    }
    html[data-theme="dark"] .health-record-hero.page-hero {
        border-color: rgba(250,204,21,.28);
        background:
            linear-gradient(90deg, rgba(43,0,14,.99) 0%, rgba(55,0,18,.97) 52%, rgba(14,8,18,.82) 100%),
            url('{{ asset("images/PUPBG.jpg") }}') right 42% / 58% auto no-repeat;
    }
    html[data-theme="dark"] .health-record-dashboard .health-status-card,
    html[data-theme="dark"] .health-record-side-card {
        border-color: rgba(250,204,21,.16);
        background: #101722;
        box-shadow: 0 16px 34px rgba(0,0,0,.34);
    }
    html[data-theme="dark"] .health-record-side-card.is-reminder { background: #111620; }
    html[data-theme="dark"] .health-record-dashboard .health-status-head,
    html[data-theme="dark"] .health-record-dashboard .health-status-note {
        border-color: rgba(255,255,255,.1);
    }
    html[data-theme="dark"] .health-record-dashboard .health-status-summary {
        border-color: rgba(34,197,94,.18);
        background: rgba(22,101,52,.16);
    }
    html[data-theme="dark"] .health-record-dashboard .health-status-message { color: #bbf7d0; }
    html[data-theme="dark"] .health-record-dashboard .health-status-summary.is-pending {
        border-color: rgba(250,204,21,.2);
        background: rgba(113,63,18,.22);
    }
    html[data-theme="dark"] .health-record-dashboard .health-status-summary.is-pending .health-status-message { color: #fde68a; }
    html[data-theme="dark"] .health-record-dashboard .health-status-summary.is-incomplete {
        border-color: rgba(248,113,113,.2);
        background: rgba(127,29,29,.2);
    }
    html[data-theme="dark"] .health-record-dashboard .health-status-summary.is-incomplete .health-status-message { color: #fecaca; }
    html[data-theme="dark"] .health-status-meta-icon {
        background: rgba(127,29,45,.32);
        color: #fda4af;
    }
    html[data-theme="dark"] .health-record-action-card {
        border-color: rgba(250,204,21,.15);
        background: rgba(5,8,13,.48);
        color: #f8fafc;
        box-shadow: 0 10px 20px rgba(0,0,0,.24);
    }
    html[data-theme="dark"] .health-record-action-card:hover {
        border-color: rgba(250,204,21,.34);
        box-shadow: 0 14px 26px rgba(0,0,0,.34);
    }
    html[data-theme="dark"] .health-record-action-description { color: #cbd5e1; }
    html[data-theme="dark"] .health-record-action-card.is-edit,
    html[data-theme="dark"] .health-record-action-card.is-download { background: rgba(5,8,13,.48); }
    html[data-theme="dark"] .health-correction-notice {
        border-color: rgba(250,204,21,.2);
        background: rgba(113,63,18,.2);
        color: #fde68a;
    }
    html[data-theme="dark"] .health-correction-copy strong { color: #fbbf24; }
    html[data-theme="dark"] .health-correction-details {
        border-color: rgba(250,204,21,.3);
        background: #101722;
        color: #fde68a;
    }
    html[data-theme="dark"] .health-record-progress-copy strong,
    html[data-theme="dark"] .health-record-reminder-copy { color: #e5e7eb; }
    html[data-theme="dark"] .health-record-progress-copy span { color: #94a3b8; }
    html[data-theme="dark"] .health-record-progress-dot { border-color: #101722; }
    html[data-theme="dark"] .health-record-progress-item::after { background: #334155; }
    html[data-theme="dark"] .health-record-reminder-copy strong,
    html[data-theme="dark"] .health-record-side-title { color: #facc15; }
    html[data-theme="dark"] .health-record-reminder-copy + .health-record-reminder-copy {
        border-color: rgba(255,255,255,.1);
    }
    @media (max-width: 980px) {
        .health-record-hero.page-hero {
            grid-template-columns: minmax(0, 1fr) 260px;
            gap: 20px;
            padding: 24px;
        }
        .health-record-hero-main {
            grid-template-columns: 88px minmax(0, 1fr);
            gap: 18px;
        }
        .health-record-hero-emblem {
            width: 84px;
            height: 84px;
        }
        .health-record-hero-emblem svg {
            width: 46px;
            height: 46px;
        }
        .health-record-content-grid {
            grid-template-columns: minmax(0, 1fr) 250px;
        }
    }
    @media (max-width: 820px) {
        .health-record-hero.page-hero,
        .health-record-content-grid {
            grid-template-columns: 1fr;
        }
        .health-record-hero-overview {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            border-top: 1px solid rgba(255,255,255,.18);
            border-left: 0;
            padding: 6px 0 0;
        }
        .health-record-hero-overview-item + .health-record-hero-overview-item {
            border-top: 0;
            border-left: 1px solid rgba(255,255,255,.14);
            padding-left: 18px;
        }
        .health-record-sidebar {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 620px) {
        .health-record-hero.page-hero {
            min-height: 0;
            margin-top: -8px;
            padding: 20px 17px;
        }
        .health-record-hero-main {
            grid-template-columns: 64px minmax(0, 1fr);
            align-items: start;
            gap: 13px;
        }
        .health-record-hero-emblem {
            width: 62px;
            height: 62px;
        }
        .health-record-hero-emblem svg {
            width: 34px;
            height: 34px;
        }
        .health-record-hero .page-hero-title { font-size: 25px; }
        .health-record-hero .page-hero-steps {
            grid-column: 1 / -1;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        .health-record-hero .page-hero-step {
            width: auto;
            min-width: 0;
            justify-content: center;
            padding-inline: 6px;
            white-space: nowrap;
        }
        .health-record-hero-overview {
            grid-template-columns: 1fr;
        }
        .health-record-hero-overview-item + .health-record-hero-overview-item {
            border-top: 1px solid rgba(255,255,255,.14);
            border-left: 0;
            padding-left: 0;
        }
        .health-record-sidebar,
        .health-record-dashboard .health-status-actions {
            grid-template-columns: 1fr;
        }
        .health-record-dashboard .health-status-card,
        .health-record-side-card { padding: 16px; }
        .health-record-dashboard .health-status-summary {
            align-items: flex-start;
            flex-direction: column;
            gap: 8px;
        }
        .health-record-dashboard .health-status-summary.is-approved .health-status-state {
            width: 100%;
            padding: 0 0 8px;
            border-right: 0;
            border-bottom: 1px solid rgba(22,163,74,.2);
        }
        .health-record-dashboard .health-status-meta-grid { grid-template-columns: 1fr; }
        .health-correction-notice {
            grid-template-columns: 24px minmax(0, 1fr);
        }
        .health-correction-copy {
            display: grid;
            gap: 3px;
        }
        .health-correction-copy strong { white-space: normal; }
        .health-correction-details {
            grid-column: 1 / -1;
            justify-self: stretch;
            justify-content: center;
        }
    }

    /* --- NOTIFICATION CENTER --- */
    .notification-dashboard {
        width: 100%;
    }
    .notification-hero.health-record-hero {
        margin-bottom: 16px;
    }
    .notification-hero .health-record-hero-emblem svg {
        width: 56px;
        height: 56px;
        stroke-width: 1.45;
    }
    .notification-hero .page-hero-step {
        gap: 7px;
    }
    .notification-hero .page-hero-step::before {
        display: none;
    }
    .notification-hero .page-hero-step svg {
        width: 14px;
        height: 14px;
        color: #ffffff;
        stroke-width: 2;
    }
    .notification-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 14px;
    }
    .notification-stat-card {
        min-height: 98px;
        display: grid;
        grid-template-columns: 48px minmax(0, 1fr);
        align-items: center;
        gap: 10px;
        padding: 13px 14px;
        border: 1px solid rgba(112,19,27,.11);
        border-radius: 8px;
        box-sizing: border-box;
        background: rgba(255,255,255,.97);
        box-shadow: 0 10px 24px rgba(39,13,20,.07);
        color: #111827;
        text-decoration: none;
        transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
    }
    .notification-stat-card:hover {
        transform: translateY(-2px);
        border-color: rgba(250,204,21,.62);
        box-shadow: 0 14px 26px rgba(39,13,20,.11);
    }
    .notification-stat-icon {
        width: 46px;
        height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: #fff0f2;
        color: #a40021;
    }
    .notification-stat-icon svg {
        width: 24px;
        height: 24px;
        stroke-width: 1.8;
    }
    .notification-stat-card.is-appointments .notification-stat-icon {
        background: #fff7da;
        color: #d97706;
    }
    .notification-stat-card.is-health .notification-stat-icon {
        background: #eaf8e9;
        color: #199447;
    }
    .notification-stat-card.is-announcements .notification-stat-icon {
        background: #eaf3ff;
        color: #2368b8;
    }
    .notification-stat-copy {
        min-width: 0;
        display: grid;
        line-height: 1.15;
    }
    .notification-stat-label {
        color: #64748b;
        font-size: 12px;
        font-weight: 850;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .notification-stat-copy strong {
        margin: 2px 0 3px;
        color: #182033;
        font-size: 26px;
        font-weight: 900;
    }
    .notification-stat-copy small {
        color: #718096;
        font-size: 12px;
        line-height: 1.4;
    }
    .notification-content-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 280px;
        gap: 14px;
        align-items: start;
    }
    .notification-feed-card,
    .notification-side-card {
        border: 1px solid rgba(112,19,27,.12);
        border-radius: 8px;
        background: rgba(255,255,255,.97);
        box-shadow: 0 14px 30px rgba(39,13,20,.08);
    }
    .notification-feed-card {
        min-width: 0;
        padding: 12px;
    }
    .notification-filter-row {
        display: flex;
        align-items: center;
        gap: 7px;
        padding: 0 0 11px;
        overflow-x: auto;
        scrollbar-width: thin;
    }
    .notification-filter-chip {
        min-height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        flex: 0 0 auto;
        padding: 8px 13px;
        border: 1px solid rgba(112,19,27,.14);
        border-radius: 999px;
        background: #ffffff;
        color: #6f1624;
        font-size: 12px;
        font-weight: 800;
        line-height: 1;
        text-decoration: none;
        transition: border-color .18s ease, background .18s ease, color .18s ease;
    }
    .notification-filter-chip span {
        color: #94a3b8;
        font-size: 10px;
    }
    .notification-filter-chip:hover {
        border-color: rgba(176,0,32,.35);
        background: #fff7f8;
    }
    .notification-filter-chip.is-active {
        border-color: #8b0018;
        background: #8b0018;
        color: #ffffff;
    }
    .notification-filter-chip.is-active span { color: #ffe9ed; }
    .notification-feed-list {
        display: grid;
        gap: 7px;
    }
    .notification-feed-item {
        min-width: 0;
        display: grid;
        grid-template-columns: 48px minmax(0, 1fr) 138px;
        align-items: center;
        gap: 11px;
        padding: 14px;
        border: 1px solid #e4e8ee;
        border-radius: 7px;
        background: #ffffff;
        color: #182033;
        text-decoration: none;
        transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease, background .18s ease;
    }
    .notification-feed-item:hover {
        transform: translateY(-1px);
        border-color: rgba(176,0,32,.25);
        box-shadow: 0 8px 18px rgba(39,13,20,.08);
    }
    .notification-feed-item.is-unread {
        border-color: rgba(176,0,32,.22);
        background: linear-gradient(90deg, #fff7f8, #ffffff 34%);
    }
    .notification-letter-icon {
        width: 46px;
        height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: #fff0f2;
        color: #a40021;
        font-size: 14px;
        font-weight: 900;
        letter-spacing: 0;
    }
    .notification-feed-item.is-appointments .notification-letter-icon {
        background: #fff7da;
        color: #c66a04;
    }
    .notification-feed-item.is-health-records .notification-letter-icon {
        background: #eaf8e9;
        color: #17863f;
    }
    .notification-feed-item.is-announcements .notification-letter-icon {
        background: #eaf3ff;
        color: #2464ad;
    }
    .notification-feed-copy {
        min-width: 0;
        display: grid;
        gap: 6px;
    }
    .notification-feed-heading {
        min-width: 0;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 7px;
    }
    .notification-feed-heading strong {
        color: #4f0c17;
        font-size: 15px;
        font-weight: 900;
        line-height: 1.3;
    }
    .notification-category-badge {
        padding: 4px 7px;
        border-radius: 999px;
        background: #fff0f2;
        color: #a40021;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .03em;
        line-height: 1;
        text-transform: uppercase;
    }
    .notification-feed-item.is-appointments .notification-category-badge {
        background: #fff7da;
        color: #a65300;
    }
    .notification-feed-item.is-health-records .notification-category-badge {
        background: #eaf8e9;
        color: #147533;
    }
    .notification-feed-item.is-announcements .notification-category-badge {
        background: #eaf3ff;
        color: #205b9e;
    }
    .notification-feed-message {
        display: -webkit-box;
        overflow: hidden;
        color: #4b5563;
        font-size: 13px;
        line-height: 1.55;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }
    .notification-feed-meta {
        min-width: 0;
        display: grid;
        justify-items: end;
        gap: 9px;
    }
    .notification-feed-meta time {
        color: #7b8798;
        font-size: 11px;
        text-align: right;
    }
    .notification-feed-action {
        min-height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 7px 11px;
        border: 1px solid rgba(176,0,32,.17);
        border-radius: 999px;
        color: #8b0018;
        font-size: 12px;
        font-weight: 850;
        white-space: nowrap;
    }
    .notification-feed-action svg {
        width: 12px;
        height: 12px;
        stroke-width: 2.2;
    }
    .notification-empty-state {
        min-height: 230px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 30px;
        color: #64748b;
        text-align: center;
    }
    .notification-empty-icon {
        width: 54px;
        height: 54px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
        border-radius: 50%;
        background: #fff0f2;
        color: #a40021;
    }
    .notification-empty-icon svg { width: 28px; height: 28px; }
    .notification-empty-state strong { color: #4f0c17; font-size: 14px; }
    .notification-empty-state p { margin: 5px 0 0; font-size: 13px; }
    .notification-pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding-top: 13px;
    }
    .notification-page-number,
    .notification-page-arrow {
        width: 31px;
        height: 31px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e2e8f0;
        border-radius: 7px;
        background: #ffffff;
        color: #64748b;
        font-size: 12px;
        font-weight: 850;
        text-decoration: none;
    }
    .notification-page-arrow:first-child svg { transform: rotate(180deg); }
    .notification-page-arrow svg { width: 14px; height: 14px; }
    .notification-page-number.is-active {
        border-color: #8b0018;
        background: #8b0018;
        color: #ffffff;
    }
    .notification-page-arrow.is-disabled {
        opacity: .35;
        pointer-events: none;
    }
    .notification-sidebar {
        display: grid;
        gap: 12px;
    }
    .notification-side-card {
        padding: 16px;
    }
    .notification-side-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 13px;
        color: #7f001d;
        font-size: 15px;
        font-weight: 900;
    }
    .notification-side-title > span {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 7px;
        background: #fff0f2;
        color: #a40021;
    }
    .notification-side-title svg { width: 18px; height: 18px; stroke-width: 1.9; }
    .notification-center-list {
        display: grid;
        gap: 10px;
        margin: 0;
    }
    .notification-center-list > div {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .notification-center-list dt {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #475569;
        font-size: 12px;
    }
    .notification-center-list dt i {
        width: 7px;
        height: 7px;
        border: 1.5px solid #64748b;
        border-radius: 50%;
    }
    .notification-center-list dt i.is-unread {
        border-color: #a40021;
        background: #a40021;
    }
    .notification-center-list dd {
        margin: 0;
        color: #182033;
        font-size: 13px;
        font-weight: 900;
    }
    .notification-last-read {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-top: 14px;
        padding-top: 12px;
        border-top: 1px solid #e8edf2;
        color: #64748b;
        font-size: 11px;
    }
    .notification-last-read strong {
        color: #334155;
        font-weight: 800;
        text-align: right;
    }
    .notification-quick-actions,
    .notification-quick-actions form {
        display: grid;
        gap: 2px;
        margin: 0;
    }
    .notification-quick-actions form { display: block; }
    .notification-quick-actions a,
    .notification-quick-actions button {
        width: 100%;
        min-height: 42px;
        display: grid;
        grid-template-columns: 19px minmax(0, 1fr) 15px;
        align-items: center;
        gap: 8px;
        padding: 8px 3px;
        border: 0;
        border-bottom: 1px solid #eef1f4;
        box-sizing: border-box;
        background: transparent;
        color: #334155;
        font: inherit;
        font-size: 12px;
        text-align: left;
        text-decoration: none;
        cursor: pointer;
    }
    .notification-quick-actions > :last-child { border-bottom: 0; }
    .notification-quick-actions svg { width: 17px; height: 17px; color: #8b0018; stroke-width: 1.8; }
    .notification-quick-actions a svg:last-child,
    .notification-quick-actions button svg:last-child { color: #64748b; }
    .notification-quick-actions a:hover,
    .notification-quick-actions button:hover { color: #8b0018; }
    .notification-quick-actions button:disabled {
        opacity: .45;
        cursor: default;
    }
    html[data-theme="dark"] .notification-stat-card,
    html[data-theme="dark"] .notification-feed-card,
    html[data-theme="dark"] .notification-side-card {
        border-color: rgba(250,204,21,.15);
        background: #101722;
        box-shadow: 0 16px 34px rgba(0,0,0,.34);
    }
    html[data-theme="dark"] .notification-stat-copy strong,
    html[data-theme="dark"] .notification-feed-heading strong,
    html[data-theme="dark"] .notification-empty-state strong,
    html[data-theme="dark"] .notification-center-list dd,
    html[data-theme="dark"] .notification-last-read strong { color: #f8fafc; }
    html[data-theme="dark"] .notification-stat-label,
    html[data-theme="dark"] .notification-stat-copy small,
    html[data-theme="dark"] .notification-feed-message,
    html[data-theme="dark"] .notification-feed-meta time,
    html[data-theme="dark"] .notification-center-list dt,
    html[data-theme="dark"] .notification-last-read,
    html[data-theme="dark"] .notification-empty-state { color: #aeb8c7; }
    html[data-theme="dark"] .notification-filter-chip,
    html[data-theme="dark"] .notification-feed-item,
    html[data-theme="dark"] .notification-page-number,
    html[data-theme="dark"] .notification-page-arrow {
        border-color: rgba(148,163,184,.2);
        background: #151e2d;
        color: #e5e7eb;
    }
    html[data-theme="dark"] .notification-filter-chip:hover { border-color: rgba(250,204,21,.28); background: #192334; }
    html[data-theme="dark"] .notification-filter-chip.is-active,
    html[data-theme="dark"] .notification-page-number.is-active {
        border-color: #facc15;
        background: #760018;
        color: #ffffff;
    }
    html[data-theme="dark"] .notification-feed-item.is-unread {
        border-color: rgba(250,204,21,.2);
        background: linear-gradient(90deg, rgba(127,29,45,.32), #151e2d 38%);
    }
    html[data-theme="dark"] .notification-feed-action {
        border-color: rgba(250,204,21,.2);
        color: #fde68a;
    }
    html[data-theme="dark"] .notification-side-title { color: #facc15; }
    html[data-theme="dark"] .notification-last-read,
    html[data-theme="dark"] .notification-quick-actions a,
    html[data-theme="dark"] .notification-quick-actions button { border-color: rgba(255,255,255,.09); }
    html[data-theme="dark"] .notification-quick-actions a,
    html[data-theme="dark"] .notification-quick-actions button { color: #e5e7eb; }
    @media (max-width: 940px) {
        .notification-content-grid { grid-template-columns: minmax(0, 1fr) 260px; }
        .notification-feed-item { grid-template-columns: 46px minmax(0, 1fr) 126px; }
    }
    @media (max-width: 820px) {
        .notification-stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .notification-content-grid { grid-template-columns: 1fr; }
        .notification-sidebar { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 620px) {
        .notification-hero .page-hero-steps { grid-template-columns: 1fr; }
        .notification-hero .page-hero-step { justify-content: flex-start; }
        .notification-stat-grid { gap: 8px; }
        .notification-stat-card {
            min-height: 78px;
            grid-template-columns: 36px minmax(0, 1fr);
            padding: 10px;
        }
        .notification-stat-icon { width: 35px; height: 35px; }
        .notification-stat-icon svg { width: 18px; height: 18px; }
        .notification-feed-card { padding: 9px; }
        .notification-feed-item {
            grid-template-columns: 40px minmax(0, 1fr);
            align-items: start;
            padding: 11px;
        }
        .notification-feed-meta {
            grid-column: 2;
            display: flex;
            align-items: center;
            justify-content: space-between;
            justify-items: stretch;
        }
        .notification-feed-meta time { text-align: left; }
        .notification-feed-action { min-height: 26px; padding: 5px 8px; }
        .notification-sidebar { grid-template-columns: 1fr; }
    }
    @media (max-width: 430px) {
        .notification-stat-grid { grid-template-columns: 1fr; }
        .notification-stat-card { min-height: 70px; }
    }

    /* Version-aware Health Form dashboard. */
    .health-doc-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 272px;
        gap: 14px;
        align-items: start;
    }
    .health-doc-main,
    .health-doc-sidebar {
        display: grid;
        gap: 14px;
        min-width: 0;
    }
    .health-doc-card,
    .health-doc-side-card {
        border: 1px solid rgba(127, 0, 25, .13);
        border-radius: 8px;
        background: rgba(255, 255, 255, .96);
        box-shadow: 0 12px 30px rgba(73, 25, 35, .08);
    }
    .health-doc-latest {
        position: relative;
        overflow: hidden;
        padding: 31px 15px 13px;
    }
    .health-doc-ribbon {
        position: absolute;
        top: 0;
        left: 0;
        min-height: 23px;
        padding: 6px 20px 5px 12px;
        clip-path: polygon(0 0, 100% 0, calc(100% - 11px) 100%, 0 100%);
        background: #850019;
        color: #fff;
        font-size: 9px;
        font-weight: 900;
        line-height: 1;
        text-transform: uppercase;
    }
    .health-doc-latest-grid {
        display: grid;
        grid-template-columns: minmax(205px, 1.35fr) minmax(145px, .82fr) minmax(145px, .82fr);
        column-gap: 12px;
        row-gap: 0;
    }
    .health-doc-identity,
    .health-doc-meta-item,
    .health-doc-type {
        min-width: 0;
        padding: 9px 10px;
        border-bottom: 1px solid rgba(148, 163, 184, .2);
    }
    .health-doc-identity,
    .health-doc-meta-item {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    .health-doc-identity {
        grid-row: span 1;
        padding-left: 0;
    }
    .health-doc-type {
        display: grid;
        align-content: center;
        gap: 3px;
        padding-left: 0;
    }
    .health-doc-primary-icon,
    .health-doc-meta-icon,
    .health-doc-section-icon,
    .health-doc-side-card h2 > span,
    .health-doc-modal-summary > span:first-child {
        display: inline-grid;
        place-items: center;
        flex: 0 0 auto;
        color: #a50020;
        background: #fff0f2;
    }
    .health-doc-primary-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
    }
    .health-doc-primary-icon svg { width: 27px; height: 27px; }
    .health-doc-meta-icon {
        width: 30px;
        height: 30px;
        border-radius: 8px;
    }
    .health-doc-meta-icon svg { width: 16px; height: 16px; }
    .health-doc-identity > span:last-child,
    .health-doc-meta-item > span:last-child {
        display: grid;
        min-width: 0;
        gap: 2px;
    }
    .health-doc-label {
        display: block;
        color: #64748b;
        font-size: 9px;
        font-weight: 900;
        line-height: 1.25;
        text-transform: uppercase;
    }
    .health-doc-form-id {
        color: #9f001e;
        font-size: 18px;
        line-height: 1.15;
    }
    .health-doc-meta-item strong,
    .health-doc-type strong {
        color: #172033;
        font-size: 11px;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }
    .health-doc-meta-item small {
        color: #64748b;
        font-size: 9px;
    }
    .health-doc-status {
        display: inline-flex;
        align-items: center;
        justify-content: flex-start;
        width: max-content;
        max-width: 100%;
        min-height: 0;
        margin-top: 3px;
        padding: 0;
        border: 0;
        border-radius: 0;
        color: #b45309;
        background: transparent;
        font-size: 8px;
        font-weight: 900;
        line-height: 1.1;
        text-transform: uppercase;
    }
    .health-doc-status.is-approved { color: #15803d; background: transparent; }
    .health-doc-status.is-correction { color: #c2410c; background: transparent; }
    .health-doc-status.is-requested { color: #9f001e; background: transparent; }
    .health-doc-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding: 13px 0 0;
    }
    .health-doc-action {
        position: relative;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 34px;
        padding: 8px 12px;
        border: 1px solid rgba(159, 0, 30, .32);
        border-radius: 6px;
        color: #850019;
        background: #fff;
        font-size: 10px;
        font-weight: 900;
        line-height: 1.15;
        text-decoration: none;
        cursor: pointer;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease, background .18s ease;
    }
    .health-doc-action svg { width: 15px; height: 15px; }
    .health-doc-action.is-primary { color: #fff; background: #92001c; }
    .health-doc-action.is-edit { color: #a34b00; border-color: #f0b743; background: #fffaf0; }
    .health-doc-action:hover,
    .health-doc-action:focus-visible {
        transform: translateY(-1px);
        border-color: #facc15;
        color: #650012;
        background: #facc15;
        box-shadow: 0 8px 18px rgba(77, 16, 29, .15);
    }
    .health-doc-action::after,
    .health-doc-history-actions a::after,
    .health-doc-history-actions button::after {
        content: '';
        position: absolute;
        top: -45%;
        bottom: -45%;
        left: -48%;
        width: 32%;
        pointer-events: none;
        opacity: 0;
        transform: skewX(-20deg);
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .72), transparent);
    }
    .health-doc-action:hover::after,
    .health-doc-action:focus-visible::after,
    .health-doc-history-actions a:hover::after,
    .health-doc-history-actions a:focus-visible::after,
    .health-doc-history-actions button:hover::after,
    .health-doc-history-actions button:focus-visible::after {
        animation: healthDocActionSweep .68s ease-out;
    }
    @keyframes healthDocActionSweep {
        0% { left: -48%; opacity: 0; }
        18% { opacity: .85; }
        100% { left: 120%; opacity: 0; }
    }
    .health-doc-note,
    .health-doc-requirement,
    .health-doc-history-request {
        display: flex;
        align-items: center;
        gap: 9px;
        margin-top: 11px;
        padding: 9px 11px;
        border: 1px solid #f4d9a5;
        border-radius: 6px;
        color: #9a4d00;
        background: #fff9eb;
        font-size: 10px;
        line-height: 1.4;
    }
    .health-doc-note svg,
    .health-doc-requirement svg,
    .health-doc-history-request svg { width: 15px; height: 15px; flex: 0 0 auto; }
    .health-doc-note.is-requested { color: #850019; border-color: #f3c6ce; background: #fff4f5; }
    .health-doc-requirement { justify-content: space-between; color: #9f001e; border-color: #f3c6ce; background: #fff7f8; }
    .health-doc-requirement > span { display: flex; align-items: center; gap: 7px; font-weight: 800; }
    .health-doc-requirement button {
        min-height: 34px;
        padding: 8px 12px;
        border: 1px solid #a50020;
        border-radius: 6px;
        color: #fff;
        background: #92001c;
        font: inherit;
        font-weight: 900;
        cursor: pointer;
    }
    .health-doc-requirement .health-doc-upload-button:hover,
    .health-doc-requirement .health-doc-upload-button:focus-visible {
        border-color: #facc15;
        color: #650012;
        background: #facc15;
    }
    .health-doc-empty {
        display: grid;
        justify-items: center;
        gap: 7px;
        padding: 30px 16px 18px;
        text-align: center;
    }
    .health-doc-empty > span {
        display: grid;
        place-items: center;
        width: 48px;
        height: 48px;
        border-radius: 50%;
        color: #a50020;
        background: #fff0f2;
    }
    .health-doc-empty > span svg { width: 25px; height: 25px; }
    .health-doc-empty strong { color: #172033; font-size: 14px; }
    .health-doc-empty p { margin: 0; color: #64748b; font-size: 11px; }
    .health-doc-section-head {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 13px 14px 11px;
    }
    .health-doc-section-icon,
    .health-doc-side-card h2 > span {
        width: 30px;
        height: 30px;
        border-radius: 8px;
    }
    .health-doc-section-icon svg,
    .health-doc-side-card h2 > span svg { width: 16px; height: 16px; }
    .health-doc-section-head h2,
    .health-doc-side-card h2 {
        margin: 0;
        color: #850019;
        font-size: 12px;
        line-height: 1.2;
    }
    .health-doc-section-head p {
        margin: 3px 0 0;
        color: #64748b;
        font-size: 9px;
    }
    .health-doc-history-table { padding: 0 10px; }
    .health-doc-history-head,
    .health-doc-history-row {
        display: grid;
        grid-template-columns: 1.12fr 1.55fr .9fr .9fr .78fr 1.18fr;
        align-items: center;
    }
    .health-doc-history-head {
        min-height: 29px;
        padding: 0 8px;
        border-radius: 5px 5px 0 0;
        color: #fff;
        background: #850019;
        font-size: 8px;
        font-weight: 900;
        text-transform: uppercase;
    }
    .health-doc-history-row {
        min-height: 66px;
        padding: 7px 8px;
        border: solid rgba(148, 163, 184, .2);
        border-width: 0 1px 1px;
        background: #fff;
        color: #334155;
        font-size: 9px;
    }
    .health-doc-history-row.is-latest { background: linear-gradient(90deg, #fffaf0, #fff); }
    .health-doc-history-row > div { min-width: 0; padding: 0 5px; }
    .health-doc-history-row strong { display: block; color: #172033; line-height: 1.35; overflow-wrap: anywhere; }
    .health-doc-history-row small { display: block; margin-top: 2px; color: #64748b; font-size: 8px; line-height: 1.35; }
    .health-doc-history-id { display: flex; align-items: center; gap: 7px; }
    .health-doc-row-icon {
        display: grid;
        place-items: center;
        width: 31px;
        height: 31px;
        flex: 0 0 31px;
        border-radius: 50%;
        color: #b45309;
        background: #fff5d8;
    }
    .health-doc-row-icon svg { width: 15px; height: 15px; }
    .health-doc-history-id small {
        width: max-content;
        padding: 2px 5px;
        border-radius: 999px;
        color: #a34b00;
        background: #fff1b9;
        font-weight: 900;
        text-transform: uppercase;
    }
    .health-doc-history-actions { display: flex; flex-wrap: wrap; gap: 3px; }
    .health-doc-history-actions a,
    .health-doc-history-actions button {
        position: relative;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        gap: 3px;
        min-height: 22px;
        padding: 4px 6px;
        border: 1px solid rgba(159, 0, 30, .25);
        border-radius: 5px;
        color: #850019;
        background: #fff;
        font: inherit;
        font-size: 8px;
        font-weight: 900;
        line-height: 1.1;
        text-decoration: none;
        cursor: pointer;
        transition: transform .18s ease, border-color .18s ease, background .18s ease, color .18s ease, box-shadow .18s ease;
    }
    .health-doc-history-actions svg { width: 10px; height: 10px; }
    .health-doc-history-actions a:hover,
    .health-doc-history-actions a:focus-visible,
    .health-doc-history-actions button:hover,
    .health-doc-history-actions button:focus-visible {
        transform: translateY(-1px);
        border-color: #facc15;
        color: #650012;
        background: #facc15;
        box-shadow: 0 7px 15px rgba(77, 16, 29, .14);
    }
    .health-doc-history-empty { padding: 22px; text-align: center; color: #64748b; font-size: 10px; }
    .health-doc-history-request {
        justify-content: space-between;
        margin: 10px;
        color: #9a4d00;
    }
    .health-doc-history-request > span { display: flex; align-items: center; gap: 6px; font-weight: 900; }
    .health-doc-history-request small { color: #7c5a22; }
    .health-doc-side-card { padding: 12px; }
    .health-doc-side-card h2 {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
    }
    .health-doc-progress { margin: 0; padding: 0; list-style: none; }
    .health-doc-progress li {
        position: relative;
        display: grid;
        grid-template-columns: 11px minmax(0, 1fr) 17px;
        gap: 8px;
        min-height: 70px;
        padding-bottom: 10px;
    }
    .health-doc-progress li:last-child { min-height: 0; padding-bottom: 0; }
    .health-doc-progress li::before {
        content: '';
        position: absolute;
        top: 12px;
        bottom: -2px;
        left: 5px;
        width: 1px;
        background: #d8dee8;
    }
    .health-doc-progress li:last-child::before { display: none; }
    .health-doc-progress i {
        position: relative;
        z-index: 1;
        width: 8px;
        height: 8px;
        margin: 3px 0 0 1px;
        border: 2px solid #cbd5e1;
        border-radius: 50%;
        background: #fff;
    }
    .health-doc-progress li.is-active i { border-color: #a50020; box-shadow: 0 0 0 3px rgba(165, 0, 32, .1); }
    .health-doc-progress li.is-complete i { border-color: #22a447; background: #22a447; }
    .health-doc-progress li.is-complete::before { background: #22a447; }
    .health-doc-progress li > span { display: grid; align-content: start; gap: 2px; }
    .health-doc-progress strong { color: #172033; font-size: 10px; }
    .health-doc-progress small { color: #64748b; font-size: 8px; }
    .health-doc-progress em { color: #64748b; font-size: 8px; font-style: normal; line-height: 1.35; }
    .health-doc-progress b {
        display: grid;
        place-items: center;
        width: 16px;
        height: 16px;
        border: 1px solid #86d398;
        border-radius: 50%;
        color: #16923a;
        background: #effcf2;
    }
    .health-doc-progress b svg { width: 10px; height: 10px; }
    .health-doc-side-card.is-reminder { background: linear-gradient(145deg, #fff9fa, #fff); }
    .health-doc-schedule {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 9px;
        border: 1px solid #f3d3d8;
        border-radius: 6px;
        color: #a50020;
        background: #fff0f2;
    }
    .health-doc-schedule > svg { width: 17px; height: 17px; flex: 0 0 auto; }
    .health-doc-schedule span { display: grid; gap: 2px; }
    .health-doc-schedule strong { font-size: 9px; }
    .health-doc-schedule small { color: #6b1c2c; font-size: 8px; }
    .health-doc-side-card p { margin: 9px 0 0; color: #64748b; font-size: 9px; line-height: 1.55; }
    .health-doc-side-card.is-help a {
        display: flex;
        align-items: center;
        gap: 7px;
        margin-top: 7px;
        color: #850019;
        font-size: 9px;
        font-weight: 800;
        text-decoration: none;
    }
    .health-doc-side-card.is-help a svg { width: 14px; height: 14px; }
    .health-doc-files-modal { width: min(760px, calc(100vw - 28px)); }
    .health-doc-files-modal .record-modal-body { padding: 15px; }
    .health-doc-modal-summary {
        display: flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 12px;
        padding: 10px;
        border: 1px solid rgba(159, 0, 30, .12);
        border-radius: 7px;
        background: #fff8f9;
    }
    .health-doc-modal-summary > span:first-child { width: 34px; height: 34px; border-radius: 50%; }
    .health-doc-modal-summary > span:first-child svg { width: 18px; height: 18px; }
    .health-doc-modal-summary > span:last-child { display: grid; gap: 2px; }
    .health-doc-modal-summary strong { color: #850019; font-size: 12px; }
    .health-doc-modal-summary small { color: #64748b; font-size: 9px; }
    .health-doc-modal-files { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 9px; }
    .health-doc-file-card {
        display: grid;
        grid-template-columns: 54px minmax(0, 1fr) auto;
        align-items: center;
        gap: 10px;
        min-width: 0;
        padding: 10px;
        border: 1px solid rgba(148, 163, 184, .25);
        border-radius: 7px;
        background: #fff;
    }
    .health-doc-file-preview {
        display: grid;
        place-items: center;
        width: 54px;
        height: 54px;
        overflow: hidden;
        border-radius: 6px;
        color: #a50020;
        background: #fff0f2;
    }
    .health-doc-file-preview img { width: 100%; height: 100%; object-fit: cover; }
    .health-doc-file-preview svg { width: 25px; height: 25px; }
    .health-doc-file-copy { display: grid; min-width: 0; gap: 3px; }
    .health-doc-file-copy strong { color: #172033; font-size: 10px; line-height: 1.3; }
    .health-doc-file-copy small { color: #64748b; font-size: 8px; }
    .health-doc-file-card > a {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        color: #850019;
        font-size: 8px;
        font-weight: 900;
        text-decoration: none;
        white-space: nowrap;
    }
    .health-doc-file-card > a svg { width: 11px; height: 11px; }
    .health-doc-modal-empty {
        grid-column: 1 / -1;
        display: grid;
        justify-items: center;
        gap: 6px;
        padding: 25px 12px;
        color: #64748b;
        text-align: center;
    }
    .health-doc-modal-empty > span { color: #a50020; }
    .health-doc-modal-empty svg { width: 30px; height: 30px; }
    .health-doc-modal-empty strong { color: #172033; font-size: 12px; }
    .health-doc-modal-empty p { margin: 0; font-size: 9px; }

    /* Match the readable typography scale used by the Notifications module. */
    .health-doc-ribbon { font-size: 10px; }
    .health-doc-label { font-size: 11px; }
    .health-doc-form-id { font-size: 20px; }
    .health-doc-meta-item strong,
    .health-doc-type strong { font-size: 13px; }
    .health-doc-meta-item small { font-size: 10px; }
    .health-doc-status { min-height: 0; padding: 0; font-size: 11px; }
    .health-doc-action { min-height: 38px; padding: 9px 13px; font-size: 12px; }
    .health-doc-note,
    .health-doc-requirement,
    .health-doc-history-request { padding: 10px 12px; font-size: 12px; }
    .health-doc-empty strong { font-size: 15px; }
    .health-doc-empty p { font-size: 12px; }
    .health-doc-section-head { padding: 15px 15px 12px; }
    .health-doc-section-icon,
    .health-doc-side-card h2 > span { width: 34px; height: 34px; }
    .health-doc-section-icon svg,
    .health-doc-side-card h2 > span svg { width: 18px; height: 18px; }
    .health-doc-section-head h2,
    .health-doc-side-card h2 { font-size: 15px; }
    .health-doc-section-head p { font-size: 11px; }
    .health-doc-history-head { min-height: 34px; font-size: 10px; }
    .health-doc-history-row { min-height: 82px; padding-top: 10px; padding-bottom: 10px; font-size: 11px; }
    .health-doc-history-row strong { font-size: 11px; }
    .health-doc-history-row small { font-size: 10px; }
    .health-doc-row-icon { width: 36px; height: 36px; flex-basis: 36px; }
    .health-doc-row-icon svg { width: 18px; height: 18px; }
    .health-doc-history-actions a,
    .health-doc-history-actions button { min-height: 25px; padding: 5px 7px; font-size: 10px; }
    .health-doc-history-actions svg { width: 12px; height: 12px; }
    .health-doc-history-request small { font-size: 10px; }
    .health-doc-side-card { padding: 15px; }
    .health-doc-progress li { min-height: 82px; }
    .health-doc-progress strong { font-size: 12px; }
    .health-doc-progress small,
    .health-doc-progress em { font-size: 10px; }
    .health-doc-schedule strong { font-size: 11px; }
    .health-doc-schedule small { font-size: 10px; }
    .health-doc-side-card p,
    .health-doc-side-card.is-help a { font-size: 11px; }
    .health-doc-modal-summary strong { font-size: 14px; }
    .health-doc-modal-summary small { font-size: 11px; }
    .health-doc-file-copy strong { font-size: 12px; }
    .health-doc-file-copy small,
    .health-doc-file-card > a { font-size: 10px; }
    .health-doc-modal-empty strong { font-size: 15px; }
    .health-doc-modal-empty p { font-size: 11px; }

    html[data-theme="dark"] .health-doc-card,
    html[data-theme="dark"] .health-doc-side-card,
    html[data-theme="dark"] .health-doc-history-row,
    html[data-theme="dark"] .health-doc-file-card {
        border-color: rgba(250, 204, 21, .16);
        background: #111827;
        box-shadow: 0 13px 28px rgba(0, 0, 0, .28);
    }
    html[data-theme="dark"] .health-doc-history-row.is-latest { background: linear-gradient(90deg, rgba(133, 0, 25, .3), #111827 45%); }
    html[data-theme="dark"] .health-doc-primary-icon,
    html[data-theme="dark"] .health-doc-meta-icon,
    html[data-theme="dark"] .health-doc-section-icon,
    html[data-theme="dark"] .health-doc-side-card h2 > span,
    html[data-theme="dark"] .health-doc-modal-summary > span:first-child,
    html[data-theme="dark"] .health-doc-file-preview,
    html[data-theme="dark"] .health-doc-empty > span { color: #facc15; background: #30151d; }
    html[data-theme="dark"] .health-doc-form-id,
    html[data-theme="dark"] .health-doc-section-head h2,
    html[data-theme="dark"] .health-doc-side-card h2,
    html[data-theme="dark"] .health-doc-side-card.is-help a,
    html[data-theme="dark"] .health-doc-modal-summary strong,
    html[data-theme="dark"] .health-doc-file-card > a { color: #fde68a; }
    html[data-theme="dark"] .health-doc-meta-item strong,
    html[data-theme="dark"] .health-doc-type strong,
    html[data-theme="dark"] .health-doc-history-row strong,
    html[data-theme="dark"] .health-doc-progress strong,
    html[data-theme="dark"] .health-doc-file-copy strong,
    html[data-theme="dark"] .health-doc-modal-empty strong,
    html[data-theme="dark"] .health-doc-empty strong { color: #f8fafc; }
    html[data-theme="dark"] .health-doc-label,
    html[data-theme="dark"] .health-doc-meta-item small,
    html[data-theme="dark"] .health-doc-section-head p,
    html[data-theme="dark"] .health-doc-history-row small,
    html[data-theme="dark"] .health-doc-progress small,
    html[data-theme="dark"] .health-doc-progress em,
    html[data-theme="dark"] .health-doc-side-card p,
    html[data-theme="dark"] .health-doc-file-copy small,
    html[data-theme="dark"] .health-doc-modal-summary small,
    html[data-theme="dark"] .health-doc-modal-empty,
    html[data-theme="dark"] .health-doc-empty p { color: #aeb8c7; }
    html[data-theme="dark"] .health-doc-identity,
    html[data-theme="dark"] .health-doc-meta-item,
    html[data-theme="dark"] .health-doc-type { border-color: rgba(255, 255, 255, .09); }
    html[data-theme="dark"] .health-doc-action,
    html[data-theme="dark"] .health-doc-history-actions a,
    html[data-theme="dark"] .health-doc-history-actions button { color: #fde68a; border-color: rgba(250, 204, 21, .22); background: #151e2d; }
    html[data-theme="dark"] .health-doc-action.is-primary { color: #fff; background: #850019; }
    html[data-theme="dark"] .health-doc-action.is-edit { color: #facc15; background: rgba(133, 0, 25, .2); }
    html[data-theme="dark"] .health-doc-action:hover,
    html[data-theme="dark"] .health-doc-action:focus-visible,
    html[data-theme="dark"] .health-doc-history-actions a:hover,
    html[data-theme="dark"] .health-doc-history-actions a:focus-visible,
    html[data-theme="dark"] .health-doc-history-actions button:hover,
    html[data-theme="dark"] .health-doc-history-actions button:focus-visible {
        border-color: #facc15;
        color: #650012;
        background: #facc15;
    }
    html[data-theme="dark"] .health-doc-note,
    html[data-theme="dark"] .health-doc-requirement,
    html[data-theme="dark"] .health-doc-history-request,
    html[data-theme="dark"] .health-doc-side-card.is-reminder,
    html[data-theme="dark"] .health-doc-modal-summary { color: #fde68a; border-color: rgba(250, 204, 21, .18); background: #151e2d; }
    html[data-theme="dark"] .health-doc-history-request small { color: #cbd5e1; }
    html[data-theme="dark"] .health-doc-progress i { background: #111827; }
    html[data-theme="dark"] .health-doc-schedule { color: #fde68a; border-color: rgba(250, 204, 21, .18); background: #2a1720; }
    html[data-theme="dark"] .health-doc-schedule small { color: #e5e7eb; }

    @media (max-width: 980px) {
        .health-doc-layout { grid-template-columns: minmax(0, 1fr) 245px; }
        .health-doc-latest-grid { grid-template-columns: minmax(180px, 1.15fr) 1fr 1fr; }
        .health-doc-history-head,
        .health-doc-history-row { grid-template-columns: 1.08fr 1.4fr .85fr .85fr .72fr 1.1fr; }
    }
    @media (max-width: 820px) {
        .health-doc-layout { grid-template-columns: 1fr; }
        .health-doc-sidebar { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .health-doc-side-card:first-child { grid-row: span 2; }
    }
    @media (max-width: 680px) {
        .health-doc-latest-grid { grid-template-columns: 1fr 1fr; }
        .health-doc-identity,
        .health-doc-type { grid-column: 1 / -1; }
        .health-doc-history-head { display: none; }
        .health-doc-history-row {
            grid-template-columns: 1fr 1fr;
            gap: 8px 12px;
            margin-bottom: 8px;
            padding: 11px;
            border-width: 1px;
            border-radius: 7px;
        }
        .health-doc-history-row > div { padding: 0; }
        .health-doc-history-row > div::before {
            content: attr(data-label);
            display: block;
            margin-bottom: 3px;
            color: #64748b;
            font-size: 8px;
            font-weight: 900;
            text-transform: uppercase;
        }
        .health-doc-history-id,
        .health-doc-history-type,
        .health-doc-history-actions { grid-column: 1 / -1; }
        .health-doc-history-id::before,
        .health-doc-history-type::before,
        .health-doc-history-actions::before { display: none !important; }
        .health-doc-modal-files { grid-template-columns: 1fr; }
    }
    @media (max-width: 520px) {
        .health-doc-layout,
        .health-doc-main,
        .health-doc-sidebar { gap: 10px; }
        .health-doc-sidebar { grid-template-columns: 1fr; }
        .health-doc-side-card:first-child { grid-row: auto; }
        .health-doc-latest { padding-right: 11px; padding-left: 11px; }
        .health-doc-latest-grid { grid-template-columns: 1fr; }
        .health-doc-identity,
        .health-doc-type { grid-column: auto; }
        .health-doc-identity,
        .health-doc-type,
        .health-doc-meta-item { padding-right: 2px; padding-left: 2px; }
        .health-doc-actions { display: grid; grid-template-columns: 1fr; }
        .health-doc-action { width: 100%; }
        .health-doc-note,
        .health-doc-requirement,
        .health-doc-history-request { align-items: flex-start; flex-direction: column; }
        .health-doc-requirement button { width: 100%; }
        .health-doc-history-row { grid-template-columns: 1fr; }
        .health-doc-history-id,
        .health-doc-history-type,
        .health-doc-history-actions { grid-column: auto; }
        .health-doc-file-card { grid-template-columns: 46px minmax(0, 1fr); }
        .health-doc-file-preview { width: 46px; height: 46px; }
        .health-doc-file-card > a { grid-column: 2; }
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
    $usesEmployeeHealthForm = $studentUsesEmployeeHealthForm ?? false;
    $profileRoleMarkers = strtolower(trim(implode(' ', array_filter([
        (string) ($user->user_role ?? ''),
        (string) ($user->user_type ?? ''),
        (string) ($user->idp_role ?? ''),
        (string) optional($linkedAdminProfile)->access_level,
        (string) optional($linkedAdminProfile)->admin_hub_role,
        (string) optional($linkedAdminProfile)->role,
    ]))));
    $showsEmployeeActiveStatus = (bool) $usesEmployeeHealthForm;
    foreach (['faculty', 'admin', 'staff', 'employee', 'dependent', 'designee', 'assistant', 'nurse'] as $employeeRoleMarker) {
        if (str_contains($profileRoleMarkers, $employeeRoleMarker)) {
            $showsEmployeeActiveStatus = true;
            break;
        }
    }
    $heroActiveStatusLabel = $showsEmployeeActiveStatus ? 'Active Employee' : 'Active Student';
    $showOfficeField = in_array($linkedAccessLevel, ['clinic_staff', 'designee', 'admin_designee', 'superadmin', 'super_admin', 'faculty'], true) || str_contains($linkedAccessLevel, 'faculty');
    $hasGuisisAccountData = (bool) ($guisisAccountData['available'] ?? false);
    $displayStudentNumber = $showsEmployeeActiveStatus
        ? trim((string) ($accountProfileData['employee_number'] ?? $user->employee_number ?? ''))
        : trim((string) ($accountProfileData['student_number'] ?? $user->student_number ?? $user->student_id ?? ''));
    if ($displayStudentNumber === '') {
        $displayStudentNumber = $showsEmployeeActiveStatus
            ? trim((string) ($user->employee_number ?? ''))
            : trim((string) ($user->student_number ?? $user->student_id ?? ''));
    }
    $looksLikeReferenceNumber = function ($value): bool {
        $value = strtoupper(trim((string) $value));

        return $value === ''
            || \Illuminate\Support\Str::startsWith($value, ['CLN', 'TEST-', 'TESTLOCAL', 'LOC-'])
            || (bool) preg_match('/^[0-9A-F]{8}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{12}$/i', $value)
            || (bool) preg_match('/^\d{4}-\d{4}-\d{4}/', $value)
            || (bool) preg_match('/^\d{4}-[A-Z]+-\d+/', $value);
    };
    $referenceMode = trim((string) ($accountProfileData['reference_mode'] ?? 'admission'));
    $referenceHeading = $usesEmployeeHealthForm
        ? 'Employee Reference'
        : ($referenceMode === 'admission' ? 'Admission Reference' : 'Clinic Reference');
    $idNumberHeading = $usesEmployeeHealthForm ? 'Employee Number' : ($referenceMode === 'admission' ? 'Student Number' : 'ID Number');
    $displayCourse = trim((string) ($accountProfileData['course_college'] ?? ''));
    $displayCourseCode = trim((string) ($accountProfileData['course_code'] ?? ''));
    if ($displayCourseCode === '' && $displayCourse !== '') {
        if (preg_match('/\(([A-Z0-9-]+)\)/', $displayCourse, $courseCodeMatch)) {
            $displayCourseCode = $courseCodeMatch[1];
        } elseif (preg_match('/\b(BS[A-Z0-9-]+|BSED-[A-Z]+|DIT|DOMT)\b/i', $displayCourse, $courseCodeMatch)) {
            $displayCourseCode = strtoupper($courseCodeMatch[1]);
        }
    }
    $cleanNamePart = function ($value): string {
        $value = trim((string) $value);
        return in_array(strtoupper($value), ['N/A', 'NA', 'NONE'], true) ? '' : $value;
    };
    $displayOffice = $cleanNamePart($accountProfileData['office'] ?? '');
    $displayFullName = trim((string) ($accountProfileData['full_name'] ?? ''));
    $cleanFirstName = $cleanNamePart($accountProfileData['first_name'] ?? $user->first_name ?? '');
    $cleanMiddleName = $cleanNamePart($accountProfileData['middle_name'] ?? $user->middle_name ?? '');
    $cleanLastName = $cleanNamePart($accountProfileData['last_name'] ?? $user->last_name ?? '');
    $cleanSuffixName = $cleanNamePart($accountProfileData['suffix_name'] ?? $user->suffix_name ?? optional($linkedAdminProfile)->suffix_name ?? '');
    $rebuiltDisplayFullName = trim(preg_replace('/\s+/', ' ', implode(' ', array_filter([$cleanFirstName, $cleanMiddleName, $cleanLastName, $cleanSuffixName]))));
    $displayFullName = $rebuiltDisplayFullName !== ''
        ? $rebuiltDisplayFullName
        : ($displayFullName !== '' ? $displayFullName : ($hasGuisisAccountData ? 'Available once enrolled' : ($user->name ?? 'Student')));
    $displayNameParts = preg_split('/\s+/', $displayFullName) ?: [];
    $displayFirstName = $displayNameParts[0] ?? $displayFullName;
    $displayRemainingName = trim(implode(' ', array_slice($displayNameParts, 1)));
    if ($looksLikeReferenceNumber($displayStudentNumber)) {
        $displayStudentNumber = '';
    }
    $displayYear = $cleanNamePart($accountProfileData['year'] ?? $user->year ?? '');
    $displaySection = trim((string) ($accountProfileData['section'] ?? $user->section ?? ''));
    $heroAcademicParts = array_values(array_filter([$displayStudentNumber, $displayCourse], fn ($value) => trim((string) $value) !== ''));
    $localMiddleName = $cleanNamePart($accountProfileData['middle_name'] ?? $user->middle_name ?? optional($linkedAdminProfile)->middle_name ?? '');
    $localMiddleName = $localMiddleName !== '' ? $localMiddleName : 'N/A';
    $localSuffixName = trim((string) ($accountProfileData['suffix_name'] ?? $user->suffix_name ?? optional($linkedAdminProfile)->suffix_name ?? ''));
    $localSuffixName = $localSuffixName !== '' ? $localSuffixName : 'N/A';
    $guisisPendingText = 'Available once enrolled';
    $guisisValue = fn ($value) => trim((string) $value) !== '' ? trim((string) $value) : $guisisPendingText;
    $guisisPendingClass = fn ($value) => trim((string) $value) === '' ? ' guisis-pending-value' : '';
    $clinicMeasurementProfile = $usesEmployeeHealthForm
        ? ($user->relationLoaded('employeeHealthProfile')
            ? $user->employeeHealthProfile
            : \App\Models\EmployeeHealthProfile::where('user_id', $user->id)->first())
        : ($user->relationLoaded('healthProfile')
            ? $user->healthProfile
            : \App\Models\HealthProfile::where('user_id', $user->id)->first());
    $heightRaw = old('height', optional($clinicMeasurementProfile)->height ?? '');
    $weightRaw = old('weight', optional($clinicMeasurementProfile)->weight ?? '');
    $bloodTypeDisplay = trim((string) ($accountProfileData['blood_type'] ?? optional($clinicMeasurementProfile)->blood_type ?? ''));
    $bloodTypeDisplay = $bloodTypeDisplay !== '' ? $bloodTypeDisplay : 'N/A';
    preg_match('/\d+(?:\.\d+)?/', (string) $heightRaw, $heightMatch);
    preg_match('/\d+(?:\.\d+)?/', (string) $weightRaw, $weightMatch);
    $heightDisplay = $heightMatch[0] ?? trim((string) $heightRaw);
    $weightDisplay = $weightMatch[0] ?? trim((string) $weightRaw);
    $showClinicMeasurements = trim((string) $heightRaw) !== '' && trim((string) $weightRaw) !== '';
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
    <div class="profile-dashboard">
    <div class="profile-hero" id="profileHeroCard">
        <svg class="profile-hero-wave" viewBox="0 0 1000 180" preserveAspectRatio="none" aria-hidden="true">
            <path d="M0 80 C82 106 142 116 232 94 C350 68 424 67 542 87 C694 110 801 120 890 82 C946 56 972 27 1000 -8 L1000 180 L0 180 Z" />
        </svg>
        <div class="profile-hero-campus" aria-hidden="true"></div>
        <div class="profile-hero-quote" aria-hidden="true">
            <span class="profile-hero-quote-mark">&ldquo;</span>
            <p>Your health is<br>our priority.</p>
        </div>
        <button type="button" class="hero-avatar" id="profilePhotoToggle" aria-label="Expand profile photo" aria-expanded="false">
            @php
                $profilePhotoRecord = $usesEmployeeHealthForm
                    ? ($user->employeeHealthProfile ?? \App\Models\EmployeeHealthProfile::where('user_id', $user->id)->first())
                    : ($user->healthProfile ?? \App\Models\HealthProfile::where('user_id', $user->id)->first());
            @endphp
            @if(!empty($profilePhotoRecord?->student_photo))
                <img src="{{ route('student.health_record.document', ['document' => 'student_photo']) }}" alt="Uploaded 2x2 Picture">
            @else
                {{ strtoupper(substr($displayFullName, 0, 1)) }}
            @endif
            <span class="hero-active-dot" aria-label="Active account">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 12.5l4.2 4.2L19 7" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
        </button>
        <div class="hero-info">
            <h1 class="hero-name">{{ $displayFullName }}</h1>
            @if($displayStudentNumber !== '' || $displayCourseCode !== '')
                <div class="hero-identity-meta">
                    @if($displayStudentNumber !== '')
                        <span class="hero-identity-pill hero-identity-pill--number">{{ $displayStudentNumber }}</span>
                    @endif
                    @if($displayCourseCode !== '')
                        <span class="hero-identity-pill hero-identity-pill--course">{{ $displayCourseCode }}</span>
                    @endif
                </div>
            @endif
            @if($isEnrolled)
                @if(!empty($heroAcademicParts))
                    <div class="hero-course" @if($linkedRoleLabel) style="display: none;" @endif>
                        {{ implode(' • ', $heroAcademicParts) }}
                    </div>
                @endif
                @if($linkedRoleLabel)
                    <div class="hero-course">
                        {{ $displayStudentNumber !== '' ? $displayStudentNumber . ' - ' : '' }}{{ $linkedRoleLabel }}
                    </div>
                @endif
            @else
                <div class="hero-course">Available once enrolled</div>
            @endif

            @if($isEnrolled)
                <div class="hero-status-line">{{ $heroActiveStatusLabel }}</div>
            @endif

            <div class="hero-stats">
                <div class="stat-item">
                    <span class="stat-icon"><x-outline-icon name="clock" /></span>
                    <span class="stat-val">{{ $pendingCount ?? 0 }}</span>
                    <span class="stat-label">Pending</span>
                </div>
                <div class="stat-item">
                    <span class="stat-icon"><x-outline-icon name="check" /></span>
                    <span class="stat-val">{{ $approvedCount ?? 0 }}</span>
                    <span class="stat-label">Approved</span>
                </div>
                <div class="stat-item">
                    <span class="stat-icon"><x-outline-icon name="clipboard-document-list" /></span>
                    <span class="stat-val">{{ $completedCount ?? 0 }}</span>
                    <span class="stat-label">Completed</span>
                </div>
                <div class="stat-item">
                    <span class="stat-icon"><x-outline-icon name="x-mark" /></span>
                    <span class="stat-val">{{ $cancelledCount ?? 0 }}</span>
                    <span class="stat-label">Cancelled</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="account-layout {{ in_array($accountView, ['health-record', 'notifications'], true) ? 'health-record-layout' : '' }}">
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
            <button type="button" id="editBtn" class="profile-edit-btn" onclick="enableEditing()">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                </svg>
                Edit Profile
            </button>
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
                    <section class="profile-form-section accent-gold profile-personal-section">
                        <h3 class="profile-form-section-title"><x-outline-icon name="user-circle" />Personal Details</h3>
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
                            <div>
                                <label class="input-label">Suffix Name</label>
                                <input type="text" class="form-control" value="{{ $localSuffixName }}" readonly>
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
                        @if($showClinicMeasurements)
                            <div class="profile-grid-2">
                                <div>
                                    <label class="input-label">Height (ft)</label>
                                    <div class="metric-field">
                                        <input type="text" name="height" class="form-control editable-input" inputmode="decimal" value="{{ $heightDisplay }}" disabled>
                                    </div>
                                </div>
                                <div>
                                    <label class="input-label">Weight (lbs)</label>
                                    <div class="metric-field">
                                        <input type="text" name="weight" class="form-control editable-input" inputmode="decimal" value="{{ $weightDisplay }}" disabled>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="profile-grid-2">
                            <div>
                                <label class="input-label">Blood Type</label>
                                <input type="text" class="form-control" value="{{ $bloodTypeDisplay }}" readonly>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="profile-column-stack">
                    <section class="profile-form-section accent-maroon profile-frame-equal profile-academic-section">
                        <h3 class="profile-form-section-title"><x-outline-icon name="document-text" />{{ $usesEmployeeHealthForm ? 'Employment Information' : 'Academic Information' }}</h3>
                        <div class="profile-grid-3">
                            @if($displayStudentNumber !== '')
                                <div>
                                    <label class="input-label">{{ $idNumberHeading }}</label>
                                    <div class="form-control profile-static-field">{{ $displayStudentNumber }}</div>
                                </div>
                            @endif
                            @if(!$usesEmployeeHealthForm && $displayCourse !== '')
                                <div>
                                    <label class="input-label">Course</label>
                                    <div class="form-control profile-course-field profile-static-field">{{ $displayCourse }}</div>
                                </div>
                            @endif
                            @if($usesEmployeeHealthForm && $displayOffice !== '')
                                <div>
                                    <label class="input-label">Office / Department</label>
                                    <div class="form-control profile-static-field">{{ $displayOffice }}</div>
                                </div>
                            @endif
                            @if($displayYear !== '')
                                <div>
                                    <label class="input-label">{{ $usesEmployeeHealthForm ? 'School Year' : 'Year' }}</label>
                                    <input type="text" name="year" class="form-control" value="{{ $displayYear }}" readonly>
                                </div>
                            @endif
                            @if($displaySection !== '')
                                <div>
                                    <label class="input-label">Section</label>
                                    <input type="text" name="section" class="form-control" value="{{ $displaySection }}" readonly>
                                </div>
                            @endif
                        </div>
                    </section>

                    <section class="profile-form-section accent-maroon profile-frame-equal profile-contact-section">
                        <h3 class="profile-form-section-title"><x-outline-icon name="envelope" />Contact Information</h3>
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

                    <section class="profile-form-section accent-gold profile-emergency-section">
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
                @if($displayStudentNumber !== '')
                    <div class="profile-info-row">
                        <label class="input-label">{{ $idNumberHeading }}</label>
                        <div class="form-control profile-static-field">{{ $displayStudentNumber }}</div>
                    </div>
                @endif
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

                @if($showClinicMeasurements)
                    <div class="profile-grid-2">
                        <div>
                            <label class="input-label">Height (ft)</label>
                            <div class="metric-field">
                                <input type="text" name="height" class="form-control editable-input" inputmode="decimal" value="{{ $heightDisplay }}" disabled>
                            </div>
                        </div>
                        <div>
                            <label class="input-label">Weight (lbs)</label>
                            <div class="metric-field">
                                <input type="text" name="weight" class="form-control editable-input" inputmode="decimal" value="{{ $weightDisplay }}" disabled>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="profile-grid-2">
                    <div>
                        <label class="input-label">Blood Type</label>
                        <input type="text" class="form-control" value="{{ $bloodTypeDisplay }}" readonly>
                    </div>
                </div>
            </section>

            <section class="profile-form-section accent-gold">
                <h3 class="profile-form-section-title"><x-outline-icon name="envelope" />Contact Information</h3>
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
        $usesEmployeeHealthForm = $studentUsesEmployeeHealthForm ?? false;
        $healthProfileRecord = $usesEmployeeHealthForm ? $user->employeeHealthProfile : $user->healthProfile;
        $healthFormRoute = $usesEmployeeHealthForm ? route('health.form.employee') : route('health.form');
        $healthFormSubmitted = $hasSubmittedHealthProfile ?? ($healthProfileRecord !== null);
        $status = optional($healthProfileRecord)->clearance_status ?? 'For Verification';
        $statusNormalized = strtolower(trim((string) $status));
        $isIssuedStatus = in_array($statusNormalized, ['issued', 'fully cleared'], true);
        $isRejectedStatus = $statusNormalized === 'rejected';
        $isConditionalStatus = str_contains($statusNormalized, 'pending') || str_contains($statusNormalized, 'conditional');
        $isPendingStatus = !$isIssuedStatus && !$isRejectedStatus;
        $recordPendingReason = trim((string) optional($healthProfileRecord)->pending_reason);
        $recordPendingReasonSearch = strtolower($recordPendingReason);
        $requiresHealthFormCorrection = str_contains($recordPendingReasonSearch, 'health form correction')
            || ($isConditionalStatus && collect([
                'health information form',
                'health form',
                'correct address',
                'home address',
                'correct information',
                'correct details',
            ])->contains(fn ($needle) => str_contains($recordPendingReasonSearch, $needle)));
        $resubmissionDocuments = collect(
            optional($healthProfileRecord)->resubmission_required_documents
                ?? optional($healthProfileRecord)->resubmission_required_fields
                ?? []
        )->filter()->values();
        $hasDisabilityValue = optional($healthProfileRecord)->has_disability;
        $requiresPwdIdProof = is_bool($hasDisabilityValue)
            ? $hasDisabilityValue
            : in_array(strtolower(trim((string) $hasDisabilityValue)), ['1', 'yes', 'true'], true);
        $resubmissionDocuments = $resubmissionDocuments
            ->filter(fn ($documentKey) => $documentKey !== 'pwd_id_proof' || $requiresPwdIdProof)
            ->values();
        $isResubmissionStatus = $statusNormalized === 'pending resubmission'
            || $resubmissionDocuments->isNotEmpty()
            || $requiresHealthFormCorrection;
        $resubmissionDocumentLabels = [
            'student_photo' => '2x2 Student Photo',
            'health_declaration' => 'Declaration of Medical Information and Data Subject Consent Form',
            'medical_certificate' => 'Medical Certificate',
            'chest_xray_result' => 'Chest X-ray Result',
            'pwd_id_proof' => 'PWD ID Proof',
        ];
        $resubmissionDocumentMeta = [
            'student_photo' => ['accept' => '.jpg,.jpeg,.png,image/jpeg,image/png', 'hint' => 'JPG or PNG, up to 1 MB'],
            'health_declaration' => ['accept' => '.pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png', 'hint' => 'PDF, JPG, or PNG, up to 1 MB'],
            'medical_certificate' => ['accept' => '.pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png', 'hint' => 'PDF, JPG, or PNG, up to 2 MB'],
            'chest_xray_result' => ['accept' => '.pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png', 'hint' => 'PDF, JPG, or PNG, up to 2 MB'],
            'pwd_id_proof' => ['accept' => '.pdf,application/pdf', 'hint' => 'PDF only, up to 2 MB'],
        ];
        $healthRecordDocumentFields = $usesEmployeeHealthForm
            ? [
                'student_photo' => 'student_photo',
                'health_declaration' => 'health_declaration',
                'medical_certificate' => 'medical_certificate',
                'chest_xray_result' => 'chest_xray_document',
                'pwd_id_proof' => 'pwd_id_proof',
            ]
            : [
                'student_photo' => 'student_photo',
                'health_declaration' => 'health_declaration',
                'medical_certificate' => 'medical_certificate',
                'chest_xray_result' => 'chest_xray_result',
                'pwd_id_proof' => 'pwd_id_proof',
            ];
        $hasStoredHealthRecordDocument = function ($documentPath): bool {
            $documentPath = trim((string) $documentPath);
            if ($documentPath === '') {
                return false;
            }

            if (filter_var($documentPath, FILTER_VALIDATE_URL)) {
                return true;
            }

            $documentPath = preg_replace('#^(?:public/)?storage/#', '', ltrim($documentPath, '/')) ?? $documentPath;
            return app(\App\Services\HealthFileStorage::class)->exists($documentPath);
        };
        $healthRecordMissingDocumentKeys = collect(array_keys($resubmissionDocumentLabels))
            ->filter(fn ($documentKey) => ($documentKey !== 'pwd_id_proof' || $requiresPwdIdProof)
                && $healthFormSubmitted
                && $healthProfileRecord
                && !$hasStoredHealthRecordDocument(data_get($healthProfileRecord, $healthRecordDocumentFields[$documentKey] ?? $documentKey)))
            ->values();
        $clinicResubmissionUploadKeys = $resubmissionDocuments
            ->filter(fn ($documentKey) => isset($resubmissionDocumentLabels[$documentKey]))
            ->unique()
            ->values();
        $missingDocumentUploadKeys = $healthRecordMissingDocumentKeys
            ->diff($clinicResubmissionUploadKeys)
            ->filter(fn ($documentKey) => isset($resubmissionDocumentLabels[$documentKey]))
            ->values();
        $hasResubmissionUploadErrors = $clinicResubmissionUploadKeys->contains(fn ($documentKey) => $errors->has($documentKey));
        $hasDocumentUploadErrors = $missingDocumentUploadKeys->contains(fn ($documentKey) => $errors->has($documentKey));
        $requiresMissingESign = $healthFormSubmitted && $healthProfileRecord && (
            $usesEmployeeHealthForm
                ? blank(optional($healthProfileRecord)->staff_signature) && blank(optional($healthProfileRecord)->uploaded_signature_path)
                : blank(optional($healthProfileRecord)->digital_signature)
        );
        $hasExistingESign = $healthFormSubmitted && $healthProfileRecord && (
            $usesEmployeeHealthForm
                ? filled(optional($healthProfileRecord)->staff_signature) || filled(optional($healthProfileRecord)->uploaded_signature_path)
                : filled(optional($healthProfileRecord)->digital_signature)
        );
        $existingESignValue = $usesEmployeeHealthForm
            ? trim((string) (optional($healthProfileRecord)->uploaded_signature_path ?: optional($healthProfileRecord)->staff_signature))
            : trim((string) optional($healthProfileRecord)->digital_signature);
        $existingESignPreviewSrc = '';
        if ($hasExistingESign && $existingESignValue !== '') {
            $existingESignPreviewSrc = str_starts_with($existingESignValue, 'data:image/')
                ? $existingESignValue
                : route('student.health_record.signature');
        }
        $hasMissingESignErrors = $errors->has('digital_signature_data') || $errors->has('digital_signature_upload') || $errors->has('signature_method');
        $recordSubmissionStatus = $isResubmissionStatus ? 'Pending Resubmission' : ($isConditionalStatus ? 'Pending Compliance' : 'Waiting for clinic review');
        $recordStatusMessage = $requiresHealthFormCorrection && $resubmissionDocuments->isEmpty()
            ? 'The clinic requested corrections to your Health Information Form. Open the form below and update the requested information.'
            : ($isResubmissionStatus
            ? 'The clinic requested replacement files for your health profile. Upload only the selected requirement/s below.'
            : ($isConditionalStatus
                ? 'Your health profile needs follow-up before it can be issued. Please check the pending reason and coordinate with the Medical Clinic.'
                : 'Your health profile has been submitted. Please proceed to the Medical Clinic on your designated schedule for medical review.'));
        $recordStatusNote = $requiresHealthFormCorrection && $resubmissionDocuments->isEmpty()
            ? 'Your approved record remains available while the requested Health Form correction is pending.'
            : ($isResubmissionStatus
            ? 'Your existing valid files will remain unchanged. Only the requested replacement file/s will be updated.'
            : ($isConditionalStatus
                ? 'Please complete the pending requirement before your record can be marked as issued.'
                : 'Clinic approval is required before your record can be marked as issued.'));
        $puptasSyncStatus = $usesEmployeeHealthForm ? null : optional($healthProfileRecord)->puptas_sync_status;
        $puptasSyncMessage = $usesEmployeeHealthForm ? '' : trim((string) optional($healthProfileRecord)->puptas_sync_message);
        $puptasSyncedAt = $usesEmployeeHealthForm ? null : optional(optional($healthProfileRecord)->puptas_synced_at)->format('M d, Y g:i A');
        $formatRecordTimestamp = function ($value): ?string {
            if (!$value) {
                return null;
            }

            try {
                return \Carbon\Carbon::parse($value)->format('M d, Y g:i A');
            } catch (\Throwable $exception) {
                return null;
            }
        };
        $latestHealthFormSubmission = collect($healthFormSubmissions ?? [])->first();
        $recordSubmittedSource = optional($latestHealthFormSubmission)->submitted_at
            ?: optional($healthProfileRecord)->resubmitted_at
            ?: optional($healthProfileRecord)->created_at;
        $recordApprovedSource = optional($latestHealthFormSubmission)->approved_at
            ?: optional($healthProfileRecord)->verified_at;
        $recordReviewSource = optional($healthProfileRecord)->review_started_at
            ?: ($healthFormSubmitted ? $recordSubmittedSource : null);
        $recordSubmittedAt = $formatRecordTimestamp($recordSubmittedSource);
        $recordApprovedAt = $formatRecordTimestamp($recordApprovedSource);
        $recordReviewAt = $formatRecordTimestamp($recordReviewSource);
        $recordLastUpdatedAt = $formatRecordTimestamp(optional($healthProfileRecord)->updated_at ?: $recordSubmittedSource);
        $recordHeroStatus = match (true) {
            !$healthFormSubmitted => 'Not Submitted',
            $isIssuedStatus => 'Approved',
            $isRejectedStatus => 'Rejected',
            $isResubmissionStatus => 'Pending Resubmission',
            $isConditionalStatus => 'Pending Compliance',
            default => 'Under Review',
        };
        $recordHeroStatusClass = match (true) {
            $isIssuedStatus => 'is-approved',
            $isRejectedStatus => 'is-rejected',
            default => 'is-pending',
        };
        $clinicScheduleLabel = app(\App\Services\ClinicWorkflowService::class)->clinicScheduleLabel();
        $recordReferenceNumber = trim((string) optional($healthProfileRecord)->reference_number);
        $recordReferenceNumber = $recordReferenceNumber !== '' ? $recordReferenceNumber : trim((string) ($user->reference_number ?? '-'));
        $hasHealthDeclaration = filled(optional($healthProfileRecord)->health_declaration);
        $healthDeclarationUploadError = $errors->has('health_declaration');
        $healthRecordMissingRequirements = collect();
        $healthRecordMissingDocumentKeys
            ->each(function ($documentKey) use ($healthRecordMissingRequirements, $resubmissionDocumentLabels) {
                $healthRecordMissingRequirements->push([
                    'key' => $documentKey,
                    'title' => $resubmissionDocumentLabels[$documentKey],
                ]);
            });
        $resubmissionDocuments
            ->filter(fn ($documentKey) => isset($resubmissionDocumentLabels[$documentKey]))
            ->each(function ($documentKey) use ($healthRecordMissingRequirements, $resubmissionDocumentLabels) {
                if (!$healthRecordMissingRequirements->contains('key', $documentKey)) {
                    $healthRecordMissingRequirements->push([
                        'key' => $documentKey,
                        'title' => $resubmissionDocumentLabels[$documentKey],
                    ]);
                }
            });
        if ($requiresMissingESign && !$healthRecordMissingRequirements->contains('key', 'digital_signature')) {
            $healthRecordMissingRequirements->push([
                'key' => 'digital_signature',
                'title' => 'Missing E-signature',
            ]);
        }
        $healthRecordMissingHasDocuments = $healthRecordMissingRequirements->contains(fn ($requirement) => ($requirement['key'] ?? '') !== 'digital_signature');
        $healthRecordMissingHasESign = $healthRecordMissingRequirements->contains('key', 'digital_signature');
        $healthRecordMissingTitle = match (true) {
            $healthRecordMissingHasDocuments && $healthRecordMissingHasESign => 'Missing Documents / E-sign',
            $healthRecordMissingHasESign => 'Missing E-sign',
            default => 'Missing Documents',
        };
        $documentUploadKeysForModal = $healthRecordMissingDocumentKeys
            ->merge($clinicResubmissionUploadKeys)
            ->filter(fn ($documentKey) => isset($resubmissionDocumentLabels[$documentKey]))
            ->unique()
            ->values();
        $documentModalUsesClinicReplacement = $missingDocumentUploadKeys->isEmpty() && $clinicResubmissionUploadKeys->isNotEmpty();
        $recordStudentNumber = trim((string) (
            optional($healthProfileRecord)->student_number
            ?: ($accountProfileData['student_number'] ?? $user->student_number ?? '')
        ));
        if ($looksLikeReferenceNumber($recordStudentNumber)) {
            $recordStudentNumber = '';
        }
        $recordBirthday = trim((string) optional($healthProfileRecord)->birthday);
        $recordBirthday = $recordBirthday !== '' ? optional(\Carbon\Carbon::parse($recordBirthday))->format('M d, Y') : '-';
        $recordChestXrayDate = optional(optional($healthProfileRecord)->xray_date)->format('M d, Y');
        $recordMedicalIssuedAt = optional(optional($healthProfileRecord)->med_cert_date)->format('M d, Y');
        $recordCourseValue = trim((string) (optional($healthProfileRecord)->course_college ?: ($accountProfileData['course_college'] ?? $user->course ?? '')));
        $recordCourseLabel = strcasecmp($recordCourseValue, 'Faculty / Staff') === 0 ? 'Classification' : 'Course';
        $recordAcademicYear = trim((string) (optional($healthProfileRecord)->school_year ?: '2025-2026'));
    @endphp
    <div class="health-record-dashboard">
        <section class="page-hero health-record-hero" aria-labelledby="healthRecordReviewTitle">
            <div class="health-record-hero-main">
                <span class="health-record-hero-emblem" aria-hidden="true">
                    <x-outline-icon name="clipboard-document-list" />
                </span>
                <div class="health-record-hero-copy">
                    <div class="page-hero-kicker">Health Record</div>
                    <h1 class="page-hero-title" id="healthRecordReviewTitle">Record Review</h1>
                    <p class="page-hero-text">Track your submitted profile, clinic approval, and uploaded digital copies in one place.</p>
                    <div class="page-hero-steps" aria-label="Health record progress">
                        <div class="page-hero-step {{ $healthFormSubmitted ? 'is-complete' : '' }}">
                            <span>Submitted</span>
                        </div>
                        <div class="page-hero-step {{ $isIssuedStatus ? 'is-complete' : ($healthFormSubmitted ? 'is-active' : '') }}">
                            <span>Under Review</span>
                        </div>
                        <div class="page-hero-step {{ $isIssuedStatus ? 'is-complete' : '' }}">
                            <span>Issued</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="health-record-hero-overview">
                <div class="health-record-hero-overview-item">
                    <span class="health-record-hero-overview-icon {{ $recordHeroStatusClass }}" aria-hidden="true">
                        <x-outline-icon name="{{ $isIssuedStatus ? 'check' : ($isRejectedStatus ? 'x-mark' : 'clock') }}" />
                    </span>
                    <span>
                        <span class="health-record-hero-overview-label">Status</span>
                        <strong class="health-record-hero-overview-value">{{ $recordHeroStatus }}</strong>
                    </span>
                </div>
                <div class="health-record-hero-overview-item">
                    <span class="health-record-hero-overview-icon" aria-hidden="true">
                        <x-outline-icon name="calendar-days" />
                    </span>
                    <span>
                        <span class="health-record-hero-overview-label">Last Updated</span>
                        <strong class="health-record-hero-overview-value">{{ $recordLastUpdatedAt ?: '-' }}</strong>
                    </span>
                </div>
            </div>
        </section>

        @include('student.partials.health-record-dashboard')

        @if(false)
        <div class="health-record-content-grid">
            <div class="health-record-main-column">
    @if(!empty($pendingHealthFormRequest))
        <div class="health-declaration-card">
            <div class="health-declaration-head">
                <div>
                    <div class="health-declaration-title">
                        <x-outline-icon name="document-text" />
                        New Health Form Requested: {{ $pendingHealthFormRequest->category }}
                    </div>
                    <p class="health-declaration-note">
                        {{ $pendingHealthFormRequest->remarks ?: 'The clinic requested a fresh Health Information Form. Your latest details will be prefilled.' }}
                    </p>
                </div>
                <a href="{{ route('health.form') }}" class="btn-print-form pending">
                    <x-outline-icon name="document-text" />
                    Fill Up New Health Form
                </a>
            </div>
        </div>
    @endif
    @if($healthRecordMissingRequirements->isNotEmpty())
        <div class="missing-requirements-panel">
            <div class="missing-requirements-main">
                <div class="missing-requirements-lead">
                    <span class="missing-requirements-icon" aria-hidden="true">
                        <x-outline-icon name="exclamation-triangle" />
                    </span>
                    <div>
                        <h2 class="missing-requirements-title">
                            <x-outline-icon name="document-text" />
                            {{ $healthRecordMissingTitle }} ({{ $healthRecordMissingRequirements->count() }})
                        </h2>
                        <p class="missing-requirements-copy">Some files are not attached yet. Upload available documents here when your program or office asks for them.</p>
                    </div>
                </div>

                <ol class="missing-requirements-list">
                    @foreach($healthRecordMissingRequirements as $missingIndex => $missingRequirement)
                        <li class="missing-requirement-item">{{ $missingRequirement['title'] }}</li>
                    @endforeach
                </ol>
                <div class="missing-requirements-footer">
                    @if($documentUploadKeysForModal->isNotEmpty())
                        <button
                            type="button"
                            class="missing-requirement-action"
                            data-missing-document-open
                            data-action="{{ $documentModalUsesClinicReplacement ? route('student.health_record.resubmit') : route('student.health_record.documents') }}"
                        >View</button>
                    @elseif($requiresMissingESign)
                        <button type="button" class="missing-requirement-action" onclick="openMissingESignModal()">View</button>
                    @endif
                </div>
            </div>

            <div class="missing-requirements-reminder">
                <span class="missing-requirements-reminder-icon" aria-hidden="true">
                    <x-outline-icon name="information-circle" />
                </span>
                <div>
                    <strong>Important Reminders</strong>
                    <p>Your submitted documents cannot be edited while under clinic review. For corrections, please contact the Medical Clinic.</p>
                </div>
            </div>
        </div>
    @endif
    <div class="health-status-card">
        <div class="health-status-head">
            <span class="health-status-title">
                <x-outline-icon name="clipboard-document-list" />
                Record Summary
            </span>
        </div>

        <div class="health-record-status-body">

        @if($healthFormSubmitted)
            @if($isIssuedStatus)
                <div class="health-status-summary is-approved">
                    <span class="health-status-state issued"><x-outline-icon name="shield-check" /> Approved</span>
                    <p class="health-status-message">Your health profile is approved and already available in your health record.</p>
                </div>

                <div class="health-status-meta-grid">
                    <div class="health-status-meta">
                        <span class="health-status-meta-icon" aria-hidden="true"><x-outline-icon name="identification" /></span>
                        <span class="health-status-meta-copy">
                            <span class="health-status-meta-label">{{ $referenceHeading }}</span>
                            <span class="health-status-meta-value">{{ $recordReferenceNumber !== '' ? $recordReferenceNumber : '-' }}</span>
                        </span>
                    </div>
                    <div class="health-status-meta">
                        <span class="health-status-meta-icon" aria-hidden="true"><x-outline-icon name="calendar-days" /></span>
                        <span class="health-status-meta-copy">
                            <span class="health-status-meta-label">Submitted At</span>
                            <span class="health-status-meta-value">{{ $recordSubmittedAt ?: '-' }}</span>
                        </span>
                    </div>
                    <div class="health-status-meta">
                        <span class="health-status-meta-icon" aria-hidden="true"><x-outline-icon name="clock" /></span>
                        <span class="health-status-meta-copy">
                            <span class="health-status-meta-label">Approve Date</span>
                            <span class="health-status-meta-value">{{ $recordApprovedAt ?: '-' }}</span>
                        </span>
                    </div>
                </div>

                @if($requiresHealthFormCorrection)
                    <div class="health-correction-notice">
                        <x-outline-icon name="exclamation-circle" />
                        <span class="health-correction-copy">
                            <strong>Health Form correction requested.</strong>
                            <span>{{ $recordPendingReason !== '' ? $recordPendingReason : 'Health Form Correction' }}</span>
                        </span>
                        <a href="{{ $healthFormRoute }}" class="health-correction-details">
                            View Details
                            <x-outline-icon name="arrow-long-right" />
                        </a>
                    </div>
                @endif

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
                    <button type="button" class="health-record-action-card is-view" onclick="openHealthRecordModal()">
                        <span class="health-record-action-icon" aria-hidden="true"><x-outline-icon name="eye" /></span>
                        <span class="health-record-action-copy">
                            <span class="health-record-action-title">View Record Details</span>
                            <span class="health-record-action-description">Review all submitted information and documents.</span>
                        </span>
                        <span class="health-record-action-arrow" aria-hidden="true"><x-outline-icon name="chevron-right" /></span>
                    </button>
                    @if($requiresHealthFormCorrection)
                        <a href="{{ $healthFormRoute }}" class="health-record-action-card is-edit">
                            <span class="health-record-action-icon" aria-hidden="true"><x-outline-icon name="pencil-square" /></span>
                            <span class="health-record-action-copy">
                                <span class="health-record-action-title">Edit Health Form</span>
                                <span class="health-record-action-description">Update your health form information.</span>
                            </span>
                            <span class="health-record-action-arrow" aria-hidden="true"><x-outline-icon name="chevron-right" /></span>
                        </a>
                    @endif
                    <a href="{{ route('student.health_record.document', ['document' => 'health_form']) }}" class="health-record-action-card is-download" download>
                        <span class="health-record-action-icon" aria-hidden="true"><x-outline-icon name="arrow-down-tray" /></span>
                        <span class="health-record-action-copy">
                            <span class="health-record-action-title">Download Health Form</span>
                            <span class="health-record-action-description">Download a copy of your health form (PDF).</span>
                        </span>
                        <span class="health-record-action-arrow" aria-hidden="true"><x-outline-icon name="chevron-right" /></span>
                    </a>
                </div>
                <span class="health-status-note"><x-outline-icon name="calendar-days" /> Valid for Academic Year {{ $recordAcademicYear }}</span>
            @else
                <div class="health-status-summary is-pending">
                    @if($isConditionalStatus)
                        <span class="health-status-state pending"><x-outline-icon name="clock" /> {{ $isResubmissionStatus ? 'Pending Resubmission' : 'Pending Compliance' }}</span>
                    @endif
                    <p class="health-status-message">{{ $recordStatusMessage }}</p>
                </div>

                <div class="health-status-meta-grid">
                    <div class="health-status-meta">
                        <span class="health-status-meta-icon" aria-hidden="true"><x-outline-icon name="identification" /></span>
                        <span class="health-status-meta-copy">
                            <span class="health-status-meta-label">{{ $referenceHeading }}</span>
                            <span class="health-status-meta-value">{{ $recordReferenceNumber !== '' ? $recordReferenceNumber : '-' }}</span>
                        </span>
                    </div>
                    <div class="health-status-meta">
                        <span class="health-status-meta-icon" aria-hidden="true"><x-outline-icon name="calendar-days" /></span>
                        <span class="health-status-meta-copy">
                            <span class="health-status-meta-label">Submitted At</span>
                            <span class="health-status-meta-value">{{ $recordSubmittedAt ?: '-' }}</span>
                        </span>
                    </div>
                    <div class="health-status-meta">
                        <span class="health-status-meta-icon" aria-hidden="true"><x-outline-icon name="clock" /></span>
                        <span class="health-status-meta-copy">
                            <span class="health-status-meta-label">Approve Date</span>
                            <span class="health-status-meta-value">{{ $recordApprovedAt ?: '-' }}</span>
                        </span>
                    </div>
                </div>

                @if($requiresHealthFormCorrection)
                    <div class="health-correction-notice">
                        <x-outline-icon name="exclamation-circle" />
                        <span class="health-correction-copy">
                            <strong>Health Form correction requested.</strong>
                            <span>{{ $recordPendingReason !== '' ? $recordPendingReason : 'Health Form Correction' }}</span>
                        </span>
                        <a href="{{ $healthFormRoute }}" class="health-correction-details">
                            View Details
                            <x-outline-icon name="arrow-long-right" />
                        </a>
                    </div>
                @endif

                <div class="health-status-actions">
                    <button type="button" class="health-record-action-card is-view" onclick="openHealthRecordModal()">
                        <span class="health-record-action-icon" aria-hidden="true"><x-outline-icon name="eye" /></span>
                        <span class="health-record-action-copy">
                            <span class="health-record-action-title">View Record Details</span>
                            <span class="health-record-action-description">Review all submitted information and documents.</span>
                        </span>
                        <span class="health-record-action-arrow" aria-hidden="true"><x-outline-icon name="chevron-right" /></span>
                    </button>
                    @if($requiresHealthFormCorrection)
                        <a href="{{ $healthFormRoute }}" class="health-record-action-card is-edit">
                            <span class="health-record-action-icon" aria-hidden="true"><x-outline-icon name="pencil-square" /></span>
                            <span class="health-record-action-copy">
                                <span class="health-record-action-title">Edit Health Form</span>
                                <span class="health-record-action-description">Update your health form information.</span>
                            </span>
                            <span class="health-record-action-arrow" aria-hidden="true"><x-outline-icon name="chevron-right" /></span>
                        </a>
                    @endif
                    <a href="{{ route('student.health_record.document', ['document' => 'health_form']) }}" class="health-record-action-card is-download" download>
                        <span class="health-record-action-icon" aria-hidden="true"><x-outline-icon name="arrow-down-tray" /></span>
                        <span class="health-record-action-copy">
                            <span class="health-record-action-title">Download Health Form</span>
                            <span class="health-record-action-description">Download a copy of your health form (PDF).</span>
                        </span>
                        <span class="health-record-action-arrow" aria-hidden="true"><x-outline-icon name="chevron-right" /></span>
                    </a>
                </div>
                <span class="health-status-note"><x-outline-icon name="calendar-days" /> {{ $recordStatusNote }}</span>
            @endif
            @else
                <div class="health-status-summary is-incomplete">
                    <span class="health-status-state incomplete"><x-outline-icon name="x-mark" /> Not Yet Submitted</span>
                    <p class="health-status-message">Your health profile has not been submitted yet.</p>
                </div>
                <a href="{{ $healthFormRoute }}" class="btn-print-form incomplete">
                    <x-outline-icon name="document-text" />
                    Complete Form Now
                </a>
            <span class="health-status-note"><x-outline-icon name="calendar-days" /> Submit your health profile to unlock clinic review.</span>
        @endif
        </div>
    </div>
            </div>

            <aside class="health-record-sidebar" aria-label="Health record status and reminders">
                <section class="health-record-side-card">
                    <h2 class="health-record-side-title">
                        <span class="health-record-side-title-icon is-yellow" aria-hidden="true">
                            <x-outline-icon name="clipboard-document-list" />
                        </span>
                        Record Status
                    </h2>
                    <ol class="health-record-progress-list">
                        <li class="health-record-progress-item {{ $healthFormSubmitted ? 'is-complete' : '' }}">
                            <span class="health-record-progress-dot" aria-hidden="true"></span>
                            <span class="health-record-progress-copy">
                                <strong>Submitted</strong>
                                <span>{{ $recordSubmittedAt ?: 'Waiting for submission' }}</span>
                            </span>
                            @if($healthFormSubmitted)
                                <span class="health-record-progress-check" aria-label="Completed"><x-outline-icon name="check" /></span>
                            @endif
                        </li>
                        <li class="health-record-progress-item {{ $isIssuedStatus ? 'is-complete' : ($healthFormSubmitted ? 'is-active' : '') }}">
                            <span class="health-record-progress-dot" aria-hidden="true"></span>
                            <span class="health-record-progress-copy">
                                <strong>Under Review</strong>
                                <span>{{ $recordReviewAt ?: ($healthFormSubmitted ? 'Clinic review in progress' : 'Waiting for submission') }}</span>
                            </span>
                            @if($isIssuedStatus)
                                <span class="health-record-progress-check" aria-label="Completed"><x-outline-icon name="check" /></span>
                            @endif
                        </li>
                        <li class="health-record-progress-item {{ $isIssuedStatus ? 'is-complete' : '' }}">
                            <span class="health-record-progress-dot" aria-hidden="true"></span>
                            <span class="health-record-progress-copy">
                                <strong>Issued</strong>
                                <span>{{ $recordApprovedAt ?: 'Waiting for clinic approval' }}</span>
                            </span>
                            @if($isIssuedStatus)
                                <span class="health-record-progress-check" aria-label="Completed"><x-outline-icon name="check" /></span>
                            @endif
                        </li>
                    </ol>
                </section>

                <section class="health-record-side-card is-reminder">
                    <h2 class="health-record-side-title">
                        <span class="health-record-side-title-icon" aria-hidden="true">
                            <x-outline-icon name="bell" />
                        </span>
                        Important Reminder
                    </h2>
                    <p class="health-record-reminder-copy"><strong>Clinic schedule:</strong><br>{{ $clinicScheduleLabel }}</p>
                    <p class="health-record-reminder-copy">Please make sure your selected clinic visit or follow-up time falls within this schedule.</p>
                </section>
            </aside>
        </div>
    </div>
    @if($healthFormSubmitted && $healthProfileRecord)
        <div class="record-modal-overlay" id="healthRecordModal" aria-hidden="true">
            <div class="record-modal health-record-details-modal" role="dialog" aria-modal="true" aria-labelledby="healthRecordModalTitle">
                <div class="record-modal-head">
                    <span class="health-record-details-head-icon" aria-hidden="true">
                        <x-outline-icon name="clipboard-document-list" />
                    </span>
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
                                        'key' => 'health_form',
                                        'title' => 'Health Information Form',
                                        'meta' => 'Saved PDF Snapshot',
                                        'path' => $usesEmployeeHealthForm ? optional($healthProfileRecord)->staff_health_form_pdf_path : 'saved-health-form.pdf',
                                        'is_image' => false,
                                    ],
                                    [
                                        'key' => 'student_photo',
                                        'title' => '2x2 Student Photo',
                                        'meta' => 'Image Upload',
                                        'path' => optional($healthProfileRecord)->student_photo,
                                        'is_image' => true,
                                    ],
                                    [
                                        'key' => 'health_declaration',
                                        'title' => 'Declaration of Medical Information and Data Subject Consent Form',
                                        'meta' => 'PDF or Image Upload',
                                        'path' => optional($healthProfileRecord)->health_declaration,
                                        'is_image' => false,
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
                                        'path' => $usesEmployeeHealthForm ? optional($healthProfileRecord)->chest_xray_document : optional($healthProfileRecord)->chest_xray_result,
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
                        <div class="record-modal-card is-full">
                            <span class="record-modal-label">Health Form History</span>
                            <div class="record-modal-links">
                                @forelse(($healthFormSubmissions ?? collect()) as $submission)
                                    <div class="record-document-card">
                                        <div class="record-document-preview" aria-hidden="true">
                                            <x-outline-icon name="document-text" />
                                        </div>
                                        <div class="record-document-body">
                                            <span class="record-document-title">{{ $submission->category }}</span>
                                            <span class="record-document-meta">
                                                {{ ucwords(str_replace('_', ' ', $submission->status)) }}
                                                {{ $submission->school_year ? ' - ' . $submission->school_year : '' }}
                                                {{ $submission->submitted_at ? ' - ' . $submission->submitted_at->format('M d, Y h:i A') : '' }}
                                            </span>
                                            <div class="record-document-actions">
                                                <a class="record-document-btn" href="{{ route('student.health_form.submission', $submission->id) }}" target="_blank" rel="noopener noreferrer">View PDF</a>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="record-modal-empty">No saved Health Form PDFs yet.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="record-modal-body-fade" aria-hidden="true"></div>
                </div>
            </div>
        </div>
    @endif
        @endif
    @if($clinicResubmissionUploadKeys->isNotEmpty() || $missingDocumentUploadKeys->isNotEmpty() || session('signature_attached') || session('signature_removed'))
        <div class="record-modal-overlay" id="missingDocumentModal" aria-hidden="true">
            <div class="record-modal missing-document-modal" role="dialog" aria-modal="true" aria-labelledby="missingDocumentTitle">
                <div class="record-modal-head">
                    <span class="missing-document-head-icon" aria-hidden="true">
                        <x-outline-icon name="exclamation-triangle" />
                    </span>
                    <button type="button" class="record-modal-close" aria-label="Close missing document modal" onclick="closeMissingDocumentModal()">
                        <x-outline-icon name="x-mark" />
                    </button>
                    <div class="record-modal-head-main">
                        <h2 class="record-modal-title" id="missingDocumentTitle">Missing Documents</h2>
                        <p class="record-modal-subtitle">Upload the missing requirement files for your health record.</p>
                    </div>
                </div>
                <form class="missing-document-body" method="POST" action="{{ $documentModalUsesClinicReplacement ? route('student.health_record.resubmit') : route('student.health_record.documents') }}" enctype="multipart/form-data" id="missingDocumentForm">
                    @csrf
                    @php
                        $missingDocumentDescriptions = [
                            'student_photo' => 'Upload a clear 2x2 ID picture with white background.',
                            'health_declaration' => 'Upload the accomplished and signed declaration form.',
                            'medical_certificate' => 'Upload a valid medical certificate from a licensed physician.',
                            'chest_xray_result' => 'Upload the chest x-ray result. Clear and readable file.',
                            'pwd_id_proof' => 'Upload your valid PWD ID proof document.',
                        ];
                        $missingDocumentCount = $documentUploadKeysForModal->count();
                    @endphp
                    <div class="missing-document-progress">
                        <div>
                            <p class="missing-document-progress-title">Requirements Progress</p>
                            <div class="missing-document-progress-track">
                                <span class="missing-document-progress-bar">
                                    <span class="missing-document-progress-fill" data-missing-document-progress-fill></span>
                                </span>
                                <span class="missing-document-progress-count"><span data-missing-document-uploaded-count>0</span> of {{ $missingDocumentCount }} Uploaded</span>
                            </div>
                        </div>
                        <div class="missing-document-stat">
                            <span class="missing-document-stat-icon" aria-hidden="true">
                                <x-outline-icon name="document-text" />
                            </span>
                            <div>
                                <strong>{{ $missingDocumentCount }} Documents</strong>
                                <span>Required</span>
                            </div>
                        </div>
                        <div class="missing-document-stat">
                            <span class="missing-document-stat-icon" aria-hidden="true">
                                <x-outline-icon name="check" />
                            </span>
                            <div>
                                <strong>PDF, JPG, PNG</strong>
                                <span>Accepted</span>
                            </div>
                        </div>
                        <div class="missing-document-stat">
                            <span class="missing-document-stat-icon" aria-hidden="true">
                                <x-outline-icon name="clipboard-document-list" />
                            </span>
                            <div>
                                <strong>2 MB</strong>
                                <span>Max size</span>
                            </div>
                        </div>
                    </div>
                    <div class="missing-document-scroll">
                        @foreach($documentUploadKeysForModal as $documentKey)
                            @php
                                $documentMeta = $resubmissionDocumentMeta[$documentKey] ?? ['accept' => '.pdf,.jpg,.jpeg,.png', 'hint' => 'PDF, JPG, or PNG'];
                                $isPhotoRequirement = $documentKey === 'student_photo';
                                $isDeclarationRequirement = $documentKey === 'health_declaration';
                                $isMedicalRequirement = $documentKey === 'medical_certificate';
                                $isXrayRequirement = $documentKey === 'chest_xray_result';
                            @endphp
                            <div class="missing-document-row">
                                <span class="missing-document-row-icon" aria-hidden="true">
                                    @if($isPhotoRequirement)
                                        <x-outline-icon name="user-circle" />
                                    @elseif($isMedicalRequirement)
                                        <x-outline-icon name="heart-pulse" />
                                    @elseif($isXrayRequirement)
                                        <x-outline-icon name="clipboard-document-list" />
                                    @else
                                        <x-outline-icon name="document-text" />
                                    @endif
                                </span>
                                <div>
                                    <h3 class="missing-document-row-title">
                                        {{ $resubmissionDocumentLabels[$documentKey] }}
                                        <span class="missing-document-required">Required</span>
                                    </h3>
                                    <p class="missing-document-description">{{ $missingDocumentDescriptions[$documentKey] ?? 'Upload the required document file.' }}</p>
                                    <p class="missing-document-meta">Accepted: {{ $isPhotoRequirement ? 'JPG, PNG' : 'PDF, JPG, PNG' }} &nbsp;•&nbsp; Max size: {{ in_array($documentKey, ['medical_certificate', 'chest_xray_result', 'pwd_id_proof'], true) ? '2 MB' : '1 MB' }}</p>
                                    @if($errors->has($documentKey))
                                        <em class="field-error-message">{{ $errors->first($documentKey) }}</em>
                                    @endif
                                </div>
                                <div class="missing-document-upload-box" data-missing-document-box>
                                    <input
                                        id="missing_document_{{ $documentKey }}"
                                        type="file"
                                        name="{{ $documentKey }}"
                                        accept="{{ $documentMeta['accept'] }}"
                                        data-missing-document-input
                                        {{ $documentModalUsesClinicReplacement ? 'required' : '' }}
                                    >
                                    <span>
                                        <span class="missing-document-drop-icon" aria-hidden="true">
                                            <x-outline-icon name="document-text" />
                                        </span>
                                        <span class="missing-document-upload-title">Drag & drop file here</span>
                                        <span class="missing-document-upload-or">or</span>
                                        <label class="missing-document-choose" for="missing_document_{{ $documentKey }}">Choose File</label>
                                        <span class="missing-document-file-name" data-missing-document-file-name>No file chosen</span>
                                    </span>
                                    <span class="missing-document-preview" data-missing-document-preview>
                                        <span class="missing-document-preview-thumb" data-missing-document-preview-thumb>FILE</span>
                                        <span class="missing-document-preview-copy">
                                            <strong data-missing-document-preview-name>Selected file</strong>
                                            <span data-missing-document-preview-size>Ready to upload</span>
                                        </span>
                                        <span class="missing-document-preview-actions">
                                            <label for="missing_document_{{ $documentKey }}">Replace</label>
                                            <button type="button" data-missing-document-remove>Remove</button>
                                        </span>
                                    </span>
                                </div>
                            </div>
                        @endforeach
                        @if($requiresMissingESign)
                            <div class="missing-document-esign-row">
                                <span class="missing-document-row-icon" aria-hidden="true">
                                    <x-outline-icon name="pencil-square" />
                                </span>
                                <div>
                                    <h3 class="missing-document-row-title">
                                        E-signature
                                        <span class="missing-document-required">{{ $requiresMissingESign ? 'Required' : 'Attached' }}</span>
                                    </h3>
                                    @if($hasExistingESign)
                                        <p class="missing-document-description">Your e-signature is attached to your Health Information Form PDF.</p>
                                        <p class="missing-document-meta">Signature preview ready</p>
                                    @else
                                        <p class="missing-document-description">Add your drawn or uploaded signature so it can be attached to your Health Information Form PDF.</p>
                                        <p class="missing-document-meta">Draw or upload PNG, JPG &nbsp;•&nbsp; Max size: 1 MB</p>
                                    @endif
                                </div>
                                @if($hasExistingESign)
                                    <div class="missing-document-upload-box is-selected">
                                        <span class="missing-document-preview">
                                            <span class="missing-document-preview-thumb">
                                                <img src="{{ $existingESignPreviewSrc }}" alt="E-signature preview">
                                            </span>
                                            <span class="missing-document-preview-copy">
                                                <strong>E-signature attached</strong>
                                                <span>Ready for PDF use</span>
                                            </span>
                                            <span class="missing-document-preview-actions">
                                                <button type="button" onclick="closeMissingDocumentModal(); openMissingESignModal()">Replace</button>
                                                <button type="submit" form="removeESignForm">Remove</button>
                                            </span>
                                        </span>
                                    </div>
                                @else
                                    <button type="button" class="missing-document-esign-action" onclick="closeMissingDocumentModal(); openMissingESignModal()">Add E-sign</button>
                                @endif
                            </div>
                        @endif
                    </div>
                    <div class="missing-document-footer">
                        <div class="missing-document-secure">
                            <span class="missing-document-secure-icon" aria-hidden="true">
                                <x-outline-icon name="check" />
                            </span>
                            <div>
                                <strong>Your files are secure</strong>
                                <span>Your documents are encrypted and will only be used for verification purposes.</span>
                            </div>
                        </div>
                        <div class="missing-upload-actions">
                            <button type="button" class="missing-esign-secondary" onclick="closeMissingDocumentModal()">Cancel</button>
                            <button type="submit" class="missing-esign-submit">
                                <x-outline-icon name="document-text" />
                                Submit Missing Documents
                            </button>
                        </div>
                    </div>
                        @if($hasDocumentUploadErrors || $hasResubmissionUploadErrors)
                            <div class="field-error-message">Please check the selected file and upload again.</div>
                        @endif
                </form>
                @if($hasExistingESign)
                    <form method="POST" action="{{ route('student.health_record.e_signature.remove') }}" id="removeESignForm">
                        @csrf
                    </form>
                @endif
            </div>
        </div>
    @endif
    @if($requiresMissingESign)
        <div class="record-modal-overlay" id="missingESignModal" aria-hidden="true">
            <div class="record-modal missing-esign-modal" role="dialog" aria-modal="true" aria-labelledby="missingESignTitle">
                <div class="record-modal-head">
                    <button type="button" class="record-modal-close" aria-label="Close missing e-sign modal" onclick="closeMissingESignModal()">
                        <x-outline-icon name="x-mark" />
                    </button>
                    <div class="record-modal-head-main">
                        <h2 class="record-modal-title" id="missingESignTitle">Missing E-sign</h2>
                        <p class="record-modal-subtitle">Add your signature so it can be attached to your Health Information Form PDF.</p>
                    </div>
                </div>
                <form class="missing-esign-body" method="POST" action="{{ route('student.health_record.e_signature') }}" enctype="multipart/form-data" id="missingESignForm">
                    @csrf
                    <input type="hidden" name="replace_existing" value="{{ $hasExistingESign ? '1' : '0' }}">
                    <input type="hidden" name="digital_signature_data" id="missingESignData" value="{{ old('digital_signature_data') }}">
                    <div class="missing-esign-methods">
                        <input type="radio" name="signature_method" id="missing_signature_draw" value="draw" {{ old('signature_method', 'draw') === 'draw' ? 'checked' : '' }}>
                        <label for="missing_signature_draw">Draw Signature</label>
                        <input type="radio" name="signature_method" id="missing_signature_upload" value="upload" {{ old('signature_method') === 'upload' ? 'checked' : '' }}>
                        <label for="missing_signature_upload">Upload PNG</label>
                    </div>
                    <div class="missing-esign-panel" id="missingESignDrawPanel">
                        <div class="missing-esign-card">
                            <span class="missing-esign-label">Draw your e-signature</span>
                            <div class="missing-esign-pad-wrap">
                                <canvas id="missingESignPad" aria-label="Draw your e-signature"></canvas>
                            </div>
                            <p class="missing-esign-hint" id="missingESignStatus">No drawn signature yet.</p>
                            @if($errors->has('digital_signature_data'))
                                <div class="field-error-message">{{ $errors->first('digital_signature_data') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="missing-esign-panel is-hidden" id="missingESignUploadPanel">
                        <div class="missing-esign-card">
                            <label class="missing-esign-label" for="missingESignUpload">Upload signature image</label>
                            <input class="missing-esign-upload" id="missingESignUpload" type="file" name="digital_signature_upload" accept=".png,.jpg,.jpeg,image/png,image/jpeg">
                            <p class="missing-esign-hint">PNG only, preferably black ink on transparent or white background. Max file size: 1 MB.</p>
                            @if($errors->has('digital_signature_upload'))
                                <div class="field-error-message">{{ $errors->first('digital_signature_upload') }}</div>
                            @endif
                        </div>
                    </div>
                    @if($errors->has('signature_method'))
                        <div class="field-error-message">{{ $errors->first('signature_method') }}</div>
                    @endif
                    <div class="missing-esign-actions">
                        <button type="button" class="missing-esign-secondary" id="clearMissingESignBtn">Clear</button>
                        <button type="submit" class="missing-esign-submit">Attach E-sign</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@else
    @php
        $notificationCounts = array_merge([
            'total' => 0,
            'unread' => 0,
            'appointments' => 0,
            'health-records' => 0,
            'announcements' => 0,
            'system' => 0,
        ], $notificationCounts ?? []);
        $notificationFilter = $notificationFilter ?? 'all';
        $allStudentNotifications = collect($notifications ?? [])->values();
        $notificationItems = isset($notificationPaginator)
            ? collect($notificationPaginator->items())
            : $allStudentNotifications;
        $lastNotification = $allStudentNotifications->first();
        $notificationFilterLinks = [
            'all' => ['All', $notificationCounts['total']],
            'unread' => ['Unread', $notificationCounts['unread']],
            'appointments' => ['Appointments', $notificationCounts['appointments']],
            'health-records' => ['Health Records', $notificationCounts['health-records']],
            'announcements' => ['Announcements', $notificationCounts['announcements']],
            'system' => ['System', $notificationCounts['system']],
        ];
    @endphp
    <div class="notification-dashboard">
        <section class="page-hero health-record-hero notification-hero" aria-labelledby="studentNotificationsTitle">
            <div class="health-record-hero-main">
                <span class="health-record-hero-emblem" aria-hidden="true">
                    <x-outline-icon name="bell" />
                </span>
                <div class="health-record-hero-copy">
                    <div class="page-hero-kicker">Clinic Updates</div>
                    <h1 class="page-hero-title" id="studentNotificationsTitle">Notifications</h1>
                    <p class="page-hero-text">Stay updated with clinic announcements, appointment changes, and important health record activities.</p>
                    <div class="page-hero-steps" aria-label="Notification categories">
                        <div class="page-hero-step">
                            <x-outline-icon name="megaphone" />
                            <span>Clinic Updates</span>
                        </div>
                        <div class="page-hero-step">
                            <x-outline-icon name="calendar-days" />
                            <span>Appointment Alerts</span>
                        </div>
                        <div class="page-hero-step">
                            <x-outline-icon name="clipboard-document-list" />
                            <span>Record Status</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="health-record-hero-overview">
                <div class="health-record-hero-overview-item">
                    <span class="health-record-hero-overview-icon" aria-hidden="true">
                        <x-outline-icon name="envelope" />
                    </span>
                    <span>
                        <span class="health-record-hero-overview-label">Unread</span>
                        <strong class="health-record-hero-overview-value">{{ $notificationCounts['unread'] }} {{ \Illuminate\Support\Str::plural('Notification', $notificationCounts['unread']) }}</strong>
                    </span>
                </div>
                <div class="health-record-hero-overview-item">
                    <span class="health-record-hero-overview-icon" aria-hidden="true">
                        <x-outline-icon name="clock" />
                    </span>
                    <span>
                        <span class="health-record-hero-overview-label">Last Activity</span>
                        <strong class="health-record-hero-overview-value">{{ $lastNotification['time'] ?? 'No activity yet' }}</strong>
                    </span>
                </div>
            </div>
        </section>

        <section class="notification-stat-grid" aria-label="Notification summary">
            <a href="{{ url('/student/account') }}?view=notifications&amp;filter=unread" class="notification-stat-card is-unread">
                <span class="notification-stat-icon"><x-outline-icon name="envelope" /></span>
                <span class="notification-stat-copy">
                    <span class="notification-stat-label">Unread</span>
                    <strong>{{ $notificationCounts['unread'] }}</strong>
                    <small>New notifications</small>
                </span>
            </a>
            <a href="{{ url('/student/account') }}?view=notifications&amp;filter=appointments" class="notification-stat-card is-appointments">
                <span class="notification-stat-icon"><x-outline-icon name="calendar-days" /></span>
                <span class="notification-stat-copy">
                    <span class="notification-stat-label">Appointments</span>
                    <strong>{{ $notificationCounts['appointments'] }}</strong>
                    <small>Appointment related</small>
                </span>
            </a>
            <a href="{{ url('/student/account') }}?view=notifications&amp;filter=health-records" class="notification-stat-card is-health">
                <span class="notification-stat-icon"><x-outline-icon name="clipboard-document-list" /></span>
                <span class="notification-stat-copy">
                    <span class="notification-stat-label">Health Records</span>
                    <strong>{{ $notificationCounts['health-records'] }}</strong>
                    <small>Record related</small>
                </span>
            </a>
            <a href="{{ url('/student/account') }}?view=notifications&amp;filter=announcements" class="notification-stat-card is-announcements">
                <span class="notification-stat-icon"><x-outline-icon name="megaphone" /></span>
                <span class="notification-stat-copy">
                    <span class="notification-stat-label">Announcements</span>
                    <strong>{{ $notificationCounts['announcements'] }}</strong>
                    <small>Clinic announcements</small>
                </span>
            </a>
        </section>

        <div class="notification-content-grid">
            <main class="notification-feed-card">
                <nav class="notification-filter-row" aria-label="Filter notifications">
                    @foreach($notificationFilterLinks as $filterKey => [$filterLabel, $filterCount])
                        <a
                            href="{{ url('/student/account') }}?view=notifications&amp;filter={{ $filterKey }}"
                            class="notification-filter-chip {{ $notificationFilter === $filterKey ? 'is-active' : '' }}"
                            @if($notificationFilter === $filterKey) aria-current="page" @endif
                        >
                            {{ $filterLabel }} <span>{{ $filterCount }}</span>
                        </a>
                    @endforeach
                </nav>

                <div class="notification-feed-list">
                    @forelse($notificationItems as $notif)
                        <a
                            href="{{ route('student.notifications.open', ['notificationId' => $notif['id']]) }}"
                            class="notification-feed-item is-{{ $notif['category'] ?? 'system' }} {{ !empty($notif['is_unread']) ? 'is-unread' : '' }}"
                        >
                            <span class="notification-letter-icon" aria-hidden="true">{{ $notif['letter_icon'] ?? 'SY' }}</span>
                            <span class="notification-feed-copy">
                                <span class="notification-feed-heading">
                                    <strong>{{ $notif['title'] ?? 'Clinic Update' }}</strong>
                                    <span class="notification-category-badge">{{ $notif['category_label'] ?? 'System' }}</span>
                                </span>
                                @if(empty($notif['announcement_id']))
                                    <span class="notification-feed-message">{{ $notif['message'] ?? 'Notification available.' }}</span>
                                @endif
                            </span>
                            <span class="notification-feed-meta">
                                <time>{{ $notif['time'] ?? 'Just now' }}</time>
                                <span class="notification-feed-action">
                                    {{ $notif['action_label'] ?? 'Open' }}
                                    <x-outline-icon name="chevron-right" />
                                </span>
                            </span>
                        </a>
                    @empty
                        <div class="notification-empty-state">
                            <span class="notification-empty-icon"><x-outline-icon name="bell" /></span>
                            <strong>No notifications found</strong>
                            <p>There are no updates in this category right now.</p>
                        </div>
                    @endforelse
                </div>

                @if(isset($notificationPaginator) && $notificationPaginator->hasPages())
                    <nav class="notification-pagination" aria-label="Notification pages">
                        <a href="{{ $notificationPaginator->previousPageUrl() ?: '#' }}" class="notification-page-arrow {{ $notificationPaginator->onFirstPage() ? 'is-disabled' : '' }}" aria-label="Previous page">
                            <x-outline-icon name="chevron-right" />
                        </a>
                        @for($page = 1; $page <= $notificationPaginator->lastPage(); $page++)
                            <a href="{{ $notificationPaginator->url($page) }}" class="notification-page-number {{ $notificationPaginator->currentPage() === $page ? 'is-active' : '' }}" @if($notificationPaginator->currentPage() === $page) aria-current="page" @endif>{{ $page }}</a>
                        @endfor
                        <a href="{{ $notificationPaginator->nextPageUrl() ?: '#' }}" class="notification-page-arrow {{ !$notificationPaginator->hasMorePages() ? 'is-disabled' : '' }}" aria-label="Next page">
                            <x-outline-icon name="chevron-right" />
                        </a>
                    </nav>
                @endif
            </main>

            <aside class="notification-sidebar">
                <section class="notification-side-card">
                    <h2 class="notification-side-title">
                        <span><x-outline-icon name="chart-bar" /></span>
                        Notification Center
                    </h2>
                    <dl class="notification-center-list">
                        <div><dt><i class="is-unread"></i>Unread</dt><dd>{{ $notificationCounts['unread'] }}</dd></div>
                        <div><dt><i></i>Read</dt><dd>{{ max(0, $notificationCounts['total'] - $notificationCounts['unread']) }}</dd></div>
                        <div><dt><i></i>Total Notifications</dt><dd>{{ $notificationCounts['total'] }}</dd></div>
                    </dl>
                    <div class="notification-last-read">
                        <span>Last Activity</span>
                        <strong>{{ $lastNotification['time'] ?? 'No activity yet' }}</strong>
                    </div>
                </section>

                <section class="notification-side-card">
                    <h2 class="notification-side-title">
                        <span><x-outline-icon name="sparkles" /></span>
                        Quick Actions
                    </h2>
                    <div class="notification-quick-actions">
                        <form action="{{ route('student.notifications.read_all') }}" method="POST">
                            @csrf
                            <button type="submit" {{ $notificationCounts['unread'] === 0 ? 'disabled' : '' }}>
                                <x-outline-icon name="envelope" />
                                <span>Mark all as read</span>
                                <x-outline-icon name="chevron-right" />
                            </button>
                        </form>
                        <a href="{{ url('/student/account') }}?view=notifications&amp;filter=unread">
                            <x-outline-icon name="bell" />
                            <span>View unread updates</span>
                            <x-outline-icon name="chevron-right" />
                        </a>
                        <a href="{{ url('/student/account') }}?view=notifications&amp;filter=all">
                            <x-outline-icon name="clipboard-document-list" />
                            <span>View all notifications</span>
                            <x-outline-icon name="chevron-right" />
                        </a>
                    </div>
                </section>
            </aside>
        </div>
    </div>
@endif
        </div>
@if($accountView === 'profile')
    </div>
@endif
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

function openMissingESignModal() {
    const modal = document.getElementById('missingESignModal');
    if (!modal) {
        return;
    }
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    window.setTimeout(function () {
        window.resizeMissingESignPad && window.resizeMissingESignPad();
    }, 60);
}

function closeMissingESignModal() {
    const modal = document.getElementById('missingESignModal');
    if (!modal) {
        return;
    }
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
}

function openMissingDocumentModal(button = null) {
    const modal = document.getElementById('missingDocumentModal');
    const form = document.getElementById('missingDocumentForm');
    if (!modal || !form) {
        return;
    }

    if (button?.dataset?.action) {
        form.action = button.dataset.action;
    }
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
}

function closeMissingDocumentModal() {
    const modal = document.getElementById('missingDocumentModal');
    if (!modal) {
        return;
    }
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
}

function initializeHealthDeclarationPreview(root = document) {
    root.querySelectorAll('[data-health-declaration-input]').forEach(function (input) {
        if (input.dataset.previewBound === 'true') {
            return;
        }

        input.dataset.previewBound = 'true';
        input.addEventListener('change', function () {
            window.updateHealthDeclarationPreview(input);
        });
    });
}

window.updateHealthDeclarationPreview = function (input) {
    const form = input.closest('form');
    const file = input.files && input.files[0] ? input.files[0] : null;
    const filename = form?.querySelector('[data-health-declaration-filename]');
    const preview = form?.querySelector('[data-health-declaration-preview]');
    const thumb = form?.querySelector('[data-health-declaration-preview-thumb]');
    const previewName = form?.querySelector('[data-health-declaration-preview-name]');
    const previewSize = form?.querySelector('[data-health-declaration-preview-size]');
    const submitButton = form?.querySelector('.health-declaration-submit');

    if (filename) {
        filename.textContent = file ? file.name : 'No file chosen';
    }

    if (!preview || !thumb || !previewName || !previewSize) {
        return;
    }

    preview.classList.remove('is-visible');
    thumb.replaceChildren();
    thumb.textContent = 'FILE';
    previewName.textContent = 'Selected file';
    previewSize.textContent = 'Ready to upload';

    if (!file) {
        return;
    }

    preview.classList.add('is-visible');
    previewName.textContent = file.name;
    previewSize.textContent = `${(file.size / 1024 / 1024).toFixed(2)} MB / 1 MB limit`;
    if (submitButton && submitButton.textContent.trim() === 'Upload Consent Form') {
        submitButton.textContent = 'Upload Selected File';
    }

    if (file.type && file.type.startsWith('image/')) {
        const image = document.createElement('img');
        image.alt = '';
        image.src = URL.createObjectURL(file);
        image.onload = function () {
            URL.revokeObjectURL(image.src);
        };
        thumb.replaceChildren(image);
    } else {
        thumb.textContent = (file.name.split('.').pop() || 'PDF').slice(0, 4).toUpperCase();
    }
};

function enableEditing() {
    alert('Edit Profile is currently unavailable for now. Please contact the clinic staff if any information needs correction.');
}

document.addEventListener('DOMContentLoaded', function () {
    const profileHeroCard = document.getElementById('profileHeroCard');
    const profilePhotoToggle = document.getElementById('profilePhotoToggle');
    const profileDashboard = profileHeroCard?.closest('.profile-dashboard');
    const modal = document.getElementById('healthRecordModal');
    const modalCard = modal?.querySelector('.record-modal');
    const missingDocumentModal = document.getElementById('missingDocumentModal');
    const missingESignModal = document.getElementById('missingESignModal');
    const missingESignCanvas = document.getElementById('missingESignPad');
    const missingESignData = document.getElementById('missingESignData');
    const missingESignStatus = document.getElementById('missingESignStatus');
    const missingESignForm = document.getElementById('missingESignForm');
    const missingESignUpload = document.getElementById('missingESignUpload');
    const missingESignRadios = Array.from(document.querySelectorAll('input[name="signature_method"]'));
    const missingESignDrawPanel = document.getElementById('missingESignDrawPanel');
    const missingESignUploadPanel = document.getElementById('missingESignUploadPanel');
    const clearMissingESignBtn = document.getElementById('clearMissingESignBtn');
    const shouldOpenMissingESignModal = @json($hasMissingESignErrors ?? false);
    const shouldOpenMissingDocumentModal = @json(($hasDocumentUploadErrors ?? false) || ($hasResubmissionUploadErrors ?? false) || session('signature_attached') || session('signature_removed'));

    profilePhotoToggle?.addEventListener('click', function () {
        const isExpanded = profileHeroCard?.classList.toggle('is-photo-expanded') ?? false;
        profileDashboard?.classList.toggle('is-photo-expanded', isExpanded);
        profilePhotoToggle.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
        profilePhotoToggle.setAttribute('aria-label', isExpanded ? 'Collapse profile photo' : 'Expand profile photo');
    });

    if (typeof updateHealthRecordModalIndicator === 'function') {
        modalCard?.addEventListener('scroll', updateHealthRecordModalIndicator);
    }
    initializeHealthDeclarationPreview(document);

    if (shouldOpenMissingESignModal) {
        openMissingESignModal();
    }
    if (shouldOpenMissingDocumentModal) {
        const missingDocumentButton = document.querySelector('[data-missing-document-open]');
        if (missingDocumentButton) {
            openMissingDocumentModal(missingDocumentButton);
        }
    }

    modal?.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeHealthRecordModal();
        }
    });
    missingESignModal?.addEventListener('click', function (event) {
        if (event.target === missingESignModal) {
            closeMissingESignModal();
        }
    });
    missingDocumentModal?.addEventListener('click', function (event) {
        if (event.target === missingDocumentModal) {
            closeMissingDocumentModal();
        }
    });
    document.querySelectorAll('[data-missing-document-open]').forEach(function (button) {
        button.addEventListener('click', function () {
            openMissingDocumentModal(button);
        });
    });
    const syncMissingDocumentProgress = function () {
        const inputs = Array.from(document.querySelectorAll('[data-missing-document-input]'));
        const uploadedCount = inputs.filter(function (input) {
            return input.files && input.files.length > 0;
        }).length;
        const countNode = document.querySelector('[data-missing-document-uploaded-count]');
        const fillNode = document.querySelector('[data-missing-document-progress-fill]');
        if (countNode) {
            countNode.textContent = uploadedCount;
        }
        if (fillNode) {
            fillNode.style.width = inputs.length ? `${Math.round((uploadedCount / inputs.length) * 100)}%` : '0%';
        }
    };
    document.querySelectorAll('[data-missing-document-input]').forEach(function (input) {
        input.addEventListener('change', function () {
            const box = input.closest('[data-missing-document-box]');
            const file = input.files && input.files[0] ? input.files[0] : null;
            const nameNode = box?.querySelector('[data-missing-document-file-name]');
            const previewName = box?.querySelector('[data-missing-document-preview-name]');
            const previewSize = box?.querySelector('[data-missing-document-preview-size]');
            const previewThumb = box?.querySelector('[data-missing-document-preview-thumb]');
            if (nameNode) {
                nameNode.textContent = file ? file.name : 'No file chosen';
            }
            if (previewName) {
                previewName.textContent = file ? file.name : 'Selected file';
            }
            if (previewSize) {
                previewSize.textContent = file
                    ? `${(file.size / 1024 / 1024).toFixed(2)} MB / upload ready`
                    : 'Ready to upload';
            }
            if (previewThumb) {
                previewThumb.replaceChildren();
                if (file && file.type.startsWith('image/')) {
                    const image = document.createElement('img');
                    image.alt = '';
                    image.src = URL.createObjectURL(file);
                    image.onload = function () {
                        URL.revokeObjectURL(image.src);
                    };
                    previewThumb.appendChild(image);
                } else {
                    previewThumb.textContent = file ? (file.name.split('.').pop() || 'FILE').slice(0, 4).toUpperCase() : 'FILE';
                }
            }
            if (box) {
                box.classList.toggle('is-selected', Boolean(file));
            }
            syncMissingDocumentProgress();
        });
    });
    document.querySelectorAll('[data-missing-document-remove]').forEach(function (button) {
        button.addEventListener('click', function () {
            const box = button.closest('[data-missing-document-box]');
            const input = box?.querySelector('[data-missing-document-input]');
            if (!box || !input) {
                return;
            }
            input.value = '';
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
    });
    document.querySelectorAll('[data-missing-document-box]').forEach(function (box) {
        box.addEventListener('dragover', function (event) {
            event.preventDefault();
            box.classList.add('is-dragging');
        });
        box.addEventListener('dragleave', function () {
            box.classList.remove('is-dragging');
        });
        box.addEventListener('drop', function (event) {
            event.preventDefault();
            box.classList.remove('is-dragging');
            const input = box.querySelector('[data-missing-document-input]');
            const file = event.dataTransfer?.files?.[0];
            if (!input || !file || typeof DataTransfer === 'undefined') {
                return;
            }
            const transfer = new DataTransfer();
            transfer.items.add(file);
            input.files = transfer.files;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
    });
    syncMissingDocumentProgress();

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') {
            return;
        }
        if (modal?.classList.contains('is-open')) {
            return closeHealthRecordModal();
        }
        if (missingESignModal?.classList.contains('is-open')) {
            return closeMissingESignModal();
        }
        if (missingDocumentModal?.classList.contains('is-open')) {
            return closeMissingDocumentModal();
        }
    });

    if (missingESignCanvas && missingESignData) {
        const context = missingESignCanvas.getContext('2d');
        let drawing = false;
        let hasDrawing = false;

        const missingESignDataUrl = function () {
            const exportCanvas = document.createElement('canvas');
            exportCanvas.width = missingESignCanvas.width;
            exportCanvas.height = missingESignCanvas.height;
            const exportContext = exportCanvas.getContext('2d');
            exportContext.fillStyle = '#ffffff';
            exportContext.fillRect(0, 0, exportCanvas.width, exportCanvas.height);
            exportContext.drawImage(missingESignCanvas, 0, 0);

            return exportCanvas.toDataURL('image/jpeg', 0.92);
        };

        window.resizeMissingESignPad = function () {
            const rect = missingESignCanvas.getBoundingClientRect();
            const ratio = window.devicePixelRatio || 1;
            const previous = hasDrawing ? missingESignDataUrl() : '';
            missingESignCanvas.width = Math.max(1, Math.floor(rect.width * ratio));
            missingESignCanvas.height = Math.max(1, Math.floor(rect.height * ratio));
            context.setTransform(ratio, 0, 0, ratio, 0, 0);
            context.lineCap = 'round';
            context.lineJoin = 'round';
            context.lineWidth = 2.4;
            context.strokeStyle = '#111827';
            if (previous) {
                const image = new Image();
                image.onload = function () {
                    context.drawImage(image, 0, 0, rect.width, rect.height);
                    missingESignData.value = missingESignDataUrl();
                };
                image.src = previous;
            }
        };

        const pointerPosition = function (event) {
            const rect = missingESignCanvas.getBoundingClientRect();
            return {
                x: event.clientX - rect.left,
                y: event.clientY - rect.top,
            };
        };

        const startDrawing = function (event) {
            drawing = true;
            hasDrawing = true;
            missingESignCanvas.setPointerCapture?.(event.pointerId);
            const position = pointerPosition(event);
            context.beginPath();
            context.moveTo(position.x, position.y);
            event.preventDefault();
        };

        const draw = function (event) {
            if (!drawing) {
                return;
            }
            const position = pointerPosition(event);
            context.lineTo(position.x, position.y);
            context.stroke();
            missingESignData.value = missingESignDataUrl();
            if (missingESignStatus) {
                missingESignStatus.textContent = 'Drawn signature ready.';
            }
            event.preventDefault();
        };

        const stopDrawing = function () {
            drawing = false;
            if (hasDrawing) {
                missingESignData.value = missingESignDataUrl();
            }
        };

        missingESignCanvas.addEventListener('pointerdown', startDrawing);
        missingESignCanvas.addEventListener('pointermove', draw);
        missingESignCanvas.addEventListener('pointerup', stopDrawing);
        missingESignCanvas.addEventListener('pointercancel', stopDrawing);
        missingESignCanvas.addEventListener('pointerleave', stopDrawing);
        window.addEventListener('resize', window.resizeMissingESignPad);
        window.resizeMissingESignPad();

        clearMissingESignBtn?.addEventListener('click', function () {
            context.clearRect(0, 0, missingESignCanvas.width, missingESignCanvas.height);
            hasDrawing = false;
            missingESignData.value = '';
            if (missingESignUpload) {
                missingESignUpload.value = '';
            }
            if (missingESignStatus) {
                missingESignStatus.textContent = 'No drawn signature yet.';
            }
        });

        const syncMissingESignMode = function () {
            const selected = missingESignRadios.find((radio) => radio.checked)?.value || 'draw';
            const isUpload = selected === 'upload';
            missingESignDrawPanel?.classList.toggle('is-hidden', isUpload);
            missingESignUploadPanel?.classList.toggle('is-hidden', !isUpload);
            if (isUpload) {
                missingESignData.value = '';
            } else if (missingESignUpload) {
                missingESignUpload.value = '';
            }
        };

        missingESignRadios.forEach(function (radio) {
            radio.addEventListener('change', syncMissingESignMode);
        });
        syncMissingESignMode();

        missingESignForm?.addEventListener('submit', function (event) {
            const selected = missingESignRadios.find((radio) => radio.checked)?.value || 'draw';
            if (selected === 'draw' && !missingESignData.value.trim()) {
                event.preventDefault();
                missingESignData.setCustomValidity('Please draw your e-signature.');
                missingESignData.reportValidity();
                missingESignData.setCustomValidity('');
            }
        });
    }
});
</script>
@endsection
