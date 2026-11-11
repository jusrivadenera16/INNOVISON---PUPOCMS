@once
    <link rel="stylesheet" href="{{ asset('css/clinic-review-carousel.css') }}">
    <script src="{{ asset('js/clinic-review-carousel.js') }}" defer></script>
@endonce
@php
    $reviewPages = collect($reviews)->take(9)->chunk(3)->values();
    $reviewCarouselId = 'clinic-reviews-' . ($avatarSymbol ?? 'home');
@endphp
<div class="clinic-review-carousel" data-review-carousel role="region" aria-roledescription="carousel" aria-label="Clinic feedback">
    <div class="feedback-stage">
        @forelse($reviewPages as $pageIndex => $reviewPage)
            <div class="feedback-review-page" data-review-page id="{{ $reviewCarouselId }}-page-{{ $pageIndex }}"
                 role="group" aria-roledescription="slide" aria-label="Page {{ $pageIndex + 1 }} of {{ $reviewPages->count() }}" @if($pageIndex > 0) hidden @endif>
                @foreach($reviewPage as $evaluation)
                    @php
                        $evaluationRating = (int) ($evaluation['rating'] ?? 0);
                        $reviewMessage = trim((string) ($evaluation['message'] ?? ''));
                    @endphp
                    <article class="feedback-slide">
                        <span class="feedback-quote" aria-hidden="true">&ldquo;</span>
                        @if($evaluationRating >= 1 && $evaluationRating <= 5)
                            <div class="feedback-stars" role="img" aria-label="{{ $evaluationRating }} out of 5 stars">
                                @for($star = 1; $star <= 5; $star++)
                                    <span class="{{ $star <= $evaluationRating ? 'is-filled' : '' }}" aria-hidden="true">&#9733;</span>
                                @endfor
                            </div>
                        @endif
                        @if($reviewMessage !== '')
                            <p class="feedback-review-message">{{ $reviewMessage }}</p>
                        @endif
                        <div class="feedback-identity">
                            <svg class="avatar" role="img" aria-label="User avatar"><use href="#{{ $avatarSymbol ?? 'avatar-placeholder' }}"></use></svg>
                            <div class="feedback-copy">
                                <h4>{{ $evaluation['name'] }}</h4>
                                <span class="comment-meta">{{ $evaluation['role'] }} &middot; {{ $evaluation['time'] }}</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @empty
            <article class="feedback-slide is-empty">
                <div class="feedback-copy">
                    <h4>No feedback has been shared yet.</h4>
                    <p>Submitted service evaluations will appear here.</p>
                </div>
            </article>
        @endforelse
    </div>
    @if($reviewPages->isNotEmpty())
        <div class="feedback-carousel-controls" role="group" aria-label="Review pages">
            <span class="feedback-carousel-line" aria-hidden="true"></span>
            @for($dot = 0; $dot < 3; $dot++)
                <button type="button" class="feedback-carousel-dot{{ $dot === 0 ? ' is-active' : '' }}" data-review-dot="{{ $dot }}"
                        aria-label="Review page {{ $dot + 1 }}{{ $dot >= $reviewPages->count() ? ' (unavailable)' : '' }}"
                        aria-pressed="{{ $dot === 0 ? 'true' : 'false' }}"
                        @if($dot < $reviewPages->count()) aria-controls="{{ $reviewCarouselId }}-page-{{ $dot }}" @else disabled @endif></button>
            @endfor
            <span class="feedback-carousel-line" aria-hidden="true"></span>
        </div>
        <span class="feedback-carousel-status" data-review-status aria-live="polite" aria-atomic="true">Page 1 of {{ $reviewPages->count() }}</span>
    @endif
</div>
