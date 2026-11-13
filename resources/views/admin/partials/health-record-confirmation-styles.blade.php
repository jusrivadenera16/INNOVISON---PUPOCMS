@once
@push('styles')
<style>
    .record-confirmation-modal { position: fixed; inset: 0; display: none; align-items: center; justify-content: center; width: 100%; height: 100%; max-width: none; max-height: none; margin: 0; padding: 18px; border: 0; box-sizing: border-box; background: rgba(15,23,42,.68); backdrop-filter: blur(5px); overflow: auto; }
    .record-confirmation-modal[open] { display: flex; }
    .record-confirmation-modal::backdrop { background: transparent; }
    .record-confirmation-card { box-sizing: border-box; width: min(430px,100%); border: 1px solid rgba(112,19,27,.22); border-radius: 16px; background: #fff; padding: 26px; text-align: center; box-shadow: 0 24px 70px rgba(15,23,42,.34); }
    .record-confirmation-icon { width: 64px; height: 64px; display: grid; place-items: center; margin: 0 auto 16px; border-radius: 50%; background: #fff7d6; color: #70131b; }
    .record-confirmation-icon svg { width: 30px; height: 30px; }
    .record-confirmation-card h3 { margin: 0; color: #111827; font-size: 19px; font-weight: 900; }
    .record-confirmation-card p { margin: 8px 0 22px; color: #475569; font-size: 14px; line-height: 1.5; }
    .record-confirmation-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .record-confirmation-actions button { min-height: 46px; padding: 8px; border: 1px solid rgba(112,19,27,.24); border-radius: 9px; background: #fff; color: #70131b; font: inherit; font-size: 14px; font-weight: 900; cursor: pointer; transition: background .16s, color .16s, border-color .16s, transform .16s; }
    .record-confirmation-actions button:last-child { border-color: #70131b; background: #70131b; color: #fff; }
    .record-confirmation-actions button:hover:not(:disabled), .record-confirmation-actions button:focus-visible { border-color: #facc15; background: #facc15; color: #70131b; transform: translateY(-1px); }
    .record-confirmation-actions button:disabled { opacity: .6; cursor: wait; }
    html[data-theme="dark"] .record-confirmation-card { border-color: rgba(250,204,21,.24); background: #111827; }
    html[data-theme="dark"] .record-confirmation-card h3 { color: #f8fafc; }
    html[data-theme="dark"] .record-confirmation-card p { color: #cbd5e1; }
    html[data-theme="dark"] .record-confirmation-actions button:first-child { border-color: #475569; background: #1e293b; color: #e2e8f0; }
    html[data-theme="dark"] .record-confirmation-actions button:last-child { border-color: #9f1239; background: #9f1239; color: #fff; }
    html[data-theme="dark"] .record-confirmation-actions button:hover:not(:disabled), html[data-theme="dark"] .record-confirmation-actions button:focus-visible { border-color: #facc15; background: #facc15; color: #70131b; }
    @media (prefers-reduced-motion: reduce) { .record-confirmation-actions button { transition: none; } }
</style>
@endpush
@endonce
