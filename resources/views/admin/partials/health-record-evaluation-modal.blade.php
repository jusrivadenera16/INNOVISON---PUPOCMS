@push('styles')
<style>
    .record-evaluation-modal {
        position: fixed; inset: 0; display: none; align-items: center; justify-content: center;
        width: 100%; height: 100%; max-width: none; max-height: none; margin: 0; padding: 18px;
        border: 0; box-sizing: border-box; background: rgba(15,23,42,.68); backdrop-filter: blur(5px); overflow: auto;
    }
    .record-evaluation-modal[open] { display: flex; }
    .record-evaluation-modal::backdrop { background: transparent; }
    .record-evaluation-card { width: min(430px,100%); border: 1px solid rgba(112,19,27,.22); border-radius: 16px; background: #fff; padding: 26px; text-align: center; box-shadow: 0 24px 70px rgba(15,23,42,.34); }
    .record-evaluation-icon { width: 64px; height: 64px; display: grid; place-items: center; margin: 0 auto 16px; border-radius: 50%; background: #fff7d6; color: #70131b; }
    .record-evaluation-icon svg { width: 30px; height: 30px; }
    .record-evaluation-card h3 { margin: 0; color: #111827; font-size: 19px; font-weight: 900; }
    .record-evaluation-card p { margin: 8px 0 22px; color: #475569; font-size: 14px; line-height: 1.5; }
    .record-evaluation-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .record-evaluation-actions button { min-height: 46px; border: 1px solid rgba(112,19,27,.24); border-radius: 9px; background: #fff; color: #70131b; font: inherit; font-size: 14px; font-weight: 900; cursor: pointer; transition: background .16s, color .16s, border-color .16s, transform .16s; }
    .record-evaluation-actions button:last-child { border-color: #70131b; background: #70131b; color: #fff; }
    .record-evaluation-actions button:hover:not(:disabled), .record-evaluation-actions button:focus-visible { border-color: #facc15; background: #facc15; color: #70131b; transform: translateY(-1px); }
    .record-evaluation-actions button:disabled { opacity: .6; cursor: wait; }
    .record-evaluation-card [data-record-evaluation-status] { color: #b91c1c; }
    .record-evaluation-card [hidden] { display: none !important; }
    html[data-theme="dark"] .record-evaluation-card { border-color: rgba(250,204,21,.24); background: #111827; }
    html[data-theme="dark"] .record-evaluation-card h3 { color: #f8fafc; }
    html[data-theme="dark"] .record-evaluation-card p { color: #cbd5e1; }
    html[data-theme="dark"] .record-evaluation-actions button:first-child { border-color: #475569; background: #1e293b; color: #e2e8f0; }
    html[data-theme="dark"] .record-evaluation-actions button:last-child { border-color: #9f1239; background: #9f1239; color: #fff; }
    html[data-theme="dark"] .record-evaluation-actions button:hover:not(:disabled), html[data-theme="dark"] .record-evaluation-actions button:focus-visible { border-color: #facc15; background: #facc15; color: #70131b; }
    html[data-theme="dark"] .record-evaluation-card [data-record-evaluation-status] { color: #fda4af; }
    @media (prefers-reduced-motion: reduce) { .record-evaluation-actions button { transition: none; } }
</style>
@endpush
<dialog class="record-evaluation-modal" id="recordEvaluationModal" data-record-evaluation-url="{{ $recordEvaluationUrl }}" aria-labelledby="recordEvaluationTitle" aria-describedby="recordEvaluationMessage">
    <div class="record-evaluation-card">
        <span class="record-evaluation-icon" aria-hidden="true"><x-outline-icon name="envelope" /></span>
        <h3 id="recordEvaluationTitle">Send Evaluation?</h3>
        <p id="recordEvaluationMessage">Send the service evaluation link to <strong>{{ $recordEvaluationName }}</strong>? An unanswered invitation can be sent again using the same link.</p>
        <p data-record-evaluation-status role="status" aria-live="polite" hidden></p>
        <div class="record-evaluation-actions">
            <button type="button" data-record-evaluation-cancel>Cancel</button>
            <button type="button" data-record-evaluation-send>Send</button>
        </div>
    </div>
</dialog>
@push('scripts')
    <script src="{{ asset('js/health-record-evaluation.js') }}" defer></script>
@endpush
