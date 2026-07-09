@extends('layouts.admin')

@section('title', 'Integration Tokens Manager')
@section('disable_voice_inputs', 'true')

@section('content')
<style>
    .token-shell {
        display: grid;
        gap: 20px;
    }

    .token-card {
        position: relative;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.96);
        border-radius: 22px;
        padding: 26px;
        box-shadow: 0 22px 50px rgba(15, 23, 42, 0.14);
        border: 1px solid rgba(128, 0, 0, 0.08);
    }

    html[data-theme="dark"] .token-card {
        background: rgba(35, 17, 25, 0.94);
        border-color: rgba(255, 255, 255, 0.08);
        box-shadow: 0 22px 50px rgba(0, 0, 0, 0.28);
    }

    .token-head h2 {
        margin: 0 0 8px;
        font-size: 1.45rem;
        font-weight: 900;
        color: #7f1d2d;
    }

    html[data-theme="dark"] .token-head h2 {
        color: #f3d6da;
    }

    .token-head p {
        margin: 0;
        color: #6b7280;
    }

    html[data-theme="dark"] .token-head p {
        color: #cbd5e1;
    }

    .token-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 18px;
        margin-top: 24px;
    }

    .system-card {
        border-radius: 18px;
        padding: 20px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(250, 244, 246, 0.98));
        border: 1px solid rgba(127, 29, 45, 0.12);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .system-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 32px rgba(127, 29, 45, 0.12);
    }

    html[data-theme="dark"] .system-card {
        background: linear-gradient(180deg, rgba(59, 24, 33, 0.96), rgba(35, 17, 25, 0.98));
        border-color: rgba(255, 255, 255, 0.08);
    }

    .system-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
        padding-bottom: 14px;
        border-bottom: 1px solid rgba(127, 29, 45, 0.1);
    }

    .system-name {
        margin: 0;
        color: #7f1d2d;
        font-size: 16px;
        font-weight: 800;
    }

    html[data-theme="dark"] .system-name {
        color: #f3d6da;
    }

    .system-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 700;
    }

    .status-active {
        background: rgba(34, 197, 94, 0.1);
        color: #166534;
    }

    .status-inactive {
        background: rgba(239, 68, 68, 0.1);
        color: #991b1b;
    }

    .token-section {
        margin-top: 14px;
    }

    .token-label {
        display: block;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b7280;
        margin-bottom: 6px;
    }

    html[data-theme="dark"] .token-label {
        color: #cbd5e1;
    }

    .token-display {
        display: flex;
        gap: 8px;
        align-items: center;
        margin-bottom: 10px;
    }

    .token-box {
        flex: 1;
        border-radius: 12px;
        padding: 10px 12px;
        background: rgba(127, 29, 45, 0.06);
        border: 1px solid rgba(127, 29, 45, 0.1);
        font-family: 'Courier New', monospace;
        font-size: 12px;
        color: #111827;
        word-break: break-all;
        max-height: 60px;
        overflow: hidden;
    }

    html[data-theme="dark"] .token-box {
        background: rgba(255, 255, 255, 0.05);
        color: #f8fafc;
        border-color: rgba(255, 255, 255, 0.08);
    }

    .token-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 10px;
    }

    .token-btn {
        flex: 1;
        min-width: 100px;
        border: 1px solid rgba(127, 29, 45, 0.16);
        border-radius: 12px;
        padding: 8px 12px;
        background: #fff;
        color: #7f1d2d;
        font-weight: 700;
        cursor: pointer;
        font-size: 12px;
        transition: all 0.2s ease;
    }

    .token-btn:hover {
        background: rgba(127, 29, 45, 0.08);
        border-color: rgba(127, 29, 45, 0.24);
    }

    .token-btn.generate {
        background: linear-gradient(135deg, #7f1d2d, #5b0c0e);
        color: #fff;
        border-color: #7f1d2d;
    }

    .token-btn.generate:hover {
        background: linear-gradient(135deg, #5b0c0e, #7f1d2d);
    }

    .token-btn.copy {
        background: #facc15;
        color: #111827;
        border-color: #facc15;
    }

    .token-btn.copy:hover {
        background: #eab308;
        border-color: #eab308;
    }

    .token-btn.revoke {
        background: #fee2e2;
        color: #991b1b;
        border-color: rgba(185, 28, 28, 0.18);
    }

    .token-btn.revoke:hover {
        background: #fecaca;
        border-color: rgba(185, 28, 28, 0.24);
    }

    html[data-theme="dark"] .token-btn {
        background: rgba(255, 255, 255, 0.08);
        color: #f8fafc;
        border-color: rgba(255, 255, 255, 0.12);
    }

    html[data-theme="dark"] .token-btn:hover {
        background: rgba(255, 255, 255, 0.12);
        border-color: rgba(255, 255, 255, 0.16);
    }

    .token-meta {
        font-size: 12px;
        color: #6b7280;
        line-height: 1.5;
    }

    html[data-theme="dark"] .token-meta {
        color: #cbd5e1;
    }

    .token-meta strong {
        color: #7f1d2d;
    }

    html[data-theme="dark"] .token-meta strong {
        color: #f3d6da;
    }

    .no-token-message {
        padding: 12px;
        background: rgba(234, 179, 8, 0.1);
        border: 1px solid rgba(234, 179, 8, 0.2);
        border-radius: 10px;
        font-size: 13px;
        color: #7c2d12;
    }

    html[data-theme="dark"] .no-token-message {
        background: rgba(84, 45, 12, 0.2);
        border-color: rgba(255, 214, 102, 0.2);
        color: #fde68a;
    }

    .token-alert {
        padding: 14px 16px;
        border-radius: 16px;
        background: rgba(34, 197, 94, 0.1);
        border: 1px solid rgba(34, 197, 94, 0.2);
        color: #166534;
        margin-bottom: 20px;
        display: none;
    }

    html[data-theme="dark"] .token-alert {
        background: rgba(34, 197, 94, 0.15);
        border-color: rgba(34, 197, 94, 0.3);
    }

    .token-alert.show {
        display: block;
    }

    .token-alert.error {
        background: rgba(239, 68, 68, 0.1);
        border-color: rgba(239, 68, 68, 0.2);
        color: #991b1b;
    }

    @media (max-width: 900px) {
        .token-grid {
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        }
    }
</style>

<div class="token-shell">
    <section class="token-card">
        <div class="token-head">
            <h2>Integration Tokens Manager</h2>
            <p>Manage API tokens for external system integrations (RIS, IMS, PUPT Website, etc.)</p>
        </div>

        <div id="tokenAlert" class="token-alert"></div>

        <div class="token-grid">
            @forelse($integrationClients as $client)
                <div class="system-card">
                    <div class="system-header">
                        <h3 class="system-name">{{ $client->system_name }}</h3>
                        <span class="system-status {{ $client->is_active ? 'status-active' : 'status-inactive' }}">
                            {{ $client->is_active ? '✓ Active' : '○ Inactive' }}
                        </span>
                    </div>

                    <div class="token-section">
                        <div class="token-label">API Token</div>
                        @if($client->tokens->count() > 0)
                            @php
                                $latestToken = $client->tokens->sortByDesc('created_at')->first();
                            @endphp
                            <div class="token-display">
                                <div class="token-box" id="token-{{ $client->id }}">
                                    {{ substr($latestToken->plainTextToken ?? '•••••••••••••••••••••••••••••', 0, 40) }}...
                                </div>
                                <button type="button" class="token-btn copy" onclick="copyToken('{{ $client->id }}', '{{ $latestToken->plainTextToken ?? '' }}')">
                                    📋 Copy
                                </button>
                            </div>
                            <div class="token-meta">
                                <strong>Created:</strong> {{ $latestToken->created_at->format('M d, Y H:i') }}<br>
                                @if($latestToken->last_used_at)
                                    <strong>Last Used:</strong> {{ $latestToken->last_used_at->format('M d, Y H:i') }}
                                @else
                                    <strong>Last Used:</strong> Never
                                @endif
                            </div>
                        @else
                            <div class="no-token-message">
                                No token generated yet
                            </div>
                        @endif
                    </div>

                    <div class="token-actions">
                        <button type="button" class="token-btn generate" onclick="generateToken('{{ $client->id }}', '{{ $client->system_name }}')">
                            🔄 Generate New
                        </button>
                        @if($client->tokens->count() > 0)
                            <button type="button" class="token-btn revoke" onclick="revokeToken('{{ $client->id }}', '{{ $client->system_name }}')">
                                ❌ Revoke
                            </button>
                        @endif
                    </div>

                    <div class="token-section">
                        <div class="token-label">System Key</div>
                        <div class="token-box" style="cursor: pointer;" onclick="copyText('{{ $client->system_key }}')">
                            {{ $client->system_key }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="system-card">
                    <p style="color: #6b7280; text-align: center;">No integration clients found</p>
                </div>
            @endforelse
        </div>

        <div style="margin-top: 30px; padding: 20px; border-radius: 16px; background: rgba(127, 29, 45, 0.04); border: 1px solid rgba(127, 29, 45, 0.1);">
            <h3 style="margin: 0 0 14px; color: #7f1d2d; font-size: 15px; font-weight: 800;">📖 How to Use These Tokens</h3>
            <div style="color: #6b7280; line-height: 1.6; font-size: 13px;">
                <p><strong style="color: #7f1d2d;">1. Generate Token:</strong> Click "Generate New" for a system to create a new API token</p>
                <p><strong style="color: #7f1d2d;">2. Copy Token:</strong> Use "Copy" to copy the full token to clipboard</p>
                <p><strong style="color: #7f1d2d;">3. Share with External System:</strong> Share the token with the external system (RIS, IMS, etc.)</p>
                <p><strong style="color: #7f1d2d;">4. Use in API Requests:</strong></p>
                <div style="background: #111827; color: #f8fafc; padding: 12px; border-radius: 10px; margin-top: 8px; font-family: 'Courier New', monospace; font-size: 11px; overflow-x: auto;">
curl -H "X-External-Api-Key: YOUR_TOKEN_HERE" \<br>
&nbsp;&nbsp;&nbsp;&nbsp;-H "X-External-System: {{ $integrationClients->first()->system_key ?? 'system-key' }}" \<br>
&nbsp;&nbsp;&nbsp;&nbsp;https://clinic-system.com/api/external/...
                </div>
                <p style="margin-top: 14px;"><strong style="color: #7f1d2d;">5. Revoke Old Token:</strong> Click "Revoke" to invalidate an old token when you generate a new one</p>
            </div>
        </div>
    </section>
</div>

<script>
    function copyToken(clientId, tokenValue) {
        if (!tokenValue) {
            showAlert('No token to copy', true);
            return;
        }
        navigator.clipboard.writeText(tokenValue).then(() => {
            showAlert('Token copied to clipboard!');
        }).catch(err => {
            showAlert('Failed to copy token', true);
        });
    }

    function copyText(text) {
        navigator.clipboard.writeText(text).then(() => {
            showAlert('Copied to clipboard!');
        }).catch(err => {
            showAlert('Failed to copy', true);
        });
    }

    function generateToken(clientId, systemName) {
        if (!confirm(`Generate a new API token for ${systemName}?`)) return;

        fetch('{{ route('admin.integration-tokens.generate') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ client_id: clientId })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showAlert(`Token generated for ${systemName}!`);
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(data.message || 'Failed to generate token', true);
            }
        })
        .catch(err => {
            showAlert('Error generating token', true);
        });
    }

    function revokeToken(clientId, systemName) {
        if (!confirm(`Revoke all tokens for ${systemName}? This cannot be undone.`)) return;

        fetch('{{ route('admin.integration-tokens.revoke') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ client_id: clientId })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showAlert(`Tokens revoked for ${systemName}`);
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(data.message || 'Failed to revoke tokens', true);
            }
        })
        .catch(err => {
            showAlert('Error revoking tokens', true);
        });
    }

    function showAlert(message, isError = false) {
        const alert = document.getElementById('tokenAlert');
        alert.textContent = message;
        alert.classList.add('show');
        if (isError) alert.classList.add('error');
        setTimeout(() => {
            alert.classList.remove('show', 'error');
        }, 3000);
    }
</script>
@endsection
