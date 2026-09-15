@extends('layouts.admin')

@section('title', 'My Health Profile')

@php
    $admin = $admin ?? auth()->user();
    $employeeProfile = $employeeProfile ?? null;
    $healthFormVersions = collect($healthFormVersions ?? [])->values();
    $versionCount = $healthFormVersions->count();
    $firstHealthFormVersion = $healthFormVersions->first();
    $selectedVersion = is_array($firstHealthFormVersion) ? ($firstHealthFormVersion['version'] ?? null) : null;
    $displayName = trim((string) ($admin?->name ?? 'Clinic User'));
    $avatarInitials = collect(preg_split('/\s+/', $displayName, -1, PREG_SPLIT_NO_EMPTY) ?: [])
        ->take(2)
        ->map(fn (string $part): string => strtoupper(substr($part, 0, 1)))
        ->implode('');
    $employeeNumber = trim((string) ($employeeProfile?->employee_number ?: $admin?->employee_number ?: ''));
    $office = trim((string) ($employeeProfile?->office ?: $admin?->adminProfile?->office ?: ''));
    $contactNumber = trim((string) ($employeeProfile?->contact_no ?: $admin?->contact_no ?: ''));
@endphp

@push('styles')
<style>
    body:has(.my-health-profile-page) .main {
        padding: 10px;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, .86), rgba(255, 255, 255, .86)),
            url('{{ asset('images/admin-bg-light.png') }}?v={{ is_file(public_path('images/admin-bg-light.png')) ? md5_file(public_path('images/admin-bg-light.png')) : 'missing' }}') center / cover fixed !important;
    }

    html[data-theme="dark"] body:has(.my-health-profile-page) .main {
        background:
            linear-gradient(180deg, rgba(20, 7, 14, .84), rgba(20, 7, 14, .84)),
            url('{{ asset('images/admin-bg-dark.png') }}?v={{ is_file(public_path('images/admin-bg-dark.png')) ? md5_file(public_path('images/admin-bg-dark.png')) : 'missing' }}') center / cover fixed !important;
    }

    .my-health-profile-page {
        --mhp-maroon: #7f0010;
        --mhp-maroon-dark: #59000b;
        --mhp-yellow: #facc15;
        --mhp-text: #172033;
        --mhp-muted: #64748b;
        --mhp-line: rgba(100, 116, 139, .2);
        --mhp-surface: rgba(255, 255, 255, .96);
        --mhp-surface-soft: #f8fafc;
        --mhp-surface-tint: #fff7f8;
        --mhp-shadow: 0 12px 28px rgba(15, 23, 42, .08);
        max-width: 1440px;
        margin: 0 auto;
        padding: 16px;
        color: var(--mhp-text);
    }

    html[data-theme="dark"] .my-health-profile-page {
        --mhp-text: #ffffff;
        --mhp-muted: #ffffff;
        --mhp-line: rgba(255, 255, 255, .14);
        --mhp-surface: rgba(15, 23, 42, .94);
        --mhp-surface-soft: rgba(30, 41, 59, .82);
        --mhp-surface-tint: rgba(127, 0, 16, .24);
        --mhp-shadow: 0 14px 32px rgba(0, 0, 0, .22);
    }

    .mhp-current-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
        white-space: nowrap;
    }

    .mhp-identity-card,
    .mhp-panel {
        border: 1px solid var(--mhp-line);
        border-radius: 12px;
        background: var(--mhp-surface);
        box-shadow: var(--mhp-shadow);
    }

    .mhp-identity-card {
        display: grid;
        grid-template-columns: minmax(240px, 1.15fr) minmax(0, 2fr) minmax(190px, .8fr);
        align-items: center;
        gap: 18px;
        min-height: 112px;
        padding: 16px 18px;
        margin-bottom: 14px;
        background:
            linear-gradient(90deg, rgba(255, 255, 255, .96), rgba(255, 255, 255, .82)),
            url('{{ asset('images/hif_bg.png') }}') right center / cover no-repeat;
    }

    html[data-theme="dark"] .mhp-identity-card {
        background:
            linear-gradient(90deg, rgba(15, 23, 42, .97), rgba(15, 23, 42, .84)),
            url('{{ asset('images/hif_bg.png') }}') right center / cover no-repeat;
    }

    .mhp-identity-main {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .mhp-avatar {
        display: grid;
        place-items: center;
        width: 64px;
        height: 64px;
        flex: 0 0 auto;
        border-radius: 50%;
        border: 2px solid rgba(127, 0, 16, .18);
        background: #fff1f2;
        color: var(--mhp-maroon);
        font-size: 19px;
        font-weight: 900;
        box-shadow: 0 6px 14px rgba(127, 0, 16, .1);
    }

    html[data-theme="dark"] .mhp-avatar {
        border-color: rgba(250, 204, 21, .3);
        background: rgba(127, 0, 16, .34);
        color: #fff7ed;
    }

    .mhp-identity-copy {
        min-width: 0;
    }

    .mhp-identity-copy h2 {
        overflow: hidden;
        margin: 0;
        color: var(--mhp-maroon);
        font-size: 17px;
        line-height: 1.25;
        font-weight: 900;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    html[data-theme="dark"] .mhp-identity-copy h2 {
        color: #ffffff;
    }

    .mhp-identity-copy p {
        margin: 4px 0 0;
        color: var(--mhp-muted);
        font-size: 13px;
        font-weight: 700;
    }

    .mhp-identity-fields {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        border-left: 1px solid var(--mhp-line);
    }

    .mhp-identity-field {
        min-width: 0;
        padding: 0 14px;
    }

    .mhp-identity-field + .mhp-identity-field {
        border-left: 1px solid var(--mhp-line);
    }

    .mhp-identity-field span {
        display: block;
        margin-bottom: 4px;
        color: var(--mhp-muted);
        font-size: 11px;
        font-weight: 800;
    }

    .mhp-identity-field strong {
        display: block;
        overflow: hidden;
        color: var(--mhp-text);
        font-size: 13px;
        font-weight: 900;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .mhp-identity-note {
        align-self: stretch;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding-left: 16px;
        border-left: 1px solid var(--mhp-line);
        color: var(--mhp-muted);
        font-size: 12px;
        font-style: italic;
        line-height: 1.45;
        text-align: right;
    }

    .mhp-identity-note strong {
        display: block;
        margin-top: 3px;
        color: var(--mhp-maroon);
        font-size: 12px;
        font-weight: 800;
    }

    html[data-theme="dark"] .mhp-identity-note strong {
        color: #facc15;
    }

    .mhp-content-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.5fr) minmax(300px, .85fr);
        gap: 14px;
        align-items: stretch;
    }

    .mhp-content-grid.is-first-form {
        grid-template-columns: minmax(280px, 600px);
        min-height: min(540px, calc(100vh - 150px));
        align-items: center;
        justify-content: center;
    }

    .mhp-content-grid.is-first-form .mhp-update-card {
        min-height: 360px;
        align-self: center;
        transform: translateY(-40px);
    }

    .mhp-panel {
        min-width: 0;
        overflow: hidden;
    }

    .mhp-panel-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 16px 11px;
        border-bottom: 1px solid var(--mhp-line);
    }

    .mhp-panel-heading-copy {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        min-width: 0;
    }

    .mhp-panel-heading-icon {
        display: grid;
        place-items: center;
        width: 30px;
        height: 30px;
        flex: 0 0 auto;
        border-radius: 8px;
        color: var(--mhp-maroon);
        background: #fff1f2;
    }

    html[data-theme="dark"] .mhp-panel-heading-icon {
        color: #facc15;
        background: rgba(127, 0, 16, .3);
    }

    .mhp-panel-heading-icon svg {
        width: 18px;
        height: 18px;
    }

    .mhp-panel-heading h2 {
        margin: 0;
        color: var(--mhp-maroon);
        font-size: 17px;
        line-height: 1.25;
        font-weight: 900;
    }

    html[data-theme="dark"] .mhp-panel-heading h2 {
        color: #ffffff;
    }

    .mhp-panel-heading p {
        margin: 2px 0 0;
        color: var(--mhp-muted);
        font-size: 12px;
        line-height: 1.35;
    }

    .mhp-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 32px;
        padding: 0 11px;
        border: 1px solid var(--mhp-maroon);
        border-radius: 7px;
        background: var(--mhp-maroon);
        color: #ffffff;
        font-family: inherit;
        font-size: 11px;
        font-weight: 900;
        line-height: 1;
        white-space: nowrap;
        text-decoration: none;
        transition: background-color .2s ease, border-color .2s ease, box-shadow .2s ease, color .2s ease, transform .2s ease;
    }

    .mhp-button:hover,
    .mhp-button:focus-visible {
        border-color: var(--mhp-yellow);
        background: var(--mhp-yellow);
        color: #7f0010 !important;
        box-shadow: 0 10px 22px rgba(250, 204, 21, .28);
        transform: translateY(-2px);
    }

    .mhp-button:hover svg,
    .mhp-button:focus-visible svg {
        color: #7f0010 !important;
    }

    .mhp-button:focus-visible {
        outline: 2px solid var(--mhp-yellow);
        outline-offset: 3px;
    }

    .mhp-button svg {
        width: 15px;
        height: 15px;
    }

    .mhp-button.is-disabled {
        cursor: not-allowed;
        opacity: .58;
    }

    .mhp-version-list {
        display: grid;
        gap: 7px;
        padding: 11px 14px 8px;
    }

    .mhp-version-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
        gap: 9px;
        min-height: 58px;
        padding: 8px 10px;
        border: 1px solid var(--mhp-line);
        border-radius: 8px;
        background: var(--mhp-surface-soft);
        transition: border-color .2s ease, background .2s ease, box-shadow .2s ease;
    }

    .mhp-version-row.is-selected {
        border-color: rgba(127, 0, 16, .42);
        background: var(--mhp-surface-tint);
        box-shadow: inset 0 0 0 1px rgba(127, 0, 16, .08);
    }

    html[data-theme="dark"] .mhp-version-row.is-selected {
        border-color: rgba(250, 204, 21, .46);
        box-shadow: inset 0 0 0 1px rgba(250, 204, 21, .08);
    }

    .mhp-version-content {
        display: flex;
        align-items: center;
        gap: 9px;
        min-width: 0;
        width: 100%;
    }

    .mhp-version-select {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 88px;
        min-height: 32px;
        padding: 0 10px;
        border: 1px solid var(--mhp-line);
        border-radius: 6px;
        background: var(--mhp-surface);
        color: var(--mhp-text);
        font-family: inherit;
        font-size: 11px;
        font-weight: 900;
        line-height: 1;
        white-space: nowrap;
        cursor: pointer;
        transition: background-color .2s ease, border-color .2s ease, box-shadow .2s ease, color .2s ease;
    }

    .mhp-version-select:hover,
    .mhp-version-select:focus-visible,
    .mhp-version-select.is-selected {
        border-color: var(--mhp-yellow);
        background: var(--mhp-yellow);
        color: #7f0010;
        box-shadow: 0 6px 12px rgba(250, 204, 21, .18);
    }

    .mhp-version-select:focus-visible {
        outline: 2px solid var(--mhp-yellow);
        outline-offset: 3px;
    }

    .mhp-version-select.is-selected {
        cursor: default;
    }

    .mhp-version-icon {
        display: grid;
        place-items: center;
        width: 30px;
        height: 34px;
        flex: 0 0 auto;
        color: var(--mhp-maroon);
    }

    html[data-theme="dark"] .mhp-version-icon {
        color: #facc15;
    }

    .mhp-version-icon svg {
        width: 23px;
        height: 23px;
    }

    .mhp-version-copy {
        min-width: 0;
    }

    .mhp-version-title {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        color: var(--mhp-text);
        font-size: 13px;
        font-weight: 900;
    }

    .mhp-current-badge {
        padding: 3px 6px;
        color: #166534;
        background: #dcfce7;
    }

    .mhp-version-meta {
        margin-top: 3px;
        color: var(--mhp-muted);
        font-size: 11px;
        line-height: 1.35;
    }

    .mhp-version-meta strong {
        color: var(--mhp-maroon);
        font-weight: 800;
    }

    html[data-theme="dark"] .mhp-version-meta strong {
        color: #facc15;
    }

    .mhp-document-actions {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .mhp-ghost-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        min-height: 28px;
        padding: 0 8px;
        border: 1px solid var(--mhp-line);
        border-radius: 6px;
        background: var(--mhp-surface);
        color: var(--mhp-text);
        font-family: inherit;
        font-size: 11px;
        font-weight: 900;
        text-decoration: none;
        white-space: nowrap;
        cursor: pointer;
        transition: border-color .18s ease, background-color .18s ease, color .18s ease, transform .18s ease;
    }

    .mhp-ghost-button:hover,
    .mhp-ghost-button:focus-visible {
        border-color: var(--mhp-maroon);
        background: var(--mhp-surface-tint);
        color: var(--mhp-maroon);
        outline: none;
    }

    html[data-theme="dark"] .mhp-ghost-button:hover,
    html[data-theme="dark"] .mhp-ghost-button:focus-visible {
        border-color: #facc15;
        background: rgba(250, 204, 21, .12);
        color: #ffffff;
    }

    .mhp-icon-button {
        width: 30px;
        min-width: 30px;
        height: 30px;
        padding: 0;
    }

    .mhp-document-upload-form {
        display: inline-flex;
        margin: 0;
    }

    .mhp-visually-hidden {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }

    .mhp-ghost-button svg {
        width: 13px;
        height: 13px;
        color: var(--mhp-maroon);
    }

    html[data-theme="dark"] .mhp-ghost-button svg {
        color: #facc15;
    }

    .mhp-version-footer {
        padding: 0 16px 12px;
        color: var(--mhp-muted);
        font-size: 11px;
        text-align: center;
    }

    .mhp-empty-state {
        display: grid;
        place-items: center;
        min-height: 156px;
        padding: 24px;
        color: var(--mhp-muted);
        text-align: center;
    }

    .mhp-empty-state svg {
        width: 30px;
        height: 30px;
        margin-bottom: 7px;
        color: var(--mhp-maroon);
    }

    html[data-theme="dark"] .mhp-empty-state svg {
        color: #facc15;
    }

    .mhp-empty-state strong {
        display: block;
        color: var(--mhp-text);
        font-size: 13px;
        font-weight: 900;
    }

    .mhp-empty-state span {
        display: block;
        margin-top: 3px;
        font-size: 12px;
    }

    .mhp-update-card {
        display: flex;
        flex-direction: column;
        min-height: 100%;
        padding: 26px 24px;
        background: #fff7f8;
    }

    html[data-theme="dark"] .mhp-update-card {
        background: #3f0a1a;
        color: #ffffff;
    }

    .mhp-update-card-icon {
        display: grid;
        place-items: center;
        width: 60px;
        height: 60px;
        margin-bottom: 16px;
        border-radius: 50%;
        background: #fff1f2;
        color: var(--mhp-maroon);
    }

    html[data-theme="dark"] .mhp-update-card-icon {
        background: rgba(127, 0, 16, .35);
        color: #facc15;
    }

    .mhp-update-card-icon svg {
        width: 28px;
        height: 28px;
    }

    .mhp-update-card h2 {
        margin: 0;
        color: var(--mhp-maroon);
        font-size: 20px;
        line-height: 1.25;
        font-weight: 900;
    }

    html[data-theme="dark"] .mhp-update-card h2 {
        color: #ffffff;
    }

    .mhp-update-card p {
        margin: 8px 0 18px;
        color: var(--mhp-muted);
        font-size: 13px;
        line-height: 1.5;
    }

    .mhp-check-list {
        display: grid;
        gap: 10px;
        padding: 0;
        margin: 0 0 22px;
        list-style: none;
    }

    .mhp-check-list li {
        display: flex;
        align-items: flex-start;
        gap: 7px;
        color: var(--mhp-muted);
        font-size: 12px;
        line-height: 1.4;
    }

    .mhp-check-list svg {
        width: 14px;
        height: 14px;
        flex: 0 0 auto;
        color: #15803d;
    }

    .mhp-update-card .mhp-button {
        width: 100%;
        min-height: 46px;
        margin-top: auto;
        font-size: 13px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, .28);
    }

    .mhp-documents-panel {
        margin-top: 14px;
    }

    .mhp-document-version-view[hidden] {
        display: none;
    }

    .mhp-document-table-wrap {
        padding: 0 14px 12px;
    }

    .mhp-document-table {
        width: 100%;
        min-width: 640px;
        border-collapse: separate;
        border-spacing: 0;
        color: var(--mhp-text);
    }

    .mhp-document-table th {
        padding: 9px 8px;
        background: var(--mhp-surface-soft);
        color: var(--mhp-muted);
        font-size: 10px;
        font-weight: 900;
        text-align: left;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .mhp-document-table th:first-child {
        border-top-left-radius: 6px;
    }

    .mhp-document-table th:last-child {
        border-top-right-radius: 6px;
    }

    .mhp-document-table tbody tr {
        background: var(--mhp-surface);
    }

    .mhp-document-table td {
        padding: 9px 8px;
        border-bottom: 1px solid var(--mhp-line);
        font-size: 12px;
        vertical-align: middle;
    }

    .mhp-document-name {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
        font-weight: 800;
    }

    .mhp-document-name svg {
        width: 17px;
        height: 17px;
        flex: 0 0 auto;
        color: var(--mhp-maroon);
    }

    html[data-theme="dark"] .mhp-document-name svg {
        color: #facc15;
    }

    .mhp-document-name span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .mhp-document-muted {
        color: var(--mhp-muted);
    }

    .mhp-document-missing {
        color: var(--mhp-muted);
        font-weight: 800;
    }

    @media (max-width: 1040px) {
        .mhp-identity-card {
            grid-template-columns: minmax(220px, 1fr) minmax(0, 1.7fr);
        }

        .mhp-identity-note {
            display: none;
        }
    }

    @media (max-width: 820px) {
        .mhp-content-grid.is-first-form {
            grid-template-columns: minmax(0, 1fr);
            min-height: 0;
        }

        .mhp-content-grid.is-first-form .mhp-update-card {
            transform: none;
        }

        .mhp-content-grid {
            grid-template-columns: 1fr;
        }

        .mhp-update-card {
            min-height: 360px;
        }

        .mhp-update-card .mhp-button {
            width: auto;
            align-self: flex-start;
        }
    }

    @media (max-width: 660px) {
        .my-health-profile-page {
            padding: 10px;
        }

        .mhp-identity-card {
            grid-template-columns: 1fr;
            gap: 12px;
            padding: 14px;
        }

        .mhp-identity-fields {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            border-top: 1px solid var(--mhp-line);
            border-left: 0;
            padding-top: 11px;
        }

        .mhp-identity-field {
            padding: 0 10px 8px 0;
        }

        .mhp-identity-field:nth-child(3) {
            grid-column: 1 / -1;
            padding-bottom: 0;
        }

        .mhp-identity-field + .mhp-identity-field {
            border-left: 0;
        }

        .mhp-panel-heading {
            flex-direction: column;
        }

        .mhp-panel-heading .mhp-button {
            width: 100%;
        }

        .mhp-version-row {
            grid-template-columns: 1fr;
            align-items: stretch;
        }

        .mhp-version-select {
            width: 100%;
        }

        .mhp-update-card .mhp-button {
            width: 100%;
        }

        .mhp-document-table-wrap {
            padding: 0 12px 12px;
            overflow: visible;
        }

        .mhp-document-table {
            display: block;
            min-width: 0;
        }

        .mhp-document-table thead {
            display: none;
        }

        .mhp-document-table tbody {
            display: block;
        }

        .mhp-document-table tbody tr {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 4px;
            padding: 10px 0;
            border-bottom: 1px solid var(--mhp-line);
        }

        .mhp-document-table td {
            display: grid;
            grid-template-columns: 92px minmax(0, 1fr);
            gap: 9px;
            min-width: 0;
            padding: 4px 2px;
            border-bottom: 0;
            font-size: 12px;
        }

        .mhp-document-table td::before {
            content: attr(data-label);
            color: var(--mhp-muted);
            font-size: 9px;
            font-weight: 900;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .mhp-document-name {
            min-width: 0;
        }

        .mhp-document-name span {
            overflow: visible;
            text-overflow: clip;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .mhp-document-actions {
            justify-content: flex-start;
        }

    }
</style>
@endpush

@section('content')
<div class="my-health-profile-page">
    @include('admin.partials.report-breadcrumb', ['items' => [
        ['label' => 'Settings', 'url' => route('admin.settings')],
        ['label' => 'My Health Profile'],
    ]])
    @if($healthFormVersions->isNotEmpty())
    <section class="mhp-identity-card" aria-label="Employee profile summary">
        <div class="mhp-identity-main">
            <div class="mhp-avatar" aria-hidden="true">{{ $avatarInitials ?: 'CU' }}</div>
            <div class="mhp-identity-copy">
                <h2 title="{{ $displayName }}">{{ $displayName }}</h2>
                <p>{{ $office !== '' ? $office : 'Employee profile' }}</p>
            </div>
        </div>

        <div class="mhp-identity-fields">
            <div class="mhp-identity-field">
                <span>Employee No.</span>
                <strong>{{ $employeeNumber !== '' ? $employeeNumber : 'Not assigned' }}</strong>
            </div>
            <div class="mhp-identity-field">
                <span>Email Address</span>
                <strong title="{{ $admin?->email }}">{{ $admin?->email ?: 'No email assigned' }}</strong>
            </div>
            <div class="mhp-identity-field">
                <span>Contact Number</span>
                <strong>{{ $contactNumber !== '' ? $contactNumber : 'Not provided' }}</strong>
            </div>
        </div>

        <div class="mhp-identity-note">
            <span>“Taking care of yourself</span>
            <strong>helps you take care of others.”</strong>
        </div>
    </section>
    @endif

    <div class="mhp-content-grid {{ $healthFormVersions->isEmpty() ? 'is-first-form' : '' }}">
        @if($healthFormVersions->isNotEmpty())
        <section class="mhp-panel" aria-labelledby="mhpVersionsHeading">
            <div class="mhp-panel-heading">
                <div class="mhp-panel-heading-copy">
                    <span class="mhp-panel-heading-icon"><x-outline-icon name="document-text" /></span>
                    <div>
                        <h2 id="mhpVersionsHeading">Health Form Versions</h2>
                        <p>Your submitted employee health forms.</p>
                    </div>
                </div>
            </div>

            @if($healthFormVersions->isNotEmpty())
                <div class="mhp-version-list">
                    @foreach($healthFormVersions as $version)
                        <article class="mhp-version-row {{ $version['version'] === $selectedVersion ? 'is-selected' : '' }}" data-version-row="{{ $version['version'] }}">
                            <div class="mhp-version-content">
                                <span class="mhp-version-icon"><x-outline-icon name="document-text" /></span>
                                <span class="mhp-version-copy">
                                    <span class="mhp-version-title">
                                        {{ $version['label'] }}
                                        @if($versionCount === 1 && ($version['is_current'] ?? false))
                                            <span class="mhp-current-badge">Active</span>
                                        @endif
                                    </span>
                                    <span class="mhp-version-meta">
                                        Employee Health Form
                                        <br>
                                        Submitted on <strong>{{ $version['submitted_at'] }}</strong>
                                    </span>
                                </span>
                            </div>
                            @if($versionCount > 1)
                                <button
                                    type="button"
                                    class="mhp-version-select {{ $version['version'] === $selectedVersion ? 'is-selected' : '' }}"
                                    data-version-select="{{ $version['version'] }}"
                                    aria-pressed="{{ $version['version'] === $selectedVersion ? 'true' : 'false' }}"
                                >
                                    <span data-version-state="{{ $version['version'] }}">{{ $version['version'] === $selectedVersion ? 'Selected' : 'Select' }}</span>
                                </button>
                            @endif
                        </article>
                    @endforeach
                </div>
                <div class="mhp-version-footer">End of list</div>
            @else
                <div class="mhp-empty-state">
                    <div>
                        <x-outline-icon name="document-clock" />
                        <strong>No health form versions yet.</strong>
                        <span>Your submitted employee health forms will appear here.</span>
                    </div>
                </div>
            @endif
        </section>
        @endif

        <aside class="mhp-panel mhp-update-card" aria-labelledby="mhpUpdateHeading">
            <span class="mhp-update-card-icon"><x-outline-icon name="document-check" /></span>
            <h2 id="mhpUpdateHeading">Update Your Health Information</h2>
            <p>Submit a new version of your employee health form to keep your records up to date.</p>
            <ul class="mhp-check-list">
                <li><x-outline-icon name="check" /><strong>Your latest form will become the current version.</strong></li>
                <li><x-outline-icon name="check" /><strong>Previous versions will remain accessible.</strong></li>
                <li><x-outline-icon name="check" /><strong>Make sure all information is accurate.</strong></li>
            </ul>
            <a href="{{ route('admin.settings.health-profile.form') }}" class="mhp-button">
                <x-outline-icon name="document-text" />
                <span>Fill Out New Form</span>
                <x-outline-icon name="arrow-long-right" />
            </a>
        </aside>
    </div>

    @if($healthFormVersions->isNotEmpty())
    <section class="mhp-panel mhp-documents-panel" aria-labelledby="mhpDocumentsHeading">
        <div class="mhp-panel-heading">
            <div class="mhp-panel-heading-copy">
                <span class="mhp-panel-heading-icon"><x-outline-icon name="document-download" /></span>
                <div>
                    <h2 id="mhpDocumentsHeading">Uploaded Documents</h2>
                    <p>Documents attached to the selected health form version.</p>
                </div>
            </div>
        </div>

        <div class="mhp-document-table-wrap">
            <table class="mhp-document-table">
                <thead>
                    <tr>
                        <th>Document Type</th>
                        <th>Document Name</th>
                        <th>Date Uploaded</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                @if($healthFormVersions->isNotEmpty())
                    @foreach($healthFormVersions as $version)
                        <tbody class="mhp-document-version-view" data-document-version="{{ $version['version'] }}" @if($version['version'] !== $selectedVersion) hidden @endif>
                            @foreach($version['documents'] as $document)
                                <tr>
                                    <td class="mhp-document-muted" data-label="Document Type">{{ $document['type'] }}</td>
                                    <td data-label="Document Name">
                                        <span class="mhp-document-name {{ ($document['uploaded'] ?? false) ? '' : 'mhp-document-missing' }}" title="{{ $document['name'] }}">
                                            <x-outline-icon name="document-text" />
                                            <span>{{ $document['name'] }}</span>
                                        </span>
                                    </td>
                                    <td class="mhp-document-muted" data-label="Date Uploaded">{{ $document['uploaded_at'] }}</td>
                                    <td data-label="Actions">
                                        @if($document['uploaded'] ?? false)
                                            <div class="mhp-document-actions">
                                                <a
                                                    href="{{ $document['view_url'] ?? '#' }}"
                                                    class="mhp-ghost-button mhp-icon-button"
                                                    title="View {{ $document['type'] }}"
                                                    aria-label="View {{ $document['type'] }}"
                                                    target="_blank"
                                                    rel="noopener"
                                                >
                                                    <x-outline-icon name="eye" />
                                                </a>
                                                <a
                                                    href="{{ $document['download_url'] ?? '#' }}"
                                                    class="mhp-ghost-button mhp-icon-button"
                                                    title="Download {{ $document['type'] }}"
                                                    aria-label="Download {{ $document['type'] }}"
                                                >
                                                    <x-outline-icon name="document-download" />
                                                </a>
                                            </div>
                                        @else
                                            <form
                                                action="{{ route('admin.settings.health-profile.document.upload', ['document' => $document['key']]) }}"
                                                method="POST"
                                                enctype="multipart/form-data"
                                                class="mhp-document-upload-form"
                                                data-document-upload-form
                                            >
                                                @csrf
                                                <input
                                                    type="file"
                                                    name="document"
                                                    accept=".pdf,.jpg,.jpeg,.png"
                                                    data-document-upload-input
                                                    hidden
                                                >
                                                <button
                                                    type="button"
                                                    class="mhp-ghost-button"
                                                    title="Upload {{ $document['type'] }} (maximum 1 MB)"
                                                    aria-label="Upload {{ $document['type'] }} (maximum 1 MB)"
                                                    data-document-upload-trigger
                                                >
                                                    <x-outline-icon name="plus" />
                                                    <span>Upload</span>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    @endforeach
                @else
                    <tbody>
                        @foreach($documentRows as $document)
                            <tr>
                                <td class="mhp-document-muted" data-label="Document Type">{{ $document['type'] }}</td>
                                <td data-label="Document Name">
                                    <span class="mhp-document-name mhp-document-missing" title="Missing">
                                        <x-outline-icon name="document-text" />
                                        <span>Missing</span>
                                    </span>
                                </td>
                                <td class="mhp-document-muted" data-label="Date Uploaded">-</td>
                                <td data-label="Actions">
                                    <form
                                        action="{{ route('admin.settings.health-profile.document.upload', ['document' => $document['key']]) }}"
                                        method="POST"
                                        enctype="multipart/form-data"
                                        class="mhp-document-upload-form"
                                        data-document-upload-form
                                    >
                                        @csrf
                                        <input
                                            type="file"
                                            name="document"
                                            accept=".pdf,.jpg,.jpeg,.png"
                                            data-document-upload-input
                                            hidden
                                        >
                                        <button
                                            type="button"
                                            class="mhp-ghost-button"
                                            title="Upload {{ $document['type'] }} (maximum 1 MB)"
                                            aria-label="Upload {{ $document['type'] }} (maximum 1 MB)"
                                            data-document-upload-trigger
                                        >
                                            <x-outline-icon name="plus" />
                                            <span>Upload</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                @endif
            </table>
        </div>
    </section>
    @endif
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const page = document.querySelector('.my-health-profile-page');
        if (!page) {
            return;
        }

        const versionButtons = Array.from(page.querySelectorAll('[data-version-select]'));
        const versionRows = Array.from(page.querySelectorAll('[data-version-row]'));
        const versionStateLabels = Array.from(page.querySelectorAll('[data-version-state]'));
        const documentViews = Array.from(page.querySelectorAll('[data-document-version]'));

        function selectVersion(version) {
            versionButtons.forEach((button) => {
                const isSelected = button.dataset.versionSelect === version;
                button.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
                button.classList.toggle('is-selected', isSelected);
            });
            versionRows.forEach((row) => {
                row.classList.toggle('is-selected', row.dataset.versionRow === version);
            });
            versionStateLabels.forEach((label) => {
                label.textContent = label.dataset.versionState === version ? 'Selected' : 'Select';
            });
            documentViews.forEach((view) => {
                view.hidden = view.dataset.documentVersion !== version;
            });
        }

        versionButtons.forEach((button) => {
            button.addEventListener('click', () => selectVersion(button.dataset.versionSelect));
        });

        page.querySelectorAll('[data-document-upload-form]').forEach((form) => {
            const input = form.querySelector('[data-document-upload-input]');
            const trigger = form.querySelector('[data-document-upload-trigger]');
            if (!input || !trigger) {
                return;
            }

            trigger.addEventListener('click', () => input.click());
            input.addEventListener('change', () => {
                const file = input.files?.[0];
                if (!file) {
                    return;
                }

                if (file.size > 1024 * 1024) {
                    window.alert('The document must not be larger than 1 MB.');
                    input.value = '';
                    return;
                }

                form.submit();
            });
        });
    })();
</script>
@endpush
