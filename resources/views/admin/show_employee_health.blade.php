@extends('layouts.admin')

@section('title', 'Employee Health Profile')

@php
    $employeeProfile = $employeeProfile ?? null;
    $employeeUser = $employeeProfile?->user;
    $formatValue = function ($value): string {
        if (is_array($value)) {
            $value = collect($value)->filter(fn ($item) => filled($item))->implode(', ');
        }

        return trim((string) $value) !== '' ? trim((string) $value) : 'N/A';
    };
    $formatDate = function ($value): string {
        if (blank($value)) {
            return 'N/A';
        }

        try {
            return \Carbon\Carbon::parse($value)->format('M d, Y');
        } catch (\Throwable $exception) {
            return (string) $value;
        }
    };
    $formatDateTime = function ($value): string {
        if (blank($value)) {
            return 'N/A';
        }

        try {
            return \Carbon\Carbon::parse($value)->format('M d, Y h:i A');
        } catch (\Throwable $exception) {
            return (string) $value;
        }
    };
    $yesNo = fn ($value): string => (bool) $value ? 'Yes' : 'No';
    $displayName = trim((string) ($employeeProfile?->name ?: $employeeUser?->name ?: 'Employee'));
    $profileInitials = collect(preg_split('/\s+/', $displayName, -1, PREG_SPLIT_NO_EMPTY) ?: [])
        ->take(2)
        ->map(fn (string $part): string => strtoupper(substr($part, 0, 1)))
        ->implode('');
    $profileInitials = $profileInitials !== '' ? $profileInitials : 'EP';
    $profileOffice = trim((string) ($employeeProfile?->office ?: $employeeProfile?->course_college ?: 'Employee'));
    $status = trim((string) ($employeeProfile?->clearance_status ?: $employeeProfile?->submission_status ?: 'Not Processed'));
    $employeeProfileTypeLabel = $employeeHealthFormAudience ?? 'admin';
    $statusClass = in_array(strtolower($status), ['issued', 'fully cleared', 'approved'], true)
        ? 'profile-status-issued'
        : (in_array(strtolower($status), ['pending', 'for verification', 'pending resubmission'], true)
            ? 'profile-status-pending'
            : 'profile-status-default');
    $employeePhotoUrl = filled($employeeProfile?->student_photo)
        ? ($displaySubmission
            ? route('admin.health_form_submissions.document', [$displaySubmission, 'student_photo'])
            : route('walkin.employeeDocument', ['employeeProfile' => $employeeProfile->id, 'document' => 'student_photo']))
        : null;
    $employeeDocuments = collect($employeeDocuments ?? []);
    $medicalCondition = method_exists($employeeProfile, 'hasMedicalCondition') && $employeeProfile->hasMedicalCondition()
        ? 'With Medical Condition'
        : 'No Medical Condition';
    $examinationFindings = collect([
        $employeeProfile?->head_findings,
        $employeeProfile?->eyes_findings,
        $employeeProfile?->ears_findings,
        $employeeProfile?->throat_findings,
        $employeeProfile?->chest_lungs_findings,
        $employeeProfile?->breast_findings,
        $employeeProfile?->heart_murmur,
        $employeeProfile?->heart_rhythm,
        $employeeProfile?->abdomen_findings,
        $employeeProfile?->extremities_findings,
        $employeeProfile?->vertebral_column_findings,
        $employeeProfile?->skin_findings,
        $employeeProfile?->scars_findings,
    ])->flatten()->filter(fn ($item) => filled($item))->implode(', ');
@endphp

