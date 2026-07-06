@extends('layouts.admin')

@section('title', 'Medical Configuration')

@push('styles')
@include('admin.partials.settings-section-style')
<style>
    .medical-config-list {
        display: grid;
        gap: 16px;
    }
    .medical-config-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .medical-config-edit {
        text-decoration: none;
    }
    .medical-config-row {
        min-height: 92px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 18px 22px;
        border-radius: 10px;
        border: 1px solid rgba(127, 0, 0, 0.12);
        background: #ffffff;
        color: var(--stg-text);
        text-decoration: none;
        box-shadow: 0 12px 26px rgba(15, 23, 42, 0.06);
        transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
    }
    .medical-config-row:hover,
    .medical-config-row:focus-visible {
        transform: translateY(-2px);
        border-color: rgba(250, 204, 21, 0.8);
        box-shadow: 0 18px 34px rgba(127, 0, 0, 0.12);
        outline: none;
    }
    .medical-config-main {
        display: flex;
        align-items: center;
        gap: 18px;
        min-width: 0;
    }
    .medical-config-icon {
        width: 58px;
        height: 58px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        border-radius: 50%;
        color: var(--stg-maroon);
        background: linear-gradient(135deg, rgba(127, 0, 0, .08), rgba(250, 204, 21, .18));
    }
    .medical-config-icon svg {
        width: 30px;
        height: 30px;
    }
    .medical-config-copy h4 {
        margin: 0 0 5px;
        color: var(--stg-text);
        font-size: 16px;
        font-weight: 900;
    }
    .medical-config-copy p {
        max-width: 620px;
        margin: 0;
        color: var(--stg-muted);
        font-size: 12px;
        line-height: 1.45;
        font-weight: 650;
    }
    .medical-config-arrow {
        color: var(--stg-maroon);
        flex: 0 0 auto;
        transition: transform .2s ease, color .2s ease;
    }
    .medical-config-arrow svg {
        width: 18px;
        height: 18px;
    }
    .medical-config-row:hover .medical-config-arrow,
    .medical-config-row:focus-visible .medical-config-arrow {
        color: #047857;
        transform: translateX(4px);
    }
    @media (max-width: 720px) {
        .medical-config-row {
            align-items: flex-start;
        }
        .medical-config-actions {
            width: 100%;
            justify-content: flex-start;
            flex-wrap: wrap;
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
        <div class="medical-config-actions">
            <a href="{{ route('admin.reports.manage-mar', ['month' => $currentMonth]) }}" class="settings-edit-btn medical-config-edit">
                <x-outline-icon name="pencil-square" />
                <span>Edit</span>
            </a>
            <a href="{{ route('admin.settings') }}" class="settings-back-link"><x-outline-icon name="chevron-right" /> Settings Hub</a>
        </div>
    </section>

    <div class="medical-config-list">
        <a href="{{ route('admin.reports.manage-mar', ['month' => $currentMonth]) }}" class="medical-config-row">
            <div class="medical-config-main">
                <div class="medical-config-icon">
                    <x-outline-icon name="accessibility-person" />
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
