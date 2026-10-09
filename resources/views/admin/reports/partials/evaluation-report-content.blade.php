@php
    $role = \App\Models\User::normalizeRole(optional(auth()->user())->user_role ?? '');
    $reportsHomeUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports') : url('/admin/reports');
@endphp
<div class="evaluation-report-shell">
    @include('admin.partials.report-breadcrumb', ['items' => [
        ['label' => 'Reports', 'url' => $reportsHomeUrl],
        ['label' => 'Service Evaluation'],
    ]])
    <div class="evaluation-report-head">
        <div>
            <h1 class="evaluation-report-title">Service Evaluation Reports</h1>
            <p class="evaluation-report-copy">Review submitted Online Client Satisfaction Survey responses, Citizen's Charter answers, service quality ratings, and suggestions.</p>
        </div>
    </div>

    <div class="evaluation-stat-grid">
        <div class="evaluation-stat-card">
            <span>Total Responses</span>
            <strong>{{ number_format($totalSubmissions) }}</strong>
        </div>
        <div class="evaluation-stat-card">
            <span>Average Overall Satisfaction</span>
            <strong>{{ $ratedCount > 0 ? number_format($averageRating, 1) . '/5' : 'N/A' }}</strong>
        </div>
        <div class="evaluation-stat-card">
            <span>Positive Ratings (4-5)</span>
            <strong>{{ number_format($positiveCount) }} <small>{{ $ratedCount > 0 ? number_format($satisfactionRate, 1) . '%' : 'No ratings' }}</small></strong>
        </div>
    </div>

    <div class="evaluation-layout">
        <aside class="evaluation-panel">
            <div class="evaluation-score-block">
                <div class="evaluation-score-kicker">Overall Satisfaction</div>
                <div class="evaluation-score-number">{{ $ratedCount > 0 ? number_format($averageRating, 1) . '/5' : 'N/A' }}</div>
                <div class="evaluation-score-copy">
                    {{ number_format($needsAttentionCount) }} submitted responses rated overall satisfaction 1 or 2.
                </div>
            </div>

            <form method="GET" class="evaluation-filter-form">
                <h3>Filter Responses</h3>
                <div class="evaluation-field">
                    <label for="evaluation-date-from">From</label>
                    <input id="evaluation-date-from" type="date" name="date_from" value="{{ $dateFrom }}">
                </div>
                <div class="evaluation-field">
                    <label for="evaluation-date-to">To</label>
                    <input id="evaluation-date-to" type="date" name="date_to" value="{{ $dateTo }}">
                </div>
                <div class="evaluation-filter-actions">
                    <button type="submit" class="evaluation-btn">Apply</button>
                    <a href="{{ url()->current() }}" class="evaluation-btn secondary">Clear</a>
                </div>
            </form>

            <div class="evaluation-filter-form">
                <h3>Citizen's Charter</h3>
                <div class="evaluation-cc-list">
                    @foreach($ccSummary as $ccQuestion)
                        <div class="evaluation-cc-item">
                            <p class="evaluation-cc-question">{{ $ccQuestion['question'] }}</p>
                            @foreach($ccQuestion['choices'] as $choice => $label)
                                <div class="evaluation-cc-choice">
                                    <span>{{ $label }}</span>
                                    <strong>{{ number_format($ccQuestion['counts'][$choice] ?? 0) }}</strong>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </aside>

        <section>
            <div class="evaluation-panel">
                <h2 class="evaluation-section-title">Service Quality Dimensions</h2>
                <div class="evaluation-sqd-list">
                    @foreach($sqdSummary as $code => $dimension)
                        <div class="evaluation-sqd-row">
                            <div class="evaluation-sqd-label">{{ $code }}. {{ $dimension['label'] }}</div>
                            <div class="evaluation-sqd-average">
                                {{ $dimension['average'] !== null ? number_format($dimension['average'], 1) . '/5' : 'N/A' }}
                            </div>
                            <div class="evaluation-sqd-meta">
                                {{ number_format($dimension['valid_count']) }} rated
                                @if($dimension['na_count'] > 0)
                                    &middot; {{ number_format($dimension['na_count']) }} N/A
                                @endif
                                &middot; Ratings 5 to 1:
                                {{ implode(' / ', array_reverse(array_slice($dimension['distribution'], 1, 5))) }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="evaluation-response-heading">
                <h2>Submitted Responses</h2>
                <span>{{ number_format($totalSubmissions) }} total</span>
            </div>

            @if($evaluationItems->count() > 0)
                <div class="evaluation-list">
                    @foreach($evaluationItems as $evaluation)
                        <article class="evaluation-card evaluation-response-card">
                            <div class="evaluation-avatar" aria-hidden="true"><x-outline-icon name="document-check" /></div>
                            <div>
                                <div class="evaluation-card-head">
                                    <div class="evaluation-card-name">{{ $evaluation->client_type ?: 'Client' }}</div>
                                    <div class="evaluation-card-meta">Submitted {{ optional($evaluation->submitted_at)->format('M d, Y g:i A') }}</div>
                                </div>
                                <div class="evaluation-chip-row">
                                    <span class="evaluation-chip">{{ $evaluation->consultation?->service ?: 'Clinic service' }}</span>
                                    @if($evaluation->consultation?->consultation_date)
                                        <span class="evaluation-chip">Consultation {{ $evaluation->consultation->consultation_date->format('M d, Y') }}</span>
                                    @endif
                                </div>
                                <p class="evaluation-message">{{ trim((string) $evaluation->suggestions) !== '' ? $evaluation->suggestions : 'No optional suggestion provided.' }}</p>
                            </div>
                            <div class="evaluation-side">
                                <div class="evaluation-rating">{{ is_numeric($evaluation->rating) ? $evaluation->rating . '/5' : 'N/A' }}</div>
                                <div class="evaluation-rating-sub">Overall</div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="evaluation-pagination">
                    {{ $evaluationItems->links() }}
                </div>
            @else
                <div class="evaluation-empty">No submitted service evaluations were found for this date range.</div>
            @endif
        </section>
    </div>
</div>
