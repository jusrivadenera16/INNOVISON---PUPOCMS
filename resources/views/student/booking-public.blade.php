@extends('layouts.student')

@section('title', 'Appointments')

@push('styles')
<style>
    .public-appt-shell {
        max-width: 1120px;
        margin: 0 auto;
        padding: 54px 20px 72px;
    }

    .public-appt-header {
        margin-bottom: 26px;
    }

    .public-appt-kicker {
        margin: 0 0 8px;
        color: #8b0000;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0.14em;
        text-transform: uppercase;
    }

    .public-appt-title {
        margin: 0;
        color: #1f2937;
        font-size: 34px;
        line-height: 1.12;
        font-weight: 900;
        letter-spacing: 0;
    }

    .public-appt-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(300px, 0.9fr);
        gap: 20px;
        align-items: stretch;
    }

    .public-appt-card {
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        background: #ffffff;
        padding: 26px;
        box-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
    }

    .public-appt-card.is-login {
        border-color: rgba(139, 0, 0, 0.18);
        background: linear-gradient(180deg, #fffdf5, #ffffff);
    }

    .public-appt-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        margin-bottom: 18px;
        border-radius: 14px;
        color: #8b0000;
        background: #fff4bf;
    }

    .public-appt-icon svg {
        width: 24px;
        height: 24px;
        stroke-width: 2.2;
    }

    .public-appt-card h2 {
        margin: 0 0 10px;
        color: #20343a;
        font-size: 22px;
        line-height: 1.2;
        font-weight: 900;
    }

    .public-appt-card p {
        margin: 0;
        color: #64748b;
        font-size: 15px;
        line-height: 1.75;
    }

    .public-appt-list {
        display: grid;
        gap: 10px;
        margin: 20px 0 0;
        padding: 0;
        list-style: none;
    }

    .public-appt-list li {
        display: flex;
        gap: 10px;
        color: #334155;
        font-size: 14px;
        line-height: 1.5;
    }

    .public-appt-list li::before {
        content: "";
        width: 8px;
        height: 8px;
        margin-top: 7px;
        border-radius: 999px;
        background: #8b0000;
        flex: 0 0 auto;
    }

    .public-appt-login-btn {
        position: relative;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        min-height: 52px;
        margin-top: 22px;
        padding: 0 22px;
        border-radius: 999px;
        border: 1px solid #8b0000;
        background: #8b0000;
        color: #ffffff;
        font-size: 15px;
        font-weight: 900;
        text-decoration: none;
        box-shadow: 0 12px 26px rgba(139, 0, 0, 0.20);
        transition: transform .18s ease, background .18s ease, color .18s ease, border-color .18s ease, box-shadow .18s ease;
    }

    .public-appt-login-btn:hover,
    .public-appt-login-btn:focus-visible {
        transform: translateY(-1px);
        background: #facc15;
        border-color: #facc15;
        color: #8b0000;
        box-shadow: 0 16px 32px rgba(139, 0, 0, 0.18);
        outline: none;
    }

    .public-appt-login-btn svg {
        width: 18px;
        height: 18px;
        stroke-width: 2.2;
    }

    html[data-theme="dark"] .public-appt-kicker {
        color: #facc15;
    }

    html[data-theme="dark"] .public-appt-title {
        color: #ffffff;
    }

    html[data-theme="dark"] .public-appt-card,
    html[data-theme="dark"] .public-appt-card.is-login {
        border-color: rgba(250, 204, 21, 0.22);
        background: #111827;
        box-shadow: 0 18px 42px rgba(0, 0, 0, 0.34);
    }

    html[data-theme="dark"] .public-appt-icon {
        color: #facc15;
        background: rgba(250, 204, 21, 0.12);
    }

    html[data-theme="dark"] .public-appt-card h2 {
        color: #ffffff;
    }

    html[data-theme="dark"] .public-appt-card p,
    html[data-theme="dark"] .public-appt-list li {
        color: #f8fafc;
    }

    html[data-theme="dark"] .public-appt-list li::before {
        background: #facc15;
    }

    @media (max-width: 820px) {
        .public-appt-shell {
            padding: 34px 16px 52px;
        }

        .public-appt-title {
            font-size: 28px;
        }

        .public-appt-grid {
            grid-template-columns: 1fr;
        }

        .public-appt-card {
            padding: 22px;
        }
    }
</style>
@endpush

@section('content')
    <main class="public-appt-shell">
        <header class="public-appt-header">
            <p class="public-appt-kicker">Clinic Appointments</p>
            <h1 class="public-appt-title">Book clinic services through One Portal</h1>
        </header>

        <section class="public-appt-grid" aria-label="Public appointment access">
            <article class="public-appt-card">
                <span class="public-appt-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M7 3v3M17 3v3M4 9h16M6 5h12a2 2 0 0 1 2 2v14H4V7a2 2 0 0 1 2-2z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8 14h3M8 18h6" stroke="currentColor" stroke-linecap="round"/>
                    </svg>
                </span>
                <h2>Appointment services</h2>
                <p>
                    The clinic appointment workspace allows you to request services and monitor your pending or approved visits.
                </p>
                <ul class="public-appt-list">
                    <li>Choose the clinic service you need.</li>
                    <li>Select available date and provide the required details.</li>
                    <li>Track your request from your student account after submission.</li>
                </ul>
            </article>

            <article class="public-appt-card is-login">
                <span class="public-appt-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M12 3l7 4v5c0 4.5-2.9 7.4-7 9-4.1-1.6-7-4.5-7-9V7l7-4z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9 12l2 2 4-5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <h2>Login required before booking</h2>
                <p>
                    Please login your account through One Portal to access the appointment booking features and manage your clinic visits.
                <a href="{{ route('login.portal') }}" class="public-appt-login-btn">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M10 17l5-5-5-5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M15 12H4" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M20 4v16" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Log In via One Portal</span>
                </a>
            </article>
        </section>
    </main>
@endsection
