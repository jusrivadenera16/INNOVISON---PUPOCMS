@extends('layouts.admin')

@section('title', 'System Preferences')

@push('styles')
@include('admin.partials.settings-section-style')
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
            <p>Configure workflow behavior for notifications, appointment approvals, assistant access hours, reminders, and clinic closures.</p>
        </div>
        <a href="{{ route('admin.settings') }}" class="settings-back-link"><x-outline-icon name="chevron-right" /> Settings Hub</a>
    </section>

    <section class="settings-panel">
        <div class="settings-panel-head">
            <div>
                <h3>Workflow Settings</h3>
                <p>These controls use the existing system settings update workflow.</p>
            </div>
        </div>
        <div class="settings-panel-body">
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="preferences_form" value="1">

                <div class="settings-form-grid">
                    <div class="settings-field">
                        <label for="admin_live_notifications">Admin Notifications</label>
                        <select id="admin_live_notifications" name="admin_live_notifications">
                            <option value="1" {{ old('admin_live_notifications', $settings->admin_live_notifications !== false ? '1' : '0') == '1' ? 'selected' : '' }}>Enabled</option>
                            <option value="0" {{ old('admin_live_notifications', $settings->admin_live_notifications !== false ? '1' : '0') == '0' ? 'selected' : '' }}>Disabled</option>
                        </select>
                    </div>
                    <div class="settings-field">
                        <label for="auto_approve">Auto Approve Appointments</label>
                        <select id="auto_approve" name="auto_approve">
                            <option value="0" {{ old('auto_approve', $settings->auto_approve ? '1' : '0') == '0' ? 'selected' : '' }}>Disabled</option>
                            <option value="1" {{ old('auto_approve', $settings->auto_approve ? '1' : '0') == '1' ? 'selected' : '' }}>Enabled</option>
                        </select>
                    </div>
                    <div class="settings-field">
                        <label for="student_assistant_open_time">Assistant Open Time</label>
                        <input id="student_assistant_open_time" name="student_assistant_open_time" type="time" value="{{ old('student_assistant_open_time', substr((string) ($settings->student_assistant_open_time ?: '08:00'), 0, 5)) }}" required>
                    </div>
                    <div class="settings-field">
                        <label for="student_assistant_close_time">Assistant Close Time</label>
                        <input id="student_assistant_close_time" name="student_assistant_close_time" type="time" value="{{ old('student_assistant_close_time', substr((string) ($settings->student_assistant_close_time ?: '20:00'), 0, 5)) }}" required>
                    </div>
                    <div class="settings-field">
                        <label for="appointment_reminder_hours">Appointment Reminder</label>
                        <select id="appointment_reminder_hours" name="appointment_reminder_hours" required>
                            @foreach($reminderOptions as $hours => $label)
                                <option value="{{ $hours }}" {{ (int) old('appointment_reminder_hours', $settings->appointment_reminder_hours ?? 24) === $hours ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="settings-field">
                        <label for="clinic_closure_enabled">Clinic Closure</label>
                        <select id="clinic_closure_enabled" name="clinic_closure_enabled">
                            <option value="0" {{ old('clinic_closure_enabled', $settings->clinic_closure_enabled ? '1' : '0') == '0' ? 'selected' : '' }}>Available</option>
                            <option value="1" {{ old('clinic_closure_enabled', $settings->clinic_closure_enabled ? '1' : '0') == '1' ? 'selected' : '' }}>Temporarily Closed</option>
                        </select>
                    </div>
                    <div class="settings-field">
                        <label for="clinic_closure_starts_at">Closure Starts</label>
                        <input id="clinic_closure_starts_at" name="clinic_closure_starts_at" type="datetime-local" value="{{ old('clinic_closure_starts_at', optional($settings->clinic_closure_starts_at)->format('Y-m-d\\TH:i')) }}">
                    </div>
                    <div class="settings-field">
                        <label for="clinic_closure_ends_at">Closure Ends</label>
                        <input id="clinic_closure_ends_at" name="clinic_closure_ends_at" type="datetime-local" value="{{ old('clinic_closure_ends_at', optional($settings->clinic_closure_ends_at)->format('Y-m-d\\TH:i')) }}">
                    </div>
                    <div class="settings-field full">
                        <label for="clinic_closure_reason">Closure Reason</label>
                        <input id="clinic_closure_reason" name="clinic_closure_reason" maxlength="100" value="{{ old('clinic_closure_reason', $settings->clinic_closure_reason) }}">
                    </div>
                    <div class="settings-field full">
                        <label for="clinic_closure_message">Closure Message</label>
                        <textarea id="clinic_closure_message" name="clinic_closure_message" maxlength="500">{{ old('clinic_closure_message', $settings->clinic_closure_message) }}</textarea>
                    </div>
                </div>
                <div class="settings-action-row">
                    <button type="submit" class="settings-save-btn"><x-outline-icon name="check" /> Save System Preferences</button>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection
