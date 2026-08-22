@extends('layouts.admin')

@section('title', 'Personal Information')

@push('styles')
@include('admin.partials.settings-section-style')
<style>
    .personal-settings-page {
        --personal-maroon: #7f0010;
        --personal-text: #172033;
        --personal-muted: #64748b;
        --personal-line: rgba(148, 163, 184, .22);
        display: grid;
        gap: 12px;
    }

    .personal-settings-page .settings-section-hero {
        align-items: center;
        margin-bottom: 0;
        padding: 14px 16px;
        border-radius: 12px;
        border-color: var(--personal-line);
        box-shadow: 0 8px 22px rgba(15, 23, 42, .05);
    }

    .personal-settings-page .settings-section-title { font-size: 26px; }
    .personal-settings-page .settings-section-title > svg {
        width: 44px;
        height: 44px;
        padding: 10px;
        border-radius: 12px;
    }
    .personal-settings-page .settings-section-hero p {
        margin-top: 4px;
        font-size: 12px;
        line-height: 1.45;
    }
    .personal-settings-page .settings-back-link {
        min-height: 34px;
        border-radius: 8px;
        padding: 0 12px;
    }
    .personal-settings-page .settings-back-link svg { transform: rotate(180deg); }

    .personal-settings-form { display: grid; gap: 12px; }

    .personal-profile-summary,
    .personal-info-section,
    .personal-note {
        border: 1px solid var(--personal-line);
        border-radius: 12px;
        background: rgba(255, 255, 255, .96);
        box-shadow: 0 8px 22px rgba(15, 23, 42, .045);
    }

    .personal-profile-summary {
        display: flex;
        align-items: center;
        gap: 14px;
        min-height: 88px;
        padding: 14px 16px;
    }
    .personal-avatar {
        width: 60px;
        height: 60px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        border-radius: 50%;
        background: var(--personal-maroon);
        color: #fff;
        font-size: 20px;
        font-weight: 900;
    }
    .personal-profile-copy { min-width: 0; flex: 1 1 auto; }
    .personal-profile-copy h2 {
        margin: 0;
        color: var(--personal-text);
        font-size: 17px;
        font-weight: 900;
    }
    .personal-profile-meta,
    .personal-profile-email {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 5px;
        color: var(--personal-muted);
        font-size: 12px;
    }
    .personal-profile-meta .personal-role {
        padding: 3px 8px;
        border-radius: 6px;
        background: #fee2e2;
        color: var(--personal-maroon);
        font-size: 11px;
        font-weight: 800;
    }
    .personal-profile-meta .personal-dot {
        width: 3px;
        height: 3px;
        border-radius: 50%;
        background: #94a3b8;
    }
    .personal-profile-email svg { width: 14px; height: 14px; color: #475569; }

    .personal-edit-button {
        flex: 0 0 auto;
        min-height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 0 13px;
        border: 1px solid rgba(127, 0, 16, .42);
        border-radius: 7px;
        background: #fff;
        color: var(--personal-maroon);
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }
    .personal-edit-button::before {
        content: "";
        position: absolute;
        inset: 0;
        background: #facc15;
        transform: translateX(-102%);
        transition: transform .46s ease;
        z-index: 0;
    }
    .personal-edit-button > * { position: relative; z-index: 1; }
    .personal-edit-button svg { width: 15px; height: 15px; }
    .personal-edit-button:hover,
    .personal-edit-button:focus-visible {
        border-color: #facc15;
        color: var(--personal-maroon);
        outline: none;
    }
    .personal-edit-button:hover::before,
    .personal-edit-button:focus-visible::before { transform: translateX(0); }

    .personal-info-section { overflow: hidden; }
    .personal-section-heading {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 11px 12px;
        color: var(--personal-maroon);
        font-size: 13px;
        font-weight: 900;
    }
    .personal-section-heading svg {
        width: 24px;
        height: 24px;
        padding: 5px;
        border-radius: 7px;
        background: #fff1f2;
        color: var(--personal-maroon);
    }
    .personal-fields-grid {
        display: grid;
        border-top: 1px solid var(--personal-line);
        padding: 0 12px;
    }
    .personal-fields-grid.basic { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .personal-fields-grid.contact,
    .personal-fields-grid.emergency { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .personal-fields-grid.basic,
    .personal-fields-grid.contact { position: relative; }
    .personal-fields-grid.basic::before,
    .personal-fields-grid.contact::before {
        content: "";
        position: absolute;
        top: 50%;
        left: 12px;
        right: 12px;
        border-top: 1px solid var(--personal-line);
    }
    .personal-field {
        display: grid;
        align-content: center;
        gap: 4px;
        min-width: 0;
        min-height: 60px;
        padding: 7px 12px;
        position: relative;
        border: 0;
    }
    .personal-field.full { grid-column: 1 / -1; }

    .personal-fields-grid.basic .personal-field:not(:nth-child(4n))::after,
    .personal-fields-grid.contact .personal-field:not(:nth-child(2n))::after,
    .personal-fields-grid.emergency .personal-field:not(:nth-child(2n))::after {
        content: "";
        position: absolute;
        top: 14px;
        right: 0;
        bottom: 14px;
        width: 1px;
        background: var(--personal-line);
    }

    .personal-field label {
        color: var(--personal-text);
        font-size: 11px;
        font-weight: 700;
    }
    .personal-field input {
        width: 100%;
        min-height: 28px;
        padding: 0;
        border: 1px solid transparent;
        border-radius: 7px;
        background: transparent;
        color: var(--personal-text);
        font: inherit;
        font-size: 13px;
        font-weight: 600;
    }
    .personal-field input:disabled {
        color: var(--personal-text);
        background: transparent;
        border-color: transparent;
        opacity: 1;
    }

    .personal-settings-form:not(.is-editing) .personal-field input,
    .personal-settings-form:not(.is-editing) .personal-field input:disabled {
        min-height: 28px;
        padding: 0 !important;
        border: 0 !important;
        border-radius: 0;
        background: transparent !important;
        box-shadow: none !important;
    }
    .personal-field input::placeholder { color: #475569; opacity: 1; }
    .personal-field input:focus {
        outline: none;
        border-color: var(--personal-maroon);
        background: #fff;
        padding: 0 8px;
        box-shadow: 0 0 0 3px rgba(127, 0, 16, .08);
    }
    .personal-settings-form.is-editing .personal-field input:not([readonly]) {
        border: 0 !important;
        border-bottom: 1px solid rgba(127, 0, 16, .28) !important;
        border-radius: 0 !important;
        background: transparent !important;
        padding: 0 !important;
        box-shadow: none !important;
    }
    .personal-settings-form.is-editing .personal-field input:not([readonly]):focus {
        border: 0 !important;
        border-bottom: 1px solid var(--personal-maroon) !important;
        border-radius: 0 !important;
        background: transparent !important;
        padding: 0 !important;
        box-shadow: none !important;
    }
    .personal-settings-form.is-editing .personal-field input:disabled {
        border-color: transparent;
        background: transparent;
        padding: 0;
    }
    .personal-settings-form.is-editing .personal-field input[readonly] {
        border: 0;
        background: transparent;
        padding: 0;
        box-shadow: none;
        cursor: default;
    }
    .personal-settings-page .settings-save-btn {
        background-color: #7f0010 !important;
        color: #ffffff !important;
    }
    .personal-settings-page .settings-save-btn:hover,
    .personal-settings-page .settings-save-btn:focus-visible {
        background-color: #7f0010 !important;
        color: #7f0010 !important;
        outline: none;
    }

    .personal-note {
        display: flex;
        align-items: center;
        gap: 8px;
        min-height: 28px;
        padding: 6px 10px;
        color: var(--personal-muted);
        font-size: 10px;
    }
    .personal-note svg { width: 13px; height: 13px; flex: 0 0 auto; }
    .personal-settings-form .settings-action-row { margin-top: 0; }

    html[data-theme="dark"] .personal-settings-page .settings-section-hero,
    html[data-theme="dark"] .personal-settings-page .personal-profile-summary,
    html[data-theme="dark"] .personal-settings-page .personal-info-section,
    html[data-theme="dark"] .personal-settings-page .personal-note {
        background: rgba(15, 23, 42, .94);
        border-color: rgba(148, 163, 184, .2);
        box-shadow: 0 12px 28px rgba(0, 0, 0, .2);
    }
    html[data-theme="dark"] .personal-settings-page .settings-section-title,
    html[data-theme="dark"] .personal-settings-page .personal-profile-copy h2,
    html[data-theme="dark"] .personal-settings-page .personal-field label,
    html[data-theme="dark"] .personal-settings-page .personal-field input { color: #f8fafc; }
    html[data-theme="dark"] .personal-settings-page .settings-section-hero p,
    html[data-theme="dark"] .personal-settings-page .personal-profile-meta,
    html[data-theme="dark"] .personal-settings-page .personal-profile-email,
    html[data-theme="dark"] .personal-settings-page .personal-note,
    html[data-theme="dark"] .personal-settings-page .personal-field input::placeholder { color: #cbd5e1; }
    html[data-theme="dark"] .personal-settings-page .personal-section-heading svg {
        background: rgba(127, 0, 16, .35);
        color: #fecdd3;
    }
    html[data-theme="dark"] .personal-settings-page .personal-field,
    html[data-theme="dark"] .personal-settings-page .personal-fields-grid { border-color: rgba(148, 163, 184, .16); }
    html[data-theme="dark"] .personal-settings-page .personal-edit-button {
        background: rgba(15, 23, 42, .7);
        color: #fecdd3;
        border-color: rgba(250, 204, 21, .45);
    }

    @media (max-width: 850px) {
        .personal-settings-page .settings-section-hero,
        .personal-profile-summary { align-items: flex-start; flex-wrap: wrap; }
        .personal-fields-grid.basic { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .personal-fields-grid.basic::before { display: none; }
        .personal-fields-grid.basic .personal-field:nth-child(2n + 3)::before {
            content: "";
            position: absolute;
            top: 0;
            left: 10px;
            right: calc(-100% + 10px);
            border-top: 1px solid var(--personal-line);
        }
        .personal-fields-grid.basic .personal-field:nth-child(2n)::after { display: none; }
    }
    @media (max-width: 560px) {
        .personal-settings-page .settings-section-hero,
        .personal-profile-summary { display: grid; }
        .personal-profile-summary { grid-template-columns: auto 1fr; }
        .personal-edit-button { grid-column: 1 / -1; width: 100%; }
        .personal-fields-grid.basic,
        .personal-fields-grid.contact,
        .personal-fields-grid.emergency { grid-template-columns: 1fr; }
        .personal-fields-grid.basic .personal-field::after,
        .personal-fields-grid.contact .personal-field::after,
        .personal-fields-grid.emergency .personal-field::after { display: none; }
        .personal-fields-grid.basic .personal-field:nth-child(2n + 3)::before { display: none; }
        .personal-fields-grid.basic .personal-field:nth-child(n + 2)::before {
            content: "";
            position: absolute;
            top: 0;
            left: 10px;
            right: 10px;
            border-top: 1px solid var(--personal-line);
        }
    }
</style>
@endpush

@section('content')
@php
    $profileName = trim((string) ($cmsProfile['name'] ?? $admin->name ?? '')) ?: 'Administrator';
    $profileParts = preg_split('/\s+/', $profileName, -1, PREG_SPLIT_NO_EMPTY);
    $profileInitials = strtoupper(substr((string) ($profileParts[0] ?? 'A'), 0, 1) . substr((string) ($profileParts[count($profileParts) - 1] ?? ''), 0, 1));
    $profileRole = strtolower(trim((string) ($cmsProfile['role'] ?? $admin->user_role ?? '')));
    $profileRoleLabel = match ($profileRole) {
        'superadmin', 'super_admin' => 'Super Administrator',
        'student_assistant', 'assistant' => 'Student Assistant',
        default => 'Clinic Staff',
    };
    $profileOffice = trim((string) ($cmsProfile['office'] ?? '')) ?: 'Clinic Office';
@endphp

<div class="settings-section-page personal-settings-page">
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
            <h1 class="settings-section-title"><x-outline-icon name="user-circle" />Personal Information</h1>
            <p>View and manage your personal details and account credentials used in the clinic management workspace.</p>
        </div>
        <a href="{{ route('admin.settings') }}" class="settings-back-link"><x-outline-icon name="chevron-right" /> Back to Settings</a>
    </section>

    <form id="personalSettingsForm" action="{{ route('admin.profile.update') }}" method="POST" class="settings-editable-form personal-settings-form">
        @csrf
        @method('PUT')

        <section class="personal-profile-summary">
            <div class="personal-avatar">{{ $profileInitials }}</div>
            <div class="personal-profile-copy">
                <h2>{{ $profileName }}</h2>
                <div class="personal-profile-meta">
                    <span class="personal-role">{{ $profileRoleLabel }}</span>
                    <span class="personal-dot"></span>
                    <span>{{ $profileOffice }}</span>
                </div>
                <div class="personal-profile-email">
                    <x-outline-icon name="envelope" />
                    <span>{{ $cmsProfile['email'] ?? $admin->email ?? '—' }}</span>
                </div>
            </div>
            <button type="button" class="personal-edit-button" data-edit-trigger data-edit-target="personalSettingsForm">
                <x-outline-icon name="pencil-square" />
                <span>Edit Information</span>
            </button>
        </section>

        <section class="personal-info-section">
            <div class="personal-section-heading"><x-outline-icon name="user-circle" /><span>Basic Information</span></div>
            <div class="personal-fields-grid basic">
                <div class="personal-field">
                    <label for="first_name">First Name</label>
                    <input id="first_name" name="first_name" value="{{ old('first_name', $cmsProfile['first_name'] ?? $admin->first_name ?? '') }}" placeholder="—" required readonly>
                </div>
                <div class="personal-field">
                    <label for="last_name">Last Name</label>
                    <input id="last_name" name="last_name" value="{{ old('last_name', $cmsProfile['last_name'] ?? $admin->last_name ?? '') }}" placeholder="—" required readonly>
                </div>
                <div class="personal-field">
                    <label for="middle_name">Middle Name</label>
                    <input id="middle_name" name="middle_name" value="{{ old('middle_name', $cmsProfile['middle_name'] ?? $admin->middle_name ?? '') }}" placeholder="—" readonly>
                </div>
                <div class="personal-field">
                    <label for="suffix_name">Suffix</label>
                    <input id="suffix_name" name="suffix_name" value="{{ old('suffix_name', $cmsProfile['suffix_name'] ?? $admin->suffix_name ?? '') }}" placeholder="—" readonly>
                </div>
                <div class="personal-field">
                    <label for="birthday">Birthday</label>
                    <input id="birthday" name="birthday" type="date" value="{{ old('birthday', $cmsProfile['birthday'] ?? '') }}" placeholder="—" readonly>
                </div>
                <div class="personal-field">
                    <label for="gender">Gender</label>
                    <input id="gender" name="gender" value="{{ old('gender', $cmsProfile['gender'] ?? '') }}" placeholder="—" readonly>
                </div>
                <div class="personal-field">
                    <label for="civil_status">Civil Status</label>
                    <input id="civil_status" name="civil_status" value="{{ old('civil_status', $cmsProfile['civil_status'] ?? '') }}" placeholder="—" disabled data-edit-field>
                </div>
                <div class="personal-field">
                    <label for="office">Office</label>
                    <input id="office" name="office" value="{{ old('office', $cmsProfile['office'] ?? '') }}" placeholder="{{ $profileOffice }}" disabled data-edit-field>
                </div>
            </div>
        </section>

        <section class="personal-info-section">
            <div class="personal-section-heading"><x-outline-icon name="phone" /><span>Contact Information</span></div>
            <div class="personal-fields-grid contact">
                <div class="personal-field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $cmsProfile['email'] ?? $admin->email ?? '') }}" placeholder="—" required readonly>
                </div>
                <div class="personal-field">
                    <label for="contact_number">Contact Number</label>
                    <input id="contact_number" name="contact_number" value="{{ old('contact_number', $cmsProfile['contact_number'] ?? '') }}" placeholder="—" disabled data-edit-field>
                </div>
                <div class="personal-field full">
                    <label for="address">Address</label>
                    <input id="address" name="address" value="{{ old('address', $cmsProfile['address'] ?? '') }}" placeholder="—" disabled data-edit-field>
                </div>
            </div>
        </section>

        <section class="personal-info-section">
            <div class="personal-section-heading"><x-outline-icon name="shield-check" /><span>Emergency Contact</span></div>
            <div class="personal-fields-grid emergency">
                <div class="personal-field">
                    <label for="emergency_contact_person">Contact Person</label>
                    <input id="emergency_contact_person" name="emergency_contact_person" value="{{ old('emergency_contact_person', $cmsProfile['emergency_contact_person'] ?? '') }}" placeholder="—" disabled data-edit-field>
                </div>
                <div class="personal-field">
                    <label for="emergency_contact_no">Contact Number</label>
                    <input id="emergency_contact_no" name="emergency_contact_no" value="{{ old('emergency_contact_no', $cmsProfile['emergency_contact_no'] ?? '') }}" placeholder="—" disabled data-edit-field>
                </div>
            </div>
        </section>

        <input type="hidden" name="role" value="{{ old('role', $cmsProfile['role'] ?? $admin->user_role ?? 'superadmin') }}">
        <input type="hidden" name="status" value="{{ old('status', $cmsProfile['status'] ?? $admin->status ?? 'active') }}">
        <div class="settings-action-row">
            <span class="settings-edit-actions">
                <button type="button" class="settings-cancel-btn" data-edit-cancel><span>Cancel</span></button>
                <button type="submit" class="settings-save-btn"><x-outline-icon name="check" /> <span>Save</span></button>
            </span>
        </div>
    </form>

    <div class="personal-note"><x-outline-icon name="information-circle" /> <span>Keep your personal information up to date to ensure accurate and secure communication within the clinic management system.</span></div>
</div>

@include('admin.partials.settings-edit-script')
@endsection
