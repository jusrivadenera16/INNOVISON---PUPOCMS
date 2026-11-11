<div class="feedback-stage">
    @forelse($reviews as $evaluation)
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
            <div class="feedback-identity">
                <svg class="avatar" role="img" aria-label="User avatar"><use href="#{{ $avatarSymbol ?? 'avatar-placeholder' }}"></use></svg>
                <div class="feedback-copy">
                    <h4>{{ $evaluation['name'] }}</h4>
                    <span class="comment-meta">{{ $evaluation['role'] }} &middot; {{ $evaluation['time'] }}</span>
                    @if($reviewMessage !== '')
                        <p>{{ $reviewMessage }}</p>
                    @endif
                </div>
            </div>
        </article>
    @empty
        <article class="feedback-slide is-empty">
            <div class="feedback-copy">
                <h4>No feedback has been shared yet.</h4>
                <p>Submitted service evaluations will appear here.</p>
            </div>
        </article>
    @endforelse
</div>
