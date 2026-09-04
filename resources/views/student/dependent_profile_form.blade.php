<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Information Form</title>
    <script
        src="{{ asset('js/sienna-accessibility-custom.umd.js') }}?v={{ filemtime(public_path('js/sienna-accessibility-custom.umd.js')) }}"
        data-asw-position="bottom-right"
        data-asw-offset="24,12"
        data-asw-size="small"
        defer
    ></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --clinic-maroon: #7f1d2d;
            --clinic-maroon-dark: #5f0012;
            --clinic-yellow: #facc15;
            --field: #f8fafc;
            --border: #d1d5db;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
            background:
                linear-gradient(rgba(39, 14, 17, 0.82), rgba(22, 8, 8, 0.84)),
                url('{{ asset('images/PUPBG.jpg') }}') center center / cover no-repeat fixed;
            padding: 28px 12px 120px;
        }

        body.dependent-profile-page .system-footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 100;
            box-shadow: 0 -12px 30px rgba(15, 23, 42, 0.18);
        }

        .profile-shell {
            max-width: 940px;
            margin: 0 auto;
            overflow: hidden;
            border: 1px solid rgba(127, 29, 45, 0.16);
            border-radius: 20px;
            background:
                linear-gradient(rgba(255, 255, 255, .42), rgba(255, 255, 255, .62)),
                url('{{ asset('images/hif_bg.png') }}') center / cover no-repeat,
                rgba(255, 255, 255, 0.97);
            box-shadow: 0 22px 48px rgba(15, 23, 42, 0.22);
        }

        .profile-topbar {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 16px;
            padding: 16px 24px;
            background: linear-gradient(135deg, var(--clinic-maroon) 0%, var(--clinic-maroon-dark) 100%);
            border-bottom: 3px solid var(--clinic-yellow);
            color: #ffffff;
        }

        .profile-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .profile-brand img {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: #ffffff;
            object-fit: contain;
        }

        .profile-brand strong,
        .profile-brand span {
            display: block;
        }

        .profile-brand strong {
            font-size: 14px;
            font-weight: 900;
            line-height: 1.15;
            text-transform: uppercase;
        }

        .profile-brand span {
            display: block;
        }

        .profile-form-title {
            color: #fde68a;
            font-size: 15px;
            font-weight: 900;
            line-height: 1.2;
            text-align: right;
            text-transform: uppercase;
        }

        .profile-brand span {
            margin-top: 3px;
            color: #fde68a;
            font-size: 12px;
            font-weight: 800;
        }

        .profile-body {
            padding: 28px;
        }

        .profile-intro {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 22px;
        }

        .profile-intro-icon {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: #fff7ed;
            border: 1px solid rgba(250, 204, 21, 0.55);
            color: var(--clinic-maroon);
            flex: 0 0 auto;
        }

        .profile-intro-icon svg,
        .btn-health svg {
            width: 20px;
            height: 20px;
        }

        .profile-intro h1 {
            margin: 0;
            color: var(--clinic-maroon);
            font-size: 1.45rem;
            font-weight: 900;
            letter-spacing: 0;
        }

        .profile-intro p {
            margin: 5px 0 0;
            color: #4b5563;
            font-size: 0.95rem;
            line-height: 1.55;
        }

        .profile-section {
            margin-top: 18px;
            padding: 20px;
            border: 1px solid rgba(127, 29, 45, 0.14);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.78);
        }

        .profile-section h2 {
            margin: 0 0 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid rgba(127, 29, 45, 0.14);
            color: var(--clinic-maroon);
            font-size: 1rem;
            font-weight: 900;
        }

        .field-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .field-grid.three {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .field-grid.four {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .field-stack {
            display: grid;
            gap: 14px;
        }

        .form-field.span-2 {
            grid-column: span 2;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            color: #6b7280;
            font-size: 0.78rem;
            font-weight: 900;
            text-transform: uppercase;
        }

        .required {
            color: #dc2626;
        }

        .form-control,
        .form-select {
            min-height: 46px;
            border: 1.5px solid #d9bfc3;
            border-radius: 8px;
            background-color: var(--field);
            color: #111827;
            font-size: 0.94rem;
            box-shadow: none;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--clinic-yellow);
            box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.24);
        }

        .form-control[readonly] {
            background: #eef2f7;
            color: #475569;
            cursor: not-allowed;
        }

        .dependent-select-wrap {
            position: relative;
        }

        .dependent-select-js .form-select.select-enhanced-original {
            position: absolute !important;
            width: 1px !important;
            height: 1px !important;
            min-height: 1px !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }

        .dependent-select-button {
            width: 100%;
            min-height: 46px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 14px;
            border: 1.5px solid #d9bfc3;
            border-radius: 8px;
            background: var(--field);
            color: #111827;
            font: inherit;
            font-size: 0.94rem;
            text-align: left;
            box-shadow: none;
            cursor: pointer;
            transition: border-color .22s ease, box-shadow .22s ease;
        }

        .dependent-select-button:focus-visible,
        .dependent-select-wrap.is-open .dependent-select-button {
            outline: none;
            border-color: var(--clinic-yellow);
            box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.24);
        }

        .dependent-select-value {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .dependent-select-chevron {
            width: 18px;
            height: 18px;
            flex: 0 0 auto;
            color: var(--clinic-maroon);
            transition: transform .22s ease;
        }

        .dependent-select-wrap.is-open .dependent-select-chevron {
            transform: rotate(180deg);
        }

        .dependent-select-menu {
            position: absolute;
            top: calc(100% + 7px);
            left: 0;
            right: 0;
            z-index: 40;
            display: none;
            max-height: 240px;
            overflow-y: auto;
            padding: 7px;
            border: 1px solid rgba(127, 29, 45, 0.18);
            border-radius: 8px;
            background: #ffffff;
            box-shadow: 0 18px 34px rgba(15, 23, 42, 0.18);
        }

        .dependent-select-wrap.is-open .dependent-select-menu {
            display: grid;
            gap: 6px;
        }

        .dependent-select-option {
            position: relative;
            isolation: isolate;
            overflow: hidden;
            width: 100%;
            min-height: 40px;
            padding: 9px 12px;
            border: 1px solid rgba(127, 29, 45, 0.13);
            border-radius: 6px;
            background: #ffffff;
            color: var(--clinic-maroon);
            font: inherit;
            font-size: 0.9rem;
            font-weight: 800;
            text-align: left;
            cursor: pointer;
            transition: background .22s ease, color .22s ease, border-color .22s ease;
        }

        .dependent-select-option::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 0;
            background: linear-gradient(90deg, transparent 0%, rgba(254, 243, 199, 0.88) 48%, transparent 100%);
            transform: translateX(-102%);
            transition: transform .48s ease;
            pointer-events: none;
        }

        .dependent-select-option > span {
            position: relative;
            z-index: 1;
        }

        .dependent-select-option:hover,
        .dependent-select-option:focus-visible {
            border-color: #eab308;
            background: var(--clinic-yellow);
            color: #70131b;
            outline: none;
        }

        .dependent-select-option:hover::before,
        .dependent-select-option:focus-visible::before {
            transform: translateX(0);
        }

        .dependent-select-option.is-selected {
            border-color: var(--clinic-maroon);
            background: var(--clinic-maroon);
            color: #ffffff;
        }

        .dependent-select-option.is-selected:hover,
        .dependent-select-option.is-selected:focus-visible {
            border-color: var(--clinic-maroon);
            background: var(--clinic-maroon);
            color: #ffffff;
        }

        .certify-row {
            display: grid;
            grid-template-columns: 20px minmax(0, 1fr);
            gap: 10px;
            margin-top: 20px;
            padding: 16px;
            border: 1px solid rgba(250, 204, 21, 0.42);
            border-radius: 10px;
            background: #fffbeb;
            color: #4b5563;
            font-size: 0.9rem;
            line-height: 1.45;
        }

        .certify-row input {
            width: 18px;
            height: 18px;
            margin-top: 2px;
            accent-color: var(--clinic-maroon);
        }

        .contact-table {
            display: grid;
            gap: 12px;
        }

        .contact-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            padding: 14px;
            border: 1px solid rgba(127, 29, 45, 0.12);
            border-radius: 8px;
            background: rgba(248, 250, 252, 0.72);
        }

        .btn-row {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 22px;
        }

        .btn-health {
            position: relative;
            isolation: isolate;
            overflow: hidden;
            min-height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 8px;
            border: 1px solid #8b0000;
            background: #8b0000;
            color: #ffffff;
            font-weight: 900;
            text-decoration: none;
            box-shadow: 0 10px 22px rgba(127, 29, 29, .22);
        }

        .btn-health::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 0;
            background: linear-gradient(90deg, transparent 0%, rgba(254, 243, 199, 0.82) 48%, transparent 100%);
            transform: translateX(-110%);
            transition: transform .62s ease;
            pointer-events: none;
        }

        .btn-health > * {
            position: relative;
            z-index: 1;
        }

        .btn-health:hover,
        .btn-health:focus-visible {
            border-color: #eab308;
            background: #facc15;
            color: #70131b;
        }

        .btn-health:hover::before,
        .btn-health:focus-visible::before {
            transform: translateX(110%);
        }

        .btn-health.btn-back {
            border-color: rgba(127, 29, 45, 0.2);
            background: #ffffff;
            color: var(--clinic-maroon);
            box-shadow: 0 8px 18px rgba(15, 23, 42, .12);
        }

        .alert {
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 700;
        }

        @media (max-width: 720px) {
            body {
                padding: 12px 8px 118px;
            }

            .profile-topbar {
                grid-template-columns: 1fr;
                padding: 14px 16px;
            }

            .profile-form-title {
                text-align: left;
            }

            .profile-body {
                padding: 20px 14px;
            }

            .field-grid,
            .field-grid.three,
            .field-grid.four,
            .contact-row {
                grid-template-columns: 1fr;
            }

            .form-field.span-2 {
                grid-column: auto;
            }

            .btn-row {
                flex-direction: column-reverse;
            }

            .btn-health {
                width: 100%;
            }
        }
    </style>
