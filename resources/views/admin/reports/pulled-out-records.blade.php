@extends('layouts.admin')

@section('title', 'Pulled Out Records')

@push('styles')
<style>
    .pullout-report-shell {
        --por-maroon: #7f1d2d;
        --por-yellow: #facc15;
        --por-border: #ead8dc;
        --por-muted: #64748b;
        display: grid;
        gap: 16px;
        padding: 10px;
    }
    .pullout-report-header,
    .pullout-report-panel {
        border: 1px solid var(--por-border);
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 14px 34px rgba(72, 16, 27, .08);
    }
    .pullout-report-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 22px 24px;
    }
    .pullout-report-title-wrap,
    .pullout-report-head-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }
    .pullout-report-icon {
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        width: 48px;
        height: 48px;
        border-radius: 8px;
        color: var(--por-maroon);
        background: #fff1f3;
    }
    .pullout-report-icon svg { width: 24px; height: 24px; }
    .pullout-report-heading h1 {
        margin: 0;
        color: #172033;
        font-size: 26px;
        line-height: 1.15;
    }
    .pullout-report-heading p {
        margin: 5px 0 0;
        color: var(--por-muted);
        font-size: 14px;
    }
    .pullout-report-back {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 38px;
        padding: 0 12px;
        border: 1px solid #e3c2c8;
        border-radius: 7px;
        color: var(--por-maroon);
        background: #fff8f8;
        font-size: 12px;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
    }
    .pullout-report-back {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        transition: color .2s ease, background .2s ease, border-color .2s ease, transform .2s ease;
    }
    .pullout-report-back > * {
        position: relative;
        z-index: 1;
    }
    .pullout-report-back svg {
        width: 16px;
        height: 16px;
    }
    .pullout-report-back::after,
    .pullout-filter-toggle::after,
    .pullout-filter-button::after,
    .pullout-filter-option::after,
    .pullout-view-btn::after {
        content: "";
        position: absolute;
        top: -45%;
        left: -130%;
        width: 120%;
        height: 190%;
        background: linear-gradient(115deg, rgba(255,247,181,0) 0%, rgba(255,247,181,.74) 46%, rgba(255,247,181,0) 100%);
        transform: skewX(-20deg);
        transition: left 1.05s ease;
        pointer-events: none;
        z-index: 0;
    }
    .pullout-report-back:hover,
    .pullout-report-back:focus-visible {
        border-color: var(--por-yellow);
        color: var(--por-maroon);
        background: var(--por-yellow);
        outline: none;
        transform: translateY(-1px);
    }
    .pullout-report-back:hover::after,
    .pullout-report-back:focus-visible::after,
    .pullout-filter-toggle:hover::after,
    .pullout-filter-toggle:focus-visible::after,
    .pullout-filter-button:hover::after,
    .pullout-filter-button:focus-visible::after,
    .pullout-filter-option:hover::after,
    .pullout-filter-option:focus-visible::after,
    .pullout-view-btn:hover::after,
    .pullout-view-btn:focus-visible::after { left: 125%; }
    .pullout-filter-form { margin: 0; }
    .pullout-toolbar {
        position: relative;
        z-index: 20;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px;
        border-bottom: 1px solid #eee5e7;
    }
    .pullout-input-wrap {
        position: relative;
        flex: 1 1 auto;
        min-width: 0;
    }
    .pullout-input-wrap > svg {
        position: absolute;
        top: 50%;
        left: 15px;
        width: 18px;
        height: 18px;
        color: var(--por-maroon);
        transform: translateY(-50%);
        pointer-events: none;
    }
    .pullout-search {
        width: 100%;
        height: 48px;
        padding: 0 15px 0 44px;
        border: 1px solid #dfcbd0;
        border-radius: 12px;
        color: #263044;
        background: #fff;
        font: inherit;
        font-size: 13px;
        outline: none;
        box-shadow: 0 5px 12px rgba(15, 23, 42, .07);
        transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
    }
    .pullout-search:hover,
    .pullout-search:focus {
        border-color: var(--por-yellow);
        box-shadow: 0 0 0 3px rgba(250,204,21,.14), 0 14px 28px rgba(112,19,27,.13);
        transform: translateY(-1px);
    }
    .pullout-filter-shell {
        position: relative;
        display: inline-flex;
        flex: 0 0 auto;
    }
    .pullout-filter-toggle,
    .pullout-filter-button,
    .pullout-view-btn {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: 1px solid var(--por-maroon);
        border-radius: 12px;
        color: #fff;
        background: var(--por-maroon);
        font: inherit;
        font-size: 12px;
        font-weight: 850;
        text-decoration: none;
        cursor: pointer;
        box-shadow: 0 12px 24px rgba(112,19,27,.2);
        transition: color .2s ease, background .2s ease, border-color .2s ease, transform .2s ease, box-shadow .2s ease;
    }
    .pullout-filter-toggle {
        width: 118px;
        height: 48px;
        padding: 0 14px;
    }
    .pullout-filter-toggle > *,
    .pullout-filter-button > *,
    .pullout-view-btn > * {
        position: relative;
        z-index: 1;
    }
    .pullout-filter-toggle::before,
    .pullout-filter-button:not(.is-reset)::before {
        content: "";
        position: absolute;
        z-index: 0;
        inset: 0;
        background: var(--por-maroon);
        transition: background .2s ease;
    }
    .pullout-filter-toggle:hover::before,
    .pullout-filter-toggle:focus-visible::before,
    .pullout-filter-button:not(.is-reset):hover::before,
    .pullout-filter-button:not(.is-reset):focus-visible::before {
        background: var(--por-yellow);
    }
    .pullout-filter-toggle svg {
        width: 17px;
        height: 17px;
        color: #fff;
        stroke: currentColor;
    }
    .pullout-filter-toggle:hover,
    .pullout-filter-toggle:focus-visible,
    .pullout-filter-button:hover,
    .pullout-filter-button:focus-visible,
    .pullout-view-btn:hover,
    .pullout-view-btn:focus-visible {
        border-color: var(--por-yellow);
        color: var(--por-maroon);
        background: var(--por-yellow);
        outline: none;
        transform: translateY(-1px);
        box-shadow: 0 12px 24px rgba(112,19,27,.16);
    }
    .pullout-filter-panel {
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        z-index: 80;
        display: none;
        width: min(310px, calc(100vw - 40px));
        padding: 14px;
        border: 1px solid #ead3d7;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 22px 48px rgba(15,23,42,.2);
    }
    .pullout-filter-shell.is-open .pullout-filter-panel { display: block; }
    .pullout-filter-panel-title {
        margin: 0 0 12px;
        color: var(--por-maroon);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .06em;
        text-transform: uppercase;
    }
    .pullout-filter-fields { display: grid; gap: 10px; }
    .pullout-filter-field {
        position: relative;
        display: grid;
        gap: 5px;
    }
    .pullout-filter-field > label {
        color: var(--por-maroon);
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase;
    }
    .pullout-filter-select-wrap { position: relative; z-index: 4; }
    .pullout-filter-select {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
        pointer-events: none;
    }
    .pullout-filter-trigger,
    .pullout-filter-date {
        width: 100%;
        height: 38px;
        border: 1px solid #ead3d7;
        border-radius: 8px;
        color: var(--por-maroon);
        background: #fff;
        font: inherit;
        font-size: 12px;
        font-weight: 800;
        outline: none;
    }
    .pullout-filter-trigger {
        position: relative;
        display: flex;
        align-items: center;
        padding: 0 38px 0 12px;
        text-align: left;
        cursor: pointer;
    }
    .pullout-filter-trigger::after {
        content: "";
        position: absolute;
        top: 50%;
        right: 14px;
        width: 7px;
        height: 7px;
        border-right: 1.6px solid currentColor;
        border-bottom: 1.6px solid currentColor;
        transform: translateY(-68%) rotate(45deg);
        transition: transform .2s ease;
    }
    .pullout-filter-select-wrap.is-open .pullout-filter-trigger::after {
        transform: translateY(-30%) rotate(225deg);
    }
    .pullout-filter-trigger:focus-visible,
    .pullout-filter-date:focus {
        border-color: var(--por-maroon);
        box-shadow: 0 0 0 3px rgba(127,29,45,.1);
    }
    .pullout-filter-menu {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        z-index: 90;
        display: none;
        gap: 8px;
        padding: 8px;
        border: 1px solid #ead3d7;
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 18px 34px rgba(15,23,42,.18);
    }
    .pullout-filter-select-wrap.is-open .pullout-filter-menu { display: grid; }
    .pullout-filter-option {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        width: 100%;
        min-height: 38px;
        padding: 0 12px;
        border: 1px solid #ead3d7;
        border-radius: 8px;
        color: var(--por-maroon);
        background: #fff;
        font: inherit;
        font-size: 13px;
        font-weight: 850;
        text-align: left;
        cursor: pointer;
    }
    .pullout-filter-option > span { position: relative; z-index: 1; }
    .pullout-filter-option.is-selected {
        border-color: var(--por-maroon);
        color: #fff;
        background: var(--por-maroon);
    }
    .pullout-filter-option:hover,
    .pullout-filter-option:focus-visible,
    .pullout-filter-option.is-selected:hover,
    .pullout-filter-option.is-selected:focus-visible {
        border-color: var(--por-yellow);
        color: var(--por-maroon);
        background: var(--por-yellow);
        outline: none;
        box-shadow: 0 8px 18px rgba(250,204,21,.24);
    }
    .pullout-filter-actions {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
        margin-top: 2px;
    }
    .pullout-filter-button {
        min-height: 38px;
        padding: 0 12px;
        border-radius: 8px;
    }
    .pullout-filter-button.is-reset {
        border-color: #dfcbd0;
        color: #334155;
        background: #fff;
        box-shadow: none;
    }
    .pullout-table-wrap {
        position: relative;
        z-index: 1;
        overflow-x: auto;
    }
    .pullout-table { width: 100%; min-width: 980px; border-collapse: collapse; }
    .pullout-table th {
        padding: 13px 14px;
        color: #791321;
        background: #fbf2f4;
        font-size: 11px;
        text-align: left;
        text-transform: uppercase;
    }
    .pullout-table td {
        padding: 14px;
        border-top: 1px solid #eee5e7;
        color: #263044;
        font-size: 13px;
        vertical-align: middle;
    }
    .pullout-person strong { display: block; color: #172033; font-size: 14px; }
    .pullout-person span { display: block; margin-top: 3px; color: var(--por-muted); font-size: 12px; }
    .pullout-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 9px;
        border-radius: 999px;
        color: #8a1421;
        background: #ffe8eb;
        font-size: 11px;
        font-weight: 800;
    }
    .pullout-view-btn {
        min-height: 38px;
        padding: 0 14px;
        border-radius: 8px;
    }
    .pullout-view-btn svg { width: 16px; height: 16px; }
    .pullout-empty { padding: 54px 20px !important; color: var(--por-muted) !important; text-align: center; }
    .pullout-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 14px 16px;
        border-top: 1px solid #eee5e7;
        color: var(--por-muted);
        font-size: 12px;
        font-weight: 700;
    }
    .pullout-pagination nav { display: flex; }
    .pullout-pagination nav > div:first-child { display: none; }
    .pullout-pagination nav > div:last-child { display: flex; align-items: center; gap: 10px; }
    .pullout-pagination nav p { display: none; }
    .pullout-pagination nav span,
    .pullout-pagination nav a { border-radius: 6px !important; }
    html[data-theme="dark"] .pullout-report-shell { --por-border: #344157; --por-muted: #aeb9cc; }
    html[data-theme="dark"] .pullout-report-header,
    html[data-theme="dark"] .pullout-report-panel {
        border-color: #344157;
        background: #111a2d;
        box-shadow: 0 16px 38px rgba(0,0,0,.4);
    }
    html[data-theme="dark"] .pullout-report-heading,
    html[data-theme="dark"] .pullout-toolbar,
    html[data-theme="dark"] .pullout-table td,
    html[data-theme="dark"] .pullout-pagination { border-color: #344157; }
    html[data-theme="dark"] .pullout-report-heading h1,
    html[data-theme="dark"] .pullout-person strong,
    html[data-theme="dark"] .pullout-table td { color: #f7f9fd; }
    html[data-theme="dark"] .pullout-report-back {
        border-color: #46516a;
        color: #f7f9fd;
        background: #192338;
    }
    html[data-theme="dark"] .pullout-search,
    html[data-theme="dark"] .pullout-filter-panel,
    html[data-theme="dark"] .pullout-filter-menu {
        border-color: rgba(255,255,255,.16);
        color: #f7f9fd;
        background: #182334;
    }
    html[data-theme="dark"] .pullout-filter-panel-title,
    html[data-theme="dark"] .pullout-filter-field > label { color: #fff; }
    html[data-theme="dark"] .pullout-filter-trigger,
    html[data-theme="dark"] .pullout-filter-date,
    html[data-theme="dark"] .pullout-filter-option {
        border-color: rgba(255,255,255,.16);
        color: #f8fafc;
        background: #223044;
    }
    html[data-theme="dark"] .pullout-filter-option.is-selected {
        border-color: #9f1d2d;
        color: #fff;
        background: #9f1d2d;
    }
    html[data-theme="dark"] .pullout-filter-option:hover,
    html[data-theme="dark"] .pullout-filter-option:focus-visible,
    html[data-theme="dark"] .pullout-filter-option.is-selected:hover,
    html[data-theme="dark"] .pullout-filter-option.is-selected:focus-visible,
    html[data-theme="dark"] .pullout-filter-button:hover,
    html[data-theme="dark"] .pullout-filter-button:focus-visible,
    html[data-theme="dark"] .pullout-report-back:hover,
    html[data-theme="dark"] .pullout-report-back:focus-visible {
        border-color: var(--por-yellow);
        color: var(--por-maroon);
        background: var(--por-yellow);
    }
    html[data-theme="dark"] .pullout-filter-button.is-reset {
        border-color: rgba(255,255,255,.16);
        color: #f8fafc;
        background: #223044;
    }
    html[data-theme="dark"] .pullout-table th { color: #ffd7dc; background: #202a40; }
    body .main .pullout-report-icon {
        color: var(--por-maroon) !important;
        -webkit-text-fill-color: var(--por-maroon) !important;
    }
    body .main .pullout-report-icon svg,
    body .main .pullout-report-icon svg * {
        fill: none !important;
        stroke: currentColor !important;
    }
    body .main button.pullout-filter-toggle,
    body .main button.pullout-filter-button:not(.is-reset) {
        border-color: var(--por-maroon) !important;
        color: #ffffff !important;
        background: var(--por-maroon) !important;
        -webkit-text-fill-color: #ffffff !important;
    }
    body .main button.pullout-filter-toggle svg,
    body .main button.pullout-filter-toggle svg *,
    body .main button.pullout-filter-toggle span,
    body .main button.pullout-filter-button:not(.is-reset) span {
        color: #ffffff !important;
        fill: none !important;
        stroke: currentColor !important;
        -webkit-text-fill-color: #ffffff !important;
    }
    body .main button.pullout-filter-toggle:hover,
    body .main button.pullout-filter-toggle:focus-visible,
    body .main button.pullout-filter-button:not(.is-reset):hover,
    body .main button.pullout-filter-button:not(.is-reset):focus-visible {
        border-color: var(--por-yellow) !important;
        color: var(--por-maroon) !important;
        background: var(--por-yellow) !important;
        -webkit-text-fill-color: var(--por-maroon) !important;
    }
    body .main button.pullout-filter-toggle:hover svg,
    body .main button.pullout-filter-toggle:focus-visible svg,
    body .main button.pullout-filter-toggle:hover svg *,
    body .main button.pullout-filter-toggle:focus-visible svg *,
    body .main button.pullout-filter-toggle:hover span,
    body .main button.pullout-filter-toggle:focus-visible span,
    body .main button.pullout-filter-button:not(.is-reset):hover span,
    body .main button.pullout-filter-button:not(.is-reset):focus-visible span {
        color: var(--por-maroon) !important;
        stroke: currentColor !important;
        -webkit-text-fill-color: var(--por-maroon) !important;
    }
    @media (max-width: 760px) {
        .pullout-report-shell { padding: 4px; }
        .pullout-report-heading { align-items: flex-start; flex-direction: column; padding: 18px; }
        .pullout-report-head-actions { width: 100%; }
        .pullout-report-back { flex: 1 1 auto; }
        .pullout-report-heading h1 { font-size: 22px; }
        .pullout-toolbar { align-items: stretch; flex-direction: column; }
        .pullout-filter-shell,
        .pullout-filter-toggle { width: 100%; }
        .pullout-filter-panel { right: 0; width: 100%; }
        .pullout-pagination { align-items: flex-start; flex-direction: column; }
    }
</style>
@endpush

@push('late-styles')
<style>
    .main .pullout-report-back {
        border-color: #7f1d2d !important;
        background-color: #7f1d2d !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }
    .main .pullout-report-back > span,
    .main .pullout-report-back > svg,
    .main .pullout-report-back > svg * {
        color: #ffffff !important;
        stroke: currentColor !important;
        -webkit-text-fill-color: #ffffff !important;
    }
    .main .pullout-report-back:hover,
    .main .pullout-report-back:focus-visible {
        border-color: #facc15 !important;
        background-color: #facc15 !important;
        color: #70131b !important;
        -webkit-text-fill-color: #70131b !important;
    }
    .main .pullout-report-back:hover > span,
    .main .pullout-report-back:focus-visible > span,
    .main .pullout-report-back:hover > svg,
    .main .pullout-report-back:focus-visible > svg,
    .main .pullout-report-back:hover > svg *,
    .main .pullout-report-back:focus-visible > svg * {
        color: #70131b !important;
        stroke: currentColor !important;
        -webkit-text-fill-color: #70131b !important;
    }
    .main .pullout-table .pullout-view-btn {
        border-color: #e3c2c8 !important;
        background-color: #ffffff !important;
        color: #7f1d2d !important;
        -webkit-text-fill-color: #7f1d2d !important;
    }
    .main .pullout-table .pullout-view-btn > span,
    .main .pullout-table .pullout-view-btn > svg,
    .main .pullout-table .pullout-view-btn > svg * {
        color: #7f1d2d !important;
        stroke: currentColor !important;
        -webkit-text-fill-color: #7f1d2d !important;
    }
    .main .pullout-table .pullout-view-btn:hover,
    .main .pullout-table .pullout-view-btn:focus-visible {
        border-color: #facc15 !important;
        background-color: #facc15 !important;
        color: #70131b !important;
        -webkit-text-fill-color: #70131b !important;
    }
    .main .pullout-table .pullout-view-btn:hover > span,
    .main .pullout-table .pullout-view-btn:focus-visible > span,
    .main .pullout-table .pullout-view-btn:hover > svg,
    .main .pullout-table .pullout-view-btn:focus-visible > svg,
    .main .pullout-table .pullout-view-btn:hover > svg *,
    .main .pullout-table .pullout-view-btn:focus-visible > svg * {
        color: #70131b !important;
        stroke: currentColor !important;
        -webkit-text-fill-color: #70131b !important;
    }
    #pulloutFilterToggle,
    #pulloutFilterPanel button.pullout-filter-button:not(.is-reset) {
        border-color: #7f1d2d !important;
        background-color: #7f1d2d !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }
    #pulloutFilterToggle::before,
    #pulloutFilterPanel button.pullout-filter-button:not(.is-reset)::before {
        background-color: #7f1d2d !important;
    }
    #pulloutFilterToggle > span,
    #pulloutFilterPanel button.pullout-filter-button:not(.is-reset) > span,
    #pulloutFilterToggle > svg,
    #pulloutFilterToggle > svg * {
        color: #ffffff !important;
        stroke: currentColor !important;
        -webkit-text-fill-color: #ffffff !important;
    }
    #pulloutFilterToggle:hover,
    #pulloutFilterToggle:focus-visible,
    #pulloutFilterPanel button.pullout-filter-button:not(.is-reset):hover,
    #pulloutFilterPanel button.pullout-filter-button:not(.is-reset):focus-visible {
        border-color: #facc15 !important;
        background-color: #facc15 !important;
        color: #70131b !important;
        -webkit-text-fill-color: #70131b !important;
    }
    #pulloutFilterToggle:hover::before,
    #pulloutFilterToggle:focus-visible::before,
    #pulloutFilterPanel button.pullout-filter-button:not(.is-reset):hover::before,
    #pulloutFilterPanel button.pullout-filter-button:not(.is-reset):focus-visible::before {
        background-color: #facc15 !important;
    }
    #pulloutFilterToggle:hover > span,
    #pulloutFilterToggle:focus-visible > span,
    #pulloutFilterPanel button.pullout-filter-button:not(.is-reset):hover > span,
    #pulloutFilterPanel button.pullout-filter-button:not(.is-reset):focus-visible > span,
    #pulloutFilterToggle:hover > svg,
    #pulloutFilterToggle:focus-visible > svg,
    #pulloutFilterToggle:hover > svg *,
    #pulloutFilterToggle:focus-visible > svg * {
        color: #70131b !important;
        stroke: currentColor !important;
        -webkit-text-fill-color: #70131b !important;
    }
</style>
@endpush

@section('content')
<div class="pullout-report-shell">
    <section class="pullout-report-header">
        <div class="pullout-report-heading">
            <div class="pullout-report-title-wrap">
                <span class="pullout-report-icon"><x-outline-icon name="document-check" /></span>
                <div>
                    <h1>Pulled Out Records</h1>
                    <p>Digital logbook of archived health records removed from active clinic workflows and portal access.</p>
                </div>
            </div>
            <div class="pullout-report-head-actions">
                <a class="pullout-report-back" href="{{ route('reports.digital-logbook') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    <span>Back</span>
                </a>
            </div>
        </div>
    </section>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <section class="pullout-report-panel">
        <form class="pullout-filter-form" id="pulloutFilterForm" method="GET" action="{{ route('reports.pulled-out-records') }}">
            <div class="pullout-toolbar">
                <div class="pullout-input-wrap">
                    <x-outline-icon name="magnifying-glass" />
                    <input class="pullout-search" type="search" name="q" value="{{ $search }}" placeholder="Search name, email, reference number, or reason">
                </div>

                <div class="pullout-filter-shell" id="pulloutFilterShell">
                    <button class="pullout-filter-toggle" id="pulloutFilterToggle" type="button" aria-expanded="false" aria-controls="pulloutFilterPanel">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                        </svg>
                        <span>Filter</span>
                    </button>

                    <div class="pullout-filter-panel" id="pulloutFilterPanel">
                        <div class="pullout-filter-panel-title">Pulled Out Filter</div>
                        <div class="pullout-filter-fields">
                            <div class="pullout-filter-field">
                                <label for="pulloutUserType">User Type</label>
                                <div class="pullout-filter-select-wrap" id="pulloutUserTypeWrap">
                                    <select class="pullout-filter-select" id="pulloutUserType" name="user_type">
                                        <option value="">All User Types</option>
                                        @foreach(['applicant' => 'Applicant', 'student' => 'Student', 'faculty' => 'Faculty', 'admin' => 'Admin', 'dependent' => 'Dependent'] as $value => $label)
                                            <option value="{{ $value }}" @selected($userType === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button class="pullout-filter-trigger" id="pulloutUserTypeTrigger" type="button" aria-haspopup="listbox" aria-expanded="false"></button>
                                    <div class="pullout-filter-menu" id="pulloutUserTypeMenu" role="listbox"></div>
                                </div>
                            </div>
                            <div class="pullout-filter-field">
                                <label for="pulloutDateFrom">Date From</label>
                                <input class="pullout-filter-date" id="pulloutDateFrom" type="date" name="date_from" value="{{ $dateFrom }}">
                            </div>
                            <div class="pullout-filter-field">
                                <label for="pulloutDateTo">Date To</label>
                                <input class="pullout-filter-date" id="pulloutDateTo" type="date" name="date_to" value="{{ $dateTo }}">
                            </div>
                            <div class="pullout-filter-actions">
                                <a class="pullout-filter-button is-reset" href="{{ route('reports.pulled-out-records') }}"><span>Reset</span></a>
                                <button class="pullout-filter-button" type="submit"><span>Apply</span></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="pullout-table-wrap">
            <table class="pullout-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>User Type</th>
                        <th>Reference Number</th>
                        <th>Previous Clearance</th>
                        <th>Reason</th>
                        <th>Pulled Out By</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        @php
                            $recordUser = $record->user;
                            $recordUserType = trim((string) ($recordUser?->user_type ?: $recordUser?->idp_role ?: $recordUser?->user_role ?: 'User'));
                        @endphp
                        <tr>
                            <td>
                                <div class="pullout-person">
                                    <strong>{{ $recordUser?->name ?: 'Unknown User' }}</strong>
                                    <span>{{ $recordUser?->email ?: 'No email available' }}</span>
                                </div>
                            </td>
                            <td><span class="pullout-badge">{{ \Illuminate\Support\Str::headline($recordUserType) }}</span></td>
                            <td>{{ $record->reference_number ?: $record->student_number ?: 'N/A' }}</td>
                            <td>{{ $record->clearance_status ?: 'N/A' }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($record->pullout_reason ?: 'N/A', 52) }}</td>
                            <td>{{ $record->pulloutCompletedBy?->name ?: $record->pulloutCompletedBy?->email ?: 'N/A' }}</td>
                            <td>{{ $record->pullout_completed_at?->format('M d, Y h:i A') ?: 'N/A' }}</td>
                            <td>
                                <a class="pullout-view-btn" href="{{ route('reports.pulled-out-records.show', $record) }}">
                                    <x-outline-icon name="eye" />
                                    <span>View</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="pullout-empty">No pulled-out health records match the selected filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($records->hasPages())
            <div class="pullout-pagination">
                <span>Showing {{ $records->firstItem() }}-{{ $records->lastItem() }} of {{ $records->total() }} records</span>
                {{ $records->links() }}
            </div>
        @endif
    </section>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const shell = document.getElementById('pulloutFilterShell');
        const toggle = document.getElementById('pulloutFilterToggle');
        const panel = document.getElementById('pulloutFilterPanel');
        const select = document.getElementById('pulloutUserType');
        const selectWrap = document.getElementById('pulloutUserTypeWrap');
        const selectTrigger = document.getElementById('pulloutUserTypeTrigger');
        const selectMenu = document.getElementById('pulloutUserTypeMenu');

        function setFilterOpen(isOpen) {
            if (!shell || !toggle) return;
            shell.classList.toggle('is-open', isOpen);
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            if (!isOpen) setSelectOpen(false);
        }

        function setSelectOpen(isOpen) {
            if (!selectWrap || !selectTrigger) return;
            selectWrap.classList.toggle('is-open', isOpen);
            selectTrigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        }

        function syncSelect() {
            if (!select || !selectTrigger || !selectMenu) return;
            const selected = select.options[select.selectedIndex] || select.options[0];
            selectTrigger.textContent = selected ? selected.textContent.trim() : '';
            selectMenu.querySelectorAll('.pullout-filter-option').forEach(function (optionButton) {
                const isSelected = optionButton.dataset.value === select.value;
                optionButton.classList.toggle('is-selected', isSelected);
                optionButton.setAttribute('aria-selected', isSelected ? 'true' : 'false');
            });
        }

        if (select && selectMenu) {
            Array.from(select.options).forEach(function (option) {
                const optionButton = document.createElement('button');
                const optionLabel = document.createElement('span');
                optionButton.type = 'button';
                optionButton.className = 'pullout-filter-option';
                optionButton.dataset.value = option.value;
                optionButton.setAttribute('role', 'option');
                optionLabel.textContent = option.textContent.trim();
                optionButton.appendChild(optionLabel);
                optionButton.addEventListener('click', function () {
                    select.value = option.value;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    syncSelect();
                    setSelectOpen(false);
                });
                selectMenu.appendChild(optionButton);
            });
            syncSelect();
        }

        toggle?.addEventListener('click', function () {
            setFilterOpen(!shell.classList.contains('is-open'));
        });

        selectTrigger?.addEventListener('click', function () {
            setSelectOpen(!selectWrap.classList.contains('is-open'));
        });

        document.addEventListener('click', function (event) {
            if (shell?.contains(event.target)) return;
            setFilterOpen(false);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') setFilterOpen(false);
        });

        panel?.addEventListener('click', function (event) {
            event.stopPropagation();
        });
    })();
</script>
@endpush
