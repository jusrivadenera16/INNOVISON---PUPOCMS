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
</style>
@endpush

@section('content')
<div class="settings-section-page">
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
            <h1 class="settings-section-title"><x-outline-icon name="home" />Clinic Information</h1>
            <p>Manage the clinic identity, location, operating hours, and service information shown across the system.</p>
        </div>
        <a href="{{ route('admin.settings') }}" class="settings-back-link"><x-outline-icon name="chevron-right" /> Settings Hub</a>
    </section>

    <div class="settings-section-grid two">
        <section class="settings-panel">
            <div class="settings-panel-head">
                <div>
                    <h3>Clinic Profile</h3>
                    <p>Core clinic identity details.</p>
                </div>
                <button type="button" class="settings-edit-btn" data-edit-target="clinicProfileForm">
                    <x-outline-icon name="pencil-square" />
                    <span>Edit</span>
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
                    <h3>Clinic Hours</h3>
                    <p>Choose the operating days and opening hours used across the clinic system.</p>
                </div>
                <button type="button" class="settings-edit-btn" data-edit-target="clinicHoursForm">
                    <x-outline-icon name="pencil-square" />
                    <span>Edit</span>
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
