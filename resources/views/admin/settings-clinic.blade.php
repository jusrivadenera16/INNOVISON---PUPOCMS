@extends('layouts.admin')

@section('title', 'Clinic Information')

@push('styles')
@include('admin.partials.settings-section-style')
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
                            <button type="submit" class="settings-save-btn"><x-outline-icon name="check" /> <span>Save Clinic Profile</span></button>
                        </span>
                    </div>
                </form>
            </div>
        </section>

        <section class="settings-panel">
            <div class="settings-panel-head">
                <div>
                    <h3>Clinic Hours</h3>
                    <p>Daily clinic operating schedule.</p>
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
                    <div class="settings-form-grid">
                        <div class="settings-field">
                            <label for="open_time">Opening Time</label>
                            <input id="open_time" name="open_time" type="time" value="{{ old('open_time', substr((string) ($settings->open_time ?: '08:00'), 0, 5)) }}" disabled data-edit-field>
                        </div>
                        <div class="settings-field">
                            <label for="close_time">Closing Time</label>
                            <input id="close_time" name="close_time" type="time" value="{{ old('close_time', substr((string) ($settings->close_time ?: '17:00'), 0, 5)) }}" disabled data-edit-field>
                        </div>
                    </div>
                    <div class="settings-action-row">
                        <span class="settings-edit-actions">
                            <button type="button" class="settings-cancel-btn" data-edit-cancel><span>Cancel</span></button>
                            <button type="submit" class="settings-save-btn"><x-outline-icon name="check" /> <span>Save Clinic Hours</span></button>
                        </span>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>
@include('admin.partials.settings-edit-script')
@endsection
