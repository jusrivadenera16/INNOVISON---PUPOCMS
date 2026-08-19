@extends('layouts.admin')

@section('title', 'User Management')

@push('styles')
<style>
    .user-management-shell {
        max-width: 1024px;
        margin: 0 auto;
        padding: 18px 26px 34px;
        color: #0f172a;
    }

    .um-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 22px;
        padding: 0;
        background: transparent;
        box-shadow: none;
    }

    .um-hero h1 {
        margin: 0;
        font-size: 1.72rem;
        font-weight: 900;
        color: #111827;
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 0;
        border-bottom: 0;
        letter-spacing: 0;
    }

    .um-hero h1 svg {
        width: 20px;
        height: 20px;
        padding: 0;
        border-radius: 0;
        color: #9f1239;
        background: transparent;
        border: 0;
        box-shadow: none;
        flex: 0 0 auto;
    }

    .um-hero-title {
        display: grid;
        gap: 7px;
    }

    .um-hero-title::after {
        content: "";
        width: 278px;
        height: 2px;
        margin-left: 0;
        border-radius: 999px;
        background: #9f1239;
        box-shadow: none;
    }

    .um-hero p {
        margin: 5px 0 0;
        color: #475569;
        font-size: .95rem;
        font-weight: 600;
    }

    .um-entry-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 380px));
        justify-content: center;
        gap: 18px;
    }

    .um-entry-card {
        display: flex;
        flex-direction: column;
        text-decoration: none;
        position: relative;
        overflow: hidden;
        width: 100%;
        min-height: 250px;
        padding: 20px 20px 18px;
        border-radius: 16px;
        border: 1px solid rgba(112, 19, 27, 0.35);
        background:
            linear-gradient(135deg, rgba(255, 255, 255, .95), rgba(255, 252, 244, .86)),
            url('/images/PUPBG.jpg') center / cover no-repeat;
        box-shadow:
            0 12px 26px rgba(112, 19, 27, 0.08),
            inset 0 1px 0 rgba(255, 255, 255, .95);
        color: #1f2937;
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease, color .2s ease, background .2s ease;
        justify-self: center;
    }

    .um-entry-card > * {
        position: relative;
        z-index: 1;
    }

    .um-entry-card::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 88% 53%, rgba(112, 19, 27, .08), transparent 14%),
            radial-gradient(circle at 90% 62%, rgba(112, 19, 27, .06), transparent 9%),
            linear-gradient(135deg, rgba(255,255,255,.92), rgba(255,255,255,.78));
        pointer-events: none;
    }

    .um-entry-card::after {
        content: "";
        position: absolute;
        top: -38%;
        bottom: -38%;
        left: -135%;
        width: 34%;
        opacity: 0;
        background: linear-gradient(105deg, rgba(255, 255, 255, 0) 0%, rgba(250, 204, 21, 0.08) 42%, rgba(250, 204, 21, 0.32) 50%, rgba(250, 204, 21, 0.08) 58%, rgba(255, 255, 255, 0) 100%);
        transform: translateX(0) skewX(-18deg);
        pointer-events: none;
        z-index: 0;
    }

    .um-entry-card:hover {
        color: #70131B;
        transform: translateY(-6px);
        border-color: rgba(250, 204, 21, .82);
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.12),
            0 18px 34px rgba(112, 19, 27, 0.14);
    }

    .um-entry-card:hover::after {
        animation: umEntrySweep .92s ease both;
    }

    @keyframes umEntrySweep {
        0% {
            opacity: 0;
            transform: translateX(0) skewX(-18deg);
        }
        18% {
            opacity: .72;
        }
        72% {
            opacity: .72;
        }
        100% {
            opacity: 0;
            transform: translateX(820%) skewX(-18deg);
        }
    }

    .um-entry-icon {
        width: 58px;
        height: 58px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        margin-bottom: 18px;
        color: #9f1239;
        background: linear-gradient(135deg, rgba(112, 19, 27, .1), rgba(250, 204, 21, .16));
        border: 1px solid rgba(112, 19, 27, .1);
        position: relative;
        z-index: 1;
        animation: umEntryFloat 3.8s ease-in-out infinite;
        transition: background .22s ease, color .22s ease, border-color .22s ease, transform .22s ease;
    }

    .um-entry-icon::after {
        content: "";
        position: absolute;
        left: 18%;
        right: 18%;
        bottom: -10px;
        height: 14px;
        border-radius: 999px;
        filter: blur(8px);
        opacity: .18;
        z-index: -1;
        pointer-events: none;
        background: radial-gradient(circle, rgba(0, 0, 0, 0.44) 0%, rgba(0, 0, 0, 0.22) 48%, transparent 86%);
    }

    .um-entry-icon svg {
        width: 28px;
        height: 28px;
        display: block;
        stroke: currentColor;
    }

    .um-entry-chip {
        position: absolute;
        top: 20px;
        right: 18px;
        width: 30px;
        height: 30px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
        background: #70131B;
        color: #ffffff;
        border: 1px solid rgba(112, 19, 27, 0.2);
        box-shadow: 0 10px 18px rgba(112, 19, 27, 0.2);
        transition: background .2s ease, color .2s ease, border-color .2s ease;
    }

    .um-entry-chip svg {
        width: 15px;
        height: 15px;
        stroke: currentColor;
        stroke-width: 2.2;
        fill: none;
    }

    .um-entry-card h2 {
        margin: 0 0 12px;
        font-size: 1.24rem;
        font-weight: 900;
        color: #9f1239 !important;
        position: relative;
        z-index: 1;
        transition: color .2s ease;
    }

    .um-entry-card h2::after {
        content: "";
        display: block;
        width: 38px;
        height: 2px;
        margin-top: 10px;
        border-radius: 999px;
        background: #9f1239;
    }

    .um-entry-card p {
        margin: 0;
        max-width: 310px;
        color: #334155 !important;
        line-height: 1.45;
        font-size: .86rem;
        font-weight: 600;
        position: relative;
        z-index: 1;
        transition: color .2s ease;
    }

    .um-entry-card hr {
        width: 100%;
        margin: auto 0 12px;
        border: 0;
        border-top: 1px solid rgba(112, 19, 27, .12);
        position: relative;
        z-index: 1;
    }

    .um-entry-features {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
        margin: 15px 0 0;
        padding: 0;
        list-style: none;
        position: relative;
        z-index: 1;
    }

    .um-entry-features li {
        display: flex;
        align-items: center;
        gap: 7px;
        min-width: 0;
        color: #334155;
        font-size: .66rem;
        font-weight: 700;
        line-height: 1.25;
    }

    .um-entry-features span {
        width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        border-radius: 9px;
        color: #9f1239;
        background: rgba(112, 19, 27, .08);
    }

    .um-entry-features svg {
        width: 15px;
        height: 15px;
    }

    .um-entry-meta {
        min-height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        margin-top: 12px;
        padding: 0 14px;
        border-radius: 10px;
        background: #70131B;
        color: #ffffff !important;
        font-size: .78rem;
        font-weight: 900;
        position: relative;
        z-index: 1;
        overflow: hidden;
        box-shadow: 0 12px 24px rgba(112, 19, 27, 0.18);
        transition: color .2s ease, background .2s ease, transform .2s ease;
    }

    .um-entry-meta::before {
        content: "";
        position: absolute;
        inset: 0;
        background: #facc15;
        transform: translateX(-102%);
        transition: transform .46s ease;
        z-index: -1;
    }

    .um-entry-meta svg {
        width: 15px;
        height: 15px;
        flex: 0 0 auto;
    }

    .um-entry-card:hover .um-entry-icon,
    .um-entry-card:hover .um-entry-chip {
        background: #70131B;
        color: #ffffff;
        border-color: rgba(112, 19, 27, 0.62);
    }

    .um-entry-card:hover h2,
    .um-entry-card:hover p {
        color: #70131B !important;
    }

    .um-entry-card:hover .um-entry-meta {
        color: #111827 !important;
        transform: translateY(-1px);
    }

    .um-entry-card:hover .um-entry-meta::before {
        transform: translateX(0);
    }

    .um-entry-card.is-admin {
        border-color: rgba(112, 19, 27, 0.35);
    }

    .um-entry-card.is-admin .um-entry-icon,
    .um-entry-card.is-admin .um-entry-features span {
        color: #70131B;
        background: linear-gradient(135deg, rgba(112, 19, 27, .09), rgba(250, 204, 21, .14));
    }

    html[data-theme="dark"] .user-management-shell {
        color: #e5eefb;
    }

    html[data-theme="dark"] .um-hero {
        background: transparent;
        border-bottom-color: transparent;
        box-shadow: none;
    }

    html[data-theme="dark"] .um-hero h1 {
        color: #f8fafc;
        border-bottom-color: rgba(240, 209, 90, 0.82);
    }

    html[data-theme="dark"] .um-hero p,
    html[data-theme="dark"] .um-entry-card p {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .um-entry-card {
        background:
            linear-gradient(135deg, rgba(18, 24, 38, .94), rgba(39, 17, 23, .9)),
            url('/images/PUPBG.jpg') center / cover no-repeat;
        border-color: rgba(250, 204, 21, 0.62);
        box-shadow:
            0 14px 26px rgba(0, 0, 0, 0.22);
    }

    html[data-theme="dark"] .um-entry-card::before {
        background: linear-gradient(135deg, rgba(15, 23, 42, .78), rgba(15, 23, 42, .62));
    }

    html[data-theme="dark"] .um-entry-card::after {
        background: linear-gradient(105deg, rgba(255, 255, 255, 0) 0%, rgba(255, 248, 196, 0.52) 48%, rgba(255, 255, 255, 0) 100%);
    }

    html[data-theme="dark"] .um-entry-card:hover {
        border-color: #facc15;
    }

    html[data-theme="dark"] .um-entry-card h2,
    html[data-theme="dark"] .um-entry-card p {
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .um-entry-features li {
        color: #dbeafe;
    }

    html[data-theme="dark"] .um-entry-card:hover h2,
    html[data-theme="dark"] .um-entry-card:hover p {
        color: #facc15 !important;
    }

    .um-entry-grid {
        grid-template-columns: repeat(2, minmax(0, 380px)) !important;
        gap: 16px !important;
    }

    .um-entry-card,
    .um-entry-card.is-admin {
        min-height: 250px !important;
        padding: 20px !important;
        border-radius: 16px !important;
        border: 1px solid rgba(112, 19, 27, 0.38) !important;
        background: #70131B !important;
        color: #ffffff !important;
        box-shadow: 0 14px 26px rgba(112, 19, 27, 0.18) !important;
        justify-content: flex-start !important;
        gap: 18px !important;
        transition: transform .22s ease, border-color .22s ease, box-shadow .22s ease, background .22s ease, color .22s ease !important;
    }

    .um-entry-card::before {
        display: none !important;
    }

    .um-entry-card::after {
        background: linear-gradient(105deg, rgba(255,255,255,0) 0%, rgba(255,248,196,.42) 48%, rgba(255,255,255,0) 100%) !important;
        width: 42% !important;
        left: -125% !important;
        top: -42% !important;
        bottom: -42% !important;
    }

    .um-entry-card:hover,
    .um-entry-card.is-admin:hover {
        background: #facc15 !important;
        color: #70131B !important;
        transform: translateY(-6px) !important;
        border-color: #facc15 !important;
        box-shadow: 0 20px 34px rgba(112, 19, 27, 0.20) !important;
    }

    .um-entry-icon,
    .um-entry-card.is-admin .um-entry-icon {
        width: 58px !important;
        height: 58px !important;
        margin-bottom: 0 !important;
        border-radius: 14px !important;
        color: #facc15 !important;
        background: rgba(255, 255, 255, .12) !important;
        border: 1px solid rgba(255, 255, 255, .18) !important;
        animation: none !important;
    }

    .um-entry-icon::after {
        display: none !important;
    }

    .um-entry-icon svg {
        width: 30px !important;
        height: 30px !important;
    }

    .um-entry-card:hover .um-entry-icon,
    .um-entry-card:hover .um-entry-chip,
    .um-entry-card.is-admin:hover .um-entry-icon {
        color: #facc15 !important;
        background: rgba(112, 19, 27, .16) !important;
        border-color: rgba(112, 19, 27, .24) !important;
    }

    .um-entry-chip {
        top: 20px !important;
        right: 18px !important;
        background: rgba(255,255,255,.10) !important;
        color: #ffffff !important;
        border-color: rgba(255,255,255,.28) !important;
        box-shadow: none !important;
    }

    .um-entry-card:hover .um-entry-chip {
        color: #70131B !important;
        background: rgba(112, 19, 27, .10) !important;
        border-color: rgba(112, 19, 27, .22) !important;
    }

    .um-entry-card h2 {
        margin: 0 0 8px !important;
        color: #ffffff !important;
        font-size: 1.18rem !important;
        line-height: 1.15 !important;
        font-weight: 900 !important;
    }

    .um-entry-card h2::after {
        display: none !important;
    }

    .um-entry-card p {
        max-width: 420px !important;
        color: rgba(255,255,255,.92) !important;
        font-size: .9rem !important;
        line-height: 1.48 !important;
        font-weight: 700 !important;
    }

    .um-entry-card:hover h2,
    .um-entry-card:hover p,
    html[data-theme="dark"] .um-entry-card:hover h2,
    html[data-theme="dark"] .um-entry-card:hover p {
        color: #70131B !important;
    }

    .um-entry-features,
    .um-entry-card hr,
    .um-entry-meta {
        display: none !important;
    }

    html[data-theme="dark"] .um-entry-card,
    html[data-theme="dark"] .um-entry-card.is-admin {
        background: linear-gradient(135deg, #70131B, #8f2230) !important;
        border-color: rgba(250, 204, 21, .36) !important;
    }

    html[data-theme="dark"] .um-entry-card:hover,
    html[data-theme="dark"] .um-entry-card.is-admin:hover {
        background: #facc15 !important;
    }

    @keyframes umEntryFloat {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-5px);
        }
    }

    @media (max-width: 900px) {
        .um-entry-grid {
            grid-template-columns: 1fr;
        }
        .um-hero p,
        .um-hero-title::after {
            margin-left: 0;
        }
        .um-entry-card {
            min-height: 0;
        }
    }

    @media (max-width: 640px) {
        .user-management-shell {
            padding: 14px 12px 32px;
        }
        .um-entry-card {
            padding: 20px 18px;
        }
        .um-entry-features {
            grid-template-columns: 1fr;
        }
    }

    .user-management-shell {
        max-width: 1180px !important;
        padding: 24px 16px 34px !important;
    }

    .um-entry-grid {
        grid-template-columns: 1fr !important;
        justify-content: stretch !important;
        gap: 16px !important;
    }

    .um-entry-card,
    .um-entry-card.is-admin,
    html[data-theme="dark"] .um-entry-card,
    html[data-theme="dark"] .um-entry-card.is-admin {
        min-height: 100px !important;
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: flex-start !important;
        gap: 18px !important;
        overflow: hidden !important;
        padding: 20px 64px 20px 22px !important;
        border-radius: 10px !important;
        border: 1px solid rgba(112, 19, 27, .16) !important;
        background: #ffffff !important;
        color: #0f172a !important;
        box-shadow: 0 12px 26px rgba(15, 23, 42, .06) !important;
        transform: none !important;
    }

    .um-entry-card:hover,
    .um-entry-card.is-admin:hover {
        background: #facc15 !important;
        color: #70131B !important;
        transform: translateY(-2px) !important;
        border-color: #facc15 !important;
        box-shadow: 0 18px 34px rgba(112, 19, 27, .14) !important;
    }

    .um-entry-card::after {
        width: 42% !important;
        left: -125% !important;
        top: -42% !important;
        bottom: -42% !important;
        background: linear-gradient(105deg, rgba(255,255,255,0) 0%, rgba(250,204,21,.18) 48%, rgba(255,255,255,0) 100%) !important;
    }

    .um-entry-icon,
    .um-entry-card.is-admin .um-entry-icon {
        width: 58px !important;
        height: 58px !important;
        flex: 0 0 58px !important;
        display: grid !important;
        place-items: center !important;
        margin: 0 !important;
        border-radius: 999px !important;
        color: #70131B !important;
        background: linear-gradient(135deg, rgba(127, 29, 45, .08), rgba(250, 204, 21, .22)) !important;
        border: 1px solid rgba(112, 19, 27, .08) !important;
    }

    .um-entry-card:hover .um-entry-icon,
    .um-entry-card.is-admin:hover .um-entry-icon {
        color: #70131B !important;
        background: rgba(112, 19, 27, .16) !important;
        border-color: rgba(112, 19, 27, .24) !important;
    }

    .um-entry-chip {
        top: 50% !important;
        right: 18px !important;
        width: 30px !important;
        height: 30px !important;
        color: #70131B !important;
        background: transparent !important;
        border: 0 !important;
        box-shadow: none !important;
        transform: translateY(-50%) !important;
    }

    .um-entry-card:hover .um-entry-chip {
        color: #70131B !important;
        background: transparent !important;
        border-color: transparent !important;
        transform: translateY(-50%) translateX(4px) !important;
    }

    .um-entry-card h2 {
        margin: 0 0 6px !important;
        color: #0f172a !important;
        font-size: 17px !important;
        line-height: 1.15 !important;
        font-weight: 950 !important;
    }

    .um-entry-card h2::after {
        display: none !important;
    }

    .um-entry-card p {
        margin: 0 !important;
        max-width: 760px !important;
        color: #0f172a !important;
        font-size: 13px !important;
        line-height: 1.45 !important;
        font-weight: 700 !important;
    }

    .um-entry-card:hover h2,
    .um-entry-card:hover p,
    html[data-theme="dark"] .um-entry-card:hover h2,
    html[data-theme="dark"] .um-entry-card:hover p {
        color: #70131B !important;
    }

    html[data-theme="dark"] .um-entry-card,
    html[data-theme="dark"] .um-entry-card.is-admin {
        background: rgba(15, 23, 42, .92) !important;
        border-color: rgba(250, 204, 21, .18) !important;
    }

    html[data-theme="dark"] .um-entry-card h2,
    html[data-theme="dark"] .um-entry-card p {
        color: #ffffff !important;
    }

    html[data-theme="dark"] .um-entry-icon,
    html[data-theme="dark"] .um-entry-card.is-admin .um-entry-icon,
    html[data-theme="dark"] .um-entry-chip {
        color: #facc15 !important;
    }

    html[data-theme="dark"] .um-entry-icon,
    html[data-theme="dark"] .um-entry-card.is-admin .um-entry-icon {
        background: rgba(250, 204, 21, .10) !important;
        border-color: rgba(250, 204, 21, .22) !important;
    }

    html[data-theme="dark"] .um-entry-card:hover,
    html[data-theme="dark"] .um-entry-card.is-admin:hover {
        background: #facc15 !important;
    }

    html[data-theme="dark"] .um-entry-card:hover .um-entry-icon,
    html[data-theme="dark"] .um-entry-card.is-admin:hover .um-entry-icon,
    html[data-theme="dark"] .um-entry-card:hover .um-entry-chip {
        color: #70131B !important;
    }
</style>
@endpush

@section('content')
<div class="user-management-shell">
    <div class="um-hero">
        <div>
            <div class="um-hero-title">
                <h1><x-outline-icon name="user-plus" />Users Management</h1>
            </div>
            <p>Choose which user-management workspace you want to open.</p>
        </div>
    </div>

    <div class="um-entry-grid">
        <a href="{{ route('admin.user-management.account-access') }}" class="um-entry-card">
            <span class="um-entry-chip" aria-hidden="true">
                <svg viewBox="0 0 24 24">
                    <path d="M5 12h14"/>
                    <path d="M13 6l6 6-6 6"/>
                </svg>
            </span>
            <span class="um-entry-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                </svg>
            </span>
            <h2>Account Access</h2>
            <p>Manage clinic login role, student-side email, and active or inactive access for users already inside the clinic system.</p>
            <ul class="um-entry-features">
                <li>
                    <span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </span>
                    Login Roles
                </li>
                <li>
                    <span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                    </span>
                    Student-side Email
                </li>
                <li>
                    <span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m17 8 5 5"/><path d="m22 8-5 5"/></svg>
                    </span>
                    Active / Inactive Access
                </li>
            </ul>
            <hr>
            <div class="um-entry-meta">
                Open Account Access
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
            </div>
        </a>

        <a href="{{ route('admin.user-management.admin-hub') }}" class="um-entry-card is-admin">
            <span class="um-entry-chip" aria-hidden="true">
                <svg viewBox="0 0 24 24">
                    <path d="M5 12h14"/>
                    <path d="M13 6l6 6-6 6"/>
                </svg>
            </span>
            <span class="um-entry-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" />
                </svg>
            </span>
            <h2>Admin Hub Management</h2>
            <p>Manage Admin Designee profiles, office assignments, and directory-linked access without changing source account information.</p>
            <ul class="um-entry-features">
                <li>
                    <span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                    </span>
                    Admin Login Email
                </li>
                <li>
                    <span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </span>
                    Admin Type
                </li>
                <li>
                    <span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-3"/><path d="M9 9h.01"/><path d="M9 13h.01"/><path d="M9 17h.01"/></svg>
                    </span>
                    Office / Department
                </li>
            </ul>
            <hr>
            <div class="um-entry-meta">
                Open Admin Hub Management
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
            </div>
        </a>
    </div>
</div>
@endsection
