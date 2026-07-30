@extends('layouts.admin')

@section('title', 'Integration Tokens Manager')
@section('disable_voice_inputs', 'true')

@section('content')
@php
    $clients = $integrationClients->sortBy('system_name')->values();
    $activeCount = $clients->where('is_active', true)->count();
    $totalTokens = $clients->sum(fn ($client) => $client->tokens->count());
    $revokedCount = 0;
    $latestToken = $clients
        ->flatMap(fn ($client) => $client->tokens->map(fn ($token) => ['token' => $token, 'client' => $client]))
        ->sortByDesc(fn ($item) => optional($item['token']->created_at)->timestamp ?? 0)
        ->first();
    $recentTokens = $clients
        ->flatMap(fn ($client) => $client->tokens->map(fn ($token) => ['token' => $token, 'client' => $client]))
        ->sortByDesc(fn ($item) => optional($item['token']->created_at)->timestamp ?? 0)
        ->take(4)
        ->values();
    $requestLogs = collect($integrationRequestLogs ?? []);
    $errorLogs = collect($apiErrorLogs ?? []);
    $clientLogPayload = $clients->mapWithKeys(function ($client) use ($requestLogs, $errorLogs) {
        $clientKey = strtolower((string) $client->system_key);
        $clientName = strtolower((string) $client->system_name);
        $clientRequestLogs = $requestLogs
            ->filter(fn ($log) => (int) ($log->integration_client_id ?? 0) === (int) $client->id
                || strtolower((string) ($log->system_key ?? '')) === $clientKey)
            ->take(30)
            ->map(fn ($log) => [
                'type' => ((int) ($log->status_code ?? 0)) >= 400 ? 'error' : 'request',
                'title' => trim(($log->http_method ?? 'GET') . ' ' . ($log->endpoint ?? '/')),
                'status' => $log->status_code ? 'HTTP ' . $log->status_code : 'No status',
                'meta' => trim(($log->response_time_ms !== null ? $log->response_time_ms . ' ms' : 'No timing') . ' | ' . ($log->ip_address ?? 'No IP')),
                'message' => $log->error_message ?: 'Request completed.',
                'time' => $log->created_at ? \Carbon\Carbon::parse($log->created_at)->format('M d, Y h:i A') : 'N/A',
            ])
            ->values();
        $clientErrorLogs = $errorLogs
            ->filter(fn ($log) => in_array(strtolower((string) ($log->system_name ?? '')), [$clientKey, $clientName], true))
            ->take(20)
            ->map(fn ($log) => [
                'type' => 'error',
                'title' => ($log->endpoint ?? 'External API') . ' error',
                'status' => $log->http_status ? 'HTTP ' . $log->http_status : ($log->error_code ?? 'Error'),
                'meta' => trim(($log->response_time_ms !== null ? $log->response_time_ms . ' ms' : 'No timing') . ' | ' . ($log->ip_address ?? 'No IP')),
                'message' => $log->error_message ?? 'External API error recorded.',
                'time' => $log->created_at ? \Carbon\Carbon::parse($log->created_at)->format('M d, Y h:i A') : 'N/A',
            ])
            ->values();
        $tokenEvents = $client->tokens
            ->sortByDesc('created_at')
            ->take(20)
            ->map(fn ($token) => [
                'type' => 'token',
                'title' => 'Token generated',
                'status' => 'Token ID ' . $token->id,
                'meta' => 'Abilities: ' . implode(', ', $token->abilities ?? []),
                'message' => $token->last_used_at ? 'Last used ' . $token->last_used_at->diffForHumans() : 'No API call recorded for this token yet.',
                'time' => $token->created_at ? $token->created_at->format('M d, Y h:i A') : 'N/A',
            ])
            ->values();

        return [
            (string) $client->id => [
                'name' => $client->system_name,
                'key' => $client->system_key,
                'logs' => $clientRequestLogs->concat($clientErrorLogs)->concat($tokenEvents)
                    ->sortByDesc(fn ($log) => strtotime($log['time']) ?: 0)
                    ->values(),
            ],
        ];
    });
@endphp

