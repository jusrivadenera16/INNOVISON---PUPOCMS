@extends('layouts.admin')

@section('title', 'Medical Configuration')

@push('styles')
@include('admin.partials.settings-section-style')
@endpush

@section('content')
<div class="settings-section-page">
    <section class="settings-section-hero">
        <div>
            <h1 class="settings-section-title"><x-outline-icon name="clipboard-document-list" />Medical Configuration</h1>
            <p>Static configuration preview for clinical setup. For now, this page only shows Medical Conditions and Medicine Types.</p>
        </div>
        <a href="{{ route('admin.settings') }}" class="settings-back-link"><x-outline-icon name="chevron-right" /> Settings Hub</a>
    </section>

    <div class="settings-section-grid two">
        <section class="settings-option-card">
            <div class="settings-option-icon">
                <x-outline-icon name="accessibility-person" />
            </div>
            <div>
                <h4>Medical Conditions</h4>
                <p>Static placeholder for configuring conditions used in consultations and reports.</p>
            </div>
        </section>

        <section class="settings-option-card">
            <div class="settings-option-icon">
                <x-outline-icon name="cube" />
            </div>
            <div>
                <h4>Medicine Types</h4>
                <p>Static placeholder for medicine type configuration used by inventory and MAR reporting.</p>
            </div>
        </section>
    </div>
</div>
@endsection
