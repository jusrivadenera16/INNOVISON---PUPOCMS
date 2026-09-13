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
    $statusClass = in_array(strtolower($status), ['issued', 'fully cleared', 'approved'], true)
        ? 'profile-status-issued'
        : (in_array(strtolower($status), ['pending', 'for verification', 'pending resubmission'], true)
            ? 'profile-status-pending'
            : 'profile-status-default');
    $employeePhotoUrl = filled($employeeProfile?->student_photo)
        ? route('walkin.employeeDocument', ['employeeProfile' => $employeeProfile->id, 'document' => 'student_photo'])
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
            <div class="profile-version-shell profile-documents-version-shell">
                <aside class="profile-version-sidebar" aria-label="Uploaded Documents versions">
                    <div class="profile-version-sidebar-head">
                        <div>
                            <h3>Document Versions</h3>
                            <p>Select a version to review its saved files.</p>
                        </div>
                        <span class="profile-history-count">1</span>
                    </div>
                    <div class="profile-version-nav" role="tablist" aria-label="Uploaded Documents versions">
                        <button type="button" class="profile-version-choice is-active" role="tab" aria-selected="true">
                            <span class="profile-version-choice-number">V1</span>
                            <span class="profile-version-choice-copy">
                                <strong>Current Documents</strong>
                                <small>{{ $formatDate($employeeProfile?->form_date) }}</small>
                            </span>
                        </button>
                    </div>
                </aside>

                <div class="profile-version-content">
                    <section class="profile-version-pane is-active">
                        <div class="profile-version-pane-head">
                            <div><h3>Current Uploaded Documents</h3><p>Latest files attached to the active Employee Health Profile.</p></div>
                            <span class="profile-history-badge">Current</span>
                        </div>
                        @if($employeeDocuments->isNotEmpty())
                            <div class="doc-grid">
                                @foreach($employeeDocuments as $document)
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
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
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
</script>
@endpush
