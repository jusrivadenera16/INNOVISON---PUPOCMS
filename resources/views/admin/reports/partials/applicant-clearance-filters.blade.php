<fieldset class="clearance-source-fields clearance-applicant-filter-fields" data-applicant-filter-fields hidden>
    <legend>Applicant filters</legend>
    <p class="clearance-data-source-question">Choose one option in any filter you need. All selected filters must match the applicant record.</p>
    @foreach($applicantFilterOptions as $filterKey => $filter)
        <div class="clearance-applicant-filter-group">
            <span class="clearance-applicant-filter-label">{{ $filter['label'] }}</span>
            <div class="clearance-applicant-filter-options">
                <label class="clearance-source-option">
                    <input
                        type="radio"
                        name="applicant_filters[{{ $filterKey }}]"
                        value=""
                        data-applicant-filter
                        data-filter-key="{{ $filterKey }}"
                        checked
                    >
                    <span>Any</span>
                </label>
                @foreach($filter['values'] as $value => $label)
                    <label class="clearance-source-option">
                        <input
                            type="radio"
                            name="applicant_filters[{{ $filterKey }}]"
                            value="{{ $value }}"
                            data-applicant-filter
                            data-filter-key="{{ $filterKey }}"
                        >
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    @endforeach
    <small class="clearance-data-source-help">The report always includes approved or issued records only. Leave a filter on Any when it should not limit the count.</small>
</fieldset>
