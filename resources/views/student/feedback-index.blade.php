@extends('layouts.student')

@section('title', 'All Feedback')

@push('styles')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    .feedback-page {
        max-width: 1080px;
        margin: 0 auto;
        padding: 18px 20px 60px;
    }

    .feedback-page-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 22px;
    }

    .feedback-page-title {
        margin: 0;
        font-size: 28px;
        color: #600000;
        font-weight: 800;
    }

    .feedback-page-text {
        margin: 6px 0 0;
        color: #64748b;
        font-size: 14px;
        line-height: 1.6;
    }

    .feedback-page-back {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 42px;
        height: 42px;
        border-radius: 999px;
        text-decoration: none;
        border: 1px solid #ead7d7;
        background: #fff;
        color: #8B0000;
        font-size: 22px;
        font-weight: 800;
        box-shadow: 0 8px 20px rgba(16,24,28,0.06);
    }

    .feedback-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
    }

    .feedback-card {
        background: #fff;
        padding: 18px;
        border-radius: 12px;
        box-shadow: 0 8px 28px rgba(16,24,28,0.06);
        display: flex;
        gap: 12px;
        align-items: flex-start;
        border: 1px solid #eef2f3;
    }

    .feedback-avatar {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        flex: 0 0 56px;
        overflow: hidden;
        fill: #cbd5e0;
    }

    .feedback-body {
        flex: 1;
        min-width: 0;
    }

    .feedback-body h4 {
        margin: 0;
        font-size: 15px;
        color: #2d3748;
    }

    .feedback-meta {
        color: #7f8b8e;
        font-size: 13px;
        font-weight: normal;
    }

    .feedback-message {
        margin: 10px 0 0;
        color: #345;
        line-height: 1.55;
        font-size: 14px;
    }

    .feedback-footer {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 12px;
    }

    .feedback-chip {
        background: rgba(15,27,38,0.04);
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 13px;
        color: #334;
    }

    .feedback-empty {
        background: #fff;
        border: 1px dashed #d8dee4;
        border-radius: 14px;
        padding: 32px 24px;
        text-align: center;
        color: #64748b;
    }

    @media (max-width: 900px) {
        .feedback-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 600px) {
        .feedback-grid { grid-template-columns: 1fr; }
    }

    .student-feedback-index-csp-1 { display:none; }
</style>
@endpush

@section('content')
<svg class="student-feedback-index-csp-1">
  <symbol id="feedback-avatar-placeholder" viewBox="0 0 24 24">
    <circle cx="12" cy="12" r="12" fill="#e2e8f0"/>
    <path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10zm0 2c-5.33 0-8 2.67-8 4v2h16v-2c0-1.33-2.67-4-8-4z" fill="#cbd5e0"/>
  </symbol>
</svg>

<div class="feedback-page">
    <div class="feedback-page-head">
        <div>
            <h1 class="feedback-page-title">All Feedback</h1>
            <p class="feedback-page-text">Browse recent clinic feedback shared by students and staff after completed appointments.</p>
        </div>
        <a href="{{ url('/student/home') }}" class="feedback-page-back" aria-label="Back to home" title="Back to home">&lt;</a>
    </div>

    @if(($feedbackCount ?? 0) > 0)
        <div class="feedback-grid">
            @foreach(($allFeedback ?? []) as $feedback)
                <article class="feedback-card">
                    <svg class="feedback-avatar" role="img" aria-label="User avatar"><use href="#feedback-avatar-placeholder"></use></svg>
                    <div class="feedback-body">
                        <h4>{{ $feedback['name'] }} <span class="feedback-meta">· {{ $feedback['role'] }} · {{ $feedback['time'] }}</span></h4>
                        <p class="feedback-message">{{ $feedback['message'] }}</p>
                        <div class="feedback-footer">
                            <span class="feedback-chip">{{ $feedback['service'] }}</span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <div class="feedback-empty">
            No submitted feedback is available yet.
        </div>
    @endif
</div>
@endsection
