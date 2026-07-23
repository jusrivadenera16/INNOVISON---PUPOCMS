@extends('layouts.admin')

@section('title', 'Medical Configuration')

@push('styles')
@include('admin.partials.settings-section-style')
<style>
    .medical-config-list {
        display: grid;
        grid-template-columns: 1fr;
        gap: 14px;
    }
    .medical-config-actions {
        display: none;
    }
    .medical-config-edit {
        text-decoration: none;
    }
    .medical-config-row {
        position: relative;
        min-height: 88px;
        display: grid;
        grid-template-columns: 70px minmax(150px, 210px) minmax(0, 1fr) 32px;
        align-items: center;
        gap: 18px;
        overflow: hidden;
        padding: 16px 20px;
        border-radius: 8px;
        border: 1px solid rgba(112, 19, 27, 0.14);
        background: #ffffff;
        color: #111827;
        text-decoration: none;
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.06);
        transition: transform .22s ease, border-color .22s ease, box-shadow .22s ease, background .22s ease, color .22s ease;
    }
    .medical-config-row > * {
        position: relative;
        z-index: 1;
    }
    .medical-config-row::after {
        content: "";
        position: absolute;
        top: -42%;
        bottom: -42%;
        left: -125%;
        width: 42%;
        opacity: 0;
        background: linear-gradient(105deg, rgba(255,255,255,0) 0%, rgba(255,248,196,.42) 48%, rgba(255,255,255,0) 100%);
        transform: translateX(0) skewX(-18deg);
        pointer-events: none;
        z-index: 0;
    }
    .medical-config-row:hover,
    .medical-config-row:focus-visible {
        background: #facc15;
        color: #70131B;
        transform: translateY(-3px);
        border-color: #facc15;
        box-shadow: 0 16px 28px rgba(112, 19, 27, 0.14);
        outline: none;
    }
    .medical-config-row:hover::after,
    .medical-config-row:focus-visible::after {
        animation: medicalConfigSweep .92s ease both;
    }
    @keyframes medicalConfigSweep {
        0% { opacity: 0; transform: translateX(0) skewX(-18deg); }
        18%, 72% { opacity: .72; }
        100% { opacity: 0; transform: translateX(720%) skewX(-18deg); }
    }
    .medical-config-main {
        display: contents;
        min-width: 0;
    }
    .medical-config-icon {
        width: 50px;
        height: 50px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        border-radius: 999px;
        color: #70131B;
        background: #fff7ed;
        border: 1px solid rgba(112, 19, 27, .10);
        transition: background .22s ease, border-color .22s ease, color .22s ease;
    }
    .medical-config-icon svg {
        width: 24px;
        height: 24px;
    }
    .medical-config-copy {
        display: contents;
    }
    .medical-config-copy h4 {
        margin: 0;
        color: #111827;
        font-size: 15px;
        font-weight: 900;
        line-height: 1.2;
        transition: color .22s ease;
    }
    .medical-config-copy p {
        margin: 0;
        color: #475569;
        font-size: 12px;
        line-height: 1.45;
        font-weight: 700;
        transition: color .22s ease;
    }
    .medical-config-arrow {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        color: #70131B;
        background: transparent;
        border: 0;
        flex: 0 0 auto;
        transition: transform .2s ease, color .2s ease, background .2s ease, border-color .2s ease;
    }
    .medical-config-arrow svg {
        width: 18px;
        height: 18px;
    }
    .medical-config-row:hover .medical-config-arrow,
    .medical-config-row:focus-visible .medical-config-arrow {
        color: #70131B;
        transform: translateX(4px);
    }
    .medical-config-row:hover .medical-config-copy h4,
    .medical-config-row:hover .medical-config-copy p,
    .medical-config-row:focus-visible .medical-config-copy h4,
    .medical-config-row:focus-visible .medical-config-copy p {
        color: #70131B;
    }
    .medical-config-row:hover .medical-config-icon,
    .medical-config-row:focus-visible .medical-config-icon {
        color: #70131B;
        background: rgba(255, 255, 255, .46);
        border-color: rgba(112, 19, 27, .24);
    }
    html[data-theme="dark"] .medical-config-row {
        background: #ffffff;
        border-color: rgba(112, 19, 27, .14);
    }
    html[data-theme="dark"] .medical-config-row:hover,
    html[data-theme="dark"] .medical-config-row:focus-visible {
        background: #facc15;
    }
    @media (max-width: 720px) {
        .medical-config-row {
            grid-template-columns: 56px minmax(0, 1fr) 30px;
            gap: 12px;
            min-height: 96px;
        }
        .medical-config-copy p {
            grid-column: 2 / 3;
        }
        .medical-config-arrow {
            grid-column: 3;
            grid-row: 1 / span 2;
        }
    }
</style>
@endpush

@section('content')
@php
    $currentMonth = now()->format('Y-m');
@endphp
<div class="settings-section-page">
    <section class="settings-section-hero">
        <div>
            <h1 class="settings-section-title"><x-outline-icon name="clipboard-document-list" />Medical Configuration</h1>
            <p>Manage clinical reference records used by reports, consultations, inventory, and MAR workflows.</p>
        </div>
    </section>

    <div class="medical-config-list">
        <a href="{{ route('admin.reports.manage-mar', ['month' => $currentMonth]) }}" class="medical-config-row">
            <div class="medical-config-main">
                <div class="medical-config-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/>
                    </svg>
                </div>
                <div class="medical-config-copy">
                    <h4>Medical Conditions</h4>
                    <p>Manage medical conditions and sub-categories used in consultations and Medical Accomplishment Reports.</p>
                </div>
            </div>
            <div class="medical-config-arrow">
                <x-outline-icon name="chevron-right" />
            </div>
        </a>

        <a href="{{ route('admin.reports.manage-medicine-types', ['month' => $currentMonth]) }}" class="medical-config-row">
            <div class="medical-config-main">
                <div class="medical-config-icon">
                    <x-outline-icon name="cube" />
                </div>
                <div class="medical-config-copy">
                    <h4>Medicine Types</h4>
                    <p>Manage medicine type references used by inventory records and MAR medicine reporting.</p>
                </div>
            </div>
            <div class="medical-config-arrow">
                <x-outline-icon name="chevron-right" />
            </div>
        </a>

        <a href="{{ route('admin.reports.manage-health-form-categories') }}" class="medical-config-row">
            <div class="medical-config-main">
                <div class="medical-config-icon">
                    <x-outline-icon name="document-text" />
                </div>
                <div class="medical-config-copy">
                    <h4>Health Form Categories</h4>
                    <p>Manage Health Form request purposes such as OJT, annual updates, and medical clearance.</p>
                </div>
            </div>
            <div class="medical-config-arrow">
                <x-outline-icon name="chevron-right" />
            </div>
        </a>
    </div>
</div>
@endsection
