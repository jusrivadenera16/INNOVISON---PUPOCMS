@extends('layouts.admin')

@section('title', 'Feedback Reports')

@push('styles')
<style>
    .feedback-report-shell {
        max-width: 1380px;
        margin: 0 auto;
        padding: 22px;
    }
    .feedback-report-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 18px;
        margin-bottom: 24px;
    }
    .feedback-report-title {
        margin: 0;
        font-size: 30px;
        font-weight: 900;
        color: #ffffff;
        letter-spacing: -0.03em;
    }
    .feedback-report-copy {
        margin: 8px 0 0;
        color: rgba(255,255,255,0.78);
        font-size: 14px;
        line-height: 1.6;
        max-width: 720px;
    }
    .feedback-report-back {
        min-width: 150px;
        width: auto !important;
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        position: relative;
        overflow: hidden;
        gap: 7px;
        min-height: 44px;
        padding: 0 18px;
        border: 1px solid #70131B;
        border-radius: 12px;
        background: #70131B;
        color: #ffffff !important;
        font-size: 13px;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
        box-shadow: 0 12px 22px rgba(112, 19, 27, 0.18);
        transition: color .08s ease, border-color .18s ease, background .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .feedback-report-back::after {
        content: "";
        position: absolute;
        top: -40%;
        left: -130%;
        width: 120%;
        height: 180%;
        background: linear-gradient(115deg, rgba(250, 204, 21, 0) 0%, rgba(250, 204, 21, 0.46) 45%, rgba(250, 204, 21, 0) 100%);
        transform: skewX(-20deg);
        transition: left 1.5s ease;
        pointer-events: none;
        z-index: 0;
    }

    .feedback-report-back:hover::after {
        left: 125%;
    }

    .feedback-report-back:hover,
    .feedback-report-back:focus {
        color: #70131B !important;
        border-color: #facc15;
        background: #facc15;
        box-shadow: 0 12px 28px rgba(112, 19, 27, 0.18);
        transform: translateY(-1px);
        outline: none;
    }
    .feedback-stat-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 24px;
    }
    .feedback-stat-card {
        position: relative;
        overflow: visible;
        background: #ffffff;
        border-radius: 12px;
        padding: 24px 22px 20px;
        box-shadow:
            0 4px 12px rgba(0,0,0,0.05),
            inset 0 1px 0 rgba(255,255,255,0.72);
        border: 1px solid rgba(112, 19, 27, 0.12);
    }
    .feedback-stat-card::before {
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
    .feedback-stat-card span {
        display: block;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #64748b;
    }
    .feedback-stat-card strong {
        display: block;
        margin-top: 8px;
        font-size: 28px;
        line-height: 1.1;
        font-weight: 900;
        color: #111827;
    }
    .feedback-layout {
        display: grid;
        grid-template-columns: 320px minmax(0, 1fr);
        gap: 22px;
        align-items: start;
    }
    .feedback-panel {
        background: #ffffff;
        border-radius: 20px;
        padding: 22px;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
    }
    .feedback-panel h3 {
        margin: 0 0 14px;
        font-size: 17px;
        font-weight: 900;
        color: #7f1d2d;
    }
    .feedback-filter-form {
        display: grid;
        gap: 14px;
    }
    .feedback-field label {
        display: block;
        margin-bottom: 7px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #475569;
    }
    .feedback-field input,
    .feedback-field select {
        width: 100%;
        height: 46px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 0 14px;
        font-size: 14px;
        color: #111827;
        background: #ffffff;
    }
    .feedback-filter-actions {
        display: flex;
        gap: 10px;
    }
    .feedback-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 44px;
        border-radius: 10px;
        padding: 0 18px;
        text-decoration: none;
        font-weight: 800;
        cursor: pointer;
        border: 1px solid #8f2230;
        position: relative;
        overflow: hidden;
        transition: color .08s linear, transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        z-index: 0;
    }
    .feedback-btn.primary {
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #ffffff;
        border-color: #8f2230;
        box-shadow:
            0 0 0 3px rgba(112, 19, 27, 0.12),
            0 10px 22px rgba(112, 19, 27, 0.20);
    }
    .feedback-btn.secondary {
        background-color: #ffffff;
        color: #70131B;
        border-color: #8f2230;
        box-shadow:
            0 0 0 3px rgba(112, 19, 27, 0.12),
            0 10px 22px rgba(112, 19, 27, 0.20);
    }
    .feedback-btn.primary::after,
    .feedback-btn.secondary::after {
        content: "";
        position: absolute;
        inset: 0;
        transform: translateX(-135%);
        transition: transform 1.5s ease;
        z-index: -1;
    }
    .feedback-btn.primary::after {
        background:
            linear-gradient(120deg,
                rgba(255, 248, 196, 0) 0%,
                rgba(255, 239, 181, 0.14) 22%,
                rgba(255, 239, 181, 0.52) 48%,
                rgba(255, 239, 181, 0.14) 72%,
                rgba(255, 248, 196, 0) 100%);
    }
    .feedback-btn.secondary::after {
        background:
            linear-gradient(120deg,
                rgba(255, 248, 196, 0) 0%,
                rgba(255, 239, 181, 0.14) 22%,
                rgba(255, 239, 181, 0.52) 48%,
                rgba(255, 239, 181, 0.14) 72%,
                rgba(255, 248, 196, 0) 100%);
    }
    .feedback-btn:hover {
        transform: translateY(-1px);
    }
    .feedback-btn.primary:hover {
        border-color: #facc15;
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.18),
            0 14px 24px rgba(112, 19, 27, 0.16);
        color: #ffffff;
    }
    .feedback-btn.secondary:hover {
        border-color: #facc15;
        background: #facc15;
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.18),
            0 14px 24px rgba(112, 19, 27, 0.16);
        color: #70131B;
    }
    .feedback-btn:hover::after {
        transform: translateX(135%);
    }
    .clinic-score-block {
        margin-top: 18px;
        padding: 18px;
        border-radius: 18px;
        background: linear-gradient(135deg, #7f1d2d, #56111e);
        color: #ffffff;
    }
    .clinic-score-kicker {
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        opacity: 0.8;
    }
    .clinic-score-number {
        margin-top: 8px;
        font-size: 42px;
        font-weight: 900;
        line-height: 1;
    }
    .clinic-score-copy {
        margin-top: 8px;
        font-size: 13px;
        line-height: 1.6;
        color: rgba(255,255,255,0.82);
    }
    .feedback-list {
        display: grid;
        gap: 16px;
    }
    .feedback-card {
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 16px;
        align-items: start;
        background: #ffffff;
        border-radius: 20px;
        padding: 20px;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
    }
    .feedback-avatar {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        background: linear-gradient(135deg, #7f1d2d, #56111e);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        font-weight: 900;
        letter-spacing: 0.04em;
        flex-shrink: 0;
    }
    .feedback-card-head {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        margin-bottom: 8px;
    }
    .feedback-card-name {
        font-size: 17px;
        font-weight: 900;
        color: #111827;
    }
    .feedback-card-meta {
        font-size: 12px;
        color: #64748b;
        font-weight: 700;
    }
    .feedback-chip-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 10px;
    }
    .feedback-chip {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #334155;
        font-size: 11px;
        font-weight: 800;
    }
    .feedback-message {
        margin: 0;
        font-size: 14px;
        line-height: 1.7;
        color: #111827;
        white-space: pre-line;
    }
    .feedback-side {
        text-align: right;
        min-width: 110px;
    }
    .feedback-rating {
        font-size: 26px;
        font-weight: 900;
        color: #7f1d2d;
        line-height: 1;
    }
    .feedback-rating-sub {
        margin-top: 6px;
        font-size: 12px;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .feedback-empty {
        background: #ffffff;
        border-radius: 20px;
        padding: 36px 24px;
        text-align: center;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
        color: #475569;
        font-weight: 700;
    }
    .feedback-pagination {
        margin-top: 18px;
    }
    .feedback-pagination svg {
        width: 16px;
        height: 16px;
    }
    html[data-theme="dark"] .feedback-report-title {
        color: #f8fafc !important;
    }
    html[data-theme="dark"] .feedback-report-copy {
        color: #cbd5e1 !important;
    }
    html[data-theme="dark"] .feedback-stat-card,
    html[data-theme="dark"] .feedback-panel,
    html[data-theme="dark"] .feedback-card,
    html[data-theme="dark"] .feedback-empty {
        background: #111827 !important;
        border: 1px solid rgba(250, 204, 21, .16) !important;
        color: #f8fafc !important;
        box-shadow: none !important;
    }
    html[data-theme="dark"] .feedback-panel h3,
    html[data-theme="dark"] .feedback-stat-card strong,
    html[data-theme="dark"] .feedback-card-name,
    html[data-theme="dark"] .feedback-message,
    html[data-theme="dark"] .feedback-rating {
        color: #f8fafc !important;
    }
    html[data-theme="dark"] .feedback-stat-card span,
    html[data-theme="dark"] .feedback-card-meta,
    html[data-theme="dark"] .feedback-rating-sub,
    html[data-theme="dark"] .feedback-field label,
    html[data-theme="dark"] .feedback-empty {
        color: #cbd5e1 !important;
    }
    html[data-theme="dark"] .feedback-field input,
    html[data-theme="dark"] .feedback-field select {
        background: #0f172a !important;
        border-color: rgba(250, 204, 21, .18) !important;
        color: #f8fafc !important;
        color-scheme: dark;
    }
    html[data-theme="dark"] .feedback-field input::placeholder {
        color: #94a3b8 !important;
    }
    html[data-theme="dark"] .feedback-chip {
        background: rgba(250, 204, 21, .12) !important;
        border: 1px solid rgba(250, 204, 21, .18);
        color: #fde68a !important;
    }
    html[data-theme="dark"] .feedback-btn.secondary {
        background: #0f172a !important;
        border-color: rgba(250, 204, 21, .22) !important;
        color: #f8fafc !important;
    }
    html[data-theme="dark"] .feedback-pagination nav,
    html[data-theme="dark"] .feedback-pagination nav > div,
    html[data-theme="dark"] .feedback-pagination p {
        color: #f8fafc !important;
    }
    html[data-theme="dark"] .feedback-pagination a,
    html[data-theme="dark"] .feedback-pagination span {
        background: #111827 !important;
        border-color: rgba(250, 204, 21, .16) !important;
        color: #f8fafc !important;
    }
    html[data-theme="dark"] .feedback-pagination span[aria-current] span {
        background: #7f0010 !important;
        border-color: #facc15 !important;
        color: #ffffff !important;
    }
    @media (max-width: 1100px) {
        .feedback-layout {
            grid-template-columns: 1fr;
        }
        .feedback-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 720px) {
        .feedback-stat-grid {
            grid-template-columns: 1fr;
        }
        .feedback-card {
            grid-template-columns: 1fr;
        }
        .feedback-side {
            text-align: left;
            min-width: 0;
        }
    }
</style>
@endpush

@section('content')
@php
    $role = \App\Models\User::normalizeRole(optional(auth()->user())->user_role ?? '');
    $reportsHomeUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports') : url('/admin/reports');
    $feedbackUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports/feedbacks') : url('/admin/reports/feedbacks');
@endphp

<div class="feedback-report-shell">
    <div class="feedback-report-head">
        <div>
            <h1 class="feedback-report-title">Feedback Reports</h1>
            <p class="feedback-report-copy">Review all submitted clinic feedback, monitor the overall patient experience, and keep a simple clinic score out of 10 based on the ratings students and staff gave after consultations.</p>
        </div>
        <a href="{{ $reportsHomeUrl }}" class="feedback-report-back">&larr; Back to Reports</a>
    </div>

    <div class="feedback-stat-grid">
        <div class="feedback-stat-card">
            <span>Total Feedbacks</span>
            <strong>{{ $totalFeedbacks }}</strong>
        </div>
        <div class="feedback-stat-card">
            <span>Rate of Clinic</span>
            <strong>{{ number_format($clinicScore, 1) }}/10</strong>
        </div>
        <div class="feedback-stat-card">
            <span>Recommended</span>
            <strong>{{ $recommendedCount }}</strong>
        </div>
    </div>

    <div class="feedback-layout">
        <aside class="feedback-panel">
            <h3>Filter Feedbacks</h3>
            <form method="GET" action="{{ $feedbackUrl }}" class="feedback-filter-form">
                <div class="feedback-field">
                    <label for="feedbackSearch">Search</label>
                    <input id="feedbackSearch" type="text" name="q" value="{{ $search }}" placeholder="Name, service, comment">
                </div>
                <div class="feedback-field">
                    <label for="feedbackMonth">Month</label>
                    <input id="feedbackMonth" type="month" name="month" value="{{ $monthFilter }}">
                </div>
                <div class="feedback-filter-actions">
                    <button type="submit" class="feedback-btn primary">Apply</button>
                    <a href="{{ $feedbackUrl }}" class="feedback-btn secondary">Reset</a>
                </div>
            </form>

            <div class="clinic-score-block">
                <div class="clinic-score-kicker">Clinic Score</div>
                <div class="clinic-score-number">{{ number_format($clinicScore, 1) }}/10</div>
                <div class="clinic-score-copy">
                    rack patient satisfaction to identify and resolve operational bottlenecks.
                </div>
            </div>
        </aside>

        <section>
            @if($feedbackItems->count() > 0)
                <div class="feedback-list">
                    @foreach($feedbackItems as $feedback)
                        <article class="feedback-card">
                            <div class="feedback-avatar">{{ $feedback->initials }}</div>
                            <div>
                                <div class="feedback-card-head">
                                    <div class="feedback-card-name">{{ $feedback->name }}</div>
                                    <div class="feedback-card-meta">· {{ $feedback->role }} · {{ $feedback->time_ago }}</div>
                                </div>
                                <div class="feedback-chip-row">
                                    <span class="feedback-chip">{{ $feedback->service }}</span>
                                    <span class="feedback-chip">{{ ucfirst($feedback->appointment_type ?: 'consultation') }}</span>
                                </div>
                                <p class="feedback-message">{{ $feedback->message !== '' ? $feedback->message : 'No written comments were added.' }}</p>
                            </div>
                            <div class="feedback-side">
                                <div class="feedback-rating">{{ $feedback->score_out_of_ten }}/10</div>
                                <div class="feedback-rating-sub">{{ $feedback->rating }}/5 Rating</div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="feedback-pagination">
                    {{ $feedbackItems->links() }}
                </div>
            @else
                <div class="feedback-empty">No feedback submissions were found for the current filters.</div>
            @endif
        </section>
    </div>
</div>
@endsection
