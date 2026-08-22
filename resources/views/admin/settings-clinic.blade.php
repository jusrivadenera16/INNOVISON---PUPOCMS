@extends('layouts.admin')

@section('title', 'Clinic Information')

@push('styles')
@include('admin.partials.settings-section-style')
<style>
    .settings-days-field {
        grid-column: 1 / -1;
        display: grid;
        gap: 9px;
    }
    .settings-days-label {
        color: var(--stg-muted);
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .06em;
        text-transform: uppercase;
    }
    .settings-days-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 8px;
    }
    .settings-day-option { position: relative; min-width: 0; }
    .settings-day-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .settings-day-option span {
        min-height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(127, 0, 0, .13);
        border-radius: 10px;
        background: #f8fafc;
        color: #475569;
        font-size: 12px;
        font-weight: 850;
        cursor: default;
        transition: border-color .18s ease, background .18s ease, color .18s ease, transform .18s ease;
    }
    .settings-day-option input:checked + span {
        border-color: rgba(250, 204, 21, .76);
        background: #7f0000;
        color: #ffffff;
        box-shadow: 0 7px 16px rgba(127, 0, 0, .16);
    }
    .settings-editable-form.is-editing .settings-day-option span { cursor: pointer; }
    .settings-editable-form.is-editing .settings-day-option:hover span {
        transform: translateY(-1px);
        border-color: rgba(250, 204, 21, .72);
    }
    html[data-theme="dark"] .settings-days-label { color: #cbd5e1; }
    html[data-theme="dark"] .settings-day-option span {
        border-color: rgba(255, 255, 255, .14);
        background: rgba(30, 41, 59, .72);
        color: #cbd5e1;
    }
    html[data-theme="dark"] .settings-day-option input:checked + span {
        border-color: rgba(250, 204, 21, .64);
        background: rgba(127, 0, 0, .62);
        color: #ffffff;
    }
    @media (max-width: 760px) {
        .settings-days-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    }

    /* Clinic settings parity with the Personal Information surface. */
    .clinic-settings-page {
        --clinic-maroon: #7f0010;
        --clinic-text: #172033;
        --clinic-muted: #64748b;
        --clinic-line: rgba(148, 163, 184, .22);
        display: grid;
        gap: 12px;
    }
    .clinic-settings-page .settings-section-hero {
        align-items: center;
        margin-bottom: 0;
        padding: 14px 16px;
        border-radius: 12px;
        border-color: var(--clinic-line);
        box-shadow: 0 8px 22px rgba(15, 23, 42, .05);
    }
    .clinic-settings-page .settings-section-hero > div:first-child {
        display: grid;
        grid-template-columns: 54px minmax(0, 1fr);
        column-gap: 14px;
        align-items: center;
        min-width: 0;
    }
    .clinic-settings-page .settings-section-title { display: contents; }
    .clinic-settings-page .clinic-title-icon {
        grid-column: 1;
        grid-row: 1 / span 2;
        display: grid;
        place-items: center;
        width: 54px;
        height: 54px;
        border-radius: 14px;
        background: #fff1f2;
        color: #b91c1c;
    }
    .clinic-settings-page .clinic-title-icon svg {
        width: 28px;
        height: 28px;
        padding: 0;
        border-radius: 0;
        background: transparent;
        color: currentColor;
    }
    .clinic-settings-page .settings-section-title > span:not(.clinic-title-icon) {
        grid-column: 2;
        grid-row: 1;
        color: var(--clinic-text);
        font-size: 26px;
        font-weight: 900;
        line-height: 1.1;
    }
    .clinic-settings-page .settings-section-hero p {
        grid-column: 2;
        grid-row: 2;
        margin: 6px 0 0;
        color: var(--clinic-muted);
        font-size: 13px;
        font-weight: 700;
        line-height: 1.45;
    }
    .clinic-settings-page .settings-back-link {
        min-height: 34px;
        border-radius: 8px;
        padding: 0 12px;
    }
    .clinic-settings-page .settings-back-link svg { transform: rotate(180deg); }
    .clinic-settings-page .settings-section-grid.two {
        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
    }
    .clinic-settings-page .settings-panel {
        overflow: hidden;
        border-radius: 12px;
        border: 1px solid var(--clinic-line);
        background: rgba(255, 255, 255, .96);
        box-shadow: 0 8px 22px rgba(15, 23, 42, .045);
    }
    .clinic-settings-page .settings-panel-head {
        align-items: center;
        min-height: 54px;
        padding: 11px 12px;
        border-bottom: 1px solid var(--clinic-line);
        background: transparent;
    }
    .clinic-settings-page .settings-panel-head > div {
        display: flex;
        align-items: center;
        gap: 9px;
    }
    .clinic-settings-page .settings-panel-head h3 {
        margin: 0;
        color: var(--clinic-maroon);
        font-size: 14px;
        font-weight: 900;
    }
    .clinic-settings-page .settings-panel-head h3 {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .clinic-settings-page .settings-panel-head h3 svg {
        width: 24px;
        height: 24px;
        flex: 0 0 auto;
        padding: 5px;
        border-radius: 7px;
        background: #fff1f2;
        color: var(--clinic-maroon);
    }
    .clinic-settings-page .settings-panel-head p { display: none; }
    .clinic-settings-page .settings-edit-btn {
        width: 34px;
        height: 34px;
        min-height: 34px;
        padding: 0;
        border-radius: 7px;
        background: #fff !important;
        color: var(--clinic-maroon) !important;
        border: 1px solid rgba(127, 0, 16, .25);
    }
    .clinic-settings-page .settings-edit-btn span { display: none; }
    .clinic-settings-page .settings-edit-btn svg { width: 16px; height: 16px; }
    .clinic-settings-page .settings-panel-body { padding: 0; }
    .clinic-settings-page .settings-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0;
        padding: 0 12px;
        border-top: 0;
    }
    .clinic-settings-page .settings-field,
    .clinic-settings-page .settings-days-field {
        position: relative;
        display: grid;
        align-content: center;
        gap: 4px;
        min-width: 0;
        min-height: 60px;
        padding: 7px 12px;
    }
    .clinic-settings-page .settings-field.full,
    .clinic-settings-page .settings-days-field { grid-column: 1 / -1; }
    .clinic-settings-page .settings-field:not(.full):not(:nth-child(2n))::after {
        content: "";
        position: absolute;
        top: 14px;
        right: 0;
        bottom: 14px;
        width: 1px;
        background: var(--clinic-line);
    }
    .clinic-settings-page .settings-field.full + .settings-field.full::before,
    .clinic-settings-page .settings-days-field::before {
        content: "";
        position: absolute;
        top: 0;
        left: 10px;
        right: 10px;
        border-top: 1px solid var(--clinic-line);
    }
    .clinic-settings-page .settings-field label,
    .clinic-settings-page .settings-days-label {
        color: var(--clinic-text);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0;
        text-transform: none;
    }
    .clinic-settings-page .settings-field input {
        width: 100%;
        min-height: 28px;
        padding: 0 !important;
        border: 0 !important;
        border-radius: 0 !important;
        background: transparent !important;
        color: var(--clinic-text);
        font-size: 14px;
        font-weight: 600;
        box-shadow: none !important;
    }
    .clinic-settings-page .settings-editable-form.is-editing .settings-field input {
        border-bottom: 1px solid rgba(127, 0, 16, .28) !important;
    }
    .clinic-settings-page .settings-editable-form.is-editing .settings-field input:focus {
        border-bottom-color: var(--clinic-maroon) !important;
        outline: none;
    }
    .clinic-settings-page .settings-days-field { gap: 8px; }
    .clinic-settings-page .settings-days-grid { padding-bottom: 7px; }
    .clinic-settings-page .settings-day-option span {
        min-height: 34px;
        border-radius: 7px;
        font-size: 12px;
    }
    .clinic-settings-page .settings-action-row {
        margin: 0;
        padding: 10px 12px 12px;
        border-top: 1px solid var(--clinic-line);
    }
    .clinic-settings-page .settings-save-btn {
        background-color: var(--clinic-maroon) !important;
        color: #ffffff !important;
    }
    .clinic-settings-page .settings-save-btn::before {
        background: rgba(255, 248, 205, .72);
        transform: translateX(-110%);
    }
    .clinic-settings-page .settings-save-btn:hover,
    .clinic-settings-page .settings-save-btn:focus-visible {
        background-color: #facc15 !important;
        color: var(--clinic-maroon) !important;
        outline: none;
    }
    .clinic-settings-page .settings-save-btn:hover::before,
    .clinic-settings-page .settings-save-btn:focus-visible::before {
        animation: clinicSaveSweep .62s ease both;
    }
    @keyframes clinicSaveSweep {
        from { transform: translateX(-110%); }
        to { transform: translateX(110%); }
    }
    .clinic-settings-page .settings-cancel-btn:hover,
    .clinic-settings-page .settings-cancel-btn:focus-visible {
        background-color: #4b5563 !important;
        color: #ffffff !important;
    }
    .clinic-settings-page .settings-cancel-btn:hover,
    .clinic-settings-page .settings-cancel-btn:focus-visible {
        background-color: #4b5563 !important;
        color: #ffffff !important;
    }
    html[data-theme="dark"] .clinic-settings-page .settings-section-title > span:not(.clinic-title-icon),
    html[data-theme="dark"] .clinic-settings-page .settings-panel-head h3,
    html[data-theme="dark"] .clinic-settings-page .settings-field label,
    html[data-theme="dark"] .clinic-settings-page .settings-days-label,
    html[data-theme="dark"] .clinic-settings-page .settings-field input { color: #f8fafc; }
    html[data-theme="dark"] .clinic-settings-page .clinic-title-icon,
    html[data-theme="dark"] .clinic-settings-page .settings-panel-head h3 svg {
        background: rgba(127, 0, 16, .35);
        color: #fecdd3;
    }
    html[data-theme="dark"] .clinic-settings-page .settings-panel {
        background: rgba(15, 23, 42, .94);
        border-color: rgba(148, 163, 184, .2);
    }
    html[data-theme="dark"] .clinic-settings-page .settings-edit-btn {
        background: rgba(15, 23, 42, .7) !important;
        color: #fecdd3 !important;
        border-color: rgba(250, 204, 21, .45);
    }
    html[data-theme="dark"] .clinic-settings-page .settings-section-hero,
    html[data-theme="dark"] .clinic-settings-page .settings-panel {
        box-shadow: 0 12px 28px rgba(0, 0, 0, .2);
    }
    html[data-theme="dark"] .clinic-settings-page .settings-section-hero p { color: #cbd5e1; }
    html[data-theme="dark"] .clinic-settings-page .settings-field,
    html[data-theme="dark"] .clinic-settings-page .settings-form-grid,
    html[data-theme="dark"] .clinic-settings-page .settings-action-row { border-color: rgba(148, 163, 184, .16); }
    @media (max-width: 560px) {
        .clinic-settings-page .settings-section-hero > div:first-child { grid-template-columns: 44px minmax(0, 1fr); }
        .clinic-settings-page .clinic-title-icon { width: 44px; height: 44px; }
        .clinic-settings-page .clinic-title-icon svg { width: 24px; height: 24px; }
        .clinic-settings-page .settings-section-title > span:not(.clinic-title-icon) { font-size: 22px; }
        .clinic-settings-page .settings-form-grid { grid-template-columns: 1fr; }
        .clinic-settings-page .settings-field:not(.full):not(:nth-child(2n))::after { display: none; }
        .clinic-settings-page .settings-field.full + .settings-field.full::before,
        .clinic-settings-page .settings-days-field::before { left: 10px; right: 10px; }
    }
</style>
@endpush

@section('content')
<div class="settings-section-page clinic-settings-page">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error">
            <ul style="margin:0; padding-left:18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="settings-section-hero">
        <div>
            <h1 class="settings-section-title"><span class="clinic-title-icon"><x-outline-icon name="home" /></span><span>Clinic Information</span></h1>
            <p>Manage the clinic identity, location, operating hours, and service information shown across the system.</p>
        </div>
        <a href="{{ route('admin.settings') }}" class="settings-back-link"><x-outline-icon name="chevron-right" /> Settings Hub</a>
    </section>

    <div class="settings-section-grid two">
        <section class="settings-panel">
            <div class="settings-panel-head">
                <div>
                    <h3><x-outline-icon name="home" />Clinic Profile</h3>
                    <p>Core clinic identity details.</p>
                </div>
                <button type="button" class="settings-edit-btn" data-edit-trigger data-edit-target="clinicProfileForm" aria-label="Edit clinic profile" title="Edit clinic profile">
                    <x-outline-icon name="pencil-square" />
                </button>
            </div>
            <div class="settings-panel-body">
                <form id="clinicProfileForm" action="{{ route('admin.settings.update') }}" method="POST" class="settings-editable-form">
                    @csrf
                    @method('PUT')
                    <div class="settings-form-grid">
                        <div class="settings-field full">
                            <label for="clinic_name">Clinic Name</label>
                            <input id="clinic_name" name="clinic_name" value="{{ old('clinic_name', $settings->clinic_name) }}" placeholder="PUP Taguig Clinic" disabled data-edit-field>
                        </div>
                        <div class="settings-field full">
                            <label for="clinic_location">Location</label>
                            <input id="clinic_location" name="clinic_location" value="{{ old('clinic_location', $settings->clinic_location) }}" placeholder="Santos Ave, Lower Bicutan, Taguig" disabled data-edit-field>
                        </div>
                    </div>
                    <div class="settings-action-row">
                        <span class="settings-edit-actions">
                            <button type="button" class="settings-cancel-btn" data-edit-cancel><span>Cancel</span></button>
                            <button type="submit" class="settings-save-btn"><x-outline-icon name="check" /> <span>Save</span></button>
                        </span>
                    </div>
                </form>
            </div>
        </section>

        <section class="settings-panel">
            <div class="settings-panel-head">
                <div>
                    <h3><x-outline-icon name="clock" />Clinic Hours</h3>
                    <p>Choose the operating days and opening hours used across the clinic system.</p>
                </div>
                <button type="button" class="settings-edit-btn" data-edit-trigger data-edit-target="clinicHoursForm" aria-label="Edit clinic hours" title="Edit clinic hours">
                    <x-outline-icon name="pencil-square" />
                </button>
            </div>
            <div class="settings-panel-body">
                <form id="clinicHoursForm" action="{{ route('admin.settings.update') }}" method="POST" class="settings-editable-form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="clinic_hours_form" value="1">
                    @php
                        $selectedOperatingDays = collect(old('operating_days', $settings->operating_days ?: [1, 2, 3, 4, 5]))
                            ->map(fn ($day) => (int) $day)
                            ->all();
                        $operatingDayOptions = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'];
                    @endphp
                    <div class="settings-form-grid">
                        <div class="settings-field">
                            <label for="open_time">Opening Time</label>
                            <input id="open_time" name="open_time" type="time" value="{{ old('open_time', substr((string) ($settings->open_time ?: '08:00'), 0, 5)) }}" disabled data-edit-field>
                        </div>
                        <div class="settings-field">
                            <label for="close_time">Closing Time</label>
                            <input id="close_time" name="close_time" type="time" value="{{ old('close_time', substr((string) ($settings->close_time ?: '17:00'), 0, 5)) }}" disabled data-edit-field>
                        </div>
                        <div class="settings-days-field">
                            <span class="settings-days-label">Operating Days</span>
                            <div class="settings-days-grid">
                                @foreach($operatingDayOptions as $dayValue => $dayLabel)
                                    <label class="settings-day-option">
                                        <input type="checkbox" name="operating_days[]" value="{{ $dayValue }}" {{ in_array($dayValue, $selectedOperatingDays, true) ? 'checked' : '' }} disabled data-edit-field>
                                        <span>{{ $dayLabel }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="settings-action-row">
                        <span class="settings-edit-actions">
                            <button type="button" class="settings-cancel-btn" data-edit-cancel><span>Cancel</span></button>
                            <button type="submit" class="settings-save-btn"><x-outline-icon name="check" /> <span>Save</span></button>
                        </span>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>
@include('admin.partials.settings-edit-script')
@endsection
