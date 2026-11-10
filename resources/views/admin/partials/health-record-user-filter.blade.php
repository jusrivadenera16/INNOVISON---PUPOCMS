@if(in_array(strtolower((string) ($userTypeFilter ?? '')), ['', 'all'], true))
    <div class="readonly-user-category-label">
        <select id="{{ $filterListId }}Category" class="readonly-user-category-select" aria-label="Filter by user category" data-readonly-user-category="{{ $filterListId }}">
            <option value="">All Users</option>
            @foreach(['applicant' => 'Applicants', 'transferee' => 'Transferees', 'student' => 'Students', 'faculty' => 'Faculty', 'admin' => 'Admin', 'dependent' => 'Dependents'] as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>
@endif
