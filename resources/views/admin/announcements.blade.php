@extends('layouts.admin')

@section('title', 'Announcement')

@push('styles')
<style>
    .announcement-page {
        width: min(1120px, 100%);
        margin: 0 auto;
        min-height: calc(100vh - 190px);
        display: flex;
        align-items: stretch;
    }

    .announcement-panel {
        position: relative;
        width: 100%;
        min-height: 560px;
        display: grid;
        place-items: center;
        overflow: hidden;
        border-radius: 22px;
        border: 1px solid rgba(112, 19, 27, 0.13);
        background:
            radial-gradient(circle at 50% 34%, rgba(112, 19, 27, 0.055), transparent 26%),
            linear-gradient(180deg, #ffffff 0%, #fffefe 100%);
        box-shadow: 0 22px 60px rgba(15, 23, 42, 0.06);
        padding: clamp(28px, 5vw, 70px);
    }

    .announcement-panel::before {
        content: "";
        position: absolute;
        inset: 20px;
        border-radius: 18px;
        border: 1px solid rgba(112, 19, 27, 0.035);
        pointer-events: none;
    }

    .announcement-empty {
        position: relative;
        z-index: 1;
        width: min(520px, 100%);
        display: grid;
        justify-items: center;
        text-align: center;
    }

    .announcement-graphic {
        position: relative;
        width: 230px;
        height: 185px;
        margin-bottom: 12px;
    }

    .announcement-graphic-glow {
        position: absolute;
        left: 50%;
        top: 22px;
        width: 150px;
        height: 150px;
        transform: translateX(-50%);
        border-radius: 50%;
        background:
            linear-gradient(135deg, rgba(112, 19, 27, 0.07), rgba(250, 204, 21, 0.14)),
            #fbf1f2;
    }

    .announcement-graphic-glow::before,
    .announcement-graphic-glow::after {
        content: "";
        position: absolute;
        border-radius: 999px;
        background: rgba(112, 19, 27, 0.12);
    }

    .announcement-graphic-glow::before {
        width: 74px;
        height: 8px;
        left: 37px;
        bottom: 28px;
        transform: rotate(-3deg);
    }

    .announcement-graphic-glow::after {
        width: 110px;
        height: 3px;
        left: 20px;
        bottom: 13px;
        background: rgba(112, 19, 27, 0.16);
    }

    .announcement-megaphone {
        position: absolute;
        left: 42px;
        top: 58px;
        width: 126px;
        height: 80px;
        transform: rotate(-16deg);
    }

    .announcement-megaphone-body {
        position: absolute;
        left: 44px;
        top: 14px;
        width: 72px;
        height: 52px;
        border-radius: 10px 46px 46px 10px;
        background: linear-gradient(135deg, #b54352, #7f1825);
        box-shadow: inset -12px 0 0 rgba(255, 255, 255, 0.16);
    }

    .announcement-megaphone-body::before {
        content: "";
        position: absolute;
        left: -18px;
        top: 10px;
        width: 28px;
        height: 32px;
        border-radius: 8px;
        background: #fff3f4;
        border: 3px solid #8f1827;
    }

    .announcement-megaphone-body::after {
        content: "";
        position: absolute;
        right: -10px;
        top: 8px;
        width: 14px;
        height: 36px;
        border-radius: 999px;
        background: #fff4f4;
        border: 4px solid #7f1825;
    }

    .announcement-megaphone-handle {
        position: absolute;
        left: 44px;
        top: 58px;
        width: 24px;
        height: 44px;
        border-radius: 8px;
        background: linear-gradient(180deg, #8f1827, #65101a);
        transform: rotate(-5deg);
        transform-origin: top center;
    }

    .announcement-megaphone-tail {
        position: absolute;
        left: 15px;
        top: 28px;
        width: 36px;
        height: 28px;
        border-radius: 10px;
        background: #7f1825;
    }

    .announcement-bubble {
        position: absolute;
        right: 26px;
        top: 22px;
        width: 82px;
        height: 54px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border-radius: 999px;
        background: linear-gradient(135deg, #9e2232, #70131B);
        box-shadow: 0 16px 30px rgba(112, 19, 27, 0.2);
    }

    .announcement-bubble::after {
        content: "";
        position: absolute;
        left: 16px;
        bottom: -9px;
        border-width: 12px 9px 0 0;
        border-style: solid;
        border-color: #70131B transparent transparent transparent;
    }

    .announcement-dot {
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #fff7f7;
    }

    .announcement-spark {
        position: absolute;
        width: 7px;
        height: 7px;
        color: #c14a5a;
    }

    .announcement-spark::before,
    .announcement-spark::after {
        content: "";
        position: absolute;
        inset: 0;
        margin: auto;
        background: currentColor;
        border-radius: 999px;
    }

    .announcement-spark::before {
        width: 7px;
        height: 2px;
    }

    .announcement-spark::after {
        width: 2px;
        height: 7px;
    }

    .announcement-spark.is-one { left: 18px; top: 70px; }
    .announcement-spark.is-two { right: 12px; top: 94px; color: #d7838f; }
    .announcement-spark.is-three { left: 46px; top: 28px; color: #d7838f; }

    .announcement-icon-badge {
        width: 50px;
        height: 50px;
        display: grid;
        place-items: center;
        margin-bottom: 18px;
        border-radius: 999px;
        background: #fff0f1;
        color: #8f1827;
        border: 1px solid rgba(143, 24, 39, 0.08);
        box-shadow: 0 10px 26px rgba(112, 19, 27, 0.08);
    }

    .announcement-icon-badge svg {
        width: 24px;
        height: 24px;
    }

    .announcement-title {
        margin: 0 0 22px;
        color: #0f172a;
        font-size: clamp(28px, 3vw, 38px);
        line-height: 1.1;
        font-weight: 950;
        letter-spacing: 0;
    }

    .announcement-copy {
        margin: 0 0 30px;
        color: #475569;
        font-size: 16px;
        font-weight: 750;
        line-height: 1.55;
    }

    .announcement-action {
        min-height: 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        border-radius: 10px;
        border: 1px solid rgba(112, 19, 27, 0.55);
        background: #ffffff;
        color: #70131B;
        padding: 0 28px;
        font-size: 14px;
        font-weight: 950;
        text-decoration: none;
        box-shadow: 0 12px 24px rgba(112, 19, 27, 0.06);
    }

    .announcement-action svg {
        width: 18px;
        height: 18px;
    }

    .announcement-action:hover,
    .announcement-action:focus-visible {
        background: #70131B;
        color: #ffffff;
        outline: none;
    }

    @media (max-width: 720px) {
        .announcement-page {
            min-height: auto;
        }

        .announcement-panel {
            min-height: 500px;
            padding: 28px 18px;
        }

        .announcement-graphic {
            transform: scale(0.88);
            margin-bottom: -4px;
        }
    }

    html[data-theme="dark"] .announcement-panel {
        background:
            radial-gradient(circle at 50% 34%, rgba(250, 204, 21, 0.08), transparent 26%),
            rgba(15, 23, 42, 0.94);
        border-color: rgba(250, 204, 21, 0.16);
    }

    html[data-theme="dark"] .announcement-title {
        color: #ffffff;
    }

    html[data-theme="dark"] .announcement-copy {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .announcement-icon-badge {
        background: rgba(250, 204, 21, 0.12);
        color: #facc15;
        border-color: rgba(250, 204, 21, 0.2);
    }

    html[data-theme="dark"] .announcement-action {
        background: rgba(17, 24, 39, 0.95);
        border-color: rgba(250, 204, 21, 0.5);
        color: #facc15;
    }

    html[data-theme="dark"] .announcement-action:hover,
    html[data-theme="dark"] .announcement-action:focus-visible {
        background: #facc15;
        color: #70131B;
    }
</style>
@endpush

@section('content')
<div class="announcement-page">
    <section class="announcement-panel">
        <div class="announcement-empty">
            <div class="announcement-graphic" aria-hidden="true">
                <div class="announcement-graphic-glow"></div>
                <div class="announcement-megaphone">
                    <div class="announcement-megaphone-tail"></div>
                    <div class="announcement-megaphone-body"></div>
                    <div class="announcement-megaphone-handle"></div>
                </div>
                <div class="announcement-bubble">
                    <span class="announcement-dot"></span>
                    <span class="announcement-dot"></span>
                    <span class="announcement-dot"></span>
                </div>
                <span class="announcement-spark is-one"></span>
                <span class="announcement-spark is-two"></span>
                <span class="announcement-spark is-three"></span>
            </div>
            <div class="announcement-icon-badge">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46" />
                </svg>
            </div>
            <h2 class="announcement-title">Announcement Center</h2>
            <p class="announcement-copy">
                We're working on this feature.<br>
                Check back soon for clinic announcements,<br>
                health advisories, and important system notices.
            </p>
            <a href="{{ route('admin.dashboard') }}" class="announcement-action">
                <x-outline-icon name="calendar-days" />
                <span>Check Back Soon</span>
            </a>
        </div>
    </section>
</div>
@endsection
