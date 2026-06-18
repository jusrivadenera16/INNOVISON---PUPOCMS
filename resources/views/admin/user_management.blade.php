@extends('layouts.admin')

@section('title', 'User Management')

@push('styles')
<style>
    .user-management-shell {
        max-width: 1180px;
        margin: 0 auto;
        padding: 20px 24px 40px;
        color: #0f172a;
    }

    .um-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 22px;
        padding: 16px 18px;
        border-radius: 0 0 20px 20px;
        border-bottom: 2px solid rgba(234, 215, 160, 0.9);
        background: linear-gradient(135deg, rgba(255, 253, 246, 0.76) 0%, rgba(255, 249, 231, 0.58) 42%, rgba(255, 255, 255, 0.82) 100%);
        box-shadow: 0 14px 26px rgba(112, 19, 27, 0.05);
    }

    .um-hero h1 {
        margin: 0;
        font-size: 1.55rem;
        font-weight: 800;
        color: #111827;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-bottom: 2px solid rgba(112, 19, 27, 0.72);
    }

    .um-hero h1 svg {
        width: 18px;
        height: 18px;
        flex: 0 0 auto;
    }

    .um-hero p {
        margin: 6px 0 0;
        color: #475569;
    }

    .um-entry-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(260px, 380px));
        justify-content: center;
        gap: 18px;
    }

    .um-entry-card {
        display: flex;
        flex-direction: column;
        text-decoration: none;
        position: relative;
        overflow: hidden;
        width: min(380px, 100%);
        min-height: 220px;
        padding: 22px 20px;
        border-radius: 16px;
        border: 1px solid rgba(112, 19, 27, 0.46);
        background: linear-gradient(135deg, #70131B, #8f2230);
        box-shadow:
            inset 0 -3px 0 rgba(250, 204, 21, 0.72),
            0 10px 24px rgba(112, 19, 27, 0.18);
        color: #ffffff;
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
        background: linear-gradient(180deg, rgba(255,255,255,0.08) 0%, rgba(255,255,255,0) 38%);
        pointer-events: none;
    }

    .um-entry-card::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(120deg, rgba(255, 248, 196, 0) 0%, rgba(250, 204, 21, 0.42) 48%, rgba(255, 248, 196, 0) 100%);
        transform: translateX(-130%);
        transition: transform .95s ease;
        pointer-events: none;
        z-index: 0;
    }

    .um-entry-card:hover {
        background: #facc15;
        color: #70131B;
        transform: translateY(-8px);
        border-color: #facc15;
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.12),
            0 20px 30px rgba(139, 0, 0, 0.22);
    }

    .um-entry-card:hover::after {
        transform: translateX(130%);
    }

    .um-entry-icon {
        width: 58px;
        height: 58px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
        margin-bottom: 14px;
        color: #ffffff;
        background: rgba(255, 248, 196, 0.12);
        border: 1px solid rgba(255, 248, 196, 0.16);
        position: relative;
        z-index: 1;
        animation: umEntryFloat 3.8s ease-in-out infinite;
        transition: background .22s ease, color .22s ease, border-color .22s ease, transform .22s ease;
    }

    .um-entry-icon::after {
        content: "";
        position: absolute;
        left: 10%;
        right: 10%;
        bottom: -10px;
        height: 14px;
        border-radius: 999px;
        filter: blur(8px);
        opacity: .6;
        z-index: -1;
        pointer-events: none;
        background: radial-gradient(circle, rgba(0, 0, 0, 0.44) 0%, rgba(0, 0, 0, 0.22) 48%, transparent 86%);
    }

    .um-entry-icon svg {
        width: 24px;
        height: 24px;
        display: block;
        stroke: currentColor;
    }

    .um-entry-chip {
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
        background: rgba(255, 248, 196, 0.12);
        color: #ffffff;
        border: 1px solid rgba(255, 248, 196, 0.72);
        box-shadow: 0 8px 14px rgba(15, 23, 42, 0.14);
        transition: background .2s ease, color .2s ease, border-color .2s ease;
    }

    .um-entry-chip svg {
        width: 14px;
        height: 14px;
        stroke: currentColor;
        stroke-width: 2.2;
        fill: none;
    }

    .um-entry-card h2 {
        margin: 0 0 8px;
        font-size: 18px;
        font-weight: 800;
        color: #ffffff !important;
        position: relative;
        z-index: 1;
        transition: color .2s ease;
    }

    .um-entry-card p {
        margin: 0;
        color: #ffffff !important;
        line-height: 1.55;
        font-size: 0.9rem;
        position: relative;
        z-index: 1;
        transition: color .2s ease;
    }

    .um-entry-meta {
        margin-top: auto;
        padding-top: 18px;
        font-size: 0.82rem;
        font-weight: 900;
        color: #ffffff !important;
        position: relative;
        z-index: 1;
        transition: color .2s ease;
    }

    .um-entry-card:hover .um-entry-icon,
    .um-entry-card:hover .um-entry-chip {
        background: #70131B;
        color: #ffffff;
        border-color: rgba(112, 19, 27, 0.62);
    }

    .um-entry-card:hover h2,
    .um-entry-card:hover p,
    .um-entry-card:hover .um-entry-meta {
        color: #70131B;
    }

    html[data-theme="dark"] .user-management-shell {
        color: #e5eefb;
    }

    html[data-theme="dark"] .um-hero {
        background: linear-gradient(135deg, rgba(18, 24, 38, 0.96), rgba(10, 15, 28, 0.92));
        border-bottom-color: rgba(240, 209, 90, 0.82);
        box-shadow: 0 18px 34px rgba(0, 0, 0, 0.28);
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
        background: #70131B;
        border-color: rgba(250, 204, 21, 0.62);
        box-shadow:
            inset 0 -3px 0 rgba(250, 204, 21, 0.92),
            0 14px 26px rgba(0, 0, 0, 0.22);
    }

    html[data-theme="dark"] .um-entry-card::before {
        background: none;
    }

    html[data-theme="dark"] .um-entry-card::after {
        background: linear-gradient(180deg, #8f2230 0%, #70131B 100%);
    }

    html[data-theme="dark"] .um-entry-card:hover {
        background: linear-gradient(135deg, #facc15, #fde68a);
        border-color: #facc15;
    }

    html[data-theme="dark"] .um-entry-card h2,
    html[data-theme="dark"] .um-entry-card p,
    html[data-theme="dark"] .um-entry-meta {
        color: #ffffff;
    }

    html[data-theme="dark"] .um-entry-card:hover h2,
    html[data-theme="dark"] .um-entry-card:hover p,
    html[data-theme="dark"] .um-entry-card:hover .um-entry-meta {
        color: #70131B;
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
    }
</style>
@endpush

@section('content')
<div class="user-management-shell">
    <div class="um-hero">
        <div>
            <h1><x-outline-icon name="users" />Users Management</h1>
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
                    <path d="M19 8v6"/>
                    <path d="M22 11h-6"/>
                </svg>
            </span>
            <h2>Account Access</h2>
            <p>Manage clinic login role, student-side email, and active or inactive access for users already inside the clinic system.</p>
            <div class="um-entry-meta">Open Account Access</div>
        </a>

        <a href="{{ route('admin.user-management.admin-hub') }}" class="um-entry-card">
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
            <div class="um-entry-meta">Open Admin Hub Profile</div>
        </a>
    </div>
</div>
@endsection