</head>
<body class="dependent-profile-page">
    <div class="profile-shell">
        <div class="profile-topbar">
            <div class="profile-brand">
                <img src="{{ asset('images/pup_logo.png') }}" alt="PUP Logo">
                <div>
                    <strong>PUP Taguig<br>Medical Clinic</strong>
                </div>
            </div>
            <div class="profile-form-title">Information Form</div>
        </div>

        <main class="profile-body">
            <div class="profile-intro">
                <span class="profile-intro-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.1a7.5 7.5 0 0 1 15 0" />
                    </svg>
                </span>
                <div>
                    <h1>Dependent Information</h1>
                    <p>Please provide your basic personal and contact information for the clinic record.</p>
                </div>
            </div>

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('dependent.profile.store') }}" method="POST" id="dependent_profile_form">
                @csrf
                <input type="hidden" id="home_address" name="home_address" value="{{ old('home_address', $prefill['home_address'] ?? '') }}">

                <section class="profile-section">
                    <h2>Personal Information</h2>
                    <div class="field-stack">
                    <div class="field-grid four">
                        <div class="form-field">
                            <label class="form-label" for="first_name">First Name <span class="required">*</span></label>
                            <input id="first_name" name="first_name" class="form-control" value="{{ old('first_name', $prefill['first_name'] ?? '') }}" required maxlength="120" readonly aria-readonly="true">
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="middle_name">Middle Name</label>
                            <input id="middle_name" name="middle_name" class="form-control" value="{{ old('middle_name', $prefill['middle_name'] ?? '') }}" maxlength="120" readonly aria-readonly="true">
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="last_name">Last Name <span class="required">*</span></label>
                            <input id="last_name" name="last_name" class="form-control" value="{{ old('last_name', $prefill['last_name'] ?? '') }}" required maxlength="120" readonly aria-readonly="true">
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="suffix_name">Suffix</label>
                            <input id="suffix_name" name="suffix_name" class="form-control" value="{{ old('suffix_name', $prefill['suffix_name'] ?? '') }}" maxlength="120" readonly aria-readonly="true">
                        </div>
                    </div>

                    <div class="field-grid">
                        <div class="form-field">
                            <label class="form-label" for="email">Email Address <span class="required">*</span></label>
                            <input id="email" name="email" type="email" class="form-control" value="{{ old('email', $prefill['email'] ?? '') }}" required maxlength="255" readonly aria-readonly="true">
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="id_number">ID Number</label>
                            <input id="id_number" name="id_number" class="form-control" value="{{ old('id_number', $prefill['id_number'] ?? '') }}" maxlength="120">
                        </div>
                    </div>

                    <div class="field-grid">
                        <div class="form-field">
                            <label class="form-label" for="birthday">Birthday <span class="required">*</span></label>
                            <input id="birthday" name="birthday" type="date" class="form-control" value="{{ old('birthday', $prefill['birthday'] ?? '') }}" required max="{{ now()->toDateString() }}">
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="age">Age <span class="required">*</span></label>
                            <input id="age" name="age" type="number" min="0" max="120" class="form-control" value="{{ old('age', $prefill['age'] ?? '') }}" required>
                        </div>
                    </div>

                    <div class="field-grid">
                        <div class="form-field">
                            <label class="form-label" for="sex">Sex <span class="required">*</span></label>
                            @php($selectedSex = old('sex', $prefill['sex'] ?? ''))
                            <select id="sex" name="sex" class="form-select" required>
                                <option value="" disabled {{ $selectedSex === '' ? 'selected' : '' }}>Select sex</option>
                                @foreach(['Male', 'Female'] as $sex)
                                    <option value="{{ $sex }}" {{ $selectedSex === $sex ? 'selected' : '' }}>{{ $sex }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="civil_status">Civil Status <span class="required">*</span></label>
                            <select id="civil_status" name="civil_status" class="form-select" required>
                                @php($civilStatus = old('civil_status', $prefill['civil_status'] ?? ''))
                                <option value="" disabled {{ $civilStatus === '' ? 'selected' : '' }}>Select status</option>
                                @foreach(['Single', 'Married', 'Widowed', 'Separated'] as $status)
                                    <option value="{{ $status }}" {{ $civilStatus === $status ? 'selected' : '' }}>{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    </div>
                </section>

                <section class="profile-section">
                    <h2>Complete Address</h2>
                    <div class="field-grid">
                        <div class="form-field span-2">
                            <label class="form-label" for="street">Street / House No. <span class="required">*</span></label>
                            <input id="street" name="street" class="form-control" value="{{ old('street', $prefill['street'] ?? '') }}" required maxlength="255" data-address-part>
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="barangay">Barangay <span class="required">*</span></label>
                            <input id="barangay" name="barangay" class="form-control" value="{{ old('barangay', $prefill['barangay'] ?? '') }}" required maxlength="120" data-address-part>
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="municipality">Municipality / City <span class="required">*</span></label>
                            <input id="municipality" name="municipality" class="form-control" value="{{ old('municipality', $prefill['municipality'] ?? '') }}" required maxlength="120" data-address-part>
                        </div>
                        <div class="form-field span-2">
                            <label class="form-label" for="province">Province <span class="required">*</span></label>
                            <input id="province" name="province" class="form-control" value="{{ old('province', $prefill['province'] ?? '') }}" required maxlength="120" data-address-part>
                        </div>
                    </div>
                </section>

                <section class="profile-section">
                    <h2>Contact Information</h2>
                    <div class="contact-table">
                        <div class="contact-row">
                            <div class="form-field">
                                <label class="form-label" for="contact_no">Contact Number <span class="required">*</span></label>
                                <input id="contact_no" name="contact_no" class="form-control" value="{{ old('contact_no', $prefill['contact_no'] ?? '') }}" required inputmode="numeric" maxlength="20">
                            </div>
                            <div class="form-field">
                                <label class="form-label" for="landline">Landline</label>
                                <input id="landline" name="landline" class="form-control" value="{{ old('landline', $prefill['landline'] ?? '') }}" maxlength="20">
                            </div>
                        </div>
                        <div class="contact-row">
                            <div class="form-field">
                                <label class="form-label" for="emergency_contact_name">Emergency Person <span class="required">*</span></label>
                                <input id="emergency_contact_name" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name', $prefill['emergency_contact_name'] ?? '') }}" required maxlength="255">
                            </div>
                            <div class="form-field">
                                <label class="form-label" for="emergency_contact_no">Emergency Contact Number <span class="required">*</span></label>
                                <input id="emergency_contact_no" name="emergency_contact_no" class="form-control" value="{{ old('emergency_contact_no', $prefill['emergency_contact_no'] ?? '') }}" required inputmode="numeric" maxlength="20">
                            </div>
                        </div>
                    </div>
                </section>

                <label class="certify-row" for="dependent_profile_certified">
                    <input id="dependent_profile_certified" type="checkbox" name="dependent_profile_certified" value="1" required {{ old('dependent_profile_certified') ? 'checked' : '' }}>
                    <span>I certify that the information I provided is true and correct.</span>
                </label>

                <div class="btn-row">
                    <a href="{{ url('/student/home') }}" class="btn-health btn-back"><span>Back</span></a>
                    <button type="submit" class="btn-health" id="dependent_profile_submit">
                        <span data-submit-label>Save Information</span>
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 12h14" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="m13 6 6 6-6 6" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </form>
        </main>
    </div>

    @include('partials.system_footer')

    <script>
        (function () {
            document.documentElement.classList.add('dependent-select-js');

            const birthday = document.getElementById('birthday');
            const age = document.getElementById('age');
            const homeAddress = document.getElementById('home_address');
            const addressParts = Array.from(document.querySelectorAll('[data-address-part]'));
            const form = document.getElementById('dependent_profile_form');
            const submitButton = document.getElementById('dependent_profile_submit');
            const submitLabel = submitButton?.querySelector('[data-submit-label]');
            const enhancedSelects = [];

            function calculateAge(value) {
                if (!value) return '';
                const birthDate = new Date(value + 'T00:00:00');
                if (Number.isNaN(birthDate.getTime())) return '';
                const today = new Date();
                let years = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    years -= 1;
                }
                return Math.max(0, years);
            }

            function syncAge() {
                const resolvedAge = calculateAge(birthday?.value || '');
                if (resolvedAge !== '' && age) {
                    age.value = resolvedAge;
                }
            }

            function syncAddress() {
                if (!homeAddress) return;
                homeAddress.value = addressParts
                    .map((input) => input.value.trim())
                    .filter(Boolean)
                    .join(', ');
            }

            function closeSelect(wrapper) {
                wrapper.classList.remove('is-open');
                const button = wrapper.querySelector('.dependent-select-button');
                if (button) button.setAttribute('aria-expanded', 'false');
            }

            function closeOtherSelects(activeWrapper) {
                enhancedSelects.forEach(({ wrapper }) => {
                    if (wrapper !== activeWrapper) closeSelect(wrapper);
                });
            }

            function enhanceSelect(select) {
                const wrapper = document.createElement('div');
                wrapper.className = 'dependent-select-wrap';
                select.classList.add('select-enhanced-original');
                select.parentNode.insertBefore(wrapper, select);
                wrapper.appendChild(select);

                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'dependent-select-button';
                button.setAttribute('aria-haspopup', 'listbox');
                button.setAttribute('aria-expanded', 'false');
                button.innerHTML = `
                    <span class="dependent-select-value"></span>
                    <svg class="dependent-select-chevron" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                `;

                const menu = document.createElement('div');
                menu.className = 'dependent-select-menu';
                menu.setAttribute('role', 'listbox');

                const valueLabel = button.querySelector('.dependent-select-value');
                const choices = Array.from(select.options).filter((option) => !option.disabled);

                function updateSelectedLabel() {
                    const selected = select.options[select.selectedIndex];
                    valueLabel.textContent = selected ? selected.textContent : 'Select option';
                    menu.querySelectorAll('.dependent-select-option').forEach((optionButton) => {
                        optionButton.classList.toggle('is-selected', optionButton.dataset.value === select.value);
                    });
                }

                choices.forEach((option) => {
                    const optionButton = document.createElement('button');
                    optionButton.type = 'button';
                    optionButton.className = 'dependent-select-option';
                    optionButton.dataset.value = option.value;
                    optionButton.setAttribute('role', 'option');
                    const optionLabel = document.createElement('span');
                    optionLabel.textContent = option.textContent;
                    optionButton.appendChild(optionLabel);
                    optionButton.addEventListener('click', () => {
                        select.value = option.value;
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                        updateSelectedLabel();
                        closeSelect(wrapper);
                        button.focus();
                    });
                    menu.appendChild(optionButton);
                });

                button.addEventListener('click', () => {
                    const willOpen = !wrapper.classList.contains('is-open');
                    closeOtherSelects(wrapper);
                    wrapper.classList.toggle('is-open', willOpen);
                    button.setAttribute('aria-expanded', String(willOpen));
                });

                button.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        closeSelect(wrapper);
                        button.focus();
                    }
                });

                select.addEventListener('change', updateSelectedLabel);
                select.addEventListener('invalid', () => {
                    button.focus();
                    closeOtherSelects(wrapper);
                    wrapper.classList.add('is-open');
                    button.setAttribute('aria-expanded', 'true');
                });

                wrapper.appendChild(button);
                wrapper.appendChild(menu);
                enhancedSelects.push({ wrapper, select, button });
                updateSelectedLabel();
            }

            birthday?.addEventListener('change', syncAge);
            addressParts.forEach((input) => {
                input.addEventListener('input', syncAddress);
                input.addEventListener('change', syncAddress);
            });
            document.querySelectorAll('.form-select').forEach(enhanceSelect);
            document.addEventListener('click', (event) => {
                if (!event.target.closest('.dependent-select-wrap')) {
                    enhancedSelects.forEach(({ wrapper }) => closeSelect(wrapper));
                }
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    enhancedSelects.forEach(({ wrapper }) => closeSelect(wrapper));
                }
            });
            form?.addEventListener('submit', () => {
                if (!submitButton || !submitLabel) return;
                submitButton.disabled = true;
                submitButton.setAttribute('aria-busy', 'true');
                submitLabel.textContent = 'Saving...';
            });
            syncAddress();
        })();
    </script>
</body>
</html>