@push('styles')
<style>
    body:has(.health-profile-wrap) .main {
        padding: 10px;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, .86), rgba(255, 255, 255, .86)),
            url('{{ asset('images/admin-bg-light.png') }}') center / cover fixed !important;
    }

    [data-theme="dark"] body:has(.health-profile-wrap) .main {
        background:
            linear-gradient(180deg, rgba(20, 7, 14, .88), rgba(20, 7, 14, .88)),
            url('{{ asset('images/admin-bg-dark.png') }}') center / cover fixed !important;
    }

    .health-profile-wrap {
        width: 100%;
        max-width: 1120px;
        margin: 0 auto;
        display: grid;
        gap: 16px;
        padding-bottom: 124px;
        box-sizing: border-box;
    }

    .health-profile-wrap .profile-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .06);
        padding: 18px;
    }

    .health-profile-wrap .profile-hero-card {
        padding: 20px;
        border-color: rgba(112, 19, 27, .10);
        box-shadow: 0 18px 38px rgba(15, 23, 42, .06);
    }

    .health-profile-wrap .profile-hero-head,
    .health-profile-wrap .profile-version-pane-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
    }

    .health-profile-wrap .profile-hero-head {
        padding-bottom: 16px;
        border-bottom: 1px solid rgba(112, 19, 27, .08);
    }

    .health-profile-wrap .profile-breadcrumb {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: -6px;
        font-size: 12px;
        font-weight: 900;
    }

    .health-profile-wrap .profile-breadcrumb a {
        color: #64748b;
        text-decoration: none;
        transition: color .18s ease;
    }

    .health-profile-wrap .profile-breadcrumb a:hover,
    .health-profile-wrap .profile-breadcrumb a:focus-visible {
        color: #70131B;
        text-decoration: underline;
        outline: none;
    }

    .health-profile-wrap .profile-breadcrumb-separator {
        color: #94a3b8;
    }

    .health-profile-wrap .profile-breadcrumb-current {
        color: #70131B;
    }

    .health-profile-wrap .profile-title,
    .health-profile-wrap .profile-name,
    .health-profile-wrap .profile-version-pane-head h3,
    .health-profile-wrap .profile-meta-v,
    .health-profile-wrap .profile-quick-item strong {
        color: #0f172a;
    }

    .health-profile-wrap .profile-title {
        margin: 0;
        font-size: 21px;
        font-weight: 900;
        line-height: 1.15;
    }

    .health-profile-wrap .profile-sub,
    .health-profile-wrap .profile-course-line,
    .health-profile-wrap .profile-version-pane-head p,
    .health-profile-wrap .profile-quick-item {
        color: #64748b;
    }

    .health-profile-wrap .profile-sub {
        margin: 6px 0 0;
        font-size: 14px;
    }

    .health-profile-wrap .profile-top-btn {
        position: relative;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 44px;
        padding: 11px 18px;
        border: 1px solid #8f2230;
        border-radius: 10px;
        background: #70131B;
        color: #ffffff;
        font-size: 15px;
        font-weight: 900;
        text-decoration: none;
        box-shadow: 0 10px 22px rgba(15, 23, 42, .10);
        transition: transform .18s ease, background .18s ease, color .18s ease, border-color .18s ease, box-shadow .18s ease;
    }

    .health-profile-wrap .profile-top-btn:hover,
    .health-profile-wrap .profile-top-btn:focus-visible {
        transform: translateY(-1px);
        border-color: #facc15;
        background: #facc15;
        color: #70131B;
        box-shadow: 0 14px 24px rgba(112, 19, 27, .16);
        outline: none;
    }

    .health-profile-wrap .profile-hero-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(260px, 340px);
        gap: 18px;
        margin-top: 18px;
    }

    .health-profile-wrap .profile-identity {
        display: grid;
        grid-template-columns: 96px minmax(0, 1fr);
        gap: 18px;
        align-items: center;
    }

    .health-profile-wrap .profile-avatar {
        width: 96px;
        height: 96px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        background: linear-gradient(135deg, #fff7ed, #f1f5f9);
        border: 4px solid #ffffff;
        box-shadow: 0 12px 26px rgba(112, 19, 27, .14);
        color: #70131B;
        font-size: 24px;
        font-weight: 900;
    }

    .health-profile-wrap .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .health-profile-wrap .profile-name {
        margin: 0;
        font-size: 24px;
        font-weight: 900;
        line-height: 1.12;
    }

    .health-profile-wrap .profile-course-line {
        margin: 5px 0 0;
        font-size: 13px;
        font-weight: 800;
    }

    .health-profile-wrap .profile-quick-row {
        display: grid;
        grid-template-columns: minmax(146px, 1.25fr) minmax(86px, .8fr) minmax(72px, .65fr) minmax(188px, 1.55fr) minmax(156px, 1.25fr);
        gap: 12px;
        margin-top: 16px;
    }

    .health-profile-wrap .profile-quick-item {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
        font-size: 11px;
        font-weight: 800;
    }

    .health-profile-wrap .profile-quick-icon,
    .health-profile-wrap .profile-status-shield,
    .health-profile-wrap .profile-tab svg,
    .health-profile-wrap .profile-meta-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }

    .health-profile-wrap .profile-quick-icon {
        width: 28px;
        height: 28px;
        border-radius: 9px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #70131B;
    }

    .health-profile-wrap .profile-quick-icon svg,
    .health-profile-wrap .profile-status-shield svg,
    .health-profile-wrap .profile-tab svg,
    .health-profile-wrap .profile-status-badge svg,
    .health-profile-wrap .doc-link svg {
        width: 16px;
        height: 16px;
    }

    .health-profile-wrap .profile-quick-item strong {
        display: block;
        font-size: 11px;
        font-weight: 900;
        overflow-wrap: anywhere;
    }

    .health-profile-wrap .profile-quick-item:nth-child(4) strong,
    .health-profile-wrap .profile-quick-item:nth-child(5) strong {
        white-space: nowrap;
        word-break: normal;
        overflow-wrap: normal;
    }

    .health-profile-wrap .profile-status-card {
        min-height: 82px;
        border-radius: 14px;
        padding: 12px;
        border: 1px solid #bbf7d0;
        background: linear-gradient(135deg, #f0fdf4, #ecfeff);
        display: flex;
        gap: 10px;
        align-items: center;
        max-width: 285px;
        justify-self: end;
    }

    .health-profile-wrap .profile-status-shield {
        width: 38px;
        height: 38px;
        border-radius: 999px;
        background: #dcfce7;
        color: #16a34a;
    }

    .health-profile-wrap .profile-status-card-title {
        margin: 0 0 2px;
        color: #64748b;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .health-profile-wrap .profile-status-card-value {
        margin: 0;
        color: #16a34a;
        font-size: 16px;
        font-weight: 900;
    }

    .health-profile-wrap .profile-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-top: 6px;
        padding: 4px 8px;
        border: 1px solid transparent;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 900;
    }

    .health-profile-wrap .profile-status-issued { background: #dcfce7; border-color: #86efac; color: #166534; }
    .health-profile-wrap .profile-status-pending { background: #ffedd5; border-color: #fdba74; color: #9a3412; }
    .health-profile-wrap .profile-status-default { background: #f1f5f9; border-color: #cbd5e1; color: #475569; }

    .health-profile-wrap .profile-content-card {
        padding: 0;
        overflow: hidden;
    }

    .health-profile-wrap .profile-switch-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 18px;
        border-bottom: 1px solid #e2e8f0;
        background: #ffffff;
    }

    .health-profile-wrap .profile-switch {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .health-profile-wrap .profile-tab {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 38px;
        padding: 0 12px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #f8fafc;
        color: #475569;
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
        transition: transform .18s ease, background .18s ease, color .18s ease, border-color .18s ease;
    }

    .health-profile-wrap .profile-tab.is-active {
        border-color: #8f2230;
        background: #70131B;
        color: #ffffff;
    }

    .health-profile-wrap .profile-tab:hover,
    .health-profile-wrap .profile-tab:focus-visible {
        transform: translateY(-1px);
        border-color: #facc15;
        background: #facc15;
        color: #70131B;
        outline: none;
    }

    .health-profile-wrap .profile-panel {
        display: none;
        padding: 18px;
    }

    .health-profile-wrap .profile-panel.is-active {
        display: block;
        animation: employee-profile-reveal .24s ease both;
    }

    .health-profile-wrap .profile-version-shell {
        display: grid;
        grid-template-columns: minmax(230px, 260px) minmax(0, 1fr);
        gap: 18px;
        margin-top: 16px;
    }

    .health-profile-wrap .profile-version-sidebar {
        min-width: 0;
        padding: 14px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
    }

    .health-profile-wrap .profile-version-sidebar-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        padding-bottom: 12px;
        border-bottom: 1px solid #e2e8f0;
    }

    .health-profile-wrap .profile-version-sidebar-head h3 {
        margin: 0;
        color: #0f172a;
        font-size: 16px;
        font-weight: 900;
    }

    .health-profile-wrap .profile-version-sidebar-head p {
        margin: 5px 0 0;
        color: #64748b;
        font-size: 11px;
        line-height: 1.35;
    }

    .health-profile-wrap .profile-history-count {
        display: inline-grid;
        place-items: center;
        min-width: 30px;
        height: 30px;
        padding: 0 8px;
        border: 1px solid #cbd5e1;
        border-radius: 7px;
        background: #ffffff;
        color: #70131B;
        font-size: 12px;
        font-weight: 900;
    }

    .health-profile-wrap .profile-version-nav {
        display: grid;
        gap: 8px;
        margin-top: 12px;
        max-height: min(52vh, 460px);
        overflow-y: auto;
        padding-right: 4px;
        scrollbar-width: thin;
        scrollbar-color: #94a3b8 transparent;
    }

    .health-profile-wrap .profile-version-nav::-webkit-scrollbar {
        width: 6px;
    }

    .health-profile-wrap .profile-version-nav::-webkit-scrollbar-track {
        background: transparent;
    }

    .health-profile-wrap .profile-version-nav::-webkit-scrollbar-thumb {
        border-radius: 999px;
        background: #94a3b8;
    }

    .health-profile-wrap .profile-version-choice {
        display: grid;
        grid-template-columns: 42px minmax(0, 1fr);
        align-items: center;
        gap: 10px;
        width: 100%;
        padding: 10px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #ffffff;
        color: #172033;
        text-align: left;
        cursor: pointer;
        transition: transform .18s ease, background .18s ease, color .18s ease, border-color .18s ease;
    }

    .health-profile-wrap .profile-version-choice:hover,
    .health-profile-wrap .profile-version-choice:focus-visible {
        transform: translateY(-1px);
        border-color: #facc15;
        background: #fff7ed;
        outline: none;
    }

    .health-profile-wrap .profile-version-choice.is-active {
        border-color: #facc15;
        background: #70131B;
        color: #ffffff;
    }

    .health-profile-wrap .profile-version-choice-number {
        display: grid;
        place-items: center;
        min-height: 38px;
        border-radius: 7px;
        background: #e2e8f0;
        color: #70131B;
        font-size: 11px;
        font-weight: 900;
    }

    .health-profile-wrap .profile-version-choice.is-active .profile-version-choice-number {
        background: #8f2230;
        color: #facc15;
    }

    .health-profile-wrap .profile-version-choice-copy {
        min-width: 0;
    }

    .health-profile-wrap .profile-version-choice-copy strong,
    .health-profile-wrap .profile-version-choice-copy small {
        display: block;
        overflow-wrap: anywhere;
    }

    .health-profile-wrap .profile-version-choice-copy strong {
        font-size: 12px;
        font-weight: 900;
    }

    .health-profile-wrap .profile-version-choice-copy small {
        margin-top: 3px;
        color: #64748b;
        font-size: 10px;
        font-weight: 800;
    }

    .health-profile-wrap .profile-version-choice.is-active .profile-version-choice-copy small {
        color: #fde68a;
    }

    .health-profile-wrap .profile-version-content {
        min-width: 0;
        padding: 14px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #ffffff;
    }

    .health-profile-wrap .profile-version-pane {
        display: block;
    }

    @keyframes employee-profile-reveal {
        from { opacity: 0; transform: translateY(4px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .health-profile-wrap .profile-version-pane-head {
        align-items: center;
        padding-bottom: 14px;
        border-bottom: 1px solid #e2e8f0;
    }

    .health-profile-wrap .profile-version-pane-head h3 {
        margin: 0;
        font-size: 17px;
        font-weight: 900;
    }

    .health-profile-wrap .profile-version-pane-head p {
        margin: 5px 0 0;
        font-size: 12px;
    }

    .health-profile-wrap .profile-history-badge {
        display: inline-flex;
        align-items: center;
        min-height: 25px;
        padding: 0 9px;
        border: 1px solid #86efac;
        border-radius: 999px;
        background: #dcfce7;
        color: #166534;
        font-size: 10px;
        font-weight: 900;
        white-space: nowrap;
    }

    .health-profile-wrap .profile-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-top: 16px;
    }

    .health-profile-wrap .profile-meta {
        min-width: 0;
        padding: 12px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #f8fafc;
    }

    .health-profile-wrap .profile-meta.is-wide { grid-column: span 2; }
    .health-profile-wrap .profile-meta.is-full { grid-column: 1 / -1; }

    #summaryPanel .profile-grid {
        grid-template-columns: 1fr;
    }

    #summaryPanel .profile-meta {
        display: grid;
        grid-template-columns: minmax(220px, .85fr) minmax(0, 2.15fr);
        align-items: center;
        min-height: 52px;
    }

    #summaryPanel .profile-meta.is-full {
        grid-column: auto;
    }

    #summaryPanel .profile-meta-k {
        margin-bottom: 0;
    }

    #healthPanel .profile-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .health-profile-wrap .profile-meta-k {
        margin-bottom: 4px;
        color: #64748b;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .05em;
        text-transform: uppercase;
    }

    .health-profile-wrap .profile-meta-v {
        font-size: 14px;
        font-weight: 900;
        line-height: 1.3;
        overflow-wrap: anywhere;
    }

    .health-profile-wrap .profile-timeline-card {
        margin-top: 16px;
        padding: 14px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
    }

    .health-profile-wrap .profile-timeline-title {
        margin: 0;
        color: #0f172a;
        font-size: 14px;
        font-weight: 900;
    }

    .health-profile-wrap .profile-timeline-subtitle {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 11px;
    }

    .health-profile-wrap .profile-timeline {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-top: 14px;
    }

    .health-profile-wrap .profile-timeline-step {
        display: grid;
        gap: 4px;
        min-width: 0;
        color: #64748b;
        font-size: 11px;
    }

    .health-profile-wrap .profile-timeline-step strong { color: #0f172a; font-size: 12px; }
    .health-profile-wrap .profile-timeline-step small { line-height: 1.35; }

    .health-profile-wrap .timeline-node {
        display: inline-grid;
        place-items: center;
        width: 25px;
        height: 25px;
        border-radius: 999px;
        background: #dcfce7;
        color: #16a34a;
    }

    .health-profile-wrap .doc-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin-top: 16px;
    }

    .health-profile-wrap .doc-file {
        min-width: 0;
        padding: 14px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
    }

    .health-profile-wrap .doc-file h4 {
        margin: 0;
        color: #0f172a;
        font-size: 13px;
        font-weight: 900;
    }

    .health-profile-wrap .doc-file p {
        margin: 5px 0 0;
        color: #64748b;
        font-size: 11px;
    }

    .health-profile-wrap .doc-actions {
        display: flex;
        gap: 8px;
        margin-top: 12px;
    }

    .health-profile-wrap .doc-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 34px;
        padding: 0 11px;
        border: 1px solid #cbd5e1;
        border-radius: 7px;
        background: #ffffff;
        color: #172033;
        font-size: 11px;
        font-weight: 900;
        text-decoration: none;
        transition: transform .18s ease, background .18s ease, color .18s ease, border-color .18s ease;
    }

    .health-profile-wrap .doc-link:hover,
    .health-profile-wrap .doc-link:focus-visible {
        transform: translateY(-1px);
        border-color: #facc15;
        background: #facc15;
        color: #70131B;
        outline: none;
    }

    .health-profile-wrap .doc-missing {
        margin-top: 12px;
        color: #64748b;
        font-size: 11px;
        font-weight: 800;
    }

    [data-theme="dark"] .health-profile-wrap .profile-card,
    [data-theme="dark"] .health-profile-wrap .profile-switch-head {
        background: #111827;
        border-color: #334155;
    }

    [data-theme="dark"] .health-profile-wrap .profile-title,
    [data-theme="dark"] .health-profile-wrap .profile-name,
    [data-theme="dark"] .health-profile-wrap .profile-version-pane-head h3,
    [data-theme="dark"] .health-profile-wrap .profile-meta-v,
    [data-theme="dark"] .health-profile-wrap .profile-quick-item strong,
    [data-theme="dark"] .health-profile-wrap .profile-timeline-title,
    [data-theme="dark"] .health-profile-wrap .profile-timeline-step strong,
    [data-theme="dark"] .health-profile-wrap .doc-file h4 {
        color: #f8fafc;
    }

    [data-theme="dark"] .health-profile-wrap .profile-sub,
    [data-theme="dark"] .health-profile-wrap .profile-course-line,
    [data-theme="dark"] .health-profile-wrap .profile-quick-item,
    [data-theme="dark"] .health-profile-wrap .profile-version-pane-head p,
    [data-theme="dark"] .health-profile-wrap .profile-meta-k,
    [data-theme="dark"] .health-profile-wrap .profile-timeline-subtitle,
    [data-theme="dark"] .health-profile-wrap .profile-timeline-step,
    [data-theme="dark"] .health-profile-wrap .doc-file p,
    [data-theme="dark"] .health-profile-wrap .doc-missing {
        color: #cbd5e1;
    }

    [data-theme="dark"] .health-profile-wrap .profile-breadcrumb a {
        color: #ffffff;
    }

    [data-theme="dark"] .health-profile-wrap .profile-breadcrumb a:hover,
    [data-theme="dark"] .health-profile-wrap .profile-breadcrumb a:focus-visible {
        color: #facc15;
    }

    [data-theme="dark"] .health-profile-wrap .profile-breadcrumb-current {
        color: #facc15;
    }

    [data-theme="dark"] .health-profile-wrap .profile-status-card-title {
        color: #64748b;
    }

    [data-theme="dark"] .health-profile-wrap .profile-status-card-value {
        color: #16a34a;
    }

    [data-theme="dark"] .health-profile-wrap .profile-hero-head,
    [data-theme="dark"] .health-profile-wrap .profile-version-pane-head {
        border-color: #334155;
    }

    [data-theme="dark"] .health-profile-wrap .profile-meta,
    [data-theme="dark"] .health-profile-wrap .profile-timeline-card,
    [data-theme="dark"] .health-profile-wrap .doc-file,
    [data-theme="dark"] .health-profile-wrap .profile-version-sidebar,
    [data-theme="dark"] .health-profile-wrap .profile-version-content {
        background: #182334;
        border-color: #475569;
    }

    [data-theme="dark"] .health-profile-wrap .profile-version-sidebar-head,
    [data-theme="dark"] .health-profile-wrap .profile-version-pane-head {
        border-color: #334155;
    }

    [data-theme="dark"] .health-profile-wrap .profile-version-sidebar-head h3,
    [data-theme="dark"] .health-profile-wrap .profile-version-choice,
    [data-theme="dark"] .health-profile-wrap .profile-version-choice-copy strong {
        color: #f8fafc;
    }

    [data-theme="dark"] .health-profile-wrap .profile-version-sidebar-head p,
    [data-theme="dark"] .health-profile-wrap .profile-version-choice-copy small {
        color: #cbd5e1;
    }

    [data-theme="dark"] .health-profile-wrap .profile-history-count,
    [data-theme="dark"] .health-profile-wrap .profile-version-choice {
        background: #1e293b;
        border-color: #475569;
    }

    [data-theme="dark"] .health-profile-wrap .profile-history-count {
        color: #facc15;
    }

    [data-theme="dark"] .health-profile-wrap .profile-version-nav {
        scrollbar-color: #64748b transparent;
    }

    [data-theme="dark"] .health-profile-wrap .profile-version-nav::-webkit-scrollbar-thumb {
        background: #64748b;
    }

    [data-theme="dark"] .health-profile-wrap .profile-version-choice-number {
        background: #334155;
        color: #facc15;
    }

    [data-theme="dark"] .health-profile-wrap .profile-version-choice.is-active {
        background: #70131B;
        border-color: #facc15;
    }

    [data-theme="dark"] .health-profile-wrap .profile-version-choice.is-active .profile-version-choice-copy small {
        color: #fde68a;
    }

    [data-theme="dark"] .health-profile-wrap .profile-quick-icon,
    [data-theme="dark"] .health-profile-wrap .profile-tab,
    [data-theme="dark"] .health-profile-wrap .doc-link {
        background: #1e293b;
        border-color: #475569;
        color: #f8fafc;
    }

    [data-theme="dark"] .health-profile-wrap .profile-top-btn {
        background: #70131B;
        border-color: #8f2230;
        color: #ffffff;
    }

    [data-theme="dark"] .health-profile-wrap .profile-tab.is-active {
        background: #70131B;
        border-color: #8f2230;
        color: #ffffff;
    }

    [data-theme="dark"] .health-profile-wrap .profile-tab:hover,
    [data-theme="dark"] .health-profile-wrap .profile-tab:focus-visible,
    [data-theme="dark"] .health-profile-wrap .profile-top-btn:hover,
    [data-theme="dark"] .health-profile-wrap .profile-top-btn:focus-visible,
    [data-theme="dark"] .health-profile-wrap .doc-link:hover,
    [data-theme="dark"] .health-profile-wrap .doc-link:focus-visible {
        background: #facc15;
        border-color: #facc15;
        color: #70131B;
    }

    [data-theme="dark"] .health-profile-wrap .profile-avatar {
        background: #1e293b;
        border-color: #334155;
        color: #facc15;
    }

    [data-theme="dark"] .health-profile-wrap .profile-status-default {
        background: #1e293b;
        border-color: #475569;
        color: #e2e8f0;
    }

    [data-theme="dark"] .health-profile-wrap .profile-status-card {
        background: #0f2d2b;
        border-color: rgba(74, 222, 128, .55);
    }

    [data-theme="dark"] .health-profile-wrap .profile-status-shield {
        background: #14532d;
        color: #86efac;
    }

    [data-theme="dark"] .health-profile-wrap .profile-status-card-title {
        color: #94a3b8;
    }

    [data-theme="dark"] .health-profile-wrap .profile-status-card-value {
        color: #22c55e;
    }

    [data-theme="dark"] .health-profile-wrap .profile-status-badge.profile-status-issued {
        background: rgba(21, 128, 61, .25);
        border-color: rgba(74, 222, 128, .55);
        color: #bbf7d0;
    }

    [data-theme="dark"] .health-profile-wrap .profile-meta {
        background: #111827;
        border-color: #334155;
    }

    .health-profile-wrap .employee-profile-actions {
        position: relative;
        flex: 0 0 auto;
    }

    .health-profile-wrap .employee-profile-actions-toggle {
        width: 42px;
        height: 42px;
        display: inline-grid;
        place-items: center;
        border: 1px solid rgba(112, 19, 27, .18);
        border-radius: 10px;
        background: #70131B;
        color: #ffffff;
        cursor: pointer;
        transition: transform .18s ease, background .18s ease, color .18s ease, border-color .18s ease, box-shadow .18s ease;
    }

    .health-profile-wrap .employee-profile-actions-toggle svg {
        width: 21px;
        height: 21px;
    }

    .health-profile-wrap .employee-profile-actions-toggle:hover,
    .health-profile-wrap .employee-profile-actions-toggle:focus-visible,
    .health-profile-wrap .employee-profile-actions.is-open .employee-profile-actions-toggle {
        transform: translateY(-1px);
        border-color: #facc15;
        background: #facc15;
        color: #70131B;
        box-shadow: 0 0 0 3px rgba(250, 204, 21, .14), 0 10px 22px rgba(112, 19, 27, .16);
        outline: none;
    }

    .health-profile-wrap .employee-profile-actions-menu {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        z-index: 90;
        width: min(232px, calc(100vw - 40px));
        display: none;
        gap: 8px;
        padding: 8px;
        border: 1px solid rgba(112, 19, 27, .16);
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 20px 42px rgba(15, 23, 42, .18);
    }

    .health-profile-wrap .employee-profile-actions.is-open .employee-profile-actions-menu {
        display: grid;
    }

    .health-profile-wrap .employee-profile-actions-menu button {
        position: relative;
        overflow: hidden;
        width: 100%;
        min-height: 42px;
        padding: 10px 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        border: 1px solid rgba(112, 19, 27, .14);
        border-radius: 8px;
        background: #fffafa;
        color: #70131B;
        font: inherit;
        font-size: 12px;
        font-weight: 900;
        text-align: left;
        cursor: pointer;
        transition: transform .18s ease, background .18s ease, color .18s ease, border-color .18s ease;
    }

    .health-profile-wrap .employee-profile-actions-menu button:hover,
    .health-profile-wrap .employee-profile-actions-menu button:focus-visible {
        transform: translateY(-1px);
        border-color: #facc15;
        background: #facc15;
        color: #70131B;
        outline: none;
    }

    .employee-action-modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 2147482500;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(15, 23, 42, .62);
    }

    .employee-action-modal.is-open {
        display: flex;
    }

    .employee-action-card {
        width: min(720px, 100%);
        max-height: min(720px, calc(100vh - 40px));
        overflow: auto;
        border: 1px solid rgba(250, 204, 21, .34);
        border-bottom: 4px solid #70131B;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 24px 60px rgba(15, 23, 42, .28);
    }

    .employee-action-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 18px 20px;
        border-radius: 17px 17px 0 0;
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #ffffff;
    }

    .employee-action-head-main {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .employee-action-head-icon {
        width: 48px;
        height: 48px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        border: 1px solid rgba(255, 255, 255, .26);
        border-radius: 10px;
        background: rgba(255, 255, 255, .1);
        color: #facc15;
    }

    .employee-action-head-icon svg {
        width: 22px;
        height: 22px;
    }

    .employee-action-head h3 {
        margin: 0;
        color: #ffffff;
        font-size: 18px;
        font-weight: 900;
    }

    .employee-action-head p {
        margin: 4px 0 0;
        color: rgba(255, 255, 255, .88);
        font-size: 13px;
    }

    .employee-action-close {
        position: relative;
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        border: 1px solid rgba(255, 255, 255, .24);
        border-radius: 999px;
        background: rgba(112, 19, 27, .45);
        color: #ffffff;
        cursor: pointer;
        transition: transform .18s ease, background .18s ease, color .18s ease, border-color .18s ease;
    }

    .employee-action-close:hover,
    .employee-action-close:focus-visible {
        transform: translateY(-1px);
        border-color: #facc15;
        background: #facc15;
        color: #70131B;
        outline: none;
    }

    .employee-action-close svg {
        width: 18px;
        height: 18px;
    }

    .employee-action-body {
        display: grid;
        gap: 16px;
        padding: 20px;
    }

    .employee-action-field {
        display: grid;
        gap: 7px;
    }

    .employee-action-field label,
    .employee-action-checks-title {
        color: #334155;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .02em;
        text-transform: uppercase;
    }

    .employee-action-field select,
    .employee-action-field textarea {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        background: #f8fafc;
        color: #172033;
        font: inherit;
        font-size: 13px;
        font-weight: 700;
    }

    .employee-action-field select {
        min-height: 48px;
        padding: 0 12px;
    }

    .employee-action-field textarea {
        min-height: 100px;
        padding: 12px;
        resize: vertical;
    }

    .employee-action-field select:focus,
    .employee-action-field textarea:focus {
        border-color: #facc15;
        box-shadow: 0 0 0 3px rgba(250, 204, 21, .14);
        outline: none;
    }

    .employee-action-select-wrap {
        position: relative;
        width: 100%;
    }

    .employee-action-select-source {
        position: absolute !important;
        inset: auto auto 0 0;
        width: 1px !important;
        min-height: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        border: 0 !important;
        opacity: 0;
        pointer-events: none;
    }

    .health-profile-wrap button.employee-action-select-trigger {
        position: relative;
        width: 100%;
        min-height: 48px;
        padding: 0 42px 0 12px;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        background: #f8fafc;
        color: #172033;
        font: inherit;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.35;
        text-align: left;
        cursor: pointer;
        transition: border-color .18s ease, box-shadow .18s ease, background .18s ease, color .18s ease;
    }

    .health-profile-wrap button.employee-action-select-trigger::after {
        content: "";
        position: absolute;
        right: 15px;
        top: 50%;
        width: 9px;
        height: 9px;
        border-right: 2px solid currentColor;
        border-bottom: 2px solid currentColor;
        transform: translateY(-68%) rotate(45deg);
        transition: transform .18s ease;
        pointer-events: none;
    }

    .employee-action-select-wrap.is-open .employee-action-select-trigger {
        border-color: #8f2230;
        box-shadow: 0 0 0 3px rgba(112, 19, 27, .12);
        outline: none;
    }

    .employee-action-select-wrap.is-open .employee-action-select-trigger::after {
        transform: translateY(-30%) rotate(225deg);
    }

    .employee-action-select-menu {
        position: absolute;
        z-index: 90;
        top: calc(100% + 8px);
        left: 0;
        right: 0;
        display: none;
        gap: 7px;
        max-height: 220px;
        overflow-y: auto;
        padding: 8px;
        border: 1px solid #efcaca;
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 18px 36px rgba(58, 12, 18, .2);
        scrollbar-width: thin;
        scrollbar-color: #8f2230 transparent;
    }

    .employee-action-select-wrap.is-open .employee-action-select-menu {
        display: grid;
        animation: employeeActionDropdownIn .18s ease both;
    }

    .employee-action-select-menu::-webkit-scrollbar {
        width: 6px;
    }

    .employee-action-select-menu::-webkit-scrollbar-track {
        background: transparent;
    }

    .employee-action-select-menu::-webkit-scrollbar-thumb {
        border-radius: 999px;
        background: #8f2230;
    }

    .health-profile-wrap button.employee-action-select-option {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        width: 100%;
        min-height: 40px;
        padding: 9px 12px;
        border: 1px solid #efcaca;
        border-radius: 8px;
        background: #ffffff;
        color: #70131B;
        font: inherit;
        font-size: 13px;
        font-weight: 800;
        line-height: 1.3;
        text-align: left;
        cursor: pointer;
        box-shadow: none;
        transition: border-color .2s ease, background .2s ease, color .2s ease, transform .2s ease;
    }

    .health-profile-wrap button.employee-action-select-option::after {
        content: "";
        position: absolute;
        z-index: 0;
        top: -45%;
        left: -130%;
        width: 120%;
        height: 190%;
        background: linear-gradient(115deg, rgba(255, 247, 181, 0) 0%, rgba(255, 247, 181, .78) 46%, rgba(255, 247, 181, 0) 100%);
        transform: skewX(-20deg);
        transition: left .9s ease;
        pointer-events: none;
    }

    .health-profile-wrap button.employee-action-select-option > span {
        position: relative;
        z-index: 1;
    }

    .health-profile-wrap button.employee-action-select-option:hover,
    .health-profile-wrap button.employee-action-select-option:focus-visible {
        transform: translateY(-1px);
        border-color: #facc15;
        background: #facc15;
        color: #70131B;
        outline: none;
        box-shadow: 0 8px 18px rgba(250, 204, 21, .22);
    }

    .health-profile-wrap button.employee-action-select-option:hover::after,
    .health-profile-wrap button.employee-action-select-option:focus-visible::after {
        left: 125%;
    }

    .health-profile-wrap button.employee-action-select-option.is-selected {
        border-color: #70131B;
        background: #70131B;
        color: #ffffff;
    }

    @keyframes employeeActionDropdownIn {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .employee-action-checks {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
    }

    .employee-action-check {
        display: flex;
        align-items: center;
        gap: 8px;
        min-height: 40px;
        padding: 8px 10px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #f8fafc;
        color: #334155;
        font-size: 12px;
        font-weight: 800;
    }

    .employee-action-check input {
        accent-color: #70131B;
    }

    .employee-action-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
    }

    .employee-action-cancel,
    .employee-action-submit {
        min-height: 42px;
        padding: 10px 16px;
        border-radius: 9px;
        font: inherit;
        font-size: 13px;
        font-weight: 900;
        cursor: pointer;
    }

    .employee-action-cancel {
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #334155;
    }

    .employee-action-submit {
        border: 1px solid #8f2230;
        background: #70131B;
        color: #ffffff;
        transition: transform .18s ease, background .18s ease, color .18s ease, border-color .18s ease;
    }

    .employee-action-cancel:hover,
    .employee-action-cancel:focus-visible,
    .employee-action-submit:hover,
    .employee-action-submit:focus-visible {
        transform: translateY(-1px);
        border-color: #facc15;
        background: #facc15;
        color: #70131B;
        outline: none;
    }

    [data-theme="dark"] .health-profile-wrap .employee-profile-actions-toggle {
        border-color: #8f2230;
        background: #70131B;
        color: #ffffff;
    }

    [data-theme="dark"] .health-profile-wrap .employee-profile-actions-menu {
        border-color: rgba(250, 204, 21, .28);
        background: #111827;
    }

    [data-theme="dark"] .health-profile-wrap .employee-profile-actions-menu button {
        border-color: #8f2230;
        background: #70131B;
        color: #ffffff;
    }

    [data-theme="dark"] .employee-action-card {
        background: #111827;
        border-color: rgba(250, 204, 21, .34);
    }

    [data-theme="dark"] .employee-action-field label,
    [data-theme="dark"] .employee-action-checks-title {
        color: #cbd5e1;
    }

    [data-theme="dark"] .employee-action-field select,
    [data-theme="dark"] .employee-action-field textarea,
    [data-theme="dark"] .employee-action-check {
        border-color: #475569;
        background: #1e293b;
        color: #f8fafc;
    }

    [data-theme="dark"] .health-profile-wrap button.employee-action-select-trigger {
        border-color: #475569;
        background: #182334;
        color: #f8fafc;
    }

    [data-theme="dark"] .employee-action-select-menu {
        border-color: #475569;
        background: #111827;
        box-shadow: 0 18px 38px rgba(0, 0, 0, .48);
    }

    [data-theme="dark"] .health-profile-wrap button.employee-action-select-option {
        border-color: #475569;
        background: #182334;
        color: #f8fafc;
    }

    [data-theme="dark"] .health-profile-wrap button.employee-action-select-option.is-selected {
        border-color: #9f1d2d;
        background: #9f1d2d;
        color: #ffffff;
    }

    [data-theme="dark"] .health-profile-wrap button.employee-action-select-option:hover,
    [data-theme="dark"] .health-profile-wrap button.employee-action-select-option:focus-visible,
    [data-theme="dark"] .health-profile-wrap button.employee-action-select-option.is-selected:hover,
    [data-theme="dark"] .health-profile-wrap button.employee-action-select-option.is-selected:focus-visible {
        border-color: #facc15;
        background: #facc15;
        color: #70131B;
    }

    [data-theme="dark"] .employee-action-cancel {
        border-color: #475569;
        background: #1e293b;
        color: #f8fafc;
    }

    [data-theme="dark"] .employee-action-submit {
        border-color: #8f2230;
        background: #70131B;
        color: #ffffff;
    }

    @media (max-width: 1024px) {
        .health-profile-wrap .profile-hero-layout { grid-template-columns: 1fr; }
        .health-profile-wrap .profile-status-card { max-width: none; justify-self: stretch; }
        .health-profile-wrap .profile-quick-row { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .health-profile-wrap .profile-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .health-profile-wrap .profile-timeline { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        #summaryPanel .profile-grid { grid-template-columns: 1fr; }
        #healthPanel .profile-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .health-profile-wrap .profile-version-shell { grid-template-columns: minmax(200px, 230px) minmax(0, 1fr); }
    }

    @media (max-width: 768px) {
        .health-profile-wrap { padding-right: 0; padding-bottom: 152px; }
        .health-profile-wrap .profile-hero-head,
        .health-profile-wrap .profile-version-pane-head { flex-direction: column; }
        .health-profile-wrap .profile-identity { grid-template-columns: 76px minmax(0, 1fr); }
        .health-profile-wrap .profile-avatar { width: 76px; height: 76px; }
        .health-profile-wrap .profile-name { font-size: 20px; }
        .health-profile-wrap .profile-quick-row,
        .health-profile-wrap .profile-grid,
        .health-profile-wrap .profile-timeline,
        .health-profile-wrap .doc-grid { grid-template-columns: 1fr; }
        #summaryPanel .profile-meta { grid-template-columns: 1fr; gap: 4px; }
        #healthPanel .profile-grid { grid-template-columns: 1fr; }
        .health-profile-wrap .profile-meta.is-wide,
        .health-profile-wrap .profile-meta.is-full { grid-column: auto; }
        .health-profile-wrap .profile-version-shell { grid-template-columns: 1fr; }
        .health-profile-wrap .profile-switch { flex-wrap: nowrap; overflow-x: auto; padding-bottom: 3px; }
        .health-profile-wrap .profile-tab { flex: 0 0 auto; }
        .health-profile-wrap .profile-switch-head { align-items: flex-start; }
        .employee-action-checks { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="health-profile-wrap">
    <nav class="profile-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('admin.health_records') }}">Health Records</a>
        <span class="profile-breadcrumb-separator" aria-hidden="true">&rarr;</span>
        <span class="profile-breadcrumb-current" aria-current="page">Employee Health Profile</span>
    </nav>

    <div class="profile-card profile-hero-card">
        <div class="profile-hero-head">
            <div>
                <h1 class="profile-title">Employee Health Profile</h1>
                <p class="profile-sub">Issued employee health profile details and submitted documents.</p>
            </div>
        </div>

        <div class="profile-hero-layout">
            <div>
                <div class="profile-identity">
                    <div class="profile-avatar">
                        @if($employeePhotoUrl)
                            <img src="{{ $employeePhotoUrl }}" alt="{{ $displayName }}">
                        @else
                            <span aria-hidden="true">{{ $profileInitials }}</span>
                        @endif
                    </div>
                    <div>
                        <h2 class="profile-name">{{ $displayName }}</h2>
                        <p class="profile-course-line">{{ $profileOffice }}</p>
                    </div>
                </div>

                <div class="profile-quick-row">
                    <div class="profile-quick-item"><span class="profile-quick-icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6"><path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.336a6.721 6.721 0 0 1-3.17.789 6.721 6.721 0 0 1-3.168-.789 3.376 3.376 0 0 1 6.338 0Z" /></svg></span><span>Employee No.<strong>{{ $formatValue($employeeProfile?->employee_number ?: $employeeUser?->employee_number) }}</strong></span></div>
                    <div class="profile-quick-item"><span class="profile-quick-icon"><x-outline-icon name="user-circle" /></span><span>Gender<strong>{{ $formatValue($employeeProfile?->sex) }}</strong></span></div>
                    <div class="profile-quick-item"><span class="profile-quick-icon"><x-outline-icon name="calendar-days" /></span><span>Age<strong>{{ $formatValue($employeeProfile?->age) }}</strong></span></div>
                    <div class="profile-quick-item"><span class="profile-quick-icon"><x-outline-icon name="envelope" /></span><span>Email<strong>{{ $formatValue($employeeUser?->email) }}</strong></span></div>
                    <div class="profile-quick-item"><span class="profile-quick-icon"><x-outline-icon name="phone" /></span><span>Contact<strong>{{ $formatValue($employeeProfile?->contact_no ?: $employeeUser?->contact_no) }}</strong></span></div>
                </div>
            </div>

            <div class="profile-status-card">
                <span class="profile-status-shield"><x-outline-icon name="check" /></span>
                <div>
                    <p class="profile-status-card-title">Health Record Status</p>
                    <p class="profile-status-card-value">{{ $status }}</p>
                    <span class="profile-status-badge {{ $statusClass }}"><x-outline-icon name="check" />{{ $status }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="profile-card profile-content-card">
        <div class="profile-switch-head">
            <div class="profile-switch" role="tablist" aria-label="Employee health profile sections">
                <button type="button" class="profile-tab is-active" data-profile-tab-target="summaryPanel" role="tab" aria-selected="true"><x-outline-icon name="user-circle" /><span>Personal Information</span></button>
                <button type="button" class="profile-tab" data-profile-tab-target="healthPanel" role="tab" aria-selected="false"><x-outline-icon name="information-circle" /><span>Health Profile</span></button>
                <button type="button" class="profile-tab" data-profile-tab-target="docsPanel" role="tab" aria-selected="false"><x-outline-icon name="document-text" /><span>Uploaded Documents</span></button>
            </div>
            @if($canRequestEmployeeHealthActions)
                <div class="employee-profile-actions" id="employeeProfileActions">
                    <button
                        type="button"
                        class="employee-profile-actions-toggle"
                        id="employeeProfileActionsToggle"
                        aria-label="Open employee health profile actions"
                        aria-haspopup="menu"
                        aria-expanded="false"
                        aria-controls="employeeProfileActionsMenu"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z" />
                        </svg>
                    </button>
                    <div class="employee-profile-actions-menu" id="employeeProfileActionsMenu" role="menu" aria-hidden="true">
                        <button type="button" id="openNewEmployeeHealthFormModal" role="menuitem">
                            <span>Request New Health Form</span>
                            <span aria-hidden="true">+</span>
                        </button>
                        <button type="button" id="openEmployeeCorrectionModal" role="menuitem">
                            <span>Request File Correction</span>
                            <span aria-hidden="true">&rarr;</span>
                        </button>
                    </div>
                </div>
            @endif
        </div>

        <section class="profile-panel is-active" id="summaryPanel" role="tabpanel">
            <div class="profile-version-pane-head">
                <div><h3>Personal Information</h3><p>Information submitted in the employee health form.</p></div>
                <span class="profile-history-badge">Employee</span>
            </div>
            <div class="profile-grid">
                <div class="profile-meta"><div class="profile-meta-k">Employee Name</div><div class="profile-meta-v">{{ $displayName }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Employee Number</div><div class="profile-meta-v">{{ $formatValue($employeeProfile?->employee_number ?: $employeeUser?->employee_number) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Email</div><div class="profile-meta-v">{{ $formatValue($employeeUser?->email) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">First Name</div><div class="profile-meta-v">{{ $formatValue($employeeProfile?->first_name) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Middle Name</div><div class="profile-meta-v">{{ $formatValue($employeeProfile?->middle_name) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Last Name</div><div class="profile-meta-v">{{ $formatValue($employeeProfile?->last_name) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Birthday</div><div class="profile-meta-v">{{ $formatDate($employeeProfile?->birthday) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Age</div><div class="profile-meta-v">{{ $formatValue($employeeProfile?->age) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Sex</div><div class="profile-meta-v">{{ $formatValue($employeeProfile?->sex) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Civil Status</div><div class="profile-meta-v">{{ $formatValue($employeeProfile?->civil_status) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Office / Department</div><div class="profile-meta-v">{{ $formatValue($employeeProfile?->office) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Health Form Category</div><div class="profile-meta-v">{{ $formatValue($employeeProfile?->health_form_category) }}</div></div>
                <div class="profile-meta is-full"><div class="profile-meta-k">Home Address</div><div class="profile-meta-v">{{ $formatValue($employeeProfile?->home_address) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Contact No.</div><div class="profile-meta-v">{{ $formatValue($employeeProfile?->contact_no ?: $employeeUser?->contact_no) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Emergency Contact</div><div class="profile-meta-v">{{ $formatValue($employeeProfile?->emergency_contact_person) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Emergency Contact No.</div><div class="profile-meta-v">{{ $formatValue($employeeProfile?->emergency_contact_no) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">School Year</div><div class="profile-meta-v">{{ $formatValue($employeeProfile?->school_year) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Form Date</div><div class="profile-meta-v">{{ $formatDate($employeeProfile?->form_date) }}</div></div>
            </div>
        </section>

        <section class="profile-panel" id="healthPanel" role="tabpanel" hidden>
            <div class="profile-version-pane-head">
                <div><h3>Current Employee Health Profile</h3><p>Latest medical information and examination details.</p></div>
                <span class="profile-history-badge">Current</span>
            </div>
            <div class="profile-grid">
                <div class="profile-meta"><div class="profile-meta-k">Medical Condition</div><div class="profile-meta-v">{{ $medicalCondition }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Submission Status</div><div class="profile-meta-v">{{ $formatValue($employeeProfile?->submission_status) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Documents Valid</div><div class="profile-meta-v">{{ $yesNo($employeeProfile?->documents_valid) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Height</div><div class="profile-meta-v">{{ $formatValue($employeeProfile?->height) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Weight</div><div class="profile-meta-v">{{ $formatValue($employeeProfile?->weight) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">BMI</div><div class="profile-meta-v">{{ $formatValue($employeeProfile?->bmi) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Blood Pressure</div><div class="profile-meta-v">{{ $formatValue($employeeProfile?->bp) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Pulse Rate</div><div class="profile-meta-v">{{ $formatValue($employeeProfile?->hr) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Respiratory Rate</div><div class="profile-meta-v">{{ $formatValue($employeeProfile?->rr) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Temperature</div><div class="profile-meta-v">{{ $formatValue($employeeProfile?->temperature) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Distress Status</div><div class="profile-meta-v">{{ $formatValue($employeeProfile?->vital_signs_distress_status) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Fit Status</div><div class="profile-meta-v">{{ $formatValue($employeeProfile?->fit_status) }}</div></div>
                <div class="profile-meta is-wide"><div class="profile-meta-k">Past Medical History</div><div class="profile-meta-v">{{ $formatValue($employeeProfile?->past_medical_history) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Hospitalization</div><div class="profile-meta-v">{{ $yesNo($employeeProfile?->previous_hospitalization) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Hospitalization Details</div><div class="profile-meta-v">{{ $formatValue($employeeProfile?->previous_hospitalization_details) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Operation / Surgery</div><div class="profile-meta-v">{{ $yesNo($employeeProfile?->operation_surgery) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Operation Details</div><div class="profile-meta-v">{{ $formatValue($employeeProfile?->operation_surgery_details) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Family History</div><div class="profile-meta-v">{{ $formatValue($employeeProfile?->family_history) }}</div></div>
                <div class="profile-meta is-wide"><div class="profile-meta-k">Current Medications</div><div class="profile-meta-v">{{ $formatValue($employeeProfile?->current_medications) }}</div></div>
                <div class="profile-meta is-full"><div class="profile-meta-k">Allergies</div><div class="profile-meta-v">{{ $formatValue($employeeProfile?->allergies) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Cigarette Smoking</div><div class="profile-meta-v">{{ $yesNo($employeeProfile?->cigarette_smoking) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Alcohol Drinking</div><div class="profile-meta-v">{{ $yesNo($employeeProfile?->alcohol_drinking) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Traveled Abroad</div><div class="profile-meta-v">{{ $yesNo($employeeProfile?->traveled_abroad) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">PWD / Disability</div><div class="profile-meta-v">{{ $yesNo($employeeProfile?->has_disability) }}</div></div>
                <div class="profile-meta is-wide"><div class="profile-meta-k">Disability Type</div><div class="profile-meta-v">{{ $formatValue($employeeProfile?->disability_type) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Chest X-ray Result</div><div class="profile-meta-v">{{ $formatValue($employeeProfile?->chest_xray_result) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Working Impression</div><div class="profile-meta-v">{{ $formatValue($employeeProfile?->working_impression) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Follow-up Date</div><div class="profile-meta-v">{{ $formatDate($employeeProfile?->follow_up_on) }}</div></div>
                <div class="profile-meta is-wide"><div class="profile-meta-k">For Work-up</div><div class="profile-meta-v">{{ $formatValue($employeeProfile?->for_work_up) }}</div></div>
                <div class="profile-meta is-wide"><div class="profile-meta-k">Referred To</div><div class="profile-meta-v">{{ $formatValue($employeeProfile?->referred_to) }}</div></div>
                <div class="profile-meta is-full"><div class="profile-meta-k">Examination Findings</div><div class="profile-meta-v">{{ $formatValue($examinationFindings) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Certified At</div><div class="profile-meta-v">{{ $formatDateTime($employeeProfile?->certified_at) }}</div></div>
                <div class="profile-meta"><div class="profile-meta-k">Verified At</div><div class="profile-meta-v">{{ $formatDateTime($employeeProfile?->verified_at) }}</div></div>
            </div>
            <div class="profile-timeline-card">
                <h4 class="profile-timeline-title">Employee Health Record Timeline</h4>
                <p class="profile-timeline-subtitle">Important events recorded for this employee profile.</p>
                <div class="profile-timeline">
                    <div class="profile-timeline-step"><span class="timeline-node"><x-outline-icon name="check" /></span><strong>Profile Submitted</strong><span>{{ $formatDate($employeeProfile?->form_date) }}</span><small>Employee health information was submitted.</small></div>
                    <div class="profile-timeline-step"><span class="timeline-node"><x-outline-icon name="check" /></span><strong>Assessment Completed</strong><span>{{ $formatDate($employeeProfile?->certified_at) }}</span><small>Assessment details were recorded.</small></div>
                    <div class="profile-timeline-step"><span class="timeline-node"><x-outline-icon name="check" /></span><strong>Verified</strong><span>{{ $formatDate($employeeProfile?->verified_at) }}</span><small>Profile verification status.</small></div>
                    <div class="profile-timeline-step"><span class="timeline-node"><x-outline-icon name="check" /></span><strong>{{ $status }}</strong><span>{{ $formatDate($employeeProfile?->updated_at) }}</span><small>Current clearance state.</small></div>
                </div>
            </div>
        </section>

        <section class="profile-panel" id="docsPanel" role="tabpanel" hidden>
            @php
                $employeeVersionHistory = collect($employeeVersionHistory ?? []);
                $employeeCurrentVersion = $employeeVersionHistory->first(fn (array $version): bool => (bool) ($version['is_current'] ?? false))
                    ?: $employeeVersionHistory->first();
                $employeeCurrentVersionNumber = (int) ($employeeCurrentVersion['version'] ?? 1);
            @endphp
            <div class="profile-version-shell profile-documents-version-shell">
                <aside class="profile-version-sidebar" aria-label="Uploaded Documents versions">
                    <div class="profile-version-sidebar-head">
                        <div>
                            <h3>Document Versions</h3>
                            <p>Select a version to review its saved files.</p>
                        </div>
                        <span class="profile-history-count">{{ $employeeVersionHistory->count() }}</span>
                    </div>
                    <div class="profile-version-nav" role="tablist" aria-label="Uploaded Documents versions">
                        @foreach($employeeVersionHistory as $version)
                            @php
                                $versionNumber = (int) ($version['version'] ?? 1);
                                $versionSubmission = $version['submission'] ?? null;
                                $versionTarget = 'employee-version-pane-' . $versionNumber;
                                $versionIsActive = $versionNumber === $employeeCurrentVersionNumber;
                                $versionLabel = ($version['is_current'] ?? false)
                                    ? 'Current Documents'
                                    : (($versionSubmission?->status ?? '') === \App\Models\HealthFormSubmission::STATUS_SUBMITTED
                                        ? 'Pending Review'
                                        : 'Previous Documents');
                            @endphp
                            <button
                                type="button"
                                class="profile-version-choice{{ $versionIsActive ? ' is-active' : '' }}"
                                role="tab"
                                aria-selected="{{ $versionIsActive ? 'true' : 'false' }}"
                                aria-controls="{{ $versionTarget }}"
                                data-profile-version-target="{{ $versionTarget }}"
                            >
                                <span class="profile-version-choice-number">V{{ $versionNumber }}</span>
                                <span class="profile-version-choice-copy">
                                    <strong>{{ $versionLabel }}</strong>
                                    <small>{{ $formatDate($versionSubmission?->approved_at ?: $versionSubmission?->submitted_at ?: $employeeProfile?->form_date) }}</small>
                                </span>
                            </button>
                        @endforeach
                    </div>
                </aside>

                <div class="profile-version-content">
                    @foreach($employeeVersionHistory as $version)
                        @php
                            $versionNumber = (int) ($version['version'] ?? 1);
                            $versionSubmission = $version['submission'] ?? null;
                            $versionIsActive = $versionNumber === $employeeCurrentVersionNumber;
                            $versionDocuments = collect($version['documents'] ?? []);
                        @endphp
                        <section
                            class="profile-version-pane{{ $versionIsActive ? ' is-active' : '' }}"
                            id="employee-version-pane-{{ $versionNumber }}"
                            role="tabpanel"
                            aria-hidden="{{ $versionIsActive ? 'false' : 'true' }}"
                            @if(!$versionIsActive) hidden @endif
                        >
                            <div class="profile-version-pane-head">
                                <div>
                                    <h3>{{ $versionIsActive ? 'Current Uploaded Documents' : 'Saved Documents - Version ' . $versionNumber }}</h3>
                                    <p>{{ $versionIsActive ? 'Latest approved files attached to the Employee Health Profile.' : 'Files saved with this employee health form version.' }}</p>
                                </div>
                                <span class="profile-history-badge">{{ $versionIsActive ? 'Current' : 'Archived' }}</span>
                            </div>
                            @if($versionDocuments->isNotEmpty())
                                <div class="doc-grid">
                                    @foreach($versionDocuments as $document)
                                        <div class="doc-file">
                                            <h4>{{ $document['type'] }}</h4>
                                            @if(($document['uploaded'] ?? false) && filled($document['view_url']))
                                                <p>{{ $document['name'] }} - Uploaded {{ $document['uploaded_at'] }}</p>
                                                <div class="doc-actions"><a class="doc-link" href="{{ $document['view_url'] }}" target="_blank" rel="noopener"><x-outline-icon name="eye" />View</a></div>
                                            @else
                                                <p>Missing</p>
                                                <div class="doc-missing">No file uploaded for this document.</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="doc-missing">No employee documents are available.</div>
                            @endif
                        </section>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
</div>

@if($canRequestEmployeeHealthActions)
    <div class="employee-action-modal" id="newEmployeeHealthFormModal" aria-hidden="true">
        <div class="employee-action-card" role="dialog" aria-modal="true" aria-labelledby="newEmployeeHealthFormTitle">
            <div class="employee-action-head">
                <div class="employee-action-head-main">
                    <span class="employee-action-head-icon" aria-hidden="true"><x-outline-icon name="document-text" /></span>
                    <div>
                        <h3 id="newEmployeeHealthFormTitle">Request New Health Form</h3>
                        <p>Ask this {{ $employeeProfileTypeLabel }} to submit an updated Health Examination Record.</p>
                    </div>
                </div>
                <button type="button" class="employee-action-close" id="closeNewEmployeeHealthFormModal" aria-label="Close new health form modal">
                    <x-outline-icon name="x-mark" />
                </button>
            </div>
            <form method="POST" action="{{ route('admin.employee_health_profile.request_health_form', $employeeProfile->id) }}" class="employee-action-body">
                @csrf
                <div class="employee-action-field">
                    <label for="newEmployeeHealthFormCategory">Category / Purpose</label>
                    <div class="employee-action-select-wrap">
                        <select id="newEmployeeHealthFormCategory" name="category" class="employee-action-select-source" required>
                            <option value="">Select category</option>
                            @foreach(($employeeHealthFormCategories ?? collect()) as $category)
                                <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="employee-action-field">
                    <label for="newEmployeeHealthFormRemarks">Remarks</label>
                    <textarea id="newEmployeeHealthFormRemarks" name="remarks" placeholder="Optional note for why a new form is needed.">{{ old('remarks') }}</textarea>
                </div>
                <div class="employee-action-actions">
                    <button type="button" class="employee-action-cancel" id="cancelNewEmployeeHealthFormModal">Cancel</button>
                    <button type="submit" class="employee-action-submit">Send Request</button>
                </div>
            </form>
        </div>
    </div>

    <div class="employee-action-modal" id="employeeCorrectionModal" aria-hidden="true">
        <div class="employee-action-card" role="dialog" aria-modal="true" aria-labelledby="employeeCorrectionTitle">
            <div class="employee-action-head">
                <div class="employee-action-head-main">
                    <span class="employee-action-head-icon" aria-hidden="true"><x-outline-icon name="document-text" /></span>
                    <div>
                        <h3 id="employeeCorrectionTitle">Request File Correction</h3>
                        <p>Select the file or Health Form details that need to be updated.</p>
                    </div>
                </div>
                <button type="button" class="employee-action-close" id="closeEmployeeCorrectionModal" aria-label="Close file correction modal">
                    <x-outline-icon name="x-mark" />
                </button>
            </div>
            <form method="POST" action="{{ route('admin.employee_health_profile.request_resubmission', $employeeProfile->id) }}" class="employee-action-body" id="employeeCorrectionForm">
                @csrf
                <input type="hidden" name="pending_reason" id="employeeCorrectionReason" value="">
                <div class="employee-action-field">
                    <span class="employee-action-checks-title">Select requirement/s</span>
                    <div class="employee-action-checks">
                        @foreach([
                            'student_photo' => '2x2 Photo',
                            'health_declaration' => 'Health Declaration',
                            'medical_certificate' => 'Medical Certificate',
                            'chest_xray_result' => 'Chest X-ray Result',
                            'pwd_id_proof' => 'PWD ID Proof',
                        ] as $documentKey => $documentLabel)
                            <label class="employee-action-check">
                                <input type="checkbox" name="resubmission_required_documents[]" value="{{ $documentKey }}">
                                <span>{{ $documentLabel }}</span>
                            </label>
                        @endforeach
                        <label class="employee-action-check">
                            <input type="checkbox" name="needs_health_form_correction" value="1">
                            <span>Health Form Correction</span>
                        </label>
                    </div>
                </div>
                <div class="employee-action-field">
                    <label for="employeeCorrectionRemarks">Remarks</label>
                    <textarea id="employeeCorrectionRemarks" placeholder="Optional note for the requested correction."></textarea>
                </div>
                <div class="employee-action-actions">
                    <button type="button" class="employee-action-cancel" id="cancelEmployeeCorrectionModal">Cancel</button>
                    <button type="submit" class="employee-action-submit">Send Correction Request</button>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
    const employeeProfileActions = document.getElementById('employeeProfileActions');
    const employeeProfileActionsToggle = document.getElementById('employeeProfileActionsToggle');
    const employeeProfileActionsMenu = document.getElementById('employeeProfileActionsMenu');
    const newEmployeeHealthFormModal = document.getElementById('newEmployeeHealthFormModal');
    const employeeCorrectionModal = document.getElementById('employeeCorrectionModal');
    const employeeCorrectionForm = document.getElementById('employeeCorrectionForm');
    const employeeCorrectionReason = document.getElementById('employeeCorrectionReason');
    const employeeCorrectionRemarks = document.getElementById('employeeCorrectionRemarks');

    function setEmployeeProfileActionsMenu(isOpen) {
        if (!employeeProfileActions || !employeeProfileActionsToggle || !employeeProfileActionsMenu) return;

        employeeProfileActions.classList.toggle('is-open', isOpen);
        employeeProfileActionsToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        employeeProfileActionsMenu.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
    }

    function setEmployeeActionModal(modal, isOpen) {
        if (!modal) return;

        modal.classList.toggle('is-open', isOpen);
        modal.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
    }

    function setEmployeeActionSelectOpen(wrapper, isOpen) {
        if (!wrapper) return;

        wrapper.classList.toggle('is-open', isOpen);
        const trigger = wrapper.querySelector('.employee-action-select-trigger');
        if (trigger) trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    }

    function closeEmployeeActionSelects(exceptWrapper) {
        document.querySelectorAll('.employee-action-select-wrap.is-open').forEach(function (wrapper) {
            if (wrapper !== exceptWrapper) {
                setEmployeeActionSelectOpen(wrapper, false);
            }
        });
    }

    function initializeEmployeeActionSelect(select) {
        if (!select || select.dataset.customDropdownReady === 'true') return;

        const wrapper = select.closest('.employee-action-select-wrap');
        if (!wrapper) return;

        select.dataset.customDropdownReady = 'true';
        const trigger = document.createElement('button');
        const menu = document.createElement('div');
        const menuId = (select.id || 'employee-action-select') + '-custom-menu';

        trigger.type = 'button';
        trigger.className = 'employee-action-select-trigger';
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');
        trigger.setAttribute('aria-controls', menuId);

        menu.id = menuId;
        menu.className = 'employee-action-select-menu';
        menu.setAttribute('role', 'listbox');

        function syncEmployeeActionSelect() {
            const selectedOption = select.options[select.selectedIndex] || select.options[0];
            trigger.textContent = selectedOption?.textContent?.trim() || 'Select an option';
            menu.querySelectorAll('.employee-action-select-option').forEach(function (optionButton) {
                const isSelected = optionButton.dataset.value === select.value;
                optionButton.classList.toggle('is-selected', isSelected);
                optionButton.setAttribute('aria-selected', isSelected ? 'true' : 'false');
            });
        }

        Array.from(select.options).forEach(function (option) {
            const optionButton = document.createElement('button');
            const optionLabel = document.createElement('span');

            optionButton.type = 'button';
            optionButton.className = 'employee-action-select-option';
            optionButton.dataset.value = option.value;
            optionButton.setAttribute('role', 'option');
            optionLabel.textContent = option.textContent.trim();
            optionButton.appendChild(optionLabel);
            optionButton.addEventListener('click', function (event) {
                event.stopPropagation();
                select.value = option.value;
                select.dispatchEvent(new Event('change', { bubbles: true }));
                syncEmployeeActionSelect();
                setEmployeeActionSelectOpen(wrapper, false);
                trigger.focus();
            });
            menu.appendChild(optionButton);
        });

        trigger.addEventListener('click', function (event) {
            event.stopPropagation();
            const willOpen = !wrapper.classList.contains('is-open');
            closeEmployeeActionSelects(wrapper);
            setEmployeeActionSelectOpen(wrapper, willOpen);
        });
        trigger.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowDown' || event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                closeEmployeeActionSelects(wrapper);
                setEmployeeActionSelectOpen(wrapper, true);
                menu.querySelector('.employee-action-select-option.is-selected, .employee-action-select-option')?.focus();
            }
        });
        select.addEventListener('change', syncEmployeeActionSelect);
        select.addEventListener('invalid', function () {
            trigger.focus();
        });

        wrapper.appendChild(trigger);
        wrapper.appendChild(menu);
        syncEmployeeActionSelect();
    }

    document.querySelectorAll('.employee-action-select-source').forEach(initializeEmployeeActionSelect);

    document.addEventListener('click', function (event) {
        if (!event.target.closest('.employee-action-select-wrap')) {
            closeEmployeeActionSelects();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeEmployeeActionSelects();
        }
    });

    function syncEmployeeCorrectionReason() {
        if (!employeeCorrectionReason) return;

        const selectedDocuments = Array.from(document.querySelectorAll('#employeeCorrectionForm input[name="resubmission_required_documents[]"]:checked'))
            .map(function (input) { return input.nextElementSibling?.textContent.trim() || ''; })
            .filter(Boolean);
        const healthFormCorrection = document.querySelector('#employeeCorrectionForm input[name="needs_health_form_correction"]:checked');
        const remarks = employeeCorrectionRemarks?.value.trim() || '';
        const parts = selectedDocuments.length > 0 ? ['Files: ' + selectedDocuments.join(', ')] : [];

        if (healthFormCorrection) {
            parts.push('Health Form Correction');
        }
        if (remarks !== '') {
            parts.push(remarks);
        }

        employeeCorrectionReason.value = parts.join('. ') || 'Health Form Correction';
    }

    employeeProfileActionsToggle?.addEventListener('click', function (event) {
        event.stopPropagation();
        setEmployeeProfileActionsMenu(!employeeProfileActions?.classList.contains('is-open'));
    });

    document.getElementById('openNewEmployeeHealthFormModal')?.addEventListener('click', function () {
        setEmployeeProfileActionsMenu(false);
        setEmployeeActionModal(newEmployeeHealthFormModal, true);
    });

    document.getElementById('openEmployeeCorrectionModal')?.addEventListener('click', function () {
        setEmployeeProfileActionsMenu(false);
        setEmployeeActionModal(employeeCorrectionModal, true);
    });

    document.getElementById('closeNewEmployeeHealthFormModal')?.addEventListener('click', function () {
        setEmployeeActionModal(newEmployeeHealthFormModal, false);
    });

    document.getElementById('cancelNewEmployeeHealthFormModal')?.addEventListener('click', function () {
        setEmployeeActionModal(newEmployeeHealthFormModal, false);
    });

    document.getElementById('closeEmployeeCorrectionModal')?.addEventListener('click', function () {
        setEmployeeActionModal(employeeCorrectionModal, false);
    });

    document.getElementById('cancelEmployeeCorrectionModal')?.addEventListener('click', function () {
        setEmployeeActionModal(employeeCorrectionModal, false);
    });

    employeeCorrectionModal?.querySelectorAll('input[type="checkbox"], textarea').forEach(function (input) {
        input.addEventListener('input', syncEmployeeCorrectionReason);
        input.addEventListener('change', syncEmployeeCorrectionReason);
    });

    employeeCorrectionForm?.addEventListener('submit', function () {
        syncEmployeeCorrectionReason();
    });

    document.addEventListener('click', function (event) {
        if (employeeProfileActions?.classList.contains('is-open') && !employeeProfileActions.contains(event.target)) {
            setEmployeeProfileActionsMenu(false);
        }
        if (newEmployeeHealthFormModal && event.target === newEmployeeHealthFormModal) {
            setEmployeeActionModal(newEmployeeHealthFormModal, false);
        }
        if (employeeCorrectionModal && event.target === employeeCorrectionModal) {
            setEmployeeActionModal(employeeCorrectionModal, false);
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;

        if (employeeProfileActions?.classList.contains('is-open')) {
            setEmployeeProfileActionsMenu(false);
            employeeProfileActionsToggle?.focus();
        }
        if (newEmployeeHealthFormModal?.classList.contains('is-open')) {
            setEmployeeActionModal(newEmployeeHealthFormModal, false);
        }
        if (employeeCorrectionModal?.classList.contains('is-open')) {
            setEmployeeActionModal(employeeCorrectionModal, false);
        }
    });

    document.querySelectorAll('.health-profile-wrap [data-profile-tab-target]').forEach(function (tab) {
        tab.addEventListener('click', function () {
            const targetId = tab.dataset.profileTabTarget;
            const root = tab.closest('.health-profile-wrap');
            if (!root || !targetId) return;

            root.querySelectorAll('[data-profile-tab-target]').forEach(function (item) {
                const isActive = item === tab;
                item.classList.toggle('is-active', isActive);
                item.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            root.querySelectorAll('.profile-panel').forEach(function (panel) {
                const isActive = panel.id === targetId;
                panel.classList.toggle('is-active', isActive);
                panel.hidden = !isActive;
            });
        });
    });

    document.querySelectorAll('.health-profile-wrap [data-profile-version-target]').forEach(function (choice) {
        choice.addEventListener('click', function () {
            const targetId = choice.dataset.profileVersionTarget;
            const root = choice.closest('.profile-documents-version-shell');
            if (!root || !targetId) return;

            root.querySelectorAll('[data-profile-version-target]').forEach(function (item) {
                const isActive = item === choice;
                item.classList.toggle('is-active', isActive);
                item.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            root.querySelectorAll('.profile-version-pane').forEach(function (pane) {
                const isActive = pane.id === targetId;
                pane.classList.toggle('is-active', isActive);
                pane.setAttribute('aria-hidden', isActive ? 'false' : 'true');
                pane.hidden = !isActive;
            });
        });
    });
</script>
@endpush
