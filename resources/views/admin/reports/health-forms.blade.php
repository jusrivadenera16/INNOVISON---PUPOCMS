@extends('layouts.admin')

@section('title', 'Health Forms Report')

@push('styles')
<style>
    .health-forms-shell {
        max-width: 1380px;
        margin: 0 auto;
        padding: 22px;
    }
    .health-forms-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 18px;
        margin-bottom: 24px;
    }
    .health-forms-title {
        margin: 0;
        font-size: 30px;
        font-weight: 900;
        color: #ffffff;
        letter-spacing: -0.03em;
    }
    .health-forms-copy {
        margin: 8px 0 0;
        color: rgba(255,255,255,0.78);
        font-size: 14px;
        line-height: 1.6;
        max-width: 720px;
    }
    .health-forms-back {
        min-width: 132px;
        width: auto !important;
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        position: relative;
        overflow: hidden;
        gap: 7px;
        padding: 10px 16px;
        border: 1px solid rgba(112, 19, 27, 0.3);
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.96);
        color: #70131B;
        font-size: 13px;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
        box-shadow: 0 0 0 2px rgba(112, 19, 27, 0.09), 0 10px 20px rgba(15, 23, 42, 0.08);
        transition: color .08s ease, border-color .18s ease, background .18s ease, box-shadow .18s ease;
    }

    .health-forms-back::after {
        content: "";
        position: absolute;
        top: -40%;
        left: -130%;
        width: 120%;
        height: 180%;
        background: linear-gradient(115deg, rgba(250, 204, 21, 0) 0%, rgba(250, 204, 21, 0.46) 45%, rgba(250, 204, 21, 0) 100%);
        transform: skewX(-20deg);
        transition: left 1.5s ease;
        pointer-events: none;
        z-index: 0;
    }

    .health-forms-back:hover::after {
        left: 125%;
    }

    .health-forms-back:hover,
    .health-forms-back:focus {
        color: #70131B;
        border-color: rgba(112, 19, 27, 0.48);
        background: #ffffff;
        box-shadow: 0 0 0 2px rgba(112, 19, 27, 0.12), 0 12px 28px rgba(15, 23, 42, 0.12);
        outline: none;
    }
    .health-forms-stat-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 24px;
    }
    .health-forms-stat-card {
        background: #ffffff;
        border-radius: 18px;
        padding: 20px 22px;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
        border-top: 5px solid #7f1d2d;
    }
    .health-forms-stat-card span {
        display: block;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #64748b;
    }
    .health-forms-stat-card strong {
        display: block;
        margin-top: 8px;
        font-size: 28px;
        line-height: 1.1;
        font-weight: 900;
        color: #111827;
    }
    .health-forms-layout {
        display: block;
    }
    .health-forms-filter-backdrop {
        position: fixed;
        inset: 0;
        z-index: 1100;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 18px;
        background: rgba(15, 23, 42, 0.52);
        backdrop-filter: blur(7px);
    }
    .health-forms-filter-backdrop.is-open {
        display: flex;
    }
    .health-forms-panel {
        width: min(760px, 96vw);
        background: #ffffff;
        border-radius: 22px;
        padding: 26px 28px;
        box-shadow: 0 24px 70px rgba(15, 23, 42, 0.24), 0 0 0 1px rgba(127, 29, 45, 0.10);
        position: relative;
        animation: healthFormsFilterIn .2s ease;
    }
    @keyframes healthFormsFilterIn {
        from { opacity: 0; transform: translateY(12px) scale(.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    .health-forms-panel h3 {
        margin: 0;
        font-size: 22px;
        font-weight: 900;
        color: #111827;
        letter-spacing: -0.02em;
    }
    .health-forms-panel-copy {
        margin: 8px 0 20px;
        color: #4b5563;
        font-size: 13px;
        line-height: 1.55;
    }
    .health-forms-filter-close {
        position: absolute;
        top: 22px;
        right: 22px;
        width: 40px;
        height: 40px;
        border-radius: 999px;
        border: 1px solid rgba(127, 29, 45, .16);
        background: #fff;
        color: #70131B;
        font-size: 22px;
        line-height: 1;
        cursor: pointer;
        box-shadow: 0 8px 18px rgba(15, 23, 42, .08);
    }
    .health-forms-filter-form {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr) minmax(0, 1fr);
        gap: 16px;
        align-items: end;
    }
    .health-forms-field label {
        display: block;
        margin-bottom: 7px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #475569;
    }
    .health-forms-field input,
    .health-forms-field select {
        width: 100%;
        height: 46px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 0 14px;
        font-size: 14px;
        color: #111827;
        background: #ffffff;
    }
    .premium-select-native {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        opacity: 0 !important;
        pointer-events: none !important;
    }
    .premium-select-shell {
        position: relative;
        width: 100%;
        min-width: 0;
        z-index: 20;
    }
    .premium-select-button {
        width: 100%;
        min-height: 46px;
        border: 1px solid rgba(112, 19, 27, .22);
        border-radius: 14px;
        background: #ffffff;
        color: #111827;
        padding: 0 44px 0 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        font-size: 14px;
        font-weight: 800;
        text-align: left;
        cursor: pointer;
        box-shadow: 0 10px 18px rgba(15, 23, 42, .06);
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }
    .premium-select-button::after {
        content: "";
        position: absolute;
        right: 16px;
        width: 10px;
        height: 10px;
        border-right: 2px solid #8B0000;
        border-bottom: 2px solid #8B0000;
        transform: rotate(45deg);
        transition: transform .18s ease;
    }
    .premium-select-shell.is-open .premium-select-button {
        border-color: #8B0000;
        box-shadow: 0 0 0 4px rgba(139, 0, 0, .08);
    }
    .premium-select-shell.is-open .premium-select-button::after {
        transform: rotate(225deg);
    }
    .premium-select-menu {
        position: absolute;
        z-index: 40;
        top: calc(100% + 8px);
        left: 0;
        right: 0;
        max-height: 230px;
        overflow-y: auto;
        padding: 8px;
        border: 1px solid rgba(112, 19, 27, .16);
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 22px 46px rgba(15, 23, 42, .18);
        display: none;
    }
    .premium-select-shell.is-open .premium-select-menu {
        display: grid;
        gap: 7px;
    }
    .premium-select-option {
        border: 1px solid rgba(148, 163, 184, .20);
        border-radius: 999px;
        background: #f8fafc;
        color: #111827;
        padding: 11px 14px;
        font-size: 13px;
        font-weight: 800;
        text-align: left;
        cursor: pointer;
        transition: background .18s ease, color .18s ease, transform .18s ease, border-color .18s ease;
    }
    .premium-select-option:hover,
    .premium-select-option.is-selected {
        background: #8B0000;
        color: #ffd700;
        border-color: #8B0000;
        transform: translateY(-1px);
    }
    .health-forms-filter-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        grid-column: 1 / -1;
    }
    .health-forms-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 44px;
        border-radius: 12px;
        padding: 0 16px;
        text-decoration: none;
        font-weight: 800;
        cursor: pointer;
        border: none;
    }
    .health-forms-btn.primary {
        background: #7f1d2d;
        color: #ffffff;
        box-shadow: 0 12px 24px rgba(127, 29, 45, .18);
    }
    .health-forms-btn.secondary {
        background: #eef2f7;
        color: #334155;
    }
    .health-forms-filter-trigger {
        background: #ffffff;
        color: #70131B;
        border: 1px solid rgba(112, 19, 27, 0.24);
        border-radius: 14px;
        min-height: 46px;
        padding: 0 24px;
        font-size: 13px;
        font-weight: 900;
        cursor: pointer;
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08);
    }
    .health-forms-table-wrap {
        background: #ffffff;
        border-radius: 20px;
        padding: 12px 12px 4px;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
        overflow-x: auto;
    }
    .health-forms-table {
        width: 100%;
        border-collapse: collapse;
    }
    .health-forms-table th,
    .health-forms-table td {
        padding: 14px 12px;
        border-bottom: 1px solid #e2e8f0;
        font-size: 14px;
        text-align: left;
        color: #111827;
        vertical-align: middle;
    }
    .health-forms-table th {
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
    }
    .health-status-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        border-radius: 999px;
        background: #dcfce7;
        color: #166534;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
    }
    .health-condition-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        border-radius: 999px;
        background: #fef2f2;
        color: #991b1b;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
    }
    .health-condition-badge.none {
        background: #eff6ff;
        color: #1d4ed8;
    }
    .health-condition-badge.pending {
        background: #fff7ed;
        color: #9a3412;
    }
    .health-forms-empty {
        padding: 44px 24px;
        text-align: center;
        color: #64748b;
        font-weight: 700;
    }
    .health-forms-pagination {
        margin-top: 18px;
        padding: 14px 18px;
        border: 1px solid #edf2f7;
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
    }
    .health-forms-pagination .pagination {
        justify-content: center;
        align-items: center;
        gap: 10px;
        margin: 0;
        flex-wrap: wrap;
    }
    .health-forms-pagination .pagination li {
        margin: 0;
    }
    .health-forms-pagination .pagination a,
    .health-forms-pagination .pagination span {
        min-width: 38px;
        height: 38px;
        padding: 0 12px;
        border-radius: 8px !important;
        border: 1px solid #e2e8f0 !important;
        background: #ffffff !important;
        color: #334155 !important;
        font-size: 12px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        box-shadow: none !important;
    }
    .health-forms-pagination .pagination a:hover {
        background: #fff7ed !important;
        border-color: #f8cfd4 !important;
        color: #70131B !important;
        box-shadow: 0 8px 18px rgba(112, 19, 27, 0.12) !important;
    }
    .health-forms-pagination .pagination .active span {
        background: #7f0010 !important;
        border-color: #7f0010 !important;
        color: #ffffff !important;
    }
    .health-forms-pagination .pagination .disabled span {
        opacity: 0.45;
        cursor: not-allowed;
    }
    .health-forms-pagination .pagination svg {
        width: 14px;
        height: 14px;
    }
    @media (max-width: 760px) {
        .health-forms-head {
            flex-direction: column;
        }
        .health-forms-filter-form {
            grid-template-columns: 1fr;
        }
        .health-forms-panel {
            padding: 24px 18px;
        }
    }
