@extends('layouts.admin')

@section('title', 'Test Accounts')
@section('disable_voice_inputs', 'true')

@push('styles')
<style>
    .test-account-page { max-width: 1120px; margin: 0 auto; padding: 20px 22px 38px; color: #111827; }
    .test-account-card { overflow: hidden; border: 1px solid rgba(112,19,27,.16); border-radius: 18px; background: #fff; box-shadow: 0 16px 38px rgba(15,23,42,.09); }
    .test-account-head { display: flex; align-items: center; justify-content: space-between; gap: 18px; padding: 22px 26px; }
    .test-account-head h1 { margin: 0; font-size: 1.35rem; font-weight: 850; color: #111827; }
    .test-account-head p { margin: 5px 0 0; color: #64748b; }
    .test-account-create { position: relative; isolation: isolate; overflow: hidden; display: inline-flex; align-items: center; gap: 8px; min-height: 42px; padding: 0 16px; border: 1px solid #760714; border-radius: 10px; background: #800817; color: #fff; font: inherit; font-size: .88rem; font-weight: 800; cursor: pointer; box-shadow: 0 8px 18px rgba(128,8,23,.18); transition: color .2s ease, background-color .2s ease, border-color .2s ease, box-shadow .2s ease; }
    .test-account-create::before, .test-account-modal-button::before, .test-account-modal-close::before { content: ""; position: absolute; z-index: 0; top: -35%; bottom: -35%; left: -45%; width: 36%; background: rgba(255,244,173,.55); transform: translateX(-180%) skewX(-22deg); transition: transform .55s cubic-bezier(.2,.75,.25,1); pointer-events: none; }
    .test-account-create:hover, .test-account-create:focus-visible { border-color: #facc15; background: #facc15; color: #70131b; box-shadow: 0 8px 20px rgba(250,204,21,.24); }
    .test-account-create:hover::before, .test-account-create:focus-visible::before, .test-account-modal-button:hover::before, .test-account-modal-button:focus-visible::before, .test-account-modal-close:hover::before, .test-account-modal-close:focus-visible::before { transform: translateX(520%) skewX(-22deg); }
    .test-account-body { padding: 0 24px 24px; }
    .test-account-flash { margin-bottom: 14px; padding: 12px 14px; border-radius: 10px; font-size: .9rem; line-height: 1.45; }
    .test-account-flash.success { border: 1px solid #86d3a4; background: #eafaf0; color: #14532d; }
    .test-account-flash.error { border: 1px solid #f3a5a5; background: #fff0f0; color: #991b1b; }
    .test-account-table-wrap { overflow-x: auto; border: 1px solid #d7dce3; border-radius: 10px; }
    .test-account-table { width: 100%; border-collapse: collapse; min-width: 720px; font-size: .86rem; }
    .test-account-table th { padding: 10px 12px; background: #f1f3f5; color: #273244; text-align: left; font-weight: 800; white-space: nowrap; }
    .test-account-table td { padding: 9px 12px; border-top: 1px solid #e3e6eb; color: #263244; vertical-align: middle; }
    .test-account-table tbody tr:hover { background: #fff9f7; }
    .test-account-row-actions { display: flex; align-items: center; gap: 6px; }
    .test-account-row-actions form { margin: 0; }
    .test-account-row-button { display: grid; place-items: center; width: 34px; height: 34px; padding: 0; border: 1px solid rgba(100,116,139,.24); border-radius: 8px; background: #f8fafc; color: #475569; cursor: pointer; transition: background .18s ease, color .18s ease, border-color .18s ease; }
    .test-account-row-button svg { width: 17px; height: 17px; }
    .test-account-row-button:not(.delete):hover, .test-account-row-button:not(.delete):focus-visible { border-color: #facc15; background: #facc15; color: #70131b; outline: none; }
    .test-account-row-button.delete { border-color: rgba(190,18,60,.22); background: #fff1f2; color: #be123c; }
    .test-account-row-button.delete:hover, .test-account-row-button.delete:focus-visible { border-color: #70131b; background: #70131b; color: #fff; box-shadow: 0 5px 14px rgba(112,19,27,.24); transform: translateY(-1px); }
    .test-account-empty { padding: 28px 16px !important; color: #64748b !important; text-align: center; }
    .test-account-pagination { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding-top: 12px; color: #64748b; font-size: .82rem; }
    .test-account-pagination nav { margin-left: auto; }
    .test-account-pagination nav svg { width: 16px; height: 16px; }
    .test-account-pagination nav a, .test-account-pagination nav span { display: inline-flex; padding: 5px 9px; border: 1px solid #d5d9df; border-radius: 6px; color: #334155; text-decoration: none; }
    .test-account-modal { position: fixed; z-index: 10050; inset: 0; display: none; align-items: center; justify-content: center; padding: 18px; background: rgba(15,23,42,.58); backdrop-filter: blur(5px); }
    .test-account-modal.is-open { display: flex; }
    .test-account-dialog { display: flex; flex-direction: column; width: min(100%, 560px); max-height: min(92vh, 780px); overflow: hidden; border: 1px solid rgba(112,19,27,.2); border-radius: 16px; background: #fff; color: #111827; box-shadow: 0 24px 70px rgba(0,0,0,.28); }
    .test-account-dialog-head { position: sticky; z-index: 2; top: 0; flex: 0 0 auto; display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; padding: 17px 20px; border-radius: 15px 15px 0 0; background: linear-gradient(120deg,#70131b,#9b1c30); color: #fff; }
    .test-account-dialog-head h2 { margin: 0; color: #fff !important; font-size: 1.15rem; font-weight: 850; }
    .test-account-dialog-head p { margin: 4px 0 0; color: rgba(255,255,255,.96) !important; font-size: .86rem; }
    .test-account-modal-close { position: relative; isolation: isolate; overflow: hidden; display: inline-flex; align-items: center; justify-content: center; flex: 0 0 36px; width: 36px; height: 36px; padding: 0; line-height: 1; border: 1px solid rgba(255,255,255,.55); border-radius: 50%; background: rgba(255,255,255,.08); color: #fff; cursor: pointer; transition: color .2s ease, background-color .2s ease, border-color .2s ease; }
    .test-account-modal-close svg { display: block; width: 18px; height: 18px; }
    .test-account-modal-close:hover, .test-account-modal-close:focus-visible { border-color: #facc15; background: #facc15; color: #70131b; }
    .test-account-modal-form { min-height: 0; overflow-y: auto; padding: 20px; }
    .test-account-grid { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 14px; }
    .test-account-field { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
    .test-account-field[hidden] { display: none !important; }
    .test-account-field.full { grid-column: 1 / -1; }
    .test-account-field label { color: #334155; font-size: .84rem; font-weight: 750; }
    .test-account-field input, .test-account-field select { width: 100%; min-height: 43px; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 9px; background: #fff; color: #111827; font: inherit; font-size: .9rem; }
    .test-account-password-wrap { position: relative; }
    .test-account-password-wrap input { padding-right: 46px; }
    .test-account-password-toggle { position: absolute; top: 50%; right: 8px; display: grid; width: 32px; height: 32px; padding: 0; place-items: center; transform: translateY(-50%); border: 0; border-radius: 7px; background: transparent; color: #64748b; cursor: pointer; }
    .test-account-password-toggle:hover, .test-account-password-toggle:focus-visible { background: #fff4c2; color: #70131b; outline: none; }
    .test-account-password-toggle[aria-pressed="true"] { color: #70131b; }
    .test-account-password-toggle svg { width: 18px; height: 18px; }
    .test-account-field input:focus, .test-account-field select:focus { outline: 3px solid rgba(250,204,21,.25); border-color: #a21d31; }
    .test-account-help { color: #64748b; font-size: .76rem; line-height: 1.4; }
    .test-account-password-suggestion { color: #475569; }
    .test-account-error { color: #b91c1c; font-size: .8rem; }
    .test-account-modal-actions { display: flex; justify-content: flex-end; gap: 10px; padding-top: 18px; }
    .test-account-modal-button { position: relative; isolation: isolate; overflow: hidden; min-height: 42px; padding: 0 16px; border: 1px solid #8b1021; border-radius: 9px; background: #8b1021; color: #fff; font: inherit; font-size: .88rem; font-weight: 800; cursor: pointer; transition: color .2s ease, background-color .2s ease, border-color .2s ease, box-shadow .2s ease; }
    .test-account-modal-button.secondary { border-color: #cbd5e1; background: #fff; color: #334155; }
    .test-account-modal-button:hover, .test-account-modal-button:focus-visible { border-color: #facc15; background: #facc15; color: #70131b; box-shadow: 0 6px 16px rgba(250,204,21,.2); }
    .test-account-modal-button.secondary::before { background: rgba(255,250,218,.8); }
    .test-account-create:focus-visible, .test-account-modal-button:focus-visible, .test-account-modal-close:focus-visible { outline: 3px solid rgba(250,204,21,.42); outline-offset: 3px; }
    html[data-theme="dark"] .test-account-card, html[data-theme="dark"] .test-account-dialog { border-color: #344155; background: #111827; color: #f8fafc; }
    html[data-theme="dark"] .test-account-head h1 { color: #f8fafc; }
    html[data-theme="dark"] .test-account-head p, html[data-theme="dark"] .test-account-pagination { color: #aab7ca; }
    html[data-theme="dark"] .test-account-flash.success { border-color: #166534; background: rgba(20,83,45,.35); color: #bbf7d0; }
    html[data-theme="dark"] .test-account-flash.error { border-color: #7f1d1d; background: rgba(127,29,29,.28); color: #fecaca; }
    html[data-theme="dark"] .test-account-table-wrap { border-color: #344155; }
    html[data-theme="dark"] .test-account-table th { background: #1e293b; color: #f1f5f9; }
    html[data-theme="dark"] .test-account-table td { border-color: #344155; color: #e2e8f0; }
    html[data-theme="dark"] .test-account-table tbody tr:hover { background: rgba(112,19,27,.18); }
    html[data-theme="dark"] .test-account-row-button, html[data-theme="dark"] .test-account-modal-button.secondary { border-color: #475569; background: #1e293b; color: #e2e8f0; }
    html[data-theme="dark"] .test-account-row-button.delete { border-color: #7f3440; background: rgba(127,29,29,.22); color: #fda4af; }
    html[data-theme="dark"] .test-account-row-button:not(.delete):hover, html[data-theme="dark"] .test-account-row-button:not(.delete):focus-visible { border-color: #facc15; background: #facc15; color: #70131b; }
    html[data-theme="dark"] .test-account-row-button.delete:hover, html[data-theme="dark"] .test-account-row-button.delete:focus-visible { border-color: #9b1c30; background: #9b1c30; color: #fff; }
    html[data-theme="dark"] .test-account-field label { color: #e2e8f0; }
    html[data-theme="dark"] .test-account-field input, html[data-theme="dark"] .test-account-field select { border-color: #475569; background: #1e293b; color: #f8fafc; color-scheme: dark; }
    html[data-theme="dark"] .test-account-password-toggle { color: #cbd5e1; }
    html[data-theme="dark"] .test-account-password-toggle:hover, html[data-theme="dark"] .test-account-password-toggle:focus-visible { background: rgba(250,204,21,.16); color: #fde68a; }
    html[data-theme="dark"] .test-account-password-toggle[aria-pressed="true"] { color: #70131b; }
    html[data-theme="dark"] .test-account-help { color: #aab7ca; }
    html[data-theme="dark"] .test-account-password-suggestion { color: #cbd5e1; }
    @media (prefers-reduced-motion: reduce) {
        .test-account-create, .test-account-modal-button, .test-account-modal-close { transition: none; }
        .test-account-create::before, .test-account-modal-button::before, .test-account-modal-close::before { transition: none; }
    }
    @media (max-width: 640px) {
        .test-account-page { padding: 14px 12px 28px; }
        .test-account-head { align-items: flex-start; flex-direction: column; padding: 18px; }
        .test-account-body { padding: 0 14px 18px; }
        .test-account-grid { grid-template-columns: 1fr; }
        .test-account-field.full { grid-column: auto; }
        .test-account-modal-actions { flex-direction: column-reverse; }
        .test-account-modal-button { width: 100%; }
        .test-account-pagination { align-items: flex-start; flex-direction: column; }
        .test-account-pagination nav { margin-left: 0; }
    }
</style>
@endpush

@section('content')
<main class="test-account-page">
    <section class="test-account-card" aria-labelledby="testAccountsTitle">
        <header class="test-account-head">
            <div>
                <h1 id="testAccountsTitle">Test Accounts</h1>
                <p>Manage and view generated test accounts for testing and staging workflow checks.</p>
            </div>
            <button type="button" class="test-account-create" id="openCreateTestAccount">
                <span aria-hidden="true">+</span> Create Test Account
            </button>
        </header>

        <div class="test-account-body">
            @if(session('createdTestAccount'))
                @php
                    $created = session('createdTestAccount');
                @endphp
                <div class="test-account-flash success" role="status">
                    <strong>Test account created.</strong> {{ $created['account_type'] }} — {{ $created['name'] }} ({{ $created['email'] }}). Test ID: {{ $created['student_id'] }}.
                </div>
            @elseif(session('updatedTestAccount'))
                <div class="test-account-flash success" role="status">Test account updated.</div>
            @elseif(session('deletedTestAccount'))
                <div class="test-account-flash success" role="status">Test account deleted.</div>
            @endif

            @if(session('testAccountError'))
                <div class="test-account-flash error" role="alert">{{ session('testAccountError') }}</div>
            @endif

            @if($errors->any())
                <div class="test-account-flash error" role="alert">Please review the highlighted fields in the account form.</div>
            @endif

            <div class="test-account-table-wrap">
                <table class="test-account-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email Address</th>
                            <th>Role / Account Type</th>
                            <th>Date Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($testAccounts as $testAccount)
                            @php
                                $accountRole = \App\Models\User::normalizeRole((string) $testAccount->user_role) === \App\Models\User::ROLE_ADMIN
                                    ? 'admin'
                                    : match (strtolower((string) $testAccount->user_type)) {
                                        'faculty' => 'faculty',
                                        'applicant' => 'applicant',
                                        'dependent' => 'dependent',
                                        default => 'student',
                                    };
                            @endphp
                            <tr>
                                <td>{{ $testAccount->name }}</td>
                                <td>{{ $testAccount->email }}</td>
                                <td>{{ $accountRole === 'admin' ? 'Admin Designee' : ($accountRole === 'faculty' ? 'Faculty' : ($accountRole === 'applicant' ? 'Applicant' : ($accountRole === 'dependent' ? 'Dependent' : 'Student'))) }}</td>
                                <td>{{ optional($testAccount->created_at)->format('M j, Y') ?? '—' }}</td>
                                <td>
                                    <div class="test-account-row-actions">
                                        <button type="button" class="test-account-row-button edit-test-account" aria-label="Edit test account" title="Edit test account"
                                            data-id="{{ $testAccount->id }}"
                                            data-first-name="{{ $testAccount->first_name }}"
                                            data-middle-name="{{ $testAccount->middle_name }}"
                                            data-last-name="{{ $testAccount->last_name }}"
                                            data-suffix-name="{{ $testAccount->suffix_name }}"
                                            data-email="{{ $testAccount->email }}"
                                            data-account-type="{{ $accountRole }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                        </button>
                                        <form method="POST" action="{{ route('admin.developer-tools.test-accounts.destroy', $testAccount->id) }}" onsubmit="return confirm('Are you sure you want to delete this test account? Accounts with linked clinic records cannot be deleted.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="test-account-row-button delete" aria-label="Delete test account" title="Delete test account">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166M19.228 5.79 18.16 19.673A2.25 2.25 0 0 1 15.916 21H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .563c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="test-account-empty">No test accounts created yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($testAccounts->total() > 0)
                <div class="test-account-pagination">
                    <span>Showing {{ $testAccounts->firstItem() }} to {{ $testAccounts->lastItem() }} of {{ $testAccounts->total() }} entries</span>
                    {{ $testAccounts->links() }}
                </div>
            @endif
        </div>
    </section>
</main>

<div class="test-account-modal" id="testAccountModal" aria-hidden="true" hidden>
    <section class="test-account-dialog" role="dialog" aria-modal="true" aria-labelledby="testAccountModalTitle">
        <header class="test-account-dialog-head">
            <div>
                <h2 id="testAccountModalTitle">Create Test Account</h2>
                <p id="testAccountModalSubtitle">Create a temporary account for a controlled workflow check.</p>
            </div>
            <button type="button" class="test-account-modal-close" id="closeTestAccountModal" aria-label="Close">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="m6 6 12 12M18 6 6 18" /></svg>
            </button>
        </header>

        <form class="test-account-modal-form" id="testAccountForm" method="POST" action="{{ route('admin.developer-tools.test-accounts.store') }}" autocomplete="off">
            @csrf
            <input type="hidden" name="_method" id="testAccountMethod" value="" disabled>
            <input type="hidden" name="editing_account_id" id="editingAccountId" value="{{ old('editing_account_id') }}">
            <div class="test-account-grid">
                <div class="test-account-field">
                    <label for="testFirstName">First name</label>
                    <input id="testFirstName" name="first_name" value="{{ old('first_name') }}" maxlength="100" required autocomplete="off">
                    @error('first_name')<span class="test-account-error">{{ $message }}</span>@enderror
                </div>
                <div class="test-account-field">
                    <label for="testMiddleName">Middle name <span class="test-account-help">(optional)</span></label>
                    <input id="testMiddleName" name="middle_name" value="{{ old('middle_name') }}" maxlength="100" autocomplete="off">
                    @error('middle_name')<span class="test-account-error">{{ $message }}</span>@enderror
                </div>
                <div class="test-account-field">
                    <label for="testLastName">Last name</label>
                    <input id="testLastName" name="last_name" value="{{ old('last_name') }}" maxlength="100" required autocomplete="off">
                    @error('last_name')<span class="test-account-error">{{ $message }}</span>@enderror
                </div>
                <div class="test-account-field">
                    <label for="testSuffixName">Suffix <span class="test-account-help">(optional)</span></label>
                    <input id="testSuffixName" name="suffix_name" value="{{ old('suffix_name') }}" maxlength="30" placeholder="Jr., III" autocomplete="off">
                    @error('suffix_name')<span class="test-account-error">{{ $message }}</span>@enderror
                </div>
                <div class="test-account-field full">
                    <label for="testEmail">Email address</label>
                    <input id="testEmail" type="email" name="email" value="{{ old('email') }}" maxlength="255" required autocomplete="off">
                    @error('email')<span class="test-account-error">{{ $message }}</span>@enderror
                </div>
                <div class="test-account-field full">
                    <label for="testAccountType">Account type</label>
                    <select id="testAccountType" name="account_type" required>
                    <option value="" disabled {{ old('account_type') ? '' : 'selected' }}>Select account type</option>
                        @foreach($accountTypes as $value => $label)
                            <option value="{{ $value }}" {{ old('account_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('account_type')<span class="test-account-error">{{ $message }}</span>@enderror
                </div>
                <div class="test-account-field full">
                    <label for="testPassword"><span id="testPasswordLabel">Password</span> <span id="testPasswordOptional" class="test-account-help" hidden>(optional when editing)</span></label>
                    <div class="test-account-password-wrap">
                    <input id="testPassword" type="password" name="password" minlength="8" required autocomplete="new-password">
                        <button type="button" class="test-account-password-toggle" id="toggleTestPassword" aria-label="Show password" aria-pressed="false" title="Show password">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6.25 9.75-6.25S21.75 12 21.75 12 18.25 18.25 12 18.25 2.25 12 2.25 12Z"/><circle cx="12" cy="12" r="2.75"/></svg>
                        </button>
                    </div>
                    <span class="test-account-help test-account-password-suggestion" id="testPasswordHelp">Suggested password is unique and editable. 8 characters with uppercase, lowercase, number, and symbol.</span>
                    @error('password')<span class="test-account-error">{{ $message }}</span>@enderror
                </div>
                <div class="test-account-field full" id="testPasswordConfirmationField" hidden>
                    <label for="testPasswordConfirmation">Confirm new password</label>
                    <div class="test-account-password-wrap">
                        <input id="testPasswordConfirmation" type="password" name="password_confirmation" minlength="8" autocomplete="new-password">
                        <button type="button" class="test-account-password-toggle" id="toggleTestPasswordConfirmation" aria-label="Show confirmation password" aria-pressed="false" title="Show confirmation password">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6.25 9.75-6.25S21.75 12 21.75 12 18.25 18.25 12 18.25 2.25 12 2.25 12Z"/><circle cx="12" cy="12" r="2.75"/></svg>
                        </button>
                    </div>
                    <span class="test-account-help">Leave both password fields blank to keep the current password.</span>
                    @error('password_confirmation')<span class="test-account-error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="test-account-modal-actions">
                <button type="button" class="test-account-modal-button secondary" id="cancelTestAccountModal">Cancel</button>
                <button type="submit" class="test-account-modal-button" id="testAccountSubmit">Create Account</button>
            </div>
        </form>
    </section>
</div>

<script>
(() => {
    const modal = document.getElementById('testAccountModal');
    const form = document.getElementById('testAccountForm');
    if (!modal || !form) return;

    const baseUrl = @json(url('/admin/developer-tools/test-accounts'));
    const method = document.getElementById('testAccountMethod');
    const title = document.getElementById('testAccountModalTitle');
    const subtitle = document.getElementById('testAccountModalSubtitle');
    const submit = document.getElementById('testAccountSubmit');
    const password = document.getElementById('testPassword');
    const confirmation = document.getElementById('testPasswordConfirmation');
    const confirmationField = document.getElementById('testPasswordConfirmationField');
    const passwordOptional = document.getElementById('testPasswordOptional');
    const passwordLabel = document.getElementById('testPasswordLabel');
    const passwordHelp = document.getElementById('testPasswordHelp');
    const passwordToggle = document.getElementById('toggleTestPassword');
    const confirmationToggle = document.getElementById('toggleTestPasswordConfirmation');
    const usedSuggestions = new Set();

    const makePasswordSuggestion = () => {
        const groups = ['ABCDEFGHJKLMNPQRSTUVWXYZ', 'abcdefghijkmnopqrstuvwxyz', '23456789', '!@#$%&*+-_='];
        const all = groups.join('');
        const randomIndex = (max) => {
            const values = new Uint32Array(1);
            window.crypto.getRandomValues(values);
            return values[0] % max;
        };
        let candidate = '';
        do {
            const chars = groups.map((group) => group[randomIndex(group.length)]);
            while (chars.length < 8) chars.push(all[randomIndex(all.length)]);
            for (let i = chars.length - 1; i > 0; i--) {
                const j = randomIndex(i + 1);
                [chars[i], chars[j]] = [chars[j], chars[i]];
            }
            candidate = chars.join('');
        } while (usedSuggestions.has(candidate));
        usedSuggestions.add(candidate);
        return candidate;
    };

    const seedSuggestedPassword = () => {
        const suggestion = makePasswordSuggestion();
        password.value = suggestion;
    };

    const setFieldVisibility = (input, toggle, visible, fieldLabel) => {
        input.type = visible ? 'text' : 'password';
        toggle.setAttribute('aria-pressed', visible ? 'true' : 'false');
        toggle.setAttribute('aria-label', `${visible ? 'Hide' : 'Show'} ${fieldLabel}`);
        toggle.title = `${visible ? 'Hide' : 'Show'} ${fieldLabel}`;
    };
    const setPasswordVisibility = (visible) => setFieldVisibility(password, passwordToggle, visible, 'password');
    const setConfirmationVisibility = (visible) => setFieldVisibility(confirmation, confirmationToggle, visible, 'confirmation password');
    const syncPasswordRequirements = () => {
        const isEditingPassword = !confirmationField.hidden;
        const hasPasswordChange = password.value.length > 0 || confirmation.value.length > 0;
        password.required = !isEditingPassword || hasPasswordChange;
        confirmation.required = isEditingPassword && hasPasswordChange;
    };

    const setOpen = (open) => {
        modal.hidden = !open;
        modal.classList.toggle('is-open', open);
        modal.setAttribute('aria-hidden', open ? 'false' : 'true');
        document.body.style.overflow = open ? 'hidden' : '';
        if (open) modal.querySelector('input:not([type="hidden"])')?.focus();
    };

    const resetForm = () => {
        form.reset();
        form.action = baseUrl;
        method.disabled = true;
        method.value = '';
        document.getElementById('editingAccountId').value = '';
        title.textContent = 'Create Test Account';
        subtitle.textContent = 'Create a temporary account for a controlled workflow check.';
        submit.textContent = 'Create Account';
        passwordLabel.textContent = 'Password';
        password.required = true;
        confirmation.required = false;
        confirmation.value = '';
        confirmationField.hidden = true;
        passwordOptional.hidden = true;
        password.autocomplete = 'new-password';
        setPasswordVisibility(false);
        setConfirmationVisibility(false);
        passwordHelp.textContent = 'Suggested password is unique and editable. 8 characters with uppercase, lowercase, number, and symbol.';
    };

    document.getElementById('openCreateTestAccount')?.addEventListener('click', () => {
        resetForm();
        seedSuggestedPassword();
        setOpen(true);
    });

    passwordToggle.addEventListener('click', () => setPasswordVisibility(password.type === 'password'));
    confirmationToggle.addEventListener('click', () => setConfirmationVisibility(confirmation.type === 'password'));
    password.addEventListener('input', syncPasswordRequirements);
    confirmation.addEventListener('input', syncPasswordRequirements);

    document.querySelectorAll('.edit-test-account').forEach((button) => {
        button.addEventListener('click', () => {
            resetForm();
            title.textContent = 'Edit Test Account';
            subtitle.textContent = 'Update the account details or set a new password.';
            submit.textContent = 'Save Changes';
            form.action = `${baseUrl}/${button.dataset.id}`;
            method.value = 'PUT';
            method.disabled = false;
            document.getElementById('editingAccountId').value = button.dataset.id;
            document.getElementById('testFirstName').value = button.dataset.firstName || '';
            document.getElementById('testMiddleName').value = button.dataset.middleName || '';
            document.getElementById('testLastName').value = button.dataset.lastName || '';
            document.getElementById('testSuffixName').value = button.dataset.suffixName || '';
            document.getElementById('testEmail').value = button.dataset.email || '';
            document.getElementById('testAccountType').value = button.dataset.accountType || 'student';
            passwordLabel.textContent = 'New password';
            confirmationField.hidden = false;
            syncPasswordRequirements();
            passwordOptional.hidden = false;
            passwordHelp.textContent = 'Leave both new password fields blank to keep the current password.';
            password.autocomplete = 'new-password';
            setOpen(true);
        });
    });

    const close = () => setOpen(false);
    document.getElementById('closeTestAccountModal')?.addEventListener('click', close);
    document.getElementById('cancelTestAccountModal')?.addEventListener('click', close);
    modal.addEventListener('click', (event) => { if (event.target === modal) close(); });
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && modal.classList.contains('is-open')) close(); });

    @if($errors->any())
        @if(old('editing_account_id'))
            form.action = `${baseUrl}/{{ (int) old('editing_account_id') }}`;
            method.value = 'PUT';
            method.disabled = false;
            title.textContent = 'Edit Test Account';
            subtitle.textContent = 'Review the highlighted fields and save your changes.';
            submit.textContent = 'Save Changes';
            passwordLabel.textContent = 'New password';
            confirmationField.hidden = false;
            syncPasswordRequirements();
            passwordOptional.hidden = false;
            passwordHelp.textContent = 'Leave both new password fields blank to keep the current password.';
        @else
            form.action = baseUrl;
            method.disabled = true;
            method.value = '';
            title.textContent = 'Create Test Account';
            submit.textContent = 'Create Account';
            password.required = true;
            passwordHelp.textContent = 'Suggested password is unique and editable. 8 characters with uppercase, lowercase, number, and symbol.';
            seedSuggestedPassword();
        @endif
        setOpen(true);
    @endif
})();
</script>
@endsection
