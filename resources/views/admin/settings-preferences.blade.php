@extends('layouts.admin')

@section('title', 'System Preferences')

@push('styles')
@include('admin.partials.settings-section-style')
<style>
    .settings-schedule-note {
        grid-column: 1 / -1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        min-height: 54px;
        padding: 12px 14px;
        border: 1px solid rgba(250, 204, 21, .34);
        border-radius: 10px;
        background: rgba(127, 0, 0, .035);
        color: #334155;
    }
    .settings-schedule-note strong { color: #7f0000; }
    .settings-schedule-note a {
        flex: 0 0 auto;
        color: #970014;
        font-size: 12px;
        font-weight: 850;
        text-decoration: none;
    }
    html[data-theme="dark"] .settings-schedule-note {
        background: rgba(15, 23, 42, .7);
        color: #e2e8f0;
    }
    html[data-theme="dark"] .settings-schedule-note strong,
    html[data-theme="dark"] .settings-schedule-note a { color: #facc15; }
    @media (max-width: 640px) {
        .settings-schedule-note { align-items: flex-start; flex-direction: column; }
    }
</style>
@endpush

@section('content')
@php
    $reminderOptions = [0 => 'Disabled', 1 => '1 hour before', 3 => '3 hours before', 24 => '1 day before', 48 => '2 days before'];
@endphp
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
            <h1 class="settings-section-title"><x-outline-icon name="code-bracket-square" />System Preferences</h1>
            <p>Configure workflow behavior for notifications, appointment approvals, reminders, and clinic closures.</p>
        </div>
        <a href="{{ route('admin.settings') }}" class="settings-back-link"><x-outline-icon name="chevron-right" /> Settings Hub</a>
    </section>

    <section class="settings-panel">
        <div class="settings-panel-head">
            <div>
                <h3>Workflow Settings</h3>
                <p>These controls use the existing system settings update workflow.</p>
            </div>
            <button type="button" class="settings-edit-btn" data-edit-target="systemPreferencesForm">
                <x-outline-icon name="pencil-square" />
                <span>Edit</span>
            </button>
        </div>
        <div class="settings-panel-body">
            <form id="systemPreferencesForm" action="{{ route('admin.settings.update') }}" method="POST" class="settings-editable-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="preferences_form" value="1">

                <div class="settings-form-grid">
                    <div class="settings-field">
                        <label for="admin_live_notifications">Admin Notifications</label>
                        <select id="admin_live_notifications" name="admin_live_notifications" disabled data-edit-field>
                            <option value="1" {{ old('admin_live_notifications', $settings->admin_live_notifications !== false ? '1' : '0') == '1' ? 'selected' : '' }}>Enabled</option>
                            <option value="0" {{ old('admin_live_notifications', $settings->admin_live_notifications !== false ? '1' : '0') == '0' ? 'selected' : '' }}>Disabled</option>
                        </select>
                    </div>
                    <div class="settings-field">
                        <label for="auto_approve">Auto Approve Appointments</label>
                        <select id="auto_approve" name="auto_approve" disabled data-edit-field>
                            <option value="0" {{ old('auto_approve', $settings->auto_approve ? '1' : '0') == '0' ? 'selected' : '' }}>Disabled</option>
                            <option value="1" {{ old('auto_approve', $settings->auto_approve ? '1' : '0') == '1' ? 'selected' : '' }}>Enabled</option>
                        </select>
                    </div>
                    <div class="settings-schedule-note">
                        <span>Assistant Admin Workspace follows <strong>{{ app(\App\Services\ClinicWorkflowService::class)->clinicScheduleLabel() }}</strong>.</span>
                        <a href="{{ route('admin.settings.clinic') }}">Manage clinic schedule</a>
                    </div>
                    <div class="settings-field">
                        <label for="appointment_reminder_hours">Appointment Reminder</label>
                        <select id="appointment_reminder_hours" name="appointment_reminder_hours" required disabled data-edit-field>
                            @foreach($reminderOptions as $hours => $label)
                                <option value="{{ $hours }}" {{ (int) old('appointment_reminder_hours', $settings->appointment_reminder_hours ?? 24) === $hours ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="settings-field">
                        <label for="clinic_closure_enabled">Clinic Closure</label>
                        <select id="clinic_closure_enabled" name="clinic_closure_enabled" disabled data-edit-field>
                            <option value="0" {{ old('clinic_closure_enabled', $settings->clinic_closure_enabled ? '1' : '0') == '0' ? 'selected' : '' }}>Available</option>
                            <option value="1" {{ old('clinic_closure_enabled', $settings->clinic_closure_enabled ? '1' : '0') == '1' ? 'selected' : '' }}>Temporarily Closed</option>
                        </select>
                    </div>
                    <div class="settings-field">
                        <label for="clinic_closure_starts_at">Closure Starts</label>
                        <input id="clinic_closure_starts_at" name="clinic_closure_starts_at" type="datetime-local" value="{{ old('clinic_closure_starts_at', optional($settings->clinic_closure_starts_at)->format('Y-m-d\\TH:i')) }}" disabled data-edit-field>
                    </div>
                    <div class="settings-field">
                        <label for="clinic_closure_ends_at">Closure Ends</label>
                        <input id="clinic_closure_ends_at" name="clinic_closure_ends_at" type="datetime-local" value="{{ old('clinic_closure_ends_at', optional($settings->clinic_closure_ends_at)->format('Y-m-d\\TH:i')) }}" disabled data-edit-field>
                    </div>
                    <div class="settings-field full">
                        <label for="clinic_closure_reason">Closure Reason</label>
                        <select id="clinic_closure_reason" name="clinic_closure_reason" disabled data-edit-field>
                            @foreach(['Staff Meeting', 'Official Clinic Activity', 'Emergency', 'Early Closure', 'Other'] as $reason)
                                <option value="{{ $reason }}" {{ old('clinic_closure_reason', $settings->clinic_closure_reason) === $reason ? 'selected' : '' }}>{{ $reason }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="settings-field full">
                        <label for="clinic_closure_message">Closure Message</label>
                        <textarea id="clinic_closure_message" name="clinic_closure_message" maxlength="500" disabled data-edit-field>{{ old('clinic_closure_message', $settings->clinic_closure_message) }}</textarea>
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
@include('admin.partials.settings-edit-script')
@endsection
