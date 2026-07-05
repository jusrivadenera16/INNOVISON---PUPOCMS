@extends('layouts.admin')

@section('title', 'User Management')

@push('styles')
<style>
    .user-management-shell {
        max-width: 1180px;
        margin: 0 auto;
        padding: 22px 24px 44px;
        color: #0f172a;
    }

    .um-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 34px;
        padding: 0;
        background: transparent;
        box-shadow: none;
    }

    .um-hero h1 {
        margin: 0;
        font-size: clamp(2rem, 3vw, 2.6rem);
        font-weight: 900;
        color: #111827;
        display: flex;
        align-items: center;
        gap: 18px;
        padding: 0;
        border-bottom: 0;
        letter-spacing: 0;
    }

    .um-hero h1 svg {
        width: 54px;
        height: 54px;
        padding: 13px;
        border-radius: 14px;
        color: #9f1239;
        background: linear-gradient(135deg, rgba(112, 19, 27, .08), rgba(250, 204, 21, .16));
        border: 1px solid rgba(112, 19, 27, .15);
        box-shadow: 0 12px 24px rgba(112, 19, 27, .08);
        flex: 0 0 auto;
    }

    .um-hero-title {
        display: grid;
        gap: 8px;
    }

    .um-hero-title::after {
        content: "";
        width: 58px;
        height: 3px;
        margin-left: 72px;
        border-radius: 999px;
        background: #9f1239;
        box-shadow: 48px 0 0 rgba(250, 204, 21, .85);
    }

    .um-hero p {
        margin: 6px 0 0 72px;
        color: #475569;
        font-size: 1rem;
        font-weight: 600;
    }

    .um-entry-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(360px, 1fr));
        justify-content: center;
        gap: 42px;
    }

    .um-entry-card {
        display: flex;
        flex-direction: column;
        text-decoration: none;
        position: relative;
        overflow: hidden;
        width: 100%;
        min-height: 430px;
        padding: 32px 30px 28px;
        border-radius: 18px;
        border: 1px solid rgba(112, 19, 27, 0.35);
        background:
            linear-gradient(135deg, rgba(255, 255, 255, .95), rgba(255, 252, 244, .86)),
            url('/images/PUPBG.jpg') center / cover no-repeat;
        box-shadow:
            0 18px 40px rgba(112, 19, 27, 0.08),
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
            0 24px 46px rgba(112, 19, 27, 0.16);
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
        width: 78px;
        height: 78px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        margin-bottom: 28px;
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
        width: 40px;
        height: 40px;
        display: block;
        stroke: currentColor;
    }

    .um-entry-chip {
        position: absolute;
        top: 32px;
        right: 30px;
        width: 46px;
        height: 46px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
        background: #70131B;
        color: #ffffff;
        border: 1px solid rgba(112, 19, 27, 0.2);
        box-shadow: 0 14px 26px rgba(112, 19, 27, 0.22);
        transition: background .2s ease, color .2s ease, border-color .2s ease;
    }

    .um-entry-chip svg {
        width: 20px;
        height: 20px;
        stroke: currentColor;
        stroke-width: 2.2;
        fill: none;
    }

    .um-entry-card h2 {
        margin: 0 0 18px;
        font-size: 1.62rem;
        font-weight: 900;
        color: #9f1239 !important;
        position: relative;
        z-index: 1;
        transition: color .2s ease;
    }

    .um-entry-card h2::after {
        content: "";
        display: block;
        width: 42px;
        height: 3px;
        margin-top: 16px;
        border-radius: 999px;
        background: #9f1239;
    }

    .um-entry-card p {
        margin: 0;
        max-width: 390px;
        color: #334155 !important;
        line-height: 1.65;
        font-size: 1rem;
        font-weight: 600;
        position: relative;
        z-index: 1;
        transition: color .2s ease;
    }

    .um-entry-card hr {
        width: 100%;
        margin: auto 0 24px;
        border: 0;
        border-top: 1px solid rgba(112, 19, 27, .12);
        position: relative;
        z-index: 1;
    }

    .um-entry-features {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin: 28px 0 0;
        padding: 0;
        list-style: none;
        position: relative;
        z-index: 1;
    }

    .um-entry-features li {
        display: flex;
        align-items: center;
        gap: 9px;
        min-width: 0;
        color: #334155;
        font-size: .78rem;
        font-weight: 700;
        line-height: 1.25;
    }

    .um-entry-features span {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        border-radius: 9px;
        color: #9f1239;
        background: rgba(112, 19, 27, .08);
    }

    .um-entry-features svg {
        width: 18px;
        height: 18px;
    }

    .um-entry-meta {
        min-height: 52px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin-top: 24px;
        padding: 0 18px;
        border-radius: 10px;
        background: #70131B;
        color: #ffffff !important;
        font-size: 0.9rem;
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
        width: 18px;
        height: 18px;
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
            padding: 24px 20px;
        }
        .um-entry-features {
            grid-template-columns: 1fr;
        }
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
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <rect x="16" y="11" width="6" height="8" rx="1.4"/>
                    <path d="M18 11V9.5a2 2 0 0 1 4 0V11"/>
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
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3l7 4v5c0 5-3.5 8-7 9-3.5-1-7-4-7-9V7l7-4z"/>
                    <path d="M9.5 12l1.7 1.7 3.3-3.4"/>
                </svg>
            </span>
            <h2>Admin Hub Profile</h2>
            <p>Manage clinic-only admin hub records, including admin login email, admin type, office, and the same shared API-backed profile fields.</p>
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
                Open Admin Hub Profile
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
            </div>
        </a>
    </div>
</div>
@endsection