<style>
    .integration-page {
        display: grid;
        gap: 18px;
        color: #111827;
    }

    .integration-shell {
        border: 1px solid rgba(127, 29, 45, 0.12);
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.96);
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.10);
        padding: 22px;
    }

    html[data-theme="dark"] .integration-shell {
        background: rgba(20, 12, 18, 0.94);
        border-color: rgba(255, 255, 255, 0.10);
        color: #f8fafc;
    }

    .integration-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 18px;
    }

    .integration-title-wrap {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .integration-title-icon,
    .stat-icon,
    .system-avatar,
    .detail-avatar,
    .security-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .integration-title-icon {
        width: 46px;
        height: 46px;
        border-radius: 15px;
        background: #fff1f2;
        color: #9f1239;
        border: 1px solid rgba(159, 18, 57, 0.12);
    }

    .integration-title h1 {
        margin: 0;
        font-size: 1.45rem;
        font-weight: 900;
        color: #111827;
    }

    .integration-title p {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 0.9rem;
    }

    html[data-theme="dark"] .integration-title h1 {
        color: #ffffff;
    }

    html[data-theme="dark"] .integration-title p {
        color: #cbd5e1;
    }

    .integration-controls {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .integration-search,
    .integration-filter {
        height: 44px;
        border-radius: 12px;
        border: 1px solid rgba(148, 163, 184, 0.28);
        background: #fff;
        color: #0f172a;
        font-weight: 700;
    }

    .integration-search {
        min-width: 250px;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 0 14px;
    }

    .integration-search input {
        border: 0;
        outline: 0;
        width: 100%;
        background: transparent;
        color: inherit;
        font-weight: 700;
    }

    .integration-search input::placeholder {
        color: #94a3b8;
    }

    .integration-filter {
        padding: 0 14px;
        min-width: 130px;
        outline: none;
    }

    html[data-theme="dark"] .integration-search,
    html[data-theme="dark"] .integration-filter {
        background: rgba(255, 255, 255, 0.06);
        color: #f8fafc;
        border-color: rgba(255, 255, 255, 0.12);
    }

    .token-pin-modal {
        position: fixed;
        inset: 0;
        z-index: 5010;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(15, 23, 42, 0.56);
        backdrop-filter: blur(8px);
    }

    .token-pin-modal.is-open {
        display: flex;
    }

    .token-pin-dialog {
        width: min(620px, 100%);
        overflow: hidden;
        border-radius: 18px;
        border: 1px solid rgba(255, 255, 255, 0.18);
        background: #ffffff;
        box-shadow: 0 24px 70px rgba(15, 23, 42, 0.26);
    }

    .token-pin-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 14px;
        padding: 20px 22px;
        background: linear-gradient(135deg, #9d1427 0%, #710012 100%);
        color: #ffffff;
    }

    .token-pin-head-main {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .token-pin-head-icon,
    .token-pin-warning-icon,
    .token-pin-entry-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }

    .token-pin-head-icon {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.22);
    }

    .token-pin-head-icon svg {
        width: 24px;
        height: 24px;
    }

    .token-pin-head h3 {
        margin: 0;
        color: #ffffff !important;
        font-size: 1.22rem;
        font-weight: 950;
    }

    .token-pin-head p {
        margin: 4px 0 0;
        color: rgba(255, 255, 255, 0.88) !important;
        font-size: 0.9rem;
    }

    .token-pin-close {
        width: 40px;
        height: 40px;
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.20);
        color: #ffffff;
        cursor: pointer;
        font-size: 26px;
        line-height: 1;
    }

    .token-pin-body {
        display: grid;
        gap: 16px;
        padding: 22px;
    }

    .token-pin-warning {
        display: flex;
        gap: 12px;
        padding: 14px;
        border-radius: 16px;
        border: 1px solid rgba(250, 204, 21, 0.34);
        background: #fff8df;
        color: #78350f;
    }

    .token-pin-warning-icon {
        width: 34px;
        height: 34px;
        border-radius: 11px;
        background: #fff1b8;
        color: #8a1220;
    }

    .token-pin-warning strong,
    .token-pin-warning span {
        display: block;
    }

    .token-pin-warning strong {
        font-weight: 950;
        color: #70131b;
    }

    .token-pin-warning span {
        margin-top: 2px;
        font-size: 0.85rem;
        line-height: 1.4;
    }

    .token-pin-entry {
        text-align: center;
        padding: 18px;
        border-radius: 18px;
        border: 1px solid rgba(127, 29, 45, 0.12);
        background: #fffafa;
    }

    .token-pin-entry-icon {
        width: 54px;
        height: 54px;
        margin: 0 auto 10px;
        border-radius: 18px;
        background: #fff1f2;
        color: #8a1220;
    }

    .token-pin-entry-icon svg {
        width: 26px;
        height: 26px;
    }

    .token-pin-entry-title {
        color: #111827;
        font-size: 1rem;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .token-pin-entry-copy {
        margin-top: 5px;
        color: #64748b;
        font-size: 0.88rem;
    }

    .token-pin-digits {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 16px;
    }

    .token-pin-digits input {
        width: 54px;
        height: 58px;
        border-radius: 14px;
        border: 1px solid rgba(127, 29, 45, 0.22);
        background: #ffffff;
        color: #111827;
        text-align: center;
        font-size: 1.45rem;
        font-weight: 950;
        outline: none;
    }

    .token-pin-digits input:focus {
        border-color: #facc15;
        box-shadow: 0 0 0 4px rgba(250, 204, 21, 0.22);
    }

    .token-pin-hidden {
        position: absolute;
        opacity: 0;
        pointer-events: none;
        width: 1px;
        height: 1px;
    }

    .token-pin-safe {
        margin-top: 12px;
        color: #64748b;
        font-size: 0.82rem;
        font-weight: 800;
    }

    .token-pin-error {
        display: none;
        padding: 10px 12px;
        border-radius: 12px;
        background: #fee2e2;
        color: #991b1b;
        font-size: 0.82rem;
        font-weight: 850;
    }

    .token-pin-error.is-visible {
        display: block;
    }

    .token-pin-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .token-pin-cancel,
    .token-pin-submit {
        min-height: 42px;
        border-radius: 12px;
        padding: 0 18px;
        font-weight: 950;
        cursor: pointer;
    }

    .token-pin-cancel {
        border: 1px solid rgba(148, 163, 184, 0.34);
        background: #ffffff;
        color: #70131b;
    }

    .token-pin-submit {
        border: 1px solid #8a1220;
        background: #8a1220;
        color: #ffffff;
    }

    html[data-theme="dark"] .token-pin-dialog,
    html[data-theme="dark"] .token-pin-entry {
        background: #111827;
        border-color: rgba(250, 204, 21, 0.20);
    }

    html[data-theme="dark"] .token-pin-warning {
        background: rgba(250, 204, 21, 0.10);
        border-color: rgba(250, 204, 21, 0.26);
        color: #fde68a;
    }

    html[data-theme="dark"] .token-pin-warning strong,
    html[data-theme="dark"] .token-pin-entry-title {
        color: #ffffff;
    }

    html[data-theme="dark"] .token-pin-entry-copy,
    html[data-theme="dark"] .token-pin-safe {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .token-pin-digits input,
    html[data-theme="dark"] .token-pin-cancel {
        background: rgba(255, 255, 255, 0.06);
        color: #f8fafc;
        border-color: rgba(250, 204, 21, 0.20);
    }

    .integration-primary {
        height: 44px;
        border: 0;
        border-radius: 12px;
        background: #8a1220;
        color: #fff;
        padding: 0 18px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        box-shadow: 0 14px 28px rgba(138, 18, 32, 0.18);
    }

    .integration-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .stat-card {
        min-height: 100px;
        border-radius: 18px;
        border: 1px solid rgba(127, 29, 45, 0.10);
        background: linear-gradient(180deg, #ffffff, #fffafa);
        padding: 18px;
        display: flex;
        align-items: center;
        gap: 14px;
    }

    html[data-theme="dark"] .stat-card {
        background: linear-gradient(180deg, rgba(42, 20, 28, 0.95), rgba(31, 15, 22, 0.98));
        border-color: rgba(255, 255, 255, 0.10);
    }

    .stat-icon {
        width: 54px;
        height: 54px;
        border-radius: 50%;
    }

    .stat-icon.green {
        background: #dcfce7;
        color: #16a34a;
    }

    .stat-icon.purple {
        background: #f3e8ff;
        color: #7e22ce;
    }

    .stat-icon.red {
        background: #fee2e2;
        color: #dc2626;
    }

    .stat-icon.blue {
        background: #dbeafe;
        color: #2563eb;
    }

    .stat-label {
        display: block;
        color: #64748b;
        font-size: 0.78rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .stat-value {
        display: block;
        margin-top: 5px;
        color: #111827;
        font-size: 1.55rem;
        font-weight: 950;
    }

    .stat-sub {
        display: block;
        margin-top: 2px;
        color: #64748b;
        font-size: 0.8rem;
        font-weight: 700;
    }

    html[data-theme="dark"] .stat-value {
        color: #ffffff;
    }

    html[data-theme="dark"] .stat-label,
    html[data-theme="dark"] .stat-sub {
        color: #cbd5e1;
    }

    .integration-workspace {
        display: grid;
        grid-template-columns: 320px minmax(0, 1fr);
        gap: 18px;
        align-items: start;
    }

    .integration-panel {
        border: 1px solid rgba(127, 29, 45, 0.10);
        border-radius: 18px;
        background: #fff;
        padding: 14px;
    }

    html[data-theme="dark"] .integration-panel {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 255, 255, 0.10);
    }

    .panel-title {
        margin: 4px 4px 12px;
        color: #111827;
        font-size: 0.88rem;
        font-weight: 950;
    }

    html[data-theme="dark"] .panel-title {
        color: #ffffff;
    }

    .system-list {
        display: grid;
        gap: 8px;
    }

    .system-item {
        width: 100%;
        border: 1px solid rgba(127, 29, 45, 0.10);
        background: #fff;
        border-radius: 14px;
        padding: 12px;
        display: grid;
        grid-template-columns: 42px 1fr auto;
        gap: 10px;
        align-items: center;
        text-align: left;
        cursor: pointer;
        transition: transform 0.18s ease, border-color 0.18s ease, background 0.18s ease;
    }

    .system-item:hover,
    .system-item.active {
        transform: translateY(-1px);
        background: #fff7f7;
        border-color: rgba(138, 18, 32, 0.55);
    }

    html[data-theme="dark"] .system-item {
        background: rgba(255, 255, 255, 0.04);
        border-color: rgba(255, 255, 255, 0.10);
    }

    html[data-theme="dark"] .system-item:hover,
    html[data-theme="dark"] .system-item.active {
        background: rgba(138, 18, 32, 0.28);
    }

    .system-avatar,
    .detail-avatar {
        background: #9f1239;
        color: #fff;
        font-weight: 950;
        border-radius: 50%;
    }

    .system-avatar {
        width: 38px;
        height: 38px;
    }

    .detail-avatar {
        width: 58px;
        height: 58px;
        font-size: 1.25rem;
    }

    .system-item strong {
        display: block;
        color: #111827;
        font-size: 0.86rem;
        font-weight: 950;
    }

    .system-item small {
        display: block;
        margin-top: 3px;
        color: #64748b;
        font-weight: 800;
    }

    html[data-theme="dark"] .system-item strong {
        color: #fff;
    }

    html[data-theme="dark"] .system-item small {
        color: #cbd5e1;
    }

    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
        background: #22c55e;
    }

    .status-dot.warn {
        background: #f59e0b;
    }

    .item-last-used {
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 800;
        text-align: right;
    }

    .detail-card {
        display: none;
    }

    .detail-card.active {
        display: block;
    }

    .detail-head {
        display: flex;
        justify-content: space-between;
        gap: 14px;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .detail-title {
        display: flex;
        align-items: center;
        gap: 13px;
    }

    .detail-title h2 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 950;
        color: #111827;
    }

    html[data-theme="dark"] .detail-title h2 {
        color: #fff;
    }

    .system-key-line {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 5px;
        color: #64748b;
        font-size: 0.86rem;
        font-weight: 800;
    }

    .connected-pill,
    .permission-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 5px 10px;
        font-size: 0.74rem;
        font-weight: 950;
    }

    .connected-pill {
        background: #dcfce7;
        color: #15803d;
    }

    .connected-pill.muted {
        background: #fef3c7;
        color: #92400e;
    }

    .detail-menu {
        width: 42px;
        height: 42px;
        border: 1px solid rgba(148, 163, 184, 0.25);
        border-radius: 12px;
        background: #fff;
        color: #64748b;
        cursor: pointer;
    }

    html[data-theme="dark"] .detail-menu {
        background: rgba(255, 255, 255, 0.06);
        color: #f8fafc;
    }

    .token-row {
        margin: 12px 0;
    }

    .masked-token {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 10px;
        align-items: center;
    }

    .masked-token-value {
        border-radius: 12px;
        padding: 13px 14px;
        background: #fff7f7;
        border: 1px solid rgba(127, 29, 45, 0.10);
        color: #64748b;
        font-family: Consolas, 'Courier New', monospace;
        font-weight: 800;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    html[data-theme="dark"] .masked-token-value {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 255, 255, 0.10);
        color: #e2e8f0;
    }

    .outline-btn,
    .danger-btn,
    .solid-btn {
        border-radius: 12px;
        min-height: 42px;
        padding: 0 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-weight: 950;
        cursor: pointer;
        border: 1px solid rgba(127, 29, 45, 0.20);
        background: #fff;
        color: #8a1220;
    }

    .solid-btn {
        background: #8a1220;
        color: #fff;
        border-color: #8a1220;
    }

    .danger-btn {
        background: #fff1f2;
        color: #be123c;
        border-color: rgba(225, 29, 72, 0.24);
    }

    html[data-theme="dark"] .outline-btn {
        background: rgba(255, 255, 255, 0.06);
        color: #f8fafc;
        border-color: rgba(255, 255, 255, 0.12);
    }

    .token-facts {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin: 14px 0;
    }

    .fact-card {
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 14px;
        padding: 12px;
        background: #fff;
        min-height: 82px;
    }

    html[data-theme="dark"] .fact-card {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 255, 255, 0.10);
    }

    .fact-card span {
        display: block;
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .fact-card strong {
        display: block;
        margin-top: 8px;
        color: #111827;
        font-size: 0.9rem;
        font-weight: 950;
    }

    .fact-card small {
        display: block;
        margin-top: 2px;
        color: #64748b;
        font-weight: 750;
    }

    html[data-theme="dark"] .fact-card strong {
        color: #fff;
    }

    .detail-actions {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-top: 14px;
    }

    .generated-token-panel {
        display: none;
        margin-top: 18px;
        padding: 18px;
        border-radius: 16px;
        border: 1px solid rgba(250, 204, 21, 0.35);
        background: rgba(250, 204, 21, 0.12);
    }

    .generated-token-panel.show {
        display: block;
    }

    .lower-grid {
        display: grid;
        grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.1fr);
        gap: 18px;
        margin-top: 18px;
    }

    .activity-list {
        display: grid;
        gap: 12px;
    }

    .activity-item {
        display: grid;
        grid-template-columns: 34px 1fr auto;
        gap: 10px;
        align-items: center;
    }

    .activity-dot {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #dcfce7;
        color: #15803d;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .activity-item strong {
        display: block;
        font-size: 0.86rem;
        color: #111827;
    }

    .activity-item span,
    .activity-item small {
        color: #64748b;
        font-size: 0.76rem;
        font-weight: 750;
    }

    html[data-theme="dark"] .activity-item strong {
        color: #fff;
    }

    .integration-log-modal {
        position: fixed;
        inset: 0;
        z-index: 6000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 22px;
        background: rgba(15, 23, 42, 0.56);
        backdrop-filter: blur(10px);
    }

    .integration-log-modal.show {
        display: flex;
    }

    .integration-log-dialog {
        width: min(980px, 100%);
        max-height: min(760px, calc(100vh - 44px));
        overflow: hidden;
        display: flex;
        flex-direction: column;
        border-radius: 22px;
        background: #ffffff;
        border: 1px solid rgba(127, 29, 45, 0.16);
        box-shadow: 0 28px 72px rgba(15, 23, 42, 0.28);
    }

    html[data-theme="dark"] .integration-log-dialog {
        background: #171017;
        border-color: rgba(250, 204, 21, 0.16);
    }

    .integration-log-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 18px 20px;
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #ffffff;
    }

    .integration-log-head h3 {
        margin: 0;
        color: #ffffff;
        font-size: 1.08rem;
        font-weight: 950;
    }

    .integration-log-head p {
        margin: 4px 0 0;
        color: rgba(255,255,255,.82);
        font-size: .84rem;
        font-weight: 750;
    }

    .integration-log-close {
        width: 40px;
        height: 40px;
        border-radius: 999px;
        border: 1px solid rgba(250, 204, 21, .3);
        background: rgba(0, 0, 0, .18);
        color: #ffffff;
        cursor: pointer;
        font-size: 1.4rem;
        line-height: 1;
    }

    .integration-log-body {
        overflow-y: auto;
        padding: 18px;
    }

    .integration-log-list {
        display: grid;
        gap: 12px;
    }

    .integration-log-item {
        display: grid;
        grid-template-columns: 36px minmax(0, 1fr) auto;
        gap: 12px;
        align-items: start;
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 14px;
        padding: 14px;
        background: #fffafa;
    }

    html[data-theme="dark"] .integration-log-item {
        background: rgba(255, 255, 255, .05);
        border-color: rgba(255, 255, 255, .10);
    }

    .integration-log-dot {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: #dbeafe;
        color: #2563eb;
        font-weight: 950;
    }

    .integration-log-dot.error {
        background: #fee2e2;
        color: #dc2626;
    }

    .integration-log-dot.token {
        background: #fef3c7;
        color: #b45309;
    }

    .integration-log-item strong {
        display: block;
        color: #111827;
        font-size: .92rem;
        font-weight: 950;
    }

    html[data-theme="dark"] .integration-log-item strong {
        color: #ffffff;
    }

    .integration-log-item span,
    .integration-log-item p {
        color: #64748b;
        font-size: .8rem;
        font-weight: 750;
    }

    .integration-log-item p {
        margin: 6px 0 0;
        line-height: 1.5;
    }

    .integration-log-time {
        color: #64748b;
        font-size: .76rem;
        font-weight: 850;
        white-space: nowrap;
    }

    .usage-card {
        min-height: 180px;
        display: grid;
        align-content: center;
        gap: 8px;
    }

    .usage-svg {
        width: 100%;
        height: 120px;
    }

    .security-row {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .security-card {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(127, 29, 45, 0.10);
        border-radius: 16px;
        padding: 14px;
        background: #fff;
        display: flex;
        gap: 12px;
        align-items: center;
        transition: background .22s ease, border-color .22s ease, color .22s ease, transform .22s ease, box-shadow .22s ease;
    }

    html[data-theme="dark"] .security-card {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 255, 255, 0.10);
    }

    .security-icon {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: #f8fafc;
        color: #64748b;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
    }

    .security-card strong {
        display: block;
        color: #111827;
        font-size: 0.86rem;
    }

    .security-card span {
        display: block;
        margin-top: 2px;
        color: #64748b;
        font-size: 0.78rem;
        font-weight: 750;
    }

    html[data-theme="dark"] .security-card strong {
        color: #fff;
    }

    .security-card.docs-card {
        border-color: rgba(250, 204, 21, .55);
        background: #8f1827;
        color: #ffffff !important;
        cursor: pointer;
        text-align: left;
        box-shadow: 0 16px 30px rgba(112, 19, 27, .14);
    }

    .security-card.docs-card::after {
        content: "";
        position: absolute;
        top: -45%;
        bottom: -45%;
        left: -130%;
        width: 42%;
        opacity: 0;
        background: linear-gradient(105deg, rgba(255,255,255,0) 0%, rgba(255,248,196,.42) 48%, rgba(255,255,255,0) 100%);
        transform: translateX(0) skewX(-18deg);
        pointer-events: none;
    }

    .security-card.docs-card:hover,
    .security-card.docs-card:focus-visible {
        background: #facc15;
        border-color: #facc15;
        color: #ffffff !important;
        transform: translateY(-3px);
        box-shadow: 0 18px 34px rgba(112, 19, 27, .18);
        outline: none;
    }

    .security-card.docs-card:hover::after,
    .security-card.docs-card:focus-visible::after {
        animation: docsCardSweep .92s ease both;
    }

    @keyframes docsCardSweep {
        0% { opacity: 0; transform: translateX(0) skewX(-18deg); }
        18%, 72% { opacity: .72; }
        100% { opacity: 0; transform: translateX(720%) skewX(-18deg); }
    }

    .security-card.docs-card .security-icon {
        border-radius: 12px;
        background: rgba(255, 255, 255, .14);
        border: 1px solid rgba(255, 255, 255, .18);
        color: #facc15;
        display: grid;
        place-items: center;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    .security-card.docs-card .security-icon svg {
        display: block;
        margin: auto;
    }

    .security-card.docs-card strong,
    .security-card.docs-card span,
    .security-card.docs-card span span {
        color: #ffffff !important;
    }

    .security-card.docs-card:hover .security-icon,
    .security-card.docs-card:focus-visible .security-icon {
        background: rgba(112, 19, 27, .12);
        border-color: rgba(112, 19, 27, .24);
        color: #ffffff;
    }

    .security-card.docs-card:hover strong,
    .security-card.docs-card:hover span,
    .security-card.docs-card:hover span span,
    .security-card.docs-card:focus-visible strong,
    .security-card.docs-card:focus-visible span,
    .security-card.docs-card:focus-visible span span {
        color: #ffffff !important;
    }

    html[data-theme="dark"] .security-card.docs-card {
        background: #8f1827;
        border-color: rgba(250, 204, 21, .55);
    }

    html[data-theme="dark"] .security-card.docs-card:hover,
    html[data-theme="dark"] .security-card.docs-card:focus-visible {
        background: #facc15;
        border-color: #facc15;
    }

    .sr-hidden {
        display: none !important;
    }

    @media (max-width: 1180px) {
        .integration-stats,
        .token-facts,
        .detail-actions,
        .security-row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .integration-workspace,
        .lower-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 720px) {
        .integration-header,
        .detail-head,
        .masked-token {
            flex-direction: column;
            display: flex;
            align-items: stretch;
        }

        .integration-controls,
        .integration-search,
        .integration-filter,
        .integration-primary {
            width: 100%;
        }

        .integration-stats,
        .token-facts,
        .detail-actions,
        .security-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="integration-page">
    <section class="integration-shell">
        <div class="integration-header">
            <div class="integration-title-wrap">
                <span class="integration-title-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                        <path d="m9 12 2 2 4-4"/>
                    </svg>
                </span>
                <div class="integration-title">
                    <h1>Integration Tokens Manager</h1>
                    <p>Manage API tokens for external system integrations.</p>
                </div>
            </div>
            <div class="integration-controls">
                <label class="integration-search">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                    </svg>
                    <input id="integrationSearch" type="search" placeholder="Search integrations...">
                </label>
                <select id="integrationStatusFilter" class="integration-filter" aria-label="Filter integrations">
                    <option value="all">All Status</option>
                    <option value="connected">Connected</option>
                    <option value="no-token">No Token</option>
                    <option value="inactive">Inactive</option>
                </select>
                <button type="button" class="integration-primary" onclick="openCreateClientModal()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    Add Client
                </button>
            </div>
        </div>

        <div id="tokenAlert" class="token-alert"></div>

        <div class="integration-stats">
            <div class="stat-card">
                <span class="stat-icon green">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m9 12 2 2 4-4"/><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                    </svg>
                </span>
                <div>
                    <span class="stat-label">Active Systems</span>
                    <span class="stat-value">{{ $activeCount }}</span>
                    <span class="stat-sub">of {{ $clients->count() }} total</span>
                </div>
            </div>
            <div class="stat-card">
                <span class="stat-icon purple">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 7h.01"/><path d="M10 13a5 5 0 1 0 4-8 5 5 0 0 0-4 8L2 21l3-3h3v-3h3l-1-2Z"/>
                    </svg>
                </span>
                <div>
                    <span class="stat-label">Generated Tokens</span>
                    <span class="stat-value">{{ $totalTokens }}</span>
                    <span class="stat-sub">stored securely</span>
                </div>
            </div>
            <div class="stat-card">
                <span class="stat-icon red">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect width="18" height="12" x="3" y="6" rx="2"/><path d="M8 12h8"/>
                    </svg>
                </span>
                <div>
                    <span class="stat-label">Revoked Tokens</span>
                    <span class="stat-value">{{ $revokedCount }}</span>
                    <span class="stat-sub">not retained by Sanctum</span>
                </div>
            </div>
            <div class="stat-card">
                <span class="stat-icon blue">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
                    </svg>
                </span>
                <div>
                    <span class="stat-label">Last Generated</span>
                    <span class="stat-value" style="font-size:1rem;">
                        {{ $latestToken ? optional($latestToken['token']->created_at)->format('M d, h:i A') : 'N/A' }}
                    </span>
                    <span class="stat-sub">{{ $latestToken['client']->system_name ?? 'No tokens yet' }}</span>
                </div>
            </div>
        </div>

        <div class="integration-workspace">
            <aside class="integration-panel">
                <h2 class="panel-title">Integrations</h2>
                <div class="system-list" id="systemList">
                    @forelse($clients as $client)
                        @php
                            $latest = $client->tokens->sortByDesc('created_at')->first();
                            $hasToken = (bool) $latest;
                            $status = !$client->is_active ? 'inactive' : ($hasToken ? 'connected' : 'no-token');
                            $initial = strtoupper(substr($client->system_name, 0, 1));
                        @endphp
                        <button
                            type="button"
                            class="system-item {{ $loop->first ? 'active' : '' }}"
                            data-client-id="{{ $client->id }}"
                            data-name="{{ strtolower($client->system_name . ' ' . $client->system_key) }}"
                            data-status="{{ $status }}"
                            onclick="selectIntegration('{{ $client->id }}')"
                        >
                            <span class="system-avatar">{{ $initial }}</span>
                            <span>
                                <span style="font-weight: 600;">{{ $client->system_name }}</span>
                                <small><span class="status-dot {{ $hasToken && $client->is_active ? '' : 'warn' }}"></span><span class="system-status-text">{{ $client->is_active ? ($hasToken ? 'Connected' : 'No token') : 'Inactive' }}</span></small>
                            </span>
                            <span class="item-last-used">
                                {{ $latest && $latest->last_used_at ? $latest->last_used_at->diffForHumans() : ($latest ? 'Never used' : 'No token') }}
                            </span>
                        </button>
                    @empty
                        <div class="no-token-message">No integration clients found.</div>
                    @endforelse
                </div>
            </aside>

            <section class="integration-panel">
                @forelse($clients as $client)
                    @php
                        $latest = $client->tokens->sortByDesc('created_at')->first();
                        $hasToken = (bool) $latest;
                        $initial = strtoupper(substr($client->system_name, 0, 1));
                        $abilities = $latest ? implode(', ', $latest->abilities ?? []) : 'None';
                    @endphp
                    <article
                        class="detail-card {{ $loop->first ? 'active' : '' }}"
                        id="integrationDetail-{{ $client->id }}"
                        data-client-id="{{ $client->id }}"
                        data-system-name="{{ $client->system_name }}"
                    >
                        <div class="detail-head">
                            <div class="detail-title">
                                <span class="detail-avatar">{{ $initial }}</span>
                                <div>
                                    <h2>{{ $client->system_name }}</h2>
                                    <div class="system-key-line">
                                        System Key: <strong>{{ $client->system_key }}</strong>
                                        <button type="button" class="detail-menu" style="width:30px;height:30px;" onclick="copyText('{{ $client->system_key }}')" title="Copy system key">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect width="14" height="14" x="8" y="8" rx="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <span class="connected-pill client-status-pill {{ $hasToken && $client->is_active ? '' : 'muted' }}">
                                    {{ $client->is_active ? ($hasToken ? 'Connected' : 'No Token') : 'Inactive' }}
                                </span>
                            </div>
                            <button type="button" class="detail-menu" title="More options">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/>
                                </svg>
                            </button>
                        </div>

                        <div class="token-row">
                            <span class="token-label">API Token</span>
                            <div class="masked-token">
                                <div class="masked-token-value">
                                    {{ $hasToken ? 'Token ID ' . $latest->id . '  |  ' . str_repeat('*', 80) : 'No token generated yet' }}
                                </div>
                                <button type="button" class="outline-btn copy-detail-token-btn" id="copyDetailToken_{{ $client->id }}" onclick="copyGeneratedTokenFromDetail('{{ $client->id }}')" {{ $hasToken ? '' : 'disabled' }}>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect width="14" height="14" x="8" y="8" rx="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>
                                    </svg>
                                    <span>Copy Token</span>
                                </button>
                            </div>
                        </div>

                        <div class="token-facts">
                            <div class="fact-card">
                                <span>Created</span>
                                <span class="token-created-date" style="font-weight: 600; display: block; margin: 4px 0;">{{ $hasToken ? $latest->created_at->format('M d, Y') : 'N/A' }}</span>
                                <small class="token-created-time">{{ $hasToken ? $latest->created_at->format('h:i A') : 'No token' }}</small>
                            </div>
                            <div class="fact-card">
                                <span>Last Used</span>
                                <span style="font-weight: 600; display: block; margin: 4px 0;">{{ $hasToken && $latest->last_used_at ? $latest->last_used_at->diffForHumans() : 'Never' }}</span>
                                <small>{{ $hasToken && $latest->last_used_at ? $latest->last_used_at->format('M d, h:i A') : 'No API call yet' }}</small>
                            </div>
                            <div class="fact-card">
                                <span>Expires</span>
                                <span style="font-weight: 600; display: block; margin: 4px 0;">Never</span>
                                <small>No expiration</small>
                            </div>
                            <div class="fact-card">
                                <span>Permissions</span>
                                <span class="token-ability-count" style="font-weight: 600; display: block; margin: 4px 0;">{{ $hasToken ? count($latest->abilities ?? []) . ' abilities' : 'None' }}</span>
                                <small class="token-ability-list">{{ $abilities }}</small>
                            </div>
                        </div>

                        <div class="detail-actions">
                            <button type="button" class="solid-btn" onclick="generateToken('{{ $client->id }}', '{{ $client->system_name }}')">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 5v14M5 12h14"/>
                                </svg>
                                Generate New Token
                            </button>
                            <button type="button" class="outline-btn" onclick="generateToken('{{ $client->id }}', '{{ $client->system_name }}')">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/>
                                </svg>
                                Rotate Token
                            </button>
                            <button type="button" class="danger-btn" onclick="revokeToken('{{ $client->id }}', '{{ $client->system_name }}', '{{ $latest->id ?? '' }}')" {{ $hasToken ? '' : 'disabled' }}>
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="m19 6-1 14H6L5 6"/>
                                </svg>
                                Revoke Token
                            </button>
                            <button type="button" class="outline-btn" onclick="openIntegrationLogs('{{ $client->id }}')">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M4 4v15.5A2.5 2.5 0 0 0 6.5 22H20V6a2 2 0 0 0-2-2H4Z"/>
                                </svg>
                                View Logs
                            </button>
                        </div>
                    </article>
                @empty
                    <div class="no-token-message">No integration client selected.</div>
                @endforelse
            </section>
        </div>

    </section>

    <div class="lower-grid">
        <section class="integration-panel">
            <div class="detail-head" style="margin-bottom:12px;">
                <h2 class="panel-title" style="margin:0;">Recent Activity</h2>
                <a href="{{ route('admin.integration-tokens.activity') }}" class="outline-btn" style="min-height:34px; display: inline-flex; align-items: center;">View All</a>
            </div>
            <div class="activity-list">
                @forelse($recentTokens as $item)
                    <div class="activity-item">
                        <span class="activity-dot">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="m9 12 2 2 4-4"/><circle cx="12" cy="12" r="10"/>
                            </svg>
                        </span>
                        <span>
                            <strong>Token generated by {{ $item['client']->system_name }}</strong>
                            <span>{{ $item['token']->name }}</span>
                        </span>
                        <small>{{ $item['token']->created_at->diffForHumans() }}</small>
                    </div>
                @empty
                    <div class="no-token-message">No token activity yet.</div>
                @endforelse
            </div>
        </section>

        <section class="integration-panel usage-card">
            <div class="detail-head" style="margin:0;">
                <div>
                    <h2 class="panel-title" style="margin:0;">API Usage</h2>
                    <p class="stat-sub">Last used timestamps are stored per token.</p>
                </div>
                <strong style="color:#8a1220;">{{ $totalTokens }} tokens</strong>
            </div>
            <svg class="usage-svg" viewBox="0 0 520 140" preserveAspectRatio="none">
                <defs>
                    <linearGradient id="usageFill" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#9f1239" stop-opacity="0.28"/>
                        <stop offset="100%" stop-color="#9f1239" stop-opacity="0"/>
                    </linearGradient>
                </defs>
                <path d="M0 115 C70 20 130 92 190 58 C260 18 310 112 370 58 C430 6 466 76 520 48" fill="none" stroke="#9f1239" stroke-width="5" stroke-linecap="round"/>
                <path d="M0 115 C70 20 130 92 190 58 C260 18 310 112 370 58 C430 6 466 76 520 48 L520 140 L0 140 Z" fill="url(#usageFill)"/>
            </svg>
        </section>
    </div>

    <section class="security-row">
        <div class="security-card">
            <span class="security-icon">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
            </span>
            <span><strong>Hashed Tokens</strong><span>Plaintext never stored</span></span>
        </div>
        <div class="security-card">
            <span class="security-icon">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                </svg>
            </span>
            <span><strong>Scoped Abilities</strong><span>Read, write, medical status</span></span>
        </div>
        <div class="security-card">
            <span class="security-icon">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                </svg>
            </span>
            <span><strong>Rotation Ready</strong><span>Generate, test, revoke</span></span>
        </div>
        <button type="button" class="security-card docs-card" onclick="window.location.href='{{ route('admin.integration-tokens.docs') }}';">
            <span class="security-icon">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M4 4v15.5A2.5 2.5 0 0 0 6.5 22H20V6a2 2 0 0 0-2-2H4Z"/>
                </svg>
            </span>
            <span><strong>Developer Docs</strong><span>Use Bearer token headers</span></span>
        </button>
    </section>
</div>

<div id="integrationLogsModal" class="integration-log-modal" aria-hidden="true">
    <div class="integration-log-dialog" role="dialog" aria-modal="true" aria-labelledby="integrationLogsTitle">
        <div class="integration-log-head">
            <div>
                <h3 id="integrationLogsTitle">Integration Logs</h3>
                <p id="integrationLogsSubtitle">Recent token activity, external API requests, and errors.</p>
            </div>
            <button type="button" class="integration-log-close" onclick="closeIntegrationLogs()" aria-label="Close integration logs">&times;</button>
        </div>
        <div class="integration-log-body">
            <div id="integrationLogsList" class="integration-log-list"></div>
        </div>
    </div>
</div>

<div class="token-pin-modal" id="tokenActionPinModal" aria-hidden="true">
    <section class="token-pin-dialog" role="dialog" aria-modal="true" aria-labelledby="tokenActionPinTitle">
        <header class="token-pin-head">
            <div class="token-pin-head-main">
                <span class="token-pin-head-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2.75 4.75 5.5v5.4c0 4.72 3.02 8.92 7.25 10.35 4.23-1.43 7.25-5.63 7.25-10.35V5.5L12 2.75Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.25 11.25v-1.5a3.25 3.25 0 1 0-6.5 0v1.5m-.5 0h7.5v5.5h-7.5v-5.5Z" />
                    </svg>
                </span>
                <div>
                    <h3 id="tokenActionPinTitle">Integration PIN</h3>
                    <p id="tokenActionPinSubtitle">Enter your 4-digit PIN to continue.</p>
                </div>
            </div>
            <button type="button" class="token-pin-close" id="closeTokenActionPinModal" aria-label="Close PIN modal">&times;</button>
        </header>
        <form class="token-pin-body" id="tokenActionPinForm">
            <div class="token-pin-warning">
                <span class="token-pin-warning-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.75A11.959 11.959 0 0 1 12 2.714Z" />
                    </svg>
                </span>
                <div>
                    <strong>Administrator Verification Required</strong>
                    <span>Token generation, rotation, revocation, reveal, and copy actions require your Integration PIN.</span>
                </div>
            </div>
            <div class="token-pin-entry">
                <span class="token-pin-entry-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 0 0-9 0v3.75m-.75 0h10.5c.621 0 1.125.504 1.125 1.125v7.5c0 .621-.504 1.125-1.125 1.125H6.75a1.125 1.125 0 0 1-1.125-1.125v-7.5c0-.621.504-1.125 1.125-1.125Z" />
                    </svg>
                </span>
                <div class="token-pin-entry-title">Enter 4-Digit PIN</div>
                <div class="token-pin-entry-copy">Please enter your 4-digit Integration PIN.</div>
                <div class="token-pin-digits" id="tokenActionPinDigits">
                    <input type="password" inputmode="numeric" maxlength="1" autocomplete="one-time-code" aria-label="PIN digit 1" required>
                    <input type="password" inputmode="numeric" maxlength="1" autocomplete="one-time-code" aria-label="PIN digit 2" required>
                    <input type="password" inputmode="numeric" maxlength="1" autocomplete="one-time-code" aria-label="PIN digit 3" required>
                    <input type="password" inputmode="numeric" maxlength="1" autocomplete="one-time-code" aria-label="PIN digit 4" required>
                </div>
                <input class="token-pin-hidden" type="password" id="tokenActionPinInput" name="pin" pattern="[0-9]{4}" maxlength="4" tabindex="-1">
                <div class="token-pin-safe">Your PIN is encrypted and safe.</div>
            </div>
            <div class="token-pin-error" id="tokenActionPinError"></div>
            <div class="token-pin-actions">
                <button type="button" class="token-pin-cancel" id="cancelTokenActionPin">Cancel</button>
                <button type="submit" class="token-pin-submit">Verify PIN</button>
            </div>
        </form>
    </section>
</div>

<script>
    let latestGeneratedToken = '';
    let selectedClientId = document.querySelector('.system-item')?.dataset.clientId || null;
    let integrationPinRequired = null;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const integrationLogData = @json($clientLogPayload);
    const tokenActionPinModal = document.getElementById('tokenActionPinModal');
    const tokenActionPinForm = document.getElementById('tokenActionPinForm');
    const tokenActionPinInput = document.getElementById('tokenActionPinInput');
    const tokenActionPinDigits = Array.from(document.querySelectorAll('#tokenActionPinDigits input'));
    const tokenActionPinError = document.getElementById('tokenActionPinError');
    const tokenActionPinSubtitle = document.getElementById('tokenActionPinSubtitle');
    const closeTokenActionPinModal = document.getElementById('closeTokenActionPinModal');
    const cancelTokenActionPin = document.getElementById('cancelTokenActionPin');
    let tokenActionPinResolver = null;

    function syncTokenActionPinInput() {
        if (!tokenActionPinInput) {
            return;
        }
        tokenActionPinInput.value = tokenActionPinDigits.map((input) => input.value).join('');
    }

    function clearTokenActionPin() {
        tokenActionPinDigits.forEach((input) => {
            input.value = '';
        });
        if (tokenActionPinInput) {
            tokenActionPinInput.value = '';
        }
        if (tokenActionPinError) {
            tokenActionPinError.textContent = '';
            tokenActionPinError.classList.remove('is-visible');
        }
    }

    function setTokenActionPinModalOpen(isOpen, actionLabel = 'continue') {
        if (!tokenActionPinModal) {
            return;
        }
        tokenActionPinModal.classList.toggle('is-open', isOpen);
        tokenActionPinModal.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        if (isOpen) {
            clearTokenActionPin();
            if (tokenActionPinSubtitle) {
                tokenActionPinSubtitle.textContent = `Enter your 4-digit PIN to ${actionLabel}.`;
            }
            window.setTimeout(() => tokenActionPinDigits[0]?.focus(), 80);
        }
    }

    function openTokenActionPinModal(actionLabel) {
        return new Promise((resolve) => {
            tokenActionPinResolver = resolve;
            setTokenActionPinModalOpen(true, actionLabel);
        });
    }

    function resolveTokenActionPin(pin) {
        if (tokenActionPinResolver) {
            tokenActionPinResolver(pin);
            tokenActionPinResolver = null;
        }
        setTokenActionPinModalOpen(false);
    }

    tokenActionPinDigits.forEach((input, index) => {
        input.addEventListener('input', () => {
            input.value = input.value.replace(/\D/g, '').slice(0, 1);
            syncTokenActionPinInput();

            if (input.value && tokenActionPinDigits[index + 1]) {
                tokenActionPinDigits[index + 1].focus();
            }
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Backspace' && !input.value && tokenActionPinDigits[index - 1]) {
                tokenActionPinDigits[index - 1].focus();
            }
        });

        input.addEventListener('paste', (event) => {
            event.preventDefault();
            const pasted = (event.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, tokenActionPinDigits.length);
            pasted.split('').forEach((value, pasteIndex) => {
                if (tokenActionPinDigits[pasteIndex]) {
                    tokenActionPinDigits[pasteIndex].value = value;
                }
            });
            syncTokenActionPinInput();
            tokenActionPinDigits[Math.min(pasted.length, tokenActionPinDigits.length) - 1]?.focus();
        });
    });

    tokenActionPinForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        syncTokenActionPinInput();
        const pin = (tokenActionPinInput?.value || '').trim();

        if (!/^\d{4}$/.test(pin)) {
            if (tokenActionPinError) {
                tokenActionPinError.textContent = 'Enter a valid 4-digit Integration PIN.';
                tokenActionPinError.classList.add('is-visible');
            }
            tokenActionPinDigits.find((input) => !input.value)?.focus();
            return;
        }

        resolveTokenActionPin(pin);
    });

    closeTokenActionPinModal?.addEventListener('click', () => resolveTokenActionPin(null));
    cancelTokenActionPin?.addEventListener('click', () => resolveTokenActionPin(null));
    tokenActionPinModal?.addEventListener('click', (event) => {
        if (event.target === tokenActionPinModal) {
            resolveTokenActionPin(null);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && tokenActionPinModal?.classList.contains('is-open')) {
            resolveTokenActionPin(null);
        }
    });

    async function isIntegrationPinRequired() {
        try {
            const response = await fetch('{{ route('admin.integration-pin.status') }}', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            const data = await response.json();
            integrationPinRequired = Boolean(data?.state?.token_action_pin_enabled);

            if (data?.state?.disabled) {
                showAlert('Integration Tokens access is disabled.', true);
                return null;
            }

            return integrationPinRequired;
        } catch (error) {
            showAlert('Unable to check Integration PIN status.', true);
            return null;
        }
    }

    async function requestIntegrationPin(actionLabel = 'continue') {
        const required = await isIntegrationPinRequired();

        if (required === null) {
            return null;
        }

        if (!required) {
            return '';
        }

        const pin = await openTokenActionPinModal(actionLabel);

        if (pin === null) {
            return null;
        }

        if (!/^\d{4}$/.test(pin.trim())) {
            showAlert('Enter a valid 4-digit Integration PIN.', true);
            return null;
        }

        const trimmedPin = pin.trim();

        try {
            const response = await fetch('{{ route('admin.integration-pin.verify') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ pin: trimmedPin })
            });
            const data = await readJsonResponse(response);

            if (!data.success) {
                showAlert(data.message || 'Incorrect Integration PIN.', true);
                return null;
            }
        } catch (error) {
            showAlert('Unable to verify Integration PIN.', true);
            return null;
        }

        return trimmedPin;
    }

    async function readJsonResponse(response) {
        try {
            return await response.json();
        } catch (error) {
            return {
                success: false,
                message: response.ok ? 'Unexpected server response.' : 'Request failed.'
            };
        }
    }

    function selectIntegration(clientId) {
        selectedClientId = clientId;

        document.querySelectorAll('.system-item').forEach((item) => {
            item.classList.toggle('active', item.dataset.clientId === clientId);
        });

        document.querySelectorAll('.detail-card').forEach((card) => {
            card.classList.toggle('active', card.dataset.clientId === clientId);
        });
    }

    function generateSelectedToken() {
        const detail = document.querySelector(`.detail-card[data-client-id="${selectedClientId}"]`);
        if (!detail) {
            showAlert('Select an integration first.', true);
            return;
        }

        generateToken(selectedClientId, detail.dataset.systemName || 'selected system');
    }

    function openIntegrationLogs(clientId) {
        const modal = document.getElementById('integrationLogsModal');
        const title = document.getElementById('integrationLogsTitle');
        const subtitle = document.getElementById('integrationLogsSubtitle');
        const list = document.getElementById('integrationLogsList');
        const payload = integrationLogData[String(clientId)] || null;

        if (!modal || !list || !payload) {
            showAlert('No logs available for this integration.', true);
            return;
        }

        title.textContent = `${payload.name} Logs`;
        subtitle.textContent = `System key: ${payload.key}`;
        list.innerHTML = '';

        if (!payload.logs || payload.logs.length === 0) {
            list.innerHTML = '<div class="no-token-message">No request logs yet. New external API calls using this key will appear here after migration runs.</div>';
        } else {
            payload.logs.forEach((log) => {
                const item = document.createElement('article');
                item.className = 'integration-log-item';
                const dotClass = log.type === 'error' ? 'error' : (log.type === 'token' ? 'token' : '');
                const dotText = log.type === 'error' ? '!' : (log.type === 'token' ? 'T' : '✓');
                item.innerHTML = `
                    <span class="integration-log-dot ${dotClass}">${dotText}</span>
                    <span>
                        <strong>${escapeHtml(log.title || 'Integration activity')}</strong>
                        <span>${escapeHtml(log.status || 'No status')} · ${escapeHtml(log.meta || '')}</span>
                        <p>${escapeHtml(log.message || '')}</p>
                    </span>
                    <span class="integration-log-time">${escapeHtml(log.time || 'N/A')}</span>
                `;
                list.appendChild(item);
            });
        }

        modal.classList.add('show');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeIntegrationLogs() {
        const modal = document.getElementById('integrationLogsModal');
        modal?.classList.remove('show');
        modal?.setAttribute('aria-hidden', 'true');
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function copyToken(clientId, tokenValue) {
        if (!tokenValue) {
            showAlert('Plaintext token is only available immediately after generation. Generate a new token if needed.', true);
            return;
        }
        navigator.clipboard.writeText(tokenValue).then(() => {
            showAlert('Token copied to clipboard.');
        }).catch(() => {
            showAlert('Failed to copy token.', true);
        });
    }

    function copyText(text) {
        navigator.clipboard.writeText(text).then(() => {
            showAlert('Copied to clipboard.');
        }).catch(() => {
            showAlert('Failed to copy.', true);
        });
    }

    async function copyGeneratedTokenFromDetail(clientId) {
        if (!latestGeneratedToken) {
            showAlert('No token available to copy.', true);
            return;
        }

        const pin = await requestIntegrationPin('copy this token');
        if (pin === null) return;

        const copyBtn = document.getElementById(`copyDetailToken_${clientId}`);
        const originalContent = copyBtn.innerHTML;

        navigator.clipboard.writeText(latestGeneratedToken).then(() => {
            copyBtn.classList.add('copied');
            copyBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg><span>Copied</span>';

            setTimeout(() => {
                copyBtn.classList.remove('copied');
                copyBtn.innerHTML = originalContent;
            }, 2000);

            showAlert('Token copied to clipboard.');
        }).catch(() => {
            showAlert('Failed to copy token.', true);
        });
    }

    let tokenIsVisible = false;

    function updateGeneratedTokenUi(clientId, systemName, data) {
        const detail = document.querySelector(`.detail-card[data-client-id="${clientId}"]`);
        const systemItem = document.querySelector(`.system-item[data-client-id="${clientId}"]`);
        const tokenId = data.token_id || 'N/A';
        const abilities = Array.isArray(data.abilities) ? data.abilities : [];
        const createdDate = data.created_date || new Date().toLocaleDateString('en-US', {
            month: 'short',
            day: '2-digit',
            year: 'numeric'
        });
        const createdTime = data.created_time || new Date().toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit'
        });

        if (detail) {
            const statusPill = detail.querySelector('.client-status-pill');
            const tokenValue = detail.querySelector('.masked-token-value');
            const copyButton = detail.querySelector('.copy-detail-token-btn');
            const revokeButton = detail.querySelector('.danger-btn');
            const createdDateEl = detail.querySelector('.token-created-date');
            const createdTimeEl = detail.querySelector('.token-created-time');
            const abilityCount = detail.querySelector('.token-ability-count');
            const abilityList = detail.querySelector('.token-ability-list');

            statusPill?.classList.remove('muted');
            if (statusPill) statusPill.textContent = 'Connected';
            if (tokenValue) tokenValue.textContent = `Token ID ${tokenId}  |  ${'*'.repeat(80)}`;
            copyButton?.removeAttribute('disabled');
            if (createdDateEl) createdDateEl.textContent = createdDate;
            if (createdTimeEl) createdTimeEl.textContent = createdTime;
            if (abilityCount) abilityCount.textContent = `${abilities.length} ${abilities.length === 1 ? 'ability' : 'abilities'}`;
            if (abilityList) abilityList.textContent = abilities.length ? abilities.join(', ') : 'None';
            if (revokeButton) {
                revokeButton.removeAttribute('disabled');
                revokeButton.setAttribute('onclick', `revokeToken('${clientId}', '${String(systemName).replace(/'/g, "\\'")}', '${tokenId}')`);
            }
        }

        if (systemItem) {
            systemItem.dataset.status = 'connected';
            const statusDot = systemItem.querySelector('.status-dot');
            const statusText = systemItem.querySelector('.system-status-text');
            const lastUsed = systemItem.querySelector('.item-last-used');

            statusDot?.classList.remove('warn');
            if (statusText) statusText.textContent = 'Connected';
            if (lastUsed) lastUsed.textContent = 'Never used';
        }

        const statCards = document.querySelectorAll('.integration-stats .stat-card');
        const generatedCount = statCards[1]?.querySelector('.stat-value');
        const lastGeneratedDate = statCards[3]?.querySelector('.stat-value');
        const lastGeneratedSystem = statCards[3]?.querySelector('.stat-sub');

        if (generatedCount) {
            const currentCount = Number.parseInt(generatedCount.textContent.trim(), 10);
            generatedCount.textContent = Number.isNaN(currentCount) ? '1' : String(currentCount + 1);
        }
        if (lastGeneratedDate) lastGeneratedDate.textContent = `${createdDate.replace(',', '')}, ${createdTime}`;
        if (lastGeneratedSystem) lastGeneratedSystem.textContent = systemName;

        filterIntegrations();
    }

    async function toggleTokenVisibility() {
        const tokenBox = document.getElementById('generatedTokenValue');
        const eyeIcon = document.getElementById('tokenEyeIcon');

        if (!tokenIsVisible) {
            const pin = await requestIntegrationPin('reveal this token');
            if (pin === null) return;

            // Show token
            tokenBox.textContent = latestGeneratedToken;
            tokenBox.style.fontFamily = "'Courier New', monospace";
            eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/><line x1="1" y1="1" x2="23" y2="23"/>';
            tokenIsVisible = true;
        } else {
            // Hide token
            tokenBox.textContent = '*'.repeat(80);
            tokenBox.style.fontFamily = 'inherit';
            eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
            tokenIsVisible = false;
        }
    }

    function closeGeneratedTokenModal() {
        const modal = document.getElementById('generatedTokenModal');
        if (modal) {
            modal.classList.remove('show');
        }
        tokenIsVisible = false;
        latestGeneratedToken = '';
    }

    async function copyGeneratedToken() {
        if (!latestGeneratedToken) {
            showAlert('No token available to copy.', true);
            return;
        }

        const pin = await requestIntegrationPin('copy this token');
        if (pin === null) return;

        navigator.clipboard.writeText(latestGeneratedToken).then(() => {
            showAlert('Token copied to clipboard!');
        }).catch(() => {
            showAlert('Failed to copy token.', true);
        });
    }

    async function generateToken(clientId, systemName) {
        if (!confirm(`Generate a new API token for ${systemName}?`)) return;

        const pin = await requestIntegrationPin(`generate a token for ${systemName}`);
        if (pin === null) return;

        fetch('{{ route('admin.integration-tokens.generate') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ client_id: clientId, pin })
        })
        .then(readJsonResponse)
        .then(data => {
            if (data.success) {
                latestGeneratedToken = data.token || '';
                document.getElementById('generatedTokenValue').textContent = latestGeneratedToken;
                document.getElementById('generatedTokenMeta').innerHTML = `<strong>System:</strong> ${systemName} | <strong>Token ID:</strong> ${data.token_id || 'N/A'} | <strong>Abilities:</strong> ${(data.abilities || []).join(', ')}`;
                document.getElementById('generatedTokenModal').classList.add('show');
                updateGeneratedTokenUi(clientId, systemName, data);
                tokenIsVisible = false;
                document.getElementById('generatedTokenValue').style.fontFamily = 'inherit';
                document.getElementById('generatedTokenValue').textContent = '*'.repeat(80);

                if (latestGeneratedToken && navigator.clipboard) {
                    navigator.clipboard.writeText(latestGeneratedToken).catch(() => {});
                }
            } else {
                showAlert(data.message || 'Failed to generate token.', true);
            }
        })
        .catch(() => {
            showAlert('Error generating token.', true);
        });
    }

    async function revokeToken(clientId, systemName, tokenId = null) {
        if (!tokenId) {
            showAlert('No token to revoke for this integration.', true);
            return;
        }

        if (!confirm(`Revoke token for ${systemName}? This cannot be undone.`)) return;

        const pin = await requestIntegrationPin(`revoke the token for ${systemName}`);
        if (pin === null) return;

        fetch('{{ route('admin.integration-tokens.revoke') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ client_id: clientId, token_id: tokenId, pin })
        })
        .then(readJsonResponse)
        .then(data => {
            if (data.success) {
                showAlert(`Token revoked for ${systemName}.`);
                setTimeout(() => location.reload(), 1200);
            } else {
                showAlert(data.message || 'Failed to revoke token.', true);
            }
        })
        .catch(() => {
            showAlert('Error revoking token.', true);
        });
    }

    async function copyGeneratedToken() {
        if (!latestGeneratedToken) {
            showAlert('No newly generated token to copy.', true);
            return;
        }

        const pin = await requestIntegrationPin('copy this token');
        if (pin === null) return;

        const copyBtn = document.getElementById('copyButton');
        const originalContent = copyBtn.innerHTML;

        navigator.clipboard.writeText(latestGeneratedToken).then(() => {
            copyBtn.classList.add('copied');
            copyBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg><span>Copied</span>';

            setTimeout(() => {
                copyBtn.classList.remove('copied');
                copyBtn.innerHTML = originalContent;
            }, 2000);

            showAlert('Token copied to clipboard.');
        }).catch(() => {
            showAlert('Failed to copy token.', true);
        });
    }

    function showAlert(message, isError = false) {
        const alert = document.getElementById('tokenAlert');
        alert.textContent = message;
        alert.classList.add('show');
        alert.classList.toggle('error', isError);
        setTimeout(() => {
            alert.classList.remove('show', 'error');
        }, 3000);
    }

    function filterIntegrations() {
        const query = (document.getElementById('integrationSearch')?.value || '').toLowerCase().trim();
        const status = document.getElementById('integrationStatusFilter')?.value || 'all';
        let firstVisible = null;

        document.querySelectorAll('.system-item').forEach((item) => {
            const matchesSearch = !query || item.dataset.name.includes(query);
            const matchesStatus = status === 'all' || item.dataset.status === status;
            const isVisible = matchesSearch && matchesStatus;
            item.classList.toggle('sr-hidden', !isVisible);

            if (isVisible && !firstVisible) {
                firstVisible = item;
            }
        });

        if (firstVisible && document.querySelector('.system-item.active.sr-hidden')) {
            selectIntegration(firstVisible.dataset.clientId);
        }
    }

    document.getElementById('integrationSearch')?.addEventListener('input', filterIntegrations);
    document.getElementById('integrationStatusFilter')?.addEventListener('change', filterIntegrations);

    // Create Client Modal Functions
    function openCreateClientModal() {
        const modal = document.getElementById('createClientModal');
        if (modal) modal.classList.add('show');
    }

    function closeCreateClientModal() {
        const modal = document.getElementById('createClientModal');
        if (modal) modal.classList.remove('show');
        document.getElementById('createClientForm').reset();
    }

    async function createClient() {
        const systemKey = document.getElementById('systemKeyInput').value.trim();
        const systemName = document.getElementById('systemNameInput').value.trim();

        if (!systemKey || !systemName) {
            showAlert('Please fill in all fields', true);
            return;
        }

        const pin = await requestIntegrationPin(`create ${systemName}`);
        if (pin === null) return;

        fetch('{{ route('admin.integration-clients.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ system_key: systemKey, system_name: systemName, pin })
        })
        .then(readJsonResponse)
        .then(data => {
            if (data.success) {
                showAlert('Client created successfully!');
                closeCreateClientModal();
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(data.message || 'Failed to create client', true);
            }
        })
        .catch(err => {
            showAlert('Error creating client', true);
        });
    }

    // Close modal when clicking outside
    document.getElementById('createClientModal')?.addEventListener('click', (e) => {
        if (e.target.id === 'createClientModal') {
            closeCreateClientModal();
        }
    });

    document.getElementById('integrationLogsModal')?.addEventListener('click', (e) => {
        if (e.target.id === 'integrationLogsModal') {
            closeIntegrationLogs();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeIntegrationLogs();
        }
    });
