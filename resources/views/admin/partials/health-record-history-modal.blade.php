@push('late-styles')
<style>
    .health-record-history-overlay[hidden], .health-record-history-item[hidden] { display: none !important; }
    .health-record-history-overlay {
        position: fixed; inset: 0; z-index: 2147483000; display: grid; place-items: center;
        padding: 18px; background: rgba(15, 23, 42, .64); backdrop-filter: blur(5px);
    }
    .health-record-history-dialog {
        width: min(890px, 100%); max-height: min(94dvh, 900px); display: flex; flex-direction: column;
        overflow: hidden; border: 1px solid #e7c9d1; border-radius: 16px; background: #fff;
        color: #172033; box-shadow: 0 24px 70px rgba(15, 23, 42, .3);
    }
    .health-record-history-head {
        display: flex; align-items: center; justify-content: space-between; gap: 18px;
        padding: 16px 22px; color: #fff; background: linear-gradient(120deg, #70131b, #971a32);
    }
    .health-record-history-heading { display: flex; align-items: center; gap: 14px; min-width: 0; }
    .health-record-history-heading-icon {
        display: grid; flex: 0 0 auto; place-items: center; width: 48px; height: 48px;
        border-radius: 50%; border: 1px solid rgba(255,255,255,.35); background: rgba(255,255,255,.12);
    }
    .health-record-history-heading-icon svg { width: 27px; height: 27px; }
    #healthRecordHistoryModal .health-record-history-head h2 { margin: 0; color: #fff !important; font-size: 21px; line-height: 1.2; }
    #healthRecordHistoryModal .health-record-history-head p { margin: 3px 0 0; color: rgba(255,255,255,.9) !important; font-size: 12px; line-height: 1.4; }
    .health-record-history-close {
        position: relative; overflow: hidden; display: grid; flex: 0 0 auto; place-items: center; width: 38px; height: 38px;
        border: 1px solid rgba(255,255,255,.24); border-radius: 999px; background: rgba(112,19,27,.45); color: #fff; cursor: pointer;
        transition: transform .18s ease, background .18s ease, color .18s ease, border-color .18s ease;
    }
    .health-record-history-close::after {
        content: ""; position: absolute; top: -35%; left: -72%; width: 48%; height: 170%;
        background: linear-gradient(120deg, transparent 0%, rgba(255,244,180,.18) 34%, rgba(255,244,180,.58) 50%, rgba(255,244,180,.18) 66%, transparent 100%);
        transform: skewX(-18deg); transition: left .48s ease; pointer-events: none;
    }
    .health-record-history-close:hover, .health-record-history-close:focus-visible {
        background: #facc15; border-color: #facc15; color: #70131b; transform: translateY(-1px);
        box-shadow: 0 10px 22px rgba(250,204,21,.22); outline: none;
    }
    .health-record-history-close:hover::after, .health-record-history-close:focus-visible::after { left: 128%; }
    .health-record-history-close svg { position: relative; z-index: 1; width: 18px; height: 18px; color: currentColor; stroke: currentColor; }
    .health-record-history-body { min-height: 0; overflow-y: auto; padding: 16px 20px 22px; }
    .health-record-history-controls { display: flex; flex-wrap: wrap; align-items: center; gap: 9px; margin: 13px 0; }
    .health-record-history-controls label { color: #334155; font-size: 12px; font-weight: 800; }
    .health-record-history-controls select {
        min-height: 36px; min-width: 210px; padding: 6px 10px; border: 1px solid #cbd5e1;
        border-radius: 8px; background: #fff; color: #172033; font: inherit; font-size: 12px;
    }
    .health-record-history-controls select:focus-visible { outline: 2px solid #facc15; }
    .health-record-history-controls select option { background: #fff; color: #172033; }
    .health-record-history-count {
        margin-left: auto; padding: 6px 13px; border-radius: 999px;
        background: #ede9f1; color: #273044; font-size: 11px; font-weight: 800;
    }
    .health-record-history-list { display: grid; gap: 7px; }
    .health-record-history-item { border: 1px solid #ddd4e2; border-radius: 10px; background: #fff; overflow: hidden; }
    .health-record-history-item[open] { border-color: #e8bdcb; background: #fff8fb; }
    .health-record-history-item > summary {
        display: flex; align-items: center; gap: 13px; padding: 10px 15px;
        list-style: none; cursor: pointer;
    }
    .health-record-history-item > summary::-webkit-details-marker { display: none; }
    .health-record-history-item > summary:hover, .health-record-history-item > summary:focus-visible { background: #fff1f5; outline: none; }
    .health-record-history-event-icon {
        display: grid; flex: 0 0 auto; place-items: center; width: 38px; height: 38px;
        border-radius: 9px; background: #fae9ef; color: #86142a;
    }
    .health-record-history-event-icon svg { width: 21px; height: 21px; }
    .health-record-history-event-copy { min-width: 0; flex: 1; }
    .health-record-history-event-copy strong { display: block; color: #81132b; font-size: 14px; line-height: 1.3; }
    .health-record-history-event-copy small { display: block; margin-top: 2px; color: #475569; font-size: 11px; }
    .health-record-history-status { padding: 6px 11px; border-radius: 999px; background: #e7edfb; color: #24437e; font-size: 10px; font-weight: 850; white-space: nowrap; }
    .health-record-history-item[data-history-status="pending-resubmission"] .health-record-history-status,
    .health-record-history-item[data-history-status="needs-correction"] .health-record-history-status { background: #fff0c2; color: #8d4300; }
    .health-record-history-item[data-history-status="approved"] .health-record-history-status,
    .health-record-history-item[data-history-status="restored"] .health-record-history-status { background: #dff5e4; color: #12632b; }
    .health-record-history-item[data-history-status="revoked"] .health-record-history-status,
    .health-record-history-item[data-history-status="pulled-out"] .health-record-history-status,
    .health-record-history-item[data-history-status="cancelled"] .health-record-history-status { background: #fbe0e5; color: #8f1422; }
    .health-record-history-item[data-history-category="Medical Information"] .health-record-history-status { background: #edf0fc; color: #433489; }
    .health-record-history-chevron { flex: 0 0 auto; width: 17px; height: 17px; color: #86142a; transition: transform .2s; }
    .health-record-history-item[open] .health-record-history-chevron { transform: rotate(180deg); }
    .health-record-history-detail {
        display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0;
        margin: 0 12px 12px; padding: 12px; border: 1px solid #e8e1e7;
        border-radius: 9px; background: #fff;
    }
    .health-record-history-detail > div { min-width: 0; padding: 4px 12px; border-right: 1px solid #e2d9e2; }
    .health-record-history-detail > div:last-child { border-right: 0; }
    .health-record-history-detail span { display: block; margin-bottom: 4px; color: #64748b; font-size: 10px; font-weight: 800; }
    .health-record-history-detail strong { display: block; overflow-wrap: anywhere; white-space: pre-wrap; font-size: 12px; font-weight: 700; }
    .health-record-history-changes { display: grid; gap: 6px; margin: 0 12px 12px; }
    .health-record-history-change {
        display: grid; grid-template-columns: 32px minmax(0, 1fr) auto; align-items: center; gap: 9px;
        padding: 8px 10px; border: 1px solid #dbe1eb; border-radius: 8px; background: #f7f9fc;
    }
    .health-record-history-change-initial { display: grid; place-items: center; width: 30px; height: 30px; border-radius: 6px; background: #e7eef8; color: #16233d; font-weight: 800; }
    .health-record-history-change strong { display: block; font-size: 12px; }
    .health-record-history-change small { display: block; margin-top: 4px; color: #334155; font-size: 11px; overflow-wrap: anywhere; }
    .health-record-history-change b { font-weight: 850; }
    .health-record-history-change em { padding: 3px 6px; border-radius: 4px; background: #e9eff6; font-style: normal; }
    .health-record-history-change em.is-new { border: 1px solid #f0b7c0; background: #fff0f2; color: #8b1427; }
    .health-record-history-change time { color: #475569; font-size: 10px; white-space: nowrap; }
    .health-record-history-empty { padding: 20px; border: 1px dashed #cbd5e1; border-radius: 9px; text-align: center; color: #64748b; font-size: 13px; }
    html[data-theme="dark"] .health-record-history-dialog { border-color: #4d3441; background: #101a2d; color: #f8fafc; }
    html[data-theme="dark"] .health-record-history-item,
    html[data-theme="dark"] .health-record-history-detail,
    html[data-theme="dark"] .health-record-history-change { border-color: #34445d; background: #172338; color: #f8fafc; }
    html[data-theme="dark"] .health-record-history-item[open] { background: #1f2940; }
    html[data-theme="dark"] .health-record-history-item > summary:hover,
    html[data-theme="dark"] .health-record-history-item > summary:focus-visible { background: #24314b; }
    html[data-theme="dark"] .health-record-history-event-copy small,
    html[data-theme="dark"] .health-record-history-detail span,
    html[data-theme="dark"] .health-record-history-change small,
    html[data-theme="dark"] .health-record-history-change time { color: #c5d0e2; }
    html[data-theme="dark"] .health-record-history-event-copy strong { color: #fff; }
    html[data-theme="dark"] .health-record-history-controls label { color: #e2e8f0; }
    html[data-theme="dark"] .health-record-history-controls select { border-color: #46546b; background: #172338; color: #f8fafc; color-scheme: dark; }
    html[data-theme="dark"] .health-record-history-controls select option { background: #172338; color: #f8fafc; }
    html[data-theme="dark"] .health-record-history-change em { background: #26354b; color: #fff; }
    html[data-theme="dark"] .health-record-history-change em.is-new { background: #4b2532; color: #fff; }
    @media (max-width: 650px) {
        .health-record-history-overlay { padding: 8px; }
        .health-record-history-dialog { max-height: calc(100dvh - 16px); }
        .health-record-history-head { padding: 12px; }
        .health-record-history-head h2 { font-size: 17px; }
        .health-record-history-heading-icon { width: 38px; height: 38px; }
        .health-record-history-heading-icon svg { width: 22px; height: 22px; }
        .health-record-history-body { padding: 12px; }
        .health-record-history-detail { grid-template-columns: 1fr; }
        .health-record-history-detail > div { border-right: 0; border-bottom: 1px solid #e2d9e2; padding: 7px 4px; }
        .health-record-history-detail > div:last-child { border-bottom: 0; }
        .health-record-history-change { grid-template-columns: 30px minmax(0, 1fr); }
        .health-record-history-change time { grid-column: 2; }
        .health-record-history-status { display: none; }
    }
</style>
@endpush

@php
    $historyEvents = collect($healthRecordHistory ?? []);
@endphp
<div class="health-record-history-overlay" id="healthRecordHistoryModal" hidden aria-hidden="true">
    <section class="health-record-history-dialog" role="dialog" aria-modal="true" aria-labelledby="healthRecordHistoryTitle" aria-describedby="healthRecordHistoryDescription" tabindex="-1">
        <header class="health-record-history-head">
            <div class="health-record-history-heading">
                <span class="health-record-history-heading-icon" aria-hidden="true"><x-outline-icon name="clock" /></span>
                <div>
                    <h2 id="healthRecordHistoryTitle">Record Activity History</h2>
                    <p id="healthRecordHistoryDescription">View requests, status changes, and administrative actions for this health record.</p>
                </div>
            </div>
            <button type="button" class="health-record-history-close" data-health-record-history-close aria-label="Close history"><x-outline-icon name="x-mark" /></button>
        </header>
        <div class="health-record-history-body">
            <div class="health-record-history-controls">
                <label for="healthRecordHistoryCategory">Filter by Category:</label>
                <select id="healthRecordHistoryCategory" data-health-record-history-filter>
                    <option value="all">All Activities</option>
                    <option value="Requests">Requests</option>
                    <option value="Forms">Health Forms</option>
                    <option value="Medical Information">Medical Information</option>
                    <option value="Status Changes">Status Changes</option>
                </select>
                <span class="health-record-history-count" data-health-record-history-count>{{ $historyEvents->count() }} {{ $historyEvents->count() === 1 ? 'Activity' : 'Activities' }}</span>
            </div>
            <div class="health-record-history-list">
                @forelse($historyEvents as $historyEvent)
                    @php
                        $historyIcon = match ($historyEvent['category']) {
                            'Medical Information' => 'heart-pulse',
                            'Status Changes' => 'arrow-path',
                            'Forms' => 'document-check',
                            default => 'document-text',
                        };
                    @endphp
                    <details class="health-record-history-item" data-history-category="{{ $historyEvent['category'] }}" data-history-status="{{ \Illuminate\Support\Str::slug($historyEvent['status']) }}" @if($loop->first) open @endif>
                        <summary>
                            <span class="health-record-history-event-icon" aria-hidden="true"><x-outline-icon name="{{ $historyIcon }}" /></span>
                            <span class="health-record-history-event-copy">
                                <strong>{{ $historyEvent['title'] }}</strong>
                                <small>{{ $historyEvent['at']->format('M d, Y') }} &nbsp;·&nbsp; {{ $historyEvent['at']->format('h:i A') }}</small>
                            </span>
                            <span class="health-record-history-status">{{ $historyEvent['status'] }}</span>
                            <x-outline-icon name="chevron-down" class="health-record-history-chevron" />
                        </summary>
                        <div class="health-record-history-detail">
                            <div><span>Recorded By</span><strong>{{ $historyEvent['actor'] }}</strong></div>
                            <div><span>Activity</span><strong>{{ $historyEvent['category'] }}</strong></div>
                            <div><span>Date &amp; Time</span><strong>{{ $historyEvent['at']->format('M d, Y - h:i A') }}</strong></div>
                            @foreach($historyEvent['details'] as $detail)
                                <div><span>{{ $detail['label'] }}</span><strong>{{ $detail['value'] }}</strong></div>
                            @endforeach
                        </div>
                        @if(!empty($historyEvent['changes']))
                            <div class="health-record-history-changes">
                                @foreach($historyEvent['changes'] as $change)
                                    <div class="health-record-history-change">
                                        <span class="health-record-history-change-initial" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($change['label'], 0, 1)) }}</span>
                                        <div>
                                            <strong>{{ $change['label'] }} Updated</strong>
                                            <small><b>Previous value</b> <em>{{ $change['before'] }}</em> &nbsp;→&nbsp; <b>Updated value</b> <em class="is-new">{{ $change['after'] }}</em></small>
                                        </div>
                                        <time>{{ $historyEvent['at']->format('M d, Y - h:i A') }}</time>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </details>
                @empty
                    <div class="health-record-history-empty">No recorded activity yet.</div>
                @endforelse
                <div class="health-record-history-empty" data-health-record-history-filter-empty hidden>No activities in this category.</div>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const overlay = document.getElementById('healthRecordHistoryModal');
        if (!overlay) return;
        const dialog = overlay.querySelector('.health-record-history-dialog');
        const closeButton = overlay.querySelector('[data-health-record-history-close]');
        const filter = overlay.querySelector('[data-health-record-history-filter]');
        const count = overlay.querySelector('[data-health-record-history-count]');
        const empty = overlay.querySelector('[data-health-record-history-filter-empty]');
        const items = Array.from(overlay.querySelectorAll('.health-record-history-item'));
        let opener = null;
        let previousOverflow = '';

        function setOpen(open) {
            overlay.hidden = !open;
            overlay.setAttribute('aria-hidden', open ? 'false' : 'true');
            if (open) {
                previousOverflow = document.body.style.overflow;
                document.body.style.overflow = 'hidden';
                closeButton.focus();
            } else {
                document.body.style.overflow = previousOverflow;
                opener?.focus();
            }
        }

        document.querySelectorAll('[data-health-record-history-open]').forEach(function (button) {
            button.addEventListener('click', function () {
                opener = button;
                setOpen(true);
            });
        });
        closeButton.addEventListener('click', function () { setOpen(false); });
        overlay.addEventListener('click', function (event) {
            if (event.target === overlay) setOpen(false);
        });
        overlay.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') { setOpen(false); return; }
            if (event.key !== 'Tab') return;
            const focusable = Array.from(dialog.querySelectorAll('button, select, summary, a[href]'))
                .filter(function (element) { return !element.hidden && element.getClientRects().length; });
            if (!focusable.length) return;
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        });
        filter.addEventListener('change', function () {
            let visible = 0;
            items.forEach(function (item) {
                item.hidden = filter.value !== 'all' && item.dataset.historyCategory !== filter.value;
                if (!item.hidden) visible += 1;
            });
            count.textContent = visible + (visible === 1 ? ' Activity' : ' Activities');
            empty.hidden = visible !== 0 || items.length === 0;
        });
    });
</script>
@endpush
