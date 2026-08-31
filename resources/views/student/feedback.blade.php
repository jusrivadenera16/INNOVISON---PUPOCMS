@extends('layouts.student')

@section('title', 'Appointment Feedback')

@push('styles')
<style>
    .feedback-shell {
        max-width: 1040px;
        margin: 0 auto;
        padding: 28px 20px 70px;
    }

    .feedback-duo {
        display: grid;
        grid-template-columns: minmax(280px, .92fr) minmax(360px, 1.08fr);
        border-radius: 24px;
        overflow: hidden;
        background: #ffffff;
        border: 1px solid rgba(112, 19, 27, .18);
        box-shadow: 0 26px 60px rgba(15, 23, 42, .12);
    }

    .feedback-left,
    .feedback-right {
        min-height: 560px;
        padding: clamp(26px, 4vw, 42px);
    }

    .feedback-left {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 34px;
        background:
            radial-gradient(circle at 18% 8%, rgba(255, 255, 255, .14), transparent 34%),
            linear-gradient(145deg, #9b1722 0%, #73121b 48%, #530a12 100%);
        color: #ffffff;
    }

    .feedback-kicker {
        display: inline-flex;
        width: fit-content;
        padding: 7px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .14);
        border: 1px solid rgba(255, 255, 255, .22);
        color: #ffdb4d;
        font-size: .72rem;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .feedback-title {
        margin: 16px 0 10px;
        color: #ffffff;
        font-size: clamp(2rem, 4vw, 3rem);
        line-height: .98;
        font-weight: 900;
        letter-spacing: 0;
    }

    .feedback-subtitle {
        max-width: 440px;
        margin: 0;
        color: rgba(255, 255, 255, .82);
        line-height: 1.65;
        font-size: .98rem;
    }

    .feedback-appointment-chip {
        margin-top: 24px;
        padding: 16px 18px;
        border-radius: 18px;
        background: rgba(255, 255, 255, .12);
        border: 1px solid rgba(255, 255, 255, .18);
        box-shadow: inset 0 1px 0 rgba(255,255,255,.12);
    }

    .feedback-appointment-chip strong {
        display: block;
        color: #ffffff;
        font-size: 1.05rem;
        font-weight: 900;
    }

    .feedback-appointment-chip span {
        display: block;
        margin-top: 5px;
        color: rgba(255, 255, 255, .82);
        font-weight: 600;
    }

    .feedback-label {
        display: block;
        margin-bottom: 10px;
        color: inherit;
        font-size: .78rem;
        font-weight: 900;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .feedback-rating {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .feedback-rating input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .feedback-rating label {
        width: 48px;
        height: 48px;
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, .24);
        background: rgba(255, 255, 255, .12);
        color: rgba(255, 255, 255, .72);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.45rem;
        cursor: pointer;
        transition: transform .18s ease, background-color .18s ease, color .18s ease, border-color .18s ease, box-shadow .18s ease;
    }

    .feedback-rating label:hover,
    .feedback-rating input:focus-visible + label {
        transform: translateY(-3px) scale(1.04);
        color: #ffdb4d;
        border-color: rgba(255, 219, 77, .72);
    }

    .feedback-rating input:checked + label,
    .feedback-rating label.is-active {
        color: #111827;
        background: #ffcf19;
        border-color: #ffcf19;
        box-shadow: 0 16px 30px rgba(255, 207, 25, .28);
        animation: feedbackStarPop .32s ease;
    }

    @keyframes feedbackStarPop {
        0% { transform: scale(.82) rotate(-8deg); }
        60% { transform: scale(1.15) rotate(5deg); }
        100% { transform: scale(1) rotate(0); }
    }

    .feedback-right {
        background: #ffffff;
        color: #172033;
    }

    .feedback-right-head {
        margin-bottom: 20px;
    }

    .feedback-right-title {
        margin: 0;
        color: #172033;
        font-size: clamp(1.4rem, 2.6vw, 2rem);
        font-weight: 900;
    }

    .feedback-right-copy {
        margin: 8px 0 0;
        color: #64748b;
        line-height: 1.55;
        font-size: .92rem;
    }

    .feedback-detail-grid {
        display: grid;
        gap: 12px;
        margin-bottom: 18px;
    }

    .feedback-detail {
        padding: 14px 16px;
        border-radius: 16px;
        border: 1px solid #e5edf6;
        background: #fbfdff;
    }

    .feedback-detail span {
        display: block;
        color: #6b7a90;
        font-size: .72rem;
        font-weight: 900;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .feedback-detail strong,
    .feedback-detail p {
        display: block;
        margin: 5px 0 0;
        color: #172033;
        font-size: .95rem;
        font-weight: 800;
        line-height: 1.45;
        overflow-wrap: anywhere;
    }

    .feedback-detail p {
        font-weight: 600;
        color: #334155;
    }

    .feedback-textarea,
    .feedback-text-static {
        width: 100%;
        min-height: 142px;
        border-radius: 18px;
        border: 1px solid #d8e2ee;
        background: #ffffff;
        padding: 15px 16px;
        color: #172033;
        font-size: .95rem;
        line-height: 1.55;
        resize: vertical;
    }

    .feedback-textarea:focus {
        outline: none;
        border-color: #8b1721;
        box-shadow: 0 0 0 4px rgba(139, 23, 33, .09);
    }

    .feedback-text-static {
        background: #f8fafc;
        white-space: pre-wrap;
    }

    .feedback-errors,
    .feedback-readonly-banner {
        margin-bottom: 16px;
        padding: 13px 15px;
        border-radius: 14px;
        font-size: .9rem;
        line-height: 1.55;
    }

    .feedback-errors {
        border: 1px solid #fecaca;
        background: #fff1f2;
        color: #b91c1c;
    }

    .feedback-readonly-banner {
        border: 1px solid #bbf7d0;
        background: #f0fdf4;
        color: #166534;
    }

    .feedback-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 20px;
        flex-wrap: wrap;
    }

    .feedback-btn {
        min-height: 46px;
        padding: 12px 20px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        text-decoration: none;
        border: 1px solid transparent;
        cursor: pointer;
        transition: transform .18s ease, box-shadow .18s ease, background-color .18s ease, color .18s ease;
    }

    .feedback-btn.primary {
        background: #8b1721;
        color: #ffffff;
        box-shadow: 0 16px 28px rgba(139, 23, 33, .18);
    }

    .feedback-btn.primary:hover,
    .feedback-btn.primary:focus {
        transform: translateY(-2px);
        background: #ffcf19;
        color: #111827;
    }

    .feedback-btn.secondary {
        background: #ffffff;
        color: #8b1721;
        border-color: rgba(139, 23, 33, .28);
    }

    .feedback-btn.secondary:hover,
    .feedback-btn.secondary:focus {
        transform: translateY(-2px);
        border-color: #ffcf19;
    }

    @media (max-width: 820px) {
        .feedback-duo {
            grid-template-columns: 1fr;
        }

        .feedback-left,
        .feedback-right {
            min-height: auto;
        }
    }
</style>
@endpush

@section('content')
@php
    $isReadonly = (bool) optional($existingFeedback)->submitted_at;
    $appointmentNumber = trim((string) ($appointment->apt_id ?? '')) ?: 'N/A';
    $appointmentNotes = trim((string) ($appointment->notes ?? $appointment->remarks ?? ''));
    $nurseRemarks = trim((string) optional($consultation)->comments);
    $selectedRating = (string) old('rating', optional($existingFeedback)->rating);
@endphp
<div class="feedback-shell">
    @if($isReadonly)
        <div class="feedback-duo">
            <section class="feedback-left">
                <div>
                    <span class="feedback-kicker">Completed Visit</span>
                    <h1 class="feedback-title">Appointment Feedback</h1>
                    <p class="feedback-subtitle">Your feedback has already been submitted. You can review it here anytime from your notifications.</p>
                    <div class="feedback-appointment-chip">
                        <strong>{{ $appointment->service }}</strong>
                        <span>{{ \Carbon\Carbon::parse($appointment->date)->format('M d, Y') }} at {{ \Carbon\Carbon::parse($appointment->time)->format('g:i A') }}</span>
                    </div>
                </div>
                <div>
                    <label class="feedback-label">Your Rating</label>
                    <div class="feedback-rating" aria-label="Submitted rating">
                        @for($i = 1; $i <= 5; $i++)
                            <input type="radio" id="ratingRead{{ $i }}" value="{{ $i }}" {{ (string) optional($existingFeedback)->rating === (string) $i ? 'checked' : '' }} disabled>
                            <label for="ratingRead{{ $i }}" class="{{ (int) optional($existingFeedback)->rating >= $i ? 'is-active' : '' }}">&#9733;</label>
                        @endfor
                    </div>
                </div>
            </section>

            <section class="feedback-right">
                <div class="feedback-right-head">
                    <h2 class="feedback-right-title">Review Summary</h2>
                    <p class="feedback-right-copy">Feedback submitted {{ optional($existingFeedback->submitted_at)->format('M d, Y g:i A') }}. Editing is disabled after submission.</p>
                </div>
                <div class="feedback-readonly-banner">Thank you for helping the clinic improve student care.</div>
                <div class="feedback-detail-grid">
                    <div class="feedback-detail"><span>Appointment Number</span><strong>{{ $appointmentNumber }}</strong></div>
                    <div class="feedback-detail"><span>Notes</span><p>{{ $appointmentNotes !== '' ? $appointmentNotes : 'No appointment notes recorded.' }}</p></div>
                    <div class="feedback-detail"><span>Nurse Remarks</span><p>{{ $nurseRemarks !== '' ? $nurseRemarks : 'No nurse remarks recorded.' }}</p></div>
                </div>
                <label class="feedback-label">Comment</label>
                <div class="feedback-text-static">{{ trim((string) optional($existingFeedback)->feedback) !== '' ? $existingFeedback->feedback : 'No written comments were added.' }}</div>
                <div class="feedback-actions">
                    <a href="{{ url('/student/account?view=notifications') }}" class="feedback-btn secondary">Back to Notifications</a>
                </div>
            </section>
        </div>
    @else
        <form action="{{ route('student.feedback.store', ['appointment' => $appointment->id]) }}" method="POST" class="feedback-duo">
            @csrf
            <section class="feedback-left">
                <div>
                    <span class="feedback-kicker">Completed Visit</span>
                    <h1 class="feedback-title">Appointment Feedback</h1>
                    <p class="feedback-subtitle">Your appointment is complete. A short review helps the clinic improve service quality and student experience.</p>
                    <div class="feedback-appointment-chip">
                        <strong>{{ $appointment->service }}</strong>
                        <span>{{ \Carbon\Carbon::parse($appointment->date)->format('M d, Y') }} at {{ \Carbon\Carbon::parse($appointment->time)->format('g:i A') }}</span>
                    </div>
                </div>
                <div>
                    <label class="feedback-label">How would you rate your appointment?</label>
                    <div class="feedback-rating" id="feedbackRatingGroup">
                        @for($i = 1; $i <= 5; $i++)
                            <input type="radio" name="rating" id="rating{{ $i }}" value="{{ $i }}" {{ $selectedRating === (string) $i ? 'checked' : '' }}>
                            <label for="rating{{ $i }}" class="{{ (int) $selectedRating >= $i ? 'is-active' : '' }}" data-rating-star="{{ $i }}">&#9733;</label>
                        @endfor
                    </div>
                </div>
            </section>

            <section class="feedback-right">
                <div class="feedback-detail-grid">
                    <div class="feedback-detail"><span>Appointment Number</span><strong>{{ $appointmentNumber }}</strong></div>
                    <div class="feedback-detail"><span>Notes</span><p>{{ $appointmentNotes !== '' ? $appointmentNotes : 'No appointment notes recorded.' }}</p></div>
                    <div class="feedback-detail"><span>Nurse Remarks</span><p>{{ $nurseRemarks !== '' ? $nurseRemarks : 'No nurse remarks recorded.' }}</p></div>
                </div>
                <label class="feedback-label" for="feedbackText">Comment</label>
                <textarea id="feedbackText" name="feedback" class="feedback-textarea" placeholder="Share anything helpful about your clinic experience.">{{ old('feedback', optional($existingFeedback)->feedback) }}</textarea>
                <div class="feedback-actions">
                    <a href="{{ url('/student/account?view=notifications') }}" class="feedback-btn secondary">Back</a>
                    <button type="submit" class="feedback-btn primary">Submit Feedback</button>
                </div>
            </section>
        </form>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ratingGroup = document.getElementById('feedbackRatingGroup');
        if (!ratingGroup) return;

        const stars = Array.from(ratingGroup.querySelectorAll('[data-rating-star]'));
        const paintStars = (rating) => {
            stars.forEach((star) => {
                const starValue = Number(star.dataset.ratingStar || 0);
                star.classList.toggle('is-active', starValue <= rating);
            });
        };

        stars.forEach((star) => {
            star.addEventListener('click', () => {
                paintStars(Number(star.dataset.ratingStar || 0));
            });
        });
    });
</script>
@endpush