</script>

<!-- Create Client Modal -->
<div id="createClientModal" class="modal-overlay" style="display:none;">
    <div class="modal-content" style="background: white; border-radius: 16px; overflow: hidden; max-width: 500px; box-shadow: 0 20px 60px rgba(0,0,0,0.2);">
        <!-- Header with Icon and Maroon Background -->
        <div style="background: linear-gradient(135deg, #8a1220, #6a0e18); padding: 24px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(255, 255, 255, 0.15); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <div>
                <h2 style="margin: 0; color: white; font-size: 1.3rem; font-weight: 900;">Create Integration Client</h2>
                <p style="margin: 4px 0 0; color: rgba(255, 255, 255, 0.85); font-size: 0.9rem;">Add a new external system to manage API tokens</p>
            </div>
        </div>

        <!-- Form Content -->
        <div style="padding: 28px;">
            <form id="createClientForm" onsubmit="event.preventDefault(); createClient();">
                <!-- System Key Field -->
                <div style="margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#7f1d2d" stroke-width="2" style="flex-shrink: 0;">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/><path d="M9 17l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8"/>
                        </svg>
                        <label for="systemKeyInput" style="color: #111827; font-size: 0.9rem; font-weight: 700;">System Key</label>
                    </div>
                    <input
                        type="text"
                        id="systemKeyInput"
                        placeholder="e.g., ris, ims, pupt_website"
                        style="width: 100%; border-radius: 8px; border: 1px solid #e5e7eb; padding: 12px 14px; font-size: 0.95rem; color: #111827; background: #f9fafb; transition: all 0.2s ease;"
                        onfocus="this.style.borderColor='#7f1d2d'; this.style.background='#fff';"
                        onblur="this.style.borderColor='#e5e7eb'; this.style.background='#f9fafb';"
                        required
                    >
                    <small style="display: block; margin-top: 6px; color: #9ca3af; font-size: 0.85rem;">Unique identifier for the system (lowercase, no spaces)</small>
                </div>

                <!-- System Name Field -->
                <div style="margin-bottom: 28px;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#7f1d2d" stroke-width="2" style="flex-shrink: 0;">
                            <circle cx="12" cy="12" r="1"/><path d="M12 1v6m0 6v6"/><path d="M4.22 4.22l4.24 4.24m5.08 5.08l4.24 4.24"/><path d="M1 12h6m6 0h6"/><path d="M4.22 19.78l4.24-4.24m5.08-5.08l4.24-4.24"/>
                        </svg>
                        <label for="systemNameInput" style="color: #111827; font-size: 0.9rem; font-weight: 700;">System Name</label>
                    </div>
                    <input
                        type="text"
                        id="systemNameInput"
                        placeholder="e.g., RIS, IMS, PUPT Website"
                        style="width: 100%; border-radius: 8px; border: 1px solid #e5e7eb; padding: 12px 14px; font-size: 0.95rem; color: #111827; background: #f9fafb; transition: all 0.2s ease;"
                        onfocus="this.style.borderColor='#7f1d2d'; this.style.background='#fff';"
                        onblur="this.style.borderColor='#e5e7eb'; this.style.background='#f9fafb';"
                        required
                    >
                    <small style="display: block; margin-top: 6px; color: #9ca3af; font-size: 0.85rem;">Display name for the system</small>
                </div>

                <!-- Buttons -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <button type="button" onclick="closeCreateClientModal()" style="border: 1.5px solid #d1d5db; border-radius: 8px; padding: 12px 18px; background: white; color: #7f1d2d; font-weight: 700; cursor: pointer; transition: all 0.2s ease; font-size: 0.95rem;">
                        Cancel
                    </button>
                    <button type="submit" style="border: none; border-radius: 8px; padding: 12px 18px; background: linear-gradient(135deg, #8a1220, #6a0e18); color: white; font-weight: 700; cursor: pointer; transition: all 0.2s ease; font-size: 0.95rem; box-shadow: 0 4px 12px rgba(138, 18, 32, 0.24);">
                        Create Client
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Generated Token Modal -->
<div id="generatedTokenModal" class="modal-overlay" style="display:none;">
    <div class="modal-content" style="background: white; border-radius: 16px; overflow: hidden; max-width: 550px; box-shadow: 0 20px 60px rgba(0,0,0,0.2);">
        <!-- Header - Maroon -->
        <div style="background: linear-gradient(135deg, #8a1220, #6a0e18); padding: 24px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(255, 255, 255, 0.15); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>
                </svg>
            </div>
            <div>
                <h2 style="margin: 0; color: white; font-size: 1.3rem; font-weight: 900;">Token Generated!</h2>
                <p style="margin: 4px 0 0; color: rgba(255, 255, 255, 0.85); font-size: 0.9rem;">Copy now, this is shown once only</p>
            </div>
        </div>

        <!-- Token Display -->
        <div style="padding: 28px;">
            <!-- Token Value -->
            <div style="margin-bottom: 20px;">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                    <!-- Key Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 18px; height: 18px; color: #7f1d2d; flex-shrink: 0;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
                    </svg>
                    <label style="color: #111827; font-size: 0.9rem; font-weight: 700;">API Token</label>
                    <button type="button" onclick="toggleTokenVisibility()" style="border: none; background: none; cursor: pointer; color: #7f1d2d; padding: 4px; display: flex; align-items: center; margin-left: auto;">
                        <svg id="tokenEyeIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <div id="generatedTokenValue" style="flex: 1; border-radius: 8px; border: 1px solid #e5e7eb; padding: 12px 14px; font-family: 'Courier New', monospace; font-size: 0.9rem; color: #111827; background: #f9fafb; word-break: break-all; max-height: 100px; overflow-y: auto;"></div>
                    <button id="copyButton" type="button" onclick="copyGeneratedToken()" class="token-copy-btn" title="Copy token">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect width="14" height="14" x="8" y="8" rx="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>
                        </svg>
                        <span>Copy</span>
                    </button>
                </div>
            </div>

            <!-- Token Meta -->
            <div id="generatedTokenMeta" style="padding: 12px; background: #f0fdf4; border-radius: 8px; border-left: 4px solid #8a1220; color: #7f1d2d; font-size: 0.85rem; line-height: 1.5;"></div>

            <!-- Continue Button with Sweep Animation -->
            <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #e5e7eb;">
                <button type="button" onclick="closeGeneratedTokenModal()" class="token-continue-btn">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                        Continue
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.48);
        backdrop-filter: blur(8px);
        display: none !important;
        align-items: center;
        justify-content: center;
        padding: 20px;
        z-index: 5000;
    }

    .modal-overlay.show {
        display: flex !important;
    }

    html[data-theme="dark"] .modal-content {
        background: rgba(20, 12, 18, 0.94) !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
    }

    html[data-theme="dark"] .modal-content h2 {
        color: #f3d6da !important;
    }

    html[data-theme="dark"] .modal-content p {
        color: #cbd5e1 !important;
    }

    html[data-theme="dark"] input {
        background: rgba(255, 255, 255, 0.05) !important;
        color: #f8fafc !important;
        border-color: rgba(255, 255, 255, 0.08) !important;
    }

    html[data-theme="dark"] input::placeholder {
        color: #94a3b8 !important;
    }

    /* Copy Button Styling */
    .token-copy-btn {
        border: none;
        border-radius: 8px;
        padding: 8px 12px;
        background: #7f1d2d;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        font-weight: 700;
        flex-shrink: 0;
        transition: all 0.2s ease;
        font-size: 0.9rem;
    }

    .token-copy-btn:hover {
        background: #6a1220;
        box-shadow: 0 4px 12px rgba(127, 29, 45, 0.3);
    }

    .token-copy-btn.copied {
        animation: checkmark-pulse 0.6s ease-out;
    }

    @keyframes checkmark-pulse {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.1);
            background: #16a34a;
        }
        100% {
            transform: scale(1);
            background: #16a34a;
        }
    }

    /* Continue Button Styling with Sweep Animation */
    .token-continue-btn {
        width: 100%;
        border: none;
        border-radius: 8px;
        padding: 12px 18px;
        background: linear-gradient(135deg, #8a1220, #6a0e18);
        color: white;
        font-weight: 700;
        cursor: pointer;
        font-size: 0.95rem;
        box-shadow: 0 4px 12px rgba(138, 18, 32, 0.24);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .token-continue-btn:hover {
        background: linear-gradient(135deg, #8a1220, #6a0e18);
        color: #8a1220;
        animation: sweep 0.6s ease-out forwards;
    }

    @keyframes sweep {
        0% {
            background: linear-gradient(135deg, #8a1220, #6a0e18);
            color: white;
        }
        50% {
            background: linear-gradient(90deg, #fbbf24, #fbbf24);
        }
        100% {
            background: #fbbf24;
            color: #8a1220;
        }
    }

    .token-continue-btn:active {
        transform: scale(0.98);
    }
</style>

@endsection