</style>
@endpush

@section('content')
@php
    $role = \App\Models\User::normalizeRole(optional(auth()->user())->user_role ?? '');
    $reportsUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports') : url('/admin/reports');
    $applicantsListUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports/health-forms/applicants-list') : url('/admin/reports/health-forms/applicants-list');
@endphp
<div class="health-forms-shell">
    <div class="health-forms-head">
        <div>
            <h1 class="health-forms-title">Health Forms</h1>
            <p class="health-forms-copy">Issued health forms summarized by course for the selected date range.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <button type="button" class="health-forms-filter-trigger" onclick="openHealthFormsFilter()">Filter</button>
            <a href="{{ $applicantsListUrl }}" class="health-forms-back" style="background: #7f1d2d; color: #ffffff; border-color: #7f1d2d;">Applicants List</a>
            <a href="{{ $reportsUrl }}" class="health-forms-back">&larr; Back to Reports</a>
        </div>
    </div>

    <div class="health-forms-stat-grid">
        <div class="health-forms-stat-card">
            <span>Total Issued</span>
            <strong>{{ $totalIssued }}</strong>
        </div>
        <div class="health-forms-stat-card">
            <span>Courses Covered</span>
            <strong>{{ $totalCourses }}</strong>
        </div>
        <div class="health-forms-stat-card">
            <span>With Condition</span>
            <strong>{{ $issuedWithConditions }}</strong>
        </div>
    </div>

    <div class="health-forms-layout">
        <div class="health-forms-filter-backdrop" id="healthFormsFilterModal" onclick="closeHealthFormsFilter(event)">
        <aside class="health-forms-panel">
            <button type="button" class="health-forms-filter-close" onclick="closeHealthFormsFilter()" aria-label="Close filter">&times;</button>
            <h3>Filter Health Forms</h3>
            <p class="health-forms-panel-copy">Narrow the student health form list by course and date range.</p>
            <form class="health-forms-filter-form" method="GET">
                <div class="health-forms-field">
                    <label for="healthFormsSearch">Course</label>
                    <select id="healthFormsSearch" name="q">
                        <option value="">All Courses</option>
                        @foreach($allCourses as $course)
                            <option value="{{ $course }}" {{ $search === $course ? 'selected' : '' }}>
                                {{ $course }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="health-forms-field">
                    <label for="healthFormsDateFrom">From</label>
                    <input id="healthFormsDateFrom" type="text" name="date_from" value="{{ $dateFrom->format('d/m/Y') }}" placeholder="DD/MM/YYYY" inputmode="numeric" pattern="\d{2}/\d{2}/\d{4}" required>
                </div>
                <div class="health-forms-field">
                    <label for="healthFormsDateTo">To</label>
                    <input id="healthFormsDateTo" type="text" name="date_to" value="{{ $dateTo->format('d/m/Y') }}" placeholder="DD/MM/YYYY" inputmode="numeric" pattern="\d{2}/\d{2}/\d{4}" required>
                </div>
                <div class="health-forms-filter-actions">
                    <button type="submit" class="health-forms-btn primary">Apply</button>
                    <a href="{{ request()->url() }}" class="health-forms-btn secondary">Reset</a>
                </div>
            </form>
        </aside>
        </div>

        <div>
            <div class="health-forms-table-wrap">
                <table class="health-forms-table">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Issued Forms</th>
                            <th>With Condition</th>
                            <th>No Condition</th>
                            <th>For Approval</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($issuedForms as $form)
                            <tr>
                                <td>{{ $form->course }}</td>
                                <td><span class="health-status-badge">{{ $form->issued_count }}</span></td>
                                <td><span class="health-condition-badge">{{ $form->with_condition_count }}</span></td>
                                <td><span class="health-condition-badge none">{{ $form->no_condition_count }}</span></td>
                                <td><span class="health-condition-badge pending">{{ $form->for_approval_count }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="health-forms-empty">No issued health forms found for this filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="health-forms-pagination">
                {{ $issuedForms->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
<script>
function openHealthFormsFilter() {
    document.getElementById('healthFormsFilterModal')?.classList.add('is-open');
}
function closeHealthFormsFilter(event) {
    if (event && event.target.id !== 'healthFormsFilterModal') return;
    document.getElementById('healthFormsFilterModal')?.classList.remove('is-open');
}

(function initHealthFormsPremiumSelects() {
    const shells = [];

    function enhance(select) {
        if (!select || select.dataset.premiumSelectReady === '1') return;

        select.dataset.premiumSelectReady = '1';
        select.classList.add('premium-select-native');

        const shell = document.createElement('div');
        shell.className = 'premium-select-shell';

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'premium-select-button';
        button.textContent = select.options[select.selectedIndex]?.textContent?.trim() || 'Select option';

        const menu = document.createElement('div');
        menu.className = 'premium-select-menu';

        Array.from(select.options).forEach((option) => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'premium-select-option';
            item.textContent = option.textContent.trim();
            item.dataset.value = option.value;
            item.classList.toggle('is-selected', option.selected);

            item.addEventListener('click', () => {
                select.value = option.value;
                button.textContent = option.textContent.trim();
                menu.querySelectorAll('.premium-select-option').forEach((node) => {
                    node.classList.toggle('is-selected', node === item);
                });
                shell.classList.remove('is-open');
                select.dispatchEvent(new Event('change', { bubbles: true }));
            });

            menu.appendChild(item);
        });

        select.parentNode.insertBefore(shell, select);
        shell.appendChild(button);
        shell.appendChild(menu);
        shell.appendChild(select);
        shells.push(shell);

        button.addEventListener('click', (event) => {
            event.stopPropagation();
            shells.forEach((otherShell) => {
                if (otherShell !== shell) otherShell.classList.remove('is-open');
            });
            shell.classList.toggle('is-open');
        });
    }

    document.querySelectorAll('#healthFormsSearch').forEach(enhance);
    document.addEventListener('click', () => shells.forEach((shell) => shell.classList.remove('is-open')));
})();
</script>
@endsection
