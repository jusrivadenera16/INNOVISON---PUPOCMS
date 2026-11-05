<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Setting;
use App\Services\GeminiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AdminAssistantController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'max:500'],
        ]);

        $text = trim($validated['text']);
        $normalized = Str::lower((string) preg_replace('/\s+/', ' ', $text));

        $intent = $this->resolveIntent($normalized);
        if ($intent !== null) {
            return response()->json($intent);
        }

        $clinicReply = $this->answerClinicInfo($normalized);
        if ($clinicReply !== null) {
            return response()->json([
                'type' => 'answer',
                'message' => $clinicReply,
                'source' => 'local',
            ]);
        }

        $medicalReply = $this->answerEmergencyGuidance($normalized);
        if ($medicalReply !== null) {
            return response()->json([
                'type' => 'answer',
                'message' => $medicalReply,
                'source' => 'local',
            ]);
        }

        $assistantScopeReply = $this->answerAssistantScope($normalized);
        if ($assistantScopeReply !== null) {
            return response()->json([
                'type' => 'answer',
                'message' => $assistantScopeReply,
                'source' => 'local',
            ]);
        }

        if (!$this->isClinicHealthOrMedicalRequest($normalized)) {
            return response()->json($this->offTopicResponse());
        }

        if ($this->isGeneralMedicalRequestOutsideClinicScope($normalized)) {
            return response()->json($this->offTopicResponse());
        }

        $aiReply = $this->askAi($text);
        if ($aiReply !== null) {
            return response()->json([
                'type' => 'answer',
                'message' => $aiReply,
                'source' => 'ai',
            ]);
        }

        return response()->json([
            'type' => 'answer',
            'message' => config('services.ai.external_processing_enabled', false)
                ? 'The AI service is temporarily unavailable. Please try again shortly. Clinic commands and built-in safety guidance are still available.'
                : 'I can help with clinic commands and built-in safety guidance. Free-text AI is disabled; enable AI_EXTERNAL_PROCESSING_ENABLED and refresh Laravel configuration to turn it on.',
            'source' => 'fallback',
        ]);
    }

    private function resolveIntent(string $text): ?array
    {
        $month = $this->parseMonthFromText($text);
        $basePath = $this->resolveWorkspaceBasePath();
        $isAdminLike = $this->isAdminLikeUser();

        if ($this->containsAny($text, ['generate mar', 'open mar', 'show mar', 'medical accomplishment'])) {
            $url = url($basePath . '/reports/mar?month=' . $month);
            return $this->redirectIntent('Opening MAR report.', $url);
        }

        if ($this->containsAny($text, ['manage mar', 'edit mar conditions'])) {
            if (!$isAdminLike) {
                return [
                    'type' => 'answer',
                    'message' => 'Manage MAR is restricted to Super Admin accounts.',
                ];
            }
            $url = url('/admin/reports/manage-mar?month=' . $month);
            return $this->redirectIntent('Opening Manage MAR.', $url);
        }

        if ($this->containsAny($text, ['print mar', 'export mar'])) {
            $url = url($basePath . '/reports/print-reports?type=mar&month=' . $month);
            return $this->redirectIntent('Generating MAR print report.', $url);
        }

        if ($this->containsAny($text, ['print inventory', 'export inventory'])) {
            $url = url($basePath . '/reports/print-reports?type=inventory&month=' . $month);
            return $this->redirectIntent('Generating inventory print report.', $url);
        }

        if ($this->containsAny($text, ['print appointment', 'export appointment'])) {
            $url = url($basePath . '/reports/print-reports?type=appointment&month=' . $month);
            return $this->redirectIntent('Generating appointment print report.', $url);
        }

        if ($this->containsAny($text, ['open dashboard', 'go to dashboard', 'show dashboard'])) {
            return $this->redirectIntent('Opening dashboard.', url($basePath . '/dashboard'));
        }

        if ($this->containsAny($text, ['open appointment', 'go to appointment', 'show appointment'])) {
            return $this->redirectIntent('Opening appointments.', url($basePath . '/appointments'));
        }

        if ($this->containsAny($text, ['open inventory', 'go to inventory', 'show inventory'])) {
            return $this->redirectIntent('Opening inventory.', url($basePath . '/inventory'));
        }

        if ($this->containsAny($text, ['open reports', 'go to reports', 'show reports'])) {
            return $this->redirectIntent('Opening reports.', url($basePath . '/reports'));
        }

        if ($this->containsAny($text, ['open settings', 'go to settings', 'show settings'])) {
            if (!$isAdminLike) {
                return [
                    'type' => 'answer',
                    'message' => 'Settings access is restricted to Super Admin accounts.',
                ];
            }
            return $this->redirectIntent('Opening settings.', url('/admin/settings'));
        }

        if ($this->containsAny($text, ['open walk in', 'open walkin', 'new walk in'])) {
            return $this->redirectIntent('Opening walk-in management.', url($basePath . '/walkin'));
        }

        if ($this->containsAny($text, ['open export hub', 'reports hub', 'export reports'])) {
            return $this->redirectIntent('Opening export hub.', url($basePath . '/reports/export-hub'));
        }

        if ($this->containsAny($text, ['inventory summary'])) {
            return $this->redirectIntent('Opening inventory summary.', url($basePath . '/reports/inventory-summary'));
        }

        return null;
    }

    private function answerClinicInfo(string $text): ?string
    {
        if ($this->containsAny($text, ['clinic hours', 'open time', 'closing time', 'what time are you open', 'what time do you close'])) {
            $settings = $this->clinicSettings();
            $clinicName = $settings?->clinic_name ?: 'PUP Clinic';
            $open = $settings?->open_time ?: '08:00';
            $close = $settings?->close_time ?: '17:00';

            return "Clinic hours for {$clinicName}: {$open} to {$close}. Please confirm holidays or special schedules at the front desk.";
        }

        if ($this->containsAny($text, ['clinic location', 'where is the clinic', 'where is clinic'])) {
            $settings = $this->clinicSettings();
            $location = $settings?->clinic_location ?: 'clinic office';

            return "Clinic location: {$location}.";
        }

        if ($this->containsAny($text, ['how to book', 'book appointment', 'set appointment'])) {
            return 'For booking: open the Student booking page, choose service/date/time, then submit. For walk-ins, use the Walk-in module in Admin.';
        }

        return null;
    }

    private function clinicSettings(): ?Setting
    {
        try {
            return Setting::first();
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function answerEmergencyGuidance(string $text): ?string
    {
        $emergencyKeywords = [
            'chest pain',
            'difficulty breathing',
            'shortness of breath',
            'severe bleeding',
            'unconscious',
            'fainting',
            'seizure',
            'stroke',
            'one side weak',
            'suicidal',
            'anaphylaxis',
        ];

        if ($this->containsAny($text, $emergencyKeywords)) {
            return 'This may be an emergency. Call emergency services now (911 in the US) or go to the nearest ER immediately. Do not wait for online guidance.';
        }

        return null;
    }

    private function answerAssistantScope(string $text): ?string
    {
        $scopeQuestions = [
            'what can you do',
            'what do you do',
            'help',
            'how can you help',
            'available commands',
            'shortcuts',
        ];

        $simpleGreetings = ['hi', 'hello', 'hey', 'good morning', 'good afternoon', 'good evening'];

        if (in_array($text, $simpleGreetings, true) || $this->containsAny($text, $scopeQuestions)) {
            return 'Hello. I can help only with clinic workflows, health-form guidance, appointments, reports, inventory, and basic symptom triage. I cannot handle unrelated requests such as coding, homework, entertainment, finance, or general web questions.';
        }

        return null;
    }

    private function isClinicHealthOrMedicalRequest(string $text): bool
    {
        return $this->containsAny($text, [
            'clinic',
            'medical',
            'medicine',
            'medication',
            'health',
            'symptom',
            'triage',
            'diagnosis',
            'doctor',
            'nurse',
            'patient',
            'emergency',
            'first aid',
            'fever',
            'cough',
            'cold',
            'flu',
            'headache',
            'migraine',
            'dizzy',
            'dizziness',
            'nausea',
            'vomit',
            'diarrhea',
            'stomach pain',
            'abdominal pain',
            'sore throat',
            'rash',
            'allergy',
            'asthma',
            'blood pressure',
            'bp',
            'pulse',
            'temperature',
            'covid',
            'vaccine',
            'xray',
            'x-ray',
            'clearance',
            'health form',
            'medical assessment',
            'physical assessment',
            'appointment',
            'booking',
            'walk in',
            'walk-in',
            'walkin',
            'consultation',
            'logbook',
            'mar',
            'medical accomplishment',
            'inventory',
            'stock',
            'reports',
            'student record',
            'health record',
            'applicant',
            'faculty',
            'dependent',
            'portal',
            'login',
            'pup',
            'puptas',
            'guisis',
        ]);
    }

    private function isGeneralMedicalRequestOutsideClinicScope(string $text): bool
    {
        $outsideClinicContexts = [
            'travel abroad',
            'travel overseas',
            'international travel',
            'abroad',
            'overseas',
            'visa',
            'passport',
            'embassy',
            'consulate',
            'immigration',
            'country requirement',
            'entry requirement',
            'travel requirement',
            'cdc',
            'who',
        ];

        if (!$this->containsAny($text, $outsideClinicContexts)) {
            return false;
        }

        return !$this->containsAny($text, [
            'pup',
            'campus clinic',
            'school clinic',
            'clinic clearance',
            'health form',
            'medical certificate from clinic',
            'appointment',
            'consultation',
        ]);
    }

    private function offTopicResponse(): array
    {
        return [
            'type' => 'answer',
            'message' => 'I can only help with this clinic system and clinic-related health support. Please ask about PUP clinic workflows, appointments, health forms, inventory, reports, medical certificates handled by the clinic, or basic symptom triage.',
            'source' => 'scope_guard',
        ];
    }

    private function askAi(string $text): ?string
    {
        if (!config('services.ai.external_processing_enabled', false)) {
            return null;
        }

        $provider = strtolower(trim((string) config('services.ai.provider', 'auto')));
        $gemini = app(GeminiClient::class);

        if ($provider === 'gemini') {
            if (!$gemini->configured()) {
                return 'Gemini is selected, but GEMINI_API_KEY is not loaded. Set it in the host environment and refresh Laravel configuration.';
            }

            $reply = $this->askGemini($text, $gemini);
            if ($reply !== null) {
                return $reply;
            }

            Log::warning('Clinic assistant Gemini request failed.', [
                'error' => $gemini->lastError(),
            ]);

            return 'Gemini is configured but could not respond. ' . ($gemini->lastError() ?: 'Check the model and provider configuration.');
        }

        if ($provider === 'auto' && $gemini->configured()) {
            $reply = $this->askGemini($text, $gemini);
            if ($reply !== null) {
                return $reply;
            }

            $geminiFailure = $gemini->lastError();
            Log::warning('Clinic assistant Gemini request failed; trying OpenAI fallback.', [
                'error' => $geminiFailure,
            ]);

            $openAiReply = $this->askOpenAi($text);
            if ($openAiReply !== null && str_starts_with($openAiReply, 'OpenAI ')) {
                return 'Gemini also failed: ' . ($geminiFailure ?: 'the configured model did not return a response.') . ' ' . $openAiReply;
            }

            return $openAiReply;
        }

        return $this->askOpenAi($text);
    }

    private function askGemini(string $text, GeminiClient $gemini): ?string
    {
        if (!$gemini->configured()) {
            return null;
        }

        $prompt = <<<PROMPT
You are the Clinic AI Assistant inside a campus clinic management system.
Only answer requests about this actual campus clinic, patient triage in the clinic context, clinic operations, or this clinic management system.
If the request is general medical information outside the clinic's scope, such as travel abroad requirements, visa medical requirements, country entry rules, or general public-health research, briefly refuse and redirect the user to PUP clinic workflows, health forms, appointments, inventory, reports, clinic-issued certificates, or basic symptom triage.
Do not write code, solve homework, provide entertainment, answer general trivia, give financial/legal advice, research travel rules, or handle unrelated productivity tasks.
For medical or symptom questions, give concise safety-first triage guidance, do not provide a definitive diagnosis, and mention urgent red flags when relevant.

User question:
{$text}
PROMPT;

        $reply = $gemini->generateChatText($prompt, 512, 0.2);
        if ($reply !== null) {
            return $reply;
        }

        return null;
    }

    private function askOpenAi(string $text): ?string
    {
        $apiKey = config('services.openai.api_key') ?: env('OPENAI_API_KEY');
        $model = config('services.openai.model') ?: env('OPENAI_MODEL', 'gpt-4o-mini');

        if (empty($apiKey)) {
            return 'OpenAI fallback is not configured. Set OPENAI_API_KEY or configure Gemini as the active provider.';
        }

        try {
            $response = Http::connectTimeout(4)
                ->timeout(12)
                ->withToken($apiKey)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model,
                    'temperature' => 0.2,
                    'max_tokens' => 260,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are the Clinic AI Assistant for a campus clinic management system. Only answer requests about this actual campus clinic, patient triage in the clinic context, clinic operations, appointments, health forms, inventory, reports, clinic-issued medical certificates, or this clinic system. If the request is general medical information outside the clinic scope, such as travel abroad requirements, visa medical requirements, country entry rules, or public-health research, briefly refuse and redirect to PUP clinic workflows. Do not write code, solve homework, provide entertainment, answer general trivia, research travel rules, or handle unrelated productivity tasks. For symptom questions, give concise safety-first triage guidance, do not provide a definitive diagnosis, and mention urgent red flags and when to escalate to emergency care.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $text,
                        ],
                    ],
                ]);

            if (!$response->successful()) {
                Log::warning('Clinic assistant OpenAI request failed.', [
                    'model' => $model,
                    'status' => $response->status(),
                    'error_type' => data_get($response->json() ?: [], 'error.type'),
                    'error_code' => data_get($response->json() ?: [], 'error.code'),
                ]);

                if (in_array($response->status(), [401, 403], true)) {
                    return 'OpenAI rejected the configured API key (HTTP ' . $response->status() . '). Replace OPENAI_API_KEY in Hostinger and refresh Laravel configuration.';
                }

                return 'OpenAI could not complete the request (HTTP ' . $response->status() . '). Check the provider status and Laravel logs.';
            }

            $content = trim((string) data_get($response->json(), 'choices.0.message.content'));
            return $content !== ''
                ? $content
                : 'OpenAI returned an empty response. Check the selected model and Laravel logs.';
        } catch (\Throwable $e) {
            Log::warning('Clinic assistant OpenAI request raised an exception.', [
                'model' => $model,
                'exception' => get_class($e),
            ]);

            return 'OpenAI could not be reached. Check Hostinger outbound HTTPS access and Laravel logs.';
        }
    }

    private function redirectIntent(string $message, string $url): array
    {
        return [
            'type' => 'action',
            'message' => $message,
            'action' => [
                'kind' => 'redirect',
                'url' => $url,
            ],
        ];
    }

    private function containsAny(string $text, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (Str::contains($text, $needle)) {
                return true;
            }
        }
        return false;
    }

    private function parseMonthFromText(string $text): string
    {
        if (preg_match('/\b(20\d{2})-(0[1-9]|1[0-2])\b/', $text, $matches) === 1) {
            return $matches[1] . '-' . $matches[2];
        }

        $monthMap = [
            'jan' => 1, 'january' => 1,
            'feb' => 2, 'february' => 2,
            'mar' => 3, 'march' => 3,
            'apr' => 4, 'april' => 4,
            'may' => 5,
            'jun' => 6, 'june' => 6,
            'jul' => 7, 'july' => 7,
            'aug' => 8, 'august' => 8,
            'sep' => 9, 'sept' => 9, 'september' => 9,
            'oct' => 10, 'october' => 10,
            'nov' => 11, 'november' => 11,
            'dec' => 12, 'december' => 12,
        ];

        $month = (int) now()->month;
        $year = (int) now()->year;

        foreach ($monthMap as $name => $value) {
            if (preg_match('/\b' . preg_quote($name, '/') . '\b/', $text) === 1) {
                $month = $value;
                break;
            }
        }

        if (preg_match('/\b(20\d{2})\b/', $text, $yearMatch) === 1) {
            $year = (int) $yearMatch[1];
        }

        return sprintf('%04d-%02d', $year, $month);
    }

    private function resolveWorkspaceBasePath(): string
    {
        $role = strtolower((string) (optional(auth()->user())->user_role ?? ''));
        return User::normalizeRole($role) === User::ROLE_ADMIN ? '/assistant' : '/admin';
    }

    private function isAdminLikeUser(): bool
    {
        $role = User::normalizeRole(optional(auth()->user())->user_role ?? '');
        return $role === User::ROLE_SUPERADMIN;
    }
}

