<?php

namespace App\Services;

use App\Models\{EmployeeHealthProfile, HealthFormCategory, HealthFormSubmission, HealthProfile};
use Illuminate\Support\Collection;

class HealthRecordCategoryFilter
{
    public function isRegularStudentReview(HealthProfile $profile): bool
    {
        if ($profile->user?->clinicAccountTypeKey() !== 'student'
            || !in_array(strtolower(trim((string) $profile->user?->student_type)), ['regular', 'shiftee', 'returnee'], true)) {
            return false;
        }

        // Regular, Shiftee and Returnee forms use direct approval, except when
        // their active submission is OJT. Keep the existing method/payload names
        // so UI routing and server-side approval use the same decision.
        $category = $profile->getAttribute('review_category_preloaded')
            ? $profile->getAttribute('review_category_value')
            : HealthFormSubmission::query()->where('health_profile_id', $profile->id)
                ->whereIn('status', ['submitted', 'under_review', 'needs_correction', 'approved', 'Approved'])
                ->latest('id')->value('category');
        $category = trim((string) ($category ?: $profile->health_form_category));

        return !preg_match('/^(?:ojt\b|on[ -]the[ -]job[ -]training\b)/i', $category);
    }

    public function label(?string $category, string $fallback = 'General Health Form'): string
    {
        $category = trim((string) $category);
        return in_array(strtolower($category), ['ojt', 'on the job training', 'on-the-job training'], true)
            ? 'On the Job Training' : ($category !== '' ? $category : $fallback);
    }

    public function options(): Collection
    {
        return HealthFormCategory::pluck('name')
            ->merge(HealthFormSubmission::whereNotNull('category')->distinct()->pluck('category'))
            ->merge(HealthProfile::whereNotNull('health_form_category')->distinct()->pluck('health_form_category'))
            ->merge(EmployeeHealthProfile::whereNotNull('health_form_category')->distinct()->pluck('health_form_category'))
            ->filter(fn ($category) => trim((string) $category) !== '')
            ->map(fn ($category) => $this->label($category))->unique()->sort()->values();
    }

    public function filter(Collection $records, string $category, bool $approvedOnly = false): Collection
    {
        if (trim($category) === '') return $records;
        $submissions = $this->latestCategorySubmissions($records, $approvedOnly);
        $studentCategories = $submissions->whereNotNull('health_profile_id')->pluck('category', 'health_profile_id');
        $employeeCategories = $submissions->whereNotNull('employee_health_profile_id')->pluck('category', 'employee_health_profile_id');
        $selected = mb_strtolower($this->label($category));

        return $records->filter(function ($record) use ($studentCategories, $employeeCategories, $selected) {
            $categories = $record instanceof EmployeeHealthProfile ? $employeeCategories : $studentCategories;
            return mb_strtolower($this->label($categories->get($record->id) ?: $record->health_form_category)) === $selected;
        })->values();
    }

    public function preloadReviewCategories(Collection $records): void
    {
        $students = $records->filter(fn ($record) => $record instanceof HealthProfile);
        if ($students->isEmpty()) return;
        $categories = $this->latestCategorySubmissions($students)->pluck('category', 'health_profile_id');
        foreach ($students as $profile) {
            $profile->setAttribute('review_category_preloaded', true);
            $profile->setAttribute('review_category_value', $categories->get($profile->id));
        }
    }

    private function latestCategorySubmissions(Collection $records, bool $approvedOnly = false): Collection
    {
        if ($records->isEmpty()) return collect();
        $students = $records->reject(fn ($record) => $record instanceof EmployeeHealthProfile)->pluck('id');
        $employees = $records->filter(fn ($record) => $record instanceof EmployeeHealthProfile)->pluck('id');
        $query = HealthFormSubmission::query()->where(function ($query) use ($students, $employees) {
            $query->whereIn('health_profile_id', $students)->orWhereIn('employee_health_profile_id', $employees);
        });
        if ($approvedOnly) $query->whereIn('status', ['approved', 'Approved'])->whereNotNull('approved_at');
        else $query->whereIn('status', ['submitted', 'under_review', 'needs_correction', 'approved', 'Approved']);
        $latestStudentIds = (clone $query)->selectRaw('MAX(id)')->whereNotNull('health_profile_id')->groupBy('health_profile_id');
        $latestEmployeeIds = (clone $query)->selectRaw('MAX(id)')->whereNotNull('employee_health_profile_id')->groupBy('employee_health_profile_id');

        // The list needs category metadata, not every version's encrypted medical snapshot.
        return $query->where(fn ($builder) => $builder->whereIn('id', $latestStudentIds)->orWhereIn('id', $latestEmployeeIds))
            ->get(['id', 'health_profile_id', 'employee_health_profile_id', 'category']);
    }
}
