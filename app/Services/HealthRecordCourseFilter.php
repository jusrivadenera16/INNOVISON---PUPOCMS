<?php

namespace App\Services;

use App\Models\EmployeeHealthProfile;
use App\Models\HealthProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class HealthRecordCourseFilter
{
    private ?Collection $options = null;
    private array $storedValues = [];

    public static function courseCode(string $value): string
    {
        static $aliases = null;
        static $codes = [];
        if ($aliases === null) {
            $aliases = [];
            foreach (StudentHealthFormRequestService::COURSES as $code => $name) {
                $codeKey = self::academicKey($code);
                $nameKey = self::academicKey($name);
                $codes[$codeKey] = $code;
                foreach ([$codeKey, $nameKey, $codeKey.$nameKey] as $alias) {
                    $aliases[$alias] = $code;
                }
            }
        }
        $key = self::academicKey($value);
        if (isset($aliases[$key])) {
            return $aliases[$key];
        }
        $prefix = preg_split('/\s+[-–—:]\s+/u', trim($value), 2) ?: [];

        return count($prefix) === 2 ? ($codes[self::academicKey($prefix[0])] ?? '') : '';
    }

    public function canonicalValue(string $value): string
    {
        $code = self::courseCode($value);

        return $code !== '' ? StudentHealthFormRequestService::COURSES[$code] : trim($value);
    }

    public function options(): Collection
    {
        $this->loadSources();

        return $this->options;
    }

    public static function isOfficeAudience(string $type): bool
    {
        return in_array($type, ['faculty', 'admin'], true);
    }

    public static function fieldLabel(string $type): string
    {
        return self::isOfficeAudience($type) ? 'Department / Office'
            : (in_array($type, ['student', 'applicant'], true) ? 'Course' : 'Course / Department / Office');
    }

    public static function placeholder(string $type): string
    {
        return self::isOfficeAudience($type) ? 'All Departments / Offices'
            : (in_array($type, ['student', 'applicant'], true) ? 'All Courses' : 'All Courses / Departments / Offices');
    }

    public static function optionLabel(string $value, string $type): string
    {
        $code = self::isOfficeAudience($type) ? '' : self::courseCode($value);

        return $code !== '' && $code !== $value ? $code.' - '.$value : $value;
    }

    // Builders use the same audience rules as the record list, not a second role classifier.
    public function scopedOptions(Builder $health, ?Builder $employees, string $type): Collection
    {
        $officeOnly = self::isOfficeAudience($type);
        $options = collect();
        foreach ($health->with('user:id,course')->get(['id', 'user_id', 'course_college']) as $profile) {
            $value = trim((string) ($profile->course_college ?: optional($profile->user)->course));
            if (!$officeOnly || self::courseCode($value) === '') {
                $options->push($officeOnly ? $value : $this->canonicalValue($value));
            }
        }
        if ($employees) {
            foreach ($employees->with('user:id,course')->get(['id', 'user_id', 'course_college', 'office']) as $profile) {
                $office = trim((string) $profile->office);
                $fallback = trim((string) ($profile->course_college ?: optional($profile->user)->course));
                // Prefer the actual office; legacy department values may live in course_college.
                $options->push($office !== '' ? $office : (self::courseCode($fallback) === '' ? $fallback : ''));
            }
        }

        return $options->filter(fn ($value) => $value !== '')
            ->unique(fn ($value) => $officeOnly ? self::textKey($value) : self::identity($value))->sort()->values();
    }

    public function matchingValues(string $selected, bool $officeOnly = false): array
    {
        $this->loadSources();
        $identity = $officeOnly ? self::textKey($selected) : self::identity($selected);

        return collect([$selected, $this->canonicalValue($selected), self::courseCode($selected)])
            ->merge($this->storedValues)
            ->filter(fn ($value) => trim($value) !== '' && ($officeOnly ? self::textKey($value) : self::identity($value)) === $identity)
            ->unique()->values()->all();
    }

    public function apply(Builder $query, string $selected, bool $employee = false, bool $officeOnly = false): void
    {
        $values = $this->matchingValues($selected, $officeOnly);
        if ($employee && $officeOnly) {
            $query->where(function (Builder $builder) use ($values) {
                $builder->whereIn('office', $values)->orWhere(function (Builder $fallback) use ($values) {
                    $fallback->where(fn (Builder $empty) => $empty->whereNull('office')->orWhere('office', ''))
                        ->where(function (Builder $legacy) use ($values) {
                            $legacy->whereIn('course_college', $values)
                                ->orWhere(function (Builder $userFallback) use ($values) {
                                    $userFallback->where(fn (Builder $empty) => $empty->whereNull('course_college')->orWhere('course_college', ''))
                                        ->whereHas('user', fn (Builder $user) => $user->whereIn('course', $values));
                                });
                        });
                });
            });

            return;
        }
        $query->where(function (Builder $builder) use ($values, $employee) {
            $builder->whereIn('course_college', $values);
            if ($employee) {
                $builder->orWhereIn('office', $values)
                    ->orWhereHas('user', fn (Builder $user) => $user->whereIn('course', $values));
            } else {
                // Keep the existing student fallback: user.course only applies when the profile course is empty.
                $builder->orWhere(function (Builder $fallback) use ($values) {
                    $fallback->where(fn (Builder $empty) => $empty->whereNull('course_college')->orWhere('course_college', ''))
                        ->whereHas('user', fn (Builder $user) => $user->whereIn('course', $values));
                });
            }
        });
    }

    private function loadSources(): void
    {
        if ($this->options !== null) {
            return;
        }

        $options = collect();
        foreach (HealthProfile::query()->notPulledOut()->with('user:id,course')
            ->get(['id', 'user_id', 'course_college']) as $profile) {
            $profileCourse = (string) $profile->course_college;
            $userCourse = (string) optional($profile->user)->course;
            $options->push($profileCourse ?: $userCourse);
            array_push($this->storedValues, $profileCourse, $userCourse);
        }
        foreach (EmployeeHealthProfile::query()->with('user:id,course')
            ->get(['id', 'user_id', 'course_college', 'office']) as $profile) {
            $profileCourse = (string) $profile->course_college;
            $office = (string) $profile->office;
            $userCourse = (string) optional($profile->user)->course;
            $options->push($profileCourse ?: $office ?: $userCourse);
            if ($office !== '') {
                $options->push($office);
            }
            array_push($this->storedValues, $profileCourse, $office, $userCourse);
        }

        $this->options = $options->map(fn ($value) => $this->canonicalValue($value))
            ->filter(fn ($value) => $value !== '')
            ->unique(fn ($value) => self::identity($value))->sort()->values();
    }

    private static function identity(string $value): string
    {
        $code = self::courseCode($value);

        return $code !== '' ? 'course:'.$code : 'other:'.self::textKey($value);
    }

    private static function textKey(string $value): string
    {
        return mb_strtolower(preg_replace('/\s+/u', ' ', trim($value)));
    }

    private static function academicKey(string $value): string
    {
        $value = preg_replace('/\b(?:of|in|and|the|major)\b/i', '', $value);

        return strtolower(preg_replace('/[^A-Za-z0-9]/', '', $value));
    }
}
