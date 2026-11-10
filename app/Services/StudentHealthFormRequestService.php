<?php

namespace App\Services;

use App\Models\HealthFormCategory;
use App\Models\HealthFormSubmission;
use App\Models\HealthProfile;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class StudentHealthFormRequestService
{
    public const SETTING_KEY = 'student_health_form_requests';
    public const COURSES = [
        'BSBA-HRM' => 'Bachelor of Science in Business Administration major in Human Resource Management',
        'BSBA-MM' => 'Bachelor of Science in Business Administration major in Marketing Management',
        'BSECE' => 'Bachelor of Science in Electronics Engineering',
        'BSIT' => 'Bachelor of Science in Information Technology',
        'BSME' => 'Bachelor of Science in Mechanical Engineering',
        'BSOA' => 'Bachelor of Science in Office Administration',
        'BSPSY' => 'Bachelor of Science in Psychology',
        'BSED-ENGLISH' => 'Bachelor of Secondary Education major in English',
        'BSED-MATH' => 'Bachelor of Secondary Education major in Mathematics',
        'DIT' => 'Diploma in Information Technology',
        'DOMT' => 'Diploma in Office Management Technology',
    ];

    public function settings(): array
    {
        $saved = json_decode((string) SystemSetting::getValue(self::SETTING_KEY, '{}'), true);
        return array_replace([
            'enabled' => false, 'purpose_ids' => [], 'eligible_years' => [],
            'starts_on' => null, 'ends_on' => null,
        ], is_array($saved) ? $saved : []);
    }

    public function availablePurposes(): Collection
    {
        return HealthFormCategory::query()->where('is_active', true)->availableFor('student')->orderBy('name')->get();
    }

    public function eligiblePurposes(User $user): Collection
    {
        $settings = $this->settings();
        if (!$settings['enabled'] || $user->clinicAccountTypeKey() !== 'student') return collect();
        $today = now()->toDateString();
        if (($settings['starts_on'] && $today < $settings['starts_on'])
            || ($settings['ends_on'] && $today > $settings['ends_on'])) return collect();

        $profile = $user->healthProfile()->first();
        if (!$profile || $profile->clearance_revoked_at
            || in_array($profile->pullout_status, [HealthProfile::PULLOUT_PENDING, HealthProfile::PULLOUT_COMPLETED], true)
            || strcasecmp((string) $profile->clearance_status, 'Withdrawn') === 0) return collect();
        $approved = in_array($profile->clearance_status, ['Approved', 'Issued', 'Fully Cleared', 'Cleared'], true)
            || $profile->latestApprovedHealthFormSubmission()->exists();
        if (!$approved || HealthFormSubmission::query()->where('user_id', $user->id)
            ->whereIn('status', ['requested', 'submitted', 'under_review', 'needs_correction'])->exists()) return collect();

        // Student-initiated cycles wait 24 hours after the latest approval.
        // Clinic/admin-requested cycles do not use this eligibility gate.
        $latestApproval = HealthFormSubmission::query()->where('user_id', $user->id)
            ->where('status', HealthFormSubmission::STATUS_APPROVED)->max('approved_at');
        if (($latestApproval && Carbon::parse($latestApproval)->gt(now()->subHours(24)))
            || ($profile->verified_at && $profile->verified_at->gt(now()->subHours(24)))) return collect();

        $course = strtoupper(preg_replace('/\s+/', ' ', trim((string) $user->course)));
        $code = array_key_exists($course, self::COURSES) ? $course : '';
        foreach (self::COURSES as $candidate => $name) {
            if ($course === strtoupper($name) || $course === strtoupper($candidate . ' - ' . $name)) $code = $candidate;
        }
        if ($course === '') $code = strtoupper(trim((string) $profile->course_code));
        if (isset($settings['eligible_courses']) && !in_array($code, $settings['eligible_courses'], true)) return collect();
        $yearText = strtolower(trim((string) $user->year));
        $year = preg_match('/^(?:year\s*)?([1-5])(?:st|nd|rd|th)?(?:\s*year)?(?:\s*[-\/]\s*\d+)?$/i', $yearText, $match)
            ? (int) $match[1]
            : (['first year' => 1, 'second year' => 2, 'third year' => 3, 'fourth year' => 4, 'fifth year' => 5][$yearText] ?? 0);
        if (!in_array($year, array_map('intval', $settings['eligible_years'][$code] ?? []), true)) return collect();

        return $this->availablePurposes()->whereIn('id', $settings['purpose_ids'])->values();
    }
}
