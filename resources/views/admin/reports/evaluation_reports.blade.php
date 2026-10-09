@extends('layouts.admin')

@section('title', 'Service Evaluation Reports')

@push('styles')
<style>
    .evaluation-report-shell {
        max-width: 1380px;
        margin: 0 auto;
        padding: 22px;
    }
    .evaluation-report-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 18px;
        margin-bottom: 24px;
    }
    .evaluation-report-title {
        margin: 0;
        font-size: 30px;
        font-weight: 900;
        color: #ffffff;
        letter-spacing: 0;
    }
    .evaluation-report-copy {
        margin: 8px 0 0;
        color: rgba(255,255,255,0.78);
        font-size: 14px;
        line-height: 1.6;
        max-width: 720px;
    }
    .evaluation-report-back {
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

    .evaluation-report-back::after {
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

    .evaluation-report-back:hover::after {
        left: 125%;
    }

    .evaluation-report-back:hover,
    .evaluation-report-back:focus {
        color: #70131B !important;
        border-color: #facc15;
        background: #facc15;
        box-shadow: 0 12px 28px rgba(112, 19, 27, 0.18);
        transform: translateY(-1px);
        outline: none;
    }
    .evaluation-stat-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 24px;
    }
    .evaluation-stat-card {
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
    .evaluation-stat-card::before {
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
    .evaluation-stat-card span {
        display: block;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #64748b;
    }
    .evaluation-stat-card strong {
        display: block;
        margin-top: 8px;
        font-size: 28px;
        line-height: 1.1;
        font-weight: 900;
        color: #111827;
    }
    .evaluation-layout {
        display: grid;
        grid-template-columns: 320px minmax(0, 1fr);
        gap: 22px;
        align-items: start;
    }
    .evaluation-panel {
        background: #ffffff;
        border-radius: 20px;
        padding: 22px;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
    }
    .evaluation-panel h3 {
        margin: 0 0 14px;
        font-size: 17px;
        font-weight: 900;
        color: #7f1d2d;
    }
    .evaluation-filter-form {
        display: grid;
        gap: 14px;
    }
    .evaluation-field label {
        display: block;
        margin-bottom: 7px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #475569;
    }
    .evaluation-field input,
    .evaluation-field select {
        width: 100%;
        height: 46px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 0 14px;
        font-size: 14px;
        color: #111827;
        background: #ffffff;
    }
    .evaluation-filter-actions {
        display: flex;
        gap: 10px;
    }
    .evaluation-btn {
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
    .evaluation-btn.primary {
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #ffffff;
        border-color: #8f2230;
        box-shadow:
            0 0 0 3px rgba(112, 19, 27, 0.12),
            0 10px 22px rgba(112, 19, 27, 0.20);
    }
    .evaluation-btn.secondary {
        background-color: #ffffff;
        color: #70131B;
        border-color: #8f2230;
        box-shadow:
            0 0 0 3px rgba(112, 19, 27, 0.12),
            0 10px 22px rgba(112, 19, 27, 0.20);
    }
    .evaluation-btn.primary::after,
    .evaluation-btn.secondary::after {
        content: "";
        position: absolute;
        inset: 0;
        transform: translateX(-135%);
        transition: transform 1.5s ease;
        z-index: -1;
    }
    .evaluation-btn.primary::after {
        background:
            linear-gradient(120deg,
                rgba(255, 248, 196, 0) 0%,
                rgba(255, 239, 181, 0.14) 22%,
                rgba(255, 239, 181, 0.52) 48%,
                rgba(255, 239, 181, 0.14) 72%,
                rgba(255, 248, 196, 0) 100%);
    }
    .evaluation-btn.secondary::after {
        background:
            linear-gradient(120deg,
                rgba(255, 248, 196, 0) 0%,
                rgba(255, 239, 181, 0.14) 22%,
                rgba(255, 239, 181, 0.52) 48%,
                rgba(255, 239, 181, 0.14) 72%,
                rgba(255, 248, 196, 0) 100%);
    }
    .evaluation-btn:hover {
        transform: translateY(-1px);
    }
    .evaluation-btn.primary:hover {
        border-color: #facc15;
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.18),
            0 14px 24px rgba(112, 19, 27, 0.16);
        color: #ffffff;
    }
    .evaluation-btn.secondary:hover {
        border-color: #facc15;
        background: #facc15;
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.18),
            0 14px 24px rgba(112, 19, 27, 0.16);
        color: #70131B;
    }
    .evaluation-btn:hover::after {
        transform: translateX(135%);
    }
    .evaluation-score-block {
        margin-top: 18px;
        padding: 18px;
        border-radius: 18px;
        background: linear-gradient(135deg, #7f1d2d, #56111e);
        color: #ffffff;
    }
    .evaluation-score-kicker {
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        opacity: 0.8;
    }
    .evaluation-score-number {
        margin-top: 8px;
        font-size: 42px;
        font-weight: 900;
        line-height: 1;
    }
    .evaluation-score-copy {
        margin-top: 8px;
        font-size: 13px;
        line-height: 1.6;
        color: rgba(255,255,255,0.82);
    }
    .evaluation-list {
        display: grid;
        gap: 16px;
    }
    .evaluation-card {
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 16px;
        align-items: start;
        background: #ffffff;
        border-radius: 20px;
        padding: 20px;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
    }
    .evaluation-avatar {
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
    .evaluation-card-head {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        margin-bottom: 8px;
    }
    .evaluation-card-name {
        font-size: 17px;
        font-weight: 900;
        color: #111827;
    }
    .evaluation-card-meta {
        font-size: 12px;
        color: #64748b;
        font-weight: 700;
    }
    .evaluation-chip-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 10px;
    }
    .evaluation-chip {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #334155;
        font-size: 11px;
        font-weight: 800;
    }
    .evaluation-message {
        margin: 0;
        font-size: 14px;
        line-height: 1.7;
        color: #111827;
        white-space: pre-line;
    }
    .evaluation-side {
        text-align: right;
        min-width: 110px;
    }
    .evaluation-rating {
        font-size: 26px;
        font-weight: 900;
        color: #7f1d2d;
        line-height: 1;
    }
    .evaluation-rating-sub {
        margin-top: 6px;
        font-size: 12px;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .evaluation-empty {
        background: #ffffff;
        border-radius: 20px;
        padding: 36px 24px;
        text-align: center;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
        color: #475569;
        font-weight: 700;
    }
    .evaluation-pagination {
        margin-top: 18px;
    }
    .evaluation-pagination svg {
        width: 16px;
        height: 16px;
    }
    html[data-theme="dark"] .evaluation-report-title {
        color: #f8fafc !important;
    }
    html[data-theme="dark"] .evaluation-report-copy {
        color: #cbd5e1 !important;
    }
    html[data-theme="dark"] .evaluation-stat-card,
    html[data-theme="dark"] .evaluation-panel,
    html[data-theme="dark"] .evaluation-card,
    html[data-theme="dark"] .evaluation-empty {
        background: #111827 !important;
        border: 1px solid rgba(250, 204, 21, .16) !important;
        color: #f8fafc !important;
        box-shadow: none !important;
    }
    html[data-theme="dark"] .evaluation-panel h3,
    html[data-theme="dark"] .evaluation-stat-card strong,
    html[data-theme="dark"] .evaluation-card-name,
    html[data-theme="dark"] .evaluation-message,
    html[data-theme="dark"] .evaluation-rating {
        color: #f8fafc !important;
    }
    html[data-theme="dark"] .evaluation-stat-card span,
    html[data-theme="dark"] .evaluation-card-meta,
    html[data-theme="dark"] .evaluation-rating-sub,
    html[data-theme="dark"] .evaluation-field label,
    html[data-theme="dark"] .evaluation-empty {
        color: #cbd5e1 !important;
    }
    html[data-theme="dark"] .evaluation-field input,
    html[data-theme="dark"] .evaluation-field select {
        background: #0f172a !important;
        border-color: rgba(250, 204, 21, .18) !important;
        color: #f8fafc !important;
        color-scheme: dark;
    }
    html[data-theme="dark"] .evaluation-field input::placeholder {
        color: #94a3b8 !important;
    }
    html[data-theme="dark"] .evaluation-chip {
        background: rgba(250, 204, 21, .12) !important;
        border: 1px solid rgba(250, 204, 21, .18);
        color: #fde68a !important;
    }
    html[data-theme="dark"] .evaluation-btn.secondary {
        background: #0f172a !important;
        border-color: rgba(250, 204, 21, .22) !important;
        color: #f8fafc !important;
    }
    html[data-theme="dark"] .evaluation-pagination nav,
    html[data-theme="dark"] .evaluation-pagination nav > div,
    html[data-theme="dark"] .evaluation-pagination p {
        color: #f8fafc !important;
    }
    html[data-theme="dark"] .evaluation-pagination a,
    html[data-theme="dark"] .evaluation-pagination span {
        background: #111827 !important;
        border-color: rgba(250, 204, 21, .16) !important;
        color: #f8fafc !important;
    }
    html[data-theme="dark"] .evaluation-pagination span[aria-current] span {
        background: #7f0010 !important;
        border-color: #facc15 !important;
        color: #ffffff !important;
    }
    @media (max-width: 1100px) {
        .evaluation-layout {
            grid-template-columns: 1fr;
        }
        .evaluation-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 720px) {
        .evaluation-stat-grid {
            grid-template-columns: 1fr;
        }
        .evaluation-card {
            grid-template-columns: 1fr;
        }
        .evaluation-side {
            text-align: left;
            min-width: 0;
        }
    }
    .evaluation-panel + .evaluation-panel {
        margin-top: 16px;
    }
    .evaluation-filter-form {
        display: grid;
        gap: 12px;
        margin-top: 20px;
        padding-top: 18px;
        border-top: 1px solid rgba(112, 19, 27, .12);
    }
    .evaluation-field label {
        display: block;
        margin-bottom: 6px;
        color: #475569;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
    }
    .evaluation-field input {
        width: 100%;
        min-height: 42px;
        padding: 0 10px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #fff;
        color: #111827;
    }
    .evaluation-stat-card strong small {
        margin-left: 5px;
        color: #64748b;
        font-size: 13px;
        font-weight: 800;
    }
    .evaluation-filter-actions {
        display: flex;
        gap: 8px;
    }
    .evaluation-btn {
        min-height: 40px;
        padding: 0 12px;
        border: 1px solid #8f2230;
        border-radius: 8px;
        background: #70131B;
        color: #fff;
        font-size: 12px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
    }
    .evaluation-btn.secondary {
        background: #fff;
        color: #70131B;
    }
    .evaluation-section-title {
        margin: 0 0 14px;
        color: #7f1d2d;
        font-size: 17px;
        font-weight: 900;
    }
    .evaluation-sqd-list {
        display: grid;
        gap: 10px;
    }
    .evaluation-sqd-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 4px 14px;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #e5e7eb;
    }
    .evaluation-sqd-row:last-child {
        border-bottom: 0;
    }
    .evaluation-sqd-label {
        color: #111827;
        font-size: 13px;
        font-weight: 800;
    }
    .evaluation-sqd-average {
        color: #70131B;
        font-size: 15px;
        font-weight: 900;
        white-space: nowrap;
    }
    .evaluation-sqd-meta {
        grid-column: 1 / -1;
        color: #64748b;
        font-size: 11px;
    }
    .evaluation-cc-list {
        display: grid;
        gap: 14px;
    }
    .evaluation-cc-item {
        padding-bottom: 12px;
        border-bottom: 1px solid rgba(112, 19, 27, .12);
    }
    .evaluation-cc-item:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }
    .evaluation-cc-question {
        margin: 0 0 8px;
        color: #111827;
        font-size: 12px;
        font-weight: 900;
        line-height: 1.45;
    }
    .evaluation-cc-choice {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        padding: 4px 0;
        color: #475569;
        font-size: 11px;
        line-height: 1.4;
    }
    .evaluation-response-heading {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: 12px;
        margin: 22px 0 12px;
    }
    .evaluation-response-heading h2 {
        margin: 0;
        color: #7f1d2d;
        font-size: 18px;
        font-weight: 900;
    }
    .evaluation-response-heading span {
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
    }
    .evaluation-response-card .evaluation-avatar {
        display: grid;
        place-items: center;
        background: #fff1f2;
        color: #70131B;
    }
    .evaluation-response-card .evaluation-avatar svg {
        width: 24px;
        height: 24px;
    }
    html[data-theme="dark"] .evaluation-field label,
    html[data-theme="dark"] .evaluation-sqd-meta,
    html[data-theme="dark"] .evaluation-cc-choice,
    html[data-theme="dark"] .evaluation-response-heading span {
        color: #cbd5e1 !important;
    }
    html[data-theme="dark"] .evaluation-field input {
        background: #0f172a !important;
        border-color: rgba(250, 204, 21, .18) !important;
        color: #f8fafc !important;
        color-scheme: dark;
    }
    html[data-theme="dark"] .evaluation-section-title,
    html[data-theme="dark"] .evaluation-sqd-label,
    html[data-theme="dark"] .evaluation-cc-question,
    html[data-theme="dark"] .evaluation-response-heading h2 {
        color: #f8fafc !important;
    }
    html[data-theme="dark"] .evaluation-sqd-row,
    html[data-theme="dark"] .evaluation-cc-item {
        border-color: rgba(250, 204, 21, .14) !important;
    }
    html[data-theme="dark"] .evaluation-btn.secondary {
        background: #0f172a;
        color: #f8fafc;
    }
    @media (max-width: 720px) {
        .evaluation-response-heading {
            align-items: flex-start;
            flex-direction: column;
        }
        .evaluation-sqd-row {
            grid-template-columns: minmax(0, 1fr) auto;
        }
    }
</style>
@endpush

@section('content')
@include('admin.reports.partials.evaluation-report-content')

@endsection
