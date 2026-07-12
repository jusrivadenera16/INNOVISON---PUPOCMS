@extends('layouts.admin')

@section('title', 'Medical Configuration')

@push('styles')
@include('admin.partials.settings-section-style')
<style>
    .medical-config-list {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }
    .medical-config-actions {
        display: none;
    }
    .medical-config-edit {
        text-decoration: none;
    }
    .medical-config-row {
        position: relative;
        min-height: 250px;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        overflow: hidden;
        padding: 20px;
        border-radius: 16px;
        border: 1px solid rgba(112, 19, 27, 0.38);
        background: #70131B;
        color: #ffffff;
        text-decoration: none;
        box-shadow: 0 14px 26px rgba(112, 19, 27, 0.18);
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
        transform: translateY(-6px);
        border-color: #facc15;
        box-shadow: 0 20px 34px rgba(112, 19, 27, 0.20);
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
        display: grid;
        gap: 18px;
        min-width: 0;
    }
    .medical-config-icon {
        width: 58px;
        height: 58px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        border-radius: 14px;
        color: #facc15;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, .18);
        transition: background .22s ease, border-color .22s ease, color .22s ease;
    }
    .medical-config-icon svg {
        width: 30px;
        height: 30px;
    }
    .medical-config-copy h4 {
        margin: 0 0 5px;
        color: #ffffff;
        font-size: 19px;
        font-weight: 900;
        line-height: 1.15;
        transition: color .22s ease;
    }
    .medical-config-copy p {
        max-width: 420px;
        margin: 0;
        color: rgba(255,255,255,.92);
        font-size: 14px;
        line-height: 1.48;
        font-weight: 700;
        transition: color .22s ease;
    }
    .medical-config-arrow {
        position: absolute;
        top: 20px;
        right: 18px;
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        color: #ffffff;
        background: rgba(255,255,255,.10);
        border: 1px solid rgba(255,255,255,.28);
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
        background: rgba(112, 19, 27, .10);
        border-color: rgba(112, 19, 27, .22);
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
        color: #facc15;
        background: rgba(112, 19, 27, .16);
        border-color: rgba(112, 19, 27, .24);
    }
    html[data-theme="dark"] .medical-config-row {
        background: linear-gradient(135deg, #70131B, #8f2230);
        border-color: rgba(250, 204, 21, .36);
    }
    html[data-theme="dark"] .medical-config-row:hover,
    html[data-theme="dark"] .medical-config-row:focus-visible {
        background: #facc15;
    }
    @media (max-width: 720px) {
        .medical-config-list {
            grid-template-columns: 1fr;
        }
        .medical-config-row {
            min-height: 220px;
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
    </div>
</div>
@endsection
