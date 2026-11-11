@php $suggestion = trim((string) $evaluation->suggestions); @endphp
<article class="evaluation-card evaluation-response-card">
    <div class="evaluation-avatar" aria-hidden="true"><x-outline-icon name="document-check" /></div>
    <div>
        <div class="evaluation-card-head">
            <div class="evaluation-card-name">{{ app(\App\Services\ClinicReviewService::class)->displayName($evaluation->user) }}</div>
            <div class="evaluation-card-meta">Submitted {{ optional($evaluation->submitted_at)->format('M d, Y g:i A') }}</div>
        </div>
        <div class="evaluation-chip-row">
            <span class="evaluation-chip">{{ $evaluation->serviceLabel() }}</span>
            @if($evaluation->consultation?->consultation_date)
                <span class="evaluation-chip">Consultation {{ $evaluation->consultation->consultation_date->format('M d, Y') }}</span>
            @endif
        </div>
        @if($suggestion !== '')
            <p class="evaluation-message">{{ $suggestion }}</p>
        @endif
    </div>
    <div class="evaluation-side">
        <div class="evaluation-rating">{{ is_numeric($evaluation->rating) ? $evaluation->rating . '/5' : 'N/A' }}</div>
        <div class="evaluation-rating-sub">Overall</div>
    </div>
</article>
