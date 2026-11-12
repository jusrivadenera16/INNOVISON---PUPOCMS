@once
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/issued-clearance-document-cards.css') }}">
    @endpush
@endonce
@foreach($issuedClearances as $issuedClearance)
    @php($issuedClearanceUrl = route('admin.medical_clearances.show', [$issuedClearance->record_type, $issuedClearance->profile_id, $issuedClearance]))
    <div class="doc-file issued-clearance-document-card">
        <h4>Medical Certificate (Issued)</h4>
        <div class="doc-actions">
            <a class="doc-link" href="{{ $issuedClearanceUrl }}" target="_blank" rel="noopener"><x-outline-icon name="document-text" /> Open</a>
        </div>
        @include('admin.partials.private-document-thumbnail', ['documentLabel' => 'Medical Certificate', 'documentSubtitle' => $issuedClearance->purpose . ' · Issued ' . $issuedClearance->issued_on->format('M d, Y')])
    </div>
@endforeach
