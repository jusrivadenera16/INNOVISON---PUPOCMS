@extends('layouts.student')

@section('title', 'FAQ')

@push('styles')
<style>
    :root {
        --faq-maroon: #940515;
        --faq-maroon-dark: #65000d;
        --faq-ink: #242735;
        --faq-muted: #697182;
        --faq-line: #eceef3;
        --faq-soft: #fff5f5;
        --faq-shell: #fffafa;
        --faq-gold: #f5b642;
    }

    .faq-page {
        position: relative;
        isolation: isolate;
        min-height: calc(100vh - 120px);
        background:
            linear-gradient(180deg, rgba(255, 250, 250, 0.70), rgba(255, 255, 255, 0.58) 42%, rgba(245, 248, 247, 0.72) 100%),
            url('{{ asset('images/student-bg.png') }}') center top / cover no-repeat;
        color: var(--faq-ink);
        overflow: visible;
    }

    .faq-page::before {
        content: none;
    }

    .faq-hero {
        position: relative;
        padding: 46px 20px 18px;
        text-align: center;
        isolation: isolate;
    }

    .faq-hero::before,
    .faq-hero::after {
        content: none;
    }

    .faq-hero::before {
        left: -80px;
        bottom: 4px;
        width: 420px;
        height: 150px;
        border-top: 1px solid rgba(148, 5, 21, 0.14);
        border-radius: 50%;
        transform: rotate(8deg);
        box-shadow:
            0 -14px 0 -13px rgba(148, 5, 21, 0.16),
            0 -28px 0 -27px rgba(148, 5, 21, 0.13),
            0 -42px 0 -41px rgba(148, 5, 21, 0.1);
    }

    .faq-hero::after {
        right: 50px;
        top: 34px;
        width: 210px;
        height: 116px;
        border-right: 5px solid rgba(148, 5, 21, 0.07);
        border-top: 5px solid rgba(148, 5, 21, 0.05);
        border-radius: 28px 0 0 0;
    }

    .faq-hero-art {
        display: none;
    }

    .faq-plus {
        position: absolute;
        width: 22px;
        height: 22px;
    }

    .faq-plus::before,
    .faq-plus::after {
        content: "";
        position: absolute;
        background: currentColor;
        border-radius: 4px;
    }

    .faq-plus::before {
        left: 8px;
        top: 0;
        width: 6px;
        height: 22px;
    }

    .faq-plus::after {
        left: 0;
        top: 8px;
        width: 22px;
        height: 6px;
    }

    .faq-plus.is-one { left: 25%; top: 26px; }
    .faq-plus.is-two { left: 14%; top: 76px; transform: scale(0.8); }
    .faq-plus.is-three { right: 22%; top: 34px; transform: scale(0.65); }

    .faq-ecg {
        position: absolute;
        right: 115px;
        top: 78px;
        width: 160px;
        height: 50px;
    }

    .faq-title {
        margin: 0 0 10px;
        color: #4f0711;
        font-size: clamp(30px, 4vw, 44px);
        font-weight: 950;
        line-height: 1;
        letter-spacing: 0;
    }

    .faq-subtitle {
        max-width: 520px;
        margin: 0 auto 24px;
        color: #6a6f7d;
        font-size: 14px;
        font-weight: 650;
        line-height: 1.55;
    }

    .faq-search-shell {
        display: flex;
        align-items: center;
        gap: 12px;
        width: min(590px, 100%);
        min-height: 60px;
        margin: 0 auto;
        padding: 0 10px 0 24px;
        border: 1px solid rgba(148, 5, 21, 0.08);
        border-radius: 999px;
        background: #ffffff;
        box-shadow: 0 16px 34px rgba(62, 24, 29, 0.12);
    }

    .faq-search-shell svg {
        width: 21px;
        height: 21px;
        flex: 0 0 auto;
        color: #8f3440;
    }

    .faq-search-input {
        width: 100%;
        min-width: 0;
        border: 0;
        outline: 0;
        background: transparent;
        color: var(--faq-ink);
        font-size: 14px;
        font-weight: 700;
    }

    .faq-search-input::placeholder {
        color: #8c8790;
        opacity: 1;
    }

    .faq-voice-btn {
        width: 44px;
        height: 44px;
        border: 0;
        border-radius: 999px;
        background: linear-gradient(180deg, #e16a77, #c84b5a);
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        box-shadow: 0 10px 18px rgba(148, 5, 21, 0.2);
        cursor: pointer;
    }

    .faq-voice-btn svg {
        width: 18px;
        height: 18px;
        color: currentColor;
    }

    .faq-voice-btn.is-active {
        background: linear-gradient(180deg, #facc15, #f5b642);
        color: var(--faq-maroon-dark);
    }

    .faq-categories {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 13px;
        max-width: 900px;
        margin: 24px auto 0;
        padding: 0 20px;
    }

    .faq-filter {
        min-height: 42px;
        padding: 0 18px;
        border: 1px solid rgba(148, 5, 21, 0.1);
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.92);
        color: #343746;
        display: inline-flex;
        align-items: center;
        gap: 9px;
        font-size: 13px;
        font-weight: 850;
        cursor: pointer;
        box-shadow: 0 9px 18px rgba(62, 24, 29, 0.08);
        transition: background 0.16s ease, border-color 0.16s ease, color 0.16s ease, transform 0.16s ease;
    }

    .faq-filter:hover,
    .faq-filter.is-active {
        border-color: rgba(148, 5, 21, 0.26);
        background: #fff3f4;
        color: var(--faq-maroon);
        transform: translateY(-1px);
    }

    .faq-filter-icon {
        width: 20px;
        height: 20px;
        border-radius: 7px;
        color: var(--faq-maroon);
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .faq-filter-icon svg {
        width: 17px;
        height: 17px;
        stroke-width: 2;
    }

    .faq-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(264px, 304px);
        gap: 28px;
        width: min(1150px, calc(100% - 40px));
        margin: 34px auto 70px;
        align-items: start;
    }

    .faq-content-column {
        min-width: 0;
        display: grid;
        gap: 24px;
        position: relative;
        z-index: 1;
        grid-column: 1;
    }

    .faq-sidebar {
        grid-column: 2;
        min-width: 0;
    }

    .sticky-sidebar {
        display: grid;
        gap: 20px;
        width: 100%;
        position: static;
    }

    .faq-main-card,
    .sidebar-widget {
        border: 1px solid rgba(148, 5, 21, 0.1);
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.96);
        box-shadow: 0 16px 34px rgba(44, 23, 25, 0.08);
    }

    .faq-main-card {
        padding: 26px 28px 24px;
    }

    .faq-card-header {
        display: flex;
        align-items: center;
        gap: 17px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--faq-line);
    }

    .faq-card-icon,
    .portal-icon,
    .question-icon,
    .reminder-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        color: var(--faq-maroon);
        background: #fff0f1;
    }

    .faq-card-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
    }

    .faq-card-icon svg {
        width: 31px;
        height: 31px;
        stroke-width: 1.8;
    }

    .faq-card-title {
        margin: 0;
        color: #1f2430;
        font-size: 22px;
        font-weight: 950;
        letter-spacing: 0;
    }

    .faq-card-title::after {
        content: "";
        display: block;
        width: 56px;
        height: 3px;
        margin-top: 10px;
        border-radius: 999px;
        background: var(--faq-maroon);
    }

    .faq-popular {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin: 24px 0 14px;
        color: var(--faq-maroon);
        font-size: 12px;
        font-weight: 950;
    }

    .faq-popular svg {
        width: 14px;
        height: 14px;
        fill: currentColor;
    }

    .faq-list {
        display: grid;
        gap: 13px;
    }

    .faq-group {
        display: grid;
        gap: 13px;
    }

    .faq-group-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 8px 0 0;
        color: #4f0711;
        font-size: 15px;
        font-weight: 950;
    }

    .faq-group-title-icon {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        border-radius: 8px;
        background: #fff0f1;
        color: var(--faq-maroon);
    }

    .faq-group-title-icon svg {
        width: 18px;
        height: 18px;
        stroke-width: 2;
    }

    .faq-question {
        border: 1px solid #e9eaf0;
        border-radius: 8px;
        background: #ffffff;
        box-shadow: 0 7px 16px rgba(42, 31, 32, 0.05);
        overflow: hidden;
    }

    .faq-question[open] {
        border-color: rgba(148, 5, 21, 0.22);
        box-shadow: 0 12px 20px rgba(148, 5, 21, 0.08);
    }

    .faq-question summary {
        min-height: 55px;
        padding: 9px 52px 9px 14px;
        display: flex;
        align-items: center;
        gap: 17px;
        position: relative;
        color: #343746;
        font-size: 14px;
        font-weight: 900;
        list-style: none;
        cursor: pointer;
    }

    .faq-question summary::-webkit-details-marker {
        display: none;
    }

    .faq-question summary::after {
        content: "+";
        position: absolute;
        right: 22px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--faq-maroon);
        font-size: 22px;
        font-weight: 500;
        line-height: 1;
    }

    .faq-question[open] summary::after {
        content: "-";
        margin-top: -2px;
    }

    .question-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
    }

    .question-icon svg {
        width: 20px;
        height: 20px;
        stroke-width: 1.9;
    }

    .faq-answer {
        padding: 0 24px 20px 67px;
        color: #677081;
        font-size: 13px;
        font-weight: 650;
        line-height: 1.65;
    }

    .faq-view-all {
        margin-top: 20px;
        border: 0;
        background: transparent;
        color: var(--faq-maroon);
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 0;
        font-size: 13px;
        font-weight: 950;
        cursor: pointer;
    }

    .faq-view-all svg {
        width: 15px;
        height: 15px;
    }

    .faq-empty {
        margin: 0;
        display: grid;
        gap: 8px;
        padding: 26px 6px 4px;
        color: var(--faq-muted);
        font-size: 14px;
        font-weight: 700;
        line-height: 1.6;
    }

    .faq-empty strong {
        color: var(--faq-ink);
        font-size: 15px;
        font-weight: 900;
    }

    .faq-empty span {
        display: block;
        max-width: 620px;
    }

    html[data-theme="dark"] .faq-page {
        --faq-ink: #f5f7fb;
        --faq-muted: #cbd5e1;
        --faq-line: rgba(148, 163, 184, 0.24);
        background:
            linear-gradient(180deg, rgba(2, 6, 23, 0.82), rgba(15, 23, 42, 0.74) 42%, rgba(2, 6, 23, 0.84) 100%),
            url('{{ asset('images/student-bg.png') }}') center top / cover no-repeat;
        color: #f5f7fb;
    }

    html[data-theme="dark"] .faq-page::before {
        content: none;
    }

    html[data-theme="dark"] .faq-hero::before,
    html[data-theme="dark"] .faq-hero::after,
    html[data-theme="dark"] .faq-hero-art {
        opacity: 0.18;
    }

    html[data-theme="dark"] .faq-title,
    html[data-theme="dark"] .faq-card-title,
    html[data-theme="dark"] .faq-group-title,
    html[data-theme="dark"] .faq-empty strong {
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .faq-subtitle,
    html[data-theme="dark"] .faq-empty,
    html[data-theme="dark"] .faq-empty span {
        color: #cbd5e1 !important;
    }

    html[data-theme="dark"] .faq-search-shell,
    html[data-theme="dark"] .faq-main-card,
    html[data-theme="dark"] .sidebar-widget {
        background: rgba(15, 23, 42, 0.92);
        border-color: rgba(148, 163, 184, 0.24);
        box-shadow: 0 18px 42px rgba(0, 0, 0, 0.34);
    }

    html[data-theme="dark"] .faq-search-input {
        color: #f8fafc;
    }

    html[data-theme="dark"] .faq-search-input::placeholder {
        color: #94a3b8;
    }

    html[data-theme="dark"] .faq-filter {
        background: rgba(15, 23, 42, 0.88);
        border-color: rgba(148, 163, 184, 0.24);
        color: #e5e7eb;
    }

    html[data-theme="dark"] .faq-filter:hover,
    html[data-theme="dark"] .faq-filter.is-active {
        background: rgba(148, 5, 21, 0.28);
        border-color: rgba(248, 113, 113, 0.42);
        color: #ffffff;
    }

    html[data-theme="dark"] .faq-card-icon,
    html[data-theme="dark"] .faq-filter-icon,
    html[data-theme="dark"] .faq-group-title-icon,
    html[data-theme="dark"] .portal-icon,
    html[data-theme="dark"] .question-icon,
    html[data-theme="dark"] .reminder-icon {
        background: rgba(148, 5, 21, 0.22);
        color: #fecdd3;
    }

    html[data-theme="dark"] .faq-card-header,
    html[data-theme="dark"] .portal-links {
        border-color: rgba(148, 163, 184, 0.22);
    }

    html[data-theme="dark"] .faq-question {
        background: rgba(2, 6, 23, 0.62);
        border-color: rgba(148, 163, 184, 0.22);
        box-shadow: none;
    }

    html[data-theme="dark"] .faq-question[open] {
        border-color: rgba(248, 113, 113, 0.38);
        box-shadow: 0 16px 28px rgba(0, 0, 0, 0.22);
    }

    html[data-theme="dark"] .faq-question summary {
        color: #f8fafc;
    }

    html[data-theme="dark"] .faq-answer,
    html[data-theme="dark"] .portal-copy,
    html[data-theme="dark"] .portal-link {
        color: #cbd5e1 !important;
    }

    html[data-theme="dark"] .faq-popular,
    html[data-theme="dark"] .faq-view-all,
    html[data-theme="dark"] .portal-link svg {
        color: #fda4af;
    }

    .sticky-sidebar {
        position: static;
        width: 100%;
        max-width: 304px;
        height: fit-content;
        justify-self: end;
        z-index: 2;
    }

    .sidebar-widget {
        width: 100%;
        max-width: 100%;
        padding: 24px;
        margin-bottom: 20px;
        overflow-wrap: anywhere;
    }

    .portal-head {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 14px;
    }

    .portal-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
    }

    .portal-icon svg {
        width: 21px;
        height: 21px;
    }

    .widget-title {
        margin: 0;
        color: #242735;
        font-size: 17px;
        font-weight: 950;
    }

    .portal-copy {
        margin: 0 0 18px;
        color: #687180;
        font-size: 13px;
        font-weight: 650;
        line-height: 1.55;
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        min-height: 46px;
        padding: 0 18px;
        border-radius: 6px;
        background: var(--faq-maroon);
        color: #ffffff;
        text-decoration: none;
        font-size: 14px;
        font-weight: 950;
        box-shadow: 0 11px 18px rgba(148, 5, 21, 0.18);
        transition: background 0.18s ease, color 0.18s ease, transform 0.18s ease;
    }

    .btn-action:hover {
        background: var(--faq-gold);
        color: var(--faq-maroon-dark);
        transform: translateY(-1px);
    }

    .btn-action svg {
        width: 17px;
        height: 17px;
    }

    .portal-links {
        display: grid;
        gap: 0;
        margin-top: 20px;
        padding-top: 12px;
        border-top: 1px solid #eeeef3;
    }

    .portal-link {
        min-height: 43px;
        display: flex;
        align-items: center;
        gap: 12px;
        color: #646b77;
        text-decoration: none;
        font-size: 13px;
        font-weight: 800;
    }

    .portal-link svg {
        width: 17px;
        height: 17px;
        color: #9c424c;
        flex: 0 0 auto;
    }

    .portal-link span {
        flex: 1;
    }

    .portal-link::after {
        content: ">";
        color: #8b3c46;
        font-size: 17px;
        line-height: 1;
    }

    .stat-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        padding: 10px 0;
        border-bottom: 1px solid #eeeef3;
        color: #646b77;
        font-size: 13px;
        font-weight: 800;
    }

    .stat-row:last-of-type {
        border-bottom: 0;
    }

    .stat-val {
        color: #202331;
        font-weight: 950;
    }

    .reminder-widget {
        border-color: rgba(245, 182, 66, 0.52);
        background: linear-gradient(135deg, #fff8e8, #ffffff);
    }

    .reminder-content {
        display: flex;
        gap: 13px;
        align-items: flex-start;
    }

    .reminder-icon {
        width: 39px;
        height: 39px;
        border-radius: 999px;
        background: #fff2cf;
        color: #b97815;
    }

    .reminder-icon svg {
        width: 21px;
        height: 21px;
    }

    .reminder-title {
        margin: 0 0 6px;
        color: #9b5e0b;
        font-size: 13px;
        font-weight: 950;
    }

    .reminder-text {
        margin: 0;
        color: #667085;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.5;
    }

    @media (max-width: 980px) {
        .faq-page {
            height: auto;
            min-height: calc(100vh - 120px);
            overflow: visible;
        }

        .faq-layout {
            display: grid;
            grid-template-columns: 1fr;
            height: auto;
            overflow: visible;
            gap: 22px;
        }

        .faq-sidebar {
            order: 2 !important;
            width: 100%;
            max-width: none;
            position: relative;
            z-index: 1;
            grid-column: auto;
            grid-row: 2;
        }

        .faq-content-column {
            order: 1 !important;
            height: auto;
            overflow: visible;
            padding-right: 0;
            width: 100%;
            max-width: none;
            min-width: 0;
            grid-column: auto;
            grid-row: 1;
        }

        .sticky-sidebar {
            position: static;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            width: 100%;
            max-width: none;
            justify-self: stretch;
            z-index: auto;
        }

        .sidebar-widget {
            width: 100%;
            max-width: none;
            margin-bottom: 0;
        }
    }

    @media (max-width: 640px) {
        .faq-page {
            overflow: visible;
        }

        .faq-hero {
            padding-top: 34px;
        }

        .faq-search-shell {
            min-height: 54px;
            padding-left: 18px;
        }

        .faq-categories {
            justify-content: flex-start;
            flex-wrap: nowrap;
            overflow-x: auto;
            padding-bottom: 4px;
        }

        .faq-filter {
            flex: 0 0 auto;
        }

        .faq-layout {
            display: grid;
            grid-template-columns: 1fr;
            gap: 18px;
            width: min(100% - 28px, 1090px);
            margin-top: 18px;
        }

        .faq-content-column,
        .faq-sidebar,
        .sticky-sidebar {
            width: 100%;
            max-width: none;
            min-width: 0;
        }

        .faq-content-column {
            order: 1 !important;
        }

        .faq-sidebar {
            order: 2 !important;
        }

        .sticky-sidebar {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .faq-main-card,
        .sidebar-widget {
            border-radius: 9px;
        }

        .faq-main-card {
            padding: 20px 16px;
        }

        .faq-card-header {
            gap: 12px;
        }

        .faq-card-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
        }

        .faq-card-title {
            font-size: 18px;
        }

        .faq-question summary {
            padding-right: 42px;
            gap: 12px;
        }

        .faq-answer {
            padding-left: 61px;
        }
    }
</style>
@endpush

@section('content')
@php
    $faqGroups = $faqs->groupBy(fn ($faq) => $faq->category ?: 'General');
    $iconMap = [
        'appointment' => 'calendar-days',
        'appointments' => 'calendar-days',
        'medical clearance' => 'document-text',
        'clearance' => 'document-text',
        'operating hours' => 'clock',
        'hours' => 'clock',
        'health forms' => 'clipboard-document-list',
        'forms' => 'clipboard-document-list',
        'contact' => 'map-pin',
        'general' => 'heart-pulse',
    ];

    $iconFor = function ($label) use ($iconMap) {
        $normalized = strtolower(trim($label));
        foreach ($iconMap as $needle => $icon) {
            if (str_contains($normalized, $needle)) {
                return $icon;
            }
        }

        return 'clipboard-document-list';
    };
@endphp

<div class="faq-page" id="faqApp">
    <div class="faq-layout">
        <div class="faq-content-column">
            <section class="faq-hero" aria-labelledby="faqTitle">
                <div class="faq-hero-art" aria-hidden="true">
                    <span class="faq-plus is-one"></span>
                    <span class="faq-plus is-two"></span>
                    <span class="faq-plus is-three"></span>
                    <svg class="faq-ecg" viewBox="0 0 160 50" fill="none">
                        <path d="M2 29H35L44 17L54 41L66 8L80 29H98L108 22L119 29H158" stroke="currentColor" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>

                <h1 class="faq-title" id="faqTitle">Need help?</h1>
                <p class="faq-subtitle">Find answers to common questions about clinic appointments, medical records, and health services.</p>

                <label class="faq-search-shell" for="faqSearch">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="m21 21-4.35-4.35M11 19a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <input type="search" class="faq-search-input" placeholder="Search clinic questions..." id="faqSearch" autocomplete="off">
                    <button class="faq-voice-btn" type="button" aria-label="Voice search" aria-pressed="false">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 14a3 3 0 0 0 3-3V6a3 3 0 1 0-6 0v5a3 3 0 0 0 3 3ZM19 11a7 7 0 0 1-14 0M12 18v3M8 21h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </label>

                @if($faqGroups->isNotEmpty())
                    <div class="faq-categories" aria-label="FAQ categories">
                        @foreach($faqGroups as $category => $categoryFaqs)
                            @php
                                $categoryIcon = $iconFor($category);
                            @endphp
                            <button class="faq-filter" type="button" data-faq-filter="{{ Str::slug($category) }}">
                                <span class="faq-filter-icon" aria-hidden="true">
                                    <x-outline-icon :name="$categoryIcon" />
                                </span>
                                <span>{{ $category }}</span>
                            </button>
                        @endforeach
                    </div>
                @else
                    <div class="faq-categories" aria-label="FAQ support">
                        <a class="faq-filter" href="#contact">
                            <span class="faq-filter-icon" aria-hidden="true">
                                <x-outline-icon name="map-pin" />
                            </span>
                            <span>Contact</span>
                        </a>
                    </div>
                @endif
            </section>

            <main class="faq-main-card" id="publishedFaqs">
                @if($faqGroups->isNotEmpty())
                    <div class="faq-card-header">
                        <span class="faq-card-icon" aria-hidden="true">
                            <x-outline-icon :name="$iconFor($faqGroups->keys()->first())" />
                        </span>
                        <h2 class="faq-card-title">{{ $faqGroups->count() === 1 ? $faqGroups->keys()->first() : 'Published FAQs' }}</h2>
                    </div>

                    <div class="faq-popular">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M13.5 2.7c.2 2.8 1.6 4.6 3.2 6.3 1.7 1.8 3.3 3.8 3.3 6.7A8 8 0 0 1 4 15.8c0-2.7 1.3-4.7 3-6.8.7 1.9 1.8 2.9 3 3.4-.6-3.4.2-6.8 3.5-9.7Z"/>
                        </svg>
                        Popular Questions
                    </div>

                    <div class="faq-list" id="faqList">
                        @foreach($faqGroups as $category => $categoryFaqs)
                            @php
                                $categoryIcon = $iconFor($category);
                            @endphp
                            <div class="faq-group" data-faq-category="{{ Str::slug($category) }}">
                                <h3 class="faq-group-title">
                                    <span class="faq-group-title-icon" aria-hidden="true">
                                        <x-outline-icon :name="$categoryIcon" />
                                    </span>
                                    <span>{{ $category }}</span>
                                </h3>
                                @foreach($categoryFaqs as $faq)
                                    <details class="faq-question" data-question="{{ Str::lower($faq->question . ' ' . $faq->answer . ' ' . $category) }}">
                                        <summary>
                                            <span class="question-icon" aria-hidden="true">
                                                <x-outline-icon :name="$categoryIcon" />
                                            </span>
                                            <span>{{ $faq->question }}</span>
                                        </summary>
                                        <div class="faq-answer">{{ $faq->answer }}</div>
                                    </details>
                                @endforeach
                            </div>
                        @endforeach
                    </div>

                    <button class="faq-view-all" type="button" id="faqViewAll">
                        View all questions
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                @else
                    <div class="faq-empty">
                        <strong>No FAQs are available yet.</strong>
                        <span>Published clinic questions will appear here once the administrator adds FAQ categories and answers.</span>
                    </div>
                @endif
            </main>
        </div>

        <aside class="faq-sidebar">
            <div class="sticky-sidebar">
                @auth('student')
                <div class="sidebar-widget">
                    <div class="portal-head">
                        <span class="portal-icon" aria-hidden="true"><x-outline-icon name="user-circle" /></span>
                        <h4 class="widget-title">Student Portal</h4>
                    </div>
                    <div class="stat-row"><span>Pending Requests</span><span class="stat-val" style="color:#b45309;">{{ $pendingCount ?? 0 }}</span></div>
                    <div class="stat-row"><span>Upcoming</span><span class="stat-val" style="color:#15803d;">{{ $upcomingCount ?? 0 }}</span></div>
                    <div class="stat-row"><span>Completed</span><span class="stat-val">{{ $completedCount ?? 0 }}</span></div>
                    <div class="stat-row"><span>Cancelled</span><span class="stat-val" style="color:#b91c1c;">{{ $cancelledCount ?? 0 }}</span></div>
                    <a href="{{ url('/student/history') }}" class="btn-action">
                        View Full History
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>
                @else
                <div class="sidebar-widget">
                    <div class="portal-head">
                        <span class="portal-icon" aria-hidden="true"><x-outline-icon name="user-circle" /></span>
                        <h4 class="widget-title">Student Portal</h4>
                    </div>
                    <p class="portal-copy">
                        Log in through One Portal to access your appointments, history, and health record.
                    </p>
                    <a href="{{ route('login.portal') }}" class="btn-action">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M10 17l5-5-5-5M15 12H3M21 5v14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Log In via One Portal
                    </a>
                    <div class="portal-links">
                        <a href="{{ url('/student/booking') }}" class="portal-link">
                            <x-outline-icon name="calendar-days" />
                            <span>View Appointments</span>
                        </a>
                        <a href="{{ url('/student/account') }}" class="portal-link">
                            <x-outline-icon name="briefcase" />
                            <span>Health Records</span>
                        </a>
                        <a href="{{ url('/student/health-form') }}" class="portal-link">
                            <x-outline-icon name="clipboard-document-list" />
                            <span>Medical Certificates</span>
                        </a>
                    </div>
                </div>
                @endauth

                <div class="sidebar-widget" id="contact" style="background: #20343a; color: white; border: none;">
                    <h4 class="widget-title" style="color: white;">Contact Us</h4>
                    <p style="font-size: 13px; opacity: 0.8; margin-bottom: 15px;">Need urgent help?</p>
                    <div style="font-size: 14px; font-weight: 600;">(02) 8837-5858<br>puptclinic@gmail.com</div>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const search = document.getElementById('faqSearch');
        const groups = Array.from(document.querySelectorAll('[data-faq-category]'));
        const filters = Array.from(document.querySelectorAll('[data-faq-filter]'));
        const viewAll = document.getElementById('faqViewAll');
        const publishedFaqs = document.getElementById('publishedFaqs');
        const voiceButton = document.querySelector('.faq-voice-btn');
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        const recognition = SpeechRecognition ? new SpeechRecognition() : null;
        let activeCategory = '';

        if (recognition) {
            recognition.lang = 'en-US';
            recognition.interimResults = true;
            recognition.continuous = false;
        }

        function applyFaqFilters() {
            const term = (search?.value || '').trim().toLowerCase();

            groups.forEach(function (group) {
                const categoryMatches = !activeCategory || group.dataset.faqCategory === activeCategory;
                let visibleInGroup = false;

                group.querySelectorAll('.faq-question').forEach(function (item) {
                    const textMatches = !term || (item.dataset.question || '').includes(term);
                    const shouldShow = categoryMatches && textMatches;
                    item.style.display = shouldShow ? '' : 'none';
                    if (shouldShow) {
                        visibleInGroup = true;
                    } else {
                        item.open = false;
                    }
                });

                group.style.display = visibleInGroup ? 'contents' : 'none';
            });

            filters.forEach(function (button) {
                button.classList.toggle('is-active', button.dataset.faqFilter === activeCategory);
            });
        }

        search?.addEventListener('input', applyFaqFilters);

        filters.forEach(function (button) {
            button.addEventListener('click', function () {
                const nextCategory = button.dataset.faqFilter;
                activeCategory = activeCategory === nextCategory ? '' : nextCategory;
                applyFaqFilters();
                publishedFaqs?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });

        viewAll?.addEventListener('click', function () {
            activeCategory = '';
            if (search) {
                search.value = '';
            }
            applyFaqFilters();
            publishedFaqs?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        voiceButton?.addEventListener('click', function () {
            if (!recognition) {
                voiceButton.title = 'Voice search is not supported in this browser.';
                voiceButton.classList.remove('is-active');
                voiceButton.setAttribute('aria-pressed', 'false');
                return;
            }

            if (voiceButton.classList.contains('is-active')) {
                recognition.stop();
                return;
            }

            try {
                recognition.start();
            } catch (error) {
                recognition.stop();
            }
        });

        recognition?.addEventListener('start', function () {
            voiceButton?.classList.add('is-active');
            voiceButton?.setAttribute('aria-pressed', 'true');
        });

        recognition?.addEventListener('result', function (event) {
            const transcript = Array.from(event.results)
                .map((result) => result[0]?.transcript || '')
                .join(' ')
                .trim();

            if (search && transcript !== '') {
                search.value = transcript;
                applyFaqFilters();
            }
        });

        recognition?.addEventListener('end', function () {
            voiceButton?.classList.remove('is-active');
            voiceButton?.setAttribute('aria-pressed', 'false');
        });

        recognition?.addEventListener('error', function () {
            voiceButton?.classList.remove('is-active');
            voiceButton?.setAttribute('aria-pressed', 'false');
        });

        applyFaqFilters();
    });
</script>
@endpush
