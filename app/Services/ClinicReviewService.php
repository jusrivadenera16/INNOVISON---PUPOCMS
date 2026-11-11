<?php

namespace App\Services;

use App\Models\ConsultationEvaluation;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class ClinicReviewService
{
    public function recent(?int $limit = null): Collection
    {
        if (!Schema::hasTable('eval_form') || !Schema::hasColumn('eval_form', 'suggestions')) {
            return collect();
        }

        $query = ConsultationEvaluation::query()
            ->with(['user', 'consultation'])
            ->where('status', 'submitted')
            ->whereNotNull('submitted_at')
            ->whereNotNull('consent_at')
            ->latest('submitted_at')
            ->latest('id');

        if ($limit !== null) {
            $query->limit(30);
        }

        $evaluations = $query->get();
        if ($limit !== null) {
            $evaluations = $evaluations->sort(function (ConsultationEvaluation $a, ConsultationEvaluation $b): int {
                $hasSuggestionA = trim((string) $a->suggestions) !== '';
                $hasSuggestionB = trim((string) $b->suggestions) !== '';
                $ratingA = in_array((int) $a->rating, range(1, 5), true) ? (int) $a->rating : 0;
                $ratingB = in_array((int) $b->rating, range(1, 5), true) ? (int) $b->rating : 0;

                return ($hasSuggestionB <=> $hasSuggestionA)
                    ?: ($ratingB <=> $ratingA)
                    ?: ($b->submitted_at->getTimestamp() <=> $a->submitted_at->getTimestamp())
                    ?: ($b->id <=> $a->id);
            })->take(max(0, $limit))->values();
        }

        return $evaluations->map(function (ConsultationEvaluation $evaluation): array {
            $user = $evaluation->user;
            $rating = (int) $evaluation->rating;

            return [
                'name' => $this->displayName($user),
                'role' => trim((string) ($evaluation->client_type ?: $user?->user_role ?: 'Student')),
                'time' => optional($evaluation->submitted_at)->diffForHumans() ?? 'Recently',
                'rating' => $rating >= 1 && $rating <= 5 ? $rating : null,
                'message' => trim((string) $evaluation->suggestions),
                'service' => $evaluation->serviceLabel(),
            ];
        });
    }

    public function displayName(?User $user): string
    {
        $firstName = trim((string) ($user?->first_name ?? ''));
        $lastName = trim((string) ($user?->last_name ?? ''));

        if ($firstName === '') {
            $nameParts = preg_split('/\s+/', trim((string) ($user?->name ?? ''))) ?: [];
            $firstName = $nameParts[0] ?? '';
            if ($lastName === '') {
                $lastName = count($nameParts) > 1 ? ($nameParts[count($nameParts) - 1] ?? '') : '';
            }
        }

        if ($firstName === '' && $lastName === '') {
            return 'Clinic User';
        }

        $lastInitial = $lastName !== '' ? mb_strtoupper(mb_substr($lastName, 0, 1)) . '.' : '';

        return trim($firstName . ' ' . $lastInitial);
    }
}
