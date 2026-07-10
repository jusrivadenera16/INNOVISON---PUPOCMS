@extends('layouts.admin')

@section('title', 'Token Activity - Recent History')
@section('disable_voice_inputs', 'true')

@section('content')
<style>
    .activity-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 24px;
    }

    .activity-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 32px;
        padding-bottom: 24px;
        border-bottom: 2px solid rgba(127, 29, 45, 0.1);
    }

    .activity-title {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .activity-title h1 {
        margin: 0;
        font-size: 1.8rem;
        font-weight: 900;
        color: #7f1d2d;
    }

    .activity-title p {
        margin: 0;
        color: #6b7280;
        font-size: 0.95rem;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 8px;
        border: 1px solid rgba(127, 29, 45, 0.2);
        background: white;
        color: #7f1d2d;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .back-link:hover {
        background: rgba(127, 29, 45, 0.05);
        border-color: rgba(127, 29, 45, 0.3);
    }

    html[data-theme="dark"] .activity-header {
        border-bottom-color: rgba(255, 255, 255, 0.1);
    }

    html[data-theme="dark"] .activity-title h1 {
        color: #f3d6da;
    }

    html[data-theme="dark"] .activity-title p {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .back-link {
        background: rgba(255, 255, 255, 0.05);
        color: #f3d6da;
        border-color: rgba(255, 255, 255, 0.1);
    }

    html[data-theme="dark"] .back-link:hover {
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(255, 255, 255, 0.2);
    }

    .activity-list {
        display: grid;
        gap: 12px;
    }

    .activity-item {
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 14px;
        align-items: center;
        padding: 16px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(127, 29, 45, 0.1);
        transition: all 0.2s ease;
    }

    .activity-item:hover {
        background: rgba(255, 255, 255, 0.98);
        border-color: rgba(127, 29, 45, 0.2);
        box-shadow: 0 4px 12px rgba(127, 29, 45, 0.08);
    }

    html[data-theme="dark"] .activity-item {
        background: rgba(35, 17, 25, 0.6);
        border-color: rgba(255, 255, 255, 0.08);
    }

    html[data-theme="dark"] .activity-item:hover {
        background: rgba(35, 17, 25, 0.8);
        border-color: rgba(255, 255, 255, 0.12);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .activity-dot {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: rgba(127, 29, 45, 0.1);
        color: #7f1d2d;
        flex-shrink: 0;
    }

    html[data-theme="dark"] .activity-dot {
        background: rgba(255, 255, 255, 0.1);
        color: #f3d6da;
    }

    .activity-content {
        display: grid;
        gap: 4px;
    }

    .activity-system {
        font-weight: 700;
        color: #7f1d2d;
        font-size: 0.95rem;
    }

    .activity-token-name {
        color: #6b7280;
        font-size: 0.9rem;
    }

    html[data-theme="dark"] .activity-system {
        color: #f3d6da;
    }

    html[data-theme="dark"] .activity-token-name {
        color: #cbd5e1;
    }

    .activity-time {
        color: #9ca3af;
        font-size: 0.85rem;
        white-space: nowrap;
    }

    html[data-theme="dark"] .activity-time {
        color: #6b7280;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6b7280;
    }

    .empty-state h3 {
        margin: 0 0 8px;
        color: #7f1d2d;
        font-size: 1.2rem;
    }

    html[data-theme="dark"] .empty-state h3 {
        color: #f3d6da;
    }

    .pagination-container {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 32px;
        padding-top: 24px;
        border-top: 1px solid rgba(127, 29, 45, 0.1);
    }

    html[data-theme="dark"] .pagination-container {
        border-top-color: rgba(255, 255, 255, 0.1);
    }

    .pagination-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        border: 1px solid rgba(127, 29, 45, 0.2);
        background: white;
        color: #7f1d2d;
        text-decoration: none;
        font-weight: 700;
        font-size: 0.9rem;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .pagination-link:hover {
        background: rgba(127, 29, 45, 0.05);
        border-color: rgba(127, 29, 45, 0.3);
    }

    .pagination-link.active {
        background: #7f1d2d;
        color: white;
        border-color: #7f1d2d;
    }

    .pagination-link.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    html[data-theme="dark"] .pagination-link {
        background: rgba(255, 255, 255, 0.05);
        color: #f3d6da;
        border-color: rgba(255, 255, 255, 0.1);
    }

    html[data-theme="dark"] .pagination-link:hover {
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(255, 255, 255, 0.2);
    }

    html[data-theme="dark"] .pagination-link.active {
        background: #f3d6da;
        color: #111827;
        border-color: #f3d6da;
    }

    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 32px;
    }

    .stat-card {
        padding: 16px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(127, 29, 45, 0.1);
    }

    html[data-theme="dark"] .stat-card {
        background: rgba(35, 17, 25, 0.6);
        border-color: rgba(255, 255, 255, 0.08);
    }

    .stat-label {
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b7280;
        margin-bottom: 6px;
    }

    html[data-theme="dark"] .stat-label {
        color: #cbd5e1;
    }

    .stat-value {
        font-size: 1.8rem;
        font-weight: 900;
        color: #7f1d2d;
    }

    html[data-theme="dark"] .stat-value {
        color: #f3d6da;
    }
</style>

<div class="activity-container">
    <div class="activity-header">
        <div class="activity-title">
            <h1>Token Activity</h1>
            <p>History of all generated tokens</p>
        </div>
        <a href="{{ route('admin.integration-tokens') }}" class="back-link">
            ← Back to Tokens
        </a>
    </div>

    @if($allTokens->total() > 0)
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-label">Total Tokens</div>
                <div class="stat-value">{{ $allTokens->total() }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Page {{ $allTokens->currentPage() }}</div>
                <div class="stat-value">of {{ $allTokens->lastPage() }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Per Page</div>
                <div class="stat-value">{{ $allTokens->perPage() }}</div>
            </div>
        </div>

        <div class="activity-list">
            @foreach($allTokens as $token)
                <div class="activity-item">
                    <span class="activity-dot">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m9 12 2 2 4-4"/><circle cx="12" cy="12" r="10"/>
                        </svg>
                    </span>
                    <div class="activity-content">
                        <div class="activity-system">
                            🔐 Token ID {{ $token->id }} - {{ $token->system_name }}
                        </div>
                        <div class="activity-token-name">
                            {{ $token->name }} (Key: {{ $token->system_key }})
                        </div>
                    </div>
                    <div class="activity-time">
                        <div>{{ $token->created_at->format('M d, Y') }}</div>
                        <small>{{ $token->created_at->format('h:i A') }}</small>
                    </div>
                </div>
            @endforeach
        </div>

        @if($allTokens->hasPages())
            <div class="pagination-container">
                @if($allTokens->onFirstPage())
                    <span class="pagination-link disabled">← Previous</span>
                @else
                    <a href="{{ $allTokens->previousPageUrl() }}" class="pagination-link">← Previous</a>
                @endif

                @foreach($allTokens->getUrlRange(1, $allTokens->lastPage()) as $page => $url)
                    @if($page == $allTokens->currentPage())
                        <span class="pagination-link active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="pagination-link">{{ $page }}</a>
                    @endif
                @endforeach

                @if($allTokens->hasMorePages())
                    <a href="{{ $allTokens->nextPageUrl() }}" class="pagination-link">Next →</a>
                @else
                    <span class="pagination-link disabled">Next →</span>
                @endif
            </div>
        @endif
    @else
        <div class="empty-state">
            <h3>No Token Activity Yet</h3>
            <p>No tokens have been generated. Create an integration client and generate a token to see activity here.</p>
            <a href="{{ route('admin.integration-tokens') }}" class="back-link" style="margin-top: 16px;">
                ← Go Back
            </a>
        </div>
    @endif
</div>
@endsection
