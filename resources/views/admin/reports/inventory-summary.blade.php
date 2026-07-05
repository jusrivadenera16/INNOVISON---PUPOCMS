@extends('layouts.admin')

@section('title', 'Inventory Summary')

@push('styles')
<style>
    .summary-container {
        padding: 10px 4px;
    }

    .summary-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 18px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .summary-title {
        margin: 0;
        color: #4b0f17;
        font-size: 26px;
        font-weight: 800;
        letter-spacing: -0.01em;
    }

    .summary-subtitle {
        margin: 6px 0 0;
        color: #6b7280;
        font-size: 14px;
        line-height: 1.5;
    }

    .summary-filter {
        display: flex;
        align-items: end;
        gap: 10px;
        flex-wrap: wrap;
    }

    .summary-filter label {
        display: block;
        margin-bottom: 6px;
        color: #6b7280;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .summary-filter input[type="month"] {
        min-width: 180px;
        padding: 10px 12px;
        border-radius: 10px;
        border: 1px solid #d8c7cd;
        color: #111827;
        background: #fff;
    }

    .summary-filter button {
        padding: 10px 16px;
        border: none;
        border-radius: 10px;
        background: #70131B;
        color: #fff;
        font-weight: 700;
        cursor: pointer;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .summary-card {
        background: #fff;
        border-radius: 14px;
        border: 1px solid #eadde1;
        border-left: 4px solid #70131B;
        padding: 18px 18px 16px;
        box-shadow: 0 8px 24px rgba(112, 19, 27, 0.06);
    }

    .summary-label {
        font-size: 11px;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .summary-value {
        margin: 6px 0 0;
        font-size: 30px;
        line-height: 1;
        color: #4b0f17;
        font-weight: 800;
    }

    .summary-meta {
        margin-top: 8px;
        color: #64748b;
        font-size: 12px;
    }

    .summary-panels {
        display: grid;
        grid-template-columns: 1.15fr 0.85fr;
        gap: 18px;
        margin-bottom: 18px;
    }

    .summary-panel,
    .summary-table-card {
        background: #fff;
        border-radius: 14px;
        border: 1px solid #eadde1;
        padding: 18px;
        box-shadow: 0 8px 24px rgba(112, 19, 27, 0.06);
    }

    .summary-table-card {
        margin-bottom: 18px;
    }

    .summary-table-title {
        margin: 0 0 10px;
        font-size: 17px;
        color: #4b0f17;
        font-weight: 800;
    }

    .summary-table-subtitle {
        margin: -2px 0 12px;
        color: #6b7280;
        font-size: 13px;
    }

    .summary-table {
        width: 100%;
        border-collapse: collapse;
    }

    .summary-table thead th {
        padding: 11px 12px;
        text-align: left;
        border-bottom: 1px solid #eddde2;
        color: #6b7280;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .summary-table td {
        padding: 12px;
        border-bottom: 1px solid #f4ebee;
        color: #334155;
        font-size: 14px;
        vertical-align: top;
    }

    .summary-table tbody tr:last-child td {
        border-bottom: none;
    }

    .summary-chip {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
    }

    .summary-chip.low {
        background: #fff7ed;
        color: #c2410c;
    }

    .summary-chip.out {
        background: #fee2e2;
        color: #b91c1c;
    }

    .summary-chip.ok {
        background: #dcfce7;
        color: #15803d;
    }

    .summary-empty {
        padding: 18px;
        border: 1px dashed #d8c7cd;
        border-radius: 12px;
        text-align: center;
        color: #6b7280;
        font-size: 14px;
    }

    .summary-back {
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

    .summary-back::after {
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

    .summary-back:hover::after {
        left: 125%;
    }

    .summary-back:hover,
    .summary-back:focus {
        color: #70131B;
        border-color: rgba(112, 19, 27, 0.48);
        background: #ffffff;
        box-shadow: 0 0 0 2px rgba(112, 19, 27, 0.12), 0 12px 28px rgba(15, 23, 42, 0.12);
        outline: none;
    }
    .summary-top-actions {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 14px;
    }
    .mar-manage-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        position: relative;
        overflow: hidden;
        white-space: nowrap;
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #ffffff;
        padding: 11px 18px;
        border-radius: 999px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 800;
        border: 1px solid #8f2230;
        box-shadow: 0 0 0 3px rgba(112, 19, 27, 0.12), 0 10px 22px rgba(112, 19, 27, 0.20);
        transition: color .08s linear, transform .18s ease, box-shadow .18s ease, background .18s ease;
        z-index: 0;
    }
    .mar-manage-btn::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(120deg, rgba(255, 248, 196, 0) 0%, rgba(255, 239, 181, 0.14) 22%, rgba(255, 239, 181, 0.52) 48%, rgba(255, 239, 181, 0.14) 72%, rgba(255, 248, 196, 0) 100%);
        transform: translateX(-135%);
        transition: transform 1.5s ease;
        z-index: -1;
    }
    .mar-manage-btn::before {
        content: "MT";
        width: 28px;
        height: 28px;
        border-radius: 999px;
        background: #ffefb5;
        color: #70131B;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: 0.04em;
        flex: 0 0 auto;
        position: relative;
        z-index: 1;
    }
    .mar-manage-btn-label {
        position: relative;
        z-index: 1;
        color: #ffffff;
    }
    .mar-manage-btn:hover {
        transform: translateY(-1px);
        border-color: #facc15;
        box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.18), 0 14px 24px rgba(112, 19, 27, 0.16);
        color: #ffffff;
    }
    .mar-manage-btn:hover::after {
        transform: translateX(135%);
    }

    @media (max-width: 1100px) {
        .summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .summary-panels {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 700px) {
        .summary-grid {
            grid-template-columns: 1fr;
        }
    }

    /* DARK MODE FIXES - Inventory Summary Cards */
    html[data-theme="dark"] .inventory-frame,
    html[data-theme="dark"] .summary-card,
    html[data-theme="dark"] .total-card {
        background: rgba(35, 17, 25, 0.96) !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .inventory-frame::before {
        background: #facc15 !important;
    }

    html[data-theme="dark"] .inventory-frame-title,
    html[data-theme="dark"] .summary-card h3,
    html[data-theme="dark"] .total-card h3 {
        color: #f3d6da !important;
    }

    html[data-theme="dark"] .inventory-frame-value,
    html[data-theme="dark"] .summary-card .value,
    html[data-theme="dark"] .total-card .value {
        color: #f8fafc !important;
        font-weight: bold !important;
    }

    html[data-theme="dark"] .inventory-frame-label,
    html[data-theme="dark"] .summary-card .label,
    html[data-theme="dark"] .total-card .label {
        color: rgba(248, 250, 252, 0.7) !important;
    }

    html[data-theme="dark"] .inventory-frame-change,
    html[data-theme="dark"] .summary-card .change {
        color: rgba(248, 250, 252, 0.6) !important;
    }

    html[data-theme="dark"] .summary-grid {
        gap: 16px !important;
    }
</style>
@endpush

@section('content')
@php
    $role = \App\Models\User::normalizeRole(optional(auth()->user())->user_role ?? '');
    $reportsHomeUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports') : url('/admin/reports');
    $summaryUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports/inventory-summary') : url('/admin/reports/inventory-summary');
    $reportMonthLabel = \Carbon\Carbon::parse($monthFilter . '-01')->format('F Y');
@endphp
<div class="summary-container">
    <div class="summary-header">
        <div>
            <h2 class="summary-title">Inventory Summary Report</h2>
            <p class="summary-subtitle">Monitor current stock, monthly consumption, and starting balances for {{ $reportMonthLabel }}.</p>
        </div>

        <form action="{{ $summaryUrl }}" method="GET" class="summary-filter">
            <div>
                <label for="summaryMonth">Report Month</label>
                <input id="summaryMonth" type="month" name="month" value="{{ $monthFilter }}">
            </div>
            <button type="submit">Apply Filter</button>
        </form>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <span class="summary-label">Total Unique Items</span>
            <h3 class="summary-value">{{ $totalItems }}</h3>
            <div class="summary-meta">Tracked inventory entries in the clinic list.</div>
        </div>
        <div class="summary-card">
            <span class="summary-label">Current Stock Quantity</span>
            <h3 class="summary-value">{{ $totalStock }}</h3>
            <div class="summary-meta">Combined remaining quantity across all items.</div>
        </div>
        <div class="summary-card">
            <span class="summary-label">Consumed This Month</span>
            <h3 class="summary-value">{{ $totalConsumed }}</h3>
            <div class="summary-meta">Based on inventory movement records for the selected month.</div>
        </div>
        <div class="summary-card">
            <span class="summary-label">Stock Alerts</span>
            <h3 class="summary-value">{{ $outOfStock + $lowStockCount }}</h3>
            <div class="summary-meta">{{ $outOfStock }} out of stock, {{ $lowStockCount }} running low.</div>
        </div>
    </div>

    <div class="summary-panels">
        <div class="summary-panel">
            <h4 class="summary-table-title">Inventory Performance by Item</h4>
            <p class="summary-table-subtitle">Starting stock is derived from current balance plus movement-recorded consumption for the selected month.</p>
            <table class="summary-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Unit</th>
                        <th>Starting</th>
                        <th>Consumed</th>
                        <th>Current</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($itemPerformance as $item)
                        <tr>
                            <td>
                                <div style="font-weight: 700;">{{ $item->name }}</div>
                                <div style="font-size: 12px; color: #6b7280;">{{ $item->report_category }}</div>
                            </td>
                            <td>
                                {{ $item->unit }}
                                @if($item->hasDispensingConversion())
                                    <div style="font-size: 12px; color: #6b7280;">{{ $item->dispensing_unit }} ({{ $item->units_per_stock_unit }} per {{ $item->unit }})</div>
                                @endif
                            </td>
                            <td>{{ rtrim(rtrim(number_format((float) $item->starting_stock, 2, '.', ''), '0'), '.') }}</td>
                            <td>
                                {{ rtrim(rtrim(number_format((float) $item->consumed, 2, '.', ''), '0'), '.') }}
                                @if($item->hasDispensingConversion())
                                    <div style="font-size: 12px; color: #6b7280;">{{ rtrim(rtrim(number_format((float) $item->consumed_display, 2, '.', ''), '0'), '.') }} {{ $item->dispensing_unit }} dispensed</div>
                                @endif
                            </td>
                            <td style="font-weight: 800;">{{ rtrim(rtrim(number_format((float) $item->current_balance, 2, '.', ''), '0'), '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="summary-empty">No inventory records available.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="summary-panel">
            <h4 class="summary-table-title">Low Stock Watchlist</h4>
            <p class="summary-table-subtitle">Items that need attention before they run out.</p>

            @if($lowStockItems->isEmpty())
                <div class="summary-empty">No low-stock items for this report period.</div>
            @else
                <table class="summary-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Balance</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lowStockItems as $item)
                            <tr>
                                <td style="font-weight: 700;">{{ $item->name }}</td>
                                <td>{{ rtrim(rtrim(number_format((float) $item->current_balance, 2, '.', ''), '0'), '.') }} {{ $item->unit }}</td>
                                <td><span class="summary-chip low">Low Stock</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div class="summary-table-card">
        <h4 class="summary-table-title">Stock by Category</h4>
        <p class="summary-table-subtitle">Category-level view of starting stock, monthly usage, and current balance.</p>
        <table class="summary-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Item Count</th>
                    <th>Starting Stock</th>
                    <th>Consumed</th>
                    <th>Current Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categorySummary as $cat)
                <tr>
                    <td style="font-weight: 700;">{{ $cat->category }}</td>
                    <td>{{ $cat->count }}</td>
                    <td>{{ $cat->starting_qty }}</td>
                    <td>{{ rtrim(rtrim(number_format((float) $cat->consumed_qty, 2, '.', ''), '0'), '.') }}</td>
                    <td>{{ $cat->total_qty }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="summary-empty">No category summary available.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <a href="{{ $reportsHomeUrl }}" class="summary-back">&larr; Back to Reports</a>
</div>
@endsection
