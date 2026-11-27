@if(optional(auth()->user())->canAccessPermission('settings.medical'))
@php
    $reportTypes = \App\Models\MarClearanceType::withTrashed()->where('is_active', true)
        ->with(['subcategories' => fn ($query) => $query->withTrashed()])->orderBy('sort_order')->orderBy('name')->get();
@endphp
<details class="export-preview-panel mar-clearance-settings">
    <summary>Part II — Clearance Display Names</summary>
    <p class="export-preview-copy">Set the names shown in MAR. Only entries with records in the selected date range will appear. Removed choices remain available for historical reports.</p>
    <form method="POST" action="{{ route('mar-clearance-types.report-names') }}">
        @csrf
        @method('PUT')
        @foreach($reportTypes as $reportTypeItem)
            <label class="mar-report-name-field"><span>{{ $reportTypeItem->name }}{{ $reportTypeItem->trashed() ? ' (removed)' : '' }}</span><input name="types[{{ $reportTypeItem->id }}]" value="{{ $reportTypeItem->report_name }}" placeholder="{{ $reportTypeItem->name }}" maxlength="160"></label>
            @foreach($reportTypeItem->subcategories as $reportChild)
                <label class="mar-report-name-field is-child"><span>{{ $reportChild->name }}{{ $reportChild->trashed() ? ' (removed)' : '' }}</span><input name="subcategories[{{ $reportChild->id }}]" value="{{ $reportChild->report_name }}" placeholder="{{ $reportChild->name }}" maxlength="160"></label>
            @endforeach
        @endforeach
        <button class="export-filter-submit" type="submit">Save Display Names</button>
        <a class="export-filter-cancel" href="{{ route('mar-clearance-types.index') }}">Manage Clearance Types / Mapping</a>
    </form>
</details>
@push('styles')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    .mar-clearance-settings summary { cursor: pointer; font-weight: 800; }
    .mar-report-name-field { display: grid; grid-template-columns: 1fr 1fr; align-items: center; gap: 14px; margin: 12px 0; font-size: 13px; }
    .mar-report-name-field.is-child { padding-left: 18px; }
    .mar-report-name-field input { box-sizing: border-box; width: 100%; min-width: 0; padding: 9px; border: 1px solid #d6bfc3; border-radius: 8px; background: #fff; color: #172033; }
    html[data-theme="dark"] .mar-report-name-field input { background: #111827; color: #f8fafc; border-color: #475569; }
    @media(max-width:640px) { .mar-report-name-field { grid-template-columns: 1fr; gap: 5px; } }
</style>
@endpush
@endif
