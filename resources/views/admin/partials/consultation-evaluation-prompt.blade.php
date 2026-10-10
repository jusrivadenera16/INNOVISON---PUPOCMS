@php
    $evaluationPrompt = session('consultation_evaluation_prompt');
    $hasEvaluationPrompt = is_array($evaluationPrompt) && !empty($evaluationPrompt['consultation_id']);
    $evaluationPromptRoute = request()->is('assistant/*')
        ? 'assistant.walkin.consultation-evaluation'
        : 'walkin.consultation-evaluation';
@endphp

@if($hasEvaluationPrompt)
    <style>
        .consultation-evaluation-prompt {
            position: fixed;
            inset: 0;
            z-index: 12000;
            display: grid;
            place-items: center;
            padding: 14px;
            background: rgba(31, 41, 55, .55);
        }

        .consultation-evaluation-dialog {
            position: relative;
            width: min(100%, 400px);
            padding: 22px 18px 18px;
            border: 0;
            border-radius: 9px;
            background: #ffffff;
            box-shadow: 0 22px 58px rgba(15, 23, 42, .3);
            text-align: center;
        }

        .consultation-evaluation-close {
            position: absolute;
            top: 8px;
            right: 8px;
            display: inline-grid;
            width: 26px;
            height: 26px;
            place-items: center;
            padding: 0;
            border: 0;
            background: transparent;
            color: #6b7280;
            cursor: pointer;
        }

        .consultation-evaluation-close:hover,
        .consultation-evaluation-close:focus-visible {
            color: #86111f;
        }

        .consultation-evaluation-close svg {
            width: 17px;
            height: 17px;
        }

        .consultation-evaluation-icon {
            display: grid;
            width: 64px;
            height: 64px;
            margin: 0 auto 13px;
            place-items: center;
            border-radius: 50%;
            background: #fcecef;
            color: #8d1020;
        }

        .consultation-evaluation-icon svg {
            width: 37px;
            height: 37px;
            fill: none;
            stroke: currentColor;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-width: 1.7;
        }

        .consultation-evaluation-dialog h2 {
            margin: 0;
            color: #111827;
            font-size: 18px;
            line-height: 1.25;
        }

        .consultation-evaluation-dialog > p {
            margin: 8px auto 0;
            max-width: 340px;
            color: #374151;
            font-size: 12px;
            line-height: 1.5;
        }

        .consultation-evaluation-points {
            display: grid;
            gap: 0;
            margin-top: 14px;
            padding: 3px 11px;
            border-radius: 7px;
            background: #fff4f5;
            text-align: left;
        }

        .consultation-evaluation-point {
            display: grid;
            grid-template-columns: 24px minmax(0, 1fr);
            align-items: center;
            min-height: 36px;
            gap: 7px;
            border-bottom: 1px solid rgba(141, 16, 32, .09);
            color: #4b1119;
            font-size: 11px;
            line-height: 1.35;
        }

        .consultation-evaluation-point:last-child {
            border-bottom: 0;
        }

        .consultation-evaluation-point svg {
            width: 17px;
            height: 17px;
            color: #8d1020;
        }

        .consultation-evaluation-actions {
            display: flex;
            gap: 8px;
            margin-top: 16px;
        }

        .consultation-evaluation-action {
            position: relative;
            display: inline-flex;
            flex: 1 1 0;
            align-items: center;
            justify-content: center;
            isolation: isolate;
            overflow: hidden;
            min-height: 38px;
            padding: 0 8px;
            border: 1px solid #b7bec8;
            border-radius: 6px;
            background: #ffffff;
            color: #374151;
            cursor: pointer;
            font-size: 12px;
            font-weight: 800;
            transition: background .18s ease, border-color .18s ease, color .18s ease, transform .18s ease, box-shadow .18s ease;
        }

        .consultation-evaluation-action::before {
            position: absolute;
            z-index: 0;
            top: -40%;
            bottom: -40%;
            left: -20%;
            width: 36%;
            background: linear-gradient(105deg, transparent, rgba(255, 248, 196, .9), transparent);
            content: "";
            opacity: 0;
            pointer-events: none;
            transform: translateX(0) skewX(-18deg);
        }

        .consultation-evaluation-action > * {
            position: relative;
            z-index: 1;
        }

        .consultation-evaluation-action:hover,
        .consultation-evaluation-action:focus-visible {
            border-color: #facc15;
            background: #facc15;
            color: #70131b;
            outline: none;
            box-shadow: 0 0 0 3px rgba(250, 204, 21, .15), 0 8px 18px rgba(112, 19, 27, .16);
            transform: translateY(-1px);
        }

        .consultation-evaluation-action:hover::before,
        .consultation-evaluation-action:focus-visible::before {
            animation: consultationEvaluationSweep .8s ease both;
        }

        @keyframes consultationEvaluationSweep {
            0% { opacity: 0; transform: translateX(0) skewX(-18deg); }
            18% { opacity: 1; }
            100% { opacity: 0; transform: translateX(390%) skewX(-18deg); }
        }

        .consultation-evaluation-action.primary {
            border-color: #86111f;
            background: #86111f;
            color: #ffffff;
        }

        .consultation-evaluation-action.primary:hover,
        .consultation-evaluation-action.primary:focus-visible {
            border-color: #facc15;
            background: #facc15;
            color: #70131b;
        }

        .consultation-evaluation-action svg {
            width: 14px;
            height: 14px;
            margin-right: 6px;
            fill: none;
            stroke: currentColor;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-width: 1.8;
        }

        @media (prefers-reduced-motion: reduce) {
            .consultation-evaluation-action {
                transition: none;
            }

            .consultation-evaluation-action:hover::before,
            .consultation-evaluation-action:focus-visible::before {
                animation: none;
            }
        }

        @media (max-width: 360px) {
            .consultation-evaluation-dialog {
                padding-inline: 14px;
            }

            .consultation-evaluation-action {
                font-size: 11px;
            }
        }
    </style>

    <div class="consultation-evaluation-prompt" role="presentation">
        <div class="consultation-evaluation-dialog" role="dialog" aria-modal="true" aria-labelledby="consultationEvaluationPromptTitle">
            <button type="button" class="consultation-evaluation-close" aria-label="Close service evaluation prompt" data-close-consultation-evaluation>
                <x-outline-icon name="x-mark" />
            </button>

            <div class="consultation-evaluation-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24">
                    <path d="m3.5 4.5 17 7.5-17 7.5 3.8-7.5-3.8-7.5Z"></path>
                    <path d="M7.3 12h13.2"></path>
                </svg>
            </div>

            <h2 id="consultationEvaluationPromptTitle">Send Service Evaluation?</h2>
            <p>Would you like to send a service evaluation form to this patient for the completed consultation?</p>

            <div class="consultation-evaluation-points">
                <div class="consultation-evaluation-point">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 6.75h13.5A2.25 2.25 0 0 1 21 9v6a2.25 2.25 0 0 1-2.25 2.25H12l-4.5 3v-3H5.25A2.25 2.25 0 0 1 3 15V9a2.25 2.25 0 0 1 2.25-2.25Z" />
                    </svg>
                    <span>The evaluation will be sent to the patient's dashboard.</span>
                </div>
                <div class="consultation-evaluation-point">
                    <x-outline-icon name="clock" />
                    <span>The patient can submit the evaluation anytime.</span>
                </div>
                <div class="consultation-evaluation-point">
                    <x-outline-icon name="chart-bar" />
                    <span>Results will be included in the service evaluation reports.</span>
                </div>
            </div>

            <form method="POST" action="{{ route($evaluationPromptRoute) }}" class="consultation-evaluation-actions">
                @csrf
                <input type="hidden" name="consultation_id" value="{{ (int) $evaluationPrompt['consultation_id'] }}">
                <button type="submit" name="decision" value="skip" class="consultation-evaluation-action"><span>Skip</span></button>
                <button type="submit" name="decision" value="send" class="consultation-evaluation-action primary">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="m3.5 4.5 17 7.5-17 7.5 3.8-7.5-3.8-7.5Z"></path>
                        <path d="M7.3 12h13.2"></path>
                    </svg>
                    <span>Send Evaluation</span>
                </button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const prompt = document.querySelector('.consultation-evaluation-prompt');
            if (!prompt) return;

            const closeButton = prompt.querySelector('[data-close-consultation-evaluation]');
            closeButton?.addEventListener('click', function () {
                prompt.remove();
            });
        });
    </script>
@endif
