@extends('layouts.admin')

@section('title', 'Personal Information')

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
            <h1 class="settings-section-title"><x-outline-icon name="user-circle" />Personal Information</h1>
            <p>Update your profile details and account credentials used by the clinic management workspace.</p>
        </div>
        <a href="{{ route('admin.settings') }}" class="settings-back-link"><x-outline-icon name="chevron-right" /> Settings Hub</a>
    </section>

    <div class="settings-section-grid">
        <section class="settings-panel">
            <div class="settings-panel-head">
                <div>
                    <h3>Profile Details</h3>
                    <p>Changes here update the current admin account profile.</p>
                </div>
            </div>
            <div class="settings-panel-body">
                <form action="{{ route('admin.profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="settings-form-grid">
                        <div class="settings-field">
                            <label for="first_name">First Name</label>
                            <input id="first_name" name="first_name" value="{{ old('first_name', $cmsProfile['first_name'] ?? $admin->first_name ?? '') }}" required>
                        </div>
                        <div class="settings-field">
                            <label for="last_name">Last Name</label>
                            <input id="last_name" name="last_name" value="{{ old('last_name', $cmsProfile['last_name'] ?? $admin->last_name ?? '') }}" required>
                        </div>
                        <div class="settings-field">
                            <label for="middle_name">Middle Name</label>
                            <input id="middle_name" name="middle_name" value="{{ old('middle_name', $cmsProfile['middle_name'] ?? $admin->middle_name ?? '') }}">
                        </div>
                        <div class="settings-field">
                            <label for="suffix_name">Suffix</label>
                            <input id="suffix_name" name="suffix_name" value="{{ old('suffix_name', $cmsProfile['suffix_name'] ?? $admin->suffix_name ?? '') }}">
                        </div>
                        <div class="settings-field">
                            <label for="email">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email', $cmsProfile['email'] ?? $admin->email ?? '') }}" required>
                        </div>
                        <div class="settings-field">
                            <label for="contact_number">Contact Number</label>
                            <input id="contact_number" name="contact_number" value="{{ old('contact_number', $cmsProfile['contact_number'] ?? '') }}">
                        </div>
                        <div class="settings-field">
                            <label for="birthday">Birthday</label>
                            <input id="birthday" name="birthday" type="date" value="{{ old('birthday', $cmsProfile['birthday'] ?? '') }}">
                        </div>
                        <div class="settings-field">
                            <label for="gender">Gender</label>
                            <input id="gender" name="gender" value="{{ old('gender', $cmsProfile['gender'] ?? '') }}">
                        </div>
                        <div class="settings-field">
                            <label for="civil_status">Civil Status</label>
                            <input id="civil_status" name="civil_status" value="{{ old('civil_status', $cmsProfile['civil_status'] ?? '') }}">
                        </div>
                        <div class="settings-field">
                            <label for="office">Office</label>
                            <input id="office" name="office" value="{{ old('office', $cmsProfile['office'] ?? 'Admission Office') }}">
                        </div>
                        <div class="settings-field full">
                            <label for="address">Address</label>
                            <input id="address" name="address" value="{{ old('address', $cmsProfile['address'] ?? '') }}">
                        </div>
                        <div class="settings-field">
                            <label for="emergency_contact_person">Emergency Contact</label>
                            <input id="emergency_contact_person" name="emergency_contact_person" value="{{ old('emergency_contact_person', $cmsProfile['emergency_contact_person'] ?? '') }}">
                        </div>
                        <div class="settings-field">
                            <label for="emergency_contact_no">Emergency Contact No.</label>
                            <input id="emergency_contact_no" name="emergency_contact_no" value="{{ old('emergency_contact_no', $cmsProfile['emergency_contact_no'] ?? '') }}">
                        </div>
                        <div class="settings-field">
                            <label for="password">New Password</label>
                            <input id="password" name="password" type="password" autocomplete="new-password">
                        </div>
                        <div class="settings-field">
                            <label for="password_confirmation">Confirm Password</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password">
                        </div>
                    </div>
                    <input type="hidden" name="role" value="{{ old('role', $cmsProfile['role'] ?? $admin->user_role ?? 'superadmin') }}">
                    <input type="hidden" name="status" value="{{ old('status', $cmsProfile['status'] ?? $admin->status ?? 'active') }}">
                    <div class="settings-action-row">
                        <button type="submit" class="settings-save-btn"><x-outline-icon name="check" /> Save Personal Information</button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>
@endsection
